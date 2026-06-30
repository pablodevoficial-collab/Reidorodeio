document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  const form = document.querySelector('[data-profile-form-sheet]');
  if (!app || !form || app.dataset.authenticated !== 'true') return;

  const feedback = document.querySelector('[data-profile-sheet-feedback]');
  const pixTitle = document.querySelector('[data-pix-status-title]');
  const pixCopy = document.querySelector('[data-pix-status-copy]');
  const avatarInput = form.querySelector('input[name="avatar"]');
  const avatarPreview = document.querySelector('[data-profile-photo-preview]');
  const greeting = document.querySelector('[data-profile-greeting]');
  const title = document.querySelector('[data-profile-title]');
  const subtitle = document.querySelector('[data-profile-subtitle]');
  const summary = document.querySelector('[data-profile-summary]');
  const totalWon = document.querySelector('[data-profile-total-won]');
  const completeFields = document.querySelector('[data-profile-complete-fields]');
  const submitButton = document.querySelector('[data-profile-submit]');
  let profileComplete = false;

  const show = (text, cls = '') => {
    if (!feedback) return;
    feedback.textContent = text || '';
    feedback.className = `rr-form-step__feedback ${cls}`.trim();
  };
  const digits = (value) => (value || '').replace(/\D+/g, '');
  const splitName = (fullName) => {
    const parts = (fullName || '').trim().split(/\s+/).filter(Boolean);
    return {
      firstname: parts[0] || '',
      lastname: parts.slice(1).join(' ') || '',
    };
  };
  const money = (value) => new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(Number(value || 0));

  const apply = (user) => {
    const fullName = [user.firstname, user.lastname].filter(Boolean).join(' ').trim();
    const firstName = user.firstname || fullName.split(/\s+/)[0] || user.username || 'competidor';
    profileComplete = Boolean(user.profile_complete);

    form.cpf.value = user.cpf || '';
    form.fullname.value = fullName;
    form.pix_key.value = user.pix_key || '';

    if (greeting) greeting.textContent = `Ola, ${firstName}`;
    if (title) title.textContent = profileComplete ? 'Meu perfil' : 'Receber premio';
    if (subtitle) {
      subtitle.textContent = profileComplete
        ? 'Edite sua chave Pix e envie uma nova foto quando quiser.'
        : 'Complete seus dados para receber premiacoes.';
    }
    if (summary) summary.hidden = !profileComplete;
    if (totalWon) totalWon.textContent = money(user.winnings?.total || 0);
    if (completeFields) completeFields.hidden = profileComplete;
    if (submitButton) submitButton.textContent = profileComplete ? 'Salvar Pix e foto' : 'Salvar dados do premio';

    form.cpf.required = !profileComplete;
    form.fullname.required = !profileComplete;

    if (avatarPreview && user.avatar_url) {
      avatarPreview.innerHTML = `<img src="${user.avatar_url}" alt="">`;
    }
    if (pixTitle) pixTitle.textContent = user.pix_key ? 'Pix pronto para premiacao' : 'Cadastre sua chave Pix';
    if (pixCopy) pixCopy.textContent = user.pix_key ? `${user.pix_key_type || 'pix'}: ${user.pix_key}` : 'Sem chave Pix cadastrada no perfil.';
  };

  avatarInput?.addEventListener('change', () => {
    const file = avatarInput.files?.[0];
    if (!file || !avatarPreview) return;

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      avatarInput.value = '';
      show('Envie uma foto JPG, PNG ou WEBP.', 'is-error');
      return;
    }

    avatarPreview.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="">`;
  });

  async function loadProfile() {
    try {
      const response = await fetch(app.dataset.profileApiUrl, { headers: { Accept: 'application/json' } });
      const data = await response.json();
      if (response.ok && data.user) apply(data.user);
    } catch (error) {
      show('Nao foi possivel carregar seu perfil.', 'is-error');
    }
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const cpf = digits(form.cpf.value);
    const names = splitName(form.fullname.value);

    if (!profileComplete && cpf.length !== 11) {
      show('Informe um CPF valido com 11 digitos.', 'is-error');
      return;
    }

    if (!profileComplete && (!names.firstname || !names.lastname)) {
      show('Informe nome completo para receber o premio.', 'is-error');
      return;
    }

    if (!form.pix_key.value.trim()) {
      show('Informe sua chave Pix.', 'is-error');
      return;
    }

    show(profileComplete ? 'Salvando perfil...' : 'Salvando dados do premio...');
    const payload = new FormData();
    payload.append('cpf', cpf);
    payload.append('firstname', names.firstname);
    payload.append('lastname', names.lastname);
    payload.append('pix_key', form.pix_key.value.trim());
    if (avatarInput?.files?.[0]) {
      payload.append('avatar', avatarInput.files[0]);
    }

    try {
      const response = await fetch(app.dataset.profileApiUrl, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: payload
      });
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.message || 'Nao foi possivel salvar.');
      avatarInput.value = '';
      apply(data.user || {});
      show(data.message || 'Perfil salvo.', 'is-success');
    } catch (error) {
      show(error.message, 'is-error');
    }
  });

  loadProfile();
});
