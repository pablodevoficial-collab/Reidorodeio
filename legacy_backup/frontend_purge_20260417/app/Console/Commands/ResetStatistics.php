<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:statistics {--force : Forçar sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zera todas as estatísticas, salas X1 e ligas Fantasy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('⚠️  ATENÇÃO: Esta ação irá APAGAR PERMANENTEMENTE:');
        $this->line('   - Todas as salas X1 (bots e reais)');
        $this->line('   - Todas as ligas Fantasy (bots e reais)');
        $this->line('   - Todos os times Fantasy');
        $this->line('   - Todos os pagamentos relacionados');
        $this->line('   - Todos os resultados e participantes');
        $this->line('   - Todas as comissões de afiliados');
        $this->line('   - Logs de remoção de bots');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Tem certeza que deseja continuar?', false)) {
                $this->info('Operação cancelada.');
                return 0;
            }

            if (!$this->confirm('Confirme novamente: APAGAR TODOS OS DADOS?', false)) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        $this->info('Iniciando reset...');
        $this->newLine();

        try {
            // Desabilitar verificação de foreign keys temporariamente
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // === X1 ROOMS ===
            $this->info('🗑️  Limpando salas X1...');
            
            if (Schema::hasTable('x1_participants')) {
                $count = DB::table('x1_participants')->count();
                DB::table('x1_participants')->truncate();
                $this->line("   ✓ {$count} participantes removidos");
            }

            if (Schema::hasTable('x1_payments')) {
                $count = DB::table('x1_payments')->count();
                DB::table('x1_payments')->truncate();
                $this->line("   ✓ {$count} pagamentos X1 removidos");
            }

            if (Schema::hasTable('x1_results')) {
                $count = DB::table('x1_results')->count();
                DB::table('x1_results')->truncate();
                $this->line("   ✓ {$count} resultados X1 removidos");
            }

            if (Schema::hasTable('x1_rooms')) {
                $count = DB::table('x1_rooms')->count();
                DB::table('x1_rooms')->truncate();
                $this->line("   ✓ {$count} salas X1 removidas");
            }

            // === FANTASY LEAGUES ===
            $this->newLine();
            $this->info('🗑️  Limpando ligas Fantasy...');

            if (Schema::hasTable('fantasy_team_competitors')) {
                $count = DB::table('fantasy_team_competitors')->count();
                DB::table('fantasy_team_competitors')->truncate();
                $this->line("   ✓ {$count} competidores de times removidos");
            }

            if (Schema::hasTable('fantasy_team_bot_removal_log')) {
                $count = DB::table('fantasy_team_bot_removal_log')->count();
                DB::table('fantasy_team_bot_removal_log')->truncate();
                $this->line("   ✓ {$count} logs de remoção de bots removidos");
            }

            if (Schema::hasTable('fantasy_scores')) {
                $count = DB::table('fantasy_scores')->count();
                DB::table('fantasy_scores')->truncate();
                $this->line("   ✓ {$count} pontuações Fantasy removidas");
            }

            if (Schema::hasTable('fantasy_teams')) {
                $count = DB::table('fantasy_teams')->count();
                DB::table('fantasy_teams')->truncate();
                $this->line("   ✓ {$count} times Fantasy removidos");
            }

            if (Schema::hasTable('fantasy_payments')) {
                $count = DB::table('fantasy_payments')->count();
                DB::table('fantasy_payments')->truncate();
                $this->line("   ✓ {$count} pagamentos Fantasy removidos");
            }

            if (Schema::hasTable('fantasy_league_competitor_stats')) {
                $count = DB::table('fantasy_league_competitor_stats')->count();
                DB::table('fantasy_league_competitor_stats')->truncate();
                $this->line("   ✓ {$count} estatísticas de competidores removidas");
            }

            if (Schema::hasTable('fantasy_league_ranking_snapshots')) {
                $count = DB::table('fantasy_league_ranking_snapshots')->count();
                DB::table('fantasy_league_ranking_snapshots')->truncate();
                $this->line("   ✓ {$count} snapshots de ranking removidos");
            }

            if (Schema::hasTable('fantasy_leagues')) {
                $count = DB::table('fantasy_leagues')->count();
                DB::table('fantasy_leagues')->truncate();
                $this->line("   ✓ {$count} ligas Fantasy removidas");
            }

            // === AFFILIATE COMMISSIONS ===
            $this->newLine();
            $this->info('🗑️  Limpando comissões de afiliados...');

            if (Schema::hasTable('affiliate_commissions')) {
                $count = DB::table('affiliate_commissions')->count();
                DB::table('affiliate_commissions')->truncate();
                $this->line("   ✓ {$count} comissões removidas");
            }

            // === BOT USERS ===
            $this->newLine();
            $this->info('🗑️  Limpando bots...');

            if (Schema::hasTable('bot_users')) {
                $count = DB::table('bot_users')->count();
                DB::table('bot_users')->truncate();
                $this->line("   ✓ {$count} bot users removidos");
            }

            // Reabilitar verificação de foreign keys
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->newLine();
            $this->info('✅ Reset concluído com sucesso!');
            $this->info('💡 O sistema está limpo e pronto para novos dados.');

            return 0;

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('❌ Erro ao resetar: ' . $e->getMessage());
            return 1;
        }
    }
}
