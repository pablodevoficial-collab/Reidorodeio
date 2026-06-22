<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitorScoringLog extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'competitor_id',
        'rodeio_id',
        'modalidade_id',
        'action_type',
        'action_category',
        'points',
        'total_score_before',
        'total_score_after',
        'event_phase',
        'notes',
        'scored_at',
        'scored_by',
        'metadata'
    ];

    protected $casts = [
        'points' => 'integer',
        'total_score_before' => 'integer',
        'total_score_after' => 'integer',
        'scored_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get the competitor that this log belongs to.
     */
    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }

    /**
     * Get the rodeio that this log belongs to.
     */
    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class);
    }

    /**
     * Get the modalidade that this log belongs to.
     */
    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }

    /**
     * Scope to filter by action category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('action_category', $category);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('scored_at', [$startDate, $endDate]);
    }

    /**
     * Get action description in Portuguese.
     */
    public function getActionDescriptionAttribute()
    {
        $descriptions = [
            'boa' => 'Boa (+50)',
            'errou_pescoco' => 'Errou: Pescoço (-100)',
            'dobrada' => 'Dobrada (-150)',
            'cabresteou' => 'Cabresteou (-200)',
            'duas_voltas' => 'Duas Voltas (-250)',
            'limpou_garupa' => 'Limpou: Garupa (+200)',
            'cola' => 'Cola (+100)',
            'cupim' => 'Cupim (+200)',
            'top' => 'Top (+500)',
            'pescou' => 'Pescou (+200)'
        ];

        return $descriptions[$this->action_type] ?? $this->action_type;
    }
}
