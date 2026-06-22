    #rrScreenLoader {
        position: fixed !important;
        inset: 0 !important;
        z-index: 99999 !important;
        width: 100vw !important;
        height: 100dvh !important;
        min-height: 100dvh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 24px !important;
        margin: 0 !important;
        background:
            radial-gradient(circle at 50% 28%, rgba(254, 221, 0, .16), transparent 28%),
            radial-gradient(circle at 50% 72%, rgba(0, 151, 57, .14), transparent 32%),
            linear-gradient(180deg, #020617 0%, #050816 100%) !important;
        color: #fff7ed !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        overflow: hidden !important;
        transition: opacity .35s ease, visibility .35s ease !important;
        transform: translateZ(0) !important;
    }

    #rrScreenLoader.is-hidden {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    #rrScreenLoader .rr-screen-loader__panel {
        width: min(100%, 380px) !important;
        display: grid !important;
        justify-items: center !important;
        gap: 18px !important;
        text-align: center !important;
    }

    #rrScreenLoader .rr-screen-loader__logo {
        width: 82px !important;
        height: 82px !important;
        object-fit: contain !important;
        filter: drop-shadow(0 16px 28px rgba(254, 221, 0, .18)) !important;
    }

    #rrScreenLoader .rr-screen-loader__eyebrow {
        margin: 0 !important;
        color: #fde68a !important;
        font-size: .72rem !important;
        font-weight: 900 !important;
        letter-spacing: .16em !important;
        text-transform: uppercase !important;
    }

    #rrScreenLoader .rr-screen-loader__title {
        margin: 0 !important;
        color: #fff !important;
        font-size: clamp(1.6rem, 7vw, 2.3rem) !important;
        line-height: 1 !important;
        font-weight: 900 !important;
    }

    #rrScreenLoader .rr-screen-loader__bar {
        width: 100% !important;
        height: 10px !important;
        overflow: hidden !important;
        border-radius: 999px !important;
        border: 1px solid rgba(255,255,255,.12) !important;
        background: rgba(15, 23, 42, .9) !important;
    }

    #rrScreenLoader .rr-screen-loader__progress {
        width: var(--rr-loader-progress, 8%) !important;
        height: 100% !important;
        border-radius: inherit !important;
        background: linear-gradient(90deg, #fedd00, #009739 58%, #38bdf8) !important;
        box-shadow: 0 0 22px rgba(254, 221, 0, .28) !important;
        transition: width .32s ease !important;
    }

    #rrScreenLoader .rr-screen-loader__meta {
        margin: 0 !important;
        color: rgba(226, 232, 240, .74) !important;
        font-size: .88rem !important;
        font-weight: 800 !important;
    }

    @media (max-width: 767px) {
        #rrScreenLoader {
            padding: 18px !important;
        }

        #rrScreenLoader .rr-screen-loader__panel {
            width: min(100%, 320px) !important;
            gap: 16px !important;
        }

        #rrScreenLoader .rr-screen-loader__logo {
            width: 72px !important;
            height: 72px !important;
        }

        #rrScreenLoader .rr-screen-loader__title {
            font-size: clamp(1.42rem, 8vw, 2rem) !important;
        }

        #rrScreenLoader .rr-screen-loader__meta {
            font-size: .82rem !important;
        }

        html,
        body {
            scroll-behavior: auto !important;
            overscroll-behavior-y: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-y;
        }

        .rr-app,
        .rr-live,
        .rr-cards,
        .rr-cards__grid {
            gap: 10px !important;
        }

        .rr-live {
            padding-bottom: calc(96px + env(safe-area-inset-bottom)) !important;
        }

        .rr-stage,
        .rr-panel,
        .rr-card,
        .rr-box,
        .rr-ranking-hero,
        .rr-ranking-list-wrap,
        .rr-modal__dialog,
        .rr-arena-card {
            box-shadow: none !important;
            filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            will-change: auto !important;
        }

        .rr-cards,
        .rr-card,
        .rr-ranking-list-wrap,
        .rr-team,
        .rr-rank {
            content-visibility: auto;
            contain-intrinsic-size: 520px;
        }

        .rr-card {
            min-height: auto !important;
            border-radius: 18px !important;
            transform: none !important;
        }

        .rr-card__particles,
        .rr-cards__particles,
        .rr-cards::before,
        .rr-cards::after,
        .rr-card__prize-wrap::before,
        .rr-card__prize-wrap::after,
        .rr-sponsor-particle,
        .rr-sponsor-ring,
        .rr-sponsor-aura,
        .rr-arena-gateway::before,
        .rr-arena-card::before {
            display: none !important;
            animation: none !important;
        }

        .rr-logo-wrap,
        .rr-logo-wrap.is-sponsor-showcase,
        .rr-arena-card,
        .rr-arena-card__media img,
        .rr-arena-card__icon,
        .rr-arena-gateway__brand img {
            animation: none !important;
            transform: none !important;
            transition: none !important;
            will-change: auto !important;
        }

        .rr-modal {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        .rr-modal__dialog {
            max-height: 100dvh !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }
    }
