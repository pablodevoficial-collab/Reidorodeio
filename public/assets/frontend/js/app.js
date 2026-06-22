document.addEventListener('DOMContentLoaded', () => {
    const arenaEntry = document.querySelector('[data-arena-entry]');
    const loaderStatus = document.querySelector('[data-loader-status]');
    const arenaHero = document.querySelector('.arena-hero');
    const authModal = document.querySelector('[data-auth-modal]');
    const openRegister = document.querySelector('[data-open-register]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');
    const registerForm = document.querySelector('[data-register-form]');
    const profileForm = document.querySelector('[data-profile-form]');
    const registerFeedback = document.querySelector('[data-register-feedback]');
    const profileFeedback = document.querySelector('[data-profile-feedback]');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const showFeedback = (node, message, type) => {
        if (!node) return;
        node.textContent = message || '';
        node.className = `rr-form-step__feedback ${type ? `is-${type}` : ''}`.trim();
    };

    const digits = (value) => (value || '').replace(/\D+/g, '');
    const splitName = (fullName) => {
        const parts = (fullName || '').trim().split(/\s+/).filter(Boolean);
        return {
            firstname: parts[0] || '',
            lastname: parts.slice(1).join(' ') || '',
        };
    };

    const openModal = () => authModal?.removeAttribute('hidden');
    const closeModal = () => authModal?.setAttribute('hidden', 'hidden');

    openRegister?.addEventListener('click', openModal);
    closeModalButtons.forEach((button) => button.addEventListener('click', closeModal));

    registerForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        showFeedback(registerFeedback, 'Verificando dados...', 'success');

        const mobile = digits(registerForm.mobile.value);
        const password = registerForm.password.value;
        const passwordConfirmation = registerForm.password_confirmation.value;

        const checkResponse = await fetch(arenaHero.dataset.checkUserUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ field: 'mobile', value: mobile }),
        });
        const checkData = await checkResponse.json();

        if (!checkData.available) {
            showFeedback(registerFeedback, checkData.message, 'error');
            return;
        }

        const registerResponse = await fetch(arenaHero.dataset.registerUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ mobile, password, password_confirmation: passwordConfirmation }),
        });
        const registerData = await registerResponse.json();

        if (!registerResponse.ok || !registerData.success) {
            showFeedback(registerFeedback, (registerData.errors || ['Nao foi possivel cadastrar.']).join(' '), 'error');
            return;
        }

        showFeedback(registerFeedback, 'Conta criada. Falta completar o perfil.', 'success');
        registerForm.hidden = true;
        profileForm.hidden = false;
    });

    profileForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        showFeedback(profileFeedback, 'Validando perfil...', 'success');

        const cpf = digits(profileForm.cpf.value);
        const cpfCheckResponse = await fetch(arenaHero.dataset.checkUserUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ field: 'cpf', value: cpf }),
        });
        const cpfCheckData = await cpfCheckResponse.json();

        if (!cpfCheckData.available) {
            showFeedback(profileFeedback, cpfCheckData.message, 'error');
            return;
        }

        const names = splitName(profileForm.fullname.value);
        const profileResponse = await fetch(arenaHero.dataset.profileUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                cpf,
                firstname: names.firstname,
                lastname: names.lastname,
                birthdate: profileForm.birthdate.value,
            }),
        });
        const profileData = await profileResponse.json();

        if (!profileResponse.ok || !profileData.success) {
            showFeedback(profileFeedback, (profileData.errors || ['Nao foi possivel completar o perfil.']).join(' '), 'error');
            return;
        }

        showFeedback(profileFeedback, 'Perfil completo. Voce recebera avisos dos novos eventos.', 'success');
        window.setTimeout(() => window.location.reload(), 900);
    });

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
