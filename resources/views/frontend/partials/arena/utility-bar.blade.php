<aside class="arena-utility">
    <div class="arena-utility__profile">
        <div class="arena-utility__avatar">{{ auth()->check() ? strtoupper(substr((string) auth()->user()->username, 0, 1)) : 'R' }}</div>
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
</aside>
