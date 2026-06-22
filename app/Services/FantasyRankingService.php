<?php

namespace App\Services;

use App\Models\FantasyLeague;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FantasyRankingService
{
    public const CACHE_TTL_SECONDS = 300; // 5 min

    public const REFRESH_TTL_SECONDS = 60; // manual refresh result

    public function cacheKey(int $fantasyEventId, int $leagueId): string
    {
        return "fantasy_ranking_event_{$fantasyEventId}_league_{$leagueId}";
    }

    public function refreshKey(string $requestId): string
    {
        return "fantasy_ranking_refresh_{$requestId}";
    }

    /**
     * Read ranking from canonical cache, with fallback to persisted snapshot.
     * Never recalculates or generates new snapshots.
     */
    public function getRankingPayload(int $fantasyEventId, int $leagueId): ?array
    {
        // Para ligas grandes, pular cache e ir direto para snapshot
        $snapshot = $this->loadLatestSnapshot($leagueId, 'full')
            ?? $this->loadLatestSnapshot($leagueId, 'top30');

        if (!$snapshot) {
            return null;
        }

        // Só cachear se tiver menos de 100 itens (evita erro de unserialize)
        $itemCount = count($snapshot['items'] ?? []);
        if ($itemCount < 100) {
            $canonicalKey = $this->cacheKey($fantasyEventId, $leagueId);
            
            try {
                Cache::put($canonicalKey, $snapshot, now()->addSeconds(self::CACHE_TTL_SECONDS));
            } catch (\Throwable $e) {
                // Ignorar erros de cache
                \Log::warning('Failed to cache fantasy ranking', [
                    'league_id' => $leagueId,
                    'items' => $itemCount,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $snapshot;
    }

    /**
     * Convert stored payload to a view payload (top30 or full).
     * This is not a recalculation: only safe limiting.
     */
    public function toView(array $payload, string $view = 'top30'): array
    {
        $view = $view === 'full' ? 'full' : 'top30';

        if ($view === 'full') {
            return $payload;
        }

        $items = Arr::get($payload, 'items');
        if (is_array($items)) {
            $payload['items'] = array_slice($items, 0, 30);
        }

        return $payload;
    }

    /**
     * Loads the latest persisted snapshot payload for a league.
     */
    private function loadLatestSnapshot(int $leagueId, string $type): ?array
    {
        if (!Schema::hasTable('fantasy_league_ranking_snapshots')) {
            return null;
        }

        try {
            $row = DB::table('fantasy_league_ranking_snapshots')
                ->where('fantasy_league_id', $leagueId)
                ->where('type', $type)
                ->orderByDesc('generated_at')
                ->first(['payload']);

            if (!$row || !$row->payload) {
                return null;
            }

            $decoded = json_decode($row->payload, true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::warning('Failed to load fantasy ranking snapshot', [
                'league_id' => $leagueId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Minimal validation helper: ensures league exists and matches event context.
     */
    public function assertLeagueContext(int $fantasyEventId, int $leagueId): ?FantasyLeague
    {
        $league = FantasyLeague::query()->find($leagueId);
        if (!$league) {
            return null;
        }

        if ((int) ($league->rodeio_id ?? 0) !== (int) $fantasyEventId) {
            return null;
        }

        if (!(bool) ($league->is_active ?? true)) {
            return null;
        }

        return $league;
    }
}
