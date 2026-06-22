<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\X1StatsService;
use Illuminate\Support\Facades\Log;

class ProcessX1RankingUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $modalidadeId;
    public string $type;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $modalidadeId = null, string $type = 'alltime')
    {
        $this->modalidadeId = $modalidadeId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $statsService = app(X1StatsService::class);
            
            // Gerar snapshot global
            $statsService->generateRankingSnapshot($this->type, null);
            
            // Se tem modalidade específica, gerar também
            if ($this->modalidadeId) {
                $statsService->generateRankingSnapshot($this->type, $this->modalidadeId);
            }

            Log::info('X1 Ranking update processed', [
                'type' => $this->type,
                'modalidade_id' => $this->modalidadeId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process X1 ranking update', [
                'error' => $e->getMessage(),
                'type' => $this->type,
                'modalidade_id' => $this->modalidadeId,
            ]);
            throw $e;
        }
    }
}
