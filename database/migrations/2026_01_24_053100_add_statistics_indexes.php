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
        // 1️⃣ Índices para competitor_stats
        if (Schema::hasTable('competitor_stats')) {
            try {
                // Índice composto para lookup por contexto
                $exists = DB::select("SHOW INDEX FROM competitor_stats WHERE Key_name = 'idx_competitor_context'");
                if (empty($exists)) {
                    Schema::table('competitor_stats', function (Blueprint $table) {
                        $table->index(
                            ['competitor_id', 'rodeio_id', 'modalidade_id', 'divisao'], 
                            'idx_competitor_context'
                        );
                    });
                }

                // Índice para filtrar por finalização
                $exists = DB::select("SHOW INDEX FROM competitor_stats WHERE Key_name = 'idx_stats_finalized'");
                if (empty($exists)) {
                    Schema::table('competitor_stats', function (Blueprint $table) {
                        $table->index(['is_finalized', 'tipo_fase'], 'idx_stats_finalized');
                    });
                }

                // Índice para ordenação por pontuação
                $exists = DB::select("SHOW INDEX FROM competitor_stats WHERE Key_name = 'idx_stats_points'");
                if (empty($exists)) {
                    Schema::table('competitor_stats', function (Blueprint $table) {
                        $table->index(['modalidade_id', 'divisao', 'pontuacao_total'], 'idx_stats_points');
                    });
                }
            } catch (\Throwable $e) {
                // Log error but don't block migration
                \Log::warning('Error creating competitor_stats indexes: ' . $e->getMessage());
            }
        }

        // 2️⃣ Índices para competitor_scoring_logs
        if (Schema::hasTable('competitor_scoring_logs')) {
            try {
                // Índice para lookup de logs por competidor e contexto
                $exists = DB::select("SHOW INDEX FROM competitor_scoring_logs WHERE Key_name = 'idx_scoring_lookup'");
                if (empty($exists)) {
                    Schema::table('competitor_scoring_logs', function (Blueprint $table) {
                        $table->index(
                            ['competitor_id', 'rodeio_id', 'modalidade_id', 'scored_at'], 
                            'idx_scoring_lookup'
                        );
                    });
                }

                // Índice para buscar logs por data
                $exists = DB::select("SHOW INDEX FROM competitor_scoring_logs WHERE Key_name = 'idx_scored_at'");
                if (empty($exists)) {
                    Schema::table('competitor_scoring_logs', function (Blueprint $table) {
                        $table->index('scored_at', 'idx_scored_at');
                    });
                }
            } catch (\Throwable $e) {
                \Log::warning('Error creating competitor_scoring_logs indexes: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('competitor_stats')) {
            try {
                Schema::table('competitor_stats', function (Blueprint $table) {
                    $table->dropIndex('idx_competitor_context');
                    $table->dropIndex('idx_stats_finalized');
                    $table->dropIndex('idx_stats_points');
                });
            } catch (\Throwable $e) {
                \Log::warning('Error dropping competitor_stats indexes: ' . $e->getMessage());
            }
        }

        if (Schema::hasTable('competitor_scoring_logs')) {
            try {
                Schema::table('competitor_scoring_logs', function (Blueprint $table) {
                    $table->dropIndex('idx_scoring_lookup');
                    $table->dropIndex('idx_scored_at');
                });
            } catch (\Throwable $e) {
                \Log::warning('Error dropping competitor_scoring_logs indexes: ' . $e->getMessage());
            }
        }
    }
};
