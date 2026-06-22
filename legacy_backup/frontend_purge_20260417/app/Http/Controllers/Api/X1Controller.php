<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\X1RoomInstance;
use App\Models\X1Participant;
use App\Models\X1Payment;
use App\Events\X1RoomClosed;
use Illuminate\Support\Facades\DB;
use App\Services\X1RoomService;
use App\Services\MercadoPagoService;
use App\Models\Modalidade;

class X1Controller extends Controller
{
    private function presetOpenEntries(): array
    {
        return [20.0, 50.0, 100.0, 150.0, 250.0, 500.0];
    }

    private function minEntryAmount(): float
    {
        return max(20, (float) config('arena.x1_min_entry', 20));
    }

    private function maxEntryAmount(): float
    {
        return max($this->minEntryAmount(), (float) config('arena.x1_max_entry', 10000));
    }

    public function __construct()
    {
        // Auth middleware already applied in routes
    }

    public function index(Request $request)
    {
        $x1Service = app(X1RoomService::class);
        $modalidadeId = (int) $request->integer('modalidade_id');
        $rodeioId = (int) $request->integer('rodeio_id');
        $divisao = trim((string) $request->query('divisao', ''));

        // Buscar salas abertas, em progresso E finalizadas nos últimos 5 dias
        $fiveDaysAgo = now()->subDays(5);
        
        $roomsQuery = X1RoomInstance::with([
            'host',
            'modalidade',
            'rodeio',
            'competitor',
            'competitorGroup.members',
            'participants.user',
            'participants.competitor',
            'participants.competitorGroup.members',
            'result' // Incluir resultado para salas finalizadas
        ])
            ->where(function($query) use ($fiveDaysAgo) {
                // Salas abertas ou em progresso
                $query->whereIn('status', ['open','in_progress'])
                    // OU salas finalizadas nos últimos 5 dias
                    ->orWhere(function($q) use ($fiveDaysAgo) {
                        $q->where('status', 'finished')
                          ->where('finished_at', '>=', $fiveDaysAgo);
                    });
            });

        if ($modalidadeId > 0) {
            $roomsQuery->where('modalidade_id', $modalidadeId);
        }

        if ($rodeioId > 0) {
            $roomsQuery->where('rodeio_id', $rodeioId);
        }

        if ($divisao !== '') {
            $roomsQuery->where('divisao', $divisao);
        }

        $rooms = $roomsQuery
            ->latest('created_at')
            ->get();

        $coveredPresetEntries = $rooms
            ->filter(function (X1RoomInstance $room) {
                return in_array((string) $room->status, ['open', 'in_progress'], true);
            })
            ->map(fn (X1RoomInstance $room) => round((float) ($room->valor_entrada ?? 0), 2))
            ->unique()
            ->values()
            ->all();

        $data = $rooms->map(function (X1RoomInstance $room) use ($x1Service) {
            $entryAmount = (float) ($room->valor_entrada ?? 0);
            $feePercent = (float) ($room->fee_percent ?? 0);
            if ($entryAmount > 0 && ($feePercent <= 0 || $feePercent > 20)) {
                $feePercent = $room->host
                    ? $x1Service->resolveFeePercent($room->host, $entryAmount)
                    : 10.0;
            }

            $expectedPrize = $entryAmount > 0
                ? $x1Service->calculatePrizeTotal($entryAmount, $feePercent)
                : 0.0;

            $storedPrize = $room->prize_total !== null ? (float) $room->prize_total : null;
            $prize = $storedPrize;

            // Corrige automaticamente dados antigos inconsistentes.
            if ($prize === null || abs($prize - $expectedPrize) > 0.5) {
                $prize = $expectedPrize;
            }

            // Dados do resultado (se finalizada)
            $result = null;
            if ($room->status === 'finished' && $room->result) {
                $result = [
                    'winner_slot' => $room->result->winner_slot,
                    'winner_user_id' => $room->result->winner_user_id,
                    'loser_user_id' => $room->result->loser_user_id,
                    'reason' => $room->result->reason,
                ];
            }

            return [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'status' => $room->status,
                'valor_entrada' => $room->valor_entrada,
                'fee_percent' => $feePercent,
                'is_premium_room' => $room->is_premium_room,
                'prize_total' => $prize,
                'is_private' => $room->is_private,
                'finished_at' => $room->finished_at,
                'result' => $result,
                'host' => $room->host ? [
                    'id' => $room->host->id,
                    'name' => $room->host->getPublicUsername(),
                    'image' => $room->host->image ? asset('assets/images/user/profile/' . $room->host->image) : null,
                    'is_premium' => $room->host->isPremium(),
                ] : null,
                'rodeio' => $room->rodeio?->only(['id','nome']),
                'rodeio_id' => $room->rodeio_id,
                'modalidade' => $room->modalidade ? [
                    'id' => $room->modalidade->id,
                    'nome' => $room->modalidade->nome,
                    'tamanho_equipe' => (int) ($room->modalidade->tamanho_equipe ?? 1),
                ] : null,
                'modalidade_id' => $room->modalidade_id,
                'competitor_id' => $room->competitor_id,
                'competitor_group_id' => $room->competitor_group_id,
                'competitor' => $room->competitor ? [
                    'id' => $room->competitor->id,
                    'nome' => $room->competitor->nome,
                    'foto_url' => $room->competitor->foto_url,
                ] : null,
                'competitor_group' => $room->competitorGroup ? [
                    'id' => $room->competitorGroup->id,
                    'nome' => $room->competitorGroup->nome ?: $room->competitorGroup->members->pluck('nome')->implode(' + '),
                    'members' => $room->competitorGroup->members->map(fn ($m) => [
                        'id' => $m->id,
                        'nome' => $m->nome,
                        'foto_url' => $m->foto_url,
                    ])->values(),
                ] : null,
                'participants' => $room->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'slot' => $participant->slot,
                        'is_host' => (bool) $participant->is_host,
                        'user' => $participant->user ? [
                            'id' => $participant->user->id,
                            'name' => $participant->user->getPublicUsername(),
                            'image' => $participant->user->image ? asset('assets/images/user/profile/' . $participant->user->image) : null,
                            'is_premium' => $participant->user->isPremium(),
                        ] : null,
                        'competitor' => $participant->competitor ? [
                            'id' => $participant->competitor->id,
                            'nome' => $participant->competitor->nome,
                            'foto_url' => $participant->competitor->foto_url,
                        ] : null,
                        'competitor_group' => $participant->competitorGroup ? [
                            'id' => $participant->competitorGroup->id,
                            'nome' => $participant->competitorGroup->nome ?: $participant->competitorGroup->members->pluck('nome')->implode(' + '),
                            'members' => $participant->competitorGroup->members->map(fn ($m) => [
                                'id' => $m->id,
                                'nome' => $m->nome,
                                'foto_url' => $m->foto_url,
                            ])->values(),
                        ] : null,
                    ];
                }),
            ];
        });

        $presetRooms = collect($this->presetOpenEntries())
            ->map(fn (float $value) => round($value, 2))
            ->filter(function (float $value) use ($coveredPresetEntries) {
                return !in_array($value, $coveredPresetEntries, true);
            })
            ->map(function (float $value) use ($x1Service, $modalidadeId, $rodeioId, $divisao) {
                $feePercent = $value <= 1000 ? 10.0 : 15.0;
                $modalidade = null;
                if ($modalidadeId > 0) {
                    $modalidade = Modalidade::query()->find($modalidadeId);
                }

                return [
                    'id' => 'preset-' . str_replace('.', '-', number_format($value, 2, '.', '')),
                    'name' => 'Sala rápida ' . number_format($value, 0, ',', '.'),
                    'description' => 'Sala pré-aberta aguardando jogadores dos dois lados.',
                    'status' => 'open',
                    'valor_entrada' => number_format($value, 2, '.', ''),
                    'fee_percent' => $feePercent,
                    'is_premium_room' => false,
                    'prize_total' => $x1Service->calculatePrizeTotal($value, $feePercent),
                    'is_private' => false,
                    'finished_at' => null,
                    'result' => null,
                    'host' => null,
                    'rodeio' => $rodeioId > 0 ? ['id' => $rodeioId, 'nome' => optional($modalidade?->rodeio)->nome] : null,
                    'rodeio_id' => $rodeioId > 0 ? $rodeioId : null,
                    'modalidade' => $modalidade ? [
                        'id' => $modalidade->id,
                        'nome' => $modalidade->nome,
                        'tamanho_equipe' => (int) ($modalidade->tamanho_equipe ?? 1),
                    ] : null,
                    'modalidade_id' => $modalidadeId > 0 ? $modalidadeId : null,
                    'divisao' => $divisao !== '' ? $divisao : null,
                    'competitor_id' => null,
                    'competitor_group_id' => null,
                    'competitor' => null,
                    'competitor_group' => null,
                    'participants' => [],
                    'is_placeholder' => true,
                    'placeholder_copy' => 'Aguardando jogadores dos dois lados',
                ];
            });

        $data = $data
            ->concat($presetRooms)
            ->sortBy(function (array $room) {
                $isPlaceholder = (bool) ($room['is_placeholder'] ?? false);
                $entryValue = number_format((float) ($room['valor_entrada'] ?? 0), 2, '.', '');

                return sprintf(
                    '%s-%s-%s',
                    $entryValue,
                    $isPlaceholder ? '1' : '0',
                    (string) ($room['id'] ?? '')
                );
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    // Check if user has active rooms
    private function hasActiveRoom($userId)
    {
        // 1. Check if user is hosting a room (pending, open, in_progress)
        $hosting = X1RoomInstance::where('host_user_id', $userId)
            ->whereIn('status', ['pending_payment', 'open', 'in_progress'])
            ->exists();

        if ($hosting) return true;

        // 2. Check if user is a paid participant in an active room
        $playing = X1Participant::where('user_id', $userId)
            ->whereHas('room', function($q) {
                $q->whereIn('status', ['open', 'in_progress']);
            })
            ->exists();
            
        if ($playing) return true;

        // 3. Check if user has a pending payment attempt (opponent role)
        // This prevents creating multiple pending payments for different rooms
        $pendingPayment = X1Payment::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('role', 'opponent')
            ->whereHas('room', function($q) {
                $q->whereIn('status', ['open']); // Room must still be open
            })
            ->exists();

        return $pendingPayment;
    }

    // Create a room (host)
    public function store(Request $request)
    {
        $user = $request->user();
        $minEntryAmount = $this->minEntryAmount();

        $data = $request->validate([
            'description' => 'nullable|string',
            'rodeio_id' => 'nullable|integer',
            'modalidade_id' => 'required|integer',
            'competitor_id' => 'nullable|integer',
            'competitor_group_id' => 'nullable|integer',
            'valor_entrada' => 'required|numeric|min:' . $minEntryAmount,
            'divisao' => 'nullable|string',
            'platform' => 'nullable|string|max:20',
        ]);

        // === VERIFICAR SE EVENTO ESTÁ FINALIZADO ===
        if (!empty($data['rodeio_id'])) {
            $rodeio = \App\Models\Rodeio::find($data['rodeio_id']);
            if ($rodeio && $rodeio->status_transmissao === 'finalizado') {
                return response()->json(['message' => 'Evento finalizado. Não é possível criar novas salas X1.'], 422);
            }
        }

        $modalidade = Modalidade::whereKey($data['modalidade_id'])->firstOrFail();
        
        // === VERIFICAR SE X1 ESTÁ PAUSADO PARA ESTA MODALIDADE ===
        if ($modalidade->pausar_x1) {
            return response()->json(['message' => 'Criação de salas X1 pausada para esta modalidade.'], 422);
        }
        
        $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);

        $competitorId = (int) ($data['competitor_id'] ?? 0);
        $groupId = (int) ($data['competitor_group_id'] ?? 0);
        if ($teamSize > 1) {
            if ($groupId <= 0) {
                return response()->json(['message' => 'Selecione um grupo válido.'], 422);
            }
            $competitorId = 0;
        } else {
            if ($competitorId <= 0) {
                return response()->json(['message' => 'Selecione um competidor válido.'], 422);
            }
            $groupId = 0;
        }

        $x1Service = app(X1RoomService::class);
        $x1Service->validateRodeioModalidadeCompetitor($data['rodeio_id'] ?? null, $data['modalidade_id'] ?? null, $competitorId ?: null, $groupId ?: null);

        $entryAmount = (float) $data['valor_entrada'];
        $feePercent = $x1Service->resolveFeePercent($user, $entryAmount);
        $prizeTotal = $x1Service->calculatePrizeTotal($entryAmount, $feePercent);
        $isPremiumRoom = $user->isPremium();

        // Determinar o ID do competitor para a FK antiga
        $competitorIdForFK = $competitorId ?: null;
        if (!$competitorIdForFK && $groupId) {
            // Se for grupo, pegar o primeiro competidor do grupo
            $firstMember = DB::table('modalidade_competitor_group_members')
                ->where('group_id', $groupId)
                ->first();
            $competitorIdForFK = $firstMember ? $firstMember->competitor_id : null;
        }
        
        if (!$competitorIdForFK) {
            return response()->json(['message' => 'Competidor inválido para criar sala.'], 422);
        }

        /**
         * === MATCHMAKING AUTOMÁTICO (evitar criar nova sala se já houver uma compatível) ===
         * Regras:
         *  - Sala deve estar OPEN e pública
         *  - Mesmo valor de entrada e modalidade
         *  - Host diferente do usuário atual
         *  - Competidor/Grupo do host deve ser diferente do selecionado pelo usuário
         *  - Rodeio/divisão, quando informados, devem coincidir
         *  - Sala não pode estar expirada/fechada
         */
        $compatibleRoom = X1RoomInstance::query()
            ->where('status', 'open')
            ->where('is_private', false)
            ->where('modalidade_id', $data['modalidade_id'])
            ->where('host_user_id', '!=', $user->id)
            ->where('valor_entrada', $entryAmount)
            ->when(!empty($data['rodeio_id']), function ($q) use ($data) {
                $q->where('rodeio_id', $data['rodeio_id']);
            })
            ->when(!empty($data['divisao']), function ($q) use ($data) {
                $q->where('divisao', $data['divisao']);
            })
            ->where(function ($q) use ($teamSize, $competitorId, $groupId) {
                if ($teamSize > 1) {
                    $q->whereNull('competitor_group_id')
                        ->orWhere('competitor_group_id', '!=', $groupId);
                } else {
                    $q->whereNull('competitor_id')
                        ->orWhere('competitor_id', '!=', $competitorId);
                }
            })
            ->whereNull('closed_at')
            ->whereNull('vencedor_id')
            ->orderBy('created_at')
            ->first();

        if ($compatibleRoom) {
            // Forçar entrada direta como opponent, sem criar nova sala
            $joinRequest = $request->duplicate(
                $request->query->all(),
                [
                    'competitor_id' => $competitorId ?: null,
                    'competitor_group_id' => $groupId ?: null,
                ]
            );
            $joinRequest->setUserResolver(function () use ($user) {
                return $user;
            });

            return $this->join($joinRequest, $compatibleRoom->id);
        }

        $room = X1RoomInstance::create([
            'criador_id' => $user->id, // Coluna antiga (obrigatória por FK)
            'competitor_escolhido_criador' => $competitorIdForFK, // FK obrigatória
            'host_user_id' => $user->id,
            'rodeio_id' => $data['rodeio_id'] ?? null,
            'modalidade_id' => $data['modalidade_id'],
            'divisao' => $data['divisao'] ?? null,
            'competitor_id' => $competitorId ?: null,
            'competitor_group_id' => $groupId ?: null,
            'name' => 'Sala X1 #' . now()->format('His'),
            'description' => $data['description'] ?? null,
            'valor_entrada' => $data['valor_entrada'],
            'is_private' => false,
            'access_code' => null,
            'fee_percent' => $feePercent,
            'is_premium_room' => $isPremiumRoom,
            'prize_total' => $prizeTotal,
            'currency' => 'BRL',
            'status' => 'pending_payment',
            'expires_at' => now()->addMinutes(30), // Expira em 30 minutos
        ]);

        $mp = app(MercadoPagoService::class);
        $externalRef = $mp->buildExternalReference($room->id, $user->id, 'host');

        $preference = $mp->createPreference([
            'items' => [[
                'title' => 'Entrada X1 - Sala #' . $room->id,
                'quantity' => 1,
                'unit_price' => (float) $room->valor_entrada,
                'currency_id' => 'BRL',
            ]],
            'external_reference' => $externalRef,
            'payer' => [
                'email' => $user->email,
                'name' => $user->firstname ?? $user->username,
                'entity_type' => 'individual',
            ],
        ]);

        X1Payment::create([
            'x1_room_id' => $room->id,
            'user_id' => $user->id,
            'role' => 'host',
            'amount' => $room->valor_entrada,
            'fee_percent' => $feePercent,
            'provider' => 'mercadopago',
            'external_reference' => $externalRef,
            'provider_preference_id' => $preference['id'] ?? null,
            'status' => 'pending',
            'payload' => array_merge($preference, [
                'competitor_id' => $competitorId ?: null,
                'competitor_group_id' => $groupId ?: null,
            ]),
        ]);

        return response()->json([
            'data' => $room,
            'payment' => [
                'provider' => 'mercadopago',
                'preference_id' => $preference['id'] ?? null,
                'init_point' => $preference['init_point'] ?? null,
                'public_key' => config('services.mercadopago.public_key'),
                'expires_at' => $room->expires_at->toIso8601String(), // Timestamp ISO8601
            ],
        ], 201);
    }

    // Join a room
    public function join(Request $request, $id)
    {
        $user = $request->user();

        $data = $request->validate([
            'competitor_id' => 'nullable|integer',
            'competitor_group_id' => 'nullable|integer',
            'platform' => 'nullable|string|max:20',
        ]);

        $room = X1RoomInstance::findOrFail($id);
        
        // === VERIFICAR SE X1 ESTÁ PAUSADO PARA ESTA MODALIDADE ===
        if ($room->modalidade && $room->modalidade->pausar_x1) {
            return response()->json(['message' => 'Criação de salas X1 pausada para esta modalidade.'], 422);
        }
        
        // === VERIFICAR SE EVENTO ESTÁ FINALIZADO ===
        if ($room->rodeio_id) {
            $rodeio = \App\Models\Rodeio::find($room->rodeio_id);
            if ($rodeio && $rodeio->status_transmissao === 'finalizado') {
                return response()->json(['message' => 'Evento finalizado. Não é possível entrar em salas X1.'], 422);
            }
        }
        
        if ($room->status !== 'open') {
            return response()->json(['message' => 'Room not open'], 400);
        }

        if ($room->host_user_id === $user->id) {
            return response()->json(['message' => 'Criador não pode entrar na própria sala'], 400);
        }

        $teamSize = (int) ($room->modalidade?->tamanho_equipe ?? 1);
        $competitorId = (int) ($data['competitor_id'] ?? 0);
        $groupId = (int) ($data['competitor_group_id'] ?? 0);

        if ($teamSize > 1) {
            if ($groupId <= 0) {
                return response()->json(['message' => 'Selecione um grupo válido.'], 422);
            }
            if ((int) $room->competitor_group_id === $groupId) {
                return response()->json(['message' => 'Grupo já selecionado pelo criador'], 422);
            }
            $competitorId = 0;
        } else {
            if ($competitorId <= 0) {
                return response()->json(['message' => 'Selecione um competidor válido.'], 422);
            }
            if ((int) $room->competitor_id === $competitorId) {
                return response()->json(['message' => 'Competidor já selecionado pelo criador'], 422);
            }
            $groupId = 0;
        }

        $x1Service = app(X1RoomService::class);
        $x1Service->validateRodeioModalidadeCompetitor($room->rodeio_id, $room->modalidade_id, $competitorId ?: null, $groupId ?: null);

        $existingParticipant = $room->participants()->where('user_id', $user->id)->first();
        if ($existingParticipant) {
            return response()->json(['message' => 'Usuário já está na sala'], 409);
        }

        if ($room->participants()->count() >= 2) {
            return response()->json(['message' => 'Sala já está completa'], 409);
        }

        $mp = app(MercadoPagoService::class);
        $externalRef = $mp->buildExternalReference($room->id, $user->id, 'opponent');

        $preference = $mp->createPreference([
            'items' => [[
                'title' => 'Entrada X1 - Sala #' . $room->id,
                'quantity' => 1,
                'unit_price' => (float) $room->valor_entrada,
                'currency_id' => 'BRL',
            ]],
            'external_reference' => $externalRef,
            'payer' => [
                'email' => $user->email,
                'name' => $user->firstname ?? $user->username,
                'entity_type' => 'individual',
            ],
        ]);

        \Log::info('💾 Salvando pagamento do opponent com competidor:', [
            'competitor_id' => $competitorId,
            'competitor_group_id' => $groupId,
            'team_size' => $teamSize,
            'user_id' => $user->id,
            'room_id' => $room->id,
        ]);

        X1Payment::create([
            'x1_room_id' => $room->id,
            'user_id' => $user->id,
            'role' => 'opponent',
            'amount' => $room->valor_entrada,
            'fee_percent' => $room->fee_percent ?? 20,
            'provider' => 'mercadopago',
            'external_reference' => $externalRef,
            'provider_preference_id' => $preference['id'] ?? null,
            'status' => 'pending',
            'payload' => array_merge($preference, [
                'competitor_id' => $competitorId,
                'competitor_group_id' => $groupId,
            ]),
        ]);

        \Log::info('✅ Pagamento salvo com sucesso para opponent');

        return response()->json([
            'data' => ['room_id' => $room->id],
            'payment' => [
                'provider' => 'mercadopago',
                'preference_id' => $preference['id'] ?? null,
                'init_point' => $preference['init_point'] ?? null,
                'public_key' => config('services.mercadopago.public_key'),
            ],
        ], 201);
    }

    public function quickBet(Request $request)
    {
        $user = $request->user();
        $minEntryAmount = $this->minEntryAmount();
        $maxEntryAmount = $this->maxEntryAmount();

        $data = $request->validate([
            'rodeio_id' => 'nullable|integer',
            'modalidade_id' => 'required|integer',
            'competitor_id' => 'required|integer',
            'valor_entrada' => 'required|numeric|min:' . $minEntryAmount . '|max:' . $maxEntryAmount,
            'divisao' => 'nullable|string',
            'platform' => 'nullable|string|max:20',
        ]);

        $modalidade = Modalidade::whereKey($data['modalidade_id'])->firstOrFail();

        if ((int) ($modalidade->tamanho_equipe ?? 1) > 1) {
            return response()->json([
                'message' => 'Aposta rápida disponível apenas para modalidades individuais.'
            ], 422);
        }

        $rodeioId = (int) ($data['rodeio_id'] ?? $modalidade->rodeio_id ?? 0);
        $competitorId = (int) $data['competitor_id'];
        $entryAmount = (float) $data['valor_entrada'];

        $x1Service = app(X1RoomService::class);
        $x1Service->validateRodeioModalidadeCompetitor(
            $rodeioId ?: null,
            (int) $data['modalidade_id'],
            $competitorId
        );

        $compatibleRoom = X1RoomInstance::query()
            ->where('status', 'open')
            ->where('modalidade_id', (int) $data['modalidade_id'])
            ->where('is_private', false)
            ->when($rodeioId > 0, function ($query) use ($rodeioId) {
                $query->where('rodeio_id', $rodeioId);
            })
            ->where('host_user_id', '!=', $user->id)
            ->where('valor_entrada', $entryAmount)
            ->where(function ($query) use ($competitorId) {
                $query->whereNull('competitor_id')
                    ->orWhere('competitor_id', '!=', $competitorId);
            })
            ->whereNull('closed_at')
            ->whereNull('vencedor_id')
            ->orderBy('created_at')
            ->first();

        if ($compatibleRoom) {
            $joinRequest = $request->duplicate(
                $request->query->all(),
                ['competitor_id' => $competitorId]
            );
            $joinRequest->setUserResolver(function () use ($user) {
                return $user;
            });

            return $this->join($joinRequest, $compatibleRoom->id);
        }

        $storeRequest = $request->duplicate(
            $request->query->all(),
            [
                'description' => 'Entrada rápida (Início)',
                'rodeio_id' => $rodeioId ?: null,
                'modalidade_id' => (int) $data['modalidade_id'],
                'competitor_id' => $competitorId,
                'valor_entrada' => $entryAmount,
                'divisao' => $data['divisao'] ?? null,
            ]
        );
        $storeRequest->setUserResolver(function () use ($user) {
            return $user;
        });

        return $this->store($storeRequest);
    }

    // Close room and dispatch processing event
    public function close(Request $request, $id)
    {
        $user = $request->user();
        $room = X1RoomInstance::findOrFail($id);
        if ($room->host_user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $room->status = 'closed';
        $room->closed_at = now();
        $room->save();

        event(new X1RoomClosed($room));

        return response()->json(['data' => $room]);
    }

    // Process PIX payment
    public function processPayment(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'formData' => 'nullable|array',
            'preferenceId' => 'required|string',
            'platform' => 'nullable|string|max:20',
        ]);

        try {
            // Buscar o payment associado à preference
            $payment = X1Payment::where('provider_preference_id', $data['preferenceId'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $room = X1RoomInstance::findOrFail($payment->x1_room_id);

            // Criar pagamento PIX via MercadoPago
            $mpService = app(MercadoPagoService::class);

            if ($payment->provider_payment_id && $payment->status === 'pending') {
                $existingQrCode = data_get($payment->payload, 'point_of_interaction.transaction_data.qr_code')
                    ?? data_get($payment->payload, 'qr_code');
                $existingQrCodeBase64 = data_get($payment->payload, 'point_of_interaction.transaction_data.qr_code_base64')
                    ?? data_get($payment->payload, 'qr_code_base64');

                if ($existingQrCode || $existingQrCodeBase64) {
                    return response()->json([
                        'success' => true,
                        'payment_id' => $payment->provider_payment_id,
                        'status' => $payment->status,
                        'qr_code' => $existingQrCode,
                        'qr_code_base64' => $existingQrCodeBase64,
                        'point_of_interaction' => data_get($payment->payload, 'point_of_interaction'),
                        'reused' => true,
                    ]);
                }
            }
            
            $paymentData = [
                'transaction_amount' => (float) $payment->amount,
                'description' => 'Entrada X1 - Sala #' . $room->id,
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $user->email,
                    'first_name' => $user->firstname ?? $user->username,
                    'last_name' => $user->lastname ?? '',
                    'entity_type' => 'individual',
                ],
                'external_reference' => $payment->external_reference,
                'notification_url' => route('ipn.MercadoPago'),
            ];

            $pixPayment = $mpService->createPixPayment($paymentData);

            \Log::info('💳 PIX criado com sucesso', [
                'mp_payment_id' => $pixPayment['id'] ?? 'NULL',
                'mp_status' => $pixPayment['status'] ?? 'unknown',
                'local_payment_id' => $payment->id,
                'preference_id' => $data['preferenceId'],
            ]);

            // Atualizar payment com dados do PIX
            $originalCompetitorId = data_get($payment->payload, 'competitor_id');
            $originalGroupId = data_get($payment->payload, 'competitor_group_id');

            unset($pixPayment['competitor_id'], $pixPayment['competitor_group_id']);

            $payment->update([
                'provider_payment_id' => $pixPayment['id'] ? (string) $pixPayment['id'] : null,
                'payload' => array_merge($pixPayment, [
                    'competitor_id' => $originalCompetitorId,
                    'competitor_group_id' => $originalGroupId,
                ]),
            ]);

            // Extrair QR Code
            $qrCodeBase64 = $pixPayment['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
            $qrCode = $pixPayment['point_of_interaction']['transaction_data']['qr_code'] ?? null;

            return response()->json([
                'success' => true,
                'payment_id' => $pixPayment['id'] ?? null,
                'status' => $pixPayment['status'] ?? 'pending',
                'qr_code' => $qrCode,
                'qr_code_base64' => $qrCodeBase64,
                'point_of_interaction' => $pixPayment['point_of_interaction'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao processar pagamento PIX', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'preference_id' => $data['preferenceId'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar PIX: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cancel payment and delete room
    public function cancelPayment(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'preferenceId' => 'required|string',
        ]);

        try {
            // Buscar o payment associado à preference
            $payment = X1Payment::where('provider_preference_id', $data['preferenceId'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            $room = X1RoomInstance::findOrFail($payment->x1_room_id);

            // Verificar se é o host da sala
            if ($room->host_user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para cancelar esta sala.'
                ], 403);
            }

            // Marcar payment como cancelado
            $payment->update([
                'status' => 'cancelled',
            ]);

            // Cancelar pagamento no MercadoPago se já foi criado
            if ($payment->provider_payment_id) {
                try {
                    $mpService = app(MercadoPagoService::class);
                    $mpService->cancelPayment($payment->provider_payment_id);
                } catch (\Exception $e) {
                    \Log::warning('Falha ao cancelar pagamento no MercadoPago', [
                        'payment_id' => $payment->provider_payment_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Deletar a sala
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pagamento e sala cancelados com sucesso.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao cancelar pagamento', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'preference_id' => $data['preferenceId'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar salas X1 compatíveis para matching automático.
     * 
     * POST /api/x1/find-matches
     */
    public function findMatches(Request $request)
    {
        $user = $request->user();
        $minEntryAmount = $this->minEntryAmount();
        $maxEntryAmount = $this->maxEntryAmount();

        $data = $request->validate([
            'competitor_id' => 'nullable|integer',
            'competitor_group_id' => 'nullable|integer',
            'valor_entrada' => 'required|numeric|min:' . $minEntryAmount . '|max:' . $maxEntryAmount,
            'rodeio_id' => 'nullable|integer',
            'modalidade_id' => 'required|integer',
            'divisao' => 'nullable|string',
        ]);

        $modalidade = Modalidade::whereKey($data['modalidade_id'])->firstOrFail();
        
        // Verificar se X1 está pausado
        if ($modalidade->pausar_x1) {
            return response()->json(['message' => 'Criação de salas X1 pausada para esta modalidade.'], 422);
        }

        // Verificar se evento está finalizado
        if (!empty($data['rodeio_id'])) {
            $rodeio = \App\Models\Rodeio::find($data['rodeio_id']);
            if ($rodeio && $rodeio->status_transmissao === 'finalizado') {
                return response()->json(['message' => 'Evento finalizado.'], 422);
            }
        }

        $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);
        $competitorId = (int) ($data['competitor_id'] ?? 0);
        $groupId = (int) ($data['competitor_group_id'] ?? 0);

        if ($teamSize > 1 && $groupId <= 0) {
            return response()->json(['message' => 'Selecione um grupo válido.'], 422);
        }

        if ($teamSize === 1 && $competitorId <= 0) {
            return response()->json(['message' => 'Selecione um competidor válido.'], 422);
        }

        $x1Service = app(X1RoomService::class);
        
        // Buscar salas compatíveis
        $compatibleRooms = $x1Service->findCompatibleRooms(
            (float) $data['valor_entrada'],
            $data['rodeio_id'] ?? null,
            $data['modalidade_id'],
            $data['divisao'] ?? null,
            $competitorId ?: null,
            $groupId ?: null
        );

        $formattedRooms = $compatibleRooms->map(function ($room) use ($x1Service) {
            return $x1Service->getRoomDetails($room);
        });

        return response()->json([
            'matches' => $formattedRooms,
            'can_create_new' => true,
            'valor_entrada' => (float) $data['valor_entrada'],
        ]);
    }

    public function customRooms(Request $request)
    {
        $minEntryAmount = $this->minEntryAmount();
        $maxEntryAmount = $this->maxEntryAmount();

        $data = $request->validate([
            'competitor_id' => 'nullable|integer',
            'competitor_group_id' => 'nullable|integer',
            'rodeio_id' => 'nullable|integer',
            'modalidade_id' => 'required|integer',
            'divisao' => 'nullable|string',
            'min_custom_amount' => 'nullable|numeric|min:' . $minEntryAmount . '|max:' . $maxEntryAmount,
        ]);

        $modalidade = Modalidade::whereKey($data['modalidade_id'])->firstOrFail();
        if ($modalidade->pausar_x1) {
            return response()->json(['message' => 'Criação de salas X1 pausada para esta modalidade.'], 422);
        }

        if (!empty($data['rodeio_id'])) {
            $rodeio = \App\Models\Rodeio::find($data['rodeio_id']);
            if ($rodeio && $rodeio->status_transmissao === 'finalizado') {
                return response()->json(['message' => 'Evento finalizado.'], 422);
            }
        }

        $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);
        $competitorId = (int) ($data['competitor_id'] ?? 0);
        $groupId = (int) ($data['competitor_group_id'] ?? 0);

        if ($teamSize > 1 && $groupId <= 0) {
            return response()->json(['message' => 'Selecione um grupo válido.'], 422);
        }

        if ($teamSize === 1 && $competitorId <= 0) {
            return response()->json(['message' => 'Selecione um competidor válido.'], 422);
        }

        $x1Service = app(X1RoomService::class);
        $minCustomAmount = max(100.0, (float) ($data['min_custom_amount'] ?? 100.0));

        $rooms = $x1Service->findCustomEntryRooms(
            $minCustomAmount,
            $data['rodeio_id'] ?? null,
            $data['modalidade_id'],
            $data['divisao'] ?? null,
            $competitorId ?: null,
            $groupId ?: null
        );

        $formattedRooms = $rooms->map(function ($room) use ($x1Service) {
            return $x1Service->getRoomDetails($room);
        });

        return response()->json([
            'rooms' => $formattedRooms,
            'min_custom_amount' => $minCustomAmount,
        ]);
    }

    /**
     * Entrar em uma sala X1 específica (usado após escolha na lista de matches).
     * 
     * POST /api/x1/join-room
     */
    public function joinRoom(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'room_id' => 'required|integer',
            'competitor_id' => 'nullable|integer',
            'competitor_group_id' => 'nullable|integer',
        ]);

        $room = X1RoomInstance::findOrFail($data['room_id']);

        // Validações básicas
        if ($room->status !== 'open') {
            return response()->json(['message' => 'Sala não está aberta para entrada.'], 400);
        }

        if ($room->host_user_id === $user->id) {
            return response()->json(['message' => 'Você não pode entrar na própria sala.'], 400);
        }

        // Verificar se modalidade está pausada
        if ($room->modalidade && $room->modalidade->pausar_x1) {
            return response()->json(['message' => 'Salas X1 pausadas para esta modalidade.'], 422);
        }

        // Verificar se evento finalizou
        if ($room->rodeio_id) {
            $rodeio = \App\Models\Rodeio::find($room->rodeio_id);
            if ($rodeio && $rodeio->status_transmissao === 'finalizado') {
                return response()->json(['message' => 'Evento finalizado.'], 422);
            }
        }

        // Delegar para o método join existente
        $joinRequest = $request->duplicate(
            $request->query->all(),
            [
                'competitor_id' => $data['competitor_id'] ?? null,
                'competitor_group_id' => $data['competitor_group_id'] ?? null,
            ]
        );
        $joinRequest->setUserResolver(function () use ($user) {
            return $user;
        });

        return $this->join($joinRequest, $room->id);
    }

}
