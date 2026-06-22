<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('competitor_modalidade')) {
            return;
        }

        Schema::table('competitor_modalidade', function (Blueprint $table) {
            if (!Schema::hasColumn('competitor_modalidade', 'divisao')) {
                $table->string('divisao', 60)->default('')->after('modalidade_id');
            }
        });

        // Index for filtering competitors by modalidade + divisao.
        try {
            $exists = DB::select("SHOW INDEX FROM competitor_modalidade WHERE Key_name = 'idx_modalidade_divisao'");
            if (empty($exists)) {
                Schema::table('competitor_modalidade', function (Blueprint $table) {
                    $table->index(['modalidade_id', 'divisao'], 'idx_modalidade_divisao');
                });
            }
        } catch (Throwable $e) {
            // Best-effort: don't block migrations on index checks.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('competitor_modalidade')) {
            return;
        }

        try {
            $exists = DB::select("SHOW INDEX FROM competitor_modalidade WHERE Key_name = 'idx_modalidade_divisao'");
            if (!empty($exists)) {
                Schema::table('competitor_modalidade', function (Blueprint $table) {
                    $table->dropIndex('idx_modalidade_divisao');
                });
            }
        } catch (Throwable $e) {
        }

        Schema::table('competitor_modalidade', function (Blueprint $table) {
            if (Schema::hasColumn('competitor_modalidade', 'divisao')) {
                $table->dropColumn('divisao');
            }
        });
    }
};
