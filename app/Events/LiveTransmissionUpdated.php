<?php

namespace App\Events;

use App\Jobs\ProcessLiveTransmission;
use App\Jobs\ProcessX1Result;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class LiveTransmissionUpdated
{
    use Dispatchable, SerializesModels;

    public $rodeioId;
    public $status;
    public $modalidadeAtual;
    public $streamUrl;
    public $viewersCount;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->rodeioId = $data['rodeio_id'] ?? null;
        $this->status = $data['status'] ?? 'updated';
        $this->modalidadeAtual = $data['modalidade_atual'] ?? null;
        $this->streamUrl = $data['stream_url'] ?? null;
        $this->viewersCount = $data['viewers_count'] ?? 0;
        $this->timestamp = now()->toISOString();

        if (!$this->rodeioId) {
            Log::warning('LiveTransmissionUpdated received payload without rodeio_id', [
                'payload' => $data,
            ]);
            return;
        }

        // Processa sincrono para não depender de worker de fila em produção compartilhada.
        // Isso garante atualização imediata da cache usada pelo frontend.
        try {
            Bus::dispatchSync(new ProcessLiveTransmission($data));
        } catch (\Throwable $e) {
            Log::error('LiveTransmissionUpdated sync dispatch failed', [
                'rodeio_id' => $this->rodeioId,
                'status' => $this->status,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
