<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliatePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'paid_by_admin_id',
        'amount',
        'notes',
        'status',
        'payment_details',
        'rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    // ==========================================
    // RELACIONAMENTOS
    // ==========================================
    
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function paidByAdmin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'paid_by_admin_id');
    }

    // ==========================================
    // HELPERS
    // ==========================================
    
    /**
     * Criar pagamento e atualizar saldos
     */
    public static function createPayment($affiliateId, $adminId, $amount, $notes = null)
    {
        $payment = self::create([
            'affiliate_id' => $affiliateId,
            'paid_by_admin_id' => $adminId,
            'amount' => $amount,
            'notes' => $notes,
            'status' => 'paid',
            'payment_details' => 'Pagamento Manual Admin'
        ]);

        // Atualizar saldos do afiliado
        $affiliate = Affiliate::find($affiliateId);
        if ($affiliate) {
            $affiliate->markCommissionPaid($amount);
        }

        \Log::info("💰 Pagamento registrado", [
            'payment_id' => $payment->id,
            'affiliate_id' => $affiliateId,
            'amount' => $amount,
            'admin_id' => $adminId
        ]);

        return $payment;
    }
    
    /**
     * Solicitar saque (Usuário)
     */
    public static function requestWithdrawal($affiliateId, $amount, $paymentDetails)
    {
        $payment = self::create([
            'affiliate_id' => $affiliateId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_details' => $paymentDetails,
            'notes' => 'Solicitação via Dashboard'
        ]);
        
        // O saldo permanece pendente até ser PAGO. 
        // Não debitamos agora, pois se rejeitar, teria que estornar.
        // A lógica de saldo disponível deve considerar: pending_commission - active_withdrawal_requests
        
        return $payment;
    }
}
