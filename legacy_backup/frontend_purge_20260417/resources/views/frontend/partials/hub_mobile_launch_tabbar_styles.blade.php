/* Launch Menu Refresh
   Authoritative mobile launch tabbar styles isolated from the generic hub stack. */
@keyframes hubLaunchTabPulseOrange {
    0%, 100% {
        box-shadow:
            0 12px 24px rgba(249, 115, 22, 0.28),
            inset 0 1px 0 rgba(255, 255, 255, 0.24);
        filter: saturate(1) brightness(1);
    }
    50% {
        box-shadow:
            0 16px 32px rgba(249, 115, 22, 0.42),
            0 0 0 2px rgba(255, 214, 170, 0.26),
            inset 0 1px 0 rgba(255, 255, 255, 0.34);
        filter: saturate(1.08) brightness(1.06);
    }
}

@keyframes hubLaunchTabPulseBlue {
    0%, 100% {
        box-shadow:
            0 12px 24px rgba(37, 99, 235, 0.26),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
        filter: saturate(1) brightness(1);
    }
    50% {
        box-shadow:
            0 16px 32px rgba(37, 99, 235, 0.4),
            0 0 0 2px rgba(191, 219, 254, 0.22),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
        filter: saturate(1.08) brightness(1.05);
    }
}

@keyframes hubLaunchTabSweep {
    0% {
        transform: translateX(-145%) skewX(-18deg);
        opacity: 0;
    }
    16% {
        opacity: 0.18;
    }
    55% {
        opacity: 0.32;
    }
    100% {
        transform: translateX(155%) skewX(-18deg);
        opacity: 0;
    }
}

.hub-mobile-tabbar__nav--launch,
body.light .hub-mobile-tabbar__nav--launch,
body.light .hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__nav--launch,
.hub-top__grid > .hub-shell__nav .hub-mobile-tabbar__nav--launch {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
}

.hub-mobile-tabbar__nav--launch .hub-tab-border-effect {
    opacity: 0 !important;
    pointer-events: none !important;
}

.hub-mobile-tabbar__launch-stage {
    background: transparent !important;
    gap: 0;
}

.hub-mobile-tabbar__btn--launch-side {
    min-height: 60px;
    padding: 0 20px;
    border-width: 1.5px;
    border-style: solid;
    border-radius: 24px;
    color: #fff7ed !important;
    text-shadow: 0 2px 12px rgba(15, 23, 42, 0.28);
    overflow: hidden;
    isolation: isolate;
    gap: 0;
    transition:
        transform 0.22s ease,
        box-shadow 0.24s ease,
        filter 0.24s ease,
        opacity 0.22s ease;
}

.hub-mobile-tabbar__btn--launch-side::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 18%, rgba(255, 255, 255, 0.34) 48%, transparent 82%);
    transform: translateX(-145%) skewX(-18deg);
    opacity: 0;
    pointer-events: none;
    z-index: 0;
}

.hub-mobile-tabbar__btn--launch-side > * {
    position: relative;
    z-index: 1;
}

.hub-mobile-tabbar__btn--launch-side.home {
    margin-right: -26px;
    padding-right: 48px;
    justify-content: center;
    background: linear-gradient(135deg, #ffb347 0%, #f97316 52%, #c2410c 100%) !important;
    border-color: rgba(255, 229, 204, 0.34) !important;
    box-shadow:
        0 12px 24px rgba(249, 115, 22, 0.28),
        inset 0 1px 0 rgba(255, 255, 255, 0.24);
    -webkit-mask-image: var(--hub-launch-side-mask);
    mask-image: var(--hub-launch-side-mask);
    -webkit-mask-size: 100% 100%;
    mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-position: center;
}

.hub-mobile-tabbar__btn--launch-side.user {
    margin-left: -26px;
    padding-left: 48px;
    justify-content: center;
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 54%, #1d4ed8 100%) !important;
    border-color: rgba(219, 234, 254, 0.3) !important;
    box-shadow:
        0 12px 24px rgba(37, 99, 235, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    -webkit-mask-image: var(--hub-launch-side-mask);
    mask-image: var(--hub-launch-side-mask);
    -webkit-mask-size: 100% 100%;
    mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
    mask-repeat: no-repeat;
    -webkit-mask-position: center;
    mask-position: center;
}

.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter:not(.active) {
    opacity: 0.9;
}

.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter.active {
    color: #ffffff !important;
    border-color: rgba(255, 255, 255, 0.54) !important;
}

.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter.active::before {
    animation: hubLaunchTabSweep 2.3s linear infinite;
}

.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter.home.active {
    animation: hubLaunchTabPulseOrange 2s ease-in-out infinite;
}

.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-filter.user.active {
    animation: hubLaunchTabPulseBlue 2s ease-in-out infinite;
}

.hub-mobile-tabbar__btn--launch-side i {
    font-size: 0.94rem;
    opacity: 1;
}

.hub-mobile-tabbar__launch-filter-label {
    font-size: 1.22rem;
    font-weight: 900;
    letter-spacing: 0.01em;
    line-height: 1;
}

.hub-mobile-tabbar__btn--launch-avatar {
    z-index: 5;
}

@media (hover: hover) {
    .hub-mobile-tabbar__btn--launch-side:hover {
        transform: translateY(-2px);
        opacity: 1;
        filter: brightness(1.04);
    }
}

@media (max-width: 700px) {
    .hub-mobile-tabbar__nav--launch {
        padding: 10px 8px calc(8px + env(safe-area-inset-bottom, 0px));
    }

    .hub-mobile-tabbar__launch-stage {
        min-height: 96px;
        gap: 8px;
    }

    .hub-mobile-tabbar {
        left: max(0.65rem, env(safe-area-inset-left, 0px));
        right: max(0.65rem, env(safe-area-inset-right, 0px));
        margin: 0 auto;
    }

    .hub-mobile-tabbar__btn--launch-side {
        min-height: 52px;
        padding: 0 14px;
        border-radius: 20px;
    }

    .hub-mobile-tabbar__btn--launch-side.home {
        margin-right: 0;
        padding-right: 26px;
        -webkit-mask-image: var(--hub-launch-side-mask);
        mask-image: var(--hub-launch-side-mask);
        -webkit-mask-size: 100% 100%;
        mask-size: 100% 100%;
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        -webkit-mask-position: center;
        mask-position: center;
    }

    .hub-mobile-tabbar__btn--launch-side.user {
        margin-left: 0;
        padding-left: 26px;
        -webkit-mask-image: var(--hub-launch-side-mask);
        mask-image: var(--hub-launch-side-mask);
        -webkit-mask-size: 100% 100%;
        mask-size: 100% 100%;
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        -webkit-mask-position: center;
        mask-position: center;
    }
}

@media (max-width: 420px) {
    .hub-mobile-tabbar__btn--launch-side {
        min-height: 48px;
        padding: 0 13px;
    }

    .hub-mobile-tabbar__btn--launch-side.home {
        margin-right: 0;
        padding-right: 22px;
        -webkit-mask-image: var(--hub-launch-side-mask);
        mask-image: var(--hub-launch-side-mask);
        -webkit-mask-size: 100% 100%;
        mask-size: 100% 100%;
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        -webkit-mask-position: center;
        mask-position: center;
    }

    .hub-mobile-tabbar__btn--launch-side.user {
        margin-left: 0;
        padding-left: 22px;
        -webkit-mask-image: var(--hub-launch-side-mask);
        mask-image: var(--hub-launch-side-mask);
        -webkit-mask-size: 100% 100%;
        mask-size: 100% 100%;
        -webkit-mask-repeat: no-repeat;
        mask-repeat: no-repeat;
        -webkit-mask-position: center;
        mask-position: center;
    }

    .hub-mobile-tabbar__launch-filter-label {
        font-size: 1.08rem;
    }
}

body.light .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn.active,
body:not(.light) .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn.active,
.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn.active {
    color: #fff7ed !important;
}

body.light .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-side,
body:not(.light) .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-side,
.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-side,
body.light .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-side .hub-mobile-tabbar__launch-filter-label,
body:not(.light) .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-side .hub-mobile-tabbar__launch-filter-label,
.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn--launch-side .hub-mobile-tabbar__launch-filter-label {
    color: #ffffff !important;
}

body.light .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn.active .hub-mobile-tabbar__launch-filter-label,
body:not(.light) .hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn.active .hub-mobile-tabbar__launch-filter-label,
.hub-mobile-tabbar__nav--launch .hub-mobile-tabbar__btn.active .hub-mobile-tabbar__launch-filter-label {
    color: #fff7ed !important;
}
