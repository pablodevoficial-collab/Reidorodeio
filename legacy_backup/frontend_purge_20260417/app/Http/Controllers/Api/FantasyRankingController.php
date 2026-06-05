<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\FetchLatestFantasyRanking;
use App\Models\Rodeio;
use App\Services\FantasyRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FantasyRankingController extends Controller
{
    /**
     * Flow 1: GET standard (auto cache TTL ~5 min).
     * Never recalculates; reads cache → snapshot fallback.
     */
    public function getRanking(Request $request, FantasyRankingService $service)
    {
        $validated = $request->validate([
            'fantasy_event_id' => 'required|integer|min:1',
            'league_id' => 'required|integer|min:1',
            'view' => 'nullable|in:top30,full',
        ]);

        $fantasyEventId = (int) $validated['fantasy_event_id'];
        $leagueId = (int) $validated['league_id'];
        $view = $validated['view'] ?? 'top30';

        $league = $service->assertLeagueContext($fantasyEventId, $leagueId);
        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Liga inválida para este evento',
            ], 404);
        }

        // Full view requires premium auth.
        $user = $request->user('sanctum');
        $isPremium = $user ? (bool) $user->isPremium() : false;

        if ($view === 'full' && !$isPremium) {
            return response()->json([
                'success' => false,
                'message' => 'Ranking completo requer Premium',
            ], 403);
        }

        $payload = $service->getRankingPayload($fantasyEventId, $leagueId);
        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Ranking não encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service->toView($payload, $view),
        ]);
    }

    /**
     * Flow 2: Manual refresh (user). Queues a lightweight read job.
     */
    public function refresh(Request $request, FantasyRankingService $service)
    {
        $validated = $request->validate([
            'fantasy_event_id' => 'required|integer|min:1',
            'league_id' => 'required|integer|min:1',
            'view' => 'nullable|in:top30,full',
        ]);

        $fantasyEventId = (int) $validated['fantasy_event_id'];
        $leagueId = (int) $validated['league_id'];
        $view = $validated['view'] ?? 'top30';

        $user = $request->user('sanctum');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $league = $service->assertLeagueContext($fantasyEventId, $leagueId);
        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Liga inválida para este evento',
            ], 404);
        }

        // Validate event active (best-effort, schema defensive).
        if (Schema::hasTable('rodeios')) {
            $rodeio = Rodeio::query()->find($fantasyEventId);
            if ($rodeio && Schema::hasColumn('rodeios', 'status_transmissao')) {
                if (($rodeio->status_transmissao ?? '') === 'finalizado') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Evento finalizado',
                    ], 422);
                }
            }
        }

        $isPremium = (bool) $user->isPremium();
        if ($view === 'full' && !$isPremium) {
            return response()->json([
                'success' => false,
                'message' => 'Ranking completo requer Premium',
            ], 403);
        }

        // Per-user rate limit (1 refresh / 15s)
        $key = 'fantasy_ranking_refresh:' . $user->id;
        $maxAttempts = 1;
        $decaySeconds = 15;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Aguarde para atualizar novamente',
                'retry_after' => $retryAfter,
            ], 429);
        }
        RateLimiter::hit($key, $decaySeconds);

        $requestId = (string) Str::uuid();

        FetchLatestFantasyRanking::dispatch(
            $requestId,
            $fantasyEventId,
            $leagueId,
            (int) $user->id,
            $view,
        );

        return response()->json([
            'success' => true,
            'status' => 'queued',
            'request_id' => $requestId,
        ]);
    }

    /**
     * Result endpoint for manual refresh.
     */
    public function result(Request $request, string $requestId, FantasyRankingService $service)
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $key = $service->refreshKey($requestId);
        $data = cache()->get($key);

        if (!$data) {
            return response()->json([
                'success' => true,
                'status' => 'pending',
            ]);
        }

        if (is_array($data) && isset($data['user_id']) && (int) $data['user_id'] !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => 'ready',
            'ranking' => $data['ranking'] ?? null,
        ]);
    }
}
