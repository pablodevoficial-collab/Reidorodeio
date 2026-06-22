<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the project already has a legacy `competitor_stats` table (global stats per competitor),
        // we preserve it by renaming to `competitor_stats_global`.
        if (Schema::hasTable('competitor_stats') && !Schema::hasTable('competitor_stats_global')) {
            Schema::rename('competitor_stats', 'competitor_stats_global');
        }

        // New per-event/per-modalidade competitor stats.
        if (!Schema::hasTable('competitor_stats')) {
            Schema::create('competitor_stats', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('competitor_id');
                $table->unsignedBigInteger('rodeio_id');
                $table->unsignedBigInteger('modalidade_id');

                $table->integer('pontuacao_total')->default(0);
                $table->integer('last_points')->default(0);

                // Core counters used by the live scoring UI.
                $table->unsignedInteger('count_negativas_total')->default(0);
                $table->unsignedInteger('count_boa')->default(0);
                $table->unsignedInteger('count_errou_pescoco')->default(0);
                $table->unsignedInteger('count_errou_pata')->default(0);
                $table->unsignedInteger('count_errou_top')->default(0);
                $table->unsignedInteger('count_dobrada')->default(0);
                $table->unsignedInteger('count_cabresteou')->default(0);
                $table->unsignedInteger('count_duas_voltas')->default(0);

                $table->unsignedInteger('count_limpou_garupa')->default(0);
                $table->unsignedInteger('count_cola')->default(0);
                $table->unsignedInteger('count_cola_neg')->default(0);
                $table->unsignedInteger('count_cupim')->default(0);
                $table->unsignedInteger('count_top')->default(0);
                $table->unsignedInteger('count_limpou_cupim_longe')->default(0);

                $table->unsignedInteger('count_pescou')->default(0);
                $table->unsignedInteger('count_garupa_neg')->default(0);
                $table->unsignedInteger('count_uma_aspa')->default(0);
                $table->unsignedInteger('count_por_cima')->default(0);

                // Custom actions (Pontuação Personalizada)
                $table->unsignedInteger('count_custom')->default(0);
                $table->integer('points_custom_total')->default(0);

                // For forward-compat: store unknown action counters without schema changes.
                $table->json('action_counts')->nullable();

                $table->timestamps();

                $table->unique(['competitor_id', 'rodeio_id', 'modalidade_id'], 'competitor_stats_unique_ctx');

                // IMPORTANT (MySQL): when renaming legacy tables, FK constraint names remain.
                // Use unique constraint names here to avoid collisions with legacy constraints.
                $table->foreign('competitor_id', 'competitor_stats_ctx_competitor_id_fk')->references('id')->on('competitors')->cascadeOnDelete();
                $table->foreign('rodeio_id', 'competitor_stats_ctx_rodeio_id_fk')->references('id')->on('rodeios')->cascadeOnDelete();
                $table->foreign('modalidade_id', 'competitor_stats_ctx_modalidade_id_fk')->references('id')->on('modalidades')->cascadeOnDelete();

                $table->index(['rodeio_id', 'modalidade_id', 'pontuacao_total'], 'competitor_stats_rank_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('competitor_stats')) {
            Schema::drop('competitor_stats');
        }

        // We don't automatically rename `competitor_stats_global` back, to avoid destructive behavior.
    }
};
