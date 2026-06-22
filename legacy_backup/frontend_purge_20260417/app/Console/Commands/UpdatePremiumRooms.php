<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\X1RoomInstance;
use App\Models\User;

class UpdatePremiumRooms extends Command
{
    protected $signature = 'x1:update-premium-rooms';
    protected $description = 'Atualiza salas existentes de usuários premium para taxa 8%';

    public function handle()
    {
        $this->info('Buscando usuários premium...');

        // Buscar todos usuários premium
        $premiumUsers = User::whereHas('subscriptions', function ($query) {
            $query->where('status', 'ativa')
                ->where('data_fim', '>=', now());
        })->pluck('id');

        if ($premiumUsers->isEmpty()) {
            $this->warn('Nenhum usuário premium encontrado.');
            return 0;
        }

        $this->info("Encontrados {$premiumUsers->count()} usuários premium.");

        // Buscar salas abertas de usuários premium que ainda não são premium
        $rooms = X1RoomInstance::whereIn('host_user_id', $premiumUsers)
            ->where('is_premium_room', false)
            ->whereIn('status', ['open', 'pending_payment'])
            ->get();

        if ($rooms->isEmpty()) {
            $this->info('Nenhuma sala para atualizar.');
            return 0;
        }

        $this->info("Atualizando {$rooms->count()} salas...");
        $bar = $this->output->createProgressBar($rooms->count());

        foreach ($rooms as $room) {
            $room->fee_percent = 8.0;
            $room->is_premium_room = true;

            if ($room->valor_entrada) {
                $total = (float) $room->valor_entrada * 2;
                $fee = $total * 0.08;
                $room->prize_total = round($total - $fee, 2);
            }

            $room->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Salas atualizadas com sucesso!');

        return 0;
    }
}
