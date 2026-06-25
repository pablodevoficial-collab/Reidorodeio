document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  const modal = document.querySelector('[data-entry-modal]');
  if (!app || !modal) return;

  const state = { league: null, all: [], selected: [], poller: null, pixCode: '', profileComplete: true };
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const fallback = '/public/assets/images/logo/logorei.png';
  const title = modal.querySelector('[data-entry-title]');
  const subtitle = modal.querySelector('[data-entry-subtitle]');
  const search = modal.querySelector('[data-entry-search]');
  const slots = modal.querySelector('[data-entry-slots]');
  const list = modal.querySelector('[data-entry-list]');
  const pay = modal.querySelector('[data-entry-pay]');
  const feedback = modal.querySelector('[data-entry-feedback]');
  const paymentFeedback = modal.querySelector('[data-entry-payment-feedback]');
  const qrImage = modal.querySelector('[data-entry-qr-image]');
  const qrText = modal.querySelector('[data-entry-qr-text]');
  const completeProfile = modal.querySelector('[data-entry-complete-profile]');
  const profileModal = document.querySelector('[data-profile-modal]');
  const registerTrigger = document.querySelector('[data-open-register]');
  const stages = [...modal.querySelectorAll('[data-entry-stage]')];
  const openProfile = () => {
    if (!profileModal) return;
    profileModal.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
  };
  const showStage = (name) => stages.forEach((item) => item.hidden = item.dataset.entryStage !== name);
  const showText = (node, text, cls = '') => {
    if (!node) return;
    node.textContent = text || '';
    node.className = `arena-entry__feedback ${cls}`.trim();
  };
  const api = (path) => `${app.dataset.fantasyBaseUrl}${path}`;
  const jsonFetch = async (url, options = {}) => {
    const response = await fetch(url, { headers: { Accept: 'application/json', ...options.headers }, ...options });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || data.success === false || data.ok === false) throw new Error(data.message || 'Não foi possível concluir esta etapa.');
    return data;
  };

  const close = () => {
    modal.setAttribute('hidden', 'hidden');
    document.body.style.overflow = '';
    window.clearInterval(state.poller);
    state.poller = null;
  };

  const renderSlots = () => {
    slots.innerHTML = Array.from({ length: 4 }, (_, index) => {
      const item = state.selected[index];
      if (!item) return `<button class="arena-entry__slot" type="button" data-remove-slot="${index}"><strong>Vaga ${index + 1}</strong><span>Escolha um competidor</span></button>`;
      return `<button class="arena-entry__slot is-filled" type="button" data-remove-slot="${index}"><img src="${item.foto_url || fallback}" alt="${item.nome}"><strong>${item.nome}</strong><span>Toque para remover</span></button>`;
    }).join('');
    pay.disabled = state.selected.length !== 4;
    pay.querySelector('span').textContent = state.selected.length === 4 ? 'Pagar e entrar' : `Escolha ${4 - state.selected.length} competidores`;
    slots.querySelectorAll('[data-remove-slot]').forEach((button) => button.addEventListener('click', () => {
      const removed = state.selected[Number(button.dataset.removeSlot)];
      if (!removed) return;
      state.selected = state.selected.filter((item) => item.id !== removed.id);
      renderSlots();
      renderList(search.value);
    }));
  };

  const renderList = (term = '') => {
    const query = term.trim().toLowerCase();
    const filtered = state.all.filter((item) => !query || item.nome.toLowerCase().includes(query));
    list.innerHTML = filtered.map((item) => `
      <button class="arena-entry__competitor ${state.selected.some((selected) => selected.id === item.id) ? 'is-selected' : ''}" type="button" data-pick-id="${item.id}">
        <img src="${item.foto_url || fallback}" alt="${item.nome}">
        <span><strong>${item.nome}</strong><span>${item.cidade || item.categoria || 'Competidor oficial'}</span></span>
      </button>`).join('');
    list.querySelectorAll('[data-pick-id]').forEach((button) => button.addEventListener('click', () => {
      const id = Number(button.dataset.pickId);
      const exists = state.selected.find((item) => item.id === id);
      state.selected = exists ? state.selected.filter((item) => item.id !== id) : state.selected.length < 4 ? [...state.selected, state.all.find((item) => item.id === id)] : state.selected;
      renderSlots();
      renderList(search.value);
    }));
  };

  const loadProfile = async () => {
    try {
      const data = await jsonFetch(app.dataset.profileApiUrl);
      state.profileComplete = Boolean(data.user?.profile_complete);
    } catch (error) {
      state.profileComplete = true;
    }
  };

  const handleApproved = async () => {
    showStage('success');
    await loadProfile();
    completeProfile.hidden = state.profileComplete;
  };

  const watchPayment = (preferenceId) => {
    window.clearInterval(state.poller);
    state.poller = window.setInterval(async () => {
      try {
        const data = await jsonFetch(api(`/payments/${preferenceId}/status`));
        if (data.status === 'approved') {
          window.clearInterval(state.poller);
          state.poller = null;
          handleApproved();
        }
      } catch (error) {
        showText(paymentFeedback, error.message, 'is-error');
      }
    }, 4000);
  };

  const open = async (league) => {
    state.league = league;
    state.selected = [];
    state.pixCode = '';
    title.textContent = league.name || 'Entrar na disputa';
    subtitle.textContent = 'Escolha quatro competidores para montar sua equipe oficial.';
    showText(feedback, 'Carregando competidores disponíveis...');
    showText(paymentFeedback, '');
    search.value = '';
    qrImage.hidden = true;
    qrText.hidden = true;
    completeProfile.hidden = true;
    showStage('picker');
    modal.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
    try {
      const data = await jsonFetch(api(`/leagues/${league.id}/available-competitors?only_available=1`));
      state.all = Array.isArray(data.data) ? data.data : [];
      renderSlots();
      renderList();
      showText(feedback, state.all.length ? 'Selecione quatro nomes para liberar o Pix.' : 'Nenhum competidor disponível neste momento.', state.all.length ? '' : 'is-error');
    } catch (error) {
      showText(feedback, error.message, 'is-error');
      list.innerHTML = '';
      renderSlots();
    }
  };

  modal.querySelectorAll('[data-entry-close]').forEach((button) => button.addEventListener('click', close));
  search.addEventListener('input', () => renderList(search.value));
  completeProfile.addEventListener('click', () => { close(); openProfile(); });
  modal.querySelector('[data-entry-copy-pix]').addEventListener('click', async () => {
    if (!state.pixCode) return;
    await navigator.clipboard.writeText(state.pixCode);
    showText(paymentFeedback, 'Código Pix copiado com sucesso.', 'is-success');
  });

  pay.addEventListener('click', async () => {
    if (state.selected.length !== 4 || !state.league) return;
    pay.disabled = true;
    pay.querySelector('span').textContent = 'Gerando Pix...';
    showText(feedback, 'Preparando pagamento da sua equipe...');
    try {
      const payload = JSON.stringify({ competitor_ids: state.selected.map((item) => item.id), platform: 'web' });
      const data = await jsonFetch(api(`/leagues/${state.league.id}/teams/pay`), { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: payload });
      if (data.free_entry || data.status === 'approved') return handleApproved();
      state.pixCode = data.qr_code || '';
      qrImage.hidden = !data.qr_code_base64;
      qrText.hidden = Boolean(data.qr_code_base64);
      if (data.qr_code_base64) qrImage.src = data.qr_code_base64;
      else qrText.textContent = state.pixCode || 'Pix indisponível no momento.';
      showStage('payment');
      showText(paymentFeedback, 'Aguardando confirmação do pagamento...');
      if (data.preference_id) watchPayment(data.preference_id);
    } catch (error) {
      pay.disabled = false;
      pay.querySelector('span').textContent = 'Pagar e entrar';
      showText(feedback, error.message, 'is-error');
    }
  });

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-open-entry]');
    if (!trigger) return;
    event.preventDefault();
    if (app.dataset.authenticated !== 'true') return registerTrigger?.click();
    open({ id: trigger.dataset.leagueId, name: trigger.dataset.leagueName });
  });
});
