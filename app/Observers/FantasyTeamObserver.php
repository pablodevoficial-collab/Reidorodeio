<?php

namespace App\Observers;

use App\Models\FantasyTeam;
use App\Http\Controllers\Admin\BotManagementController;
use Illuminate\Support\Facades\Log;

/**
 * Observer para FantasyTeam
 * 
 * Monitora criação de times de usuários reais e ajusta
 * a quantidade de bots automaticamente (proporção 1:3)
 */
class FantasyTeamObserver
{
    /**
     * Chamado após um novo time ser criado
     */
    public function created(FantasyTeam $team): void
    {
        // Só processa se for time de usuário REAL (não bot)
        if ($team->user_id && !$team->bot_user_id) {
            $this->adjustBotsAfterRealUserJoined($team);
        }
    }

    /**
     * Ajusta bots quando um usuário real entra na liga
     */
    protected function adjustBotsAfterRealUserJoined(FantasyTeam $team): void
    {
        try {
            $leagueId = $team->fantasy_league_id;
            
            if (!$leagueId) {
                return;
            }

            // Chama método estático do controller de bots
            $result = BotManagementController::adjustBotsInLeague($leagueId);

            if ($result['removed'] > 0) {
                Log::info("🤖 FantasyTeamObserver: {$result['reason']}", [
                    'league_id' => $leagueId,
                    'new_team_id' => $team->id,
                    'user_id' => $team->user_id,
                    'bots_removed' => $result['removed'],
                ]);
            }

        } catch (\Exception $e) {
            Log::error("❌ FantasyTeamObserver erro: " . $e->getMessage(), [
                'team_id' => $team->id,
                'league_id' => $team->fantasy_league_id ?? null,
            ]);
        }
    }
}
