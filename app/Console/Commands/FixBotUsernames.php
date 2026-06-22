<?php

namespace App\Console\Commands;

use App\Models\BotUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixBotUsernames extends Command
{
    protected $signature = 'bots:fix-usernames {--premium-percent=70 : Percentual de bots que devem ser premium}';
    protected $description = 'Atualizar usernames de bots para estilo FIFA e ajustar premium';

    public function handle()
    {
        $this->info("=== VERSAO 3 === fix-usernames");

        $bots = BotUser::all();
        $this->info("🤖 Atualizando {$bots->count()} bots...");

        // Carregar TODOS os usernames existentes pra evitar duplicata
        $allBotUsernames = BotUser::pluck('username', 'id')->toArray();
        $allUsersUsernames = DB::table('users')->pluck('username')->toArray();
        $usedUsernames = array_flip(array_merge(array_values($allBotUsernames), $allUsersUsernames));

        $suffixes = ['Jr', 'Neto', 'FC', 'BR', 'Pro', 'GG', 'RR', 'XD', 'CR', 'FX', 'MS', 'TK', 'LG', 'SG', 'VT'];
        $updated = 0;
        $errors = 0;

        foreach ($bots as $bot) {
            try {
                $primeiro = ucfirst(Str::ascii(mb_strtolower($bot->firstname ?? 'Bot')));
                $ultimo = ucfirst(Str::ascii(mb_strtolower($bot->lastname ?? 'User')));
                $partes = explode(' ', $ultimo);
                $ultimo = ucfirst(end($partes));

                $formats = [
                    $primeiro . $ultimo,
                    $primeiro . '_' . $ultimo,
                    $primeiro . '.' . $ultimo,
                    $primeiro . strtoupper(substr($ultimo, 0, 1)),
                    strtoupper(substr($primeiro, 0, 1)) . $ultimo,
                    $primeiro . $suffixes[array_rand($suffixes)],
                    strtolower($primeiro) . $ultimo,
                    $primeiro . strtoupper(substr($primeiro, 0, 1) . substr($ultimo, 0, 1)),
                    strtolower($primeiro) . '_' . strtolower($ultimo),
                    $ultimo . $primeiro,
                    $ultimo . '_' . strtoupper(substr($primeiro, 0, 2)),
                    strtolower($primeiro) . rand(1, 99),
                    $primeiro . '_' . rand(10, 99),
                ];

                shuffle($formats);

                $newUsername = null;
                foreach ($formats as $candidate) {
                    if (!isset($usedUsernames[$candidate])) {
                        $newUsername = $candidate;
                        break;
                    }
                }

                // Fallback GARANTIDO: usa ID do bot (impossível duplicar)
                if (!$newUsername) {
                    $newUsername = $primeiro . '_' . $bot->id;
                }

                // Registrar novo, liberar antigo
                unset($usedUsernames[$bot->username]);
                $usedUsernames[$newUsername] = true;

                $oldEmail = strtolower(Str::ascii($bot->username)) . '@bot.local';
                $newEmail = strtolower(Str::ascii($newUsername)) . '@bot.local';

                DB::table('bot_users')->where('id', $bot->id)->update([
                    'username' => $newUsername,
                    'email' => $newEmail,
                    'updated_at' => now(),
                ]);

                // Sincronizar tabela users - tentar por email e por username antigo
                DB::table('users')
                    ->where(function ($q) use ($oldEmail, $bot) {
                        $q->where('email', $oldEmail)
                          ->orWhere('email', strtolower($bot->username) . '@bot.local')
                          ->orWhere('username', $bot->username);
                    })
                    ->where('email', 'like', '%@bot.local')
                    ->update([
                        'username' => $newUsername,
                        'email' => $newEmail,
                    ]);

                $updated++;
            } catch (\Throwable $e) {
                $errors++;
                $this->warn("  ⚠️ bot#{$bot->id}: {$e->getMessage()}");
                // NÃO morre, continua pro próximo
            }
        }

        $this->info("✅ {$updated} bots atualizados! ({$errors} erros)");

        // Ajustar premium dos bots
        $premiumPercent = (int) $this->option('premium-percent');
        $totalBots = $bots->count();
        $targetPremium = (int) round($totalBots * $premiumPercent / 100);
        $currentPremium = BotUser::where('is_premium', true)->count();

        $this->info("👑 Premium: {$currentPremium}/{$totalBots} atual → alvo {$targetPremium} ({$premiumPercent}%)");

        if ($targetPremium > $currentPremium) {
            BotUser::where('is_premium', false)
                ->inRandomOrder()
                ->limit($targetPremium - $currentPremium)
                ->update(['is_premium' => true, 'premium_until' => now()->addDays(180)]);
            $this->info("  ⬆️ Promovidos a premium");
        } elseif ($targetPremium < $currentPremium) {
            BotUser::where('is_premium', true)
                ->inRandomOrder()
                ->limit($currentPremium - $targetPremium)
                ->update(['is_premium' => false, 'premium_until' => null]);
            $this->info("  ⬇️ Removidos do premium");
        }

        return 0;
    }
}
