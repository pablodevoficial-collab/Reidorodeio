<?php

namespace App\Events;

use App\Jobs\ProcessScoringUpdate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoringUpdated
{
    use Dispatchable, SerializesModels;

    public $competitorId;
    public $modalidadeId;
    public $rodeioId;
    public $pontuacao;
    public $tempo;
    public $action;
    public $competitorName;
    public $timestamp;
    public $operatorName;

    /**
     * Create a new event instance.
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
        $this->timestamp = now()->toISOString();
        $this->operatorName = $data['operator_name'] ?? 'Sistema';

        // Dispatch job for async processing
        ProcessScoringUpdate::dispatch($data);
    }
}
