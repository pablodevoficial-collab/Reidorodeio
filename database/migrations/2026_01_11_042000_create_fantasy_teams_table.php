<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fantasy_teams')) {
            // Add missing columns forward-only
            Schema::table('fantasy_teams', function (Blueprint $table) {
                if (!Schema::hasColumn('fantasy_teams', 'fantasy_league_id')) {
                    $table->unsignedBigInteger('fantasy_league_id')->nullable()->index('fantasy_teams_league_idx');
                }
                if (!Schema::hasColumn('fantasy_teams', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->index('fantasy_teams_user_idx');
                }
                if (!Schema::hasColumn('fantasy_teams', 'team_name')) {
                    $table->string('team_name', 255)->nullable();
                }
                if (!Schema::hasColumn('fantasy_teams', 'total_points')) {
                    $table->decimal('total_points', 10, 2)->default(0)->index('fantasy_teams_points_idx');
                }
                if (!Schema::hasColumn('fantasy_teams', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index('fantasy_teams_active_idx');
                }
                if (!Schema::hasColumn('fantasy_teams', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            return;
        }

        Schema::create('fantasy_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fantasy_league_id');
            $table->unsignedBigInteger('user_id');
            $table->string('team_name', 255);
            $table->decimal('total_points', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['fantasy_league_id', 'user_id'], 'fantasy_teams_unique_user_per_league');
            $table->index(['fantasy_league_id', 'total_points'], 'fantasy_teams_league_points_idx');

            $table->foreign('fantasy_league_id', 'fantasy_teams_league_fk')->references('id')->on('fantasy_leagues')->cascadeOnDelete();
            $table->foreign('user_id', 'fantasy_teams_user_fk')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Forward-only
    }
};
