<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Competitor;
use App\Models\Rodeio;
use App\Models\Modalidade;
use App\Models\UserX1Stat;

/**
 * CacheService - Gerenciamento centralizado de cache para otimização de performance
 * 
 * Usa file cache (compatível com shared hosting) com TTLs agressivos
 * para reduzir queries ao banco em operações frequentes.
 */
class CacheService
{
    // TTLs em segundos
    const TTL_RANKINGS = 300;      // 5 minutos
    const TTL_RODEIOS = 600;       // 10 minutos
    const TTL_MODALIDADES = 3600;  // 1 hora (raramente mudam)
    const TTL_COMPETITORS = 600;   // 10 minutos
    const TTL_STATS = 300;         // 5 minutos
    
    // Cache Keys
    const KEY_X1_RANKING = 'x1_ranking_top';
    const KEY_RODEIOS_ACTIVE = 'rodeios_active';
    const KEY_RODEIOS_ALL = 'rodeios_all';
    const KEY_MODALIDADES = 'modalidades_all';
    const KEY_COMPETITORS = 'competitors_';
    
    // ====================================================
    // RANKING X1
    // ====================================================
    
    /**
     * Obtém ranking X1 top N (cacheado)
     */
    public static function getX1Ranking(int $limit = 30): array
    {
        $key = self::KEY_X1_RANKING . '_' . $limit;
        
        return Cache::remember($key, self::TTL_RANKINGS, function () use ($limit) {
            return UserX1Stat::with('user:id,username,firstname,lastname,image')
                ->whereNull('modalidade_id')
                ->where('total_prize_won', '>', 0)
                ->orderByDesc('total_prize_won')
                ->limit($limit)
                ->get()
                ->map(function ($stat, $index) {
                    $username = $stat->user->username ?? null;
                    $fullName = trim((string) (($stat->user->firstname ?? '') . ' ' . ($stat->user->lastname ?? '')));
                    $displayName = $username ?: ($fullName !== '' ? $fullName : 'Usuário');
                    $avatar = $stat->user && $stat->user->image
                        ? asset('assets/images/user/profile/' . $stat->user->image)
                        : null;

                    return [
                        'position' => $index + 1,
                        'user_id' => $stat->user_id,
                        'name' => $displayName,
                        'avatar' => $avatar,
                        'total_prize_won' => (float) $stat->total_prize_won,
                        'wins' => (int) $stat->wins,
                        'losses' => (int) $stat->losses,
                        'total_matches' => (int) $stat->total_x1s,
                    ];
                })
                ->toArray();
        });
    }
    
    /**
     * Limpa cache do ranking (chamar após resultado X1)
     */
    public static function clearX1Ranking(): void
    {
        Cache::forget(self::KEY_X1_RANKING . '_30');
        Cache::forget(self::KEY_X1_RANKING . '_50');
        Cache::forget(self::KEY_X1_RANKING . '_100');
    }
    
    // ====================================================
    // RODEIOS
    // ====================================================
    
    /**
     * Obtém rodeios ativos (cacheado)
     */
    public static function getActiveRodeios(): array
    {
        return Cache::remember(self::KEY_RODEIOS_ACTIVE, self::TTL_RODEIOS, function () {
            return Rodeio::where('status', 'active')
                ->orWhere('status', 'ativo')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'status', 'start', 'end'])
                ->toArray();
        });
    }
    
    /**
     * Obtém todos os rodeios (cacheado)
     */
    public static function getAllRodeios(): array
    {
        return Cache::remember(self::KEY_RODEIOS_ALL, self::TTL_RODEIOS, function () {
            return Rodeio::orderBy('name')
                ->get(['id', 'name', 'slug', 'status', 'start', 'end'])
                ->toArray();
        });
    }
    
    /**
     * Limpa cache de rodeios
     */
    public static function clearRodeios(): void
    {
        Cache::forget(self::KEY_RODEIOS_ACTIVE);
        Cache::forget(self::KEY_RODEIOS_ALL);
    }
    
    // ====================================================
    // MODALIDADES
    // ====================================================
    
    /**
     * Obtém todas as modalidades (cacheado)
     */
    public static function getModalidades(): array
    {
        return Cache::remember(self::KEY_MODALIDADES, self::TTL_MODALIDADES, function () {
            return Modalidade::orderBy('nome')
                ->get(['id', 'nome', 'rodeio_id', 'status'])
                ->toArray();
        });
    }
    
    /**
     * Limpa cache de modalidades
     */
    public static function clearModalidades(): void
    {
        Cache::forget(self::KEY_MODALIDADES);
    }
    
    // ====================================================
    // COMPETIDORES
    // ====================================================
    
    /**
     * Obtém competidores por modalidade (cacheado)
     */
    public static function getCompetitorsByModalidade(int $modalidadeId): array
    {
        $key = self::KEY_COMPETITORS . 'mod_' . $modalidadeId;
        
        return Cache::remember($key, self::TTL_COMPETITORS, function () use ($modalidadeId) {
            // Competidores são vinculados via modalidade_competitor_groups, não diretamente
            return DB::table('modalidade_competitor_groups as g')
                ->join('modalidade_competitor_group_members as m', 'g.id', '=', 'm.group_id')
                ->join('competitors as c', 'c.id', '=', 'm.competitor_id')
                ->where('g.modalidade_id', $modalidadeId)
                ->where('c.status', 'ativo')
                ->select('c.id', 'c.nome', 'c.foto', 'g.modalidade_id', 'g.id as group_id', 'g.nome as group_nome')
                ->distinct()
                ->orderBy('c.nome')
                ->get()
                ->toArray();
        });
    }
    
    /**
     * Obtém competidores por rodeio (cacheado)
     */
    public static function getCompetitorsByRodeio(int $rodeioId): array
    {
        $key = self::KEY_COMPETITORS . 'rod_' . $rodeioId;
        
        return Cache::remember($key, self::TTL_COMPETITORS, function () use ($rodeioId) {
            return DB::table('modalidades as mod')
                ->join('modalidade_competitor_groups as g', 'g.modalidade_id', '=', 'mod.id')
                ->join('modalidade_competitor_group_members as m', 'g.id', '=', 'm.group_id')
                ->join('competitors as c', 'c.id', '=', 'm.competitor_id')
                ->where('mod.rodeio_id', $rodeioId)
                ->where('c.status', 'ativo')
                ->select('c.id', 'c.nome', 'c.foto', 'mod.id as modalidade_id')
                ->distinct()
                ->orderBy('c.nome')
                ->get()
                ->toArray();
        });
    }
    
    /**
     * Limpa cache de competidores
     */
    public static function clearCompetitors(?int $modalidadeId = null, ?int $rodeioId = null): void
    {
        if ($modalidadeId) {
            Cache::forget(self::KEY_COMPETITORS . 'mod_' . $modalidadeId);
        }
        if ($rodeioId) {
            Cache::forget(self::KEY_COMPETITORS . 'rod_' . $rodeioId);
        }
    }
    
    // ====================================================
    // USER STATS
    // ====================================================
    
    /**
     * Obtém estatísticas X1 do usuário (cacheado)
     */
    public static function getUserX1Stats(int $userId): ?array
    {
        $key = 'user_x1_stats_' . $userId;
        
        return Cache::remember($key, self::TTL_STATS, function () use ($userId) {
            $stats = UserX1Stat::where('user_id', $userId)->first();
            
            if (!$stats) {
                return null;
            }
            
            return [
                'total_matches' => (int) $stats->total_x1s,
                'wins' => (int) $stats->wins,
                'losses' => (int) $stats->losses,
                'draws' => (int) $stats->draws,
                'total_bet' => (float) $stats->total_invested,
                'total_prize_won' => (float) $stats->total_prize_won,
                'win_rate' => $stats->total_x1s > 0 
                    ? round(($stats->wins / $stats->total_x1s) * 100, 1) 
                    : 0,
            ];
        });
    }
    
    /**
     * Limpa cache de stats do usuário
     */
    public static function clearUserStats(int $userId): void
    {
        Cache::forget('user_x1_stats_' . $userId);
    }
    
    // ====================================================
    // UTILITÁRIOS
    // ====================================================
    
    /**
     * Limpa todo o cache do sistema
     */
    public static function clearAll(): void
    {
        self::clearX1Ranking();
        self::clearRodeios();
        self::clearModalidades();
        // Não limpa cache de usuários individuais - muito custoso
    }
    
    /**
     * Aquece o cache com dados frequentes
     */
    public static function warmUp(): void
    {
        // Pré-carrega dados mais acessados
        self::getX1Ranking(30);
        self::getActiveRodeios();
        self::getAllRodeios();
        self::getModalidades();
    }
}
