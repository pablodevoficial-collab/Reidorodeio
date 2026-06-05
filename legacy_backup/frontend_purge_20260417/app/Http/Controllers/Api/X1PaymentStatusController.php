<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\X1Payment;
use App\Models\FantasyPayment;
use App\Services\MercadoPagoService;
use App\Services\X1PaymentSettlementService;
use Illuminate\Support\Facades\DB;

class X1PaymentStatusController extends Controller
{
    public function checkStatus(Request $request)
    {
        $user = $request->user();
        $preferenceId = $request->input('preference_id');
        
        if (!$preferenceId) {
            return response()->json(['error' => 'preference_id required'], 400);
        }
        
        $x1Payment = X1Payment::where('provider_preference_id', $preferenceId)
            ->where('user_id', $user->id)
            ->first();
        
        // Se não encontrou em X1, verificar em Fantasy
        if (!$x1Payment) {
            return $this->checkFantasyPayment($user, $preferenceId);
        }

        if ($this->isRefundedStatus($x1Payment->status)) {
            return response()->json($this->buildX1StatusResponse($x1Payment));
        }

        if ((string) $x1Payment->status === 'approved') {
            app(X1PaymentSettlementService::class)->settleApprovedPayment($x1Payment);
            $x1Payment->refresh();

            return response()->json($this->buildX1StatusResponse($x1Payment));
        }

        // Se NÃO tem payment ID do MP, tentar descobrir via busca por external_reference
        if (!$x1Payment->provider_payment_id && !$this->isFinalStatus($x1Payment->status)) {
            try {
                $mp = app(MercadoPagoService::class);
                $found = $mp->searchPaymentsByExternalReference($x1Payment->external_reference);
                
                if ($found && !empty($found['id'])) {
                    \Log::info("🔍 Payment ID encontrado via busca por external_reference", [
                        'payment_id' => $x1Payment->id,
                        'mp_payment_id' => $found['id'],
                        'mp_status' => $found['status'] ?? 'unknown',
                    ]);
                    
                    // Preservar competitor_id e competitor_group_id do payload original
                    $originalCompetitorId = data_get($x1Payment->payload, 'competitor_id');
                    $originalGroupId = data_get($x1Payment->payload, 'competitor_group_id');
                    
                    // Remover chaves conflitantes do payload do MP
                    unset($found['competitor_id'], $found['competitor_group_id']);
                    
                    $x1Payment->provider_payment_id = (string) $found['id'];
                    $existingPayload = is_array($x1Payment->payload) ? $x1Payment->payload : [];
                    $x1Payment->payload = array_merge($existingPayload, $found, [
                        'competitor_id' => $originalCompetitorId,
                        'competitor_group_id' => $originalGroupId,
                    ]);
                    
                    $paymentStatus = $found['status'] ?? 'pending';
                    $preserveRefundStatus = $this->isRefundedStatus($x1Payment->status)
                        && $paymentStatus === 'approved';

                    if (!$preserveRefundStatus && $paymentStatus !== $x1Payment->status) {
                        $x1Payment->status = $paymentStatus;
                    }
                    
                    if ($paymentStatus === 'approved') {
                        if (!$x1Payment->paid_at) {
                            $x1Payment->paid_at = now();
                        }
                        $x1Payment->save();
                        app(X1PaymentSettlementService::class)->settleApprovedPayment($x1Payment);
                        $x1Payment->refresh();
                        
                        // Notificação
                        $paymentUser = \App\Models\User::find($x1Payment->user_id);
                        if ($paymentUser && (string) $x1Payment->status === 'approved') {
                            notify($paymentUser, 'PAYMENT_CONFIRM', [
                                'amount' => showAmount($x1Payment->amount),
                                'trx' => $x1Payment->provider_payment_id ?? $x1Payment->id,
                                'post_time' => showDateTime($x1Payment->paid_at),
                                'url' => route('home'),
                            ], ['email']);
                        }
                    } else {
                        $x1Payment->save();
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Erro ao buscar payment por external_reference', [
                    'error' => $e->getMessage(),
                    'external_reference' => $x1Payment->external_reference,
                ]);
            }
        }
        
        // Se já tem payment ID do MP, buscar status atualizado
        if ($x1Payment->provider_payment_id && !$this->isFinalStatus($x1Payment->status)) {
            try {
                $mp = app(MercadoPagoService::class);
                $payment = $mp->fetchPayment($x1Payment->provider_payment_id);
                $paymentStatus = $payment['status'] ?? 'pending';
                
                // Atualizar status local
                if ($paymentStatus !== $x1Payment->status) {
                    \Log::info("🔄 Atualizando status do pagamento via polling", [
                        'payment_id' => $x1Payment->id,
                        'old_status' => $x1Payment->status,
                        'new_status' => $paymentStatus,
                    ]);
                    
                    DB::transaction(function () use ($x1Payment, $paymentStatus, $payment) {
                        // Preservar competitor_id e competitor_group_id do payload original ANTES de modificar
                        $originalCompetitorId = data_get($x1Payment->payload, 'competitor_id');
                        $originalGroupId = data_get($x1Payment->payload, 'competitor_group_id');
                        
                        \Log::info('🔐 [Polling] Preservando competidor do payload original', [
                            'competitor_id' => $originalCompetitorId,
                            'competitor_group_id' => $originalGroupId,
                        ]);
                        
                        // Remover chaves conflitantes do payload do MP antes do merge
                        unset($payment['competitor_id'], $payment['competitor_group_id']);
                        
                        $preserveRefundStatus = $this->isRefundedStatus($x1Payment->status)
                            && $paymentStatus === 'approved';

                        if (!$preserveRefundStatus) {
                            $x1Payment->status = $paymentStatus;
                        }
                        $existingPayload = is_array($x1Payment->payload) ? $x1Payment->payload : [];
                        $x1Payment->payload = array_merge($existingPayload, $payment, [
                            'competitor_id' => $originalCompetitorId,
                            'competitor_group_id' => $originalGroupId,
                        ]);
                        
                        if ($paymentStatus === 'approved') {
                            if (!$x1Payment->paid_at) {
                                $x1Payment->paid_at = now();
                            }
                            $x1Payment->save();

                            // Processar aprovação
                            app(X1PaymentSettlementService::class)->settleApprovedPayment($x1Payment);
                            $x1Payment->refresh();

                            // Enviar Notificação de Pagamento Confirmado
                            $user = \App\Models\User::find($x1Payment->user_id);
                            if ($user && (string) $x1Payment->status === 'approved') {
                                notify($user, 'PAYMENT_CONFIRM', [
                                    'amount' => showAmount($x1Payment->amount),
                                    'trx' => $x1Payment->provider_payment_id ?? $x1Payment->id,
                                    'post_time' => showDateTime($x1Payment->paid_at),
                                    'url' => route('home'),
                                ], ['email']);
                            }
                        } else {
                            $x1Payment->save();
                        }
                    });
                }
            } catch (\Exception $e) {
                \Log::error('Erro ao verificar status do pagamento', [
                    'error' => $e->getMessage(),
                    'payment_id' => $x1Payment->provider_payment_id,
                ]);
            }
        }
        
        return response()->json($this->buildX1StatusResponse($x1Payment));
    }
    
    /**
     * Verificar pagamento Fantasy
     */
    private function checkFantasyPayment($user, string $preferenceId)
    {
        $fantasyPayment = FantasyPayment::where('provider_preference_id', $preferenceId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$fantasyPayment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        
        // Se já tem payment ID do MP, buscar status atualizado
        if ($fantasyPayment->provider_payment_id && $fantasyPayment->status !== 'approved') {
            try {
                $mp = app(MercadoPagoService::class);
                $payment = $mp->fetchPayment($fantasyPayment->provider_payment_id);
                $paymentStatus = $payment['status'] ?? 'pending';
                
                // Atualizar status local
                if ($paymentStatus !== $fantasyPayment->status) {
                    \Log::info("🔄 [Fantasy] Atualizando status do pagamento via polling", [
                        'payment_id' => $fantasyPayment->id,
                        'old_status' => $fantasyPayment->status,
                        'new_status' => $paymentStatus,
                    ]);
                    
                    $fantasyPayment->status = $paymentStatus;
                    
                    if ($paymentStatus === 'approved' && !$fantasyPayment->paid_at) {
                        $fantasyPayment->paid_at = now();
                        $fantasyPayment->save();
                        
                        // Processar aprovação do Fantasy - criar time
                        $this->processFantasyApproval($fantasyPayment);
                    } else {
                        $fantasyPayment->save();
                    }
                }
            } catch (\Exception $e) {
                \Log::error('[Fantasy] Erro ao verificar status do pagamento', [
                    'error' => $e->getMessage(),
                    'payment_id' => $fantasyPayment->provider_payment_id,
                ]);
            }
        }
        
        return response()->json([
            'status' => $fantasyPayment->status,
            'paid_at' => $fantasyPayment->paid_at?->toIso8601String(),
            'league_id' => $fantasyPayment->fantasy_league_id,
            'team_id' => $fantasyPayment->fantasy_team_id,
            'type' => 'fantasy',
        ]);
    }
    
    /**
     * Processar aprovação de pagamento Fantasy
     */
    private function processFantasyApproval(FantasyPayment $payment)
    {
        // Se já tem time criado, não fazer nada
        if ($payment->fantasy_team_id) {
            \Log::info('[Fantasy] Time já existe para este pagamento', [
                'payment_id' => $payment->id,
                'team_id' => $payment->fantasy_team_id,
            ]);
            return;
        }
        
        // Usar o FantasyPaymentController para processar
        $controller = app(\App\Http\Controllers\Api\FantasyPaymentController::class);
        $controller->processApproval($payment);
    }
    
    private function buildX1StatusResponse(X1Payment $x1Payment): array
    {
        $refund = is_array($x1Payment->payload) ? (array) data_get($x1Payment->payload, 'refund', []) : [];
        $status = (string) $x1Payment->status;

        return [
            'status' => $status,
            'paid_at' => $x1Payment->paid_at?->toIso8601String(),
            'room_id' => $x1Payment->x1_room_id,
            'wallet_refunded' => $this->isRefundedStatus($status),
            'refunded_amount' => $this->isRefundedStatus($status)
                ? (float) ($refund['amount'] ?? $x1Payment->amount ?? 0)
                : null,
            'message' => $this->isRefundedStatus($status)
                ? (string) ($refund['message'] ?? 'O valor foi devolvido para sua carteira.')
                : null,
        ];
    }

    private function isRefundedStatus(?string $status): bool
    {
        return str_starts_with(strtolower((string) $status), 'refunded');
    }

    private function isFinalStatus(?string $status): bool
    {
        $normalized = strtolower((string) $status);

        return in_array($normalized, ['approved', 'rejected', 'cancelled', 'refunded'], true)
            || $this->isRefundedStatus($normalized);
    }
}
