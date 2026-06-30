document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  const form = document.querySelector('[data-profile-form-sheet]');
  if (!app || !form || app.dataset.authenticated !== 'true') return;

  const feedback = document.querySelector('[data-profile-sheet-feedback]');
  const pixTitle = document.querySelector('[data-pix-status-title]');
  const pixCopy = document.querySelector('[data-pix-status-copy]');
  const avatarInput = form.querySelector('input[name="avatar"]');
  const avatarPreview = document.querySelector('[data-profile-photo-preview]');
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

  const apply = (user) => {
    const fullName = [user.firstname, user.lastname].filter(Boolean).join(' ').trim();
    form.cpf.value = user.cpf || '';
    form.fullname.value = fullName;
    form.pix_key.value = user.pix_key || '';
    if (avatarPreview && user.avatar_url) {
      avatarPreview.innerHTML = `<img src="${user.avatar_url}" alt="">`;
    }
    if (pixTitle) pixTitle.textContent = user.pix_key ? 'Pix pronto para premiação' : 'Cadastre sua chave Pix';
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
      show('Não foi possível carregar seu perfil.', 'is-error');
    }
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const cpf = digits(form.cpf.value);
    const names = splitName(form.fullname.value);

    if (cpf.length !== 11) {
      show('Informe um CPF válido com 11 dígitos.', 'is-error');
      return;
    }

    if (!names.firstname || !names.lastname) {
      show('Informe nome completo para receber o prêmio.', 'is-error');
      return;
    }

    show('Salvando dados do prêmio...');
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
      if (!response.ok || !data.ok) throw new Error(data.message || 'Não foi possível salvar.');
      apply(data.user || {});
      show(data.message || 'Dados do prêmio salvos.', 'is-success');
    } catch (error) {
      show(error.message, 'is-error');
    }
  });

  loadProfile();
});
