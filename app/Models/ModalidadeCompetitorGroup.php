<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModalidadeCompetitorGroup extends Model
{
    protected $table = 'modalidade_competitor_groups';

    protected $fillable = [
        'modalidade_id',
        'divisao',
        'nome',
        'tamanho',
        'status',
    ];

    protected $casts = [
        'tamanho' => 'integer',
    ];

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class, 'modalidade_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            Competitor::class,
            'modalidade_competitor_group_members',
            'group_id',
            'competitor_id'
        )->withTimestamps();
    }
}
