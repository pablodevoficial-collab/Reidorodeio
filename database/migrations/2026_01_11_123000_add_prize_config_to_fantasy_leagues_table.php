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
            if (!Schema::hasColumn('fantasy_leagues', 'house_cut_percent')) {
                // Percentual que fica com a casa (lucro/fee). Regra: 20% a 50%.
                $table->decimal('house_cut_percent', 5, 2)->default(30.00)->after('price');
                $table->index('house_cut_percent');
            }

            if (!Schema::hasColumn('fantasy_leagues', 'closes_at')) {
                // Momento em que a liga fecha para entrada.
                $table->timestamp('closes_at')->nullable()->after('divisao');
                $table->index('closes_at');
            }
        });
    }

    public function down(): void
    {
        // Forward-only migration by project convention.
    }
};
