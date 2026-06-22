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
        Schema::table('fantasy_team_competitors', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_team_competitors', 'current_points')) {
                $table->integer('current_points')->default(0)->after('multiplier');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fantasy_team_competitors', function (Blueprint $table) {
            $table->dropColumn('current_points');
        });
    }
};
