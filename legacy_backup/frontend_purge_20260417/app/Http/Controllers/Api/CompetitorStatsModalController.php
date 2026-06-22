<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\CompetitorFollowEvent;
use App\Models\CompetitorContextStat;
use App\Services\CompetitorFollowerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompetitorStatsModalController extends Controller
{
    public function __construct(
        private readonly CompetitorFollowerService $followerService
    ) {
    }

    private const DETAIL_FIELDS = [
        'count_dobrada' => ['label' => 'Dobrada', 'tone' => 'positive'],
        'count_duas_voltas' => ['label' => 'Duas Voltas', 'tone' => 'positive'],
        'count_cola' => ['label' => 'Cola', 'tone' => 'positive'],
        'count_cupim' => ['label' => 'Cupim', 'tone' => 'positive'],
        'count_pescou' => ['label' => 'Pescou', 'tone' => 'positive'],
        'count_por_cima' => ['label' => 'Por Cima', 'tone' => 'positive'],
        'count_limpou_garupa' => ['label' => 'Limpou Garupa', 'tone' => 'positive'],
        'count_limpou_cupim_longe' => ['label' => 'Limpou Cupim', 'tone' => 'positive'],
        'count_limpou_top' => ['label' => 'Limpou Top', 'tone' => 'positive'],
        'count_limpou_top_mao' => ['label' => 'Limpou Top com a Mão', 'tone' => 'positive'],
        'count_top' => ['label' => 'Top', 'tone' => 'positive'],
        'count_uma_aspa' => ['label' => 'Uma Aspa', 'tone' => 'positive'],
        'count_cabresteou' => ['label' => 'Cabresteou', 'tone' => 'negative'],
        'count_errou_pescoco' => ['label' => 'Errou Pescoço', 'tone' => 'negative'],
        'count_errou_pata' => ['label' => 'Errou Pata', 'tone' => 'negative'],
        'count_errou_top' => ['label' => 'Errou Top', 'tone' => 'negative'],
        'count_boi_tirou' => ['label' => 'Boi Tirou', 'tone' => 'negative'],
        'count_boi_pulou' => ['label' => 'Boi Pulou', 'tone' => 'negative'],
        'count_queimou_raia' => ['label' => 'Queimou a Raia', 'tone' => 'negative'],
        'count_caiu_do_cavalo' => ['label' => 'Caiu do Cavalo', 'tone' => 'negative'],
        'count_saiu_enrolado' => ['label' => 'Saiu Enrolado', 'tone' => 'negative'],
    ];

    public function contexts(Request $request, Competitor $competitor): JsonResponse
    {
        $competitor->loadMissing('stats');
        $user = $request->user();

        $contexts = collect();

        if (Schema::hasTable('competitor_stats')) {
            $contexts = DB::table('competitor_stats as cs')
                ->leftJoin('rodeios as r', 'r.id', '=', 'cs.rodeio_id')
                ->leftJoin('modalidades as m', 'm.id', '=', 'cs.modalidade_id')
                ->where('cs.competitor_id', $competitor->id)
                ->select([
                    'cs.*',
                    DB::raw('COALESCE(r.name, CONCAT("Rodeio #", cs.rodeio_id)) as rodeio_name'),
                    DB::raw('COALESCE(m.nome, CONCAT("Modalidade #", cs.modalidade_id)) as modalidade_name'),
                ])
                ->orderByRaw('CASE WHEN cs.is_finalized = 0 THEN 0 ELSE 1 END')
                ->orderByDesc('cs.last_updated_at')
                ->orderByDesc('cs.updated_at')
                ->get()
                ->map(fn ($row) => $this->serializeContextRow($row));
        }

        $recentEvents = CompetitorFollowEvent::query()
            ->with(['rodeio:id,name', 'modalidade:id,nome'])
            ->where('competitor_id', $competitor->id)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (CompetitorFollowEvent $event) => $this->serializeFollowEvent($event));

        return response()->json([
            'success' => true,
            'data' => [
                'competitor' => [
                    'id' => (int) $competitor->id,
                    'name' => (string) ($competitor->nome ?? 'Sem nome'),
                    'photo_url' => $competitor->foto_url,
                    'level' => $this->normalizeLevel((string) ($competitor->nivel ?? '')),
                    'level_label' => $this->levelLabel((string) ($competitor->nivel ?? '')),
                    'claimed' => (bool) ($competitor->profile_claimed ?? false),
                    'followers_count' => $competitor->followers()->count(),
                    'is_following' => $this->followerService->isFollowing($user, $competitor),
                    'can_follow' => (bool) $user,
                ],
                'global' => $this->serializeStatsRecord($competitor->stats),
                'contexts' => $contexts->values()->all(),
                'recent_events' => $recentEvents->values()->all(),
                'filters' => [
                    'events' => $this->buildEventFilters($contexts)->values()->all(),
                    'modalidades' => $this->buildModalidadeFilters($contexts)->values()->all(),
                    'divisoes' => $this->buildDivisaoFilters($contexts)->values()->all(),
                ],
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'isPremium') || !$user->isPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Comparativo disponível apenas para membros premium.',
            ], 403);
        }

        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'exclude' => 'nullable|integer',
            'rodeio_id' => 'nullable|integer',
            'modalidade_id' => 'nullable|integer',
            'divisao' => 'nullable|string|max:60',
            'limit' => 'nullable|integer|min:1|max:40',
        ]);

        $query = Competitor::query()
            ->active()
            ->when(!empty($validated['exclude']), fn ($builder) => $builder->where('id', '!=', (int) $validated['exclude']))
            ->when(!empty($validated['q']), function ($builder) use ($validated) {
                $term = trim((string) $validated['q']);
                $builder->where('nome', 'like', '%' . $term . '%');
            })
            ->when(
                Schema::hasTable('competitor_stats') && (!empty($validated['rodeio_id']) || !empty($validated['modalidade_id']) || array_key_exists('divisao', $validated)),
                function ($builder) use ($validated) {
                    $rodeioId = !empty($validated['rodeio_id']) ? (int) $validated['rodeio_id'] : null;
                    $modalidadeId = !empty($validated['modalidade_id']) ? (int) $validated['modalidade_id'] : null;
                    $divisao = array_key_exists('divisao', $validated) ? trim((string) ($validated['divisao'] ?? '')) : null;

                    $builder->whereExists(function ($subquery) use ($rodeioId, $modalidadeId, $divisao) {
                        $subquery->selectRaw('1')
                            ->from('competitor_stats as cs')
                            ->whereColumn('cs.competitor_id', 'competitors.id')
                            ->when($rodeioId, fn ($query) => $query->where('cs.rodeio_id', $rodeioId))
                            ->when($modalidadeId, fn ($query) => $query->where('cs.modalidade_id', $modalidadeId));

                        if ($divisao !== null && $divisao !== '') {
                            $subquery->where('cs.divisao', $divisao);
                        }
                    });
                }
            )
            ->orderBy('nome')
            ->limit((int) ($validated['limit'] ?? 18))
            ->get(['id', 'nome', 'foto', 'nivel', 'profile_claimed']);

        return response()->json([
            'success' => true,
            'data' => $query->map(function (Competitor $competitor) {
                return [
                    'id' => (int) $competitor->id,
                    'name' => (string) ($competitor->nome ?? 'Sem nome'),
                    'short_name' => $this->shortName((string) ($competitor->nome ?? 'Sem nome')),
                    'photo_url' => $competitor->foto_url,
                    'level' => $this->normalizeLevel((string) ($competitor->nivel ?? '')),
                    'level_label' => $this->levelLabel((string) ($competitor->nivel ?? '')),
                    'claimed' => (bool) ($competitor->profile_claimed ?? false),
                ];
            })->values()->all(),
        ]);
    }

    public function compare(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'isPremium') || !$user->isPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Comparativo disponível apenas para membros premium.',
            ], 403);
        }

        $validated = $request->validate([
            'competitor_a' => 'required|integer',
            'competitor_b' => 'required|integer|different:competitor_a',
            'rodeio_id' => 'nullable|integer',
            'modalidade_id' => 'nullable|integer',
            'divisao' => 'nullable|string|max:60',
        ]);

        $competitorA = Competitor::query()->findOrFail((int) $validated['competitor_a']);
        $competitorB = Competitor::query()->findOrFail((int) $validated['competitor_b']);

        $hasContext = !empty($validated['rodeio_id']) || !empty($validated['modalidade_id']) || !empty($validated['divisao']);

        $contextInfo = [
            'rodeio_id' => !empty($validated['rodeio_id']) ? (int) $validated['rodeio_id'] : null,
            'modalidade_id' => !empty($validated['modalidade_id']) ? (int) $validated['modalidade_id'] : null,
            'divisao' => array_key_exists('divisao', $validated) ? trim((string) ($validated['divisao'] ?? '')) : '',
        ];

        $statsA = $hasContext ? $this->findContextStats($competitorA->id, $contextInfo) : $competitorA->stats;
        $statsB = $hasContext ? $this->findContextStats($competitorB->id, $contextInfo) : $competitorB->stats;

        if ($hasContext && (!$statsA || !$statsB)) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível comparar esses competidores nesse recorte.',
            ], 404);
        }

        $recordA = $this->serializeStatsRecord($statsA);
        $recordB = $this->serializeStatsRecord($statsB);

        return response()->json([
            'success' => true,
            'data' => [
                'context' => [
                    'mode' => $hasContext ? 'context' : 'global',
                    'rodeio_id' => $contextInfo['rodeio_id'],
                    'modalidade_id' => $contextInfo['modalidade_id'],
                    'divisao' => $contextInfo['divisao'],
                    'label' => $this->buildContextLabel($contextInfo),
                ],
                'competitor_a' => $this->serializeCompetitorForCompare($competitorA, $recordA),
                'competitor_b' => $this->serializeCompetitorForCompare($competitorB, $recordB),
                'summary_rows' => $this->buildComparisonRows($recordA, $recordB, false),
                'detail_rows' => $this->buildComparisonRows($recordA, $recordB, true),
                'detail_positive_rows' => $this->buildDetailComparisonRows($recordA, $recordB, 'positive'),
                'detail_negative_rows' => $this->buildDetailComparisonRows($recordA, $recordB, 'negative'),
            ],
        ]);
    }

    private function serializeContextRow(object $row): array
    {
        $stats = $this->serializeStatsRecord($row);
        $divisao = trim((string) ($row->divisao ?? ''));
        $context = [
            'key' => implode(':', [
                (int) ($row->rodeio_id ?? 0),
                (int) ($row->modalidade_id ?? 0),
                $divisao,
            ]),
            'rodeio_id' => (int) ($row->rodeio_id ?? 0),
            'rodeio_name' => (string) ($row->rodeio_name ?? ('Rodeio #' . (int) ($row->rodeio_id ?? 0))),
            'modalidade_id' => (int) ($row->modalidade_id ?? 0),
            'modalidade_name' => (string) ($row->modalidade_name ?? ('Modalidade #' . (int) ($row->modalidade_id ?? 0))),
            'divisao' => $divisao,
            'divisao_label' => $divisao !== '' ? $divisao : 'Geral',
            'tipo_fase' => trim((string) ($row->tipo_fase ?? '')),
            'is_finalized' => (bool) ($row->is_finalized ?? false),
            'last_updated_at' => !empty($row->last_updated_at) ? \Illuminate\Support\Carbon::parse($row->last_updated_at)->toIso8601String() : null,
            'stats' => $stats,
        ];

        $context['context_label'] = trim($context['rodeio_name'] . ' • ' . $context['modalidade_name'] . ($context['divisao_label'] !== '' ? ' • ' . $context['divisao_label'] : ''));

        return $context;
    }

    private function serializeCompetitorForCompare(Competitor $competitor, array $record): array
    {
        return [
            'id' => (int) $competitor->id,
            'name' => (string) ($competitor->nome ?? 'Sem nome'),
            'short_name' => $this->shortName((string) ($competitor->nome ?? 'Sem nome')),
            'photo_url' => $competitor->foto_url,
            'level' => $this->normalizeLevel((string) ($competitor->nivel ?? '')),
            'level_label' => $this->levelLabel((string) ($competitor->nivel ?? '')),
            'stats' => $record,
        ];
    }

    private function serializeStatsRecord($record): array
    {
        $boas = (int) data_get($record, 'count_boa', 0);
        $errors = (int) data_get($record, 'count_negativas_total', 0);
        $attempts = $boas + $errors;
        $aproveitamento = $attempts > 0
            ? round(($boas / $attempts) * 100, 1)
            : round((float) data_get($record, 'aproveitamento', 0), 1);

        $detail = [];
        foreach (self::DETAIL_FIELDS as $field => $meta) {
            $detail[] = [
                'field' => $field,
                'label' => $meta['label'],
                'tone' => $meta['tone'],
                'value' => (int) data_get($record, $field, 0),
            ];
        }

        return [
            'aproveitamento' => $aproveitamento,
            'boas' => $boas,
            'errors' => $errors,
            'attempts' => $attempts,
            'armadas_label' => $attempts > 0 ? ($boas . '/' . $attempts) : '0/0',
            'destrezas' => $this->computeDestrezas($record),
            'details' => $detail,
        ];
    }

    private function buildComparisonRows(array $recordA, array $recordB, bool $detail = false): array
    {
        $rows = [];

        if (!$detail) {
            $summary = [
                ['field' => 'aproveitamento', 'label' => 'Aproveitamento', 'suffix' => '%', 'higher_wins' => true],
                ['field' => 'attempts', 'label' => 'Armadas', 'suffix' => '', 'higher_wins' => true],
                ['field' => 'boas', 'label' => 'Boas', 'suffix' => '', 'higher_wins' => true],
                ['field' => 'errors', 'label' => 'Erros', 'suffix' => '', 'higher_wins' => false],
                ['field' => 'destrezas', 'label' => 'Destrezas', 'suffix' => '', 'higher_wins' => true],
            ];

            foreach ($summary as $item) {
                $rows[] = $this->compareValues(
                    $item['field'],
                    $item['label'],
                    (float) ($recordA[$item['field']] ?? 0),
                    (float) ($recordB[$item['field']] ?? 0),
                    $item['higher_wins'],
                    $item['suffix']
                );
            }

            return $rows;
        }

        $detailByFieldA = collect($recordA['details'] ?? [])->keyBy('field');
        $detailByFieldB = collect($recordB['details'] ?? [])->keyBy('field');

        foreach (self::DETAIL_FIELDS as $field => $meta) {
            $rows[] = $this->compareValues(
                $field,
                $meta['label'],
                (float) ($detailByFieldA[$field]['value'] ?? 0),
                (float) ($detailByFieldB[$field]['value'] ?? 0),
                $meta['tone'] !== 'negative',
                ''
            );
        }

        return $rows;
    }

    private function buildDetailComparisonRows(array $recordA, array $recordB, string $tone): array
    {
        $rows = [];
        $detailByFieldA = collect($recordA['details'] ?? [])->keyBy('field');
        $detailByFieldB = collect($recordB['details'] ?? [])->keyBy('field');

        foreach (self::DETAIL_FIELDS as $field => $meta) {
            if (($meta['tone'] ?? 'neutral') !== $tone) {
                continue;
            }

            $rows[] = $this->compareValues(
                $field,
                $meta['label'],
                (float) ($detailByFieldA[$field]['value'] ?? 0),
                (float) ($detailByFieldB[$field]['value'] ?? 0),
                $meta['tone'] !== 'negative',
                ''
            );
        }

        return $rows;
    }

    private function compareValues(string $field, string $label, float $valueA, float $valueB, bool $higherWins, string $suffix): array
    {
        $winner = 'tie';

        if ($valueA !== $valueB) {
            if ($higherWins) {
                $winner = $valueA > $valueB ? 'a' : 'b';
            } else {
                $winner = $valueA < $valueB ? 'a' : 'b';
            }
        }

        return [
            'field' => $field,
            'label' => $label,
            'value_a' => $valueA,
            'value_b' => $valueB,
            'display_a' => $this->formatMetricValue($valueA, $suffix),
            'display_b' => $this->formatMetricValue($valueB, $suffix),
            'winner' => $winner,
        ];
    }

    private function formatMetricValue(float $value, string $suffix): string
    {
        $formatted = fmod($value, 1.0) === 0.0
            ? number_format($value, 0, ',', '.')
            : number_format($value, 1, ',', '.');

        return $formatted . $suffix;
    }

    private function computeDestrezas($record): int
    {
        return (int) data_get($record, 'count_limpou_garupa', 0)
            + (int) data_get($record, 'count_cola', 0)
            + (int) data_get($record, 'count_cupim', 0)
            + (int) data_get($record, 'count_top', 0)
            + (int) data_get($record, 'count_pescou', 0)
            + (int) data_get($record, 'count_limpou_cupim_longe', 0)
            + (int) data_get($record, 'count_pescou_uma_aspa', 0)
            + (int) data_get($record, 'count_limpou_top', 0)
            + (int) data_get($record, 'count_limpou_top_mao', 0);
    }

    private function buildEventFilters(Collection $contexts): Collection
    {
        return $contexts
            ->groupBy('rodeio_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'id' => (int) ($first['rodeio_id'] ?? 0),
                    'label' => (string) ($first['rodeio_name'] ?? 'Evento'),
                    'modalidades_count' => $items->pluck('modalidade_id')->filter()->unique()->count(),
                    'divisoes_count' => $items->pluck('divisao_label')->filter()->unique()->count(),
                    'contexts_count' => $items->count(),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE);
    }

    private function buildModalidadeFilters(Collection $contexts): Collection
    {
        return $contexts
            ->groupBy(fn ($item) => $item['rodeio_id'] . ':' . $item['modalidade_id'])
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'id' => (int) ($first['modalidade_id'] ?? 0),
                    'rodeio_id' => (int) ($first['rodeio_id'] ?? 0),
                    'label' => (string) ($first['modalidade_name'] ?? 'Modalidade'),
                    'divisoes_count' => $items->pluck('divisao_label')->filter()->unique()->count(),
                ];
            })
            ->values();
    }

    private function buildDivisaoFilters(Collection $contexts): Collection
    {
        return $contexts
            ->map(function (array $item) {
                return [
                    'rodeio_id' => (int) ($item['rodeio_id'] ?? 0),
                    'modalidade_id' => (int) ($item['modalidade_id'] ?? 0),
                    'label' => (string) ($item['divisao_label'] ?? 'Geral'),
                    'value' => (string) ($item['divisao'] ?? ''),
                ];
            })
            ->unique(fn ($item) => implode(':', [$item['rodeio_id'], $item['modalidade_id'], $item['value']]))
            ->values();
    }

    private function findContextStats(int $competitorId, array $contextInfo): ?CompetitorContextStat
    {
        if (!Schema::hasTable('competitor_stats')) {
            return null;
        }

        $query = CompetitorContextStat::query()
            ->where('competitor_id', $competitorId)
            ->when(!empty($contextInfo['rodeio_id']), fn ($builder) => $builder->where('rodeio_id', (int) $contextInfo['rodeio_id']))
            ->when(!empty($contextInfo['modalidade_id']), fn ($builder) => $builder->where('modalidade_id', (int) $contextInfo['modalidade_id']));

        $divisao = trim((string) ($contextInfo['divisao'] ?? ''));
        if ($divisao !== '') {
            $query->where('divisao', $divisao);
        }

        return $query->first();
    }

    private function normalizeLevel(string $level): string
    {
        $level = strtolower(trim($level));

        return match ($level) {
            'legado' => 'ascendente',
            'presilha' => 'competidor',
            default => $level !== '' ? $level : 'competidor',
        };
    }

    private function levelLabel(string $level): string
    {
        return match ($this->normalizeLevel($level)) {
            'favorito' => 'Favorito',
            'elite' => 'Elite',
            'ascendente' => 'Ascendente',
            default => 'Competidor',
        };
    }

    private function shortName(string $name): string
    {
        $tokens = preg_split('/\s+/', trim($name)) ?: [];
        $tokens = array_values(array_filter($tokens, static fn ($token) => $token !== ''));

        return empty($tokens) ? 'Sem nome' : collect($tokens)->take(2)->implode(' ');
    }

    private function buildContextLabel(array $contextInfo): string
    {
        $parts = [];
        if (!empty($contextInfo['rodeio_id'])) {
            $parts[] = 'Evento';
        }
        if (!empty($contextInfo['modalidade_id'])) {
            $parts[] = 'Modalidade';
        }
        if (!empty($contextInfo['divisao'])) {
            $parts[] = $contextInfo['divisao'];
        }

        return empty($parts) ? 'Resumo geral' : implode(' • ', $parts);
    }

    private function serializeFollowEvent(CompetitorFollowEvent $event): array
    {
        $metadata = is_array($event->metadata) ? $event->metadata : [];

        return [
            'id' => (int) $event->id,
            'type' => (string) $event->event_type,
            'title' => (string) $event->title,
            'message' => (string) $event->message,
            'cta_label' => (string) ($event->cta_label ?? 'Ver mais'),
            'cta_url' => (string) ($event->cta_url ?? ''),
            'rodeio_name' => (string) ($event->rodeio?->name ?? ''),
            'modalidade_name' => (string) ($event->modalidade?->nome ?? ''),
            'divisao' => (string) ($metadata['divisao'] ?? ''),
            'group_name' => (string) ($metadata['group_name'] ?? ''),
            'prize_label' => (string) ($metadata['prize_label'] ?? ''),
            'metadata' => $metadata,
            'created_at' => $event->created_at?->toIso8601String(),
            'created_human' => $event->created_at?->diffForHumans(),
        ];
    }
}
