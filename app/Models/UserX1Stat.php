<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserX1Stat extends Model
{
    protected $table = 'user_x1_stats';

    protected $fillable = [
        'user_id',
        'modalidade_id',
        'total_x1s',
        'wins',
        'losses',
        'draws',
        'win_rate',
        'total_prize_won',
        'total_invested',
        'profit',
        'current_streak',
        'best_win_streak',
        'worst_loss_streak',
        'rating',
        'peak_rating',
        'last_x1_at',
    ];

    protected $casts = [
        'win_rate' => 'decimal:2',
        'total_prize_won' => 'decimal:2',
        'total_invested' => 'decimal:2',
        'profit' => 'decimal:2',
        'last_x1_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }

    /**
     * Calcula e atualiza o win_rate
     */
    public function recalculateWinRate(): void
    {
        if ($this->total_x1s > 0) {
            $this->win_rate = round(($this->wins / $this->total_x1s) * 100, 2);
        } else {
            $this->win_rate = 0;
        }
    }

    /**
     * Calcula e atualiza o profit
     */
    public function recalculateProfit(): void
    {
        $this->profit = $this->total_prize_won - $this->total_invested;
    }

    /**
     * Obtém as estatísticas globais (todas modalidades) do usuário
     */
    public static function getGlobalStats(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->whereNull('modalidade_id')
            ->first();
    }

    /**
     * Obtém ou cria estatísticas globais do usuário
     */
    public static function getOrCreateGlobalStats(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId, 'modalidade_id' => null],
            ['rating' => 1000, 'peak_rating' => 1000]
        );
    }

    /**
     * Obtém estatísticas por modalidade
     */
    public static function getByModalidade(int $userId, int $modalidadeId): ?self
    {
        return self::where('user_id', $userId)
            ->where('modalidade_id', $modalidadeId)
            ->first();
    }
}
