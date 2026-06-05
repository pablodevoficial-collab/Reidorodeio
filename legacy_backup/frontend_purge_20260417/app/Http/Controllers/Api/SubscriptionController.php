<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    private const PREMIUM_TRIAL_DAYS = 3;

    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Lista planos disponíveis
     * GET /api/subscriptions/plans
     */
    public function plans()
    {
        $plans = $this->subscriptionService->getAvailablePlans();
        $user = auth('sanctum')->user();

        $canTrial = $user ? $this->subscriptionService->isEligibleForTrial($user) : false;
        $trialReason = $user ? $this->subscriptionService->getTrialIneligibilityReason($user) : 'Faça login para ver se você tem direito ao teste grátis.';

        return response()->json([
            'success' => true,
            'plans' => $plans->map(function ($plan) use ($canTrial) {
                $normalizedDescription = $this->normalizeTrialCopy($plan->description);
                $normalizedBadge = $this->normalizeTrialCopy($plan->badge);
                $normalizedFeatures = $this->normalizeTrialFeatures($plan->features ?? []);

                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => (float) $plan->price,
                    'original_price' => (float) $plan->original_price,
                    'formatted_price' => $plan->formatted_price,
                    'formatted_monthly_price' => $plan->formatted_monthly_price,
                    'monthly_price' => (float) $plan->monthly_price,
                    'duration_days' => $plan->duration_days,
                    'trial_days' => $canTrial ? self::PREMIUM_TRIAL_DAYS : 0,
                    'billing_cycle' => $plan->billing_cycle,
                    'period_label' => $plan->period_label,
                    'description' => $normalizedDescription,
                    'features' => $normalizedFeatures,
                    'payment_methods' => $plan->payment_methods ?? ['pix', 'card'], // Checkout Pro aceita tudo
                    'is_recurring' => $plan->is_recurring ?? false,
                    'badge' => $normalizedBadge,
                    'badge_color' => $plan->badge_color,
                    'is_featured' => $plan->is_featured,
                    'savings' => $plan->savings,
                    'free_months' => $plan->free_months,
                    'has_trial' => $canTrial,
                    // Regras de cancelamento para exibir no frontend
                    'cancel_rules' => [
                        'min_days' => $plan->min_days_for_full_refund,
                        'penalty_months' => $plan->early_cancel_penalty_months,
                        'penalty_amount' => round($plan->early_cancel_penalty_months * 49.90, 2),
                    ],
                ];
            }),
            'can_trial' => $canTrial,
            'trial_days_label' => $canTrial ? self::PREMIUM_TRIAL_DAYS . ' dias' : null,
            'trial_reason' => $canTrial ? null : $trialReason,
            'user_has_cpf' => $user && !empty($user->cpf),
        ]);
    }

    /**
     * Retorna status da assinatura do usuário
     * GET /api/subscriptions/status
     */
    public function status(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $status = $this->subscriptionService->getSubscriptionStatus($user);

        return response()->json([
            'success' => true,
            ...$status,
        ]);
    }

    /**
     * Inicia trial gratuito
     * POST /api/subscriptions/start-trial
     */
    public function startTrial(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
        ]);

        $plan = $this->subscriptionService->findPlanBySlug($validated['plan_slug']);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não encontrado',
            ], 404);
        }

        if (!$this->subscriptionService->isEligibleForTrial($user)) {
            $reason = $this->subscriptionService->getTrialIneligibilityReason($user);
            return response()->json([
                'success' => false,
                'message' => $reason ?? 'Você não é elegível para o período de teste. Exclusivo para quem já participou de X1 ou Fantasy.',
            ], 422);
        }

        try {
            // Agora cria sempre 3 dias independente do plano
            $subscription = $this->subscriptionService->createTrialSubscription($user, $plan);

            return response()->json([
                'success' => true,
                'message' => "🎉 Trial de 3 dias ativado com sucesso!",
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'trial_ends_at' => $subscription->trial_ends_at->toIso8601String(),
                    'remaining_days' => $subscription->remaining_days,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar trial', [
                'user_id' => $user->id,
                'plan' => $plan->slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cria preferência de pagamento para Brick
     * POST /api/subscriptions/create-preference
     */
    public function createPreference(Request $request)
    {
        $user = $request->user('sanctum');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
            'start_trial' => 'nullable|boolean',
        ]);

        $plan = $this->subscriptionService->findPlanBySlug($validated['plan_slug']);
        $isTrial = filter_var($validated['start_trial'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Se for trial, valor simbólico de R$ 1,00 para validação
        $amount = $isTrial ? 1.00 : (float) $plan->price;
        
        $mp = app(MercadoPagoService::class);
        
        // Checkout Pro: Não restringimos métodos para garantir conversão, 
        // a menos que seja Trial (aí geralmente precisa de cartão para estorno)
        // Mas como o usuário pediu "Checkout Pro para mensal/semestral/anual", liberamos tudo.
        
        $excludedMethods = [];
        $excludedTypes = [['id' => 'ticket']]; // Excluir boleto por padrão (baixa conversão)
        
        if ($isTrial) {
            // Trial exige cartão para garantir identidade
            $excludedMethods[] = ['id' => 'pix'];
        }

        try {
            $preferencePayload = [
                'items' => [[
                    'title' => 'Assinatura ' . $plan->name . ($isTrial ? ' (Trial)' : ''),
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'currency_id' => 'BRL',
                ]],
                'payer' => [
                    'email' => $user->email,
                    'name' => $user->firstname ?? $user->username,
                    'entity_type' => 'individual',
                ],
                'external_reference' => 'sub:' . $plan->id . '|user:' . $user->id . '|trial:' . ($isTrial ? '1' : '0'),
                'back_urls' => [
                    'success' => route('premium.landing', ['status' => 'success']),
                    'failure' => route('premium.landing', ['status' => 'failure']),
                    'pending' => route('premium.landing', ['status' => 'pending']),
                ],
                // 'auto_return' => 'approved', // Removido para evitar erro de validação em ambiente local
                'payment_methods' => [
                    'excluded_payment_methods' => $excludedMethods,
                    'excluded_payment_types' => $excludedTypes,
                    'installments' => 1
                ],
                'statement_descriptor' => 'REIDORODEIO',
            ];

            $preference = $mp->createPreference($preferencePayload);

            return response()->json([
                'success' => true,
                'preference_id' => $preference['id'],
                'init_point' => $preference['init_point'], // URL de redirecionamento do Checkout Pro
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar preferência de assinatura', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao criar pagamento'], 500);
        }
    }

    /**
     * Inicia processo de assinatura paga
     * POST /api/subscriptions/subscribe
     * 
     * @param plan_slug string - Slug do plano (mensal, semestral, anual)
     * @param payment_method string - Método de pagamento (pix ou card)
     */
    public function subscribe(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
            'payment_method' => 'required|string|in:pix,card',
        ]);

        $plan = $this->subscriptionService->findPlanBySlug($validated['plan_slug']);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não encontrado',
            ], 404);
        }

        // Verifica se o plano aceita o método de pagamento
        $allowedMethods = $plan->payment_methods ?? ['pix'];
        if (!in_array($validated['payment_method'], $allowedMethods)) {
            return response()->json([
                'success' => false,
                'message' => 'Este plano não aceita pagamento por ' . ($validated['payment_method'] === 'pix' ? 'PIX' : 'cartão'),
            ], 422);
        }

        // Se for cartão, redireciona para checkout do MercadoPago
        if ($validated['payment_method'] === 'card') {
            return $this->subscribeCard($request);
        }

        // PIX: gera QR Code
        try {
            $mpService = app(MercadoPagoService::class);
            
            $externalRef = 'PREMIUM_' . $user->id . '_' . time() . '_' . Str::random(6);

            $pixData = [
                'transaction_amount' => (float) $plan->price,
                'description' => $plan->name . ' - ' . config('app.name'),
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $user->email,
                    'first_name' => $user->firstname ?? explode(' ', $user->name ?? 'Cliente')[0],
                    'last_name' => $user->lastname ?? '',
                    'entity_type' => 'individual',
                ],
                'external_reference' => $externalRef,
                'notification_url' => route('api.webhooks.subscription'),
            ];

            $pixPayment = $mpService->createPixPayment($pixData);

            $qrCode = $pixPayment['point_of_interaction']['transaction_data']['qr_code'] ?? null;
            $qrCodeBase64 = $pixPayment['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
            $paymentId = $pixPayment['id'] ?? null;

            // Criar subscription pendente
            $subscription = $this->subscriptionService->createPendingSubscription($user, $plan, $paymentId, 'pix');

            Log::info('💳 PIX gerado para assinatura', [
                'user_id' => $user->id,
                'plan' => $plan->slug,
                'payment_id' => $paymentId,
                'subscription_id' => $subscription->id,
            ]);

            return response()->json([
                'success' => true,
                'payment_method' => 'pix',
                'payment' => [
                    'id' => $paymentId,
                    'external_reference' => $externalRef,
                    'qr_code' => $qrCode,
                    'qr_code_base64' => $qrCodeBase64 ? 'data:image/png;base64,' . $qrCodeBase64 : null,
                    'pix_code' => $qrCode, // Código copia-e-cola
                    'amount' => (float) $plan->price,
                    'formatted_amount' => $plan->formatted_price,
                    'expires_at' => now()->addMinutes(30)->toIso8601String(),
                ],
                'plan' => [
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => $plan->formatted_price,
                    'duration_days' => $plan->duration_days,
                ],
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento PIX de assinatura', [
                'user_id' => $user->id,
                'plan' => $plan->slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PIX. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Verifica status do pagamento
     * GET /api/subscriptions/payment/{paymentId}/status
     */
    public function checkPaymentStatus(Request $request, string $paymentId)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        try {
            $mpService = app(MercadoPagoService::class);
            $payment = $mpService->fetchPayment($paymentId);

            $status = $payment['status'] ?? 'pending';

            if ($status === 'approved') {
                $existingSubscription = $user->subscriptions()
                    ->with('plan')
                    ->where('transaction_id', $paymentId)
                    ->latest('id')
                    ->first();

                if ($existingSubscription) {
                    if ($existingSubscription->status === Subscription::STATUS_PENDENTE) {
                        $existingSubscription = $this->subscriptionService->activatePendingSubscription(
                            $existingSubscription,
                            $paymentId,
                            'mercadopago',
                            [
                                'activated_via' => 'polling',
                                'payment_amount' => $payment['transaction_amount'] ?? null,
                                'external_reference' => $payment['external_reference'] ?? null,
                            ]
                        );
                    }

                    return response()->json([
                        'success' => true,
                        'status' => 'approved',
                        'message' => '🎉 Pagamento aprovado! Bem-vindo ao Premium!',
                        'subscription' => $this->buildApprovedSubscriptionPayload($existingSubscription),
                    ]);
                }

                $plan = $this->resolvePlanFromExternalReference(
                    (string) ($payment['external_reference'] ?? ''),
                    (int) $user->id
                );

                if ($plan) {
                    $subscription = $this->subscriptionService->createPaidSubscription(
                        $user,
                        $plan,
                        $paymentId
                    );

                    $subscription->update([
                        'payment_method' => ($payment['payment_method_id'] ?? null) === 'pix' ? 'pix' : 'card',
                        'auto_renew' => ($payment['payment_method_id'] ?? null) !== 'pix',
                        'metadata' => array_merge($subscription->metadata ?? [], [
                            'activated_via' => 'polling',
                            'external_reference' => $payment['external_reference'] ?? null,
                            'payment_amount' => $payment['transaction_amount'] ?? null,
                        ]),
                    ]);

                    return response()->json([
                        'success' => true,
                        'status' => 'approved',
                        'message' => '🎉 Pagamento aprovado! Bem-vindo ao Premium!',
                        'subscription' => $this->buildApprovedSubscriptionPayload($subscription->fresh()),
                    ]);
                }

                Log::warning('Pagamento premium aprovado sem plano resolvido', [
                    'payment_id' => $paymentId,
                    'user_id' => $user->id,
                    'external_reference' => $payment['external_reference'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'approved',
                    'message' => 'Pagamento aprovado. Aguarde a sincronização da assinatura.',
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => match($status) {
                    'pending' => 'Aguardando pagamento...',
                    'in_process' => 'Processando pagamento...',
                    'rejected' => 'Pagamento rejeitado',
                    'cancelled' => 'Pagamento cancelado',
                    default => 'Status: ' . $status,
                },
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar pagamento', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar pagamento',
            ], 500);
        }
    }

    /**
     * Cancela assinatura
     * POST /api/subscriptions/cancel
     */
    public function cancel(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $subscription = $user->getCurrentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Você não possui assinatura ativa',
            ], 422);
        }

        try {
            $subscription = $this->subscriptionService->cancelSubscription(
                $subscription,
                $validated['reason'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Assinatura cancelada. Você ainda terá acesso até ' . $subscription->data_fim->format('d/m/Y'),
                'access_until' => $subscription->data_fim->toDateString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reativa auto-renovação
     * POST /api/subscriptions/reactivate
     */
    public function reactivate(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $subscription = $user->getCurrentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Você não possui assinatura ativa',
            ], 422);
        }

        if (!$subscription->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Sua assinatura não está cancelada',
            ], 422);
        }

        $subscription->update([
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'auto_renew' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Auto-renovação reativada com sucesso!',
        ]);
    }

    /**
     * Inicia assinatura por cartão de crédito (Mercado Pago Subscriptions)
     * POST /api/subscriptions/subscribe-card
     */
    public function subscribeCard(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
        ]);

        $plan = $this->subscriptionService->findPlanBySlug($validated['plan_slug']);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não encontrado',
            ], 404);
        }

        // Verifica se o plano aceita cartão
        $allowedMethods = $plan->payment_methods ?? ['pix'];
        if (!in_array('card', $allowedMethods)) {
            return response()->json([
                'success' => false,
                'message' => 'Este plano não aceita pagamento por cartão.',
            ], 422);
        }

        try {
            $mpService = app(MercadoPagoService::class);
            $eligibleForTrial = $this->subscriptionService->isEligibleForTrial($user);
            
            // Para planos recorrentes (mensal), usa preapproval
            if ($plan->is_recurring) {
                $preapprovalData = [
                    'reason' => $plan->name . ' - ' . config('app.name'),
                    'auto_recurring' => [
                        'frequency' => 1,
                        'frequency_type' => 'months',
                        'transaction_amount' => (float) $plan->price,
                        'currency_id' => 'BRL',
                    ],
                    'back_url' => route('premium.callback'),
                    'payer_email' => $user->email,
                    'external_reference' => 'CARD_SUB|' . $user->id . '|' . $plan->slug . '|trial:' . ($eligibleForTrial ? '1' : '0') . '|' . Str::random(8),
                ];

                // Adiciona trial se elegível
                if ($eligibleForTrial) {
                    $preapprovalData['auto_recurring']['free_trial'] = [
                        'frequency' => self::PREMIUM_TRIAL_DAYS,
                        'frequency_type' => 'days',
                    ];
                }

                $preapproval = $mpService->createPreapproval($preapprovalData);

                if (!empty($preapproval['init_point'])) {
                    return response()->json([
                        'success' => true,
                        'checkout_url' => $preapproval['init_point'],
                        'preapproval_id' => $preapproval['id'] ?? null,
                        'message' => 'Redirecionando para checkout seguro...',
                    ]);
                }

                throw new \Exception('Não foi possível criar a assinatura recorrente');
            }
            
            // Para planos não-recorrentes (semestral/anual), usa preference (checkout único)
            $preferenceData = [
                'items' => [[
                    'title' => $plan->name . ' - ' . config('app.name'),
                    'quantity' => 1,
                    'unit_price' => (float) $plan->price,
                    'currency_id' => 'BRL',
                ]],
                'payer' => [
                    'email' => $user->email,
                    'name' => $user->name ?? 'Cliente',
                ],
                'back_urls' => [
                    'success' => route('premium.callback', ['status' => 'approved']),
                    'failure' => route('premium.callback', ['status' => 'rejected']),
                    'pending' => route('premium.callback', ['status' => 'pending']),
                ],
                // 'auto_return' => 'approved', 
                'external_reference' => 'CARD_SINGLE|' . $user->id . '|' . $plan->slug . '|' . Str::random(8),
                'notification_url' => route('api.webhooks.subscription'),
            ];

            $preference = $mpService->createPreference($preferenceData);

            if (!empty($preference['init_point'])) {
                // Criar subscription pendente
                $subscription = $this->subscriptionService->createPendingSubscription($user, $plan, $preference['id'] ?? 'pref_' . Str::random(8), 'card');

                return response()->json([
                    'success' => true,
                    'checkout_url' => $preference['init_point'],
                    'preference_id' => $preference['id'] ?? null,
                    'subscription_id' => $subscription->id,
                    'message' => 'Redirecionando para checkout seguro...',
                ]);
            }

            throw new \Exception('Não foi possível criar o checkout');

        } catch (\Exception $e) {
            Log::error('Erro ao criar assinatura por cartão', [
                'user_id' => $user->id,
                'plan' => $plan->slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar assinatura: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Callback após pagamento no Mercado Pago
     * GET /premium/callback
     */
    public function callback(Request $request)
    {
        $preapprovalId = $request->get('preapproval_id');
        $status = $request->get('status');
        $externalReference = $request->get('external_reference');

        if ($status === 'authorized' && $preapprovalId) {
            // Assinatura autorizada
            try {
                // Extrair dados do external_reference
                $parts = explode('|', $externalReference);
                $userId = $parts[1] ?? null;
                $planSlug = $parts[2] ?? null;
                $trialDays = 0;
                foreach ($parts as $part) {
                    if ($part === 'trial:1') {
                        $trialDays = self::PREMIUM_TRIAL_DAYS;
                        break;
                    }
                }

                if ($userId && $planSlug) {
                    $user = \App\Models\User::find($userId);
                    $plan = $this->subscriptionService->findPlanBySlug($planSlug);

                    if ($user && $plan) {
                        if ($trialDays === 0 && $plan->is_recurring && $this->subscriptionService->isEligibleForTrial($user)) {
                            $trialDays = self::PREMIUM_TRIAL_DAYS;
                        }

                        // Criar assinatura no sistema
                        $this->subscriptionService->createCardSubscription(
                            $user,
                            $plan,
                            $preapprovalId,
                            [],
                            $trialDays
                        );

                        return redirect()->route('home')
                            ->with('success', '🎉 Assinatura Premium ativada com sucesso!');
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erro no callback de assinatura', [
                    'preapproval_id' => $preapprovalId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Redirecionar com mensagem de erro ou pendência
        $message = match($status) {
            'authorized' => 'Assinatura ativada!',
            'pending' => 'Assinatura pendente de confirmação',
            'cancelled' => 'Assinatura cancelada',
            default => 'Processo de assinatura finalizado',
        };

        return redirect()->route('home', ['section' => 'premium'])
            ->with($status === 'authorized' ? 'success' : 'info', $message);
    }

    /**
     * Cancela assinatura com cálculo de reembolso
     * POST /api/subscriptions/cancel-with-refund
     */
    public function cancelWithRefund(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'confirm_refund' => 'required|boolean',
        ]);

        $subscription = $user->getCurrentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Você não possui assinatura ativa',
            ], 422);
        }

        try {
            $result = $this->subscriptionService->cancelSubscriptionWithRefund(
                $subscription,
                $validated['reason'] ?? null
            );

            $refund = $result['refund'];
            $subscription = $result['subscription'];

            $message = 'Assinatura cancelada.';
            
            if ($subscription->isCardSubscription()) {
                $message .= ' A cobrança no cartão foi interrompida.';
            } elseif ($refund['refund'] > 0) {
                $message .= sprintf(
                    ' Reembolso de R$ %.2f será processado em até 7 dias úteis.',
                    $refund['refund']
                );
                if ($refund['penalty'] > 0) {
                    $message .= sprintf(' (Multa de R$ %.2f aplicada)', $refund['penalty']);
                }
            }

            $message .= ' Você terá acesso até ' . $subscription->data_fim->format('d/m/Y');

            return response()->json([
                'success' => true,
                'message' => $message,
                'refund' => $refund,
                'access_until' => $subscription->data_fim->toDateString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Calcula prévia do reembolso
     * GET /api/subscriptions/refund-preview
     */
    public function refundPreview(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $subscription = $user->getCurrentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Você não possui assinatura ativa',
            ], 422);
        }

        $refundCalc = $subscription->refund_calculation;

        return response()->json([
            'success' => true,
            'payment_method' => $subscription->payment_method,
            'days_used' => $subscription->days_used,
            'refund' => $refundCalc,
            'is_card' => $subscription->isCardSubscription(),
            'message' => $subscription->isCardSubscription()
                ? 'Assinatura por cartão: cancele quando quiser sem multa.'
                : $refundCalc['message'] ?? '',
        ]);
    }

    /**
     * Processa pagamento de assinatura com cartão inline (Checkout Transparente)
     * POST /api/subscriptions/process-card
     * 
     * Este endpoint recebe um card_token gerado pelo SDK do Mercado Pago no frontend
     * e processa o pagamento sem redirecionar o usuário.
     */
    public function processCardInline(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
            'card_token' => 'required|string',
            'payment_method_id' => 'required|string', // visa, mastercard, etc
            'issuer_id' => 'nullable|string',
            'installments' => 'nullable|integer|min:1|max:12',
            'payer_email' => 'nullable|email',
            'identification_type' => 'nullable|string',
            'identification_number' => 'nullable|string',
            'start_trial' => 'nullable|boolean', // Se true, inicia trial de 3 dias
        ]);

        $plan = $this->subscriptionService->findPlanBySlug($validated['plan_slug']);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plano não encontrado',
            ], 404);
        }

        // Verifica se o plano aceita cartão
        $allowedMethods = $plan->payment_methods ?? ['pix'];
        if (!in_array('card', $allowedMethods)) {
            return response()->json([
                'success' => false,
                'message' => 'Este plano não aceita pagamento por cartão.',
            ], 422);
        }

        $mpService = app(MercadoPagoService::class);

        try {
            // Se está iniciando trial, valida cartão com R$1 e estorna
            $startTrial = $validated['start_trial'] ?? false;
            $canTrial = $startTrial && $this->subscriptionService->isEligibleForTrial($user);
            
            if ($canTrial) {
                return $this->processTrialWithCard($request, $user, $plan, $validated, $mpService);
            }

            // Pagamento direto (sem trial)
            return $this->processDirectCardPayment($user, $plan, $validated, $mpService);

        } catch (\Exception $e) {
            Log::error('Erro ao processar pagamento inline', [
                'user_id' => $user->id,
                'plan' => $plan->slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Processa trial com validação de cartão (cobra R$1 e estorna)
     */
    private function processTrialWithCard(Request $request, $user, $plan, array $validated, MercadoPagoService $mpService)
    {
        $cardToken = $validated['card_token'];
        $paymentMethodId = $validated['payment_method_id'];
        
        // 1. Cobra R$1,00 para validar o cartão
        $validationPayload = [
            'transaction_amount' => 1.00,
            'token' => $cardToken,
            'description' => 'Validação de cartão - ' . config('app.name'),
            'installments' => 1,
            'payment_method_id' => $paymentMethodId,
            'payer' => [
                'email' => $validated['payer_email'] ?? $user->email,
                'entity_type' => 'individual',
            ],
            'external_reference' => 'CARD_VALIDATION|' . $user->id . '|' . Str::random(8),
            'capture' => true,
        ];

        // Adiciona identificação se fornecida
        if (!empty($validated['identification_type']) && !empty($validated['identification_number'])) {
            $validationPayload['payer']['identification'] = [
                'type' => $validated['identification_type'],
                'number' => preg_replace('/[^0-9]/', '', $validated['identification_number']),
            ];
        } elseif ($user->cpf) {
            $validationPayload['payer']['identification'] = [
                'type' => 'CPF',
                'number' => preg_replace('/[^0-9]/', '', $user->cpf),
            ];
        }

        if (!empty($validated['issuer_id'])) {
            $validationPayload['issuer_id'] = (int) $validated['issuer_id'];
        }

        $validationPayment = $mpService->processCardPayment($validationPayload);

        if (($validationPayment['status'] ?? '') !== 'approved') {
            $statusDetail = $validationPayment['status_detail'] ?? 'unknown';
            throw new \Exception($this->getCardErrorMessage($statusDetail));
        }

        Log::info('✅ Cartão validado com R$1', [
            'user_id' => $user->id,
            'payment_id' => $validationPayment['id'],
        ]);

        // 2. Estorna o R$1 imediatamente
        try {
            $mpService->createRefund($validationPayment['id']);
            Log::info('💰 R$1 estornado', ['payment_id' => $validationPayment['id']]);
        } catch (\Exception $e) {
            // Log mas não falha - o estorno pode ser feito manualmente depois
            Log::warning('Falha ao estornar R$1 de validação', [
                'payment_id' => $validationPayment['id'],
                'error' => $e->getMessage(),
            ]);
        }

        // 3. Cria assinatura recorrente com trial
        $preapprovalPayload = [
            'reason' => $plan->name . ' - ' . config('app.name'),
            'auto_recurring' => [
                'frequency' => 1,
                'frequency_type' => 'months',
                'transaction_amount' => (float) $plan->price,
                'currency_id' => 'BRL',
                'free_trial' => [
                    'frequency' => self::PREMIUM_TRIAL_DAYS,
                    'frequency_type' => 'days',
                ],
            ],
            'payer_email' => $validated['payer_email'] ?? $user->email,
            'card_token_id' => $cardToken,
            'external_reference' => 'CARD_TRIAL|' . $user->id . '|' . $plan->slug . '|' . Str::random(8),
            'back_url' => url('/'),
            'status' => 'authorized',
        ];

        $preapproval = $mpService->createCardSubscription($preapprovalPayload);

        // 4. Cria assinatura no sistema
        $subscription = $this->subscriptionService->createTrialSubscription($user, $plan);
        $subscription->update([
            'transaction_id' => $preapproval['id'] ?? null,
            'payment_method' => 'card',
            'mp_preapproval_id' => $preapproval['id'] ?? null,
            'card_last_four' => $validationPayment['card']['last_four_digits'] ?? null,
            'card_brand' => $validationPayment['payment_method_id'] ?? null,
        ]);

        Log::info('🎉 Trial com cartão criado', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'preapproval_id' => $preapproval['id'] ?? null,
            'trial_days' => self::PREMIUM_TRIAL_DAYS,
        ]);

        return response()->json([
            'success' => true,
            'message' => '🎉 Trial de ' . self::PREMIUM_TRIAL_DAYS . ' dias ativado! Seu cartão será cobrado automaticamente após o período de teste.',
            'subscription' => [
                'id' => $subscription->id,
                'status' => 'trial',
                'trial_ends_at' => $subscription->trial_ends_at->toIso8601String(),
                'remaining_days' => $subscription->remaining_days,
                'next_billing_date' => $subscription->trial_ends_at->format('d/m/Y'),
                'next_billing_amount' => $plan->formatted_price,
            ],
            'card' => [
                'last_four' => $validationPayment['card']['last_four_digits'] ?? null,
                'brand' => ucfirst($validationPayment['payment_method_id'] ?? ''),
            ],
            'reload' => true, // Frontend deve recarregar para aplicar layout premium
        ], 201);
    }

    /**
     * Processa pagamento direto com cartão (sem trial)
     */
    private function processDirectCardPayment($user, $plan, array $validated, MercadoPagoService $mpService)
    {
        $cardToken = $validated['card_token'];
        $paymentMethodId = $validated['payment_method_id'];
        $installments = $validated['installments'] ?? 1;

        $paymentPayload = [
            'transaction_amount' => (float) $plan->price,
            'token' => $cardToken,
            'description' => $plan->name . ' - ' . config('app.name'),
            'installments' => (int) $installments,
            'payment_method_id' => $paymentMethodId,
            'payer' => [
                'email' => $validated['payer_email'] ?? $user->email,
                'entity_type' => 'individual',
            ],
            'external_reference' => 'PREMIUM|' . $user->id . '|' . $plan->slug . '|' . Str::random(8),
            'capture' => true,
            'notification_url' => route('api.webhooks.subscription'),
        ];

        // Adiciona identificação
        if (!empty($validated['identification_type']) && !empty($validated['identification_number'])) {
            $paymentPayload['payer']['identification'] = [
                'type' => $validated['identification_type'],
                'number' => preg_replace('/[^0-9]/', '', $validated['identification_number']),
            ];
        } elseif ($user->cpf) {
            $paymentPayload['payer']['identification'] = [
                'type' => 'CPF',
                'number' => preg_replace('/[^0-9]/', '', $user->cpf),
            ];
        }

        if (!empty($validated['issuer_id'])) {
            $paymentPayload['issuer_id'] = (int) $validated['issuer_id'];
        }

        $payment = $mpService->processCardPayment($paymentPayload);

        $status = $payment['status'] ?? 'unknown';

        if ($status === 'approved') {
            // Criar assinatura paga
            $subscription = $this->subscriptionService->createPaidSubscription(
                $user,
                $plan,
                $payment['id']
            );

            $subscription->update([
                'payment_method' => 'card',
                'card_last_four' => $payment['card']['last_four_digits'] ?? null,
                'card_brand' => $payment['payment_method_id'] ?? null,
            ]);

            Log::info('🎉 Pagamento com cartão aprovado', [
                'user_id' => $user->id,
                'payment_id' => $payment['id'],
                'plan' => $plan->slug,
            ]);

            return response()->json([
                'success' => true,
                'message' => '🎉 Pagamento aprovado! Bem-vindo ao Premium!',
                'status' => 'approved',
                'subscription' => [
                    'id' => $subscription->id,
                    'plan' => $plan->name,
                    'data_fim' => $subscription->data_fim->format('d/m/Y'),
                    'remaining_days' => $subscription->remaining_days,
                ],
                'payment' => [
                    'id' => $payment['id'],
                    'amount' => $plan->formatted_price,
                    'installments' => $installments,
                ],
                'reload' => true,
            ]);
        }

        if ($status === 'in_process' || $status === 'pending') {
            // Criar subscription pendente
            $subscription = $this->subscriptionService->createPendingSubscription($user, $plan, $payment['id'], 'card');

            return response()->json([
                'success' => true,
                'message' => 'Pagamento em análise. Você será notificado quando for aprovado.',
                'status' => 'pending',
                'payment_id' => $payment['id'],
            ]);
        }

        // Pagamento rejeitado
        $statusDetail = $payment['status_detail'] ?? 'unknown';
        throw new \Exception($this->getCardErrorMessage($statusDetail));
    }

    /**
     * Mapeia status_detail para mensagem amigável
     */
    private function getCardErrorMessage(string $statusDetail): string
    {
        return match($statusDetail) {
            'cc_rejected_insufficient_amount' => 'Saldo insuficiente no cartão',
            'cc_rejected_bad_filled_card_number' => 'Número do cartão incorreto',
            'cc_rejected_bad_filled_date' => 'Data de validade incorreta',
            'cc_rejected_bad_filled_security_code' => 'Código de segurança incorreto',
            'cc_rejected_bad_filled_other' => 'Dados do cartão incorretos',
            'cc_rejected_high_risk' => 'Pagamento não autorizado por segurança. Tente outro cartão.',
            'cc_rejected_max_attempts' => 'Limite de tentativas atingido. Aguarde alguns minutos.',
            'cc_rejected_duplicated_payment' => 'Pagamento duplicado detectado',
            'cc_rejected_card_disabled' => 'Cartão desabilitado. Entre em contato com o banco.',
            'cc_rejected_call_for_authorize' => 'Ligue para o banco para autorizar esta compra',
            'cc_rejected_card_error' => 'Erro no cartão. Tente novamente.',
            'cc_rejected_other_reason' => 'Cartão recusado. Tente outro cartão.',
            'rejected_high_risk' => 'Pagamento recusado por segurança',
            'pending_contingency' => 'Pagamento pendente de processamento',
            'pending_review_manual' => 'Pagamento em análise manual',
            default => 'Pagamento recusado. Verifique os dados e tente novamente.',
        };
    }

    private function resolvePlanFromExternalReference(string $externalReference, int $userId): ?SubscriptionPlan
    {
        if ($externalReference === '') {
            return null;
        }

        if (preg_match('/^sub:(\d+)\|user:(\d+)\|trial:(0|1)$/', $externalReference, $matches)) {
            if ((int) $matches[2] !== $userId) {
                return null;
            }

            return $this->subscriptionService->findPlanById((int) $matches[1]);
        }

        if (preg_match('/^sub:([^|]+)\|user:(\d+)\|trial:(0|1)$/', $externalReference, $matches)) {
            if ((int) $matches[2] !== $userId) {
                return null;
            }

            return $this->subscriptionService->findPlanBySlug($matches[1]);
        }

        if (preg_match('/^PREMIUM\|(\d+)\|([^|]+)\|/', $externalReference, $matches)) {
            if ((int) $matches[1] !== $userId) {
                return null;
            }

            return $this->subscriptionService->findPlanBySlug($matches[2]);
        }

        return null;
    }

    private function buildApprovedSubscriptionPayload(Subscription $subscription): array
    {
        $plan = $subscription->plan;

        return [
            'id' => $subscription->id,
            'plan' => $plan?->name ?? $subscription->plano,
            'data_fim' => $subscription->data_fim?->toDateString(),
            'remaining_days' => $subscription->remaining_days,
        ];
    }

    private function normalizeTrialCopy(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        $normalized = preg_replace('/\b(14|30)\s*dias\b/ui', self::PREMIUM_TRIAL_DAYS . ' dias', $text);

        return $normalized ?? $text;
    }

    private function normalizeTrialFeatures($features): array
    {
        if (!is_array($features)) {
            return [];
        }

        return array_values(array_map(function ($feature) {
            return $this->normalizeTrialCopy((string) $feature) ?? (string) $feature;
        }, $features));
    }

    /**
     * Retorna a public key do Mercado Pago para o frontend
     * GET /api/subscriptions/mp-public-key
     */
    public function getMercadoPagoPublicKey()
    {
        $publicKey = config('services.mercadopago.public_key');
        
        if (!$publicKey) {
            return response()->json([
                'success' => false,
                'message' => 'Public key não configurada',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'public_key' => $publicKey,
        ]);
    }
}
