<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\User;
use App\Services\MercadoPagoService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Job para processar cobranças de assinaturas
 * Gera PIX para renovação e notifica usuários
 * Deve ser agendado diariamente via scheduler
 */
class ProcessSubscriptionBilling implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 300; // 5 minutos entre tentativas

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
    public function handle(MercadoPagoService $mp, SubscriptionService $service): void
    {
        Log::info('💳 Iniciando processamento de cobranças de assinatura');

        $processed = 0;
        $failed = 0;

        // Buscar assinaturas que precisam de cobrança
        // - Status pendente (marcadas pelo ExpireTrialSubscriptions)
        // - Ou ativas com next_billing_date próxima (3 dias) e auto_renew
        $subscriptions = Subscription::where(function ($q) {
                $q->where('status', Subscription::STATUS_PENDENTE)
                  ->orWhere(function ($q2) {
                      $q2->where('status', Subscription::STATUS_ATIVA)
                         ->whereNull('cancelled_at')
                         ->where('auto_renew', true)
                         ->whereBetween('next_billing_date', [
                             now()->subDay(),
                             now()->addDays(3)
                         ]);
                  });
            })
            ->where('is_trial', false)
            ->whereNotNull('subscription_plan_id')
            ->with(['user', 'plan'])
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $this->processSubscription($subscription, $mp);
                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                $this->handleBillingFailure($subscription, $e);
            }
        }

        Log::info('✅ Processamento de cobranças concluído', [
            'processed' => $processed,
            'failed' => $failed,
        ]);
    }

    /**
     * Processa cobrança de uma assinatura
     */
    protected function processSubscription(Subscription $subscription, MercadoPagoService $mp): void
    {
        $user = $subscription->user;
        $plan = $subscription->plan;

        if (!$user || !$plan) {
            throw new \Exception('Usuário ou plano não encontrado');
        }

        // Verificar se já existe pagamento pendente recente (últimas 24h)
        $recentPayment = $subscription->metadata['pending_payment_id'] ?? null;
        if ($recentPayment) {
            $paymentCreatedAt = $subscription->metadata['pending_payment_created_at'] ?? null;
            if ($paymentCreatedAt && now()->diffInHours($paymentCreatedAt) < 24) {
                Log::info('⏳ Pagamento pendente ainda válido', [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $recentPayment,
                ]);
                return;
            }
        }

        // Gerar external_reference único
        $externalRef = 'SUB_' . $subscription->id . '_' . Str::random(8);

        // Criar pagamento PIX
        $paymentData = [
            'transaction_amount' => (float) $plan->price,
            'description' => "Renovação Premium {$plan->name} - Rei do Rodeio",
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $user->email,
                'first_name' => explode(' ', $user->name)[0] ?? 'Cliente',
                'last_name' => explode(' ', $user->name)[1] ?? 'Premium',
            ],
            'external_reference' => $externalRef,
            'notification_url' => config('app.url') . '/api/webhooks/subscription',
        ];

        $response = $mp->createPixPayment($paymentData);

        // Salvar referência do pagamento
        $subscription->update([
            'status' => Subscription::STATUS_PENDENTE,
            'payment_attempts' => ($subscription->payment_attempts ?? 0) + 1,
            'metadata' => array_merge($subscription->metadata ?? [], [
                'pending_payment_id' => $response['id'] ?? null,
                'pending_payment_created_at' => now()->toIso8601String(),
                'external_reference' => $externalRef,
                'pix_qr_code' => $response['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                'pix_qr_code_base64' => $response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
            ]),
        ]);

        Log::info('📧 Cobrança de renovação gerada', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'payment_id' => $response['id'] ?? null,
            'amount' => $plan->price,
        ]);

        // TODO: Enviar email/notificação push para o usuário pagar
        // Notification::send($user, new SubscriptionRenewalDue($subscription, $response));
    }

    /**
     * Trata falha de cobrança
     */
    protected function handleBillingFailure(Subscription $subscription, \Throwable $e): void
    {
        $attempts = ($subscription->payment_attempts ?? 0) + 1;
        
        $subscription->update([
            'payment_attempts' => $attempts,
            'last_payment_error' => $e->getMessage(),
            'metadata' => array_merge($subscription->metadata ?? [], [
                'last_billing_error' => $e->getMessage(),
                'last_billing_attempt' => now()->toIso8601String(),
            ]),
        ]);

        Log::error('❌ Falha na cobrança de assinatura', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'attempts' => $attempts,
            'error' => $e->getMessage(),
        ]);

        // Após 3 tentativas, expirar a assinatura
        if ($attempts >= 3) {
            $subscription->update([
                'status' => Subscription::STATUS_EXPIRADA,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'expired_reason' => 'billing_failed_max_attempts',
                    'expired_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::warning('⚠️ Assinatura expirada por falha de pagamento', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);
        }
    }
}
