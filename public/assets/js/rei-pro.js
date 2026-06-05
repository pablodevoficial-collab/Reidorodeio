(function () {
    'use strict';

    const doc = document;
    const root = doc.documentElement;
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isMobile = window.matchMedia && window.matchMedia('(max-width: 767px)').matches;

    function ready(callback) {
        if (doc.readyState === 'loading') {
            doc.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }
        callback();
    }

    function qs(selector, context = doc) {
        return context.querySelector(selector);
    }

    function qsa(selector, context = doc) {
        return Array.from(context.querySelectorAll(selector));
    }

    const pressSelector = [
        'button',
        '[role="button"]',
        'a.rr-btn',
        '.rr-btn',
        '.rr-entry-card',
        '.rr-card__btn',
        '.rr-hero__btn',
        '.rr-side__nav-btn',
        '.rr-mobile-actions__btn',
        '.rr-mobile-footer__btn',
        '.rr-arena-back',
        '.rr-choice__btn'
    ].join(', ');

    function ensureToastStack() {
        let stack = qs('.rr-pro-toast-stack');
        if (!stack) {
            stack = doc.createElement('div');
            stack.className = 'rr-pro-toast-stack';
            stack.setAttribute('aria-live', 'polite');
            stack.setAttribute('aria-atomic', 'true');
            doc.body.appendChild(stack);
        }
        return stack;
    }

    function toast(title, message) {
        const stack = ensureToastStack();
        const item = doc.createElement('div');
        item.className = 'rr-pro-toast';
        item.innerHTML = '<i class="fas fa-bolt"></i><div><strong></strong><span></span></div>';
        item.querySelector('strong').textContent = title;
        item.querySelector('span').textContent = message;
        stack.appendChild(item);

        window.setTimeout(() => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(8px)';
            window.setTimeout(() => item.remove(), 260);
        }, 3200);
    }

    function addRipple(event) {
        const target = event.currentTarget;
        if (!target || target.disabled || reduceMotion) return;

        const rect = target.getBoundingClientRect();
        const ripple = doc.createElement('span');
        ripple.className = 'rr-pro-ripple';
        ripple.style.left = `${event.clientX - rect.left}px`;
        ripple.style.top = `${event.clientY - rect.top}px`;
        target.appendChild(ripple);
        window.setTimeout(() => ripple.remove(), 720);
    }

    function bindRipples(context = doc) {
        if (isMobile) return;

        qsa('[data-arena-target], .rr-arena-card__button, .rr-btn, .rr-entry-card, .rr-hero__btn, .rr-card__btn, .rr-side__nav-btn, .rr-mobile-actions__btn, .rr-choice__btn', context)
            .forEach((button) => {
                if (button.dataset.rrProRippleBound === '1') return;
                button.dataset.rrProRippleBound = '1';
                button.addEventListener('pointerdown', addRipple);
            });
    }

    function bindNativePress(context = doc) {
        qsa(pressSelector, context).forEach((button) => {
            if (button.dataset.rrProPressBound === '1') return;
            button.dataset.rrProPressBound = '1';

            const clear = () => button.classList.remove('is-pressed');

            button.addEventListener('pointerdown', (event) => {
                if (button.disabled || button.getAttribute('aria-disabled') === 'true') return;
                if (event.pointerType === 'mouse' && event.button !== 0) return;
                button.classList.add('is-pressed');
            });

            button.addEventListener('pointerup', clear);
            button.addEventListener('pointercancel', clear);
            button.addEventListener('pointerleave', clear);
            button.addEventListener('blur', clear);
        });
    }

    function initTilt(context = doc) {
        if (isMobile || reduceMotion || !window.VanillaTilt) return;

        qsa('.rr-arena-card, .rr-card:not(.rr-card--placeholder)', context).forEach((card) => {
            if (card.dataset.rrProTiltBound === '1') return;
            card.dataset.rrProTiltBound = '1';
            window.VanillaTilt.init(card, {
                max: card.classList.contains('rr-arena-card') ? 7 : 3,
                speed: 650,
                glare: true,
                'max-glare': card.classList.contains('rr-arena-card') ? 0.16 : 0.08,
                scale: 1.01
            });
        });
    }

    function initLucide() {
        if (isMobile) return;

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons({
                attrs: {
                    'stroke-width': 2.2
                }
            });
        }
    }

    function celebrate(source) {
        return;
    }

    function bindCelebrations(context = doc) {
        qsa('[data-arena-target="bolao"], .rr-card__btn--enter, .rr-mobile-actions__pix', context).forEach((button) => {
            if (button.dataset.rrProCelebrateBound === '1') return;
            button.dataset.rrProCelebrateBound = '1';
            button.addEventListener('click', () => {
                if (!button.disabled) celebrate(button);
            });
        });
    }

    function initSwiper() {
        if (isMobile) return;

        if (!window.Swiper) return;

        qsa('[data-rr-swiper]').forEach((node) => {
            if (node.dataset.rrProSwiperBound === '1') return;
            node.dataset.rrProSwiperBound = '1';
            new window.Swiper(node, {
                slidesPerView: 1,
                spaceBetween: 14,
                grabCursor: true,
                keyboard: { enabled: true },
                pagination: {
                    el: node.querySelector('.swiper-pagination'),
                    clickable: true
                },
                navigation: {
                    nextEl: node.querySelector('.swiper-button-next'),
                    prevEl: node.querySelector('.swiper-button-prev')
                },
                breakpoints: {
                    768: { slidesPerView: 2 },
                    1100: { slidesPerView: 3 }
                }
            });
        });
    }

    function initScrollCue() {
        if (isMobile) return;

        let cue = qs('.rr-pro-scroll-cue');
        if (!cue) {
            cue = doc.createElement('div');
            cue.className = 'rr-pro-scroll-cue';
            doc.body.appendChild(cue);
        }

        const update = () => {
            const height = root.scrollHeight - window.innerHeight;
            const progress = height > 0 ? Math.min(100, Math.max(0, (window.scrollY / height) * 100)) : 0;
            root.style.setProperty('--rr-pro-scroll', `${progress}%`);
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    }

    function upgradeDynamicContent(context = doc) {
        bindNativePress(context);
        bindRipples(context);
        bindCelebrations(context);
        initTilt(context);
        initLucide();
    }

    function observeCards() {
        const grid = qs('#rrCardsGrid');
        if (!grid || !window.MutationObserver) return;

        const observer = new MutationObserver(() => {
            upgradeDynamicContent(grid);
        });

        observer.observe(grid, { childList: true, subtree: true });
    }

    ready(() => {
        doc.body.classList.add('rr-pro-ready');
        initScrollCue();
        initSwiper();
        upgradeDynamicContent();
        observeCards();

        doc.addEventListener('rr:cards-rendered', (event) => upgradeDynamicContent(event.target || doc));
        doc.addEventListener('rr:pro-toast', (event) => {
            const detail = event.detail || {};
            toast(detail.title || 'Rei do Rodeio', detail.message || 'Ação concluída.');
        });

        window.ReiPro = {
            celebrate,
            toast,
            refresh: upgradeDynamicContent
        };
    });
})();
