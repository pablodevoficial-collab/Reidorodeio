<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\X1RoomInstance;
use App\Models\X1Payment;

class CleanExpiredX1Rooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x1:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpar salas X1 pendentes que expiraram (30 minutos sem pagamento)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Buscando salas X1 expiradas...');

        // 1. Limpar salas pending_payment (ninguém pagou ou pagou parcial e expirou)
        // Se alguém pagou numa sala pending (ex: criador pagou mas status não virou open?), 
        // idealmente deveria reembolsar também. Mas por simplificação, pending_payment geralmente é "ninguém pagou".
        // Vamos verificar se há pagamentos confirmados nessas salas antes de deletar.
        
        $pendingRooms = X1RoomInstance::where('status', 'pending_payment')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($pendingRooms as $room) {
            // Verificar se há algum participante que pagou (caso raro de erro de status)
            $paidParticipants = $room->participants()->where('payment_status', 'paid')->get();
            
            if ($paidParticipants->count() > 0) {
                $this->refundParticipants($room, $paidParticipants, 'pending_expired');
            }

            // Cancelar payments associados
            X1Payment::where('x1_room_id', $room->id)
                ->update(['status' => 'expired']);
            
            $room->delete(); // Ou update status para 'cancelled'
            $this->line("   Sala pendente #{$room->id} removida.");
        }

        // 2. Limpar salas OPEN que expiraram (Criador pagou, mas ninguém entrou)
        // "AS salas x1 que tiverem um competidor ou grupo selecionado e nao tiverem oponente aceito"
        $openExpiredRooms = X1RoomInstance::where('status', 'open')
            ->where('expires_at', '<=', now())
            ->get();

        if ($openExpiredRooms->count() > 0) {
            $this->warn("⚠️ Encontradas {$openExpiredRooms->count()} sala(s) OPEN expiradas.");
            
            foreach ($openExpiredRooms as $room) {
                $this->line("   Processando sala OPEN #{$room->id}...");

                // Reembolsar participantes que pagaram (Geralmente só o Host)
                $paidParticipants = $room->participants()->where('payment_status', 'paid')->get();
                $this->refundParticipants($room, $paidParticipants, 'open_expired');

                // Atualizar status para cancelled/expired
                $room->status = 'cancelled';
                $room->save();
            }
        }

        $this->info("✅ Limpeza concluída.");
        return 0;
    }

    private function refundParticipants($room, $participants, $reason)
    {
        foreach ($participants as $participant) {
            $user = $participant->user;
            if (!$user) continue;

            $amount = $participant->amount;
            
            // Reembolso integral para o saldo principal (Cancelamento)
            $user->balance += $amount;
            $user->save();

            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'charge' => 0,
                'post_balance' => $user->balance,
                'trx_type' => '+',
                'details' => "Reembolso X1 - Sala #{$room->id} expirada sem oponente",
                'trx' => getTrx(),
                'remark' => 'x1_refund_expired',
            ]);

            // Atualizar status do participant
            $participant->payment_status = 'refunded';
            $participant->save();

            // Atualizar status do pagamento
            X1Payment::where('x1_room_id', $room->id)
                ->where('user_id', $user->id)
                ->update(['status' => 'refunded_expired']);
                
            $this->line("      Reembolsado usuário #{$user->id}: R$ {$amount}");
        }
    }
}
