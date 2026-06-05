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
        // Adicionar campo para admin escolher que tipo de stats usar no Fantasy
        if (Schema::hasTable('fantasy_leagues')) {
            Schema::table('fantasy_leagues', function (Blueprint $table) {
                if (!Schema::hasColumn('fantasy_leagues', 'tipo_stats')) {
                    $table->enum('tipo_stats', ['final', 'classificatoria', 'ambos'])
                        ->default('final')
                        ->after('modalidade_id')
                        ->comment('Tipo de estatísticas usadas: apenas final, classificatória, ou ambos');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fantasy_leagues')) {
            Schema::table('fantasy_leagues', function (Blueprint $table) {
                if (Schema::hasColumn('fantasy_leagues', 'tipo_stats')) {
                    $table->dropColumn('tipo_stats');
                }
            });
        }
    }
};
