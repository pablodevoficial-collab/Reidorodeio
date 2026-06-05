<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorFollowEvent extends Model
{
    protected $fillable = [
        'competitor_id',
        'event_type',
        'title',
        'message',
        'cta_label',
        'cta_url',
        'source_key',
        'metadata',
        'rodeio_id',
        'modalidade_id',
        'fantasy_league_id',
        'scoring_log_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }

    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class);
    }

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }

    public function fantasyLeague(): BelongsTo
    {
        return $this->belongsTo(FantasyLeague::class);
    }

    public function scoringLog(): BelongsTo
    {
        return $this->belongsTo(CompetitorScoringLog::class);
    }
}
