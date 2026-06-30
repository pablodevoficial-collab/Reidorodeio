document.addEventListener('DOMContentLoaded', () => {
  const app = document.querySelector('[data-arena-app]');
  if (!app) return;

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
  const rankingModal = document.querySelector('[data-ranking-modal]');
  const rankingFeedback = document.querySelector('[data-ranking-feedback]');
  const rankingList = document.querySelector('[data-ranking-list]');
  const refreshButton = document.querySelector('[data-refresh-leagues]');
  const statusMap = { open: 'Inscrições abertas', closed: 'Inscrições encerradas', always_open: 'Entrada liberada' };
  const rankingCache = new Map();
  const successRefreshMs = 3000;
  let refreshRunId = 0;

  const show = (node, text, cls = '') => {
    if (!node) return;
    node.textContent = text || '';
    node.className = `arena-board__feedback ${cls}`.trim();
  };

  const money = (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  const dateLabel = (v) => v ? new Date(v).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' }) : 'Sem prazo';
  const sponsorLabel = (league) => league.organizer?.name || league.name;
  const cardValue = (value) => `<strong>${value}</strong>`;

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

  document.querySelector('[data-open-rules]')?.addEventListener('click', () => openModal(rulesModal));
  document.querySelectorAll('[data-close-rules]').forEach((n) => n.addEventListener('click', () => closeModal(rulesModal)));
  document.querySelector('[data-open-profile]')?.addEventListener('click', () => isAuth ? openModal(profileModal) : openRegister());
  document.querySelectorAll('[data-close-profile]').forEach((n) => n.addEventListener('click', () => closeModal(profileModal)));
  document.querySelector('[data-open-pix]')?.addEventListener('click', () => isAuth ? openModal(pixModal) : openRegister());
  document.querySelectorAll('[data-close-pix]').forEach((n) => n.addEventListener('click', () => closeModal(pixModal)));
  document.querySelectorAll('[data-close-ranking]').forEach((n) => n.addEventListener('click', () => closeModal(rankingModal)));
  document.querySelector('[data-pix-primary]')?.addEventListener('click', () => {
    closeModal(pixModal);
    isAuth ? openModal(profileModal) : openRegister();
  });
  document.querySelector('[data-open-support]')?.addEventListener('click', () => {
    if (supportUrl) window.open(supportUrl, '_blank', 'noopener,noreferrer');
  });

  refreshButton?.addEventListener('click', () => loadLeagues(true));

  const syncUtilityState = () => {
    if (!utility || !utilityToggle) return;
    if (window.innerWidth > 720) {
      utility.classList.remove('is-open');
      utilityToggle.setAttribute('aria-expanded', 'false');
      return;
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

  const rankingAction = (league) => `<button class="arena-button arena-button--ghost" data-open-ranking data-league-id="${league.id}" data-league-name="${league.name}">Ranking</button>`;

  const setRefreshButtonState = (state) => {
    if (!refreshButton) return;
    refreshButton.classList.toggle('is-loading', state === 'loading');
    refreshButton.classList.toggle('is-success', state === 'success');
    refreshButton.disabled = state === 'loading' || state === 'success';
    refreshButton.textContent = state === 'success' ? '✓' : 'Atualizar';
  };

  const setRefreshState = (active) => {
    setRefreshButtonState(active ? 'loading' : 'idle');
  };

  const render = (leagues) => {
    if (!grid) return;
    if (!leagues.length) {
      grid.innerHTML = '';
      show(feedback, 'Nenhum bolão oficial encontrado para este evento.', 'is-error');
      return;
    }

    show(feedback, 'Arena oficial carregada.');

    grid.innerHTML = leagues.map((league) => {
      const prize = league.prize_type === 'physical' ? (league.prize_description || 'Prêmio físico') : money(league.total_prize || league.prize_pool);
      const entries = `${league.teams_count} / 100`;
      const price = league.is_premium ? 'Premium' : (Number(league.price) > 0 ? money(league.price) : 'Grátis');
      const deadline = dateLabel(league.registration_deadline || league.closes_at);

      return `
      <article class="arena-card">
        <div class="arena-card__media">
          <span class="arena-card__badge">${statusMap[league.registration_status] || 'Arena oficial'}</span>
        </div>
        <div>
          <h3>${sponsorLabel(league)}</h3>
          <p>${league.name}${league.modalidade?.nome ? ` • ${league.modalidade.nome}` : ''}${league.divisao ? ` • ${league.divisao}` : ''}</p>
        </div>
        <div class="arena-card__meta">
          <span>Premiação${cardValue(prize)}</span>
          <span>Entradas${cardValue(entries)}</span>
        </div>
        <div class="arena-card__foot">
          <span>Entrada${cardValue(price)}</span>
          <span>Prazo${cardValue(deadline)}</span>
        </div>
        <div class="arena-card__actions">${cardAction(league)}</div>
        <div class="arena-card__actions arena-card__actions--secondary">${rankingAction(league)}</div>
      </article>`;
    }).join('');

    grid.querySelectorAll('[data-card-register]').forEach((n) => n.addEventListener('click', openRegister));
    grid.querySelectorAll('[data-card-rules]').forEach((n) => n.addEventListener('click', () => openModal(rulesModal)));
    grid.querySelectorAll('[data-open-ranking]').forEach((n) => n.addEventListener('click', () => openRanking(Number(n.dataset.leagueId), n.dataset.leagueName)));
  };

  async function fetchLeagues(useEventFilter) {
    const url = new URL(app.dataset.leaguesUrl, window.location.origin);
    if (useEventFilter && eventId) url.searchParams.set('rodeio_id', eventId);
    url.searchParams.set('only_active', '1');
    const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
    const data = await response.json();
    return Array.isArray(data?.data) ? data.data : [];
  }

  async function fetchLeagueRanking(leagueId) {
    const base = (app.dataset.fantasyBaseUrl || '').replace(/\/$/, '');
    const response = await fetch(`${base}/leagues/${leagueId}/ranking`, { headers: { Accept: 'application/json' } });
    const data = await response.json();
    return data?.data || null;
  }

  const renderRanking = (data) => {
    if (!rankingList) return;
    const ranking = Array.isArray(data?.ranking) ? data.ranking.slice(0, 100) : [];
    const paid = Number(data?.display_paid_positions || data?.paid_positions || 0);
    const prizePool = Number(data?.prize_pool || 0);
    const distribution = data?.distribution || {};
    const maxUsers = Number(data?.max_users || 0);
    const positionCount = Math.max(maxUsers, paid, ranking.length, 3);
    const rankingByPosition = new Map(ranking.map((item) => [Number(item.position), item]));

    const prizeForPosition = (position) => {
      if (data?.prize_type === 'physical') return data?.prize_description || 'Prêmio físico';
      const percent = Number(distribution[position] || 0);
      if (!percent || !prizePool) return '';
      return money((prizePool * percent) / 100);
    };
    const participantLabel = (item) => item ? (item.display_name || item.user_name || 'Usuário') : 'Aguardando entrada';
    const teamLabel = (item) => item ? (item.team_name || 'Equipe oficial') : '';

    const podium = [1, 2, 3].map((position) => {
      const item = rankingByPosition.get(position);
      return `
        <article class="arena-ranking__podium-card arena-ranking__podium-card--${position}">
          <strong>${position}º</strong>
          <span>${participantLabel(item)}</span>
          <small>${teamLabel(item)}</small>
          <em>${prizeForPosition(position)}</em>
        </article>
      `;
    }).join('');

    const rows = Array.from({ length: Math.max(positionCount - 3, 0) }, (_, index) => index + 4).map((position) => {
      const item = rankingByPosition.get(position);
      const points = item?.can_view_points ? (item.points ?? 0) : (item ? 'Oculto' : 'Livre');
      return `
        <article class="arena-ranking__row ${position <= paid ? 'is-paid' : ''} ${item ? '' : 'is-open'}">
          <strong>#${position}</strong>
          <span>
            <b>${participantLabel(item)}</b>
            <small>${teamLabel(item)}</small>
          </span>
          <em>${prizeForPosition(position)}<small>${points}</small></em>
        </article>
      `;
    }).join('');

    rankingList.innerHTML = `
      <div class="arena-ranking__podium">${podium}</div>
      <div class="arena-ranking__scroll">${rows || '<div class="arena-ranking__empty">Top 3 definido para este bolão.</div>'}</div>
    `;
  };

  const openRanking = async (leagueId, leagueName) => {
    try {
      const cached = rankingCache.get(leagueId);
      const data = cached || await fetchLeagueRanking(leagueId);
      if (!cached) rankingCache.set(leagueId, data);
      if (!rankingModal) return;
      openModal(rankingModal);
      if (rankingFeedback) rankingFeedback.textContent = 'Carregando ranking...';
      if (rankingList) rankingList.innerHTML = '';
      renderRanking(data);
      if (rankingFeedback) rankingFeedback.textContent = '';
    } catch (error) {
      if (rankingFeedback) rankingFeedback.textContent = 'Não foi possível carregar o ranking agora.';
    }
  };

  async function loadLeagues(showButtonFeedback = false) {
    show(feedback, 'Carregando bolões oficiais...');
    const runId = ++refreshRunId;
    if (showButtonFeedback) setRefreshState(true);
    try {
      let leagues = (await fetchLeagues(true)).filter((item) => item.is_active || item.event_finalized);
      if (!leagues.length) {
        leagues = (await fetchLeagues(false)).filter((item) => item.is_active || item.event_finalized);
        if (leagues.length) show(feedback, 'Mostrando bolões ativos da arena geral.');
      }
      render(leagues);
    } catch (error) {
      show(feedback, 'Não foi possível carregar os bolões da arena.', 'is-error');
    } finally {
      if (showButtonFeedback && runId === refreshRunId) {
        setRefreshButtonState('success');
        await new Promise((resolve) => setTimeout(resolve, successRefreshMs));
        setRefreshState(false);
      }
    }
  }

  loadLeagues(false);
});
