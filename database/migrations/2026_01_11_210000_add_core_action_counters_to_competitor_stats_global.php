<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addColumnsIfMissing(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $unsignedCounters = [
            'count_boa',
            'count_negativas_total',
            'count_errou_pescoco',
            'count_errou_pata',
            'count_errou_top',
            'count_dobrada',
            'count_cabresteou',
            'count_duas_voltas',
            'count_limpou_garupa',
            'count_garupa_neg',
            'count_cola',
            'count_cola_neg',
            'count_cupim',
            'count_top',
            'count_pescou',
            'count_uma_aspa',
            'count_por_cima',
            'count_limpou_cupim_longe',
            'count_custom',

            // Armadas adicionais (positivas/negativas)
            'count_pescou_uma_aspa',
            'count_limpou_top',
            'count_limpou_top_mao',
            'count_boi_tirou',
            'count_boi_pulou',
            'count_queimou_raia',
            'count_caiu_do_cavalo',
            'count_saiu_enrolado',
        ];

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $unsignedCounters) {
            if (!Schema::hasColumn($tableName, 'pontuacao_total')) {
                $table->integer('pontuacao_total')->default(0);
            }
            if (!Schema::hasColumn($tableName, 'last_points')) {
                $table->integer('last_points')->default(0);
            }
            if (!Schema::hasColumn($tableName, 'pontuacao_media')) {
                // Stored as decimal for consistency with model casts.
                $table->decimal('pontuacao_media', 10, 2)->default(0);
            }

            foreach ($unsignedCounters as $col) {
                if (!Schema::hasColumn($tableName, $col)) {
                    $table->unsignedInteger($col)->default(0);
                }
            }

            if (!Schema::hasColumn($tableName, 'points_custom_total')) {
                $table->integer('points_custom_total')->default(0);
            }

            // Forward-compat: unknown action counters without schema changes.
            if (!Schema::hasColumn($tableName, 'action_counts')) {
                $table->json('action_counts')->nullable();
            }
        });
    }

    private function dropColumnsIfPresent(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $columns = [
            'pontuacao_total',
            'last_points',
            'pontuacao_media',

            'count_boa',
            'count_negativas_total',
            'count_errou_pescoco',
            'count_errou_pata',
            'count_errou_top',
            'count_dobrada',
            'count_cabresteou',
            'count_duas_voltas',
            'count_limpou_garupa',
            'count_garupa_neg',
            'count_cola',
            'count_cola_neg',
            'count_cupim',
            'count_top',
            'count_pescou',
            'count_uma_aspa',
            'count_por_cima',
            'count_limpou_cupim_longe',

            'count_custom',
            'points_custom_total',
            'action_counts',

            'count_pescou_uma_aspa',
            'count_limpou_top',
            'count_limpou_top_mao',
            'count_boi_tirou',
            'count_boi_pulou',
            'count_queimou_raia',
            'count_caiu_do_cavalo',
            'count_saiu_enrolado',
        ];

        $toDrop = [];
        foreach ($columns as $col) {
            if (Schema::hasColumn($tableName, $col)) {
                $toDrop[] = $col;
            }
        }

        if ($toDrop === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($toDrop) {
            $table->dropColumn($toDrop);
        });
    }

    public function up(): void
    {
        // This controller increments counters on the global stats model.
        // Ensure the underlying table has all required columns.
        $this->addColumnsIfMissing('competitor_stats_global');

        // Keep event stats table aligned when present.
        $this->addColumnsIfMissing('competitor_stats');
    }

    public function down(): void
    {
        // Non-destructive rollback isn't safe here because these columns may become relied upon.
        // But we implement it for completeness.
        $this->dropColumnsIfPresent('competitor_stats');
        $this->dropColumnsIfPresent('competitor_stats_global');
    }
};
