@props([
    'items' => [],
    'id' => 'rrSubmenu',
    'activeIndex' => 0
])

<nav class="rr-epic-submenu" id="{{ $id }}" role="tablist">
    <div class="rr-epic-submenu__track">
        <div class="rr-epic-submenu__effect" data-effect></div>
        
        @foreach($items as $index => $item)
        <button 
            type="button"
            role="tab"
            class="rr-epic-submenu__btn {{ $index === $activeIndex ? 'is-active' : '' }} {{ $item['variant'] ?? '' }}"
            data-filter="{{ $item['filter'] ?? $item['label'] }}"
            data-index="{{ $index }}"
            @if(!empty($item['id'])) id="{{ $item['id'] }}" @endif
            @if(!empty($item['action'])) data-action="{{ $item['action'] }}" @endif
            @if(!empty($item['onclick'])) onclick="{{ $item['onclick'] }}" @endif
            @if(!empty($item['accent'])) style="--submenu-accent: {{ $item['accent'] }}" @endif
            aria-selected="{{ $index === $activeIndex ? 'true' : 'false' }}"
        >
            @if(!empty($item['crown']))
            <span class="rr-epic-submenu__crown"><i class="fas fa-crown"></i></span>
            @endif
            
            <span class="rr-epic-submenu__icon-wrap">
                @if(!empty($item['icon']))
                <i class="{{ $item['icon'] }} rr-epic-submenu__icon"></i>
                @else
                <span class="rr-epic-submenu__count" data-count="{{ $item['filter'] ?? $item['label'] }}">{{ $item['count'] ?? 0 }}</span>
                @endif
            </span>
            
            <span class="rr-epic-submenu__text">
                <span class="rr-epic-submenu__label">{{ $item['label'] }}</span>
                @if(!empty($item['meta']))
                <span class="rr-epic-submenu__meta">{{ $item['meta'] }}</span>
                @endif
            </span>
        </button>
        @endforeach
    </div>
</nav>

<style>
.rr-epic-submenu {
    position: sticky;
    top: calc(var(--rr-navbar-height, 0px) + 12px);
    z-index: 40;
    width: 100%;
    margin-bottom: 1rem;
}

body.hub-page .rr-epic-submenu {
    top: calc(var(--hub-navbar-height, 0px) + 12px);
}

.rr-epic-submenu__track {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: stretch;
    gap: 0;
    padding: 6px;
    background: linear-gradient(180deg, rgba(5, 10, 18, 0.94) 0%, rgba(9, 15, 26, 0.96) 100%);
    border-radius: 22px;
    overflow: hidden;
    border: 1px solid rgba(96, 165, 250, 0.24);
    box-shadow:
        0 18px 40px rgba(2, 6, 15, 0.42),
        inset 0 1px 0 rgba(255,255,255,0.06),
        inset 0 0 0 1px rgba(96, 165, 250, 0.04);
    backdrop-filter: blur(18px) saturate(1.08);
    isolation: isolate;
}

.rr-epic-submenu__track::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(90deg, rgba(34, 211, 238, 0.08), transparent 24%, transparent 76%, rgba(59, 130, 246, 0.08)),
        linear-gradient(180deg, rgba(255,255,255,0.08), transparent 28%);
    pointer-events: none;
    z-index: 0;
}

.rr-epic-submenu__track::after {
    content: "";
    position: absolute;
    inset: 1px;
    border-radius: inherit;
    background: repeating-linear-gradient(
        90deg,
        transparent 0,
        transparent 22px,
        rgba(148, 163, 184, 0.06) 23px,
        transparent 24px
    );
    mask-image: linear-gradient(180deg, rgba(0,0,0,0.42), transparent 85%);
    pointer-events: none;
    z-index: 0;
}

.rr-epic-submenu__effect {
    position: absolute;
    top: 6px;
    left: 0;
    width: 72px;
    height: calc(100% - 12px);
    border-radius: 18px;
    background: linear-gradient(180deg, color-mix(in srgb, var(--submenu-active-color, #60a5fa) 22%, transparent), rgba(255,255,255,0.03));
    border: 1px solid color-mix(in srgb, var(--submenu-active-color, #60a5fa) 48%, rgba(255,255,255,0.1));
    box-shadow:
        0 0 0 1px rgba(255,255,255,0.03) inset,
        0 12px 24px color-mix(in srgb, var(--submenu-active-color, #60a5fa) 18%, transparent),
        0 0 20px color-mix(in srgb, var(--submenu-active-color, #60a5fa) 14%, transparent);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1;
    opacity: 0;
}

.rr-epic-submenu__effect.is-ready {
    opacity: 1;
}

.rr-epic-submenu__btn {
    cursor: pointer;
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 5px;
    background: transparent;
    border: 0;
    padding: 14px 10px 12px;
    margin: 0;
    color: rgba(232, 239, 255, 0.9);
    transition: color 0.28s ease, transform 0.28s ease, opacity 0.28s ease;
    -webkit-tap-highlight-color: transparent;
    position: relative;
    min-width: 0;
    z-index: 2;
    --submenu-accent: #60a5fa;
}

.rr-epic-submenu__btn::before {
    content: "";
    position: absolute;
    inset: 4px 2px;
    border-radius: 16px;
    background: linear-gradient(180deg, rgba(255,255,255,0.04), transparent 72%);
    opacity: 0;
    transform: scale(0.98);
    transition: opacity 0.28s ease, transform 0.28s ease;
    z-index: -1;
}

.rr-epic-submenu__icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
    border: 1px solid rgba(160, 188, 255, 0.14);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.06),
        0 8px 16px rgba(0,0,0,0.18);
    transition: all 0.28s ease;
}

.rr-epic-submenu__icon {
    font-size: 15px;
    transition: all 0.28s ease;
}

.rr-epic-submenu__count {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.04em;
    transition: all 0.28s ease;
}

.rr-epic-submenu__text {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.rr-epic-submenu__label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 84px;
}

.rr-epic-submenu__meta {
    font-size: 9px;
    font-weight: 600;
    opacity: 0.74;
    white-space: nowrap;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

#rrStatsSubmenu .rr-epic-submenu__text {
    width: 100%;
    min-width: 0;
}

#rrStatsSubmenu .rr-epic-submenu__label {
    display: block;
    width: 100%;
    max-width: none;
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
    line-height: 1.08;
    text-align: center;
    overflow-wrap: anywhere;
}

.rr-epic-submenu__crown {
    position: absolute;
    top: 7px;
    right: 7px;
    width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(251, 191, 36, 0.14);
    border: 1px solid rgba(251, 191, 36, 0.32);
    font-size: 9px;
    color: #fbbf24;
    filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.45));
    animation: submenuCrownBounce 2s ease-in-out infinite;
}

@keyframes submenuCrownBounce {
    0%, 100% { transform: translateY(0) scale(1); }
    25% { transform: translateY(-2px) scale(1.08); }
    50% { transform: translateY(0) rotate(8deg); }
    75% { transform: translateY(-1px) rotate(-8deg); }
}

.rr-epic-submenu__btn.is-active {
    color: var(--submenu-accent, #60a5fa);
}

.rr-epic-submenu__btn.is-active::before {
    opacity: 1;
    transform: scale(1);
}

.rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap {
    background: linear-gradient(180deg, color-mix(in srgb, var(--submenu-accent, #60a5fa) 22%, rgba(255,255,255,0.05)), rgba(255,255,255,0.03));
    border-color: color-mix(in srgb, var(--submenu-accent, #60a5fa) 52%, rgba(255,255,255,0.12));
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.08),
        0 0 0 1px color-mix(in srgb, var(--submenu-accent, #60a5fa) 12%, transparent),
        0 12px 22px color-mix(in srgb, var(--submenu-accent, #60a5fa) 20%, transparent);
}

.rr-epic-submenu__btn.is-active .rr-epic-submenu__icon,
.rr-epic-submenu__btn.is-active .rr-epic-submenu__count {
    color: var(--submenu-accent, #60a5fa);
    filter: drop-shadow(0 0 10px color-mix(in srgb, var(--submenu-accent, #60a5fa) 55%, transparent));
}

.rr-epic-submenu__btn.is-active .rr-epic-submenu__label {
    text-shadow: 0 0 10px color-mix(in srgb, var(--submenu-accent, #60a5fa) 28%, transparent);
}

@media (hover: hover) {
    .rr-epic-submenu__btn:not(.is-active):hover {
        color: var(--submenu-accent, #60a5fa);
    }

    .rr-epic-submenu__btn:not(.is-active):hover::before {
        opacity: 0.75;
        transform: scale(1);
    }

    .rr-epic-submenu__btn:not(.is-active):hover .rr-epic-submenu__icon-wrap {
        background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.03));
        border-color: color-mix(in srgb, var(--submenu-accent, #60a5fa) 22%, rgba(255,255,255,0.12));
        transform: translateY(-2px);
    }
}

.rr-epic-submenu__btn.rr-submenu__card--create {
    --submenu-accent: #22c55e;
}

.rr-epic-submenu__btn.rr-submenu__card--create::before {
    opacity: 1;
    transform: scale(1);
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.16), rgba(34, 211, 238, 0.06));
}

.rr-epic-submenu__btn.rr-submenu__card--create .rr-epic-submenu__icon-wrap {
    background: linear-gradient(180deg, rgba(34, 197, 94, 0.16), rgba(34, 197, 94, 0.05));
    border-color: rgba(34, 197, 94, 0.3);
}

.rr-epic-submenu__btn.rr-submenu__card--create .rr-epic-submenu__icon {
    color: #22c55e;
}

.rr-epic-submenu__btn.rr-submenu__card--create .rr-epic-submenu__label {
    color: #eafff2;
}

.rr-epic-submenu__btn.rr-submenu__card--create .rr-epic-submenu__meta {
    color: rgba(125, 255, 190, 0.8);
}

@media (hover: hover) {
    .rr-epic-submenu__btn.rr-submenu__card--create:hover .rr-epic-submenu__icon {
        transform: rotate(90deg);
    }
}

.rr-epic-submenu__btn.rr-submenu__card--premium {
    --submenu-accent: #3b82f6;
}

#rrPerfilSubmenu {
    --rr-perfil-submenu-cyan: #22d3ee;
    --rr-perfil-submenu-blue: #2563eb;
    --rr-perfil-submenu-green: #22c55e;
}

#rrPerfilSubmenu .rr-epic-submenu__track {
    background:
        radial-gradient(circle at top center, rgba(34, 211, 238, 0.14), transparent 34%),
        linear-gradient(180deg, rgba(3, 10, 24, 0.96) 0%, rgba(6, 18, 40, 0.98) 100%);
    border-color: rgba(34, 211, 238, 0.26);
    box-shadow:
        0 22px 48px rgba(2, 8, 20, 0.52),
        inset 0 1px 0 rgba(255,255,255,0.08),
        inset 0 0 0 1px rgba(34, 211, 238, 0.05),
        0 0 26px rgba(37, 99, 235, 0.08);
}

#rrPerfilSubmenu .rr-epic-submenu__track::before {
    background:
        linear-gradient(90deg, rgba(34, 211, 238, 0.12), transparent 18%, transparent 82%, rgba(37, 99, 235, 0.14)),
        linear-gradient(180deg, rgba(255,255,255,0.1), transparent 26%);
}

#rrPerfilSubmenu .rr-epic-submenu__track::after {
    background:
        repeating-linear-gradient(
            90deg,
            transparent 0,
            transparent 19px,
            rgba(34, 211, 238, 0.08) 20px,
            transparent 21px
        ),
        repeating-linear-gradient(
            180deg,
            rgba(255,255,255,0.04) 0,
            rgba(255,255,255,0.04) 1px,
            transparent 1px,
            transparent 10px
        );
    animation: rrPerfilSubmenuScan 8s linear infinite;
}

#rrPerfilSubmenu .rr-epic-submenu__btn {
    color: rgba(232, 245, 255, 0.94);
}

#rrPerfilSubmenu .rr-epic-submenu__btn::after {
    content: "";
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 7px;
    height: 1px;
    background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--submenu-accent, #22d3ee) 85%, white), transparent);
    opacity: 0;
    transform: scaleX(0.35);
    transform-origin: center;
    transition: opacity 0.28s ease, transform 0.28s ease;
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active::after,
#rrPerfilSubmenu .rr-epic-submenu__btn:hover::after {
    opacity: 0.9;
    transform: scaleX(1);
}

#rrPerfilSubmenu .rr-epic-submenu__icon-wrap {
    border-radius: 12px;
    background:
        linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02)),
        linear-gradient(135deg, rgba(34, 211, 238, 0.08), transparent 60%);
}

#rrPerfilSubmenu .rr-epic-submenu__label {
    letter-spacing: 0.12em;
}

#rrPerfilSubmenu .rr-epic-submenu__meta {
    opacity: 0.8;
    color: rgba(148, 163, 184, 0.95);
}

#rrPerfilSubmenu .rr-epic-submenu__effect {
    background:
        linear-gradient(180deg, color-mix(in srgb, var(--submenu-active-color, #22d3ee) 24%, transparent), rgba(255,255,255,0.04));
    box-shadow:
        0 0 0 1px rgba(255,255,255,0.03) inset,
        0 12px 28px color-mix(in srgb, var(--submenu-active-color, #22d3ee) 20%, transparent),
        0 0 30px color-mix(in srgb, var(--submenu-active-color, #22d3ee) 16%, transparent);
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"] {
    --submenu-accent: var(--rr-perfil-submenu-blue);
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"] {
    --submenu-accent: var(--rr-perfil-submenu-green);
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"] .rr-epic-submenu__icon-wrap {
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.06),
        0 8px 18px rgba(37, 99, 235, 0.16);
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"] .rr-epic-submenu__icon-wrap {
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.06),
        0 8px 18px rgba(34, 197, 94, 0.14);
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap {
    transform: translateY(-1px) scale(1.02);
}

@keyframes rrPerfilSubmenuScan {
    0% {
        transform: translateX(-2%);
        opacity: 0.42;
    }
    50% {
        transform: translateX(2%);
        opacity: 0.78;
    }
    100% {
        transform: translateX(-2%);
        opacity: 0.42;
    }
}

@media (max-width: 576px) {
    .rr-epic-submenu__track {
        border-radius: 18px;
        padding: 5px;
    }

    .rr-epic-submenu__btn {
        padding: 11px 4px 9px;
        gap: 4px;
    }

    .rr-epic-submenu__icon-wrap {
        width: 34px;
        height: 34px;
        border-radius: 12px;
    }

    .rr-epic-submenu__icon {
        font-size: 13px;
    }

    .rr-epic-submenu__count {
        font-size: 12px;
    }

    .rr-epic-submenu__label {
        font-size: 9px;
        max-width: 58px;
    }

    #rrStatsSubmenu .rr-epic-submenu__btn {
        min-height: 76px;
        padding: 8px 1px 7px;
    }

    #rrStatsSubmenu .rr-epic-submenu__label {
        font-size: 7px;
        max-width: none;
        line-height: 1.08;
        letter-spacing: 0;
    }

    .rr-epic-submenu__meta {
        display: none;
    }

    .rr-epic-submenu__effect {
        top: 5px;
        width: 46px;
        height: calc(100% - 10px);
    }

    #rrPerfilSubmenu .rr-epic-submenu__track {
        border-radius: 20px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn::after {
        left: 8px;
        right: 8px;
        bottom: 6px;
    }
}

@media (max-width: 380px) {
    .rr-epic-submenu__label {
        font-size: 8px;
        max-width: 46px;
    }

    #rrStatsSubmenu .rr-epic-submenu__label {
        font-size: 6.5px;
        max-width: none;
    }

    .rr-epic-submenu__icon-wrap {
        width: 30px;
        height: 30px;
    }
}

body:not(.is-premium) .rr-epic-submenu {
    --rr-submenu-accent-ui: #ff8a1c;
    --rr-submenu-text-ui: #f8fbff;
    --rr-submenu-track-ui: rgba(8, 12, 20, 0.74);
    --rr-submenu-border-ui: rgba(96, 165, 250, 0.22);
}

body.light:not(.is-premium) .rr-epic-submenu {
    --rr-submenu-accent-ui: #ea580c;
    --rr-submenu-text-ui: #172033;
    --rr-submenu-track-ui: rgba(255, 255, 255, 0.82);
    --rr-submenu-border-ui: rgba(59, 130, 246, 0.22);
}

body:not(.is-premium) .rr-epic-submenu__track {
    background: var(--rr-submenu-track-ui);
    border-color: var(--rr-submenu-border-ui);
    box-shadow:
        0 18px 36px rgba(2, 6, 15, 0.18),
        inset 0 1px 0 rgba(255,255,255,0.06);
    backdrop-filter: blur(16px) saturate(1.12);
}

body:not(.is-premium) .rr-epic-submenu__btn {
    color: var(--rr-submenu-text-ui);
    --submenu-accent: var(--rr-submenu-accent-ui);
}

body:not(.is-premium) .rr-epic-submenu__btn:not(.is-active) {
    opacity: 0.8;
}

body:not(.is-premium) .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
    background: color-mix(in srgb, var(--rr-submenu-track-ui) 82%, rgba(255,255,255,0.05));
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
}

body:not(.is-premium) .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap {
    background: color-mix(in srgb, var(--rr-submenu-accent-ui) 18%, transparent);
    border: 1px solid color-mix(in srgb, var(--rr-submenu-accent-ui) 44%, transparent);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.06),
        0 12px 20px color-mix(in srgb, var(--rr-submenu-accent-ui) 16%, transparent);
}

body.is-premium .rr-epic-submenu__track {
    border-color: rgba(59, 130, 246, 0.34);
    background: linear-gradient(180deg, rgba(6, 18, 36, 0.94) 0%, rgba(11, 28, 54, 0.96) 100%);
    box-shadow:
        0 18px 40px rgba(2, 8, 20, 0.5),
        inset 0 1px 0 rgba(255,255,255,0.05),
        0 0 24px rgba(59, 130, 246, 0.12);
}

body.is-premium .rr-epic-submenu__btn {
    --submenu-accent: #3b82f6;
}

body.is-premium .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap {
    background: rgba(59, 130, 246, 0.16);
    box-shadow: 0 12px 24px rgba(59, 130, 246, 0.22);
}

body.is-premium .rr-epic-submenu__btn.rr-submenu__card--create {
    --submenu-accent: #3b82f6;
}

body.is-premium .rr-epic-submenu__btn.rr-submenu__card--create .rr-epic-submenu__meta {
    color: rgba(125, 195, 255, 0.82);
}
</style>

<script>
(function() {
    var submenu = document.getElementById('{{ $id }}');
    if (!submenu) return;
    
    var track = submenu.querySelector('.rr-epic-submenu__track');
    var effect = submenu.querySelector('[data-effect]');
    var buttons = submenu.querySelectorAll('.rr-epic-submenu__btn');
    
    function updateEffect(activeBtn) {
        if (!effect || !activeBtn || !track) return;
        
        var trackRect = track.getBoundingClientRect();
        var btnRect = activeBtn.getBoundingClientRect();
        
        var effectWidth = Math.max(btnRect.width - 6, 46);
        var leftPos = (btnRect.left - trackRect.left) + ((btnRect.width - effectWidth) / 2);
        
        var accentColor = getComputedStyle(activeBtn).getPropertyValue('--submenu-accent').trim() || '#60a5fa';
        
        effect.style.width = effectWidth + 'px';
        effect.style.left = leftPos + 'px';
        effect.style.setProperty('--submenu-active-color', accentColor);
        
        if (!effect.classList.contains('is-ready')) {
            setTimeout(function() { effect.classList.add('is-ready'); }, 50);
        }
    }
    
    function setActive(btn) {
        buttons.forEach(function(b) {
            b.classList.remove('is-active');
            b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('is-active');
        btn.setAttribute('aria-selected', 'true');
        updateEffect(btn);
    }
    
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            setActive(btn);
        });
    });

    var activeObserver = new MutationObserver(function() {
        var active = submenu.querySelector('.rr-epic-submenu__btn.is-active');
        if (active) {
            updateEffect(active);
        }
    });

    buttons.forEach(function(btn) {
        activeObserver.observe(btn, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    });
    
    // Initial position
    var activeBtn = submenu.querySelector('.rr-epic-submenu__btn.is-active');
    if (activeBtn) {
        setTimeout(function() { updateEffect(activeBtn); }, 100);
    }
    
    // Recalculate on resize
    var resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            var active = submenu.querySelector('.rr-epic-submenu__btn.is-active');
            if (active) updateEffect(active);
        }, 100);
    });
})();
</script>
