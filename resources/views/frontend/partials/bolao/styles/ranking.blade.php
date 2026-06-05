    .rr-ranking-shell {
        display: grid;
        gap: 16px;
    }

    .rr-ranking-hero,
    .rr-ranking-list-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,.08);
        background:
            radial-gradient(circle at top, rgba(249,115,22,.14), transparent 30%),
            radial-gradient(circle at right, rgba(59,130,246,.12), transparent 36%),
            linear-gradient(180deg, rgba(15,23,42,.96), rgba(3,7,18,.98));
        box-shadow: 0 18px 36px rgba(0,0,0,.28);
    }

    .rr-ranking-hero {
        padding: 18px;
        display: grid;
        gap: 16px;
    }

    .rr-ranking-hero__topline,
    .rr-ranking-list-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .rr-ranking-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(249,115,22,.22);
        background: linear-gradient(135deg, rgba(249,115,22,.16), rgba(59,130,246,.12));
        color: #fff7ed;
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .rr-ranking-badge--alt {
        border-color: rgba(255,255,255,.12);
        color: rgba(226,232,240,.76);
        background: rgba(255,255,255,.05);
    }

    .rr-ranking-hero__grid {
        display: grid;
        grid-template-columns: minmax(0,1fr) minmax(280px, 360px);
        gap: 16px;
        align-items: start;
    }

    .rr-ranking-hero__eyebrow {
        display: inline-block;
        color: #fdba74;
        font-size: .76rem;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .rr-ranking-hero__title {
        margin: 0;
        color: #fff;
        font-size: clamp(1.2rem, 2vw, 1.8rem);
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .rr-ranking-hero__copy p,
    .rr-ranking-list-header__meta {
        margin: 8px 0 0;
        color: rgba(226,232,240,.72);
        font-size: .84rem;
        line-height: 1.5;
    }

    .rr-ranking-stats {
        display: grid;
        gap: 10px;
    }

    .rr-ranking-stat {
        padding: 12px 14px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.08);
        background: rgba(255,255,255,.05);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
    }

    .rr-ranking-stat span {
        display: block;
        color: rgba(226,232,240,.72);
        font-size: .74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .rr-ranking-stat strong {
        display: block;
        margin-top: 6px;
        color: #fff;
        font-size: 1rem;
        font-weight: 900;
    }

    .rr-ranking-podium {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 12px;
        align-items: end;
    }

    .rr-ranking-podium-card {
        position: relative;
        min-height: 214px;
        display: grid;
        gap: 10px;
        align-content: start;
        justify-items: center;
        padding: 14px 14px 16px;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,.08);
        background: linear-gradient(180deg, rgba(15,23,42,.92), rgba(3,7,18,.98));
        box-shadow: 0 16px 28px rgba(0,0,0,.22);
        text-align: center;
    }

    .rr-ranking-podium-card--champion {
        min-height: 242px;
        transform: translateY(-10px);
        border-color: rgba(249,115,22,.34);
        box-shadow: 0 20px 30px rgba(0,0,0,.24), 0 0 0 1px rgba(249,115,22,.12);
    }

    .rr-ranking-podium-card--silver { border-color: rgba(59,130,246,.28); }
    .rr-ranking-podium-card--bronze { border-color: rgba(255,255,255,.14); }
    .rr-ranking-podium-card--mine {
        background:
            linear-gradient(180deg, rgba(249,115,22,.1), rgba(59,130,246,.08)),
            linear-gradient(180deg, rgba(15,23,42,.92), rgba(3,7,18,.98));
    }

    .rr-ranking-podium-card__medal {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-weight: 900;
        color: #fff;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.1);
    }

    .rr-ranking-podium-card--champion .rr-ranking-podium-card__medal { background: linear-gradient(135deg, #f97316, #fb923c); }
    .rr-ranking-podium-card--silver .rr-ranking-podium-card__medal { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .rr-ranking-podium-card--bronze .rr-ranking-podium-card__medal { background: linear-gradient(135deg, #fff, #e5e7eb); color:#0f172a; }

    .rr-ranking-podium-card__avatar {
        width: 70px;
        height: 70px;
        border-radius: 20px;
        display: grid;
        place-items: center;
        overflow: hidden;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.1);
        color: #fff;
        font-weight: 900;
        font-size: 1rem;
    }

    .rr-ranking-podium-card__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rr-ranking-podium-card__name {
        color: #fff;
        font-weight: 900;
        font-size: .98rem;
        line-height: 1.15;
    }

    .rr-ranking-podium-card__meta,
    .rr-ranking-podium-card__prize {
        color: rgba(226,232,240,.74);
        font-size: .8rem;
    }

    .rr-ranking-podium-card__points {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.05);
        color: #fff7ed;
        font-weight: 900;
        font-size: .8rem;
    }

    .rr-ranking-list-wrap {
        padding: 16px;
        display: grid;
        gap: 12px;
    }

    .rr-ranking-list-header__title {
        margin: 0;
        color: #fff;
        font-size: 1rem;
        font-weight: 900;
    }

    .rr-ranking-list {
        display: grid;
        gap: 10px;
        max-height: 440px;
        overflow: auto;
        scrollbar-width: none;
    }

    .rr-ranking-list::-webkit-scrollbar { display: none; }

    .rr-ranking-row {
        position: relative;
        display: grid;
        grid-template-columns: 50px 44px minmax(0,1fr) auto 16px;
        gap: 12px;
        align-items: center;
        padding: 12px 14px;
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.08);
        background: linear-gradient(180deg, rgba(15,23,42,.9), rgba(3,7,18,.96));
        box-shadow: 0 12px 24px rgba(0,0,0,.18);
        overflow: hidden;
    }

    .rr-ranking-row::before {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(90deg, rgba(249,115,22,.12), transparent 38%, rgba(59,130,246,.08));
        opacity: .85;
    }

    .rr-ranking-row--gold{ border-color: rgba(249,115,22,.34); box-shadow: 0 18px 28px rgba(0,0,0,.24), 0 0 0 1px rgba(249,115,22,.12); }
    .rr-ranking-row--silver{ border-color: rgba(59,130,246,.28); }
    .rr-ranking-row--bronze{ border-color: rgba(255,255,255,.14); }
    .rr-ranking-row--mine{
        background:
            linear-gradient(180deg, rgba(249,115,22,.12), rgba(59,130,246,.1)),
            linear-gradient(180deg, rgba(15,23,42,.92), rgba(3,7,18,.96));
        border-color: rgba(249,115,22,.42);
    }

    .rr-ranking-row__pos,
    .rr-ranking-row__avatar,
    .rr-ranking-row__body,
    .rr-ranking-row__score,
    .rr-ranking-row__chev {
        position: relative;
        z-index: 1;
    }

    .rr-ranking-row__pos {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        color: #fff;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.08);
    }

    .rr-ranking-row--gold .rr-ranking-row__pos{ background: linear-gradient(135deg, #f97316, #fb923c); }
    .rr-ranking-row--silver .rr-ranking-row__pos{ background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .rr-ranking-row--bronze .rr-ranking-row__pos{ background: linear-gradient(135deg, #fff, #e5e7eb); color:#0f172a; }

    .rr-ranking-row__avatar {
        width: 44px;
        height: 44px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.1);
        background: rgba(255,255,255,.05);
        display: grid;
        place-items: center;
        color: #fff;
        font-weight: 900;
        font-size: .84rem;
    }

    .rr-ranking-row__avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rr-ranking-row__body {
        min-width: 0;
        display: grid;
        gap: 6px;
    }

    .rr-ranking-row__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .rr-ranking-row__name { color:#fff; font-weight:900; line-height:1.15; font-size:.94rem; }
    .rr-ranking-row__subtitle { margin-top:.15rem; color: rgba(226,232,240,.72); font-size:.78rem; line-height:1.35; }
    .rr-ranking-row__points { display:inline-flex; align-items:center; min-height:32px; padding:0 .72rem; border-radius:999px; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.05); color:#fff; font-weight:900; font-size:.78rem; white-space:nowrap; }
    .rr-ranking-row__bar{ height:8px; border-radius:999px; overflow:hidden; background: rgba(255,255,255,.07); }
    .rr-ranking-row__bar span{ display:block; height:100%; border-radius:inherit; background: linear-gradient(90deg, #f97316, #3b82f6); box-shadow: 0 0 14px rgba(59,130,246,.3); }
    .rr-ranking-row__score { color:#fff7ed; font-weight:900; font-size:.82rem; }
    .rr-ranking-row__chev { color: rgba(226,232,240,.55); font-size: 1rem; }

    .rr-ranking-empty{
        position:relative;
        overflow:hidden;
        display:grid;
        gap:.55rem;
        justify-items:center;
        padding:1.2rem;
        text-align:center;
        border-radius:20px;
        border:1px dashed rgba(255,255,255,.12);
        background: linear-gradient(180deg, rgba(15,23,42,.62), rgba(3,7,18,.86));
        color:rgba(226,232,240,.78);
    }

    .rr-ranking-empty__badge{
        display:inline-flex;
        align-items:center;
        gap:.5rem;
        min-height:36px;
        padding:0 .96rem;
        border-radius:999px;
        border:1px solid rgba(255,255,255,.12);
        background: linear-gradient(135deg, rgba(249,115,22,.16), rgba(59,130,246,.12));
        color:#fff7ed;
        font-size:.76rem;
        font-weight:900;
        letter-spacing:.08em;
        text-transform:uppercase;
    }

    .rr-ranking-empty__title{ color:#fff; font-size:1rem; font-weight:900; letter-spacing:-.03em; }
    .rr-ranking-empty__text{ max-width:420px; color:rgba(226,232,240,.72); font-size:.84rem; line-height:1.55; }

    .rr-side__controls .rr-hero__btn,
    .rr-side__controls .rr-select,
    .rr-side__controls .rr-select-wrap {
        width: 100%;
    }

    .rr-select-wrap {
        position: relative;
        width: 100%;
    }

    .rr-select-wrap__icon-left,
    .rr-select-wrap__icon-right {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .rr-select-wrap__icon-left {
        left: 22px;
        color: rgba(236, 252, 203, 0.6);
    }

    .rr-select-wrap__icon-right {
        right: 22px;
        color: #ecfccb;
    }

    .rr-desktop-only {
        display: none;
    }

    @media (min-width: 768px) {
        .rr-desktop-only {
            display: grid;
        }

        .rr-side__nav {
            display: grid;
        }

        .rr-mobile-only {
            display: none !important;
        }
    }

    @media (max-width: 767px) {
        .rr-live {
            padding-bottom: 0;
        }

        .rr-mobile-refresh-fixed {
            position: fixed !important;
            right: max(20px, env(safe-area-inset-right)) !important;
            bottom: max(20px, env(safe-area-inset-bottom)) !important;
            z-index: 50 !important;
            min-height: 56px !important;
            padding: 0 24px !important;
            background: linear-gradient(135deg, #10b981, #047857) !important;
            border: 2px solid #6ee7b7 !important;
            color: #fff !important;
            box-shadow: 0 8px 30px rgba(0,0,0,0.6) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .rr-mobile-refresh-fixed:active {
            transform: translateY(2px) scale(0.95);
        }

        .rr-mobile-refresh-fab {
            position: fixed;
            right: max(14px, env(safe-area-inset-right));
            bottom: max(14px, env(safe-area-inset-bottom));
            z-index: 80;
            display: block;
        }

        .rr-mobile-refresh-fab.is-hidden {
            display: none !important;
        }

        .rr-mobile-refresh-fixed--icon-only {
            min-width: 56px !important;
            width: 56px !important;
            padding: 0 !important;
            gap: 0 !important;
            justify-content: center !important;
        }

        .rr-mobile-refresh-fixed--icon-only span {
            display: none !important;
        }

        .rr-mobile-refresh-fixed--icon-only i {
            margin-right: 0 !important;
        }

        .rr-mobile-actions {
            margin: -26px 18px 0;
        }
    }
    @media (max-width: 767px) {
        .rr-mobile-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: -26px 18px 0;
            position: relative;
            z-index: 24;
        }

        .rr-ranking-hero__grid {
            grid-template-columns: 1fr;
        }

        .rr-ranking-podium {
            grid-template-columns: 1fr;
        }

        .rr-ranking-podium-card,
        .rr-ranking-podium-card--champion {
            min-height: auto;
            transform: none;
        }

        .rr-ranking-list {
            max-height: 360px;
        }

        .rr-ranking-row {
            grid-template-columns: 44px 38px minmax(0,1fr) auto 12px;
            gap: .6rem;
            padding: .78rem .8rem;
        }

        .rr-ranking-row__pos {
            width: 44px;
            height: 44px;
            border-radius: 14px;
        }

        .rr-ranking-row__avatar {
            width: 38px;
            height: 38px;
            border-radius: 14px;
        }
    }

    @media (min-width: 768px) {
        .rr-app {
            gap: 12px;
        }

        .rr-live {
            gap: 12px;
            padding-bottom: 32px;
        }

        .rr-live__top,
        .rr-live__hero,
        .rr-cards {
            padding: 14px 18px;
        }

        .rr-live__grid {
            gap: 12px;
            grid-template-columns: minmax(0, 1.15fr) minmax(240px, 300px) minmax(0, 1fr);
        }

        .rr-side {
            gap: 8px;
        }

        .rr-side__headline {
            font-size: clamp(1.55rem, 2.1vw, 2.45rem);
            line-height: 1;
        }

        .rr-side__text {
            font-size: .9rem;
            line-height: 1.45;
        }

        .rr-side__nav {
            margin-top: 12px;
            gap: 10px;
            width: min(100%, 500px);
        }

        .rr-side__nav-btn {
            min-height: 48px;
            font-size: .9rem;
            padding: 0 14px;
            letter-spacing: .01em;
        }

        .rr-side__nav-btn i {
            font-size: 1.02em;
        }

        .rr-stat {
            padding: 10px 12px;
            border-radius: 16px;
        }

        .rr-stat small {
            font-size: .66rem;
        }

        .rr-stat strong {
            margin-top: 4px;
            font-size: .9rem;
        }

        .rr-hero__center {
            gap: 8px;
        }

        .rr-logo-wrap {
            width: min(100%, 190px);
            padding: 12px;
            border-radius: 22px;
        }

        .rr-logo {
            height: 108px;
        }

        .rr-hero__name {
            min-height: 42px;
            padding: 0 14px;
            font-size: .92rem;
        }

        .rr-countdown {
            min-height: 46px;
            padding: 0 18px;
        }

        .rr-hero__actions {
            gap: 8px;
        }

        .rr-hero__btn {
            min-height: 44px;
            padding: 0 14px;
            font-size: .92rem;
        }

        .rr-cards {
            gap: 12px;
        }

        .rr-cards__grid {
            gap: 12px;
        }

        .rr-card {
            min-height: 242px;
            padding: 14px;
        }

        .rr-card__ghost {
            width: 92px;
            inset: 16px 14px auto auto;
        }

        .rr-card__prize-wrap {
            min-height: 120px;
            margin-top: 10px;
            padding: 12px 12px 10px;
        }

        .rr-card__prize-label {
            margin-bottom: 6px;
            padding: 3px 12px;
            font-size: .68rem;
        }

        .rr-card__prize-frame {
            min-height: 72px;
            padding: 10px 12px;
        }

        .rr-card__prize-value {
            font-size: clamp(1.45rem, 2vw, 2rem);
        }

        .rr-card__meta {
            margin-top: 10px;
        }

        .rr-card__actions {
            margin-top: 14px;
        }
    }

