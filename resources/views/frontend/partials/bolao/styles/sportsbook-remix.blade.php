    .rr-pro-ready {
        --rr-book-bg: #081018;
        --rr-book-bg-2: #0b141d;
        --rr-book-panel: #101924;
        --rr-book-panel-2: #172231;
        --rr-book-panel-3: #1d2938;
        --rr-book-border: rgba(255, 255, 255, 0.08);
        --rr-book-border-strong: rgba(255, 181, 53, 0.28);
        --rr-book-text: #f5f7fb;
        --rr-book-muted: #96a2b1;
        --rr-book-muted-2: #c5ced8;
        --rr-book-green: #0f9259;
        --rr-book-green-deep: #0a7647;
        --rr-book-gold: #ffb535;
        --rr-book-gold-deep: #d98a17;
        --rr-book-cyan: #1fb6a5;
        --rr-book-shadow: 0 20px 48px rgba(0, 0, 0, 0.28);
    }

    .rr-pro-ready .rr-hidden {
        display: none !important;
    }

    body {
        background: #050d15 !important;
    }

    .rr-main {
        width: 100% !important;
        min-height: 100dvh !important;
        padding: 0 !important;
    }

    .rr-main > .rr-site-shell {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }

    .rr-app {
        gap: 0 !important;
        min-height: 100dvh !important;
    }

    html.rr-home-viewport-lock,
    body.rr-home-viewport-lock {
        height: 100%;
        overflow: hidden !important;
    }

    .rr-pro-ready .rr-arena-gateway,
    .rr-pro-ready .rr-live,
    .rr-pro-ready .rr-x1-stage {
        background:
            radial-gradient(circle at top right, rgba(15, 146, 89, 0.12), transparent 28%),
            radial-gradient(circle at top left, rgba(255, 181, 53, 0.11), transparent 24%),
            linear-gradient(180deg, #081018 0%, #0b141d 46%, #09111a 100%) !important;
        color: var(--rr-book-text) !important;
    }

    .rr-pro-ready .rr-arena-gateway {
        padding: 20px !important;
    }

    .rr-pro-ready .rr-arena-gateway__shell {
        width: min(1220px, 100%) !important;
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) !important;
        gap: 16px !important;
    }

    .rr-pro-ready .rr-book-header,
    .rr-pro-ready .rr-book-hero,
    .rr-pro-ready .rr-book-strip,
    .rr-pro-ready .rr-live__mast,
    .rr-pro-ready .rr-live__market-board,
    .rr-pro-ready .rr-live__event-strip {
        border-radius: 16px !important;
        border: 1px solid var(--rr-book-border) !important;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.045), rgba(255, 255, 255, 0.02)) !important;
        box-shadow: var(--rr-book-shadow) !important;
    }

    .rr-pro-ready .rr-book-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        padding: 14px 18px !important;
    }

    .rr-pro-ready .rr-book-header__brand,
    .rr-pro-ready .rr-book-header__utilities,
    .rr-pro-ready .rr-book-strip__chips,
    .rr-pro-ready .rr-live__mast-actions,
    .rr-pro-ready .rr-live__event-strip,
    .rr-pro-ready .rr-arena-card__markets {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand {
        border-radius: 12px !important;
        border: 1px solid var(--rr-book-border) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        padding: 8px 14px 8px 8px !important;
        gap: 12px !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand img {
        width: 46px !important;
        height: 46px !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand strong {
        font-size: 0.95rem !important;
        font-weight: 800 !important;
        color: var(--rr-book-text) !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand span {
        font-size: 0.82rem !important;
        color: var(--rr-book-muted) !important;
        font-weight: 600 !important;
    }

    .rr-pro-ready .status-badge,
    .rr-pro-ready .rr-arena-gateway__kicker,
    .rr-pro-ready .rr-arena-card__tag,
    .rr-pro-ready .rr-pill,
    .rr-pro-ready .rr-card__badge,
    .rr-pro-ready .rr-book-strip__chip {
        min-height: 0 !important;
        border-radius: 999px !important;
        border: 1px solid var(--rr-book-border) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        color: var(--rr-book-muted-2) !important;
        padding: 7px 12px !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__kicker,
    .rr-pro-ready .rr-arena-card--bolao .rr-arena-card__tag,
    .rr-pro-ready .rr-pill,
    .rr-pro-ready .rr-card__badge,
    .rr-pro-ready .rr-book-strip__chip.is-live {
        border-color: rgba(255, 181, 53, 0.24) !important;
        background: rgba(255, 181, 53, 0.1) !important;
        color: #ffd890 !important;
    }

    .rr-pro-ready .rr-book-strip__chip.is-alt,
    .rr-pro-ready .rr-arena-card--x1 .rr-arena-card__tag {
        border-color: rgba(15, 146, 89, 0.26) !important;
        background: rgba(15, 146, 89, 0.12) !important;
        color: #8fe0b4 !important;
    }

    .rr-pro-ready .rr-arena-card--ranking .rr-arena-card__tag {
        border-color: rgba(31, 182, 165, 0.24) !important;
        background: rgba(31, 182, 165, 0.12) !important;
        color: #9dece3 !important;
    }

    .rr-pro-ready .rr-book-hero {
        display: grid !important;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, 0.8fr) !important;
        gap: 18px !important;
        padding: 22px !important;
    }

    .rr-pro-ready .rr-book-hero__intro {
        display: grid !important;
        align-content: start !important;
        gap: 14px !important;
        min-width: 0 !important;
    }

    .rr-pro-ready .rr-arena-gateway__intro::before {
        display: none !important;
    }

    .rr-pro-ready .rr-arena-gateway__title {
        max-width: 12ch !important;
        margin: 0 !important;
        color: #fffaf0 !important;
        font-size: clamp(2.8rem, 5.8vw, 5rem) !important;
        line-height: 0.94 !important;
        letter-spacing: 0 !important;
    }

    .rr-pro-ready .rr-arena-gateway__copy {
        max-width: 46ch !important;
        margin: 0 !important;
        color: var(--rr-book-muted-2) !important;
        font-size: 1rem !important;
        line-height: 1.58 !important;
        font-weight: 600 !important;
    }

    .rr-pro-ready .rr-book-hero__panel {
        display: grid !important;
        gap: 12px !important;
        align-content: start !important;
        padding: 18px !important;
        border-radius: 14px !important;
        border: 1px solid var(--rr-book-border) !important;
        background:
            linear-gradient(180deg, rgba(15, 146, 89, 0.08), rgba(255, 181, 53, 0.03)),
            rgba(255, 255, 255, 0.035) !important;
    }

    .rr-pro-ready .rr-book-hero__panel-title {
        font-size: 0.82rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        color: #d8e1ea !important;
        letter-spacing: 0.08em !important;
    }

    .rr-pro-ready .rr-book-hero__panel-card {
        padding: 14px !important;
        border-radius: 12px !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        background: rgba(11, 20, 29, 0.72) !important;
        display: grid !important;
        gap: 6px !important;
    }

    .rr-pro-ready .rr-book-hero__panel-card small,
    .rr-pro-ready .rr-card__metric span,
    .rr-pro-ready .rr-card__prize-label,
    .rr-pro-ready .rr-stat small,
    .rr-pro-ready .rr-live__featured-meta small {
        color: var(--rr-book-muted) !important;
        font-size: 0.73rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    .rr-pro-ready .rr-book-hero__panel-card strong,
    .rr-pro-ready .rr-card__metric strong,
    .rr-pro-ready .rr-stat strong,
    .rr-pro-ready .rr-live__featured-meta strong {
        color: var(--rr-book-text) !important;
        font-size: 1rem !important;
        font-weight: 800 !important;
        line-height: 1.3 !important;
    }

    .rr-pro-ready .rr-book-strip {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 14px !important;
        padding: 14px 18px !important;
    }

    .rr-pro-ready .rr-book-strip__label {
        color: var(--rr-book-muted) !important;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
    }

    .rr-pro-ready .rr-arena-gateway__choices {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 16px !important;
        perspective: none !important;
    }

    .rr-pro-ready .rr-arena-card {
        min-height: 100% !important;
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) !important;
        align-content: start !important;
        gap: 16px !important;
        padding: 18px !important;
        border-radius: 18px !important;
        border: 1px solid var(--rr-book-border) !important;
        border-top: 3px solid var(--rr-book-gold) !important;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.045), rgba(255, 255, 255, 0.02)),
            var(--rr-book-panel) !important;
        box-shadow: var(--rr-book-shadow) !important;
    }

    .rr-pro-ready .rr-arena-card--x1 {
        border-top-color: var(--rr-book-green) !important;
    }

    .rr-pro-ready .rr-arena-card--ranking {
        border-top-color: var(--rr-book-cyan) !important;
    }

    .rr-pro-ready .rr-arena-card__media {
        width: 62px !important;
        min-height: 62px !important;
        height: 62px !important;
        border-radius: 16px !important;
        border: 1px solid var(--rr-book-border) !important;
        background: rgba(5, 10, 15, 0.7) !important;
    }

    .rr-pro-ready .rr-arena-card__media img {
        width: 40px !important;
        max-height: 40px !important;
    }

    .rr-pro-ready .rr-arena-card__icon {
        width: 42px !important;
        height: 42px !important;
        border-radius: 12px !important;
        font-size: 1.1rem !important;
    }

    .rr-pro-ready .rr-arena-card__body {
        display: grid !important;
        gap: 12px !important;
    }

    .rr-pro-ready .rr-arena-card__meta-row {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    .rr-pro-ready .rr-arena-card__stat {
        color: var(--rr-book-muted) !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
    }

    .rr-pro-ready .rr-arena-card__title {
        margin: 0 !important;
        color: #fffaf0 !important;
        font-size: clamp(1.8rem, 3vw, 2.6rem) !important;
        line-height: 1 !important;
    }

    .rr-pro-ready .rr-arena-card__copy {
        margin: 0 !important;
        color: var(--rr-book-muted-2) !important;
        font-size: 0.95rem !important;
        line-height: 1.55 !important;
        font-weight: 600 !important;
    }

    .rr-pro-ready .rr-arena-card__markets {
        gap: 8px !important;
    }

    .rr-pro-ready .rr-arena-card__markets span {
        border-radius: 999px !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        padding: 7px 10px !important;
        color: var(--rr-book-muted-2) !important;
        font-size: 0.78rem !important;
        font-weight: 700 !important;
    }

    .rr-pro-ready .rr-arena-card__actions {
        min-width: 0 !important;
    }

    .rr-pro-ready .rr-arena-card__button,
    .rr-pro-ready .rr-hero__btn,
    .rr-pro-ready .rr-side__nav-btn,
    .rr-pro-ready .rr-mobile-actions__btn,
    .rr-pro-ready .rr-arena-back,
    .rr-pro-ready .rr-card__btn,
    .rr-pro-ready .rr-mobile-footer__btn {
        min-height: 46px !important;
        border-radius: 12px !important;
        border: 1px solid var(--rr-book-border) !important;
        box-shadow: none !important;
        font-size: 0.95rem !important;
        font-weight: 800 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
    }

    .rr-pro-ready .rr-arena-card__button,
    .rr-pro-ready .rr-hero__btn,
    .rr-pro-ready .rr-card__btn--enter {
        background: linear-gradient(180deg, var(--rr-book-gold), var(--rr-book-gold-deep)) !important;
        color: #1b1203 !important;
        border-color: rgba(255, 181, 53, 0.45) !important;
    }

    .rr-pro-ready .rr-arena-card__button--x1 {
        background: linear-gradient(180deg, var(--rr-book-green), var(--rr-book-green-deep)) !important;
        color: #eefcf5 !important;
        border-color: rgba(15, 146, 89, 0.45) !important;
    }

    .rr-pro-ready .rr-arena-card__button--ranking,
    .rr-pro-ready .rr-card__btn--ranking,
    .rr-pro-ready .rr-card__btn--notify {
        background: linear-gradient(180deg, #178c8d, #126f72) !important;
        color: #eefdfb !important;
        border-color: rgba(31, 182, 165, 0.42) !important;
    }

    .rr-pro-ready .rr-card__btn--locked {
        background: rgba(255, 255, 255, 0.04) !important;
        color: var(--rr-book-muted) !important;
        border-color: var(--rr-book-border) !important;
    }

    .rr-pro-ready .rr-live {
        gap: 14px !important;
        padding: 16px 16px 96px !important;
    }

    .rr-pro-ready .rr-arena-back {
        width: fit-content !important;
        padding-inline: 15px !important;
        background: rgba(255, 255, 255, 0.05) !important;
        color: var(--rr-book-text) !important;
    }

    .rr-pro-ready .rr-live__hero,
    .rr-pro-ready .rr-cards {
        width: min(1220px, 100%) !important;
        margin-inline: auto !important;
    }

    .rr-pro-ready .rr-live__hero {
        padding: 16px !important;
        border-radius: 18px !important;
    }

    .rr-pro-ready .rr-live__mast {
        display: flex !important;
        align-items: end !important;
        justify-content: space-between !important;
        gap: 16px !important;
        padding: 18px !important;
        margin-bottom: 14px !important;
    }

    .rr-pro-ready .rr-live__mast-copy {
        display: grid !important;
        gap: 8px !important;
        max-width: 640px !important;
    }

    .rr-pro-ready .rr-live__mast-copy .rr-side__headline {
        margin: 0 !important;
        color: #fffaf0 !important;
        font-size: clamp(1.8rem, 3.2vw, 3rem) !important;
        line-height: 1.04 !important;
    }

    .rr-pro-ready .rr-live__mast-copy .rr-side__text {
        margin: 0 !important;
        color: var(--rr-book-muted-2) !important;
        font-size: 0.97rem !important;
        line-height: 1.6 !important;
        font-weight: 600 !important;
    }

    .rr-pro-ready .rr-live__mast-actions {
        justify-content: flex-end !important;
    }

    .rr-pro-ready .rr-side__nav-btn,
    .rr-pro-ready .rr-mobile-actions__btn {
        background: rgba(255, 255, 255, 0.045) !important;
        color: var(--rr-book-text) !important;
    }

    .rr-pro-ready .rr-live__market-board {
        display: grid !important;
        gap: 14px !important;
        padding: 18px !important;
    }

    .rr-pro-ready .rr-live__featured {
        display: grid !important;
        gap: 0 !important;
        padding: 0 !important;
        border-radius: 16px !important;
        border: 0 !important;
        background: transparent !important;
    }

    .rr-pro-ready .rr-live__featured-head,
    .rr-pro-ready .rr-countdown,
    .rr-pro-ready .rr-hero__actions,
    .rr-pro-ready .rr-live__event-strip,
    .rr-pro-ready .rr-dots,
    .rr-pro-ready .rr-live__featured-meta,
    .rr-pro-ready .rr-live__featured-side,
    .rr-pro-ready #rrNotifyButton,
    .rr-pro-ready #rrHeroStatEventLabel,
    .rr-pro-ready #rrHeroStatEvent,
    .rr-pro-ready #rrHeroStatModalidadeContainer,
    .rr-pro-ready #rrModalidadeSelectWrapDesktop,
    .rr-pro-ready #rrModalidadeSelectWrapMobile {
        display: none !important;
    }

    .rr-pro-ready .rr-live__featured-head {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        flex-wrap: wrap !important;
    }

    .rr-pro-ready .rr-live__featured-meta {
        display: grid !important;
        gap: 4px !important;
        text-align: right !important;
    }

    .rr-pro-ready .rr-live__featured-main {
        display: grid !important;
        grid-template-columns: auto minmax(0, 1fr) minmax(200px, 0.42fr) !important;
        gap: 16px !important;
        align-items: stretch !important;
    }

    .rr-pro-ready .rr-logo-wrap {
        width: 126px !important;
        min-height: 126px !important;
        padding: 12px !important;
        border-radius: 18px !important;
        border: 1px solid var(--rr-book-border) !important;
        background: rgba(0, 0, 0, 0.26) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
    }

    .rr-pro-ready .rr-logo {
        height: 82px !important;
    }

    .rr-pro-ready .rr-live__featured-copy {
        display: grid !important;
        gap: 8px !important;
        min-width: 0 !important;
        align-content: center !important;
    }

    .rr-pro-ready .rr-hero__actions,
    .rr-pro-ready .rr-side__controls,
    .rr-pro-ready .rr-hero__mobile-controls {
        display: grid !important;
        gap: 12px !important;
    }

    .rr-pro-ready .rr-hero__name {
        width: 100% !important;
        min-height: 0 !important;
        display: block !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        color: #fffaf0 !important;
        font-size: clamp(1.5rem, 3.4vw, 2.5rem) !important;
        line-height: 1.05 !important;
        font-weight: 900 !important;
        text-align: left !important;
    }

    .rr-pro-ready .rr-hero__name.is-sponsor-link {
        cursor: pointer !important;
    }

    .rr-pro-ready .rr-countdown {
        min-height: 0 !important;
        padding: 12px 14px !important;
        border-radius: 14px !important;
        border: 1px solid rgba(255, 181, 53, 0.18) !important;
        background: rgba(255, 181, 53, 0.08) !important;
        display: grid !important;
        gap: 2px !important;
        justify-items: start !important;
    }

    .rr-pro-ready .rr-countdown small {
        color: var(--rr-book-muted) !important;
        font-size: 0.72rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
    }

    .rr-pro-ready .rr-countdown span {
        color: #fffaf0 !important;
        font-size: 1.15rem !important;
        font-weight: 900 !important;
    }

    .rr-pro-ready .rr-live__featured-side {
        display: grid !important;
        gap: 12px !important;
        align-content: start !important;
    }

    .rr-pro-ready #rrHeroDesktopControls {
        display: grid !important;
        align-content: center !important;
    }

    .rr-pro-ready #rrRefreshButtonDesktop {
        width: 100% !important;
    }

    .rr-pro-ready .rr-stat,
    .rr-pro-ready .rr-select-wrap {
        padding: 13px 14px !important;
        border-radius: 14px !important;
        border: 1px solid var(--rr-book-border) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-select {
        background: transparent !important;
        color: var(--rr-book-text) !important;
        font-weight: 700 !important;
    }

    .rr-pro-ready .rr-select option {
        color: #111827 !important;
    }

    .rr-pro-ready .rr-live__event-strip {
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 14px 16px !important;
    }

    .rr-pro-ready .rr-dots {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        min-height: 0 !important;
    }

    .rr-pro-ready .rr-dot {
        width: 8px !important;
        height: 8px !important;
        border-radius: 999px !important;
        background: rgba(255, 255, 255, 0.22) !important;
    }

    .rr-pro-ready .rr-dot.is-active {
        background: var(--rr-book-gold) !important;
        box-shadow: 0 0 0 4px rgba(255, 181, 53, 0.16) !important;
    }

    .rr-pro-ready .rr-mobile-actions {
        width: min(1220px, 100%) !important;
        margin: 0 auto !important;
        gap: 10px !important;
    }

    .rr-pro-ready .rr-cards {
        padding: 14px !important;
        border-radius: 18px !important;
    }

    .rr-pro-ready .rr-cards__grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
        gap: 14px !important;
        width: 100% !important;
        overflow: visible !important;
    }

    .rr-pro-ready .rr-card {
        display: grid !important;
        gap: 14px !important;
        width: 100% !important;
        padding: 14px !important;
        border-radius: 18px !important;
        border: 1px solid var(--rr-book-border) !important;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.035), rgba(255, 255, 255, 0.02)),
            var(--rr-book-panel) !important;
        box-shadow: var(--rr-book-shadow) !important;
    }

    .rr-pro-ready .rr-card__ghost,
    .rr-pro-ready .rr-card__particles,
    .rr-pro-ready .rr-card__particle,
    .rr-pro-ready .rr-cards__particles,
    .rr-pro-ready .rr-sponsor-aura,
    .rr-pro-ready .rr-sponsor-orbit,
    .rr-pro-ready .rr-sponsor-particles {
        display: none !important;
    }

    .rr-pro-ready .rr-card__media-band {
        position: relative !important;
        min-height: 124px !important;
        overflow: hidden !important;
        border-radius: 16px !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        background: #0b131d !important;
    }

    .rr-pro-ready .rr-card__poster {
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        opacity: 0.42 !important;
    }

    .rr-pro-ready .rr-card__scrim {
        position: absolute !important;
        inset: 0 !important;
        background:
            linear-gradient(180deg, rgba(8, 16, 24, 0.12) 0%, rgba(8, 16, 24, 0.82) 84%),
            linear-gradient(90deg, rgba(8, 16, 24, 0.74) 0%, rgba(8, 16, 24, 0.18) 100%) !important;
    }

    .rr-pro-ready .rr-card__top,
    .rr-pro-ready .rr-card__event-row {
        position: relative !important;
        z-index: 1 !important;
    }

    .rr-pro-ready .rr-card__top {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        padding: 12px 12px 0 !important;
    }

    .rr-pro-ready .rr-card__event-row {
        display: grid !important;
        grid-template-columns: 48px minmax(0, 1fr) !important;
        gap: 10px !important;
        align-items: end !important;
        padding: 24px 12px 12px !important;
    }

    .rr-pro-ready .rr-card__event-logo {
        width: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        background: rgba(8, 16, 24, 0.78) !important;
        padding: 5px !important;
        object-fit: contain !important;
    }

    .rr-pro-ready .rr-card__event {
        margin-top: 0 !important;
        text-align: left !important;
        gap: 3px !important;
    }

    .rr-pro-ready .rr-card__event strong {
        display: block !important;
        color: #fffaf0 !important;
        font-size: 1rem !important;
        font-weight: 900 !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .rr-pro-ready .rr-card__event span,
    .rr-pro-ready .rr-card__meta {
        color: var(--rr-book-muted-2) !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
    }

    .rr-pro-ready .rr-meta {
        color: #f3d28f !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
    }

    .rr-pro-ready .rr-card__scoreboard {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 10px !important;
    }

    .rr-pro-ready .rr-card__metric {
        min-width: 0 !important;
        padding: 12px !important;
        border-radius: 14px !important;
        border: 1px solid var(--rr-book-border) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        display: grid !important;
        gap: 6px !important;
    }

    .rr-pro-ready .rr-card__metric strong {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .rr-pro-ready .rr-card__prize-wrap {
        margin-top: 0 !important;
        padding: 14px !important;
        border-radius: 16px !important;
        border: 1px solid rgba(255, 181, 53, 0.18) !important;
        background:
            linear-gradient(180deg, rgba(255, 181, 53, 0.08), rgba(255, 181, 53, 0.03)),
            rgba(255, 255, 255, 0.03) !important;
    }

    .rr-pro-ready .rr-card__prize-frame {
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
    }

    .rr-pro-ready .rr-card__prize-value {
        color: #fffaf0 !important;
        font-size: clamp(1.55rem, 3.8vw, 2.3rem) !important;
        font-weight: 900 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .rr-pro-ready .rr-card__prize-value--text {
        font-size: 1.2rem !important;
        line-height: 1.3 !important;
        white-space: normal !important;
    }

    .rr-pro-ready .rr-card__prize-current {
        margin-top: 6px !important;
        color: var(--rr-book-muted-2) !important;
        font-size: 0.8rem !important;
    }

    .rr-pro-ready .rr-card__meta {
        margin-top: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
    }

    .rr-pro-ready .rr-card__actions {
        margin-top: 0 !important;
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 10px !important;
    }

    .rr-pro-ready .rr-card__btn {
        min-height: 44px !important;
    }

    .rr-pro-ready .rr-mobile-footer {
        margin-top: 2px !important;
    }

    .rr-pro-ready .rr-mobile-footer__actions {
        gap: 10px !important;
    }

    .rr-pro-ready .rr-mobile-footer__btn {
        border-radius: 12px !important;
        background: rgba(255, 255, 255, 0.045) !important;
        color: var(--rr-book-text) !important;
        border-color: var(--rr-book-border) !important;
        text-shadow: none !important;
        box-shadow: none !important;
    }

    .rr-pro-ready .rr-mobile-footer__btn--rules {
        background: linear-gradient(180deg, rgba(255, 181, 53, 0.18), rgba(255, 181, 53, 0.08)) !important;
        color: #ffe3a7 !important;
        border-color: rgba(255, 181, 53, 0.24) !important;
    }

    .rr-pro-ready .rr-mobile-footer__btn--support {
        background: linear-gradient(180deg, rgba(15, 146, 89, 0.18), rgba(15, 146, 89, 0.08)) !important;
        color: #a8f0c9 !important;
        border-color: rgba(15, 146, 89, 0.26) !important;
    }

    @media (max-width: 1100px) {
        .rr-pro-ready .rr-book-hero,
        .rr-pro-ready .rr-live__featured-main {
            grid-template-columns: 1fr !important;
        }

        .rr-pro-ready .rr-arena-gateway__choices {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 767px) {
        .rr-pro-ready .rr-live {
            gap: 8px !important;
            padding: 0 12px 0 !important;
            align-content: start !important;
        }

        .rr-pro-ready .rr-arena-gateway,
        .rr-pro-ready .rr-live,
        .rr-pro-ready .rr-x1-stage {
            padding-inline: 12px !important;
        }

        .rr-pro-ready #rrBolaoFrontend,
        .rr-pro-ready #rrLiveStage {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .rr-pro-ready .rr-live__mast-copy,
        .rr-pro-ready .rr-live__event-strip {
            display: none !important;
        }

        .rr-pro-ready #rrLiveStage > .rr-arena-back {
            display: inline-flex !important;
            width: 100% !important;
            margin: 0 !important;
            margin-top: 0 !important;
            justify-content: flex-start !important;
        }

        .rr-pro-ready .rr-app,
        .rr-pro-ready .rr-main,
        .rr-pro-ready .rr-main > .rr-site-shell {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .rr-pro-ready .rr-book-header,
        .rr-pro-ready .rr-book-hero,
        .rr-pro-ready .rr-book-strip,
        .rr-pro-ready .rr-live__mast,
        .rr-pro-ready .rr-live__market-board,
        .rr-pro-ready .rr-live__event-strip,
        .rr-pro-ready .rr-live__featured,
        .rr-pro-ready .rr-cards,
        .rr-pro-ready .rr-card,
        .rr-pro-ready .rr-arena-card {
            border-radius: 14px !important;
        }

        .rr-pro-ready .rr-book-header {
            padding: 12px !important;
            align-items: start !important;
        }

        .rr-pro-ready .rr-book-header__utilities {
            width: 100% !important;
        }

        .rr-pro-ready .status-badge,
        .rr-pro-ready .rr-book-strip__chip {
            padding: 6px 10px !important;
            font-size: 0.72rem !important;
        }

        .rr-pro-ready .rr-book-hero {
            padding: 16px !important;
            gap: 14px !important;
        }

        .rr-pro-ready .rr-arena-gateway__title {
            max-width: 100% !important;
            font-size: clamp(2.35rem, 11vw, 3.5rem) !important;
        }

        .rr-pro-ready .rr-arena-gateway__copy {
            font-size: 0.92rem !important;
            line-height: 1.5 !important;
        }

        .rr-pro-ready .rr-book-strip {
            align-items: start !important;
            flex-direction: column !important;
            padding: 12px !important;
        }

        .rr-pro-ready .rr-arena-gateway__choices {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }

        .rr-pro-ready .rr-arena-card {
            padding: 14px !important;
            gap: 14px !important;
        }

        .rr-pro-ready .rr-live {
            margin-top: 0 !important;
            padding-bottom: 22px !important;
        }

        .rr-pro-ready .rr-live__hero,
        .rr-pro-ready .rr-cards {
            width: 100% !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .rr-pro-ready .rr-live__hero {
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .rr-pro-ready .rr-live__mast {
            padding: 0 !important;
            align-items: start !important;
            flex-direction: column !important;
            margin-bottom: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            gap: 8px !important;
        }

        .rr-pro-ready .rr-live__mast-actions {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            margin: 0 !important;
        }

        .rr-pro-ready .rr-live__market-board,
        .rr-pro-ready .rr-live__featured {
            padding: 14px !important;
        }

        .rr-pro-ready .rr-live__market-board {
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            gap: 10px !important;
        }

        .rr-pro-ready .rr-live__featured {
            padding: 0 !important;
        }

        .rr-pro-ready .rr-live__featured-head {
            align-items: start !important;
        }

        .rr-pro-ready .rr-live__featured-meta {
            text-align: left !important;
        }

        .rr-pro-ready .rr-live__featured-main {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }

        .rr-pro-ready .rr-logo-wrap {
            width: 96px !important;
            min-height: 96px !important;
        }

        .rr-pro-ready .rr-logo {
            height: 64px !important;
        }

        .rr-pro-ready .rr-hero__name {
            font-size: 1.48rem !important;
        }

        .rr-pro-ready .rr-live__event-strip {
            flex-direction: column !important;
            align-items: stretch !important;
            padding: 10px 12px !important;
        }

        .rr-pro-ready .rr-hero__mobile-controls {
            display: grid !important;
            gap: 8px !important;
        }

        .rr-pro-ready .rr-cards {
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            margin-bottom: 0 !important;
        }

        .rr-pro-ready .rr-cards__grid {
            grid-template-columns: none !important;
            grid-auto-flow: column !important;
            grid-auto-columns: minmax(86vw, 86vw) !important;
            gap: 10px !important;
            overflow-x: auto !important;
            overscroll-behavior-x: contain !important;
            scroll-snap-type: x proximity !important;
            padding: 0 12px 6px 0 !important;
            scrollbar-width: none !important;
        }

        .rr-pro-ready .rr-cards__grid::-webkit-scrollbar {
            display: none !important;
        }

        .rr-pro-ready .rr-card {
            scroll-snap-align: start !important;
        }

        .rr-pro-ready .rr-card__media-band {
            min-height: 112px !important;
        }

        .rr-pro-ready .rr-card__scoreboard {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .rr-pro-ready .rr-card__metric:last-child {
            grid-column: 1 / -1 !important;
        }

        .rr-pro-ready .rr-card__meta,
        .rr-pro-ready .rr-card__actions {
            grid-template-columns: 1fr !important;
        }

        .rr-pro-ready .rr-mobile-footer__btn {
            min-height: 46px !important;
            font-size: 0.92rem !important;
        }

        .rr-pro-ready .rr-arena-back {
            margin: 0 !important;
        }

        .rr-pro-ready .rr-mobile-actions {
            margin: 0 !important;
            gap: 10px !important;
        }

        .rr-pro-ready .rr-mobile-footer {
            margin-top: 0 !important;
            padding-top: 0 !important;
            padding-bottom: calc(12px + env(safe-area-inset-bottom)) !important;
        }

        .rr-pro-ready .rr-mobile-footer__actions {
            gap: 10px !important;
        }

        .rr-pro-ready .rr-mobile-refresh-fab {
            display: none !important;
        }
    }

    .rr-pro-ready .rr-arena-gateway--entry {
        position: relative !important;
        display: grid !important;
        align-items: center !important;
        min-height: 100dvh !important;
        padding: 0 !important;
        background:
            linear-gradient(125deg, rgba(255, 181, 53, 0.12), transparent 32%),
            linear-gradient(235deg, rgba(31, 182, 165, 0.12), transparent 36%),
            linear-gradient(180deg, #061018 0%, #08131d 52%, #050d15 100%) !important;
        isolation: isolate !important;
        overflow: hidden !important;
    }

    .rr-pro-ready .rr-arena-gateway--entry::before {
        content: "" !important;
        position: absolute !important;
        inset: 0 !important;
        background:
            repeating-linear-gradient(
                90deg,
                rgba(255, 255, 255, 0.035) 0 1px,
                transparent 1px 88px
            ) !important;
        opacity: 0.14 !important;
        mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.9) 0%, transparent 100%) !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-arena-gateway--entry::after {
        content: "" !important;
        position: absolute !important;
        inset: -18% -42% !important;
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(255, 181, 53, 0.1) 28%,
            rgba(34, 197, 94, 0.1) 50%,
            rgba(31, 182, 165, 0.08) 72%,
            transparent 100%
        ) !important;
        transform: translate3d(-16%, 0, 0) skewX(-18deg) !important;
        opacity: 0.82 !important;
        animation: rr-entry-sweep 18s linear infinite !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-entry-shell {
        position: relative !important;
        z-index: 1 !important;
        width: min(760px, 100vw) !important;
        min-height: 100dvh !important;
        margin: 0 auto !important;
        display: grid !important;
        align-content: center !important;
        justify-items: center !important;
        gap: clamp(18px, 3vw, 28px) !important;
        padding: clamp(26px, 4vw, 42px) clamp(16px, 3vw, 30px) !important;
        border-radius: 0 !important;
        border-inline: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-block: 0 !important;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02)),
            rgba(9, 16, 24, 0.92) !important;
        box-shadow: 0 28px 64px rgba(0, 0, 0, 0.28) !important;
        overflow: hidden !important;
    }

    .rr-pro-ready .rr-entry-shell::before {
        content: "" !important;
        position: absolute !important;
        left: -10% !important;
        right: -10% !important;
        bottom: 24% !important;
        height: 1px !important;
        background: linear-gradient(
            90deg,
            transparent 0%,
            rgba(255, 181, 53, 0.34) 24%,
            rgba(34, 197, 94, 0.34) 50%,
            rgba(31, 182, 165, 0.34) 76%,
            transparent 100%
        ) !important;
        opacity: 0.72 !important;
        animation: rr-entry-line 9s ease-in-out infinite !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-entry-shell::after {
        content: "" !important;
        position: absolute !important;
        inset: 16px 14px !important;
        border-radius: 18px !important;
        border: 1px solid rgba(255, 255, 255, 0.04) !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-entry-brand {
        position: relative !important;
        z-index: 1 !important;
        display: grid !important;
        justify-items: center !important;
        gap: 18px !important;
    }

    .rr-pro-ready .rr-entry-logo-frame {
        position: relative !important;
        width: clamp(200px, 30vw, 272px) !important;
        aspect-ratio: 1 !important;
        overflow: hidden !important;
        display: grid !important;
        place-items: center !important;
        border-radius: 28px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: linear-gradient(180deg, rgba(7, 12, 18, 0.98), rgba(10, 16, 22, 0.94)) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.08),
            0 18px 42px rgba(0, 0, 0, 0.34) !important;
        animation: rr-entry-float 6.2s ease-in-out infinite !important;
    }

    .rr-pro-ready .rr-entry-logo-frame::before {
        content: "" !important;
        position: absolute !important;
        inset: -18% !important;
        z-index: 0 !important;
        border-radius: 42px !important;
        background:
            radial-gradient(circle at 28% 26%, rgba(255, 181, 53, 0.28), transparent 36%),
            radial-gradient(circle at 74% 34%, rgba(34, 197, 94, 0.16), transparent 34%),
            radial-gradient(circle at 64% 78%, rgba(31, 182, 165, 0.22), transparent 38%) !important;
        filter: blur(18px) !important;
        opacity: 0.82 !important;
        animation: rr-entry-logo-aura 7.2s ease-in-out infinite !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-entry-logo-frame::after {
        content: "" !important;
        position: absolute !important;
        inset: 0 !important;
        border-radius: 28px !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.12), transparent 24%, transparent 72%, rgba(255, 255, 255, 0.06)),
            linear-gradient(180deg, rgba(255, 255, 255, 0.03), transparent 32%) !important;
        mix-blend-mode: screen !important;
        opacity: 0.92 !important;
        animation: rr-entry-logo-gloss 6.4s ease-in-out infinite !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-entry-logo {
        position: relative !important;
        z-index: 1 !important;
        width: 84% !important;
        max-width: 100% !important;
        height: auto !important;
        object-fit: contain !important;
        filter: drop-shadow(0 16px 28px rgba(255, 166, 24, 0.22)) !important;
    }

    .rr-pro-ready .rr-entry-title {
        margin: 0 !important;
        display: grid !important;
        gap: 8px !important;
        justify-items: center !important;
        color: #fff8ef !important;
        text-align: center !important;
        text-transform: uppercase !important;
        font-family: "Ethnocentric", "Inter", system-ui, sans-serif !important;
        font-size: clamp(1.9rem, 7vw, 4rem) !important;
        line-height: 1.05 !important;
        letter-spacing: 0 !important;
        text-shadow: 0 10px 30px rgba(0, 0, 0, 0.32) !important;
    }

    .rr-pro-ready .rr-entry-title span {
        display: block !important;
    }

    .rr-pro-ready .rr-entry-segments {
        position: relative !important;
        z-index: 1 !important;
        width: min(760px, 100%) !important;
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 12px !important;
    }

    .rr-pro-ready .rr-entry-segments--single {
        width: min(320px, 100%) !important;
        grid-template-columns: minmax(0, 1fr) !important;
    }

    .rr-pro-ready .rr-entry-card {
        --rr-entry-accent: #ffb535;
        --rr-entry-surface: rgba(255, 181, 53, 0.12);
        --rr-entry-border: rgba(255, 181, 53, 0.2);
        position: relative !important;
        isolation: isolate !important;
        overflow: hidden !important;
        display: grid !important;
        justify-items: center !important;
        align-content: center !important;
        gap: 12px !important;
        min-height: clamp(122px, 16vw, 154px) !important;
        padding: 18px 10px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 22px !important;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02)),
            rgba(11, 20, 29, 0.96) !important;
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.24) !important;
        cursor: pointer !important;
        transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease !important;
    }

    .rr-pro-ready .rr-entry-card::before {
        content: "" !important;
        position: absolute !important;
        inset: -160% 28% 42% -40% !important;
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.1) 50%, transparent 100%) !important;
        transform: rotate(18deg) !important;
        opacity: 0.7 !important;
        animation: rr-entry-sheen 11s linear infinite !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-entry-card::after {
        content: "" !important;
        position: absolute !important;
        left: 14px !important;
        right: 14px !important;
        bottom: 14px !important;
        height: 3px !important;
        border-radius: 999px !important;
        background: linear-gradient(90deg, var(--rr-entry-accent), rgba(255, 255, 255, 0)) !important;
        opacity: 0.92 !important;
        pointer-events: none !important;
    }

    .rr-pro-ready .rr-entry-card:nth-child(2)::before {
        animation-delay: -3.6s !important;
    }

    .rr-pro-ready .rr-entry-card:nth-child(3)::before {
        animation-delay: -7.2s !important;
    }

    .rr-pro-ready .rr-entry-card:hover,
    .rr-pro-ready .rr-entry-card:focus-visible {
        transform: translateY(-4px) !important;
        border-color: var(--rr-entry-border) !important;
        box-shadow:
            0 22px 44px rgba(0, 0, 0, 0.3),
            0 0 0 1px var(--rr-entry-border) !important;
        outline: 0 !important;
    }

    .rr-pro-ready .rr-entry-card--bolao {
        --rr-entry-accent: #ffb535;
        --rr-entry-surface: rgba(255, 181, 53, 0.12);
        --rr-entry-border: rgba(255, 181, 53, 0.28);
    }

    .rr-pro-ready .rr-entry-card--x1 {
        --rr-entry-accent: #22c55e;
        --rr-entry-surface: rgba(34, 197, 94, 0.12);
        --rr-entry-border: rgba(34, 197, 94, 0.26);
    }

    .rr-pro-ready .rr-entry-card--ranking,
    .rr-pro-ready .rr-entry-card--stats {
        --rr-entry-accent: #1fb6a5;
        --rr-entry-surface: rgba(31, 182, 165, 0.14);
        --rr-entry-border: rgba(31, 182, 165, 0.26);
    }

    .rr-pro-ready .rr-entry-card__icon {
        position: relative !important;
        z-index: 1 !important;
        width: 58px !important;
        height: 58px !important;
        display: grid !important;
        place-items: center !important;
        border-radius: 18px !important;
        border: 1px solid var(--rr-entry-border) !important;
        background:
            linear-gradient(180deg, var(--rr-entry-surface), rgba(255, 255, 255, 0.02)),
            rgba(8, 14, 20, 0.96) !important;
        color: var(--rr-entry-accent) !important;
        font-size: 1.35rem !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
        animation: rr-entry-icon 5.6s ease-in-out infinite !important;
    }

    .rr-pro-ready .rr-entry-card--x1 .rr-entry-card__icon {
        animation-delay: -1.8s !important;
    }

    .rr-pro-ready .rr-entry-card--ranking .rr-entry-card__icon,
    .rr-pro-ready .rr-entry-card--stats .rr-entry-card__icon {
        animation-delay: -3.4s !important;
    }

    .rr-pro-ready .rr-entry-card__label {
        position: relative !important;
        z-index: 1 !important;
        color: #f8fafc !important;
        font-size: clamp(0.94rem, 2vw, 1.08rem) !important;
        font-weight: 800 !important;
        line-height: 1.1 !important;
        white-space: nowrap !important;
        letter-spacing: 0 !important;
    }

    .rr-pro-ready .rr-entry-card--stats .rr-entry-card__label--stacked {
        display: grid !important;
        gap: 0.08rem !important;
        width: 100% !important;
        justify-items: center !important;
        text-align: center !important;
        white-space: normal !important;
        font-size: clamp(0.78rem, 1.9vw, 0.94rem) !important;
        line-height: 1.02 !important;
        text-wrap: balance !important;
    }

    .rr-pro-ready .rr-entry-card--stats .rr-entry-card__label--stacked > span {
        display: block !important;
    }

    .rr-pro-ready .rr-entry-prompt {
        position: relative !important;
        z-index: 1 !important;
        margin: 0 !important;
        color: #d7e0ea !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        text-align: center !important;
        letter-spacing: 0 !important;
    }

    @media (min-width: 768px) {
        .rr-pro-ready .rr-arena-gateway--entry {
            min-height: 100vh !important;
            padding: 0 !important;
        }

        .rr-pro-ready .rr-entry-shell {
            width: min(1140px, calc(100vw - 80px)) !important;
            min-height: 100vh !important;
            align-content: center !important;
            gap: 14px !important;
            padding: 12px 36px 10px !important;
        }

        .rr-pro-ready .rr-entry-brand {
            gap: 8px !important;
        }

        .rr-pro-ready .rr-entry-logo-frame {
            width: clamp(132px, 11vw, 176px) !important;
            border-radius: 22px !important;
        }

        .rr-pro-ready .rr-entry-logo-frame::after {
            border-radius: 22px !important;
        }

        .rr-pro-ready .rr-entry-title {
            gap: 2px !important;
            font-size: clamp(1.55rem, 3.8vw, 2.4rem) !important;
            line-height: 0.92 !important;
        }

        .rr-pro-ready .rr-entry-segments {
            width: min(860px, 100%) !important;
            gap: 14px !important;
        }

        .rr-pro-ready .rr-entry-segments--single {
            width: min(360px, 100%) !important;
        }

        .rr-pro-ready .rr-entry-card {
            min-height: 102px !important;
            padding: 12px 12px !important;
            gap: 8px !important;
        }

        .rr-pro-ready .rr-entry-card__icon {
            width: 44px !important;
            height: 44px !important;
            border-radius: 14px !important;
            font-size: 1rem !important;
        }

        .rr-pro-ready .rr-entry-card__label {
            font-size: 0.92rem !important;
        }

        .rr-pro-ready .rr-entry-card--stats .rr-entry-card__label--stacked {
            display: block !important;
            white-space: nowrap !important;
            font-size: 0.82rem !important;
        }

        .rr-pro-ready .rr-entry-card--stats .rr-entry-card__label--stacked > span {
            display: inline !important;
        }

        .rr-pro-ready .rr-entry-card--stats .rr-entry-card__label--stacked > span + span::before {
            content: " " !important;
        }

        .rr-pro-ready .rr-entry-prompt {
            font-size: 0.94rem !important;
        }
    }

    @media (min-width: 1200px) {
        .rr-pro-ready .rr-entry-shell {
            width: min(1240px, calc(100vw - 96px)) !important;
            padding: 8px 44px 8px !important;
        }

        .rr-pro-ready .rr-entry-logo-frame {
            width: 156px !important;
        }

        .rr-pro-ready .rr-entry-title {
            font-size: 2.15rem !important;
        }

        .rr-pro-ready .rr-entry-card {
            min-height: 96px !important;
        }
    }

    @keyframes rr-entry-sweep {
        0% {
            transform: translate3d(-18%, 0, 0) skewX(-18deg);
        }

        100% {
            transform: translate3d(18%, 0, 0) skewX(-18deg);
        }
    }

    @keyframes rr-entry-line {
        0%,
        100% {
            transform: translateX(-4%);
            opacity: 0.46;
        }

        50% {
            transform: translateX(4%);
            opacity: 0.88;
        }
    }

    @keyframes rr-entry-logo-aura {
        0%,
        100% {
            transform: scale(0.98) translate3d(-1.5%, -1%, 0);
            opacity: 0.72;
        }

        50% {
            transform: scale(1.04) translate3d(1.5%, 1%, 0);
            opacity: 0.94;
        }
    }

    @keyframes rr-entry-logo-gloss {
        0%,
        100% {
            opacity: 0.78;
            transform: translate3d(0, 0, 0);
        }

        50% {
            opacity: 1;
            transform: translate3d(0, -1.5%, 0);
        }
    }

    @keyframes rr-entry-float {
        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    @keyframes rr-entry-icon {
        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-3px);
        }
    }

    @keyframes rr-entry-sheen {
        0% {
            transform: translateX(-180%) rotate(18deg);
        }

        100% {
            transform: translateX(240%) rotate(18deg);
        }
    }

    @media (max-width: 767px) {
        .rr-pro-ready .rr-arena-gateway--entry {
            min-height: 100dvh !important;
            padding: 0 !important;
        }

        .rr-pro-ready .rr-entry-shell {
            width: 100vw !important;
            min-height: 100dvh !important;
            padding: 18px 14px 14px !important;
            gap: 18px !important;
            border-radius: 0 !important;
            border-inline: 0 !important;
        }

        .rr-pro-ready .rr-entry-shell::after {
            inset: 12px 10px !important;
            border-radius: 18px !important;
        }

        .rr-pro-ready .rr-entry-logo-frame {
            width: 190px !important;
            border-radius: 22px !important;
        }

        .rr-pro-ready .rr-entry-logo-frame::after {
            border-radius: 22px !important;
        }

        .rr-pro-ready .rr-entry-title {
            gap: 6px !important;
            font-size: clamp(1.42rem, 8vw, 2.2rem) !important;
        }

        .rr-pro-ready .rr-entry-segments {
            gap: 9px !important;
        }

        .rr-pro-ready .rr-entry-segments--single {
            width: min(280px, 100%) !important;
        }

        .rr-pro-ready .rr-entry-card {
            min-height: 110px !important;
            padding: 14px 8px !important;
            border-radius: 18px !important;
            gap: 10px !important;
        }

        .rr-pro-ready .rr-entry-card__icon {
            width: 46px !important;
            height: 46px !important;
            border-radius: 14px !important;
            font-size: 1.08rem !important;
        }

        .rr-pro-ready .rr-entry-card__label {
            font-size: 0.86rem !important;
        }

        .rr-pro-ready .rr-entry-prompt {
            font-size: 0.88rem !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rr-pro-ready .rr-arena-gateway--entry::after,
        .rr-pro-ready .rr-entry-shell::before,
        .rr-pro-ready .rr-entry-logo-frame,
        .rr-pro-ready .rr-entry-logo-frame::before,
        .rr-pro-ready .rr-entry-card::before,
        .rr-pro-ready .rr-entry-card__icon {
            animation: none !important;
        }
    }

    #rrArenaGateway.rr-hidden,
    #rrLiveStage.rr-hidden,
    #rrX1Stage.rr-hidden,
    #rrStatsStage.rr-hidden {
        display: none !important;
    }
