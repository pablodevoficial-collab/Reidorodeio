<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessLiveTransmission implements ShouldQueue
{
    use Queueable;

    public $rodeioId;
    public $status;
    public $modalidadeAtual;
    public $streamUrl;
    public $viewersCount;
    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->rodeioId = $data['rodeio_id'] ?? null;
        $this->status = $data['status'] ?? 'updated';
        $this->modalidadeAtual = $data['modalidade_atual'] ?? null;
        $this->streamUrl = $data['stream_url'] ?? null;
        $this->viewersCount = $data['viewers_count'] ?? 0;
        $this->message = $data['message'] ?? null;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->rodeioId) {
            Log::warning('ProcessLiveTransmission skipped: missing rodeio_id');
            return;
        }

        try {
            $transmissionData = [
                'rodeio_id' => $this->rodeioId,
                'status' => $this->status,
                'modalidade_atual' => $this->modalidadeAtual,
                'stream_url' => $this->streamUrl,
                'viewers_count' => $this->viewersCount,
                'message' => $this->message,
                'timestamp' => now()->toISOString()
            ];

            // Armazenar status de transmissão atual
            $this->updateTransmissionStatus($transmissionData);

            // Registrar no log de transmissões
            $this->logTransmissionEvent($transmissionData);

            Log::info('Live transmission update processed', [
                'rodeio_id' => $this->rodeioId,
                'status' => $this->status,
                'viewers_count' => $this->viewersCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process live transmission update', [
                'error' => $e->getMessage(),
                'rodeio_id' => $this->rodeioId,
                'data' => $transmissionData ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar status de transmissão atual
     */
    private function updateTransmissionStatus($data)
    {
        $cacheKey = "live_transmission_{$this->rodeioId}";
        Cache::put($cacheKey, $data, now()->addHours(24));

        // Atualizar lista de transmissões ativas
        $activeKey = 'active_transmissions';
        $activeTransmissions = Cache::get($activeKey, []);
        $normalizedStatus = strtolower((string) $this->status);
        $activeStatuses = ['started', 'live', 'ao_vivo', 'pausado', 'programado', 'classificatoria', 'em_apuracao', 'inicio_finais'];
        $inactiveStatuses = ['ended', 'stopped', 'finalizado', 'divisao_finalizada', 'finished', 'closed'];

        if (in_array($normalizedStatus, $activeStatuses, true)) {
            $activeTransmissions[$this->rodeioId] = $data;
        } elseif (in_array($normalizedStatus, $inactiveStatuses, true)) {
            unset($activeTransmissions[$this->rodeioId]);
        } else {
            // Status desconhecido: mantém na lista, atualizando payload.
            $activeTransmissions[$this->rodeioId] = $data;
        }

        Cache::put($activeKey, $activeTransmissions, now()->addHours(24));
    }

    /**
     * Registrar evento de transmissão no log
     */
    private function logTransmissionEvent($data)
    {
        $logKey = "transmission_log_{$this->rodeioId}";
        $logs = Cache::get($logKey, []);

        // Adicionar novo evento no início
        array_unshift($logs, $data);

        // Manter apenas os últimos 100 eventos
        $logs = array_slice($logs, 0, 100);

        Cache::put($logKey, $logs, now()->addDays(7));
    }
}
