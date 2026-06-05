@extends('frontend.layouts.app')

@php
    $launchSector = trim((string) ($launchSector ?? ''));
    $isBolaoLaunchMode = $launchSector === 'bolao';
    $hubInicioUrl = $isBolaoLaunchMode ? route('hub.bolao.inicio') : route('hub.inicio');
    $hubHeroMode = in_array((string) ($hubLiveMode ?? ''), ['live', 'scheduled'], true)
        ? (string) $hubLiveMode
        : ($activeRodeio ? 'scheduled' : 'empty');
    $rawStreamUrl = $activeRodeio?->stream_url ?? ($hubHeroMode === 'live' ? env('LIVE_STREAM_URL') : null);
    $isAppClient = (bool) request()->query('app');
    $androidAppUrl = (string) config('services.app_download.android_url', '');
    $iosAppUrl = (string) config('services.app_download.ios_url', '');
    $androidStoreUrl = $androidAppUrl !== '' ? $androidAppUrl : 'https://play.google.com/store/apps';
    $iosStoreUrl = $iosAppUrl !== '' ? $iosAppUrl : 'https://apps.apple.com/br/charts/iphone';
    $appQrTargetUrl = $androidAppUrl !== '' ? $androidAppUrl : ($iosAppUrl !== '' ? $iosAppUrl : $androidStoreUrl);
    $appQrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . rawurlencode($appQrTargetUrl);
    $webAppPromoEnabled = false;
    $webAppPromoUserId = auth()->id();
    $webAppPromoGenericUrl = 'reiapp://hub?tab=inicio&source=web_premium_offer';
    $liveStreamUrl = null;

    if (!empty($rawStreamUrl)) {
        if (
            preg_match('/youtube\.com\/(?:watch\?v=|embed\/|live\/)([^&\n?#\/]+)/i', $rawStreamUrl, $match)
            || preg_match('/youtu\.be\/([^&\n?#\/]+)/i', $rawStreamUrl, $match)
        ) {
            $liveStreamUrl = 'https://www.youtube.com/embed/' . $match[1];
        } else {
            $liveStreamUrl = $rawStreamUrl;
        }
    }

    $liveStreamEmbedUrl = null;
    $liveVideoId = null;
    if (!empty($liveStreamUrl)) {
        $liveStreamEmbedUrl = $liveStreamUrl . (str_contains($liveStreamUrl, '?') ? '&' : '?') . 'autoplay=0&modestbranding=1&rel=0';
        if (preg_match('/embed\/([^?&#\/]+)/', $liveStreamUrl, $vidMatch)) {
            $liveVideoId = $vidMatch[1];
        }
    }
    $hubCanPlayLive = $hubHeroMode === 'live' && !empty($liveStreamEmbedUrl);
    $hubHeroTitle = trim((string) ($activeRodeio?->name ?? 'Rei do Rodeio'));
    $hubHeroLogoUrl = $hubRodeioLogoUrl ?? siteLogo();
    $hubHeaderUser = auth()->user();
    $hubHeaderAvatarUrl = $hubHeaderUser && $hubHeaderUser->image
        ? asset(getFilePath('userProfile') . '/' . $hubHeaderUser->image)
        : null;
    $hubHeaderUsername = trim((string) ($hubHeaderUser?->username ?: $hubHeaderUser?->firstname ?: 'Rei do Rodeio'));
    $hubHeaderInitial = strtoupper(substr($hubHeaderUsername, 0, 1));
    $hubHeroBadgeText = $hubHeroMode === 'live' ? 'AO VIVO' : 'PROGRAMADO';
    $hubHeroHint = match ($hubHeroMode) {
        'live' => $hubCanPlayLive ? 'Dê play no rodeio!' : 'Transmissão em preparação',
        'scheduled' => 'Próximo rodeio confirmado',
        default => 'Aguarde a próxima programação',
    };
    $hubMobileWalletBalance = auth()->check() ? (float) (auth()->user()->balance ?? 0) : 0;
    $hubMobileWalletLabel = 'R$ ' . number_format($hubMobileWalletBalance, 2, ',', '.');
    $hubMobileMembershipLabel = auth()->check() ? 'Bilhetes do Bolão' : null;
    $hubMobileVoucherTicker = collect();

    if (auth()->check()) {
        $voucherRows = auth()->user()
            ->vouchers()
            ->where('voucher_type', 'fantasy_ticket')
            ->whereIn('credit_amount', [20.00, 50.00, 100.00])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get(['status', 'credit_amount', 'remaining_uses']);

        $hubMobileVoucherTicker = collect([20, 50, 100])->map(function (int $amount) use ($voucherRows) {
            $tierRows = $voucherRows->filter(fn ($voucher) => (float) $voucher->credit_amount === (float) $amount);
            $activeCount = $tierRows
                ->filter(fn ($voucher) => (string) $voucher->status === 'active' && (int) ($voucher->remaining_uses ?? 0) > 0)
                ->count();
            $totalCount = $tierRows->count();

            return [
                'amount' => $amount,
                'label' => $activeCount . '/' . $totalCount . ' R$' . $amount,
            ];
        })->values();
    }
@endphp

@section('hub_header')
<!-- Brand header (sticky navbar-wrapper KTO-style) -->
@if($isBolaoLaunchMode)
<style>
    :root {
        --rr-bolao-launch-width: min(1680px, calc(100vw - 32px));
        --rr-bolao-launch-content-pad: clamp(14px, 1.35vw, 18px);
    }
    .hub-navbar-wrapper {
        display: none !important;
    }
    :root {
        --hub-navbar-height: 0px !important;
        --hub-navbar-offset: 0px !important;
    }
    body.hub-page main.rr-container {
        padding-top: 0 !important;
    }
    .hub-top__grid {
        grid-template-columns: 1fr !important;
        justify-items: center;
        align-items: center;
        align-content: center;
    }
    .hub-top__main {
        width: 100%;
        max-width: 100%;
    }
    @media (max-width: 768px) {
        :root {
            --hub-navbar-height: 0px !important;
            --hub-navbar-offset: 0px !important;
        }
        body.hub-page main.rr-container {
            padding-top: 0 !important;
        }
    }
</style>
@endif
<header class="hub-navbar-wrapper {{ $isAppClient ? 'hub-navbar-wrapper--app' : 'hub-navbar-wrapper--site' }}" id="hubBrandOverlay">
    <nav class="hub-navbar-inner" aria-label="Hub Navigation">
        <div class="hub-navbar-content">
            <!-- Left: logo + marca -->
            <div class="hub-navbar-left">
                <a class="hub-navbar-logo {{ auth()->check() ? 'hub-navbar-logo--wallet' : '' }}" href="{{ route('home') }}">
                    @auth
                    <span class="hub-navbar-logo__avatar" aria-hidden="true">
                        @if($hubHeaderAvatarUrl)
                            <img class="hub-navbar-logo__avatar-img" src="{{ $hubHeaderAvatarUrl }}" alt="{{ $hubHeaderUsername }}">
                        @else
                            <span class="hub-navbar-logo__avatar-fallback">{{ $hubHeaderInitial }}</span>
                        @endif
                    </span>
                    <span class="hub-navbar-logo__identity">
                        <span class="hub-navbar-logo__eyebrow">Bem vindo</span>
                        <span class="hub-navbar-logo__user">
                            {{ $hubHeaderUsername }}
                            @if(auth()->user()->isPremium())
                                <i class="fas fa-crown hub-navbar-logo__crown"></i>
                            @endif
                        </span>
                    </span>
                    @else
                    <img class="hub-navbar-logo__img" src="{{ siteLogo() }}" alt="Rei do Rodeio">
                    <img class="hub-navbar-logo__img hub-navbar-logo__img--premium" src="{{ versionedAsset('assets/images/logo_icon/premiumleague.png') }}" alt="Rei do Rodeio Premium">
                    @endauth
                    @auth
                    <span class="hub-navbar-mobile-tools">
                        <span class="hub-navbar-mobile-wallet" aria-label="Carteira do cliente">
                            <span class="hub-navbar-mobile-wallet__copy">
                                <strong class="hub-navbar-mobile-wallet__value">{{ $hubMobileWalletLabel }}</strong>
                            </span>
                            <button type="button"
                                    class="hub-navbar-mobile-wallet__plus"
                                    aria-label="Adicionar saldo"
                                    onclick="window.switchHubTab ? window.switchHubTab('loja') : window.location='{{ route('hub.loja') }}'">
                                <i class="fas fa-plus" aria-hidden="true"></i>
                            </button>
                        </span>
                        <span class="hub-navbar-mobile-voucher-ticker {{ auth()->user()->isPremium() ? 'is-premium' : 'is-free' }}" aria-label="Bilhetes do bolão">
                            <span class="hub-navbar-mobile-voucher-ticker__member">{{ $hubMobileMembershipLabel }}</span>
                            <span class="hub-navbar-mobile-voucher-ticker__window" data-voucher-ticker>
                                @foreach ($hubMobileVoucherTicker as $voucherTickerItem)
                                    <span class="hub-navbar-mobile-voucher-ticker__item{{ $loop->first ? ' is-visible' : '' }}">
                                        {{ $voucherTickerItem['label'] }}
                                    </span>
                                @endforeach
                            </span>
                            <button type="button"
                                    class="hub-navbar-mobile-voucher-ticker__plus"
                                    aria-label="Comprar bilhetes do bolão"
                                    onclick="try { sessionStorage.setItem('rr_store_initial_panel', 'bolao'); } catch(e) {} ; window.switchHubTab ? window.switchHubTab('loja') : window.location='{{ route('hub.loja') }}'">
                                <i class="fas fa-plus" aria-hidden="true"></i>
                            </button>
                        </span>
                    </span>
                    @else
                    <span class="hub-navbar-logo__name rr-ethnocentric">REI DO RODEIO</span>
                    @endauth
                </a>

                @guest
                <div class="hub-navbar-cta-group">
                    <button type="button" class="hub-navbar-btn hub-navbar-btn--primary hub-navbar-btn--entrar hub-navbar-btn--after-menu" onclick="window.openAuthModal ? window.openAuthModal() : window.RRAuthModal?.open()">ENTRAR NA ARENA</button>
                </div>
                @endguest
            </div>

            <!-- Desktop horizontal nav tabs -->
            <nav class="hub-header-nav" id="hubHeaderNav" role="tablist">
                @if($isBolaoLaunchMode)
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--orange active" data-section="inicio" data-url="{{ $hubInicioUrl }}" data-accent="#f59e0be6">
                    <i class="fas fa-home"></i> @lang('Início')
                </button>
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--blue" data-section="pix" data-profile-target="financeiro" data-url="{{ route('hub.perfil') }}" data-accent="#f59e0be6">
                    <i class="fas fa-wallet"></i> @lang('Pix')
                </button>
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--green" data-action="user">
                    <i class="fas fa-user-edit"></i> @lang('Editar Perfil')
                </button>
                @else
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--orange active" data-section="inicio" data-url="{{ $hubInicioUrl }}" data-accent="#f59e0be6">
                    <i class="fas fa-home"></i> @lang('Início')
                </button>
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--green" data-section="estatisticas" data-url="{{ route('hub.stats') }}" data-accent="#f59e0be6">
                    <i class="fas fa-chart-bar"></i> @lang('Estatísticas')
                </button>
                @if(!$isAppClient)
                    <button type="button" class="hub-header-nav__btn hub-header-nav__btn--store" data-section="loja" data-url="{{ route('hub.loja') }}" data-accent="#f59e0be6">
                        <i class="fas fa-store"></i> @lang('Loja')
                    </button>
                @endif
                @if(!auth()->check() || !auth()->user()->isPremium())
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--purple" data-section="premium" data-url="{{ route('hub.premium') }}" data-accent="#f59e0be6">
                    <i class="fas fa-crown"></i> @lang('Premium')
                </button>
                @endif
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--blue" data-section="pix" data-profile-target="financeiro" data-url="{{ route('hub.perfil') }}" data-accent="#f59e0be6">
                    <i class="fas fa-wallet"></i> @lang('Pix')
                </button>
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--green" data-action="user">
                    <i class="fas fa-user-edit"></i> @lang('Editar Perfil')
                </button>
                @auth
                <button type="button" class="hub-header-nav__btn hub-header-nav__btn--red" data-action="logout">
                    <i class="fas fa-sign-out-alt"></i> @lang('Sair')
                </button>
                @endauth
                @endif
            </nav>

            <div class="hub-navbar-actions">
                @if($isAppClient)
                <button type="button" class="hub-app-return-btn hub-app-return-btn--desktop" data-app-return-community>
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    <span>Voltar para comunidade</span>
                </button>
                @endif

                <button type="button" class="hub-help-btn hub-help-btn--desktop" id="openHelpModalDesktop" title="Abrir central de ajuda">
                    <span>Ajuda</span>
                </button>

            </div>
        </div>

        @if($isAppClient)
        <button type="button" class="hub-app-return-btn hub-app-return-btn--mobile" data-app-return-community>
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            <span>Voltar para comunidade</span>
        </button>
        @endif

        <!-- Mobile bottom: login button -->
        @guest
        <div class="hub-navbar-mobile-auth">
            <button type="button" class="hub-navbar-btn hub-navbar-btn--primary hub-navbar-btn--grow hub-navbar-btn--entrar" onclick="window.openAuthModal ? window.openAuthModal() : window.RRAuthModal?.open()">ENTRAR NA ARENA</button>
        </div>
        @endguest

        <div class="hub-platform-switcher {{ $isAppClient ? 'is-app' : 'is-site' }}" id="hubPlatformSwitcher" aria-label="Alternar entre site e aplicativo">
            <button type="button" class="hub-platform-switcher__item {{ $isAppClient ? '' : 'is-active' }}" data-platform-target="site">
                <span class="hub-platform-switcher__icon">
                    <i class="fas fa-globe-americas" aria-hidden="true"></i>
                </span>
                <span class="hub-platform-switcher__label">Site</span>
            </button>
            <button type="button" class="hub-platform-switcher__chip {{ $isAppClient ? 'is-active' : '' }}" data-platform-target="app">
                <span class="hub-platform-switcher__chip-glow" aria-hidden="true"></span>
                <span class="hub-platform-switcher__icon">
                    <i class="fas fa-mobile-alt" aria-hidden="true"></i>
                </span>
                <span class="hub-platform-switcher__label">{{ $isAppClient ? 'No app' : 'Abrir app' }}</span>
            </button>
        </div>

        {{-- Mobile: Bem-vindo + username (só logado) --}}
        @auth
        <div class="hub-mobile-welcome">
            <div class="hub-mobile-welcome__greeting">Bem vindo!</div>
            <div class="hub-mobile-welcome__user">
                {{ auth()->user()->username }}
                @if(auth()->user()->isPremium())
                <i class="fas fa-crown hub-mobile-welcome__crown"></i>
                @endif
            </div>
        </div>
        @endauth

        <button type="button" class="hub-mobile-help-bubble" id="hubMobileHelpBubble" aria-label="Ajuda">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                <path d="M9.6 9.2a2.5 2.5 0 1 1 4.1 2c-.7.5-1.4 1-1.4 2v.3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                <circle cx="12" cy="16.8" r="1.1" fill="currentColor"></circle>
            </svg>
            <span class="hub-mobile-help-bubble__hint">Precisa de ajuda?</span>
        </button>
        </div>
    </nav>
</header>
@endsection

@section('content')
<div class="hub-portal-transition" id="hubPortalTransition" aria-hidden="true">
    <div class="hub-portal-transition__shade"></div>
    <div class="hub-portal-transition__curtain"></div>
    <div class="hub-portal-transition__glow"></div>
</div>

<div class="hub-help-popout" id="hubHelpPopout" aria-hidden="true">
    <div class="hub-help-popout__backdrop" data-close-help></div>
    <div class="hub-help-popout__panel" role="dialog" aria-modal="true" aria-label="Ajuda">
        <div class="hub-help-popout__header">
            <div>
                <div class="hub-help-popout__title">Central de Ajuda</div>
                <div class="hub-help-popout__subtitle">Escolha como quer aprender ou falar com o suporte.</div>
            </div>
            <button type="button" class="hub-help-popout__close" data-close-help aria-label="Fechar">×</button>
        </div>

        <div class="hub-help-popout__body">
            <div class="hub-help-view is-active" data-help-view="home">
                <div class="hub-help-choice-grid">
                    <button type="button" class="hub-help-choice" data-help-action="show-tutorials">
                        <i class="fas fa-play-circle" aria-hidden="true"></i>
                        <span>Tutoriais</span>
                    </button>
                    <button type="button" class="hub-help-choice" data-help-action="live-support">
                        <i class="fas fa-headset" aria-hidden="true"></i>
                        <span>Suporte ao vivo</span>
                    </button>
                </div>
            </div>

            <div class="hub-help-view" data-help-view="tutorials">
                <div class="hub-help-topics">
                    <button type="button" class="hub-help-topic-btn" data-help-topic="estatisticas">Estatísticas</button>
                    <button type="button" class="hub-help-topic-btn" data-help-topic="x1">X1</button>
                    <button type="button" class="hub-help-topic-btn" data-help-topic="bolao">Bolão</button>
                </div>
                <div class="hub-help-inline-actions">
                    <button type="button" class="hub-help-back-btn" data-help-action="back-home">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Voltar
                    </button>
                </div>
            </div>

            <div class="hub-help-view" data-help-view="video">
                <div class="hub-help-video-title" id="hubHelpVideoTitle">Tutorial</div>
                <div class="hub-help-video-wrap">
                    <iframe id="hubHelpVideoFrame"
                            src=""
                            title="Tutorial"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen></iframe>
                </div>
                <div class="hub-help-inline-actions">
                    <button type="button" class="hub-help-back-btn" data-help-action="back-tutorials">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Voltar
                    </button>
                    <button type="button" class="hub-help-back-btn" data-close-help>Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="hub-app-download-modal" id="hubAppDownloadModal" aria-hidden="true">
    <div class="hub-app-download-modal__backdrop" data-close-app-download></div>
    <div class="hub-app-download-modal__dialog" role="dialog" aria-modal="true" aria-label="Baixar aplicativo Rei do Rodeio">
        <button type="button" class="hub-app-download-modal__close" data-close-app-download aria-label="Fechar">×</button>
        <div class="hub-app-download-modal__eyebrow">BAIXE O APP REI DO RODEIO</div>
        <h3 class="hub-app-download-modal__title">Escaneie o QR code ou abra a loja oficial</h3>
        <p class="hub-app-download-modal__text">
            No computador, aponte a câmera do celular para o QR code. No celular, você também pode abrir direto pela loja oficial abaixo.
        </p>
        <div class="hub-app-download-modal__qr-wrap">
            <div class="hub-app-download-modal__qr-frame">
                <img
                    class="hub-app-download-modal__qr-image"
                    src="{{ $appQrImageUrl }}"
                    alt="QR code para baixar o app Rei do Rodeio"
                >
            </div>
            <div class="hub-app-download-modal__qr-caption">
                QR code genérico temporário apontando para a loja oficial.
            </div>
        </div>
        <div class="hub-app-download-modal__actions">
            <a class="hub-app-download-modal__store hub-app-download-modal__store--android" href="{{ $androidStoreUrl }}" target="_blank" rel="noopener">
                <i class="fab fa-google-play" aria-hidden="true"></i>
                <span>Baixar no Android</span>
            </a>
            <a class="hub-app-download-modal__store hub-app-download-modal__store--ios" href="{{ $iosStoreUrl }}" target="_blank" rel="noopener">
                <i class="fab fa-apple" aria-hidden="true"></i>
                <span>Baixar no iPhone</span>
            </a>
        </div>
        <button type="button" class="hub-app-download-modal__secondary" id="hubRetryAppOpen">
            Tentar abrir o app agora
        </button>
    </div>
</div>

<div class="hub-web-app-promo" id="hubWebAppPromo" aria-hidden="true">
    <div class="hub-web-app-promo__backdrop" data-close-web-app-promo></div>
    <div class="hub-web-app-promo__dialog" role="dialog" aria-modal="true" aria-label="Benefício para baixar o aplicativo">
        <button type="button" class="hub-web-app-promo__close" data-close-web-app-promo aria-label="Fechar">×</button>
        <div class="hub-web-app-promo__eyebrow">VANTAGEM EXCLUSIVA NO APP</div>
        <h3 class="hub-web-app-promo__title">Baixe o app e ganhe 1 mês de Premium</h3>
        <p class="hub-web-app-promo__text">
            Entrando pelo aplicativo você libera uma experiência mais rápida na arena e ainda participa da campanha de
            <strong>1 mês de Premium</strong> para quem baixar e fizer login pelo app.
        </p>
        <div class="hub-web-app-promo__highlight">
            <i class="fas fa-crown" aria-hidden="true"></i>
            <span>Abra o app no celular e faça login na sua conta para ativar esse benefício.</span>
        </div>
        <div class="hub-web-app-promo__actions">
            <a
                class="hub-web-app-promo__store hub-web-app-promo__store--android"
                href="{{ $webAppPromoGenericUrl }}"
                data-open-native-app
                data-platform="android"
            >
                <i class="fab fa-google-play" aria-hidden="true"></i>
                <span>Google Play</span>
            </a>
            <a
                class="hub-web-app-promo__store hub-web-app-promo__store--ios"
                href="{{ $webAppPromoGenericUrl }}"
                data-open-native-app
                data-platform="ios"
            >
                <i class="fab fa-apple" aria-hidden="true"></i>
                <span>App Store</span>
            </a>
        </div>
        <div class="hub-web-app-promo__note">
            Links de teste: por enquanto os botões tentam abrir o app diretamente.
        </div>
        <button type="button" class="hub-web-app-promo__secondary" data-close-web-app-promo>
            Continuar no site
        </button>
    </div>
</div>

<script>
(function() {
    var themeToggles = Array.prototype.slice.call(document.querySelectorAll('.rr-tt[data-theme-toggle="hub"]'));
    var desktopHelpBtn = document.getElementById('openHelpModalDesktop');
    var mobileHelpBubble = document.getElementById('hubMobileHelpBubble');
    var helpPopout = document.getElementById('hubHelpPopout');
    var helpClosers = document.querySelectorAll('[data-close-help]');
    var helpViews = helpPopout ? helpPopout.querySelectorAll('[data-help-view]') : [];
    var helpVideoFrame = document.getElementById('hubHelpVideoFrame');
    var helpVideoTitle = document.getElementById('hubHelpVideoTitle');
    var HELP_BUBBLE_NUDGE_KEY = 'hub_help_bubble_nudge_at';
    var HELP_BUBBLE_NUDGE_INTERVAL_MS = 60 * 60 * 1000; // 1h

    var HELP_TUTORIALS = {
        estatisticas: { title: 'Tutorial de Estatísticas', url: 'https://www.youtube.com/watch?v=VIDEO_ID_ESTATISTICAS' },
        x1: { title: 'Tutorial do X1', url: 'https://www.youtube.com/watch?v=VIDEO_ID_X1' },
        bolao: { title: 'Tutorial do Bolão', url: 'https://www.youtube.com/watch?v=VIDEO_ID_BOLAO' }
    };
    var TAWK_WIDGET_URL = 'https://embed.tawk.to/69b347496d0a751c37337702/1jji4sude';
    var tawkLoadPromise = null;

    function getThemeButtons(toggle) {
        if (!toggle) return { darkBtn: null, lightBtn: null };
        return {
            darkBtn: toggle.querySelector('[data-theme-mode="dark"]'),
            lightBtn: toggle.querySelector('[data-theme-mode="light"]')
        };
    }

    function ensureThemeIndicator(toggle) {
        if (!toggle) return null;
        var indicator = toggle.querySelector('.rr-tt-indicator');
        if (!indicator) {
            indicator = document.createElement('span');
            indicator.className = 'rr-tt-indicator';
            toggle.insertBefore(indicator, toggle.firstChild);
        }
        return indicator;
    }

    function updateThemeIndicator(toggle, immediate) {
        if (!toggle) return;
        var indicator = ensureThemeIndicator(toggle);
        var activeBtn = toggle.querySelector('.rr-tt-btn.is-active') || toggle.querySelector('.rr-tt-btn[aria-pressed="true"]');
        if (!indicator || !activeBtn) return;

        var x = activeBtn.offsetLeft;
        var w = activeBtn.offsetWidth;
        if (immediate) {
            indicator.style.transition = 'none';
        } else {
            indicator.style.transition = '';
        }
        toggle.style.setProperty('--tt-indicator-x', x + 'px');
        toggle.style.setProperty('--tt-indicator-w', w + 'px');

        if (immediate) {
            requestAnimationFrame(function() {
                indicator.style.transition = '';
            });
        }
    }

    function refreshThemeToggles(immediate) {
        themeToggles.forEach(function(toggle) {
            updateThemeIndicator(toggle, !!immediate);
        });
    }

    function triggerThemeFeedback(button) {
        if (!button) return;
        button.classList.add('is-pressed');
        setTimeout(function() {
            button.classList.remove('is-pressed');
        }, 140);

        try {
            if (window.matchMedia && window.matchMedia('(max-width: 768px)').matches && navigator.vibrate) {
                navigator.vibrate(10);
            }
        } catch (e) {}
    }

    function spawnThemeRipple(button, clientX, clientY) {
        if (!button) return;
        var rect = button.getBoundingClientRect();
        var ripple = document.createElement('span');
        ripple.className = 'rr-tt-ripple';
        ripple.style.left = (clientX - rect.left) + 'px';
        ripple.style.top = (clientY - rect.top) + 'px';
        button.appendChild(ripple);
        ripple.addEventListener('animationend', function() {
            ripple.remove();
        }, { once: true });
    }

    function syncThemeButtons() {
        var isLight = document.body.classList.contains('light');
        themeToggles.forEach(function(toggle) {
            if (!toggle) return;
            var parts = getThemeButtons(toggle);
            var darkBtn = parts.darkBtn;
            var lightBtn = parts.lightBtn;
            var darkActive = !isLight;
            var lightActive = isLight;

            if (darkBtn) {
                darkBtn.classList.toggle('is-active', darkActive);
                darkBtn.setAttribute('aria-pressed', darkActive ? 'true' : 'false');
            }
            if (lightBtn) {
                lightBtn.classList.toggle('is-active', lightActive);
                lightBtn.setAttribute('aria-pressed', lightActive ? 'true' : 'false');
            }
        });
        refreshThemeToggles(false);
    }

    function setTheme(mode, triggerButton) {
        if (mode !== 'light' && mode !== 'dark') return;

        var nextIsLight = mode === 'light';
        var currentIsLight = document.body.classList.contains('light');

        if (nextIsLight === currentIsLight) {
            syncThemeButtons();
            refreshThemeToggles(false);
            return;
        }

        document.body.classList.toggle('light', nextIsLight);
        try { localStorage.setItem('hub-theme', mode); } catch(e) {}
        syncThemeButtons();

        if (triggerButton) {
            triggerThemeFeedback(triggerButton);
        }
    }

    function syncThemeFromState(immediate) {
        syncThemeButtons();
        refreshThemeToggles(!!immediate);
    }

    function bindThemeToggle(toggle) {
        if (!toggle) return;
        ensureThemeIndicator(toggle);
        var drag = {
            active: false,
            pointerId: null,
            startX: 0,
            startIndicatorX: 0,
            moved: false,
            suppressClickUntil: 0
        };

        function isMobileViewport() {
            return window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
        }

        function getIndicatorX() {
            var raw = getComputedStyle(toggle).getPropertyValue('--tt-indicator-x') || '';
            var value = parseFloat(raw);
            if (Number.isFinite(value)) return value;
            var activeBtn = toggle.querySelector('.rr-tt-btn.is-active') || toggle.querySelector('.rr-tt-btn[aria-pressed="true"]');
            return activeBtn ? activeBtn.offsetLeft : 0;
        }

        function applyIndicatorX(x, immediate) {
            var indicator = ensureThemeIndicator(toggle);
            if (!indicator) return;
            if (immediate) indicator.style.transition = 'none';
            toggle.style.setProperty('--tt-indicator-x', x + 'px');
            if (immediate) {
                requestAnimationFrame(function() {
                    indicator.style.transition = '';
                });
            }
        }

        function snapThemeFromIndicator() {
            var parts = getThemeButtons(toggle);
            var darkBtn = parts.darkBtn;
            var lightBtn = parts.lightBtn;
            if (!darkBtn || !lightBtn) return;

            var x = getIndicatorX();
            var darkDist = Math.abs(x - darkBtn.offsetLeft);
            var lightDist = Math.abs(x - lightBtn.offsetLeft);
            var targetBtn = lightDist < darkDist ? lightBtn : darkBtn;
            var mode = targetBtn.getAttribute('data-theme-mode');
            setTheme(mode, targetBtn);
        }

        function onPointerMove(e) {
            if (!drag.active || e.pointerId !== drag.pointerId) return;
            var parts = getThemeButtons(toggle);
            var darkBtn = parts.darkBtn;
            var lightBtn = parts.lightBtn;
            if (!darkBtn || !lightBtn) return;

            var dx = e.clientX - drag.startX;
            var minX = Math.min(darkBtn.offsetLeft, lightBtn.offsetLeft);
            var maxX = Math.max(darkBtn.offsetLeft, lightBtn.offsetLeft);
            var next = Math.max(minX, Math.min(maxX, drag.startIndicatorX + dx));

            drag.moved = drag.moved || Math.abs(dx) > 5;
            applyIndicatorX(next, false);
            e.preventDefault();
        }

        function endPointerDrag(e) {
            if (!drag.active || e.pointerId !== drag.pointerId) return;
            try { toggle.releasePointerCapture(e.pointerId); } catch (_) {}
            drag.active = false;
            drag.pointerId = null;
            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', endPointerDrag);
            window.removeEventListener('pointercancel', endPointerDrag);

            if (drag.moved) {
                drag.suppressClickUntil = Date.now() + 260;
                snapThemeFromIndicator();
            } else {
                refreshThemeToggles(false);
            }
        }

        toggle.addEventListener('pointerdown', function(e) {
            var button = e.target.closest('[data-theme-mode]');
            if (!button || !toggle.contains(button)) return;
            spawnThemeRipple(button, e.clientX, e.clientY);

            // Drag only on mobile: indicator follows finger and snaps on release.
            if (!isMobileViewport() || e.pointerType === 'mouse') return;

            drag.active = true;
            drag.pointerId = e.pointerId;
            drag.startX = e.clientX;
            drag.startIndicatorX = getIndicatorX();
            drag.moved = false;

            try { toggle.setPointerCapture(e.pointerId); } catch (_) {}
            window.addEventListener('pointermove', onPointerMove);
            window.addEventListener('pointerup', endPointerDrag);
            window.addEventListener('pointercancel', endPointerDrag);
        });

        toggle.addEventListener('click', function(e) {
            if (Date.now() < drag.suppressClickUntil) {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            var button = e.target.closest('[data-theme-mode]');
            if (!button || !toggle.contains(button)) return;
            var mode = button.getAttribute('data-theme-mode');
            setTheme(mode, button);
        });
    }

    function toYouTubeEmbed(url) {
        if (!url) return '';
        var m = String(url).match(/(?:youtube\.com\/(?:watch\?v=|embed\/|live\/)|youtu\.be\/)([^&\n?#\/]+)/i);
        if (!m || !m[1]) return url;
        return 'https://www.youtube.com/embed/' + m[1] + '?autoplay=1&rel=0&modestbranding=1';
    }

    function setHelpView(name) {
        if (!helpViews || !helpViews.length) return;
        helpViews.forEach(function(view) {
            var active = view.getAttribute('data-help-view') === name;
            view.classList.toggle('is-active', active);
        });
    }

    function resetHelpModal() {
        setHelpView('home');
        if (helpVideoFrame) helpVideoFrame.src = '';
        if (helpVideoTitle) helpVideoTitle.textContent = 'Tutorial';
    }

    function openHelp() {
        if (!helpPopout) return;
        if (mobileHelpBubble) mobileHelpBubble.classList.remove('is-nudging');
        resetHelpModal();
        helpPopout.classList.add('is-open');
        helpPopout.setAttribute('aria-hidden', 'false');
    }

    function closeHelp() {
        if (!helpPopout) return;
        if (helpVideoFrame) helpVideoFrame.src = '';
        helpPopout.classList.remove('is-open');
        helpPopout.setAttribute('aria-hidden', 'true');
    }

    function ensureLiveSupportWidget() {
        var api = window.Tawk_API;
        if (api && (typeof api.maximize === 'function' || typeof api.toggle === 'function')) {
            return Promise.resolve(api);
        }

        if (tawkLoadPromise) {
            return tawkLoadPromise;
        }

        tawkLoadPromise = new Promise(function(resolve, reject) {
            var settled = false;
            var existingScript = document.querySelector('script[data-tawk-live-support="1"]');
            var currentApi = window.Tawk_API = window.Tawk_API || {};
            var previousOnLoad = currentApi.onLoad;

            function settleSuccess(nextApi) {
                if (settled) return;
                settled = true;
                resolve(nextApi || window.Tawk_API || currentApi);
            }

            function settleFailure(error) {
                if (settled) return;
                settled = true;
                tawkLoadPromise = null;
                reject(error);
            }

            currentApi.onLoad = function() {
                try {
                    if (typeof previousOnLoad === 'function') previousOnLoad();
                } catch (e) {}
                settleSuccess(window.Tawk_API || currentApi);
            };

            if (existingScript) {
                window.setTimeout(function() {
                    var readyApi = window.Tawk_API;
                    if (readyApi && (typeof readyApi.maximize === 'function' || typeof readyApi.toggle === 'function')) {
                        settleSuccess(readyApi);
                        return;
                    }
                    settleFailure(new Error('Tawk widget not ready'));
                }, 2500);
                return;
            }

            window.Tawk_LoadStart = new Date();
            var script = document.createElement('script');
            script.async = true;
            script.src = TAWK_WIDGET_URL;
            script.charset = 'UTF-8';
            script.setAttribute('crossorigin', '*');
            script.setAttribute('data-tawk-live-support', '1');
            script.onerror = function() {
                settleFailure(new Error('Failed to load Tawk script'));
            };
            script.onload = function() {
                window.setTimeout(function() {
                    var readyApi = window.Tawk_API;
                    if (readyApi && (typeof readyApi.maximize === 'function' || typeof readyApi.toggle === 'function')) {
                        settleSuccess(readyApi);
                    }
                }, 500);
            };

            var firstScript = document.getElementsByTagName('script')[0];
            if (firstScript && firstScript.parentNode) {
                firstScript.parentNode.insertBefore(script, firstScript);
            } else {
                (document.head || document.body || document.documentElement).appendChild(script);
            }

            window.setTimeout(function() {
                var readyApi = window.Tawk_API;
                if (readyApi && (typeof readyApi.maximize === 'function' || typeof readyApi.toggle === 'function')) {
                    settleSuccess(readyApi);
                    return;
                }
                settleFailure(new Error('Timed out waiting for Tawk widget'));
            }, 10000);
        });

        return tawkLoadPromise;
    }

    function openLiveSupport() {
        return ensureLiveSupportWidget().then(function(api) {
            closeHelp();
            if (api && typeof api.maximize === 'function') {
                api.maximize();
                return true;
            }
            if (api && typeof api.toggle === 'function') {
                api.toggle();
                return true;
            }
            throw new Error('Tawk API unavailable');
        });
    }

    function maybeNudgeHelpBubble() {
        if (!mobileHelpBubble) return;
        if (!(window.matchMedia && window.matchMedia('(max-width: 599px)').matches)) return;

        var now = Date.now();
        var last = 0;
        try {
            last = parseInt(localStorage.getItem(HELP_BUBBLE_NUDGE_KEY) || '0', 10) || 0;
        } catch (e) {
            last = 0;
        }
        if (now - last < HELP_BUBBLE_NUDGE_INTERVAL_MS) return;

        try { localStorage.setItem(HELP_BUBBLE_NUDGE_KEY, String(now)); } catch (e) {}

        setTimeout(function() {
            mobileHelpBubble.classList.add('is-nudging');
            setTimeout(function() {
                mobileHelpBubble.classList.remove('is-nudging');
            }, 2600);
        }, 900);
    }

    themeToggles.forEach(function(toggle) {
        bindThemeToggle(toggle);
    });
    syncThemeFromState(true);
    if (desktopHelpBtn) desktopHelpBtn.addEventListener('click', openHelp);
    if (mobileHelpBubble) mobileHelpBubble.addEventListener('click', openHelp);
    maybeNudgeHelpBubble();

    window.addEventListener('resize', function() {
        refreshThemeToggles(true);
    });

    // Keep toggle UI synced even if another script toggles body.light after load.
    try {
        var bodyThemeObserver = new MutationObserver(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].attributeName === 'class') {
                    syncThemeFromState(false);
                    break;
                }
            }
        });
        bodyThemeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    } catch (e) {}

    window.addEventListener('pageshow', function() {
        syncThemeFromState(true);
    });

    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) syncThemeFromState(false);
    });

    // Safety sync for late theme application on refresh.
    setTimeout(function() { syncThemeFromState(true); }, 0);
    setTimeout(function() { syncThemeFromState(false); }, 180);

    if (helpClosers && helpClosers.length) {
        helpClosers.forEach(function(el) {
            el.addEventListener('click', closeHelp);
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeHelp();
    });

    if (helpPopout) {
        helpPopout.addEventListener('click', function(e) {
            var actionBtn = e.target.closest('[data-help-action]');
            var topicBtn = e.target.closest('[data-help-topic]');

            if (actionBtn) {
                var action = actionBtn.getAttribute('data-help-action');
                if (action === 'show-tutorials') {
                    setHelpView('tutorials');
                    return;
                }
                if (action === 'back-home') {
                    setHelpView('home');
                    return;
                }
                if (action === 'back-tutorials') {
                    if (helpVideoFrame) helpVideoFrame.src = '';
                    setHelpView('tutorials');
                    return;
                }
                if (action === 'live-support') {
                    actionBtn.disabled = true;
                    openLiveSupport()
                        .catch(function() {
                            alert('Suporte ao vivo indisponível no momento.');
                        })
                        .finally(function() {
                            actionBtn.disabled = false;
                        });
                    return;
                }
            }

            if (topicBtn) {
                var topic = topicBtn.getAttribute('data-help-topic');
                var conf = HELP_TUTORIALS[topic];
                if (!conf) return;
                if (helpVideoTitle) helpVideoTitle.textContent = conf.title;
                if (helpVideoFrame) helpVideoFrame.src = toYouTubeEmbed(conf.url);
                setHelpView('video');
            }
        });
    }
})();
</script>

<section class="hub-hero hub-top" id="hubTop">
    <div class="hub-top__grid">
            @include('frontend.partials.hub_mobile_tabbar', ['isBolaoLaunchMode' => $isBolaoLaunchMode, 'hubInicioUrl' => $hubInicioUrl])

        <!-- Conteúdo dinâmico (antes ficava em hub-shell, agora é grid-area do hub-top__grid) -->
        <div id="hubMainColumn" class="hub-top__main">
            <div id="hubSection" class="hub-section">
                <div class="hub-section__placeholder" id="hubSectionPlaceholder">
                    <div class="spinner" aria-hidden="true"></div>
                    <div>
                        <p class="mb-1 fw-semibold">@lang('Selecione uma aba para carregar o conteúdo.')</p>
                        <small class="text-muted d-block">@lang('As informações aparecem logo abaixo desta seção.')</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- hub-shell mantido vazio para não quebrar referências CSS -->
<section class="hub-shell"></section>

@auth
@php
    $hubUser = auth()->user();
    $hubAvatarUrl = $hubUser->image ? asset(getFilePath('userProfile') . '/' . $hubUser->image) : null;
    $hubIsPremium = method_exists($hubUser, 'isPremium') ? $hubUser->isPremium() : false;
@endphp
<div class="hub-profile-popout" id="hubProfilePopout" aria-hidden="true">
    <div class="hub-profile-popout__backdrop" data-close-profile></div>
    <div class="hub-profile-popout__panel" role="dialog" aria-modal="true" aria-label="Perfil">
        <div class="hub-profile-popout__header">
            <div>
                <div class="hub-profile-popout__title">@lang('Atenção: para receber prêmios mantenha seu perfil sempre atualizado!')</div>
                <div class="hub-profile-popout__subtitle">@lang('Campos já cadastrados ficam bloqueados.')</div>
            </div>
            <button type="button" class="hub-profile-popout__close" data-close-profile aria-label="Fechar">×</button>
        </div>

        <div class="hub-profile-popout__body">
            <div class="hub-profile-popout__alert" id="hubProfileAlert" style="display:none"></div>

            <form id="hubProfileForm" method="post" enctype="multipart/form-data" action="{{ route('user.profile.update') }}">
                @csrf

                <div class="hub-profile-photo">
                    <div class="hub-profile-photo__preview" aria-label="Foto de perfil">
                        @if($hubAvatarUrl)
                            <img id="hubProfileAvatar" src="{{ $hubAvatarUrl }}" alt="Foto de perfil" data-initial-src="{{ $hubAvatarUrl }}" data-had-image="1" />
                        @else
                            <div class="hub-profile-photo__placeholder" id="hubProfileAvatarPlaceholder">
                                <i class="fas fa-user" aria-hidden="true"></i>
                            </div>
                            <img id="hubProfileAvatar" src="" alt="Foto de perfil" style="display:none" data-initial-src="" data-had-image="0" />
                        @endif
                    </div>

                    <div class="hub-profile-photo__controls">
                        <label class="hub-profile-photo__label">@lang('Foto de perfil')</label>
                        <input class="hub-profile-input" type="file" name="image" id="hubProfileImage" accept="image/*" />
                        <small class="hub-profile-help">@lang('JPG, PNG ou WEBP (até 5MB).')</small>
                    </div>
                </div>

                <div class="hub-profile-grid">
                    <div class="hub-profile-field">
                        <label class="hub-profile-label">@lang('Primeiro nome')</label>
                        <input class="hub-profile-input" type="text" name="firstname" value="{{ $hubUser->firstname ?? '' }}" {{ !empty($hubUser->firstname) ? 'disabled' : '' }}>
                    </div>

                    <div class="hub-profile-field">
                        <label class="hub-profile-label">@lang('Sobrenome')</label>
                        <input class="hub-profile-input" type="text" name="lastname" value="{{ $hubUser->lastname ?? '' }}" {{ !empty($hubUser->lastname) ? 'disabled' : '' }}>
                    </div>

                    <div class="hub-profile-field">
                        <label class="hub-profile-label">@lang('Username')</label>

                        <div class="hub-username-wrap">
                            <input class="hub-profile-input" type="text" name="username" value="{{ $hubUser->username ?? '' }}">
                            <input class="hub-profile-input" type="text" name="username_confirmation" placeholder="@lang('Confirmar username')">
                        </div>
                    </div>

                    <div class="hub-profile-field">
                        <label class="hub-profile-label">@lang('Email')</label>
                        <input class="hub-profile-input" type="email" name="email" value="{{ $hubUser && method_exists($hubUser, 'hasRealEmail') && $hubUser->hasRealEmail() ? $hubUser->email : '' }}">
                    </div>

                    <div class="hub-profile-field">
                        <label class="hub-profile-label">@lang('WhatsApp (mobile)')</label>
                        <input class="hub-profile-input" type="text" name="mobile" value="{{ $hubUser->mobile ?? '' }}" inputmode="numeric" {{ !empty($hubUser->mobile) ? 'disabled' : '' }}>
                    </div>

                    <div class="hub-profile-field">
                        <label class="hub-profile-label">@lang('CPF')</label>
                        <input class="hub-profile-input" type="text" name="cpf" id="hubProfileCpf" value="{{ $hubUser->cpf ?? '' }}" inputmode="numeric" placeholder="000.000.000-00" maxlength="14" {{ !empty($hubUser->cpf) ? 'disabled' : '' }}>
                    </div>

                    <div class="hub-profile-field">
                        <label class="hub-profile-label">@lang('Data de nascimento')</label>
                        <input class="hub-profile-input" type="text" name="birthdate" id="hubProfileBirthdate" value="{{ $hubUser->birthdate ? \Carbon\Carbon::parse($hubUser->birthdate)->format('d/m/Y') : '' }}" inputmode="numeric" placeholder="DD/MM/AAAA" maxlength="10" {{ !empty($hubUser->birthdate) ? 'disabled' : '' }}>
                    </div>

                    <div class="hub-profile-field hub-profile-field--full">
                        <label class="hub-profile-label">@lang('Privacidade no ranking')</label>
                        <input type="hidden" name="show_in_listings" value="0">
                        <label class="hub-profile-toggle" for="hubProfileShowInListings">
                            <input
                                type="checkbox"
                                id="hubProfileShowInListings"
                                name="show_in_listings"
                                value="1"
                                {{ (bool) ($hubUser->show_in_listings ?? true) ? 'checked' : '' }}
                            >
                            <span class="hub-profile-toggle__switch" aria-hidden="true"></span>
                            <span class="hub-profile-toggle__copy">
                                <strong>Exibir meu username nos rankings</strong>
                                <small>Desative para aparecer mascarado nas listas e rankings públicos.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="hub-profile-actions">
                    <button type="button" class="hub-profile-btn hub-profile-btn--ghost" data-close-profile>@lang('Fechar')</button>
                    <button type="submit" class="hub-profile-btn hub-profile-btn--primary" id="hubProfileSubmit">@lang('Salvar')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth

<!-- ============================================
     MODAL: TERMOS DE USO AFILIADOS
     ============================================ -->
<div id="affiliateTermsModal" class="rr-modal-overlay" style="display: none;">
    <div class="rr-modal-container">
        <div class="rr-modal-header">
            <h3 class="rr-modal-title">
                <i class="fas fa-file-contract"></i>
                Termos e Condições do Programa de Afiliados
            </h3>
            <button type="button" class="rr-modal-close" onclick="closeAffiliateTermsModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="rr-modal-body">
            <div class="rr-terms-content">
                <h4>1. DEFINIÇÕES E ESCOPO</h4>
                <p>O presente instrumento estabelece os termos e condições aplicáveis ao Programa de Afiliados da plataforma <strong>REI DO RODEIO</strong>, destinado a usuários cadastrados que desejam promover a plataforma mediante o compartilhamento de links de indicação, recebendo comissões sobre a atividade de seus indicados.</p>

                <h4>2. ADESÃO AO PROGRAMA</h4>
                <p>2.1. A participação no Programa de Afiliados é voluntária e gratuita.</p>
                <p>2.2. Ao ativar sua conta de afiliado, você declara estar ciente e concordar integralmente com todos os termos aqui estabelecidos.</p>
                <p>2.3. A plataforma reserva-se o direito de suspender ou cancelar contas de afiliados que violem os presentes termos.</p>

                <h4>3. SISTEMA DE COMISSÕES</h4>
                <p>3.1. As comissões serão calculadas conforme o nível (tier) do afiliado:</p>
                <ul>
                    <li><strong>🤠 Iniciante (0-9 indicações):</strong> 20% sobre taxa X1 | 5% sobre prêmios Bolão</li>
                    <li><strong>🏆 Promotor (10-49 indicações):</strong> 25% sobre taxa X1 | 7% sobre prêmios Bolão</li>
                    <li><strong>👑 Embaixador (50-199 indicações):</strong> 30% sobre taxa X1 | 8% sobre prêmios Bolão</li>
                    <li><strong>💎 Lenda (200+ indicações):</strong> 35% sobre taxa X1 | 10% sobre prêmios Bolão</li>
                </ul>

                <p>3.2. <strong>Salas X1:</strong> Em salas onde ambos os jogadores possuem afiliados diferentes, a comissão será dividida 50/50 entre os respectivos afiliados.</p>

                <p>3.3. <strong>Período de Aprovação:</strong> Todas as comissões passam por um período de análise de 7 (sete) dias corridos antes de serem aprovadas para pagamento, visando garantir a legitimidade das transações.</p>

                <h4>4. POLÍTICA DE PAGAMENTOS</h4>
                <p>4.1. Os pagamentos das comissões aprovadas serão realizados <strong>três vezes por semana</strong>:</p>
                <ul>
                    <li><strong>Segundas-feiras, Quartas-feiras e Sextas-feiras</strong></li>
                    <li><strong>Horário:</strong> Entre 08:00h e 17:00h (horário de Brasília)</li>
                </ul>

                <p>4.2. O pagamento será efetuado mediante análise administrativa, desde que:</p>
                <ul>
                    <li>As comissões estejam no status "aprovada";</li>
                    <li>O afiliado possua chave PIX cadastrada e validada;</li>
                    <li>O valor mínimo para saque seja atingido (conforme política vigente).</li>
                </ul>

                <p>4.3. Após a confirmação do pagamento pela administração, o saldo de "Comissão Pendente" será zerado no sistema.</p>

                <h4>5. OBRIGAÇÕES DO AFILIADO</h4>
                <p>5.1. Promover a plataforma de forma ética e verdadeira, sem fazer promessas falsas ou enganosas.</p>
                <p>5.2. Não utilizar métodos fraudulentos, spam, ou práticas que violem a legislação vigente.</p>
                <p>5.3. Não se passar pela plataforma ou criar confusão sobre sua relação com o REI DO RODEIO.</p>
                <p>5.4. Manter seus dados cadastrais atualizados, especialmente a chave PIX para recebimento de pagamentos.</p>

                <h4>6. VEDAÇÕES</h4>
                <p>É expressamente proibido:</p>
                <ul>
                    <li>Criar múltiplas contas para autoindicação;</li>
                    <li>Utilizar bots, scripts ou ferramentas automatizadas para gerar indicações falsas;</li>
                    <li>Promover a plataforma através de conteúdos ofensivos, difamatórios ou ilegais;</li>
                    <li>Induzir usuários ao erro sobre as funcionalidades, riscos ou natureza da plataforma.</li>
                </ul>

                <h4>7. SUSPENSÃO E CANCELAMENTO</h4>
                <p>7.1. A administração poderá suspender ou cancelar a conta de afiliado a qualquer momento, sem aviso prévio, em caso de violação destes termos.</p>
                <p>7.2. Em caso de fraude comprovada, além do cancelamento da conta, as comissões pendentes serão retidas e medidas legais cabíveis poderão ser adotadas.</p>

                <h4>8. ALTERAÇÕES DOS TERMOS</h4>
                <p>8.1. A plataforma reserva-se o direito de alterar estes termos a qualquer momento, mediante notificação aos afiliados através do sistema.</p>
                <p>8.2. A continuidade na utilização do programa após as alterações implica em aceitação tácita dos novos termos.</p>

                <h4>9. DISPOSIÇÕES GERAIS</h4>
                <p>9.1. O presente programa não configura relação de emprego, sociedade ou parceria entre o afiliado e a plataforma.</p>
                <p>9.2. Dúvidas ou questões relacionadas ao programa devem ser direcionadas através dos canais oficiais de suporte.</p>

                <div class="rr-terms-acceptance">
                    <p style="text-align: center; margin-top: 24px; color: #10b981; font-weight: 600;">
                        <i class="fas fa-check-circle"></i>
                        Ao clicar em "Aceitar e Ativar", você declara ter lido, compreendido e aceito integralmente os termos acima.
                    </p>
                </div>
            </div>
        </div>

        <div class="rr-modal-footer">
            <button type="button" class="rr-perfil-btn rr-perfil-btn--secondary" onclick="closeAffiliateTermsModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" id="btnAcceptTerms" class="rr-perfil-btn rr-perfil-btn--primary">
                <i class="fas fa-check"></i> Aceitar e Ativar
            </button>
        </div>
    </div>
</div>

<script>
// ============================================
// MODAL DE TERMOS - FUNÇÕES
// ============================================

function openAffiliateTermsModal() {
    const modal = document.getElementById('affiliateTermsModal');
    if (!modal) {
        console.error('Modal não encontrado!');
        return;
    }

    console.log('🚀 Abrindo modal de termos...');

    // Criar backdrop adicional de segurança
    let backdrop = document.getElementById('modal-safety-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'modal-safety-backdrop';
        backdrop.style.cssText = `
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(0, 0, 0, 0.95) !important;
            z-index: 2147483646 !important;
            pointer-events: none !important;
        `;
        document.body.appendChild(backdrop);
        console.log('🛡️ Backdrop de segurança criado!');
    }
    backdrop.style.display = 'block';

    // Adicionar classe ao body para esconder outros elementos
    document.body.classList.add('modal-open');

    // Adicionar classe ao modal
    modal.classList.add('modal-active');

    // Exibir modal
    modal.style.display = 'flex';

    // Bloquear scroll
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
    document.body.style.top = '0';

    console.log('✅ Modal aberto!');
}

function closeAffiliateTermsModal() {
    const modal = document.getElementById('affiliateTermsModal');
    if (!modal) return;

    console.log('🚪 Fechando modal...');

    // Remover backdrop de segurança
    const backdrop = document.getElementById('modal-safety-backdrop');
    if (backdrop) {
        backdrop.style.display = 'none';
        console.log('🛡️ Backdrop removido!');
    }

    // Remover classes
    document.body.classList.remove('modal-open');
    modal.classList.remove('modal-active');

    // Esconder modal
    modal.style.display = 'none';

    // Restaurar scroll
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
    document.body.style.top = '';

    console.log('✅ Modal fechado!');
}

// Ativar afiliado quando clicar em "Aceitar e Ativar"
document.addEventListener('DOMContentLoaded', function() {
    const btnAccept = document.getElementById('btnAcceptTerms');
    if (btnAccept) {
        btnAccept.addEventListener('click', async function() {
            const btn = this;
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ativando...';

            try {
                const response = await fetch('{{ route("user.affiliate.activate.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error('Erro HTTP: ' + response.status);
                }

                const data = await response.json();

                if (data.success) {
                    alert('✅ ' + data.message);
                    closeAffiliateTermsModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('❌ ' + (data.message || 'Erro ao ativar afiliado'));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Erro ao ativar conta de afiliado. Verifique o console.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
        console.log('✅ Event listener do botão Aceitar registrado!');
    } else {
        console.error('❌ Botão btnAcceptTerms não encontrado!');
    }
});
</script>
@endsection

@push('style')
<link rel="stylesheet" href="{{ asset('css/hub/fantasy.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/competitor-modal-refactored.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/premium-cards.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/inicial-stats.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/inicial-fantasy.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/shield-cards.css') }}?v={{ time() }}">
<style>
:root {
    --hub-primary: #f59e0be6;
    --hub-primary-dark: #d97706;
    --hub-muted: #94a3b8;
    --hub-fire-1: #020202;
    --hub-fire-2: #060606;
    --hub-fire-3: #0a0a0a;
    --hub-fire-ember: #f59e0be6;
    --hub-fire-glow: #f59e0be6;
    --hub-navbar-height: 96px;
}

/* IMPORTANT: main.css sets overflow-x:hidden on main and its children.
   When only one axis is hidden, browsers may compute the other axis as auto,
   which clips box-shadows (seen on the video player in mobile).
   Override to visible for the hub page so glows/shadows aren't cut.
   BUT on mobile, we MUST hide overflow-x to prevent horizontal scroll. */
html {
    overflow-x: hidden;
}

body {
    overflow-x: hidden;
}

/* Ensure content layers sit above the particles canvas */
.hub-hero.hub-top,
section.hub-shell,
.hub-profile-popout {
    position: relative;
    z-index: 2;
}

/* Disable global layout particles on hub: use one unified canvas only */
body.hub-page #rrParticlesCanvas {
    display: none !important;
}

/* Unified fire canvas (full-page, bottom -> top) */
.hub-unified-fire-canvas {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
    opacity: 0.74;
    filter: drop-shadow(0 0 10px rgba(245, 158, 11, 0.22));
}

/* Keep all legacy particle canvases disabled */
.rr-header-particles,
.rr-hero-particles,
.rr-footer-particles {
    display: none !important;
}

.hub-hero.hub-top .hub-top__grid {
    position: relative;
    z-index: 1;
}

/* Main hub blocks transparent for one shared background plane */
body.hub-page .hub-hero.hub-top,
body.hub-page .rr-footer,
body.hub-page .rr-footer-pro {
    background: transparent !important;
    background-image: none !important;
}

body.hub-page .hub-navbar-wrapper {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
        radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.08), transparent 40%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(2, 6, 23, 0.96)) !important;
    backdrop-filter: blur(12px) saturate(1.22) !important;
    -webkit-backdrop-filter: blur(12px) saturate(1.22) !important;
}

body.hub-page .hub-navbar-wrapper.is-scrolled {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 36%),
        radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.1), transparent 42%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(2, 6, 23, 0.99)) !important;
    backdrop-filter: blur(18px) saturate(1.45) !important;
    -webkit-backdrop-filter: blur(18px) saturate(1.45) !important;
    box-shadow: 0 8px 26px rgba(2, 6, 23, 0.4), inset 0 -1px 0 rgba(245, 158, 11, 0.12);
}

body.hub-page #hubSection,
body.hub-page #hubSection > * {
    background: transparent !important;
    background-image: none !important;
}

body.hub-page #hubSection > *::before,
body.hub-page #hubSection > *::after {
    background: transparent !important;
    background-image: none !important;
}

@media (max-width: 768px) {
    body:not(.light) .hub-unified-fire-canvas { opacity: 0.82; }
    body.light .hub-unified-fire-canvas { opacity: 0.98; }
}

/* =============================================
   DARK THEME (default) — fogo/brasa
============================================= */
body:not(.light) {
    background-color: var(--hub-fire-1) !important;
    background-image:
        radial-gradient(circle at 20% 12%, rgba(245, 158, 11, 0.08), transparent 38%),
        radial-gradient(circle at 82% 6%, rgba(251, 191, 36, 0.06), transparent 42%),
        linear-gradient(165deg, var(--hub-fire-1) 0%, var(--hub-fire-2) 45%, var(--hub-fire-3) 100%) !important;
    color: #e2e8f0;
}
body:not(.light)::after {
    background: radial-gradient(circle at 35% 18%, rgba(245, 158, 11, 0.08), transparent 62%);
}
body:not(.light) .hub-hero.hub-top {
    background: linear-gradient(180deg, rgba(245, 158, 11, 0.05) 0%, rgba(8, 8, 8, 0.76) 42%, rgba(2, 2, 2, 0.94) 100%);
}

/* LIGHT THEME overrides for hub */
body.light.hub-page {
    background-color: #fff7ed !important;
    background-image:
        radial-gradient(circle at 18% 10%, rgba(251, 146, 60, 0.17), transparent 38%),
        radial-gradient(circle at 86% 8%, rgba(249, 115, 22, 0.13), transparent 40%),
        linear-gradient(165deg, #fffaf5 0%, #ffefdf 48%, #ffe8d2 100%) !important;
    color: #4a2a1a;
}

body.light.hub-page::after {
    background: radial-gradient(circle at 36% 18%, rgba(249, 115, 22, 0.12), transparent 62%);
}

body.light .hub-hero.hub-top {
    background: transparent;
}
body.light .rr-hero-particles,
body.light .rr-header-particles {
    opacity: 0.55;
    filter: brightness(1.1) saturate(1.3);
}
body.light .hub-navbar-wrapper {
    background: linear-gradient(135deg, rgba(255, 249, 242, 0.84) 0%, rgba(255, 240, 222, 0.8) 50%, rgba(255, 249, 242, 0.84) 100%) !important;
    border-bottom-color: rgba(234, 88, 12, 0.24);
}
body.light .hub-navbar-wrapper.is-scrolled {
    background: linear-gradient(135deg, rgba(255, 249, 242, 0.96) 0%, rgba(255, 236, 212, 0.93) 50%, rgba(255, 249, 242, 0.96) 100%) !important;
    box-shadow: 0 8px 24px rgba(234, 88, 12, 0.12), inset 0 -1px 0 rgba(234, 88, 12, 0.08);
}
body.light .hub-navbar-inner,
body.light .hub-navbar-logo__name,
body.light .hub-navbar-link {
    color: #4a2a1a;
}
body.light .hub-navbar-title {
    color: #4a2a1a;
    -webkit-text-fill-color: #4a2a1a;
}
body.light .hub-navbar-welcome__text {
    color: rgba(96, 46, 16, 0.58);
}
body.light .hub-navbar-welcome__user {
    color: #4a2a1a;
}
body.light .hub-navbar-link.is-active {
    color: #fff;
}
body.light .hub-navbar-btn--entrar {
    color: #1b1207;
}
body.light .hub-navbar-btn--download {
    color: #6b2d12;
    border-color: rgba(124, 45, 18, 0.18);
    background: rgba(255, 255, 255, 0.72);
}
body.light .rr-footer-pro {
    background: linear-gradient(to bottom, #fff7ed 0%, #ffe8d2 100%);
    border-top-color: rgba(234, 88, 12, 0.16);
}
body.light .rr-footer-pro,
body.light .rr-footer-pro .rr-brand-font {
    color: #4a2a1a;
}
body.light .rr-footer-pro .rr-brand-font {
    background: linear-gradient(135deg, #9a3412 0%, #c2410c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
body.light .rr-footer-pro__tagline {
    color: #9a6a49;
}
body.light .rr-footer-pro__bottom {
    border-top-color: rgba(234, 88, 12, 0.14);
    color: #b06b3b;
}
body.light .rr-security-badge {
    color: #9a6a49;
    background: rgba(249, 115, 22, 0.07);
    border-color: rgba(249, 115, 22, 0.16);
}

/* Allow visible overflow only on larger screens where it's needed for shadows */
@media (min-width: 1024px) {
    html {
        overflow-x: visible !important;
    }
    body {
        overflow-x: visible !important;
    }
}

/* Status Badges Overlay */
.hub-hero__player {
    position: relative;
}

.hub-status-overlay {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 10;
    display: flex;
    gap: 8px;
    pointer-events: none;
}

/* ============================================
   STATUS BADGES
============================================ */
.badge-live-now {
    background: rgba(220, 38, 38, 0.9);
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 800;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 0 15px rgba(220, 38, 38, 0.4);
}

.dot-pulse {
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    animation: pulse-red 1.5s infinite;
}

@keyframes pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}

.badge-paused-now {
    background: rgba(234, 179, 8, 0.9);
    color: black;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 800;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    box-shadow: 0 0 15px rgba(234, 179, 8, 0.4);
}

.badge-current-modality {
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* On mobile, prevent horizontal scroll */
main.rr-container,
main.rr-container > * {
    overflow-y: visible !important;
}

@media (max-width: 1023px) {
    main.rr-container,
    main.rr-container > * {
        overflow-x: hidden !important;
    }
}

@media (min-width: 1024px) {
    main.rr-container,
    main.rr-container > * {
        overflow: visible !important;
        overflow-x: visible !important;
        overflow-y: visible !important;
    }
}

.hub-hero {
        padding: 0 0 0.5rem;
        display: block;
        position: relative;
        z-index: 2;
        margin-bottom: 0;
    }

/* Hide banners/live/brand hero on tabs that não precisam — #hubTop continua visível pq #hubSection vive lá */
body[data-hub-section]:not([data-hub-section="inicio"]) .hub-top__live-wrapper,
body[data-hub-section]:not([data-hub-section="inicio"]):not([data-hub-section="premium"]) .hub-top__brand,
body[data-hub-section]:not([data-hub-section="inicio"]) .rr-side-panel--right-stack,
body[data-hub-section]:not([data-hub-section="inicio"]) .rr-side-panel--bottom {
    display: none !important;
}

/* Desktop: remove extra gap between tabs and content below */
@media (min-width: 769px) {
    .hub-hero {
        padding-top: 0.2rem;
        padding-bottom: 0.25rem;
        margin-bottom: 0;
    }
}

    .hub-top__grid {
        width: 100%;
        max-width: none;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
        align-items: start;
        padding: 0 12px;
    }

    @if($isBolaoLaunchMode)
    .hub-top__grid {
        width: var(--rr-bolao-launch-width);
        max-width: var(--rr-bolao-launch-width);
        padding-inline: 0;
        gap: 0.75rem;
        margin-left: auto;
        margin-right: auto;
    }
    @endif

    /* Mobile: containers edge-to-edge, sem bordas laterais */
    @media (max-width: 768px) {
        .hub-top__grid {
            padding: 0;
        }

        @if($isBolaoLaunchMode)
        .hub-top__grid {
            width: 100%;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
        @endif
    }

    /* Conteúdo dinâmico (hubSection) dentro do grid */
    .hub-top__main {
        grid-column: 1 / -1;
        min-width: 0;
        width: 100%;
    }

.hub-top__brand {
    width: 100%;
}

.hub-top__brand { grid-area: brand; }

.hub-top__brand {
    display: flex;
    justify-content: center;
    align-items: stretch;
}

.hub-top__brand {
    flex-direction: column;
}

.hub-top__brand {
    gap: 0.9rem;
    padding: 0.25rem 0;
}

.hub-brand-center{
    width: 100%;
    height: 100%;
    min-height: 220px;
    border-radius: 0;
    border: 0;
    background: transparent;
    backdrop-filter: none;
    display: grid;
    place-items: center;
    gap: .65rem;
    padding: .25rem;
    text-align: center;
}

.hub-brand-menu-slot{
    width: 100%;
    display: none;
    justify-content: center;
}

.hub-brand-center__logo{
    width: 140px;
    height: auto;
    filter: drop-shadow(0 14px 26px var(--rr-primary-soft, rgba(249,115,22,0.35)));
}

.hub-brand-center__name{
    font-weight: 400;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: rgba(248,250,252,.95);
    font-size: 1.1rem;
}

/* ========================================
   📺 LIVE + MENU VERTICAL LAYOUT (Desktop)
======================================== */

.hub-top__live-wrapper {
    width: 100%;
}

.hub-top__grid > .hub-shell__nav {
    width: 100%;
    display: flex;
    justify-content: center;
}

.hub-top__banners {
    width: 100%;
    max-width: 100%;
}

/* Mobile: apenas live */
@media (max-width: 767px) {
    .hub-vertical-menu {
        display: none !important;
    }
    
    .hub-top__grid { max-width: 100%; }
}

/* Desktop: grid master — tudo dentro de hub-top__grid */
@media (min-width: 768px) {
    .hub-top__grid {
        grid-template-columns: 1fr;
        width: 100%;
        margin: 0 auto;
        padding: 0.5rem 10px;
        gap: 0;
    }

    @if($isBolaoLaunchMode)
    .hub-top__grid {
        grid-template-columns: minmax(0, 1fr) 154px;
        column-gap: 12px;
        gap: 0.75rem;
        align-items: start;
    }
    @endif

    .hub-top__live-wrapper {
        min-width: 0;
        width: 100%;
    }

    /* Hub content area below live by default */
    .hub-top__main {
        grid-column: 1 / -1;
    }

    @if($isBolaoLaunchMode)
    .hub-top__main {
        grid-column: 1;
    }
    @endif

    /* Hide the old vertical menu/tabbar on desktop — menu is in header now */
    .hub-top__grid > .hub-shell__nav {
        display: none !important;
    }
    
    .hub-top__banners {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }
    
    .hub-vertical-menu {
        display: none !important;
    }
}

.hub-vertical-menu__tab {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 8px;
    color: rgba(203, 213, 225, 0.8);
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.hub-vertical-menu__tab i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

.hub-vertical-menu__tab:hover {
    background: rgba(148, 163, 184, 0.1);
    border-color: rgba(148, 163, 184, 0.2);
    color: #f8fafc;
    transform: translateX(2px);
}

.hub-vertical-menu__tab.active {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.2));
    border-color: rgba(249, 115, 22, 0.3);
    color: #f59e0be6;
}

.hub-vertical-menu__tab.active i {
    color: #f59e0be6;
}

.hub-vertical-menu__tab--premium {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(245, 158, 11, 0.15));
    border-color: rgba(251, 191, 36, 0.3);
    color: #f59e0be6;
}

.hub-vertical-menu__tab--premium:hover {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.15), rgba(245, 158, 11, 0.2));
    border-color: rgba(251, 191, 36, 0.5);
}

.hub-vertical-menu__tab--premium i {
    color: #f59e0be6;
}

/* ========================================
   KTO-STYLE NAVBAR WRAPPER
======================================== */
.hub-navbar-wrapper {
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 120;
    width: 100%;
    background: linear-gradient(135deg, rgba(33, 11, 4, 0.94) 0%, rgba(82, 25, 7, 0.90) 50%, rgba(20, 7, 3, 0.95) 100%);
    backdrop-filter: blur(18px) saturate(1.6);
    -webkit-backdrop-filter: blur(18px) saturate(1.6);
    padding-top: env(safe-area-inset-top, 0px);
    border-bottom: 1px solid rgba(255, 126, 44, 0.32);
    box-shadow: 0 1px 10px rgba(0, 0, 0, 0.32), inset 0 -1px 0 rgba(255, 198, 130, 0.08);
    transition: box-shadow 0.3s, background 0.3s;
}

.hub-navbar-wrapper.is-scrolled {
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.35);
    background: linear-gradient(135deg, rgba(28, 9, 4, 0.97) 0%, rgba(73, 22, 7, 0.94) 50%, rgba(18, 6, 2, 0.97) 100%);
}

body.hub-page {
    --hub-navbar-offset: calc(var(--hub-navbar-height) + 8px);
    padding-top: 0 !important;
}

body.hub-page #hubNavbarSpacer {
    display: block;
    width: 100%;
    height: var(--hub-navbar-height, 96px);
    min-height: var(--hub-navbar-height, 96px);
    flex: 0 0 auto;
    pointer-events: none;
}

body.hub-page main.rr-container {
    margin-top: 0 !important;
    padding-top: 0 !important;
    overflow: visible !important;
}

body.hub-page #hubTop {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

body.hub-page #hubTop,
body.hub-page #hubSection {
    scroll-margin-top: calc(var(--hub-navbar-offset) + 4px);
}

/* Header particles canvas */
.rr-header-particles {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
    opacity: 0.8;
}

/* Footer particle layer */
.rr-footer,
.rr-footer-pro {
    position: relative;
    overflow: hidden;
}

.rr-footer .rr-footer-particles,
.rr-footer-pro .rr-footer-particles {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
    opacity: 0.75;
}

.rr-footer > *:not(.rr-footer-particles),
.rr-footer-pro > *:not(.rr-footer-particles) {
    position: relative;
    z-index: 1;
}

.hub-navbar-inner {
    display: flex;
    position: relative;
    z-index: 1;
    min-height: 95px;
    width: 100%;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.471rem;
    padding: 0.471rem 0.941rem;
    font-size: 0.824rem;
    color: #f8fafc;
}

@media (min-width: 600px) {
    .hub-navbar-inner {
        min-height: 64px;
        height: auto;
        flex-direction: row;
        justify-content: space-between;
        gap: 0;
    }
}

.hub-navbar-content {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    flex: 1;
    width: 100%;
    align-items: center;
    position: relative;
}

@media (min-width: 768px) {
    .hub-navbar-content {
        display: grid;
        grid-template-columns: minmax(260px, auto) minmax(0, 1fr) auto;
        align-items: center;
        column-gap: 1rem;
    }
}

@media (max-width: 599px) {
    .hub-navbar-content {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        gap: 0.45rem;
    }

    .hub-navbar-left {
        width: auto;
        justify-content: flex-start;
        min-width: 0;
    }
}

/* ---- Left: Nav menu items ---- */
.hub-navbar-left {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.hub-navbar-cta-group {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
}

@media (min-width: 768px) {
    .hub-navbar-left {
        margin-left: 0.65rem;
        justify-content: flex-start;
        min-width: 0;
        flex-wrap: wrap;
        gap: 0.85rem;
    }
}

.hub-navbar-title {
    display: none;
}

/* ==========================================
   THEME TOGGLE — SEGMENTED BUTTONS
========================================== */
.rr-tt {
    display: inline-flex;
    align-items: center;
    position: relative;
    width: 150px;
    height: 42px;
    border-radius: 999px;
    border: 1px solid rgba(255, 168, 112, 0.34);
    background:
        radial-gradient(circle at 24% 20%, rgba(255, 187, 133, 0.2), transparent 50%),
        linear-gradient(180deg, rgba(32, 11, 4, 0.88), rgba(20, 7, 3, 0.92));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.16),
        0 10px 22px rgba(0, 0, 0, 0.34);
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    overflow: hidden;
    flex-shrink: 0;
    padding: 3px;
    gap: 3px;
    --tt-indicator-x: 3px;
    --tt-indicator-w: calc(50% - 4.5px);
}

.rr-tt-indicator {
    position: absolute;
    top: 3px;
    left: 0;
    height: calc(100% - 6px);
    width: var(--tt-indicator-w);
    transform: translateX(var(--tt-indicator-x));
    border-radius: 999px;
    pointer-events: none;
    background:
        linear-gradient(150deg, #fdba74 0%, #f59e0be6 42%, #f59e0be6 100%);
    box-shadow:
        0 6px 14px rgba(249, 115, 22, 0.38),
        inset 0 1px 0 rgba(255, 255, 255, 0.42),
        inset 0 -2px 0 rgba(154, 52, 18, 0.38);
    transition: transform 0.32s cubic-bezier(0.2, 0.8, 0.2, 1), width 0.25s ease;
    z-index: 0;
}

.rr-tt-btn {
    flex: 1 1 0;
    min-width: 0;
    position: relative;
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    color: rgba(248, 250, 252, 0.8);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    padding: 0;
    margin: 0;
    font-size: 11px;
    font-weight: 800;
    line-height: 1;
    opacity: 1;
    cursor: pointer;
    z-index: 1;
    transition: color 0.22s ease, transform 0.22s ease, filter 0.22s ease;
}

.rr-tt-btn::before {
    content: '';
    position: absolute;
    inset: 1px;
    border-radius: 999px;
    border: 0;
    opacity: 0;
    transition: none;
    display: none;
}

.rr-tt-btn.is-active {
    color: #1b1207;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.35);
}

.rr-tt-btn:not(.is-active) {
    color: rgba(248, 250, 252, 0.78);
}

.rr-tt-btn:hover:not(.is-active) {
    color: #fff;
    transform: translateY(-1px);
}

.rr-tt-btn.is-pressed {
    transform: translateY(1px) scale(0.985);
    filter: brightness(0.98);
}

.rr-tt-btn.is-active::before {
    opacity: 1;
}

.rr-tt-btn__icon {
    display: inline-block;
    font-size: 11px;
    opacity: 0.92;
}

.rr-tt-btn__label {
    display: block;
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    max-width: none;
}

.rr-tt-ripple {
    position: absolute;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    pointer-events: none;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.65), rgba(255, 255, 255, 0));
    transform: translate(-50%, -50%) scale(0.2);
    animation: rrThemeRipple 0.5s ease-out forwards;
}

.rr-tt-btn:focus-visible {
    outline: 1px solid rgba(249, 115, 22, 0.45);
    outline-offset: -1px;
}

@keyframes rrThemeRipple {
    0% { transform: translate(-50%, -50%) scale(0.2); opacity: 0.75; }
    100% { transform: translate(-50%, -50%) scale(4.6); opacity: 0; }
}

body.light .rr-tt {
    border-color: rgba(124, 45, 18, 0.24);
    background:
        radial-gradient(circle at 24% 20%, rgba(255, 173, 112, 0.24), transparent 50%),
        linear-gradient(180deg, rgba(255, 248, 239, 0.96), rgba(255, 235, 217, 0.97));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.72),
        0 10px 20px rgba(124, 45, 18, 0.16);
}

body.light .rr-tt-btn {
    color: rgba(74, 42, 26, 0.8);
}

body.light .rr-tt-btn:not(.is-active) {
    color: rgba(74, 42, 26, 0.82);
}

body.light .rr-tt-btn:hover:not(.is-active) {
    color: #4a2a1a;
}

body.light .rr-tt-btn.is-active {
    color: #2d1408;
}

body.light .rr-tt-indicator {
    background: linear-gradient(155deg, #fed7aa 0%, #f59e0be6 52%, #ea580c 100%);
    box-shadow:
        0 6px 13px rgba(194, 65, 12, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.65),
        inset 0 -2px 0 rgba(154, 52, 18, 0.25);
}

/* Header positioning - desktop */
.hub-navbar-actions {
    display: none;
}

.hub-app-return-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.42rem;
    min-height: 36px;
    padding: 0.52rem 0.95rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 198, 130, 0.30);
    background:
        linear-gradient(180deg, rgba(54, 17, 6, 0.96), rgba(25, 8, 3, 0.96));
    color: #fff3e8;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.12),
        0 12px 22px rgba(0, 0, 0, 0.34),
        0 0 0 1px rgba(249, 115, 22, 0.08);
    cursor: pointer;
    transition: transform 0.16s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    z-index: 8;
    white-space: nowrap;
}

.hub-app-return-btn i {
    color: #f59e0be6;
    font-size: 0.82rem;
}

.hub-app-return-btn:hover,
.hub-app-return-btn:focus-visible {
    transform: translateY(-1px);
    border-color: rgba(249, 115, 22, 0.56);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.15),
        0 14px 26px rgba(0, 0, 0, 0.36),
        0 0 24px rgba(249, 115, 22, 0.16);
}

.hub-app-return-btn:focus-visible {
    outline: 2px solid rgba(249, 115, 22, 0.5);
    outline-offset: 2px;
}

.hub-app-return-btn--mobile {
    display: inline-flex;
    align-self: stretch;
    margin-top: 0.15rem;
}

.hub-app-return-btn--desktop {
    display: none;
}

.rr-tt--header {
    display: inline-flex;
}
@media (min-width: 600px) {
    .hub-navbar-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        justify-self: end;
        margin-left: 0;
        z-index: 3;
    }

    .rr-tt--header {
        display: inline-flex;
    }

    .hub-app-return-btn--desktop {
        display: inline-flex;
    }

    .hub-app-return-btn--mobile {
        display: none;
    }
}

@media (max-width: 599px) {
    .hub-navbar-inner {
        min-height: 62px;
        gap: 0.2rem;
        padding: 0.35rem 0.7rem;
    }

    .hub-navbar-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        flex-shrink: 0;
    }

    .hub-navbar-logo {
        gap: 0.38rem;
    }

    .hub-navbar-wrapper--site .hub-navbar-logo__img {
        display: none;
    }

    .hub-navbar-logo__img {
        width: 42px;
        height: 42px;
        border-radius: 8px;
    }

    .hub-navbar-logo__name {
        font-size: 1rem;
        letter-spacing: 0.04em;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .hub-navbar-wrapper--site .hub-navbar-logo {
        gap: 0;
        margin-left: 0.48rem;
    }

    .hub-navbar-wrapper--site .hub-navbar-left {
        min-width: 0;
        flex: 1 1 auto;
    }

    .hub-navbar-wrapper--site .hub-navbar-logo__name {
        display: block;
        font-size: clamp(0.94rem, 4vw, 1.12rem);
        letter-spacing: 0.04em;
        max-width: min(188px, calc(100vw - 164px));
        white-space: normal;
        line-height: 1.08;
        overflow: visible;
        text-overflow: clip;
        text-wrap: balance;
    }

    .hub-navbar-mobile-wallet {
        display: inline-flex;
        max-width: min(176px, calc(100vw - 150px));
    }

    .hub-navbar-logo--wallet .hub-navbar-logo__name {
        display: none;
    }

    .hub-help-btn {
        height: 32px;
        padding: 0 0.72rem;
        font-size: 0.68rem;
        box-shadow:
            0 4px 0 #166534,
            0 8px 14px rgba(6, 78, 59, 0.28),
            inset 0 1px 0 rgba(255, 255, 255, 0.28);
    }

    .rr-tt--header {
        width: 74px;
        height: 32px;
        padding: 2px;
        gap: 2px;
        --tt-indicator-x: 2px;
        --tt-indicator-w: calc(50% - 3px);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.12),
            0 6px 12px rgba(0, 0, 0, 0.22);
    }

    .rr-tt--header .rr-tt-btn__label {
        display: none;
    }

    .rr-tt--header .rr-tt-btn__icon {
        font-size: 10px;
        margin: 0;
    }

    .hub-navbar-mobile-auth,
    .rr-tt--mobile,
    .hub-mobile-help-bubble,
    .hub-mobile-welcome {
        display: none !important;
    }
}

.hub-help-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    height: 32px;
    padding: 0 0.52rem;
    border-radius: 999px;
    border: 1px solid rgba(167, 243, 208, 0.28);
    background: linear-gradient(155deg, #34d399 0%, #22c55e 45%, #16a34a 100%);
    color: #04210f;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: transform 0.16s ease, box-shadow 0.22s ease, filter 0.22s ease;
    white-space: nowrap;
    box-shadow:
        0 4px 0 #166534,
        0 8px 14px rgba(6, 78, 59, 0.32),
        inset 0 1px 0 rgba(255, 255, 255, 0.34);
}

.hub-help-btn::before {
    content: '';
    position: absolute;
    inset: 2px 2px auto 2px;
    height: 45%;
    border-radius: 999px;
    background: linear-gradient(to bottom, rgba(255, 255, 255, 0.32), rgba(255, 255, 255, 0));
    pointer-events: none;
}

.hub-help-btn i {
    font-size: 0.84rem;
}

.hub-help-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.04);
    box-shadow:
        0 5px 0 #166534,
        0 10px 16px rgba(6, 78, 59, 0.36),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
}

.hub-help-btn:active {
    transform: translateY(5px);
    box-shadow:
        0 2px 0 #166534,
        0 8px 14px rgba(6, 78, 59, 0.34),
        inset 0 1px 0 rgba(255, 255, 255, 0.28);
}

body.light .hub-help-btn {
    border-color: rgba(22, 163, 74, 0.4);
    background: linear-gradient(155deg, #6ee7b7 0%, #34d399 42%, #16a34a 100%);
    color: #052312;
    box-shadow:
        0 7px 0 #15803d,
        0 14px 24px rgba(22, 101, 52, 0.32),
        inset 0 1px 0 rgba(255, 255, 255, 0.55);
}

body.light .hub-help-btn:hover {
    box-shadow:
        0 9px 0 #15803d,
        0 18px 28px rgba(22, 101, 52, 0.38),
        inset 0 1px 0 rgba(255, 255, 255, 0.62);
}

/* Mobile toggle - below Entrar button */
.rr-tt--mobile {
    display: inline-flex;
    margin: 8px auto 6px;
    width: min(220px, calc(100vw - 32px));
    height: 44px;
}

.rr-tt--mobile .rr-tt-btn {
    font-size: 12px;
    gap: 0.35rem;
}

.rr-tt--mobile .rr-tt-btn__icon {
    display: inline-block;
}

.rr-tt--mobile .rr-tt-btn__label {
    max-width: none;
    font-size: 12px;
    letter-spacing: 0.02em;
}

@media (prefers-reduced-motion: reduce) {
    .rr-tt-indicator,
    .rr-tt-btn,
    .rr-tt-ripple,
    .hub-mobile-help-bubble__hint,
    .hub-mobile-help-bubble.is-nudging svg {
        animation: none !important;
        transition: none !important;
    }
}
@media (min-width: 600px) {
    .rr-tt--mobile {
        display: none;
    }
}

.hub-mobile-help-bubble {
    position: absolute;
    left: 10px;
    bottom: 8px;
    width: 34px !important;
    min-width: 34px !important;
    max-width: 34px !important;
    height: 34px !important;
    min-height: 34px !important;
    max-height: 34px !important;
    padding: 0 !important;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9989;
    color: #032111;
    background: linear-gradient(155deg, #6ee7b7 0%, #22c55e 45%, #15803d 100%);
    box-shadow:
        0 8px 0 #14532d,
        0 14px 24px rgba(6, 78, 59, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.45);
    cursor: pointer;
    transition: transform 0.16s ease, box-shadow 0.2s ease;
    overflow: visible;
}

.hub-mobile-help-bubble i {
    font-size: 14px;
}

.hub-mobile-help-bubble svg {
    width: 14px;
    height: 14px;
}

.hub-mobile-help-bubble__hint {
    position: absolute;
    left: calc(100% + 8px);
    top: 50%;
    transform: translateY(-50%) scaleX(0.65);
    transform-origin: left center;
    opacity: 0;
    pointer-events: none;
    white-space: nowrap;
    padding: 6px 10px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    background: linear-gradient(155deg, #6ee7b7 0%, #22c55e 45%, #15803d 100%);
    color: #032111;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    box-shadow:
        0 5px 0 #14532d,
        0 10px 16px rgba(6, 78, 59, 0.38),
        inset 0 1px 0 rgba(255, 255, 255, 0.42);
    transition: opacity 0.25s ease, transform 0.34s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.hub-mobile-help-bubble.is-nudging svg {
    animation: hubHelpBubblePulse 0.45s ease;
}

.hub-mobile-help-bubble.is-nudging .hub-mobile-help-bubble__hint {
    opacity: 1;
    transform: translateY(-50%) scaleX(1);
}

@keyframes hubHelpBubblePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.12); }
    100% { transform: scale(1); }
}

.hub-mobile-help-bubble:active {
    transform: translateY(5px);
    box-shadow:
        0 2px 0 #14532d,
        0 8px 14px rgba(6, 78, 59, 0.32),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

@media (max-width: 599px) {
    .hub-mobile-help-bubble {
        display: inline-flex;
    }
}

@media (min-width: 600px) {
    .hub-mobile-help-bubble {
        display: none !important;
    }
}

/* Mobile welcome (logged in) */
.hub-mobile-welcome {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.15rem;
    padding: 0.25rem 0 0.1rem;
    line-height: 1.3;
}

.hub-mobile-welcome__title {
    font-size: 1rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(248, 250, 252, 0.95);
    white-space: nowrap;
}

.hub-mobile-welcome__greeting {
    font-size: 0.75rem;
    color: rgba(248, 250, 252, 0.55);
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.hub-mobile-welcome__user {
    font-size: 0.95rem;
    color: #f59e0be6;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    max-width: 80vw;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hub-mobile-welcome__crown {
    color: #f59e0be6;
    font-size: 0.85rem;
    animation: welcomeCrownGlow 2s ease-in-out infinite;
}

body.light .hub-mobile-welcome__title {
    color: #0f172a;
    -webkit-text-fill-color: #0f172a;
}

body.light .hub-mobile-welcome__greeting {
    color: rgba(15, 23, 42, 0.5);
}

body.light .hub-mobile-welcome__user {
    color: #ea580c;
}

@media (min-width: 600px) {
    .hub-mobile-welcome {
        display: none;
    }
}

/* Help popout/tutorial */
.hub-help-popout {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: none;
}

.hub-help-popout.is-open {
    display: block;
}

.hub-help-popout__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.72);
    backdrop-filter: blur(4px);
}

.hub-help-popout__panel {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: min(560px, calc(100% - 24px));
    max-height: calc(100% - 24px);
    overflow: auto;
    border-radius: 18px;
    border: 1px solid rgba(255, 133, 56, 0.28);
    background: linear-gradient(180deg, rgba(36, 12, 4, 0.97), rgba(20, 7, 3, 0.98));
    box-shadow: 0 32px 90px rgba(0, 0, 0, 0.55);
}

.hub-help-popout__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1rem 0.85rem;
    border-bottom: 1px solid rgba(255, 133, 56, 0.2);
}

.hub-help-popout__title {
    font-size: 1.18rem;
    font-weight: 800;
    color: #fff7ed;
    line-height: 1.2;
}

.hub-help-popout__subtitle {
    margin-top: 0.2rem;
    font-size: 0.84rem;
    color: rgba(255, 214, 188, 0.85);
}

.hub-help-popout__close {
    border: 0;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
}

.hub-help-popout__body {
    padding: 1rem;
}

.hub-help-view { display: none; }
.hub-help-view.is-active { display: block; }

.hub-help-choice-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.hub-help-choice {
    min-height: 88px;
    border-radius: 14px;
    border: 1px solid rgba(255, 133, 56, 0.26);
    background: linear-gradient(160deg, rgba(57, 20, 9, 0.92), rgba(28, 10, 5, 0.96));
    color: #fff7ed;
    display: grid;
    place-items: center;
    gap: 0.32rem;
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: transform 0.16s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.hub-help-choice i {
    font-size: 1.1rem;
    color: #fdba74;
}

.hub-help-choice:hover {
    transform: translateY(-2px);
    border-color: rgba(251, 146, 60, 0.5);
    box-shadow: 0 10px 18px rgba(0, 0, 0, 0.35);
}

.hub-help-topics {
    display: grid;
    gap: 0.55rem;
}

.hub-help-topic-btn,
.hub-help-back-btn {
    border: 1px solid rgba(255, 133, 56, 0.25);
    border-radius: 11px;
    min-height: 40px;
    padding: 0 0.95rem;
    color: #fff7ed;
    background: linear-gradient(145deg, rgba(46, 17, 8, 0.9), rgba(24, 9, 4, 0.95));
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    text-decoration: none;
}

.hub-help-topic-btn:hover,
.hub-help-back-btn:hover {
    border-color: rgba(251, 146, 60, 0.5);
}

.hub-help-inline-actions {
    margin-top: 0.8rem;
    display: flex;
    gap: 0.55rem;
    flex-wrap: wrap;
}

.hub-help-video-title {
    color: #fff7ed;
    font-size: 0.96rem;
    font-weight: 800;
    margin-bottom: 0.55rem;
}

.hub-help-video-wrap {
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(255, 133, 56, 0.28);
    background: #000;
    aspect-ratio: 16 / 9;
}

.hub-help-video-wrap iframe {
    width: 100%;
    height: 100%;
    border: 0;
}

@media (max-width: 520px) {
    .hub-help-choice-grid {
        grid-template-columns: 1fr;
    }
}

body.light .hub-help-popout__panel {
    border-color: rgba(234, 88, 12, 0.25);
    background: linear-gradient(180deg, rgba(255, 249, 242, 0.98), rgba(255, 237, 221, 0.98));
}

body.light .hub-help-popout__header {
    border-bottom-color: rgba(234, 88, 12, 0.18);
}

body.light .hub-help-popout__title {
    color: #7c2d12;
}

body.light .hub-help-popout__subtitle {
    color: rgba(124, 45, 18, 0.8);
}

body.light .hub-help-popout__close {
    color: #7c2d12;
    background: rgba(124, 45, 18, 0.08);
}

body.light .hub-help-choice {
    border-color: rgba(234, 88, 12, 0.24);
    background: linear-gradient(160deg, rgba(255, 249, 241, 0.95), rgba(255, 238, 220, 0.98));
    color: #7c2d12;
}

body.light .hub-help-choice i {
    color: #ea580c;
}

body.light .hub-help-topic-btn,
body.light .hub-help-back-btn {
    border-color: rgba(234, 88, 12, 0.24);
    background: linear-gradient(150deg, rgba(255, 249, 241, 0.96), rgba(255, 239, 224, 0.98));
    color: #7c2d12;
}

body.light .hub-help-video-title {
    color: #7c2d12;
}

@keyframes welcomeCrownGlow {
    0%, 100% {
        filter: drop-shadow(0 0 3px rgba(251, 191, 36, 0.4));
        transform: scale(1);
    }
    50% {
        filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.8));
        transform: scale(1.1);
    }
}

/* ---- Desktop horizontal header nav ---- */
.hub-header-nav {
    display: none;
}

@media (min-width: 768px) {
    .hub-header-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        justify-self: center;
        gap: 2px;
        width: 100%;
        min-width: 0;
    }

    .hub-header-nav__btn--orange {
        --hub-header-btn-bg: #f59e0be6;
        --hub-header-btn-bg-hover: rgba(234, 179, 8, 0.16);
        --hub-header-btn-shadow: rgba(202, 138, 4, 0.26);
    }

    .hub-header-nav__btn--green {
        --hub-header-btn-bg: #2563eb;
        --hub-header-btn-bg-hover: rgba(37, 99, 235, 0.16);
        --hub-header-btn-shadow: rgba(37, 99, 235, 0.24);
    }

    .hub-header-nav__btn--blue {
        --hub-header-btn-bg: #16a34a;
        --hub-header-btn-bg-hover: rgba(22, 163, 74, 0.16);
        --hub-header-btn-shadow: rgba(22, 163, 74, 0.24);
    }

    .hub-header-nav__btn--store {
        --hub-header-btn-bg: #f59e0be6;
        --hub-header-btn-bg-hover: rgba(245, 158, 11, 0.16);
        --hub-header-btn-shadow: rgba(217, 119, 6, 0.24);
    }

    .hub-header-nav__btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.45rem 1rem;
        font-size: 0.88rem;
        font-weight: 600;
        text-transform: capitalize;
        position: relative;
        transition: color 0.2s, background 0.2s;
        white-space: nowrap;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        color: #fff;
    }

    .hub-header-nav__btn i {
        font-size: 0.92rem;
        color: #fff;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .hub-header-nav__btn:hover {
        background: var(--hub-header-btn-bg-hover, rgba(248, 250, 252, 0.12));
        color: #fff;
        transform: translateY(-1px);
    }
    .hub-header-nav__btn:hover i {
        transform: translateY(-2px);
    }

    /* Tema claro: menu laranja */
    body.light .hub-header-nav__btn,
    body.light .hub-header-nav__btn i {
        color: var(--hub-primary);
    }
    body.light .hub-header-nav__btn:hover {
        background: var(--hub-header-btn-bg-hover, rgba(245, 158, 11, 0.12));
        color: var(--hub-primary-dark);
    }
    body.light .hub-header-nav__btn:hover i {
        color: var(--hub-primary-dark);
    }

    /* Tema escuro: texto branco + icones laranja (quando nao ativo) */
    body:not(.light) .hub-header-nav__btn {
        color: #fff;
    }
    body:not(.light) .hub-header-nav__btn i {
        color: var(--hub-primary);
    }

    /* Active unificado em laranja */
    .hub-header-nav__btn.active {
        background: var(--hub-header-btn-bg, var(--hub-primary));
        color: #fff;
        box-shadow: 0 10px 20px var(--hub-header-btn-shadow, rgba(245, 158, 11, 0.24));
    }

    .hub-header-nav__btn.active i {
        color: #fff;
    }

    /* Tema escuro: ícone do menu selecionado em branco */
    body:not(.light) .hub-header-nav__btn.active i {
        color: #fff !important;
    }

    /* Tema claro: item selecionado no header com texto branco */
    body.light .hub-header-nav__btn.active,
    body.light .hub-header-nav__btn.active i {
        color: #fff !important;
    }

}

.hub-navbar-menu-items {
    display: none;
}

@media (min-width: 768px) {
    .hub-navbar-menu-items {
        display: flex;
        gap: 0.25rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
}

.hub-navbar-tab {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.4rem 0.75rem;
    color: rgba(248, 250, 252, 0.7);
    font-size: 0.824rem;
    font-weight: 600;
    text-transform: capitalize;
    position: relative;
    transition: color 0.25s ease;
    white-space: nowrap;
}

.hub-navbar-tab:hover {
    color: #f59e0be6;
}

.hub-navbar-tab.active {
    color: #f59e0be6;
}

/* Tema claro: tabs selecionadas do header com texto branco */
body.light .hub-navbar-tab.active,
body.light .hub-navbar-tab.premium-tab.active {
    color: #fff !important;
}

.hub-navbar-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 2px;
    background: #f59e0be6;
    border-radius: 2px;
    box-shadow: 0 0 6px rgba(249, 115, 22, 0.5);
}

.hub-navbar-tab.premium-tab {
    color: rgba(139, 92, 246, 0.8);
}

.hub-navbar-tab.premium-tab:hover {
    color: #8b5cf6;
}

.hub-navbar-tab.premium-tab.active {
    color: #8b5cf6;
}

.hub-navbar-tab.premium-tab.active::after {
    background: #8b5cf6;
    box-shadow: 0 0 6px rgba(139, 92, 246, 0.5);
}

/* ---- Logo ---- */
.hub-navbar-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: inherit;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .hub-navbar-logo {
        margin-right: 0.35rem;
    }
}

.hub-navbar-logo__img {
    width: 88px;
    height: 88px;
    object-fit: contain;
    filter: drop-shadow(0 2px 10px rgba(249, 115, 22, 0.4));
    border-radius: 10px;
}

.hub-navbar-logo__name {
    display: inline-block;
    font-size: 1.85rem;
    font-weight: 400;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(248, 250, 252, 0.95);
    line-height: 1;
    white-space: nowrap;
}

.hub-navbar-logo--wallet .hub-navbar-logo__name {
    display: none;
}

.hub-navbar-logo__avatar {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(249, 115, 22, 0.22);
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(15, 23, 42, 0.25));
    box-shadow: 0 14px 24px rgba(2, 6, 23, 0.18);
    flex-shrink: 0;
}

.hub-navbar-logo__avatar-img,
.hub-launch-desktop-menu__avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.hub-navbar-logo__avatar-fallback,
.hub-launch-desktop-menu__avatar-fallback {
    width: 100%;
    height: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    font-size: 1.35rem;
    font-weight: 900;
    text-transform: uppercase;
}

.hub-navbar-logo__identity {
    display: none;
    flex-direction: column;
    gap: 0.14rem;
    min-width: 0;
    line-height: 1.08;
}

.hub-navbar-logo__eyebrow {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 237, 213, 0.7);
}

.hub-navbar-logo__user {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: rgba(255, 247, 237, 0.98);
    font-size: 1.06rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    max-width: 220px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hub-navbar-logo__crown {
    color: #f59e0be6;
    font-size: 0.9rem;
    animation: welcomeCrownGlow 2s ease-in-out infinite;
    flex-shrink: 0;
}

@media (min-width: 768px) {
    .hub-navbar-logo__identity {
        display: flex;
    }
}

body.light .hub-navbar-logo__eyebrow {
    color: rgba(154, 52, 18, 0.72);
}

body.light .hub-navbar-logo__user {
    color: #9a3412;
}

.hub-navbar-mobile-tools {
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    margin-left: 0.5rem;
    min-width: 0;
}

.hub-navbar-mobile-wallet {
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    padding: 0.32rem 0.38rem 0.32rem 0.6rem;
    border-radius: 999px;
    border: 1px solid rgba(96, 165, 250, 0.18);
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 34%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(2, 6, 23, 0.98));
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.05),
        0 12px 24px rgba(2, 6, 23, 0.22);
    min-width: 0;
}

.hub-navbar-logo:not(.hub-navbar-logo--wallet) .hub-navbar-mobile-tools {
    display: none;
}

.hub-navbar-mobile-voucher-ticker {
    position: relative;
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    width: 102px;
    height: 34px;
    padding: 0.28rem 1.6rem 0.28rem 0.56rem;
    border-radius: 14px;
    border: 1px solid rgba(245, 158, 11, 0.22);
    background:
        radial-gradient(circle at top center, rgba(251, 191, 36, 0.16), transparent 46%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.98));
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.05),
        0 10px 20px rgba(2, 6, 23, 0.24);
    overflow: hidden;
    flex-shrink: 0;
}

.hub-navbar-mobile-voucher-ticker__member {
    display: block;
    margin-bottom: 1px;
    color: #f8fafc;
    font-size: 0.43rem;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
}

.hub-navbar-mobile-voucher-ticker.is-premium .hub-navbar-mobile-voucher-ticker__member {
    color: #fde68a;
}

.hub-navbar-mobile-voucher-ticker.is-free .hub-navbar-mobile-voucher-ticker__member {
    color: #bfdbfe;
}

.hub-navbar-mobile-voucher-ticker__window {
    position: relative;
    display: block;
    height: 12px;
    overflow: hidden;
}

.hub-navbar-mobile-voucher-ticker__plus {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    border: 1px solid rgba(251, 191, 36, 0.32);
    border-radius: 999px;
    background: linear-gradient(135deg, #f59e0be6, #fbbf24);
    color: #111827;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 14px rgba(245, 158, 11, 0.24);
    cursor: pointer;
}

.hub-navbar-mobile-voucher-ticker__plus i {
    font-size: 0.62rem;
}

.hub-navbar-mobile-voucher-ticker__item {
    position: absolute;
    inset: 0;
    display: block;
    color: #f8fafc;
    font-size: 0.63rem;
    font-weight: 900;
    letter-spacing: -0.01em;
    line-height: 12px;
    white-space: nowrap;
    opacity: 0;
    transform: translateY(-145%);
    transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.34s ease;
}

.hub-navbar-mobile-voucher-ticker__item.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.hub-navbar-mobile-voucher-ticker__item.is-exiting {
    opacity: 0;
    transform: translateY(145%);
}

.hub-navbar-mobile-wallet__copy {
    display: grid;
    gap: 0;
    min-width: 0;
}

.hub-navbar-mobile-wallet__value {
    display: block;
    color: #f8fafc;
    font-size: 0.86rem;
    line-height: 1;
    font-weight: 900;
    letter-spacing: -0.03em;
    white-space: nowrap;
}

.hub-navbar-mobile-wallet__plus {
    width: 24px;
    height: 24px;
    border: 0;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: linear-gradient(135deg, #f59e0be6, #fbbf24);
    color: #111827;
    box-shadow: 0 8px 18px rgba(245, 158, 11, 0.24);
}

.hub-navbar-mobile-wallet__plus i {
    font-size: 0.74rem;
}

@media (min-width: 768px) {
    .hub-navbar-logo__name {
        display: inline-block;
    }
}

@media (max-width: 420px) {
    .hub-navbar-logo__img,
    .hub-navbar-logo__img--premium {
        display: none !important;
    }

    .hub-navbar-logo {
        gap: 0;
        min-width: 0;
    }

    .hub-navbar-logo__name {
        font-size: 0.92rem;
        letter-spacing: 0.03em;
        max-width: 118px;
    }

    .hub-navbar-wrapper--site .hub-navbar-logo__name {
        font-size: 0.94rem;
        max-width: min(168px, calc(100vw - 156px));
    }

    .hub-navbar-mobile-wallet {
        max-width: min(160px, calc(100vw - 144px));
        padding: 0.28rem 0.34rem 0.28rem 0.5rem;
    }

    .hub-navbar-mobile-tools {
        gap: 0.3rem;
        margin-left: 0.35rem;
    }

    .hub-navbar-mobile-voucher-ticker {
        width: 114px;
        height: 32px;
        padding: 0.25rem 1.5rem 0.25rem 0.5rem;
    }

    .hub-navbar-mobile-voucher-ticker__member {
        font-size: 0.39rem;
    }

    .hub-navbar-mobile-voucher-ticker__item {
        font-size: 0.58rem;
    }

    .hub-navbar-mobile-wallet__value {
        font-size: 0.78rem;
    }
}

body.light .hub-navbar-mobile-wallet {
    border-color: rgba(245, 158, 11, 0.18);
    background:
        radial-gradient(circle at top right, rgba(96, 165, 250, 0.1), transparent 34%),
        linear-gradient(180deg, rgba(255, 250, 245, 0.98), rgba(255, 242, 230, 0.98));
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.7),
        0 12px 24px rgba(194, 65, 12, 0.1);
}

body.light .hub-navbar-mobile-wallet__value {
    color: #172033;
}

body.light .hub-navbar-mobile-voucher-ticker {
    border-color: rgba(245, 158, 11, 0.18);
    background:
        radial-gradient(circle at top center, rgba(251, 191, 36, 0.14), transparent 50%),
        linear-gradient(180deg, rgba(255, 250, 245, 0.98), rgba(255, 242, 230, 0.98));
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.72),
        0 10px 20px rgba(194, 65, 12, 0.1);
}

body.light .hub-navbar-mobile-voucher-ticker__member {
    color: #9a3412;
}

body.light .hub-navbar-mobile-voucher-ticker.is-premium .hub-navbar-mobile-voucher-ticker__member {
    color: #b45309;
}

body.light .hub-navbar-mobile-voucher-ticker.is-free .hub-navbar-mobile-voucher-ticker__member {
    color: #1d4ed8;
}

body.light .hub-navbar-mobile-voucher-ticker__item {
    color: #172033;
}

body.light .hub-navbar-mobile-voucher-ticker__plus {
    border-color: rgba(217, 119, 6, 0.18);
    box-shadow: 0 6px 14px rgba(217, 119, 6, 0.18);
}

/* ---- Right: Auth buttons / User info ---- */
.hub-navbar-right {
    display: none;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
}

@media (min-width: 600px) {
    .hub-navbar-right {
        display: flex;
    }
}

.hub-navbar-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.4rem 1rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.8rem;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.25s ease;
    border: 1px solid transparent;
    cursor: pointer;
    height: 36px;
}

.hub-navbar-btn--after-menu {
    display: none;
}

@media (min-width: 768px) {
    .hub-navbar-btn--after-menu {
        display: inline-flex;
        margin-left: 0.35rem;
    }
}

.hub-navbar-btn--ghost {
    background: transparent;
    border: 1px solid rgba(248, 250, 252, 0.2);
    color: #f8fafc;
}

.hub-navbar-btn--ghost:hover {
    background: rgba(248, 250, 252, 0.08);
    border-color: rgba(248, 250, 252, 0.35);
}

.hub-navbar-btn--primary {
    background: linear-gradient(160deg, #fcd34d 0%, #f59e0be6 45%, #d97706 100%);
    color: #1b1207;
    border: none;
    box-shadow:
        0 8px 0 #92400e,
        0 14px 24px rgba(120, 53, 15, 0.34),
        inset 0 1px 0 rgba(255, 255, 255, 0.35);
    transform: translateY(0);
    transition: transform 0.15s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.hub-navbar-btn--primary:hover {
    background: linear-gradient(160deg, #fde68a 0%, #f59e0be6 40%, #f59e0be6 100%);
    box-shadow:
        0 10px 0 #92400e,
        0 18px 28px rgba(120, 53, 15, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.42);
    transform: translateY(-2px);
    filter: brightness(1.03);
}

.hub-navbar-btn--primary:active {
    transform: translateY(6px);
    box-shadow:
        0 2px 0 #92400e,
        0 8px 14px rgba(120, 53, 15, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.28);
}

.hub-navbar-btn--entrar {
    font-weight: 900;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.hub-navbar-btn--download {
    gap: 0.45rem;
    border-color: rgba(255, 184, 132, 0.4);
    background: rgba(255, 255, 255, 0.06);
    color: #fff3e7;
}

.hub-navbar-btn--download:hover {
    background: rgba(255, 255, 255, 0.11);
    border-color: rgba(255, 204, 163, 0.62);
}

.hub-navbar-btn--grow {
    flex-grow: 1;
    flex-basis: 50%;
}

/* ---- Mobile bottom auth row ---- */
.hub-navbar-mobile-auth {
    display: flex;
    gap: 0.35rem;
    width: 100%;
}

@media (min-width: 600px) {
    .hub-navbar-mobile-auth {
        display: none;
    }
}

.hub-platform-switcher {
    position: fixed;
    left: 50%;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 18px);
    transform: translateX(-50%);
    z-index: 140;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    width: min(92vw, 430px);
    padding: 0.55rem 0.7rem;
    border: 1px solid rgba(255, 151, 76, 0.18);
    border-radius: 999px;
    background:
        linear-gradient(180deg, rgba(20, 7, 3, 0.96), rgba(10, 4, 2, 0.98)),
        radial-gradient(circle at top, rgba(249, 115, 22, 0.2), transparent 52%);
    box-shadow:
        0 20px 46px rgba(0, 0, 0, 0.42),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
}

.hub-platform-switcher__item,
.hub-platform-switcher__chip {
    border: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    min-height: 52px;
    border-radius: 999px;
    transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}

.hub-platform-switcher__item {
    flex: 1 1 48%;
    padding: 0.65rem 0.95rem;
    color: rgba(255, 227, 208, 0.8);
    background: rgba(255, 255, 255, 0.04);
}

.hub-platform-switcher__item.is-active {
    color: #fff7ed;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.04));
    box-shadow: inset 0 0 0 1px rgba(255, 176, 120, 0.16);
}

.hub-platform-switcher__chip {
    position: relative;
    flex: 1.18 1 52%;
    overflow: hidden;
    padding: 0.7rem 1.05rem;
    color: #fff7ed;
    background: linear-gradient(135deg, #f59e0be6 0%, #ea580c 48%, #c2410c 100%);
    box-shadow:
        0 12px 24px rgba(194, 65, 12, 0.34),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

.hub-platform-switcher__chip.is-active {
    box-shadow:
        0 12px 26px rgba(194, 65, 12, 0.2),
        inset 0 0 0 1px rgba(255, 248, 240, 0.22);
    filter: saturate(0.9);
}

.hub-platform-switcher__chip-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 0%, rgba(255, 255, 255, 0.24) 35%, transparent 70%);
    transform: translateX(-120%);
    animation: hubPlatformGlow 4.2s ease-in-out infinite;
}

.hub-platform-switcher__icon {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.12);
    font-size: 0.95rem;
}

.hub-platform-switcher__label {
    position: relative;
    z-index: 1;
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.01em;
}

.hub-platform-switcher__item:hover,
.hub-platform-switcher__chip:hover {
    transform: translateY(-1px);
}

.hub-portal-transition {
    position: fixed;
    inset: 0;
    z-index: 420;
    opacity: 0;
    pointer-events: none;
}

.hub-portal-transition.is-active {
    opacity: 1;
}

.hub-portal-transition__shade,
.hub-portal-transition__curtain,
.hub-portal-transition__glow {
    position: absolute;
    inset: 0;
}

.hub-portal-transition__shade {
    background: rgba(7, 3, 2, 0.2);
    opacity: 0;
}

.hub-portal-transition__curtain {
    background:
        linear-gradient(90deg, rgba(255, 168, 86, 0.06) 0%, rgba(255, 133, 52, 0.42) 18%, rgba(249, 115, 22, 0.94) 46%, rgba(126, 39, 7, 0.98) 100%);
    transform: translateX(-118%);
}

.hub-portal-transition__glow {
    width: 34vw;
    min-width: 140px;
    max-width: 220px;
    left: -34vw;
    background: linear-gradient(90deg, rgba(255, 229, 204, 0), rgba(255, 235, 214, 0.95), rgba(255, 229, 204, 0));
    filter: blur(18px);
    opacity: 0;
}

.hub-portal-transition.is-active .hub-portal-transition__shade {
    animation: hubPortalShade 420ms ease forwards;
}

.hub-portal-transition.is-active .hub-portal-transition__curtain {
    animation: hubPortalSweep 420ms cubic-bezier(0.72, 0.02, 0.24, 1) forwards;
}

.hub-portal-transition.is-active .hub-portal-transition__glow {
    animation: hubPortalGlowSweep 420ms cubic-bezier(0.72, 0.02, 0.24, 1) forwards;
}

.hub-app-download-modal {
    position: fixed;
    inset: 0;
    z-index: 260;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.2rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.hub-app-download-modal.is-open {
    opacity: 1;
    pointer-events: auto;
}

.hub-app-download-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(9, 3, 2, 0.76);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.hub-app-download-modal__dialog {
    position: relative;
    width: min(100%, 420px);
    padding: 1.35rem;
    border-radius: 28px;
    border: 1px solid rgba(255, 159, 92, 0.22);
    background:
        radial-gradient(circle at top right, rgba(249, 115, 22, 0.16), transparent 40%),
        linear-gradient(180deg, rgba(31, 11, 4, 0.98), rgba(13, 5, 2, 0.98));
    box-shadow: 0 28px 80px rgba(0, 0, 0, 0.46);
}

.hub-app-download-modal__close {
    position: absolute;
    top: 0.8rem;
    right: 0.9rem;
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 999px;
    cursor: pointer;
    color: #ffedd5;
    background: rgba(255, 255, 255, 0.06);
}

.hub-app-download-modal__eyebrow {
    margin-bottom: 0.7rem;
    color: #fdba74;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.18em;
}

.hub-app-download-modal__title {
    margin: 0 0 0.8rem;
    color: #fff7ed;
    font-size: 1.5rem;
    font-weight: 900;
    line-height: 1.1;
}

.hub-app-download-modal__text {
    margin: 0 0 1.1rem;
    color: rgba(255, 229, 214, 0.82);
    line-height: 1.55;
}

.hub-app-download-modal__qr-wrap {
    display: grid;
    justify-items: center;
    gap: 0.75rem;
    margin: 0 0 1.15rem;
}

.hub-app-download-modal__qr-frame {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 214px;
    height: 214px;
    padding: 14px;
    border-radius: 26px;
    background: linear-gradient(180deg, rgba(255, 248, 240, 0.98), rgba(255, 237, 219, 0.95));
    box-shadow:
        0 18px 34px rgba(0, 0, 0, 0.26),
        inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.hub-app-download-modal__qr-image {
    width: 100%;
    height: 100%;
    display: block;
    border-radius: 18px;
}

.hub-app-download-modal__qr-caption {
    color: rgba(255, 224, 197, 0.78);
    font-size: 0.83rem;
    text-align: center;
}

.hub-app-download-modal__actions {
    display: grid;
    gap: 0.75rem;
}

.hub-app-download-modal__store,
.hub-app-download-modal__secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    min-height: 52px;
    width: 100%;
    padding: 0.85rem 1rem;
    border-radius: 18px;
    font-weight: 800;
    text-decoration: none;
}

.hub-app-download-modal__store--android,
.hub-app-download-modal__store--ios {
    color: #fff7ed;
    background: linear-gradient(135deg, #f59e0be6 0%, #c2410c 100%);
}

.hub-app-download-modal__secondary {
    margin-top: 0.9rem;
    border: 1px solid rgba(255, 176, 120, 0.2);
    color: #fed7aa;
    background: rgba(255, 255, 255, 0.04);
}

.hub-app-download-modal__empty {
    padding: 0.95rem 1rem;
    border-radius: 18px;
    color: #fed7aa;
    background: rgba(255, 255, 255, 0.04);
    line-height: 1.5;
}

.hub-app-download-modal.is-download-mode #hubRetryAppOpen {
    display: none;
}

.hub-web-app-promo {
    position: fixed;
    inset: 0;
    z-index: 265;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.2rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.22s ease;
}

.hub-web-app-promo.is-open {
    opacity: 1;
    pointer-events: auto;
}

.hub-web-app-promo__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(8, 3, 2, 0.82);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.hub-web-app-promo__dialog {
    position: relative;
    width: min(100%, 460px);
    padding: 1.45rem;
    border-radius: 30px;
    border: 1px solid rgba(255, 169, 92, 0.25);
    background:
        radial-gradient(circle at top right, rgba(249, 115, 22, 0.22), transparent 42%),
        radial-gradient(circle at bottom left, rgba(255, 176, 103, 0.12), transparent 34%),
        linear-gradient(180deg, rgba(31, 11, 4, 0.985), rgba(13, 5, 2, 0.985));
    box-shadow: 0 32px 90px rgba(0, 0, 0, 0.5);
}

.hub-web-app-promo__close {
    position: absolute;
    top: 0.9rem;
    right: 0.95rem;
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 999px;
    cursor: pointer;
    color: #ffedd5;
    background: rgba(255, 255, 255, 0.06);
}

.hub-web-app-promo__eyebrow {
    margin-bottom: 0.7rem;
    color: #fdba74;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.2em;
}

.hub-web-app-promo__title {
    margin: 0 0 0.85rem;
    color: #fff7ed;
    font-size: 1.7rem;
    line-height: 1.05;
    font-weight: 900;
}

.hub-web-app-promo__text {
    margin: 0 0 1rem;
    color: rgba(255, 231, 215, 0.85);
    line-height: 1.6;
}

.hub-web-app-promo__highlight {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.95rem 1rem;
    border-radius: 20px;
    color: #fff7ed;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.22), rgba(194, 65, 12, 0.18));
    border: 1px solid rgba(255, 190, 136, 0.18);
}

.hub-web-app-promo__highlight i {
    color: #fdba74;
    margin-top: 0.15rem;
}

.hub-web-app-promo__actions {
    display: grid;
    gap: 0.8rem;
}

.hub-web-app-promo__store,
.hub-web-app-promo__secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
    min-height: 54px;
    width: 100%;
    padding: 0.9rem 1rem;
    border-radius: 18px;
    font-weight: 800;
    text-decoration: none;
}

.hub-web-app-promo__store {
    color: #fff7ed;
    background: linear-gradient(135deg, #f59e0be6 0%, #c2410c 100%);
    box-shadow: 0 10px 24px rgba(249, 115, 22, 0.24);
}

.hub-web-app-promo__secondary {
    margin-top: 0.95rem;
    border: 1px solid rgba(255, 176, 120, 0.18);
    color: #fed7aa;
    background: rgba(255, 255, 255, 0.04);
}

.hub-web-app-promo__note {
    margin-top: 0.85rem;
    color: rgba(255, 214, 170, 0.72);
    font-size: 0.84rem;
    text-align: center;
}

@media (max-width: 520px) {
    .hub-web-app-promo__dialog {
        padding: 1.2rem;
        border-radius: 26px;
    }

    .hub-web-app-promo__title {
        font-size: 1.45rem;
    }
}

@keyframes hubPlatformGlow {
    0%, 100% { transform: translateX(-120%); opacity: 0; }
    25% { opacity: 1; }
    55% { transform: translateX(120%); opacity: 0.95; }
    100% { opacity: 0; }
}

@keyframes hubPortalShade {
    0% { opacity: 0; }
    30% { opacity: 0.42; }
    100% { opacity: 0; }
}

@keyframes hubPortalSweep {
    0% { transform: translateX(-118%); }
    100% { transform: translateX(102%); }
}

@keyframes hubPortalGlowSweep {
    0% { transform: translateX(-120%); opacity: 0; }
    24% { opacity: 0.95; }
    100% { transform: translateX(145vw); opacity: 0; }
}

@media (min-width: 900px) {
    .hub-platform-switcher {
        width: 360px;
        bottom: 22px;
    }
}

@media (min-width: 768px) {
    .hub-platform-switcher {
        display: none !important;
    }
}

@media (max-width: 768px) {
    .hub-platform-switcher {
        display: none !important;
    }
}

@media (max-width: 599px) {
    body.hub-page {
        padding-bottom: 0 !important;
    }
}

/* ---- User group (authenticated) ---- */
.hub-navbar-user-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.hub-navbar-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.55rem;
    border-radius: 10px;
    font-size: 0.5rem;
    letter-spacing: 0.12em;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
}

.hub-navbar-badge--beta {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(59, 130, 246, 0.2));
    border: 1px solid rgba(37, 99, 235, 0.3);
    animation: navbarBadgePulse 2.5s ease-in-out infinite;
}

.hub-navbar-badge--beta i { color: #2563eb; font-size: 0.5rem; }
.hub-navbar-badge--beta span { color: #60a5fa; }

.hub-navbar-badge--premium {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(37, 99, 235, 0.35));
    border: 1px solid rgba(59, 130, 246, 0.4);
    animation: navbarBadgePulse 2.5s ease-in-out infinite;
}

.hub-navbar-badge--premium i { color: #f59e0be6; font-size: 0.5rem; animation: navbarCrownBounce 1.5s ease-in-out infinite; }
.hub-navbar-badge--premium span { color: #60a5fa; }

@keyframes navbarBadgePulse {
    0%, 100% { box-shadow: 0 0 6px rgba(59, 130, 246, 0.2); }
    50% { box-shadow: 0 0 14px rgba(59, 130, 246, 0.4); }
}

@keyframes navbarCrownBounce {
    0%, 100% { transform: rotate(-5deg) scale(1); }
    50% { transform: rotate(5deg) scale(1.1); }
}

.hub-navbar-user-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid rgba(248, 250, 252, 0.2);
    background: rgba(248, 250, 252, 0.06);
    color: #f8fafc;
    cursor: pointer;
    transition: all 0.25s ease;
}

.hub-navbar-user-btn:hover {
    background: rgba(249, 115, 22, 0.15);
    border-color: rgba(249, 115, 22, 0.4);
}

.hub-navbar-user-icon {
    width: 20px;
    height: 20px;
    fill: transparent;
    stroke: currentColor;
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Mobile: hide hub-top__brand (logo area in grid) */
@media (max-width: 768px) {
    .hub-top__brand {
        display: none !important;
    }
}

/* ========================================
   🔵 PREMIUM BRAND STYLING
======================================== */
/* Default: hide premium logo */
.hub-navbar-logo__img--premium,
.hub-brand-center__logo--premium,
.hub-brand-premium-badge {
    display: none !important;
}

/* Premium user: show premium logo, hide default */
body.is-premium .hub-navbar-logo__img:not(.hub-navbar-logo__img--premium),
body.is-premium .hub-brand-center__logo:not(.hub-brand-center__logo--premium) {
    display: none !important;
}

body.is-premium .hub-navbar-logo__img--premium,
body.is-premium .hub-brand-center__logo--premium {
    display: block !important;
}

@media (max-width: 767px) {
    body.is-premium .hub-navbar-logo__img--premium {
        display: none !important;
    }

    body.is-premium .hub-navbar-logo__img:not(.hub-navbar-logo__img--premium) {
        display: block !important;
    }
}

body.is-premium .hub-brand-premium-badge {
    display: flex !important;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(37, 99, 235, 0.35));
    border: 1px solid rgba(59, 130, 246, 0.4);
    border-radius: 12px;
    animation: navbarBadgePulse 2.5s ease-in-out infinite;
}

body.is-premium .hub-brand-premium-badge i {
    color: #f59e0be6;
    font-size: 0.6rem;
    animation: navbarCrownBounce 1.5s ease-in-out infinite;
}

body.is-premium .hub-brand-premium-badge span {
    color: #60a5fa;
    font-size: 0.5rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

/* Premium: border azul no navbar */
body.is-premium .hub-navbar-wrapper {
    border-bottom-color: rgba(59, 130, 246, 0.2);
}

body.is-premium .hub-navbar-logo__img {
    filter: drop-shadow(0 2px 8px rgba(59, 130, 246, 0.35));
}

body.is-premium .hub-navbar-tab:hover,
body.is-premium .hub-navbar-tab.active {
    color: #3b82f6;
}

body.is-premium .hub-navbar-tab.active::after {
    background: #3b82f6;
    box-shadow: 0 0 6px rgba(59, 130, 246, 0.5);
}

body.is-premium .hub-navbar-btn--primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
}

body.is-premium .hub-navbar-btn--primary:hover {
    background: linear-gradient(135deg, #60a5fa, #3b82f6);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

body.is-premium .hub-navbar-user-btn:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: rgba(59, 130, 246, 0.4);
}

/* Desktop hub-brand-center premium */
body.is-premium .hub-brand-center {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

body.is-premium .hub-brand-center__logo {
    filter: drop-shadow(0 4px 12px rgba(59, 130, 246, 0.5));
}

@media (min-width: 769px) {
    body.is-premium .hub-brand-premium-badge {
        padding: 0.25rem 0.65rem;
    }
    body.is-premium .hub-brand-premium-badge i {
        font-size: 0.7rem;
    }
    body.is-premium .hub-brand-premium-badge span {
        font-size: 0.55rem;
    }
}

/* ========================================
   🔵 PREMIUM: Blue accents on menu/tabs
======================================== */
body.is-premium {
    --hub-primary: #3b82f6;
    --hub-primary-dark: #2563eb;
}

body.is-premium .hub-mobile-tabbar__nav {
    border-color: #3b82f6;
    background: linear-gradient(180deg, #0a1628 0%, #0d1f3c 100%);
    box-shadow:
        0 -4px 20px rgba(0,0,0,0.3),
        inset 0 1px 0 rgba(255,255,255,0.05),
        0 0 12px rgba(59, 130, 246, 0.15);
}

@media (min-width: 769px) {
    body.is-premium .hub-mobile-tabbar__nav {
        box-shadow:
            0 4px 20px rgba(0,0,0,0.5),
            inset 0 1px 0 rgba(255,255,255,0.05),
            0 0 12px rgba(59, 130, 246, 0.15);
    }
}

body.is-premium .hub-section {
    background: transparent;
    border-color: rgba(59, 130, 246, 0.08);
}

body.is-premium .hub-tabs__nav li::after {
    background: #0d1a2e;
}

body.is-premium .hub-tabs__nav li {
    --tab-color: #3b82f6;
    --tab-ring: rgba(59,130,246,0.12);
    --tab-glow: rgba(59,130,246,0.18);
    --tab-shadow: rgba(59,130,246,0.12);
}

body.is-premium .hub-tabs__nav li[data-section="x1"] {
    --tab-color: #3b82f6;
    --tab-ring: rgba(59,130,246,0.12);
    --tab-glow: rgba(59,130,246,0.22);
    --tab-shadow: rgba(59,130,246,0.12);
}

body.is-premium .hub-section__placeholder .spinner {
    border-color: rgba(59, 130, 246, 0.2);
    border-top-color: #3b82f6;
}

body.is-premium .hub-mobile-tabbar__btn.fantasy {
    --hub-tab-accent: #3b82f6;
}

/* ========================================
   🟠 BETA BRAND STYLING (Non-Premium)
======================================== */
.hub-brand-beta-badge {
    display: none !important;
}

body:not(.is-premium) .hub-brand-beta-badge {
    display: flex !important;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(59, 130, 246, 0.18));
    border: 1px solid rgba(37, 99, 235, 0.25);
    border-radius: 12px;
    animation: navbarBadgePulse 2.5s ease-in-out infinite;
    position: relative;
    z-index: 10;
}

body:not(.is-premium) .hub-brand-beta-badge i {
    color: #2563eb;
    font-size: 0.6rem;
    animation: navbarCrownBounce 1.5s ease-in-out infinite;
    filter: drop-shadow(0 0 4px rgba(37, 99, 235, 0.4));
}

body:not(.is-premium) .hub-brand-beta-badge span {
    color: #1d4ed8;
    font-size: 0.5rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    font-weight: 700;
}

/* Beta: non-premium navbar tint */
body:not(.is-premium) .hub-navbar-wrapper {
    border-bottom-color: rgba(249, 115, 22, 0.15);
}

body:not(.is-premium) .hub-navbar-logo__img {
    filter: drop-shadow(0 2px 8px rgba(249, 115, 22, 0.35));
}

body:not(.is-premium) .hub-brand-center {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

body:not(.is-premium) .hub-brand-center__logo {
    filter: drop-shadow(0 4px 12px rgba(249, 115, 22, 0.6));
}

@media (min-width: 769px) {
    body:not(.is-premium) .hub-brand-beta-badge {
        padding: 0.3rem 0.75rem;
    }
    body:not(.is-premium) .hub-brand-beta-badge i {
        font-size: 0.75rem;
    }
    body:not(.is-premium) .hub-brand-beta-badge span {
        font-size: 0.6rem;
        letter-spacing: 0.14em;
    }
}

/* ========================================
   📊 STATS PAGINATION - Modern Style
======================================== */
.rr-stats-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.rr-stats-pagination__btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(234, 88, 12, 0.15));
    border: 1px solid rgba(249, 115, 22, 0.3);
    border-radius: 12px;
    color: #f59e0be6;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.rr-stats-pagination__btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent, rgba(249, 115, 22, 0.2));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.rr-stats-pagination__btn:hover::before {
    opacity: 1;
}

.rr-stats-pagination__btn:hover {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(234, 88, 12, 0.25));
    border-color: rgba(249, 115, 22, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.rr-stats-pagination__btn:active {
    transform: translateY(0);
}

.rr-stats-pagination__btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.4);
}

.rr-stats-pagination__btn:disabled:hover {
    transform: none;
    box-shadow: none;
}

.rr-stats-pagination__btn i {
    font-size: 0.85rem;
    transition: transform 0.3s ease;
}

.rr-stats-pagination__btn--prev:hover i {
    transform: translateX(-3px);
}

.rr-stats-pagination__btn--next:hover i {
    transform: translateX(3px);
}

.rr-stats-pagination__indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    min-width: 100px;
    justify-content: center;
}

.rr-stats-pagination__current {
    color: #f59e0be6;
    font-size: 1.1rem;
}

.rr-stats-pagination__separator {
    color: rgba(255, 255, 255, 0.3);
    font-weight: 400;
}

.rr-stats-pagination__total {
    color: rgba(255, 255, 255, 0.6);
}

@media (max-width: 576px) {

    .rr-stats-pagination__btn {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
    }

    .rr-stats-pagination__label {
        display: none;
    }

    .rr-stats-pagination__btn i {
        margin: 0 !important;
    }

    .rr-stats-pagination__indicator {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        min-width: 80px;
    }
}

/* Hub: hide site header on all breakpoints */
body.hub-page .rr-navbar { display: none; }

/* Desktop/web: hide site header to feel like app */
@media (min-width: 769px) {
    /* Make hub full-width on desktop */
    body.hub-page main.rr-container { width: 100%; margin: 0; }

    /* Desktop: nav is in header now, hide all tabbar/menu instances */
    .hub-brand-menu-slot { display: none; }
    section.hub-shell > .hub-shell__nav { display: none !important; }
    #hubBrandMenuSlot .hub-shell__nav { display: none !important; }
    .hub-mobile-tabbar {
        z-index: 1002 !important;
        position: relative;
    }

/* Medium-large viewports: tabbar still hidden on desktop */
@media (min-width: 769px) and (max-width: 1499px) {
    section.hub-shell > .hub-shell__nav {
        display: none !important;
    }
}

    /* Ensure live and banners occupy their columns nicely */

    .hub-brand-center__logo { width: 260px; }
    .hub-brand-center__name { font-size: 1.25rem; }
}

/* Profile popout */
.hub-profile-popout {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 9999;
}

.hub-profile-popout.is-open {
    display: block;
}

.hub-profile-popout__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(3px);
}

.hub-profile-popout__panel {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: min(720px, calc(100% - 24px));
    max-height: calc(100% - 24px);
    overflow: auto;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(15, 23, 42, 0.92);
    box-shadow: 0 40px 120px rgba(0, 0, 0, 0.55);
}

.hub-profile-popout__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.hub-profile-popout__title {
    font-weight: 900;
    color: #f8fafc;
    font-size: 1rem;
    line-height: 1.25;
}

.hub-profile-popout__subtitle {
    margin-top: .25rem;
    color: rgba(203, 213, 225, 0.9);
    font-weight: 600;
    font-size: .85rem;
}

.hub-profile-popout__close {
    border: 0;
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 22px;
    line-height: 1;
}

.hub-profile-popout__body {
    padding: 1.1rem;
}

.hub-profile-popout__alert {
    border-radius: 12px;
    padding: .85rem .95rem;
    margin-bottom: .9rem;
    border: 1px solid rgba(249, 115, 22, 0.28);
    background: rgba(249, 115, 22, 0.12);
    color: #f8fafc;
    font-weight: 650;
}

.hub-profile-popout__alert.is-info {
    border-color: rgba(96, 165, 250, 0.28);
    background: rgba(59, 130, 246, 0.16);
}

.hub-profile-popout__alert.is-success {
    border-color: rgba(74, 222, 128, 0.28);
    background: rgba(22, 163, 74, 0.16);
}

.hub-profile-popout__alert.is-error {
    border-color: rgba(248, 113, 113, 0.3);
    background: rgba(220, 38, 38, 0.16);
}

.hub-profile-photo {
    display: grid;
    grid-template-columns: 92px 1fr;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1.1rem;
}

.hub-profile-photo__preview {
    width: 92px;
    height: 92px;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.06);
    overflow: hidden;
    display: grid;
    place-items: center;
}

.hub-profile-photo__preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hub-profile-photo__placeholder {
    width: 100%;
    height: 100%;
    display: grid;
    place-items: center;
    color: rgba(255, 255, 255, 0.8);
    font-size: 30px;
}

.hub-profile-photo__label {
    display: block;
    font-weight: 800;
    color: #f8fafc;
    margin-bottom: .25rem;
}

.hub-profile-help {
    display: block;
    color: rgba(203, 213, 225, 0.9);
    margin-top: .25rem;
    font-weight: 600;
}

.hub-profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .9rem;
}

.hub-profile-field {
    display: grid;
    gap: .4rem;
}

.hub-profile-field--full {
    grid-column: 1 / -1;
}

.hub-profile-label {
    font-weight: 800;
    color: #e2e8f0;
    font-size: .85rem;
}

.hub-profile-toggle {
    display: flex;
    align-items: center;
    gap: .85rem;
    min-height: 72px;
    padding: .9rem 1rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(2, 6, 23, 0.38);
    cursor: pointer;
}

.hub-profile-toggle input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.hub-profile-toggle__switch {
    position: relative;
    flex: 0 0 56px;
    width: 56px;
    height: 32px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.28);
    background: rgba(30, 41, 59, 0.92);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
    transition: background .18s ease, border-color .18s ease, box-shadow .18s ease;
}

.hub-profile-toggle__switch::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 24px;
    height: 24px;
    border-radius: 999px;
    background: linear-gradient(180deg, #ffffff, #cbd5e1);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.28);
    transition: transform .18s ease;
}

.hub-profile-toggle input:checked + .hub-profile-toggle__switch {
    border-color: rgba(34, 197, 94, 0.36);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.9), rgba(21, 128, 61, 0.92));
    box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.12), 0 14px 24px rgba(21, 128, 61, 0.16);
}

.hub-profile-toggle input:checked + .hub-profile-toggle__switch::after {
    transform: translateX(24px);
}

.hub-profile-toggle__copy {
    display: grid;
    gap: .24rem;
}

.hub-profile-toggle__copy strong {
    color: #f8fafc;
    font-size: .92rem;
    font-weight: 900;
    line-height: 1.15;
}

.hub-profile-toggle__copy small {
    color: rgba(203, 213, 225, 0.82);
    font-size: .78rem;
    line-height: 1.35;
    font-weight: 600;
}

.hub-username-wrap {
    position: relative;
    display: grid;
    gap: .4rem;
}

.hub-username-lock {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    border-radius: 16px;
    background: rgba(2, 6, 23, 0.05);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    pointer-events: auto;
}

.hub-username-lock__card {
    width: min(92%, 420px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    background: rgba(15, 23, 42, 0.62);
    box-shadow: 0 18px 50px rgba(0,0,0,0.45);
    padding: .75rem .9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}

.hub-username-lock__title {
    font-weight: 900;
    color: #f8fafc;
    font-size: .84rem;
    line-height: 1.2;
}

@media (min-width: 769px) {
    .hub-username-lock__card {
        width: min(100%, 360px);
        padding: .6rem .75rem;
    }

    .hub-username-lock__title {
        font-size: .82rem;
    }
}

.hub-username-lock__btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    text-decoration: none;
    border-radius: 999px;
    padding: .55rem .9rem;
    background: linear-gradient(135deg, #f59e0be6, #ea580c);
    color: #0f172a;
    font-weight: 900;
    white-space: nowrap;
}

.hub-profile-input {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(2, 6, 23, 0.35);
    color: #f8fafc;
    padding: .65rem .75rem;
    outline: none;
}

.hub-profile-input:disabled {
    opacity: .55;
    cursor: not-allowed;
}

.hub-profile-actions {
    display: flex;
    justify-content: flex-end;
    gap: .6rem;
    margin-top: 1rem;
}

.hub-profile-btn {
    border-radius: 999px;
    padding: .65rem 1rem;
    font-weight: 900;
    border: 1px solid rgba(255, 255, 255, 0.16);
    cursor: pointer;
}

.hub-profile-btn--ghost {
    background: rgba(255, 255, 255, 0.06);
    color: #f8fafc;
}

.hub-profile-btn--primary {
    background: linear-gradient(135deg, #f59e0be6, #ea580c);
    border: 0;
    color: #0f172a;
}

body.light .hub-profile-popout__backdrop {
    background: rgba(15, 23, 42, 0.18);
    backdrop-filter: blur(6px);
}

body.light .hub-profile-popout__panel {
    border: 1px solid rgba(249, 115, 22, 0.18);
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.1), transparent 34%),
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.12), transparent 30%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(255, 247, 237, 0.98));
    box-shadow:
        0 34px 80px rgba(15, 23, 42, 0.18),
        0 10px 28px rgba(249, 115, 22, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
}

body.light .hub-profile-popout__header {
    border-bottom-color: rgba(59, 130, 246, 0.14);
}

body.light .hub-profile-popout__title {
    color: #ea580c;
}

body.light .hub-profile-popout__subtitle {
    color: #2563eb;
}

body.light .hub-profile-popout__close {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 237, 213, 0.92));
    border: 1px solid rgba(249, 115, 22, 0.2);
    color: #ea580c;
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.12);
}

body.light .hub-profile-popout__alert {
    border-color: rgba(249, 115, 22, 0.22);
    background: linear-gradient(135deg, rgba(255, 237, 213, 0.94), rgba(239, 246, 255, 0.92));
    color: #1e3a8a;
}

body.light .hub-profile-popout__alert.is-info {
    border-color: rgba(59, 130, 246, 0.24);
    background: linear-gradient(135deg, rgba(219, 234, 254, 0.98), rgba(239, 246, 255, 0.96));
    color: #1d4ed8;
}

body.light .hub-profile-popout__alert.is-success {
    border-color: rgba(22, 163, 74, 0.24);
    background: linear-gradient(135deg, rgba(220, 252, 231, 0.98), rgba(240, 253, 244, 0.96));
    color: #166534;
}

body.light .hub-profile-popout__alert.is-error {
    border-color: rgba(239, 68, 68, 0.24);
    background: linear-gradient(135deg, rgba(254, 226, 226, 0.98), rgba(255, 241, 242, 0.96));
    color: #b91c1c;
}

body.light .hub-profile-photo__preview {
    border-color: rgba(59, 130, 246, 0.16);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(239, 246, 255, 0.96));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
}

body.light .hub-profile-photo__placeholder {
    color: #2563eb;
}

body.light .hub-profile-photo__label,
body.light .hub-profile-label {
    color: #1e3a8a;
}

body.light .hub-profile-help {
    color: #64748b;
}

body.light .hub-profile-popout__panel,
body.light .hub-profile-popout__panel p,
body.light .hub-profile-popout__panel strong {
    color: #1e3a8a;
}

body.light .hub-profile-input {
    border-color: rgba(59, 130, 246, 0.18);
    background: rgba(255, 255, 255, 0.98);
    color: #0f172a;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.92),
        0 6px 16px rgba(148, 163, 184, 0.08);
    color-scheme: light;
}

body.light .hub-profile-input::placeholder {
    color: #94a3b8;
}

body.light .hub-profile-input[type="file"] {
    padding: 0.55rem 0.6rem;
    color: #1e3a8a;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(239, 246, 255, 0.98));
}

body.light .hub-profile-input[type="file"]::file-selector-button {
    margin-right: 0.75rem;
    padding: 0.55rem 0.85rem;
    border: 1px solid rgba(249, 115, 22, 0.24);
    border-radius: 10px;
    background: linear-gradient(135deg, #fdba74, #f97316);
    color: #fff;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 10px 18px rgba(249, 115, 22, 0.16);
}

body.light .hub-profile-input:focus {
    border-color: rgba(249, 115, 22, 0.38);
    box-shadow:
        0 0 0 3px rgba(249, 115, 22, 0.14),
        0 10px 22px rgba(249, 115, 22, 0.08);
}

body.light .hub-profile-input:disabled {
    opacity: 1;
    color: #334155;
    background: linear-gradient(180deg, rgba(241, 245, 249, 0.98), rgba(226, 232, 240, 0.98));
    border-color: rgba(148, 163, 184, 0.22);
}

body.light .hub-profile-toggle {
    border-color: rgba(59, 130, 246, 0.16);
    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(239,246,255,0.96));
    box-shadow: 0 10px 24px rgba(148, 163, 184, 0.1);
}

body.light .hub-profile-toggle__switch {
    border-color: rgba(148, 163, 184, 0.26);
    background: rgba(226, 232, 240, 0.96);
}

body.light .hub-profile-toggle__switch::after {
    background: linear-gradient(180deg, #ffffff, #e2e8f0);
}

body.light .hub-profile-toggle__copy strong {
    color: #0f172a;
}

body.light .hub-profile-toggle__copy small {
    color: #475569;
}

body.light .hub-profile-btn {
    box-shadow: 0 10px 20px rgba(148, 163, 184, 0.1);
}

body.light .hub-profile-btn--ghost {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(239, 246, 255, 0.95));
    border-color: rgba(59, 130, 246, 0.2);
    color: #2563eb;
    box-shadow: 0 12px 22px rgba(59, 130, 246, 0.12);
}

body.light .hub-profile-btn--primary {
    background: linear-gradient(135deg, #fb923c, #f97316 52%, #ea580c 100%);
    color: #ffffff;
    box-shadow: 0 14px 28px rgba(249, 115, 22, 0.24);
}

@media (max-width: 768px) {
    .hub-profile-grid {
        grid-template-columns: 1fr;
    }
}

/* Hide the header CTA on the hub page (CTA is rendered above tabs) */
body.hub-page .rr-navbar .rr-header-cta {
    display: none;
}

.hub-side-card {
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(12px);
    padding: 1.25rem 1.25rem;
}

.hub-side-card.hub-winners-card {
    flex-grow: 1;
    max-height: none;
    height: 100%;
}

.hub-live-card {
    width: 100%;
    border-radius: 20px;
    overflow: visible;
    background: transparent;
    position: relative;
    padding: 3px;
}

/* Borda estática da live */
.hub-live-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 20px;
    padding: 3px;
    background: linear-gradient(135deg, #f59e0be6 0%, #ea580c 100%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    z-index: 1;
    pointer-events: none;
}

.hub-side-card__kicker {
    margin: 0 0 .4rem;
    font-size: .75rem;
    letter-spacing: .28em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.95);
    font-weight: 800;
}

.hub-side-card__title {
    margin: 0 0 .5rem;
    font-weight: 800;
    font-size: 1.15rem;
    color: #e2e8f0;
}

.hub-side-card__text {
    margin: 0;
    color: rgba(203, 213, 225, 0.92);
    line-height: 1.45;
    font-weight: 600;
}

/* Estilos do card de últimos ganhadores */
.hub-winners-card {
    height: 100%;
    max-height: 400px;
    overflow: hidden;
}

.hub-winners-list {
    max-height: none;
    height: calc(100% - 120px);
    overflow: hidden;
    margin: 3rem 0;
    position: relative;
}

.hub-winners-scroll-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    animation: winnerScroll 90s linear infinite;
}

@keyframes winnerScroll {
    0% {
        transform: translateY(0);
    }
    100% {
        transform: translateY(-100%);
    }
}

.hub-winners-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2rem;
    color: #94a3b8;
    font-size: 0.875rem;
}

.hub-winner-item {
    padding: 8px;
    border-left: 4px solid #22c55e;
    border-bottom: 1px solid rgba(34, 197, 94, 0.25);
    background: transparent;
    margin-bottom: 5px;
    font-size: 14px;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 35px;
    display: flex;
    align-items: center;
    border-radius: 4px;
}

.hub-winner-content {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    font-size: 14px; /* Aumentado de 11px para 14px */
    line-height: 1.2;
}

.hub-winner-name {
    font-weight: 600;
    color: #1f2937;
}

.hub-winner-type {
    font-size: 12px; /* Aumentado de 10px para 12px */
    color: #6b7280;
    font-weight: bold; /* Negrito para destacar */
}

.hub-winner-prize {
    font-weight: 700;
    color: #f59e0be6;
    margin-left: auto;
}

.hub-winner-item:hover {
    background: rgba(34, 197, 94, 0.08);
}

.hub-winner-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0be6, #ea580c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.875rem;
}

.hub-winner-info {
    flex: 1;
    min-width: 0;
}

.hub-winner-name {
    font-weight: 600;
    color: #ffffff;
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hub-winner-type {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.125rem;
    font-weight: bold; /* Negrito para destacar */
}

.hub-winner-prize {
    font-weight: 700;
    color: #22c55e;
    font-size: 0.875rem;
    white-space: nowrap;
}

/* Premium winners highlight */
.hub-winner-item--premium {
    border-left-color: #f59e0be6;
    background: linear-gradient(90deg, rgba(245,158,11,0.08) 0%, transparent 100%);
}
.hub-winner-item--premium .hub-winner-name {
    color: #f59e0be6;
}
.hub-winner-premium-badge {
    font-size: 0.7rem;
    margin-right: 3px;
}

.hub-winners-footer {
    text-align: center;
}

.hub-hero__player {
    position: relative;
    width: 92%;
    max-width: 760px;
    margin: 0 auto;
}

.hub-player-shell {
    border-radius: 20px;
    overflow: visible;
    box-shadow: 0 25px 60px rgba(249,115,22,0.35);
}

.hub-live-card .hub-player-frame {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 aspect ratio */
    overflow: hidden;
    background: #000;
    border-radius: 17px;
    z-index: 0;
}

.hub-live-card .hub-player-frame iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* ========================================
   CAPA CUSTOMIZADA DA LIVE
======================================== */
.hub-live-cover {
    position: absolute;
    inset: 0;
    z-index: 2;
    cursor: pointer;
    border-radius: inherit;
    overflow: hidden;
}

.hub-live-cover__bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(28, 9, 4, 0.95) 0%, rgba(78, 24, 8, 0.78) 48%, rgba(16, 6, 2, 0.95) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.hub-live-cover__logo {
    width: 240px;
    height: 240px;
    object-fit: contain;
    opacity: 0.12;
    filter: grayscale(0.5);
}

.hub-live-cover__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: linear-gradient(
        180deg,
        rgba(35, 12, 4, 0.34) 0%,
        rgba(35, 12, 4, 0.12) 40%,
        rgba(35, 12, 4, 0.34) 100%
    );
}

.hub-live-cover__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(239, 68, 68, 0.9);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    padding: 4px 12px;
    border-radius: 4px;
    text-transform: uppercase;
}

.hub-live-cover__badge--scheduled {
    background: rgba(245, 158, 11, 0.9);
    color: #1a0f04;
}

.hub-live-cover__dot {
    font-size: 6px;
    animation: liveDotPulse 1.5s ease-in-out infinite;
}

@keyframes liveDotPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.hub-live-cover__play {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    transition: transform 0.25s ease, filter 0.25s ease;
    filter: drop-shadow(0 4px 12px rgba(249,115,22,0.4));
}

.hub-live-cover__play:hover {
    transform: scale(1.15);
    filter: drop-shadow(0 6px 20px rgba(249,115,22,0.6));
}

.hub-live-cover__play[hidden] {
    display: none !important;
}

.hub-live-cover__title {
    font-size: 0.85rem;
    letter-spacing: 0.1em;
    color: rgba(248, 250, 252, 0.7);
    text-transform: uppercase;
}

.hub-live-cover__hint {
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: rgba(248, 250, 252, 0.82);
    text-transform: uppercase;
}

.hub-live-deadline {
    position: absolute;
    left: 50%;
    bottom: 10px;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    min-width: 210px;
    padding: 8px 14px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.92), rgba(249, 115, 22, 0.92));
    border: 1px solid rgba(255, 255, 255, 0.38);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.45), 0 0 24px rgba(249, 115, 22, 0.45);
    z-index: 4;
    pointer-events: none;
}

.hub-live-deadline--scheduled {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.94), rgba(249, 115, 22, 0.9));
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.45), 0 0 24px rgba(245, 158, 11, 0.34);
}

.hub-live-deadline__label {
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.95);
}

.hub-live-deadline__value {
    font-size: 1rem;
    line-height: 1;
    font-weight: 900;
    letter-spacing: 0.04em;
    color: #fff;
    text-shadow: 0 0 12px rgba(255, 255, 255, 0.35);
}

@media (max-width: 767px) {
    .hub-live-deadline {
        bottom: 8px;
        min-width: 182px;
        padding: 6px 12px;
        border-radius: 10px;
        gap: 1px;
    }

    .hub-live-deadline__label {
        font-size: 0.48rem;
        letter-spacing: 0.05em;
    }

    .hub-live-deadline__value {
        font-size: 0.88rem;
    }
}

@media (min-width: 768px) {
    .hub-live-cover__logo {
        width: 360px;
        height: 360px;
    }
    .hub-live-cover__title {
        font-size: 1.1rem;
    }
    .hub-live-cover__hint {
        font-size: 0.68rem;
    }

    .hub-live-deadline {
        bottom: 14px;
        min-width: 250px;
        padding: 10px 16px;
    }
    .hub-live-deadline__label {
        font-size: 0.66rem;
    }
    .hub-live-deadline__value {
        font-size: 1.18rem;
    }
}

.hub-shell {
    margin-top: 0;
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
}

/* Conteúdo dinâmico agora vive dentro de hub-top__grid */
.hub-top__main {
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
    min-width: 0;
    width: 100%;
    max-width: 100%;
}

/* Tabbar encostado no topo do hubSection */
.hub-shell > .hub-mobile-tabbar {
    margin: 0;
    padding: 0;
}

.hub-shell__nav {
    width: 100%;
    display: flex;
    justify-content: center;
    margin: 0;
}

@if($isBolaoLaunchMode)
.hub-launch-desktop-menu {
    display: none;
}

@media (min-width: 769px) {
    .hub-launch-desktop-menu {
        display: flex;
        position: sticky;
        top: 118px;
        z-index: 55;
        grid-column: 2;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 16px;
        width: 100%;
        max-width: 154px;
        margin: 0;
        padding: 18px 14px;
        border-radius: 24px;
        border: 1px solid rgba(249, 115, 22, 0.18);
        background:
            radial-gradient(circle at top right, rgba(34, 197, 94, 0.08), transparent 34%),
            radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.1), transparent 36%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(8, 12, 24, 0.98));
        box-shadow: 0 24px 44px rgba(2, 6, 23, 0.22);
    }

    body.light .hub-launch-desktop-menu,
    body.light .hub-top__grid > .hub-launch-desktop-menu {
        border-color: rgba(234, 88, 12, 0.14) !important;
        background:
            radial-gradient(circle at top right, rgba(34, 197, 94, 0.08), transparent 34%),
            radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.10), transparent 36%),
            linear-gradient(180deg, rgba(255, 252, 247, 0.98), rgba(255, 255, 255, 0.98)) !important;
        box-shadow:
            0 18px 34px rgba(234, 88, 12, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.9) !important;
    }

    .hub-launch-desktop-menu__brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        text-align: center;
    }

    .hub-launch-desktop-menu__logo {
        width: 52px;
        height: 52px;
        object-fit: contain;
        filter: drop-shadow(0 12px 18px rgba(249, 115, 22, 0.18));
    }

    .hub-launch-desktop-menu__avatar {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(249, 115, 22, 0.22);
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(15, 23, 42, 0.25));
        box-shadow: 0 12px 18px rgba(2, 6, 23, 0.18);
        flex-shrink: 0;
    }

    .hub-launch-desktop-menu__copy {
        display: grid;
        gap: 2px;
        justify-items: center;
    }

    .hub-launch-desktop-menu__kicker {
        color: rgba(255, 237, 213, 0.7);
        font-size: 0.68rem;
        font-weight: 900;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .hub-launch-desktop-menu__title {
        color: #fff7ed;
        font-size: 1.08rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        line-height: 1;
    }

    body.light .hub-launch-desktop-menu__kicker {
        color: rgba(154, 52, 18, 0.72);
    }

    body.light .hub-launch-desktop-menu__title {
        color: #431407;
    }

    .hub-launch-desktop-menu__nav {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 10px;
        width: 100%;
        margin: 0;
    }

    .hub-launch-desktop-menu__nav .hub-header-nav__btn {
        width: 100%;
        justify-content: center;
        padding: 0.75rem 0.8rem;
    }
}
@endif

/* Tabs card: allow glows outside the card without clipping */
.hub-tabs {
    overflow: visible !important;
    position: relative;
    z-index: 5;
}

.hub-tabs .card-body {
    overflow: visible !important;
    padding-top: 1.25rem;
    padding-bottom: 1.25rem;
}

ul.hub-tabs__nav {
    padding-top: 8px;
    padding-bottom: 8px;
}

    /* New circular tab style (icon-focused) */
    ul.hub-tabs__nav {
        position: relative;
        display: flex;
        width: 100%;
        margin: 0;
        padding: 0;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 40px;
    }

    @media (min-width: 769px) {
        ul.hub-tabs__nav {
            gap: 44px;
        }
    }

    .hub-tabs__nav ul{ }

    .hub-tabs__nav li {
        position: relative;
        list-style: none;
        width: 64px;
        height: 64px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: 0.35s ease;
        transform: scale(1);
        transform-origin: center;
        will-change: transform;
        z-index: 1;
        /* per-tab theme */
        --tab-color: var(--hub-primary);
        --tab-ring: rgba(249,115,22,0.12);
        --tab-glow: rgba(249,115,22,0.18);
        --tab-shadow: rgba(249,115,22,0.12);
    }

    /* Selected tab is 30% larger */
    .hub-tabs__nav li.active {
        transform: scale(1.3);
        z-index: 10;
    }

    /* Tab colors */
    .hub-tabs__nav li[data-section="equipes"] {
        --tab-color: #22c55e; /* green */
        --tab-ring: rgba(34,197,94,0.12);
        --tab-glow: rgba(34,197,94,0.22);
        --tab-shadow: rgba(34,197,94,0.12);
    }
    .hub-tabs__nav li[data-section="x1"] {
        --tab-color: #f59e0be6; /* orange */
        --tab-ring: rgba(249,115,22,0.12);
        --tab-glow: rgba(249,115,22,0.22);
        --tab-shadow: rgba(249,115,22,0.12);
    }
    .hub-tabs__nav li[data-section="estatisticas"] {
        --tab-color: #facc15; /* yellow */
        --tab-ring: rgba(250,204,21,0.14);
        --tab-glow: rgba(250,204,21,0.24);
        --tab-shadow: rgba(250,204,21,0.14);
    }

    .hub-tabs__nav li::before {
        content: '';
        position: absolute;
        inset: 14px;
        border-radius: 50%;
        box-shadow: 0 0 0 6px var(--tab-ring);
        transition: 0.35s ease;
        backdrop-filter: blur(2px);
    }


    .hub-tabs__nav li::after {
        /* subtle inner fill to reduce harsh diamond look */
        content: '';
        position: absolute;
        inset: 6px;
        background: #ffffff;
        border: 4px solid transparent;
        transition: 0.35s ease;
        border-radius: 50%;
    }


    /* Active: colored outline around the white inner circle */
    .hub-tabs__nav li.active::after {
        border-color: var(--tab-color);
    }


    /* Remove the active/click "glow" highlight */
    .hub-tabs__nav li.active::before {
        box-shadow: 0 0 0 6px var(--tab-ring);
    }

    .hub-tabs__nav li a {
        position: relative;
        text-decoration: none;
        z-index: 10;
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        color: var(--hub-muted);
    }

    .hub-tab-ring {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 96px;
        height: 96px;
        transform: translate(-50%, -50%) rotate(-90deg);
        pointer-events: none;
        z-index: 2;
        overflow: visible;
    }

    @keyframes hub-ring-spin {
        0% { transform: translate(-50%, -50%) rotate(-90deg); }
        100% { transform: translate(-50%, -50%) rotate(630deg); }
    }

    .hub-tabs__nav li.hub-tab--spin .hub-tab-ring {
        animation: hub-ring-spin 900ms ease-out 1;
    }

    @media (prefers-reduced-motion: reduce) {
        .hub-tabs__nav li.hub-tab--spin .hub-tab-ring {
            animation: none;
        }
    }

    .hub-tab-ring__text {
        fill: var(--tab-color);
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        opacity: 0.95;
    }

    .hub-tabs__nav li a i {
        font-size: 1.6rem;
        transition: transform 0.35s ease, filter 0.35s ease, color 0.35s ease;
        color: var(--tab-color);
        opacity: 1;
        transform-origin: center;
        z-index: 3;
    }

    /* Prevent browser focus ring/press glow on these circular tabs */
    .hub-tabs__nav li a {
        -webkit-tap-highlight-color: transparent;
        outline: none;
    }

    .hub-tabs__nav li a:focus,
    .hub-tabs__nav li a:focus-visible {
        outline: none;
        box-shadow: none;
    }

    /* Only apply hover glow on devices that actually support hover.
       This avoids the "shadow on click" that gets stuck on mobile taps. */
    @media (hover: hover) and (pointer: fine) {
        .hub-tabs__nav li:hover::before {
            inset: 8px;
            box-shadow: 0 0 0 8px var(--tab-glow), 0 6px 24px var(--tab-shadow);
        }

        .hub-tabs__nav li:hover::after {
            inset: 4px;
        }

        .hub-tabs__nav li.active:hover::after {
            inset: 2px;
        }

        .hub-tabs__nav li:hover a i {
            transform: scale(1.22);
        }
    }

    .hub-tabs__nav li a span {
        display: none;
    }

    .hub-tabs__nav li:hover a span {
        display: none;
    }

    /* No icon glow on selected/clicked */
    .hub-tabs__nav li a i {
        filter: none;
    }

.rr-premium-cta {
    background: linear-gradient(135deg, rgba(255, 107, 53, .12) 0%, rgba(247, 147, 30, .08) 100%);
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 20px;
    padding: 1.25rem 1.5rem;
    display: grid;
    gap: .75rem;
}

.rr-premium-cta > a.rr-btn-primary {
    justify-self: center;
    width: fit-content;
    display: inline-flex;
    align-items: center;
    font-size: 1.2em;
    padding: 0.65em 1.1em;
}

.rr-btn-primary {
    background: linear-gradient(135deg, var(--hub-primary), var(--hub-primary-dark));
    color: #111827;
    font-weight: 700;
    border: none;
    border-radius: 14px;
    padding: .65rem 1.1rem;
}

.hub-section {
    border-radius: 0 0 26px 26px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-top: none;
    background: transparent;
    min-height: auto;
    transition: opacity .3s ease;
}

/* Desktop: rounded all corners */
@media (min-width: 769px) {
    .hub-section {
        border-radius: 26px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        margin-top: 0.5rem;
        padding: 1rem 1.5rem 1.5rem;
    }

    @if($isBolaoLaunchMode)
    #hubMainColumn.hub-top__main,
    #hubSection.hub-section {
        width: 100%;
        max-width: 100%;
    }

    #hubMainColumn.hub-top__main {
        padding-right: 0;
    }

    #hubSection.hub-section {
        padding-inline: var(--rr-bolao-launch-content-pad);
        margin-bottom: 0;
    }

    body.hub-page main.rr-container {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.hub-page .rr-footer,
    body.hub-page .rr-footer-pro {
        margin-top: 0 !important;
    }
    @endif
}

.hub-section__placeholder {
    min-height: 120px;
    border: 1px dashed rgba(148, 163, 184, 0.4);
    border-radius: 18px;
    padding: 2.5rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    text-align: left;
}

.hub-section__placeholder .spinner {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 4px solid rgba(249, 115, 22, 0.2);
    border-top-color: var(--hub-primary);
    animation: hub-spin 1s linear infinite;
}

@keyframes hub-spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    body.hub-page #hubSection.hub-section {
        padding-inline: 0.8rem !important;
    }

    body.hub-page #hubSection.hub-section .rr-mobile-control-stack {
        width: 100%;
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
    }

    .hub-hero {
        padding: .75rem 0 .25rem;
        margin-bottom: 0;
    }

    .hub-top__grid {
        grid-template-columns: 1fr;
        padding: 0;
        justify-items: stretch;
        align-items: stretch;
        align-content: start;
    }

    .hub-top__grid > * {
        width: 100%;
        min-width: 0;
    }

    .hub-shell {
        margin-top: 0;
    }

    .hub-live-card {
        border-radius: 0;
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.2);
    }

    /* Mobile: side-card sem bordas laterais */
    .hub-side-card {
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
    }

    /* Hub-section: sem bordas no mobile */
    .hub-section {
        border: none !important;
        border-radius: 0 !important;
    }

    /* Winners card mobile: igual ao desktop */
    .hub-winners-card {
        display: block !important;
        height: 120px !important;
        min-height: 120px !important;
        max-height: 120px !important;
        width: 100% !important;
        margin-top: 0;
        padding: 8px !important;
        position: relative !important;
        overflow: hidden !important;
        border-radius: 0 !important;
        /* Usar background padrão do card, não forçar */
    }
    
    /* Ocultar título "Últimos Ganhadores" no mobile */
    .hub-winners-card .hub-side-card__kicker {
        display: none;
    }
    
    /* Ocultar footer no mobile para mais espaço */
    .hub-winners-card .hub-winners-footer {
        display: none;
    }
    
    .hub-winners-list {
        position: relative !important;
        width: 100% !important;
        height: 100px !important;
        overflow: hidden !important;
        margin: 0 !important;
        background: transparent !important;
    }
    
    /* Footer oculto no mobile */
    .hub-winners-footer {
        display: none;
    }
    
    .hub-winners-scroll-container {
        height: 240px !important; /* 2x a altura para scroll */
        animation: winnerScrollMobile 60s linear infinite;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
    }
    
    @keyframes winnerScrollMobile {
        0% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(-50%); /* 50% para scroll suave */
        }
    }
    
    .hub-winner-item {
        margin-bottom: 3px !important;
        padding: 6px 8px !important;
        min-height: 28px !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
        background: rgba(245, 158, 11, 0.1) !important; /* Igual desktop */
        color: #374151 !important; /* Cor igual desktop */
        border-radius: 4px !important;
        font-size: 11px !important;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .hub-winner-content {
        display: flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        font-size: 11px !important;
        color: #374151 !important; /* Cor igual desktop */
        font-weight: normal !important;
    }

    .hub-winner-name {
        font-weight: bold !important; /* NEGRITO */
        color: white !important; /* BRANCO */
    }

    .hub-winner-prize {
        font-weight: 700 !important; /* Igual desktop */
        color: #22c55e !important; /* VERDE igual desktop */
        font-size: 11px !important;
        margin-left: auto !important;
        flex-shrink: 0 !important;
        text-align: right !important;
        min-width: 50px !important;
    }

    /* Tipo mobile igual desktop */
    .hub-winners-card .hub-winner-type,
    .hub-winner-item .hub-winner-type,
    span.hub-winner-type {
        color: #ffffff !important; /* Cor igual desktop */
        flex-shrink: 0 !important;
        min-width: 35px !important;
        font-weight: bold !important;
        font-size: 10px !important;
    }

    .rr-premium-cta {
        flex-direction: column;
        align-items: flex-start;
    }

    .hub-section__placeholder {
        flex-direction: column;
        text-align: center;
    }
}

/* Mobile bottom tab bar (hub only) */
/* ============================================
   🔥 EPIC TABBAR - Rei do Rodeio Style
   ============================================ */

.hub-mobile-tabbar {
    display: block;
    width: 100%;
    position: relative;
    z-index: 20;
}

.hub-shell__nav--launch-mobile {
    width: 100%;
}

@media (min-width: 769px) {
    .hub-shell__nav--launch-mobile {
        display: none;
    }
}

/* Desktop: mesma largura do menu principal */
@media (min-width: 769px) {
    .hub-top__grid > .hub-shell__nav {
        width: 220px;
        justify-content: flex-start;
        align-self: stretch;
        margin-left: -8px;
    }

    .hub-top__grid > .hub-shell__nav .hub-mobile-tabbar {
        width: 100%;
        max-width: none;
        margin: 0;
        position: relative;
    }

    .hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__nav {
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 8px;
        border-radius: 12px;
        border: 2px solid var(--hub-active-color, #f59e0be6) !important;
        padding: 10px;
    }

    .hub-top__grid > .hub-shell__nav .hub-tab-border-effect {
        display: none !important;
    }

    .hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__btn {
        flex: 0 0 auto;
        flex-direction: row;
        justify-content: flex-start;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        text-align: left;
    }

    .hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__btn .hub-tab-icon {
        width: 20px;
        height: 20px;
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__label {
        max-width: none;
        font-size: 12px;
    }
}

.hub-mobile-tabbar__nav {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    width: 100%;
    padding: 8px 10px 10px;
    background: linear-gradient(180deg, rgba(18, 8, 4, 0.97) 0%, rgba(11, 4, 2, 0.99) 100%);
    border-radius: 20px;
    gap: 2px;
    flex-wrap: nowrap;
    overflow: visible;
    border: 1px solid rgba(255, 228, 214, 0.08);
    box-shadow:
        0 16px 34px rgba(0, 0, 0, 0.42),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
    transition: border-color 0.22s ease, box-shadow 0.22s ease;
}

.hub-mobile-tabbar__nav--launch {
    display: block;
    padding: 18px 12px calc(12px + env(safe-area-inset-bottom, 0px));
    border-radius: 28px;
    overflow: visible;
}

.hub-mobile-tabbar__launch-stage {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    align-items: end;
    gap: 12px;
    min-height: 108px;
}

.hub-mobile-tabbar__btn--launch-side {
    position: relative;
    min-height: 54px;
    padding: 0 16px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    color: rgba(255, 247, 237, 0.9);
    font-size: 0.9rem;
    font-weight: 900;
    letter-spacing: 0.01em;
    text-shadow: 0 1px 12px rgba(0, 0, 0, 0.34);
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 10px 20px rgba(2, 6, 23, 0.18);
    z-index: 2;
}

.hub-mobile-tabbar__btn--launch-side.home {
    --hub-launch-side-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 60' preserveAspectRatio='none'%3E%3Crect width='100' height='60' rx='20' ry='20' fill='white'/%3E%3Ccircle cx='104' cy='-8' r='44' fill='black'/%3E%3C/svg%3E");
}

.hub-mobile-tabbar__btn--launch-side.user {
    --hub-launch-side-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 60' preserveAspectRatio='none'%3E%3Crect width='100' height='60' rx='20' ry='20' fill='white'/%3E%3Ccircle cx='-4' cy='-8' r='44' fill='black'/%3E%3C/svg%3E");
}

.hub-mobile-tabbar__btn--launch-side i {
    font-size: 0.92rem;
    opacity: 0.95;
}

.hub-mobile-tabbar__btn--launch-filter.active {
    color: #fff7ed;
    background: transparent;
}

.hub-mobile-tabbar__launch-filter-label {
    position: relative;
    z-index: 1;
    display: block;
    width: 100%;
    text-align: center;
}

.hub-mobile-tabbar__btn--launch-avatar {
    --hub-launch-avatar-lift: -24px;
    position: relative;
    align-self: start;
    justify-self: center;
    width: 116px;
    min-width: 116px;
    margin-bottom: -18px;
    padding: 0;
    border-radius: 999px;
    background: transparent !important;
    transform: translateY(var(--hub-launch-avatar-lift)) rotate(calc(var(--hub-launch-avatar-tilt, 0deg)));
    transform-origin: center center;
    z-index: 4;
    transition: transform 0.28s ease, filter 0.28s ease, opacity 0.28s ease;
}

.hub-mobile-tabbar__btn--launch-avatar:active {
    transform: translateY(calc(var(--hub-launch-avatar-lift) + 2px)) scale(0.985) rotate(calc(var(--hub-launch-avatar-tilt, 0deg)));
}

.hub-mobile-tabbar__launch-avatar-glow {
    position: absolute;
    inset: 12px 10px 14px;
    border-radius: 999px;
    background:
        radial-gradient(circle, rgba(249, 115, 22, 0.22), rgba(249, 115, 22, 0));
    filter: blur(14px);
    opacity: 0.78;
    pointer-events: none;
}

.hub-mobile-tabbar__launch-avatar-shell {
    position: relative;
    width: 116px;
    height: 116px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background:
        radial-gradient(circle at 50% 28%, rgba(255, 243, 230, 0.34), rgba(255, 243, 230, 0)),
        linear-gradient(180deg, rgba(255, 180, 96, 0.96), rgba(249, 115, 22, 0.92));
    z-index: 6;
    box-shadow:
        0 16px 28px rgba(15, 23, 42, 0.26),
        0 8px 16px rgba(249, 115, 22, 0.22);
}

.hub-mobile-tabbar__launch-avatar-ring {
    position: absolute;
    inset: -4px;
    border-radius: 999px;
    border: 1px solid rgba(255, 237, 213, 0.34);
    opacity: 0.72;
    animation: hubLaunchAvatarPulse 4.2s ease-in-out infinite;
}

.hub-mobile-tabbar__launch-avatar-ring--outer {
    inset: -8px;
    border-color: rgba(255, 255, 255, 0.12);
    opacity: 0.32;
    animation-duration: 5.4s;
}

.hub-mobile-tabbar__launch-avatar-core {
    position: relative;
    width: 96px;
    height: 96px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 999px;
    background: linear-gradient(180deg, rgba(24, 12, 7, 0.92), rgba(8, 5, 3, 0.98));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.18),
        0 0 0 2px rgba(255, 255, 255, 0.08);
}

.hub-mobile-tabbar__launch-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.hub-mobile-tabbar__launch-avatar-img--guest {
    object-fit: contain;
    padding: 16px;
}

.hub-mobile-tabbar__launch-avatar-fallback {
    color: #fff7ed;
    font-size: 2.1rem;
    font-weight: 900;
    letter-spacing: 0.02em;
}

.hub-mobile-tabbar__launch-avatar-badge {
    position: absolute;
    left: 50%;
    bottom: -6px;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    z-index: 7;
    width: auto;
    min-width: 96px;
    min-height: 28px;
    padding: 0 14px;
    background:
        linear-gradient(180deg, rgba(18, 24, 38, 0.94), rgba(29, 39, 61, 0.98));
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 999px;
    color: #f8fafc;
    font-size: 0.54rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-align: center;
    white-space: nowrap;
    box-shadow:
        0 10px 18px rgba(15, 23, 42, 0.22),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
    pointer-events: none;
}

@keyframes hubLaunchAvatarBounce {
    0% { transform: translateY(var(--hub-launch-avatar-lift)) scale(1) rotate(calc(var(--hub-launch-avatar-tilt, 0deg))); }
    40% { transform: translateY(calc(var(--hub-launch-avatar-lift) - 6px)) scale(1.04) rotate(calc(var(--hub-launch-avatar-tilt, 0deg))); }
    100% { transform: translateY(var(--hub-launch-avatar-lift)) scale(1) rotate(calc(var(--hub-launch-avatar-tilt, 0deg))); }
}

.hub-mobile-tabbar__btn--launch-avatar.is-jumped {
    animation: hubLaunchAvatarBounce 0.52s cubic-bezier(0.22, 0.61, 0.36, 1);
}

.hub-mobile-tabbar__nav--launch[data-active-section="perfil"] .hub-mobile-tabbar__launch-avatar-glow {
    background: radial-gradient(circle, rgba(59, 130, 246, 0.34), rgba(59, 130, 246, 0));
}

@keyframes hubLaunchAvatarPulse {
    0%, 100% { transform: scale(1); opacity: 0.86; }
    50% { transform: scale(1.035); opacity: 1; }
}

.hub-mobile-tabbar__nav--app {
    min-height: 84px;
    padding: 10px 100px 10px 10px;
}

/* HARD OVERRIDE: always show full accent border (web + mobile) */
.hub-mobile-tabbar__nav,
.hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__nav {
    border: 1px solid rgba(255, 228, 214, 0.08) !important;
    box-shadow:
        0 16px 34px rgba(0, 0, 0, 0.42),
        inset 0 1px 0 rgba(255, 255, 255, 0.06) !important;
}

/* Desktop: rounded all corners */
@media (min-width: 769px) {
    .hub-mobile-tabbar__nav {
        border-radius: 18px;
        padding: 8px;
        box-shadow:
            0 4px 20px rgba(0,0,0,0.5),
            inset 0 1px 0 rgba(255,255,255,0.05);
    }
}

/* 🌟 BORDER EFFECT - Animated glow indicator */
.hub-tab-border-effect {
    position: absolute;
    width: 34px;
    height: 3px;
    bottom: 5px;
    left: calc(12.5% - 17px);
    background: #f59e0be6;
    border-radius: 12px;
    filter: drop-shadow(0 0 8px rgba(249, 115, 22, 0.45));
    transition: left 0.32s cubic-bezier(0.22, 0.61, 0.36, 1),
                background-color 0.22s ease,
                filter 0.22s ease;
    will-change: left, background-color, filter;
    transform: translateZ(0);
    z-index: 10;
}

.hub-mobile-tabbar__nav--launch .hub-tab-border-effect {
    width: 112px;
    height: 56px;
    top: auto;
    bottom: 0;
    left: 0;
    border-radius: 20px;
    background: linear-gradient(135deg, #ffd7a8 0%, #f59e0be6 44%, #f97316 100%);
    filter: drop-shadow(0 12px 18px rgba(249, 115, 22, 0.2));
    transition:
        left 0.32s cubic-bezier(0.22, 0.61, 0.36, 1),
        width 0.32s cubic-bezier(0.22, 0.61, 0.36, 1),
        background-color 0.22s ease,
        filter 0.22s ease;
    z-index: 0;
    pointer-events: none;
}

/* TAB BUTTONS */
.hub-mobile-tabbar__btn {
    cursor: pointer;
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    background: transparent;
    border: 0;
    border-radius: 14px;
    padding: 8px 4px 6px;
    margin: 0;
    color: #f2d9c8;
    box-shadow: none;
    transition: color 0.22s ease, transform 0.2s ease, background-color 0.22s ease, box-shadow 0.2s ease, filter 0.2s ease;
    -webkit-tap-highlight-color: transparent;
    position: relative;
    min-width: 0;
    will-change: transform, color;
}

.hub-mobile-tabbar__btn::after {
    content: none;
    position: absolute;
    left: 6%;
    right: 6%;
    bottom: -4px;
    height: 34px;
    border-radius: 999px;
    background:
        radial-gradient(62% 100% at 50% 100%, color-mix(in srgb, var(--hub-active-color, #f59e0be6) 90%, #fff 18%) 0%, transparent 72%),
        radial-gradient(48% 78% at 50% 88%, color-mix(in srgb, var(--hub-active-color, #f59e0be6) 78%, #fff 8%) 0%, transparent 78%);
    filter: blur(8px);
    opacity: 0;
    transform: translateY(4px) scaleY(0.92);
    transition: opacity 0.24s ease, transform 0.24s ease;
    pointer-events: none;
}

.hub-mobile-tabbar__btn.active::after {
    opacity: 0;
    transform: translateY(4px) scaleY(0.92);
}

/* Per-tab accent colors */
.hub-mobile-tabbar__btn.home { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.trophy { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.fantasy { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.chart { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.store { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.premium { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.portal { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.user { --hub-tab-accent: #f59e0be6; }
.hub-mobile-tabbar__btn.logout { --hub-tab-accent: #f59e0be6; }

/* ICON STYLES */
.hub-mobile-tabbar__btn .hub-tab-icon {
    width: 22px;
    height: 22px;
    display: block;
    fill: transparent;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
    transition: fill 0.22s ease, stroke 0.22s ease, transform 0.22s ease;
    margin-bottom: 4px;
    will-change: transform, fill, stroke;
}

.hub-mobile-tabbar__btn:not(.active) .hub-tab-icon {
    stroke: currentColor;
}

/* Tema escuro: icones laranja mesmo sem selecao */
body:not(.light) .hub-mobile-tabbar__btn:not(.active) .hub-tab-icon {
    color: #f2d9c8;
    stroke: #f2d9c8;
    opacity: 0.84;
}

.hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal {
    position: absolute;
    top: -24px;
    right: 2px;
    z-index: 8;
    width: 98px;
    height: 98px;
    flex: none;
    padding: 0;
    margin: 0;
    color: #fff7ed;
    background: transparent !important;
    border-radius: 999px;
}

.hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal .hub-tab-icon {
    margin-bottom: 0;
}

.hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-badge {
    width: 98px;
    height: 98px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: linear-gradient(135deg, #ffb067 0%, #f59e0be6 42%, #a93a0a 100%);
    border: 2px solid rgba(255, 240, 229, 0.58);
    box-shadow:
        0 12px 24px rgba(249, 115, 22, 0.34),
        0 10px 18px rgba(0, 0, 0, 0.34),
        inset 0 1px 0 rgba(255, 255, 255, 0.32);
}

.hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-core {
    width: 70px;
    height: 70px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(34, 12, 5, 0.22);
    backdrop-filter: blur(8px);
}

.hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-logo {
    width: 58px;
    height: auto;
    display: block;
    filter: drop-shadow(0 8px 10px rgba(0, 0, 0, 0.28));
}

.hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-label {
    position: absolute;
    left: 50%;
    bottom: 7px;
    transform: translateX(-50%);
    font-size: 9px;
    line-height: 1;
    font-weight: 800;
    color: #ffe7d2;
    letter-spacing: 0.02em;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.42);
}

/* Active state */
.hub-mobile-tabbar__btn.active {
    color: #ff8a2a;
    transform: none;
    background: rgba(255, 255, 255, 0.04);
    box-shadow: none;
}

.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter.active {
    color: #fff7ed;
    background: transparent;
    border-color: transparent;
    box-shadow: none;
}

.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter:not(.active) {
    color: rgba(255, 247, 237, 0.96);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
}

.hub-mobile-tabbar__btn:active {
    transform: translateY(1px);
}

.hub-mobile-tabbar__btn.active .hub-tab-icon {
    fill: transparent !important;
    stroke: currentColor;
    stroke-width: 1.9;
    transform: none;
}

/* Tema escuro: item ativo com ícone branco */
body:not(.light) .hub-mobile-tabbar__btn.active {
    color: #ff8a2a;
}

body:not(.light) .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter.active {
    color: #fff7ed;
}

body:not(.light) .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter:not(.active) {
    color: rgba(255, 247, 237, 0.96);
}

/* LABEL */
.hub-mobile-tabbar__label {
    display: block;
    font-size: 9.5px;
    line-height: 1.1;
    font-weight: 800;
    color: currentColor;
    text-transform: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 68px;
    transition: color 0.22s ease, text-shadow 0.22s ease;
}

.hub-mobile-tabbar__btn.active .hub-mobile-tabbar__label {
    text-shadow: none;
}

/* Hover effects (desktop) */
@media (hover: hover) {
    .hub-mobile-tabbar__btn:not(.active):hover {
        color: #f59e0be6;
    }

    .hub-mobile-tabbar__btn:not(.active):hover .hub-tab-icon {
        stroke: #f59e0be6;
        transform: translateY(-2px);
    }

    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal:hover,
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal:not(.active):hover {
        color: #fff7ed;
        transform: translateY(-2px) scale(1.02);
    }

    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal:hover .hub-tab-icon,
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal:not(.active):hover .hub-tab-icon {
        stroke: #fff7ed;
        transform: none;
    }
}

.hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal:active {
    transform: scale(0.985);
}

/* Order */
.hub-mobile-tabbar__btn.user { order: 97; }
.hub-mobile-tabbar__btn.login { order: 98; }
.hub-mobile-tabbar__btn.logout { order: 99; }

.hub-mobile-tabbar__btn.login {
    flex: 0 0 auto;
    padding: 4px 2px;
    color: #fff7ed;
}

.hub-mobile-tabbar__login-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-height: 34px;
    padding: 0 0.9rem;
    border-radius: 999px;
    background: linear-gradient(160deg, #fdba74 0%, #f59e0be6 40%, #f59e0be6 100%);
    color: #1b1207;
    font-size: 0.78rem;
    font-weight: 900;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    box-shadow:
        0 5px 0 #9a3412,
        0 10px 16px rgba(124, 45, 18, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.42);
    transition: transform 0.16s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.hub-mobile-tabbar__btn.login:active .hub-mobile-tabbar__login-pill {
    transform: translateY(2px);
    box-shadow:
        0 2px 0 #9a3412,
        0 6px 10px rgba(124, 45, 18, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.32);
}

/* Desktop tweaks */
@media (min-width: 769px) {
    .hub-mobile-tabbar { z-index: 30; }
    .hub-mobile-tabbar__btn { padding: 12px 10px 10px; }
    .hub-mobile-tabbar__btn .hub-tab-icon { width: 26px; height: 26px; }
    .hub-mobile-tabbar__label { font-size: 10px; }
    .hub-mobile-tabbar__btn.login { display: none !important; }
}

/* Mobile: smaller icons, fixed to bottom */
@media (max-width: 700px) {
    .hub-mobile-tabbar__nav--launch {
        padding: 16px 10px calc(10px + env(safe-area-inset-bottom, 0px));
        border-radius: 24px;
    }

    .hub-mobile-tabbar__launch-stage {
        min-height: 94px;
        gap: 10px;
    }

    .hub-mobile-tabbar__btn--launch-side {
        min-height: 48px;
        padding: 0 12px;
        border-radius: 18px;
        font-size: 0.8rem;
    }

    .hub-mobile-tabbar__btn--launch-avatar {
        width: 108px;
        min-width: 108px;
        margin-bottom: -16px;
        --hub-launch-avatar-lift: -20px;
    }

    .hub-mobile-tabbar__launch-avatar-badge {
        min-width: 90px;
        min-height: 26px;
        bottom: -5px;
        padding: 0 12px;
        font-size: 0.5rem;
        letter-spacing: 0.06em;
    }

    .hub-mobile-tabbar__launch-avatar-shell {
        width: 108px;
        height: 108px;
    }

    .hub-mobile-tabbar__launch-avatar-core {
        width: 88px;
        height: 88px;
    }

    .hub-mobile-tabbar__nav {
        gap: 0;
        padding: 8px 8px calc(8px + env(safe-area-inset-bottom, 0px));
    }
    .hub-mobile-tabbar__btn {
        flex: 1;
        padding: 8px 2px 4px;
    }
    .hub-mobile-tabbar__btn.login {
        flex: 0 0 auto;
        padding: 6px 2px;
    }
    .hub-mobile-tabbar__login-pill {
        min-height: 31px;
        padding: 0 0.72rem;
        font-size: 0.68rem;
    }
    .hub-mobile-tabbar__nav--app {
        min-height: 82px;
        padding: 10px 92px calc(8px + env(safe-area-inset-bottom, 0px)) 8px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal {
        top: -22px;
        right: -2px;
        width: 92px;
        height: 92px;
        padding: 0;
    }
    .hub-mobile-tabbar__btn .hub-tab-icon {
        width: 21px;
        height: 21px;
        margin-bottom: 4px;
    }
    .hub-mobile-tabbar__label {
        font-size: 8px;
        max-width: 58px;
    }
    .hub-tab-border-effect {
        width: 30px;
        height: 3px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-badge {
        width: 92px;
        height: 92px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-core {
        width: 64px;
        height: 64px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-logo {
        width: 52px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-label {
        font-size: 8.5px;
        bottom: 8px;
    }

    @guest
    .hub-top__profile {
        display: none !important;
    }
    @endguest

    .hub-mobile-tabbar {
        display: block;
        position: fixed;
        left: 0.8rem;
        right: 0.8rem;
        bottom: calc(8px + env(safe-area-inset-bottom, 0px));
        z-index: 2147483000;
        width: auto;
        isolation: isolate;
        transform: translate3d(0, 0, 0);
        -webkit-transform: translate3d(0, 0, 0);
        will-change: transform;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    .hub-mobile-tabbar__nav {
        padding-bottom: 8px;
        border-radius: 16px;
    }

    .hub-mobile-tabbar__nav--launch {
        padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
        border-radius: 24px;
    }

    .hub-shell {
        padding-bottom: 0;
    }
    
    /* iOS Safari fix - garante espaço extra para conteúdo */
    #hubSection {
        min-height: auto;
    }

    .rr-footer,
    .rr-footer-pro {
        position: relative;
        z-index: 0 !important;
    }
}

@supports (-webkit-touch-callout: none) {
    @media (max-width: 700px) {
        .hub-mobile-tabbar {
            position: fixed !important;
            z-index: 2147483000 !important;
            bottom: calc(10px + env(safe-area-inset-bottom, 0px)) !important;
            transform: translate3d(0, 0, 0) !important;
            -webkit-transform: translate3d(0, 0, 0) !important;
        }

        .hub-mobile-tabbar__nav {
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
        }
    }
}

/* Small screens */
@media (max-width: 420px) {
    .hub-mobile-tabbar__btn--launch-side {
        min-height: 44px;
        padding: 0 10px;
        font-size: 0.74rem;
    }

    .hub-mobile-tabbar__launch-avatar-shell {
        width: 108px;
        height: 108px;
    }

    .hub-mobile-tabbar__btn--launch-avatar {
        --hub-launch-avatar-lift: -18px;
    }

    .hub-mobile-tabbar__launch-avatar-core {
        width: 88px;
        height: 88px;
    }

    .hub-mobile-tabbar__launch-avatar-badge {
        min-width: 84px;
        min-height: 24px;
        bottom: -4px;
        padding: 0 10px;
        font-size: 0.44rem;
        letter-spacing: 0.05em;
    }

    .hub-mobile-tabbar__label { font-size: 7.5px; }
    .hub-mobile-tabbar__btn .hub-tab-icon { width: 20px; height: 20px; }
    .hub-mobile-tabbar__nav--app { padding-right: 84px; }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__btn.portal {
        right: -4px;
        width: 88px;
        height: 88px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-badge {
        width: 88px;
        height: 88px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-core {
        width: 60px;
        height: 60px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-logo {
        width: 48px;
    }
    .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-label {
        font-size: 8px;
    }
}

/* ============================================
   ☀️ LIGHT THEME - TABBAR
   Match the header's light background + particles feel
   ============================================ */
body.light .hub-mobile-tabbar__nav,
body.light .hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__nav {
    background: linear-gradient(180deg, rgba(255, 249, 242, 0.95) 0%, rgba(255, 236, 212, 0.9) 100%) !important;
    border-color: rgba(124, 45, 18, 0.12) !important;
    box-shadow:
        0 -2px 12px rgba(120, 62, 24, 0.07),
        0 4px 16px rgba(120, 62, 24, 0.1),
        inset 0 1px 0 rgba(255,255,255,0.7) !important;
}

body.light .hub-mobile-tabbar__launch-stage {
    background: transparent;
}

body.light .hub-mobile-tabbar__btn--launch-filter {
    color: #7c2d12;
}

body.light .hub-mobile-tabbar__btn--launch-filter.active {
    color: #fff7ed;
}

body.light .hub-mobile-tabbar__nav--launch .hub-tab-border-effect {
    background: linear-gradient(135deg, #ffe1bf 0%, #fb923c 42%, #ea580c 100%);
    filter: drop-shadow(0 8px 16px rgba(234, 88, 12, 0.18));
}

body.light .hub-mobile-tabbar__btn--launch-side {
    background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(255,247,237,0.88));
    border-color: rgba(234, 88, 12, 0.12);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.9),
        0 10px 22px rgba(194, 65, 12, 0.08);
}

body.light .hub-mobile-tabbar__launch-avatar-shell {
    background:
        radial-gradient(circle at 50% 30%, rgba(255,255,255,0.56), rgba(255,255,255,0)),
        conic-gradient(from 190deg, rgba(251, 146, 60, 0.95), rgba(249, 115, 22, 0.9), rgba(96, 165, 250, 0.7), rgba(251, 146, 60, 0.95));
}

body.light .hub-mobile-tabbar__launch-avatar-core {
    background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(255,247,237,0.92));
}

body.light .hub-mobile-tabbar__launch-avatar-fallback {
    color: #9a3412;
}

body.light .hub-mobile-tabbar__launch-avatar-badge {
    background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(241,245,249,0.94));
    border-color: rgba(59, 130, 246, 0.14);
    color: #1e3a8a;
}

/* ============================================
   Launch Menu Refresh
   Flat floating tabs hugging the center avatar
   ============================================ */
@include('frontend.partials.hub_mobile_launch_tabbar_styles')

body.light .hub-mobile-tabbar__btn {
    color: #7C2D12;
    background: transparent;
    border: 0;
    box-shadow: none;
}

body.light .hub-mobile-tabbar__btn:not(.active) .hub-tab-icon {
    stroke: #7C2D12;
}

body.light .hub-mobile-tabbar__btn.active {
    color: #ea580c;
    background: rgba(234, 88, 12, 0.08);
}

body.light .hub-mobile-tabbar__btn.active .hub-tab-icon {
    fill: transparent !important;
    stroke: currentColor;
    stroke-width: 1.7;
}

body.light .hub-mobile-tabbar__label {
    color: currentColor;
}

body.light .hub-mobile-tabbar__nav--app .hub-mobile-tabbar__portal-label {
    color: #7c2d12;
    text-shadow: none;
}

body.light .hub-tab-border-effect {
    background: #ea580c;
    filter: drop-shadow(0 0 8px rgba(234, 88, 12, 0.28));
}

@media (hover: hover) {
    body.light .hub-mobile-tabbar__btn:not(.active):hover {
        color: #ea580c;
    }
    body.light .hub-mobile-tabbar__btn:not(.active):hover .hub-tab-icon {
        stroke: #ea580c;
    }
}

/* ============================================
   FANTASY LOTADO - BATTLE TEXT
============================================ */
.rr-league-card__battle-text--fantasy {
    background: linear-gradient(135deg, #f59e0be6 0%, #f59e0be6 50%, #fdba74 100%);
    color: white;
    padding: 14px 20px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 12px;
    box-shadow: 0 4px 20px rgba(249, 115, 22, 0.4);
    animation: fantasyGlow 2s ease-in-out infinite;
    cursor: pointer;
    transition: all 0.3s ease;
}

.rr-league-card__battle-text--fantasy:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(249, 115, 22, 0.6);
}

/* Finalized league - resultado disponível */
.rr-league-card__battle-text--finalized {
    background: linear-gradient(135deg, #d4a017 0%, #f0c74b 50%, #d4a017 100%) !important;
    box-shadow: 0 4px 16px rgba(212, 160, 23, 0.35) !important;
    animation: none !important;
}
.rr-league-card__battle-text--finalized:hover {
    box-shadow: 0 6px 22px rgba(212, 160, 23, 0.55) !important;
}
.rr-league-card__battle-text--finalized i {
    margin-right: 6px;
}

@keyframes fantasyGlow {
    0%, 100% {
        box-shadow: 0 4px 20px rgba(249, 115, 22, 0.4);
    }
    50% {
        box-shadow: 0 4px 30px rgba(249, 115, 22, 0.7);
    }
}

/* ============================================
   FIX: Hub Footer Spacing
   ============================================ */
body.hub-page .rr-footer {
    margin-top: 0 !important;
    border-top: 1px solid rgba(245, 158, 11, 0.22);
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
        radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.08), transparent 40%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(2, 6, 23, 0.98) 100%);
}
body.hub-page .rr-footer-pro {
    border-top: 1px solid rgba(245, 158, 11, 0.2);
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
        radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.08), transparent 40%),
        linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(2, 6, 23, 0.99) 100%) !important;
}

/* Ensure no gap between hub-section and footer */
body.hub-page .hub-shell {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

/* Desktop: Fix footer spacing */
@media (min-width: 769px) {
    body.hub-page .rr-footer-pro {
        box-sizing: border-box;
        width: 100%;
        padding-left: calc(168px + 16px);
        padding-right: 16px;
    }

    body.hub-page #hubSection.has-inline-footer > .rr-footer-pro,
    body.hub-page #hubSection.has-inline-footer > .rr-footer {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-top: 10px !important;
        border-radius: 20px;
    }

    body.hub-page .rr-footer-pro__inner {
        width: min(100%, var(--rr-bolao-launch-width));
        max-width: var(--rr-bolao-launch-width);
        margin-left: auto;
        margin-right: auto;
    }

    body.hub-page .rr-footer {
        border-radius: 0 0 26px 26px; /* Round bottom corners if attached to hub-section */
        margin-top: -1.5rem !important; /* Pull up to cover hub-section padding if needed */
        position: relative;
        z-index: 0;
        padding-top: 3rem; /* Compensate for pull-up */
    }
    
    /* If the footer is outside hub-shell, we need to ensure they touch */
    body.hub-page .hub-section {
        margin-bottom: 0 !important;
        border-bottom: none;
        border-radius: 26px 26px 0 0; /* Only round top */
    }
    
    /* Make the footer look like continuation of the card */
    body.hub-page .rr-footer {
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
            radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.08), transparent 40%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(2, 6, 23, 0.99) 100%);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-top: none;
        border-radius: 0 0 26px 26px;
        width: 100%;
        margin-top: 0 !important;
    }
}

/* Mobile: Ensure footer touches bottom */
@media (max-width: 768px) {
    body.hub-page .rr-footer {
        padding-bottom: calc(110px + env(safe-area-inset-bottom, 0px)) !important;
        margin-top: 0 !important;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
            radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.08), transparent 40%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(2, 6, 23, 0.99) 100%);
        border-top: 1px solid rgba(245, 158, 11, 0.18);
    }
    
    body.hub-page .hub-section {
        border-radius: 0;
        border: none;
        padding-bottom: 0;
    }

    /* Footer pro: espaço para tabbar no mobile */
    body.hub-page .rr-footer-pro {
        padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px)) !important;
        margin-top: 0 !important;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
            radial-gradient(circle at bottom left, rgba(245, 158, 11, 0.08), transparent 40%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(2, 6, 23, 0.99) 100%) !important;
        border-top: 1px solid rgba(245, 158, 11, 0.2);
    }

    /* Keep footer content above the floating mobile tabbar/avatar. */
    body.hub-page .rr-footer-pro__inner {
        padding-bottom: calc(136px + env(safe-area-inset-bottom, 0px)) !important;
    }
}

/* Tema claro: footer em paleta clara */
body.light.hub-page .rr-footer {
    background: linear-gradient(180deg, rgba(255, 249, 242, 0.96) 0%, rgba(255, 238, 220, 0.94) 100%) !important;
    border-top-color: rgba(234, 88, 12, 0.18) !important;
    color: #334155;
}

body.light.hub-page .rr-footer-pro {
    background: linear-gradient(180deg, rgba(255, 249, 242, 0.98) 0%, rgba(255, 238, 220, 0.96) 100%) !important;
    border-top-color: rgba(234, 88, 12, 0.2) !important;
    color: #334155;
}

@media (min-width: 769px) {
    body.light.hub-page .rr-footer {
        border-color: rgba(234, 88, 12, 0.15) !important;
        border-top: none !important;
    }
}

@media (max-width: 768px) {
    body.light.hub-page .rr-footer {
        background: linear-gradient(180deg, rgba(255, 249, 242, 0.96) 0%, rgba(255, 238, 220, 0.95) 100%) !important;
        border-top-color: rgba(234, 88, 12, 0.16) !important;
    }

    body.light.hub-page .rr-footer-pro {
        background: linear-gradient(180deg, rgba(255, 249, 242, 0.98) 0%, rgba(255, 238, 220, 0.96) 100%) !important;
        border-top-color: rgba(234, 88, 12, 0.18) !important;
    }
}

/* Final override: keep a single transparent plane for content/footer */
body.hub-page .hub-hero.hub-top,
body.hub-page .rr-footer,
body.hub-page .rr-footer-pro {
    background: transparent !important;
    background-image: none !important;
}

/* Final header behavior: keep it pinned at the top and let content start below it */
body.hub-page #hubBrandOverlay,
body.hub-page > #hubBrandOverlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    transform: none !important;
    will-change: auto !important;
    z-index: 1200 !important;
}

body.hub-page #hubTop {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

@media (max-width: 768px) {
    body.hub-page main.rr-container {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    body.hub-page #hubTop,
    body.hub-page #hubSection {
        scroll-margin-top: calc(var(--hub-navbar-offset) + 4px);
    }
}

/* Tema claro laranja aplicado nos principais blocos carregados via partial */
@media (min-width: 769px) {
    body.light.hub-page #hubSection .rr-perfil-card,
    body.light.hub-page #hubSection .rr-affiliate-stat-card,
    body.light.hub-page #hubSection .rr-x1-room-item,
    body.light.hub-page #hubSection .rr-x1-history-item,
    body.light.hub-page #hubSection .rr-assinatura-status,
    body.light.hub-page #hubSection .rr-assinatura-cta,
    body.light.hub-page #hubSection .stats-hero,
    body.light.hub-page #hubSection .stats-pcard,
    body.light.hub-page #hubSection .stats-feat,
    body.light.hub-page #hubSection .stats-cta {
        background: linear-gradient(145deg, rgba(255, 249, 242, 0.92), rgba(255, 238, 220, 0.86)) !important;
        border-color: rgba(234, 88, 12, 0.18) !important;
        box-shadow: 0 8px 20px rgba(234, 88, 12, 0.08);
    }
}
</style>
@endpush

@push('script')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="{{ asset('js/hub/fantasy.js') }}?v={{ time() }}" defer></script>
<script src="{{ asset('assets/js/inicial-stats.js') }}?v={{ time() }}" defer></script>
<script>
    // Hub live stream bootstrap
    window.LIVE_STREAM_URL = @json($liveStreamUrl ?? '');
    window.LIVE_STREAM_FALLBACK_URL = @json($liveStreamUrl ?? '');
    window.HUB_ACTIVE_RODEIO_ID = @json($activeRodeio?->id);
    window.HUB_LIVE_MODE = @json($hubHeroMode);
    window.HUB_LIVE_TIMER_TARGET = @json($hubLiveTimerIso ?? null);
</script>
<script>
(function($){
    $(function(){
        document.body.classList.add('hub-page');

        // Header fixo com fundo fosco quando começa a sobrepor o conteúdo
        (function(){
            var overlay = document.getElementById('hubBrandOverlay');
            if (!overlay) return;

            var frameId = null;
            var scrollFrameId = null;

            function syncHubNavbarHeight() {
                frameId = null;
                var styles = window.getComputedStyle(overlay);
                if (styles.display === 'none' || styles.visibility === 'hidden') {
                    document.documentElement.style.setProperty('--hub-navbar-height', '0px');
                    document.documentElement.style.setProperty('--rr-navbar-height', '0px');
                    return;
                }

                var height = Math.ceil(overlay.getBoundingClientRect().height) || 96;
                document.documentElement.style.setProperty('--hub-navbar-height', height + 'px');
                document.documentElement.style.setProperty('--rr-navbar-height', height + 'px');
            }

            function scheduleSyncHubNavbarHeight() {
                if (frameId !== null) {
                    window.cancelAnimationFrame(frameId);
                }
                frameId = window.requestAnimationFrame(syncHubNavbarHeight);
            }

            function syncHubNavbarSurface() {
                scrollFrameId = null;
                var scrollTop = window.scrollY || document.documentElement.scrollTop || 0;
                overlay.classList.toggle('is-scrolled', scrollTop > 8);
            }

            function scheduleSyncHubNavbarSurface() {
                if (scrollFrameId !== null) {
                    window.cancelAnimationFrame(scrollFrameId);
                }
                scrollFrameId = window.requestAnimationFrame(syncHubNavbarSurface);
            }

            scheduleSyncHubNavbarHeight();
            scheduleSyncHubNavbarSurface();
            window.addEventListener('load', scheduleSyncHubNavbarHeight);
            window.addEventListener('resize', scheduleSyncHubNavbarHeight, { passive: true });
            window.addEventListener('orientationchange', scheduleSyncHubNavbarHeight, { passive: true });
            window.addEventListener('scroll', scheduleSyncHubNavbarSurface, { passive: true });

            if ('ResizeObserver' in window) {
                var overlayObserver = new ResizeObserver(scheduleSyncHubNavbarHeight);
                overlayObserver.observe(overlay);
            }

            requestAnimationFrame(scheduleSyncHubNavbarHeight);
            setTimeout(scheduleSyncHubNavbarHeight, 120);
            requestAnimationFrame(scheduleSyncHubNavbarSurface);
            setTimeout(scheduleSyncHubNavbarSurface, 120);
        })();

        (function initHubLivePlayer(){
            if (window.__hubLiveInitDone) return;
            window.__hubLiveInitDone = true;

            var liveCol = document.getElementById('hubTopLiveCol');
            var liveFrame = document.getElementById('hubTopLiveFrame');
            var liveIframe = document.getElementById('hubTopLiveIframe');
            var liveCover = document.getElementById('hubLiveCover');
            var playBtn = document.getElementById('hubLivePlayBtn');
            var liveBadge = document.getElementById('hubLiveBadge');
            var liveBadgeDot = document.getElementById('hubLiveBadgeDot');
            var liveBadgeText = document.getElementById('hubLiveBadgeText');
            var liveDeadline = document.getElementById('hubLiveDeadline');
            var liveDeadlineLabel = document.getElementById('hubLiveDeadlineLabel');
            var liveDeadlineValue = document.getElementById('hubLiveDeadlineValue');
            if (!liveCol || !liveFrame || !liveIframe) return;

            var initialIframeSrc = liveIframe.getAttribute('data-src') || liveIframe.getAttribute('src') || '';
            if (!window.LIVE_STREAM_URL && initialIframeSrc) {
                window.LIVE_STREAM_URL = initialIframeSrc;
            }
            if (!window.LIVE_STREAM_FALLBACK_URL && (window.LIVE_STREAM_URL || initialIframeSrc)) {
                window.LIVE_STREAM_FALLBACK_URL = window.LIVE_STREAM_URL || initialIframeSrc;
            }

            function getLiveMode() {
                return String(window.HUB_LIVE_MODE || '').toLowerCase();
            }

            function setLiveMode(mode) {
                window.HUB_LIVE_MODE = mode;
            }

            function statusToMode(status) {
                var value = String(status || '').toLowerCase();
                if (!value) return getLiveMode() || 'empty';
                return value === 'programado' ? 'scheduled' : 'live';
            }

            function renderLiveState(hasPlayableUrl) {
                var isLiveMode = getLiveMode() === 'live';

                if (liveBadge) {
                    liveBadge.classList.toggle('hub-live-cover__badge--scheduled', !isLiveMode);
                }
                if (liveBadgeDot) {
                    liveBadgeDot.hidden = !isLiveMode;
                }
                if (liveBadgeText) {
                    liveBadgeText.textContent = isLiveMode ? 'AO VIVO' : 'PROGRAMADO';
                }
                if (playBtn) {
                    playBtn.hidden = !isLiveMode || !hasPlayableUrl;
                }
                if (liveDeadline) {
                    liveDeadline.classList.toggle('hub-live-deadline--scheduled', !isLiveMode);
                }
                if (liveDeadlineLabel) {
                    liveDeadlineLabel.textContent = isLiveMode ? 'Rodeio termina em' : 'Programado para';
                }
            }

            /* Play button: hide cover, show iframe with autoplay */
            function startLivePlayer() {
                if (getLiveMode() !== 'live') return;
                var dataSrc = liveIframe.getAttribute('data-src') || window.LIVE_STREAM_URL || '';
                if (!dataSrc) return;
                var src = dataSrc + (dataSrc.indexOf('?') >= 0 ? '&' : '?') + 'autoplay=1&modestbranding=1&rel=0';
                liveIframe.src = src;
                liveIframe.dataset.currentSrc = src;
                liveIframe.style.display = '';
                if (liveCover) liveCover.style.display = 'none';
            }

            if (playBtn) playBtn.addEventListener('click', startLivePlayer);
            if (liveCover) liveCover.addEventListener('click', startLivePlayer);

            function normalizeToEmbedUrl(url) {
                if (!url) return '';
                var raw = String(url).trim();
                if (!raw) return '';

                var patterns = [
                    /youtube\.com\/(?:watch\?v=|embed\/|live\/)([^&\n?#\/]+)/i,
                    /youtu\.be\/([^&\n?#\/]+)/i
                ];

                for (var i = 0; i < patterns.length; i++) {
                    var match = raw.match(patterns[i]);
                    if (match && match[1]) {
                        return 'https://www.youtube.com/embed/' + match[1];
                    }
                }

                return raw;
            }

            function addPlayerParams(url) {
                if (!url) return '';
                return url + (url.indexOf('?') >= 0 ? '&' : '?') + 'autoplay=0&modestbranding=1&rel=0';
            }

            function formatLiveCountdown(iso) {
                if (!iso) return null;
                var target = new Date(iso);
                if (isNaN(target.getTime())) return null;

                var diff = target.getTime() - Date.now();
                if (diff <= 0) return 'Encerrado';

                var totalSeconds = Math.floor(diff / 1000);
                var days = Math.floor(totalSeconds / 86400);
                var hours = Math.floor((totalSeconds % 86400) / 3600);
                var minutes = Math.floor((totalSeconds % 3600) / 60);
                var seconds = totalSeconds % 60;

                if (days > 0) return days + 'd ' + hours + 'h ' + minutes + 'm';
                if (hours > 0) return hours + 'h ' + minutes + 'm ' + seconds + 's';
                return minutes + 'm ' + seconds + 's';
            }

            function renderLiveDeadline() {
                if (!liveDeadline || !liveDeadlineValue) return;
                var value = formatLiveCountdown(window.HUB_LIVE_TIMER_TARGET);
                if (!value) {
                    liveDeadline.hidden = true;
                    return;
                }
                liveDeadline.hidden = false;
                liveDeadlineValue.textContent = value;
            }

            function renderLive(url) {
                var mode = getLiveMode();
                var embedBase = normalizeToEmbedUrl(url);
                var isLiveMode = mode === 'live';
                var hasPlayableUrl = Boolean(embedBase);

                if (mode === 'empty' && !hasPlayableUrl) {
                    liveIframe.removeAttribute('src');
                    delete liveIframe.dataset.currentSrc;
                    liveFrame.hidden = true;
                    liveCol.hidden = true;
                    return;
                }

                renderLiveState(hasPlayableUrl);

                if (!isLiveMode) {
                    liveIframe.removeAttribute('src');
                    delete liveIframe.dataset.currentSrc;
                    if (liveCover) liveCover.style.display = '';
                    liveIframe.style.display = 'none';
                    liveFrame.hidden = false;
                    liveCol.hidden = false;
                    renderLiveDeadline();
                    return;
                }

                if (!embedBase) {
                    liveIframe.removeAttribute('src');
                    delete liveIframe.dataset.currentSrc;
                    if (liveCover) liveCover.style.display = '';
                    liveIframe.style.display = 'none';
                    liveFrame.hidden = false;
                    liveCol.hidden = false;
                    renderLiveDeadline();
                    return;
                }

                /* Update data-src so play button always uses latest URL */
                var embedUrl = addPlayerParams(embedBase);
                liveIframe.setAttribute('data-src', embedBase);

                /* If cover is already hidden (user clicked play), update iframe directly */
                if (liveCover && liveCover.style.display === 'none') {
                    if (liveIframe.dataset.currentSrc !== embedUrl) {
                        liveIframe.src = embedUrl;
                        liveIframe.dataset.currentSrc = embedUrl;
                    }
                    liveIframe.style.display = '';
                } else {
                    /* Cover still visible — show cover, keep iframe hidden */
                    if (liveCover) liveCover.style.display = '';
                    liveIframe.style.display = 'none';
                }

                liveFrame.hidden = false;
                liveCol.hidden = false;
                renderLiveDeadline();
            }

            renderLive(window.LIVE_STREAM_URL || '');
            renderLiveDeadline();
            setInterval(renderLiveDeadline, 1000);

            var rodeioId = window.HUB_ACTIVE_RODEIO_ID;
            if (!rodeioId) return;

            function refreshLiveFromApi() {
                fetch('/api/realtime/transmission/' + rodeioId, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function(response){
                        if (!response.ok) return null;
                        return response.json();
                    })
                    .then(function(payload){
                        if (!payload || !payload.success || !payload.data) return;
                        var nextMode = statusToMode(payload.data.status);
                        var nextUrl = payload.data.live_stream_url || payload.data.stream_url || '';

                        if (nextMode !== getLiveMode()) {
                            setLiveMode(nextMode);
                            window.HUB_LIVE_TIMER_TARGET = null;
                        }

                        // Mantém fallback (env/stream inicial) quando a API não fornecer URL
                        if (!nextUrl) {
                            nextUrl = window.LIVE_STREAM_FALLBACK_URL || window.LIVE_STREAM_URL || '';
                        }

                        if (nextUrl !== (window.LIVE_STREAM_URL || '')) {
                            window.LIVE_STREAM_URL = nextUrl;
                        }
                        renderLive(window.LIVE_STREAM_URL || '');
                    })
                    .catch(function(error){
                        console.debug('[hub-live] atualização da transmissão falhou', error);
                    });
            }

            refreshLiveFromApi();
            setInterval(refreshLiveFromApi, 30000);
        })();

        const HUB_AUTH = Boolean(@json(auth()->check()));
        const HUB_LOGOUT_URL = @json(route('user.logout'));
        const HUB_IS_PREMIUM = Boolean(@json(auth()->check() && auth()->user()->isPremium()));
        const HUB_APP_CONTEXT = {
            isApp: Boolean(@json((bool) request()->query('app'))),
            platform: @json((string) request()->query('platform', ''))
        };
        const HUB_APP_DOWNLOADS = {
            androidUrl: @json($androidAppUrl),
            iosUrl: @json($iosAppUrl)
        };
        const HUB_WEB_APP_PROMO = {
            enabled: Boolean(@json($webAppPromoEnabled)),
            userId: @json($webAppPromoUserId),
            genericUrl: @json($webAppPromoGenericUrl)
        };

        function postNativeMessage(type, payload) {
            var data = {
                source: 'rei_hub',
                type: type,
                payload: payload || {}
            };

            try {
                if (window.ReactNativeWebView && typeof window.ReactNativeWebView.postMessage === 'function') {
                    window.ReactNativeWebView.postMessage(JSON.stringify(data));
                }
            } catch (e) {}

            try {
                if (window.webkit && window.webkit.messageHandlers && window.webkit.messageHandlers.reiApp) {
                    window.webkit.messageHandlers.reiApp.postMessage(data);
                }
            } catch (e) {}

            try {
                if (window.chrome && window.chrome.webview && typeof window.chrome.webview.postMessage === 'function') {
                    window.chrome.webview.postMessage(data);
                }
            } catch (e) {}
        }

        window.RRAppContext = Object.assign(window.RRAppContext || {}, HUB_APP_CONTEXT, {
            postMessage: postNativeMessage
        });

        postNativeMessage('hub_boot', {
            authenticated: HUB_AUTH,
            premium: HUB_IS_PREMIUM
        });

        function openAuthModal(){
            postNativeMessage('hub_open_auth', {
                section: document.body.getAttribute('data-hub-section') || 'inicio'
            });
            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
                return;
            }
            var btn = document.getElementById('openAuthModal');
            if (btn) btn.click();
        }
        window.openAuthModal = openAuthModal;

        document.querySelectorAll('[data-app-return-community]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                postNativeMessage('hub_return_to_community', {
                    section: document.body.getAttribute('data-hub-section') || 'inicio'
                });
            });
        });

        var appDownloadModal = document.getElementById('hubAppDownloadModal');
        var appDownloadClosers = document.querySelectorAll('[data-close-app-download]');
        var appDownloadOpeners = document.querySelectorAll('[data-open-app-download]');
        var retryAppOpenBtn = document.getElementById('hubRetryAppOpen');
        var webAppPromoModal = document.getElementById('hubWebAppPromo');
        var webAppPromoClosers = document.querySelectorAll('[data-close-web-app-promo]');
        var webAppPromoOpeners = document.querySelectorAll('[data-open-native-app]');
        var platformSwitcher = document.getElementById('hubPlatformSwitcher');
        var portalTransition = document.getElementById('hubPortalTransition');
        var appOpenFallbackTimer = null;
        var appOpenHideHandlerBound = false;
        var portalTransitionResetTimer = null;
        var webAppPromoStorageKey = HUB_WEB_APP_PROMO.userId
            ? 'rr_web_app_promo_seen_' + HUB_WEB_APP_PROMO.userId
            : 'rr_web_app_promo_seen_guest';

        function currentHubSection() {
            return document.body.getAttribute('data-hub-section') || 'inicio';
        }

        function currentHubBrowserUrl() {
            try {
                var url = new URL(window.location.href);
                url.searchParams.delete('app');
                url.searchParams.delete('source');
                url.searchParams.delete('platform');
                return url.toString();
            } catch (e) {
                return window.location.href;
            }
        }

        function currentHubAppUrl() {
            var section = currentHubSection();
            var params = new URLSearchParams();
            params.set('tab', section);
            params.set('source', 'web');
            try {
                params.set('return_url', currentHubBrowserUrl());
            } catch (e) {}
            return 'reiapp://hub?' + params.toString();
        }

        function openAppDownloadModal(mode) {
            if (!appDownloadModal) {
                return;
            }
            resetPortalTransition();
            appDownloadModal.classList.toggle('is-download-mode', mode === 'download');
            appDownloadModal.classList.add('is-open');
            appDownloadModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        }

        function closeAppDownloadModal() {
            if (!appDownloadModal) {
                return;
            }
            appDownloadModal.classList.remove('is-open');
            appDownloadModal.classList.remove('is-download-mode');
            appDownloadModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }

        function openWebAppPromoModal() {
            if (!webAppPromoModal) {
                return;
            }
            webAppPromoModal.classList.add('is-open');
            webAppPromoModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        }

        function closeWebAppPromoModal() {
            if (!webAppPromoModal) {
                return;
            }
            webAppPromoModal.classList.remove('is-open');
            webAppPromoModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }

        function markWebAppPromoSeen() {
            try {
                localStorage.setItem(webAppPromoStorageKey, '1');
            } catch (e) {}
        }

        function hasSeenWebAppPromo() {
            try {
                return localStorage.getItem(webAppPromoStorageKey) === '1';
            } catch (e) {
                return false;
            }
        }

        function maybeOpenWebAppPromo() {
            if (!HUB_WEB_APP_PROMO.enabled || HUB_APP_CONTEXT.isApp || !webAppPromoModal) {
                return;
            }

            if (hasSeenWebAppPromo()) {
                return;
            }

            markWebAppPromoSeen();
            window.setTimeout(function() {
                if (!document.body.classList.contains('rr-modal-open')) {
                    openWebAppPromoModal();
                }
            }, 900);
        }

        function resetPortalTransition() {
            if (!portalTransition) {
                return;
            }
            if (portalTransitionResetTimer) {
                clearTimeout(portalTransitionResetTimer);
                portalTransitionResetTimer = null;
            }
            portalTransition.classList.remove('is-active');
        }

        function runPortalTransition(callback) {
            if (!portalTransition) {
                if (typeof callback === 'function') {
                    callback();
                }
                return;
            }

            resetPortalTransition();
            portalTransition.offsetWidth;
            portalTransition.classList.add('is-active');

            window.setTimeout(function() {
                if (typeof callback === 'function') {
                    callback();
                }
            }, 180);

            portalTransitionResetTimer = window.setTimeout(resetPortalTransition, 460);
        }

        function attemptOpenNativeApp() {
            var deepLink = currentHubAppUrl();
            if (!deepLink) {
                openAppDownloadModal();
                return;
            }

            if (appOpenFallbackTimer) {
                clearTimeout(appOpenFallbackTimer);
            }

            if (!appOpenHideHandlerBound) {
                appOpenHideHandlerBound = true;
                window.addEventListener('pagehide', function() {
                    if (appOpenFallbackTimer) {
                        clearTimeout(appOpenFallbackTimer);
                    }
                    closeAppDownloadModal();
                });
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden && appOpenFallbackTimer) {
                        clearTimeout(appOpenFallbackTimer);
                    }
                });
            }

            window.location.href = deepLink;
            appOpenFallbackTimer = window.setTimeout(function() {
                if (!document.hidden) {
                    openAppDownloadModal();
                }
            }, 1300);
        }

        function openCurrentHubInBrowser() {
            var browserUrl = currentHubBrowserUrl();
            if (HUB_APP_CONTEXT.isApp) {
                postNativeMessage('hub_open_external_url', {
                    url: browserUrl,
                    section: currentHubSection()
                });
                return;
            }

            window.location.href = browserUrl;
        }

        appDownloadClosers.forEach(function(btn) {
            btn.addEventListener('click', closeAppDownloadModal);
        });

        appDownloadOpeners.forEach(function(btn) {
            btn.addEventListener('click', function() {
                openAppDownloadModal('download');
            });
        });

        webAppPromoClosers.forEach(function(btn) {
            btn.addEventListener('click', closeWebAppPromoModal);
        });

        webAppPromoOpeners.forEach(function(btn) {
            btn.addEventListener('click', function(event) {
                event.preventDefault();
                closeWebAppPromoModal();
                runPortalTransition(function() {
                    attemptOpenNativeApp();
                });
            });
        });

        if (retryAppOpenBtn) {
            retryAppOpenBtn.addEventListener('click', function() {
                closeAppDownloadModal();
                attemptOpenNativeApp();
            });
        }

        if (platformSwitcher) {
            platformSwitcher.querySelectorAll('[data-platform-target]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var target = btn.getAttribute('data-platform-target');
                    if (target === 'app') {
                        if (HUB_APP_CONTEXT.isApp) {
                            return;
                        }
                        attemptOpenNativeApp();
                        return;
                    }

                    if (HUB_APP_CONTEXT.isApp) {
                        openCurrentHubInBrowser();
                    }
                });
            });
        }

        maybeOpenWebAppPromo();

        function openProfilePopout(){
            var pop = document.getElementById('hubProfilePopout');
            if (!pop) return;
            pop.classList.add('is-open');
            pop.setAttribute('aria-hidden', 'false');
        }

        function resetProfilePopoutForm(){
            var form = document.getElementById('hubProfileForm');
            var alertBox = document.getElementById('hubProfileAlert');
            var imageInput = document.getElementById('hubProfileImage');
            var img = document.getElementById('hubProfileAvatar');
            var placeholder = document.getElementById('hubProfileAvatarPlaceholder');

            if (form) {
                form.reset();
            }

            if (alertBox) {
                alertBox.style.display = 'none';
                alertBox.innerHTML = '';
            }

            if (imageInput) {
                imageInput.value = '';
            }

            if (img) {
                var initialSrc = img.dataset.initialSrc || '';
                var hadImage = img.dataset.hadImage === '1';

                if (hadImage && initialSrc) {
                    img.src = initialSrc;
                    img.style.display = '';
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                } else {
                    img.removeAttribute('src');
                    img.style.display = 'none';
                    if (placeholder) {
                        placeholder.style.display = '';
                    }
                }
            }
        }

        function closeProfilePopout(){
            var pop = document.getElementById('hubProfilePopout');
            if (!pop) return;
            resetProfilePopoutForm();
            pop.classList.remove('is-open');
            pop.setAttribute('aria-hidden', 'true');
        }

        var pendingProfileSection = null;

        function queueProfileSection(sectionName){
            pendingProfileSection = sectionName || 'perfil';
            window.RR_PENDING_PROFILE_SECTION = pendingProfileSection;
            window.RR_ACTIVE_PROFILE_SECTION = pendingProfileSection;
        }

        function clearProfileSectionQueue(){
            pendingProfileSection = null;
            window.RR_PENDING_PROFILE_SECTION = null;
        }

        function flushProfileSection(){
            if (!pendingProfileSection || typeof window.switchToSection !== 'function') {
                return false;
            }

            var targetSection = pendingProfileSection;
            clearProfileSectionQueue();
            window.switchToSection(targetSection);
            window.RR_ACTIVE_PROFILE_SECTION = targetSection;
            return true;
        }

        function openProfileTarget(sectionName){
            queueProfileSection(sectionName || 'perfil');
            openProfilePopout();

            if (flushProfileSection()) {
                return;
            }

            window.setTimeout(flushProfileSection, 60);
            window.setTimeout(flushProfileSection, 180);
            window.setTimeout(flushProfileSection, 360);
        }
        window.openProfileTarget = openProfileTarget;

        function showProfilePopoutAlert(message, tone){
            var alertBox = document.getElementById('hubProfileAlert');
            if (!alertBox) return;

            alertBox.style.display = 'block';
            alertBox.classList.remove('is-error', 'is-success', 'is-info');
            alertBox.classList.add(tone === 'error' ? 'is-error' : (tone === 'success' ? 'is-success' : 'is-info'));
            alertBox.innerHTML = message || '';
        }

        function openProfileTargetWithAlert(sectionName, message, tone){
            openProfileTarget(sectionName || 'perfil');
            window.setTimeout(function(){
                showProfilePopoutAlert(message, tone);
            }, 80);
            window.setTimeout(function(){
                showProfilePopoutAlert(message, tone);
            }, 220);
        }

        window.openProfileTargetWithAlert = openProfileTargetWithAlert;

        $(document).on('click', '.hub-open-profile', function(){
            openProfileTarget('perfil');
        });

        $(document).on('click', '[data-close-profile]', function(){
            closeProfilePopout();
        });

        $(document).on('keydown', function(e){
            if (e.key === 'Escape') {
                closeProfilePopout();
            }
        });

        $(document).on('click', '[data-premium-link]', function(){
            // Let navigation happen, but close the popout for a cleaner transition.
            closeProfilePopout();
        });

        var imageInput = document.getElementById('hubProfileImage');
        if (imageInput) {
            imageInput.addEventListener('change', function(){
                var file = this.files && this.files[0];
                if (!file) return;

                var img = document.getElementById('hubProfileAvatar');
                var placeholder = document.getElementById('hubProfileAvatarPlaceholder');
                var reader = new FileReader();
                reader.onload = function(evt){
                    if (img) {
                        img.src = evt.target.result;
                        img.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        // ✅ Máscaras para CPF e Data de Nascimento
        var cpfInput = document.getElementById('hubProfileCpf');
        if (cpfInput && !cpfInput.disabled) {
            cpfInput.addEventListener('input', function(e){
                var value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
                
                if (value.length <= 11) {
                    // Aplica máscara: 000.000.000-00
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                }
                
                e.target.value = value;
            });
        }

        var birthdateInput = document.getElementById('hubProfileBirthdate');
        if (birthdateInput && !birthdateInput.disabled) {
            birthdateInput.addEventListener('input', function(e){
                var value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
                
                if (value.length <= 8) {
                    // Aplica máscara: DD/MM/AAAA
                    value = value.replace(/(\d{2})(\d)/, '$1/$2');
                    value = value.replace(/(\d{2})(\d)/, '$1/$2');
                }
                
                e.target.value = value;
            });
        }

        var profileForm = document.getElementById('hubProfileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', async function(e){
                e.preventDefault();

                var alertBox = document.getElementById('hubProfileAlert');
                var submitBtn = document.getElementById('hubProfileSubmit');
                if (alertBox) {
                    alertBox.style.display = 'none';
                    alertBox.innerHTML = '';
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Salvando...';
                }

                try {
                    var formData = new FormData(profileForm);
                    var usernameValue = (formData.get('username') || '').toString().trim();
                    var usernameConfirmationValue = (formData.get('username_confirmation') || '').toString().trim();
                    var currentUsernameValue = '{{ trim((string) ($hubUser->username ?? '')) }}';

                    if (!usernameValue || usernameValue === currentUsernameValue) {
                        formData.delete('username_confirmation');
                    } else {
                        formData.set('username', usernameValue);
                        formData.set('username_confirmation', usernameConfirmationValue);
                    }
                    
                    // ✅ Converter data DD/MM/AAAA para YYYY-MM-DD antes de enviar
                    var birthdateValue = formData.get('birthdate');
                    if (birthdateValue && birthdateValue.includes('/')) {
                        var parts = birthdateValue.split('/');
                        if (parts.length === 3) {
                            // DD/MM/AAAA -> YYYY-MM-DD
                            var formattedDate = parts[2] + '-' + parts[1] + '-' + parts[0];
                            formData.set('birthdate', formattedDate);
                        }
                    }
                    
                    // ✅ Remover pontos e traços do CPF antes de enviar
                    var cpfValue = formData.get('cpf');
                    if (cpfValue) {
                        formData.set('cpf', cpfValue.replace(/\D/g, ''));
                    }
                    
                    var response = await fetch(profileForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    var data = null;
                    var contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        var text = await response.text();
                        data = { success: false, errors: ['Resposta inesperada do servidor.'] };
                        // If CSRF expired, Laravel commonly returns 419 HTML.
                        if (response.status === 419) {
                            data.errors = ['Sessão expirada. Recarregue a página e tente novamente.'];
                        }
                    }

                    if (!response.ok || !data.success) {
                        var errors = (data && data.errors) ? data.errors : ['Não foi possível atualizar o perfil.'];
                        if (alertBox) {
                            alertBox.style.display = 'block';
                            alertBox.innerHTML = '<strong>Não foi possível salvar:</strong><br>' + errors.map(function(x){ return '• ' + x; }).join('<br>');
                        }
                        return;
                    }

                    if (alertBox) {
                        alertBox.style.display = 'block';
                        alertBox.innerHTML = '<strong>OK:</strong> ' + (data.message || 'Perfil atualizado.');
                    }

                    // Lock fields that were just saved (as per backend response)
                    if (data.locked) {
                        Object.keys(data.locked).forEach(function(key){
                            if (!data.locked[key]) return;
                            var input = profileForm.querySelector('[name="' + key + '"]');
                            var conf = profileForm.querySelector('[name="' + key + '_confirmation"]');
                            if (input) input.disabled = true;
                            if (conf) conf.disabled = true;
                        });
                    }

                    // Update hub displayed username
                    // (hub top no longer shows the username card)

                    // Update avatar if returned
                    if (data.user && data.user.avatar) {
                        var img = document.getElementById('hubProfileAvatar');
                        var placeholder = document.getElementById('hubProfileAvatarPlaceholder');
                        if (img) {
                            img.src = data.user.avatar;
                            img.style.display = 'block';
                        }
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    }

                    if (window.RR_RODEIO_REMINDER_PREFILL) {
                        var savedEmail = (formData.get('email') || '').toString().trim().toLowerCase();
                        window.RR_RODEIO_REMINDER_PREFILL.email = savedEmail;
                        window.RR_RODEIO_REMINDER_PREFILL.hasRealEmail = savedEmail !== '' && !savedEmail.endsWith('@cadastro.local');
                    }
                } catch (err) {
                    if (alertBox) {
                        alertBox.style.display = 'block';
                        alertBox.innerHTML = '<strong>Erro:</strong> Falha ao enviar dados. Tente novamente.';
                    }
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Salvar';
                    }
                }
            });
        }

        $.ajaxSetup({ headers: { 'X-Requested-With': 'XMLHttpRequest' } });

        // Bolão (equipes) front-end: API-driven cards + popout.
        // This must live in the hub page because sections are injected via AJAX (scripts inside partials won't execute).
        window.RRFantasy = window.RRFantasy || (function(){
            var BASE_PATH = @json(rtrim(request()->getBaseUrl(), '/'));
            var BASE_URL = (window.location && window.location.origin ? window.location.origin : '') + BASE_PATH;
            var PUBLIC_STORAGE_BASE = @json(rtrim(publicStorageBaseUrl(), '/'));

            function apiUrl(path){
                if (!path) return BASE_URL;
                var p = String(path);
                if (/^https?:\/\//i.test(p)) return p;
                if (p.charAt(0) === '/') return BASE_URL + p;
                return BASE_URL + '/' + p;
            }

            function imgUrl(path){
                if (!path) return '';
                var p = String(path);
                if (/^https?:\/\//i.test(p)) return p;
                if (p.charAt(0) === '/') return BASE_URL + p;
                return BASE_URL + '/' + p;
            }

            function competitorPhotoPath(value){
                var v = (value === null || value === undefined) ? '' : String(value);
                v = v.trim();
                if (!v) return null; // Sem fallback
                if (/^https?:\/\//i.test(v)) return v;
                v = v.replace(/\\/g, '/');
                if (v.charAt(0) === '/') return v;
                if (v.toLowerCase().indexOf('storage/') === 0) return PUBLIC_STORAGE_BASE + '/' + v.substring('storage/'.length);
                if (v.toLowerCase().indexOf('competitors/') === 0) return PUBLIC_STORAGE_BASE + '/' + v;
                if (v.toLowerCase().indexOf('public/competitors/') === 0) return PUBLIC_STORAGE_BASE + '/' + v.substring('public/'.length);
                if (v.indexOf('/') === -1) return PUBLIC_STORAGE_BASE + '/competitors/' + v;
                return '/' + v.replace(/^\/+/, '');
            }

            function competitorPhotoUrl(c){
                if (!c) return imgUrl(null); // Sem fallback
                var direct = c.foto_url || c.fotoUrl || '';
                var p = competitorPhotoPath(direct || c.foto || '');
                return imgUrl(p);
            }

            var state = {
                leagues: [],
                selectedTier: 'Todos',
                modalLeagueId: null,
                showFull: false,
                modalEl: null,
                modalInstance: null,
                styleInstalled: false,
                bound: false,
                lastLoadedAt: null,
                scrollLock: {
                    active: false,
                    y: 0,
                    prev: null,
                },
                builder: {
                    loaded: false,
                    competitors: [],
                    selectedIds: [], // ordered; first is captain
                    verifiedOk: false,
                    verifiedSig: null,
                    filter: '',
                }
            };

            function installStyleOnce(){
                if (state.styleInstalled) return;
                state.styleInstalled = true;
                var styleId = 'rrFantasyInjectedStyle';
                if (document.getElementById(styleId)) return;
                var css = `
                    /* ============================================
                       FANTASY MODAL - REI DO RODEIO THEME
                       ============================================ */
                    body.rr-modal-open{ overflow:hidden; }
                    .rr-fantasy-modal{ display:none; position:fixed; inset:0; z-index:9999; overscroll-behavior: contain; }
                    .rr-fantasy-modal.rr-fantasy-modal--open{ display:block; }
                    .rr-fantasy-modal::before{
                        content:'';
                        position:absolute;
                        inset:0;
                        background:
                            radial-gradient(circle at 16% 10%, rgba(249, 115, 22, .28), transparent 24%),
                            radial-gradient(circle at 84% 12%, rgba(59, 130, 246, .24), transparent 26%),
                            linear-gradient(180deg, rgba(0,0,0,.88), rgba(2,6,23,.94));
                        backdrop-filter: blur(10px);
                    }
                    .rr-fantasy-modal .modal-dialog{ position:relative; z-index:1; width:min(96vw, 940px); margin:3vh auto; }
                    .rr-fantasy-modal .modal-content{
                        max-height:94vh;
                        overflow:hidden;
                        border-radius:26px;
                        display:flex;
                        flex-direction:column;
                        background:
                            radial-gradient(circle at top left, rgba(249, 115, 22, 0.15), transparent 26%),
                            radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 28%),
                            linear-gradient(180deg, #050608 0%, #090b12 42%, #0a0d14 100%);
                        border: 1px solid rgba(255, 255, 255, 0.08);
                        box-shadow:
                            0 30px 84px rgba(0,0,0,0.62),
                            0 0 0 1px rgba(249, 115, 22, 0.08),
                            inset 0 1px 0 rgba(255,255,255,0.05);
                    }
                    .rr-fantasy-modal .modal-body{
                        overflow:auto;
                        -webkit-overflow-scrolling: touch;
                        overscroll-behavior: contain;
                        flex:1 1 auto;
                        min-height:0;
                        padding: 1rem 1rem 1.1rem;
                        background: linear-gradient(180deg, rgba(3,7,18,.56), rgba(3,7,18,.8));
                    }

                    .rr-fantasy-modal .modal-header{
                        padding: 1rem 1.15rem;
                        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                        background: linear-gradient(135deg, rgba(249, 115, 22, 0.14), rgba(59, 130, 246, 0.12));
                        backdrop-filter: blur(14px);
                    }
                    .rr-fantasy-modal .modal-title{
                        font-weight: 900;
                        font-size: 1.32rem;
                        background: linear-gradient(135deg, #ffffff 0%, #ffe4d4 28%, #93c5fd 72%, #f97316 100%);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        background-clip: text;
                    }
                    .rr-fantasy-modal .text-muted{ color: rgba(226, 232, 240, 0.8) !important; }

                    .rr-fantasy-close{
                        appearance:none;
                        border: 1px solid rgba(255,255,255,.14) !important;
                        background: rgba(2,6,23,.72) !important;
                        color:#fff !important;
                        width:44px !important;
                        height:44px !important;
                        border-radius:50% !important;
                        display:inline-flex !important;
                        align-items:center !important;
                        justify-content:center !important;
                        cursor:pointer !important;
                        font-size:1.2rem !important;
                        transition: all .3s ease !important;
                        opacity: 1 !important;
                        padding: 0 !important;
                        background-image: none !important;
                        position: relative;
                        z-index: 10;
                    }
                    .rr-fantasy-close:hover{
                        background: linear-gradient(135deg, rgba(249,115,22,.22), rgba(59,130,246,.18)) !important;
                        border-color: rgba(249,115,22,.52) !important;
                        transform: rotate(8deg) translateY(-1px);
                    }
                    .rr-fantasy-close:focus{
                        outline: none !important;
                        box-shadow: 0 0 0 3px rgba(59,130,246,.42) !important;
                    }

                    .rr-fantasy-modal__meta{ display:flex; gap:.65rem; flex-wrap:wrap; margin-bottom:1rem; padding:0 .25rem; }
                    .rr-fantasy-pill{
                        display:inline-flex;
                        align-items:center;
                        gap:.5rem;
                        padding:.4rem .9rem;
                        border-radius:999px;
                        background: linear-gradient(135deg, rgba(249,115,22,.16), rgba(59,130,246,.14));
                        border: 1px solid rgba(255,255,255,.12);
                        color: #fff7ed;
                        font-size:.8rem;
                        font-weight: 600;
                        backdrop-filter: blur(8px);
                    }
                    .rr-fantasy-pill strong{ color: #ffffff; }

                    .rr-fantasy-tabs{
                        display: flex;
                        gap: .5rem;
                        margin-bottom: 1rem;
                        padding: 4px;
                        background: rgba(2, 6, 23, 0.72);
                        border-radius: 16px;
                        border: 1px solid rgba(255, 255, 255, 0.08);
                    }
                    .rr-fantasy-tab{
                        flex: 1;
                        padding: .7rem 1rem;
                        border: none;
                        background: transparent;
                        color: rgba(226, 232, 240, 0.72);
                        font-weight: 700;
                        font-size: .85rem;
                        border-radius: 10px;
                        cursor: pointer;
                        transition: all .3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: .5rem;
                    }
                    .rr-fantasy-tab:hover{
                        background: rgba(249, 115, 22, 0.12);
                        color: #fff;
                    }
                    .rr-fantasy-tab--active{
                        background: linear-gradient(135deg, #f97316, #3b82f6) !important;
                        color: #fff !important;
                        box-shadow: 0 12px 22px rgba(59, 130, 246, 0.26);
                    }
                    .rr-fantasy-tab i{ font-size: .9rem; }
                    @media (max-width: 600px){
                        .rr-fantasy-tabs{ margin:0 0 1rem; padding:4px; }
                        .rr-fantasy-tab{ padding: .55rem .5rem; font-size: .75rem; gap: .35rem; }
                        .rr-fantasy-tab i{ font-size: .8rem; }
                    }

                    .rr-fantasy-panel{ display: none; }
                    .rr-fantasy-panel--active{ display: block; animation: panelFadeIn .3s ease; }
                    @keyframes panelFadeIn{ from{ opacity:0; transform: translateY(10px); } to{ opacity:1; transform: translateY(0); } }

                    .rr-fantasy-panel__header{
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:.75rem;
                        margin-bottom:.75rem;
                        padding-bottom: .75rem;
                        border-bottom: 1px solid rgba(249, 115, 22, 0.12);
                    }
                    .rr-fantasy-panel__title{ font-weight: 900; margin:0; font-size: 1.1rem; color: #f8fafc; }
                    .rr-fantasy-panel__actions{ display:flex; gap:.5rem; flex-wrap:wrap; }

                    .rr-fantasy-btn{
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:.4rem;
                        padding:.5rem .85rem;
                        border-radius:12px;
                        border:1px solid rgba(255,255,255,.12);
                        background: linear-gradient(135deg, rgba(249,115,22,.14), rgba(59,130,246,.12));
                        color:#fff;
                        font-weight:700;
                        font-size:.85rem;
                        cursor:pointer;
                        transition: all .3s ease;
                    }
                    .rr-fantasy-btn:hover{
                        background: linear-gradient(135deg, rgba(249,115,22,.22), rgba(59,130,246,.18));
                        border-color: rgba(249,115,22,.32);
                        transform: translateY(-2px);
                    }
                    .rr-fantasy-btn--primary{
                        background: linear-gradient(135deg, #f97316, #2563eb);
                        border-color: transparent;
                        color: #fff;
                        box-shadow: 0 8px 18px rgba(249, 115, 22, 0.22);
                    }
                    .rr-fantasy-btn--primary:hover{
                        background: linear-gradient(135deg, #fb923c, #3b82f6);
                        box-shadow: 0 10px 24px rgba(59, 130, 246, 0.28);
                    }
                    .rr-fantasy-btn--ghost{ background: rgba(2,6,23,.42); border-color: rgba(255,255,255,.12); }

                    .rr-fantasy-ranking-shell{
                        display:grid;
                        gap:1rem;
                        color:#f8fafc;
                    }

                    .rr-fantasy-ranking-hero{
                        position:relative;
                        overflow:hidden;
                        padding:1rem;
                        border-radius:28px;
                        border:1px solid rgba(255,255,255,.08);
                        background:
                            radial-gradient(circle at top left, rgba(249,115,22,.24), transparent 32%),
                            radial-gradient(circle at top right, rgba(59,130,246,.2), transparent 28%),
                            linear-gradient(180deg, rgba(15,23,42,.96), rgba(3,7,18,.98));
                        box-shadow:
                            0 20px 40px rgba(0,0,0,.28),
                            inset 0 1px 0 rgba(255,255,255,.05);
                    }

                    .rr-fantasy-ranking-hero::before,
                    .rr-fantasy-ranking-hero::after{
                        content:'';
                        position:absolute;
                        pointer-events:none;
                        border-radius:999px;
                        filter: blur(24px);
                        opacity:.8;
                    }

                    .rr-fantasy-ranking-hero::before{
                        top:-38px;
                        left:8%;
                        width:160px;
                        height:160px;
                        background: radial-gradient(circle, rgba(249,115,22,.26), rgba(249,115,22,0));
                    }

                    .rr-fantasy-ranking-hero::after{
                        right:-30px;
                        bottom:-16px;
                        width:180px;
                        height:180px;
                        background: radial-gradient(circle, rgba(59,130,246,.2), rgba(59,130,246,0));
                    }

                    .rr-fantasy-ranking-hero__topline{
                        position:relative;
                        z-index:1;
                        display:flex;
                        align-items:center;
                        justify-content:space-between;
                        gap:.5rem;
                        flex-wrap:wrap;
                        margin-bottom:.85rem;
                    }

                    .rr-fantasy-ranking-badge{
                        display:inline-flex;
                        align-items:center;
                        gap:.45rem;
                        min-height:32px;
                        padding:0 .9rem;
                        border-radius:999px;
                        border:1px solid rgba(255,255,255,.12);
                        background: linear-gradient(135deg, rgba(249,115,22,.18), rgba(59,130,246,.14));
                        color:#fff7ed;
                        font-size:.72rem;
                        font-weight:900;
                        letter-spacing:.12em;
                        text-transform:uppercase;
                        box-shadow:0 10px 22px rgba(0,0,0,.18);
                    }

                    .rr-fantasy-ranking-badge--alt{
                        background: rgba(255,255,255,.06);
                        color:#f8fafc;
                    }

                    .rr-fantasy-ranking-hero__grid{
                        position:relative;
                        z-index:1;
                        display:grid;
                        grid-template-columns:minmax(0,1.1fr) minmax(0,.95fr);
                        gap:1rem;
                        align-items:center;
                    }

                    .rr-fantasy-ranking-hero__copy{
                        display:grid;
                        gap:.75rem;
                    }

                    .rr-fantasy-ranking-hero__eyebrow{
                        margin:0;
                        color:#f97316;
                        font-size:.74rem;
                        font-weight:900;
                        letter-spacing:.18em;
                        text-transform:uppercase;
                    }

                    .rr-fantasy-ranking-hero__title{
                        margin:0;
                        font-size:clamp(1.32rem, 2.6vw, 2.12rem);
                        line-height:1.02;
                        font-weight:900;
                        letter-spacing:-.05em;
                        color:#fff;
                    }

                    .rr-fantasy-ranking-hero__copy p{
                        margin:0;
                        color:rgba(226,232,240,.82);
                        font-size:.94rem;
                        line-height:1.65;
                    }

                    .rr-fantasy-ranking-stats{
                        display:grid;
                        grid-template-columns:repeat(2,minmax(0,1fr));
                        gap:.68rem;
                    }

                    .rr-fantasy-ranking-stat{
                        display:grid;
                        gap:.15rem;
                        min-height:72px;
                        padding:.82rem .9rem;
                        border-radius:18px;
                        border:1px solid rgba(255,255,255,.08);
                        background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.03));
                        box-shadow: inset 0 1px 0 rgba(255,255,255,.04);
                    }

                    .rr-fantasy-ranking-stat span{
                        color:rgba(226,232,240,.66);
                        font-size:.65rem;
                        font-weight:900;
                        letter-spacing:.12em;
                        text-transform:uppercase;
                    }

                    .rr-fantasy-ranking-stat strong{
                        color:#fff;
                        font-size:1rem;
                        font-weight:900;
                        line-height:1.2;
                    }

                    .rr-fantasy-ranking-podium{
                        display:grid;
                        grid-template-columns:repeat(3,minmax(0,1fr));
                        gap:.75rem;
                        align-items:end;
                    }

                    .rr-fantasy-podium-card{
                        position:relative;
                        display:flex;
                        flex-direction:column;
                        gap:.72rem;
                        min-height:240px;
                        padding:.9rem .85rem 1rem;
                        border-radius:24px;
                        border:1px solid rgba(255,255,255,.08);
                        overflow:hidden;
                        background: linear-gradient(180deg, rgba(30,41,59,.72), rgba(2,6,23,.94));
                        box-shadow: 0 16px 32px rgba(0,0,0,.24);
                    }

                    .rr-fantasy-podium-card::before{
                        content:'';
                        position:absolute;
                        inset:0;
                        background: linear-gradient(180deg, rgba(255,255,255,.05), transparent 38%, rgba(255,255,255,.02));
                        pointer-events:none;
                    }

                    .rr-fantasy-podium-card::after{
                        content:'';
                        position:absolute;
                        left:12px;
                        right:12px;
                        bottom:10px;
                        height:8px;
                        border-radius:999px;
                        background: linear-gradient(90deg, rgba(255,255,255,.05), var(--rr-fantasy-podium-accent, rgba(249,115,22,.7)), rgba(59,130,246,.78));
                        opacity:.95;
                    }

                    .rr-fantasy-podium-card--champion{
                        min-height:282px;
                        transform: translateY(-8px);
                        border-color: rgba(249,115,22,.34);
                        background:
                            radial-gradient(circle at top, rgba(249,115,22,.18), transparent 38%),
                            radial-gradient(circle at bottom right, rgba(59,130,246,.12), transparent 30%),
                            linear-gradient(180deg, rgba(249,115,22,.16), rgba(2,6,23,.94));
                        box-shadow:
                            0 26px 42px rgba(0,0,0,.34),
                            0 0 0 1px rgba(249,115,22,.12),
                            0 0 28px rgba(59,130,246,.14);
                    }

                    .rr-fantasy-podium-card--silver{ --rr-fantasy-podium-accent: rgba(59,130,246,.8); }
                    .rr-fantasy-podium-card--bronze{ --rr-fantasy-podium-accent: rgba(249,115,22,.72); }

                    .rr-fantasy-podium-card--mine{
                        border-color: rgba(249,115,22,.42);
                        box-shadow:
                            0 24px 38px rgba(0,0,0,.3),
                            0 0 0 1px rgba(59,130,246,.12),
                            0 0 24px rgba(249,115,22,.12);
                    }

                    .rr-fantasy-podium-card__medal,
                    .rr-fantasy-podium-card__badge{
                        position:relative;
                        z-index:1;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        width:42px;
                        height:42px;
                        border-radius:14px;
                        border:1px solid rgba(255,255,255,.14);
                        background: rgba(255,255,255,.06);
                        box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
                    }

                    .rr-fantasy-podium-card__medal{
                        position:absolute;
                        top:.85rem;
                        left:.85rem;
                        font-size:1.05rem;
                    }

                    .rr-fantasy-podium-card__badge{
                        position:absolute;
                        top:.85rem;
                        right:.85rem;
                        width:auto;
                        min-height:32px;
                        padding:0 .72rem;
                        border-radius:999px;
                        color:#fff7ed;
                        font-size:.66rem;
                        font-weight:900;
                        letter-spacing:.14em;
                        text-transform:uppercase;
                    }

                    .rr-fantasy-podium-card__avatar,
                    .rr-fantasy-ranking-row-v2__avatar{
                        position:relative;
                        z-index:1;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        overflow:hidden;
                    }

                    .rr-fantasy-podium-card__avatar{
                        width:92px;
                        height:92px;
                        margin:1.6rem auto 0;
                        border-radius:28px;
                        padding:5px;
                        border:1px solid rgba(255,255,255,.12);
                        background: linear-gradient(135deg, rgba(249,115,22,.32), rgba(59,130,246,.22));
                        box-shadow:
                            0 16px 30px rgba(0,0,0,.28),
                            0 0 0 10px rgba(255,255,255,.03);
                    }

                    .rr-fantasy-podium-card--champion .rr-fantasy-podium-card__avatar{
                        width:108px;
                        height:108px;
                    }

                    .rr-fantasy-podium-card__avatar img,
                    .rr-fantasy-ranking-row-v2__avatar img{
                        width:100%;
                        height:100%;
                        object-fit:cover;
                        object-position:center top;
                        display:block;
                    }

                    .rr-fantasy-podium-card__avatar span,
                    .rr-fantasy-ranking-row-v2__avatar span{
                        width:100%;
                        height:100%;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        border-radius:inherit;
                        color:#fff;
                        font-weight:900;
                        letter-spacing:.1em;
                        background: linear-gradient(135deg, rgba(249,115,22,.55), rgba(59,130,246,.45));
                    }

                    .rr-fantasy-podium-card__avatar img{ border-radius:22px; }
                    .rr-fantasy-ranking-row-v2__avatar img{ border-radius:16px; }

                    .rr-fantasy-podium-card__rank{
                        position:relative;
                        z-index:1;
                        text-align:center;
                        color:rgba(226,232,240,.7);
                        font-size:.7rem;
                        font-weight:900;
                        letter-spacing:.18em;
                        text-transform:uppercase;
                    }

                    .rr-fantasy-podium-card__name{
                        position:relative;
                        z-index:1;
                        min-height:42px;
                        margin:0;
                        color:#fff;
                        font-size:1rem;
                        font-weight:900;
                        line-height:1.16;
                        text-align:center;
                        display:-webkit-box;
                        -webkit-box-orient:vertical;
                        -webkit-line-clamp:2;
                        overflow:hidden;
                    }

                    .rr-fantasy-podium-card__meta{
                        position:relative;
                        z-index:1;
                        display:grid;
                        gap:.48rem;
                        margin-top:auto;
                    }

                    .rr-fantasy-podium-card__chip{
                        display:flex;
                        align-items:center;
                        justify-content:space-between;
                        gap:.5rem;
                        min-height:38px;
                        padding:.58rem .72rem;
                        border-radius:16px;
                        border:1px solid rgba(255,255,255,.08);
                        background: rgba(255,255,255,.05);
                    }

                    .rr-fantasy-podium-card__chip span{
                        color:rgba(226,232,240,.66);
                        font-size:.62rem;
                        font-weight:900;
                        letter-spacing:.14em;
                        text-transform:uppercase;
                    }

                    .rr-fantasy-podium-card__chip strong{
                        color:#fff;
                        font-size:.88rem;
                        font-weight:900;
                        white-space:nowrap;
                    }

                    .rr-fantasy-ranking-list-wrap{
                        position:relative;
                        display:grid;
                        gap:.7rem;
                    }

                    .rr-fantasy-ranking-list-header{
                        display:flex;
                        justify-content:space-between;
                        align-items:flex-end;
                        gap:1rem;
                        padding:.2rem .15rem;
                    }

                    .rr-fantasy-ranking-list-header__title{
                        margin:0;
                        color:#fff;
                        font-size:1rem;
                        font-weight:900;
                    }

                    .rr-fantasy-ranking-list-header__meta{
                        color:rgba(226,232,240,.72);
                        font-size:.8rem;
                        line-height:1.35;
                    }

                    .rr-fantasy-ranking-list{
                        display:grid;
                        gap:.65rem;
                        max-height:430px;
                        overflow:auto;
                        padding-right:.2rem;
                        scrollbar-width:none;
                    }

                    .rr-fantasy-ranking-list::-webkit-scrollbar{ display:none; }

                    .rr-fantasy-ranking-row-v2{
                        position:relative;
                        display:grid;
                        grid-template-columns: 50px 44px minmax(0,1fr) auto 18px;
                        gap:.75rem;
                        align-items:center;
                        padding:.85rem .9rem;
                        border-radius:22px;
                        border:1px solid rgba(255,255,255,.08);
                        background: linear-gradient(180deg, rgba(15,23,42,.9), rgba(3,7,18,.96));
                        box-shadow: 0 12px 24px rgba(0,0,0,.18);
                        overflow:hidden;
                    }

                    .rr-fantasy-ranking-row-v2::before{
                        content:'';
                        position:absolute;
                        inset:0;
                        pointer-events:none;
                        background: linear-gradient(90deg, rgba(249,115,22,.12), transparent 38%, rgba(59,130,246,.08));
                        opacity:.85;
                    }

                    .rr-fantasy-ranking-row-v2--gold{ border-color: rgba(249,115,22,.34); box-shadow: 0 18px 28px rgba(0,0,0,.24), 0 0 0 1px rgba(249,115,22,.12); }
                    .rr-fantasy-ranking-row-v2--silver{ border-color: rgba(59,130,246,.28); }
                    .rr-fantasy-ranking-row-v2--bronze{ border-color: rgba(255,255,255,.14); }

                    .rr-fantasy-ranking-row-v2--mine{
                        background:
                            linear-gradient(180deg, rgba(249,115,22,.12), rgba(59,130,246,.1)),
                            linear-gradient(180deg, rgba(15,23,42,.92), rgba(3,7,18,.96));
                        border-color: rgba(249,115,22,.42);
                        box-shadow: 0 18px 30px rgba(0,0,0,.24), 0 0 0 1px rgba(59,130,246,.12);
                    }

                    .rr-fantasy-ranking-row-v2__pos{
                        position:relative;
                        z-index:1;
                        width:50px;
                        height:50px;
                        border-radius:16px;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        font-weight:900;
                        color:#fff;
                        background: rgba(255,255,255,.06);
                        border:1px solid rgba(255,255,255,.08);
                    }

                    .rr-fantasy-ranking-row-v2--gold .rr-fantasy-ranking-row-v2__pos{ background: linear-gradient(135deg, #f97316, #fb923c); }
                    .rr-fantasy-ranking-row-v2--silver .rr-fantasy-ranking-row-v2__pos{ background: linear-gradient(135deg, #3b82f6, #60a5fa); }
                    .rr-fantasy-ranking-row-v2--bronze .rr-fantasy-ranking-row-v2__pos{ background: linear-gradient(135deg, #fff, #e5e7eb); color:#0f172a; }

                    .rr-fantasy-ranking-row-v2__avatar{
                        position:relative;
                        z-index:1;
                        width:44px;
                        height:44px;
                        border-radius:16px;
                        overflow:hidden;
                        border:1px solid rgba(255,255,255,.1);
                        background: rgba(255,255,255,.05);
                    }

                    .rr-fantasy-ranking-row-v2__body{
                        position:relative;
                        z-index:1;
                        min-width:0;
                        display:grid;
                        gap:.35rem;
                    }

                    .rr-fantasy-ranking-row-v2__top{
                        display:flex;
                        align-items:flex-start;
                        justify-content:space-between;
                        gap:.8rem;
                    }

                    .rr-fantasy-ranking-row-v2__name{ color:#fff; font-weight:900; line-height:1.15; font-size:.94rem; }
                    .rr-fantasy-ranking-row-v2__subtitle{ margin-top:.2rem; color: rgba(226,232,240,.72); font-size:.78rem; line-height:1.35; }
                    .rr-fantasy-ranking-row-v2__points{ display:inline-flex; align-items:center; min-height:32px; padding:0 .72rem; border-radius:999px; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.05); color:#fff; font-weight:900; font-size:.78rem; white-space:nowrap; }
                    .rr-fantasy-ranking-row-v2__bar{ height:8px; border-radius:999px; overflow:hidden; background: rgba(255,255,255,.07); }
                    .rr-fantasy-ranking-row-v2__bar span{ display:block; height:100%; border-radius:inherit; background: linear-gradient(90deg, #f97316, #3b82f6); box-shadow: 0 0 14px rgba(59,130,246,.3); }
                    .rr-fantasy-ranking-row-v2__chev{ position:relative; z-index:1; color: rgba(226,232,240,.55); font-size: 1rem; }

                    .rr-fantasy-ranking-empty{
                        position:relative;
                        overflow:hidden;
                        display:grid;
                        gap:.55rem;
                        justify-items:center;
                        padding:1.2rem;
                        text-align:center;
                        border-radius:20px;
                        border:1px dashed rgba(255,255,255,.12);
                        background: linear-gradient(180deg, rgba(15,23,42,.62), rgba(3,7,18,.86));
                        color:rgba(226,232,240,.78);
                    }

                    .rr-fantasy-ranking-empty__badge{
                        display:inline-flex;
                        align-items:center;
                        gap:.5rem;
                        min-height:36px;
                        padding:0 .96rem;
                        border-radius:999px;
                        border:1px solid rgba(255,255,255,.12);
                        background: linear-gradient(135deg, rgba(249,115,22,.16), rgba(59,130,246,.12));
                        color:#fff7ed;
                        font-size:.76rem;
                        font-weight:900;
                        letter-spacing:.08em;
                        text-transform:uppercase;
                    }

                    .rr-fantasy-ranking-empty__title{ color:#fff; font-size:1rem; font-weight:900; letter-spacing:-.03em; }
                    .rr-fantasy-ranking-empty__text{ max-width:420px; color:rgba(226,232,240,.72); font-size:.84rem; line-height:1.55; }

                    @media (max-width: 900px){
                        .rr-fantasy-ranking-hero__grid{ grid-template-columns:1fr; }
                        .rr-fantasy-ranking-podium{ grid-template-columns:repeat(3,minmax(0,1fr)); }
                        .rr-fantasy-podium-card--champion{ transform:none; }
                    }

                    @media (max-width: 720px){
                        .rr-fantasy-modal .modal-dialog{ width:min(100vw, 100%); margin:0; }
                        .rr-fantasy-modal .modal-content{ border-radius:20px; min-height:100vh; }
                        .rr-fantasy-ranking-podium{ grid-template-columns:1fr; }
                        .rr-fantasy-podium-card,
                        .rr-fantasy-podium-card--champion{ min-height:auto; transform:none; }
                        .rr-fantasy-ranking-list{ max-height:360px; }
                        .rr-fantasy-ranking-row-v2{ grid-template-columns: 44px 38px minmax(0,1fr) auto 12px; gap:.6rem; padding:.78rem .8rem; }
                        .rr-fantasy-ranking-row-v2__pos{ width:44px; height:44px; border-radius:14px; }
                        .rr-fantasy-ranking-row-v2__avatar{ width:38px; height:38px; border-radius:14px; }
                    }

                    /* ============================================
                       RANKING - TOP 3 HIGHLIGHT
                       ============================================ */
                    .rr-fantasy-ranking-list{ 
                        display:flex; 
                        flex-direction:column; 
                        gap:.4rem; 
                        max-height: 400px; 
                        overflow-y: auto;
                        /* Esconder scrollbar mas manter funcionalidade */
                        scrollbar-width: none; /* Firefox */
                        -ms-overflow-style: none; /* IE/Edge */
                    }
                    /* Chrome, Safari, Opera */
                    .rr-fantasy-ranking-list::-webkit-scrollbar {
                        display: none;
                    }
                    .rr-fantasy-ranking-row{
                        display:flex;
                        justify-content:space-between;
                        gap:.75rem;
                        padding:.65rem .85rem;
                        border-radius:12px;
                        background: rgba(15, 23, 42, 0.6);
                        border:1px solid rgba(99,102,241,.15);
                        transition: all .2s ease;
                    }
                    .rr-fantasy-ranking-row:hover{
                        background: rgba(99,102,241,.1);
                        border-color: rgba(99,102,241,.3);
                    }
                    .rr-fantasy-ranking-row--gold{
                        background: linear-gradient(135deg, rgba(255,215,0,.15), rgba(255,183,0,.1)) !important;
                        border-color: rgba(255,215,0,.4) !important;
                    }
                    .rr-fantasy-ranking-row--silver{
                        background: linear-gradient(135deg, rgba(192,192,192,.12), rgba(169,169,169,.08)) !important;
                        border-color: rgba(192,192,192,.35) !important;
                    }
                    .rr-fantasy-ranking-row--bronze{
                        background: linear-gradient(135deg, rgba(205,127,50,.12), rgba(184,115,51,.08)) !important;
                        border-color: rgba(205,127,50,.35) !important;
                    }
                    .rr-fantasy-ranking-row__pos{ width:46px; font-weight:900; }
                    .rr-fantasy-ranking-row--gold .rr-fantasy-ranking-row__pos{ color: #ffd700; }
                    .rr-fantasy-ranking-row--silver .rr-fantasy-ranking-row__pos{ color: #c0c0c0; }
                    .rr-fantasy-ranking-row--bronze .rr-fantasy-ranking-row__pos{ color: #cd7f32; }
                    .rr-fantasy-ranking-row__pos{ color: #818cf8; }
                    .rr-fantasy-ranking-row__name{ flex:1; color:#e0e7ff; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
                    .rr-fantasy-ranking-row__pts{ color:#a5b4fc; font-weight:800; }

                    /* My Team Empty */
                    .rr-fantasy-myteam-empty{
                        text-align: center;
                        padding: 3rem 1rem;
                        color: rgba(165,180,252,.6);
                    }
                    .rr-fantasy-myteam-empty i{
                        font-size: 3rem;
                        margin-bottom: 1rem;
                        opacity: .5;
                    }
                    .rr-fantasy-myteam-empty p{
                        margin-bottom: 1.5rem;
                    }

                    /* Fields */
                    .rr-fantasy-field{
                        width:100%;
                        background: rgba(15,23,42,.6);
                        border:1px solid rgba(99,102,241,.3);
                        color:#e0e7ff;
                        border-radius:10px;
                        padding:.6rem .85rem;
                        outline:none;
                        transition: all .3s ease;
                    }
                    .rr-fantasy-field:focus{
                        border-color: rgba(99,102,241,.6);
                        box-shadow: 0 0 0 3px rgba(99,102,241,.15);
                    }
                    .rr-fantasy-field::placeholder{ color: rgba(165,180,252,.5); }
                    .rr-fantasy-help{ color: rgba(165,180,252,.75); font-size:.82rem; }

                    /* Builder slots */
                    .rr-fantasy-slots{ display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:.6rem; }
                    @media (max-width: 520px){ .rr-fantasy-slots{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                    .rr-fantasy-slot{ position:relative; min-height: 156px; display:flex; align-items:center; justify-content:center; padding:.15rem; background: transparent; border: 0; }
                    .rr-fantasy-slot--filled{ background: transparent; border:0; }
                    .rr-fantasy-slot--captain .hex-card__inner{ box-shadow: 0 0 0 3px rgba(250,204,21,.28) inset; }
                    .rr-fantasy-slot__empty{ color: rgba(165,180,252,.65); font-weight: 800; font-size: .85rem; text-align:center; }
                    .rr-fantasy-slot__badge{ position:absolute; left:.55rem; top:.55rem; z-index:6; padding:.18rem .45rem; border-radius:999px; font-size:.7rem; font-weight:900; letter-spacing:.2px; background: rgba(250,204,21,.14); border:1px solid rgba(250,204,21,.45); color: rgba(255,255,255,.92); }
                    .rr-fantasy-slot__remove{ position:absolute; right:.55rem; top:.55rem; z-index:6; width:34px; height:34px; border-radius:10px; border:1px solid rgba(255,255,255,.12); background: rgba(0,0,0,.25); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; }
                    .rr-fantasy-slot__remove:hover{ background: rgba(239,68,68,.3); }
                    .rr-fantasy-slot__name{ color:#e0e7ff; font-weight: 900; font-size: .95rem; text-align:center; line-height: 1.2; padding: 0 .25rem; }
                    .rr-fantasy-slot__card{ width:100%; display:flex; justify-content:center; align-items:center; }
                    .rr-fantasy-slot__card .rr-card-item{ width:100%; max-width: 145px; }
                    .rr-fantasy-slot__card .hex-card{ margin: 0; }

                    /* Placeholder hex slots */
                    .rr-fantasy-modal .hex-card--placeholder{ filter: none; }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__border{ background: rgba(99,102,241,.25); }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__border-line{ background: rgba(15,23,42,.8); }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__inner{ background: linear-gradient(179deg, rgba(99,102,241,.15), rgba(99,102,241,.08) 90%); }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__img{ background: repeating-linear-gradient(126deg, rgba(99,102,241,.1) 0%, rgba(99,102,241,.15) 1.2%, transparent 1.19%, transparent 4%); }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__athlete{ padding-top: 0; align-items:center; }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__athlete img{ display:none; }
                    .rr-fantasy-slot__plus{ width: 54px; height: 54px; border-radius: 16px; display:flex; align-items:center; justify-content:center; font-size: 2.1rem; font-weight: 900; color: rgba(165,180,252,.75); background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.3); }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__text{ padding: .35rem .45rem 1.0rem; }
                    .rr-fantasy-modal .hex-card--placeholder .hex-card__name{ color: rgba(165,180,252,.85); }

                    /* Hex card (scoped to fantasy modal) */
                    .rr-fantasy-modal .rr-card-item{ width: 100%; max-width: 220px; }
                    .rr-fantasy-modal .hex-card{ filter: drop-shadow(0px 0px 5px var(--card-color)) drop-shadow(0px 0px 15px var(--card-color)); position: relative; max-width: 200px; margin: auto; font-family: inherit; }
                    .rr-fantasy-modal .hex-card__border,
                    .rr-fantasy-modal .hex-card__border-line,
                    .rr-fantasy-modal .hex-card__inner{ clip-path: polygon(50% 0, 100% 20%, 100% 80%, 50% 100%, 0% 80%, 0% 20%); }
                    .rr-fantasy-modal .hex-card__border{ position:absolute; width:98%; height:102%; background: var(--card-color); left:1%; top:1%; }
                    .rr-fantasy-modal .hex-card__border-line{ position:absolute; width:95%; height:99%; left:2.5%; top:2.5%; background: linear-gradient(to bottom, hsl(42, 90%, 72%) 33%, hsl(0, 0%, 0%) 70%); }
                    .rr-fantasy-modal .rr-card-item[data-nivel="legado"] .hex-card__border-line,
                    .rr-fantasy-modal .rr-card-item[data-nivel="ascendente"] .hex-card__border-line{ background: linear-gradient(to bottom, hsl(220, 15%, 85%) 33%, hsl(220, 10%, 20%) 70%); }
                    .rr-fantasy-modal .rr-card-item[data-nivel="legado"] .hex-card__inner,
                    .rr-fantasy-modal .rr-card-item[data-nivel="ascendente"] .hex-card__inner{ background: linear-gradient(179deg, hsl(220, 10%, 70%), hsl(220, 10%, 50%) 90%); }
                    .rr-fantasy-modal .hex-card__inner{ background: linear-gradient(179deg, var(--card-color), #e3a83b 90%); padding: .4rem .15rem 0; width: 92%; margin-top: 4.8%; margin-left: 4%; }
                    .rr-fantasy-modal .hex-card__img{ display:flex; padding: 0 .5rem; background: repeating-linear-gradient(126deg, hsla(0, 0%, 100%, 0.219) 0%, #ffffff57 1.2%, transparent 1.19%, transparent 4%); position: relative; justify-content:center; min-height: 100px; }
                    .rr-fantasy-modal .hex-card__badge{ position:absolute; top: 5px; left: 5px; font-size: 1.2rem; z-index:2; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); }
                    .rr-fantasy-modal .hex-card__athlete{ flex:1; padding-top: 8%; display:flex; justify-content:center; align-items:flex-end; overflow:hidden; }
                    .rr-fantasy-modal .hex-card__athlete img{ width: 90%; max-height: 100px; object-fit: cover; object-position: top center; border-radius: 4px; }
                    .rr-fantasy-modal .hex-card__text{ position: relative; padding: .5rem .5rem 3.4rem; text-align:center; background: rgba(0,0,0,0.1); }
                    .rr-fantasy-modal .hex-card__text:before{ content:''; position:absolute; inset:0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="%23d4a574" width="100" height="100"/></svg>'); opacity: .15; z-index:-1; }
                    .rr-fantasy-modal .hex-card__type{ width: min-content; margin: -8% auto .3rem; padding: 2px 12px; border-radius: 4px; background: var(--bg-color); color:#000; font-size:.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; }
                    .rr-fantasy-modal .hex-card__name{ font-size: 1rem; font-weight: 800; margin: 0 0 .2rem; color:#000; text-transform: uppercase; line-height: 1.1; position: relative; }
                    .rr-fantasy-modal .hex-card__name:before{ content:''; background: linear-gradient(to right, transparent, #3730305c, black, #0000004f, transparent); position:absolute; left:0; right:0; bottom:-2px; height:2px; }
                    .rr-fantasy-modal .hex-card__points{ font-size: 1.4rem; font-weight: 800; margin: .3rem 0 .2rem; color:#000; position:relative; }
                    .rr-fantasy-modal .hex-card__stats{ display:flex; justify-content:center; gap:.5rem; margin-top:.4rem; font-size:.65rem; font-weight: 600; color:#333; }
                    .rr-fantasy-modal .hex-card__stat{ background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 3px; }
                    .rr-fantasy-modal .hex-card__actions{ position:absolute; left:-0.5rem; right:-0.5rem; bottom: .15rem; margin:0; z-index:3; }
                    .rr-fantasy-modal .hex-card__btn{ display:block; width:100%; background: rgba(0,0,0,0.78); border: 1px solid rgba(255,255,255,0.18); color:#fff; padding: 8px 0 9px; border-radius: 0; font-size: .78rem; font-weight: 600; cursor:pointer; transition: all 0.2s ease; letter-spacing: 0.2px; clip-path: polygon(0 0, 100% 0, 92% 58%, 50% 100%, 8% 58%); }
                    .rr-fantasy-modal .hex-card__btn:hover{ background: rgba(0,0,0,0.92); border-color: rgba(255,255,255,0.28); }

                    /* Mini hex cards for selection list */
                    .rr-fantasy-hex-mini-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: .9rem .6rem; padding: .4rem 0 .2rem; justify-items: center; max-height: 420px; overflow:auto; }
                    @media (max-width: 575.98px){ .rr-fantasy-hex-mini-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem .5rem; } }
                    @media (max-width: 420px){ .rr-fantasy-hex-mini-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                    @media (max-width: 575.98px){ .rr-fantasy-hex-mini-grid{ max-height: none; overflow: visible; } }
                    .rr-fantasy-hex-mini-btn{ border:0; background: transparent; padding:0; cursor:pointer; width:100%; display:flex; justify-content:center; }
                    .rr-fantasy-hex-mini-btn:disabled{ opacity:.45; cursor:not-allowed; }
                    .rr-fantasy-hex-mini-btn.is-selected .hex-card__inner{ box-shadow: 0 0 0 3px rgba(99,102,241,.4) inset; }
                    .rr-fantasy-hex-mini-btn:focus-visible .hex-card__inner{ outline: 2px solid rgba(99,102,241,.85); outline-offset: 2px; }
                    .rr-fantasy-modal .hex-card--mini{ max-width: 145px; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__img{ min-height: 78px; padding: 0 .45rem; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__athlete{ padding-top: 10%; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__athlete img{ width: 92%; max-height: 78px; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__badge{ display:none; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__text{ padding: .35rem .45rem 1.0rem; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__type{ display:none; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__name{ font-size: .78rem; margin: 0; }
                    .rr-fantasy-modal .hex-card--mini .hex-card__points,
                    .rr-fantasy-modal .hex-card--mini .hex-card__stats,
                    .rr-fantasy-modal .hex-card--mini .hex-card__actions{ display:none; }

                    /* Custom scrollbar */
                    .rr-fantasy-modal ::-webkit-scrollbar{ width: 6px; height: 6px; }
                    .rr-fantasy-modal ::-webkit-scrollbar-track{ background: rgba(15,23,42,.4); border-radius: 3px; }
                    .rr-fantasy-modal ::-webkit-scrollbar-thumb{ background: rgba(99,102,241,.4); border-radius: 3px; }
                    .rr-fantasy-modal ::-webkit-scrollbar-thumb:hover{ background: rgba(99,102,241,.6); }
                `;
                var style = document.createElement('style');
                style.id = styleId;
                style.textContent = css;
                document.head.appendChild(style);
            }

            function selectionSig(ids){
                return (Array.isArray(ids) ? ids.map(String).join(',') : '');
            }

            function setSelectedIds(ids){
                var arr = Array.isArray(ids) ? ids.map(function(x){ return String(x); }) : [];
                arr = arr.filter(Boolean);
                // unique while preserving order
                var seen = new Set();
                var ordered = [];
                arr.forEach(function(id){
                    if (seen.has(id)) return;
                    seen.add(id);
                    ordered.push(id);
                });

                var prevSig = selectionSig(state.builder.selectedIds);
                state.builder.selectedIds = ordered.slice(0, 4);
                var nextSig = selectionSig(state.builder.selectedIds);
                if (prevSig !== nextSig) {
                    state.builder.verifiedOk = false;
                    state.builder.verifiedSig = null;
                    var hint = (ensureModal().querySelector('#rrFantasyBuilderHint'));
                    if (hint) hint.textContent = '';
                    var vr = (ensureModal().querySelector('#rrFantasyVerifyResult'));
                    if (vr) vr.innerHTML = '';
                }
                updateBuilderButtons();
                renderBuilderSlots();
                renderBuilderList();
            }

            function updateBuilderButtons(){
                var modalEl = ensureModal();
                var verifyBtn = modalEl.querySelector('[data-action="builder-verify"]');
                var saveBtn = modalEl.querySelector('[data-action="builder-save"]');
                var ids = state.builder.selectedIds;
                var ready = ids.length === 4;
                if (verifyBtn) verifyBtn.disabled = !ready;
                if (saveBtn) saveBtn.disabled = !(ready && state.builder.verifiedOk && state.builder.verifiedSig === selectionSig(ids));
            }

            function num(val){
                if (val === null || val === undefined) return 0;
                var s = String(val).replace(',', '.');
                var n = parseFloat(s);
                return isNaN(n) ? 0 : n;
            }

            function formatBRL(val){
                var n = num(val);
                var decimals = 2;
                // Mostrar mais casas decimais para valores pequenos (teste)
                if (n > 0 && n < 0.01) decimals = 6;
                else if (n > 0 && n < 1) decimals = 4;
                try {
                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(n);
                } catch (e) {
                    return 'R$ ' + n.toFixed(decimals);
                }
            }

            function formatClose(iso){
                if (!iso) return '—';
                var d = new Date(iso);
                if (isNaN(d.getTime())) return '—';
                return d.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
            }

            // ✅ Timer countdown - mostra tempo restante
            function formatCountdown(iso){
                if (!iso) return '—';
                var target = new Date(iso);
                if (isNaN(target.getTime())) return '—';

                var now = new Date();
                var diff = target.getTime() - now.getTime();

                // Se já passou, mostrar "Encerrado"
                if (diff <= 0) return 'Encerrado';

                var totalMinutes = Math.floor(diff / 60000);
                var days = Math.floor(totalMinutes / 1440);
                var hours = Math.floor((totalMinutes % 1440) / 60);
                var minutes = totalMinutes % 60;

                // Formatação inteligente
                if (days > 0) {
                    return days + 'd ' + hours + 'h';
                } else if (hours > 0) {
                    return hours + 'h ' + minutes + 'm';
                } else {
                    return minutes + 'm';
                }
            }

            // ✅ Atualizar todos os timers na página
            function updateAllTimers(){
                document.querySelectorAll('[data-closes-at]').forEach(function(el){
                    var iso = el.getAttribute('data-closes-at');
                    if (iso) {
                        var countdown = formatCountdown(iso);
                        // Atualizar o span interno se existir, senão o próprio elemento
                        var span = el.querySelector('span');
                        if (span) {
                            span.textContent = countdown;
                        } else {
                            el.textContent = countdown;
                        }
                        // Adicionar classe se encerrado
                        if (countdown === 'Encerrado') {
                            el.classList.add('rr-timer--ended');
                        } else {
                            el.classList.remove('rr-timer--ended');
                        }
                    }
                });
            }

            // Iniciar atualização dos timers a cada minuto
            setInterval(updateAllTimers, 60000);

            function formatFantasyTierLabel(price){
                var normalized = Math.round(num(price) * 100) / 100;
                if (!normalized) return 'R$0';
                return 'R$' + normalized.toLocaleString('pt-BR', {
                    minimumFractionDigits: normalized % 1 === 0 ? 0 : 2,
                    maximumFractionDigits: 2
                });
            }

            function tierOf(league){
                if (league && league.is_premium) return 'Premium';
                var p = Math.round(num(league && league.price) * 100) / 100;
                if (p === 20) return 'R$20';
                if (p === 50) return 'R$50';
                if (p === 100) return 'R$100';
                return formatFantasyTierLabel(league && league.price);
            }

            async function fetchJson(url, options){
                var resp = await fetch(url, Object.assign({
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                }, options || {}));

                var contentType = resp.headers.get('content-type') || '';
                var data = null;
                if (contentType.includes('application/json')) {
                    data = await resp.json();
                } else {
                    var text = await resp.text();
                    data = { success: false, message: text };
                }
                data.__http = { ok: resp.ok, status: resp.status };
                return data;
            }

            function updateTierCounts(sectionEl){
                var counts = { 'Todos': 0, 'Premium': 0, 'R$20': 0, 'R$50': 0, 'R$100': 0 };
                state.leagues.forEach(function(l){
                    var t = tierOf(l);
                    if (counts[t] !== undefined) counts[t] += 1;
                    counts['Todos'] += 1;
                });

                sectionEl.querySelectorAll('#rrFantasySubmenu [data-count]').forEach(function(el){
                    var tier = el.getAttribute('data-count');
                    el.textContent = String(counts[tier] || 0);
                });
            }

            function renderLeagues(sectionEl){
                var grid = sectionEl.querySelector('#rrFantasyLeaguesGrid');
                var empty = sectionEl.querySelector('#rrFantasyLeaguesEmpty');
                if (!grid) return;

                var filtered = state.leagues.filter(function(l){
                    if (state.selectedTier === 'Todos') return true;
                    return tierOf(l) === state.selectedTier;
                });

                if (!filtered.length) {
                    grid.innerHTML = '';
                    if (empty) empty.style.display = '';
                    return;
                }

                if (empty) empty.style.display = 'none';

                grid.innerHTML = filtered.map(function(l){
                    var teams = parseInt(l.teams_count || 0, 10) || 0;
                    var maxUsers = l.max_users ? parseInt(l.max_users, 10) : null;
                    var progress = (maxUsers && maxUsers > 0) ? Math.min(100, Math.round((teams / maxUsers) * 100)) : 0;
                    var badge = l.is_premium ? '' : (l.category || 'Bolão');
                    var rodeioNome = l.rodeio && l.rodeio.nome ? l.rodeio.nome : '';
                    var modNome = l.modalidade && l.modalidade.nome ? l.modalidade.nome : '';
                    var statusLive = (l.rodeio && l.rodeio.status_transmissao) ? l.rodeio.status_transmissao : null;
                    var deadline = l.closes_at ? formatClose(l.closes_at) : '—';
                    var entrantsLabel = maxUsers ? (teams + '/' + maxUsers) : (teams + '/—');
                    var tags = [];

                    // Status de transmissão
                    if (statusLive) {
                        var st = String(statusLive);
                        if (st === 'ao_vivo') tags.push({ text: 'AO VIVO', cls: 'rr-league-card__tag--live' });
                        else if (st === 'pausado') tags.push({ text: 'PAUSADO', cls: 'rr-league-card__tag--paused' });
                        else if (st === 'programado') tags.push({ text: 'PROGRAMADO', cls: 'rr-league-card__tag--scheduled' });
                        else if (st === 'scheduled') tags.push({ text: 'PROGRAMADO', cls: 'rr-league-card__tag--scheduled' });
                        else if (st === 'finalizado') tags.push({ text: 'FINALIZADO', cls: 'rr-league-card__tag--ended' });
                        else if (st === 'finished') tags.push({ text: 'FINALIZADO', cls: 'rr-league-card__tag--ended' });
                        // Não adicionar status desconhecidos
                    }
                    // ✅ Se o bolão foi finalizado pelo admin, forçar tag FINALIZADO
                    if (l.event_finalized && !tags.some(function(t){ return t && typeof t === 'object' && t.text === 'FINALIZADO'; })) {
                        tags.unshift({ text: 'FINALIZADO', cls: 'rr-league-card__tag--ended' });
                    }
                    if (rodeioNome) tags.push(rodeioNome);
                    if (modNome) tags.push(modNome);

                    var hasPhoto = !!(l && l.image_url);
                    var imgHtml = '';
                    if (hasPhoto) {
                        var src = String(l.image_url || '');
                        imgHtml = `<div class="rr-league-card__img rr-league-card__img--has-photo">
                            <img class="rr-league-card__photo" src="${escapeHtml(src)}" alt="" loading="lazy" onerror="this.parentElement.style.display='none'">
                        </div>`;
                    }

                    var isPremium = !!l.is_premium;
                    var premiumClass = isPremium ? ' rr-league-card--premium' : '';
                    var normalLogoUrl = '{{ asset("assets/images/logo_icon/logo.png") }}';
                    var premiumLogoUrl = '{{ asset("assets/images/logo_icon/premiumleague.png") }}';
                    var logoUrl = isPremium ? premiumLogoUrl : normalLogoUrl;
                    var logoHtml = '<div class="rr-league-card__top-logo"><img src="' + logoUrl + '" alt="Logo" onerror="this.parentElement.style.display=\'none\'"></div>';

                    // Premium usa crown no header, normal usa badge
                    var premiumCrownHtml = isPremium ? '<div class="rr-league-card__premium-crown"><i class="fas fa-crown"></i></div>' : '';
                    var badgeHtml = badge ? '<span class="rr-league-card__badge">' + escapeHtml(badge) + '</span>' : '';
                    
                    // ✅ Verificar se bolão está cheio (100% das vagas preenchidas)
                    var isLeagueFull = maxUsers > 0 && teams >= maxUsers;

                    // Prêmio destacado - usa total_prize (potencial) se disponível, senão prize_pool (atual)
                    var totalPrizeValue = num(l.total_prize);
                    var prizePoolValue = num(l.prize_pool);
                    var prizeLabel = '';
                    var prizeSubLabel = '';

                    if (isPremium && String(l.reward_mode) !== 'manual_prize') {
                        prizeLabel = 'Acúmulo de pontos';
                    } else if (totalPrizeValue > 0) {
                        // Mostra prêmio total potencial (baseado no max_users)
                        prizeLabel = formatBRL(l.total_prize);
                        prizeSubLabel = 'Prêmio Total';
                    } else if (prizePoolValue > 0) {
                        // Fallback para prize_pool calculado
                        prizeLabel = formatBRL(l.prize_pool);
                        prizeSubLabel = 'Prêmio Atual';
                    }

                    var prizeHtml = prizeLabel ? `
                        <div class="rr-league-card__prize">
                            <span class="rr-league-card__prize-icon">🏆</span>
                            <span class="rr-league-card__prize-label">${prizeSubLabel || 'Prêmio'}</span>
                            <span class="rr-league-card__prize-value">${escapeHtml(prizeLabel)}</span>
                        </div>
                    ` : '';

                    return `
                        <div class="rr-league-card${premiumClass}${l.event_finalized ? ' rr-league-card--finalized' : ''}${l.registration_status === 'closed' ? ' rr-league-card--closed' : ''}" data-league-id="${l.id}">
                            ${logoHtml}
                            ${imgHtml}
                            <div class="rr-league-card__content">
                                ${!isPremium && badge ? badgeHtml : ''}
                                <div class="rr-league-card__header">
                                    <div>
                                        <h4 class="rr-league-card__title">${escapeHtml(l.name || 'Bolão')}</h4>
                                        <small class="rr-league-card__owner">${escapeHtml(rodeioNome)} ${modNome ? ('• ' + escapeHtml(modNome)) : ''}</small>
                                    </div>
                                    ${isPremium ? premiumCrownHtml : ''}
                                </div>
                                ${l.registration_deadline && l.registration_status !== 'closed' && !isLeagueFull ? `
                                    <div class="rr-league-card__deadline" data-deadline="${l.registration_deadline}">
                                        <i class="fas fa-hourglass-half"></i> 
                                        <span>Inscrições: <strong>${escapeHtml(formatCountdown(l.registration_deadline))}</strong></span>
                                    </div>
                                ` : ''}
                                ${l.registration_status === 'closed' ? `
                                    <div class="rr-league-card__deadline rr-league-card__deadline--closed">
                                        <i class="fas fa-lock"></i> 
                                        <span>Inscrições encerradas</span>
                                    </div>
                                ` : ''}
                                ${prizeHtml}
                                ${!isPremium && num(l.price) > 0 ? `
                                    <div class="rr-league-card__entry-fee">
                                        <span class="rr-league-card__entry-fee-label">Entrada:</span>
                                        <span class="rr-league-card__entry-fee-value">${formatBRL(l.price)}</span>
                                    </div>
                                ` : ''}
                                <div class="rr-league-card__tags">
                                    ${tags.map(function(t){
                                        if (t && typeof t === 'object') {
                                            return `<span class="rr-league-card__tag ${t.cls || ''}">${escapeHtml(String(t.text || ''))}</span>`;
                                        }
                                        return `<span class="rr-league-card__tag">${escapeHtml(String(t))}</span>`;
                                    }).join('')}
                                </div>
                                <div class="rr-league-card__stats">
                                    <div class="rr-league-card__stat">
                                        <svg viewBox="0 0 24 24" class="rr-league-card__icon"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                                        ${escapeHtml(entrantsLabel)}
                                    </div>
                                </div>
                                <div class="rr-league-card__progress">
                                    <div class="rr-league-card__progress-bar" style="width:${progress}%;"></div>
                                </div>
                                ${l.event_finalized ? `
                                    <div class="rr-league-card__battle-text rr-league-card__battle-text--fantasy rr-league-card__battle-text--finalized rr-league-card__action-trigger" data-action="open-result" data-league-id="${l.id}">
                                        <i class="fas fa-trophy"></i> Ver Resultado
                                    </div>
                                ` : l.is_full ? `
                                    <div class="rr-league-card__battle-text rr-league-card__battle-text--fantasy rr-league-card__action-trigger" data-action="open-ranking" data-league-id="${l.id}">
                                        Tá valendo! Ver ranking
                                    </div>
                                ` : `
                                    <button class="rr-league-card__btn${l.registration_status === 'closed' ? ' rr-league-card__btn--disabled' : ''} rr-league-card__action-trigger" type="button" data-action="open-league-modal" data-league-id="${l.id}" ${l.registration_status === 'closed' ? 'disabled' : ''}>
                                        <i class="fas ${l.registration_status === 'closed' ? 'fa-lock' : 'fa-bolt'}"></i>
                                        <span>${l.registration_status === 'closed' ? 'Inscrições Encerradas' : (num(l.price) > 0 ? 'Participar por ' + formatBRL(l.price) : 'Participar')}</span>
                                    </button>
                                `}
                            </div>
                        </div>
                    `;
                }).join('');
                
                // 📄 Atualizar paginação após renderizar
                if (window.updateFantasyPagination) {
                    setTimeout(function() {
                        window.updateFantasyPagination();
                    }, 50);
                }
                
                // 🎯 Registrar eventos para os botões renderizados
                setTimeout(function() {
                    grid.querySelectorAll('[data-action="open-league-modal"], [data-action="open-ranking"], [data-action="open-result"]').forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            var action = this.getAttribute('data-action');
                            var leagueId = this.getAttribute('data-league-id');
                            
                            if (action === 'open-result' || action === 'open-ranking') {
                                // Bolão finalizado ou cheio: abrir modal no ranking
                                openLeagueModal(leagueId, 'ranking');
                            } else if (action === 'open-league-modal') {
                                // Abrir modal completo (bolões abertos)
                                openLeagueModal(leagueId, 'overview');
                            }
                        });
                    });
                }, 100);
            }

            function renderTopCards(sectionEl){
                // Top prize league - prioriza total_prize, senão prize_pool
                var topPrize = null;
                state.leagues.forEach(function(l){
                    var prize = num(l.total_prize) || num(l.prize_pool);
                    if (prize <= 0) return;
                    if (!topPrize) { topPrize = l; return; }
                    var topVal = num(topPrize.total_prize) || num(topPrize.prize_pool);
                    if (prize > topVal) topPrize = l;
                });

                var setText = function(id, val){
                    var el = sectionEl.querySelector('#' + id);
                    if (el) el.textContent = val;
                };

                if (topPrize) {
                    var prizeVal = num(topPrize.total_prize) || num(topPrize.prize_pool);
                    setText('rrFantasyTopPrizeLeague', topPrize.name || 'Bolão');
                    setText('rrFantasyTopPrizeValue', formatBRL(prizeVal));
                    setText('rrFantasyTopPrizeEntries', String(topPrize.teams_count || 0));
                    setText('rrFantasyTopPrizeDeadline', formatClose(topPrize.closes_at));
                    setText('rrFantasyTopPrizeUpdated', state.lastLoadedAt ? ('Atualizado ' + state.lastLoadedAt) : '');
                    loadRankingInto(sectionEl, topPrize, 'rrFantasyTopPrizeTop', 3, false);
                } else {
                    setText('rrFantasyTopPrizeLeague', 'Sem premiação');
                    setText('rrFantasyTopPrizeValue', '—');
                    setText('rrFantasyTopPrizeEntries', '—');
                    setText('rrFantasyTopPrizeDeadline', '—');
                    setText('rrFantasyTopPrizeUpdated', '');
                    var list = sectionEl.querySelector('#rrFantasyTopPrizeTop');
                    if (list) list.innerHTML = '';
                }

                // Premium card: best premium league snapshot (not season ranking)
                var premium = state.leagues.filter(function(l){ return !!l.is_premium; });
                var bestPremium = premium.length ? premium.reduce(function(a, b){
                    var aVal = num(a.total_prize) || num(a.prize_pool);
                    var bVal = num(b.total_prize) || num(b.prize_pool);
                    return bVal > aVal ? b : a;
                }, premium[0]) : null;

                if (bestPremium) {
                    setText('rrFantasyPremiumTitle', bestPremium.name || 'Bolão Premium');
                    setText('rrFantasyPremiumSeason', bestPremium.rodeio && bestPremium.rodeio.nome ? bestPremium.rodeio.nome : 'Premium');
                    var premPrize = num(bestPremium.total_prize) || num(bestPremium.prize_pool);
                    if (String(bestPremium.reward_mode) === 'manual_prize' && premPrize > 0) {
                        setText('rrFantasyPremiumPool', formatBRL(premPrize));
                    } else {
                        setText('rrFantasyPremiumPool', '—');
                    }
                    var note = sectionEl.querySelector('#rrFantasyPremiumNote');
                    if (note) {
                        note.textContent = (String(bestPremium.reward_mode) === 'manual_prize')
                            ? 'Bolão premium em destaque (com prêmio).'
                            : 'Bolão premium em destaque (acúmulo de pontos).';
                    }
                    setText('rrFantasyPremiumUpdated', state.lastLoadedAt ? ('Atualizado ' + state.lastLoadedAt) : '');
                    loadRankingInto(sectionEl, bestPremium, 'rrFantasyPremiumTop', 3, false, true);
                } else {
                    setText('rrFantasyPremiumTitle', 'Premium');
                    setText('rrFantasyPremiumSeason', '');
                    setText('rrFantasyPremiumPool', '—');
                    var noteEl = sectionEl.querySelector('#rrFantasyPremiumNote');
                    if (noteEl) noteEl.textContent = 'Nenhum bolão premium ao vivo.';
                    setText('rrFantasyPremiumUpdated', '');
                    var premList = sectionEl.querySelector('#rrFantasyPremiumTop');
                    if (premList) premList.innerHTML = '';
                }
            }

            function escapeHtml(str){
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function cssUrl(url){
                if (!url) return '';
                return String(url).replace(/[\n\r"'\\]/g, function(ch){
                    return encodeURIComponent(ch);
                });
            }

            function normalizeRankingItems(payload){
                if (!payload) return [];
                var items = [];
                if (Array.isArray(payload)) items = payload;
                else if (payload && Array.isArray(payload.items)) items = payload.items;
                if (!Array.isArray(items)) return [];
                return items.map(function(it, idx){
                    var name = it.team_name || it.name || it.username || it.user_name || ('#' + (idx + 1));
                    var pts = it.points;
                    if (pts === null || pts === undefined || pts === '') pts = it.total_points;
                    if (pts === null || pts === undefined || pts === '') pts = it.pontuacao;
                    if (pts === null || pts === undefined || pts === '') pts = it.score;
                    if (pts === null || pts === undefined || pts === '') pts = it.pontos;
                    if (pts === null || pts === undefined) pts = '';
                    var numericPts = parseFloat(String(pts).replace(',', '.'));
                    if (!isFinite(numericPts)) numericPts = null;
                    var pos = it.position || it.pos || (idx + 1);
                    return {
                        pos: pos,
                        name: name,
                        display_name: it.display_name || name,
                        show_in_listings: it.show_in_listings,
                        points: pts,
                        points_value: numericPts,
                        is_mine: !!it.is_mine,
                        user_foto: it.user_foto || it.user_avatar || it.avatar || null
                    };
                });
            }

            function rankingNumericPoints(row){
                var value = row ? row.points_value : null;
                return (typeof value === 'number' && isFinite(value)) ? value : null;
            }

            function formatRankingPoints(value){
                if (value === null || value === undefined || value === '') return '—';
                var numeric = typeof value === 'number' ? value : parseFloat(String(value).replace(',', '.'));
                if (!isFinite(numeric)) return '—';

                return new Intl.NumberFormat('pt-BR', {
                    maximumFractionDigits: 2,
                    minimumFractionDigits: 0,
                }).format(numeric);
            }

            function rankingDisplayName(row){
                var name = row.display_name || row.name || row.username || row.user_name || 'Usuário';
                return row.show_in_listings === false ? maskUsername(name) : name;
            }

            function rankingAvatarFallback(name){
                var parts = String(name || '').trim().split(/\s+/).filter(Boolean);
                if (!parts.length) return 'RR';

                return parts.slice(0, 2).map(function(part){
                    return (part.charAt(0) || 'R').toUpperCase();
                }).join('').substring(0, 2);
            }

            function rankingAvatarMarkup(row, className){
                var displayName = rankingDisplayName(row);
                var avatarSrc = row.user_foto ? imgUrl(row.user_foto) : '';
                var fallback = escapeHtml(rankingAvatarFallback(displayName));
                var alt = escapeHtml(displayName);

                if (avatarSrc) {
                    return '<div class="' + className + '">'
                        + '<img src="' + escapeHtml(avatarSrc) + '" alt="' + alt + '" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">'
                        + '<span style="display:none;">' + fallback + '</span>'
                        + '</div>';
                }

                return '<div class="' + className + '"><span>' + fallback + '</span></div>';
            }

            function buildRankingPodiumCard(row, config, leaderPoints){
                var displayName = rankingDisplayName(row);
                var numericPoints = rankingNumericPoints(row);
                var gap = numericPoints === null ? null : Math.max(0, leaderPoints - numericPoints);
                var gapText = config.rank === 1
                    ? 'Líder absoluto'
                    : (numericPoints === null ? 'Sem pontuação' : (gap > 0 ? (formatRankingPoints(gap) + ' pts atrás') : 'Empatado no topo'));

                return `
                    <article class="rr-fantasy-podium-card rr-fantasy-podium-card--${config.tone}${row.is_mine ? ' rr-fantasy-podium-card--mine' : ''}" style="--rr-fantasy-podium-accent:${config.accent};">
                        <div class="rr-fantasy-podium-card__medal">${config.medal}</div>
                        <div class="rr-fantasy-podium-card__badge">${config.rankLabel}</div>
                        ${rankingAvatarMarkup(row, 'rr-fantasy-podium-card__avatar')}
                        <div class="rr-fantasy-podium-card__rank">${config.rankText}</div>
                        <h4 class="rr-fantasy-podium-card__name">${escapeHtml(displayName)}</h4>
                        <div class="rr-fantasy-podium-card__meta">
                            <div class="rr-fantasy-podium-card__chip"><span>Pontos</span><strong>${escapeHtml(formatRankingPoints(numericPoints))}</strong></div>
                            <div class="rr-fantasy-podium-card__chip"><span>Diferença</span><strong>${escapeHtml(gapText)}</strong></div>
                        </div>
                    </article>
                `;
            }

            function buildRankingRow(row, index, leaderPoints){
                var displayName = rankingDisplayName(row);
                var numericPoints = rankingNumericPoints(row);
                var gap = numericPoints === null ? null : Math.max(0, leaderPoints - numericPoints);
                var progress = (leaderPoints > 0 && numericPoints !== null) ? Math.max(6, Math.min(100, (numericPoints / leaderPoints) * 100)) : 0;
                var subtitle = index === 0
                    ? 'Líder da rodada'
                    : (numericPoints === null ? 'Sem pontuação' : (gap > 0 ? (formatRankingPoints(gap) + ' pts do líder') : 'Empatado no topo'));
                var rowClass = 'rr-fantasy-ranking-row-v2';

                if (index === 0) rowClass += ' rr-fantasy-ranking-row-v2--gold';
                else if (index === 1) rowClass += ' rr-fantasy-ranking-row-v2--silver';
                else if (index === 2) rowClass += ' rr-fantasy-ranking-row-v2--bronze';
                if (row.is_mine) rowClass += ' rr-fantasy-ranking-row-v2--mine';

                return `
                    <article class="${rowClass}" role="listitem">
                        <div class="rr-fantasy-ranking-row-v2__pos">#${escapeHtml(String(row.pos || (index + 1)))}</div>
                        ${rankingAvatarMarkup(row, 'rr-fantasy-ranking-row-v2__avatar')}
                        <div class="rr-fantasy-ranking-row-v2__body">
                            <div class="rr-fantasy-ranking-row-v2__top">
                                <div>
                                    <div class="rr-fantasy-ranking-row-v2__name">${escapeHtml(displayName)}</div>
                                    <div class="rr-fantasy-ranking-row-v2__subtitle">${escapeHtml(subtitle)}</div>
                                </div>
                                <div class="rr-fantasy-ranking-row-v2__points">${escapeHtml(formatRankingPoints(numericPoints))} pts</div>
                            </div>
                            <div class="rr-fantasy-ranking-row-v2__bar"><span style="width:${progress}%"></span></div>
                        </div>
                        <div class="rr-fantasy-ranking-row-v2__chev"><i class="fas fa-chevron-right"></i></div>
                    </article>
                `;
            }

            async function loadRanking(leagueId, view){
                console.log('📡 loadRanking chamado:', leagueId, view);
                
                // ✅ Usar a rota correta da API
                var url = apiUrl('/api/fantasy/leagues/' + encodeURIComponent(leagueId) + '/ranking' + (view ? '?view=' + encodeURIComponent(view) : ''));
                console.log('📡 URL:', url);

                var data = await fetchJson(url);
                console.log('📡 Resposta da API:', data);
                
                if (!data.__http.ok || !data.success) {
                    console.error('❌ Erro ao carregar ranking:', data);
                    return { ok: false, http: data.__http, message: data.message || 'Falha ao carregar ranking' };
                }
                
                // ✅ API retorna em data.ranking (array de objetos)
                var rankingData = data.data && data.data.ranking ? data.data.ranking : [];
                console.log('✅ Ranking carregado:', rankingData.length, 'times');
                
                return { ok: true, data: rankingData, meta: data.data || {} };
            }

            async function loadRankingInto(sectionEl, league, containerId, limit, full, premiumStyle){
                var container = sectionEl.querySelector('#' + containerId);
                if (!container) return;
                container.innerHTML = '<div class="text-muted small">Carregando ranking...</div>';

                var res = await loadRanking(league.id, full ? 'full' : 'top30');
                if (!res.ok) {
                    container.innerHTML = '<div class="text-muted small">Ranking indisponível</div>';
                    return;
                }

                // ✅ Agora res.data já é o array de ranking
                var items = res.data || [];
                if (typeof limit === 'number') items = items.slice(0, limit);
                if (!items.length) {
                    container.innerHTML = '<div class="text-muted small">Sem dados</div>';
                    return;
                }

                container.innerHTML = items.map(function(row, idx){
                    var rowCls = 'rr-fantasy-ranking-row';
                    if (idx === 0) rowCls += ' rr-fantasy-ranking-row--gold';
                    else if (idx === 1) rowCls += ' rr-fantasy-ranking-row--silver';
                    else if (idx === 2) rowCls += ' rr-fantasy-ranking-row--bronze';
                    
                    // ✅ Compatibilidade com formato da API
                    var pos = row.position || row.pos || (idx + 1);
                    var name = row.display_name || row.user_name || row.username || row.team_name || row.name || 'Usuário';
                    var displayName = row.display_name || ((row.show_in_listings === false) ? maskUsername(name) : name);
                    var points = row.points || 0;
                    
                    return `
                        <div class="${rowCls}" role="listitem">
                            <span class="rr-fantasy-ranking-row__pos">#${pos}</span>
                            <span class="rr-fantasy-ranking-row__name">${escapeHtml(displayName)}</span>
                        </div>
                    `;
                }).join('');
            }

            // 🏆 MODAL SIMPLIFICADO PARA RANKING (Bolões Cheios) - ESTILO UNIFICADO
            window.openRankingOnlyModal = async function(leagueId) {
                console.log('🏆 openRankingOnlyModal:', leagueId);
                
                // Acessar funções do escopo RRFantasy
                var RRFantasy = window.RRFantasy;
                if (!RRFantasy) {
                    console.error('❌ RRFantasy não inicializado');
                    return;
                }
                
                // Buscar dados da liga
                var league = state.leagues.find(function(l){ return String(l.id) === String(leagueId); });
                if (!league) {
                    console.error('❌ Liga não encontrada:', leagueId);
                    return;
                }

                // Criar modal simplificado
                installStyleOnce();
                var modalId = 'rrFantasyRankingModal';
                var existingModal = document.getElementById(modalId);
                if (existingModal) {
                    existingModal.remove();
                }

                var isPremium = !!league.is_premium;
                var rodeioNome = league.rodeio && league.rodeio.nome ? league.rodeio.nome : '';
                var modNome = league.modalidade && league.modalidade.nome ? league.modalidade.nome : '';
                var prizeVal = num(league.total_prize) || num(league.prize_pool);
                var prizeLabel = '';
                
                if (isPremium && String(league.reward_mode) !== 'manual_prize') {
                    prizeLabel = 'Acúmulo de pontos';
                } else if (prizeVal > 0) {
                    prizeLabel = formatBRL(prizeVal);
                }

                var el = document.createElement('div');
                el.className = 'modal fade rr-fantasy-modal';
                el.id = modalId;
                el.tabIndex = -1;
                el.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="flex: 1; text-align: center;">
                                    <h5 class="modal-title" style="margin: 0;">🏆 ${escapeHtml(league.name || 'Ranking')}</h5>
                                    <small class="text-muted" style="display: block; margin-top: 0.25rem;">${escapeHtml(rodeioNome)} ${modNome ? ('• ' + escapeHtml(modNome)) : ''}</small>
                                </div>
                                <button type="button" class="btn-close rr-fantasy-close" data-bs-dismiss="modal" aria-label="Fechar" style="position: absolute; right: 1.25rem; top: 50%; transform: translateY(-50%);">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="modal-body" style="padding: 0;">
                                <div id="rrFantasyUnifiedRanking">
                                    <div style="display: flex; align-items: center; justify-content: center; min-height: 300px; flex-direction: column; gap: 1rem;">
                                        <div style="width: 48px; height: 48px; border: 3px solid rgba(249, 115, 22, 0.2); border-top-color: #f59e0be6; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                                        <div style="color: #94a3b8; font-size: 0.95rem; font-weight: 500;">Carregando ranking...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(el);

                // Criar instância Bootstrap e abrir
                var modalInstance = null;
                if (hasBootstrapModal()) {
                    modalInstance = bootstrap.Modal.getOrCreateInstance(el);
                    modalInstance.show();
                } else {
                    el.classList.add('rr-fantasy-modal--open');
                    lockBodyScroll();
                }

                // Event handlers
                el.addEventListener('shown.bs.modal', function(){ lockBodyScroll(); });
                el.addEventListener('hidden.bs.modal', function(){
                    unlockBodyScroll();
                    el.remove();
                });

                var closeBtn = el.querySelector('.rr-fantasy-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        if (modalInstance) {
                            modalInstance.hide();
                        } else {
                            el.classList.remove('rr-fantasy-modal--open');
                            unlockBodyScroll();
                            el.remove();
                        }
                    });
                }

                // Funções de distribuição (mesmo padrão draft-arena)
                function hubGetPaidPositions(total) {
                    if (total <= 0) return 0;
                    return Math.max(1, Math.floor(total * 0.10));
                }
                
                function hubGetPrizeDistribution(positions) {
                    if (positions <= 0) return {};
                    if (positions === 1) return { 1: 1 };
                    if (positions === 2) return { 1: 0.65, 2: 0.35 };
                    if (positions === 3) return { 1: 0.50, 2: 0.30, 3: 0.20 };

                    var tiers = [{ from: 1, to: 1 }, { from: 2, to: 2 }, { from: 3, to: 3 }];
                    var remaining = positions - 3;
                    var pos = 4;

                    if (remaining <= 3) {
                        tiers.push({ from: 4, to: positions });
                    } else {
                        var chunks = remaining <= 8 ? 2 : (remaining <= 20 ? 3 : 4);
                        var base = Math.floor(remaining / chunks);
                        var extra = remaining - base * chunks;
                        var sizes = [];
                        for (var c = 0; c < chunks; c++) sizes.push(base + (c < extra ? 1 : 0));
                        sizes.sort(function(a, b) { return a - b; });
                        for (var s = 0; s < sizes.length; s++) {
                            tiers.push({ from: pos, to: pos + sizes[s] - 1 });
                            pos += sizes[s];
                        }
                    }

                    var nTiers = tiers.length;
                    var floorPctPerPerson = 100.0 / (positions * 3.6);
                    var totalFloor = floorPctPerPerson * positions;
                    var curvePool = 100.0 - totalFloor;
                    var spread = Math.max(3, Math.pow(positions, 1.2));
                    var ratio = Math.pow(spread, 1.0 / Math.max(1, nTiers - 1));
                    var perPerson = new Array(nTiers);
                    perPerson[nTiers - 1] = 1;
                    for (var i = nTiers - 2; i >= 0; i--) perPerson[i] = perPerson[i + 1] * ratio;

                    var totalRaw = 0;
                    for (var t = 0; t < nTiers; t++) {
                        var count = tiers[t].to - tiers[t].from + 1;
                        totalRaw += perPerson[t] * count;
                    }

                    var distribution = {};
                    for (var j = 0; j < nTiers; j++) {
                        var tierCount = tiers[j].to - tiers[j].from + 1;
                        var curvePctPerPerson = curvePool * perPerson[j] / totalRaw;
                        var totalPctPerPerson = floorPctPerPerson + curvePctPerPerson;
                        var pctAsFraction = totalPctPerPerson / 100;
                        for (var p = tiers[j].from; p <= tiers[j].to; p++) {
                            distribution[p] = pctAsFraction;
                        }
                    }

                    return distribution;
                }
                
                // Carregar ranking via API
                var rankingContainer = el.querySelector('#rrFantasyUnifiedRanking');
                
                try {
                    var url = apiUrl('/api/fantasy/leagues/' + encodeURIComponent(leagueId) + '/ranking');
                    var data = await fetchJson(url);
                    
                    if (!data.__http.ok || !data.success || !data.data) {
                        rankingContainer.innerHTML = '<div class="text-center text-muted" style="padding: 2rem;"><i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.5;"></i><p>Nenhum time cadastrado ainda.</p></div>';
                        return;
                    }
                    
                    var items = data.data.ranking || [];
                    var totalPlayers = parseInt(data.data.total_teams) || items.length;
                    var maxUsers = parseInt(data.data.max_users) || 0;
                    var entryPrice = parseFloat(data.data.entry_price) || 0;
                    var houseCut = parseFloat(data.data.house_cut_percent) || 0;
                    var currentPrize = parseFloat(data.data.prize_pool) || (totalPlayers * entryPrice * (1 - houseCut / 100));
                    var currentPaidPositions = hubGetPaidPositions(totalPlayers);
                    var dist = hubGetPrizeDistribution(currentPaidPositions);
                    var logoUrl = '{{ asset("assets/images/logo_icon/favicon.png") }}';
                    
                    if (!items.length) {
                        rankingContainer.innerHTML = '<div class="text-center text-muted" style="padding: 2rem;"><i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.5;"></i><p>Nenhum time cadastrado ainda.</p></div>';
                        return;
                    }
                    
                    // === RENDER UNIFICADO: INFO + PÓDIO + TABELA ===
                    var html = '<div class="prize-distribution-simulator" style="display: block; margin: 0; border: none; border-radius: 0;">';
                    html += '<div class="prize-distribution-header"><i class="las la-trophy"></i><span>Ranking do Bolão • <span style="color: #ef4444;">Encerrado</span></span></div>';
                    html += '<div class="prize-distribution-body">';
                    
                    // Info Header
                    html += '<div class="prize-tier-info">';
                    html += '<div class="prize-tier-label">INSCRITOS / PAGOS</div>';
                    html += '<div class="prize-tier-value"><span>' + totalPlayers + '</span> inscritos → <span>' + currentPaidPositions + '</span> posições pagas</div>';
                    if (totalPlayers > 0) {
                        html += '<div class="prize-tier-desc">Prêmio Total: <strong style="color: #22c55e;">' + formatBRL(currentPrize) + '</strong></div>';
                    } else {
                        html += '<div class="prize-tier-desc"><span style="color: #ef4444;">Aguardando equipes para calcular premiação</span></div>';
                    }
                    html += '</div>';
                    
                    // Pódio Visual (Top 3)
                    var top3 = items.slice(0, 3);
                    if (top3.length > 0) {
                        var positions = [
                            { idx: 1, medal: '🥈', color: '#C0C0C0', height: '200px', rank: '2º' },
                            { idx: 0, medal: '🥇', color: '#FFD700', height: '230px', rank: '1º' },
                            { idx: 2, medal: '🥉', color: '#CD7F32', height: '180px', rank: '3º' }
                        ];
                        
                        html += '<div class="unified-podium">';
                        html += '<div class="unified-podium__title"><span>🏆</span><h6>Pódio ' + (currentPrize > 0 ? '& Premiação' : '') + '</h6></div>';
                        html += '<div class="unified-podium__columns">';
                        
                        positions.forEach(function(p) {
                            var team = top3[p.idx];
                            if (!team) { html += '<div class="unified-podium__col"></div>'; return; }
                            
                            var teamName = team.display_name || team.user_name || team.username || team.name || team.team_name || 'Usuário';
                            var displayName = team.display_name || ((team.show_in_listings === false) ? maskUsername(teamName) : teamName);
                            var avatarUrl = team.user_foto || team.user_avatar || team.avatar || logoUrl;
                            var points = team.points || 0;
                            var pos = p.idx + 1;
                            var isPaid = pos <= currentPaidPositions;
                            var prizeAmount = isPaid ? (currentPrize * (dist[pos] || 0)) : 0;
                            
                            html += '<div class="unified-podium__col">';
                            html += '<div class="unified-podium__avatar" style="border-color: ' + p.color + '; box-shadow: 0 4px 15px rgba(0,0,0,0.3), 0 0 20px ' + p.color + '40;">';
                            html += '<img src="' + avatarUrl + '" alt="' + escapeHtml(displayName) + '" onerror="this.src=\'' + logoUrl + '\'">';
                            html += '</div>';
                            html += '<div class="unified-podium__pedestal" style="border-color: ' + p.color + '; background: linear-gradient(135deg, ' + p.color + '25, ' + p.color + '15); height: ' + p.height + ';">';
                            html += '<div class="unified-podium__medal">' + p.medal + '</div>';
                            html += '<div class="unified-podium__rank" style="color: ' + p.color + ';">' + p.rank + ' Lugar</div>';
                            html += '<div class="unified-podium__name">' + escapeHtml(displayName) + '</div>';
                            if (prizeAmount > 0) {
                                html += '<div class="unified-podium__prize" style="border-color: ' + p.color + '60;">';
                                html += '<small>Prêmio</small><strong>' + formatBRL(prizeAmount) + '</strong></div>';
                            }
                            html += '</div></div>';
                        });
                        
                        html += '</div></div>';
                    }
                    
                    // Tabela completa
                    html += '<div style="flex: 1; overflow-y: auto; overflow-x: hidden; min-height: 0;">';
                    html += '<table class="prize-table"><thead><tr>';
                    html += '<th style="width: 60px;">POS</th><th>USUÁRIO</th>';
                    html += '<th style="width: 130px; text-align: right;">PRÊMIO</th>';
                    html += '</tr></thead><tbody>';
                    
                    var minRows = 20;
                    var displayRows = Math.max(minRows, items.length, currentPaidPositions);
                    
                    for (var i = 0; i < displayRows; i++) {
                        var pos = i + 1;
                        var team = items[i];
                        var isPaid = pos <= currentPaidPositions;
                        var percent = isPaid ? ((dist[pos] || 0) * 100).toFixed(1) : 0;
                        var prizeAmount = isPaid ? (currentPrize * (dist[pos] || 0)) : 0;
                        
                        var posDisplay = pos;
                        var posClass = 'prize-pos-cell';
                        if (pos === 1) { posDisplay = '🥇'; posClass += ' podium-gold'; }
                        else if (pos === 2) { posDisplay = '🥈'; posClass += ' podium-silver'; }
                        else if (pos === 3) { posDisplay = '🥉'; posClass += ' podium-bronze'; }
                        
                        var teamName = team ? (team.display_name || team.user_name || team.username || team.name || ('Usuário #' + pos)) : '—';
                        var displayName = team ? (team.display_name || ((team.show_in_listings === false) ? maskUsername(teamName) : teamName)) : '—';
                        var points = team ? (team.points || 0) : '—';
                        
                        html += '<tr style="' + (!team ? 'opacity: 0.4;' : '') + '">';
                        html += '<td class="' + posClass + '">' + posDisplay + '</td>';
                        html += '<td style="font-weight: 500;">' + escapeHtml(displayName) + '</td>';
                        html += '<td class="prize-amount-cell" style="text-align: right; font-weight: 600; color: ' + (isPaid ? '#22c55e' : '#64748b') + ';">' + (isPaid ? formatBRL(prizeAmount) : '—') + '</td>';
                        html += '</tr>';
                    }
                    
                    html += '</tbody></table></div>';
                    
                    // Footer
                    html += '<div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(249, 115, 22, 0.2); font-size: 0.8rem; color: #94a3b8; text-align: center;">';
                    html += '<span>⏰ Atualizado em tempo real</span>';
                    html += '</div>';
                    
                    html += '</div></div>';
                    
                    rankingContainer.innerHTML = html;
                    
                } catch (err) {
                    console.error('❌ Erro ao carregar ranking:', err);
                    rankingContainer.innerHTML = '<div class="text-center text-muted" style="padding: 2rem;"><i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i><p>Erro ao carregar ranking</p></div>';
                }
            };

            function switchTab(tabName) {
                var modalEl = state.modalEl;
                if (!modalEl) return;
                modalEl.querySelectorAll('.rr-fantasy-tab').forEach(function(t) {
                    t.classList.toggle('rr-fantasy-tab--active', t.getAttribute('data-tab') === tabName);
                });
                modalEl.querySelectorAll('.rr-fantasy-panel[data-panel]').forEach(function(p) {
                    p.classList.toggle('rr-fantasy-panel--active', p.getAttribute('data-panel') === tabName);
                });
            }

            function ensureModal(){
                if (state.modalEl && document.body.contains(state.modalEl)) return state.modalEl;
                installStyleOnce();

                var el = document.createElement('div');
                el.className = 'modal fade rr-fantasy-modal';
                el.id = 'rrFantasyLeagueModal';
                el.tabIndex = -1;
                el.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div style="min-width:0;">
                                    <h5 class="modal-title" id="rrFantasyModalTitle">Bolão</h5>
                                    <div class="text-muted small" id="rrFantasyModalSubtitle"></div>
                                </div>
                                <button type="button" class="rr-fantasy-close btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                            </div>
                            <div class="modal-body">
                                <div class="rr-fantasy-modal__meta" id="rrFantasyModalMeta"></div>

                                <!-- TABS -->
                                <div class="rr-fantasy-tabs">
                                    <button class="rr-fantasy-tab rr-fantasy-tab--active" data-tab="build">
                                        <i class="fas fa-user-plus"></i> Montar Equipe
                                    </button>
                                    <button class="rr-fantasy-tab" data-tab="ranking">
                                        <i class="fas fa-trophy"></i> Ranking
                                    </button>
                                    <button class="rr-fantasy-tab" data-tab="myteam">
                                        <i class="fas fa-users"></i> Minha Equipe
                                    </button>
                                </div>

                                <!-- PANEL: BUILD TEAM -->
                                <div class="rr-fantasy-panel rr-fantasy-panel--active" data-panel="build">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <p>Sistema de montagem de equipe será implementado em breve.</p>
                                    </div>
                                </div>

                                <!-- PANEL: RANKING -->
                                <div class="rr-fantasy-panel" data-panel="ranking">
                                    <div class="rr-fantasy-panel__header">
                                        <h6 class="rr-fantasy-panel__title">Ranking do Bolão</h6>
                                        <div class="rr-fantasy-panel__actions">
                                            <button type="button" class="rr-fantasy-btn rr-fantasy-btn--primary" data-action="ranking-refresh">Atualizar</button>
                                            <button type="button" class="rr-fantasy-btn rr-fantasy-btn--ghost" data-action="ranking-toggle">Ver completo</button>
                                        </div>
                                    </div>
                                    <div class="rr-fantasy-ranking-shell" id="rrFantasyModalRanking" aria-live="polite"></div>
                                    <div class="text-muted small mt-2" id="rrFantasyModalRankingHint"></div>
                                </div>

                                <!-- PANEL: MY TEAM -->
                                <div class="rr-fantasy-panel" data-panel="myteam">
                                    <div id="rrFantasyMyTeamContent">
                                        <div class="rr-fantasy-myteam-empty">
                                            <i class="fas fa-users-slash"></i>
                                            <p>Você ainda não tem uma equipe neste bolão</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(el);
                state.modalEl = el;
                state.modalInstance = null;

                // Direct click handler for close button
                var closeBtn = el.querySelector('.rr-fantasy-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (hasBootstrapModal()) {
                            try {
                                var mi = getModalInstance();
                                if (mi && typeof mi.hide === 'function') mi.hide();
                            } catch (err) {
                                hideModalFallback();
                            }
                        } else {
                            hideModalFallback();
                        }
                    });
                }

                el.addEventListener('shown.bs.modal', function(){
                    lockBodyScroll();
                });

                el.addEventListener('hidden.bs.modal', function(){
                    unlockBodyScroll();
                    state.modalLeagueId = null;
                    state.showFull = false;
                    state.builder.loaded = false;
                    state.builder.competitors = [];
                    state.builder.selectedIds = [];
                    state.builder.verifiedOk = false;
                    state.builder.verifiedSig = null;
                });

                return el;
            }

            function getModalInstance(){
                if (!hasBootstrapModal()) return null;
                var el = ensureModal();
                if (state.modalInstance) return state.modalInstance;
                state.modalInstance = bootstrap.Modal.getOrCreateInstance(el);
                return state.modalInstance;
            }

            function hasBootstrapModal(){
                return !!(window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function');
            }

            function lockBodyScroll(){
                if (state.scrollLock && state.scrollLock.active) return;
                var y = window.scrollY || document.documentElement.scrollTop || 0;
                state.scrollLock.y = y;
                state.scrollLock.prev = {
                    position: document.body.style.position,
                    top: document.body.style.top,
                    left: document.body.style.left,
                    right: document.body.style.right,
                    width: document.body.style.width,
                };
                state.scrollLock.active = true;

                document.body.classList.add('rr-modal-open');
                // iOS/Android: overflow:hidden alone may still allow background scroll.
                document.body.style.position = 'fixed';
                document.body.style.top = (-y) + 'px';
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
            }

            function unlockBodyScroll(){
                if (!state.scrollLock || !state.scrollLock.active) {
                    document.body.classList.remove('rr-modal-open');
                    return;
                }
                var y = state.scrollLock.y || 0;
                var prev = state.scrollLock.prev || {};

                state.scrollLock.active = false;
                state.scrollLock.prev = null;

                document.body.classList.remove('rr-modal-open');
                document.body.style.position = prev.position || '';
                document.body.style.top = prev.top || '';
                document.body.style.left = prev.left || '';
                document.body.style.right = prev.right || '';
                document.body.style.width = prev.width || '';

                window.scrollTo(0, y);
            }

            function showModalFallback(){
                var el = ensureModal();
                el.classList.add('rr-fantasy-modal--open');
                lockBodyScroll();
            }

            function hideModalFallback(){
                var el = ensureModal();
                el.classList.remove('rr-fantasy-modal--open');
                unlockBodyScroll();
                state.modalLeagueId = null;
                state.showFull = false;
                state.builder.loaded = false;
                state.builder.competitors = [];
                state.builder.selectedIds = [];
                state.builder.verifiedOk = false;
                state.builder.verifiedSig = null;
            }

            function renderBuilderSlots(){
                var el = document.getElementById('rrFantasyBuilderSlots');
                if (!el) return;
                var ids = state.builder.selectedIds;

                var slots = [];
                for (var i = 0; i < 4; i++) {
                    var id = ids[i] ? String(ids[i]) : '';
                    if (!id) {
                        slots.push(`
                            <div class="rr-fantasy-slot" data-action="builder-slot" data-slot-index="${i}">
                                <div class="rr-fantasy-slot__card">
                                    <div class="rr-card-item" data-nivel="placeholder" style="max-width:140px;">
                                        <div class="hex-card hex-card--mini hex-card--placeholder" style="--card-color: rgba(255,255,255,.18);">
                                            <div class="hex-card__border"></div>
                                            <div class="hex-card__border-line"></div>
                                            <div class="hex-card__inner">
                                                <div class="hex-card__img">
                                                    <div class="hex-card__athlete">
                                                        <div class="rr-fantasy-slot__plus">+</div>
                                                    </div>
                                                </div>
                                                <div class="hex-card__text">
                                                    <h2 class="hex-card__name">Slot ${i + 1}</h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                        continue;
                    }

                    var c = state.builder.competitors.find(function(x){ return String(x.id) === id; }) || null;
                    var card = c ? renderHexMiniCard(c) : ('<div class="text-muted small">#' + escapeHtml(id) + '</div>');
                    var isCaptain = i === 0;
                    slots.push(`
                        <div class="rr-fantasy-slot rr-fantasy-slot--filled ${isCaptain ? 'rr-fantasy-slot--captain' : ''}">
                            ${isCaptain ? '<div class="rr-fantasy-slot__badge">CAPITÃO</div>' : ''}
                            <button type="button" class="rr-fantasy-slot__remove" data-action="builder-remove" data-competitor-id="${escapeHtml(id)}" aria-label="Remover">×</button>
                            <div class="rr-fantasy-slot__card">${card}</div>
                        </div>
                    `);
                }
                el.innerHTML = slots.join('');
                updateBuilderButtons();
            }

            function categoryKey(c){
                var raw = (c && (c.categoria || c.nivel)) ? String(c.categoria || c.nivel) : '';
                raw = raw.toLowerCase().trim();
                if (['favorito','elite','legado','presilha','ascendente','competidor'].includes(raw)) return raw;
                return raw || 'desconhecido';
            }

            function categoryColor(key){
                var map = {
                    favorito: 'hsl(43, 100%, 50%)',
                    elite: 'hsl(25, 95%, 53%)',
                    legado: 'hsl(220, 10%, 70%)',
                    ascendente: 'hsl(220, 10%, 70%)',
                    presilha: 'hsl(145, 63%, 42%)',
                    competidor: 'hsl(145, 63%, 42%)'
                };
                return map[key] || 'hsl(25, 100%, 50%)';
            }

            function categoryBadge(key){
                var map = { favorito: '⭐', elite: '❤️', legado: '📈', ascendente: '📈', presilha: '👤', competidor: '👤' };
                return map[key] || '🔥';
            }

            function renderHexCard(c, opts){
                opts = opts || {};
                var key = categoryKey(c);
                var color = categoryColor(key);
                var badge = categoryBadge(key);
                var foto = c && c.foto ? imgUrl(c.foto) : '';
                var pts = (c && c.pontuacao_total !== null && c.pontuacao_total !== undefined) ? parseInt(c.pontuacao_total, 10) : 0;
                if (isNaN(pts)) pts = 0;
                var last = (c && c.last_points !== null && c.last_points !== undefined) ? parseInt(c.last_points, 10) : null;
                if (last !== null && isNaN(last)) last = null;
                var neg = (c && c.count_negativas_total !== null && c.count_negativas_total !== undefined) ? parseInt(c.count_negativas_total, 10) : null;
                if (neg !== null && isNaN(neg)) neg = null;
                var div = c.modalidade_divisao || c.stats_divisao || '';

                var statA = (div ? String(div) : (c.cidade ? String(c.cidade) : ''));
                var statB = (last === null ? '—' : ((last > 0 ? '+' : '') + String(last)));
                var statBStyle = (last !== null && last < 0) ? 'style="color:#b91c1c; background: rgba(185,28,28,.15);"' : '';

                var actionText = opts.actionText || 'Selecionar';
                var action = opts.action || 'builder-toggle';
                var actionId = opts.actionId || (c ? c.id : '');
                var disabled = opts.disabled ? 'disabled' : '';

                return `
                    <div class="rr-card-item" data-nivel="${escapeHtml(key)}" style="max-width:200px;">
                      <div class="hex-card" style="--card-color: ${escapeHtml(color)};">
                        <div class="hex-card__border"></div>
                        <div class="hex-card__border-line"></div>
                        <div class="hex-card__inner">
                          <div class="hex-card__img">
                            <div class="hex-card__badge">${escapeHtml(badge)}</div>
                            <div class="hex-card__athlete">
                              <img src="${escapeHtml(foto)}" alt="" onerror="this.style.display='none'">
                            </div>
                          </div>
                          <div class="hex-card__text">
                            <div class="hex-card__type" style="--bg-color: ${escapeHtml(color)};">${escapeHtml(String(key).toUpperCase())}</div>
                            <h2 class="hex-card__name">${escapeHtml(c.nome || 'Competidor')}</h2>
                            <p class="hex-card__points">${escapeHtml(String(pts))} PTS</p>
                            <div class="hex-card__stats">
                              <span class="hex-card__stat">${escapeHtml(statA)}</span>
                              <span class="hex-card__stat" ${statBStyle}>${escapeHtml(statB)}</span>
                            </div>
                            <div class="hex-card__actions">
                              <button type="button" class="hex-card__btn" ${disabled} data-action="${escapeHtml(action)}" data-competitor-id="${escapeHtml(String(actionId))}">${escapeHtml(actionText)}</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                `;
            }

                        function renderHexMiniCard(c){
                                var key = categoryKey(c);
                                var color = categoryColor(key);
                            var foto = competitorPhotoUrl(c);

                                return `
                                        <div class="rr-card-item" data-nivel="${escapeHtml(key)}" style="max-width:140px;">
                                            <div class="hex-card hex-card--mini" style="--card-color: ${escapeHtml(color)};">
                                                <div class="hex-card__border"></div>
                                                <div class="hex-card__border-line"></div>
                                                <div class="hex-card__inner">
                                                    <div class="hex-card__img">
                                                        <div class="hex-card__athlete">
                                                            <img src="${escapeHtml(foto)}" alt="" onerror="this.style.display='none'">
                                                        </div>
                                                    </div>
                                                    <div class="hex-card__text">
                                                        <h2 class="hex-card__name">${escapeHtml(c.nome || 'Competidor')}</h2>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                `;
                        }

            function renderBuilderList(){
                var el = document.getElementById('rrFantasyBuilderList');
                if (!el) return;
                if (!state.builder.loaded) {
                    el.innerHTML = '<div class="text-muted small">Clique em “Carregar competidores”.</div>';
                    return;
                }

                if (!state.builder.competitors.length) {
                    el.innerHTML = '<div class="text-muted small">Nenhum competidor disponível.</div>';
                    return;
                }

                var filter = String(state.builder.filter || '').toLowerCase().trim();
                var items = state.builder.competitors;
                if (filter) {
                    items = items.filter(function(c){
                        return String(c.nome || '').toLowerCase().includes(filter);
                    });
                }

                if (!items.length) {
                    el.innerHTML = '<div class="text-muted small">Nenhum competidor encontrado para este filtro.</div>';
                    return;
                }

                var html = '<div class="rr-fantasy-hex-mini-grid">';
                items.forEach(function(c){
                    var id = String(c.id);
                    var selectedIndex = state.builder.selectedIds.indexOf(id);
                    var isSelected = selectedIndex >= 0;
                    var disabled = (!isSelected && state.builder.selectedIds.length >= 4);
                    html += `
                        <button type="button" class="rr-fantasy-hex-mini-btn ${isSelected ? 'is-selected' : ''}" ${disabled ? 'disabled' : ''}
                            data-action="builder-toggle" data-competitor-id="${escapeHtml(id)}" aria-pressed="${isSelected ? 'true' : 'false'}">
                            ${renderHexMiniCard(c)}
                        </button>
                    `;
                });
                html += '</div>';
                el.innerHTML = html;
            }

            async function openLeagueModal(leagueId, initialTab){
                var league = state.leagues.find(function(l){ return String(l.id) === String(leagueId); });
                if (!league) return;
                state.modalLeagueId = league.id;
                state.showFull = false;

                var modalEl = ensureModal();

                // Abre na aba especificada ou na aba build (padrão)
                switchTab(initialTab || 'build');

                modalEl.querySelector('#rrFantasyModalTitle').textContent = league.name || 'Bolão';
                modalEl.querySelector('#rrFantasyModalSubtitle').textContent = (league.rodeio && league.rodeio.nome ? league.rodeio.nome : '')
                    + (league.modalidade && league.modalidade.nome ? (' • ' + league.modalidade.nome) : '');

                var meta = modalEl.querySelector('#rrFantasyModalMeta');
                if (meta) {
                    meta.innerHTML = [
                        '<span class="rr-fantasy-pill">Prêmio: <strong>' + escapeHtml(formatBRL(league.prize_pool)) + '</strong></span>',
                        '<span class="rr-fantasy-pill">Inscritos: <strong>' + escapeHtml(String(league.teams_count || 0)) + '</strong></span>',
                        (league.divisao ? '<span class="rr-fantasy-pill">Divisão: <strong>' + escapeHtml(league.divisao) + '</strong></span>' : '')
                    ].join(' ');
                }

                // Reset builder UI
                state.builder.loaded = false;
                state.builder.competitors = [];
                state.builder.selectedIds = [];
                state.builder.verifiedOk = false;
                state.builder.verifiedSig = null;
                var teamName = modalEl.querySelector('#rrFantasyTeamName');
                if (teamName) teamName.value = '';
                var search = modalEl.querySelector('#rrFantasyCompetitorSearch');
                if (search) search.value = '';
                state.builder.filter = '';
                var hint = modalEl.querySelector('#rrFantasyBuilderHint');
                if (hint) hint.textContent = '';
                var vr = modalEl.querySelector('#rrFantasyVerifyResult');
                if (vr) vr.innerHTML = '';
                renderBuilderSlots();
                renderBuilderList();
                updateBuilderButtons();

                // Load initial ranking (Top 10 from top30)
                await loadModalRanking(false);

                // Auto-load competitors for team building
                loadBuilderCompetitors();

                if (hasBootstrapModal()) {
                    var inst = getModalInstance();
                    if (inst && typeof inst.show === 'function') inst.show();
                } else {
                    showModalFallback();
                }
            }

            // 🏆 NOVO MODAL APENAS PARA RANKING (delega para o unificado)
            async function openRankingOnlyModal(leagueId) {
                console.log('🚀 openRankingOnlyModal (delegando para window.openRankingOnlyModal):', leagueId);
                if (window.openRankingOnlyModal) {
                    return window.openRankingOnlyModal(leagueId);
                }
            }

            async function loadModalRanking(full){
                var modalEl = ensureModal();
                var list = modalEl.querySelector('#rrFantasyModalRanking');
                var hint = modalEl.querySelector('#rrFantasyModalRankingHint');
                if (hint) hint.textContent = '';
                if (!list) return;
                list.innerHTML = '<div class="text-muted small">Carregando ranking...</div>';

                var leagueId = state.modalLeagueId;
                var res = await loadRanking(leagueId, full ? 'full' : 'top30');
                if (!res.ok) {
                    list.innerHTML = '<div class="text-muted small">Ranking indisponível.</div>';
                    if (res.http && res.http.status === 403 && hint) {
                        hint.textContent = 'Ranking completo requer Premium.';
                    }
                    return;
                }

                var meta = res.meta || {};
                var league = state.leagues.find(function(l){ return String(l.id) === String(state.modalLeagueId); }) || {};
                var items = normalizeRankingItems((meta && meta.ranking) || res.data || res.ranking || res);
                if (!full) items = items.slice(0, 10);

                if (!items.length) {
                    list.innerHTML = `
                        <div class="rr-fantasy-ranking-empty">
                            <span class="rr-fantasy-ranking-empty__badge"><i class="fas fa-inbox"></i> Sem equipes</span>
                            <div class="rr-fantasy-ranking-empty__title">Ainda não há equipes no bolão</div>
                            <div class="rr-fantasy-ranking-empty__text">Assim que as inscrições começarem a entrar, o pódio e a lista do ranking aparecem aqui com a identidade visual do Rei do Rodeio.</div>
                        </div>
                    `;
                    return;
                }

                var topItems = items.slice(0, 3);
                var listItems = items.slice(3);
                if (!full) listItems = listItems.slice(0, 7);

                var leaderPoints = topItems.length ? (rankingNumericPoints(topItems[0]) || 0) : 0;
                var totalTeams = parseInt(meta.total_teams, 10) || items.length || 0;
                var paidPositions = parseInt(meta.display_paid_positions || meta.paid_positions || meta.projected_paid_positions, 10) || Math.max(3, Math.floor((totalTeams || items.length || 1) * 0.1));
                var prizePool = parseFloat(meta.prize_pool) || 0;
                var entryPrice = parseFloat(meta.entry_price) || 0;
                var statusLabel = meta.is_finalized ? 'Encerrado' : 'Ao vivo';
                var statusNote = meta.is_finalized ? 'Resultados finais' : 'Atualização manual';
                var leagueName = meta.league_name || league.name || 'Ranking do Bolão';
                var rodeioName = (league.rodeio && league.rodeio.nome) ? league.rodeio.nome : '';
                var modalityName = (league.modalidade && league.modalidade.nome) ? league.modalidade.nome : '';
                var metaText = rodeioName + (modalityName ? (' • ' + modalityName) : '');

                var podiumOrder = [
                    { index: 1, medal: '🥈', accent: '#3b82f6', tone: 'silver', rankLabel: '2º lugar', rankText: 'Vice-líder' },
                    { index: 0, medal: '🥇', accent: '#f97316', tone: 'champion', rankLabel: '1º lugar', rankText: 'Campeão' },
                    { index: 2, medal: '🥉', accent: '#f8fafc', tone: 'bronze', rankLabel: '3º lugar', rankText: 'Top 3' },
                ];

                var podiumHtml = podiumOrder.map(function(slot){
                    var row = topItems[slot.index];
                    if (!row) {
                        return `
                            <article class="rr-fantasy-podium-card rr-fantasy-podium-card--${slot.tone}">
                                <div class="rr-fantasy-podium-card__medal">${slot.medal}</div>
                                <div class="rr-fantasy-podium-card__badge">${slot.rankLabel}</div>
                                <div class="rr-fantasy-podium-card__avatar"><span>RR</span></div>
                                <div class="rr-fantasy-podium-card__rank">Sem equipe</div>
                                <h4 class="rr-fantasy-podium-card__name">Aguardando participante</h4>
                                <div class="rr-fantasy-podium-card__meta">
                                    <div class="rr-fantasy-podium-card__chip"><span>Pontos</span><strong>—</strong></div>
                                    <div class="rr-fantasy-podium-card__chip"><span>Diferença</span><strong>—</strong></div>
                                </div>
                            </article>
                        `;
                    }

                    return buildRankingPodiumCard(row, slot, leaderPoints);
                }).join('');

                var rowsHtml = listItems.length ? listItems.map(function(row, idx){
                    return buildRankingRow(row, idx + 3, leaderPoints);
                }).join('') : `
                    <div class="rr-fantasy-ranking-empty">
                        <span class="rr-fantasy-ranking-empty__badge"><i class="fas fa-award"></i> Top 3 definido</span>
                        <div class="rr-fantasy-ranking-empty__title">O ranking parou no pódio por enquanto</div>
                        <div class="rr-fantasy-ranking-empty__text">Os três primeiros já estão destacados acima. Abra a visão completa quando quiser ver o restante da classificação.</div>
                    </div>
                `;

                list.innerHTML = `
                    <section class="rr-fantasy-ranking-shell" aria-label="Ranking do bolão ${escapeHtml(leagueName)}">
                        <div class="rr-fantasy-ranking-hero">
                            <div class="rr-fantasy-ranking-hero__topline">
                                <span class="rr-fantasy-ranking-badge">${escapeHtml(statusLabel)}</span>
                                <span class="rr-fantasy-ranking-badge rr-fantasy-ranking-badge--alt">${escapeHtml(full ? 'Lista completa' : 'Top 10')}</span>
                            </div>

                            <div class="rr-fantasy-ranking-hero__grid">
                                <div class="rr-fantasy-ranking-hero__copy">
                                    <p class="rr-fantasy-ranking-hero__eyebrow">Rei do Rodeio</p>
                                    <h3 class="rr-fantasy-ranking-hero__title">${escapeHtml(leagueName)}</h3>
                                    <p>${escapeHtml(metaText ? (metaText + ' • ') : '')}${escapeHtml(meta.is_finalized ? 'Ranking fechado com a definição final do bolão.' : 'Ranking ao vivo com visual premium e atualização sob demanda.')}</p>

                                    <div class="rr-fantasy-ranking-stats">
                                        <div class="rr-fantasy-ranking-stat">
                                            <span>Equipes</span>
                                            <strong>${escapeHtml(String(totalTeams || 0))}</strong>
                                        </div>
                                        <div class="rr-fantasy-ranking-stat">
                                            <span>Premiadas</span>
                                            <strong>${escapeHtml(String(paidPositions || 0))}</strong>
                                        </div>
                                        <div class="rr-fantasy-ranking-stat">
                                            <span>Prêmio</span>
                                            <strong>${prizePool > 0 ? escapeHtml(formatBRL(prizePool)) : 'Pontos acumulados'}</strong>
                                        </div>
                                        <div class="rr-fantasy-ranking-stat">
                                            <span>Entrada</span>
                                            <strong>${entryPrice > 0 ? escapeHtml(formatBRL(entryPrice)) : 'Gratuito'}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="rr-fantasy-ranking-podium" aria-label="Pódio do ranking">
                                    ${podiumHtml}
                                </div>
                            </div>
                        </div>

                        <div class="rr-fantasy-ranking-list-wrap">
                            <div class="rr-fantasy-ranking-list-header">
                                <div>
                                    <h4 class="rr-fantasy-ranking-list-header__title">Classificação geral</h4>
                                    <div class="rr-fantasy-ranking-list-header__meta">${escapeHtml(statusNote)} • ${escapeHtml(full ? 'exibindo todos os participantes' : 'exibindo os 10 primeiros')}</div>
                                </div>
                                <div class="rr-fantasy-ranking-list-header__meta">${escapeHtml(String(listItems.length || 0))} linhas na visão atual</div>
                            </div>

                            <div class="rr-fantasy-ranking-list" role="list">
                                ${rowsHtml}
                            </div>
                        </div>
                    </section>
                `;

                var toggleBtn = modalEl.querySelector('[data-action="ranking-toggle"]');
                if (toggleBtn) {
                    toggleBtn.innerHTML = state.showFull
                        ? '<i class="fas fa-list"></i> Ver top 10'
                        : '<i class="fas fa-expand"></i> Ver completo';
                }
            }

            async function refreshRanking(){
                var league = state.leagues.find(function(l){ return String(l.id) === String(state.modalLeagueId); });
                if (!league || !league.rodeio || !league.rodeio.id) return;

                var modalEl = ensureModal();
                var hint = modalEl.querySelector('#rrFantasyModalRankingHint');
                if (hint) hint.textContent = 'Atualizando...';

                var payload = {
                    fantasy_event_id: league.rodeio.id,
                    league_id: league.id,
                    view: state.showFull ? 'full' : 'top30'
                };

                var resp = await fetchJson(apiUrl('/api/fantasy/ranking/refresh'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!resp.__http.ok || !resp.success) {
                    if (resp.__http.status === 401) {
                        if (hint) hint.textContent = 'Para entrar, use o botão Entrar no menu.';
                    } else if (resp.__http.status === 403) {
                        if (hint) hint.textContent = resp.message || 'Requer Premium.';
                    } else {
                        if (hint) hint.textContent = resp.message || 'Falha ao atualizar.';
                    }
                    return;
                }

                var requestId = resp.request_id;
                if (!requestId) {
                    if (hint) hint.textContent = 'Atualização enfileirada.';
                    return;
                }

                // Poll result
                for (var i = 0; i < 12; i++) {
                    await new Promise(function(r){ setTimeout(r, 1000); });
                    var r = await fetchJson(apiUrl('/api/fantasy/ranking/result/' + encodeURIComponent(requestId)));
                    if (r && r.success && r.status === 'ready') {
                        if (!r.ranking) {
                            if (hint) hint.textContent = 'Ranking indisponível.';
                        } else {
                            if (hint) hint.textContent = 'Atualizado agora.';
                        }
                        await loadModalRanking(state.showFull);
                        return;
                    }
                }

                if (hint) hint.textContent = 'Aguardando processamento...';
            }

            async function loadBuilderCompetitors(){
                var leagueId = state.modalLeagueId;
                if (!leagueId) return;
                var modalEl = ensureModal();
                var hint = modalEl.querySelector('#rrFantasyBuilderHint');
                if (hint) hint.textContent = 'Carregando competidores...';

                var data = await fetchJson(apiUrl('/api/fantasy/leagues/' + encodeURIComponent(leagueId) + '/available-competitors'));
                if (!data.__http.ok || !data.success) {
                    if (hint) hint.textContent = data.message || 'Falha ao carregar competidores.';
                    state.builder.loaded = true;
                    state.builder.competitors = [];
                    renderBuilderSlots();
                    renderBuilderList();
                    updateBuilderButtons();
                    return;
                }

                state.builder.loaded = true;
                state.builder.competitors = Array.isArray(data.data) ? data.data : [];
                state.builder.pointsHidden = !!(data && data.meta && data.meta.points_hidden_until_final);
                if (hint) {
                    var applied = data && data.meta ? !!data.meta.divisao_filter_applied : false;
                    var leagueDiv = data && data.meta ? data.meta.league_divisao : null;
                    if (state.builder.pointsHidden) {
                        hint.textContent = 'Pontuações ocultas até a finalização da modalidade.';
                    } else if (leagueDiv && !applied) {
                        hint.textContent = 'Aviso: este bolão possui divisão, mas o filtro não foi aplicado. Verifique o vínculo de divisões na modalidade.';
                    } else {
                        hint.textContent = '';
                    }
                }
                renderBuilderSlots();
                renderBuilderList();
                updateBuilderButtons();
            }

            async function verifyTeam(){
                var leagueId = state.modalLeagueId;
                if (!leagueId) return;
                var modalEl = ensureModal();
                var hint = modalEl.querySelector('#rrFantasyBuilderHint');
                var vr = modalEl.querySelector('#rrFantasyVerifyResult');

                var ids = state.builder.selectedIds.map(function(x){ return parseInt(x, 10); }).filter(Boolean);
                if (ids.length !== 4) {
                    if (hint) hint.textContent = 'Selecione 4 competidores para verificar.';
                    return;
                }

                if (hint) hint.textContent = 'Verificando equipe...';
                if (vr) vr.innerHTML = '';

                var payload = { competitor_ids: ids };
                var resp = await fetchJson(apiUrl('/api/fantasy/leagues/' + encodeURIComponent(leagueId) + '/teams/verify'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!resp.__http.ok || !resp.success) {
                    state.builder.verifiedOk = false;
                    state.builder.verifiedSig = null;
                    if (resp.__http.status === 401) {
                        if (hint) hint.textContent = 'Para entrar, use o botão Entrar no menu.';
                    } else {
                        if (hint) hint.textContent = resp.message || 'Falha ao verificar.';
                    }
                    updateBuilderButtons();
                    return;
                }

                state.builder.verifiedOk = !!resp.ok;
                state.builder.verifiedSig = selectionSig(state.builder.selectedIds);
                updateBuilderButtons();

                if (hint) hint.textContent = resp.ok ? 'Equipe OK. Você pode criar agora.' : 'Equipe inválida. Ajuste os competidores.';
                if (vr) {
                    var rows = Array.isArray(resp.data) ? resp.data : [];
                    vr.innerHTML = rows.map(function(r){
                        var ok = !!r.ok;
                        var reasons = Array.isArray(r.reasons) ? r.reasons : [];
                        return '<div class="rr-fantasy-ranking-row" style="align-items:center;">'
                            + '<span class="rr-fantasy-ranking-row__pos" style="width:60px; color:' + (ok ? '#22c55e' : '#f59e0be6') + ';">' + (ok ? 'OK' : 'ERRO') + '</span>'
                            + '<span class="rr-fantasy-ranking-row__name">' + escapeHtml(r.nome || ('#' + r.id)) + '</span>'
                            + '<span class="rr-fantasy-ranking-row__pts" style="min-width:220px; text-align:right;">' + escapeHtml(ok ? 'apto' : (reasons.join(' • ') || 'não apto')) + '</span>'
                            + '</div>';
                    }).join('');
                }
            }

            async function saveTeam(){
                var leagueId = state.modalLeagueId;
                if (!leagueId) return;
                var modalEl = ensureModal();
                var hint = modalEl.querySelector('#rrFantasyBuilderHint');

                var teamName = (modalEl.querySelector('#rrFantasyTeamName') || {}).value || '';
                teamName = teamName.trim();
                if (!teamName) {
                    if (hint) hint.textContent = 'Informe o nome do time.';
                    return;
                }

                var selectedIds = state.builder.selectedIds.map(function(x){ return parseInt(x, 10); }).filter(Boolean);
                if (selectedIds.length !== 4) {
                    if (hint) hint.textContent = 'Selecione 4 competidores.';
                    return;
                }

                if (!(state.builder.verifiedOk && state.builder.verifiedSig === selectionSig(state.builder.selectedIds))) {
                    if (hint) hint.textContent = 'Verifique a equipe antes de criar.';
                    return;
                }

                if (hint) hint.textContent = 'Salvando...';

                var payload = {
                    team_name: teamName,
                    competitor_ids: selectedIds,
                    captain_id: selectedIds.length ? selectedIds[0] : null,
                };

                var resp = await fetchJson(apiUrl('/api/fantasy/leagues/' + encodeURIComponent(leagueId) + '/teams'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!resp.__http.ok || !resp.success) {
                    if (resp.__http.status === 401) {
                        if (hint) hint.textContent = 'Para entrar, use o botão Entrar no menu.';
                    } else {
                        if (hint) hint.textContent = resp.message || 'Falha ao salvar.';
                    }
                    return;
                }

                if (hint) hint.textContent = 'Equipe criada!';
            }

            function bindOnce(rootEl){
                if (state.bound) return;
                state.bound = true;

                // Clicks inside the fantasy section (tier switch + open league)
                rootEl.addEventListener('click', function(e){
                    var tierCard = e.target.closest && e.target.closest('#rrFantasySubmenu .rr-epic-submenu__btn');
                    if (tierCard && rootEl.contains(tierCard)) {
                        var sectionEl = rootEl.querySelector('[data-section="equipes"]');
                        if (!sectionEl) return;
                        var cards = Array.from(sectionEl.querySelectorAll('#rrFantasySubmenu .rr-epic-submenu__btn'));
                        cards.forEach(function(c){ c.classList.remove('is-active'); });
                        tierCard.classList.add('is-active');
                        state.selectedTier = tierCard.getAttribute('data-filter') || 'Todos';
                        renderLeagues(sectionEl);
                        return;
                    }

                }, true);

                // Modal lives in document.body, so handle its clicks at document-level.
                document.addEventListener('click', function(e){
                    var modalEl = state.modalEl;
                    if (!modalEl || !modalEl.contains(e.target)) return;

                    // Close button (fallback when bootstrap isn't present)
                    var closeBtn = e.target.closest && e.target.closest('[data-bs-dismiss="modal"], .btn-close, .rr-fantasy-close');
                    if (closeBtn) {
                        if (hasBootstrapModal()) {
                            try {
                                var mi = getModalInstance();
                                if (mi && typeof mi.hide === 'function') mi.hide();
                            } catch (e2) {}
                        } else {
                            hideModalFallback();
                        }
                        return;
                    }

                    // Tab switching
                    var tabBtn = e.target.closest && e.target.closest('[data-tab]');
                    if (tabBtn) {
                        var tabName = tabBtn.getAttribute('data-tab');
                        switchTab(tabName);
                        return;
                    }

                    // Backdrop click closes (fallback)
                    if (!hasBootstrapModal() && e.target === modalEl) {
                        hideModalFallback();
                        return;
                    }

                    var actBtn = e.target.closest && e.target.closest('[data-action]');
                    if (!actBtn) return;
                    var act = actBtn.getAttribute('data-action');

                    if (act === 'ranking-toggle') {
                        state.showFull = !state.showFull;
                        loadModalRanking(state.showFull);
                        return;
                    }
                    if (act === 'ranking-refresh') {
                        refreshRanking();
                        return;
                    }
                    if (act === 'builder-load') {
                        loadBuilderCompetitors();
                        return;
                    }
                    if (act === 'builder-verify') {
                        verifyTeam();
                        return;
                    }
                    if (act === 'builder-save') {
                        saveTeam();
                        return;
                    }
                    if (act === 'builder-toggle') {
                        var id = actBtn.getAttribute('data-competitor-id');
                        if (!id) return;
                        var ids = state.builder.selectedIds.slice();
                        var idx = ids.indexOf(String(id));
                        if (idx >= 0) {
                            ids.splice(idx, 1);
                        } else {
                            if (ids.length >= 4) return;
                            ids.push(String(id));
                        }
                        setSelectedIds(ids);
                        return;
                    }
                    if (act === 'builder-remove') {
                        var rid = actBtn.getAttribute('data-competitor-id');
                        if (!rid) return;
                        var current = state.builder.selectedIds.slice();
                        var rIdx = current.indexOf(String(rid));
                        if (rIdx >= 0) current.splice(rIdx, 1);
                        setSelectedIds(current);
                        return;
                    }
                }, true);

                // Search field (input events don't bubble through capture reliably across browsers)
                document.addEventListener('input', function(ev){
                    var t = ev && ev.target ? ev.target : null;
                    if (!t || t.id !== 'rrFantasyCompetitorSearch') return;
                    state.builder.filter = String(t.value || '');
                    renderBuilderList();
                }, true);
            }

            async function loadLeagues(sectionEl){
                var grid = sectionEl.querySelector('#rrFantasyLeaguesGrid');
                if (grid) grid.innerHTML = '<div class="text-muted small">Carregando bolões...</div>';
                var empty = sectionEl.querySelector('#rrFantasyLeaguesEmpty');
                if (empty) empty.style.display = 'none';

                var leaguesUrl = apiUrl('/api/fantasy/leagues?only_live=0&only_active=1');
                window.__rrFantasyDebug = { baseUrl: BASE_URL, leaguesUrl: leaguesUrl };
                try { console.log('[RRFantasy] baseUrl=', BASE_URL, 'leaguesUrl=', leaguesUrl); } catch (e) {}

                var data = await fetchJson(leaguesUrl);
                state.lastLoadedAt = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });

                if (!data.__http.ok || !data.success) {
                    var msg = 'Não foi possível carregar bolões.';
                    if (data && data.__http) msg += ' (' + data.__http.status + ')';
                    if (data && data.message) msg += ' ' + data.message;
                    if (grid) grid.innerHTML = '<div class="text-muted small">' + escapeHtml(msg) + '</div>';
                    if (empty) { empty.textContent = msg; empty.style.display = ''; }

                    var setText = function(id, val){
                        var el = sectionEl.querySelector('#' + id);
                        if (el) el.textContent = val;
                    };
                    setText('rrFantasyTopPrizeLeague', 'Falha ao carregar');
                    setText('rrFantasyTopPrizeValue', '—');
                    setText('rrFantasyPremiumTitle', 'Falha ao carregar');
                    var note = sectionEl.querySelector('#rrFantasyPremiumNote');
                    if (note) note.textContent = msg;
                    return;
                }

                state.leagues = Array.isArray(data.data) ? data.data : [];

                // If the current tier has no leagues, switch to the first tier that has at least one.
                (function autoSelectTier(){
                    var tiers = ['Todos', 'Premium', 'R$20', 'R$50', 'R$100'];
                    var hasInTier = function(t){
                        if (t === 'Todos') return state.leagues.length > 0;
                        return state.leagues.some(function(l){ return tierOf(l) === t; });
                    };
                    if (!hasInTier(state.selectedTier)) {
                        for (var i = 0; i < tiers.length; i++) {
                            if (hasInTier(tiers[i])) { state.selectedTier = tiers[i]; break; }
                        }
                    }
                })();

                // Sync active button with selected tier
                (function syncActiveButton(){
                    var btns = sectionEl.querySelectorAll('#rrFantasySubmenu .rr-epic-submenu__btn');
                    btns.forEach(function(btn){
                        var f = btn.getAttribute('data-filter') || '';
                        if (f === state.selectedTier) {
                            btn.classList.add('is-active');
                        } else {
                            btn.classList.remove('is-active');
                        }
                    });
                })();

                updateTierCounts(sectionEl);
                renderLeagues(sectionEl);
                // Ranking is shown when opening a league (modal), not as separate cards here.
            }

            function init(rootEl){
                try {
                    if (!rootEl) return;
                    var sectionEl = rootEl.querySelector('[data-section="equipes"]');
                    if (!sectionEl) return;
                    
                    if (!rootEl.__rrStatsBound) {
                        rootEl.__rrStatsBound = true;
                        bindOnce(rootEl);
                        installStyleOnce();
                    }
                    
                    // Sempre recarrega os bolões quando retorna ao menu
                    loadLeagues(sectionEl);
                } catch (err) {
                    try { console.error('[RRFantasy] init failed', err); } catch (e) {}
                    try {
                        var sectionEl2 = rootEl && rootEl.querySelector ? rootEl.querySelector('[data-section="equipes"]') : null;
                        var grid2 = sectionEl2 ? sectionEl2.querySelector('#rrFantasyLeaguesGrid') : null;
                        if (grid2) grid2.innerHTML = '<div class="text-muted small">Erro ao iniciar Bolão. Veja o console.</div>';
                    } catch (e2) {}
                }
            }

            return { init: init };
        })();

        // Stats (estatisticas): filter + search + pagination.
        // Must live in the hub page because sections are injected via AJAX.
        window.RRStats = window.RRStats || (function(){
            var pendingOpenKey = 'rr_stats_open_target';

            function normalizePendingOpen(target){
                if (!target) return null;

                var id = Number(target.id || target.competitor_id || 0);
                if (!id) return null;

                return {
                    id: id,
                    name: String(target.name || target.nome || '').trim()
                };
            }

            function queuePendingOpen(target){
                var normalized = normalizePendingOpen(target);
                if (!normalized) return false;

                window.__rrPendingStatsOpen = normalized;

                try {
                    sessionStorage.setItem(pendingOpenKey, JSON.stringify(normalized));
                } catch (err) {}

                return true;
            }

            function readPendingOpen(){
                var normalized = normalizePendingOpen(window.__rrPendingStatsOpen);
                if (normalized) return normalized;

                try {
                    var stored = sessionStorage.getItem(pendingOpenKey);
                    if (!stored) return null;
                    return normalizePendingOpen(JSON.parse(stored));
                } catch (err) {
                    return null;
                }
            }

            function clearPendingOpen(){
                window.__rrPendingStatsOpen = null;

                try {
                    sessionStorage.removeItem(pendingOpenKey);
                } catch (err) {}
            }

            function consumePendingOpen(rootEl){
                if (!rootEl || typeof window.abrirModalStats !== 'function') return false;

                var pending = readPendingOpen();
                if (!pending) return false;

                var btn = rootEl.querySelector('.verTodasBtn[data-id="' + String(pending.id) + '"]');
                if (!btn) return false;

                try {
                    clearPendingOpen();
                    window.abrirModalStats(btn);
                    return true;
                } catch (err) {
                    queuePendingOpen(pending);
                    return false;
                }
            }

            function initClaimModal(rootEl){
                if (!rootEl || rootEl.__rrStatsClaimBound) return;

                var modal = rootEl.querySelector('#rrStatsClaimModal');
                var openBtn = rootEl.querySelector('#rrStatsClaimOpenBtn');
                var closeBtn = rootEl.querySelector('#rrStatsClaimCloseBtn');
                var form = rootEl.querySelector('#rrStatsClaimForm');
                var submitBtn = rootEl.querySelector('#rrStatsClaimSubmitBtn');
                var feedback = rootEl.querySelector('#rrStatsClaimFeedback');
                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                var storeUrl = @json(route('competitor_requests.store'));

                if (!modal || !openBtn || !form) return;

                rootEl.__rrStatsClaimBound = true;

                function setFeedback(message, type) {
                    if (!feedback) return;
                    feedback.textContent = message || '';
                    feedback.className = 'rr-stats-claim-feedback is-visible ' + (type === 'success' ? 'is-success' : 'is-error');
                }

                function clearFeedback() {
                    if (!feedback) return;
                    feedback.textContent = '';
                    feedback.className = 'rr-stats-claim-feedback';
                }

                function openModal() {
                    if (openBtn.disabled) return;
                    clearFeedback();
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal() {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                }

                openBtn.addEventListener('click', function(){
                    openModal();
                });

                if (closeBtn) {
                    closeBtn.addEventListener('click', function(){
                        closeModal();
                    });
                }

                modal.addEventListener('click', function(event){
                    if (event.target === modal) {
                        closeModal();
                    }
                });

                if (!window.__rrStatsClaimEscapeBound) {
                    window.__rrStatsClaimEscapeBound = true;
                    document.addEventListener('keydown', function(event){
                        if (event.key !== 'Escape') return;
                        document.querySelectorAll('.rr-stats-claim-modal.is-open').forEach(function(openModalEl){
                            openModalEl.classList.remove('is-open');
                            openModalEl.setAttribute('aria-hidden', 'true');
                        });
                        document.body.classList.remove('overflow-hidden');
                    });
                }

                form.addEventListener('submit', async function(event){
                    event.preventDefault();
                    clearFeedback();

                    var formData = new FormData(form);

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.dataset.originalText = submitBtn.textContent;
                        submitBtn.textContent = 'Enviando...';
                    }

                    try {
                        var response = await fetch(storeUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        var data = await response.json().catch(function () { return {}; });
                        if (!response.ok || data.success !== true) {
                            var errorMessage = Array.isArray(data.errors)
                                ? data.errors.join(' ')
                                : (data.message || 'Não foi possível enviar sua solicitação agora.');
                            throw new Error(errorMessage);
                        }

                        setFeedback(data.message || 'Solicitação enviada com sucesso.', 'success');
                        openBtn.disabled = true;
                        openBtn.textContent = 'Solicitação em análise';

                        window.setTimeout(function(){
                            closeModal();
                        }, 1400);
                    } catch (error) {
                        setFeedback(error.message || 'Não foi possível enviar sua solicitação agora.', 'error');
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = submitBtn.dataset.originalText || 'Enviar solicitação';
                        }
                    }
                });
            }

            function init(rootEl){
                try {
                    if (!rootEl) return;

                    initClaimModal(rootEl);

                    var grid = rootEl.querySelector('#rrCardsGrid');
                    if (!grid) return;

                    var items = Array.from(grid.querySelectorAll('.rr-stats-item, .rr-card-item'));
                    if (!items.length) return;

                    var prevBtn = rootEl.querySelector('#prevPageBtn');
                    var nextBtn = rootEl.querySelector('#nextPageBtn');
                    var pageIndicator = rootEl.querySelector('#pageIndicator');
                    var paginationEl = rootEl.querySelector('.rr-pagination');
                    var resultsMeta = rootEl.querySelector('#rrStatsResultsMeta');
                    var searchInput = rootEl.querySelector('#rrStatsSearchInput');
                    var searchForm = rootEl.querySelector('#rrStatsSearchForm');
                    var searchIcon = rootEl.querySelector('#rrStatsSearchIcon');
                    var modalidadeSelect = rootEl.querySelector('#rrStatsModalidadeSelect');

                    var filterCards = Array.from(rootEl.querySelectorAll('#rrStatsSubmenu .rr-epic-submenu__btn[data-filter]'));

                    var currentFilter = 'todos';
                    var currentModalidade = String((modalidadeSelect && modalidadeSelect.value) || 'todos').toLowerCase();
                    var searchTerm = '';
                    var currentPage = 1;

                    function isMobile(){
                        return window.matchMedia && window.matchMedia('(max-width: 575.98px)').matches;
                    }
                    function isTablet(){
                        return window.matchMedia && window.matchMedia('(min-width: 576px) and (max-width: 991.98px)').matches;
                    }
                    function perPage(){
                        if (isMobile()) return 10;
                        return 32;
                    }

                    function computeMatches(){
                        var term = (searchTerm || '').toLowerCase();
                        return items.filter(function(el){
                            var niv = (el.getAttribute('data-nivel') || '').toLowerCase();
                            // Normalizar nomes antigos para novos
                            if (niv === 'legado') niv = 'ascendente';
                            if (niv === 'presilha') niv = 'competidor';

                            var nomeEl = el.querySelector('.rr-stats-card__name') || el.querySelector('.premium-card__name') || el.querySelector('.hex-card__name');
                            var nome = (nomeEl ? nomeEl.textContent : '').toLowerCase();
                            var modalidadeIds = String(el.getAttribute('data-modalidades') || '')
                                .split(',')
                                .map(function(value){ return value.trim(); })
                                .filter(Boolean);
                            var matchesCategory = (currentFilter === 'todos') || (niv === currentFilter);
                            var matchesModalidade = (currentModalidade === 'todos') || modalidadeIds.indexOf(currentModalidade) !== -1;
                            var matchesSearch = !term || nome.indexOf(term) !== -1;
                            return matchesCategory && matchesModalidade && matchesSearch;
                        });
                    }

                    function renderPage(){
                        var matches = computeMatches();
                        var size = perPage();
                        var totalPages = Math.max(1, Math.ceil(matches.length / size));

                        if (currentPage > totalPages) currentPage = totalPages;
                        if (currentPage < 1) currentPage = 1;

                        items.forEach(function(el){ el.style.display = 'none'; });

                        var start = (currentPage - 1) * size;
                        var end = start + size;
                        matches.slice(start, end).forEach(function(el){ el.style.display = ''; });

                        if (pageIndicator) {
                            // Atualizar novo formato de paginação
                            var currentSpan = pageIndicator.querySelector('.rr-stats-pagination__current');
                            var totalSpan = pageIndicator.querySelector('.rr-stats-pagination__total');
                            
                            if (currentSpan && totalSpan) {
                                currentSpan.textContent = currentPage;
                                totalSpan.textContent = totalPages;
                            } else {
                                // Fallback para formato antigo
                                pageIndicator.textContent = currentPage + '/' + totalPages;
                            }
                        }
                        
                        if (prevBtn) prevBtn.disabled = currentPage <= 1;
                        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
                        if (paginationEl) paginationEl.style.display = totalPages > 1 ? 'flex' : 'none';
                        if (resultsMeta) {
                            resultsMeta.textContent = matches.length + ' competidores visíveis nesta aba.';
                        }
                    }

                    // Bind filter cards (if submenu exists)
                    if (filterCards.length) {
                        filterCards.forEach(function(card){
                            card.style.cursor = 'pointer';
                            card.addEventListener('click', function(){
                                var filter = card.getAttribute('data-filter') || 'todos';
                                filter = (filter || '').toLowerCase();
                                if (filter === 'legado') filter = 'ascendente';
                                if (filter === 'presilha') filter = 'competidor';
                                currentFilter = filter || 'todos';
                                currentPage = 1;
                                filterCards.forEach(function(c){ c.classList.remove('is-active'); });
                                card.classList.add('is-active');
                                renderPage();
                            });
                        });
                    }

                    if (modalidadeSelect && !modalidadeSelect._rrStatsModalidadeBound) {
                        modalidadeSelect._rrStatsModalidadeBound = true;
                        modalidadeSelect.addEventListener('change', function() {
                            currentModalidade = String(modalidadeSelect.value || 'todos').toLowerCase();
                            currentPage = 1;
                            renderPage();
                        });
                    }

                    // Search
                    if (searchForm && searchInput && !searchForm._rrSearchBound) {
                        searchForm._rrSearchBound = true;

                        function syncSearchState() {
                            var hasValue = !!(searchInput.value || '').trim();
                            searchForm.classList.toggle('has-value', hasValue);
                        }

                        searchInput.addEventListener('input', function(){
                            searchTerm = searchInput.value || '';
                            currentPage = 1;
                            syncSearchState();
                            renderPage();
                        });

                        searchInput.addEventListener('keydown', function(e){
                            if (e.key === 'Escape') {
                                searchInput.value = '';
                                searchTerm = '';
                                currentPage = 1;
                                syncSearchState();
                                renderPage();
                            }
                        });

                        if (searchIcon) {
                            searchIcon.addEventListener('click', function(e){
                                e.stopPropagation();
                                if ((searchInput.value || '').trim()) {
                                    searchInput.value = '';
                                    searchTerm = '';
                                    currentPage = 1;
                                    syncSearchState();
                                    renderPage();
                                } else {
                                    try { searchInput.focus(); } catch (err) {}
                                }
                            });
                        }

                        syncSearchState();
                    }

                    // Modal open: card click and "Ver Mais" fallback (event delegation)
                    if (!rootEl.__rrStatsModalBound) {
                        rootEl.__rrStatsModalBound = true;
                        rootEl.addEventListener('click', function(e){
                            var btn = e.target.closest('.verTodasBtn');
                            if (btn) {
                                if (typeof window.abrirModalStats === 'function') {
                                    e.preventDefault();
                                    window.abrirModalStats(btn);
                                }
                                return;
                            }

                            var card = e.target.closest('.rr-stats-item, .rr-card-item');
                            if (!card) return;
                            if (e.target.closest('a, button, input, select, textarea, label')) return;

                            var cardBtn = card.querySelector('.verTodasBtn');
                            if (cardBtn && typeof window.abrirModalStats === 'function') {
                                window.abrirModalStats(cardBtn);
                            }
                        });
                    }

                    // Pagination
                    if (prevBtn) {
                        prevBtn.addEventListener('click', function(){
                            if (currentPage > 1) {
                                currentPage -= 1;
                                renderPage();
                            }
                        });
                    }
                    if (nextBtn) {
                        nextBtn.addEventListener('click', function(){
                            currentPage += 1;
                            renderPage();
                        });
                    }

                    // Initial paint
                    renderPage();
                    requestAnimationFrame(function(){
                        consumePendingOpen(rootEl);
                    });
                } catch (err) {
                    try { console.error('[RRStats] init failed', err); } catch (e) {}
                }
            }

            return {
                init: init,
                queuePendingOpen: queuePendingOpen,
                consumePendingOpen: consumePendingOpen
            };
        })();

        // X1: submenu selection state.
        window.RRX1 = window.RRX1 || (function(){
            function init(rootEl){
                try {
                    if (!rootEl) return;
                    var cards = Array.from(rootEl.querySelectorAll('#rrX1Submenu .rr-epic-submenu__btn[data-filter]'));
                    if (!cards.length) return;
                    cards.forEach(function(card){
                        card.style.cursor = 'pointer';
                        card.addEventListener('click', function(){
                            // Check if it's an action card
                            var action = card.dataset.action;
                            if (action === 'openCreateModal') {
                                // Trigger the modal fallback
                                var createBtn = document.getElementById('x1CreateRoomBtn') || document.getElementById('openCreateModal');
                                if (createBtn) createBtn.click();
                                return;
                            }
                            cards.forEach(function(c){ c.classList.remove('is-active'); });
                            card.classList.add('is-active');
                        });
                    });
                } catch (err) {
                    try { console.error('[RRX1] init failed', err); } catch (e) {}
                }
            }

            return { init: init };
        })();

        var sectionCache = {};
        var sectionUrls = {};
        var $hubSection = $('#hubSection');
        var $navBtns = $('.hub-mobile-tabbar__btn');
        var $navbarTabs = $('.hub-navbar-tab, .hub-navbar-user-btn, .hub-vertical-menu__tab');
        var $headerNavBtns = $('.hub-header-nav__btn');
        var hubFooterNode = null;
        var hubFooterPlaceholder = null;
        var hubSectionObserver = null;
        
        function initHubFooterNode() {
            if (hubFooterNode) return;

            hubFooterNode = document.querySelector('body.hub-page .rr-footer-pro')
                || document.querySelector('body.hub-page .rr-footer')
                || null;

            if (!hubFooterNode || !hubFooterNode.parentNode) return;

            hubFooterPlaceholder = document.createComment('hub-footer-placeholder');
            hubFooterNode.parentNode.insertBefore(hubFooterPlaceholder, hubFooterNode.nextSibling);
        }

        function syncHubFooterPlacement() {
            initHubFooterNode();

            if (!hubFooterNode || !$hubSection || !$hubSection.length) return;

            var hubSectionEl = $hubSection.get(0);
            if (!hubSectionEl) return;

            var isDesktop = window.matchMedia && window.matchMedia('(min-width: 769px)').matches;
            if (isDesktop) {
                if (hubFooterNode.parentNode !== hubSectionEl) {
                    hubSectionEl.appendChild(hubFooterNode);
                }
                hubSectionEl.classList.add('has-inline-footer');
                return;
            }

            hubSectionEl.classList.remove('has-inline-footer');
            if (hubFooterPlaceholder && hubFooterPlaceholder.parentNode && hubFooterNode.parentNode !== hubFooterPlaceholder.parentNode) {
                hubFooterPlaceholder.parentNode.insertBefore(hubFooterNode, hubFooterPlaceholder.nextSibling);
            }
        }

        function normalizeInicioMobileCardSizes() {
            if (!window.matchMedia || !window.matchMedia('(max-width: 768px)').matches) return;
            if (!$hubSection || !$hubSection.length) return;

            var hubSectionEl = $hubSection.get(0);
            if (!hubSectionEl) return;

            var skipOverflowUnlock = function(el) {
                return el.classList && (
                    el.classList.contains('rr-inicio-grid')
                    || el.classList.contains('rr-inicio-grid-wrap--bolaos')
                    || el.classList.contains('rr-inicio-grid-wrap--x1rooms')
                );
            };

            var unlockClip = function(el) {
                if (!el || skipOverflowUnlock(el)) return;
                el.style.setProperty('overflow-x', 'visible', 'important');
                el.style.setProperty('overflow-y', 'visible', 'important');
                el.style.setProperty('contain', 'none', 'important');
                el.style.setProperty('min-width', '0');
                el.style.setProperty('box-sizing', 'border-box');
            };

            var roots = hubSectionEl.querySelectorAll(
                '.rr-mobile-control-stack, .rr-inicio-shell, #rrInicioSection, #rrInicioSection .card-body, .rr-inicio-grid-wrap, .rr-competitor-mobile-row'
            );
            roots.forEach(function(el) {
                el.style.width = '100%';
                el.style.maxWidth = '100%';
                el.style.minWidth = '0';
                el.style.marginLeft = '0';
                el.style.marginRight = '0';
                el.style.boxSizing = 'border-box';
                unlockClip(el);

                if (el.matches && el.matches('#rrInicioSection .card-body')) {
                    el.style.padding = '0';
                }
            });

            var cards = hubSectionEl.querySelectorAll(
                '.rr-inicio-event-call, .rr-bolao-launch-simple, .rr-bolao-card, .rr-x1room-card, .rr-neuro-wrapper'
            );
            cards.forEach(function(card) {
                card.style.width = '100%';
                card.style.maxWidth = '100%';
                card.style.minWidth = '0';
                card.style.marginLeft = '0';
                card.style.marginRight = '0';
                card.style.boxSizing = 'border-box';

                var node = card;
                while (node && node !== hubSectionEl) {
                    unlockClip(node);
                    node = node.parentElement;
                }
            });

            // Some mobile styles force fixed flex-basis (e.g. 220px !important).
            // Override with full-width cards so all blocks share the same column size.
            hubSectionEl.querySelectorAll('.rr-bolao-grid > .rr-bolao-card, .rr-x1-room-grid > .rr-x1room-card').forEach(function(card) {
                card.style.setProperty('flex', '0 0 100%', 'important');
                card.style.setProperty('width', '100%', 'important');
                card.style.setProperty('min-width', '100%', 'important');
                card.style.setProperty('max-width', '100%', 'important');
            });

            hubSectionEl.querySelectorAll('.rr-bolao-grid, .rr-x1-room-grid').forEach(function(grid) {
                grid.style.setProperty('padding-left', '0', 'important');
                grid.style.setProperty('padding-right', '0', 'important');
            });

            unlockClip(hubSectionEl);
        }

        function bindHubSectionObserver() {
            if (hubSectionObserver || !window.MutationObserver || !$hubSection || !$hubSection.length) return;
            var target = $hubSection.get(0);
            if (!target) return;

            var queued = false;
            hubSectionObserver = new MutationObserver(function() {
                if (queued) return;
                queued = true;
                window.requestAnimationFrame(function() {
                    queued = false;
                    normalizeInicioMobileCardSizes();
                });
            });

            hubSectionObserver.observe(target, { childList: true, subtree: true });
        }

        // ✅ Definir seção inicial
        var defaultSection = 'inicio';

        var loaderMarkup = `
            <div class="hub-section__placeholder">
                <div class="spinner" aria-hidden="true"></div>
                <div>
                    <p class="mb-1 fw-semibold">@lang('Carregando dados...')</p>
                    <small class="text-muted d-block">@lang('Isso deve levar apenas alguns segundos.')</small>
                </div>
            </div>
        `;

        var emptyMarkup = `
            <div class="hub-section__placeholder">
                <i class="fas fa-layer-group fa-2x text-warning mb-2" aria-hidden="true"></i>
                <div>
                    <p class="mb-1 fw-semibold">@lang('Selecione uma das abas acima.')</p>
                    <small class="text-muted d-block">@lang('O conteúdo aparecerá aqui automaticamente.')</small>
                </div>
            </div>
        `;

        function errorMarkup(section) {
            return `
                <div class="hub-section__placeholder text-center">
                    <i class="fas fa-triangle-exclamation fa-2x text-warning mb-3" aria-hidden="true"></i>
                    <p class="mb-2 fw-semibold">@lang('Não foi possível carregar esta aba.')</p>
                    <button type="button" class="btn btn-sm rr-btn-primary" data-retry-section="${section}">
                        <i class="fas fa-rotate-right me-1"></i>@lang('Tentar novamente')
                    </button>
                </div>
            `;
        }

        function normalizeSectionUrl(rawUrl) {
            if (!rawUrl) return rawUrl;

            try {
                var parsed = new URL(rawUrl, window.location.origin);
                var current = new URL(window.location.href);
                var path = parsed.pathname || '/';

                if (path.indexOf('/rei/public') === 0) {
                    path = path.replace(/^\/rei\/public/, '') || '/';
                    parsed = new URL(path + (parsed.search || ''), current.origin);
                }

                if (parsed.origin !== current.origin) {
                    parsed = new URL((parsed.pathname || '/') + (parsed.search || ''), current.origin);
                }

                return parsed.toString();
            } catch (e) {
                return rawUrl;
            }
        }

        $navBtns.each(function(){
            var $btn = $(this);
            var section = $btn.data('section');
            var url = normalizeSectionUrl($btn.data('url'));
            if (section && url) {
                sectionUrls[section] = url;
            }
        });
        // Also register navbar tab URLs
        $navbarTabs.each(function(){
            var $btn = $(this);
            var section = $btn.data('section');
            var url = normalizeSectionUrl($btn.data('url'));
            if (section && url && !sectionUrls[section]) {
                sectionUrls[section] = url;
            }
        });
        // Register header nav button URLs
        $headerNavBtns.each(function(){
            var $btn = $(this);
            var section = $btn.data('section');
            var url = normalizeSectionUrl($btn.data('url'));
            if (section && url && !sectionUrls[section]) {
                sectionUrls[section] = url;
            }
        });
        if (!sectionUrls[defaultSection]) {
            defaultSection = ($navBtns.filter('[data-section]').first().data('section') || 'equipes');
        }

        function setActive(section){
            document.body.setAttribute('data-hub-section', section);
            postNativeMessage('hub_section_change', {
                section: section
            });
            
            // 🔥 Salvar aba ativa no localStorage para persistir após F5
            try {
                localStorage.setItem('hub_active_section', section);
            } catch(e) {}

            // 📺 Pausar/retomar iframe da live conforme a aba
            var liveIframe = document.getElementById('hubTopLiveIframe');
            if (liveIframe) {
                var showLive = (section === 'inicio' || section === 'premium');
                if (!showLive) {
                    // Salvar src e remover para parar o vídeo
                    if (liveIframe.src && !liveIframe.dataset.savedSrc) {
                        liveIframe.dataset.savedSrc = liveIframe.src;
                    }
                    liveIframe.removeAttribute('src');
                } else if (liveIframe.dataset.savedSrc && !liveIframe.src) {
                    // Restaurar src ao voltar para aba com live
                    liveIframe.src = liveIframe.dataset.savedSrc;
                    delete liveIframe.dataset.savedSrc;
                }
            }

            if ($navBtns && $navBtns.length) {
                $navBtns.removeClass('active');
                var $activeBtn = $navBtns.filter('[data-section="' + section + '"]');
                $activeBtn.addClass('active');

                // 🔥 Animate border-effect to active tab
                updateBorderEffect($activeBtn);
            }

            // Sync navbar tabs (KTO-style header)
            if ($navbarTabs && $navbarTabs.length) {
                $navbarTabs.removeClass('active');
                $navbarTabs.filter('[data-section="' + section + '"]').addClass('active');
            }

            // Sync header nav buttons
            if ($headerNavBtns && $headerNavBtns.length) {
                $headerNavBtns.removeClass('active');
                $headerNavBtns.filter('[data-section="' + section + '"]').addClass('active');
            }
        }

        // 🌟 Border Effect Animation
        function updateBorderEffect($activeBtn) {
            var btn = $activeBtn[0];
            if (!btn) return;

            var nav = btn.closest('.hub-mobile-tabbar__nav');
            if (!nav) return;

            var borderEffect = nav.querySelector('.hub-tab-border-effect');
            if (!borderEffect) return;

            var navRect = nav.getBoundingClientRect();
            var btnRect = btn.getBoundingClientRect();
            var isLaunchNav = nav.classList.contains('hub-mobile-tabbar__nav--launch');

            var leftPos;
            var effectWidth;
            if (isLaunchNav) {
                var launchHost = borderEffect.offsetParent || nav;
                var launchHostRect = launchHost.getBoundingClientRect();
                leftPos = btnRect.left - launchHostRect.left;
                effectWidth = btnRect.width;
            } else {
                var btnCenter = btnRect.left + (btnRect.width / 2) - navRect.left;
                effectWidth = parseInt(getComputedStyle(borderEffect).width) || 40;
                leftPos = btnCenter - (effectWidth / 2);
            }

            // Get the accent color of the active button (robust fallback chain)
            var accentColor = (btn.getAttribute('data-accent') || '').trim();
            if (!accentColor) {
                accentColor = getComputedStyle(btn).getPropertyValue('--hub-tab-accent').trim();
            }
            if (!accentColor) {
                if (btn.classList.contains('home')) accentColor = '#f59e0be6';
                else if (btn.classList.contains('chart')) accentColor = '#22c55e';
                else if (btn.classList.contains('premium')) accentColor = '#8b5cf6';
                else if (btn.classList.contains('user')) accentColor = '#2563eb';
                else if (btn.classList.contains('logout')) accentColor = '#ef4444';
                else accentColor = '#f59e0be6';
            }

            // Apply animation
            requestAnimationFrame(function(){
                var isLightTheme = document.body.classList.contains('light');
                var indicatorColor = isLightTheme ? '#ea580c' : accentColor;
                borderEffect.style.width = effectWidth + 'px';
                borderEffect.style.left = leftPos + 'px';
                borderEffect.style.setProperty('--hub-active-color', accentColor);
                borderEffect.style.background = isLaunchNav
                    ? (isLightTheme
                        ? 'linear-gradient(135deg, #ffe1bf 0%, #fb923c 42%, #ea580c 100%)'
                        : 'linear-gradient(135deg, #ffd7a8 0%, #f59e0be6 44%, #f97316 100%)')
                    : indicatorColor;
                borderEffect.style.filter = isLaunchNav
                    ? 'drop-shadow(0 8px 16px ' + accentColor + '33)'
                    : 'drop-shadow(0 0 8px ' + accentColor + '55)';

                nav.style.setProperty('--hub-active-color', accentColor);
                nav.setAttribute('data-active-section', btn.getAttribute('data-section') || '');
                nav.style.border = '1px solid rgba(255,228,214,0.08)';
                nav.style.borderColor = 'rgba(255,228,214,0.08)';
                nav.style.boxShadow = '0 16px 34px rgba(0,0,0,0.42), inset 0 1px 0 rgba(255,255,255,0.06)';
            });
        }

        function initLaunchMobileMenuMagic() {
            var launchNavs = document.querySelectorAll('.hub-mobile-tabbar__nav--launch');
            if (!launchNavs.length) return;

            launchNavs.forEach(function(nav) {
                if (nav.dataset.magicBound === '1') return;
                nav.dataset.magicBound = '1';

                var avatarBtn = nav.querySelector('.hub-mobile-tabbar__btn--launch-avatar');
                if (!avatarBtn) return;

                var resetTilt = function() {
                    avatarBtn.style.removeProperty('--hub-launch-avatar-tilt');
                };

                nav.addEventListener('pointermove', function(event) {
                    if (!event.isPrimary) return;

                    var rect = nav.getBoundingClientRect();
                    var ratio = rect.width ? ((event.clientX - rect.left) / rect.width) : 0.5;
                    var clamped = Math.max(0, Math.min(1, ratio));
                    var tilt = ((clamped - 0.5) * 12).toFixed(2);
                    avatarBtn.style.setProperty('--hub-launch-avatar-tilt', tilt + 'deg');
                });

                nav.addEventListener('pointerleave', resetTilt);
                nav.addEventListener('pointercancel', resetTilt);

                nav.addEventListener('click', function(event) {
                    var trigger = event.target.closest('.hub-mobile-tabbar__btn--launch-filter, .hub-mobile-tabbar__btn--launch-avatar');
                    if (!trigger) return;

                    avatarBtn.classList.remove('is-jumped');
                    void avatarBtn.offsetWidth;
                    avatarBtn.classList.add('is-jumped');

                    window.setTimeout(function() {
                        avatarBtn.classList.remove('is-jumped');
                    }, 520);
                });
            });
        }

        // Recalculate on resize
        var resizeTimeout;
        $(window).on('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                var $activeBtn = $navBtns.filter('.active');
                if ($activeBtn.length) {
                    updateBorderEffect($activeBtn);
                }
                syncHubFooterPlacement();
                normalizeInicioMobileCardSizes();
                bindHubSectionObserver();
            }, 100);
        });

        // Initialize border effect on page load
        $(document).ready(function() {
            setTimeout(function() {
                var $activeBtn = $navBtns.filter('.active');
                if ($activeBtn.length) {
                    updateBorderEffect($activeBtn);
                }
                syncHubFooterPlacement();
                normalizeInicioMobileCardSizes();
                bindHubSectionObserver();
            }, 100);
        });

        function cleanupInicioSidePanels() {
            if (typeof window.__rrRestoreHubLive === 'function') {
                window.__rrRestoreHubLive();
            }
            var grid = document.querySelector('.hub-top__grid');
            if (!grid) return;
            var panels = grid.querySelectorAll('.rr-side-panel--right-stack, .rr-side-panel--bottom, .rr-side-panel--left, .rr-side-panel--right');
            for (var i = 0; i < panels.length; i++) panels[i].remove();
            grid.classList.remove('has-side-panels');
        }

        function reparentInicioSidePanels() {
            cleanupInicioSidePanels();
            if (typeof window.rrReinitCarousels === 'function') {
                setTimeout(function() {
                    window.rrReinitCarousels();
                }, 120);
            }
        }

        function render(html){
            try {
                if (!$hubSection || !$hubSection.length) {
                    console.error('❌ $hubSection não encontrado');
                    return;
                }
                
                if (typeof html !== 'string') {
                    console.error('❌ HTML inválido para render:', html);
                    return;
                }

                // Clean up inicio side panels before rendering new content
                cleanupInicioSidePanels();
                
                $hubSection.stop(true, true).css('opacity', 0);
                requestAnimationFrame(function(){
                    try {
                        $hubSection.html(html).css('opacity', 1);
                        syncHubFooterPlacement();
                        normalizeMobileHubScroll();
                        normalizeInicioMobileCardSizes();
                        bindHubSectionObserver();
                    } catch (err) {
                        console.error('❌ Erro ao renderizar HTML:', err);
                        $hubSection.css('opacity', 1);
                    }
                });
            } catch (err) {
                console.error('❌ Erro na função render:', err);
            }
        }

        // Estado de carregamento - impede cliques múltiplos
        var isLoading = false;

        function hasAnyScrollLockModalOpen(){
            var classLocked = document.body.classList.contains('modal-open')
                || document.body.classList.contains('rr-modal-open')
                || document.body.classList.contains('rr-legal-modal-open');

            if (!classLocked) return false;

            // Só mantém lock se existir modal realmente aberto na tela.
            var selectors = [
                '.modal.show',
                '.rr-fantasy-modal--open',
                '.rr-legal-modal.is-open',
                '.rr-x1modal-overlay.active',
                '.rr-modal-overlay[style*="display: block"]',
                '.rr-modal-overlay[style*="display:block"]'
            ];
            for (var i = 0; i < selectors.length; i++) {
                if (document.querySelector(selectors[i])) return true;
            }

            return false;
        }

        function normalizeMobileHubScroll(){
            if (window.innerWidth >= 769) return;
            if (hasAnyScrollLockModalOpen()) return;

            // Se algum lock antigo ficou preso, limpa para manter scroll único e fluido no app/webview.
            document.body.classList.remove('rr-modal-open');
            document.body.classList.remove('modal-open');
            document.body.classList.remove('rr-legal-modal-open');
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
            document.documentElement.style.overflowY = '';
        }

        function setLoadingState(loading) {
            isLoading = loading;
            
            // Adicionar/remover atributo disabled e classe visual nos botões
            var $allBtns = $navBtns.add($navbarTabs).add($headerNavBtns);
            if ($allBtns && $allBtns.length) {
                if (loading) {
                    $allBtns.attr('disabled', true).css({
                        'opacity': '0.5',
                        'pointer-events': 'none',
                        'cursor': 'not-allowed'
                    });
                } else {
                    $allBtns.removeAttr('disabled').css({
                        'opacity': '',
                        'pointer-events': '',
                        'cursor': ''
                    });
                }
            }

            if (!loading) {
                normalizeMobileHubScroll();
            }
        }

        function loadSection(section, forceReload){
            // ⛔ Bloquear se já estiver carregando
            if (isLoading) {
                console.log('⏳ Aguarde o carregamento atual terminar...');
                return;
            }

            var url = sectionUrls[section];
            if(!url){
                render(emptyMarkup);
                return;
            }

            if(!forceReload && sectionCache[section]){
                render(sectionCache[section]);
                if(section === 'perfil' || section === 'pix' || section === 'afiliados'){
                    if (typeof window.initPerfilSubmenus === 'function') {
                        window.initPerfilSubmenus();
                    }
                    window.setTimeout(flushProfileSection, 60);
                    window.setTimeout(flushProfileSection, 180);
                }
                if(section === 'equipes' && window.RRFantasy && typeof window.RRFantasy.init === 'function'){
                    requestAnimationFrame(function(){
                        window.RRFantasy.init(document.getElementById('hubSection'));
                    });
                }
                if(section === 'estatisticas' && window.RRStats && typeof window.RRStats.init === 'function'){
                    requestAnimationFrame(function(){
                        window.RRStats.init(document.getElementById('hubSection'));
                    });
                }
                if(section === 'loja' && window.RRStore && typeof window.RRStore.init === 'function'){
                    requestAnimationFrame(function(){
                        window.RRStore.init(document.getElementById('hubSection'));
                    });
                }
                if(section === 'x1' && window.RRX1 && typeof window.RRX1.init === 'function'){
                    requestAnimationFrame(function(){
                        window.RRX1.init(document.getElementById('hubSection'));
                    });
                }
                if(section === 'inicio'){
                    reparentInicioSidePanels();
                }
                return;
            }

            // 🔒 Travar durante carregamento
            setLoadingState(true);

            // 🔄 Limpar cache se forceReload = true para garantir dados frescos
            if (forceReload && sectionCache[section]) {
                delete sectionCache[section];
                console.log('🔄 Cache limpo para:', section);
            }

            render(loaderMarkup);

            $.get(url)
                .done(function(response){
                    var unlockScheduled = false;

                    function unlockAfterRender() {
                        if (unlockScheduled) return;
                        unlockScheduled = true;
                        requestAnimationFrame(function(){
                            setLoadingState(false);
                        });
                    }

                    try {
                        if (!response || typeof response !== 'string') {
                            console.error('❌ Resposta inválida do servidor:', response);
                            render('<div class="text-center p-4"><div class="text-muted">Erro ao carregar conteúdo</div></div>');
                            unlockAfterRender();
                            return;
                        }
                        
                        sectionCache[section] = response;
                        render(response);
                        if(section === 'perfil' || section === 'pix' || section === 'afiliados'){
                            if (typeof window.initPerfilSubmenus === 'function') {
                                window.initPerfilSubmenus();
                            }
                            window.setTimeout(flushProfileSection, 60);
                            window.setTimeout(flushProfileSection, 180);
                        }
                        
                        if(section === 'equipes' && window.RRFantasy && typeof window.RRFantasy.init === 'function'){
                            requestAnimationFrame(function(){
                                window.RRFantasy.init(document.getElementById('hubSection'));
                                // Remover busca (lupa) inserida nos partials de Bolão
                                try{ setTimeout(function(){
                                    document.querySelectorAll('.rr-fantasy-search-wrapper').forEach(function(el){ el.remove(); });
                                }, 50); }catch(e){}
                            });
                        }
                        if(section === 'estatisticas' && window.RRStats && typeof window.RRStats.init === 'function'){
                            requestAnimationFrame(function(){
                            window.RRStats.init(document.getElementById('hubSection'));
                            });
                        }
                        if(section === 'loja' && window.RRStore && typeof window.RRStore.init === 'function'){
                            requestAnimationFrame(function(){
                                window.RRStore.init(document.getElementById('hubSection'));
                            });
                        }
                        if(section === 'x1' && window.RRX1 && typeof window.RRX1.init === 'function'){
                            requestAnimationFrame(function(){
                                window.RRX1.init(document.getElementById('hubSection'));
                            });
                        }
                        if(section === 'inicio'){
                            reparentInicioSidePanels();
                        }

                        unlockAfterRender();
                    } catch (err) {
                        console.error('❌ Erro na renderização da seção:', err);
                        render('<div class="text-center p-4"><div class="text-muted">Erro ao processar conteúdo</div></div>');
                        unlockAfterRender();
                    } finally {
                        // desbloqueio ocorre apenas após render/init via unlockAfterRender()
                    }
                })
                .fail(function(xhr, status, error){
                    try {
                        console.error('❌ Erro AJAX:', { xhr, status, error });
                        render(errorMarkup(section));
                    } catch (err) {
                        console.error('❌ Erro no fail handler:', err);
                        if ($hubSection && $hubSection.length) {
                            $hubSection.html('<div class="text-center p-4"><div class="text-muted">Erro ao carregar seção</div></div>');
                        }
                    }
                    
                    // 🔓 Destravar imediatamente em erro
                    setLoadingState(false);
                });
        }

        // Tab bar (used on web + mobile)
        if ($navBtns && $navBtns.length) {
            $navBtns.on('click', function(e){
                e.preventDefault();

                var $btn = $(this);
                var action = $btn.data('action');
                var section = $btn.data('section');

                if (action === 'logout') {
                    if (HUB_AUTH && HUB_LOGOUT_URL) {
                        postNativeMessage('hub_logout', {
                            section: document.body.getAttribute('data-hub-section') || 'inicio'
                        });
                        window.location.href = HUB_LOGOUT_URL;
                    }
                    return;
                }

                if (action === 'open-auth') {
                    openAuthModal();
                    return;
                }

                if (action === 'user') {
                    if (HUB_AUTH) {
                        openProfileTarget('perfil');
                    } else {
                        openAuthModal();
                    }
                    return;
                }

                if (action === 'switch-app') {
                    runPortalTransition(function() {
                        if (HUB_APP_CONTEXT.isApp) {
                            postNativeMessage('hub_return_to_community', {
                                section: currentHubSection()
                            });
                            return;
                        }

                        attemptOpenNativeApp();
                    });
                    return;
                }

                if (!section) {
                    return;
                }

                // 🔄 SEMPRE recarregar para garantir dados atualizados
                // Removida verificação: if (section === document.body.getAttribute('data-hub-section')) return;

                if (section === 'perfil' || section === 'pix' || section === 'afiliados') {
                    queueProfileSection($btn.data('profile-target') || 'perfil');
                } else {
                    clearProfileSectionQueue();
                }

                setActive(section);
                loadSection(section, true);
            });
        }

        // Navbar tabs (KTO-style header) — sync with bottom tabbar
        if ($navbarTabs && $navbarTabs.length) {
            $navbarTabs.on('click', function(e){
                e.preventDefault();
                var $btn = $(this);
                var section = $btn.data('section');
                if (!section) return;

                // For perfil button, open profile popout
                if (section === 'perfil' || section === 'pix' || section === 'afiliados') {
                    setActive(section);
                    if (section === 'perfil') {
                        openProfileTarget($btn.data('profile-target') || 'perfil');
                    } else {
                        queueProfileSection($btn.data('profile-target') || 'financeiro');
                        loadSection(section, true);
                    }
                    return;
                }

                clearProfileSectionQueue();
                setActive(section);
                loadSection(section, true);
            });
        }

        // Desktop header nav buttons
        if ($headerNavBtns && $headerNavBtns.length) {
            $headerNavBtns.on('click', function(e){
                e.preventDefault();
                var $btn = $(this);
                var action = $btn.data('action');
                var section = $btn.data('section');

                if (action === 'logout') {
                    if (HUB_AUTH && HUB_LOGOUT_URL) {
                        postNativeMessage('hub_logout', {
                            section: document.body.getAttribute('data-hub-section') || 'inicio'
                        });
                        window.location.href = HUB_LOGOUT_URL;
                    }
                    return;
                }

                if (action === 'user') {
                    if (HUB_AUTH) {
                        openProfileTarget('perfil');
                    } else {
                        openAuthModal();
                    }
                    return;
                }

                if (!section) return;

                if (section === 'perfil' || section === 'pix' || section === 'afiliados') {
                    queueProfileSection($btn.data('profile-target') || 'perfil');
                } else {
                    clearProfileSectionQueue();
                }

                setActive(section);
                loadSection(section, true);
            });
        }

        initLaunchMobileMenuMagic();

        $hubSection.on('click', '[data-retry-section]', function(){
            var section = $(this).data('retry-section');
            loadSection(section, true);
        });

        // 🔥 Verificar parâmetros de URL para redirecionamento de perfil
        var urlParams = new URLSearchParams(window.location.search);
        var tabParam = urlParams.get('tab');
        var competitorParam = urlParams.get('competitor');
        var completeProfile = urlParams.get('complete_profile');
        var searchRoomParam = urlParams.get('search_room');

        // 💾 Salvar search_room no sessionStorage para a aba X1 ler depois do AJAX
        if (searchRoomParam) {
            sessionStorage.setItem('x1_search_room', searchRoomParam);
            if (!tabParam) tabParam = 'x1'; // Auto-redirecionar para aba X1
        }

        // Verificar também sessionStorage (para registro sem params na URL)
        var redirectToPerfil = sessionStorage.getItem('redirect_to_perfil');
        if (redirectToPerfil === '1' && !@json($isBolaoLaunchMode)) {
            sessionStorage.removeItem('redirect_to_perfil');
            tabParam = 'perfil';
        } else if (redirectToPerfil === '1' && @json($isBolaoLaunchMode)) {
            sessionStorage.removeItem('redirect_to_perfil');
        }

        if (tabParam === 'afiliados') {
            tabParam = 'pix';
        }

        if (tabParam && sectionUrls[tabParam]) {
            if (tabParam === 'pix') {
                setActive('pix');
                queueProfileSection('financeiro');
                loadSection('pix', true);

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
                return;
            }

            if (tabParam === 'estatisticas' && competitorParam && window.RRStats && typeof window.RRStats.queuePendingOpen === 'function') {
                window.RRStats.queuePendingOpen({
                    id: parseInt(competitorParam, 10) || 0
                });
            }

            // Redirecionar para a aba especificada na URL
            setActive(tabParam);
            loadSection(tabParam, true);

            // Se complete_profile=1, armazenar no sessionStorage para exibir banner
            if (completeProfile === '1') {
                sessionStorage.setItem('show_complete_profile_banner', '1');
            }

            // Limpar os parâmetros da URL sem recarregar a página
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        } else {
            // Restaurar última aba visitada (localStorage) ou iniciar em "inicio"
            var savedSection = null;
            try { savedSection = localStorage.getItem('hub_active_section'); } catch(e) {}
            if (savedSection === 'perfil') {
                savedSection = 'pix';
            }
            var restoreSection = @json($isBolaoLaunchMode)
                ? 'inicio'
                : ((savedSection && sectionUrls[savedSection]) ? savedSection : defaultSection);
            setActive(restoreSection);
            loadSection(restoreSection, true);
        }

        // Safety net para webview/mobile: garante que não fique lock residual de scroll.
        setTimeout(normalizeMobileHubScroll, 120);

        // Expor função global para navegação entre abas
        window.switchHubTab = function(section) {
            // Fechar perfil se estiver aberto
            var pop = document.getElementById('hubProfilePopout');
            if (pop && pop.classList.contains('is-open')) {
                pop.classList.remove('is-open');
                pop.setAttribute('aria-hidden', 'true');
            }

            if (section === 'pix') {
                setActive('pix');
                queueProfileSection('financeiro');
                loadSection('pix', true);
                window.history.replaceState(null, '', '/?tab=pix');
                return;
            }

            if (section === 'afiliados') section = 'pix';

            if (sectionUrls[section]) {
                if (section !== 'perfil' && section !== 'pix') {
                    clearProfileSectionQueue();
                }
                setActive(section);
                loadSection(section, true);
                window.history.replaceState(null, '', '/?tab=' + section);
            }
        };

        // Função utilitária para escapar HTML
        function escapeHtml(str){
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Função utilitária para converter para número
        function num(val) {
            var s = String(val).replace(',', '.');
            var n = parseFloat(s);
            return isNaN(n) ? 0 : n;
        }

        // Função utilitária para formatar moeda BRL
        function formatBRL(val){
            var n = num(val);
            var decimals = 2;
            if (n > 0 && n < 0.01) decimals = 6;
            else if (n > 0 && n < 1) decimals = 4;
            try {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(n);
            } catch (e) {
                return 'R$ ' + n.toFixed(decimals);
            }
        }

    });
})(jQuery);
</script>

<!-- Unified Global Fire Particles (desktop + mobile, bottom -> top) -->
<script>
(function() {
    var canvas = document.getElementById('hubUnifiedFireCanvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'hubUnifiedFireCanvas';
        canvas.className = 'hub-unified-fire-canvas';
        canvas.setAttribute('aria-hidden', 'true');
        document.body.insertBefore(canvas, document.body.firstChild || null);
    }

    if (!canvas.getContext) return;

    var ctx = canvas.getContext('2d');
    var particles = [];
    var rafId = null;
    var resizeTimer = null;
    var reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    var motionFactor = reduceMotionQuery.matches ? 0.45 : 1;
    var canvasWidth = 0;
    var canvasHeight = 0;
    var stableViewport = { w: 0, h: 0 };

    var firePaletteDark = [
        [255, 222, 171],
        [255, 184, 106],
        [255, 133, 55],
        [235, 76, 34]
    ];

    var firePaletteLight = [
        [255, 208, 148],
        [251, 170, 93],
        [246, 125, 57],
        [224, 98, 46]
    ];

    function isLight() {
        return document.body.classList.contains('light');
    }

    function palette() {
        return isLight() ? firePaletteLight : firePaletteDark;
    }

    function pickFireColor() {
        var list = palette();
        return list[Math.floor(Math.random() * list.length)];
    }

    function clamp(n, min, max) {
        return Math.max(min, Math.min(max, n));
    }

    function particleCountForViewport(w, h) {
        var area = w * h;
        var mobile = w <= 768;
        var base = mobile ? Math.floor(area / 4300) : Math.floor(area / 5200);
        return clamp(base, mobile ? 130 : 120, mobile ? 260 : 320);
    }

    function readViewport() {
        var vv = window.visualViewport;
        var w = vv ? Math.round(vv.width) : window.innerWidth;
        var h = vv ? Math.round(vv.height) : window.innerHeight;
        return { w: w, h: h };
    }

    function resetParticle(p, w, h, spawnFromBottom) {
        p.x = Math.random() * w;
        p.y = spawnFromBottom ? (h + Math.random() * (h * 0.35)) : (Math.random() * h);
        p.size = Math.random() * 2.4 + 0.6;
        p.speedY = (Math.random() * 1.5 + 0.55) * motionFactor;
        p.speedX = (Math.random() - 0.5) * 0.35 * motionFactor;
        p.sway = (Math.random() * 0.7 + 0.2) * motionFactor;
        p.phase = Math.random() * Math.PI * 2;
        p.opacity = Math.random() * 0.42 + 0.16;
        p.color = pickFireColor();
    }

    function initParticles(w, h) {
        particles = [];
        var count = particleCountForViewport(w, h);
        for (var i = 0; i < count; i++) {
            var p = {};
            resetParticle(p, w, h, true);
            p.y -= Math.random() * (h * 0.18);
            particles.push(p);
        }
    }

    function setCanvasSize() {
        var vp = readViewport();
        var w = vp.w;
        var h = vp.h;
        var ratio = window.devicePixelRatio || 1;

        canvas.width = w * ratio;
        canvas.height = h * ratio;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        canvasWidth = w;
        canvasHeight = h;
        stableViewport = { w: w, h: h };

        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(ratio, ratio);
        initParticles(w, h);
    }

    function drawConnections(maxDist) {
        for (var i = 0; i < particles.length; i++) {
            for (var j = i + 1; j < particles.length; j++) {
                var dx = particles[i].x - particles[j].x;
                var dy = particles[i].y - particles[j].y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist > maxDist) continue;

                var c1 = particles[i].color;
                var c2 = particles[j].color;
                var mixR = Math.round((c1[0] + c2[0]) / 2);
                var mixG = Math.round((c1[1] + c2[1]) / 2);
                var mixB = Math.round((c1[2] + c2[2]) / 2);
                var alpha = 0.14 * (1 - dist / maxDist);

                ctx.strokeStyle = 'rgba(' + mixR + ', ' + mixG + ', ' + mixB + ', ' + alpha + ')';
                ctx.lineWidth = 0.55;
                ctx.beginPath();
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.stroke();
            }
        }
    }

    function animate(ts) {
        var w = canvasWidth;
        var h = canvasHeight;
        ctx.clearRect(0, 0, w, h);

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];

            p.y -= p.speedY;
            p.x += p.speedX + Math.sin(ts * 0.0013 + p.phase) * p.sway;

            if (p.y < -40 || p.x < -50 || p.x > w + 50) {
                resetParticle(p, w, h, true);
            }

            var pulse = p.opacity * (0.7 + 0.3 * Math.sin(ts * 0.002 + p.phase));
            var c = p.color;

            ctx.fillStyle = 'rgba(' + c[0] + ', ' + c[1] + ', ' + c[2] + ', ' + pulse + ')';
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fill();

            // Short flame tail
            ctx.strokeStyle = 'rgba(' + c[0] + ', ' + c[1] + ', ' + c[2] + ', ' + (pulse * 0.26) + ')';
            ctx.lineWidth = 0.7;
            ctx.beginPath();
            ctx.moveTo(p.x, p.y + 1);
            ctx.lineTo(p.x - (p.sway * 5), p.y + 12);
            ctx.stroke();
        }

        drawConnections(canvasWidth <= 768 ? 86 : 102);
        rafId = requestAnimationFrame(animate);
    }

    function restart() {
        if (rafId) cancelAnimationFrame(rafId);
        setCanvasSize();
        rafId = requestAnimationFrame(animate);
    }

    function onResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            var next = readViewport();
            var widthDelta = Math.abs(next.w - stableViewport.w);
            var heightDelta = Math.abs(next.h - stableViewport.h);
            var isMobile = next.w <= 768;

            // Ignore tiny height changes caused by mobile browser chrome while scrolling.
            if (isMobile && widthDelta < 2 && heightDelta > 0 && heightDelta < 140) {
                return;
            }

            restart();
        }, 120);
    }

    function onThemeToggle() {
        // recolor all particles instantly to current theme palette
        for (var i = 0; i < particles.length; i++) {
            particles[i].color = pickFireColor();
        }
    }

    function onReduceMotionChange(e) {
        motionFactor = e.matches ? 0.45 : 1;
        restart();
    }

    if (reduceMotionQuery.addEventListener) {
        reduceMotionQuery.addEventListener('change', onReduceMotionChange);
    } else if (reduceMotionQuery.addListener) {
        reduceMotionQuery.addListener(onReduceMotionChange);
    }

    var bodyObserver = new MutationObserver(function(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            if (mutations[i].attributeName === 'class') {
                onThemeToggle();
                break;
            }
        }
    });
    bodyObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (rafId) cancelAnimationFrame(rafId);
            return;
        }
        rafId = requestAnimationFrame(animate);
    });

    window.addEventListener('resize', onResize, { passive: true });
    restart();
})();

(function() {
    var tickers = Array.prototype.slice.call(document.querySelectorAll('[data-voucher-ticker]'));
    if (!tickers.length) return;

    tickers.forEach(function(windowEl) {
        var items = Array.prototype.slice.call(windowEl.querySelectorAll('.hub-navbar-mobile-voucher-ticker__item'));
        if (items.length <= 1) return;

        var currentIndex = items.findIndex(function(item) {
            return item.classList.contains('is-visible');
        });

        if (currentIndex < 0) {
            currentIndex = 0;
            items[0].classList.add('is-visible');
        }

        window.setInterval(function() {
            var current = items[currentIndex];
            var nextIndex = (currentIndex + 1) % items.length;
            var next = items[nextIndex];

            current.classList.add('is-exiting');
            current.classList.remove('is-visible');

            next.classList.add('is-visible');

            window.setTimeout(function() {
                current.classList.remove('is-exiting');
            }, 520);

            currentIndex = nextIndex;
        }, 2300);
    });
})();
</script>
<script src="{{ versionedAsset('assets/js/x1-arena-clean.js') }}"></script>
@endpush
