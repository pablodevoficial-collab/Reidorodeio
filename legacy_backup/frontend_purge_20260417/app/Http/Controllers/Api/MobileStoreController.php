<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppStoreProduct;
use App\Models\AppStorePurchase;
use App\Services\AppStoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStoreController extends Controller
{
    public function __construct(
        private readonly AppStoreService $storeService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => $this->storeService->overview(
                $user,
                (string) $request->query('platform', 'android')
            ),
        ]);
    }

    public function topUpWallet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:20', 'max:5000'],
            'platform' => ['nullable', 'string', 'max:20'],
        ]);

        $purchase = $this->storeService->createWalletTopUp(
            $request->user(),
            (float) $validated['amount']
        );

        return response()->json([
            'success' => true,
            'message' => 'PIX da recarga gerado com sucesso.',
            'data' => $this->serializePurchase($purchase),
        ], 201);
    }

    public function purchaseProduct(Request $request, AppStoreProduct $product): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:pix,wallet'],
            'platform' => ['nullable', 'string', 'max:20'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $purchase = $this->storeService->purchaseProduct(
            $request->user(),
            $product,
            (string) $validated['payment_method'],
            (int) ($validated['quantity'] ?? 1),
        );

        return response()->json([
            'success' => true,
            'message' => $purchase->status === 'approved'
                ? 'Compra aprovada com sucesso.'
                : 'PIX da compra gerado com sucesso.',
            'data' => $this->serializePurchase($purchase),
        ], $purchase->status === 'approved' ? 200 : 201);
    }

    public function purchaseStatus(Request $request, AppStorePurchase $purchase): JsonResponse
    {
        $purchase = $this->storeService->refreshPurchase($request->user(), $purchase);

        return response()->json([
            'success' => true,
            'data' => $this->serializePurchase($purchase),
        ]);
    }

    public function cancelPurchase(Request $request, AppStorePurchase $purchase): JsonResponse
    {
        $purchase = $this->storeService->cancelPurchase($request->user(), $purchase);

        return response()->json([
            'success' => true,
            'message' => 'Pagamento cancelado.',
            'data' => $this->serializePurchase($purchase),
        ]);
    }

    private function serializePurchase(AppStorePurchase $purchase): array
    {
        $payload = $purchase->payload ?? [];

        return [
            'id' => (int) $purchase->id,
            'status' => (string) $purchase->status,
            'purchase_kind' => (string) $purchase->purchase_kind,
            'payment_method' => (string) $purchase->payment_method,
            'amount' => (float) $purchase->amount,
            'wallet_credit_amount' => (float) $purchase->wallet_credit_amount,
            'description' => (string) ($purchase->description ?? ''),
            'provider_payment_id' => $purchase->provider_payment_id,
            'provider_preference_id' => $purchase->provider_preference_id,
            'quantity' => (int) ($payload['quantity'] ?? 1),
            'qr_code' => $payload['qr_code'] ?? null,
            'qr_code_base64' => $payload['qr_code_base64'] ?? null,
            'paid_at' => optional($purchase->paid_at)->toIso8601String(),
            'expires_at' => optional($purchase->expires_at)->toIso8601String(),
            'fulfilled_at' => optional($purchase->fulfilled_at)->toIso8601String(),
        ];
    }
}
