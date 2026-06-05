<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompetitorRanking;
use App\Services\LiveScoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller para API de rankings em tempo real
 */
class LiveRankingController extends Controller
{
    protected LiveScoringService $scoringService;

    public function __construct(LiveScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Ranking ao vivo por evento (Rodeio + Modalidade)
     * 
     * GET /api/rankings/live/{rodeioId}/{modalidadeId}
     */
    public function liveByEvent(Request $request, int $rodeioId, int $modalidadeId): JsonResponse
    {
        $divisao = $request->query('divisao');
        $limit = min((int) $request->query('limit', 50), 100);

        $ranking = $this->scoringService->getLiveRanking($rodeioId, $modalidadeId, $divisao, $limit);

        return response()->json([
            'success' => true,
            'data' => $ranking,
        ]);
    }

    /**
     * Ranking mensal
     * 
     * GET /api/rankings/monthly/{year}/{month}
     */
    public function monthly(Request $request, int $year, int $month): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 100);

        $ranking = CompetitorRanking::query()
            ->monthly($year, $month)
            ->top($limit)
            ->with('competitor:id,nome,foto,categoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'month' => $month,
                'count' => $ranking->count(),
                'ranking' => $ranking->map(fn($r) => [
                    'position' => $r->position,
                    'previous_position' => $r->previous_position,
                    'competitor' => [
                        'id' => $r->competitor_id,
                        'name' => $r->competitor->nome ?? 'N/A',
                        'photo' => $r->competitor->foto ?? null,
                        'category' => $r->competitor->categoria ?? null,
                    ],
                    'total_points' => $r->total_points,
                    'efficiency_rate' => round($r->efficiency_rate ?? 0, 1),
                    'total_actions' => $r->total_actions,
                ]),
            ],
        ]);
    }

    /**
     * Ranking geral (overall)
     * 
     * GET /api/rankings/overall
     */
    public function overall(Request $request): JsonResponse
    {
        $limit = min((int) $request->query('limit', 50), 100);

        $ranking = CompetitorRanking::query()
            ->overall()
            ->top($limit)
            ->with('competitor:id,nome,foto,categoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $ranking->count(),
                'ranking' => $ranking->map(fn($r) => [
                    'position' => $r->position,
                    'previous_position' => $r->previous_position,
                    'competitor' => [
                        'id' => $r->competitor_id,
                        'name' => $r->competitor->nome ?? 'N/A',
                        'photo' => $r->competitor->foto ?? null,
                        'category' => $r->competitor->categoria ?? null,
                    ],
                    'total_points' => $r->total_points,
                    'events_count' => $r->events_count,
                ]),
            ],
        ]);
    }

    /**
     * Histórico de pontuação de um competidor
     * 
     * GET /api/rankings/competitor/{competitorId}/history
     */
    public function competitorHistory(Request $request, int $competitorId): JsonResponse
    {
        $rodeioId = $request->query('rodeio_id') ? (int) $request->query('rodeio_id') : null;
        $limit = min((int) $request->query('limit', 50), 100);

        $history = $this->scoringService->getCompetitorHistory($competitorId, $rodeioId, $limit);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Tabela de pontuação (referência)
     * 
     * GET /api/rankings/points-table
     */
    public function pointsTable(): JsonResponse
    {
        $table = LiveScoringService::getPointsTable();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => count($table),
                'points_table' => $table,
            ],
        ]);
    }

    /**
     * Breakdown de ações de um competidor em um evento
     * 
     * GET /api/rankings/competitor/{competitorId}/breakdown
     */
    public function competitorBreakdown(Request $request, int $competitorId): JsonResponse
    {
        $request->validate([
            'rodeio_id' => 'required|integer',
            'modalidade_id' => 'required|integer',
        ]);

        $rodeioId = (int) $request->query('rodeio_id');
        $modalidadeId = (int) $request->query('modalidade_id');
        $divisao = $request->query('divisao');

        $ranking = CompetitorRanking::query()
            ->where('competitor_id', $competitorId)
            ->byEvent($rodeioId, $modalidadeId, $divisao)
            ->with('competitor:id,nome,foto')
            ->first();

        if (!$ranking) {
            return response()->json([
                'success' => false,
                'error' => 'Competidor não encontrado neste evento',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'competitor' => [
                    'id' => $ranking->competitor_id,
                    'name' => $ranking->competitor->nome ?? 'N/A',
                    'photo' => $ranking->competitor->foto ?? null,
                ],
                'position' => $ranking->position,
                'total_points' => $ranking->total_points,
                'efficiency_rate' => round($ranking->efficiency_rate ?? 0, 1),
                'total_actions' => $ranking->total_actions,
                'positive_actions' => $ranking->positive_actions,
                'negative_actions' => $ranking->negative_actions,
                'action_breakdown' => $ranking->action_breakdown,
                'calculated_at' => $ranking->calculated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Comparar dois competidores
     * 
     * GET /api/rankings/compare
     */
    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'competitor_a' => 'required|integer',
            'competitor_b' => 'required|integer',
            'rodeio_id' => 'nullable|integer',
            'modalidade_id' => 'nullable|integer',
        ]);

        $competitorA = (int) $request->query('competitor_a');
        $competitorB = (int) $request->query('competitor_b');
        $rodeioId = $request->query('rodeio_id') ? (int) $request->query('rodeio_id') : null;
        $modalidadeId = $request->query('modalidade_id') ? (int) $request->query('modalidade_id') : null;

        $query = CompetitorRanking::query();

        if ($rodeioId && $modalidadeId) {
            $query->byEvent($rodeioId, $modalidadeId);
        } else {
            $query->overall();
        }

        $rankingA = (clone $query)->where('competitor_id', $competitorA)->with('competitor:id,nome,foto')->first();
        $rankingB = (clone $query)->where('competitor_id', $competitorB)->with('competitor:id,nome,foto')->first();

        if (!$rankingA || !$rankingB) {
            return response()->json([
                'success' => false,
                'error' => 'Um ou ambos competidores não encontrados',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'context' => $rodeioId ? 'event' : 'overall',
                'competitor_a' => $this->formatRankingForComparison($rankingA),
                'competitor_b' => $this->formatRankingForComparison($rankingB),
                'winner' => $rankingA->total_points > $rankingB->total_points 
                    ? 'a' 
                    : ($rankingB->total_points > $rankingA->total_points ? 'b' : 'tie'),
                'points_difference' => abs($rankingA->total_points - $rankingB->total_points),
            ],
        ]);
    }

    /**
     * Formatar ranking para comparação
     */
    private function formatRankingForComparison(CompetitorRanking $ranking): array
    {
        return [
            'competitor' => [
                'id' => $ranking->competitor_id,
                'name' => $ranking->competitor->nome ?? 'N/A',
                'photo' => $ranking->competitor->foto ?? null,
            ],
            'position' => $ranking->position,
            'total_points' => $ranking->total_points,
            'efficiency_rate' => round($ranking->efficiency_rate ?? 0, 1),
            'positive_actions' => $ranking->positive_actions,
            'negative_actions' => $ranking->negative_actions,
            'action_breakdown' => $ranking->action_breakdown,
        ];
    }
}
