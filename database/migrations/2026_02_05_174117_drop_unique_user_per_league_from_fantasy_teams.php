<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove unique constraint to allow multiple teams per user per league.
     */
    public function up(): void
    {
        Schema::table('fantasy_teams', function (Blueprint $table) {
            $table->dropUnique('fantasy_teams_unique_user_per_league');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fantasy_teams', function (Blueprint $table) {
            $table->unique(['fantasy_league_id', 'user_id'], 'fantasy_teams_unique_user_per_league');
        });
    }
};
