<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Rodeio;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\League;
use App\Models\Modalidade;
use App\Models\Outcome;
use App\Models\Page;
use App\Models\SubscriptionPlan;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\CompetitorContextStat;
use App\Models\BotUser;
use App\Models\FantasyLeague;
use App\Models\X1Payment;
use App\Services\CompetitorOddsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SiteController extends Controller {
    public function landingPage(Request $request)
    {
        return $this->bolaoHub($request);
    }

    public function bolaoHub(Request $request)
    {
        $request->merge(['launch_sector' => 'bolao']);

        return $this->hubDashboard($request);
    }

    public function hubBolaoInicio(Request $request)
    {
        $request->merge(['launch_sector' => 'bolao']);

        return $this->hubInicio($request);
    }

    private function buildHubInicioCards(Collection $entries, string $homeMode, array $oddsByCompetitor, array $oddsByGroup, bool $isPremiumUser, array $context): Collection
    {
        return $entries->map(function ($entry) use ($homeMode, $oddsByCompetitor, $oddsByGroup, $isPremiumUser, $context) {
            $isGroupMode = $homeMode === 'group';
            $entryId = (int) ($entry->id ?? 0);
            $oddsData = $isGroupMode ? ($oddsByGroup[$entryId] ?? null) : ($oddsByCompetitor[$entryId] ?? null);
            $x1Count = (int) ($oddsData['x1_count'] ?? 0);
            $freeMultiplier = (float) ($oddsData['free_multiplier'] ?? 1.90);
            $premiumMultiplier = (float) ($oddsData['premium_multiplier'] ?? 1.93);

            if ($isGroupMode) {
                $members = collect($entry->members ?? []);
                $memberIds = $members->pluck('id')
                    ->filter(fn ($id) => (int) $id > 0)
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
                $memberNames = $members->pluck('nome')
                    ->filter(fn ($name) => trim((string) $name) !== '')
                    ->map(fn ($name) => trim((string) $name))
                    ->values()
                    ->all();
                $memberPhotos = $members->map(function ($member) {
                    return $member->foto_url ?: asset('assets/images/logo_icon/logo.png');
                })->values()->all();
                $captainName = trim((string) ($members->first()->nome ?? ''));
                $entryNameRaw = trim((string) ($entry->nome ?: $members->pluck('nome')->implode(' + ')));
                $entryNameRaw = $entryNameRaw !== '' ? $entryNameRaw : ('Grupo #' . $entryId);
                $entryPhoto = optional($members->first())->foto_url ?: asset('assets/images/logo_icon/logo.png');
                $entrySubtitle = Str::limit($members->pluck('nome')->implode(' • '), 60, '...');
                $nivelRaw = (string) ($oddsData['nivel_key'] ?? 'elite');
                $searchText = trim(implode(' ', array_filter([
                    $entryNameRaw,
                    $members->pluck('nome')->implode(' '),
                    $context['modalidade_nome'] ?? '',
                    $context['rodeio_nome'] ?? '',
                ])));
            } else {
                $memberIds = [$entryId];
                $memberNames = [trim((string) ($entry->nome ?? ''))];
                $memberPhotos = [$entryPhoto = ($entry->foto_url ?: asset('assets/images/logo_icon/logo.png'))];
                $entryNameRaw = trim((string) ($entry->nome ?? ''));
                $entrySubtitle = '';
                $nivelRaw = (string) ($entry->nivel ?? 'competidor');
                $searchText = trim(implode(' ', array_filter([
                    $entryNameRaw,
                    $context['modalidade_nome'] ?? '',
                    $context['rodeio_nome'] ?? '',
                ])));
            }

            $nivelKey = strtolower(trim((string) preg_replace('/\s+/', '', $nivelRaw)));
            $nivelKey = strtr($nivelKey, ['á' => 'a', 'ã' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
            if ($nivelKey === 'legado') {
                $nivelKey = 'ascendente';
            }
            if ($nivelKey === 'presilha') {
                $nivelKey = 'competidor';
            }
            if (!in_array($nivelKey, ['favorito', 'elite', 'ascendente', 'competidor'], true)) {
                $nivelKey = 'competidor';
            }

            $competitorDisplayName = $isGroupMode ? ($captainName !== '' ? $captainName : $entryNameRaw) : $entryNameRaw;
            if (!$isGroupMode) {
                $nameParts = preg_split('/\s+/u', $competitorDisplayName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if (count($nameParts) === 2) {
                    $firstName = trim($nameParts[0]);
                    $secondName = trim($nameParts[1]);
                    $firstNameIsLarge = Str::length($firstName) >= 10;

                    if (Str::length($firstName) > 12) {
                        $firstName = Str::substr($firstName, 0, 11) . '.';
                    }

                    $joinedLength = Str::length($firstName) + Str::length($secondName);
                    if ($firstNameIsLarge || Str::length($secondName) > 8 || $joinedLength > 16) {
                        $secondName = Str::upper(Str::substr($secondName, 0, 1)) . '.';
                    }

                    $competitorDisplayName = trim($firstName . ' ' . $secondName);
                } elseif (count($nameParts) > 2) {
                    $firstName = array_shift($nameParts);
                    $lastName = array_pop($nameParts);
                    $middleInitials = array_map(static function (string $part): string {
                        return Str::upper(Str::substr($part, 0, 1)) . '.';
                    }, $nameParts);
                    $competitorDisplayName = trim($firstName . ' ' . implode(' ', $middleInitials) . ' ' . $lastName);
                }
            }

            if (!$isGroupMode) {
                $competitorDisplayName = Str::limit($competitorDisplayName, 16, '...');
            }

            if ($x1Count >= 25) {
                $neonClass = 'bets-25-plus';
            } elseif ($x1Count >= 20) {
                $neonClass = 'bets-20-24';
            } elseif ($x1Count >= 15) {
                $neonClass = 'bets-15-19';
            } elseif ($x1Count >= 10) {
                $neonClass = 'bets-10-14';
            } elseif ($x1Count >= 5) {
                $neonClass = 'bets-5-9';
            } else {
                $neonClass = 'bets-0-4';
            }

            return [
                'entry_id' => $entryId,
                'entry_type' => $isGroupMode ? 'group' : 'competitor',
                'entry_name_raw' => $entryNameRaw,
                'entry_photo' => $entryPhoto,
                'entry_subtitle' => $entrySubtitle,
                'search_text' => $searchText,
                'member_ids' => $memberIds,
                'member_names' => $memberNames,
                'member_photos' => $memberPhotos,
                'member_count' => count($memberIds),
                'captain_name' => $isGroupMode ? ($captainName !== '' ? $captainName : $entryNameRaw) : $entryNameRaw,
                'nivel_key' => $nivelKey,
                'competitor_display_name' => $competitorDisplayName,
                'free_multiplier' => $freeMultiplier,
                'premium_multiplier' => $premiumMultiplier,
                'neon_class' => $neonClass,
                'modalidade_id' => $context['modalidade_id'] ?? null,
                'modalidade_nome' => $context['modalidade_nome'] ?? null,
                'rodeio_id' => $context['rodeio_id'] ?? null,
                'rodeio_nome' => $context['rodeio_nome'] ?? null,
                'divisao' => $context['divisao'] ?? null,
                'sort_multiplier' => $isPremiumUser ? $premiumMultiplier : $freeMultiplier,
            ];
        })->sortByDesc('sort_multiplier')->values();
    }

    private function buildHubInicioSection($modalidade, ?Rodeio $activeRodeio, ?string $divisao, bool $isPremiumUser): array
    {
        $context = [
            'rodeio_id' => $activeRodeio?->id ?: $modalidade?->rodeio_id,
            'rodeio_nome' => $activeRodeio?->nome ?? $activeRodeio?->titulo ?? $activeRodeio?->name ?? null,
            'modalidade_id' => $modalidade?->id,
            'modalidade_nome' => $modalidade?->nome,
            'divisao' => $divisao,
        ];

        $teamSize = (int) ($modalidade->tamanho_equipe ?? 1);
        $homeMode = $teamSize > 1 ? 'group' : 'competitor';
        $entries = collect();
        $competitors = collect();
        $oddsByCompetitor = [];
        $oddsByGroup = [];
        $oddsAutomation = [
            'settings' => [],
            'finance' => [
                'paid_volume' => 0.0,
                'house_fee' => 0.0,
                'margin_percent' => 0.0,
            ],
            'boost_global' => false,
        ];

        if ($homeMode === 'group') {
            $groupsQuery = $modalidade->competitorGroups()
                ->whereNotIn('status', ['desqualificado', 'inativo'])
                ->with(['members' => function ($query) {
                    $query->select('competitors.id', 'competitors.nome', 'competitors.foto', 'competitors.nivel', 'competitors.created_at');
                    if (Schema::hasTable('competitor_stats')) {
                        $query->with('stats');
                    }
                }]);

            if (!empty($divisao) && Schema::hasColumn('modalidade_competitor_groups', 'divisao')) {
                $groupsQuery->where('divisao', $divisao);
            }

            $entries = $groupsQuery->get()
                ->filter(function ($group) use ($teamSize) {
                    return $group->members && $group->members->count() >= $teamSize;
                })
                ->values();

            if ($entries->isNotEmpty()) {
                $oddsResult = app(CompetitorOddsService::class)->buildGroupOddsMap(
                    $entries,
                    $context['rodeio_id'] ? (int) $context['rodeio_id'] : null,
                    $context['modalidade_id'] ? (int) $context['modalidade_id'] : null,
                    $isPremiumUser
                );

                $oddsByGroup = $oddsResult['odds'] ?? [];
                $oddsAutomation = [
                    'settings' => $oddsResult['settings'] ?? [],
                    'finance' => $oddsResult['finance'] ?? $oddsAutomation['finance'],
                    'boost_global' => (bool) ($oddsResult['boost_global'] ?? false),
                ];
            }
        } else {
            $competitorsQuery = $modalidade->competitors();
            if (Schema::hasTable('competitor_stats')) {
                $competitorsQuery->with('stats');
            }

            if (!empty($divisao) && Schema::hasTable('competitor_modalidade') && Schema::hasColumn('competitor_modalidade', 'divisao')) {
                $competitorsQuery->wherePivot('divisao', $divisao);
            }

            $competitors = $competitorsQuery->get()
                ->sortByDesc(function ($competitor) {
                    return (float) (optional($competitor->stats)->pontuacao_total ?? 0);
                })
                ->values();

            $entries = $competitors;

            if ($competitors->isNotEmpty()) {
                $oddsResult = app(CompetitorOddsService::class)->buildOddsMap(
                    $competitors,
                    $context['rodeio_id'] ? (int) $context['rodeio_id'] : null,
                    $context['modalidade_id'] ? (int) $context['modalidade_id'] : null,
                    $isPremiumUser
                );

                $oddsByCompetitor = $oddsResult['odds'] ?? [];
                $oddsAutomation = [
                    'settings' => $oddsResult['settings'] ?? [],
                    'finance' => $oddsResult['finance'] ?? $oddsAutomation['finance'],
                    'boost_global' => (bool) ($oddsResult['boost_global'] ?? false),
                ];
            }
        }

        $cards = $this->buildHubInicioCards(
            $entries,
            $homeMode,
            $oddsByCompetitor,
            $oddsByGroup,
            $isPremiumUser,
            $context
        );

        $sectionKey = 'modalidade-' . (int) ($modalidade->id ?? 0);
        $desktopPrimaryCards = $homeMode === 'group'
            ? $cards
            : $cards->filter(fn ($card) => in_array($card['nivel_key'], ['favorito', 'elite'], true))->values();
        $desktopSecondaryCards = $homeMode === 'group'
            ? collect()
            : $cards->filter(fn ($card) => in_array($card['nivel_key'], ['ascendente', 'competidor'], true))->values();

        if ($homeMode !== 'group' && $desktopPrimaryCards->isEmpty()) {
            $desktopPrimaryCards = $cards;
            $desktopSecondaryCards = collect();
        }

        $divisaoLabel = trim((string) ($context['divisao'] ?? ''));
        $modeLabel = $homeMode === 'group'
            ? ($teamSize === 2 ? 'Duplas da modalidade' : 'Equipes da modalidade')
            : 'Competidores da modalidade';

        $subtitleParts = array_filter([
            $context['rodeio_nome'] ?? null,
            $divisaoLabel !== '' ? 'Divisão ' . $divisaoLabel : null,
            $homeMode === 'group'
                ? 'Odd do grupo e entrada por equipe'
                : 'Odd individual e entrada por competidor',
        ]);

        return [
            'section_key' => $sectionKey,
            'mode' => $homeMode,
            'is_group_mode' => $homeMode === 'group',
            'team_size' => $teamSize,
            'max_bolao_price' => 0.0,
            'entry_count' => $entries->count(),
            'x1_betting_open' => $homeMode !== 'group' || $entries->count() >= 3,
            'x1_closed_message' => $homeMode === 'group' && $entries->count() < 3
                ? 'X1 indisponível. São necessários ao menos 3 grupos para liberar o duelo.'
                : null,
            'mode_label' => $modeLabel,
            'subtitle' => implode(' • ', $subtitleParts),
            'entries' => $entries,
            'competitors' => $competitors,
            'cards' => $cards,
            'desktop_primary_cards' => $desktopPrimaryCards,
            'desktop_secondary_cards' => $desktopSecondaryCards,
            'desktop_primary_label' => $homeMode === 'group' ? 'Grupos da modalidade' : 'Favoritos e elite',
            'desktop_secondary_label' => 'Ascendentes e competidores',
            'context' => $context,
            'odds_by_competitor' => $oddsByCompetitor,
            'odds_by_group' => $oddsByGroup,
            'odds_automation' => $oddsAutomation,
            'carousel_ids' => [
                'main' => 'rrInicioCarousel-' . $sectionKey,
                'mobile' => 'rrInicioCarouselMobile-' . $sectionKey,
                'primary' => 'rrInicioCarouselPrimary-' . $sectionKey,
                'secondary' => 'rrInicioCarouselSecondary-' . $sectionKey,
                'bolao' => 'rrBolaoGrid-' . $sectionKey,
                'x1' => 'rrX1RoomGrid-' . $sectionKey,
            ],
        ];
    }

    private function resolveHubInicioSectionBolaoPrices(Collection $modalidades, ?Rodeio $activeRodeio, ?string $divisao): array
    {
        $modalidadeIds = $modalidades->pluck('id')
            ->filter(fn ($id) => (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($modalidadeIds)) {
            return [];
        }

        $query = FantasyLeague::query()
            ->selectRaw('modalidade_id, MAX(COALESCE(price, 0)) as max_bolao_price')
            ->whereIn('modalidade_id', $modalidadeIds)
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhere('status', 'finalized');
            });

        if (($activeRodeio?->id ?? 0) > 0) {
            $query->where('rodeio_id', (int) $activeRodeio->id);
        }

        $divisao = trim((string) ($divisao ?? ''));
        if ($divisao !== '') {
            $query->where(function ($q) use ($divisao) {
                $q->where('divisao', $divisao)
                    ->orWhereNull('divisao')
                    ->orWhere('divisao', '');
            });
        }

        return $query
            ->groupBy('modalidade_id')
            ->pluck('max_bolao_price', 'modalidade_id')
            ->map(fn ($value) => round((float) $value, 2))
            ->all();
    }
    private function activeTransmissionStatuses(): array
    {
        return [
            'ao_vivo',
            'pausado',
            'programado',
            'classificatoria',
            'em_apuracao',
            'inicio_finais',
            'divisao_finalizada',
        ];
    }

    private function liveDisplayStatuses(): array
    {
        return array_values(array_filter(
            $this->activeTransmissionStatuses(),
            static fn (string $status): bool => $status !== 'programado'
        ));
    }

    private function publicAssetUrl(?string $path): ?string
    {
        $value = trim((string) ($path ?? ''));
        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $value = str_replace('\\', '/', $value);
        $value = ltrim($value, '/');
        $lower = strtolower($value);

        if (str_starts_with($lower, 'public/')) {
            $value = substr($value, strlen('public/'));
            $lower = strtolower($value);
        }

        if (str_starts_with($lower, 'assets/')) {
            return asset($value);
        }

        $resolved = publicStorageUrl($value);

        return $resolved !== '' ? $resolved : null;
    }

    public function rodeioLogo(Rodeio $rodeio)
    {
        $path = trim((string) ($rodeio->logo ?? ''));
        if ($path === '') {
            abort(404);
        }

        $normalized = normalizePublicAssetPath($path);
        if ($normalized === '') {
            abort(404);
        }

        $publicCandidate = public_path($normalized);
        if (is_file($publicCandidate)) {
            return response()->file($publicCandidate);
        }

        $storageCandidate = preg_replace('#^storage/#i', '', $normalized);
        if ($storageCandidate && Storage::disk('public')->exists($storageCandidate)) {
            return Storage::disk('public')->response($storageCandidate);
        }

        abort(404);
    }

    private function resolveHubFeaturedRodeio(): array
    {
        $activeRodeio = null;
        $hubLiveMode = 'empty';
        $featuredRodeios = collect();
        $hasTransmissionStatus = Schema::hasColumn('rodeios', 'status_transmissao');
        $hasStartColumn = Schema::hasColumn('rodeios', 'start');
        $today = Carbon::now()->startOfDay();

        if ($hasTransmissionStatus) {
            $liveRodeios = Rodeio::query()
                ->with(['modalidadeAtual'])
                ->whereNotNull('status_transmissao')
                ->whereIn('status_transmissao', $this->liveDisplayStatuses())
                ->orderByRaw("CASE WHEN status_transmissao = 'ao_vivo' THEN 0 ELSE 1 END")
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->limit(6)
                ->get();

            $scheduledRodeioQuery = Rodeio::query()
                ->with(['modalidadeAtual'])
                ->where('status_transmissao', 'programado');

            if ($hasStartColumn) {
                $scheduledRodeioQuery->where(function ($query) use ($today) {
                    $query->whereNull('start')
                        ->orWhere('start', '>=', $today);
                });

                $scheduledRodeioQuery
                    ->orderByRaw('CASE WHEN start IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('start')
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id');
            } else {
                $scheduledRodeioQuery
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id');
            }

            $scheduledRodeios = $scheduledRodeioQuery
                ->limit(6)
                ->get();
            $liveRodeio = $liveRodeios->first();
            $scheduledRodeio = $scheduledRodeios->first();

            if ($liveRodeio) {
                $activeRodeio = $liveRodeio;
                $hubLiveMode = 'live';
                $featuredRodeios = $liveRodeios
                    ->concat($scheduledRodeios)
                    ->unique('id')
                    ->take(6)
                    ->values();
            } elseif ($scheduledRodeio) {
                $activeRodeio = $scheduledRodeio;
                $hubLiveMode = 'scheduled';
                $featuredRodeios = $scheduledRodeios
                    ->unique('id')
                    ->take(6)
                    ->values();
            } else {
                $activeRodeio = Rodeio::query()
                    ->with(['modalidadeAtual'])
                    ->where('status', 'ativo')
                    ->when($hasStartColumn, function ($query) use ($today) {
                        $query->where(function ($innerQuery) use ($today) {
                            $innerQuery->whereNull('start')
                                ->orWhere('start', '>=', $today);
                        });
                    })
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();

                if ($activeRodeio) {
                    $hubLiveMode = 'scheduled';
                    $featuredRodeios = collect([$activeRodeio]);
                }
            }
        }

        if (!$activeRodeio && !$hasTransmissionStatus) {
            $activeRodeio = Rodeio::query()
                ->with(['modalidadeAtual'])
                ->when($hasStartColumn, function ($query) use ($today) {
                    $query->where(function ($innerQuery) use ($today) {
                        $innerQuery->whereNull('start')
                            ->orWhere('start', '>=', $today);
                    });
                })
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
            $hubLiveMode = $activeRodeio ? 'scheduled' : 'empty';
            $featuredRodeios = $activeRodeio ? collect([$activeRodeio]) : collect();
        }

        return [
            'rodeio' => $activeRodeio,
            'mode' => $hubLiveMode,
            'rodeios' => $featuredRodeios,
        ];
    }

    private function buildHubFeaturedRodeioUiPayload(?Rodeio $rodeio, string $mode, $featuredRodeios = null): array
    {
        $safeMode = in_array($mode, ['live', 'scheduled'], true) ? $mode : 'empty';
        $logoUrl = asset('assets/images/logo_icon/logo.png');
        $timerIso = null;
        $featuredLogoItems = collect($featuredRodeios)
            ->filter(fn ($item) => $item instanceof Rodeio)
            ->unique('id')
            ->take(2)
            ->values();

        if ($featuredLogoItems->isEmpty() && $rodeio) {
            $featuredLogoItems = collect([$rodeio]);
        }

        if ($rodeio && filled($rodeio->logo)) {
            $logoUrl = route('rodeios.logo', $rodeio);
        }

        if ($rodeio) {
            try {
                $timerRaw = $safeMode === 'live'
                    ? ($rodeio->end ?? null)
                    : ($rodeio->start ?? null);

                if ($timerRaw) {
                    $timerAt = Carbon::parse($timerRaw);
                    if ($timerAt->isFuture()) {
                        $timerIso = $timerAt->toIso8601String();
                    }
                }
            } catch (\Throwable $e) {
                $timerIso = null;
            }
        }

        $logos = $featuredLogoItems->map(function (Rodeio $item) {
            return [
                'rodeio_id' => $item->id,
                'title' => trim((string) (($item->nome ?? $item->titulo ?? $item->name ?? null) ?: 'Rei do Rodeio')),
                'logo_url' => filled($item->logo)
                    ? route('rodeios.logo', $item)
                    : asset('assets/images/logo_icon/logo.png'),
            ];
        })->values()->all();

        $items = $featuredLogoItems->map(function (Rodeio $item) {
            $itemMode = ((string) ($item->status_transmissao ?? '') === 'ao_vivo') ? 'live' : 'scheduled';
            $itemTimerIso = null;

            try {
                $itemTimerRaw = $itemMode === 'live'
                    ? ($item->end ?? null)
                    : ($item->start ?? null);

                if ($itemTimerRaw) {
                    $itemTimerAt = Carbon::parse($itemTimerRaw);
                    if ($itemTimerAt->isFuture()) {
                        $itemTimerIso = $itemTimerAt->toIso8601String();
                    }
                }
            } catch (\Throwable $e) {
                $itemTimerIso = null;
            }

            return [
                'rodeio_id' => $item->id,
                'title' => trim((string) (($item->nome ?? $item->titulo ?? $item->name ?? null) ?: 'Rei do Rodeio')),
                'logo_url' => filled($item->logo)
                    ? route('rodeios.logo', $item)
                    : asset('assets/images/logo_icon/logo.png'),
                'timer_iso' => $itemTimerIso,
                'mode' => $itemMode,
                'badge' => $itemMode === 'live' ? 'Ao vivo agora' : 'Programado',
                'accent' => $itemMode === 'live' ? 'Arena aberta' : 'Próximo evento',
                'label' => $itemMode === 'live' ? 'Rodeio termina em' : 'Começa em',
                'status_transmissao' => (string) ($item->status_transmissao ?? ''),
            ];
        })->values()->all();

        return [
            'mode' => $safeMode,
            'title' => trim((string) (($rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? null) ?: 'Rei do Rodeio')),
            'logo_url' => $logoUrl,
            'logos' => $logos,
            'items' => $items,
            'timer_iso' => $timerIso,
            'badge' => $safeMode === 'live' ? 'Ao vivo agora' : 'Programado',
            'accent' => $safeMode === 'live' ? 'Arena aberta' : 'Próximo evento',
            'label' => $safeMode === 'live' ? 'Rodeio termina em' : 'Começa em',
            'rodeio_id' => $rodeio?->id,
            'status_transmissao' => (string) ($rodeio->status_transmissao ?? ''),
        ];
    }

    private function getActiveLeagueAndCategory($leagueSlug, $categoryId) {
        $activeCategory = Rodeio::where('status', 'ativo');
        if ($categoryId) {
            $activeCategory->where('id', $categoryId);
        } else {
            $activeCategory->orderBy('name');
        }

        $activeCategory = $activeCategory->first();

        $activeLeague = null;

        if ($leagueSlug) {
            $activeLeague   = League::activeForUser()->where('slug', $leagueSlug)->firstOrFail();
            $activeCategory = $activeLeague?->category;
        }

        return [$activeLeague, $activeCategory];
    }

    private function homePageMarkets() {
        return ['h2h', 'h2h_3way', 'spreads', 'totals', 'outrights', 'winner', 'points_armadas', 'points_destresas', 'points_extras'];
    }

    public function gamesByLeague($id) {
        // Redireciona direto para /
        return redirect()->route('home');
    }
    
    public function gamesByCategory($id) {
        // Redireciona direto para /
        return redirect()->route('home');
    }

    public function switchType() {
        $url = url()->previous() ?? '/';
        if (session()->has('game_type')) {
            session()->forget('game_type');
        } else {
            session()->put('game_type', 'live');
        }

        return redirect($url);
    }

    public function oddsType($type) {
        session()->put('odds_type', $type);
        return redirect()->back();
    }

    public function pages($slug) {
        $page        = Page::where('tempname', activeTemplate())->where('slug', $slug)->firstOrFail();
        $pageTitle   = $page->name;
        $sections    = $page->secs;
        $seoContents = $page->seo_content;
        $seoImage    = @$seoContents->image ? getImage(getFilePath('seo') . '/' . @$seoContents->image, getFileSize('seo')) : null;
        return view('Template::pages', compact('pageTitle', 'sections', 'seoContents', 'seoImage'));
    }

    public function contact() {
    $pageTitle = "Contact Us";
    $user      = Auth::user();
        return view('Template::contact', compact('pageTitle', 'user'));
    }

    public function contactSubmit(Request $request) {

        $request->validate([
            'name'    => 'required',
            'email'   => 'required',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $random = getNumber();

    $ticket           = new SupportTicket();
    $ticket->user_id  = Auth::id() ?? 0;
        $ticket->name     = $request->name;
        $ticket->email    = $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;

        $ticket->ticket     = $random;
        $ticket->subject    = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status     = Status::TICKET_OPEN;
        $ticket->save();

    $adminNotification            = new AdminNotification();
    $adminNotification->user_id   = Auth::user() ? Auth::user()->id : 0;
        $adminNotification->title     = 'A new contact message has been submitted';
        $adminNotification->click_url = route('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message                    = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message           = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug) {
        $policy      = Frontend::where('slug', $slug)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle   = $policy->data_values->title;
        $seoContents = $policy->seo_content;
        $seoImage    = @$seoContents->image ? frontendImage('policy_pages', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::policy', compact('policy', 'pageTitle', 'seoContents', 'seoImage'));
    }

    public function changeLanguage($lang = null) {
        $language = Language::where('code', $lang)->first();
        if (!$language) {
            $lang = 'en';
        }

        session()->put('lang', $lang);
        return back();
    }

    public function blog() {
        $pageTitle = "News and Updates";
        $blogs     = Frontend::where('data_keys', 'blog.element')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::blog', compact('pageTitle', 'blogs'));
    }

    public function blogDetails($slug) {
        $blog        = Frontend::where('slug', $slug)->where('data_keys', 'blog.element')->firstOrFail();
        $pageTitle   = 'Read Full News';
        $latestBlogs = Frontend::where('id', '!=', $blog->id)->where('data_keys', 'blog.element')->orderBy('id', 'desc')->limit(10)->get();
        $seoContents = $blog->seo_content;
        $seoImage    = @$seoContents->image ? frontendImage('blog', $seoContents->image, getFileSize('seo'), true) : null;
        return view('Template::blog_details', compact('blog', 'pageTitle', 'seoContents', 'seoImage', 'latestBlogs'));
    }

    public function cookieAccept() {
        Cookie::queue('gdpr_cookie', gs('site_name'), 43200);
    }

    public function cookiePolicy() {
        $cookieContent = Frontend::where('data_keys', 'cookie.data')->first();
        abort_if($cookieContent->data_values->status != Status::ENABLE, 404);
        $pageTitle = 'Cookie Policy';
        $cookie    = Frontend::where('data_keys', 'cookie.data')->first();
        return view('Template::cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null) {
        $pattern = '/\d+x\d+/';
        if (preg_match($pattern, $size)) {

            $imgWidth  = explode('x', $size)[0];
            $imgHeight = explode('x', $size)[1];
            $text      = $imgWidth . '×' . $imgHeight;
            $color     = [100, 100, 100];
            $bgColor   = [255, 255, 255];
            $fontSize  = round(($imgWidth - 50) / 8);
        } else {
            $text      = $size;
            $imgWidth  = 50;
            $imgHeight = 50;

            $color    = [255, 255, 255];
            $bgColor  = generateRandomColor();
            $fontSize = 22;
        }

        $fontFile = realpath('assets/font/solaimanLipi_bold.ttf');

        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        // Se GD não estiver disponível, retornar um PNG vazio como fallback para evitar erro fatal
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagettftext')) {
            // PNG 1x1 transparente
            $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=');
            header('Content-Type: image/png');
            echo $pngData;
            return;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, ...$color);
        $bgFill    = imagecolorallocate($image, ...$bgColor);
        imagefill($image, 0, 0, $bgFill);
        $textBox    = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance() {
        $pageTitle = 'Maintenance Mode';
        if (gs('maintenance_mode') == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        return view('Template::maintenance', compact('pageTitle', 'maintenance'));
    }

    public function hubDashboard(Request $request) {
        if ($request->ajax() || $request->boolean('hub_partial')) {
            return $this->hubInicio();
        }

        $launchSector = trim((string) $request->input('launch_sector', ''));
        $pageTitle = 'Central de entretenimento Country Digital';

        $featuredRodeio = $this->resolveHubFeaturedRodeio();
        $activeRodeio = $featuredRodeio['rodeio'] ?? null;
        $hubLiveMode = $featuredRodeio['mode'] ?? 'empty';
        $hubFeaturedUi = $this->buildHubFeaturedRodeioUiPayload(
            $activeRodeio,
            $hubLiveMode,
            $featuredRodeio['rodeios'] ?? collect()
        );
        $hubLiveTimerIso = $hubFeaturedUi['timer_iso'] ?? null;
        $hubRodeioLogoUrl = $hubFeaturedUi['logo_url'] ?? asset('assets/images/logo_icon/logo.png');
        $hubFeaturedLogos = $hubFeaturedUi['logos'] ?? [];
        $hubFeaturedItems = $hubFeaturedUi['items'] ?? [];

        // Banners
        $banners = \App\Models\Banner::where('status', 'ativo')->latest()->get();

        // Dados auxiliares (lista recente apenas para vitrine)
        $recentGames = collect();

            // Dados para fantasy leagues
            $fantasyLeagues = collect();
            $fantasyStats = [];

        // Dados para salas X1 (reais)
        $x1Query = \App\Models\X1RoomInstance::with([
                'host',
                'hostBot',
                'opponentBot',
                'modalidade',
                'participants.user',
                'participants.competitor',
                'participants.competitorGroup.members'
            ])
            ->latest();
        $x1Rooms = $x1Query->take(8)->get()->map(function ($room) {
            $opponent = $room->participants->firstWhere('is_host', false);
            // Bots e usuários com show_in_listings=false são mascarados
            if ($room->bot_criador_id && $room->hostBot) {
                $challengerName = $room->hostBot->getPublicUsername();
            } else {
                $challengerName = $room->host ? $room->host->getPublicUsername() : 'Usuário';
            }
            if ($room->bot_oponente_id && $room->opponentBot) {
                $opponentName = $room->opponentBot->getPublicUsername();
            } else {
                $opponentName = $opponent?->user ? $opponent->user->getPublicUsername() : null;
            }
            return (object) [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'challenger_name' => $challengerName,
                'opponent_name' => $opponentName,
                'amount' => $room->valor_entrada,
                'modalidade_name' => $room->modalidade?->nome,
                'created_at' => $room->created_at,
                'status' => $room->status,
                'is_my_room' => auth()->check() && $room->host_user_id === auth()->id(),
                'is_premium' => ($room->fee_percent ?? 20) < 20,
            ];
        });

        $x1Stats = [
            'total_rooms' => \App\Models\X1RoomInstance::count(),
            'my_challenges' => auth()->check() ? \App\Models\X1RoomInstance::where('host_user_id', auth()->id())->count() : 0,
            'wins' => 0,
            'losses' => 0,
            'total_winnings' => 0,
        ];

        // Modalidades para criação de salas X1 (apenas ativas para X1)
        $modalidades = \App\Models\Modalidade::where('pausar_x1', false)->orderBy('nome')->get();

        // Rodeios para criação de salas X1
        $rodeioQuery = \App\Models\Rodeio::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'nome')) {
            $rodeioQuery->orderBy('nome');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'titulo')) {
            $rodeioQuery->orderBy('titulo');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'name')) {
            $rodeioQuery->orderBy('name');
        } else {
            $rodeioQuery->orderBy('id');
        }
        $rodeiosRaw = $rodeioQuery->get();
        $rodeios = $rodeiosRaw->map(function ($rodeio) {
            return (object) [
                'id' => $rodeio->id,
                'label' => $rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? ('Rodeio #' . $rodeio->id),
            ];
        });

        return view('frontend.inicial_hub', compact(
            'pageTitle',
            'launchSector',
            'activeRodeio',
            'recentGames',
            'fantasyLeagues',
            'fantasyStats',
            'x1Rooms',
            'x1Stats',
            'modalidades',
            'rodeios',
            'banners',
            'hubLiveMode',
            'hubLiveTimerIso',
            'hubRodeioLogoUrl',
            'hubFeaturedLogos',
            'hubFeaturedItems'
        ))->with('isHubPage', true);
    }

    public function premiumLanding(Request $request) {
        $query = $request->query();
        $query['tab'] = 'premium';

        return redirect()->route('home', $query);
    }

    public function hubPremium() {
        return view('frontend.partials.inicial_premium_content');
    }

    public function arenaStatisticsData(Request $request)
    {
        $user = $request->user();
        $isPremiumUser = $user ? (bool) $user->isPremium() : false;
        $subscriptionStatus = $user ? $user->subscriptionStatus() : [
            'is_premium' => false,
            'status' => 'guest',
            'can_trial' => true,
        ];

        $payload = [
            'access' => [
                'is_authenticated' => (bool) $user,
                'is_premium' => $isPremiumUser,
                'status' => (string) ($subscriptionStatus['status'] ?? 'free'),
                'label' => $this->statisticsAccessLabel($subscriptionStatus),
                'plan' => (string) ($subscriptionStatus['plan'] ?? ''),
                'days_remaining' => (int) ($subscriptionStatus['days_remaining'] ?? 0),
            ],
            'support_phone' => '5547997953323',
            'plans' => $this->statisticsArenaPlans(),
            'filters' => [
                'rodeios' => [],
                'modalidades' => [],
                'divisoes' => [],
                'selected' => [
                    'rodeio_id' => null,
                    'modalidade_id' => null,
                    'divisao' => '',
                ],
            ],
            'scope' => [
                'rodeio_nome' => 'Arena Estatísticas',
                'modalidade_nome' => 'Aguardando transmissão',
                'divisao' => '',
                'logo_url' => asset('assets/images/logo_icon/logo.png'),
                'event_mode' => 'empty',
            ],
            'summary' => [
                'competitors' => 0,
                'total_points' => 0,
                'average_points' => 0,
                'average_aproveitamento' => 0,
                'leader_name' => null,
                'leader_points' => 0,
                'last_update_label' => 'Sem pontuação ao vivo',
            ],
            'entries' => [],
            'has_data' => false,
        ];

        if (!Schema::hasTable('competitor_stats')) {
            return response()->json([
                'success' => true,
                'data' => $payload,
            ]);
        }

        $availableRows = CompetitorContextStat::query()
            ->select(['rodeio_id', 'modalidade_id', 'divisao'])
            ->whereNotNull('rodeio_id')
            ->whereNotNull('modalidade_id')
            ->distinct()
            ->get();

        if ($availableRows->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => $payload,
            ]);
        }

        $featuredState = $this->resolveHubFeaturedRodeio();
        $featuredRodeio = $featuredState['rodeio'] ?? null;

        $rodeioIds = $availableRows->pluck('rodeio_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $modalidadeIds = $availableRows->pluck('modalidade_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $rodeios = Rodeio::query()
            ->whereIn('id', $rodeioIds->all())
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->keyBy('id');

        $modalidades = Modalidade::query()
            ->whereIn('id', $modalidadeIds->all())
            ->orderBy('nome')
            ->get()
            ->keyBy('id');

        $requestedRodeioId = (int) $request->integer('rodeio_id');
        $featuredRodeioId = (int) ($featuredRodeio?->id ?? 0);
        $selectedRodeioId = in_array($requestedRodeioId, $rodeioIds->all(), true)
            ? $requestedRodeioId
            : (in_array($featuredRodeioId, $rodeioIds->all(), true) ? $featuredRodeioId : (int) ($rodeioIds->first() ?? 0));

        $rowsForRodeio = $availableRows->filter(fn ($row) => (int) $row->rodeio_id === $selectedRodeioId)->values();
        $modalidadeIdsForRodeio = $rowsForRodeio->pluck('modalidade_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $requestedModalidadeId = (int) $request->integer('modalidade_id');
        $featuredModalidadeId = (int) ($featuredRodeio?->modalidade_atual ?? 0);
        $selectedModalidadeId = in_array($requestedModalidadeId, $modalidadeIdsForRodeio->all(), true)
            ? $requestedModalidadeId
            : (in_array($featuredModalidadeId, $modalidadeIdsForRodeio->all(), true) ? $featuredModalidadeId : (int) ($modalidadeIdsForRodeio->first() ?? 0));

        $selectedRodeio = $rodeios->get($selectedRodeioId);
        $selectedModalidade = $modalidades->get($selectedModalidadeId);
        $rowsForScope = $rowsForRodeio->filter(fn ($row) => (int) $row->modalidade_id === $selectedModalidadeId)->values();

        $divisionOptions = $rowsForScope->pluck('divisao')
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values();

        if ($divisionOptions->isEmpty() && $selectedModalidade) {
            $divisionOptions = collect($selectedModalidade->divisoes_nomes ?? [])
                ->map(fn ($value) => trim((string) $value))
                ->filter(fn ($value) => $value !== '')
                ->unique()
                ->values();
        }

        $requestedDivisao = trim((string) $request->query('divisao', ''));
        $currentDivisao = trim((string) ($selectedRodeio?->divisao_atual ?? ''));
        $selectedDivisao = in_array($requestedDivisao, $divisionOptions->all(), true)
            ? $requestedDivisao
            : (in_array($currentDivisao, $divisionOptions->all(), true) ? $currentDivisao : (string) ($divisionOptions->first() ?? ''));

        $statsQuery = CompetitorContextStat::query()
            ->with([
                'competitor' => function ($query) {
                    $query->select(['id', 'nome', 'foto', 'claimed_user_id', 'status']);
                },
            ])
            ->where('rodeio_id', $selectedRodeioId)
            ->where('modalidade_id', $selectedModalidadeId)
            ->when($selectedDivisao !== '', fn ($query) => $query->where('divisao', $selectedDivisao))
            ->whereHas('competitor', fn ($query) => $query->where('status', 'ativo'))
            ->orderByDesc('pontuacao_total')
            ->orderByDesc('count_boa')
            ->orderBy('competitor_id');

        $scopeStats = $statsQuery->get()->values();
        $leader = $scopeStats->first();
        $totalCompetitors = $scopeStats->count();
        $totalPoints = (int) $scopeStats->sum('pontuacao_total');
        $averagePoints = $totalCompetitors > 0 ? round($totalPoints / $totalCompetitors, 1) : 0;
        $averageAproveitamento = $totalCompetitors > 0
            ? round((float) $scopeStats->map(fn (CompetitorContextStat $stat) => $this->statisticsAproveitamento($stat))->avg(), 1)
            : 0;

        $lastUpdatedAt = $scopeStats
            ->pluck('last_updated_at')
            ->filter()
            ->sortDesc()
            ->first();

        $payload['filters'] = [
            'rodeios' => $rodeioIds
                ->map(fn ($id) => $rodeios->get($id))
                ->filter()
                ->map(function (Rodeio $rodeio) {
                    return [
                        'id' => (int) $rodeio->id,
                        'nome' => trim((string) ($rodeio->nome ?? $rodeio->titulo ?? 'Rei do Rodeio')),
                        'logo_url' => filled($rodeio->logo)
                            ? route('rodeios.logo', $rodeio)
                            : asset('assets/images/logo_icon/logo.png'),
                        'status_transmissao' => (string) ($rodeio->status_transmissao ?? 'programado'),
                        'divisao_atual' => trim((string) ($rodeio->divisao_atual ?? '')),
                    ];
                })
                ->values()
                ->all(),
            'modalidades' => $modalidadeIdsForRodeio
                ->map(fn ($id) => $modalidades->get($id))
                ->filter()
                ->map(function (Modalidade $modalidade) {
                    return [
                        'id' => (int) $modalidade->id,
                        'rodeio_id' => (int) $modalidade->rodeio_id,
                        'nome' => trim((string) ($modalidade->nome ?? 'Modalidade')),
                        'tem_divisoes' => (bool) ($modalidade->tem_divisoes ?? false),
                    ];
                })
                ->values()
                ->all(),
            'divisoes' => $divisionOptions->values()->all(),
            'selected' => [
                'rodeio_id' => $selectedRodeioId ?: null,
                'modalidade_id' => $selectedModalidadeId ?: null,
                'divisao' => $selectedDivisao,
            ],
        ];

        $payload['scope'] = [
            'rodeio_nome' => trim((string) ($selectedRodeio?->nome ?? $selectedRodeio?->titulo ?? 'Arena Estatísticas')),
            'modalidade_nome' => trim((string) ($selectedModalidade?->nome ?? 'Sem modalidade ativa')),
            'divisao' => $selectedDivisao,
            'logo_url' => ($selectedRodeio && filled($selectedRodeio->logo))
                ? route('rodeios.logo', $selectedRodeio)
                : asset('assets/images/logo_icon/logo.png'),
            'event_mode' => (string) ($featuredState['mode'] ?? 'empty'),
        ];

        $payload['summary'] = [
            'competitors' => $totalCompetitors,
            'total_points' => $totalPoints,
            'average_points' => $averagePoints,
            'average_aproveitamento' => $averageAproveitamento,
            'leader_name' => $isPremiumUser ? trim((string) ($leader?->competitor?->nome ?? '')) : null,
            'leader_points' => (int) ($leader?->pontuacao_total ?? 0),
            'last_update_label' => $lastUpdatedAt ? optional($lastUpdatedAt)->format('d/m H:i') : 'Sem pontuação ao vivo',
        ];

        $payload['entries'] = $isPremiumUser
            ? $scopeStats->take(80)->values()->map(function (CompetitorContextStat $stat, int $index) {
                $competitor = $stat->competitor;

                return [
                    'rank' => $index + 1,
                    'competitor_id' => (int) $stat->competitor_id,
                    'name' => trim((string) ($competitor?->nome ?? 'Competidor')),
                    'photo_url' => $competitor?->foto_url ?: asset('assets/images/logo_icon/logo.png'),
                    'points' => (int) ($stat->pontuacao_total ?? 0),
                    'last_points' => (int) ($stat->last_points ?? 0),
                    'aproveitamento' => $this->statisticsAproveitamento($stat),
                    'good_actions' => (int) ($stat->count_boa ?? 0),
                    'negative_actions' => (int) ($stat->count_negativas_total ?? 0),
                    'custom_actions' => (int) ($stat->count_custom ?? 0),
                    'custom_points' => (int) ($stat->points_custom_total ?? 0),
                    'division' => trim((string) ($stat->divisao ?? '')),
                    'phase' => trim((string) ($stat->tipo_fase ?? '')),
                    'is_finalized' => (bool) ($stat->is_finalized ?? false),
                    'last_updated_label' => optional($stat->last_updated_at)->format('d/m H:i'),
                    'top_actions' => $this->statisticsActionHighlights($stat),
                ];
            })->all()
            : [];

        $payload['has_data'] = $totalCompetitors > 0;

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    public function hubInicio(Request $request) {
        $launchSector = trim((string) $request->input('launch_sector', ''));
        $activeRodeio = null;
        $homeSections = collect();
        $homeEntries = collect();
        $homeMode = 'competitor';
        $competitors = collect();
        $oddsByCompetitor = [];
        $oddsByGroup = [];
        $oddsAutomation = [
            'settings' => [],
            'finance' => [
                'paid_volume' => 0.0,
                'house_fee' => 0.0,
                'margin_percent' => 0.0,
            ],
            'boost_global' => false,
        ];

        $context = [
            'rodeio_id' => null,
            'rodeio_nome' => null,
            'modalidade_id' => null,
            'modalidade_nome' => null,
            'divisao' => null,
        ];

        $isPremiumUser = auth()->check()
            && method_exists(auth()->user(), 'isPremium')
            && auth()->user()->isPremium();

        // Pagamentos pendentes (X1 + Bolão) para exibir no bilhete
        $pendingPayments = [
            'count' => 0,
            'x1' => [],
            'fantasy' => [],
        ];

        try {
            $activeRodeioQuery = Rodeio::query()->with(['modalidadeAtual']);
            if (Schema::hasColumn('rodeios', 'status_transmissao')) {
                $activeRodeioQuery
                    ->whereNotNull('status_transmissao')
                    ->whereIn('status_transmissao', $this->activeTransmissionStatuses());
            }
            $activeRodeio = $activeRodeioQuery
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            if (!$activeRodeio) {
                $fallbackActiveRodeioQuery = Rodeio::query()
                    ->with(['modalidadeAtual'])
                    ->where('status', 'ativo')
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id');

                $activeRodeio = $fallbackActiveRodeioQuery->first();
            }

            $modalidades = collect();

            if ($activeRodeio) {
                $modalidadesQuery = $activeRodeio->modalidades();

                if (Schema::hasColumn('modalidades', 'pausar_x1')) {
                    $modalidadesQuery->where('pausar_x1', false);
                }

                if (Schema::hasColumn('modalidades', 'status')) {
                    $modalidadesQuery->where('status', '!=', 'inativo');
                }

                $modalidadeAtualId = (int) ($activeRodeio->modalidade_atual ?? 0);
                if (Schema::hasColumn('modalidades', 'nome')) {
                    $modalidadesQuery
                        ->orderByRaw(
                            'CASE WHEN modalidades.id = ? THEN 0 ELSE 1 END',
                            [$modalidadeAtualId]
                        )
                        ->orderBy('nome');
                } else {
                    $modalidadesQuery->orderBy('id');
                }

                $modalidades = $modalidadesQuery->get();
            }

            if ($modalidades->isEmpty()) {
                $fallbackQuery = \App\Models\Modalidade::query()->with('rodeio')->whereHas('rodeio');

                if (Schema::hasColumn('modalidades', 'pausar_x1')) {
                    $fallbackQuery->where('pausar_x1', false);
                }

                if (Schema::hasColumn('modalidades', 'status')) {
                    $fallbackQuery->where('status', '!=', 'inativo');
                }

                if (Schema::hasColumn('modalidades', 'nome')) {
                    $fallbackQuery->orderBy('nome');
                } else {
                    $fallbackQuery->orderBy('id');
                }

                $modalidades = $fallbackQuery->get();

                if (!$activeRodeio && $modalidades->isNotEmpty()) {
                    $activeRodeio = $modalidades->first()->rodeio ?: Rodeio::find($modalidades->first()->rodeio_id);
                }
            }

            $divisaoAtual = (string) ($activeRodeio?->divisao_atual ?? '');

            $homeSections = $modalidades
                ->map(function ($modalidade) use ($activeRodeio, $divisaoAtual, $isPremiumUser) {
                    return $this->buildHubInicioSection($modalidade, $activeRodeio, $divisaoAtual ?: null, $isPremiumUser);
                })
                ->filter(function (array $section) {
                    return $section['cards'] instanceof \Illuminate\Support\Collection
                        ? $section['cards']->isNotEmpty()
                        : !empty($section['cards']);
                })
                ->values();

            if ($homeSections->isNotEmpty()) {
                $maxBolaoByModalidade = $this->resolveHubInicioSectionBolaoPrices(
                    $modalidades,
                    $activeRodeio,
                    $divisaoAtual ?: null
                );

                $homeSections = $homeSections
                    ->values()
                    ->map(function (array $section, int $index) use ($maxBolaoByModalidade) {
                        $modalidadeId = (int) ($section['context']['modalidade_id'] ?? 0);
                        $section['max_bolao_price'] = (float) ($maxBolaoByModalidade[$modalidadeId] ?? 0);
                        $section['_original_index'] = $index;

                        return $section;
                    })
                    ->sort(function (array $left, array $right) {
                        $priceCompare = ((float) ($right['max_bolao_price'] ?? 0)) <=> ((float) ($left['max_bolao_price'] ?? 0));
                        if ($priceCompare !== 0) {
                            return $priceCompare;
                        }

                        return ((int) ($left['_original_index'] ?? 0)) <=> ((int) ($right['_original_index'] ?? 0));
                    })
                    ->map(function (array $section) {
                        unset($section['_original_index']);
                        return $section;
                    })
                    ->values();
            }

            if ($homeSections->isNotEmpty()) {
                $primarySection = $homeSections->first();
                $homeEntries = collect($primarySection['entries'] ?? []);
                $homeMode = (string) ($primarySection['mode'] ?? 'competitor');
                $competitors = collect($primarySection['competitors'] ?? []);
                $oddsByCompetitor = $primarySection['odds_by_competitor'] ?? [];
                $oddsByGroup = $primarySection['odds_by_group'] ?? [];
                $oddsAutomation = $primarySection['odds_automation'] ?? $oddsAutomation;
                $context = $primarySection['context'] ?? $context;
            }

            if (auth()->check()) {
                $uid = auth()->id();
                $pendingWindowStart = now()->subMinutes(30);

                if (Schema::hasTable('x1_payments')) {
                    $x1Select = ['id', 'x1_room_id', 'amount', 'status', 'provider_preference_id', 'created_at'];
                    if (Schema::hasColumn('x1_payments', 'payload')) {
                        $x1Select[] = 'payload';
                    }

                    $pendingPayments['x1'] = X1Payment::where('user_id', $uid)
                        ->whereIn('status', ['pending', 'pending_payment'])
                        ->where('created_at', '>=', $pendingWindowStart)
                        ->latest()
                        ->take(10)
                        ->get($x1Select)
                        ->map(function ($payment) {
                            $payloadRaw = $payment->payload ?? null;
                            $payload = [];

                            if (is_array($payloadRaw)) {
                                $payload = $payloadRaw;
                            } elseif (is_string($payloadRaw) && $payloadRaw !== '') {
                                $decoded = json_decode($payloadRaw, true);
                                $payload = is_array($decoded) ? $decoded : [];
                            }

                            $qrCode = data_get($payload, 'qr_code')
                                ?: data_get($payload, 'point_of_interaction.transaction_data.qr_code');
                            $qrCodeBase64 = data_get($payload, 'qr_code_base64')
                                ?: data_get($payload, 'point_of_interaction.transaction_data.qr_code_base64');

                            return [
                                'id' => $payment->id,
                                'x1_room_id' => $payment->x1_room_id ?? null,
                                'amount' => $payment->amount ?? null,
                                'status' => $payment->status ?? null,
                                'provider_preference_id' => $payment->provider_preference_id ?? null,
                                'created_at' => $payment->created_at ?? null,
                                'qr_code' => $qrCode,
                                'qr_code_base64' => $qrCodeBase64,
                            ];
                        })
                        ->toArray();
                }

                if (Schema::hasTable('fantasy_payments')) {
                    $fantasySelect = ['id', 'fantasy_league_id', 'amount', 'status', 'provider_preference_id', 'created_at'];
                    if (Schema::hasColumn('fantasy_payments', 'payload')) {
                        $fantasySelect[] = 'payload';
                    }

                    $pendingPayments['fantasy'] = DB::table('fantasy_payments')
                        ->where('user_id', $uid)
                        ->where('status', 'pending')
                        ->where('created_at', '>=', $pendingWindowStart)
                        ->latest()
                        ->take(10)
                        ->get($fantasySelect)
                        ->map(function ($payment) {
                            $payload = [];
                            if (isset($payment->payload) && $payment->payload !== null && $payment->payload !== '') {
                                if (is_array($payment->payload)) {
                                    $payload = $payment->payload;
                                } else {
                                    $decoded = json_decode((string) $payment->payload, true);
                                    $payload = is_array($decoded) ? $decoded : [];
                                }
                            }

                            $qrCode = data_get($payload, 'qr_code')
                                ?: data_get($payload, 'point_of_interaction.transaction_data.qr_code');
                            $qrCodeBase64 = data_get($payload, 'qr_code_base64')
                                ?: data_get($payload, 'point_of_interaction.transaction_data.qr_code_base64');

                            return [
                                'id' => $payment->id ?? null,
                                'fantasy_league_id' => $payment->fantasy_league_id ?? null,
                                'amount' => $payment->amount ?? null,
                                'status' => $payment->status ?? null,
                                'provider_preference_id' => $payment->provider_preference_id ?? null,
                                'created_at' => $payment->created_at ?? null,
                                'qr_code' => $qrCode,
                                'qr_code_base64' => $qrCodeBase64,
                            ];
                        })
                        ->toArray();
                }

                $pendingPayments['count'] = count($pendingPayments['x1']) + count($pendingPayments['fantasy']);
            }
        } catch (\Throwable $e) {
            Log::error('Hub inicio partial failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        $featuredRodeio = $this->resolveHubFeaturedRodeio();
        $activeRodeio = $featuredRodeio['rodeio'] ?? ($activeRodeio ?? null);
        $hubLiveMode = $featuredRodeio['mode'] ?? 'empty';
        $hubFeaturedUi = $this->buildHubFeaturedRodeioUiPayload(
            $activeRodeio,
            $hubLiveMode,
            $featuredRodeio['rodeios'] ?? collect()
        );
        $hubLiveTimerIso = $hubFeaturedUi['timer_iso'] ?? null;
        $hubRodeioLogoUrl = $hubFeaturedUi['logo_url'] ?? asset('assets/images/logo_icon/logo.png');
        $hubFeaturedLogos = $hubFeaturedUi['logos'] ?? [];
        $hubFeaturedItems = $hubFeaturedUi['items'] ?? [];

        return view('frontend.partials.inicial_inicio_content', compact(
            'launchSector',
            'homeSections',
            'competitors',
            'homeEntries',
            'homeMode',
            'context',
            'isPremiumUser',
            'pendingPayments',
            'oddsByCompetitor',
            'oddsByGroup',
            'oddsAutomation',
            'activeRodeio',
            'hubLiveMode',
            'hubLiveTimerIso',
            'hubRodeioLogoUrl',
            'hubFeaturedLogos',
            'hubFeaturedItems'
        ));
    }


    public function hubFantasy() {
        return view('frontend.partials.inicial_fantasy_content');
    }

    public function hubStats(Request $request) {
        if (!$request->ajax() && !$request->boolean('hub_partial')) {
            $query = $request->query();
            unset($query['hub_partial']);
            $query['tab'] = 'estatisticas';

            return redirect()->route('home', $query);
        }

        $featuredRodeio = $this->resolveHubFeaturedRodeio();
        $statsActiveRodeio = $featuredRodeio['rodeio'] ?? null;
        $statsEventMode = $featuredRodeio['mode'] ?? 'empty';
        $statsModalidades = collect();
        $competitors = collect();

        $statsModalidadesQuery = \App\Models\Modalidade::query()->whereHas('rodeio');

        if (Schema::hasColumn('modalidades', 'status')) {
            $statsModalidadesQuery->where('status', '!=', 'inativo');
        }

        if ($statsActiveRodeio) {
            $statsModalidadesQuery->orderByRaw(
                'CASE WHEN modalidades.rodeio_id = ? THEN 0 ELSE 1 END',
                [(int) $statsActiveRodeio->id]
            );

            if (Schema::hasColumn('modalidades', 'nome')) {
                $statsModalidadesQuery
                    ->orderByRaw(
                        'CASE WHEN modalidades.id = ? THEN 0 ELSE 1 END',
                        [(int) ($statsActiveRodeio->modalidade_atual ?? 0)]
                    )
                    ->orderBy('nome');
            } else {
                $statsModalidadesQuery->orderBy('id');
            }
        } elseif (Schema::hasColumn('modalidades', 'nome')) {
            $statsModalidadesQuery->orderBy('nome');
        } else {
            $statsModalidadesQuery->orderBy('id');
        }

        $statsModalidades = $statsModalidadesQuery->get(['modalidades.id', 'modalidades.rodeio_id', 'modalidades.nome', 'modalidades.tamanho_equipe']);

        $modalidadeIds = $statsModalidades->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0)
            ->values()
            ->all();

        $competitorsQuery = \App\Models\Competitor::query()
            ->active()
            ->with([
                'modalidades' => function ($query) use ($modalidadeIds) {
                    $query->select(['modalidades.id', 'modalidades.rodeio_id', 'modalidades.nome']);

                    if (!empty($modalidadeIds)) {
                        $query->whereIn('modalidades.id', $modalidadeIds);
                    }
                },
            ])
            ->when(Schema::hasTable('competitor_stats'), function ($query) {
                $query->with('stats');
            })
            ->orderBy('nome');

        $competitors = $competitorsQuery->get();

        return view('frontend.partials.inicial_stats_content', [
            'competitors' => $competitors,
            'hidePoints' => true,
            'statsActiveRodeio' => $statsActiveRodeio,
            'statsEventMode' => $statsEventMode,
            'statsModalidades' => $statsModalidades,
        ]);
    }

    private function statisticsAccessLabel(array $subscriptionStatus): string
    {
        if ((bool) ($subscriptionStatus['is_premium'] ?? false)) {
            return (($subscriptionStatus['status'] ?? '') === 'trial')
                ? 'Trial premium ativo'
                : 'Premium ativo';
        }

        if (($subscriptionStatus['status'] ?? '') === 'guest') {
            return 'Entre para liberar';
        }

        return 'Acesso free';
    }

    private function statisticsArenaPlans(): array
    {
        $plans = Schema::hasTable('subscription_plans')
            ? SubscriptionPlan::query()->active()->ordered()->get()
            : collect();

        if ($plans->isNotEmpty()) {
            return $plans->map(fn (SubscriptionPlan $plan) => $this->statisticsPlanPayload($plan))->values()->all();
        }

        return [
            [
                'id' => 0,
                'name' => 'Premium Mensal',
                'slug' => 'mensal',
                'formatted_price' => 'R$ 49,90',
                'formatted_monthly_price' => 'R$ 49,90',
                'billing_cycle' => 'monthly',
                'period_label' => '/mês',
                'description' => 'Acesso completo às estatísticas premium.',
                'badge' => 'Recorrente',
                'badge_color' => '#22c55e',
                'is_featured' => false,
                'is_recurring' => true,
                'trial_days' => 3,
                'features' => ['Arena Estatísticas', 'Filtros por divisão', 'Leitura completa do competidor'],
            ],
            [
                'id' => 0,
                'name' => 'Premium Semestral',
                'slug' => 'semestral',
                'formatted_price' => 'R$ 249,90',
                'formatted_monthly_price' => 'R$ 41,65',
                'billing_cycle' => 'semiannual',
                'period_label' => '/semestre',
                'description' => 'Plano semestral com economia e acesso premium contínuo.',
                'badge' => 'Popular',
                'badge_color' => '#3b82f6',
                'is_featured' => true,
                'is_recurring' => false,
                'trial_days' => 0,
                'features' => ['Arena Estatísticas', 'Prioridade premium', 'Dados por rodeio e modalidade'],
            ],
            [
                'id' => 0,
                'name' => 'Premium Anual',
                'slug' => 'anual',
                'formatted_price' => 'R$ 499,90',
                'formatted_monthly_price' => 'R$ 41,66',
                'billing_cycle' => 'annual',
                'period_label' => '/ano',
                'description' => 'Melhor custo-benefício para operação anual.',
                'badge' => 'Melhor oferta',
                'badge_color' => '#f59e0b',
                'is_featured' => false,
                'is_recurring' => false,
                'trial_days' => 0,
                'features' => ['Arena Estatísticas', 'Comparativo completo', 'Leitura premium do competidor'],
            ],
        ];
    }

    private function statisticsPlanPayload(SubscriptionPlan $plan): array
    {
        return [
            'id' => (int) $plan->id,
            'name' => (string) $plan->name,
            'slug' => (string) $plan->slug,
            'formatted_price' => (string) $plan->formatted_price,
            'formatted_monthly_price' => (string) $plan->formatted_monthly_price,
            'billing_cycle' => (string) ($plan->billing_cycle ?? ''),
            'period_label' => (string) $plan->period_label,
            'description' => (string) ($plan->description ?? ''),
            'badge' => (string) ($plan->badge ?? ''),
            'badge_color' => (string) ($plan->badge_color ?? '#22c55e'),
            'is_featured' => (bool) ($plan->is_featured ?? false),
            'is_recurring' => (bool) ($plan->is_recurring ?? false),
            'trial_days' => (int) ($plan->trial_days ?? 0),
            'features' => collect($plan->features ?? [])
                ->map(fn ($feature) => trim((string) $feature))
                ->filter(fn ($feature) => $feature !== '')
                ->values()
                ->all(),
        ];
    }

    private function statisticsAproveitamento(CompetitorContextStat $stat): float
    {
        $good = (int) ($stat->count_boa ?? 0);
        $negative = (int) ($stat->count_negativas_total ?? 0);
        $total = $good + $negative;

        if ($total <= 0) {
            return 0;
        }

        return round(($good / $total) * 100, 1);
    }

    private function statisticsActionHighlights(CompetitorContextStat $stat): array
    {
        $actionCounts = is_array($stat->action_counts) ? $stat->action_counts : [];
        $actionCounts['boa'] = (int) ($stat->count_boa ?? 0);
        $actionCounts['negativas'] = (int) ($stat->count_negativas_total ?? 0);

        if ((int) ($stat->count_custom ?? 0) > 0) {
            $actionCounts['custom'] = (int) ($stat->count_custom ?? 0);
        }

        return collect($actionCounts)
            ->mapWithKeys(fn ($count, $key) => [trim((string) $key) => (int) $count])
            ->filter(fn ($count, $key) => $key !== '' && $count > 0)
            ->sortDesc()
            ->take(3)
            ->map(function ($count, $key) {
                return [
                    'label' => $this->statisticsActionLabel((string) $key),
                    'count' => (int) $count,
                ];
            })
            ->values()
            ->all();
    }

    private function statisticsActionLabel(string $key): string
    {
        $normalized = trim(Str::lower($key));

        return match ($normalized) {
            'boa' => 'Boas',
            'negativas' => 'Negativas',
            'custom' => 'Custom',
            default => Str::title(str_replace('_', ' ', $normalized)),
        };
    }

    public function featuredRodeioState()
    {
        $featuredRodeio = $this->resolveHubFeaturedRodeio();
        $activeRodeio = $featuredRodeio['rodeio'] ?? null;
        $hubLiveMode = $featuredRodeio['mode'] ?? 'empty';

        return response()->json([
            'success' => true,
            'data' => $this->buildHubFeaturedRodeioUiPayload(
                $activeRodeio,
                $hubLiveMode,
                $featuredRodeio['rodeios'] ?? collect()
            ),
        ]);
    }

    public function hubX1() {
        $pageTitle = 'Salas x1';
        $currentUserId = auth()->id();
        
        $roomsQuery = \App\Models\X1RoomInstance::with([
            'host',
            'modalidade',
            'rodeio',
            'participants.user',
            'participants.competitor',              // ✅ Competidor do participante
            'participants.competitorGroup.members', // ✅ Grupo do participante
            'competitor',
            'competitorGroup.members',
            'payments',
            'result' // ✅ Incluir resultado para salas finalizadas
        ])->latest();

        $rooms = $roomsQuery->get()
            ->filter(function ($room) use ($currentUserId) {
                // ✅ FILTRAR: Salas finalizadas aparecem por 5 dias (visíveis para todos)
                // IMPORTANTE: Verificar ANTES de closed_at porque finished também tem closed_at preenchido
                if ($room->status === 'finished' && $room->finished_at) {
                    $fiveDaysAgo = now()->subDays(5);
                    return $room->finished_at >= $fiveDaysAgo;
                }
                
                // ✅ FILTRAR: Salas com pagamento pendente só aparecem para o host
                if ($room->status === 'pending_payment') {
                    return $room->host_user_id === $currentUserId;
                }
                
                // ✅ FILTRAR: Salas in_progress aparecem para os participantes OU são salas de bots (visíveis para todos)
                if ($room->status === 'in_progress') {
                    // Salas de bot são sempre visíveis
                    if ($room->is_bot_room) {
                        return true;
                    }
                    // Verificar se o usuário é host ou participante
                    $isHost = $room->host_user_id === $currentUserId;
                    $isParticipant = $room->participants->contains('user_id', $currentUserId);
                    return $isHost || $isParticipant;
                }
                
                // ✅ FILTRAR: Salas completed/closed/cancelled não aparecem
                if (in_array($room->status, ['closed', 'completed', 'cancelled'])) {
                    return false;
                }
                
                // ✅ FILTRAR: Salas com closed_at preenchido são finalizadas (mas já foram tratadas acima)
                if ($room->closed_at !== null) {
                    return false;
                }
                
                // ✅ FILTRAR: Salas com vencedor definido são finalizadas
                if ($room->vencedor_id !== null) {
                    return false;
                }
                // Salas 'open' são visíveis para todos
                return true;
            })
            ->map(function ($room) {
            $opponent = $room->participants->firstWhere('is_host', false);
            
            // === BUSCAR DADOS DE BOT SE NECESSÁRIO ===
            $hostBotUser = null;
            $opponentBotUser = null;
            
            if ($room->is_bot_room) {
                if ($room->bot_criador_id) {
                    $hostBotUser = BotUser::find($room->bot_criador_id);
                }
                if ($room->bot_oponente_id) {
                    $opponentBotUser = BotUser::find($room->bot_oponente_id);
                }
            }
            
            // === HOST COMPETITOR DATA ===
            $hostCompetitorLabel = null;
            $hostCompetitors = [];
            
            if ($room->competitorGroup) {
                $hostCompetitorLabel = $room->competitorGroup->nome ?: $room->competitorGroup->members->pluck('nome')->implode(' + ');
                // Buscar membros do grupo com fotos
                $hostCompetitors = $room->competitorGroup->members->map(function($member) {
                    return [
                        'id' => $member->id,
                        'nome' => $member->nome,
                        'foto' => $member->foto_url ?: null,
                    ];
                })->toArray();
            } elseif ($room->competitor) {
                $hostCompetitorLabel = $room->competitor->nome;
                $hostCompetitors = [[
                    'id' => $room->competitor->id,
                    'nome' => $room->competitor->nome,
                    'foto' => $room->competitor->foto_url ?: null,
                ]];
            }
            
            // === OPPONENT COMPETITOR DATA ===
            $opponentCompetitorLabel = null;
            $opponentCompetitors = [];
            
            // Para salas de bot, buscar competidor diretamente pela coluna
            if ($room->is_bot_room && $room->competitor_escolhido_oponente) {
                $opponentComp = \App\Models\Competitor::find($room->competitor_escolhido_oponente);
                if ($opponentComp) {
                    $opponentCompetitorLabel = $opponentComp->nome;
                    $opponentCompetitors = [[
                        'id' => $opponentComp->id,
                        'nome' => $opponentComp->nome,
                        'foto' => $opponentComp->foto_url ?: null,
                    ]];
                    
                    // Verificar se o competidor é parte de um grupo
                    $groupMember = \DB::table('modalidade_competitor_group_members')
                        ->where('competitor_id', $opponentComp->id)
                        ->first();
                    
                    if ($groupMember) {
                        $group = \App\Models\ModalidadeCompetitorGroup::find($groupMember->group_id);
                        if ($group) {
                            $opponentCompetitorLabel = $group->nome ?: $group->members->pluck('nome')->implode(' + ');
                            $opponentCompetitors = $group->members->map(function($member) {
                                return [
                                    'id' => $member->id,
                                    'nome' => $member->nome,
                                    'foto' => $member->foto_url ?: null,
                                ];
                            })->toArray();
                        }
                    }
                }
            } elseif ($opponent) {
                // Carregar relações se não estiverem carregadas
                if (!$opponent->relationLoaded('competitor')) {
                    $opponent->load('competitor');
                }
                if (!$opponent->relationLoaded('competitorGroup')) {
                    $opponent->load('competitorGroup');
                }
                
                if ($opponent->competitorGroup) {
                    $opponentCompetitorLabel = $opponent->competitorGroup->nome ?: $opponent->competitorGroup->members->pluck('nome')->implode(' + ');
                    $opponentCompetitors = $opponent->competitorGroup->members->map(function($member) {
                        return [
                            'id' => $member->id,
                            'nome' => $member->nome,
                            'foto' => $member->foto_url ?: null,
                        ];
                    })->toArray();
                } elseif ($opponent->competitor) {
                    $opponentCompetitorLabel = $opponent->competitor->nome;
                    $opponentCompetitors = [[
                        'id' => $opponent->competitor->id,
                        'nome' => $opponent->competitor->nome,
                        'foto' => $opponent->competitor->foto_url ?: null,
                    ]];
                }
            }
            
            // Buscar preferenceId do pagamento do host
            $hostPayment = $room->payments()->where('role', 'host')->first();
            
            // Calcular prêmio se estiver nulo
            $prizeTotal = $room->prize_total;
            if ($prizeTotal === null && $room->valor_entrada) {
                $total = (float) $room->valor_entrada * 2;
                $feePercent = (float) ($room->fee_percent ?? 20);
                $fee = $total * ($feePercent / 100);
                $prizeTotal = round($total - $fee, 2);
            }
            
            // Dados do resultado (se finalizada)
            $winnerSlot = null;
            $winnerUsername = null;
            if ($room->status === 'finished' && $room->result) {
                $winnerSlot = $room->result->winner_slot;
                // Buscar username do vencedor (respeitando show_in_listings)
                if ($room->result->winner_user_id) {
                    $winnerUser = \App\Models\User::find($room->result->winner_user_id);
                    $winnerUsername = $winnerUser ? $winnerUser->getPublicUsername() : null;
                }
            }
            
            // Mascara usernames (bots sempre mascarados com **)
            $challengerName = 'Usuário';
            if ($hostBotUser) {
                $challengerName = $hostBotUser->getPublicUsername();
            } elseif ($room->host) {
                $challengerName = $room->host->getPublicUsername();
            }

            $opponentName = null;
            if ($opponentBotUser) {
                $opponentName = $opponentBotUser->getPublicUsername();
            } elseif ($opponent?->user) {
                $opponentName = $opponent->user->getPublicUsername();
            }

            return [
                'id' => $room->id,
                'name' => $room->name,
                'challenger' => $challengerName,
                'challenger_is_premium' => $hostBotUser ? $hostBotUser->isPremium() : ($room->host?->isPremium() ?? false),
                'opponent' => $opponentName,
                'opponent_is_premium' => $opponentBotUser ? $opponentBotUser->isPremium() : ($opponent?->user?->isPremium() ?? false),
                'amount' => $room->valor_entrada,
                'mode' => $room->modalidade?->nome ?? 'Modalidade',
                'status' => $room->status,
                'created' => $room->created_at?->diffForHumans(),
                'is_premium' => $room->is_premium_room ?? false,
                'fee_percent' => $room->fee_percent ?? 20,
                'prize_total' => $prizeTotal,
                'winner_slot' => $winnerSlot,
                'winner_username' => $winnerUsername,
                'host_competitor' => $hostCompetitorLabel,
                'host_competitors' => $hostCompetitors,
                'opponent_competitor' => $opponentCompetitorLabel,
                'opponent_competitors' => $opponentCompetitors,
                'host_username' => $challengerName,
                'host_competitor_id' => $room->competitor_id,
                'host_competitor_group_id' => $room->competitor_group_id,
                'modalidade_id' => $room->modalidade_id,
                'modalidade_name' => $room->modalidade?->nome ?? 'Modalidade',
                'modalidade_team_size' => (int) ($room->modalidade?->tamanho_equipe ?? 1),
                'rodeio_id' => $room->rodeio_id,
                'rodeio_name' => $room->rodeio?->name ?? 'Rodeio',
                'divisao' => $room->divisao,
                'host_user_id' => $room->host_user_id,
                'participant_ids' => $room->participants->pluck('user_id')->toArray(),
                'preference_id' => $hostPayment?->provider_preference_id ?? '',
                'expires_at' => $room->expires_at?->toIso8601String(),
                'is_bot_room' => $room->is_bot_room ?? false,
            ];
        });

        $stats = [
            // 'minhas' = salas ATIVAS do usuário logado (host ou participant)
            // Exclui: completed, closed, finished, cancelled, closed_at preenchido, vencedor definido
            'minhas' => \App\Models\X1RoomInstance::where(function($q) use ($currentUserId) {
                $q->where('host_user_id', $currentUserId)
                  ->orWhereHas('participants', function($pq) use ($currentUserId) {
                      $pq->where('user_id', $currentUserId);
                  });
            })
            ->whereNotIn('status', ['completed', 'closed', 'finished', 'cancelled'])
            ->whereNull('closed_at')
            ->whereNull('vencedor_id')
            ->count(),
            'total' => \App\Models\X1RoomInstance::whereIn('status', ['open', 'in_progress'])
                ->whereNull('closed_at')
                ->whereNull('vencedor_id')
                ->count(),
            'premium' => \App\Models\X1RoomInstance::where('is_premium_room', true)
                ->where('status', '!=', 'pending_payment')
                ->whereNull('closed_at')
                ->whereNull('vencedor_id')
                ->count(),
            'abertas' => \App\Models\X1RoomInstance::where('status', 'open')
                ->whereNull('closed_at')
                ->count(),
        ];

        $modalidadesRaw = \App\Models\Modalidade::where('pausar_x1', false)->orderBy('nome')->get();
        $modalidades = $modalidadesRaw->map(function ($modalidade) {
            return (object) [
                'id' => $modalidade->id,
                'label' => $modalidade->nome ?? $modalidade->titulo ?? $modalidade->name ?? ('Modalidade #' . $modalidade->id),
                'team_size' => (int) ($modalidade->tamanho_equipe ?? 1),
                'tipo' => $modalidade->tipo_participacao ?? 'individual',
                'tem_divisoes' => (bool) ($modalidade->tem_divisoes ?? false),
                'divisoes' => $modalidade->divisoes_nomes ?? [],
            ];
        });

        $rodeioQuery = \App\Models\Rodeio::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'nome')) {
            $rodeioQuery->orderBy('nome');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'titulo')) {
            $rodeioQuery->orderBy('titulo');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'name')) {
            $rodeioQuery->orderBy('name');
        } else {
            $rodeioQuery->orderBy('id');
        }
        $rodeiosRaw = $rodeioQuery->get();
        $rodeios = $rodeiosRaw->map(function ($rodeio) {
            return (object) [
                'id' => $rodeio->id,
                'label' => $rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? ('Rodeio #' . $rodeio->id),
            ];
        });

        // ✅ Verificar se algum rodeio ativo tem evento finalizado
        $eventFinalized = $rodeiosRaw->where('status_transmissao', 'finalizado')->isNotEmpty();

        return view('frontend.partials.inicial_x1_content', compact('rooms', 'stats', 'modalidades', 'rodeios', 'eventFinalized'));
    }

    public function hubPerfil() {
        return view('frontend.partials.inicial_perfil_content');
    }
}
