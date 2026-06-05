<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\X1Participant;
use App\Models\X1Payment;
use App\Models\X1RoomInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class X1PaymentSettlementService
{
    public function settleApprovedPayment(X1Payment $payment): array
    {
        return DB::transaction(function () use ($payment) {
            $lockedPayment = X1Payment::query()->lockForUpdate()->find($payment->id);

            if (!$lockedPayment) {
                return [
                    'outcome' => 'missing_payment',
                    'message' => 'Pagamento X1 nao encontrado.',
                ];
            }

            if ($this->isRefundedStatus($lockedPayment->status)) {
                return $this->buildRefundResult($lockedPayment);
            }

            if ((string) $lockedPayment->status !== 'approved') {
                return [
                    'outcome' => 'payment_not_approved',
                    'status' => $lockedPayment->status,
                    'message' => 'Pagamento ainda nao aprovado.',
                ];
            }

            $lockedPayment->paid_at = $lockedPayment->paid_at ?? now();
            $lockedPayment->save();

            $room = X1RoomInstance::query()->lockForUpdate()->find($lockedPayment->x1_room_id);
            if (!$room) {
                return $this->refundToBalance(
                    $lockedPayment,
                    null,
                    'refunded_room_unavailable',
                    'x1_refund_room_unavailable',
                    'Pagamento confirmado, mas a sala nao estava mais disponivel. O valor foi devolvido para sua carteira.'
                );
            }

            if ($lockedPayment->role === 'host') {
                return $this->settleHostPayment($lockedPayment, $room);
            }

            return $this->settleOpponentPayment($lockedPayment, $room);
        });
    }

    private function settleHostPayment(X1Payment $payment, X1RoomInstance $room): array
    {
        $existingParticipant = X1Participant::query()
            ->where('x1_room_id', $room->id)
            ->where('user_id', $payment->user_id)
            ->lockForUpdate()
            ->first();

        if ($existingParticipant && $existingParticipant->payment_status === 'paid') {
            return [
                'outcome' => 'already_processed_host',
                'status' => $payment->status,
                'room_id' => $room->id,
                'message' => 'Pagamento do criador ja processado.',
            ];
        }

        if (in_array((string) $room->status, ['cancelled', 'closed', 'finished'], true)) {
            return $this->refundToBalance(
                $payment,
                $room,
                'refunded_room_unavailable',
                'x1_refund_room_unavailable',
                'Pagamento confirmado, mas a sala nao estava mais disponivel. O valor foi devolvido para sua carteira.'
            );
        }

        $room->status = 'open';
        $room->host_paid_at = $payment->paid_at ?? now();
        $room->save();

        X1Participant::query()->updateOrCreate(
            [
                'x1_room_id' => $room->id,
                'user_id' => $payment->user_id,
            ],
            [
                'competitor_id' => $room->competitor_id,
                'competitor_group_id' => $room->competitor_group_id,
                'amount' => $payment->amount,
                'slot' => 1,
                'payment_status' => 'paid',
                'paid_at' => $payment->paid_at ?? now(),
                'is_host' => true,
            ]
        );

        return [
            'outcome' => 'host_joined',
            'status' => 'approved',
            'room_id' => $room->id,
            'message' => 'Pagamento do criador processado com sucesso.',
        ];
    }

    private function settleOpponentPayment(X1Payment $payment, X1RoomInstance $room): array
    {
        $existingParticipant = X1Participant::query()
            ->where('x1_room_id', $room->id)
            ->where('user_id', $payment->user_id)
            ->lockForUpdate()
            ->first();

        if ($existingParticipant && $existingParticipant->payment_status === 'paid') {
            return [
                'outcome' => 'already_processed_opponent',
                'status' => $payment->status,
                'room_id' => $room->id,
                'message' => 'Pagamento do oponente ja processado.',
            ];
        }

        $otherPaidOpponentExists = X1Participant::query()
            ->where('x1_room_id', $room->id)
            ->where('is_host', false)
            ->where('payment_status', 'paid')
            ->where('user_id', '!=', $payment->user_id)
            ->lockForUpdate()
            ->exists();

        $roomUnavailable = $room->status !== 'open'
            || $room->closed_at !== null
            || $room->finished_at !== null
            || $otherPaidOpponentExists;

        if ($roomUnavailable) {
            return $this->refundToBalance(
                $payment,
                $room,
                'refunded_room_full',
                'x1_refund_room_full',
                'A sala ja foi preenchida quando o PIX foi aprovado. O valor foi devolvido para sua carteira.'
            );
        }

        X1Participant::query()->updateOrCreate(
            [
                'x1_room_id' => $room->id,
                'user_id' => $payment->user_id,
            ],
            [
                'competitor_id' => data_get($payment->payload, 'competitor_id'),
                'competitor_group_id' => data_get($payment->payload, 'competitor_group_id'),
                'amount' => $payment->amount,
                'slot' => 2,
                'payment_status' => 'paid',
                'paid_at' => $payment->paid_at ?? now(),
                'is_host' => false,
            ]
        );

        $paidCount = X1Participant::query()
            ->where('x1_room_id', $room->id)
            ->where('payment_status', 'paid')
            ->count();

        $transitionedToInProgress = false;
        if ($paidCount >= 2 && $room->status !== 'in_progress') {
            $room->status = 'in_progress';
            $room->save();
            $transitionedToInProgress = true;
        }

        if ($transitionedToInProgress) {
            app(AppCommunityFeedService::class)
                ->publishX1RoomMatched($room->fresh(['host', 'modalidade', 'rodeio', 'participants.user']));

        }

        return [
            'outcome' => $transitionedToInProgress ? 'room_started' : 'opponent_joined',
            'status' => 'approved',
            'room_id' => $room->id,
            'message' => $transitionedToInProgress
                ? 'Pagamento confirmado e sala iniciada.'
                : 'Pagamento confirmado com sucesso.',
        ];
    }

    private function refundToBalance(
        X1Payment $payment,
        ?X1RoomInstance $room,
        string $status,
        string $remark,
        string $message
    ): array {
        $user = User::query()->lockForUpdate()->find($payment->user_id);

        if (!$user) {
            Log::warning('[X1] Usuario nao encontrado para reembolso automatico', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'status' => $status,
            ]);

            $payload = is_array($payment->payload) ? $payment->payload : [];
            $payload['refund'] = [
                'status' => 'failed_missing_user',
                'reason' => $status,
                'message' => $message,
                'amount' => (float) $payment->amount,
                'refunded_at' => now()->toIso8601String(),
            ];

            $payment->status = $status;
            $payment->payload = $payload;
            $payment->save();

            return [
                'outcome' => 'refund_failed_missing_user',
                'status' => $status,
                'room_id' => $room?->id,
                'message' => $message,
            ];
        }

        $before = round((float) ($user->balance ?? 0), 2);
        $amount = round((float) $payment->amount, 2);
        $after = round($before + $amount, 2);

        $user->balance = $after;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'charge' => 0,
            'post_balance' => $after,
            'trx_type' => '+',
            'details' => $room
                ? "Reembolso X1 - Sala #{$room->id} indisponivel apos aprovacao do PIX"
                : 'Reembolso X1 - Sala indisponivel apos aprovacao do PIX',
            'trx' => getTrx(),
            'remark' => $remark,
        ]);

        $payload = is_array($payment->payload) ? $payment->payload : [];
        $payload['refund'] = [
            'status' => 'wallet_refunded',
            'reason' => $status,
            'message' => $message,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'refunded_at' => now()->toIso8601String(),
        ];

        $payment->status = $status;
        $payment->payload = $payload;
        $payment->save();

        return [
            'outcome' => 'wallet_refunded',
            'status' => $status,
            'wallet_refunded' => true,
            'refunded_amount' => $amount,
            'room_id' => $room?->id,
            'message' => $message,
        ];
    }

    private function buildRefundResult(X1Payment $payment): array
    {
        $refund = is_array($payment->payload) ? (array) data_get($payment->payload, 'refund', []) : [];

        return [
            'outcome' => 'already_refunded',
            'status' => $payment->status,
            'wallet_refunded' => true,
            'refunded_amount' => (float) ($refund['amount'] ?? $payment->amount ?? 0),
            'room_id' => $payment->x1_room_id,
            'message' => (string) ($refund['message'] ?? 'O valor foi devolvido para sua carteira.'),
        ];
    }

    private function isRefundedStatus(?string $status): bool
    {
        return str_starts_with(strtolower((string) $status), 'refunded');
    }
}
