<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'referred_user_id',
        'type',
        'x1_room_id',
        'fantasy_team_id',
        'base_amount',
        'commission_percent',
        'commission_amount',
        'status',
        'eligible_at',
        'approved_at',
        'paid_at',
        'metadata'
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'eligible_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array'
    ];

    // ==========================================
    // RELACIONAMENTOS
    // ==========================================
    
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function x1Room()
    {
        return $this->belongsTo(\App\Models\X1Room::class, 'x1_room_id');
    }

    public function fantasyTeam()
    {
        return $this->belongsTo(\App\Models\FantasyTeam::class, 'fantasy_team_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeEligible($query)
    {
        return $query->where('eligible_at', '<=', now())
                     ->where('status', 'pending');
    }

    public function scopeX1($query)
    {
        return $query->where('type', 'x1_room');
    }

    public function scopeFantasy($query)
    {
        return $query->where('type', 'fantasy_prize');
    }

    // ==========================================
    // HELPERS
    // ==========================================
    
    /**
     * Aprovar comissão
     */
    public function approve()
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        // Adicionar ao saldo pendente do afiliado
        $this->affiliate->addPendingCommission($this->commission_amount);

        \Log::info("✅ Comissão aprovada", [
            'commission_id' => $this->id,
            'affiliate' => $this->affiliate->user->username,
            'amount' => $this->commission_amount,
            'type' => $this->type
        ]);

        return true;
    }

    /**
     * Marcar como paga
     */
    public function markAsPaid()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);

        return true;
    }
}
