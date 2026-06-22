<?php

namespace App\Services;

use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\FantasyTeamCompetitor;
use App\Models\CompetitorContextStat;
use App\Models\User;
use App\Services\FantasyScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Serviço para atualizar pontos do Fantasy em tempo real
 */
class FantasyPointsUpdateService
{
    /**
     * Atualizar pontos de todas as equipes Fantasy quando competidor pontua
     */
    public function updateTeamPoints(int $competitorId, int $rodeioId, int $modalidadeId, string $divisao): void
    {
        try {
            // Buscar leagues ativas para este rodeio + modalidade
            $leagues = FantasyLeague::query()
                ->where('rodeio_id', $rodeioId)
                ->where('modalidade_id', $modalidadeId)
                ->where('is_active', true)
                ->get();

            if ($leagues->isEmpty()) {
                return;
            }

            foreach ($leagues as $league) {
                $this->updateLeagueTeamsForCompetitor($league, $competitorId, $divisao);
            }
        } catch (\Throwable $e) {
            Log::error('Error updating Fantasy team points', [
                'competitor_id' => $competitorId,
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Atualizar times de uma liga específica para um competidor
     */
    private function updateLeagueTeamsForCompetitor(FantasyLeague $league, int $competitorId, string $divisao): void
    {
        // Buscar times que têm este competidor
        $teamCompetitors = FantasyTeamCompetitor::query()
            ->whereHas('team', function ($query) use ($league) {
                $query->where('fantasy_league_id', $league->id);
            })
            ->where('competitor_id', $competitorId)
            ->with('team')
            ->get();

        if ($teamCompetitors->isEmpty()) {
            return;
        }

        // Buscar stats do competidor baseado no tipo_stats da liga
        $stats = $this->getCompetitorStats($league, $competitorId, $divisao);

        // Se não achou stats, setar 0 (pode ter sido resetado/desqualificado)
        $points = $stats ? (int) ($stats->pontuacao_total ?? 0) : 0;

        // Atualizar pontos em cada time
        foreach ($teamCompetitors as $teamCompetitor) {
            $teamCompetitor->current_points = $points;
            $teamCompetitor->save();

            // Recalcular total do time
            $this->recalculateTeamTotal($teamCompetitor->team);
        }

        // Atualizar ranking e gerar snapshot da liga
        $this->updateRanking($league->id);
        $this->generateSnapshot($league);
    }

    /**
     * Buscar estatísticas do competidor baseado no tipo_stats da liga
     */
    private function getCompetitorStats(FantasyLeague $league, int $competitorId, string $divisao)
    {
        $tipoStats = $league->tipo_stats ?? 'final';

        $query = CompetitorContextStat::query()
            ->where('competitor_id', $competitorId)
            ->where('rodeio_id', $league->rodeio_id)
            ->where('modalidade_id', $league->modalidade_id);

        if ($tipoStats === 'final') {
            // Prioridade: stats finais na divisão específica
            $finalStats = (clone $query)->where('divisao', $divisao)->where('tipo_fase', 'final')->first();
            if ($finalStats) {
                return $finalStats;
            }
            // Fallback 1: classificatória com divisão vazia
            $classifStats = (clone $query)->where('divisao', '')->where('tipo_fase', 'classificatoria')->first();
            if ($classifStats) {
                return $classifStats;
            }
            // Fallback 2: qualquer stat disponível para este competidor (ignora divisão/fase)
            return (clone $query)->orderByDesc('pontuacao_total')->first();
        } elseif ($tipoStats === 'classificatoria') {
            // Prioridade: classificatória com divisão vazia
            $stats = (clone $query)->where('divisao', '')->where('tipo_fase', 'classificatoria')->first();
            if ($stats) {
                return $stats;
            }
            // Fallback: qualquer stat disponível
            return (clone $query)->orderByDesc('pontuacao_total')->first();
        } elseif ($tipoStats === 'ambos') {
            // Somar classificatória + final
            $statsClassificatoria = CompetitorContextStat::query()
                ->where('competitor_id', $competitorId)
                ->where('rodeio_id', $league->rodeio_id)
                ->where('modalidade_id', $league->modalidade_id)
                ->where('divisao', '')
                ->where('tipo_fase', 'classificatoria')
                ->first();

            $statsFinal = CompetitorContextStat::query()
                ->where('competitor_id', $competitorId)
                ->where('rodeio_id', $league->rodeio_id)
                ->where('modalidade_id', $league->modalidade_id)
                ->where('divisao', $divisao)
                ->where('tipo_fase', 'final')
                ->first();

            $totalPoints = 0;
            $totalPoints += (int) ($statsClassificatoria->pontuacao_total ?? 0);
            $totalPoints += (int) ($statsFinal->pontuacao_total ?? 0);

            // Retornar objeto simulado
            return (object) ['pontuacao_total' => $totalPoints];
        }

        // Fallback: qualquer stat disponível
        return $query->first();
    }

    /**
     * Recalcular pontuação total de um time (soma de todos competidores + bônus capitão)
     */
    public function recalculateTeamTotal(FantasyTeam $team): int
    {
        $total = 0;

        $competitors = FantasyTeamCompetitor::query()
            ->where('fantasy_team_id', $team->id)
            ->get();

        foreach ($competitors as $comp) {
            $points = (int) ($comp->current_points ?? 0);
            $mult = (float) ($comp->multiplier ?? 1.0);

            // Capitão: se multiplier é padrão (1.0), aplicar 50% bônus (1.5x)
            // Se multiplier já foi definido acima de 1.0, usar o valor explícito
            if ($comp->is_captain && $mult <= 1.0) {
                $mult = 1.5;
            }

            $total += (int) round($points * $mult);
        }

        $team->total_points = (float) $total;
        $team->save();

        return $total;
    }

    /**
     * Atualizar ranking de uma liga
     */
    public function updateRanking(int $leagueId): void
    {
        try {
            $teams = FantasyTeam::query()
                ->where('fantasy_league_id', $leagueId)
                ->orderByDesc('total_points')
                ->orderBy('id')
                ->get();

            $hasRankColumn = DB::getSchemaBuilder()->hasColumn('fantasy_teams', 'rank');

            $rank = 1;
            foreach ($teams as $team) {
                if ($hasRankColumn) {
                    $team->rank = $rank;
                    $team->save();
                }
                $rank++;
            }
        } catch (\Throwable $e) {
            Log::error('Error updating Fantasy ranking', [
                'league_id' => $leagueId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gerar snapshot de ranking para uma liga (delega ao FantasyScoringService)
     */
    private function generateSnapshot(FantasyLeague $league): void
    {
        try {
            $scoringService = app(FantasyScoringService::class);
            $scoringService->storeRankingSnapshot($league);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate Fantasy ranking snapshot', [
                'league_id' => $league->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Lidar com desqualificação de competidor
     */
    public function handleDisqualification(int $competitorId, int $rodeioId, int $modalidadeId): void
    {
        Log::info('Fantasy disqualification ignored for scoring recalculation', [
            'competitor_id' => $competitorId,
            'rodeio_id' => $rodeioId,
            'modalidade_id' => $modalidadeId,
        ]);
    }

    /**
     * Obter estatísticas em tempo real de uma liga (para exibir no ranking)
     */
    public function getLiveStats(int $leagueId): array
    {
        try {
            $league = FantasyLeague::findOrFail($leagueId);

            $teams = FantasyTeam::query()
                ->where('fantasy_league_id', $leagueId)
                ->with(['user:id,username,image,show_in_listings', 'teamCompetitors.competitor'])
                ->orderByDesc('total_points')
                ->get();

            $stats = [];
            $rank = 1;

            foreach ($teams as $team) {
                $publicUsername = $team->user
                    ? ($team->user->username ?? 'Unknown')
                    : 'Unknown';
                $stats[] = [
                    'rank' => $rank,
                    'team_id' => $team->id,
                    'team_name' => $team->team_name,
                    'user' => [
                        'username' => $publicUsername,
                        'image' => $team->user->image ?? null,
                        'show_in_listings' => (bool) ($team->user->show_in_listings ?? true),
                    ],
                    'total_points' => $team->total_points,
                    'competitors' => $team->teamCompetitors->map(function ($tc) {
                        return [
                            'competitor_id' => $tc->competitor_id,
                            'name' => $tc->competitor->nome ?? 'Unknown',
                            'current_points' => $tc->current_points,
                            'is_captain' => $tc->is_captain,
                        ];
                    }),
                ];
                $rank++;
            }

            return [
                'success' => true,
                'league_id' => $leagueId,
                'stats' => $stats,
                'updated_at' => now()->toISOString(),
            ];
        } catch (\Throwable $e) {
            Log::error('Error getting Fantasy live stats', [
                'league_id' => $leagueId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
