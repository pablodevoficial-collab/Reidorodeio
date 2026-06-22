@php
    $isBolaoLaunchMode = (bool) ($isBolaoLaunchMode ?? false);
    $hubInicioUrl = $hubInicioUrl ?? route('hub.inicio');
    $tabbarUser = auth()->user();
    $tabbarIsGuest = !$tabbarUser;
    $tabbarAvatarUrl = $tabbarUser && $tabbarUser->image ? asset(getFilePath('userProfile') . '/' . $tabbarUser->image) : null;
    $tabbarGuestLogoUrl = asset('assets/images/logo_icon/logo.png');
    $tabbarAvatarName = trim((string) ($tabbarUser->username ?? $tabbarUser->firstname ?? 'R'));
    $tabbarAvatarFallback = $tabbarAvatarName !== ''
        ? strtoupper(substr($tabbarAvatarName, 0, 1))
        : 'R';
@endphp

<div class="hub-shell__nav {{ $isBolaoLaunchMode ? 'hub-shell__nav--launch-mobile' : '' }}" aria-label="Menu">
    <div class="hub-mobile-tabbar">
        <nav class="hub-mobile-tabbar__nav {{ $isBolaoLaunchMode ? 'hub-mobile-tabbar__nav--launch' : '' }} {{ $isAppClient ? 'hub-mobile-tabbar__nav--app' : 'hub-mobile-tabbar__nav--site' }}" role="tablist">
            @if($isBolaoLaunchMode)
                <div class="hub-mobile-tabbar__launch-stage">
                    <div class="hub-tab-border-effect"></div>

                    <button type="button" class="hub-mobile-tabbar__btn hub-mobile-tabbar__btn--launch-filter hub-mobile-tabbar__btn--launch-side home active" data-section="inicio" data-url="{{ $hubInicioUrl }}" data-accent="#f97316" aria-label="Início">
                        <span class="hub-mobile-tabbar__launch-filter-label">@lang('Início')</span>
                    </button>

                    <button type="button" class="hub-mobile-tabbar__btn hub-mobile-tabbar__btn--launch-avatar{{ $tabbarIsGuest ? ' is-guest' : '' }}" data-action="user" aria-label="{{ $tabbarIsGuest ? 'Entrar' : 'Abrir perfil' }}">
                        <span class="hub-mobile-tabbar__launch-avatar-glow"></span>
                        <span class="hub-mobile-tabbar__launch-avatar-shell">
                            <span class="hub-mobile-tabbar__launch-avatar-ring"></span>
                            <span class="hub-mobile-tabbar__launch-avatar-ring hub-mobile-tabbar__launch-avatar-ring--outer"></span>
                            <span class="hub-mobile-tabbar__launch-avatar-core">
                                @if($tabbarAvatarUrl)
                                    <img src="{{ $tabbarAvatarUrl }}" alt="Foto de perfil" class="hub-mobile-tabbar__launch-avatar-img">
                                @elseif($tabbarIsGuest)
                                    <img src="{{ $tabbarGuestLogoUrl }}" alt="Rei do Rodeio" class="hub-mobile-tabbar__launch-avatar-img hub-mobile-tabbar__launch-avatar-img--guest">
                                @else
                                    <span class="hub-mobile-tabbar__launch-avatar-fallback">{{ $tabbarAvatarFallback }}</span>
                                @endif
                            </span>
                        </span>
                        <span class="hub-mobile-tabbar__launch-avatar-badge">{{ $tabbarIsGuest ? 'Entrar' : 'Abrir Perfil' }}</span>
                    </button>

                    <button type="button" class="hub-mobile-tabbar__btn hub-mobile-tabbar__btn--launch-filter hub-mobile-tabbar__btn--launch-side user" data-section="pix" data-profile-target="financeiro" data-url="{{ route('hub.perfil') }}" data-accent="#f97316" aria-label="Pix">
                        <span class="hub-mobile-tabbar__launch-filter-label">@lang('Pix')</span>
                    </button>
                </div>
            @else
            <div class="hub-tab-border-effect"></div>

            <button type="button" class="hub-mobile-tabbar__btn home active" data-section="inicio" data-url="{{ $hubInicioUrl }}" data-accent="#f97316" aria-label="Início">
                <svg class="hub-tab-icon" id="hub-icon-home" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 10.5L12 3l9 7.5"></path>
                    <path d="M5 9.8V21h14V9.8"></path>
                    <path d="M9 21v-6h6v6"></path>
                </svg>
                <span class="hub-mobile-tabbar__label">@lang('Início')</span>
            </button>

            <button type="button" class="hub-mobile-tabbar__btn chart" data-section="estatisticas" data-url="{{ route('hub.stats') }}" data-accent="#f97316" aria-label="Estatísticas">
                <svg class="hub-tab-icon" id="hub-icon-stats" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2.99625 7.99624C2.99625 5.23482 5.23482 2.99625 7.99625 2.99625H16.0037C18.7652 2.99625 21.0037 5.23482 21.0037 7.99625V16.0037C21.0037 18.7652 18.7652 21.0037 16.0037 21.0037H7.99624C5.23482 21.0037 2.99625 18.7652 2.99625 16.0037V7.99624Z" />
                    <g>
                        <path d="M7.49813 13.2605V16.0016" />
                        <path d="M10.4994 7.99832V16.0017" />
                        <path d="M13.5006 11.5068V16.0016" />
                        <path d="M16.5019 6.75208V16.0017" />
                    </g>
                </svg>
                <span class="hub-mobile-tabbar__label">@lang('Estatísticas')</span>
            </button>

            @if(!$isAppClient)
                <button type="button" class="hub-mobile-tabbar__btn store" data-section="loja" data-url="{{ route('hub.loja') }}" data-accent="#f59e0b" aria-label="Loja">
                    <svg class="hub-tab-icon" id="hub-icon-store" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 8.5L5.4 4h13.2L20 8.5"></path>
                        <path d="M5 9h14v8.6A2.4 2.4 0 0 1 16.6 20H7.4A2.4 2.4 0 0 1 5 17.6V9Z"></path>
                        <path d="M9 12h6"></path>
                    </svg>
                    <span class="hub-mobile-tabbar__label">@lang('Loja')</span>
                </button>
            @endif

            @if($isAppClient)
                <button type="button" class="hub-mobile-tabbar__btn premium" data-section="premium" data-url="{{ route('hub.premium') }}" data-accent="#f97316" aria-label="Premium">
                    <svg class="hub-tab-icon" id="hub-icon-premium" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 17l-2-9 5.5 4L12 5l3.5 7L21 8l-2 9H5z" />
                        <path d="M5 17h14" />
                    </svg>
                    <span class="hub-mobile-tabbar__label">@lang('Premium')</span>
                </button>

                <button
                    type="button"
                    class="hub-mobile-tabbar__btn portal"
                    data-action="switch-app"
                    aria-label="Ir para a comunidade"
                >
                    <span class="hub-mobile-tabbar__portal-badge" aria-hidden="true">
                        <span class="hub-mobile-tabbar__portal-core">
                            <img
                                class="hub-mobile-tabbar__portal-logo"
                                src="{{ asset('assets/images/logo_icon/logo.png') }}"
                                alt=""
                            >
                        </span>
                    </span>
                    <span class="hub-mobile-tabbar__portal-label">Comunidade</span>
                </button>
            @else
                @if(!auth()->check() || !auth()->user()->isPremium())
                    <button type="button" class="hub-mobile-tabbar__btn premium" data-section="premium" data-url="{{ route('hub.premium') }}" data-accent="#f97316" aria-label="Premium">
                        <svg class="hub-tab-icon" id="hub-icon-premium" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 17l-2-9 5.5 4L12 5l3.5 7L21 8l-2 9H5z" />
                            <path d="M5 17h14" />
                        </svg>
                        <span class="hub-mobile-tabbar__label">@lang('Premium')</span>
                    </button>
                @endif

                <button type="button" class="hub-mobile-tabbar__btn user" data-section="pix" data-profile-target="financeiro" data-url="{{ route('hub.perfil') }}" data-accent="#f97316" aria-label="Pix">
                    <svg class="hub-tab-icon" id="hub-icon-perfil" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12.1601 10.87C12.0601 10.86 11.9401 10.86 11.8301 10.87C9.45006 10.79 7.56006 8.84 7.56006 6.44C7.56006 3.99 9.54006 2 12.0001 2C14.4501 2 16.4401 3.99 16.4401 6.44C16.4301 8.84 14.5401 10.79 12.1601 10.87Z" />
                        <path d="M7.15997 14.56C4.73997 16.18 4.73997 18.82 7.15997 20.43C9.90997 22.27 14.42 22.27 17.17 20.43C19.59 18.81 19.59 16.17 17.17 14.56C14.43 12.73 9.91997 12.73 7.15997 14.56Z" />
                    </svg>
                    <span class="hub-mobile-tabbar__label">@lang('Pix')</span>
                </button>

                @guest
                    <button type="button" class="hub-mobile-tabbar__btn login" data-action="open-auth" aria-label="Entrar">
                        <span class="hub-mobile-tabbar__login-pill">
                            <span>@lang('Entrar')</span>
                            <span aria-hidden="true">></span>
                        </span>
                    </button>
                @endguest

                @auth
                    <button type="button" class="hub-mobile-tabbar__btn logout" data-action="logout" aria-label="Sair">
                        <svg class="hub-tab-icon" id="hub-icon-logout" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8.90002 7.56023C9.21002 3.96023 11.06 2.49023 15.11 2.49023H15.24C19.71 2.49023 21.5 4.28023 21.5 8.75023V15.2702C21.5 19.7402 19.71 21.5302 15.24 21.5302H15.11C11.09 21.5302 9.24002 20.0802 8.91002 16.5402" />
                            <path d="M15 12H3.62" />
                            <path d="M5.85 8.65039L2.5 12.0004L5.85 15.3504" />
                        </svg>
                        <span class="hub-mobile-tabbar__label">@lang('Sair')</span>
                    </button>
                @endauth
            @endif
            @endif
        </nav>
    </div>
</div>
