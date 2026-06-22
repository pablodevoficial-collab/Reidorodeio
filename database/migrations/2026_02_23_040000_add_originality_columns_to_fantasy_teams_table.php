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
        if (!Schema::hasTable('fantasy_teams')) {
            return;
        }

        Schema::table('fantasy_teams', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_teams', 'originality_factor')) {
                $table->decimal('originality_factor', 5, 2)->default(1.00)->after('total_points');
            }

            if (!Schema::hasColumn('fantasy_teams', 'similarity_count')) {
                $table->unsignedInteger('similarity_count')->default(0)->after('originality_factor');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('fantasy_teams')) {
            return;
        }

        Schema::table('fantasy_teams', function (Blueprint $table) {
            if (Schema::hasColumn('fantasy_teams', 'similarity_count')) {
                $table->dropColumn('similarity_count');
            }

            if (Schema::hasColumn('fantasy_teams', 'originality_factor')) {
                $table->dropColumn('originality_factor');
            }
        });
    }
};

