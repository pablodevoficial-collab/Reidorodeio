<aside class="arena-utility">
    @php
        $arenaUser = auth()->user();
        $arenaUserInitial = $arenaUser ? strtoupper(substr((string) ($arenaUser->username ?: $arenaUser->firstname ?: 'U'), 0, 1)) : 'R';
        $arenaUserAvatar = $arenaUser && $arenaUser->image
            ? asset('assets/images/user/profile/' . ltrim((string) $arenaUser->image, '/'))
            : null;
    @endphp
    <button class="arena-utility__toggle" type="button" data-utility-toggle aria-expanded="false">
        <span class="arena-utility__toggle-main">
            <strong class="arena-utility__toggle-label">Menu</strong>
            @auth
                <span class="arena-utility__toggle-avatar" aria-hidden="true">
                    @if($arenaUserAvatar)
                        <img src="{{ $arenaUserAvatar }}" alt="">
                    @else
                        {{ $arenaUserInitial }}
                    @endif
                </span>
            @endauth
        </span>
        <span class="arena-utility__toggle-arrow" aria-hidden="true"></span>
    </button>
    <div class="arena-utility__panel" data-utility-panel>
        <div class="arena-utility__profile">
            <div class="arena-utility__avatar">
                @if($arenaUserAvatar)
                    <img src="{{ $arenaUserAvatar }}" alt="">
                @else
                    {{ $arenaUserInitial }}
                @endif
            </div>
            <div>
                <span>{{ auth()->check() ? 'Seu acesso' : 'Arena oficial' }}</span>
                <strong>{{ auth()->check() ? (auth()->user()->username ?? 'Perfil') : 'Rei do Rodeio' }}</strong>
            </div>
        </div>
        <div class="arena-utility__actions">
            <button class="arena-tool" type="button" data-open-profile>Perfil</button>
            <button class="arena-tool" type="button" data-open-pix>Pix</button>
            <button class="arena-tool" type="button" data-open-rules>Regras</button>
            <button class="arena-tool" type="button" data-open-support>Suporte</button>
        </div>
        @auth
        <a class="arena-logout" href="{{ route('user.logout') }}" aria-label="Logout">&nearr;</a>
        @endauth
    </div>
</aside>
