<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Relação com plano
            if (!Schema::hasColumn('subscriptions', 'subscription_plan_id')) {
                $table->foreignId('subscription_plan_id')->nullable()->after('user_id')->constrained('subscription_plans')->nullOnDelete();
            }
            
            // Trial (período grátis)
            if (!Schema::hasColumn('subscriptions', 'is_trial')) {
                $table->boolean('is_trial')->default(false)->after('status');
            }
            if (!Schema::hasColumn('subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('is_trial');
            }
            
            // Auto-renovação
            if (!Schema::hasColumn('subscriptions', 'auto_renew')) {
                $table->boolean('auto_renew')->default(true)->after('data_fim');
            }
            
            // Cancelamento
            if (!Schema::hasColumn('subscriptions', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('auto_renew');
            }
            if (!Schema::hasColumn('subscriptions', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            }
            
            // Próxima cobrança
            if (!Schema::hasColumn('subscriptions', 'next_billing_date')) {
                $table->date('next_billing_date')->nullable()->after('data_fim');
            }
            
            // Histórico de pagamentos
            if (!Schema::hasColumn('subscriptions', 'last_payment_at')) {
                $table->timestamp('last_payment_at')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('subscriptions', 'payment_attempts')) {
                $table->integer('payment_attempts')->default(0)->after('last_payment_at');
            }
            
            // Metadados
            if (!Schema::hasColumn('subscriptions', 'metadata')) {
                $table->json('metadata')->nullable()->after('payment_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $columns = [
                'subscription_plan_id', 'is_trial', 'trial_ends_at', 
                'auto_renew', 'cancelled_at', 'cancellation_reason',
                'next_billing_date', 'last_payment_at', 'payment_attempts', 'metadata'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    if ($column === 'subscription_plan_id') {
                        $table->dropForeign(['subscription_plan_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
