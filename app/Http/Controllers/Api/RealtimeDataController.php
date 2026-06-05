<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\Modalidade;
use App\Models\ModalidadeCompetitorGroup;
use App\Models\Rodeio;
use App\Models\RankingSnapshot;
use App\Models\Sponsor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RealtimeDataController extends Controller
{
    private function normalizeRodeioBoundary($value, bool $isEnd = false): ?string
    {
        if (blank($value)) {
            return null;
        }

        $raw = trim((string) $value);

        try {
            $date = Carbon::parse($raw);

            // Legacy rodeios may still have date-only values saved at midnight.
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1) {
                $date = $isEnd ? $date->endOfDay() : $date->startOfDay();
            }

            return $date->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function modalidadeIsFinalizada(int $modalidadeId): bool
    {
        if ($modalidadeId <= 0) {
            return false;
        }

        if (!Schema::hasTable('modalidades')) {
            return false;
        }

        try {
            $cacheKey = "modalidade_status_finalizada_{$modalidadeId}";
            return (bool) Cache::remember($cacheKey, now()->addSeconds(10), function () use ($modalidadeId) {
                $m = Modalidade::query()->select(['id', 'status'])->find($modalidadeId);
                return $m && strtolower((string) ($m->status ?? '')) === 'finalizado';
            });
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function maskRankingPayloadUntilFinal(array $payload): array
    {
        if (!isset($payload['ranking']) || !is_array($payload['ranking'])) {
            $payload['ranking'] = [];
        }

        $ranking = array_map(function ($row) {
            if (!is_array($row)) {
                return $row;
            }
            // Keep identity fields, hide points.
            $row['score'] = null;
            return $row;
        }, $payload['ranking']);

        // Prevent inference by ordering: expose list alphabetically while in progress.
        usort($ranking, function ($a, $b) {
            $an = is_array($a) ? (string) ($a['nome'] ?? '') : '';
            $bn = is_array($b) ? (string) ($b['nome'] ?? '') : '';
            return strcasecmp($an, $bn);
        });

        $payload['ranking'] = array_values($ranking);

        if (!isset($payload['statistics']) || !is_array($payload['statistics'])) {
            $payload['statistics'] = [];
        }
        $payload['statistics']['top_score'] = null;
        $payload['statistics']['average_score'] = null;
        $payload['statistics']['points_hidden_until_final'] = true;
        $payload['statistics']['finalized'] = false;

        return $payload;
    }

    /**
     * Obter estatísticas em tempo real
     */
    public function getRealtimeStats()
    {
        try {
            $stats = Cache::get('realtime_stats', [
                'competitor_status_counts' => [
                    'active' => 0,
                    'inactive' => 0,
                    'disqualified' => 0,
                    'finished' => 0
                ],
                'ranking_stats' => [
                    'total_competitors_ranked' => 0,
                    'top_score' => 0,
                    'average_score' => 0,
                    'last_ranking_update' => null
                ],
                'last_update' => null
            ]);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get realtime stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas em tempo real'
            ], 500);
        }
    }

    /**
     * Obter ranking de uma modalidade
     */
    public function getRanking(Request $request, $modalidadeId)
    {
        try {
            $modalidadeId = (int) $modalidadeId;

            $rankingKey = "ranking_modalidade_{$modalidadeId}";
            $ranking = Cache::get($rankingKey);

            // Fallback: buscar último snapshot persistido
            if (!$ranking && Schema::hasTable('ranking_snapshots')) {
                $snapshot = RankingSnapshot::query()
                    ->where('modalidade_id', $modalidadeId)
                    ->orderByDesc('generated_at')
                    ->first();

                $ranking = $snapshot?->payload;
            }

            if (!$ranking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ranking não encontrado para esta modalidade'
                ], 404);
            }

            // Hide points until modalidade is finalized (frontend anti-snipe).
            if (!$this->modalidadeIsFinalizada($modalidadeId) && is_array($ranking)) {
                $ranking = $this->maskRankingPayloadUntilFinal($ranking);
            } elseif (is_array($ranking)) {
                if (!isset($ranking['statistics']) || !is_array($ranking['statistics'])) {
                    $ranking['statistics'] = [];
                }
                $ranking['statistics']['points_hidden_until_final'] = false;
                $ranking['statistics']['finalized'] = true;
            }

            // Paywall: Top30 público, full apenas premium autenticado
            $user = $request->user('sanctum');
            $isPremium = $user ? (bool) $user->isPremium() : false;

            if (!$isPremium && isset($ranking['ranking']) && is_array($ranking['ranking'])) {
                $ranking['ranking'] = array_slice($ranking['ranking'], 0, 30);
                $ranking['statistics']['limited_to_top'] = 30;
                $ranking['statistics']['is_premium_view'] = false;
            } elseif (isset($ranking['statistics']) && is_array($ranking['statistics'])) {
                $ranking['statistics']['is_premium_view'] = $isPremium;
            }

            return response()->json([
                'success' => true,
                'data' => $ranking
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get ranking', [
                'modalidade_id' => $modalidadeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter ranking'
            ], 500);
        }
    }

    /**
     * Obter status de transmissão ao vivo
     */
    public function getLiveTransmission($rodeioId)
    {
        try {
            // Fonte principal: banco de dados (estado real do admin live transmission).
            $rodeio = Rodeio::query()->find((int) $rodeioId);

            // Cache vira complementar (viewers/log rápidos), não fonte de verdade.
            $transmissionKey = "live_transmission_{$rodeioId}";
            $cached = Cache::get($transmissionKey, []);
            if (!is_array($cached)) {
                $cached = [];
            }

            if (!$rodeio && empty($cached)) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Sem transmissão ativa no momento'
                ]);
            }

            $status = (string) (
                $rodeio?->status_transmissao
                ?? data_get($cached, 'status')
                ?? 'programado'
            );

            $streamUrl = $rodeio?->stream_url
                ?? data_get($cached, 'stream_url')
                ?? data_get($cached, 'live_stream_url');

            $transmission = [
                'rodeio_id' => (int) ($rodeio?->id ?? data_get($cached, 'rodeio_id', $rodeioId)),
                'status' => $status,
                'modalidade_atual' => $rodeio?->modalidade_atual ?? data_get($cached, 'modalidade_atual'),
                'stream_url' => $streamUrl,
                'live_stream_url' => $streamUrl,
                'viewers_count' => (int) data_get($cached, 'viewers_count', 0),
                'message' => data_get($cached, 'message'),
                'timestamp' => now()->toISOString(),
                'source' => 'db',
            ];

            return response()->json([
                'success' => true,
                'data' => $transmission
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get live transmission', [
                'rodeio_id' => $rodeioId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter transmissão ao vivo'
            ], 500);
        }
    }

    /**
     * Obter status de competidores por modalidade
     */
    public function getCompetitorsByModalidade(Request $request, $modalidadeId)
    {
        try {
            $validated = $request->validate([
                'rodeio_id' => 'nullable|integer|min:1',
                'divisao' => 'nullable|string|max:60',
                'modo' => 'nullable|string|in:competidores,grupos',
            ]);

            $modalidadeId = (int) $modalidadeId;

            $divisao = trim((string) ($validated['divisao'] ?? ''));
            $rodeioId = (int) ($validated['rodeio_id'] ?? 0);
            $modo = strtolower((string) ($validated['modo'] ?? ''));

            $modalidade = null;
            try {
                $modalidade = Modalidade::query()->find($modalidadeId);
            } catch (Throwable $e) {
                $modalidade = null;
            }

            // If rodeio_id is provided and modalidade has divisions, default divisao to rodeio.divisao_atual.
            if ($divisao === '' && $rodeioId > 0 && Schema::hasTable('modalidades') && Schema::hasTable('rodeios')) {
                if ($modalidade && (bool) ($modalidade->tem_divisoes ?? false) && Schema::hasColumn('rodeios', 'divisao_atual')) {
                    try {
                        $rodeio = Rodeio::query()->find($rodeioId);
                        $divisao = trim((string) ($rodeio?->divisao_atual ?? ''));
                    } catch (\Throwable $e) {
                        $divisao = '';
                    }
                }
            }

            $teamSize = (int) ($modalidade?->tamanho_equipe ?? 1);
            if ($modo === 'grupos' && $teamSize > 1 && Schema::hasTable('modalidade_competitor_groups') && Schema::hasTable('modalidade_competitor_group_members')) {
                $groupsKey = "modalidade_{$modalidadeId}_grupos";
                if ($divisao !== '') {
                    $slug = Str::slug($divisao);
                    $groupsKey = "modalidade_{$modalidadeId}_divisao_{$slug}_grupos";
                }

                $groups = Cache::get($groupsKey);
                if (!$groups || !is_array($groups) || $groups === []) {
                    $query = ModalidadeCompetitorGroup::query()
                        ->where('modalidade_id', $modalidadeId)
                        ->whereNotIn('status', ['desqualificado', 'inativo']) // Excluir apenas grupos desqualificados/inativos
                        ->with(['members' => function ($q) {
                            $q->select('competitors.id', 'competitors.nome', 'competitors.foto', 'competitors.nivel');
                        }]);

                    if ($divisao !== '') {
                        $query->where('divisao', $divisao);
                    }

                    $rows = $query->orderBy('id')->get();
                    $groups = $rows->map(function ($g) use ($modalidadeId, $rodeioId) {
                        $members = $g->members?->map(function ($m) {
                            return [
                                'id' => (int) $m->id,
                                'nome' => (string) $m->nome,
                                'foto' => $m->foto ? (string) $m->foto : '/assets/images/logo_icon/favicon.png',
                                'nivel' => $m->nivel ? (string) $m->nivel : null,
                            ];
                        })->values()->all() ?? [];
                        $groupName = $g->nome ?: collect($members)->pluck('nome')->implode(' + ');

                        return [
                            'group_id' => (int) $g->id,
                            'group_name' => (string) ($groupName ?: ('Grupo #' . $g->id)),
                            'members' => $members,
                            'modalidade_id' => (int) $modalidadeId,
                            'rodeio_id' => $rodeioId > 0 ? (int) $rodeioId : null,
                            'divisao' => (string) ($g->divisao ?? ''),
                            'tamanho' => (int) ($g->tamanho ?? count($members)),
                        ];
                    })
                    // Filtrar grupos incompletos (membros < tamanho esperado)
                    ->filter(function ($g) {
                        return count($g['members']) >= $g['tamanho'];
                    })
                    ->values()->all();

                    Cache::put($groupsKey, $groups, now()->addMinutes(5));
                }

                return response()->json([
                    'success' => true,
                    'meta' => [
                        'modalidade_id' => $modalidadeId,
                        'rodeio_id' => $rodeioId > 0 ? $rodeioId : null,
                        'divisao' => $divisao !== '' ? $divisao : null,
                        'cache_key' => $groupsKey,
                        'modo' => 'grupos',
                        'team_size' => $teamSize,
                    ],
                    'data' => $groups,
                ]);
            }

            $competitorsKey = "modalidade_{$modalidadeId}_competitors";
            if ($divisao !== '') {
                $slug = Str::slug($divisao);
                $competitorsKey = "modalidade_{$modalidadeId}_divisao_{$slug}_competitors";
            }

            $competitors = Cache::get($competitorsKey);

            // Fallback (DB): used by Fantasy ao vivo (avoid empty UI if cache not warmed).
            if (!$competitors || !is_array($competitors) || $competitors === []) {
                if (!Schema::hasTable('competitor_modalidade') || !Schema::hasTable('competitors')) {
                    $competitors = [];
                } else {
                    $q = DB::table('competitor_modalidade as cm')
                        ->join('competitors as c', 'c.id', '=', 'cm.competitor_id')
                        ->where('cm.modalidade_id', $modalidadeId)
                        ->where('c.status', 'ativo');

                    if (Schema::hasColumn('competitor_modalidade', 'disponivel_participacao')) {
                        $q->where('cm.disponivel_participacao', 1);
                    }

                    if ($divisao !== '' && Schema::hasColumn('competitor_modalidade', 'divisao')) {
                        $q->where('cm.divisao', $divisao);
                    }

                    $rows = $q
                        ->select([
                            'c.id as competitor_id',
                            'c.nome as competitor_name',
                            'c.foto',
                            'c.nivel',
                            'cm.modalidade_id',
                            'cm.status as new_status',
                        ])
                        ->orderBy('c.nome')
                        ->get();

                    $competitors = [];
                    foreach ($rows as $r) {
                        $competitors[(int) $r->competitor_id] = [
                            'competitor_id' => (int) $r->competitor_id,
                            'competitor_name' => (string) $r->competitor_name,
                            'foto' => $r->foto ? (string) $r->foto : '/assets/images/logo_icon/favicon.png',
                            'nivel' => $r->nivel ? (string) $r->nivel : null,
                            'modalidade_id' => (int) $r->modalidade_id,
                            'rodeio_id' => $rodeioId > 0 ? (int) $rodeioId : null,
                            'old_status' => null,
                            'new_status' => (string) ($r->new_status ?? 'inscrito'),
                            'timestamp' => now()->toISOString(),
                        ];
                    }
                }

                // Cache for a short window (used for live views).
                Cache::put($competitorsKey, $competitors, now()->addMinutes(5));
            }

            return response()->json([
                'success' => true,
                'meta' => [
                    'modalidade_id' => $modalidadeId,
                    'rodeio_id' => $rodeioId > 0 ? $rodeioId : null,
                    'divisao' => $divisao !== '' ? $divisao : null,
                    'cache_key' => $competitorsKey,
                    'modo' => 'competidores',
                    'team_size' => $teamSize,
                ],
                'data' => array_values($competitors)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get competitors by modalidade', [
                'modalidade_id' => $modalidadeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter competidores'
            ], 500);
        }
    }

    public function searchCompetitors(Request $request)
    {
        try {
            $validated = $request->validate([
                'q' => 'nullable|string|max:120',
                'limit' => 'nullable|integer|min:1|max:60',
            ]);

            $term = trim((string) ($validated['q'] ?? ''));
            $limit = (int) ($validated['limit'] ?? 40);

            $competitors = Competitor::query()
                ->active()
                ->when($term !== '', function ($builder) use ($term) {
                    $builder->where('nome', 'like', '%' . $term . '%');
                })
                ->orderBy('nome')
                ->limit($limit)
                ->get(['id', 'nome', 'foto', 'nivel', 'profile_claimed']);

            return response()->json([
                'success' => true,
                'data' => $competitors->map(function (Competitor $competitor) {
                    return [
                        'id' => (int) $competitor->id,
                        'name' => (string) ($competitor->nome ?? 'Sem nome'),
                        'short_name' => $this->shortCompetitorName((string) ($competitor->nome ?? 'Sem nome')),
                        'photo_url' => $competitor->foto_url,
                        'level' => $this->normalizeCompetitorLevel((string) ($competitor->nivel ?? '')),
                        'level_label' => $this->competitorLevelLabel((string) ($competitor->nivel ?? '')),
                        'claimed' => (bool) ($competitor->profile_claimed ?? false),
                    ];
                })->values()->all(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to search competitors for home filter', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar competidores.',
            ], 500);
        }
    }

    private function normalizeCompetitorLevel(string $level): string
    {
        $value = Str::of($level)
            ->lower()
            ->ascii()
            ->replace(' ', '')
            ->toString();

        return match ($value) {
            'favorito' => 'favorito',
            'elite' => 'elite',
            'ascendente', 'legado' => 'ascendente',
            default => 'competidor',
        };
    }

    private function competitorLevelLabel(string $level): string
    {
        return match ($this->normalizeCompetitorLevel($level)) {
            'favorito' => 'Favorito',
            'elite' => 'Elite',
            'ascendente' => 'Ascendente',
            default => 'Competidor',
        };
    }

    private function shortCompetitorName(string $name): string
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return 'Sem nome';
        }

        return Str::limit($trimmed, 24, '...');
    }

    /**
     * Metadados de uma modalidade (tipo, divisoes, tamanho da equipe)
     */
    public function getModalidadeMeta($modalidadeId)
    {
        try {
            $modalidadeId = (int) $modalidadeId;
            $modalidade = Modalidade::query()->findOrFail($modalidadeId);

            // Verificar se está em fase classificatória (sem exigir divisão)
            $statusClassificatoria = ['programado', 'classificatoria', 'ativo'];
            $isClassificatoria = in_array($modalidade->status ?? 'ativo', $statusClassificatoria);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => (int) $modalidade->id,
                    'nome' => (string) ($modalidade->nome ?? ''),
                    'tem_divisoes' => (bool) ($modalidade->tem_divisoes ?? false),
                    'divisoes' => $modalidade->divisoes_nomes ?? [],
                    'tipo_participacao' => (string) ($modalidade->tipo_participacao ?? 'individual'),
                    'tamanho_equipe' => (int) ($modalidade->tamanho_equipe ?? 1),
                    'status' => (string) ($modalidade->status ?? 'ativo'),
                    'is_classificatoria' => $isClassificatoria,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Modalidade não encontrada',
            ], 404);
        }
    }

    /**
     * Listar todos os rodeios disponíveis
     */
    public function getRodeios()
    {
        try {
            $rodeios = Rodeio::query()
                ->orderByRaw('CASE WHEN start IS NULL THEN 1 ELSE 0 END')
                ->orderBy('start')
                ->orderBy('name')
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => (int) $r->id,
                        'label' => (string) ($r->nome ?? $r->name ?? $r->titulo ?? 'Rodeio #' . $r->id),
                        'start' => $this->normalizeRodeioBoundary($r->getRawOriginal('start') ?: $r->start, false),
                        'end' => $this->normalizeRodeioBoundary($r->getRawOriginal('end') ?: $r->end, true),
                        'status_transmissao' => (string) ($r->status_transmissao ?? ''),
                    ];
                });

            $sponsors = collect();
            if (Schema::hasTable('sponsors')) {
                $sponsors = Sponsor::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderByDesc('id')
                    ->get()
                    ->map(function ($sponsor) {
                        $logo = trim((string) $sponsor->logo);
                        $logoUrl = Str::startsWith($logo, ['http://', 'https://'])
                            ? $logo
                            : asset('storage/' . ltrim($logo, '/'));

                        return [
                            'id' => (int) $sponsor->id,
                            'name' => (string) $sponsor->name,
                            'logo_url' => $logoUrl,
                            'url' => (string) $sponsor->url,
                            'sort_order' => (int) $sponsor->sort_order,
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'data' => $rodeios,
                'sponsors' => $sponsors,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar rodeios: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modalidades por rodeio (com metadados para frontend)
     */
    public function getModalidadesByRodeio($rodeioId)
    {
        try {
            $rodeioId = (int) $rodeioId;
            if ($rodeioId <= 0) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }
            $query = Modalidade::query()->orderBy('nome');

            if ($rodeioId > 0) {
                $query->where('rodeio_id', $rodeioId);
            }
            
            // Filtrar modalidades que não estão pausadas para X1
            $query->where('pausar_x1', false);

            $statusClassificatoria = ['programado', 'classificatoria'];

            $modalidades = $query->get()->map(function ($m) use ($statusClassificatoria) {
                $status = (string) ($m->status ?? 'programado');
                $isClassificatoria = in_array($status, $statusClassificatoria);
                
                // Verificar se há competidores/grupos com divisão atribuída
                $hasAssignedDivisions = false;
                if ((bool) ($m->tem_divisoes ?? false)) {
                    $modalidadeId = (int) $m->id;
                    $teamSize = (int) ($m->tamanho_equipe ?? 1);
                    
                    if ($teamSize > 1) {
                        // Modo grupo: verificar se algum grupo tem divisão
                        $hasAssignedDivisions = DB::table('modalidade_competitor_groups')
                            ->where('modalidade_id', $modalidadeId)
                            ->whereNotNull('divisao')
                            ->where('divisao', '!=', '')
                            ->exists();
                    } else {
                        // Modo individual: verificar se algum competidor tem divisão
                        $hasAssignedDivisions = DB::table('competitor_modalidade')
                            ->where('modalidade_id', $modalidadeId)
                            ->whereNotNull('divisao')
                            ->where('divisao', '!=', '')
                            ->exists();
                    }
                }
                
                return [
                    'id' => (int) $m->id,
                    'nome' => (string) ($m->nome ?? ''),
                    'rodeio_id' => $m->rodeio_id ? (int) $m->rodeio_id : null,
                    'tem_divisoes' => (bool) ($m->tem_divisoes ?? false),
                    'divisoes' => $m->divisoes_nomes ?? [],
                    'tipo_participacao' => (string) ($m->tipo_participacao ?? 'individual'),
                    'tamanho_equipe' => (int) ($m->tamanho_equipe ?? 1),
                    'status' => $status,
                    'is_classificatoria' => $isClassificatoria,
                    'has_assigned_divisions' => $hasAssignedDivisions, // Nova flag
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $modalidades,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar modalidades',
            ], 500);
        }
    }

    /**
     * Listar todas as modalidades disponíveis (com filtro opcional por rodeio via query string)
     */
    public function getModalidades(Request $request)
    {
        try {
            $rodeioId = (int) $request->query('rodeio_id', 0);
            
            $query = Modalidade::query()->orderBy('nome');
            
            if ($rodeioId > 0) {
                $query->where('rodeio_id', $rodeioId);
            }
            
            // Filtrar modalidades que não estão pausadas para X1
            $query->where('pausar_x1', false);

            $statusClassificatoria = ['programado', 'classificatoria'];

            $modalidades = $query->get()->map(function ($m) use ($statusClassificatoria) {
                $status = (string) ($m->status ?? 'programado');
                $isClassificatoria = in_array($status, $statusClassificatoria);
                
                // Verificar se há competidores/grupos com divisão atribuída
                $hasAssignedDivisions = false;
                if ((bool) ($m->tem_divisoes ?? false)) {
                    $modalidadeId = (int) $m->id;
                    $teamSize = (int) ($m->tamanho_equipe ?? 1);
                    
                    if ($teamSize > 1) {
                        // Modo grupo: verificar se algum grupo tem divisão
                        $hasAssignedDivisions = DB::table('modalidade_competitor_groups')
                            ->where('modalidade_id', $modalidadeId)
                            ->whereNotNull('divisao')
                            ->where('divisao', '!=', '')
                            ->exists();
                    } else {
                        // Modo individual: verificar se algum competidor tem divisão
                        $hasAssignedDivisions = DB::table('competitor_modalidade')
                            ->where('modalidade_id', $modalidadeId)
                            ->whereNotNull('divisao')
                            ->where('divisao', '!=', '')
                            ->exists();
                    }
                }
                
                return [
                    'id' => (int) $m->id,
                    'nome' => (string) ($m->nome ?? ''),
                    'rodeio_id' => $m->rodeio_id ? (int) $m->rodeio_id : null,
                    'tem_divisoes' => (bool) ($m->tem_divisoes ?? false),
                    'divisoes' => $m->divisoes_nomes ?? [],
                    'tipo_participacao' => (string) ($m->tipo_participacao ?? 'individual'),
                    'tamanho_equipe' => (int) ($m->tamanho_equipe ?? 1),
                    'status' => $status,
                    'is_classificatoria' => $isClassificatoria,
                    'has_assigned_divisions' => $hasAssignedDivisions, // Nova flag
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $modalidades,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar modalidades',
            ], 500);
        }
    }

    /**
     * Obter transmissões ativas
     */
    public function getActiveTransmissions()
    {
        try {
            $activeTransmissions = Cache::get('active_transmissions', []);

            return response()->json([
                'success' => true,
                'data' => array_values($activeTransmissions)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get active transmissions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter transmissões ativas'
            ], 500);
        }
    }

    /**
     * Obter log de transmissões de um rodeio
     */
    public function getTransmissionLog($rodeioId)
    {
        try {
            $logKey = "transmission_log_{$rodeioId}";
            $logs = Cache::get($logKey, []);

            return response()->json([
                'success' => true,
                'data' => array_values($logs)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get transmission log', [
                'rodeio_id' => $rodeioId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter log de transmissões'
            ], 500);
        }
    }

    /**
     * Obter todos os rankings
     */
    public function getAllRankings(Request $request)
    {
        try {
            $globalRankings = Cache::get('global_rankings', []);

            // Fallback: se não houver cache global, montar a partir dos últimos snapshots
            if (($globalRankings === [] || $globalRankings === null) && Schema::hasTable('ranking_snapshots')) {
                $latestByModalidade = RankingSnapshot::query()
                    ->select('modalidade_id')
                    ->whereNotNull('modalidade_id')
                    ->groupBy('modalidade_id')
                    ->get()
                    ->pluck('modalidade_id');

                $globalRankings = [];
                foreach ($latestByModalidade as $modalidadeId) {
                    $snapshot = RankingSnapshot::query()
                        ->where('modalidade_id', $modalidadeId)
                        ->orderByDesc('generated_at')
                        ->first();
                    if ($snapshot) {
                        $globalRankings[$modalidadeId] = $snapshot->payload;
                    }
                }
            }

            $user = $request->user('sanctum');
            $isPremium = $user ? (bool) $user->isPremium() : false;

            // Apply points-hiding gate per modalidade.
            $statusByModalidade = [];
            try {
                $modalidadeIds = array_values(array_filter(array_map('intval', is_array($globalRankings) ? array_keys($globalRankings) : [])));
                if ($modalidadeIds !== [] && Schema::hasTable('modalidades')) {
                    $statusByModalidade = Modalidade::query()
                        ->whereIn('id', $modalidadeIds)
                        ->pluck('status', 'id')
                        ->map(fn ($v) => strtolower((string) $v))
                        ->all();
                }
            } catch (\Throwable $e) {
                $statusByModalidade = [];
            }

            if (is_array($globalRankings)) {
                foreach ($globalRankings as $key => $data) {
                    $mid = (int) $key;
                    $final = (($statusByModalidade[$mid] ?? '') === 'finalizado');
                    if (!$final && is_array($data)) {
                        $globalRankings[$key] = $this->maskRankingPayloadUntilFinal($data);
                    } elseif (is_array($data)) {
                        if (!isset($data['statistics']) || !is_array($data['statistics'])) {
                            $data['statistics'] = [];
                        }
                        $data['statistics']['points_hidden_until_final'] = false;
                        $data['statistics']['finalized'] = true;
                        $globalRankings[$key] = $data;
                    }
                }
            }

            if (!$isPremium && is_array($globalRankings)) {
                foreach ($globalRankings as $key => $data) {
                    if (is_array($data) && isset($data['ranking']) && is_array($data['ranking'])) {
                        $data['ranking'] = array_slice($data['ranking'], 0, 30);
                        if (!isset($data['statistics']) || !is_array($data['statistics'])) {
                            $data['statistics'] = [];
                        }
                        $data['statistics']['limited_to_top'] = 30;
                        $data['statistics']['is_premium_view'] = false;
                        $globalRankings[$key] = $data;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $globalRankings
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get all rankings', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter rankings'
            ], 500);
        }
    }

    /**
     * Obter últimos ganhadores (X1 e Bolão/Fantasy), incluindo bots mascarados
     */
    public function getRecentWinners()
    {
        try {
            // Buscar últimas transações de prêmio (fantasy_prize e x1_win)
            // Inclui bots — nomes de bot são mascarados com **
            $hasSubscriptions = Schema::hasTable('subscriptions');

            $query = DB::table('transactions as t')
                ->join('users as u', 'u.id', '=', 't.user_id');

            if ($hasSubscriptions) {
                $query->leftJoin('subscriptions as s', function ($j) {
                    $j->on('s.user_id', '=', 'u.id')
                      ->where(function ($q) {
                          $q->where(function ($q2) {
                              $q2->where('s.status', 'ativa')
                                 ->whereDate('s.data_fim', '>=', now()->toDateString());
                          })->orWhere(function ($q2) {
                              $q2->where('s.is_trial', true)
                                 ->where('s.trial_ends_at', '>=', now());
                          });
                      });
                });
            }

            $selects = [
                'u.id as user_id',
                'u.username',
                'u.firstname',
                'u.lastname',
                'u.email',
                't.amount as prize',
                't.remark',
                't.details',
                't.created_at',
            ];
            if (Schema::hasColumn('users', 'show_in_listings')) {
                $selects[] = 'u.show_in_listings';
            } else {
                $selects[] = DB::raw('1 as show_in_listings');
            }
            if ($hasSubscriptions) {
                $selects[] = DB::raw('IF(s.id IS NOT NULL, 1, 0) as is_premium');
            } else {
                $selects[] = DB::raw('0 as is_premium');
            }

            $winners = $query
                ->whereIn('t.remark', ['fantasy_prize', 'x1_win', 'x1_prize'])
                ->where('t.amount', '>', 0)
                ->orderByDesc('t.created_at')
                ->limit(30)
                ->get($selects);

            $result = $winners->map(function ($w) {
                $type = ($w->remark === 'fantasy_prize') ? 'fantasy' : 'x1';

                // Bots usam nome real como base pro mascaramento
                $isBot = str_ends_with($w->email ?? '', '@bot.local');
                $fullName = trim((string) (($w->firstname ?? '') . ' ' . ($w->lastname ?? '')));
                $baseName = $w->username ?: ($fullName !== '' ? $fullName : 'Usuário');
                $displayName = $isBot && preg_match('/^(bo|bot|bo\*\*|bo\*\*ot|bo\d+)/i', (string) ($w->username ?? ''))
                    ? 'Participante'
                    : $baseName;
                $showInListings = $isBot ? false : (bool) ($w->show_in_listings ?? true);

                return [
                    'name'             => $displayName,
                    'prize'            => (float) $w->prize,
                    'type'             => $type,
                    'show_in_listings' => $showInListings,
                    'is_premium'       => (bool) ($w->is_premium ?? false),
                    'details'          => $w->details,
                    'created_at'       => $w->created_at,
                ];
            })->values()->toArray();

            $botWinners = [];
            if (Schema::hasTable('fantasy_teams') && Schema::hasTable('bot_users')) {
                $botRows = DB::table('fantasy_teams as ft')
                    ->join('bot_users as bu', 'bu.id', '=', 'ft.bot_user_id')
                    ->whereNotNull('ft.bot_user_id')
                    ->where('ft.prize_won', '>', 0)
                    ->whereNotNull('ft.prize_paid_at')
                    ->orderByDesc('ft.prize_paid_at')
                    ->limit(30)
                    ->get([
                        'bu.username',
                        'bu.email',
                        'ft.prize_won as prize',
                        'ft.prize_paid_at as created_at',
                        'ft.final_position',
                        'ft.fantasy_league_id',
                        'ft.team_name',
                    ]);

                $botWinners = $botRows->map(function ($w) {
                    $details = sprintf(
                        'Prêmio Fantasy - %sº lugar - Liga #%s',
                        $w->final_position ?? '-',
                        $w->fantasy_league_id ?? '-'
                    );

                    return [
                        'name'             => $w->username ?? 'Bot',
                        'prize'            => (float) $w->prize,
                        'type'             => 'fantasy',
                        'show_in_listings' => false,
                        'is_premium'       => false,
                        'details'          => $details,
                        'created_at'       => $w->created_at,
                    ];
                })->values()->toArray();
            }

            $result = collect($result)
                ->concat($botWinners)
                ->sortByDesc(function ($row) {
                    return $row['created_at'] ?? null;
                })
                ->take(30)
                ->values()
                ->toArray();

            // Garante pelo menos 1 ganhador (real ou bot), ignorando show_in_listings
            if (empty($result)) {
                $fallback = $winners->first() ?: ($botWinners[0] ?? null);
                if ($fallback) {
                    $result = [
                        [
                            'name' => $fallback['name'] ?? 'Usuário',
                            'prize' => $fallback['prize'] ?? 0,
                            'type' => $fallback['type'] ?? 'x1',
                            'show_in_listings' => true,
                            'is_premium' => $fallback['is_premium'] ?? false,
                            'details' => $fallback['details'] ?? '',
                            'created_at' => $fallback['created_at'] ?? now(),
                        ]
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'winners' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get recent winners', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter últimos ganhadores',
                'debug_error' => $e->getMessage(),
                'debug_line' => $e->getLine(),
                'winners' => [],
            ], 500);
        }
    }
}
