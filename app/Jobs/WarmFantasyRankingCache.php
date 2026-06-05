<?php

namespace App\Jobs;

use App\Models\FantasyLeague;
use App\Services\FantasyRankingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WarmFantasyRankingCache implements ShouldQueue
{
    use Queueable;

    public function handle(FantasyRankingService $service): void
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            return;
        }

        try {
            $leagues = FantasyLeague::query()
                ->where('is_active', true)
                ->whereNotNull('rodeio_id')
                ->get(['id', 'rodeio_id']);

            foreach ($leagues as $league) {
                try {
                    $service->getRankingPayload((int) $league->rodeio_id, (int) $league->id);
                } catch (\Throwable $e) {
                    Log::warning('Warm fantasy ranking failed for league', [
                        'league_id' => $league->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WarmFantasyRankingCache failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
