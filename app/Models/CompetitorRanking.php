<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorRanking extends Model
{
    protected $fillable = [
        'competitor_id',
        'competitor_group_id',
        'ranking_type',
        'rodeio_id',
        'modalidade_id',
        'divisao',
        'year',
        'month',
        'position',
        'previous_position',
        'total_points',
        'points_change',
        'total_actions',
        'positive_actions',
        'negative_actions',
        'efficiency_rate',
        'action_breakdown',
        'calculated_at',
        'events_count',
    ];

    protected $casts = [
        'position' => 'integer',
        'previous_position' => 'integer',
        'total_points' => 'integer',
        'points_change' => 'integer',
        'total_actions' => 'integer',
        'positive_actions' => 'integer',
        'negative_actions' => 'integer',
        'efficiency_rate' => 'decimal:2',
        'action_breakdown' => 'array',
        'calculated_at' => 'datetime',
        'events_count' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Tipos de ranking disponíveis
     */
    public const TYPE_EVENT = 'event';
    public const TYPE_MONTHLY = 'monthly';
    public const TYPE_YEARLY = 'yearly';
    public const TYPE_OVERALL = 'overall';

    /**
     * Competidor deste ranking
     */
    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }

    /**
     * Grupo (dupla/trio) deste ranking
     */
    public function competitorGroup(): BelongsTo
    {
        return $this->belongsTo(ModalidadeCompetitorGroup::class, 'competitor_group_id');
    }

    /**
     * Rodeio deste ranking (para tipo event)
     */
    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class);
    }

    /**
     * Modalidade deste ranking
     */
    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }

    /**
     * Verificar se subiu de posição
     */
    public function hasImproved(): bool
    {
        if ($this->previous_position === null) {
            return false;
        }
        return $this->position < $this->previous_position;
    }

    /**
     * Verificar se caiu de posição
     */
    public function hasDropped(): bool
    {
        if ($this->previous_position === null) {
            return false;
        }
        return $this->position > $this->previous_position;
    }

    /**
     * Obter variação de posição
     */
    public function getPositionChangeAttribute(): int
    {
        if ($this->previous_position === null) {
            return 0;
        }
        return $this->previous_position - $this->position;
    }

    /**
     * Scope para ranking por evento
     */
    public function scopeByEvent($query, int $rodeioId, int $modalidadeId, ?string $divisao = null)
    {
        return $query->where('ranking_type', self::TYPE_EVENT)
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->when($divisao, fn($q) => $q->where('divisao', $divisao));
    }

    /**
     * Scope para ranking mensal
     */
    public function scopeMonthly($query, int $year, int $month)
    {
        return $query->where('ranking_type', self::TYPE_MONTHLY)
            ->where('year', $year)
            ->where('month', $month);
    }

    /**
     * Scope para ranking geral
     */
    public function scopeOverall($query)
    {
        return $query->where('ranking_type', self::TYPE_OVERALL);
    }

    /**
     * Scope ordenado por posição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position', 'asc');
    }

    /**
     * Scope top N posições
     */
    public function scopeTop($query, int $limit = 30)
    {
        return $query->ordered()->limit($limit);
    }
}
