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
        Schema::create('competitor_rankings', function (Blueprint $table) {
            $table->id();
            
            // Identificação do competidor
            $table->unsignedBigInteger('competitor_id');
            $table->unsignedBigInteger('competitor_group_id')->nullable(); // Para duplas/trios
            
            // Contexto do ranking
            $table->enum('ranking_type', ['event', 'monthly', 'yearly', 'overall']);
            $table->unsignedBigInteger('rodeio_id')->nullable(); // Para ranking por evento
            $table->unsignedBigInteger('modalidade_id')->nullable();
            $table->string('divisao', 60)->nullable();
            $table->unsignedSmallInteger('year')->nullable(); // Para rankings mensais/anuais
            $table->unsignedTinyInteger('month')->nullable(); // Para ranking mensal
            
            // Posição e pontuação
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('previous_position')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('points_change')->default(0); // Variação desde última atualização
            
            // Estatísticas agregadas
            $table->unsignedInteger('total_actions')->default(0);
            $table->unsignedInteger('positive_actions')->default(0);
            $table->unsignedInteger('negative_actions')->default(0);
            $table->decimal('efficiency_rate', 5, 2)->default(0); // % de ações positivas
            
            // Breakdown por categoria (JSON para flexibilidade)
            $table->json('action_breakdown')->nullable();
            // Exemplo: {"boa": 10, "top": 2, "errou_pescoco": 3}
            
            // Metadados
            $table->timestamp('calculated_at')->nullable();
            $table->unsignedInteger('events_count')->default(0); // Qtd eventos participados
            
            $table->timestamps();
            
            // Índices
            $table->index('competitor_id');
            $table->index('competitor_group_id');
            $table->index(['ranking_type', 'rodeio_id', 'modalidade_id']);
            $table->index(['ranking_type', 'year', 'month']);
            $table->index(['ranking_type', 'position']);
            
            // Unique constraint para evitar duplicatas
            $table->unique([
                'competitor_id', 
                'ranking_type', 
                'rodeio_id', 
                'modalidade_id',
                'divisao',
                'year',
                'month'
            ], 'unique_ranking_entry');
            
            // Foreign keys
            $table->foreign('competitor_id')->references('id')->on('competitors')->onDelete('cascade');
            $table->foreign('rodeio_id')->references('id')->on('rodeios')->onDelete('cascade');
            $table->foreign('modalidade_id')->references('id')->on('modalidades')->onDelete('cascade');
        });

        // Tabela para snapshots de ranking (histórico)
        Schema::create('ranking_history', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('competitor_id');
            $table->enum('ranking_type', ['event', 'monthly', 'yearly', 'overall']);
            $table->unsignedBigInteger('rodeio_id')->nullable();
            $table->unsignedBigInteger('modalidade_id')->nullable();
            
            $table->unsignedInteger('position');
            $table->integer('total_points');
            $table->json('action_breakdown')->nullable();
            
            $table->timestamp('snapshot_at');
            $table->timestamps();
            
            $table->index(['competitor_id', 'ranking_type']);
            $table->index('snapshot_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_history');
        Schema::dropIfExists('competitor_rankings');
    }
};
