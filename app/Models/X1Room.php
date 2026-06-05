<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class X1Room extends Model
{
    protected $table = 'x1_rooms';
    
    protected $fillable = [
        'host_user_id',
        'opponent_user_id',
        'name',
        'description',
        'rodeio_id',
        'modalidade_id',
        'divisao',
        'competitor_id',
        'competitor_group_id',
        'valor_entrada',
        'status',
        'is_private',
        'expires_at',
        'finished_at'
    ];

    protected $casts = [
        'valor_entrada' => 'decimal:2',
        'expires_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_private' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Get the competitor that owns the X1 room.
     */
    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }

    /**
     * Get the competitor group that owns the X1 room.
     */
    public function competitorGroup(): BelongsTo
    {
        return $this->belongsTo(ModalidadeCompetitorGroup::class, 'competitor_group_id');
    }

    /**
     * Get the modalidade that owns the X1 room.
     */
    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }

    /**
     * Get the rodeio that owns the X1 room.
     */
    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class);
    }

    /**
     * Get the host user (creator) of the room.
     */
    public function hostUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Get the opponent user (joiner) of the room.
     */
    public function opponentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_user_id');
    }
}
