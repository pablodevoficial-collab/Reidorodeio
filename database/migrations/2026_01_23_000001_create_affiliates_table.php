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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('referral_code', 20)->unique();
            $table->enum('tier', ['bronze', 'silver', 'gold', 'diamond'])->default('bronze');
            
            // Estatísticas
            $table->integer('total_referrals')->default(0)->comment('Total de indicações');
            $table->integer('active_referrals')->default(0)->comment('Indicações ativas');
            
            // Financeiro (SEM SALDO DISPONÍVEL - apenas acumulado)
            $table->decimal('total_earned', 12, 2)->default(0)->comment('Total ganho historicamente');
            $table->decimal('pending_commission', 12, 2)->default(0)->comment('Comissão não resgatada (aguardando pagamento admin)');
            $table->decimal('paid_total', 12, 2)->default(0)->comment('Total já pago pelo admin');
            
            // Status
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->text('suspended_reason')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('referral_code', 'idx_referral_code');
            $table->index('tier', 'idx_tier');
            $table->index('status', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
