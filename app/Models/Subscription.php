<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'plano',
        'status',
        'is_trial',
        'trial_cpf',
        'trial_ends_at',
        'data_inicio',
        'data_fim',
        'next_billing_date',
        'auto_renew',
        'cancelled_at',
        'cancellation_reason',
        'valor',
        'monthly_value',
        'total_paid',
        'refund_amount',
        'refund_status',
        'refund_transaction_id',
        'gateway_pagamento',
        'payment_method',
        'transaction_id',
        'mp_subscription_id',
        'mp_preapproval_id',
        'mp_preapproval_plan_id',
        'card_last_four',
        'card_brand',
        'last_payment_at',
        'payment_attempts',
        'metadata',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'next_billing_date' => 'date',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'valor' => 'decimal:2',
        'monthly_value' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'is_trial' => 'boolean',
        'auto_renew' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Constantes de status
     */
    const STATUS_ATIVA = 'ativa';
    const STATUS_TRIAL = 'trial';
    const STATUS_CANCELADA = 'cancelada';
    const STATUS_EXPIRADA = 'expirada';
    const STATUS_PENDENTE = 'pendente';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Verifica se a assinatura está ativa
     */
    public function isActive(): bool
    {
        // Trial ativo
        if ($this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return true;
        }

        // Assinatura paga ativa
        return $this->status === self::STATUS_ATIVA && $this->data_fim >= now()->toDateString();
    }

    /**
     * Verifica se está em período de trial
     */
    public function isOnTrial(): bool
    {
        return $this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Verifica se o trial expirou
     */
    public function trialExpired(): bool
    {
        return $this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Verifica se foi cancelada
     */
    public function isCancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }

    /**
     * Verifica se pode ser renovada
     */
    public function canRenew(): bool
    {
        // Pode renovar se estiver ativa ou expirada (reativação)
        // Desde que não esteja cancelada explicitamente
        return !$this->isCancelled() && in_array($this->status, [self::STATUS_ATIVA, self::STATUS_EXPIRADA]);
    }

    /**
     * Dias restantes
     */
    public function getRemainingDaysAttribute(): int
    {
        if ($this->isOnTrial()) {
            return (int) max(0, floor(now()->diffInDays($this->trial_ends_at, false)));
        }

        if (!$this->isActive()) {
            return 0;
        }

        return (int) max(0, floor(now()->diffInDays($this->data_fim, false)));
    }

    /**
     * Dias restantes do trial
     */
    public function getTrialRemainingDaysAttribute(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }

        return (int) max(0, floor(now()->diffInDays($this->trial_ends_at, false)));
    }

    /**
     * Label do status formatado
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isOnTrial()) {
            return 'Trial (' . $this->trial_remaining_days . ' dias)';
        }

        return match($this->status) {
            self::STATUS_ATIVA => 'Ativa',
            self::STATUS_TRIAL => 'Trial',
            self::STATUS_CANCELADA => 'Cancelada',
            self::STATUS_EXPIRADA => 'Expirada',
            self::STATUS_PENDENTE => 'Pendente',
            default => ucfirst($this->status),
        };
    }

    /**
     * Cor do status
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isOnTrial()) {
            return '#8b5cf6'; // Roxo
        }

        return match($this->status) {
            self::STATUS_ATIVA => '#22c55e',    // Verde
            self::STATUS_TRIAL => '#8b5cf6',    // Roxo
            self::STATUS_CANCELADA => '#ef4444', // Vermelho
            self::STATUS_EXPIRADA => '#6b7280',  // Cinza
            self::STATUS_PENDENTE => '#f59e0b',  // Amarelo
            default => '#6b7280',
        };
    }

    /**
     * Escopo: assinaturas ativas
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_ATIVA)
              ->where('data_fim', '>=', now()->toDateString());
        })->orWhere(function ($q) {
            $q->where('is_trial', true)
              ->where('trial_ends_at', '>=', now());
        });
    }

    /**
     * Escopo: trials que vão expirar
     */
    public function scopeTrialsExpiringSoon($query, int $days = 3)
    {
        return $query->where('is_trial', true)
            ->where('trial_ends_at', '>=', now())
            ->where('trial_ends_at', '<=', now()->addDays($days));
    }

    /**
     * Escopo: próximas para renovação
     */
    public function scopeDueForRenewal($query)
    {
        return $query->where('auto_renew', true)
            ->where('status', self::STATUS_ATIVA)
            ->where('next_billing_date', '<=', now()->toDateString())
            ->whereNull('cancelled_at');
    }

    /**
     * Verifica se é assinatura por cartão
     */
    public function isCardSubscription(): bool
    {
        return $this->payment_method === 'card';
    }

    /**
     * Verifica se é assinatura por PIX
     */
    public function isPixSubscription(): bool
    {
        return $this->payment_method === 'pix';
    }

    /**
     * Calcula dias de uso
     */
    public function getDaysUsedAttribute(): int
    {
        if (!$this->data_inicio) {
            return 0;
        }
        return (int) max(0, floor($this->data_inicio->diffInDays(now())));
    }

    /**
     * Calcula reembolso disponível
     */
    public function getRefundCalculationAttribute(): array
    {
        if (!$this->plan) {
            return ['refund' => 0, 'penalty' => 0, 'eligible' => false];
        }

        $amountPaid = $this->isCardSubscription() 
            ? $this->total_paid 
            : $this->valor;

        return $this->plan->calculateRefund($amountPaid, $this->days_used);
    }

    /**
     * Info do cartão formatada
     */
    public function getCardInfoAttribute(): ?string
    {
        if (!$this->card_last_four) {
            return null;
        }
        
        $brand = $this->card_brand ? ucfirst($this->card_brand) : 'Cartão';
        return "{$brand} •••• {$this->card_last_four}";
    }

    /**
     * Label do método de pagamento
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'card' => 'Cartão de Crédito',
            'pix' => 'PIX',
            'app' => 'Benefício do app',
            'account' => 'Benefício promocional',
            default => 'N/A',
        };
    }
}
