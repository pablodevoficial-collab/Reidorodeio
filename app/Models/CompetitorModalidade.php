<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitorModalidade extends Model
{
    protected $table = 'competitor_modalidade';

    protected $fillable = [
        'competitor_id',
        'modalidade_id',
        'status',
        'numero_participacao',
        'multiplicador_atual',
        'disponivel_participacao',
        'dados_especificos',
        'observacoes',
        'posicao_final',
    ];

    protected $casts = [
        'disponivel_participacao' => 'boolean',
        'dados_especificos' => 'array',
        'multiplicador_atual' => 'decimal:2',
    ];
}
