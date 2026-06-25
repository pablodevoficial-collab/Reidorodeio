document.addEventListener('DOMContentLoaded', () => {
    const arenaEntry = document.querySelector('[data-arena-entry]');
    const loaderStatus = document.querySelector('[data-loader-status]');
    const arenaApp = document.querySelector('[data-arena-app]');
    const authModal = document.querySelector('[data-auth-modal]');
    const openRegister = document.querySelector('[data-open-register]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');
    const authChoicePanel = document.querySelector('[data-auth-panel="choice"]');
    const authChoiceFeedback = document.querySelector('[data-auth-choice-feedback]');
    const loginForm = document.querySelector('[data-login-form]');
    const registerForm = document.querySelector('[data-register-form]');
    const profileForm = document.querySelector('[data-profile-form]');
    const loginFeedback = document.querySelector('[data-login-feedback]');
    const registerFeedback = document.querySelector('[data-register-feedback]');
    const profileFeedback = document.querySelector('[data-profile-feedback]');
    const registerMobilePanel = document.querySelector('[data-step-panel="mobile"]');
    const registerPasswordPanel = document.querySelector('[data-step-panel="password"]');
    const profileCpfPanel = document.querySelector('[data-profile-panel="cpf"]');
    const profileNamePanel = document.querySelector('[data-profile-panel="name"]');
    const profileBirthdatePanel = document.querySelector('[data-profile-panel="birthdate"]');
    const checkMobileButton = document.querySelector('[data-check-mobile]');
    const checkCpfButton = document.querySelector('[data-check-cpf]');
    const nextProfileButton = document.querySelector('[data-next-profile]');
    const authActionButtons = document.querySelectorAll('[data-auth-action]');
    const authBackButtons = document.querySelectorAll('[data-auth-back]');

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

    const toggleLoading = (button, loading) => {
        if (!button) return;
        button.classList.toggle('is-loading', loading);
    };

    const showPanel = (node, display = 'grid') => {
        if (!node) return;
        node.style.display = display;
    };

    const hidePanel = (node) => {
        if (!node) return;
        node.style.display = 'none';
    };

    const resetWizard = () => {
        loginForm?.reset();
        registerForm?.reset();

        if (profileForm) {
            profileForm.reset();
            hidePanel(profileForm);
        }

        showPanel(authChoicePanel);
        hidePanel(loginForm);
        hidePanel(registerForm);
        showPanel(registerMobilePanel);
        hidePanel(registerPasswordPanel);
        showPanel(profileCpfPanel);
        hidePanel(profileNamePanel);
        hidePanel(profileBirthdatePanel);

        showFeedback(authChoiceFeedback, '', '');
        showFeedback(loginFeedback, '', '');
        showFeedback(registerFeedback, '', '');
        showFeedback(profileFeedback, '', '');
    };

    const switchAuthFlow = (mode) => {
        hidePanel(authChoicePanel);
        hidePanel(loginForm);
        hidePanel(registerForm);
        hidePanel(profileForm);

        if (mode === 'login') {
            showPanel(loginForm);
            loginForm?.querySelector('input[name="identifier"]')?.focus();
            return;
        }

        if (mode === 'register') {
            showPanel(registerForm);
            showPanel(registerMobilePanel);
            hidePanel(registerPasswordPanel);
            registerForm?.querySelector('input[name="mobile"]')?.focus();
            return;
        }

        showPanel(authChoicePanel);
    };

    const openModal = () => {
        if (!authModal) return;
        resetWizard();
        authModal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        if (!authModal) return;
        authModal.setAttribute('hidden', 'hidden');
        document.body.style.overflow = '';
    };

    openRegister?.addEventListener('click', openModal);
    closeModalButtons.forEach((button) => button.addEventListener('click', closeModal));

    authModal?.addEventListener('click', (event) => {
        if (event.target === authModal) {
            closeModal();
        }
    });

    authActionButtons.forEach((button) => {
        button.addEventListener('click', () => switchAuthFlow(button.dataset.authAction));
    });

    authBackButtons.forEach((button) => {
        button.addEventListener('click', resetWizard);
    });

    loginForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const identifier = digits(loginForm.identifier.value);
        const password = loginForm.password.value;

        if (!identifier) {
            showFeedback(loginFeedback, 'Informe seu CPF ou WhatsApp.', 'error');
            return;
        }

        showFeedback(loginFeedback, 'Entrando na arena...', 'success');
        const submitButton = loginForm.querySelector('button[type="submit"]');
        toggleLoading(submitButton, true);

        try {
            const response = await fetch(arenaApp?.dataset.loginUrl || '/user/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cpf: identifier, password })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error((data.errors || ['Não foi possível entrar.']).join(' '));
            }

            showFeedback(loginFeedback, data.message || 'Login realizado com sucesso.', 'success');
            window.setTimeout(() => {
                window.location.href = data.redirect_url || window.location.href;
            }, 400);
        } catch (error) {
            showFeedback(loginFeedback, error.message, 'error');
        } finally {
            toggleLoading(submitButton, false);
        }
    });

    checkMobileButton?.addEventListener('click', async () => {
        const mobile = digits(registerForm.mobile.value);
        showFeedback(registerFeedback, 'Verificando WhatsApp...', 'success');
        toggleLoading(checkMobileButton, true);

        try {
            const checkResponse = await fetch(arenaApp?.dataset.checkUserUrl || '/user/check-user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ field: 'mobile', value: mobile })
            });
            const checkData = await checkResponse.json();

            if (!checkData.available) {
                showFeedback(registerFeedback, checkData.message, 'error');
                return;
            }

            showFeedback(registerFeedback, 'WhatsApp liberado. Agora crie sua senha.', 'success');
            hidePanel(registerMobilePanel);
            showPanel(registerPasswordPanel);
        } catch (error) {
            showFeedback(registerFeedback, 'Não foi possível validar seu WhatsApp agora.', 'error');
        } finally {
            toggleLoading(checkMobileButton, false);
        }
    });

    registerForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        showFeedback(registerFeedback, 'Criando conta...', 'success');

        const mobile = digits(registerForm.mobile.value);
        const password = registerForm.password.value;
        const passwordConfirmation = registerForm.password_confirmation.value;
        const submitButton = registerForm.querySelector('button[type="submit"]');
        toggleLoading(submitButton, true);

        try {
            const registerResponse = await fetch(arenaApp?.dataset.registerUrl || '/user/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mobile, password, password_confirmation: passwordConfirmation })
            });
            const registerData = await registerResponse.json();

            if (!registerResponse.ok || !registerData.success) {
                throw new Error((registerData.errors || ['Não foi possível cadastrar.']).join(' '));
            }

            showFeedback(registerFeedback, 'Conta criada. Falta completar o perfil.', 'success');
            hidePanel(registerForm);
            showPanel(profileForm);
        } catch (error) {
            showFeedback(registerFeedback, error.message, 'error');
        } finally {
            toggleLoading(submitButton, false);
        }
    });

    checkCpfButton?.addEventListener('click', async () => {
        const cpf = digits(profileForm.cpf.value);
        showFeedback(profileFeedback, 'Verificando CPF...', 'success');
        toggleLoading(checkCpfButton, true);

        try {
            const cpfCheckResponse = await fetch(arenaApp?.dataset.checkUserUrl || '/user/check-user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ field: 'cpf', value: cpf })
            });
            const cpfCheckData = await cpfCheckResponse.json();

            if (!cpfCheckData.available) {
                showFeedback(profileFeedback, cpfCheckData.message, 'error');
                return;
            }

            showFeedback(profileFeedback, 'CPF liberado. Agora informe seu nome.', 'success');
            hidePanel(profileCpfPanel);
            showPanel(profileNamePanel);
        } catch (error) {
            showFeedback(profileFeedback, 'Não foi possível validar seu CPF agora.', 'error');
        } finally {
            toggleLoading(checkCpfButton, false);
        }
    });

    nextProfileButton?.addEventListener('click', () => {
        const fullName = profileForm.fullname.value.trim();
        if (!fullName || fullName.split(/\s+/).length < 2) {
            showFeedback(profileFeedback, 'Informe nome e sobrenome para continuar.', 'error');
            return;
        }

        showFeedback(profileFeedback, 'Perfeito. Falta a data de nascimento.', 'success');
        hidePanel(profileNamePanel);
        showPanel(profileBirthdatePanel);
    });

    profileForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        showFeedback(profileFeedback, 'Finalizando perfil...', 'success');

        const cpf = digits(profileForm.cpf.value);
        const names = splitName(profileForm.fullname.value);
        const submitButton = profileForm.querySelector('button[type="submit"]');
        toggleLoading(submitButton, true);

        try {
            const profileResponse = await fetch(arenaApp?.dataset.profileUrl || '/user/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    cpf,
                    firstname: names.firstname,
                    lastname: names.lastname,
                    birthdate: profileForm.birthdate.value
                })
            });
            const profileData = await profileResponse.json();

            if (!profileResponse.ok || !profileData.success) {
                throw new Error((profileData.errors || ['Não foi possível completar o perfil.']).join(' '));
            }

            showFeedback(profileFeedback, 'Perfil completo. Você receberá avisos dos novos eventos.', 'success');
            window.setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            showFeedback(profileFeedback, error.message, 'error');
        } finally {
            toggleLoading(submitButton, false);
        }
    });

    if (!arenaEntry || !loaderStatus) {
        return;
    }

    const statusMessages = [
        'Preparando a arena do bolão...',
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
