<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('modalidades')) {
            return;
        }

        Schema::table('modalidades', function (Blueprint $table) {
            if (!Schema::hasColumn('modalidades', 'tipo_participacao')) {
                $table->string('tipo_participacao', 20)->default('individual');
            }
            if (!Schema::hasColumn('modalidades', 'tamanho_equipe')) {
                $table->unsignedTinyInteger('tamanho_equipe')->default(1);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('modalidades')) {
            return;
        }

        Schema::table('modalidades', function (Blueprint $table) {
            if (Schema::hasColumn('modalidades', 'tamanho_equipe')) {
                $table->dropColumn('tamanho_equipe');
            }
            if (Schema::hasColumn('modalidades', 'tipo_participacao')) {
                $table->dropColumn('tipo_participacao');
            }
        });
    }
};
