<?php

namespace Tests\Unit;

use App\Models\AppUserVoucher;
use App\Models\FantasyLeague;
use App\Models\User;
use App\Services\AppStoreService;
use App\Services\MercadoPagoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class AppStoreServiceTest extends TestCase
{
    private AppStoreService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->service = new AppStoreService(Mockery::mock(MercadoPagoService::class));
    }

    public function test_it_only_matches_vouchers_for_exact_supported_bolao_values(): void
    {
        $user = $this->createUser();

        $voucher20 = AppUserVoucher::query()->create([
            'user_id' => $user->id,
            'voucher_type' => 'fantasy_ticket',
            'status' => 'active',
            'title' => 'Voucher R$ 20',
            'credit_amount' => 20.00,
            'remaining_uses' => 1,
            'activated_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        AppUserVoucher::query()->create([
            'user_id' => $user->id,
            'voucher_type' => 'fantasy_ticket',
            'status' => 'active',
            'title' => 'Voucher R$ 50',
            'credit_amount' => 50.00,
            'remaining_uses' => 1,
            'activated_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertSame($voucher20->id, $this->service->eligibleFantasyVoucher($user, 20.00)?->id);
        $this->assertNull($this->service->eligibleFantasyVoucher($user, 0.01));
        $this->assertNull($this->service->eligibleFantasyVoucher($user, 15.00));
    }

    public function test_it_marks_voucher_as_used_after_consumption(): void
    {
        $user = $this->createUser();
        $league = FantasyLeague::query()->create([
            'name' => 'Bolão 20',
            'price' => 20.00,
        ]);

        $voucher = AppUserVoucher::query()->create([
            'user_id' => $user->id,
            'voucher_type' => 'fantasy_ticket',
            'status' => 'active',
            'title' => 'Voucher R$ 20',
            'credit_amount' => 20.00,
            'remaining_uses' => 1,
            'activated_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $consumedVoucher = $this->service->consumeFantasyVoucher($voucher, $league);

        $this->assertSame('used', $consumedVoucher->status);
        $this->assertSame(0, $consumedVoucher->remaining_uses);
        $this->assertSame($league->id, $consumedVoucher->fantasy_league_id);
        $this->assertNotNull($consumedVoucher->used_at);
        $this->assertNull($this->service->eligibleFantasyVoucher($user, 20.00));
    }

    public function test_it_creates_multiple_ticket_records_for_quantity_purchase(): void
    {
        $user = $this->createUser(500);
        $product = \App\Models\AppStoreProduct::query()->create([
            'slug' => 'voucher_bolao_20',
            'title' => 'Bilhete de bolão R$ 20',
            'product_type' => 'voucher',
            'price' => 20.00,
            'currency' => 'BRL',
            'payment_methods' => ['wallet', 'pix'],
            'is_active' => true,
            'metadata' => [
                'voucher_type' => 'fantasy_ticket',
                'credit_amount' => 20.00,
                'remaining_uses' => 1,
                'expires_in_days' => 60,
            ],
        ]);

        $purchase = $this->service->purchaseProduct($user, $product, 'wallet', 3);

        $this->assertSame('approved', $purchase->status);
        $this->assertSame(60.0, (float) $purchase->amount);
        $this->assertSame(3, (int) ($purchase->payload['quantity'] ?? 0));
        $this->assertSame(3, AppUserVoucher::query()->where('app_store_purchase_id', $purchase->id)->count());
        $this->assertDatabaseHas('app_wallet_transactions', [
            'app_store_purchase_id' => $purchase->id,
            'amount' => 60,
            'source' => 'voucher_purchase',
        ]);
    }

    private function createUser(float $balance = 0): User
    {
        $user = new User([
            'firstname' => 'Teste',
            'lastname' => 'Voucher',
            'username' => 'voucher_' . uniqid(),
            'email' => uniqid('voucher_', true) . '@example.com',
            'password' => bcrypt('secret'),
        ]);

        $user->balance = $balance;
        $user->save();

        return $user;
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->boolean('is_bot')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('fantasy_leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('app_user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->unsignedBigInteger('app_store_product_id')->nullable();
            $table->unsignedBigInteger('app_store_purchase_id')->nullable();
            $table->string('voucher_type');
            $table->string('status')->default('active');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->integer('remaining_uses')->default(1);
            $table->unsignedBigInteger('fantasy_league_id')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('app_store_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('product_type');
            $table->decimal('price', 12, 2);
            $table->string('currency')->default('BRL');
            $table->json('payment_methods')->nullable();
            $table->string('badge')->nullable();
            $table->string('badge_color')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('app_store_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->unsignedBigInteger('app_store_product_id')->nullable();
            $table->string('purchase_kind');
            $table->string('status');
            $table->string('payment_method')->nullable();
            $table->string('provider')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('wallet_credit_amount', 12, 2)->default(0);
            $table->string('external_reference')->nullable();
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_preference_id')->nullable();
            $table->string('description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('app_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->unsignedBigInteger('app_store_purchase_id')->nullable();
            $table->string('direction');
            $table->string('source');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('balance_before', 12, 2)->default(0);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }
}
