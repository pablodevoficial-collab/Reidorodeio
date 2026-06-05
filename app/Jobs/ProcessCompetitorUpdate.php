<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessCompetitorUpdate implements ShouldQueue
{
    use Queueable;

    public $competitorId;
    public $competitorName;
    public $modalidadeId;
    public $rodeioId;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->competitorId = $data['competitor_id'];
        $this->competitorName = $data['competitor_name'];
        $this->modalidadeId = $data['modalidade_id'] ?? null;
        $this->rodeioId = $data['rodeio_id'] ?? null;
        $this->oldStatus = $data['old_status'];
        $this->newStatus = $data['new_status'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $competitorData = [
                'competitor_id' => $this->competitorId,
                'competitor_name' => $this->competitorName,
                'modalidade_id' => $this->modalidadeId,
                'rodeio_id' => $this->rodeioId,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus,
                'timestamp' => now()->toISOString()
            ];

            // Atualizar status do competidor
            $this->updateCompetitorStatus($competitorData);

            // Atualizar estatísticas em tempo real
            $this->updateRealTimeStats();

            Log::info('Competitor status update processed', [
                'competitor_id' => $this->competitorId,
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process competitor update', [
                'error' => $e->getMessage(),
                'competitor_id' => $this->competitorId,
                'data' => $competitorData ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar status do competidor
     */
    private function updateCompetitorStatus($data)
    {
        $cacheKey = "competitor_status_{$this->competitorId}";
        Cache::put($cacheKey, $data, now()->addHours(24));

        // Atualizar lista de competidores por modalidade
        if ($this->modalidadeId) {
            $modalidadeKey = "modalidade_{$this->modalidadeId}_competitors";
            $competitors = Cache::get($modalidadeKey, []);
            $competitors[$this->competitorId] = $data;
            Cache::put($modalidadeKey, $competitors, now()->addHours(24));
        }

        // Atualizar lista de competidores por rodeio
        if ($this->rodeioId) {
            $rodeioKey = "rodeio_{$this->rodeioId}_competitors";
            $competitors = Cache::get($rodeioKey, []);
            $competitors[$this->competitorId] = $data;
            Cache::put($rodeioKey, $competitors, now()->addHours(24));
        }
    }

    /**
     * Atualizar estatísticas em tempo real
     */
    private function updateRealTimeStats()
    {
        $statsKey = 'realtime_stats';

        // Contar competidores por status
        $statusCounts = [
            'active' => 0,
            'inactive' => 0,
            'disqualified' => 0,
            'finished' => 0
        ];

        // Buscar todos os competidores ativos
        $activeCompetitors = Cache::get('active_competitors', []);

        foreach ($activeCompetitors as $competitor) {
            $status = $competitor['new_status'] ?? 'inactive';
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        $stats = Cache::get($statsKey, []);
        $stats['competitor_status_counts'] = $statusCounts;
        $stats['last_update'] = now()->toISOString();

        Cache::put($statsKey, $stats, now()->addHours(24));
    }
}
