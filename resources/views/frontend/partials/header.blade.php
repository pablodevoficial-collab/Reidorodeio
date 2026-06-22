<header class="rr-site-header">
    <div class="rr-site-shell rr-site-header__inner">
        <a class="rr-site-header__brand" href="{{ route('home') }}">
            <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio" class="rr-site-header__logo">
            <div class="rr-site-header__copy">
                <strong>Rei do Rodeio</strong>
                <span>Bolão oficial</span>
            </div>
        </a>

        <nav class="rr-site-header__actions" aria-label="Ações principais">
            @auth
                <a href="{{ route('home') }}" class="rr-btn rr-btn--secondary">Minha área</a>
                <a href="{{ route('user.logout') }}" class="rr-btn rr-btn--primary">Sair</a>
            @else
                <a href="{{ route('user.login') }}" class="rr-btn rr-btn--secondary">Entrar</a>
                <a href="{{ route('user.register') }}" class="rr-btn rr-btn--primary">Criar conta</a>
            @endauth
        </nav>
    </div>
</header>

<style>
    .rr-site-header {
        position: sticky;
        top: 0;
        z-index: 40;
        padding: 18px 0 14px;
        background: rgba(5, 8, 22, 0.9);
        backdrop-filter: blur(18px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .rr-site-header::after {
        content: "";
        display: block;
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent 0%, rgba(249, 115, 22, 0.32) 50%, transparent 100%);
        pointer-events: none;
    }

    .rr-site-header__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .rr-site-header__brand {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .rr-site-header__logo {
        width: 54px;
        height: 54px;
        object-fit: contain;
        filter: drop-shadow(0 12px 22px rgba(249, 115, 22, 0.28));
    }

    .rr-site-header__copy {
        display: grid;
        gap: 2px;
    }

    .rr-site-header__copy strong {
        color: #fff7ed;
        font-size: 1.05rem;
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    .rr-site-header__copy span {
        color: #94a3b8;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.16em;
    }

    .rr-site-header__actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    @media (max-width: 767px) {
        .rr-site-header {
            padding: 12px 0;
        }

        .rr-site-header__inner {
            flex-wrap: wrap;
        }

        .rr-site-header__brand {
            width: 100%;
        }

        .rr-site-header__actions {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rr-site-header__actions .rr-btn {
            width: 100%;
        }
    }
</style>
