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
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('referred_user_id')->constrained('users')->onDelete('cascade');
            
            // Tipo de comissão
            $table->enum('type', ['x1_room', 'fantasy_prize']);
            
            // Referências (nullable pois depende do tipo)
            $table->unsignedBigInteger('x1_room_id')->nullable();
            $table->unsignedBigInteger('fantasy_team_id')->nullable();
            
            // Valores
            $table->decimal('base_amount', 12, 2)->comment('Valor base (taxa X1 ou prêmio fantasy)');
            $table->decimal('commission_percent', 5, 2)->comment('Porcentagem aplicada (20-35% X1 ou 5-10% Fantasy)');
            $table->decimal('commission_amount', 12, 2)->comment('Valor da comissão');
            
            // Status simplificado (sem saque, apenas approved/paid pelo admin)
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->timestamp('eligible_at')->nullable()->comment('Quando pode ser aprovada (7 dias após criação)');
            $table->timestamp('approved_at')->nullable()->comment('Quando foi aprovada automaticamente');
            $table->timestamp('paid_at')->nullable()->comment('Quando admin marcou como pago');
            
            // Metadados (detalhes da sala, prêmio, etc)
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['affiliate_id', 'status'], 'idx_affiliate_status');
            $table->index('type', 'idx_type');
            $table->index(['eligible_at', 'status'], 'idx_eligible_status');
            $table->index('created_at', 'idx_created_at');
            
            // Foreign keys opcionais (podem não existir ainda)
            // Comentado para evitar erro se tabelas não existirem
            // $table->foreign('x1_room_id')->references('id')->on('x1_rooms')->onDelete('set null');
            // $table->foreign('fantasy_team_id')->references('id')->on('fantasy_teams')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
