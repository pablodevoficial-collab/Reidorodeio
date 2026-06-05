<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorContextStat extends Model
{
    protected $table = 'competitor_stats';

    protected $fillable = [
        'competitor_id',
        'rodeio_id',
        'modalidade_id',
        'divisao',
        'tipo_fase',
        'is_finalized',
        'last_updated_at',
        'pontuacao_total',
        'last_points',
        'count_negativas_total',
        'count_boa',
        'count_errou_pescoco',
        'count_errou_pata',
        'count_errou_top',
        'count_dobrada',
        'count_cabresteou',
        'count_duas_voltas',
        'count_limpou_garupa',
        'count_cola',
        'count_cola_neg',
        'count_cupim',
        'count_top',
        'count_limpou_cupim_longe',
        'count_pescou',
        'count_garupa_neg',
        'count_uma_aspa',
        'count_por_cima',
        'count_pescou_uma_aspa',
        'count_limpou_top',
        'count_limpou_top_mao',
        'count_boi_tirou',
        'count_boi_pulou',
        'count_queimou_raia',
        'count_caiu_do_cavalo',
        'count_saiu_enrolado',
        'count_custom',
        'points_custom_total',
        'action_counts',
    ];

    protected $casts = [
        'competitor_id' => 'integer',
        'rodeio_id' => 'integer',
        'modalidade_id' => 'integer',
        'divisao' => 'string',
        'tipo_fase' => 'string',
        'is_finalized' => 'boolean',
        'last_updated_at' => 'datetime',
        'pontuacao_total' => 'integer',
        'last_points' => 'integer',
        'count_negativas_total' => 'integer',
        'count_boa' => 'integer',
        'count_errou_pescoco' => 'integer',
        'count_errou_pata' => 'integer',
        'count_errou_top' => 'integer',
        'count_dobrada' => 'integer',
        'count_cabresteou' => 'integer',
        'count_duas_voltas' => 'integer',
        'count_limpou_garupa' => 'integer',
        'count_cola' => 'integer',
        'count_cola_neg' => 'integer',
        'count_cupim' => 'integer',
        'count_top' => 'integer',
        'count_limpou_cupim_longe' => 'integer',
        'count_pescou' => 'integer',
        'count_garupa_neg' => 'integer',
        'count_uma_aspa' => 'integer',
        'count_por_cima' => 'integer',
        'count_pescou_uma_aspa' => 'integer',
        'count_limpou_top' => 'integer',
        'count_limpou_top_mao' => 'integer',
        'count_boi_tirou' => 'integer',
        'count_boi_pulou' => 'integer',
        'count_queimou_raia' => 'integer',
        'count_caiu_do_cavalo' => 'integer',
        'count_saiu_enrolado' => 'integer',
        'count_custom' => 'integer',
        'points_custom_total' => 'integer',
        'action_counts' => 'array',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }
}