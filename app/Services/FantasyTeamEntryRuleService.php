<?php

namespace App\Services;

use App\Models\FantasyTeam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FantasyTeamEntryRuleService
{
    private const MIN_NEW_COMPETITORS_PER_EXTRA_TEAM = 2;

    public function validateForUser(int $leagueId, int $userId, array $competitorIds): ?array
    {
        $competitorIds = $this->normalizeIds($competitorIds);
        if (count($competitorIds) !== 4) {
            return null;
        }

        $existingTeamSelections = $this->existingTeamSelections($leagueId, $userId);
        if (!$existingTeamSelections) {
            return null;
        }

        $selectedSorted = $competitorIds;
        sort($selectedSorted);

        foreach ($existingTeamSelections as $existingIds) {
            $existingSorted = $existingIds;
            sort($existingSorted);

            if ($existingSorted === $selectedSorted) {
                return [
                    'success' => false,
                    'message' => 'Essa equipe ja foi montada neste bolao. Troque pelo menos 2 competidores.',
                    'team_rule' => 'exact_duplicate',
                    'min_new_competitors' => self::MIN_NEW_COMPETITORS_PER_EXTRA_TEAM,
                    'new_competitors' => 0,
                ];
            }
        }

        $usedIds = array_values(array_unique(array_merge(...$existingTeamSelections)));
        $newCompetitorsCount = count(array_diff($selectedSorted, $usedIds));

        if ($newCompetitorsCount < self::MIN_NEW_COMPETITORS_PER_EXTRA_TEAM) {
            return [
                'success' => false,
                'message' => 'Para montar outra equipe, escolha pelo menos 2 competidores que ainda nao foram usados em nenhuma equipe sua neste bolao.',
                'team_rule' => 'minimum_new_competitors',
                'min_new_competitors' => self::MIN_NEW_COMPETITORS_PER_EXTRA_TEAM,
                'new_competitors' => $newCompetitorsCount,
            ];
        }

        return null;
    }

    private function existingTeamSelections(int $leagueId, int $userId): array
    {
        $usesPivotTable = Schema::hasTable('fantasy_team_competitors');
        $select = ['id'];
        if (!$usesPivotTable && Schema::hasColumn('fantasy_teams', 'competitors')) {
            $select[] = 'competitors';
        }

        $teams = FantasyTeam::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get($select);

        if ($teams->isEmpty()) {
            return [];
        }

        if ($usesPivotTable) {
            $teamIds = $teams->pluck('id')->map(fn ($id) => (int) $id)->all();
            $rowsByTeam = DB::table('fantasy_team_competitors')
                ->whereIn('fantasy_team_id', $teamIds)
                ->get(['fantasy_team_id', 'competitor_id'])
                ->groupBy(fn ($row) => (int) $row->fantasy_team_id);

            return $teams
                ->map(fn (FantasyTeam $team) => $this->normalizeIds(
                    $rowsByTeam->get((int) $team->id, collect())
                        ->pluck('competitor_id')
                        ->all()
                ))
                ->filter(fn (array $ids) => count($ids) === 4)
                ->values()
                ->all();
        }

        return $teams
            ->map(fn (FantasyTeam $team) => $this->normalizeIds($team->getCompetitors()->pluck('id')->all()))
            ->filter(fn (array $ids) => count($ids) === 4)
            ->values()
            ->all();
    }

    private function normalizeIds(array $ids): array
    {
        $normalized = array_values(array_unique(array_filter(
            array_map(fn ($id) => (int) $id, $ids),
            fn ($id) => $id > 0
        )));

        sort($normalized);

        return $normalized;
    }
}
