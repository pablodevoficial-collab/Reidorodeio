<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class X1RoomSeeder extends Seeder
{
    /**
     * Gera salas X1 em diversos estados:
     * - open (aguardando oponente)
     * - in_progress (com oponente, partida em andamento)
     * - closed (encerrada, resultado definido)
     * - finished (prêmio processado)
     * - cancelled (expirada/cancelada)
     * - pending_payment (aguardando pagamento do host)
     * - private (aberta com código de acesso)
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Pegar IDs existentes do banco
        $userIds   = DB::table('users')->where('id', '!=', 1)->pluck('id')->toArray();
        $compIds   = DB::table('competitors')->pluck('id')->toArray();
        $rodeioId  = DB::table('rodeios')->value('id');
        $modId     = DB::table('modalidades')->value('id');
        $groupIds  = DB::table('modalidade_competitor_groups')->pluck('id')->toArray();

        if (count($userIds) < 20) {
            $this->command->error('Precisa de pelo menos 20 usuários. Encontrados: ' . count($userIds));
            return;
        }
        if (empty($compIds)) {
            $this->command->error('Nenhum competidor encontrado.');
            return;
        }

        shuffle($userIds);
        shuffle($compIds);
        shuffle($groupIds);

        $idx  = 0;
        $cIdx = 0;
        $gIdx = 0;

        $nextUser  = function () use (&$userIds, &$idx) { return $userIds[$idx++ % count($userIds)]; };
        $nextComp  = function () use (&$compIds, &$cIdx) { return $compIds[$cIdx++ % count($compIds)]; };
        $nextGroup = function () use (&$groupIds, &$gIdx) { return empty($groupIds) ? null : $groupIds[$gIdx++ % count($groupIds)]; };

        $feeFor = function (float $valor, bool $premium): float {
            return $valor <= 1000 ? ($premium ? 7 : 10) : ($premium ? 10 : 15);
        };

        $prizeFor = function (float $valor, float $fee): float {
            return ($valor * 2) - (($valor * 2) * ($fee / 100));
        };

        // Helper: create room + host participant + host payment
        $createRoom = function (array $room, string $status, int $hostId, int $compId, ?int $groupId, float $fee, float $prize, Carbon $created, ?Carbon $hostPaid = null, array $extra = []) use ($rodeioId, $modId) {
            $data = array_merge([
                'name'                          => $room['name'],
                'description'                   => $room['desc'] ?? null,
                'criador_id'                    => $hostId,
                'host_user_id'                  => $hostId,
                'competitor_escolhido_criador'   => $compId,
                'rodeio_id'                     => $rodeioId,
                'modalidade_id'                 => $modId,
                'competitor_id'                 => $compId,
                'competitor_group_id'           => $groupId,
                'valor_entrada'                 => $room['valor'],
                'fee_percent'                   => $fee,
                'is_premium_room'               => $room['premium'] ?? false,
                'is_bot_room'                   => false,
                'prize_total'                   => $prize,
                'currency'                      => 'BRL',
                'status'                        => $status,
                'host_paid_at'                  => $hostPaid,
                'created_at'                    => $created,
                'updated_at'                    => $created,
            ], $extra);

            return DB::table('x1_rooms')->insertGetId($data);
        };

        $createParticipant = function (int $roomId, int $userId, int $compId, ?int $groupId, int $slot, float $amount, bool $isHost, string $payStatus, ?Carbon $paidAt, Carbon $created) {
            DB::table('x1_participants')->insert([
                'x1_room_id'        => $roomId,
                'user_id'           => $userId,
                'competitor_id'     => $compId,
                'competitor_group_id' => $groupId,
                'slot'              => $slot,
                'amount'            => $amount,
                'payment_status'    => $payStatus,
                'is_host'           => $isHost,
                'paid_at'           => $paidAt,
                'created_at'        => $created,
                'updated_at'        => $created,
            ]);
        };

        $createPayment = function (int $roomId, int $userId, string $role, float $amount, float $fee, string $status, ?Carbon $paidAt, Carbon $created) {
            DB::table('x1_payments')->insert([
                'x1_room_id'  => $roomId,
                'user_id'     => $userId,
                'role'        => $role,
                'amount'      => $amount,
                'fee_percent' => $fee,
                'provider'    => 'mercadopago',
                'status'      => $status,
                'paid_at'     => $paidAt,
                'created_at'  => $created,
                'updated_at'  => $created,
            ]);
        };

        // =============================================
        // 1) SALAS ABERTAS (open) - aguardando oponente
        // =============================================
        $openRooms = [
            ['valor' => 20,   'name' => 'X1 Amistoso R$20',      'desc' => 'Sala aberta para quem quiser!', 'premium' => false, 'hours_ago' => 2],
            ['valor' => 50,   'name' => 'Desafio R$50',           'desc' => 'Vem pro duelo!', 'premium' => false, 'hours_ago' => 1],
            ['valor' => 100,  'name' => 'X1 Centenário',          'desc' => 'Sala de R$100 esperando adversário', 'premium' => false, 'hours_ago' => 3],
            ['valor' => 200,  'name' => 'Duelo Alto R$200',       'desc' => 'Só os brabos! Sala de R$200', 'premium' => false, 'hours_ago' => 0.5],
            ['valor' => 500,  'name' => 'X1 Premium R$500',       'desc' => 'Sala premium de alto valor', 'premium' => true, 'hours_ago' => 1],
            ['valor' => 10,   'name' => 'X1 Iniciante',           'desc' => 'Sala para iniciantes, R$10', 'premium' => false, 'hours_ago' => 4],
            ['valor' => 1000, 'name' => 'X1 Lendário R$1000',     'desc' => 'O maior desafio! Quem topa?', 'premium' => true, 'hours_ago' => 0.25],
            ['valor' => 50,   'name' => 'Duelo Rápido',           'desc' => 'Sala rápida de R$50', 'premium' => false, 'hours_ago' => 5],
        ];

        $openCount = 0;
        foreach ($openRooms as $r) {
            $hostId  = $nextUser();
            $compId  = $nextComp();
            $groupId = $nextGroup();
            $fee     = $feeFor($r['valor'], $r['premium']);
            $prize   = $prizeFor($r['valor'], $fee);
            $created = $now->copy()->subHours($r['hours_ago']);
            $expires = $now->copy()->addHours(24 - $r['hours_ago']);

            $roomId = $createRoom($r, 'open', $hostId, $compId, $groupId, $fee, $prize, $created, $created, [
                'expires_at' => $expires,
            ]);

            $createParticipant($roomId, $hostId, $compId, $groupId, 1, $r['valor'], true, 'approved', $created, $created);
            $createPayment($roomId, $hostId, 'host', $r['valor'], $fee, 'approved', $created, $created);
            $openCount++;
        }
        $this->command->info("✅ {$openCount} salas OPEN criadas");

        // =============================================
        // 2) SALAS EM ANDAMENTO (in_progress)
        // =============================================
        $inProgressRooms = [
            ['valor' => 50,   'name' => 'Duelo Quente R$50',      'desc' => 'Partida em andamento!', 'premium' => false, 'hours_ago' => 1],
            ['valor' => 100,  'name' => 'X1 em Disputa',           'desc' => 'Quem leva essa?', 'premium' => false, 'hours_ago' => 0.5],
            ['valor' => 200,  'name' => 'Batalha R$200',           'desc' => 'Os dois já entraram!', 'premium' => false, 'hours_ago' => 2],
            ['valor' => 500,  'name' => 'X1 Master R$500',         'desc' => 'Confronto de alto nível', 'premium' => true, 'hours_ago' => 0.75],
            ['valor' => 20,   'name' => 'Duelo Iniciante',         'desc' => 'Partida de R$20 rolando', 'premium' => false, 'hours_ago' => 3],
            ['valor' => 1000, 'name' => 'Mega X1 R$1000',          'desc' => 'A sala mais cara em jogo!', 'premium' => true, 'hours_ago' => 0.3],
        ];

        $ipCount = 0;
        foreach ($inProgressRooms as $r) {
            $hostId     = $nextUser();
            $opponentId = $nextUser();
            $compHost   = $nextComp();
            $compOpp    = $nextComp();
            $groupHost  = $nextGroup();
            $groupOpp   = $nextGroup();
            $fee        = $feeFor($r['valor'], $r['premium']);
            $prize      = $prizeFor($r['valor'], $fee);
            $createdAt  = $now->copy()->subHours($r['hours_ago'] + 0.5);
            $joinedAt   = $now->copy()->subHours($r['hours_ago']);

            $roomId = $createRoom($r, 'in_progress', $hostId, $compHost, $groupHost, $fee, $prize, $createdAt, $createdAt, [
                'oponente_id'                    => $opponentId,
                'competitor_escolhido_oponente'  => $compOpp,
                'data_inicio'                    => $joinedAt,
                'expires_at'                     => $now->copy()->addHours(24),
                'updated_at'                     => $joinedAt,
            ]);

            $createParticipant($roomId, $hostId,     $compHost, $groupHost, 1, $r['valor'], true,  'approved', $createdAt, $createdAt);
            $createParticipant($roomId, $opponentId,  $compOpp,  $groupOpp,  2, $r['valor'], false, 'approved', $joinedAt,  $joinedAt);

            $createPayment($roomId, $hostId,     'host',     $r['valor'], $fee, 'approved', $createdAt, $createdAt);
            $createPayment($roomId, $opponentId, 'opponent', $r['valor'], $fee, 'approved', $joinedAt,  $joinedAt);

            $ipCount++;
        }
        $this->command->info("✅ {$ipCount} salas IN_PROGRESS criadas");

        // =============================================
        // 3) SALAS FECHADAS (closed) - resultado definido
        // =============================================
        $closedRooms = [
            ['valor' => 50,   'name' => 'X1 Encerrado R$50',      'desc' => 'Resultado definido!', 'premium' => false, 'hours_ago' => 6],
            ['valor' => 100,  'name' => 'Duelo Finalizado',        'desc' => 'Partida encerrada', 'premium' => false, 'hours_ago' => 8],
            ['valor' => 200,  'name' => 'X1 Definido R$200',       'desc' => 'Vencedor decidido!', 'premium' => false, 'hours_ago' => 12],
            ['valor' => 500,  'name' => 'X1 Premium Encerrado',    'desc' => 'Sala premium finalizada', 'premium' => true, 'hours_ago' => 10],
            ['valor' => 20,   'name' => 'Duelo R$20 Encerrado',    'desc' => 'Sala encerrada', 'premium' => false, 'hours_ago' => 24],
        ];

        $closedCount = 0;
        foreach ($closedRooms as $r) {
            $hostId     = $nextUser();
            $opponentId = $nextUser();
            $compHost   = $nextComp();
            $compOpp    = $nextComp();
            $groupHost  = $nextGroup();
            $groupOpp   = $nextGroup();
            $fee        = $feeFor($r['valor'], $r['premium']);
            $prize      = $prizeFor($r['valor'], $fee);
            $winnerId   = rand(0, 1) === 0 ? $hostId : $opponentId;
            $loserId    = $winnerId === $hostId ? $opponentId : $hostId;
            $winnerSlot = $winnerId === $hostId ? 1 : 2;
            $createdAt  = $now->copy()->subHours($r['hours_ago'] + 1);
            $joinedAt   = $createdAt->copy()->addMinutes(30);
            $closedAt   = $now->copy()->subHours($r['hours_ago']);

            $roomId = $createRoom($r, 'closed', $hostId, $compHost, $groupHost, $fee, $prize, $createdAt, $createdAt, [
                'oponente_id'                    => $opponentId,
                'competitor_escolhido_oponente'  => $compOpp,
                'vencedor_id'                    => $winnerId,
                'data_inicio'                    => $joinedAt,
                'data_fim'                       => $closedAt,
                'closed_at'                      => $closedAt,
                'expires_at'                     => $createdAt->copy()->addHours(24),
                'updated_at'                     => $closedAt,
            ]);

            $createParticipant($roomId, $hostId, $compHost, $groupHost, 1, $r['valor'], true, 'approved', $createdAt, $createdAt);
            $createParticipant($roomId, $opponentId, $compOpp, $groupOpp, 2, $r['valor'], false, 'approved', $joinedAt, $joinedAt);

            $createPayment($roomId, $hostId,     'host',     $r['valor'], $fee, 'approved', $createdAt, $createdAt);
            $createPayment($roomId, $opponentId, 'opponent', $r['valor'], $fee, 'approved', $joinedAt,  $joinedAt);

            DB::table('x1_results')->insert([
                'x1_room_id'     => $roomId,
                'winner_user_id' => $winnerId,
                'winner_slot'    => $winnerSlot,
                'loser_user_id'  => $loserId,
                'payload'        => json_encode(['winner_id' => $winnerId, 'prize' => $prize]),
                'processed_at'   => $closedAt,
                'created_at'     => $closedAt,
                'updated_at'     => $closedAt,
            ]);

            $closedCount++;
        }
        $this->command->info("✅ {$closedCount} salas CLOSED criadas");

        // =============================================
        // 4) SALAS FINALIZADAS (finished) - prêmio pago
        // =============================================
        $finishedRooms = [
            ['valor' => 50,   'name' => 'X1 Pago R$50',           'desc' => 'Prêmio já distribuído', 'premium' => false, 'days_ago' => 1],
            ['valor' => 100,  'name' => 'Duelo Pago R$100',       'desc' => 'Vencedor recebeu o prêmio', 'premium' => false, 'days_ago' => 2],
            ['valor' => 200,  'name' => 'X1 Completo R$200',      'desc' => 'Tudo resolvido!', 'premium' => false, 'days_ago' => 3],
            ['valor' => 500,  'name' => 'X1 Premium Pago',        'desc' => 'Sala premium concluída', 'premium' => true, 'days_ago' => 1],
            ['valor' => 1000, 'name' => 'Mega X1 Pago R$1000',    'desc' => 'O grande prêmio foi pago!', 'premium' => true, 'days_ago' => 5],
            ['valor' => 10,   'name' => 'X1 Mini Pago',           'desc' => 'Salinha de R$10 finalizada', 'premium' => false, 'days_ago' => 7],
            ['valor' => 20,   'name' => 'X1 R$20 Finalizado',     'desc' => 'Mais uma concluída', 'premium' => false, 'days_ago' => 4],
            ['valor' => 2000, 'name' => 'X1 Supremo R$2000',      'desc' => 'A maior sala já jogada!', 'premium' => true, 'days_ago' => 2],
        ];

        $finCount = 0;
        foreach ($finishedRooms as $r) {
            $hostId     = $nextUser();
            $opponentId = $nextUser();
            $compHost   = $nextComp();
            $compOpp    = $nextComp();
            $groupHost  = $nextGroup();
            $groupOpp   = $nextGroup();
            $fee        = $feeFor($r['valor'], $r['premium']);
            $prize      = $prizeFor($r['valor'], $fee);
            $winnerId   = rand(0, 1) === 0 ? $hostId : $opponentId;
            $loserId    = $winnerId === $hostId ? $opponentId : $hostId;
            $winnerSlot = $winnerId === $hostId ? 1 : 2;
            $createdAt  = $now->copy()->subDays($r['days_ago'])->subHours(2);
            $joinedAt   = $createdAt->copy()->addMinutes(30);
            $closedAt   = $createdAt->copy()->addHours(1);
            $finishedAt = $createdAt->copy()->addHours(1.5);

            $roomId = $createRoom($r, 'finished', $hostId, $compHost, $groupHost, $fee, $prize, $createdAt, $createdAt, [
                'oponente_id'                    => $opponentId,
                'competitor_escolhido_oponente'  => $compOpp,
                'vencedor_id'                    => $winnerId,
                'data_inicio'                    => $joinedAt,
                'data_fim'                       => $closedAt,
                'closed_at'                      => $closedAt,
                'finished_at'                    => $finishedAt,
                'expires_at'                     => $createdAt->copy()->addHours(24),
                'updated_at'                     => $finishedAt,
            ]);

            $createParticipant($roomId, $hostId, $compHost, $groupHost, 1, $r['valor'], true, 'approved', $createdAt, $createdAt);
            $createParticipant($roomId, $opponentId, $compOpp, $groupOpp, 2, $r['valor'], false, 'approved', $joinedAt, $joinedAt);

            $createPayment($roomId, $hostId,     'host',     $r['valor'], $fee, 'approved', $createdAt, $createdAt);
            $createPayment($roomId, $opponentId, 'opponent', $r['valor'], $fee, 'approved', $joinedAt,  $joinedAt);

            DB::table('x1_results')->insert([
                'x1_room_id'     => $roomId,
                'winner_user_id' => $winnerId,
                'winner_slot'    => $winnerSlot,
                'loser_user_id'  => $loserId,
                'payload'        => json_encode(['winner_id' => $winnerId, 'prize' => $prize, 'paid' => true]),
                'processed_at'   => $closedAt,
                'prize_paid_at'  => $finishedAt,
                'created_at'     => $closedAt,
                'updated_at'     => $finishedAt,
            ]);

            $finCount++;
        }
        $this->command->info("✅ {$finCount} salas FINISHED criadas");

        // =============================================
        // 5) SALAS CANCELADAS (cancelled)
        // =============================================
        $cancelledRooms = [
            ['valor' => 50,   'name' => 'X1 Expirado R$50',       'desc' => 'Ninguém entrou a tempo', 'premium' => false, 'days_ago' => 1],
            ['valor' => 100,  'name' => 'Duelo Cancelado',         'desc' => 'Sala cancelada pelo host', 'premium' => false, 'days_ago' => 2],
            ['valor' => 20,   'name' => 'X1 Timeout',              'desc' => 'Expirou sem oponente', 'premium' => false, 'days_ago' => 3],
        ];

        $cancelCount = 0;
        foreach ($cancelledRooms as $r) {
            $hostId  = $nextUser();
            $compId  = $nextComp();
            $groupId = $nextGroup();
            $fee     = $feeFor($r['valor'], $r['premium']);
            $prize   = $prizeFor($r['valor'], $fee);
            $createdAt = $now->copy()->subDays($r['days_ago'])->subHours(25);
            $expiredAt = $createdAt->copy()->addHours(24);

            $roomId = $createRoom($r, 'cancelled', $hostId, $compId, $groupId, $fee, $prize, $createdAt, $createdAt, [
                'closed_at'  => $expiredAt,
                'expires_at' => $expiredAt,
                'updated_at' => $expiredAt,
            ]);

            $createParticipant($roomId, $hostId, $compId, $groupId, 1, $r['valor'], true, 'approved', $createdAt, $createdAt);
            $createPayment($roomId, $hostId, 'host', $r['valor'], $fee, 'approved', $createdAt, $createdAt);

            $cancelCount++;
        }
        $this->command->info("✅ {$cancelCount} salas CANCELLED criadas");

        // =============================================
        // 6) SALAS PENDENTES DE PAGAMENTO (pending_payment)
        // =============================================
        $pendingRooms = [
            ['valor' => 50,  'name' => 'X1 Aguardando PIX',      'desc' => 'Esperando pagamento do criador', 'premium' => false],
            ['valor' => 100, 'name' => 'Sala Nova R$100',         'desc' => 'Recém criada, PIX pendente', 'premium' => false],
        ];

        $pendCount = 0;
        foreach ($pendingRooms as $r) {
            $hostId  = $nextUser();
            $compId  = $nextComp();
            $groupId = $nextGroup();
            $fee     = $feeFor($r['valor'], $r['premium']);
            $prize   = $prizeFor($r['valor'], $fee);
            $createdAt = $now->copy()->subMinutes(5);

            $roomId = $createRoom($r, 'pending_payment', $hostId, $compId, $groupId, $fee, $prize, $createdAt, null, [
                'expires_at' => $now->copy()->addMinutes(15),
            ]);

            $createParticipant($roomId, $hostId, $compId, $groupId, 1, $r['valor'], true, 'pending', null, $createdAt);
            $createPayment($roomId, $hostId, 'host', $r['valor'], $fee, 'pending', null, $createdAt);

            $pendCount++;
        }
        $this->command->info("✅ {$pendCount} salas PENDING_PAYMENT criadas");

        // =============================================
        // 7) SALAS PRIVADAS (open, com access_code)
        // =============================================
        $privateRooms = [
            ['valor' => 100, 'name' => 'X1 Privado #1',          'desc' => 'Sala privada com código', 'premium' => false, 'code' => 'AMIGO2026'],
            ['valor' => 200, 'name' => 'Duelo Secreto',           'desc' => 'Apenas convidados', 'premium' => false, 'code' => 'SECRETO22'],
        ];

        $privCount = 0;
        foreach ($privateRooms as $r) {
            $hostId  = $nextUser();
            $compId  = $nextComp();
            $groupId = $nextGroup();
            $fee     = $feeFor($r['valor'], $r['premium']);
            $prize   = $prizeFor($r['valor'], $fee);
            $createdAt = $now->copy()->subHours(1);

            $roomId = $createRoom($r, 'open', $hostId, $compId, $groupId, $fee, $prize, $createdAt, $createdAt, [
                'is_private'  => true,
                'access_code' => $r['code'],
                'expires_at'  => $now->copy()->addHours(23),
            ]);

            $createParticipant($roomId, $hostId, $compId, $groupId, 1, $r['valor'], true, 'approved', $createdAt, $createdAt);
            $createPayment($roomId, $hostId, 'host', $r['valor'], $fee, 'approved', $createdAt, $createdAt);

            $privCount++;
        }
        $this->command->info("✅ {$privCount} salas PRIVADAS criadas");

        $total = $openCount + $ipCount + $closedCount + $finCount + $cancelCount + $pendCount + $privCount;
        $this->command->info("🎯 TOTAL: {$total} salas X1 criadas com sucesso!");
    }
}
