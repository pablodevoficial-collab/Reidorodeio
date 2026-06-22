<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetCompetitorStats extends Command
{
    protected $signature = 'competitors:reset-stats
                            {--logs : Também apaga os scoring logs}
                            {--force : Pular confirmação}';

    protected $description = 'Zera TODAS as estatísticas de competidores (global, por evento e opcionalmente logs)';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('⚠️  Isso vai ZERAR todas as estatísticas de TODOS os competidores. Continuar?')) {
            $this->info('Cancelado.');
            return 0;
        }

        $this->info('🔄 Zerando estatísticas...');

        // 1. Tabela global (competitor_stats_global)
        $global = DB::table('competitor_stats_global')->count();
        DB::table('competitor_stats_global')->update($this->zeroColumns());
        $this->line("  ✅ competitor_stats_global: {$global} registros zerados");

        // 2. Tabela por evento (competitor_stats)
        $perEvent = DB::table('competitor_stats')->count();
        DB::table('competitor_stats')->delete();
        $this->line("  ✅ competitor_stats: {$perEvent} registros deletados");

        // 3. Scoring logs (opcional)
        if ($this->option('logs')) {
            $logs = DB::table('competitor_scoring_logs')->count();
            DB::table('competitor_scoring_logs')->delete();
            $this->line("  ✅ competitor_scoring_logs: {$logs} registros deletados");
        }

        $this->newLine();
        $this->info('🎯 Todas as estatísticas foram zeradas com sucesso!');
        return 0;
    }

    private function zeroColumns(): array
    {
        return [
            'vitorias' => 0,
            'derrotas' => 0,
            'empates' => 0,
            'aproveitamento' => 0,
            'pontuacao_media' => 0,
            'pontuacao_total' => 0,
            'last_points' => 0,
            'count_boa' => 0,
            'count_negativas_total' => 0,
            'count_errou_pescoco' => 0,
            'count_errou_pata' => 0,
            'count_errou_top' => 0,
            'count_dobrada' => 0,
            'count_cabresteou' => 0,
            'count_duas_voltas' => 0,
            'count_limpou_garupa' => 0,
            'count_garupa_neg' => 0,
            'count_cola' => 0,
            'count_cola_neg' => 0,
            'count_cupim' => 0,
            'count_top' => 0,
            'count_pescou' => 0,
            'count_uma_aspa' => 0,
            'count_por_cima' => 0,
            'count_limpou_cupim_longe' => 0,
            'count_pescou_uma_aspa' => 0,
            'count_limpou_top' => 0,
            'count_limpou_top_mao' => 0,
            'count_boi_tirou' => 0,
            'count_boi_pulou' => 0,
            'count_queimou_raia' => 0,
            'count_caiu_do_cavalo' => 0,
            'count_saiu_enrolado' => 0,
        ];
    }
}
