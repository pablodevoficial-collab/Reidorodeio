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
        Schema::create('x1_ranking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modalidade_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['daily', 'weekly', 'monthly', 'alltime'])->default('alltime');
            $table->json('payload'); // Array de rankings [{user_id, nome, rating, wins, win_rate, position}]
            $table->unsignedInteger('total_players')->default(0);
            $table->timestamp('generated_at');
            $table->timestamps();
            
            // Índices
            $table->index(['modalidade_id', 'type', 'generated_at']);
            $table->index(['type', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('x1_ranking_snapshots');
    }
};
