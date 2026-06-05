<?php

namespace App\Services;

use App\Models\CompetitorContextStat;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FantasyScoringService
{
    /**
     * Recalcula (de forma idempotente) a pontuação dos times de uma liga a partir de competitor_stats.
     * Pode limitar ao(s) time(s) afetado(s) por um competidor específico.
     */
    public function recalculateLeague(FantasyLeague $league, ?int $changedCompetitorId = null): array
    {
        if (!Schema::hasTable('fantasy_teams') || !Schema::hasTable('fantasy_team_competitors') || !Schema::hasTable('competitor_stats')) {
            return ['updated_teams' => 0, 'league_id' => $league->id];
        }

        if (!$league->rodeio_id || !$league->modalidade_id) {
            return ['updated_teams' => 0, 'league_id' => $league->id];
        }

        $teamQuery = FantasyTeam::query()->where('fantasy_league_id', $league->id);

        if ($changedCompetitorId) {
            $teamQuery->whereIn('id', function ($q) use ($changedCompetitorId) {
                $q->from('fantasy_team_competitors')
                    ->select('fantasy_team_id')
                    ->where('competitor_id', $changedCompetitorId);
            });
        }

        $teamIds = $teamQuery->pluck('id');
        if ($teamIds->isEmpty()) {
            $this->storeRankingSnapshot($league);
            return ['updated_teams' => 0, 'league_id' => $league->id];
        }

        $pivotRows = DB::table('fantasy_team_competitors')
            ->whereIn('fantasy_team_id', $teamIds)
            ->get(['fantasy_team_id', 'competitor_id', 'multiplier', 'is_captain']);

        if ($pivotRows->isEmpty()) {
            // Times sem competidores: zera pontos
            FantasyTeam::whereIn('id', $teamIds)->update(['total_points' => 0]);
            $this->storeRankingSnapshot($league);
            return ['updated_teams' => (int) $teamIds->count(), 'league_id' => $league->id];
        }

        $competitorIds = $pivotRows->pluck('competitor_id')->unique()->values();

        $statsQuery = CompetitorContextStat::query()
            ->where('rodeio_id', (int) $league->rodeio_id)
            ->where('modalidade_id', (int) $league->modalidade_id)
            ->whereIn('competitor_id', $competitorIds);

        if (Schema::hasColumn('competitor_stats', 'divisao')) {
            $statsQuery->where('divisao', (string) ($league->divisao ?? ''));
        }

        $statsByCompetitor = $statsQuery->get(['competitor_id', 'pontuacao_total'])->keyBy('competitor_id');

        $byTeam = [];
        foreach ($pivotRows as $row) {
            $byTeam[$row->fantasy_team_id][] = $row;
        }

        $updated = 0;

        foreach ($byTeam as $teamId => $rows) {
            $total = 0.0;

            foreach ($rows as $row) {
                $stat = $statsByCompetitor[$row->competitor_id] ?? null;
                $base = (float) ($stat?->pontuacao_total ?? 0);
                $mult = (float) ($row->multiplier ?? 1);

                // Capitão: se multiplier é padrão (1.0), aplicar 50% bônus (1.5x)
                if ($row->is_captain && $mult <= 1.0) {
                    $mult = 1.5;
                }

                $total += round($base * $mult);
            }

            FantasyTeam::where('id', (int) $teamId)->update(['total_points' => $total]);
            $updated++;
        }

        $this->storeRankingSnapshot($league);

        return ['updated_teams' => $updated, 'league_id' => $league->id];
    }

    /**
     * Gera snapshot (top30 + full) e também coloca em cache para consumo rápido.
     */
    public function storeRankingSnapshot(FantasyLeague $league): void
    {
        if (!Schema::hasTable('fantasy_teams')) {
            return;
        }

        try {
            $query = FantasyTeam::query()
                ->leftJoin('users', 'fantasy_teams.user_id', '=', 'users.id')
                ->leftJoin('bot_users', 'fantasy_teams.bot_user_id', '=', 'bot_users.id')
                ->where('fantasy_teams.fantasy_league_id', $league->id)
                ->where('fantasy_teams.is_active', true)
                ->orderByDesc('fantasy_teams.total_points')
                ->orderBy('fantasy_teams.id');

            $full = $query->get([
                'fantasy_teams.id',
                'fantasy_teams.user_id',
                'fantasy_teams.bot_user_id',
                'fantasy_teams.team_name',
                'fantasy_teams.total_points',
                'users.username as user_username',
                'users.image as user_avatar',
                'users.show_in_listings as user_show_in_listings',
                'bot_users.username as bot_username'
            ]);

            $payloadFull = [
                'league_id' => $league->id,
                'generated_at' => now()->toISOString(),
                'items' => $full->map(function ($t, $i) {
                    $showInListings = $t->user_id
                        ? (bool) ($t->user_show_in_listings ?? true)
                        : false;
                    $username = $t->user_username
                        ? $t->user_username
                        : ($t->bot_username ?: 'Usuário');
                    $avatar = $t->user_avatar;
                    
                    return [
                        'position' => $i + 1,
                        'team_id' => $t->id,
                        'user_id' => $t->user_id,
                        'bot_user_id' => $t->bot_user_id,
                        'team_name' => $t->team_name,
                        'username' => $username,
                        'user_name' => $username,
                        'display_name' => $username,
                        'user_avatar' => $avatar,
                        'show_in_listings' => $showInListings,
                        'points' => (float) $t->total_points,
                    ];
                })->all(),
            ];

            $payloadTop30 = $payloadFull;
            $payloadTop30['items'] = array_slice($payloadFull['items'], 0, 30);

            Cache::put("fantasy_league_ranking_top30_{$league->id}", $payloadTop30, now()->addMinutes(10));
            Cache::put("fantasy_league_ranking_full_{$league->id}", $payloadFull, now()->addMinutes(10));

            if (Schema::hasTable('fantasy_league_ranking_snapshots')) {
                DB::table('fantasy_league_ranking_snapshots')->updateOrInsert(
                    ['fantasy_league_id' => $league->id, 'type' => 'top30'],
                    ['payload' => json_encode($payloadTop30), 'generated_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                );
                DB::table('fantasy_league_ranking_snapshots')->updateOrInsert(
                    ['fantasy_league_id' => $league->id, 'type' => 'full'],
                    ['payload' => json_encode($payloadFull), 'generated_at' => now(), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to store fantasy ranking snapshot', [
                'league_id' => $league->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
