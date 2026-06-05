<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BotUser;
use App\Models\X1RoomInstance;
use App\Models\X1Participant;
use App\Models\X1Payment;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\Rodeio;
use App\Models\Modalidade;
use App\Models\Competitor;
use App\Models\ModalidadeCompetitorGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * SISTEMA DE BOTS - REI DO RODEIO
 * 
 * OBJETIVO: Popular o site com atividade falsa para parecer movimentado
 * 
 * REGRAS IMPORTANTES:
 * 1. Bots são VISÍVEIS para usuários reais (aparecem em listas, rankings, etc)
 * 2. Usuários reais NÃO podem interagir com bots:
 *    - Salas X1 de bots são criadas com status 'in_progress' (já fechadas)
 *    - Ligas Fantasy de bots estão visíveis mas não permitem entrada de reais
 * 3. Bots servem apenas como "vitrine" de atividade
 * 4. Este sistema é CONFIDENCIAL - não mencionar para usuários
 */
class BotManagementController extends Controller
{
    /**
     * Interface principal de gerenciamento de bots
     */
    public function index()
    {
        $pageTitle = 'Gerenciamento de Bots';

        // Estatísticas atuais
        $stats = [
            'users' => User::where('is_bot', true)->count(),
            'x1_rooms' => X1RoomInstance::where('is_bot_room', true)->count(),
            'fantasy_leagues' => FantasyLeague::where('is_bot_league', true)->count(),
            'fantasy_teams' => FantasyTeam::whereHas('user', function($q) {
                $q->where('is_bot', true);
            })->count(),
        ];

        // Últimas criações (log de atividade)
        $recentActivity = $this->getRecentActivity();

        // Rodeios disponíveis (todos, não só ativos - permite testes)
        $rodeios = Rodeio::orderBy('start', 'desc')->get();

        // Verificar arquivo de bots
        $botsFile = storage_path('app/bots.json');
        $botsAvailable = 0;
        if (file_exists($botsFile)) {
            $botsData = json_decode(file_get_contents($botsFile), true);
            $botsAvailable = is_array($botsData) ? count($botsData) : 0;
        }

        return view('admin.users.bots', compact('pageTitle', 'stats', 'recentActivity', 'rodeios', 'botsAvailable'));
    }

    /**
     * Gerar salas X1 com bots (seguindo fluxo real de pagamento)
     */
    public function generateX1(Request $request)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1|max:100',
                'rodeio_id' => 'required|exists:rodeios,id',
                'value_mode_x1' => 'required|in:auto,manual',
                'fixed_value_x1' => 'required_if:value_mode_x1,manual|nullable|integer|min:20|max:5000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação: ' . collect($e->errors())->flatten()->first()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $quantity = $request->quantity;
            $rodeioId = $request->rodeio_id;
            $valueMode = $request->value_mode_x1;
            $fixedValue = $request->fixed_value_x1;

            // Carregar bots disponíveis
            $bots = $this->loadBotsData();
            if (empty($bots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum bot disponível no arquivo bots.json'
                ], 400);
            }

            // Obter modalidades do rodeio (qualquer status - permite testes)
            $modalidades = Modalidade::where('rodeio_id', $rodeioId)->get();

            if ($modalidades->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma modalidade encontrada para este rodeio. Cadastre modalidades primeiro.'
                ], 400);
            }

            $x1Service = app(\App\Services\X1RoomService::class);
            $created = 0;
            $minMinutesApart = 15; // Mínimo de 15 minutos entre cada sala
            $maxHoursBack = 72; // Máximo de 72 horas (3 dias) para trás
            $attempts = 0;
            $maxAttempts = $quantity * 3; // Máximo de tentativas para evitar loop infinito

            while ($created < $quantity && $attempts < $maxAttempts) {
                $attempts++;
                
                // Calcular timestamp aleatório
                // Cada sala tem pelo menos 15 minutos de diferença da anterior
                $minutesBack = ($created * $minMinutesApart) + rand(0, 60); // +0-60min extra aleatório
                $maxMinutesBack = $maxHoursBack * 60;
                if ($minutesBack > $maxMinutesBack) {
                    $minutesBack = rand($minMinutesApart, $maxMinutesBack);
                }
                $createdAt = now()->subMinutes($minutesBack);
                
                // Selecionar modalidade aleatória
                $modalidade = $modalidades->random();
                $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);

                // Obter competidores ou grupos da modalidade
                if ($teamSize > 1) {
                    $items = ModalidadeCompetitorGroup::where('modalidade_id', $modalidade->id)->get();
                } else {
                    $items = $modalidade->competitors;
                }

                if ($items->count() < 2) continue;

                // Selecionar 2 itens diferentes
                $item1 = $items->random();
                $item2 = $items->where('id', '!=', $item1->id)->random();

                // Criar 2 usuários bot
                $hostBot = $this->createBotUser($bots);
                $opponentBot = $this->createBotUser($bots);

                // 🎯 Valor: manual ou automático
                if ($valueMode === 'manual' && $fixedValue) {
                    $valor = (int) $fixedValue;
                } else {
                    // Valores aleatórios entre R$ 100 e R$ 1000 (múltiplos de 50)
                    $multiplo = rand(2, 20); // 2*50=100 até 20*50=1000
                    $valor = $multiplo * 50;
                }
                
                // 🎯 Taxas realistas variadas (não usuários premium)
                $taxasRealistas = [20, 15, 12, 10, 8, 7];
                $feePercent = $taxasRealistas[array_rand($taxasRealistas)];
                
                $prizeTotal = $x1Service->calculatePrizeTotal($valor, $feePercent);

                // Determinar IDs baseado em competidor ou grupo
                if ($teamSize > 1) {
                    // Para grupos: pegar primeiro membro como competitor_id principal
                    $firstMember1 = DB::table('modalidade_competitor_group_members')
                        ->where('group_id', $item1->id)
                        ->orderBy('id', 'asc')
                        ->first();
                    $firstMember2 = DB::table('modalidade_competitor_group_members')
                        ->where('group_id', $item2->id)
                        ->orderBy('id', 'asc')
                        ->first();
                    
                    // Se grupo não tem membros, pular esta sala
                    if (!$firstMember1 || !$firstMember2) {
                        continue;
                    }
                    
                    $competitorId1 = $firstMember1->competitor_id;
                    $competitorGroupId1 = $item1->id;
                    $competitorId2 = $firstMember2->competitor_id;
                    $competitorGroupId2 = $item2->id;
                    
                    $competitorEscolhidoCriador = $firstMember1->competitor_id;
                    $competitorEscolhidoOponente = $firstMember2->competitor_id;
                } else {
                    $competitorId1 = $item1->id;
                    $competitorGroupId1 = null;
                    $competitorId2 = $item2->id;
                    $competitorGroupId2 = null;
                    
                    $competitorEscolhidoCriador = $item1->id;
                    $competitorEscolhidoOponente = $item2->id;
                }

                // 1️⃣ CRIAR SALA (como sala real - status pending_payment primeiro)
                // Verificar se o host é premium
                $isHostPremium = $hostBot->isPremium();
                
                $room = X1RoomInstance::create([
                    'bot_criador_id' => $hostBot->id, // Bot na tabela bot_users
                    'criador_id' => null, // NULL porque é bot
                    'competitor_escolhido_criador' => $competitorEscolhidoCriador, // FK obrigatória
                    'host_user_id' => null, // NULL porque é bot
                    'rodeio_id' => $rodeioId,
                    'modalidade_id' => $modalidade->id,
                    'divisao' => $modalidade->is_classificatoria ? null : collect(['Novato', 'Amador', 'Profissional', 'Elite'])->random(),
                    'competitor_id' => $competitorId1,
                    'competitor_group_id' => $competitorGroupId1,
                    'name' => 'Sala X1 #' . now()->format('His'),
                    'valor_entrada' => $valor,
                    'is_private' => false,
                    'access_code' => null,
                    'fee_percent' => $isHostPremium ? 15 : $feePercent, // Premium tem taxa reduzida
                    'is_premium_room' => $isHostPremium,
                    'prize_total' => $prizeTotal,
                    'currency' => 'BRL',
                    'status' => 'pending_payment',
                    'is_bot_room' => true,
                    'expires_at' => now()->addDays(7),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // 2️⃣ NÃO CRIAR PAGAMENTO/PARTICIPANT PARA BOTS
                // Bots não precisam de payment tracking, apenas a sala em si

                // 3️⃣ ABRIR SALA DIRETO (bots pagam instantaneamente)
                $hostPaidAt = $createdAt->copy()->addSeconds(rand(30, 120));
                $room->update([
                    'status' => 'open',
                    'host_paid_at' => $hostPaidAt,
                    'updated_at' => $hostPaidAt,
                ]);

                // 4️⃣ ATUALIZAR SALA COM OPONENTE (bot)
                $opponentJoinedAt = $hostPaidAt->copy()->addMinutes(rand(1, 10));
                $room->update([
                    'bot_oponente_id' => $opponentBot->id, // Bot na tabela bot_users
                    'oponente_id' => null, // NULL porque é bot
                    'competitor_escolhido_oponente' => $competitorEscolhidoOponente, // FK obrigatória
                ]);

                // 5️⃣ NÃO CRIAR PAGAMENTO/PARTICIPANT PARA OPONENTE BOT
                // Bots não precisam de payment tracking

                // 6️⃣ SALA COMPLETA - INICIA
                $room->update([
                    'status' => 'in_progress',
                    'updated_at' => $opponentJoinedAt,
                ]);

                $created++;
            }

            DB::commit();

            $message = "{$created} salas X1 criadas com sucesso!";
            if ($created < $quantity) {
                $message .= " (Solicitadas: {$quantity}. Algumas modalidades não tinham competidores suficientes.)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'created' => $created,
                    'new_total' => X1RoomInstance::where('is_bot_room', true)->count(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar salas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar ligas Fantasy com bots
     */
    public function generateFantasy(Request $request)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1|max:50',
                'rodeio_id' => 'required|exists:rodeios,id',
                'teams_per_league' => 'nullable|integer|min:10|max:500',
                'value_mode_fantasy' => 'required|in:auto,manual',
                'fixed_value_fantasy' => 'required_if:value_mode_fantasy,manual|nullable|integer|in:20,50,100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação: ' . collect($e->errors())->flatten()->first()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $quantity = $request->quantity;
            $rodeioId = $request->rodeio_id;
            $teamsPerLeague = $request->teams_per_league ?? rand(30, 50);
            $valueMode = $request->value_mode_fantasy;
            $fixedValue = $request->fixed_value_fantasy;
            
            \Log::info("🎯 Gerando Fantasy - Modo: {$valueMode}, Valor Fixo: " . ($fixedValue ?? 'null'));

            // Carregar bots disponíveis
            $bots = $this->loadBotsData();
            if (empty($bots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum bot disponível no arquivo bots.json'
                ], 400);
            }

            // Obter modalidades do rodeio (qualquer status - permite testes)
            $modalidades = Modalidade::where('rodeio_id', $rodeioId)->get();

            if ($modalidades->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma modalidade encontrada para este rodeio. Cadastre modalidades primeiro.'
                ], 400);
            }

            $created = 0;
            $totalTeams = 0;
            $attempts = 0;
            $maxAttempts = $quantity * 3; // Máximo de tentativas para evitar loop infinito
            
            // ✅ Configs padrão de salary cap (usadas em todas as ligas)
            $defaultSalaryCap = 1000;
            $defaultBasePrice = 150;
            $defaultPricePerPick = 10;
            $defaultMaxPrice = 300;

            while ($created < $quantity && $attempts < $maxAttempts) {
                $attempts++;
                
                // Selecionar modalidade aleatória
                $modalidade = $modalidades->random();

                // Verificar se modalidade tem competidores suficientes
                $competitors = $modalidade->competitors;
                if ($competitors->count() < 4) {
                    \Log::warning("⚠️ Modalidade {$modalidade->id} tem apenas {$competitors->count()} competidores (mínimo: 4)");
                    continue;
                }
                
                // ✅ VALIDAR SE É POSSÍVEL MONTAR TIMES
                // Para bots, simular preços iniciais entre 100-250
                $competitorsWithPrice = $competitors->map(function($c) {
                    $c->fantasy_price = rand(100, 250); // Preço inicial simulado
                    return $c;
                });
                
                if ($competitorsWithPrice->count() < 4) {
                    \Log::warning("⚠️ Modalidade {$modalidade->id} tem apenas {$competitorsWithPrice->count()} competidores");
                    continue;
                }
                
                // Verificar se o time mais barato possível cabe no budget
                $cheapest4 = $competitorsWithPrice->sortBy('fantasy_price')->take(4)->sum('fantasy_price');
                if ($cheapest4 > $defaultSalaryCap) {
                    \Log::warning("⚠️ Modalidade {$modalidade->id}: impossível montar time! Mínimo: {$cheapest4} > Budget: {$defaultSalaryCap}");
                    continue;
                }
                
                // Atualizar referência para usar competidores com preço calculado
                $competitors = $competitorsWithPrice;

                // 🎯 Valor: manual ou automático
                if ($valueMode === 'manual' && $fixedValue) {
                    $entryFee = (int) $fixedValue;
                    \Log::info("✅ Usando valor MANUAL: R$ {$entryFee}");
                } else {
                    $entryFee = rand(4, 8) * 5; // R$ 20 a R$ 40
                    \Log::info("🎲 Usando valor AUTOMÁTICO: R$ {$entryFee}");
                }
                
                $maxUsers = $teamsPerLeague;
                $houseCutPercent = 30; // 30% padrão
                
                // Calcular prêmio total (entry_fee * max_users * (1 - house_cut))
                $totalPrize = $entryFee * $maxUsers * ((100 - $houseCutPercent) / 100);
                
                // Nomes e categorias variadas para parecer real
                $leagueNames = [
                    'Liga Elite',
                    'Copa dos Campeões',
                    'Torneio Rei do Rodeio',
                    'Desafio Nacional',
                    'Liga Profissional',
                    'Campeonato Aberto',
                    'Arena Premium',
                    'Batalha dos Peões',
                    'Liga Masters',
                    'Copa Ouro'
                ];
                
                $categories = [
                    'ouro',
                    'prata',
                    'bronze',
                    'elite',
                    'profissional'
                ];
                
                // Prazo de inscrição: entre 3 a 7 dias
                $registrationDeadline = now()->addDays(rand(3, 7))->addHours(rand(1, 23));
                
                // Criar liga
                // Status 'in_progress' = liga ativa e visível
                // is_bot_league = true permite filtrar no admin
                // Ligas de bots aparecem normalmente para usuários
                $league = FantasyLeague::create([
                    'rodeio_id' => $rodeioId,
                    'modalidade_id' => $modalidade->id,
                    'name' => collect($leagueNames)->random(),
                    'category' => collect($categories)->random(),
                    'price' => $entryFee,
                    'house_cut_percent' => $houseCutPercent,
                    'total_prize' => $totalPrize,
                    'max_users' => $maxUsers,
                    'registration_deadline' => $registrationDeadline,
                    'is_active' => true,
                    'is_bot_league' => true,
                ]);

                // Criar times para a liga
                // Vamos randomizar o timestamp de criação para simular que times foram criados ao longo do tempo
                $baseTimestamp = now()->subHours(rand(1, 48));
                
                \Log::info("🏆 Criando {$teamsPerLeague} times para liga {$league->id}...");
                $teamsCreatedThisLeague = 0;
                
                for ($t = 0; $t < $teamsPerLeague; $t++) {
                    try {
                        $botUser = $this->createBotUser($bots);

                    // ✅ SELECIONAR 4 COMPETIDORES QUE CAIBAM NO BUDGET DE 1000
                    $budget = $league->salary_cap ?? $defaultSalaryCap;
                    $selectedCompetitors = collect();
                    $maxAttempts = 50; // Limitar tentativas
                    $attempt = 0;
                    
                    while ($selectedCompetitors->count() < 4 && $attempt < $maxAttempts) {
                        $attempt++;
                        
                        // Resetar seleção
                        $selectedCompetitors = collect();
                        $remainingBudget = $budget;
                        $availableCompetitors = $competitors->shuffle();
                        
                        // Selecionar 4 competidores respeitando budget
                        foreach ($availableCompetitors as $competitor) {
                            if ($selectedCompetitors->count() >= 4) break;
                            
                            if ($competitor->fantasy_price <= $remainingBudget) {
                                // Se é o último competidor, garantir que caiba
                                if ($selectedCompetitors->count() == 3) {
                                    // Aceitar qualquer um que caiba
                                    $selectedCompetitors->push($competitor);
                                    break;
                                } else {
                                    $selectedCompetitors->push($competitor);
                                    $remainingBudget -= $competitor->fantasy_price;
                                }
                            }
                        }
                    }
                    
                    // Se não conseguiu selecionar 4, pular este time
                    if ($selectedCompetitors->count() < 4) {
                        \Log::warning("⚠️ Não conseguiu selecionar 4 competidores com budget {$budget} na tentativa {$t}");
                        continue;
                    }
                    
                    // Calcular budget usado pelo time
                    $budgetUsed = $selectedCompetitors->sum('fantasy_price');
                    
                    $captain = $selectedCompetitors->first();

                    // Gerar pontos aleatórios (0-150 pontos por enquanto, sem eventos reais)
                    // Quando o admin pontuar competidores, os pontos serão atualizados
                    $totalPoints = rand(0, 150);
                    
                    // Timestamp de criação escalonado (cada time alguns minutos depois do anterior)
                    $createdAt = $baseTimestamp->copy()->addMinutes($t * rand(5, 30));

                    $team = FantasyTeam::create([
                        'bot_user_id' => $botUser->id, // Bot na tabela bot_users
                        'user_id' => null, // NULL porque é bot
                        'fantasy_league_id' => $league->id,
                        'team_name' => $botUser->username, // Nome do time igual ao username para realismo
                        'budget' => $budgetUsed, // Budget realmente usado
                        'total_points' => $totalPoints,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    // Adicionar competidores ao time
                    foreach ($selectedCompetitors as $competitor) {
                        $team->competitorsRelation()->attach($competitor->id, [
                            'is_captain' => $competitor->id === $captain->id,
                            'role' => 'titular',
                            'multiplier' => $competitor->id === $captain->id ? 1.5 : 1,
                        ]);
                    }

                    $totalTeams++;
                    $teamsCreatedThisLeague++;
                    
                    } catch (\Exception $e) {
                        \Log::error("❌ Erro ao criar time {$t} da liga {$league->id}: " . $e->getMessage());
                        // Não interrompe o loop, continua tentando criar outros times
                    }
                }
                
                \Log::info("✅ Liga {$league->id} criada com {$teamsCreatedThisLeague} times");

                // Criar snapshot inicial de ranking
                // Buscar todos os times da liga ordenados por pontos (se houver) ou created_at
                $teams = \App\Models\FantasyTeam::where('fantasy_league_id', $league->id)
                    ->with(['botUser'])
                    ->orderByDesc('total_points')
                    ->orderBy('created_at')
                    ->get();

                $rankingItems = [];
                $position = 1;
                foreach ($teams as $team) {
                    $botUser = $team->botUser;
                    $rankingItems[] = [
                        'pos' => $position++,
                        'name' => $team->team_name ?? 'Time Bot',
                        'team_name' => $team->team_name ?? 'Time Bot',
                        'points' => (float) $team->total_points,
                        'team_id' => $team->id,
                        'user_id' => null,
                        'bot_user_id' => $botUser ? $botUser->id : null,
                        'user_name' => $botUser ? $botUser->username : 'Bot',
                        'user_foto' => null,
                        'is_premium' => $botUser ? $botUser->isPremium() : false,
                    ];
                }

                $snapshotPayload = [
                    'items' => $rankingItems,
                    'generated_at' => now()->toIso8601String(),
                    'league_id' => $league->id,
                    'fantasy_event_id' => $rodeioId,
                ];

                // Salvar snapshot no banco
                \DB::table('fantasy_league_ranking_snapshots')->updateOrInsert(
                    [
                        'fantasy_league_id' => $league->id,
                        'type' => 'full'
                    ],
                    [
                        'payload' => json_encode($snapshotPayload),
                        'generated_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                // Também criar snapshot top30
                $top30Items = array_slice($rankingItems, 0, 30);
                $snapshotPayloadTop30 = [
                    'items' => $top30Items,
                    'generated_at' => now()->toIso8601String(),
                    'league_id' => $league->id,
                    'fantasy_event_id' => $rodeioId,
                ];

                \DB::table('fantasy_league_ranking_snapshots')->updateOrInsert(
                    [
                        'fantasy_league_id' => $league->id,
                        'type' => 'top30'
                    ],
                    [
                        'payload' => json_encode($snapshotPayloadTop30),
                        'generated_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $created++;
            }

            DB::commit();

            $message = "{$created} ligas Fantasy criadas com {$totalTeams} times!";
            if ($created < $quantity) {
                $message .= " (Solicitadas: {$quantity} ligas. Algumas modalidades não tinham competidores suficientes.)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'leagues_created' => $created,
                    'teams_created' => $totalTeams,
                    'new_total_leagues' => FantasyLeague::where('is_bot_league', true)->count(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar ligas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar todos os bots
     */
    public function clearAll(Request $request)
    {
        try {
            DB::beginTransaction();

            $type = $request->input('type', 'all'); // all, x1, fantasy, bot_users

            $deleted = [
                'bot_users' => 0,
                'x1_rooms' => 0,
                'fantasy_leagues' => 0,
                'fantasy_teams' => 0,
            ];

            if (in_array($type, ['all', 'x1'])) {
                $deleted['x1_rooms'] = X1RoomInstance::where('is_bot_room', true)->delete();
            }

            if (in_array($type, ['all', 'fantasy'])) {
                // 1. Deletar competidores dos times (FantasyTeamCompetitor)
                DB::table('fantasy_team_competitors')
                    ->whereIn('fantasy_team_id', function($query) {
                        $query->select('id')
                            ->from('fantasy_teams')
                            ->whereNotNull('bot_user_id');
                    })
                    ->delete();

                // 2. Deletar times de bot
                $deleted['fantasy_teams'] = FantasyTeam::whereNotNull('bot_user_id')->delete();

                // 3. Deletar as ligas bot
                $deleted['fantasy_leagues'] = FantasyLeague::where('is_bot_league', true)->delete();
            }

            if ($type === 'all') {
                $deleted['bot_users'] = BotUser::query()->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bots removidos com sucesso!',
                'data' => $deleted
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar bots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar usuário bot a partir dos dados carregados
     */
    private function createBotUser($botsData)
    {
        static $usedIndices = [];
        
        // Selecionar bot aleatório não usado
        $availableIndices = array_diff(array_keys($botsData), $usedIndices);
        if (empty($availableIndices)) {
            // Se todos foram usados, resetar
            $usedIndices = [];
            $availableIndices = array_keys($botsData);
        }
        
        $index = $availableIndices[array_rand($availableIndices)];
        $usedIndices[] = $index;
        
        $botData = $botsData[$index];
        
        // Separar nome completo
        $nomePartes = explode(' ', $botData['nome']);
        $primeiroNome = $nomePartes[0];
        $ultimoNome = end($nomePartes);
        
        // Gerar username estilo FIFA (CamelCase, sem números)
        $username = self::generateFifaUsername($primeiroNome, $ultimoNome);
        
        // Email natural
        $email = strtolower($username) . '@bot.local';

        // Criar usuário bot (na tabela bot_users, não users!)
        $botUser = BotUser::create([
            'firstname' => $primeiroNome,
            'lastname' => implode(' ', array_slice($nomePartes, 1)),
            'username' => $username,
            'email' => $email,
            'mobile' => preg_replace('/[^0-9]/', '', $botData['celular'] ?? ''),
            'cpf' => preg_replace('/[^0-9]/', '', $botData['cpf']),
            'referred_by_id' => 1, // Afiliado do admin (bash)
        ]);
        
        // 70% de chance de ser premium
        if (rand(1, 100) <= 70) {
            $botUser->update([
                'is_premium' => true,
                'premium_until' => now()->addDays(rand(30, 90)),
            ]);
        }
        
        return $botUser;
    }

    /**
     * Carregar dados de bots do arquivo JSON
     */
    /**
     * Gerar username estilo FIFA (CamelCase, sem números)
     * Formatos variados: PedroSilva, Lucas_Costa, MarcosJr, ThiagoPM
     */
    private static function generateFifaUsername(string $primeiro, string $ultimo): string
    {
        $primeiro = ucfirst(Str::ascii(mb_strtolower($primeiro)));
        $ultimo = ucfirst(Str::ascii(mb_strtolower($ultimo)));

        // Sufixos naturais para variação
        $suffixes = ['Jr', 'Neto', 'FC', 'BR', 'Pro', 'GG', 'RR', 'XD', 'CR', 'FX'];

        // Formatos variados (pesos: CamelCase mais comum)
        $formats = [
            fn() => $primeiro . $ultimo,                                           // PedroSilva
            fn() => $primeiro . '_' . $ultimo,                                     // Pedro_Silva
            fn() => $primeiro . '.' . $ultimo,                                     // Pedro.Silva
            fn() => $primeiro . strtoupper(substr($ultimo, 0, 1)),                 // PedroS
            fn() => strtoupper(substr($primeiro, 0, 1)) . $ultimo,                // PSilva
            fn() => $primeiro . $suffixes[array_rand($suffixes)],                  // PedroJr
            fn() => strtolower($primeiro) . $ultimo,                               // pedroSilva
            fn() => $primeiro . strtoupper(substr($primeiro, 0, 1) . substr($ultimo, 0, 1)), // PedroPJ
        ];

        // Tentar até 20 vezes com formatos aleatórios
        $tentativas = 0;
        do {
            $format = $formats[array_rand($formats)];
            $username = $format();
            $tentativas++;
        } while (BotUser::where('username', $username)->exists() && $tentativas < 20);

        // Fallback: adicionar letra aleatória no final
        if (BotUser::where('username', $username)->exists()) {
            $username = $primeiro . $ultimo . chr(rand(65, 90)); // PedroSilvaK
        }

        return $username;
    }

    /**
     * Carregar dados de bots de TODOS os arquivos JSON
     */
    private function loadBotsData()
    {
        $storagePath = storage_path('app');
        $allBots = [];
        
        // Buscar todos arquivos JSON na pasta
        $files = glob($storagePath . '/*.json');
        
        if (empty($files)) {
            \Log::warning('⚠️ Nenhum arquivo JSON encontrado em storage/app');
            return [];
        }
        
        foreach ($files as $file) {
            $fileName = basename($file);
            
            try {
                $content = file_get_contents($file);
                $data = json_decode($content, true);
                
                if (is_array($data)) {
                    $count = count($data);
                    $allBots = array_merge($allBots, $data);
                    \Log::info("✅ Carregados {$count} bots de {$fileName}");
                } else {
                    \Log::warning("⚠️ Formato inválido em {$fileName}");
                }
            } catch (\Exception $e) {
                \Log::error("❌ Erro ao ler {$fileName}: " . $e->getMessage());
            }
        }
        
        \Log::info("🤖 Total de bots carregados: " . count($allBots) . " de " . count($files) . " arquivos");
        
        return $allBots;
    }

    /**
     * Obter atividades recentes
     */
    private function getRecentActivity()
    {
        $activity = [];

        // Últimas salas X1 criadas
        $recentX1 = X1RoomInstance::where('is_bot_room', true)
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentX1 as $room) {
            $activity[] = [
                'date' => $room->created_at->format('d/m H:i'),
                'description' => '1 sala X1 criada',
                'type' => 'x1',
            ];
        }

        // Últimas ligas Fantasy criadas
        $recentFantasy = FantasyLeague::where('is_bot_league', true)
            ->latest()
            ->take(3)
            ->get();

        foreach ($recentFantasy as $league) {
            $teamsCount = $league->teams()->whereHas('user', function($q) {
                $q->where('is_bot', true);
            })->count();
            
            $activity[] = [
                'date' => $league->created_at->format('d/m H:i'),
                'description' => "1 liga Fantasy ({$teamsCount} bots)",
                'type' => 'fantasy',
            ];
        }

        // Ordenar por data
        usort($activity, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return array_slice($activity, 0, 5);
    }

    /**
     * Upload de novo arquivo bots.json
     */
    public function uploadBotsFile(Request $request)
    {
        $request->validate([
            'bots_file' => 'required|file|mimes:json|max:10240',
        ]);

        try {
            $file = $request->file('bots_file');
            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true);

            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo JSON inválido'
                ], 400);
            }

            // Salvar arquivo
            $path = storage_path('app/bots.json');
            file_put_contents($path, $content);

            return response()->json([
                'success' => true,
                'message' => count($data) . ' bots carregados com sucesso!',
                'data' => [
                    'count' => count($data),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter ligas Fantasy disponíveis para população de bots
     */
    public function getAvailableLeagues()
    {
        try {
            $leagues = FantasyLeague::query()
                ->where('is_active', true)
                ->with(['rodeio', 'modalidade:id,nome']) // rodeio doesn't have 'nome' column; eager-load full rodeio model and only modalidade nome
                ->withCount(['teams as total_teams'])
                ->withCount(['teams as bot_teams' => function ($q) {
                    $q->whereNotNull('bot_user_id');
                }])
                ->withCount(['teams as real_teams' => function ($q) {
                    $q->whereNull('bot_user_id')->whereNotNull('user_id');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($league) {
                    return [
                        'id' => $league->id,
                        'name' => $league->name,
                        'rodeio' => $league->rodeio?->nome ?? 'Sem rodeio',
                        'modalidade' => $league->modalidade?->nome ?? 'Sem modalidade',
                        'total_teams' => $league->total_teams,
                        'bot_teams' => $league->bot_teams,
                        'real_teams' => $league->real_teams,
                        'max_users' => $league->max_users,
                        'is_bot_league' => (bool) $league->is_bot_league,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $leagues,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar ligas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Popular uma liga Fantasy existente com bots
     * Adiciona bots até atingir o número mínimo especificado
     */
    public function populateLeagueWithBots(Request $request)
    {
        try {
            $request->validate([
                'league_id' => 'required|exists:fantasy_leagues,id',
                'min_bots' => 'required|integer|min:10|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação: ' . collect($e->errors())->flatten()->first()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $leagueId = $request->league_id;
            $minBots = $request->min_bots;

            $league = FantasyLeague::with('modalidade')->find($leagueId);
            if (!$league) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liga não encontrada'
                ], 404);
            }

            if (!$league->modalidade_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liga não tem modalidade vinculada'
                ], 422);
            }

            // Contar bots atuais na liga
            $currentBots = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNotNull('bot_user_id')
                ->count();

            $botsToCreate = max(0, $minBots - $currentBots);

            if ($botsToCreate === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Liga já possui {$currentBots} bots (mínimo: {$minBots})",
                    'data' => [
                        'created' => 0,
                        'total_bots' => $currentBots,
                    ]
                ]);
            }

            // Verificar limite da liga
            $totalTeams = FantasyTeam::where('fantasy_league_id', $leagueId)->count();
            $maxUsers = (int) ($league->max_users ?? 500);
            $availableSlots = $maxUsers - $totalTeams;

            if ($availableSlots <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Liga está cheia ({$totalTeams}/{$maxUsers} participantes)"
                ], 422);
            }

            $botsToCreate = min($botsToCreate, $availableSlots);

            // Carregar bots disponíveis
            $bots = $this->loadBotsData();
            if (empty($bots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum bot disponível no arquivo bots.json'
                ], 400);
            }

            // Obter competidores da modalidade
            $modalidade = $league->modalidade;
            $competitors = $modalidade->competitors;

            if ($competitors->count() < 4) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modalidade tem menos de 4 competidores'
                ], 422);
            }

            // Configs de salary cap
            $defaultSalaryCap = $league->salary_cap ?? 1000;
            $defaultBasePrice = $league->base_price ?? 150;
            $defaultPricePerPick = $league->price_per_pick ?? 10;
            $defaultMaxPrice = $league->max_price ?? 300;

            // Atribuir preços aos competidores
            $competitorsWithPrice = $competitors->map(function($c) {
                $c->fantasy_price = rand(100, 250);
                return $c;
            });

            $created = 0;
            $baseTimestamp = now()->subHours(rand(1, 24));

            for ($t = 0; $t < $botsToCreate; $t++) {
                try {
                    $botUser = $this->createBotUser($bots);

                    // Selecionar 4 competidores dentro do budget
                    $budget = $defaultSalaryCap;
                    $selectedCompetitors = collect();
                    $maxAttempts = 50;
                    $attempt = 0;

                    while ($selectedCompetitors->count() < 4 && $attempt < $maxAttempts) {
                        $attempt++;
                        $selectedCompetitors = collect();
                        $remainingBudget = $budget;
                        $availableCompetitors = $competitorsWithPrice->shuffle();

                        foreach ($availableCompetitors as $competitor) {
                            if ($selectedCompetitors->count() >= 4) break;

                            if ($competitor->fantasy_price <= $remainingBudget) {
                                if ($selectedCompetitors->count() == 3) {
                                    $selectedCompetitors->push($competitor);
                                    break;
                                } else {
                                    $selectedCompetitors->push($competitor);
                                    $remainingBudget -= $competitor->fantasy_price;
                                }
                            }
                        }
                    }

                    if ($selectedCompetitors->count() < 4) {
                        continue;
                    }

                    $budgetUsed = $selectedCompetitors->sum('fantasy_price');
                    $captain = $selectedCompetitors->first();
                    $createdAt = $baseTimestamp->copy()->addMinutes($t * rand(5, 30));

                    $team = FantasyTeam::create([
                        'bot_user_id' => $botUser->id,
                        'user_id' => null,
                        'fantasy_league_id' => $league->id,
                        'team_name' => $botUser->username,
                        'budget' => $budgetUsed,
                        'total_points' => rand(0, 100),
                        'is_active' => true,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    foreach ($selectedCompetitors as $competitor) {
                        $team->competitorsRelation()->attach($competitor->id, [
                            'is_captain' => $competitor->id === $captain->id,
                            'role' => 'titular',
                            'multiplier' => $competitor->id === $captain->id ? 1.5 : 1,
                        ]);
                    }

                    $created++;

                } catch (\Exception $e) {
                    \Log::error("Erro ao criar bot {$t} na liga {$leagueId}: " . $e->getMessage());
                }
            }

            DB::commit();

            $totalBots = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNotNull('bot_user_id')
                ->count();

            return response()->json([
                'success' => true,
                'message' => "{$created} bots adicionados à liga '{$league->name}'!",
                'data' => [
                    'created' => $created,
                    'total_bots' => $totalBots,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao popular liga: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover bots de uma liga Fantasy existente
     * Pode remover uma quantidade específica ou todos os bots
     */
    public function removeBotsFromLeague(Request $request)
    {
        try {
            $request->validate([
                'league_id' => 'required|exists:fantasy_leagues,id',
                'quantity' => 'nullable|integer|min:1|max:500',
                'remove_all' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação: ' . collect($e->errors())->flatten()->first()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $leagueId = $request->league_id;
            $removeAll = $request->remove_all === '1';
            $quantity = (int) $request->quantity;

            $league = FantasyLeague::find($leagueId);
            if (!$league) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liga não encontrada'
                ], 404);
            }

            // Contar bots atuais na liga
            $currentBots = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNotNull('bot_user_id')
                ->count();

            if ($currentBots === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Esta liga não possui bots para remover.',
                    'data' => [
                        'removed' => 0,
                        'remaining_bots' => 0,
                    ]
                ]);
            }

            $botsToRemove = $removeAll ? $currentBots : min($quantity, $currentBots);

            if ($botsToRemove <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe uma quantidade válida para remover.'
                ], 422);
            }

            // Selecionar bots para remover (os com menos pontos primeiro)
            $botsToDelete = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNotNull('bot_user_id')
                ->orderBy('total_points', 'asc')
                ->orderBy('created_at', 'desc')
                ->limit($botsToRemove)
                ->get();

            $removed = 0;
            foreach ($botsToDelete as $botTeam) {
                // Deletar competidores do time
                DB::table('fantasy_team_competitors')
                    ->where('fantasy_team_id', $botTeam->id)
                    ->delete();

                // Registrar remoção no log
                if (Schema::hasTable('fantasy_team_bot_removal_log')) {
                    DB::table('fantasy_team_bot_removal_log')->insert([
                        'fantasy_league_id' => $leagueId,
                        'fantasy_team_id' => $botTeam->id,
                        'bot_user_id' => $botTeam->bot_user_id,
                        'removed_at' => now(),
                        'reason' => $removeAll
                            ? 'Remoção manual: todos os bots pelo admin'
                            : "Remoção manual: {$botsToRemove} bots pelo admin",
                    ]);
                }

                // Deletar o time
                $botTeam->forceDelete();
                $removed++;
            }

            // Limpar ranking snapshots (payload JSON, não tem fantasy_team_id)
            DB::table('fantasy_league_ranking_snapshots')
                ->where('fantasy_league_id', $leagueId)
                ->delete();

            // Contar bots restantes
            $remainingBots = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNotNull('bot_user_id')
                ->count();

            DB::commit();

            \Log::info("🗑️ Liga {$leagueId}: {$removed} bots removidos manualmente pelo admin. Restantes: {$remainingBots}");

            return response()->json([
                'success' => true,
                'message' => $removeAll
                    ? "Todos os {$removed} bots foram removidos da liga."
                    : "{$removed} bot(s) removido(s) da liga.",
                'data' => [
                    'removed' => $removed,
                    'remaining_bots' => $remainingBots,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erro ao remover bots da liga: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover bots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover bots de uma liga quando usuários reais entram
     * Proporção: 1 bot sai a cada 3 usuários reais
     * Este método é chamado automaticamente pelo FantasyTeamObserver
     */
    public static function adjustBotsInLeague(int $leagueId): array
    {
        $result = ['removed' => 0, 'reason' => ''];

        try {
            // Contar times reais (não-bots) na liga
            $realTeamsCount = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNull('bot_user_id')
                ->whereNotNull('user_id')
                ->count();

            // Contar bots na liga
            $botTeamsCount = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNotNull('bot_user_id')
                ->count();

            if ($botTeamsCount === 0) {
                $result['reason'] = 'Sem bots na liga';
                return $result;
            }

            // Proporção: 1 bot sai a cada 3 usuários reais
            // Cálculo: bots a manter = total_bots_inicial - (real_teams / 3)
            // Exemplo: 60 bots iniciais, 12 reais = 60 - 4 = 56 bots devem ficar
            
            // Bots que devem sair = real_teams / 3 (arredondado para baixo)
            $botsToRemove = (int) floor($realTeamsCount / 3);
            
            // Limitar para não remover mais do que existe
            $botsToRemove = min($botsToRemove, $botTeamsCount);
            
            // Manter pelo menos 5 bots para a liga não ficar vazia
            $minBotsToKeep = 5;
            $currentBotsShouldHave = $botTeamsCount - $botsToRemove;
            if ($currentBotsShouldHave < $minBotsToKeep && $botTeamsCount > $minBotsToKeep) {
                $botsToRemove = $botTeamsCount - $minBotsToKeep;
            }

            if ($botsToRemove <= 0) {
                $result['reason'] = 'Nenhum bot precisa sair ainda';
                return $result;
            }

            // Verificar quantos bots já foram removidos anteriormente
            // Comparando com o número esperado de remoções
            // Queremos remover apenas os bots "extras" que deveriam ter saído
            $alreadyRemoved = Schema::hasTable('fantasy_team_bot_removal_log')
                ? DB::table('fantasy_team_bot_removal_log')
                    ->where('fantasy_league_id', $leagueId)
                    ->count()
                : 0;
            
            $toRemoveNow = $botsToRemove - $alreadyRemoved;
            
            if ($toRemoveNow <= 0) {
                $result['reason'] = 'Bots já foram ajustados para o número atual de usuários';
                return $result;
            }

            // Selecionar bots para remover (os mais recentes, com menos pontos)
            $botsToDelete = FantasyTeam::where('fantasy_league_id', $leagueId)
                ->whereNotNull('bot_user_id')
                ->orderBy('total_points', 'asc')
                ->orderBy('created_at', 'desc')
                ->limit($toRemoveNow)
                ->get();

            foreach ($botsToDelete as $botTeam) {
                // Registrar remoção no log
                if (Schema::hasTable('fantasy_team_bot_removal_log')) {
                    DB::table('fantasy_team_bot_removal_log')->insert([
                        'fantasy_league_id' => $leagueId,
                        'fantasy_team_id' => $botTeam->id,
                        'bot_user_id' => $botTeam->bot_user_id,
                        'removed_at' => now(),
                        'reason' => "Ajuste automático: {$realTeamsCount} usuários reais",
                    ]);
                }

                // Soft delete do time bot
                $botTeam->delete();
                $result['removed']++;
            }

            $result['reason'] = "Removidos {$result['removed']} bots (proporção 1:3 com {$realTeamsCount} usuários reais)";
            
            \Log::info("🤖 Liga {$leagueId}: {$result['reason']}");

        } catch (\Exception $e) {
            $result['reason'] = 'Erro: ' . $e->getMessage();
            \Log::error("Erro ao ajustar bots na liga {$leagueId}: " . $e->getMessage());
        }

        return $result;
    }
}
