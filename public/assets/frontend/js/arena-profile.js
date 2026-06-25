document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  const form = document.querySelector('[data-profile-form-sheet]');
  if (!app || !form || app.dataset.authenticated !== 'true') return;
  const feedback = document.querySelector('[data-profile-sheet-feedback]');
  const pixTitle = document.querySelector('[data-pix-status-title]');
  const pixCopy = document.querySelector('[data-pix-status-copy]');
  const birth = form.querySelector('input[name="birth_date"]');
  const show = (text, cls = '') => { if (!feedback) return; feedback.textContent = text || ''; feedback.className = `rr-form-step__feedback ${cls}`.trim(); };
  const digits = (v) => (v || '').replace(/\D+/g, '').slice(0, 8);
  const formatBirth = (v) => digits(v).replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');
  const isoBirth = (v) => {
    const m = String(v || '').match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    return m ? `${m[3]}-${m[2]}-${m[1]}` : v;
  };

  birth?.addEventListener('input', () => { birth.value = formatBirth(birth.value); });

  const apply = (user) => {
    form.username.value = user.username || '';
    form.email.value = user.email || '';
    form.whatsapp.value = user.mobile || '';
    form.birth_date.value = user.birth_date ? formatBirth(user.birth_date.split('-').reverse().join('')) : '';
    form.pix_key.value = user.pix_key || '';
    if (pixTitle) pixTitle.textContent = user.pix_key ? 'Pix pronto para premiacao' : 'Cadastre sua chave Pix';
    if (pixCopy) pixCopy.textContent = user.pix_key ? `${user.pix_key_type || 'pix'}: ${user.pix_key}` : 'Sem chave Pix cadastrada no perfil.';
  };

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
    show('Salvando perfil...');
    const payload = new FormData();
    payload.append('username', form.username.value.trim());
    payload.append('email', form.email.value.trim());
    payload.append('whatsapp', form.whatsapp.value.trim());
    payload.append('birth_date', isoBirth(form.birth_date.value.trim()));
    payload.append('pix_key', form.pix_key.value.trim());
    try {
      const response = await fetch(app.dataset.profileApiUrl, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: payload
      });
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.message || 'Nao foi possivel salvar.');
      apply(data.user || {});
      show(data.message || 'Perfil salvo com sucesso.', 'is-success');
    } catch (error) {
      show(error.message, 'is-error');
    }
  });

  loadProfile();
});
