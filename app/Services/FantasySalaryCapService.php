<?php

namespace App\Services;

use App\Models\FantasyLeague;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FantasySalaryCapService
{
    public const TEAM_SIZE = 4;

    public function getEligibleCompetitorIds(FantasyLeague $league, bool $onlyAvailable = true): array
    {
        if (!$league->modalidade_id || !Schema::hasTable('competitor_modalidade')) {
            return [];
        }

        $query = DB::table('competitor_modalidade as cm')
            ->join('competitors as c', 'c.id', '=', 'cm.competitor_id')
            ->where('cm.modalidade_id', (int) $league->modalidade_id)
            ->where('c.status', 'ativo')
            ->when($onlyAvailable, fn ($q) => $q->where('cm.disponivel_participacao', 1));

        $canJoinStats = Schema::hasTable('competitor_stats') && $league->rodeio_id;
        if ($canJoinStats) {
            $query->leftJoin('competitor_stats as cs', function ($join) use ($league) {
                $join->on('cs.competitor_id', '=', 'c.id')
                    ->where('cs.rodeio_id', '=', (int) $league->rodeio_id)
                    ->where('cs.modalidade_id', '=', (int) $league->modalidade_id);
            });

            $query->where(function ($q) {
                $q->whereNull('cs.id')
                    ->orWhere('cs.is_finalized', false)
                    ->orWhere('cs.tipo_fase', 'classificatoria');
            });
        }

        $hasPivotDivisao = Schema::hasColumn('competitor_modalidade', 'divisao');
        $leagueDivisao = trim((string) ($league->divisao ?? ''));

        $modalidade = $league->modalidade;
        $isClassificatoria = $modalidade && in_array($modalidade->status, ['classificatoria', 'programado'], true);
        $hasAssignedDivisions = false;

        if ($hasPivotDivisao && !$isClassificatoria) {
            $hasAssignedDivisions = DB::table('competitor_modalidade')
                ->where('modalidade_id', (int) $league->modalidade_id)
                ->whereNotNull('divisao')
                ->where('divisao', '!=', '')
                ->exists();
        }

        if ($leagueDivisao !== '' && $hasPivotDivisao && !$isClassificatoria && $hasAssignedDivisions) {
            $query->where('cm.divisao', $leagueDivisao);
        }

        return $this->normalizeIds(
            $query->pluck('cm.competitor_id')->all()
        );
    }

    public function getLeaguePricing(FantasyLeague $league, array $availableCompetitorIds): array
    {
        $availableIds = $this->normalizeIds($availableCompetitorIds);
        $config = $this->getConfig($league);

        if ($availableIds === []) {
            return [
                'prices' => [],
                'pick_counts' => [],
                'meta' => $config + [
                    'team_size' => self::TEAM_SIZE,
                    'available_count' => 0,
                    'prices_rebalanced' => false,
                    'cheapest_team_cost' => 0,
                ],
            ];
        }

        $pickCounts = $this->getActivePickCounts($league, $availableIds);
        $prices = [];

        foreach ($availableIds as $competitorId) {
            $pickCount = (int) ($pickCounts[$competitorId] ?? 0);
            $prices[$competitorId] = min(
                $config['base_price'] + ($pickCount * $config['price_per_pick']),
                $config['max_price']
            );
        }

        $pricesRebalanced = false;
        if (count($availableIds) >= self::TEAM_SIZE) {
            [$prices, $pricesRebalanced] = $this->rebalancePricesToCap(
                $prices,
                $availableIds,
                $config['salary_cap']
            );
        }

        return [
            'prices' => $prices,
            'pick_counts' => $pickCounts,
            'meta' => $config + [
                'team_size' => self::TEAM_SIZE,
                'available_count' => count($availableIds),
                'prices_rebalanced' => $pricesRebalanced,
                'cheapest_team_cost' => $this->sumCheapestPrices($prices, $availableIds, self::TEAM_SIZE),
            ],
        ];
    }

    private function getActivePickCounts(FantasyLeague $league, array $competitorIds): array
    {
        if ($competitorIds === []) {
            return [];
        }

        if (Schema::hasTable('fantasy_team_competitors') && Schema::hasTable('fantasy_teams')) {
            $query = DB::table('fantasy_team_competitors as ftc')
                ->join('fantasy_teams as ft', 'ft.id', '=', 'ftc.fantasy_team_id')
                ->where('ft.fantasy_league_id', (int) $league->id)
                ->where('ft.is_active', true)
                ->whereIn('ftc.competitor_id', $competitorIds);

            if (Schema::hasColumn('fantasy_teams', 'deleted_at')) {
                $query->whereNull('ft.deleted_at');
            }

            return $query
                ->groupBy('ftc.competitor_id')
                ->select('ftc.competitor_id', DB::raw('COUNT(DISTINCT ftc.fantasy_team_id) as pick_count'))
                ->pluck('pick_count', 'ftc.competitor_id')
                ->map(fn ($value) => (int) $value)
                ->toArray();
        }

        if (Schema::hasTable('fantasy_league_competitor_stats')) {
            return DB::table('fantasy_league_competitor_stats')
                ->where('fantasy_league_id', (int) $league->id)
                ->whereIn('competitor_id', $competitorIds)
                ->pluck('pick_count', 'competitor_id')
                ->map(fn ($value) => (int) $value)
                ->toArray();
        }

        return [];
    }

    private function rebalancePricesToCap(array $prices, array $availableIds, int $salaryCap): array
    {
        $currentCheapest = $this->sumCheapestPrices($prices, $availableIds, self::TEAM_SIZE);
        if ($currentCheapest <= 0 || $currentCheapest <= $salaryCap) {
            return [$prices, false];
        }

        $factor = $salaryCap / max(1, $currentCheapest);
        foreach ($prices as $competitorId => $price) {
            $prices[$competitorId] = max(1, (int) floor($price * $factor));
        }

        $guard = 0;
        while ($this->sumCheapestPrices($prices, $availableIds, self::TEAM_SIZE) > $salaryCap && $guard < 5000) {
            $cheapestIds = $this->getCheapestIds($prices, $availableIds, self::TEAM_SIZE);
            usort($cheapestIds, function (int $left, int $right) use ($prices) {
                $rightPrice = (int) ($prices[$right] ?? 0);
                $leftPrice = (int) ($prices[$left] ?? 0);

                if ($rightPrice === $leftPrice) {
                    return $left <=> $right;
                }

                return $rightPrice <=> $leftPrice;
            });

            $reduceId = $cheapestIds[0] ?? null;
            if (!$reduceId || ($prices[$reduceId] ?? 0) <= 1) {
                break;
            }

            $prices[$reduceId] -= 1;
            $guard++;
        }

        return [$prices, true];
    }

    private function sumCheapestPrices(array $prices, array $availableIds, int $teamSize): int
    {
        $cheapestIds = $this->getCheapestIds($prices, $availableIds, $teamSize);

        $sum = 0;
        foreach ($cheapestIds as $competitorId) {
            $sum += (int) ($prices[$competitorId] ?? 0);
        }

        return $sum;
    }

    private function getCheapestIds(array $prices, array $availableIds, int $teamSize): array
    {
        $filtered = [];
        foreach ($availableIds as $competitorId) {
            $filtered[$competitorId] = (int) ($prices[$competitorId] ?? 0);
        }

        asort($filtered, SORT_NUMERIC);

        return array_slice(array_keys($filtered), 0, $teamSize);
    }

    private function getConfig(FantasyLeague $league): array
    {
        $basePrice = max(0, (int) ($league->base_price ?? 150));
        $maxPrice = max($basePrice, (int) ($league->max_price ?? 300));

        return [
            'salary_cap' => max(1, (int) ($league->salary_cap ?? 1000)),
            'base_price' => $basePrice,
            'price_per_pick' => max(0, (int) ($league->price_per_pick ?? 10)),
            'max_price' => $maxPrice,
        ];
    }

    private function normalizeIds(array $ids): array
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            fn ($value) => is_numeric($value) ? (int) $value : 0,
            $ids
        ))));

        sort($normalized);

        return $normalized;
    }
}
