<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_leagues', 'reward_mode')) {
                // computed: prize comes from entries * price - house_cut
                // manual_prize: admin-defined prize pool (used for premium)
                // points: no prize, accumulates points only
                $table->string('reward_mode', 20)->default('computed')->after('is_premium');
                $table->index('reward_mode');
            }

            if (!Schema::hasColumn('fantasy_leagues', 'manual_prize_pool')) {
                $table->decimal('manual_prize_pool', 10, 2)->nullable()->after('reward_mode');
            }
        });
    }

    public function down(): void
    {
        // Forward-only migration by project convention.
    }
};
