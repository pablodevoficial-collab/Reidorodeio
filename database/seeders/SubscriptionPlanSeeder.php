<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Premium Mensal',
                'slug' => 'mensal',
                'android_product_id' => 'premium_mensal',
                'ios_product_id' => 'premium_mensal',
                'price' => 49.90,
                'original_price' => 49.90,
                'duration_days' => 30,
                'trial_days' => 3, // 3 dias grátis para novos
                'billing_cycle' => 'monthly',
                'description' => 'Acesso completo a todos os recursos premium com renovação mensal.',
                'features' => [
                    'Taxa X1 reduzida (7-15% vs 10-18%)',
                    'Fantasy Premium gratuito',
                    'Rankings completos',
                    'Alterar username',
                    'Salas X1 exclusivas',
                    'Suporte prioritário',
                    '3 dias grátis para novos usuários',
                ],
                'badge' => 'Experimente Grátis',
                'badge_color' => '#22c55e',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Premium Semestral',
                'slug' => 'semestral',
                'android_product_id' => 'premium_semestral',
                'ios_product_id' => 'premium_semestral',
                'price' => 249.90,
                'original_price' => 299.40, // 6 x 49.90
                'duration_days' => 180,
                'trial_days' => 0,
                'billing_cycle' => 'semiannual',
                'description' => 'Economize com o plano semestral! 1 mês grátis incluído.',
                'features' => [
                    'Tudo do plano mensal',
                    '1 mês grátis (pague 5, leve 6)',
                    'Economia de R$49,50',
                    'Prioridade em novos recursos',
                    'Brindes sazonais',
                ],
                'badge' => 'Popular',
                'badge_color' => '#3b82f6',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium Anual',
                'slug' => 'anual',
                'android_product_id' => 'premium_anual',
                'ios_product_id' => 'premium_anual',
                'price' => 499.90,
                'original_price' => 598.80, // 12 x 49.90
                'duration_days' => 365,
                'trial_days' => 0,
                'billing_cycle' => 'annual',
                'description' => 'Melhor custo-benefício! Quase 2 meses grátis.',
                'features' => [
                    'Tudo do plano semestral',
                    '~2 meses grátis (pague 10, leve 12)',
                    'Economia de R$98,90',
                    'Acesso antecipado a novidades',
                    'Badge exclusivo de fundador',
                    'Brindes especiais de aniversário',
                ],
                'badge' => 'Melhor Oferta',
                'badge_color' => '#f59e0b',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('✅ Planos de assinatura criados/atualizados com sucesso!');
    }
}
