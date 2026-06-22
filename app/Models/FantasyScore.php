<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FantasyScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'fantasy_team_id',
        'competitor_id',
        'modalidade_id',
        'real_score',
        'fantasy_points',
        'multiplier',
        'bonus_points',
        'penalty_points',
        'scored_at'
    ];

    protected $casts = [
        'real_score' => 'decimal:2',
        'fantasy_points' => 'decimal:2',
        'bonus_points' => 'decimal:2',
        'penalty_points' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'scored_at' => 'datetime'
    ];

    /**
     * Relacionamento com time fantasy
     */
    public function fantasyTeam()
    {
        return $this->belongsTo(FantasyTeam::class);
    }

    /**
     * Relacionamento com competidor
     */
    public function competitor()
    {
        return $this->belongsTo(Competitor::class);
    }

    /**
     * Relacionamento com modalidade
     */
    public function modalidade()
    {
        return $this->belongsTo(Modalidade::class);
    }

    /**
     * Calcular pontos fantasy totais
     */
    public function getTotalFantasyPoints()
    {
        $base = $this->fantasy_points * ($this->multiplier ?? 1);
        return $base + ($this->bonus_points ?? 0) - ($this->penalty_points ?? 0);
    }
}
