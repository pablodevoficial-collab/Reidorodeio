<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function hasClaimedAppPremiumBenefit(User $user): bool
    {
        return Subscription::query()
            ->where('user_id', $user->id)
            ->where('gateway_pagamento', 'app_bonus')
            ->exists();
    }

    public function canActivateAppPremiumBenefit(User $user): bool
    {
        if ($user->isPremium()) {
            return false;
        }

        return !$this->hasClaimedAppPremiumBenefit($user);
    }

    public function getAppPremiumBenefitIneligibilityReason(User $user): ?string
    {
        if ($user->isPremium()) {
            return 'Sua conta já possui Premium ativo.';
        }

        if ($this->hasClaimedAppPremiumBenefit($user)) {
            return 'O benefício de 1 mês do app já foi utilizado nesta conta.';
        }

        return null;
    }

    /**
     * Verifica se CPF já usou trial (controle global)
     */
    public function hasCpfUsedTrial(string $cpf): bool
    {
        // Normaliza CPF (remove pontuação)
        $cpf = preg_replace('/\D/', '', $cpf);
        
        return DB::table('subscription_trial_cpfs')
            ->where('cpf', $cpf)
            ->exists();
    }

    /**
     * Verifica se usuário já teve trial alguma vez
     */
    public function hasHadTrial(User $user): bool
    {
        return Subscription::where('user_id', $user->id)
            ->where('is_trial', true)
            ->exists();
    }

    /**
     * Verifica se usuário tem histórico de atividades (X1 ou Fantasy)
     */
    public function hasActivityHistory(User $user): bool
    {
        // Verificar participação em X1 (como Host ou Participante)
        $hasX1Host = DB::table('x1_rooms')->where('host_user_id', $user->id)->exists();
        $hasX1Participant = DB::table('x1_participants')->where('user_id', $user->id)->exists();

        if ($hasX1Host || $hasX1Participant) {
            return true;
        }

        // Verificar participação em Fantasy (ter um time)
        $hasFantasy = DB::table('fantasy_teams')->where('user_id', $user->id)->exists();

        return $hasFantasy;
    }

    /**
     * Verifica se usuário é elegível para trial
     * - Nunca teve trial na conta
     * - CPF nunca foi usado para trial
     * - Tem CPF verificado
     * - TEM HISTÓRICO DE ATIVIDADE (X1 ou Fantasy) - Nova regra
     */
    public function isEligibleForTrial(User $user): bool
    {
        // Já teve trial na conta
        if ($this->hasHadTrial($user)) {
            return false;
        }

        // Já é premium
        if ($user->isPremium()) {
            return false;
        }

        // Verifica se tem CPF
        $cpf = $user->cpf ?? null;
        if (!$cpf) {
            // Sem CPF = não pode fazer trial
            return false;
        }

        // Verifica se CPF já usou trial
        if ($this->hasCpfUsedTrial($cpf)) {
            return false;
        }

        // Nova Regra: Precisa ter atividade no site (X1 ou Fantasy)
        if (!$this->hasActivityHistory($user)) {
            return false;
        }

        return true;
    }

    /**
     * Retorna motivo de não elegibilidade para trial
     */
    public function getTrialIneligibilityReason(User $user): ?string
    {
        if ($this->hasHadTrial($user)) {
            return 'Você já utilizou o período de teste anteriormente.';
        }

        if ($user->isPremium()) {
            return 'Você já é assinante Premium.';
        }

        $cpf = $user->cpf ?? null;
        if (!$cpf) {
            return 'Informe seu CPF no perfil para ativar o teste grátis.';
        }

        if ($this->hasCpfUsedTrial($cpf)) {
            return 'Este CPF já foi utilizado em um período de teste.';
        }

        if (!$this->hasActivityHistory($user)) {
            return 'O teste grátis é exclusivo para quem já participou de salas X1 ou ligas Fantasy.';
        }

        return null;
    }

    /**
     * Registra CPF como usado para trial
     */
    protected function registerTrialCpf(User $user, Subscription $subscription, string $cpf): void
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        
        DB::table('subscription_trial_cpfs')->insert([
            'cpf' => $cpf,
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Cria assinatura trial (3 dias grátis por atividade)
     */
    public function createTrialSubscription(User $user, SubscriptionPlan $plan): Subscription
    {
        if (!$this->isEligibleForTrial($user)) {
            $reason = $this->getTrialIneligibilityReason($user);
            throw new \Exception($reason ?? 'Usuário não é elegível para período de teste.');
        }

        // Regra fixa: 3 dias de trial
        $trialDays = 3;

        $cpf = $user->cpf ?? null;
        if (!$cpf) {
            throw new \Exception('CPF é obrigatório para ativar o teste grátis.');
        }

        return DB::transaction(function () use ($user, $plan, $cpf, $trialDays) {
            $now = now();
            $trialEndsAt = $now->copy()->addDays($trialDays);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'plano' => $plan->slug,
                'status' => Subscription::STATUS_TRIAL,
                'is_trial' => true,
                'trial_cpf' => preg_replace('/\D/', '', $cpf),
                'trial_ends_at' => $trialEndsAt,
                'data_inicio' => $now->toDateString(),
                'data_fim' => $trialEndsAt->toDateString(),
                'next_billing_date' => $trialEndsAt->toDateString(),
                'auto_renew' => true, // Para converter em paga depois (Checkout Pro não renova auto sem token, mas ok)
                'valor' => 0,
                'gateway_pagamento' => 'trial',
                'payment_method' => 'account', // Alterado de 'card' pois é ativado por elegibilidade
                'metadata' => [
                    'trial_started_at' => $now->toIso8601String(),
                    'original_plan_price' => $plan->price,
                    'cpf_hash' => hash('sha256', $cpf),
                    'trial_reason' => 'activity_reward'
                ],
            ]);

            // Registra CPF como usado
            $this->registerTrialCpf($user, $subscription, $cpf);

            Log::info('🎉 Trial de 3 dias criado', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'trial_ends_at' => $trialEndsAt->toDateTimeString(),
            ]);

            return $subscription;
        });
    }

    public function grantAppPremiumBenefit(
        User $user,
        SubscriptionPlan $plan,
        int $days = 30,
        string $platform = 'mobile'
    ): Subscription {
        if (!$this->canActivateAppPremiumBenefit($user)) {
            $reason = $this->getAppPremiumBenefitIneligibilityReason($user);
            throw new \Exception($reason ?? 'O benefício do app não está disponível para esta conta.');
        }

        return DB::transaction(function () use ($user, $plan, $days, $platform) {
            $this->cancelPreviousSubscriptions($user);

            $now = now();
            $trialEndsAt = $now->copy()->addDays(max(1, $days));

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'plano' => $plan->slug,
                'status' => Subscription::STATUS_TRIAL,
                'is_trial' => true,
                'trial_ends_at' => $trialEndsAt,
                'data_inicio' => $now->toDateString(),
                'data_fim' => $trialEndsAt->toDateString(),
                'next_billing_date' => $trialEndsAt->toDateString(),
                'auto_renew' => false,
                'valor' => 0,
                'monthly_value' => $plan->price,
                'gateway_pagamento' => 'app_bonus',
                'payment_method' => 'app',
                'metadata' => [
                    'benefit_source' => 'mobile_app_login_bonus',
                    'benefit_platform' => $platform,
                    'benefit_days' => max(1, $days),
                    'benefit_claimed_at' => $now->toIso8601String(),
                    'plan_name' => $plan->name,
                    'plan_price' => $plan->price,
                ],
            ]);

            Log::info('🎁 Benefício Premium do app ativado', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'plan' => $plan->slug,
                'days' => max(1, $days),
                'platform' => $platform,
            ]);

            return $subscription;
        });
    }

    /**
     * Cria assinatura pendente (aguardando pagamento PIX)
     */
    public function createPendingSubscription(
        User $user,
        SubscriptionPlan $plan,
        string $paymentId,
        string $paymentMethod = 'pix'
    ): Subscription {
        return DB::transaction(function () use ($user, $plan, $paymentId, $paymentMethod) {
            $now = now();

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'plano' => $plan->slug,
                'status' => Subscription::STATUS_PENDENTE,
                'is_trial' => false,
                'data_inicio' => $now->toDateString(),
                'data_fim' => $now->toDateString(), // Será atualizado quando pago
                'auto_renew' => !$plan->is_recurring, // PIX não renova auto
                'valor' => $plan->price,
                'gateway_pagamento' => 'mercadopago',
                'payment_method' => $paymentMethod,
                'transaction_id' => $paymentId,
                'metadata' => [
                    'plan_name' => $plan->name,
                    'plan_price' => $plan->price,
                    'created_at' => $now->toIso8601String(),
                    'payment_id' => $paymentId,
                ],
            ]);

            Log::info('⏳ Assinatura pendente criada', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'plan' => $plan->slug,
                'payment_id' => $paymentId,
            ]);

            return $subscription;
        });
    }

    /**
     * Cria assinatura paga
     */
    public function createPaidSubscription(
        User $user, 
        SubscriptionPlan $plan, 
        string $transactionId, 
        string $gateway = 'mercadopago'
    ): Subscription {
        return DB::transaction(function () use ($user, $plan, $transactionId, $gateway) {
            // Cancelar assinaturas anteriores
            $this->cancelPreviousSubscriptions($user);

            $now = now();
            $dataFim = $now->copy()->addDays($plan->duration_days);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'plano' => $plan->slug,
                'status' => Subscription::STATUS_ATIVA,
                'is_trial' => false,
                'data_inicio' => $now->toDateString(),
                'data_fim' => $dataFim->toDateString(),
                'next_billing_date' => $dataFim->toDateString(),
                'auto_renew' => true,
                'valor' => $plan->price,
                'gateway_pagamento' => $gateway,
                'transaction_id' => $transactionId,
                'last_payment_at' => $now,
                'metadata' => [
                    'plan_name' => $plan->name,
                    'plan_price' => $plan->price,
                    'purchased_at' => $now->toIso8601String(),
                ],
            ]);

            Log::info('✅ Assinatura paga criada', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'plan' => $plan->slug,
                'amount' => $plan->price,
            ]);

            return $subscription;
        });
    }

    /**
     * Converte trial em assinatura paga
     */
    public function convertTrialToPaid(
        Subscription $subscription, 
        string $transactionId, 
        string $gateway = 'mercadopago'
    ): Subscription {
        if (!$subscription->is_trial) {
            throw new \Exception('Esta assinatura não é um trial.');
        }

        $plan = $subscription->plan;
        if (!$plan) {
            throw new \Exception('Plano não encontrado.');
        }

        return DB::transaction(function () use ($subscription, $plan, $transactionId, $gateway) {
            $now = now();
            $dataFim = $now->copy()->addDays($plan->duration_days);

            $subscription->update([
                'status' => Subscription::STATUS_ATIVA,
                'is_trial' => false,
                'trial_ends_at' => null,
                'data_inicio' => $now->toDateString(),
                'data_fim' => $dataFim->toDateString(),
                'next_billing_date' => $dataFim->toDateString(),
                'valor' => $plan->price,
                'gateway_pagamento' => $gateway,
                'transaction_id' => $transactionId,
                'last_payment_at' => $now,
                'payment_attempts' => 0,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'converted_from_trial_at' => $now->toIso8601String(),
                ]),
            ]);

            Log::info('🔄 Trial convertido para pago', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Renova assinatura
     */
    public function renewSubscription(
        Subscription $subscription, 
        string $transactionId, 
        string $gateway = 'mercadopago'
    ): Subscription {
        if (!$subscription->canRenew()) {
            throw new \Exception('Esta assinatura não pode ser renovada.');
        }

        $plan = $subscription->plan;
        if (!$plan) {
            throw new \Exception('Plano não encontrado.');
        }

        return DB::transaction(function () use ($subscription, $plan, $transactionId, $gateway) {
            $now = now();
            
            // Se já expirou, renova a partir de AGORA (para não cobrar pelo tempo inativo)
            // Se ainda está ativa, estende a partir do final atual
            if ($subscription->data_fim->isPast()) {
                $newDataFim = $now->copy()->addDays($plan->duration_days);
            } else {
                $newDataFim = $subscription->data_fim->copy()->addDays($plan->duration_days);
            }

            $subscription->update([
                'status' => Subscription::STATUS_ATIVA, // Garante que volta a ficar ativa
                'data_fim' => $newDataFim->toDateString(),
                'next_billing_date' => $newDataFim->toDateString(),
                'transaction_id' => $transactionId,
                'gateway_pagamento' => $gateway,
                'last_payment_at' => $now,
                'payment_attempts' => 0,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'last_renewal_at' => $now->toIso8601String(),
                    'renewals' => ($subscription->metadata['renewals'] ?? 0) + 1,
                    'reactivated_from_expired' => $subscription->status === Subscription::STATUS_EXPIRADA,
                ]),
            ]);

            Log::info('🔁 Assinatura renovada', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'new_data_fim' => $newDataFim->toDateString(),
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Cancela assinatura
     */
    public function cancelSubscription(Subscription $subscription, ?string $reason = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $reason) {
            $subscription->update([
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'auto_renew' => false,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'cancelled_at' => now()->toIso8601String(),
                    'cancellation_reason' => $reason,
                ]),
            ]);

            // Nota: NÃO alteramos o status para 'cancelada' imediatamente
            // O usuário mantém acesso até data_fim
            // Um job irá mudar o status quando expirar

            Log::info('❌ Assinatura cancelada', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'reason' => $reason,
                'access_until' => $subscription->data_fim->toDateString(),
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Expira trial
     */
    public function expireTrial(Subscription $subscription): Subscription
    {
        if (!$subscription->is_trial) {
            throw new \Exception('Esta assinatura não é um trial.');
        }

        return DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => Subscription::STATUS_EXPIRADA,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'trial_expired_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info('⏰ Trial expirado', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Cancela assinaturas anteriores do usuário
     */
    protected function cancelPreviousSubscriptions(User $user): void
    {
        Subscription::where('user_id', $user->id)
            ->whereIn('status', [Subscription::STATUS_ATIVA, Subscription::STATUS_TRIAL])
            ->update([
                'status' => Subscription::STATUS_CANCELADA,
                'cancelled_at' => now(),
                'cancellation_reason' => 'Substituída por nova assinatura',
            ]);
    }

    /**
     * Retorna status detalhado da assinatura do usuário
     */
    public function getSubscriptionStatus(User $user): array
    {
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return [
                'is_premium' => false,
                'status' => 'free',
                'subscription' => null,
                'plan' => null,
                'can_trial' => $this->isEligibleForTrial($user),
                'message' => 'Você ainda não é Premium',
            ];
        }

        $plan = $subscription->plan;

        return [
            'is_premium' => true,
            'status' => $subscription->isOnTrial() ? 'trial' : $subscription->status,
            'subscription' => [
                'id' => $subscription->id,
                'is_trial' => $subscription->is_trial,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                'data_inicio' => $subscription->data_inicio->toDateString(),
                'data_fim' => $subscription->data_fim->toDateString(),
                'remaining_days' => $subscription->remaining_days,
                'auto_renew' => $subscription->auto_renew,
                'is_cancelled' => $subscription->isCancelled(),
                'status_label' => $subscription->status_label,
                'status_color' => $subscription->status_color,
            ],
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->price,
                'formatted_price' => $plan->formatted_price,
                'billing_cycle' => $plan->billing_cycle,
            ] : null,
            'can_trial' => false,
            'message' => $subscription->isOnTrial() 
                ? "Trial ativo - {$subscription->trial_remaining_days} dias restantes"
                : "Premium ativo - {$subscription->remaining_days} dias restantes",
        ];
    }

    /**
     * Lista planos disponíveis
     */
    public function getAvailablePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return SubscriptionPlan::active()->ordered()->get();
    }

    /**
     * Encontra plano por slug
     */
    public function findPlanBySlug(string $slug): ?SubscriptionPlan
    {
        return SubscriptionPlan::where('slug', $slug)->active()->first();
    }

    /**
     * Encontra plano por ID.
     */
    public function findPlanById(int $id): ?SubscriptionPlan
    {
        return SubscriptionPlan::whereKey($id)->active()->first();
    }

    /**
     * Ativa uma assinatura pendente após confirmação do pagamento.
     */
    public function activatePendingSubscription(
        Subscription $subscription,
        string $transactionId,
        string $gateway = 'mercadopago',
        array $extraMetadata = []
    ): Subscription {
        $plan = $subscription->plan;
        if (!$plan) {
            throw new \Exception('Plano não encontrado.');
        }

        return DB::transaction(function () use ($subscription, $plan, $transactionId, $gateway, $extraMetadata) {
            $now = now();
            $dataFim = $now->copy()->addDays($plan->duration_days ?? 30);

            $subscription->update([
                'status' => Subscription::STATUS_ATIVA,
                'is_trial' => false,
                'trial_ends_at' => null,
                'data_inicio' => $now->toDateString(),
                'data_fim' => $dataFim->toDateString(),
                'next_billing_date' => $dataFim->toDateString(),
                'transaction_id' => $transactionId,
                'last_payment_at' => $now,
                'gateway_pagamento' => $gateway,
                'payment_attempts' => 0,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'activated_at' => $now->toIso8601String(),
                ], $extraMetadata),
            ]);

            Log::info('✅ Assinatura pendente ativada', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'plan' => $subscription->plano,
                'transaction_id' => $transactionId,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Cria assinatura por cartão de crédito (recorrente)
     */
    public function createCardSubscription(
        User $user,
        SubscriptionPlan $plan,
        string $mpSubscriptionId,
        array $cardInfo = [],
        int $trialDays = 0
    ): Subscription {
        if (!$plan->is_recurring) {
            throw new \Exception('Este plano não suporta assinatura recorrente por cartão.');
        }

        return DB::transaction(function () use ($user, $plan, $mpSubscriptionId, $cardInfo) {
            // Cancelar assinaturas anteriores
            $this->cancelPreviousSubscriptions($user);

            $now = now();
            $effectiveTrialDays = max(0, $trialDays);
            $trialEndsAt = $effectiveTrialDays > 0
                ? $now->copy()->addDays($effectiveTrialDays)
                : null;
            
            // Se tem trial, data_fim = trial_ends_at, senão = 30 dias
            $dataFim = $trialEndsAt ?? $now->copy()->addDays(30);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'plano' => $plan->slug,
                'status' => $trialEndsAt ? Subscription::STATUS_TRIAL : Subscription::STATUS_ATIVA,
                'is_trial' => $trialEndsAt !== null,
                'trial_ends_at' => $trialEndsAt,
                'data_inicio' => $now->toDateString(),
                'data_fim' => $dataFim->toDateString(),
                'next_billing_date' => $dataFim->toDateString(),
                'auto_renew' => true,
                'valor' => $plan->price,
                'monthly_value' => $plan->price,
                'total_paid' => 0, // Trial é grátis
                'gateway_pagamento' => 'mercadopago',
                'payment_method' => 'card',
                'mp_subscription_id' => $mpSubscriptionId,
                'card_last_four' => $cardInfo['last_four'] ?? null,
                'card_brand' => $cardInfo['brand'] ?? null,
                'metadata' => [
                    'plan_name' => $plan->name,
                    'plan_price' => $plan->price,
                    'created_at' => $now->toIso8601String(),
                    'trial_days' => $effectiveTrialDays,
                    'card_info' => $cardInfo,
                ],
            ]);

            Log::info('💳 Assinatura por cartão criada', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'mp_subscription_id' => $mpSubscriptionId,
                'is_trial' => $subscription->is_trial,
            ]);

            return $subscription;
        });
    }

    /**
     * Processa cobrança de assinatura por cartão
     */
    public function processCardPayment(
        Subscription $subscription,
        string $transactionId,
        float $amount
    ): Subscription {
        return DB::transaction(function () use ($subscription, $transactionId, $amount) {
            $now = now();
            $newDataFim = $now->copy()->addDays(30);

            $subscription->update([
                'status' => Subscription::STATUS_ATIVA,
                'is_trial' => false,
                'trial_ends_at' => null,
                'data_fim' => $newDataFim->toDateString(),
                'next_billing_date' => $newDataFim->toDateString(),
                'transaction_id' => $transactionId,
                'last_payment_at' => $now,
                'total_paid' => $subscription->total_paid + $amount,
                'payment_attempts' => 0,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'last_payment_at' => $now->toIso8601String(),
                    'payments' => ($subscription->metadata['payments'] ?? 0) + 1,
                ]),
            ]);

            Log::info('💰 Pagamento de cartão processado', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'amount' => $amount,
                'total_paid' => $subscription->total_paid + $amount,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Cancela assinatura com cálculo de reembolso
     */
    public function cancelSubscriptionWithRefund(Subscription $subscription, ?string $reason = null): array
    {
        $plan = $subscription->plan;
        $refundCalc = ['refund' => 0, 'penalty' => 0, 'eligible' => true, 'message' => ''];

        // Calcular reembolso se for PIX (não recorrente)
        if ($subscription->isPixSubscription() && $plan) {
            $refundCalc = $plan->calculateRefund($subscription->valor, $subscription->days_used);
        }

        return DB::transaction(function () use ($subscription, $reason, $refundCalc) {
            $subscription->update([
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'auto_renew' => false,
                'refund_amount' => $refundCalc['refund'] ?? 0,
                'refund_status' => ($refundCalc['refund'] ?? 0) > 0 ? 'pending' : null,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'cancelled_at' => now()->toIso8601String(),
                    'cancellation_reason' => $reason,
                    'refund_calculation' => $refundCalc,
                ]),
            ]);

            // Se for cartão recorrente, precisa cancelar no Mercado Pago
            if ($subscription->isCardSubscription() && $subscription->mp_subscription_id) {
                // TODO: Chamar API do Mercado Pago para cancelar preapproval
                // $this->mercadoPago->cancelPreapproval($subscription->mp_subscription_id);
            }

            Log::info('❌ Assinatura cancelada com reembolso', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'reason' => $reason,
                'refund' => $refundCalc['refund'] ?? 0,
                'penalty' => $refundCalc['penalty'] ?? 0,
            ]);

            return [
                'subscription' => $subscription->fresh(),
                'refund' => $refundCalc,
            ];
        });
    }

    /**
     * Marca reembolso como processado
     */
    public function markRefundProcessed(Subscription $subscription, string $refundTransactionId): Subscription
    {
        return DB::transaction(function () use ($subscription, $refundTransactionId) {
            $subscription->update([
                'refund_status' => 'completed',
                'refund_transaction_id' => $refundTransactionId,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'refund_processed_at' => now()->toIso8601String(),
                    'refund_transaction_id' => $refundTransactionId,
                ]),
            ]);

            Log::info('💸 Reembolso processado', [
                'subscription_id' => $subscription->id,
                'refund_amount' => $subscription->refund_amount,
                'refund_transaction_id' => $refundTransactionId,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Encontra assinatura por ID do Mercado Pago
     */
    public function findByMpSubscriptionId(string $mpSubscriptionId): ?Subscription
    {
        return Subscription::where('mp_subscription_id', $mpSubscriptionId)->first();
    }

    /**
     * Retorna status detalhado da assinatura do usuário (atualizado)
     */
    public function getSubscriptionStatusDetailed(User $user): array
    {
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return [
                'is_premium' => false,
                'status' => 'free',
                'subscription' => null,
                'plan' => null,
                'can_trial' => $this->isEligibleForTrial($user),
                'message' => 'Você ainda não é Premium',
            ];
        }

        $plan = $subscription->plan;
        $refundCalc = $subscription->refund_calculation;

        return [
            'is_premium' => true,
            'status' => $subscription->isOnTrial() ? 'trial' : $subscription->status,
            'subscription' => [
                'id' => $subscription->id,
                'is_trial' => $subscription->is_trial,
                'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                'data_inicio' => $subscription->data_inicio->toDateString(),
                'data_fim' => $subscription->data_fim->toDateString(),
                'remaining_days' => $subscription->remaining_days,
                'days_used' => $subscription->days_used,
                'auto_renew' => $subscription->auto_renew,
                'is_cancelled' => $subscription->isCancelled(),
                'status_label' => $subscription->status_label,
                'status_color' => $subscription->status_color,
                'payment_method' => $subscription->payment_method,
                'payment_method_label' => $subscription->payment_method_label,
                'card_info' => $subscription->card_info,
                'total_paid' => $subscription->total_paid,
                'refund_available' => $refundCalc['refund'] ?? 0,
                'refund_penalty' => $refundCalc['penalty'] ?? 0,
            ],
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->price,
                'formatted_price' => $plan->formatted_price,
                'billing_cycle' => $plan->billing_cycle,
                'is_recurring' => $plan->is_recurring,
                'payment_methods' => $plan->payment_methods,
            ] : null,
            'can_trial' => false,
            'message' => $subscription->isOnTrial() 
                ? "Trial ativo - {$subscription->trial_remaining_days} dias restantes"
                : "Premium ativo - {$subscription->remaining_days} dias restantes",
        ];
    }
}
