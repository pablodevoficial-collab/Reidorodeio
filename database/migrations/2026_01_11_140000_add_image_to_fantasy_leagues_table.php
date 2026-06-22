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

        if (Schema::hasColumn('fantasy_leagues', 'image')) {
            return;
        }

        Schema::table('fantasy_leagues', function (Blueprint $table) {
            $table->string('image', 255)->nullable()->after('category');
        });
    }

    public function down(): void
    {
        // Forward-only migration by project convention.
    }
};
