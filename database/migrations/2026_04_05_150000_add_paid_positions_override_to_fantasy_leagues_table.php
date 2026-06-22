<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fantasy_leagues') || Schema::hasColumn('fantasy_leagues', 'paid_positions_override')) {
            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            $table->unsignedInteger('paid_positions_override')
                ->nullable()
                ->after('max_users');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('fantasy_leagues') || !Schema::hasColumn('fantasy_leagues', 'paid_positions_override')) {
            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            $table->dropColumn('paid_positions_override');
        });
    }
};
