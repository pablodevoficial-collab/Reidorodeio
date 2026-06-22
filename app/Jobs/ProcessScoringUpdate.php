<?php

namespace App\Jobs;

use App\Jobs\ProcessFantasyScoringUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessScoringUpdate implements ShouldQueue
{
    use Queueable;

    public $competitorId;
    public $modalidadeId;
    public $rodeioId;
    public $pontuacao;
    public $tempo;
    public $action;
    public $competitorName;
    public $operatorName;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->competitorId = $data['competitor_id'];
        $this->modalidadeId = $data['modalidade_id'];
        $this->rodeioId = $data['rodeio_id'] ?? null;
        $this->pontuacao = $data['pontuacao'];
        $this->tempo = $data['tempo'] ?? null;
        $this->action = $data['action'];
        $this->competitorName = $data['competitor_name'];
        $this->operatorName = $data['operator_name'] ?? 'Sistema';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $updateData = [
                'competitor_id' => $this->competitorId,
                'competitor_name' => $this->competitorName,
                'modalidade_id' => $this->modalidadeId,
                'rodeio_id' => $this->rodeioId,
                'pontuacao' => $this->pontuacao,
                'tempo' => $this->tempo,
                'action' => $this->action,
                'timestamp' => now()->toISOString(),
                'operator' => $this->operatorName
            ];

            // Armazenar no cache para recuperação via polling/SSE
            $this->storeScoringUpdate($updateData);

            // Atualizar estatísticas em tempo real
            $this->updateRealTimeStats($updateData);

            // Automatizar Fantasy (recalcular times afetados + snapshot ranking) sem bloquear writes
            try {
                if ($this->rodeioId) {
                    ProcessFantasyScoringUpdate::dispatch(
                        competitorId: (int) $this->competitorId,
                        modalidadeId: (int) $this->modalidadeId,
                        rodeioId: (int) $this->rodeioId,
                    );
                }
            } catch (\Throwable $e) {
                // não bloqueia
            }

            Log::info('Scoring update processed successfully', [
                'competitor_id' => $this->competitorId,
                'modalidade_id' => $this->modalidadeId,
                'action' => $this->action
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process scoring update', [
                'error' => $e->getMessage(),
                'competitor_id' => $this->competitorId,
                'data' => $updateData ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Armazenar atualização de pontuação no cache
     */
    private function storeScoringUpdate($data)
    {
        $cacheKey = "scoring_updates_{$this->modalidadeId}";
        $updates = Cache::get($cacheKey, []);

        // Adicionar nova atualização no início do array
        array_unshift($updates, $data);

        // Manter apenas as últimas 50 atualizações
        $updates = array_slice($updates, 0, 50);

        Cache::put($cacheKey, $updates, now()->addHours(24));

        // Também armazenar por rodeio se aplicável
        if ($this->rodeioId) {
            $rodeioCacheKey = "scoring_updates_rodeio_{$this->rodeioId}";
            $rodeioUpdates = Cache::get($rodeioCacheKey, []);
            array_unshift($rodeioUpdates, $data);
            $rodeioUpdates = array_slice($rodeioUpdates, 0, 50);
            Cache::put($rodeioCacheKey, $rodeioUpdates, now()->addHours(24));
        }
    }

    /**
     * Atualizar estatísticas em tempo real
     */
    private function updateRealTimeStats($data)
    {
        // Atualizar estatísticas da modalidade
        $statsKey = "modalidade_stats_{$this->modalidadeId}";
        $stats = Cache::get($statsKey, [
            'total_updates' => 0,
            'last_update' => null,
            'active_competitors' => []
        ]);

        $stats['total_updates']++;
        $stats['last_update'] = now()->toISOString();

        // Rastrear competidores ativos
        if (!in_array($this->competitorId, $stats['active_competitors'])) {
            $stats['active_competitors'][] = $this->competitorId;
        }

        Cache::put($statsKey, $stats, now()->addHours(24));

        // Atualizar estatísticas globais
        $globalStatsKey = 'global_scoring_stats';
        $globalStats = Cache::get($globalStatsKey, [
            'total_updates_today' => 0,
            'last_update' => null,
            'active_modalidades' => []
        ]);

        $globalStats['total_updates_today']++;
        $globalStats['last_update'] = now()->toISOString();

        if (!in_array($this->modalidadeId, $globalStats['active_modalidades'])) {
            $globalStats['active_modalidades'][] = $this->modalidadeId;
        }

        Cache::put($globalStatsKey, $globalStats, now()->addDay());
    }
}
