<?php

namespace App\Jobs;

use App\Services\FantasyRankingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchLatestFantasyRanking implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $requestId,
        public int $fantasyEventId,
        public int $leagueId,
        public int $userId,
        public string $view = 'top30',
    ) {
    }

    public function handle(FantasyRankingService $service): void
    {
        try {
            $payload = $service->getRankingPayload($this->fantasyEventId, $this->leagueId);

            if (!$payload) {
                Cache::put($service->refreshKey($this->requestId), [
                    'status' => 'ready',
                    'user_id' => $this->userId,
                    'fantasy_event_id' => $this->fantasyEventId,
                    'league_id' => $this->leagueId,
                    'view' => $this->view,
                    'ranking' => null,
                ], now()->addSeconds(FantasyRankingService::REFRESH_TTL_SECONDS));

                return;
            }

            Cache::put($service->refreshKey($this->requestId), [
                'status' => 'ready',
                'user_id' => $this->userId,
                'fantasy_event_id' => $this->fantasyEventId,
                'league_id' => $this->leagueId,
                'view' => $this->view,
                'ranking' => $service->toView($payload, $this->view),
            ], now()->addSeconds(FantasyRankingService::REFRESH_TTL_SECONDS));
        } catch (\Throwable $e) {
            // Never break the queue for realtime refresh.
            Log::warning('FetchLatestFantasyRanking failed', [
                'request_id' => $this->requestId,
                'league_id' => $this->leagueId,
                'fantasy_event_id' => $this->fantasyEventId,
                'error' => $e->getMessage(),
            ]);

            try {
                Cache::put($service->refreshKey($this->requestId), [
                    'status' => 'ready',
                    'user_id' => $this->userId,
                    'fantasy_event_id' => $this->fantasyEventId,
                    'league_id' => $this->leagueId,
                    'view' => $this->view,
                    'ranking' => null,
                    'error' => 'refresh_failed',
                ], now()->addSeconds(FantasyRankingService::REFRESH_TTL_SECONDS));
            } catch (\Throwable $ignored) {
                // ignore
            }
        }
    }
}
