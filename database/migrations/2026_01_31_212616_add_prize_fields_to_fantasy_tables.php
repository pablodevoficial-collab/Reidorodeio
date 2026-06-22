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
        // Campos para fantasy_teams (posição e prêmio)
        Schema::table('fantasy_teams', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_teams', 'final_position')) {
                $table->unsignedInteger('final_position')->nullable()->after('total_points');
            }
            if (!Schema::hasColumn('fantasy_teams', 'prize_won')) {
                $table->decimal('prize_won', 10, 2)->nullable()->after('final_position');
            }
            if (!Schema::hasColumn('fantasy_teams', 'prize_paid_at')) {
                $table->timestamp('prize_paid_at')->nullable()->after('prize_won');
            }
        });

        // Campos para fantasy_leagues (distribuição e status)
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_leagues', 'prize_distribution')) {
                // JSON: {"1": 50, "2": 30, "3": 20} = 50% pro 1º, 30% pro 2º, 20% pro 3º
                $table->json('prize_distribution')->nullable()->after('total_prize');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'status')) {
                $table->enum('status', ['draft', 'active', 'closed', 'finalized', 'cancelled'])
                    ->default('active')->after('is_active');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'finalized_at')) {
                $table->timestamp('finalized_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('fantasy_leagues', 'finalized_by')) {
                $table->unsignedBigInteger('finalized_by')->nullable()->after('finalized_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fantasy_teams', function (Blueprint $table) {
            $table->dropColumn(['final_position', 'prize_won', 'prize_paid_at']);
        });

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            $table->dropColumn(['prize_distribution', 'status', 'finalized_at', 'finalized_by']);
        });
    }
};
