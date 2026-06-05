    const statsSupportPhone = '5547997953323';

    function statsArenaUrl(overrides = {}) {
        const params = new URLSearchParams();
        const rodeioId = overrides.rodeio_id ?? state.statsFilters?.rodeio_id ?? null;
        const modalidadeId = overrides.modalidade_id ?? state.statsFilters?.modalidade_id ?? null;
        const divisao = overrides.divisao ?? state.statsFilters?.divisao ?? '';

        if (rodeioId) params.set('rodeio_id', String(rodeioId));
        if (modalidadeId) params.set('modalidade_id', String(modalidadeId));
        if (divisao) params.set('divisao', String(divisao));

        const query = params.toString();
        return query ? `${config.statsArenaData}?${query}` : config.statsArenaData;
    }

    function statsPremiumSupportUrl(planName = 'Premium Estatísticas') {
        const scope = state.statsPayload?.scope || {};
        const message = [
            `Olá! Quero ativar o ${planName} da Arena Estatísticas.`,
            scope.rodeio_nome ? `Rodeio: ${scope.rodeio_nome}` : null,
            scope.modalidade_nome ? `Modalidade: ${scope.modalidade_nome}` : null,
            scope.divisao ? `Divisão: ${scope.divisao}` : null,
        ].filter(Boolean).join('\n');

        return `https://wa.me/${statsSupportPhone}?text=${encodeURIComponent(message)}`;
    }

    function renderStatsStatus(access = {}) {
        if (!el.statsSubscriptionStatus) return;

        const label = access.label || 'Acesso free';
        const plan = access.plan ? esc(access.plan) : 'Premium Estatísticas';
        const days = Number(access.days_remaining || 0);
        const meta = access.is_premium
            ? (days > 0 ? `${days} dia(s) restantes` : plan)
            : 'Mensal, semestral e anual';

        el.statsSubscriptionStatus.innerHTML = `
            <small>Status da conta</small>
            <strong>${esc(label)}</strong>
            <span>${esc(meta)}</span>
        `;

        if (el.statsPremiumAction) {
            el.statsPremiumAction.textContent = access.is_premium
                ? 'Premium liberado'
                : (access.is_authenticated ? 'Quero premium' : 'Entrar e liberar');
        }
    }

    function renderStatsFilters(data = {}) {
        const filters = data.filters || {};
        const selected = filters.selected || {};
        const rodeios = Array.isArray(filters.rodeios) ? filters.rodeios : [];
        const modalidades = Array.isArray(filters.modalidades) ? filters.modalidades : [];
        const divisoes = Array.isArray(filters.divisoes) ? filters.divisoes : [];

        if (el.statsRodeioSelect) {
            el.statsRodeioSelect.innerHTML = rodeios.length
                ? rodeios.map((item) => `<option value="${esc(item.id)}" ${Number(item.id) === Number(selected.rodeio_id) ? 'selected' : ''}>${esc(item.nome)}</option>`).join('')
                : '<option value="">Sem rodeio ativo</option>';
            el.statsRodeioSelect.disabled = rodeios.length === 0;
        }

        if (el.statsModalidadeSelect) {
            el.statsModalidadeSelect.innerHTML = modalidades.length
                ? modalidades.map((item) => `<option value="${esc(item.id)}" ${Number(item.id) === Number(selected.modalidade_id) ? 'selected' : ''}>${esc(item.nome)}</option>`).join('')
                : '<option value="">Sem modalidade</option>';
            el.statsModalidadeSelect.disabled = modalidades.length === 0;
        }

        if (el.statsDivisionChips) {
            const chips = [];
            if (divisoes.length > 1) {
                chips.push(`
                    <button class="rr-stats-stage__division-chip ${selected.divisao ? '' : 'is-active'}" type="button" data-stats-divisao="">
                        Geral
                    </button>
                `);
            }

            chips.push(...divisoes.map((divisao) => `
                <button class="rr-stats-stage__division-chip ${String(divisao) === String(selected.divisao || '') ? 'is-active' : ''}" type="button" data-stats-divisao="${esc(divisao)}">
                    ${esc(divisao)}
                </button>
            `));

            el.statsDivisionChips.innerHTML = chips.join('');
        }
    }

    function renderStatsSummary(data = {}) {
        const scope = data.scope || {};
        const summary = data.summary || {};

        if (el.statsScopeTitle) {
            const scopeParts = [scope.rodeio_nome, scope.modalidade_nome, scope.divisao].filter(Boolean);
            el.statsScopeTitle.textContent = scopeParts.join(' • ') || 'Arena Estatísticas';
        }

        if (el.statsScopeEyebrow) {
            el.statsScopeEyebrow.textContent = scope.event_mode === 'live' ? 'Pontuação ao vivo' : 'Leitura premium';
        }

        if (el.statsScopeLogo) {
            el.statsScopeLogo.src = scope.logo_url || config.logo;
        }

        if (el.statsBoardMeta) {
            el.statsBoardMeta.textContent = summary.last_update_label
                ? `Última atualização ${summary.last_update_label}`
                : 'Os dados entram a partir da live oficial.';
        }

        if (!el.statsSummaryGrid) return;

        const cards = [
            { label: 'Competidores', value: String(summary.competitors || 0) },
            { label: 'Pontos no escopo', value: `${Number(summary.total_points || 0).toLocaleString('pt-BR')} pts` },
            { label: 'Média de pontos', value: `${Number(summary.average_points || 0).toLocaleString('pt-BR')} pts` },
            { label: 'Aproveitamento médio', value: `${Number(summary.average_aproveitamento || 0).toLocaleString('pt-BR')}%` },
            {
                label: 'Líder atual',
                value: data.access?.is_premium && summary.leader_name
                    ? `${summary.leader_name} • ${Number(summary.leader_points || 0).toLocaleString('pt-BR')} pts`
                    : 'Exibido no Premium',
            },
        ];

        el.statsSummaryGrid.innerHTML = cards.map((card) => `
            <article class="rr-stats-stage__metric">
                <small>${esc(card.label)}</small>
                <strong>${esc(card.value)}</strong>
            </article>
        `).join('');
    }

    function renderStatsPlans(data = {}) {
        if (!el.statsPlans) return;

        const access = data.access || {};
        const plans = Array.isArray(data.plans) ? data.plans : [];

        if (access.is_premium) {
            el.statsPlans.innerHTML = '';
            return;
        }

        const lockedCard = `
            <article class="rr-stats-stage__locked">
                <i class="fas fa-crown"></i>
                <strong>Arena Estatísticas liberada só para membro Premium.</strong>
                <span>O painel completo abre a leitura do competidor por rodeio, modalidade e divisão, usando a mesma pontuação da live oficial.</span>
            </article>
        `;

        const planCards = plans.map((plan) => `
            <article class="rr-stats-stage__plan ${plan.is_featured ? 'is-featured' : ''}">
                ${plan.badge ? `<span class="rr-stats-stage__plan-badge" style="background:${esc(plan.badge_color || '#22c55e')}">${esc(plan.badge)}</span>` : ''}
                <div>
                    <h4>${esc(plan.name)}</h4>
                    <div class="rr-stats-stage__plan-price">
                        <strong>${esc(plan.formatted_price || '')}</strong>
                        <span>${esc(plan.period_label || '')}</span>
                    </div>
                    <div class="rr-stats-stage__plan-copy">${esc(plan.description || '')}</div>
                </div>
                <ul class="rr-stats-stage__plan-list">
                    ${(Array.isArray(plan.features) ? plan.features : []).slice(0, 3).map((feature) => `<li>${esc(feature)}</li>`).join('')}
                </ul>
                <button class="rr-stats-stage__plan-button" type="button" data-stats-plan="${esc(plan.name)}">
                    ${access.is_authenticated ? 'Quero este plano' : 'Entrar e liberar'}
                </button>
            </article>
        `).join('');

        el.statsPlans.innerHTML = lockedCard + planCards;
    }

    function renderStatsLeaderboard(data = {}) {
        if (!el.statsLeaderboard) return;

        const access = data.access || {};
        const entries = Array.isArray(data.entries) ? data.entries : [];

        if (!data.has_data) {
            el.statsLeaderboard.innerHTML = '<div class="rr-stats-stage__empty">Ainda não há pontuação ao vivo disponível para este recorte.</div>';
            return;
        }

        if (!access.is_premium) {
            el.statsLeaderboard.innerHTML = '';
            return;
        }

        el.statsLeaderboard.innerHTML = entries.map((entry) => `
            <article class="rr-stats-stage__row">
                <div class="rr-stats-stage__row-rank">#${esc(entry.rank)}</div>
                <div class="rr-stats-stage__row-main">
                    <div class="rr-stats-stage__row-avatar">
                        <img src="${esc(entry.photo_url || config.logo)}" alt="${esc(entry.name)}">
                    </div>
                    <div class="rr-stats-stage__row-copy">
                        <div class="rr-stats-stage__row-name">${esc(entry.name)}</div>
                        <div class="rr-stats-stage__row-meta">
                            ${(entry.phase ? esc(entry.phase) : 'Pontuação oficial')}${entry.division ? ` • ${esc(entry.division)}` : ''}
                        </div>
                        <div class="rr-stats-stage__row-tags">
                            <span class="rr-stats-stage__row-tag">${Number(entry.good_actions || 0).toLocaleString('pt-BR')} boas</span>
                            <span class="rr-stats-stage__row-tag">${Number(entry.negative_actions || 0).toLocaleString('pt-BR')} negativas</span>
                            <span class="rr-stats-stage__row-tag">${Number(entry.aproveitamento || 0).toLocaleString('pt-BR')}% aproveitamento</span>
                            ${(Array.isArray(entry.top_actions) ? entry.top_actions : []).slice(0, 2).map((item) => `<span class="rr-stats-stage__row-tag">${esc(item.label)} ${Number(item.count || 0).toLocaleString('pt-BR')}</span>`).join('')}
                        </div>
                    </div>
                </div>
                <div class="rr-stats-stage__row-score">
                    <strong>${Number(entry.points || 0).toLocaleString('pt-BR')} pts</strong>
                    <span>${entry.last_updated_label ? `Atualizado ${esc(entry.last_updated_label)}` : 'Sem atualização recente'}</span>
                </div>
            </article>
        `).join('');
    }

    function renderStatsArena(data = {}) {
        state.statsPayload = data;
        state.statsFilters = {
            rodeio_id: data.filters?.selected?.rodeio_id || null,
            modalidade_id: data.filters?.selected?.modalidade_id || null,
            divisao: data.filters?.selected?.divisao || '',
        };

        hideMessage(el.statsFeedback);
        renderStatsStatus(data.access || {});
        renderStatsFilters(data);
        renderStatsSummary(data);
        renderStatsPlans(data);
        renderStatsLeaderboard(data);
    }

    async function loadStatsArena(overrides = {}, { force = false } = {}) {
        if (!el.statsStage) return;

        const nextFilters = {
            rodeio_id: overrides.rodeio_id ?? state.statsFilters?.rodeio_id ?? null,
            modalidade_id: overrides.modalidade_id ?? state.statsFilters?.modalidade_id ?? null,
            divisao: overrides.divisao ?? state.statsFilters?.divisao ?? '',
        };
        const requestKey = JSON.stringify(nextFilters);

        if (!force && state.statsPayload && state.statsRequestKey === requestKey) {
            return;
        }

        state.statsRequestKey = requestKey;
        state.statsLoading = true;
        if (el.statsRefreshButton) el.statsRefreshButton.disabled = true;

        try {
            const response = await json(statsArenaUrl(nextFilters));
            renderStatsArena(response.data || {});
        } catch (error) {
            showMessage(el.statsFeedback, 'error', error.message || 'Não foi possível carregar as estatísticas premium.');
        } finally {
            state.statsLoading = false;
            if (el.statsRefreshButton) el.statsRefreshButton.disabled = false;
        }
    }

    function handleStatsPremiumIntent(planName = 'Premium Estatísticas') {
        if (!state.authenticated) {
            openAuthGate('register');
            showMessage(el.statsFeedback, 'error', 'Faça login para liberar a Arena Estatísticas.');
            return;
        }

        window.open(statsPremiumSupportUrl(planName), '_blank', 'noopener');
    }

    if (el.statsRodeioSelect) {
        el.statsRodeioSelect.addEventListener('change', async () => {
            await loadStatsArena({
                rodeio_id: Number(el.statsRodeioSelect.value) || null,
                modalidade_id: null,
                divisao: '',
            }, { force: true });
        });
    }

    if (el.statsModalidadeSelect) {
        el.statsModalidadeSelect.addEventListener('change', async () => {
            await loadStatsArena({
                modalidade_id: Number(el.statsModalidadeSelect.value) || null,
                divisao: '',
            }, { force: true });
        });
    }

    if (el.statsRefreshButton) {
        el.statsRefreshButton.addEventListener('click', async () => {
            await loadStatsArena({}, { force: true });
        });
    }

    if (el.statsPremiumAction) {
        el.statsPremiumAction.addEventListener('click', () => {
            const featuredPlan = Array.isArray(state.statsPayload?.plans)
                ? state.statsPayload.plans.find((plan) => plan.is_featured)
                : null;
            handleStatsPremiumIntent(featuredPlan?.name || 'Premium Estatísticas');
        });
    }

    if (el.statsDivisionChips) {
        el.statsDivisionChips.addEventListener('click', async (event) => {
            const button = event.target?.closest('[data-stats-divisao]');
            if (!button) return;
            await loadStatsArena({
                divisao: button.dataset.statsDivisao || '',
            }, { force: true });
        });
    }

    if (el.statsPlans) {
        el.statsPlans.addEventListener('click', (event) => {
            const button = event.target?.closest('[data-stats-plan]');
            if (!button) return;
            handleStatsPremiumIntent(button.dataset.statsPlan || 'Premium Estatísticas');
        });
    }
