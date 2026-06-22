<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FantasyTeamCompetitor extends Model
{
    protected $table = 'fantasy_team_competitors';

    protected $fillable = [
        'fantasy_team_id',
        'competitor_id',
        'role',
        'is_captain',
        'multiplier',
        'current_points',
    ];

    protected $casts = [
        'is_captain' => 'boolean',
        'multiplier' => 'decimal:2',
        'current_points' => 'integer',
    ];

    /**
     * Relacionamento com a equipe
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(FantasyTeam::class, 'fantasy_team_id');
    }

    /**
     * Relacionamento com o competidor
     */
    public function competitor(): BelongsTo
    {
        return $this->belongsTo(Competitor::class);
    }
}
