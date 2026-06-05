@php
    $launchSector = trim((string) ($launchSector ?? ''));
    $isBolaoLaunchMode = $launchSector === 'bolao';
    $isPremiumUser = (bool) ($isPremiumUser ?? (auth()->check() && method_exists(auth()->user(), 'isPremium') && auth()->user()->isPremium()));
    $feeRangeLabel = $isPremiumUser ? '7%-10%' : '10%-15%';
    $homeSections = collect($homeSections ?? []);
    $primarySection = $homeSections->first();
    $homeMode = (string) ($homeMode ?? ($primarySection['mode'] ?? 'competitor'));
    $sectionModes = $homeSections->pluck('mode')->filter()->unique()->values();
    $hasMixedHomeModes = $sectionModes->count() > 1;
    $isGroupMode = !$hasMixedHomeModes && $homeMode === 'group';
    $x1MinEntry = max(20, (float) config('arena.x1_min_entry', 20));
    $x1CustomMinEntry = max(100.01, $x1MinEntry);
    $x1MaxEntry = max($x1MinEntry, (float) config('arena.x1_max_entry', 10000));
    $x1TestEntryOptions = collect(config('arena.x1_test_entry_options', [20.00, 50.00, 100.00]))
        ->map(fn ($value) => round((float) $value, 2))
        ->filter(fn ($value) => $value >= $x1MinEntry)
        ->take(3)
        ->values();
    $entryLabelSingular = $hasMixedHomeModes ? 'competidor ou grupo' : ($isGroupMode ? 'grupo' : 'competidor');
    $entryLabelPlural = $hasMixedHomeModes ? 'competidores e grupos' : ($isGroupMode ? 'grupos' : 'competidores');
    $entryLabelCapitalized = $hasMixedHomeModes ? 'Entrada' : ($isGroupMode ? 'Grupo' : 'Competidor');
    $isMobileBrowser = (bool) preg_match('/Android|iPhone|iPad|iPod|Mobile/i', (string) request()->userAgent());
    $entrySearchTitle = $hasMixedHomeModes ? 'Buscar competidor ou grupo' : ($isGroupMode ? 'Buscar Grupo' : 'Buscar Competidor');
    $entrySearchPlaceholder = $hasMixedHomeModes
        ? 'Digite o nome do competidor, grupo ou modalidade...'
        : ($isGroupMode ? 'Digite o nome do grupo ou membro...' : 'Digite o nome do competidor...');
    $primaryContext = (array) ($primarySection['context'] ?? ($context ?? []));
    $hasMultipleModalidades = $homeSections->count() > 1;
    $rootEntityMode = $hasMixedHomeModes ? 'mixed' : $homeMode;
    $inicioHeroRodeio = $inicioHeroRodeio ?? ($activeRodeio ?? null);
    $inicioHeroMode = $inicioHeroMode ?? ($hubLiveMode ?? null);
    $inicioHeroLogoUrl = $inicioHeroLogoUrl ?? ($hubRodeioLogoUrl ?? null);
    $inicioHeroLogos = collect($inicioHeroLogos ?? ($hubFeaturedLogos ?? []))
        ->filter(fn ($item) => is_array($item))
        ->take(2)
        ->values();
    $inicioHeroItems = collect($inicioHeroItems ?? ($hubFeaturedItems ?? []))
        ->filter(fn ($item) => is_array($item))
        ->values();
    $inicioHeroTimerIso = $inicioHeroTimerIso ?? ($hubLiveTimerIso ?? null);
    $inicioHeroMode = in_array((string) ($inicioHeroMode ?? ''), ['live', 'scheduled'], true)
        ? (string) $inicioHeroMode
        : ($inicioHeroRodeio ? 'scheduled' : 'empty');
    $inicioHeroTitle = trim((string) (($inicioHeroRodeio->nome ?? $inicioHeroRodeio->titulo ?? $inicioHeroRodeio->name ?? null) ?: 'Rei do Rodeio'));
    $inicioHeroLogoUrl = (string) ($inicioHeroLogoUrl ?? asset('assets/images/logo_icon/logo.png'));
    if ($inicioHeroLogos->isEmpty()) {
        $inicioHeroLogos = collect([[
            'rodeio_id' => $inicioHeroRodeio?->id,
            'title' => $inicioHeroTitle,
            'logo_url' => $inicioHeroLogoUrl,
        ]]);
    }
    if ($inicioHeroItems->isEmpty()) {
        $inicioHeroItems = collect([[
            'rodeio_id' => $inicioHeroRodeio?->id,
            'title' => $inicioHeroTitle,
            'logo_url' => $inicioHeroLogoUrl,
            'timer_iso' => $inicioHeroTimerIso,
            'mode' => $inicioHeroMode,
            'badge' => $inicioHeroBadge ?? 'Programado',
            'accent' => $inicioHeroAccent ?? 'Próximo evento',
            'label' => $inicioHeroLabel ?? 'Começa em',
            'status_transmissao' => (string) ($inicioHeroRodeio->status_transmissao ?? ''),
        ]]);
    }
    $inicioHeroHasCarousel = $inicioHeroItems->count() > 1;
    $inicioHeroBadge = $inicioHeroMode === 'live' ? 'Ao vivo agora' : 'Programado';
    $inicioHeroAccent = $inicioHeroMode === 'live' ? 'Arena aberta' : 'Próximo evento';
    $inicioHeroLabel = $inicioHeroMode === 'live' ? 'Rodeio termina em' : 'Começa em';
    $inicioHeroUrgency = $inicioHeroMode === 'live'
        ? [
            'kicker' => 'Bolão valendo agora',
            'title' => 'O prêmio está correndo e quem demora entra atrás.',
            'note' => 'Monte sua equipe antes do cronômetro apertar e antes da galera ocupar as melhores posições do evento.',
            'floaters' => [
                ['title' => 'Entrada quente', 'meta' => 'quem entra agora ainda pega o melhor timing'],
                ['title' => 'Vagas sob pressão', 'meta' => 'a arena está recebendo equipes neste momento'],
                ['title' => 'Fecha no relógio', 'meta' => 'depois da janela, só sobra assistir'],
            ],
        ]
        : [
            'kicker' => 'Janela curta do bolão',
            'title' => '',
            'note' => '',
            'floaters' => [
                ['title' => 'Última janela', 'meta' => 'quem prepara cedo larga na frente'],
                ['title' => 'Prêmio em mira', 'meta' => 'só disputa quem confirmar a equipe'],
                ['title' => 'Relógio armado', 'meta' => 'o melhor timing não espera ninguém'],
            ],
        ];
    $inicioReminderUser = auth()->user();
    $inicioReminderName = $inicioReminderUser
        ? trim((string) (($inicioReminderUser->firstname ?? '') . ' ' . ($inicioReminderUser->lastname ?? '')))
        : '';
    if ($inicioReminderName === '' && $inicioReminderUser) {
        $inicioReminderName = (string) ($inicioReminderUser->username ?? '');
    }
    $inicioReminderEmail = (string) ($inicioReminderUser->email ?? '');
    $inicioReminderSubscribedRodeioIds = ($inicioReminderUser || $inicioReminderEmail !== '')
        ? app(\App\Services\RodeioEmailReminderService::class)->subscribedRodeioIdsFor($inicioReminderUser, $inicioReminderEmail)
        : [];
    $inicioRodeioOptions = $homeSections
        ->map(function ($section) {
            $sectionContext = (array) ($section['context'] ?? []);
            $rodeioId = (int) ($sectionContext['rodeio_id'] ?? 0);
            $rodeioName = trim((string) ($sectionContext['rodeio_nome'] ?? ''));

            if ($rodeioId <= 0 || $rodeioName === '') {
                return null;
            }

            return [
                'id' => $rodeioId,
                'label' => $rodeioName,
            ];
        })
        ->filter()
        ->unique('id')
        ->values();
    $inicioModalidadeOptions = $homeSections
        ->map(function ($section) {
            $sectionContext = (array) ($section['context'] ?? []);
            $modalidadeId = (int) ($sectionContext['modalidade_id'] ?? 0);
            $modalidadeName = trim((string) ($sectionContext['modalidade_nome'] ?? ''));
            $rodeioId = (int) ($sectionContext['rodeio_id'] ?? 0);

            if ($modalidadeId <= 0 || $modalidadeName === '') {
                return null;
            }

            return [
                'id' => $modalidadeId,
                'label' => $modalidadeName,
                'rodeio_id' => $rodeioId,
            ];
        })
        ->filter()
        ->unique(fn ($item) => ($item['rodeio_id'] ?? 0) . ':' . ($item['id'] ?? 0))
        ->values();
@endphp

<script>
    window.RR_PENDING_PAYMENTS = @json($pendingPayments ?? ['count' => 0, 'x1' => [], 'fantasy' => []]);
    window.RR_HUB_CONTEXT = {
        rodeio_id: {{ (int) (($primaryContext['rodeio_id'] ?? 0) ?: 0) }},
        modalidade_id: {{ (int) (($primaryContext['modalidade_id'] ?? 0) ?: 0) }},
        rodeio_nome: @json((string) ($primaryContext['rodeio_nome'] ?? '')),
        modalidade_nome: @json((string) ($primaryContext['modalidade_nome'] ?? '')),
        divisao: @json((string) ($primaryContext['divisao'] ?? '')),
        has_multiple_modalidades: {{ $hasMultipleModalidades ? 'true' : 'false' }},
    };
    window.RR_INICIO_HERO_TIMER_TARGET = @json($inicioHeroTimerIso ?? null);
    window.RR_INICIO_HERO_STATUS_URL = @json(route('home.featured-rodeio'));
    window.RR_INICIO_HERO_ITEMS = @json($inicioHeroItems->values()->all());
    window.RR_RODEIO_EMAIL_REMINDER_URL_TEMPLATE = @json(url('/rodeios/__RODEIO__/email-reminder'));
    window.RR_RODEIO_REMINDER_PREFILL = {
        name: @json($inicioReminderName),
        email: @json($inicioReminderEmail),
        hasRealEmail: {{ auth()->check() && method_exists(auth()->user(), 'hasRealEmail') && auth()->user()->hasRealEmail() ? 'true' : 'false' }},
        authenticated: {{ auth()->check() ? 'true' : 'false' }},
        subscribedRodeios: @json($inicioReminderSubscribedRodeioIds),
    };
  </script>

@if($isBolaoLaunchMode)
<style>
.rr-inicio-layout--bolao-launch {
    position: relative;
    gap: 0 !important;
  }
  .rr-inicio-layout--bolao-launch .rr-mobile-control-stack,
  .rr-inicio-layout--bolao-launch .rr-competitor-tools,
  .rr-inicio-layout--bolao-launch .rr-inicio-modalidade-group,
  .rr-inicio-layout--bolao-launch #rrInicioBolaos,
  .rr-inicio-layout--bolao-launch #rrInicioX1Rooms {
    display: none !important;
  }
  .rr-inicio-layout--bolao-launch .rr-inicio-shell {
    margin-bottom: 0 !important;
  }
  @media (min-width: 768px) {
    .rr-inicio-layout--bolao-launch {
      width: min(1680px, calc(100vw - 32px));
      margin: 0 auto;
      grid-template-columns: 1fr;
      padding-right: 166px;
    }
    .rr-inicio-layout--bolao-launch .rr-inicio-shell {
      width: 100%;
      margin-left: auto;
      margin-right: auto;
    }
  }
  @media (min-width: 1100px) {
    .rr-inicio-layout--bolao-launch {
      grid-template-columns: 1fr !important;
    }
    .rr-inicio-layout--bolao-launch > #rrInicioSection {
      grid-column: 1 !important;
      grid-row: auto !important;
    }
  }
  .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu {
    display: none;
  }
  @media (min-width: 768px) {
    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu {
      display: flex;
      position: absolute;
      top: 10px;
      right: 0;
      z-index: 26;
      flex-direction: column;
      align-items: stretch;
      justify-content: flex-start;
      gap: 12px;
      width: 154px;
      max-width: 154px;
      padding: 16px 12px;
      border-radius: 20px;
      border: 1px solid rgba(249, 115, 22, 0.2);
      background:
        radial-gradient(circle at top right, rgba(34, 197, 94, 0.08), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.1), transparent 36%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(8, 12, 24, 0.98));
      box-shadow: 0 24px 40px rgba(2, 6, 23, 0.24);
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__brand {
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__logo {
      width: 50px;
      height: 50px;
      object-fit: contain;
      filter: drop-shadow(0 10px 16px rgba(249, 115, 22, 0.18));
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__copy {
      text-align: center;
      display: grid;
      gap: 2px;
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__kicker {
      color: rgba(255, 237, 213, 0.74);
      font-size: 0.64rem;
      font-weight: 900;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      margin-top: 2px;
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__title {
      color: #fff7ed;
      font-size: 0.9rem;
      font-weight: 900;
      line-height: 1.05;
      letter-spacing: -0.02em;
      word-break: break-word;
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__nav {
      display: grid;
      gap: 8px;
      width: 100%;
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__nav .hub-header-nav__btn {
      width: 100%;
      justify-content: center;
      padding: 0.7rem 0.72rem;
    }

    body.light .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu {
      border-color: rgba(234, 88, 12, 0.16);
      background:
        radial-gradient(circle at top right, rgba(34, 197, 94, 0.08), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.1), transparent 36%),
        linear-gradient(180deg, rgba(255, 252, 247, 0.98), rgba(255, 255, 255, 0.98));
      box-shadow:
        0 18px 32px rgba(234, 88, 12, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    body.light .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__kicker {
      color: rgba(154, 52, 18, 0.72);
    }

    body.light .rr-inicio-layout--bolao-launch .rr-inicio-launch-menu__title {
      color: #431407;
    }
  }
  .rr-bolao-launch-simple {
    display: block;
    position: relative;
    isolation: isolate;
    margin-top: 18px;
    padding: 16px;
    border-radius: 28px;
    border: 1px solid rgba(148, 163, 184, .14);
    background:
      radial-gradient(circle at top right, rgba(249, 115, 22, .08), transparent 26%),
      radial-gradient(circle at bottom left, rgba(59, 130, 246, .08), transparent 34%),
      linear-gradient(180deg, rgba(10, 15, 27, .96), rgba(3, 7, 18, .99));
    box-shadow:
      0 28px 52px rgba(2, 6, 23, .34),
      inset 0 1px 0 rgba(255, 255, 255, .05);
    overflow: hidden;
  }
  @media (max-width: 768px) {
    .rr-inicio-layout--bolao-launch .rr-bolao-launch-simple {
            width: 100% !important;
            max-width: 100% !important;
      min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
    }
  }
  .rr-bolao-launch-simple::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      linear-gradient(120deg, transparent 0%, rgba(255,255,255,.03) 24%, transparent 42%),
      radial-gradient(circle at 82% 14%, rgba(34, 197, 94, .1), transparent 18%);
    pointer-events: none;
  }
  body.light .rr-bolao-launch-simple {
    border-color: rgba(15, 23, 42, .08);
    background:
      radial-gradient(circle at top right, rgba(249, 115, 22, .06), transparent 26%),
      radial-gradient(circle at bottom left, rgba(59, 130, 246, .06), transparent 34%),
      linear-gradient(180deg, rgba(255, 250, 245, .98), rgba(255, 255, 255, .99));
    box-shadow:
      0 20px 38px rgba(15, 23, 42, .06),
      inset 0 1px 0 rgba(255, 255, 255, .92);
  }
  .rr-bolao-launch-simple__actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
  }
  .rr-bolao-launch-simple__btn {
    --rr-bolao-accent: #38bdf8;
    --rr-bolao-accent-rgb: 56, 189, 248;
    --rr-bolao-accent-soft: rgba(56, 189, 248, .18);
    --rr-bolao-pointer-x: 50%;
    --rr-bolao-pointer-y: 18%;
    --rr-bolao-tilt-x: 0deg;
    --rr-bolao-tilt-y: 0deg;
    --rr-bolao-lift: 0px;
    position: relative;
    isolation: isolate;
    min-height: 176px;
    padding: 16px 16px 15px;
    border: 1px solid rgba(var(--rr-bolao-accent-rgb), .22);
    border-radius: 28px;
    overflow: hidden;
    color: #f8fafc;
    text-align: left;
    display: grid;
    align-content: space-between;
    justify-items: start;
    gap: 12px;
    background:
      radial-gradient(circle at var(--rr-bolao-pointer-x) var(--rr-bolao-pointer-y), rgba(var(--rr-bolao-accent-rgb), .18), transparent 30%),
      linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(7, 11, 21, .98));
    box-shadow:
      0 26px 40px rgba(0,0,0,.28),
      inset 0 1px 0 rgba(255,255,255,.06),
      inset 0 -18px 34px rgba(2, 6, 23, .28);
    transform:
      perspective(1200px)
      rotateX(var(--rr-bolao-tilt-x))
      rotateY(var(--rr-bolao-tilt-y))
      translateY(var(--rr-bolao-lift));
    transform-style: preserve-3d;
    will-change: transform, box-shadow, filter;
    transition: transform .22s ease, box-shadow .22s ease, filter .22s ease, border-color .22s ease, opacity .22s ease;
  }
  .rr-bolao-launch-simple__btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      linear-gradient(145deg, rgba(255,255,255,.08), transparent 36%, transparent 62%, rgba(var(--rr-bolao-accent-rgb), .06) 100%),
      radial-gradient(circle at top left, rgba(255,255,255,.1), transparent 30%);
    z-index: 0;
    pointer-events: none;
  }
  .rr-bolao-launch-simple__btn::after {
    content: "";
    position: absolute;
    inset: 0;
    padding: 2px;
    border-radius: inherit;
    background:
      linear-gradient(
        120deg,
        rgba(var(--rr-bolao-accent-rgb), .34) 0%,
        rgba(var(--rr-bolao-accent-rgb), .96) 18%,
        rgba(255,255,255,.52) 28%,
        rgba(var(--rr-bolao-accent-rgb), .24) 42%,
        rgba(var(--rr-bolao-accent-rgb), .12) 100%
      );
    background-size: 220% 220%;
    background-position: 0% 50%;
    animation: rrBolaoLaunchBorderFlow 4.8s ease-in-out infinite, rrBolaoLaunchBorderPulse 3.2s ease-in-out infinite;
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    z-index: 0;
    pointer-events: none;
    opacity: .96;
    filter: drop-shadow(0 0 12px rgba(var(--rr-bolao-accent-rgb), .24));
  }
  .rr-bolao-launch-simple__btn > * {
    position: relative;
    z-index: 1;
  }
  .rr-bolao-launch-simple__bg-logo {
    position: absolute;
    inset: 50% auto auto 50%;
    z-index: 0;
    width: min(72%, 248px);
    height: min(72%, 148px);
    object-fit: cover;
    object-position: center;
    transform: translate3d(-50%, -47%, 0) scale(1.18);
    opacity: .1;
    filter: grayscale(.04) brightness(1.08) drop-shadow(0 0 18px rgba(var(--rr-bolao-accent-rgb), .09));
    pointer-events: none;
    user-select: none;
    mix-blend-mode: screen;
  }
  .rr-bolao-launch-simple__btn[data-disabled="1"] .rr-bolao-launch-simple__bg-logo {
    opacity: .06;
  }
  .rr-bolao-launch-simple__btn:hover {
    --rr-bolao-lift: -8px;
    filter: brightness(1.05) saturate(1.04);
    box-shadow:
      0 36px 54px rgba(0,0,0,.34),
      0 0 26px rgba(var(--rr-bolao-accent-rgb), .14),
      inset 0 1px 0 rgba(255,255,255,.08),
      inset 0 -20px 36px rgba(2, 6, 23, .34);
  }
  .rr-bolao-launch-simple__btn[data-disabled="1"] {
    opacity: .52;
    filter: grayscale(.08) saturate(.82);
    box-shadow:
      0 18px 30px rgba(0,0,0,.2),
      inset 0 1px 0 rgba(255,255,255,.04),
      inset 0 -14px 26px rgba(2, 6, 23, .26);
  }
  .rr-bolao-launch-simple__btn--20 {
    --rr-bolao-accent: #38bdf8;
    --rr-bolao-accent-rgb: 56, 189, 248;
    --rr-bolao-accent-soft: rgba(56, 189, 248, .18);
  }
  .rr-bolao-launch-simple__btn--50 {
    --rr-bolao-accent: #22c55e;
    --rr-bolao-accent-rgb: 34, 197, 94;
    --rr-bolao-accent-soft: rgba(34, 197, 94, .18);
  }
  .rr-bolao-launch-simple__btn--100 {
    --rr-bolao-accent: #f59e0b;
    --rr-bolao-accent-rgb: 245, 158, 11;
    --rr-bolao-accent-soft: rgba(245, 158, 11, .18);
  }
  .rr-bolao-launch-simple__btn--custom {
    --rr-bolao-accent: #8b5cf6;
    --rr-bolao-accent-rgb: 139, 92, 246;
    --rr-bolao-accent-soft: rgba(139, 92, 246, .18);
  }
  .rr-bolao-launch-simple__kicker {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 31px;
    padding: 0 12px;
    border-radius: 999px;
    text-align: center;
    background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.04));
    border: 1px solid rgba(var(--rr-bolao-accent-rgb), .28);
    color: rgba(255,255,255,.92);
    font-size: .61rem;
    font-weight: 900;
    letter-spacing: .16em;
    text-transform: uppercase;
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,.1),
      0 0 0 1px rgba(255,255,255,.02);
  }
  .rr-bolao-launch-simple__topline {
    width: 100%;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
  }
  .rr-bolao-launch-simple__meta-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    align-self: flex-start;
    min-height: 28px;
    padding: 0 10px;
    border-radius: 999px;
    text-align: center;
    background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
    border: 1px solid rgba(var(--rr-bolao-accent-rgb), .22);
    color: rgba(226, 232, 240, .86);
    font-size: .56rem;
    font-weight: 900;
    letter-spacing: .14em;
    text-transform: uppercase;
    white-space: nowrap;
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,.08),
      0 0 0 1px rgba(255,255,255,.02);
  }
  .rr-bolao-launch-simple__prize-stack {
    width: 100%;
    display: grid;
    gap: 8px;
    justify-items: center;
  }
  .rr-bolao-launch-simple__price-label {
    display: grid;
    gap: 2px;
    width: 100%;
    padding: 0 6px;
    text-align: center;
    overflow: hidden;
  }
  .rr-bolao-launch-simple__price-label-main,
  .rr-bolao-launch-simple__price-label-sub {
    display: block;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .rr-bolao-launch-simple__price-label-main {
    font-size: .64rem;
    font-weight: 900;
    letter-spacing: .08em;
    line-height: 1.05;
    text-transform: uppercase;
    color: rgba(255, 255, 255, .88);
  }
  .rr-bolao-launch-simple__price-label-sub {
    font-size: .5rem;
    font-weight: 800;
    letter-spacing: .11em;
    line-height: 1.05;
    text-transform: uppercase;
    color: rgba(var(--rr-bolao-accent-rgb), .92);
  }
  .rr-bolao-launch-simple__price {
    position: relative;
    display: grid;
    gap: 0;
    min-width: min(100%, 214px);
    max-width: min(100%, 258px);
    padding: 15px 14px;
    border-radius: 22px;
    background:
      radial-gradient(circle at 14% 18%, rgba(var(--rr-bolao-accent-rgb), .22), transparent 42%),
      linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.03)),
      rgba(5, 8, 16, .46);
    border: 1px solid rgba(255,255,255,.12);
    box-shadow:
      0 16px 28px rgba(2, 6, 23, .22),
      inset 0 1px 0 rgba(255,255,255,.08),
      inset 0 -10px 18px rgba(2, 6, 23, .18);
    overflow: hidden;
    backdrop-filter: blur(14px);
  }
  .rr-bolao-launch-simple__price::before {
    content: "";
    position: absolute;
    inset: -1px;
    padding: 1px;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(var(--rr-bolao-accent-rgb), .72), rgba(255,255,255,.08), rgba(var(--rr-bolao-accent-rgb), .14));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: .75;
    pointer-events: none;
  }
  .rr-bolao-launch-simple__price::after {
    content: "";
    position: absolute;
    inset: 0 auto 0 -34%;
    width: 44%;
    background: linear-gradient(100deg, transparent 0%, rgba(255,255,255,.2) 48%, transparent 100%);
    transform: skewX(-20deg);
    opacity: .46;
    animation: rrBolaoLaunchPrizeSheen 5.2s ease-in-out infinite;
    pointer-events: none;
  }
  .rr-bolao-launch-simple__price-value {
    display: block;
    width: 100%;
    max-width: none;
    text-align: center;
    font-size: clamp(1.48rem, 2vw, 2.24rem);
    font-weight: 950;
    font-variant-numeric: tabular-nums;
    letter-spacing: -.05em;
    line-height: .92;
    color: #ffffff;
    text-shadow:
      0 12px 22px rgba(15, 23, 42, .28),
      0 0 22px rgba(var(--rr-bolao-accent-rgb), .22);
  }
  .rr-bolao-launch-simple__price-value.has-odometer {
    display: inline-flex;
    align-items: flex-end;
    gap: 0.04em;
    max-width: none;
    white-space: nowrap;
    letter-spacing: 0;
  }
  .rr-bolao-launch-simple__price-symbol {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 0.92em;
    line-height: 0.92;
    flex: 0 0 auto;
  }
  .rr-bolao-launch-simple__price-symbol--currency {
    font-size: 0.56em;
    font-weight: 900;
    letter-spacing: 0.04em;
    opacity: 0.92;
    align-self: flex-start;
    padding-top: 0.16em;
  }
  .rr-bolao-launch-simple__price-symbol--separator {
    font-size: 0.78em;
    opacity: 0.84;
    align-self: center;
  }
  .rr-bolao-launch-simple__odometer-digit {
    position: relative;
    display: inline-flex;
    height: 0.92em;
    overflow: hidden;
    line-height: 0.92;
    flex: 0 0 auto;
  }
  .rr-bolao-launch-simple__odometer-track {
    --digit: 0;
    display: grid;
    grid-auto-rows: 0.92em;
    transform: translateY(calc(var(--digit) * -0.92em));
    transition: transform 0.92s cubic-bezier(.2, .9, .2, 1.02);
    will-change: transform;
  }
  .rr-bolao-launch-simple__odometer-track span {
    height: 0.92em;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .rr-bolao-launch-simple__price.is-unlimited .rr-bolao-launch-simple__price-label {
    color: rgba(224, 231, 255, .9);
  }
  .rr-bolao-launch-simple__price.is-unlimited .rr-bolao-launch-simple__price-value {
    color: #fef3c7;
    text-shadow:
      0 12px 22px rgba(15, 23, 42, .26),
      0 0 24px rgba(253, 224, 71, .26);
  }
  .rr-bolao-launch-simple__label {
    display: none;
  }
  .rr-bolao-launch-simple__cta-actions {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }
  .rr-bolao-launch-simple__cta {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    position: relative;
    overflow: hidden;
    min-height: 46px;
    padding: 0 14px;
    border-radius: 17px;
    border: 1px solid rgba(255,255,255,.12);
    background:
      linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.02)),
      rgba(5, 8, 14, .4);
    color: #f8fafc;
    font-size: .79rem;
    font-weight: 900;
    letter-spacing: .04em;
    box-shadow:
      0 14px 24px rgba(2, 6, 23, .22),
      inset 0 1px 0 rgba(255,255,255,.08),
      inset 0 -6px 12px rgba(2, 6, 23, .18);
    transition: transform .18s ease, filter .18s ease, box-shadow .18s ease, opacity .18s ease, border-color .18s ease;
    backdrop-filter: blur(10px);
  }
  .rr-bolao-launch-simple__cta::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.22) 32%, transparent 56%);
    transform: translateX(-130%);
    animation: rrBolaoLaunchCtaShine 4.8s linear infinite;
    pointer-events: none;
    opacity: .54;
  }
  .rr-bolao-launch-simple__cta:hover:not(:disabled) {
    transform: translateY(-2px);
    filter: brightness(1.05) saturate(1.05);
  }
  .rr-bolao-launch-simple__cta:disabled {
    opacity: .42;
    cursor: not-allowed;
  }
  .rr-bolao-launch-simple__cta--team {
    border-color: rgba(245, 208, 74, .48);
    background:
      radial-gradient(circle at top center, rgba(250, 204, 21, .28), transparent 58%),
      linear-gradient(180deg, rgba(251, 191, 36, .36), rgba(180, 83, 9, .34)),
      rgba(5, 8, 14, .46);
    color: #fffbea;
    text-shadow:
      0 1px 0 rgba(120, 53, 15, .42),
      0 0 14px rgba(250, 204, 21, .14);
    box-shadow:
      0 18px 28px rgba(180, 83, 9, .24),
      0 0 20px rgba(250, 204, 21, .14),
      inset 0 1px 0 rgba(255,255,255,.16),
      inset 0 -10px 16px rgba(120, 53, 15, .22);
  }
  .rr-bolao-launch-simple__cta--ranking {
    border-color: rgba(74, 222, 128, .42);
    background:
      radial-gradient(circle at top center, rgba(74, 222, 128, .22), transparent 58%),
      linear-gradient(180deg, rgba(34, 197, 94, .24), rgba(20, 83, 45, .22)),
      rgba(5, 8, 14, .46);
    color: #f0fdf4;
    text-shadow: 0 1px 0 rgba(20, 83, 45, .36);
    box-shadow:
      0 18px 28px rgba(22, 163, 74, .18),
      0 0 22px rgba(74, 222, 128, .14),
      inset 0 1px 0 rgba(255,255,255,.1);
  }
  .rr-bolao-launch-simple__cta--share {
    min-width: 44px;
    padding: 0;
    border-color: rgba(125, 211, 252, .3);
    background:
      radial-gradient(circle at top center, rgba(56, 189, 248, .2), transparent 58%),
      linear-gradient(180deg, rgba(37, 99, 235, .24), rgba(30, 41, 59, .26)),
      rgba(5, 8, 14, .46);
    color: #eff6ff;
    box-shadow:
      0 16px 24px rgba(37, 99, 235, .16),
      0 0 18px rgba(56, 189, 248, .1),
      inset 0 1px 0 rgba(255,255,255,.08);
  }
  .rr-bolao-launch-simple__cta--share span {
    display: none;
  }
  .rr-bolao-launch-simple__cta--share i {
    font-size: .92rem;
    opacity: 1;
  }
  .rr-bolao-launch-simple__cta i {
    font-size: .78rem;
    opacity: .92;
  }
  body.light .rr-bolao-launch-simple__btn {
    border-color: rgba(var(--rr-bolao-accent-rgb), .26);
    color: #0f172a;
    background:
      radial-gradient(circle at var(--rr-bolao-pointer-x) var(--rr-bolao-pointer-y), rgba(var(--rr-bolao-accent-rgb), .12), transparent 30%),
      linear-gradient(180deg, rgba(255,255,255,.95), rgba(248, 250, 252, .98));
    box-shadow:
      0 22px 34px rgba(15, 23, 42, .08),
      inset 0 1px 0 rgba(255,255,255,.95),
      inset 0 -16px 24px rgba(15, 23, 42, .04);
  }
  body.light .rr-bolao-launch-simple__kicker,
  body.light .rr-bolao-launch-simple__price,
  body.light .rr-bolao-launch-simple__cta {
    border-color: rgba(15, 23, 42, .1);
  }
  body.light .rr-bolao-launch-simple__kicker {
    color: #1e293b;
    background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(248,250,252,.88));
  }
  body.light .rr-bolao-launch-simple__meta-badge {
    color: #334155;
    background: linear-gradient(180deg, rgba(255,255,255,.92), rgba(248,250,252,.88));
    border-color: rgba(var(--rr-bolao-accent-rgb), .18);
  }
  body.light .rr-bolao-launch-simple__price {
    background:
      radial-gradient(circle at 14% 18%, rgba(var(--rr-bolao-accent-rgb), .14), transparent 42%),
      linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,250,252,.92));
    box-shadow:
      0 16px 28px rgba(15, 23, 42, .08),
      inset 0 1px 0 rgba(255,255,255,.96),
      inset 0 -10px 18px rgba(15, 23, 42, .04);
  }
  body.light .rr-bolao-launch-simple__price-label {
    color: #475569;
  }
  body.light .rr-bolao-launch-simple__price-value {
    color: #0f172a;
    text-shadow:
      0 12px 22px rgba(255,255,255,.32),
      0 0 18px rgba(var(--rr-bolao-accent-rgb), .12);
  }
  body.light .rr-bolao-launch-simple__cta {
    color: #f8fafc;
    background:
      linear-gradient(180deg, rgba(30, 41, 59, .96), rgba(15, 23, 42, .94)),
      rgba(15, 23, 42, .92);
    box-shadow:
      0 16px 28px rgba(15, 23, 42, .16),
      inset 0 1px 0 rgba(255,255,255,.12),
      inset 0 -8px 14px rgba(2, 6, 23, .18);
  }
  body.light .rr-bolao-launch-simple__cta--team {
    color: #fffdf4;
    border-color: rgba(217, 119, 6, .82);
    background:
      radial-gradient(circle at top center, rgba(250, 204, 21, .34), transparent 58%),
      linear-gradient(180deg, rgba(245, 158, 11, .98), rgba(180, 83, 9, 1));
    box-shadow:
      0 18px 30px rgba(180, 83, 9, .28),
      0 0 22px rgba(250, 204, 21, .24),
      inset 0 1px 0 rgba(255,255,255,.3),
      inset 0 -10px 16px rgba(120, 53, 15, .26);
  }
  body.light .rr-bolao-launch-simple__cta--ranking {
    color: #f0fdf4;
    border-color: rgba(34, 197, 94, .54);
    background:
      radial-gradient(circle at top center, rgba(74, 222, 128, .28), transparent 58%),
      linear-gradient(180deg, rgba(34, 197, 94, .96), rgba(21, 128, 61, .98));
    box-shadow:
      0 18px 30px rgba(22, 163, 74, .2),
      0 0 22px rgba(74, 222, 128, .2),
      inset 0 1px 0 rgba(255,255,255,.22),
      inset 0 -10px 16px rgba(20, 83, 45, .24);
  }
  body.light .rr-bolao-launch-simple__cta--share {
    color: #eff6ff;
    border-color: rgba(59, 130, 246, .48);
    background:
      radial-gradient(circle at top center, rgba(96, 165, 250, .24), transparent 58%),
      linear-gradient(180deg, rgba(59, 130, 246, .95), rgba(29, 78, 216, .98));
    box-shadow:
      0 16px 26px rgba(37, 99, 235, .18),
      0 0 18px rgba(96, 165, 250, .18),
      inset 0 1px 0 rgba(255,255,255,.18),
      inset 0 -8px 14px rgba(30, 64, 175, .22);
  }
  @keyframes rrBolaoLaunchBorderFlow {
    0%, 100% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
  }
  @keyframes rrBolaoLaunchBorderPulse {
    0%, 100% {
      opacity: .88;
      filter: drop-shadow(0 0 6px rgba(var(--rr-bolao-accent-rgb), .12));
    }
    50% {
      opacity: .96;
      filter: drop-shadow(0 0 16px rgba(var(--rr-bolao-accent-rgb), .24));
    }
  }
  @keyframes rrBolaoLaunchPrizeSheen {
    0%, 12% {
      transform: translateX(-160%) skewX(-20deg);
      opacity: 0;
    }
    24% {
      opacity: .46;
    }
    42% {
      transform: translateX(290%) skewX(-20deg);
      opacity: .1;
    }
    100% {
      transform: translateX(290%) skewX(-20deg);
      opacity: 0;
    }
  }
  @keyframes rrBolaoLaunchCtaShine {
    0%, 20% {
      transform: translateX(-135%);
      opacity: 0;
    }
    28% {
      opacity: .5;
    }
    44% {
      transform: translateX(150%);
      opacity: .08;
    }
    100% {
      transform: translateX(150%);
      opacity: 0;
    }
  }
  @media (max-width: 640px) {
    .rr-bolao-launch-simple {
      margin-top: 14px;
      padding: 14px;
      border-radius: 24px;
    }
    .rr-bolao-launch-simple__actions {
      grid-template-columns: 1fr;
            width: min(100%, 420px);
            margin-inline: auto;
            justify-items: center;
            align-items: center;
    }
    .rr-bolao-launch-simple__btn--custom {
      order: 1;
    }
    .rr-bolao-launch-simple__btn--100 {
      order: 2;
    }
    .rr-bolao-launch-simple__btn--50 {
      order: 3;
    }
    .rr-bolao-launch-simple__btn--20 {
      order: 4;
    }
    .rr-bolao-launch-simple__btn {
            width: min(100%, 360px);
            max-width: 360px;
            justify-self: center;
            min-height: 184px;
            padding: 18px 16px 17px;
      border-radius: 24px;
      transform: none !important;
            text-align: center;
            justify-items: center;
            align-content: center;
    }
    .rr-bolao-launch-simple__topline {
      gap: 8px;
            align-items: center;
            justify-content: center;
    }
    .rr-bolao-launch-simple__meta-badge {
            align-self: center;
      min-height: 26px;
      padding: 0 9px;
      font-size: .52rem;
      letter-spacing: .12em;
    }
        .rr-bolao-launch-simple__prize-stack {
            justify-items: center;
            align-content: center;
        }
        .rr-bolao-launch-simple__price-label {
            text-align: center;
        }
    .rr-bolao-launch-simple__cta-actions {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 8px;
            width: 100%;
            max-width: 100%;
            margin-inline: auto;
    }
    .rr-bolao-launch-simple__cta {
            min-height: 44px;
      font-size: .74rem;
      padding: 0 10px;
            width: 100%;
    }
    .rr-bolao-launch-simple__price {
      min-width: 100%;
      max-width: 100%;
            justify-self: center;
    }
    .rr-bolao-launch-simple__price-value {
      max-width: 100%;
    }
    .rr-bolao-launch-simple__price-value.has-odometer {
      flex-wrap: wrap;
      row-gap: 0.08em;
    }
    .rr-bolao-launch-simple__btn::after {
      opacity: .72;
    }
  }
  .rr-inicio-event-carousel {
    position: relative;
    --rr-inicio-carousel-card-width: 240px;
    --rr-inicio-carousel-card-height: 230px;
    width: min(100%, 420px);
    height: 290px;
    perspective: 1400px;
    margin: 0 auto;
    touch-action: pan-y pinch-zoom;
  }
  .rr-inicio-event-carousel__scene {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    transition: transform .7s cubic-bezier(.22, 1, .36, 1);
    touch-action: pan-y pinch-zoom;
    pointer-events: none;
  }
  .rr-inicio-event-carousel__card {
    position: absolute;
    top: 26px;
    left: 50%;
    width: var(--rr-inicio-carousel-card-width);
    height: var(--rr-inicio-carousel-card-height);
    margin-left: calc(var(--rr-inicio-carousel-card-width) / -2);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.14);
    background:
      linear-gradient(180deg, rgba(15, 23, 42, .94), rgba(7, 11, 21, .98));
    box-shadow:
      0 24px 34px rgba(0, 0, 0, .34),
      inset 0 1px 0 rgba(255,255,255,.08);
    cursor: pointer;
    transform-style: preserve-3d;
    transition: transform .45s ease, opacity .35s ease, filter .35s ease, box-shadow .35s ease;
    backface-visibility: hidden;
    pointer-events: auto;
  }
  .rr-inicio-event-carousel__card::before {
    content: "";
    position: absolute;
    inset: -16px;
    border-radius: 30px;
    background: radial-gradient(circle at center, rgba(255,255,255,.24) 0%, rgba(255,255,255,.06) 26%, transparent 72%);
    filter: blur(18px);
    opacity: .48;
    z-index: -1;
    animation: rrInicioEventCarouselPulse 3.2s ease-in-out infinite;
  }
  .rr-inicio-event-carousel__card::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255,255,255,.12), transparent 34%, transparent 62%, rgba(15,23,42,.22));
    pointer-events: none;
  }
  .rr-inicio-event-carousel__media {
    position: absolute;
    inset: 16px 16px 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,.08);
    background:
      radial-gradient(circle at top center, rgba(255,255,255,.06), transparent 58%),
      linear-gradient(180deg, rgba(10, 16, 29, .88), rgba(8, 12, 22, .96));
    box-shadow:
      inset 0 1px 0 rgba(255,255,255,.05),
      0 18px 28px rgba(2, 6, 23, .24);
  }
  .rr-inicio-event-carousel__image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 0;
    filter: drop-shadow(0 14px 22px rgba(15, 23, 42, .34));
  }
  .rr-inicio-event-carousel__caption {
    position: absolute;
    inset: auto 12px 12px;
    padding: 10px 12px;
    border-radius: 16px;
    background: rgba(15, 23, 42, .74);
    border: 1px solid rgba(255,255,255,.08);
    backdrop-filter: blur(12px);
    color: #f8fafc;
    font-size: .74rem;
    font-weight: 900;
    line-height: 1.15;
    letter-spacing: .04em;
    text-transform: uppercase;
    text-align: center;
  }
  .rr-inicio-event-carousel__dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 12px;
  }
  .rr-inicio-event-carousel__dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.28);
    transition: transform .22s ease, background .22s ease;
  }
  .rr-inicio-event-carousel__dot.is-active {
    background: #f59e0be6;
    transform: scale(1.6);
  }
  .rr-inicio-event-lightbox {
    position: fixed;
    inset: 0;
    z-index: 2147483646;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(2, 6, 23, .84);
    backdrop-filter: blur(10px);
  }
  .rr-inicio-event-lightbox[hidden] {
    display: none !important;
  }
  .rr-inicio-event-lightbox__img {
    max-width: min(92vw, 780px);
    max-height: 82vh;
    border-radius: 22px;
    box-shadow: 0 26px 48px rgba(0,0,0,.42);
    background: rgba(15, 23, 42, .92);
  }
  .rr-inicio-event-lightbox__close {
    position: absolute;
    top: 18px;
    right: 22px;
    width: 44px;
    height: 44px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(15, 23, 42, .84);
    color: #fff;
    font-size: 1.3rem;
  }
  body.light .rr-inicio-event-carousel__card {
    border-color: rgba(245, 158, 11, .18);
    background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(248, 250, 252, .96));
    box-shadow: 0 24px 34px rgba(15, 23, 42, .08), inset 0 1px 0 rgba(255,255,255,.92);
  }
  body.light .rr-inicio-event-carousel__caption {
    background: rgba(255,255,255,.84);
    border-color: rgba(15, 23, 42, .08);
    color: #0f172a;
  }
  body.light .rr-inicio-event-carousel__nav {
    background: rgba(255,255,255,.9);
    color: #0f172a;
    border-color: rgba(15, 23, 42, .08);
    box-shadow: 0 12px 24px rgba(15, 23, 42, .08);
  }
  @keyframes rrInicioEventCarouselPulse {
    0%, 100% { transform: scale(1); opacity: .44; }
    50% { transform: scale(1.08); opacity: .86; }
  }
  @media (max-width: 767px) {
    .rr-inicio-event-carousel {
      --rr-inicio-carousel-card-width: 186px;
      --rr-inicio-carousel-card-height: 190px;
      width: min(100%, 320px);
      height: 246px;
    }
    .rr-inicio-event-carousel__card {
      top: 18px;
      border-radius: 20px;
    }
    .rr-inicio-event-carousel__media {
      inset: 14px 14px 48px;
      padding: 14px;
      border-radius: 16px;
    }
    .rr-inicio-event-carousel__image {
      padding: 0;
    }
    .rr-inicio-event-carousel__caption {
      inset: auto 10px 10px;
      padding: 8px 10px;
      font-size: .64rem;
    }
}

.rr-inicio-layout--bolao-launch #rrInicioSubmenu .rr-inicio-submenu__field[for="rrInicioModalidadeFilter"],
.rr-inicio-layout--bolao-launch #rrInicioSubmenu .rr-inicio-submenu__field[for="rrInicioRodeioFilter"] {
    display: none !important;
}

.rr-inicio-layout--bolao-launch #rrInicioModalidadeFilter,
.rr-inicio-layout--bolao-launch #rrInicioRodeioFilter {
    position: absolute !important;
    width: 0 !important;
    height: 0 !important;
    opacity: 0 !important;
    pointer-events: none !important;
    visibility: hidden !important;
    display: none !important;
}

.rr-inicio-layout--bolao-launch #rrInicioSubmenu [for="rrInicioModalidadeFilter"],
.rr-inicio-layout--bolao-launch #rrInicioSubmenu [for="rrInicioRodeioFilter"],
.rr-inicio-layout--bolao-launch #rrInicioSubmenu #rrInicioModalidadeFilter,
.rr-inicio-layout--bolao-launch #rrInicioSubmenu #rrInicioRodeioFilter {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

.rr-inicio-layout--bolao-launch .rr-inicio-event-call__mobile-selector-row select,
.rr-inicio-layout--bolao-launch .rr-inicio-event-call__mobile-selector select {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
</style>
@endif

<style>
:root {
    --rr-card-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
    --rr-card-hover-shadow: 0 12px 48px rgba(249, 115, 22, 0.3);
    --rr-primary-glow: rgba(249, 115, 22, 0.4);
}

@media (min-width: 768px) {
    .rr-inicio-layout--bolao-launch .rr-inicio-event-call__mobile-selector-row {
        margin-top: 12px;
        z-index: 32;
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-event-call__mobile-selector {
        width: min(460px, 58vw);
        min-width: 290px;
        overflow: visible;
    }

    .rr-inicio-layout--bolao-launch .rr-inicio-event-call__mobile-selector-menu {
        max-height: 220px;
        box-shadow: 0 26px 36px rgba(2, 6, 23, 0.42);
    }
}

.rr-inicio-shell { 
    position: relative;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    margin-bottom: 0 !important;
}
.rr-inicio-shell .card-body {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    padding-bottom: 6px;
}
.rr-inicio-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 14px;
    width: 100%;
    min-width: 0;
}
.rr-inicio-layout > * {
    min-width: 0;
    max-width: 100%;
}
.rr-competitor-mobile-row {
    display: block;
}
.rr-competitor-desktop-stack {
    display: none;
}
.rr-competitor-row {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.rr-competitor-row__label {
    display: none;
}
.rr-inicio-section {
    margin-top: 4px;
}

.rr-inicio-submenu {
    position: sticky;
    top: calc(var(--hub-navbar-offset, var(--hub-navbar-height, 96px)) + 12px);
    z-index: 20;
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin: 8px 0 12px;
    padding: 14px;
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(15, 23, 42, 0.92) 100%),
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.18), transparent 52%);
    box-shadow: 0 18px 44px rgba(2, 6, 23, 0.22);
    backdrop-filter: blur(18px);
}

.rr-inicio-submenu__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.rr-inicio-submenu__title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    color: #f8fafc;
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.rr-inicio-submenu__title i {
    color: #f59e0be6;
    font-size: 0.9rem;
}

.rr-inicio-submenu__filters {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) repeat(2, minmax(0, 0.9fr));
    gap: 12px;
    align-items: end;
}

.rr-inicio-submenu__search {
    min-width: 0;
}

.rr-inicio-submenu__search .rr-mobile-search-shell {
    width: 100%;
    margin: 0;
}

.rr-inicio-submenu__field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.rr-inicio-submenu__label {
    color: rgba(148, 163, 184, 0.9);
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.rr-inicio-submenu__select {
    width: 100%;
    min-height: 48px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(15, 23, 42, 0.72);
    color: #f8fafc;
    padding: 0 16px;
    font-size: .97rem;
    font-weight: 700;
    outline: none;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-inicio-submenu__select:focus {
    border-color: rgba(59, 130, 246, 0.46);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
}

.rr-inicio-filter-empty {
    margin: 10px 0 4px;
    padding: 18px 16px;
    border-radius: 18px;
    border: 1px dashed rgba(148, 163, 184, 0.28);
    background: rgba(15, 23, 42, 0.35);
    color: #cbd5e1;
    font-size: .94rem;
    font-weight: 700;
    text-align: center;
}

.rr-inicio-x1-closed {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 220px;
    padding: 22px 18px;
    border-radius: 22px;
    border: 1px dashed rgba(245, 158, 11, 0.28);
    background:
        radial-gradient(circle at top center, rgba(245, 158, 11, 0.12), transparent 46%),
        rgba(15, 23, 42, 0.42);
    text-align: center;
}

.rr-inicio-x1-closed__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid rgba(248, 113, 113, 0.32);
    background: rgba(127, 29, 29, 0.28);
    color: #fecaca;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.rr-inicio-x1-closed__title {
    color: #fff7ed;
    font-size: 1.08rem;
    font-weight: 900;
    line-height: 1.1;
}

.rr-inicio-x1-closed__text {
    max-width: 320px;
    color: #cbd5e1;
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.45;
}

.rr-inicio-modalidade-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px 10px 22px;
    border-top: 1px solid rgba(148, 163, 184, 0.12);
    position: relative;
    overflow: visible;
}

.rr-inicio-modalidade-group:first-of-type {
    border-top: 0;
    padding-top: 8px;
}

.rr-inicio-modalidade-group__head {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    padding: 0 2px;
}

.rr-inicio-section-badge-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 0 2px 2px;
}

.rr-inicio-modalidade-group__kicker {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid rgba(249, 115, 22, 0.24);
    background: rgba(249, 115, 22, 0.14);
    color: #fdba74;
    font-size: 0.68rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rr-inicio-subcatalog {
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    overflow: visible;
}

.rr-inicio-modalidade-group .rr-inicio-grid-wrap--competidores,
.rr-inicio-modalidade-group .rr-inicio-grid-wrap--competidores .rr-inicio-grid {
    overflow: visible !important;
}

.rr-inicio-subcatalog__badge {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    border: 1px solid rgba(59, 130, 246, 0.22);
    background: rgba(30, 64, 175, 0.18);
    color: #bfdbfe;
    font-size: 0.7rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

@media (max-width: 767px) {
    .rr-inicio-modalidade-group__head,
    .rr-inicio-section-badge-row {
        justify-content: center;
    }
}

.rr-inicio-section-stack {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

body.light .rr-inicio-submenu {
    background:
        linear-gradient(180deg, rgba(255, 250, 245, 0.98) 0%, rgba(255, 247, 237, 0.96) 100%),
        radial-gradient(circle at top left, rgba(251, 146, 60, 0.18), transparent 55%);
    border-color: rgba(251, 146, 60, 0.16);
    box-shadow: 0 18px 42px rgba(217, 119, 6, 0.12);
}

body.light .rr-inicio-submenu__label {
    color: #9a3412;
}

body.light .rr-inicio-submenu__select {
    background: rgba(255, 255, 255, 0.92);
    border-color: rgba(251, 146, 60, 0.18);
    color: #1f2937;
}

body.light .rr-inicio-filter-empty {
    background: rgba(255, 255, 255, 0.82);
    border-color: rgba(251, 146, 60, 0.22);
    color: #64748b;
}

body.light .rr-inicio-x1-closed {
    border-color: rgba(251, 146, 60, 0.24);
    background:
        radial-gradient(circle at top center, rgba(245, 158, 11, 0.1), transparent 46%),
        rgba(255, 255, 255, 0.78);
}

body.light .rr-inicio-x1-closed__badge {
    border-color: rgba(239, 68, 68, 0.18);
    background: rgba(254, 226, 226, 0.92);
    color: #b91c1c;
}

body.light .rr-inicio-x1-closed__title {
    color: #7c2d12;
}

body.light .rr-inicio-x1-closed__text {
    color: #64748b;
}

body.light .rr-inicio-modalidade-group {
    border-top-color: rgba(234, 88, 12, 0.08);
}

body.light .rr-inicio-modalidade-group__kicker {
    border-color: rgba(234, 88, 12, 0.16);
    background: rgba(255, 237, 213, 0.9);
    color: #c2410c;
}

body.light .rr-inicio-subcatalog__badge {
    border-color: rgba(37, 99, 235, 0.16);
    background: rgba(219, 234, 254, 0.92);
    color: #1d4ed8;
}

body.light .rr-inicio-submenu__title {
    color: #7c2d12;
}

@media (max-width: 767px) {
    .rr-inicio-submenu {
        top: calc(var(--hub-navbar-offset, var(--hub-navbar-height, 76px)) + 6px);
        gap: 10px;
        padding: 12px 10px;
        border-radius: 18px;
    }

    .rr-inicio-submenu__filters {
        grid-template-columns: minmax(0, 1fr);
    }

    .rr-inicio-submenu__head {
        justify-content: center;
    }

    .rr-inicio-submenu__title {
        font-size: 0.9rem;
        text-align: center;
    }

    .rr-inicio-submenu__select {
        min-height: 44px;
        font-size: .92rem;
        padding: 0 14px;
    }

}

/* ---- Labels de seção (mobile) — espelha desktop rr-side-panel__label ---- */
.rr-inicio-section-label {
    font-size: 0.95rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 6px 12px 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.rr-inicio-section-label i {
    color: #f59e0be6;
    font-size: 0.9rem;
}

.rr-inicio-section-label.rr-inicio-section-label--hero {
    padding: 8px 12px 4px;
    gap: 8px;
}

.rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__text {
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1.05;
}

.rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__title {
    font-size: 0.95rem;
    font-weight: 900;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #e2e8f0;
    text-shadow: 0 2px 12px rgba(249, 115, 22, 0.28);
}

.rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__title .is-highlight {
    color: #f59e0be6;
    text-shadow: 0 0 14px rgba(251, 146, 60, 0.55);
}

body.light .rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__title {
    color: #000;
    text-shadow: none;
}

.rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__subtitle {
    margin-top: 2px;
    font-size: 0.67rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.92);
}

.rr-mobile-quick-nav {
    display: block;
    position: relative;
    margin: 8px 10px 0;
    z-index: 15;
}

.rr-mobile-quick-nav__actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.rr-mobile-quick-nav__item {
    position: relative;
    display: block;
    min-width: 0;
    min-height: 46px;
    border-radius: 16px;
    overflow: hidden;
}

.rr-mobile-quick-nav__surface {
    position: absolute;
    inset: 0;
    width: 100%;
    min-height: 100%;
    padding: 0;
    border: 0;
    border-radius: inherit;
    background: transparent;
    box-shadow: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
}

.rr-mobile-quick-nav__surface > * {
    opacity: 0 !important;
    visibility: hidden !important;
}

.rr-mobile-quick-nav__chrome {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 46px;
    padding: 0 12px;
    pointer-events: none;
}

.rr-mobile-quick-nav__chrome-icon {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.34);
}

.rr-mobile-quick-nav__chrome-icon svg {
    width: 12px;
    height: 12px;
}

.rr-mobile-quick-nav__chrome-text {
    font-size: 0.8rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    line-height: 1;
    text-transform: uppercase;
    white-space: nowrap;
}

.rr-mobile-quick-nav__item--x1 {
    background: linear-gradient(180deg, #f59e0be6 0%, #f59e0be6 56%, #c2410c 100%);
    box-shadow: 0 10px 22px rgba(249, 115, 22, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.42), inset 0 -3px 0 rgba(124, 45, 18, 0.34);
}

.rr-mobile-quick-nav__item--x1 .rr-mobile-quick-nav__chrome-text {
    color: #fff7ed;
}

.rr-mobile-quick-nav__item--x1 .rr-mobile-quick-nav__chrome-icon {
    background: rgba(255, 255, 255, 0.18);
    color: #ffedd5;
}

.rr-mobile-quick-nav__item--bolao {
    background: linear-gradient(180deg, #4ade80 0%, #22c55e 58%, #15803d 100%);
    box-shadow: 0 10px 22px rgba(34, 197, 94, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.32), inset 0 -3px 0 rgba(20, 83, 45, 0.36);
}

.rr-mobile-quick-nav__item--bolao .rr-mobile-quick-nav__chrome-text {
    color: #ecfdf5;
}

.rr-mobile-quick-nav__item--bolao .rr-mobile-quick-nav__chrome-icon {
    background: rgba(255, 255, 255, 0.18);
    color: #dcfce7;
}

@media (min-width: 992px) {
    .rr-mobile-quick-nav {
        display: none !important;
    }
}

.rr-mobile-quick-empty {
    display: none;
}

.rr-mobile-control-stack {
    min-width: 0;
}

.rr-competitor-tools {
    margin: 4px 10px 0;
    padding: 8px 10px;
    border-radius: 10px;
    border: 1px solid rgba(249, 115, 22, 0.24);
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.5), rgba(30, 41, 59, 0.38));
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.rr-inicio-live-embed {
    display: none;
}

.rr-competitor-search-btn {
    position: absolute;
    inset: 0;
    width: 100%;
    min-height: 100%;
    padding: 0;
    border-radius: inherit;
    background: transparent !important;
    border-color: transparent !important;
    color: transparent !important;
    -webkit-text-fill-color: transparent !important;
    box-shadow: none !important;
    overflow: hidden;
    font-size: 0 !important;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    transition: transform 0.14s ease, filter 0.2s ease;
}

.rr-competitor-search-btn > * {
    opacity: 0 !important;
    visibility: hidden !important;
}

.rr-mobile-search-shell {
    position: relative;
    width: 100%;
    min-height: 44px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(249, 115, 22, 0.28);
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.88), rgba(30, 41, 59, 0.84));
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.rr-mobile-search-shell__chrome {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 16px;
    pointer-events: none;
    z-index: 1;
}

.rr-mobile-search-shell:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
}

.rr-mobile-search-shell__icon {
    width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #f59e0be6;
    flex: 0 0 auto;
}

.rr-mobile-search-shell__icon svg {
    width: 18px;
    height: 18px;
}

.rr-mobile-search-shell__label {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #fff7ed;
    font-size: 0.78rem;
    font-weight: 900;
    letter-spacing: 0.02em;
    line-height: 1;
}

.rr-competitor-levels {
    display: flex;
    align-items: center;
    gap: 6px;
}

.rr-competitor-level-legend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.rr-competitor-level-label {
    font-size: 0.48rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: rgba(226, 232, 240, 0.82);
    line-height: 1;
}

.rr-competitor-level-chip {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.55) inset, 0 2px 8px rgba(2, 6, 23, 0.36);
}

.rr-competitor-level-chip--favorito { background: #facc15; }
.rr-competitor-level-chip--elite { background: #f59e0be6; }
.rr-competitor-level-chip--ascendente { background: #3b82f6; }
.rr-competitor-level-chip--competidor { background: #22c55e; }

.rr-competitor-level-chip[title] { cursor: help; }

body.light .rr-competitor-tools {
    border-color: rgba(37, 99, 235, 0.14);
    background: linear-gradient(135deg, rgba(255, 249, 242, 0.98), rgba(255, 237, 221, 0.96));
    box-shadow: 0 10px 20px rgba(124, 45, 18, 0.08);
}

body.light .rr-mobile-search-shell {
    border-color: rgba(30, 64, 175, 0.14);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(255, 244, 230, 0.96));
    box-shadow: 0 10px 20px rgba(124, 45, 18, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.74);
}

body.light .rr-mobile-search-shell__icon {
    color: #ea580c;
}

body.light .rr-mobile-search-shell__label {
    color: #5b2f14;
}

body.light .rr-competitor-level-label {
    color: #6b4a35;
    text-shadow: none;
}

body.light .rr-competitor-level-chip {
    border-color: rgba(91, 47, 20, 0.18);
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.72) inset, 0 3px 8px rgba(124, 45, 18, 0.12);
}

.rr-competitor-search-modal {
    position: fixed;
    inset: 0;
    z-index: 2147483647;
    background: rgba(2, 6, 23, 0.68);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px;
    isolation: isolate;
}

.rr-competitor-search-modal[hidden] {
    display: none !important;
}

.rr-competitor-search-modal__card {
    width: min(460px, 100%);
    max-height: min(76vh, 640px);
    position: relative;
    z-index: 1;
    border-radius: 14px;
    border: 1px solid rgba(249, 115, 22, 0.32);
    background: linear-gradient(150deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.96));
    box-shadow: 0 24px 48px rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.rr-competitor-search-modal__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.rr-competitor-search-modal__title {
    color: #f59e0be6;
    font-size: 0.86rem;
    font-weight: 900;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.rr-competitor-search-modal__close {
    border: 0;
    background: transparent;
    color: #fca5a5;
    font-size: 1.2rem;
    line-height: 1;
    cursor: pointer;
}

.rr-competitor-search-modal__body {
    padding: 10px 12px 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.rr-competitor-search-input {
    width: 100%;
    border-radius: 10px;
    border: 1px solid rgba(249, 115, 22, 0.35);
    background: rgba(15, 23, 42, 0.82);
    color: #fff;
    padding: 10px 12px;
    font-size: 0.88rem;
}

.rr-competitor-search-input::placeholder {
    color: rgba(148, 163, 184, 0.8);
}

.rr-competitor-search-results {
    max-height: min(52vh, 380px);
    overflow-y: auto;
    display: grid;
    gap: 8px;
}

.rr-competitor-search-item {
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-left: 4px solid var(--rr-level-color, #22c55e);
    background: rgba(15, 23, 42, 0.55);
    color: #e2e8f0;
    border-radius: 8px;
    padding: 8px 10px;
    text-align: left;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.rr-competitor-search-item:hover {
    border-color: rgba(249, 115, 22, 0.4);
}

.rr-competitor-search-item__name {
    font-size: 0.84rem;
    font-weight: 700;
}

.rr-competitor-search-item__level {
    font-size: 0.66rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--rr-level-color, #22c55e);
}

.rr-competitor-search-empty {
    color: rgba(148, 163, 184, 0.92);
    text-align: center;
    font-size: 0.8rem;
    padding: 8px;
}

body.light .rr-competitor-search-modal {
    background: rgba(120, 62, 24, 0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

body.light .rr-competitor-search-modal__card {
    border-color: rgba(234, 88, 12, 0.2);
    background: linear-gradient(160deg, rgba(255, 252, 247, 0.98), rgba(255, 241, 223, 0.96));
    box-shadow: 0 24px 48px rgba(120, 62, 24, 0.18);
}

body.light .rr-competitor-search-modal__head {
    border-bottom-color: rgba(234, 88, 12, 0.12);
    background: linear-gradient(180deg, rgba(255, 248, 240, 0.92), rgba(255, 239, 220, 0.76));
}

body.light .rr-competitor-search-modal__title {
    color: #9a3412;
}

body.light .rr-competitor-search-modal__close {
    color: #c2410c;
}

body.light .rr-competitor-search-input {
    border-color: rgba(234, 88, 12, 0.18);
    background: rgba(255, 255, 255, 0.88);
    color: #1f2937;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

body.light .rr-competitor-search-input::placeholder {
    color: #64748b;
}

body.light .rr-competitor-search-item {
    border-color: rgba(148, 163, 184, 0.18);
    background: rgba(255, 255, 255, 0.82);
    color: #1f2937;
    box-shadow: 0 8px 18px rgba(148, 163, 184, 0.08);
}

body.light .rr-competitor-search-item:hover {
    border-color: rgba(234, 88, 12, 0.28);
    background: rgba(255, 247, 237, 0.94);
}

body.light .rr-competitor-search-item__name {
    color: #1f2937;
}

body.light .rr-competitor-search-empty {
    color: #64748b;
}

.rr-neuro-wrapper.rr-card-search-focus .rr-card-inner {
    animation: rr-card-search-focus 0.95s ease;
}

@keyframes rr-card-search-focus {
    0% { transform: translateY(0) scale(1); box-shadow: 0 0 0 0 rgba(249, 115, 22, 0); }
    40% { transform: translateY(-5px) scale(1.02); box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.72), 0 0 20px rgba(249, 115, 22, 0.56); }
    100% { transform: translateY(0) scale(1); box-shadow: 0 0 0 0 rgba(249, 115, 22, 0); }
}

@media (max-width: 767px) {
    #rrInicioSection,
    #rrInicioSection .card-body {
        overflow-x: clip !important;
    }

    .rr-mobile-control-stack {
        display: grid;
        gap: 10px;
        width: calc(100% - 20px);
        max-width: calc(100vw - 20px);
        min-width: 0;
        margin: 8px 10px 14px;
        position: relative;
        z-index: 15;
        overflow-x: clip;
        overflow-y: visible;
        contain: layout inline-size;
        isolation: isolate;
    }

    .rr-mobile-control-stack > * {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .rr-mobile-control-stack .rr-mobile-quick-nav {
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: visible !important;
    }

    .rr-mobile-control-stack .rr-competitor-tools {
        margin: 0 !important;
        width: 100%;
        max-width: 100%;
        overflow-x: clip;
        overflow-y: visible;
    }

    .rr-mobile-control-stack .rr-mobile-search-shell,
    .rr-mobile-control-stack .rr-competitor-search-btn {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
    }

    .rr-mobile-quick-nav,
    .rr-mobile-quick-nav__actions,
    .rr-mobile-quick-nav__item,
    .rr-mobile-quick-nav__surface,
    .rr-mobile-quick-nav__panel,
    .rr-competitor-tools,
    .rr-competitor-search-btn,
    .rr-mobile-search-shell {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .rr-mobile-quick-nav {
        display: block !important;
        position: relative;
        margin: 8px 10px 0;
        z-index: 15;
    }

    .rr-mobile-quick-nav__actions {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .rr-mobile-quick-nav__item {
        position: relative;
        display: block !important;
        min-width: 0;
        min-height: 46px;
        border-radius: 16px;
        overflow: hidden;
    }

    .rr-mobile-quick-nav__surface {
        position: absolute;
        inset: 0;
        width: 100%;
        min-height: 100%;
        padding: 0;
        border: 0;
        border-radius: inherit;
        background: transparent !important;
        box-shadow: none !important;
        cursor: pointer;
        transition: transform 0.16s ease, box-shadow 0.2s ease, filter 0.2s ease;
        appearance: none;
        -webkit-appearance: none;
        display: block;
    }

    .rr-mobile-quick-nav__surface > * {
        opacity: 0 !important;
        visibility: hidden !important;
    }

    .rr-mobile-quick-nav__chrome {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 12px;
        pointer-events: none;
    }

    .rr-mobile-quick-nav__chrome-icon {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.34);
    }

    .rr-mobile-quick-nav__chrome-icon svg {
        width: 12px;
        height: 12px;
    }

    .rr-mobile-quick-nav__chrome-text {
        font-size: 0.8rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        line-height: 1;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .rr-mobile-quick-nav__item--x1 {
        background: linear-gradient(180deg, #f59e0be6 0%, #f59e0be6 56%, #c2410c 100%) !important;
        box-shadow: 0 10px 22px rgba(249, 115, 22, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.42), inset 0 -3px 0 rgba(124, 45, 18, 0.34) !important;
    }

    .rr-mobile-quick-nav__item--x1 .rr-mobile-quick-nav__chrome-text {
        color: #fff7ed;
    }

    .rr-mobile-quick-nav__item--x1 .rr-mobile-quick-nav__chrome-icon {
        background: rgba(255, 255, 255, 0.18);
        color: #ffedd5;
    }

    .rr-mobile-quick-nav__item--bolao {
        background: linear-gradient(180deg, #4ade80 0%, #22c55e 58%, #15803d 100%) !important;
        box-shadow: 0 10px 22px rgba(34, 197, 94, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.32), inset 0 -3px 0 rgba(20, 83, 45, 0.36) !important;
    }

    .rr-mobile-quick-nav__item--bolao .rr-mobile-quick-nav__chrome-text {
        color: #ecfdf5;
    }

    .rr-mobile-quick-nav__item--bolao .rr-mobile-quick-nav__chrome-icon {
        background: rgba(255, 255, 255, 0.18);
        color: #dcfce7;
    }

    .rr-mobile-quick-nav__surface.is-open,
    .rr-mobile-quick-nav__surface.is-active {
        transform: translateY(1px);
        filter: saturate(1.06);
    }

    .rr-mobile-quick-nav__panel {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        padding: 12px;
        border-radius: 16px;
        border: 1px solid rgba(249, 115, 22, 0.26);
        background: linear-gradient(160deg, rgba(17, 24, 39, 0.96), rgba(30, 41, 59, 0.94));
        box-shadow: 0 16px 34px rgba(0, 0, 0, 0.34);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .rr-mobile-quick-nav__panel[hidden] {
        display: none !important;
    }

    .rr-mobile-quick-nav__panel[data-filter-panel="x1"] .rr-mobile-quick-nav__options {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 8px;
    }

    .rr-mobile-quick-nav__panel[data-filter-panel="bolao"] .rr-mobile-quick-nav__options {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 8px;
    }

    .rr-mobile-quick-nav__option {
        width: 100%;
        min-height: 38px;
        padding: 0 10px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        color: #fff7ed;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        cursor: pointer;
    }

    .rr-mobile-quick-nav__option.is-active {
        border-color: rgba(251, 191, 36, 0.72);
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.34), rgba(234, 179, 8, 0.26));
        color: #fff;
    }

    .rr-mobile-quick-empty {
        display: block;
        padding: 14px 12px 18px;
        color: rgba(255, 237, 213, 0.82);
        font-size: 0.74rem;
        font-weight: 700;
        text-align: center;
        letter-spacing: 0.02em;
    }

    .rr-mobile-quick-empty[hidden] {
        display: none !important;
    }

    body.light .rr-mobile-quick-nav__panel {
        border-color: rgba(37, 99, 235, 0.14);
        background: linear-gradient(160deg, rgba(255, 252, 247, 0.98), rgba(255, 242, 230, 0.96));
        box-shadow: 0 14px 30px rgba(124, 45, 18, 0.12);
    }

    body.light .rr-mobile-quick-nav__option {
        border-color: rgba(30, 64, 175, 0.12);
        background: rgba(255, 255, 255, 0.76);
        color: #7c2d12;
    }

    body.light .rr-mobile-quick-nav__option.is-active {
        border-color: rgba(249, 115, 22, 0.34);
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.18), rgba(37, 99, 235, 0.12));
        color: #111827;
    }

    body.light .rr-mobile-quick-empty {
        color: rgba(124, 45, 18, 0.78);
    }

    .rr-competitor-tools {
        margin: 8px 10px 0;
        padding: 0;
        border: 0;
        background: transparent;
        display: block;
    }

    .rr-mobile-search-shell {
        min-height: 46px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(249, 115, 22, 0.28);
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.88), rgba(30, 41, 59, 0.84));
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .rr-competitor-search-btn {
        position: absolute;
        inset: 0;
        width: 100%;
        min-height: 100%;
        padding: 0;
        border-radius: inherit;
        background: transparent !important;
        border-color: transparent !important;
        color: transparent !important;
        -webkit-text-fill-color: transparent !important;
        box-shadow: none !important;
        overflow: hidden;
        font-size: 0 !important;
        appearance: none;
        -webkit-appearance: none;
    }

    .rr-competitor-search-btn > * {
        opacity: 0 !important;
        visibility: hidden !important;
    }

    .rr-mobile-search-shell__chrome {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 16px;
        pointer-events: none;
        z-index: 1;
    }

    .rr-mobile-search-shell__icon {
        width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #f59e0be6;
        flex: 0 0 auto;
    }

    .rr-mobile-search-shell__icon svg {
        width: 18px;
        height: 18px;
    }

    .rr-mobile-search-shell__label {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #fff7ed;
        font-size: 0.78rem;
        font-weight: 900;
        letter-spacing: 0.02em;
        line-height: 1;
    }

    body.light .rr-mobile-search-shell__label {
        color: #5b2f14;
    }

    .rr-competitor-levels {
        display: none;
    }

    .rr-competitor-level-legend {
        flex: 0 0 auto;
        gap: 5px;
    }

    .rr-competitor-level-chip {
        width: 13px;
        height: 13px;
        border-radius: 3px;
    }

    .rr-competitor-level-label {
        font-size: 0.44rem;
    }

    .rr-competitor-search-modal {
        padding: 10px;
        align-items: flex-start;
    }

    .rr-competitor-search-modal__card {
        width: 100%;
        max-height: min(82vh, 720px);
        margin-top: max(8px, env(safe-area-inset-top, 0px));
        border-radius: 18px;
    }

    .rr-competitor-search-modal__body {
        padding: 12px;
    }

    .rr-competitor-search-input {
        min-height: 46px;
        border-radius: 14px;
        font-size: 0.92rem;
        padding: 0 14px;
    }

    .rr-inicio-grid {
        touch-action: auto;
        scroll-snap-type: none !important;
        overscroll-behavior: auto;
    }

    /* Remove sobreposição agressiva entre blocos (estava prejudicando o scroll) */
    .rr-inicio-layout > #rrInicioSection {
        order: 1;
    }

    .rr-inicio-layout > #rrInicioX1Rooms {
        order: 2;
    }

    .rr-inicio-layout > #rrInicioBolaos {
        order: 3;
    }

    #rrInicioSection {
        position: relative;
        z-index: auto;
        margin-bottom: 0 !important;
    }

    #rrInicioSection .card-body {
        overflow-x: clip !important;
        overflow-y: visible !important;
    }

    #rrInicioSection .rr-inicio-grid-wrap--competidores {
        overflow: visible !important;
    }

    #rrInicioSection .rr-competitor-mobile-row {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: visible;
        contain: layout inline-size paint;
    }

    #rrInicioSection .rr-competitor-mobile-row .rr-inicio-grid-wrap--competidores {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: visible;
    }

    #rrInicioSection .rr-inicio-grid {
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .rr-competitor-mobile-row .rr-inicio-grid {
        padding-left: 10px !important;
        padding-right: 10px !important;
        scroll-padding-left: 10px;
        scroll-padding-right: 10px;
    }

    .rr-competitor-mobile-row .rr-inicio-grid > * {
        flex: 0 0 auto;
    }

    #rrInicioSection .card-body {
        padding-bottom: 8px;
    }

    .rr-inicio-layout--bolao-launch #rrInicioSection > .card-body {
        padding: 0 !important;
        width: 100%;
        max-width: 100%;
    }

    #rrInicioBolaos {
        position: relative;
        z-index: auto;
        margin-bottom: 0 !important;
        padding-top: 0;
    }

    #rrInicioBolaos .rr-inicio-section__body,
    #rrInicioBolaos .rr-inicio-grid-wrap--bolaos {
        overflow: visible !important;
    }

    #rrInicioBolaos .rr-inicio-grid {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch;
    }

    #rrInicioX1Rooms {
        position: relative;
        z-index: auto;
        margin-bottom: 0 !important;
        padding-top: 0;
    }

    #rrInicioX1Rooms .rr-inicio-section__body,
    #rrInicioX1Rooms .rr-inicio-grid-wrap--x1rooms {
        overflow: visible !important;
    }

    #rrInicioX1Rooms .rr-inicio-grid {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        -webkit-overflow-scrolling: touch;
    }

    .rr-x1-room-grid {
        display: flex !important;
        flex-wrap: nowrap !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        padding-right: 12px;
        touch-action: pan-y !important;
        overscroll-behavior-x: contain;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
    }

    .rr-x1-room-grid::-webkit-scrollbar {
        display: none !important;
    }

    .rr-x1-room-grid > .rr-x1room-card {
        flex: 0 0 220px !important;
        scroll-snap-align: start;
    }

    #rrInicioBolaos .rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__title {
        font-size: 0.74rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
}

.rr-inicio-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.rr-inicio-head__title { 
    margin: 0; 
    color: #e5e7eb; 
    font-weight: 700; 
    font-size: 1.4rem;
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.rr-inicio-head__meta { 
    color: #94a3b8; 
    font-size: 0.85rem; 
}

/* ---- Grid Layout (responsivo) ---- */
.rr-inicio-grid {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none !important;
    padding: 16px 0 20px 20px;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    overscroll-behavior-x: contain;
}

.rr-inicio-grid::-webkit-scrollbar { 
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}

/* ============================================
   🔥 NEOMORPHIC COMPETITOR CARDS - REI DO RODEIO
   ============================================ */

:root {
    --rr-neuro-primary: #f59e0be6;
    --rr-neuro-secondary: #22c55e;
    --rr-neuro-dark: #1e1b26;
    --rr-neuro-bg: linear-gradient(120deg, #2a2d3a, #1a1d28);
    --rr-neuro-text: #e7e7e7;
    --rr-neuro-text-light: #b4b4b4;
}

/* ---- Holographic Card Wrapper ---- */
.rr-neuro-wrapper {
    --sunpillar-1: hsl(2, 100%, 73%);
    --sunpillar-2: hsl(53, 100%, 69%);
    --sunpillar-3: hsl(93, 100%, 69%);
    --sunpillar-4: hsl(176, 100%, 76%);
    --sunpillar-5: hsl(228, 100%, 74%);
    --sunpillar-6: hsl(283, 100%, 73%);
    --card-radius: 12px;
    --card-opacity: 1;
    width: 140px;
    position: relative;
    flex: 0 0 auto;
    border: none;
    contain: layout style;
    --rr-level-ring: #22c55e;
    --rr-level-glow: rgba(34, 197, 94, 0.38);
    --rr-level-particle-core: rgba(34, 197, 94, 0.58);
    --rr-level-particle-soft: rgba(34, 197, 94, 0.24);
    --rr-level-veil: rgba(7, 35, 21, 0.62);
}

.rr-neuro-wrapper[data-nivel="favorito"] {
    --rr-level-ring: #facc15;
    --rr-level-glow: rgba(250, 204, 21, 0.45);
    --rr-level-particle-core: rgba(250, 204, 21, 0.62);
    --rr-level-particle-soft: rgba(250, 204, 21, 0.26);
    --rr-level-veil: rgba(61, 41, 8, 0.62);
}

.rr-neuro-wrapper[data-nivel="elite"] {
    --rr-level-ring: #f59e0be6;
    --rr-level-glow: rgba(249, 115, 22, 0.42);
    --rr-level-particle-core: rgba(249, 115, 22, 0.64);
    --rr-level-particle-soft: rgba(249, 115, 22, 0.26);
    --rr-level-veil: rgba(60, 28, 8, 0.62);
}

.rr-neuro-wrapper[data-nivel="ascendente"],
.rr-neuro-wrapper[data-nivel="legado"] {
    --rr-level-ring: #3b82f6;
    --rr-level-glow: rgba(59, 130, 246, 0.42);
    --rr-level-particle-core: rgba(59, 130, 246, 0.62);
    --rr-level-particle-soft: rgba(59, 130, 246, 0.25);
    --rr-level-veil: rgba(13, 26, 58, 0.62);
}

.rr-neuro-wrapper[data-nivel="competidor"],
.rr-neuro-wrapper[data-nivel="presilha"] {
    --rr-level-ring: #22c55e;
    --rr-level-glow: rgba(34, 197, 94, 0.4);
    --rr-level-particle-core: rgba(34, 197, 94, 0.58);
    --rr-level-particle-soft: rgba(34, 197, 94, 0.24);
    --rr-level-veil: rgba(7, 35, 21, 0.62);
}

/* Glow behind wrapper - disabled by default */
.rr-neuro-wrapper::before,
.rr-neuro-wrapper::after {
    display: none;
}

/* ---- Inner card surface (static) ---- */
.rr-card-inner {
    display: grid;
    border-radius: var(--card-radius);
    position: relative;
    overflow: hidden;
    background-blend-mode: color-dodge, normal, normal, normal;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
    background-size: 100% 100%;
    background-position: 0px 0px, 0px 0px, 50% 50%, 0px 0px;
    background-image:
        radial-gradient(
            farthest-side circle at 50% 50%,
            hsla(266, 100%, 90%, 1) 4%,
            hsla(266, 50%, 80%, 0.75) 10%,
            hsla(266, 25%, 70%, 0.5) 50%,
            hsla(266, 0%, 60%, 0) 100%
        ),
        radial-gradient(35% 52% at 55% 20%, #00FFAAC4 0%, #073AFF00 100%),
        radial-gradient(100% 100% at 50% 50%, #00C1FFFF 1%, #073AFF00 76%),
        conic-gradient(from 124deg at 50% 50%, #C137FFFF 0%, #07C6FFFF 40%, #07C6FFFF 60%, #C137FFFF 100%);
    background-image: none;
    background-color: transparent;
    background-blend-mode: normal;
    z-index: 1;
}

/* Inside dark background */
.rr-card-inside {
    grid-area: 1 / -1;
    position: absolute;
    inset: 1px;
    background-image: linear-gradient(145deg, #60496e8c 0%, #71C4FF44 100%);
    background-color: rgb(0 0 0 / 90%);
    background-image: none;
    background-color: transparent;
    border-radius: var(--card-radius);
    transform: translate3d(0, 0, 0.01px);
    pointer-events: none;
}

/* ---- Shine (holographic rainbow, static animated) ---- */
.rr-card-inner .card__shine {
    display: none;
    grid-area: 1 / -1;
    position: relative;
    border-radius: var(--card-radius);
    overflow: hidden;
    z-index: 3;
    pointer-events: none;
    filter: brightness(.75) contrast(1.4) saturate(.4) opacity(0.6);
    animation: rr-holo-bg 18s linear infinite;
    mix-blend-mode: color-dodge;
    --space: 5%;
    --angle: -45deg;
    background: transparent;
    background-size: 500% 500%, 300% 300%, 200% 200%;
    background-repeat: repeat;
    background-image:
        repeating-linear-gradient(0deg,
            var(--sunpillar-1) calc(var(--space)*1),
            var(--sunpillar-2) calc(var(--space)*2),
            var(--sunpillar-3) calc(var(--space)*3),
            var(--sunpillar-4) calc(var(--space)*4),
            var(--sunpillar-5) calc(var(--space)*5),
            var(--sunpillar-6) calc(var(--space)*6),
            var(--sunpillar-1) calc(var(--space)*7)
        ),
        repeating-linear-gradient(
            var(--angle),
            #0e152e 0%,
            hsl(180, 10%, 60%) 3.8%,
            hsl(180, 29%, 66%) 4.5%,
            hsl(180, 10%, 60%) 5.2%,
            #0e152e 10%,
            #0e152e 12%
        ),
        radial-gradient(
            farthest-corner circle at 50% 50%,
            hsla(0, 0%, 0%, 0.1) 12%,
            hsla(0, 0%, 0%, 0.15) 20%,
            hsla(0, 0%, 0%, 0.25) 120%
        );
    background-position: 0% 50%, 50% 50%, center center;
    background-blend-mode: color, hard-light;
}

/* ---- Glare (static soft overlay) ---- */
.rr-card-inner .card__glare {
    display: none;
    grid-area: 1 / -1;
    position: relative;
    border-radius: var(--card-radius);
    overflow: hidden;
    z-index: 4;
    pointer-events: none;
    background-image:
        radial-gradient(
            farthest-corner circle at 50% 40%,
            hsl(248, 25%, 80%) 12%,
            hsla(207, 40%, 30%, .8) 90%
        );
    mix-blend-mode: overlay;
    filter: brightness(.8) contrast(1.2);
}

/* ---- Content layer (above shine/glare) ---- */
.rr-card-content-layer {
    grid-area: 1 / -1;
    position: relative;
    z-index: 5;
    border-radius: var(--card-radius);
    pointer-events: auto;
    height: 100%;
    display: flex;
    flex-direction: column;
}

@keyframes rr-holo-bg {
    0% { background-position: 0% 50%, 0% 0%, center center; }
    100% { background-position: 0% 50%, 90% 90%, center center; }
}

/* Bordas Neon Animadas por Apostas (a cada 5 apostas) */
.rr-neuro-wrapper.bets-0-4 { --glow-tint: rgba(59, 130, 246, 0.3); }
.rr-neuro-wrapper.bets-5-9 { --glow-tint: rgba(34, 197, 94, 0.3); }
.rr-neuro-wrapper.bets-10-14 { --glow-tint: rgba(234, 179, 8, 0.3); }
.rr-neuro-wrapper.bets-15-19 { --glow-tint: rgba(249, 115, 22, 0.3); }
.rr-neuro-wrapper.bets-20-24 { --glow-tint: rgba(251, 146, 60, 0.35); }
.rr-neuro-wrapper.bets-25-plus { --glow-tint: rgba(239, 68, 68, 0.4); }

/* Mobile: scroll horizontal */
@media (max-width: 767px) {
    .rr-neuro-wrapper {
        width: 130px;
    }
}

/* Desktop: grid vertical */
@media (min-width: 768px) {
    .rr-neuro-wrapper {
        width: 180px;
        min-width: 180px;
        max-width: 180px;
        flex: 0 0 180px;
    }
}

/* ---- Header Transparente ---- */
.rr-neuro-header {
    background: transparent;
    padding: 6px 8px;
    position: relative;
    min-height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Imagem do Competidor - Mini */
.rr-neuro-img-container {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    border: 2.5px solid var(--rr-level-ring);
    box-shadow:
        0 0 0 2px rgba(15, 23, 42, 0.95),
        0 0 16px var(--rr-level-glow),
        0 6px 14px rgba(0, 0, 0, 0.45);
    transform: scale(1);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    background: #0f172a;
}

.rr-neuro-wrapper:hover .rr-neuro-img-container {
    transform: scale(1.07);
    box-shadow:
        0 0 0 2px rgba(15, 23, 42, 1),
        0 0 22px var(--rr-level-glow),
        0 8px 16px rgba(0, 0, 0, 0.5);
}

.rr-neuro-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
}

/* ---- Content Section ---- */
.rr-neuro-content {
    padding: 7px 8px;
}

.rr-neuro-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 4px;
    line-height: 1.2;
    display: block;
    width: 100%;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 1.15em;
    background-image: linear-gradient(to bottom, white, #aaaadd);
    background-size: 1em 1.5em;
    -webkit-text-fill-color: transparent;
    -webkit-background-clip: text;
    background-clip: text;
    mix-blend-mode: plus-lighter;
}

body.light .rr-neuro-title {
    color: #7C2D12;
    background-image: none;
    -webkit-text-fill-color: #7C2D12;
    mix-blend-mode: normal;
}

@media (max-width: 767px) {
    .rr-neuro-content {
        min-width: 0;
    }

    .rr-neuro-title {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: ellipsis !important;
        display: block !important;
        line-height: 1.15;
        min-height: 1.15em;
    }
}

.rr-neuro-stats-inline {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 6px;
    padding: 6px 8px;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(22, 163, 74, 0.08));
    border: 1px solid rgba(34, 197, 94, 0.25);
    border-radius: 6px;
    transition: all 0.2s ease;
    cursor: pointer;
    gap: 4px;
}

.rr-neuro-play-now,
.rr-neuro-stats-inline,
.rr-neuro-view-stats,
.rr-neuro-premium-banner {
    appearance: none;
    -webkit-appearance: none;
    font-family: inherit;
    outline: none;
}

.rr-neuro-stats-inline[data-action="open-slip"]:hover {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.25), rgba(22, 163, 74, 0.18));
    border-color: rgba(34, 197, 94, 0.5);
    transform: translateY(-1px);
    box-shadow: 0 3px 12px rgba(34, 197, 94, 0.35);
}

.rr-neuro-stats-inline span {
    font-size: 0.65rem;
    color: var(--rr-neuro-text-light);
    font-weight: 500;
}

.rr-neuro-stats-inline .rr-neuro-odd-label {
    font-size: 0.55rem;
    color: rgba(148, 163, 184, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.rr-neuro-play-now {
    display: block;
    text-align: center;
    width: 100%;
    margin-top: 0;
    margin-bottom: 2px;
    padding: 4px 10px;
    font-family: Arial, sans-serif;
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #fff;
    background: linear-gradient(180deg, #f59e0be6 0%, #ea580c 50%, #c2410c 100%);
    border: none;
    border-radius: 4px;
    box-shadow:
        0 2px 0 #9a3412,
        0 4px 0 #7c2d12,
        0 6px 8px rgba(0, 0, 0, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
    transform: translateY(0);
    transition: transform 0.1s ease, box-shadow 0.1s ease;
    cursor: pointer;
    position: relative;
}

.rr-neuro-play-now:active {
    transform: translateY(3px);
    box-shadow:
        0 1px 0 #9a3412,
        0 1px 4px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.rr-neuro-multiplier {
    font-size: 1rem !important;
    font-weight: 900 !important;
    color: #22c55e !important;
    text-shadow: 0 0 8px rgba(34, 197, 94, 0.4);
}

/* Premium Banner */
.rr-neuro-premium-banner {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 7px;
    background:
        linear-gradient(135deg, rgba(14, 165, 233, 0.18) 0%, rgba(37, 99, 235, 0.2) 42%, rgba(249, 115, 22, 0.22) 100%);
    border: 1px solid rgba(125, 211, 252, 0.28);
    border-radius: 8px;
    margin-bottom: 6px;
    cursor: pointer;
    box-shadow:
        0 10px 20px rgba(2, 6, 23, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.14);
    transition: all 0.3s ease;
}

.rr-neuro-premium-banner:hover {
    background:
        linear-gradient(135deg, rgba(56, 189, 248, 0.26) 0%, rgba(37, 99, 235, 0.28) 40%, rgba(251, 146, 60, 0.3) 100%);
    border-color: rgba(125, 211, 252, 0.42);
    transform: translateY(-1px);
}

.rr-neuro-premium-icon {
    width: 13px;
    height: 13px;
    background: linear-gradient(135deg, #38bdf8 0%, #2563eb 52%, #f59e0be6 100%);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff7ed;
    font-size: 0.44rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.28);
}

.rr-neuro-premium-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 1px;
    min-width: 0;
}

.rr-neuro-premium-title {
    font-size: 0.5rem;
    color: rgba(239, 246, 255, 0.88);
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.rr-neuro-premium-value {
    font-size: 0.76rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: 0.01em;
    color: #fff;
    margin: 0;
    background: linear-gradient(135deg, #7dd3fc 0%, #93c5fd 42%, #fdba74 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 6px 14px rgba(37, 99, 235, 0.28);
}

.rr-neuro-premium-diff {
    font-size: 0.6rem;
    color: var(--rr-neuro-secondary);
    margin: 0;
    font-weight: 600;
}

.rr-neuro-premium-arrow {
    color: rgba(224, 242, 254, 0.84);
    font-size: 0.72rem;
    transition: transform 0.3s ease;
}

.rr-neuro-premium-banner:hover .rr-neuro-premium-arrow {
    transform: translateX(2px);
}



/* ============================================
   💰 BOLÃO CARDS - NEOMORPHIC STYLE
   ============================================ */

.rr-bolao-card {
    --rr-bolao-cover-scale: 1.88;
    width: 180px;
    background: linear-gradient(145deg, #1a1f2e, #0f1419);
    border-radius: 6px;
    overflow: visible;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    flex-shrink: 0;
    position: relative;
    padding-top: 0;
    margin-top: 44px;
    border: 2px solid transparent;
    contain: layout style;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    isolation: isolate;
}

@media (min-width: 768px) {
    .rr-inicio-grid-wrap--bolaos .rr-bolao-card {
        width: 250px;
        padding-top: 66px;
        margin-top: 66px;
    }

    .rr-inicio-grid-wrap--bolaos .rr-bolao-header {
        top: -68px;
        width: 120px;
        height: 120px;
    }
    .rr-inicio-grid-wrap--bolaos .rr-bolao-header { z-index: 0; }
}

/* Bordas por tipo de bolão */
.rr-bolao-card.bolao--20 {
    border-color: #f59e0be6;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(234, 179, 8, 0.5),
                inset 0 0 20px rgba(234, 179, 8, 0.1);
}

.rr-bolao-card.bolao--50 {
    border-color: #22c55e;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(34, 197, 94, 0.5),
                inset 0 0 20px rgba(34, 197, 94, 0.1);
}

.rr-bolao-card.bolao--100 {
    border-color: #f59e0be6;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(249, 115, 22, 0.5),
                inset 0 0 20px rgba(249, 115, 22, 0.1);
}

.rr-bolao-card.bolao--premium {
    border-color: #3b82f6;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(59, 130, 246, 0.5),
                inset 0 0 20px rgba(59, 130, 246, 0.1);
}

.rr-bolao-card:hover {
    transform: translateY(-3px);
}

/* Header com imagem - Flutuante */
.rr-bolao-header {
    position: absolute;
    top: -52px;
    left: 50%;
    transform: translateX(-50%);
    width: 94px;
    height: 94px;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
    z-index: 6;
}

.rr-bolao-image {
    position: relative;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
    transition: transform 0.3s ease, filter 0.3s ease;
    border: none;
    background: transparent;
    box-shadow: none;
    animation: rr-bolao-photo-float 2.8s ease-in-out infinite;
    z-index: -1;
}

.rr-bolao-card:hover .rr-bolao-image {
    transform: scale(1.08) rotate(2deg);
    filter: brightness(1.08);
}

@keyframes rr-bolao-photo-float {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-4px) scale(1.03);
    }
}

/* Badge Premium */
.rr-bolao-premium-badge {
    position: absolute;
    top: -10px;
    right: 8px;
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    color: #000;
    font-size: 0.5rem;
    font-weight: 800;
    padding: 2px 5px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 2px;
    z-index: 3;
}

.rr-bolao-premium-badge i {
    font-size: 0.55rem;
}

/* Badge Exclusivo (Laranja) - Alinhado com a imagem */
.rr-bolao-exclusive-badge {
    position: absolute;
    top: -35px;
    left: 8px;
    background: linear-gradient(135deg, #ff6b35, #f7931e);
    color: #fff;
    font-size: 0.5rem;
    font-weight: 800;
    padding: 2px 5px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 2px;
    z-index: 3;
    box-shadow: 0 2px 6px rgba(255, 107, 53, 0.4);
}

.rr-bolao-exclusive-badge i {
    font-size: 0.55rem;
}

/* Conteúdo */
.rr-bolao-content {
    padding: 8px;
}

.rr-bolao-title {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(248, 250, 252, 0.95);
    margin-bottom: 6px;
    line-height: 1.3;
    height: 32px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* Informações do Rodeio e Modalidade */
.rr-bolao-meta {
    margin-bottom: 6px;
    padding: 4px 0;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.rr-bolao-meta-item {
    font-size: 0.55rem;
    color: rgba(148, 163, 184, 0.7);
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 3px;
    margin-bottom: 2px;
}

.rr-bolao-meta-item i {
    font-size: 0.5rem;
    color: rgba(148, 163, 184, 0.5);
}

.rr-bolao-meta-label {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.rr-bolao-meta-value {
    font-weight: 400;
}

/* Prêmio - DESTAQUE ÚNICO */
.rr-bolao-prize {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(22, 163, 74, 0.2));
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 4px;
    padding: 6px;
    margin-bottom: 6px;
    text-align: center;
}

.rr-bolao-prize-label {
    font-size: 0.5rem;
    color: rgba(134, 239, 172, 0.8);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 2px;
    font-weight: 600;
}

.rr-bolao-prize-value {
    font-size: 0.9rem;
    font-weight: 900;
    color: #22c55e;
    font-family: Arial, sans-serif;
}

/* Info row */
.rr-bolao-info {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 8px;
    font-size: 0.55rem;
    color: rgba(148, 163, 184, 0.8);
    margin-bottom: 6px;
    padding: 0 2px;
    flex-wrap: wrap;
}

.rr-bolao-info-item {
    display: flex;
    align-items: center;
    gap: 3px;
}

.rr-bolao-info-item i {
    font-size: 0.55rem;
    color: rgba(148, 163, 184, 0.6);
}

.rr-bolao-info-item--entry {
    color: #f59e0be6;
    font-weight: 700;
}

.rr-bolao-info-item--entry i {
    color: rgba(251, 191, 36, 0.85);
}

.rr-bolao-info-item--timer {
    width: 100%;
    justify-content: center;
    font-size: 0.52rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

/* Status badges */
.rr-bolao-status {
    display: inline-block;
    font-size: 0.5rem;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: 700;
    text-transform: uppercase;
    margin-left: auto;
}

.rr-bolao-status--open {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.rr-bolao-status--full {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.rr-bolao-status--closed {
    background: rgba(148, 163, 184, 0.15);
    color: #94a3b8;
    border: 1px solid rgba(148, 163, 184, 0.3);
}

/* Botão Criar Equipe */
.rr-bolao-btn {
    width: 100%;
    padding: 5px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border: none;
    border-radius: 4px;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.2s ease;
    box-shadow: -1px -1px 2px rgba(80, 80, 80, 0.1),
                1px 1px 2px rgba(0, 0, 0, 0.3);
}

.rr-bolao-btn:hover {
    background: linear-gradient(135deg, #60a5fa, #3b82f6);
    transform: translateY(-1px);
    box-shadow: -1px -1px 2px rgba(80, 80, 80, 0.15),
                1px 1px 2px rgba(0, 0, 0, 0.4),
                0 0 8px rgba(59, 130, 246, 0.4);
}

.rr-bolao-btn:disabled {
    background: rgba(71, 85, 105, 0.5);
    cursor: not-allowed;
    opacity: 0.5;
}

.rr-bolao-btn i {
    font-size: 0.6rem;
}

/* Loading state */
.rr-neuro-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: rgba(148, 163, 184, 0.8);
}

/* ============================================
   ⚔️ X1 ROOM CARDS - NEOMORPHIC STYLE
   ============================================ */

.rr-x1room-card {
    width: 180px;
    background: linear-gradient(145deg, #1a1f2e, #0f1419);
    border-radius: 6px;
    overflow: visible;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    flex-shrink: 0;
    position: relative;
    border: 2px solid transparent;
    contain: layout style;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
}

/* Borda neon por valor de entrada da sala */
.rr-x1room-card.x1val-0-50 {
    border-color: #3b82f6;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 15px rgba(59, 130, 246, 0.5),
                inset 0 0 15px rgba(59, 130, 246, 0.1);
}

.rr-x1room-card.x1val-50-100 {
    border-color: #22c55e;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 15px rgba(34, 197, 94, 0.5),
                inset 0 0 15px rgba(34, 197, 94, 0.1);
}

.rr-x1room-card.x1val-100-250 {
    border-color: #f59e0be6;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 18px rgba(234, 179, 8, 0.5),
                inset 0 0 18px rgba(234, 179, 8, 0.1);
}

.rr-x1room-card.x1val-250-500 {
    border-color: #f59e0be6;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 20px rgba(249, 115, 22, 0.5),
                inset 0 0 20px rgba(249, 115, 22, 0.1);
}

.rr-x1room-card.x1val-500-1000 {
    border-color: #f59e0be6;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 22px rgba(251, 146, 60, 0.6),
                inset 0 0 22px rgba(251, 146, 60, 0.15);
}

.rr-x1room-card.x1val-1000-plus {
    border-color: #ef4444;
    box-shadow: -2px -2px 4px rgba(80, 80, 80, 0.1),
                2px 2px 4px rgba(0, 0, 0, 0.4),
                0 0 25px rgba(239, 68, 68, 0.7),
                inset 0 0 25px rgba(239, 68, 68, 0.2);
}

.rr-x1room-card:hover {
    transform: translateY(-3px);
}

/* Header com VS */
.rr-x1room-header {
    padding: 8px 8px 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 40px;
}

.rr-x1room-player {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    flex: 1;
    min-width: 0;
}

.rr-x1room-player-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    border: 1.5px solid rgba(148, 163, 184, 0.3);
    background: rgba(15, 23, 42, 0.6);
    flex-shrink: 0;
}

.rr-x1room-player-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.rr-x1room-player-avatar--logo img {
    object-fit: contain;
    padding: 2px;
}

.rr-x1room-player-avatar--host {
    border-color: rgba(251, 191, 36, 0.5);
}

.rr-x1room-player-name {
    font-size: 0.6rem;
    font-weight: 700;
    color: rgba(248, 250, 252, 0.95);
    text-align: center;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 70px;
    display: flex;
    align-items: center;
    gap: 2px;
}

.rr-x1room-player-name--host {
    color: #f59e0be6;
}

.rr-x1room-player-name--waiting {
    color: rgba(148, 163, 184, 0.5);
    font-style: italic;
}

.rr-x1room-player-crown {
    color: #f59e0be6;
    font-size: 0.5rem;
    flex-shrink: 0;
}

.rr-x1room-vs {
    font-size: 0.7rem;
    font-weight: 900;
    color: #ef4444;
    text-shadow: 0 0 8px rgba(239, 68, 68, 0.5);
    flex-shrink: 0;
}

/* Content */
.rr-x1room-content {
    padding: 6px 8px 8px;
}

/* Competitor / Modalidade info */
.rr-x1room-meta {
    margin-bottom: 6px;
    padding: 4px 0;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.rr-x1room-meta-item {
    font-size: 0.55rem;
    color: rgba(148, 163, 184, 0.7);
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 3px;
    margin-bottom: 2px;
}

.rr-x1room-meta-item i {
    font-size: 0.5rem;
    color: rgba(148, 163, 184, 0.5);
}

/* Prêmio */
.rr-x1room-prize {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(22, 163, 74, 0.2));
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 4px;
    padding: 5px;
    margin-bottom: 6px;
    text-align: center;
}

.rr-x1room-prize-label {
    font-size: 0.5rem;
    color: rgba(134, 239, 172, 0.8);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1px;
    font-weight: 600;
}

.rr-x1room-prize-value {
    font-size: 0.9rem;
    font-weight: 900;
    color: #22c55e;
    font-family: Arial, sans-serif;
}

/* Info row: entrada + status */
.rr-x1room-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.55rem;
    color: rgba(148, 163, 184, 0.8);
    margin-bottom: 6px;
    padding: 0 2px;
}

.rr-x1room-info-item {
    display: flex;
    align-items: center;
    gap: 3px;
}

.rr-x1room-info-item i {
    font-size: 0.55rem;
    color: rgba(148, 163, 184, 0.6);
}

/* Status badges */
.rr-x1room-status {
    display: inline-block;
    font-size: 0.5rem;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: 700;
    text-transform: uppercase;
}

.rr-x1room-status--open {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.rr-x1room-status--in_progress {
    background: rgba(249, 115, 22, 0.15);
    color: #f59e0be6;
    border: 1px solid rgba(249, 115, 22, 0.3);
}

.rr-x1room-status--finished {
    background: rgba(148, 163, 184, 0.15);
    color: #94a3b8;
    border: 1px solid rgba(148, 163, 184, 0.3);
}

/* Tema escuro: textos cinza dos cards X1 em branco */
body:not(.light) .rr-x1room-player-name--waiting,
body:not(.light) .rr-x1room-meta-item,
body:not(.light) .rr-x1room-meta-item i,
body:not(.light) .rr-x1room-info,
body:not(.light) .rr-x1room-info-item,
body:not(.light) .rr-x1room-info-item i,
body:not(.light) .rr-x1room-status--finished {
    color: rgba(248, 250, 252, 0.95) !important;
}

/* Tema claro: textos cinza dos cards X1 em escuro */
body.light .rr-x1room-player-name--waiting,
body.light .rr-x1room-meta-item,
body.light .rr-x1room-meta-item i,
body.light .rr-x1room-info,
body.light .rr-x1room-info-item,
body.light .rr-x1room-info-item i,
body.light .rr-x1room-status--finished {
    color: #7C2D12 !important;
}

/* Tema claro: mesma cor dos nomes dos competidores nos textos de info dos cards */
body.light .rr-neuro-stats-inline span,
body.light .rr-neuro-stats-inline .rr-neuro-odd-label,
body.light .rr-neuro-premium-title,
body.light .rr-bolao-status--closed {
    color: #7C2D12 !important;
}

body.light .rr-neuro-premium-value {
    color: #1e3a8a !important;
    background: none !important;
    -webkit-background-clip: initial !important;
    background-clip: initial !important;
    -webkit-text-fill-color: #1e3a8a !important;
    text-shadow: none !important;
}

/* Textos pequenos do card de bolao sempre em branco */
.rr-bolao-meta-item,
.rr-bolao-meta-item i,
.rr-bolao-meta-label,
.rr-bolao-meta-value,
.rr-bolao-info,
.rr-bolao-info-item,
.rr-bolao-info-item i,
.rr-bolao-prize-label,
.rr-bolao-status,
.rr-bolao-status--open,
.rr-bolao-status--full,
.rr-bolao-status--closed {
    color: #fff !important;
}

body.light .rr-bolao-meta-item,
body.light .rr-bolao-meta-item i,
body.light .rr-bolao-meta-label,
body.light .rr-bolao-meta-value,
body.light .rr-bolao-info,
body.light .rr-bolao-info-item,
body.light .rr-bolao-info-item i,
body.light .rr-bolao-prize-label,
body.light .rr-bolao-status,
body.light .rr-bolao-status--open,
body.light .rr-bolao-status--full,
body.light .rr-bolao-status--closed {
    color: #000 !important;
}

/* Premium badge */
.rr-x1room-premium-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    color: #000;
    font-size: 0.5rem;
    font-weight: 800;
    padding: 2px 5px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 2px;
    z-index: 3;
}

.rr-x1room-premium-badge i {
    font-size: 0.55rem;
}

/* Winner badge - sobreposto no topo centralizado */
.rr-x1room-winner-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    font-size: 0.55rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 3px;
    z-index: 4;
    white-space: nowrap;
    box-shadow: 0 3px 10px rgba(34, 197, 94, 0.5);
}

.rr-x1room-winner-badge i {
    font-size: 0.55rem;
}

/* Winner badge herda cor da borda do card */
.rr-x1room-card.x1val-0-50 .rr-x1room-winner-badge {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    box-shadow: 0 3px 10px rgba(59, 130, 246, 0.5);
}
.rr-x1room-card.x1val-50-100 .rr-x1room-winner-badge {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    box-shadow: 0 3px 10px rgba(34, 197, 94, 0.5);
}
.rr-x1room-card.x1val-100-250 .rr-x1room-winner-badge {
    background: linear-gradient(135deg, #f59e0be6, #ca8a04);
    box-shadow: 0 3px 10px rgba(234, 179, 8, 0.5);
}
.rr-x1room-card.x1val-250-500 .rr-x1room-winner-badge {
    background: linear-gradient(135deg, #f59e0be6, #ea580c);
    box-shadow: 0 3px 10px rgba(249, 115, 22, 0.5);
}
.rr-x1room-card.x1val-500-1000 .rr-x1room-winner-badge {
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    box-shadow: 0 3px 10px rgba(251, 146, 60, 0.5);
}
.rr-x1room-card.x1val-1000-plus .rr-x1room-winner-badge {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 3px 10px rgba(239, 68, 68, 0.5);
}

/* Botão Entrar / Ver */
.rr-x1room-btn {
    width: 100%;
    padding: 5px;
    background: linear-gradient(135deg, #f59e0be6, #ea580c);
    border: none;
    border-radius: 4px;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.2s ease;
    box-shadow: -1px -1px 2px rgba(80, 80, 80, 0.1),
                1px 1px 2px rgba(0, 0, 0, 0.3);
}

.rr-x1room-btn:hover {
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    transform: translateY(-1px);
    box-shadow: -1px -1px 2px rgba(80, 80, 80, 0.15),
                1px 1px 2px rgba(0, 0, 0, 0.4),
                0 0 8px rgba(249, 115, 22, 0.4);
}

.rr-x1room-btn--finished {
    background: linear-gradient(135deg, #475569, #334155);
    cursor: default;
}

.rr-x1room-btn--finished:hover {
    background: linear-gradient(135deg, #475569, #334155);
    transform: none;
    box-shadow: -1px -1px 2px rgba(80, 80, 80, 0.1),
                1px 1px 2px rgba(0, 0, 0, 0.3);
}

.rr-x1room-btn i {
    font-size: 0.6rem;
}

/* ============================================
   🎯 GRID LAYOUT - MOBILE SCROLL / DESKTOP GRID
   ============================================ */

.rr-inicio-grid {
    display: flex;
    gap: 16px;
    padding: 16px 0 20px;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(249, 115, 22, 0.5) rgba(15, 23, 42, 0.3);
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    overscroll-behavior-x: contain;
}

.rr-inicio-grid-wrap {
    position: relative;
}

.rr-carousel-nav {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    padding: 8px 8px 0;
}

.rr-carousel-nav[hidden] {
    display: none !important;
}

.rr-carousel-nav__btn {
    width: 24px;
    height: 24px;
    border-radius: 999px;
    border: 1px solid rgba(249, 115, 22, 0.28);
    background: linear-gradient(180deg, rgba(17, 24, 39, 0.9), rgba(30, 41, 59, 0.9));
    color: rgba(251, 191, 36, 0.96);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.62rem;
    box-shadow: 0 8px 16px rgba(2, 6, 23, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.14);
    transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease, opacity 0.16s ease;
    cursor: pointer;
}

.rr-carousel-nav__btn:hover:not(:disabled) {
    transform: translateY(-1px);
    filter: brightness(1.06);
}

.rr-carousel-nav__btn:disabled {
    opacity: 0.38;
    cursor: default;
    box-shadow: none;
}

body.light .rr-carousel-nav__btn {
    border-color: rgba(30, 64, 175, 0.14);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 240, 224, 0.96));
    color: #1e3a8a;
    box-shadow: 0 8px 16px rgba(124, 45, 18, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.84);
}

.rr-inicio-grid-wrap::after {
    content: '›';
    position: absolute;
    right: 16px;
    top: 28px;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    color: rgba(251, 146, 60, 0.9);
    text-shadow: 0 0 8px rgba(249, 115, 22, 0.5);
    pointer-events: none;
    z-index: 6;
    animation: rr-scroll-hint-dots 1.8s ease-in-out infinite;
}

.rr-inicio-grid-wrap::before {
    content: 'DESLIZE';
    position: absolute;
    right: 10px;
    top: 8px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid rgba(249, 115, 22, 0.65);
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.86), rgba(30, 41, 59, 0.86));
    font-size: 0.62rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    color: rgba(251, 191, 36, 0.95);
    text-shadow: 0 0 6px rgba(249, 115, 22, 0.4);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
    pointer-events: none;
    z-index: 6;
    animation: rr-scroll-hint-breath 2s ease-in-out infinite;
}

@keyframes rr-scroll-hint-breath {
    0%, 100% {
        transform: translateX(0);
        opacity: 0.86;
    }
    50% {
        transform: translateX(-4px);
        opacity: 1;
    }
}

@keyframes rr-scroll-hint-dots {
    0%, 100% {
        transform: translateX(0);
        opacity: 0.6;
    }
    50% {
        transform: translateX(5px);
        opacity: 1;
    }
}

.rr-inicio-grid::-webkit-scrollbar {
    height: 6px;
}

.rr-inicio-grid::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.3);
    border-radius: 3px;
}

.rr-inicio-grid::-webkit-scrollbar-thumb {
    background: rgba(249, 115, 22, 0.5);
    border-radius: 3px;
}

.rr-inicio-grid::-webkit-scrollbar-thumb:hover {
    background: rgba(249, 115, 22, 0.7);
}

/* Mobile: scroll horizontal com snap e espaçamento */
@media (max-width: 767px) {
    .rr-inicio-grid {
        padding: 16px 8px;
        padding-left: 8px;
        scrollbar-width: none;
    }

    /* Snap apenas quando o usuário interage (não durante auto-scroll) */
    .rr-inicio-grid.is-user-scroll {
        scroll-snap-type: x proximity;
    }

    .rr-inicio-grid-wrap::before {
        right: 8px;
        top: 8px;
        font-size: 0.56rem;
        padding: 4px 8px;
    }

    .rr-inicio-grid-wrap::after {
        right: 12px;
        top: 27px;
        font-size: 0.65rem;
    }

    .rr-carousel-nav {
        padding: 8px 10px 0;
    }
    
    .rr-inicio-grid::-webkit-scrollbar {
        display: none;
    }
    
    .rr-neuro-wrapper,
    .rr-bolao-card,
    .rr-x1room-card {
        scroll-snap-align: start;
    }
    
    /* Primeiro item com espaçamento extra */
    .rr-neuro-wrapper:first-child,
    .rr-bolao-card:first-child {
        margin-left: 0;
    }

    .rr-inicio-shell .card-body {
        padding-bottom: 0;
    }
    .rr-inicio-section {
        margin-top: 0;
    }

    /* Mobile: containers sem bordas laterais, edge-to-edge */
    .rr-inicio-shell,
    .rr-inicio-shell.card {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin-left: 0;
        margin-right: 0;
    }

    .rr-inicio-section {
        border: none;
        border-radius: 0;
        padding-left: 0;
        padding-right: 0;
    }

    .rr-inicio-section__body {
        padding-left: 0;
        padding-right: 0;
    }

    .hub-side-card {
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
    }
}

/* Desktop: scroll horizontal sem scrollbar, apenas drag */
@media (min-width: 768px) {
    .rr-inicio-grid {
        padding: 4px 0;
        gap: 16px;
        scrollbar-width: none;
        cursor: grab;
    }
    
    .rr-inicio-grid::-webkit-scrollbar {
        display: none !important;
    }
    
    .rr-inicio-grid.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        user-select: none;
    }

    .rr-inicio-layout {
        gap: 18px;
    }

    .rr-inicio-shell,
    #rrInicioBolaos .rr-inicio-section__body,
    #rrInicioX1Rooms .rr-inicio-section__body {
        border: 1px solid rgba(249, 115, 22, 0.22);
        border-radius: 24px;
        background: transparent;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
        overflow: hidden;
    }

    body.light .rr-inicio-shell,
    body.light #rrInicioBolaos .rr-inicio-section__body,
    body.light #rrInicioX1Rooms .rr-inicio-section__body {
        background: transparent;
        border-color: rgba(234, 88, 12, 0.14);
        box-shadow: 0 14px 34px rgba(234, 88, 12, 0.08);
    }

    .rr-inicio-shell .card-body,
    .rr-inicio-section__body {
        padding: 18px;
    }

    .rr-inicio-section {
        margin-top: 0;
    }

    .rr-inicio-section-label {
        display: flex !important;
        justify-content: flex-start;
        padding: 0 0 14px;
    }

    .rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__text {
        align-items: flex-start;
    }

    .rr-inicio-section-label.rr-inicio-section-label--hero .rr-inicio-section-label__title {
        font-size: 1rem;
    }

    .rr-competitor-tools {
        margin: 8px 10px 16px !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        flex-wrap: nowrap;
        gap: 0;
    }

    .rr-inicio-grid-wrap {
        border-radius: 18px;
    }

    .rr-carousel-nav {
        padding-right: 4px;
    }
}

@media (min-width: 1100px) {
    .rr-inicio-layout {
        grid-template-columns: minmax(0, 1.16fr) minmax(420px, 1.06fr);
        align-items: start;
    }

    .rr-inicio-layout > #rrInicioSection {
        grid-column: 1;
        grid-row: 1 / span 2;
        min-height: 100%;
    }

    .rr-inicio-layout > #rrInicioBolaos {
        grid-column: 2;
        grid-row: 1;
    }

    .rr-inicio-layout > #rrInicioX1Rooms {
        grid-column: 2;
        grid-row: 2;
    }
}

/* ===== Desktop: Tudo dentro de hub-top__grid com CSS Grid ===== */
@media (min-width: 768px) {
    .rr-competitor-mobile-row {
        display: none;
    }

    .rr-competitor-desktop-stack {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .rr-competitor-row__label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 10px;
        font-size: 0.74rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(255, 221, 196, 0.82);
    }

    .rr-competitor-row__label strong {
        color: #fff7ed;
        font-weight: 900;
    }

    body.light .rr-competitor-row__label {
        color: rgba(68, 42, 20, 0.74);
    }

    body.light .rr-competitor-row__label strong {
        color: #7c2d12;
    }

    /*  Grid master quando inicio está ativo:
        col1 = live   |  col2 = X1 + Bolão (empilhados)
        row2 = competidores (full-width)
        row3 = hubMain restante (se houver conteúdo adicional) */
    .hub-top__grid.has-side-panels {
        display: grid;
        grid-template-columns: 1fr 1.08fr;
        grid-template-rows: auto auto auto;
        align-items: start;
        width: 100%;
        padding: 0.5rem 10px !important;
        gap: 6px 12px;
    }

    /* Live: col1, row1 */
    .hub-top__grid.has-side-panels > .hub-top__live-wrapper {
        grid-column: 1;
        grid-row: 1;
    }

    /* Painel empilhado X1+Bolão: col2, row1 — até a borda direita */
    .hub-top__grid .rr-side-panel--right-stack {
        grid-column: 2;
        grid-row: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
        overflow: visible;
        min-width: 0;
        padding-right: 0;
        position: relative;
        z-index: 12;
    }

    /* Competidores: full-width, row2 */
    .hub-top__grid .rr-side-panel--bottom {
        grid-column: 1 / -1;
        grid-row: 2;
        overflow: visible;
        position: relative;
        min-width: 0;
        z-index: 4;
    }

    /* hubMain: full-width, row3 — esconde quando vazio (reparented) */
    .hub-top__grid.has-side-panels > .hub-top__main {
        grid-column: 1 / -1;
        grid-row: 3;
    }

    /* Tabbar desktop já fica escondido no hub.blade */
    .hub-top__grid.has-side-panels > .hub-shell__nav {
        display: none !important;
    }

    .rr-inicio-event-call {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin: 0 10px 14px;
        padding: 16px 16px 70px;
        min-height: 212px;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(245, 158, 11, 0.34);
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.2), transparent 42%),
            radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.24), transparent 48%),
            linear-gradient(180deg, rgba(18, 24, 38, 0.98) 0%, rgba(10, 13, 24, 0.98) 100%);
        box-shadow:
            0 18px 40px rgba(2, 6, 23, 0.42),
            inset 0 1px 0 rgba(255, 255, 255, 0.04);
        isolation: isolate;
    }

    .rr-inicio-event-call::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(130deg, rgba(245, 158, 11, 0.12), transparent 38%, rgba(37, 99, 235, 0.08) 72%, transparent 100%);
        pointer-events: none;
        z-index: -1;
    }

    body.light .rr-inicio-event-call {
        border-color: rgba(251, 146, 60, 0.22);
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.1), transparent 42%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.82) 0%, rgba(255, 255, 255, 0.62) 100%);
        box-shadow:
            0 14px 28px rgba(148, 163, 184, 0.14),
            inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }

    body.light .rr-inicio-event-call::before {
        background:
            linear-gradient(130deg, rgba(245, 158, 11, 0.08), transparent 42%, rgba(245, 158, 11, 0.04) 76%, transparent 100%);
    }

    body.light .rr-inicio-event-call--launch {
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.14), transparent 34%),
            radial-gradient(circle at 12% 82%, rgba(59, 130, 246, 0.1), transparent 28%),
            radial-gradient(circle at 88% 28%, rgba(34, 197, 94, 0.08), transparent 24%),
            linear-gradient(180deg, rgba(255, 250, 244, 0.98) 0%, rgba(255, 247, 237, 0.98) 100%);
    }

    body.light .rr-inicio-event-call--launch::before {
        background:
            linear-gradient(128deg, rgba(245, 158, 11, 0.08), transparent 34%, rgba(59, 130, 246, 0.08) 68%, transparent 100%);
    }

    body.light .rr-inicio-event-call__launch-title {
        color: #7c2d12;
    }

    body.light .rr-inicio-event-call__launch-note {
        color: #7c5a4a;
    }

    .rr-inicio-event-call__badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
    }

    .rr-inicio-event-call__badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #fff7ed;
        border: 1px solid rgba(245, 158, 11, 0.3);
        background: rgba(15, 23, 42, 0.7);
        box-shadow: 0 10px 24px rgba(2, 6, 23, 0.22);
        animation: rrInicioEventBadgeFloat 2.8s ease-in-out infinite;
    }

    .rr-inicio-event-call__badge i,
    .rr-inicio-event-call__launch-kicker i {
        color: #f97316;
    }

    .rr-inicio-event-call__badge--live {
        color: #fef3c7;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.88), rgba(217, 119, 6, 0.82));
        border-color: rgba(253, 224, 71, 0.44);
        box-shadow: 0 14px 28px rgba(245, 158, 11, 0.24);
    }

    .rr-inicio-event-call__badge--accent {
        animation-delay: .24s;
    }

    .rr-inicio-event-call__badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #f97316;
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.34);
        animation: rrInicioEventDotPulse 1.8s ease-in-out infinite;
    }

    .rr-inicio-event-call--launch {
        gap: 14px;
        min-height: 332px;
        padding: 18px 18px 92px;
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.24), transparent 34%),
            radial-gradient(circle at 12% 82%, rgba(59, 130, 246, 0.2), transparent 28%),
            radial-gradient(circle at 88% 28%, rgba(34, 197, 94, 0.16), transparent 24%),
            linear-gradient(180deg, rgba(18, 24, 38, 0.98) 0%, rgba(10, 13, 24, 0.98) 100%);
    }

    .rr-inicio-event-call--launch::before {
        background:
            linear-gradient(128deg, rgba(245, 158, 11, 0.16), transparent 34%, rgba(59, 130, 246, 0.12) 68%, transparent 100%);
    }

    .rr-inicio-event-call__launch-copy {
        position: relative;
        z-index: 2;
        display: grid;
        gap: 8px;
        width: min(100%, 680px);
        text-align: center;
    }

    .rr-inicio-event-call__launch-kicker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: fit-content;
        margin: 0 auto;
        padding: 7px 14px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.22), rgba(37, 99, 235, 0.2));
        color: #fff7ed;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        box-shadow: 0 16px 28px rgba(2, 6, 23, 0.2);
        animation: rrInicioEventBadgeFloat 2.6s ease-in-out infinite;
    }

    .rr-inicio-event-call__launch-kicker::before {
        content: "";
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #f97316;
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.42);
        animation: rrInicioEventDotPulse 1.7s ease-in-out infinite;
    }

    .rr-inicio-event-call__launch-title {
        margin: 0;
        color: #fff7ed;
        font-size: clamp(1.45rem, 2.8vw, 2.35rem);
        line-height: 1.02;
        font-weight: 900;
        letter-spacing: -0.05em;
        text-wrap: balance;
    }

    .rr-inicio-event-call__launch-note {
        margin: 0 auto;
        max-width: 56ch;
        color: rgba(255, 237, 213, 0.86);
        font-size: 0.95rem;
        line-height: 1.58;
        font-weight: 700;
    }

    .rr-inicio-event-call__launch-title:empty,
    .rr-inicio-event-call__launch-note:empty {
        display: none;
    }

    .rr-inicio-event-call__logo-wrap {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 122px;
        padding: 2px 0 0;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap {
        min-height: 320px;
        padding-top: 10px;
    }

    .rr-inicio-event-call__logo-stack {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        flex-wrap: nowrap;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-stack {
        z-index: 3;
        gap: 0;
        animation: rrInicioLaunchLogoFloat 5.2s ease-in-out infinite;
    }

    .rr-inicio-event-call__logo-wrap::before {
        content: "";
        position: absolute;
        width: 156px;
        height: 156px;
        border-radius: 999px;
        background:
            radial-gradient(circle, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0.02) 58%, transparent 72%);
        filter: blur(3px);
        animation: rrInicioEventHalo 3.2s ease-in-out infinite;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap::before {
        width: 380px;
        height: 380px;
        background:
            radial-gradient(circle, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0.08) 38%, rgba(59, 130, 246, 0.04) 62%, transparent 74%);
        filter: blur(10px);
        animation-duration: 4.8s;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap::after {
        display: none;
    }

    .rr-inicio-event-call__logo-frame {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 124px;
        height: 124px;
        padding: 12px;
        border-radius: 28px;
        border: 1px solid rgba(245, 158, 11, 0.22);
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.86) 0%, rgba(12, 18, 30, 0.82) 100%);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.05),
            0 18px 32px rgba(2, 6, 23, 0.28);
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame {
        width: 168px;
        height: 168px;
        padding: 16px;
        border-radius: 36px;
        border-color: rgba(251, 191, 36, 0.22);
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.88) 0%, rgba(8, 12, 24, 0.86) 100%);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.05),
            0 20px 38px rgba(2, 6, 23, 0.28);
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame:first-child {
        width: auto;
        height: auto;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame:nth-child(2) {
        margin-left: -56px;
        transform: translate(8px, 52px) rotate(8deg);
        opacity: 0.96;
    }

    .rr-inicio-event-call__logo {
        position: relative;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        filter: drop-shadow(0 12px 30px rgba(245, 158, 11, 0.2));
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo {
        filter: drop-shadow(0 18px 34px rgba(245, 158, 11, 0.26));
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame:first-child .rr-inicio-event-call__logo {
        width: clamp(260px, 34vw, 440px);
        height: auto;
        max-width: min(78vw, 440px);
        max-height: none;
        filter:
            drop-shadow(0 26px 44px rgba(245, 158, 11, 0.22))
            drop-shadow(0 10px 28px rgba(37, 99, 235, 0.14));
    }

    .rr-inicio-event-call__launch-floaters {
        position: absolute;
        inset: 0;
        z-index: 4;
        pointer-events: none;
    }

    .rr-inicio-event-call__mobile-badges {
        display: none;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__badges {
        display: none;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__launch-floaters {
        display: none;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__mobile-badges {
        position: absolute;
        inset: 0;
        z-index: 6;
        display: block;
        pointer-events: none;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__mobile-badge {
        position: absolute;
        --rr-inicio-mobile-badge-transform: translate3d(0, 0, 0);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08));
        color: #fff7ed;
        font-size: 0.6rem;
        font-weight: 900;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        box-shadow: 0 14px 24px rgba(3, 7, 18, 0.22);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transform: var(--rr-inicio-mobile-badge-transform);
        animation: rrInicioLaunchMobileBadgeFloat 4.8s ease-in-out infinite;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__mobile-badge i {
        color: #f97316;
        font-size: 0.78rem;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__mobile-badge--one {
        top: 8px;
        left: 12px;
        animation-delay: 0s;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__mobile-badge--four {
        top: 54px;
        left: 18px;
        animation-delay: 0.65s;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__mobile-badge--five {
        top: 54px;
        right: 18px;
        animation-delay: 1.05s;
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__mobile-badge--three {
        top: 100px;
        left: 50%;
        --rr-inicio-mobile-badge-transform: translate3d(-50%, 0, 0);
        animation-delay: 1.45s;
    }

    .rr-inicio-event-call__launch-floater {
        position: absolute;
        display: grid;
        gap: 0.24rem;
        min-width: 166px;
        padding: 0.78rem 0.92rem;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08));
        box-shadow: 0 18px 32px rgba(3, 7, 18, 0.22);
        color: #eff6ff;
        backdrop-filter: blur(16px);
    }

    .rr-inicio-event-call__launch-floater i {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.12rem;
        border-radius: 999px;
        background: rgba(249, 115, 22, 0.12);
        color: #f97316;
    }

    .rr-inicio-event-call__launch-floater strong {
        display: block;
        font-size: 0.98rem;
        line-height: 1.05;
        letter-spacing: -0.03em;
    }

    .rr-inicio-event-call__launch-floater span {
        display: block;
        color: rgba(239, 246, 255, 0.78);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.11em;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .rr-inicio-event-call__launch-floater--one {
        top: 4px;
        left: 10%;
        transform: rotate(-7deg);
        animation: rrInicioLaunchFloater 6.4s ease-in-out infinite;
    }

    .rr-inicio-event-call__launch-floater--two {
        top: 26px;
        right: 8%;
        transform: rotate(6deg);
        animation: rrInicioLaunchFloater 7.1s ease-in-out infinite reverse;
    }

    .rr-inicio-event-call__launch-floater--three {
        right: 14%;
        bottom: 18px;
        transform: rotate(-4deg);
        animation: rrInicioLaunchFloater 6.8s ease-in-out infinite;
    }

    body.light .rr-inicio-event-call__mobile-badge {
        color: #7c2d12;
        border-color: rgba(194, 65, 12, 0.16);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 247, 237, 0.88));
        box-shadow: 0 14px 24px rgba(148, 163, 184, 0.18);
    }

    body.light .rr-inicio-event-call__launch-kicker {
        color: #9a3412;
        border-color: rgba(194, 65, 12, 0.16);
        background: linear-gradient(135deg, rgba(255, 237, 213, 0.94), rgba(219, 234, 254, 0.92));
    }

    body.light .rr-inicio-event-call__launch-floater {
        color: #1f2937;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(255, 247, 237, 0.84));
        border-color: rgba(194, 65, 12, 0.14);
        box-shadow: 0 16px 30px rgba(148, 163, 184, 0.18);
    }

    body.light .rr-inicio-event-call__launch-floater span {
        color: #92400e;
    }

    body.light .rr-inicio-event-call__timer-reminder {
        border-color: rgba(30, 64, 175, 0.16);
        background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(255,247,237,0.88));
        color: #1e3a8a;
        box-shadow: 0 12px 22px rgba(148, 163, 184, 0.16), inset 0 1px 0 rgba(255,255,255,0.88);
    }

    body.light .rr-inicio-event-call__timer-reminder.is-active {
        border-color: rgba(239, 68, 68, 0.28);
        background: linear-gradient(180deg, rgba(254,242,242,0.98), rgba(254,226,226,0.96));
        color: #dc2626;
        box-shadow:
            0 0 0 1px rgba(239, 68, 68, 0.16),
            0 12px 22px rgba(239, 68, 68, 0.16),
            inset 0 1px 0 rgba(255,255,255,0.92);
    }

    body.light .rr-inicio-reminder-modal__card {
        border-color: rgba(234, 88, 12, 0.16);
        background:
            radial-gradient(circle at top left, rgba(249, 115, 22, 0.1), transparent 34%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 28%),
            linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,247,237,0.96));
        color: #0f172a;
        box-shadow: 0 28px 54px rgba(120, 62, 24, 0.16);
    }

    body.light .rr-inicio-reminder-modal__title,
    body.light .rr-inicio-reminder-modal__btn--ghost,
    body.light .rr-inicio-reminder-modal__close {
        color: #0f172a;
    }

    body.light .rr-inicio-reminder-modal__text,
    body.light .rr-inicio-reminder-modal__field label {
        color: #334155;
    }

    body.light .rr-inicio-reminder-modal__field input {
        border-color: rgba(30, 64, 175, 0.12);
        background: rgba(255,255,255,0.92);
        color: #0f172a;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.92);
    }

    body.light .rr-inicio-reminder-modal__field input::placeholder {
        color: #64748b;
    }

    body.light .rr-inicio-reminder-modal__hint {
        color: #1e3a8a;
    }

    body.light .rr-inicio-reminder-modal__confirm-title {
        color: #0f172a;
    }

    body.light .rr-inicio-reminder-modal__success-title {
        color: #166534;
    }

    body.light .rr-inicio-reminder-modal__success-text {
        color: #1e3a8a;
        font-weight: 700;
    }

    body.light .rr-inicio-reminder-modal__btn--ghost,
    body.light .rr-inicio-reminder-modal__close {
        border-color: rgba(30, 64, 175, 0.12);
        background: rgba(255,255,255,0.8);
    }

    .rr-inicio-event-call__timer {
        position: absolute;
        left: 50%;
        bottom: 16px;
        transform: translateX(-50%);
        z-index: 8;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-width: 186px;
        padding: 10px 18px;
        border-radius: 18px;
        text-align: center;
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.96) 0%, rgba(217, 119, 6, 0.92) 100%);
        color: #fff7ed;
        box-shadow:
            0 18px 26px rgba(124, 45, 18, 0.34),
            inset 0 1px 0 rgba(255, 255, 255, 0.28);
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__timer {
        bottom: 68px;
    }

    .rr-inicio-event-call__timer-label {
        font-size: 0.56rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        opacity: 0.95;
    }

    .rr-inicio-event-call__timer-value {
        font-size: 1.12rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        line-height: 1;
        white-space: nowrap;
    }

    .rr-inicio-event-call__timer-main {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .rr-inicio-event-call__timer-reminder {
        width: 48px;
        height: 48px;
        border: 1px solid rgba(255, 247, 237, 0.26);
        border-radius: 16px;
        background: rgba(120, 53, 15, 0.18);
        color: #fff7ed;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 2;
        pointer-events: auto;
        touch-action: manipulation;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, opacity .18s ease;
    }

    .rr-inicio-event-call__timer-reminder:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 18px rgba(124, 45, 18, 0.22), inset 0 1px 0 rgba(255,255,255,0.22);
    }

    .rr-inicio-event-call__timer-reminder.is-active {
        border-color: rgba(248, 113, 113, 0.34);
        background: linear-gradient(180deg, rgba(127, 29, 29, 0.44), rgba(69, 10, 10, 0.32));
        color: #fecaca;
        box-shadow:
            0 0 0 1px rgba(248, 113, 113, 0.22),
            0 14px 22px rgba(127, 29, 29, 0.22),
            inset 0 1px 0 rgba(255,255,255,0.18);
        animation: rrReminderBellPulse 2.1s ease-in-out infinite;
    }

    .rr-inicio-event-call__timer-reminder.is-loading {
        opacity: 0.78;
        transform: scale(0.96);
        pointer-events: none;
        box-shadow:
            0 0 0 1px rgba(245, 158, 11, 0.18),
            0 10px 18px rgba(124, 45, 18, 0.18),
            inset 0 1px 0 rgba(255,255,255,0.18);
    }

    .rr-inicio-event-call__timer-reminder[hidden] {
        display: none !important;
    }

    .rr-inicio-event-call__mobile-selector-row {
        position: absolute;
        left: 50%;
        bottom: 14px;
        transform: translateX(-50%);
        z-index: 9;
        width: min(92%, 340px);
        display: flex;
        justify-content: center;
        pointer-events: auto;
    }

    .rr-inicio-event-call__mobile-selector {
        position: relative;
        width: min(100%, 320px);
    }

    .rr-inicio-event-call__mobile-selector-trigger {
        width: 100%;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid rgba(74, 222, 128, 0.34);
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.22), rgba(22, 163, 74, 0.16));
        color: #ecfdf5;
        font-size: 0.66rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        box-shadow: 0 14px 24px rgba(21, 128, 61, 0.24);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .rr-inicio-event-call__mobile-selector-trigger i {
        color: #bbf7d0;
        font-size: 0.8rem;
    }

    .rr-inicio-event-call__mobile-selector-trigger[disabled],
    .rr-inicio-event-call__mobile-selector-trigger[aria-disabled="true"] {
        opacity: 0.62;
        cursor: not-allowed;
    }

    .rr-inicio-event-call__mobile-selector-chevron {
        margin-left: auto;
        transition: transform .2s ease;
    }

    .rr-inicio-event-call__mobile-selector.is-open .rr-inicio-event-call__mobile-selector-chevron {
        transform: rotate(180deg);
    }

    .rr-inicio-event-call__mobile-selector-menu {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        width: 100%;
        display: grid;
        gap: 6px;
        padding: 8px;
        border-radius: 14px;
        border: 1px solid rgba(74, 222, 128, 0.28);
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(6, 12, 24, 0.98));
        box-shadow: 0 20px 32px rgba(2, 6, 23, 0.36);
        max-height: 220px;
        overflow-y: auto;
        z-index: 2147483646;
    }

    .rr-inicio-event-call__mobile-selector-menu[hidden] {
        display: none !important;
    }

    .rr-inicio-event-call__mobile-selector-menu:empty {
        display: none !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .rr-inicio-event-call__mobile-selector-menu.is-fixed {
        position: fixed;
        top: var(--rr-bolao-modalidade-menu-top, 50%);
        left: var(--rr-bolao-modalidade-menu-left, 50%);
        width: var(--rr-bolao-modalidade-menu-width, min(92vw, 320px));
    }

    .rr-inicio-event-call__mobile-selector-option {
        width: 100%;
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 0 10px;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.26);
        background: rgba(15, 23, 42, 0.68);
        color: #f8fafc;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .rr-inicio-event-call__mobile-selector-option.is-active {
        border-color: rgba(74, 222, 128, 0.42);
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.28), rgba(21, 128, 61, 0.22));
        color: #ecfdf5;
    }

    .rr-inicio-reminder-modal {
        position: fixed;
        inset: 0;
        z-index: 2147483400;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        min-height: 100dvh;
        overflow-y: auto;
        overscroll-behavior: contain;
        background: rgba(15, 23, 42, 0.66);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .rr-inicio-reminder-modal[hidden] {
        display: none !important;
    }

    .rr-inicio-reminder-modal.is-open {
        display: flex;
    }

    .rr-inicio-reminder-modal__card {
        width: min(100%, 440px);
        margin: auto;
        max-height: calc(100dvh - 36px);
        border-radius: 26px;
        border: 1px solid rgba(255, 237, 213, 0.18);
        background:
            radial-gradient(circle at top left, rgba(249, 115, 22, 0.12), transparent 34%),
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.1), transparent 28%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(17, 24, 39, 0.96));
        box-shadow: 0 30px 60px rgba(2, 6, 23, 0.42);
        color: #eff6ff;
        overflow: hidden;
    }

    body.rr-reminder-body-lock {
        overflow: hidden;
        overscroll-behavior: contain;
    }

    .rr-inicio-reminder-modal__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 22px 12px;
    }

    .rr-inicio-reminder-modal__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: #fdba74;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .rr-inicio-reminder-modal__title {
        margin: 0;
        color: #fff7ed;
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1.1;
    }

    .rr-inicio-reminder-modal__text {
        margin: 8px 0 0;
        color: #cbd5e1;
        font-size: 0.96rem;
        line-height: 1.65;
    }

    .rr-inicio-reminder-modal__close {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.08);
        color: #fff7ed;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .rr-inicio-reminder-modal__body {
        padding: 0 22px 22px;
    }

    .rr-inicio-reminder-modal__stack {
        display: grid;
        gap: 12px;
    }

    .rr-inicio-reminder-modal__field {
        display: grid;
        gap: 8px;
    }

    .rr-inicio-reminder-modal__field label {
        color: #cbd5e1;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .rr-inicio-reminder-modal__field input {
        width: 100%;
        min-height: 50px;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.08);
        color: #fff7ed;
        padding: 0 16px;
        font-size: 0.98rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
    }

    .rr-inicio-reminder-modal__field input::placeholder {
        color: rgba(226, 232, 240, 0.68);
    }

    .rr-inicio-reminder-modal__confirm {
        display: grid;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.42), rgba(15, 23, 42, 0.28));
    }

    .rr-inicio-reminder-modal__confirm-title {
        margin: 0;
        color: #f8fafc;
        font-size: 1rem;
        line-height: 1.2;
        font-weight: 900;
        text-align: center;
    }

    .rr-inicio-reminder-modal__hint,
    .rr-inicio-reminder-modal__status {
        margin: 2px 0 0;
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .rr-inicio-reminder-modal__hint {
        color: #93c5fd;
    }

    .rr-inicio-reminder-modal__status {
        color: #fde68a;
        min-height: 1.55em;
    }

    .rr-inicio-reminder-modal__status.is-error {
        color: #fca5a5;
    }

    .rr-inicio-reminder-modal__status.is-success {
        color: #86efac;
    }

    .rr-inicio-reminder-modal__success {
        display: grid;
        justify-items: center;
        gap: 14px;
        padding: 18px 6px 4px;
        text-align: center;
    }

    .rr-inicio-reminder-modal__success-visual {
        position: relative;
        width: 108px;
        height: 108px;
        display: grid;
        place-items: center;
    }

    .rr-inicio-reminder-modal__success-ring,
    .rr-inicio-reminder-modal__success-badge {
        border-radius: 999px;
    }

    .rr-inicio-reminder-modal__success-ring {
        position: absolute;
        inset: 0;
        border: 1px solid rgba(74, 222, 128, 0.24);
        animation: rrReminderSuccessRing 1.8s ease-in-out infinite;
    }

    .rr-inicio-reminder-modal__success-ring--two {
        inset: 12px;
        animation-delay: .14s;
    }

    .rr-inicio-reminder-modal__success-badge {
        position: relative;
        z-index: 1;
        width: 72px;
        height: 72px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, #22c55e, #15803d);
        color: #ecfdf5;
        box-shadow:
            0 18px 30px rgba(21, 128, 61, 0.28),
            inset 0 1px 0 rgba(255,255,255,0.22);
        animation: rrReminderSuccessBadge .58s cubic-bezier(.22, 1.22, .36, 1) both;
    }

    .rr-inicio-reminder-modal__success-badge i {
        font-size: 1.6rem;
    }

    .rr-inicio-reminder-modal__success-title {
        color: #f8fafc;
        font-size: 1.12rem;
        font-weight: 900;
        line-height: 1.15;
    }

    .rr-inicio-reminder-modal__success-text {
        margin: 0;
        color: #cbd5e1;
        font-size: 0.92rem;
        line-height: 1.6;
        max-width: 29ch;
    }

    .rr-inicio-reminder-modal__actions {
        display: flex;
        gap: 10px;
        margin-top: 18px;
    }

    .rr-inicio-reminder-modal__btn {
        min-height: 50px;
        border-radius: 16px;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 0.92rem;
        font-weight: 900;
        letter-spacing: 0.04em;
    }

    .rr-inicio-reminder-modal__btn--ghost {
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.08);
        color: #fff7ed;
    }

    .rr-inicio-reminder-modal__btn--primary {
        flex: 1 1 auto;
        border: 1px solid rgba(253, 186, 116, 0.24);
        background: linear-gradient(135deg, #f59e0b, #f97316);
        color: #fff7ed;
        box-shadow: 0 16px 26px rgba(194, 65, 12, 0.24);
    }

    @media (max-width: 768px) {
        .rr-inicio-reminder-modal {
            align-items: flex-end;
            justify-content: stretch;
            padding: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.12), rgba(15, 23, 42, 0.84));
        }

        .rr-inicio-reminder-modal__card {
            width: 100%;
            max-width: none;
            max-height: min(82dvh, 640px);
            margin: 0;
            margin-top: auto;
            align-self: stretch;
            border-radius: 28px 28px 0 0;
            border-left: 0;
            border-right: 0;
            border-bottom: 0;
            box-shadow:
                0 -18px 44px rgba(2, 6, 23, 0.42),
                inset 0 1px 0 rgba(255,255,255,0.06);
            animation: rrReminderSheetIn .22s ease-out;
        }

        .rr-inicio-reminder-modal__head {
            position: relative;
            padding: 24px 18px 10px;
            gap: 12px;
        }

        .rr-inicio-reminder-modal__head::before {
            content: "";
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 56px;
            height: 5px;
            border-radius: 999px;
            background: rgba(226, 232, 240, 0.24);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
        }

        .rr-inicio-reminder-modal__title {
            font-size: 1.22rem;
        }

        .rr-inicio-reminder-modal__text {
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .rr-inicio-reminder-modal__body {
            padding: 0 18px 20px;
        }

        .rr-inicio-reminder-modal__confirm {
            padding: 13px 14px;
            border-radius: 18px;
        }

        .rr-inicio-reminder-modal__confirm-title {
            font-size: 0.96rem;
        }

        .rr-inicio-reminder-modal__hint,
        .rr-inicio-reminder-modal__status {
            font-size: 0.84rem;
        }

        .rr-inicio-reminder-modal__actions {
            display: grid;
            grid-template-columns: minmax(0, 92px) minmax(0, 1fr);
            gap: 10px;
        }

        .rr-inicio-reminder-modal__btn {
            min-height: 52px;
            border-radius: 16px;
            font-size: 0.95rem;
        }
    }

    @keyframes rrReminderBellPulse {
        0%, 100% {
            transform: translateY(0);
            box-shadow:
                0 0 0 1px rgba(74, 222, 128, 0.22),
                0 14px 22px rgba(21, 128, 61, 0.22),
                inset 0 1px 0 rgba(255,255,255,0.18);
        }
        50% {
            transform: translateY(-1px);
            box-shadow:
                0 0 0 4px rgba(74, 222, 128, 0.08),
                0 18px 28px rgba(21, 128, 61, 0.28),
                inset 0 1px 0 rgba(255,255,255,0.18);
        }
    }

    @keyframes rrReminderSuccessRing {
        0%, 100% {
            transform: scale(1);
            opacity: .72;
        }
        50% {
            transform: scale(1.06);
            opacity: .28;
        }
    }

    @keyframes rrReminderSuccessBadge {
        0% {
            transform: scale(.72);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes rrReminderSheetIn {
        from {
            transform: translateY(24px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .rr-inicio-event-call--launch .rr-inicio-event-call__timer {
        min-width: 224px;
        padding: 12px 20px;
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(245, 158, 11, 0.98) 0%, rgba(234, 88, 12, 0.94) 100%);
        box-shadow:
            0 22px 32px rgba(124, 45, 18, 0.42),
            inset 0 1px 0 rgba(255, 255, 255, 0.28),
            0 0 36px rgba(245, 158, 11, 0.18);
        animation: rrInicioLaunchTimerPulse 2.8s ease-in-out infinite;
    }

    @media (max-width: 767px) {
        .rr-inicio-event-call {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            padding: 14px 12px 16px;
            min-height: 236px;
            max-height: none;
            border-radius: 22px;
        }

        .rr-inicio-event-call--launch {
            min-height: 0;
            padding: 14px 12px 16px;
        }

        .rr-inicio-layout--bolao-launch .rr-inicio-submenu__head,
        .rr-inicio-layout--bolao-launch .rr-inicio-submenu__filters,
        .rr-inicio-layout--bolao-launch .rr-inicio-event-call__badges,
        .rr-inicio-layout--bolao-launch .rr-inicio-event-call__logo-wrap,
        .rr-inicio-layout--bolao-launch .rr-inicio-event-call__timer,
        .rr-inicio-layout--bolao-launch .rr-inicio-event-call__mobile-selector-row,
        .rr-inicio-layout--bolao-launch .rr-mobile-search-shell,
        .rr-inicio-layout--bolao-launch .rr-mobile-quick-nav__actions,
        .rr-inicio-layout--bolao-launch .rr-bolao-launch-simple__actions,
        .rr-inicio-layout--bolao-launch .rr-bolao-launch-simple__cta-actions {
            justify-content: center;
            align-items: center;
        }

        .rr-inicio-layout--bolao-launch .rr-inicio-submenu__title,
        .rr-inicio-layout--bolao-launch .rr-inicio-event-call__timer-main {
            text-align: center;
        }

        .rr-inicio-event-call__badges {
            justify-content: center;
            gap: 6px;
        }

        .rr-inicio-event-call__badge {
            padding: 5px 10px;
            font-size: 0.58rem;
            letter-spacing: 0.1em;
        }

        .rr-inicio-event-call__launch-copy {
            display: none;
        }

        .rr-inicio-event-call__launch-kicker {
            padding: 6px 11px;
            font-size: 0.6rem;
            letter-spacing: 0.12em;
        }

        .rr-inicio-event-call__launch-title {
            font-size: clamp(1.15rem, 7vw, 1.6rem);
        }

        .rr-inicio-event-call__launch-note {
            font-size: 0.82rem;
            line-height: 1.45;
        }

        .rr-inicio-event-call__logo-wrap {
            display: flex;
            min-height: auto;
            padding-top: 4px;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap {
            min-height: 276px;
            padding-top: 18px;
        }

        .rr-inicio-event-call__logo-stack {
            gap: 10px;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-stack {
            gap: 0;
        }

        .rr-inicio-event-call__logo-wrap::before {
            width: 148px;
            height: 148px;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap::before {
            width: 244px;
            height: 244px;
            filter: blur(8px);
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap::after {
            display: none;
        }

        .rr-inicio-event-call__logo-frame {
            width: 98px;
            height: 98px;
            min-width: 98px;
            min-height: 98px;
            padding: 10px;
            border-radius: 24px;
            overflow: hidden;
        }

        .rr-inicio-event-call__logo-stack .rr-inicio-event-call__logo-frame {
            width: 90px;
            height: 90px;
            min-width: 90px;
            min-height: 90px;
            padding: 8px;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame,
        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-stack .rr-inicio-event-call__logo-frame {
            width: 104px;
            height: 104px;
            min-width: 104px;
            min-height: 104px;
            padding: 9px;
            border-radius: 28px;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame:first-child,
        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-stack .rr-inicio-event-call__logo-frame:first-child {
            width: auto;
            height: auto;
            min-width: 0;
            min-height: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame:nth-child(2) {
            margin-left: -28px;
            transform: translate(4px, 32px) rotate(7deg);
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame:first-child .rr-inicio-event-call__logo {
            width: min(82vw, 270px) !important;
            height: auto !important;
            max-width: min(82vw, 270px) !important;
            max-height: none !important;
        }

        .rr-inicio-event-call__logo {
            display: block;
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            max-height: none !important;
            margin: 0;
        }

        .rr-inicio-event-call__launch-floater {
            min-width: 118px;
            padding: 0.54rem 0.62rem;
            border-radius: 16px;
        }

        .rr-inicio-event-call__launch-floater strong {
            font-size: 0.76rem;
        }

        .rr-inicio-event-call__launch-floater span {
            font-size: 0.58rem;
            letter-spacing: 0.08em;
        }

        .rr-inicio-event-call__launch-floater i {
            width: 24px;
            height: 24px;
            margin-bottom: 0.06rem;
        }

        .rr-inicio-event-call__launch-floater--one {
            top: 0;
            left: 0;
        }

        .rr-inicio-event-call__launch-floater--two {
            top: 12px;
            right: -2px;
        }

        .rr-inicio-event-call__launch-floater--three {
            right: 4%;
            bottom: 8px;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__launch-floaters {
            display: none;
        }

        .rr-inicio-event-call__mobile-badges {
            position: absolute;
            top: 2px;
            left: 8px;
            right: 8px;
            z-index: 6;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: none;
        }

        .rr-inicio-event-call__mobile-badge {
            position: relative;
            --rr-inicio-mobile-badge-transform: translate3d(0, 0, 0);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 34px;
            padding: 0 12px;
            max-width: calc(50% - 6px);
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08));
            color: #fff7ed;
            font-size: 0.6rem;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            box-shadow: 0 14px 24px rgba(3, 7, 18, 0.22);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            animation: rrInicioLaunchMobileBadgeFloat 4.8s ease-in-out infinite;
        }

        .rr-inicio-event-call__mobile-badge i {
            color: #f97316;
            font-size: 0.78rem;
        }

        .rr-inicio-event-call__mobile-badge--one {
            top: auto;
            left: auto;
            animation-delay: 0s;
        }

        .rr-inicio-event-call__mobile-badge--two {
            display: none;
        }

        .rr-inicio-event-call__mobile-badge--three {
            display: none;
        }

        .rr-inicio-event-call__timer {
            position: relative;
            left: auto;
            bottom: auto;
            transform: none;
            min-width: 174px;
            margin-top: 2px;
            padding: 10px 14px;
            border-radius: 16px;
            flex-direction: row;
            gap: 10px;
        }

        .rr-inicio-event-call__timer-label {
            font-size: 0.5rem;
        }

        .rr-inicio-event-call__timer-value {
            font-size: 1rem;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__timer {
            min-width: 188px;
            width: auto;
            max-width: 100%;
            margin-top: 10px;
            padding: 10px 14px;
            border-radius: 18px;
        }

        .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap {
            padding-top: 56px;
        }

        .rr-inicio-event-call__timer-reminder {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            flex: 0 0 42px;
        }

        .rr-inicio-reminder-modal {
            padding: 12px;
            align-items: flex-start;
        }

        .rr-inicio-reminder-modal__card {
            margin-top: auto;
            margin-bottom: auto;
            max-height: calc(100dvh - 24px);
        }

        .rr-inicio-reminder-modal__head,
        .rr-inicio-reminder-modal__body {
            padding-left: 16px;
            padding-right: 16px;
        }

        .rr-inicio-reminder-modal__actions {
            flex-direction: column-reverse;
        }

        .rr-inicio-reminder-modal__btn {
            width: 100%;
        }

    }

    @keyframes rrInicioEventBadgeFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-2px); }
    }

    @keyframes rrInicioEventDotPulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 247, 237, 0.42); }
        70% { box-shadow: 0 0 0 8px rgba(255, 247, 237, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 247, 237, 0); }
    }

    @keyframes rrInicioEventHalo {
        0%, 100% { transform: scale(0.98); opacity: 0.82; }
        50% { transform: scale(1.04); opacity: 1; }
    }

    @keyframes rrInicioLaunchLogoFloat {
        0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
        50% { transform: translate3d(0, -10px, 0) scale(1.02); }
    }

    @keyframes rrInicioLaunchRingDrift {
        0%, 100% { transform: translateX(-50%) scale(0.96) rotate(0deg); opacity: 0.72; }
        50% { transform: translateX(-50%) scale(1.04) rotate(7deg); opacity: 1; }
    }

    @keyframes rrInicioLaunchFloater {
        0%, 100% { transform: translate3d(0, 0, 0) rotate(var(--rr-inicio-launch-rotation, 0deg)); }
        50% { transform: translate3d(0, -10px, 0) rotate(var(--rr-inicio-launch-rotation, 0deg)); }
    }

    @keyframes rrInicioLaunchMobileBadgeFloat {
        0%, 100% { transform: var(--rr-inicio-mobile-badge-transform, translate3d(0, 0, 0)); }
        50% { transform: var(--rr-inicio-mobile-badge-transform, translate3d(0, 0, 0)) translate3d(0, -6px, 0); }
    }

    @keyframes rrInicioLaunchTimerPulse {
        0%, 100% { transform: translateX(-50%) scale(1); }
        50% { transform: translateX(-50%) scale(1.018); }
    }

    .rr-inicio-event-call__launch-floater--one { --rr-inicio-launch-rotation: -7deg; }
    .rr-inicio-event-call__launch-floater--two { --rr-inicio-launch-rotation: 6deg; }
    .rr-inicio-event-call__launch-floater--three { --rr-inicio-launch-rotation: -4deg; }

    @media (max-width: 767px) {
        @keyframes rrInicioLaunchTimerPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.018); }
        }
    }

    /* ---- Labels de seção ---- */
    .rr-side-panel__label {
        font-size: 0.95rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 2px 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .rr-side-panel__label i {
        color: #f59e0be6;
        font-size: 0.9rem;
    }

    .rr-side-panel__label.rr-side-panel__label--hero {
        gap: 8px;
    }

    .rr-side-panel__label.rr-side-panel__label--hero .rr-side-panel__label-text {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        line-height: 1.05;
    }

    .rr-side-panel__label.rr-side-panel__label--hero .rr-side-panel__label-title {
        font-size: 0.95rem;
        font-weight: 900;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #e2e8f0;
        text-shadow: 0 2px 12px rgba(249, 115, 22, 0.28);
    }

    .rr-side-panel__label.rr-side-panel__label--hero .rr-side-panel__label-title .is-highlight {
        color: #f59e0be6;
        text-shadow: 0 0 14px rgba(251, 146, 60, 0.55);
    }

    body.light .rr-side-panel__label.rr-side-panel__label--hero .rr-side-panel__label-title {
        color: #000;
        text-shadow: none;
    }

    .rr-side-panel__label.rr-side-panel__label--hero .rr-side-panel__label-subtitle {
        margin-top: 2px;
        font-size: 0.67rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, 0.92);
    }

    /* ---- Sub-row (scroll horizontal) ---- */
    .rr-side-panel__row {
        position: relative;
        overflow: visible;
        min-width: 0;
    }
    .rr-side-panel__row .rr-inicio-grid {
        flex-direction: row;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: visible;
        max-height: none;
        padding: 16px 8px 20px;
        gap: 10px;
        scrollbar-width: none;
        cursor: grab;
        -webkit-overflow-scrolling: touch;
    }
    .rr-side-panel__row .rr-inicio-grid::-webkit-scrollbar { display: none; }

    /* Hide default grid-wrap overlays inside rows */
    .rr-side-panel__row .rr-inicio-grid-wrap::after,
    .rr-side-panel__row .rr-inicio-grid-wrap::before { display: none !important; }

    /* Fade + seta na borda direita de cada row */
    .rr-side-panel__row::after {
        display: none;
    }
    .rr-side-panel__row::before {
        content: '➜';
        position: absolute; right: 10px; top: 50%;
        transform: translateY(-50%);
        font-size: 0.9rem; font-weight: 700;
        color: rgba(249,115,22,0.8);
        text-shadow: 0 0 5px rgba(249,115,22,0.35);
        pointer-events: none; z-index: 6;
    }

    /* ---- Bolão cards compactos ---- */
    .rr-side-panel__row .rr-bolao-card   { flex-shrink:0; min-width:290px; width:290px; min-height:0; padding-top:74px; margin-top:74px; }
    .rr-side-panel__row .rr-bolao-header  { top:-78px; width:138px; height:138px; }
    .rr-side-panel__row .rr-bolao-content { padding:10px; }
    .rr-side-panel__row .rr-bolao-title   { font-size:0.84rem; margin-bottom:6px; height:38px; }
    .rr-side-panel__row .rr-bolao-meta    { margin-bottom:6px; padding:4px 0; }
    .rr-side-panel__row .rr-bolao-meta-item { font-size:0.64rem; gap:4px; margin-bottom:2px; }
    .rr-side-panel__row .rr-bolao-prize       { padding:6px; margin-bottom:6px; }
    .rr-side-panel__row .rr-bolao-prize-label { font-size:0.58rem; }
    .rr-side-panel__row .rr-bolao-prize-value { font-size:1.12rem; }
    .rr-side-panel__row .rr-bolao-info    { font-size:0.62rem; margin-bottom:6px; }
    .rr-side-panel__row .rr-bolao-btn     { padding:6px; font-size:0.68rem; gap:5px; }

    /* ---- X1 cards compactos ---- */
    .rr-side-panel__row .rr-x1room-card          { flex-shrink:0; min-width:200px; width:200px; min-height:0; }
    .rr-side-panel__row .rr-x1room-header         { min-height:44px; padding:8px 8px 4px; gap:6px; }
    .rr-side-panel__row .rr-x1room-player-avatar  { width:34px; height:34px; }
    .rr-side-panel__row .rr-x1room-player-name    { font-size:0.6rem; max-width:72px; gap:2px; }
    .rr-side-panel__row .rr-x1room-vs             { font-size:0.7rem; }
    .rr-side-panel__row .rr-x1room-content        { padding:6px 8px 8px; }
    .rr-side-panel__row .rr-x1room-meta           { margin-bottom:5px; padding:3px 0; }
    .rr-side-panel__row .rr-x1room-meta-item      { font-size:0.55rem; }
    .rr-side-panel__row .rr-x1room-prize          { padding:5px; margin-bottom:5px; }
    .rr-side-panel__row .rr-x1room-prize-label    { font-size:0.52rem; }
    .rr-side-panel__row .rr-x1room-prize-value    { font-size:0.9rem; }
    .rr-side-panel__row .rr-x1room-info           { font-size:0.55rem; margin-bottom:5px; }
    .rr-side-panel__row .rr-x1room-btn            { padding:5px; font-size:0.6rem; }

    /* ---- Bottom panel: competidores ---- */
    .hub-top__grid .rr-side-panel--bottom .rr-inicio-shell {
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }
    .hub-top__grid .rr-side-panel--bottom .rr-inicio-shell .card-body {
        padding: 0 !important;
    }
    .hub-top__grid .rr-side-panel--bottom .rr-competitor-desktop-stack {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .hub-top__grid .rr-side-panel--bottom .rr-inicio-grid-wrap {
        position: relative;
        overflow: visible;
        margin-top: 0;
        z-index: 2;
    }
    .hub-top__grid .rr-side-panel--bottom .rr-inicio-grid {
        flex-direction: row;
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: visible;
        max-height: none;
        padding: 10px 8px 20px;
        gap: 10px;
        scrollbar-width: none;
        cursor: grab;
        -webkit-overflow-scrolling: touch;
    }
    .hub-top__grid .rr-side-panel--bottom .rr-inicio-grid::-webkit-scrollbar { display: none; }
    .hub-top__grid .rr-side-panel--bottom .rr-neuro-wrapper {
        flex-shrink: 0;
        min-width: 170px;
        width: 170px;
    }

    /* Fade + seta na borda direita dos competidores */
    .hub-top__grid .rr-side-panel--bottom .rr-inicio-grid-wrap::after {
        content: '›';
        position: absolute;
        right: 16px;
        top: 26px;
        font-size: 0.65rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        color: rgba(251, 146, 60, 0.88);
        text-shadow: 0 0 8px rgba(249,115,22,0.45);
        pointer-events: none;
        z-index: 6;
        animation: rr-scroll-hint-dots 1.8s ease-in-out infinite;
    }
    .hub-top__grid .rr-side-panel--bottom .rr-inicio-grid-wrap::before {
        content: 'DESLIZE';
        position: absolute;
        right: 10px;
        top: 8px;
        padding: 4px 9px;
        border-radius: 999px;
        border: 1px solid rgba(249,115,22,0.62);
        background: linear-gradient(135deg, rgba(17,24,39,0.84), rgba(30,41,59,0.84));
        font-size: 0.56rem;
        font-weight: 900;
        letter-spacing: 0.1em;
        color: rgba(251, 191, 36, 0.95);
        text-shadow: 0 0 6px rgba(249,115,22,0.38);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.32);
        pointer-events: none;
        z-index: 6;
        animation: rr-scroll-hint-breath 2s ease-in-out infinite;
    }

    /* Esconde seções na posição original quando reparentadas */
    #rrInicioBolaos.rr-reparented,
    #rrInicioX1Rooms.rr-reparented,
    #rrInicioSection.rr-reparented {
        display: none;
    }
}

@media (min-width: 1400px) {
    .rr-inicio-grid {
        gap: 20px;
    }
}

/* ============================================
    X1 RESULT MODAL
   ============================================ */
.rr-x1modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 2147483647;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: x1modalFadeIn 0.25s ease;
}
.rr-x1modal-overlay.active {
    display: flex;
}
@keyframes x1modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.rr-x1modal {
    background: linear-gradient(160deg, #111827, #0a0e17);
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.15);
    width: 100%;
    max-width: 380px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6),
                0 0 40px rgba(249, 115, 22, 0.15);
    animation: x1modalSlideUp 0.3s ease;
}
@keyframes x1modalSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* ============================================
   Draft Success Popout
   ============================================ */
.rr-draft-success-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 2147483647;
    padding: 16px;
    align-items: center;
    justify-content: center;
    background: rgba(2, 6, 23, 0.78);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    overscroll-behavior: contain;
}

.rr-draft-success-overlay[hidden] { display: none !important; }
.rr-draft-success-overlay.is-open { display: flex; animation: x1modalFadeIn 0.24s ease; }

.rr-draft-success-popout {
    position: relative;
    width: min(100%, 430px);
    overflow: hidden;
    border-radius: 28px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.2), transparent 34%),
        radial-gradient(circle at right center, rgba(37, 99, 235, 0.18), transparent 30%),
        linear-gradient(160deg, rgba(15, 23, 42, 0.98), rgba(2, 6, 23, 0.96));
    box-shadow:
        0 28px 80px rgba(0, 0, 0, 0.52),
        0 0 44px rgba(249, 115, 22, 0.16);
    animation: rrDraftSuccessPopIn 0.34s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.rr-draft-success-popout::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.08) 26%, transparent 48%);
    transform: translateX(-120%);
    animation: rrDraftRankingSheen 6.4s linear infinite;
}

.rr-draft-success-popout__close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: rgba(248, 250, 252, 0.88);
    font-size: 1.3rem;
    line-height: 1;
    z-index: 2;
}

.rr-draft-success-popout__hero {
    position: relative;
    display: grid;
    justify-items: center;
    gap: 14px;
    padding: 28px 24px 18px;
    text-align: center;
}

.rr-draft-success-popout__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.48rem;
    min-height: 36px;
    padding: 0 1rem;
    border-radius: 999px;
    border: 1px solid rgba(34, 197, 94, 0.26);
    background: linear-gradient(180deg, rgba(34, 197, 94, 0.18), rgba(14, 116, 144, 0.12));
    color: #dcfce7;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    box-shadow: 0 12px 24px rgba(2, 6, 23, 0.18);
}

.rr-draft-success-popout__badge i { color: #86efac; }

.rr-draft-success-popout__orb {
    position: relative;
    width: 114px;
    height: 114px;
    border-radius: 32px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(134, 239, 172, 0.28);
    background: linear-gradient(160deg, rgba(34, 197, 94, 0.2), rgba(37, 99, 235, 0.16));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 20px 42px rgba(2, 6, 23, 0.26);
    isolation: isolate;
}

.rr-draft-success-popout__orb::before,
.rr-draft-success-popout__orb::after {
    content: "";
    position: absolute;
    inset: -12px;
    border-radius: 38px;
    border: 1px solid rgba(134, 239, 172, 0.22);
    opacity: 0.66;
    animation: rrDraftSuccessRingPulse 2.8s ease-in-out infinite;
}

.rr-draft-success-popout__orb::after {
    inset: -22px;
    border-color: rgba(59, 130, 246, 0.18);
    animation-delay: 0.32s;
}

.rr-draft-success-popout__orb i {
    position: relative;
    z-index: 1;
    font-size: 2.7rem;
    color: #dcfce7;
    filter: drop-shadow(0 8px 22px rgba(34, 197, 94, 0.32));
    animation: rrDraftSuccessCheckPulse 1.8s ease-in-out infinite;
}

.rr-draft-success-popout__title {
    margin: 0;
    color: #f8fafc;
    font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    line-height: 0.96;
}

.rr-draft-success-popout__text {
    margin: 0;
    max-width: 320px;
    color: rgba(226, 232, 240, 0.8);
    font-size: 0.96rem;
    line-height: 1.5;
}

.rr-draft-success-popout__meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    padding: 0 24px 22px;
}

.rr-draft-success-popout__meta-item {
    display: grid;
    gap: 6px;
    padding: 14px 16px;
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(15, 23, 42, 0.34));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.rr-draft-success-popout__meta-label {
    color: rgba(148, 163, 184, 0.76);
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.rr-draft-success-popout__meta-value {
    color: #f8fafc;
    font-size: 1rem;
    font-weight: 900;
    line-height: 1.2;
}

.rr-draft-success-popout__footer { padding: 0 24px 24px; }

.rr-draft-success-popout__action {
    position: relative;
    width: 100%;
    min-height: 52px;
    border-radius: 18px;
    border: 1px solid rgba(96, 165, 250, 0.22);
    background: linear-gradient(135deg, #f59e0be6, #2563eb);
    color: #fff;
    font-size: 0.88rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow: 0 18px 34px rgba(37, 99, 235, 0.22);
    overflow: hidden;
}

.rr-draft-success-popout__action::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.24) 30%, transparent 58%);
    transform: translateX(-120%);
    animation: rrDraftRankingButtonShine 4.6s linear infinite;
}

.rr-draft-success-popout__action > * { position: relative; z-index: 1; }

@keyframes rrDraftSuccessPopIn {
    from { opacity: 0; transform: translateY(22px) scale(0.94); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes rrDraftSuccessRingPulse {
    0%, 100% { transform: scale(0.98); opacity: 0.38; }
    50% { transform: scale(1.04); opacity: 0.82; }
}

@keyframes rrDraftSuccessCheckPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
}

body.light .rr-draft-success-overlay { background: rgba(15, 23, 42, 0.24); }

body.light .rr-draft-success-popout {
    border-color: rgba(15, 23, 42, 0.08);
    background:
        radial-gradient(circle at top left, rgba(34, 197, 94, 0.14), transparent 34%),
        radial-gradient(circle at right center, rgba(37, 99, 235, 0.12), transparent 28%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
    box-shadow:
        0 28px 70px rgba(15, 23, 42, 0.16),
        0 0 32px rgba(37, 99, 235, 0.08);
}

body.light .rr-draft-success-popout__close {
    background: rgba(15, 23, 42, 0.06);
    color: #0f172a;
}

body.light .rr-draft-success-popout__badge {
    border-color: rgba(22, 163, 74, 0.18);
    background: linear-gradient(180deg, rgba(240, 253, 244, 0.96), rgba(239, 246, 255, 0.9));
    color: #166534;
}

body.light .rr-draft-success-popout__badge i { color: #16a34a; }

body.light .rr-draft-success-popout__orb {
    border-color: rgba(22, 163, 74, 0.16);
    background: linear-gradient(160deg, #111827, #1f2937);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 20px 38px rgba(15, 23, 42, 0.16);
}

body.light .rr-draft-success-popout__title,
body.light .rr-draft-success-popout__meta-value { color: #0f172a; }

body.light .rr-draft-success-popout__text,
body.light .rr-draft-success-popout__meta-label { color: #64748b; }

body.light .rr-draft-success-popout__meta-item {
    border-color: rgba(15, 23, 42, 0.08);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.92));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 12px 24px rgba(15, 23, 42, 0.06);
}

body.light .rr-draft-success-popout__action {
    border-color: rgba(15, 23, 42, 0.06);
    background: linear-gradient(135deg, #111827, #0f172a 55%, #1e293b 100%);
    box-shadow: 0 18px 34px rgba(15, 23, 42, 0.18);
}

@media (max-width: 640px) {
    .rr-draft-success-overlay {
        padding: 14px;
        align-items: center;
    }

    .rr-draft-success-popout {
        border-radius: 24px;
    }

    .rr-draft-success-popout__hero {
        padding: 24px 18px 14px;
    }

    .rr-draft-success-popout__orb {
        width: 96px;
        height: 96px;
        border-radius: 26px;
    }

    .rr-draft-success-popout__orb i { font-size: 2.2rem; }

    .rr-draft-success-popout__meta {
        grid-template-columns: 1fr;
        padding: 0 18px 18px;
    }

    .rr-draft-success-popout__footer { padding: 0 18px 18px; }
}

.rr-x1modal__close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(148, 163, 184, 0.15);
    border: none;
    color: rgba(248, 250, 252, 0.7);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    z-index: 2;
}
.rr-x1modal__close:hover {
    background: rgba(239, 68, 68, 0.3);
    color: #fff;
}

.rr-x1modal__header {
    padding: 20px 20px 10px;
    text-align: center;
}
.rr-x1modal__title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(148, 163, 184, 0.6);
    margin-bottom: 4px;
}
.rr-x1modal__room-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: rgba(248, 250, 252, 0.95);
}

/* VS Section */
.rr-x1modal__vs {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 16px 20px;
}
.rr-x1modal__player {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    flex: 1;
    min-width: 0;
    position: relative;
}
.rr-x1modal__player--winner {
    filter: none;
}
.rr-x1modal__player--loser {
    opacity: 0.45;
    filter: grayscale(0.6);
}

.rr-x1modal__avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    border: 2.5px solid rgba(148, 163, 184, 0.3);
    background: rgba(15, 23, 42, 0.6);
}
.rr-x1modal__player--winner .rr-x1modal__avatar {
    border-color: #f59e0be6;
    box-shadow: 0 0 18px rgba(251, 191, 36, 0.5),
                0 0 40px rgba(251, 191, 36, 0.2);
}
.rr-x1modal__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.rr-x1modal__avatar--logo img {
    object-fit: contain;
    padding: 4px;
}

.rr-x1modal__player-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: rgba(248, 250, 252, 0.9);
    display: flex;
    align-items: center;
    gap: 4px;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.rr-x1modal__player--winner .rr-x1modal__player-name {
    color: #f59e0be6;
}
.rr-x1modal__crown {
    color: #f59e0be6;
    font-size: 0.7rem;
}

/* Winner trophy badge */
.rr-x1modal__winner-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    color: #1a1f2e;
    font-size: 0.55rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 10px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 3px;
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.4);
}
.rr-x1modal__winner-badge i {
    font-size: 0.5rem;
}

.rr-x1modal__vs-text {
    font-size: 1.3rem;
    font-weight: 900;
    color: #ef4444;
    text-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
    flex-shrink: 0;
}

/* Details section */
.rr-x1modal__details {
    padding: 0 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.rr-x1modal__detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: rgba(30, 41, 59, 0.5);
    border-radius: 8px;
    border: 1px solid rgba(148, 163, 184, 0.08);
}
.rr-x1modal__detail-label {
    font-size: 0.72rem;
    color: rgba(148, 163, 184, 0.7);
    display: flex;
    align-items: center;
    gap: 6px;
}
.rr-x1modal__detail-label i {
    font-size: 0.65rem;
    color: rgba(148, 163, 184, 0.5);
    width: 14px;
    text-align: center;
}
.rr-x1modal__detail-value {
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(248, 250, 252, 0.9);
}
.rr-x1modal__detail-value--prize {
    color: #22c55e;
    font-size: 1rem;
    font-weight: 900;
}
.rr-x1modal__detail-value--status {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
}
.rr-x1modal__detail-value--open {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
}
.rr-x1modal__detail-value--in_progress {
    background: rgba(249, 115, 22, 0.15);
    color: #f59e0be6;
}
.rr-x1modal__detail-value--finished {
    background: rgba(148, 163, 184, 0.15);
    color: #94a3b8;
}

/* Reason */
.rr-x1modal__reason {
    text-align: center;
    padding: 8px 12px;
    margin: 0 20px 16px;
    background: rgba(251, 191, 36, 0.08);
    border: 1px solid rgba(251, 191, 36, 0.2);
    border-radius: 8px;
    font-size: 0.72rem;
    color: rgba(251, 191, 36, 0.9);
}
.rr-x1modal__reason i {
    margin-right: 4px;
}

/* Competitor picks section */
.rr-x1modal__competitors {
    padding: 0 20px 16px;
}
.rr-x1modal__comp-title {
    text-align: center;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(148, 163, 184, 0.5);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rr-x1modal__comp-title::before,
.rr-x1modal__comp-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(148, 163, 184, 0.15);
}
.rr-x1modal__comp-sides {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.rr-x1modal__comp-side {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.rr-x1modal__comp-side-label {
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(148, 163, 184, 0.45);
    margin-bottom: 2px;
}
.rr-x1modal__comp-vs {
    font-size: 0.85rem;
    font-weight: 900;
    color: #ef4444;
    text-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
    flex-shrink: 0;
    align-self: center;
    margin-top: 14px;
}

/* Solo competitor (1): single photo */
.rr-x1modal__comp-solo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.rr-x1modal__comp-photo {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(148, 163, 184, 0.25);
    background: rgba(15, 23, 42, 0.5);
}
.rr-x1modal__comp-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.rr-x1modal__comp-photo--fallback img {
    object-fit: contain;
    padding: 3px;
}
.rr-x1modal__comp-name {
    font-size: 0.68rem;
    font-weight: 600;
    color: rgba(248, 250, 252, 0.85);
    text-align: center;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Duo competitors (2): two photos side-by-side with mini VS */
.rr-x1modal__comp-duo {
    display: flex;
    align-items: center;
    gap: 6px;
}
.rr-x1modal__comp-duo-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
}
.rr-x1modal__comp-duo .rr-x1modal__comp-photo {
    width: 34px;
    height: 34px;
}
.rr-x1modal__comp-duo .rr-x1modal__comp-name {
    font-size: 0.6rem;
    max-width: 60px;
}
.rr-x1modal__comp-duo-sep {
    font-size: 0.55rem;
    font-weight: 700;
    color: rgba(148, 163, 184, 0.4);
}

/* Multi competitors (3+): names only */
.rr-x1modal__comp-list {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}
.rr-x1modal__comp-list-item {
    font-size: 0.62rem;
    font-weight: 600;
    color: rgba(248, 250, 252, 0.8);
    text-align: center;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rr-x1modal__comp-empty {
    font-size: 0.6rem;
    color: rgba(148, 163, 184, 0.35);
    font-style: italic;
}

/* Footer close btn */
.rr-x1modal__footer {
    padding: 0 20px 20px;
    text-align: center;
}
.rr-x1modal__close-btn {
    width: 100%;
    padding: 10px;
    background: rgba(148, 163, 184, 0.1);
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 8px;
    color: rgba(248, 250, 252, 0.7);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.rr-x1modal__close-btn:hover {
    background: rgba(148, 163, 184, 0.2);
    color: #fff;
}

/* ============================================
   Cards sem sombra e sem fundo (competidor/bolao/x1)
   ============================================ */
.rr-neuro-wrapper,
.rr-neuro-wrapper::before,
.rr-neuro-wrapper::after,
.rr-card-inner,
.rr-card-inside,
.rr-neuro-header,
.rr-neuro-content,
.rr-neuro-img-container,
.rr-bolao-card,
.rr-bolao-card::before,
.rr-bolao-card::after,
.rr-bolao-header,
.rr-bolao-content,
.rr-bolao-image,
.rr-x1room-card,
.rr-x1room-card::before,
.rr-x1room-card::after,
.rr-x1room-header,
.rr-x1room-content {
    background: transparent !important;
    background-image: none !important;
    box-shadow: none !important;
}

.rr-neuro-wrapper:hover .rr-neuro-img-container,
.rr-bolao-card:hover,
.rr-bolao-card:hover .rr-bolao-image,
.rr-x1room-card:hover {
    box-shadow: none !important;
}

/* Borda de linha nos cards de competidor por nivel */
.rr-neuro-wrapper .rr-card-inner {
    border: 2px solid var(--rr-level-ring, #22c55e) !important;
    border-radius: 12px !important;
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(120% 95% at 50% 0%, color-mix(in srgb, var(--rr-level-ring, #22c55e) 16%, transparent) 0%, transparent 64%),
        linear-gradient(165deg, rgba(14, 20, 36, 0.88), rgba(6, 10, 18, 0.92)) !important;
    box-shadow:
        0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 22%, transparent) inset,
        0 8px 20px color-mix(in srgb, var(--rr-level-ring, #22c55e) 20%, transparent),
        0 16px 28px rgba(2, 6, 23, 0.42) !important;
    transition: transform 0.22s ease, box-shadow 0.26s ease, border-color 0.22s ease;
    animation: rr-competitor-card-pulse 2.8s ease-in-out infinite;
}

.rr-neuro-wrapper .rr-card-inner::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(125deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 40%),
        radial-gradient(circle at 18% 14%, color-mix(in srgb, var(--rr-level-ring, #22c55e) 26%, transparent) 0%, transparent 58%);
    pointer-events: none;
}

.rr-neuro-wrapper:hover .rr-card-inner {
    transform: translateY(-3px);
    box-shadow:
        0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 32%, transparent) inset,
        0 0 16px color-mix(in srgb, var(--rr-level-ring, #22c55e) 42%, transparent),
        0 14px 30px rgba(2, 6, 23, 0.5) !important;
}

@keyframes rr-competitor-card-pulse {
    0%, 100% {
        box-shadow:
            0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 22%, transparent) inset,
            0 8px 20px color-mix(in srgb, var(--rr-level-ring, #22c55e) 20%, transparent),
            0 16px 28px rgba(2, 6, 23, 0.42);
    }
    50% {
        box-shadow:
            0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 32%, transparent) inset,
            0 0 14px color-mix(in srgb, var(--rr-level-ring, #22c55e) 34%, transparent),
            0 16px 30px rgba(2, 6, 23, 0.45);
    }
}

body.light .rr-neuro-wrapper .rr-card-inner {
    background:
        radial-gradient(120% 95% at 50% 0%, color-mix(in srgb, var(--rr-level-ring, #22c55e) 22%, transparent) 0%, transparent 64%),
        linear-gradient(165deg, rgba(255, 253, 249, 0.98), rgba(255, 246, 237, 0.98)) !important;
    box-shadow:
        0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 30%, transparent) inset,
        0 8px 18px color-mix(in srgb, var(--rr-level-ring, #22c55e) 24%, transparent),
        0 10px 20px rgba(124, 45, 18, 0.16) !important;
}

/* ==== Bolao/X1 cards: same premium punch as competitor cards ==== */
.rr-bolao-card,
.rr-x1room-card {
    position: relative;
    border-radius: 10px !important;
    overflow: hidden;
    background:
        radial-gradient(120% 95% at 50% 0%, color-mix(in srgb, var(--glow-tint, rgba(249, 115, 22, 0.35)) 30%, transparent) 0%, transparent 62%),
        linear-gradient(165deg, rgba(14, 20, 36, 0.9), rgba(6, 10, 18, 0.94)) !important;
    box-shadow:
        0 0 0 1px rgba(249, 115, 22, 0.2) inset,
        0 10px 24px color-mix(in srgb, var(--glow-tint, rgba(249, 115, 22, 0.35)) 45%, transparent),
        0 16px 30px rgba(2, 6, 23, 0.44) !important;
    transition: transform 0.22s ease, box-shadow 0.26s ease, border-color 0.22s ease;
}

.rr-bolao-card {
    overflow: visible !important;
}

.rr-bolao-header {
    top: -52px !important;
    width: 94px !important;
    height: 94px !important;
    z-index: 8 !important;
    overflow: visible !important;
}

.rr-bolao-image {
    border-radius: 12px !important;
    border: none !important;
    outline: none !important;
    background: transparent !important;
    box-shadow: none !important;
    filter: none !important;
    animation: rr-bolao-photo-float 2.8s ease-in-out infinite !important;
}

.rr-bolao-card:hover .rr-bolao-image,
.rr-side-panel__row .rr-bolao-image {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    filter: none !important;
}

.rr-bolao-card:hover,
.rr-x1room-card:hover {
    transform: translateY(-3px);
    box-shadow:
        0 0 0 1px rgba(249, 115, 22, 0.28) inset,
        0 0 16px color-mix(in srgb, var(--glow-tint, rgba(249, 115, 22, 0.35)) 70%, transparent),
        0 16px 32px rgba(2, 6, 23, 0.52) !important;
}

/* 3D buttons */
.rr-bolao-btn,
.rr-x1room-btn {
    position: relative;
    border-radius: 6px !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    background: linear-gradient(155deg, #f59e0be6 0%, #f59e0be6 45%, #ea580c 100%) !important;
    box-shadow:
        0 5px 0 #9a3412,
        0 10px 16px rgba(124, 45, 18, 0.38),
        inset 0 1px 0 rgba(255, 255, 255, 0.32) !important;
    transform: translateY(0);
    transition: transform 0.15s ease, box-shadow 0.22s ease, filter 0.22s ease !important;
}

.rr-bolao-btn::before,
.rr-x1room-btn::before {
    content: '';
    position: absolute;
    inset: 1px 1px auto 1px;
    height: 44%;
    border-radius: 5px;
    background: linear-gradient(to bottom, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0));
    pointer-events: none;
}

.rr-bolao-btn:hover,
.rr-x1room-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.05);
    box-shadow:
        0 7px 0 #9a3412,
        0 14px 22px rgba(124, 45, 18, 0.44),
        inset 0 1px 0 rgba(255, 255, 255, 0.38) !important;
}

.rr-bolao-btn:active,
.rr-x1room-btn:active {
    transform: translateY(3px);
    box-shadow:
        0 1px 0 #9a3412,
        0 6px 10px rgba(124, 45, 18, 0.32),
        inset 0 1px 0 rgba(255, 255, 255, 0.26) !important;
}

.rr-x1room-btn--finished,
.rr-x1room-btn--finished:hover {
    background: linear-gradient(155deg, #64748b 0%, #475569 45%, #334155 100%) !important;
    box-shadow:
        0 5px 0 #1e293b,
        0 10px 16px rgba(15, 23, 42, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
    transform: translateY(0) !important;
    filter: none !important;
}

body.light .rr-bolao-card,
body.light .rr-x1room-card {
    background:
        radial-gradient(120% 95% at 50% 0%, color-mix(in srgb, var(--glow-tint, rgba(249, 115, 22, 0.35)) 36%, transparent) 0%, transparent 62%),
        linear-gradient(165deg, rgba(255, 253, 249, 0.98), rgba(255, 244, 233, 0.98)) !important;
    box-shadow:
        0 0 0 1px rgba(249, 115, 22, 0.24) inset,
        0 8px 18px color-mix(in srgb, var(--glow-tint, rgba(249, 115, 22, 0.35)) 45%, transparent),
        0 10px 20px rgba(124, 45, 18, 0.18) !important;
}

/* ============================================
   Inicio Card Polish
   ============================================ */
.rr-neuro-wrapper {
    width: 152px;
    position: relative;
    isolation: isolate;
    transform-style: preserve-3d;
}

.rr-neuro-wrapper::before,
.rr-neuro-wrapper::after {
    content: '';
    position: absolute;
    pointer-events: none;
    display: block !important;
}

.rr-neuro-wrapper::before {
    inset: auto 10px -14px;
    height: 42px;
    border-radius: 999px;
    background: radial-gradient(circle, color-mix(in srgb, var(--rr-level-ring, #22c55e) 46%, transparent) 0%, transparent 72%) !important;
    filter: blur(18px);
    opacity: 0.72;
    z-index: 0;
}

.rr-neuro-wrapper::after {
    inset: 8px;
    border-radius: 18px;
    border: 1px solid color-mix(in srgb, var(--rr-level-ring, #22c55e) 24%, transparent);
    opacity: 0.38;
    z-index: 1;
}

.rr-neuro-wrapper .rr-card-inner {
    height: 292px;
    min-height: 292px;
    border-radius: 18px !important;
    padding: 1px;
    background:
        radial-gradient(115% 86% at 16% 0%, color-mix(in srgb, var(--rr-level-ring, #22c55e) 26%, transparent) 0%, transparent 54%),
        radial-gradient(82% 72% at 100% 100%, color-mix(in srgb, var(--glow-tint, rgba(34, 197, 94, 0.35)) 30%, transparent) 0%, transparent 58%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(15, 23, 42, 0)) !important;
}

.rr-neuro-wrapper .rr-card-inner::after {
    content: '';
    position: absolute;
    inset: 1px;
    border-radius: 17px;
    background:
        linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0) 34%),
        radial-gradient(circle at top right, rgba(255, 255, 255, 0.07), transparent 30%);
    pointer-events: none;
    z-index: 1;
}

.rr-neuro-topline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}

.rr-neuro-level-badge,
.rr-neuro-context-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 22px;
    padding: 0 8px;
    border-radius: 999px;
    font-size: 0.48rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    white-space: nowrap;
    box-shadow: 0 10px 18px rgba(2, 6, 23, 0.18);
}

.rr-neuro-level-badge {
    color: #f8fafc;
    border: 1px solid color-mix(in srgb, var(--rr-level-ring, #22c55e) 72%, transparent);
    background: linear-gradient(180deg, color-mix(in srgb, var(--rr-level-ring, #22c55e) 28%, rgba(15, 23, 42, 0.92)), rgba(15, 23, 42, 0.42));
}

.rr-neuro-level-badge i,
.rr-neuro-context-badge i {
    font-size: 0.64rem;
}

.rr-neuro-context-badge {
    color: rgba(226, 232, 240, 0.78);
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(15, 23, 42, 0.52);
}

.rr-neuro-header {
    padding: 8px 8px 6px;
    min-height: auto;
    display: block;
    flex: none;
}

.rr-neuro-portrait-stage {
    position: relative;
    min-height: 78px;
    display: grid;
    place-items: center;
}

.rr-neuro-portrait-glow {
    position: absolute;
    inset: 10px 18px 0;
    border-radius: 28px;
    background:
        radial-gradient(circle at top, color-mix(in srgb, var(--rr-level-ring, #22c55e) 42%, transparent) 0%, transparent 56%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(15, 23, 42, 0));
    filter: blur(6px);
    opacity: 0.92;
}

.rr-neuro-portrait-chip {
    position: absolute;
    top: -2px;
    right: 0;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    min-height: 18px;
    padding: 0 6px;
    border-radius: 999px;
    color: #fff7ed;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.32), rgba(37, 99, 235, 0.18));
    box-shadow: 0 14px 22px rgba(2, 6, 23, 0.22);
    font-size: 0.42rem;
    font-weight: 900;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.rr-neuro-img-container {
    width: 66px;
    height: 66px;
    border-width: 2px;
    border-radius: 18px;
    box-shadow:
        0 0 0 3px rgba(15, 23, 42, 0.9),
        0 0 28px var(--rr-level-glow),
        0 18px 28px rgba(0, 0, 0, 0.42);
    position: relative;
    z-index: 1;
}

.rr-neuro-content {
    padding: 0 8px 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 0;
    flex: 1 1 auto;
}

.rr-neuro-title-wrap {
    display: grid;
    gap: 2px;
}

.rr-neuro-title {
    margin: 0;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: -0.03em;
    min-height: 1.15em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-neuro-subtitle {
    margin: -2px 0 0;
    color: rgba(226, 232, 240, 0.72);
    font-size: 0.5rem;
    line-height: 1.2;
    min-height: 1.2em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-neuro-group-members-btn {
    margin: 2px auto 0;
    min-height: 24px;
    padding: 0 10px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.78), rgba(15, 23, 42, 0.92));
    color: #cbd5f5;
    font-size: 0.5rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.05),
        0 10px 16px rgba(2, 6, 23, 0.14);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    cursor: pointer;
}

.rr-neuro-group-members-btn i {
    font-size: 0.62rem;
    color: #60a5fa;
}

.rr-neuro-note {
    margin: 0;
    min-height: 3.05em;
    color: rgba(226, 232, 240, 0.76);
    font-size: 0.67rem;
    line-height: 1.45;
}

.rr-neuro-stats-inline {
    position: relative;
    align-items: flex-start;
    justify-content: flex-start;
    flex-direction: column;
    gap: 3px;
    margin-bottom: 0;
    padding: 6px 7px;
    border-radius: 10px;
    border: 1px solid color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent);
    background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent), transparent 42%),
        linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(9, 14, 26, 0.94));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.06),
        0 14px 22px rgba(2, 6, 23, 0.22);
    overflow: hidden;
}

.rr-neuro-stats-inline::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.08) 32%, transparent 60%);
    transform: translateX(-100%);
    animation: rrDraftRankingBarShine 5.8s linear infinite;
    pointer-events: none;
}

.rr-neuro-stats-inline .rr-neuro-odd-label {
    font-size: 0.4rem;
    position: relative;
    z-index: 1;
}

.rr-neuro-odd-row {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 5px;
    position: relative;
    z-index: 1;
}

.rr-neuro-multiplier {
    font-size: 0.88rem !important;
    line-height: 1;
}

.rr-neuro-view-stats {
    width: 100%;
    min-height: 24px;
    margin: 0;
    padding: 0 8px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.76), rgba(15, 23, 42, 0.9));
    color: #cbd5f5;
    font-size: 0.48rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.05),
        0 10px 16px rgba(2, 6, 23, 0.14);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    cursor: pointer;
    flex: none;
    white-space: nowrap;
}

.rr-neuro-view-stats i {
    font-size: 0.62rem;
    color: #60a5fa;
}

.rr-neuro-view-stats__label {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-neuro-play-now {
    width: 100%;
    min-height: 30px;
    margin: 0;
    margin-top: auto;
    padding: 0 8px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(135deg, #f59e0be6, #2563eb 72%, #1d4ed8);
    box-shadow:
        0 8px 0 rgba(29, 78, 216, 0.48),
        0 18px 24px rgba(37, 99, 235, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.24);
    transform: translateY(0);
}

.rr-neuro-play-now__copy {
    display: grid;
    gap: 0;
    text-align: left;
}

.rr-neuro-play-now__label {
    font-size: 0.52rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rr-neuro-play-now__sub {
    font-size: 0.62rem;
    color: rgba(255, 247, 237, 0.84);
}

.rr-neuro-play-now__icon {
    width: 18px;
    height: 18px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.16);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.22);
    flex: none;
}

.rr-neuro-premium-banner {
    width: 100%;
    display: flex;
    align-items: center;
    margin-bottom: 0;
    margin-top: -2px;
    padding: 7px 8px;
    border-radius: 12px;
    border: 1px solid rgba(250, 204, 21, 0.18);
    background:
        radial-gradient(circle at top left, rgba(250, 204, 21, 0.18), transparent 36%),
        linear-gradient(145deg, rgba(120, 53, 15, 0.2), rgba(15, 23, 42, 0.9));
    box-shadow: 0 16px 22px rgba(2, 6, 23, 0.18);
    cursor: pointer;
}

.rr-neuro-premium-label {
    margin: 0;
    font-size: 0.52rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(253, 230, 138, 0.78);
}

.rr-neuro-wrapper:hover::before {
    opacity: 0.92;
    filter: blur(22px);
}

.rr-neuro-wrapper:hover::after {
    opacity: 0.62;
}

.rr-neuro-wrapper:hover .rr-card-inner {
    transform: translateY(-3px);
}

.rr-neuro-wrapper:hover .rr-neuro-portrait-chip {
    transform: translateY(-2px);
}

.rr-neuro-wrapper:hover .rr-neuro-img-container {
    transform: translateY(-2px) scale(1.04);
}

.rr-neuro-view-stats:hover {
    transform: translateY(-1px);
    border-color: rgba(96, 165, 250, 0.28);
    color: #eff6ff;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 12px 18px rgba(37, 99, 235, 0.14);
}

.rr-neuro-stats-inline[data-action="open-slip"]:hover {
    transform: translateY(-2px);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 18px 26px rgba(2, 6, 23, 0.28);
}

.rr-neuro-play-now:hover {
    transform: translateY(-1px);
    box-shadow:
        0 10px 0 rgba(29, 78, 216, 0.48),
        0 20px 28px rgba(37, 99, 235, 0.26),
        inset 0 1px 0 rgba(255, 255, 255, 0.28);
}

.rr-neuro-play-now:active {
    transform: translateY(3px);
    box-shadow:
        0 3px 0 rgba(29, 78, 216, 0.48),
        0 10px 18px rgba(37, 99, 235, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

.rr-neuro-premium-banner:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 28px rgba(2, 6, 23, 0.22);
}

.rr-bolao-card {
    width: 214px;
    min-height: 342px;
    padding-top: 20px !important;
    margin-top: 18px !important;
    border-radius: 20px !important;
    overflow: visible !important;
    display: flex;
}

.rr-bolao-shell {
    display: flex;
    flex-direction: column;
    min-height: 100%;
    width: 100%;
    overflow: hidden;
    border-radius: 20px;
    position: relative;
    z-index: 0;
    padding-top: 0;
}

.rr-bolao-bg {
    position: absolute;
    top: -18px;
    left: 14px;
    right: 14px;
    height: 142px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}

.rr-bolao-bg-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center top;
    transform: scale(calc(var(--rr-bolao-cover-scale, 1) * 0.68));
    transform-origin: center top;
    opacity: 0.16;
    filter: saturate(0.95);
}

.rr-bolao-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.rr-bolao-badges-left,
.rr-bolao-badges-right {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.rr-bolao-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: 0.54rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.rr-bolao-badges .rr-bolao-premium-badge,
.rr-bolao-badges .rr-bolao-exclusive-badge,
.rr-bolao-badges .rr-bolao-status {
    position: static;
    top: auto;
    left: auto;
    right: auto;
    margin: 0;
    box-shadow: none;
}

.rr-bolao-content {
    position: relative;
    z-index: 3;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 11px;
    padding: 14px;
    overflow: hidden;
}

.rr-bolao-chip-row,
.rr-bolao-prize,
.rr-bolao-facts,
.rr-bolao-deadline,
.rr-bolao-btn {
    position: relative;
    z-index: 5;
}

.rr-bolao-kicker {
    font-size: 0.56rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #f59e0be6;
}

.rr-bolao-title {
    margin: 0;
    height: auto;
    min-height: 2.7em;
    font-size: 0.92rem;
    line-height: 1.35;
}

.rr-bolao-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    z-index: 8;
    position: static;
    min-height: 0;
    align-content: stretch;
}

.rr-bolao-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 28px;
    padding: 0 10px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(15, 23, 42, 0.42);
    color: rgba(248, 250, 252, 0.86);
    font-size: 0.58rem;
    font-weight: 700;
    position: relative;
    z-index: 9;
}

.rr-bolao-prize {
    position: relative;
    margin: 0;
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid rgba(34, 197, 94, 0.34);
    background:
        linear-gradient(180deg, rgba(6, 95, 70, 0.92) 0%, rgba(5, 46, 22, 0.88) 100%);
    overflow: hidden;
    box-shadow:
        0 12px 24px rgba(2, 6, 23, 0.28),
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 0 0 1px rgba(34, 197, 94, 0.12);
    animation: rr-bolao-prize-breathe 3.4s ease-in-out infinite;
}

.rr-bolao-prize::after {
    content: '';
    position: absolute;
    inset: -20% auto -20% -42%;
    width: 42%;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.18) 52%, transparent 100%);
    transform: skewX(-18deg);
    animation: rr-bolao-prize-shine 3.8s ease-in-out infinite;
    pointer-events: none;
}

.rr-bolao-prize-value {
    font-size: 1.06rem;
    color: #4ade80;
    text-shadow: 0 0 18px rgba(34, 197, 94, 0.2);
    animation: rr-bolao-prize-value-glow 2.2s ease-in-out infinite;
}

@keyframes rr-bolao-prize-breathe {
    0%, 100% {
        box-shadow:
            0 12px 24px rgba(2, 6, 23, 0.28),
            inset 0 1px 0 rgba(255, 255, 255, 0.08),
            0 0 0 1px rgba(34, 197, 94, 0.12);
    }
    50% {
        box-shadow:
            0 14px 28px rgba(2, 6, 23, 0.34),
            inset 0 1px 0 rgba(255, 255, 255, 0.12),
            0 0 0 1px rgba(74, 222, 128, 0.22),
            0 0 22px rgba(34, 197, 94, 0.16);
    }
}

@keyframes rr-bolao-prize-shine {
    0%, 56% { transform: translateX(0) skewX(-18deg); opacity: 0; }
    62% { opacity: 1; }
    100% { transform: translateX(420%) skewX(-18deg); opacity: 0; }
}

@keyframes rr-bolao-prize-value-glow {
    0%, 100% {
        transform: scale(1);
        text-shadow: 0 0 18px rgba(34, 197, 94, 0.2);
    }
    50% {
        transform: scale(1.035);
        text-shadow: 0 0 24px rgba(74, 222, 128, 0.34);
    }
}

.rr-bolao-facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.rr-bolao-fact {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 10px 11px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(2, 6, 23, 0.3);
}

.rr-bolao-fact-label {
    font-size: 0.54rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.86);
}

.rr-bolao-fact-value {
    font-size: 0.84rem;
    font-weight: 800;
    color: #fff;
}

.rr-bolao-deadline {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 9px 10px;
    border-radius: 12px;
    border: 1px solid rgba(59, 130, 246, 0.18);
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.92) 0%, rgba(10, 15, 30, 0.9) 100%);
    color: rgba(241, 245, 249, 0.92);
    font-size: 0.62rem;
    font-weight: 700;
    box-shadow:
        0 10px 18px rgba(2, 6, 23, 0.22),
        inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-bolao-btn {
    margin-top: auto;
    min-height: 42px;
    border-radius: 12px !important;
    font-size: 0.68rem;
    letter-spacing: 0.04em;
}

.rr-bolao-actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    width: 100%;
    margin-top: auto;
}

.rr-bolao-actions .rr-bolao-btn {
    margin-top: 0;
}

@media (max-width: 767px) {
    #rrInicioBolaos .rr-bolao-actions {
        display: flex;
        align-items: stretch;
        gap: 8px;
        width: 100%;
    }

    #rrInicioBolaos .rr-bolao-actions .rr-bolao-btn {
        flex: 1 1 0;
        min-width: 0;
        min-height: 40px;
        margin: 0 !important;
        padding: 0 10px;
        line-height: 1.1;
        align-self: stretch;
        box-sizing: border-box;
        transform: none !important;
        border-radius: 12px !important;
    }

    #rrInicioBolaos .rr-bolao-actions .rr-bolao-btn::before {
        display: none;
    }

    #rrInicioBolaos .rr-bolao-actions .rr-bolao-btn:hover,
    #rrInicioBolaos .rr-bolao-actions .rr-bolao-btn:active,
    #rrInicioBolaos .rr-bolao-actions .rr-bolao-btn:focus-visible {
        transform: none !important;
    }
}

.rr-bolao-btn--voucher-ready {
    background: linear-gradient(135deg, #f59e0be6 0%, #f59e0be6 42%, #2563eb 100%) !important;
    box-shadow:
        0 5px 0 #9a3412,
        0 14px 26px rgba(249, 115, 22, 0.34),
        0 0 18px rgba(251, 191, 36, 0.28),
        inset 0 1px 0 rgba(255, 255, 255, 0.34) !important;
}

.rr-bolao-btn--voucher-ready:hover {
    box-shadow:
        0 7px 0 #9a3412,
        0 18px 30px rgba(249, 115, 22, 0.38),
        0 0 20px rgba(251, 191, 36, 0.36),
        inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
}

.rr-bolao-btn--ranking,
.rr-bolao-btn--ranking:hover {
    background: linear-gradient(155deg, #f59e0be6 0%, #f97316 45%, #c2410c 100%) !important;
    box-shadow:
        0 5px 0 #9a3412,
        0 10px 16px rgba(124, 45, 18, 0.38),
        inset 0 1px 0 rgba(255, 255, 255, 0.32) !important;
}

.rr-bolao-voucher-note {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    margin-top: 2px;
    padding: 8px 10px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.16), rgba(59, 130, 246, 0.12));
    border: 1px solid rgba(251, 191, 36, 0.26);
    color: #fde68a;
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.rr-bolao-voucher-note i {
    color: #f59e0be6;
}

body.light .rr-bolao-voucher-note {
    color: #9a3412;
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.18), rgba(59, 130, 246, 0.08));
    border-color: rgba(245, 158, 11, 0.24);
}

.rr-x1room-card {
    width: 218px;
    min-height: 330px;
    border-radius: 20px !important;
    overflow: hidden !important;
}

.rr-x1room-shell {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 100%;
    padding: 14px;
}

.rr-x1room-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.rr-x1room-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: 0.54rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rr-x1room-pill--premium {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.24), rgba(245, 158, 11, 0.18));
    border: 1px solid rgba(251, 191, 36, 0.34);
    color: #fde68a;
}

.rr-x1room-status {
    padding: 0 10px;
    min-height: 26px;
    border-radius: 999px;
    font-size: 0.54rem;
    display: inline-flex;
    align-items: center;
}

.rr-x1room-header {
    padding: 12px;
    gap: 10px;
    min-height: auto;
    border-radius: 16px;
    background: rgba(2, 6, 23, 0.3);
    border: 1px solid rgba(148, 163, 184, 0.12);
}

.rr-x1room-player {
    gap: 5px;
}

.rr-x1room-player-avatar {
    width: 44px;
    height: 44px;
    border-width: 2px;
}

.rr-x1room-player-name {
    max-width: none;
    font-size: 0.7rem;
    justify-content: center;
}

.rr-x1room-player-role {
    font-size: 0.53rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.7);
}

.rr-x1room-vs {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.22);
    font-size: 0.78rem;
}

.rr-x1room-highlight {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 12px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.14), rgba(15, 23, 42, 0.06));
    border: 1px solid rgba(249, 115, 22, 0.18);
}

.rr-x1room-highlight-label {
    font-size: 0.54rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.84);
}

.rr-x1room-highlight-value {
    font-size: 0.88rem;
    font-weight: 800;
    line-height: 1.3;
    color: #fff;
}

.rr-x1room-highlight-meta {
    font-size: 0.62rem;
    font-weight: 700;
    color: #fdba74;
}

.rr-x1room-facts {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.rr-x1room-fact {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 10px 11px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(2, 6, 23, 0.28);
}

.rr-x1room-fact-label {
    font-size: 0.54rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.86);
}

.rr-x1room-fact-value {
    font-size: 0.84rem;
    font-weight: 800;
    color: #fff;
}

.rr-x1room-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 0;
}

.rr-x1room-btn {
    margin-top: auto;
    min-height: 42px;
    border-radius: 12px !important;
    font-size: 0.68rem;
    letter-spacing: 0.04em;
}

.rr-x1room-winner-badge {
    top: 12px;
    left: auto;
    right: 12px;
    transform: none;
    max-width: calc(100% - 24px);
    overflow: hidden;
    text-overflow: ellipsis;
}

body.light .rr-neuro-note,
body.light .rr-neuro-context-badge,
body.light .rr-bolao-fact-label,
body.light .rr-bolao-deadline,
body.light .rr-x1room-fact-label,
body.light .rr-x1room-player-role,
body.light .rr-x1room-highlight-label {
    color: #7c2d12 !important;
}

body.light .rr-bolao-chip,
body.light .rr-neuro-context-badge,
body.light .rr-x1room-header,
body.light .rr-x1room-highlight,
body.light .rr-x1room-fact,
body.light .rr-bolao-fact,
body.light .rr-bolao-deadline {
    background: rgba(255, 255, 255, 0.62) !important;
    border-color: rgba(234, 88, 12, 0.14) !important;
}

body.light .rr-bolao-content,
body.light .rr-x1room-shell,
body.light .rr-x1room-content {
    color: #4a2a1a !important;
}

body.light .rr-bolao-title,
body.light .rr-bolao-fact-value,
body.light .rr-bolao-chip,
body.light .rr-bolao-timer,
body.light .rr-x1room-player-name,
body.light .rr-x1room-highlight-value,
body.light .rr-x1room-fact-value {
    color: #4a2a1a !important;
}

body.light .rr-x1room-player-name--host {
    color: #b45309 !important;
}

body.light .rr-neuro-wrapper::before {
    background: radial-gradient(circle, color-mix(in srgb, var(--rr-level-ring, #22c55e) 28%, transparent) 0%, transparent 72%) !important;
}

body.light .rr-neuro-wrapper::after {
    border-color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, rgba(15, 23, 42, 0.12));
    opacity: 0.5;
}

body.light .rr-neuro-wrapper .rr-card-inner {
    background:
        radial-gradient(115% 86% at 16% 0%, color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent) 0%, transparent 54%),
        radial-gradient(82% 72% at 100% 100%, color-mix(in srgb, var(--glow-tint, rgba(34, 197, 94, 0.3)) 24%, transparent) 0%, transparent 58%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 247, 237, 0.98)) !important;
    box-shadow:
        0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 26%, transparent) inset,
        0 14px 24px color-mix(in srgb, var(--rr-level-ring, #22c55e) 14%, transparent),
        0 18px 28px rgba(124, 45, 18, 0.1) !important;
}

body.light .rr-neuro-level-badge {
    color: #0f172a;
    background: linear-gradient(180deg, color-mix(in srgb, var(--rr-level-ring, #22c55e) 16%, rgba(255, 255, 255, 0.98)), rgba(255, 255, 255, 0.92));
}

body.light .rr-neuro-context-badge {
    color: #7c2d12 !important;
    background: rgba(255, 255, 255, 0.82) !important;
    border-color: rgba(234, 88, 12, 0.12) !important;
}

body.light .rr-neuro-portrait-chip {
    color: #7c2d12;
    background: linear-gradient(135deg, rgba(255, 237, 213, 0.98), rgba(219, 234, 254, 0.96));
    border-color: rgba(234, 88, 12, 0.16);
}

body.light .rr-neuro-img-container {
    background: rgba(255, 255, 255, 0.98);
    box-shadow:
        0 0 0 3px rgba(255, 255, 255, 0.92),
        0 0 22px color-mix(in srgb, var(--rr-level-ring, #22c55e) 20%, transparent),
        0 16px 24px rgba(124, 45, 18, 0.12);
}

body.light .rr-neuro-title {
    color: #0f172a !important;
}

body.light .rr-neuro-subtitle,
body.light .rr-neuro-note {
    color: #7c2d12 !important;
}

body.light .rr-neuro-stats-inline {
    background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--rr-level-ring, #22c55e) 12%, transparent), transparent 42%),
        linear-gradient(145deg, rgba(255, 255, 255, 0.96), rgba(255, 249, 243, 0.98));
    border-color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 14%, rgba(15, 23, 42, 0.08));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 14px 22px rgba(124, 45, 18, 0.08);
}

body.light .rr-neuro-view-stats {
    border-color: rgba(37, 99, 235, 0.12);
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.98));
    color: #1e293b;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 10px 18px rgba(15, 23, 42, 0.08);
}

body.light .rr-neuro-view-stats i {
    color: #2563eb;
}

body.light .rr-neuro-play-now {
    color: #fff;
}

body.light .rr-neuro-play-now__label {
    color: #fff7ed !important;
}

body.light .rr-neuro-play-now__sub {
    color: rgba(255, 237, 213, 0.84);
}

body.light .rr-neuro-premium-banner {
    background:
        radial-gradient(circle at top left, rgba(250, 204, 21, 0.12), transparent 36%),
        linear-gradient(145deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.96));
    border-color: rgba(217, 119, 6, 0.14);
    box-shadow: 0 16px 24px rgba(124, 45, 18, 0.08);
}

body.light .rr-neuro-premium-label {
    color: #b45309;
}

body.light .rr-bolao-prize-label,
body.light .rr-x1room-prize-label {
    color: #166534 !important;
}

body.light .rr-bolao-prize-value,
body.light .rr-x1room-prize-value {
    color: #15803d !important;
}

body.light .rr-bolao-chip i,
body.light .rr-bolao-deadline i,
body.light .rr-x1room-fact i,
body.light .rr-x1room-highlight-meta,
body.light .rr-x1room-pill--premium {
    color: #9a3412 !important;
}

body.light .rr-x1room-pill--premium {
    background: linear-gradient(135deg, rgba(254, 240, 138, 0.7), rgba(251, 191, 36, 0.34)) !important;
    border-color: rgba(217, 119, 6, 0.24) !important;
}

@media (min-width: 768px) {
    .hub-top__grid .rr-side-panel--bottom .rr-neuro-wrapper {
        min-width: 186px;
        width: 186px;
    }

    .rr-side-panel__row .rr-bolao-card {
        min-width: 304px;
        width: 304px;
        min-height: 372px;
    }

    .rr-side-panel__row .rr-x1room-card {
        min-width: 252px;
        width: 252px;
        min-height: 346px;
    }
}

@media (max-width: 767px) {
    .rr-neuro-wrapper {
        width: 146px;
    }

    .rr-neuro-wrapper .rr-card-inner {
        height: 284px;
        min-height: 284px;
    }

    .rr-neuro-portrait-chip {
        top: 2px;
        right: 2px;
        min-height: 20px;
        padding: 0 6px;
        font-size: 0.44rem;
    }

    .rr-neuro-img-container {
        width: 66px;
        height: 66px;
        border-radius: 18px;
    }

    .rr-neuro-content {
        padding: 0 8px 8px;
        gap: 5px;
    }

    .rr-neuro-title {
        font-size: 0.76rem;
    }

    .rr-neuro-stats-inline {
        gap: 2px;
        padding: 5px 6px;
        border-radius: 9px;
    }

    .rr-neuro-stats-inline .rr-neuro-odd-label {
        font-size: 0.36rem;
        letter-spacing: 0.05em;
    }

    .rr-neuro-odd-row {
        align-items: center;
        flex-direction: row;
        justify-content: center;
        gap: 4px;
    }

    .rr-neuro-multiplier {
        font-size: 0.8rem !important;
    }

    .rr-neuro-view-stats {
        min-height: 20px;
        padding: 0 6px;
        gap: 3px;
        font-size: 0.4rem;
        letter-spacing: 0.02em;
        text-transform: none;
    }

    .rr-neuro-view-stats i {
        font-size: 0.5rem;
    }

    .rr-neuro-view-stats__label {
        text-align: center;
    }

    .rr-neuro-play-now {
        min-height: 31px;
        padding: 0 8px;
        border-radius: 9px;
    }

    .rr-neuro-play-now__label {
        font-size: 0.48rem;
        letter-spacing: 0.07em;
    }

    .rr-neuro-play-now__icon {
        width: 16px;
        height: 16px;
    }

    .rr-neuro-premium-banner {
        margin-top: 0;
        padding: 5px 6px;
        border-radius: 10px;
    }

    .rr-neuro-premium-icon {
        width: 11px;
        height: 11px;
        font-size: 0.38rem;
    }

    .rr-neuro-premium-value {
        font-size: 0.64rem;
    }

    .rr-neuro-premium-arrow {
        font-size: 0.58rem;
    }

    .rr-bolao-card {
        width: 280px;
        min-height: 376px;
        margin-top: 58px;
        padding-top: 58px;
    }

.rr-x1room-card {
        width: 220px;
        min-height: 322px;
    }

    .rr-bolao-header {
        top: -60px;
        width: 112px;
        height: 112px;
    }

    .rr-bolao-content {
        padding: 10px 10px 12px;
    }

    .rr-bolao-title {
        font-size: 0.8rem;
        line-height: 1.25;
        height: 36px;
        margin-bottom: 8px;
    }

    .rr-bolao-meta,
    .rr-bolao-prize,
    .rr-bolao-info {
        margin-bottom: 8px;
    }

    .rr-bolao-info {
        justify-content: center;
    }

    .rr-bolao-info-item--timer {
        justify-content: center;
    }
}

/* ============================================
   Stable Competitor Cards v2
   ============================================ */
.rr-neuro-wrapper {
    width: 164px;
    min-width: 164px;
    position: relative;
    isolation: isolate;
    flex: 0 0 auto;
}

.rr-neuro-wrapper--with-premium {
    padding-bottom: 18px;
}

.rr-neuro-wrapper::before,
.rr-neuro-wrapper::after {
    content: '';
    position: absolute;
    pointer-events: none;
}

.rr-neuro-wrapper::before {
    inset: auto 14px -16px;
    height: 34px;
    border-radius: 999px;
    background: radial-gradient(circle, color-mix(in srgb, var(--rr-level-ring, #22c55e) 34%, transparent) 0%, transparent 72%);
    filter: blur(16px);
    opacity: 0.52;
    z-index: 0;
}

.rr-neuro-wrapper::after {
    inset: 6px;
    border-radius: 20px;
    border: 1px solid color-mix(in srgb, var(--rr-level-ring, #22c55e) 22%, transparent);
    opacity: 0.42;
    z-index: 1;
}

.rr-neuro-wrapper .rr-card-inner {
    height: 332px;
    min-height: 332px;
    border: none !important;
    border-radius: 22px !important;
    overflow: hidden;
    padding: 1px;
    animation: none !important;
    background:
        linear-gradient(145deg, color-mix(in srgb, var(--rr-level-ring, #22c55e) 34%, rgba(255, 255, 255, 0.08)), rgba(255, 255, 255, 0.06)),
        radial-gradient(120% 92% at 12% 0%, color-mix(in srgb, var(--rr-level-ring, #22c55e) 24%, transparent) 0%, transparent 56%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(7, 10, 19, 0.98)) !important;
    box-shadow:
        0 18px 28px rgba(2, 6, 23, 0.34),
        0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent) inset !important;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.rr-neuro-wrapper .rr-card-inner::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0) 34%),
        radial-gradient(circle at top right, rgba(255, 255, 255, 0.08), transparent 32%);
    pointer-events: none;
    z-index: 1;
}

.rr-card-inside {
    inset: 1px;
    border-radius: 21px;
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(5, 8, 16, 0.98)),
        radial-gradient(circle at top left, color-mix(in srgb, var(--glow-tint, rgba(34, 197, 94, 0.3)) 18%, transparent), transparent 42%);
    opacity: 1;
}

.rr-card-inner .card__shine,
.rr-card-inner .card__glare {
    display: none !important;
}

.rr-card-content-layer {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 8px;
    height: 100%;
    padding: 10px;
}

.rr-neuro-header {
    display: grid;
    gap: 8px;
    padding: 0;
    min-height: 0;
    background: transparent;
}

.rr-neuro-topline {
    display: flex;
    justify-content: flex-start;
    gap: 6px;
    margin: 0;
}

.rr-neuro-level-badge {
    min-width: 0;
    min-height: 24px;
    padding: 0 9px;
    gap: 5px;
    justify-content: flex-start;
    border-radius: 999px;
    font-size: 0.5rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow: none;
}

.rr-neuro-level-badge span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-neuro-level-badge {
    background: linear-gradient(135deg, color-mix(in srgb, var(--rr-level-ring, #22c55e) 34%, rgba(15, 23, 42, 0.98)), rgba(15, 23, 42, 0.82));
    border-color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 70%, transparent);
    color: #f8fafc;
}

.rr-neuro-level-badge i {
    font-size: 0.58rem;
    flex: none;
}

.rr-neuro-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    justify-items: center;
    align-items: start;
    gap: 8px;
    min-height: 126px;
    padding: 10px;
    border-radius: 18px;
    border: 1px solid color-mix(in srgb, var(--rr-level-ring, #22c55e) 22%, rgba(255, 255, 255, 0.06));
    background:
        radial-gradient(circle at top left, color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent), transparent 46%),
        linear-gradient(160deg, rgba(15, 23, 42, 0.88), rgba(10, 14, 26, 0.92));
}

.rr-neuro-avatar-stack {
    position: relative;
    display: grid;
    place-items: center;
    min-height: 64px;
    width: 100%;
}

.rr-neuro-portrait-glow {
    position: absolute;
    inset: -6px;
    border-radius: 22px;
    background: radial-gradient(circle, color-mix(in srgb, var(--rr-level-ring, #22c55e) 32%, transparent) 0%, transparent 72%);
    filter: blur(10px);
    opacity: 0.9;
}

.rr-neuro-img-container {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    border: 2px solid var(--rr-level-ring, #22c55e);
    background: rgba(15, 23, 42, 0.96);
    box-shadow:
        0 0 0 3px rgba(15, 23, 42, 0.86),
        0 14px 20px rgba(2, 6, 23, 0.28);
    z-index: 1;
    overflow: hidden;
}

.rr-neuro-img-container--captain {
    width: 72px;
    height: 72px;
    border-radius: 22px;
}

.rr-neuro-group-roster {
    position: relative;
    z-index: 1;
    display: grid;
    justify-content: center;
    align-content: center;
    gap: 6px;
    width: 100%;
    padding: 4px 0 2px;
}

.rr-neuro-group-roster--compact {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.rr-neuro-group-roster--ten {
    grid-template-columns: repeat(5, minmax(0, 1fr));
}

.rr-neuro-group-roster__item {
    width: 24px;
    height: 24px;
    border-radius: 999px;
    overflow: hidden;
    border: 1.5px solid rgba(255, 255, 255, 0.18);
    background: rgba(15, 23, 42, 0.92);
    box-shadow: 0 4px 10px rgba(2, 6, 23, 0.24);
    justify-self: center;
}

.rr-neuro-group-roster__item.is-captain {
    border-color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 72%, #fff 12%);
    box-shadow:
        0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 26%, transparent),
        0 4px 10px rgba(2, 6, 23, 0.24);
}

.rr-neuro-group-roster__item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
}

.rr-neuro-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
}

.rr-neuro-title-wrap {
    display: grid;
    gap: 4px;
    min-width: 0;
    width: 100%;
    justify-items: center;
    align-content: start;
    text-align: center;
}

.rr-neuro-avatar-stack--group {
    width: 100%;
    max-width: 116px;
    min-height: 74px;
    padding: 10px 8px;
    border-radius: 20px;
    background:
        radial-gradient(circle at top right, rgba(96, 165, 250, 0.18), transparent 34%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(2, 6, 23, 0.95));
    border: 1px solid rgba(96, 165, 250, 0.16);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.04),
        0 14px 28px rgba(2, 6, 23, 0.2);
}

.rr-neuro-captain-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-height: 22px;
    padding: 0 9px;
    border-radius: 999px;
    border: 1px solid color-mix(in srgb, var(--rr-level-ring, #22c55e) 26%, rgba(255,255,255,0.08));
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.72)),
        radial-gradient(circle at top left, color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent), transparent 52%);
    color: #e2e8f0;
    font-size: 0.46rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.05),
        0 8px 14px rgba(2, 6, 23, 0.18);
}

.rr-neuro-captain-badge i {
    color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 82%, #fff 12%);
    font-size: 0.54rem;
}

.rr-neuro-portrait-chip {
    display: none !important;
}

.rr-neuro-title {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 900;
    line-height: 1.12;
    letter-spacing: -0.02em;
    color: #f8fafc;
    min-height: 2.24em;
    white-space: normal;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.rr-neuro-wrapper[data-entry-type="group"] .rr-neuro-title {
    font-size: 0.76rem;
    line-height: 1.16;
}

.rr-neuro-subtitle {
    margin: 0;
    font-size: 0.52rem;
    line-height: 1.25;
    color: rgba(226, 232, 240, 0.72);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-neuro-content {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 0;
    padding: 0;
}

.rr-neuro-stats-inline {
    position: relative;
    display: grid;
    gap: 3px;
    min-height: 50px;
    padding: 8px 10px;
    border-radius: 14px;
    border: 1px solid color-mix(in srgb, var(--rr-level-ring, #22c55e) 22%, transparent);
    background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent), transparent 42%),
        linear-gradient(145deg, rgba(12, 18, 32, 0.96), rgba(8, 11, 20, 0.98));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.06),
        0 12px 18px rgba(2, 6, 23, 0.18);
    overflow: hidden;
}

.rr-neuro-stats-inline::before {
    display: none;
}

.rr-neuro-stats-inline .rr-neuro-odd-label {
    position: relative;
    z-index: 1;
    font-size: 0.42rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.82);
}

.rr-neuro-odd-row {
    width: 100%;
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-end;
    justify-content: flex-start;
    gap: 6px;
}

.rr-neuro-multiplier {
    font-size: 1rem !important;
    line-height: 1;
    font-weight: 900;
    color: #f8fafc;
}

.rr-neuro-view-stats {
    width: 100%;
    min-height: 30px;
    padding: 0 10px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.68), rgba(15, 23, 42, 0.84));
    color: #dbeafe;
    font-size: 0.52rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: none;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.05),
        0 10px 16px rgba(2, 6, 23, 0.12);
    white-space: nowrap;
}

.rr-neuro-view-stats i {
    font-size: 0.58rem;
    color: #60a5fa;
}

.rr-neuro-view-stats__label {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-neuro-play-now {
    width: 100%;
    min-height: 38px;
    margin: 0;
    margin-top: 0;
    padding: 0 12px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(135deg, #f59e0be6 0%, #ea580c 38%, #2563eb 100%);
    box-shadow:
        0 8px 0 rgba(37, 99, 235, 0.42),
        0 16px 22px rgba(37, 99, 235, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.22);
    transform: translateY(0);
}

.rr-neuro-play-now--floating {
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 20px;
    z-index: 6;
}

.rr-neuro-play-now--floating-premium {
    bottom: 26px;
}

.rr-neuro-play-now__copy {
    min-width: 0;
    display: grid;
    gap: 0;
    text-align: left;
}

.rr-neuro-play-now__label {
    font-size: 0.58rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #fff7ed;
}

.rr-neuro-play-now__icon {
    width: 20px;
    height: 20px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.16);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.22);
    flex: none;
    color: #fff;
}

.rr-neuro-premium-banner {
    width: 100%;
    min-height: 30px;
    margin: 0;
    margin-top: auto;
    padding: 0 10px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid rgba(250, 204, 21, 0.18);
    background:
        radial-gradient(circle at top left, rgba(250, 204, 21, 0.14), transparent 36%),
        linear-gradient(145deg, rgba(120, 53, 15, 0.18), rgba(15, 23, 42, 0.92));
    box-shadow: 0 12px 18px rgba(2, 6, 23, 0.14);
    cursor: pointer;
}

.rr-neuro-premium-banner--floating {
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: -10px;
    z-index: 5;
    margin-top: 0;
}

.rr-neuro-premium-icon {
    width: 15px;
    height: 15px;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #38bdf8 0%, #2563eb 52%, #f59e0be6 100%);
    color: #fff7ed;
    font-size: 0.46rem;
    flex: none;
}

.rr-neuro-premium-text {
    flex: 1 1 auto;
    min-width: 0;
}

.rr-neuro-premium-value {
    display: block;
    font-size: 0.68rem;
    line-height: 1;
    font-weight: 900;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #fff;
}

.rr-neuro-premium-arrow {
    flex: none;
    font-size: 0.64rem;
    color: rgba(224, 242, 254, 0.84);
}

@media (hover: hover) and (pointer: fine) {
    .rr-neuro-wrapper:hover .rr-card-inner {
        transform: translateY(-3px);
        box-shadow:
            0 22px 32px rgba(2, 6, 23, 0.4),
            0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 24%, transparent) inset !important;
    }

    .rr-neuro-wrapper:hover::before {
        opacity: 0.72;
    }

    .rr-neuro-stats-inline[data-action="open-slip"]:hover,
    .rr-neuro-view-stats:hover,
    .rr-neuro-play-now:hover,
    .rr-neuro-premium-banner:hover {
        transform: translateY(-1px);
    }
}

body.light .rr-neuro-wrapper::before {
    background: radial-gradient(circle, color-mix(in srgb, var(--rr-level-ring, #22c55e) 24%, transparent) 0%, transparent 72%);
}

body.light .rr-neuro-wrapper::after {
    border-color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 16%, rgba(15, 23, 42, 0.12));
}

body.light .rr-neuro-wrapper .rr-card-inner {
    background:
        linear-gradient(145deg, color-mix(in srgb, var(--rr-level-ring, #22c55e) 24%, rgba(255, 255, 255, 0.96)), rgba(255, 255, 255, 0.9)),
        radial-gradient(120% 92% at 12% 0%, color-mix(in srgb, var(--rr-level-ring, #22c55e) 16%, transparent) 0%, transparent 56%),
        linear-gradient(180deg, rgba(255, 252, 248, 0.98), rgba(255, 245, 236, 0.98)) !important;
    box-shadow:
        0 16px 24px rgba(124, 45, 18, 0.12),
        0 0 0 1px color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, transparent) inset !important;
}

body.light .rr-card-inside {
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(255, 249, 243, 0.98)),
        radial-gradient(circle at top left, color-mix(in srgb, var(--glow-tint, rgba(34, 197, 94, 0.18)) 14%, transparent), transparent 42%);
}

body.light .rr-neuro-level-badge {
    color: #0f172a;
    background: linear-gradient(135deg, color-mix(in srgb, var(--rr-level-ring, #22c55e) 18%, rgba(255, 255, 255, 0.98)), rgba(255, 255, 255, 0.92));
}

body.light .rr-neuro-hero {
    background:
        radial-gradient(circle at top left, color-mix(in srgb, var(--rr-level-ring, #22c55e) 12%, transparent), transparent 46%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.94), rgba(255, 248, 240, 0.98));
    border-color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 16%, rgba(234, 88, 12, 0.12));
}

body.light .rr-neuro-group-roster__item {
    border-color: rgba(234, 88, 12, 0.16);
    background: rgba(255, 248, 240, 0.98);
}

body.light .rr-neuro-captain-badge {
    border-color: rgba(234, 88, 12, 0.14);
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 248, 240, 0.98)),
        radial-gradient(circle at top left, rgba(245, 158, 11, 0.12), transparent 52%);
    color: #7c2d12;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.42),
        0 8px 16px rgba(124, 45, 18, 0.08);
}

body.light .rr-neuro-captain-badge i {
    color: #d97706;
}

body.light .rr-neuro-group-members-btn {
    border-color: rgba(234, 88, 12, 0.14);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 248, 240, 0.98));
    color: #334155;
}

body.light .rr-neuro-group-members-btn i {
    color: #2563eb;
}

body.light .rr-neuro-portrait-chip {
    background: linear-gradient(135deg, rgba(255, 237, 213, 0.98), rgba(219, 234, 254, 0.96));
    border-color: rgba(234, 88, 12, 0.14);
    color: #7c2d12;
}

body.light .rr-neuro-img-container {
    background: rgba(255, 255, 255, 0.98);
    box-shadow:
        0 0 0 3px rgba(255, 255, 255, 0.9),
        0 12px 18px rgba(124, 45, 18, 0.12);
}

body.light .rr-neuro-title {
    color: #0f172a !important;
}

body.light .rr-neuro-subtitle,
body.light .rr-neuro-stats-inline .rr-neuro-odd-label {
    color: #7c2d12 !important;
}

body.light .rr-neuro-stats-inline {
    background:
        radial-gradient(circle at top right, color-mix(in srgb, var(--rr-level-ring, #22c55e) 12%, transparent), transparent 42%),
        linear-gradient(145deg, rgba(255, 255, 255, 0.94), rgba(255, 249, 243, 0.98));
    border-color: color-mix(in srgb, var(--rr-level-ring, #22c55e) 12%, rgba(15, 23, 42, 0.08));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 10px 16px rgba(124, 45, 18, 0.08);
}

body.light .rr-neuro-multiplier {
    color: #0f172a;
}

body.light .rr-neuro-view-stats {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(248, 250, 252, 0.98));
    border-color: rgba(37, 99, 235, 0.12);
    color: #1e293b;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 10px 16px rgba(15, 23, 42, 0.08);
}

body.light .rr-neuro-view-stats i {
    color: #2563eb;
}

body.light .rr-neuro-premium-banner {
    background:
        radial-gradient(circle at top left, rgba(250, 204, 21, 0.12), transparent 36%),
        linear-gradient(145deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.96));
    border-color: rgba(217, 119, 6, 0.14);
    box-shadow: 0 12px 18px rgba(124, 45, 18, 0.08);
}

body.light .rr-neuro-premium-value {
    color: #9a3412;
}

body.light .rr-neuro-premium-arrow {
    color: #b45309;
}

@media (min-width: 768px) {
    .rr-neuro-wrapper {
        width: 172px;
        min-width: 172px;
    }

    .rr-neuro-play-now--floating {
        bottom: 10px;
    }

    .rr-neuro-play-now--floating-premium {
        bottom: 16px;
    }

    .rr-neuro-premium-banner--floating {
        bottom: -14px;
    }

    .hub-top__grid .rr-side-panel--bottom .rr-neuro-wrapper {
        width: 190px;
        min-width: 190px;
    }

    .rr-neuro-wrapper .rr-card-inner {
        height: 344px;
        min-height: 344px;
    }

    .rr-neuro-hero {
        grid-template-columns: minmax(0, 1fr);
        min-height: 132px;
        padding: 12px;
    }

    .rr-neuro-img-container {
        width: 64px;
        height: 64px;
    }

    .rr-neuro-img-container--captain {
        width: 74px;
        height: 74px;
    }

    .rr-neuro-avatar-stack--group {
        max-width: 122px;
        min-height: 82px;
        padding: 10px 8px;
    }

    .rr-neuro-group-roster {
        gap: 5px;
        padding: 6px 0 2px;
    }

    .rr-neuro-group-roster__item {
        width: 22px;
        height: 22px;
    }

    .rr-neuro-title {
        font-size: 0.88rem;
    }

    .rr-neuro-captain-badge {
        min-height: 24px;
        padding: 0 10px;
        font-size: 0.48rem;
    }

    .rr-neuro-multiplier {
        font-size: 1.08rem !important;
    }

    .rr-neuro-play-now {
        min-height: 40px;
    }
}

@media (max-width: 767px) {
    .rr-neuro-wrapper {
        width: 152px;
        min-width: 152px;
        overflow: visible !important;
    }

    .rr-neuro-wrapper .rr-card-inner {
        height: 304px;
        min-height: 304px;
        border-radius: 20px !important;
    }

    .rr-card-inside {
        border-radius: 19px;
    }

    .rr-card-content-layer {
        padding: 8px;
        gap: 7px;
    }

    .rr-neuro-wrapper--with-premium {
        padding-bottom: 60px;
    }

    .rr-neuro-wrapper {
        padding-bottom: 38px;
    }

    .rr-neuro-topline {
        gap: 5px;
    }

    .rr-neuro-level-badge {
        min-height: 22px;
        padding: 0 7px;
        gap: 4px;
        font-size: 0.44rem;
    }

    .rr-neuro-level-badge i {
        font-size: 0.52rem;
    }

    .rr-neuro-hero {
        grid-template-columns: minmax(0, 1fr);
        min-height: 108px;
        padding: 8px;
        gap: 7px;
        border-radius: 16px;
    }

    .rr-neuro-avatar-stack {
        min-height: 56px;
    }

    .rr-neuro-img-container {
        width: 52px;
        height: 52px;
        border-radius: 16px;
    }

    .rr-neuro-avatar-stack--group {
        max-width: 106px;
        min-height: 72px;
        padding: 8px 6px;
        border-radius: 18px;
    }

    .rr-neuro-group-roster {
        gap: 4px;
        padding: 4px 0 0;
    }

    .rr-neuro-group-roster__item {
        width: 18px;
        height: 18px;
    }

    .rr-neuro-title {
        font-size: 0.74rem;
        min-height: 2.16em;
    }

    .rr-neuro-captain-badge {
        min-height: 20px;
        padding: 0 8px;
        gap: 4px;
        font-size: 0.42rem;
    }

    .rr-neuro-captain-badge i {
        font-size: 0.48rem;
    }

    .rr-neuro-subtitle {
        font-size: 0.48rem;
    }

    .rr-neuro-group-members-btn {
        min-height: 22px;
        padding: 0 8px;
        font-size: 0.46rem;
        gap: 4px;
    }

    .rr-neuro-group-members-btn i {
        font-size: 0.56rem;
    }

    .rr-neuro-content {
        gap: 6px;
    }

    .rr-neuro-stats-inline {
        min-height: 46px;
        padding: 7px 8px;
        border-radius: 12px;
    }

    .rr-neuro-stats-inline .rr-neuro-odd-label {
        font-size: 0.36rem;
    }

    .rr-neuro-multiplier {
        font-size: 0.9rem !important;
    }

    .rr-neuro-view-stats {
        min-height: 28px;
        padding: 0 8px;
        font-size: 0.46rem;
    }

    .rr-neuro-view-stats i {
        font-size: 0.52rem;
    }

    .rr-neuro-play-now {
        min-height: 34px;
        padding: 0 10px;
        border-radius: 12px;
    }

    .rr-neuro-play-now--floating {
        left: 10px;
        right: 10px;
        bottom: 18px;
    }

    .rr-neuro-play-now--floating-premium {
        bottom: 22px;
    }

    .rr-neuro-play-now__label {
        font-size: 0.52rem;
    }

    .rr-neuro-play-now__icon {
        width: 18px;
        height: 18px;
    }

    .rr-neuro-premium-banner {
        min-height: 28px;
        padding: 0 8px;
        border-radius: 10px;
    }

    .rr-neuro-premium-banner--floating {
        left: 10px;
        right: 10px;
        bottom: -10px;
    }

    .rr-neuro-premium-icon {
        width: 13px;
        height: 13px;
        font-size: 0.4rem;
    }

    .rr-neuro-premium-value {
        font-size: 0.6rem;
    }

    .rr-neuro-premium-arrow {
        font-size: 0.58rem;
    }
}
</style>

<div class="rr-inicio-layout{{ $isBolaoLaunchMode ? ' rr-inicio-layout--bolao-launch' : '' }}">
@if($isBolaoLaunchMode)
<aside class="rr-inicio-launch-menu" aria-label="Menu principal">
    <a class="rr-inicio-launch-menu__brand" href="{{ route('home') }}">
        <img class="rr-inicio-launch-menu__logo" src="{{ siteLogo() }}" alt="Rei do Rodeio">
    </a>
    <div class="rr-inicio-launch-menu__copy">
        <span class="rr-inicio-launch-menu__kicker">{{ auth()->check() ? 'Bem vindo' : 'Menu principal' }}</span>
        <span class="rr-inicio-launch-menu__title{{ auth()->check() ? '' : ' rr-ethnocentric' }}">{{ auth()->check() ? (auth()->user()->username ?? 'Perfil') : 'Rei do Rodeio' }}</span>
    </div>
    <nav class="rr-inicio-launch-menu__nav" role="tablist">
        <button type="button" class="hub-header-nav__btn hub-header-nav__btn--orange active" data-section="inicio" data-accent="#f59e0be6">
            <i class="fas fa-home"></i> @lang('Início')
        </button>
        <button type="button" class="hub-header-nav__btn hub-header-nav__btn--blue" data-section="pix" data-profile-target="financeiro" data-accent="#f59e0be6">
            <i class="fas fa-wallet"></i> @lang('Pix')
        </button>
        <button type="button" class="hub-header-nav__btn hub-header-nav__btn--green" data-action="user">
            <i class="fas fa-user-edit"></i> @lang('Editar Perfil')
        </button>
    </nav>
</aside>
@endif
<div class="card border-0 shadow-sm rr-card rr-inicio-shell" data-section="inicio" id="rrInicioSection" data-is-premium="{{ $isPremiumUser ? '1' : '0' }}" data-entity-mode="{{ $rootEntityMode }}" data-has-multiple-modalidades="{{ $hasMultipleModalidades ? '1' : '0' }}">
    <div class="card-body">
        <div class="rr-inicio-event-call{{ $isBolaoLaunchMode ? ' rr-inicio-event-call--launch' : '' }}" id="rrInicioEventCall">
            <div class="rr-inicio-event-call__badges">
                <span class="rr-inicio-event-call__badge {{ $inicioHeroMode === 'live' ? 'rr-inicio-event-call__badge--live' : '' }}" id="rrInicioEventBadge">
                    @if($inicioHeroMode === 'live')
                        <span class="rr-inicio-event-call__badge-dot" aria-hidden="true" id="rrInicioEventBadgeDot"></span>
                    @else
                        <i class="fas fa-calendar-check" aria-hidden="true" id="rrInicioEventBadgeIcon"></i>
                    @endif
                    <span id="rrInicioEventBadgeText">{{ $inicioHeroBadge }}</span>
                </span>
                <span class="rr-inicio-event-call__badge rr-inicio-event-call__badge--accent" id="rrInicioEventAccent">
                    <i class="fas fa-trophy" aria-hidden="true"></i>
                    <span id="rrInicioEventAccentText">{{ $inicioHeroAccent }}</span>
                </span>
            </div>
            @if($isBolaoLaunchMode)
            <div class="rr-inicio-event-call__launch-copy">
                <span class="rr-inicio-event-call__launch-kicker" id="rrInicioEventUrgencyKicker">{{ $inicioHeroUrgency['kicker'] }}</span>
                <h3 class="rr-inicio-event-call__launch-title" id="rrInicioEventUrgencyTitle">{{ $inicioHeroUrgency['title'] }}</h3>
                <p class="rr-inicio-event-call__launch-note" id="rrInicioEventUrgencyNote">{{ $inicioHeroUrgency['note'] }}</p>
            </div>
            @endif
            <div class="rr-inicio-event-call__logo-wrap">
                <div id="rrInicioEventVisual" data-has-carousel="{{ $inicioHeroHasCarousel ? '1' : '0' }}">
                    @if($inicioHeroHasCarousel)
                        <div class="rr-inicio-event-carousel" id="rrInicioEventCarouselWrap">
                            <div class="rr-inicio-event-carousel__scene" id="rrInicioEventCarousel"></div>
                            <div class="rr-inicio-event-carousel__controls">
                                <button type="button" class="rr-inicio-event-carousel__nav" id="rrInicioEventCarouselPrev" aria-label="Evento anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="rr-inicio-event-carousel__nav" id="rrInicioEventCarouselNext" aria-label="Próximo evento">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="rr-inicio-event-carousel__dots" id="rrInicioEventCarouselDots"></div>
                    @else
                        <span class="rr-inicio-event-call__logo-stack" id="rrInicioEventLogoStack">
                            @foreach($inicioHeroLogos as $heroLogo)
                                <span class="rr-inicio-event-call__logo-frame">
                                    <img
                                        class="rr-inicio-event-call__logo"
                                        @if($loop->first) id="rrInicioEventLogo" @endif
                                        src="{{ (string) ($heroLogo['logo_url'] ?? $inicioHeroLogoUrl) }}"
                                        alt="{{ (string) ($heroLogo['title'] ?? $inicioHeroTitle) }}"
                                        onerror="this.src='{{ asset('assets/images/logo_icon/logo.png') }}'">
                                </span>
                            @endforeach
                        </span>
                    @endif
                </div>
                @if($isBolaoLaunchMode)
                <div class="rr-inicio-event-call__launch-floaters" aria-hidden="true">
                    <article class="rr-inicio-event-call__launch-floater rr-inicio-event-call__launch-floater--one">
                        <i class="fas fa-fire"></i>
                        <strong id="rrInicioEventFloaterOneTitle">{{ $inicioHeroUrgency['floaters'][0]['title'] }}</strong>
                        <span id="rrInicioEventFloaterOneMeta">{{ $inicioHeroUrgency['floaters'][0]['meta'] }}</span>
                    </article>
                    <article class="rr-inicio-event-call__launch-floater rr-inicio-event-call__launch-floater--two">
                        <i class="fas fa-users"></i>
                        <strong id="rrInicioEventFloaterTwoTitle">{{ $inicioHeroUrgency['floaters'][1]['title'] }}</strong>
                        <span id="rrInicioEventFloaterTwoMeta">{{ $inicioHeroUrgency['floaters'][1]['meta'] }}</span>
                    </article>
                    <article class="rr-inicio-event-call__launch-floater rr-inicio-event-call__launch-floater--three">
                        <i class="fas fa-stopwatch"></i>
                        <strong id="rrInicioEventFloaterThreeTitle">{{ $inicioHeroUrgency['floaters'][2]['title'] }}</strong>
                        <span id="rrInicioEventFloaterThreeMeta">{{ $inicioHeroUrgency['floaters'][2]['meta'] }}</span>
                    </article>
                </div>
                <div class="rr-inicio-event-call__mobile-badges">
                    <span class="rr-inicio-event-call__mobile-badge rr-inicio-event-call__mobile-badge--one">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Ganhe Dinheiro Real</span>
                    </span>
                    <span class="rr-inicio-event-call__mobile-badge rr-inicio-event-call__mobile-badge--five">
                        <i class="fas fa-users"></i>
                        <span>Monte sua Equipe</span>
                    </span>
                </div>
                @endif
            </div>
            <div class="rr-inicio-event-call__timer" id="rrInicioEventTimer" data-mode="{{ $inicioHeroMode }}" data-rodeio-id="{{ (int) ($inicioHeroRodeio->id ?? 0) }}" data-rodeio-title="{{ e($inicioHeroTitle) }}" @if(!empty($inicioHeroTimerIso)) data-closes-at="{{ $inicioHeroTimerIso }}" @endif>
                <div class="rr-inicio-event-call__timer-main">
                    <span class="rr-inicio-event-call__timer-label" id="rrInicioEventTimerLabel">{{ $inicioHeroLabel }}</span>
                    <strong class="rr-inicio-event-call__timer-value" id="rrInicioEventTimerValue">{{ !empty($inicioHeroTimerIso) ? '--:--' : 'Em breve' }}</strong>
                </div>
                <button
                    type="button"
                    class="rr-inicio-event-call__timer-reminder"
                    id="rrInicioReminderTrigger"
                    aria-label="Ativar notificação por e-mail do rodeio"
                    title="Ativar notificação por e-mail do rodeio"
                    @if($inicioHeroMode === 'live' || empty($inicioHeroRodeio?->id)) hidden @endif>
                    <i class="fas fa-bell" aria-hidden="true"></i>
                </button>
            </div>
            @if($isBolaoLaunchMode)
            <div class="rr-inicio-event-call__mobile-selector-row">
                <div class="rr-inicio-event-call__mobile-selector" id="rrInicioBolaoModalidadePickerWrap">
                    <button
                        type="button"
                        class="rr-inicio-event-call__mobile-selector-trigger"
                        id="rrInicioBolaoModalidadePickerBtn"
                        aria-haspopup="listbox"
                        aria-expanded="false">
                        <i class="fas fa-filter"></i>
                        <span id="rrInicioBolaoModalidadePickerLabel">Selecione uma modalidade</span>
                        <i class="fas fa-chevron-down rr-inicio-event-call__mobile-selector-chevron"></i>
                    </button>
                    <div
                        class="rr-inicio-event-call__mobile-selector-menu"
                        id="rrInicioBolaoModalidadePickerMenu"
                        role="listbox"
                        aria-label="Selecionar modalidade do bolão"
                        hidden>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="rr-inicio-event-lightbox" id="rrInicioEventLightbox" hidden>
            <button type="button" class="rr-inicio-event-lightbox__close" id="rrInicioEventLightboxClose" aria-label="Fechar imagem">&times;</button>
            <img class="rr-inicio-event-lightbox__img" id="rrInicioEventLightboxImg" src="" alt="Rodeio em destaque">
        </div>
        <div class="rr-inicio-reminder-modal" id="rrInicioReminderModal" aria-hidden="true" hidden>
            <div class="rr-inicio-reminder-modal__card" role="dialog" aria-modal="true" aria-labelledby="rrInicioReminderTitle">
                <div class="rr-inicio-reminder-modal__head">
                    <div>
                        <span class="rr-inicio-reminder-modal__eyebrow"><i class="fas fa-bell"></i> Alerta do rodeio</span>
                        <h3 class="rr-inicio-reminder-modal__title" id="rrInicioReminderTitle">Receba o aviso de início</h3>
                        <p class="rr-inicio-reminder-modal__text" id="rrInicioReminderText">Ative seu e-mail para receber a confirmação agora e outro aviso quando o próximo evento começar.</p>
                    </div>
                    <button type="button" class="rr-inicio-reminder-modal__close" id="rrInicioReminderClose" aria-label="Fechar modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="rr-inicio-reminder-modal__body">
                    <form id="rrInicioReminderForm" class="rr-inicio-reminder-modal__stack">
                        <div class="rr-inicio-reminder-modal__confirm">
                            <strong class="rr-inicio-reminder-modal__confirm-title">Ativar notificação de início do evento?</strong>
                            <p class="rr-inicio-reminder-modal__hint" id="rrInicioReminderHint">Se você confirmar, vamos usar o e-mail da sua conta para enviar o aviso de confirmação e o alerta quando o rodeio começar.</p>
                        </div>
                        <p class="rr-inicio-reminder-modal__status" id="rrInicioReminderStatus" aria-live="polite"></p>
                        <div class="rr-inicio-reminder-modal__actions">
                            <button type="button" class="rr-inicio-reminder-modal__btn rr-inicio-reminder-modal__btn--ghost" id="rrInicioReminderCancel">Não</button>
                            <button type="submit" class="rr-inicio-reminder-modal__btn rr-inicio-reminder-modal__btn--primary" id="rrInicioReminderSubmit">
                                <i class="fas fa-bell"></i>
                                <span>Sim, ativar</span>
                            </button>
                        </div>
                    </form>
                    <div class="rr-inicio-reminder-modal__success" id="rrInicioReminderSuccess" hidden>
                        <div class="rr-inicio-reminder-modal__success-visual" aria-hidden="true">
                            <span class="rr-inicio-reminder-modal__success-ring rr-inicio-reminder-modal__success-ring--one"></span>
                            <span class="rr-inicio-reminder-modal__success-ring rr-inicio-reminder-modal__success-ring--two"></span>
                            <span class="rr-inicio-reminder-modal__success-badge">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                        <strong class="rr-inicio-reminder-modal__success-title">Alerta ativado</strong>
                        <p class="rr-inicio-reminder-modal__success-text" id="rrInicioReminderSuccessText">Seu aviso de início foi ligado com sucesso.</p>
                    </div>
                </div>
            </div>
        </div>
        @if($isBolaoLaunchMode)
        <section class="rr-bolao-launch-simple" id="rrBolaoLaunchSimple">
            <div class="rr-bolao-launch-simple__actions">
                <article class="rr-bolao-launch-simple__btn rr-bolao-launch-simple__btn--20" data-bolao-launch-card="20" data-disabled="1">
                    <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="" class="rr-bolao-launch-simple__bg-logo" data-bolao-launch-bg-logo loading="lazy">
                    <div class="rr-bolao-launch-simple__topline">
                        <span class="rr-bolao-launch-simple__kicker">Entrada: 20,00</span>
                        <span class="rr-bolao-launch-simple__meta-badge" data-bolao-launch-slots>0/0</span>
                    </div>
                    <div class="rr-bolao-launch-simple__prize-stack">
                        <span class="rr-bolao-launch-simple__price-label">
                            <span class="rr-bolao-launch-simple__price-label-main">Bolão aberto</span>
                            <span class="rr-bolao-launch-simple__price-label-sub"></span>
                        </span>
                        <span class="rr-bolao-launch-simple__price">
                            <span class="rr-bolao-launch-simple__price-value">--</span>
                        </span>
                    </div>
                    <div class="rr-bolao-launch-simple__cta-actions">
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--team" data-bolao-launch-action="team" disabled><span>Entrar</span> <i class="fas fa-arrow-right"></i></button>
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--ranking" data-bolao-launch-action="ranking" disabled><span>Ranking</span> <i class="fas fa-trophy"></i></button>
                    </div>
                </article>
                <article class="rr-bolao-launch-simple__btn rr-bolao-launch-simple__btn--50" data-bolao-launch-card="50" data-disabled="1">
                    <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="" class="rr-bolao-launch-simple__bg-logo" data-bolao-launch-bg-logo loading="lazy">
                    <div class="rr-bolao-launch-simple__topline">
                        <span class="rr-bolao-launch-simple__kicker">Entrada: 50,00</span>
                        <span class="rr-bolao-launch-simple__meta-badge" data-bolao-launch-slots>0/0</span>
                    </div>
                    <div class="rr-bolao-launch-simple__prize-stack">
                        <span class="rr-bolao-launch-simple__price-label">
                            <span class="rr-bolao-launch-simple__price-label-main">Bolão aberto</span>
                            <span class="rr-bolao-launch-simple__price-label-sub"></span>
                        </span>
                        <span class="rr-bolao-launch-simple__price">
                            <span class="rr-bolao-launch-simple__price-value">--</span>
                        </span>
                    </div>
                    <div class="rr-bolao-launch-simple__cta-actions">
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--team" data-bolao-launch-action="team" disabled><span>Entrar</span> <i class="fas fa-arrow-right"></i></button>
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--ranking" data-bolao-launch-action="ranking" disabled><span>Ranking</span> <i class="fas fa-trophy"></i></button>
                    </div>
                </article>
                <article class="rr-bolao-launch-simple__btn rr-bolao-launch-simple__btn--100" data-bolao-launch-card="100" data-disabled="1">
                    <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="" class="rr-bolao-launch-simple__bg-logo" data-bolao-launch-bg-logo loading="lazy">
                    <div class="rr-bolao-launch-simple__topline">
                        <span class="rr-bolao-launch-simple__kicker">Entrada: 100,00</span>
                        <span class="rr-bolao-launch-simple__meta-badge" data-bolao-launch-slots>0/0</span>
                    </div>
                    <div class="rr-bolao-launch-simple__prize-stack">
                        <span class="rr-bolao-launch-simple__price-label">
                            <span class="rr-bolao-launch-simple__price-label-main">Bolão aberto</span>
                            <span class="rr-bolao-launch-simple__price-label-sub"></span>
                        </span>
                        <span class="rr-bolao-launch-simple__price">
                            <span class="rr-bolao-launch-simple__price-value">--</span>
                        </span>
                    </div>
                    <div class="rr-bolao-launch-simple__cta-actions">
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--team" data-bolao-launch-action="team" disabled><span>Entrar</span> <i class="fas fa-arrow-right"></i></button>
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--ranking" data-bolao-launch-action="ranking" disabled><span>Ranking</span> <i class="fas fa-trophy"></i></button>
                    </div>
                </article>
                <article class="rr-bolao-launch-simple__btn rr-bolao-launch-simple__btn--custom" data-bolao-launch-card="custom" data-disabled="1" hidden>
                    <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="" class="rr-bolao-launch-simple__bg-logo" data-bolao-launch-bg-logo loading="lazy">
                    <div class="rr-bolao-launch-simple__topline">
                        <span class="rr-bolao-launch-simple__kicker">Entrada personalizada</span>
                        <span class="rr-bolao-launch-simple__meta-badge" data-bolao-launch-slots>0/0</span>
                    </div>
                    <div class="rr-bolao-launch-simple__prize-stack">
                        <span class="rr-bolao-launch-simple__price-label">
                            <span class="rr-bolao-launch-simple__price-label-main">Bolão aberto</span>
                            <span class="rr-bolao-launch-simple__price-label-sub"></span>
                        </span>
                        <span class="rr-bolao-launch-simple__price">
                            <span class="rr-bolao-launch-simple__price-value">--</span>
                        </span>
                    </div>
                    <div class="rr-bolao-launch-simple__cta-actions">
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--team" data-bolao-launch-action="team" disabled><span>Entrar</span> <i class="fas fa-arrow-right"></i></button>
                        <button type="button" class="rr-bolao-launch-simple__cta rr-bolao-launch-simple__cta--ranking" data-bolao-launch-action="ranking" disabled><span>Ranking</span> <i class="fas fa-trophy"></i></button>
                    </div>
                </article>
            </div>
        </section>
        @endif
        <div class="rr-mobile-control-stack">
            <div class="rr-inicio-submenu" id="rrInicioSubmenu">
                <div class="rr-inicio-submenu__head">
                    <h3 class="rr-inicio-submenu__title">
                        <i class="fas fa-sliders-h" aria-hidden="true"></i>
                        <span>
                            <span class="is-highlight">Escolha</span>
                            {{ $isMobileBrowser ? 'uma opção' : ($hasMultipleModalidades ? 'a modalidade e o alvo' : ('Seu ' . ($isGroupMode ? 'Grupo' : 'Competidor'))) }}
                            <span class="is-highlight">Agora</span>
                        </span>
                    </h3>
                </div>
                <div class="rr-inicio-submenu__filters">
                    <div class="rr-inicio-submenu__search">
                        <div class="rr-mobile-search-shell">
                            <button type="button" class="rr-competitor-search-btn" id="rrCompetitorSearchOpen" aria-label="Buscar {{ $entryLabelSingular }}">
                                <i class="fas fa-search"></i>
                                <span>Buscar {{ $entryLabelSingular }}</span>
                            </button>
                            <span class="rr-mobile-search-shell__chrome" aria-hidden="true">
                                <span class="rr-mobile-search-shell__icon">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M10.5 4a6.5 6.5 0 1 0 4.03 11.6l4.43 4.43 1.41-1.41-4.43-4.43A6.5 6.5 0 0 0 10.5 4Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <span class="rr-mobile-search-shell__label">Buscar {{ $entryLabelSingular }}</span>
                            </span>
                        </div>
                    </div>
                    @unless($isBolaoLaunchMode)
                    <label class="rr-inicio-submenu__field" for="rrInicioRodeioFilter">
                        <span class="rr-inicio-submenu__label">Rodeio</span>
                        <select class="rr-inicio-submenu__select" id="rrInicioRodeioFilter">
                            <option value="">Todos os rodeios</option>
                            @foreach($inicioRodeioOptions as $option)
                            <option value="{{ $option['id'] }}" {{ (int) ($primaryContext['rodeio_id'] ?? 0) === (int) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="rr-inicio-submenu__field" for="rrInicioModalidadeFilter">
                        <span class="rr-inicio-submenu__label">Modalidade</span>
                        <select class="rr-inicio-submenu__select" id="rrInicioModalidadeFilter">
                            <option value="">Todas as modalidades</option>
                            @foreach($inicioModalidadeOptions as $option)
                            <option value="{{ $option['id'] }}" data-rodeio-id="{{ $option['rodeio_id'] }}" {{ (int) ($primaryContext['modalidade_id'] ?? 0) === (int) $option['id'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    @endunless
                </div>
            </div>
            <div class="rr-mobile-quick-nav" id="rrMobileQuickNav" style="{{ $isMobileBrowser ? 'display:block;position:relative;margin:8px 10px 0;z-index:15;' : '' }}">
                <div class="rr-mobile-quick-nav__actions" style="{{ $isMobileBrowser ? 'display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;' : '' }}">
                    <div class="rr-mobile-quick-nav__item rr-mobile-quick-nav__item--x1" style="{{ $isMobileBrowser ? 'display:block;position:relative;min-width:0;min-height:46px;border-radius:16px;overflow:hidden;background:linear-gradient(180deg,#f8bb3b 0%,#f59e0be6 56%,#c2410c 100%);box-shadow:0 10px 22px rgba(245,158,11,.32), inset 0 1px 0 rgba(255,255,255,.42), inset 0 -3px 0 rgba(124,45,18,.34);' : '' }}">
                        <button type="button" class="rr-mobile-quick-nav__surface rr-mobile-quick-nav__surface--x1" data-scroll-target="x1" aria-label="Ir para salas X1" style="{{ $isMobileBrowser ? 'position:absolute;inset:0;width:100%;min-height:100%;padding:0;border:0;border-radius:inherit;background:transparent;box-shadow:none;cursor:pointer;' : '' }}">
                            <i class="fas fa-bolt" aria-hidden="true"></i>
                            <span>X1</span>
                        </button>
                        <span class="rr-mobile-quick-nav__chrome" aria-hidden="true" style="{{ $isMobileBrowser ? 'position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:0 12px;pointer-events:none;' : '' }}">
                            <span class="rr-mobile-quick-nav__chrome-icon" style="{{ $isMobileBrowser ? 'width:22px;height:22px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;background:rgba(255,255,255,.18);color:#ffedd5;box-shadow:inset 0 1px 0 rgba(255,255,255,.34);' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M13.2 2 6 13h4.6L9.8 22 18 10.8h-4.7L13.2 2Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="rr-mobile-quick-nav__chrome-text" style="{{ $isMobileBrowser ? 'font-size:.8rem;font-weight:900;letter-spacing:.08em;line-height:1;text-transform:uppercase;white-space:nowrap;color:#fff7ed;' : '' }}">X1</span>
                        </span>
                    </div>
                    <div class="rr-mobile-quick-nav__item rr-mobile-quick-nav__item--bolao" style="{{ $isMobileBrowser ? 'display:block;position:relative;min-width:0;min-height:46px;border-radius:16px;overflow:hidden;background:linear-gradient(180deg,#4ade80 0%,#22c55e 58%,#15803d 100%);box-shadow:0 10px 22px rgba(34,197,94,.28), inset 0 1px 0 rgba(255,255,255,.32), inset 0 -3px 0 rgba(20,83,45,.36);' : '' }}">
                        <button type="button" class="rr-mobile-quick-nav__surface rr-mobile-quick-nav__surface--bolao" data-scroll-target="bolao" aria-label="Ir para bolões" style="{{ $isMobileBrowser ? 'position:absolute;inset:0;width:100%;min-height:100%;padding:0;border:0;border-radius:inherit;background:transparent;box-shadow:none;cursor:pointer;' : '' }}">
                            <i class="fas fa-trophy" aria-hidden="true"></i>
                            <span>Bolão</span>
                        </button>
                        <span class="rr-mobile-quick-nav__chrome" aria-hidden="true" style="{{ $isMobileBrowser ? 'position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:0 12px;pointer-events:none;' : '' }}">
                            <span class="rr-mobile-quick-nav__chrome-icon" style="{{ $isMobileBrowser ? 'width:22px;height:22px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;background:rgba(255,255,255,.18);color:#dcfce7;box-shadow:inset 0 1px 0 rgba(255,255,255,.34);' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M8 3h8v2h3v2c0 2.9-1.7 5.4-4.2 6.6A6 6 0 0 1 13 17.7V20h3v2H8v-2h3v-2.3a6 6 0 0 1-1.8-4.1C6.7 12.4 5 9.9 5 7V5h3V3Zm-1 4c0 1.5.7 2.8 1.8 3.7A6 6 0 0 1 8 8.5V7H7Zm10 0v1.5a6 6 0 0 1-.8 2.2A4.98 4.98 0 0 0 18 7h-1Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="rr-mobile-quick-nav__chrome-text" style="{{ $isMobileBrowser ? 'font-size:.8rem;font-weight:900;letter-spacing:.08em;line-height:1;text-transform:uppercase;white-space:nowrap;color:#f0fdf4;' : '' }}">Bolão</span>
                        </span>
                    </div>
                    <div class="rr-mobile-quick-nav__item rr-mobile-quick-nav__item--stats" style="{{ $isMobileBrowser ? 'display:block;position:relative;min-width:0;min-height:46px;border-radius:16px;overflow:hidden;grid-column:1 / -1;background:linear-gradient(180deg,#60a5fa 0%,#2563eb 58%,#1d4ed8 100%);box-shadow:0 10px 22px rgba(37,99,235,.28), inset 0 1px 0 rgba(255,255,255,.32), inset 0 -3px 0 rgba(30,64,175,.36);' : '' }}">
                        <button
                            type="button"
                            class="rr-mobile-quick-nav__surface rr-mobile-quick-nav__surface--stats"
                            data-hub-section="estatisticas"
                            data-hub-url="{{ route('hub.stats') }}"
                            aria-label="Abrir Estatísticas"
                            style="{{ $isMobileBrowser ? 'position:absolute;inset:0;width:100%;min-height:100%;padding:0;border:0;border-radius:inherit;background:transparent;box-shadow:none;cursor:pointer;' : '' }}"
                        >
                            <i class="fas fa-chart-bar" aria-hidden="true"></i>
                            <span>Estatísticas</span>
                        </button>
                        <span class="rr-mobile-quick-nav__chrome" aria-hidden="true" style="{{ $isMobileBrowser ? 'position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:0 12px;pointer-events:none;' : '' }}">
                            <span class="rr-mobile-quick-nav__chrome-icon" style="{{ $isMobileBrowser ? 'width:22px;height:22px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;background:rgba(255,255,255,.18);color:#dbeafe;box-shadow:inset 0 1px 0 rgba(255,255,255,.34);' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 19V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M12 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M19 19V8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="rr-mobile-quick-nav__chrome-text" style="{{ $isMobileBrowser ? 'font-size:.8rem;font-weight:900;letter-spacing:.08em;line-height:1;text-transform:uppercase;white-space:nowrap;color:#eff6ff;' : '' }}">Estatísticas</span>
                        </span>
                    </div>
                </div>
                <div class="rr-mobile-quick-nav__panel" data-filter-panel="x1" hidden>
                    <div class="rr-mobile-quick-nav__options">
                        <button type="button" class="rr-mobile-quick-nav__option" data-filter-target="x1" data-filter-value="20">R$ 20</button>
                        <button type="button" class="rr-mobile-quick-nav__option" data-filter-target="x1" data-filter-value="50">R$ 50</button>
                        <button type="button" class="rr-mobile-quick-nav__option" data-filter-target="x1" data-filter-value="100">R$ 100</button>
                        <button type="button" class="rr-mobile-quick-nav__option" data-filter-target="x1" data-filter-value="all">Todas as salas</button>
                    </div>
                </div>
                <div class="rr-mobile-quick-nav__panel" data-filter-panel="bolao" hidden>
                    <div class="rr-mobile-quick-nav__options">
                        <button type="button" class="rr-mobile-quick-nav__option" data-filter-target="bolao" data-filter-value="20">R$ 20</button>
                        <button type="button" class="rr-mobile-quick-nav__option" data-filter-target="bolao" data-filter-value="50">R$ 50</button>
                        <button type="button" class="rr-mobile-quick-nav__option" data-filter-target="bolao" data-filter-value="100">R$ 100</button>
                    </div>
                </div>
            </div>
            <div class="rr-competitor-tools">
                @if(!$isGroupMode)
                <div class="rr-competitor-levels" aria-label="Níveis de competidores">
                    <span class="rr-competitor-level-legend">
                        <span class="rr-competitor-level-label">Favorito</span>
                        <span class="rr-competitor-level-chip rr-competitor-level-chip--favorito" title="Favorito"></span>
                    </span>
                    <span class="rr-competitor-level-legend">
                        <span class="rr-competitor-level-label">Elite</span>
                        <span class="rr-competitor-level-chip rr-competitor-level-chip--elite" title="Elite"></span>
                    </span>
                    <span class="rr-competitor-level-legend">
                        <span class="rr-competitor-level-label">Ascendente</span>
                        <span class="rr-competitor-level-chip rr-competitor-level-chip--ascendente" title="Ascendente"></span>
                    </span>
                    <span class="rr-competitor-level-legend">
                        <span class="rr-competitor-level-label">Competidor</span>
                        <span class="rr-competitor-level-chip rr-competitor-level-chip--competidor" title="Competidor"></span>
                    </span>
                </div>
                @endif
            </div>
            <div class="rr-inicio-filter-empty" id="rrInicioFilterEmpty" hidden>
                Nenhum bloco encontrado para esse filtro. Ajuste o rodeio ou a modalidade.
            </div>
        </div>
        @forelse($homeSections as $section)
        @php
            $sectionContext = $section['context'] ?? [];
            $sectionKey = $section['section_key'] ?? ('modalidade-' . $loop->index);
            $sectionTitle = $sectionContext['modalidade_nome'] ?? ('Modalidade #' . ($sectionContext['modalidade_id'] ?? $loop->iteration));
        @endphp
        <section class="rr-inicio-modalidade-group"
            data-section-key="{{ $sectionKey }}"
            data-section-kind="arena"
            data-modalidade-id="{{ $sectionContext['modalidade_id'] ?? '' }}"
            data-rodeio-id="{{ $sectionContext['rodeio_id'] ?? '' }}"
            data-divisao="{{ $sectionContext['divisao'] ?? '' }}"
            data-entry-mode="{{ $section['mode'] ?? 'competitor' }}"
            style="{{ (int) ($primaryContext['modalidade_id'] ?? 0) !== (int) ($sectionContext['modalidade_id'] ?? 0) ? 'display:none;' : '' }}">
            <div class="rr-inicio-modalidade-group__head">
                <span class="rr-inicio-modalidade-group__kicker">
                    <i class="fas {{ !empty($section['is_group_mode']) ? 'fa-users' : 'fa-user' }}"></i>
                    Competidores
                </span>
            </div>

            @if(!empty($section['is_group_mode']))
            <div class="rr-inicio-grid-wrap rr-inicio-grid-wrap--competidores">
                <div class="rr-inicio-grid"
                    id="{{ $section['carousel_ids']['main'] }}"
                    data-carousel-auto="1">
                    @include('frontend.partials.inicial_inicio_entry_cards', [
                        'cards' => $section['cards'],
                        'entryLabelSingular' => 'grupo',
                    ])
                </div>
                <div class="rr-carousel-nav" data-carousel-nav-for="{{ $section['carousel_ids']['main'] }}" hidden>
                    <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="prev" data-carousel-target="{{ $section['carousel_ids']['main'] }}" aria-label="Voltar {{ $sectionTitle }}">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="next" data-carousel-target="{{ $section['carousel_ids']['main'] }}" aria-label="Avançar {{ $sectionTitle }}">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            @else
            <div class="rr-competitor-mobile-row">
                <div class="rr-inicio-grid-wrap rr-inicio-grid-wrap--competidores">
                    <div class="rr-inicio-grid"
                        id="{{ $section['carousel_ids']['mobile'] }}"
                        data-carousel-auto="1">
                        @include('frontend.partials.inicial_inicio_entry_cards', [
                            'cards' => $section['cards'],
                            'entryLabelSingular' => 'competidor',
                        ])
                    </div>
                    <div class="rr-carousel-nav" data-carousel-nav-for="{{ $section['carousel_ids']['mobile'] }}" hidden>
                        <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="prev" data-carousel-target="{{ $section['carousel_ids']['mobile'] }}" aria-label="Voltar {{ $sectionTitle }}">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="next" data-carousel-target="{{ $section['carousel_ids']['mobile'] }}" aria-label="Avançar {{ $sectionTitle }}">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="rr-competitor-desktop-stack">
                <div class="rr-competitor-row">
                    <div class="rr-competitor-row__label"><strong>{{ $section['desktop_primary_label'] ?? 'Favoritos e elite' }}</strong></div>
                    <div class="rr-inicio-grid-wrap rr-inicio-grid-wrap--competidores">
                        <div class="rr-inicio-grid"
                            id="{{ $section['carousel_ids']['primary'] }}"
                            data-carousel-auto="1">
                            @include('frontend.partials.inicial_inicio_entry_cards', [
                                'cards' => $section['desktop_primary_cards'],
                                'entryLabelSingular' => 'competidor',
                            ])
                        </div>
                        <div class="rr-carousel-nav" data-carousel-nav-for="{{ $section['carousel_ids']['primary'] }}" hidden>
                            <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="prev" data-carousel-target="{{ $section['carousel_ids']['primary'] }}" aria-label="Voltar {{ $sectionTitle }}">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="next" data-carousel-target="{{ $section['carousel_ids']['primary'] }}" aria-label="Avançar {{ $sectionTitle }}">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @if(($section['desktop_secondary_cards'] ?? collect())->isNotEmpty())
                <div class="rr-competitor-row">
                    <div class="rr-competitor-row__label"><strong>{{ $section['desktop_secondary_label'] ?? 'Ascendentes e competidores' }}</strong></div>
                    <div class="rr-inicio-grid-wrap rr-inicio-grid-wrap--competidores">
                        <div class="rr-inicio-grid"
                            id="{{ $section['carousel_ids']['secondary'] }}"
                            data-carousel-auto="1">
                            @include('frontend.partials.inicial_inicio_entry_cards', [
                                'cards' => $section['desktop_secondary_cards'],
                                'entryLabelSingular' => 'competidor',
                            ])
                        </div>
                        <div class="rr-carousel-nav" data-carousel-nav-for="{{ $section['carousel_ids']['secondary'] }}" hidden>
                            <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="prev" data-carousel-target="{{ $section['carousel_ids']['secondary'] }}" aria-label="Voltar {{ $sectionTitle }}">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="next" data-carousel-target="{{ $section['carousel_ids']['secondary'] }}" aria-label="Avançar {{ $sectionTitle }}">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </section>
        @empty
        <div class="alert alert-info mb-0">Nenhuma modalidade disponível para exibir na arena.</div>
        @endforelse

<!-- Seção de Bolões -->
<div class="rr-inicio-section" id="rrInicioBolaos">
    <div class="rr-inicio-section__body">
        <div class="rr-inicio-section-badge-row">
            <span class="rr-inicio-subcatalog__badge">Bolões</span>
        </div>
        <div class="rr-inicio-section-stack">
            @forelse($homeSections as $section)
            @php
                $sectionContext = $section['context'] ?? [];
                $sectionTitle = $sectionContext['modalidade_nome'] ?? ('Modalidade #' . ($loop->iteration));
                $gridId = $section['carousel_ids']['bolao'];
            @endphp
            <section class="rr-inicio-subcatalog"
                data-section-key="{{ $section['section_key'] }}"
                data-section-kind="bolao"
                data-modalidade-id="{{ $sectionContext['modalidade_id'] ?? '' }}"
                data-rodeio-id="{{ $sectionContext['rodeio_id'] ?? '' }}"
                data-divisao="{{ $sectionContext['divisao'] ?? '' }}"
                style="{{ (int) ($primaryContext['modalidade_id'] ?? 0) !== (int) ($sectionContext['modalidade_id'] ?? 0) ? 'display:none;' : '' }}">
                <div class="rr-inicio-grid-wrap rr-inicio-grid-wrap--bolaos">
                    <div class="rr-inicio-grid rr-bolao-grid"
                        id="{{ $gridId }}"
                        data-carousel-auto="1"
                        data-carousel-touch="1"
                        data-modalidade-id="{{ $sectionContext['modalidade_id'] ?? '' }}"
                        data-rodeio-id="{{ $sectionContext['rodeio_id'] ?? '' }}"
                        data-divisao="{{ $sectionContext['divisao'] ?? '' }}">
                        <div class="rr-neuro-loading">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="mt-2 text-muted">Carregando bolões de {{ $sectionTitle }}...</p>
                        </div>
                    </div>
                    <div class="rr-carousel-nav" data-carousel-nav-for="{{ $gridId }}" hidden>
                        <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="prev" data-carousel-target="{{ $gridId }}" aria-label="Voltar bolões {{ $sectionTitle }}">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="next" data-carousel-target="{{ $gridId }}" aria-label="Avançar bolões {{ $sectionTitle }}">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
            @empty
            <div class="rr-neuro-loading"><p class="text-muted">Nenhuma modalidade ativa para listar bolões.</p></div>
            @endforelse
        </div>
    </div>
</div>

<!-- Seção de Salas X1 -->
<div class="rr-inicio-section" id="rrInicioX1Rooms">
    <div class="rr-inicio-section__body">
        <div class="rr-inicio-section-badge-row">
            <span class="rr-inicio-subcatalog__badge">Salas X1</span>
        </div>
        <div class="rr-inicio-section-stack">
            @forelse($homeSections as $section)
            @php
                $sectionContext = $section['context'] ?? [];
                $sectionTitle = $sectionContext['modalidade_nome'] ?? ('Modalidade #' . ($loop->iteration));
                $gridId = $section['carousel_ids']['x1'];
            @endphp
            <section class="rr-inicio-subcatalog"
                data-section-key="{{ $section['section_key'] }}"
                data-section-kind="x1"
                data-modalidade-id="{{ $sectionContext['modalidade_id'] ?? '' }}"
                data-rodeio-id="{{ $sectionContext['rodeio_id'] ?? '' }}"
                data-divisao="{{ $sectionContext['divisao'] ?? '' }}"
                style="{{ (int) ($primaryContext['modalidade_id'] ?? 0) !== (int) ($sectionContext['modalidade_id'] ?? 0) ? 'display:none;' : '' }}">
                <div class="rr-inicio-grid-wrap rr-inicio-grid-wrap--x1rooms">
                    <div class="rr-inicio-grid rr-x1-room-grid"
                        id="{{ $gridId }}"
                        data-carousel-auto="1"
                        data-carousel-touch="1"
                        data-entry-mode="{{ $section['mode'] ?? 'competitor' }}"
                        data-entry-count="{{ (int) ($section['entry_count'] ?? 0) }}"
                        data-x1-betting-open="{{ !empty($section['x1_betting_open']) ? '1' : '0' }}"
                        data-x1-closed-message="{{ e((string) ($section['x1_closed_message'] ?? 'Apostas fechadas para esta modalidade.')) }}"
                        data-modalidade-id="{{ $sectionContext['modalidade_id'] ?? '' }}"
                        data-rodeio-id="{{ $sectionContext['rodeio_id'] ?? '' }}"
                        data-divisao="{{ $sectionContext['divisao'] ?? '' }}">
                        <div class="rr-neuro-loading">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="mt-2 text-muted">Carregando salas X1 de {{ $sectionTitle }}...</p>
                        </div>
                    </div>
                    <div class="rr-carousel-nav" data-carousel-nav-for="{{ $gridId }}" hidden>
                        <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="prev" data-carousel-target="{{ $gridId }}" aria-label="Voltar salas X1 {{ $sectionTitle }}">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="rr-carousel-nav__btn" data-carousel-scroll="next" data-carousel-target="{{ $gridId }}" aria-label="Avançar salas X1 {{ $sectionTitle }}">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
            @empty
            <div class="rr-neuro-loading"><p class="text-muted">Nenhuma modalidade ativa para listar salas X1.</p></div>
            @endforelse
        </div>
    </div>
</div>

</div>
</div>
</div>

<div class="rr-competitor-search-modal" id="rrCompetitorSearchModal" hidden>
    <div class="rr-competitor-search-modal__card" role="dialog" aria-modal="true" aria-labelledby="rrCompetitorSearchTitle">
        <div class="rr-competitor-search-modal__head">
            <div class="rr-competitor-search-modal__title" id="rrCompetitorSearchTitle">{{ $entrySearchTitle }}</div>
            <button type="button" class="rr-competitor-search-modal__close" id="rrCompetitorSearchClose" aria-label="Fechar busca">×</button>
        </div>
        <div class="rr-competitor-search-modal__body">
            <input type="text" class="rr-competitor-search-input" id="rrCompetitorSearchInput" placeholder="{{ $entrySearchPlaceholder }}" autocomplete="off">
            <div class="rr-competitor-search-results" id="rrCompetitorSearchResults"></div>
        </div>
    </div>
</div>

{{-- =============================================
    BETSLIP - Cupom de Apostas X1
    Web: centralizado | Mobile: acima do menu
    ============================================= --}}
<div class="rr-betslip-overlay" id="rrInicioSlip" hidden>
    <div class="rr-betslip-backdrop" id="rrBetslipBackdrop"></div>
    <section class="rr-betslip rr-betslip--intro" data-qa="bet-slip">
        <header class="rr-betslip__header rr-betslip__header--intro">
            <div class="rr-betslip__header-copy">
                <span class="rr-betslip__eyebrow">Arena X1</span>
                <h3 class="rr-betslip__title">Bilhete X1</h3>
                <p class="rr-betslip__subtitle" id="rrBetslipSubtitle">Escolha a entrada e confirme.</p>
            </div>
            <div class="rr-betslip__header-right rr-betslip__header-right--intro">
                @if(($pendingPayments['count'] ?? 0) > 0)
                <button type="button" class="rr-betslip__icon-btn rr-betslip__pending-btn" id="rrPendingPaymentsBtn" aria-label="Pagamentos pendentes">
                    <i class="fas fa-clock"></i>
                    <span>Pendente ({{ $pendingPayments['count'] }})</span>
                </button>
                @endif
                <button type="button" class="rr-betslip__close" id="rrInicioSlipClose" aria-label="Fechar">×</button>
            </div>
        </header>

        <div class="rr-betslip__bets" id="rrBetslipBets">
            <div class="rr-betslip__card">
                <div class="rr-betslip__card-main rr-betslip__card-main--intro">
                    <div class="rr-betslip__hero">
                        <div class="rr-betslip__hero-top">
                            <span class="rr-betslip__hero-chip" id="rrBetslipHeroChip">Competidor escolhido</span>
                            <span class="rr-betslip__hero-kicker">Duelo X1</span>
                        </div>
                        <div class="rr-betslip__hero-name" id="rrInicioSlipCompetitor">Competidor</div>
                        <div class="rr-betslip__facts">
                            <div class="rr-betslip__fact rr-betslip__fact--odds" id="rrBetslipOdds">
                                <span class="rr-betslip__fact-label">Paga</span>
                                <strong class="rr-betslip__fact-value rr-betslip__odds-value">1,90x</strong>
                            </div>
                            <div class="rr-betslip__fact">
                                <span class="rr-betslip__fact-label">Entrada</span>
                                <strong class="rr-betslip__fact-value" id="rrBetslipStakePreview">Escolha</strong>
                            </div>
                            <div class="rr-betslip__fact">
                                <span class="rr-betslip__fact-label">Retorno</span>
                                <strong class="rr-betslip__fact-value rr-betslip__fact-value--success" id="rrBetslipReturn">R$0,00</strong>
                            </div>
                        </div>
                    </div>

                    <div class="rr-betslip__status-panel" id="rrBetslipStatusPanel">
                        <span class="rr-betslip__status-kicker" id="rrBetslipStatusKicker">Passo 1</span>
                        <strong class="rr-betslip__status-title" id="rrBetslipStatusTitle">Escolha o valor da sua entrada</strong>
                        <span class="rr-betslip__status-text" id="rrBetslipStatusText">Depois disso, buscamos uma sala com esse mesmo valor. Se não existir, criamos uma nova para você.</span>
                    </div>

                    <div class="rr-betslip__stake-area rr-betslip__stake-area--intro" id="rrInicioSlipAmountSection">
                        <div class="rr-betslip__stake-heading">
                            <span class="rr-betslip__section-step">Passo 1</span>
                            <h4 class="rr-betslip__section-callout">Escolha o valor da entrada</h4>
                            <p class="rr-betslip__section-copy">Toque em um valor abaixo para liberar a continuação.</p>
                        </div>
                        <div class="rr-betslip__stake-buttons">
                            @foreach ($x1TestEntryOptions as $entryOption)
                            <button type="button" class="rr-betslip__stake-btn rr-inicio-slip__stake" data-value="{{ number_format($entryOption, 2, '.', '') }}">
                                <span>R${{ number_format($entryOption, 2, ',', '.') }}</span>
                            </button>
                            @endforeach
                            <button type="button" class="rr-betslip__stake-btn rr-betslip__stake-btn--max rr-inicio-slip__stake rr-inicio-slip__stake--custom" data-value="custom">Outro</button>
                        </div>
                    </div>

                    <div class="rr-betslip__section rr-betslip__section--matches" id="rrInicioSlipMatchesSection" hidden>
                        <h4 class="rr-betslip__section-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            Salas disponíveis
                        </h4>
                        <div class="rr-betslip__matches-list" id="rrInicioSlipMatchesList"></div>
                        <button type="button" class="rr-betslip__btn rr-betslip__btn--create" id="rrInicioSlipCreateNew">
                            <i class="fas fa-plus-circle"></i> Criar nova sala
                        </button>
                    </div>

                    <div class="rr-betslip__loading" id="rrInicioSlipLoading" hidden>
                        <div class="rr-betslip__spinner"></div>
                        <span>Buscando sala compatível...</span>
                    </div>
                </div>
            </div>
        </div>

        <footer class="rr-betslip__footer rr-betslip__footer--intro">
            <div class="rr-betslip__return-row rr-betslip__return-row--intro">
                <div class="rr-betslip__return-copy">
                    <span class="rr-betslip__return-label">Retorno potencial</span>
                    <span class="rr-betslip__return-help" id="rrBetslipActionHint">Primeiro escolha um valor para continuar.</span>
                </div>
                <span class="rr-betslip__return-value" id="rrBetslipReturnFooter">R$0,00</span>
            </div>
            <button type="button" class="rr-betslip__place-btn" id="rrBetslipPlaceBtn">
                <span class="rr-betslip__place-text">Escolha um valor</span>
                <div class="rr-betslip__place-processing" hidden>
                    <span id="rrBetslipProcessingText">Buscando sala...</span>
                    <div class="rr-betslip__spinner rr-betslip__spinner--sm"></div>
                </div>
            </button>
        </footer>
    </section>
</div>

{{-- Modal de Valor Personalizado --}}
<div class="rr-inicio-custom-modal" id="rrInicioCustomModal" hidden>
    <div class="rr-betslip-backdrop" data-custom-close></div>
    <section class="rr-inicio-custom-modal__card" role="dialog" aria-modal="true" aria-labelledby="rrInicioCustomTitle">
        <header class="rr-inicio-custom-modal__header">
            <div class="rr-inicio-custom-modal__header-copy">
                <span class="rr-inicio-custom-modal__eyebrow">Valor personalizado</span>
                <h4 class="rr-inicio-custom-modal__title" id="rrInicioCustomTitle">Criar sala no seu valor</h4>
                <p class="rr-inicio-custom-modal__subtitle">Escolha a entrada e confirme.</p>
            </div>
            <button type="button" class="rr-inicio-custom-modal__close" id="rrInicioCustomCloseTop" data-custom-close aria-label="Fechar">×</button>
        </header>

        <div class="rr-inicio-custom-modal__body">
            <div class="rr-inicio-custom-modal__summary">
                <div class="rr-inicio-custom-modal__summary-head">
                    <span class="rr-inicio-custom-modal__chip">Entrada livre</span>
                    <span class="rr-inicio-custom-modal__chip rr-inicio-custom-modal__chip--soft">Acima de R$100</span>
                </div>
                <div class="rr-inicio-custom-modal__name" id="rrInicioCustomName">Competidor</div>
                <div class="rr-inicio-custom-modal__facts">
                    <div class="rr-inicio-custom-modal__fact">
                        <span class="rr-inicio-custom-modal__fact-label">Faixa</span>
                        <strong class="rr-inicio-custom-modal__fact-value">100+</strong>
                    </div>
                    <div class="rr-inicio-custom-modal__fact">
                        <span class="rr-inicio-custom-modal__fact-label">Máximo</span>
                        <strong class="rr-inicio-custom-modal__fact-value">{{ 'R$' . number_format($x1MaxEntry, 2, ',', '.') }}</strong>
                    </div>
                    <div class="rr-inicio-custom-modal__fact">
                        <span class="rr-inicio-custom-modal__fact-label">Retorno</span>
                        <strong class="rr-inicio-custom-modal__fact-value rr-inicio-custom-modal__fact-value--success" id="rrInicioCustomReturn">R$0,00</strong>
                    </div>
                </div>
                <div class="rr-inicio-custom-modal__matches" id="rrInicioCustomMatches"></div>
            </div>

            <div class="rr-inicio-custom-modal__form">
                <label class="rr-inicio-custom-modal__label" for="rrInicioCustomInput">Digite o valor da entrada acima de R$100,00</label>
                <div class="rr-inicio-custom-modal__input-group">
                    <span class="rr-inicio-custom-modal__prefix">R$</span>
                    <input type="number" class="rr-inicio-custom-modal__input" id="rrInicioCustomInput"
                           placeholder="150,00" min="{{ number_format($x1CustomMinEntry, 2, '.', '') }}" max="{{ number_format($x1MaxEntry, 2, '.', '') }}" step="0.01"/>
                </div>
                <div class="rr-inicio-custom-modal__quick">
                    <button type="button" class="rr-inicio-custom-modal__quick-btn" data-custom-value="150">R$150</button>
                    <button type="button" class="rr-inicio-custom-modal__quick-btn" data-custom-value="250">R$250</button>
                    <button type="button" class="rr-inicio-custom-modal__quick-btn" data-custom-value="500">R$500</button>
                </div>
            </div>
        </div>

        <footer class="rr-inicio-custom-modal__footer">
            <button type="button" class="rr-inicio-custom-modal__back" id="rrInicioCustomClose" data-custom-close>Voltar</button>
            <button type="button" class="rr-inicio-custom-modal__submit" id="rrInicioCustomSubmit">
                <i class="fas fa-rocket"></i> Criar sala
            </button>
        </footer>
    </section>
</div>

{{-- PIX Payment Modal --}}
<div class="rr-inicio-pix" id="rrInicioPixModal" hidden>
    <div class="rr-betslip-backdrop"></div>
    <div class="rr-inicio-pix__card">
        <h4 class="rr-inicio-pix__title"><i class="fas fa-qrcode"></i> Pagamento PIX</h4>
        <div class="rr-inicio-pix__qr-wrap">
            <img id="rrInicioPixImage" class="rr-inicio-pix__qr" alt="QR Code PIX">
        </div>
        <textarea id="rrInicioPixCode" class="rr-inicio-pix__code" rows="3" readonly></textarea>
        <div class="rr-inicio-pix__actions">
            <button type="button" class="rr-inicio-pix__btn rr-inicio-pix__btn--copy" id="rrInicioPixCopy">
                <i class="fas fa-copy"></i> Copiar PIX
            </button>
            <button type="button" class="rr-inicio-pix__btn rr-inicio-pix__btn--check" id="rrInicioPixCheck">
                <i class="fas fa-sync-alt"></i> Verificar
            </button>
        </div>
        <div class="rr-inicio-pix__status" id="rrInicioPixStatus">Aguardando confirmação...</div>
        <button type="button" class="rr-betslip__btn rr-betslip__btn--cancel" id="rrInicioPixClose">Fechar</button>
    </div>
</div>

{{-- =============================================
    BILHETE DE ENTRADA - Join X1 Room
    Aberto ao clicar "Entrar na Sala" no card X1
    ============================================= --}}
<div class="rr-betslip-overlay" id="rrJoinSlip" hidden>
    <div class="rr-betslip-backdrop" id="rrJoinSlipBackdrop"></div>
    <section class="rr-betslip rr-joinslip" data-qa="join-slip">
        {{-- Header --}}
        <header class="rr-betslip__header">
            <div class="rr-betslip__header-left">
                <button type="button" class="rr-betslip__trash" id="rrJoinSlipClose" aria-label="Fechar">
                    <svg width="16" height="16" fill="none" viewBox="0 0 16 16"><path fill="currentColor" d="M7.388 6.716v4.281c0 .897-1.345.897-1.345 0V6.716c0-.897 1.345-.897 1.345 0m2.57 4.281V6.716c0-.897-1.346-.897-1.346 0v4.281c0 .897 1.345.897 1.345 0"/><path fill="currentColor" fill-rule="evenodd" d="M10.813 2.862a1.53 1.53 0 0 0-1.529-1.529H6.716a1.53 1.53 0 0 0-1.53 1.53v.183h-3.18a.673.673 0 0 0 0 1.345h.665l.74 8.873a1.53 1.53 0 0 0 1.524 1.403h6.13a1.53 1.53 0 0 0 1.524-1.403l.74-8.873h.665a.673.673 0 1 0 0-1.345h-3.18zm-1.345.184v-.184a.183.183 0 0 0-.184-.183H6.716a.183.183 0 0 0-.184.183v.184zm2.51 1.345H4.022l.73 8.762a.183.183 0 0 0 .183.168h6.13a.183.183 0 0 0 .183-.168z" clip-rule="evenodd"/></svg>
                </button>
                <div class="rr-betslip__divider-v"></div>
                <h3 class="rr-betslip__title">Entrar na Sala X1</h3>
            </div>
        </header>

        {{-- Room Info --}}
        <div class="rr-betslip__bets" id="rrJoinSlipBets">
            <div class="rr-betslip__card">
                <div class="rr-betslip__card-main">
                    {{-- VS Header --}}
                    <div class="rr-joinslip__vs-header">
                        <div class="rr-joinslip__player">
                            <img id="rrJoinSlipHostAvatar" class="rr-joinslip__avatar" src="" alt="">
                            <span class="rr-joinslip__player-name" id="rrJoinSlipHostName">Criador</span>
                        </div>
                        <span class="rr-joinslip__vs-badge">VS</span>
                        <div class="rr-joinslip__player">
                            <div class="rr-joinslip__avatar rr-joinslip__avatar--you">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="rr-joinslip__player-name rr-joinslip__player-name--you">Você</span>
                        </div>
                    </div>

                    {{-- Prize Highlight --}}
                    <div class="rr-joinslip__prize-highlight">
                        <div class="rr-joinslip__prize-label">
                            <i class="fas fa-gem"></i> Prêmio total
                        </div>
                        <div class="rr-joinslip__prize-value" id="rrJoinSlipPremioBig">R$0</div>
                        <div class="rr-joinslip__prize-note">
                            Ganha quem chegar mais longe na prova!
                        </div>
                    </div>

                    {{-- Room details --}}
                    <div class="rr-joinslip__details">
                        <div class="rr-joinslip__detail-row">
                            <span class="rr-joinslip__detail-label"><i class="fas fa-trophy"></i> Modalidade</span>
                            <span class="rr-joinslip__detail-value" id="rrJoinSlipModalidade">—</span>
                        </div>
                        <div class="rr-joinslip__detail-row">
                            <span class="rr-joinslip__detail-label"><i class="fas fa-horse"></i> Competidor do Criador</span>
                            <span class="rr-joinslip__detail-value" id="rrJoinSlipHostCompetitor">—</span>
                        </div>
                        <div class="rr-joinslip__detail-row">
                            <span class="rr-joinslip__detail-label"><i class="fas fa-coins"></i> Entrada</span>
                            <span class="rr-joinslip__detail-value rr-joinslip__detail-value--entry" id="rrJoinSlipEntrada">R$0</span>
                        </div>
                    </div>

                    {{-- Competitor selector --}}
                    <div class="rr-joinslip__selector">
                        <label class="rr-joinslip__selector-label">Seu Competidor / Grupo</label>
                        <button type="button" class="rr-joinslip__selector-btn" id="rrJoinSlipPickBtn">
                            <div class="rr-joinslip__selector-placeholder" id="rrJoinSlipPickPlaceholder">
                                <i class="fas fa-search"></i>
                                <span>Escolher Competidor...</span>
                            </div>
                            <div class="rr-joinslip__selector-selected" id="rrJoinSlipPickSelected" hidden>
                                <img class="rr-joinslip__selector-img" id="rrJoinSlipPickImg" src="" alt="">
                                <span class="rr-joinslip__selector-name" id="rrJoinSlipPickName"></span>
                                <i class="fas fa-pen rr-joinslip__selector-edit"></i>
                            </div>
                        </button>
                    </div>

                    {{-- Loading --}}
                    <div class="rr-betslip__loading" id="rrJoinSlipLoading" hidden>
                        <div class="rr-betslip__spinner"></div>
                        <span>Processando...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <footer class="rr-betslip__footer">
            <div class="rr-betslip__return-row">
                <span class="rr-betslip__return-label">Você paga</span>
                <span class="rr-betslip__return-value rr-joinslip__pay-value" id="rrJoinSlipPayValue">R$0</span>
            </div>
            <button type="button" class="rr-betslip__place-btn" id="rrJoinSlipConfirmBtn" disabled>
                <span class="rr-joinslip__confirm-text" id="rrJoinSlipConfirmText">SELECIONE UM COMPETIDOR</span>
                <div class="rr-betslip__place-processing" id="rrJoinSlipProcessing" hidden>
                    <span>ENTRANDO...</span>
                    <div class="rr-betslip__spinner rr-betslip__spinner--sm"></div>
                </div>
            </button>
        </footer>
    </section>
</div>

<!-- Pagamentos Pendentes -->
<div class="rr-betslip-overlay" id="rrPendingModal" hidden>
    <div class="rr-betslip-backdrop" id="rrPendingBackdrop"></div>
    <section class="rr-betslip rr-joinslip" style="max-width:520px;">
        <header class="rr-betslip__header">
            <div class="rr-betslip__header-left">
                <button type="button" class="rr-betslip__trash" id="rrPendingClose" aria-label="Fechar">
                    <i class="fas fa-times"></i>
                </button>
                <div class="rr-betslip__divider-v"></div>
                <h3 class="rr-betslip__title">Pagamentos Pendentes</h3>
                <span class="rr-betslip__counter" id="rrPendingCounter">0</span>
            </div>
        </header>
        <div class="rr-betslip__bets" id="rrPendingList" style="max-height:360px;overflow:auto;padding:12px 10px;"></div>
        <footer class="rr-betslip__footer">
            <button type="button" class="rr-betslip__place-btn" id="rrPendingCloseBtn">OK, entendi</button>
        </footer>
    </section>
</div>

<div class="rr-competitor-search-modal" id="rrGroupMembersModal" hidden>
    <div class="rr-competitor-search-modal__card" role="dialog" aria-modal="true" aria-labelledby="rrGroupMembersTitle">
        <div class="rr-competitor-search-modal__head">
            <div class="rr-competitor-search-modal__title" id="rrGroupMembersTitle">Integrantes do grupo</div>
            <button type="button" class="rr-competitor-search-modal__close" id="rrGroupMembersClose" aria-label="Fechar grupo">×</button>
        </div>
        <div class="rr-competitor-search-modal__body">
            <div class="rr-competitor-search-results" id="rrGroupMembersResults"></div>
        </div>
    </div>
</div>

{{-- =============================================
    MODAL: Escolher Competidor / Grupo
    ============================================= --}}
<div class="rr-picker-overlay" id="rrCompetitorPicker" hidden>
    <div class="rr-betslip-backdrop" id="rrPickerBackdrop"></div>
    <div class="rr-picker">
        <header class="rr-picker__header">
            <h3 class="rr-picker__title"><i class="fas fa-users"></i> Escolher Competidor</h3>
            <button type="button" class="rr-picker__close" id="rrPickerClose">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="rr-picker__search">
            <i class="fas fa-search rr-picker__search-icon"></i>
            <input type="text" class="rr-picker__search-input" id="rrPickerSearch" placeholder="Buscar..." autocomplete="off">
        </div>
        <div class="rr-picker__list" id="rrPickerList">
            <div class="rr-betslip__loading">
                <div class="rr-betslip__spinner"></div>
                <span>Carregando competidores...</span>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   🎰 BETSLIP - Cupom de Apostas X1
   Inspirado em design de slip de apostas esportivas
   Web: centralizado | Mobile: acima do menu
   ============================================ */

/* --- Overlay & Backdrop --- */
.rr-betslip-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.rr-betslip-overlay[hidden] { display: none !important; }

.rr-betslip-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.8);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

/* --- Main Betslip Card --- */
.rr-betslip {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 460px;
    max-height: 84vh;
    background: #111827;
    border-radius: 18px;
    overflow: hidden;
    box-shadow:
        0 0 0 1px rgba(249, 115, 22, 0.2),
        0 20px 60px rgba(0, 0, 0, 0.6),
        0 0 40px rgba(249, 115, 22, 0.08);
    animation: betslipSlideIn 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.rr-betslip--intro {
    width: min(560px, 100%);
    max-width: 560px;
    max-height: min(86vh, 920px);
    border-radius: 24px;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.16), transparent 38%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(10, 15, 27, 0.98) 100%);
}

.rr-betslip--intro .rr-betslip__bets {
    overflow: visible;
}

@keyframes betslipSlideIn {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* --- Header --- */
.rr-betslip__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    background: #0d1117;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}

.rr-betslip__header--intro {
    align-items: flex-start;
    gap: 16px;
    padding: 18px 20px 16px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.03) 0%, rgba(13, 17, 23, 0.16) 100%);
}

.rr-betslip__header-copy {
    min-width: 0;
    flex: 1;
}

.rr-betslip__header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.rr-betslip__header-right {
    display: flex;
    align-items: center;
    gap: 4px;
}

.rr-betslip__header-right--intro {
    gap: 10px;
    flex-shrink: 0;
}

.rr-betslip__eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.14);
    border: 1px solid rgba(249, 115, 22, 0.28);
    color: #fdba74;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.rr-betslip__trash {
    background: none;
    border: 0;
    color: #64748b;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    transition: color 0.2s;
}
.rr-betslip__trash:hover { color: #ef4444; }

.rr-betslip__divider-v {
    width: 1px;
    height: 20px;
    background: rgba(255,255,255,0.1);
    flex-shrink: 0;
}

.rr-betslip__title {
    font-weight: 700;
    font-size: 13px;
    color: #e2e8f0;
    margin: 0;
    line-height: 1.2;
    white-space: nowrap;
}

.rr-betslip--intro .rr-betslip__title {
    font-size: 24px;
    font-weight: 800;
    color: #f8fafc;
    white-space: normal;
}

.rr-betslip__subtitle {
    margin: 8px 0 0;
    color: rgba(226, 232, 240, 0.78);
    font-size: 13px;
    line-height: 1.55;
    max-width: 440px;
}

.rr-betslip__counter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 26px;
    height: 22px;
    border-radius: 20px;
    border: 2px solid rgba(148,163,184,0.3);
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    padding: 0 6px;
}

.rr-betslip__icon-btn {
    background: transparent;
    border: 0;
    cursor: pointer;
    color: #64748b;
    min-width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 8px;
    padding: 6px 8px;
    transition: background 0.2s, color 0.2s;
}
.rr-betslip__pending-btn {
    background: linear-gradient(135deg, rgba(234,179,8,0.18), rgba(251,191,36,0.12));
    color: #d97706;
    font-weight: 700;
    border: 1px solid rgba(234,179,8,0.35);
    border-radius: 10px;
}
.rr-betslip__pending-btn:hover {
    color: #f59e0be6;
    border-color: rgba(234,179,8,0.55);
    background: linear-gradient(135deg, rgba(234,179,8,0.24), rgba(251,191,36,0.18));
}
.rr-betslip__icon-btn:hover {
    background: rgba(255,255,255,0.05);
    color: #cbd5e1;
}

.rr-betslip__close {
    min-width: 42px;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(255, 255, 255, 0.04);
    color: #f8fafc;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, border-color 0.2s, transform 0.2s;
}

.rr-betslip__close:hover {
    background: rgba(239, 68, 68, 0.14);
    border-color: rgba(239, 68, 68, 0.32);
    transform: translateY(-1px);
}

/* --- Bets Area (scrollable) --- */
.rr-betslip__bets {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}
.rr-betslip__card {
    border-bottom: 1px solid rgba(255,255,255,0.04);
}
.rr-betslip__card-main {
    padding: 14px 14px 10px;
}

.rr-betslip__card-main--intro {
    padding: 18px 20px 16px;
}

.rr-betslip__hero {
    margin-bottom: 16px;
    padding: 18px;
    border-radius: 18px;
    border: 1px solid rgba(249, 115, 22, 0.22);
    background:
        linear-gradient(135deg, rgba(249, 115, 22, 0.12), rgba(15, 23, 42, 0.08)),
        rgba(15, 23, 42, 0.68);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
}

.rr-betslip__hero-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}

.rr-betslip__hero-chip,
.rr-betslip__hero-kicker {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rr-betslip__hero-chip {
    background: rgba(255, 255, 255, 0.06);
    color: #e2e8f0;
}

.rr-betslip__hero-kicker {
    background: rgba(34, 197, 94, 0.14);
    color: #86efac;
}

.rr-betslip__hero-name {
    font-size: 28px;
    font-weight: 900;
    line-height: 1.05;
    color: #fff;
    margin-bottom: 8px;
    letter-spacing: -0.02em;
}

.rr-betslip__hero-note {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(226, 232, 240, 0.76);
    font-size: 14px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}

.rr-betslip__facts {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.rr-betslip__fact {
    padding: 12px 12px 11px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(2, 6, 23, 0.38);
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.rr-betslip__fact-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.82);
}

.rr-betslip__fact-value {
    font-size: 20px;
    font-weight: 900;
    line-height: 1;
    color: #f8fafc;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-betslip__fact-value--success {
    color: #86efac;
}

/* --- Leg Info --- */
.rr-betslip__leg {
    margin-bottom: 12px;
}
.rr-betslip__leg-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 6px;
}
.rr-betslip__leg-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    flex: 1;
}
.rr-betslip__sport-icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
}
.rr-betslip__selection-label {
    font-size: 14px;
    font-weight: 700;
    color: #f1f5f9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rr-betslip__leg-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.rr-betslip__odds {
    display: flex;
    align-items: center;
    gap: 4px;
}
.rr-betslip__odds-value {
    font-size: 14px;
    font-weight: 800;
    color: #22c55e;
}
.rr-betslip__remove-leg {
    background: none;
    border: 0;
    color: #475569;
    cursor: pointer;
    padding: 2px;
    display: flex;
    transition: color 0.2s;
}
.rr-betslip__remove-leg:hover { color: #ef4444; }

.rr-betslip__market-label {
    font-size: 12px;
    color: #f59e0be6;
    font-weight: 500;
    margin-bottom: 4px;
}
.rr-betslip__event-info {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #94a3b8;
}
.rr-betslip__participant {
    font-weight: 500;
}
.rr-betslip__vs {
    color: #475569;
    font-weight: 700;
    font-size: 11px;
}

/* --- Stake Area --- */
.rr-betslip__stake-area {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0;
}

.rr-betslip__status-panel {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 16px 18px;
    margin-bottom: 14px;
    border-radius: 18px;
    border: 1px solid rgba(59, 130, 246, 0.18);
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.72), rgba(15, 23, 42, 0.92));
}

.rr-betslip__status-kicker {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #93c5fd;
}

.rr-betslip__status-title {
    font-size: 18px;
    font-weight: 800;
    line-height: 1.2;
    color: #f8fafc;
}

.rr-betslip__status-text {
    font-size: 13px;
    line-height: 1.55;
    color: rgba(226, 232, 240, 0.76);
}

.rr-betslip__stake-area--intro {
    display: block;
    padding: 18px;
    border-radius: 18px;
    background: rgba(2, 6, 23, 0.42);
    border: 1px solid rgba(148, 163, 184, 0.12);
}

.rr-betslip__stake-heading {
    margin-bottom: 14px;
}

.rr-betslip__section-step {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.14);
    color: #f59e0be6;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.rr-betslip__section-callout {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #f8fafc;
}

.rr-betslip__section-copy {
    margin: 6px 0 0;
    font-size: 13px;
    line-height: 1.5;
    color: rgba(226, 232, 240, 0.72);
}

.rr-betslip__stake-input {
    width: 100px;
    flex-shrink: 0;
    background: #0d1117;
    border: 1px solid rgba(148,163,184,0.15);
    border-radius: 6px;
    padding: 10px 12px;
    color: #f1f5f9;
    font-size: 15px;
    font-weight: 700;
    text-align: right;
    outline: none;
    transition: border-color 0.2s;
}
.rr-betslip__stake-input::placeholder { color: #475569; }
.rr-betslip__stake-input:focus { border-color: #f59e0be6; }

.rr-betslip__stake-buttons {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    width: 100%;
}
.rr-betslip__stake-btn {
    min-width: 0;
    min-height: 56px;
    padding: 10px 8px;
    background: rgba(15, 23, 42, 0.68);
    border: 1px solid rgba(148,163,184,0.18);
    border-radius: 14px;
    color: #e2e8f0;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s, box-shadow 0.15s;
    text-align: center;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
}
.rr-betslip__stake-btn:hover {
    background: rgba(249,115,22,0.08);
    border-color: rgba(249,115,22,0.4);
    color: #f59e0be6;
    transform: translateY(-1px);
}
.rr-betslip__stake-btn.is-active {
    background: linear-gradient(135deg, rgba(34,197,94,0.18), rgba(15,118,110,0.18));
    border-color: rgba(34,197,94,0.72);
    color: #dcfce7;
    box-shadow: 0 10px 24px rgba(34, 197, 94, 0.16);
}
.rr-betslip__stake-btn--max {
    background: rgba(49, 46, 129, 0.34);
    border-color: rgba(139,92,246,0.4);
    color: #a78bfa;
    font-weight: 800;
    letter-spacing: 0.5px;
}
.rr-betslip__stake-btn--max:hover {
    background: rgba(139,92,246,0.1);
    border-color: #8b5cf6;
    color: #c4b5fd;
}
.rr-inicio-slip__stake--custom {
    background:
        linear-gradient(180deg, rgba(74, 222, 128, 0.98) 0%, rgba(34, 197, 94, 0.98) 52%, rgba(21, 128, 61, 1) 100%);
    border-color: rgba(74, 222, 128, 0.95);
    color: #f0fdf4;
    text-shadow: 0 1px 0 rgba(5, 46, 22, 0.5);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.34),
        inset 0 -3px 0 rgba(20, 83, 45, 0.75),
        0 12px 24px rgba(22, 163, 74, 0.28),
        0 4px 0 rgba(20, 83, 45, 0.9);
}
.rr-inicio-slip__stake--custom:hover {
    background:
        linear-gradient(180deg, rgba(134, 239, 172, 1) 0%, rgba(34, 197, 94, 1) 48%, rgba(21, 128, 61, 1) 100%);
    border-color: #bbf7d0;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.4),
        inset 0 -3px 0 rgba(20, 83, 45, 0.82),
        0 16px 28px rgba(34, 197, 94, 0.34),
        0 5px 0 rgba(20, 83, 45, 0.92);
}
.rr-inicio-slip__stake--custom.is-active {
    background:
        linear-gradient(180deg, rgba(187, 247, 208, 1) 0%, rgba(34, 197, 94, 1) 45%, rgba(22, 101, 52, 1) 100%);
    border-color: #dcfce7;
    color: #ffffff;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.46),
        inset 0 -4px 0 rgba(20, 83, 45, 0.9),
        0 18px 32px rgba(34, 197, 94, 0.38),
        0 6px 0 rgba(20, 83, 45, 0.95);
}

/* --- Sections (matches, loading) --- */
.rr-betslip__section {
    padding: 12px 14px;
}

.rr-betslip__section--matches {
    padding: 18px 0 0;
}

.rr-betslip__section[hidden] { display: none !important; }
.rr-betslip__section-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
    margin-bottom: 10px;
}
.rr-betslip__matches-list {
    max-height: 240px;
    overflow-y: auto;
    margin-bottom: 10px;
}
.rr-betslip__match-card,
.rr-inicio-slip__match-card {
    background: rgba(15,23,42,0.6);
    border: 1px solid rgba(148,163,184,0.1);
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, transform 0.15s;
}
.rr-betslip__match-card:hover,
.rr-inicio-slip__match-card:hover {
    background: rgba(15,23,42,0.9);
    border-color: rgba(34,197,94,0.5);
    transform: translateX(3px);
}
.rr-inicio-slip__match-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.rr-inicio-slip__match-competitor {
    font-size: 13px;
    font-weight: 600;
    color: #f1f5f9;
}
.rr-inicio-slip__match-badge {
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    color: #1e293b;
    font-size: 9px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.rr-inicio-slip__match-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    color: #64748b;
}
.rr-inicio-slip__match-multiplier {
    font-size: 15px;
    font-weight: 800;
    color: #22c55e;
}

/* --- Buttons (shared) --- */
.rr-betslip__btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.rr-betslip__btn--create {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 4px 12px rgba(16,185,129,0.25);
}
.rr-betslip__btn--create:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(16,185,129,0.35);
}
.rr-betslip__btn--cancel {
    background: rgba(148,163,184,0.1);
    color: #94a3b8;
    margin-top: 8px;
}
.rr-betslip__btn--cancel:hover {
    background: rgba(148,163,184,0.2);
    color: #cbd5e1;
}

/* --- Loading --- */
.rr-betslip__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 32px 20px;
    color: #f59e0be6;
    font-size: 13px;
    font-weight: 500;
}
.rr-betslip__loading[hidden] { display: none !important; }
.rr-betslip__spinner {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2.5px solid rgba(249,115,22,0.2);
    border-top-color: #f59e0be6;
    animation: betslipSpin 0.8s linear infinite;
}
.rr-betslip__spinner--sm {
    width: 14px;
    height: 14px;
    border-width: 2px;
}
@keyframes betslipSpin { to { transform: rotate(360deg); } }

/* --- Footer --- */
.rr-betslip__footer {
    padding: 12px 14px;
    background: #0d1117;
    border-top: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}

.rr-betslip__footer--intro {
    padding: 16px 20px 20px;
    background: linear-gradient(180deg, rgba(13, 17, 23, 0.88) 0%, rgba(2, 6, 23, 0.96) 100%);
}

.rr-betslip__return-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.rr-betslip__return-row--intro {
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 14px;
}

.rr-betslip__return-copy {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.rr-betslip__return-label {
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
}

.rr-betslip__return-help {
    font-size: 12px;
    line-height: 1.45;
    color: rgba(226, 232, 240, 0.66);
}

.rr-betslip__return-value {
    font-size: 15px;
    font-weight: 800;
    color: #22c55e;
}

.rr-betslip__place-btn {
    width: 100%;
    padding: 14px;
    border: 0;
    border-radius: 8px;
    background: linear-gradient(135deg, #f59e0be6, #ea580c);
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(249,115,22,0.35);
}
.rr-betslip__place-btn:hover {
    background: linear-gradient(135deg, #ea580c, #c2410c);
    transform: translateY(-1px);
    box-shadow: 0 6px 24px rgba(249,115,22,0.5);
}
.rr-betslip__place-btn:disabled {
    opacity: 0.5;
    cursor: default;
    transform: none;
}
.rr-betslip__place-processing[hidden] { display: none; }
.rr-betslip__place-processing {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* --- Desktop intro slip: mais horizontal, menos texto, sem depender de scroll --- */
@media (min-width: 701px) {
    .rr-betslip--intro {
        width: min(840px, 92vw);
        max-width: 840px;
        max-height: min(78vh, 600px);
    }

    .rr-betslip--intro .rr-betslip__header--intro {
        align-items: center;
        padding: 14px 20px 12px;
    }

    .rr-betslip--intro .rr-betslip__subtitle,
    .rr-betslip--intro .rr-betslip__status-text,
    .rr-betslip--intro .rr-betslip__section-copy,
    .rr-betslip--intro .rr-betslip__return-help {
        display: none;
    }

    .rr-betslip--intro .rr-betslip__bets {
        overflow-y: auto;
        min-height: 0;
        scrollbar-width: none;
    }

    .rr-betslip--intro .rr-betslip__bets::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    .rr-betslip--intro .rr-betslip__card-main--intro {
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(300px, 0.98fr);
        grid-template-areas:
            "hero status"
            "hero stake"
            "hero matches"
            "hero loading";
        gap: 12px;
        padding: 12px 20px 12px;
        align-items: start;
    }

    .rr-betslip--intro .rr-betslip__hero {
        grid-area: hero;
        margin-bottom: 0;
        padding: 14px 16px;
        border-radius: 20px;
    }

    .rr-betslip--intro .rr-betslip__hero-top {
        margin-bottom: 8px;
    }

    .rr-betslip--intro .rr-betslip__hero-chip,
    .rr-betslip--intro .rr-betslip__hero-kicker {
        min-height: 24px;
        padding: 0 10px;
        font-size: 10px;
    }

    .rr-betslip--intro .rr-betslip__hero-name {
        font-size: 24px;
        margin-bottom: 4px;
    }

    .rr-betslip--intro .rr-betslip__hero-note {
        margin-bottom: 8px;
        font-size: 12px;
    }

    .rr-betslip--intro .rr-betslip__facts {
        gap: 8px;
    }

    .rr-betslip--intro .rr-betslip__fact {
        padding: 11px 12px 10px;
    }

    .rr-betslip--intro .rr-betslip__fact-value {
        font-size: 16px;
    }

    .rr-betslip--intro .rr-betslip__status-panel {
        grid-area: status;
        margin-bottom: 0;
        padding: 12px 14px;
        border-radius: 16px;
    }

    .rr-betslip--intro .rr-betslip__status-title {
        font-size: 15px;
        line-height: 1.18;
    }

    .rr-betslip--intro .rr-betslip__stake-area--intro {
        grid-area: stake;
        padding: 12px 14px;
        border-radius: 16px;
    }

    .rr-betslip--intro .rr-betslip__stake-heading {
        margin-bottom: 8px;
    }

    .rr-betslip--intro .rr-betslip__section-step {
        margin-bottom: 6px;
    }

    .rr-betslip--intro .rr-betslip__section-callout {
        font-size: 14px;
    }

    .rr-betslip--intro .rr-betslip__stake-buttons {
        gap: 8px;
    }

    .rr-betslip--intro .rr-betslip__stake-btn {
        min-height: 46px;
        font-size: 13px;
    }

    .rr-betslip--intro .rr-betslip__section--matches {
        grid-area: matches;
        padding: 0;
    }

    .rr-betslip--intro .rr-betslip__matches-list {
        max-height: 168px;
        margin-bottom: 0;
    }

    .rr-betslip--intro .rr-betslip__loading {
        grid-area: loading;
        padding-top: 0;
        min-height: 90px;
    }

    .rr-betslip--intro .rr-betslip__footer--intro {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 10px 18px 14px;
    }

    .rr-betslip--intro .rr-betslip__return-row--intro {
        flex: 1;
        align-items: center;
        margin-bottom: 0;
    }

    .rr-betslip--intro .rr-betslip__return-copy {
        gap: 2px;
    }

    .rr-betslip--intro .rr-betslip__return-label {
        font-size: 12px;
    }

    .rr-betslip--intro .rr-betslip__return-value {
        font-size: 17px;
    }

    .rr-betslip--intro .rr-betslip__place-btn {
        width: min(300px, 100%);
        min-width: 240px;
        min-height: 48px;
        flex: 0 0 auto;
    }
}

/* --- Mobile: bottom sheet above tabbar --- */
@media (max-width: 700px) {
    .rr-betslip-overlay {
        align-items: flex-end;
        padding: 0;
    }
    .rr-betslip {
        max-width: 100%;
        max-height: none;
        border-radius: 16px 16px 0 0;
        margin-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
        animation: betslipSlideUp 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
    }
    .rr-betslip--intro {
        border-radius: 24px 24px 0 0;
        max-height: none;
    }
    @keyframes betslipSlideUp {
        from { opacity: 0; transform: translateY(100%); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .rr-betslip__header--intro,
    .rr-betslip__card-main--intro,
    .rr-betslip__footer--intro {
        padding-left: 14px;
        padding-right: 14px;
    }
    .rr-betslip--intro .rr-betslip__header--intro {
        align-items: center;
        gap: 10px;
        padding-top: 12px;
        padding-bottom: 10px;
    }
    .rr-betslip--intro .rr-betslip__header-copy {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .rr-betslip--intro .rr-betslip__eyebrow {
        margin-bottom: 0;
        padding: 4px 8px;
        font-size: 9px;
    }
    .rr-betslip--intro .rr-betslip__title {
        font-size: 19px;
    }
    .rr-betslip--intro .rr-betslip__subtitle,
    .rr-betslip--intro .rr-betslip__status-text,
    .rr-betslip--intro .rr-betslip__section-copy,
    .rr-betslip--intro .rr-betslip__return-help,
    .rr-betslip--intro .rr-betslip__hero-kicker {
        display: none;
    }
    .rr-betslip--intro .rr-betslip__header-right--intro {
        gap: 8px;
    }
    .rr-betslip--intro .rr-betslip__pending-btn {
        min-height: 36px;
        padding: 6px 10px;
        font-size: 11px;
    }
    .rr-betslip--intro .rr-betslip__close {
        width: 38px;
        min-width: 38px;
        height: 38px;
        border-radius: 10px;
        font-size: 22px;
    }
    .rr-betslip--intro .rr-betslip__card-main--intro {
        display: grid;
        gap: 10px;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .rr-betslip--intro .rr-betslip__hero {
        margin-bottom: 0;
        padding: 12px;
        border-radius: 16px;
    }
    .rr-betslip--intro .rr-betslip__hero-top {
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .rr-betslip--intro .rr-betslip__hero-name {
        font-size: 22px;
        margin-bottom: 4px;
    }
    .rr-betslip--intro .rr-betslip__hero-note {
        margin-bottom: 8px;
        font-size: 12px;
        gap: 6px;
    }
    .rr-betslip--intro .rr-betslip__facts {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
    }
    .rr-betslip--intro .rr-betslip__fact {
        padding: 9px 7px 8px;
        border-radius: 12px;
        gap: 3px;
    }
    .rr-betslip--intro .rr-betslip__fact-label {
        font-size: 9px;
        letter-spacing: 0.04em;
    }
    .rr-betslip--intro .rr-betslip__fact-value {
        font-size: 15px;
    }
    .rr-betslip--intro .rr-betslip__status-panel {
        border-radius: 14px;
        padding: 10px 12px;
        margin-bottom: 0;
    }
    .rr-betslip--intro .rr-betslip__status-kicker {
        font-size: 10px;
    }
    .rr-betslip--intro .rr-betslip__status-title {
        font-size: 14px;
        line-height: 1.15;
    }
    .rr-betslip--intro .rr-betslip__stake-area--intro {
        border-radius: 14px;
        padding: 12px;
    }
    .rr-betslip--intro .rr-betslip__stake-heading {
        margin-bottom: 8px;
    }
    .rr-betslip--intro .rr-betslip__section-step {
        margin-bottom: 4px;
        padding: 3px 8px;
        font-size: 9px;
    }
    .rr-betslip--intro .rr-betslip__section-callout {
        font-size: 14px;
    }
    .rr-betslip--intro .rr-betslip__stake-buttons {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
    }
    .rr-betslip--intro .rr-betslip__stake-btn {
        min-height: 48px;
        padding: 8px 4px;
        font-size: 12px;
        border-radius: 12px;
    }
    .rr-betslip--intro .rr-betslip__stake-btn--max {
        letter-spacing: 0;
    }
    .rr-betslip--intro .rr-betslip__footer--intro {
        padding-top: 10px;
        padding-bottom: 14px;
    }
    .rr-betslip--intro .rr-betslip__return-row--intro {
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .rr-betslip--intro .rr-betslip__return-label {
        font-size: 11px;
    }
    .rr-betslip--intro .rr-betslip__return-value {
        font-size: 16px;
    }
    .rr-betslip--intro .rr-betslip__place-btn {
        min-height: 50px;
        padding: 12px;
        font-size: 12px;
        letter-spacing: 0.08em;
    }
}

/* --- PIX Modal (reuses existing classes) --- */
.rr-inicio-pix {
    position: fixed;
    inset: 0;
    z-index: 2147483646;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.rr-inicio-pix__card {
    position: relative;
    z-index: 1;
    width: min(420px, 95vw);
    background: #111827;
    border: 1px solid rgba(249,115,22,0.2);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
}
.rr-inicio-pix__title {
    color: #e2e8f0;
    margin: 0 0 14px;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rr-inicio-pix__qr-wrap {
    background: #fff;
    border-radius: 10px;
    padding: 16px;
    margin: 14px 0;
    text-align: center;
}
.rr-inicio-pix__qr { width: 200px; max-width: 100%; height: auto; }
.rr-inicio-pix__code {
    width: 100%;
    border-radius: 8px;
    border: 1px solid rgba(148,163,184,0.15);
    background: #0d1117;
    color: #cbd5e1;
    padding: 10px;
    font-size: 12px;
    font-family: Arial, sans-serif;
    resize: none;
}
.rr-inicio-pix__actions {
    margin-top: 12px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.rr-inicio-pix__btn {
    border: 0;
    border-radius: 8px;
    padding: 11px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.rr-inicio-pix__btn:hover { transform: translateY(-1px); }
.rr-inicio-pix__btn--copy {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: #fff;
    box-shadow: 0 4px 12px rgba(14,165,233,0.3);
}
.rr-inicio-pix__btn--check {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    box-shadow: 0 4px 12px rgba(34,197,94,0.3);
}
.rr-inicio-pix__status {
    margin-top: 12px;
    color: #64748b;
    font-size: 12px;
    text-align: center;
}

.rr-pix-loader {
    position: fixed;
    inset: 0;
    z-index: 2147483647;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(7, 11, 22, 0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.rr-pix-loader__card {
    width: min(420px, calc(100vw - 32px));
    padding: 26px 22px;
    border-radius: 24px;
    border: 1px solid rgba(249, 115, 22, 0.24);
    background:
        radial-gradient(circle at top, rgba(249, 115, 22, 0.18), transparent 48%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(9, 14, 24, 0.98));
    box-shadow:
        0 30px 80px rgba(2, 6, 23, 0.42),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
    display: grid;
    justify-items: center;
    gap: 10px;
    text-align: center;
}
.rr-pix-loader__svg {
    width: min(260px, 62vw);
    height: auto;
    display: block;
    overflow: visible;
    color: #e8e4e3;
}
.rr-pix-loader__svg .mainDot,
.rr-pix-loader__svg .otherDots circle {
    fill: currentColor;
    transform-box: fill-box;
    transform-origin: center bottom;
}
.rr-pix-loader__svg .mainDot {
    animation: rrPixMainDot 1.75s ease-in-out infinite;
}
.rr-pix-loader__svg .otherDots circle {
    animation: rrPixBounceDot 1.05s cubic-bezier(0.22, 0.61, 0.36, 1) infinite;
}
.rr-pix-loader__svg .otherDots circle:nth-child(1) { animation-delay: 0.06s; }
.rr-pix-loader__svg .otherDots circle:nth-child(2) { animation-delay: 0.14s; }
.rr-pix-loader__svg .otherDots circle:nth-child(3) { animation-delay: 0.22s; }
.rr-pix-loader__svg .otherDots circle:nth-child(4) { animation-delay: 0.30s; }
.rr-pix-loader__svg .otherDots circle:nth-child(5) { animation-delay: 0.38s; }
.rr-pix-loader__title {
    font-size: 22px;
    font-weight: 900;
    letter-spacing: -0.02em;
    color: #fff7ed;
}
.rr-pix-loader__copy {
    max-width: 280px;
    font-size: 13px;
    line-height: 1.45;
    color: rgba(255, 237, 213, 0.82);
}
body.light .rr-pix-loader {
    background: rgba(255, 242, 230, 0.82);
}
body.light .rr-pix-loader__card {
    border-color: rgba(234, 88, 12, 0.18);
    background:
        radial-gradient(circle at top, rgba(249, 115, 22, 0.14), transparent 48%),
        linear-gradient(180deg, rgba(255, 251, 247, 0.98), rgba(255, 242, 230, 0.98));
    box-shadow:
        0 30px 80px rgba(124, 45, 18, 0.14),
        inset 0 1px 0 rgba(255, 255, 255, 0.65);
}
body.light .rr-pix-loader__svg {
    color: #f59e0be6;
}
body.light .rr-pix-loader__title {
    color: #7c2d12;
}
body.light .rr-pix-loader__copy {
    color: rgba(124, 45, 18, 0.74);
}

@keyframes rrPixMainDot {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(240px); }
}

@keyframes rrPixBounceDot {
    0%, 100% { transform: translate3d(0, 0, 0) scaleX(1) scaleY(1); }
    20% { transform: translate3d(0, -42px, 0) scaleX(1) scaleY(1); }
    44% { transform: translate3d(-40px, 0, 0) scaleX(1.18) scaleY(0.78); }
    62% { transform: translate3d(-40px, -10px, 0) scaleX(1.04) scaleY(0.96); }
}

/* --- Custom Modal --- */
.rr-inicio-custom-modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.rr-inicio-custom-modal__card {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-rows: auto minmax(0, 1fr) auto;
    width: min(620px, 95vw);
    max-height: calc(100dvh - 40px);
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.16), transparent 34%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(10, 15, 27, 0.98) 100%);
    border: 1px solid rgba(249, 115, 22, 0.18);
    border-radius: 22px;
    overflow: hidden;
    box-shadow:
        0 0 0 1px rgba(249, 115, 22, 0.16),
        0 20px 60px rgba(0, 0, 0, 0.58),
        0 0 40px rgba(249, 115, 22, 0.08);
}
.rr-inicio-custom-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 72px 14px 20px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.03) 0%, rgba(13, 17, 23, 0.14) 100%);
}
.rr-inicio-custom-modal__header-copy {
    min-width: 0;
    flex: 1;
}
.rr-inicio-custom-modal__eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.14);
    border: 1px solid rgba(249, 115, 22, 0.28);
    color: #fdba74;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    margin-bottom: 10px;
}
.rr-inicio-custom-modal__title {
    font-size: 24px;
    font-weight: 800;
    color: #f8fafc;
    margin: 0;
    line-height: 1.04;
}
.rr-inicio-custom-modal__subtitle {
    margin: 8px 0 0;
    color: rgba(226, 232, 240, 0.78);
    font-size: 13px;
    line-height: 1.45;
}
.rr-inicio-custom-modal__close {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 3;
    min-width: 42px;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(255, 255, 255, 0.04);
    color: #f8fafc;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, border-color 0.2s, transform 0.2s;
}
.rr-inicio-custom-modal__close:hover {
    background: rgba(239, 68, 68, 0.14);
    border-color: rgba(239, 68, 68, 0.32);
    transform: translateY(-1px);
}
.rr-inicio-custom-modal__body {
    display: grid;
    gap: 12px;
    padding: 12px 20px 14px;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-width: none;
}
.rr-inicio-custom-modal__body::-webkit-scrollbar {
    width: 0;
    height: 0;
}
.rr-inicio-custom-modal__summary {
    display: flex;
    flex-direction: column;
    padding: 16px 18px;
    border-radius: 18px;
    border: 1px solid rgba(249, 115, 22, 0.22);
    background:
        linear-gradient(135deg, rgba(249, 115, 22, 0.12), rgba(15, 23, 42, 0.08)),
        rgba(15, 23, 42, 0.68);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
}
.rr-inicio-custom-modal__summary-head {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.rr-inicio-custom-modal__chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: rgba(34, 197, 94, 0.14);
    color: #86efac;
}
.rr-inicio-custom-modal__chip--soft {
    background: rgba(255, 255, 255, 0.06);
    color: #e2e8f0;
}
.rr-inicio-custom-modal__name {
    font-size: 28px;
    font-weight: 900;
    line-height: 1.04;
    color: #fff;
    letter-spacing: -0.02em;
}
.rr-inicio-custom-modal__matches {
    margin-top: 0;
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
}
.rr-inicio-custom-modal__room-list {
    display: grid;
    gap: 8px;
}
.rr-inicio-custom-modal__room-list--scroll {
    max-height: 236px;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding-right: 4px;
}
.rr-inicio-custom-modal__room-list-wrap {
    position: relative;
}
.rr-inicio-custom-modal__room-list-wrap--scroll::before {
    content: 'DESLIZE ↓';
    position: absolute;
    right: 6px;
    top: -10px;
    padding: 3px 7px;
    border-radius: 999px;
    border: 1px solid rgba(249, 115, 22, 0.65);
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.9), rgba(30, 41, 59, 0.9));
    color: rgba(251, 146, 60, 0.96);
    font-size: 0.52rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    line-height: 1;
    pointer-events: none;
    z-index: 2;
    box-shadow: 0 6px 12px rgba(2, 6, 23, 0.28);
}
.rr-inicio-custom-modal__room-list--scroll::-webkit-scrollbar {
    width: 6px;
}
.rr-inicio-custom-modal__room-list--scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.35);
    border-radius: 999px;
}
.rr-inicio-custom-modal__room-card {
    width: 100%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(2, 6, 23, 0.34);
    color: #f8fafc;
    cursor: pointer;
    display: grid;
    gap: 8px;
    text-align: left;
    transition: transform 0.18s, border-color 0.18s, background 0.18s, box-shadow 0.18s;
}
.rr-inicio-custom-modal__room-card:hover {
    transform: translateY(-1px);
    border-color: rgba(249, 115, 22, 0.42);
    background: rgba(15, 23, 42, 0.62);
    box-shadow: 0 10px 24px rgba(2, 6, 23, 0.24);
}
.rr-inicio-custom-modal__room-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.rr-inicio-custom-modal__room-entry {
    font-size: 18px;
    font-weight: 900;
    color: #fdba74;
}
.rr-inicio-custom-modal__room-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 24px;
    padding: 0 8px;
    border-radius: 999px;
    background: rgba(34, 197, 94, 0.14);
    color: #86efac;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.rr-inicio-custom-modal__room-name {
    font-size: 14px;
    font-weight: 800;
    line-height: 1.25;
    color: #f8fafc;
}
.rr-inicio-custom-modal__room-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}
.rr-inicio-custom-modal__room-prize {
    font-size: 12px;
    font-weight: 700;
    color: #86efac;
}
.rr-inicio-custom-modal__room-wait {
    font-size: 11px;
    color: rgba(226, 232, 240, 0.68);
}
.rr-inicio-custom-modal__room-cta {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #f59e0be6;
}
.rr-inicio-custom-modal__note {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(59, 130, 246, 0.18);
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.72), rgba(15, 23, 42, 0.92));
}
.rr-inicio-custom-modal__note-title {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #93c5fd;
}
.rr-inicio-custom-modal__note-copy {
    font-size: 13px;
    line-height: 1.45;
    color: rgba(226, 232, 240, 0.8);
}
.rr-inicio-custom-modal__facts {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    margin: 14px 0 12px;
}
.rr-inicio-custom-modal__fact {
    padding: 11px 12px 10px;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(2, 6, 23, 0.38);
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}
.rr-inicio-custom-modal__fact-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.82);
}
.rr-inicio-custom-modal__fact-value {
    font-size: 19px;
    font-weight: 900;
    line-height: 1;
    color: #f8fafc;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.rr-inicio-custom-modal__fact-value--success {
    color: #86efac;
}
.rr-inicio-custom-modal__form {
    padding: 16px 18px;
    border-radius: 18px;
    background: rgba(2, 6, 23, 0.42);
    border: 1px solid rgba(148, 163, 184, 0.12);
}
.rr-inicio-custom-modal__label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 8px;
}
.rr-inicio-custom-modal__input-group {
    position: relative;
    margin-bottom: 10px;
}
.rr-inicio-custom-modal__prefix {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-weight: 800;
    font-size: 15px;
}
.rr-inicio-custom-modal__input {
    width: 100%;
    padding: 15px 16px 15px 42px;
    background: #0d1117;
    border: 1px solid rgba(148,163,184,0.15);
    border-radius: 14px;
    color: #f8fafc;
    font-size: 18px;
    font-weight: 800;
}
.rr-inicio-custom-modal__input:focus {
    outline: none;
    border-color: #f59e0be6;
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14);
}
.rr-inicio-custom-modal__quick {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
}
.rr-inicio-custom-modal__quick-btn {
    min-height: 48px;
    padding: 8px 6px;
    background: rgba(15, 23, 42, 0.68);
    border: 1px solid rgba(148,163,184,0.18);
    border-radius: 12px;
    color: #e2e8f0;
    font-weight: 800;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s, box-shadow 0.15s;
    text-align: center;
}
.rr-inicio-custom-modal__quick-btn:hover {
    background: rgba(249,115,22,0.08);
    border-color: rgba(249,115,22,0.4);
    color: #f59e0be6;
    transform: translateY(-1px);
}
.rr-inicio-custom-modal__quick-btn.is-active {
    background: linear-gradient(135deg, rgba(34,197,94,0.18), rgba(15,118,110,0.18));
    border-color: rgba(34,197,94,0.72);
    color: #dcfce7;
    box-shadow: 0 10px 24px rgba(34, 197, 94, 0.16);
}
.rr-inicio-custom-modal__footer {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px 18px;
    border-top: 1px solid rgba(148, 163, 184, 0.12);
    background: linear-gradient(180deg, rgba(13, 17, 23, 0.88) 0%, rgba(2, 6, 23, 0.96) 100%);
    position: relative;
    z-index: 2;
    box-shadow: 0 -12px 30px rgba(2, 6, 23, 0.28);
}
.rr-inicio-custom-modal__back,
.rr-inicio-custom-modal__submit {
    min-height: 52px;
    border: 0;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
}
.rr-inicio-custom-modal__back {
    flex: 0 0 144px;
    background: rgba(148,163,184,0.12);
    color: #cbd5e1;
    border: 1px solid rgba(148,163,184,0.16);
}
.rr-inicio-custom-modal__back:hover {
    transform: translateY(-1px);
    background: rgba(148,163,184,0.18);
}
.rr-inicio-custom-modal__submit {
    flex: 1;
    background: linear-gradient(135deg, #f59e0be6, #ea580c);
    color: #fff;
    box-shadow: 0 4px 16px rgba(249,115,22,0.35);
}
.rr-inicio-custom-modal__submit:hover {
    transform: translateY(-1px);
    background: linear-gradient(135deg, #ea580c, #c2410c);
    box-shadow: 0 6px 24px rgba(249,115,22,0.5);
}
@media (min-width: 901px) {
    .rr-inicio-custom-modal {
        align-items: flex-start;
        padding: calc(var(--hub-navbar-height, 72px) + 24px) 5vw 18px;
        box-sizing: border-box;
    }
    .rr-inicio-custom-modal__card {
        width: 100%;
        max-width: none;
        height: calc(100dvh - var(--hub-navbar-height, 72px) - 42px);
        max-height: none;
    }
    .rr-inicio-custom-modal__body {
        grid-template-columns: minmax(0, 1.06fr) minmax(280px, 0.94fr);
        align-items: stretch;
        gap: 14px;
        padding: 14px 18px 14px;
        overflow: hidden;
    }
    .rr-inicio-custom-modal__summary {
        min-height: 0;
        height: 100%;
        overflow: hidden;
    }
    .rr-inicio-custom-modal__form {
        align-self: stretch;
    }
    .rr-inicio-custom-modal__matches {
        gap: 12px;
    }
    .rr-inicio-custom-modal__room-list-wrap,
    .rr-inicio-custom-modal__room-list-wrap--scroll {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        height: auto;
    }
    .rr-inicio-custom-modal__room-list {
        min-height: 0;
        height: auto;
    }
    .rr-inicio-custom-modal__room-list--scroll {
        flex: 1 1 auto;
        min-height: 0;
        max-height: none;
        height: auto;
    }
    .rr-inicio-custom-modal__name {
        font-size: 22px;
    }
    .rr-inicio-custom-modal__fact {
        padding: 10px 8px 9px;
    }
    .rr-inicio-custom-modal__fact-value {
        font-size: 14px;
    }
    .rr-inicio-custom-modal__label {
        font-size: 12px;
    }
    .rr-inicio-custom-modal__input {
        padding: 13px 14px 13px 40px;
        font-size: 16px;
    }
    .rr-inicio-custom-modal__quick-btn {
        min-height: 42px;
        font-size: 12px;
    }
    .rr-inicio-custom-modal__footer {
        padding: 12px 18px 14px;
    }
    .rr-inicio-custom-modal__back,
    .rr-inicio-custom-modal__submit {
        min-height: 48px;
        font-size: 12px;
    }
}
@media (max-width: 700px) {
    .rr-pix-loader {
        padding: 18px;
    }
    .rr-pix-loader__card {
        width: min(360px, calc(100vw - 24px));
        padding: 22px 18px;
        border-radius: 22px;
        gap: 8px;
    }
    .rr-pix-loader__svg {
        width: min(220px, 66vw);
    }
    .rr-pix-loader__title {
        font-size: 18px;
    }
    .rr-pix-loader__copy {
        font-size: 12px;
    }
    .rr-inicio-custom-modal {
        align-items: flex-end;
        padding: calc(var(--hub-navbar-height, 72px) + env(safe-area-inset-top, 0px) + 8px) 0 0;
        box-sizing: border-box;
    }
    .rr-inicio-custom-modal__card {
        width: 100%;
        max-height: calc(100dvh - var(--hub-navbar-height, 72px) - env(safe-area-inset-top, 0px) - 8px);
        border-radius: 22px 22px 0 0;
    }
    .rr-inicio-custom-modal__header {
        padding: 14px 58px 10px 14px;
        gap: 10px;
    }
    .rr-inicio-custom-modal__eyebrow {
        margin-bottom: 6px;
        padding: 4px 8px;
        font-size: 9px;
    }
    .rr-inicio-custom-modal__title {
        font-size: 20px;
    }
    .rr-inicio-custom-modal__subtitle {
        font-size: 12px;
        line-height: 1.35;
    }
    .rr-inicio-custom-modal__close {
        top: 12px;
        right: 12px;
        width: 38px;
        min-width: 38px;
        height: 38px;
        border-radius: 10px;
        font-size: 22px;
    }
    .rr-inicio-custom-modal__body {
        padding: 10px 14px 12px;
        gap: 10px;
    }
    .rr-inicio-custom-modal__summary,
    .rr-inicio-custom-modal__form {
        padding: 12px;
        border-radius: 14px;
    }
    .rr-inicio-custom-modal__name {
        font-size: 22px;
    }
    .rr-inicio-custom-modal__note {
        padding: 10px 12px;
        border-radius: 12px;
    }
    .rr-inicio-custom-modal__note-title {
        font-size: 10px;
    }
    .rr-inicio-custom-modal__note-copy {
        font-size: 12px;
    }
    .rr-inicio-custom-modal__room-card {
        padding: 10px 12px;
        border-radius: 12px;
        gap: 6px;
    }
    .rr-inicio-custom-modal__room-list--scroll {
        max-height: 210px;
        padding-right: 2px;
    }
    .rr-inicio-custom-modal__room-list-wrap--scroll::before {
        right: 4px;
        top: -8px;
        padding: 2px 6px;
        font-size: 0.48rem;
    }
    .rr-inicio-custom-modal__room-entry {
        font-size: 16px;
    }
    .rr-inicio-custom-modal__room-name {
        font-size: 13px;
    }
    .rr-inicio-custom-modal__room-prize,
    .rr-inicio-custom-modal__room-wait,
    .rr-inicio-custom-modal__room-cta {
        font-size: 10px;
    }
    .rr-inicio-custom-modal__facts {
        gap: 6px;
    }
    .rr-inicio-custom-modal__fact {
        padding: 9px 7px 8px;
        border-radius: 12px;
        gap: 3px;
    }
    .rr-inicio-custom-modal__fact-label {
        font-size: 9px;
        letter-spacing: 0.04em;
    }
    .rr-inicio-custom-modal__fact-value {
        font-size: 15px;
    }
    .rr-inicio-custom-modal__input {
        padding: 13px 14px 13px 38px;
        font-size: 17px;
        border-radius: 12px;
    }
    .rr-inicio-custom-modal__prefix {
        left: 12px;
        font-size: 14px;
    }
    .rr-inicio-custom-modal__quick {
        gap: 6px;
    }
    .rr-inicio-custom-modal__quick-btn {
        min-height: 44px;
        font-size: 12px;
        border-radius: 10px;
        padding: 6px 4px;
    }
    .rr-inicio-custom-modal__footer {
        padding: 10px 14px calc(14px + env(safe-area-inset-bottom, 0px));
        position: sticky;
        bottom: 0;
    }
    .rr-inicio-custom-modal__back,
    .rr-inicio-custom-modal__submit {
        min-height: 48px;
        font-size: 12px;
    }
    .rr-inicio-custom-modal__back {
        flex-basis: 120px;
    }
}

/* Hidden states */
.rr-inicio-slip[hidden],
.rr-inicio-pix[hidden],
.rr-inicio-custom-modal[hidden],
.rr-pix-loader[hidden] {
    display: none !important;
}
</style>

<style>
/* ============================================
   ⚔️ JOIN SLIP - Bilhete de Entrada na Sala X1
   ============================================ */

/* VS Header */
.rr-joinslip__vs-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 14px 0 16px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 14px;
}
.rr-joinslip__player {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    min-width: 80px;
}
.rr-joinslip__avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(249,115,22,0.4);
    background: #1e293b;
}
.rr-joinslip__avatar--you {
    display: flex;
    align-items: center;
    justify-content: center;
    border-color: rgba(34,197,94,0.4);
    color: #22c55e;
    font-size: 18px;
}
.rr-joinslip__player-name {
    font-size: 12px;
    font-weight: 600;
    color: #e2e8f0;
    max-width: 90px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.rr-joinslip__player-name--you {
    color: #22c55e;
}
.rr-joinslip__vs-badge {
    font-size: 16px;
    font-weight: 900;
    color: #f59e0be6;
    text-shadow: 0 0 12px rgba(249,115,22,0.4);
    letter-spacing: 1px;
    flex-shrink: 0;
}

/* Prize highlight */
.rr-joinslip__prize-highlight {
    background: linear-gradient(135deg, rgba(34,197,94,0.14), rgba(14,165,233,0.08));
    border: 1px solid rgba(34,197,94,0.25);
    border-radius: 12px;
    padding: 12px 14px;
    margin-bottom: 12px;
    box-shadow: 0 8px 28px rgba(15,118,110,0.18);
}
.rr-joinslip__prize-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #86efac;
    display: flex;
    align-items: center;
    gap: 6px;
}
.rr-joinslip__prize-label i {
    color: #22c55e;
}
.rr-joinslip__prize-value {
    margin-top: 4px;
    font-size: 22px;
    font-weight: 900;
    color: #22c55e;
    text-shadow: 0 0 20px rgba(34,197,94,0.35);
}
.rr-joinslip__prize-note {
    margin-top: 4px;
    font-size: 11px;
    color: #cbd5e1;
    opacity: 0.9;
}

/* Room details */
.rr-joinslip__details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}
.rr-joinslip__detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 0;
}
.rr-joinslip__detail-row--prize {
    padding: 8px 10px;
    background: rgba(34,197,94,0.06);
    border: 1px solid rgba(34,197,94,0.15);
    border-radius: 8px;
    margin-top: 2px;
}
.rr-joinslip__detail-label {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}
.rr-joinslip__detail-label i {
    font-size: 11px;
    width: 14px;
    text-align: center;
    color: #64748b;
}
.rr-joinslip__detail-value {
    font-size: 13px;
    font-weight: 600;
    color: #e2e8f0;
}
.rr-joinslip__detail-value--entry {
    color: #f59e0be6;
    font-weight: 700;
}
.rr-joinslip__detail-value--prize {
    font-size: 16px;
    font-weight: 800;
    color: #22c55e;
}

/* Competitor selector button */
.rr-joinslip__selector {
    margin-bottom: 8px;
}
.rr-joinslip__selector-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.rr-joinslip__selector-btn {
    width: 100%;
    min-height: 52px;
    background: #0d1117;
    border: 2px dashed rgba(148,163,184,0.25);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    padding: 10px 14px;
    display: flex;
    align-items: center;
}
.rr-joinslip__selector-btn:hover {
    border-color: rgba(249,115,22,0.5);
    background: rgba(249,115,22,0.04);
}
.rr-joinslip__selector-btn.has-selection {
    border-style: solid;
    border-color: rgba(34,197,94,0.5);
    background: rgba(34,197,94,0.04);
}
.rr-joinslip__selector-placeholder {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #64748b;
    font-size: 13px;
    font-weight: 500;
    width: 100%;
}
.rr-joinslip__selector-placeholder i {
    font-size: 14px;
    color: #475569;
}
.rr-joinslip__selector-selected {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
}
.rr-joinslip__selector-selected[hidden] { display: none; }
.rr-joinslip__selector-img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(34,197,94,0.4);
    flex-shrink: 0;
}
.rr-joinslip__selector-name {
    flex: 1;
    font-size: 14px;
    font-weight: 700;
    color: #f1f5f9;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.rr-joinslip__selector-edit {
    color: #64748b;
    font-size: 11px;
    flex-shrink: 0;
}
.rr-joinslip__pay-value {
    color: #f59e0be6 !important;
}

/* ============================================
   🔍 COMPETITOR / GROUP PICKER MODAL
   ============================================ */
.rr-picker-overlay {
    position: fixed;
    inset: 0;
    z-index: 10002;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.rr-picker-overlay[hidden] { display: none !important; }

.rr-picker {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 440px;
    max-height: 75vh;
    background: #111827;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 0 0 1px rgba(249,115,22,0.2),
        0 20px 60px rgba(0,0,0,0.7),
        0 0 40px rgba(249,115,22,0.08);
    animation: betslipSlideIn 0.25s cubic-bezier(0.22,0.61,0.36,1);
}
.rr-picker__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: #0d1117;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}
.rr-picker__title {
    font-size: 15px;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rr-picker__title i { color: #f59e0be6; }
.rr-picker__close {
    background: none;
    border: 0;
    color: #64748b;
    font-size: 16px;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 6px;
    transition: all 0.2s;
}
.rr-picker__close:hover { background: rgba(239,68,68,0.15); color: #ef4444; }

.rr-picker__search {
    position: relative;
    padding: 10px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    flex-shrink: 0;
}
.rr-picker__search-icon {
    position: absolute;
    left: 28px;
    top: 50%;
    transform: translateY(-50%);
    color: #475569;
    font-size: 13px;
}
.rr-picker__search-input {
    width: 100%;
    padding: 9px 12px 9px 34px;
    background: #0d1117;
    border: 1px solid rgba(148,163,184,0.12);
    border-radius: 8px;
    color: #f1f5f9;
    font-size: 13px;
    outline: none;
}
.rr-picker__search-input::placeholder { color: #475569; }
.rr-picker__search-input:focus { border-color: rgba(249,115,22,0.4); }

.rr-picker__list {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
}
.rr-picker__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.15s;
    border: 1px solid transparent;
    margin-bottom: 4px;
}
.rr-picker__item:hover {
    background: rgba(249,115,22,0.06);
    border-color: rgba(249,115,22,0.2);
}
.rr-picker__item.is-disabled {
    opacity: 0.4;
    pointer-events: none;
}
.rr-picker__item-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(148,163,184,0.15);
    flex-shrink: 0;
    background: #1e293b;
}
.rr-picker__item-info {
    flex: 1;
    min-width: 0;
}
.rr-picker__item-name {
    font-size: 14px;
    font-weight: 700;
    color: #f1f5f9;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.rr-picker__item-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}
.rr-picker__item-badge {
    flex-shrink: 0;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 10px;
    background: rgba(239,68,68,0.15);
    color: #ef4444;
}
.rr-picker__item-arrow {
    color: #475569;
    font-size: 12px;
    flex-shrink: 0;
}
.rr-picker__empty {
    text-align: center;
    padding: 32px 20px;
    color: #64748b;
    font-size: 13px;
}
.rr-picker__group-members {
    display: flex;
    gap: 4px;
    margin-top: 3px;
}
.rr-picker__group-member-img {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(148,163,184,0.2);
}

@media (max-width: 700px) {
    .rr-picker-overlay {
        align-items: flex-end;
        padding: 0;
    }
    .rr-picker {
        max-width: 100%;
        max-height: calc(100vh - 80px);
        border-radius: 16px 16px 0 0;
        margin-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
    }
}

@media (max-width: 700px) {
    .rr-inicio-pix__card {
        max-width: 100%;
        border-radius: 16px 16px 12px 12px;
    }
}
</style>

<style>
/* ============================================
   💰 MODAL DRAFT BOLÃO
   ============================================ */

.rr-draft-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2147483645 !important;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(6px);
    padding: 0;
    overflow: hidden;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.rr-draft-modal[hidden] {
    display: none !important;
}

.rr-draft-modal::-webkit-scrollbar { display: none; }

.rr-draft-modal.is-open {
    display: flex;
}

.rr-draft-container {
    width: 82%;
    height: 100vh;
    max-height: 100vh;
    max-width: none;
    background: linear-gradient(145deg, #0f172a, #1e293b);
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.rr-draft-container::-webkit-scrollbar { display: none; }

/* Header do Modal */
.rr-draft-header {
    padding: 14px 18px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9));
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rr-draft-header__info h2 {
    font-size: 1.25rem;
    font-weight: 800;
    color: #f8fafc;
    margin: 0 0 4px 0;
}

.rr-draft-header__info p {
    font-size: 0.8rem;
    color: rgba(148, 163, 184, 0.9);
    margin: 0;
}

.rr-draft-header__close {
    background:
        radial-gradient(circle at 30% 30%, rgba(254, 226, 226, 0.28), transparent 42%),
        linear-gradient(180deg, #ff4d4f 0%, #dc2626 58%, #991b1b 100%);
    border: 1px solid rgba(254, 202, 202, 0.42);
    color: #fff7f7;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    font-size: 1.7rem;
    font-weight: 900;
    line-height: 1;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease, border-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow:
        0 16px 30px rgba(153, 27, 27, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.22);
    text-shadow: 0 1px 0 rgba(127, 29, 29, 0.4);
}

.rr-draft-header__close:hover {
    transform: translateY(-1px) scale(1.04);
    filter: saturate(1.08) brightness(1.02);
    border-color: rgba(255, 229, 229, 0.62);
    box-shadow:
        0 18px 34px rgba(185, 28, 28, 0.46),
        inset 0 1px 0 rgba(255, 255, 255, 0.28);
}

/* Tabs */
.rr-draft-tabs {
    display: flex;
    gap: 8px;
    padding: 10px 16px;
    background: rgba(15, 23, 42, 0.5);
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.rr-draft-tab {
    padding: 9px 14px;
    background: transparent;
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 8px;
    color: rgba(203, 213, 225, 0.8);
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.rr-draft-tab:hover {
    background: rgba(148, 163, 184, 0.1);
    color: #f8fafc;
}

.rr-draft-tab.active {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-color: #3b82f6;
    color: #fff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

/* Content */
.rr-draft-content {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow: hidden;
    padding: 16px 18px 18px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.rr-draft-content::-webkit-scrollbar { display: none; }

.rr-draft-lock {
    overflow: hidden !important;
}

.rr-draft-panel {
    display: none;
}

.rr-draft-panel.active {
    display: block;
}

/* Actions */
.rr-draft-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
}

@media (min-width: 768px) {
    .rr-draft-actions {
        flex-direction: row;
        align-items: center;
    }
}

/* Budget Display */
.rr-draft-budget {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.2));
    border: 1px solid rgba(251, 191, 36, 0.3);
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rr-draft-budget__label {
    font-size: 0.8rem;
    color: rgba(251, 191, 36, 0.9);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.rr-draft-budget__value {
    font-size: 1.35rem;
    font-weight: 900;
    color: #f59e0be6;
}

/* Competidores Grid - 3 por linha no mobile */
.rr-draft-competitors {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 24px;
    max-height: 420px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.rr-draft-competitors::-webkit-scrollbar { display: none; }

.rr-draft-competitor-card {
    background: linear-gradient(145deg, #1a1f2e, #0f1419);
    border: 2px solid rgba(148, 163, 184, 0.2);
    border-radius: 12px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.rr-draft-competitor-card:hover {
    transform: translateY(-2px);
    border-color: rgba(59, 130, 246, 0.5);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.rr-draft-competitor-card.selected {
    background: linear-gradient(145deg, rgba(34, 197, 94, 0.2), rgba(22, 163, 74, 0.15));
    border-color: #22c55e;
    border-width: 3px;
    box-shadow: 0 0 15px rgba(34, 197, 94, 0.3);
}

.rr-draft-competitor-card.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.rr-draft-competitor__photo {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    margin-bottom: 8px;
    border: 2px solid rgba(148, 163, 184, 0.2);
}

.rr-draft-competitor-card.selected .rr-draft-competitor__photo {
    border-color: #22c55e;
}

.rr-draft-competitor__name {
    font-size: 0.75rem;
    font-weight: 700;
    color: #f8fafc;
    margin-bottom: 6px;
    line-height: 1.2;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-draft-competitor__price {
    font-size: 1rem;
    font-weight: 900;
    color: #f59e0be6;
    margin-top: auto;
}

.rr-draft-competitor__price-label {
    font-size: 0.65rem;
    color: rgba(148, 163, 184, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Mini card (Sua Equipe) */
.rr-draft-competitor-card--mini {
    padding: 6px;
    border-radius: 10px;
    border-width: 1px;
    width: 78px;
    min-width: 78px;
    max-width: 90px;
    box-shadow: 0 6px 14px rgba(0,0,0,0.2);
}

.rr-draft-competitor-card--mini .rr-draft-competitor__photo {
    width: 52px;
    height: 52px;
    margin: 0;
}

/* Desktop: 4-5 por linha */
@media (min-width: 768px) {
    .rr-draft-competitors {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 16px;
    }
    
    .rr-draft-competitor__photo {
        width: 70px;
        height: 70px;
    }
    
    .rr-draft-competitor__name {
        font-size: 0.85rem;
    }
}

/* Selected Team */
.rr-draft-team {
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 20px;
}

.rr-draft-team__title {
    font-size: 1rem;
    font-weight: 700;
    color: #f8fafc;
    margin-bottom: 12px;
}

.rr-draft-team__list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 12px;
}

.rr-draft-team__member {
    position: relative;
}

.rr-draft-team__member-remove {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 0.95rem;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(0,0,0,0.2);
    border-radius: 50%;
}

/* Pay Button */
.rr-draft-pay-btn {
    width: 100%;
    padding: 12px 14px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border: none;
    border-radius: 10px;
    color: #052e16;
    font-size: 1rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.4);
}

.rr-draft-pay-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(34, 197, 94, 0.6);
}

.rr-draft-pay-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.rr-draft-pay-btn.is-disabled {
    opacity: 0.62;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.35);
}

.rr-draft-pay-btn.is-disabled:hover {
    transform: none;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.35);
}

/* Ranking List */
.rr-draft-ranking {
    max-height: none;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    display: flex;
    flex-direction: column;
    gap: 10px;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.rr-draft-ranking::-webkit-scrollbar { display: none; }

.rr-draft-ranking-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.rr-draft-ranking-item:nth-child(odd) {
    background: rgba(15, 23, 42, 0.3);
}

.rr-draft-ranking-item:nth-child(1) .rr-draft-ranking__position {
    background: linear-gradient(135deg, #f59e0be6, #f59e0be6);
    color: #000;
}

.rr-draft-ranking-item:nth-child(2) .rr-draft-ranking__position {
    background: linear-gradient(135deg, #94a3b8, #64748b);
    color: #fff;
}

.rr-draft-ranking-item:nth-child(3) .rr-draft-ranking__position {
    background: linear-gradient(135deg, #cd7f32, #b87333);
    color: #fff;
}

.rr-draft-ranking__position {
    background: rgba(59, 130, 246, 0.15);
    color: #60a5fa;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.875rem;
}

.rr-draft-ranking__prize {
    font-size: 1.125rem;
    font-weight: 700;
    color: #22c55e;
}

/* Ranking v2 */
.rr-draft-ranking-shell {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 16px;
    flex: 1;
    min-height: 0;
    overflow: hidden;
}

.rr-draft-ranking-shell::before,
.rr-draft-ranking-shell::after {
    content: "";
    position: absolute;
    pointer-events: none;
    border-radius: 999px;
    filter: blur(20px);
    opacity: 0.75;
}

.rr-draft-ranking-shell::before {
    top: -42px;
    left: 8%;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.2), rgba(249, 115, 22, 0));
}

.rr-draft-ranking-shell::after {
    top: 10px;
    right: -30px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0));
}

.rr-draft-ranking-toolbar {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 16px;
    align-items: center;
    padding: 16px 18px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    border-radius: 22px;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.18), transparent 36%),
        radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.16), transparent 34%),
        linear-gradient(135deg, rgba(15, 23, 42, 0.86), rgba(51, 65, 85, 0.58));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.06),
        0 20px 40px rgba(2, 6, 23, 0.28);
    overflow: hidden;
}

.rr-draft-ranking-toolbar::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.04) 22%, transparent 40%);
    transform: translateX(-100%);
    animation: rrDraftRankingSheen 8.5s linear infinite;
    pointer-events: none;
}

.rr-draft-ranking-toolbar__copy {
    position: relative;
    z-index: 1;
    display: grid;
    gap: 10px;
}

.rr-draft-ranking-toolbar__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    width: fit-content;
    min-height: 34px;
    padding: 0 0.92rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(59, 130, 246, 0.15));
    color: #fff7ed;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    box-shadow: 0 12px 24px rgba(2, 6, 23, 0.2);
    backdrop-filter: blur(12px);
}

.rr-draft-ranking-title {
    font-weight: 900;
    color: #f8fafc;
    font-size: clamp(1.02rem, 2vw, 1.28rem);
    letter-spacing: -0.03em;
    line-height: 1.05;
}

.rr-draft-ranking-meta {
    color: rgba(226, 232, 240, 0.76);
    font-size: 0.84rem;
    line-height: 1.45;
}

.rr-draft-ranking-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.68rem;
}

.rr-draft-ranking-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 38px;
    padding: 0 0.98rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.08));
    color: #e2e8f0;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.03em;
    box-shadow: 0 12px 24px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(14px);
}

.rr-draft-ranking-chip i {
    color: #fde68a;
}

.rr-draft-ranking-chip--one {
    animation: rrDraftRankingBadgeFloat 5.4s ease-in-out infinite;
}

.rr-draft-ranking-chip--two {
    animation: rrDraftRankingBadgeFloat 6s ease-in-out infinite 0.25s;
}

.rr-draft-ranking-chip--three {
    animation: rrDraftRankingBadgeFloat 6.6s ease-in-out infinite 0.45s;
}

.rr-draft-ranking-toolbar__actions {
    position: relative;
    z-index: 1;
    display: grid;
    gap: 12px;
    justify-items: end;
}

.rr-draft-ranking-visual {
    position: relative;
    width: 174px;
    height: 96px;
    display: grid;
    place-items: center;
}

.rr-draft-ranking-visual::before,
.rr-draft-ranking-visual::after {
    content: "";
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
}

.rr-draft-ranking-visual::before {
    inset: 14px 32px;
    background: radial-gradient(circle, rgba(191, 219, 254, 0.42), rgba(59, 130, 246, 0.08) 52%, rgba(14, 165, 233, 0) 76%);
    filter: blur(10px);
    animation: rrDraftRankingHaloPulse 5.6s ease-in-out infinite;
}

.rr-draft-ranking-visual::after {
    inset: 24px 42px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: inset 0 0 0 1px rgba(191, 219, 254, 0.08);
    animation: rrDraftRankingRingDrift 7s ease-in-out infinite;
}

.rr-draft-ranking-visual__core {
    position: relative;
    z-index: 1;
    width: 68px;
    height: 68px;
    display: grid;
    place-items: center;
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    background: linear-gradient(160deg, rgba(249, 115, 22, 0.3), rgba(59, 130, 246, 0.22));
    color: #fde68a;
    font-size: 1.4rem;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 18px 28px rgba(2, 6, 23, 0.3);
    backdrop-filter: blur(12px);
    animation: rrDraftRankingCoreFloat 4.8s ease-in-out infinite;
}

.rr-draft-ranking-visual__badge {
    position: absolute;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    min-height: 34px;
    padding: 0 0.88rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08));
    color: #eff6ff;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow: 0 12px 20px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(12px);
}

.rr-draft-ranking-visual__badge i {
    color: #fde68a;
}

.rr-draft-ranking-visual__badge--one {
    top: 6px;
    right: 0;
    --rr-draft-rank-rotate: -4deg;
    transform: rotate(var(--rr-draft-rank-rotate));
    animation: rrDraftRankingCardFloat 6.2s ease-in-out infinite;
}

.rr-draft-ranking-visual__badge--two {
    left: 0;
    bottom: 4px;
    --rr-draft-rank-rotate: 5deg;
    transform: rotate(var(--rr-draft-rank-rotate));
    animation: rrDraftRankingCardFloat 6.9s ease-in-out infinite reverse;
}

.rr-draft-refresh-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.62rem;
    min-height: 50px;
    padding: 0 1.18rem;
    border-radius: 18px;
    border: 1px solid rgba(96, 165, 250, 0.28);
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.92), rgba(37, 99, 235, 0.96));
    color: #fff;
    font-size: 0.86rem;
    font-weight: 900;
    letter-spacing: 0.03em;
    cursor: pointer;
    box-shadow: 0 18px 34px rgba(37, 99, 235, 0.24);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.rr-draft-refresh-btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.26) 30%, transparent 56%);
    transform: translateX(-120%);
    animation: rrDraftRankingButtonShine 4.8s linear infinite;
}

.rr-draft-refresh-btn > * {
    position: relative;
    z-index: 1;
}

.rr-draft-refresh-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 36px rgba(37, 99, 235, 0.28);
}

.rr-draft-refresh-btn:disabled {
    opacity: 0.68;
    cursor: not-allowed;
    box-shadow: none;
}

.rr-draft-refresh-btn:disabled::before {
    animation: none;
}

.rr-draft-podium {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    align-items: stretch;
    padding: 12px 0 4px;
}

.rr-draft-podium::before {
    content: "";
    position: absolute;
    inset: auto 4% -8px;
    height: 44px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.18), rgba(15, 23, 42, 0));
    filter: blur(16px);
    opacity: 0.7;
    pointer-events: none;
}

.rr-draft-podium-card {
    --rr-draft-podium-accent: rgba(96, 165, 250, 0.56);
    --rr-draft-podium-glow: rgba(59, 130, 246, 0.24);
    --rr-draft-podium-offset: 0px;
    position: relative;
    padding: 16px 16px calc(28px + var(--rr-draft-bar-height, 22px));
    border-radius: 28px;
    border: 1px solid rgba(148, 163, 184, 0.24);
    min-height: var(--rr-draft-podium-min-height, 228px);
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 12px;
    overflow: hidden;
    background:
        radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.18), transparent 34%),
        radial-gradient(circle at top left, var(--rr-draft-podium-glow), transparent 50%),
        radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.08), transparent 34%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(15, 23, 42, 0.98) 74%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 24px 40px rgba(0, 0, 0, 0.42),
        0 0 0 1px rgba(255, 255, 255, 0.03);
    isolation: isolate;
    animation: rrDraftRankingCardLift 6.3s ease-in-out infinite var(--rr-draft-rank-delay, 0s);
}

.rr-draft-podium-card::before,
.rr-draft-podium-card::after {
    content: "";
    position: absolute;
    pointer-events: none;
}

.rr-draft-podium-card::before {
    inset: 1px;
    border-radius: 27px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    opacity: 0.82;
}

.rr-draft-podium-card::after {
    inset: -22% 14%;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0));
    filter: blur(14px);
    opacity: 0.44;
    transform: translateY(0);
    animation: rrDraftRankingHaloPulse 5.8s ease-in-out infinite var(--rr-draft-rank-delay, 0s);
}

.rr-draft-podium-card__topline {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}

.rr-draft-podium-card__medal {
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(2, 6, 23, 0.46), rgba(15, 23, 42, 0.72));
    border: 1px solid rgba(255, 255, 255, 0.22);
    font-size: 1.1rem;
    box-shadow:
        0 12px 20px rgba(2, 6, 23, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.16);
    backdrop-filter: blur(10px);
}

.rr-draft-podium-card__badge {
    position: relative;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    width: fit-content;
    min-height: 36px;
    padding: 0 0.96rem;
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08));
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: #fff7ed;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    box-shadow: 0 12px 22px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(12px);
    animation: rrDraftRankingBadgeFloat 5.8s ease-in-out infinite var(--rr-draft-rank-delay, 0s);
}

.rr-draft-podium-card__badge i {
    color: #fde68a;
}

.rr-draft-podium-card--premium {
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 18px 32px rgba(0, 0, 0, 0.34),
        0 0 0 1px rgba(250, 204, 21, 0.18),
        0 0 34px rgba(250, 204, 21, 0.18);
}

.rr-draft-podium-card--premium::before {
    border-color: rgba(250, 204, 21, 0.22);
}

.rr-draft-podium-card--mine {
    border-color: rgba(34, 197, 94, 0.44);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.1),
        0 22px 38px rgba(0, 0, 0, 0.34),
        0 0 0 1px rgba(34, 197, 94, 0.2),
        0 0 38px rgba(34, 197, 94, 0.2);
}

.rr-draft-podium-card--mine::before {
    border-color: rgba(134, 239, 172, 0.34);
}

.rr-draft-podium-card--mine .rr-draft-podium-card__pos,
.rr-draft-podium-card--mine .rr-draft-podium-card__name {
    color: #f8fafc;
}

.rr-draft-podium-card--mine .rr-draft-podium-card__badge {
    background: linear-gradient(135deg, rgba(21, 128, 61, 0.28), rgba(59, 130, 246, 0.18));
    border-color: rgba(134, 239, 172, 0.3);
}

.rr-draft-podium-card__scene {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 88px;
    margin-top: -2px;
}

.rr-draft-podium-card__scene::before {
    content: "";
    position: absolute;
    width: 124px;
    height: 124px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0));
    filter: blur(10px);
    opacity: 0.88;
}

.rr-draft-podium-card__scene::after {
    content: "";
    position: absolute;
    inset: auto 16px 0;
    height: 16px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0));
    filter: blur(8px);
}

.rr-draft-podium-card__avatar-wrap {
    position: relative;
    z-index: 1;
    width: fit-content;
    margin-top: 0;
    padding: 12px;
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.06));
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow:
        0 18px 32px rgba(2, 6, 23, 0.24),
        0 0 0 10px rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(14px);
}

.rr-draft-podium-card--premium .rr-draft-podium-card__avatar-wrap {
    padding: 12px;
    border-color: rgba(250, 204, 21, 0.26);
    background: linear-gradient(180deg, rgba(251, 191, 36, 0.24), rgba(255, 255, 255, 0.08));
    box-shadow:
        0 18px 30px rgba(2, 6, 23, 0.24),
        0 0 0 12px rgba(250, 204, 21, 0.05),
        0 0 0 1px rgba(250, 204, 21, 0.14);
}

.rr-draft-podium-card__pos {
    position: relative;
    z-index: 1;
    font-weight: 900;
    font-size: 0.7rem;
    color: rgba(226, 232, 240, 0.8);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    text-align: center;
}

.rr-draft-podium-card__name {
    position: relative;
    z-index: 1;
    font-weight: 800;
    color: #f8fafc;
    line-height: 1.2;
    min-height: 44px;
    font-size: 1.04rem;
    flex: 1;
    min-width: 0;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

.rr-draft-podium-card__name-line {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 8px;
    margin-top: -2px;
}

.rr-draft-podium-card__premium-crown {
    flex: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    margin-top: 1px;
    border-radius: 999px;
    color: #fde68a;
    background: linear-gradient(180deg, rgba(251, 191, 36, 0.24), rgba(245, 158, 11, 0.1));
    border: 1px solid rgba(250, 204, 21, 0.26);
    box-shadow: 0 10px 18px rgba(245, 158, 11, 0.16);
    font-size: 0.8rem;
}

.rr-draft-podium-card--premium .rr-draft-podium-card__name {
    color: #fffbeb;
    text-shadow: 0 0 22px rgba(250, 204, 21, 0.18);
}

.rr-draft-podium-card__stats {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
    margin-top: auto;
}

.rr-draft-podium-card__pill {
    display: grid;
    grid-template-columns: 30px minmax(0, 1fr);
    align-items: center;
    gap: 8px;
    min-height: 54px;
    padding: 10px 12px;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.04)),
        rgba(2, 6, 23, 0.26);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 12px 20px rgba(2, 6, 23, 0.18);
}

.rr-draft-podium-card__pill i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 999px;
    color: #fff7ed;
    background: rgba(255, 255, 255, 0.12);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

.rr-draft-podium-card__pill-copy {
    display: grid;
    gap: 2px;
    min-width: 0;
}

.rr-draft-podium-card__pill strong {
    color: #fff7ed;
    font-weight: 900;
    font-size: 1rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-draft-podium-card__pill small {
    color: rgba(226, 232, 240, 0.7);
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.rr-draft-podium-card__pill--prize i {
    background: linear-gradient(180deg, rgba(251, 191, 36, 0.32), rgba(249, 115, 22, 0.22));
    color: #fde68a;
}

.rr-draft-podium-card__pill--score i {
    background: linear-gradient(180deg, rgba(59, 130, 246, 0.32), rgba(37, 99, 235, 0.2));
    color: #bfdbfe;
}

.rr-draft-podium-card__pedestal {
    position: absolute;
    left: 14px;
    right: 14px;
    bottom: 12px;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 40px;
    padding: 0 12px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: linear-gradient(180deg, rgba(2, 6, 23, 0.46), rgba(15, 23, 42, 0.72));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 14px 20px rgba(2, 6, 23, 0.18);
}

.rr-draft-podium-card__pedestal-rank {
    position: relative;
    z-index: 1;
    min-width: 38px;
    color: #fff7ed;
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: -0.03em;
}

.rr-draft-podium-card__pedestal-fill {
    position: relative;
    flex: 1;
    height: 10px;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.08);
}

.rr-draft-podium-card__pedestal-fill::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.12), var(--rr-draft-podium-accent));
}

.rr-draft-podium-card__score {
    color: rgba(226, 232, 240, 0.74);
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.rr-draft-podium-card__bar {
    position: absolute;
    left: 16px;
    right: 16px;
    bottom: 24px;
    height: 6px;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.04), var(--rr-draft-podium-accent));
    box-shadow: 0 8px 14px rgba(2, 6, 23, 0.16);
    opacity: 0.9;
}

.rr-draft-podium-card__bar::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.26) 36%, transparent 62%);
    transform: translateX(-100%);
    animation: rrDraftRankingBarShine 4.6s linear infinite var(--rr-draft-rank-delay, 0s);
}

.rr-draft-podium-card--gold {
    --rr-draft-podium-accent: rgba(251, 191, 36, 0.86);
    --rr-draft-podium-glow: rgba(234, 179, 8, 0.28);
    border-color: rgba(234, 179, 8, 0.34);
    background:
        radial-gradient(circle at top left, rgba(251, 191, 36, 0.26), transparent 42%),
        linear-gradient(160deg, rgba(120, 53, 15, 0.36), rgba(15, 23, 42, 0.94) 76%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.1),
        0 24px 40px rgba(161, 98, 7, 0.28),
        0 0 0 1px rgba(251, 191, 36, 0.12);
}

.rr-draft-podium-card--silver {
    --rr-draft-podium-accent: rgba(203, 213, 225, 0.82);
    --rr-draft-podium-glow: rgba(148, 163, 184, 0.22);
    border-color: rgba(203, 213, 225, 0.22);
    background:
        radial-gradient(circle at top left, rgba(226, 232, 240, 0.18), transparent 40%),
        linear-gradient(160deg, rgba(51, 65, 85, 0.42), rgba(15, 23, 42, 0.94) 76%);
}

.rr-draft-podium-card--bronze {
    --rr-draft-podium-accent: rgba(249, 115, 22, 0.84);
    --rr-draft-podium-glow: rgba(194, 65, 12, 0.22);
    border-color: rgba(249, 115, 22, 0.28);
    background:
        radial-gradient(circle at top left, rgba(251, 146, 60, 0.2), transparent 42%),
        linear-gradient(160deg, rgba(124, 45, 18, 0.38), rgba(15, 23, 42, 0.94) 76%);
}

.rr-draft-podium-card--champion {
    --rr-draft-podium-offset: -10px;
    transform: translateY(-10px) scale(1.04);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.12),
        0 30px 54px rgba(161, 98, 7, 0.3),
        0 0 0 1px rgba(251, 191, 36, 0.18),
        0 0 40px rgba(251, 191, 36, 0.18);
}

.rr-draft-podium-card--placeholder {
    border-style: dashed;
    opacity: 0.9;
    box-shadow: none;
}

.rr-draft-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,0.2);
    background: rgba(255, 247, 237, 0.96);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: #e2e8f0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rr-draft-avatar--podium {
    width: 70px;
    height: 70px;
    border-width: 3px;
    box-shadow: 0 16px 28px rgba(2, 6, 23, 0.24);
}

.rr-draft-avatar--mini {
    width: 24px;
    height: 24px;
    border-width: 1px;
}

.rr-draft-podium-card--champion .rr-draft-avatar--podium {
    width: 88px;
    height: 88px;
}

.rr-draft-avatar--photo {
    background: rgba(255, 255, 255, 0.96);
}

.rr-draft-avatar img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 4px;
    box-sizing: border-box;
    display: block;
}

.rr-draft-avatar img.rr-draft-avatar__img--photo {
    object-fit: cover;
    object-position: center top;
    padding: 0;
}

.rr-draft-ranking-list-wrap {
    position: relative;
    z-index: 1;
}

.rr-draft-ranking-list {
    position: relative;
    border: 1px solid rgba(148, 163, 184, 0.14);
    border-radius: 24px;
    overflow: hidden;
    background: linear-gradient(180deg, rgba(2, 6, 23, 0.42), rgba(15, 23, 42, 0.54));
    max-height: 460px;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
    padding: 10px;
    scrollbar-width: none;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-draft-ranking-list-wrap--has-nav .rr-draft-ranking-list {
    padding-bottom: 66px;
}

.rr-draft-ranking-list::-webkit-scrollbar { display: none; }

.rr-draft-ranking-row {
    display: grid;
    grid-template-columns: 46px 30px minmax(0, 1fr) auto;
    gap: 10px;
    align-items: center;
    padding: 10px 12px;
    border: 1px solid rgba(148, 163, 184, 0.12);
    border-radius: 16px;
    background:
        radial-gradient(circle at top left, rgba(59, 130, 246, 0.08), transparent 36%),
        linear-gradient(180deg, rgba(30, 41, 59, 0.62), rgba(15, 23, 42, 0.72));
    box-shadow: 0 10px 18px rgba(0, 0, 0, 0.18);
}

.rr-draft-ranking-row:nth-child(odd) {
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.08), transparent 34%),
        linear-gradient(180deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.74));
}

.rr-draft-ranking-row--mine {
    border-color: rgba(251, 191, 36, 0.42);
    background:
        radial-gradient(circle at top left, rgba(251, 191, 36, 0.18), transparent 34%),
        radial-gradient(circle at right center, rgba(34, 197, 94, 0.12), transparent 28%),
        linear-gradient(180deg, rgba(56, 189, 248, 0.12), rgba(15, 23, 42, 0.82));
    box-shadow:
        0 16px 28px rgba(0, 0, 0, 0.22),
        0 0 0 1px rgba(251, 191, 36, 0.14),
        0 0 28px rgba(251, 191, 36, 0.14);
}

.rr-draft-ranking-row--mine .rr-draft-ranking-row__pos {
    color: #fde68a;
}

.rr-draft-ranking-row--mine .rr-draft-ranking-row__name {
    color: #ffffff;
}

.rr-draft-ranking-row--mine .rr-draft-ranking-row__score {
    border-color: rgba(251, 191, 36, 0.24);
    background: rgba(120, 53, 15, 0.18);
    color: #fef3c7;
}

.rr-draft-ranking-nav {
    position: absolute;
    right: 14px;
    bottom: 16px;
    z-index: 4;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    min-height: 40px;
    padding: 0 0.92rem;
    border: 1px solid rgba(251, 191, 36, 0.28);
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.94));
    color: #f8fafc;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.04em;
    box-shadow:
        0 16px 24px rgba(2, 6, 23, 0.34),
        0 0 0 1px rgba(251, 191, 36, 0.1);
    backdrop-filter: blur(10px);
}

.rr-draft-ranking-nav i {
    color: #fbbf24;
}

.rr-draft-ranking-nav.is-top i {
    color: #60a5fa;
}

.rr-draft-ranking-row--placeholder {
    border-style: dashed;
    opacity: 0.88;
    box-shadow: none;
}

.rr-draft-ranking-row__pos {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 0 0.56rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(15, 23, 42, 0.54);
    font-weight: 900;
    color: #f8fafc;
    letter-spacing: -0.02em;
    font-size: 0.84rem;
}

.rr-draft-ranking-row__avatar {
    display: flex;
    align-items: center;
    justify-content: center;
}

.rr-draft-ranking-row__name {
    font-weight: 800;
    color: #f8fafc;
    overflow: visible;
    text-overflow: clip;
    white-space: normal;
    line-height: 1.08;
    font-size: 0.9rem;
}

.rr-draft-ranking-row__detail {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
}

.rr-draft-ranking-row__prize {
    font-size: 0.82rem;
    color: #86efac;
    font-weight: 900;
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
    line-height: 1.04;
}

.rr-draft-ranking-row__score {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: 0 0.72rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(180deg, rgba(51, 65, 85, 0.4), rgba(30, 41, 59, 0.48));
    color: #cbd5e1;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    box-shadow: 0 8px 14px rgba(2, 6, 23, 0.12);
}

.rr-draft-ranking-row__prize--out {
    color: rgba(229, 231, 235, 0.55);
    font-weight: 600;
}
.rr-draft-ranking-row__name--placeholder,
.rr-draft-podium-card__name--placeholder {
    color: rgba(226, 232, 240, 0.72);
}

.rr-draft-ranking-empty {
    position: relative;
    z-index: 1;
    display: grid;
    justify-items: center;
    gap: 10px;
    padding: 28px 24px;
    text-align: center;
    color: rgba(148, 163, 184, 0.82);
    font-weight: 600;
    border: 1px dashed rgba(148, 163, 184, 0.18);
    border-radius: 20px;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.42), rgba(15, 23, 42, 0.62));
}

.rr-draft-ranking-empty__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    min-height: 38px;
    padding: 0 0.98rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: linear-gradient(180deg, rgba(249, 115, 22, 0.16), rgba(59, 130, 246, 0.14));
    color: #fff7ed;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rr-draft-ranking-empty__title {
    color: #f8fafc;
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: -0.03em;
}

.rr-draft-ranking-empty__text {
    color: rgba(226, 232, 240, 0.72);
    font-size: 0.84rem;
    line-height: 1.5;
    max-width: 420px;
}

@keyframes rrDraftRankingSheen {
    0%, 18% { transform: translateX(-120%); }
    28%, 100% { transform: translateX(150%); }
}

@keyframes rrDraftRankingButtonShine {
    0%, 24% { transform: translateX(-120%); }
    36%, 100% { transform: translateX(130%); }
}

@keyframes rrDraftRankingHaloPulse {
    0%, 100% { transform: scale(0.96); opacity: 0.76; }
    50% { transform: scale(1.05); opacity: 1; }
}

@keyframes rrDraftRankingRingDrift {
    0%, 100% { transform: scale(0.96) rotate(0deg); }
    50% { transform: scale(1.04) rotate(4deg); }
}

@keyframes rrDraftRankingCoreFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

@keyframes rrDraftRankingBadgeFloat {
    0%, 100% { transform: translate3d(0, 0, 0); }
    50% { transform: translate3d(0, -7px, 0); }
}

@keyframes rrDraftRankingCardFloat {
    0%, 100% { transform: translate3d(0, 0, 0) rotate(var(--rr-draft-rank-rotate, 0deg)); }
    50% { transform: translate3d(0, -8px, 0) rotate(calc(var(--rr-draft-rank-rotate, 0deg) + 2deg)); }
}

@keyframes rrDraftRankingCardLift {
    0%, 100% { transform: translateY(var(--rr-draft-podium-offset, 0px)); }
    50% { transform: translateY(calc(var(--rr-draft-podium-offset, 0px) - 6px)); }
}

@keyframes rrDraftRankingBarShine {
    0%, 26% { transform: translateX(-100%); }
    42%, 100% { transform: translateX(130%); }
}

@media (prefers-reduced-motion: reduce) {
    .rr-bolao-prize,
    .rr-bolao-prize::after,
    .rr-bolao-prize-value,
    .rr-draft-prize-card::before,
    .rr-draft-prize-card__logo-wrap::before,
    .rr-draft-prize-card__logo-wrap::after,
    .rr-draft-prize-card__logo-mark,
    .rr-draft-prize-card__badge,
    .rr-draft-prize-card__floater,
    .rr-draft-tab::before,
    .rr-draft-ranking-toolbar::before,
    .rr-draft-ranking-chip,
    .rr-draft-ranking-visual::before,
    .rr-draft-ranking-visual::after,
    .rr-draft-ranking-visual__core,
    .rr-draft-ranking-visual__badge,
    .rr-draft-refresh-btn::before,
    .rr-draft-podium-card,
    .rr-draft-podium-card::after,
    .rr-draft-podium-card__badge,
    .rr-draft-podium-card__bar::after {
        animation: none !important;
    }
}

/* Mobile Adjustments */
@media (max-width: 768px) {
    .rr-draft-container {
        width: 100%;
        height: 100vh;
        border-radius: 12px;
    }
    
    .rr-draft-competitors {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .rr-draft-content {
        padding: 14px;
        overflow: hidden;
        gap: 10px;
    }
}
</style>

<style>
.rr-draft-modal {
    padding: 14px;
    background: rgba(2, 6, 23, 0.86);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.rr-draft-container {
    width: min(1040px, calc(100vw - 28px));
    max-width: 1040px;
    max-height: min(92vh, 900px);
    border-radius: 28px;
    border: 1px solid rgba(251, 146, 60, 0.24);
    background:
        radial-gradient(circle at top right, rgba(249, 115, 22, 0.16), transparent 32%),
        linear-gradient(180deg, #231008 0%, #160904 48%, #110804 100%);
    box-shadow:
        0 28px 80px rgba(0, 0, 0, 0.58),
        0 0 0 1px rgba(255, 255, 255, 0.03);
    position: relative;
    overflow-x: hidden;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    overscroll-behavior: contain;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.rr-draft-container::-webkit-scrollbar {
    display: none;
}

.rr-draft-container::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        linear-gradient(120deg, rgba(255, 255, 255, 0.04), transparent 24%),
        linear-gradient(180deg, rgba(249, 115, 22, 0.08), transparent 48%);
}

.rr-draft-header,
.rr-draft-overview,
.rr-draft-tabs,
.rr-draft-content {
    position: relative;
    z-index: 1;
}

.rr-draft-header {
    padding: 18px 24px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
}

.rr-draft-header__actions {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
}

.rr-draft-header__capacity {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 40px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background:
        linear-gradient(135deg, rgba(249, 115, 22, 0.18), rgba(59, 130, 246, 0.12)),
        rgba(15, 23, 42, 0.64);
    color: #fff7ed;
    font-size: 0.74rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.12),
        0 16px 28px rgba(2, 6, 23, 0.24);
    backdrop-filter: blur(12px);
}

.rr-draft-header__capacity i {
    color: #fbbf24;
}

.rr-draft-header__capacity strong {
    color: #f8fafc;
    font-size: 0.9rem;
    letter-spacing: 0.04em;
}

.rr-draft-refresh-btn--header {
    min-height: 40px;
    min-width: 40px;
    padding: 0 0.94rem;
    border-radius: 999px;
    gap: 0.46rem;
    font-size: 0.72rem;
    letter-spacing: 0.06em;
    box-shadow:
        0 14px 24px rgba(37, 99, 235, 0.22),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

.rr-draft-refresh-btn--header i {
    font-size: 0.82rem;
}

.rr-draft-header__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    padding: 0 14px;
    margin: 0;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.18), rgba(59, 130, 246, 0.12));
    color: #fff7ed;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    box-shadow: 0 12px 24px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(12px);
}

.rr-draft-header__eyebrow i {
    color: #f59e0be6;
}

.rr-draft-header__close {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: 1px solid rgba(254, 202, 202, 0.52);
    background:
        radial-gradient(circle at 30% 30%, rgba(254, 226, 226, 0.36), transparent 40%),
        linear-gradient(180deg, #ff5a5f 0%, #e11d48 50%, #991b1b 100%);
    color: #fff7f7;
    box-shadow:
        0 18px 30px rgba(127, 29, 29, 0.38),
        inset 0 1px 0 rgba(255, 255, 255, 0.22);
}

.rr-draft-header__close:hover {
    transform: translateY(-1px) scale(1.04);
}

.rr-draft-overview {
    padding: 10px 24px 18px;
    display: block;
}

.rr-draft-prize-card,
.rr-draft-meta-card,
.rr-draft-toolbar__item,
.rr-draft-main,
.rr-draft-ranking-shell {
    border: 1px solid rgba(148, 163, 184, 0.14);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-draft-prize-card {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(240px, 0.85fr);
    gap: 20px;
    padding: 24px;
    border-radius: 28px;
    overflow: hidden;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.28), transparent 32%),
        radial-gradient(circle at right center, rgba(59, 130, 246, 0.18), transparent 28%),
        linear-gradient(140deg, rgba(124, 45, 18, 0.48), rgba(30, 41, 59, 0.82) 68%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 24px 44px rgba(2, 6, 23, 0.26);
}

.rr-draft-prize-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.06) 20%, transparent 40%);
    transform: translateX(-100%);
    animation: rrDraftRankingSheen 9s linear infinite;
    pointer-events: none;
}

.rr-draft-prize-card__copy,
.rr-draft-prize-card__visual {
    position: relative;
    z-index: 1;
}

.rr-draft-prize-card__copy {
    display: grid;
    align-content: start;
    gap: 14px;
}

.rr-draft-prize-card__topline {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.rr-draft-prize-card__status {
    display: inline-flex;
    align-items: center;
    gap: 0.48rem;
    min-height: 36px;
    padding: 0 0.98rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08));
    color: #fff7ed;
    font-size: 0.74rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow: 0 12px 22px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(14px);
}

.rr-draft-prize-card__status i {
    color: #fde68a;
}

.rr-draft-prize-card__title {
    margin: 0;
    font-size: clamp(1.8rem, 4vw, 2.7rem);
    line-height: 0.96;
    font-weight: 900;
    letter-spacing: -0.05em;
    color: #fff7ed;
}

.rr-draft-prize-card__label,
.rr-draft-meta-card__label,
.rr-draft-budget__label,
.rr-draft-pool-kicker {
    display: block;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 800;
}

.rr-draft-prize-card__label {
    color: rgba(255, 237, 213, 0.74);
}

.rr-draft-prize-card__value {
    font-size: clamp(2.8rem, 6vw, 4.8rem);
    line-height: 0.88;
    font-weight: 900;
    color: #fff7ed;
    letter-spacing: -0.07em;
    text-shadow: 0 14px 34px rgba(124, 45, 18, 0.28);
}

.rr-draft-prize-card__note {
    min-height: 1.2rem;
    color: #fde68a;
    font-size: 0.96rem;
    font-weight: 800;
    max-width: 540px;
}

.rr-draft-prize-card__note[data-tone="error"] { color: #fca5a5; }
.rr-draft-prize-card__note[data-tone="success"] { color: #86efac; }
.rr-draft-prize-card__note[data-tone="warn"] { color: #fde68a; }

.rr-draft-prize-card__subnote {
    color: rgba(255, 237, 213, 0.72);
    font-size: 0.88rem;
    line-height: 1.5;
    max-width: 560px;
}

.rr-draft-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.rr-draft-meta-card {
    display: grid;
    gap: 6px;
    padding: 14px 16px;
    border-radius: 20px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(15, 23, 42, 0.28));
    box-shadow: 0 14px 26px rgba(2, 6, 23, 0.16);
    backdrop-filter: blur(14px);
}

.rr-draft-meta-card__label {
    color: rgba(251, 191, 36, 0.82);
}

.rr-draft-meta-card__value,
.rr-draft-budget__value {
    font-size: 1.05rem;
    font-weight: 900;
    color: #f8fafc;
}

.rr-draft-prize-card__visual {
    position: relative;
    min-height: 250px;
    width: min(100%, 320px);
    margin-left: auto;
}

.rr-draft-prize-card__logo-wrap {
    position: absolute;
    inset: 50% auto auto 50%;
    transform: translate(-50%, -50%);
    width: min(100%, 220px);
    aspect-ratio: 1 / 1;
    display: grid;
    place-items: center;
}

.rr-draft-prize-card__logo-wrap::before,
.rr-draft-prize-card__logo-wrap::after {
    content: "";
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
}

.rr-draft-prize-card__logo-wrap::before {
    inset: 8%;
    background: radial-gradient(circle at 50% 50%, rgba(191, 219, 254, 0.34), rgba(59, 130, 246, 0.12) 42%, rgba(14, 165, 233, 0) 70%);
    filter: blur(14px);
    animation: rrDraftRankingHaloPulse 5.8s ease-in-out infinite;
}

.rr-draft-prize-card__logo-wrap::after {
    inset: 16%;
    border: 1px solid rgba(255, 255, 255, 0.16);
    box-shadow: inset 0 0 0 1px rgba(191, 219, 254, 0.08);
    animation: rrDraftRankingRingDrift 7.2s ease-in-out infinite;
}

.rr-draft-prize-card__logo-mark {
    position: relative;
    z-index: 2;
    width: 124px;
    height: 124px;
    display: grid;
    place-items: center;
    border-radius: 36px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    background: linear-gradient(160deg, rgba(249, 115, 22, 0.28), rgba(59, 130, 246, 0.22));
    color: #fde68a;
    font-size: 3rem;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 24px 42px rgba(2, 6, 23, 0.28);
    backdrop-filter: blur(12px);
    animation: rrDraftRankingCoreFloat 4.8s ease-in-out infinite;
}

.rr-draft-prize-card__badge {
    position: absolute;
    top: 4%;
    left: 2%;
    z-index: 3;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    min-height: 36px;
    padding: 0 0.96rem;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: #fff7ed;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    box-shadow: 0 12px 22px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(12px);
    animation: rrDraftRankingBadgeFloat 5.9s ease-in-out infinite;
}

.rr-draft-prize-card__badge i {
    color: #fde68a;
}

.rr-draft-prize-card__floater {
    position: absolute;
    display: grid;
    gap: 0.16rem;
    min-width: 146px;
    padding: 0.8rem 0.9rem;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08));
    box-shadow: 0 16px 28px rgba(2, 6, 23, 0.18);
    color: #eff6ff;
    backdrop-filter: blur(14px);
}

.rr-draft-prize-card__floater i { color: #fde68a; }
.rr-draft-prize-card__floater strong { font-size: 0.88rem; line-height: 1.05; }
.rr-draft-prize-card__floater span { font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.78; }
.rr-draft-prize-card__floater--top {
    top: 8%;
    right: 0;
    --rr-draft-rank-rotate: -6deg;
    transform: rotate(var(--rr-draft-rank-rotate));
    animation: rrDraftRankingCardFloat 6.5s ease-in-out infinite;
}
.rr-draft-prize-card__floater--bottom {
    left: 0;
    bottom: 6%;
    --rr-draft-rank-rotate: 6deg;
    transform: rotate(var(--rr-draft-rank-rotate));
    animation: rrDraftRankingCardFloat 7.1s ease-in-out infinite reverse;
}

.rr-draft-tabs {
    gap: 12px;
    padding: 0 24px 16px;
    background: transparent;
    border-bottom: none;
}

.rr-draft-tab {
    position: relative;
    min-width: 168px;
    min-height: 52px;
    padding: 0 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.62rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(15, 23, 42, 0.54));
    color: rgba(248, 250, 252, 0.84);
    font-weight: 800;
    box-shadow: 0 14px 26px rgba(2, 6, 23, 0.16);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease, color 0.2s ease, border-color 0.2s ease, border-width 0.2s ease;
}

.rr-draft-tab[data-tab="team"] {
    border-color: rgba(34, 197, 94, 0.34);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.88), rgba(22, 163, 74, 0.82) 55%, rgba(22, 101, 52, 0.9) 100%);
    color: rgba(255, 255, 255, 0.94);
    box-shadow:
        0 16px 28px rgba(22, 163, 74, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

.rr-draft-tab[data-tab="ranking"] {
    border-color: rgba(249, 115, 22, 0.34);
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.88), rgba(249, 115, 22, 0.82) 55%, rgba(194, 65, 12, 0.9) 100%);
    color: rgba(255, 255, 255, 0.94);
    box-shadow:
        0 16px 28px rgba(234, 88, 12, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

.rr-draft-tab:hover {
    transform: translateY(-2px);
    color: #fff;
    box-shadow: 0 18px 30px rgba(2, 6, 23, 0.2);
}

.rr-draft-tab.active {
    background: linear-gradient(135deg, #f59e0be6, #2563eb);
    border-width: 2px;
    border-color: rgba(251, 146, 60, 0.96);
    box-shadow:
        0 18px 30px rgba(37, 99, 235, 0.22),
        0 0 0 2px rgba(249, 115, 22, 0.18);
    color: #fff;
}

.rr-draft-tab[data-tab="team"].active {
    background: linear-gradient(135deg, #22c55e, #16a34a 52%, #166534);
    border-color: rgba(251, 146, 60, 0.96);
    box-shadow:
        0 18px 30px rgba(22, 163, 74, 0.34),
        0 0 0 2px rgba(249, 115, 22, 0.18);
    color: #fff;
}

.rr-draft-tab[data-tab="ranking"].active {
    background: linear-gradient(135deg, #f59e0be6, #f97316 52%, #c2410c);
    border-color: rgba(251, 146, 60, 0.96);
    box-shadow:
        0 18px 30px rgba(234, 88, 12, 0.34),
        0 0 0 2px rgba(249, 115, 22, 0.18);
    color: #fff;
}

.rr-draft-tab::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.18) 30%, transparent 56%);
    transform: translateX(-120%);
    animation: rrDraftRankingButtonShine 5.2s linear infinite;
}

.rr-draft-tab > * {
    position: relative;
    z-index: 1;
}

.rr-draft-tab i {
    color: #fde68a;
}

.rr-draft-tab.active i {
    color: #fff7ed;
}

.rr-draft-content {
    padding: 0 24px 24px;
    overflow: visible;
    min-height: auto;
}

.rr-draft-workspace {
    display: block;
}

.rr-draft-stage {
    min-width: 0;
}

.rr-draft-panel.active {
    display: block;
    height: auto;
}

.rr-draft-builder {
    display: grid;
    grid-template-rows: auto auto auto auto;
    gap: 14px;
    height: auto;
    min-height: auto;
}

.rr-draft-builder__head {
    position: relative;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-end;
    padding: 18px 20px;
    border-radius: 24px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.14), transparent 30%),
        radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.12), transparent 28%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.52), rgba(15, 23, 42, 0.72));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    overflow: hidden;
}

.rr-draft-builder__head::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.05) 24%, transparent 44%);
    transform: translateX(-100%);
    animation: rrDraftRankingSheen 10s linear infinite;
    pointer-events: none;
}

.rr-draft-pool-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    width: fit-content;
    min-height: 34px;
    padding: 0 0.88rem;
    color: #fff7ed;
    margin-bottom: 10px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: linear-gradient(180deg, rgba(249, 115, 22, 0.16), rgba(59, 130, 246, 0.12));
    box-shadow: 0 12px 22px rgba(2, 6, 23, 0.14);
}

.rr-draft-pool-title {
    font-size: 1.18rem;
    font-weight: 900;
    color: #fff7ed;
    letter-spacing: -0.03em;
}

.rr-draft-selection-status {
    display: block;
    font-size: 1.06rem;
    font-weight: 900;
    color: #f8fafc;
}

.rr-draft-action-note {
    margin: 6px 0 0;
    color: rgba(226, 232, 240, 0.72);
    font-size: 0.88rem;
    line-height: 1.45;
    max-width: 440px;
}

.rr-draft-action-note[data-tone="error"] { color: #fca5a5; }
.rr-draft-action-note[data-tone="success"] { color: #86efac; }
.rr-draft-action-note[data-tone="warn"] { color: #fde68a; }

.rr-draft-team-slots {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.rr-draft-slot {
    position: relative;
    min-height: 148px;
    padding: 16px 10px 14px;
    border-radius: 20px;
    border: 1px dashed rgba(251, 146, 60, 0.26);
    background: linear-gradient(160deg, rgba(15, 23, 42, 0.5), rgba(41, 20, 9, 0.62));
    display: grid;
    justify-items: center;
    align-content: center;
    gap: 8px;
    text-align: center;
}

.rr-draft-slot--filled {
    border-style: solid;
    border-color: rgba(34, 197, 94, 0.42);
    background: linear-gradient(160deg, rgba(11, 38, 24, 0.94), rgba(15, 23, 42, 0.92));
}

.rr-draft-slot__index {
    position: absolute;
    top: 10px;
    left: 10px;
    min-width: 26px;
    height: 26px;
    padding: 0 8px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(249, 115, 22, 0.18);
    color: #fdba74;
    font-size: 0.72rem;
    font-weight: 900;
}

.rr-draft-slot__remove {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.74);
    color: rgba(248, 250, 252, 0.9);
    font-size: 1rem;
    line-height: 1;
}

.rr-draft-slot__photo {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    object-fit: contain;
    padding: 6px;
    box-sizing: border-box;
    background: rgba(255, 247, 237, 0.96);
    border: 2px solid rgba(251, 146, 60, 0.24);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.28);
}

.rr-draft-slot__name {
    font-size: 0.9rem;
    font-weight: 900;
    color: #f8fafc;
}

.rr-draft-slot__empty {
    max-width: 100px;
    color: rgba(226, 232, 240, 0.64);
    font-size: 0.8rem;
    line-height: 1.35;
}

.rr-draft-toolbar {
    display: grid;
    grid-template-columns: minmax(140px, 180px) minmax(240px, 280px);
    gap: 12px;
    align-items: stretch;
}

.rr-draft-toolbar__slots {
    padding: 12px;
    border-radius: 18px;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.08), transparent 34%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(15, 23, 42, 0.42));
    box-shadow: 0 14px 24px rgba(2, 6, 23, 0.16);
    backdrop-filter: blur(14px);
}

.rr-draft-toolbar__slots--mobile {
    display: none;
}

.rr-draft-team-slots--compact {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
}

.rr-draft-team-slots--compact .rr-draft-slot {
    min-height: 92px;
    padding: 10px 4px 8px;
    border-radius: 16px;
    gap: 5px;
}

.rr-draft-team-slots--compact .rr-draft-slot__photo {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    padding: 4px;
}

.rr-draft-team-slots--compact .rr-draft-slot__name {
    max-width: 100%;
    font-size: 0.68rem;
    line-height: 1.05;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-draft-team-slots--compact .rr-draft-slot__empty {
    max-width: 46px;
    font-size: 0.58rem;
    line-height: 1.1;
}

.rr-draft-toolbar__item {
    display: grid;
    gap: 8px;
    padding: 14px 16px;
    border-radius: 18px;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.08), transparent 34%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(15, 23, 42, 0.42));
    box-shadow: 0 14px 24px rgba(2, 6, 23, 0.16);
    backdrop-filter: blur(14px);
}

.rr-draft-budget__label {
    color: rgba(251, 191, 36, 0.82);
}

.rr-draft-pay-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    padding: 16px 18px;
    background: linear-gradient(135deg, #f59e0be6, #2563eb);
    color: #fff7ed;
    box-shadow: 0 18px 32px rgba(37, 99, 235, 0.24);
    min-height: 100%;
    overflow: hidden;
    text-align: left;
}

.rr-draft-pay-btn:hover:not(:disabled) {
    box-shadow: 0 20px 36px rgba(37, 99, 235, 0.28);
}

.rr-draft-pay-btn.is-disabled,
.rr-draft-pay-btn:disabled {
    background: linear-gradient(135deg, rgba(71, 85, 105, 0.55), rgba(30, 41, 59, 0.72));
    color: rgba(226, 232, 240, 0.72);
    box-shadow: none;
}

.rr-draft-pay-btn--locked {
    background: linear-gradient(135deg, rgba(51, 65, 85, 0.94), rgba(15, 23, 42, 0.96));
    color: #e2e8f0;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.06),
        0 16px 28px rgba(2, 6, 23, 0.16);
}

.rr-draft-pay-btn__stack {
    position: relative;
    z-index: 1;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.rr-draft-pay-btn__icon {
    flex: none;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.16);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    font-size: 0.92rem;
}

.rr-draft-pay-btn__copy {
    min-width: 0;
    display: grid;
    gap: 2px;
}

.rr-draft-pay-btn__label {
    display: block;
    font-size: 1rem;
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.rr-draft-pay-btn__sub {
    display: block;
    font-size: 0.75rem;
    line-height: 1.2;
    color: rgba(255, 247, 237, 0.82);
}

.rr-draft-pay-btn--locked .rr-draft-pay-btn__icon {
    background: rgba(15, 23, 42, 0.34);
    color: #f8fafc;
}

.rr-draft-pay-btn--locked .rr-draft-pay-btn__sub {
    color: rgba(226, 232, 240, 0.84);
}

.rr-draft-pay-btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.26) 30%, transparent 56%);
    transform: translateX(-120%);
    animation: rrDraftRankingButtonShine 4.8s linear infinite;
}

.rr-draft-pay-btn > * {
    position: relative;
    z-index: 1;
}

.rr-draft-pay-btn.is-disabled::before,
.rr-draft-pay-btn:disabled::before {
    animation: none;
}

.rr-draft-main {
    border-radius: 24px;
    padding: 18px;
    display: block;
    gap: 14px;
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 28%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.08), transparent 26%),
        rgba(15, 23, 42, 0.42);
    overflow: visible;
}

.rr-draft-pool-head {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: flex-end;
}

.rr-draft-pool-copy {
    color: rgba(148, 163, 184, 0.76);
    font-size: 0.88rem;
    max-width: 360px;
}

.rr-draft-search {
    position: relative;
    margin: 14px 0 16px;
}

.rr-draft-search__icon {
    position: absolute;
    top: 50%;
    left: 14px;
    transform: translateY(-50%);
    color: rgba(148, 163, 184, 0.82);
    font-size: 0.92rem;
    pointer-events: none;
}

.rr-draft-search__input {
    width: 100%;
    min-height: 48px;
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.88), rgba(30, 41, 59, 0.9));
    color: #f8fafc;
    padding: 0 16px 0 40px;
    font-size: 0.94rem;
    font-weight: 700;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-draft-search__input::placeholder {
    color: rgba(148, 163, 184, 0.72);
}

.rr-draft-search__input:focus {
    outline: none;
    border-color: rgba(249, 115, 22, 0.4);
    box-shadow:
        0 0 0 3px rgba(249, 115, 22, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-draft-competitors {
    margin-bottom: 0;
    min-height: auto;
    max-height: none;
    overflow: visible;
    padding-right: 0;
    padding-bottom: 8px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.rr-draft-competitor-card {
    position: relative;
    min-height: 192px;
    border-radius: 18px;
    padding: 0;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background-color: rgba(15, 23, 42, 0.28);
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.22);
    display: grid;
    grid-template-rows: 1fr auto;
    justify-items: start;
    align-content: end;
    gap: 0;
    text-align: left;
    cursor: pointer;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    overflow: hidden;
}

.rr-draft-competitor-card:hover {
    transform: translateY(-2px);
    border-color: rgba(249, 115, 22, 0.34);
}

.rr-draft-competitor-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.12) 0%, rgba(15, 23, 42, 0.56) 48%, rgba(15, 23, 42, 0.88) 100%),
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.08), transparent 34%);
    z-index: 1;
    pointer-events: none;
}

.rr-draft-competitor-card.selected {
    border-color: rgba(34, 197, 94, 0.64);
}

.rr-draft-competitor__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.06) 0%, rgba(15, 23, 42, 0.88) 100%);
    z-index: 1;
    pointer-events: none;
}

.rr-draft-competitor__body {
    position: relative;
    z-index: 2;
    display: grid;
    gap: 0;
    align-items: end;
    width: 100%;
    min-height: 100%;
    padding: 16px;
    background: linear-gradient(180deg, transparent 60%, rgba(15, 23, 42, 0.94) 100%);
    box-sizing: border-box;
}

.rr-draft-competitor__name {
    position: relative;
    z-index: 2;
    width: 100%;
    font-size: 1rem;
    min-height: auto;
    color: #ffffff;
    font-weight: 900;
    line-height: 1.2;
    text-shadow: 0 14px 24px rgba(7, 17, 27, 0.38);
}

.rr-draft-competitor-card.selected::after {
    content: 'Selecionado';
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 8px;
    border-radius: 999px;
    background: rgba(34, 197, 94, 0.16);
    color: #86efac;
    font-size: 0.64rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.rr-draft-competitor-card.disabled {
    opacity: 0.52;
    cursor: not-allowed;
    transform: none;
}

.rr-draft-competitor__photo {
    position: absolute;
    inset: 0;
    background-position: center center;
    background-repeat: no-repeat;
    background-size: cover;
    opacity: 0.98;
    transform: scale(1.03);
    filter: saturate(1.02) contrast(0.94) brightness(0.84);
    z-index: 0;
    pointer-events: none;
}

.rr-draft-empty-state {
    min-height: 220px;
    display: grid;
    place-items: center;
    border-radius: 18px;
    border: 1px dashed rgba(148, 163, 184, 0.22);
    color: rgba(148, 163, 184, 0.72);
    text-align: center;
    padding: 20px;
    line-height: 1.5;
}

.rr-draft-ranking {
    height: 100%;
    overflow: auto;
    padding-right: 4px;
}

.rr-draft-ranking-shell {
    border-radius: 24px;
    padding: 18px;
    background: rgba(15, 23, 42, 0.42);
}

@media (min-width: 1180px) {
    .rr-draft-container {
        width: min(1600px, calc(100vw - 48px));
        max-width: 1600px;
        max-height: min(94vh, 980px);
    }

    .rr-draft-workspace {
        display: block;
        padding: 10px 24px 24px;
    }

    .rr-draft-overview,
    .rr-draft-content {
        padding-left: 0;
        padding-right: 0;
    }

    .rr-draft-overview {
        padding-top: 0;
        padding-bottom: 0;
        position: sticky;
        top: 0;
        align-self: start;
    }

    .rr-draft-stage {
        display: grid;
        align-content: start;
        min-width: 0;
    }

    .rr-draft-content {
        padding-top: 0;
        padding-bottom: 0;
    }

    .rr-draft-prize-card {
        grid-template-columns: 1fr;
        gap: 18px;
        min-height: 100%;
    }

    .rr-draft-prize-card__copy {
        gap: 16px;
    }

    .rr-draft-prize-card__title {
        font-size: clamp(2rem, 2.2vw, 2.8rem);
    }

    .rr-draft-prize-card__value {
        font-size: clamp(3rem, 4.4vw, 4.9rem);
    }

    .rr-draft-prize-card__visual {
        width: min(100%, 340px);
        min-height: 220px;
        margin-inline: auto;
    }

    .rr-draft-builder {
        gap: 16px;
    }

    .rr-draft-toolbar {
        grid-template-columns: minmax(160px, 200px) minmax(260px, 1.15fr);
    }

    .rr-draft-pay-btn {
        min-height: 100%;
    }

    .rr-draft-main {
        padding: 20px;
    }

    .rr-draft-competitors {
        grid-template-columns: repeat(auto-fill, minmax(154px, 1fr));
    }
}

.rr-draft-ranking-toolbar {
    padding: 12px 14px;
    border-radius: 18px;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.18), transparent 34%),
        radial-gradient(circle at bottom right, rgba(59, 130, 246, 0.14), transparent 28%),
        linear-gradient(135deg, rgba(30, 41, 59, 0.72), rgba(124, 45, 18, 0.22));
}

.rr-draft-ranking-title {
    font-size: 1rem;
}

.rr-draft-podium-card {
    border-radius: 18px;
}

.rr-draft-ranking-list {
    border-radius: 18px;
    background: rgba(2, 6, 23, 0.3);
}

.rr-draft-ranking-row {
    border-radius: 14px;
}

@media (max-width: 900px) {
    .rr-draft-overview {
        grid-template-columns: 1fr;
    }

    .rr-draft-prize-card {
        grid-template-columns: 1fr;
    }

    .rr-draft-prize-card__visual {
        width: 100%;
        max-width: 240px;
        min-height: 190px;
        margin-inline: auto;
    }

    .rr-draft-toolbar {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rr-draft-ranking-toolbar {
        grid-template-columns: 1fr;
    }

    .rr-draft-ranking-toolbar__actions {
        width: 100%;
        justify-items: stretch;
    }

    .rr-draft-ranking-visual {
        width: 100%;
        max-width: 180px;
        margin-inline: auto;
    }

    .rr-draft-refresh-btn {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .rr-draft-modal {
        padding: 0;
        align-items: flex-end;
    }

    .rr-draft-container {
        width: 100%;
        max-width: 100%;
        height: auto;
        min-height: 100vh;
        max-height: 100vh;
        border-radius: 24px 24px 0 0;
    }

    .rr-draft-header,
    .rr-draft-overview,
    .rr-draft-tabs,
    .rr-draft-content {
        padding-left: 16px;
        padding-right: 16px;
    }

    .rr-draft-header {
        padding-top: 16px;
        padding-bottom: 0;
    }

    .rr-draft-tabs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding-bottom: 12px;
        padding-top: 4px;
        overflow: visible;
        scrollbar-width: none;
        border-radius: 20px;
    }

    .rr-draft-content {
        -webkit-overflow-scrolling: touch;
        padding-bottom: 24px;
    }

    .rr-draft-tabs::-webkit-scrollbar {
        display: none;
    }

    .rr-draft-meta-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rr-draft-prize-card {
        padding: 20px;
    }

    .rr-draft-prize-card__topline,
    .rr-draft-prize-card__value,
    .rr-draft-meta-card--deadline,
    .rr-draft-toolbar__item--entry,
    .rr-draft-selection-status {
        display: none;
    }

    .rr-draft-team-slots--desktop {
        display: none;
    }

    .rr-draft-prize-card__title {
        font-size: clamp(1.5rem, 4vw, 2.3rem);
    }

    .rr-draft-prize-card__value {
        font-size: clamp(2.4rem, 7vw, 3.8rem);
    }

    .rr-draft-meta-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .rr-draft-tabs {
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(15, 23, 42, 0.08));
    }

    .rr-draft-tab {
        min-width: 0;
        width: 100%;
        min-height: 52px;
        padding: 0 16px;
        border-radius: 18px;
        border-color: rgba(255, 255, 255, 0.16);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(15, 23, 42, 0.7));
        box-shadow:
            0 16px 26px rgba(2, 6, 23, 0.18),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }

    .rr-draft-tab.active {
        transform: translateY(-1px);
        box-shadow:
            0 18px 30px rgba(37, 99, 235, 0.26),
            inset 0 1px 0 rgba(255, 255, 255, 0.18);
    }

    .rr-draft-builder__head,
    .rr-draft-pool-head {
        flex-direction: column;
        align-items: flex-start;
    }

    .rr-draft-builder__head {
        padding: 14px 16px;
        border-radius: 20px;
        background:
            radial-gradient(circle at top left, rgba(249, 115, 22, 0.18), transparent 32%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.68));
    }

    .rr-draft-search {
        margin: 12px 0 14px;
    }

    .rr-draft-toolbar {
        grid-template-columns: 1fr;
    }

    .rr-draft-toolbar__slots--mobile {
        display: block;
        grid-column: 1 / -1;
    }

    .rr-draft-pay-btn {
        grid-column: 1 / -1;
    }

    .rr-draft-builder {
        height: auto;
        min-height: auto;
        grid-template-rows: auto auto auto auto;
    }

    .rr-draft-main {
        padding: 16px;
        display: block;
        min-height: auto;
        overflow: visible;
    }

    .rr-draft-competitors {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        overflow: visible;
        padding-right: 0;
        padding-bottom: 8px;
    }

    .rr-draft-competitor-card {
        min-height: 126px;
        padding: 10px 8px;
        border-radius: 16px;
    }

    .rr-draft-competitor-card.selected::after {
        top: 8px;
        right: 8px;
        padding: 3px 6px;
        font-size: 0.58rem;
    }

    .rr-draft-competitor__photo {
        opacity: 0.38;
    }

    .rr-draft-competitor__name {
        min-height: 34px;
        font-size: 0.78rem;
    }
}

@media (max-width: 640px) {
    .rr-draft-container {
        min-height: 100dvh;
        max-height: 100dvh;
        border-radius: 24px 24px 0 0;
    }

    .rr-draft-header,
    .rr-draft-overview,
    .rr-draft-tabs,
    .rr-draft-content {
        padding-left: 14px;
        padding-right: 14px;
    }

    .rr-draft-header {
        gap: 10px;
        padding-top: 12px;
        padding-bottom: 0;
    }

    .rr-draft-header__capacity {
        min-height: 36px;
        padding: 0 12px;
        font-size: 0.66rem;
        letter-spacing: 0.06em;
    }

    .rr-draft-header__capacity strong {
        font-size: 0.82rem;
    }

    .rr-draft-header__actions {
        gap: 8px;
    }

    .rr-draft-refresh-btn--header {
        min-height: 36px;
        min-width: 36px;
        padding: 0 0.82rem;
        font-size: 0.66rem;
    }

    .rr-draft-header__eyebrow {
        padding: 4px 8px;
        font-size: 0.56rem;
    }

    #rrDraftLeagueInfo,
    .rr-draft-header__hint,
    .rr-draft-prize-card__note,
    .rr-draft-action-note,
    .rr-draft-pool-copy {
        display: none;
    }

    #rrDraftCtaHint[data-visible="1"] {
        display: block;
    }

    .rr-draft-header__close {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        font-size: 1.55rem;
    }

    .rr-draft-overview {
        gap: 8px;
        padding-bottom: 10px;
    }

    .rr-draft-prize-card {
        gap: 12px;
        padding: 16px 14px;
        border-radius: 18px;
    }

    .rr-draft-prize-card__title {
        font-size: 1.14rem;
        line-height: 1.08;
    }

    .rr-draft-prize-card__label,
    .rr-draft-meta-card__label,
    .rr-draft-budget__label,
        .rr-draft-pool-kicker {
        font-size: 0.58rem;
    }

    .rr-draft-meta-grid {
        gap: 8px;
    }

    .rr-draft-meta-card {
        gap: 4px;
        padding: 10px;
        border-radius: 14px;
    }

    .rr-draft-meta-card__value,
    .rr-draft-budget__value {
        font-size: 0.92rem;
    }

    .rr-draft-prize-card__visual {
        display: none;
    }

    .rr-draft-tabs {
        gap: 8px;
        padding-top: 2px;
        padding-bottom: 10px;
        overflow: visible;
    }

    .rr-draft-tab {
        min-height: 50px;
        padding: 10px 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.76rem;
        letter-spacing: 0.01em;
    }

    .rr-draft-content {
        padding-bottom: calc(118px + env(safe-area-inset-bottom, 0px));
    }

    .rr-draft-builder {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .rr-draft-builder__head {
        order: 1;
        display: grid;
        gap: 6px;
        padding: 12px 14px;
    }

    .rr-draft-pool-kicker {
        margin-bottom: 2px;
    }

    .rr-draft-builder__head .rr-draft-pool-kicker {
        display: none;
    }

    .rr-draft-pool-title {
        font-size: 0.94rem;
        text-align: center;
        width: 100%;
    }

    .rr-draft-main {
        order: 3;
        padding: 12px;
        border-radius: 16px;
    }

    .rr-draft-pool-head {
        margin-bottom: 8px;
        align-items: center;
    }

    .rr-draft-search__input {
        min-height: 46px;
        border-radius: 14px;
        font-size: 0.9rem;
    }

    .rr-draft-competitors {
        gap: 8px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding-bottom: 0;
    }

    .rr-draft-competitor-card {
        min-height: 108px;
        padding: 10px 6px 8px;
        border-radius: 14px;
        gap: 5px;
    }

    .rr-draft-competitor-card.selected::after {
        top: 6px;
        right: 6px;
        padding: 3px 6px;
        font-size: 0.52rem;
    }

    .rr-draft-competitor__photo {
        opacity: 0.34;
    }

    .rr-draft-competitor__name {
        min-height: 28px;
        font-size: 0.72rem;
        line-height: 1.15;
    }

    .rr-draft-empty-state {
        min-height: 120px;
        padding: 16px;
    }

    .rr-draft-toolbar {
        order: 4;
        position: sticky;
        bottom: 0;
        z-index: 5;
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 10px;
        border-radius: 18px 18px 0 0;
        border: 1px solid rgba(148, 163, 184, 0.14);
        background: linear-gradient(180deg, rgba(2, 6, 23, 0.08), rgba(2, 6, 23, 0.94) 18%, rgba(2, 6, 23, 0.98) 100%);
        backdrop-filter: blur(12px);
        box-shadow: 0 -16px 32px rgba(2, 6, 23, 0.42);
    }

    .rr-draft-toolbar__slots {
        grid-column: 1 / -1;
        padding: 8px;
        border-radius: 14px;
    }

    .rr-draft-team-slots--compact {
        gap: 6px;
    }

    .rr-draft-team-slots--compact .rr-draft-slot {
        min-height: 74px;
        padding: 8px 3px 6px;
        border-radius: 12px;
        gap: 4px;
    }

    .rr-draft-team-slots--compact .rr-draft-slot__index {
        top: 5px;
        left: 5px;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        font-size: 0.54rem;
    }

    .rr-draft-team-slots--compact .rr-draft-slot__remove {
        top: 5px;
        right: 5px;
        width: 18px;
        height: 18px;
        font-size: 0.72rem;
    }

    .rr-draft-team-slots--compact .rr-draft-slot__photo {
        width: 28px;
        height: 28px;
        border-radius: 10px;
        padding: 3px;
    }

    .rr-draft-team-slots--compact .rr-draft-slot__name {
        font-size: 0.54rem;
        line-height: 1.05;
    }

    .rr-draft-team-slots--compact .rr-draft-slot__empty {
        max-width: 40px;
        font-size: 0.5rem;
        line-height: 1.1;
    }

    .rr-draft-toolbar__item {
        gap: 4px;
        padding: 10px 8px;
        border-radius: 14px;
    }

    .rr-draft-pay-btn {
        min-height: 50px;
        padding: 12px;
        border-radius: 14px;
        font-size: 0.76rem;
        letter-spacing: 0.08em;
    }

    .rr-draft-pay-btn__stack {
        gap: 8px;
    }

    .rr-draft-pay-btn__icon {
        width: 28px;
        height: 28px;
        font-size: 0.78rem;
    }

    .rr-draft-pay-btn__label {
        font-size: 0.86rem;
    }

    .rr-draft-pay-btn__sub {
        font-size: 0.66rem;
    }

    .rr-draft-ranking-shell {
        padding: 12px;
        border-radius: 16px;
    }

    .rr-draft-ranking-toolbar {
        padding: 14px 12px;
        gap: 12px;
    }

    .rr-draft-ranking-toolbar__eyebrow,
    .rr-draft-ranking-chip,
    .rr-draft-ranking-empty__badge {
        min-height: 34px;
        font-size: 0.68rem;
    }

    .rr-draft-podium {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .rr-draft-podium-card--champion {
        grid-column: auto;
        order: 0;
        transform: translateY(0) scale(1);
    }

    .rr-draft-podium-card {
        min-height: 194px;
        padding: 12px 10px calc(22px + var(--rr-draft-bar-height, 18px));
    }

    .rr-draft-avatar--podium {
        width: 54px;
        height: 54px;
    }

    .rr-draft-podium-card--champion .rr-draft-avatar--podium {
        width: 62px;
        height: 62px;
    }

    .rr-draft-podium-card__scene {
        min-height: 74px;
    }

    .rr-draft-podium-card__pill {
        grid-template-columns: 26px minmax(0, 1fr);
        min-height: 48px;
        padding: 8px 10px;
        border-radius: 16px;
    }

    .rr-draft-podium-card__pill i {
        width: 26px;
        height: 26px;
        font-size: 0.76rem;
    }

    .rr-draft-podium-card__pill strong {
        font-size: 0.84rem;
    }

    .rr-draft-podium-card__pill small {
        font-size: 0.54rem;
    }

    .rr-draft-podium-card__pedestal {
        left: 10px;
        right: 10px;
        bottom: 10px;
        min-height: 34px;
        padding: 0 10px;
    }

    .rr-draft-podium-card__pedestal-rank {
        min-width: 28px;
        font-size: 0.88rem;
    }

    .rr-draft-ranking-list {
        grid-template-columns: 1fr;
        max-height: none;
    }

    .rr-draft-ranking-row {
        grid-template-columns: 34px 22px minmax(0, 1fr) auto;
    }
}

@media (max-width: 520px) {
    .rr-draft-tabs {
        gap: 6px;
    }

    .rr-draft-tab {
        min-height: 40px;
        padding: 8px 10px;
        font-size: 0.72rem;
    }

    .rr-draft-header__eyebrow {
        min-height: 32px;
        margin-bottom: 0;
    }

    .rr-draft-prize-card {
        padding: 10px 12px;
    }

    .rr-draft-prize-card__value {
        font-size: 1.84rem;
    }

    .rr-draft-meta-grid,
    .rr-draft-toolbar {
        grid-template-columns: 1fr 1fr;
    }

    .rr-draft-meta-card {
        padding: 9px 10px;
    }

    .rr-draft-pay-btn {
        grid-column: 1 / -1;
    }

    .rr-draft-ranking-toolbar__copy,
    .rr-draft-ranking-chips {
        gap: 8px;
    }

    .rr-draft-ranking-title {
        font-size: 0.98rem;
    }

    .rr-draft-ranking-meta {
        font-size: 0.78rem;
    }

    .rr-draft-ranking-visual {
        display: none;
    }

    .rr-draft-podium {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .rr-draft-podium-card {
        min-height: 164px;
        padding: 10px 8px calc(18px + var(--rr-draft-bar-height, 16px));
        border-radius: 18px;
        gap: 8px;
    }

    .rr-draft-podium-card::before {
        border-radius: 17px;
    }

    .rr-draft-podium-card__topline {
        justify-content: center;
    }

    .rr-draft-podium-card__badge {
        min-height: 28px;
        padding: 0 0.6rem;
        font-size: 0.52rem;
        letter-spacing: 0.1em;
    }

    .rr-draft-podium-card__medal {
        width: 30px;
        height: 30px;
        font-size: 0.82rem;
    }

    .rr-draft-podium-card__avatar-wrap {
        margin-top: 0;
        padding: 6px;
        border-radius: 999px;
    }

    .rr-draft-avatar--podium,
    .rr-draft-podium-card--champion .rr-draft-avatar--podium {
        width: 40px;
        height: 40px;
    }

    .rr-draft-podium-card__pos {
        font-size: 0.52rem;
        letter-spacing: 0.16em;
    }

    .rr-draft-podium-card__name {
        min-height: 28px;
        font-size: 0.68rem;
        text-align: center;
        -webkit-line-clamp: 2;
    }

    .rr-draft-podium-card__name-line {
        justify-content: center;
        gap: 5px;
        margin-top: -4px;
    }

    .rr-draft-podium-card__scene {
        min-height: 52px;
    }

    .rr-draft-podium-card__stats {
        gap: 6px;
    }

    .rr-draft-podium-card__pill {
        grid-template-columns: 1fr;
        justify-items: center;
        min-height: 34px;
        padding: 6px 5px;
        border-radius: 12px;
        gap: 3px;
    }

    .rr-draft-podium-card__pill i,
    .rr-draft-podium-card__pill small {
        display: none;
    }

    .rr-draft-podium-card__pill strong {
        font-size: 0.64rem;
        text-align: center;
        white-space: normal;
        line-height: 1.1;
    }

    .rr-draft-podium-card__premium-crown {
        width: 20px;
        height: 20px;
        font-size: 0.58rem;
    }

    .rr-draft-podium-card__pedestal {
        left: 8px;
        right: 8px;
        bottom: 8px;
        min-height: 26px;
        padding: 0 8px;
        border-radius: 12px;
    }

    .rr-draft-podium-card__pedestal-rank {
        min-width: 20px;
        font-size: 0.7rem;
    }

    .rr-draft-podium-card__pedestal-fill {
        height: 6px;
    }

    .rr-draft-podium-card__bar {
        left: 12px;
        right: 12px;
        bottom: 16px;
        height: 4px;
    }

    .rr-draft-ranking-row {
        grid-template-columns: 40px 26px minmax(0, 1fr) auto;
        gap: 8px;
        padding: 9px 10px;
    }

    .rr-draft-ranking-row__pos {
        min-height: 30px;
        padding: 0 0.44rem;
        font-size: 0.74rem;
    }

    .rr-draft-ranking-row__name {
        font-size: 0.8rem;
    }

    .rr-draft-ranking-row__prize {
        font-size: 0.72rem;
    }

    .rr-draft-ranking-row__score {
        min-height: 26px;
        padding: 0 0.52rem;
        font-size: 0.62rem;
    }

    .rr-draft-builder__head {
        gap: 4px;
    }

    .rr-draft-pool-title,
    .rr-draft-selection-status {
        font-size: 0.88rem;
    }

    .rr-draft-team-slots {
        gap: 5px;
    }

    .rr-draft-slot {
        min-height: 80px;
        padding: 9px 3px 7px;
    }

    .rr-draft-slot__photo {
        width: 34px;
        height: 34px;
    }

    .rr-draft-slot__empty {
        max-width: 40px;
        font-size: 0.56rem;
    }

    .rr-draft-competitor-card {
        min-height: 100px;
        padding: 9px 6px 7px;
    }

    .rr-draft-competitor__photo {
        opacity: 0.3;
    }

    .rr-draft-competitor__name {
        min-height: 24px;
        font-size: 0.64rem;
    }
}

body.light .rr-draft-modal {
    --rr-draft-light-shell: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 244, 245, 0.98));
    --rr-draft-light-panel: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.92));
    --rr-draft-light-panel-strong: linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(241, 245, 249, 0.94));
    --rr-draft-light-line: rgba(15, 23, 42, 0.1);
    --rr-draft-light-line-strong: rgba(234, 88, 12, 0.18);
    --rr-draft-light-text: #0f172a;
    --rr-draft-light-text-soft: #334155;
    --rr-draft-light-muted: #64748b;
    --rr-draft-light-warm: rgba(234, 88, 12, 0.12);
    --rr-draft-light-warm-strong: rgba(234, 88, 12, 0.2);
    --rr-draft-light-cool: rgba(37, 99, 235, 0.12);
    background: rgba(15, 23, 42, 0.26);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}

body.light .rr-draft-container {
    border-color: rgba(15, 23, 42, 0.08);
    background:
        radial-gradient(circle at top right, rgba(234, 88, 12, 0.12), transparent 34%),
        radial-gradient(circle at left center, rgba(37, 99, 235, 0.08), transparent 30%),
        var(--rr-draft-light-shell);
    box-shadow:
        0 30px 80px rgba(15, 23, 42, 0.14),
        0 0 0 1px rgba(255, 255, 255, 0.72) inset;
    color: var(--rr-draft-light-text);
}

body.light .rr-draft-container::before {
    background:
        linear-gradient(120deg, rgba(255, 255, 255, 0.9), transparent 24%),
        linear-gradient(180deg, rgba(234, 88, 12, 0.05), transparent 44%);
}

body.light .rr-draft-header__eyebrow,
body.light .rr-draft-header__capacity,
body.light .rr-draft-prize-card__status,
body.light .rr-draft-pool-kicker,
body.light .rr-draft-prize-card__badge,
body.light .rr-draft-prize-card__floater,
body.light .rr-draft-ranking-toolbar__eyebrow,
body.light .rr-draft-ranking-chip,
body.light .rr-draft-ranking-visual__badge,
body.light .rr-draft-podium-card__badge,
body.light .rr-draft-ranking-empty__badge {
    border-color: var(--rr-draft-light-line-strong);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 247, 237, 0.84));
    color: var(--rr-draft-light-text-soft);
    box-shadow: 0 14px 26px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-header__eyebrow i,
body.light .rr-draft-header__capacity i,
body.light .rr-draft-prize-card__status i,
body.light .rr-draft-prize-card__badge i,
body.light .rr-draft-prize-card__floater i,
body.light .rr-draft-ranking-toolbar__eyebrow i,
body.light .rr-draft-ranking-chip i,
body.light .rr-draft-ranking-visual__badge i,
body.light .rr-draft-podium-card__badge i,
body.light .rr-draft-ranking-empty__badge i {
    color: #d97706;
}

body.light .rr-draft-header__close {
    border-color: rgba(248, 113, 113, 0.44);
    background:
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.28), transparent 42%),
        linear-gradient(180deg, #ff5a5f 0%, #ef4444 55%, #b91c1c 100%);
    color: #fff7f7;
    box-shadow:
        0 16px 28px rgba(185, 28, 28, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

body.light .rr-draft-tab {
    border-color: rgba(15, 23, 42, 0.08);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.94));
    color: var(--rr-draft-light-text-soft);
    box-shadow: 0 14px 26px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-tab[data-tab="team"] {
    border-color: rgba(34, 197, 94, 0.28);
    background: linear-gradient(135deg, #22c55e, #16a34a 55%, #166534 100%);
    color: #ffffff;
    box-shadow: 0 16px 28px rgba(22, 163, 74, 0.16);
}

body.light .rr-draft-tab[data-tab="ranking"] {
    border-color: rgba(249, 115, 22, 0.28);
    background: linear-gradient(135deg, #f59e0be6, #f97316 55%, #c2410c 100%);
    color: #ffffff;
    box-shadow: 0 16px 28px rgba(234, 88, 12, 0.16);
}

body.light .rr-draft-tab:hover {
    color: var(--rr-draft-light-text);
    box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
}

body.light .rr-draft-tab.active {
    border-width: 2px;
    border-color: rgba(249, 115, 22, 0.9);
    background: linear-gradient(135deg, #111827, #0f172a 55%, #1e293b 100%);
    color: #ffffff;
    box-shadow:
        0 18px 30px rgba(15, 23, 42, 0.18),
        0 0 0 2px rgba(249, 115, 22, 0.14);
}

body.light .rr-draft-tab[data-tab="team"].active {
    border-color: rgba(249, 115, 22, 0.9);
    background: linear-gradient(135deg, #22c55e, #16a34a 55%, #166534 100%);
    box-shadow:
        0 18px 30px rgba(22, 163, 74, 0.22),
        0 0 0 2px rgba(249, 115, 22, 0.14);
    color: #ffffff;
}

body.light .rr-draft-tab[data-tab="ranking"].active {
    border-color: rgba(249, 115, 22, 0.9);
    background: linear-gradient(135deg, #f59e0be6, #f97316 55%, #c2410c 100%);
    box-shadow:
        0 18px 30px rgba(234, 88, 12, 0.2),
        0 0 0 2px rgba(249, 115, 22, 0.14);
    color: #ffffff;
}

body.light .rr-draft-tab i {
    color: #d97706;
}

body.light .rr-draft-tab.active i {
    color: #ffffff;
}

body.light .rr-draft-prize-card,
body.light .rr-draft-meta-card,
body.light .rr-draft-toolbar__slots,
body.light .rr-draft-toolbar__item,
body.light .rr-draft-main,
body.light .rr-draft-ranking-shell,
body.light .rr-draft-builder__head,
body.light .rr-draft-empty-state {
    border-color: var(--rr-draft-light-line);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.82),
        0 18px 36px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-prize-card {
    background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.18), transparent 34%),
        radial-gradient(circle at right center, rgba(37, 99, 235, 0.14), transparent 28%),
        linear-gradient(140deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94) 68%);
}

body.light .rr-draft-prize-card__label,
body.light .rr-draft-meta-card__label,
body.light .rr-draft-budget__label,
body.light .rr-draft-pool-kicker {
    color: #9a3412;
}

body.light .rr-draft-prize-card__title,
body.light .rr-draft-prize-card__value,
body.light .rr-draft-meta-card__value,
body.light .rr-draft-budget__value,
body.light .rr-draft-pool-title,
body.light .rr-draft-selection-status,
body.light .rr-draft-slot__name,
body.light .rr-draft-competitor__name,
body.light .rr-draft-ranking-title,
body.light .rr-draft-ranking-row__name,
body.light .rr-draft-podium-card__pos,
body.light .rr-draft-podium-card__name,
body.light .rr-draft-podium-card__points,
body.light .rr-draft-ranking-empty__title {
    color: var(--rr-draft-light-text);
}

body.light .rr-draft-prize-card__value {
    text-shadow: 0 14px 30px rgba(234, 88, 12, 0.12);
}

body.light .rr-draft-prize-card__note {
    color: #9a3412;
}

body.light .rr-draft-prize-card__note[data-tone="error"],
body.light .rr-draft-action-note[data-tone="error"] {
    color: #b91c1c;
}

body.light .rr-draft-prize-card__note[data-tone="success"],
body.light .rr-draft-action-note[data-tone="success"] {
    color: #15803d;
}

body.light .rr-draft-prize-card__note[data-tone="warn"],
body.light .rr-draft-action-note[data-tone="warn"] {
    color: #b45309;
}

body.light .rr-draft-prize-card__subnote,
body.light .rr-draft-action-note,
body.light .rr-draft-pool-copy,
body.light .rr-draft-ranking-meta,
body.light .rr-draft-slot__empty,
body.light .rr-draft-ranking-empty__text,
body.light .rr-draft-empty-state,
body.light .rr-draft-podium-card__score,
body.light .rr-draft-ranking-row__prize--out,
body.light .rr-draft-ranking-row__name--placeholder,
body.light .rr-draft-podium-card__name--placeholder {
    color: var(--rr-draft-light-muted);
}

body.light .rr-draft-ranking-row__pos {
    min-height: auto;
    padding: 0;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
    color: #64748b;
    font-weight: 900;
    font-size: 0.92rem;
    letter-spacing: -0.04em;
}

body.light .rr-draft-meta-card,
body.light .rr-draft-builder__head,
body.light .rr-draft-main,
body.light .rr-draft-ranking-shell,
body.light .rr-draft-empty-state {
    background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.08), transparent 34%),
        radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.06), transparent 28%),
        var(--rr-draft-light-panel-strong);
}

body.light .rr-draft-slot {
    border-color: rgba(15, 23, 42, 0.12);
    background: linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.92));
}

body.light .rr-draft-slot--filled {
    border-color: rgba(22, 163, 74, 0.26);
    background:
        radial-gradient(circle at top left, rgba(22, 163, 74, 0.12), transparent 34%),
        linear-gradient(160deg, rgba(240, 253, 244, 0.96), rgba(255, 255, 255, 0.96));
}

body.light .rr-draft-slot__index {
    background: rgba(234, 88, 12, 0.12);
    color: #9a3412;
}

body.light .rr-draft-slot__remove {
    background: rgba(15, 23, 42, 0.08);
    color: var(--rr-draft-light-text);
}

body.light .rr-draft-slot__photo,
body.light .rr-draft-avatar {
    background: rgba(255, 255, 255, 0.98);
    border-color: rgba(15, 23, 42, 0.08);
    box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-avatar {
    color: var(--rr-draft-light-text-soft);
}

body.light .rr-draft-competitor-card {
    border-color: rgba(15, 23, 42, 0.1);
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.06), transparent 34%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94));
    box-shadow: 0 14px 26px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-competitor-card:hover {
    border-color: rgba(234, 88, 12, 0.22);
}

body.light .rr-draft-competitor-card::before {
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.18) 0%, rgba(241, 245, 249, 0.62) 48%, rgba(248, 250, 252, 0.92) 100%),
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 34%);
}

body.light .rr-draft-competitor-card.selected {
    border-color: rgba(22, 163, 74, 0.32);
    background:
        radial-gradient(circle at top left, rgba(22, 163, 74, 0.12), transparent 36%),
        radial-gradient(circle at right center, rgba(234, 88, 12, 0.08), transparent 30%),
        linear-gradient(160deg, rgba(240, 253, 244, 0.98), rgba(255, 247, 237, 0.96));
}

body.light .rr-draft-competitor-card.selected::after {
    background: rgba(22, 163, 74, 0.12);
    color: #166534;
}

body.light .rr-draft-toolbar {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0.96));
    box-shadow: 0 -16px 32px rgba(15, 23, 42, 0.12);
    backdrop-filter: blur(16px);
}

body.light .rr-draft-toolbar__item {
    background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.06), transparent 34%),
        var(--rr-draft-light-panel);
}

body.light .rr-draft-toolbar__slots {
    background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.06), transparent 34%),
        var(--rr-draft-light-panel);
}

body.light .rr-draft-pay-btn,
body.light .rr-draft-refresh-btn {
    border: 1px solid rgba(124, 45, 18, 0.18);
    background: linear-gradient(135deg, #f59e0b, #f97316 55%, #c2410c 100%);
    color: #ffffff;
    box-shadow: 0 18px 34px rgba(234, 88, 12, 0.22);
}

body.light .rr-draft-pay-btn:hover:not(:disabled),
body.light .rr-draft-refresh-btn:hover:not(:disabled) {
    box-shadow: 0 22px 38px rgba(234, 88, 12, 0.26);
}

body.light .rr-draft-pay-btn.is-disabled,
body.light .rr-draft-pay-btn:disabled,
body.light .rr-draft-refresh-btn:disabled {
    border-color: rgba(194, 65, 12, 0.2);
    background: linear-gradient(135deg, rgba(251, 146, 60, 0.82), rgba(234, 88, 12, 0.7));
    color: rgba(255, 255, 255, 0.92);
    box-shadow: 0 14px 24px rgba(234, 88, 12, 0.18);
}

body.light .rr-draft-pay-btn--locked {
    border-color: rgba(194, 65, 12, 0.18);
    background: linear-gradient(135deg, rgba(251, 146, 60, 0.9), rgba(234, 88, 12, 0.78));
    color: #ffffff;
    box-shadow: 0 16px 28px rgba(234, 88, 12, 0.2);
}

body.light .rr-draft-pay-btn__icon {
    background: rgba(255, 255, 255, 0.18);
}

body.light .rr-draft-pay-btn__sub {
    color: rgba(255, 247, 237, 0.82);
}

body.light .rr-draft-pay-btn--locked .rr-draft-pay-btn__icon {
    background: rgba(255, 255, 255, 0.2);
    color: #fff7ed;
}

body.light .rr-draft-pay-btn--locked .rr-draft-pay-btn__sub {
    color: rgba(255, 247, 237, 0.82);
}

body.light .rr-draft-pay-btn:not(.is-disabled):not(:disabled):not(.rr-draft-pay-btn--locked) {
    border-color: rgba(22, 163, 74, 0.22);
    background: linear-gradient(135deg, #22c55e, #16a34a 55%, #166534 100%);
    color: #f0fdf4;
    box-shadow: 0 18px 34px rgba(22, 163, 74, 0.28);
}

body.light .rr-draft-pay-btn:not(.is-disabled):not(:disabled):not(.rr-draft-pay-btn--locked):hover {
    box-shadow: 0 22px 38px rgba(22, 163, 74, 0.34);
}

body.light .rr-draft-ranking-shell::before {
    background: radial-gradient(circle, rgba(234, 88, 12, 0.12), rgba(234, 88, 12, 0));
}

body.light .rr-draft-ranking-shell::after {
    background: radial-gradient(circle, rgba(37, 99, 235, 0.1), rgba(37, 99, 235, 0));
}

body.light .rr-draft-ranking-toolbar {
    border-color: rgba(15, 23, 42, 0.08);
    background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.12), transparent 34%),
        radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.1), transparent 30%),
        linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.88),
        0 18px 32px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-prize-card__logo-wrap::before,
body.light .rr-draft-ranking-visual::before {
    background: radial-gradient(circle at 50% 50%, rgba(234, 88, 12, 0.18), rgba(37, 99, 235, 0.08) 48%, rgba(37, 99, 235, 0) 74%);
}

body.light .rr-draft-prize-card__logo-wrap::after,
body.light .rr-draft-ranking-visual::after {
    border-color: rgba(15, 23, 42, 0.08);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.74);
}

body.light .rr-draft-prize-card__logo-mark,
body.light .rr-draft-ranking-visual__core {
    border-color: rgba(15, 23, 42, 0.08);
    background: linear-gradient(160deg, #111827, #1f2937);
    color: #f59e0be6;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 20px 34px rgba(15, 23, 42, 0.16);
}

body.light .rr-draft-podium-card {
    border-color: rgba(15, 23, 42, 0.1);
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 42%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(241, 245, 249, 0.94) 72%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 18px 32px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-podium-card::before {
    border-color: rgba(15, 23, 42, 0.06);
}

body.light .rr-draft-podium-card::after {
    background: radial-gradient(circle, rgba(255, 255, 255, 0.66), rgba(255, 255, 255, 0));
}

body.light .rr-draft-podium::before,
body.light .rr-draft-podium-card__scene::before {
    background: radial-gradient(circle, rgba(251, 191, 36, 0.18), rgba(255, 255, 255, 0));
}

body.light .rr-draft-podium-card__medal,
body.light .rr-draft-podium-card__avatar-wrap {
    border-color: rgba(15, 23, 42, 0.08);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-podium-card__pill {
    border-color: rgba(15, 23, 42, 0.08);
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94)),
        rgba(255, 255, 255, 0.82);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 12px 20px rgba(15, 23, 42, 0.06);
}

body.light .rr-draft-podium-card__pill strong,
body.light .rr-draft-podium-card__pedestal-rank {
    color: #0f172a;
}

body.light .rr-draft-podium-card__pill small {
    color: #64748b;
}

body.light .rr-draft-podium-card__pill i {
    box-shadow: none;
}

body.light .rr-draft-podium-card__pedestal {
    border-color: rgba(15, 23, 42, 0.08);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(241, 245, 249, 0.92));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.88),
        0 14px 20px rgba(15, 23, 42, 0.08);
}

body.light .rr-draft-podium-card__pedestal-fill {
    background: rgba(148, 163, 184, 0.16);
}

body.light .rr-draft-podium-card--premium {
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 18px 28px rgba(15, 23, 42, 0.08),
        0 0 0 1px rgba(217, 119, 6, 0.16),
        0 0 28px rgba(245, 158, 11, 0.12);
}

body.light .rr-draft-podium-card--premium::before {
    border-color: rgba(217, 119, 6, 0.16);
}

body.light .rr-draft-podium-card--premium .rr-draft-podium-card__avatar-wrap {
    border-color: rgba(217, 119, 6, 0.18);
    background: linear-gradient(180deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.94));
    box-shadow:
        0 14px 24px rgba(15, 23, 42, 0.1),
        0 0 0 1px rgba(217, 119, 6, 0.08);
}

body.light .rr-draft-podium-card__premium-crown {
    color: #b45309;
    background: linear-gradient(180deg, rgba(255, 247, 237, 0.98), rgba(255, 255, 255, 0.94));
    border-color: rgba(217, 119, 6, 0.18);
    box-shadow: 0 10px 18px rgba(217, 119, 6, 0.12);
}

body.light .rr-draft-podium-card--premium .rr-draft-podium-card__name {
    color: #0f172a;
    text-shadow: none;
}

body.light .rr-draft-podium-card--gold {
    border-color: rgba(217, 119, 6, 0.22);
    background:
        radial-gradient(circle at top left, rgba(251, 191, 36, 0.16), transparent 42%),
        linear-gradient(160deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.96) 76%);
}

body.light .rr-draft-podium-card--silver {
    border-color: rgba(148, 163, 184, 0.2);
    background:
        radial-gradient(circle at top left, rgba(203, 213, 225, 0.16), transparent 40%),
        linear-gradient(160deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.96) 76%);
}

body.light .rr-draft-podium-card--bronze {
    border-color: rgba(234, 88, 12, 0.2);
    background:
        radial-gradient(circle at top left, rgba(251, 146, 60, 0.14), transparent 42%),
        linear-gradient(160deg, rgba(255, 247, 237, 0.98), rgba(255, 255, 255, 0.96) 76%);
}

body.light .rr-draft-podium-card--champion {
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 26px 46px rgba(217, 119, 6, 0.16),
        0 0 0 1px rgba(217, 119, 6, 0.14),
        0 0 34px rgba(251, 191, 36, 0.14);
}

body.light .rr-draft-podium-card--mine {
    border-color: rgba(22, 163, 74, 0.3);
    box-shadow:
        0 20px 34px rgba(148, 163, 184, 0.16),
        0 0 0 1px rgba(22, 163, 74, 0.12),
        0 0 32px rgba(22, 163, 74, 0.12);
}

body.light .rr-draft-podium-card--mine .rr-draft-podium-card__badge {
    background: linear-gradient(135deg, rgba(220, 252, 231, 0.98), rgba(219, 234, 254, 0.92));
    border-color: rgba(22, 163, 74, 0.18);
    color: #166534;
}

body.light .rr-draft-ranking-list {
    border-color: rgba(15, 23, 42, 0.08);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(248, 250, 252, 0.96));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 14px 28px rgba(15, 23, 42, 0.06);
}

body.light .rr-draft-ranking-row {
    border-color: rgba(15, 23, 42, 0.08);
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.06), transparent 34%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94));
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
}

body.light .rr-draft-ranking-row:nth-child(odd) {
    background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.06), transparent 34%),
        linear-gradient(180deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.94));
}

body.light .rr-draft-ranking-row--mine {
    border-color: rgba(249, 115, 22, 0.24);
    background:
        radial-gradient(circle at top left, rgba(251, 191, 36, 0.16), transparent 34%),
        radial-gradient(circle at right center, rgba(59, 130, 246, 0.08), transparent 30%),
        linear-gradient(180deg, rgba(255, 251, 235, 0.98), rgba(255, 255, 255, 0.96));
    box-shadow:
        0 14px 26px rgba(148, 163, 184, 0.12),
        0 0 0 1px rgba(249, 115, 22, 0.08),
        0 0 24px rgba(249, 115, 22, 0.08);
}

body.light .rr-draft-ranking-row--mine .rr-draft-ranking-row__pos {
    color: #c2410c;
}

body.light .rr-draft-ranking-row--mine .rr-draft-ranking-row__name {
    color: #0f172a;
}

body.light .rr-draft-ranking-row__prize {
    color: #1d4ed8;
}

body.light .rr-draft-ranking-row__score {
    border-color: rgba(15, 23, 42, 0.08);
    background: linear-gradient(135deg, #111827, #1f2937);
    color: #ffffff;
    box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
}

body.light .rr-draft-ranking-row--mine .rr-draft-ranking-row__score {
    border-color: rgba(249, 115, 22, 0.16);
    background: linear-gradient(180deg, rgba(255, 247, 237, 0.98), rgba(255, 237, 213, 0.96));
    color: #9a3412;
}

body.light .rr-draft-ranking-nav {
    border-color: rgba(249, 115, 22, 0.2);
    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(239,246,255,0.96));
    color: #0f172a;
    box-shadow:
        0 14px 24px rgba(148, 163, 184, 0.12),
        0 0 0 1px rgba(249, 115, 22, 0.08);
}

body.light .rr-draft-ranking-nav i {
    color: #ea580c;
}

body.light .rr-draft-ranking-nav.is-top i {
    color: #2563eb;
}

body.light .rr-draft-ranking-empty {
    border-color: rgba(15, 23, 42, 0.12);
    background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.06), transparent 36%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94));
    color: var(--rr-draft-light-text-soft);
}
</style>

<!-- Modal Draft Bolão -->
<div class="rr-draft-modal" id="rrDraftModal" hidden>
    <div class="rr-draft-container">
        <div class="rr-draft-header">
            <div class="rr-draft-header__capacity" id="rrDraftCapacityBadge">
                <i class="fas fa-users"></i>
                <span>Equipes</span>
                <strong id="rrDraftCapacityValue">0/0</strong>
            </div>
            <div class="rr-draft-header__actions">
                <button type="button" class="rr-draft-refresh-btn rr-draft-refresh-btn--header" id="rrDraftHeaderRefresh" aria-label="Atualizar ranking">
                    <i class="fas fa-rotate-right"></i>
                    <span>Atualizar</span>
                </button>
                <button class="rr-draft-header__close" id="rrDraftClose">×</button>
            </div>
        </div>

        <div class="rr-draft-workspace">
            <div class="rr-draft-stage">
                <div class="rr-draft-content">
                    <div class="rr-draft-panel active" data-panel="team">
                        <div class="rr-draft-builder">
                            <div class="rr-draft-team-slots rr-draft-team-slots--desktop" id="rrDraftTeamList"></div>

                            <div class="rr-draft-toolbar">
                                <div class="rr-draft-toolbar__slots rr-draft-toolbar__slots--mobile">
                                    <div class="rr-draft-team-slots rr-draft-team-slots--compact" id="rrDraftTeamListMobile"></div>
                                </div>
                                <div class="rr-draft-toolbar__item rr-draft-toolbar__item--entry">
                                    <span class="rr-draft-budget__label">Entrada</span>
                                    <strong class="rr-draft-budget__value" id="rrDraftToolbarEntry">--</strong>
                                </div>
                                <button class="rr-draft-pay-btn" id="rrDraftPayBtn" type="button">
                                    Entrar no bolão
                                </button>
                            </div>

                            <section class="rr-draft-main">
                                <div class="rr-draft-pool-head">
                                    <div>
                                        <div class="rr-draft-pool-kicker">Lista de competidores</div>
                                        <div class="rr-draft-pool-title">Toque para selecionar</div>
                                    </div>
                                </div>

                                <div class="rr-draft-search">
                                    <i class="fas fa-search rr-draft-search__icon" aria-hidden="true"></i>
                                    <input
                                        type="text"
                                        class="rr-draft-search__input"
                                        id="rrDraftCompetitorSearch"
                                        placeholder="Buscar competidor..."
                                        autocomplete="off"
                                    >
                                </div>

                                <div class="rr-draft-competitors" id="rrDraftCompetitors">
                                    <div class="rr-draft-empty-state">Carregando competidores...</div>
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="rr-draft-panel" data-panel="ranking">
                        <div class="rr-draft-ranking" id="rrDraftRanking"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal PIX - Bolão (mesmo padrão visual do X1) -->
<div class="rr-inicio-pix" id="rrDraftPixModal" hidden>
    <div class="rr-betslip-backdrop" id="rrDraftPixBackdrop"></div>
    <div class="rr-inicio-pix__card">
        <h4 class="rr-inicio-pix__title"><i class="fas fa-qrcode"></i> Pagamento PIX - Bolão</h4>
        <div class="rr-inicio-pix__qr-wrap">
            <img id="rrDraftPixImage" class="rr-inicio-pix__qr" alt="QR Code PIX Bolão">
        </div>
        <textarea id="rrDraftPixCode" class="rr-inicio-pix__code" rows="3" readonly></textarea>
        <div class="rr-inicio-pix__actions">
            <button type="button" class="rr-inicio-pix__btn rr-inicio-pix__btn--copy" id="rrDraftPixCopy">
                <i class="fas fa-copy"></i> Copiar PIX
            </button>
            <button type="button" class="rr-inicio-pix__btn rr-inicio-pix__btn--check" id="rrDraftPixCheck">
                <i class="fas fa-sync-alt"></i> Verificar
            </button>
        </div>
        <div class="rr-inicio-pix__status" id="rrDraftPixStatus">Aguardando confirmação...</div>
        <button type="button" class="rr-betslip__btn rr-betslip__btn--cancel" id="rrDraftPixClose">Fechar</button>
    </div>
</div>

<div class="rr-pix-loader" id="rrPixGenerationOverlay" hidden aria-live="polite" aria-busy="true" aria-label="Carregando PIX">
    <div class="rr-pix-loader__card">
        <svg class="rr-pix-loader__svg" viewBox="0 0 800 600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <g class="dots">
                <circle class="mainDot" cx="300" cy="300" r="12.5" />
                <g class="otherDots">
                    <circle cx="340" cy="300" r="12.5" />
                    <circle cx="380" cy="300" r="12.5" />
                    <circle cx="420" cy="300" r="12.5" />
                    <circle cx="460" cy="300" r="12.5" />
                    <circle cx="500" cy="300" r="12.5" />
                </g>
            </g>
        </svg>
        <div class="rr-pix-loader__title" id="rrPixGenerationTitle">Carregando PIX</div>
        <div class="rr-pix-loader__copy" id="rrPixGenerationCopy">Gerando o QR code para abrir em seguida.</div>
    </div>
</div>

<div class="rr-draft-success-overlay" id="rrDraftSuccessModal" hidden aria-hidden="true">
    <div class="rr-betslip-backdrop" id="rrDraftSuccessBackdrop"></div>
    <div class="rr-draft-success-popout" role="dialog" aria-modal="true" aria-label="Equipe criada com sucesso">
        <button type="button" class="rr-draft-success-popout__close" id="rrDraftSuccessClose" aria-label="Fechar">×</button>
        <div class="rr-draft-success-popout__hero">
            <span class="rr-draft-success-popout__badge" id="rrDraftSuccessBadge"><i class="fas fa-circle-check"></i> Equipe confirmada</span>
            <div class="rr-draft-success-popout__orb" aria-hidden="true">
                <i class="fas fa-check"></i>
            </div>
            <h4 class="rr-draft-success-popout__title" id="rrDraftSuccessTitle">Equipe criada com sucesso</h4>
            <p class="rr-draft-success-popout__text" id="rrDraftSuccessText">Sua equipe já está no bolão. Agora é só acompanhar o ranking e torcer.</p>
        </div>
        <div class="rr-draft-success-popout__meta">
            <div class="rr-draft-success-popout__meta-item">
                <span class="rr-draft-success-popout__meta-label">Bolão</span>
                <strong class="rr-draft-success-popout__meta-value" id="rrDraftSuccessLeague">Bolão na Arena</strong>
            </div>
            <div class="rr-draft-success-popout__meta-item">
                <span class="rr-draft-success-popout__meta-label">Confirmação</span>
                <strong class="rr-draft-success-popout__meta-value" id="rrDraftSuccessMethod">Confirmada</strong>
            </div>
        </div>
        <div class="rr-draft-success-popout__footer">
            <button type="button" class="rr-draft-success-popout__action" id="rrDraftSuccessCloseBtn">
                <span>Fechar</span>
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('rrInicioSection');
    if (!root) return;
    // Rebind sempre que o partial for reinjetado via AJAX (evita botões "mortos").
    // Mantemos o marker apenas para debug.
    root.dataset.bound = '1';

    const isPremium = root.dataset.isPremium === '1';
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    const entryMode = root.dataset.entityMode || 'competitor';
    const hasMultipleModalidades = root.dataset.hasMultipleModalidades === '1';
    const entryLabelSingular = @json($entryLabelSingular);
    const entryLabelPlural = @json($entryLabelPlural);
    const entryLabelCapitalized = @json($entryLabelCapitalized);
    
    // Elements - Betslip
    const slip = document.getElementById('rrInicioSlip');
    const slipTitle = document.getElementById('rrInicioSlipCompetitor');
    const slipHeroChip = document.getElementById('rrBetslipHeroChip');
    const slipSubtitle = document.getElementById('rrBetslipSubtitle');
    const amountSection = document.getElementById('rrInicioSlipAmountSection');
    const matchesSection = document.getElementById('rrInicioSlipMatchesSection');
    const matchesList = document.getElementById('rrInicioSlipMatchesList');
    const loadingEl = document.getElementById('rrInicioSlipLoading');
    const stakeInput = document.getElementById('rrBetslipStakeInput');
    const oddsDisplay = document.getElementById('rrBetslipOdds');
    const returnDisplay = document.getElementById('rrBetslipReturn');
    const returnFooterDisplay = document.getElementById('rrBetslipReturnFooter');
    const placeBtn = document.getElementById('rrBetslipPlaceBtn');
    const placeBtnText = placeBtn?.querySelector('.rr-betslip__place-text');
    const placeBtnProcessing = placeBtn?.querySelector('.rr-betslip__place-processing');
    const placeBtnProcessingText = document.getElementById('rrBetslipProcessingText');
    const stakePreviewEl = document.getElementById('rrBetslipStakePreview');
    const statusKickerEl = document.getElementById('rrBetslipStatusKicker');
    const statusTitleEl = document.getElementById('rrBetslipStatusTitle');
    const statusTextEl = document.getElementById('rrBetslipStatusText');
    const actionHintEl = document.getElementById('rrBetslipActionHint');
    const backdrop = document.getElementById('rrBetslipBackdrop');
    
    // Elements - Custom Modal & PIX
    const customModal = document.getElementById('rrInicioCustomModal');
    const customMatches = document.getElementById('rrInicioCustomMatches');
    const customInput = document.getElementById('rrInicioCustomInput');
    const customNameEl = document.getElementById('rrInicioCustomName');
    const customReturnEl = document.getElementById('rrInicioCustomReturn');
    const competitorSearchModal = document.getElementById('rrCompetitorSearchModal');
    const competitorSearchOpen = document.getElementById('rrCompetitorSearchOpen');
    const competitorSearchClose = document.getElementById('rrCompetitorSearchClose');
    const competitorSearchInput = document.getElementById('rrCompetitorSearchInput');
    const competitorSearchResults = document.getElementById('rrCompetitorSearchResults');
    const groupMembersModal = document.getElementById('rrGroupMembersModal');
    const groupMembersClose = document.getElementById('rrGroupMembersClose');
    const groupMembersResults = document.getElementById('rrGroupMembersResults');
    const groupMembersTitle = document.getElementById('rrGroupMembersTitle');
    const inicioSubmenu = document.getElementById('rrInicioSubmenu');
    const inicioRodeioFilter = document.getElementById('rrInicioRodeioFilter');
    const inicioModalidadeFilter = document.getElementById('rrInicioModalidadeFilter');
    const bolaoModalidadePickerWrap = document.getElementById('rrInicioBolaoModalidadePickerWrap');
    const bolaoModalidadePickerBtn = document.getElementById('rrInicioBolaoModalidadePickerBtn');
    const bolaoModalidadePickerLabel = document.getElementById('rrInicioBolaoModalidadePickerLabel');
    const bolaoModalidadePickerMenu = document.getElementById('rrInicioBolaoModalidadePickerMenu');
    const inicioFilterEmpty = document.getElementById('rrInicioFilterEmpty');
    const mobileQuickNav = document.getElementById('rrMobileQuickNav');
    const competitorTools = root.querySelector('.rr-competitor-tools');
    const x1Section = document.getElementById('rrInicioX1Rooms');
    const bolaoSection = document.getElementById('rrInicioBolaos');
    const modal = document.getElementById('rrInicioPixModal');
    const modalImg = document.getElementById('rrInicioPixImage');
    const modalCode = document.getElementById('rrInicioPixCode');
    const modalStatus = document.getElementById('rrInicioPixStatus');
    const isBolaoLaunchLayout = !!root.closest('.rr-inicio-layout--bolao-launch');
    const isIOSDevice = /iPad|iPhone|iPod/.test(window.navigator?.userAgent || '')
        || (/Macintosh/.test(window.navigator?.userAgent || '') && navigator.maxTouchPoints > 1);
    document.documentElement.classList.toggle('rr-ios', !!isIOSDevice);

    // Elements - PIX Bolao
    const draftPixModal = document.getElementById('rrDraftPixModal');
    const draftPixBackdrop = document.getElementById('rrDraftPixBackdrop');
    const draftPixImg = document.getElementById('rrDraftPixImage');
    const draftPixCode = document.getElementById('rrDraftPixCode');
    const draftPixStatus = document.getElementById('rrDraftPixStatus');
    const pixGenerationOverlay = document.getElementById('rrPixGenerationOverlay');
    const pixGenerationTitle = document.getElementById('rrPixGenerationTitle');
    const pixGenerationCopy = document.getElementById('rrPixGenerationCopy');
    const draftSuccessModal = document.getElementById('rrDraftSuccessModal');
    const draftSuccessBackdrop = document.getElementById('rrDraftSuccessBackdrop');
    const draftSuccessTitle = document.getElementById('rrDraftSuccessTitle');
    const draftSuccessText = document.getElementById('rrDraftSuccessText');
    const draftSuccessLeague = document.getElementById('rrDraftSuccessLeague');
    const draftSuccessMethod = document.getElementById('rrDraftSuccessMethod');
    const draftSuccessBadge = document.getElementById('rrDraftSuccessBadge');
    const mobileQuickFilterState = { x1: null, bolao: null };
    const arenaCatalogSections = Array.from(root.querySelectorAll('.rr-inicio-modalidade-group'));
    const bolaoCatalogSections = Array.from(document.querySelectorAll('#rrInicioBolaos .rr-inicio-subcatalog'));
    const x1CatalogSections = Array.from(document.querySelectorAll('#rrInicioX1Rooms .rr-inicio-subcatalog'));
    let activeQuickPanel = null;
    let activeSearchSelection = null;
    let competitorSearchDebounce = null;
    let competitorSearchRequestId = 0;
    const draftSuccessStorageKey = 'rr_draft_success_popout';

    if (window.__rrInicioLiveResizeHandler) {
        window.removeEventListener('resize', window.__rrInicioLiveResizeHandler);
    }
    window.__rrInicioLiveResizeHandler = function () {
        syncMobileQuickNavChrome();
        if (!isMobileQuickViewport()) {
            closeMobileQuickPanels();
        }
    };
    window.addEventListener('resize', window.__rrInicioLiveResizeHandler, { passive: true });

    function isMobileQuickViewport() {
        const ua = String(window.navigator?.userAgent || '');
        if (/Android|iPhone|iPad|iPod|Mobile/i.test(ua)) {
            return true;
        }
        return !!(window.matchMedia && window.matchMedia('(max-width: 767px)').matches);
    }

    function enforceBolaoLaunchLegacyFiltersHidden() {
        const shouldHideLegacyFilters = isBolaoLaunchLayout || !!bolaoModalidadePickerWrap;
        if (!shouldHideLegacyFilters) return;

        const legacySelectors = Array.from(document.querySelectorAll(
            '#rrInicioSubmenu [id="rrInicioRodeioFilter"], #rrInicioSubmenu [id="rrInicioModalidadeFilter"], #rrInicioSubmenu .rr-inicio-submenu__select, .rr-inicio-event-call__mobile-selector-row select, .rr-inicio-event-call__mobile-selector select'
        ));
        const knownLegacy = [inicioRodeioFilter, inicioModalidadeFilter].filter(Boolean);
        const allLegacySelects = Array.from(new Set([].concat(legacySelectors, knownLegacy)));

        allLegacySelects.forEach(function (selectEl) {
            if (!selectEl) return;
            selectEl.style.setProperty('display', 'none', 'important');
            selectEl.style.setProperty('visibility', 'hidden', 'important');
            selectEl.style.setProperty('opacity', '0', 'important');
            selectEl.style.setProperty('pointer-events', 'none', 'important');
            selectEl.setAttribute('hidden', 'hidden');

            const field = selectEl.closest('.rr-inicio-submenu__field');
            if (field) {
                field.style.setProperty('display', 'none', 'important');
                field.style.setProperty('visibility', 'hidden', 'important');
                field.style.setProperty('pointer-events', 'none', 'important');
                field.setAttribute('hidden', 'hidden');
                field.remove();
                return;
            }

            selectEl.remove();
        });
    }

    enforceBolaoLaunchLegacyFiltersHidden();
    if (isBolaoLaunchLayout) {
        window.setTimeout(enforceBolaoLaunchLegacyFiltersHidden, 0);
        window.setTimeout(enforceBolaoLaunchLegacyFiltersHidden, 350);
        window.setTimeout(enforceBolaoLaunchLegacyFiltersHidden, 1200);
    }

    function formatInicioHeroCountdown(iso) {
        const timerRoot = root.querySelector('#rrInicioEventTimer');
        const mode = timerRoot ? String(timerRoot.getAttribute('data-mode') || 'empty') : 'empty';
        if (!iso) return mode === 'live' ? 'Ao vivo' : 'Em breve';
        const target = new Date(iso);
        if (Number.isNaN(target.getTime())) return mode === 'live' ? 'Ao vivo' : 'Em breve';

        const now = new Date();
        const diff = target.getTime() - now.getTime();
        if (diff <= 0) return mode === 'live' ? 'Finalizando' : 'Aguardando';

        const totalMinutes = Math.floor(diff / 60000);
        const days = Math.floor(totalMinutes / 1440);
        const hours = Math.floor((totalMinutes % 1440) / 60);
        const minutes = totalMinutes % 60;

        if (days > 0) return `${days}d ${hours}h ${minutes}m`;
        if (hours > 0) return `${hours}h ${minutes}m`;
        return `${Math.max(minutes, 1)}m`;
    }

    function ensureInicioHeroMobileStyles() {
        const styleId = 'rr-inicio-mobile-hero-inline';
        let styleEl = document.getElementById(styleId);
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }

        styleEl.textContent = `
@media (max-width: 767px) {
    body .rr-inicio-event-call {
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 10px !important;
        margin: 0 10px 14px !important;
        padding: 14px 12px 16px !important;
        min-height: 236px !important;
        max-height: none !important;
        border-radius: 22px !important;
        overflow: hidden !important;
        border: 1px solid rgba(245, 158, 11, 0.34) !important;
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.2), transparent 42%),
            radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.24), transparent 48%),
            linear-gradient(180deg, rgba(18, 24, 38, 0.98) 0%, rgba(10, 13, 24, 0.98) 100%) !important;
        box-shadow:
            0 18px 40px rgba(2, 6, 23, 0.42),
            inset 0 1px 0 rgba(255, 255, 255, 0.04) !important;
    }

    body.light .rr-inicio-event-call {
        border-color: rgba(251, 146, 60, 0.22) !important;
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.1), transparent 42%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.82) 0%, rgba(255, 255, 255, 0.62) 100%) !important;
        box-shadow:
            0 14px 28px rgba(148, 163, 184, 0.14),
            inset 0 1px 0 rgba(255, 255, 255, 0.5) !important;
    }

    body .rr-inicio-event-call__badges {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        width: 100% !important;
    }

    body .rr-inicio-event-call--launch .rr-inicio-event-call__badges {
        display: none !important;
    }

    body .rr-inicio-event-call__badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        padding: 5px 10px !important;
        border-radius: 999px !important;
        font-size: 0.58rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.1em !important;
        text-transform: uppercase !important;
        color: #fff7ed !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
        background: rgba(15, 23, 42, 0.7) !important;
        box-shadow: 0 10px 24px rgba(2, 6, 23, 0.22) !important;
    }

    body .rr-inicio-event-call__badge--live {
        color: #fef3c7 !important;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.88), rgba(217, 119, 6, 0.82)) !important;
        border-color: rgba(253, 224, 71, 0.44) !important;
    }

    body .rr-inicio-event-call__logo-wrap {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        min-height: auto !important;
        padding-top: 4px !important;
    }

    body .rr-inicio-event-call__logo-stack {
        position: relative !important;
        z-index: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    body .rr-inicio-event-call__logo-wrap::before {
        content: "" !important;
        position: absolute !important;
        width: 148px !important;
        height: 148px !important;
        border-radius: 999px !important;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0.02) 58%, transparent 72%) !important;
        filter: blur(3px) !important;
    }

    body.light .rr-inicio-event-call::before {
        background: linear-gradient(130deg, rgba(245, 158, 11, 0.08), transparent 42%, rgba(245, 158, 11, 0.04) 76%, transparent 100%) !important;
    }

    body .rr-inicio-event-call__logo-frame {
        position: relative !important;
        z-index: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 98px !important;
        height: 98px !important;
        min-width: 98px !important;
        min-height: 98px !important;
        padding: 10px !important;
        border-radius: 24px !important;
        overflow: hidden !important;
        border: 1px solid rgba(245, 158, 11, 0.22) !important;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.86) 0%, rgba(12, 18, 30, 0.82) 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 18px 32px rgba(2, 6, 23, 0.28) !important;
    }

    body .rr-inicio-event-call__logo-stack .rr-inicio-event-call__logo-frame {
        width: 90px !important;
        height: 90px !important;
        min-width: 90px !important;
        min-height: 90px !important;
        padding: 8px !important;
    }

    body .rr-inicio-event-call__logo {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        object-position: center !important;
        margin: 0 !important;
    }

    body .rr-inicio-event-call--launch {
        min-height: 0 !important;
        padding: 14px 12px 16px !important;
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.24), transparent 34%),
            radial-gradient(circle at 12% 82%, rgba(59, 130, 246, 0.2), transparent 28%),
            radial-gradient(circle at 88% 28%, rgba(34, 197, 94, 0.16), transparent 24%),
            linear-gradient(180deg, rgba(18, 24, 38, 0.98) 0%, rgba(10, 13, 24, 0.98) 100%) !important;
    }

    body.light .rr-inicio-event-call--launch {
        background:
            radial-gradient(circle at top center, rgba(245, 158, 11, 0.14), transparent 34%),
            radial-gradient(circle at 12% 82%, rgba(59, 130, 246, 0.1), transparent 28%),
            radial-gradient(circle at 88% 28%, rgba(34, 197, 94, 0.08), transparent 24%),
            linear-gradient(180deg, rgba(255, 250, 244, 0.98) 0%, rgba(255, 247, 237, 0.98) 100%) !important;
    }

    body .rr-inicio-event-call__launch-copy {
        display: none !important;
    }

    body .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap {
        min-height: 276px !important;
        padding-top: 56px !important;
    }

    body .rr-inicio-event-call--launch .rr-inicio-event-call__logo-stack {
        gap: 12px !important;
    }

    body .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap::before {
        width: 244px !important;
        height: 244px !important;
        background:
            radial-gradient(circle, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0.08) 38%, rgba(59, 130, 246, 0.04) 62%, transparent 74%) !important;
        filter: blur(8px) !important;
    }

    body .rr-inicio-event-call--launch .rr-inicio-event-call__logo-wrap::after {
        display: none !important;
    }

    body .rr-inicio-event-call--launch .rr-inicio-event-call__logo-frame,
    body .rr-inicio-event-call--launch .rr-inicio-event-call__logo-stack .rr-inicio-event-call__logo-frame {
        width: 104px !important;
        height: 104px !important;
        min-width: 104px !important;
        min-height: 104px !important;
        padding: 9px !important;
        border-radius: 28px !important;
        border: 1px solid rgba(245, 158, 11, 0.22) !important;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.9) 0%, rgba(12, 18, 30, 0.86) 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 18px 32px rgba(2, 6, 23, 0.28) !important;
    }

    body .rr-inicio-event-call--launch .rr-inicio-event-call__launch-floaters {
        display: none !important;
    }

    body .rr-inicio-event-call__mobile-badges {
        position: absolute !important;
        inset: 66px 0 auto 0 !important;
        z-index: 6 !important;
        display: block !important;
        pointer-events: none !important;
        overflow: visible !important;
    }

    body .rr-inicio-event-call__mobile-badge {
        position: absolute !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        min-height: 34px !important;
        padding: 0 12px !important;
        max-width: 42% !important;
        border-radius: 999px !important;
        border: 1px solid rgba(255, 255, 255, 0.16) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08)) !important;
        color: #fff7ed !important;
        font-size: 0.6rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.1em !important;
        text-transform: uppercase !important;
        box-shadow: 0 14px 24px rgba(3, 7, 18, 0.22) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        pointer-events: none !important;
    }

    body .rr-inicio-event-call__mobile-selector-row {
        position: relative !important;
        z-index: 8 !important;
        width: 100% !important;
        display: flex !important;
        justify-content: center !important;
        margin-top: 8px !important;
        pointer-events: auto !important;
    }

    body .rr-inicio-event-call__mobile-selector {
        position: relative !important;
        z-index: 9 !important;
        pointer-events: auto !important;
        width: min(92%, 320px) !important;
        min-width: 210px !important;
    }

    body .rr-inicio-event-call__mobile-selector-trigger {
        width: 100% !important;
        min-height: 36px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 0 12px !important;
        border-radius: 999px !important;
        border: 1px solid rgba(74, 222, 128, 0.34) !important;
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.22), rgba(22, 163, 74, 0.16)) !important;
        color: #ecfdf5 !important;
        font-size: 0.58rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        box-shadow: 0 14px 24px rgba(21, 128, 61, 0.24) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
    }

    body .rr-inicio-event-call__mobile-selector-trigger i {
        color: #bbf7d0 !important;
        font-size: 0.72rem !important;
    }

    body .rr-inicio-event-call__mobile-selector-chevron {
        margin-left: auto !important;
        transition: transform .2s ease !important;
    }

    body .rr-inicio-event-call__mobile-selector.is-open .rr-inicio-event-call__mobile-selector-chevron {
        transform: rotate(180deg) !important;
    }

    body .rr-inicio-event-call__mobile-selector-menu {
        position: fixed !important;
        top: var(--rr-bolao-modalidade-menu-top, 50%) !important;
        left: var(--rr-bolao-modalidade-menu-left, 50%) !important;
        width: var(--rr-bolao-modalidade-menu-width, min(92vw, 320px)) !important;
        z-index: 2147483647 !important;
    }

    body .rr-inicio-event-call__mobile-selector-menu[hidden] {
        display: none !important;
    }

    body .rr-inicio-event-call__mobile-selector-option {
        width: 100% !important;
        min-height: 34px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 8px !important;
        padding: 0 10px !important;
        border-radius: 10px !important;
        border: 1px solid rgba(148, 163, 184, 0.26) !important;
        background: rgba(15, 23, 42, 0.68) !important;
        color: #f8fafc !important;
        font-size: 0.62rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
    }

    body .rr-inicio-event-call__mobile-selector-option.is-active {
        border-color: rgba(74, 222, 128, 0.42) !important;
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.28), rgba(21, 128, 61, 0.22)) !important;
        color: #ecfdf5 !important;
    }

    body.light .rr-inicio-event-call__mobile-badge {
        color: #7c2d12 !important;
        border-color: rgba(194, 65, 12, 0.16) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 247, 237, 0.88)) !important;
        box-shadow: 0 14px 24px rgba(148, 163, 184, 0.18) !important;
    }

    body.light .rr-inicio-event-call__mobile-selector-trigger {
        border-color: rgba(34, 197, 94, 0.22) !important;
        background: linear-gradient(180deg, rgba(240, 253, 244, 0.96), rgba(220, 252, 231, 0.92)) !important;
        color: #166534 !important;
        box-shadow: 0 14px 24px rgba(34, 197, 94, 0.14) !important;
    }

    body.light .rr-inicio-event-call__mobile-selector-trigger i {
        color: #16a34a !important;
    }

    body.light .rr-inicio-event-call__mobile-selector-menu {
        border-color: rgba(34, 197, 94, 0.2) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(240, 253, 244, 0.96)) !important;
        box-shadow: 0 16px 28px rgba(148, 163, 184, 0.18) !important;
    }

    body.light .rr-inicio-event-call__mobile-selector-option {
        border-color: rgba(148, 163, 184, 0.28) !important;
        background: rgba(255, 255, 255, 0.9) !important;
        color: #14532d !important;
    }

    body.light .rr-inicio-event-call__mobile-selector-option.is-active {
        border-color: rgba(34, 197, 94, 0.32) !important;
        background: linear-gradient(180deg, rgba(220, 252, 231, 0.96), rgba(187, 247, 208, 0.9)) !important;
        color: #14532d !important;
    }

    body.light .rr-inicio-event-call__badge,
    body.light .rr-inicio-event-call__launch-kicker,
    body.light .rr-inicio-event-call__mobile-badge {
        color: #1e3a8a !important;
        font-weight: 900 !important;
    }

    body.light .rr-inicio-event-call__badge {
        border-color: rgba(249, 115, 22, 0.18) !important;
        background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(255,247,237,0.94)) !important;
        box-shadow: 0 10px 24px rgba(148, 163, 184, 0.14), inset 0 1px 0 rgba(255,255,255,0.9) !important;
    }

    body.light .rr-inicio-event-call__badge i,
    body.light .rr-inicio-event-call__launch-kicker i,
    body.light .rr-inicio-event-call__mobile-badge i {
        color: #f97316 !important;
    }

    body.light .rr-inicio-event-call__badge-dot,
    body.light .rr-inicio-event-call__launch-kicker::before {
        background: #f97316 !important;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14) !important;
    }

    body.light .rr-inicio-event-call__launch-floater,
    body.light .rr-inicio-event-call__launch-floater strong,
    body.light .rr-inicio-event-call__launch-floater span {
        color: #1e3a8a !important;
        font-weight: 800 !important;
    }

    body.light .rr-inicio-event-call__launch-floater i {
        color: #f97316 !important;
        background: rgba(249, 115, 22, 0.12) !important;
    }

    body .rr-inicio-event-call__mobile-badge i {
        color: #f97316 !important;
        font-size: 0.78rem !important;
    }

    body .rr-inicio-event-call__mobile-badges {
        justify-content: center !important;
        align-items: center !important;
        gap: 8px !important;
    }

    body .rr-inicio-event-call__mobile-badge--one {
        left: auto !important;
        right: auto !important;
        top: 0 !important;
        transform: rotate(0deg) !important;
        animation: rrInicioLaunchMobileBadgeFloat 4.8s ease-in-out infinite !important;
    }

    body .rr-inicio-event-call__mobile-badge--two {
        display: none !important;
    }

    body .rr-inicio-event-call__mobile-badge--three {
        display: none !important;
    }

    body .rr-inicio-event-call__mobile-badge--four {
        display: none !important;
    }

    body .rr-inicio-event-call__mobile-badge--five {
        right: auto !important;
        left: auto !important;
        top: 0 !important;
        transform: rotate(0deg) !important;
        animation: rrInicioLaunchMobileBadgeFloat 4.8s ease-in-out infinite 2.1s !important;
    }

    body .rr-inicio-event-call__timer {
        position: relative !important;
        left: auto !important;
        bottom: auto !important;
        transform: none !important;
        z-index: 8 !important;
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        min-width: 0 !important;
        min-height: 42px !important;
        margin-top: 2px !important;
        padding: 0 14px !important;
        border-radius: 999px !important;
        border: 1px solid rgba(255, 255, 255, 0.16) !important;
        text-align: center !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08)) !important;
        color: #fff7ed !important;
        box-shadow: 0 14px 24px rgba(3, 7, 18, 0.22) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
    }

    body .rr-inicio-event-call__timer-label {
        font-size: 0.54rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.1em !important;
        text-transform: uppercase !important;
        color: rgba(255, 247, 237, 0.84) !important;
    }

    body .rr-inicio-event-call__timer-value {
        font-size: 1rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.04em !important;
        line-height: 1 !important;
        white-space: nowrap !important;
    }

    body .rr-inicio-event-call__timer-main {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
    }

    body .rr-inicio-event-call__timer-reminder {
        width: 42px !important;
        height: 42px !important;
        border-radius: 999px !important;
        flex: 0 0 42px !important;
        position: relative !important;
        z-index: 2 !important;
        pointer-events: auto !important;
        touch-action: manipulation !important;
        border: 1px solid rgba(255, 255, 255, 0.16) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08)) !important;
        color: #fff7ed !important;
        box-shadow: 0 14px 24px rgba(3, 7, 18, 0.22) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
    }

    body.light .rr-bolao-launch-simple__btn {
        background:
            radial-gradient(circle at var(--rr-bolao-pointer-x) var(--rr-bolao-pointer-y), rgba(var(--rr-bolao-accent-rgb), 0.14), transparent 32%),
            linear-gradient(180deg, rgba(255, 244, 236, 0.98), rgba(255, 239, 229, 0.96)) !important;
        box-shadow:
            0 22px 34px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.96),
            inset 0 -16px 24px rgba(249, 115, 22, 0.05) !important;
    }

    body.light .rr-bolao-launch-simple__btn[data-disabled="1"] {
        opacity: 0.84 !important;
        filter: saturate(0.9) !important;
    }

    body.light .rr-bolao-launch-simple__kicker,
    body.light .rr-bolao-launch-simple__meta-badge {
        color: #334155 !important;
        border-color: rgba(148, 163, 184, 0.28) !important;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(241,245,249,0.94)) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.98), 0 8px 16px rgba(148, 163, 184, 0.12) !important;
    }

    body.light .rr-bolao-launch-simple__price-label-main {
        color: #7c2d12 !important;
    }

    body.light .rr-bolao-launch-simple__price-label-sub {
        color: rgba(var(--rr-bolao-accent-rgb), 0.98) !important;
    }

    body.light .rr-bolao-launch-simple__cta {
        color: #eff6ff !important;
        text-shadow: 0 1px 0 rgba(15, 23, 42, 0.18) !important;
    }

    body.light .rr-bolao-launch-simple__cta:disabled {
        color: #cbd5e1 !important;
        border-color: rgba(148, 163, 184, 0.3) !important;
        background:
            linear-gradient(180deg, rgba(148, 163, 184, 0.88), rgba(100, 116, 139, 0.94)) !important;
        box-shadow:
            0 10px 18px rgba(148, 163, 184, 0.18),
            inset 0 1px 0 rgba(255,255,255,0.32),
            inset 0 -8px 14px rgba(51, 65, 85, 0.16) !important;
    }

    body.light .rr-inicio-event-call__timer {
        border-color: rgba(148, 163, 184, 0.26) !important;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(241,245,249,0.94)) !important;
        color: #0f172a !important;
        box-shadow:
            0 14px 26px rgba(148, 163, 184, 0.18),
            inset 0 1px 0 rgba(255,255,255,0.98) !important;
    }

    body.light .rr-inicio-event-call__timer-label {
        color: #9a3412 !important;
    }

    body.light .rr-inicio-event-call__timer-value {
        color: #0f172a !important;
        text-shadow: none !important;
    }

    body.light .rr-inicio-event-call__timer-reminder {
        border-color: rgba(59, 130, 246, 0.28) !important;
        background: linear-gradient(180deg, rgba(96,165,250,0.98), rgba(37,99,235,0.94)) !important;
        color: #eff6ff !important;
        box-shadow:
            0 12px 22px rgba(96, 165, 250, 0.24),
            inset 0 1px 0 rgba(255,255,255,0.28) !important;
    }

    html.rr-ios body .rr-inicio-event-call__mobile-badge,
    html.rr-ios body .rr-inicio-event-call__mobile-selector-trigger,
    html.rr-ios body .rr-inicio-event-call__timer,
    html.rr-ios body .rr-inicio-event-call__timer-reminder {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        opacity: 1 !important;
    }

    html.rr-ios body .rr-inicio-event-call__mobile-badge {
        border-color: rgba(255, 255, 255, 0.26) !important;
        background: linear-gradient(180deg, rgba(17, 24, 39, 0.96), rgba(2, 6, 23, 0.92)) !important;
        color: #fff !important;
        box-shadow: 0 14px 24px rgba(2, 6, 23, 0.36) !important;
    }

    html.rr-ios body.light .rr-inicio-event-call__mobile-badge {
        border-color: rgba(194, 65, 12, 0.24) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 245, 235, 0.96)) !important;
        color: #7c2d12 !important;
        box-shadow: 0 12px 22px rgba(148, 163, 184, 0.2) !important;
    }

    @keyframes rrInicioLaunchMobileBadgeFloat {
        0%, 100% { transform: translate3d(0, 0, 0); }
        50% { transform: translate3d(0, -6px, 0); }
    }

    @keyframes rrInicioLaunchMobileBadgeFloatCenter {
        0%, 100% { transform: translate3d(-50%, 0, 0); }
        50% { transform: translate3d(-50%, -6px, 0); }
    }
}`;
    }

    const inicioHeroCarouselState = {
        items: [],
        focusIndex: 0,
        rotation: 0,
        intervalId: null,
        startX: 0,
        startY: 0,
        dragging: false,
        lastPointerX: 0,
        lastPointerY: 0,
        dragAxis: null,
        suppressClickUntil: 0,
    };

    function getInicioHeroVisualRoot() {
        const heroRoot = root.querySelector('#rrInicioEventCall');
        return heroRoot ? heroRoot.querySelector('#rrInicioEventVisual') : null;
    }

    function getInicioHeroItems(payload) {
        const fallbackLogo = `{{ asset('assets/images/logo_icon/logo.png') }}`;
        const rawItems = Array.isArray(payload?.items) && payload.items.length
            ? payload.items
            : (Array.isArray(payload?.logos) && payload.logos.length ? payload.logos : [{
                rodeio_id: payload?.rodeio_id || 0,
                title: payload?.title || 'Rei do Rodeio',
                logo_url: payload?.logo_url || fallbackLogo,
                timer_iso: payload?.timer_iso || null,
                mode: payload?.mode || 'scheduled',
                badge: payload?.badge || 'Programado',
                accent: payload?.accent || 'Próximo evento',
                label: payload?.label || 'Começa em',
                status_transmissao: payload?.status_transmissao || '',
            }]);

        return rawItems.map(function (item, index) {
            return {
                rodeio_id: Number(item?.rodeio_id || 0),
                title: String(item?.title || payload?.title || ('Rodeio ' + (index + 1))),
                logo_url: String(item?.logo_url || payload?.logo_url || fallbackLogo),
                timer_iso: item?.timer_iso || payload?.timer_iso || null,
                mode: String(item?.mode || payload?.mode || 'scheduled'),
                badge: String(item?.badge || payload?.badge || 'Programado'),
                accent: String(item?.accent || payload?.accent || 'Próximo evento'),
                label: String(item?.label || payload?.label || 'Começa em'),
                status_transmissao: String(item?.status_transmissao || payload?.status_transmissao || ''),
            };
        });
    }

    function applyInicioHeroDisplayPayload(payload) {
        const heroRoot = root.querySelector('#rrInicioEventCall');
        if (!heroRoot || !payload || typeof payload !== 'object') return;

        const mode = String(payload.mode || 'empty');
        const badge = heroRoot.querySelector('#rrInicioEventBadge');
        const badgeText = heroRoot.querySelector('#rrInicioEventBadgeText');
        const accentText = heroRoot.querySelector('#rrInicioEventAccentText');
        const timerRoot = heroRoot.querySelector('#rrInicioEventTimer');
        const timerLabel = heroRoot.querySelector('#rrInicioEventTimerLabel');
        const timerValue = heroRoot.querySelector('#rrInicioEventTimerValue');
        const oldDot = heroRoot.querySelector('#rrInicioEventBadgeDot');
        const oldIcon = heroRoot.querySelector('#rrInicioEventBadgeIcon');

        if (badge) {
            badge.classList.toggle('rr-inicio-event-call__badge--live', mode === 'live');
        }

        if (badgeText) {
            badgeText.textContent = String(payload.badge || (mode === 'live' ? 'Ao vivo agora' : 'Programado'));
        }

        if (accentText) {
            accentText.textContent = String(payload.accent || (mode === 'live' ? 'Arena aberta' : 'Próximo evento'));
        }

        if (timerRoot) {
            timerRoot.setAttribute('data-mode', mode);
            timerRoot.setAttribute('data-rodeio-id', String(payload.rodeio_id || 0));
            timerRoot.setAttribute('data-rodeio-title', String(payload.title || 'Próximo rodeio'));
            if (payload.timer_iso) {
                timerRoot.setAttribute('data-closes-at', payload.timer_iso);
            } else {
                timerRoot.removeAttribute('data-closes-at');
            }
        }

        if (timerLabel) {
            timerLabel.textContent = String(payload.label || (mode === 'live' ? 'Rodeio termina em' : 'Começa em'));
        }

        applyInicioHeroUrgencyContent(payload);

        if (mode === 'live' && !oldDot && badge) {
            const dot = document.createElement('span');
            dot.className = 'rr-inicio-event-call__badge-dot';
            dot.setAttribute('aria-hidden', 'true');
            dot.id = 'rrInicioEventBadgeDot';
            badge.insertBefore(dot, badge.firstChild);
        }

        if (mode !== 'live' && !oldIcon && badge) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-calendar-check';
            icon.setAttribute('aria-hidden', 'true');
            icon.id = 'rrInicioEventBadgeIcon';
            badge.insertBefore(icon, badge.firstChild);
        }

        if (mode === 'live' && oldIcon) {
            oldIcon.remove();
        }

        if (mode !== 'live' && oldDot) {
            oldDot.remove();
        }

        if (timerValue) {
            timerValue.textContent = formatInicioHeroCountdown(payload.timer_iso || '');
        }

        syncReminderTrigger({
            rodeioId: Number(payload.rodeio_id || 0),
            mode: mode,
            title: String(payload.title || 'Próximo rodeio'),
        });
    }

    function stopInicioHeroCarouselAuto() {
        if (inicioHeroCarouselState.intervalId) {
            window.clearInterval(inicioHeroCarouselState.intervalId);
            inicioHeroCarouselState.intervalId = null;
        }
    }

    function startInicioHeroCarouselAuto() {
        stopInicioHeroCarouselAuto();
        if (inicioHeroCarouselState.items.length <= 1) return;
        inicioHeroCarouselState.intervalId = window.setInterval(function () {
            rotateInicioHeroCarousel(1);
        }, 3200);
    }

    function updateInicioHeroCarouselScene() {
        const scene = root.querySelector('#rrInicioEventCarousel');
        const dots = root.querySelector('#rrInicioEventCarouselDots');
        if (!scene) return;

        const total = inicioHeroCarouselState.items.length;
        if (!total) return;
        const angleStep = 360 / total;
        inicioHeroCarouselState.rotation = -(inicioHeroCarouselState.focusIndex * angleStep);
        scene.style.transform = 'rotateY(' + inicioHeroCarouselState.rotation + 'deg)';

        scene.querySelectorAll('.rr-inicio-event-carousel__card').forEach(function (card, index) {
            card.classList.toggle('is-active', index === inicioHeroCarouselState.focusIndex);
            card.style.opacity = index === inicioHeroCarouselState.focusIndex ? '1' : '0.56';
            card.style.filter = index === inicioHeroCarouselState.focusIndex ? 'brightness(1.04)' : 'brightness(.84)';
            card.style.boxShadow = index === inicioHeroCarouselState.focusIndex
                ? '0 30px 40px rgba(0, 0, 0, .38), 0 0 28px rgba(245, 158, 11, .18), inset 0 1px 0 rgba(255,255,255,.08)'
                : '';
        });

        if (dots) {
            dots.querySelectorAll('.rr-inicio-event-carousel__dot').forEach(function (dot, index) {
                dot.classList.toggle('is-active', index === inicioHeroCarouselState.focusIndex);
            });
        }

        applyInicioHeroDisplayPayload(inicioHeroCarouselState.items[inicioHeroCarouselState.focusIndex]);
    }

    function rotateInicioHeroCarousel(step) {
        const total = inicioHeroCarouselState.items.length;
        if (!total) return;
        inicioHeroCarouselState.focusIndex = (inicioHeroCarouselState.focusIndex + step + total) % total;
        updateInicioHeroCarouselScene();
    }

    function openInicioHeroLightbox(src, alt) {
        const lightbox = root.querySelector('#rrInicioEventLightbox');
        const img = root.querySelector('#rrInicioEventLightboxImg');
        if (!lightbox || !img) return;
        img.src = src;
        img.alt = alt || 'Rodeio em destaque';
        lightbox.hidden = false;
    }

    function renderInicioHeroCarousel(items) {
        const visualRoot = getInicioHeroVisualRoot();
        if (!visualRoot) return;

        visualRoot.innerHTML = '<div class="rr-inicio-event-carousel" id="rrInicioEventCarouselWrap">'
            + '<div class="rr-inicio-event-carousel__scene" id="rrInicioEventCarousel"></div>'
            + '</div><div class="rr-inicio-event-carousel__dots" id="rrInicioEventCarouselDots"></div>';

        const scene = visualRoot.querySelector('#rrInicioEventCarousel');
        const dots = visualRoot.querySelector('#rrInicioEventCarouselDots');
        if (!scene || !dots) return;

        inicioHeroCarouselState.items = items.slice();
        const total = items.length;
        const angleStep = 360 / total;
        const radius = window.innerWidth <= 767 ? 150 : 240;
        const previousRodeioId = Number(root.querySelector('#rrInicioEventTimer')?.getAttribute('data-rodeio-id') || 0);
        const preservedIndex = Math.max(0, items.findIndex(function (item) { return Number(item.rodeio_id || 0) === previousRodeioId; }));
        inicioHeroCarouselState.focusIndex = preservedIndex;

        scene.innerHTML = items.map(function (item, index) {
            const src = String(item.logo_url || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
            const title = String(item.title || 'Rei do Rodeio').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return '<article class="rr-inicio-event-carousel__card" data-index="' + index + '" style="transform: rotateY(' + (index * angleStep) + 'deg) translateZ(' + radius + 'px)">'
                + '<div class="rr-inicio-event-carousel__media">'
                + '<img class="rr-inicio-event-carousel__image" src="' + src + '" alt="' + title + '" onerror="this.src=\'{{ asset('assets/images/logo_icon/logo.png') }}\'">'
                + '</div>'
                + '<div class="rr-inicio-event-carousel__caption">' + title + '</div>'
                + '</article>';
        }).join('');

        dots.innerHTML = items.map(function (_, index) {
            return '<span class="rr-inicio-event-carousel__dot' + (index === inicioHeroCarouselState.focusIndex ? ' is-active' : '') + '" data-index="' + index + '"></span>';
        }).join('');

        scene.querySelectorAll('.rr-inicio-event-carousel__card').forEach(function (card) {
            card.addEventListener('click', function () {
                if (Date.now() < inicioHeroCarouselState.suppressClickUntil) {
                    return;
                }
                const index = Number(card.getAttribute('data-index') || 0);
                if (index === inicioHeroCarouselState.focusIndex) {
                    const item = inicioHeroCarouselState.items[index];
                    openInicioHeroLightbox(item.logo_url, item.title);
                    return;
                }
                inicioHeroCarouselState.focusIndex = index;
                updateInicioHeroCarouselScene();
                startInicioHeroCarouselAuto();
            });
        });

        dots.querySelectorAll('.rr-inicio-event-carousel__dot').forEach(function (dot) {
            dot.addEventListener('click', function () {
                inicioHeroCarouselState.focusIndex = Number(dot.getAttribute('data-index') || 0);
                updateInicioHeroCarouselScene();
                startInicioHeroCarouselAuto();
            });
        });

        scene.addEventListener('mouseenter', stopInicioHeroCarouselAuto);
        scene.addEventListener('mouseleave', startInicioHeroCarouselAuto);
        scene.addEventListener('touchstart', function (event) {
            stopInicioHeroCarouselAuto();
            inicioHeroCarouselState.dragging = true;
            inicioHeroCarouselState.startX = event.touches[0].clientX;
            inicioHeroCarouselState.startY = event.touches[0].clientY;
            inicioHeroCarouselState.lastPointerX = inicioHeroCarouselState.startX;
            inicioHeroCarouselState.lastPointerY = inicioHeroCarouselState.startY;
            inicioHeroCarouselState.dragAxis = null;
        }, { passive: true });
        scene.addEventListener('touchmove', function (event) {
            if (!inicioHeroCarouselState.dragging) return;
            const touch = event.touches[0];
            const deltaX = touch.clientX - inicioHeroCarouselState.startX;
            const deltaY = touch.clientY - inicioHeroCarouselState.startY;

            inicioHeroCarouselState.lastPointerX = touch.clientX;
            inicioHeroCarouselState.lastPointerY = touch.clientY;

            if (!inicioHeroCarouselState.dragAxis) {
                if (Math.abs(deltaX) < 8 && Math.abs(deltaY) < 8) return;
                inicioHeroCarouselState.dragAxis = Math.abs(deltaX) > Math.abs(deltaY) ? 'x' : 'y';
            }

            if (inicioHeroCarouselState.dragAxis === 'x') {
                event.preventDefault();
            }
        }, { passive: false });
        scene.addEventListener('touchend', function () {
            if (!inicioHeroCarouselState.dragging) return;
            const delta = inicioHeroCarouselState.lastPointerX - inicioHeroCarouselState.startX;
            const deltaY = inicioHeroCarouselState.lastPointerY - inicioHeroCarouselState.startY;
            const wasHorizontal = inicioHeroCarouselState.dragAxis === 'x';
            inicioHeroCarouselState.dragging = false;
            inicioHeroCarouselState.dragAxis = null;
            if (wasHorizontal && Math.abs(delta) > 26 && Math.abs(delta) > Math.abs(deltaY)) {
                inicioHeroCarouselState.suppressClickUntil = Date.now() + 350;
                rotateInicioHeroCarousel(delta < 0 ? 1 : -1);
            }
            startInicioHeroCarouselAuto();
        });
        scene.addEventListener('touchcancel', function () {
            inicioHeroCarouselState.dragging = false;
            inicioHeroCarouselState.dragAxis = null;
            startInicioHeroCarouselAuto();
        });

        updateInicioHeroCarouselScene();
        startInicioHeroCarouselAuto();
    }

    function renderInicioHeroLogos(payload) {
        const heroRoot = root.querySelector('#rrInicioEventCall');
        const visualRoot = heroRoot ? heroRoot.querySelector('#rrInicioEventVisual') : null;
        if (!visualRoot) return;

        const fallbackLogo = `{{ asset('assets/images/logo_icon/logo.png') }}`;
        const items = getInicioHeroItems(payload);

        if (items.length > 1) {
            renderInicioHeroCarousel(items);
            return;
        }

        stopInicioHeroCarouselAuto();
        visualRoot.innerHTML = '<span class="rr-inicio-event-call__logo-stack" id="rrInicioEventLogoStack"></span>';
        const logoStack = visualRoot.querySelector('#rrInicioEventLogoStack');
        if (!logoStack) return;

        logoStack.innerHTML = items.map(function(item, index) {
            const src = String(item?.logo_url || fallbackLogo)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;');
            const alt = String(item?.title || 'Rei do Rodeio')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            return '<span class="rr-inicio-event-call__logo-frame">'
                + '<img class="rr-inicio-event-call__logo"'
                + (index === 0 ? ' id="rrInicioEventLogo"' : '')
                + ' src="' + src + '" alt="' + alt + '"'
                + ' onerror="this.src=\'' + fallbackLogo + '\'">'
                + '</span>';
        }).join('');
    }

    function applyInicioHeroState(payload) {
        const heroRoot = root.querySelector('#rrInicioEventCall');
        if (!heroRoot || !payload || typeof payload !== 'object') return;
        renderInicioHeroLogos(payload);
        if (getInicioHeroItems(payload).length <= 1) {
            applyInicioHeroDisplayPayload(getInicioHeroItems(payload)[0] || payload);
        }
    }

    function updateInicioHeroTimer() {
        const timerRoot = root.querySelector('#rrInicioEventTimer');
        const timerValue = timerRoot ? timerRoot.querySelector('.rr-inicio-event-call__timer-value') : null;
        if (!timerRoot || !timerValue) return;
        timerValue.textContent = formatInicioHeroCountdown(timerRoot.getAttribute('data-closes-at'));
    }

    function refreshInicioHeroState() {
        const endpoint = window.RR_INICIO_HERO_STATUS_URL;
        if (!endpoint || !root.querySelector('#rrInicioEventCall')) return;

        fetch(endpoint, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function(response) {
                if (!response.ok) return null;
                return response.json();
            })
            .then(function(payload) {
                if (!payload || !payload.success || !payload.data) return;
                applyInicioHeroState(payload.data);
            })
            .catch(function(error) {
                console.debug('[inicio-hero] atualização falhou', error);
            });
    }

    ensureInicioHeroMobileStyles();

    const reminderModal = root.querySelector('#rrInicioReminderModal');
    const reminderTrigger = root.querySelector('#rrInicioReminderTrigger');
    const reminderClose = root.querySelector('#rrInicioReminderClose');
    const reminderCancel = root.querySelector('#rrInicioReminderCancel');
    const reminderForm = root.querySelector('#rrInicioReminderForm');
    const reminderStatus = root.querySelector('#rrInicioReminderStatus');
    const reminderSubmit = root.querySelector('#rrInicioReminderSubmit');
    const reminderModalText = root.querySelector('#rrInicioReminderText');
    const reminderHint = root.querySelector('#rrInicioReminderHint');
    const reminderSuccess = root.querySelector('#rrInicioReminderSuccess');
    const reminderSuccessText = root.querySelector('#rrInicioReminderSuccessText');
    const reminderPrefill = window.RR_RODEIO_REMINDER_PREFILL || {};
    const reminderState = {
        submitting: false,
        reloadTimer: null,
        lastOpenAt: 0,
        subscribedRodeios: new Set(
            (Array.isArray(reminderPrefill.subscribedRodeios) ? reminderPrefill.subscribedRodeios : [])
                .map(function (value) { return Number(value || 0); })
                .filter(function (value) { return value > 0; })
        ),
    };

    if (reminderModal) {
        document.querySelectorAll('#rrInicioReminderModal').forEach(function (node) {
            if (node !== reminderModal) {
                node.remove();
            }
        });

        if (reminderModal.parentElement !== document.body) {
            document.body.appendChild(reminderModal);
        }

        reminderModal.hidden = true;
        reminderModal.classList.remove('is-open');
        reminderModal.setAttribute('aria-hidden', 'true');
    }

    function getCurrentReminderMeta() {
        const timerRoot = root.querySelector('#rrInicioEventTimer');
        if (!timerRoot) {
            return { rodeioId: 0, mode: 'empty', title: 'Próximo rodeio' };
        }

        return {
            rodeioId: Number(timerRoot.getAttribute('data-rodeio-id') || 0),
            mode: String(timerRoot.getAttribute('data-mode') || 'empty'),
            title: String(timerRoot.getAttribute('data-rodeio-title') || 'Próximo rodeio'),
        };
    }

    function buildReminderUrl(rodeioId) {
        const template = String(window.RR_RODEIO_EMAIL_REMINDER_URL_TEMPLATE || '');
        if (!template || !rodeioId) return '';
        return template.replace('__RODEIO__', String(rodeioId));
    }

    function setReminderStatus(message, tone) {
        if (!reminderStatus) return;
        reminderStatus.textContent = String(message || '');
        reminderStatus.classList.remove('is-error', 'is-success');
        if (tone === 'error') reminderStatus.classList.add('is-error');
        if (tone === 'success') reminderStatus.classList.add('is-success');
    }

    function resetReminderModalState() {
        if (reminderForm) {
            reminderForm.hidden = false;
        }

        if (reminderSuccess) {
            reminderSuccess.hidden = true;
        }

        setReminderStatus('', '');
    }

    function showReminderSuccess(message) {
        if (reminderForm) {
            reminderForm.hidden = true;
        }

        if (reminderSuccessText) {
            reminderSuccessText.textContent = String(message || 'Seu aviso de início foi ligado com sucesso.');
        }

        if (reminderSuccess) {
            reminderSuccess.hidden = false;
        }

        setReminderStatus('', '');
    }

    function syncReminderTrigger(meta) {
        if (!reminderTrigger) return;

        const reminderMeta = meta || getCurrentReminderMeta();
        const isAvailable = reminderMeta.rodeioId > 0 && reminderMeta.mode !== 'live';
        const isActive = reminderState.subscribedRodeios.has(reminderMeta.rodeioId);
        const triggerIcon = reminderTrigger.querySelector('i');

        reminderTrigger.hidden = !isAvailable || isActive;
        reminderTrigger.disabled = !isAvailable || !!reminderState.submitting || isActive;
        reminderTrigger.classList.toggle('is-active', isActive);
        reminderTrigger.classList.toggle('is-loading', !!reminderState.submitting);
        const triggerLabel = isActive ? 'Alerta por e-mail já ativado' : 'Ativar notificação por e-mail do rodeio';
        reminderTrigger.setAttribute('title', triggerLabel);
        reminderTrigger.setAttribute('aria-label', triggerLabel);

        if (triggerIcon) {
            triggerIcon.className = isActive ? 'fas fa-bell-slash' : 'fas fa-bell';
            triggerIcon.setAttribute('aria-hidden', 'true');
        }
    }

    function ensureReminderCanActivate() {
        const meta = getCurrentReminderMeta();
        if (!meta.rodeioId || meta.mode === 'live') return null;

        if (!window.RR_RODEIO_REMINDER_PREFILL?.authenticated) {
            if (typeof window.openAuthModal === 'function') {
                window.openAuthModal();
                return null;
            }
            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
                return null;
            }
            setReminderStatus('Faça login para ativar a notificação do rodeio.', 'error');
            return null;
        }

        if (!window.RR_RODEIO_REMINDER_PREFILL?.hasRealEmail) {
            const profileReason = 'Para ativar a notificação de início, você precisa cadastrar um e-mail real no seu perfil. Sem ele não conseguimos te avisar quando o rodeio começar.';

            if (typeof window.openProfileTargetWithAlert === 'function') {
                window.openProfileTargetWithAlert('perfil', '<strong>E-mail obrigatório para o alerta.</strong><br>' + profileReason, 'info');
                return null;
            }

            if (typeof window.openProfileTarget === 'function') {
                window.openProfileTarget('perfil');
                return null;
            }

            setReminderStatus(profileReason, 'error');
            return null;
        }

        return meta;
    }

    function openReminderModal() {
        if (!reminderModal || !reminderForm) return;

        const meta = ensureReminderCanActivate();
        if (!meta) return;

        if (reminderModalText) {
            reminderModalText.textContent = 'Quer receber o aviso quando ' + meta.title + ' começar?';
        }

        if (reminderHint) {
            const userEmail = String(window.RR_RODEIO_REMINDER_PREFILL?.email || '').trim();
            reminderHint.textContent = userEmail !== ''
                ? 'Se você confirmar, vamos usar ' + userEmail + ' para enviar a confirmação agora e o aviso de início do rodeio.'
                : 'Se você confirmar, vamos usar o e-mail da sua conta para enviar a confirmação agora e o aviso de início do rodeio.';
        }

        resetReminderModalState();

        if (reminderState.subscribedRodeios.has(meta.rodeioId)) {
            showReminderSuccess('Esse alerta já está ativo. Quando ' + meta.title + ' começar, vamos avisar no e-mail da sua conta.');
        }

        reminderModal.hidden = false;
        reminderModal.classList.add('is-open');
        reminderModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('rr-reminder-body-lock');
    }

    async function handleReminderTriggerPress(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (reminderState.submitting) {
            return;
        }

        const now = Date.now();
        if (now - reminderState.lastOpenAt < 320) {
            return;
        }

        reminderState.lastOpenAt = now;
        const meta = ensureReminderCanActivate();
        if (!meta) {
            return;
        }

        if (reminderState.subscribedRodeios.has(meta.rodeioId)) {
            syncReminderTrigger(meta);
            return;
        }

        reminderState.submitting = true;
        syncReminderTrigger(meta);

        try {
            const result = await activateReminderRequest();

            if (!result.ok) {
                if (result.status === 401) {
                    if (typeof window.openAuthModal === 'function') {
                        window.openAuthModal();
                    } else if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                        window.RRAuthModal.open();
                    }
                    return;
                }

                alert(String(result.data?.message || result.message || 'Não foi possível ativar o alerta agora.'));
                return;
            }

            reminderState.subscribedRodeios.add(result.meta.rodeioId);
            syncReminderTrigger(result.meta);
        } catch (error) {
            console.debug('[rodeio-reminder] falha ao ativar alerta', error);
            alert('Falha de conexão ao ativar o alerta. Tente de novo.');
        } finally {
            reminderState.submitting = false;
            syncReminderTrigger(meta);
        }
    }

    window.RRHandleInicioReminderTrigger = handleReminderTriggerPress;

    function closeReminderModal() {
        if (!reminderModal) return;
        reminderModal.classList.remove('is-open');
        reminderModal.setAttribute('aria-hidden', 'true');
        reminderModal.hidden = true;
        document.body.classList.remove('rr-reminder-body-lock');
        resetReminderModalState();
    }

    async function activateReminderRequest() {
        const meta = getCurrentReminderMeta();
        if (!meta.rodeioId) {
            return { ok: false, message: 'Não encontramos o próximo rodeio para ativar o alerta agora.' };
        }

        const endpoint = buildReminderUrl(meta.rodeioId);
        if (!endpoint) {
            return { ok: false, message: 'Não foi possível montar a rota do alerta agora.' };
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({}),
        });

        const rawResponse = await response.text();
        let data = {};

        try {
            data = rawResponse ? JSON.parse(rawResponse) : {};
        } catch (error) {
            data = {
                message: rawResponse && rawResponse.length < 240 ? rawResponse : '',
            };
        }

        return {
            ok: response.ok && !!data?.success,
            status: response.status,
            data: data,
            meta: meta,
        };
    }

    async function submitReminderForm(event) {
        event.preventDefault();
        if (!reminderForm || reminderState.submitting) return;

        reminderState.submitting = true;
        if (reminderSubmit) reminderSubmit.disabled = true;
        setReminderStatus('Ativando sua notificação...', '');

        try {
            const result = await activateReminderRequest();

            if (!result.ok) {
                if (result.status === 401) {
                    closeReminderModal();
                    if (typeof window.openAuthModal === 'function') {
                        window.openAuthModal();
                        return;
                    }
                    if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                        window.RRAuthModal.open();
                        return;
                    }
                }

                setReminderStatus(String(result.data?.message || result.message || 'Não foi possível ativar o alerta agora.'), 'error');
                return;
            }

            reminderState.subscribedRodeios.add(result.meta.rodeioId);
            syncReminderTrigger(result.meta);
            showReminderSuccess(String(result.data?.message || 'Alerta ativado com sucesso. Recarregando sua arena...'));

            if (reminderState.reloadTimer) {
                window.clearTimeout(reminderState.reloadTimer);
            }

            reminderState.reloadTimer = window.setTimeout(function () {
                window.location.reload();
            }, 1300);
        } catch (error) {
            console.debug('[rodeio-reminder] falha ao ativar alerta', error);
            setReminderStatus('Falha de conexão ao ativar o alerta. Tente de novo.', 'error');
        } finally {
            reminderState.submitting = false;
            if (reminderSubmit) reminderSubmit.disabled = false;
        }
    }

    if (reminderTrigger) {
        reminderTrigger.addEventListener('click', handleReminderTriggerPress);
        reminderTrigger.addEventListener('touchend', handleReminderTriggerPress, { passive: false });
    }

    if (!window.RRInicioReminderGlobalBinding) {
        window.RRInicioReminderGlobalBinding = true;

        document.addEventListener('click', function (event) {
            const trigger = event.target && event.target.closest ? event.target.closest('#rrInicioReminderTrigger') : null;
            if (!trigger) return;
            if (typeof window.RRHandleInicioReminderTrigger === 'function') {
                window.RRHandleInicioReminderTrigger(event);
            }
        });

        document.addEventListener('touchend', function (event) {
            const trigger = event.target && event.target.closest ? event.target.closest('#rrInicioReminderTrigger') : null;
            if (!trigger) return;
            if (typeof window.RRHandleInicioReminderTrigger === 'function') {
                window.RRHandleInicioReminderTrigger(event);
            }
        }, { passive: false });
    }

    if (reminderClose) {
        reminderClose.addEventListener('click', closeReminderModal);
    }

    if (reminderCancel) {
        reminderCancel.addEventListener('click', closeReminderModal);
    }

    if (reminderModal) {
        reminderModal.addEventListener('click', function (event) {
            if (event.target === reminderModal) {
                closeReminderModal();
            }
        });
    }

    if (reminderForm) {
        reminderForm.addEventListener('submit', submitReminderForm);
    }

    function resolveInicioHeroUrgencyContent(payload) {
        const mode = String(payload?.mode || 'scheduled');
        const title = String(payload?.title || 'o evento');

        if (mode === 'live') {
            return {
                kicker: 'Bolão valendo agora',
                title: 'O prêmio do ' + title + ' está correndo e quem demora entra atrás.',
                note: 'Monte sua equipe antes do cronômetro apertar e antes da galera ocupar as melhores posições da disputa.',
                floaters: [
                    { title: 'Entrada quente', meta: 'quem entra agora ainda pega o melhor timing' },
                    { title: 'Vagas sob pressão', meta: 'a arena está recebendo equipes neste momento' },
                    { title: 'Fecha no relógio', meta: 'depois da janela, só sobra assistir' },
                ]
            };
        }

        return {
            kicker: 'Janela curta do bolão',
            title: '',
            note: '',
            floaters: [
                { title: 'Última janela', meta: 'quem prepara cedo larga na frente' },
                { title: 'Prêmio em mira', meta: 'só disputa quem confirmar a equipe' },
                { title: 'Relógio armado', meta: 'o melhor timing não espera ninguém' },
            ]
        };
    }

    function applyInicioHeroUrgencyContent(payload) {
        const heroRoot = root.querySelector('#rrInicioEventCall');
        if (!heroRoot || !heroRoot.classList.contains('rr-inicio-event-call--launch')) return;

        const content = resolveInicioHeroUrgencyContent(payload);
        const kickerEl = heroRoot.querySelector('#rrInicioEventUrgencyKicker');
        const titleEl = heroRoot.querySelector('#rrInicioEventUrgencyTitle');
        const noteEl = heroRoot.querySelector('#rrInicioEventUrgencyNote');

        if (kickerEl) kickerEl.textContent = content.kicker;
        if (titleEl) titleEl.textContent = content.title;
        if (noteEl) noteEl.textContent = content.note;

        const mapping = [
            ['#rrInicioEventFloaterOneTitle', '#rrInicioEventFloaterOneMeta'],
            ['#rrInicioEventFloaterTwoTitle', '#rrInicioEventFloaterTwoMeta'],
            ['#rrInicioEventFloaterThreeTitle', '#rrInicioEventFloaterThreeMeta'],
        ];

        mapping.forEach(function(selectors, index) {
            const item = content.floaters[index] || null;
            if (!item) return;
            const titleNode = heroRoot.querySelector(selectors[0]);
            const metaNode = heroRoot.querySelector(selectors[1]);
            if (titleNode) titleNode.textContent = item.title;
            if (metaNode) metaNode.textContent = item.meta;
        });

    }

    function syncMobileQuickNavChrome() {
        if (!mobileQuickNav) return;

        const isMobile = isMobileQuickViewport();
        const actions = mobileQuickNav.querySelector('.rr-mobile-quick-nav__actions');
        const searchShell = root.querySelector('.rr-mobile-search-shell');

        mobileQuickNav.style.display = isMobile ? 'block' : 'none';
        mobileQuickNav.style.margin = isMobile ? '8px 10px 0' : '';
        mobileQuickNav.style.position = isMobile ? 'relative' : '';
        mobileQuickNav.style.zIndex = isMobile ? '15' : '';

        if (actions) {
            actions.style.display = isMobile ? 'grid' : '';
            actions.style.gridTemplateColumns = isMobile ? 'repeat(2, minmax(0, 1fr))' : '';
            actions.style.gap = isMobile ? '8px' : '';
        }

        mobileQuickNav.querySelectorAll('.rr-mobile-quick-nav__item').forEach((item) => {
            item.style.display = isMobile ? 'block' : '';
            item.style.position = isMobile ? 'relative' : '';
            item.style.minHeight = isMobile ? '46px' : '';
            item.style.borderRadius = isMobile ? '16px' : '';
            item.style.overflow = isMobile ? 'hidden' : '';
            item.style.gridColumn = isMobile && item.classList.contains('rr-mobile-quick-nav__item--stats') ? '1 / -1' : '';

            if (!isMobile) {
                item.style.background = '';
                item.style.boxShadow = '';
                return;
            }

            if (item.classList.contains('rr-mobile-quick-nav__item--x1')) {
                item.style.background = 'linear-gradient(180deg, #f8bb3b 0%, #f59e0be6 56%, #c2410c 100%)';
                item.style.boxShadow = '0 10px 22px rgba(245, 158, 11, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.42), inset 0 -3px 0 rgba(124, 45, 18, 0.34)';
            } else if (item.classList.contains('rr-mobile-quick-nav__item--bolao')) {
                item.style.background = 'linear-gradient(180deg, #4ade80 0%, #22c55e 58%, #15803d 100%)';
                item.style.boxShadow = '0 10px 22px rgba(34, 197, 94, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.32), inset 0 -3px 0 rgba(20, 83, 45, 0.36)';
            } else if (item.classList.contains('rr-mobile-quick-nav__item--stats')) {
                item.style.background = 'linear-gradient(180deg, #60a5fa 0%, #2563eb 58%, #1d4ed8 100%)';
                item.style.boxShadow = '0 10px 22px rgba(37, 99, 235, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.32), inset 0 -3px 0 rgba(30, 64, 175, 0.36)';
            }
        });

        mobileQuickNav.querySelectorAll('.rr-mobile-quick-nav__chrome').forEach((chrome) => {
            chrome.style.display = isMobile ? 'flex' : '';
            chrome.style.minHeight = isMobile ? '46px' : '';
        });

        if (searchShell) {
            searchShell.style.display = isMobile ? 'block' : '';
            searchShell.style.minHeight = isMobile ? '46px' : '';
        }
    }

    syncMobileQuickNavChrome();

    function bindMobileQuickScrollButtons() {
        if (!mobileQuickNav) return;

        mobileQuickNav.querySelectorAll('[data-scroll-target]').forEach((button) => {
            if (button.dataset.quickScrollBound === '1') return;
            button.dataset.quickScrollBound = '1';

            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                closeMobileQuickPanels();
                scrollToInicioQuickSection(button.getAttribute('data-scroll-target'));
            });
        });
    }

    bindMobileQuickScrollButtons();

    function getQuickFilterBucket(value) {
        const amount = Number(value || 0);
        if (!Number.isFinite(amount) || amount <= 0) return '';
        if (amount <= 20) return '20';
        if (amount <= 50) return '50';
        return '100';
    }

    function formatQuickFilterLabel(value) {
        if (!value || value === 'all') return '';
        return 'R$ ' + String(value);
    }

    function closeMobileQuickPanels() {
        activeQuickPanel = null;
        if (!mobileQuickNav) return;
        mobileQuickNav.querySelectorAll('[data-filter-panel]').forEach((panel) => {
            panel.hidden = true;
        });
        mobileQuickNav.querySelectorAll('[data-filter-popout]').forEach((button) => {
            button.classList.remove('is-open');
        });
    }

    function openMobileQuickPanel(type) {
        if (!mobileQuickNav) return;
        const nextType = String(type || '');
        if (!nextType) return;

        const isSameOpen = activeQuickPanel === nextType;
        closeMobileQuickPanels();
        if (isSameOpen) return;

        activeQuickPanel = nextType;
        const panel = mobileQuickNav.querySelector('[data-filter-panel="' + nextType + '"]');
        const trigger = mobileQuickNav.querySelector('[data-filter-popout="' + nextType + '"]');
        if (panel) panel.hidden = false;
        if (trigger) trigger.classList.add('is-open');
    }

    function syncMobileQuickOptionState() {
        if (!mobileQuickNav) return;

        mobileQuickNav.querySelectorAll('[data-filter-popout]').forEach((button) => {
            const type = button.getAttribute('data-filter-popout');
            const activeValue = mobileQuickFilterState[type];
            button.classList.toggle('is-active', !!activeValue);
        });

        mobileQuickNav.querySelectorAll('[data-filter-target]').forEach((option) => {
            const target = option.getAttribute('data-filter-target');
            const value = option.getAttribute('data-filter-value');
            option.classList.toggle('is-active', String(mobileQuickFilterState[target] || '') === String(value || ''));
        });
    }

    function getScrollTargetTop(element) {
        if (!element) return 0;
        const headerHeight = Math.ceil(document.getElementById('hubBrandOverlay')?.getBoundingClientRect().height || 0);
        return Math.max(0, window.scrollY + element.getBoundingClientRect().top - headerHeight - 10);
    }

    function scrollToInicioFilterTarget(sectionEl, gridSelector) {
        if (!sectionEl) return;
        const visibleCard = sectionEl.querySelector(gridSelector + ' > article:not([hidden])');
        const target = visibleCard || sectionEl;
        window.scrollTo({
            top: getScrollTargetTop(target),
            behavior: 'smooth',
        });
    }

    function scrollToInicioQuickSection(type) {
        const targetType = String(type || '');
        if (targetType === 'x1') {
            scrollToInicioFilterTarget(x1Section, '.rr-x1-room-grid');
            return true;
        }

        if (targetType === 'bolao') {
            scrollToInicioFilterTarget(bolaoSection, '.rr-bolao-grid');
            return true;
        }

        return false;
    }

    function syncMobileQuickEmptyState(grid, hasMatches, emptyText) {
        if (!grid) return;
        let emptyState = grid.querySelector('.rr-mobile-quick-empty');
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.className = 'rr-mobile-quick-empty';
            emptyState.hidden = true;
            grid.appendChild(emptyState);
        }

        emptyState.textContent = emptyText;
        emptyState.hidden = !!hasMatches;
    }

    function applyGridQuickFilter(gridId, value, emptyText) {
        const grid = document.getElementById(gridId);
        if (!grid) return;

        const cards = Array.from(grid.children).filter((child) => child.matches && child.matches('article'));
        let visibleCount = 0;

        cards.forEach((card) => {
            const bucket = String(card.dataset.quickFilterBucket || '');
            const shouldShow = !value || value === 'all' || bucket === String(value);
            card.hidden = !shouldShow;
            if (shouldShow) visibleCount += 1;
        });

        grid.scrollLeft = 0;
        syncMobileQuickEmptyState(grid, visibleCount > 0, emptyText);
    }

    function applyBolaoQuickPriority(gridId, value) {
        const grid = document.getElementById(gridId);
        if (!grid) return;

        const cards = Array.from(grid.children).filter((child) => child.matches && child.matches('article'));
        if (!cards.length) return;

        cards.forEach((card, index) => {
            if (!card.dataset.quickOrder) {
                card.dataset.quickOrder = String(index);
            }
            card.hidden = false;
        });

        const baseSort = (a, b) => Number(a.dataset.quickOrder || 0) - Number(b.dataset.quickOrder || 0);
        let orderedCards = [...cards].sort(baseSort);

        if (value && value !== 'all') {
            const matches = orderedCards.filter((card) => String(card.dataset.quickFilterBucket || '') === String(value));
            const others = orderedCards.filter((card) => String(card.dataset.quickFilterBucket || '') !== String(value));
            if (matches.length) {
                orderedCards = [...matches, ...others];
            }
        }

        orderedCards.forEach((card) => grid.appendChild(card));
        grid.scrollLeft = 0;
        syncMobileQuickEmptyState(grid, true, '');
    }

    function applyMobileQuickFilters() {
        document.querySelectorAll('.rr-x1-room-grid').forEach((grid) => {
            if (grid.id) {
                applyGridQuickFilter(
                    grid.id,
                    mobileQuickFilterState.x1,
                    formatQuickFilterLabel(mobileQuickFilterState.x1)
                        ? 'Nenhuma sala ' + formatQuickFilterLabel(mobileQuickFilterState.x1) + ' disponível agora.'
                        : 'Nenhuma sala disponível agora.'
                );
            }
        });
        document.querySelectorAll('.rr-bolao-grid').forEach((grid) => {
            if (grid.id) {
                applyBolaoQuickPriority(grid.id, mobileQuickFilterState.bolao);
            }
        });
        syncMobileQuickOptionState();
    }

    function refreshInicioModalidadeOptions() {
        if (!inicioModalidadeFilter) return;

        const activeRodeio = String(inicioRodeioFilter?.value || '');
        let hasSelectedOption = !inicioModalidadeFilter.value;

        Array.from(inicioModalidadeFilter.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const optionRodeio = String(option.dataset.rodeioId || '');
            const shouldShow = !activeRodeio || !optionRodeio || optionRodeio === activeRodeio;
            option.hidden = !shouldShow;
            option.disabled = !shouldShow;

            if (shouldShow && option.value === inicioModalidadeFilter.value) {
                hasSelectedOption = true;
            }
        });

        if (!hasSelectedOption) {
            inicioModalidadeFilter.value = '';
        }
    }

    function nodeMatchesInicioFilters(node, rodeioId, modalidadeId) {
        if (!node) return false;

        const nodeRodeioId = String(node.dataset.rodeioId || '');
        const nodeModalidadeId = String(node.dataset.modalidadeId || '');

        if (rodeioId && nodeRodeioId !== rodeioId) return false;
        if (modalidadeId && nodeModalidadeId !== modalidadeId) return false;
        return true;
    }

    function applyInicioSubmenuFilters() {
        const rodeioId = String(inicioRodeioFilter?.value || '');
        const modalidadeId = String(inicioModalidadeFilter?.value || '');

        let visibleArena = 0;
        let visibleBolao = 0;
        let visibleX1 = 0;

        arenaCatalogSections.forEach((section) => {
            const sectionMatches = nodeMatchesInicioFilters(section, rodeioId, modalidadeId) && nodeMatchesSearchContexts(section);
            let visibleCards = 0;

            Array.from(section.querySelectorAll('.rr-neuro-wrapper')).forEach((card) => {
                const shouldShowCard = sectionMatches && cardMatchesSearchSelection(card);
                card.style.display = shouldShowCard ? '' : 'none';
                if (shouldShowCard) visibleCards += 1;
            });

            section.style.display = visibleCards > 0 ? '' : 'none';
            if (visibleCards > 0) visibleArena += 1;
        });

        bolaoCatalogSections.forEach((section) => {
            const shouldShow = nodeMatchesInicioFilters(section, rodeioId, modalidadeId) && nodeMatchesSearchContexts(section);
            section.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleBolao += 1;
        });

        x1CatalogSections.forEach((section) => {
            const shouldShow = nodeMatchesInicioFilters(section, rodeioId, modalidadeId) && nodeMatchesSearchContexts(section);
            section.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleX1 += 1;
        });

        if (competitorTools) {
            competitorTools.style.display = '';
        }

        if (bolaoSection) {
            bolaoSection.style.display = visibleBolao === 0 ? 'none' : '';
        }

        if (x1Section) {
            x1Section.style.display = visibleX1 === 0 ? 'none' : '';
        }

        if (inicioFilterEmpty) {
            inicioFilterEmpty.style.display = (visibleArena + visibleBolao + visibleX1) > 0 ? 'none' : '';
        }

        syncMobileQuickNavChrome();
    }

    if (inicioRodeioFilter) {
        inicioRodeioFilter.addEventListener('change', () => {
            refreshInicioModalidadeOptions();
            applyInicioSubmenuFilters();
        });
    }

    if (inicioModalidadeFilter) {
        inicioModalidadeFilter.addEventListener('change', applyInicioSubmenuFilters);
    }

    refreshInicioModalidadeOptions();
    if (inicioRodeioFilter && !inicioRodeioFilter.value) {
        inicioRodeioFilter.value = String(window.RR_HUB_CONTEXT?.rodeio_id || '');
        refreshInicioModalidadeOptions();
    }
    if (inicioModalidadeFilter && !inicioModalidadeFilter.value) {
        inicioModalidadeFilter.value = String(window.RR_HUB_CONTEXT?.modalidade_id || '');
    }
    applyInicioSubmenuFilters();
    
    // Verificar se todos elementos críticos existem
    if (!slip || !amountSection || !matchesSection || !loadingEl) {
        console.error('RR Início: Elementos críticos do bilhete não encontrados');
        return;
    }
    
    // Garantir que tudo está escondido na inicialização
    if (slip) {
        slip.hidden = true;
        slip.style.display = 'none';
    }
    if (modal) {
        modal.hidden = true;
        modal.style.display = 'none';
    }
    if (draftPixModal) {
        draftPixModal.hidden = true;
        draftPixModal.style.display = 'none';
    }
    if (customModal) {
        customModal.hidden = true;
        customModal.style.display = 'none';
    }
    if (competitorSearchModal) {
        competitorSearchModal.hidden = true;
        document.body.appendChild(competitorSearchModal);
    }
    if (groupMembersModal) {
        groupMembersModal.hidden = true;
        document.body.appendChild(groupMembersModal);
    }
    const initialHeroItems = Array.isArray(window.RR_INICIO_HERO_ITEMS) ? window.RR_INICIO_HERO_ITEMS : [];
    if (initialHeroItems.length) {
        applyInicioHeroState({
            rodeio_id: initialHeroItems[0]?.rodeio_id || 0,
            title: initialHeroItems[0]?.title || 'Rei do Rodeio',
            logo_url: initialHeroItems[0]?.logo_url || '',
            timer_iso: initialHeroItems[0]?.timer_iso || null,
            mode: initialHeroItems[0]?.mode || 'scheduled',
            badge: initialHeroItems[0]?.badge || 'Programado',
            accent: initialHeroItems[0]?.accent || 'Próximo evento',
            label: initialHeroItems[0]?.label || 'Começa em',
            status_transmissao: initialHeroItems[0]?.status_transmissao || '',
            items: initialHeroItems
        });
    }
    const eventLightbox = root.querySelector('#rrInicioEventLightbox');
    const eventLightboxClose = root.querySelector('#rrInicioEventLightboxClose');
    const closeInicioHeroLightbox = function () {
        const img = root.querySelector('#rrInicioEventLightboxImg');
        if (eventLightbox) eventLightbox.hidden = true;
        if (img) img.src = '';
    };
    if (eventLightboxClose) {
        eventLightboxClose.addEventListener('click', closeInicioHeroLightbox);
    }
    if (eventLightbox) {
        eventLightbox.addEventListener('click', function (event) {
            if (event.target === eventLightbox) {
                closeInicioHeroLightbox();
            }
        });
    }
    document.body.style.overflow = '';
    updateInicioHeroTimer();
    if (window.__rrInicioHeroTimerInterval) {
        window.clearInterval(window.__rrInicioHeroTimerInterval);
    }
    window.__rrInicioHeroTimerInterval = window.setInterval(updateInicioHeroTimer, 60000);
    syncReminderTrigger();
    refreshInicioHeroState();
    if (window.__rrInicioHeroStateInterval) {
        window.clearInterval(window.__rrInicioHeroStateInterval);
    }
    window.__rrInicioHeroStateInterval = window.setInterval(refreshInicioHeroState, 30000);
    
    console.log('RR Início: Betslip inicializado', {
        slip: !!slip,
        modal: !!modal,
        customModal: !!customModal
    });

    const inicioScrollLock = {
        count: 0,
        y: 0,
        bodyPrev: null,
        htmlPrev: null,
    };

    function lockInicioBackgroundScroll(owner) {
        if (!owner || owner.dataset.rrScrollLocked === '1') return;

        owner.dataset.rrScrollLocked = '1';

        if (inicioScrollLock.count === 0) {
            const y = window.scrollY || document.documentElement.scrollTop || 0;
            inicioScrollLock.y = y;
            inicioScrollLock.bodyPrev = {
                position: document.body.style.position,
                top: document.body.style.top,
                left: document.body.style.left,
                right: document.body.style.right,
                width: document.body.style.width,
                overflow: document.body.style.overflow,
            };
            inicioScrollLock.htmlPrev = {
                overflow: document.documentElement.style.overflow,
                overflowY: document.documentElement.style.overflowY,
            };

            document.body.classList.add('rr-inicio-modal-open');
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = (-y) + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.documentElement.style.overflow = 'hidden';
            document.documentElement.style.overflowY = 'hidden';
        }

        inicioScrollLock.count += 1;
    }

    function unlockInicioBackgroundScroll(owner) {
        if (!owner || owner.dataset.rrScrollLocked !== '1') return;

        delete owner.dataset.rrScrollLocked;
        inicioScrollLock.count = Math.max(0, inicioScrollLock.count - 1);

        if (inicioScrollLock.count > 0) {
            return;
        }

        const bodyPrev = inicioScrollLock.bodyPrev || {};
        const htmlPrev = inicioScrollLock.htmlPrev || {};
        const y = inicioScrollLock.y || 0;

        document.body.classList.remove('rr-inicio-modal-open');
        document.body.style.position = bodyPrev.position || '';
        document.body.style.top = bodyPrev.top || '';
        document.body.style.left = bodyPrev.left || '';
        document.body.style.right = bodyPrev.right || '';
        document.body.style.width = bodyPrev.width || '';
        document.body.style.overflow = bodyPrev.overflow || '';
        document.documentElement.style.overflow = htmlPrev.overflow || '';
        document.documentElement.style.overflowY = htmlPrev.overflowY || '';

        inicioScrollLock.bodyPrev = null;
        inicioScrollLock.htmlPrev = null;
        inicioScrollLock.y = 0;

        window.scrollTo(0, y);
    }

    function showPixGenerationOverlay(title, copy) {
        if (!pixGenerationOverlay) return;

        if (pixGenerationTitle) {
            pixGenerationTitle.textContent = title || 'Carregando PIX';
        }
        if (pixGenerationCopy) {
            pixGenerationCopy.textContent = copy || 'Gerando o QR code para abrir em seguida.';
        }

        if (!pixGenerationOverlay.hidden) {
            return;
        }

        pixGenerationOverlay.hidden = false;
        pixGenerationOverlay.style.display = '';
        lockInicioBackgroundScroll(pixGenerationOverlay);
    }

    function hidePixGenerationOverlay() {
        if (!pixGenerationOverlay || pixGenerationOverlay.hidden) return;
        pixGenerationOverlay.hidden = true;
        pixGenerationOverlay.style.display = 'none';
        unlockInicioBackgroundScroll(pixGenerationOverlay);
    }

    let selectedCard = null;
    let selectedAmount = null;
    let currentMultiplier = 1.90;
    let currentMatches = [];
    let activePreferenceId = null;
    let pollTimer = null;
    let draftActivePreferenceId = null;
    let draftPollTimer = null;
    let draftPayInFlight = false;
    let draftPayCooldownUntil = 0;
    let draftSuccessTimer = null;
    let draftReturnStatusCheckAt = 0;
    let draftModalHomeParent = null;
    let draftModalHomeNextSibling = null;

    function setDraftHint(message, tone) {
        const normalizedMessage = String(message || '');
        const normalizedTone = String(tone || '');
        const shouldPinCtaMessage = normalizedTone !== '';
        const currentState = window.draftState;

        const hintEl = document.getElementById('rrDraftLeagueHint');
        if (hintEl) {
            hintEl.textContent = normalizedMessage;
            hintEl.dataset.tone = normalizedTone;
        }

        const ctaHintEl = document.getElementById('rrDraftCtaHint');
        if (ctaHintEl) {
            if (shouldPinCtaMessage) {
                ctaHintEl.textContent = normalizedMessage;
            } else if (currentState) {
                const missing = Math.max(0, currentState.maxTeamSize - currentState.selectedCompetitors.length);
                ctaHintEl.textContent = missing === 0
                    ? 'Toque em entrar no bolão para confirmar.'
                    : `Escolha ${missing} ${missing === 1 ? 'nome' : 'nomes'} para liberar.`;
            }
            ctaHintEl.dataset.tone = normalizedTone;
            ctaHintEl.dataset.visible = shouldPinCtaMessage ? '1' : '0';
            ctaHintEl.dataset.pinned = shouldPinCtaMessage ? '1' : '0';
            ctaHintEl.dataset.source = shouldPinCtaMessage ? 'hint' : '';
        }
    }

    function setDraftText(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value ?? '';
        }
    }

    function getDraftEntryLabel(league) {
        const entryValue = parseFloat(league?.price || league?.entry_price || 0) || 0;
        return league?.is_premium ? 'Premium' : (entryValue > 0 ? formatBRL(entryValue) : 'Grátis');
    }

    function getLeagueMaxUsers(league) {
        const parsed = parseInt(
            league?.max_users
            ?? league?.max_players
            ?? league?.max_entries
            ?? league?.maxParticipants
            ?? 0,
            10
        );

        return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
    }

    function isLeagueUnlimited(league) {
        if (!league || typeof league !== 'object' || league.is_premium) {
            return false;
        }

        const explicitFlag = [
            league.is_unlimited,
            league.unlimited,
            league.has_unlimited_entries,
            league.unlimited_entries,
        ].some((value) => value === true || value === 1 || value === '1');

        return explicitFlag || getLeagueMaxUsers(league) <= 0;
    }

    function getLeagueTeamsCount(league) {
        const parsed = parseInt(league?.teams_count ?? league?.total_teams ?? 0, 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
    }

    function getLeagueTeamsLabel(league, options = {}) {
        const totalTeams = getLeagueTeamsCount(league);
        const maxUsers = getLeagueMaxUsers(league);
        const arenaSuffix = options.arena === true ? ' equipes na arena' : ' na arena';

        if (isLeagueUnlimited(league)) {
            return `${totalTeams}${arenaSuffix}`;
        }

        return `${totalTeams}${maxUsers ? '/' + maxUsers : ''}`;
    }

    function getLeagueCapacityLabel(league) {
        const totalTeams = getLeagueTeamsCount(league);
        if (isLeagueUnlimited(league)) {
            return `${totalTeams}/Ilimitado`;
        }

        const maxUsers = getLeagueMaxUsers(league);
        return `${totalTeams}/${maxUsers || 0}`;
    }

    function getLeagueExpansionCopy(visiblePaidPositions, projectedPaidPositions, options = {}) {
        const currentTop = Math.max(parseInt(visiblePaidPositions || 0, 10) || 0, 3);
        const projectedTop = Math.max(parseInt(projectedPaidPositions || 0, 10) || 0, currentTop);
        const shortMode = options.short === true;

        if (projectedTop > currentTop) {
            return shortMode
                ? `Pode abrir até Top ${projectedTop}`
                : `Zona paga atual no Top ${currentTop}. Pode abrir até Top ${projectedTop}.`;
        }

        return shortMode
            ? `Top ${currentTop} pagando`
            : `Top ${currentTop} pagando agora. O prêmio segue acumulando.`;
    }

    function getBolaoLaunchPrizeAmount(league) {
        if (!league || typeof league !== 'object') {
            return null;
        }

        if ((league.scoring_mode || '').toLowerCase() === 'points') {
            return null;
        }

        const explicitPrize = parseFloat(
            league.total_prize
            || league.prize_pool
            || (league.is_premium ? league.manual_prize_pool : 0)
            || 0
        );

        if (Number.isFinite(explicitPrize) && explicitPrize > 0) {
            return explicitPrize;
        }

        const entryPrice = parseFloat(league.price || league.entry_price || 0);
        if (!(entryPrice > 0)) {
            return 0;
        }

        const participantCount = isLeagueUnlimited(league)
            ? Math.max(getLeagueTeamsCount(league), 0)
            : getLeagueMaxUsers(league);

        if (!(participantCount > 0)) {
            return 0;
        }

        const totalPool = entryPrice * participantCount;
        const houseCutPercent = parseFloat(
            league.house_cut_percent
            ?? league.house_cut
            ?? league.tax_percent
            ?? 20
        );
        const safeHouseCut = Number.isFinite(houseCutPercent) ? houseCutPercent : 20;

        return Math.max(0, totalPool - (totalPool * safeHouseCut / 100));
    }

    function renderBolaoLaunchPrizeOdometer(target, amount) {
        if (!target) return;

        const safeAmount = Number.isFinite(amount) ? Math.max(0, amount) : 0;
        const formatted = formatBRL(safeAmount);
        const digitMarkup = Array.from({ length: 10 }, (_, digit) => `<span>${digit}</span>`).join('');

        target.classList.add('has-odometer');
        target.innerHTML = Array.from(formatted).map((char) => {
            if (/\d/.test(char)) {
                return `<span class="rr-bolao-launch-simple__odometer-digit"><span class="rr-bolao-launch-simple__odometer-track" data-digit="${char}" style="--digit:0">${digitMarkup}</span></span>`;
            }

            const symbolClass = /[A-Z$]/.test(char)
                ? 'rr-bolao-launch-simple__price-symbol rr-bolao-launch-simple__price-symbol--currency'
                : 'rr-bolao-launch-simple__price-symbol rr-bolao-launch-simple__price-symbol--separator';
            const safeChar = char === ' ' ? '&nbsp;' : char;

            return `<span class="${symbolClass}">${safeChar}</span>`;
        }).join('');

        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => {
                target.querySelectorAll('.rr-bolao-launch-simple__odometer-track').forEach((track) => {
                    const digit = parseInt(track.getAttribute('data-digit') || '0', 10) || 0;
                    track.style.setProperty('--digit', String(digit));
                });
            });
        });
    }

    function getDraftDefaultHint(league) {
        if (isLeagueUnlimited(league)) {
            return 'Prêmio acumulando e faixa paga expandindo a cada nova equipe.';
        }

        return 'Monte 4 nomes e confirme.';
    }

    function getBolaoLaunchContextLabel(league) {
        const hubContext = window.RR_HUB_CONTEXT || {};
        const rodeioName = String(
            league?.rodeio?.nome
            || league?.rodeio_nome
            || hubContext.rodeio_nome
            || league?.rodeioName
            || league?.event_name
            || ''
        ).trim();
        const modalidadeName = String(
            league?.modalidade?.nome
            || league?.modalidade_nome
            || hubContext.modalidade_nome
            || league?.modalidadeName
            || ''
        ).trim();

        if (rodeioName && modalidadeName) {
            return `${rodeioName} • ${modalidadeName}`;
        }

        return rodeioName || modalidadeName || 'Bolão aberto';
    }

    function getBolaoLaunchContextParts(league) {
        const hubContext = window.RR_HUB_CONTEXT || {};
        const rodeioName = String(
            league?.rodeio?.nome
            || league?.rodeio_nome
            || hubContext.rodeio_nome
            || league?.rodeioName
            || league?.event_name
            || ''
        ).trim();
        const modalidadeName = String(
            league?.modalidade?.nome
            || league?.modalidade_nome
            || hubContext.modalidade_nome
            || league?.modalidadeName
            || ''
        ).trim();

        return {
            rodeioName,
            modalidadeName,
            combined: getBolaoLaunchContextLabel(league),
        };
    }

    function getBolaoLaunchLogoUrl(league) {
        const fallbackLogo = '{{ asset("assets/images/logo_icon/logo.png") }}';
        return String(
            league?.rodeio?.foto
            || league?.rodeio?.logo_url
            || league?.rodeio?.logo
            || league?.rodeio_logo
            || league?.logo_url
            || fallbackLogo
        ).trim() || fallbackLogo;
    }

    function getDraftPrizeLabel(league) {
        const cachedPrize = parseFloat(window.currentDraftPrizePool || 0) || 0;
        if (cachedPrize > 0) {
            return formatBRL(cachedPrize);
        }

        if (league?.is_premium && league?.manual_prize_pool) {
            return formatBRL(parseFloat(league.manual_prize_pool));
        }

        if (league?.is_premium) {
            return 'Pontos';
        }

        if (league?.total_prize && parseFloat(league.total_prize) > 0) {
            return formatBRL(parseFloat(league.total_prize));
        }

        const price = parseFloat(league?.price || league?.entry_price || 0) || 0;
        const houseCut = parseFloat(league?.house_cut_percent || league?.house_cut || 0) || 0;
        const metaParticipants = league?.max_users ? parseInt(league.max_users, 10) : 200;
        const totalPool = metaParticipants * price;
        const houseTake = totalPool * (houseCut / 100);

        return formatBRL(Math.max(0, totalPool - houseTake));
    }

    function getDraftDeadlineLabel(league) {
        const deadlineMs = getLeagueDeadlineMs(league);
        if (!Number.isFinite(deadlineMs)) {
            return 'Sem limite';
        }

        return formatCountdown(deadlineMs - Date.now());
    }

    function getDraftShortName(fullName) {
        const rawName = String(fullName || 'Competidor').trim();
        const nameParts = rawName.split(/\s+/).filter(Boolean);

        if (!nameParts.length) {
            return 'Competidor';
        }

        if (nameParts.length === 1) {
            return nameParts[0];
        }

        if (nameParts.length === 2) {
            let firstName = nameParts[0];
            let secondName = nameParts[1];
            const firstNameIsLarge = firstName.length >= 10;

            if (firstName.length > 12) {
                firstName = `${firstName.slice(0, 11)}.`;
            }

            const joinedLength = firstName.length + secondName.length;
            if (firstNameIsLarge || secondName.length > 8 || joinedLength > 16) {
                secondName = `${secondName.charAt(0).toUpperCase()}.`;
            }

            return `${firstName} ${secondName}`.trim();
        }

        const firstName = nameParts.shift();
        const lastName = nameParts.pop();
        const middleInitials = nameParts.map((part) => `${part.charAt(0).toUpperCase()}.`);

        return [firstName, ...middleInitials, lastName].filter(Boolean).join(' ').trim();
    }

    function getDraftFirstName(fullName) {
        const firstName = String(fullName || 'Competidor').trim().split(/\s+/).filter(Boolean)[0] || 'Competidor';
        return firstName.length > 12 ? `${firstName.slice(0, 11)}.` : firstName;
    }

    function getDraftPhotoUrl(entry) {
        return '{{ asset("assets/images/logo_icon/logo.png") }}';
    }

    function updateDraftLeagueSummary(league) {
        if (!league) return;

        const entryLabel = getDraftEntryLabel(league);
        const prizeLabel = getDraftPrizeLabel(league);
        const teamsLabel = getLeagueTeamsLabel(league);
        const deadlineLabel = getDraftDeadlineLabel(league);
        const unlimited = isLeagueUnlimited(league);

        setDraftText(
            'rrDraftLeagueInfo',
            unlimited
                ? `Entrada ${entryLabel} • ${getLeagueTeamsLabel(league, { arena: true })} • prêmio acumula sem parar`
                : `Entrada ${entryLabel} • ${teamsLabel} equipes na arena • prazo ${deadlineLabel}`
        );
        setDraftText('rrDraftSummaryEntry', entryLabel);
        setDraftText('rrDraftSummaryPrize', prizeLabel);
        setDraftText('rrDraftSummaryTeams', teamsLabel);
        setDraftText('rrDraftSummaryDeadline', deadlineLabel);
        setDraftText('rrDraftToolbarEntry', entryLabel);
        setDraftText('rrDraftCapacityValue', getLeagueCapacityLabel(league));
    }

    function getDraftDuplicateCheck(state) {
        const selectedIds = Array.isArray(state?.selectedCompetitors)
            ? state.selectedCompetitors
                .map((competitor) => parseInt(competitor?.id, 10))
                .filter((id) => Number.isFinite(id) && id > 0)
            : [];
        const existingTeams = Array.isArray(state?.existingTeams) ? state.existingTeams : [];
        let maxOverlap = 0;

        existingTeams.forEach((team) => {
            const teamIds = Array.isArray(team?.competitor_ids)
                ? team.competitor_ids
                    .map((id) => parseInt(id, 10))
                    .filter((id) => Number.isFinite(id) && id > 0)
                : [];
            const overlapCount = selectedIds.filter((id) => teamIds.includes(id)).length;
            if (overlapCount > maxOverlap) {
                maxOverlap = overlapCount;
            }
        });

        const selectedCount = selectedIds.length;
        const maxTeamSize = Number(state?.maxTeamSize || 4);
        const differentStillNeeded = Math.max(0, maxTeamSize - selectedCount);
        const hasAnyRepeat = maxOverlap >= 1;
        const hardBlocked = maxOverlap >= 2;
        const softLocked = hasAnyRepeat && selectedCount < maxTeamSize;

        return {
            selectedCount,
            maxOverlap,
            hasAnyRepeat,
            hardBlocked,
            softLocked,
            isLocked: hardBlocked || softLocked,
            differentStillNeeded,
            hint: hardBlocked
                ? 'Equipe repetida, troque pelo menos 1 nome'
                : hasAnyRepeat
                    ? `Equipe repetida, selecione ${differentStillNeeded} diferentes`
                    : '',
        };
    }

    function renderDraftPayButtonState({ label, sublabel = '', icon = 'fa-bolt', locked = false }) {
        const safeLabel = escapeHtml(label || 'Entrar no bolão');
        const safeSub = escapeHtml(sublabel || '');
        const safeIcon = escapeHtml(icon || 'fa-bolt');
        return `
            <span class="rr-draft-pay-btn__stack">
                <span class="rr-draft-pay-btn__icon"><i class="fas ${safeIcon}"></i></span>
                <span class="rr-draft-pay-btn__copy">
                    <span class="rr-draft-pay-btn__label">${safeLabel}</span>
                    ${safeSub ? `<span class="rr-draft-pay-btn__sub">${safeSub}</span>` : ''}
                </span>
            </span>
        `;
    }

    function updateDraftActionState() {
        const state = window.draftState;
        if (!state) return;

        const missing = Math.max(0, state.maxTeamSize - state.selectedCompetitors.length);
        const ready = missing === 0;
        const duplicateCheck = getDraftDuplicateCheck(state);
        const selectionLabel = ready
            ? 'Equipe pronta'
            : `Faltam ${missing} ${missing === 1 ? 'competidor' : 'competidores'}`;
        const actionCopy = ready
            ? 'Toque em entrar no bolão para confirmar.'
            : `Escolha ${missing} ${missing === 1 ? 'nome' : 'nomes'} para liberar.`;

        setDraftText('rrDraftSelectionStatus', selectionLabel);
        const ctaHintEl = document.getElementById('rrDraftCtaHint');
        if (duplicateCheck.isLocked && ctaHintEl) {
            ctaHintEl.textContent = duplicateCheck.hint;
            ctaHintEl.dataset.tone = duplicateCheck.hardBlocked ? 'error' : 'warn';
            ctaHintEl.dataset.visible = '1';
            ctaHintEl.dataset.pinned = '1';
            ctaHintEl.dataset.source = 'duplicate';
        } else if (ctaHintEl?.dataset.source === 'duplicate') {
            setDraftText('rrDraftCtaHint', actionCopy);
            ctaHintEl.dataset.tone = '';
            ctaHintEl.dataset.visible = '0';
            ctaHintEl.dataset.pinned = '0';
            ctaHintEl.dataset.source = '';
        } else if (ctaHintEl?.dataset.pinned === '1') {
            ctaHintEl.dataset.visible = '1';
        } else {
            setDraftText('rrDraftCtaHint', actionCopy);
            if (ctaHintEl) {
                ctaHintEl.dataset.tone = '';
                ctaHintEl.dataset.visible = '0';
                ctaHintEl.dataset.pinned = '0';
                ctaHintEl.dataset.source = '';
            }
        }

        document.querySelectorAll('#rrDraftPayBtn').forEach((payBtn) => {
            if (draftPayInFlight) return;
            const buttonLocked = duplicateCheck.isLocked;
            const buttonDisabled = !ready || buttonLocked;
            payBtn.disabled = buttonDisabled;
            payBtn.classList.toggle('is-disabled', buttonDisabled);
            payBtn.classList.toggle('rr-draft-pay-btn--locked', buttonLocked);
            payBtn.innerHTML = buttonLocked
                ? renderDraftPayButtonState({
                    label: 'Equipe repetida',
                    sublabel: duplicateCheck.hardBlocked
                        ? 'Troque pelo menos 1 nome'
                        : `Selecione ${duplicateCheck.differentStillNeeded} diferentes`,
                    icon: 'fa-lock',
                    locked: true,
                })
                : renderDraftPayButtonState({
                    label: ready ? 'Entrar no bolão' : `Faltam ${missing}`,
                    sublabel: ready ? 'Equipe pronta para confirmar' : 'Complete os 4 nomes',
                    icon: ready ? 'fa-bolt' : 'fa-users',
                });
        });
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function formatBRL(value) {
        const n = Number(value || 0);
        return n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getCardEntryType(card) {
        return String(card?.dataset?.entryType || entryMode || 'competitor').toLowerCase();
    }

    function getCardEntryName(card) {
        if (getCardEntryType(card) === 'group') {
            return String(card?.dataset?.captainName || card?.dataset?.entryName || 'Capitão').trim();
        }
        return String(card?.dataset?.entryName || card?.dataset?.competitorName || entryLabelCapitalized).trim();
    }

    function getRoomCaptainName(group) {
        if (group && Array.isArray(group.members) && group.members.length > 0) {
            return String(group.members[0]?.nome || '').trim();
        }
        return String(group?.nome || '').trim();
    }

    function getCardModalidadeName(card) {
        return String(card?.dataset?.modalidadeName || '').trim();
    }

    function getCardCompetitorId(card) {
        return Number(card?.dataset?.competitorId || 0);
    }

    function getCardGroupId(card) {
        return Number(card?.dataset?.groupId || card?.dataset?.entryId || 0);
    }

    function getStakeValue() {
        return _stakeAmount || 0;
    }

    const RR_INICIO_MIN_STAKE = {{ number_format($x1MinEntry, 2, '.', '') }};
    const RR_INICIO_CUSTOM_MIN_STAKE = {{ number_format($x1CustomMinEntry, 2, '.', '') }};
    const RR_INICIO_MAX_STAKE = {{ number_format($x1MaxEntry, 2, '.', '') }};
    var _stakeAmount = 0;

    function setPlaceBtnLabel(label) {
        if (placeBtnText) {
            placeBtnText.textContent = label || 'Continuar';
        }
    }

    function setPlaceBtnProcessingLabel(label) {
        if (placeBtnProcessingText) {
            placeBtnProcessingText.textContent = label || 'Processando...';
        }
    }

    function setSlipState(state, message) {
        const amountSelected = getStakeValue() >= RR_INICIO_MIN_STAKE;

        switch (state) {
            case 'selected':
                if (statusKickerEl) statusKickerEl.textContent = 'Passo 2';
                if (statusTitleEl) statusTitleEl.textContent = 'Valor definido. Continuar?';
                if (statusTextEl) statusTextEl.textContent = message || 'Buscamos uma sala igual ou criamos uma nova.';
                if (actionHintEl) actionHintEl.textContent = 'Buscar sala ou criar.';
                setPlaceBtnLabel('Buscar sala ou criar');
                break;
            case 'searching':
                if (statusKickerEl) statusKickerEl.textContent = 'Buscando';
                if (statusTitleEl) statusTitleEl.textContent = 'Procurando sala disponível';
                if (statusTextEl) statusTextEl.textContent = message || 'Localizando uma sala compatível.';
                if (actionHintEl) actionHintEl.textContent = 'Aguarde um instante.';
                setPlaceBtnProcessingLabel('Buscando sala...');
                break;
            case 'creating':
                if (statusKickerEl) statusKickerEl.textContent = 'Criando';
                if (statusTitleEl) statusTitleEl.textContent = 'Criando sua sala';
                if (statusTextEl) statusTextEl.textContent = message || 'Nenhuma sala aberta encontrada.';
                if (actionHintEl) actionHintEl.textContent = 'Abrindo nova sala.';
                setPlaceBtnProcessingLabel('Criando sala...');
                break;
            case 'pix':
                if (statusKickerEl) statusKickerEl.textContent = 'PIX pronto';
                if (statusTitleEl) statusTitleEl.textContent = 'Pague o PIX para entrar';
                if (statusTextEl) statusTextEl.textContent = message || 'Abra o QR Code e confirme o pagamento.';
                if (actionHintEl) actionHintEl.textContent = 'Aguardando PIX.';
                setPlaceBtnLabel('Aguardando PIX');
                break;
            case 'error':
                if (statusKickerEl) statusKickerEl.textContent = 'Atenção';
                if (statusTitleEl) statusTitleEl.textContent = 'Não foi possível continuar';
                if (statusTextEl) statusTextEl.textContent = message || 'Revise o valor e tente novamente.';
                if (actionHintEl) actionHintEl.textContent = 'Tente novamente.';
                setPlaceBtnLabel(amountSelected ? 'Tentar novamente' : 'Escolha um valor');
                break;
            case 'idle':
            default:
                if (statusKickerEl) statusKickerEl.textContent = 'Passo 1';
                if (statusTitleEl) statusTitleEl.textContent = 'Escolha sua entrada';
                if (statusTextEl) statusTextEl.textContent = message || 'Depois disso, buscamos ou criamos uma sala.';
                if (actionHintEl) actionHintEl.textContent = 'Escolha um valor.';
                setPlaceBtnLabel(amountSelected ? 'Buscar sala ou criar' : 'Escolha um valor');
                break;
        }
    }

    function setStakeValue(val) {
        _stakeAmount = val > 0 ? val : 0;
        updateReturn();
        // Highlight active button
        if (slip) {
            slip.querySelectorAll('.rr-inicio-slip__stake').forEach(function(btn) {
                var bv = btn.dataset.value;
                if (bv !== 'custom') {
                    btn.classList.toggle('is-active', Number(bv) === _stakeAmount);
                }
            });
        }
    }

    function updateReturn() {
        const stake = getStakeValue();
        const potential = stake * currentMultiplier;
        if (returnDisplay) {
            returnDisplay.textContent = stake > 0 ? formatBRL(potential) : 'R$0,00';
        }
        if (returnFooterDisplay) {
            returnFooterDisplay.textContent = stake > 0 ? formatBRL(potential) : 'R$0,00';
        }
        if (stakePreviewEl) {
            stakePreviewEl.textContent = stake > 0 ? formatBRL(stake) : 'Escolha';
        }
        updateCustomAmountPreview();
        // Habilitar/desabilitar botão
        if (placeBtn) {
            placeBtn.disabled = stake < RR_INICIO_MIN_STAKE;
        }
        if (!placeBtnProcessing || placeBtnProcessing.hidden) {
            setSlipState(stake >= RR_INICIO_MIN_STAKE ? 'selected' : 'idle');
        }
    }

    function syncCustomQuickButtons(amount) {
        if (!customModal) return;
        customModal.querySelectorAll('[data-custom-value]').forEach((button) => {
            button.classList.toggle('is-active', Number(button.dataset.customValue || 0) === amount);
        });
    }

    function updateCustomAmountPreview() {
        const amount = parseFloat(customInput?.value || '');
        const isValidAmount = !isNaN(amount) && amount >= RR_INICIO_CUSTOM_MIN_STAKE;
        const potential = isValidAmount ? amount * currentMultiplier : 0;

        if (customReturnEl) {
            customReturnEl.textContent = potential > 0 ? formatBRL(potential) : 'R$0,00';
        }

        syncCustomQuickButtons(isValidAmount ? amount : 0);
    }

    function renderCustomRoomOptions(rooms, minCustomAmount) {
        if (!customMatches) return;

        const limitLabel = formatBRL(minCustomAmount || 100);
        const roomListWrapClass = rooms.length > 1
            ? 'rr-inicio-custom-modal__room-list-wrap rr-inicio-custom-modal__room-list-wrap--scroll'
            : 'rr-inicio-custom-modal__room-list-wrap';
        const roomListClass = rooms.length > 1
            ? 'rr-inicio-custom-modal__room-list rr-inicio-custom-modal__room-list--scroll'
            : 'rr-inicio-custom-modal__room-list';

        if (!rooms.length) {
            customMatches.innerHTML = ''
                + '<div class="rr-inicio-custom-modal__note">'
                + '<span class="rr-inicio-custom-modal__note-title">Nenhuma sala acima de ' + escapeHtml(limitLabel) + '</span>'
                + '<span class="rr-inicio-custom-modal__note-copy">Digite um valor maior para criar uma nova sala e gerar o PIX na sequencia.</span>'
                + '</div>';
            return;
        }

        customMatches.innerHTML = ''
            + '<div class="rr-inicio-custom-modal__note">'
            + '<span class="rr-inicio-custom-modal__note-title">Salas abertas acima de ' + escapeHtml(limitLabel) + '</span>'
            + '<span class="rr-inicio-custom-modal__note-copy">Entre direto em uma sala ja aberta ou digite um valor maior para criar a sua.</span>'
            + '</div>'
            + '<div class="' + roomListWrapClass + '">'
            + '<div class="' + roomListClass + '">'
            + rooms.map((room) => {
                const roomId = Number(room?.id || 0);
                const entryFormatted = escapeHtml(room?.valor_entrada_formatted || formatBRL(room?.valor_entrada || 0));
                const entryAmount = Number(room?.valor_entrada || 0).toFixed(2);
                const roomName = escapeHtml(room?.competitor_name || 'Sala aberta');
                const prizeFormatted = escapeHtml(room?.prize_total_formatted || formatBRL(room?.prize_total || 0));
                const waiting = escapeHtml(room?.waiting_time || '');
                const premiumBadge = room?.host_is_premium
                    ? '<span class="rr-inicio-custom-modal__room-badge">Premium</span>'
                    : '';

                return ''
                    + '<button type="button" class="rr-inicio-custom-modal__room-card" data-custom-room-id="' + roomId + '" data-custom-room-value="' + entryAmount + '">'
                    + '  <div class="rr-inicio-custom-modal__room-head">'
                    + '      <span class="rr-inicio-custom-modal__room-entry">' + entryFormatted + '</span>'
                    +        premiumBadge
                    + '  </div>'
                    + '  <div class="rr-inicio-custom-modal__room-name">' + roomName + '</div>'
                    + '  <div class="rr-inicio-custom-modal__room-meta">'
                    + '      <span class="rr-inicio-custom-modal__room-prize">Premio ' + prizeFormatted + '</span>'
                    + '      <span class="rr-inicio-custom-modal__room-wait">' + waiting + '</span>'
                    + '  </div>'
                    + '  <span class="rr-inicio-custom-modal__room-cta">Entrar nessa sala</span>'
                    + '</button>';
            }).join('')
            + '</div>'
            + '</div>';
    }

    async function loadCustomRoomOptions() {
        if (!selectedCard || !customMatches) return;

        const modalidadeId = Number(selectedCard.dataset.modalidadeId || 0);
        const rodeioId = Number(selectedCard.dataset.rodeioId || 0);
        const competitorId = getCardCompetitorId(selectedCard);
        const competitorGroupId = getCardGroupId(selectedCard);
        const entryType = getCardEntryType(selectedCard);
        const divisao = selectedCard.dataset.divisao || null;

        if (!modalidadeId || (entryType === 'group' ? !competitorGroupId : !competitorId)) {
            renderCustomRoomOptions([], 100);
            return;
        }

        customMatches.innerHTML = ''
            + '<div class="rr-inicio-custom-modal__note">'
            + '<span class="rr-inicio-custom-modal__note-title">Carregando salas acima de ' + escapeHtml(formatBRL(100)) + '</span>'
            + '<span class="rr-inicio-custom-modal__note-copy">Estamos buscando salas abertas maiores para voce entrar direto.</span>'
            + '</div>';

        try {
            const response = await fetch('/api/x1/custom-rooms', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    competitor_id: entryType === 'group' ? null : competitorId,
                    competitor_group_id: entryType === 'group' ? competitorGroupId : null,
                    rodeio_id: rodeioId || null,
                    modalidade_id: modalidadeId,
                    divisao: divisao || null,
                    min_custom_amount: 100,
                }),
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Nao foi possivel carregar as salas abertas.');
            }

            renderCustomRoomOptions(data.rooms || [], Number(data.min_custom_amount || 100));
        } catch (error) {
            customMatches.innerHTML = ''
                + '<div class="rr-inicio-custom-modal__note">'
                + '<span class="rr-inicio-custom-modal__note-title">Nao foi possivel carregar as salas abertas</span>'
                + '<span class="rr-inicio-custom-modal__note-copy">' + escapeHtml(error?.message || 'Digite um valor maior para criar sua sala agora.') + '</span>'
                + '</div>';
        }
    }

    function openSlip(card) {
        console.log('RR Início: openSlip chamado', card);
        if (!card) {
            console.error('RR Início: openSlip chamado sem card');
            return;
        }
        
        selectedCard = card;
        selectedAmount = null;
        currentMatches = [];
        
        // Preencher dados do competidor no betslip
        const competitorName = getCardEntryName(card) || entryLabelCapitalized;
        const modalidadeName = getCardModalidadeName(card);
        const entryType = getCardEntryType(card);
        slipTitle.textContent = competitorName;
        if (slipHeroChip) {
            slipHeroChip.textContent = entryType === 'group' ? 'Grupo escolhido' : 'Competidor escolhido';
        }
        if (slipSubtitle) {
            slipSubtitle.textContent = modalidadeName
                ? 'Aposta ligada à modalidade ' + modalidadeName + '.'
                : 'Escolha a entrada e confirme.';
        }
        
        // Pegar multiplier do data attribute do card
        currentMultiplier = parseFloat(card.dataset.multiplier || '1.90');
        if (isPremium && card.dataset.premiumMultiplier) {
            currentMultiplier = parseFloat(card.dataset.premiumMultiplier);
        }
        currentMultiplier = Math.min(currentMultiplier, 1.99);
        if (oddsDisplay) {
            const oddsVal = oddsDisplay.querySelector('.rr-betslip__odds-value');
            if (oddsVal) oddsVal.textContent = currentMultiplier.toFixed(2).replace('.', ',') + 'x';
        }
        
        // Reset state
        setStakeValue(0);
        amountSection.hidden = false;
        amountSection.style.display = '';
        matchesSection.hidden = true;
        loadingEl.hidden = true;
        setPlaceBtnProcessing(false);
        setSlipState('idle');
        
        slip.hidden = false;
        slip.style.display = '';
        lockInicioBackgroundScroll(slip);
    }

    function closeSlip() {
        console.log('RR Início: closeSlip chamado');
        slip.hidden = true;
        slip.style.display = 'none';
        unlockInicioBackgroundScroll(slip);
        selectedCard = null;
        selectedAmount = null;
        currentMatches = [];
        setStakeValue(0);
        setSlipState('idle');
    }

    function setPlaceBtnProcessing(processing) {
        if (placeBtnText) placeBtnText.hidden = processing;
        if (placeBtnProcessing) placeBtnProcessing.hidden = !processing;
        if (placeBtn) {
            placeBtn.disabled = processing || getStakeValue() < RR_INICIO_MIN_STAKE;
        }
    }

    async function handleAmountSelect(amount) {
        if (!selectedCard) return;

        if (amount === 'custom') {
            openCustomModal();
            return;
        }

        // Valor fixo: define o campo com o valor do botão
        setStakeValue(Number(amount));
        setSlipState('selected');
    }

    // Botão APOSTE JÁ: valida valor e busca salas
    async function handlePlaceBet() {
        const amount = getStakeValue();
        if (amount < RR_INICIO_MIN_STAKE) {
            setSlipState('idle');
            return;
        }
        selectedAmount = amount;
        setSlipState('searching');
        setPlaceBtnProcessing(true);
        await findMatches(amount);
    }

    async function findMatches(amount) {
        if (!isAuthenticated) {
            closeSlip();
            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
            } else {
                window.location.href = '{{ route("user.login") }}';
            }
            return;
        }

        const modalidadeId = Number(selectedCard.dataset.modalidadeId || 0);
        const rodeioId = Number(selectedCard.dataset.rodeioId || 0);
        const competitorId = getCardCompetitorId(selectedCard);
        const competitorGroupId = getCardGroupId(selectedCard);
        const entryType = getCardEntryType(selectedCard);
        const divisao = selectedCard.dataset.divisao || null;

        if (!modalidadeId || (entryType === 'group' ? !competitorGroupId : !competitorId)) {
            alert('Contexto de modalidade não disponível.');
            setPlaceBtnProcessing(false);
            return;
        }

        // Mostrar loading, esconder stake
        amountSection.hidden = true;
        amountSection.style.display = 'none';
        loadingEl.hidden = false;
        matchesSection.hidden = true;

        try {
            const response = await fetch('/api/x1/find-matches', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    competitor_id: entryType === 'group' ? null : competitorId,
                    competitor_group_id: entryType === 'group' ? competitorGroupId : null,
                    valor_entrada: amount,
                    rodeio_id: rodeioId || null,
                    modalidade_id: modalidadeId,
                    divisao: divisao || null,
                }),
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Erro ao buscar salas.');
            }

            currentMatches = data.matches || [];
            loadingEl.hidden = true;

            if (currentMatches.length > 0) {
                // Autoconectar na primeira sala compatível (sem mostrar lista)
                const firstMatch = currentMatches[0];
                await joinRoom(firstMatch.id);
                return;
            } else {
                // Nenhuma sala encontrada — criar automaticamente
                setSlipState('creating');
                await createRoom(amount);
            }

        } catch (error) {
            loadingEl.hidden = true;
            amountSection.hidden = false;
            amountSection.style.display = '';
            setPlaceBtnProcessing(false);
            setSlipState('error', error.message || 'Erro ao buscar salas.');
            alert(error.message || 'Erro ao buscar salas.');
        }
    }

    function renderMatches() {
        matchesList.innerHTML = '';
        
        currentMatches.forEach(match => {
            const card = document.createElement('div');
            card.className = 'rr-inicio-slip__match-card';
            card.dataset.roomId = match.id;
            
            card.innerHTML = `
                <div class="rr-inicio-slip__match-header">
                    <span class="rr-inicio-slip__match-competitor">${match.competitor_name}</span>
                    ${match.host_is_premium ? '<span class="rr-inicio-slip__match-badge">PREMIUM</span>' : ''}
                </div>
                <div class="rr-inicio-slip__match-info">
                    <span>${match.waiting_time}</span>
                    <span class="rr-inicio-slip__match-multiplier">${match.multiplier_formatted}</span>
                </div>
                <div class="rr-inicio-slip__match-info">
                    <span>Você ganha: ${match.prize_total_formatted}</span>
                </div>
            `;
            
            card.addEventListener('click', () => joinRoom(match.id));
            matchesList.appendChild(card);
        });
    }

    async function joinRoom(roomId) {
        if (!selectedCard) return;

        const competitorId = getCardCompetitorId(selectedCard);
        const competitorGroupId = getCardGroupId(selectedCard);
        const entryType = getCardEntryType(selectedCard);

        loadingEl.hidden = false;
        matchesSection.hidden = true;
        setSlipState('searching', 'Encontramos uma sala compatível. Estamos reservando sua entrada agora.');
        setPlaceBtnProcessingLabel('Entrando na sala...');
        setPlaceBtnProcessing(true);

        try {
            const response = await fetch('/api/x1/join-room', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    room_id: roomId,
                    competitor_id: entryType === 'group' ? null : competitorId,
                    competitor_group_id: entryType === 'group' ? competitorGroupId : null,
                }),
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Erro ao entrar na sala.');
            }

            const preferenceId = data?.payment?.preference_id;
            if (!preferenceId) {
                throw new Error('Preferência de pagamento não encontrada.');
            }

            // Gerar PIX
            await processPayment(preferenceId);

        } catch (error) {
            loadingEl.hidden = true;
            amountSection.hidden = false;
            amountSection.style.display = '';
            matchesSection.hidden = true;
            setPlaceBtnProcessing(false);
            setSlipState('error', error.message || 'Erro ao entrar na sala.');
            alert(error.message || 'Erro ao entrar na sala.');
        }
    }

    async function createRoom(amount) {
        if (!selectedCard) return;

        const modalidadeId = Number(selectedCard.dataset.modalidadeId || 0);
        const rodeioId = Number(selectedCard.dataset.rodeioId || 0);
        const competitorId = getCardCompetitorId(selectedCard);
        const competitorGroupId = getCardGroupId(selectedCard);
        const entryType = getCardEntryType(selectedCard);
        const divisao = selectedCard.dataset.divisao || null;

        setSlipState('creating');
        setPlaceBtnProcessingLabel('Criando sala...');
        setPlaceBtnProcessing(true);

        try {
            const response = await fetch('/api/x1', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    description: 'Sala X1 (Início)',
                    rodeio_id: rodeioId || null,
                    modalidade_id: modalidadeId,
                    competitor_id: entryType === 'group' ? null : competitorId,
                    competitor_group_id: entryType === 'group' ? competitorGroupId : null,
                    valor_entrada: amount,
                    divisao: divisao || null,
                }),
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Erro ao criar sala.');
            }

            const preferenceId = data?.payment?.preference_id;
            if (!preferenceId) {
                throw new Error('Preferência de pagamento não encontrada.');
            }

            // Gerar PIX
            await processPayment(preferenceId);

        } catch (error) {
            loadingEl.hidden = true;
            amountSection.hidden = false;
            amountSection.style.display = '';
            setPlaceBtnProcessing(false);
            setSlipState('error', error.message || 'Erro ao criar sala.');
            alert(error.message || 'Erro ao criar sala.');
        }
    }

    async function processPayment(preferenceId) {
        try {
            showPixGenerationOverlay('Carregando PIX', 'Gerando o QR code da sua sala para abrir agora.');
            const response = await fetch('/api/x1/process-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ preferenceId }),
            });

            const pixData = await response.json();
            
            if (!response.ok || !pixData.success) {
                throw new Error(pixData.message || 'Falha ao gerar QR Code PIX.');
            }

            openPixModal(preferenceId, pixData);

        } catch (error) {
            hidePixGenerationOverlay();
            alert(error.message || 'Erro ao processar pagamento.');
            loadingEl.hidden = true;
            amountSection.hidden = false;
            amountSection.style.display = '';
            setPlaceBtnProcessing(false);
            setSlipState('error', error.message || 'Erro ao processar pagamento.');
        }
    }

    function openPixModal(preferenceId, pixData) {
        activePreferenceId = preferenceId;
        hidePixGenerationOverlay();
        modal.hidden = false;
        modal.style.display = '';
        lockInicioBackgroundScroll(modal);
        modalStatus.textContent = 'Aguardando confirmação...';

        const qrBase64 = String(pixData.qr_code_base64 || '').replace(/^data:image\/[a-zA-Z+.-]+;base64,/, '');
        modalImg.src = qrBase64 ? ('data:image/png;base64,' + qrBase64) : '';
        modalCode.value = pixData.qr_code || '';
        setPlaceBtnProcessing(false);
        setSlipState('pix');

        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(function () {
            checkPaymentStatus(false);
        }, 3000);
    }

    async function openPendingPixByPreference(preferenceId, options = {}) {
        const type = String(options.type || 'x1').toLowerCase();
        let pixData = {
            qr_code: String(options.qr_code || ''),
            qr_code_base64: String(options.qr_code_base64 || ''),
        };
        const shouldFetchPix = (!pixData.qr_code || !pixData.qr_code_base64) && type === 'x1';

        // Fallback para X1: tenta recuperar/gerar o PIX novamente pelo backend.
        if (shouldFetchPix) {
            try {
                showPixGenerationOverlay('Carregando PIX', 'Recuperando o QR code deste pagamento pendente.');
                const response = await fetch('/api/x1/process-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ preferenceId }),
                });

                const data = await response.json();
                if (response.ok && data?.success) {
                    pixData = {
                        qr_code: String(data.qr_code || ''),
                        qr_code_base64: String(data.qr_code_base64 || ''),
                    };
                }
            } catch (_) {
                hidePixGenerationOverlay();
                // fallback silencioso
            }
        }

        if (!pixData.qr_code && !pixData.qr_code_base64) {
            hidePixGenerationOverlay();
            alert('Nao foi possivel recuperar o QR Code deste pagamento pendente.');
            return false;
        }

        openPixModal(preferenceId, pixData);
        return true;
    }

    window.RRInicioOpenPendingPix = openPendingPixByPreference;

    function closePixModal() {
        modal.hidden = true;
        modal.style.display = 'none';
        unlockInicioBackgroundScroll(modal);
        activePreferenceId = null;
        setPlaceBtnProcessing(false);
        setSlipState(getStakeValue() >= RR_INICIO_MIN_STAKE ? 'selected' : 'idle');
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    async function checkPaymentStatus(manualCheck) {
        if (!activePreferenceId) return;
        try {
            const response = await fetch('/api/x1/payment-status?preference_id=' + encodeURIComponent(activePreferenceId), {
                headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json();
            const status = String(data.status || '').toLowerCase();

            if (status === 'approved') {
                modalStatus.textContent = 'Pagamento confirmado! Redirecionando...';
                closeSlip();
                setTimeout(function () {
                    closePixModal();
                    window.location.reload();
                }, 1200);
                return;
            }

            if (status.startsWith('refunded') || data.wallet_refunded) {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
                modalStatus.textContent = data.message || 'Sala preenchida. O valor voltou para sua carteira.';
                closeSlip();
                setTimeout(function () {
                    closePixModal();
                    alert(data.message || 'A sala ja foi preenchida. O valor foi devolvido para sua carteira.');
                    window.location.reload();
                }, 180);
                return;
            }

            if (manualCheck) {
                alert('Status atual: ' + (data.status || 'pendente'));
            }
        } catch (error) {
            if (manualCheck) {
                alert('Erro ao verificar pagamento. Tente novamente.');
            }
        }
    }

    function openCustomModal() {
        if (!isAuthenticated) {
            closeSlip();
            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
            } else {
                window.location.href = '{{ route("user.login") }}';
            }
            return;
        }

        customModal.hidden = false;
        customModal.style.display = '';
        lockInicioBackgroundScroll(customModal);
        if (customNameEl) {
            customNameEl.textContent = getCardEntryName(selectedCard) || entryLabelCapitalized;
        }
        customInput.value = '';
        updateCustomAmountPreview();
        loadCustomRoomOptions();
    }

    function closeCustomModal() {
        customModal.hidden = true;
        customModal.style.display = 'none';
        customInput.value = '';
        updateCustomAmountPreview();
        unlockInicioBackgroundScroll(customModal);
    }

    function levelLabel(level) {
        const map = {
            favorito: 'Favorito',
            elite: 'Elite',
            ascendente: 'Ascendente',
            competidor: 'Competidor',
        };
        return map[String(level || '').toLowerCase()] || 'Competidor';
    }

    function levelColor(level) {
        const map = {
            favorito: '#facc15',
            elite: '#f59e0be6',
            ascendente: '#3b82f6',
            competidor: '#22c55e',
        };
        return map[String(level || '').toLowerCase()] || '#22c55e';
    }

    function isElementVisible(element) {
        return !!(element && element.getClientRects && element.getClientRects().length);
    }

    function getCompetitorCards(onlyVisible = true) {
        return Array.from(root.querySelectorAll('.rr-neuro-wrapper')).filter((card) => {
            const grid = card.closest('.rr-inicio-grid');
            if (!grid) return false;
            if (!onlyVisible) return true;
            return isElementVisible(card) && isElementVisible(grid);
        });
    }

    function getUniqueCompetitorCards() {
        const seen = new Set();
        return getCompetitorCards(false).filter((card) => {
            const key = getCardSearchKey(card);
            if (!key || seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }

    function getVisibleSearchCards() {
        const seen = new Set();
        return getCompetitorCards(true).filter((card) => {
            const key = getCardSearchKey(card);
            if (!key || seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }

    function getCardSearchKey(card) {
        const entryId = String(card?.dataset?.entryId || card?.dataset?.competitorId || card?.dataset?.groupId || '').trim();
        const modalidadeId = String(card?.dataset?.modalidadeId || '').trim();
        const entryType = getCardEntryType(card);
        if (!entryId) return '';
        return [modalidadeId || '0', entryType || 'competitor', entryId].join(':');
    }

    function parseDelimitedDataset(value, separator) {
        return String(value || '')
            .split(separator)
            .map((item) => String(item || '').trim())
            .filter(Boolean);
    }

    function getCardMemberIds(card) {
        return parseDelimitedDataset(card?.dataset?.memberIds || '', ',');
    }

    function getCardMemberNames(card) {
        return parseDelimitedDataset(card?.dataset?.memberNames || '', '|');
    }

    function getCardContextKey(card) {
        const rodeioId = String(card?.dataset?.rodeioId || '').trim();
        const modalidadeId = String(card?.dataset?.modalidadeId || '').trim();
        const divisao = String(card?.dataset?.divisao || '').trim().toLowerCase();
        if (!modalidadeId) return '';
        return [rodeioId || '0', modalidadeId, divisao || 'sem-divisao'].join(':');
    }

    function renderCompetitorSearchResults(queryText, entries = []) {
        if (!competitorSearchResults) return;
        const filtered = Array.isArray(entries) ? entries : [];

        if (!filtered.length) {
            competitorSearchResults.innerHTML = '<div class="rr-competitor-search-empty">Nenhum competidor encontrado.</div>';
            return;
        }

        const resetButton = activeSearchSelection
            ? `
                <button type="button" class="rr-competitor-search-item rr-competitor-search-item--reset" data-reset-search-filter="1" style="--rr-level-color:#60a5fa;">
                    <span class="rr-competitor-search-item__name">Mostrar tudo novamente</span>
                    <span class="rr-competitor-search-item__level">Limpar o filtro atual da página</span>
                </button>
            `
            : '';

        competitorSearchResults.innerHTML = resetButton + filtered.map((entry) => {
            const name = escapeHtml(entry.name || entry.short_name || entryLabelCapitalized);
            const level = String(entry.level || 'competidor').toLowerCase();
            const typeLabel = entry.level_label || levelLabel(level);
            const color = levelColor(level);
            const entryKey = escapeHtml(entry.key);
            return `
                <button type="button" class="rr-competitor-search-item" data-entry-key="${entryKey}" style="--rr-level-color:${color};">
                    <span class="rr-competitor-search-item__name">${name}</span>
                    <span class="rr-competitor-search-item__level">${typeLabel}</span>
                </button>
            `;
        }).join('');
    }

    function openCompetitorSearchModal() {
        if (!competitorSearchModal) return;
        document.body.appendChild(competitorSearchModal);
        competitorSearchModal.hidden = false;
        lockInicioBackgroundScroll(competitorSearchModal);
        if (competitorSearchInput) {
            competitorSearchInput.value = '';
            setTimeout(() => competitorSearchInput.focus(), 10);
        }
        queueCompetitorSearch('');
    }

    function closeCompetitorSearchModal() {
        if (!competitorSearchModal) return;
        competitorSearchModal.hidden = true;
        unlockInicioBackgroundScroll(competitorSearchModal);
    }

    function openGroupMembersModal(card) {
        if (!groupMembersModal || !groupMembersResults || !card) return;

        const captainName = String(card.dataset.captainName || card.dataset.entryName || 'Capitão').trim();
        const memberNames = getCardMemberNames(card);

        if (groupMembersTitle) {
            groupMembersTitle.textContent = 'Grupo de ' + captainName;
        }

        if (!memberNames.length) {
            groupMembersResults.innerHTML = '<div class="rr-competitor-search-empty">Nenhum integrante encontrado para este grupo.</div>';
        } else {
            groupMembersResults.innerHTML = memberNames.map((name, index) => `
                <div class="rr-competitor-search-item" style="--rr-level-color:${index === 0 ? '#f59e0be6' : '#22c55e'};">
                    <span class="rr-competitor-search-item__name">${escapeHtml(name)}</span>
                    <span class="rr-competitor-search-item__level">${index === 0 ? 'Capitão' : 'Integrante'}</span>
                </div>
            `).join('');
        }

        document.body.appendChild(groupMembersModal);
        groupMembersModal.hidden = false;
        lockInicioBackgroundScroll(groupMembersModal);
    }

    function closeGroupMembersModal() {
        if (!groupMembersModal) return;
        groupMembersModal.hidden = true;
        unlockInicioBackgroundScroll(groupMembersModal);
    }

    function focusCompetitorCardById(entryKey) {
        const target = getCompetitorCards(true).find((card) => getCardSearchKey(card) === String(entryKey));
        if (!target) return;
        const grid = target.closest('.rr-inicio-grid');
        if (!grid) return;

        const left = Math.max(0, target.offsetLeft - 10);
        grid.scrollTo({ left, behavior: 'smooth' });

        target.classList.remove('rr-card-search-focus');
        void target.offsetWidth;
        target.classList.add('rr-card-search-focus');
        setTimeout(() => target.classList.remove('rr-card-search-focus'), 1100);
    }

    function clearInicioSearchSelection() {
        activeSearchSelection = null;
        if (competitorSearchInput) {
            competitorSearchInput.value = '';
        }
        applyInicioSubmenuFilters();
    }

    function buildSearchSelection(entryKey) {
        const [kind, rawId] = String(entryKey || '').split(':');
        const selectionId = String(rawId || '').trim();
        if (!kind || !selectionId) return null;

        const cards = getCompetitorCards(false);

        if (kind === 'group') {
            const groupCards = cards.filter((card) => String(card.dataset.groupId || card.dataset.entryId || '').trim() === selectionId);
            const contexts = Array.from(new Set(groupCards.map(getCardContextKey).filter(Boolean)));
            return {
                kind: 'group',
                id: selectionId,
                contexts,
                focusEntryKey: groupCards[0] ? getCardSearchKey(groupCards[0]) : '',
            };
        }

        const competitorCards = cards.filter((card) => {
            if (String(card.dataset.competitorId || card.dataset.entryId || '').trim() === selectionId) return true;
            return getCardMemberIds(card).includes(selectionId);
        });
        const contexts = Array.from(new Set(competitorCards.map(getCardContextKey).filter(Boolean)));
        return {
            kind: 'competitor',
            id: selectionId,
            contexts,
            focusEntryKey: competitorCards.find((card) => String(card.dataset.competitorId || card.dataset.entryId || '').trim() === selectionId)
                ? getCardSearchKey(competitorCards.find((card) => String(card.dataset.competitorId || card.dataset.entryId || '').trim() === selectionId))
                : '',
        };
    }

    function nodeMatchesSearchContexts(node) {
        if (!activeSearchSelection) return true;
        if (!activeSearchSelection.contexts?.length) return false;
        return activeSearchSelection.contexts.includes(getCardContextKey(node) || [
            String(node?.dataset?.rodeioId || '').trim() || '0',
            String(node?.dataset?.modalidadeId || '').trim(),
            String(node?.dataset?.divisao || '').trim().toLowerCase() || 'sem-divisao',
        ].join(':'));
    }

    function cardMatchesSearchSelection(card) {
        if (!activeSearchSelection) return true;
        if (!nodeMatchesSearchContexts(card)) return false;

        if (activeSearchSelection.kind === 'group') {
            return String(card.dataset.groupId || card.dataset.entryId || '').trim() === String(activeSearchSelection.id);
        }

        const competitorId = String(card.dataset.competitorId || card.dataset.entryId || '').trim();
        if (competitorId === String(activeSearchSelection.id)) return true;
        return getCardMemberIds(card).includes(String(activeSearchSelection.id));
    }

    async function fetchCompetitorSearchResults(queryText) {
        const requestId = ++competitorSearchRequestId;
        const query = String(queryText || '').trim().toLowerCase();

        if (competitorSearchResults) {
            competitorSearchResults.innerHTML = '<div class="rr-competitor-search-empty">Buscando entradas disponíveis...</div>';
        }

        try {
            const entries = getVisibleSearchCards()
                .map((card) => {
                    const isGroup = getCardEntryType(card) === 'group';
                    const entryName = String(card.dataset.entryName || '').trim();
                    const captainName = String(card.dataset.captainName || '').trim();
                    const displayName = isGroup
                        ? (captainName || entryName || 'Grupo disponível')
                        : (entryName || 'Competidor disponível');
                    const searchText = String(card.dataset.searchText || displayName).toLowerCase();
                    const level = String(card.dataset.nivel || 'competidor').toLowerCase();

                    return {
                        key: getCardSearchKey(card),
                        id: String(card.dataset.entryId || card.dataset.competitorId || card.dataset.groupId || ''),
                        name: displayName,
                        short_name: displayName,
                        level,
                        level_label: isGroup ? 'Grupo disponível' : levelLabel(level),
                        claimed: false,
                        search_text: searchText,
                    };
                })
                .filter((entry) => !query || entry.search_text.includes(query))
                .slice(0, 40);

            if (requestId !== competitorSearchRequestId) return;
            renderCompetitorSearchResults(query, entries);
        } catch (error) {
            if (requestId !== competitorSearchRequestId) return;
            competitorSearchResults.innerHTML = '<div class="rr-competitor-search-empty">Erro ao buscar entradas disponíveis.</div>';
        }
    }

    function queueCompetitorSearch(queryText) {
        if (competitorSearchDebounce) {
            clearTimeout(competitorSearchDebounce);
        }
        competitorSearchDebounce = setTimeout(() => {
            fetchCompetitorSearchResults(queryText);
        }, 160);
    }

    async function handleCustomSubmit() {
        const amount = parseFloat(customInput.value);
        
        if (isNaN(amount) || amount < RR_INICIO_CUSTOM_MIN_STAKE || amount > RR_INICIO_MAX_STAKE) {
            alert('Valor customizado deve ser acima de R$100,00 e ate ' + formatBRL(RR_INICIO_MAX_STAKE));
            return;
        }

        closeCustomModal();
        setStakeValue(amount);
        selectedAmount = amount;
        await findMatches(amount);
    }

    // Evita clique acidental em card quando o gesto era scroll vertical.
    let suppressCardClicksUntil = 0;
    let rootTouchStartX = 0;
    let rootTouchStartY = 0;

    root.addEventListener('touchstart', function (event) {
        const touch = event.touches && event.touches[0];
        if (!touch) return;
        rootTouchStartX = touch.clientX;
        rootTouchStartY = touch.clientY;
    }, { passive: true });

    root.addEventListener('touchmove', function (event) {
        const touch = event.touches && event.touches[0];
        if (!touch) return;
        const dx = Math.abs(touch.clientX - rootTouchStartX);
        const dy = Math.abs(touch.clientY - rootTouchStartY);
        if (dy > 8 && dy > dx) {
            suppressCardClicksUntil = Date.now() + 260;
        }
    }, { passive: true });

    function goToPremiumTab() {
        if (window.goToPremiumTab) {
            window.goToPremiumTab();
            return;
        }

        if (window.switchHubTab) {
            window.switchHubTab('premium');
            return;
        }

        window.location.href = '/?tab=premium';
    }

    function openStatsFromCard(card) {
        if (!card || getCardEntryType(card) !== 'competitor') return;

        const competitorId = getCardCompetitorId(card) || Number(card.dataset.entryId || 0);
        if (!competitorId) return;

        const payload = {
            id: competitorId,
            name: getCardEntryName(card) || 'Competidor',
        };

        if (window.RRStats && typeof window.RRStats.queuePendingOpen === 'function') {
            window.RRStats.queuePendingOpen(payload);
        } else {
            window.__rrPendingStatsOpen = payload;
            try {
                sessionStorage.setItem('rr_stats_open_target', JSON.stringify(payload));
            } catch (error) {}
        }

        if (window.switchHubTab) {
            window.switchHubTab('estatisticas');
            return;
        }

        window.location.href = '/?tab=estatisticas';
    }

    // Event Listeners
    root.addEventListener('click', function (event) {
        if (Date.now() < suppressCardClicksUntil) return;

        const premiumButton = event.target.closest('[data-action="go-premium"]');
        if (premiumButton) {
            goToPremiumTab();
            return;
        }

        const statsButton = event.target.closest('[data-action="open-stats"]');
        if (statsButton) {
            const card = statsButton.closest('.rr-neuro-wrapper');
            if (card) openStatsFromCard(card);
            return;
        }

        const oddButton = event.target.closest('[data-action="open-slip"]');
        if (oddButton) {
            const card = oddButton.closest('.rr-neuro-wrapper');
            if (card) openSlip(card);
        }
    });

    function bindOpenSlipTargets() {
        root.querySelectorAll('[data-action="open-slip"]').forEach((target) => {
            if (!target || target.dataset.rrSlipBound === '1') return;
            target.dataset.rrSlipBound = '1';
            target.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const card = target.closest('.rr-neuro-wrapper');
                if (card) openSlip(card);
            });
        });
    }

    function bindStatsTargets() {
        root.querySelectorAll('[data-action="open-stats"]').forEach((target) => {
            if (!target || target.dataset.rrStatsBound === '1') return;
            target.dataset.rrStatsBound = '1';
            target.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const card = target.closest('.rr-neuro-wrapper');
                if (card) openStatsFromCard(card);
            });
        });
    }

    function bindPremiumTargets() {
        root.querySelectorAll('[data-action="go-premium"]').forEach((target) => {
            if (!target || target.dataset.rrPremiumBound === '1') return;
            target.dataset.rrPremiumBound = '1';
            target.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                goToPremiumTab();
            });
        });
    }

    function bindGroupMembersTargets() {
        root.querySelectorAll('[data-action="open-group-members"]').forEach((target) => {
            if (!target || target.dataset.rrGroupMembersBound === '1') return;
            target.dataset.rrGroupMembersBound = '1';
            target.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const card = target.closest('.rr-neuro-wrapper');
                if (card) openGroupMembersModal(card);
            });
        });
    }

    bindOpenSlipTargets();
    bindStatsTargets();
    bindPremiumTargets();
    bindGroupMembersTargets();

    // Fechar betslip
    document.getElementById('rrInicioSlipClose')?.addEventListener('click', closeSlip);
    document.getElementById('rrBetslipRemoveLeg')?.addEventListener('click', closeSlip);
    backdrop?.addEventListener('click', closeSlip);

    // Stake input removido - usando botões fixos
    
    // Botões de aposta rápida + personalizado
    if (slip) {
        slip.querySelectorAll('.rr-inicio-slip__stake').forEach((button) => {
            button.addEventListener('click', function () {
                const value = button.dataset.value;
                handleAmountSelect(value === 'custom' ? 'custom' : Number(value));
            });
        });
    }

    // Botão APOSTE JÁ
    placeBtn?.addEventListener('click', handlePlaceBet);

    document.getElementById('rrInicioSlipCreateNew')?.addEventListener('click', () => {
        if (selectedAmount) createRoom(selectedAmount);
    });

    customModal?.querySelectorAll('[data-custom-close]').forEach((button) => {
        button.addEventListener('click', closeCustomModal);
    });
    document.getElementById('rrInicioCustomSubmit')?.addEventListener('click', handleCustomSubmit);
    customInput?.addEventListener('input', updateCustomAmountPreview);
    customInput?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            handleCustomSubmit();
        }
    });
    customModal?.querySelectorAll('[data-custom-value]').forEach((button) => {
        button.addEventListener('click', function () {
            const value = Number(button.getAttribute('data-custom-value') || 0);
            if (!value) return;
            customInput.value = value.toFixed(2);
            updateCustomAmountPreview();
        });
    });
    customMatches?.addEventListener('click', async function (event) {
        const roomButton = event.target.closest('[data-custom-room-id]');
        if (!roomButton) return;

        const roomId = Number(roomButton.getAttribute('data-custom-room-id') || 0);
        const entryAmount = Number(roomButton.getAttribute('data-custom-room-value') || 0);
        if (!roomId || entryAmount <= 0) return;

        closeCustomModal();
        setStakeValue(entryAmount);
        selectedAmount = entryAmount;
        await joinRoom(roomId);
    });

    document.getElementById('rrInicioPixClose')?.addEventListener('click', closePixModal);
    document.getElementById('rrInicioPixCopy')?.addEventListener('click', async function () {
        if (!modalCode.value) return;
        try {
            await navigator.clipboard.writeText(modalCode.value);
            modalStatus.textContent = 'Código PIX copiado!';
        } catch (error) {
            modalCode.select();
            document.execCommand('copy');
            modalStatus.textContent = 'Código PIX copiado!';
        }
    });
    document.getElementById('rrInicioPixCheck')?.addEventListener('click', function () {
        checkPaymentStatus(true);
    });

    competitorSearchOpen?.addEventListener('click', openCompetitorSearchModal);
    competitorSearchClose?.addEventListener('click', closeCompetitorSearchModal);
    groupMembersClose?.addEventListener('click', closeGroupMembersModal);
    competitorSearchModal?.addEventListener('click', function (event) {
        if (event.target === competitorSearchModal) closeCompetitorSearchModal();
    });

    groupMembersModal?.addEventListener('click', function (event) {
        if (event.target === groupMembersModal) closeGroupMembersModal();
    });
    competitorSearchInput?.addEventListener('input', function () {
        queueCompetitorSearch(competitorSearchInput.value);
    });
    competitorSearchResults?.addEventListener('click', function (event) {
        const resetButton = event.target.closest('[data-reset-search-filter]');
        if (resetButton) {
            closeCompetitorSearchModal();
            clearInicioSearchSelection();
            return;
        }

        const item = event.target.closest('.rr-competitor-search-item');
        if (!item) return;
        const competitorId = item.getAttribute('data-entry-key');
        closeCompetitorSearchModal();
        if (!competitorId) return;

        const selection = buildSearchSelection(competitorId);
        if (!selection) return;
        activeSearchSelection = selection;

        const uniqueRodeios = Array.from(new Set(selection.contexts.map((context) => String(context).split(':')[0]).filter((value) => value && value !== '0')));
        const uniqueModalidades = Array.from(new Set(selection.contexts.map((context) => String(context).split(':')[1]).filter(Boolean)));

        if (inicioRodeioFilter && uniqueRodeios.length === 1) {
            inicioRodeioFilter.value = uniqueRodeios[0];
            refreshInicioModalidadeOptions();
        }

        if (inicioModalidadeFilter) {
            inicioModalidadeFilter.value = uniqueModalidades.length === 1 ? uniqueModalidades[0] : '';
        }

        applyInicioSubmenuFilters();

        const focusTarget = selection.focusEntryKey || competitorId;
        if (focusTarget) {
            setTimeout(() => focusCompetitorCardById(focusTarget), 80);
        }
    });

    mobileQuickNav?.addEventListener('click', function (event) {
        const sectionButton = event.target.closest('[data-hub-section]');
        if (sectionButton) {
            event.preventDefault();
            closeMobileQuickPanels();

            const section = String(sectionButton.getAttribute('data-hub-section') || '');
            const targetButton = document.querySelector(
                '.hub-mobile-tabbar__btn[data-section="' + section + '"], .hub-header-nav__btn[data-section="' + section + '"], [data-section="' + section + '"][data-url]'
            );

            if (targetButton && targetButton !== sectionButton) {
                targetButton.click();
                return;
            }

            const fallbackUrl = sectionButton.getAttribute('data-hub-url');
            if (fallbackUrl) {
                window.location.href = fallbackUrl;
            }
            return;
        }

        const quickScrollTrigger = event.target.closest('[data-scroll-target]');
        if (quickScrollTrigger) {
            event.preventDefault();
            closeMobileQuickPanels();
            scrollToInicioQuickSection(quickScrollTrigger.getAttribute('data-scroll-target'));
            return;
        }

        const trigger = event.target.closest('[data-filter-popout]');
        if (trigger) {
            event.preventDefault();
            closeMobileQuickPanels();

            const targetType = String(trigger.getAttribute('data-filter-popout') || '');
            if (scrollToInicioQuickSection(targetType)) {
                return;
            }

            openMobileQuickPanel(targetType);
            return;
        }

        const option = event.target.closest('[data-filter-target]');
        if (!option) return;

        event.preventDefault();
        const target = String(option.getAttribute('data-filter-target') || '');
        const value = String(option.getAttribute('data-filter-value') || '');
        if (!target || !value) return;

        mobileQuickFilterState[target] = value;
        closeMobileQuickPanels();
        applyMobileQuickFilters();

        if (!isMobileQuickViewport()) return;

        if (target === 'x1') {
            scrollToInicioFilterTarget(x1Section, '.rr-x1-room-grid');
            return;
        }

        if (target === 'bolao') {
            scrollToInicioFilterTarget(bolaoSection, '.rr-bolao-grid');
        }
    });

    if (window.__rrInicioQuickNavAwayHandler) {
        document.removeEventListener('click', window.__rrInicioQuickNavAwayHandler, true);
    }
    window.__rrInicioQuickNavAwayHandler = function (event) {
        if (!mobileQuickNav || !mobileQuickNav.contains(event.target)) {
            closeMobileQuickPanels();
        }
    };
    document.addEventListener('click', window.__rrInicioQuickNavAwayHandler, true);
    syncMobileQuickOptionState();

    // ---- Auto-scroll desativado: usa scroll nativo ----

    function initInfiniteCarousel(carouselId, options = {}) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;
        const nav = document.querySelector('[data-carousel-nav-for="' + carouselId + '"]');
        const prevBtn = nav ? nav.querySelector('[data-carousel-scroll="prev"]') : null;
        const nextBtn = nav ? nav.querySelector('[data-carousel-scroll="next"]') : null;

        if (typeof carousel.__rrInfiniteCleanup === 'function') {
            carousel.__rrInfiniteCleanup();
            carousel.__rrInfiniteCleanup = null;
        }
        carousel.classList.remove('is-dragging');
        carousel.classList.remove('is-user-scroll');

        let isPointerDown = false;
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let startScrollLeft = 0;
        let suppressClickUntil = 0;
        let dragAxis = '';
        const dragThreshold = 8;
        const enableTouchDrag = options.touch === true;

        const getCarouselStep = () => {
            const firstCard = carousel.querySelector('.rr-neuro-wrapper, .rr-bolao-card, .rr-x1room-card');
            const computed = window.getComputedStyle(carousel);
            const gap = parseFloat(computed.columnGap || computed.gap || '0') || 0;
            const firstWidth = firstCard ? firstCard.getBoundingClientRect().width : 0;
            const viewportStep = carousel.clientWidth * (window.innerWidth < 768 ? 0.84 : 0.68);
            return Math.max(firstWidth + gap, viewportStep, 140);
        };

        const updateNavState = () => {
            if (!nav || !prevBtn || !nextBtn) return;

            const maxScroll = Math.max(0, carousel.scrollWidth - carousel.clientWidth);
            const hasOverflow = maxScroll > 12;

            nav.hidden = false;
            prevBtn.disabled = !hasOverflow || carousel.scrollLeft <= 4;
            nextBtn.disabled = !hasOverflow || carousel.scrollLeft >= (maxScroll - 4);
        };

        const handleNavClick = (direction) => {
            const delta = direction === 'prev' ? -1 : 1;
            carousel.classList.add('is-user-scroll');
            carousel.scrollBy({
                left: delta * getCarouselStep(),
                behavior: 'smooth',
            });
            window.setTimeout(updateNavState, 180);
        };
        const onPrevClick = () => handleNavClick('prev');
        const onNextClick = () => handleNavClick('next');

        const hasMouseLikePointer = (event) => {
            if (window.innerWidth < 768) return false;
            if (!event) return false;
            return event.pointerType === 'mouse' || event.pointerType === 'pen' || event.pointerType === '';
        };

        const hasTouchLikePointer = (event) => {
            if (!enableTouchDrag) return false;
            if (window.innerWidth >= 768) return false;
            if (!event) return false;
            return event.pointerType === 'touch';
        };

        const shouldHandlePointer = (event) => hasMouseLikePointer(event) || hasTouchLikePointer(event);

        const onPointerDown = (event) => {
            if (!shouldHandlePointer(event)) return;
            if (hasMouseLikePointer(event) && event.button !== 0) return;
            if (hasMouseLikePointer(event) && event.target.closest('button, a, input, textarea, select, label')) return;

            isPointerDown = true;
            isDragging = false;
            dragAxis = '';
            startX = event.clientX;
            startY = event.clientY;
            startScrollLeft = carousel.scrollLeft;
            carousel.classList.add('is-user-scroll');
            if (hasMouseLikePointer(event)) {
                carousel.setPointerCapture?.(event.pointerId);
            }
        };

        const onPointerMove = (event) => {
            if (!isPointerDown || !shouldHandlePointer(event)) return;

            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            if (!dragAxis) {
                if (Math.abs(deltaX) < dragThreshold && Math.abs(deltaY) < dragThreshold) {
                    return;
                }

                dragAxis = Math.abs(deltaX) >= Math.abs(deltaY) ? 'x' : 'y';
                if (dragAxis !== 'x') {
                    finishDrag(event.pointerId);
                    return;
                }
            }

            if (!isDragging && Math.abs(deltaX) >= dragThreshold) {
                isDragging = true;
                carousel.classList.add('is-dragging');
            }

            if (!isDragging) return;

            carousel.scrollLeft = startScrollLeft - deltaX;
            suppressClickUntil = Date.now() + 250;
            event.preventDefault();
        };

        const finishDrag = (pointerId) => {
            if (pointerId !== undefined) {
                if (carousel.hasPointerCapture?.(pointerId)) {
                    carousel.releasePointerCapture(pointerId);
                }
            }
            isPointerDown = false;
            dragAxis = '';
            carousel.classList.remove('is-dragging');
            window.setTimeout(() => {
                isDragging = false;
            }, 0);
        };

        const onPointerUp = (event) => {
            if (!shouldHandlePointer(event)) return;
            finishDrag(event.pointerId);
        };

        const onPointerCancel = (event) => {
            if (!shouldHandlePointer(event)) return;
            finishDrag(event.pointerId);
        };

        const onLostPointerCapture = () => {
            finishDrag();
        };

        const onDragStart = (event) => {
            if (window.innerWidth >= 768) {
                event.preventDefault();
            }
        };

        const onClickCapture = (event) => {
            if (Date.now() < suppressClickUntil) {
                event.preventDefault();
                event.stopPropagation();
            }
        };

        carousel.addEventListener('pointerdown', onPointerDown);
        carousel.addEventListener('pointermove', onPointerMove);
        carousel.addEventListener('pointerup', onPointerUp);
        carousel.addEventListener('pointercancel', onPointerCancel);
        carousel.addEventListener('lostpointercapture', onLostPointerCapture);
        carousel.addEventListener('dragstart', onDragStart);
        carousel.addEventListener('click', onClickCapture, true);
        carousel.addEventListener('scroll', updateNavState, { passive: true });
        window.addEventListener('resize', updateNavState, { passive: true });

        if (prevBtn) {
            prevBtn.addEventListener('click', onPrevClick);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', onNextClick);
        }

        requestAnimationFrame(updateNavState);

        carousel.__rrInfiniteCleanup = function () {
            isPointerDown = false;
            isDragging = false;
            carousel.classList.remove('is-dragging');
            carousel.classList.remove('is-user-scroll');
            carousel.removeEventListener('pointerdown', onPointerDown);
            carousel.removeEventListener('pointermove', onPointerMove);
            carousel.removeEventListener('pointerup', onPointerUp);
            carousel.removeEventListener('pointercancel', onPointerCancel);
            carousel.removeEventListener('lostpointercapture', onLostPointerCapture);
            carousel.removeEventListener('dragstart', onDragStart);
            carousel.removeEventListener('click', onClickCapture, true);
            carousel.removeEventListener('scroll', updateNavState);
            window.removeEventListener('resize', updateNavState);
            if (prevBtn) prevBtn.removeEventListener('click', onPrevClick);
            if (nextBtn) nextBtn.removeEventListener('click', onNextClick);
        };
    }

    function initInicioInfiniteScrolls() {
        document.querySelectorAll('[data-carousel-auto="1"]').forEach((carousel) => {
            if (!carousel.id) return;
            initInfiniteCarousel(carousel.id, {
                touch: carousel.dataset.carouselTouch === '1' || carousel.classList.contains('rr-bolao-grid') || carousel.classList.contains('rr-x1-room-grid'),
            });
        });
    }

    // Expor para o hub reinicializar após reparenting no desktop
    window.rrReinitCarousels = initInicioInfiniteScrolls;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(initInicioInfiniteScrolls, 500));
    } else {
        setTimeout(initInicioInfiniteScrolls, 500);
    }

    function hasInicioOpenModal() {
        return !!document.querySelector(
            '#rrInicioSlip:not([hidden]), ' +
            '#rrInicioPixModal:not([hidden]), ' +
            '#rrInicioCustomModal:not([hidden]), ' +
            '#rrCompetitorSearchModal:not([hidden]), ' +
            '#rrJoinSlip:not([hidden]), ' +
            '#rrDraftModal.is-open, ' +
            '.rr-competitor-picker-overlay.active, ' +
            '.rr-modal-overlay[style*="display: block"], ' +
            '.rr-modal-overlay[style*="display:block"]'
        );
    }

    function normalizeInicioMobileScrollLock() {
        if (window.innerWidth >= 769) return;
        if (hasInicioOpenModal()) return;

        document.body.classList.remove('rr-inicio-modal-open');
        document.body.classList.remove('rr-draft-lock');
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.documentElement.style.overflow = '';
        document.documentElement.style.overflowY = '';
        inicioScrollLock.count = 0;
        inicioScrollLock.bodyPrev = null;
        inicioScrollLock.htmlPrev = null;
        inicioScrollLock.y = 0;
    }

    // Safety net para webview/mobile: evita lock residual de scroll.
    normalizeInicioMobileScrollLock();
    if (window.__rrInicioScrollLockInterval) {
        clearInterval(window.__rrInicioScrollLockInterval);
    }
    window.__rrInicioScrollLockInterval = setInterval(normalizeInicioMobileScrollLock, 1500);

    // ============================================
    // 💰 CARREGAR E RENDERIZAR BOLÕES
    // ============================================

    let bolaoTimerTicker = null;
    let lastLoadedBolaoLeagues = [];

    function getLeagueDeadlineMs(league) {
        const preferred = league.registration_deadline || league.closes_at || null;
        if (!preferred) return null;
        const ms = Date.parse(preferred);
        return Number.isFinite(ms) ? ms : null;
    }

    function formatCountdown(msLeft) {
        if (!Number.isFinite(msLeft)) return 'Sem limite';
        if (msLeft <= 0) return 'Encerrado';

        const totalSeconds = Math.floor(msLeft / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (days > 0) return `${days}d ${hours}h ${minutes}m`;
        if (hours > 0) return `${hours}h ${minutes}m ${seconds}s`;
        return `${minutes}m ${seconds}s`;
    }

    function refreshBolaoTimers() {
        const timerEls = document.querySelectorAll('.rr-bolao-timer[data-deadline-ms]');
        if (!timerEls.length) return;

        const now = Date.now();
        timerEls.forEach((el) => {
            const deadline = Number(el.dataset.deadlineMs || NaN);
            if (!Number.isFinite(deadline)) {
                el.textContent = 'Termina em: Sem limite';
                return;
            }
            const left = deadline - now;
            el.textContent = `Termina em: ${formatCountdown(left)}`;
        });
    }

    function startBolaoTimerTicker() {
        if (window.__rrBolaoTimerTicker) {
            clearInterval(window.__rrBolaoTimerTicker);
            window.__rrBolaoTimerTicker = null;
        }
        if (bolaoTimerTicker) {
            clearInterval(bolaoTimerTicker);
            bolaoTimerTicker = null;
        }
        refreshBolaoTimers();
        bolaoTimerTicker = setInterval(refreshBolaoTimers, 1000);
        window.__rrBolaoTimerTicker = bolaoTimerTicker;
    }
    
    async function loadBolaos() {
        const bolaoGrids = Array.from(document.querySelectorAll('.rr-bolao-grid'));
        if (!bolaoGrids.length) return;

        const requestConfig = {
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        };

        for (const bolaoGrid of bolaoGrids) {
            const modalidadeId = Number(bolaoGrid.dataset.modalidadeId || 0);
            const rodeioId = Number(bolaoGrid.dataset.rodeioId || 0);
            const divisao = String(bolaoGrid.dataset.divisao || '').trim();
            const sectionTitle = bolaoGrid.closest('.rr-inicio-subcatalog')?.querySelector('.rr-inicio-subcatalog__name')?.textContent?.trim() || 'esta modalidade';

            if (!modalidadeId && !rodeioId) {
                bolaoGrid.innerHTML = '<div class="rr-neuro-loading"><p class="text-muted">Nenhum rodeio ativo para carregar bolões.</p></div>';
                continue;
            }

            try {
                const attempts = [];
                const seenKeys = new Set();
                const queueAttempt = (params) => {
                    const queryKey = JSON.stringify(params);
                    if (seenKeys.has(queryKey)) return;
                    seenKeys.add(queryKey);
                    attempts.push(params);
                };

                if (modalidadeId > 0) {
                    queueAttempt({
                        modalidade_id: String(modalidadeId),
                        rodeio_id: rodeioId > 0 ? String(rodeioId) : '',
                        divisao: divisao,
                    });
                }

                if (rodeioId > 0) {
                    queueAttempt({
                        rodeio_id: String(rodeioId),
                        divisao: divisao,
                    });
                    queueAttempt({
                        rodeio_id: String(rodeioId),
                    });
                }

                let leagues = [];

                for (const attempt of attempts) {
                    const params = new URLSearchParams({ only_active: '1' });
                    Object.entries(attempt).forEach(([key, value]) => {
                        if (value !== null && value !== undefined && String(value).trim() !== '') {
                            params.set(key, String(value));
                        }
                    });
                    params.set('_ts', String(Date.now()));

                    const response = await fetch('/api/fantasy/leagues?' + params.toString(), requestConfig);
                    const data = await response.json();

                    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                        leagues = data.data;
                        break;
                    }
                }

                if (!leagues.length) {
                    bolaoGrid.innerHTML = '<div class="rr-neuro-loading"><p class="text-muted">Nenhum bolão disponível para ' + escapeHtml(sectionTitle) + '.</p></div>';
                    continue;
                }

                bolaoGrid.innerHTML = '';

                leagues.forEach((league, index) => {
                    const card = createBolaoCard(league);
                    card.dataset.quickOrder = String(index);
                    bolaoGrid.appendChild(card);
                });

                lastLoadedBolaoLeagues = Array.isArray(leagues) ? leagues.slice() : [];

                setTimeout(() => initInfiniteCarousel(bolaoGrid.id, { touch: true }), 120);
            } catch (error) {
                console.error('Erro ao carregar bolões:', error);
                bolaoGrid.innerHTML = '<div class="rr-neuro-loading"><p class="text-danger">Erro ao carregar bolões.</p></div>';
            }
        }

        applyMobileQuickFilters();
        startBolaoTimerTicker();
    }

    const bolaoLaunchLeaguesById = new Map();
    const bolaoLaunchPool = [];
    let bolaoLaunchSelectedModalidadeId = '';
    const bolaoLaunchModalidadePlaceholder = 'Selecione uma modalidade';

    function mapLaunchBolaoEntry(entryValue) {
        const normalized = Math.round((parseFloat(entryValue || 0) || 0) * 100) / 100;
        if (Math.abs(normalized - 0.01) < 0.01) return '';
        if (Math.abs(normalized - 20) < 0.01) return '20';
        if (Math.abs(normalized - 50) < 0.01) return '50';
        if (Math.abs(normalized - 100) < 0.01) return '100';
        return normalized > 0 ? 'custom' : '';
    }

    function isCustomBolaoLaunchEntry(entryKey) {
        return String(entryKey || '') === 'custom';
    }

    function getBolaoLaunchLeagueModalidadeId(league) {
        return String(league?.modalidade_id || league?.modalidade?.id || '');
    }

    function getBolaoLaunchLeagueModalidadeName(league) {
        const fromLeague = String(league?.modalidade?.nome || league?.modalidade_nome || '').trim();
        if (fromLeague) return fromLeague;

        const contextName = String(window.RR_HUB_CONTEXT?.modalidade_nome || '').trim();
        if (contextName) return contextName;

        const fallbackId = String(league?.modalidade_id || league?.modalidade?.id || '').trim();
        if (fallbackId) return `Modalidade ${fallbackId}`;

        return 'Modalidade';
    }

    function getBolaoLaunchLeagueById(leagueId) {
        const normalizedId = Number(leagueId || 0);
        if (!Number.isFinite(normalizedId) || normalizedId <= 0) {
            return null;
        }
        return bolaoLaunchLeaguesById.get(normalizedId) || null;
    }

    function getBolaoLaunchSortedLeagues(source) {
        return (Array.isArray(source) ? source.slice() : []).sort((left, right) => {
            const leftPrice = parseFloat(left?.price || left?.entry_price || 0) || 0;
            const rightPrice = parseFloat(right?.price || right?.entry_price || 0) || 0;
            if (leftPrice !== rightPrice) return leftPrice - rightPrice;

            const leftPrize = parseFloat(left?.total_prize || left?.manual_prize_pool || 0) || 0;
            const rightPrize = parseFloat(right?.total_prize || right?.manual_prize_pool || 0) || 0;
            if (leftPrize !== rightPrize) return rightPrize - leftPrize;

            const leftId = Number(left?.id || 0);
            const rightId = Number(right?.id || 0);
            return leftId - rightId;
        });
    }

    function getBolaoLaunchFilteredLeagues() {
        const selectedModalidadeId = String(bolaoLaunchSelectedModalidadeId || '').trim();
        if (!selectedModalidadeId) {
            return getBolaoLaunchSortedLeagues(bolaoLaunchPool);
        }

        const modalidadeLeagues = bolaoLaunchPool.filter((league) => {
            const leagueModalidadeId = getBolaoLaunchLeagueModalidadeId(league) || '__default__';
            return leagueModalidadeId === selectedModalidadeId;
        });

        return getBolaoLaunchSortedLeagues(modalidadeLeagues);
    }

    function closeBolaoModalidadePicker() {
        if (!bolaoModalidadePickerWrap || !bolaoModalidadePickerMenu || !bolaoModalidadePickerBtn) return;
        bolaoModalidadePickerWrap.classList.remove('is-open');
        bolaoModalidadePickerBtn.setAttribute('aria-expanded', 'false');
        bolaoModalidadePickerMenu.hidden = true;
        if (bolaoModalidadePickerMenu.parentElement !== bolaoModalidadePickerWrap) {
            bolaoModalidadePickerWrap.appendChild(bolaoModalidadePickerMenu);
        }
    }

    function positionBolaoModalidadePickerMenu() {
        if (!bolaoModalidadePickerBtn || !bolaoModalidadePickerMenu) return;
        if (bolaoModalidadePickerMenu.parentElement !== document.body) {
            document.body.appendChild(bolaoModalidadePickerMenu);
        }
        bolaoModalidadePickerMenu.classList.add('is-fixed');
        const rect = bolaoModalidadePickerBtn.getBoundingClientRect();
        const viewportWidth = Math.max(window.innerWidth || 0, 320);
        const desiredWidth = Math.min(Math.max(rect.width, 220), Math.floor(viewportWidth * 0.92));
        const left = Math.min(
            Math.max(8, rect.left + (rect.width / 2) - (desiredWidth / 2)),
            Math.max(8, viewportWidth - desiredWidth - 8)
        );
        const top = Math.max(8, rect.bottom + 8);

        bolaoModalidadePickerMenu.style.setProperty('--rr-bolao-modalidade-menu-top', `${top}px`);
        bolaoModalidadePickerMenu.style.setProperty('--rr-bolao-modalidade-menu-left', `${left}px`);
        bolaoModalidadePickerMenu.style.setProperty('--rr-bolao-modalidade-menu-width', `${desiredWidth}px`);
    }

    function setBolaoLaunchModalidade(modalidadeId) {
        bolaoLaunchSelectedModalidadeId = String(modalidadeId || '').trim();
        updateBolaoLaunchButtonsState();
        renderBolaoLaunchModalidadePicker();
    }

    function renderBolaoLaunchModalidadePicker() {
        enforceBolaoLaunchLegacyFiltersHidden();

        if (!bolaoModalidadePickerWrap || !bolaoModalidadePickerMenu || !bolaoModalidadePickerBtn || !bolaoModalidadePickerLabel) {
            return;
        }

        const modalidadesMap = new Map();
        bolaoLaunchPool.forEach((league) => {
            const modalidadeId = getBolaoLaunchLeagueModalidadeId(league) || '__default__';
            const modalidadeName = getBolaoLaunchLeagueModalidadeName(league);
            if (!modalidadeId || !modalidadeName || modalidadesMap.has(modalidadeId)) return;
            modalidadesMap.set(modalidadeId, modalidadeName);
        });

        const modalidadeOptions = Array.from(modalidadesMap.entries())
            .sort((left, right) => left[1].localeCompare(right[1], 'pt-BR'))
            .map(([id, label]) => ({ id: String(id), label: String(label) }));

        if (!modalidadeOptions.length && bolaoLaunchPool.length > 0) {
            modalidadeOptions.push({
                id: '__default__',
                label: getBolaoLaunchLeagueModalidadeName(bolaoLaunchPool[0]),
            });
        }

        if (!modalidadeOptions.length) {
            bolaoModalidadePickerWrap.hidden = false;
            bolaoModalidadePickerBtn.disabled = true;
            bolaoModalidadePickerBtn.setAttribute('aria-disabled', 'true');
            bolaoModalidadePickerMenu.innerHTML = '';
            closeBolaoModalidadePicker();
            return;
        }

        bolaoModalidadePickerWrap.hidden = false;
        bolaoModalidadePickerBtn.disabled = false;
        bolaoModalidadePickerBtn.removeAttribute('aria-disabled');

        if (bolaoLaunchSelectedModalidadeId && !modalidadesMap.has(bolaoLaunchSelectedModalidadeId)) {
            bolaoLaunchSelectedModalidadeId = '';
        }

        if (!bolaoLaunchSelectedModalidadeId && modalidadeOptions.length) {
            const preferredModalidadeId = String(window.RR_HUB_CONTEXT?.modalidade_id || '').trim();
            if (preferredModalidadeId && modalidadesMap.has(preferredModalidadeId)) {
                bolaoLaunchSelectedModalidadeId = preferredModalidadeId;
            } else {
                bolaoLaunchSelectedModalidadeId = modalidadeOptions[0].id;
            }
        }

        const activeOption = modalidadeOptions.find((option) => option.id === bolaoLaunchSelectedModalidadeId) || null;
        bolaoModalidadePickerLabel.textContent = activeOption
            ? activeOption.label
            : bolaoLaunchModalidadePlaceholder;

        const menuOptions = modalidadeOptions.map((option) => {
            const isActive = option.id === bolaoLaunchSelectedModalidadeId;
            return `
                <button type="button" class="rr-inicio-event-call__mobile-selector-option${isActive ? ' is-active' : ''}" data-bolao-modalidade-option="${option.id}" role="option" aria-selected="${isActive ? 'true' : 'false'}">
                    <span>${escapeHtml(option.label)}</span>
                    ${isActive ? '<i class="fas fa-check"></i>' : ''}
                </button>
            `;
        }).join('');

        bolaoModalidadePickerMenu.innerHTML = menuOptions;
        closeBolaoModalidadePicker();
    }

    function applyBolaoLaunchCardTone(card, league) {
        if (!card) return;

        card.classList.remove(
            'rr-bolao-launch-simple__btn--20',
            'rr-bolao-launch-simple__btn--50',
            'rr-bolao-launch-simple__btn--100',
            'rr-bolao-launch-simple__btn--custom'
        );

        const fallbackEntryKey = String(card.dataset.bolaoLaunchCard || '');
        const toneKey = league
            ? (mapLaunchBolaoEntry(league.price || league.entry_price || 0) || 'custom')
            : fallbackEntryKey;

        const toneClass = toneKey === '20'
            ? 'rr-bolao-launch-simple__btn--20'
            : toneKey === '50'
                ? 'rr-bolao-launch-simple__btn--50'
                : toneKey === '100'
                    ? 'rr-bolao-launch-simple__btn--100'
                    : 'rr-bolao-launch-simple__btn--custom';

        card.classList.add(toneClass);
    }

    function updateBolaoLaunchButtonsState() {
        const root = document.getElementById('rrBolaoLaunchSimple');
        if (!root) return;

        const statusEl = document.getElementById('rrBolaoLaunchStatus');
        let availableCount = 0;
        const leaguesForDisplay = getBolaoLaunchFilteredLeagues().slice(0, 4);

        root.querySelectorAll('[data-bolao-launch-card]').forEach((card, index) => {
            const entryKey = String(card.dataset.bolaoLaunchCard || '');
            const league = leaguesForDisplay[index] || null;
            const priceEl = card.querySelector('.rr-bolao-launch-simple__price');
            const priceValueEl = card.querySelector('.rr-bolao-launch-simple__price-value');
            const priceLabelEl = card.querySelector('.rr-bolao-launch-simple__price-label');
            const priceLabelMainEl = priceLabelEl?.querySelector('.rr-bolao-launch-simple__price-label-main');
            const priceLabelSubEl = priceLabelEl?.querySelector('.rr-bolao-launch-simple__price-label-sub');
            const kicker = card.querySelector('.rr-bolao-launch-simple__kicker');
            const slotsBadge = card.querySelector('[data-bolao-launch-slots]');
            const bgLogoEl = card.querySelector('[data-bolao-launch-bg-logo]');
            const teamAction = card.querySelector('[data-bolao-launch-action="team"]');
            const rankingAction = card.querySelector('[data-bolao-launch-action="ranking"]');
            const isCustomCard = isCustomBolaoLaunchEntry(entryKey);
            card.hidden = false;
            applyBolaoLaunchCardTone(card, league);

            if (league) {
                availableCount += 1;
                card.dataset.disabled = '0';
                card.dataset.leagueId = String(league.id || '');
                const rankingOnly = league.registration_status === 'closed' || league.is_full || league.status === 'finalized' || league.status === 'finished';
                if (teamAction) {
                    teamAction.disabled = !!rankingOnly;
                    teamAction.innerHTML = '<span>Entrar</span> <i class="fas fa-arrow-right"></i>';
                }
                if (rankingAction) {
                    rankingAction.disabled = false;
                    rankingAction.innerHTML = '<span>Ranking</span> <i class="fas fa-trophy"></i>';
                }
                if (bgLogoEl) {
                    const logoUrl = getBolaoLaunchLogoUrl(league);
                    bgLogoEl.src = logoUrl;
                    bgLogoEl.alt = '';
                    bgLogoEl.hidden = false;
                    bgLogoEl.onerror = function () {
                        this.onerror = null;
                        this.src = '{{ asset("assets/images/logo_icon/logo.png") }}';
                    };
                }
                if (priceEl && priceValueEl) {
                    const prizeLabel = getBolaoLaunchPrizeLabel(league);
                    const unlimited = isLeagueUnlimited(league);
                    priceEl.classList.toggle('is-unlimited', unlimited);
                    priceValueEl.classList.remove('has-odometer');
                    if (prizeLabel.mode === 'currency') {
                        renderBolaoLaunchPrizeOdometer(priceValueEl, prizeLabel.amount);
                    } else {
                        priceValueEl.textContent = prizeLabel.text || '--';
                    }
                    if (priceLabelEl) {
                        const contextLabel = getBolaoLaunchContextParts(league);
                        priceLabelEl.setAttribute('title', contextLabel.combined);
                        if (priceLabelMainEl) {
                            priceLabelMainEl.textContent = contextLabel.rodeioName || contextLabel.combined;
                        } else {
                            priceLabelEl.textContent = contextLabel.combined;
                        }
                        if (priceLabelSubEl) {
                            priceLabelSubEl.textContent = contextLabel.modalidadeName || '';
                        }
                    }
                }
            if (kicker) {
                if (isLeagueUnlimited(league)) {
                    kicker.textContent = 'Entrada ilimitada';
                } else {
                    const leagueEntryValue = parseFloat(league.price || league.entry_price || 0) || 0;
                        kicker.textContent = leagueEntryValue > 0
                            ? `Entrada: ${formatBRL(leagueEntryValue).replace('R$ ', '')}`
                        : 'Entrada personalizada';
                }
            }
            if (slotsBadge) {
                slotsBadge.textContent = getLeagueCapacityLabel(league);
            }
        } else {
            card.dataset.disabled = '1';
            delete card.dataset.leagueId;
            if (teamAction) {
                teamAction.disabled = true;
                    teamAction.innerHTML = '<span>Entrar</span> <i class="fas fa-arrow-right"></i>';
                }
                if (rankingAction) {
                    rankingAction.disabled = true;
                    rankingAction.innerHTML = '<span>Ranking</span> <i class="fas fa-trophy"></i>';
                }
                if (bgLogoEl) {
                    bgLogoEl.src = '{{ asset("assets/images/logo_icon/logo.png") }}';
                    bgLogoEl.alt = '';
                }
                if (priceEl && priceValueEl) {
                    priceEl.classList.remove('is-unlimited');
                    priceValueEl.classList.remove('has-odometer');
                    priceValueEl.textContent = '--';
                    if (priceLabelEl) {
                        priceLabelEl.setAttribute('title', 'Bolão aberto');
                        if (priceLabelMainEl) {
                            priceLabelMainEl.textContent = 'Bolão aberto';
                        } else {
                            priceLabelEl.textContent = 'Bolão aberto';
                        }
                        if (priceLabelSubEl) {
                            priceLabelSubEl.textContent = '';
                        }
                    }
            }
            if (kicker) {
                kicker.textContent = isCustomCard ? 'Entrada personalizada' : 'Bolão aguardando';
            }
            if (slotsBadge) {
                slotsBadge.textContent = '0/0';
            }
        }
    });

        if (statusEl) {
            if (availableCount > 0) {
                statusEl.dataset.tone = 'success';
                const selectedModalidade = bolaoModalidadePickerLabel ? bolaoModalidadePickerLabel.textContent.trim() : '';
                statusEl.textContent = selectedModalidade
                    ? `Bolões de ${selectedModalidade} disponíveis.`
                    : 'Escolha um bolão e entre agora.';
            } else {
                statusEl.dataset.tone = 'error';
                statusEl.textContent = 'Nenhum bolão disponível para a modalidade selecionada agora.';
            }
        }

        initBolaoLaunchCardFx();
    }

    function initBolaoLaunchCardFx() {
        const root = document.getElementById('rrBolaoLaunchSimple');
        if (!root) return;

        const hasHoverPointer = window.matchMedia
            ? window.matchMedia('(hover: hover) and (pointer: fine)').matches
            : true;

        root.querySelectorAll('[data-bolao-launch-card]').forEach((card) => {
            if (!(card instanceof HTMLElement)) return;
            if (card.dataset.rrLaunchFxBound === '1') return;

            card.dataset.rrLaunchFxBound = '1';

            const resetCardFx = () => {
                card.style.setProperty('--rr-bolao-pointer-x', '50%');
                card.style.setProperty('--rr-bolao-pointer-y', '18%');
                card.style.setProperty('--rr-bolao-tilt-x', '0deg');
                card.style.setProperty('--rr-bolao-tilt-y', '0deg');
                card.style.setProperty('--rr-bolao-lift', '0px');
            };

            resetCardFx();

            if (!hasHoverPointer) {
                return;
            }

            card.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                if (!rect.width || !rect.height) return;

                const x = Math.min(Math.max((event.clientX - rect.left) / rect.width, 0), 1);
                const y = Math.min(Math.max((event.clientY - rect.top) / rect.height, 0), 1);
                const tiltY = ((x - 0.5) * 10).toFixed(2);
                const tiltX = ((0.5 - y) * 8).toFixed(2);

                card.style.setProperty('--rr-bolao-pointer-x', `${(x * 100).toFixed(2)}%`);
                card.style.setProperty('--rr-bolao-pointer-y', `${(y * 100).toFixed(2)}%`);
                card.style.setProperty('--rr-bolao-tilt-x', `${tiltX}deg`);
                card.style.setProperty('--rr-bolao-tilt-y', `${tiltY}deg`);
                card.style.setProperty('--rr-bolao-lift', card.dataset.disabled === '1' ? '0px' : '-6px');
            });

            card.addEventListener('pointerleave', resetCardFx);
            card.addEventListener('pointercancel', resetCardFx);
        });
    }

    function getBolaoLaunchPrizeLabel(league) {
        if (!league || typeof league !== 'object') {
            return { text: '', mode: 'text', amount: null };
        }

        if ((league.scoring_mode || '').toLowerCase() === 'points') {
            return { text: 'Pontos', mode: 'text', amount: null };
        }

        const prizeAmount = getBolaoLaunchPrizeAmount(league);
        if (prizeAmount !== null) {
            return {
                text: formatBRL(prizeAmount),
                mode: 'currency',
                amount: prizeAmount,
            };
        }

        return { text: '', mode: 'text', amount: null };
    }

    async function loadBolaoLaunchOptions() {
        const root = document.getElementById('rrBolaoLaunchSimple');
        if (!root) return;

        const statusEl = document.getElementById('rrBolaoLaunchStatus');
        if (statusEl) {
            statusEl.dataset.tone = '';
            statusEl.textContent = 'Carregando bolões do evento...';
        }

        bolaoLaunchLeaguesById.clear();
        bolaoLaunchPool.length = 0;

        try {
            const appendLeague = (league) => {
                const leagueId = Number(league?.id || 0);
                if (!Number.isFinite(leagueId) || leagueId <= 0) return;
                if (league?.is_premium) return;
                if (bolaoLaunchLeaguesById.has(leagueId)) return;
                bolaoLaunchLeaguesById.set(leagueId, league);
                bolaoLaunchPool.push(league);
            };

            if (Array.isArray(lastLoadedBolaoLeagues) && lastLoadedBolaoLeagues.length) {
                lastLoadedBolaoLeagues.forEach((league) => {
                    appendLeague(league);
                });
            }

            const context = window.RR_HUB_CONTEXT || {};
            const attempts = [];
            const seenKeys = new Set();
            const queueAttempt = (params) => {
                const queryKey = JSON.stringify(params);
                if (seenKeys.has(queryKey)) return;
                seenKeys.add(queryKey);
                attempts.push(params);
            };

            if (Number(context.modalidade_id || 0) > 0) {
                queueAttempt({
                    modalidade_id: String(context.modalidade_id),
                    rodeio_id: Number(context.rodeio_id || 0) > 0 ? String(context.rodeio_id) : '',
                    divisao: String(context.divisao || ''),
                });
            }

            if (Number(context.rodeio_id || 0) > 0) {
                queueAttempt({
                    rodeio_id: String(context.rodeio_id),
                    divisao: String(context.divisao || ''),
                });
                queueAttempt({
                    rodeio_id: String(context.rodeio_id),
                });
            }

            queueAttempt({});

            for (const attempt of attempts) {
                const params = new URLSearchParams({ only_active: '1', _ts: String(Date.now()) });
                Object.entries(attempt).forEach(([key, value]) => {
                    if (value !== null && value !== undefined && String(value).trim() !== '') {
                        params.set(key, String(value));
                    }
                });

                const response = await fetch('/api/fantasy/leagues?' + params.toString(), {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await response.json();

                if (!data.success || !Array.isArray(data.data)) {
                    continue;
                }

                data.data.forEach((league) => {
                    appendLeague(league);
                });
            }
        } catch (error) {
            console.error('Erro ao carregar bolões do lançamento:', error);
            if (statusEl) {
                statusEl.dataset.tone = 'error';
                statusEl.textContent = 'Não foi possível carregar os bolões agora.';
            }
        }

        renderBolaoLaunchModalidadePicker();
        updateBolaoLaunchButtonsState();
    }

    function createBolaoCard(league) {
        const card = document.createElement('article');
        card.className = 'rr-bolao-card';
        card.dataset.leagueId = league.id;
        card.dataset.modalidadeId = String(league.modalidade_id || league.modalidade?.id || '');
        card.dataset.rodeioId = String(league.rodeio_id || league.rodeio?.id || '');
        card.dataset.divisao = String(league.divisao || '');

        // Determinar classe de borda por tipo/preço
        let borderClass = '';
        const price = parseFloat(league.price || 0);
        if (league.is_premium) {
            borderClass = 'bolao--premium';
        } else if (price <= 20) {
            borderClass = 'bolao--20';
        } else if (price <= 50) {
            borderClass = 'bolao--50';
        } else {
            borderClass = 'bolao--100';
        }
        
        card.classList.add(borderClass);

        // Imagem de fundo: sempre usar a logo/foto do rodeio
        const imageUrl = league.rodeio?.foto || league.rodeio?.logo_url || league.rodeio?.logo || '/assets/images/logo_icon/logo.png';
        
        // Cálculo do Prêmio Total
        let prizeDisplay;
        if (league.is_premium && league.manual_prize_pool) {
            // Premium com prêmio manual
            prizeDisplay = formatBRL(parseFloat(league.manual_prize_pool));
        } else if (league.is_premium) {
            // Premium sem prêmio (apenas pontos)
            prizeDisplay = 'Pontos';
        } else if (league.total_prize && parseFloat(league.total_prize) > 0) {
            // Prêmio total pré-calculado pelo servidor (baseado em max_users)
            prizeDisplay = formatBRL(parseFloat(league.total_prize));
        } else {
            // Fallback: calcula prêmio com max_users (ou 200 se não definido)
            const price = parseFloat(league.price || 0);
            const houseCut = parseFloat(league.house_cut_percent || 0);
            const metaParticipants = league.max_users ? parseInt(league.max_users) : 200;
            
            const totalPool = metaParticipants * price;
            const houseTake = totalPool * (houseCut / 100);
            const prize = Math.max(0, totalPool - houseTake);
            
            prizeDisplay = formatBRL(prize);
        }

        const entryValue = parseFloat(league.price || league.entry_price || 0) || 0;
        const hasEligibleVoucher = !!league.has_eligible_voucher && !league.is_premium && entryValue > 0;
        card.dataset.quickFilterBucket = getQuickFilterBucket(entryValue);
        card.dataset.entryValue = String(entryValue);
        const entryDisplay = league.is_premium
            ? 'Premium'
            : (entryValue > 0 ? formatBRL(entryValue) : 'Grátis');
        const teamsCountLabel = getLeagueTeamsLabel(league);
        const unlimited = isLeagueUnlimited(league);
        const leagueName = escapeHtml(league.name || 'Bolão');
        const rodeioName = escapeHtml(league.rodeio?.nome || '');
        const modalidadeName = escapeHtml(league.modalidade?.nome || '');
        // Status
        let status = '';
        let statusClass = '';
        if (league.registration_status === 'closed') {
            status = 'Encerrado';
            statusClass = 'rr-bolao-status--closed';
        } else if (league.is_full) {
            status = 'Lotado';
            statusClass = 'rr-bolao-status--full';
        } else {
            status = 'Aberto';
            statusClass = 'rr-bolao-status--open';
        }

        // Botão
        const rankingOnly = league.registration_status === 'closed' || league.is_full || league.status === 'finalized' || league.status === 'finished';
        const entryButtonClass = hasEligibleVoucher ? ' rr-bolao-btn--voucher-ready' : '';
        const deadlineMs = getLeagueDeadlineMs(league);
        const timerText = formatCountdown((deadlineMs ?? NaN) - Date.now());

        card.innerHTML = `
            <div class="rr-bolao-shell">
                <div class="rr-bolao-content">
                    <div class="rr-bolao-bg" aria-hidden="true">
                        <img src="${imageUrl}" alt="" class="rr-bolao-bg-image" loading="lazy" onerror="this.onerror=null;this.src='/assets/images/logo_icon/logo.png';">
                    </div>
                    <div class="rr-bolao-chip-row">
                        ${rodeioName ? `<span class="rr-bolao-chip"><i class="fas fa-map-marker-alt"></i>${rodeioName}</span>` : ''}
                        ${modalidadeName ? `<span class="rr-bolao-chip"><i class="fas fa-trophy"></i>${modalidadeName}</span>` : ''}
                    </div>
                    <div class="rr-bolao-prize">
                        <div class="rr-bolao-prize-label">${unlimited ? 'Prêmio Atual' : 'Prêmio Total'}</div>
                        <div class="rr-bolao-prize-value">${prizeDisplay}</div>
                    </div>
                    <div class="rr-bolao-facts">
                        <div class="rr-bolao-fact">
                            <span class="rr-bolao-fact-label">Entrada</span>
                            <strong class="rr-bolao-fact-value">${entryDisplay}</strong>
                        </div>
                        <div class="rr-bolao-fact">
                            <span class="rr-bolao-fact-label">${unlimited ? 'Arena' : 'Equipes'}</span>
                            <strong class="rr-bolao-fact-value">${escapeHtml(teamsCountLabel)}</strong>
                        </div>
                    </div>
                    ${unlimited ? '<div class="rr-bolao-voucher-note"><i class="fas fa-chart-line"></i><span>Prêmio acumula e a faixa paga pode expandir</span></div>' : ''}
                    <div class="rr-bolao-deadline">
                        <i class="fas fa-hourglass-half"></i>
                        <span class="rr-bolao-timer" data-deadline-ms="${Number.isFinite(deadlineMs) ? deadlineMs : ''}">Termina em: ${timerText}</span>
                    </div>
                    ${hasEligibleVoucher ? '<div class="rr-bolao-voucher-note"><i class="fas fa-ticket-alt"></i><span>Voucher pronto para este bolão</span></div>' : ''}
                    <div class="rr-bolao-actions">
                        <button class="rr-bolao-btn${entryButtonClass}" ${rankingOnly ? 'disabled' : ''} data-action="open-draft-team">
                            <i class="fas ${hasEligibleVoucher ? 'fa-ticket-alt' : 'fa-bolt'}"></i>
                            <span>Entrar</span>
                        </button>
                        <button class="rr-bolao-btn rr-bolao-btn--ranking" data-action="open-draft-ranking">
                            <i class="fas fa-trophy"></i>
                            <span>Ranking</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        card.querySelector('[data-action="open-draft-team"]').addEventListener('click', () => {
            if (!isAuthenticated) {
                promptDraftAuthentication();
                return;
            }
            openDraft(league, { initialPanel: rankingOnly ? 'ranking' : 'team' });
        });

        card.querySelector('[data-action="open-draft-ranking"]').addEventListener('click', () => {
            if (!isAuthenticated) {
                promptDraftAuthentication();
                return;
            }
            openDraft(league, { initialPanel: 'ranking' });
        });

        return card;
    }

    function clearVoucherReadyStateForLeague(leagueId) {
        const normalizedLeagueId = Number(leagueId || 0);
        if (!Number.isFinite(normalizedLeagueId) || normalizedLeagueId <= 0) {
            return;
        }

        if (window.currentDraftLeague && Number(window.currentDraftLeague.id || 0) === normalizedLeagueId) {
            window.currentDraftLeague.has_eligible_voucher = false;
            window.currentDraftLeague.eligible_voucher_credit = null;
        }

        document.querySelectorAll(`.rr-bolao-card[data-league-id="${normalizedLeagueId}"]`).forEach((card) => {
            card.querySelector('.rr-bolao-voucher-note')?.remove();

            const teamButton = card.querySelector('[data-action="open-draft-team"]');
            if (!teamButton) {
                return;
            }

            teamButton.classList.remove('rr-bolao-btn--voucher-ready');
            teamButton.innerHTML = '<i class="fas fa-bolt"></i><span>Entrar</span>';
        });
    }

    function formatBRL(value) {
        return 'R$ ' + parseFloat(value).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function lockDraftBackground() {
        if (document.body.classList.contains('rr-draft-body-lock')) {
            return;
        }

        const scrollY = window.scrollY || window.pageYOffset || 0;
        window.__rrDraftScrollY = scrollY;
        window.__rrDraftBodyLock = {
            overflow: document.body.style.overflow || '',
            position: document.body.style.position || '',
            top: document.body.style.top || '',
            left: document.body.style.left || '',
            right: document.body.style.right || '',
            width: document.body.style.width || '',
        };

        document.body.classList.add('rr-draft-lock', 'rr-draft-body-lock');
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
    }

    function unlockDraftBackground() {
        if (!document.body.classList.contains('rr-draft-body-lock')) {
            return;
        }

        const previous = window.__rrDraftBodyLock || {};
        const scrollY = Number(window.__rrDraftScrollY || 0);

        document.body.classList.remove('rr-draft-lock', 'rr-draft-body-lock');
        document.body.style.overflow = previous.overflow || '';
        document.body.style.position = previous.position || '';
        document.body.style.top = previous.top || '';
        document.body.style.left = previous.left || '';
        document.body.style.right = previous.right || '';
        document.body.style.width = previous.width || '';
        window.scrollTo(0, scrollY);
    }

    function promptDraftAuthentication() {
        closeDraft();

        const openAuth = () => {
            if (typeof window.openAuthModal === 'function') {
                window.openAuthModal();
                return;
            }

            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
                return;
            }

            window.location.href = '{{ route("user.login") }}';
        };

        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(openAuth);
            return;
        }

        setTimeout(openAuth, 0);
    }

    function setDraftPanel(panelName = 'team') {
        const teamPanel = document.querySelector('.rr-draft-panel[data-panel="team"]');
        const rankingPanel = document.querySelector('.rr-draft-panel[data-panel="ranking"]');
        const isRanking = panelName === 'ranking';

        if (teamPanel) {
            teamPanel.hidden = isRanking;
            teamPanel.style.display = isRanking ? 'none' : '';
            teamPanel.classList.toggle('active', !isRanking);
        }

        if (rankingPanel) {
            rankingPanel.hidden = !isRanking;
            rankingPanel.style.display = isRanking ? '' : 'none';
            rankingPanel.classList.toggle('active', isRanking);
        }
    }

    function openDraft(league, options = {}) {
        console.log('Abrindo draft para bolão:', league);

        if (!isAuthenticated) {
            promptDraftAuthentication();
            return;
        }

        const rankingOnly = !!options.rankingOnly;
        const initialPanel = options.initialPanel === 'ranking' || rankingOnly ? 'ranking' : 'team';

        // Armazenar dados da liga
        window.currentDraftLeague = league;
        window.currentDraftPrizePool =
            parseFloat(league.total_prize || league.prize_pool || league.manual_prize_pool || 0) || 0;
        window.currentDraftEntry =
            league.is_premium ? 0 : parseFloat(league.price || league.entry_price || 0) || 0;
        
        updateDraftLeagueSummary(league);
        setDraftHint(
            initialPanel === 'ranking'
                ? 'Confira o ranking atual deste bolão.'
                : getDraftDefaultHint(league),
            initialPanel === 'ranking' ? 'warn' : ''
        );
        
        // Resetar estado
        window.draftState = {
            maxTeamSize: 4,
            selectedCompetitors: [],
            allCompetitors: [],
            existingTeams: [],
            searchTerm: ''
        };
        
        // Atualizar UI
        updateDraftSelectionSummary();
        updateDraftTeam();

        setDraftPanel(initialPanel);

        // Carregar competidores
        if (initialPanel === 'team') {
            loadDraftCompetitors(league.id);
        }
        
        // Carregar ranking
        loadDraftRanking(league.id);
        
        // Mover modal para body para escapar do stacking context
        var draftModal = document.getElementById('rrDraftModal');
        if (!draftModalHomeParent) {
            draftModalHomeParent = draftModal.parentElement || null;
            draftModalHomeNextSibling = draftModal.nextSibling || null;
        }
        if (draftModal.parentElement !== document.body) {
            document.body.appendChild(draftModal);
        }
        // Abrir modal
        draftModal.hidden = false;
        draftModal.style.display = '';
        draftModal.classList.add('is-open');
        draftModal.scrollTop = 0;
        const draftContainer = draftModal.querySelector('.rr-draft-container');
        if (draftContainer) {
            draftContainer.scrollTop = 0;
        }
        lockDraftBackground();
    }

    function closeDraft() {
        var draftModal = document.getElementById('rrDraftModal');
        draftModal.classList.remove('is-open');
        draftModal.hidden = true;
        draftModal.style.display = 'none';
        if (draftModalHomeParent && draftModal.parentElement === document.body) {
            if (draftModalHomeNextSibling && draftModalHomeNextSibling.parentNode === draftModalHomeParent) {
                draftModalHomeParent.insertBefore(draftModal, draftModalHomeNextSibling);
            } else {
                draftModalHomeParent.appendChild(draftModal);
            }
        }
        closeDraftPixModal();
        unlockDraftBackground();
        setDraftPanel('team');
        window.currentDraftLeague = null;
        window.draftState = null;
    }

    async function loadDraftCompetitors(leagueId) {
        const grid = document.getElementById('rrDraftCompetitors');
        const searchInput = document.getElementById('rrDraftCompetitorSearch');
        grid.innerHTML = '<div class="rr-draft-empty-state">Carregando competidores...</div>';
        if (searchInput) {
            searchInput.value = '';
        }
        if (window.draftState) {
            window.draftState.searchTerm = '';
        }
        
        try {
            const response = await fetch(`/api/fantasy/leagues/${leagueId}/available-competitors`);
            const data = await response.json();
            
            if (!data.success || !data.data) {
                throw new Error('Falha ao carregar competidores');
            }
            
            window.draftState.allCompetitors = data.data;
            window.draftState.existingTeams = Array.isArray(data.meta?.user_active_teams)
                ? data.meta.user_active_teams
                : [];
            updateDraftActionState();
            renderDraftCompetitors();
            
        } catch (error) {
            console.error('Erro ao carregar competidores:', error);
            grid.innerHTML = '<div class="rr-draft-empty-state" style="color:#fca5a5;">Erro ao carregar competidores.</div>';
        }
    }

    function renderDraftCompetitors() {
        const grid = document.getElementById('rrDraftCompetitors');
        const competitors = window.draftState.allCompetitors;
        const searchTerm = String(window.draftState.searchTerm || '').trim().toLowerCase();
        
        if (!competitors || competitors.length === 0) {
            grid.innerHTML = '<div class="rr-draft-empty-state">Nenhum competidor disponível no momento.</div>';
            return;
        }

        const filteredCompetitors = competitors.filter((comp) => {
            if (!searchTerm) return true;
            const fullName = String(comp.nome || comp.name || 'Competidor').toLowerCase();
            return fullName.includes(searchTerm);
        });

        if (filteredCompetitors.length === 0) {
            grid.innerHTML = '<div class="rr-draft-empty-state">Nenhum competidor encontrado para essa busca.</div>';
            return;
        }
        
        grid.innerHTML = '';
        
        filteredCompetitors.forEach(comp => {
            const isSelected = window.draftState.selectedCompetitors.some(c => c.id === comp.id);
            const teamFull = window.draftState.selectedCompetitors.length >= window.draftState.maxTeamSize && !isSelected;
            const isDisabled = !isSelected && teamFull;
            
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'rr-draft-competitor-card';
            
            if (isSelected) card.classList.add('selected');
            if (isDisabled) card.classList.add('disabled');
            
            const fullName = comp.nome || comp.name || 'Competidor';
            const shortNameRaw = getDraftShortName(fullName);
            const shortName = shortNameRaw.length > 20 ? `${shortNameRaw.slice(0, 17)}...` : shortNameRaw;
            const photoUrl = getDraftPhotoUrl(comp);

            card.style.backgroundImage = `url('${escapeHtml(photoUrl)}')`;
            card.style.backgroundPosition = 'center center';
            card.style.backgroundSize = 'cover';
            card.style.backgroundRepeat = 'no-repeat';

            card.innerHTML = `
                <span class="rr-draft-competitor__overlay" aria-hidden="true"></span>
                <div class="rr-draft-competitor__body">
                    <div class="rr-draft-competitor__name">${shortName}</div>
                </div>
            `;
            card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
            card.disabled = isDisabled;
            
            if (!isDisabled) {
                card.addEventListener('click', () => toggleCompetitor(comp));
            }
            
            grid.appendChild(card);
        });
    }

    function toggleCompetitor(competitor) {
        const state = window.draftState;
        const index = state.selectedCompetitors.findIndex(c => c.id === competitor.id);
        
        if (index >= 0) {
            state.selectedCompetitors.splice(index, 1);
        } else {
            if (state.selectedCompetitors.length >= state.maxTeamSize) {
                setDraftHint(`Sua equipe já está completa com ${state.maxTeamSize} competidores.`, 'warn');
                return;
            }

            state.selectedCompetitors.push({ ...competitor });
        }
        
        updateDraftSelectionSummary();
        updateDraftTeam();
        renderDraftCompetitors();
        setDraftHint(
            state.selectedCompetitors.length === state.maxTeamSize
                ? 'Equipe pronta. Confirme no rodapé.'
                : 'Escolha os próximos nomes.',
            ''
        );
    }

    function updateDraftSelectionSummary() {
        const state = window.draftState;
        updateDraftActionState();
    }

    function updateDraftTeam() {
        const lists = [
            document.getElementById('rrDraftTeamList'),
            document.getElementById('rrDraftTeamListMobile'),
        ].filter(Boolean);
        const state = window.draftState;
        const team = state.selectedCompetitors;

        lists.forEach((list) => {
            list.innerHTML = '';
            for (let slotIndex = 0; slotIndex < state.maxTeamSize; slotIndex += 1) {
                const member = team[slotIndex] || null;
                const item = document.createElement('div');
                item.className = 'rr-draft-slot' + (member ? ' rr-draft-slot--filled' : '');

                if (member) {
                    const photoUrl = getDraftPhotoUrl(member);
                    const firstName = escapeHtml(getDraftFirstName(member.nome || member.name));

                    item.innerHTML = `
                        <span class="rr-draft-slot__index">${slotIndex + 1}</span>
                        <button class="rr-draft-slot__remove" type="button" aria-label="Remover ${firstName}">×</button>
                        <img src="${photoUrl}" alt="${firstName}" class="rr-draft-slot__photo" loading="lazy">
                        <span class="rr-draft-slot__name">${firstName}</span>
                    `;

                    item.querySelector('.rr-draft-slot__remove').addEventListener('click', () => {
                        toggleCompetitor(member);
                    });
                } else {
                    item.innerHTML = `
                        <span class="rr-draft-slot__index">${slotIndex + 1}</span>
                        <span class="rr-draft-slot__empty">Escolher</span>
                    `;
                }

                list.appendChild(item);
            }
        });
    }

    async function loadDraftRanking(leagueId, opts = {}) {
        const container = document.getElementById('rrDraftRanking');
        const refreshBtn = document.getElementById('rrDraftRankingRefresh');
        const headerRefreshBtn = document.getElementById('rrDraftHeaderRefresh');
        if (!container) return;

        if (refreshBtn) refreshBtn.disabled = true;
        if (headerRefreshBtn) headerRefreshBtn.disabled = true;

        container.innerHTML = `
            <div class="rr-draft-ranking-shell">
                <div class="rr-draft-ranking-empty">
                    <span class="rr-draft-ranking-empty__badge">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Carregando ranking
                    </span>
                    <strong class="rr-draft-ranking-empty__title">Sincronizando ranking completo.</strong>
                    <span class="rr-draft-ranking-empty__text">Buscando todas as posições do bolão e os valores pagos em cada faixa.</span>
                </div>
            </div>
        `;

        try {
            const response = await fetch(`/api/fantasy/leagues/${leagueId}/ranking`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json();

            if (!response.ok || !data.success || !data.data) {
                throw new Error(data.message || 'Falha ao carregar ranking');
            }

            const payload = data.data;
            const ranking = Array.isArray(payload.ranking) ? payload.ranking : [];
            const totalTeams = parseInt(payload.total_teams || ranking.length || 0, 10) || 0;
            const maxUsers = parseInt(payload.max_users || payload.max_players || 0, 10) || null;
            const entryPrice = parseFloat(payload.entry_price || payload.price || 0);
            const houseCut = parseFloat(payload.house_cut_percent || payload.house_cut || 0);
            const prizePoolFromApi = parseFloat(payload.prize_pool || 0);
            const prizePool = prizePoolFromApi > 0
                ? prizePoolFromApi
                : (window.currentDraftPrizePool && window.currentDraftPrizePool > 0
                    ? window.currentDraftPrizePool
                    : Math.max(0, totalTeams * entryPrice * (1 - houseCut / 100)));
            const paidPositions = parseInt(payload.paid_positions || 0, 10) || 0;
            const projectedPaidPositions = parseInt(payload.projected_paid_positions || 0, 10) || 0;
            const displayPaidPositions = parseInt(payload.display_paid_positions || payload.projected_paid_positions || paidPositions || 0, 10) || 0;
            const distribution = payload.distribution && typeof payload.distribution === 'object'
                ? payload.distribution
                : null;

            const projectedPrizeFromLeague = parseFloat(
                window.currentDraftLeague?.total_prize || window.currentDraftLeague?.manual_prize_pool || 0
            ) || 0;

            if (projectedPrizeFromLeague <= 0) {
                window.currentDraftPrizePool = prizePool;
            }
            if (entryPrice > 0) {
                window.currentDraftEntry = entryPrice;
            }
            if (window.currentDraftLeague) {
                window.currentDraftLeague.teams_count = totalTeams;
                window.currentDraftLeague.max_users = maxUsers;
                if (payload && Object.prototype.hasOwnProperty.call(payload, 'is_unlimited')) {
                    window.currentDraftLeague.is_unlimited = payload.is_unlimited;
                }
            }

            renderDraftRanking({
                container,
                ranking,
                totalTeams,
                maxUsers,
                prizePool,
                entryPrice,
                houseCut,
                paidPositions,
                projectedPaidPositions,
                displayPaidPositions,
                distribution,
                myTeams: Array.isArray(payload.my_teams) ? payload.my_teams : [],
                updatedAt: payload.updated_at || null,
            });

            // Atualiza header da modal com prêmio atual
            const infoEl = document.getElementById('rrDraftLeagueInfo');
            if (infoEl && window.currentDraftLeague) {
                updateDraftLeagueSummary(window.currentDraftLeague);
            }
        } catch (error) {
            console.error('Erro ao carregar ranking:', error);
            container.innerHTML = `
                <div class="rr-draft-ranking-empty" style="color:#ef4444;">
                    <span class="rr-draft-ranking-empty__badge"><i class="fas fa-triangle-exclamation"></i> Falha no ranking</span>
                    <strong class="rr-draft-ranking-empty__title">Não foi possível carregar o pódio agora.</strong>
                    <span class="rr-draft-ranking-empty__text">Atualize o ranking novamente em alguns segundos.</span>
                </div>
            `;
        } finally {
            if (refreshBtn) refreshBtn.disabled = false;
            if (headerRefreshBtn) headerRefreshBtn.disabled = false;
        }
    }

    function renderDraftRanking({ container, ranking, totalTeams, maxUsers, prizePool, entryPrice, houseCut, paidPositions, projectedPaidPositions, displayPaidPositions, distribution, myTeams, updatedAt }) {
        const safeRanking = Array.isArray(ranking) ? ranking : [];
        const rankingLeague = {
            ...(window.currentDraftLeague || {}),
            teams_count: totalTeams,
            max_users: maxUsers,
        };
        const unlimited = isLeagueUnlimited(rankingLeague);
        const normalizedRanking = safeRanking
            .map((row, index) => {
                const pos = parseInt(row.position || row.pos || index + 1, 10) || (index + 1);
                return {
                    ...row,
                    __position: pos,
                };
            })
            .sort((a, b) => a.__position - b.__position);
        const rankingByPosition = new Map(
            normalizedRanking.map((row) => [row.__position, row])
        );
        const visiblePaidPositions = Math.max(displayPaidPositions || paidPositions || 0, 3);
        const projectedPayouts = Math.max(projectedPaidPositions || visiblePaidPositions, 3);
        const myPositions = (Array.isArray(myTeams) ? myTeams : [])
            .map((row) => parseInt(row?.position || row?.pos || 0, 10))
            .filter((position) => position > 0)
            .sort((a, b) => a - b);
        const totalSlots = unlimited
            ? Math.max(totalTeams || 0, 3)
            : Math.max(parseInt(maxUsers || 0, 10) || 0, totalTeams || 0, 3);
        prizePoolGlobal = prizePool;

        const podiumRows = [rankingByPosition.get(1) || null, rankingByPosition.get(2) || null, rankingByPosition.get(3) || null];
        const podiumMaxPoints = Math.max(...podiumRows.map(p => Number(p?.points || p?.total_points || p?.score || 0)), 1);
        const podiumHtml = `
            <div class="rr-draft-podium rr-draft-podium--hero">
                ${renderPodiumSlot(rankingByPosition.get(2) || null, 2, podiumMaxPoints, visiblePaidPositions, distribution)}
                ${renderPodiumSlot(rankingByPosition.get(1) || null, 1, podiumMaxPoints, visiblePaidPositions, distribution, true)}
                ${renderPodiumSlot(rankingByPosition.get(3) || null, 3, podiumMaxPoints, visiblePaidPositions, distribution)}
            </div>
        `;

        const listRows = [];
        for (let pos = 4; pos <= totalSlots; pos++) {
            const row = rankingByPosition.get(pos) || null;
            listRows.push(
                row
                    ? renderDraftRankingRow(row, pos, prizePool, visiblePaidPositions, distribution)
                    : renderDraftRankingPlaceholder(pos, prizePool, visiblePaidPositions, distribution)
            );
        }

        const hasMyRankNav = myPositions.some((position) => position > 3);
        const rowsHtml = listRows.length ? `
            <div class="rr-draft-ranking-list-wrap ${hasMyRankNav ? 'rr-draft-ranking-list-wrap--has-nav' : ''}">
                <div class="rr-draft-ranking-list" role="list">
                    ${listRows.join('')}
                </div>
                ${hasMyRankNav ? `
                    <button type="button" class="rr-draft-ranking-nav" id="rrDraftMyRankNav" aria-label="Ir até sua próxima posição no ranking">
                        <i class="fas fa-arrow-down"></i>
                        <span>Minha posição</span>
                    </button>
                ` : ''}
            </div>
        ` : `
            <div class="rr-draft-ranking-empty">
                <span class="rr-draft-ranking-empty__badge"><i class="fas fa-list-ol"></i> Ranking fixo</span>
                <strong class="rr-draft-ranking-empty__title">${unlimited ? 'A arena já está pronta para crescer com novas equipes.' : 'Todas as posições do bolão aparecem aqui em ordem fixa.'}</strong>
                <span class="rr-draft-ranking-empty__text">${unlimited ? `A zona premiada inicial vai até a posição #${projectedPayouts}.` : `As posições pagas estão marcadas com o prêmio correspondente até a posição #${projectedPayouts}.`}</span>
            </div>
        `;

        container.innerHTML = `
            <div class="rr-draft-ranking-shell">
                ${podiumHtml}
                ${rowsHtml}
            </div>
        `;

        setupDraftRankingNavigator(container, myPositions);
    }

    function renderDraftRankingRow(row, pos, prizePool, paidPositions, distribution = null) {
        const name = getDraftRankingDisplayName(row);
        const avatar = renderAvatar(name, row.user_foto, {
            variant: 'mini',
            useUserPhoto: row.show_in_listings !== false,
        });
        const prize = estimatePrize(prizePool, paidPositions, pos, distribution);
        const canViewPoints = row?.can_view_points !== false;
        const points = canViewPoints ? (row.points || row.total_points || row.score || 0) : null;
        const scoreLabel = canViewPoints ? formatFantasyPoints(points) : 'Oculto';

        return `
            <div class="rr-draft-ranking-row ${row?.is_mine ? 'rr-draft-ranking-row--mine' : ''}" role="listitem" data-position="${pos}" data-is-mine="${row?.is_mine ? '1' : '0'}">
                <span class="rr-draft-ranking-row__pos">#${pos}</span>
                <span class="rr-draft-ranking-row__avatar">${avatar}</span>
                <div class="rr-draft-ranking-row__detail">
                    <span class="rr-draft-ranking-row__name">${escapeHtml(name)}</span>
                    <span class="rr-draft-ranking-row__prize ${prize ? '' : 'rr-draft-ranking-row__prize--out'}">
                        ${prize ? 'Prêmio ' + formatBRL(prize) : 'Fora da premiação'}
                    </span>
                </div>
                <span class="rr-draft-ranking-row__score">${escapeHtml(scoreLabel)}</span>
            </div>
        `;
    }

    function renderDraftRankingPlaceholder(pos, prizePool, paidPositions, distribution = null) {
        const prize = estimatePrize(prizePool, paidPositions, pos, distribution);

        return `
            <div class="rr-draft-ranking-row rr-draft-ranking-row--placeholder" role="listitem">
                <span class="rr-draft-ranking-row__pos">#${pos}</span>
                <span class="rr-draft-ranking-row__avatar">${renderAvatar('Equipe', '', { variant: 'mini' })}</span>
                <div class="rr-draft-ranking-row__detail">
                    <span class="rr-draft-ranking-row__name rr-draft-ranking-row__name--placeholder">Aguardando equipe</span>
                    <span class="rr-draft-ranking-row__prize ${prize ? '' : 'rr-draft-ranking-row__prize--out'}">
                        ${prize ? 'Prêmio ' + formatBRL(prize) : 'Fora da premiação'}
                    </span>
                </div>
                <span class="rr-draft-ranking-row__score">vaga</span>
            </div>
        `;
    }

    function renderPodiumSlot(row, place, maxPoints, paidPositions, distribution = null, isChampion = false) {
        const hasRow = !!row;
        const name = hasRow ? getDraftRankingDisplayName(row) : 'Aguardando equipe';
        const canViewPoints = hasRow ? row?.can_view_points !== false : false;
        const points = hasRow && canViewPoints ? (row.points || row.total_points || row.score || 0) : 0;
        const isPremium = hasRow && !!row.is_premium;
        const medal = place === 1 ? '🥇' : place === 2 ? '🥈' : '🥉';
        const placeBadge = place === 1
            ? { icon: 'fa-crown', label: 'Campeão' }
            : place === 2
                ? { icon: 'fa-medal', label: 'Vice-líder' }
                : { icon: 'fa-star', label: 'Top 3' };
        const badgeLabel = hasRow ? placeBadge.label : (place === 1 ? 'Vaga do campeão' : 'Pódio aberto');
        const baseHeight = isChampion ? 196 : place === 2 ? 176 : 170;
        const barHeight = hasRow
            ? (canViewPoints
                ? Math.max(14, Math.min(70, (points / maxPoints) * 70))
                : (isChampion ? 62 : place === 2 ? 52 : 46))
            : 14;
        const cardClass = place === 1 ? 'rr-draft-podium-card--gold' : place === 2 ? 'rr-draft-podium-card--silver' : 'rr-draft-podium-card--bronze';
        const avatar = renderAvatar(name, hasRow ? row.user_foto : '', {
            variant: 'podium',
            useUserPhoto: hasRow && row.show_in_listings !== false,
        });
        const crownHtml = isPremium
            ? '<span class="rr-draft-podium-card__premium-crown" title="Usuário Premium"><i class="fas fa-crown"></i></span>'
            : '';
        const prize = estimatePrize(prizePoolGlobal, paidPositions, place, distribution); // uses global captured below
        const animationDelay = `${place * 0.14}s`;
        const prizeLabel = prize ? formatBRL(prize) : (place <= paidPositions ? 'Abrindo faixa' : 'Sem prêmio');
        const scoreLabel = !hasRow
            ? 'Aguardando time'
            : (canViewPoints ? formatFantasyPoints(points) : 'Pontuação oculta');
        const scoreMeta = !hasRow
            ? 'status'
            : (canViewPoints ? 'pontuação' : 'visível após finalização');
        return `
            <div class="rr-draft-podium-card rr-draft-podium-card--place-${place} ${cardClass} ${isChampion ? 'rr-draft-podium-card--champion' : ''} ${isPremium ? 'rr-draft-podium-card--premium' : ''} ${hasRow ? '' : 'rr-draft-podium-card--placeholder'} ${row?.is_mine ? 'rr-draft-podium-card--mine' : ''}" data-position="${place}" data-is-mine="${row?.is_mine ? '1' : '0'}" style="--rr-draft-podium-min-height:${baseHeight}px;--rr-draft-bar-height:${barHeight}px;--rr-draft-rank-delay:${animationDelay};">
                <div class="rr-draft-podium-card__topline">
                    <div class="rr-draft-podium-card__badge"><i class="fas ${placeBadge.icon}"></i> ${escapeHtml(badgeLabel)}</div>
                    <div class="rr-draft-podium-card__medal">${medal}</div>
                </div>
                <div class="rr-draft-podium-card__scene">
                    <span class="rr-draft-podium-card__orb rr-draft-podium-card__orb--left" aria-hidden="true"><i class="fas fa-gem"></i></span>
                    <div class="rr-draft-podium-card__avatar-wrap">${avatar}</div>
                    <span class="rr-draft-podium-card__orb rr-draft-podium-card__orb--right" aria-hidden="true"><i class="fas fa-trophy"></i></span>
                </div>
                <div class="rr-draft-podium-card__pos">${place}º Lugar</div>
                <div class="rr-draft-podium-card__name-line">
                    <div class="rr-draft-podium-card__name ${hasRow ? '' : 'rr-draft-podium-card__name--placeholder'}">${escapeHtml(name)}</div>
                    ${crownHtml}
                </div>
                <div class="rr-draft-podium-card__stats">
                    <div class="rr-draft-podium-card__pill rr-draft-podium-card__pill--prize">
                        <i class="fas fa-coins"></i>
                        <span class="rr-draft-podium-card__pill-copy">
                            <strong>${escapeHtml(prizeLabel)}</strong>
                            <small>premiação</small>
                        </span>
                    </div>
                    <div class="rr-draft-podium-card__pill rr-draft-podium-card__pill--score">
                        <i class="fas fa-bolt"></i>
                        <span class="rr-draft-podium-card__pill-copy">
                            <strong>${escapeHtml(scoreLabel)}</strong>
                            <small>${escapeHtml(scoreMeta)}</small>
                        </span>
                    </div>
                </div>
                <div class="rr-draft-podium-card__pedestal">
                    <span class="rr-draft-podium-card__pedestal-rank">#${place}</span>
                    <span class="rr-draft-podium-card__pedestal-fill"></span>
                </div>
                <div class="rr-draft-podium-card__bar"></div>
            </div>
        `;
    }

    let prizePoolGlobal = 0;

    function setupDraftRankingNavigator(container, myPositions) {
        const shell = container?.querySelector?.('.rr-draft-ranking-shell');
        const list = shell?.querySelector?.('.rr-draft-ranking-list');
        const button = shell?.querySelector?.('#rrDraftMyRankNav');

        if (!shell || !button || !list || !Array.isArray(myPositions) || !myPositions.length) {
            return;
        }

        const label = button.querySelector('span');
        const icon = button.querySelector('i');
        let delayedSyncTimer = null;

        const getMineRows = () => Array.from(list.querySelectorAll('.rr-draft-ranking-row--mine'))
            .sort((a, b) => (parseInt(a.dataset.position || '0', 10) || 0) - (parseInt(b.dataset.position || '0', 10) || 0));

        const findNextBelow = () => {
            const viewportBottom = list.scrollTop + list.clientHeight;
            return getMineRows().find((row) => (row.offsetTop + row.offsetHeight) > (viewportBottom + 12)) || null;
        };

        const updateButtonState = () => {
            const nextBelow = findNextBelow();
            const mode = nextBelow ? 'down' : 'top';

            button.dataset.mode = mode;
            button.classList.toggle('is-top', mode === 'top');
            button.setAttribute('aria-label', mode === 'top' ? 'Voltar ao topo do ranking' : 'Ir até sua próxima posição no ranking');

            if (label) {
                label.textContent = mode === 'top' ? 'Topo' : 'Minha posição';
            }

            if (icon) {
                icon.className = mode === 'top' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
            }
        };

        const syncButtonState = () => {
            if (delayedSyncTimer) {
                window.clearTimeout(delayedSyncTimer);
            }

            updateButtonState();
            window.requestAnimationFrame(() => {
                updateButtonState();
                window.requestAnimationFrame(updateButtonState);
            });

            delayedSyncTimer = window.setTimeout(updateButtonState, 140);
        };

        button.addEventListener('click', function () {
            const mode = button.dataset.mode || 'down';
            if (mode === 'top') {
                list.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            const nextBelow = findNextBelow();
            if (nextBelow) {
                list.scrollTo({
                    top: Math.max(0, nextBelow.offsetTop - 12),
                    behavior: 'smooth',
                });
                return;
            }

            list.scrollTo({ top: 0, behavior: 'smooth' });
        });

        list.addEventListener('scroll', updateButtonState, { passive: true });
        if (typeof window.ResizeObserver === 'function') {
            const observer = new window.ResizeObserver(syncButtonState);
            observer.observe(list);
            observer.observe(shell);
        }
        syncButtonState();
    }

    function getDraftRankingDisplayName(row) {
        return row?.display_name || row?.user_name || row?.username || row?.name || row?.team_name || 'Usuário';
    }

    function formatFantasyPoints(value) {
        const amount = Number(value || 0);
        return `${amount.toLocaleString('pt-BR', {
            minimumFractionDigits: Number.isInteger(amount) ? 0 : 1,
            maximumFractionDigits: Number.isInteger(amount) ? 0 : 1,
        })} pts`;
    }

    function renderAvatar(name, fotoUrl, options = {}) {
        const logoUrl = '{{ asset("assets/images/logo_icon/logo.png") }}';
        const variant = options?.variant || 'default';
        const useUserPhoto = !!options?.useUserPhoto;
        const normalizedFoto = typeof fotoUrl === 'string' ? fotoUrl.trim() : '';
        const hasPhoto = useUserPhoto && normalizedFoto !== '';
        const avatarClasses = ['rr-draft-avatar'];
        const imageClasses = [];

        if (variant === 'podium') {
            avatarClasses.push('rr-draft-avatar--podium');
        } else if (variant === 'mini') {
            avatarClasses.push('rr-draft-avatar--mini');
        }

        if (hasPhoto) {
            avatarClasses.push('rr-draft-avatar--photo');
            imageClasses.push('rr-draft-avatar__img--photo');
        }

        const source = hasPhoto ? normalizedFoto : logoUrl;
        const onErrorAttr = hasPhoto
            ? ` onerror="this.onerror=null;this.src='${logoUrl}';this.classList.remove('rr-draft-avatar__img--photo');"`
            : '';

        return `<span class="${avatarClasses.join(' ')}"><img src="${escapeHtml(source)}" alt="${escapeHtml(name)}" loading="lazy" class="${imageClasses.join(' ')}"${onErrorAttr}></span>`;
    }

    function estimatePrize(pool, paidPositions, position, distribution = null) {
        prizePoolGlobal = pool;
        if (!pool || !paidPositions || position > paidPositions) return null;
        const normalizedDistribution = distribution && typeof distribution === 'object'
            ? distribution
            : generateDraftPrizeDistribution(paidPositions);
        const prizePercent = Number(normalizedDistribution?.[position] ?? normalizedDistribution?.[String(position)] ?? 0);
        return prizePercent > 0 ? pool * (prizePercent / 100) : null;
    }

    function generateDraftPrizeDistribution(paidPositions) {
        const tiers = generateDraftPrizeTiers(paidPositions);
        const distribution = {};

        tiers.forEach((tier) => {
            const count = tier.to - tier.from + 1;
            const pctPerPerson = roundDraftNumber(tier.pct / Math.max(1, count));
            for (let pos = tier.from; pos <= tier.to; pos++) {
                distribution[pos] = pctPerPerson;
            }
        });

        const sum = Object.values(distribution).reduce((total, value) => total + Number(value || 0), 0);
        if (distribution[1] && Math.abs(sum - 100) > 0.01) {
            distribution[1] = roundDraftNumber(distribution[1] + (100 - sum));
        }

        return distribution;
    }

    function generateDraftPrizeTiers(paidPositions) {
        if (!paidPositions || paidPositions <= 0) return [];
        if (paidPositions === 1) return [{ from: 1, to: 1, pct: 100 }];
        if (paidPositions === 2) return [{ from: 1, to: 1, pct: 65 }, { from: 2, to: 2, pct: 35 }];
        if (paidPositions === 3) return [{ from: 1, to: 1, pct: 50 }, { from: 2, to: 2, pct: 30 }, { from: 3, to: 3, pct: 20 }];

        const tiers = [
            { from: 1, to: 1 },
            { from: 2, to: 2 },
            { from: 3, to: 3 },
        ];

        const remaining = paidPositions - 3;
        let pos = 4;

        if (remaining <= 3) {
            tiers.push({ from: 4, to: paidPositions });
        } else {
            const chunks = remaining <= 8 ? 2 : (remaining <= 20 ? 3 : 4);
            const base = Math.floor(remaining / chunks);
            const extra = remaining - (base * chunks);
            const sizes = [];

            for (let chunk = 0; chunk < chunks; chunk++) {
                sizes.push(base + (chunk < extra ? 1 : 0));
            }

            sizes.sort((a, b) => a - b);

            sizes.forEach((size) => {
                tiers.push({ from: pos, to: pos + size - 1 });
                pos += size;
            });
        }

        const tierCount = tiers.length;
        const floorPctPerPerson = 100 / (paidPositions * 3.6);
        const totalFloor = floorPctPerPerson * paidPositions;
        const curvePool = 100 - totalFloor;
        const spread = Math.max(3, Math.pow(paidPositions, 1.2));
        const ratio = Math.pow(spread, 1 / Math.max(1, tierCount - 1));
        const perPerson = new Array(tierCount).fill(0);

        perPerson[tierCount - 1] = 1;
        for (let i = tierCount - 2; i >= 0; i--) {
            perPerson[i] = perPerson[i + 1] * ratio;
        }

        let totalRaw = 0;
        for (let i = 0; i < tierCount; i++) {
            const count = tiers[i].to - tiers[i].from + 1;
            totalRaw += perPerson[i] * count;
        }

        for (let i = 0; i < tierCount; i++) {
            const count = tiers[i].to - tiers[i].from + 1;
            const curvePctPerPerson = curvePool * perPerson[i] / Math.max(totalRaw, 1);
            const totalPctPerPerson = floorPctPerPerson + curvePctPerPerson;
            tiers[i].pct = roundDraftNumber(totalPctPerPerson * count);
        }

        const sum = tiers.reduce((total, tier) => total + Number(tier.pct || 0), 0);
        if (tiers.length && Math.abs(sum - 100) > 0.01) {
            tiers[0].pct = roundDraftNumber(Number(tiers[0].pct || 0) + (100 - sum));
        }

        return tiers;
    }

    function roundDraftNumber(value) {
        return floorDraftMoney(value);
    }

    function floorDraftMoney(value) {
        return Math.floor(Number(value || 0) * 100) / 100;
    }

    function openDraftPixModal(preferenceId, pixData) {
        if (!draftPixModal || !draftPixImg || !draftPixCode || !draftPixStatus) {
            hidePixGenerationOverlay();
            return;
        }

        draftActivePreferenceId = preferenceId;
        hidePixGenerationOverlay();
        if (draftPixModal.parentElement !== document.body) {
            document.body.appendChild(draftPixModal);
        }
        draftPixModal.hidden = false;
        draftPixModal.style.display = '';
        lockInicioBackgroundScroll(draftPixModal);
        draftPixStatus.textContent = 'Aguardando confirmação do PIX...';
        draftPixStatus.style.color = '';

        const qrBase64 = String(pixData?.qr_code_base64 || '').replace(/^data:image\/[a-zA-Z+.-]+;base64,/, '');
        draftPixImg.src = qrBase64 ? ('data:image/png;base64,' + qrBase64) : '';
        draftPixCode.value = String(pixData?.qr_code || '');

        if (draftPollTimer) clearInterval(draftPollTimer);
        draftPollTimer = setInterval(() => {
            checkDraftPaymentStatus(false);
        }, 3000);
    }

    function closeDraftPixModal() {
        if (draftPixModal) {
            draftPixModal.hidden = true;
            draftPixModal.style.display = 'none';
            unlockInicioBackgroundScroll(draftPixModal);
        }
        draftActivePreferenceId = null;
        if (draftPollTimer) {
            clearInterval(draftPollTimer);
            draftPollTimer = null;
        }
    }

    function buildDraftSuccessPayload(options = {}) {
        const entryAmount = Number(options.entryAmount ?? window.currentDraftEntry ?? 0);
        const hasEntryAmount = Number.isFinite(entryAmount) && entryAmount > 0;

        return {
            title: String(options.title || 'Equipe criada com sucesso'),
            message: String(options.message || 'Sua equipe já está no bolão. Agora é só acompanhar o ranking e torcer.'),
            leagueName: String(options.leagueName || window.currentDraftLeague?.name || 'Bolão na Arena'),
            methodLabel: String(options.methodLabel || 'Entrada confirmada'),
            entryLabel: String(options.entryLabel || (hasEntryAmount ? formatBRL(entryAmount) : 'Entrada liberada')),
            badgeLabel: String(options.badgeLabel || 'Equipe confirmada'),
        };
    }

    function queueDraftSuccessPayload(payload) {
        try {
            sessionStorage.setItem(draftSuccessStorageKey, JSON.stringify(buildDraftSuccessPayload(payload)));
        } catch (_) {
            // silencioso
        }
    }

    function consumeQueuedDraftSuccessPayload() {
        try {
            const raw = sessionStorage.getItem(draftSuccessStorageKey);
            if (!raw) return null;
            sessionStorage.removeItem(draftSuccessStorageKey);
            return buildDraftSuccessPayload(JSON.parse(raw));
        } catch (_) {
            try { sessionStorage.removeItem(draftSuccessStorageKey); } catch (__){}
            return null;
        }
    }

    function isDraftSuccessMobileMode() {
        if (typeof window === 'undefined') return false;
        if (typeof window.matchMedia === 'function') {
            return window.matchMedia('(max-width: 640px)').matches;
        }
        return (window.innerWidth || 0) <= 640;
    }

    function closeDraftSuccessPopout(options = {}) {
        if (!draftSuccessModal || draftSuccessModal.hidden) return;
        const source = String(options?.source || 'button');

        if (isDraftSuccessMobileMode() && source !== 'button') {
            return;
        }

        if (draftSuccessTimer) {
            clearTimeout(draftSuccessTimer);
            draftSuccessTimer = null;
        }

        draftSuccessModal.classList.remove('is-open');
        draftSuccessModal.setAttribute('aria-hidden', 'true');
        unlockInicioBackgroundScroll(draftSuccessModal);

        setTimeout(() => {
            if (!draftSuccessModal.classList.contains('is-open')) {
                draftSuccessModal.hidden = true;
                draftSuccessModal.style.display = 'none';
            }
        }, 220);
    }

    function openDraftSuccessPopout(payload) {
        if (!draftSuccessModal || !draftSuccessTitle || !draftSuccessText || !draftSuccessLeague || !draftSuccessMethod) {
            return;
        }

        const prepared = buildDraftSuccessPayload(payload);

        if (draftSuccessTimer) {
            clearTimeout(draftSuccessTimer);
            draftSuccessTimer = null;
        }

        draftSuccessTitle.textContent = prepared.title;
        draftSuccessText.textContent = prepared.message;
        draftSuccessLeague.textContent = prepared.leagueName;
        draftSuccessMethod.textContent = prepared.entryLabel
            ? `${prepared.methodLabel} • ${prepared.entryLabel}`
            : prepared.methodLabel;
        if (draftSuccessBadge) {
            draftSuccessBadge.innerHTML = '<i class="fas fa-circle-check"></i> ' + escapeHtml(prepared.badgeLabel);
        }

        draftSuccessModal.hidden = false;
        draftSuccessModal.style.display = '';
        draftSuccessModal.setAttribute('aria-hidden', 'false');
        lockInicioBackgroundScroll(draftSuccessModal);

        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(() => draftSuccessModal.classList.add('is-open'));
        } else {
            draftSuccessModal.classList.add('is-open');
        }

        if (!isDraftSuccessMobileMode()) {
            draftSuccessTimer = window.setTimeout(() => {
                closeDraftSuccessPopout({ source: 'timer' });
            }, 4200);
        }
    }

    function flushQueuedDraftSuccessPopout() {
        if (document.hidden) return false;
        const payload = consumeQueuedDraftSuccessPayload();
        if (!payload) return false;
        openDraftSuccessPopout(payload);
        return true;
    }

    function completeDraftEntrySuccess(options = {}) {
        const prepared = buildDraftSuccessPayload(options);
        const shouldDeferPopout = Boolean(options.deferToVisible) || document.hidden;

        closeDraftPixModal();
        closeDraft();
        loadBolaos();

        if (shouldDeferPopout) {
            queueDraftSuccessPayload(prepared);
            return prepared;
        }

        window.setTimeout(() => {
            openDraftSuccessPopout(prepared);
        }, Number(options.delay || 160));

        return prepared;
    }

    function triggerDraftStatusCheckOnReturn() {
        if (document.hidden || !draftActivePreferenceId) return;

        const now = Date.now();
        if (now - draftReturnStatusCheckAt < 1200) {
            return;
        }

        draftReturnStatusCheckAt = now;
        checkDraftPaymentStatus(false);
    }

    window.RRInicioQueueDraftSuccess = queueDraftSuccessPayload;

    async function checkDraftPaymentStatus(manualCheck) {
        if (!draftActivePreferenceId) return;
        try {
            const statusResponse = await fetch(`/api/fantasy/payments/${encodeURIComponent(draftActivePreferenceId)}/status`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
            });
            const statusData = await statusResponse.json();
            if (!statusResponse.ok) {
                throw new Error(statusData.message || 'Erro ao verificar pagamento do bolão.');
            }

            const status = String(statusData.status || '').toLowerCase();

            if (status === 'approved') {
                if (draftPixStatus) {
                    draftPixStatus.textContent = 'Pagamento confirmado! Equipe cadastrada.';
                    draftPixStatus.style.color = '#22c55e';
                }
                if (draftPollTimer) {
                    clearInterval(draftPollTimer);
                    draftPollTimer = null;
                }

                setTimeout(() => {
                    completeDraftEntrySuccess({
                        leagueName: window.currentDraftLeague?.name || 'Bolão na Arena',
                        methodLabel: 'PIX aprovado',
                        entryLabel: Number(window.currentDraftEntry || 0) > 0 ? formatBRL(window.currentDraftEntry) : 'Entrada liberada',
                        badgeLabel: 'Equipe confirmada',
                        message: 'Pagamento PIX confirmado. Sua equipe já entrou no bolão e o ranking será atualizado.',
                        deferToVisible: document.hidden,
                        delay: 180,
                    });
                }, document.hidden ? 0 : 420);
                return;
            }

            if (status === 'expired' || status === 'cancelled' || status === 'failed') {
                if (draftPixStatus) {
                    draftPixStatus.textContent = 'Pagamento ' + status + '. Gere um novo PIX para continuar.';
                    draftPixStatus.style.color = '#ef4444';
                }
                if (draftPollTimer) {
                    clearInterval(draftPollTimer);
                    draftPollTimer = null;
                }
                return;
            }

            if (manualCheck && draftPixStatus) {
                draftPixStatus.textContent = 'Status atual: pendente.';
                draftPixStatus.style.color = '#f59e0be6';
            }
        } catch (error) {
            if (manualCheck && draftPixStatus) {
                draftPixStatus.textContent = error?.message || 'Erro ao verificar pagamento.';
                draftPixStatus.style.color = '#ef4444';
            }
        }
    }

    function bindAllById(id, handler, mark = 'rrBound') {
        document.querySelectorAll('#' + id).forEach((el) => {
            if (!el || el.dataset[mark] === '1') return;
            el.dataset[mark] = '1';
            el.addEventListener('click', handler);
        });
    }

    // Event Listeners do Modal (robustos contra reinjeção AJAX)
    bindAllById('rrDraftClose', closeDraft);
    bindAllById('rrDraftHeaderRefresh', () => {
        const leagueId = Number(window.currentDraftLeague?.id || 0);
        if (leagueId > 0) {
            loadDraftRanking(leagueId, { force: true });
        }
    }, 'rrDraftHeaderRefreshBound');
    bindAllById('rrDraftPixClose', closeDraftPixModal);
    bindAllById('rrDraftSuccessClose', () => closeDraftSuccessPopout({ source: 'button' }), 'rrDraftSuccessBound');
    bindAllById('rrDraftSuccessCloseBtn', () => closeDraftSuccessPopout({ source: 'button' }), 'rrDraftSuccessBtnBound');
    const draftModalRoot = document.getElementById('rrDraftModal');
    if (draftModalRoot && draftModalRoot.dataset.rrOverlayBound !== '1') {
        draftModalRoot.dataset.rrOverlayBound = '1';
        draftModalRoot.addEventListener('click', (event) => {
            if (event.target === draftModalRoot) {
                closeDraft();
            }
        });
    }
    if (!window.__rrDraftEscBound) {
        window.__rrDraftEscBound = true;
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && document.getElementById('rrDraftModal')?.classList.contains('is-open')) {
                closeDraft();
            }
        });
    }
    if (draftPixBackdrop && draftPixBackdrop.dataset.rrBound !== '1') {
        draftPixBackdrop.dataset.rrBound = '1';
        draftPixBackdrop.addEventListener('click', closeDraftPixModal);
    }
    if (draftSuccessBackdrop && draftSuccessBackdrop.dataset.rrBound !== '1') {
        draftSuccessBackdrop.dataset.rrBound = '1';
        draftSuccessBackdrop.addEventListener('click', () => closeDraftSuccessPopout({ source: 'backdrop' }));
    }
    bindAllById('rrDraftPixCheck', () => checkDraftPaymentStatus(true));
    bindAllById('rrDraftPixCopy', async () => {
        if (!draftPixCode || !draftPixCode.value) return;
        try {
            await navigator.clipboard.writeText(draftPixCode.value);
            if (draftPixStatus) {
                draftPixStatus.textContent = 'Codigo PIX copiado!';
                draftPixStatus.style.color = '#22c55e';
            }
        } catch (error) {
            draftPixCode.select();
            document.execCommand('copy');
            if (draftPixStatus) {
                draftPixStatus.textContent = 'Codigo PIX copiado!';
                draftPixStatus.style.color = '#22c55e';
            }
        }
    });

    if (!window.__rrDraftSuccessEscBound) {
        window.__rrDraftSuccessEscBound = true;
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !document.getElementById('rrDraftSuccessModal')?.hidden) {
                closeDraftSuccessPopout({ source: 'escape' });
            }
        });
    }

    if (!window.__rrDraftSuccessReturnBound) {
        window.__rrDraftSuccessReturnBound = true;
        const handleDraftReturn = () => {
            flushQueuedDraftSuccessPopout();
            triggerDraftStatusCheckOnReturn();
        };

        window.addEventListener('focus', handleDraftReturn);
        window.addEventListener('pageshow', () => setTimeout(handleDraftReturn, 60));
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                handleDraftReturn();
            }
        });
    }

    setTimeout(() => {
        flushQueuedDraftSuccessPopout();
    }, 180);
    
    const draftCompetitorSearch = document.getElementById('rrDraftCompetitorSearch');
    if (draftCompetitorSearch && draftCompetitorSearch.dataset.rrBound !== '1') {
        draftCompetitorSearch.dataset.rrBound = '1';
        draftCompetitorSearch.addEventListener('input', (event) => {
            if (!window.draftState) return;
            window.draftState.searchTerm = String(event.target.value || '');
            renderDraftCompetitors();
        });
    }
    
    async function handleDraftPay(event) {
        const state = window.draftState;
        const league = window.currentDraftLeague;
        const clickedBtn = event?.currentTarget instanceof HTMLElement ? event.currentTarget : null;
        const payButtons = clickedBtn ? [clickedBtn] : Array.from(document.querySelectorAll('#rrDraftPayBtn'));
        const nowTs = Date.now();

        if (draftPayInFlight) {
            setDraftHint('Aguarde, já estamos processando seu pedido...', 'warn');
            return;
        }

        if (nowTs < draftPayCooldownUntil) {
            const waitSec = Math.ceil((draftPayCooldownUntil - nowTs) / 1000);
            setDraftHint(`Aguarde ${waitSec}s para tentar novamente.`, 'warn');
            return;
        }
        
        if (!isAuthenticated) {
            promptDraftAuthentication();
            return;
        }

        if (!state || !league || !league.id) {
            setDraftHint('Reabra o bolão e selecione os competidores novamente.', 'error');
            return;
        }

        if (state.selectedCompetitors.length !== state.maxTeamSize) {
            setDraftHint('Selecione exatamente 4 competidores para continuar.', 'warn');
            return;
        }

        try {
            draftPayInFlight = true;
            payButtons.forEach((btn) => {
                btn.disabled = true;
                btn.classList.add('is-disabled');
                btn.classList.remove('rr-draft-pay-btn--locked');
                btn.innerHTML = renderDraftPayButtonState({
                    label: 'Entrando...',
                    sublabel: 'Validando sua equipe e a entrada',
                    icon: 'fa-spinner fa-spin',
                });
            });
            setDraftHint('Confirmando sua entrada no bolão...', 'warn');
            showPixGenerationOverlay('Carregando PIX', 'Preparando o pagamento do bolão e gerando o QR code.');

            const competitorIds = state.selectedCompetitors
                .map(c => parseInt(c.id, 10))
                .filter(id => Number.isFinite(id) && id > 0);

            const captainId = competitorIds[0] || null;

            const response = await fetch(`/api/fantasy/leagues/${league.id}/teams/pay`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    competitor_ids: competitorIds,
                    captain_id: captainId,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                if (response.status === 429) {
                    const retryAfter = parseInt(response.headers.get('Retry-After') || '60', 10);
                    draftPayCooldownUntil = Date.now() + (Math.max(retryAfter, 5) * 1000);
                    throw new Error(`Muitas tentativas. Aguarde ${Math.max(retryAfter, 5)}s e tente novamente.`);
                }
                throw new Error(data.message || 'Erro ao processar entrada no bolão.');
            }

            if (data.free_entry) {
                hidePixGenerationOverlay();
                if (data.voucher_applied) {
                    clearVoucherReadyStateForLeague(league.id);
                }
                const successMessage = data.wallet_applied
                    ? 'Saldo da carteira usado com sucesso. Sua equipe já entrou no bolão.'
                    : data.voucher_applied
                        ? 'Voucher aplicado com sucesso. Sua equipe já entrou no bolão.'
                        : (data.message || 'Equipe cadastrada com sucesso!');
                setDraftHint(successMessage, 'success');
                completeDraftEntrySuccess({
                    leagueName: league?.name || 'Bolão na Arena',
                    methodLabel: data.wallet_applied
                        ? 'Carteira aplicada'
                        : data.voucher_applied
                            ? 'Voucher aplicado'
                            : 'Entrada liberada',
                    entryLabel: Number(window.currentDraftEntry || 0) > 0 ? formatBRL(window.currentDraftEntry) : 'Entrada grátis',
                    badgeLabel: data.wallet_applied
                        ? 'Saldo confirmado'
                        : data.voucher_applied
                            ? 'Voucher confirmado'
                            : 'Equipe confirmada',
                    message: successMessage,
                    delay: 180,
                });
                return;
            }

            const preferenceId = data.preference_id;
            if (!preferenceId) {
                hidePixGenerationOverlay();
                throw new Error('Pagamento iniciado sem identificador.');
            }

            setDraftHint('PIX gerado. Faça o pagamento para concluir sua entrada no bolão.', 'warn');
            openDraftPixModal(preferenceId, data);
        } catch (error) {
            hidePixGenerationOverlay();
            console.error('Erro no pagamento do bolão:', error);
            setDraftHint(error?.message || 'Erro ao processar pagamento do bolão.', 'error');
        } finally {
            draftPayInFlight = false;
            draftPayCooldownUntil = Math.max(draftPayCooldownUntil, Date.now() + 1500);
            updateDraftActionState();
        }
    }

    bindAllById('rrDraftPayBtn', handleDraftPay, 'rrPayBound');

    document.querySelectorAll('[data-bolao-launch-action]').forEach((button) => {
        if (button.dataset.rrLaunchBound === '1') return;
        button.dataset.rrLaunchBound = '1';
        button.addEventListener('click', async () => {
            const card = button.closest('[data-bolao-launch-card]');
            let league = getBolaoLaunchLeagueById(card?.dataset.leagueId || '');

            if (!league) {
                await loadBolaoLaunchOptions();
                league = getBolaoLaunchLeagueById(card?.dataset.leagueId || '');
            }

            if (!league) {
                const statusEl = document.getElementById('rrBolaoLaunchStatus');
                if (statusEl) {
                    statusEl.dataset.tone = 'error';
                    statusEl.textContent = 'Esse bolão ainda não está disponível para abertura.';
                }
                return;
            }

            if (!isAuthenticated) {
                promptDraftAuthentication();
                return;
            }

            const buttonAction = String(button.dataset.bolaoLaunchAction || '');
            const action = buttonAction === 'ranking' ? 'ranking' : 'team';
            const rankingOnly = league.registration_status === 'closed' || league.is_full || league.status === 'finalized' || league.status === 'finished';
            openDraft(league, { initialPanel: action === 'ranking' ? 'ranking' : (rankingOnly ? 'ranking' : 'team') });
        });
    });

    if (bolaoModalidadePickerBtn && bolaoModalidadePickerWrap && bolaoModalidadePickerMenu) {
        let bolaoModalidadePointerHandled = false;

        const toggleBolaoModalidadePicker = function (event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            if (bolaoModalidadePickerBtn.disabled) return;
            enforceBolaoLaunchLegacyFiltersHidden();
            const hasOptions = bolaoModalidadePickerMenu.querySelector('[data-bolao-modalidade-option]');
            if (!hasOptions) {
                closeBolaoModalidadePicker();
                return;
            }
            const isOpen = bolaoModalidadePickerWrap.classList.contains('is-open');
            if (isOpen) {
                closeBolaoModalidadePicker();
                return;
            }
            bolaoModalidadePickerWrap.classList.add('is-open');
            bolaoModalidadePickerBtn.setAttribute('aria-expanded', 'true');
            positionBolaoModalidadePickerMenu();
            bolaoModalidadePickerMenu.hidden = false;
        };

        bolaoModalidadePickerBtn.addEventListener('click', function (event) {
            if (bolaoModalidadePointerHandled) {
                bolaoModalidadePointerHandled = false;
                event.preventDefault();
                event.stopPropagation();
                return;
            }
            toggleBolaoModalidadePicker(event);
        });
        bolaoModalidadePickerBtn.addEventListener('touchend', toggleBolaoModalidadePicker, { passive: false });
        bolaoModalidadePickerBtn.addEventListener('pointerdown', function (event) {
            const pointerType = String(event.pointerType || 'mouse').toLowerCase();
            if (pointerType === 'mouse' || pointerType === 'pen') {
                bolaoModalidadePointerHandled = true;
                toggleBolaoModalidadePicker(event);
                window.setTimeout(function () {
                    bolaoModalidadePointerHandled = false;
                }, 0);
                return;
            }

            event.stopPropagation();
        });
        bolaoModalidadePickerBtn.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            toggleBolaoModalidadePicker(event);
        });

        bolaoModalidadePickerMenu.addEventListener('click', function (event) {
            event.stopPropagation();
            const option = event.target.closest('[data-bolao-modalidade-option]');
            if (!option) return;
            event.preventDefault();
            const modalidadeId = String(option.getAttribute('data-bolao-modalidade-option') || '').trim();
            if (!modalidadeId) return;
            setBolaoLaunchModalidade(modalidadeId);
            closeBolaoModalidadePicker();
        });

        bolaoModalidadePickerMenu.addEventListener('touchstart', function (event) {
            event.stopPropagation();
        }, { passive: true });
    }

    if (window.__rrBolaoModalidadePickerOutsideHandler) {
        document.removeEventListener('click', window.__rrBolaoModalidadePickerOutsideHandler, true);
        document.removeEventListener('touchstart', window.__rrBolaoModalidadePickerOutsideHandler, true);
    }
    window.__rrBolaoModalidadePickerOutsideHandler = function (event) {
        if (!bolaoModalidadePickerWrap) return;
        if (bolaoModalidadePickerWrap.contains(event.target)) return;
        if (bolaoModalidadePickerMenu && bolaoModalidadePickerMenu.contains(event.target)) return;
        closeBolaoModalidadePicker();
    };
    document.addEventListener('click', window.__rrBolaoModalidadePickerOutsideHandler, true);
    document.addEventListener('touchstart', window.__rrBolaoModalidadePickerOutsideHandler, true);

    if (window.__rrBolaoModalidadePickerViewportHandler) {
        window.removeEventListener('resize', window.__rrBolaoModalidadePickerViewportHandler, true);
        window.removeEventListener('scroll', window.__rrBolaoModalidadePickerViewportHandler, true);
    }
    window.__rrBolaoModalidadePickerViewportHandler = function () {
        if (!bolaoModalidadePickerWrap || !bolaoModalidadePickerWrap.classList.contains('is-open')) return;
        positionBolaoModalidadePickerMenu();
    };
    window.addEventListener('resize', window.__rrBolaoModalidadePickerViewportHandler, { passive: true, capture: true });
    window.addEventListener('scroll', window.__rrBolaoModalidadePickerViewportHandler, { passive: true, capture: true });

    // Carregar bolões ao inicializar
    loadBolaos();
    loadBolaoLaunchOptions();

    // ============================================
    // ⚔️ CARREGAR E RENDERIZAR SALAS X1
    // ============================================

    async function loadX1Rooms() {
        const x1Grids = Array.from(document.querySelectorAll('.rr-x1-room-grid'));
        if (!x1Grids.length) return;

        for (const x1Grid of x1Grids) {
            const x1BettingOpen = String(x1Grid.dataset.x1BettingOpen || '1') === '1';
            const entryMode = String(x1Grid.dataset.entryMode || 'competitor');
            const entryCount = Number(x1Grid.dataset.entryCount || 0);
            const modalidadeId = Number(x1Grid.dataset.modalidadeId || 0);
            const rodeioId = Number(x1Grid.dataset.rodeioId || 0);
            const divisao = String(x1Grid.dataset.divisao || '').trim();
            const sectionTitle = x1Grid.closest('.rr-inicio-subcatalog')?.querySelector('.rr-inicio-subcatalog__name')?.textContent?.trim() || 'esta modalidade';

            if (!x1BettingOpen) {
                const closedMessage = String(
                    x1Grid.dataset.x1ClosedMessage
                    || (entryMode === 'group'
                        ? 'Apostas fechadas. São necessários ao menos 3 grupos para liberar o X1.'
                        : 'Apostas fechadas para esta modalidade.')
                );
                x1Grid.innerHTML = ''
                    + '<div class="rr-inicio-x1-closed">'
                    + '<span class="rr-inicio-x1-closed__badge"><i class="fas fa-lock"></i> Apostas fechadas</span>'
                    + '<strong class="rr-inicio-x1-closed__title">X1 indisponível no momento</strong>'
                    + '<p class="rr-inicio-x1-closed__text">' + escapeHtml(closedMessage) + '</p>'
                    + (entryMode === 'group'
                        ? '<p class="rr-inicio-x1-closed__text">Grupos elegíveis agora: ' + escapeHtml(String(entryCount)) + '</p>'
                        : '')
                    + '</div>';
                continue;
            }

            if (!modalidadeId) {
                x1Grid.innerHTML = '<div class="rr-neuro-loading"><p class="text-muted">Nenhuma modalidade ativa para carregar salas X1.</p></div>';
                continue;
            }

            try {
                const params = new URLSearchParams({
                    modalidade_id: String(modalidadeId),
                });
                if (rodeioId > 0) params.set('rodeio_id', String(rodeioId));
                if (divisao) params.set('divisao', divisao);

                const response = await fetch('/api/x1?' + params.toString());
                const data = await response.json();

                if (!data.data || data.data.length === 0) {
                    x1Grid.innerHTML = '<div class="rr-neuro-loading"><p class="text-muted">Nenhuma sala X1 disponível para ' + escapeHtml(sectionTitle) + '.</p></div>';
                    continue;
                }

                const rooms = data.data;
                x1Grid.innerHTML = '';

                rooms.forEach(room => {
                    const card = createX1RoomCard(room);
                    x1Grid.appendChild(card);
                });

                setTimeout(() => initInfiniteCarousel(x1Grid.id, { touch: true }), 120);
            } catch (error) {
                console.error('Erro ao carregar salas X1:', error);
                x1Grid.innerHTML = '<div class="rr-neuro-loading"><p class="text-danger">Erro ao carregar salas X1.</p></div>';
            }
        }

        applyMobileQuickFilters();
    }

    function createX1RoomCard(room) {
        const card = document.createElement('article');
        card.className = 'rr-x1room-card';
        card.dataset.roomId = room.id;
        card.dataset.modalidadeId = String(room.modalidade_id || room.modalidade?.id || '');
        card.dataset.rodeioId = String(room.rodeio_id || room.rodeio?.id || '');
        card.dataset.divisao = String(room.divisao || '');
        card.dataset.entryType = room.competitor_group ? 'group' : 'competitor';
        card.dataset.entryId = String(room.competitor_group_id || room.competitor_id || room.competitor_group?.id || room.competitor?.id || '');

        // Classe de status
        card.classList.add('x1status-' + (room.status || 'open'));

        // Classe de valor (borda neon por valor de entrada)
        const entrada = parseFloat(room.valor_entrada || 0);
        card.dataset.quickFilterBucket = getQuickFilterBucket(entrada);
        card.dataset.entryValue = String(entrada);
        if (entrada >= 1000) {
            card.classList.add('x1val-1000-plus');
        } else if (entrada >= 500) {
            card.classList.add('x1val-500-1000');
        } else if (entrada >= 250) {
            card.classList.add('x1val-250-500');
        } else if (entrada >= 100) {
            card.classList.add('x1val-100-250');
        } else if (entrada >= 50) {
            card.classList.add('x1val-50-100');
        } else {
            card.classList.add('x1val-0-50');
        }

        // Nomes dos jogadores
        const isPlaceholderRoom = Boolean(room.is_placeholder);
        const hostName = isPlaceholderRoom ? 'Aguardando' : (room.host ? room.host.name : 'Anônimo');
        const hostImage = room.host ? room.host.image : null;
        const hostIsPremium = room.host ? room.host.is_premium : false;
        let opponentName = null;
        let opponentImage = null;
        let opponentIsPremium = false;
        let winnerName = null;

        if (isPlaceholderRoom) {
            opponentName = 'Aguardando';
        }

        if (room.participants && room.participants.length > 0) {
            const oppParticipant = room.participants.find(p => !p.is_host);
            if (oppParticipant && oppParticipant.user) {
                opponentName = oppParticipant.user.name;
                opponentImage = oppParticipant.user.image;
                opponentIsPremium = oppParticipant.user.is_premium || false;
            }
        }

        // Verificar vencedor
        if (room.result && room.result.winner_user_id) {
            const winnerId = room.result.winner_user_id;
            if (room.host && room.host.id === winnerId) {
                winnerName = hostName;
            } else if (opponentName) {
                winnerName = opponentName;
            }
        }

        // Competidor / grupo
        const competitorLabel = isPlaceholderRoom
            ? (room.placeholder_copy || 'Aguardando jogadores dos dois lados')
            : (room.competitor_group 
            ? (getRoomCaptainName(room.competitor_group) || room.competitor_group.nome || ('Grupo #' + room.competitor_group.id))
            : (room.competitor ? room.competitor.nome : ''));

        // Prêmio
        const prize = parseFloat(room.prize_total || 0);
        const entry = parseFloat(room.valor_entrada || 0);

        // Status label
        let statusLabel = '';
        let statusClass = '';
        switch (room.status) {
            case 'open':
                statusLabel = isPlaceholderRoom ? 'Pré-aberta' : 'Aberta';
                statusClass = 'rr-x1room-status--open';
                break;
            case 'in_progress':
                statusLabel = 'Em Jogo';
                statusClass = 'rr-x1room-status--in_progress';
                break;
            case 'finished':
                statusLabel = 'Finalizada';
                statusClass = 'rr-x1room-status--finished';
                break;
            default:
                statusLabel = room.status || 'N/A';
                statusClass = 'rr-x1room-status--open';
        }

        // Botão
        let btnText = '';
        let btnIcon = '';
        let btnClass = 'rr-x1room-btn';
        if (room.status === 'open') {
            btnText = isPlaceholderRoom ? 'Abrir Sala' : 'Entrar na Sala';
            btnIcon = isPlaceholderRoom ? 'fas fa-bolt' : 'fas fa-sign-in-alt';
        } else if (room.status === 'in_progress') {
            btnText = 'Acompanhar';
            btnIcon = 'fas fa-eye';
        } else {
            btnText = 'Ver Resultado';
            btnIcon = 'fas fa-trophy';
            btnClass += ' rr-x1room-btn--finished';
        }

        const logoUrl = '{{ asset("assets/images/logo_icon/logo.png") }}';
        const hostNameShort = escapeHtml(truncate(hostName, 10));
        const opponentNameShort = escapeHtml(opponentName ? truncate(opponentName, 10) : 'Aguardando');
        const competitorLabelSafe = escapeHtml(competitorLabel ? truncate(competitorLabel, isPlaceholderRoom ? 34 : 28) : 'Escolha do criador');
        const modalidadeNameSafe = escapeHtml(room.modalidade?.nome || '');
        const statusLabelSafe = escapeHtml(statusLabel);
        const winnerNameSafe = winnerName ? escapeHtml(truncate(winnerName, 16)) : null;

        function playerAvatarHtml(image, isHost) {
            const extraClass = isHost ? ' rr-x1room-player-avatar--host' : '';
            if (image) {
                return '<div class="rr-x1room-player-avatar' + extraClass + '"><img src="' + image + '" alt="" onerror="this.onerror=null;this.src=\'' + logoUrl + '\';this.style.objectFit=\'contain\';this.style.padding=\'2px\'"></div>';
            }
            return '<div class="rr-x1room-player-avatar rr-x1room-player-avatar--logo' + extraClass + '"><img src="' + logoUrl + '" alt=""></div>';
        }

        function crownHtml(isPremium) {
            return isPremium ? '<i class="fas fa-crown rr-x1room-player-crown"></i>' : '';
        }

        card.innerHTML = `
            ${winnerNameSafe ? '<div class="rr-x1room-winner-badge"><i class="fas fa-trophy"></i> ' + winnerNameSafe + '</div>' : ''}
            <div class="rr-x1room-shell">
                <div class="rr-x1room-topbar">
                    <span class="rr-x1room-status ${statusClass}">${statusLabelSafe}</span>
                    ${room.is_premium_room ? '<span class="rr-x1room-pill rr-x1room-pill--premium"><i class="fas fa-crown"></i> Premium</span>' : ''}
                </div>
                <div class="rr-x1room-header">
                    <div class="rr-x1room-player">
                        ${playerAvatarHtml(hostImage, true)}
                        <span class="rr-x1room-player-name rr-x1room-player-name--host" title="${escapeHtml(hostName)}">${hostNameShort}${crownHtml(hostIsPremium)}</span>
                        <span class="rr-x1room-player-role">${isPlaceholderRoom ? 'Lado 1' : 'Criador'}</span>
                    </div>
                    <span class="rr-x1room-vs">VS</span>
                    <div class="rr-x1room-player">
                        ${opponentName ? playerAvatarHtml(opponentImage, false) : playerAvatarHtml(null, false)}
                        <span class="rr-x1room-player-name ${opponentName ? '' : 'rr-x1room-player-name--waiting'}" title="${escapeHtml(opponentName || 'Aguardando...')}">${opponentNameShort}${opponentName ? crownHtml(opponentIsPremium) : ''}</span>
                        <span class="rr-x1room-player-role">${isPlaceholderRoom ? 'Lado 2' : (opponentName ? 'Desafiante' : 'Aguardando')}</span>
                    </div>
                </div>
                <div class="rr-x1room-content">
                    <div class="rr-x1room-highlight">
                        <span class="rr-x1room-highlight-label">${isPlaceholderRoom ? 'Status da sala' : 'Competidor do duelo'}</span>
                        <strong class="rr-x1room-highlight-value">${competitorLabelSafe}</strong>
                        ${modalidadeNameSafe ? '<span class="rr-x1room-highlight-meta">' + modalidadeNameSafe + '</span>' : ''}
                    </div>
                    <div class="rr-x1room-facts">
                        <div class="rr-x1room-fact">
                            <span class="rr-x1room-fact-label">Prêmio</span>
                            <strong class="rr-x1room-fact-value">${formatBRL(prize)}</strong>
                        </div>
                        <div class="rr-x1room-fact">
                            <span class="rr-x1room-fact-label">Entrada cada</span>
                            <strong class="rr-x1room-fact-value">${formatBRL(entry)}</strong>
                        </div>
                    </div>
                    <button class="${btnClass}" data-action="x1-room-action">
                        <i class="${btnIcon}"></i>
                        <span>${btnText}</span>
                    </button>
                </div>
            </div>
        `;

        // Event listener
        const btn = card.querySelector('[data-action="x1-room-action"]');
        if (btn && room.status === 'open') {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (isPlaceholderRoom) {
                    openPresetX1Room(room);
                    return;
                }
                joinX1Room(room);
            });
        }

        // Ver Resultado → abre modal
        if (btn && room.status === 'finished') {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openX1ResultModal(room);
            });
        }

        // Acompanhar (em jogo) -> abre o mesmo modal no padrao de resultado
        if (btn && room.status === 'in_progress') {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openX1ResultModal(room);
            });
        }

        return card;
    }

    function openPresetX1Room(room) {
        if (!isAuthenticated) {
            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
            } else {
                window.location.href = '{{ route("user.login") }}';
            }
            return;
        }

        openJoinSlip({
            ...room,
            __preset_open_room: true,
            host: {
                name: 'Aguardando',
                image: null,
                is_premium: false,
            },
            competitor: null,
            competitor_group: null,
        });
    }

    function truncate(str, max) {
        if (!str) return '';
        return str.length > max ? str.substring(0, max) + '…' : str;
    }

    function joinX1Room(room) {
        // Abrir bilhete de entrada (join slip) ao invés de navegar para aba X1
        openJoinSlip(room);
    }

    // ============================================
    // ⚔️ JOIN SLIP — Bilhete de Entrada na Sala X1
    // ============================================
    const joinSlip = document.getElementById('rrJoinSlip');
    const joinSlipBackdrop = document.getElementById('rrJoinSlipBackdrop');
    const joinSlipLoading = document.getElementById('rrJoinSlipLoading');
    const joinSlipConfirmBtn = document.getElementById('rrJoinSlipConfirmBtn');
    const joinSlipConfirmText = document.getElementById('rrJoinSlipConfirmText');
    const joinSlipProcessing = document.getElementById('rrJoinSlipProcessing');
    const joinSlipPickBtn = document.getElementById('rrJoinSlipPickBtn');
    const joinSlipPickPlaceholder = document.getElementById('rrJoinSlipPickPlaceholder');
    const joinSlipPickSelected = document.getElementById('rrJoinSlipPickSelected');
    const joinSlipPickImg = document.getElementById('rrJoinSlipPickImg');
    const joinSlipPickName = document.getElementById('rrJoinSlipPickName');

    let joinSlipRoom = null;
    let joinSlipSelection = null; // { type: 'competitor'|'group', id, name, foto }

    function openJoinSlip(room) {
        if (!isAuthenticated) {
            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
            } else {
                window.location.href = '{{ route("user.login") }}';
            }
            return;
        }

        joinSlipRoom = room;
        joinSlipSelection = null;

        const logoUrl = '{{ asset("assets/images/logo_icon/logo.png") }}';
        const isPresetOpenRoom = Boolean(room?.__preset_open_room);

        // Preencher dados do host
        const hostAvatar = document.getElementById('rrJoinSlipHostAvatar');
        const hostNameEl = document.getElementById('rrJoinSlipHostName');
        if (hostAvatar) {
            hostAvatar.src = room.host?.image || logoUrl;
            hostAvatar.onerror = function() { this.src = logoUrl; };
        }
        if (hostNameEl) hostNameEl.textContent = truncate(isPresetOpenRoom ? 'Aguardando' : (room.host?.name || 'Criador'), 12);

        // Modalidade
        const modalidadeEl = document.getElementById('rrJoinSlipModalidade');
        if (modalidadeEl) modalidadeEl.textContent = room.modalidade?.nome || '—';

        // Competidor do host
        const hostCompEl = document.getElementById('rrJoinSlipHostCompetitor');
        if (hostCompEl) {
            hostCompEl.textContent = isPresetOpenRoom
                ? (room.placeholder_copy || 'Sala pré-aberta aguardando o primeiro criador')
                : room.competitor_group
                ? (getRoomCaptainName(room.competitor_group) || 'Capitão do grupo')
                : (room.competitor?.nome || '—');
        }

        // Valores
        const entry = parseFloat(room.valor_entrada || 0);
        const prize = parseFloat(room.prize_total || 0);
        const entradaEl = document.getElementById('rrJoinSlipEntrada');
        const premioEl = document.getElementById('rrJoinSlipPremio');
        const premioBigEl = document.getElementById('rrJoinSlipPremioBig');
        const payEl = document.getElementById('rrJoinSlipPayValue');
        if (entradaEl) entradaEl.textContent = formatBRL(entry);
        if (premioEl) premioEl.textContent = formatBRL(prize);
        if (premioBigEl) premioBigEl.textContent = formatBRL(prize);
        if (payEl) payEl.textContent = formatBRL(entry);

        // Reset selector
        resetJoinSlipSelector();

        // Atualizar placeholder conforme tipo (individual ou grupo)
        const teamSize = room.modalidade?.tamanho_equipe || 1;
        const isGroup = teamSize > 1;
        const selectorLabel = document.querySelector('.rr-joinslip__selector-label');
        if (selectorLabel) selectorLabel.textContent = isGroup ? 'Seu Grupo' : 'Seu Competidor';
        const placeholderText = joinSlipPickPlaceholder?.querySelector('span');
        if (placeholderText) placeholderText.textContent = isGroup ? 'Escolher Grupo...' : 'Escolher Competidor...';
        if (joinSlipConfirmText) {
            joinSlipConfirmText.textContent = isPresetOpenRoom ? 'CRIAR SALA' : 'SELECIONE UM COMPETIDOR';
        }

        // Reset loading/processing
        if (joinSlipLoading) joinSlipLoading.hidden = true;
        setJoinSlipProcessing(false);

        // Mostrar
        if (joinSlip) {
            joinSlip.hidden = false;
            joinSlip.style.display = '';
            lockInicioBackgroundScroll(joinSlip);
        }
    }

    function closeJoinSlip() {
        if (joinSlip) {
            joinSlip.hidden = true;
            joinSlip.style.display = 'none';
            unlockInicioBackgroundScroll(joinSlip);
        }
        joinSlipRoom = null;
        joinSlipSelection = null;
    }

    function resetJoinSlipSelector() {
        joinSlipSelection = null;
        if (joinSlipPickPlaceholder) joinSlipPickPlaceholder.hidden = false;
        if (joinSlipPickSelected) joinSlipPickSelected.hidden = true;
        if (joinSlipPickBtn) joinSlipPickBtn.classList.remove('has-selection');
        if (joinSlipConfirmBtn) joinSlipConfirmBtn.disabled = true;
        if (joinSlipConfirmText) joinSlipConfirmText.textContent = 'SELECIONE UM COMPETIDOR';
    }

    function setJoinSlipSelection(selection) {
        joinSlipSelection = selection;
        const logoUrl = '{{ asset("assets/images/logo_icon/logo.png") }}';
        const isPresetOpenRoom = Boolean(joinSlipRoom?.__preset_open_room);

        if (joinSlipPickPlaceholder) joinSlipPickPlaceholder.hidden = true;
        if (joinSlipPickSelected) joinSlipPickSelected.hidden = false;
        if (joinSlipPickImg) {
            joinSlipPickImg.src = selection.foto || logoUrl;
            joinSlipPickImg.onerror = function() { this.src = logoUrl; };
        }
        if (joinSlipPickName) joinSlipPickName.textContent = selection.name;
        if (joinSlipPickBtn) joinSlipPickBtn.classList.add('has-selection');
        if (joinSlipConfirmBtn) joinSlipConfirmBtn.disabled = false;
        if (joinSlipConfirmText) joinSlipConfirmText.textContent = isPresetOpenRoom ? 'CRIAR SALA' : 'ENTRAR NA SALA';
    }

    function setJoinSlipProcessing(processing) {
        if (joinSlipConfirmText) joinSlipConfirmText.hidden = processing;
        if (joinSlipProcessing) joinSlipProcessing.hidden = !processing;
        if (joinSlipConfirmBtn) joinSlipConfirmBtn.disabled = processing;
    }

    async function handleJoinSlipConfirm() {
        if (!joinSlipRoom || !joinSlipSelection) return;

        setJoinSlipProcessing(true);
        if (joinSlipLoading) joinSlipLoading.hidden = false;

        try {
            const isPresetOpenRoom = Boolean(joinSlipRoom?.__preset_open_room);
            const body = isPresetOpenRoom
                ? {
                    description: 'Sala X1 (Pré-aberta)',
                    rodeio_id: Number(joinSlipRoom.rodeio_id || joinSlipRoom.rodeio?.id || 0) || null,
                    modalidade_id: Number(joinSlipRoom.modalidade_id || joinSlipRoom.modalidade?.id || 0) || null,
                    valor_entrada: parseFloat(joinSlipRoom.valor_entrada || 0),
                    divisao: String(joinSlipRoom.divisao || '') || null,
                }
                : { room_id: joinSlipRoom.id };
            if (joinSlipSelection.type === 'group') {
                body.competitor_group_id = joinSlipSelection.id;
            } else {
                body.competitor_id = joinSlipSelection.id;
            }

            const response = await fetch(isPresetOpenRoom ? '/api/x1' : '/api/x1/join-room', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Erro ao entrar na sala.');
            }

            const preferenceId = data?.payment?.preference_id;
            if (!preferenceId) {
                throw new Error('Preferência de pagamento não encontrada.');
            }

            // Gerar PIX
            await processPayment(preferenceId);

        } catch (error) {
            if (joinSlipLoading) joinSlipLoading.hidden = true;
            setJoinSlipProcessing(false);
            alert(error.message || 'Erro ao entrar na sala.');
        }
    }

    // Event listeners do Join Slip
    document.getElementById('rrJoinSlipClose')?.addEventListener('click', closeJoinSlip);
    joinSlipBackdrop?.addEventListener('click', closeJoinSlip);
    joinSlipConfirmBtn?.addEventListener('click', handleJoinSlipConfirm);

    // Abrir picker ao clicar no botão de seleção
    joinSlipPickBtn?.addEventListener('click', () => {
        if (!joinSlipRoom) return;
        openCompetitorPicker();
    });

    // ============================================
    // 🔍 COMPETITOR / GROUP PICKER MODAL
    // ============================================
    const pickerOverlay = document.getElementById('rrCompetitorPicker');
    const pickerBackdrop = document.getElementById('rrPickerBackdrop');
    const pickerList = document.getElementById('rrPickerList');
    const pickerSearch = document.getElementById('rrPickerSearch');
    let pickerData = []; // array of { type, id, name, foto, meta, disabled }


    function openCompetitorPicker() {
        if (!joinSlipRoom) return;

        // Determinar se é grupo ou individual
        const teamSize = joinSlipRoom.modalidade?.tamanho_equipe || 1;
        const modalidadeId = joinSlipRoom.modalidade_id || joinSlipRoom.modalidade?.id;
        const rodeioId = joinSlipRoom.rodeio_id;
        const isGroup = teamSize > 1;

        // Atualizar título do picker conforme tipo
        const pickerTitleEl = document.querySelector('.rr-picker__title');
        if (pickerTitleEl) {
            pickerTitleEl.innerHTML = '<i class="fas fa-' + (isGroup ? 'users' : 'horse') + '"></i> Escolher ' + (isGroup ? 'Grupo' : 'Competidor');
        }

        // Host's choice — to disable in picker
        const hostCompetitorId = joinSlipRoom.competitor_id || joinSlipRoom.competitor?.id || 0;
        const hostGroupId = joinSlipRoom.competitor_group_id || joinSlipRoom.competitor_group?.id || 0;

        // Reset search
        if (pickerSearch) pickerSearch.value = '';

        // Show loading
        pickerList.innerHTML = `
            <div class="rr-betslip__loading">
                <div class="rr-betslip__spinner"></div>
                <span>Carregando ${isGroup ? 'grupos' : 'competidores'}...</span>
            </div>
        `;

        pickerOverlay.hidden = false;
        pickerOverlay.style.display = '';
        // Do not change body overflow since join slip already set it

        // Fetch competitors from API
        let url = '/api/realtime/competitors/modalidade/' + modalidadeId + '?rodeio_id=' + (rodeioId || '');
        if (isGroup) url += '&modo=grupos';

        fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then(r => r.json())
        .then(result => {
            if (!result.success || !result.data || result.data.length === 0) {
                pickerList.innerHTML = '<div class="rr-picker__empty"><i class="fas fa-ghost" style="font-size:24px;margin-bottom:8px;display:block;"></i>Nenhum competidor disponível.</div>';
                pickerData = [];
                return;
            }

            if (isGroup) {
                pickerData = result.data.map(g => ({
                    type: 'group',
                    id: g.group_id,
                    name: g.group_name,
                    foto: g.members?.[0]?.foto || '/assets/images/logo_icon/favicon.png',
                    members: g.members || [],
                    meta: g.members?.map(m => m.nome).join(' + ') || '',
                    disabled: g.group_id === hostGroupId,
                }));
            } else {
                pickerData = result.data.map(c => ({
                    type: 'competitor',
                    id: c.competitor_id,
                    name: c.competitor_name || c.nome,
                    foto: c.foto_url || c.foto || '/assets/images/logo_icon/favicon.png',
                    members: null,
                    meta: c.nivel || '',
                    disabled: c.competitor_id === hostCompetitorId,
                }));
            }

            renderPickerList('');
        })
        .catch(err => {
            pickerList.innerHTML = '<div class="rr-picker__empty" style="color:#ef4444;"><i class="fas fa-exclamation-triangle" style="font-size:24px;margin-bottom:8px;display:block;"></i>Erro ao carregar. Tente novamente.</div>';
            pickerData = [];
        });
    }

    function closeCompetitorPicker() {
        if (pickerOverlay) {
            pickerOverlay.hidden = true;
            pickerOverlay.style.display = 'none';
        }
    }

    function renderPickerList(filter) {
        const q = (filter || '').toLowerCase().trim();
        const filtered = pickerData.filter(item => {
            if (!q) return true;
            return item.name.toLowerCase().includes(q) || (item.meta || '').toLowerCase().includes(q);
        });

        if (filtered.length === 0) {
            pickerList.innerHTML = '<div class="rr-picker__empty">Nenhum resultado encontrado.</div>';
            return;
        }

        const logoUrl = '{{ asset("assets/images/logo_icon/logo.png") }}';
        pickerList.innerHTML = '';
        filtered.forEach(item => {
            const div = document.createElement('div');
            div.className = 'rr-picker__item' + (item.disabled ? ' is-disabled' : '');

            let membersHtml = '';
            if (item.type === 'group' && item.members && item.members.length > 0) {
                membersHtml = '<div class="rr-picker__group-members">' +
                    item.members.slice(0, 4).map(m =>
                        '<img class="rr-picker__group-member-img" src="' + (m.foto || logoUrl) + '" alt="' + m.nome + '" onerror="this.src=\'' + logoUrl + '\'">'
                    ).join('') +
                    (item.members.length > 4 ? '<span style="color:#64748b;font-size:10px;align-self:center;">+' + (item.members.length - 4) + '</span>' : '') +
                '</div>';
            }

            div.innerHTML = `
                <img class="rr-picker__item-img" src="${item.foto || logoUrl}" alt="${item.name}" onerror="this.src='${logoUrl}'">
                <div class="rr-picker__item-info">
                    <div class="rr-picker__item-name">${item.name}</div>
                    ${item.meta ? '<div class="rr-picker__item-meta">' + item.meta + '</div>' : ''}
                    ${membersHtml}
                </div>
                ${item.disabled ? '<span class="rr-picker__item-badge">Já escolhido</span>' : '<i class="fas fa-chevron-right rr-picker__item-arrow"></i>'}
            `;

            if (!item.disabled) {
                div.addEventListener('click', () => {
                    setJoinSlipSelection({
                        type: item.type,
                        id: item.id,
                        name: item.name,
                        foto: item.foto,
                    });
                    closeCompetitorPicker();
                });
            }

            pickerList.appendChild(div);
        });
    }

    // Event listeners do Picker
    document.getElementById('rrPickerClose')?.addEventListener('click', closeCompetitorPicker);
    pickerBackdrop?.addEventListener('click', closeCompetitorPicker);
    pickerSearch?.addEventListener('input', function() {
        renderPickerList(this.value);
    });

    // Carregar salas X1 ao inicializar
    loadX1Rooms();

    /* ============================================
       🏆 X1 RESULT MODAL
       ============================================ */
    function openX1ResultModal(room) {
        // Remove existing modal if any
        let overlay = document.getElementById('rrX1ResultModal');
        if (overlay) overlay.remove();

        const logoUrl = '{{ asset("assets/images/logo_icon/logo.png") }}';

        // Host data
        const hostName = room.host ? room.host.name : 'Anônimo';
        const hostImage = room.host ? room.host.image : null;
        const hostIsPremium = room.host ? room.host.is_premium : false;
        const hostId = room.host ? room.host.id : null;

        // Opponent data
        let opponentName = null;
        let opponentImage = null;
        let opponentIsPremium = false;
        let opponentId = null;

        if (room.participants && room.participants.length > 0) {
            const opp = room.participants.find(p => !p.is_host);
            if (opp && opp.user) {
                opponentName = opp.user.name;
                opponentImage = opp.user.image;
                opponentIsPremium = opp.user.is_premium || false;
                opponentId = opp.user.id;
            }
        }

        // Winner
        const winnerId = room.result ? room.result.winner_user_id : null;
        const isHostWinner = winnerId && hostId === winnerId;
        const isOpponentWinner = winnerId && opponentId === winnerId;

        // Competitor picks per participant
        const hostParticipant = room.participants ? room.participants.find(p => p.is_host) : null;
        const oppParticipant = room.participants ? room.participants.find(p => !p.is_host) : null;

        function getCompetitors(participant) {
            if (!participant) return [];
            if (participant.competitor_group && participant.competitor_group.members && participant.competitor_group.members.length > 0) {
                return participant.competitor_group.members;
            }
            if (participant.competitor) {
                return [participant.competitor];
            }
            return [];
        }

        function getRoomCompetitors() {
            if (room.competitor_group && room.competitor_group.members && room.competitor_group.members.length > 0) {
                return room.competitor_group.members;
            }
            if (room.competitor) {
                return [{ id: room.competitor.id, nome: room.competitor.nome, foto_url: room.competitor.foto_url || null }];
            }
            return [];
        }

        let hostComps = getCompetitors(hostParticipant);
        let oppComps = getCompetitors(oppParticipant);
        const roomComps = getRoomCompetitors();

        // Fallback to room-level if participants don't have individual picks
        if (hostComps.length === 0 && roomComps.length > 0) hostComps = roomComps;
        if (oppComps.length === 0 && roomComps.length > 0) oppComps = roomComps;

        // Check if both sides share the exact same competitors (room-level shared)
        const sharedComps = (hostComps.length > 0 && oppComps.length > 0 &&
            hostComps.length === oppComps.length &&
            hostComps.every((c, i) => c.id === oppComps[i].id));

        function compPhotoHtml(comp) {
            const url = comp.foto_url || logoUrl;
            const isFallback = !comp.foto_url || comp.foto_url.includes('favicon');
            return '<div class="rr-x1modal__comp-photo ' + (isFallback ? 'rr-x1modal__comp-photo--fallback' : '') + '">'
                + '<img src="' + url + '" alt="" onerror="this.onerror=null;this.src=\'' + logoUrl + '\';this.style.objectFit=\'contain\';this.style.padding=\'3px\'">'
                + '</div>';
        }

        function renderCompSide(comps) {
            if (comps.length === 0) return '<span class="rr-x1modal__comp-empty">—</span>';

            if (comps.length === 1) {
                return '<div class="rr-x1modal__comp-solo">'
                    + compPhotoHtml(comps[0])
                    + '<span class="rr-x1modal__comp-name">' + comps[0].nome + '</span>'
                    + '</div>';
            }

            if (comps.length === 2) {
                return '<div class="rr-x1modal__comp-duo">'
                    + '<div class="rr-x1modal__comp-duo-item">' + compPhotoHtml(comps[0]) + '<span class="rr-x1modal__comp-name">' + comps[0].nome + '</span></div>'
                    + '<span class="rr-x1modal__comp-duo-sep">&amp;</span>'
                    + '<div class="rr-x1modal__comp-duo-item">' + compPhotoHtml(comps[1]) + '<span class="rr-x1modal__comp-name">' + comps[1].nome + '</span></div>'
                    + '</div>';
            }

            // 3+ competitors: names only
            let html = '<div class="rr-x1modal__comp-list">';
            comps.forEach(function(c) {
                html += '<span class="rr-x1modal__comp-list-item">' + c.nome + '</span>';
            });
            html += '</div>';
            return html;
        }

        // Build competitor section HTML
        let compSectionHtml = '';
        if (hostComps.length > 0 || oppComps.length > 0) {
            // Always show both sides with player names
            compSectionHtml = '<div class="rr-x1modal__competitors">'
                + '<div class="rr-x1modal__comp-title"><i class="fas fa-horse"></i> Competidores</div>'
                + '<div class="rr-x1modal__comp-sides">'
                + '<div class="rr-x1modal__comp-side"><div class="rr-x1modal__comp-side-label">' + hostName + '</div>' + renderCompSide(hostComps) + '</div>'
                + '<span class="rr-x1modal__comp-vs">VS</span>'
                + '<div class="rr-x1modal__comp-side"><div class="rr-x1modal__comp-side-label">' + (opponentName || '???') + '</div>' + renderCompSide(oppComps) + '</div>'
                + '</div>'
                + '</div>';
        }

        // Details
        const prize = parseFloat(room.prize_total || 0);
        const entry = parseFloat(room.valor_entrada || 0);
        const competitorLabel = room.competitor_group
            ? room.competitor_group.nome
            : (room.competitor ? room.competitor.nome : '');
        const modalidadeLabel = room.modalidade ? room.modalidade.nome : '';
        const rodeioLabel = room.rodeio ? room.rodeio.nome : '';
        const reason = room.result ? room.result.reason : '';

        // Status
        let statusLabel = 'Finalizada';
        let statusSlug = 'finished';
        if (room.status === 'open') { statusLabel = 'Aberta'; statusSlug = 'open'; }
        else if (room.status === 'in_progress') { statusLabel = 'Em Jogo'; statusSlug = 'in_progress'; }

        function avatarHtml(image) {
            if (image) {
                return '<img src="' + image + '" alt="" onerror="this.onerror=null;this.src=\'' + logoUrl + '\';this.style.objectFit=\'contain\';this.style.padding=\'4px\'">';
            }
            return '<img src="' + logoUrl + '" alt="" style="object-fit:contain;padding:4px">';
        }

        function crownIcon(isPremium) {
            return isPremium ? '<i class="fas fa-crown rr-x1modal__crown"></i>' : '';
        }

        // Build modal HTML
        overlay = document.createElement('div');
        overlay.id = 'rrX1ResultModal';
        overlay.className = 'rr-x1modal-overlay active';
        overlay.innerHTML = `
            <div class="rr-x1modal">
                <button class="rr-x1modal__close" data-close-x1modal>&times;</button>
                <div class="rr-x1modal__header">
                    <div class="rr-x1modal__title">Sala X1 #${room.id}</div>
                    <div class="rr-x1modal__room-name">${room.name || 'Desafio X1'}</div>
                </div>

                <div class="rr-x1modal__vs">
                    <div class="rr-x1modal__player ${isHostWinner ? 'rr-x1modal__player--winner' : (winnerId ? 'rr-x1modal__player--loser' : '')}">
                        ${isHostWinner ? '<div class="rr-x1modal__winner-badge"><i class="fas fa-trophy"></i> VENCEDOR</div>' : ''}
                        <div class="rr-x1modal__avatar ${!hostImage ? 'rr-x1modal__avatar--logo' : ''}">
                            ${avatarHtml(hostImage)}
                        </div>
                        <span class="rr-x1modal__player-name">${hostName}${crownIcon(hostIsPremium)}</span>
                    </div>
                    <span class="rr-x1modal__vs-text">VS</span>
                    <div class="rr-x1modal__player ${isOpponentWinner ? 'rr-x1modal__player--winner' : (winnerId ? 'rr-x1modal__player--loser' : '')}">
                        ${isOpponentWinner ? '<div class="rr-x1modal__winner-badge"><i class="fas fa-trophy"></i> VENCEDOR</div>' : ''}
                        <div class="rr-x1modal__avatar ${!opponentImage ? 'rr-x1modal__avatar--logo' : ''}">
                            ${avatarHtml(opponentImage)}
                        </div>
                        <span class="rr-x1modal__player-name">${opponentName || '???'}${opponentName ? crownIcon(opponentIsPremium) : ''}</span>
                    </div>
                </div>

                ${compSectionHtml}

                ${reason ? '<div class="rr-x1modal__reason"><i class="fas fa-info-circle"></i> ' + reason + '</div>' : ''}

                <div class="rr-x1modal__details">
                    <div class="rr-x1modal__detail-row">
                        <span class="rr-x1modal__detail-label"><i class="fas fa-trophy"></i> Prêmio</span>
                        <span class="rr-x1modal__detail-value rr-x1modal__detail-value--prize">${formatBRL(prize)}</span>
                    </div>
                    <div class="rr-x1modal__detail-row">
                        <span class="rr-x1modal__detail-label"><i class="fas fa-coins"></i> Entrada (cada)</span>
                        <span class="rr-x1modal__detail-value">${formatBRL(entry)}</span>
                    </div>
                    ${competitorLabel ? '<div class="rr-x1modal__detail-row"><span class="rr-x1modal__detail-label"><i class="fas fa-horse"></i> Competidor</span><span class="rr-x1modal__detail-value">' + competitorLabel + '</span></div>' : ''}
                    ${modalidadeLabel ? '<div class="rr-x1modal__detail-row"><span class="rr-x1modal__detail-label"><i class="fas fa-list"></i> Modalidade</span><span class="rr-x1modal__detail-value">' + modalidadeLabel + '</span></div>' : ''}
                    ${rodeioLabel ? '<div class="rr-x1modal__detail-row"><span class="rr-x1modal__detail-label"><i class="fas fa-map-marker-alt"></i> Rodeio</span><span class="rr-x1modal__detail-value">' + rodeioLabel + '</span></div>' : ''}
                    <div class="rr-x1modal__detail-row">
                        <span class="rr-x1modal__detail-label"><i class="fas fa-circle"></i> Status</span>
                        <span class="rr-x1modal__detail-value rr-x1modal__detail-value--status rr-x1modal__detail-value--${statusSlug}">${statusLabel}</span>
                    </div>
                    ${room.is_premium_room ? '<div class="rr-x1modal__detail-row"><span class="rr-x1modal__detail-label"><i class="fas fa-crown" style="color:#f59e0be6"></i> Tipo</span><span class="rr-x1modal__detail-value" style="color:#f59e0be6">PREMIUM</span></div>' : ''}
                    ${room.finished_at ? '<div class="rr-x1modal__detail-row"><span class="rr-x1modal__detail-label"><i class="fas fa-calendar"></i> Finalizada</span><span class="rr-x1modal__detail-value">' + new Date(room.finished_at).toLocaleDateString('pt-BR') + '</span></div>' : ''}
                </div>

                <div class="rr-x1modal__footer">
                    <button class="rr-x1modal__close-btn" data-close-x1modal>Fechar</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Close handlers
        overlay.querySelectorAll('[data-close-x1modal]').forEach(btn => {
            btn.addEventListener('click', () => closeX1ResultModal());
        });
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeX1ResultModal();
        });
        document.addEventListener('keydown', x1ModalEscHandler);
    }

    function x1ModalEscHandler(e) {
        if (e.key === 'Escape') closeX1ResultModal();
    }

    function closeX1ResultModal() {
        const overlay = document.getElementById('rrX1ResultModal');
        if (overlay) {
            overlay.classList.remove('active');
            setTimeout(() => overlay.remove(), 200);
        }
        document.removeEventListener('keydown', x1ModalEscHandler);
    }

})();
</script>

<script>
// Pagamentos pendentes (reabre PIX / checkout existente)
(function(){
  if (window.__RR_PENDING_MODAL_BOUND) return;
  window.__RR_PENDING_MODAL_BOUND = true;

  const pending = window.RR_PENDING_PAYMENTS || {count:0,x1:[],fantasy:[]};
  const btn = document.getElementById('rrPendingPaymentsBtn');
  const modal = document.getElementById('rrPendingModal');
  const backdrop = document.getElementById('rrPendingBackdrop');
  const closeBtn = document.getElementById('rrPendingClose');
  const okBtn = document.getElementById('rrPendingCloseBtn');
  const list = document.getElementById('rrPendingList');
  const counter = document.getElementById('rrPendingCounter');

  if (!modal || !list || !counter) return;
  counter.textContent = pending.count || 0;
  if (!btn) return;

  const renderList = () => {
    const rows = [];
    const pixByPreference = Object.create(null);

    const renderRow = (orderId, pref, amount, type, qrCode, qrCodeBase64) => {
      if (pref) {
        pixByPreference[pref] = {
          type,
          qr_code: qrCode || '',
          qr_code_base64: qrCodeBase64 || '',
        };
      }

      rows.push(
        `<div class="rr-history-item">
            <div class="rr-history-item__header" style="align-items:center;">
                <span class="rr-history-item__badge" style="background: rgba(234,179,8,0.18); color:#d97706;">#${orderId}</span>
                <span class="rr-history-item__prize">R$ ${parseFloat(amount||0).toFixed(2)}</span>
            </div>
            <div class="rr-history-item__meta" style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                <span><i class="fas fa-clock"></i> Pendente</span>
                ${pref ? `<button class="rr-perfil-btn rr-perfil-btn--secondary" data-pref="${pref}">
                    <i class="fas fa-qrcode"></i> Pagar
                </button>` : ''}
            </div>
        </div>`
      );
    };

    (pending.x1 || []).forEach(p =>
      renderRow(
        p.id,
        p.provider_preference_id || p.external_reference || '',
        p.amount,
        'x1',
        p.qr_code,
        p.qr_code_base64
      )
    );

    (pending.fantasy || []).forEach(p =>
      renderRow(
        p.id,
        p.provider_preference_id || p.external_reference || '',
        p.amount,
        'fantasy',
        p.qr_code,
        p.qr_code_base64
      )
    );

    list.innerHTML = rows.length ? rows.join('') : '<div class="rr-perfil-placeholder"><i class="fas fa-check-circle"></i><h4>Sem pendências</h4></div>';
    list.querySelectorAll('button[data-pref]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const pref = btn.getAttribute('data-pref');
        if (!pref) return;
        const payment = pixByPreference[pref] || {};

        closeModal();

        if (typeof window.RRInicioOpenPendingPix === 'function') {
          const opened = await window.RRInicioOpenPendingPix(pref, payment);
          if (opened) return;
        }

        // fallback garantido: abre checkout externo na mesma aba
        window.location.href = `https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=${encodeURIComponent(pref)}`;
      });
    });
  };

  const openModal = () => {
    renderList();
    modal.hidden = false;
    modal.style.display = '';
  };
  const closeModal = () => {
    modal.hidden = true;
    modal.style.display = 'none';
  };

  btn.addEventListener('click', openModal);
  backdrop?.addEventListener('click', closeModal);
  closeBtn?.addEventListener('click', closeModal);
  okBtn?.addEventListener('click', closeModal);
})();
</script>
<script>
// Fallback: sincroniza pagamentos pendentes ao abrir a pagina (X1/Fantasy)
(function () {
  const pending = window.RR_PENDING_PAYMENTS || { x1: [], fantasy: [] };
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const headers = {
    'Accept': 'application/json',
    'X-CSRF-TOKEN': csrf,
  };

  const x1Pending = (pending.x1 || [])
    .map((p) => p.provider_preference_id)
    .filter(Boolean)
    .slice(0, 8);

  const fantasyPending = (pending.fantasy || [])
    .map((p) => p.provider_preference_id)
    .filter(Boolean)
    .slice(0, 8);

  if (!x1Pending.length && !fantasyPending.length) return;

  const sync = async () => {
    let hasApproved = false;
    let fantasyApprovedCount = 0;
    let x1RefundMessage = '';

    for (const preferenceId of x1Pending) {
      try {
        const res = await fetch('/api/x1/payment-status?preference_id=' + encodeURIComponent(preferenceId), {
          method: 'GET',
          headers,
          credentials: 'same-origin',
        });
        const data = await res.json();
        if (res.ok && String(data.status || '').toLowerCase() === 'approved') {
          hasApproved = true;
        } else if (res.ok && (String(data.status || '').toLowerCase().startsWith('refunded') || data.wallet_refunded)) {
          x1RefundMessage = data.message || 'Uma sala X1 foi preenchida antes da sua confirmacao e o valor voltou para sua carteira.';
        }
      } catch (_) {
        // silencioso no fallback
      }
    }

    for (const preferenceId of fantasyPending) {
      try {
        const res = await fetch('/api/fantasy/payments/' + encodeURIComponent(preferenceId) + '/status', {
          method: 'GET',
          headers,
          credentials: 'same-origin',
        });
        const data = await res.json();
        if (res.ok && String(data.status || '').toLowerCase() === 'approved') {
          hasApproved = true;
          fantasyApprovedCount += 1;
        }
      } catch (_) {
        // silencioso no fallback
      }
    }

    if (hasApproved) {
      if (fantasyApprovedCount > 0) {
        const payload = fantasyApprovedCount > 1
          ? {
              title: 'Equipes confirmadas',
              message: `${fantasyApprovedCount} pagamentos PIX foram aprovados e suas equipes já entraram nos bolões.`,
              leagueName: `${fantasyApprovedCount} entradas confirmadas`,
              methodLabel: 'PIX aprovado',
              entryLabel: 'Confirmado',
              badgeLabel: 'Entradas confirmadas',
            }
          : {
              title: 'Equipe criada com sucesso',
              message: 'Pagamento PIX confirmado. Sua equipe já entrou no bolão.',
              leagueName: 'Bolão na Arena',
              methodLabel: 'PIX aprovado',
              entryLabel: 'Confirmado',
              badgeLabel: 'Equipe confirmada',
            };

        if (typeof window.RRInicioQueueDraftSuccess === 'function') {
          window.RRInicioQueueDraftSuccess(payload);
        } else {
          try {
            sessionStorage.setItem('rr_draft_success_popout', JSON.stringify(payload));
          } catch (_) {
            // silencioso no fallback
          }
        }
      }

      window.location.reload();
      return;
    }

    if (x1RefundMessage) {
      alert(x1RefundMessage);
      window.location.reload();
    }
  };

  // Pequeno atraso para evitar disputa com outros scripts de inicializacao
  setTimeout(sync, 1200);
})();
</script>
