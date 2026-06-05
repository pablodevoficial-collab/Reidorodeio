<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;
use App\Services\RankingSnapshotService;
use App\Models\UserX1Stats;

/**
 * Atualiza rankings e aquece cache
 * 
 * Rode a cada 5 minutos via cron para manter rankings frescos
 */
class UpdateRankingsCache extends Command
{
    protected $signature = 'cache:update-rankings 
                            {--warm : Também aquece o cache geral}';

    protected $description = 'Atualiza cache de rankings X1 e Fantasy';

    public function handle()
    {
        $this->info('Atualizando rankings...');
        
        // Limpa cache atual
        CacheService::clearX1Ranking();
        
        // Regenera ranking X1
        $this->line('  Gerando ranking X1 top 30...');
        $ranking = CacheService::getX1Ranking(30);
        $count = isset($ranking['total']) ? $ranking['total'] : count($ranking);
        $this->info("    -> {$count} jogadores no ranking");
        
        // Se tiver serviço de snapshot de ranking, chama ele também
        if (class_exists(RankingSnapshotService::class)) {
            try {
                $this->line('  Atualizando snapshot de ranking...');
                $service = new RankingSnapshotService();
                
                if (method_exists($service, 'generateX1Ranking')) {
                    $service->generateX1Ranking();
                    $this->info('    -> Snapshot X1 atualizado');
                }
                
                if (method_exists($service, 'generateFantasyRanking')) {
                    $service->generateFantasyRanking();
                    $this->info('    -> Snapshot Fantasy atualizado');
                }
            } catch (\Exception $e) {
                $this->warn("    -> Erro no snapshot: " . $e->getMessage());
            }
        }
        
        // Aquece cache geral se solicitado
        if ($this->option('warm')) {
            $this->line('  Aquecendo cache geral...');
            CacheService::warmUp();
            $this->info('    -> Cache aquecido');
        }
        
        $this->info('Rankings atualizados com sucesso!');
        
        return 0;
    }
}
