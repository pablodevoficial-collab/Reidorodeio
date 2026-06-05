<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required',
        ]);

        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];
        
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        // Check if subscription exists
        $subscription = PushSubscription::where('endpoint', $endpoint)->first();

        if ($subscription) {
            // Update user_id if changed (e.g. user logged in)
            if ($userId && $subscription->user_id !== $userId) {
                $subscription->user_id = $userId;
            }
            $subscription->is_active = true;
            $subscription->save();
        } else {
            PushSubscription::create([
                'user_id' => $userId,
                'endpoint' => $endpoint,
                'public_key' => $key,
                'auth_token' => $token,
                'content_encoding' => $request->contentEncoding,
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Notification subscribed successfully.']);
    }

    /**
     * Remove a subscription.
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'endpoint' => 'required',
        ]);

        $subscription = PushSubscription::where('endpoint', $request->endpoint)->first();

        if ($subscription) {
            $subscription->delete();
        }

        return response()->json(['success' => true]);
    }
}
