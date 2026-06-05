<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fantasy_team_competitors')) {
            // forward-only add missing
            Schema::table('fantasy_team_competitors', function (Blueprint $table) {
                if (!Schema::hasColumn('fantasy_team_competitors', 'fantasy_team_id')) {
                    $table->unsignedBigInteger('fantasy_team_id')->index('ftc_team_idx');
                }
                if (!Schema::hasColumn('fantasy_team_competitors', 'competitor_id')) {
                    $table->unsignedBigInteger('competitor_id')->index('ftc_competitor_idx');
                }
                if (!Schema::hasColumn('fantasy_team_competitors', 'role')) {
                    $table->string('role', 20)->default('titular');
                }
                if (!Schema::hasColumn('fantasy_team_competitors', 'is_captain')) {
                    $table->boolean('is_captain')->default(false);
                }
                if (!Schema::hasColumn('fantasy_team_competitors', 'multiplier')) {
                    $table->decimal('multiplier', 6, 2)->default(1);
                }
                $hasCreatedAt = Schema::hasColumn('fantasy_team_competitors', 'created_at');
                $hasUpdatedAt = Schema::hasColumn('fantasy_team_competitors', 'updated_at');
                if (!$hasCreatedAt && !$hasUpdatedAt) {
                    $table->timestamps();
                } elseif (!$hasCreatedAt) {
                    $table->timestamp('created_at')->nullable();
                } elseif (!$hasUpdatedAt) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
            return;
        }

        Schema::create('fantasy_team_competitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fantasy_team_id');
            $table->unsignedBigInteger('competitor_id');
            $table->string('role', 20)->default('titular');
            $table->boolean('is_captain')->default(false);
            $table->decimal('multiplier', 6, 2)->default(1);
            $table->timestamps();

            $table->unique(['fantasy_team_id', 'competitor_id'], 'ftc_unique');
            $table->index(['competitor_id'], 'ftc_competitor_idx');

            $table->foreign('fantasy_team_id', 'ftc_team_fk')->references('id')->on('fantasy_teams')->cascadeOnDelete();
            $table->foreign('competitor_id', 'ftc_competitor_fk')->references('id')->on('competitors')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Forward-only
    }
};
