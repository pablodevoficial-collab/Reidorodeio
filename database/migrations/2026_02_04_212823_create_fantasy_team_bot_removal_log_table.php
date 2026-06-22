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
        Schema::create('fantasy_team_bot_removal_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fantasy_league_id');
            $table->unsignedBigInteger('fantasy_team_id');
            $table->unsignedBigInteger('bot_user_id')->nullable();
            $table->timestamp('removed_at');
            $table->string('reason', 255)->nullable();
            $table->timestamps();
            
            // Índices para queries rápidas
            $table->index('fantasy_league_id');
            $table->index('removed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fantasy_team_bot_removal_log');
    }
};
