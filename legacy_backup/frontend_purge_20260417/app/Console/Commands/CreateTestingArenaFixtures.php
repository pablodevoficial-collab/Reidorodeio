<?php

namespace App\Console\Commands;

use App\Models\Competitor;
use App\Models\FantasyLeague;
use App\Models\Modalidade;
use App\Models\Rodeio;
use App\Models\User;
use App\Models\X1Participant;
use App\Models\X1Payment;
use App\Models\X1RoomInstance;
use App\Services\X1RoomService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateTestingArenaFixtures extends Command
{
    protected $signature = 'testing:create-arena-fixtures {--refresh : Recria as fixtures de teste existentes}';

    protected $description = 'Cria rodeio, bolao de R$0,01 e salas X1 com entradas baixas para testes';

    public function handle(): int
    {
        $users = User::query()
            ->where('email', 'not like', '%@bot.local')
            ->orderBy('id')
            ->take(4)
            ->get();

        if ($users->count() < 3) {
            $this->error('Nao ha usuarios suficientes para criar as salas X1 de teste.');
            return self::FAILURE;
        }

        $competitors = Competitor::query()
            ->orderByDesc('id')
            ->take(6)
            ->get();

        if ($competitors->count() < 3) {
            $this->error('Nao ha competidores suficientes para montar o rodeio de teste.');
            return self::FAILURE;
        }

        $x1Service = app(X1RoomService::class);
        $now = now();

        DB::transaction(function () use ($users, $competitors, $x1Service, $now) {
            $rodeio = Rodeio::query()->updateOrCreate(
                ['name' => 'Rodeio Rei do Rodeio - Testes'],
                [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->addDays(7),
                    'status' => 1,
                    'status_transmissao' => 'ao_vivo',
                    'divisao_atual' => 'Teste',
                    'pausar_x1' => false,
                    'info' => [
                        'cidade' => 'Ambiente de Testes',
                        'descricao' => 'Rodeio criado automaticamente para testes de bolao e X1 com entradas baixas.',
                    ],
                ]
            );

            $modalidade = Modalidade::query()->updateOrCreate(
                [
                    'rodeio_id' => $rodeio->id,
                    'nome' => 'Laço Comprido Teste',
                ],
                [
                    'inicio' => $now,
                    'tipo_premio' => 'dinheiro',
                    'valor_premio' => 0,
                    'descricao_premio' => 'Premiacao de ambiente de testes',
                    'status' => 'programado',
                    'pausar_x1' => false,
                    'tem_divisoes' => true,
                    'divisoes' => [
                        ['nome' => 'Teste'],
                    ],
                    'tipo_participacao' => 'individual',
                    'tamanho_equipe' => 1,
                ]
            );

            $rodeio->forceFill([
                'modalidade_atual' => $modalidade->id,
                'updated_at' => $now,
            ])->save();

            $syncPayload = [];
            foreach ($competitors as $index => $competitor) {
                $syncPayload[$competitor->id] = [
                    'divisao' => 'Teste',
                    'status' => 'ativo',
                    'numero_participacao' => $index + 1,
                    'multiplicador_atual' => 1.9,
                    'disponivel_participacao' => 1,
                    'observacoes' => 'Fixture automatica para testes de arena',
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }
            $modalidade->competitors()->syncWithoutDetaching($syncPayload);

            FantasyLeague::query()->updateOrCreate(
                ['name' => 'Bolão Teste R$0,01'],
                [
                    'category' => 'Bolão Teste',
                    'image' => null,
                    'price' => 0.01,
                    'house_cut_percent' => 10,
                    'is_premium' => false,
                    'reward_mode' => 'computed',
                    'manual_prize_pool' => null,
                    'total_prize' => 0.90,
                    'is_active' => true,
                    'is_bot_league' => false,
                    'max_users' => 100,
                    'rodeio_id' => $rodeio->id,
                    'modalidade_id' => $modalidade->id,
                    'divisao' => 'Teste',
                    'closes_at' => $now->copy()->addDays(7),
                    'registration_deadline' => $now->copy()->addDays(7),
                    'allow_late_registration' => false,
                ]
            );

            if ($this->option('refresh')) {
                $existingRooms = X1RoomInstance::query()
                    ->whereIn('name', [
                        'Sala X1 Teste R$0,01',
                        'Sala X1 Teste R$1,00',
                        'Sala X1 Teste R$5,00',
                    ])
                    ->get();

                foreach ($existingRooms as $room) {
                    X1Payment::query()->where('x1_room_id', $room->id)->delete();
                    X1Participant::query()->where('x1_room_id', $room->id)->delete();
                    $room->delete();
                }
            }

            $fixtures = [
                [
                    'name' => 'Sala X1 Teste R$0,01',
                    'description' => 'Sala de teste com entrada minima liberada.',
                    'amount' => 0.01,
                    'host' => $users[0],
                    'competitor' => $competitors[0],
                ],
                [
                    'name' => 'Sala X1 Teste R$1,00',
                    'description' => 'Sala de teste com entrada reduzida para validacao de PIX.',
                    'amount' => 1.00,
                    'host' => $users[1],
                    'competitor' => $competitors[1],
                ],
                [
                    'name' => 'Sala X1 Teste R$5,00',
                    'description' => 'Sala de teste com valor baixo para homologacao.',
                    'amount' => 5.00,
                    'host' => $users[2],
                    'competitor' => $competitors[2],
                ],
            ];

            foreach ($fixtures as $fixture) {
                $room = X1RoomInstance::query()->where('name', $fixture['name'])->first();
                if ($room) {
                    continue;
                }

                $host = $fixture['host'];
                $competitor = $fixture['competitor'];
                $amount = (float) $fixture['amount'];
                $feePercent = $x1Service->resolveFeePercent($host, $amount);
                $prizeTotal = $x1Service->calculatePrizeTotal($amount, $feePercent);
                $referenceSlug = str_replace([' ', 'R$', ',', '.'], ['_', '', '_', ''], mb_strtolower($fixture['name']));

                $room = X1RoomInstance::query()->create([
                    'name' => $fixture['name'],
                    'description' => $fixture['description'],
                    'criador_id' => $host->id,
                    'host_user_id' => $host->id,
                    'competitor_escolhido_criador' => $competitor->id,
                    'rodeio_id' => $rodeio->id,
                    'modalidade_id' => $modalidade->id,
                    'divisao' => 'Teste',
                    'competitor_id' => $competitor->id,
                    'valor_entrada' => $amount,
                    'is_private' => false,
                    'fee_percent' => $feePercent,
                    'is_premium_room' => false,
                    'prize_total' => $prizeTotal,
                    'currency' => 'BRL',
                    'status' => 'open',
                    'host_paid_at' => $now,
                    'expires_at' => $now->copy()->addDays(2),
                    'metadata' => [
                        'fixture_group' => 'arena_testing',
                        'fixture_key' => $referenceSlug,
                    ],
                ]);

                X1Participant::query()->create([
                    'x1_room_id' => $room->id,
                    'user_id' => $host->id,
                    'slot' => 1,
                    'competitor_id' => $competitor->id,
                    'amount' => $amount,
                    'payment_status' => 'approved',
                    'paid_at' => $now,
                    'is_host' => true,
                ]);

                X1Payment::query()->create([
                    'x1_room_id' => $room->id,
                    'user_id' => $host->id,
                    'role' => 'host',
                    'amount' => $amount,
                    'fee_percent' => $feePercent,
                    'provider' => 'mercadopago',
                    'external_reference' => 'fixture_' . $referenceSlug,
                    'provider_payment_id' => 'fixture_' . $referenceSlug,
                    'provider_preference_id' => 'fixture_' . $referenceSlug,
                    'status' => 'approved',
                    'payload' => [
                        'fixture' => true,
                    ],
                    'paid_at' => $now,
                ]);
            }

            if (!Cache::has('fantasy_leagues_cache_version')) {
                Cache::forever('fantasy_leagues_cache_version', 1);
            }
            Cache::increment('fantasy_leagues_cache_version');
        });

        $this->info('Fixtures de teste criadas/atualizadas: rodeio, bolao de R$0,01 e salas X1.');

        return self::SUCCESS;
    }
}
