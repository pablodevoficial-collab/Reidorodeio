<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NativePushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobilePushController extends Controller
{
    public function __construct(
        private NativePushService $nativePushService
    ) {
    }

    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'enabled' => $this->nativePushService->hasPublicClientConfig(),
            'firebase' => $this->nativePushService->publicClientConfig(),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'platform' => ['nullable', 'string', 'max:30'],
        ]);

        $deviceToken = $this->nativePushService->registerToken(
            (int) $request->user()->id,
            $validated['token'],
            true
        );

        return response()->json([
            'success' => true,
            'message' => 'Token do app registrado com sucesso.',
            'device_token_id' => $deviceToken->id,
        ]);
    }

    public function unregister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:500'],
        ]);

        $this->nativePushService->unregisterToken(
            (int) $request->user()->id,
            $validated['token']
        );

        return response()->json([
            'success' => true,
            'message' => 'Token do app removido com sucesso.',
        ]);
    }
}
