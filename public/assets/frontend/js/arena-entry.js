document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  const modal = document.querySelector('[data-entry-modal]');
  if (!app || !modal) return;

  const state = {
    league: null,
    all: [],
    selected: [],
    poller: null,
    pixCode: '',
    profileComplete: true,
    preferenceId: null
  };

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const fallback = '/public/assets/images/logo/logorei.png';
  const title = modal.querySelector('[data-entry-title]');
  const subtitle = modal.querySelector('[data-entry-subtitle]');
  const search = modal.querySelector('[data-entry-search]');
  const slots = modal.querySelector('[data-entry-slots]');
  const counter = modal.querySelector('[data-entry-counter]');
  const list = modal.querySelector('[data-entry-list]');
  const pay = modal.querySelector('[data-entry-pay]');
  const feedback = modal.querySelector('[data-entry-feedback]');
  const paymentFeedback = modal.querySelector('[data-entry-payment-feedback]');
  const paymentStatus = modal.querySelector('[data-entry-payment-status]');
  const qrImage = modal.querySelector('[data-entry-qr-image]');
  const qrText = modal.querySelector('[data-entry-qr-text]');
  const completeProfile = modal.querySelector('[data-entry-complete-profile]');
  const profileModal = document.querySelector('[data-profile-modal]');
  const registerTrigger = document.querySelector('[data-open-register]');
  const copyPixButton = modal.querySelector('[data-entry-copy-pix]');
  const stages = [...modal.querySelectorAll('[data-entry-stage]')];

  const openProfile = () => {
    if (!profileModal) return;
    profileModal.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
  };

  const showStage = (name) => stages.forEach((item) => {
    item.hidden = item.dataset.entryStage !== name;
  });

  const showText = (node, text, cls = '') => {
    if (!node) return;
    node.textContent = text || '';
    node.className = `arena-entry__feedback ${cls}`.trim();
  };

  const showPaymentStatus = (text, cls = '') => {
    if (!paymentStatus) return;
    paymentStatus.textContent = text || '';
    paymentStatus.className = `arena-entry__payment-status ${cls}`.trim();
  };

  const api = (path) => `${app.dataset.fantasyBaseUrl}${path}`;

  const qrSource = (value) => {
    if (!value) return '';
    return String(value).startsWith('data:image') ? value : `data:image/png;base64,${value}`;
  };

  const jsonFetch = async (url, options = {}) => {
    const response = await fetch(url, {
      headers: { Accept: 'application/json', ...options.headers },
      ...options
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok || data.success === false || data.ok === false) {
      throw new Error(data.message || 'Não foi possível concluir esta etapa.');
    }
    return data;
  };

  const resetPaymentView = () => {
    state.pixCode = '';
    state.preferenceId = null;
    qrImage.hidden = true;
    qrImage.removeAttribute('src');
    qrText.hidden = true;
    qrText.textContent = '';
    showPaymentStatus('');
    showText(paymentFeedback, '');
  };

  const close = () => {
    modal.setAttribute('hidden', 'hidden');
    document.body.style.overflow = '';
    window.clearInterval(state.poller);
    state.poller = null;
  };

  const renderSlots = () => {
    if (counter) {
      counter.textContent = `${state.selected.length}/4 selecionados`;
    }

    slots.innerHTML = Array.from({ length: 4 }, (_, index) => {
      const item = state.selected[index];
      if (!item) {
        return `
          <button class="arena-entry__slot" type="button" data-remove-slot="${index}">
            <strong>Vaga ${index + 1}</strong>
            <span>Escolha um competidor</span>
          </button>
        `;
      }

      return `
        <button class="arena-entry__slot is-filled" type="button" data-remove-slot="${index}">
          <img src="${item.foto_url || fallback}" alt="${item.nome}">
          <strong>${item.nome}</strong>
          <span>Toque para remover</span>
        </button>
      `;
    }).join('');

    pay.disabled = state.selected.length !== 4;
    pay.querySelector('span').textContent = state.selected.length === 4
      ? 'Pagar e entrar'
      : `Escolha ${4 - state.selected.length} competidores`;

    slots.querySelectorAll('[data-remove-slot]').forEach((button) => {
      button.addEventListener('click', () => {
        const removed = state.selected[Number(button.dataset.removeSlot)];
        if (!removed) return;
        state.selected = state.selected.filter((item) => item.id !== removed.id);
        renderSlots();
        renderList(search.value);
      });
    });
  };

  const renderList = (term = '') => {
    const query = term.trim().toLowerCase();
    const filtered = state.all.filter((item) => !query || item.nome.toLowerCase().includes(query));

    if (!filtered.length) {
      list.innerHTML = '<div class="arena-entry__empty">Nenhum competidor encontrado.</div>';
      return;
    }

    list.innerHTML = filtered.map((item) => `
      <button class="arena-entry__competitor ${state.selected.some((selected) => selected.id === item.id) ? 'is-selected' : ''}" type="button" data-pick-id="${item.id}">
        <img src="${item.foto_url || fallback}" alt="${item.nome}">
        <span>
          <strong>${item.nome}</strong>
          <span>${item.cidade || item.categoria || 'Competidor oficial'}</span>
        </span>
      </button>
    `).join('');

    list.querySelectorAll('[data-pick-id]').forEach((button) => {
      button.addEventListener('click', () => {
        const id = Number(button.dataset.pickId);
        const exists = state.selected.find((item) => item.id === id);

        if (exists) {
          state.selected = state.selected.filter((item) => item.id !== id);
        } else if (state.selected.length < 4) {
          const picked = state.all.find((item) => item.id === id);
          if (picked) state.selected = [...state.selected, picked];
        }

        renderSlots();
        renderList(search.value);
      });
    });
  };

  const loadProfile = async () => {
    try {
      const data = await jsonFetch(app.dataset.profileApiUrl);
      state.profileComplete = Boolean(data.user?.profile_complete);
    } catch (error) {
      state.profileComplete = true;
    }
  };

  const loadMyTeam = async (leagueId) => {
    try {
      const data = await jsonFetch(api(`/leagues/${leagueId}/teams/me`));
      return data.data || null;
    } catch (error) {
      return null;
    }
  };

  const handleApproved = async () => {
    showStage('success');
    await loadProfile();
    completeProfile.hidden = state.profileComplete;
  };

  const renderPayment = (data) => {
    state.preferenceId = data.preference_id || state.preferenceId;
    state.pixCode = data.qr_code || '';

    qrImage.hidden = !data.qr_code_base64;
    qrText.hidden = Boolean(data.qr_code_base64);

    if (data.qr_code_base64) {
      qrImage.src = qrSource(data.qr_code_base64);
    } else {
      qrText.textContent = state.pixCode || 'Pix indisponível no momento.';
    }

    if (data.status === 'queued') {
      const queueText = data.queue_position
        ? `Seu Pix está na fila. Posição ${data.queue_position}.`
        : 'Seu Pix está na fila de liberação.';
      const waitText = data.estimated_wait_minutes
        ? ` Previsão aproximada de ${data.estimated_wait_minutes} min.`
        : '';
      showPaymentStatus(`${queueText}${waitText}`, 'is-queued');
      showText(paymentFeedback, 'Assim que o Pix for liberado, o QR Code aparecerá aqui.');
      qrImage.hidden = true;
      qrText.hidden = false;
      qrText.textContent = 'Aguardando liberação do QR Code Pix...';
      copyPixButton.disabled = true;
      return;
    }

    copyPixButton.disabled = !state.pixCode;
    showPaymentStatus('Pagamento aguardando confirmação.', 'is-pending');
    showText(paymentFeedback, 'Escaneie o QR Code ou copie o Pix. Após a confirmação, sua participação será ativada automaticamente.');
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
          return;
        }

        if (data.status === 'pending' || data.status === 'queued') {
          renderPayment(data);
          return;
        }

        if (data.status === 'expired') {
          window.clearInterval(state.poller);
          state.poller = null;
          showPaymentStatus('O Pix expirou.', 'is-expired');
          showText(paymentFeedback, 'Feche e gere um novo pagamento para continuar.', 'is-error');
        }
      } catch (error) {
        showText(paymentFeedback, error.message, 'is-error');
      }
    }, 4000);
  };

  const open = async (league) => {
    state.league = league;
    state.all = [];
    state.selected = [];
    search.value = '';
    resetPaymentView();
    title.textContent = league.name || 'Entrar na disputa';
    subtitle.textContent = 'Escolha quatro competidores para montar sua equipe oficial.';
    completeProfile.hidden = true;
    showText(feedback, 'Carregando competidores disponíveis...');
    showStage('picker');
    renderSlots();
    list.innerHTML = '';
    modal.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';

    try {
      const existingTeam = await loadMyTeam(league.id);
      if (existingTeam) {
        await handleApproved();
        return;
      }

      const data = await jsonFetch(api(`/leagues/${league.id}/available-competitors?only_available=1`));
      state.all = Array.isArray(data.data) ? data.data : [];
      renderSlots();
      renderList();
      showText(
        feedback,
        state.all.length
          ? 'Selecione quatro nomes para liberar o Pix.'
          : 'Nenhum competidor disponível neste momento.',
        state.all.length ? '' : 'is-error'
      );
    } catch (error) {
      showText(feedback, error.message, 'is-error');
      list.innerHTML = '';
      renderSlots();
    }
  };

  modal.querySelectorAll('[data-entry-close]').forEach((button) => {
    button.addEventListener('click', close);
  });

  search.addEventListener('input', () => renderList(search.value));
  completeProfile.addEventListener('click', () => {
    close();
    openProfile();
  });

  copyPixButton.addEventListener('click', async () => {
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
      const payload = JSON.stringify({
        competitor_ids: state.selected.map((item) => item.id),
        platform: 'web'
      });

      const data = await jsonFetch(api(`/leagues/${state.league.id}/teams/pay`), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: payload
      });

      if (data.free_entry || data.status === 'approved') {
        await handleApproved();
        return;
      }

      showStage('payment');
      renderPayment(data);

      if (data.preference_id) {
        watchPayment(data.preference_id);
      }
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
    if (app.dataset.authenticated !== 'true') {
      registerTrigger?.click();
      return;
    }

    open({
      id: trigger.dataset.leagueId,
      name: trigger.dataset.leagueName
    });
  });
});
