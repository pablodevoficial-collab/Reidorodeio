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

        $columns = [
            // Armadas (positivas)
            'count_pescou_uma_aspa',
            'count_limpou_top',
            'count_limpou_top_mao',

            // Armadas (negativas)
            'count_boi_tirou',
            'count_boi_pulou',
            'count_queimou_raia',
            'count_caiu_do_cavalo',
            'count_saiu_enrolado',
        ];

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
            foreach ($columns as $col) {
                if (!Schema::hasColumn($tableName, $col)) {
                    $table->unsignedInteger($col)->default(0);
                }
            }
        });
    }

    private function dropColumnsIfPresent(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        $columns = [
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
        $this->addColumnsIfMissing('competitor_stats');
        $this->addColumnsIfMissing('competitor_stats_global');
    }

    public function down(): void
    {
        $this->dropColumnsIfPresent('competitor_stats');
        $this->dropColumnsIfPresent('competitor_stats_global');
    }
};
