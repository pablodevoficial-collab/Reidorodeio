document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  if (!app) return;
  const grid = document.querySelector('[data-leagues-grid]');
  const feedback = document.querySelector('[data-leagues-feedback]');
  const countMeta = document.querySelector('[data-stage-meta="count"]');
  const openRegister = () => document.querySelector('[data-open-register]')?.click();
  const show = (node, text, cls = '') => { if (!node) return; node.textContent = text || ''; node.className = `arena-board__feedback ${cls}`.trim(); };
  const money = (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  const dateLabel = (v) => v ? new Date(v).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) : 'Sem prazo';
  const statusMap = { open: 'Inscricoes abertas', closed: 'Inscricoes encerradas', always_open: 'Entrada liberada' };
  const isAuth = app.dataset.authenticated === 'true';
  const eventId = app.dataset.eventId;

  const rulesModal = document.querySelector('[data-rules-modal]');
  const profileModal = document.querySelector('[data-profile-modal]');
  const pixModal = document.querySelector('[data-pix-modal]');
  const openModal = (modal) => { if (!modal) return; modal.removeAttribute('hidden'); document.body.style.overflow = 'hidden'; };
  const closeModal = (modal) => { if (!modal) return; modal.setAttribute('hidden', 'hidden'); document.body.style.overflow = ''; };

  document.querySelector('[data-open-rules]')?.addEventListener('click', () => openModal(rulesModal));
  document.querySelectorAll('[data-close-rules]').forEach((n) => n.addEventListener('click', () => closeModal(rulesModal)));
  document.querySelector('[data-open-profile]')?.addEventListener('click', () => isAuth ? openModal(profileModal) : openRegister());
  document.querySelectorAll('[data-close-profile]').forEach((n) => n.addEventListener('click', () => closeModal(profileModal)));
  document.querySelector('[data-open-pix]')?.addEventListener('click', () => isAuth ? openModal(pixModal) : openRegister());
  document.querySelectorAll('[data-close-pix]').forEach((n) => n.addEventListener('click', () => closeModal(pixModal)));
  document.querySelector('[data-pix-primary]')?.addEventListener('click', () => { closeModal(pixModal); isAuth ? openModal(profileModal) : openRegister(); });
  document.querySelector('[data-refresh-leagues]')?.addEventListener('click', () => loadLeagues());

  const cardAction = (league) => {
    if (!isAuth) return '<button class="arena-button arena-button--solid" data-card-register>Entrar para disputar</button>';
    if (!league.entry_enabled) return '<button class="arena-button arena-button--ghost" data-card-rules>Evento aguardando competidores</button>';
    if (league.registration_status === 'closed') return '<button class="arena-button arena-button--ghost" data-card-rules>Ver regulamento</button>';
    return '<button class="arena-button arena-button--solid" data-card-profile>Completar perfil e entrar</button>';
  };

  const render = (leagues) => {
    if (!grid) return;
    if (!leagues.length) {
      grid.innerHTML = '';
      countMeta && (countMeta.textContent = 'Nenhum bolao encontrado');
      show(feedback, 'Nenhum bolao oficial encontrado para este evento.', 'is-error');
      return;
    }
    countMeta && (countMeta.textContent = `${leagues.length} bolao${leagues.length > 1 ? 'es' : ''} oficial(is)`);
    show(feedback, 'Arena oficial carregada.');
    grid.innerHTML = leagues.map((league) => `
      <article class="arena-card">
        <div class="arena-card__media">
          <img src="${league.image_url || league.rodeio?.logo_url || '/assets/images/logo/logorei.png'}" alt="${league.name}">
          <span class="arena-card__badge">${statusMap[league.registration_status] || 'Arena oficial'}</span>
        </div>
        <div><h3>${league.name}</h3><p>${league.modalidade?.nome || 'Bolao oficial'}${league.divisao ? ` . ${league.divisao}` : ''}${league.organizer?.name ? ` . ${league.organizer.name}` : ''}</p></div>
        <div class="arena-card__meta">
          <span>Premiacao<strong>${league.prize_type === 'physical' ? (league.prize_description || 'Premio fisico') : money(league.prize_pool || league.total_prize)}</strong></span>
          <span>Entradas<strong>${league.teams_count}${league.max_users ? ` / ${league.max_users}` : ''}</strong></span>
        </div>
        <div class="arena-card__foot">
          <span>Entrada<strong>${league.is_premium ? 'Premium' : (Number(league.price) > 0 ? money(league.price) : 'Gratis')}</strong></span>
          <span>Prazo<strong>${dateLabel(league.registration_deadline || league.closes_at)}</strong></span>
        </div>
        <div class="arena-card__actions">${cardAction(league)}</div>
      </article>`).join('');
    grid.querySelectorAll('[data-card-register]').forEach((n) => n.addEventListener('click', openRegister));
    grid.querySelectorAll('[data-card-rules]').forEach((n) => n.addEventListener('click', () => openModal(rulesModal)));
    grid.querySelectorAll('[data-card-profile]').forEach((n) => n.addEventListener('click', () => openModal(profileModal)));
  };

  async function loadLeagues() {
    show(feedback, 'Carregando boloes oficiais...');
    try {
      const url = new URL(app.dataset.leaguesUrl, window.location.origin);
      if (eventId) url.searchParams.set('rodeio_id', eventId);
      url.searchParams.set('only_active', '1');
      const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
      const data = await response.json();
      const leagues = Array.isArray(data) ? data : [];
      render(leagues.filter((item) => item.is_active || item.event_finalized));
    } catch (error) {
      show(feedback, 'Nao foi possivel carregar os boloes da arena.', 'is-error');
    }
  }

  loadLeagues();
});
