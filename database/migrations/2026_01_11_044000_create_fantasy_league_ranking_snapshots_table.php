<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fantasy_league_ranking_snapshots')) {
            return;
        }

        Schema::create('fantasy_league_ranking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fantasy_league_id');
            $table->string('type', 20); // top30 | full
            $table->json('payload');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['fantasy_league_id', 'type'], 'flrs_unique');
            $table->index(['fantasy_league_id', 'generated_at'], 'flrs_league_generated_idx');

            $table->foreign('fantasy_league_id', 'flrs_league_fk')->references('id')->on('fantasy_leagues')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Forward-only
    }
};
