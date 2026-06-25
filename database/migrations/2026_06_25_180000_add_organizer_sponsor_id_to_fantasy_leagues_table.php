<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (!Schema::hasColumn('fantasy_leagues', 'organizer_sponsor_id')) {
                $table->foreignId('organizer_sponsor_id')->nullable()->after('modalidade_id')->constrained('sponsors')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fantasy_leagues', function (Blueprint $table) {
            if (Schema::hasColumn('fantasy_leagues', 'organizer_sponsor_id')) {
                $table->dropConstrainedForeignId('organizer_sponsor_id');
            }
        });
    }
};
