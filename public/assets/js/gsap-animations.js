/**
 * GSAP Animation Utilities
 * Helper functions for common animation patterns
 */

const GsapAnimations = {
    /**
     * Animate element entrance from bottom to top
     */
    fadeUpEntry(element, delay = 0) {
        return gsap.fromTo(element,
            { opacity: 0, y: 40 },
            { opacity: 1, y: 0, duration: 0.8, delay, ease: "power2.out" }
        );
    },

    /**
     * Animate element entrance from left to right
     */
    slideInFromLeft(element, delay = 0) {
        return gsap.fromTo(element,
            { opacity: 0, x: -50 },
            { opacity: 1, x: 0, duration: 0.8, delay, ease: "power2.out" }
        );
    },

    /**
     * Animate element entrance from right to left
     */
    slideInFromRight(element, delay = 0) {
        return gsap.fromTo(element,
            { opacity: 0, x: 50 },
            { opacity: 1, x: 0, duration: 0.8, delay, ease: "power2.out" }
        );
    },

    /**
     * Stagger animation for multiple elements
     */
    staggerElements(elements, duration = 0.8, stagger = 0.1) {
        return gsap.fromTo(elements,
            { opacity: 0, y: 30 },
            {
                opacity: 1,
                y: 0,
                duration,
                stagger,
                ease: "power2.out"
            }
        );
    },

    /**
     * Pulse animation
     */
    pulse(element, times = 3) {
        return gsap.to(element, {
            scale: 1.1,
            opacity: 0.8,
            duration: 0.4,
            repeat: times * 2 - 1,
            yoyo: true,
            ease: "sine.inOut"
        });
    },

    /**
     * Shake animation (for errors)
     */
    shake(element, intensity = 10) {
        return gsap.to(element, {
            x: (i) => {
                return i % 2 === 0 ? intensity : -intensity;
            },
            duration: 0.1,
            repeat: 5,
            ease: "power1.inOut",
            onComplete() {
                gsap.set(element, { x: 0 });
            }
        });
    },

    /**
     * Scale bounce effect
     */
    bounce(element) {
        return gsap.to(element, {
            scale: 1.15,
            duration: 0.4,
            ease: "back.out",
            yoyo: true,
            repeat: 1
        });
    },

    /**
     * Glow animation
     */
    glow(element, color = 'rgba(249, 115, 22, 0.8)', duration = 1) {
        return gsap.to(element, {
            boxShadow: `0 0 30px ${color}`,
            duration,
            yoyo: true,
            repeat: -1,
            ease: "sine.inOut"
        });
    },

    /**
     * Count up animation (for stats)
     */
    countUp(element, from = 0, to = 100, duration = 2) {
        const obj = { value: from };
        return gsap.to(obj, {
            value: to,
            duration,
            ease: "power2.out",
            onUpdate() {
                element.textContent = Math.round(obj.value);
            }
        });
    },

    /**
     * Smooth scroll to element
     */
    scrollToElement(element, offsetTop = 100) {
        const elementPosition = element.getBoundingClientRect().top + window.pageYOffset - offsetTop;
        return gsap.to(window, {
            scrollTo: elementPosition,
            duration: 1,
            ease: "power2.inOut"
        });
    },

    /**
     * Rotate animation
     */
    rotate(element, degrees = 360, duration = 1) {
        return gsap.to(element, {
            rotation: degrees,
            duration,
            ease: "power2.inOut"
        });
    },

    /**
     * Flip animation (3D)
     */
    flip(element, duration = 0.6) {
        return gsap.to(element, {
            rotationY: 180,
            duration,
            transformPerspective: 1000,
            ease: "back.inOut"
        });
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GsapAnimations;
}
