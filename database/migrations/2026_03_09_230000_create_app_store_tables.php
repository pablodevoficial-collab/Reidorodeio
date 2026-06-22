<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_store_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('product_type', 40);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('BRL');
            $table->json('payment_methods')->nullable();
            $table->string('badge')->nullable();
            $table->string('badge_color', 20)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('android_product_id')->nullable();
            $table->string('ios_product_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('app_store_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('app_store_product_id')->nullable()->constrained('app_store_products')->nullOnDelete();
            $table->string('purchase_kind', 40);
            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 30)->default('pix');
            $table->string('provider', 40)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('wallet_credit_amount', 12, 2)->default(0);
            $table->string('external_reference')->unique();
            $table->string('provider_payment_id')->nullable()->index();
            $table->string('provider_preference_id')->nullable()->index();
            $table->string('description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('app_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('app_store_purchase_id')->nullable()->constrained('app_store_purchases')->nullOnDelete();
            $table->string('direction', 10);
            $table->string('source', 40);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 18, 2)->default(0);
            $table->decimal('balance_after', 18, 2)->default(0);
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('app_user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('app_store_product_id')->nullable()->constrained('app_store_products')->nullOnDelete();
            $table->foreignId('app_store_purchase_id')->nullable()->constrained('app_store_purchases')->nullOnDelete();
            $table->string('voucher_type', 40)->default('fantasy_ticket');
            $table->string('status', 30)->default('active');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('remaining_uses')->default(1);
            $table->foreignId('fantasy_league_id')->nullable()->constrained('fantasy_leagues')->nullOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('android_product_id')->nullable()->after('slug');
            $table->string('ios_product_id')->nullable()->after('android_product_id');
        });

        DB::table('subscription_plans')
            ->whereNull('android_product_id')
            ->where('slug', 'mensal')
            ->update([
                'android_product_id' => 'premium_mensal',
                'ios_product_id' => 'premium_mensal',
            ]);

        DB::table('subscription_plans')
            ->whereNull('android_product_id')
            ->where('slug', 'semestral')
            ->update([
                'android_product_id' => 'premium_semestral',
                'ios_product_id' => 'premium_semestral',
            ]);

        DB::table('subscription_plans')
            ->whereNull('android_product_id')
            ->where('slug', 'anual')
            ->update([
                'android_product_id' => 'premium_anual',
                'ios_product_id' => 'premium_anual',
            ]);

        $now = now();

        DB::table('app_store_products')->insert([
            [
                'slug' => 'wallet_topup_50',
                'title' => 'Adicionar R$ 50',
                'subtitle' => 'Recarga rápida',
                'description' => 'Crédito instantâneo na carteira para futuras compras no app.',
                'product_type' => 'wallet_topup',
                'price' => 50.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix']),
                'badge' => 'Popular',
                'badge_color' => '#f97316',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 10,
                'metadata' => json_encode([
                    'credit_amount' => 50.00,
                    'icon' => 'wallet',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'wallet_topup_100',
                'title' => 'Adicionar R$ 100',
                'subtitle' => 'Saldo reforçado',
                'description' => 'Use na compra de vouchers e novas experiências do app.',
                'product_type' => 'wallet_topup',
                'price' => 100.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix']),
                'badge' => 'Melhor giro',
                'badge_color' => '#22c55e',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 20,
                'metadata' => json_encode([
                    'credit_amount' => 100.00,
                    'icon' => 'wallet',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'wallet_topup_200',
                'title' => 'Adicionar R$ 200',
                'subtitle' => 'Carteira turbo',
                'description' => 'Mais saldo disponível para vouchers e ativações futuras.',
                'product_type' => 'wallet_topup',
                'price' => 200.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix']),
                'badge' => 'Alta rotação',
                'badge_color' => '#eab308',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 30,
                'metadata' => json_encode([
                    'credit_amount' => 200.00,
                    'icon' => 'wallet',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'voucher_bolao_20',
                'title' => 'Voucher de bolão R$ 20',
                'subtitle' => '1 entrada garantida',
                'description' => 'Libera uma participação em bolões pagos de até R$ 20.',
                'product_type' => 'voucher',
                'price' => 20.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix', 'wallet']),
                'badge' => 'Bolão',
                'badge_color' => '#3b82f6',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 40,
                'metadata' => json_encode([
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 20.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 60,
                    'bonus_copy' => '1 entrada liberada em bolões de até R$ 20.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'voucher_combo_155_bonus20',
                'title' => 'Voucher R$ 155 + bolão grátis',
                'subtitle' => 'Destaque da loja',
                'description' => 'Ao comprar este voucher, o cliente desbloqueia 1 entrada gratuita em bolão de até R$ 20.',
                'product_type' => 'voucher',
                'price' => 155.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix', 'wallet']),
                'badge' => 'Combo R$ 155',
                'badge_color' => '#ef4444',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 50,
                'metadata' => json_encode([
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 20.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 90,
                    'bonus_copy' => 'Garante o direito de entrar em 1 bolão de até R$ 20 sem novo pagamento.',
                    'campaign_price' => 155.00,
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'android_product_id',
                'ios_product_id',
            ]);
        });

        Schema::dropIfExists('app_user_vouchers');
        Schema::dropIfExists('app_wallet_transactions');
        Schema::dropIfExists('app_store_purchases');
        Schema::dropIfExists('app_store_products');
    }
};
