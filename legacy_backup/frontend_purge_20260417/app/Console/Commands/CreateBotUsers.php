<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\X1RoomInstance;
use App\Models\X1Participant;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\Competitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateBotUsers extends Command
{
    protected $signature = 'bots:populate {--x1=10} {--fantasy=5} {--clear}';
    protected $description = '🤖 Popular site com bots (X1 e Fantasy)';

    public function handle()
    {
        if ($this->option('clear')) {
            $this->clearBots();
            return;
        }

        $botsData = $this->loadBotsData();
        if (empty($botsData)) {
            $this->error('❌ Arquivo bots.json vazio! Gere pessoas em https://www.4devs.com.br/gerador_de_pessoas');
            return;
        }

        $x1Count = (int) $this->option('x1');
        $fantasyCount = (int) $this->option('fantasy');

        $this->info("🤖 Criando bots...");
        $botUsers = $this->createBotUsers($botsData, $x1Count * 2 + $fantasyCount * 50);

        if ($x1Count > 0) {
            $this->info("⚔️ Criando {$x1Count} salas X1...");
            $this->createBotX1Rooms($botUsers, $x1Count);
        }

        if ($fantasyCount > 0) {
            $this->info("🏆 Criando {$fantasyCount} ligas Fantasy...");
            $this->createBotFantasyLeagues($botUsers, $fantasyCount);
        }

        $this->info("✅ Bots criados com sucesso!");
    }

    private function loadBotsData(): array
    {
        $path = storage_path('app/bots.json');
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?? [];
    }

    private function createBotUsers(array $botsData, int $count): array
    {
        $users = [];
        $limit = min($count, count($botsData));
        
        // Pre-calculate hash for performance
        $passwordHash = Hash::make('bot-password-default');

        for ($i = 0; $i < $limit; $i++) {
            $bot = $botsData[$i];
            
            // Check if bot already exists to avoid duplication/errors
            $email = strtolower(Str::slug($bot['nome'] ?? 'bot')) . $i . '@bot.local';
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $bot['nome'] ?? 'Bot User',
                    'email' => $email,
                    'is_bot' => true,
                    'cpf' => preg_replace('/\D/', '', $bot['cpf'] ?? ''),
                    'phone' => preg_replace('/\D/', '', $bot['celular'] ?? ''),
                    'password' => $passwordHash,
                    'email_verified_at' => now(),
                ]);
            }

            $users[] = $user;
        }

        return $users;
    }

    private function createBotX1Rooms(array $botUsers, int $count): void
    {
        $rodeios = \App\Models\Rodeio::where('status_transmissao', '!=', 'finalizado')->get();
        if ($rodeios->isEmpty()) return;

        for ($i = 0; $i < $count; $i++) {
            $rodeio = $rodeios->random();
            $modalidade = $rodeio->modalidades()->first();
            if (!$modalidade) continue;

            $competitors = $modalidade->competitors()->inRandomOrder()->limit(2)->get();
            if ($competitors->count() < 2) continue;

            $host = $botUsers[array_rand($botUsers)];
            $opponent = $botUsers[array_rand($botUsers)];
            while ($opponent->id === $host->id) {
                $opponent = $botUsers[array_rand($botUsers)];
            }

            $valor = collect([50, 100, 200, 500])->random();

            $room = X1RoomInstance::create([
                'rodeio_id' => $rodeio->id,
                'modalidade_id' => $modalidade->id,
                'host_user_id' => $host->id,
                'competitor_escolhido_criador' => $competitors[0]->id,
                'competitor_escolhido_oponente' => $competitors[1]->id,
                'valor_entrada' => $valor,
                'status' => 'in_progress',
                'is_bot_room' => true,
                'name' => "X1 {$modalidade->nome}",
                'description' => 'Sala bot',
            ]);

            X1Participant::create([
                'x1_room_id' => $room->id,
                'user_id' => $host->id,
                'competitor_id' => $competitors[0]->id,
                'is_host' => true,
            ]);

            X1Participant::create([
                'x1_room_id' => $room->id,
                'user_id' => $opponent->id,
                'competitor_id' => $competitors[1]->id,
                'is_host' => false,
            ]);
        }
    }

    private function createBotFantasyLeagues(array $botUsers, int $count): void
    {
        $rodeios = \App\Models\Rodeio::where('status_transmissao', '!=', 'finalizado')->get();
        if ($rodeios->isEmpty()) return;

        for ($i = 0; $i < $count; $i++) {
            $rodeio = $rodeios->random();
            $modalidade = $rodeio->modalidades()->first();
            if (!$modalidade) continue;

            $competitors = $modalidade->competitors()->inRandomOrder()->limit(20)->get();
            if ($competitors->count() < 8) continue;

            $league = FantasyLeague::create([
                'name' => "Bolão " . Str::random(8),
                'rodeio_id' => $rodeio->id,
                'modalidade_id' => $modalidade->id,
                'entry_fee' => 0,
                'max_users' => 50,
                'is_active' => true,
                'is_bot_league' => true,
                'is_premium' => false,
            ]);

            $teamsCount = rand(30, 50);
            for ($j = 0; $j < $teamsCount; $j++) {
                $bot = $botUsers[array_rand($botUsers)];
                $teamCompetitors = $competitors->random(4);

                $team = FantasyTeam::create([
                    'user_id' => $bot->id,
                    'fantasy_league_id' => $league->id,
                    'team_name' => 'Time ' . $bot->name,
                    'total_points' => 0,
                    'is_active' => true,
                ]);

                foreach ($teamCompetitors as $k => $comp) {
                    $team->competitors()->attach($comp->id, [
                        'is_captain' => $k === 0,
                        'current_points' => 0,
                    ]);
                }
            }
        }
    }

    private function clearBots(): void
    {
        $this->info('🗑️ Limpando bots...');
        
        $botUserIds = User::where('is_bot', true)->pluck('id');
        
        X1RoomInstance::where('is_bot_room', true)->delete();
        FantasyLeague::where('is_bot_league', true)->delete();
        User::where('is_bot', true)->delete();
        
        $this->info('✅ Bots removidos!');
    }
}
