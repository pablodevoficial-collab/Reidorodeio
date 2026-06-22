<?php

namespace App\Jobs;

use App\Models\FantasyLeague;
use App\Services\FantasyScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProcessFantasyScoringUpdate implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $competitorId,
        public int $modalidadeId,
        public ?int $rodeioId = null,
    ) {
    }

    public function handle(FantasyScoringService $service): void
    {
        if (!$this->rodeioId) {
            return;
        }

        if (!Schema::hasTable('fantasy_leagues')) {
            return;
        }

        try {
            $leagues = FantasyLeague::query()
                ->where('is_active', true)
                ->where('rodeio_id', (int) $this->rodeioId)
                ->where('modalidade_id', (int) $this->modalidadeId)
                ->get();

            foreach ($leagues as $league) {
                try {
                    $service->recalculateLeague($league, $this->competitorId);
                } catch (\Throwable $e) {
                    Log::warning('Fantasy recalculation failed for league', [
                        'league_id' => $league->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Fantasy recalculation failed', [
                'error' => $e->getMessage(),
                'rodeio_id' => $this->rodeioId,
                'modalidade_id' => $this->modalidadeId,
                'competitor_id' => $this->competitorId,
            ]);
        }
    }
}
