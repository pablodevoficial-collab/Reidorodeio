<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tier',
        'min_referrals',
        'x1_commission_percent',
        'fantasy_commission_percent',
        'benefits'
    ];

    protected $casts = [
        'x1_commission_percent' => 'decimal:2',
        'fantasy_commission_percent' => 'decimal:2',
        'benefits' => 'array'
    ];

    // ==========================================
    // HELPERS
    // ==========================================
    
    /**
     * Obter tier por nome
     */
    public static function getByTier($tierName)
    {
        return self::where('tier', $tierName)->first();
    }

    /**
     * Obter tier baseado em número de indicações
     */
    public static function getTierByReferrals($referralCount)
    {
        return self::where('min_referrals', '<=', $referralCount)
            ->orderBy('min_referrals', 'desc')
            ->first();
    }

    /**
     * Listar todos os tiers ordenados
     */
    public static function allOrdered()
    {
        return self::orderBy('min_referrals', 'asc')->get();
    }

    /**
     * Badge do tier
     */
    public function getBadgeAttribute()
    {
        return $this->benefits['badge'] ?? '🥉';
    }

    /**
     * Nome do tier
     */
    public function getNameAttribute()
    {
        return $this->benefits['name'] ?? 'Intermediário';
    }

    /**
     * Cor do tier
     */
    public function getColorAttribute()
    {
        return $this->benefits['color'] ?? '#cd7f32';
    }
}
