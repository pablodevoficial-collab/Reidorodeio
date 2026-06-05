<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columnsToDrop = [];

        if (Schema::hasColumn('modalidades', 'end')) {
            $columnsToDrop[] = 'end';
        }
        if (Schema::hasColumn('modalidades', 'regras_eliminacao')) {
            $columnsToDrop[] = 'regras_eliminacao';
        }
        if (Schema::hasColumn('modalidades', 'regras_classificacao')) {
            $columnsToDrop[] = 'regras_classificacao';
        }
        if (Schema::hasColumn('modalidades', 'ordem')) {
            $columnsToDrop[] = 'ordem';
        }

        if (empty($columnsToDrop)) {
            return;
        }

        Schema::table('modalidades', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
    }

    public function down(): void
    {
        Schema::table('modalidades', function (Blueprint $table) {
            if (!Schema::hasColumn('modalidades', 'end')) {
                $table->dateTime('end')->nullable();
            }
            if (!Schema::hasColumn('modalidades', 'regras_eliminacao')) {
                $table->text('regras_eliminacao')->nullable();
            }
            if (!Schema::hasColumn('modalidades', 'regras_classificacao')) {
                $table->text('regras_classificacao')->nullable();
            }
            if (!Schema::hasColumn('modalidades', 'ordem')) {
                $table->integer('ordem')->nullable();
            }
        });
    }
};
