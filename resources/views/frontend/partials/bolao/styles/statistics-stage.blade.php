    .rr-stats-stage {
        min-height: 100dvh !important;
        padding: 20px 16px 24px !important;
        background:
            radial-gradient(circle at top right, rgba(31, 182, 165, 0.16), transparent 28%),
            radial-gradient(circle at bottom left, rgba(255, 181, 53, 0.12), transparent 32%),
            linear-gradient(180deg, #06111a 0%, #040814 100%) !important;
    }

    .rr-stats-stage__shell {
        width: min(1180px, 100%) !important;
        margin: 0 auto !important;
        display: grid !important;
        gap: 16px !important;
    }

    .rr-stats-stage__hero,
    .rr-stats-stage__summary,
    .rr-stats-stage__board {
        border-radius: 24px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background:
            linear-gradient(180deg, rgba(13, 20, 34, 0.98), rgba(7, 12, 25, 0.98)),
            rgba(7, 12, 25, 0.98) !important;
        box-shadow: 0 24px 54px rgba(2, 6, 23, 0.32) !important;
    }

    .rr-stats-stage__hero {
        display: grid !important;
        gap: 16px !important;
        padding: 18px !important;
    }

    .rr-stats-stage__hero-top,
    .rr-stats-stage__summary-head,
    .rr-stats-stage__board-head {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 16px !important;
    }

    .rr-stats-stage__tag {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        min-height: 38px !important;
        padding: 0 14px !important;
        border-radius: 999px !important;
        border: 1px solid rgba(31, 182, 165, 0.28) !important;
        background: rgba(31, 182, 165, 0.12) !important;
        color: #8df3e3 !important;
        font-size: 0.76rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
    }

    .rr-stats-stage__title,
    .rr-stats-stage__summary-head h3,
    .rr-stats-stage__board-head h3 {
        margin: 0 !important;
        color: #f8fafc !important;
        font-size: clamp(1.5rem, 3vw, 2.35rem) !important;
        line-height: 1.04 !important;
        font-weight: 900 !important;
        letter-spacing: 0 !important;
    }

    .rr-stats-stage__copy,
    .rr-stats-stage__board-head p,
    .rr-stats-stage__status small,
    .rr-stats-stage__summary-head small {
        margin: 0 !important;
        color: rgba(226, 232, 240, 0.72) !important;
        line-height: 1.55 !important;
        font-weight: 600 !important;
    }

    .rr-stats-stage__status {
        min-width: 220px !important;
        padding: 14px 16px !important;
        border-radius: 20px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        display: grid !important;
        gap: 4px !important;
    }

    .rr-stats-stage__status strong {
        color: #fff7ed !important;
        font-size: 1rem !important;
        line-height: 1.15 !important;
        font-weight: 900 !important;
    }

    .rr-stats-stage__status span {
        color: rgba(226, 232, 240, 0.72) !important;
        font-weight: 600 !important;
    }

    .rr-stats-stage__filters {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 12px !important;
    }

    .rr-stats-stage__field {
        display: grid !important;
        gap: 8px !important;
    }

    .rr-stats-stage__field span {
        color: rgba(226, 232, 240, 0.76) !important;
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.04em !important;
        text-transform: uppercase !important;
    }

    .rr-stats-stage__field select,
    .rr-stats-stage__refresh {
        width: 100% !important;
        min-height: 52px !important;
        border-radius: 18px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(8, 14, 24, 0.96) !important;
        color: #f8fafc !important;
        font-weight: 800 !important;
        padding: 0 16px !important;
    }

    .rr-stats-stage__refresh {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        align-self: end !important;
        background: linear-gradient(135deg, #ffb535, #f59e0b) !important;
        color: #111827 !important;
        border-color: rgba(255, 181, 53, 0.28) !important;
        box-shadow: 0 18px 30px rgba(245, 158, 11, 0.2) !important;
    }

    .rr-stats-stage__divisions {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
    }

    .rr-stats-stage__division-chip {
        min-height: 38px !important;
        padding: 0 14px !important;
        border-radius: 999px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        color: rgba(226, 232, 240, 0.78) !important;
        font-weight: 800 !important;
    }

    .rr-stats-stage__division-chip.is-active {
        border-color: rgba(31, 182, 165, 0.28) !important;
        background: rgba(31, 182, 165, 0.14) !important;
        color: #d1fae5 !important;
        box-shadow: 0 0 0 1px rgba(31, 182, 165, 0.2) !important;
    }

    .rr-stats-stage__body {
        display: grid !important;
        grid-template-columns: minmax(290px, 360px) minmax(0, 1fr) !important;
        gap: 16px !important;
    }

    .rr-stats-stage__summary,
    .rr-stats-stage__board {
        padding: 18px !important;
        display: grid !important;
        gap: 16px !important;
    }

    .rr-stats-stage__scope-logo-wrap {
        width: 84px !important;
        height: 84px !important;
        border-radius: 22px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(5, 10, 22, 0.9) !important;
        display: grid !important;
        place-items: center !important;
        overflow: hidden !important;
    }

    .rr-stats-stage__scope-logo-wrap img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .rr-stats-stage__summary-grid {
        display: grid !important;
        gap: 12px !important;
    }

    .rr-stats-stage__metric {
        border-radius: 18px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        padding: 14px 16px !important;
        display: grid !important;
        gap: 4px !important;
    }

    .rr-stats-stage__metric small {
        color: rgba(148, 163, 184, 0.88) !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    .rr-stats-stage__metric strong {
        color: #fff7ed !important;
        font-size: 1.4rem !important;
        line-height: 1.05 !important;
        font-weight: 900 !important;
    }

    .rr-stats-stage__board-cta {
        min-height: 46px !important;
        padding: 0 16px !important;
        border-radius: 16px !important;
        border: 1px solid rgba(31, 182, 165, 0.2) !important;
        background: rgba(31, 182, 165, 0.14) !important;
        color: #d1fae5 !important;
        font-weight: 900 !important;
        white-space: nowrap !important;
    }

    .rr-stats-stage__plans,
    .rr-stats-stage__leaderboard {
        display: grid !important;
        gap: 12px !important;
    }

    .rr-stats-stage__plans {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }

    .rr-stats-stage__plan,
    .rr-stats-stage__row,
    .rr-stats-stage__locked {
        border-radius: 20px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(255, 255, 255, 0.04) !important;
    }

    .rr-stats-stage__plan {
        padding: 16px !important;
        display: grid !important;
        gap: 12px !important;
    }

    .rr-stats-stage__plan.is-featured {
        border-color: rgba(255, 181, 53, 0.24) !important;
        box-shadow: 0 18px 38px rgba(245, 158, 11, 0.12) !important;
    }

    .rr-stats-stage__plan-badge {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        min-height: 30px !important;
        padding: 0 10px !important;
        border-radius: 999px !important;
        font-size: 0.7rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: #081019 !important;
        justify-self: start !important;
    }

    .rr-stats-stage__plan h4,
    .rr-stats-stage__row-name {
        margin: 0 !important;
        color: #f8fafc !important;
        font-weight: 900 !important;
        line-height: 1.08 !important;
    }

    .rr-stats-stage__plan-price {
        display: flex !important;
        align-items: baseline !important;
        gap: 8px !important;
        flex-wrap: wrap !important;
    }

    .rr-stats-stage__plan-price strong {
        color: #fff7ed !important;
        font-size: 1.55rem !important;
        font-weight: 900 !important;
    }

    .rr-stats-stage__plan-price span,
    .rr-stats-stage__plan-copy,
    .rr-stats-stage__plan-list,
    .rr-stats-stage__row-meta,
    .rr-stats-stage__row-updated {
        color: rgba(226, 232, 240, 0.72) !important;
        font-weight: 600 !important;
    }

    .rr-stats-stage__plan-list {
        margin: 0 !important;
        padding-left: 18px !important;
        display: grid !important;
        gap: 6px !important;
    }

    .rr-stats-stage__plan-button {
        min-height: 44px !important;
        border-radius: 14px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: linear-gradient(135deg, rgba(31, 182, 165, 0.22), rgba(31, 182, 165, 0.1)) !important;
        color: #d1fae5 !important;
        font-weight: 900 !important;
    }

    .rr-stats-stage__locked {
        padding: 24px !important;
        display: grid !important;
        gap: 10px !important;
        text-align: left !important;
    }

    .rr-stats-stage__locked i {
        color: #ffb535 !important;
        font-size: 1.3rem !important;
    }

    .rr-stats-stage__locked strong {
        color: #fff7ed !important;
        font-size: 1.2rem !important;
        line-height: 1.1 !important;
    }

    .rr-stats-stage__locked span {
        color: rgba(226, 232, 240, 0.72) !important;
        font-weight: 600 !important;
    }

    .rr-stats-stage__row {
        padding: 14px 16px !important;
        display: grid !important;
        grid-template-columns: auto minmax(0, 1fr) auto !important;
        align-items: center !important;
        gap: 14px !important;
    }

    .rr-stats-stage__row-rank {
        width: 40px !important;
        height: 40px !important;
        border-radius: 14px !important;
        display: grid !important;
        place-items: center !important;
        background: rgba(255, 181, 53, 0.16) !important;
        color: #ffd79b !important;
        font-weight: 900 !important;
    }

    .rr-stats-stage__row-main {
        display: grid !important;
        grid-template-columns: 52px minmax(0, 1fr) !important;
        gap: 12px !important;
        align-items: center !important;
        min-width: 0 !important;
    }

    .rr-stats-stage__row-avatar {
        width: 52px !important;
        height: 52px !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(5, 10, 22, 0.92) !important;
    }

    .rr-stats-stage__row-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .rr-stats-stage__row-copy {
        min-width: 0 !important;
        display: grid !important;
        gap: 6px !important;
    }

    .rr-stats-stage__row-name,
    .rr-stats-stage__row-meta {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .rr-stats-stage__row-tags {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }

    .rr-stats-stage__row-tag {
        min-height: 28px !important;
        padding: 0 10px !important;
        border-radius: 999px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        color: rgba(226, 232, 240, 0.82) !important;
        font-size: 0.72rem !important;
        font-weight: 800 !important;
    }

    .rr-stats-stage__row-score {
        display: grid !important;
        gap: 6px !important;
        justify-items: end !important;
        text-align: right !important;
    }

    .rr-stats-stage__row-score strong {
        color: #fff7ed !important;
        font-size: 1.22rem !important;
        font-weight: 900 !important;
        line-height: 1 !important;
    }

    .rr-stats-stage__row-score span {
        color: rgba(148, 163, 184, 0.88) !important;
        font-size: 0.76rem !important;
        font-weight: 800 !important;
    }

    .rr-stats-stage__empty {
        border-radius: 20px !important;
        border: 1px dashed rgba(255, 255, 255, 0.12) !important;
        padding: 24px !important;
        text-align: center !important;
        color: rgba(226, 232, 240, 0.72) !important;
        font-weight: 700 !important;
    }

    @media (max-width: 991px) {
        .rr-stats-stage__body {
            grid-template-columns: 1fr !important;
        }

        .rr-stats-stage__plans {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 767px) {
        .rr-stats-stage {
            padding: 12px !important;
        }

        .rr-stats-stage__hero,
        .rr-stats-stage__summary,
        .rr-stats-stage__board {
            border-radius: 20px !important;
            padding: 14px !important;
        }

        .rr-stats-stage__hero-top,
        .rr-stats-stage__summary-head,
        .rr-stats-stage__board-head {
            flex-direction: column !important;
        }

        .rr-stats-stage__status {
            min-width: 0 !important;
            width: 100% !important;
        }

        .rr-stats-stage__filters {
            grid-template-columns: 1fr !important;
        }

        .rr-stats-stage__row {
            grid-template-columns: 1fr !important;
            justify-items: stretch !important;
        }

        .rr-stats-stage__row-rank {
            width: 36px !important;
            height: 36px !important;
        }

        .rr-stats-stage__row-main {
            grid-template-columns: 46px minmax(0, 1fr) !important;
        }

        .rr-stats-stage__row-avatar {
            width: 46px !important;
            height: 46px !important;
        }

        .rr-stats-stage__row-score {
            justify-items: start !important;
            text-align: left !important;
        }
    }
