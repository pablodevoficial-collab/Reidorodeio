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
            // Método de pagamento: 'pix' ou 'card'
            $table->string('payment_method', 20)->default('pix')->after('gateway_pagamento');
            
            // ID da assinatura no Mercado Pago (para cartão recorrente)
            $table->string('mp_subscription_id')->nullable()->after('transaction_id');
            
            // ID do preapproval plan no Mercado Pago
            $table->string('mp_preapproval_plan_id')->nullable()->after('mp_subscription_id');
            
            // Últimos 4 dígitos do cartão
            $table->string('card_last_four', 4)->nullable()->after('mp_preapproval_plan_id');
            
            // Bandeira do cartão (visa, mastercard, etc)
            $table->string('card_brand', 20)->nullable()->after('card_last_four');
            
            // Valor mensal para cálculo de reembolso
            $table->decimal('monthly_value', 10, 2)->default(49.90)->after('valor');
            
            // Total pago até agora (para assinaturas recorrentes)
            $table->decimal('total_paid', 10, 2)->default(0)->after('monthly_value');
            
            // Valor do reembolso em caso de cancelamento
            $table->decimal('refund_amount', 10, 2)->nullable()->after('total_paid');
            
            // Status do reembolso
            $table->string('refund_status', 20)->nullable()->after('refund_amount');
            
            // ID do reembolso no gateway
            $table->string('refund_transaction_id')->nullable()->after('refund_status');
            
            // Índices
            $table->index('mp_subscription_id');
            $table->index('payment_method');
        });
        
        // Atualizar planos existentes
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Métodos de pagamento aceitos: json array ['pix', 'card']
            $table->json('payment_methods')->nullable()->after('features');
            
            // É assinatura recorrente (cartão mensal)?
            $table->boolean('is_recurring')->default(false)->after('is_featured');
            
            // Dias mínimos antes de reembolso sem multa
            $table->integer('min_days_for_full_refund')->default(90)->after('trial_days');
            
            // Multa em meses para cancelamento antecipado
            $table->integer('early_cancel_penalty_months')->default(2)->after('min_days_for_full_refund');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['mp_subscription_id']);
            $table->dropIndex(['payment_method']);
            
            $table->dropColumn([
                'payment_method',
                'mp_subscription_id',
                'mp_preapproval_plan_id',
                'card_last_four',
                'card_brand',
                'monthly_value',
                'total_paid',
                'refund_amount',
                'refund_status',
                'refund_transaction_id',
            ]);
        });
        
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'payment_methods',
                'is_recurring',
                'min_days_for_full_refund',
                'early_cancel_penalty_months',
            ]);
        });
    }
};
