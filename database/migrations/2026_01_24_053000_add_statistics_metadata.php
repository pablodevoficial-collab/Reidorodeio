<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1️⃣ Adicionar campo tipo_fase em competitor_stats
        if (Schema::hasTable('competitor_stats')) {
            Schema::table('competitor_stats', function (Blueprint $table) {
                if (!Schema::hasColumn('competitor_stats', 'tipo_fase')) {
                    $table->enum('tipo_fase', ['classificatoria', 'final'])
                        ->default('final')
                        ->after('divisao')
                        ->comment('Fase da competição: classificatória ou final');
                }
                
                if (!Schema::hasColumn('competitor_stats', 'is_finalized')) {
                    $table->boolean('is_finalized')
                        ->default(false)
                        ->after('tipo_fase')
                        ->comment('Indica se as estatísticas desta fase foram finalizadas');
                }
                
                if (!Schema::hasColumn('competitor_stats', 'last_updated_at')) {
                    $table->timestamp('last_updated_at')
                        ->nullable()
                        ->after('updated_at')
                        ->comment('Última atualização de pontuação');
                }
            });

            // Atualizar registros existentes: se tem divisão, é final
            DB::statement("
                UPDATE competitor_stats 
                SET tipo_fase = 'final' 
                WHERE divisao IS NOT NULL AND divisao != ''
            ");
        }

        // 2️⃣ Adicionar soft deletes em competitor_scoring_logs
        if (Schema::hasTable('competitor_scoring_logs')) {
            Schema::table('competitor_scoring_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('competitor_scoring_logs', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('competitor_stats')) {
            Schema::table('competitor_stats', function (Blueprint $table) {
                if (Schema::hasColumn('competitor_stats', 'tipo_fase')) {
                    $table->dropColumn('tipo_fase');
                }
                if (Schema::hasColumn('competitor_stats', 'is_finalized')) {
                    $table->dropColumn('is_finalized');
                }
                if (Schema::hasColumn('competitor_stats', 'last_updated_at')) {
                    $table->dropColumn('last_updated_at');
                }
            });
        }

        if (Schema::hasTable('competitor_scoring_logs')) {
            Schema::table('competitor_scoring_logs', function (Blueprint $table) {
                if (Schema::hasColumn('competitor_scoring_logs', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
