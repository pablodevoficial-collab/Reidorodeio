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
        Schema::create('user_x1_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('modalidade_id')->nullable()->constrained()->onDelete('set null');
            
            // Contadores de batalhas
            $table->unsignedInteger('total_x1s')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->unsignedInteger('draws')->default(0);
            $table->decimal('win_rate', 5, 2)->default(0);
            
            // Financeiro
            $table->decimal('total_prize_won', 12, 2)->default(0);
            $table->decimal('total_invested', 12, 2)->default(0);
            $table->decimal('profit', 12, 2)->default(0);
            
            // Sequências
            $table->integer('current_streak')->default(0); // + vitórias, - derrotas
            $table->unsignedInteger('best_win_streak')->default(0);
            $table->unsignedInteger('worst_loss_streak')->default(0);
            
            // Rating ELO
            $table->unsignedInteger('rating')->default(1000);
            $table->unsignedInteger('peak_rating')->default(1000);
            
            // Metadata
            $table->timestamp('last_x1_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->unique(['user_id', 'modalidade_id']);
            $table->index(['rating', 'total_x1s']);
            $table->index(['wins', 'win_rate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_x1_stats');
    }
};
