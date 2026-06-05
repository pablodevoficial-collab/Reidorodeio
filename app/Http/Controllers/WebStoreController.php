<?php

namespace App\Http\Controllers;

use App\Models\AppStoreProduct;
use App\Models\AppStorePurchase;
use App\Models\StoreProductSubmission;
use App\Services\AppStoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebStoreController extends Controller
{
    public function __construct(
        private readonly AppStoreService $storeService
    ) {
    }

    public function index(Request $request)
    {
        if (!$request->ajax() && !$request->boolean('hub_partial')) {
            $query = $request->query();
            unset($query['hub_partial']);
            $query['tab'] = 'loja';

            return redirect()->route('home', $query);
        }

        $user = $request->user();
        $storeOverview = $this->storeService->overview($user, 'web');
        $storePurchases = [];
        $productSubmissions = collect();

        if ($user) {
            $storePurchases = AppStorePurchase::query()
                ->with('product')
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit(12)
                ->get()
                ->map(fn (AppStorePurchase $purchase) => $this->serializePurchaseSummary($purchase))
                ->all();

            $productSubmissions = StoreProductSubmission::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit(6)
                ->get();
        }

        return view('frontend.partials.inicial_loja_content', [
            'storeOverview' => $storeOverview,
            'storePurchases' => $storePurchases,
            'productSubmissions' => $productSubmissions,
        ]);
    }

    public function purchaseProduct(Request $request, AppStoreProduct $product): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:pix,wallet'],
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

    public function createTopUp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:20', 'max:5000'],
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

    public function submitProductListing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'commission_percent' => ['required', 'numeric', 'min:1', 'max:90'],
            'photos' => ['required', 'array', 'min:1', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $photoPaths = collect($request->file('photos', []))
            ->map(fn ($file) => $file->store('store_product_submissions', 'public'))
            ->values()
            ->all();

        $submission = StoreProductSubmission::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => round((float) $validated['price'], 2),
            'commission_percent' => round((float) $validated['commission_percent'], 2),
            'photos' => $photoPaths,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produto enviado para análise da loja.',
            'data' => [
                'id' => (int) $submission->id,
                'status' => (string) $submission->status,
            ],
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
            'formatted_amount' => 'R$ ' . number_format((float) $purchase->amount, 2, ',', '.'),
            'wallet_credit_amount' => (float) $purchase->wallet_credit_amount,
            'description' => (string) ($purchase->description ?? ''),
            'provider_payment_id' => $purchase->provider_payment_id,
            'provider_preference_id' => $purchase->provider_preference_id,
            'quantity' => (int) ($payload['quantity'] ?? 1),
            'product' => $purchase->product ? [
                'id' => (int) $purchase->product->id,
                'slug' => (string) $purchase->product->slug,
                'title' => (string) $purchase->product->title,
                'product_type' => (string) $purchase->product->product_type,
            ] : null,
            'qr_code' => $payload['qr_code'] ?? null,
            'qr_code_base64' => $payload['qr_code_base64'] ?? null,
            'paid_at' => optional($purchase->paid_at)->toIso8601String(),
            'expires_at' => optional($purchase->expires_at)->toIso8601String(),
            'fulfilled_at' => optional($purchase->fulfilled_at)->toIso8601String(),
        ];
    }

    private function serializePurchaseSummary(AppStorePurchase $purchase): array
    {
        return [
            'id' => (int) $purchase->id,
            'status' => (string) $purchase->status,
            'purchase_kind' => (string) $purchase->purchase_kind,
            'payment_method' => (string) $purchase->payment_method,
            'amount' => (float) $purchase->amount,
            'formatted_amount' => 'R$ ' . number_format((float) $purchase->amount, 2, ',', '.'),
            'description' => (string) ($purchase->description ?? ''),
            'product_title' => (string) ($purchase->product?->title ?? $purchase->description ?? 'Item da loja'),
            'product_slug' => (string) ($purchase->product?->slug ?? ''),
            'created_at_label' => optional($purchase->created_at)?->format('d/m H:i'),
            'paid_at_label' => optional($purchase->paid_at)?->format('d/m H:i'),
            'expires_at_label' => optional($purchase->expires_at)?->format('d/m H:i'),
            'is_pending' => $purchase->isPending(),
        ];
    }
}
