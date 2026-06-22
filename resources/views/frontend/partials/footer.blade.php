<footer class="rr-site-footer">
    <div class="rr-site-shell rr-site-footer__inner">
        <div class="rr-site-footer__brand">
            <strong>Rei do Rodeio</strong>
            <span>Operação focada em bolão.</span>
        </div>

        <nav class="rr-site-footer__links" aria-label="Links institucionais">
            <a href="{{ route('home') }}">Início</a>
            @guest
                <a href="{{ route('user.login') }}">Entrar</a>
                <a href="{{ route('user.register') }}">Criar conta</a>
            @else
                <a href="{{ route('web.fantasy.my-teams') }}">Minhas equipes</a>
                <a href="{{ route('user.logout') }}">Sair</a>
            @endguest
            <a href="{{ route('rules.fantasy') }}">Regras</a>
            <a href="https://wa.me/5547997953323?text={{ urlencode('Olá! Preciso de ajuda com o bolão do Rei do Rodeio.') }}" target="_blank" rel="noopener">Suporte</a>
        </nav>
    </div>
</footer>

<style>
    .rr-site-footer {
        padding: 0 0 28px;
    }

    .rr-site-footer__inner {
        display: grid;
        gap: 18px;
        padding: 22px 24px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(14px);
    }

    .rr-site-footer__brand {
        display: grid;
        gap: 4px;
    }

    .rr-site-footer__brand strong {
        color: #fff7ed;
        font-size: 1rem;
        font-weight: 900;
    }

    .rr-site-footer__brand span {
        color: #94a3b8;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .rr-site-footer__links {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .rr-site-footer__links a {
        display: inline-flex;
        align-items: center;
        min-height: 42px;
        padding: 0 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        color: #cbd5e1;
        font-size: 0.9rem;
        font-weight: 700;
    }

    @media (max-width: 767px) {
        .rr-site-footer__inner {
            padding: 18px;
        }

        .rr-site-footer__links {
            display: grid;
            grid-template-columns: 1fr;
        }

        .rr-site-footer__links a {
            justify-content: center;
        }
    }
</style>
