<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingSnapshot extends Model
{
    protected $fillable = [
        'rodeio_id',
        'modalidade_id',
        'payload',
        'generated_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'generated_at' => 'datetime',
    ];

    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class, 'rodeio_id');
    }

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class, 'modalidade_id');
    }
}
