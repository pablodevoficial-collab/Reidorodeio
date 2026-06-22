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
            if (!Schema::hasColumn('fantasy_leagues', 'prize_type')) {
                $table->string('prize_type', 20)->default('money')->after('manual_prize_pool');
            }

            if (!Schema::hasColumn('fantasy_leagues', 'prize_description')) {
                $table->text('prize_description')->nullable()->after('prize_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (Schema::hasColumn('fantasy_leagues', 'prize_description')) {
                $table->dropColumn('prize_description');
            }

            if (Schema::hasColumn('fantasy_leagues', 'prize_type')) {
                $table->dropColumn('prize_type');
            }
        });
    }
};
