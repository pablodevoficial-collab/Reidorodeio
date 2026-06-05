<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modalidade;
use App\Models\ModalidadeOddsSetting;
use App\Models\Rodeio;
use App\Services\CompetitorOddsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ModalidadeOddsController extends Controller
{
    private function resolveRodeioOrderColumn(): string
    {
        if (Schema::hasColumn('rodeios', 'nome')) {
            return 'nome';
        }
        if (Schema::hasColumn('rodeios', 'titulo')) {
            return 'titulo';
        }
        if (Schema::hasColumn('rodeios', 'name')) {
            return 'name';
        }

        return 'id';
    }

    public function index(Request $request, CompetitorOddsService $oddsService)
    {
        $pageTitle = 'Automação de Odds por Modalidade';

        $rodeios = Rodeio::query()
            ->orderBy($this->resolveRodeioOrderColumn())
            ->get();

        $query = Modalidade::query()
            ->with(['rodeio', 'oddsSetting']);

        if ($request->filled('rodeio_id')) {
            $query->where('rodeio_id', (int) $request->input('rodeio_id'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where('nome', 'like', '%' . $q . '%');
        }

        $modalidades = $query
            ->orderBy('rodeio_id')
            ->orderBy('nome')
            ->paginate(20)
            ->appends($request->only(['rodeio_id', 'q']));

        $scopeRodeioFilter = $request->filled('rodeio_id')
            ? (int) $request->input('rodeio_id')
            : null;

        $modalidades->setCollection(
            $modalidades->getCollection()->map(function (Modalidade $modalidade) use ($oddsService, $scopeRodeioFilter) {
                $scopeRodeioId = $scopeRodeioFilter ?: ((int) ($modalidade->rodeio_id ?: 0) ?: null);
                $settings = $oddsService->getMergedSettings((int) $modalidade->id);
                $finance = $oddsService->getFinanceSnapshot($scopeRodeioId, (int) $modalidade->id);
                $boostAvailable = $oddsService->isBoostAvailable($settings, $finance);
                $lowVolumeCompetitors = $oddsService->countLowVolumeCompetitors(
                    $scopeRodeioId,
                    (int) $modalidade->id,
                    (int) ($settings['low_bet_threshold'] ?? 3)
                );

                return (object) [
                    'modalidade' => $modalidade,
                    'settings' => $settings,
                    'finance' => $finance,
                    'boost_available' => $boostAvailable,
                    'low_volume_competitors' => $lowVolumeCompetitors,
                    'scope_rodeio_id' => $scopeRodeioId,
                ];
            })
        );

        return view('admin.modalidade_odds.index', compact(
            'pageTitle',
            'rodeios',
            'modalidades'
        ));
    }

    public function edit(Request $request, Modalidade $modalidade, CompetitorOddsService $oddsService)
    {
        $pageTitle = 'Configurar Odds Automáticas';
        $modalidade->load(['rodeio', 'competitors:id,nome']);

        $scopeRodeioId = (int) ($request->input('rodeio_id') ?: $modalidade->rodeio_id);
        if ($scopeRodeioId <= 0) {
            $scopeRodeioId = null;
        }

        $settings = $oddsService->getMergedSettings((int) $modalidade->id);
        $finance = $oddsService->getFinanceSnapshot($scopeRodeioId, (int) $modalidade->id);
        $boostAvailable = $oddsService->isBoostAvailable($settings, $finance);
        $counts = $oddsService->getCompetitorBetCounts($scopeRodeioId, (int) $modalidade->id);

        $competitorRows = $modalidade->competitors
            ->map(function ($competitor) use ($counts, $settings) {
                $x1Count = (int) ($counts[(int) $competitor->id] ?? 0);
                $tier = 'normal';

                if ($x1Count <= (int) ($settings['very_low_bet_threshold'] ?? 1)) {
                    $tier = 'very_low';
                } elseif ($x1Count <= (int) ($settings['low_bet_threshold'] ?? 3)) {
                    $tier = 'low';
                }

                return (object) [
                    'id' => (int) $competitor->id,
                    'nome' => (string) $competitor->nome,
                    'x1_count' => $x1Count,
                    'tier' => $tier,
                ];
            })
            ->sortBy('x1_count')
            ->values();

        return view('admin.modalidade_odds.edit', compact(
            'pageTitle',
            'modalidade',
            'scopeRodeioId',
            'settings',
            'finance',
            'boostAvailable',
            'competitorRows'
        ));
    }

    public function update(Request $request, Modalidade $modalidade)
    {
        $validated = $request->validate([
            'bankroll_gate_amount' => 'required|numeric|min:0',
            'low_bet_threshold' => 'required|integer|min:0|max:9999',
            'very_low_bet_threshold' => 'required|integer|min:0|max:9999',
            'max_free_odd' => 'required|numeric|min:1|max:2',
            'max_premium_odd' => 'required|numeric|min:1|max:10',
            'min_house_margin_percent' => 'required|numeric|min:30|max:100',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        if ((int) $validated['very_low_bet_threshold'] > (int) $validated['low_bet_threshold']) {
            return back()
                ->withErrors(['very_low_bet_threshold' => 'A faixa "quase sem participação" não pode ser maior que a faixa "baixa participação".'])
                ->withInput();
        }

        if ((float) $validated['max_premium_odd'] < (float) $validated['max_free_odd']) {
            return back()
                ->withErrors(['max_premium_odd' => 'O teto Premium não pode ser menor que o teto Free.'])
                ->withInput();
        }

        ModalidadeOddsSetting::query()->updateOrCreate(
            ['modalidade_id' => (int) $modalidade->id],
            $validated
        );

        $notify[] = ['success', 'Configuração de odds automáticas salva com sucesso.'];
        return redirect()->route('admin.modalidade_odds.edit', $modalidade->id)->withNotify($notify);
    }
}
