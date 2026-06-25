document.addEventListener('DOMContentLoaded', () => {
    const arenaState = document.querySelector('[data-arena-state]');
    if (!arenaState || arenaState.dataset.hasEvent === 'true') {
        return;
    }

    const statusUrl = arenaState.dataset.arenaStatusUrl;
    if (!statusUrl) {
        return;
    }

    let checking = false;

    const checkArenaStatus = async () => {
        if (checking) {
            return;
        }

        checking = true;

        try {
            const response = await fetch(statusUrl, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();

            if (response.ok && data.has_event) {
                window.location.reload();
            }
        } catch (error) {
            console.error('Arena status check failed.', error);
        } finally {
            checking = false;
        }
    };

    window.setInterval(checkArenaStatus, 15000);
});
