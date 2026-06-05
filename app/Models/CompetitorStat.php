<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorStat extends Model
{
    protected $table = 'competitor_stats_global';

    protected $fillable = [
        'competitor_id',
        'vitorias',
        'derrotas', 
        'empates',
        'aproveitamento',
    'pontuacao_media',
    'pontuacao_total',
        'last_points',
    // contadores por ação
    'count_boa',
    'count_negativas_total',
    'count_errou_pescoco',
    'count_dobrada',
    'count_cabresteou',
    'count_duas_voltas',
    'count_limpou_garupa',
    'count_cola',
    'count_cupim',
    'count_top',
    'count_pescou',
    // novos contadores
    'count_errou_pata',
    'count_errou_top',
    'count_garupa_neg',
    'count_cola_neg',
    'count_uma_aspa',
    'count_por_cima',
    'count_limpou_cupim_longe',
    // armadas adicionais
    'count_pescou_uma_aspa',
    'count_limpou_top',
    'count_limpou_top_mao',
    'count_boi_tirou',
    'count_boi_pulou',
    'count_queimou_raia',
    'count_caiu_do_cavalo',
    'count_saiu_enrolado',
    ];

    protected $casts = [
        'vitorias' => 'integer',
        'derrotas' => 'integer',
        'empates' => 'integer',
        'aproveitamento' => 'decimal:2',
        'pontuacao_media' => 'decimal:2',
    'pontuacao_total' => 'integer',
    'last_points' => 'integer',
    // casts dos contadores
    'count_boa' => 'integer',
    'count_negativas_total' => 'integer',
    'count_errou_pescoco' => 'integer',
    'count_dobrada' => 'integer',
    'count_cabresteou' => 'integer',
    'count_duas_voltas' => 'integer',
    'count_limpou_garupa' => 'integer',
    'count_cola' => 'integer',
    'count_cupim' => 'integer',
    'count_top' => 'integer',
    'count_pescou' => 'integer',
    // casts novos contadores
    'count_errou_pata' => 'integer',
    'count_errou_top' => 'integer',
    'count_garupa_neg' => 'integer',
    'count_cola_neg' => 'integer',
    'count_uma_aspa' => 'integer',
    'count_por_cima' => 'integer',
    'count_limpou_cupim_longe' => 'integer',
    'count_pescou_uma_aspa' => 'integer',
    'count_limpou_top' => 'integer',
    'count_limpou_top_mao' => 'integer',
    'count_boi_tirou' => 'integer',
    'count_boi_pulou' => 'integer',
    'count_queimou_raia' => 'integer',
    'count_caiu_do_cavalo' => 'integer',
    'count_saiu_enrolado' => 'integer',
    ];

    /**
     * Get the competitor that owns the stats.
     */
    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }

    public function getAproveitamentoAttribute($value): float
    {
        return $this->resolveAproveitamento($value);
    }

    private function resolveAproveitamento($fallback = 0): float
    {
        $boas = (int) ($this->attributes['count_boa'] ?? 0);
        $negativas = (int) ($this->attributes['count_negativas_total'] ?? 0);
        $total = $boas + $negativas;

        if ($total > 0) {
            return round(($boas / $total) * 100, 2);
        }

        return round((float) ($fallback ?? 0), 2);
    }

    /**
     * Calculate approval rate automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($stat) {
            $stat->attributes['aproveitamento'] = $stat->resolveAproveitamento($stat->attributes['aproveitamento'] ?? 0);
        });
    }
}
