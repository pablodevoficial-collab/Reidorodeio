<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'android_product_id',
        'ios_product_id',
        'price',
        'original_price',
        'duration_days',
        'trial_days',
        'min_days_for_full_refund',
        'early_cancel_penalty_months',
        'billing_cycle',
        'description',
        'features',
        'payment_methods',
        'badge',
        'badge_color',
        'is_featured',
        'is_recurring',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'features' => 'array',
        'payment_methods' => 'array',
        'is_featured' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Assinaturas deste plano
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Escopo para planos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Escopo para ordenação
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Calcula economia em relação ao plano mensal
     */
    public function getSavingsAttribute(): float
    {
        if ($this->billing_cycle === 'monthly') {
            return 0;
        }

        // Preço mensal base: R$49,90
        $monthlyPrice = 49.90;
        $months = $this->duration_days / 30;
        $fullPrice = $monthlyPrice * $months;
        
        return round($fullPrice - $this->price, 2);
    }

    /**
     * Calcula preço por mês
     */
    public function getMonthlyPriceAttribute(): float
    {
        $months = max(1, $this->duration_days / 30);
        return round($this->price / $months, 2);
    }

    /**
     * Retorna meses grátis (para badge)
     */
    public function getFreeMonthsAttribute(): int
    {
        if ($this->billing_cycle === 'monthly') {
            return 0;
        }

        $monthlyPrice = 49.90;
        $months = $this->duration_days / 30;
        $paidMonths = $this->price / $monthlyPrice;
        
        return (int) round($months - $paidMonths);
    }

    /**
     * Verifica se oferece trial
     */
    public function hasTrialAttribute(): bool
    {
        return $this->trial_days > 0;
    }

    /**
     * Formata preço para exibição
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Formata preço mensal para exibição
     */
    public function getFormattedMonthlyPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->monthly_price, 2, ',', '.');
    }

    /**
     * Retorna período formatado
     */
    public function getPeriodLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'monthly' => '/mês',
            'semiannual' => '/semestre',
            'annual' => '/ano',
            default => '',
        };
    }

    /**
     * Verifica se aceita cartão
     */
    public function acceptsCard(): bool
    {
        $methods = $this->payment_methods ?? ['pix'];
        return in_array('card', $methods);
    }

    /**
     * Verifica se aceita PIX
     */
    public function acceptsPix(): bool
    {
        $methods = $this->payment_methods ?? ['pix'];
        return in_array('pix', $methods);
    }

    /**
     * Calcula reembolso para cancelamento
     * 
     * Regras:
     * - MENSAL: Trial (3 dias) = R$0 | Após trial = multa 1 mês
     * - SEMESTRAL: Antes 1 mês = multa 2 meses | Após = proporcional
     * - ANUAL: Antes 3 meses = multa 3 meses | Após = proporcional
     * 
     * @param float $amountPaid Valor pago
     * @param int $daysUsed Dias de uso
     * @return array ['refund' => valor, 'penalty' => multa, 'eligible' => bool, 'message' => string]
     */
    public function calculateRefund(float $amountPaid, int $daysUsed): array
    {
        $monthlyPrice = 49.90; // Preço base mensal
        $minDays = $this->min_days_for_full_refund ?? 30;
        $penaltyMonths = $this->early_cancel_penalty_months ?? 1;
        
        // PLANO MENSAL (Recorrente por cartão)
        if ($this->is_recurring) {
            // Ainda no trial
            if ($daysUsed <= ($this->trial_days ?? 3)) {
                return [
                    'refund' => 0,
                    'penalty' => 0,
                    'days_used' => $daysUsed,
                    'eligible' => true,
                    'can_cancel_free' => true,
                    'message' => 'Cancelamento gratuito no período de teste!',
                ];
            }
            
            // Após trial - cobra multa de 1 mês
            $penalty = $penaltyMonths * $monthlyPrice;
            return [
                'refund' => 0,
                'penalty' => round($penalty, 2),
                'days_used' => $daysUsed,
                'eligible' => true,
                'can_cancel_free' => false,
                'message' => "Multa de {$penaltyMonths} mês(es) por cancelamento após período de teste.",
            ];
        }
        
        // PLANOS PIX (Semestral/Anual)
        $totalMonths = $this->duration_days / 30;
        $monthsUsed = ceil($daysUsed / 30);
        $monthlyValue = $amountPaid / $totalMonths;
        
        // Antes do período mínimo - aplica multa
        if ($daysUsed < $minDays) {
            $usedValue = $monthsUsed * $monthlyPrice; // Usa preço cheio
            $penalty = $penaltyMonths * $monthlyPrice;
            $refund = max(0, $amountPaid - $usedValue - $penalty);
            
            return [
                'refund' => round($refund, 2),
                'penalty' => round($penalty, 2),
                'months_used' => $monthsUsed,
                'days_used' => $daysUsed,
                'eligible' => true,
                'can_cancel_free' => false,
                'message' => "Multa de {$penaltyMonths} mês(es) por cancelamento antes de " . round($minDays / 30) . " mês(es).",
            ];
        }
        
        // Após período mínimo - reembolso proporcional sem multa
        $remainingMonths = max(0, $totalMonths - $monthsUsed);
        $refund = $remainingMonths * $monthlyValue;
        
        return [
            'refund' => round($refund, 2),
            'penalty' => 0,
            'months_used' => $monthsUsed,
            'remaining_months' => $remainingMonths,
            'days_used' => $daysUsed,
            'eligible' => true,
            'can_cancel_free' => false,
            'message' => $refund > 0 
                ? "Reembolso de " . round($remainingMonths) . " mês(es) restante(s)."
                : "Sem reembolso (período já utilizado).",
        ];
    }
}
