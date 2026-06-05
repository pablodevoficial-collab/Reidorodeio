    /* Premium arena direction */
    .rr-pro-ready .rr-arena-gateway {
        min-height: 100svh !important;
        align-items: stretch !important;
        padding: clamp(18px, 3vw, 42px) !important;
        background:
            linear-gradient(90deg, rgba(3, 7, 18, .96) 0%, rgba(5, 11, 20, .86) 42%, rgba(7, 14, 18, .52) 100%),
            linear-gradient(180deg, rgba(21, 12, 5, .36), rgba(2, 6, 23, .9)),
            url("{{ asset('assets/admin/images/login.jpg') }}") center/cover no-repeat !important;
    }

    .rr-pro-ready .rr-arena-gateway::before {
        display: block !important;
        z-index: -1 !important;
        opacity: .64 !important;
        background:
            linear-gradient(115deg, transparent 0 48%, rgba(214, 151, 54, .2) 49%, transparent 52%),
            repeating-linear-gradient(90deg, rgba(255,255,255,.055) 0 1px, transparent 1px 120px),
            repeating-linear-gradient(0deg, rgba(255,255,255,.035) 0 1px, transparent 1px 120px) !important;
        mask-image: linear-gradient(180deg, transparent, #000 12%, #000 82%, transparent) !important;
    }

    .rr-pro-ready .rr-arena-gateway::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            radial-gradient(circle at 78% 24%, rgba(214, 151, 54, .2), transparent 24%),
            radial-gradient(circle at 34% 88%, rgba(20, 83, 45, .34), transparent 30%),
            linear-gradient(180deg, transparent 0%, rgba(2, 6, 23, .72) 100%);
        pointer-events: none;
    }

    .rr-pro-ready .rr-arena-gateway__shell {
        width: min(1380px, 100%) !important;
        min-height: calc(100svh - clamp(36px, 6vw, 84px)) !important;
        grid-template-columns: minmax(330px, .82fr) minmax(0, 1.18fr) !important;
        gap: clamp(22px, 4vw, 64px) !important;
        align-items: center !important;
    }

    .rr-pro-ready .rr-arena-gateway__intro {
        position: relative !important;
        gap: clamp(16px, 2.2vw, 26px) !important;
        padding: clamp(18px, 3vw, 42px) 0 !important;
    }

    .rr-pro-ready .rr-arena-gateway__intro::before {
        content: "";
        width: 72px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #d69736, #14532d);
        box-shadow: 0 0 28px rgba(214, 151, 54, .28);
    }

    .rr-pro-ready .rr-arena-gateway__brand {
        width: fit-content !important;
        padding: 10px 14px 10px 10px !important;
        border: 1px solid rgba(255,255,255,.11) !important;
        border-radius: 999px !important;
        background: rgba(5, 10, 23, .68) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.08) !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand img {
        width: 48px !important;
        height: 48px !important;
        filter: drop-shadow(0 12px 24px rgba(214, 151, 54, .22)) !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand strong {
        color: #fff8e7 !important;
        font-size: .92rem !important;
        letter-spacing: .08em !important;
    }

    .rr-pro-ready .rr-arena-gateway__brand span {
        color: rgba(226,232,240,.68) !important;
        font-size: .8rem !important;
        font-weight: 800 !important;
    }

    .rr-pro-ready .rr-arena-gateway__kicker {
        width: fit-content !important;
        min-height: 34px !important;
        padding: 0 13px !important;
        border-color: rgba(214,151,54,.38) !important;
        background: rgba(214,151,54,.12) !important;
        color: #f6d58b !important;
    }

    .rr-pro-ready .rr-arena-gateway__title {
        max-width: 8ch !important;
        color: #fff8e7 !important;
        background: none !important;
        -webkit-text-fill-color: currentColor !important;
        font-size: clamp(4rem, 8.6vw, 8.6rem) !important;
        line-height: .82 !important;
        letter-spacing: -.055em !important;
        text-shadow: 0 18px 56px rgba(0,0,0,.42) !important;
    }

    .rr-pro-ready .rr-arena-gateway__copy {
        max-width: 560px !important;
        color: rgba(255,248,231,.82) !important;
        font-size: clamp(1rem, 1.45vw, 1.18rem) !important;
        line-height: 1.65 !important;
        font-weight: 750 !important;
    }

    .rr-pro-ready .rr-arena-gateway__status {
        gap: 9px !important;
    }

    .rr-pro-ready .status-badge {
        min-height: 40px !important;
        padding: 0 14px !important;
        border-radius: 999px !important;
        border-color: rgba(255,255,255,.12) !important;
        background: rgba(5, 10, 23, .62) !important;
        color: rgba(255,248,231,.88) !important;
    }

    .rr-pro-ready .status-badge i {
        color: #d69736 !important;
    }

    .rr-pro-ready .rr-arena-gateway__choices {
        align-items: stretch !important;
        gap: clamp(14px, 2vw, 22px) !important;
    }

    .rr-pro-ready .rr-arena-card {
        min-height: clamp(480px, 62vh, 650px) !important;
        border-radius: 18px !important;
        padding: clamp(18px, 2.5vw, 30px) !important;
        border: 1px solid rgba(255,255,255,.13) !important;
        background:
            linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.018)),
            radial-gradient(circle at 74% 16%, rgba(214,151,54,.24), transparent 28%),
            linear-gradient(160deg, rgba(24,18,11,.96), rgba(5,10,23,.98) 54%, rgba(2,6,23,.98)) !important;
        box-shadow: 0 34px 90px rgba(0,0,0,.42) !important;
    }

    .rr-pro-ready .rr-arena-card--x1 {
        background:
            linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.018)),
            radial-gradient(circle at 74% 16%, rgba(22,101,52,.34), transparent 30%),
            linear-gradient(160deg, rgba(8,24,18,.96), rgba(5,10,23,.98) 56%, rgba(2,6,23,.98)) !important;
    }

    .rr-pro-ready .rr-arena-card::after {
        inset: auto 26px 24px !important;
        height: 2px !important;
        background: linear-gradient(90deg, #d69736, rgba(255,248,231,.46), #14532d) !important;
    }

    .rr-pro-ready .rr-arena-card__media {
        min-height: clamp(170px, 25vh, 250px) !important;
        border-radius: 14px !important;
        border-color: rgba(255,255,255,.1) !important;
        background:
            radial-gradient(circle at 50% 35%, rgba(255,248,231,.16), transparent 36%),
            linear-gradient(145deg, rgba(255,255,255,.08), rgba(255,255,255,.02)) !important;
    }

    .rr-pro-ready .rr-arena-card__tag {
        width: fit-content !important;
        min-height: 36px !important;
        padding: 0 13px !important;
        border-color: rgba(214,151,54,.28) !important;
        background: rgba(214,151,54,.12) !important;
        color: #f6d58b !important;
    }

    .rr-pro-ready .rr-arena-card--x1 .rr-arena-card__tag {
        border-color: rgba(34,197,94,.24) !important;
        background: rgba(20,83,45,.22) !important;
        color: #bbf7d0 !important;
    }

    .rr-pro-ready .rr-arena-card__title {
        color: #fff8e7 !important;
        font-size: clamp(3rem, 5.8vw, 5.8rem) !important;
        letter-spacing: -.055em !important;
    }

    .rr-pro-ready .rr-arena-card__copy {
        max-width: 38ch !important;
        color: rgba(226,232,240,.76) !important;
        font-size: 1rem !important;
        line-height: 1.55 !important;
    }

    .rr-pro-ready .rr-arena-card__button {
        min-height: 58px !important;
        border-radius: 14px !important;
        color: #1c1207 !important;
        background: linear-gradient(180deg, #f7d58a 0%, #d69736 52%, #8d5620 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.42), 0 22px 40px rgba(141,86,32,.24) !important;
    }

    .rr-pro-ready .rr-arena-card__button--x1 {
        color: #ecfdf5 !important;
        background: linear-gradient(180deg, #166534 0%, #14532d 58%, #052e16 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.18), 0 22px 40px rgba(5,46,22,.28) !important;
    }

    @media (max-width: 980px) {
        .rr-pro-ready .rr-arena-gateway__shell {
            min-height: auto !important;
            grid-template-columns: 1fr !important;
        }

        .rr-pro-ready .rr-arena-gateway__title {
            max-width: 9ch !important;
        }

        .rr-pro-ready .rr-arena-card {
            min-height: 420px !important;
        }
    }

    @media (max-width: 767px) {
        .rr-pro-ready .rr-arena-gateway {
            min-height: 100dvh !important;
            padding: 12px 14px 18px !important;
            background:
                radial-gradient(circle at 78% 10%, rgba(214,151,54,.12), transparent 30%),
                linear-gradient(180deg, #030712 0%, #07110f 48%, #020617 100%) !important;
        }

        .rr-pro-ready .rr-arena-gateway::before,
        .rr-pro-ready .rr-arena-gateway::after {
            display: none !important;
            animation: none !important;
        }

        .rr-pro-ready .rr-arena-gateway__intro {
            gap: 9px !important;
            padding: 4px 0 0 !important;
        }

        .rr-pro-ready .rr-arena-gateway__intro::before {
            width: 56px !important;
            height: 3px !important;
        }

        .rr-pro-ready .rr-arena-gateway__brand {
            max-width: 100% !important;
            padding: 8px 12px 8px 8px !important;
        }

        .rr-pro-ready .rr-arena-gateway__brand img {
            width: 42px !important;
            height: 42px !important;
        }

        .rr-pro-ready .rr-arena-gateway__kicker {
            min-height: 32px !important;
            font-size: .68rem !important;
        }

        .rr-pro-ready .rr-arena-gateway__title {
            max-width: 100% !important;
            font-size: clamp(2.7rem, 14vw, 4rem) !important;
            line-height: .88 !important;
            letter-spacing: -.045em !important;
        }

        .rr-pro-ready .rr-arena-gateway__copy {
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            font-size: .86rem !important;
            line-height: 1.35 !important;
        }

        .rr-pro-ready .rr-arena-gateway__status {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 7px !important;
        }

        .rr-pro-ready .status-badge {
            min-height: 40px !important;
            justify-content: center !important;
            padding: 0 8px !important;
            text-align: center !important;
            font-size: .68rem !important;
            line-height: 1.1 !important;
        }

        .rr-pro-ready .rr-arena-gateway__choices {
            grid-template-columns: 1fr !important;
            gap: 9px !important;
        }

        .rr-pro-ready .rr-arena-card {
            min-height: auto !important;
            grid-template-columns: 82px minmax(0, 1fr) !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 12px !important;
            border-radius: 16px !important;
            transform: none !important;
            transition: none !important;
            box-shadow: 0 10px 26px rgba(0,0,0,.28) !important;
            background:
                linear-gradient(180deg, rgba(255,255,255,.055), rgba(255,255,255,.016)),
                linear-gradient(160deg, rgba(18,15,10,.96), rgba(5,10,23,.98) 58%, rgba(2,6,23,.98)) !important;
        }

        .rr-pro-ready .rr-arena-card,
        .rr-pro-ready .rr-arena-card *,
        .rr-pro-ready .rr-arena-gateway__brand img {
            animation: none !important;
            will-change: auto !important;
        }

        .rr-pro-ready .rr-arena-card::before,
        .rr-pro-ready .rr-arena-card::after {
            display: none !important;
        }

        .rr-pro-ready .rr-arena-card__media {
            min-height: 96px !important;
            height: 96px !important;
            grid-row: 1 / span 2 !important;
        }

        .rr-pro-ready .rr-arena-card__media img {
            width: 68px !important;
            max-height: 68px !important;
        }

        .rr-pro-ready .rr-arena-card__icon {
            width: 62px !important;
            height: 62px !important;
            font-size: 1.45rem !important;
        }

        .rr-pro-ready .rr-arena-card__body {
            gap: 7px !important;
        }

        .rr-pro-ready .rr-arena-card__title {
            font-size: clamp(1.7rem, 9vw, 2.45rem) !important;
        }

        .rr-pro-ready .rr-arena-card__copy {
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            font-size: .76rem !important;
            line-height: 1.28 !important;
        }

        .rr-pro-ready .rr-arena-card__actions {
            grid-column: 2 !important;
        }

        .rr-pro-ready .rr-arena-card__button {
            min-height: 42px !important;
            border-radius: 12px !important;
            font-size: .8rem !important;
        }
    }
