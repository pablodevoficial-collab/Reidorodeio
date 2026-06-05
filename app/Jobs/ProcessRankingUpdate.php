<?php

namespace App\Jobs;

use App\Models\RankingSnapshot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProcessRankingUpdate implements ShouldQueue
{
    use Queueable;

    public $modalidadeId;
    public $rodeioId;
    public $ranking;
    public $statistics;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->modalidadeId = $data['modalidade_id'];
        $this->rodeioId = $data['rodeio_id'] ?? null;
        $this->ranking = $data['ranking'];
        $this->statistics = $data['statistics'] ?? [];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $rankingData = [
                'modalidade_id' => $this->modalidadeId,
                'rodeio_id' => $this->rodeioId,
                'ranking' => $this->ranking,
                'statistics' => $this->statistics,
                'timestamp' => now()->toISOString()
            ];

            // Persistir snapshot (se a tabela existir)
            if (Schema::hasTable('ranking_snapshots')) {
                RankingSnapshot::create([
                    'modalidade_id' => $this->modalidadeId,
                    'rodeio_id' => $this->rodeioId,
                    'payload' => $rankingData,
                    'generated_at' => now(),
                ]);
            }

            // Atualizar ranking da modalidade
            $this->updateRanking($rankingData);

            // Atualizar estatísticas globais
            $this->updateGlobalStats();

            Log::info('Ranking update processed', [
                'modalidade_id' => $this->modalidadeId,
                'rodeio_id' => $this->rodeioId,
                'ranking_count' => count($this->ranking)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process ranking update', [
                'error' => $e->getMessage(),
                'modalidade_id' => $this->modalidadeId,
                'data' => $rankingData ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar ranking da modalidade
     */
    private function updateRanking($data)
    {
        $rankingKey = "ranking_modalidade_{$this->modalidadeId}";
        Cache::put($rankingKey, $data, now()->addHours(24));

        // Atualizar ranking por rodeio se especificado
        if ($this->rodeioId) {
            $rodeioRankingKey = "ranking_rodeio_{$this->rodeioId}_modalidade_{$this->modalidadeId}";
            Cache::put($rodeioRankingKey, $data, now()->addHours(24));
        }

        // Atualizar cache de rankings globais
        $globalRankingsKey = 'global_rankings';
        $globalRankings = Cache::get($globalRankingsKey, []);
        $globalRankings[$this->modalidadeId] = $data;
        Cache::put($globalRankingsKey, $globalRankings, now()->addHours(24));
    }

    /**
     * Atualizar estatísticas globais
     */
    private function updateGlobalStats()
    {
        $statsKey = 'realtime_stats';
        $stats = Cache::get($statsKey, []);

        // Calcular estatísticas dos rankings
        $rankingStats = [
            'total_competitors_ranked' => count($this->ranking),
            'top_score' => !empty($this->ranking) ? $this->ranking[0]['score'] ?? 0 : 0,
            'average_score' => $this->calculateAverageScore(),
            'last_ranking_update' => now()->toISOString()
        ];

        $stats['ranking_stats'] = $rankingStats;
        $stats['last_update'] = now()->toISOString();

        Cache::put($statsKey, $stats, now()->addHours(24));
    }

    /**
     * Calcular pontuação média do ranking
     */
    private function calculateAverageScore()
    {
        if (empty($this->ranking)) {
            return 0;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($this->ranking as $competitor) {
            if (isset($competitor['score'])) {
                $totalScore += $competitor['score'];
                $count++;
            }
        }

        return $count > 0 ? round($totalScore / $count, 2) : 0;
    }
}
