<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserX1Stat;
use App\Models\X1RoomInstance;
use App\Models\X1RankingSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class X1StatsService
{
    /**
     * Registra o resultado de um X1 e atualiza as estatísticas
     */
    public function recordX1Result(
        X1RoomInstance $room,
        int $winnerId,
        int $loserId,
        float $prizeAmount,
        float $entryAmount
    ): void {
        DB::transaction(function () use ($room, $winnerId, $loserId, $prizeAmount, $entryAmount) {
            $validModalidadeId = $this->resolveValidModalidadeId($room->modalidade_id);

            // Atualizar estatísticas globais do vencedor
            $this->updateWinnerStats($winnerId, $prizeAmount, $entryAmount, $loserId, $validModalidadeId);
            
            // Atualizar estatísticas globais do perdedor
            $this->updateLoserStats($loserId, $entryAmount, $winnerId, $validModalidadeId);
            
            // Se tem modalidade, atualizar stats por modalidade também
            if ($validModalidadeId) {
                $this->updateWinnerStats($winnerId, $prizeAmount, $entryAmount, $loserId, $validModalidadeId, true);
                $this->updateLoserStats($loserId, $entryAmount, $winnerId, $validModalidadeId, true);
            }
            
            // Invalidar cache de ranking
            $this->invalidateRankingCache($validModalidadeId);
        });
    }

    /**
     * Registra um empate (Draw) e atualiza estatísticas
     */
    public function recordDraw(
        X1RoomInstance $room,
        int $player1Id,
        int $player2Id
    ): void {
        DB::transaction(function () use ($room, $player1Id, $player2Id) {
            $validModalidadeId = $this->resolveValidModalidadeId($room->modalidade_id);

            // Atualizar stats de empate para ambos
            $this->updateDrawStats($player1Id, $player2Id, $validModalidadeId);
            $this->updateDrawStats($player2Id, $player1Id, $validModalidadeId);

            // Por modalidade
            if ($validModalidadeId) {
                $this->updateDrawStats($player1Id, $player2Id, $validModalidadeId, true);
                $this->updateDrawStats($player2Id, $player1Id, $validModalidadeId, true);
            }

            $this->invalidateRankingCache($validModalidadeId);
        });
    }

    private function updateDrawStats(
        int $userId,
        int $opponentId,
        ?int $modalidadeId,
        bool $byModalidade = false
    ): void {
        $stats = UserX1Stat::firstOrCreate(
            [
                'user_id' => $userId,
                'modalidade_id' => $byModalidade ? $modalidadeId : null,
            ],
            [
                'total_x1s' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0,
                'win_rate' => 0, 'total_prize_won' => 0, 'total_invested' => 0, 'profit' => 0,
                'current_streak' => 0, 'best_win_streak' => 0, 'worst_loss_streak' => 0,
                'rating' => 1000, 'peak_rating' => 1000,
            ]
        );

        $opponentStats = UserX1Stat::where('user_id', $opponentId)
            ->where('modalidade_id', $byModalidade ? $modalidadeId : null)
            ->first();
        $opponentRating = $opponentStats?->rating ?? 1000;

        // Calculate ELO for Draw (0.5 score)
        $newRating = $this->calculateEloRating($stats->rating, $opponentRating, 0.5);

        $stats->total_x1s++;
        $stats->draws++;
        // No money changes on draw (refunded)
        
        $stats->rating = $newRating;
        if ($newRating > $stats->peak_rating) {
            $stats->peak_rating = $newRating;
        }

        // Reset streaks on draw? Or keep them? Usually draw breaks win streak.
        $stats->current_streak = 0; 

        $stats->last_x1_at = now();
        $stats->recalculateWinRate();
        $stats->save();
    }

    /**
     * Atualiza estatísticas do vencedor
     */
    private function updateWinnerStats(
        int $userId,
        float $prizeAmount,
        float $entryAmount,
        int $opponentId,
        ?int $modalidadeId,
        bool $byModalidade = false
    ): void {
        $stats = UserX1Stat::firstOrCreate(
            [
                'user_id' => $userId,
                'modalidade_id' => $byModalidade ? $modalidadeId : null,
            ],
            [
                'total_x1s' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0,
                'win_rate' => 0, 'total_prize_won' => 0, 'total_invested' => 0, 'profit' => 0,
                'current_streak' => 0, 'best_win_streak' => 0, 'worst_loss_streak' => 0,
                'rating' => 1000, 'peak_rating' => 1000,
            ]
        );

        // Obter rating do oponente para cálculo ELO
        $opponentStats = UserX1Stat::where('user_id', $opponentId)
            ->where('modalidade_id', $byModalidade ? $modalidadeId : null)
            ->first();
        $opponentRating = $opponentStats?->rating ?? 1000;

        // Calcular novo rating (ELO simplificado)
        $newRating = $this->calculateEloRating($stats->rating, $opponentRating, true);

        // Atualizar contadores
        $stats->total_x1s++;
        $stats->wins++;
        $stats->total_prize_won += $prizeAmount;
        $stats->total_invested += $entryAmount;
        $stats->rating = $newRating;
        
        // Peak rating
        if ($newRating > $stats->peak_rating) {
            $stats->peak_rating = $newRating;
        }

        // Sequência de vitórias
        if ($stats->current_streak >= 0) {
            $stats->current_streak++;
        } else {
            $stats->current_streak = 1;
        }
        
        if ($stats->current_streak > $stats->best_win_streak) {
            $stats->best_win_streak = $stats->current_streak;
        }

        $stats->last_x1_at = now();
        $stats->recalculateWinRate();
        $stats->recalculateProfit();
        $stats->save();
    }

    /**
     * Atualiza estatísticas do perdedor
     */
    private function updateLoserStats(
        int $userId,
        float $entryAmount,
        int $opponentId,
        ?int $modalidadeId,
        bool $byModalidade = false
    ): void {
        $stats = UserX1Stat::firstOrCreate(
            [
                'user_id' => $userId,
                'modalidade_id' => $byModalidade ? $modalidadeId : null,
            ],
            [
                'total_x1s' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0,
                'win_rate' => 0, 'total_prize_won' => 0, 'total_invested' => 0, 'profit' => 0,
                'current_streak' => 0, 'best_win_streak' => 0, 'worst_loss_streak' => 0,
                'rating' => 1000, 'peak_rating' => 1000,
            ]
        );

        // Obter rating do oponente para cálculo ELO
        $opponentStats = UserX1Stat::where('user_id', $opponentId)
            ->where('modalidade_id', $byModalidade ? $modalidadeId : null)
            ->first();
        $opponentRating = $opponentStats?->rating ?? 1000;

        // Calcular novo rating (ELO simplificado)
        $newRating = $this->calculateEloRating($stats->rating, $opponentRating, false);

        // Atualizar contadores
        $stats->total_x1s++;
        $stats->losses++;
        $stats->total_invested += $entryAmount;
        $stats->rating = max(100, $newRating); // Mínimo de 100

        // Sequência de derrotas
        if ($stats->current_streak <= 0) {
            $stats->current_streak--;
        } else {
            $stats->current_streak = -1;
        }
        
        $absStreak = abs($stats->current_streak);
        if ($absStreak > $stats->worst_loss_streak) {
            $stats->worst_loss_streak = $absStreak;
        }

        $stats->last_x1_at = now();
        $stats->recalculateWinRate();
        $stats->recalculateProfit();
        $stats->save();
    }

    /**
     * Calcula novo rating ELO
     * K-factor = 32 (padrão para jogadores novos)
     * @param bool|float $scoreOrWon true/1.0 for win, false/0.0 for loss, 0.5 for draw
     */
    private function calculateEloRating(int $playerRating, int $opponentRating, $scoreOrWon): int
    {
        $kFactor = 32;
        $expectedScore = 1 / (1 + pow(10, ($opponentRating - $playerRating) / 400));
        
        if (is_bool($scoreOrWon)) {
            $actualScore = $scoreOrWon ? 1 : 0;
        } else {
            $actualScore = (float) $scoreOrWon;
        }
        
        return (int) round($playerRating + $kFactor * ($actualScore - $expectedScore));
    }

    /**
     * Gera um snapshot de ranking X1
     */
    public function generateRankingSnapshot(string $type = 'alltime', ?int $modalidadeId = null): X1RankingSnapshot
    {
        $query = UserX1Stat::query()
            ->with('user:id,username,firstname,lastname,image')
            ->where('total_x1s', '>=', 1); // Mínimo 1 X1 para aparecer no ranking

        if ($modalidadeId) {
            $query->where('modalidade_id', $modalidadeId);
        } else {
            $query->whereNull('modalidade_id');
        }

        // Ordenar por total_prize_won (maior ganho), depois por wins, depois por win_rate
        $stats = $query->orderByDesc('total_prize_won')
            ->orderByDesc('wins')
            ->orderByDesc('win_rate')
            ->get();

        $payload = $stats->map(function ($stat, $index) {
            $user = $stat->user;
            return [
                'position' => $index + 1,
                'user_id' => $stat->user_id,
                'username' => $user?->username ?? 'Anônimo',
                'name' => $user?->firstname ?? $user?->username ?? 'Anônimo',
                'avatar' => $user?->image ? asset('assets/images/user/profile/' . $user->image) : null,
                'rating' => $stat->rating,
                'total_x1s' => $stat->total_x1s,
                'wins' => $stat->wins,
                'losses' => $stat->losses,
                'win_rate' => $stat->win_rate,
                'current_streak' => $stat->current_streak,
                'best_win_streak' => $stat->best_win_streak,
                'total_prize_won' => $stat->total_prize_won,
            ];
        })->toArray();

        $payload = $this->dedupeRankingPayload($payload);
        foreach ($payload as $index => &$entry) {
            $entry['position'] = $index + 1;
        }
        unset($entry);

        $snapshot = X1RankingSnapshot::create([
            'modalidade_id' => $modalidadeId,
            'type' => $type,
            'payload' => $payload,
            'total_players' => count($payload),
            'generated_at' => now(),
        ]);

        // Cachear o ranking
        $cacheKey = $this->getRankingCacheKey($type, $modalidadeId);
        Cache::put($cacheKey, $payload, now()->addHours(1));

        Log::info('X1 Ranking snapshot generated', [
            'type' => $type,
            'modalidade_id' => $modalidadeId,
            'total_players' => count($payload),
        ]);

        return $snapshot;
    }

    /**
     * Obtém ranking do cache ou gera novo
     */
    public function getRanking(string $type = 'alltime', ?int $modalidadeId = null, bool $forceRefresh = false): array
    {
        $cacheKey = $this->getRankingCacheKey($type, $modalidadeId);

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey) ?? [];
            return $this->dedupeRankingPayload($cached);
        }

        // Tentar obter do último snapshot
        $snapshot = X1RankingSnapshot::getLatest($type, $modalidadeId);
        
        if ($snapshot && $snapshot->generated_at->diffInMinutes(now()) < 60) {
            $payload = $snapshot->payload ?? [];
            $payload = $this->dedupeRankingPayload($payload);
            Cache::put($cacheKey, $payload, now()->addHours(1));
            return $payload;
        }

        // Gerar novo snapshot
        $newSnapshot = $this->generateRankingSnapshot($type, $modalidadeId);
        return $newSnapshot->payload ?? [];
    }

    /**
     * Obtém Top N do ranking
     */
    public function getTopN(int $n = 30, string $type = 'alltime', ?int $modalidadeId = null): array
    {
        $ranking = $this->getRanking($type, $modalidadeId);
        return array_slice($ranking, 0, $n);
    }

    /**
     * Obtém estatísticas de um usuário
     */
    public function getUserStats(int $userId, ?int $modalidadeId = null): ?array
    {
        $stats = UserX1Stat::where('user_id', $userId)
            ->when($modalidadeId, fn($q) => $q->where('modalidade_id', $modalidadeId))
            ->when(!$modalidadeId, fn($q) => $q->whereNull('modalidade_id'))
            ->first();

        if (!$stats) {
            return null;
        }

        // Buscar posição no ranking
        $ranking = $this->getRanking('alltime', $modalidadeId);
        $position = null;
        foreach ($ranking as $index => $entry) {
            if ($entry['user_id'] == $userId) {
                $position = $index + 1;
                break;
            }
        }

        return [
            'total_x1s' => $stats->total_x1s,
            'wins' => $stats->wins,
            'losses' => $stats->losses,
            'draws' => $stats->draws,
            'win_rate' => $stats->win_rate,
            'total_prize_won' => $stats->total_prize_won,
            'total_invested' => $stats->total_invested,
            'profit' => $stats->profit,
            'current_streak' => $stats->current_streak,
            'best_win_streak' => $stats->best_win_streak,
            'worst_loss_streak' => $stats->worst_loss_streak,
            'rating' => $stats->rating,
            'peak_rating' => $stats->peak_rating,
            'last_x1_at' => $stats->last_x1_at?->toIso8601String(),
            'ranking_position' => $position,
            'total_ranked_players' => count($ranking),
        ];
    }

    /**
     * Chave de cache para ranking
     */
    private function getRankingCacheKey(string $type, ?int $modalidadeId): string
    {
        $key = "x1_ranking_{$type}";
        if ($modalidadeId) {
            $key .= "_modalidade_{$modalidadeId}";
        }
        return $key;
    }

    /**
     * Invalida cache de ranking
     */
    private function invalidateRankingCache(?int $modalidadeId): void
    {
        $types = ['daily', 'weekly', 'monthly', 'alltime'];
        
        foreach ($types as $type) {
            Cache::forget($this->getRankingCacheKey($type, null));
            if ($modalidadeId) {
                Cache::forget($this->getRankingCacheKey($type, $modalidadeId));
            }
        }
    }

    /**
     * Remove entradas duplicadas por usuário mantendo a primeira (já ordenada).
     *
     * @param array<int, array<string, mixed>> $payload
     * @return array<int, array<string, mixed>>
     */
    private function dedupeRankingPayload(array $payload): array
    {
        $seen = [];
        $clean = [];

        foreach ($payload as $entry) {
            $userId = (int) ($entry['user_id'] ?? 0);
            if ($userId <= 0 || isset($seen[$userId])) {
                continue;
            }

            $seen[$userId] = true;
            $clean[] = $entry;
        }

        foreach ($clean as $index => &$entry) {
            $entry['position'] = $index + 1;
        }
        unset($entry);

        return $clean;
    }

    /**
     * Garante que modalidade_id referenciada pela sala ainda existe.
     * Evita erro de FK em user_x1_stats quando a modalidade foi removida.
     */
    private function resolveValidModalidadeId(?int $modalidadeId): ?int
    {
        if (!$modalidadeId) {
            return null;
        }

        $cacheKey = "modalidade_exists_{$modalidadeId}";
        $exists = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($modalidadeId) {
            return DB::table('modalidades')->where('id', $modalidadeId)->exists();
        });

        return $exists ? $modalidadeId : null;
    }
}
