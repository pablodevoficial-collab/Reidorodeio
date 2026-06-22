<?php

namespace App\Services;

use App\Events\LiveRankingUpdated;
use App\Models\Competitor;
use App\Models\CompetitorContextStat;
use App\Models\CompetitorRanking;
use App\Models\CompetitorScoringLog;
use App\Models\CompetitorStat;
use App\Models\FantasyLeague;
use App\Models\Modalidade;
use App\Models\ModalidadeCompetitorGroup;
use App\Models\Rodeio;
use App\Models\X1Participant;
use App\Models\X1RoomInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service para pontuação em tempo real e cálculo de rankings
 * 
 * Processa ações de competidores durante transmissões ao vivo,
 * atualiza rankings e integra automaticamente com Fantasy e X1.
 */
class LiveScoringService
{
    private const FOLLOWER_NOTIFIABLE_ACTIONS = [
        'boa',
        'limpou_top',
        'limpou_top_mao',
    ];

    /**
     * Tabela de pontuação por ação (Laço Comprido)
     * Fonte única usada pelo painel e pela gravação da pontuação.
     */
    private const ACTION_DEFINITIONS = [
        'boa' => ['points' => 300, 'label' => 'Armada boa', 'variant' => 'success', 'section' => 'Pontuação', 'category' => 'positive'],
        'errou' => ['points' => -50, 'label' => 'Errou', 'variant' => 'danger', 'section' => 'Pontuação', 'category' => 'negative'],
    ];

    protected FantasyPointsUpdateService $fantasyService;

    public function __construct(FantasyPointsUpdateService $fantasyService)
    {
        $this->fantasyService = $fantasyService;
    }

    /**
     * Registrar uma ação de pontuação
     * 
     * @param int $competitorId ID do competidor
     * @param int $rodeioId ID do rodeio
     * @param int $modalidadeId ID da modalidade
     * @param string $actionType Tipo da ação (boa, top, errou_pescoco, etc)
     * @param string|null $divisao Divisão (A, B, etc)
     * @param string $eventPhase Fase do evento (classificatoria, final)
     * @param int|null $scoredBy ID do admin que pontuou
     * @param array $metadata Dados adicionais
     * @return array Resultado da operação
     */
    public function recordAction(
        int $competitorId,
        int $rodeioId,
        int $modalidadeId,
        string $actionType,
        ?string $divisao = null,
        string $eventPhase = 'classificatoria',
        ?int $scoredBy = null,
        array $metadata = []
    ): array {
        // Validar ação
        if (!isset(self::ACTION_DEFINITIONS[$actionType])) {
            return [
                'success' => false,
                'error' => "Ação desconhecida: {$actionType}",
            ];
        }

        $points = (int) self::ACTION_DEFINITIONS[$actionType]['points'];
        $category = $this->getActionCategory($actionType);

        try {
            return DB::transaction(function () use (
                $competitorId, $rodeioId, $modalidadeId, $actionType, 
                $points, $category, $divisao, $eventPhase, $scoredBy, $metadata
            ) {
                // 1. Obter pontuação atual antes da ação
                $contextStat = $this->getOrCreateContextStat($competitorId, $rodeioId, $modalidadeId, $divisao, $eventPhase);
                $scoreBefore = $contextStat->pontuacao_total ?? 0;
                $scoreAfter = $scoreBefore + $points;

                // 2. Registrar log da ação
                $log = CompetitorScoringLog::create([
                    'competitor_id' => $competitorId,
                    'rodeio_id' => $rodeioId,
                    'modalidade_id' => $modalidadeId,
                    'action_type' => $actionType,
                    'action_category' => $category,
                    'points' => $points,
                    'total_score_before' => $scoreBefore,
                    'total_score_after' => $scoreAfter,
                    'event_phase' => $eventPhase,
                    'notes' => $metadata['notes'] ?? null,
                    'scored_at' => now(),
                    'scored_by' => $scoredBy,
                    'metadata' => $metadata,
                ]);

                // 3. Atualizar stats de contexto (Rodeio + Modalidade)
                $this->updateContextStats($contextStat, $actionType, $points);

                // 4. Atualizar stats globais do competidor
                $this->updateGlobalStats($competitorId, $actionType, $points);

                // 5. Recalcular rankings
                $this->updateRankings($competitorId, $rodeioId, $modalidadeId, $divisao);

                // 6. Atualizar Fantasy (se houver ligas ativas)
                $this->updateFantasyPoints($competitorId, $rodeioId, $modalidadeId, $divisao ?? '');

                // 7. Verificar impacto em salas X1
                $x1Impact = $this->checkX1Impact($competitorId, $rodeioId, $modalidadeId);

                // 8. Broadcast evento via WebSocket
                $this->broadcastRankingUpdate($rodeioId, $modalidadeId, $divisao);

                // 9. Limpar cache relacionado
                $this->clearRelatedCache($rodeioId, $modalidadeId, $competitorId);

                // 10. Notificar seguidores em ações de destaque.
                $this->notifyCompetitorFollowers($log);

                Log::info('[LiveScoring] Ação registrada', [
                    'competitor_id' => $competitorId,
                    'action' => $actionType,
                    'points' => $points,
                    'score_before' => $scoreBefore,
                    'score_after' => $scoreAfter,
                    'x1_impact' => $x1Impact,
                ]);

                return [
                    'success' => true,
                    'log_id' => $log->id,
                    'action' => $actionType,
                    'points' => $points,
                    'score_before' => $scoreBefore,
                    'score_after' => $scoreAfter,
                    'x1_rooms_affected' => $x1Impact['rooms_affected'] ?? 0,
                ];
            });

        } catch (\Exception $e) {
            Log::error('[LiveScoring] Erro ao registrar ação', [
                'competitor_id' => $competitorId,
                'action' => $actionType,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Registrar múltiplas ações de uma vez (batch)
     */
    public function recordBatchActions(array $actions): array
    {
        $results = [];
        
        foreach ($actions as $action) {
            $results[] = $this->recordAction(
                $action['competitor_id'],
                $action['rodeio_id'],
                $action['modalidade_id'],
                $action['action_type'],
                $action['divisao'] ?? null,
                $action['event_phase'] ?? 'classificatoria',
                $action['scored_by'] ?? null,
                $action['metadata'] ?? []
            );
        }

        return $results;
    }

    /**
     * Apenas atualizar rankings (SEM criar logs ou modificar stats)
     * 
     * Este método é usado quando o Controller já processou a pontuação
     * e só precisa que os rankings sejam recalculados/atualizados.
     * 
     * @param int $competitorId
     * @param int $rodeioId
     * @param int $modalidadeId
     * @param string $actionType
     * @param string|null $divisao
     * @param array $metadata
     * @return array
     */
    public function syncRankingsOnly(
        int $competitorId,
        int $rodeioId,
        int $modalidadeId,
        string $actionType,
        ?string $divisao = null,
        array $metadata = []
    ): array {
        try {
            // Obter pontos da tabela (se existir)
            $points = (int) (self::ACTION_DEFINITIONS[$actionType]['points'] ?? 0);

            // 1. Atualizar rankings (Evento, Mensal, Geral)
            $this->updateRankings($competitorId, $rodeioId, $modalidadeId, $divisao);

            // 2. Disparar evento WebSocket
            $this->broadcastRankingUpdate($rodeioId, $modalidadeId, $divisao);

            // 3. Verificar impacto em X1 (sem processar resultado, apenas notificar)
            $x1Impact = $this->checkX1Impact($competitorId, $rodeioId, $modalidadeId);

            return [
                'success' => true,
                'action' => $actionType,
                'points' => $points,
                'rankings_updated' => true,
                'x1_rooms_affected' => $x1Impact['rooms_affected'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::warning('[LiveScoring] Erro ao sincronizar rankings', [
                'competitor_id' => $competitorId,
                'action' => $actionType,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Obter ou criar stats de contexto
     */
    private function getOrCreateContextStat(
        int $competitorId, 
        int $rodeioId, 
        int $modalidadeId, 
        ?string $divisao,
        string $eventPhase
    ): CompetitorContextStat {
        return CompetitorContextStat::firstOrCreate([
            'competitor_id' => $competitorId,
            'rodeio_id' => $rodeioId,
            'modalidade_id' => $modalidadeId,
            'divisao' => $divisao ?? '',
            'tipo_fase' => $eventPhase,
        ], [
            'pontuacao_total' => 0,
            'vitorias' => 0,
            'derrotas' => 0,
            'empates' => 0,
        ]);
    }

    /**
     * Atualizar stats de contexto
     */
    private function updateContextStats(CompetitorContextStat $stat, string $actionType, int $points): void
    {
        // Incrementar pontuação total
        $stat->pontuacao_total = ($stat->pontuacao_total ?? 0) + $points;

        // Mapa de ação → coluna (para ações cujo nome difere da coluna)
        $actionColumnMap = [
            'garupa' => 'count_garupa_neg',
        ];

        // Incrementar contador específico da ação
        $counterColumn = $actionColumnMap[$actionType] ?? "count_{$actionType}";
        if ($stat->hasColumn($counterColumn)) {
            $stat->$counterColumn = ($stat->$counterColumn ?? 0) + 1;
        }

        // Atualizar contadores de categoria
        $category = $this->getActionCategory($actionType);
        if ($category === 'positive') {
            $stat->count_boa = ($stat->count_boa ?? 0) + ($actionType === 'boa' ? 1 : 0);
        } elseif (in_array($category, ['negative', 'severe'])) {
            $stat->count_negativas_total = ($stat->count_negativas_total ?? 0) + 1;
        }

        $stat->save();
    }

    /**
     * Atualizar stats globais do competidor
     */
    private function updateGlobalStats(int $competitorId, string $actionType, int $points): void
    {
        $stat = CompetitorStat::firstOrCreate(
            ['competitor_id' => $competitorId],
            ['pontuacao_total' => 0]
        );

        // Incrementar pontuação total
        $stat->pontuacao_total = ($stat->pontuacao_total ?? 0) + $points;
        $stat->last_points = $points;

        // Mapa de ação → coluna (para ações cujo nome difere da coluna)
        $actionColumnMap = [
            'garupa' => 'count_garupa_neg',
        ];

        // Incrementar contador específico
        $counterColumn = $actionColumnMap[$actionType] ?? "count_{$actionType}";
        if (array_key_exists($counterColumn, $stat->getAttributes()) || in_array($counterColumn, $stat->getFillable())) {
            $stat->$counterColumn = ($stat->$counterColumn ?? 0) + 1;
        }

        // Atualizar contador de negativas
        $category = $this->getActionCategory($actionType);
        if (in_array($category, ['negative', 'severe'])) {
            $stat->count_negativas_total = ($stat->count_negativas_total ?? 0) + 1;
        }

        $stat->save();
    }

    /**
     * Atualizar rankings
     */
    private function updateRankings(int $competitorId, int $rodeioId, int $modalidadeId, ?string $divisao): void
    {
        // 1. Ranking por evento
        $this->updateEventRanking($rodeioId, $modalidadeId, $divisao);

        // 2. Ranking mensal
        $this->updateMonthlyRanking(now()->year, now()->month);

        // 3. Ranking geral (pode ser feito em background)
        // $this->updateOverallRanking();
    }

    /**
     * Atualizar ranking por evento
     */
    public function updateEventRanking(int $rodeioId, int $modalidadeId, ?string $divisao = null): void
    {
        try {
            // Buscar todos competidores com stats neste contexto
            $stats = CompetitorContextStat::query()
                ->where('rodeio_id', $rodeioId)
                ->where('modalidade_id', $modalidadeId)
                ->when($divisao, fn($q) => $q->where('divisao', $divisao))
                ->orderByDesc('pontuacao_total')
                ->get();

            $position = 1;
            foreach ($stats as $stat) {
                // Buscar breakdown de ações
                $breakdown = $this->getActionBreakdown($stat->competitor_id, $rodeioId, $modalidadeId, $divisao);

                // Calcular métricas
                $totalActions = array_sum($breakdown);
                $positiveActions = 0;
                $negativeActions = 0;

                foreach ($breakdown as $action => $count) {
                    if (in_array($action, self::ACTION_CATEGORIES['positive'])) {
                        $positiveActions += $count;
                    } else {
                        $negativeActions += $count;
                    }
                }

                $efficiencyRate = $totalActions > 0 
                    ? ($positiveActions / $totalActions) * 100 
                    : 0;

                // Atualizar ou criar ranking
                $ranking = CompetitorRanking::updateOrCreate(
                    [
                        'competitor_id' => $stat->competitor_id,
                        'ranking_type' => CompetitorRanking::TYPE_EVENT,
                        'rodeio_id' => $rodeioId,
                        'modalidade_id' => $modalidadeId,
                        'divisao' => $divisao ?? '',
                        'year' => null,
                        'month' => null,
                    ],
                    [
                        'previous_position' => DB::raw('position'),
                        'position' => $position,
                        'total_points' => $stat->pontuacao_total,
                        'total_actions' => $totalActions,
                        'positive_actions' => $positiveActions,
                        'negative_actions' => $negativeActions,
                        'efficiency_rate' => $efficiencyRate,
                        'action_breakdown' => $breakdown,
                        'calculated_at' => now(),
                    ]
                );

                // Calcular variação de pontos
                $ranking->points_change = $stat->pontuacao_total - ($ranking->getOriginal('total_points') ?? 0);
                $ranking->save();

                $position++;
            }

        } catch (\Exception $e) {
            Log::error('[LiveScoring] Erro ao atualizar ranking de evento', [
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Atualizar ranking mensal
     */
    public function updateMonthlyRanking(int $year, int $month): void
    {
        try {
            $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
            $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

            // Agregar pontuação de todos os logs do mês
            $monthlyStats = CompetitorScoringLog::query()
                ->select('competitor_id')
                ->selectRaw('SUM(points) as total_points')
                ->selectRaw('COUNT(*) as total_actions')
                ->selectRaw('SUM(CASE WHEN points > 0 THEN 1 ELSE 0 END) as positive_actions')
                ->selectRaw('SUM(CASE WHEN points < 0 THEN 1 ELSE 0 END) as negative_actions')
                ->whereBetween('scored_at', [$startDate, $endDate])
                ->groupBy('competitor_id')
                ->orderByDesc('total_points')
                ->get();

            $position = 1;
            foreach ($monthlyStats as $stat) {
                $efficiencyRate = $stat->total_actions > 0 
                    ? ($stat->positive_actions / $stat->total_actions) * 100 
                    : 0;

                CompetitorRanking::updateOrCreate(
                    [
                        'competitor_id' => $stat->competitor_id,
                        'ranking_type' => CompetitorRanking::TYPE_MONTHLY,
                        'year' => $year,
                        'month' => $month,
                        'rodeio_id' => null,
                        'modalidade_id' => null,
                        'divisao' => null,
                    ],
                    [
                        'previous_position' => DB::raw('position'),
                        'position' => $position,
                        'total_points' => $stat->total_points,
                        'total_actions' => $stat->total_actions,
                        'positive_actions' => $stat->positive_actions,
                        'negative_actions' => $stat->negative_actions,
                        'efficiency_rate' => $efficiencyRate,
                        'calculated_at' => now(),
                    ]
                );

                $position++;
            }

        } catch (\Exception $e) {
            Log::error('[LiveScoring] Erro ao atualizar ranking mensal', [
                'year' => $year,
                'month' => $month,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Atualizar ranking geral (overall)
     */
    public function updateOverallRanking(): void
    {
        try {
            // Agregar pontuação total de todos os tempos
            $overallStats = CompetitorStat::query()
                ->select('competitor_id', 'pontuacao_total')
                ->orderByDesc('pontuacao_total')
                ->get();

            $position = 1;
            foreach ($overallStats as $stat) {
                CompetitorRanking::updateOrCreate(
                    [
                        'competitor_id' => $stat->competitor_id,
                        'ranking_type' => CompetitorRanking::TYPE_OVERALL,
                        'year' => null,
                        'month' => null,
                        'rodeio_id' => null,
                        'modalidade_id' => null,
                        'divisao' => null,
                    ],
                    [
                        'previous_position' => DB::raw('position'),
                        'position' => $position,
                        'total_points' => $stat->pontuacao_total,
                        'calculated_at' => now(),
                    ]
                );

                $position++;
            }

        } catch (\Exception $e) {
            Log::error('[LiveScoring] Erro ao atualizar ranking geral', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obter breakdown de ações por competidor
     */
    private function getActionBreakdown(int $competitorId, int $rodeioId, int $modalidadeId, ?string $divisao): array
    {
        $query = CompetitorScoringLog::query()
            ->where('competitor_id', $competitorId)
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId);

        // Se divisão especificada, filtrar. Senão, pegar todas
        // (a divisão está em metadata ou podemos inferir pela fase)

        $logs = $query->get();

        $breakdown = [];
        foreach ($logs as $log) {
            $action = $log->action_type;
            $breakdown[$action] = ($breakdown[$action] ?? 0) + 1;
        }

        return $breakdown;
    }

    /**
     * Atualizar pontos do Fantasy
     */
    private function updateFantasyPoints(int $competitorId, int $rodeioId, int $modalidadeId, string $divisao): void
    {
        try {
            $this->fantasyService->updateTeamPoints($competitorId, $rodeioId, $modalidadeId, $divisao);
        } catch (\Exception $e) {
            Log::error('[LiveScoring] Erro ao atualizar Fantasy', [
                'competitor_id' => $competitorId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verificar impacto em salas X1
     */
    private function checkX1Impact(int $competitorId, int $rodeioId, int $modalidadeId): array
    {
        try {
            // Buscar salas X1 ativas com este competidor
            $participants = X1Participant::query()
                ->where('competitor_id', $competitorId)
                ->whereHas('roomInstance', function ($q) use ($rodeioId, $modalidadeId) {
                    $q->where('status', 'in_progress')
                      ->where('rodeio_id', $rodeioId)
                      ->where('modalidade_id', $modalidadeId);
                })
                ->with('roomInstance')
                ->get();

            $roomsAffected = [];
            
            foreach ($participants as $participant) {
                $room = $participant->roomInstance;
                if ($room) {
                    $roomsAffected[] = [
                        'room_id' => $room->id,
                        'room_code' => $room->room_code ?? null,
                        'participant_role' => $participant->role, // host ou opponent
                    ];
                }
            }

            return [
                'rooms_affected' => count($roomsAffected),
                'rooms' => $roomsAffected,
            ];

        } catch (\Exception $e) {
            Log::error('[LiveScoring] Erro ao verificar impacto X1', [
                'competitor_id' => $competitorId,
                'error' => $e->getMessage(),
            ]);
            return ['rooms_affected' => 0];
        }
    }

    /**
     * Broadcast atualização de ranking via WebSocket
     */
    private function broadcastRankingUpdate(int $rodeioId, int $modalidadeId, ?string $divisao): void
    {
        try {
            // Obter top 30 do ranking atual
            $topRanking = CompetitorRanking::query()
                ->byEvent($rodeioId, $modalidadeId, $divisao)
                ->top(30)
                ->with('competitor:id,nome,foto')
                ->get();

            $payload = [
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'divisao' => $divisao,
                'updated_at' => now()->toISOString(),
                'ranking' => $topRanking->map(fn($r) => [
                    'position' => $r->position,
                    'previous_position' => $r->previous_position,
                    'competitor_id' => $r->competitor_id,
                    'competitor_name' => $r->competitor->nome ?? 'N/A',
                    'competitor_photo' => $r->competitor->foto ?? null,
                    'total_points' => $r->total_points,
                    'points_change' => $r->points_change,
                    'efficiency_rate' => $r->efficiency_rate,
                ]),
            ];

            // Disparar evento (se classe existir)
            if (class_exists('App\Events\LiveRankingUpdated')) {
                event(new \App\Events\LiveRankingUpdated($payload));
            }

        } catch (\Exception $e) {
            Log::error('[LiveScoring] Erro ao broadcast ranking', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Limpar cache relacionado
     */
    private function clearRelatedCache(int $rodeioId, int $modalidadeId, int $competitorId): void
    {
        Cache::forget("ranking:event:{$rodeioId}:{$modalidadeId}");
        Cache::forget("competitor:stats:{$competitorId}");
        Cache::forget("ranking:monthly:" . now()->format('Y-m'));
    }

    /**
     * Obter categoria de uma ação
     */
    private function getActionCategory(string $actionType): string
    {
        return self::ACTION_DEFINITIONS[$actionType]['category'] ?? 'unknown';
    }

    private function notifyCompetitorFollowers(CompetitorScoringLog $log): void
    {
        if (!in_array($log->action_type, self::FOLLOWER_NOTIFIABLE_ACTIONS, true)) {
            return;
        }

        $competitor = Competitor::query()->find($log->competitor_id);
        if (!$competitor) {
            return;
        }

        $rodeio = Rodeio::query()->find($log->rodeio_id);
        $modalidade = Modalidade::query()->find($log->modalidade_id);
        $actionLabel = $log->action_description ?: ucfirst(str_replace('_', ' ', $log->action_type));
        $actionLabel = trim((string) preg_replace('/\s*\(.+\)$/', '', $actionLabel));
        $context = collect([
            $modalidade?->nome,
            $rodeio?->name,
        ])->filter()->implode(' • ');

        app(CompetitorFollowerService::class)->createEvent($competitor, 'scoring_highlight', [
            'title' => $competitor->nome . ' mandou ' . $actionLabel,
            'message' => trim($competitor->nome . ' acabou de registrar ' . $actionLabel . ($context !== '' ? ' em ' . $context . '.' : '.')),
            'cta_label' => 'Ver ficha completa',
            'cta_url' => route('hub.stats', ['competitor' => $competitor->id]),
            'rodeio_id' => $log->rodeio_id,
            'modalidade_id' => $log->modalidade_id,
            'scoring_log_id' => $log->id,
            'source_key' => 'scoring_log:' . $log->id,
            'metadata' => [
                'action_type' => $log->action_type,
                'points' => $log->points,
                'event_phase' => $log->event_phase,
            ],
        ]);
    }

    /**
     * Obter ranking ao vivo
     */
    public function getLiveRanking(int $rodeioId, int $modalidadeId, ?string $divisao = null, int $limit = 50): array
    {
        $cacheKey = "ranking:live:{$rodeioId}:{$modalidadeId}:" . ($divisao ?? 'all');

        return Cache::remember($cacheKey, 30, function () use ($rodeioId, $modalidadeId, $divisao, $limit) {
            $ranking = CompetitorRanking::query()
                ->byEvent($rodeioId, $modalidadeId, $divisao)
                ->top($limit)
                ->with('competitor:id,nome,foto,categoria')
                ->get();

            return [
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'divisao' => $divisao,
                'count' => $ranking->count(),
                'updated_at' => $ranking->first()?->calculated_at?->toISOString(),
                'ranking' => $ranking->map(fn($r) => [
                    'position' => $r->position,
                    'previous_position' => $r->previous_position,
                    'position_change' => $r->position_change,
                    'competitor' => [
                        'id' => $r->competitor_id,
                        'name' => $r->competitor->nome ?? 'N/A',
                        'photo' => $r->competitor->foto ?? null,
                        'category' => $r->competitor->categoria ?? null,
                    ],
                    'total_points' => $r->total_points,
                    'points_change' => $r->points_change,
                    'efficiency_rate' => round($r->efficiency_rate, 1),
                    'total_actions' => $r->total_actions,
                    'positive_actions' => $r->positive_actions,
                    'negative_actions' => $r->negative_actions,
                    'action_breakdown' => $r->action_breakdown,
                ]),
            ];
        });
    }

    /**
     * Obter histórico de pontuação de um competidor
     */
    public function getCompetitorHistory(int $competitorId, ?int $rodeioId = null, ?int $limit = 50): array
    {
        $query = CompetitorScoringLog::query()
            ->where('competitor_id', $competitorId)
            ->when($rodeioId, fn($q) => $q->where('rodeio_id', $rodeioId))
            ->orderByDesc('scored_at')
            ->limit($limit);

        $logs = $query->with(['rodeio:id,name', 'modalidade:id,nome'])->get();

        return [
            'competitor_id' => $competitorId,
            'count' => $logs->count(),
            'history' => $logs->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action_type,
                'action_description' => $log->action_description,
                'category' => $log->action_category,
                'points' => $log->points,
                'score_before' => $log->total_score_before,
                'score_after' => $log->total_score_after,
                'rodeio' => $log->rodeio?->name,
                'modalidade' => $log->modalidade?->nome,
                'event_phase' => $log->event_phase,
                'scored_at' => $log->scored_at?->toISOString(),
            ]),
        ];
    }

    /**
     * Obter tabela de pontuação (para exibição)
     */
    public static function getPointsTable(): array
    {
        $table = [];
        
        foreach (self::ACTION_DEFINITIONS as $action => $definition) {
            $table[] = [
                'action' => $action,
                'points' => (int) $definition['points'],
                'label' => $definition['label'],
                'variant' => $definition['variant'],
                'section' => $definition['section'],
                'category' => $definition['category'],
                'is_positive' => ((int) $definition['points']) > 0,
            ];
        }

        // Ordenar: positivos primeiro, depois por pontos desc
        usort($table, function ($a, $b) {
            if ($a['is_positive'] !== $b['is_positive']) {
                return $b['is_positive'] <=> $a['is_positive'];
            }
            return abs($b['points']) <=> abs($a['points']);
        });

        return $table;
    }

    public static function getScoreButtonSections(): array
    {
        $sections = [];

        foreach (self::ACTION_DEFINITIONS as $action => $definition) {
            $sectionName = $definition['section'];

            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = [
                    'section' => $sectionName,
                    'buttons' => [],
                ];
            }

            $sections[$sectionName]['buttons'][] = [
                'action' => $action,
                'points' => (int) $definition['points'],
                'label' => $definition['label'],
                'variant' => $definition['variant'],
            ];
        }

        return array_values($sections);
    }
}
