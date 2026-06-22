<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\X1StatsService;
use App\Models\X1RoomInstance;
use App\Models\X1Result;
use App\Models\UserX1Stat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class X1RankingController extends Controller
{
    protected X1StatsService $statsService;

    public function __construct(X1StatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * GET /api/x1/rankings/top30
     * Ranking público - Top 30
     */
    public function top30(Request $request): JsonResponse
    {
        $modalidadeId = $request->query('modalidade_id');
        $type = $request->query('type', 'alltime');

        $ranking = $this->statsService->getTopN(
            30,
            $type,
            $modalidadeId ? (int) $modalidadeId : null
        );

        return response()->json([
            'success' => true,
            'data' => $ranking,
            'meta' => [
                'type' => $type,
                'modalidade_id' => $modalidadeId,
                'total' => count($ranking),
                'is_premium_required' => false,
            ],
        ]);
    }

    /**
     * GET /api/x1/rankings/full
     * Ranking completo - Premium only
     */
    public function full(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Verificar se é premium
        if (!$user || !$user->isPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Ranking completo disponível apenas para usuários Premium',
                'is_premium_required' => true,
            ], 403);
        }

        $modalidadeId = $request->query('modalidade_id');
        $type = $request->query('type', 'alltime');

        $ranking = $this->statsService->getRanking(
            $type,
            $modalidadeId ? (int) $modalidadeId : null
        );

        return response()->json([
            'success' => true,
            'data' => $ranking,
            'meta' => [
                'type' => $type,
                'modalidade_id' => $modalidadeId,
                'total' => count($ranking),
                'is_premium_required' => true,
            ],
        ]);
    }

    /**
     * GET /api/x1/stats/me
     * Estatísticas do usuário logado
     */
    public function myStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado',
            ], 401);
        }

        $modalidadeId = $request->query('modalidade_id');
        
        $stats = $this->statsService->getUserStats(
            $user->id,
            $modalidadeId ? (int) $modalidadeId : null
        );

        if (!$stats) {
            // Retornar estatísticas zeradas para novos usuários
            $stats = [
                'total_x1s' => 0,
                'wins' => 0,
                'losses' => 0,
                'draws' => 0,
                'win_rate' => 0,
                'total_prize_won' => 0,
                'total_invested' => 0,
                'profit' => 0,
                'current_streak' => 0,
                'best_win_streak' => 0,
                'worst_loss_streak' => 0,
                'rating' => 1000,
                'peak_rating' => 1000,
                'last_x1_at' => null,
                'ranking_position' => null,
                'total_ranked_players' => 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->firstname ?? $user->username,
            ],
        ]);
    }

    /**
     * GET /api/x1/stats/{userId}
     * Estatísticas de um usuário específico
     */
    public function userStats(Request $request, int $userId): JsonResponse
    {
        $modalidadeId = $request->query('modalidade_id');
        
        $stats = $this->statsService->getUserStats(
            $userId,
            $modalidadeId ? (int) $modalidadeId : null
        );

        if (!$stats) {
            return response()->json([
                'success' => false,
                'message' => 'Estatísticas não encontradas para este usuário',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * GET /api/x1/history/me
     * Histórico de X1s do usuário logado
     */
    public function myHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado',
            ], 401);
        }

        $perPage = min(50, (int) $request->query('per_page', 10));

        // Buscar resultados onde o usuário participou
        $results = X1Result::query()
            ->with(['room.modalidade', 'room.competitor', 'room.competitorGroup.members', 'winner'])
            ->whereHas('room.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('processed_at')
            ->paginate($perPage);

        $history = $results->map(function ($result) use ($user) {
            $room = $result->room;
            $isWinner = $result->winner_user_id === $user->id;
            
            return [
                'id' => $result->id,
                'room_id' => $room->id,
                'room_name' => $room->name,
                'modalidade' => $room->modalidade?->nome,
                'competitor' => $room->competitor?->nome,
                'competitor_group' => $room->competitorGroup?->nome,
                'valor_entrada' => $room->valor_entrada,
                'prize_total' => $room->prize_total,
                'is_winner' => $isWinner,
                'result' => $isWinner ? 'victory' : 'defeat',
                'profit' => $isWinner ? ($room->prize_total - $room->valor_entrada) : -$room->valor_entrada,
                'processed_at' => $result->processed_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $history,
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * GET /api/x1/active/me
     * Salas ativas do usuário
     */
    public function myActiveRooms(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado',
            ], 401);
        }

        $rooms = X1RoomInstance::query()
            ->with(['modalidade', 'competitor', 'competitorGroup.members', 'participants.user'])
            ->where(function ($q) use ($user) {
                $q->where('host_user_id', $user->id)
                  ->orWhereHas('participants', function ($pq) use ($user) {
                      $pq->where('user_id', $user->id);
                  });
            })
            ->whereIn('status', ['pending_payment', 'open', 'in_progress'])
            ->orderByDesc('created_at')
            ->get();

        $data = $rooms->map(function ($room) use ($user) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'status' => $room->status,
                'is_host' => $room->host_user_id === $user->id,
                'modalidade' => $room->modalidade?->nome,
                'competitor' => $room->competitor?->nome,
                'valor_entrada' => $room->valor_entrada,
                'prize_total' => $room->prize_total,
                'participants_count' => $room->participants->count(),
                'expires_at' => $room->expires_at?->toIso8601String(),
                'created_at' => $room->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
