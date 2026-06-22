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
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_leagues', 'total_prize')) {
                // Prêmio total calculado: (entrada * max_users) - lucro_casa
                // Fórmula: total_prize = (price * max_users) * (1 - house_cut_percent/100)
                $table->decimal('total_prize', 12, 2)->default(0)->after('manual_prize_pool');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (Schema::hasColumn('fantasy_leagues', 'total_prize')) {
                $table->dropColumn('total_prize');
            }
        });
    }
};
