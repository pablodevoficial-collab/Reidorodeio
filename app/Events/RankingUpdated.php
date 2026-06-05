<?php

namespace App\Events;

use App\Jobs\ProcessRankingUpdate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RankingUpdated
{
    use Dispatchable, SerializesModels;

    public $modalidadeId;
    public $rodeioId;
    public $ranking;
    public $statistics;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->modalidadeId = $data['modalidade_id'];
        $this->rodeioId = $data['rodeio_id'] ?? null;
        $this->ranking = $data['ranking'];
        $this->statistics = $data['statistics'] ?? [];
        $this->timestamp = now()->toISOString();

        // Dispatch job for async processing
        ProcessRankingUpdate::dispatch($data);
    }
}
