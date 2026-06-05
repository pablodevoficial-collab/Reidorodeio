<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job para expirar trials e assinaturas vencidas
 * Deve ser agendado para rodar a cada hora via scheduler
 */
class ExpireTrialSubscriptions implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('subscriptions');
    }

    /**
     * Execute the job.
     */
    public function handle(SubscriptionService $service): void
    {
        Log::info('🔄 Iniciando verificação de trials e assinaturas expiradas');

        $expiredCount = 0;
        $trialExpiredCount = 0;

        // 1. Expirar trials que passaram da data
        $expiredTrials = Subscription::where('status', Subscription::STATUS_TRIAL)
            ->where('is_trial', true)
            ->where(function ($q) {
                $q->where('trial_ends_at', '<', now())
                  ->orWhere('data_fim', '<', now());
            })
            ->get();

        foreach ($expiredTrials as $subscription) {
            try {
                $service->expireTrial($subscription);
                $trialExpiredCount++;
                
                Log::info('⏰ Trial expirado automaticamente', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao expirar trial', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2. Expirar assinaturas pagas que passaram da data_fim e foram canceladas
        $expiredPaid = Subscription::where('status', Subscription::STATUS_ATIVA)
            ->whereNotNull('cancelled_at')
            ->where('data_fim', '<', now())
            ->get();

        foreach ($expiredPaid as $subscription) {
            try {
                $subscription->update([
                    'status' => Subscription::STATUS_EXPIRADA,
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'expired_at' => now()->toIso8601String(),
                        'expired_reason' => 'cancellation_period_ended',
                    ]),
                ]);
                $expiredCount++;
                
                Log::info('📅 Assinatura cancelada expirada', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao expirar assinatura', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3. Marcar como pendentes assinaturas ativas que passaram da data de cobrança
        // (para serem processadas pelo ProcessSubscriptionBilling)
        $needsBilling = Subscription::where('status', Subscription::STATUS_ATIVA)
            ->whereNull('cancelled_at')
            ->where('auto_renew', true)
            ->where('next_billing_date', '<=', now())
            ->where('data_fim', '<=', now()->addDays(3)) // 3 dias de margem
            ->update([
                'status' => Subscription::STATUS_PENDENTE,
            ]);

        // 4. Expirar assinaturas ativas NÃO renováveis que venceram
        // (ex: PIX avulso que chegou ao fim do prazo)
        $expiredNonRenewing = Subscription::where('status', Subscription::STATUS_ATIVA)
            ->where('auto_renew', false)
            ->where('data_fim', '<', now())
            ->get();

        foreach ($expiredNonRenewing as $subscription) {
            try {
                $subscription->update([
                    'status' => Subscription::STATUS_EXPIRADA,
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'expired_at' => now()->toIso8601String(),
                        'expired_reason' => 'non_renewing_period_ended',
                    ]),
                ]);
                $expiredCount++;

                Log::info('📅 Assinatura não-renovável expirada', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao expirar assinatura não-renovável', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('✅ Verificação de expiração concluída', [
            'trials_expired' => $trialExpiredCount,
            'subscriptions_expired' => $expiredCount,
            'marked_for_billing' => $needsBilling,
        ]);
    }
}
