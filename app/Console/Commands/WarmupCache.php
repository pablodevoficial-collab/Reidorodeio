<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

/**
 * Aquece o cache do sistema
 * 
 * Rode após deploy ou restart do servidor
 */
class WarmupCache extends Command
{
    protected $signature = 'cache:warmup 
                            {--clear : Limpa cache antes de aquecer}';

    protected $description = 'Aquece o cache com dados frequentemente acessados';

    public function handle()
    {
        if ($this->option('clear')) {
            $this->info('Limpando cache atual...');
            CacheService::clearAll();
        }
        
        $this->info('Aquecendo cache do sistema...');
        
        // Rankings
        $this->line('  [1/4] Rankings X1...');
        $ranking = CacheService::getX1Ranking(30);
        $this->info("    -> " . count($ranking) . " jogadores cacheados");
        
        // Rodeios
        $this->line('  [2/4] Rodeios...');
        $active = CacheService::getActiveRodeios();
        $all = CacheService::getAllRodeios();
        $this->info("    -> " . count($active) . " ativos, " . count($all) . " total");
        
        // Modalidades
        $this->line('  [3/4] Modalidades...');
        $modalidades = CacheService::getModalidades();
        $this->info("    -> " . count($modalidades) . " modalidades cacheadas");
        
        // Competidores por modalidade
        $this->line('  [4/4] Competidores...');
        $totalCompetitors = 0;
        foreach ($modalidades as $mod) {
            $competitors = CacheService::getCompetitorsByModalidade($mod['id']);
            $totalCompetitors += count($competitors);
        }
        $this->info("    -> {$totalCompetitors} competidores cacheados");
        
        $this->newLine();
        $this->info('✓ Cache aquecido com sucesso!');
        
        return 0;
    }
}
