<aside class="rr-bolao-page__desktop-rail">
    <div class="rr-bolao-stage">
        <img class="rr-bolao-stage__logo" src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio">

        <div class="rr-bolao-stage__card">
            <strong>Bem vindo</strong>
            <span>{{ auth()->check() ? (auth()->user()->username ?? 'cliente') : 'visitante' }}</span>
        </div>

        <a href="{{ route('home') }}" class="rr-btn rr-btn--primary">Início</a>
        <a href="{{ auth()->check() ? route('web.fantasy.my-teams') : route('user.login') }}" class="rr-btn rr-btn--secondary">Pix</a>
        <a href="{{ auth()->check() ? route('user.logout') : route('user.register') }}" class="rr-btn rr-btn--secondary">
            {{ auth()->check() ? 'Editar perfil' : 'Criar conta' }}
        </a>
    </div>
</aside>
