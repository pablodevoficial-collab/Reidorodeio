<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add salary cap configuration to fantasy_leagues
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_leagues', 'salary_cap')) {
                $table->unsignedInteger('salary_cap')->default(1000)->after('max_users');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'base_price')) {
                $table->unsignedInteger('base_price')->default(150)->after('salary_cap');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'price_per_pick')) {
                $table->unsignedInteger('price_per_pick')->default(10)->after('base_price');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'max_price')) {
                $table->unsignedInteger('max_price')->default(300)->after('price_per_pick');
            }
        });

        // Create table to track pick counts per competitor per league
        if (!Schema::hasTable('fantasy_league_competitor_stats')) {
            Schema::create('fantasy_league_competitor_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fantasy_league_id');
                $table->unsignedBigInteger('competitor_id');
                $table->unsignedInteger('pick_count')->default(0);
                $table->timestamps();

                $table->unique(['fantasy_league_id', 'competitor_id'], 'flcs_unique');
                $table->index('competitor_id', 'flcs_competitor_idx');

                $table->foreign('fantasy_league_id', 'flcs_league_fk')
                    ->references('id')->on('fantasy_leagues')->cascadeOnDelete();
                $table->foreign('competitor_id', 'flcs_competitor_fk')
                    ->references('id')->on('competitors')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Forward-only
    }
};
