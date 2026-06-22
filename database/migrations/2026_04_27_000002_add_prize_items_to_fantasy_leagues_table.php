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
            if (!Schema::hasColumn('fantasy_leagues', 'prize_items')) {
                $table->json('prize_items')->nullable()->after('prize_description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (Schema::hasColumn('fantasy_leagues', 'prize_items')) {
                $table->dropColumn('prize_items');
            }
        });
    }
};
