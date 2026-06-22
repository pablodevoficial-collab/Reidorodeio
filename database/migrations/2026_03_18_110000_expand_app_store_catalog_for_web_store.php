<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $products = [
            [
                'slug' => 'voucher_bolao_20',
                'title' => 'Voucher de bolão R$ 20',
                'subtitle' => 'Entrada para ligas de até R$ 20',
                'description' => 'Garante uma entrada em bolões pagos com valor de até R$ 20.',
                'product_type' => 'voucher',
                'price' => 20.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix', 'wallet']),
                'badge' => 'Bolão R$ 20',
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
                'updated_at' => $now,
            ],
            [
                'slug' => 'voucher_bolao_50',
                'title' => 'Voucher de bolão R$ 50',
                'subtitle' => 'Entrada para ligas de até R$ 50',
                'description' => 'Garante uma entrada em bolões pagos com valor de até R$ 50.',
                'product_type' => 'voucher',
                'price' => 50.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix', 'wallet']),
                'badge' => 'Bolão R$ 50',
                'badge_color' => '#22c55e',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 50,
                'metadata' => json_encode([
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 50.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 60,
                    'bonus_copy' => '1 entrada liberada em bolões de até R$ 50.',
                ]),
                'updated_at' => $now,
            ],
            [
                'slug' => 'voucher_bolao_100',
                'title' => 'Voucher de bolão R$ 100',
                'subtitle' => 'Entrada para ligas de até R$ 100',
                'description' => 'Garante uma entrada em bolões pagos com valor de até R$ 100.',
                'product_type' => 'voucher',
                'price' => 100.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix', 'wallet']),
                'badge' => 'Bolão R$ 100',
                'badge_color' => '#f97316',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 60,
                'metadata' => json_encode([
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 100.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 60,
                    'bonus_copy' => '1 entrada liberada em bolões de até R$ 100.',
                ]),
                'updated_at' => $now,
            ],
            [
                'slug' => 'voucher_combo_boloes_150',
                'title' => 'Combo 3 vouchers por R$ 150',
                'subtitle' => '1 voucher de cada bolão',
                'description' => 'Leve um pacote com vouchers de R$ 20, R$ 50 e R$ 100 no mesmo checkout.',
                'product_type' => 'voucher',
                'price' => 150.00,
                'currency' => 'BRL',
                'payment_methods' => json_encode(['pix', 'wallet']),
                'badge' => 'Promoção',
                'badge_color' => '#eab308',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 70,
                'metadata' => json_encode([
                    'voucher_type' => 'fantasy_ticket',
                    'expires_in_days' => 90,
                    'bonus_copy' => 'Combo com 3 entradas: bolões de R$ 20, R$ 50 e R$ 100.',
                    'bundle_vouchers' => [
                        [
                            'title' => 'Voucher de bolão R$ 20',
                            'description' => '1 entrada em bolões pagos de até R$ 20.',
                            'voucher_type' => 'fantasy_ticket',
                            'credit_amount' => 20.00,
                            'remaining_uses' => 1,
                            'expires_in_days' => 90,
                        ],
                        [
                            'title' => 'Voucher de bolão R$ 50',
                            'description' => '1 entrada em bolões pagos de até R$ 50.',
                            'voucher_type' => 'fantasy_ticket',
                            'credit_amount' => 50.00,
                            'remaining_uses' => 1,
                            'expires_in_days' => 90,
                        ],
                        [
                            'title' => 'Voucher de bolão R$ 100',
                            'description' => '1 entrada em bolões pagos de até R$ 100.',
                            'voucher_type' => 'fantasy_ticket',
                            'credit_amount' => 100.00,
                            'remaining_uses' => 1,
                            'expires_in_days' => 90,
                        ],
                    ],
                ]),
                'updated_at' => $now,
            ],
        ];

        foreach ($products as $product) {
            $slug = $product['slug'];
            $exists = DB::table('app_store_products')->where('slug', $slug)->exists();

            DB::table('app_store_products')->updateOrInsert(
                ['slug' => $slug],
                array_merge(
                    $product,
                    $exists ? [] : ['created_at' => $now],
                )
            );
        }

        DB::table('app_store_products')
            ->where('slug', 'voucher_combo_155_bonus20')
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        DB::table('app_store_products')
            ->whereIn('slug', [
                'voucher_bolao_50',
                'voucher_bolao_100',
                'voucher_combo_boloes_150',
            ])
            ->delete();

        DB::table('app_store_products')
            ->where('slug', 'voucher_bolao_20')
            ->update([
                'subtitle' => '1 entrada garantida',
                'description' => 'Libera uma participação em bolões pagos de até R$ 20.',
                'badge' => 'Bolão',
                'badge_color' => '#3b82f6',
                'sort_order' => 40,
                'metadata' => json_encode([
                    'voucher_type' => 'fantasy_ticket',
                    'credit_amount' => 20.00,
                    'remaining_uses' => 1,
                    'expires_in_days' => 60,
                    'bonus_copy' => '1 entrada liberada em bolões de até R$ 20.',
                ]),
                'updated_at' => now(),
            ]);

        DB::table('app_store_products')
            ->where('slug', 'voucher_combo_155_bonus20')
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }
};
