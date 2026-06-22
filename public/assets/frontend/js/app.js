document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('[data-scroll-target]');

    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            const targetId = link.getAttribute('data-scroll-target');
            const target = targetId ? document.getElementById(targetId) : null;

            if (!target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    const header = document.querySelector('.topbar');

    if (!header) {
        return;
    }

    const syncScrolledState = () => {
        header.classList.toggle('topbar--scrolled', window.scrollY > 14);
    };

    syncScrolledState();
    window.addEventListener('scroll', syncScrolledState, { passive: true });
});
