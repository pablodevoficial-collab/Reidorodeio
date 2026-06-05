@push('script')
<script>
(() => {
    const app = document.getElementById('rrBolaoFrontend');
    if (!app) return;

    const config = {
        auth: @json($authPayload),
        csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        logo: @json(asset('assets/images/logo_icon/logo.png')),
        login: @json(route('user.login')),
        register: @json(route('user.register')),
        leagues: @json(url('/api/fantasy/leagues')),
        statsArenaData: @json(route('arena.stats.data')),
        rodeios: @json(url('/api/realtime/rodeios')),
        rodeioLogo: @json(url('/rodeios/__RODEIO__/logo')),
        competitors: @json(url('/api/fantasy/leagues/__LEAGUE__/available-competitors')),
        verify: @json(url('/api/fantasy/leagues/__LEAGUE__/teams/verify')),
        pay: @json(url('/api/fantasy/leagues/__LEAGUE__/teams/pay')),
        paymentStatus: @json(url('/api/fantasy/payments/__PAYMENT__/status')),
        cancelPayment: @json(url('/api/fantasy/payments/__PAYMENT__/cancel')),
        ranking: @json(url('/api/fantasy/leagues/__LEAGUE__/ranking')),
        reminder: @json(url('/rodeios/__RODEIO__/email-reminder')),
        fantasyReminder: @json(url('/fantasy/leagues/slots/__SLOT__/email-reminder')),
        profile: @json(url('/api/fantasy/user/profile')),
    };

    const state = {
        mode: null,
        authenticated: !!config.auth.authenticated,
        userName: config.auth.name || config.auth.username || 'Competidor',
        hasRealEmail: !!config.auth.has_real_email,
        profileComplete: !!config.auth.profile_complete,
        events: [],
        rodeios: [],
        sponsors: [],
        leagues: [],
        eventIndex: 0,
        modalidadeId: null,
        statsPayload: null,
        statsFilters: {
            rodeio_id: null,
            modalidade_id: null,
            divisao: '',
        },
        statsLoading: false,
        statsRequestKey: '',
        reminders: Array.from(new Set([...(config.auth.reminders || [])])),
        fantasyReminderSlots: Array.from(new Set([...(config.auth.fantasy_reminder_slots || [])])).map((slot) => String(slot || '').toLowerCase()),
        teamLeague: null,
        competitors: [],
        userActiveTeams: [],
        selected: [],
        captainId: null,
        pixPreferenceId: null,
        pixCode: '',
        pixExpiresAt: null,
        pixStatus: null,
        pixDeleteConfirming: false,
        pixCloseInFlight: false,
        pixUnloadCancelSent: false,
        carouselTimer: null,
        countdownTimer: null,
        rankingLeagueId: null,
    };

    const el = {
        authStage: document.getElementById('rrAuthStage'),
        authBack: document.getElementById('rrAuthBack'),
        authClose: document.getElementById('rrAuthClose'),
        arenaGateway: document.getElementById('rrArenaGateway'),
        liveStage: document.getElementById('rrLiveStage'),
        x1Stage: document.getElementById('rrX1Stage'),
        statsStage: document.getElementById('rrStatsStage'),
        authFeedback: document.getElementById('rrAuthFeedback'),
        liveFeedback: document.getElementById('rrLiveFeedback'),
        statsFeedback: document.getElementById('rrStatsFeedback'),
        loginForm: document.getElementById('rrLoginForm'),
        registerForm: document.getElementById('rrRegisterForm'),
        userName: document.getElementById('rrLiveUserName'),
        avatar: document.getElementById('rrLiveAvatar'),
        heroLogoWrap: document.getElementById('rrHeroLogoWrap'),
        heroLogo: document.getElementById('rrHeroLogo'),
        sponsorParticles: document.getElementById('rrSponsorParticles'),
        heroBadge: document.getElementById('rrHeroBadge'),
        heroEventName: document.getElementById('rrHeroEventName'),
        heroCountdown: document.getElementById('rrHeroCountdown'),
        heroCountdownLabel: document.getElementById('rrHeroCountdownLabel'),
        heroCountdownValue: document.getElementById('rrHeroCountdownValue'),
        heroDots: document.getElementById('rrHeroDots'),
        heroStatEventLabel: document.getElementById('rrHeroStatEventLabel'),
        heroStatEvent: document.getElementById('rrHeroStatEvent'),
        heroStatModalidade: document.getElementById('rrHeroStatModalidade'),
        heroStatModalidadeContainer: document.getElementById('rrHeroStatModalidadeContainer'),
        heroDesktopControls: document.getElementById('rrHeroDesktopControls'),
        heroMobileControls: document.getElementById('rrHeroMobileControls'),
        modalidadeSelectWrapDesktop: document.getElementById('rrModalidadeSelectWrapDesktop'),
        modalidadeSelectWrapMobile: document.getElementById('rrModalidadeSelectWrapMobile'),
        modalidadeSelectDesktop: document.getElementById('rrModalidadeSelectDesktop'),
        modalidadeSelectMobile: document.getElementById('rrModalidadeSelectMobile'),
        notifyButton: document.getElementById('rrNotifyButton'),
        refreshButtonDesktop: document.getElementById('rrRefreshButtonDesktop'),
        refreshButtonMobile: document.getElementById('rrRefreshButtonMobile'),
        cardsParticles: document.getElementById('rrCardsParticles'),
        cardsGrid: document.getElementById('rrCardsGrid'),
        teamModal: document.getElementById('rrTeamModal'),
        teamModalFeedback: document.getElementById('rrTeamModalFeedback'),
        teamToast: document.getElementById('rrTeamToast'),
        teamListShell: document.getElementById('rrTeamListShell'),
        teamScroll: document.getElementById('rrTeamScroll'),
        teamSlots: document.getElementById('rrTeamSlots'),
        competitorSearch: document.getElementById('rrCompetitorSearch'),
        competitorsGrid: document.getElementById('rrCompetitorsGrid'),
        refreshCompetitorsButton: document.getElementById('rrRefreshCompetitorsButton'),
        confirmTeamButton: document.getElementById('rrConfirmTeamButton'),
        pixModal: document.getElementById('rrPixModal'),
        pixModalMeta: document.getElementById('rrPixModalMeta'),
        pixModalContent: document.getElementById('rrPixModalContent'),
        pixModalActions: document.getElementById('rrPixModalActions'),
        pixRawCode: document.getElementById('rrPixRawCode'),
        btnCopyPix: document.getElementById('rrBtnCopyPix'),
        btnVerifyPix: document.getElementById('rrBtnVerifyPix'),
        rankingModal: document.getElementById('rrRankingModal'),
        rankingModalTitle: document.getElementById('rrRankingModalTitle'),
        rankingPrizeGrid: document.getElementById('rrRankingPrizeGrid'),
        rankingPodium: document.getElementById('rrRankingPodium'),
        rankingUpdatedAt: document.getElementById('rrRankingUpdatedAt'),
        rankingHeroMeta: document.getElementById('rrRankingHeroMeta'),
        rankingListMeta: document.getElementById('rrRankingListMeta'),
        rankingList: document.getElementById('rrRankingList'),
        profileModal: document.getElementById('rrProfileModal'),
        profileForm: document.getElementById('rrProfileForm'),
        profileFeedback: document.getElementById('rrProfileFeedback'),
        profileAvatarName: document.getElementById('rrProfileAvatarName'),
        profileAvatarStatus: document.getElementById('rrProfileAvatarStatus'),
        walletModal: document.getElementById('rrWalletModal'),
        walletTotalWon: document.getElementById('rrWalletTotalWon'),
        walletBalance: document.getElementById('rrWalletBalance'),
        comingSoonModal: document.getElementById('rrComingSoonModal'),
        comingSoonRegister: document.getElementById('rrComingSoonRegister'),
        desktopProfile: document.getElementById('rrDesktopProfile'),
        desktopPix: document.getElementById('rrDesktopPix'),
        desktopRulesBtn: document.getElementById('rrDesktopRules'),
        mobileProfile: document.getElementById('rrMobileProfile'),
        mobilePix: document.getElementById('rrMobilePix'),
        mobileRulesBtn: document.getElementById('rrMobileRulesBtn'),
        statsScopeEyebrow: document.getElementById('rrStatsScopeEyebrow'),
        statsScopeTitle: document.getElementById('rrStatsScopeTitle'),
        statsScopeLogo: document.getElementById('rrStatsScopeLogo'),
        statsSubscriptionStatus: document.getElementById('rrStatsSubscriptionStatus'),
        statsRodeioSelect: document.getElementById('rrStatsRodeioSelect'),
        statsModalidadeSelect: document.getElementById('rrStatsModalidadeSelect'),
        statsRefreshButton: document.getElementById('rrStatsRefreshButton'),
        statsDivisionChips: document.getElementById('rrStatsDivisionChips'),
        statsSummaryGrid: document.getElementById('rrStatsSummaryGrid'),
        statsBoardEyebrow: document.getElementById('rrStatsBoardEyebrow'),
        statsBoardTitle: document.getElementById('rrStatsBoardTitle'),
        statsBoardMeta: document.getElementById('rrStatsBoardMeta'),
        statsPremiumAction: document.getElementById('rrStatsPremiumAction'),
        statsPlans: document.getElementById('rrStatsPlans'),
        statsLeaderboard: document.getElementById('rrStatsLeaderboard'),
        rulesModal: document.getElementById('rrRulesModal'),
        rulesModalTitle: document.getElementById('rrRulesModalTitle'),
        rulesModalMeta: document.getElementById('rrRulesModalMeta'),
        rulesModalRulesContent: document.getElementById('rrRulesModalRulesContent'),
        rulesModalTermsContent: document.getElementById('rrRulesModalTermsContent'),
        toggleTermsButton: document.getElementById('rrToggleTermsButton'),
        mobileRefreshFab: document.getElementById('rrMobileRefreshFab'),
        screenLoader: document.getElementById('rrScreenLoader'),
        screenLoaderTitle: document.getElementById('rrScreenLoaderTitle'),
        screenLoaderMeta: document.getElementById('rrScreenLoaderMeta'),
        screenLoaderProgress: document.getElementById('rrScreenLoaderProgress'),
    };

    const modalidadeSelects = [el.modalidadeSelectDesktop, el.modalidadeSelectMobile].filter(Boolean);
    const refreshButtons = [el.refreshButtonDesktop, el.refreshButtonMobile].filter(Boolean);
    const mobileRefreshMediaQuery = window.matchMedia('(max-width: 767px)');
    const mobileRefreshPreferenceKey = 'rr_mobile_refresh_icon_only';
    const pixUnloadCancelKey = 'rr_pix_cancel_on_reload';
    const mobileRefreshHideThreshold = 144;
    const isMobileViewport = window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
    const screenLoaderMinimumMs = 3000;
    let screenLoaderStartedAt = Date.now();
    let screenLoaderTimer = null;
    let mobileRefreshSyncFrame = null;
    let modalScrollLockCount = 0;
    let teamToastTimer = null;
    let pixCountdownTimer = null;
    let pixQueuePollTimer = null;
    let modalScrollPosition = 0;
    let rulesModalView = 'rules';
    const hasMinimalBolaoHero = !el.heroEventName || !el.heroLogo || !el.notifyButton;

    function preferredArenaFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const arena = String(params.get('arena') || window.location.hash.replace('#', '') || '').toLowerCase();
        if (arena === 'bolao' || arena === 'bolão') return 'bolao';
        if (arena === 'x1') return 'x1';
        if (arena === 'stats' || arena === 'estatisticas' || arena === 'estatísticas') return 'stats';
        return 'home';
    }

    function delay(ms) {
        return new Promise((resolve) => window.setTimeout(resolve, ms));
    }

    function setScreenLoaderProgress(progress) {
        if (!el.screenLoaderProgress) return;
        const value = Math.max(0, Math.min(100, Number(progress) || 0));
        el.screenLoaderProgress.style.setProperty('--rr-loader-progress', `${value}%`);
    }

    function showScreenLoader(title = 'Carregando ambiente seguro', meta = 'Preparando sua experiência') {
        if (!el.screenLoader) return;

        screenLoaderStartedAt = Date.now();
        if (screenLoaderTimer) window.clearInterval(screenLoaderTimer);
        el.screenLoader.style.display = 'flex';
        if (el.screenLoaderTitle) el.screenLoaderTitle.textContent = title;
        if (el.screenLoaderMeta) el.screenLoaderMeta.textContent = meta;
        setScreenLoaderProgress(8);
        el.screenLoader.classList.remove('is-hidden');
        screenLoaderTimer = window.setInterval(() => {
            const elapsed = Date.now() - screenLoaderStartedAt;
            const progress = Math.min(92, 8 + (elapsed / screenLoaderMinimumMs) * 82);
            setScreenLoaderProgress(progress);
        }, 160);
    }

    async function hideScreenLoader() {
        if (!el.screenLoader) return;
        const remaining = Math.max(0, screenLoaderMinimumMs - (Date.now() - screenLoaderStartedAt));
        if (remaining) await delay(remaining);
        if (screenLoaderTimer) {
            window.clearInterval(screenLoaderTimer);
            screenLoaderTimer = null;
        }
        setScreenLoaderProgress(100);
        await delay(220);
        el.screenLoader.classList.add('is-hidden');
        await delay(360);
        el.screenLoader.style.display = 'none';
    }

    function syncHomeViewportLock(arena) {
        const lockViewport = arena === 'home';
        document.documentElement.classList.toggle('rr-home-viewport-lock', lockViewport);
        document.body.classList.toggle('rr-home-viewport-lock', lockViewport);
    }

    function showArena(arena, updateUrl = true, transitionLoader = true) {
        const target = arena === 'bolao' || arena === 'x1' || arena === 'stats' ? arena : 'home';
        if (transitionLoader && target !== 'home') {
            const loaderMeta = target === 'x1'
                ? 'Preparando duelo X1'
                : (target === 'stats' ? 'Preparando estatísticas premium' : 'Preparando bolão');
            showScreenLoader('Carregando arena', loaderMeta);
        }

        el.arenaGateway?.classList.toggle('rr-hidden', target !== 'home');
        el.liveStage?.classList.toggle('rr-hidden', target !== 'bolao');
        el.x1Stage?.classList.toggle('rr-hidden', target !== 'x1');
        el.statsStage?.classList.toggle('rr-hidden', target !== 'stats');
        syncHomeViewportLock(target);

        if (updateUrl) {
            const suffix = target === 'home' ? window.location.pathname : `${window.location.pathname}?arena=${target}`;
            window.history.replaceState({}, '', suffix);
        }

        if (target !== 'home') {
            window.setTimeout(() => window.scrollTo({ top: 0, behavior: mobileRefreshMediaQuery.matches ? 'auto' : 'smooth' }), 0);
        }

        if (target === 'stats') {
            loadStatsArena().finally(() => {
                if (transitionLoader) hideScreenLoader();
            });
            return;
        }

        if (transitionLoader && target !== 'home') {
            hideScreenLoader();
        }
    }

    const normalizeCpf = (value) => String(value || '').replace(/\D+/g, '').slice(0, 11);
    const formatCpf = (value) => normalizeCpf(value).replace(/^(\d{3})(\d)/, '$1.$2').replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3').replace(/\.(\d{3})(\d)/, '.$1-$2');
    const formatBirthDate = (value) => {
        const v = String(value || '').replace(/\D+/g, '').slice(0, 8);
        return v.replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');
    };
    const money = (value) => Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const roundMoney = (value) => Math.round((Number(value) + Number.EPSILON) * 100) / 100;
    const esc = (value) => String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    const fantasyCardSlots = [
        { key: 'custom', label: 'Personalizado', metaLabel: 'Personalizado' },
        { key: '20', label: 'R$20', metaLabel: '20R$' },
        { key: '50', label: 'R$50', metaLabel: '50R$' },
        { key: '100', label: 'R$100', metaLabel: '100R$' },
        { key: 'free', label: 'Grátis', metaLabel: 'Livre', placeholder: false },
    ];
    const minimumCompetitorsToEnterLeague = 8;
    const blockedSponsorNames = new Set(['pampasul']);
    const blockedLeagueNames = new Set(['liga classe a']);

    function normalizeBlockedName(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toLowerCase();
    }

    function isBlockedSponsor(sponsor) {
        const name = normalizeBlockedName(sponsor?.name);
        return blockedSponsorNames.has(name);
    }

    function isBlockedLeague(league) {
        const leagueName = normalizeBlockedName(league?.name);
        const rodeioName = normalizeBlockedName(league?.rodeio?.nome);
        return blockedLeagueNames.has(leagueName) || blockedLeagueNames.has(rodeioName);
    }

    function isBlockedRodeio(rodeio) {
        const rodeioName = normalizeBlockedName(rodeio?.nome || rodeio?.label);
        return blockedLeagueNames.has(rodeioName);
    }

    function normalizeFantasySlotKey(value) {
        const normalized = String(value || '').trim().toLowerCase();
        if (normalized === 'custom') return 'custom';
        if (normalized === 'free') return 'free';
        if (normalized === '20' || normalized === '50' || normalized === '100') return normalized;
        return null;
    }

    function getFantasyLeagueSlotKey(league) {
        if (league?.is_premium) return null;
        const priceValue = roundMoney(Number(league?.price || league?.entry_price || 0));
        if (priceValue <= 0) return 'free';
        if (priceValue === 20) return '20';
        if (priceValue === 50) return '50';
        if (priceValue === 100) return '100';
        return 'custom';
    }

    function getFantasyCardEntryValue(league) {
        const priceValue = roundMoney(Number(league?.price || league?.entry_price || 0));
        if (priceValue > 0) return priceValue;

        const slotKey = normalizeFantasySlotKey(league?.slot_key || getFantasyLeagueSlotKey(league));
        if (slotKey === '100') return 100;
        if (slotKey === '50') return 50;
        if (slotKey === '20') return 20;
        return 0;
    }

    function buildFantasyPlaceholderCard(slot) {
        return {
            placeholder: true,
            slot_key: slot.key,
            slot_label: slot.label,
            slot_meta_label: slot.metaLabel,
            id: `placeholder-${slot.key}`,
            price: '0',
            prize_pool: '0',
            teams_count: 0,
            max_users: 0,
            registration_status: 'coming_soon',
            modalidade: { nome: `Card ${slot.label}` },
            rodeio: { nome: 'Rei do Rodeio', logo_url: config.logo },
        };
    }

    function initialsForName(value) {
        return String(value || 'Competidor')
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .map((part) => part.charAt(0))
            .join('')
            .slice(0, 2)
            .toUpperCase() || 'C';
    }

    function normalizeBirthDateForInput(value) {
        const raw = String(value || '').trim();
        if (!raw) return '';
        const isoMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (isoMatch) return `${isoMatch[3]}/${isoMatch[2]}/${isoMatch[1]}`;
        return formatBirthDate(raw);
    }

    @include('frontend.partials.bolao.scripts.statistics-stage')

    function lockBodyScroll() {
        if (modalScrollLockCount === 0) {
            modalScrollPosition = window.scrollY || window.pageYOffset || 0;
            document.documentElement.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${modalScrollPosition}px`;
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
        }

        modalScrollLockCount += 1;
    }

    function unlockBodyScroll() {
        if (modalScrollLockCount > 0) {
            modalScrollLockCount -= 1;
        }

        if (modalScrollLockCount > 0) {
            return;
        }

        document.documentElement.style.overflow = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.body.style.overflow = '';
        window.scrollTo(0, modalScrollPosition);
    }

    function setProfileAvatar(profile = {}) {
        if (!el.avatar) return;

        const name = profile.username || state.userName || 'Competidor';
        const avatarUrl = profile.avatar_url || '';
        const status = profile.photo_status || (avatarUrl ? 'approved' : (profile.photo_pending_review ? 'pending' : 'empty'));
        const fallback = initialsForName(name);

        el.avatar.dataset.status = status;
        el.avatar.innerHTML = avatarUrl
            ? `<img src="${esc(avatarUrl)}" alt="${esc(name)}" style="width:100%; height:100%; object-fit:contain; object-position:center center; border-radius:50%; background:#020617; display:block;">`
            : `<span>${esc(fallback)}</span>`;

        if (el.profileAvatarName) {
            el.profileAvatarName.textContent = name;
        }

        if (el.profileAvatarStatus) {
            el.profileAvatarStatus.dataset.status = status;
            el.profileAvatarStatus.style.color = status === 'approved'
                ? '#4ade80'
                : status === 'pending'
                    ? '#fbbf24'
                    : '#94a3b8';
            el.profileAvatarStatus.textContent = status === 'approved'
                ? 'Foto aprovada'
                : status === 'pending'
                    ? 'Foto em análise'
                    : 'Nenhuma foto aprovada';
        }
    }

    function applyProfileData(profile = {}) {
        const profileForm = el.profileForm;
        if (!profileForm) return;

        const usernameInput = document.getElementById('rrProfileUsername');
        const emailInput = profileForm.querySelector('input[name="email"]');
        const whatsappInput = profileForm.querySelector('input[name="whatsapp"]');
        const birthDateInput = profileForm.querySelector('input[name="birth_date"]');
        const pixKeyInput = profileForm.querySelector('input[name="pix_key"]');

        if (usernameInput && profile.username !== undefined) usernameInput.value = profile.username || '';
        if (emailInput && profile.email !== undefined) emailInput.value = profile.email || '';
        if (whatsappInput && profile.mobile !== undefined) whatsappInput.value = profile.mobile || '';
        if (birthDateInput && profile.birth_date !== undefined) birthDateInput.value = normalizeBirthDateForInput(profile.birth_date || '');
        if (pixKeyInput && profile.pix_key !== undefined) pixKeyInput.value = profile.pix_key || '';

        if (profile.username) {
            state.userName = profile.username;
        }
        if (profile.has_real_email !== undefined) {
            state.hasRealEmail = !!profile.has_real_email;
        }
        if (profile.profile_complete !== undefined) {
            state.profileComplete = !!profile.profile_complete;
        }
        if (Array.isArray(profile.reminders)) {
            state.reminders = Array.from(new Set(profile.reminders.map((value) => Number(value) || 0).filter((value) => value > 0)));
        }
        if (Array.isArray(profile.fantasy_reminder_slots)) {
            state.fantasyReminderSlots = Array.from(new Set(profile.fantasy_reminder_slots.map((slot) => String(slot || '').toLowerCase()).filter(Boolean)));
        }
        if (el.userName && state.userName) {
            el.userName.textContent = state.userName;
        }

        setProfileAvatar(profile);
    }

    function syncMobileRefreshButtonPosition() {
        if (!el.refreshButtonMobile || !el.mobileRefreshFab || !el.heroMobileControls) return;

        if (el.refreshButtonMobile.parentElement !== el.heroMobileControls) {
            el.heroMobileControls.appendChild(el.refreshButtonMobile);
        }

        el.refreshButtonMobile.style.removeProperty('position');
        el.refreshButtonMobile.style.removeProperty('top');
        el.refreshButtonMobile.style.removeProperty('right');
        el.refreshButtonMobile.style.removeProperty('bottom');
        el.refreshButtonMobile.style.removeProperty('left');
        el.refreshButtonMobile.style.removeProperty('transform');
    }

    function syncMobileRefreshButtonMode() {
        if (!el.refreshButtonMobile) return;

        const iconOnly = false;
        const refreshLabel = el.refreshButtonMobile.querySelector('span');

        el.refreshButtonMobile.classList.toggle('rr-mobile-refresh-fixed--icon-only', iconOnly);
        if (refreshLabel) {
            refreshLabel.hidden = iconOnly;
        }

        if (iconOnly) {
            el.refreshButtonMobile.setAttribute('aria-label', 'Atualizar bolões');
        } else {
            el.refreshButtonMobile.removeAttribute('aria-label');
        }
    }

    function syncMobileRefreshFabVisibility() {
        if (!el.mobileRefreshFab) return;
        el.mobileRefreshFab.classList.add('is-hidden');
        el.mobileRefreshFab.setAttribute('aria-hidden', 'true');
    }

    function syncMobileRefreshControls() {
        syncMobileRefreshButtonPosition();
        syncMobileRefreshButtonMode();
        syncMobileRefreshFabVisibility();
    }

    function syncRulesModalView(view = rulesModalView) {
        rulesModalView = view === 'terms' ? 'terms' : 'rules';

        if (!el.rulesModalRulesContent || !el.rulesModalTermsContent || !el.toggleTermsButton || !el.rulesModalTitle || !el.rulesModalMeta) {
            return;
        }

        const showingTerms = rulesModalView === 'terms';

        el.rulesModalRulesContent.classList.toggle('rr-hidden', showingTerms);
        el.rulesModalTermsContent.classList.toggle('rr-hidden', !showingTerms);
        el.rulesModalTitle.textContent = showingTerms ? 'Termos de Uso' : 'Regras do Bolão';
        el.rulesModalMeta.textContent = showingTerms ? 'Leia e confirme os termos antes de continuar' : 'Leia atentamente antes de participar';
        el.toggleTermsButton.innerHTML = showingTerms
            ? '<i class="fas fa-book-open"></i> <span>Ver Regras do Bolão</span>'
            : '<i class="fas fa-file-contract"></i> <span>Ler Termos de Uso</span>';
        el.toggleTermsButton.setAttribute('aria-pressed', showingTerms ? 'true' : 'false');
    }

    function scheduleMobileRefreshControlsSync() {
        if (mobileRefreshSyncFrame) return;

        mobileRefreshSyncFrame = window.requestAnimationFrame(() => {
            mobileRefreshSyncFrame = null;
            syncMobileRefreshControls();
        });
    }

    function showMessage(target, type, message) {
        if (!target) return;
        target.className = `rr-message rr-message--${type}`;
        const list = Array.isArray(message) ? message : [message];
        target.innerHTML = list.map((item) => `<div>${esc(item)}</div>`).join('');
    }

    function hideMessage(target) {
        if (!target) return;
        target.className = 'rr-hidden';
        target.innerHTML = '';
    }

    function hideTeamToast() {
        if (teamToastTimer) {
            clearTimeout(teamToastTimer);
            teamToastTimer = null;
        }
        if (!el.teamToast) return;
        el.teamToast.className = 'rr-team-toast';
        el.teamToast.innerHTML = '';
    }

    function showTeamToast(type, message) {
        if (!el.teamToast) return;
        if (teamToastTimer) clearTimeout(teamToastTimer);
        const icon = type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check';
        el.teamToast.className = `rr-team-toast rr-team-toast--${type} is-visible`;
        el.teamToast.innerHTML = `<i class="fas ${icon}"></i><span>${esc(message)}</span>`;
        teamToastTimer = window.setTimeout(() => hideTeamToast(), 2400);
    }

    function syncTeamScrollFade() {
        if (!el.teamListShell || !el.teamScroll) return;
        const scrollable = el.teamScroll.scrollHeight - el.teamScroll.clientHeight;
        const hasMore = scrollable > 8 && (el.teamScroll.scrollTop + el.teamScroll.clientHeight) < (el.teamScroll.scrollHeight - 8);
        el.teamListShell.classList.toggle('has-more', hasMore);
    }

    function getTeamSelectionBlockMessage() {
        if (state.selected.length !== 4) return '';

        const selectedIds = state.selected.map((item) => Number(item.id)).filter(Boolean);
        const selectedKey = selectedIds.slice().sort((a, b) => a - b).join('|');
        const usedIds = new Set();

        const hasExactDuplicate = state.userActiveTeams.some((team) => {
            const competitorIds = Array.isArray(team?.competitor_ids)
                ? team.competitor_ids.map((id) => Number(id)).filter(Boolean)
                : [];

            competitorIds.forEach((id) => usedIds.add(id));
            return competitorIds.slice().sort((a, b) => a - b).join('|') === selectedKey;
        });

        if (hasExactDuplicate) {
            return 'Essa equipe ja foi montada';
        }

        if (state.userActiveTeams.length > 0) {
            const newCompetitors = selectedIds.filter((id) => !usedIds.has(id)).length;
            if (newCompetitors < 2) {
                return 'Use 2 competidores novos';
            }
        }

        return '';
    }

    function updateConfirmTeamButton() {
        if (!el.confirmTeamButton) return;
        const entryPrice = Number(state.teamLeague?.price || 0);
        const entryPriceLabel = entryPrice > 0 ? money(entryPrice) : 'grátis';

        if (state.selected.length !== 4) {
            el.confirmTeamButton.disabled = true;
            el.confirmTeamButton.classList.add('rr-btn--locked');
            el.confirmTeamButton.innerHTML = '<i class="fas fa-lock"></i><span>Selecione 4 competidores</span>';
            return;
        }

        const teamBlockMessage = getTeamSelectionBlockMessage();
        if (teamBlockMessage) {
            el.confirmTeamButton.disabled = true;
            el.confirmTeamButton.classList.add('rr-btn--locked');
            el.confirmTeamButton.innerHTML = `<i class="fas fa-lock"></i><span>${esc(teamBlockMessage)}</span>`;
            return;
        }

        el.confirmTeamButton.disabled = false;
        el.confirmTeamButton.classList.remove('rr-btn--locked');
        el.confirmTeamButton.innerHTML = `<span>Confirmar entrada ${esc(entryPriceLabel)}</span>`;
    }

    function renderPixModal(payment = {}) {
        stopPixStatusWatch();
        state.pixPreferenceId = payment.preference_id || null;
        state.pixCode = payment.qr_code || '';
        state.pixExpiresAt = payment.expires_at || null;
        state.pixStatus = payment.status || 'pending';
        setPixDeleteButtonConfirming(false);

        if (payment.status === 'queued') {
            renderPixQueued(payment);
            return;
        }

        if (payment.status === 'expired') {
            renderPixExpired();
            return;
        }

        if (el.pixModalMeta) {
            el.pixModalMeta.textContent = 'Escaneie o QR Code ou copie o PIX para concluir sua entrada.';
        }

        if (el.pixRawCode) {
            el.pixRawCode.value = state.pixCode;
        }

        if (el.pixModalActions) {
            el.pixModalActions.style.display = 'flex';
        }

        if (el.btnCopyPix) {
            el.btnCopyPix.style.display = '';
        }

        if (el.btnVerifyPix) {
            el.btnVerifyPix.style.display = '';
        }

        if (el.pixModalContent) {
            el.pixModalContent.innerHTML = `
                ${renderPixCountdownBlock()}
                <div style="width: 244px; max-width: 100%; padding: 14px; border-radius: 24px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);">
                    ${payment.qr_code_base64 ? `<img src="data:image/png;base64,${payment.qr_code_base64}" alt="PIX" style="width: 100%; height: auto; display:block; border-radius: 18px; background:#fff;">` : '<div style="display:grid; place-items:center; aspect-ratio:1; border-radius:18px; background:rgba(255,255,255,0.06); color:#94a3b8; font-weight:800;">QR indisponível</div>'}
                </div>
                <div style="display:grid; gap:4px; width:100%;">
                    <strong style="color:#fff; font-size:1rem;">Pague para confirmar sua equipe</strong>
                    <span style="color:#fca5a5; font-size:0.88rem; line-height:1.45; font-weight:800;">Se recarregar ou fechar esta página, sua equipe e este PIX serão excluídos.</span>
                </div>`;
        }

        startPixCountdown(payment.expires_at || null);
    }

    function renderPixApproved() {
        stopPixStatusWatch();
        state.pixStatus = 'approved';
        clearPixUnloadCancel(state.pixPreferenceId);
        setPixDeleteButtonConfirming(false);
        if (el.pixModalMeta) {
            el.pixModalMeta.textContent = 'Pagamento aprovado';
        }

        if (el.pixModalActions) {
            el.pixModalActions.style.display = 'none';
        }

        if (el.pixModalContent) {
            el.pixModalContent.innerHTML = `
                <div style="width:96px; height:96px; border-radius:999px; display:grid; place-items:center; background:linear-gradient(135deg, rgba(74,222,128,0.24), rgba(22,163,74,0.92)); box-shadow:0 24px 46px rgba(22,163,74,0.28); color:#ecfdf5; font-size:2.6rem;">
                    <i class="fas fa-check"></i>
                </div>
                <div style="display:grid; gap:6px;">
                    <strong style="color:#4ade80; font-size:1.18rem;">Obrigado por participar!</strong>
                    <span style="color:#dcfce7; font-size:0.95rem;">Boa Sorte!</span>
                </div>`;
        }
    }

    function renderPixQueued(payment = {}) {
        const queuePosition = Number(payment.queue_position || 0);
        const estimatedWaitMinutes = Number(payment.estimated_wait_minutes || 0);
        state.pixStatus = 'queued';
        setPixDeleteButtonConfirming(false);

        if (el.pixModalMeta) {
            el.pixModalMeta.textContent = 'Gerando seu PIX';
        }

        if (el.pixModalActions) {
            el.pixModalActions.style.display = 'flex';
        }

        if (el.btnCopyPix) {
            el.btnCopyPix.style.display = 'none';
        }

        if (el.btnVerifyPix) {
            el.btnVerifyPix.style.display = '';
        }

        if (el.pixModalContent) {
            el.pixModalContent.innerHTML = `
                <div style="width:96px; height:96px; border-radius:999px; border:4px solid rgba(59,130,246,0.18); border-top-color:#60a5fa; animation: rrPixQueueSpin 1s linear infinite;"></div>
                <div style="display:grid; gap:6px; width:100%;">
                    <strong style="color:#fff; font-size:1.08rem;">Você está na fila do PIX</strong>
                    <span style="color:#cbd5e1; font-size:0.94rem;">Lugar na fila: <strong style="color:#f8fafc;">${esc(queuePosition || 1)}</strong></span>
                    <span style="color:#94a3b8; font-size:0.84rem; line-height:1.45;">Tempo estimado para gerar seu PIX: <strong style="color:#f8fafc;">${esc(estimatedWaitMinutes || 5)} min</strong></span>
                </div>`;
        }

        pixQueuePollTimer = window.setInterval(() => {
            refreshPixPaymentStatus(true).catch(() => {});
        }, 12000);
    }

    function renderPixExpired() {
        stopPixStatusWatch();
        clearPixUnloadCancel(state.pixPreferenceId);
        state.pixCode = '';
        state.pixExpiresAt = null;
        state.pixStatus = 'expired';
        setPixDeleteButtonConfirming(false);

        if (el.pixModalMeta) {
            el.pixModalMeta.textContent = 'PIX expirado';
        }

        if (el.pixModalActions) {
            el.pixModalActions.style.display = 'none';
        }

        if (el.pixModalContent) {
            el.pixModalContent.innerHTML = `
                <div style="width:96px; height:96px; border-radius:999px; display:grid; place-items:center; background:linear-gradient(135deg, rgba(248,113,113,0.18), rgba(185,28,28,0.88)); box-shadow:0 24px 46px rgba(127,29,29,0.22); color:#fff1f2; font-size:2.3rem;">
                    <i class="fas fa-clock"></i>
                </div>
                <div style="display:grid; gap:6px; width:100%;">
                    <strong style="color:#fda4af; font-size:1.08rem;">Este QR Code expirou</strong>
                    <span style="color:#fecaca; font-size:0.92rem; line-height:1.45; font-weight:800;">O PIX expirou e a equipe reservada foi cancelada. Gere um novo PIX para entrar novamente.</span>
                </div>`;
        }
    }

    function stopPixStatusWatch() {
        if (pixCountdownTimer) {
            clearInterval(pixCountdownTimer);
            pixCountdownTimer = null;
        }

        if (pixQueuePollTimer) {
            clearInterval(pixQueuePollTimer);
            pixQueuePollTimer = null;
        }
    }

    function resetPixState() {
        state.pixPreferenceId = null;
        state.pixCode = '';
        state.pixExpiresAt = null;
        state.pixStatus = null;
        state.pixDeleteConfirming = false;
        state.pixCloseInFlight = false;
        state.pixUnloadCancelSent = false;
        setPixDeleteButtonConfirming(false);
    }

    function isPixModalLocked() {
        const status = String(state.pixStatus || '').toLowerCase();
        return !!state.pixPreferenceId && !['approved', 'expired', 'cancelled'].includes(status);
    }

    function cancelPaymentUrl(preferenceId) {
        return config.cancelPayment.replace('__PAYMENT__', encodeURIComponent(String(preferenceId || '')));
    }

    function rememberPixUnloadCancel(preferenceId) {
        if (!preferenceId) return;
        try {
            sessionStorage.setItem(pixUnloadCancelKey, String(preferenceId));
        } catch (_) {}
    }

    function takePixUnloadCancel() {
        try {
            const preferenceId = sessionStorage.getItem(pixUnloadCancelKey) || '';
            sessionStorage.removeItem(pixUnloadCancelKey);
            return preferenceId;
        } catch (_) {
            return '';
        }
    }

    function clearPixUnloadCancel(preferenceId = null) {
        try {
            const savedPreferenceId = sessionStorage.getItem(pixUnloadCancelKey);
            if (!preferenceId || !savedPreferenceId || savedPreferenceId === String(preferenceId)) {
                sessionStorage.removeItem(pixUnloadCancelKey);
            }
        } catch (_) {}
    }

    function sendPixCancelOnPageExit(preferenceId) {
        if (!preferenceId) return;

        const payload = JSON.stringify({ reason: 'page_reload' });
        const headers = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': config.csrf,
        };

        try {
            fetch(cancelPaymentUrl(preferenceId), {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers,
                body: payload,
            });
            return;
        } catch (_) {}

        if (navigator.sendBeacon) {
            try {
                const body = new URLSearchParams();
                body.set('_token', config.csrf);
                body.set('reason', 'page_reload');
                navigator.sendBeacon(cancelPaymentUrl(preferenceId), body);
            } catch (_) {}
        }
    }

    async function cancelPixPreference(preferenceId, reason = 'page_reload_resume') {
        if (!preferenceId) return null;

        return json(cancelPaymentUrl(preferenceId), {
            method: 'POST',
            body: JSON.stringify({ reason }),
            headers: { 'Content-Type': 'application/json' },
        });
    }

    async function cancelPixMarkedForReload() {
        const preferenceId = takePixUnloadCancel();
        if (!preferenceId || !state.authenticated) return;

        try {
            await cancelPixPreference(preferenceId, 'page_reload_resume');
        } catch (_) {
            // The keepalive request may already have cancelled it before this page loaded.
        }
    }

    function cancelPixOnPageExit() {
        if (!isPixModalLocked() || state.pixUnloadCancelSent) return;

        const preferenceId = state.pixPreferenceId;
        state.pixUnloadCancelSent = true;
        state.pixCloseInFlight = true;
        state.pixStatus = 'cancelled';

        rememberPixUnloadCancel(preferenceId);
        stopPixStatusWatch();
        sendPixCancelOnPageExit(preferenceId);
    }

    async function cancelPixReservation(options = {}) {
        const { closeAfter = false, successMessage = 'Equipe removida da fila do PIX.' } = options;
        if (!state.pixPreferenceId || state.pixCloseInFlight) return false;

        state.pixCloseInFlight = true;

        try {
            const response = await cancelPixPreference(state.pixPreferenceId, 'manual_delete');

            stopPixStatusWatch();
            clearPixUnloadCancel(state.pixPreferenceId);
            closeModal(el.pixModal, { force: true });
            resetPixState();
            await loadLeagues(true);

            if (closeAfter) {
                showMessage(el.teamModalFeedback, 'success', response.message || successMessage);
            }

            return true;
        } catch (error) {
            state.pixCloseInFlight = false;
            setPixDeleteButtonConfirming(false);
            throw error;
        }
    }

    async function dismissPixModal() {
        if (!el.pixModal?.classList.contains('is-open') || state.pixCloseInFlight) {
            return;
        }

        if (isPixModalLocked()) {
            if (el.pixModalMeta) {
                el.pixModalMeta.textContent = 'Finalize o pagamento ou toque em Excluir equipe para cancelar este PIX.';
            }
            setPixDeleteButtonConfirming(false);
            return;
        }

        closeModal(el.pixModal, { force: true });
        resetPixState();
    }

    function setPixDeleteButtonConfirming(confirming) {
        state.pixDeleteConfirming = !!confirming;
        if (!el.btnVerifyPix) return;

        if (state.pixDeleteConfirming) {
            el.btnVerifyPix.innerHTML = '<i class="fas fa-triangle-exclamation"></i> Confirmar exclusão';
            el.btnVerifyPix.style.background = 'linear-gradient(135deg, rgba(220, 38, 38, 0.28), rgba(153, 27, 27, 0.95))';
            el.btnVerifyPix.style.color = '#fff1f2';
            el.btnVerifyPix.style.border = '1px solid rgba(254, 202, 202, 0.4)';
            return;
        }

        el.btnVerifyPix.innerHTML = '<i class="fas fa-trash"></i> Excluir equipe';
        el.btnVerifyPix.style.background = 'rgba(248, 113, 113, 0.12)';
        el.btnVerifyPix.style.color = '#fda4af';
        el.btnVerifyPix.style.border = '1px solid rgba(248, 113, 113, 0.45)';
    }

    function formatPixRemaining(seconds) {
        const safeSeconds = Math.max(0, Number(seconds || 0));
        const minutes = Math.floor(safeSeconds / 60);
        const remainingSeconds = safeSeconds % 60;
        return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    }

    function renderPixCountdownBlock() {
        return `
            <div id="rrPixUrgencyCard" style="width:100%; display:grid; gap:10px; padding:16px 16px 14px; border-radius:20px; background:linear-gradient(135deg, rgba(250, 204, 21, 0.22), rgba(239, 68, 68, 0.18)); border:1px solid rgba(252, 211, 77, 0.28); box-shadow:inset 0 1px 0 rgba(255,255,255,0.08), 0 18px 40px rgba(239, 68, 68, 0.12);">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                    <span style="color:#fde68a; font-size:0.8rem; font-weight:900; letter-spacing:0.12em; text-transform:uppercase;">Tempo limite do PIX</span>
                    <span style="color:#fef2f2; font-size:0.78rem; font-weight:800;">Expira em 5 min</span>
                </div>
                <div id="rrPixTimerValue" style="font-size:2rem; line-height:1; font-weight:900; color:#fff7ed; letter-spacing:0.04em; text-shadow:0 6px 20px rgba(127, 29, 29, 0.4);">05:00</div>
                <div style="width:100%; height:14px; border-radius:999px; background:rgba(15, 23, 42, 0.45); overflow:hidden; border:1px solid rgba(255,255,255,0.08);">
                    <div id="rrPixTimerBar" style="width:100%; height:100%; border-radius:999px; background:linear-gradient(90deg, #facc15 0%, #f97316 58%, #dc2626 100%); box-shadow:0 0 18px rgba(248, 113, 113, 0.35); transform-origin:left center; transition:width 0.85s linear, background 0.3s ease;"></div>
                </div>
                <div id="rrPixTimerHint" style="color:#fee2e2; font-size:0.88rem; line-height:1.35; font-weight:700;">Finalize o pagamento antes do tempo acabar ou o PIX e a equipe expiram juntos.</div>
            </div>`;
    }

    function updatePixCountdownUI(remainingSeconds) {
        const timerCard = document.getElementById('rrPixUrgencyCard');
        const timerValue = document.getElementById('rrPixTimerValue');
        const timerBar = document.getElementById('rrPixTimerBar');
        const timerHint = document.getElementById('rrPixTimerHint');
        if (!timerCard || !timerValue || !timerBar || !timerHint) return;

        const totalSeconds = 5 * 60;
        const safeSeconds = Math.max(0, Number(remainingSeconds || 0));
        const percent = Math.max(0, Math.min(100, (safeSeconds / totalSeconds) * 100));
        const warning = safeSeconds <= 120;
        const critical = safeSeconds <= 60;

        timerValue.textContent = formatPixRemaining(safeSeconds);
        timerBar.style.width = `${percent}%`;
        timerBar.style.background = critical
            ? 'linear-gradient(90deg, #fb7185 0%, #ef4444 45%, #991b1b 100%)'
            : warning
                ? 'linear-gradient(90deg, #fde047 0%, #f97316 50%, #dc2626 100%)'
                : 'linear-gradient(90deg, #facc15 0%, #f59e0b 52%, #ef4444 100%)';
        timerCard.style.animation = critical ? 'rrPixUrgencyPulse 1s ease-in-out infinite' : '';
        timerCard.style.borderColor = critical ? 'rgba(252, 165, 165, 0.5)' : warning ? 'rgba(253, 224, 71, 0.4)' : 'rgba(252, 211, 77, 0.28)';
        timerHint.textContent = critical
            ? 'Último minuto. Se zerar, o PIX expira e sua equipe é cancelada.'
            : warning
                ? 'Atenção: quando o tempo acabar, o PIX e a equipe expiram automaticamente.'
                : 'Finalize o pagamento antes do tempo acabar ou o PIX e a equipe expiram juntos.';
    }

    function startPixCountdown(expiresAt) {
        stopPixStatusWatch();
        if (!expiresAt) return;

        const tick = () => {
            const remainingSeconds = Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000);
            updatePixCountdownUI(remainingSeconds);

            if (el.pixModalMeta) {
                el.pixModalMeta.textContent = remainingSeconds > 0
                    ? `PIX expira em ${formatPixRemaining(remainingSeconds)}`
                    : 'PIX expirando...';
            }

            if (remainingSeconds <= 0) {
                renderPixExpired();
                refreshPixPaymentStatus(true);
            }
        };

        tick();
        pixCountdownTimer = window.setInterval(tick, 1000);
        pixQueuePollTimer = window.setInterval(() => {
            refreshPixPaymentStatus(true).catch(() => {});
        }, 10000);
    }

    async function refreshPixPaymentStatus(silent = false) {
        if (!state.pixPreferenceId) return;

        const status = await json(config.paymentStatus.replace('__PAYMENT__', state.pixPreferenceId));
        if (status.status === 'approved') {
            renderPixApproved();
            await loadLeagues(true);
            window.setTimeout(() => window.location.reload(), 1800);
            return;
        }

        renderPixModal(status);

        if (!silent && el.pixModalMeta && status.status === 'pending') {
            el.pixModalMeta.textContent = 'Pagamento ainda pendente. A confirmação será atualizada automaticamente.';
        }
    }

    async function copyTextValue(value) {
        const text = String(value || '').trim();
        if (!text) return false;

        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (_) {}
        }

        const fallbackField = el.pixRawCode;
        if (!fallbackField) return false;

        const previousValue = fallbackField.value;
        fallbackField.value = text;
        fallbackField.removeAttribute('readonly');
        fallbackField.style.position = 'fixed';
        fallbackField.style.left = '12px';
        fallbackField.style.bottom = '12px';
        fallbackField.style.width = '1px';
        fallbackField.style.height = '1px';
        fallbackField.style.opacity = '0.01';
        fallbackField.style.pointerEvents = 'none';

        fallbackField.focus();
        fallbackField.select();
        fallbackField.setSelectionRange(0, fallbackField.value.length);

        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (_) {
            copied = false;
        }

        fallbackField.value = previousValue;
        fallbackField.setAttribute('readonly', 'readonly');
        fallbackField.blur();
        window.getSelection()?.removeAllRanges();

        return copied;
    }

    async function json(url, options = {}) {
        const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(options.headers || {}) };
        if ((options.method || 'GET').toUpperCase() !== 'GET') headers['X-CSRF-TOKEN'] = config.csrf;
        const response = await fetch(url, { credentials: 'same-origin', ...options, headers });
        let payload = {};
        try { payload = await response.json(); } catch (_) {}
        if (!response.ok) {
            const validationErrors = payload.errors && typeof payload.errors === 'object'
                ? Object.values(payload.errors).flat().filter(Boolean)
                : [];
            throw new Error(payload.message || validationErrors[0] || 'Não foi possível concluir esta ação.');
        }
        return payload;
    }

    function setMode(mode) {
        state.mode = mode;
        document.querySelectorAll('[data-auth-mode-trigger]').forEach((button) => button.classList.toggle('is-active', button.dataset.authModeTrigger === mode));
        el.authStage.classList.toggle('is-form-mode', mode === 'login' || mode === 'register');
        el.loginForm.classList.toggle('is-active', mode === 'login');
        el.registerForm.classList.toggle('is-active', mode === 'register');
        hideMessage(el.authFeedback);
    }

    function openAuthGate(mode = null) {
        if (!el.authStage) return;
        el.authStage.classList.remove('rr-hidden');
        document.body.classList.add('rr-auth-locked');
        setMode(mode);
    }

    function closeAuthGate() {
        if (!el.authStage) return;
        el.authStage.classList.add('rr-hidden');
        document.body.classList.remove('rr-auth-locked');
        hideMessage(el.authFeedback);
    }

    function setAuthenticated(flag, userName = state.userName, hasRealEmail = state.hasRealEmail, profileComplete = state.profileComplete) {
        state.authenticated = flag;
        state.userName = userName || 'Competidor';
        state.hasRealEmail = !!hasRealEmail;
        state.profileComplete = !!profileComplete;
        app.dataset.authenticated = flag ? '1' : '0';
        if (flag) {
            closeAuthGate();
        }
        if (el.userName) el.userName.textContent = state.userName;
        if (el.avatar) el.avatar.textContent = state.userName.split(' ').map((part) => part.charAt(0)).join('').slice(0, 2).toUpperCase();
    }

    async function ensureCompleteProfileForNotifications() {
        if (!state.authenticated) {
            return false;
        }

        try {
            const response = await json(config.profile);
            if (response?.user) {
                applyProfileData(response.user);
                if (response.user.profile_complete) {
                    return true;
                }
            }
        } catch (error) {
            showMessage(el.liveFeedback, 'error', error.message);
            return false;
        }

        showMessage(el.liveFeedback, 'error', 'Complete todo o seu perfil para ativar notificações.');
        return false;
    }

    function parseEventTime(value) {
        if (!value) return null;
        const time = new Date(value).getTime();
        return Number.isFinite(time) ? time : null;
    }

    function computeEventLiveState(event) {
        if (isSponsorEvent(event)) return false;
        const now = Date.now();
        const startTime = parseEventTime(event?.deadline);
        const endTime = parseEventTime(event?.endAt);
        return startTime !== null && endTime !== null && now >= startTime && now <= endTime;
    }

    function finalizeHeroEvents(events) {
        return events.map((event) => {
            event.hasLiveLeague = isSponsorEvent(event) ? false : computeEventLiveState(event);
            return event;
        }).sort((a, b) => {
            if (a.hasLiveLeague !== b.hasLiveLeague) return a.hasLiveLeague ? -1 : 1;
            return (a.deadline ? new Date(a.deadline).getTime() : Number.MAX_SAFE_INTEGER) - (b.deadline ? new Date(b.deadline).getTime() : Number.MAX_SAFE_INTEGER);
        });
    }

    function buildSponsorEvents(sponsors) {
        return (Array.isArray(sponsors) ? sponsors : [])
            .filter((sponsor) => sponsor && sponsor.url && sponsor.logo_url)
            .map((sponsor) => ({
                type: 'sponsor',
                id: `sponsor-${sponsor.id}`,
                sponsorId: Number(sponsor.id || 0),
                name: sponsor.name || 'Patrocinador',
                logo: sponsor.logo_url || config.logo,
                sponsorUrl: sponsor.url,
                sortOrder: Number(sponsor.sort_order || 0),
                deadline: null,
                endAt: null,
                deadlines: [],
                modalidades: [],
                hasUpcomingLeague: false,
                hasLiveLeague: false,
            }))
            .sort((a, b) => {
                const orderDiff = a.sortOrder - b.sortOrder;
                if (orderDiff !== 0) return orderDiff;
                return String(a.name).localeCompare(String(b.name), 'pt-BR');
            });
    }

    function interleaveSponsorEvents(events, sponsors) {
        const sponsorEvents = buildSponsorEvents(sponsors);
        if (!sponsorEvents.length) return events;
        if (!events.length) return sponsorEvents;

        const result = [];
        const max = Math.max(events.length, sponsorEvents.length);

        for (let index = 0; index < max; index += 1) {
            if (events[index]) result.push(events[index]);
            if (sponsorEvents[index]) result.push(sponsorEvents[index]);
        }

        return result;
    }

    function buildEvents(leagues) {
        const map = new Map();
        leagues.forEach((league) => {
            const rodeio = league.rodeio || {};
            const id = Number(rodeio.id || 0);
            if (!id) return;
            if (!map.has(id)) map.set(id, {
                id,
                name: rodeio.nome || league.name || 'Rodeio',
                logo: rodeio.logo_url || league.image_url || config.logo,
                deadlines: [],
                endAt: rodeio.end || null,
                modalidades: new Map(),
                hasUpcomingLeague: false,
            });
            const bucket = map.get(id);
            const deadline = league.registration_deadline || league.closes_at || rodeio.start || null;
            if (deadline) bucket.deadlines.push(deadline);
            if (rodeio.end) bucket.endAt = rodeio.end;
            if (String(league.registration_status || '').toLowerCase() !== 'closed') bucket.hasUpcomingLeague = true;
            const modalidade = league.modalidade || {};
            const modalidadeId = Number(modalidade.id || 0);
            if (!bucket.modalidades.has(modalidadeId)) bucket.modalidades.set(modalidadeId, { id: modalidadeId, nome: modalidade.nome || 'Bolão' });
        });
        return finalizeHeroEvents(Array.from(map.values()).map((event) => {
            event.deadlines.sort();
            event.deadline = event.deadlines[0] || null;
            event.modalidades = Array.from(event.modalidades.values()).sort((a, b) => a.nome.localeCompare(b.nome, 'pt-BR'));
            return event;
        }));
    }

    function mergeHeroEvents(leagueEvents, rodeios, sponsors = []) {
        const map = new Map();

        leagueEvents.forEach((event) => {
            map.set(event.id, { ...event });
        });

        rodeios.forEach((rodeio) => {
            const id = Number(rodeio.id || 0);
            if (!id) return;
            const start = rodeio.start || null;
            const end = rodeio.end || null;

            if (!map.has(id)) {
                map.set(id, {
                    id,
                    name: rodeio.label || `Rodeio ${id}`,
                    logo: config.rodeioLogo.replace('__RODEIO__', id),
                    deadlines: start ? [start] : [],
                    endAt: end,
                    modalidades: [],
                    hasUpcomingLeague: true,
                    deadline: start,
                });
                return;
            }

            const current = map.get(id);
            if (!current.logo || current.logo === config.logo) {
                current.logo = config.rodeioLogo.replace('__RODEIO__', id);
            }
            if (!current.name || current.name === 'Rodeio') {
                current.name = rodeio.label || current.name;
            }
            if (start) {
                current.deadlines = Array.isArray(current.deadlines) ? current.deadlines : [];
                current.deadlines.push(start);
                current.deadlines.sort();
                current.deadline = current.deadlines[0] || current.deadline || null;
            }
            if (end) {
                current.endAt = end;
            }
        });

        return interleaveSponsorEvents(finalizeHeroEvents(Array.from(map.values())), sponsors);
    }

    const currentEvent = () => state.events[state.eventIndex] || null;
    const isSponsorEvent = (event) => event?.type === 'sponsor';
    const leagueById = (leagueId) => state.leagues.find((item) => Number(item.id) === Number(leagueId)) || null;

    function buildSponsorLogoParticles() {
        if (!el.sponsorParticles || el.sponsorParticles.dataset.ready === '1') return;
        el.sponsorParticles.innerHTML = '';
        el.sponsorParticles.dataset.ready = '1';
    }

    function buildCardsPanelParticles() {
        if (!el.cardsParticles || el.cardsParticles.dataset.ready === '1') return;
        el.cardsParticles.innerHTML = '';
        el.cardsParticles.dataset.ready = '1';
    }

    function renderHeroBadge(isSponsor = false) {
        if (!el.heroBadge) return;

        const icon = el.heroBadge.querySelector('i');
        const label = el.heroBadge.querySelector('span');

        el.heroBadge.classList.toggle('is-sponsor', isSponsor);
        if (icon) icon.className = isSponsor ? 'fas fa-gem' : 'fas fa-money-bill-wave';
        if (label) label.textContent = isSponsor ? 'Patrocinador' : 'Premiação real';
    }

    function getHeroControlEvent(displayEvent = currentEvent()) {
        if (!isSponsorEvent(displayEvent)) return displayEvent;

        return state.events.find((event) => !isSponsorEvent(event) && computeEventLiveState(event))
            || state.events.find((event) => !isSponsorEvent(event) && event?.hasUpcomingLeague)
            || state.events.find((event) => !isSponsorEvent(event))
            || null;
    }

    function openSponsorEvent(event = currentEvent()) {
        if (!isSponsorEvent(event)) return false;
        const sponsorUrl = String(event.sponsorUrl || '').trim();
        if (!sponsorUrl) return false;
        window.open(sponsorUrl, '_blank', 'noopener');
        return true;
    }

    function computeTargetPrize(league) {
        const manualPrize = Number(league.manual_prize_pool || 0);
        if (String(league.prize_type || 'money') === 'physical') return 0;
        if (manualPrize > 0) return manualPrize;

        const count = Number(league.teams_count || 0);
        const priceVal = Number(league.price || 0);
        if (!league.is_premium && count > 0 && priceVal > 0) {
            const houseCutPercent = Number(league.house_cut_percent || 0);
            const targetTotalPool = count * priceVal;
            return Math.max(0, targetTotalPool - (targetTotalPool * (houseCutPercent / 100)));
        }

        return Number(league.prize_pool || 0);
    }

    function computeDisplayPrize(league) {
        if (String(league.prize_type || 'money') === 'physical') return 0;

        const manualPrize = Number(league.manual_prize_pool || 0);
        if (manualPrize > 0) return manualPrize;

        const priceVal = Number(league.price || 0);
        const max = Number(league.max_users || 0);
        const houseCutPercent = Number(league.house_cut_percent || 0);

        if (!league.is_premium && max > 0 && priceVal > 0) {
            const maxTotalPool = max * priceVal;
            return Math.max(0, maxTotalPool - (maxTotalPool * (houseCutPercent / 100)));
        }

        return computeTargetPrize(league);
    }

    function getPhysicalPrizeLabel(league) {
        if (String(league?.prize_type || 'money') !== 'physical') return '';
        const description = String(league?.prize_description || '').trim();
        if (description) return description;

        const items = normalizePrizeItems(league?.prize_items);
        const count = Object.keys(items).length;

        if (count === 1) return items[1] || Object.values(items)[0] || 'Prêmio físico';
        if (count > 1) return `${count} prêmios físicos`;

        return 'Prêmio físico';
    }

    function normalizePrizeItems(items) {
        if (!items || typeof items !== 'object') return {};

        return Object.entries(items).reduce((acc, [position, prize]) => {
            const normalizedPosition = Number(position);
            const value = String(prize || '').trim();
            if (normalizedPosition > 0 && value) {
                acc[normalizedPosition] = value;
            }
            return acc;
        }, {});
    }

    function physicalPrizeForPosition(items, position) {
        const normalized = normalizePrizeItems(items);
        return normalized[Number(position)] || '';
    }

    function visibleLeagues() {
        const filteredLeagues = state.leagues
            .filter((league) => String(league.registration_status || '').toLowerCase() !== 'closed')
            .filter((league) => !state.modalidadeId || Number(league?.modalidade?.id || 0) === Number(state.modalidadeId))
            .sort((a, b) => {
                const entryDiff = getFantasyCardEntryValue(b) - getFantasyCardEntryValue(a);
                if (entryDiff !== 0) return entryDiff;
                return computeDisplayPrize(b) - computeDisplayPrize(a);
            });

        const leaguesBySlot = new Map();
        filteredLeagues.forEach((league) => {
            const slotKey = getFantasyLeagueSlotKey(league);
            if (!slotKey) return;
            const current = leaguesBySlot.get(slotKey);
            if (!current || computeDisplayPrize(league) > computeDisplayPrize(current)) {
                leaguesBySlot.set(slotKey, league);
            }
        });

        return fantasyCardSlots
            .map((slot) => leaguesBySlot.get(slot.key) || (slot.placeholder === false ? null : buildFantasyPlaceholderCard(slot)))
            .filter(Boolean)
            .sort((a, b) => {
                const aPlaceholder = !!a?.placeholder;
                const bPlaceholder = !!b?.placeholder;

                const entryDiff = getFantasyCardEntryValue(b) - getFantasyCardEntryValue(a);
                if (entryDiff !== 0) {
                    return entryDiff;
                }

                const prizeDiff = computeDisplayPrize(b) - computeDisplayPrize(a);
                if (prizeDiff !== 0) {
                    return prizeDiff;
                }

                if (aPlaceholder !== bPlaceholder) {
                    return aPlaceholder ? 1 : -1;
                }

                return Number(a?.id || 0) - Number(b?.id || 0);
            });
    }

    function computeInitialModalidade() {
        const openLeagues = state.leagues.filter((league) => String(league.registration_status || '').toLowerCase() !== 'closed');
        const modalidadePrizes = new Map();
        openLeagues.forEach(league => {
            const mId = Number(league?.modalidade?.id || 0);
            if (mId === 0) return;
            const prize = computeTargetPrize(league);
            modalidadePrizes.set(mId, (modalidadePrizes.get(mId) || 0) + prize);
        });
        let bestId = null;
        let maxPrize = -1;
        for (const [mId, total] of modalidadePrizes.entries()) {
            if (total > maxPrize) {
                maxPrize = total;
                bestId = mId;
            }
        }
        return bestId;
    }

    function buildPrizeParticles(total = 18) {
        return '';
    }

    function getLeagueDisplayTitle(league) {
        return String(league?.name || league?.rodeio?.nome || 'Rei do Rodeio').trim() || 'Rei do Rodeio';
    }

    function getLeagueDisplaySubtitle(league, placeholder = false) {
        const title = getLeagueDisplayTitle(league);
        const parts = [];

        if (placeholder) {
            if (league?.slot_label) parts.push(String(league.slot_label).trim());
        } else if (league?.modalidade?.nome) {
            parts.push(String(league.modalidade.nome).trim());
        }

        if (league?.divisao) {
            parts.push(String(league.divisao).trim());
        }

        const rodeioName = String(league?.rodeio?.nome || '').trim();
        if (rodeioName && rodeioName !== title) {
            parts.push(rodeioName);
        }

        const uniqueParts = Array.from(new Set(parts.filter(Boolean)));
        return uniqueParts.join(' \u2022 ') || 'Bol\u00e3o';
    }

    function getLeaguePosterFallback(league) {
        return String(league?.rodeio?.logo_url || config.logo || '');
    }

    function getLeaguePosterUrl(league) {
        return String(league?.image_url || getLeaguePosterFallback(league) || config.logo || '');
    }

    function renderModalidades() {
        const openLeagues = state.leagues.filter((league) => String(league.registration_status || '').toLowerCase() !== 'closed');
        const modalidadesMap = new Map();
        openLeagues.forEach(league => {
            const mId = Number(league?.modalidade?.id || 0);
            if (mId === 0) return;
            if (!modalidadesMap.has(mId)) {
                modalidadesMap.set(mId, league.modalidade.nome || 'Bolão');
            }
        });
        const modalidades = Array.from(modalidadesMap.entries()).map(([id, nome]) => ({ id, nome }))
            .sort((a, b) => a.nome.localeCompare(b.nome, 'pt-BR'));

        if (!modalidades.length) {
            state.modalidadeId = null;
            modalidadeSelects.forEach((select) => {
                select.innerHTML = '<option value="" disabled selected>Selecione uma modalidade</option>';
            });
            return;
        }
        if (state.modalidadeId && !modalidades.some((item) => Number(item.id) === Number(state.modalidadeId))) {
            state.modalidadeId = null;
        }
        let newHtml = `<option value="" disabled ${!state.modalidadeId ? 'selected' : ''}>Selecione uma modalidade</option>`;
        newHtml += modalidades.map((item) => `<option value="${esc(item.id)}" ${Number(item.id) === Number(state.modalidadeId) ? 'selected' : ''}>${esc(item.nome)}</option>`).join('');
        modalidadeSelects.forEach((select) => {
            if (select.innerHTML !== newHtml) {
                select.innerHTML = newHtml;
            }
        });
    }

    function renderCountdown(event = currentEvent()) {
        if (!el.heroCountdownLabel || !el.heroCountdownValue) return;
        if (el.heroCountdown) el.heroCountdown.style.display = '';
        if (event) event.hasLiveLeague = computeEventLiveState(event);
        if (el.heroCountdown) el.heroCountdown.classList.toggle('is-ending', !!event?.hasLiveLeague);
        const targetTime = event?.hasLiveLeague ? event?.endAt : event?.deadline;
        if (!targetTime) {
            el.heroCountdownLabel.textContent = event?.hasLiveLeague ? 'Encerra em' : 'Sem prazo';
            el.heroCountdownValue.textContent = '--';
            return;
        }
        const diff = new Date(targetTime).getTime() - Date.now();
        if (diff <= 0) {
            el.heroCountdownLabel.textContent = 'Encerrado';
            el.heroCountdownValue.textContent = '00h 00m 00s';
            return;
        }
        const total = Math.floor(diff / 1000);
        const d = Math.floor(total / 86400);
        const h = Math.floor((total % 86400) / 3600);
        const m = Math.floor((total % 3600) / 60);
        const s = total % 60;
        el.heroCountdownLabel.textContent = event.hasLiveLeague ? 'Encerra em' : 'Começa em';
        el.heroCountdownValue.textContent = `${d > 0 ? `${d}d ` : ''}${String(h).padStart(2, '0')}h ${String(m).padStart(2, '0')}m ${String(s).padStart(2, '0')}s`;
    }

    function renderHero() {
        if (hasMinimalBolaoHero) return;
        const event = currentEvent();
        if (!event) {
            el.heroLogo.src = config.logo;
            renderHeroBadge(false);
            el.heroEventName.classList.remove('is-sponsor-link');
            el.heroEventName.removeAttribute('role');
            el.heroEventName.removeAttribute('tabindex');
            el.heroEventName.removeAttribute('aria-label');
            el.heroEventName.textContent = 'Nenhum rodeio disponível';
            if (el.heroStatEventLabel) el.heroStatEventLabel.textContent = 'Próximos rodeios';
            el.heroStatEvent.textContent = 'Sem rodeios';
            el.heroStatModalidade.textContent = '--';
            if (el.heroStatModalidadeContainer) el.heroStatModalidadeContainer.style.display = 'none';
            if (el.modalidadeSelectWrapDesktop) el.modalidadeSelectWrapDesktop.style.display = 'none';
            if (el.modalidadeSelectWrapMobile) el.modalidadeSelectWrapMobile.style.display = 'none';
            if (el.heroDesktopControls) el.heroDesktopControls.style.display = '';
            if (el.heroMobileControls) el.heroMobileControls.style.display = '';
            el.heroDots.innerHTML = '';
            el.notifyButton.disabled = false;
            el.notifyButton.style.display = 'none';
            el.notifyButton.classList.remove('is-active', 'is-sponsor');
            if (el.heroLogoWrap) el.heroLogoWrap.classList.remove('is-sponsor-showcase');
            const emptyIcon = el.notifyButton.querySelector('i');
            if (emptyIcon) emptyIcon.className = 'fas fa-bell';
            const emptySpan = el.notifyButton.querySelector('span');
            if (emptySpan) emptySpan.textContent = 'Me avise quando começar';
            renderCountdown();
            return;
        }
        const sponsorActive = isSponsorEvent(event);
        const controlEvent = getHeroControlEvent(event);
        if (controlEvent) controlEvent.hasLiveLeague = computeEventLiveState(controlEvent);
        event.hasLiveLeague = sponsorActive ? !!controlEvent?.hasLiveLeague : computeEventLiveState(event);
        renderHeroBadge(sponsorActive);
        if (el.heroLogoWrap) el.heroLogoWrap.classList.toggle('is-sponsor-showcase', sponsorActive);
        if (sponsorActive) buildSponsorLogoParticles();
        el.heroLogo.src = event.logo || config.logo;
        el.heroEventName.textContent = sponsorActive ? `Acessar ${event.name}` : event.name;
        el.heroEventName.classList.toggle('is-sponsor-link', sponsorActive && !!event.sponsorUrl);
        if (sponsorActive && event.sponsorUrl) {
            el.heroEventName.setAttribute('role', 'button');
            el.heroEventName.setAttribute('tabindex', '0');
            el.heroEventName.setAttribute('aria-label', `Acessar ${event.name}`);
        } else {
            el.heroEventName.removeAttribute('role');
            el.heroEventName.removeAttribute('tabindex');
            el.heroEventName.removeAttribute('aria-label');
        }
        if (el.heroStatEventLabel) el.heroStatEventLabel.textContent = isSponsorEvent(event) ? 'Patrocinador' : (event.hasLiveLeague ? 'Rodeio ao vivo' : 'Próximos rodeios');
        el.heroStatEvent.textContent = event.name;
        
        const hasModalidades = Array.isArray(controlEvent?.modalidades) && controlEvent.modalidades.length > 0;
        const canShowModalidades = hasModalidades && !!controlEvent?.hasLiveLeague;
        
        if (el.heroStatModalidadeContainer) {
            el.heroStatModalidadeContainer.style.display = canShowModalidades ? '' : 'none';
        }
        
        if (el.modalidadeSelectWrapDesktop) el.modalidadeSelectWrapDesktop.style.display = canShowModalidades ? '' : 'none';
        if (el.modalidadeSelectWrapMobile) el.modalidadeSelectWrapMobile.style.display = canShowModalidades ? '' : 'none';
        if (el.heroDesktopControls) el.heroDesktopControls.style.display = '';
        if (el.heroMobileControls) el.heroMobileControls.style.display = '';
        
        const activeSelect = modalidadeSelects.find((select) => Number(select.value || 0) === Number(state.modalidadeId)) || modalidadeSelects[0];
        const selectedOption = activeSelect?.options[activeSelect.selectedIndex];
        const selectedModalidadeText = (selectedOption && selectedOption.value !== "") ? selectedOption.text : null;
        el.heroStatModalidade.textContent = selectedModalidadeText || 'Selecione uma modalidade';
        el.heroDots.innerHTML = state.events.length > 1 ? state.events.map((_, index) => `<span class="rr-dot${index === state.eventIndex ? ' is-active' : ''}"></span>`).join('') : '';

        if (sponsorActive) {
            const sponsorIcon = el.notifyButton.querySelector('i');
            const sponsorSpan = el.notifyButton.querySelector('span');
            if (sponsorIcon) sponsorIcon.className = 'fas fa-bell';
            if (sponsorSpan) sponsorSpan.textContent = 'Me avise quando começar';
            const hasControlReminder = controlEvent && state.reminders && state.reminders.includes(controlEvent.id);
            el.notifyButton.disabled = false;
            el.notifyButton.style.display = (!controlEvent || controlEvent.hasLiveLeague || hasControlReminder) ? 'none' : '';
            el.notifyButton.classList.remove('is-active', 'is-sponsor');
            renderCountdown(controlEvent);
            return;
        }
        
        const hasReminded = state.reminders && state.reminders.includes(event.id);
        el.notifyButton.style.display = (event.hasLiveLeague || hasReminded) ? 'none' : '';
        
        const notifyIcon = el.notifyButton.querySelector('i');
        if (notifyIcon) notifyIcon.className = 'fas fa-bell';
        el.notifyButton.disabled = false;
        el.notifyButton.classList.remove('is-active', 'is-sponsor');
        el.notifyButton.querySelector('span').textContent = 'Me avise quando começar';
        renderCountdown(event);
    }

    function animatePrizes() {
        if (window.matchMedia && window.matchMedia('(max-width: 767px), (prefers-reduced-motion: reduce)').matches) {
            el.cardsGrid.querySelectorAll('.rr-slot-prize').forEach((node) => {
                const target = parseFloat(node.dataset.val) || 0;
                if (target > 0) node.textContent = money(target);
            });
            return;
        }

        el.cardsGrid.querySelectorAll('.rr-slot-prize').forEach((node) => {
            const target = parseFloat(node.dataset.val) || 0;
            if (target <= 0) return;
            const duration = 1200; // 1.2s spin
            const startTimestamp = performance.now();
            
            const step = (timestamp) => {
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                // easeOutExpo gives a slot machine slowing down feel
                const easeOut = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                node.textContent = money(target * easeOut);
                
                if (progress < 1) requestAnimationFrame(step);
                else node.textContent = money(target);
            };
            requestAnimationFrame(step);
        });
    }

    function renderCards() {
        const tones = ['violet', 'green', 'amber', 'blue'];
        const cards = visibleLeagues();
        el.cardsGrid.innerHTML = cards.map((league, index) => {
            const placeholder = !!league.placeholder;
            const count = Number(league.teams_count || 0);
            const max = Number(league.max_users || 0);
            const slotKey = normalizeFantasySlotKey(league.slot_key || getFantasyLeagueSlotKey(league));
            const hasFantasyReminder = !!slotKey && state.fantasyReminderSlots.includes(slotKey);
            const availableCompetitorsCount = Number(league.available_competitors_count || 0);
            const entryEnabled = !placeholder && !!league.entry_enabled && availableCompetitorsCount >= minimumCompetitorsToEnterLeague;
            const missingCompetitors = Math.max(0, minimumCompetitorsToEnterLeague - availableCompetitorsCount);
            
            let currentCalculatedPrize = Number(league.prize_pool || 0);
            let maxPotentialPrize = currentCalculatedPrize;
            
            if (!placeholder) {
                currentCalculatedPrize = computeTargetPrize(league);
                maxPotentialPrize = computeDisplayPrize(league);
            }
            const physicalPrizeLabel = placeholder ? '' : getPhysicalPrizeLabel(league);
            const prizeValueClass = physicalPrizeLabel ? 'rr-card__prize-value rr-card__prize-value--text' : 'rr-card__prize-value rr-slot-prize';
            const prizeValueAttrs = physicalPrizeLabel ? '' : ` data-val="${currentCalculatedPrize}"`;
            const prizeValueText = physicalPrizeLabel || money(currentCalculatedPrize);
            const prizeLabel = physicalPrizeLabel ? 'Pr\u00eamio' : 'Pr\u00eamio atual';
            const prizeCurrentMarkup = physicalPrizeLabel
                ? '<div class="rr-card__prize-current">Pr\u00eamio definido pela administra\u00e7\u00e3o</div>'
                : `<div class="rr-card__prize-current">Meta do bol\u00e3o: <span class="rr-slot-prize" data-val="${maxPotentialPrize}">${esc(money(maxPotentialPrize))}</span></div>`;
            const headline = getLeagueDisplayTitle(league);
            const subtitle = getLeagueDisplaySubtitle(league, placeholder);
            const posterFallback = getLeaguePosterFallback(league);
            const posterUrl = getLeaguePosterUrl(league);
            const entryLabel = placeholder ? 'Em breve' : (Number(league.price || 0) > 0 ? money(league.price) : 'Gr\u00e1tis');
            const teamsValue = max > 0 ? `${count}/${max}` : `${count}`;
            const availableValue = placeholder ? 'Aviso' : `${availableCompetitorsCount}/${minimumCompetitorsToEnterLeague}`;
            const statusPrimary = placeholder
                ? 'Aguardando abertura'
                : (entryEnabled ? 'Entrada liberada' : `Faltam ${missingCompetitors}`);
            const statusSecondary = placeholder
                ? 'Ative o aviso deste card'
                : (entryEnabled ? `${count} equipes confirmadas` : `${availableCompetitorsCount} competidores liberados`);
            const badgeText = placeholder
                ? 'Bol\u00e3o em breve'
                : (Number(league.price || 0) > 0 ? `Entrada ${money(league.price)}` : 'Entrada gr\u00e1tis');
            const metaText = placeholder ? (league.slot_meta_label || '') : (max > 0 ? `${count}/${max}` : 'aberto');

            return `
                <article class="rr-card${placeholder ? ' rr-card--placeholder' : ''}" data-tone="${esc(tones[index % tones.length])}">
                    <img class="rr-card__ghost" src="${esc(league?.rodeio?.logo_url || config.logo)}" alt="">
                    <div class="rr-card__media-band">
                        <img class="rr-card__poster" src="${esc(posterUrl)}" alt="${esc(headline)}" onerror="this.onerror=null;this.src='${esc(posterFallback)}';">
                        <div class="rr-card__scrim"></div>
                        <div class="rr-card__top">
                            <span class="rr-card__badge">${esc(badgeText)}</span>
                            <span class="rr-meta">${esc(metaText)}</span>
                        </div>
                        <div class="rr-card__event-row">
                            <img class="rr-card__event-logo" src="${esc(league?.rodeio?.logo_url || posterFallback)}" alt="${esc(league?.rodeio?.nome || headline)}" onerror="this.onerror=null;this.src='${esc(config.logo)}';">
                            <div class="rr-card__event"><strong>${esc(headline)}</strong><span>${esc(subtitle)}</span></div>
                        </div>
                    </div>
                    <div class="rr-card__scoreboard">
                        <div class="rr-card__metric"><span>Entrada</span><strong>${esc(entryLabel)}</strong></div>
                        <div class="rr-card__metric"><span>Equipes</span><strong>${esc(teamsValue)}</strong></div>
                        <div class="rr-card__metric"><span>Dispon\u00edveis</span><strong>${esc(availableValue)}</strong></div>
                    </div>
                    <div class="rr-card__prize-wrap">
                        <div class="rr-card__particles">${buildPrizeParticles(placeholder ? 8 : 18)}</div>
                        <div class="rr-card__prize-label">${esc(prizeLabel)}</div>
                        <div class="rr-card__prize-frame">
                            <div class="${prizeValueClass}"${prizeValueAttrs}>${esc(prizeValueText)}</div>
                            ${prizeCurrentMarkup}
                        </div>
                    </div>
                    <div class="rr-card__meta"><span>${esc(statusPrimary)}</span><span>${esc(statusSecondary)}</span></div>
                    <div class="rr-card__actions">
                        <button class="rr-card__btn ${placeholder || !entryEnabled ? 'rr-card__btn--locked' : 'rr-card__btn--enter'}" type="button" ${placeholder || !entryEnabled ? 'disabled' : ''} data-open-team="${esc(league.id)}"><i class="fas ${placeholder || !entryEnabled ? 'fa-lock' : 'fa-coins'}"></i> Entrar</button>
                        ${placeholder
                            ? `<button class="rr-card__btn rr-card__btn--notify${hasFantasyReminder ? ' is-active' : ''}" type="button" data-notify-slot="${esc(slotKey || '')}" ${hasFantasyReminder ? 'disabled' : ''}><i class="fas ${hasFantasyReminder ? 'fa-check' : 'fa-bell'}"></i> ${hasFantasyReminder ? 'Ativado' : 'Notificar'}</button>`
                            : `<button class="rr-card__btn rr-card__btn--ranking" type="button" data-open-ranking="${esc(league.id)}"><i class="fas fa-chart-simple"></i> Ranking</button>`}
                    </div>
                </article>`;
        }).join('');
        el.cardsGrid.querySelectorAll('[data-open-team]').forEach((button) => button.addEventListener('click', () => openTeamModal(Number(button.dataset.openTeam))));
        el.cardsGrid.querySelectorAll('[data-open-ranking]').forEach((button) => button.addEventListener('click', () => openRankingModal(Number(button.dataset.openRanking))));
        el.cardsGrid.querySelectorAll('[data-notify-slot]').forEach((button) => button.addEventListener('click', () => handleFantasySlotReminder(button.dataset.notifySlot, button)));
        el.cardsGrid.dispatchEvent(new CustomEvent('rr:cards-rendered', { bubbles: true }));
        animatePrizes();
    }

    function renderAll() {
        renderModalidades();
        renderHero();
        renderCards();
    }

    async function loadLeagues(keepState = false) {
        try {
            const [leaguesPayload, rodeiosPayload] = await Promise.all([
                json(`${config.leagues}?only_active=1`),
                json(config.rodeios),
            ]);
            state.leagues = (Array.isArray(leaguesPayload.data) ? leaguesPayload.data : []).filter((league) => !isBlockedLeague(league));
            state.rodeios = (Array.isArray(rodeiosPayload.data) ? rodeiosPayload.data : []).filter((rodeio) => !isBlockedRodeio(rodeio));
            state.sponsors = [];
            state.events = mergeHeroEvents(buildEvents(state.leagues), state.rodeios, []);
            
            // se NÃO for click de atualizar bolões com intenção de manter o state, resetamos
            if (!keepState) {
                state.eventIndex = 0;
                state.modalidadeId = null;
            }
            if (state.eventIndex >= state.events.length) {
                state.eventIndex = 0;
            }
            const liveEventIndex = state.events.findIndex((event) => event.hasLiveLeague);
            if (liveEventIndex >= 0) {
                state.eventIndex = liveEventIndex;
            }
            
            renderAll();
            hideMessage(el.liveFeedback);
            startCarousel();
            startCountdown();
            scheduleMobileRefreshControlsSync();
        } catch (error) {
            showMessage(el.liveFeedback, 'error', error.message);
        }
    }

    function startCarousel() {
        if (state.carouselTimer) clearInterval(state.carouselTimer);
        if (state.events.length <= 1) return;
        state.carouselTimer = setInterval(() => {
            state.eventIndex = (state.eventIndex + 1) % state.events.length;
            renderHero();
        }, 8500);
    }

    function startCountdown() {
        if (state.countdownTimer) clearInterval(state.countdownTimer);
        state.countdownTimer = setInterval(() => {
            renderHero();
        }, 1000);
        renderHero();
    }

    async function submitAuth(event, mode) {
        event.preventDefault();
        hideMessage(el.authFeedback);
        const formData = new FormData(event.currentTarget);
        formData.set('cpf', normalizeCpf(formData.get('cpf')));
        try {
            const payload = await json(mode === 'login' ? config.login : config.register, { method: 'POST', body: formData });
            setAuthenticated(
                true,
                payload?.user?.username || state.userName,
                payload?.user?.has_real_email ?? state.hasRealEmail,
                payload?.user?.profile_complete ?? state.profileComplete
            );
            try {
                const profilePayload = await json(config.profile);
                if (profilePayload?.user) {
                    applyProfileData(profilePayload.user);
                }
            } catch (_) {}
            await loadLeagues();
        } catch (error) {
            showMessage(el.authFeedback, 'error', error.message);
        }
    }

    function openModal(modal) {
        if (!modal) return;
        if (!modal.classList.contains('is-open')) {
            lockBodyScroll();
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal(modal, options = {}) {
        if (!modal) return false;
        if (!modal.classList.contains('is-open')) {
            return false;
        }

        if (modal === el.pixModal && isPixModalLocked() && !options.force) {
            if (el.pixModalMeta) {
                el.pixModalMeta.textContent = 'Finalize o pagamento ou toque em Excluir equipe para cancelar este PIX.';
            }
            return false;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (modal === el.teamModal) {
            hideTeamToast();
            syncTeamScrollFade();
        }
        if (modal === el.pixModal) {
            stopPixStatusWatch();
            setPixDeleteButtonConfirming(false);
        }
        unlockBodyScroll();
        return true;
    }

    function renderSlots() {
        const items = state.selected.slice();
        while (items.length < 4) items.push(null);
        el.teamSlots.innerHTML = items.map((item, index) => {
            if (!item) {
                return `
                    <div class="rr-team-member rr-team-member--empty">
                        <div class="rr-team-member__avatar rr-team-member__avatar--empty"><i class="fas fa-user"></i></div>
                        <div class="rr-team-member__content">
                            <div class="rr-team-member__top">
                                <span class="rr-team-member__name">Vaga ${index + 1}</span>
                            </div>
                            <span class="rr-meta">Selecione um competidor</span>
                        </div>
                    </div>`;
            }
            return `
                <div class="rr-team-member">
                    <img class="rr-team-member__avatar" src="${esc(item.foto_url || config.logo)}" alt="${esc(item.nome)}">
                    <div class="rr-team-member__content">
                        <div class="rr-team-member__top">
                            <span class="rr-team-member__name">${esc(item.nome)}</span>
                            <button class="rr-team-member__remove" type="button" data-remove-competitor="${esc(item.id)}" aria-label="Remover ${esc(item.nome)}"><i class="fas fa-xmark"></i></button>
                        </div>
                        ${index === 0 ? '<span class="rr-team-member__badge"><i class="fas fa-crown"></i> Capitão</span>' : ''}
                    </div>
                </div>`;
        }).join('');
        el.teamSlots.querySelectorAll('[data-remove-competitor]').forEach((button) => button.addEventListener('click', () => {
            const competitorId = Number(button.dataset.removeCompetitor);
            state.selected = state.selected.filter((item) => Number(item.id) !== competitorId);
            if (Number(state.captainId) === competitorId) state.captainId = state.selected[0]?.id || null;
            renderSlots();
            renderCompetitors();
        }));
        updateConfirmTeamButton();
    }

    function renderCompetitors() {
        const term = String(el.competitorSearch.value || '').trim().toLowerCase();
        const filtered = state.competitors.filter((item) => !term || [item.nome, item.cidade, item.categoria].filter(Boolean).join(' ').toLowerCase().includes(term));
        el.competitorsGrid.innerHTML = filtered.map((item) => {
            const selected = state.selected.some((entry) => Number(entry.id) === Number(item.id));
            const full = state.selected.length >= 4;
            const buttonContent = selected
                ? '<i class="fas fa-xmark"></i>'
                : full
                    ? '<i class="fas fa-lock"></i>'
                    : esc(state.selected.length === 0 ? 'Selecionar Capitão' : 'Selecionar');
            const buttonClass = selected
                ? 'rr-competitor__add rr-competitor__add--remove'
                : full
                    ? 'rr-competitor__add rr-competitor__add--locked'
                    : 'rr-competitor__add';
            const buttonAttr = selected
                ? `data-remove-competitor-list="${esc(item.id)}"`
                : full
                    ? 'disabled'
                    : `data-add-competitor="${esc(item.id)}"`;
            return `
                <div class="rr-competitor">
                    <img src="${esc(item.foto_url || config.logo)}" alt="${esc(item.nome)}">
                    <div>
                        <div class="rr-competitor__name">${esc(item.nome)}</div>
                        <div class="rr-competitor__meta">${esc(item.cidade || item.categoria || 'Disponível')}</div>
                    </div>
                    <button class="${buttonClass}" type="button" ${buttonAttr} aria-label="${esc(selected ? `Remover ${item.nome}` : full ? `${item.nome} bloqueado` : `Selecionar ${item.nome}`)}">${buttonContent}</button>
                </div>`;
        }).join('');
        el.competitorsGrid.querySelectorAll('[data-add-competitor]').forEach((button) => button.addEventListener('click', () => {
            const competitor = state.competitors.find((item) => Number(item.id) === Number(button.dataset.addCompetitor));
            if (!competitor || state.selected.length >= 4) return;
            state.selected.push(competitor);
            state.captainId = state.selected[0]?.id || competitor.id;
            renderSlots();
            renderCompetitors();
        }));
        el.competitorsGrid.querySelectorAll('[data-remove-competitor-list]').forEach((button) => button.addEventListener('click', () => {
            const competitorId = Number(button.dataset.removeCompetitorList);
            state.selected = state.selected.filter((item) => Number(item.id) !== competitorId);
            if (Number(state.captainId) === competitorId) state.captainId = state.selected[0]?.id || null;
            renderSlots();
            renderCompetitors();
        }));
        window.requestAnimationFrame(syncTeamScrollFade);
    }

    async function refreshTeamCompetitors() {
        if (!state.teamLeague) return;
        if (el.refreshCompetitorsButton) {
            el.refreshCompetitorsButton.disabled = true;
            el.refreshCompetitorsButton.innerHTML = '<i class="fas fa-rotate fa-spin"></i><span>Atualizando</span>';
        }
        try {
            const payload = await json(`${config.competitors.replace('__LEAGUE__', state.teamLeague.id)}?only_available=1`);
            state.competitors = Array.isArray(payload.data) ? payload.data : [];
            state.userActiveTeams = Array.isArray(payload.meta?.user_active_teams) ? payload.meta.user_active_teams : [];
            const validIds = new Set(state.competitors.map((item) => Number(item.id)));
            state.selected = state.selected.filter((item) => validIds.has(Number(item.id)));
            state.captainId = state.selected[0]?.id || null;
            renderSlots();
            renderCompetitors();
            hideMessage(el.teamModalFeedback);
            showTeamToast('success', 'Lista atualizada.');
        } catch (error) {
            showTeamToast('error', error.message);
        } finally {
            if (el.refreshCompetitorsButton) {
                el.refreshCompetitorsButton.disabled = false;
                el.refreshCompetitorsButton.innerHTML = '<i class="fas fa-rotate"></i><span>Atualizar</span>';
            }
        }
    }

    async function openTeamModal(leagueId) {
        if (!state.authenticated) {
            openAuthGate();
            return;
        }
        const league = leagueById(leagueId);
        if (!league) return showMessage(el.liveFeedback, 'error', 'Bolão não encontrado.');
        if (!league.entry_enabled || Number(league.available_competitors_count || 0) < minimumCompetitorsToEnterLeague) {
            return showMessage(el.liveFeedback, 'error', 'O bolão precisa ter no mínimo 8 competidores disponíveis para liberar entradas.');
        }
        state.teamLeague = league;
        state.selected = [];
        state.userActiveTeams = [];
        state.captainId = null;
        state.competitors = [];
        el.competitorSearch.value = '';
        hideMessage(el.teamModalFeedback);
        hideTeamToast();
        renderSlots();
        el.competitorsGrid.innerHTML = '<div class="rr-note">Carregando competidores...</div>';
        window.requestAnimationFrame(syncTeamScrollFade);
        openModal(el.teamModal);
        try {
            const payload = await json(`${config.competitors.replace('__LEAGUE__', leagueId)}?only_available=1`);
            state.competitors = Array.isArray(payload.data) ? payload.data : [];
            if (state.competitors.length < minimumCompetitorsToEnterLeague) {
                closeModal(el.teamModal);
                return showMessage(el.liveFeedback, 'error', 'O bolão precisa ter no mínimo 8 competidores disponíveis para liberar entradas.');
            }
            state.userActiveTeams = Array.isArray(payload.meta?.user_active_teams) ? payload.meta.user_active_teams : [];
            renderCompetitors();
        } catch (error) {
            showMessage(el.teamModalFeedback, 'error', error.message);
        }
    }

    async function confirmEntry() {
        if (!state.teamLeague || state.selected.length !== 4) return showMessage(el.teamModalFeedback, 'error', 'Selecione exatamente 4 competidores.');
        hideMessage(el.teamModalFeedback);
        el.confirmTeamButton.disabled = true;
        const competitorIds = state.selected.map((item) => Number(item.id));
        const captainId = Number(state.captainId || competitorIds[0]);
        try {
            const verify = await json(config.verify.replace('__LEAGUE__', state.teamLeague.id), {
                method: 'POST',
                body: JSON.stringify({ competitor_ids: competitorIds }),
                headers: { 'Content-Type': 'application/json' },
            });
            if (!verify.ok) {
                const invalid = (verify.data || []).filter((item) => !item.ok).map((item) => `${item.nome}: ${item.reasons.join(', ')}`);
                throw new Error(invalid[0] || 'A equipe não passou na verificação.');
            }
            const payment = await json(config.pay.replace('__LEAGUE__', state.teamLeague.id), {
                method: 'POST',
                body: JSON.stringify({ competitor_ids: competitorIds, captain_id: captainId, platform: 'web' }),
                headers: { 'Content-Type': 'application/json' },
            });
            if (payment.free_entry) {
                showMessage(el.teamModalFeedback, 'success', payment.message || 'Equipe confirmada com sucesso.');
                await loadLeagues();
                
                // Show profile modal if they haven't filled it out yet
                if (!state.hasRealEmail) {
                    setTimeout(() => openModal(el.profileModal), 800);
                }
            } else {
                closeModal(el.teamModal);
                renderPixModal(payment);
                openModal(el.pixModal);
                
                // Show profile modal if they haven't filled it out yet so they can receive their prize later
                if (!state.hasRealEmail) {
                    setTimeout(() => openModal(el.profileModal), 2500); 
                }
            }
        } catch (error) {
            showMessage(el.teamModalFeedback, 'error', error.message);
        } finally {
            updateConfirmTeamButton();
        }
    }

    function rankingDisplayName(item) {
        return item?.display_name || item?.user_name || item?.username || item?.team_name || 'Usuário';
    }

    function rankingAvatarMarkup(item, className) {
        const name = rankingDisplayName(item);
        const photo = item?.user_foto || item?.avatar || item?.photo_url || '';
        const src = photo || config.logo;
        const imageClass = photo ? 'rr-ranking-avatar-img' : 'rr-ranking-avatar-logo';
        const alt = photo ? name : 'Rei do Rodeio';

        return `<span class="${esc(className)}"><img class="${imageClass}" src="${esc(src)}" alt="${esc(alt)}"></span>`;
    }

    function rankingPointsValue(item) {
        const value = item?.points ?? item?.total_points ?? item?.score;
        if (value === null || value === undefined || value === '') return null;
        return Number(value);
    }

    function rankingPointsLabel(item) {
        const value = rankingPointsValue(item);
        return value === null || Number.isNaN(value) ? '--' : Number(value).toFixed(2);
    }

    function rankingPrizeForPosition(distribution, prizePool, position) {
        const percent = Number(distribution?.[position] || distribution?.[String(position)] || 0);
        if (!percent || !prizePool) return null;
        return (percent / 100) * prizePool;
    }

    function rankingRowTone(position, isMine) {
        if (isMine) return ' rr-ranking-row--mine';
        if (position === 1) return ' rr-ranking-row--gold';
        if (position === 2) return ' rr-ranking-row--silver';
        if (position === 3) return ' rr-ranking-row--bronze';
        return '';
    }

    function renderRankingPodiumCard(item, position, distribution, prizePool, isFinished, prizeItems = {}) {
        const name = item ? rankingDisplayName(item) : "Aguardando...";
        const physicalPrize = physicalPrizeForPosition(prizeItems, position);
        const prize = rankingPrizeForPosition(distribution, prizePool, position);
        const points = item ? rankingPointsLabel(item) : "0.00";
        const showPoints = item && (isFinished || item.is_mine);
        
        return `<div class="rr-podium-v2__slot rr-podium-v2__slot--${position}">
            ${rankingAvatarMarkup(item || {}, "rr-podium-v2__avatar")}
            <div class="rr-podium-v2__base">
                <span class="rr-podium-v2__rank">${position}</span>
                <div class="rr-podium-v2__name">${esc(name)}</div>
                ${physicalPrize ? `<div class="rr-podium-v2__prize">${esc(physicalPrize)}</div>` : (prize > 0 ? `<div class="rr-podium-v2__prize">${esc(money(prize))}</div>` : "")}
                <div class="rr-podium-v2__points">${showPoints ? esc(points) + " pts" : "Oculto"}</div>
            </div>
        </div>`;
    }

    async function renderRankingModalContent(leagueId) {
        const league = leagueById(leagueId);
        if (!league) {
            throw new Error('Bolão não encontrado.');
        }

        const podiumContainer = document.getElementById("rrRankingPodiumContainer");
        const listContainer = document.getElementById("rrRankingList");
        const refreshBtn = document.getElementById("rrRankingRefreshBtn");

        if(podiumContainer) podiumContainer.innerHTML = "<div class=\"rr-podium-wait\"><i class=\"fas fa-spinner fa-spin\"></i>Carregando pódio...</div>";
        if(listContainer) listContainer.innerHTML = "";

        try {
            const payload = await json(config.ranking.replace("__LEAGUE__", leagueId));
            const data = payload.data || {};
            const prizePool = Number(data.prize_pool || 0);
            const distribution = data.distribution || {};
            const prizeItems = data.prize_items || league.prize_items || {};
            const isFinished = league.status === "finished" || league.status === "completed" || data.status === "finished";
            
            const ranking = (Array.isArray(data.ranking) ? data.ranking : [])
                .map((item, index) => ({ ...item, position: Number(item.position || index + 1) }))
                .sort((a, b) => Number(a.position || 0) - Number(b.position || 0));
                
            const topMap = new Map(ranking.slice(0, 3).map((item) => [Number(item.position), item]));
            const podiumHtml = [2, 1, 3].map((pos) => renderRankingPodiumCard(topMap.get(pos) || null, pos, distribution, prizePool, isFinished, prizeItems)).join("");
            
            if(podiumContainer) podiumContainer.innerHTML = podiumHtml || "<div class=\"rr-podium-wait\">Pódio vazio</div>";
            
            const maxUsers = Number(league.max_users || 0);
            const maxPosInRanking = ranking.length > 0 ? ranking[ranking.length - 1].position : 0;
            const prizeItemsCount = Object.keys(normalizePrizeItems(prizeItems)).length;
            const targetPositions = Math.max(maxUsers, maxPosInRanking, prizeItemsCount);
            
            let listRowsHtml = "";
            for (let pos = 4; pos <= targetPositions; pos++) {
                const itemsForPos = ranking.filter(r => r.position === pos);
                if (itemsForPos.length > 0) {
                    listRowsHtml += itemsForPos.map((item) => {
                        const showPointsList = isFinished || item.is_mine;
                        const pointsValList = rankingPointsLabel(item);
                        const physicalPrize = physicalPrizeForPosition(prizeItems, item.position);
                        const listPrize = rankingPrizeForPosition(distribution, prizePool, item.position);
                        return `<div style="display:grid; grid-template-columns: auto 1fr auto; align-items:center; gap:12px; padding:12px; background:rgba(255,255,255,0.03); border-radius:16px;">
                            <div style="width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,0.08); color:#fff; font-weight:800; display:grid; place-items:center; font-size:0.85rem;">${item.position}</div>
                            <div style="display:flex; align-items:center; gap:10px; overflow:hidden;">
                                ${rankingAvatarMarkup(item, "rr-list-avatar")}
                                <div style="display:flex; flex-direction:column;">
                                    <div style="color:#fff; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:0.9rem;">${esc(rankingDisplayName(item))}</div>
                                    ${physicalPrize ? `<div style="color:#4ade80; font-size:0.75rem; font-weight:800;">${esc(physicalPrize)}</div>` : (listPrize > 0 ? `<div style="color:#4ade80; font-size:0.75rem; font-weight:800;">${esc(money(listPrize))}</div>` : "")}
                                </div>
                            </div>
                            <div style="color:#cbd5e1; font-weight:800; font-size:0.85rem;">${showPointsList ? esc(pointsValList) + " pts" : "Oculto"}</div>
                        </div>`;
                    }).join("");
                } else {
                    const physicalPrize = physicalPrizeForPosition(prizeItems, pos);
                    const listPrize = rankingPrizeForPosition(distribution, prizePool, pos);
                    listRowsHtml += `<div style="display:grid; grid-template-columns: auto 1fr auto; align-items:center; gap:12px; padding:12px; background:rgba(255,255,255,0.015); border-radius:16px; opacity: 0.7; border: 1px dashed rgba(255,255,255,0.05);">
                        <div style="width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,0.04); color:#94a3b8; font-weight:800; display:grid; place-items:center; font-size:0.85rem;">${pos}</div>
                        <div style="display:flex; align-items:center; gap:10px; overflow:hidden;">
                            ${rankingAvatarMarkup({}, "rr-list-avatar rr-list-avatar--empty")}
                            <div style="display:flex; flex-direction:column;">
                                <div style="color:#94a3b8; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:0.9rem; font-style:italic;">Posição Disponível</div>
                                ${physicalPrize ? `<div style="color:#10b981; font-size:0.75rem; font-weight:800;">${esc(physicalPrize)}</div>` : (listPrize > 0 ? `<div style="color:#10b981; font-size:0.75rem; font-weight:800;">${esc(money(listPrize))}</div>` : "")}
                            </div>
                        </div>
                        <div style="color:#64748b; font-weight:800; font-size:0.85rem;">-- pts</div>
                    </div>`;
                }
            }

            if(listContainer) {
                listContainer.innerHTML = listRowsHtml || "<div style=\"text-align:center; padding:20px; color:#cbd5e1; font-size:0.85rem;\">Nenhuma outra posição cadastrada.</div>";
            }
        } catch(e) {
             if(podiumContainer) podiumContainer.innerHTML = "<div style=\"color:#fca5a5; padding:20px;\">" + esc(e.message) + "</div>";
             throw e;
        }
    }

    async function openRankingModal(leagueId) {
        state.rankingLeagueId = Number(leagueId) || null;
        openModal(el.rankingModal);
        await renderRankingModalContent(leagueId);
    }

    async function refreshOpenRankingModal() {
        if (!state.rankingLeagueId || !el.rankingModal?.classList.contains('is-open')) {
            return;
        }

        const refreshBtn = document.getElementById('rrRankingRefreshBtn');
        const icon = refreshBtn?.querySelector('i');

        if (refreshBtn) refreshBtn.disabled = true;
        if (icon) icon.classList.add('fa-spin');

        try {
            await renderRankingModalContent(state.rankingLeagueId);
        } finally {
            if (icon) icon.classList.remove('fa-spin');
            if (refreshBtn) refreshBtn.disabled = false;
        }
    }

    async function refreshFrontendSlices(options = {}) {
        const { refreshLeagues = true, refreshRanking = true, refreshCompetitors = true } = options;

        if (refreshLeagues) {
            await loadLeagues(true);
        }

        if (refreshRanking) {
            await refreshOpenRankingModal();
        }

        if (refreshCompetitors && state.teamLeague && el.teamModal?.classList.contains('is-open')) {
            const refreshedLeague = leagueById(state.teamLeague.id);
            if (refreshedLeague) {
                state.teamLeague = refreshedLeague;
            }
            await refreshTeamCompetitors();
        }
    }

    async function handleReminder() {
        if (!el.notifyButton) return;
        const displayEvent = currentEvent();
        const event = isSponsorEvent(displayEvent) ? getHeroControlEvent(displayEvent) : displayEvent;
        if (!event) return;
        if (!state.authenticated) {
            openAuthGate();
            return;
        }
        if (!(await ensureCompleteProfileForNotifications())) {
            openModal(el.profileModal);
            return;
        }
        
        // Pausa o carrossel imediatamente antes de fazer o post
        if (state.carouselTimer) clearInterval(state.carouselTimer);
        
        try {
            const btnSpan = el.notifyButton.querySelector('span');
            btnSpan.textContent = 'Ativando...';
            el.notifyButton.disabled = true;
            
            await json(config.reminder.replace('__RODEIO__', event.id), { method: 'POST', body: JSON.stringify({}), headers: { 'Content-Type': 'application/json' } });
            
            // Mostra o estilo e popout no próprio botão
            el.notifyButton.style.display = '';
            el.notifyButton.classList.add('is-active');
            btnSpan.textContent = 'Aviso ativado!';
            
            // Registra localmente
            state.reminders = state.reminders || [];
            if (!state.reminders.includes(event.id)) {
                state.reminders.push(event.id);
            }
            
            // Aguarda 2.5s para o usuário ler, reconstrói a cena (agora com botão sumido) e retoma
            setTimeout(() => {
                el.notifyButton.disabled = false;
                renderHero();
                startCarousel();
            }, 2500);
            
        } catch (error) {
            showMessage(el.liveFeedback, 'error', error.message);
            el.notifyButton.disabled = false;
            startCarousel();
        }
    }

    async function handleFantasySlotReminder(slotKey, button) {
        const normalizedSlot = normalizeFantasySlotKey(slotKey);
        if (!normalizedSlot || !button) return;

        if (!state.authenticated) {
            openAuthGate();
            return;
        }
        if (!(await ensureCompleteProfileForNotifications())) {
            openModal(el.profileModal);
            return;
        }

        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ativando...';

        try {
            const response = await json(config.fantasyReminder.replace('__SLOT__', encodeURIComponent(normalizedSlot)), {
                method: 'POST',
                body: JSON.stringify({}),
                headers: { 'Content-Type': 'application/json' }
            });

            state.fantasyReminderSlots = Array.from(new Set([...(state.fantasyReminderSlots || []), normalizedSlot]));
            button.classList.add('is-active');
            button.innerHTML = '<i class="fas fa-check"></i> Ativado';
            button.disabled = true;
            hideMessage(el.liveFeedback);
        } catch (error) {
            button.disabled = false;
            button.innerHTML = originalHtml;
            showMessage(el.liveFeedback, 'error', error.message);
        }
    }

    async function handleProfileUpdate(event) {
        event.preventDefault();
        hideMessage(el.profileFeedback);
        const btn = event.currentTarget.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.textContent = 'Salvando...';
        btn.disabled = true;
        try {
            const formData = new FormData(event.currentTarget);
            
            // Format DD/MM/YYYY to YYYY-MM-DD for backend
            const dateStr = formData.get('birth_date');
            if (dateStr && dateStr.length === 10) {
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    formData.set('birth_date', `${parts[2]}-${parts[1]}-${parts[0]}`);
                }
            }

            const response = await json(config.profile, { method: 'POST', body: formData });
            applyProfileData(response?.user || {
                username: formData.get('username') || state.userName,
                has_real_email: state.hasRealEmail,
                profile_complete: true,
            });
            closeModal(el.profileModal);
            showMessage(el.liveFeedback, 'success', response.message || 'Perfil atualizado! Você já pode participar e receber notificações.');
        } catch (error) {
            showMessage(el.profileFeedback, 'error', error.message);
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }

    document.querySelectorAll('[data-auth-mode-trigger]').forEach((button) => button.addEventListener('click', () => setMode(button.dataset.authModeTrigger)));
    document.querySelectorAll('[data-arena-target]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.arenaTarget || 'home';
            const isGatewaySoonTarget = button.closest('#rrArenaGateway') && (target === 'x1' || target === 'stats');

            if (isGatewaySoonTarget) {
                openModal(el.comingSoonModal);
                return;
            }

            showArena(target);
        });
    });
    document.querySelectorAll('.rr-arena-card').forEach((card) => {
        if (isMobileViewport) return;

        card.addEventListener('pointermove', (event) => {
            const rect = card.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / Math.max(rect.width, 1)) * 100;
            const y = ((event.clientY - rect.top) / Math.max(rect.height, 1)) * 100;
            card.style.setProperty('--rr-spot-x', `${x}%`);
            card.style.setProperty('--rr-spot-y', `${y}%`);
        });
    });
    el.authBack.addEventListener('click', () => {
        if (state.mode === 'login' || state.mode === 'register') {
            setMode(null);
            return;
        }
        closeAuthGate();
    });
    if (el.authClose) {
        el.authClose.addEventListener('click', () => closeAuthGate());
    }
    [document.getElementById('rrLoginCpf'), document.getElementById('rrRegisterCpf')].forEach((input) => input && input.addEventListener('input', () => { input.value = formatCpf(input.value); }));
    const birthDateInput = document.getElementById('rrProfileBirthDate');
    if (birthDateInput) birthDateInput.addEventListener('input', () => { birthDateInput.value = formatBirthDate(birthDateInput.value); });
    
    el.loginForm.addEventListener('submit', (event) => submitAuth(event, 'login'));
    el.registerForm.addEventListener('submit', (event) => submitAuth(event, 'register'));
    el.profileForm.addEventListener('submit', handleProfileUpdate);
    modalidadeSelects.forEach((select) => select.addEventListener('change', () => {
        state.modalidadeId = Number(select.value) || null;
        renderAll();
    }));
    if (el.notifyButton) {
        el.notifyButton.addEventListener('click', handleReminder);
    }
    if (el.heroEventName) {
        el.heroEventName.addEventListener('click', () => openSponsorEvent());
        el.heroEventName.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                if (openSponsorEvent()) event.preventDefault();
            }
        });
    }
    if (el.heroLogoWrap) {
        if (isMobileViewport) {
            el.heroLogoWrap.style.setProperty('--rr-logo-mx', '50%');
            el.heroLogoWrap.style.setProperty('--rr-logo-my', '42%');
        } else {
            el.heroLogoWrap.addEventListener('pointermove', (event) => {
                if (!el.heroLogoWrap.classList.contains('is-sponsor-showcase')) return;
                const rect = el.heroLogoWrap.getBoundingClientRect();
                const x = ((event.clientX - rect.left) / rect.width) * 100;
                const y = ((event.clientY - rect.top) / rect.height) * 100;
                el.heroLogoWrap.style.setProperty('--rr-logo-mx', `${x.toFixed(2)}%`);
                el.heroLogoWrap.style.setProperty('--rr-logo-my', `${y.toFixed(2)}%`);
            }, { passive: true });
            el.heroLogoWrap.addEventListener('pointerdown', () => el.heroLogoWrap.classList.add('is-touching'), { passive: true });
            el.heroLogoWrap.addEventListener('pointerup', () => el.heroLogoWrap.classList.remove('is-touching'), { passive: true });
            el.heroLogoWrap.addEventListener('pointerleave', () => {
                el.heroLogoWrap.classList.remove('is-touching');
                el.heroLogoWrap.style.setProperty('--rr-logo-mx', '50%');
                el.heroLogoWrap.style.setProperty('--rr-logo-my', '42%');
            }, { passive: true });
        }
    }

    const rankingRefreshBtn = document.getElementById('rrRankingRefreshBtn');
    if (rankingRefreshBtn) {
        rankingRefreshBtn.addEventListener('click', async () => {
            await refreshFrontendSlices({ refreshLeagues: true, refreshRanking: true, refreshCompetitors: false });
        });
    }

    refreshButtons.forEach((button) => button.addEventListener('click', async () => {
        if (button === el.refreshButtonMobile && mobileRefreshMediaQuery.matches && localStorage.getItem(mobileRefreshPreferenceKey) !== '1') {
            localStorage.setItem(mobileRefreshPreferenceKey, '1');
            syncMobileRefreshButtonMode();
        }

        const icon = button.querySelector('i');
        if (icon) icon.classList.add('fa-spin');
        refreshButtons.forEach((item) => { item.disabled = true; });

        try {
            await refreshFrontendSlices({ refreshLeagues: true, refreshRanking: true, refreshCompetitors: true });
        } finally {
            if (icon) icon.classList.remove('fa-spin');
            refreshButtons.forEach((item) => { item.disabled = false; });
            scheduleMobileRefreshControlsSync();
        }
    }));
    syncMobileRefreshControls();
    window.addEventListener('beforeunload', cancelPixOnPageExit);
    window.addEventListener('pagehide', cancelPixOnPageExit);
    window.addEventListener('scroll', scheduleMobileRefreshControlsSync, { passive: true });
    window.addEventListener('resize', scheduleMobileRefreshControlsSync);
    if (typeof mobileRefreshMediaQuery.addEventListener === 'function') {
        mobileRefreshMediaQuery.addEventListener('change', scheduleMobileRefreshControlsSync);
    } else if (typeof mobileRefreshMediaQuery.addListener === 'function') {
        mobileRefreshMediaQuery.addListener(scheduleMobileRefreshControlsSync);
    }
    el.competitorSearch.addEventListener('input', renderCompetitors);
    if (el.teamScroll) el.teamScroll.addEventListener('scroll', syncTeamScrollFade, { passive: true });
    if (el.refreshCompetitorsButton) el.refreshCompetitorsButton.addEventListener('click', refreshTeamCompetitors);
    el.confirmTeamButton.addEventListener('click', confirmEntry);
    
    if (el.btnCopyPix) {
        el.btnCopyPix.addEventListener('click', async () => {
            const code = state.pixCode || el.pixRawCode?.value || '';
            if (code) {
                const copied = await copyTextValue(code);
                if (copied) {
                    const originalHTML = el.btnCopyPix.innerHTML;
                    el.btnCopyPix.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                    el.btnCopyPix.style.background = '#4ade80';
                    el.btnCopyPix.style.color = '#020617';
                    setTimeout(() => {
                        el.btnCopyPix.innerHTML = originalHTML;
                        el.btnCopyPix.style.background = '';
                        el.btnCopyPix.style.color = '';
                    }, 2500);
                } else if (el.pixModalMeta) {
                    el.pixModalMeta.textContent = 'Não foi possível copiar automaticamente. Tente pressionar e copiar manualmente.';
                }
            }
        });
    }

    if (el.btnVerifyPix) {
        el.btnVerifyPix.addEventListener('click', async () => {
            if (!state.pixPreferenceId) return;

            if (!state.pixDeleteConfirming) {
                setPixDeleteButtonConfirming(true);
                if (el.pixModalMeta) {
                    el.pixModalMeta.textContent = 'Toque novamente em Confirmar exclusão para remover a equipe e liberar a fila.';
                }
                return;
            }

            const originalHTML = el.btnVerifyPix.innerHTML;
            el.btnVerifyPix.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Excluindo...';
            el.btnVerifyPix.disabled = true;
            try {
                await cancelPixReservation({ closeAfter: true });
            } catch (e) {
                if (el.pixModalMeta) {
                    el.pixModalMeta.textContent = e.message;
                }
                showMessage(el.teamModalFeedback, 'error', e.message);
            }
            el.btnVerifyPix.disabled = false;
            if (state.pixPreferenceId) {
                el.btnVerifyPix.innerHTML = originalHTML;
            }
        });
    }

    const openProfileEditor = async () => {
        if (!state.authenticated) {
            openAuthGate();
            return;
        }
        hideMessage(el.profileFeedback);
        try {
            const response = await json(config.profile);
            if (response?.user) {
                applyProfileData(response.user);
            }
        } catch (error) {
            showMessage(el.profileFeedback, 'error', error.message);
        }
        openModal(el.profileModal);
    };
    const openPixWallet = () => {
        if (!state.authenticated) {
            openAuthGate();
            return;
        }
        if (!state.hasRealEmail) {
            openProfileEditor();
        } else {
            el.walletTotalWon.textContent = money(config.auth?.total_won || 0);
            el.walletBalance.textContent = money(config.auth?.balance || 0);
            openModal(el.walletModal);
        }
    };

    // Visitantes podem navegar livremente; ao clicar em botões de ação, abre escolha de autenticação.
    app.addEventListener('click', (event) => {
        if (state.authenticated) return;

        const button = event.target?.closest('button');
        if (!button) return;

        if (button.closest('#rrAuthStage')) return;
        if (button.closest('#rrStatsStage')) return;
        if (button.hasAttribute('data-arena-target')) return;
        if (button.hasAttribute('data-close-modal')) return;
        if (button.hasAttribute('data-open-ranking')) return;
        if (button.id === 'rrRankingRefreshBtn') return;

        event.preventDefault();
        event.stopImmediatePropagation();

        openAuthGate();
        showMessage(el.liveFeedback, 'error', 'Faça login para continuar.');
    }, true);

    if (el.desktopProfile) el.desktopProfile.addEventListener('click', openProfileEditor);
    if (el.mobileProfile) el.mobileProfile.addEventListener('click', openProfileEditor);
    if (el.desktopPix) el.desktopPix.addEventListener('click', openPixWallet);
    if (el.mobilePix) el.mobilePix.addEventListener('click', openPixWallet);
    
    const openRulesModal = () => { openModal(el.rulesModal); };
    if (el.desktopRulesBtn) el.desktopRulesBtn.addEventListener('click', () => { syncRulesModalView('rules'); openRulesModal(); });
    if (el.mobileRulesBtn) el.mobileRulesBtn.addEventListener('click', () => { syncRulesModalView('rules'); openRulesModal(); });
    if (el.comingSoonRegister) {
        el.comingSoonRegister.addEventListener('click', () => {
            closeModal(el.comingSoonModal, { force: true });
            openAuthGate('register');
        });
    }
    if (el.toggleTermsButton) el.toggleTermsButton.addEventListener('click', () => {
        syncRulesModalView(rulesModalView === 'rules' ? 'terms' : 'rules');
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', async () => {
        const modal = document.getElementById(button.dataset.closeModal);
        if (!modal) return;
        if (modal === el.pixModal) {
            await dismissPixModal();
            return;
        }
        closeModal(modal);
    }));
    [el.teamModal, el.rankingModal, el.pixModal, el.walletModal, el.profileModal, el.rulesModal, el.comingSoonModal].forEach((modal) => {
        if (modal) {
            modal.addEventListener('click', async (event) => {
                if (event.target !== modal) return;
                if (modal === el.pixModal) {
                    await dismissPixModal();
                    return;
                }
                closeModal(modal);
            });
        }
    });

    if (state.authenticated) {
        setAuthenticated(true, state.userName);
    } else {
        setAuthenticated(false, state.userName, state.hasRealEmail);
    }

    buildCardsPanelParticles();
    const initialArena = preferredArenaFromUrl();
    if (initialArena !== 'home') {
        const initialMeta = initialArena === 'x1'
            ? 'Preparando duelo X1'
            : (initialArena === 'stats' ? 'Preparando estatísticas premium' : 'Preparando bolão');
        showScreenLoader('Carregando arena', initialMeta);
    } else {
        showScreenLoader('Carregando ambiente seguro', 'Preparando sua experiência');
    }
    showArena(initialArena, false, false);
    (async () => {
        try {
            await cancelPixMarkedForReload();
            await loadLeagues();
        } finally {
            await hideScreenLoader();
        }
    })();
})();
</script>
@endpush
