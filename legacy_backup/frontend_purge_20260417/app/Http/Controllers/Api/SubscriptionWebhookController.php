<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Services\MercadoPagoService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Webhook controller para processar pagamentos de assinatura premium
 */
class SubscriptionWebhookController extends Controller
{
    public function __construct(
        protected MercadoPagoService $mercadoPago,
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle incoming webhook from MercadoPago
     */
    public function __invoke(Request $request)
    {
        Log::info('🔔 Webhook Subscription MercadoPago recebido', [
            'body' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Obter payment ID
        $paymentId = $request->input('data.id') ?? $request->input('id');
        $type = $request->input('type') ?? $request->input('action');

        if (!$paymentId) {
            Log::warning('⚠️ Webhook subscription sem payment ID');
            return response()->json(['message' => 'Missing payment id'], 400);
        }

        // Verificar se é notificação de pagamento
        if (!in_array($type, ['payment', 'payment.created', 'payment.updated'])) {
            Log::info('ℹ️ Webhook tipo ignorado', ['type' => $type]);
            return response()->json(['ok' => true, 'ignored' => true]);
        }

        Log::info('📥 Processando pagamento de assinatura', ['payment_id' => $paymentId]);

        try {
            // Buscar dados do pagamento no MercadoPago
            $payment = $this->mercadoPago->fetchPayment((string) $paymentId);
            
            Log::info('💳 Dados do pagamento subscription', [
                'payment_id' => $paymentId,
                'status' => $payment['status'] ?? 'unknown',
                'external_reference' => $payment['external_reference'] ?? null,
            ]);

            $externalRef = $payment['external_reference'] ?? null;
            if (!$externalRef) {
                Log::warning('⚠️ Sem external_reference no pagamento');
                return response()->json(['message' => 'Missing external reference'], 400);
            }

            // Verificar se é pagamento de subscription (formato: SUB_{id}_{random} ou PREMIUM_{user_id}_{timestamp})
            if (!str_starts_with($externalRef, 'SUB_') && !str_starts_with($externalRef, 'PREMIUM_')) {
                Log::info('ℹ️ Pagamento não é de subscription', ['external_ref' => $externalRef]);
                return response()->json(['ok' => true, 'not_subscription' => true]);
            }

            $paymentStatus = $payment['status'] ?? 'pending';

            // Só processar se aprovado
            if ($paymentStatus !== 'approved') {
                Log::info('⏳ Pagamento subscription ainda não aprovado', [
                    'status' => $paymentStatus,
                    'external_ref' => $externalRef,
                ]);
                return response()->json(['ok' => true, 'pending' => true]);
            }

            // Processar pagamento aprovado
            return $this->processApprovedPayment($externalRef, $payment, $paymentId);

        } catch (\Throwable $e) {
            Log::error('❌ Erro ao processar webhook subscription', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Processing error'], 500);
        }
    }

    /**
     * Processa pagamento aprovado
     */
    protected function processApprovedPayment(string $externalRef, array $payment, string $paymentId)
    {
        return DB::transaction(function () use ($externalRef, $payment, $paymentId) {
            
            // Formato SUB_{subscription_id}_{random}
            if (str_starts_with($externalRef, 'SUB_')) {
                return $this->processSubscriptionRenewal($externalRef, $payment, $paymentId);
            }
            
            // Formato PREMIUM_{user_id}_{timestamp}
            if (str_starts_with($externalRef, 'PREMIUM_')) {
                return $this->processNewSubscription($externalRef, $payment, $paymentId);
            }

            return response()->json(['error' => 'Unknown reference format'], 400);
        });
    }

    /**
     * Processa renovação de assinatura existente
     */
    protected function processSubscriptionRenewal(string $externalRef, array $payment, string $paymentId)
    {
        // Extrair subscription_id do external_reference (SUB_{id}_{random})
        $parts = explode('_', $externalRef);
        $subscriptionId = $parts[1] ?? null;

        if (!$subscriptionId) {
            Log::warning('⚠️ Não foi possível extrair subscription_id', ['external_ref' => $externalRef]);
            return response()->json(['error' => 'Invalid external reference'], 400);
        }

        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            Log::warning('⚠️ Subscription não encontrada', ['subscription_id' => $subscriptionId]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // Verificar se já foi processado
        if ($subscription->transaction_id === $paymentId) {
            Log::info('ℹ️ Pagamento já processado', ['payment_id' => $paymentId]);
            return response()->json(['ok' => true, 'already_processed' => true]);
        }

        // Processar renovação ou conversão de trial
        if ($subscription->is_trial) {
            $this->subscriptionService->convertTrialToPaid($subscription, $paymentId, 'mercadopago');
            Log::info('🔄 Trial convertido para pago via webhook', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);
        } else {
            $this->subscriptionService->renewSubscription($subscription, $paymentId, 'mercadopago');
            Log::info('🔁 Assinatura renovada via webhook', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
            ]);
        }

        return response()->json(['ok' => true, 'renewed' => true]);
    }

    /**
     * Processa nova assinatura
     */
    protected function processNewSubscription(string $externalRef, array $payment, string $paymentId)
    {
        // Extrair user_id do external_reference (PREMIUM_{user_id}_{timestamp})
        $parts = explode('_', $externalRef);
        $userId = $parts[1] ?? null;

        if (!$userId) {
            Log::warning('⚠️ Não foi possível extrair user_id', ['external_ref' => $externalRef]);
            return response()->json(['error' => 'Invalid external reference'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            Log::warning('⚠️ Usuário não encontrado', ['user_id' => $userId]);
            return response()->json(['error' => 'User not found'], 404);
        }

        // Buscar subscription pendente do usuário
        $subscription = Subscription::where('user_id', $userId)
            ->where('status', Subscription::STATUS_PENDENTE)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$subscription) {
            Log::warning('⚠️ Subscription pendente não encontrada', ['user_id' => $userId]);
            return response()->json(['error' => 'Pending subscription not found'], 404);
        }

        // Verificar se já foi processado
        if ($subscription->transaction_id === $paymentId) {
            Log::info('ℹ️ Pagamento já processado', ['payment_id' => $paymentId]);
            return response()->json(['ok' => true, 'already_processed' => true]);
        }

        // Ativar assinatura
        $subscription = $this->subscriptionService->activatePendingSubscription(
            $subscription,
            $paymentId,
            'mercadopago',
            [
                'activated_via' => 'webhook',
                'payment_amount' => $payment['transaction_amount'] ?? null,
            ]
        );

        Log::info('✅ Nova assinatura ativada via webhook', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'plan' => $subscription->plano,
        ]);

        return response()->json(['ok' => true, 'activated' => true]);
    }
}
