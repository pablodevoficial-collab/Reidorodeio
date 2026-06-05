<?php

namespace App\Console\Commands;

use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FantasyCleanupBots extends Command
{
    protected $signature = 'fantasy:cleanup-bots';
    protected $description = 'Remove bots de bolões que fecham em menos de 1 hora';

    public function handle()
    {
        $cutoff = now()->addHour();

        // Ligas que fecham em menos de 1 hora e ainda têm bots
        $leagues = FantasyLeague::where('is_active', true)
            ->whereNotNull('registration_deadline')
            ->where('registration_deadline', '<=', $cutoff)
            ->where('registration_deadline', '>', now())
            ->get();

        $totalRemoved = 0;

        foreach ($leagues as $league) {
            $botTeams = FantasyTeam::where('fantasy_league_id', $league->id)
                ->whereNotNull('bot_user_id')
                ->whereNull('deleted_at')
                ->get();

            if ($botTeams->isEmpty()) continue;

            $count = $botTeams->count();
            foreach ($botTeams as $botTeam) {
                $botTeam->delete(); // soft delete
            }

            $totalRemoved += $count;
            $minutesLeft = now()->diffInMinutes($league->registration_deadline);
            Log::info("Fantasy cleanup: removed {$count} bots from league '{$league->name}' (ID: {$league->id}, {$minutesLeft}min left)");
            $this->line("  Liga '{$league->name}': {$count} bots removidos ({$minutesLeft}min para fechar)");
        }

        if ($totalRemoved > 0) {
            $this->info("✅ {$totalRemoved} bots removidos de {$leagues->count()} ligas");
        } else {
            $this->info("Nenhum bot para remover no momento");
        }

        return 0;
    }
}
