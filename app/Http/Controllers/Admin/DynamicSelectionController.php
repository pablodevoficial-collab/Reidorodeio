<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\Modalidade;
use App\Models\Rodeio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DynamicSelectionController extends Controller
{
    public function index()
    {
        $pageTitle = 'Seleção Dinâmica';
        return view('admin.dynamic_selection.index', compact('pageTitle'));
    }

    public function getCompetitors(Request $request)
    {
        if (!Schema::hasTable('competitor_modalidade')) {
            return response()->json([
                'competitors' => [],
                'stats' => [
                    'total' => 0,
                    'confirmados' => 0,
                    'inscritos' => 0,
                    'eliminados' => 0,
                ],
                'warning' => 'Tabela competitor_modalidade não existe neste ambiente.',
            ]);
        }

        $modalidadeId = $request->get('modalidade_id');
        if (!$modalidadeId) {
            return response()->json([
                'competitors' => [],
                'stats' => [
                    'total' => 0,
                    'confirmados' => 0,
                    'inscritos' => 0,
                    'eliminados' => 0,
                ],
            ]);
        }

        $query = Competitor::with(['modalidades' => function($q) use ($request) {
            $q->where('modalidades.id', $request->modalidade_id);
        }]);

        // Filtrar competidores que estão na modalidade específica
        $query->whereHas('modalidades', function($q) use ($request) {
            $q->where('modalidades.id', $request->modalidade_id);
        });

        $competitors = $query->get()->map(function($competitor) {
            $modalidade = $competitor->modalidades->first();
            if ($modalidade) {
                $competitor->modalidade_nome = $modalidade->nome;
                $competitor->modalidade_id = $modalidade->id;
            }
            return $competitor;
        });

        return response()->json([
            'competitors' => $competitors,
            'stats' => [
                'total' => $competitors->count(),
                'confirmados' => $competitors->where('pivot.status', 'confirmado')->count(),
                'inscritos' => $competitors->where('pivot.status', 'inscrito')->count(),
                'eliminados' => $competitors->where('pivot.status', 'eliminado')->count()
            ]
        ]);
    }

    public function searchCompetitors(Request $request)
    {
        $searchTerm = $request->get('q', '');
        $modalidadeId = $request->get('modalidade_id');

        $query = Competitor::where('nome', 'like', "%{$searchTerm}%");

        if ($modalidadeId) {
            $query->whereHas('modalidades', function($q) use ($modalidadeId) {
                $q->where('modalidades.id', $modalidadeId);
            });
        }

        $competitors = $query->limit(10)->get();

        return response()->json(['competitors' => $competitors]);
    }

    public function getCompetitorDetails(Competitor $competitor, Request $request)
    {
        $modalidadeId = $request->get('modalidade_id');
        
        $competitor->load(['modalidades' => function($q) use ($modalidadeId) {
            if ($modalidadeId) {
                $q->where('modalidades.id', $modalidadeId);
            }
        }, 'stats']);

        $modalidadeData = null;
        if ($modalidadeId && $competitor->modalidades->isNotEmpty()) {
            $modalidadeData = $competitor->modalidades->first()->pivot;
        }

        return response()->json([
            'competitor' => $competitor,
            'modalidade_data' => $modalidadeData,
            'stats' => $competitor->stats
        ]);
    }

    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'competitor_ids' => 'required|array',
            'competitor_ids.*' => 'exists:competitors,id',
            'modalidade_id' => 'required|exists:modalidades,id',
            'status' => 'required|in:inscrito,confirmado,eliminado'
        ]);

        \App\Models\CompetitorModalidade::where('modalidade_id', $request->modalidade_id)
            ->whereIn('competitor_id', $request->competitor_ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado para ' . count($request->competitor_ids) . ' competidores!',
            'affected_count' => count($request->competitor_ids)
        ]);
    }

    public function updateCompetitorPosition(Request $request, Modalidade $modalidade, Competitor $competitor)
    {
        $request->validate([
            'posicao_final' => 'required|integer|min:1'
        ]);

        \App\Models\CompetitorModalidade::where([
            'competitor_id' => $competitor->id,
            'modalidade_id' => $modalidade->id
        ])->update(['posicao_final' => $request->posicao_final]);

        return response()->json([
            'success' => true,
            'message' => 'Posição atualizada!',
            'position' => $request->posicao_final
        ]);
    }

    public function getModalidadeStats(Modalidade $modalidade)
    {
        $competitors = $modalidade->competitors;
        
        $stats = [
            'total' => $competitors->count(),
            'confirmados' => $competitors->where('pivot.status', 'confirmado')->count(),
            'inscritos' => $competitors->where('pivot.status', 'inscrito')->count(),
            'eliminados' => $competitors->where('pivot.status', 'eliminado')->count(),
            'com_pontuacao' => $competitors->where('pivot.pontuacao', '>', 0)->count(),
            'media_pontuacao' => $competitors->avg('pivot.pontuacao') ?: 0,
            'maior_pontuacao' => $competitors->max('pivot.pontuacao') ?: 0
        ];

        return response()->json([
            'modalidade' => $modalidade,
            'stats' => $stats,
            'ranking' => $competitors->sortByDesc('pivot.pontuacao')->take(5)->values()
        ]);
    }

    public function getRealTimeUpdates(Request $request)
    {
        // Simular atualizações em tempo real
        // Em produção, isso seria integrado com WebSockets
        
        $modalidadeId = $request->get('modalidade_id');
        $lastUpdate = $request->get('last_update', now()->subMinutes(1));

        // Buscar mudanças recentes (simulado)
        $updates = [
            [
                'type' => 'status_change',
                'competitor_id' => 1,
                'competitor_name' => 'João Silva',
                'old_status' => 'inscrito',
                'new_status' => 'confirmado',
                'timestamp' => now()->subSeconds(30)->toISOString()
            ],
            [
                'type' => 'score_update',
                'competitor_id' => 2,
                'competitor_name' => 'Maria Santos',
                'old_score' => 7.5,
                'new_score' => 8.2,
                'timestamp' => now()->subSeconds(10)->toISOString()
            ]
        ];

        return response()->json([
            'updates' => $updates,
            'last_check' => now()->toISOString()
        ]);
    }
}
