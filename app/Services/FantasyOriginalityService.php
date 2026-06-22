<?php

namespace App\Services;

use App\Models\FantasyTeam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FantasyOriginalityService
{
    private const ORIGINALITY_FACTORS = [
        0 => 1.00,
        1 => 0.98,
        2 => 0.95,
        3 => 0.90,
        4 => 0.85,
    ];

    public function calculateOriginality(int $leagueId, array $competitorIds): array
    {
        $teamIds = FantasyTeam::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('is_active', true)
            ->pluck('id');

        if ($teamIds->isEmpty()) {
            return [
                'originality_factor' => 1.00,
                'similarity_count' => 0
            ];
        }

        $normalizedCompetitorIds = array_values(array_unique(array_map('intval', $competitorIds)));
        $maxSimilarity = 0;

        if (Schema::hasTable('fantasy_team_competitors')) {
            $teamCompetitors = DB::table('fantasy_team_competitors')
                ->whereIn('fantasy_team_id', $teamIds->all())
                ->select(['fantasy_team_id', 'competitor_id'])
                ->get()
                ->groupBy('fantasy_team_id');

            foreach ($teamIds as $teamId) {
                $teamCompetitorIds = ($teamCompetitors->get($teamId) ?? collect())
                    ->pluck('competitor_id')
                    ->map(fn ($value) => (int) $value)
                    ->values()
                    ->toArray();
                $matches = count(array_intersect($normalizedCompetitorIds, $teamCompetitorIds));

                if ($matches > $maxSimilarity) {
                    $maxSimilarity = $matches;
                }
            }
        } else {
            $existingTeams = FantasyTeam::query()
                ->whereIn('id', $teamIds->all())
                ->get();

            foreach ($existingTeams as $team) {
                $teamCompetitorIds = $team->getCompetitors()
                    ->pluck('id')
                    ->map(fn ($value) => (int) $value)
                    ->values()
                    ->toArray();
                $matches = count(array_intersect($normalizedCompetitorIds, $teamCompetitorIds));

                if ($matches > $maxSimilarity) {
                    $maxSimilarity = $matches;
                }
            }
        }

        if ($maxSimilarity > 4) {
            $maxSimilarity = 4;
        }

        if ($maxSimilarity < 0) {
            $maxSimilarity = 0;
        }

        $factor = self::ORIGINALITY_FACTORS[$maxSimilarity] ?? 0.85;

        return [
            'originality_factor' => $factor,
            'similarity_count' => $maxSimilarity
        ];
    }

    public function getOriginalityFactor(int $similarityCount): float
    {
        return self::ORIGINALITY_FACTORS[$similarityCount] ?? 0.85;
    }

    public function calculateFinalPoints(float $rawPoints, float $originalityFactor): float
    {
        return round($rawPoints * $originalityFactor, 2);
    }
}
