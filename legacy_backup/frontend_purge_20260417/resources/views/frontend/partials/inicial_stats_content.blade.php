@php
    $statsActiveRodeio = $statsActiveRodeio ?? null;
    $statsEventMode = (string) ($statsEventMode ?? '');
    $statsEventModeLabel = match ($statsEventMode) {
        'live' => 'Rodeio ao vivo',
        'scheduled' => 'Rodeio programado',
        default => 'Radar geral',
    };
    $statsEventName = trim((string) ($statsActiveRodeio?->name ?? 'Central de estatísticas'));
    $statsEventContextCopy = $statsEventMode === 'live'
        ? 'Modalidades abertas no rodeio ao vivo'
        : ($statsEventMode === 'scheduled' ? 'Modalidades abertas no próximo rodeio programado' : 'Modalidades disponíveis no radar');
    $statsModalidades = collect($statsModalidades ?? []);

    $normalizeLevel = static function ($value): string {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'legado' => 'ascendente',
            'presilha' => 'competidor',
            default => $normalized !== '' ? $normalized : 'competidor',
        };
    };

    $shortName = static function ($value): string {
        $tokens = preg_split('/\s+/', trim((string) $value)) ?: [];
        $tokens = array_values(array_filter($tokens, static fn ($token) => $token !== ''));

        return empty($tokens) ? 'Sem nome' : collect($tokens)->take(2)->implode(' ');
    };

    $levelLabel = static function (string $level): string {
        return match ($level) {
            'favorito' => 'Favorito',
            'elite' => 'Elite',
            'ascendente' => 'Ascendente',
            default => 'Competidor',
        };
    };

    $levelAccent = static function (string $level): string {
        return match ($level) {
            'favorito' => '#facc15',
            'elite' => '#f97316',
            'ascendente' => '#60a5fa',
            default => '#22c55e',
        };
    };

    $publicEventStatsByCompetitor = collect($eventStatsByCompetitor ?? [])->map(function ($events) {
        return collect($events)->map(function ($event) {
            return [
                'rodeio_id' => (int) data_get($event, 'rodeio_id', 0),
                'modalidade_id' => (int) data_get($event, 'modalidade_id', 0),
            ];
        })->values()->all();
    })->all();

    $competitorCollection = collect($competitors ?? []);
    $preparedCompetitors = $competitorCollection->map(function ($competitor) use ($normalizeLevel, $shortName, $levelLabel, $levelAccent) {
        $stats = $competitor->stats;
        $levelKey = $normalizeLevel($competitor->nivel ?? '');
        $boas = (int) ($stats->count_boa ?? 0);
        $errors = (int) ($stats->count_negativas_total ?? 0);
        $attempts = $boas + $errors;
        $destrezas =
            (int) ($stats->count_limpou_garupa ?? 0) +
            (int) ($stats->count_cola ?? 0) +
            (int) ($stats->count_cupim ?? 0) +
            (int) ($stats->count_top ?? 0) +
            (int) ($stats->count_pescou ?? 0) +
            (int) ($stats->count_limpou_cupim_longe ?? 0) +
            (int) ($stats->count_pescou_uma_aspa ?? 0) +
            (int) ($stats->count_limpou_top ?? 0) +
            (int) ($stats->count_limpou_top_mao ?? 0);

        return [
            'competitor' => $competitor,
            'stats' => $stats,
            'id' => (int) $competitor->id,
            'name' => $competitor->nome ?? 'Sem nome',
            'short_name' => $shortName($competitor->nome ?? 'Sem nome'),
            'photo_url' => $competitor->foto_url,
            'claimed' => (int) ($competitor->profile_claimed ?? 0),
            'level_key' => $levelKey,
            'level_label' => $levelLabel($levelKey),
            'accent' => $levelAccent($levelKey),
            'wins' => (int) ($stats->vitorias ?? 0),
            'draws' => (int) ($stats->empates ?? 0),
            'losses' => (int) ($stats->derrotas ?? 0),
            'aproveitamento' => (int) round((float) ($stats->aproveitamento ?? 0)),
            'boas' => $boas,
            'errors' => $errors,
            'attempts' => $attempts,
            'destrezas' => $destrezas,
            'modalidade_ids' => collect($competitor->modalidades ?? [])
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->filter(static fn ($id) => $id > 0)
                ->values()
                ->all(),
        ];
    })->sort(function ($left, $right) {
        $leftAproveitamento = (int) ($left['aproveitamento'] ?? 0);
        $rightAproveitamento = (int) ($right['aproveitamento'] ?? 0);

        if ($leftAproveitamento !== $rightAproveitamento) {
            return $rightAproveitamento <=> $leftAproveitamento;
        }

        $leftDestrezas = (int) ($left['destrezas'] ?? 0);
        $rightDestrezas = (int) ($right['destrezas'] ?? 0);

        if ($leftDestrezas !== $rightDestrezas) {
            return $rightDestrezas <=> $leftDestrezas;
        }

        $leftBoas = (int) ($left['boas'] ?? 0);
        $rightBoas = (int) ($right['boas'] ?? 0);

        if ($leftBoas !== $rightBoas) {
            return $rightBoas <=> $leftBoas;
        }

        $leftAttempts = (int) ($left['attempts'] ?? 0);
        $rightAttempts = (int) ($right['attempts'] ?? 0);

        if ($leftAttempts !== $rightAttempts) {
            return $rightAttempts <=> $leftAttempts;
        }

        return strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
    })->values();

    $isPremiumUser = auth()->check() && method_exists(auth()->user(), 'isPremium')
        ? auth()->user()->isPremium()
        : false;

    $categoryCounts = [
        'todos' => $preparedCompetitors->count(),
        'favorito' => $preparedCompetitors->where('level_key', 'favorito')->count(),
        'elite' => $preparedCompetitors->where('level_key', 'elite')->count(),
        'ascendente' => $preparedCompetitors->where('level_key', 'ascendente')->count(),
        'competidor' => $preparedCompetitors->where('level_key', 'competidor')->count(),
    ];

    $rrCategories = [
        ['label' => 'Todos', 'key' => 'todos', 'meta' => 'Radar geral'],
        ['label' => 'Favorito', 'key' => 'favorito', 'meta' => 'Mais quentes'],
        ['label' => 'Elite', 'key' => 'elite', 'meta' => 'Mais consistentes'],
        ['label' => 'Ascendente', 'key' => 'ascendente', 'meta' => 'Subindo'],
        ['label' => 'Competidor', 'key' => 'competidor', 'meta' => 'Base ativa'],
    ];

    $avgAproveitamento = (int) round($preparedCompetitors->avg('aproveitamento') ?? 0);
    $totalArmadas = (int) $preparedCompetitors->sum('attempts');
    $totalBoas = (int) $preparedCompetitors->sum('boas');
    $totalErrors = (int) $preparedCompetitors->sum('errors');
    $totalDestrezas = (int) $preparedCompetitors->sum('destrezas');
    $heroLeader = $preparedCompetitors->first();
    $topLeaders = $preparedCompetitors->take(3)->values();
    $statsHeroTitle = $statsEventMode === 'live'
        ? 'Radar ao vivo de ' . $statsEventName
        : ($statsEventMode === 'scheduled'
            ? 'Radar do próximo rodeio programado'
            : 'Radar completo de competidores');
    $statsHeroCopy = $statsEventMode === 'live'
        ? 'Leia rapidamente quem está mais quente no rodeio atual, filtre por modalidade e abra a ficha completa sem sair do hub.'
        : ($statsEventMode === 'scheduled'
            ? 'Antecipe a leitura do próximo rodeio, filtre por modalidade e acompanhe quem chega mais forte no radar.'
            : 'Use os filtros para descobrir quem lidera em aproveitamento, armadas, boas, erros e destrezas.');
    $heroSpotlightTitle = $heroLeader
        ? ($heroLeader['short_name'] . ' lidera o radar agora')
        : 'Resumo geral do radar';
    $heroSpotlightCopy = $heroLeader
        ? ($heroLeader['aproveitamento'] . '% de aproveitamento, ' . number_format($heroLeader['boas'], 0, ',', '.') . ' boas e ' . number_format($heroLeader['destrezas'], 0, ',', '.') . ' destrezas registradas.')
        : ('A central soma ' . number_format($totalArmadas, 0, ',', '.') . ' armadas, ' . number_format($totalBoas, 0, ',', '.') . ' boas e ' . number_format($totalErrors, 0, ',', '.') . ' erros registrados.');
    $statsModalidadeCounts = $statsModalidades->mapWithKeys(function ($modalidade) use ($preparedCompetitors) {
        $modalidadeId = (int) ($modalidade->id ?? 0);

        return [
            $modalidadeId => $preparedCompetitors->filter(static function ($item) use ($modalidadeId) {
                return in_array($modalidadeId, $item['modalidade_ids'] ?? [], true);
            })->count(),
        ];
    })->all();
    $statsModalidadesPrepared = $statsModalidades->map(function ($modalidade) use ($statsModalidadeCounts) {
        $modalidadeId = (int) ($modalidade->id ?? 0);

        return [
            'id' => $modalidadeId,
            'label' => trim((string) ($modalidade->nome ?? ('Modalidade #' . $modalidadeId))),
            'count' => (int) ($statsModalidadeCounts[$modalidadeId] ?? 0),
        ];
    })->filter(static fn ($modalidade) => $modalidade['count'] > 0)->values();
    $statsViewer = auth()->user();
    $hasPendingCompetitorRequest = $statsViewer && \Illuminate\Support\Facades\Schema::hasTable('competitor_registration_requests')
        ? \App\Models\CompetitorRegistrationRequest::query()
            ->where('user_id', $statsViewer->id)
            ->where('status', 'pending')
            ->exists()
        : false;
    $hasClaimedCompetitorProfile = $statsViewer && method_exists($statsViewer, 'claimedCompetitor')
        ? $statsViewer->claimedCompetitor()->exists()
        : false;
@endphp

<script>
window.rrEventStatsByCompetitor = @json($publicEventStatsByCompetitor);
window.rrEventLookup = @json($eventLookup ?? []);
window.rrModalidadeLookup = @json($modalidadeLookup ?? []);
window.rrIsPremium = Boolean(@json($isPremiumUser));
</script>

<link rel="stylesheet" href="{{ asset('assets/css/competitor-modal-refactored.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/premium-modal.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/inicial-stats-refresh.css') }}?v={{ time() }}">

<style>
  .rr-stats-shell{display:grid;gap:1.1rem;padding:.2rem 0 1rem;color:#fff7ed}
  .rr-stats-top,.rr-stats-toolbar{display:grid;gap:1rem}
  .rr-stats-hero,.rr-stats-side,.rr-stats-toolbar,.rr-stats-empty{position:relative;overflow:hidden;border-radius:28px;border:1px solid rgba(249,115,22,.14);background:radial-gradient(circle at top right,rgba(59,130,246,.15),transparent 34%),radial-gradient(circle at bottom left,rgba(249,115,22,.16),transparent 36%),linear-gradient(180deg,rgba(24,12,8,.98),rgba(10,5,3,.99));box-shadow:0 20px 38px rgba(0,0,0,.22),inset 0 1px 0 rgba(255,255,255,.04)}
  .rr-stats-hero,.rr-stats-side,.rr-stats-toolbar{padding:1.08rem}
  .rr-stats-pill{display:inline-flex;align-items:center;gap:.45rem;min-height:32px;padding:0 .86rem;border-radius:999px;border:1px solid rgba(255,173,114,.18);background:rgba(249,115,22,.12);color:#ffd2ad;font-size:.7rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}
  .rr-stats-title{margin:.82rem 0 .42rem;font-size:clamp(1.34rem,2.4vw,2.02rem);line-height:1.04;font-weight:900;letter-spacing:-.05em;color:#fff9f5}
  .rr-stats-copy{margin:0;max-width:62ch;color:rgba(255,232,214,.8);font-size:.92rem;line-height:1.65}
  .rr-stats-hero__metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.72rem;margin-top:1rem}
  .rr-stats-kpi{padding:.92rem;border-radius:20px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.035)}
  .rr-stats-kpi span{display:block;color:rgba(255,221,198,.68);font-size:.66rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
  .rr-stats-kpi strong{display:block;margin-top:.32rem;font-size:1.18rem;font-weight:900;line-height:1;color:#fffaf6}
  .rr-stats-spotlight{display:grid;gap:.3rem;margin-top:1rem;padding:1rem 1.05rem;border-radius:22px;border:1px solid rgba(255,255,255,.06);background:linear-gradient(135deg,rgba(249,115,22,.18),rgba(37,99,235,.14))}
  .rr-stats-spotlight small{display:block;color:rgba(255,222,199,.8);font-size:.68rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
  .rr-stats-spotlight strong{display:block;font-size:1.02rem;font-weight:900;color:#fff9f5}
  .rr-stats-spotlight p{margin:0;color:rgba(255,240,230,.82);font-size:.86rem;line-height:1.55}
  .rr-stats-side__title,.rr-stats-toolbar h3{margin:.72rem 0 .3rem;font-size:1.05rem;font-weight:900;line-height:1.25;color:#fff9f5}
  .rr-stats-toolbar h3{max-width:38ch}
  .rr-stats-leaders{display:grid;gap:.72rem}
  .rr-stats-leader{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:.78rem;padding:.88rem .95rem;border-radius:18px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.04)}
  .rr-stats-leader__rank{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;background:linear-gradient(135deg,rgba(249,115,22,.95),rgba(194,65,12,.92));color:#fff7ed;font-size:.84rem;font-weight:900}
  .rr-stats-leader__name{display:block;font-size:.92rem;font-weight:900;color:#fff8f3}
  .rr-stats-leader__meta{display:block;color:rgba(255,225,204,.68);font-size:.75rem;line-height:1.45}
  .rr-stats-leader__score{font-size:1.02rem;font-weight:900;color:#fff7ed}
  .rr-stats-toolbar__meta{margin-top:.35rem;color:rgba(255,219,195,.82);font-size:.82rem;font-weight:800}
  .rr-stats-toolbar__controls{display:grid;gap:.78rem}
  .rr-stats-toolbar__control{display:grid;gap:.45rem}
  .rr-stats-control-label{display:block;color:rgba(255,219,195,.72);font-size:.68rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
  .rr-stats-select-wrap,.rr-stats-search-form{position:relative;display:flex;align-items:center;min-height:58px;border-radius:18px;border:1px solid rgba(249,115,22,.18);background:linear-gradient(180deg,rgba(28,13,8,.96),rgba(12,6,4,.99));box-shadow:inset 0 1px 0 rgba(255,255,255,.03),0 14px 28px rgba(0,0,0,.18);overflow:hidden}
  .rr-stats-select{appearance:none;-webkit-appearance:none;width:100%;height:100%;border:0;outline:0;background:transparent;color:#fff7ed;padding:0 3.25rem 0 1rem;font-size:.94rem;font-weight:800;cursor:pointer}
  .rr-stats-select option{color:#111827}
  .rr-stats-select:disabled{cursor:not-allowed;opacity:.66}
  .rr-stats-select__chevron{position:absolute;right:16px;top:50%;width:16px;height:16px;transform:translateY(-50%);color:#f97316;pointer-events:none}
  .rr-stats-search-form{padding:0 56px 0 0}
  .rr-stats-search-form input{width:100%;border:0;outline:0;background:transparent;color:#fff7ed;padding:0 1rem;font-size:.95rem}
  .rr-stats-search-form input::placeholder{color:rgba(255,220,193,.72)}
  .rr-stats-search-icon{position:absolute;right:4px;top:4px;width:46px;height:calc(100% - 8px);display:inline-flex;align-items:center;justify-content:center;border-radius:14px;color:#f97316;background:rgba(255,255,255,.04);cursor:pointer}
  .rr-stats-search-icon svg{position:absolute;width:18px;height:18px;transition:opacity .2s ease,transform .2s ease}
  .rr-stats-search-form .icon-close{opacity:0;transform:scale(.72)}
  .rr-stats-search-form.has-value .icon-search,.rr-stats-search-form.is-open .icon-search{opacity:0;transform:scale(.72)}
  .rr-stats-search-form.has-value .icon-close,.rr-stats-search-form.is-open .icon-close{opacity:1;transform:scale(1)}
  .rr-stats-cta{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.1rem;border-radius:24px;border:1px solid rgba(249,115,22,.14);background:linear-gradient(135deg,rgba(249,115,22,.14),rgba(37,99,235,.12));box-shadow:0 20px 32px rgba(0,0,0,.16)}
  .rr-stats-cta__copy{display:grid;gap:.3rem}
  .rr-stats-cta__copy span{display:block;color:#ffd7b8;font-size:.68rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
  .rr-stats-cta__copy strong{color:#fff9f5;font-size:1rem;font-weight:900}
  .rr-stats-cta__copy p{margin:0;color:rgba(255,230,214,.8);font-size:.85rem;line-height:1.55}
  .rr-stats-cta__btn{border:0;min-height:48px;padding:0 1.1rem;border-radius:16px;background:linear-gradient(135deg,#10b981,#2563eb);color:#fff;font-size:.82rem;font-weight:900;letter-spacing:.04em;text-transform:uppercase;box-shadow:0 14px 24px rgba(0,0,0,.18)}
  .rr-stats-cta__btn:disabled{opacity:.6;box-shadow:none}
  .rr-stats-claim-modal{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(2,6,23,.82);backdrop-filter:blur(8px)}
  .rr-stats-claim-modal.is-open{display:flex}
  .rr-stats-claim-modal__dialog{width:min(100%,760px);max-height:min(92vh,920px);overflow:auto;border-radius:28px;border:1px solid rgba(249,115,22,.18);background:linear-gradient(180deg,rgba(16,24,40,.98),rgba(8,12,24,.99));box-shadow:0 30px 70px rgba(0,0,0,.36)}
  .rr-stats-claim-modal__hero{display:grid;gap:1rem;padding:1.2rem 1.2rem 1rem;border-bottom:1px solid rgba(255,255,255,.06);background:radial-gradient(circle at top right,rgba(37,99,235,.18),transparent 34%),radial-gradient(circle at left center,rgba(249,115,22,.18),transparent 38%)}
  .rr-stats-claim-modal__hero-top{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start}
  .rr-stats-claim-modal__badge{display:inline-flex;align-items:center;gap:.45rem;min-height:34px;padding:0 .9rem;border-radius:999px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.08);color:#fde7d4;font-size:.74rem;font-weight:900;text-transform:uppercase;letter-spacing:.08em}
  .rr-stats-claim-modal__close{width:40px;height:40px;border-radius:999px;border:0;background:rgba(255,255,255,.08);color:#fff;font-size:1.15rem}
  .rr-stats-claim-modal__hero h3{margin:0;color:#fff9f5;font-size:1.3rem;font-weight:900}
  .rr-stats-claim-modal__hero p{margin:.35rem 0 0;color:rgba(255,230,214,.82);line-height:1.65}
  .rr-stats-claim-modal__premium{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.7rem}
  .rr-stats-claim-modal__premium-item{padding:.85rem .9rem;border-radius:18px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.04);color:#fff5eb;font-size:.8rem;line-height:1.45}
  .rr-stats-claim-modal__premium-item strong{display:block;margin-bottom:.2rem}
  .rr-stats-claim-modal__body{padding:1.1rem;display:grid;gap:1rem}
  .rr-stats-claim-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}
  .rr-stats-claim-field{display:grid;gap:.42rem}
  .rr-stats-claim-field--full{grid-column:1/-1}
  .rr-stats-claim-field label{color:#f8fafc;font-size:.78rem;font-weight:800;letter-spacing:.04em}
  .rr-stats-claim-field input,.rr-stats-claim-field textarea{width:100%;min-height:52px;border-radius:16px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.8);color:#fff;padding:.88rem 1rem;outline:0}
  .rr-stats-claim-field textarea{min-height:118px;resize:vertical}
  .rr-stats-claim-field input::placeholder,.rr-stats-claim-field textarea::placeholder{color:#94a3b8}
  .rr-stats-claim-submit{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding-top:.2rem}
  .rr-stats-claim-submit small{color:#94a3b8;line-height:1.6}
  .rr-stats-claim-submit button{border:0;min-height:52px;padding:0 1.2rem;border-radius:16px;background:linear-gradient(135deg,#f97316,#2563eb);color:#fff;font-size:.84rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em}
  .rr-stats-claim-feedback{display:none;padding:.9rem 1rem;border-radius:16px;font-size:.84rem;font-weight:700}
  .rr-stats-claim-feedback.is-visible{display:block}
  .rr-stats-claim-feedback.is-success{background:rgba(16,185,129,.14);border:1px solid rgba(16,185,129,.24);color:#86efac}
  .rr-stats-claim-feedback.is-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);color:#fca5a5}
  #rrStatsSubmenu.rr-epic-submenu{position:relative;top:auto;margin-bottom:0}
  #rrStatsSubmenu .rr-epic-submenu__track{padding:4px;border-radius:22px;border:1px solid rgba(245,158,11,.28);background:linear-gradient(160deg,rgba(46,32,7,.98),rgba(20,13,2,.99));box-shadow:0 16px 28px rgba(0,0,0,.16)}
  #rrStatsSubmenu .rr-epic-submenu__btn{min-height:62px;padding:11px 8px 9px}
  #rrStatsSubmenu .rr-epic-submenu__btn .rr-epic-submenu__text{width:100%;min-width:0}
  #rrStatsSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label{
    display:block;
    width:100%;
    font-size:10px;
    line-height:1.12;
    text-transform:uppercase;
    letter-spacing:.02em;
    white-space:normal;
    overflow:visible;
    text-overflow:clip;
    max-width:none;
    text-align:center;
    overflow-wrap:anywhere;
  }
  #rrStatsSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta{font-size:8px;opacity:.58}
  #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="todos"]{--submenu-accent:#fbbf24}
  #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="favorito"]{--submenu-accent:#facc15}
  #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="elite"]{--submenu-accent:#dc2626}
  #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="ascendente"]{--submenu-accent:#1e3a8a}
  #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="competidor"]{--submenu-accent:#16a34a}
  body.is-premium #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="todos"]{--submenu-accent:#fbbf24 !important}
  body.is-premium #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="favorito"]{--submenu-accent:#facc15 !important}
  body.is-premium #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="elite"]{--submenu-accent:#dc2626 !important}
  body.is-premium #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="ascendente"]{--submenu-accent:#1e3a8a !important}
  body.is-premium #rrStatsSubmenu .rr-epic-submenu__btn[data-filter="competidor"]{--submenu-accent:#16a34a !important}
  #rrStatsSubmenu .rr-epic-submenu__btn:not(.is-active){opacity:.92}
  #rrStatsSubmenu .rr-epic-submenu__btn:not(.is-active) .rr-epic-submenu__icon-wrap{border:1px solid color-mix(in srgb,var(--submenu-accent) 38%,transparent);background:color-mix(in srgb,var(--submenu-accent) 12%,transparent)}
  #rrStatsSubmenu .rr-epic-submenu__btn:not(.is-active) .rr-epic-submenu__count,
  #rrStatsSubmenu .rr-epic-submenu__btn:not(.is-active) .rr-epic-submenu__label{color:color-mix(in srgb,var(--submenu-accent) 72%,#fff)}
  #rrStatsSubmenu .rr-epic-submenu__btn.is-active{color:#fff !important;background:var(--submenu-accent) !important;box-shadow:0 10px 18px color-mix(in srgb,var(--submenu-accent) 26%,transparent) !important}
  #rrStatsSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap{background:rgba(255,255,255,.14) !important;border:1px solid rgba(255,255,255,.22) !important;box-shadow:none !important}
  #rrStatsSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__count,
  #rrStatsSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__label,
  #rrStatsSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__meta{color:#fff !important}
  #rrStatsSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__label{text-shadow:none !important}
  #rrStatsSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__count{filter:none !important}
  #rrStatsSubmenu .rr-epic-submenu__effect{background:var(--submenu-active-color) !important}
  .rr-stats-grid{display:grid;grid-template-columns:repeat(1,minmax(0,1fr));gap:1rem}
  .rr-stats-item{min-width:0}
  .rr-stats-card{position:relative;overflow:hidden;border-radius:28px;border:1px solid rgba(255,255,255,.07);background:linear-gradient(180deg,rgba(23,10,6,.98),rgba(10,5,3,.99));box-shadow:0 18px 34px rgba(0,0,0,.22);--rr-stats-accent:#f97316}
  .rr-stats-card::before{content:"";position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,var(--rr-stats-accent),rgba(255,255,255,0))}
  .rr-stats-card__content{position:relative;z-index:1;display:flex;flex-direction:column;gap:.88rem;height:100%;padding:1rem}
  .rr-stats-card__head{display:flex;gap:.8rem;align-items:flex-start}
  .rr-stats-card__identity{display:grid;grid-template-columns:auto minmax(0,1fr);gap:.85rem;min-width:0;flex:1}
  .rr-stats-card__photo-wrap,.rr-stats-card__photo{width:80px;height:80px;margin:0}
  .rr-stats-card__photo{border-radius:22px;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,rgba(52,22,10,.95),rgba(15,6,3,.98));box-shadow:0 14px 28px rgba(0,0,0,.28)}
  .rr-stats-card__photo img{width:100%;height:100%;object-fit:cover;object-position:center top}
  .rr-stats-card__eyebrow{display:inline-flex;align-items:center;min-height:25px;padding:0 .68rem;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);color:rgba(255,224,205,.72);font-size:.64rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
  .rr-stats-card__name{margin:.46rem 0 0;font-size:1.02rem;font-weight:900;line-height:1.16;color:#fff9f5;text-transform:uppercase;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;overflow:hidden}
  .rr-stats-card__badges{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.5rem}
  .rr-stats-card__badge{display:inline-flex;align-items:center;min-height:28px;padding:.34rem .7rem;border-radius:999px;font-size:.68rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;color:#fff3e6;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
  .rr-stats-card__bar{padding:.9rem .95rem;border-radius:20px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.035)}
  .rr-stats-card__barhead{display:flex;justify-content:space-between;gap:.6rem;margin-bottom:.48rem}
  .rr-stats-card__barhead span{color:rgba(255,223,197,.66);font-size:.69rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
  .rr-stats-card__barhead strong{font-size:1rem;font-weight:900;color:#fff8f3}
  .rr-stats-card__track{height:10px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden}
  .rr-stats-card__track span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--rr-stats-accent),#fb923c)}
  .rr-stats-card__metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}
  .rr-stats-card__metric{padding:.8rem .78rem;border-radius:17px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.035)}
  .rr-stats-card__metric span{display:block;color:rgba(255,219,195,.62);font-size:.63rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
  .rr-stats-card__metric strong{display:block;margin-top:.28rem;font-size:.95rem;font-weight:900;color:#fffaf6;line-height:1.1}
  .rr-stats-card__footer{display:grid;gap:.72rem;margin-top:auto}
  .rr-stats-card__record{display:flex;flex-wrap:wrap;gap:.42rem}
  .rr-stats-card__record span{display:inline-flex;align-items:center;justify-content:center;min-height:32px;min-width:52px;padding:.28rem .56rem;border-radius:999px;color:rgba(255,236,219,.84);font-size:.7rem;font-weight:900;letter-spacing:.05em;text-transform:uppercase;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06)}
  .rr-stats-card__btn{width:100%;min-height:46px;border-radius:16px;border:0;background:linear-gradient(135deg,var(--rr-stats-accent),#ea580c);color:#fff8f2;font-size:.74rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase;box-shadow:0 14px 24px rgba(0,0,0,.18)}
  .rr-stats-item[data-nivel="favorito"] .rr-stats-card{--rr-stats-accent:#facc15}
  .rr-stats-item[data-nivel="elite"] .rr-stats-card{--rr-stats-accent:#f97316}
  .rr-stats-item[data-nivel="ascendente"] .rr-stats-card{--rr-stats-accent:#60a5fa}
  .rr-stats-item[data-nivel="competidor"] .rr-stats-card{--rr-stats-accent:#22c55e}
  .rr-stats-pagination{display:flex;align-items:center;justify-content:center;gap:.6rem;padding-top:.15rem}
  .rr-stats-pagination__btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;min-height:44px;padding:0 1rem;border:1px solid rgba(249,115,22,.18);border-radius:999px;background:linear-gradient(135deg,rgba(249,115,22,.18),rgba(37,99,235,.12));color:#fffaf6;font-size:.78rem;font-weight:800;box-shadow:0 12px 22px rgba(0,0,0,.14)}
  .rr-stats-pagination__btn:disabled{opacity:.38;box-shadow:none}
  .rr-stats-pagination__indicator{display:inline-flex;align-items:center;justify-content:center;gap:.22rem;min-width:88px;color:#fff2e6;font-size:.82rem;font-weight:900}
  .rr-stats-pagination__current,.rr-stats-pagination__total{color:#f97316}
  .rr-stats-empty{padding:1.5rem 1.15rem;text-align:center;color:rgba(255,220,193,.72)}
  .rr-stats-empty strong{display:block;color:#fff7ed;font-size:1rem;font-weight:900}
  body.light .rr-stats-hero,body.light .rr-stats-side,body.light .rr-stats-toolbar,body.light .rr-stats-empty{background:radial-gradient(circle at top right,rgba(59,130,246,.12),transparent 34%),linear-gradient(180deg,#ffffff,#fff7ed);border-color:rgba(234,88,12,.12);box-shadow:0 18px 32px rgba(148,163,184,.12)}
  body.light .rr-stats-card{background:radial-gradient(circle at top left,rgba(251,146,60,.18),transparent 42%),radial-gradient(circle at bottom right,rgba(249,115,22,.12),transparent 38%),linear-gradient(180deg,#fff7ed,#ffedd5) !important;border-color:rgba(234,88,12,.16);box-shadow:0 18px 32px rgba(234,88,12,.12)}
  body.light .rr-stats-title,body.light .rr-stats-side__title,body.light .rr-stats-toolbar h3,body.light .rr-stats-card__name,body.light .rr-stats-card__barhead strong,body.light .rr-stats-card__metric strong,body.light .rr-stats-empty strong,body.light .rr-stats-leader__name,body.light .rr-stats-leader__score{color:#111827}
  body.light .rr-stats-copy,body.light .rr-stats-leader__meta,body.light .rr-stats-toolbar__meta,body.light .rr-stats-empty,body.light .rr-stats-spotlight p{color:#475569}
  body.light .rr-stats-pill{background:rgba(234,88,12,.1);border-color:rgba(234,88,12,.16);color:#9a3412}
  body.light .rr-stats-kpi,body.light .rr-stats-leader,body.light .rr-stats-card__bar,body.light .rr-stats-card__metric,body.light .rr-stats-card__record span,body.light .rr-stats-card__eyebrow,body.light .rr-stats-card__badge{background:linear-gradient(180deg,rgba(255,251,247,.96),rgba(255,244,233,.92));border-color:rgba(234,88,12,.12)}
  body.light .rr-stats-kpi span,body.light .rr-stats-card__barhead span,body.light .rr-stats-card__metric span,body.light .rr-stats-card__eyebrow,body.light .rr-stats-card__record span{color:#64748b}
  body.light .rr-stats-kpi strong{color:#111827}
  body.light .rr-stats-spotlight{background:linear-gradient(135deg,rgba(249,115,22,.1),rgba(59,130,246,.08));border-color:rgba(234,88,12,.16)}
  body.light .rr-stats-spotlight small{color:#c2410c}
  body.light .rr-stats-spotlight strong{color:#111827}
  body.light .rr-stats-leader__rank{background:linear-gradient(135deg,#f97316,#ea580c);color:#fff7ed}
  body.light .rr-stats-control-label{color:#64748b}
  body.light .rr-stats-select-wrap,body.light .rr-stats-search-form{background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,247,237,.98));border-color:rgba(234,88,12,.16);box-shadow:0 12px 24px rgba(148,163,184,.12)}
  body.light .rr-stats-select,body.light .rr-stats-search-form input{color:#111827}
  body.light .rr-stats-search-form input::placeholder{color:#64748b}
  body.light .rr-stats-select__chevron,body.light .rr-stats-search-icon{color:#ea580c}
  body.light .rr-stats-search-icon{background:rgba(234,88,12,.08)}
  body.light .rr-stats-card__photo{background:linear-gradient(180deg,rgba(255,244,233,.98),rgba(255,235,214,.98));border-color:rgba(234,88,12,.14);box-shadow:0 12px 24px rgba(148,163,184,.14)}
  body.light .rr-stats-card__badge{color:#9a3412}
  body.light .rr-stats-card__track{background:rgba(148,163,184,.18)}
  body.light .rr-stats-pagination__indicator{color:#334155}
  body.light .rr-stats-pagination__btn{background:linear-gradient(135deg,#f97316,#ea580c);border-color:rgba(194,65,12,.18);color:#fffaf6;box-shadow:0 12px 24px rgba(234,88,12,.22)}
  body.light #rrStatsSubmenu .rr-epic-submenu__track{background:linear-gradient(180deg,rgba(255,250,235,.98),rgba(255,243,214,.96));border-color:rgba(217,119,6,.18)}
  body.light #rrStatsSubmenu .rr-epic-submenu__btn:not(.is-active) .rr-epic-submenu__count,
  body.light #rrStatsSubmenu .rr-epic-submenu__btn:not(.is-active) .rr-epic-submenu__label{color:var(--submenu-accent)}
  body.light #rrStatsSubmenu .rr-epic-submenu__btn:not(.is-active) .rr-epic-submenu__meta{color:color-mix(in srgb,var(--submenu-accent) 74%,#334155)}
  body.light #rrStatsSubmenu .rr-epic-submenu__btn.is-active{background:var(--submenu-accent) !important;box-shadow:0 10px 18px color-mix(in srgb,var(--submenu-accent) 24%,transparent) !important}
  @media (max-width: 576px){
    .rr-stats-cta{flex-direction:column;align-items:stretch}
    .rr-stats-claim-modal__premium,.rr-stats-claim-grid{grid-template-columns:1fr}
    .rr-stats-claim-submit{flex-direction:column;align-items:stretch}
    #rrStatsSubmenu .rr-epic-submenu__btn{padding:8px 1px 7px;min-height:70px}
    #rrStatsSubmenu .rr-epic-submenu__btn .rr-epic-submenu__text{gap:2px;width:100%;min-width:0}
    #rrStatsSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label{
      display:block !important;
      font-size:7px !important;
      line-height:1.08 !important;
      letter-spacing:0 !important;
      max-width:none !important;
      width:100% !important;
      white-space:normal !important;
      overflow:visible !important;
      text-overflow:clip !important;
      text-align:center !important;
      word-break:keep-all !important;
      overflow-wrap:anywhere !important;
      hyphens:none !important;
    }
  }
  @media (min-width:940px){.rr-stats-top{grid-template-columns:minmax(0,1.34fr) minmax(320px,.9fr)}.rr-stats-toolbar{grid-template-columns:minmax(280px,.64fr) minmax(0,1fr);align-items:start}.rr-stats-toolbar__controls{grid-template-columns:minmax(240px,.94fr) minmax(220px,.72fr);align-items:end}}
  @media (min-width:720px){.rr-stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media (min-width:1080px){.rr-stats-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
  @media (min-width:1380px){.rr-stats-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
  @media (max-width:720px){.rr-stats-hero__metrics{grid-template-columns:repeat(2,minmax(0,1fr))}.rr-stats-pagination__label{display:none}}
  @media (max-width:520px){.rr-stats-hero__metrics,.rr-stats-card__metrics{grid-template-columns:1fr}.rr-stats-card__identity{grid-template-columns:72px minmax(0,1fr)}.rr-stats-card__photo-wrap,.rr-stats-card__photo{width:72px;height:72px}}
</style>

<div class="rr-stats-shell">
  @unless($isPremiumUser)
  <section class="rr-stats-premium-call">
    <div class="rr-stats-premium-call__copy">
      <div class="rr-stats-premium-call__web-badges">
        <span class="rr-stats-premium-call__web-badge rr-stats-premium-call__web-badge--one"><i class="fas fa-chart-line"></i> Odds premium</span>
        <span class="rr-stats-premium-call__web-badge rr-stats-premium-call__web-badge--two"><i class="fas fa-balance-scale"></i> Comparativo</span>
        <span class="rr-stats-premium-call__web-badge rr-stats-premium-call__web-badge--three"><i class="fas fa-layer-group"></i> Leitura completa</span>
        <span class="rr-stats-premium-call__web-badge rr-stats-premium-call__web-badge--four"><i class="fas fa-bolt"></i> Entrada mais forte</span>
      </div>

      <div class="rr-stats-premium-call__actions">
        <button type="button" class="rr-stats-premium-call__btn" onclick="window.goToPremiumTab ? window.goToPremiumTab() : (window.switchHubTab && window.switchHubTab('premium'));">
          <i class="fas fa-crown"></i>
          <span>Quero destravar</span>
        </button>
      </div>
    </div>

    <div class="rr-stats-premium-call__visual" aria-hidden="true">
      <div class="rr-stats-premium-call__logo-wrap">
        <span class="rr-stats-premium-call__badge"><i class="fas fa-crown"></i> Premium</span>
        <img class="rr-stats-premium-call__logo" src="{{ asset('assets/images/logo_icon/premiumleague.png') }}?v={{ time() }}" alt="Premium League" onerror="this.src='{{ asset('assets/images/logo_icon/logo.png') }}'">
      </div>

      <article class="rr-stats-premium-call__floater rr-stats-premium-call__floater--top">
        <i class="fas fa-balance-scale"></i>
        <strong>Comparativo premium</strong>
        <span>frente a frente</span>
      </article>

      <article class="rr-stats-premium-call__floater rr-stats-premium-call__floater--bottom">
        <i class="fas fa-chart-line"></i>
        <strong>Leitura completa</strong>
        <span>Estatísticas avançadas</span>
      </article>
    </div>
  </section>
  @endunless

  <section class="rr-stats-toolbar">
    <div class="rr-stats-toolbar__controls">
      <div class="rr-stats-toolbar__control">
        <span class="rr-stats-control-label">{{ $statsEventContextCopy }}</span>
        <div class="rr-stats-select-wrap">
          <select id="rrStatsModalidadeSelect" class="rr-stats-select" @if($statsModalidadesPrepared->isEmpty()) disabled @endif>
            <option value="todos">Todas as modalidades</option>
            @foreach($statsModalidadesPrepared as $modalidade)
              <option value="{{ $modalidade['id'] }}">{{ $modalidade['label'] }}</option>
            @endforeach
          </select>
          <span class="rr-stats-select__chevron" aria-hidden="true">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 7 5 5 5-5"></path></svg>
          </span>
        </div>
      </div>

      <div class="rr-stats-toolbar__control">
        <span class="rr-stats-control-label">Buscar competidor</span>
        <div class="rr-stats-search-form is-open" id="rrStatsSearchForm">
          <input type="search" id="rrStatsSearchInput" placeholder="Buscar competidor pelo nome">
          <span class="rr-stats-search-icon" id="rrStatsSearchIcon">
            <svg class="icon-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
            <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </span>
        </div>
      </div>
    </div>

    <x-rr-submenu
      id="rrStatsSubmenu"
      :items="collect($rrCategories)->map(function ($cat) use ($categoryCounts) {
          $accentMap = [
              'todos' => '#fbbf24',
              'favorito' => '#facc15',
              'elite' => '#dc2626',
              'ascendente' => '#1e3a8a',
              'competidor' => '#16a34a',
          ];

          return [
              'label' => $cat['label'],
              'meta' => $cat['meta'],
              'filter' => $cat['key'],
              'count' => $categoryCounts[$cat['key']] ?? 0,
              'accent' => $accentMap[$cat['key']] ?? '#fbbf24',
          ];
      })->values()->all()"
      :activeIndex="0"
    />
  </section>

  <section class="rr-stats-cta">
    <div class="rr-stats-cta__copy">
      <span>Perfil oficial de competidor</span>
      <strong>Cadastre-se como competidor e assuma sua presença dentro da Rei do Rodeio</strong>
      <p>Envie seus dados, abra sua conta e deixe sua solicitação pronta para aprovação no painel administrativo.</p>
    </div>
    <button
      type="button"
      class="rr-stats-cta__btn"
      id="rrStatsClaimOpenBtn"
      @if($hasPendingCompetitorRequest || $hasClaimedCompetitorProfile) disabled @endif
    >
      @if($hasClaimedCompetitorProfile)
        Perfil já vinculado
      @elseif($hasPendingCompetitorRequest)
        Solicitação em análise
      @else
        Cadastre-se competidor!
      @endif
    </button>
  </section>

  <div class="rr-stats-claim-modal" id="rrStatsClaimModal" aria-hidden="true">
    <div class="rr-stats-claim-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rrStatsClaimTitle">
      <div class="rr-stats-claim-modal__hero">
        <div class="rr-stats-claim-modal__hero-top">
          <span class="rr-stats-claim-modal__badge"><i class="fas fa-horse"></i> Cadastro de competidor</span>
          <button type="button" class="rr-stats-claim-modal__close" id="rrStatsClaimCloseBtn" aria-label="Fechar">×</button>
        </div>
        <div>
          <h3 id="rrStatsClaimTitle">Entre como competidor e usuário ao mesmo tempo</h3>
          <p>Preencha sua conta normal, envie sua apresentação e deixe seu perfil pronto para análise. Quando o admin aprovar, seu usuário passa a operar com o perfil oficial de competidor vinculado.</p>
        </div>
        <div class="rr-stats-claim-modal__premium">
          <div class="rr-stats-claim-modal__premium-item">
            <strong>Comparativo premium</strong>
            Frente a frente e leitura completa para quem quer se posicionar forte.
          </div>
          <div class="rr-stats-claim-modal__premium-item">
            <strong>Estatísticas avançadas</strong>
            Mais profundidade de análise para acompanhar evolução e performance.
          </div>
          <div class="rr-stats-claim-modal__premium-item">
            <strong>Perfil oficial no ecossistema</strong>
            Seu nome, foto e presença conectados ao rodeio dentro da plataforma.
          </div>
        </div>
      </div>

      <form class="rr-stats-claim-modal__body" id="rrStatsClaimForm" enctype="multipart/form-data">
        <div class="rr-stats-claim-feedback" id="rrStatsClaimFeedback"></div>

        <div class="rr-stats-claim-grid">
          <div class="rr-stats-claim-field">
            <label for="rrClaimFirstname">Nome</label>
            <input id="rrClaimFirstname" name="firstname" type="text" value="{{ $statsViewer->firstname ?? '' }}" placeholder="Seu nome" required>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimLastname">Sobrenome</label>
            <input id="rrClaimLastname" name="lastname" type="text" value="{{ $statsViewer->lastname ?? '' }}" placeholder="Seu sobrenome" required>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimUsername">Username</label>
            <input id="rrClaimUsername" name="username" type="text" value="{{ $statsViewer->username ?? '' }}" placeholder="username" required>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimEmail">Email</label>
            <input id="rrClaimEmail" name="email" type="email" value="{{ $statsViewer->email ?? '' }}" placeholder="email@dominio.com" required>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimMobile">WhatsApp</label>
            <input id="rrClaimMobile" name="mobile" type="text" value="{{ $statsViewer->mobile ?? '' }}" placeholder="(00) 00000-0000" required>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimCpf">CPF</label>
            <input id="rrClaimCpf" name="cpf" type="text" value="{{ $statsViewer->cpf ?? '' }}" placeholder="00000000000" required>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimBirthdate">Data de nascimento</label>
            <input id="rrClaimBirthdate" name="birthdate" type="date" value="{{ optional($statsViewer?->birthdate)->format('Y-m-d') }}" required>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimImage">Foto de perfil</label>
            <input id="rrClaimImage" name="image" type="file" accept="image/*">
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimPassword">Senha @if($statsViewer) (opcional) @endif</label>
            <input id="rrClaimPassword" name="password" type="password" placeholder="@if($statsViewer) Preencha só para trocar @else Crie sua senha @endif" @if(!$statsViewer) required @endif>
          </div>
          <div class="rr-stats-claim-field">
            <label for="rrClaimPasswordConfirmation">Confirmar senha</label>
            <input id="rrClaimPasswordConfirmation" name="password_confirmation" type="password" placeholder="Repita a senha" @if(!$statsViewer) required @endif>
          </div>
          <div class="rr-stats-claim-field rr-stats-claim-field--full">
            <label for="rrClaimBio">Apresentação do competidor</label>
            <textarea id="rrClaimBio" name="biografia" placeholder="Conte quem você é, sua trajetória e por que seu perfil deve entrar na plataforma como competidor oficial."></textarea>
          </div>
        </div>

        <div class="rr-stats-claim-submit">
          <small>Ao enviar, sua conta fica pronta e a solicitação vai para o painel administrativo em <strong>Competidores &gt; Solicitações</strong>.</small>
          <button type="submit" id="rrStatsClaimSubmitBtn">Enviar solicitação</button>
        </div>
      </form>
    </div>
  </div>

  @if($preparedCompetitors->isNotEmpty())
    <div class="rr-stats-grid" id="rrCardsGrid">
      @foreach($preparedCompetitors as $item)
        @php
            $competitor = $item['competitor'];
            $stats = $item['stats'];
            $attemptsLabel = $item['attempts'] > 0 ? $item['boas'] . '/' . $item['attempts'] : '0/0';
        @endphp
        <div class="rr-stats-item" data-nivel="{{ $item['level_key'] }}" data-modalidades="{{ implode(',', $item['modalidade_ids']) }}">
          <article class="rr-stats-card" style="--rr-stats-accent: {{ $item['accent'] }};" data-color="{{ $item['accent'] }}" data-nivel="{{ $item['level_key'] }}">
            <div class="rr-stats-card__content">
              <div class="rr-stats-card__head">
                <div class="rr-stats-card__identity">
                  <div class="rr-stats-card__photo-wrap">
                    <div class="rr-stats-card__photo">
                      <img src="{{ $item['photo_url'] }}" alt="{{ $item['name'] }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/images/logo_icon/favicon.png') }}';">
                    </div>
                  </div>
                  <div>
                    <span class="rr-stats-card__eyebrow">#{{ $loop->iteration }} no radar</span>
                    <h3 class="rr-stats-card__name">{{ $item['name'] }}</h3>
                    <div class="rr-stats-card__badges">
                      <span class="rr-stats-card__badge">{{ $item['level_label'] }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="rr-stats-card__bar">
                <div class="rr-stats-card__barhead">
                  <span>Aproveitamento</span>
                  <strong>{{ $item['aproveitamento'] }}%</strong>
                </div>
                <div class="rr-stats-card__track">
                  <span style="width: {{ max(0, min(100, $item['aproveitamento'])) }}%"></span>
                </div>
              </div>

              <div class="rr-stats-card__metrics">
                <div class="rr-stats-card__metric"><span>Armadas</span><strong>{{ $attemptsLabel }}</strong></div>
                <div class="rr-stats-card__metric"><span>Boas</span><strong>{{ $item['boas'] }}</strong></div>
                <div class="rr-stats-card__metric"><span>Erros</span><strong>{{ $item['errors'] }}</strong></div>
                <div class="rr-stats-card__metric"><span>Destrezas</span><strong>{{ $item['destrezas'] }}</strong></div>
              </div>

              <div class="rr-stats-card__footer">
                <div class="rr-stats-card__record">
                  <span>{{ $item['wins'] }}V</span>
                  <span>{{ $item['draws'] }}E</span>
                  <span>{{ $item['losses'] }}D</span>
                </div>

                <button
                  type="button"
                  class="rr-stats-card__btn verTodasBtn"
                  onclick="event.stopPropagation(); window.abrirModalStats && window.abrirModalStats(this);"
                  data-id="{{ $item['id'] }}"
                  data-premium="{{ (int) $isPremiumUser }}"
                  data-claimed="{{ $item['claimed'] }}"
                  data-nivel="{{ $item['level_key'] }}"
                  data-nome="{{ $competitor->nome }}"
                  data-foto="{{ $item['photo_url'] }}"
                  data-vitorias="{{ $item['wins'] }}"
                  data-derrotas="{{ $item['losses'] }}"
                  data-empates="{{ $item['draws'] }}"
                  data-aproveitamento="{{ $item['aproveitamento'] }}"
                  data-armadas="{{ $attemptsLabel }}"
                  data-destrezas="{{ $item['destrezas'] }}"
                  data-erros="{{ $item['errors'] }}"
                  data-count-boa="{{ (int) ($stats->count_boa ?? 0) }}"
                  data-count-negativas-total="{{ (int) ($stats->count_negativas_total ?? 0) }}"
                  data-count-errou-pescoco="{{ (int) ($stats->count_errou_pescoco ?? 0) }}"
                  data-count-errou-pata="{{ (int) ($stats->count_errou_pata ?? 0) }}"
                  data-count-errou-top="{{ (int) ($stats->count_errou_top ?? 0) }}"
                  data-count-dobrada="{{ (int) ($stats->count_dobrada ?? 0) }}"
                  data-count-cabresteou="{{ (int) ($stats->count_cabresteou ?? 0) }}"
                  data-count-duas-voltas="{{ (int) ($stats->count_duas_voltas ?? 0) }}"
                  data-count-limpou-garupa="{{ (int) ($stats->count_limpou_garupa ?? 0) }}"
                  data-count-garupa-neg="{{ (int) ($stats->count_garupa_neg ?? 0) }}"
                  data-count-cola="{{ (int) ($stats->count_cola ?? 0) }}"
                  data-count-cola-neg="{{ (int) ($stats->count_cola_neg ?? 0) }}"
                  data-count-cupim="{{ (int) ($stats->count_cupim ?? 0) }}"
                  data-count-top="{{ (int) ($stats->count_top ?? 0) }}"
                  data-count-pescou="{{ (int) ($stats->count_pescou ?? 0) }}"
                  data-count-uma-aspa="{{ (int) ($stats->count_uma_aspa ?? 0) }}"
                  data-count-por-cima="{{ (int) ($stats->count_por_cima ?? 0) }}"
                  data-count-limpou-cupim-longe="{{ (int) ($stats->count_limpou_cupim_longe ?? 0) }}"
                  data-count-pescou-uma-aspa="{{ (int) ($stats->count_pescou_uma_aspa ?? 0) }}"
                  data-count-limpou-top="{{ (int) ($stats->count_limpou_top ?? 0) }}"
                  data-count-limpou-top-mao="{{ (int) ($stats->count_limpou_top_mao ?? 0) }}"
                  data-count-boi-tirou="{{ (int) ($stats->count_boi_tirou ?? 0) }}"
                  data-count-boi-pulou="{{ (int) ($stats->count_boi_pulou ?? 0) }}"
                  data-count-queimou-raia="{{ (int) ($stats->count_queimou_raia ?? 0) }}"
                  data-count-caiu-do-cavalo="{{ (int) ($stats->count_caiu_do_cavalo ?? 0) }}"
                  data-count-saiu-enrolado="{{ (int) ($stats->count_saiu_enrolado ?? 0) }}"
                >
                  Abrir ficha completa
                </button>
              </div>
            </div>
          </article>
        </div>
      @endforeach
    </div>

    <nav class="rr-stats-pagination rr-pagination">
      <button class="rr-stats-pagination__btn rr-stats-pagination__btn--prev" id="prevPageBtn" type="button"><i class="fas fa-chevron-left"></i><span class="rr-stats-pagination__label">Anterior</span></button>
      <div class="rr-stats-pagination__indicator" id="pageIndicator"><span class="rr-stats-pagination__current">1</span><span>/</span><span class="rr-stats-pagination__total">1</span></div>
      <button class="rr-stats-pagination__btn rr-stats-pagination__btn--next" id="nextPageBtn" type="button"><span class="rr-stats-pagination__label">Proximo</span><i class="fas fa-chevron-right"></i></button>
    </nav>
  @else
    <div class="rr-stats-empty"><strong>Nenhum competidor encontrado.</strong><span>Quando a base estiver pronta, os cards vao aparecer aqui.</span></div>
  @endif
</div>
