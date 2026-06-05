<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY plano VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE subscriptions MODIFY status VARCHAR(20) NOT NULL DEFAULT 'ativa'");

        DB::statement("
            UPDATE subscriptions s
            LEFT JOIN subscription_plans sp ON sp.id = s.subscription_plan_id
            SET s.plano = COALESCE(NULLIF(s.plano, ''), sp.slug, 'mensal')
            WHERE s.plano = '' OR s.plano IS NULL
        ");

        DB::statement("
            UPDATE subscriptions
            SET status = CASE
                WHEN is_trial = 1 THEN 'trial'
                WHEN cancelled_at IS NOT NULL THEN 'cancelada'
                WHEN last_payment_at IS NULL AND transaction_id IS NOT NULL THEN 'pendente'
                WHEN data_fim >= CURDATE() THEN 'ativa'
                ELSE 'expirada'
            END
            WHERE status = '' OR status IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: os campos foram convertidos para VARCHAR para evitar novos erros de enum.
    }
};
