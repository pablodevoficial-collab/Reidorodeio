<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Models\X1RoomInstance;
use Illuminate\Support\Facades\Log;

class SubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        if ($subscription->isActive()) {
            $this->updateUserRoomsToPremium($subscription->user_id);
        }
    }

    public function updated(Subscription $subscription): void
    {
        if ($subscription->isActive()) {
            $this->updateUserRoomsToPremium($subscription->user_id);
        }
    }

    protected function updateUserRoomsToPremium(int $userId): void
    {
        $rooms = X1RoomInstance::where('host_user_id', $userId)
            ->where('is_premium_room', false)
            ->whereIn('status', ['open', 'pending_payment'])
            ->get();

        foreach ($rooms as $room) {
            $oldFee = (float) $room->fee_percent;
            $room->fee_percent = 8.0;
            $room->is_premium_room = true;

            if ($room->valor_entrada) {
                $total = (float) $room->valor_entrada * 2;
                $fee = $total * 0.08;
                $room->prize_total = round($total - $fee, 2);
            }

            $room->save();

            Log::info('Sala X1 atualizada para premium', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'old_fee' => $oldFee,
                'new_fee' => 8.0,
            ]);
        }
    }
}
