document.addEventListener('DOMContentLoaded', () => {
    const arenaEntry = document.querySelector('[data-arena-entry]');
    const loaderStatus = document.querySelector('[data-loader-status]');

    if (!arenaEntry || !loaderStatus) {
        return;
    }

    const statusMessages = [
        'Preparando a arena do bolao...',
        'Ajustando luz, clima e entrada...',
        'Tudo pronto. Pode entrar.'
    ];

    window.setTimeout(() => {
        loaderStatus.textContent = statusMessages[1];
    }, 1100);

    window.setTimeout(() => {
        loaderStatus.textContent = statusMessages[2];
        arenaEntry.classList.remove('is-locked');
        arenaEntry.classList.add('is-ready');
        arenaEntry.removeAttribute('aria-disabled');
    }, 3000);
});
