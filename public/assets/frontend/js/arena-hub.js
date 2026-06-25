document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  if (!app) return;

  const fallbackLogo = '/public/assets/images/logo/logorei.png';
  const grid = document.querySelector('[data-leagues-grid]');
  const feedback = document.querySelector('[data-leagues-feedback]');
  const utility = document.querySelector('.arena-utility');
  const utilityToggle = document.querySelector('[data-utility-toggle]');
  const isAuth = app.dataset.authenticated === 'true';
  const eventId = app.dataset.eventId;
  const supportUrl = app.dataset.supportUrl;
  const openRegister = () => document.querySelector('[data-open-register]')?.click();
  const rulesModal = document.querySelector('[data-rules-modal]');
  const profileModal = document.querySelector('[data-profile-modal]');
  const pixModal = document.querySelector('[data-pix-modal]');
  const statusMap = { open: 'Inscrições abertas', closed: 'Inscrições encerradas', always_open: 'Entrada liberada' };
  const show = (node, text, cls = '') => {
    if (!node) return;
    node.textContent = text || '';
    node.className = `arena-board__feedback ${cls}`.trim();
  };
  const money = (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  const dateLabel = (v) => v ? new Date(v).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) : 'Sem prazo';
  const safeImage = (primary, fallback) => primary || fallback || fallbackLogo;
  const openModal = (modal) => {
    if (!modal) return;
    modal.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
  };
  const closeModal = (modal) => {
    if (!modal) return;
    modal.setAttribute('hidden', 'hidden');
    document.body.style.overflow = '';
  };
  const bindImageFallbacks = (scope) => {
    if (!scope) return;
    scope.querySelectorAll('img[data-fallback-src]').forEach((img) => {
      img.addEventListener('error', () => {
        const nextSrc = img.dataset.fallbackSrc || fallbackLogo;
        if (img.src.endsWith(nextSrc)) return;
        img.src = nextSrc;
      }, { once: true });
    });
  };

  document.querySelector('[data-open-rules]')?.addEventListener('click', () => openModal(rulesModal));
  document.querySelectorAll('[data-close-rules]').forEach((n) => n.addEventListener('click', () => closeModal(rulesModal)));
  document.querySelector('[data-open-profile]')?.addEventListener('click', () => isAuth ? openModal(profileModal) : openRegister());
  document.querySelectorAll('[data-close-profile]').forEach((n) => n.addEventListener('click', () => closeModal(profileModal)));
  document.querySelector('[data-open-pix]')?.addEventListener('click', () => isAuth ? openModal(pixModal) : openRegister());
  document.querySelectorAll('[data-close-pix]').forEach((n) => n.addEventListener('click', () => closeModal(pixModal)));
  document.querySelector('[data-pix-primary]')?.addEventListener('click', () => {
    closeModal(pixModal);
    isAuth ? openModal(profileModal) : openRegister();
  });
  document.querySelector('[data-open-support]')?.addEventListener('click', () => {
    if (supportUrl) window.open(supportUrl, '_blank', 'noopener,noreferrer');
  });
  document.querySelector('[data-refresh-leagues]')?.addEventListener('click', () => loadLeagues());

  const syncUtilityState = () => {
    if (!utility || !utilityToggle) return;
    if (window.innerWidth > 720) {
      utility.classList.remove('is-open');
      return utilityToggle.setAttribute('aria-expanded', 'false');
    }
    utilityToggle.setAttribute('aria-expanded', utility.classList.contains('is-open') ? 'true' : 'false');
  };

  utilityToggle?.addEventListener('click', () => {
    if (!utility || window.innerWidth > 720) return;
    utility.classList.toggle('is-open');
    syncUtilityState();
  });

  window.addEventListener('resize', syncUtilityState);
  syncUtilityState();

  const cardAction = (league) => {
    if (!isAuth) return '<button class="arena-button arena-button--solid" data-card-register>Entrar na disputa</button>';
    if (!league.entry_enabled) return '<button class="arena-button arena-button--ghost" data-card-rules>Evento aguardando competidores</button>';
    if (league.registration_status === 'closed') return '<button class="arena-button arena-button--ghost" data-card-rules>Ver regulamento</button>';
    return `<button class="arena-button arena-button--solid" data-open-entry data-league-id="${league.id}" data-league-name="${league.name}">Entrar na disputa</button>`;
  };

  const render = (leagues) => {
    if (!grid) return;
    if (!leagues.length) {
      grid.innerHTML = '';
      return show(feedback, 'Nenhum bolão oficial encontrado para este evento.', 'is-error');
    }

    show(feedback, 'Arena oficial carregada.');

    grid.innerHTML = leagues.map((league) => `
      <article class="arena-card">
        <div class="arena-card__media">
          <img src="${safeImage(league.image_url || league.rodeio?.logo_url, fallbackLogo)}" data-fallback-src="${fallbackLogo}" alt="${league.name}">
          <span class="arena-card__badge">${statusMap[league.registration_status] || 'Arena oficial'}</span>
        </div>
        <div><h3>${league.organizer?.name || league.name}</h3><p>${league.name}${league.modalidade?.nome ? ` • ${league.modalidade.nome}` : ''}${league.divisao ? ` • ${league.divisao}` : ''}</p></div>
        <div class="arena-card__meta">
          <span>Premiação<strong>${league.prize_type === 'physical' ? (league.prize_description || 'Prêmio físico') : money(league.total_prize || league.prize_pool)}</strong></span>
          <span>Meta de entradas<strong>${league.teams_count} / 100</strong></span>
        </div>
        <div class="arena-card__foot">
          <span>Entrada<strong>${league.is_premium ? 'Premium' : (Number(league.price) > 0 ? money(league.price) : 'Grátis')}</strong></span>
          <span>Prazo<strong>${dateLabel(league.registration_deadline || league.closes_at)}</strong></span>
        </div>
        <div class="arena-card__actions">${cardAction(league)}</div>
      </article>`).join('');

    bindImageFallbacks(grid);
    grid.querySelectorAll('[data-card-register]').forEach((n) => n.addEventListener('click', openRegister));
    grid.querySelectorAll('[data-card-rules]').forEach((n) => n.addEventListener('click', () => openModal(rulesModal)));
  };

  async function fetchLeagues(useEventFilter) {
    const url = new URL(app.dataset.leaguesUrl, window.location.origin);
    if (useEventFilter && eventId) url.searchParams.set('rodeio_id', eventId);
    url.searchParams.set('only_active', '1');
    const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
    const data = await response.json();
    return Array.isArray(data?.data) ? data.data : [];
  }

  async function loadLeagues() {
    show(feedback, 'Carregando bolões oficiais...');
    try {
      let leagues = (await fetchLeagues(true)).filter((item) => item.is_active || item.event_finalized);
      if (!leagues.length) {
        leagues = (await fetchLeagues(false)).filter((item) => item.is_active || item.event_finalized);
        if (leagues.length) show(feedback, 'Mostrando bolões ativos da arena geral.');
      }
      render(leagues);
    } catch (error) {
      show(feedback, 'Não foi possível carregar os bolões da arena.', 'is-error');
    }
  }

  loadLeagues();
});
