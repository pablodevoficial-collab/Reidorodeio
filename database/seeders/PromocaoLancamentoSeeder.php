<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class PromocaoLancamentoSeeder extends Seeder
{
    /**
     * 🚀 PROMOÇÃO DE LANÇAMENTO - Primeiros 1000 clientes!
     * 
     * MENSAL: 50% OFF
     * - De R$49,90 por R$24,90/mês
     * 
     * SEMESTRAL: 50% OFF  
     * - De R$250,00 por R$124,90
     * 
     * ANUAL: 70% OFF (MEGA DESCONTO!)
     * - De R$500,00 por R$149,90
     */
    public function run(): void
    {
        // Plano Mensal - 50% OFF
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'mensal'],
            [
                'name' => 'Premium Mensal',
                'price' => 24.90,
                'original_price' => 49.90,
                'duration_days' => 30,
                'trial_days' => 3,
                'min_days_for_full_refund' => 3,
                'early_cancel_penalty_months' => 1,
                'billing_cycle' => 'monthly',
                'description' => '🔥 LANÇAMENTO: 50% OFF! De R$49,90 por apenas R$24,90/mês. Primeiros 1000 clientes!',
                'features' => [
                    '🔥 50% OFF - Promoção de Lançamento',
                    'Taxa X1 reduzida (7% até R$1.000)',
                    'Bolão Premium grátis',
                    'Editar username ilimitado',
                    'Salas X1 exclusivas',
                    'Selo e visual premium',
                    '3 dias grátis para testar',
                ],
                'payment_methods' => ['card'],
                'badge' => '🔥 50% OFF',
                'badge_color' => '#ef4444',
                'is_featured' => false,
                'is_recurring' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Plano Semestral - 50% OFF
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'semestral'],
            [
                'name' => 'Premium Semestral',
                'price' => 124.90,
                'original_price' => 249.90,
                'duration_days' => 180,
                'trial_days' => 0,
                'min_days_for_full_refund' => 30,
                'early_cancel_penalty_months' => 2,
                'billing_cycle' => 'semiannual',
                'description' => '🔥 LANÇAMENTO: 50% OFF! De R$249,90 por apenas R$124,90. Primeiros 1000 clientes!',
                'features' => [
                    '🔥 50% OFF - Promoção de Lançamento',
                    'Tudo do plano mensal',
                    'Economia de R$125,00',
                    '6 meses de acesso completo',
                ],
                'payment_methods' => ['pix', 'card'],
                'badge' => '🔥 50% OFF',
                'badge_color' => '#ef4444',
                'is_featured' => true,
                'is_recurring' => false,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Plano Anual - 70% OFF (MEGA!)
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'anual'],
            [
                'name' => 'Premium Anual',
                'price' => 199.90,
                'original_price' => 499.90,
                'duration_days' => 365,
                'trial_days' => 0,
                'min_days_for_full_refund' => 90,
                'early_cancel_penalty_months' => 3,
                'billing_cycle' => 'annual',
                'description' => '🚀 MEGA LANÇAMENTO: 60% OFF! De R$499,90 por apenas R$199,90. Primeiros 1000 clientes!',
                'features' => [
                    '🚀 60% OFF - MEGA Promoção!',
                    'Tudo do plano semestral',
                    'Economia de R$300,00',
                    '12 meses de acesso completo',
                    'Melhor custo-benefício',
                ],
                'payment_methods' => ['pix', 'card'],
                'badge' => '🚀 60% OFF',
                'badge_color' => '#8b5cf6',
                'is_featured' => false,
                'is_recurring' => false,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $this->command->info('');
        $this->command->info('🚀 ═══════════════════════════════════════════════════════');
        $this->command->info('   PROMOÇÃO DE LANÇAMENTO ATIVADA!');
        $this->command->info('   Primeiros 1000 clientes');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->newLine();
        
        $this->command->table(
            ['Plano', 'De', 'Por', 'Desconto', 'Economia'],
            [
                ['Mensal', 'R$49,90', 'R$24,90', '50% OFF', 'R$25,00/mês'],
                ['Semestral', 'R$249,90', 'R$124,90', '50% OFF', 'R$125,00'],
                ['Anual', 'R$499,90', 'R$199,90', '60% OFF', 'R$300,00'],
            ]
        );
        
        $this->command->newLine();
        $this->command->info('✅ Planos atualizados com sucesso!');
    }
}
