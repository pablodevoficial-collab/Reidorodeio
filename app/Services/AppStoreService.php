<?php

namespace App\Services;

use App\Models\AppStoreProduct;
use App\Models\AppStorePurchase;
use App\Models\AppUserVoucher;
use App\Models\AppWalletTransaction;
use App\Models\FantasyLeague;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppStoreService
{
    public function __construct(
        private readonly MercadoPagoService $mercadoPagoService
    ) {
    }

    public function overview(?User $user, string $platform = 'android'): array
    {
        $normalizedPlatform = strtolower(trim($platform)) ?: 'android';
        $this->syncWebStoreCatalog();

        $recentTransactions = collect();
        $activeVouchers = collect();

        if ($user) {
            $recentTransactions = AppWalletTransaction::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit(10)
                ->get();

            $activeVouchers = AppUserVoucher::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->get();
        }

        $activeVoucherCounts = $activeVouchers
            ->groupBy('app_store_product_id')
            ->map(fn (Collection $items) => $items->count());

        $products = AppStoreProduct::query()
            ->active()
            ->ordered()
            ->get();

        $topUps = $products
            ->where('product_type', 'wallet_topup')
            ->values()
            ->map(fn (AppStoreProduct $product) => $this->mapProduct($product, (int) ($activeVoucherCounts[$product->id] ?? 0), $normalizedPlatform))
            ->all();

        $vouchers = $products
            ->where('product_type', 'voucher')
            ->values()
            ->map(fn (AppStoreProduct $product) => $this->mapProduct($product, (int) ($activeVoucherCounts[$product->id] ?? 0), $normalizedPlatform))
            ->all();

        return [
            'wallet' => [
                'available_balance' => (float) ($user?->balance ?? 0),
                'receivable_balance' => (float) ($user?->receivable_balance ?? 0),
                'active_vouchers' => $activeVouchers->count(),
                'recent_transactions' => $recentTransactions
                    ->map(fn (AppWalletTransaction $transaction) => [
                        'id' => (int) $transaction->id,
                        'direction' => (string) $transaction->direction,
                        'source' => (string) $transaction->source,
                        'amount' => (float) $transaction->amount,
                        'balance_before' => (float) $transaction->balance_before,
                        'balance_after' => (float) $transaction->balance_after,
                        'description' => (string) $transaction->description,
                        'created_at' => optional($transaction->created_at)->toIso8601String(),
                    ])
                    ->values()
                    ->all(),
            ],
            'topups' => $topUps,
            'vouchers' => $vouchers,
            'active_vouchers' => $activeVouchers
                ->map(fn (AppUserVoucher $voucher) => $this->mapVoucher($voucher))
                ->values()
                ->all(),
            'premium' => [
                'requires_platform_billing' => in_array($normalizedPlatform, ['android', 'ios'], true),
                'items' => SubscriptionPlan::query()
                    ->active()
                    ->ordered()
                    ->get()
                    ->map(fn (SubscriptionPlan $plan) => $this->mapPremiumPlan($plan, $normalizedPlatform))
                    ->values()
                    ->all(),
            ],
            'policy' => $this->policyNotes($normalizedPlatform),
        ];
    }

    private function syncWebStoreCatalog(): void
    {
        if (!Schema::hasTable('app_store_products')) {
            return;
        }

        $now = now();

        foreach ($this->webStoreCatalogDefinitions() as $product) {
            AppStoreProduct::query()->updateOrCreate(
                ['slug' => $product['slug']],
                array_merge($product, ['updated_at' => $now])
            );
        }

        AppStoreProduct::query()
            ->where('slug', 'voucher_combo_155_bonus20')
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);

        AppStoreProduct::query()
            ->where('slug', 'voucher_combo_boloes_150')
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    private function webStoreCatalogDefinitions(): array
    {
        return [
            [
                'slug' => 'voucher_bolao_20',
                'title' => 'Bilhete de bolão R$ 20',
                'subtitle' => 'Entrada para ligas de até R$ 20',
                'description' => 'Garante um bilhete para bolões pagos com valor de até R$ 20.',
                'product_type' => 'voucher',
                'price' => 20.00,
                'currency' => 'BRL',
                'payment_methods' => ['pix', 'wallet'],
                'badge' => 'Bilhete R$ 20',
                'badge_color' => '#3b82f6',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 40,
                'metadata' => [
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 20.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 60,
                    'bonus_copy' => '1 bilhete liberado em bolões de até R$ 20.',
                ],
            ],
            [
                'slug' => 'voucher_bolao_50',
                'title' => 'Bilhete de bolão R$ 50',
                'subtitle' => 'Entrada para ligas de até R$ 50',
                'description' => 'Garante um bilhete para bolões pagos com valor de até R$ 50.',
                'product_type' => 'voucher',
                'price' => 50.00,
                'currency' => 'BRL',
                'payment_methods' => ['pix', 'wallet'],
                'badge' => 'Bilhete R$ 50',
                'badge_color' => '#22c55e',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 50,
                'metadata' => [
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 50.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 60,
                    'bonus_copy' => '1 bilhete liberado em bolões de até R$ 50.',
                ],
            ],
            [
                'slug' => 'voucher_bolao_100',
                'title' => 'Bilhete de bolão R$ 100',
                'subtitle' => 'Entrada para ligas de até R$ 100',
                'description' => 'Garante um bilhete para bolões pagos com valor de até R$ 100.',
                'product_type' => 'voucher',
                'price' => 100.00,
                'currency' => 'BRL',
                'payment_methods' => ['pix', 'wallet'],
                'badge' => 'Bilhete R$ 100',
                'badge_color' => '#f97316',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 60,
                'metadata' => [
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 100.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 60,
                    'bonus_copy' => '1 bilhete liberado em bolões de até R$ 100.',
                ],
            ],
            [
                'slug' => 'voucher_combo_boloes_170',
                'title' => 'Combo 3 bilhetes por R$ 170',
                'subtitle' => '1 bilhete de cada bolão',
                'description' => 'Leve um pacote com bilhetes de R$ 20, R$ 50 e R$ 100 no mesmo checkout.',
                'product_type' => 'voucher',
                'price' => 170.00,
                'currency' => 'BRL',
                'payment_methods' => ['pix', 'wallet'],
                'badge' => 'Promoção',
                'badge_color' => '#eab308',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 70,
                'metadata' => [
                    'voucher_type' => 'fantasy_ticket',
                    'expires_in_days' => 90,
                    'bonus_copy' => 'Combo com 3 bilhetes: bolões de R$ 20, R$ 50 e R$ 100.',
                    'bundle_vouchers' => [
                        [
                            'title' => 'Bilhete de bolão R$ 20',
                            'description' => '1 bilhete em bolões pagos de até R$ 20.',
                            'voucher_type' => 'fantasy_ticket',
                            'credit_amount' => 20.00,
                            'remaining_uses' => 1,
                            'expires_in_days' => 90,
                        ],
                        [
                            'title' => 'Bilhete de bolão R$ 50',
                            'description' => '1 bilhete em bolões pagos de até R$ 50.',
                            'voucher_type' => 'fantasy_ticket',
                            'credit_amount' => 50.00,
                            'remaining_uses' => 1,
                            'expires_in_days' => 90,
                        ],
                        [
                            'title' => 'Bilhete de bolão R$ 100',
                            'description' => '1 bilhete em bolões pagos de até R$ 100.',
                            'voucher_type' => 'fantasy_ticket',
                            'credit_amount' => 100.00,
                            'remaining_uses' => 1,
                            'expires_in_days' => 90,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function createWalletTopUp(User $user, float $amount): AppStorePurchase
    {
        $roundedAmount = round($amount, 2);

        if ($roundedAmount < 10) {
            throw ValidationException::withMessages([
                'amount' => 'A recarga mínima da carteira é de R$ 10,00.',
            ]);
        }

        if ($roundedAmount > 5000) {
            throw ValidationException::withMessages([
                'amount' => 'A recarga máxima por operação é de R$ 5.000,00.',
            ]);
        }

        return $this->createPixPurchase(
            user: $user,
            amount: $roundedAmount,
            description: 'Recarga de carteira',
            purchaseKind: 'wallet_topup',
            walletCreditAmount: $roundedAmount,
            payload: [
                'custom_amount' => true,
                'product_title' => 'Recarga de carteira',
            ],
        );
    }

    public function purchaseProduct(User $user, AppStoreProduct $product, string $paymentMethod, int $quantity = 1): AppStorePurchase
    {
        if (!$product->is_active) {
            throw ValidationException::withMessages([
                'product' => 'Este item não está disponível no momento.',
            ]);
        }

        if (!$product->supportsPaymentMethod($paymentMethod)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Este item não aceita a forma de pagamento escolhida.',
            ]);
        }

        if ($product->product_type === 'wallet_topup') {
            if ($paymentMethod !== 'pix') {
                throw ValidationException::withMessages([
                    'payment_method' => 'Recarga de carteira usa PIX neste fluxo.',
                ]);
            }

            $creditAmount = (float) data_get($product->metadata, 'credit_amount', $product->price);

            return $this->createPixPurchase(
                user: $user,
                amount: (float) $product->price,
                description: $product->title,
                purchaseKind: 'wallet_topup',
                walletCreditAmount: $creditAmount,
                product: $product,
            );
        }

        if ($product->product_type !== 'voucher') {
            throw ValidationException::withMessages([
                'product' => 'Tipo de item ainda não suportado pela loja.',
            ]);
        }

        $purchaseQuantity = $this->normalizeVoucherPurchaseQuantity($quantity);
        $totalAmount = round((float) $product->price * $purchaseQuantity, 2);
        $purchaseDescription = $this->buildStorePurchaseDescription($product->title, $purchaseQuantity);

        if ($paymentMethod === 'wallet') {
            return DB::transaction(function () use ($user, $product, $purchaseQuantity, $totalAmount, $purchaseDescription) {
                $purchase = AppStorePurchase::create([
                    'user_id' => $user->id,
                    'app_store_product_id' => $product->id,
                    'purchase_kind' => 'voucher',
                    'status' => 'approved',
                    'payment_method' => 'wallet',
                    'provider' => 'balance',
                    'amount' => $totalAmount,
                    'wallet_credit_amount' => 0,
                    'external_reference' => 'store:wallet:' . $user->id . ':' . Str::lower(Str::random(12)),
                    'description' => $purchaseDescription,
                    'paid_at' => now(),
                    'payload' => array_merge($this->snapshotProduct($product), [
                        'quantity' => $purchaseQuantity,
                    ]),
                ]);

                $this->debitWallet(
                    userId: (int) $user->id,
                    amount: $totalAmount,
                    source: 'voucher_purchase',
                    description: 'Compra na loja: ' . $purchaseDescription,
                    purchaseId: (int) $purchase->id,
                    metadata: [
                        'product_slug' => $product->slug,
                        'quantity' => $purchaseQuantity,
                    ],
                );

                $this->fulfillPurchase($purchase->fresh(['product']));

                return $purchase->fresh(['product']);
            });
        }

        return $this->createPixPurchase(
            user: $user,
            amount: $totalAmount,
            description: $purchaseDescription,
            purchaseKind: 'voucher',
            walletCreditAmount: 0,
            product: $product,
            payload: [
                'quantity' => $purchaseQuantity,
            ],
        );
    }

    public function refreshPurchase(User $user, AppStorePurchase $purchase): AppStorePurchase
    {
        $this->assertPurchaseOwner($user, $purchase);

        if ($purchase->status !== 'pending') {
            return $purchase->fresh(['product']);
        }

        if ($purchase->expires_at && $purchase->expires_at->isPast()) {
            $purchase->update(['status' => 'expired']);
            return $purchase->fresh(['product']);
        }

        if (!$purchase->provider_payment_id) {
            return $purchase->fresh(['product']);
        }

        try {
            $payment = $this->mercadoPagoService->fetchPayment($purchase->provider_payment_id);
            $status = (string) ($payment['status'] ?? 'pending');

            if ($status === 'approved') {
                return $this->finalizeApprovedPixPurchase($purchase, $payment);
            }

            if (in_array($status, ['cancelled', 'rejected'], true)) {
                $payload = $purchase->payload ?? [];
                $payload['provider_status'] = $status;
                $purchase->update([
                    'status' => $status,
                    'payload' => $payload,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Falha ao verificar pagamento da loja', [
                'purchase_id' => $purchase->id,
                'provider_payment_id' => $purchase->provider_payment_id,
                'error' => $exception->getMessage(),
            ]);
        }

        return $purchase->fresh(['product']);
    }

    public function cancelPurchase(User $user, AppStorePurchase $purchase): AppStorePurchase
    {
        $this->assertPurchaseOwner($user, $purchase);

        if ($purchase->status !== 'pending') {
            return $purchase->fresh(['product']);
        }

        if ($purchase->provider_payment_id) {
            try {
                $this->mercadoPagoService->cancelPayment($purchase->provider_payment_id);
            } catch (\Throwable $exception) {
                Log::warning('Falha ao cancelar pagamento da loja', [
                    'purchase_id' => $purchase->id,
                    'provider_payment_id' => $purchase->provider_payment_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $purchase->update(['status' => 'cancelled']);

        return $purchase->fresh(['product']);
    }

    public function eligibleFantasyVoucher(User $user, float $entryAmount): ?AppUserVoucher
    {
        $normalizedEntryAmount = $this->normalizeFantasyVoucherEntryAmount($entryAmount);

        if ($normalizedEntryAmount === null) {
            return null;
        }

        return AppUserVoucher::query()
            ->where('user_id', $user->id)
            ->where('voucher_type', 'fantasy_ticket')
            ->where('status', 'active')
            ->where('remaining_uses', '>', 0)
            ->where('credit_amount', '=', $normalizedEntryAmount)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('expires_at')
            ->orderBy('id')
            ->first();
    }

    public function consumeFantasyVoucher(AppUserVoucher $voucher, FantasyLeague $league): AppUserVoucher
    {
        return DB::transaction(function () use ($voucher, $league) {
            /** @var AppUserVoucher|null $lockedVoucher */
            $lockedVoucher = AppUserVoucher::query()
                ->whereKey($voucher->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedVoucher || !$lockedVoucher->isUsableForAmount((float) ($league->price ?? 0))) {
                throw ValidationException::withMessages([
                    'voucher' => 'O voucher não está mais disponível para este bolão.',
                ]);
            }

            $remainingUses = max(0, (int) $lockedVoucher->remaining_uses - 1);

            $lockedVoucher->status = $remainingUses > 0 ? 'active' : 'used';
            $lockedVoucher->remaining_uses = $remainingUses;
            $lockedVoucher->fantasy_league_id = $league->id;
            $lockedVoucher->used_at = now();
            $lockedVoucher->save();

            return $lockedVoucher->fresh();
        });
    }

    private function normalizeFantasyVoucherEntryAmount(float $entryAmount): ?float
    {
        $normalized = round($entryAmount, 2);

        return in_array($normalized, [20.00, 50.00, 100.00], true)
            ? $normalized
            : null;
    }

    private function normalizeVoucherPurchaseQuantity(int $quantity): int
    {
        return max(1, min(10, $quantity));
    }

    private function buildStorePurchaseDescription(string $title, int $quantity): string
    {
        return $quantity > 1
            ? $title . ' x' . $quantity
            : $title;
    }

    public function debitFantasyLeagueWallet(
        User $user,
        FantasyLeague $league,
        float $amount,
        array $metadata = []
    ): void {
        $this->debitWallet(
            userId: (int) $user->id,
            amount: round($amount, 2),
            source: 'fantasy_entry',
            description: 'Entrada no bolão: ' . $league->name,
            purchaseId: null,
            metadata: array_merge([
                'fantasy_league_id' => (int) $league->id,
            ], $metadata),
        );
    }

    private function createPixPurchase(
        User $user,
        float $amount,
        string $description,
        string $purchaseKind,
        float $walletCreditAmount = 0,
        ?AppStoreProduct $product = null,
        array $payload = []
    ): AppStorePurchase {
        $externalReference = 'store:' . $purchaseKind . '|user:' . $user->id . '|' . Str::lower(Str::random(10));

        $purchase = AppStorePurchase::create([
            'user_id' => $user->id,
            'app_store_product_id' => $product?->id,
            'purchase_kind' => $purchaseKind,
            'status' => 'pending',
            'payment_method' => 'pix',
            'provider' => 'mercadopago',
            'amount' => round($amount, 2),
            'wallet_credit_amount' => round($walletCreditAmount, 2),
            'external_reference' => $externalReference,
            'description' => $description,
            'expires_at' => now()->addMinutes(30),
            'payload' => array_merge(
                $payload,
                $this->snapshotProduct($product),
            ),
        ]);

        try {
            $payment = $this->mercadoPagoService->createPixPayment([
                'transaction_amount' => round($amount, 2),
                'description' => $description . ' - ' . config('app.name'),
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $user->email,
                    'first_name' => $user->firstname ?? ($user->username ?: 'Cliente'),
                    'last_name' => $user->lastname ?? '',
                    'entity_type' => 'individual',
                ],
                'external_reference' => $externalReference,
            ]);
        } catch (\Throwable $exception) {
            $purchase->update(['status' => 'failed']);
            throw ValidationException::withMessages([
                'payment' => 'Não foi possível gerar o PIX da loja agora. Tente novamente.',
            ]);
        }

        $nextPayload = array_merge($purchase->payload ?? [], [
            'qr_code' => data_get($payment, 'point_of_interaction.transaction_data.qr_code'),
            'qr_code_base64' => data_get($payment, 'point_of_interaction.transaction_data.qr_code_base64'),
        ]);

        $purchase->update([
            'provider_payment_id' => (string) ($payment['id'] ?? ''),
            'provider_preference_id' => (string) ($payment['id'] ?? ''),
            'payload' => $nextPayload,
        ]);

        return $purchase->fresh(['product']);
    }

    private function finalizeApprovedPixPurchase(AppStorePurchase $purchase, array $providerPayload): AppStorePurchase
    {
        return DB::transaction(function () use ($purchase, $providerPayload) {
            /** @var AppStorePurchase $lockedPurchase */
            $lockedPurchase = AppStorePurchase::query()
                ->with('product')
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPurchase->fulfilled_at) {
                return $lockedPurchase;
            }

            $payload = $lockedPurchase->payload ?? [];
            $payload['provider_status'] = (string) ($providerPayload['status'] ?? 'approved');

            $lockedPurchase->status = 'approved';
            $lockedPurchase->paid_at = $lockedPurchase->paid_at ?? now();
            $lockedPurchase->payload = $payload;
            $lockedPurchase->save();

            $this->fulfillPurchase($lockedPurchase);

            return $lockedPurchase->fresh(['product']);
        });
    }

    private function fulfillPurchase(AppStorePurchase $purchase): void
    {
        if ($purchase->fulfilled_at) {
            return;
        }

        $product = $purchase->product;

        if ($purchase->purchase_kind === 'wallet_topup') {
            $creditAmount = (float) ($purchase->wallet_credit_amount ?: $purchase->amount);

            $this->creditWallet(
                userId: (int) $purchase->user_id,
                amount: $creditAmount,
                source: 'wallet_topup',
                description: 'Recarga aprovada na loja',
                purchaseId: (int) $purchase->id,
                metadata: [
                    'provider' => $purchase->provider,
                    'provider_payment_id' => $purchase->provider_payment_id,
                ],
            );

            $purchase->update([
                'fulfilled_at' => now(),
            ]);

            return;
        }

        if ($purchase->purchase_kind === 'voucher') {
            $voucherMetadata = $product?->metadata ?? ($purchase->payload['metadata'] ?? []);
            $bundleVouchers = data_get($voucherMetadata, 'bundle_vouchers', []);
            $quantity = max(1, (int) data_get($purchase->payload, 'quantity', 1));

            if (is_array($bundleVouchers) && !empty($bundleVouchers)) {
                for ($copy = 0; $copy < $quantity; $copy++) {
                    foreach ($bundleVouchers as $bundleVoucher) {
                        if (!is_array($bundleVoucher)) {
                            continue;
                        }

                        $this->createVoucherRecord(
                            purchase: $purchase,
                            product: $product,
                            voucherMetadata: $bundleVoucher,
                            fallbackTitle: $product?->title ?? (string) ($purchase->description ?? 'Voucher'),
                            fallbackDescription: $product?->description,
                            extraMetadata: [
                                'bundle_slug' => $product?->slug,
                                'bundle_title' => $product?->title,
                                'bonus_copy' => data_get($voucherMetadata, 'bonus_copy'),
                                'bundle_copy_index' => $copy + 1,
                                'purchase_quantity' => $quantity,
                            ],
                        );
                    }
                }
            } else {
                for ($copy = 0; $copy < $quantity; $copy++) {
                    $this->createVoucherRecord(
                        purchase: $purchase,
                        product: $product,
                        voucherMetadata: is_array($voucherMetadata) ? $voucherMetadata : [],
                        fallbackTitle: $product?->title ?? (string) ($purchase->description ?? 'Voucher'),
                        fallbackDescription: $product?->description,
                        extraMetadata: [
                            'purchase_quantity' => $quantity,
                            'purchase_copy_index' => $copy + 1,
                        ],
                    );
                }
            }

            $purchase->update([
                'fulfilled_at' => now(),
            ]);
        }
    }

    private function createVoucherRecord(
        AppStorePurchase $purchase,
        ?AppStoreProduct $product,
        array $voucherMetadata,
        string $fallbackTitle,
        ?string $fallbackDescription = null,
        array $extraMetadata = []
    ): void {
        $creditAmount = (float) data_get($voucherMetadata, 'credit_amount', $purchase->amount);
        $remainingUses = (int) data_get($voucherMetadata, 'remaining_uses', 1);
        $expiresInDays = (int) data_get($voucherMetadata, 'expires_in_days', 60);

        AppUserVoucher::create([
            'user_id' => $purchase->user_id,
            'app_store_product_id' => $product?->id,
            'app_store_purchase_id' => $purchase->id,
            'voucher_type' => (string) data_get($voucherMetadata, 'voucher_type', 'fantasy_ticket'),
            'status' => 'active',
            'title' => (string) data_get($voucherMetadata, 'title', $fallbackTitle),
            'description' => data_get($voucherMetadata, 'description', $fallbackDescription),
            'credit_amount' => $creditAmount,
            'remaining_uses' => max(1, $remainingUses),
            'activated_at' => now(),
            'expires_at' => now()->addDays(max(1, $expiresInDays)),
            'metadata' => array_merge([
                'product_slug' => $product?->slug,
                'bonus_copy' => data_get($voucherMetadata, 'bonus_copy'),
            ], $extraMetadata),
        ]);
    }

    private function creditWallet(
        int $userId,
        float $amount,
        string $source,
        string $description,
        ?int $purchaseId = null,
        array $metadata = []
    ): void {
        /** @var User $user */
        $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

        $before = round((float) ($user->balance ?? 0), 2);
        $after = round($before + $amount, 2);

        $user->balance = $after;
        $user->save();

        AppWalletTransaction::create([
            'user_id' => $userId,
            'app_store_purchase_id' => $purchaseId,
            'direction' => 'credit',
            'source' => $source,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->recordLegacyTransaction(
            userId: $userId,
            trxType: '+',
            amount: $amount,
            postBalance: $after,
            details: $description,
            remark: 'app_wallet_topup',
        );
    }

    private function debitWallet(
        int $userId,
        float $amount,
        string $source,
        string $description,
        ?int $purchaseId = null,
        array $metadata = []
    ): void {
        /** @var User $user */
        $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

        $before = round((float) ($user->balance ?? 0), 2);
        if ($before < $amount) {
            throw ValidationException::withMessages([
                'wallet' => 'Saldo insuficiente na carteira.',
            ]);
        }

        $after = round($before - $amount, 2);

        $user->balance = $after;
        $user->save();

        AppWalletTransaction::create([
            'user_id' => $userId,
            'app_store_purchase_id' => $purchaseId,
            'direction' => 'debit',
            'source' => $source,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $this->recordLegacyTransaction(
            userId: $userId,
            trxType: '-',
            amount: $amount,
            postBalance: $after,
            details: $description,
            remark: 'app_wallet_debit',
        );
    }

    private function recordLegacyTransaction(
        int $userId,
        string $trxType,
        float $amount,
        float $postBalance,
        string $details,
        string $remark
    ): void {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        DB::table('transactions')->insert([
            'user_id' => $userId,
            'trx_type' => $trxType,
            'trx' => getTrx(),
            'amount' => $amount,
            'charge' => 0,
            'post_balance' => $postBalance,
            'details' => $details,
            'remark' => $remark,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assertPurchaseOwner(User $user, AppStorePurchase $purchase): void
    {
        if ((int) $purchase->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'purchase' => 'Compra não encontrada para este usuário.',
            ]);
        }
    }

    private function snapshotProduct(?AppStoreProduct $product): array
    {
        if (!$product) {
            return [];
        }

        return [
            'product_id' => (int) $product->id,
            'product_slug' => (string) $product->slug,
            'product_title' => (string) $product->title,
            'product_type' => (string) $product->product_type,
            'metadata' => $product->metadata ?? [],
        ];
    }

    private function mapProduct(AppStoreProduct $product, int $ownedCount = 0, string $platform = 'android'): array
    {
        $paymentMethods = $product->payment_methods ?? [];
        if ($platform === 'ios') {
            $paymentMethods = [];
        }

        return [
            'id' => (int) $product->id,
            'slug' => (string) $product->slug,
            'title' => (string) $product->title,
            'subtitle' => $product->subtitle,
            'description' => $product->description,
            'product_type' => (string) $product->product_type,
            'price' => (float) $product->price,
            'formatted_price' => 'R$ ' . number_format((float) $product->price, 2, ',', '.'),
            'payment_methods' => $paymentMethods,
            'badge' => $product->badge,
            'badge_color' => $product->badge_color,
            'is_featured' => (bool) $product->is_featured,
            'owned_active_count' => $ownedCount,
            'max_quantity' => $product->product_type === 'voucher' ? 10 : 1,
            'metadata' => $product->metadata ?? [],
        ];
    }

    private function mapVoucher(AppUserVoucher $voucher): array
    {
        return [
            'id' => (int) $voucher->id,
            'title' => (string) $voucher->title,
            'description' => $voucher->description,
            'status' => (string) $voucher->status,
            'credit_amount' => (float) $voucher->credit_amount,
            'remaining_uses' => (int) $voucher->remaining_uses,
            'expires_at' => optional($voucher->expires_at)->toIso8601String(),
            'used_at' => optional($voucher->used_at)->toIso8601String(),
            'metadata' => $voucher->metadata ?? [],
        ];
    }

    private function mapPremiumPlan(SubscriptionPlan $plan, string $platform): array
    {
        $requiresPlatformBilling = in_array($platform, ['android', 'ios'], true);
        $checkoutCta = 'Abrir Premium na arena';
        $checkoutNote = $requiresPlatformBilling
            ? 'A versão publicada do app deve usar a cobrança nativa da loja para o Premium.'
            : 'Enquanto o billing nativo não estiver configurado, use a área premium da arena.';

        if ($platform === 'ios') {
            $checkoutCta = 'Disponivel em breve no iPhone';
            $checkoutNote = 'No iOS, o Premium precisa usar compra nativa da App Store. O checkout externo foi removido desta versao.';
        }

        return [
            'id' => (int) $plan->id,
            'name' => (string) $plan->name,
            'slug' => (string) $plan->slug,
            'price' => (float) $plan->price,
            'formatted_price' => $plan->formatted_price,
            'formatted_monthly_price' => $plan->formatted_monthly_price,
            'description' => $plan->description,
            'features' => $plan->features ?? [],
            'payment_methods' => $plan->payment_methods ?? ['pix'],
            'badge' => $plan->badge,
            'badge_color' => $plan->badge_color,
            'is_featured' => (bool) ($plan->is_featured ?? false),
            'is_recurring' => (bool) ($plan->is_recurring ?? false),
            'billing_cycle' => (string) ($plan->billing_cycle ?? ''),
            'period_label' => $plan->period_label,
            'trial_days' => (int) ($plan->trial_days ?? 0),
            'has_trial' => (bool) ($plan->has_trial ?? false),
            'monthly_price' => (float) $plan->monthly_price,
            'savings' => (float) $plan->savings,
            'free_months' => (int) $plan->free_months,
            'android_product_id' => $plan->android_product_id,
            'ios_product_id' => $plan->ios_product_id,
            'checkout_mode' => $requiresPlatformBilling ? 'platform_billing' : 'arena_checkout',
            'native_checkout_enabled' => false,
            'checkout_cta' => $checkoutCta,
            'checkout_note' => $checkoutNote,
        ];
    }

    private function policyNotes(string $platform): array
    {
        return [
            'wallet' => $platform === 'ios'
                ? 'No iPhone e iPad, recargas, pagamentos financeiros e saques sao concluídos somente no site externo.'
                : 'A carteira do app aceita PIX para recarga e mostra o saldo disponivel para compras internas.',
            'premium' => in_array($platform, ['android', 'ios'], true)
                ? ($platform === 'ios'
                    ? 'No iOS, o Premium fica apenas em modo informativo ate a integracao com StoreKit.'
                    : 'Premium e conteudo digital e deve usar billing da plataforma no app publicado.')
                : 'Premium pode ficar disponível pela área web/arena enquanto o billing nativo não estiver configurado.',
            'fantasy' => $platform === 'ios'
                ? 'No iOS, pagamentos do X1, bolao e vouchers ficam fora do app e seguem para o site.'
                : 'Carteira e vouchers usam PIX/Mercado Pago neste fluxo. A distribuicao em lojas moveis depende da revisao de politicas de fantasy/real-money.',
        ];
    }
}
