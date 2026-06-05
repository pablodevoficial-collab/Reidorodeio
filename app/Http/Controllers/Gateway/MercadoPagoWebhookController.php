<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\FantasyPayment;
use App\Models\X1Payment;
use App\Services\MercadoPagoService;
use App\Services\X1PaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        \Log::info('🔔 Webhook MercadoPago recebido', [
            'body' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        $paymentId = $request->input('data.id') ?? $request->input('id');
        if (!$paymentId) {
            \Log::warning('⚠️ Webhook sem payment ID');
            return response()->json(['message' => 'Missing payment id'], 400);
        }

        \Log::info('📥 Processando pagamento', ['payment_id' => $paymentId]);

        $mp = app(MercadoPagoService::class);
        $payment = $mp->fetchPayment((string) $paymentId);

        \Log::info('💳 Dados do pagamento', ['payment' => $payment]);

        $externalRef = $payment['external_reference'] ?? null;
        if (!$externalRef) {
            \Log::warning('⚠️ Sem external_reference');
            return response()->json(['message' => 'Missing external reference'], 400);
        }

        $paymentStatus = $payment['status'] ?? 'pending';
        \Log::info('✅ Status do pagamento', [
            'external_ref' => $externalRef,
            'status' => $paymentStatus,
        ]);

        DB::transaction(function () use ($externalRef, $paymentId, $paymentStatus, $payment) {
            if (str_starts_with($externalRef, 'fantasy:')) {
                $this->processFantasyPayment($externalRef, $paymentId, $paymentStatus, $payment);
                return;
            }

            $x1Payment = X1Payment::where('external_reference', $externalRef)->first();
            if (!$x1Payment) {
                \Log::warning('⚠️ X1Payment não encontrado', ['external_ref' => $externalRef]);
                return;
            }

            \Log::info('📦 X1Payment encontrado', [
                'id' => $x1Payment->id,
                'room_id' => $x1Payment->x1_room_id,
                'role' => $x1Payment->role,
            ]);

            // Preservar competitor_id e competitor_group_id do payload original ANTES de modificar
            $originalCompetitorId = data_get($x1Payment->payload, 'competitor_id');
            $originalGroupId = data_get($x1Payment->payload, 'competitor_group_id');
            
            \Log::info('🔐 Preservando competidor do payload original', [
                'competitor_id' => $originalCompetitorId,
                'competitor_group_id' => $originalGroupId,
            ]);
            
            // Remover chaves conflitantes do payload do MP antes do merge
            unset($payment['competitor_id'], $payment['competitor_group_id']);
            
            $preserveRefundStatus = str_starts_with(strtolower((string) $x1Payment->status), 'refunded')
                && $paymentStatus === 'approved';

            $x1Payment->provider_payment_id = (string) $paymentId;
            if (!$preserveRefundStatus) {
                $x1Payment->status = $paymentStatus;
            }
            $existingPayload = is_array($x1Payment->payload) ? $x1Payment->payload : [];
            $x1Payment->payload = array_merge($existingPayload, $payment, [
                'competitor_id' => $originalCompetitorId,
                'competitor_group_id' => $originalGroupId,
            ]);
            if ($paymentStatus === 'approved') {
                $x1Payment->paid_at = now();
            }
            $x1Payment->save();

            if ($paymentStatus !== 'approved') {
                \Log::info('⏳ Pagamento ainda não aprovado', ['status' => $paymentStatus]);
                return;
            }

            $settlement = app(X1PaymentSettlementService::class)->settleApprovedPayment($x1Payment);
            \Log::info('🧾 Liquidação X1 concluída via webhook', [
                'x1_payment_id' => $x1Payment->id,
                'room_id' => $x1Payment->x1_room_id,
                'role' => $x1Payment->role,
                'settlement' => $settlement,
            ]);
        });

        \Log::info('✅ Webhook processado com sucesso');
        return response()->json(['ok' => true]);
    }

    private function processFantasyPayment(string $externalRef, string $paymentId, string $paymentStatus, array $payment): void
    {
        $fantasyPayment = FantasyPayment::where('external_reference', $externalRef)->first();
        if (!$fantasyPayment) {
            \Log::warning('⚠️ FantasyPayment não encontrado', ['external_ref' => $externalRef]);
            return;
        }

        $payload = $fantasyPayment->payload ?? [];
        $payload['pix_payment'] = $payment;
        $payload['qr_code'] = $payload['qr_code']
            ?? data_get($payment, 'point_of_interaction.transaction_data.qr_code');
        $payload['qr_code_base64'] = $payload['qr_code_base64']
            ?? data_get($payment, 'point_of_interaction.transaction_data.qr_code_base64');

        $fantasyPayment->provider_payment_id = (string) $paymentId;
        $fantasyPayment->payload = $payload;

        if ($paymentStatus === 'approved' && !$fantasyPayment->paid_at) {
            $fantasyPayment->paid_at = now();
        }

        $fantasyPayment->save();

        if ($paymentStatus !== 'approved') {
            $fantasyPayment->status = $paymentStatus;
            $fantasyPayment->save();

            \Log::info('⏳ Pagamento Fantasy ainda não aprovado', [
                'status' => $paymentStatus,
                'payment_id' => $fantasyPayment->id,
            ]);
            return;
        }

        if (!$fantasyPayment->fantasy_team_id) {
            app(\App\Http\Controllers\Api\FantasyPaymentController::class)->processApproval($fantasyPayment);
            $fantasyPayment->refresh();
        }

        \Log::info('✅ Pagamento Fantasy processado via webhook', [
            'payment_id' => $fantasyPayment->id,
            'team_id' => $fantasyPayment->fantasy_team_id,
        ]);
    }
}
