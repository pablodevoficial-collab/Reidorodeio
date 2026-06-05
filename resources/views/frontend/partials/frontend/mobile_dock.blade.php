<nav class="rr-bolao-mobile-dock" aria-label="Ações rápidas mobile">
    <a href="{{ route('home') }}" class="rr-bolao-mobile-dock__btn rr-bolao-mobile-dock__btn--home">Início</a>
    <a href="{{ auth()->check() ? route('web.fantasy.my-teams') : route('user.login') }}" class="rr-bolao-mobile-dock__btn rr-bolao-mobile-dock__btn--pix">Pix</a>
</nav>
