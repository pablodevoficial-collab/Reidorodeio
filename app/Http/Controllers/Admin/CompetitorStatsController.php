<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\CompetitorScoringLog;
use Illuminate\Http\Request;

class CompetitorStatsController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Estatísticas dos Competidores';
        $query = Competitor::query()->with('stats');

        if ($search = $request->get('q')) {
            $query->where('nome', 'like', "%{$search}%");
        }

        $competitors = $query->orderBy('nome')->get();
        
        // Garantir que todos os competidores tenham estatísticas
        foreach ($competitors as $competitor) {
            if (!$competitor->stats) {
                $competitor->stats()->create([
                    'competitor_id' => $competitor->id,
                    'vitorias' => 0,
                    'derrotas' => 0,
                    'empates' => 0,
                    'aproveitamento' => 0,
                    'pontuacao_media' => 0,
                    'pontuacao_total' => 0,
                    'last_points' => 0,
                    'count_boa' => 0,
                    'count_negativas_total' => 0,
                    'count_errou_pescoco' => 0,
                    'count_dobrada' => 0,
                    'count_cabresteou' => 0,
                    'count_duas_voltas' => 0,
                    'count_limpou_garupa' => 0,
                    'count_cola' => 0,
                    'count_cupim' => 0,
                    'count_top' => 0,
                    'count_pescou' => 0,
                    'count_errou_pata' => 0,
                    'count_errou_top' => 0,
                    'count_garupa_neg' => 0,
                    'count_cola_neg' => 0,
                    'count_uma_aspa' => 0,
                    'count_por_cima' => 0,
                    'count_limpou_cupim_longe' => 0,
                ]);
                $competitor->load('stats'); // Recarregar a relação
            }
        }

        return view('admin.competitor_stats.index', compact('pageTitle', 'competitors'));
    }

    public function show(Competitor $competitor)
    {
        $pageTitle = 'Estatísticas - ' . ($competitor->nome ?? 'Competidor');
        $competitor->load('stats');

        // Se não tiver stats, criar uma entrada vazia
        if (!$competitor->stats) {
            $competitor->stats()->create([
                'competitor_id' => $competitor->id,
                'vitorias' => 0,
                'derrotas' => 0,
                'empates' => 0,
                'aproveitamento' => 0,
                'pontuacao_media' => 0,
                'pontuacao_total' => 0,
                'last_points' => 0,
                'count_boa' => 0,
                'count_negativas_total' => 0,
                'count_errou_pescoco' => 0,
                'count_dobrada' => 0,
                'count_cabresteou' => 0,
                'count_duas_voltas' => 0,
                'count_limpou_garupa' => 0,
                'count_cola' => 0,
                'count_cupim' => 0,
                'count_top' => 0,
                'count_pescou' => 0,
                'count_errou_pata' => 0,
                'count_errou_top' => 0,
                'count_garupa_neg' => 0,
                'count_cola_neg' => 0,
                'count_uma_aspa' => 0,
                'count_por_cima' => 0,
                'count_limpou_cupim_longe' => 0,
            ]);
            $competitor->load('stats'); // Recarregar
        }

        if (request()->boolean('as') || request()->get('as') === 'json') {
            // resposta JSON para o modal "Ver todas"
            $stats = optional($competitor->stats)->toArray() ?? [];
            unset($stats['id'], $stats['competitor_id'], $stats['created_at'], $stats['updated_at']);
            return response()->json([
                'success' => true,
                'competitor' => [
                    'id' => $competitor->id,
                    'nome' => $competitor->nome,
                    'foto_url' => $competitor->foto_url ?? asset('assets/images/logo_icon/favicon.png'),
                ],
                'stats' => $stats,
            ]);
        }

        $logs = CompetitorScoringLog::where('competitor_id', $competitor->id)
            ->orderBy('scored_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.competitor_stats.show', compact('pageTitle', 'competitor', 'logs'));
    }
}
