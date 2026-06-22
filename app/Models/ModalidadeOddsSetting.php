<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModalidadeOddsSetting extends Model
{
    protected $table = 'modalidade_odds_settings';

    protected $fillable = [
        'modalidade_id',
        'is_enabled',
        'bankroll_gate_amount',
        'low_bet_threshold',
        'very_low_bet_threshold',
        'low_bet_boost',
        'very_low_bet_boost',
        'max_free_odd',
        'max_premium_odd',
        'min_house_margin_percent',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'bankroll_gate_amount' => 'decimal:2',
        'low_bet_threshold' => 'integer',
        'very_low_bet_threshold' => 'integer',
        'low_bet_boost' => 'decimal:3',
        'very_low_bet_boost' => 'decimal:3',
        'max_free_odd' => 'decimal:2',
        'max_premium_odd' => 'decimal:2',
        'min_house_margin_percent' => 'decimal:2',
    ];

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class, 'modalidade_id');
    }
}

