<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class X1RankingSnapshot extends Model
{
    protected $table = 'x1_ranking_snapshots';

    protected $fillable = [
        'modalidade_id',
        'type',
        'payload',
        'total_players',
        'generated_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'generated_at' => 'datetime',
    ];

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }

    /**
     * Obtém o snapshot mais recente por tipo
     */
    public static function getLatest(string $type = 'alltime', ?int $modalidadeId = null): ?self
    {
        return self::where('type', $type)
            ->when($modalidadeId, fn($q) => $q->where('modalidade_id', $modalidadeId))
            ->when(!$modalidadeId, fn($q) => $q->whereNull('modalidade_id'))
            ->orderByDesc('generated_at')
            ->first();
    }

    /**
     * Obtém o Top N do ranking
     */
    public function getTopN(int $n = 30): array
    {
        $payload = $this->payload ?? [];
        return array_slice($payload, 0, $n);
    }

    /**
     * Encontra a posição de um usuário no ranking
     */
    public function getUserPosition(int $userId): ?array
    {
        $payload = $this->payload ?? [];
        
        foreach ($payload as $index => $entry) {
            if (($entry['user_id'] ?? null) == $userId) {
                return [
                    'position' => $index + 1,
                    'data' => $entry,
                ];
            }
        }
        
        return null;
    }
}
