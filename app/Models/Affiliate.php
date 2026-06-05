<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_code',
        'tier',
        'total_referrals',
        'active_referrals',
        'total_earned',
        'pending_commission',
        'paid_total',
        'status',
        'suspended_reason'
    ];

    protected $casts = [
        'total_earned' => 'decimal:2',
        'pending_commission' => 'decimal:2',
        'paid_total' => 'decimal:2',
    ];

    // ==========================================
    // RELACIONAMENTOS
    // ==========================================
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function activeReferrals()
    {
        return $this->referrals()->where('status', 'active');
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function payments()
    {
        return $this->hasMany(AffiliatePayment::class);
    }

    // ==========================================
    // HELPERS
    // ==========================================
    
    /**
     * Obter dados do tier atual
     */
    public function tierData()
    {
        return AffiliateTier::where('tier', $this->tier)->first();
    }

    /**
     * Obter próximo tier
     */
    public function nextTier()
    {
        return AffiliateTier::where('min_referrals', '>', $this->active_referrals)
            ->orderBy('min_referrals', 'asc')
            ->first();
    }

    /**
     * Atualizar tier baseado em indicações ativas
     */
    public function updateTier()
    {
        $newTier = AffiliateTier::where('min_referrals', '<=', $this->active_referrals)
            ->orderBy('min_referrals', 'desc')
            ->first();
        
        if ($newTier && $newTier->tier !== $this->tier) {
            $oldTier = $this->tier;
            $this->update(['tier' => $newTier->tier]);
            
            \Log::info("🎖️ Afiliado evoluiu de tier", [
                'affiliate_id' => $this->id,
                'user' => $this->user->username,
                'old_tier' => $oldTier,
                'new_tier' => $newTier->tier,
                'referrals' => $this->active_referrals
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Incrementar indicações
     */
    public function addReferral($userId = null)
    {
        $this->increment('total_referrals');
        $this->increment('active_referrals');
        
        // Verificar se deve subir de tier
        $this->updateTier();
    }

    /**
     * Adicionar comissão pendente
     */
    public function addPendingCommission($amount)
    {
        $this->increment('pending_commission', $amount);
        $this->increment('total_earned', $amount);
    }

    /**
     * Marcar comissão como paga
     */
    public function markCommissionPaid($amount)
    {
        $this->decrement('pending_commission', $amount);
        $this->increment('paid_total', $amount);
    }
}
