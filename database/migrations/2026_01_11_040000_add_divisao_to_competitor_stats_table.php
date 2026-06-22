<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('competitor_stats')) {
            return;
        }

        if (!Schema::hasColumn('competitor_stats', 'divisao')) {
            Schema::table('competitor_stats', function (Blueprint $table) {
                $table->string('divisao', 60)->default('')->after('modalidade_id');
                $table->index(['rodeio_id', 'modalidade_id', 'divisao', 'pontuacao_total'], 'competitor_stats_rank_div_idx');
            });
        }

        // Ajustar unique constraint para incluir divisao (evita colisão entre divisões).
        // IMPORTANTE: usamos default '' (não NULL) para manter unicidade também quando não há divisão.
        Schema::table('competitor_stats', function (Blueprint $table) {
            // Se existir, remove o unique antigo.
            // O nome foi criado explicitamente no split migration.
            try {
                $table->dropUnique('competitor_stats_unique_ctx');
            } catch (Throwable $e) {
                // ignore
            }

            // Recria unique com divisao.
            try {
                $table->unique(['competitor_id', 'rodeio_id', 'modalidade_id', 'divisao'], 'competitor_stats_unique_ctx_div');
            } catch (Throwable $e) {
                // ignore
            }
        });
    }

    public function down(): void
    {
        // Forward-only: não removemos coluna nem índices.
    }
};
