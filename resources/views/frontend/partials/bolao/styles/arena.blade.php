    .rr-arena-gateway {
        min-height: 100vh;
        position: relative;
        display: grid;
        align-items: center;
        padding: 20px;
        isolation: isolate;
        overflow: hidden;
        background:
            linear-gradient(112deg, rgba(3, 7, 18, .94) 0%, rgba(11, 18, 34, .88) 48%, rgba(20, 8, 5, .78) 100%),
            url("{{ asset('assets/admin/images/login.jpg') }}") center/cover no-repeat;
    }

    .rr-arena-gateway::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            linear-gradient(90deg, rgba(245, 158, 11, .12), transparent 28%, rgba(20, 184, 166, .12) 72%, rgba(59, 130, 246, .14)),
            repeating-linear-gradient(90deg, rgba(255,255,255,.05) 0 1px, transparent 1px 92px);
        mask-image: linear-gradient(180deg, transparent, #000 18%, #000 82%, transparent);
        opacity: .5;
        animation: rrGridDrift 15s linear infinite;
    }

    .rr-arena-gateway__shell {
        width: min(1280px, 100%);
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(280px, .74fr) minmax(0, 1.26fr);
        gap: clamp(18px, 3vw, 34px);
        align-items: center;
    }

    .rr-arena-gateway__intro {
        display: grid;
        gap: 18px;
        align-content: center;
        padding-block: 28px;
        animation: rrIntroIn .72s cubic-bezier(.2,.9,.2,1) both;
    }

    .rr-arena-gateway__brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rr-arena-gateway__brand img {
        width: 58px;
        height: 58px;
        object-fit: contain;
        filter: drop-shadow(0 14px 24px rgba(249, 115, 22, .3));
        animation: rrLogoFloat 4.8s ease-in-out infinite;
    }

    .rr-arena-gateway__brand strong {
        display: block;
        color: #fff7ed;
        font-size: 1.02rem;
        font-weight: 900;
        letter-spacing: .04em;
    }

    .rr-arena-gateway__brand span,
    .rr-arena-gateway__kicker,
    .rr-arena-gateway__copy,
    .rr-arena-card__copy,
    .rr-x1-stage__copy,
    .rr-x1-step p {
        color: rgba(226, 232, 240, .78);
    }

    .rr-arena-gateway__kicker {
        width: fit-content;
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid rgba(251, 191, 36, .28);
        background: rgba(15, 23, 42, .62);
        color: #fde68a;
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .rr-arena-gateway__title {
        margin: 0;
        color: #fff7ed;
        font-size: clamp(3rem, 6.2vw, 6.6rem);
        line-height: .84;
        font-weight: 900;
        letter-spacing: 0;
    }

    .rr-arena-gateway__copy {
        max-width: 470px;
        margin: 0;
        font-size: clamp(1rem, 1.35vw, 1.12rem);
        line-height: 1.5;
        font-weight: 700;
    }

    .rr-arena-gateway__status {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .rr-arena-gateway__status span,
    .rr-arena-card__tag,
    .rr-x1-stage__tag {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, .1);
        background: rgba(15, 23, 42, .7);
        color: #e2e8f0;
        font-size: .76rem;
        font-weight: 900;
    }

    .rr-arena-gateway__choices {
        display: grid;
        grid-template-columns: repeat(2, minmax(260px, 1fr));
        gap: 18px;
        perspective: 1200px;
    }

    .rr-arena-card {
        --rr-accent: #f59e0b;
        --rr-accent-2: #ef4444;
        --rr-spot-x: 50%;
        --rr-spot-y: 12%;
        position: relative;
        min-height: 600px;
        overflow: hidden;
        display: grid;
        align-content: space-between;
        gap: 24px;
        padding: clamp(18px, 2vw, 28px);
        border-radius: 8px;
        border: 1px solid color-mix(in srgb, var(--rr-accent) 30%, rgba(255,255,255,.1));
        background:
            radial-gradient(circle at var(--rr-spot-x) var(--rr-spot-y), color-mix(in srgb, var(--rr-accent) 32%, transparent), transparent 28%),
            linear-gradient(180deg, rgba(17, 24, 39, .88), rgba(3, 7, 18, .98));
        box-shadow: 0 28px 70px rgba(0, 0, 0, .34);
        transform-style: preserve-3d;
        transition: transform .42s cubic-bezier(.2,.9,.2,1), border-color .42s ease, box-shadow .42s ease;
        animation: rrCardIn .78s cubic-bezier(.2,.9,.2,1) both;
    }

    .rr-arena-card:nth-child(2) {
        animation-delay: .12s;
    }

    .rr-arena-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, transparent 0 38%, rgba(255,255,255,.18) 48%, transparent 58%),
            linear-gradient(180deg, rgba(255,255,255,.08), transparent 38%);
        transform: translateX(-140%);
        transition: transform .7s ease;
        pointer-events: none;
    }

    .rr-arena-card::after {
        content: "";
        position: absolute;
        inset: auto 18px 18px;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--rr-accent), var(--rr-accent-2));
        transform: scaleX(.42);
        transform-origin: left;
        transition: transform .42s ease;
    }

    .rr-arena-card:hover,
    .rr-arena-card:focus-within {
        transform: translateY(-8px) rotateX(1.2deg);
        border-color: color-mix(in srgb, var(--rr-accent) 62%, rgba(255,255,255,.18));
        box-shadow: 0 34px 84px rgba(0,0,0,.42), 0 0 44px color-mix(in srgb, var(--rr-accent) 18%, transparent);
    }

    .rr-arena-card:hover::before,
    .rr-arena-card:focus-within::before {
        transform: translateX(140%);
    }

    .rr-arena-card:hover::after,
    .rr-arena-card:focus-within::after {
        transform: scaleX(1);
    }

    .rr-arena-card--x1 {
        --rr-accent: #38bdf8;
        --rr-accent-2: #14b8a6;
        background:
            radial-gradient(circle at var(--rr-spot-x) var(--rr-spot-y), color-mix(in srgb, var(--rr-accent) 30%, transparent), transparent 28%),
            linear-gradient(180deg, rgba(8, 16, 32, .9), rgba(3, 7, 18, .98));
    }

    .rr-arena-card__media {
        min-height: 230px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, .08);
        background:
            radial-gradient(circle, color-mix(in srgb, var(--rr-accent) 20%, transparent), transparent 54%),
            linear-gradient(145deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .02));
    }

    .rr-arena-card__media img {
        width: min(72%, 250px);
        max-height: 190px;
        object-fit: contain;
        filter: drop-shadow(0 18px 34px rgba(0, 0, 0, .34));
        transition: transform .42s ease;
    }

    .rr-arena-card__icon {
        width: 144px;
        height: 144px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, .16);
        background: linear-gradient(145deg, rgba(59, 130, 246, .32), rgba(16, 185, 129, .18));
        color: #e0f2fe;
        font-size: 3rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.16), 0 18px 42px rgba(37, 99, 235, .22);
        transition: transform .42s ease;
    }

    .rr-arena-card:hover .rr-arena-card__media img,
    .rr-arena-card:hover .rr-arena-card__icon {
        transform: translateY(-4px) scale(1.06);
    }

    .rr-arena-card__body {
        display: grid;
        gap: 12px;
    }

    .rr-arena-card__title {
        margin: 0;
        color: #fff;
        font-size: clamp(2.3rem, 4vw, 4.35rem);
        line-height: .9;
        font-weight: 900;
        letter-spacing: 0;
    }

    .rr-arena-card__copy {
        margin: 0;
        max-width: 34ch;
        line-height: 1.42;
        font-weight: 700;
    }

    .rr-arena-card__actions {
        display: grid;
        gap: 10px;
    }

    .rr-arena-card__button,
    .rr-arena-back,
    .rr-x1-stage__button {
        min-height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 0;
        border-radius: 8px;
        cursor: pointer;
        color: #fff;
        font-weight: 900;
        text-decoration: none;
        transition: transform .2s ease, filter .2s ease;
    }

    .rr-arena-card__button:hover,
    .rr-arena-card__button:focus-visible,
    .rr-x1-stage__button:hover,
    .rr-x1-stage__button:focus-visible {
        transform: translateY(-2px);
        filter: saturate(1.1) brightness(1.04);
    }

    .rr-arena-card__button--bolao {
        background: linear-gradient(180deg, #f59e0b, #ea580c 56%, #9a3412);
        box-shadow: inset 0 2px 5px rgba(255,255,255,.34), 0 18px 32px rgba(234, 88, 12, .25);
    }

    .rr-arena-card__button--x1,
    .rr-x1-stage__button {
        background: linear-gradient(180deg, #2563eb, #0f766e);
        box-shadow: inset 0 2px 5px rgba(255,255,255,.26), 0 18px 32px rgba(37, 99, 235, .22);
    }

    .rr-arena-back {
        width: fit-content;
        min-height: 42px;
        padding: 0 14px;
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(15, 23, 42, .78);
        color: #e2e8f0;
        font-size: .88rem;
        position: sticky;
        top: 12px;
        z-index: 25;
        margin: 12px 0 0 12px;
    }

    .rr-x1-stage {
        min-height: 100vh;
        padding: 18px;
        display: grid;
        background:
            linear-gradient(120deg, rgba(2, 6, 23, .9), rgba(5, 46, 45, .72)),
            url("{{ asset('assets/admin/images/login.jpg') }}") center/cover no-repeat;
    }

    .rr-x1-stage__shell {
        width: min(1120px, 100%);
        margin: auto;
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 22px;
        align-items: center;
    }

    .rr-x1-stage__panel,
    .rr-x1-step {
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(3, 7, 18, .78);
        box-shadow: 0 28px 70px rgba(0,0,0,.32);
    }

    .rr-x1-stage__panel {
        padding: 26px;
        display: grid;
        gap: 16px;
    }

    .rr-x1-stage__title {
        margin: 0;
        color: #fff7ed;
        font-size: clamp(2.4rem, 5vw, 4.7rem);
        line-height: .9;
        font-weight: 900;
        letter-spacing: 0;
    }

    .rr-x1-stage__copy {
        margin: 0;
        font-size: 1.04rem;
        line-height: 1.65;
        font-weight: 700;
    }

    .rr-x1-stage__grid {
        display: grid;
        gap: 12px;
    }

    .rr-x1-step {
        padding: 18px;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 14px;
        align-items: start;
    }

    .rr-x1-step i {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: linear-gradient(145deg, rgba(37, 99, 235, .34), rgba(16, 185, 129, .18));
        color: #bfdbfe;
    }

    .rr-x1-step strong {
        display: block;
        color: #fff;
        font-weight: 900;
    }

    .rr-x1-step p {
        margin: 5px 0 0;
        line-height: 1.45;
    }

    @media (max-width: 980px) {
        .rr-arena-gateway__shell,
        .rr-x1-stage__shell {
            grid-template-columns: 1fr;
        }

        .rr-arena-gateway__choices {
            grid-template-columns: 1fr;
        }

        .rr-arena-card {
            min-height: 420px;
        }
    }

    @media (max-width: 767px) {
        .rr-arena-gateway,
        .rr-x1-stage {
            padding: 12px;
        }

        .rr-arena-gateway__intro {
            padding-block: 10px;
        }

        .rr-arena-gateway__title,
        .rr-x1-stage__title {
            font-size: clamp(2.55rem, 15vw, 3.7rem);
        }

        .rr-arena-card {
            min-height: auto;
            padding: 18px;
        }

        .rr-arena-card__media {
            min-height: 150px;
        }

        .rr-x1-step {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rr-arena-gateway::before,
        .rr-arena-gateway__intro,
        .rr-arena-gateway__brand img,
        .rr-arena-card {
            animation: none !important;
        }

        .rr-arena-card,
        .rr-arena-card__media img,
        .rr-arena-card__icon,
        .rr-arena-card__button {
            transition: none !important;
        }
    }

    @keyframes rrIntroIn {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes rrCardIn {
        from { opacity: 0; transform: translateY(26px) scale(.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes rrLogoFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    @keyframes rrGridDrift {
        from { transform: translateX(0); }
        to { transform: translateX(92px); }
    }

