<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class FantasyTeam extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'bot_user_id',
        'fantasy_league_id',
        'team_name',
        'budget',
        'total_points',
        'competitors', // legacy json support (if column exists)
        'substitutions_used',
        'formation',
        'is_active',
        'originality_factor',
        'similarity_count'
    ];

    protected $casts = [
        'competitors' => 'array',
        'formation' => 'array',
        'total_points' => 'decimal:2',
        'originality_factor' => 'decimal:2',
        'similarity_count' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Relacionamento com bot user
     */
    public function botUser()
    {
        return $this->belongsTo(\App\Models\BotUser::class, 'bot_user_id');
    }

    /**
     * Relacionamento com liga fantasy
     */
    public function fantasyLeague()
    {
        return $this->belongsTo(FantasyLeague::class);
    }

    /**
     * Seleção relacional de competidores (novo padrão).
     */
    public function competitorsRelation(): BelongsToMany
    {
        $pivotColumns = ['role', 'is_captain', 'multiplier'];

        if (Schema::hasColumn('fantasy_team_competitors', 'current_points')) {
            $pivotColumns[] = 'current_points';
        }

        return $this->belongsToMany(Competitor::class, 'fantasy_team_competitors', 'fantasy_team_id', 'competitor_id')
            ->withPivot($pivotColumns)
            ->withTimestamps();
    }

    /**
     * Relacionamento HasMany com a tabela pivot (para acesso direto aos registros pivot)
     */
    public function teamCompetitors()
    {
        return $this->hasMany(FantasyTeamCompetitor::class, 'fantasy_team_id');
    }

    /**
     * Pontuações por modalidade
     */
    public function fantasyScores()
    {
        return $this->hasMany(FantasyScore::class);
    }

    /**
     * Obter competidores do time
     */
    public function getCompetitors()
    {
        // Prefer relational pivot if table exists.
        if (Schema::hasTable('fantasy_team_competitors')) {
            return $this->competitorsRelation()->get();
        }

        // Legacy JSON fallback
        $list = $this->competitors;
        if (empty($list)) {
            return collect();
        }

        $ids = array_values(array_filter(array_map(function ($row) {
            if (is_array($row) && isset($row['competitor_id'])) return (int) $row['competitor_id'];
            if (is_numeric($row)) return (int) $row;
            return null;
        }, (array) $list)));

        if (!$ids) {
            return collect();
        }

        return Competitor::whereIn('id', $ids)->get();
    }

    /**
     * Adicionar competidor ao time
     */
    public function addCompetitor($competitorId, $position = 'titular')
    {
        if (Schema::hasTable('fantasy_team_competitors')) {
            // role: titular|reserva (mantemos compatibilidade com "position")
            $role = $position ?: 'titular';
            if ($this->competitorsRelation()->where('competitor_id', $competitorId)->exists()) {
                return false;
            }
            $this->competitorsRelation()->attach($competitorId, [
                'role' => $role,
                'is_captain' => false,
                'multiplier' => 1,
            ]);
            return true;
        }

        $competitors = $this->competitors ?? [];
        
        // Verificar se já está no time
        if (in_array($competitorId, array_column($competitors, 'competitor_id'))) {
            return false;
        }

        // Verificar limite de competidores
        if (count($competitors) >= $this->fantasyLeague->max_competitors_per_team ?? 8) {
            return false;
        }

        $competitors[] = [
            'competitor_id' => $competitorId,
            'position' => $position,
            'added_at' => now()->toISOString()
        ];

        $this->competitors = $competitors;
        $this->save();

        return true;
    }

    /**
     * Remover competidor do time
     */
    public function removeCompetitor($competitorId)
    {
        if (Schema::hasTable('fantasy_team_competitors')) {
            $this->competitorsRelation()->detach($competitorId);
            return true;
        }

        $competitors = $this->competitors ?? [];
        
        $competitors = array_filter($competitors, function($comp) use ($competitorId) {
            return $comp['competitor_id'] != $competitorId;
        });

        $this->competitors = array_values($competitors);
        $this->save();

        return true;
    }

    /**
     * Calcular pontos do time
     */
    public function calculatePoints()
    {
        // Legacy method: mantido, mas a automação principal agora é via FantasyScoringService.
        $totalPoints = 0;
        $competitors = $this->getCompetitors();

        foreach ($competitors as $competitor) {
            // Caminho antigo dependia de pivot/colunas que não existem mais no fluxo atual.
            // Mantemos o método para compatibilidade, mas evitamos quebrar em runtime.
        }

        $this->total_points = $totalPoints;
        $this->save();

        return $totalPoints;
    }

    /**
     * Converter pontuação real para pontos fantasy
     */
    private function convertToFantasyPoints($pontuacao, $tempo, $modalidade)
    {
        $scoringSystem = $this->fantasyLeague->scoring_system ?? [];
        
        $fantasyPoints = 0;

        // Pontos base pela pontuação
        if ($pontuacao >= 8) {
            $fantasyPoints += $scoringSystem['perfect_score'] ?? 100;
        } elseif ($pontuacao >= 6) {
            $fantasyPoints += $scoringSystem['good_score'] ?? 75;
        } elseif ($pontuacao >= 4) {
            $fantasyPoints += $scoringSystem['average_score'] ?? 50;
        } elseif ($pontuacao > 0) {
            $fantasyPoints += $scoringSystem['basic_score'] ?? 25;
        }

        // Bônus por tempo (se aplicável)
        if ($tempo && $tempo <= 8.0) {
            $fantasyPoints += $scoringSystem['speed_bonus'] ?? 20;
        }

        // Penalidade por eliminação
        if ($pontuacao == 0) {
            $fantasyPoints += $scoringSystem['elimination_penalty'] ?? -10;
        }

        return $fantasyPoints;
    }

    /**
     * Verificar se pode fazer substituições
     */
    public function canMakeSubstitution()
    {
        $maxSubstitutions = $this->fantasyLeague->max_substitutions ?? 3;
        return $this->substitutions_used < $maxSubstitutions;
    }

    /**
     * Fazer substituição de competidor
     */
    public function substituteCompetitor($oldCompetitorId, $newCompetitorId)
    {
        if (!$this->canMakeSubstitution()) {
            return false;
        }

        if ($this->removeCompetitor($oldCompetitorId) && $this->addCompetitor($newCompetitorId)) {
            $this->substitutions_used++;
            $this->save();
            return true;
        }

        return false;
    }
}
