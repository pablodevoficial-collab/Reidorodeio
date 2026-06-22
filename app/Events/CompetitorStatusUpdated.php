<?php

namespace App\Events;

use App\Jobs\ProcessCompetitorUpdate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetitorStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $competitorId;
    public $competitorName;
    public $modalidadeId;
    public $rodeioId;
    public $oldStatus;
    public $newStatus;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->competitorId = $data['competitor_id'];
        $this->competitorName = $data['competitor_name'];
        $this->modalidadeId = $data['modalidade_id'] ?? null;
        $this->rodeioId = $data['rodeio_id'] ?? null;
        $this->oldStatus = $data['old_status'];
        $this->newStatus = $data['new_status'];
        $this->timestamp = now()->toISOString();

        // Dispatch job for async processing
        ProcessCompetitorUpdate::dispatch($data);
    }
}
