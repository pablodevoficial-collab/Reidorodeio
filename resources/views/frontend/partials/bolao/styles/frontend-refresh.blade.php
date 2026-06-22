    /* Rei do Rodeio: premium betting-grade interface refresh. */
    .rr-pro-ready {
        --rr-bg: #07090d;
        --rr-surface: #10151d;
        --rr-surface-2: #151b25;
        --rr-border: rgba(255, 255, 255, .09);
        --rr-border-strong: rgba(214, 151, 54, .42);
        --rr-text: #f8fafc;
        --rr-muted: rgba(226, 232, 240, .68);
        --rr-muted-2: rgba(148, 163, 184, .78);
        --rr-gold: #d69736;
        --rr-gold-soft: rgba(214, 151, 54, .14);
        --rr-green: #15803d;
        --rr-teal: #0f766e;
    }

    .rr-pro-ready .rr-arena-gateway,
    .rr-pro-ready .rr-live,
    .rr-pro-ready .rr-x1-stage {
        background:
            linear-gradient(180deg, rgba(214,151,54,.06), transparent 220px),
            linear-gradient(180deg, #090c11 0%, #07090d 56%, #05070b 100%) !important;
        color: var(--rr-text) !important;
    }

    .rr-pro-ready .rr-arena-gateway::before,
    .rr-pro-ready .rr-arena-gateway::after,
    .rr-pro-ready .rr-cards__particles,
    .rr-pro-ready .rr-card__particles,
    .rr-pro-ready .rr-card__particle,
    .rr-pro-ready .rr-card__prize-wrap::before,
    .rr-pro-ready .rr-card__prize-wrap::after,
    .rr-pro-ready .rr-sponsor-aura,
    .rr-pro-ready .rr-sponsor-orbit,
    .rr-pro-ready .rr-sponsor-particles {
        display: none !important;
    }

    .rr-pro-ready .rr-arena-gateway {
        min-height: 100dvh !important;
        align-items: start !important;
        padding: clamp(18px, 3vw, 40px) !important;
        overflow: auto !important;
    }

    .rr-pro-ready .rr-arena-gateway__shell {
        width: min(1180px, 100%) !important;
        min-height: auto !important;
        margin: 0 auto !important;
        display: grid !important;
        grid-template-columns: minmax(300px, .78fr) minmax(0, 1.22fr) !important;
        gap: clamp(22px, 4vw, 54px) !important;
        align-items: start !important;
    }

    .rr-pro-ready .rr-arena-gateway__intro {
        gap: 16px !important;
        padding: 10px 0 0 !important;
        animation: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__intro::before {
        content: "" !important;
        width: 44px !important;
        height: 2px !important;
        background: var(--rr-gold) !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand {
        width: fit-content !important;
        max-width: 100% !important;
        padding: 7px 12px 7px 7px !important;
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        background: rgba(255,255,255,.045) !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand img {
        width: 42px !important;
        height: 42px !important;
        animation: none !important;
        filter: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand strong {
        color: var(--rr-text) !important;
        font-size: .9rem !important;
        letter-spacing: 0 !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand span {
        color: var(--rr-muted-2) !important;
        font-size: .78rem !important;
        letter-spacing: 0 !important;
        font-weight: 700 !important;
    }

    .rr-pro-ready .rr-arena-gateway__kicker,
    .rr-pro-ready .status-badge,
    .rr-pro-ready .rr-arena-card__tag,
    .rr-pro-ready .rr-x1-stage__tag,
    .rr-pro-ready .rr-pill,
    .rr-pro-ready .rr-card__badge {
        min-height: auto !important;
        width: fit-content !important;
        padding: 5px 9px !important;
        border-radius: 6px !important;
        border: 1px solid rgba(214,151,54,.22) !important;
        background: rgba(214,151,54,.08) !important;
        color: #e8c782 !important;
        font-size: .72rem !important;
        font-weight: 800 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__kicker i,
    .rr-pro-ready .status-badge i,
    .rr-pro-ready .rr-arena-card__tag i,
    .rr-pro-ready .rr-card__badge i {
        color: var(--rr-gold) !important;
    }

    .rr-pro-ready .rr-arena-gateway__title {
        max-width: 10ch !important;
        margin: 0 !important;
        color: #fff7e5 !important;
        background: none !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(3rem, 7vw, 5.8rem) !important;
        line-height: .96 !important;
        letter-spacing: 0 !important;
        text-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__copy {
        max-width: 44ch !important;
        margin: 0 !important;
        color: var(--rr-muted) !important;
        font-size: clamp(.98rem, 1.2vw, 1.08rem) !important;
        line-height: 1.55 !important;
        font-weight: 650 !important;
    }

    .rr-pro-ready .rr-arena-gateway__status {
        display: flex !important;
        justify-content: flex-start !important;
        gap: 8px !important;
    }

    .rr-pro-ready .status-badge {
        min-width: 0 !important;
        color: var(--rr-muted) !important;
        border-color: var(--rr-border) !important;
        background: rgba(255,255,255,.045) !important;
    }

    .rr-pro-ready .status-badge:hover {
        transform: none !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__choices {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 10px !important;
        perspective: none !important;
    }

    .rr-pro-ready .rr-arena-card {
        min-height: auto !important;
        display: grid !important;
        grid-template-columns: 78px minmax(0, 1fr) auto !important;
        align-items: center !important;
        gap: 14px !important;
        padding: 16px !important;
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        border-left: 3px solid var(--rr-gold) !important;
        background: linear-gradient(180deg, rgba(255,255,255,.045), rgba(255,255,255,.025)) !important;
        box-shadow: none !important;
        transform: none !important;
        transition: border-color .18s ease, background .18s ease !important;
        animation: none !important;
        will-change: auto !important;
    }

    .rr-pro-ready .rr-arena-card--x1 {
        border-left-color: var(--rr-green) !important;
    }

    .rr-pro-ready .rr-arena-card--ranking {
        border-left-color: var(--rr-teal) !important;
    }

    .rr-pro-ready .rr-arena-card:hover,
    .rr-pro-ready .rr-arena-card:focus-within {
        border-color: rgba(214,151,54,.34) !important;
        background: rgba(255,255,255,.06) !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-card::before,
    .rr-pro-ready .rr-arena-card::after {
        display: none !important;
    }

    .rr-pro-ready .rr-arena-card__media {
        width: 78px !important;
        min-height: 78px !important;
        height: 78px !important;
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        background: #0b1018 !important;
    }

    .rr-pro-ready .rr-arena-card__media img {
        width: 54px !important;
        max-height: 54px !important;
        animation: none !important;
        filter: none !important;
    }

    .rr-pro-ready .rr-arena-card__icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 8px !important;
        background: rgba(214,151,54,.1) !important;
        color: #e8c782 !important;
        box-shadow: none !important;
        font-size: 1.25rem !important;
    }

    .rr-pro-ready .rr-arena-card--x1 .rr-arena-card__icon {
        background: rgba(21,128,61,.11) !important;
        color: #86efac !important;
    }

    .rr-pro-ready .rr-arena-card--ranking .rr-arena-card__icon {
        background: rgba(15,118,110,.12) !important;
        color: #99f6e4 !important;
    }

    .rr-pro-ready .rr-arena-card__body {
        gap: 7px !important;
        min-width: 0 !important;
    }

    .rr-pro-ready .rr-arena-card__title,
    .rr-pro-ready .rr-x1-stage__title,
    .rr-pro-ready .rr-side__headline {
        color: #fff7e5 !important;
        letter-spacing: 0 !important;
        text-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-card__title {
        font-size: clamp(1.8rem, 3.6vw, 2.8rem) !important;
        line-height: 1 !important;
    }

    .rr-pro-ready .rr-arena-card__copy {
        max-width: 44ch !important;
        color: var(--rr-muted) !important;
        font-size: .9rem !important;
        line-height: 1.35 !important;
        font-weight: 650 !important;
    }

    .rr-pro-ready .rr-arena-card__actions {
        min-width: 170px !important;
    }

    .rr-pro-ready .rr-arena-card__button,
    .rr-pro-ready .rr-x1-stage__button,
    .rr-pro-ready .rr-hero__btn,
    .rr-pro-ready .rr-card__btn,
    .rr-pro-ready .rr-side__nav-btn,
    .rr-pro-ready .rr-mobile-actions__btn,
    .rr-pro-ready .rr-arena-back,
    .rr-pro-ready .rr-competitor__add {
        min-height: 42px !important;
        border-radius: 7px !important;
        border: 1px solid var(--rr-border) !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        transform: none !important;
        box-shadow: none !important;
        text-shadow: none !important;
        transition: background .18s ease, border-color .18s ease, color .18s ease !important;
    }

    .rr-pro-ready .rr-arena-card__button,
    .rr-pro-ready .rr-card__btn--enter,
    .rr-pro-ready .rr-hero__btn,
    .rr-pro-ready .rr-competitor__add {
        background: var(--rr-gold) !important;
        color: #130f08 !important;
        border-color: rgba(214,151,54,.7) !important;
    }

    .rr-pro-ready .rr-arena-card__button--x1,
    .rr-pro-ready .rr-x1-stage__button {
        background: var(--rr-green) !important;
        color: #ecfdf5 !important;
        border-color: rgba(34,197,94,.38) !important;
    }

    .rr-pro-ready .rr-arena-card__button--ranking,
    .rr-pro-ready .rr-card__btn--ranking,
    .rr-pro-ready .rr-card__btn--notify {
        background: var(--rr-teal) !important;
        color: #ecfeff !important;
        border-color: rgba(45,212,191,.3) !important;
    }

    .rr-pro-ready .rr-card__btn--locked {
        background: rgba(255,255,255,.08) !important;
        color: rgba(226,232,240,.76) !important;
        border-color: var(--rr-border) !important;
        cursor: not-allowed !important;
    }

    .rr-pro-ready .rr-arena-card__button:hover,
    .rr-pro-ready .rr-arena-card__button:focus-visible,
    .rr-pro-ready .rr-card__btn:hover,
    .rr-pro-ready .rr-card__btn:focus-visible,
    .rr-pro-ready .rr-hero__btn:hover,
    .rr-pro-ready .rr-hero__btn:focus-visible {
        filter: brightness(1.05) !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-live {
        min-height: 100dvh !important;
        gap: 12px !important;
        padding: 14px 14px 86px !important;
    }

    .rr-pro-ready .rr-panel,
    .rr-pro-ready .rr-x1-stage__panel,
    .rr-pro-ready .rr-x1-step,
    .rr-pro-ready .rr-ranking-hero,
    .rr-pro-ready .rr-ranking-list-wrap,
    .rr-pro-ready .rr-ranking-podium-card,
    .rr-pro-ready .rr-ranking-row {
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        background: rgba(255,255,255,.045) !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
    }

    .rr-pro-ready .rr-live__hero,
    .rr-pro-ready .rr-cards {
        width: min(1180px, 100%) !important;
        margin-inline: auto !important;
    }

    .rr-pro-ready .rr-live__hero {
        padding: 14px !important;
    }

    .rr-pro-ready .rr-live__grid {
        grid-template-columns: minmax(0, .92fr) minmax(260px, 340px) minmax(0, .9fr) !important;
        gap: 12px !important;
        align-items: stretch !important;
    }

    .rr-pro-ready .rr-side,
    .rr-pro-ready .rr-hero__center,
    .rr-pro-ready .rr-side__stats {
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        background: #0d1219 !important;
        box-shadow: none !important;
        padding: 14px !important;
    }

    .rr-pro-ready .rr-side__headline {
        font-size: clamp(1.55rem, 3vw, 2.65rem) !important;
        line-height: 1.06 !important;
    }

    .rr-pro-ready .rr-side__text,
    .rr-pro-ready .rr-x1-stage__copy,
    .rr-pro-ready .rr-x1-step p {
        color: var(--rr-muted) !important;
    }

    .rr-pro-ready .rr-side__nav {
        gap: 8px !important;
    }

    .rr-pro-ready .rr-side__nav-btn {
        justify-content: flex-start !important;
        background: rgba(255,255,255,.045) !important;
        color: var(--rr-text) !important;
    }

    .rr-pro-ready .rr-logo-wrap {
        width: 132px !important;
        padding: 10px !important;
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        background: #080c12 !important;
        box-shadow: none !important;
        overflow: hidden !important;
    }

    .rr-pro-ready .rr-logo-wrap::before {
        display: none !important;
    }

    .rr-pro-ready .rr-logo {
        height: 88px !important;
        filter: none !important;
    }

    .rr-pro-ready .rr-hero__name {
        width: min(100%, 300px) !important;
        min-height: 44px !important;
        border-radius: 7px !important;
        color: var(--rr-text) !important;
        background: rgba(255,255,255,.045) !important;
        border: 1px solid var(--rr-border) !important;
    }

    .rr-pro-ready .rr-countdown {
        min-height: 42px !important;
        border-radius: 7px !important;
        background: rgba(255,255,255,.045) !important;
        border: 1px solid var(--rr-border) !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-mobile-actions {
        width: min(1180px, 100%) !important;
        margin: 0 auto !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 10px !important;
    }

    .rr-pro-ready .rr-mobile-actions__profile,
    .rr-pro-ready .rr-mobile-actions__pix {
        background: #10151d !important;
        color: var(--rr-text) !important;
        border-color: var(--rr-border) !important;
    }

    .rr-pro-ready .rr-cards {
        padding: 12px !important;
        overflow: hidden !important;
        max-width: 100% !important;
    }

    .rr-pro-ready .rr-cards__grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important;
        grid-auto-flow: row !important;
        grid-auto-columns: auto !important;
        gap: 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important;
        scroll-snap-type: none !important;
    }

    .rr-pro-ready .rr-card {
        width: 100% !important;
        min-height: auto !important;
        padding: 14px !important;
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        background: #10151d !important;
        box-shadow: none !important;
        transform: none !important;
        opacity: 1 !important;
        scroll-snap-align: unset !important;
    }

    .rr-pro-ready .rr-card:hover {
        transform: none !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-card__ghost {
        width: 74px !important;
        inset: 14px 14px auto auto !important;
        opacity: .06 !important;
    }

    .rr-pro-ready .rr-card__top,
    .rr-pro-ready .rr-card__meta {
        align-items: center !important;
    }

    .rr-pro-ready .rr-card__event-row {
        margin-top: 14px !important;
        display: grid !important;
        grid-template-columns: 46px minmax(0, 1fr) !important;
        align-items: center !important;
        gap: 10px !important;
        min-width: 0 !important;
    }

    .rr-pro-ready .rr-card__event-logo {
        width: 46px !important;
        height: 46px !important;
        object-fit: contain !important;
        border-radius: 8px !important;
        border: 1px solid var(--rr-border) !important;
        background: #080c12 !important;
        padding: 5px !important;
    }

    .rr-pro-ready .rr-card__event {
        margin-top: 0 !important;
        text-align: left !important;
        gap: 3px !important;
        min-width: 0 !important;
    }

    .rr-pro-ready .rr-card__event strong {
        color: var(--rr-text) !important;
        font-size: 1rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .rr-pro-ready .rr-card__event span {
        color: var(--rr-muted-2) !important;
        font-size: .78rem !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .rr-pro-ready .rr-card__prize-wrap {
        margin-top: 14px !important;
        min-height: auto !important;
        padding: 12px !important;
        border-radius: 8px !important;
        border: 1px solid rgba(214,151,54,.2) !important;
        background: rgba(214,151,54,.07) !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-card__prize-label {
        min-height: auto !important;
        width: fit-content !important;
        margin: 0 0 8px !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        color: var(--rr-muted-2) !important;
        font-size: .72rem !important;
    }

    .rr-pro-ready .rr-card__prize-frame {
        min-height: auto !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-card__prize-value {
        color: #fff7e5 !important;
        font-size: clamp(1.75rem, 4vw, 2.35rem) !important;
        line-height: 1 !important;
        letter-spacing: 0 !important;
        text-shadow: none !important;
    }

    .rr-pro-ready .rr-card__prize-current {
        margin-top: 6px !important;
        color: var(--rr-muted) !important;
        font-size: .74rem !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
    }

    .rr-pro-ready .rr-card__meta {
        margin-top: 12px !important;
        color: var(--rr-muted) !important;
        font-size: .78rem !important;
    }

    .rr-pro-ready .rr-card__actions {
        margin-top: 12px !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 8px !important;
    }

    .rr-pro-ready .rr-card__btn {
        min-height: 42px !important;
        font-size: .9rem !important;
    }

    .rr-pro-ready .rr-x1-stage {
        min-height: 100dvh !important;
        padding: 18px !important;
    }

    .rr-pro-ready .rr-x1-stage__shell {
        width: min(980px, 100%) !important;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr) !important;
        gap: 14px !important;
    }

    .rr-pro-ready .rr-x1-stage__panel {
        padding: 18px !important;
    }

    .rr-pro-ready .rr-x1-stage__title {
        font-size: clamp(2.2rem, 5vw, 4.2rem) !important;
        line-height: 1.02 !important;
    }

    .rr-pro-ready .rr-x1-step {
        padding: 14px !important;
    }

    .rr-pro-ready .rr-ranking-row__bar span {
        background: linear-gradient(90deg, var(--rr-gold), var(--rr-teal)) !important;
        box-shadow: none !important;
    }

    .rr-pro-ready *,
    .rr-pro-ready *::before,
    .rr-pro-ready *::after {
        scroll-behavior: auto !important;
    }

    @media (max-width: 1100px) {
        .rr-pro-ready .rr-arena-gateway__shell,
        .rr-pro-ready .rr-live__grid,
        .rr-pro-ready .rr-x1-stage__shell {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 767px) {
        .rr-pro-ready .rr-arena-gateway {
            padding: 14px !important;
        }

        .rr-pro-ready .rr-arena-gateway__shell {
            gap: 14px !important;
        }

        .rr-pro-ready .rr-arena-gateway__intro {
            gap: 10px !important;
            padding-top: 0 !important;
        }

        .rr-pro-ready .rr-arena-gateway__brand {
            padding: 6px 10px 6px 6px !important;
        }

        .rr-pro-ready .rr-arena-gateway__brand img {
            width: 36px !important;
            height: 36px !important;
        }

        .rr-pro-ready .rr-arena-gateway__kicker {
            display: none !important;
        }

        .rr-pro-ready .rr-arena-gateway__title {
            max-width: 100% !important;
            font-size: clamp(2.45rem, 12vw, 3.25rem) !important;
            line-height: 1 !important;
        }

        .rr-pro-ready .rr-arena-gateway__copy {
            font-size: .9rem !important;
            line-height: 1.42 !important;
        }

        .rr-pro-ready .rr-arena-gateway__status {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 6px !important;
        }

        .rr-pro-ready .status-badge {
            width: auto !important;
            justify-content: center !important;
            padding: 7px 6px !important;
            font-size: .72rem !important;
        }

        .rr-pro-ready .rr-arena-card {
            grid-template-columns: 54px minmax(0, 1fr) !important;
            gap: 10px !important;
            padding: 12px !important;
        }

        .rr-pro-ready .rr-arena-card__media {
            width: 54px !important;
            min-height: 54px !important;
            height: 54px !important;
            grid-row: 1 / span 2 !important;
        }

        .rr-pro-ready .rr-arena-card__media img {
            width: 38px !important;
            max-height: 38px !important;
        }

        .rr-pro-ready .rr-arena-card__icon {
            width: 36px !important;
            height: 36px !important;
            font-size: 1rem !important;
        }

        .rr-pro-ready .rr-arena-card__tag {
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            font-size: .7rem !important;
        }

        .rr-pro-ready .rr-arena-card__title {
            font-size: clamp(1.45rem, 7vw, 1.95rem) !important;
        }

        .rr-pro-ready .rr-arena-card__copy {
            font-size: .78rem !important;
            line-height: 1.3 !important;
        }

        .rr-pro-ready .rr-arena-card__actions {
            grid-column: 2 !important;
            min-width: 0 !important;
        }

        .rr-pro-ready .rr-arena-card__button {
            width: 100% !important;
            min-height: 38px !important;
            padding: 0 10px !important;
            font-size: .82rem !important;
        }

        .rr-pro-ready .rr-live {
            padding: 10px 10px 92px !important;
            gap: 10px !important;
        }

        .rr-pro-ready .rr-arena-back {
            margin: 0 !important;
            position: static !important;
            width: 100% !important;
            justify-content: flex-start !important;
            background: #10151d !important;
            color: var(--rr-text) !important;
        }

        .rr-pro-ready .rr-live__hero,
        .rr-pro-ready .rr-cards {
            padding: 10px !important;
        }

        .rr-pro-ready .rr-side,
        .rr-pro-ready .rr-side__stats {
            display: none !important;
        }

        .rr-pro-ready .rr-hero__center {
            padding: 12px !important;
            gap: 9px !important;
        }

        .rr-pro-ready .rr-logo-wrap {
            width: 96px !important;
        }

        .rr-pro-ready .rr-logo {
            height: 62px !important;
        }

        .rr-pro-ready .rr-hero__name {
            min-height: 40px !important;
            font-size: .9rem !important;
        }

        .rr-pro-ready .rr-countdown,
        .rr-pro-ready .rr-hero__btn {
            width: 100% !important;
        }

        .rr-pro-ready .rr-mobile-actions {
            display: grid !important;
            gap: 8px !important;
        }

        .rr-pro-ready .rr-mobile-actions__btn {
            min-height: 44px !important;
            font-size: .9rem !important;
        }

        .rr-pro-ready .rr-cards__grid {
            grid-template-columns: 1fr !important;
            grid-auto-flow: row !important;
            grid-auto-columns: auto !important;
            gap: 10px !important;
            overflow: hidden !important;
            scroll-snap-type: none !important;
        }

        .rr-pro-ready .rr-card {
            padding: 12px !important;
        }

        .rr-pro-ready .rr-card__top {
            gap: 8px !important;
        }

        .rr-pro-ready .rr-card__badge {
            max-width: 70% !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .rr-pro-ready .rr-card__ghost {
            width: 58px !important;
            opacity: .05 !important;
        }

        .rr-pro-ready .rr-card__event-row {
            grid-template-columns: 42px minmax(0, 1fr) !important;
            gap: 9px !important;
        }

        .rr-pro-ready .rr-card__event-logo {
            width: 42px !important;
            height: 42px !important;
        }

        .rr-pro-ready .rr-card__prize-wrap {
            padding: 10px !important;
        }

        .rr-pro-ready .rr-card__prize-value {
            font-size: 1.9rem !important;
        }

        .rr-pro-ready .rr-card__meta {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
            font-size: .72rem !important;
        }

        .rr-pro-ready .rr-card__actions {
            grid-template-columns: 1fr 1fr !important;
        }

        .rr-pro-ready .rr-card__btn {
            min-height: 40px !important;
            font-size: .84rem !important;
        }

        .rr-pro-ready .rr-x1-stage {
            padding: 12px !important;
        }

        .rr-pro-ready .rr-x1-stage__panel {
            padding: 14px !important;
        }

        .rr-pro-ready .rr-x1-stage__title {
            font-size: clamp(1.9rem, 9vw, 2.7rem) !important;
        }

        .rr-pro-ready .rr-x1-step {
            grid-template-columns: 34px minmax(0, 1fr) !important;
            gap: 10px !important;
            padding: 12px !important;
        }
    }
