<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modalidade;
use App\Models\ModalidadeCompetitorGroup;
use App\Models\Rodeio;
use App\Models\Competitor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModalidadeController extends Controller
{
    private function notifyAttachedCompetitors(Modalidade $modalidade, array $syncData, array $competitorIds, bool $isClassificatoria = false): void
    {
        if (empty($competitorIds) || !Schema::hasTable('competitor_follow_events')) {
            return;
        }

        try {
            $competitors = Competitor::query()->whereIn('id', $competitorIds)->get();
            $rodeioName = $modalidade->rodeio?->name ?? 'rodeio';

            foreach ($competitors as $competitor) {
                $divisaoLabel = trim((string) ($syncData[$competitor->id]['divisao'] ?? ''));
                $context = $modalidade->nome . ' • ' . $rodeioName;
                if ($divisaoLabel !== '') {
                    $context .= ' • ' . $divisaoLabel;
                }

                app(\App\Services\CompetitorFollowerService::class)->createEvent($competitor, 'modalidade_entry', [
                    'title' => $competitor->nome . ' entrou em nova disputa',
                    'message' => $competitor->nome . ' foi confirmado em ' . $context . '.',
                    'cta_label' => 'Acompanhar competidor',
                    'cta_url' => route('hub.stats', ['competitor' => $competitor->id]),
                    'rodeio_id' => $modalidade->rodeio_id,
                    'modalidade_id' => $modalidade->id,
                    'source_key' => 'modalidade_attach:' . $modalidade->id . ':' . $competitor->id . ':' . md5($divisaoLabel),
                    'metadata' => [
                        'divisao' => $divisaoLabel,
                        'is_classificatoria' => $isClassificatoria,
                    ],
                ]);
            }
        } catch (\Throwable $exception) {
            \Log::warning('[Modalidade] Falha ao notificar entrada de competidor', [
                'modalidade_id' => $modalidade->id,
                'competitor_ids' => $competitorIds,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyAttachedGroups(Modalidade $modalidade, array $groupIds): void
    {
        if (empty($groupIds) || !Schema::hasTable('modalidade_competitor_groups') || !Schema::hasTable('competitor_follow_events')) {
            return;
        }

        try {
            $groups = ModalidadeCompetitorGroup::with(['members' => function ($query) {
                    $query->select('competitors.id', 'competitors.nome');
                }])
                ->where('modalidade_id', $modalidade->id)
                ->whereIn('id', $groupIds)
                ->get();

            $rodeioName = $modalidade->rodeio?->name ?? 'rodeio';

            foreach ($groups as $group) {
                $groupName = trim((string) ($group->nome ?: $group->members->pluck('nome')->implode(' + ')));
                $divisaoLabel = trim((string) ($group->divisao ?? ''));
                $context = $modalidade->nome . ' • ' . $rodeioName;
                if ($divisaoLabel !== '') {
                    $context .= ' • ' . $divisaoLabel;
                }

                foreach ($group->members as $competitor) {
                    app(\App\Services\CompetitorFollowerService::class)->createEvent($competitor, 'modalidade_group_entry', [
                        'title' => $competitor->nome . ' entrou em equipe',
                        'message' => $competitor->nome . ' foi vinculado ao grupo ' . $groupName . ' em ' . $context . '.',
                        'cta_label' => 'Acompanhar competidor',
                        'cta_url' => route('hub.stats', ['competitor' => $competitor->id]),
                        'rodeio_id' => $modalidade->rodeio_id,
                        'modalidade_id' => $modalidade->id,
                        'source_key' => 'modalidade_group_attach:' . $modalidade->id . ':' . $group->id . ':' . $competitor->id,
                        'metadata' => [
                            'group_id' => $group->id,
                            'group_name' => $groupName,
                            'divisao' => $divisaoLabel,
                        ],
                    ]);
                }
            }
        } catch (\Throwable $exception) {
            \Log::warning('[Modalidade] Falha ao notificar entrada em grupo', [
                'modalidade_id' => $modalidade->id,
                'group_ids' => $groupIds,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function getTipoParticipacaoOptions(): array
    {
        return [
            'individual' => ['label' => 'Individual', 'size' => 1],
            'dupla' => ['label' => 'Dupla', 'size' => 2],
            'trio' => ['label' => 'Trio', 'size' => 3],
            'quarteto' => ['label' => 'Quarteto', 'size' => 4],
            'quinteto' => ['label' => 'Quinteto', 'size' => 5],
            'sexteto' => ['label' => 'Sexteto', 'size' => 6],
            'septeto' => ['label' => 'Septeto', 'size' => 7],
            'octeto' => ['label' => 'Octeto', 'size' => 8],
            'noneto' => ['label' => 'Noneto', 'size' => 9],
            'deceto' => ['label' => 'Deceto', 'size' => 10],
        ];
    }

    private function resolveTamanhoEquipe(string $tipo): int
    {
        $options = $this->getTipoParticipacaoOptions();
        return (int) ($options[$tipo]['size'] ?? 1);
    }

    private function formatTipoLabel(?string $tipo, ?int $tamanhoEquipe): string
    {
        $options = $this->getTipoParticipacaoOptions();
        if ($tipo && isset($options[$tipo])) {
            $size = (int) ($options[$tipo]['size'] ?? 1);
            return $options[$tipo]['label'] . ' (' . $size . ')';
        }

        $size = (int) ($tamanhoEquipe ?: 1);
        return 'Individual (' . $size . ')';
    }

    private function createGroupForModalidade(Modalidade $modalidade, array $data): ModalidadeCompetitorGroup
    {
        $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);
        if ($teamSize <= 1) {
            throw new \InvalidArgumentException('Modalidade individual não exige grupos.');
        }

        $ids = array_values(array_unique(array_map('intval', $data['competitor_ids'] ?? [])));
        if (count($ids) !== $teamSize) {
            throw new \InvalidArgumentException("Selecione exatamente {$teamSize} competidores para formar o grupo.");
        }

        $divisao = trim((string) ($data['divisao'] ?? ''));
        $isClassificatoria = filter_var($data['is_classificatoria'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($modalidade->tem_divisoes && !$isClassificatoria) {
            $allowed = $this->normalizeDivisoes($modalidade->divisoes ?? []);
            if ($divisao === '') {
                throw new \InvalidArgumentException('Selecione a divisão.');
            }
            $allowedLower = array_map(fn ($v) => mb_strtolower($v), $allowed);
            if (!in_array(mb_strtolower($divisao), $allowedLower, true)) {
                throw new \InvalidArgumentException('Divisão inválida.');
            }
        } else {
            $divisao = '';
        }

        if (Schema::hasTable('modalidade_competitor_groups') && Schema::hasTable('modalidade_competitor_group_members')) {
            $alreadyGrouped = DB::table('modalidade_competitor_group_members as m')
                ->join('modalidade_competitor_groups as g', 'g.id', '=', 'm.group_id')
                ->where('g.modalidade_id', $modalidade->id)
                ->when($modalidade->tem_divisoes && $divisao !== '', function ($q) use ($divisao) {
                    $q->where('g.divisao', $divisao);
                })
                ->whereIn('m.competitor_id', $ids)
                ->exists();

            if ($alreadyGrouped) {
                throw new \InvalidArgumentException('Alguns competidores já estão em outro grupo.');
            }
        }

        $groupName = trim((string) ($data['nome'] ?? ''));
        if ($groupName === '') {
            $memberNames = Competitor::whereIn('id', $ids)->orderBy('nome')->pluck('nome')->all();
            $groupName = mb_substr(implode(' + ', $memberNames), 0, 120);
        }

        $groupStatus = $isClassificatoria ? 'classificatoria' : 'ativo';
        $group = ModalidadeCompetitorGroup::create([
            'modalidade_id' => $modalidade->id,
            'divisao' => $divisao !== '' ? $divisao : null,
            'nome' => $groupName,
            'tamanho' => $teamSize,
            'status' => $groupStatus,
        ]);

        $group->members()->sync($ids);

        return $group;
    }

    /**
     * Normaliza divisões - suporta formato antigo (array de strings) e novo (array de objetos)
     */
    private function normalizeDivisoes(?array $divisoes): array
    {
        if (empty($divisoes)) {
            return [];
        }
        
        $items = [];
        $seen = [];
        
        foreach ($divisoes as $v) {
            // Novo formato: array de objetos com nome, tipo_premio, valor_premio, descricao_premio
            if (is_array($v) && isset($v['nome'])) {
                $nome = trim((string) $v['nome']);
                if ($nome === '') {
                    continue;
                }
                $key = mb_strtolower($nome);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $items[] = $v;
            }
            // Formato antigo: string simples
            elseif (is_string($v)) {
                $s = trim($v);
                if ($s === '') {
                    continue;
                }
                $key = mb_strtolower($s);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                // Converter para novo formato
                $items[] = [
                    'nome' => $s,
                    'tipo_premio' => 'dinheiro',
                    'valor_premio' => null,
                    'descricao_premio' => null,
                ];
            }
        }

        return $items;
    }
    
    /**
     * Extrai apenas os nomes das divisões (para compatibilidade com código legado)
     */
    public static function getDivisoesNomes(?array $divisoes): array
    {
        if (empty($divisoes)) {
            return [];
        }
        
        $nomes = [];
        foreach ($divisoes as $v) {
            if (is_array($v) && isset($v['nome'])) {
                $nomes[] = $v['nome'];
            } elseif (is_string($v)) {
                $nomes[] = $v;
            }
        }
        
        return $nomes;
    }

    public function index(Request $request)
    {
        $pageTitle = 'Modalidades';
        $query = Modalidade::with('rodeio');

        if (Schema::hasTable('competitor_modalidade')) {
            $query->withCount('competitors');
        }

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where('nome', 'like', '%' . $search . '%');
        }
        $rodeioId = (string) $request->input('rodeio_id', '');
        if ($rodeioId !== '') {
            $query->where('rodeio_id', $rodeioId);
        }
        $status = (string) $request->input('status', '');
        if ($status !== '') {
            $query->where('status', $status);
        }

        $modalidades = $query
            ->orderBy('rodeio_id')
            ->orderBy('inicio')
            ->orderBy('nome')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->only('q', 'rodeio_id', 'status'));

        $rodeios = Rodeio::orderBy('name')->get();

        return view('admin.modalidades.index', compact('modalidades', 'pageTitle', 'rodeios'));
    }

    public function create()
    {
        $pageTitle = 'Criar Modalidade';
        $rodeios = Rodeio::orderBy('name')->get();
        $tipoOptions = $this->getTipoParticipacaoOptions();
        return view('admin.modalidades.create', compact('rodeios', 'pageTitle', 'tipoOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'nome' => 'required|string|max:255',
            'inicio' => 'required|date',
            'tipo_premio' => 'required|in:dinheiro,fisico',
            'tipo_participacao' => 'required|in:individual,dupla,trio,quarteto,quinteto,sexteto,septeto,octeto,noneto,deceto',
            'valor_premio' => 'nullable|numeric|min:0',
            'descricao_premio' => 'nullable|string|max:255',
            'status' => 'nullable|in:ativo,inativo,finalizado,programado,ao_vivo,pausado',
            'tem_divisoes' => 'nullable|boolean',
            // Novo formato: divisões com premiação
            'divisoes_data' => 'nullable|array',
            'divisoes_data.*.nome' => 'required_with:divisoes_data|string|max:100',
            'divisoes_data.*.tipo_premio' => 'nullable|in:dinheiro,fisico',
            'divisoes_data.*.valor_premio' => 'nullable|numeric|min:0',
            'divisoes_data.*.descricao_premio' => 'nullable|string|max:255',
        ]);

        // Status padrão: programado (já que tem data/hora de início)
        $validated['status'] = $validated['status'] ?? 'programado';

        $temDivisoes = (bool) ($validated['tem_divisoes'] ?? false);
        
        // Processar divisões no novo formato
        $divisoesData = $validated['divisoes_data'] ?? [];
        $divisoes = [];
        
        if ($temDivisoes && !empty($divisoesData)) {
            foreach ($divisoesData as $div) {
                if (!empty($div['nome'])) {
                    $divisoes[] = [
                        'nome' => trim($div['nome']),
                        'tipo_premio' => $div['tipo_premio'] ?? 'dinheiro',
                        'valor_premio' => $div['valor_premio'] ?? null,
                        'descricao_premio' => $div['descricao_premio'] ?? null,
                    ];
                }
            }
        }

        if ($temDivisoes && empty($divisoes)) {
            return back()->withErrors(['divisoes_data' => 'Adicione ao menos 1 divisão.'])->withInput();
        }

        $validated['tem_divisoes'] = $temDivisoes;
        $validated['divisoes'] = $temDivisoes ? $divisoes : null;
        $validated['tamanho_equipe'] = $this->resolveTamanhoEquipe($validated['tipo_participacao']);
        
        // Remover divisoes_data do validated pois não é coluna do banco
        unset($validated['divisoes_data']);

        $modalidade = Modalidade::create($validated);

        return redirect()->route('admin.modalidades.index')->with('notify', [['success', 'Modalidade criada com sucesso!']]);
    }

    public function edit(Modalidade $modalidade)
    {
        $pageTitle = 'Editar Modalidade';
        $rodeios = Rodeio::orderBy('name')->get();
        $tipoOptions = $this->getTipoParticipacaoOptions();
        return view('admin.modalidades.edit', compact('modalidade', 'rodeios', 'pageTitle', 'tipoOptions'));
    }

    public function update(Request $request, Modalidade $modalidade)
    {
        $validated = $request->validate([
            'rodeio_id' => 'required|exists:rodeios,id',
            'nome' => 'required|string|max:255',
            'inicio' => 'required|date',
            'tipo_premio' => 'required|in:dinheiro,fisico',
            'tipo_participacao' => 'required|in:individual,dupla,trio,quarteto,quinteto,sexteto,septeto,octeto,noneto,deceto',
            'valor_premio' => 'nullable|numeric|min:0',
            'descricao_premio' => 'nullable|string|max:255',
            'status' => 'required|in:ativo,inativo,finalizado,programado,ao_vivo,pausado',
            'tem_divisoes' => 'nullable|boolean',
            // Novo formato: divisões com premiação
            'divisoes_data' => 'nullable|array',
            'divisoes_data.*.nome' => 'required_with:divisoes_data|string|max:100',
            'divisoes_data.*.tipo_premio' => 'nullable|in:dinheiro,fisico',
            'divisoes_data.*.valor_premio' => 'nullable|numeric|min:0',
            'divisoes_data.*.descricao_premio' => 'nullable|string|max:255',
        ]);

        $temDivisoes = (bool) ($validated['tem_divisoes'] ?? false);
        
        // Processar divisões no novo formato
        $divisoesData = $validated['divisoes_data'] ?? [];
        $divisoes = [];
        
        if ($temDivisoes && !empty($divisoesData)) {
            foreach ($divisoesData as $div) {
                if (!empty($div['nome'])) {
                    $divisoes[] = [
                        'nome' => trim($div['nome']),
                        'tipo_premio' => $div['tipo_premio'] ?? 'dinheiro',
                        'valor_premio' => $div['valor_premio'] ?? null,
                        'descricao_premio' => $div['descricao_premio'] ?? null,
                    ];
                }
            }
        }

        if ($temDivisoes && empty($divisoes)) {
            return back()->withErrors(['divisoes_data' => 'Adicione ao menos 1 divisão.'])->withInput();
        }

        $validated['tem_divisoes'] = $temDivisoes;
        $validated['divisoes'] = $temDivisoes ? $divisoes : null;
        $validated['tamanho_equipe'] = $this->resolveTamanhoEquipe($validated['tipo_participacao']);
        
        // Remover divisoes_data do validated pois não é coluna do banco
        unset($validated['divisoes_data']);

        $modalidade->update($validated);

        return redirect()->route('admin.modalidades.index')->with('notify', [['success', 'Modalidade atualizada com sucesso!']]);
    }

    public function destroy(Modalidade $modalidade)
    {
        $modalidade->delete();
        return redirect()->route('admin.modalidades.index')->with('notify', [['success', 'Modalidade removida com sucesso!']]);
    }

    public function show(Modalidade $modalidade)
    {
        $pageTitle = 'Detalhes da Modalidade';
        $modalidade->load('rodeio');
        return view('admin.modalidades.show', compact('modalidade', 'pageTitle'));
    }

    /**
     * Tela em popout para gerenciar competidores vinculados à modalidade.
     */
    public function competitorsPopout(Modalidade $modalidade)
    {
        $modalidade->load('rodeio');
        $pageTitle = 'Competidores • ' . $modalidade->nome;
        $pageSubtitle = 'Modalidade #' . $modalidade->id . ' • Rodeio: ' . ($modalidade->rodeio->name ?? '-');

        return view('admin.modalidades.competitors_popout', compact('modalidade', 'pageTitle', 'pageSubtitle'));
    }

    /**
     * Lista competidores disponíveis e já vinculados para a modalidade (AJAX JSON).
     */
    public function competitors(Modalidade $modalidade)
    {
        try {
            $attached = $modalidade->competitors()->select('competitors.id', 'competitors.nome')->get();

            // Disponíveis: todos ativos que não estão vinculados a esta modalidade
            $available = Competitor::select('id', 'nome')
                ->whereNotIn('id', $attached->pluck('id'))
                ->orderBy('nome')
                ->get();

            $attachedOut = $attached->map(function ($c) {
                $divisao = '';
                try {
                    $divisao = (string) ($c->pivot->divisao ?? '');
                } catch (\Throwable $e) {
                    $divisao = '';
                }

                return [
                    'id' => (int) $c->id,
                    'nome' => (string) $c->nome,
                    'divisao' => $divisao,
                ];
            });

            $availableOut = $available->map(fn ($c) => [
                'id' => (int) $c->id,
                'nome' => (string) $c->nome,
            ]);

            $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);
            $groupMode = $teamSize > 1;
            $groupsPayload = [
                'attached' => [],
                'available' => [],
            ];

            if ($groupMode && Schema::hasTable('modalidade_competitor_groups') && Schema::hasTable('modalidade_competitor_group_members')) {
                $groups = ModalidadeCompetitorGroup::with(['members' => function ($q) {
                        $q->select('competitors.id', 'competitors.nome');
                    }])
                    ->where('modalidade_id', $modalidade->id)
                    ->where('status', '!=', 'desqualificado') // Excluir grupos desqualificados
                    ->orderBy('id', 'desc')
                    ->get();

                $attachedMap = [];
                if (Schema::hasTable('competitor_modalidade')) {
                    $attachedRows = DB::table('competitor_modalidade')
                        ->where('modalidade_id', $modalidade->id)
                        ->select(['competitor_id', 'divisao'])
                        ->get();
                    foreach ($attachedRows as $row) {
                        $attachedMap[(int) $row->competitor_id] = (string) ($row->divisao ?? '');
                    }
                }

                foreach ($groups as $group) {
                    $members = $group->members ?? collect();
                    $memberIds = $members->pluck('id')->map(fn ($v) => (int) $v)->all();
                    $groupDivisao = (string) ($group->divisao ?? '');

                    $allAttached = true;
                    foreach ($memberIds as $id) {
                        if (!array_key_exists($id, $attachedMap)) {
                            $allAttached = false;
                            break;
                        }

                        if ($modalidade->tem_divisoes && $groupDivisao !== '') {
                            $currentDiv = (string) ($attachedMap[$id] ?? '');
                            if ($currentDiv !== $groupDivisao) {
                                $allAttached = false;
                                break;
                            }
                        }
                    }

                    $groupName = $group->nome ?: $members->pluck('nome')->implode(' + ');
                    $payload = [
                        'id' => (int) $group->id,
                        'nome' => (string) ($groupName ?: ('Grupo #' . $group->id)),
                        'divisao' => $groupDivisao,
                        'members' => $members->map(function ($m) {
                            return [
                                'id' => (int) $m->id,
                                'nome' => (string) $m->nome,
                            ];
                        })->values()->all(),
                    ];

                    if ($allAttached) {
                        $groupsPayload['attached'][] = $payload;
                    } else {
                        $groupsPayload['available'][] = $payload;
                    }
                }
            }

            // Verificar se há pontuações registradas (resultados do live-transmission)
            $hasScores = false;
            try {
                $hasScores = \DB::table('competitor_scoring_logs')
                    ->where('modalidade_id', $modalidade->id)
                    ->exists();
            } catch (\Throwable $e) {
                $hasScores = false;
            }

            // Verificar se há grupos na classificatória (sem divisão atribuída)
            $hasClassificatoriaGroups = false;
            if ($groupMode && Schema::hasTable('modalidade_competitor_groups')) {
                $hasClassificatoriaGroups = ModalidadeCompetitorGroup::where('modalidade_id', $modalidade->id)
                    ->where(function ($q) {
                        $q->where('status', 'classificatoria')
                          ->orWhereNull('divisao')
                          ->orWhere('divisao', '');
                    })
                    ->exists();
            }

            return response()->json([
                'modalidade' => [
                    'id' => (int) $modalidade->id,
                    'tem_divisoes' => (bool) ($modalidade->tem_divisoes ?? false),
                    'divisoes' => $modalidade->divisoes_nomes ?? [],
                    'tipo_participacao' => (string) ($modalidade->tipo_participacao ?? 'individual'),
                    'tamanho_equipe' => (int) ($modalidade->tamanho_equipe ?? 1),
                    'status' => (string) ($modalidade->status ?? 'ativo'),
                    'has_scores' => $hasScores,
                    'has_classificatoria_groups' => $hasClassificatoriaGroups,
                ],
                'attached' => $attachedOut,
                'available' => $availableOut,
                'group_mode' => $groupMode,
                'groups' => $groupsPayload,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Anexa (sincroniza) competidores à modalidade.
     */
    public function attachCompetitors(Request $request, Modalidade $modalidade)
    {
        try {
            $previousCompetitorIds = $modalidade->competitors()->pluck('competitors.id')->map(fn ($id) => (int) $id)->all();
            $previousAttachedGroupIds = [];
            $modalidade->loadMissing('rodeio');

            $data = $request->validate([
                'competitor_ids' => 'array',
                'competitor_ids.*' => 'integer|exists:competitors,id',
                'group_ids' => 'array',
                'group_ids.*' => 'integer|exists:modalidade_competitor_groups,id',
                'competitor_divisoes' => 'nullable|array',
                'group_divisoes' => 'nullable|array',
                'is_classificatoria' => 'nullable|boolean',
            ]);

            $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);
            $groupMode = $teamSize > 1;
            $isClassificatoria = filter_var($data['is_classificatoria'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $ids = $data['competitor_ids'] ?? [];
            $groupIds = $data['group_ids'] ?? [];

            $temDivisoes = (bool) ($modalidade->tem_divisoes ?? false);
            $allowedDivisoes = $this->normalizeDivisoes($modalidade->divisoes ?? []);
            $mapDivisoes = is_array($data['competitor_divisoes'] ?? null) ? $data['competitor_divisoes'] : [];
            $groupDivisoes = is_array($data['group_divisoes'] ?? null) ? $data['group_divisoes'] : [];

            // Se tem divisões configuradas mas lista vazia, só é erro se NÃO for classificatória
            if ($temDivisoes && $allowedDivisoes === [] && !$isClassificatoria) {
                return response()->json([
                    'error' => true,
                    'message' => 'Modalidade exige divisões, mas não possui lista configurada.',
                ], 422);
            }

            $normalizedDivById = [];

            if ($groupMode) {
                if (!Schema::hasTable('modalidade_competitor_groups') || !Schema::hasTable('modalidade_competitor_group_members')) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Banco de dados ainda não está preparado para grupos (migração pendente).',
                    ], 500);
                }

                $previousAttachedGroupIds = ModalidadeCompetitorGroup::query()
                    ->where('modalidade_id', $modalidade->id)
                    ->whereIn('id', $groupIds)
                    ->get()
                    ->filter(function (ModalidadeCompetitorGroup $group) use ($modalidade) {
                        $memberIds = $group->members()->pluck('competitors.id')->map(fn ($id) => (int) $id)->all();
                        if (empty($memberIds)) {
                            return false;
                        }

                        $attachedCount = DB::table('competitor_modalidade')
                            ->where('modalidade_id', $modalidade->id)
                            ->whereIn('competitor_id', $memberIds)
                            ->count();

                        return $attachedCount === count($memberIds);
                    })
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                if (empty($groupIds)) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Selecione ao menos um grupo antes de salvar.',
                    ], 422);
                }

                $groups = ModalidadeCompetitorGroup::with(['members' => function ($q) {
                        $q->select('competitors.id', 'competitors.nome');
                    }])
                    ->where('modalidade_id', $modalidade->id)
                    ->whereIn('id', $groupIds)
                    ->get();

                $ids = [];
                foreach ($groups as $group) {
                    // Verificar se há divisão no request ou no grupo
                    $requestDivisao = $groupDivisoes[(string) $group->id] ?? $groupDivisoes[(int) $group->id] ?? null;
                    $groupDivisao = $requestDivisao !== null ? trim((string) $requestDivisao) : (string) ($group->divisao ?? '');
                    
                    // Atualizar divisão do grupo se foi passada no request
                    if ($requestDivisao !== null && $requestDivisao !== $group->divisao) {
                        $group->divisao = $groupDivisao;
                        $group->save();
                    }
                    
                    // Na classificatória, não exigir divisão
                    if ($temDivisoes && !$isClassificatoria) {
                        if ($groupDivisao === '') {
                            return response()->json([
                                'error' => true,
                                'message' => 'Grupo "' . $group->nome . '" está sem divisão. Atribua uma divisão antes de salvar.',
                            ], 422);
                        }
                    }

                    foreach ($group->members as $member) {
                        $ids[] = (int) $member->id;
                        if ($temDivisoes && $groupDivisao !== '') {
                            $normalizedDivById[(int) $member->id] = $groupDivisao;
                        }
                    }
                }

                $ids = array_values(array_unique($ids));
            } else if ($temDivisoes && !$isClassificatoria) {
                // Somente exige divisão se NÃO for classificatória
                $allowedSet = [];
                foreach ($allowedDivisoes as $d) {
                    $nome = is_array($d) ? ($d['nome'] ?? '') : (string) $d;
                    $allowedSet[mb_strtolower($nome)] = $nome;
                }

                foreach ($ids as $id) {
                    $raw = $mapDivisoes[(string) $id] ?? $mapDivisoes[(int) $id] ?? null;
                    $val = trim((string) ($raw ?? ''));
                    if ($val === '') {
                        return response()->json([
                            'error' => true,
                            'message' => 'Selecione a divisão para todos os competidores antes de salvar.',
                        ], 422);
                    }
                    $key = mb_strtolower($val);
                    if (!isset($allowedSet[$key])) {
                        return response()->json([
                            'error' => true,
                            'message' => "Divisão inválida: {$val}",
                        ], 422);
                    }
                    $normalizedDivById[(int) $id] = $allowedSet[$key];
                }
            }

            $hasPivotDivisao = Schema::hasTable('competitor_modalidade') && Schema::hasColumn('competitor_modalidade', 'divisao');
            if ($temDivisoes && !$hasPivotDivisao) {
                return response()->json([
                    'error' => true,
                    'message' => 'Banco de dados ainda não está preparado para divisões (migração pendente).',
                ], 500);
            }

            // Sincroniza mantendo campos pivot padrão
            $syncData = [];
            foreach ($ids as $id) {
                $syncData[$id] = [
                    'divisao' => ($temDivisoes && $hasPivotDivisao) ? ($normalizedDivById[(int) $id] ?? '') : '',
                    'status' => 'inscrito',
                    'disponivel_participacao' => true,
                ];
            }

            DB::transaction(function () use ($modalidade, $syncData, $groupMode, $groupIds) {
                $modalidade->competitors()->sync($syncData);

                // No modo grupo, deletar grupos que foram removidos da seleção
                if ($groupMode) {
                    ModalidadeCompetitorGroup::where('modalidade_id', $modalidade->id)
                        ->whereNotIn('id', $groupIds)
                        ->delete();
                }
            });

            $newCompetitorIds = array_values(array_diff(array_map('intval', array_keys($syncData)), $previousCompetitorIds));
            if (!empty($newCompetitorIds)) {
                $this->notifyAttachedCompetitors($modalidade, $syncData, $newCompetitorIds, $isClassificatoria);
            }

            if ($groupMode && !empty($groupIds)) {
                $newAttachedGroupIds = array_values(array_diff(array_map('intval', $groupIds), $previousAttachedGroupIds));
                if (!empty($newAttachedGroupIds)) {
                    $this->notifyAttachedGroups($modalidade, $newAttachedGroupIds);
                }
            }

            return response()->json(['message' => 'Competidores vinculados com sucesso']);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gerenciar grupos (equipes) de competidores para modalidade.
     */
    public function groups(Request $request, Modalidade $modalidade)
    {
        $pageTitle = 'Grupos • ' . $modalidade->nome;
        $tipoLabel = $this->formatTipoLabel($modalidade->tipo_participacao, $modalidade->tamanho_equipe);

        $divisao = '';
        if ($modalidade->tem_divisoes) {
            $divisao = trim((string) $request->get('divisao', ''));
        }

        $competitors = Competitor::query()->select('id', 'nome')->orderBy('nome')->get();

        $groupedIds = [];
        if (Schema::hasTable('modalidade_competitor_groups') && Schema::hasTable('modalidade_competitor_group_members')) {
            $groupedIds = DB::table('modalidade_competitor_group_members as m')
                ->join('modalidade_competitor_groups as g', 'g.id', '=', 'm.group_id')
                ->where('g.modalidade_id', $modalidade->id)
                ->where('g.status', '!=', 'desqualificado') // Excluir grupos desqualificados
                ->when($modalidade->tem_divisoes && $divisao !== '', function ($q) use ($divisao) {
                    $q->where('g.divisao', $divisao);
                })
                ->pluck('m.competitor_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $availableCompetitors = $competitors;

        $groups = ModalidadeCompetitorGroup::with(['members' => function ($q) {
                $q->select('competitors.id', 'competitors.nome');
            }])
            ->where('modalidade_id', $modalidade->id)
            ->where('status', '!=', 'desqualificado') // Excluir grupos desqualificados
            ->when($modalidade->tem_divisoes && $divisao !== '', function ($q) use ($divisao) {
                $q->where('divisao', $divisao);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.modalidades.groups', compact(
            'modalidade',
            'pageTitle',
            'tipoLabel',
            'divisao',
            'groups',
            'availableCompetitors',
            'groupedIds'
        ));
    }

    public function storeGroup(Request $request, Modalidade $modalidade)
    {
        $data = $request->validate([
            'competitor_ids' => 'required|array',
            'competitor_ids.*' => 'integer|exists:competitors,id',
            'divisao' => 'nullable|string|max:60',
            'nome' => 'nullable|string|max:120',
            'is_classificatoria' => 'nullable|boolean',
        ]);
        $divisao = trim((string) ($data['divisao'] ?? ''));
        try {
            $this->createGroupForModalidade($modalidade, $data);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['competitor_ids' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.modalidades.groups', ['modalidade' => $modalidade->id, 'divisao' => $divisao])
            ->with('notify', [['success', 'Grupo criado com sucesso!']]);
    }

    public function storeGroupJson(Request $request, Modalidade $modalidade)
    {
        $validator = Validator::make($request->all(), [
            'competitor_ids' => 'required|array',
            'competitor_ids.*' => 'integer|exists:competitors,id',
            'divisao' => 'nullable|string|max:60',
            'nome' => 'nullable|string|max:120',
            'is_classificatoria' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first() ?: 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $group = $this->createGroupForModalidade($modalidade, $data);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Grupo criado com sucesso!',
            'group' => [
                'id' => (int) $group->id,
                'nome' => (string) ($group->nome ?? ''),
            ],
        ]);
    }

    public function destroyGroup(Modalidade $modalidade, ModalidadeCompetitorGroup $group)
    {
        if ((int) $group->modalidade_id !== (int) $modalidade->id) {
            abort(404);
        }

        $group->delete();

        return redirect()
            ->route('admin.modalidades.groups', ['modalidade' => $modalidade->id, 'divisao' => request('divisao', '')])
            ->with('notify', [['success', 'Grupo removido.']]);
    }

    /**
     * Atualizar divisão de um grupo existente (após classificatória)
     */
    public function updateGroupDivisao(Request $request, Modalidade $modalidade, ModalidadeCompetitorGroup $group)
    {
        if ((int) $group->modalidade_id !== (int) $modalidade->id) {
            abort(404);
        }

        $data = $request->validate([
            'divisao' => 'nullable|string|max:60',
        ]);

        $novaDivisao = trim((string) ($data['divisao'] ?? ''));
        
        $group->divisao = $novaDivisao;
        $group->save();

        // Atualizar também a divisão dos competidores do grupo na pivot competitor_modalidade
        $memberIds = $group->members()->pluck('competitors.id')->all();
        if (!empty($memberIds) && !empty($novaDivisao)) {
            \DB::table('competitor_modalidade')
                ->where('modalidade_id', $modalidade->id)
                ->whereIn('competitor_id', $memberIds)
                ->update(['divisao' => $novaDivisao]);
        }

        // Resposta JSON para requisições AJAX
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Divisão atualizada!',
                'divisao' => $novaDivisao,
            ]);
        }

        return redirect()
            ->route('admin.modalidades.groups', ['modalidade' => $modalidade->id, 'divisao' => request('filter_divisao', '')])
            ->with('notify', [['success', 'Divisão do grupo atualizada!']]);
    }

    /**
     * Pausar/despausar a criação de salas X1 para uma modalidade
     */
    public function togglePauseX1(Request $request, Modalidade $modalidade)
    {
        $modalidade->pausar_x1 = !$modalidade->pausar_x1;
        $modalidade->save();

        $status = $modalidade->pausar_x1 ? 'pausado' : 'ativo';
        $message = $modalidade->pausar_x1 
            ? 'X1 pausado para esta modalidade!' 
            : 'X1 reativado para esta modalidade!';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status,
                'pausar_x1' => $modalidade->pausar_x1,
            ]);
        }

        return redirect()
            ->route('admin.modalidades.index')
            ->with('notify', [['success', $message]]);
    }
}
