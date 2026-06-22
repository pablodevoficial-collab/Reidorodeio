<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rodeio;
use App\Models\Modalidade;
use App\Models\Competitor;
use App\Models\ModalidadeCompetitorGroup;
use App\Models\CompetitorScoringLog;
use App\Models\CompetitorContextStat;
use App\Events\ScoringUpdated;
use App\Events\RankingUpdated;
use App\Events\LiveTransmissionUpdated;
use App\Services\RankingSnapshotService;
use App\Services\CompetitorStatisticsService;
use App\Services\FantasyPointsUpdateService;
use App\Services\LiveScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LiveTransmissionController extends Controller
{
    protected CompetitorStatisticsService $statsService;
    protected FantasyPointsUpdateService $fantasyService;
    protected LiveScoringService $liveScoringService;

    public function __construct(
        CompetitorStatisticsService $statsService,
        FantasyPointsUpdateService $fantasyService,
        LiveScoringService $liveScoringService
    ) {
        $this->statsService = $statsService;
        $this->fantasyService = $fantasyService;
        $this->liveScoringService = $liveScoringService;
    }
    private function normalizeYouTubeUrlToEmbedBase(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        // Common formats:
        // - https://www.youtube.com/watch?v=VIDEO_ID
        // - https://youtu.be/VIDEO_ID
        // - https://www.youtube.com/embed/VIDEO_ID
        // - https://www.youtube.com/live/VIDEO_ID
        $patterns = [
            '/youtube\.com\/(?:watch\?v=|embed\/|live\/)([^&\n?#\/]+)/i',
            '/youtu\.be\/([^&\n?#\/]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m) && !empty($m[1])) {
                return 'https://www.youtube.com/embed/' . $m[1];
            }
        }

        // Fallback: keep as-is (may be another provider)
        return $url;
    }

    private function updateEnvValue(string $key, string $value): bool
    {
        $envPath = base_path('.env');
        if (!is_file($envPath) || !is_writable($envPath)) {
            return false;
        }

        $contents = file_get_contents($envPath);
        if ($contents === false) {
            return false;
        }

        $newline = str_contains($contents, "\r\n") ? "\r\n" : "\n";

        // Avoid queries in env to prevent malformed concatenation in frontend view
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);

        // Replace or append key=value (preserving file newline style)
        $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=.*$/m';
        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $key . '=' . $value, $contents);
        } else {
            $contents = rtrim($contents, "\r\n") . $newline . $key . '=' . $value . $newline;
        }

        return file_put_contents($envPath, $contents, LOCK_EX) !== false;
    }

    /**
     * Atualiza cache de transmissão imediatamente (sem depender de worker de fila).
     */
    private function syncTransmissionCache(Rodeio $rodeio, array $extra = []): void
    {
        try {
            $payload = array_merge([
                'rodeio_id' => (int) $rodeio->id,
                'status' => (string) ($rodeio->status_transmissao ?? 'programado'),
                'modalidade_atual' => $rodeio->modalidade_atual ?? null,
                'stream_url' => $rodeio->stream_url ?? null,
                'viewers_count' => 0,
                'message' => null,
                'timestamp' => now()->toISOString(),
            ], $extra);

            Cache::put("live_transmission_{$rodeio->id}", $payload, now()->addHours(24));

            $activeKey = 'active_transmissions';
            $activeTransmissions = Cache::get($activeKey, []);
            $status = strtolower((string) ($payload['status'] ?? ''));
            $inactiveStatuses = ['ended', 'stopped', 'finalizado', 'divisao_finalizada', 'finished', 'closed', 'somente_estatisticas'];

            if (in_array($status, $inactiveStatuses, true)) {
                unset($activeTransmissions[$rodeio->id]);
            } else {
                $activeTransmissions[$rodeio->id] = $payload;
            }
            Cache::put($activeKey, $activeTransmissions, now()->addHours(24));

            $logKey = "transmission_log_{$rodeio->id}";
            $logs = Cache::get($logKey, []);
            array_unshift($logs, $payload);
            Cache::put($logKey, array_slice($logs, 0, 100), now()->addDays(7));
        } catch (\Throwable $e) {
            Log::warning('Falha ao sincronizar cache da transmissão', [
                'rodeio_id' => $rodeio->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getModalidadesByRodeio(Request $request)
    {
        $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id'
        ]);

        $rodeio = Rodeio::findOrFail($request->rodeio_id);

        $select = ['id', 'nome', 'status', 'inicio'];
        if (Schema::hasColumn('modalidades', 'tem_divisoes')) {
            $select[] = 'tem_divisoes';
        }
        if (Schema::hasColumn('modalidades', 'divisoes')) {
            $select[] = 'divisoes';
        }

        $modalidades = $rodeio->modalidades()
            ->select($select)
            ->orderBy('inicio')
            ->orderBy('nome')
            ->orderBy('id')
            ->get()
            ->map(function($m){
                // Extrair nomes das divisões (pode ser array de strings ou array de objetos)
                $divisoesNomes = [];
                if ($m->divisoes) {
                    foreach ($m->divisoes as $div) {
                        if (is_array($div) && isset($div['nome'])) {
                            $divisoesNomes[] = $div['nome'];
                        } elseif (is_string($div)) {
                            $divisoesNomes[] = $div;
                        }
                    }
                }
                
                return [
                    'id' => $m->id,
                    'nome' => $m->nome,
                    'status' => $m->status,
                    'tem_divisoes' => (bool) ($m->tem_divisoes ?? false),
                    'divisoes' => $divisoesNomes,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $modalidades->count(),
            'modalidades' => $modalidades
        ]);
    }
    public function index(Request $request)
    {
        $pageTitle = 'Pontuação de Rodeio';

        // Se veio de uma seleção anterior, passar os IDs dos competidores selecionados
        $selectedCompetitorIds = $request->competitor_ids ? explode(',', $request->competitor_ids) : null;
        $rodeioId = $request->rodeio_id;
        $scoreButtonSections = LiveScoringService::getScoreButtonSections();

        return view('admin.live_transmission.index', compact('pageTitle', 'selectedCompetitorIds', 'rodeioId', 'scoreButtonSections'));
    }

    public function updateEventStatus(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Status manual desativado. O rodeio agora segue a data e hora configuradas.'
        ]);
    }

    /**
     * Finalizar stats de classificatória (chamado por updateEventStatus)
     */
    private function finalizeClassificatoriaStats(int $rodeioId, int $modalidadeId): int
    {
        try {
            $count = $this->statsService->finalizeClassificatoria($rodeioId, $modalidadeId);
            
            \Log::info('✅ Classificatória finalizada via status', [
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'competitors_finalized' => $count
            ]);
            
            return $count;
        } catch (\Exception $e) {
            \Log::error('❌ Erro ao finalizar classificatória via status', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Finalizar stats de uma divisão (chamado por updateEventStatus)
     */
    private function finalizeDivisaoStats(int $rodeioId, int $modalidadeId, string $divisao): int
    {
        try {
            $count = $this->statsService->finalizeDivisao($rodeioId, $modalidadeId, $divisao);
            
            \Log::info('✅ Divisão finalizada via status', [
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'divisao' => $divisao,
                'competitors_finalized' => $count
            ]);
            
            // Atualizar ranking Fantasy
            try {
                $leagues = \App\Models\FantasyLeague::query()
                    ->where('rodeio_id', $rodeioId)
                    ->where('modalidade_id', $modalidadeId)
                    ->where('is_active', true)
                    ->get();

                foreach ($leagues as $league) {
                    $this->fantasyService->updateRanking($league->id);
                }
            } catch (\Throwable $e) {
                \Log::warning('Erro ao atualizar rankings Fantasy', ['error' => $e->getMessage()]);
            }
            
            return $count;
        } catch (\Exception $e) {
            \Log::error('❌ Erro ao finalizar divisão via status', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function getTransmissionData(Request $request)
    {
        $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id',
            'selected_competitor_ids' => 'nullable|string',
            'only_rodeio' => 'nullable|boolean',
            'divisao' => 'nullable|string|max:60',
        ]);

        $rodeio = Rodeio::findOrFail($request->rodeio_id);
        $divisao = trim((string) ($request->divisao ?? ''));
        $currentStatus = $rodeio->status_transmissao ?? 'programado';

        // Otimização: quando só precisamos de metadados do rodeio (status/stream/modalidade atual)
        // não carregamos todos os competidores.
        if ($request->boolean('only_rodeio')) {
            return response()->json([
                'success' => true,
                'rodeio' => $rodeio,
                'competitors' => [],
                'modalidade_id' => $request->modalidade_id
            ]);
        }
        
        // Buscar competidores da modalidade
        if ($request->modalidade_id) {
            $modalidade = \App\Models\Modalidade::find($request->modalidade_id);
            $tamanhoEquipe = (int) ($modalidade->tamanho_equipe ?? 1);
            
            // Se tem divisão selecionada, buscar competidores dessa divisão
            if (!empty($divisao)) {
                $competitorIds = collect();
                
                // 1. Buscar competidores dos GRUPOS dessa divisão
                $groupIds = \App\Models\ModalidadeCompetitorGroup::where('modalidade_id', $request->modalidade_id)
                    ->where('divisao', $divisao)
                    ->where('status', '!=', 'desqualificado')
                    ->pluck('id');
                
                if ($groupIds->isNotEmpty()) {
                    $groupCompetitorIds = \DB::table('modalidade_competitor_group_members')
                        ->whereIn('group_id', $groupIds)
                        ->pluck('competitor_id');
                    
                    $competitorIds = $competitorIds->merge($groupCompetitorIds);
                }
                
                // 2. Também buscar competidores pelo competitor_modalidade.divisao (fallback)
                $modalidadeCompetitorIds = \DB::table('competitor_modalidade')
                    ->where('modalidade_id', $request->modalidade_id)
                    ->where('divisao', $divisao)
                    ->where(function($q) {
                        $q->where('status', '!=', 'desqualificado')
                          ->orWhereNull('status');
                    })
                    ->pluck('competitor_id');
                
                $competitorIds = $competitorIds->merge($modalidadeCompetitorIds)->unique();
                
                if ($competitorIds->isEmpty()) {
                    $competitorsQuery = Competitor::whereRaw('1 = 0'); // Nenhum competidor
                } else {
                    $competitorsQuery = Competitor::whereIn('id', $competitorIds->all());
                }
            } else {
                // Classificatória (sem divisão) - mostrar todos os competidores da modalidade
                // Buscar de DUAS fontes: pivot competitor_modalidade E grupos
                
                // 1. Competidores da pivot competitor_modalidade
                $pivotCompetitorIds = \DB::table('competitor_modalidade')
                    ->where('modalidade_id', $request->modalidade_id)
                    ->where(function($q) {
                        $q->where('status', '!=', 'desqualificado')
                          ->orWhereNull('status');
                    })
                    ->pluck('competitor_id');
                
                // 2. Competidores dos grupos desta modalidade
                $groupCompetitorIds = \DB::table('modalidade_competitor_group_members as m')
                    ->join('modalidade_competitor_groups as g', 'g.id', '=', 'm.group_id')
                    ->where('g.modalidade_id', $request->modalidade_id)
                    ->where('g.status', '!=', 'desqualificado')
                    ->pluck('m.competitor_id');
                
                // Merge e remove duplicados
                $allCompetitorIds = $pivotCompetitorIds->merge($groupCompetitorIds)->unique();
                
                if ($allCompetitorIds->isEmpty()) {
                    $competitorsQuery = Competitor::whereRaw('1 = 0'); // Nenhum competidor
                } else {
                    $competitorsQuery = Competitor::whereIn('id', $allCompetitorIds->all());
                }
            }
        } else {
            $competitorsQuery = Competitor::query();
        }

        // Se IDs específicos foram fornecidos, filtrar por eles
        if ($request->selected_competitor_ids) {
            $selectedIds = explode(',', $request->selected_competitor_ids);
            $competitorsQuery->whereIn('id', $selectedIds);
        }

        $competitors = $competitorsQuery->get();

        if ($request->filled('modalidade_id') && $request->filled('rodeio_id')) {
            $contextStats = CompetitorContextStat::query()
                ->where('rodeio_id', (int) $request->rodeio_id)
                ->where('modalidade_id', (int) $request->modalidade_id)
                ->where('divisao', $divisao)
                ->whereIn('competitor_id', $competitors->pluck('id'))
                ->get()
                ->keyBy('competitor_id');

            $competitors->each(function (Competitor $competitor) use ($contextStats) {
                $competitor->setRelation('stats', $contextStats->get($competitor->id) ?: new CompetitorContextStat([
                    'competitor_id' => $competitor->id,
                    'pontuacao_total' => 0,
                    'last_points' => 0,
                ]));
            });
        } else {
            $competitors->load('stats');
        }

        return response()->json([
            'success' => true,
            'rodeio' => $rodeio,
            'competitors' => $competitors,
            'modalidade_id' => $request->modalidade_id,
            'divisao' => $divisao,
            'status' => $currentStatus,
        ]);
    }

    public function saveStreamUrl(Request $request)
    {
        $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'stream_url' => 'required|url'
        ]);

        $rodeio = Rodeio::findOrFail($request->rodeio_id);
        $streamUrl = trim($request->stream_url);
        $rodeio->update(['stream_url' => $streamUrl]);

        $embedBase = $this->normalizeYouTubeUrlToEmbedBase($streamUrl);
        $envUpdated = false;
        if ($embedBase) {
            $envUpdated = $this->updateEnvValue('LIVE_STREAM_URL', $embedBase);
        }

        $configCached = is_file(base_path('bootstrap/cache/config.php'));
        if ($envUpdated && $configCached) {
            // In case config cache is enabled, try to clear it so env changes are reflected.
            try {
                Artisan::call('config:clear');
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $this->syncTransmissionCache($rodeio, [
            'live_stream_url' => $embedBase,
            'env_updated' => $envUpdated,
        ]);

        event(new LiveTransmissionUpdated([
            'rodeio_id' => $rodeio->id,
            'status' => $rodeio->status_transmissao,
            'modalidade_atual' => $rodeio->modalidade_atual,
            'stream_url' => $streamUrl,
            'live_stream_url' => $embedBase,
            'env_updated' => $envUpdated,
        ]));

        return response()->json([
            'success' => true,
            'message' => $envUpdated ? 'URL da transmissão salva e LIVE_STREAM_URL atualizado!' : 'URL da transmissão salva! (não foi possível atualizar o .env)',
            'rodeio_stream_url' => $streamUrl,
            'live_stream_url' => $embedBase,
            'env_updated' => $envUpdated,
            'config_cached' => $configCached,
        ]);
    }

    public function saveCurrentModalidade(Request $request)
    {
        $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'modalidade_id' => 'required|exists:modalidades,id',
            'divisao' => 'nullable|string|max:100',
        ]);

        $rodeio = Rodeio::findOrFail($request->rodeio_id);
        $modalidade = Modalidade::findOrFail($request->modalidade_id);

        $divisao = trim((string) ($request->divisao ?? ''));
        $temDivisoes = (bool) ($modalidade->tem_divisoes ?? false);
        
        // Extrair nomes das divisões (compatível com formato string e objeto)
        $allowedNames = [];
        if ($modalidade->divisoes) {
            foreach ($modalidade->divisoes as $div) {
                if (is_array($div) && isset($div['nome'])) {
                    $allowedNames[] = $div['nome'];
                } elseif (is_string($div)) {
                    $allowedNames[] = $div;
                }
            }
        }

        if ($temDivisoes) {
            if ($divisao === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selecione uma divisão para esta modalidade.',
                ], 422);
            }
            if ($allowedNames !== [] && !in_array($divisao, $allowedNames, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Divisão inválida para esta modalidade.',
                ], 422);
            }
        } else {
            $divisao = '';
        }

        $rodeio->update([
            'modalidade_atual' => $modalidade->id,
            'divisao_atual' => $divisao !== '' ? $divisao : null,
        ]);

        // Sincronizar o status da modalidade com o status da transmissão do rodeio
        if ($modalidade && $rodeio->status_transmissao) {
            $modalidade->status = $rodeio->status_transmissao;
            $modalidade->save();
        }

        $this->syncTransmissionCache($rodeio, [
            'divisao_atual' => $rodeio->divisao_atual,
        ]);

        event(new LiveTransmissionUpdated([
            'rodeio_id' => $rodeio->id,
            'status' => $rodeio->status_transmissao,
            'modalidade_atual' => $rodeio->modalidade_atual,
            'divisao_atual' => $rodeio->divisao_atual,
            'stream_url' => $rodeio->stream_url ?? null,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Modalidade atual salva!',
            'divisao_atual' => $rodeio->divisao_atual,
        ]);
    }

    public function finishModalidade(Request $request)
    {
        $request->validate([
            'modalidade_id' => 'required|exists:modalidades,id',
        ]);

        $modalidade = Modalidade::with('rodeio')->findOrFail($request->modalidade_id);
        $modalidade->status = 'finalizado';
        $modalidade->save();

        // Congelar "Última Pontuação" como total final para os competidores desta modalidade
        $competitors = $modalidade->competitors()->with('stats')->get();
        foreach ($competitors as $comp) {
            if (!$comp->stats) {
                $comp->stats()->create([
                    'pontuacao_total' => 0,
                    'vitorias' => 0,
                    'derrotas' => 0,
                    'empates' => 0,
                    'pontuacao_media' => 0,
                    'last_points' => 0,
                ]);
                $comp->refresh();
            }
            try {
                $comp->stats->last_points = (int) ($comp->stats->pontuacao_total ?? 0);
                $comp->stats->save();
            } catch (\Throwable $e) {
                // ignora se coluna não existir em ambientes antigos
            }
        }

        $championsNotified = 0;
        try {
            $championsNotified = $this->notifyModalidadeChampions($modalidade);
        } catch (\Throwable $exception) {
            Log::warning('[LiveTransmission] Falha ao notificar campeoes da modalidade', [
                'modalidade_id' => $modalidade->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Modalidade finalizada com sucesso',
            'champions_notified' => $championsNotified,
        ]);
    }

    private function notifyModalidadeChampions(Modalidade $modalidade): int
    {
        if (!Schema::hasTable('competitor_follow_events')) {
            return 0;
        }

        $rodeio = $modalidade->rodeio;
        $rodeioName = (string) ($rodeio?->name ?? 'Rodeio');
        $divisaoAtual = trim((string) ($rodeio?->divisao_atual ?? ''));
        $teamSize = max(1, (int) ($modalidade->tamanho_equipe ?? 1));
        $followerService = app(\App\Services\CompetitorFollowerService::class);
        $createdEvents = 0;
        $groupMemberIds = [];

        if ($teamSize > 1 && Schema::hasTable('modalidade_competitor_groups') && Schema::hasTable('modalidade_competitor_group_members')) {
            $groupsQuery = ModalidadeCompetitorGroup::query()
                ->with(['members' => function ($query) {
                    $query->select('competitors.id', 'competitors.nome');
                }])
                ->where('modalidade_id', $modalidade->id)
                ->where(function ($query) {
                    $query->where('status', '!=', 'desqualificado')
                        ->orWhereNull('status');
                });

            if ((bool) ($modalidade->tem_divisoes ?? false) && $divisaoAtual !== '' && Schema::hasColumn('modalidade_competitor_groups', 'divisao')) {
                $groupsQuery->where('divisao', $divisaoAtual);
            }

            foreach ($groupsQuery->get() as $group) {
                $groupDivisao = trim((string) ($group->divisao ?? $divisaoAtual));
                $groupName = trim((string) ($group->nome ?: $group->members->pluck('nome')->implode(' + ')));
                $prize = $this->resolveModalidadePrizeData($modalidade, $groupDivisao);

                foreach ($group->members as $competitor) {
                    $groupMemberIds[] = (int) $competitor->id;

                    $event = $followerService->createEvent($competitor, 'modalidade_champion', [
                        'title' => $competitor->nome . ' foi campeao',
                        'message' => $this->buildChampionMessage($competitor->nome, $rodeioName, $modalidade->nome, $groupDivisao, $groupName, $prize['label']),
                        'cta_label' => 'Ver ficha completa',
                        'cta_url' => route('hub.stats', ['competitor' => $competitor->id]),
                        'rodeio_id' => $modalidade->rodeio_id,
                        'modalidade_id' => $modalidade->id,
                        'source_key' => 'modalidade_champion:' . $modalidade->id . ':' . $competitor->id . ':group:' . $group->id . ':' . md5($groupDivisao),
                        'metadata' => [
                            'result' => 'champion',
                            'divisao' => $groupDivisao,
                            'group_id' => $group->id,
                            'group_name' => $groupName,
                            'prize_type' => $prize['tipo'],
                            'prize_amount' => $prize['valor'],
                            'prize_description' => $prize['descricao'],
                            'prize_label' => $prize['label'],
                        ],
                    ]);

                    if ($event) {
                        $createdEvents++;
                    }
                }
            }
        }

        if (!Schema::hasTable('competitor_modalidade')) {
            return $createdEvents;
        }

        $pivotQuery = DB::table('competitor_modalidade')
            ->where('modalidade_id', $modalidade->id)
            ->where(function ($query) {
                $query->where('status', '!=', 'desqualificado')
                    ->orWhereNull('status');
            });

        if ((bool) ($modalidade->tem_divisoes ?? false) && $divisaoAtual !== '' && Schema::hasColumn('competitor_modalidade', 'divisao')) {
            $pivotQuery->where('divisao', $divisaoAtual);
        }

        if (!empty($groupMemberIds)) {
            $pivotQuery->whereNotIn('competitor_id', array_values(array_unique($groupMemberIds)));
        }

        $pivotRows = $pivotQuery
            ->select(['competitor_id', DB::raw(Schema::hasColumn('competitor_modalidade', 'divisao') ? 'COALESCE(divisao, "") as divisao' : '"" as divisao')])
            ->get();

        if ($pivotRows->isEmpty()) {
            return $createdEvents;
        }

        $competitorsById = Competitor::query()
            ->whereIn('id', $pivotRows->pluck('competitor_id')->all())
            ->get()
            ->keyBy('id');

        foreach ($pivotRows as $row) {
            $competitor = $competitorsById->get((int) $row->competitor_id);
            if (!$competitor) {
                continue;
            }

            $competitorDivisao = trim((string) ($row->divisao ?? ''));
            $prize = $this->resolveModalidadePrizeData($modalidade, $competitorDivisao);

            $event = $followerService->createEvent($competitor, 'modalidade_champion', [
                'title' => $competitor->nome . ' foi campeao',
                'message' => $this->buildChampionMessage($competitor->nome, $rodeioName, $modalidade->nome, $competitorDivisao, null, $prize['label']),
                'cta_label' => 'Ver ficha completa',
                'cta_url' => route('hub.stats', ['competitor' => $competitor->id]),
                'rodeio_id' => $modalidade->rodeio_id,
                'modalidade_id' => $modalidade->id,
                'source_key' => 'modalidade_champion:' . $modalidade->id . ':' . $competitor->id . ':solo:' . md5($competitorDivisao),
                'metadata' => [
                    'result' => 'champion',
                    'divisao' => $competitorDivisao,
                    'group_id' => null,
                    'group_name' => null,
                    'prize_type' => $prize['tipo'],
                    'prize_amount' => $prize['valor'],
                    'prize_description' => $prize['descricao'],
                    'prize_label' => $prize['label'],
                ],
            ]);

            if ($event) {
                $createdEvents++;
            }
        }

        return $createdEvents;
    }

    private function resolveModalidadePrizeData(Modalidade $modalidade, string $divisao = ''): array
    {
        $tipo = trim((string) ($modalidade->tipo_premio ?? ''));
        $valor = $modalidade->valor_premio;
        $descricao = trim((string) ($modalidade->descricao_premio ?? ''));

        if ($divisao !== '' && (bool) ($modalidade->tem_divisoes ?? false)) {
            $divisaoData = $modalidade->getDivisaoByNome($divisao);
            if (is_array($divisaoData)) {
                $tipo = trim((string) ($divisaoData['tipo_premio'] ?? $tipo));
                $valor = $divisaoData['valor_premio'] ?? $valor;
                $descricao = trim((string) ($divisaoData['descricao_premio'] ?? $descricao));
            }
        }

        return [
            'tipo' => $tipo,
            'valor' => is_numeric($valor) ? (float) $valor : null,
            'descricao' => $descricao,
            'label' => $this->formatPrizeLabel($tipo, $valor, $descricao),
        ];
    }

    private function formatPrizeLabel(?string $tipo, $valor, ?string $descricao): string
    {
        $normalizedType = mb_strtolower(trim((string) $tipo));
        if (in_array($normalizedType, ['dinheiro', 'valor'], true) && is_numeric($valor)) {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
        }

        $descricao = trim((string) $descricao);
        if ($descricao !== '') {
            return $descricao;
        }

        return 'Premio cadastrado na modalidade';
    }

    private function buildChampionMessage(
        string $competitorName,
        string $rodeioName,
        string $modalidadeName,
        string $divisao,
        ?string $groupName,
        string $prizeLabel
    ): string {
        $contextParts = array_filter([$rodeioName, $modalidadeName, $divisao !== '' ? $divisao : null]);

        $message = $competitorName . ' ganhou ' . implode(' > ', $contextParts) . '.';

        $groupName = trim((string) $groupName);
        if ($groupName !== '') {
            $message .= ' Grupo: ' . $groupName . '.';
        }

        $message .= ' Premio: ' . $prizeLabel . '.';

        return $message;
    }

    public function addOperationLog(Request $request)
    {
        // Implementar log de operações se necessário
        return response()->json(['success' => true]);
    }

    public function getViewersCount()
    {
        // Simular contagem de espectadores
        $viewersCount = rand(100, 2000);
        
        return response()->json([
            'viewers' => $viewersCount,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function addScore(Request $request)
    {
        $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'action' => 'required|string',
            'points' => 'required|integer',
            'rodeio_id' => 'nullable|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id',
            'divisao' => 'nullable|string'
        ]);

        // Se houver modalidade no request (ou modalidade atual do rodeio), bloquear caso esteja finalizada
        $modalidade = null;
        if ($request->filled('modalidade_id')) {
            $modalidade = Modalidade::find($request->modalidade_id);
        } elseif ($request->filled('rodeio_id')) {
            $rodeio = Rodeio::find($request->rodeio_id);
            if ($rodeio && $rodeio->modalidade_atual) {
                $modalidade = Modalidade::find($rodeio->modalidade_atual);
            }
        }
        if ($modalidade && ($modalidade->status === 'finalizado')) {
            return response()->json([
                'success' => false,
                'message' => 'Modalidade já finalizada. Pontuação bloqueada.'
            ], 422);
        }

        $competitor = Competitor::findOrFail($request->competitor_id);

        // Verificar se o competidor tem estatísticas
        if (!$competitor->stats) {
            $competitor->stats()->create([
                'pontuacao_total' => 0,
                'vitorias' => 0,
                'derrotas' => 0
            ]);
        }

        // Capturar pontuação anterior (antes de atualizar stats)
        $totalScoreBefore = (int) ($competitor->stats->pontuacao_total ?? 0);

        // Mapear categorias de ação
        $actionCategories = [
            'boa' => 'armadas',
            'errou' => 'erros',
        ];

        // Criar log detalhado da pontuação
        $scoreLog = CompetitorScoringLog::create([
            'competitor_id' => $competitor->id,
            'rodeio_id' => $request->rodeio_id,
            'modalidade_id' => $modalidade?->id ?? $request->modalidade_id,
            'action_type' => $request->action,
            'action_category' => $actionCategories[$request->action] ?? 'outros',
            'points' => $request->points,
            'total_score_before' => $totalScoreBefore,
            'total_score_after' => $totalScoreBefore, // será atualizado após recomputar stats
            'event_phase' => 'regular',
            'notes' => $request->notes,
            'scored_at' => now(),
            'scored_by' => Auth::user()->username ?? Auth::user()->name ?? 'admin',
            'metadata' => [
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'divisao' => null,
            ]
        ]);

        // Atualizar estatísticas baseado no tipo de ação
        $this->updateCompetitorStatistics($competitor, $request->action, $request->points);

        // Se ação negativa (erro): marcar competidor como indisponível na modalidade
        $isNegativeAction = ($actionCategories[$request->action] ?? '') === 'erros';
        $competitorMarkedOut = false;
        if ($isNegativeAction) {
            $resolvedModalidadeId = (int) ($modalidade?->id ?? $request->modalidade_id ?? 0);
            if ($resolvedModalidadeId > 0) {
                try {
                    DB::table('competitor_modalidade')
                        ->where('competitor_id', $competitor->id)
                        ->where('modalidade_id', $resolvedModalidadeId)
                        ->update([
                            'disponivel_participacao' => false,
                            'observacoes' => DB::raw("CONCAT(COALESCE(observacoes,''), ' [AUTO] Erro: {$request->action}')"),
                        ]);
                    $competitorMarkedOut = true;
                } catch (\Throwable $e) {
                    // coluna pode não existir
                }
            }
        }

        // Atualizar estatísticas por contexto (rodeio + modalidade), para consumo no frontend.
        try {
            $resolvedModalidadeId = (int) ($modalidade?->id ?? $request->modalidade_id);
            $resolvedRodeioId = (int) ($request->rodeio_id ?? 0);
            if ($resolvedRodeioId > 0 && $resolvedModalidadeId > 0 && Schema::hasTable('competitor_stats')) {
                $divisao = '';
                $tipoFase = 'classificatoria'; // Default: classificatória
                
                try {
                    $rodeio = Rodeio::find($resolvedRodeioId);
                    $currentStatus = $rodeio?->status_transmissao ?? 'programado';
                    
                    // 🎯 DETECÇÃO BASEADA NO STATUS DO EVENTO
                    if ($currentStatus === 'classificatoria') {
                        // Classificatória: stats sem divisão
                        $tipoFase = 'classificatoria';
                        $divisao = '';
                    } elseif ($currentStatus === 'inicio_finais') {
                        // Finais: stats com divisão
                        $tipoFase = 'final';
                        $divisao = (string) ($rodeio?->divisao_atual ?? $request->divisao ?? '');
                        
                        // Fallback: usar divisão do competidor se não tiver no rodeio
                        if (empty($divisao)) {
                            $competitorModalidade = DB::table('competitor_modalidade')
                                ->where('competitor_id', $competitor->id)
                                ->where('modalidade_id', $resolvedModalidadeId)
                                ->first();
                            $divisao = (string) ($competitorModalidade->divisao ?? '');
                        }
                    } else {
                        // Outros status (programado, em_apuracao, etc): usa lógica legada
                        $tipoFase = 'classificatoria';
                        $divisao = '';
                    }
                } catch (\Throwable $e) {
                    $divisao = '';
                    $tipoFase = 'classificatoria';
                }

                // Anexa divisao e tipo_fase no log criado (melhora o undo contextual)
                try {
                    if (isset($scoreLog) && $scoreLog instanceof \App\Models\CompetitorScoringLog) {
                        $meta = is_array($scoreLog->metadata) ? $scoreLog->metadata : [];
                        $meta['divisao'] = (string) $divisao;
                        $meta['tipo_fase'] = $tipoFase;
                        $scoreLog->metadata = $meta;
                        $scoreLog->save();
                    }
                } catch (\Throwable $e) {
                    // não bloqueia
                }

                $this->statsService->updateStats(
                    competitorId: (int) $competitor->id,
                    rodeioId: $resolvedRodeioId,
                    modalidadeId: $resolvedModalidadeId,
                    divisao: $divisao,
                    tipoFase: $tipoFase,
                    action: (string) $request->action,
                    points: (int) $request->points
                );

                // 🎯 INTEGRAÇÃO FANTASY - Atualizar pontos em tempo real
                $this->fantasyService->updateTeamPoints(
                    competitorId: (int) $competitor->id,
                    rodeioId: $resolvedRodeioId,
                    modalidadeId: $resolvedModalidadeId,
                    divisao: $divisao
                );

                // 🏆 INTEGRAÇÃO RANKING REAL-TIME - Atualizar rankings ao vivo (sem duplicar pontuação)
                $this->liveScoringService->syncRankingsOnly(
                    competitorId: (int) $competitor->id,
                    rodeioId: $resolvedRodeioId,
                    modalidadeId: $resolvedModalidadeId,
                    actionType: (string) $request->action,
                    divisao: $divisao,
                    metadata: [
                        'scored_by' => Auth::user()->username ?? Auth::user()->name ?? 'admin',
                        'log_id' => $scoreLog?->id,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // não bloqueia a pontuação caso o update contextual falhe
        }

        // Recarregar para obter valor atualizado do contexto selecionado
        $competitor->stats->refresh();
        $totalScoreAfter = $this->getContextStatTotal(
            (int) $competitor->id,
            (int) ($request->rodeio_id ?? 0),
            (int) ($modalidade?->id ?? $request->modalidade_id ?? 0),
            (string) ($divisao ?? '')
        );

        // Atualizar o total_after no último log (o que acabamos de criar)
        try {
            if (isset($scoreLog) && $scoreLog instanceof \App\Models\CompetitorScoringLog) {
                $scoreLog->update(['total_score_after' => $totalScoreAfter]);
            }
        } catch (\Throwable $e) {
            // não quebra a request por falha de update do log
        }

        // Disparar evento de scoring (vai enfileirar ProcessScoringUpdate)
        try {
            event(new ScoringUpdated([
                'competitor_id' => $competitor->id,
                'modalidade_id' => $modalidade?->id ?? (int) $request->modalidade_id,
                'rodeio_id' => $request->rodeio_id,
                'pontuacao' => (int) $request->points,
                'tempo' => $request->tempo ?? null,
                'action' => $request->action,
                'competitor_name' => $competitor->nome,
                'operator_name' => Auth::user()->username ?? Auth::user()->name ?? 'admin',
            ]));
        } catch (\Throwable $e) {
            // não bloqueia a pontuação caso o realtime falhe
        }

        // Recalcular e disparar ranking (vai enfileirar ProcessRankingUpdate)
        try {
            $resolvedModalidadeId = (int) ($modalidade?->id ?? $request->modalidade_id);
            if ($resolvedModalidadeId > 0) {
                $service = app(RankingSnapshotService::class);
                // Passar divisão para filtrar ranking corretamente (classification vs finals)
                $payload = $service->buildModalidadeRankingPayload(
                    $resolvedModalidadeId, 
                    $request->rodeio_id,
                    $divisao ?? null
                );
                event(new RankingUpdated($payload));
            }
        } catch (\Throwable $e) {
            // não bloqueia a pontuação
        }

        return response()->json([
            'success' => true,
            'message' => 'Pontuação adicionada com sucesso!',
            'new_score' => $totalScoreAfter,
            'action_description' => $this->getActionDescription($request->action, $request->points),
            'log_id' => CompetitorScoringLog::latest()->first()->id,
            'competitor_marked_out' => $competitorMarkedOut,
            'competitor_id' => $competitor->id,
        ]);
    }

    public function canUndoLastScore(Request $request)
    {
        $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'rodeio_id' => 'nullable|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id',
        ]);

        // Resolve modalidade (mesma regra do addScore/undoLastScore)
        $modalidade = null;
        if ($request->filled('modalidade_id')) {
            $modalidade = Modalidade::find($request->modalidade_id);
        } elseif ($request->filled('rodeio_id')) {
            $rodeio = Rodeio::find($request->rodeio_id);
            if ($rodeio && $rodeio->modalidade_atual) {
                $modalidade = Modalidade::find($rodeio->modalidade_atual);
            }
        }

        $resolvedModalidadeId = (int) ($modalidade?->id ?? ($request->modalidade_id ?? 0));
        $resolvedRodeioId = (int) ($request->rodeio_id ?? 0);

        $query = CompetitorScoringLog::query()
            ->where('competitor_id', (int) $request->competitor_id)
            ->where('action_type', '!=', 'undo')
            ->whereNull('metadata->undone_at')
            ->orderByDesc('id');

        if ($resolvedRodeioId > 0) {
            $query->where('rodeio_id', $resolvedRodeioId);
        }
        if ($resolvedModalidadeId > 0) {
            $query->where('modalidade_id', $resolvedModalidadeId);
        }

        $last = $query->first(['id', 'action_type', 'points', 'scored_at']);

        return response()->json([
            'success' => true,
            'can_undo' => (bool) $last,
            'last_action' => $last?->action_type,
            'last_points' => (int) ($last?->points ?? 0),
            'last_scored_at' => $last?->scored_at,
        ]);
    }

    public function markCompetitorOut(Request $request)
    {
        $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'rodeio_id' => 'nullable|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id',
            'reason' => 'nullable|string|max:255',
        ]);

        if (!Schema::hasTable('competitor_modalidade') || !Schema::hasColumn('competitor_modalidade', 'disponivel_participacao')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabela de vinculo (competitor_modalidade) não disponível neste ambiente.',
            ], 422);
        }

        // Resolve modalidade (mesma regra do addScore)
        $modalidade = null;
        if ($request->filled('modalidade_id')) {
            $modalidade = Modalidade::find($request->modalidade_id);
        } elseif ($request->filled('rodeio_id')) {
            $rodeio = Rodeio::find($request->rodeio_id);
            if ($rodeio && $rodeio->modalidade_atual) {
                $modalidade = Modalidade::find($rodeio->modalidade_atual);
            }
        }

        $resolvedModalidadeId = (int) ($modalidade?->id ?? ($request->modalidade_id ?? 0));
        if ($resolvedModalidadeId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Modalidade não definida.',
            ], 422);
        }

        $divisaoAtual = '';
        if ($request->filled('rodeio_id') && Schema::hasTable('rodeios') && Schema::hasColumn('rodeios', 'divisao_atual')) {
            try {
                $rodeio = Rodeio::find((int) $request->rodeio_id);
                $divisaoAtual = (string) ($rodeio?->divisao_atual ?? '');
            } catch (\Throwable $e) {
                $divisaoAtual = '';
            }
        }

        $q = DB::table('competitor_modalidade')
            ->where('competitor_id', (int) $request->competitor_id)
            ->where('modalidade_id', $resolvedModalidadeId);

        // Se modalidade tem divisões, aplica o filtro de divisão quando disponível no schema.
        try {
            $temDivisoes = (bool) ($modalidade?->tem_divisoes ?? false);
            if ($temDivisoes && $divisaoAtual !== '' && Schema::hasColumn('competitor_modalidade', 'divisao')) {
                $q->where('divisao', $divisaoAtual);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $updated = (int) $q->update([
            'disponivel_participacao' => 0,
            'observacoes' => DB::raw("TRIM(CONCAT(COALESCE(observacoes,''), '\\n', '[AUTO] Marcado como fora em ', NOW()))"),
            'updated_at' => now(),
        ]);

        if ($updated <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vínculo competidor x modalidade não encontrado para marcar como fora.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Competidor marcado como fora (indisponível para equipes/X1).',
            'competitor_id' => (int) $request->competitor_id,
            'modalidade_id' => $resolvedModalidadeId,
        ]);
    }

    /**
     * Update competitor statistics based on action type
     */
    public function undoLastScore(Request $request)
    {
        $request->validate([
            'competitor_id' => 'nullable|exists:competitors,id',
            'rodeio_id' => 'nullable|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id',
        ]);

        // Resolve modalidade (mesma regra do addScore)
        $modalidade = null;
        if ($request->filled('modalidade_id')) {
            $modalidade = Modalidade::find($request->modalidade_id);
        } elseif ($request->filled('rodeio_id')) {
            $rodeio = Rodeio::find($request->rodeio_id);
            if ($rodeio && $rodeio->modalidade_atual) {
                $modalidade = Modalidade::find($rodeio->modalidade_atual);
            }
        }

        $resolvedModalidadeId = (int) ($modalidade?->id ?? ($request->modalidade_id ?? 0));
        $resolvedRodeioId = (int) ($request->rodeio_id ?? 0);

        // Se competitor_id não foi informado, descobrir automaticamente pelo último log
        if ($request->filled('competitor_id')) {
            $competitor = Competitor::findOrFail($request->competitor_id);
        } else {
            $lastLogQuery = CompetitorScoringLog::query()
                ->where('action_type', '!=', 'undo')
                ->whereNull('metadata->undone_at')
                ->orderByDesc('id');

            if ($resolvedRodeioId > 0) {
                $lastLogQuery->where('rodeio_id', $resolvedRodeioId);
            }
            if ($resolvedModalidadeId > 0) {
                $lastLogQuery->where('modalidade_id', $resolvedModalidadeId);
            }

            $lastLog = $lastLogQuery->first();
            if (!$lastLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma pontuação para desfazer.',
                ], 404);
            }

            $competitor = Competitor::findOrFail($lastLog->competitor_id);
        }
        if (!$competitor->stats) {
            $competitor->stats()->create([
                'pontuacao_total' => 0,
                'vitorias' => 0,
                'derrotas' => 0,
                'empates' => 0,
                'pontuacao_media' => 0,
                'last_points' => 0,
            ]);
            $competitor->refresh();
        }

        $result = DB::transaction(function () use ($competitor, $resolvedRodeioId, $resolvedModalidadeId) {
            $query = CompetitorScoringLog::query()
                ->where('competitor_id', (int) $competitor->id)
                ->where('action_type', '!=', 'undo')
                ->whereNull('metadata->undone_at')
                ->orderByDesc('id');

            if ($resolvedRodeioId > 0) {
                $query->where('rodeio_id', $resolvedRodeioId);
            }
            if ($resolvedModalidadeId > 0) {
                $query->where('modalidade_id', $resolvedModalidadeId);
            }

            /** @var CompetitorScoringLog|null $last */
            $last = $query->lockForUpdate()->first();
            if (!$last) {
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Nenhuma pontuação para desfazer.'
                ];
            }

            $action = (string) $last->action_type;
            $points = (int) $last->points;

            $meta = is_array($last->metadata) ? $last->metadata : [];
            $divisao = (string) ($meta['divisao'] ?? '');
            $totalBefore = $this->getContextStatTotal((int) $competitor->id, $resolvedRodeioId, $resolvedModalidadeId, $divisao);

            // Reverte stats globais
            $this->revertCompetitorStatistics($competitor, $action, $points);
            $competitor->stats->refresh();
            $totalAfter = (int) ($competitor->stats->pontuacao_total ?? 0);

            // Reverte stats por contexto (rodeio + modalidade + divisao)
            try {
                if ($resolvedRodeioId > 0 && $resolvedModalidadeId > 0 && Schema::hasTable('competitor_stats')) {
                    $meta = is_array($last->metadata) ? $last->metadata : [];
                    $divisao = (string) ($meta['divisao'] ?? '');
                    $tipoFase = (string) ($meta['tipo_fase'] ?? 'final');
                    
                    if ($divisao === '') {
                        try {
                            $rodeio = Rodeio::find($resolvedRodeioId);
                            $divisao = (string) ($rodeio?->divisao_atual ?? '');
                        } catch (\Throwable $e) {
                            $divisao = '';
                        }
                    }
                    $this->revertCompetitorContextStatistics(
                        competitorId: (int) $competitor->id,
                        rodeioId: $resolvedRodeioId,
                        modalidadeId: $resolvedModalidadeId,
                        divisao: $divisao,
                        tipoFase: $tipoFase,
                        action: $action,
                        points: $points,
                    );
                }
            } catch (\Throwable $e) {
                // não bloqueia
            }

            $totalAfter = $this->getContextStatTotal((int) $competitor->id, $resolvedRodeioId, $resolvedModalidadeId, $divisao);

            // Marca log original como desfeito
            $meta = is_array($last->metadata) ? $last->metadata : [];
            $meta['undone_at'] = now()->toISOString();
            $meta['undone_by_user_id'] = Auth::id();
            $last->metadata = $meta;
            $last->save();

            // Cria log de undo (auditoria)
            $undoLog = CompetitorScoringLog::create([
                'competitor_id' => $competitor->id,
                'rodeio_id' => $resolvedRodeioId ?: $last->rodeio_id,
                'modalidade_id' => $resolvedModalidadeId ?: $last->modalidade_id,
                'action_type' => 'undo',
                'action_category' => 'sistema',
                'points' => -$points,
                'total_score_before' => $totalBefore,
                'total_score_after' => $totalAfter,
                'event_phase' => $last->event_phase,
                'notes' => 'Desfez última ação',
                'scored_at' => now(),
                'scored_by' => Auth::user()->username ?? Auth::user()->name ?? 'admin',
                'metadata' => [
                    'user_id' => Auth::id(),
                    'undone_log_id' => $last->id,
                    'undone_action' => $action,
                    'undone_points' => $points,
                ],
            ]);

            // Vincula undo_log_id no original
            try {
                $meta = is_array($last->metadata) ? $last->metadata : [];
                $meta['undo_log_id'] = $undoLog->id;
                $last->metadata = $meta;
                $last->save();
            } catch (\Throwable $e) {
                // ignora
            }

            return [
                'success' => true,
                'new_score' => $totalAfter,
                'undone_action' => $action,
                'undone_points' => $points,
                'divisao' => $divisao ?? '',
            ];
        });

        if (!($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Falha ao desfazer.'
            ], (int) ($result['status'] ?? 422));
        }

        // Dispara evento de scoring para sincronizar (delta negativo)
        try {
            event(new ScoringUpdated([
                'competitor_id' => $competitor->id,
                'modalidade_id' => $resolvedModalidadeId ?: null,
                'rodeio_id' => $resolvedRodeioId ?: null,
                'pontuacao' => -((int) ($result['undone_points'] ?? 0)),
                'tempo' => null,
                'action' => 'undo',
                'competitor_name' => $competitor->nome,
                'operator_name' => Auth::user()->username ?? Auth::user()->name ?? 'admin',
            ]));
        } catch (\Throwable $e) {
            // não bloqueia
        }

        // Recalcula e dispara ranking
        try {
            if ($resolvedModalidadeId > 0) {
                $service = app(RankingSnapshotService::class);
                // Passar divisão extraída do log original
                $payload = $service->buildModalidadeRankingPayload(
                    $resolvedModalidadeId, 
                    $resolvedRodeioId ?: null,
                    ($result['divisao'] ?? null) ?: null
                );
                event(new RankingUpdated($payload));
            }
        } catch (\Throwable $e) {
            // não bloqueia
        }

        return response()->json([
            'success' => true,
            'message' => 'Última pontuação desfeita com sucesso!',
            'competitor_id' => $competitor->id,
            'competitor_name' => $competitor->nome,
            'new_total' => (int) ($result['new_score'] ?? 0),
            'new_score' => (int) ($result['new_score'] ?? 0),
            'undone_action' => $result['undone_action'] ?? null,
            'undone_points' => (int) ($result['undone_points'] ?? 0),
        ]);
    }

    private function revertCompetitorStatistics(Competitor $competitor, string $action, int $points): void
    {
        $stats = $competitor->stats;
        if (!$stats) {
            return;
        }

        $actionToCounter = [
            'boa' => 'count_boa',
            'errou_pescoco' => 'count_errou_pescoco',
            'errou_pata' => 'count_errou_pata',
            'errou_top' => 'count_errou_top',
            'dobrada' => 'count_dobrada',
            'cabresteou' => 'count_cabresteou',
            'duas_voltas' => 'count_duas_voltas',
            'limpou_garupa' => 'count_limpou_garupa',
            'garupa' => 'count_garupa_neg',
            'cola' => 'count_cola',
            'cola_neg' => 'count_cola_neg',
            'cupim' => 'count_cupim',
            'top' => 'count_top',
            'pescou' => 'count_pescou',
            'uma_aspa' => 'count_uma_aspa',
            'por_cima' => 'count_por_cima',
            'limpou_cupim_longe' => 'count_limpou_cupim_longe',
            'pescou_uma_aspa' => 'count_pescou_uma_aspa',
            'limpou_top' => 'count_limpou_top',
            'limpou_top_mao' => 'count_limpou_top_mao',
            'boi_tirou' => 'count_boi_tirou',
            'boi_pulou' => 'count_boi_pulou',
            'queimou_raia' => 'count_queimou_raia',
            'caiu_do_cavalo' => 'count_caiu_do_cavalo',
            'saiu_enrolado' => 'count_saiu_enrolado',
        ];

        $negativeActions = [
            'errou_pescoco',
            'errou_pata',
            'errou_top',
            'dobrada',
            'cabresteou',
            'duas_voltas',
            'garupa',
            'cola_neg',
            'uma_aspa',
            'por_cima',
            'boi_tirou',
            'boi_pulou',
            'queimou_raia',
            'caiu_do_cavalo',
            'saiu_enrolado',
        ];

        $updates = [
            'pontuacao_total' => DB::raw('COALESCE(pontuacao_total,0) - ' . ((int) $points)),
            'last_points' => -((int) $points),
        ];
        if (isset($actionToCounter[$action])) {
            $col = $actionToCounter[$action];
            $updates[$col] = DB::raw("GREATEST(COALESCE({$col},0) - 1, 0)");
        }
        if (in_array($action, $negativeActions, true)) {
            $updates['count_negativas_total'] = DB::raw('GREATEST(COALESCE(count_negativas_total,0) - 1, 0)');
        }

        try {
            \App\Models\CompetitorStat::query()->whereKey($stats->getKey())->update($updates);
            $stats->refresh();
        } catch (\Throwable $e) {
            // fallback best-effort
            try { $stats->increment('pontuacao_total', -((int) $points)); } catch (\Throwable $e) { /* ignore */ }
        }

        try {
            $totalBoasNegativas = (int) (($stats->count_boa ?? 0) + ($stats->count_negativas_total ?? 0));
            $stats->pontuacao_media = $totalBoasNegativas > 0
                ? ((int) ($stats->pontuacao_total ?? 0)) / $totalBoasNegativas
                : 0;
            $stats->save();
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function revertCompetitorContextStatistics(int $competitorId, int $rodeioId, int $modalidadeId, string $divisao, string $tipoFase, string $action, int $points): void
    {
        $stats = CompetitorContextStat::query()->where([
            'competitor_id' => $competitorId,
            'rodeio_id' => $rodeioId,
            'modalidade_id' => $modalidadeId,
            'divisao' => (string) ($divisao ?? ''),
        ])->first();

        if (!$stats) {
            return;
        }

        $actionToCounter = [
            'boa' => 'count_boa',
            'errou_pescoco' => 'count_errou_pescoco',
            'errou_pata' => 'count_errou_pata',
            'errou_top' => 'count_errou_top',
            'dobrada' => 'count_dobrada',
            'cabresteou' => 'count_cabresteou',
            'duas_voltas' => 'count_duas_voltas',
            'limpou_garupa' => 'count_limpou_garupa',
            'garupa' => 'count_garupa_neg',
            'cola' => 'count_cola',
            'cola_neg' => 'count_cola_neg',
            'cupim' => 'count_cupim',
            'top' => 'count_top',
            'pescou' => 'count_pescou',
            'uma_aspa' => 'count_uma_aspa',
            'por_cima' => 'count_por_cima',
            'limpou_cupim_longe' => 'count_limpou_cupim_longe',
            'pescou_uma_aspa' => 'count_pescou_uma_aspa',
            'limpou_top' => 'count_limpou_top',
            'limpou_top_mao' => 'count_limpou_top_mao',
            'boi_tirou' => 'count_boi_tirou',
            'boi_pulou' => 'count_boi_pulou',
            'queimou_raia' => 'count_queimou_raia',
            'caiu_do_cavalo' => 'count_caiu_do_cavalo',
            'saiu_enrolado' => 'count_saiu_enrolado',
        ];

        $negativeActions = [
            'errou_pescoco',
            'errou_pata',
            'errou_top',
            'dobrada',
            'cabresteou',
            'duas_voltas',
            'garupa',
            'cola_neg',
            'uma_aspa',
            'por_cima',
            'boi_tirou',
            'boi_pulou',
            'queimou_raia',
            'caiu_do_cavalo',
            'saiu_enrolado',
        ];

        $updates = [
            'pontuacao_total' => DB::raw('COALESCE(pontuacao_total,0) - ' . ((int) $points)),
            'last_points' => -((int) $points),
        ];

        if ($action === 'custom') {
            $updates['count_custom'] = DB::raw('GREATEST(COALESCE(count_custom,0) - 1, 0)');
            $updates['points_custom_total'] = DB::raw('COALESCE(points_custom_total,0) - ' . ((int) $points));
        } elseif (isset($actionToCounter[$action])) {
            $col = $actionToCounter[$action];
            $updates[$col] = DB::raw("GREATEST(COALESCE({$col},0) - 1, 0)");
        } else {
            try {
                $counts = is_array($stats->action_counts) ? $stats->action_counts : [];
                if (isset($counts[$action]) && (int) $counts[$action] > 0) {
                    $counts[$action] = (int) $counts[$action] - 1;
                    if ((int) $counts[$action] <= 0) {
                        unset($counts[$action]);
                    }
                    $stats->action_counts = $counts;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (in_array($action, $negativeActions, true)) {
            $updates['count_negativas_total'] = DB::raw('GREATEST(COALESCE(count_negativas_total,0) - 1, 0)');
        }

        \App\Models\CompetitorContextStat::query()->whereKey($stats->getKey())->update($updates);
        $stats->refresh();
        $stats->last_updated_at = now();
        try { $stats->save(); } catch (\Throwable $e) { /* ignore */ }
    }

    private function updateCompetitorStatistics($competitor, $action, $points)
    {
        $stats = $competitor->stats;
        
        // Incrementar contador específico da ação
        $actionToCounter = [
            'boa' => 'count_boa',
        ];

        // Incrementar contador específico da ação
        if (isset($actionToCounter[$action])) {
            $stats->increment($actionToCounter[$action]);
        }
        
        // Se ação for negativa, incrementa total de negativas
        $negativeActions = ['errou'];
        if (in_array($action, $negativeActions, true)) {
            // garante existência da coluna em runtime
            try { $stats->increment('count_negativas_total'); } catch (\Throwable $e) { /* coluna pode não existir antes da migração */ }
        }
        
        // Atualizar pontuação total e média
        $stats->increment('pontuacao_total', $points);
        $stats->last_points = $points;
        
        // Recalcular pontuação média baseada no total de ações
        $totalBoasNegativas = ($stats->count_boa ?? 0) + ($stats->count_negativas_total ?? 0);
        if ($totalBoasNegativas > 0) {
            $stats->pontuacao_media = $stats->pontuacao_total / $totalBoasNegativas;
        }

        // Recalcular aproveitamento (% de armadas boas)
        $stats->aproveitamento = $totalBoasNegativas > 0
            ? round(($stats->count_boa / $totalBoasNegativas) * 100, 2)
            : 0;
        
        $stats->save();
    }

    private function updateCompetitorContextStatistics(int $competitorId, int $rodeioId, int $modalidadeId, string $divisao, string $tipoFase, string $action, int $points): void
    {
        $stats = CompetitorContextStat::query()->firstOrCreate(
            [
                'competitor_id' => $competitorId,
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'divisao' => (string) ($divisao ?? ''),
            ],
            [
                'divisao' => (string) ($divisao ?? ''),
                'tipo_fase' => $tipoFase,
                'pontuacao_total' => 0,
                'last_points' => 0,
                'is_finalized' => false,
            ]
        );

        // Atualizar tipo_fase se mudou
        if ($stats->tipo_fase !== $tipoFase) {
            $stats->tipo_fase = $tipoFase;
        }

        $actionToCounter = [
            'boa' => 'count_boa',
        ];

        if ($action === 'custom') {
            $stats->increment('count_custom');
            $stats->increment('points_custom_total', $points);
        } elseif (isset($actionToCounter[$action])) {
            $stats->increment($actionToCounter[$action]);
        } else {
            $counts = is_array($stats->action_counts) ? $stats->action_counts : [];
            $counts[$action] = (int) ($counts[$action] ?? 0) + 1;
            $stats->action_counts = $counts;
        }

        $negativeActions = ['errou'];
        if (in_array($action, $negativeActions, true)) {
            $stats->increment('count_negativas_total');
        }

        $stats->increment('pontuacao_total', $points);
        $stats->last_points = $points;
        $stats->last_updated_at = now();
        $stats->save();
    }

    /**
     * Get action description in Portuguese
     */
    private function getActionDescription($action, $points)
    {
        $descriptions = [
            'boa' => 'Armada boa (+300)',
            'errou' => 'Errou (-50)',
        ];

        $sign = $points > 0 ? '+' : '';
        return $descriptions[$action] ?? "{$action} ({$sign}{$points})";
    }

    private function getContextStatTotal(int $competitorId, int $rodeioId, int $modalidadeId, string $divisao = ''): int
    {
        if ($competitorId <= 0 || $rodeioId <= 0 || $modalidadeId <= 0 || !Schema::hasTable('competitor_stats')) {
            return 0;
        }

        return (int) (CompetitorContextStat::query()
            ->where('competitor_id', $competitorId)
            ->where('rodeio_id', $rodeioId)
            ->where('modalidade_id', $modalidadeId)
            ->where('divisao', trim($divisao))
            ->value('pontuacao_total') ?? 0);
    }

    public function getCompetitorsStats(Request $request)
    {
        $request->validate([
            'modalidade_id' => 'required|exists:modalidades,id',
            'rodeio_id' => 'nullable|exists:rodeios,id'
        ]);

        $modalidade = Modalidade::findOrFail($request->modalidade_id);
        
        // Verificar se a modalidade pertence ao rodeio especificado (se fornecido)
        if ($request->rodeio_id && $modalidade->rodeio_id != $request->rodeio_id) {
            return response()->json([
                'success' => false,
                'message' => 'Modalidade não pertence ao rodeio especificado'
            ], 400);
        }
        
        $rodeioId = (int) ($request->rodeio_id ?? 0);

        $divisaoAtual = '';
        if ($rodeioId > 0 && Schema::hasTable('rodeios') && Schema::hasColumn('rodeios', 'divisao_atual')) {
            try {
                $rodeio = Rodeio::find($rodeioId);
                $divisaoAtual = (string) ($rodeio?->divisao_atual ?? '');
            } catch (\Throwable $e) {
                $divisaoAtual = '';
            }
        }

        $hasPivotDivisao = Schema::hasTable('competitor_modalidade') && Schema::hasColumn('competitor_modalidade', 'divisao');

        $competitors = Competitor::query()
            ->where('status', 'ativo')
            ->whereHas('modalidades', function ($query) use ($request, $modalidade, $divisaoAtual, $hasPivotDivisao) {
                $query->where('modalidade_id', (int) $request->modalidade_id);

                // Only list available competitors.
                if (Schema::hasColumn('competitor_modalidade', 'disponivel_participacao')) {
                    $query->where('competitor_modalidade.disponivel_participacao', 1);
                }

                // If modalidade has divisions, filter by rodeio.divisao_atual.
                if ((bool) ($modalidade->tem_divisoes ?? false) && $divisaoAtual !== '' && $hasPivotDivisao) {
                    $query->where('competitor_modalidade.divisao', $divisaoAtual);
                }
            })
            ->orderBy('nome')
            ->get();

        $ctxStatsByCompetitorId = collect();
        if ($rodeioId > 0 && Schema::hasTable('competitor_stats')) {

            $ctxStatsByCompetitorId = CompetitorContextStat::query()
                ->where('rodeio_id', $rodeioId)
                ->where('modalidade_id', (int) $request->modalidade_id)
                ->when(Schema::hasColumn('competitor_stats', 'divisao'), function ($q) use ($divisaoAtual) {
                    return $q->where('divisao', (string) ($divisaoAtual ?? ''));
                })
                ->whereIn('competitor_id', $competitors->pluck('id'))
                ->get()
                ->keyBy('competitor_id');
        }

        $data = $competitors->map(function($competitor) use ($ctxStatsByCompetitorId, $rodeioId) {
            $ctx = $rodeioId > 0 ? ($ctxStatsByCompetitorId[$competitor->id] ?? null) : null;
            $total = (int) ($ctx?->pontuacao_total ?? $competitor->stats?->pontuacao_total ?? 0);
            $countBoa = (int) ($ctx?->count_boa ?? $competitor->stats?->count_boa ?? 0);
            $countNegTotal = (int) ($ctx?->count_negativas_total ?? $competitor->stats?->count_negativas_total ?? 0);
            $pontuacaoMedia = 0;
            $den = $countBoa + $countNegTotal;
            if ($den > 0) {
                $pontuacaoMedia = $total / $den;
            }

            return [
                'id' => $competitor->id,
                'nome' => $competitor->nome,
                'foto' => $competitor->foto,
                'vitorias' => $competitor->stats?->vitorias ?? 0,
                'derrotas' => $competitor->stats?->derrotas ?? 0,
                'empates' => $competitor->stats?->empates ?? 0,
                'pontuacao_media' => $pontuacaoMedia,
                'pontuacao_total' => $total,
                'aproveitamento' => $competitor->stats?->aproveitamento ?? 0,
                // contadores por ação
                'count_boa' => $countBoa,
                'count_errou_pescoco' => (int) ($ctx?->count_errou_pescoco ?? $competitor->stats?->count_errou_pescoco ?? 0),
                'count_dobrada' => (int) ($ctx?->count_dobrada ?? $competitor->stats?->count_dobrada ?? 0),
                'count_cabresteou' => (int) ($ctx?->count_cabresteou ?? $competitor->stats?->count_cabresteou ?? 0),
                'count_duas_voltas' => (int) ($ctx?->count_duas_voltas ?? $competitor->stats?->count_duas_voltas ?? 0),
                'count_limpou_garupa' => (int) ($ctx?->count_limpou_garupa ?? $competitor->stats?->count_limpou_garupa ?? 0),
                'count_cola' => (int) ($ctx?->count_cola ?? $competitor->stats?->count_cola ?? 0),
                'count_cupim' => (int) ($ctx?->count_cupim ?? $competitor->stats?->count_cupim ?? 0),
                'count_top' => (int) ($ctx?->count_top ?? $competitor->stats?->count_top ?? 0),
                'count_pescou' => (int) ($ctx?->count_pescou ?? $competitor->stats?->count_pescou ?? 0),
                'count_errou_pata' => (int) ($ctx?->count_errou_pata ?? $competitor->stats?->count_errou_pata ?? 0),
                'count_errou_top' => (int) ($ctx?->count_errou_top ?? $competitor->stats?->count_errou_top ?? 0),
                'count_garupa_neg' => (int) ($ctx?->count_garupa_neg ?? $competitor->stats?->count_garupa_neg ?? 0),
                'count_cola_neg' => (int) ($ctx?->count_cola_neg ?? $competitor->stats?->count_cola_neg ?? 0),
                'count_uma_aspa' => (int) ($ctx?->count_uma_aspa ?? $competitor->stats?->count_uma_aspa ?? 0),
                'count_por_cima' => (int) ($ctx?->count_por_cima ?? $competitor->stats?->count_por_cima ?? 0),
                'count_limpou_cupim_longe' => (int) ($ctx?->count_limpou_cupim_longe ?? $competitor->stats?->count_limpou_cupim_longe ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'modalidade_nome' => $modalidade->nome,
            'modalidade_status' => $modalidade->status,
            'competitors' => $data
        ]);
    }

    public function updateCompetitorStats(Request $request)
    {
        $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'vitorias' => 'required|integer|min:0',
            'derrotas' => 'required|integer|min:0',
            'empates' => 'required|integer|min:0',
            'pontuacao_media' => 'nullable|numeric|min:0'
        ]);

        $competitor = Competitor::findOrFail($request->competitor_id);

        $competitor->stats()->updateOrCreate(
            ['competitor_id' => $competitor->id],
            $request->only([
                'vitorias', 'derrotas', 'empates', 'pontuacao_media'
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Estatísticas atualizadas com sucesso!'
        ]);
    }

    public function getCompetitorScoringHistory(Request $request)
    {
        $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'rodeio_id' => 'nullable|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $query = CompetitorScoringLog::where('competitor_id', $request->competitor_id)
            ->with(['rodeio', 'modalidade'])
            ->orderBy('scored_at', 'desc');

        if ($request->rodeio_id) {
            $query->where('rodeio_id', $request->rodeio_id);
        }

        if ($request->modalidade_id) {
            $query->where('modalidade_id', $request->modalidade_id);
        }

        $logs = $query->limit($request->limit ?? 50)->get();

        $data = $logs->map(function($log) {
            return [
                'id' => $log->id,
                'action_description' => $log->action_description,
                'action_category' => $log->action_category,
                'points' => $log->points,
                'total_score_before' => $log->total_score_before,
                'total_score_after' => $log->total_score_after,
                'scored_at' => $log->scored_at->format('d/m/Y H:i:s'),
                'scored_by' => $log->scored_by,
                'rodeio_name' => $log->rodeio->name ?? 'N/A',
                'modalidade_name' => $log->modalidade->nome ?? 'N/A',
                'notes' => $log->notes
            ];
        });

        return response()->json([
            'success' => true,
            'logs' => $data,
            'total_logs' => $logs->count()
        ]);
    }

    public function getCompetitorStatsSummary(Request $request)
    {
        $request->validate([
            'competitor_id' => 'required|exists:competitors,id',
            'rodeio_id' => 'nullable|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id'
        ]);

        $query = CompetitorScoringLog::where('competitor_id', $request->competitor_id);

        if ($request->rodeio_id) {
            $query->where('rodeio_id', $request->rodeio_id);
        }

        if ($request->modalidade_id) {
            $query->where('modalidade_id', $request->modalidade_id);
        }

        $logs = $query->get();

        $summary = [
            'total_actions' => $logs->count(),
            'total_points' => $logs->sum('points'),
            'positive_actions' => $logs->where('points', '>', 0)->count(),
            'negative_actions' => $logs->where('points', '<', 0)->count(),
            'by_category' => $logs->groupBy('action_category')->map(function($categoryLogs) {
                return [
                    'count' => $categoryLogs->count(),
                    'total_points' => $categoryLogs->sum('points'),
                    'average_points' => $categoryLogs->avg('points')
                ];
            }),
            'by_action_type' => $logs->groupBy('action_type')->map(function($actionLogs) {
                return [
                    'count' => $actionLogs->count(),
                    'total_points' => $actionLogs->sum('points')
                ];
            }),
            'best_score' => $logs->max('total_score_after'),
            'worst_score' => $logs->min('total_score_after'),
            'last_action' => $logs->sortByDesc('scored_at')->first()
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    /**
     * Desqualificar competidor e processar resultados automáticos das salas X1
     */
    public function disqualifyCompetitor(Request $request)
    {
        try {
            $competitorId = $request->input('competitor_id');
            $rodeioId = $request->input('rodeio_id');
            $modalidadeId = $request->input('modalidade_id');
            $action = $request->input('action');
            $points = $request->input('points');
            $label = $request->input('label');

            // Validar dados
            if (!$competitorId || !$rodeioId || !$modalidadeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados insuficientes para desqualificação'
                ], 400);
            }

            // Buscar competidor
            $competitor = Competitor::find($competitorId);
            if (!$competitor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Competidor não encontrado'
                ], 404);
            }

            \Log::info('🔥 Iniciando desqualificação', [
                'competitor_id' => $competitorId,
                'competitor_name' => $competitor->nome,
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId
            ]);

            // 1️⃣ DESQUALIFICAÇÃO NÃO ALTERA PONTUAÇÃO NEM ESTATÍSTICAS DO COMPETIDOR.
            // O competidor/grupo apenas deixa de ficar disponível para novas escolhas.

            // 2️⃣ IDENTIFICAR GRUPOS QUE O COMPETIDOR PERTENCE
            $groupIds = DB::table('modalidade_competitor_group_members')
                ->where('competitor_id', $competitorId)
                ->pluck('group_id')
                ->toArray();
            
            \Log::info('👥 Grupos do competidor:', ['groups' => $groupIds]);

            // REMOVER DE GRUPOS E FANTASY (Conforme solicitado: "Tira eles de listas x1, listas fantasy, grupo ligado a ele")
            
            // DISSOLVER GRUPOS INTEIROS (Se um membro é desqualificado, o grupo todo é removido/desqualificado)
            $allGroupMemberIds = [];
            if (!empty($groupIds)) {
                // Buscar TODOS os membros dos grupos ANTES de deletar
                $allGroupMemberIds = DB::table('modalidade_competitor_group_members')
                    ->whereIn('group_id', $groupIds)
                    ->pluck('competitor_id')
                    ->unique()
                    ->values()
                    ->toArray();
                
                \Log::info('👥 Membros de grupos afetados pela desqualificação:', [
                    'group_ids' => $groupIds,
                    'member_ids' => $allGroupMemberIds
                ]);
                
                // Remove TODOS os membros dos grupos afetados
                DB::table('modalidade_competitor_group_members')
                    ->whereIn('group_id', $groupIds)
                    ->delete();
                
                // Marcar grupos como inativos/desqualificados
                DB::table('modalidade_competitor_groups')
                    ->whereIn('id', $groupIds)
                    ->update(['status' => 'desqualificado']);
                
                // Marcar TODOS os membros do grupo como indisponíveis
                if (!empty($allGroupMemberIds)) {
                    DB::table('competitor_modalidade')
                        ->whereIn('competitor_id', $allGroupMemberIds)
                        ->where('modalidade_id', $modalidadeId)
                        ->update([
                            'disponivel_participacao' => 0,
                            'observacoes' => DB::raw("CONCAT(COALESCE(observacoes,''), '\n[AUTO] Grupo desqualificado em " . now() . "')")
                        ]);
                    
                    // NÃO remover de fantasy_team_competitors:
                    // Competidores desqualificados mantêm seus pontos e continuam
                    // aparecendo nas equipes já montadas pelo usuário.
                    // A remoção de disponivel_participacao=0 já impede que sejam
                    // selecionados em NOVAS equipes.
                }
                
                // Limpar cache de grupos para essa modalidade
                \Illuminate\Support\Facades\Cache::forget("modalidade_{$modalidadeId}_grupos");
                // Limpar também caches com divisão (se existirem)
                $divisoes = DB::table('modalidade_competitor_groups')
                    ->whereIn('id', $groupIds)
                    ->distinct()
                    ->pluck('divisao');
                foreach ($divisoes as $div) {
                    if ($div) {
                        $slug = \Illuminate\Support\Str::slug($div);
                        \Illuminate\Support\Facades\Cache::forget("modalidade_{$modalidadeId}_divisao_{$slug}_grupos");
                    }
                }
            }

            // Se não tiver grupos, remove apenas a associação individual (caso exista lógica residual)
            DB::table('modalidade_competitor_group_members')
                ->where('competitor_id', $competitorId)
                ->delete();

            // NÃO remover de fantasy_team_competitors:
            // Competidor desqualificado mantém pontos e aparece nas equipes já montadas.
            // disponivel_participacao=0 já impede seleção em novas equipes.

            // 3️⃣ BUSCAR PARTICIPANTES QUE ESCOLHERAM ESSE COMPETIDOR OU SEUS GRUPOS EM SALAS ATIVAS
            // Status: open, in_progress, pending_payment (aguardando oponente ou pagamento)
            $participantsToDisqualify = \App\Models\X1Participant::whereHas('room', function($q) {
                    $q->whereIn('status', ['open', 'in_progress', 'pending_payment']);
                })
                ->where(function($q) use ($competitorId, $groupIds) {
                    $q->where('competitor_id', $competitorId);
                    if (!empty($groupIds)) {
                        $q->orWhereIn('competitor_group_id', $groupIds);
                    }
                })
                ->with(['room.participants.user'])
                ->get();
            
            // 3️⃣.1 TAMBÉM BUSCAR SALAS ONDE O COMPETIDOR/GRUPO ESTÁ NA FK DIRETA (sem participante ainda)
            $roomsWithDirectFK = \App\Models\X1RoomInstance::whereIn('status', ['open', 'in_progress', 'pending_payment'])
                ->where(function($q) use ($competitorId, $groupIds) {
                    $q->where('competitor_id', $competitorId);
                    if (!empty($groupIds)) {
                        $q->orWhereIn('competitor_group_id', $groupIds);
                    }
                })
                ->with(['participants.user'])
                ->get();
            
            \Log::info('📊 Participantes X1 afetados (vão perder):', [
                'total_participants' => $participantsToDisqualify->count(),
                'total_rooms_direct_fk' => $roomsWithDirectFK->count()
            ]);

            $affectedRooms = 0;
            $winners = 0;
            $paymentsProcessed = 0;
            $usersNotified = [];
            $processedRoomIds = []; // Evitar processar mesma sala 2x

            // 4️⃣ PROCESSAR SALAS VIA FK DIRETA (host escolheu o competidor desqualificado)
            foreach ($roomsWithDirectFK as $room) {
                if (in_array($room->id, $processedRoomIds)) continue;
                $processedRoomIds[] = $room->id;
                
                // Ignorar salas já finalizadas
                if (in_array($room->status, ['completed', 'closed', 'cancelled'])) continue;

                // Buscar participantes pagos
                $paidParticipants = $room->participants->where('payment_status', 'paid');
                
                // Se ninguém pagou (pending_payment), apenas cancela
                if ($paidParticipants->count() === 0) {
                    $room->status = 'cancelled';
                    $room->closed_at = now();
                    $room->save();
                    \Log::info('🚫 Sala cancelada (pending_payment, FK direta)', ['room_id' => $room->id]);
                    $affectedRooms++;
                    continue;
                }

                // Se só o host pagou (open), reembolsar e cancelar
                if ($paidParticipants->count() === 1) {
                    $hostParticipant = $paidParticipants->first();
                    if ($hostParticipant && $hostParticipant->user && $room->valor_entrada > 0) {
                        $hostParticipant->user->increment('balance', $room->valor_entrada);
                        DB::table('transactions')->insert([
                            'user_id' => $hostParticipant->user_id,
                            'amount' => $room->valor_entrada,
                            'post_balance' => $hostParticipant->user->balance,
                            'charge' => 0,
                            'trx_type' => '+',
                            'trx' => getTrx(),
                            'remark' => 'x1_refund',
                            'details' => "Reembolso X1 (Competidor desqualificado) - Sala #{$room->id}",
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    $room->status = 'cancelled';
                    $room->closed_at = now();
                    $room->save();
                    \Log::info('🚫 Sala cancelada + reembolso (open, FK direta)', ['room_id' => $room->id]);
                    $affectedRooms++;
                    continue;
                }

                // Se 2 participantes pagaram, o oponente (quem NÃO escolheu o competidor desqualificado) vence
                $winnerParticipant = $room->participants
                    ->where('payment_status', 'paid')
                    ->filter(function($p) use ($competitorId, $groupIds) {
                        return $p->competitor_id != $competitorId && !in_array($p->competitor_group_id, $groupIds);
                    })
                    ->first();

                if (!$winnerParticipant) {
                    // Ambos escolheram o mesmo competidor? Reembolsar ambos
                    foreach ($paidParticipants as $p) {
                        if ($p->user && $room->valor_entrada > 0) {
                            $p->user->increment('balance', $room->valor_entrada);
                            DB::table('transactions')->insert([
                                'user_id' => $p->user_id,
                                'amount' => $room->valor_entrada,
                                'post_balance' => $p->user->balance,
                                'charge' => 0,
                                'trx_type' => '+',
                                'trx' => getTrx(),
                                'remark' => 'x1_refund',
                                'details' => "Reembolso X1 (Ambos desqualificados) - Sala #{$room->id}",
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                    $room->status = 'cancelled';
                    $room->closed_at = now();
                    $room->save();
                    \Log::info('🚫 Sala cancelada + reembolso ambos (FK direta)', ['room_id' => $room->id]);
                    $affectedRooms++;
                    continue;
                }

                // Declarar vencedor
                $winnerId = $winnerParticipant->user_id;
                $loserId = $room->participants
                    ->where('id', '!=', $winnerParticipant->id)
                    ->first()?->user_id;

                $this->processX1Winner($room, $winnerId, $loserId, $competitorId, $usersNotified);
                $winners++;
                $paymentsProcessed++;
                $affectedRooms++;
            }

            // 5️⃣ PROCESSAR CADA SALA VIA PARTICIPANTES
            foreach ($participantsToDisqualify as $loserParticipant) {
                $room = $loserParticipant->room;
                
                // Evitar processar mesma sala 2x
                if (in_array($room->id, $processedRoomIds)) continue;
                $processedRoomIds[] = $room->id;
                
                // Ignorar salas já finalizadas (caso a query não tenha filtrado corretamente)
                if (in_array($room->status, ['completed', 'closed', 'cancelled'])) continue;

                // Identificar vencedor (o outro participante pago)
                $winnerParticipant = $room->participants
                    ->where('id', '!=', $loserParticipant->id)
                    ->where('payment_status', 'paid')
                    ->first();
                
                // Se não tiver outro participante pago (sala aguardando oponente), apenas cancela
                if (!$winnerParticipant) {
                    // Host desqualificado e sala vazia -> Cancelar sala e reembolsar se já pagou
                    if ($loserParticipant->payment_status === 'paid' && $room->valor_entrada > 0) {
                        $user = $loserParticipant->user;
                        if ($user) {
                            // Creditar em "a receber" (receivable_balance), não no saldo principal
                            $user->receivable_balance = (float) ($user->receivable_balance ?? 0) + (float) $room->valor_entrada;
                            $user->save();

                            DB::table('transactions')->insert([
                                'user_id' => $user->id,
                                'amount' => $room->valor_entrada,
                                'post_balance' => $user->receivable_balance, // rastreia valores a receber
                                'charge' => 0,
                                'trx_type' => '+',
                                'trx' => getTrx(),
                                'remark' => 'x1_refund_receivable',
                                'details' => "Reembolso X1 (Sala cancelada por desqualificação - creditado em Valores a Receber) - Sala #{$room->id}",
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);

                            // Marcar pagamentos como reembolsados (a receber)
                            \App\Models\X1Payment::where('x1_room_id', $room->id)
                                ->where('user_id', $user->id)
                                ->update(['status' => 'refunded_receivable']);
                        }
                    }

                    $room->status = 'cancelled';
                    $room->closed_at = now();
                    $room->save();
                    \Log::info('🚫 Sala cancelada (host desqualificado, sem oponente)', ['room_id' => $room->id]);
                    $affectedRooms++;
                    continue;
                }

                $winnerId = $winnerParticipant->user_id;
                $loserId = $loserParticipant->user_id;
                
                \Log::info('✅ Sala X1 sendo finalizada por desqualificação', [
                    'room_id' => $room->id,
                    'winner_id' => $winnerId,
                    'loser_id' => $loserId,
                    'reason' => 'Desqualificação Competidor/Grupo'
                ]);

                // Atualizar status da sala
                $room->status = 'finished';
                $room->finished_at = now();
                $room->closed_at = now();
                $room->save(); 

                // Criar resultado manualmente
                \App\Models\X1Result::create([
                    'x1_room_id' => $room->id,
                    'winner_user_id' => $winnerId,
                    'payload' => [
                        'loser_user_id' => $loserId,
                        'prize_total' => $room->prize_total,
                        'reason' => 'disqualification',
                        'disqualified_competitor_id' => $competitorId,
                        'processed_by' => 'admin_disqualification'
                    ],
                    'processed_at' => now(),
                ]);

                // Atualizar Stats dos Usuários (Wins/Losses)
                DB::table('users')->where('id', $winnerId)->increment('x1_wins');
                DB::table('users')->where('id', $loserId)->increment('x1_losses');

                // 6️⃣ PROCESSAR PAGAMENTO (vencedor recebe o prêmio)
                $prizeAmount = (float) $room->prize_total;
                
                // Creditar vencedor (balance + total_earnings)
                DB::table('users')->where('id', $winnerId)->increment('balance', $prizeAmount);
                DB::table('users')->where('id', $winnerId)->increment('total_earnings', $prizeAmount);
                
                // Registrar transação
                DB::table('transactions')->insert([
                    'user_id' => $winnerId,
                    'amount' => $prizeAmount,
                    'post_balance' => DB::table('users')->where('id', $winnerId)->value('balance'),
                    'charge' => 0,
                    'trx_type' => '+',
                    'trx' => getTrx(),
                    'remark' => 'x1_win',
                    'details' => "Vitória X1 (Oponente desqualificado) - Sala #{$room->id}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $paymentsProcessed++;

                // 7️⃣ ATUALIZAR ESTATÍSTICAS X1
                try {
                    app(\App\Services\X1StatsService::class)->recordX1Result(
                        $room, 
                        $winnerId, 
                        $loserId, 
                        $prizeAmount, 
                        (float)$room->valor_entrada
                    );
                } catch (\Throwable $e) {
                    \Log::warning('⚠️ Erro ao atualizar stats X1', ['error' => $e->getMessage()]);
                }

                // 8️⃣ PROCESSAR COMISSÕES DE AFILIADOS
                try {
                    $totalEntry = (float) $room->valor_entrada * 2;
                    $platformFee = $totalEntry - (float) $room->prize_total;
                    
                    if ($platformFee > 0) {
                        app(\App\Services\AffiliateCommissionService::class)->processX1Commission(
                            $room->id,
                            $winnerId,
                            $loserId,
                            (float) $platformFee
                        );
                        \Log::info('💰 Comissões de afiliados processadas', [
                            'room_id' => $room->id,
                            'platform_fee' => $platformFee
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('⚠️ Erro ao processar comissões de afiliados', ['error' => $e->getMessage()]);
                }

                // Notificar usuários
                if (!in_array($winnerId, $usersNotified)) {
                    $usersNotified[] = $winnerId;
                }
                if (!in_array($loserId, $usersNotified)) {
                    $usersNotified[] = $loserId;
                }

                $winners++;
                $affectedRooms++;
            }

            \Log::info('✅ Desqualificação processada com sucesso', [
                'rooms_completed' => $affectedRooms,
                'winners' => $winners,
                'payments' => $paymentsProcessed,
                'users_notified' => count($usersNotified)
            ]);

            // 7️⃣ ATUALIZAR STATUS NO COMPETITOR_MODALIDADE (marcar como fora)
            try {
                 DB::table('competitor_modalidade')
                    ->where('competitor_id', $competitorId)
                    ->where('modalidade_id', $modalidadeId)
                    ->update([
                        'disponivel_participacao' => 0,
                        'observacoes' => DB::raw("CONCAT(COALESCE(observacoes,''), '\n[AUTO] Desqualificado em " . now() . "')")
                    ]);
                 
                 // Limpar caches de competidores da modalidade
                 \Illuminate\Support\Facades\Cache::forget("modalidade_{$modalidadeId}_competitors");
                 // Limpar cache de ligas fantasy que usam essa modalidade
                 $fantasyLeagueIds = DB::table('fantasy_leagues')
                     ->where('modalidade_id', $modalidadeId)
                     ->where('is_active', true)
                     ->pluck('id');
                 foreach ($fantasyLeagueIds as $lid) {
                     \Illuminate\Support\Facades\Cache::forget("fantasy_leagues_" . md5(json_encode([]) . '_1_0'));
                 }
            } catch (\Throwable $e) {}

            // 8️⃣ DISPARAR EVENTO WEBSOCKET
            try {
                broadcast(new LiveTransmissionUpdated([
                    'type' => 'competitor_disqualified',
                    'competitor_id' => $competitorId,
                    'competitor_name' => $competitor->nome,
                    'rodeio_id' => $rodeioId,
                    'modalidade_id' => $modalidadeId,
                    'x1_rooms_affected' => $affectedRooms
                ]));
            } catch (\Throwable $e) {
                \Log::warning('⚠️ Erro ao enviar evento websocket', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => "Competidor {$competitor->nome} desqualificado com sucesso",
                'affected' => [
                    'x1_rooms_completed' => $affectedRooms,
                    'winners' => $winners,
                    'payments_processed' => $paymentsProcessed,
                    'users_notified' => count($usersNotified)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao desqualificar competidor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar desqualificação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar classificatória - marcar todos competidores como finalizados
     */
    public function finalizeClassificatoria(Request $request)
    {
        $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'modalidade_id' => 'required|exists:modalidades,id'
        ]);

        try {
            $rodeioId = (int) $request->rodeio_id;
            $modalidadeId = (int) $request->modalidade_id;

            $count = $this->statsService->finalizeClassificatoria($rodeioId, $modalidadeId);

            \Log::info('✅ Classificatória finalizada', [
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'competitors_finalized' => $count
            ]);

            return response()->json([
                'success' => true,
                'message' => "Classificatória finalizada! {$count} competidor(es) marcado(s).",
                'competitors_finalized' => $count
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao finalizar classificatória', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar classificatória: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar se tem classificatória não finalizada
     */
    public function checkUnfinalizedClassificatoria(Request $request)
    {
        $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'modalidade_id' => 'required|exists:modalidades,id'
        ]);

        try {
            $rodeioId = (int) $request->rodeio_id;
            $modalidadeId = (int) $request->modalidade_id;

            $count = CompetitorContextStat::where('rodeio_id', $rodeioId)
                ->where('modalidade_id', $modalidadeId)
                ->where('tipo_fase', 'classificatoria')
                ->where('is_finalized', false)
                ->count();

            return response()->json([
                'success' => true,
                'has_unfinalized' => $count > 0,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'has_unfinalized' => false,
                'count' => 0
            ]);
        }
    }

    /**
     * Finalizar divisão - marcar todos competidores de uma divisão como finalizados
     */
    public function finalizeDivisao(Request $request)
    {
        $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'modalidade_id' => 'required|exists:modalidades,id',
            'divisao' => 'required|string'
        ]);

        try {
            $rodeioId = (int) $request->rodeio_id;
            $modalidadeId = (int) $request->modalidade_id;
            $divisao = (string) $request->divisao;

            $count = $this->statsService->finalizeDivisao($rodeioId, $modalidadeId, $divisao);

            \Log::info('✅ Divisão finalizada', [
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $modalidadeId,
                'divisao' => $divisao,
                'competitors_finalized' => $count
            ]);

            // 🎯 ATUALIZAR RANKING FANTASY APÓS FINALIZAR
            try {
                // Buscar leagues ativas e atualizar rankings
                $leagues = \App\Models\FantasyLeague::query()
                    ->where('rodeio_id', $rodeioId)
                    ->where('modalidade_id', $modalidadeId)
                    ->where('status', 'active')
                    ->get();

                foreach ($leagues as $league) {
                    $this->fantasyService->updateRanking($league->id);
                }
            } catch (\Throwable $e) {
                \Log::warning('Erro ao atualizar rankings Fantasy', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => "Divisão '{$divisao}' finalizada! {$count} competidor(es) marcado(s).",
                'competitors_finalized' => $count
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao finalizar divisão', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar divisão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processa vitória X1 por desqualificação
     * Usado internamente por disqualifyCompetitor()
     */
    private function processX1Winner($room, int $winnerId, ?int $loserId, int $disqualifiedCompetitorId, array &$usersNotified): void
    {
        // Atualizar status da sala
        $room->status = 'finished';
        $room->finished_at = now();
        $room->closed_at = now();
        $room->save();

        // Criar resultado
        \App\Models\X1Result::create([
            'x1_room_id' => $room->id,
            'winner_user_id' => $winnerId,
            'payload' => [
                'loser_user_id' => $loserId,
                'prize_total' => $room->prize_total,
                'reason' => 'disqualification',
                'disqualified_competitor_id' => $disqualifiedCompetitorId,
                'processed_by' => 'admin_disqualification'
            ],
            'processed_at' => now(),
        ]);

        // Atualizar Stats dos Usuários
        DB::table('users')->where('id', $winnerId)->increment('x1_wins');
        if ($loserId) {
            DB::table('users')->where('id', $loserId)->increment('x1_losses');
        }

        // Processar pagamento
        $prizeAmount = (float) $room->prize_total;
        
        // Creditar vencedor (balance + total_earnings)
        DB::table('users')->where('id', $winnerId)->increment('balance', $prizeAmount);
        DB::table('users')->where('id', $winnerId)->increment('total_earnings', $prizeAmount);
        
        // Registrar transação
        DB::table('transactions')->insert([
            'user_id' => $winnerId,
            'amount' => $prizeAmount,
            'post_balance' => DB::table('users')->where('id', $winnerId)->value('balance'),
            'charge' => 0,
            'trx_type' => '+',
            'trx' => getTrx(),
            'remark' => 'x1_win',
            'details' => "Vitória X1 (Oponente desqualificado) - Sala #{$room->id}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 🔗 PROCESSAR COMISSÕES DE AFILIADOS
        try {
            $totalEntry = (float) $room->valor_entrada * 2;
            $platformFee = $totalEntry - (float) $room->prize_total;
            
            if ($platformFee > 0) {
                app(\App\Services\AffiliateCommissionService::class)->processX1Commission(
                    $room->id,
                    $winnerId,
                    $loserId,
                    (float) $platformFee
                );
                \Log::info('💰 Comissões de afiliados processadas', [
                    'room_id' => $room->id,
                    'platform_fee' => $platformFee
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('⚠️ Erro ao processar comissões de afiliados', ['error' => $e->getMessage()]);
        }

        // Atualizar estatísticas X1
        try {
            app(\App\Services\X1StatsService::class)->recordX1Result(
                $room,
                $winnerId,
                $loserId ?? 0,
                $prizeAmount,
                (float)$room->valor_entrada
            );
        } catch (\Throwable $e) {
            \Log::warning('⚠️ Erro ao atualizar stats X1', ['error' => $e->getMessage()]);
        }

        // Rastrear notificações
        if (!in_array($winnerId, $usersNotified)) {
            $usersNotified[] = $winnerId;
        }
        if ($loserId && !in_array($loserId, $usersNotified)) {
            $usersNotified[] = $loserId;
        }

        \Log::info('✅ Sala X1 finalizada por desqualificação (FK)', [
            'room_id' => $room->id,
            'winner_id' => $winnerId,
            'loser_id' => $loserId,
            'prize' => $prizeAmount
        ]);
    }
}
