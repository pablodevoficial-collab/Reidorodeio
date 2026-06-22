<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class UpdateSubscriptionPlansSeeder extends Seeder
{
    /**
     * Atualiza os planos de assinatura com as novas regras:
     * 
     * MENSAL (Cartão):
     * - 3 dias grátis (trial único por CPF)
     * - Se cancelar no trial: R$0
     * - Se cancelar após trial: multa de 1 mês (R$49,90)
     * 
     * SEMESTRAL (PIX ou Cartão):
     * - R$250,00 (economia de ~R$50)
     * - Se cancelar antes de 1 mês: multa de 2 meses (R$99,80)
     * - Após 1 mês: reembolso proporcional
     * 
     * ANUAL (PIX ou Cartão):
     * - R$500,00 (economia de ~R$100)
     * - Se cancelar antes de 3 meses: multa de 3 meses (R$149,70)
     * - Após 3 meses: reembolso proporcional
     */
    public function run(): void
    {
        // Plano Mensal (Cartão de Crédito com Trial)
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'mensal'],
            [
                'name' => 'Premium Mensal',
                'price' => 49.90,
                'original_price' => 49.90,
                'duration_days' => 30,
                'trial_days' => 3,
                'min_days_for_full_refund' => 3, // Pode cancelar grátis no trial
                'early_cancel_penalty_months' => 1, // Multa de 1 mês após trial
                'billing_cycle' => 'monthly',
                'description' => '3 dias grátis para novos membros! Depois R$49,90/mês. Cancele quando quiser.',
                'features' => [
                    'Taxa X1 reduzida (7%)',
                    'Bolão Premium grátis',
                    'Estatísticas avançadas',
                    'Editar username ilimitado',
                    'Salas X1 exclusivas',
                    'Selo e visual premium',
                    '3 dias grátis (1x por CPF)',
                ],
                'payment_methods' => ['card'],
                'badge' => '3 Dias Grátis',
                'badge_color' => '#22c55e',
                'is_featured' => false,
                'is_recurring' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Plano Semestral (PIX ou Cartão)
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'semestral'],
            [
                'name' => 'Premium Semestral',
                'price' => 250.00,
                'original_price' => 299.40, // 6 x 49.90
                'duration_days' => 180,
                'trial_days' => 0, // Sem trial
                'min_days_for_full_refund' => 30, // 1 mês
                'early_cancel_penalty_months' => 2, // Multa de 2 meses
                'billing_cycle' => 'semiannual',
                'description' => 'Economize R$50! Pague via PIX ou cartão. Reembolso proporcional após 1 mês.',
                'features' => [
                    'Tudo do plano mensal',
                    'Economia de R$49,40',
                    '1 mês grátis incluído',
                    'Prioridade em eventos',
                    'Brindes sazonais',
                ],
                'payment_methods' => ['pix', 'card'],
                'badge' => 'Mais Popular',
                'badge_color' => '#3b82f6',
                'is_featured' => true,
                'is_recurring' => false,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Plano Anual (PIX ou Cartão)
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'anual'],
            [
                'name' => 'Premium Anual',
                'price' => 500.00,
                'original_price' => 598.80, // 12 x 49.90
                'duration_days' => 365,
                'trial_days' => 0, // Sem trial
                'min_days_for_full_refund' => 90, // 3 meses
                'early_cancel_penalty_months' => 3, // Multa de 3 meses
                'billing_cycle' => 'annual',
                'description' => 'Melhor custo-benefício! Economize ~R$100. Reembolso proporcional após 3 meses.',
                'features' => [
                    'Tudo do plano semestral',
                    'Economia de R$98,80',
                    '~2 meses grátis',
                    'Badge exclusivo VIP',
                    'Acesso antecipado a novidades',
                    'Brindes de aniversário',
                ],
                'payment_methods' => ['pix', 'card'],
                'badge' => 'Melhor Valor',
                'badge_color' => '#f59e0b',
                'is_featured' => false,
                'is_recurring' => false,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $this->command->info('✅ Planos de assinatura atualizados com sucesso!');
        $this->command->newLine();
        $this->command->table(
            ['Plano', 'Preço', 'Trial', 'Pagamento', 'Multa Cancel.'],
            SubscriptionPlan::active()->ordered()->get()->map(fn($p) => [
                $p->name,
                $p->formatted_price,
                $p->trial_days > 0 ? $p->trial_days . ' dias' : '-',
                implode('/', $p->payment_methods ?? []),
                $p->early_cancel_penalty_months . ' mês(es)',
            ])
        );
    }
}
