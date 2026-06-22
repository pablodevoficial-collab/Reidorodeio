<div id="rrPreloader" class="rr-preloader" aria-hidden="true">
    <div class="rr-preloader__backdrop"></div>
    <div class="rr-preloader__dust rr-preloader__dust--left"></div>
    <div class="rr-preloader__dust rr-preloader__dust--right"></div>

    <div class="rr-preloader__stage">
        <div class="rr-preloader__mark">
            <span class="rr-preloader__ring"></span>
            <span class="rr-preloader__ring rr-preloader__ring--delay"></span>
            <img
                src="{{ siteLogo() }}"
                alt="Rei do Rodeio"
                class="rr-preloader__logo rr-preloader__logo--default"
            >
            <img
                src="{{ versionedAsset('assets/images/logo_icon/premiumleague.png') }}"
                alt="Rei do Rodeio Premium"
                class="rr-preloader__logo rr-preloader__logo--premium"
            >
            <span class="rr-preloader__flash"></span>
        </div>

        <div class="rr-preloader__copy">
            <p class="rr-preloader__eyebrow">Arena carregando</p>
            <h2 class="rr-preloader__title rr-ethnocentric">Rei do Rodeio</h2>
            <p class="rr-preloader__subtitle rr-preloader__subtitle--default">Entrando na disputa.</p>
            <p class="rr-preloader__subtitle rr-preloader__subtitle--premium">Liberando a arena premium.</p>
        </div>

        <div class="rr-preloader__progress" aria-hidden="true">
            <span class="rr-preloader__track">
                <span id="rrPreloaderBar" class="rr-preloader__fill"></span>
            </span>
            <span id="rrPreloaderPercent" class="rr-preloader__percent">0%</span>
        </div>
    </div>
</div>

<style>
.rr-preloader {
    --rr-loader-accent: #f97316;
    --rr-loader-accent-soft: rgba(249, 115, 22, 0.28);
    --rr-loader-bg: #050816;
    --rr-loader-surface: rgba(7, 16, 33, 0.76);
    --rr-loader-line: rgba(255, 255, 255, 0.1);
    --rr-loader-text: #f8fafc;
    --rr-loader-muted: rgba(226, 232, 240, 0.76);
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: grid;
    place-items: center;
    overflow: hidden;
    background:
        radial-gradient(circle at top, rgba(249, 115, 22, 0.22), transparent 34%),
        radial-gradient(circle at 20% 80%, rgba(234, 88, 12, 0.18), transparent 28%),
        linear-gradient(145deg, #020617 0%, #08111f 55%, #030712 100%);
    opacity: 1;
    visibility: visible;
    transition: opacity 0.28s ease, visibility 0.28s ease;
}

body[data-user-premium="1"] .rr-preloader {
    --rr-loader-accent: #3b82f6;
    --rr-loader-accent-soft: rgba(59, 130, 246, 0.26);
    background:
        radial-gradient(circle at top, rgba(59, 130, 246, 0.2), transparent 34%),
        radial-gradient(circle at 20% 80%, rgba(14, 165, 233, 0.14), transparent 28%),
        linear-gradient(145deg, #020617 0%, #061222 55%, #020814 100%);
}

.rr-preloader.rr-preloader--hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.rr-preloader__backdrop,
.rr-preloader__dust,
.rr-preloader__flash,
.rr-preloader__ring {
    position: absolute;
}

.rr-preloader__backdrop {
    inset: -12%;
    background:
        linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.035) 50%, transparent 100%),
        repeating-linear-gradient(
            90deg,
            transparent 0,
            transparent 34px,
            rgba(255, 255, 255, 0.02) 35px,
            transparent 36px
        );
    opacity: 0.7;
    transform: skewY(-9deg);
    animation: rrLoaderSweep 4.5s linear infinite;
}

.rr-preloader__dust {
    width: 38vmax;
    height: 38vmax;
    border-radius: 50%;
    filter: blur(30px);
    opacity: 0.5;
    background: radial-gradient(circle, var(--rr-loader-accent-soft) 0%, transparent 68%);
}

.rr-preloader__dust--left {
    top: -14vmax;
    left: -10vmax;
    animation: rrLoaderFloat 4.8s ease-in-out infinite;
}

.rr-preloader__dust--right {
    right: -14vmax;
    bottom: -16vmax;
    animation: rrLoaderFloat 5.4s ease-in-out infinite reverse;
}

.rr-preloader__stage {
    position: relative;
    width: min(92vw, 580px);
    padding: 2rem 1.4rem;
    border: 1px solid var(--rr-loader-line);
    border-radius: 28px;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.015)),
        var(--rr-loader-surface);
    box-shadow:
        0 30px 80px rgba(0, 0, 0, 0.45),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(16px);
    text-align: center;
}

.rr-preloader__mark {
    position: relative;
    width: clamp(126px, 24vw, 188px);
    aspect-ratio: 1;
    margin: 0 auto 1.4rem;
    display: grid;
    place-items: center;
}

.rr-preloader__ring {
    inset: 8%;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.02) inset,
        0 0 28px var(--rr-loader-accent-soft);
    animation: rrLoaderPulse 1.6s ease-out infinite;
}

.rr-preloader__ring--delay {
    inset: -4%;
    animation-delay: 0.55s;
}

.rr-preloader__flash {
    inset: 24%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 72%);
    mix-blend-mode: screen;
    animation: rrLoaderFlash 1.4s ease-in-out infinite;
}

.rr-preloader__logo {
    position: relative;
    z-index: 2;
    width: clamp(82px, 15vw, 122px);
    height: auto;
    display: block;
    filter: drop-shadow(0 0 24px var(--rr-loader-accent-soft));
    transform: translateZ(0);
    animation: rrLoaderLogo 1.4s ease-in-out infinite;
}

.rr-preloader__logo--premium {
    display: none;
}

body[data-user-premium="1"] .rr-preloader__logo--default {
    display: none;
}

body[data-user-premium="1"] .rr-preloader__logo--premium {
    display: block;
}

.rr-preloader__copy {
    display: grid;
    gap: 0.45rem;
    margin-bottom: 1.3rem;
}

.rr-preloader__eyebrow {
    margin: 0;
    font-size: 0.72rem;
    letter-spacing: 0.34em;
    text-transform: uppercase;
    color: var(--rr-loader-accent);
    font-weight: 800;
}

.rr-preloader__title {
    margin: 0;
    color: var(--rr-loader-text);
    font-size: clamp(1.45rem, 5vw, 2.7rem);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rr-preloader__subtitle {
    margin: 0;
    color: var(--rr-loader-muted);
    font-size: 0.96rem;
    font-weight: 500;
}

.rr-preloader__subtitle--premium {
    display: none;
}

body[data-user-premium="1"] .rr-preloader__subtitle--default {
    display: none;
}

body[data-user-premium="1"] .rr-preloader__subtitle--premium {
    display: block;
}

.rr-preloader__progress {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.rr-preloader__track {
    position: relative;
    flex: 1;
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.3);
}

.rr-preloader__fill {
    display: block;
    width: 0;
    height: 100%;
    border-radius: inherit;
    background:
        linear-gradient(90deg, var(--rr-loader-accent) 0%, #fff0 0%),
        linear-gradient(90deg, var(--rr-loader-accent) 0%, #fdba74 42%, var(--rr-loader-accent) 100%);
    background-size: 220% 100%;
    box-shadow: 0 0 26px var(--rr-loader-accent-soft);
    transition: width 0.16s ease-out;
    animation: rrLoaderTrack 1.1s linear infinite;
}

body[data-user-premium="1"] .rr-preloader__fill {
    background:
        linear-gradient(90deg, var(--rr-loader-accent) 0%, #fff0 0%),
        linear-gradient(90deg, var(--rr-loader-accent) 0%, #93c5fd 42%, var(--rr-loader-accent) 100%);
}

.rr-preloader__percent {
    width: 48px;
    text-align: right;
    color: var(--rr-loader-text);
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
}

@keyframes rrLoaderPulse {
    0% {
        transform: scale(0.84);
        opacity: 0;
    }
    45% {
        opacity: 0.95;
    }
    100% {
        transform: scale(1.08);
        opacity: 0;
    }
}

@keyframes rrLoaderLogo {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-5px) scale(1.03);
    }
}

@keyframes rrLoaderFlash {
    0%, 100% {
        opacity: 0.28;
        transform: scale(0.92);
    }
    50% {
        opacity: 0.78;
        transform: scale(1.12);
    }
}

@keyframes rrLoaderTrack {
    from {
        background-position: 200% 0;
    }
    to {
        background-position: -200% 0;
    }
}

@keyframes rrLoaderSweep {
    from {
        transform: translate3d(-6%, -2%, 0) skewY(-9deg);
    }
    to {
        transform: translate3d(6%, 2%, 0) skewY(-9deg);
    }
}

@keyframes rrLoaderFloat {
    0%, 100% {
        transform: translate3d(0, 0, 0);
    }
    50% {
        transform: translate3d(0, 22px, 0);
    }
}

@media (max-width: 640px) {
    .rr-preloader__stage {
        width: min(92vw, 420px);
        padding: 1.55rem 1rem 1.1rem;
        border-radius: 22px;
    }

    .rr-preloader__progress {
        gap: 0.65rem;
    }

    .rr-preloader__percent {
        width: 42px;
        font-size: 0.74rem;
    }

    .rr-preloader__subtitle {
        font-size: 0.88rem;
    }
}
</style>

<script>
(function () {
    'use strict';

    const preloader = document.getElementById('rrPreloader');
    const bar = document.getElementById('rrPreloaderBar');
    const percent = document.getElementById('rrPreloaderPercent');

    if (!preloader || !bar || !percent) {
        return;
    }

    const getNavigationType = () => {
        try {
            const entries = window.performance && typeof window.performance.getEntriesByType === 'function'
                ? window.performance.getEntriesByType('navigation')
                : [];

            if (entries && entries.length && entries[0] && entries[0].type) {
                return entries[0].type;
            }

            if (window.performance && window.performance.navigation) {
                if (window.performance.navigation.type === 1) {
                    return 'reload';
                }
                if (window.performance.navigation.type === 2) {
                    return 'back_forward';
                }
            }
        } catch (error) {
            return 'navigate';
        }

        return 'navigate';
    };

    const isHomePath = window.location.pathname === '/' || window.location.pathname === '';
    const isReload = getNavigationType() === 'reload';
    const throttleWindowMs = 20 * 60 * 1000;
    const storageKey = 'rr_home_loader_last_shown_at';
    let lastShownAt = 0;

    try {
        lastShownAt = Number(window.localStorage.getItem(storageKey) || '0');
    } catch (error) {
        lastShownAt = 0;
    }

    const withinCooldown = lastShownAt > 0 && (Date.now() - lastShownAt) < throttleWindowMs;

    if (!isHomePath || !isReload || withinCooldown) {
        preloader.remove();
        return;
    }

    try {
        window.localStorage.setItem(storageKey, String(Date.now()));
    } catch (error) {}

    const minVisibleMs = 320;
    const fadeMs = 280;
    const maxVisibleMs = 2600;
    const start = Date.now();

    let rafId = 0;
    let hidden = false;
    let current = 0;
    let target = document.readyState === 'complete' ? 88 : 72;

    const setProgress = (value) => {
        current = Math.max(current, Math.min(100, value));
        bar.style.width = current + '%';
        percent.textContent = Math.round(current) + '%';
    };

    const tick = () => {
        if (hidden) {
            return;
        }

        current += (target - current) * 0.12;
        setProgress(current);

        if (Math.abs(target - current) > 0.4) {
            rafId = window.requestAnimationFrame(tick);
        }
    };

    const hideLoader = () => {
        if (hidden) {
            return;
        }

        hidden = true;
        window.cancelAnimationFrame(rafId);
        setProgress(100);
        preloader.classList.add('rr-preloader--hidden');

        window.setTimeout(() => {
            preloader.remove();
        }, fadeMs);
    };

    const finishWhenReady = () => {
        target = 100;
        tick();

        const elapsed = Date.now() - start;
        const remaining = Math.max(0, minVisibleMs - elapsed);
        window.setTimeout(hideLoader, remaining);
    };

    rafId = window.requestAnimationFrame(tick);

    if (document.readyState === 'complete') {
        finishWhenReady();
    } else {
        window.addEventListener('load', finishWhenReady, { once: true });
    }

    window.setTimeout(finishWhenReady, maxVisibleMs);
})();
</script>
