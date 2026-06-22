@php
    $user = auth()->user();
    $subscriptionService = app(\App\Services\SubscriptionService::class);
    $isPremium = $user && $user->isPremium();
    $canTrial = $user && $subscriptionService->isEligibleForTrial($user);
    $currentSubscription = $user ? $user->getCurrentSubscription() : null;
    $trialReason = null;
    $isActivityLocked = false;

    if ($user && ! $canTrial && ! $isPremium) {
        $trialReason = $subscriptionService->getTrialIneligibilityReason($user);
        $isActivityLocked = $trialReason && \Illuminate\Support\Str::contains($trialReason, 'participou');
    }

    $commandCards = [
        [
            'icon' => 'fa-percent',
            'pill' => 'X1',
            'title' => 'Taxa menor na operação',
            'text' => 'Você mantém mais retorno líquido nas entradas do X1, principalmente quando o giro sobe.',
        ],
        [
            'icon' => 'fa-trophy',
            'pill' => 'Bolão',
            'title' => 'Entrada liberada nas ligas premium',
            'text' => 'Os bolões premium entram no mesmo fluxo do hub, sem cobrança extra de inscrição.',
        ],
        [
            'icon' => 'fa-chart-line',
            'pill' => 'Leitura',
            'title' => 'Mais contexto para decidir',
            'text' => 'Estatísticas, histórico e leitura do rodeio ficam mais fortes antes de você entrar.',
        ],
    ];

    $benefits = [
        [
            'icon' => 'fa-percent',
            'title' => 'Taxa menor no X1',
            'desc' => '7% até R$1.000 e 10% acima disso.',
            'accent' => 'gold',
            'meta' => 'Mais retorno por sala',
        ],
        [
            'icon' => 'fa-trophy',
            'title' => 'Bolão premium grátis',
            'desc' => 'Entre nas ligas premium sem pagar entrada.',
            'accent' => 'blue',
            'meta' => 'Liga premium liberada',
        ],
        [
            'icon' => 'fa-chart-bar',
            'title' => 'Estatísticas completas',
            'desc' => 'Mais leitura antes de decidir a entrada.',
            'accent' => 'orange',
            'meta' => 'Radar mais forte',
        ],
        [
            'icon' => 'fa-door-open',
            'title' => 'Salas exclusivas',
            'desc' => 'Acesso aos ambientes premium da arena.',
            'accent' => 'teal',
            'meta' => 'Ambientes liberados',
        ],
    ];

    $comparisonRows = [
        ['icon' => 'fa-percent', 'feature' => 'Taxa X1', 'free' => '10% / 15%', 'premium' => '7% / 10%'],
        ['icon' => 'fa-trophy', 'feature' => 'Bolão premium', 'free' => 'liga paga', 'premium' => 'liga grátis'],
        ['icon' => 'fa-chart-line', 'feature' => 'Estatísticas', 'free' => 'básica', 'premium' => 'completa'],
        ['icon' => 'fa-crown', 'feature' => 'Salas premium', 'free' => 'não', 'premium' => 'sim'],
    ];

    $faqItems = [
        ['question' => 'Como funciona o teste grátis?', 'answer' => 'Quem for elegível pode ativar 3 dias grátis antes da cobrança do plano.'],
        ['question' => 'Como pago?', 'answer' => 'O checkout abre no modal do próprio hub via Mercado Pago.'],
        ['question' => 'Posso cancelar depois?', 'answer' => 'Nos planos recorrentes, o cancelamento para a próxima renovação.'],
    ];
@endphp

<style>
.rr-premium-landing {
    --bg: #050816;
    --shell: rgba(8, 14, 28, 0.88);
    --card: rgba(13, 22, 43, 0.88);
    --card-strong: rgba(17, 29, 58, 0.95);
    --border: rgba(125, 166, 255, 0.18);
    --text: #f7f8fc;
    --text-soft: #d5dbeb;
    --muted: #94a3b8;
    --orange: #f97316;
    --orange-soft: #fb923c;
    --blue: #2563eb;
    --blue-soft: #60a5fa;
    --green: #22c55e;
    --shadow: 0 28px 70px rgba(3, 7, 18, 0.45);
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.24), transparent 34%),
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.18), transparent 32%),
        linear-gradient(180deg, #060a15 0%, #050816 100%);
    color: var(--text);
    min-height: 100vh;
    padding: 1rem 0 5rem;
    position: relative;
    overflow: hidden;
}
body.light .rr-premium-landing {
    --shell: rgba(255, 250, 244, 0.94);
    --card: rgba(255, 255, 255, 0.92);
    --card-strong: rgba(255, 247, 238, 0.98);
    --border: rgba(194, 65, 12, 0.15);
    --text: #221718;
    --text-soft: #433332;
    --muted: #8a6f68;
    --orange: #ea580c;
    --orange-soft: #fb923c;
    --blue: #1d4ed8;
    --blue-soft: #3b82f6;
    --green: #15803d;
    --shadow: 0 24px 60px rgba(194, 65, 12, 0.12);
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 30%),
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.11), transparent 28%),
        linear-gradient(180deg, #fffdfb 0%, #fff7ee 100%);
}
.rr-premium-container { width: min(1180px, calc(100% - 24px)); margin: 0 auto; }
.rr-premium-shell {
    position: relative; overflow: hidden; border-radius: 32px; padding: 1.25rem;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.08), transparent 22%), linear-gradient(225deg, rgba(37, 99, 235, 0.14), transparent 30%), var(--shell);
    border: 1px solid var(--border); box-shadow: var(--shadow); backdrop-filter: blur(22px);
}
.rr-premium-shell::before, .rr-premium-shell::after { content: ""; position: absolute; border-radius: 999px; pointer-events: none; }
.rr-premium-shell::before { width: 360px; height: 360px; right: -120px; top: -140px; background: radial-gradient(circle, rgba(37, 99, 235, 0.28), transparent 68%); }
.rr-premium-shell::after { width: 320px; height: 320px; left: -130px; bottom: -180px; background: radial-gradient(circle, rgba(249, 115, 22, 0.22), transparent 70%); }
.rr-premium-particles { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0; opacity: 0.8; }
.rr-premium-shell__rail, .rr-premium-hero-grid, .rr-premium-section, .rr-premium-plan-stage, .rr-premium-endgame__grid { position: relative; z-index: 1; }
.rr-premium-shell__rail {
    display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;
    font-size: .72rem; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; color: var(--muted);
}
.rr-premium-shell__rail span:last-child { color: var(--text-soft); }
.rr-premium-hero-grid { display: grid; grid-template-columns: minmax(0,1.08fr) minmax(320px,.92fr); gap: 1rem; }
.rr-premium-hero-copy, .rr-premium-hero-panel, .rr-premium-command__card, .rr-premium-benefit-card, .rr-premium-compare__table, .rr-premium-plan-brief, .rr-premium-plan-card, .rr-premium-faq, .rr-premium-cta-final {
    border: 1px solid var(--border); background: var(--card); border-radius: 28px; backdrop-filter: blur(18px);
}
.rr-premium-hero-copy, .rr-premium-hero-panel, .rr-premium-command__card, .rr-premium-benefit-card, .rr-premium-plan-brief, .rr-premium-plan-card, .rr-premium-faq, .rr-premium-cta-final { padding: 1.25rem; }
.rr-premium-kicker, .rr-premium-section-kicker, .rr-premium-command__pill, .rr-premium-panel__label, .rr-premium-plan-card__eyebrow {
    display: inline-flex; align-items: center; gap: .45rem; width: fit-content; padding: .5rem .85rem; border-radius: 999px;
    font-size: .76rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; background: rgba(249,115,22,.12); color: #ffd7bf; border: 1px solid rgba(249,115,22,.22);
}
body.light .rr-premium-kicker, body.light .rr-premium-section-kicker, body.light .rr-premium-command__pill, body.light .rr-premium-panel__label, body.light .rr-premium-plan-card__eyebrow { color: #b45309; border-color: rgba(194,65,12,.16); }
.rr-premium-hero__title { margin: 0; font-size: clamp(2.5rem, 5vw, 4.6rem); line-height: .94; letter-spacing: -.04em; }
.rr-premium-hero__title span { display: block; }
.rr-premium-hero__title-intro { font-size: 1rem; letter-spacing: .28em; text-transform: uppercase; color: var(--muted); margin-bottom: .85rem; }
.rr-premium-hero__title-main { background: linear-gradient(135deg, #fff3c8 0%, #fb923c 24%, #60a5fa 58%, #dbeafe 100%); -webkit-background-clip: text; background-clip: text; color: transparent; display: inline-block; }
body.light .rr-premium-hero__title-main { background: linear-gradient(135deg, #c2410c 0%, #f97316 32%, #2563eb 100%); -webkit-background-clip: text; background-clip: text; }
.rr-premium-hero__subtitle { margin: 0; max-width: 54ch; color: var(--text-soft); line-height: 1.7; }
.rr-premium-keyword { color: #8cc2ff; font-weight: 800; }
body.light .rr-premium-keyword { color: #c2410c; }
.rr-premium-hero__chips, .rr-premium-cta-final__chips, .rr-premium-plan-card__badges { display: flex; flex-wrap: wrap; gap: .7rem; }
.rr-premium-chip, .rr-premium-cta-final__chip, .rr-premium-plan-card__trial-badge, .rr-premium-plan-card__savings {
    display: inline-flex; align-items: center; gap: .45rem; padding: .7rem .95rem; border-radius: 999px; font-size: .82rem; font-weight: 700;
    color: var(--text); background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
}
body.light .rr-premium-chip, body.light .rr-premium-cta-final__chip, body.light .rr-premium-plan-card__trial-badge, body.light .rr-premium-plan-card__savings { background: rgba(255,255,255,.78); border-color: rgba(194,65,12,.14); }
.rr-premium-hero__cta { display: flex; flex-wrap: wrap; gap: .85rem; align-items: center; }
.rr-premium-btn {
    appearance: none; border: 0; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: .65rem;
    min-height: 52px; padding: .95rem 1.4rem; border-radius: 18px; font-size: .95rem; font-weight: 800; letter-spacing: .02em;
    cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
}
.rr-premium-btn:hover { transform: translateY(-1px); }
.rr-premium-btn--primary { background: linear-gradient(135deg, var(--orange) 0%, var(--blue) 100%); color: #fff; box-shadow: 0 18px 34px rgba(37,99,235,.24); }
.rr-premium-btn--trial { background: linear-gradient(135deg, #fbbf24 0%, var(--orange) 45%, var(--blue) 100%); color: #fff; box-shadow: 0 18px 34px rgba(249,115,22,.3); }
.rr-premium-btn--ghost { background: rgba(255,255,255,.05); color: var(--text); border: 1px solid rgba(255,255,255,.1); }
body.light .rr-premium-btn--ghost { background: rgba(255,255,255,.88); border-color: rgba(194,65,12,.14); }
.rr-premium-btn--large { min-height: 58px; padding: 1rem 1.65rem; }
.rr-premium-btn:disabled, .rr-premium-btn--disabled { cursor: not-allowed; opacity: .72; transform: none; }
.rr-premium-hero__status, .rr-premium-hero__alert {
    display: inline-flex; align-items: center; gap: .75rem; padding: .95rem 1.1rem; border-radius: 18px; font-weight: 700; font-size: .92rem;
}
.rr-premium-hero__status { background: rgba(34,197,94,.12); color: #d8ffe5; border: 1px solid rgba(34,197,94,.28); }
body.light .rr-premium-hero__status { color: #166534; background: rgba(34,197,94,.09); border-color: rgba(22,163,74,.2); }
.rr-premium-hero__status small { display: block; opacity: .84; font-size: .76rem; font-weight: 600; }
.rr-premium-hero__alert { background: rgba(248,113,113,.12); color: #fecaca; border: 1px solid rgba(248,113,113,.24); }
body.light .rr-premium-hero__alert { color: #b91c1c; background: rgba(248,113,113,.08); border-color: rgba(220,38,38,.16); }
.rr-premium-hero__metrics { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: .85rem; }
.rr-premium-metric { padding: 1rem; border-radius: 22px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); min-height: 122px; }
body.light .rr-premium-metric { background: rgba(255,255,255,.76); border-color: rgba(194,65,12,.12); }
.rr-premium-metric span, .rr-premium-plan-card__price-period, .rr-premium-plan-card__price-monthly { display: block; color: var(--muted); }
.rr-premium-metric strong { display: block; margin: .35rem 0 .45rem; font-size: 1.45rem; line-height: 1; text-transform: uppercase; letter-spacing: -.04em; }
.rr-premium-metric p, .rr-premium-section-desc, .rr-premium-command__card p, .rr-premium-benefit-card__desc, .rr-premium-plan-brief p, .rr-premium-cta-final p { margin: 0; color: var(--text-soft); line-height: 1.65; }
.rr-premium-hero-panel { display: flex; flex-direction: column; gap: 1rem; background: linear-gradient(180deg, rgba(37,99,235,.1), transparent 36%), var(--card-strong); }
.rr-premium-panel__hero { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.rr-premium-panel__hero img { width: 78px; height: 78px; object-fit: contain; filter: drop-shadow(0 12px 22px rgba(37,99,235,.24)); }
.rr-premium-panel__headline { margin: 0; font-size: 1.45rem; line-height: 1.1; }
.rr-premium-panel__copy { margin: 0; color: var(--text-soft); line-height: 1.65; }
.rr-premium-panel__list { display: grid; gap: .8rem; }
.rr-premium-panel__item { display: grid; grid-template-columns: auto 1fr auto; gap: .85rem; align-items: start; padding: .95rem 1rem; border-radius: 20px; background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.08); }
body.light .rr-premium-panel__item { background: rgba(255,255,255,.76); border-color: rgba(194,65,12,.12); }
.rr-premium-panel__item i { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 14px; background: linear-gradient(135deg, rgba(249,115,22,.18), rgba(37,99,235,.18)); color: #fff0e6; }
body.light .rr-premium-panel__item i { color: #c2410c; }
.rr-premium-panel__item strong, .rr-premium-plan-card__price-value { font-size: 1.35rem; line-height: 1; letter-spacing: -.05em; }
.rr-premium-panel__item p { margin: .3rem 0 0; color: var(--muted); font-size: .83rem; line-height: 1.45; }
.rr-premium-panel__tag { display: inline-flex; align-items: center; padding: .5rem .75rem; border-radius: 999px; font-size: .76rem; font-weight: 800; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.2); color: #d8ffe5; }
body.light .rr-premium-panel__tag { color: #166534; background: rgba(34,197,94,.09); border-color: rgba(21,128,61,.18); }
.rr-premium-panel__versus { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .85rem; }
.rr-premium-panel__versus-card { padding: 1rem; border-radius: 22px; border: 1px solid var(--border); background: rgba(255,255,255,.04); }
body.light .rr-premium-panel__versus-card { background: rgba(255,255,255,.7); }
.rr-premium-panel__versus-card--premium { border-color: rgba(249,115,22,.28); background: linear-gradient(180deg, rgba(249,115,22,.1), transparent 58%), rgba(255,255,255,.05); }
body.light .rr-premium-panel__versus-card--premium { background: linear-gradient(180deg, rgba(249,115,22,.08), transparent 58%), rgba(255,255,255,.8); }
.rr-premium-panel__versus-card span { display: block; font-size: .76rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--muted); margin-bottom: .7rem; }
.rr-premium-panel__versus-card strong { display: block; margin-bottom: .35rem; font-size: 1.2rem; }
.rr-premium-panel__versus-card p, .rr-premium-panel__foot { margin: 0; color: var(--text-soft); line-height: 1.6; }
.rr-premium-panel__foot { padding: 1rem 1.05rem; border-radius: 20px; background: rgba(255,255,255,.05); border: 1px dashed rgba(255,255,255,.12); font-size: .92rem; }
body.light .rr-premium-panel__foot { background: rgba(255,255,255,.74); border-color: rgba(194,65,12,.16); }
.rr-premium-section { margin-top: 1.4rem; padding: 0 0 .2rem; }
.rr-premium-section-heading { display: grid; gap: .8rem; margin-bottom: 1.15rem; }
.rr-premium-section-title { margin: 0; font-size: clamp(1.7rem, 3vw, 2.5rem); line-height: 1.05; letter-spacing: -.04em; }
.rr-premium-command__grid, .rr-premium-benefits__grid { display: grid; gap: 1rem; }
.rr-premium-command__grid { grid-template-columns: repeat(3, minmax(0,1fr)); }
.rr-premium-benefits__grid, .rr-premium-plans__grid { grid-template-columns: repeat(2, minmax(0,1fr)); display: grid; gap: 1rem; }
.rr-premium-command__card h3, .rr-premium-benefit-card__title, .rr-premium-plan-brief h3, .rr-premium-cta-final h2 { margin: .95rem 0 .65rem; font-size: 1.28rem; line-height: 1.2; letter-spacing: -.03em; }
.rr-premium-benefit-card { position: relative; overflow: hidden; }
.rr-premium-benefit-card::before { content: ""; position: absolute; inset: 0 auto auto 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--orange), var(--blue)); opacity: .84; }
.rr-premium-benefit-card[data-accent="gold"]::before { background: linear-gradient(90deg, #fbbf24, #f97316); }
.rr-premium-benefit-card[data-accent="orange"]::before { background: linear-gradient(90deg, #f97316, #fb923c); }
.rr-premium-benefit-card[data-accent="blue"]::before { background: linear-gradient(90deg, #60a5fa, #2563eb); }
.rr-premium-benefit-card[data-accent="green"]::before { background: linear-gradient(90deg, #22c55e, #16a34a); }
.rr-premium-benefit-card[data-accent="cobalt"]::before { background: linear-gradient(90deg, #2563eb, #1d4ed8); }
.rr-premium-benefit-card[data-accent="teal"]::before { background: linear-gradient(90deg, #14b8a6, #0f766e); }
.rr-premium-benefit-card__icon { width: 52px; height: 52px; display: inline-flex; align-items: center; justify-content: center; border-radius: 16px; font-size: 1.1rem; color: var(--text); background: linear-gradient(135deg, rgba(249,115,22,.16), rgba(37,99,235,.14)); border: 1px solid rgba(255,255,255,.08); }
body.light .rr-premium-benefit-card__icon { color: #c2410c; border-color: rgba(194,65,12,.14); }
.rr-premium-benefit-card__badge { display: inline-flex; align-items: center; gap: .5rem; margin-top: 1rem; padding: .75rem .9rem; border-radius: 16px; background: rgba(255,255,255,.05); color: var(--text); border: 1px solid rgba(255,255,255,.08); font-size: .84rem; font-weight: 700; }
body.light .rr-premium-benefit-card__badge { background: rgba(255,255,255,.76); border-color: rgba(194,65,12,.12); }
.rr-premium-compare__table { overflow: hidden; padding: 0; }
.rr-premium-compare__header, .rr-premium-compare__row { display: grid; grid-template-columns: minmax(0,1.2fr) minmax(0,.8fr) minmax(0,.95fr); }
.rr-premium-compare__header { background: rgba(255,255,255,.04); font-size: .8rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--muted); }
body.light .rr-premium-compare__header { background: rgba(255,255,255,.74); }
.rr-premium-compare__header > div, .rr-premium-compare__row > div { padding: 1rem 1.2rem; }
.rr-premium-compare__row { border-top: 1px solid rgba(255,255,255,.07); }
body.light .rr-premium-compare__row { border-top-color: rgba(194,65,12,.09); }
.rr-premium-compare__feature { display: flex; align-items: center; gap: .7rem; font-weight: 700; color: var(--text); }
.rr-premium-compare__feature i { color: var(--orange-soft); }
.rr-premium-compare__free, .rr-premium-compare__premium { color: var(--text-soft); line-height: 1.55; }
.rr-premium-compare__premium { background: linear-gradient(90deg, rgba(249,115,22,.08), rgba(37,99,235,.08)); font-weight: 700; color: var(--text); }
body.light .rr-premium-compare__premium { background: linear-gradient(90deg, rgba(249,115,22,.06), rgba(37,99,235,.06)); }
.rr-premium-plan-stage, .rr-premium-endgame__grid { display: grid; gap: 1rem; }
.rr-premium-plan-stage { grid-template-columns: minmax(280px,.82fr) minmax(0,1.18fr); }
.rr-premium-trial-banner { display: flex; align-items: center; gap: .8rem; margin-bottom: 1rem; padding: .95rem 1.05rem; border-radius: 18px; background: linear-gradient(135deg, rgba(249,115,22,.14), rgba(37,99,235,.12)); border: 1px solid rgba(249,115,22,.18); font-weight: 700; }
.rr-premium-trial-banner--locked { background: rgba(248,113,113,.12); border-color: rgba(248,113,113,.22); color: #fecaca; }
body.light .rr-premium-trial-banner { border-color: rgba(194,65,12,.14); }
body.light .rr-premium-trial-banner--locked { background: rgba(248,113,113,.08); border-color: rgba(220,38,38,.16); color: #b91c1c; }
.rr-premium-plan-brief__steps { display: grid; gap: .85rem; margin: 1rem 0 0; padding: 0; list-style: none; }
.rr-premium-plan-brief__steps li { display: grid; grid-template-columns: auto 1fr; gap: .75rem; align-items: start; padding: .9rem .95rem; border-radius: 18px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); }
body.light .rr-premium-plan-brief__steps li { background: rgba(255,255,255,.76); border-color: rgba(194,65,12,.12); }
.rr-premium-plan-brief__step-index { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: .9rem; font-weight: 800; background: linear-gradient(135deg, var(--orange), var(--blue)); color: #fff; }
.rr-premium-plan-brief__status, .rr-premium-cta-final__status { margin-top: 1rem; padding: 1rem; border-radius: 18px; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.2); color: #d8ffe5; font-weight: 700; }
body.light .rr-premium-plan-brief__status, body.light .rr-premium-cta-final__status { background: rgba(34,197,94,.08); border-color: rgba(21,128,61,.16); color: #166534; }
.rr-premium-plans__loading, .rr-premium-plans__empty { grid-column: 1 / -1; min-height: 280px; display: grid; place-items: center; gap: .8rem; text-align: center; padding: 1.25rem; border-radius: 24px; border: 1px dashed var(--border); color: var(--text-soft); background: rgba(255,255,255,.03); }
body.light .rr-premium-plans__loading, body.light .rr-premium-plans__empty { background: rgba(255,255,255,.72); }
.spinner { width: 42px; height: 42px; border-radius: 999px; border: 3px solid rgba(255,255,255,.12); border-top-color: var(--orange); animation: premiumSpin .9s linear infinite; }
body.light .spinner { border-color: rgba(194,65,12,.12); border-top-color: var(--blue); }
@keyframes premiumSpin { to { transform: rotate(360deg); } }
.rr-premium-plan-card { position: relative; display: flex; flex-direction: column; gap: 1rem; overflow: hidden; }
.rr-premium-plan-card::before { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(37,99,235,.08), transparent 42%); pointer-events: none; }
.rr-premium-plan-card--featured { border-color: rgba(249,115,22,.28); box-shadow: 0 24px 42px rgba(249,115,22,.12); }
.rr-premium-plan-card__badge { position: absolute; top: 1rem; right: 1rem; padding: .45rem .8rem; border-radius: 999px; color: #fff; font-size: .74rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.rr-premium-plan-card__header { display: flex; justify-content: space-between; gap: 1rem; padding-top: .1rem; }
.rr-premium-plan-card__name { margin: .6rem 0 0; font-size: 1.45rem; line-height: 1.05; letter-spacing: -.04em; }
.rr-premium-plan-card__payment-type { height: fit-content; display: inline-flex; align-items: center; gap: .45rem; padding: .65rem .8rem; border-radius: 16px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); color: var(--text-soft); font-size: .8rem; font-weight: 700; }
body.light .rr-premium-plan-card__payment-type { background: rgba(255,255,255,.78); border-color: rgba(194,65,12,.12); }
.rr-premium-plan-card__price { display: grid; gap: .4rem; }
.rr-premium-plan-card__price-value { font-size: clamp(2rem, 4vw, 2.8rem); }
.rr-premium-plan-card__trial-badge { background: rgba(34,197,94,.13); border-color: rgba(34,197,94,.18); }
body.light .rr-premium-plan-card__trial-badge { color: #166534; }
.rr-premium-plan-card__trial-badge--muted { background: rgba(148,163,184,.12); border-color: rgba(148,163,184,.18); color: #cbd5e1; }
body.light .rr-premium-plan-card__trial-badge--muted { color: #64748b; }
.rr-premium-plan-card__savings { background: rgba(249,115,22,.12); border-color: rgba(249,115,22,.18); }
.rr-premium-plan-card__features { display: grid; gap: .7rem; margin: 0; padding: 0; list-style: none; }
.rr-premium-plan-card__features li { display: flex; align-items: flex-start; gap: .7rem; color: var(--text-soft); line-height: 1.55; }
.rr-premium-plan-card__features li i { color: var(--orange-soft); margin-top: .15rem; }
.rr-premium-plan-card__cancel-info { padding: .85rem .95rem; border-radius: 16px; background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.08); color: var(--text-soft); font-size: .84rem; line-height: 1.5; }
body.light .rr-premium-plan-card__cancel-info { background: rgba(255,255,255,.76); border-color: rgba(194,65,12,.12); }
.rr-premium-plan-card__notice { padding: .85rem .95rem; border-radius: 16px; background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.18); color: #fecaca; font-size: .82rem; line-height: 1.55; }
body.light .rr-premium-plan-card__notice { background: rgba(248,113,113,.08); border-color: rgba(220,38,38,.14); color: #b91c1c; }
.rr-premium-plan-card__cta { width: 100%; }
.rr-premium-endgame__grid { grid-template-columns: minmax(0,1fr) minmax(320px,.88fr); }
.rr-premium-faq__list { display: grid; gap: .8rem; }
.rr-premium-faq__item { border-radius: 18px; border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.04); overflow: hidden; }
body.light .rr-premium-faq__item { border-color: rgba(194,65,12,.12); background: rgba(255,255,255,.76); }
.rr-premium-faq__item summary { cursor: pointer; list-style: none; padding: 1rem 1.05rem; font-weight: 700; color: var(--text); position: relative; }
.rr-premium-faq__item summary::-webkit-details-marker { display: none; }
.rr-premium-faq__item summary::after { content: "+"; position: absolute; right: 1rem; top: .92rem; font-size: 1.4rem; line-height: 1; color: var(--orange-soft); }
.rr-premium-faq__item[open] summary::after { content: "–"; }
.rr-premium-faq__item p { margin: 0; padding: 0 1.05rem 1rem; color: var(--text-soft); line-height: 1.7; }
.rr-premium-cta-final { display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(180deg, rgba(249,115,22,.1), transparent 40%), linear-gradient(225deg, rgba(37,99,235,.12), transparent 36%), var(--card-strong); }
.rr-premium-modal { position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 1rem; }
.rr-premium-modal__backdrop { position: absolute; inset: 0; background: rgba(3,7,18,.72); backdrop-filter: blur(10px); }
.rr-premium-modal__content { position: relative; z-index: 1; width: min(920px,100%); max-height: calc(100vh - 32px); overflow: auto; padding: 1rem; border-radius: 26px; border: 1px solid var(--border); background: var(--card-strong); box-shadow: var(--shadow); }
body.light .rr-premium-modal__content { background: rgba(255,250,244,.98); }
.rr-premium-modal__close { position: absolute; top: .9rem; right: .9rem; width: 42px; height: 42px; border-radius: 14px; border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.06); color: var(--text); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
body.light .rr-premium-modal__close { background: rgba(255,255,255,.9); border-color: rgba(194,65,12,.14); }
.rr-premium-modal__body { min-height: 240px; }
.rr-premium-modal__loading, .rr-premium-modal__success, .rr-premium-modal__error { min-height: 240px; display: grid; place-items: center; text-align: center; gap: .9rem; padding: 2rem 1rem; }
.rr-premium-modal__success i { color: #22c55e; font-size: 2.2rem; }
.rr-premium-modal__error i { color: #ef4444; font-size: 2.2rem; }
.rr-premium-modal__iframe-wrap { width: 100%; height: min(70vh,620px); overflow: hidden; border-radius: 18px; border: 1px solid var(--border); background: #fff; }
.rr-premium-modal__iframe-wrap iframe { width: 100%; height: 100%; border: 0; }
.rr-premium-modal__footer { display: grid; gap: .8rem; margin-top: 1rem; text-align: center; color: var(--text-soft); }
.rr-premium-checkout { display: grid; gap: 1rem; }
.rr-premium-checkout__hero {
    display: grid;
    gap: .9rem;
    padding: 1.1rem;
    border-radius: 22px;
    border: 1px solid rgba(249,115,22,.22);
    background:
        radial-gradient(circle at top right, rgba(37,99,235,.22), transparent 38%),
        linear-gradient(135deg, rgba(249,115,22,.16), rgba(15,23,42,.92));
}
body.light .rr-premium-checkout__hero {
    border-color: rgba(37,99,235,.14);
    background:
        radial-gradient(circle at top right, rgba(37,99,235,.14), transparent 38%),
        linear-gradient(135deg, rgba(249,115,22,.08), rgba(255,255,255,.98));
}
.rr-premium-checkout__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    width: fit-content;
    padding: .5rem .8rem;
    border-radius: 999px;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    background: rgba(255,255,255,.09);
    color: #fff;
    border: 1px solid rgba(255,255,255,.12);
}
body.light .rr-premium-checkout__eyebrow {
    background: rgba(255,255,255,.9);
    color: #1d4ed8;
    border-color: rgba(37,99,235,.14);
}
.rr-premium-checkout__headline {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 1rem;
}
.rr-premium-checkout__headline h3 {
    margin: 0;
    font-size: clamp(1.4rem, 3vw, 2rem);
    line-height: 1;
    letter-spacing: -.04em;
}
.rr-premium-checkout__headline strong {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    line-height: 1;
    letter-spacing: -.05em;
}
.rr-premium-checkout__hero p {
    margin: 0;
    color: var(--text-soft);
    line-height: 1.6;
}
.rr-premium-checkout__summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: .75rem;
}
.rr-premium-checkout__summary-card {
    padding: .9rem .95rem;
    border-radius: 18px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
}
body.light .rr-premium-checkout__summary-card {
    background: rgba(255,255,255,.92);
    border-color: rgba(37,99,235,.1);
}
.rr-premium-checkout__summary-card span {
    display: block;
    margin-bottom: .32rem;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--muted);
}
.rr-premium-checkout__summary-card strong {
    display: block;
    font-size: 1.08rem;
    line-height: 1.2;
}
.rr-premium-checkout__methods {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: .85rem;
}
.rr-premium-checkout__method {
    width: 100%;
    padding: 1rem;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 20px;
    background: rgba(255,255,255,.05);
    color: var(--text);
    text-align: left;
    display: grid;
    gap: .6rem;
    cursor: pointer;
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.rr-premium-checkout__method:hover {
    transform: translateY(-1px);
    border-color: rgba(249,115,22,.28);
    box-shadow: 0 16px 34px rgba(15,23,42,.22);
}
body.light .rr-premium-checkout__method {
    background: rgba(255,255,255,.95);
    border-color: rgba(37,99,235,.1);
}
.rr-premium-checkout__method i {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(249,115,22,.18), rgba(37,99,235,.22));
    color: #fff;
}
.rr-premium-checkout__method strong {
    display: block;
    font-size: 1.05rem;
}
.rr-premium-checkout__method small {
    display: block;
    color: var(--text-soft);
    line-height: 1.5;
}
.rr-premium-checkout__method.is-disabled {
    opacity: .55;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}
.rr-premium-checkout__method.is-disabled:hover {
    transform: none;
    border-color: rgba(255,255,255,.1);
    box-shadow: none;
}
.rr-premium-checkout__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.rr-premium-checkout__back {
    appearance: none;
    border: 0;
    background: transparent;
    color: var(--text-soft);
    font-size: .9rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    cursor: pointer;
    padding: .2rem 0;
}
.rr-premium-checkout__back:hover { color: var(--text); }
.rr-premium-checkout__security {
    color: var(--muted);
    font-size: .8rem;
    display: inline-flex;
    align-items: center;
    gap: .45rem;
}
.rr-premium-checkout__pix {
    display: grid;
    grid-template-columns: minmax(230px,.95fr) minmax(0,1.05fr);
    gap: 1rem;
    align-items: start;
}
.rr-premium-checkout__qr-card,
.rr-premium-checkout__info-card,
.rr-premium-checkout__card-shell {
    padding: 1rem;
    border-radius: 22px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
}
body.light .rr-premium-checkout__qr-card,
body.light .rr-premium-checkout__info-card,
body.light .rr-premium-checkout__card-shell {
    background: rgba(255,255,255,.95);
    border-color: rgba(37,99,235,.1);
}
.rr-premium-checkout__qr-card {
    display: grid;
    gap: .85rem;
    justify-items: center;
}
.rr-premium-checkout__qr-image {
    width: min(100%, 250px);
    aspect-ratio: 1;
    border-radius: 18px;
    background: #fff;
    padding: .75rem;
    border: 1px solid rgba(255,255,255,.08);
}
.rr-premium-checkout__qr-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
.rr-premium-checkout__status {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem .85rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    background: rgba(245,158,11,.16);
    color: #fde68a;
    border: 1px solid rgba(245,158,11,.24);
}
body.light .rr-premium-checkout__status {
    color: #9a3412;
    background: rgba(245,158,11,.1);
    border-color: rgba(245,158,11,.16);
}
.rr-premium-checkout__status.is-approved {
    background: rgba(34,197,94,.16);
    color: #bbf7d0;
    border-color: rgba(34,197,94,.24);
}
body.light .rr-premium-checkout__status.is-approved {
    color: #166534;
    background: rgba(34,197,94,.09);
    border-color: rgba(21,128,61,.16);
}
.rr-premium-checkout__info-card {
    display: grid;
    gap: .9rem;
}
.rr-premium-checkout__info-card h4,
.rr-premium-checkout__card-shell h4 {
    margin: 0;
    font-size: 1.02rem;
}
.rr-premium-checkout__info-card p,
.rr-premium-checkout__card-shell p {
    margin: 0;
    color: var(--text-soft);
    line-height: 1.55;
}
.rr-premium-checkout__code {
    padding: .95rem 1rem;
    border-radius: 18px;
    background: rgba(15,23,42,.58);
    border: 1px dashed rgba(255,255,255,.14);
    color: #f8fafc;
    font-size: .88rem;
    line-height: 1.6;
    word-break: break-word;
}
body.light .rr-premium-checkout__code {
    background: #eff6ff;
    border-color: rgba(37,99,235,.18);
    color: #1e3a8a;
}
.rr-premium-checkout__actions {
    display: flex;
    flex-wrap: wrap;
    gap: .7rem;
}
.rr-premium-checkout__feedback {
    padding: .85rem .95rem;
    border-radius: 16px;
    font-size: .88rem;
    line-height: 1.55;
    background: rgba(59,130,246,.12);
    border: 1px solid rgba(59,130,246,.18);
    color: #bfdbfe;
}
body.light .rr-premium-checkout__feedback {
    background: rgba(59,130,246,.08);
    border-color: rgba(59,130,246,.14);
    color: #1d4ed8;
}
.rr-premium-checkout__feedback.is-error {
    background: rgba(248,113,113,.12);
    border-color: rgba(248,113,113,.18);
    color: #fecaca;
}
body.light .rr-premium-checkout__feedback.is-error {
    background: rgba(248,113,113,.08);
    border-color: rgba(220,38,38,.14);
    color: #b91c1c;
}
.rr-premium-checkout__feedback.is-success {
    background: rgba(34,197,94,.12);
    border-color: rgba(34,197,94,.18);
    color: #bbf7d0;
}
body.light .rr-premium-checkout__feedback.is-success {
    background: rgba(34,197,94,.08);
    border-color: rgba(21,128,61,.14);
    color: #166534;
}
.rr-premium-checkout__card-form {
    display: grid;
    gap: .95rem;
}
.rr-premium-checkout__form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: .85rem;
}
.rr-premium-checkout__field {
    display: grid;
    gap: .38rem;
}
.rr-premium-checkout__field--full { grid-column: 1 / -1; }
.rr-premium-checkout__label {
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .11em;
    text-transform: uppercase;
    color: var(--muted);
}
.rr-premium-checkout__input,
.rr-premium-checkout__hosted,
.rr-premium-checkout__select {
    min-height: 56px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.1);
    background: rgba(255,255,255,.06);
    color: var(--text);
    padding: 0 .95rem;
    width: 100%;
}
.rr-premium-checkout__hosted { padding: .85rem .95rem; }
body.light .rr-premium-checkout__input,
body.light .rr-premium-checkout__hosted,
body.light .rr-premium-checkout__select {
    background: #ffffff;
    border-color: rgba(37,99,235,.12);
    color: #172554;
}
.rr-premium-checkout__input::placeholder { color: var(--muted); }
.rr-premium-checkout__submit { width: 100%; }
.rr-premium-checkout__submit[disabled] { opacity: .7; cursor: wait; }
.rr-premium-checkout__microcopy {
    margin: 0;
    color: var(--muted);
    font-size: .82rem;
}
.rr-premium-checkout__empty-qr {
    width: min(100%, 250px);
    aspect-ratio: 1;
    border-radius: 18px;
    display: grid;
    place-items: center;
    text-align: center;
    padding: 1rem;
    background: rgba(15,23,42,.6);
    color: var(--text-soft);
    border: 1px dashed rgba(255,255,255,.12);
}
body.light .rr-premium-checkout__empty-qr {
    background: #eff6ff;
    color: #1e3a8a;
    border-color: rgba(37,99,235,.18);
}
.rr-premium-scroll-card { transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease; }
@media (max-width: 1180px) { .rr-premium-hero-grid, .rr-premium-plan-stage, .rr-premium-endgame__grid { grid-template-columns: 1fr; } }
@media (max-width: 991px) {
    .rr-premium-command__grid, .rr-premium-benefits__grid, .rr-premium-plans__grid, .rr-premium-hero__metrics { grid-template-columns: 1fr; }
    .rr-premium-shell__rail { flex-direction: column; align-items: flex-start; }
    .rr-premium-compare__header, .rr-premium-compare__row { grid-template-columns: 1fr; }
    .rr-premium-compare__header > div:not(:first-child) { display: none; }
    .rr-premium-compare__free::before, .rr-premium-compare__premium::before { display: block; margin-bottom: .35rem; font-size: .7rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--muted); }
    .rr-premium-compare__free::before { content: "Free"; }
    .rr-premium-compare__premium::before { content: "Premium"; }
    .rr-premium-scroll-card.is-in-focus { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(15,23,42,.22); border-color: rgba(249,115,22,.22); }
}
@media (max-width: 767px) {
    .rr-premium-shell, .rr-premium-hero-copy, .rr-premium-hero-panel, .rr-premium-command__card, .rr-premium-benefit-card, .rr-premium-plan-brief, .rr-premium-plan-card, .rr-premium-faq, .rr-premium-cta-final { border-radius: 24px; }
    .rr-premium-btn, .rr-premium-hero__status, .rr-premium-hero__alert { width: 100%; }
    .rr-premium-hero__cta { flex-direction: column; align-items: stretch; }
    .rr-premium-panel__hero, .rr-premium-plan-card__header { flex-direction: column; align-items: flex-start; }
    .rr-premium-panel__versus { grid-template-columns: 1fr; }
    .rr-premium-modal__content { padding: .85rem; border-radius: 22px; }
    .rr-premium-modal__iframe-wrap { height: min(68vh,540px); }
    .rr-premium-checkout__headline,
    .rr-premium-checkout__row { flex-direction: column; align-items: flex-start; }
    .rr-premium-checkout__summary,
    .rr-premium-checkout__methods,
    .rr-premium-checkout__pix,
    .rr-premium-checkout__form-grid { grid-template-columns: 1fr; }
}
.rr-premium-stage {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 0.92fr) minmax(320px, 1.08fr);
    align-items: center;
    gap: 1.4rem;
    min-height: 420px;
    padding: 0.2rem 0 0.15rem;
    isolation: isolate;
}

.rr-premium-stage::before {
    content: "";
    position: absolute;
    inset: 6% 18% auto auto;
    width: 340px;
    height: 340px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0) 68%);
    filter: blur(10px);
    opacity: 0.85;
    pointer-events: none;
    z-index: 0;
}

.rr-premium-stage__copy,
.rr-premium-stage__visual {
    position: relative;
    z-index: 1;
}

.rr-premium-stage__copy {
    display: grid;
    gap: 1rem;
    align-content: center;
    max-width: 560px;
}

.rr-premium-stage__kicker {
    width: fit-content;
    background: rgba(255, 255, 255, 0.16) !important;
    color: #f8fafc !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
}

.rr-premium-stage__title {
    margin: 0;
    font-size: clamp(3rem, 6vw, 5.7rem);
    line-height: 0.92;
    letter-spacing: -0.08em;
    color: #fff;
    text-wrap: balance;
}

.rr-premium-stage__title span {
    display: block;
    color: #bfdbfe;
}

.rr-premium-stage__lead {
    margin: 0;
    max-width: 34rem;
    color: rgba(239, 246, 255, 0.88);
    font-size: 1.03rem;
    line-height: 1.55;
}

.rr-premium-stage__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    align-items: stretch;
}

.rr-premium-stage__actions .rr-premium-btn {
    min-width: 182px;
}

.rr-premium-stage__actions .rr-premium-hero__status,
.rr-premium-stage__actions .rr-premium-hero__alert {
    margin: 0;
}

.rr-premium-stage__visual {
    min-height: 400px;
}

.rr-premium-stage__logo-wrap {
    position: absolute;
    inset: 50% auto auto 50%;
    transform: translate(-50%, -50%);
    width: min(100%, 460px);
    aspect-ratio: 1 / 1;
    display: grid;
    place-items: center;
}

.rr-premium-stage__logo-wrap::before,
.rr-premium-stage__logo-wrap::after {
    content: "";
    position: absolute;
    inset: 10%;
    border-radius: 999px;
    pointer-events: none;
}

.rr-premium-stage__logo-wrap::before {
    background:
        radial-gradient(circle at 50% 50%, rgba(191, 219, 254, 0.42), rgba(59, 130, 246, 0.14) 44%, rgba(14, 165, 233, 0) 70%);
    filter: blur(12px);
    animation: rrPremiumHaloPulse 5.8s ease-in-out infinite;
}

.rr-premium-stage__logo-wrap::after {
    inset: 19%;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: inset 0 0 0 1px rgba(191, 219, 254, 0.12);
    opacity: 0.82;
    animation: rrPremiumRingDrift 7.2s ease-in-out infinite;
}

.rr-premium-stage__logo {
    position: relative;
    z-index: 2;
    width: clamp(240px, 30vw, 360px);
    max-width: 82%;
    object-fit: contain;
    filter: drop-shadow(0 24px 42px rgba(3, 7, 18, 0.42));
    animation: rrPremiumLogoFloat 4.8s ease-in-out infinite;
}

.rr-premium-stage__logo-badge {
    position: absolute;
    top: 6%;
    left: 12%;
    z-index: 3;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.72rem 1rem;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.16);
    color: #fff7ed;
    font-size: 0.84rem;
    font-weight: 900;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    box-shadow: 0 16px 28px rgba(3, 7, 18, 0.18);
    backdrop-filter: blur(12px);
}

.rr-premium-stage__floaters {
    position: absolute;
    inset: 0;
    z-index: 4;
    pointer-events: none;
}

.rr-premium-floater {
    position: absolute;
    display: grid;
    gap: 0.18rem;
    min-width: 160px;
    padding: 0.8rem 0.95rem;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08));
    box-shadow: 0 18px 32px rgba(3, 7, 18, 0.2);
    color: #eff6ff;
    backdrop-filter: blur(16px);
}

.rr-premium-floater strong {
    font-size: 0.98rem;
    line-height: 1.05;
    letter-spacing: -0.03em;
}

.rr-premium-floater span {
    font-size: 0.77rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(239, 246, 255, 0.78);
}

.rr-premium-floater i {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.2rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
}

.rr-premium-floater--x1 {
    top: 2%;
    right: 6%;
    transform: rotate(-6deg);
    animation: rrPremiumCardFloat 6.5s ease-in-out infinite;
}

.rr-premium-floater--bolao {
    left: 2%;
    bottom: 10%;
    transform: rotate(-4deg);
    animation: rrPremiumCardFloat 7.1s ease-in-out infinite reverse;
}

.rr-premium-floater--stats {
    right: 12%;
    bottom: 3%;
    transform: rotate(5deg);
    animation: rrPremiumCardFloat 6.8s ease-in-out infinite;
}

body.light .rr-premium-stage__kicker {
    background: rgba(255, 255, 255, 0.22) !important;
    color: #eff6ff !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
}

body.light .rr-premium-stage__title,
body.light .rr-premium-stage__title span,
body.light .rr-premium-stage__lead,
body.light .rr-premium-floater,
body.light .rr-premium-floater span {
    color: #f8fbff !important;
}

body.light .rr-premium-floater {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(219, 234, 254, 0.12));
    border-color: rgba(255, 255, 255, 0.18);
}

@keyframes rrPremiumLogoFloat {
    0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
    50% { transform: translate3d(0, -14px, 0) scale(1.02); }
}

@keyframes rrPremiumHaloPulse {
    0%, 100% { opacity: 0.7; transform: scale(0.94); }
    50% { opacity: 1; transform: scale(1.02); }
}

@keyframes rrPremiumRingDrift {
    0%, 100% { transform: scale(0.96) rotate(0deg); opacity: 0.78; }
    50% { transform: scale(1.04) rotate(8deg); opacity: 1; }
}

@keyframes rrPremiumCardFloat {
    0%, 100% { transform: translate3d(0, 0, 0) rotate(var(--rr-premium-rotation, 0deg)); }
    50% { transform: translate3d(0, -10px, 0) rotate(var(--rr-premium-rotation, 0deg)); }
}

.rr-premium-floater--x1 { --rr-premium-rotation: -6deg; }
.rr-premium-floater--bolao { --rr-premium-rotation: -4deg; }
.rr-premium-floater--stats { --rr-premium-rotation: 5deg; }

@media (max-width: 980px) {
    .rr-premium-stage {
        grid-template-columns: 1fr;
        gap: 1rem;
        min-height: 0;
    }

    .rr-premium-stage__visual {
        order: -1;
        min-height: 320px;
    }

    .rr-premium-stage__copy {
        max-width: none;
        text-align: center;
        justify-items: center;
    }

    .rr-premium-stage__actions {
        justify-content: center;
    }

    .rr-premium-stage__logo-wrap {
        position: relative;
        inset: auto;
        transform: none;
        margin: 0 auto;
        width: min(100%, 380px);
    }

    .rr-premium-stage__logo-badge {
        top: 5%;
        left: 10%;
    }

    .rr-premium-floater--x1 {
        top: 2%;
        right: 4%;
    }

    .rr-premium-floater--bolao {
        left: 2%;
        bottom: 4%;
    }

    .rr-premium-floater--stats {
        right: 6%;
        bottom: -1%;
    }
}

@media (max-width: 767px) {
    .rr-premium-stage {
        gap: 0.8rem;
        padding-top: 0.1rem;
    }

    .rr-premium-stage__visual {
        min-height: 260px;
    }

    .rr-premium-stage__logo-wrap {
        width: min(100%, 300px);
    }

    .rr-premium-stage__logo {
        width: min(86%, 245px);
    }

    .rr-premium-stage__logo-badge {
        top: 2%;
        left: 5%;
        padding: 0.58rem 0.8rem;
        font-size: 0.68rem;
        letter-spacing: 0.13em;
    }

    .rr-premium-stage__title {
        font-size: clamp(2.45rem, 13vw, 3.4rem);
    }

    .rr-premium-stage__lead {
        font-size: 0.92rem;
        line-height: 1.45;
    }

    .rr-premium-stage__actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .rr-premium-stage__actions .rr-premium-btn,
    .rr-premium-stage__actions .rr-premium-hero__status,
    .rr-premium-stage__actions .rr-premium-hero__alert {
        width: 100%;
        min-width: 0;
    }

    .rr-premium-floater {
        min-width: 112px;
        padding: 0.58rem 0.72rem;
        border-radius: 16px;
    }

    .rr-premium-floater strong {
        font-size: 0.82rem;
    }

    .rr-premium-floater span {
        font-size: 0.61rem;
        letter-spacing: 0.11em;
    }

    .rr-premium-floater i {
        width: 24px;
        height: 24px;
        font-size: 0.78rem;
    }

    .rr-premium-floater--x1 {
        top: 4%;
        right: 0;
    }

    .rr-premium-floater--bolao {
        left: 0;
        bottom: 12%;
    }

    .rr-premium-floater--stats {
        right: 3%;
        bottom: -2%;
    }
}
</style>

<style>
.rr-premium-landing {
    background: #150e0a !important;
    color: #fff5ec !important;
    padding: 0.75rem 0 3rem !important;
}

body.light .rr-premium-landing {
    background: #fff7f1 !important;
    color: #2b1a12 !important;
}

.rr-premium-shell,
.rr-premium-hero-copy,
.rr-premium-benefit-card,
.rr-premium-compare__table,
.rr-premium-plan-brief,
.rr-premium-plan-card,
.rr-premium-faq,
.rr-premium-modal__content {
    background: #241711 !important;
    border-color: #573628 !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
}

body.light .rr-premium-shell,
body.light .rr-premium-hero-copy,
body.light .rr-premium-benefit-card,
body.light .rr-premium-compare__table,
body.light .rr-premium-plan-brief,
body.light .rr-premium-plan-card,
body.light .rr-premium-faq,
body.light .rr-premium-modal__content {
    background: #fffdf9 !important;
    border-color: #ecd5c5 !important;
}

.rr-premium-shell::before,
.rr-premium-shell::after,
.rr-premium-particles,
.rr-premium-hero-panel,
.rr-premium-command,
.rr-premium-cta-final,
.rr-premium-hero__metrics {
    display: none !important;
}

.rr-premium-shell {
    padding: 1rem !important;
    border-radius: 22px !important;
}

.rr-premium-shell__rail {
    margin-bottom: 0.8rem !important;
    letter-spacing: 0.12em !important;
}

.rr-premium-hero-grid {
    grid-template-columns: 1fr !important;
    gap: 0.9rem !important;
}

.rr-premium-hero-copy,
.rr-premium-benefit-card,
.rr-premium-plan-brief,
.rr-premium-plan-card,
.rr-premium-faq {
    padding: 1rem !important;
    border-radius: 20px !important;
}

.rr-premium-kicker,
.rr-premium-section-kicker,
.rr-premium-command__pill,
.rr-premium-panel__label,
.rr-premium-plan-card__eyebrow {
    background: #ffe1bf !important;
    color: #cf5c08 !important;
    border-color: #f0cfa6 !important;
}

.rr-premium-hero__title {
    font-size: clamp(2rem, 4vw, 3rem) !important;
}

.rr-premium-hero__title-main {
    background: none !important;
    color: #f07b1f !important;
    -webkit-text-fill-color: currentColor !important;
}

.rr-premium-keyword {
    color: #f07b1f !important;
    text-shadow: none !important;
    animation: none !important;
}

.rr-premium-hero__subtitle,
.rr-premium-section-desc,
.rr-premium-benefit-card__desc,
.rr-premium-plan-brief p,
.rr-premium-faq__item p,
.rr-premium-compare__free,
.rr-premium-plan-card__cancel-info,
.rr-premium-plan-card__payment-type,
.rr-premium-plan-card__features li {
    color: #dfc6b2 !important;
}

body.light .rr-premium-hero__subtitle,
body.light .rr-premium-section-desc,
body.light .rr-premium-benefit-card__desc,
body.light .rr-premium-plan-brief p,
body.light .rr-premium-faq__item p,
body.light .rr-premium-compare__free,
body.light .rr-premium-plan-card__cancel-info,
body.light .rr-premium-plan-card__payment-type,
body.light .rr-premium-plan-card__features li {
    color: #6f5446 !important;
}

.rr-premium-chip,
.rr-premium-plan-card__trial-badge,
.rr-premium-plan-card__savings,
.rr-premium-plan-card__payment-type,
.rr-premium-plan-card__cancel-info,
.rr-premium-plan-brief__steps li,
.rr-premium-faq__item,
.rr-premium-compare__header {
    background: #362117 !important;
    border-color: #573628 !important;
}

body.light .rr-premium-chip,
body.light .rr-premium-plan-card__trial-badge,
body.light .rr-premium-plan-card__savings,
body.light .rr-premium-plan-card__payment-type,
body.light .rr-premium-plan-card__cancel-info,
body.light .rr-premium-plan-brief__steps li,
body.light .rr-premium-faq__item,
body.light .rr-premium-compare__header {
    background: #ffead8 !important;
    border-color: #ecd5c5 !important;
}

.rr-premium-benefits__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 0.85rem !important;
}

.rr-premium-benefits__grid > :nth-child(n+5),
.rr-premium-faq__list > :nth-child(n+4) {
    display: none !important;
}

.rr-premium-section.rr-premium-compare .rr-premium-section-desc {
    display: none !important;
}

.rr-premium-plan-stage {
    grid-template-columns: minmax(220px, 0.82fr) minmax(0, 1.18fr) !important;
    gap: 0.85rem !important;
}

.rr-premium-plan-card::before {
    display: none !important;
}

.rr-premium-btn--primary {
    background: #f07b1f !important;
    box-shadow: none !important;
}

.rr-premium-btn--trial {
    background: linear-gradient(180deg, #ffb86f 0%, #f07b1f 100%) !important;
    box-shadow: none !important;
}

.rr-premium-btn--ghost {
    background: #362117 !important;
    border-color: #573628 !important;
}

body.light .rr-premium-btn--ghost {
    background: #ffead8 !important;
    border-color: #ecd5c5 !important;
}

@media (max-width: 900px) {
    .rr-premium-plan-stage,
    .rr-premium-benefits__grid,
    .rr-premium-plans__grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 767px) {
    .rr-premium-container {
        width: min(100%, calc(100% - 12px)) !important;
    }

    .rr-premium-shell {
        padding: 0.75rem !important;
        border-radius: 18px !important;
    }

    .rr-premium-shell__rail {
        display: none !important;
    }

    .rr-premium-hero-copy,
    .rr-premium-benefit-card,
    .rr-premium-plan-brief,
    .rr-premium-plan-card,
    .rr-premium-faq,
    .rr-premium-compare__table {
        padding: 0.9rem !important;
        border-radius: 18px !important;
    }

    .rr-premium-hero__chips,
    .rr-premium-hero__cta {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .rr-premium-btn,
    .rr-premium-hero__status,
    .rr-premium-hero__alert {
        width: 100% !important;
    }

    .rr-premium-plan-card__header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }
}
</style>

<style>
.rr-premium-landing {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.18), transparent 32%),
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.12), transparent 26%),
        linear-gradient(180deg, #071022 0%, #040914 100%) !important;
    color: #f8fbff !important;
}

body.light .rr-premium-landing {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 32%),
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.08), transparent 26%),
        linear-gradient(180deg, #fffdfb 0%, #f8fbff 100%) !important;
    color: #1e293b !important;
}

.rr-premium-shell {
    background:
        radial-gradient(circle at top right, rgba(96, 165, 250, 0.32), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.18), transparent 24%),
        linear-gradient(135deg, #0f3ccf 0%, #09288a 44%, #03133f 100%) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 28px 70px rgba(3, 10, 30, 0.34) !important;
    padding: 1.25rem !important;
    border-radius: 30px !important;
}

body.light .rr-premium-shell {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.22), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.14), transparent 24%),
        linear-gradient(135deg, #dbeafe 0%, #93c5fd 35%, #1d4ed8 100%) !important;
    border-color: rgba(30, 64, 175, 0.12) !important;
    box-shadow: 0 24px 60px rgba(37, 99, 235, 0.08) !important;
}

.rr-premium-shell::before,
.rr-premium-shell::after,
.rr-premium-particles {
    display: none !important;
}

.rr-premium-shell__rail,
.rr-premium-command,
.rr-premium-benefits,
.rr-premium-compare,
.rr-premium-endgame,
.rr-premium-plan-brief,
.rr-premium-hero__metrics,
.rr-premium-panel__versus,
.rr-premium-panel__foot {
    display: none !important;
}

.rr-premium-hero-grid {
    grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr) !important;
    gap: 1rem !important;
}

.rr-premium-hero-panel {
    display: flex !important;
    flex-direction: column;
    gap: 1rem;
}

.rr-premium-hero-copy,
.rr-premium-hero-panel,
.rr-premium-plan-card,
.rr-premium-modal__content {
    background: rgba(8, 16, 44, 0.28) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    backdrop-filter: blur(16px) !important;
    box-shadow: none !important;
}

body.light .rr-premium-hero-copy,
body.light .rr-premium-hero-panel,
body.light .rr-premium-plan-card,
body.light .rr-premium-modal__content {
    background: rgba(255, 255, 255, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.42) !important;
}

.rr-premium-hero-copy,
.rr-premium-hero-panel {
    padding: 1.2rem !important;
}

.rr-premium-kicker,
.rr-premium-panel__label,
.rr-premium-section-kicker,
.rr-premium-plan-card__eyebrow {
    background: rgba(255, 255, 255, 0.14) !important;
    color: #fff7ed !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
}

body.light .rr-premium-kicker,
body.light .rr-premium-panel__label,
body.light .rr-premium-section-kicker,
body.light .rr-premium-plan-card__eyebrow {
    background: rgba(255, 255, 255, 0.74) !important;
    color: #1e3a8a !important;
    border-color: rgba(255, 255, 255, 0.44) !important;
}

.rr-premium-hero__title {
    color: #ffffff !important;
    font-size: clamp(2.5rem, 5vw, 4.4rem) !important;
}

.rr-premium-hero__title-main {
    background: none !important;
    color: #ffffff !important;
    -webkit-text-fill-color: currentColor !important;
    letter-spacing: -0.06em;
}

body.light .rr-premium-hero__title,
body.light .rr-premium-hero__title-main {
    color: #eff6ff !important;
}

.rr-premium-hero__title-intro,
.rr-premium-hero__subtitle,
.rr-premium-panel__copy,
.rr-premium-plan-card__payment-type,
.rr-premium-plan-card__features li,
.rr-premium-plan-card__cancel-info,
.rr-premium-section-desc {
    color: rgba(239, 246, 255, 0.84) !important;
}

body.light .rr-premium-hero__title-intro,
body.light .rr-premium-hero__subtitle,
body.light .rr-premium-panel__copy,
body.light .rr-premium-plan-card__payment-type,
body.light .rr-premium-plan-card__features li,
body.light .rr-premium-plan-card__cancel-info,
body.light .rr-premium-section-desc {
    color: rgba(239, 246, 255, 0.9) !important;
}

.rr-premium-keyword {
    color: #facc15 !important;
}

body.light .rr-premium-keyword {
    color: #fef3c7 !important;
}

.rr-premium-chip,
.rr-premium-panel__item,
.rr-premium-plan-card__payment-type,
.rr-premium-plan-card__trial-badge,
.rr-premium-plan-card__savings,
.rr-premium-plan-card__cancel-info {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}

body.light .rr-premium-chip,
body.light .rr-premium-panel__item,
body.light .rr-premium-plan-card__payment-type,
body.light .rr-premium-plan-card__trial-badge,
body.light .rr-premium-plan-card__savings,
body.light .rr-premium-plan-card__cancel-info {
    background: rgba(255, 255, 255, 0.52) !important;
    border-color: rgba(255, 255, 255, 0.42) !important;
}

.rr-premium-panel__hero {
    align-items: center !important;
}

.rr-premium-panel__hero img {
    width: 128px !important;
    height: 128px !important;
    filter: drop-shadow(0 18px 28px rgba(3, 7, 18, 0.34));
}

.rr-premium-panel__item strong,
.rr-premium-panel__headline,
.rr-premium-plan-card__name,
.rr-premium-plan-card__price-value,
.rr-premium-section-title {
    color: #ffffff !important;
}

body.light .rr-premium-panel__item strong,
body.light .rr-premium-panel__headline,
body.light .rr-premium-plan-card__name,
body.light .rr-premium-plan-card__price-value,
body.light .rr-premium-section-title {
    color: #eff6ff !important;
}

.rr-premium-section.rr-premium-plans {
    margin-top: 1rem !important;
}

.rr-premium-plan-stage {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
}

.rr-premium-plans__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 1rem !important;
}

.rr-premium-plans__mini-grid {
    display: none;
}

.rr-premium-plans__mini-card {
    appearance: none;
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.1);
    border-radius: 18px;
    padding: 0.78rem 0.65rem;
    display: grid;
    gap: 0.22rem;
    text-align: left;
    color: #ffffff;
    cursor: pointer;
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
}

.rr-premium-plans__mini-card:hover,
.rr-premium-plans__mini-card:focus-visible {
    transform: translateY(-1px);
    border-color: rgba(249, 115, 22, 0.34);
    box-shadow: 0 10px 22px rgba(2, 6, 23, 0.14);
    outline: none;
}

.rr-premium-plans__mini-period {
    display: block;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(239, 246, 255, 0.72);
}

.rr-premium-plans__mini-price {
    display: block;
    font-size: 1.05rem;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.04em;
}

.rr-premium-plan-card.is-plan-focus {
    border-color: rgba(249, 115, 22, 0.38) !important;
    box-shadow: 0 18px 34px rgba(249, 115, 22, 0.18) !important;
}

.rr-premium-plan-card__badge,
.rr-premium-plan-card__badges {
    display: none !important;
}

body.light .rr-premium-shell {
    background:
        radial-gradient(circle at top right, rgba(59, 130, 246, 0.18), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.1), transparent 24%),
        linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%) !important;
}

body.light .rr-premium-hero-copy,
body.light .rr-premium-hero-panel,
body.light .rr-premium-plan-card,
body.light .rr-premium-modal__content {
    background: rgba(255, 255, 255, 0.94) !important;
    border-color: rgba(30, 64, 175, 0.1) !important;
}

body.light .rr-premium-kicker,
body.light .rr-premium-panel__label,
body.light .rr-premium-section-kicker,
body.light .rr-premium-plan-card__eyebrow,
body.light .rr-premium-chip,
body.light .rr-premium-panel__item,
body.light .rr-premium-plan-card__payment-type,
body.light .rr-premium-plan-card__cancel-info {
    background: rgba(239, 246, 255, 0.96) !important;
    border-color: rgba(30, 64, 175, 0.1) !important;
    color: #1e3a8a !important;
}

body.light .rr-premium-hero__title,
body.light .rr-premium-hero__title-main,
body.light .rr-premium-panel__headline,
body.light .rr-premium-panel__item strong,
body.light .rr-premium-plan-card__name,
body.light .rr-premium-plan-card__price-value,
body.light .rr-premium-section-title {
    color: #172554 !important;
}

body.light .rr-premium-hero__title-intro,
body.light .rr-premium-hero__subtitle,
body.light .rr-premium-panel__copy,
body.light .rr-premium-panel__item p,
body.light .rr-premium-plan-card__payment-type,
body.light .rr-premium-plan-card__features li,
body.light .rr-premium-plan-card__cancel-info,
body.light .rr-premium-plan-card__price-period,
body.light .rr-premium-plan-card__price-monthly,
body.light .rr-premium-section-desc {
    color: #475569 !important;
}

body.light .rr-premium-keyword,
body.light .rr-premium-plan-card__features li i {
    color: #ea580c !important;
}

body.light .rr-premium-plans__mini-card {
    background: rgba(255, 255, 255, 0.96) !important;
    border-color: rgba(30, 64, 175, 0.1) !important;
    color: #172554 !important;
}

body.light .rr-premium-plans__mini-period {
    color: #475569 !important;
}

body.light .rr-premium-plan-card.is-plan-focus {
    border-color: rgba(37, 99, 235, 0.28) !important;
    box-shadow: 0 18px 34px rgba(37, 99, 235, 0.12) !important;
}

@media (max-width: 980px) {
    .rr-premium-hero-grid,
    .rr-premium-plans__grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 767px) {
    .rr-premium-shell {
        padding: 0.9rem !important;
        border-radius: 24px !important;
    }

    .rr-premium-plans__mini-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.55rem;
        margin: 0 0 0.85rem;
    }

    .rr-premium-hero-copy,
    .rr-premium-hero-panel,
    .rr-premium-plan-card {
        padding: 1rem !important;
        border-radius: 22px !important;
    }

    .rr-premium-panel__hero img {
        width: 98px !important;
        height: 98px !important;
    }

    .rr-premium-hero__chips,
    .rr-premium-hero__cta {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .rr-premium-btn,
    .rr-premium-hero__status,
    .rr-premium-hero__alert {
        width: 100% !important;
    }

    .rr-premium-modal__content {
        width: min(420px, calc(100vw - 12px)) !important;
        padding: 0.7rem !important;
        border-radius: 18px !important;
    }

    .rr-premium-modal__close {
        top: 0.55rem !important;
        right: 0.55rem !important;
        width: 36px !important;
        height: 36px !important;
        border-radius: 12px !important;
    }

    .rr-premium-checkout {
        gap: 0.7rem !important;
    }

    .rr-premium-checkout__hero,
    .rr-premium-checkout__card-shell,
    .rr-premium-checkout__qr-card,
    .rr-premium-checkout__info-card {
        padding: 0.75rem !important;
        border-radius: 18px !important;
    }

    .rr-premium-checkout__headline {
        gap: 0.35rem !important;
    }

    .rr-premium-checkout__headline h3 {
        font-size: 1.02rem !important;
    }

    .rr-premium-checkout__headline strong {
        font-size: 1.3rem !important;
    }

    .rr-premium-checkout__method {
        min-height: 48px !important;
        padding: 0.72rem 0.8rem !important;
        border-radius: 16px !important;
        gap: 0.55rem !important;
    }

    .rr-premium-checkout__method i {
        width: 34px !important;
        height: 34px !important;
        border-radius: 11px !important;
        font-size: 0.9rem !important;
    }

    .rr-premium-checkout__method strong {
        font-size: 0.9rem !important;
    }

    .rr-premium-checkout__row {
        gap: 0.45rem !important;
    }

    .rr-premium-checkout__row h4 {
        font-size: 0.92rem !important;
    }

    .rr-premium-checkout__back {
        font-size: 0.82rem !important;
    }

    .rr-premium-checkout__form-grid {
        gap: 0.55rem !important;
    }

    .rr-premium-checkout__field {
        gap: 0.25rem !important;
    }

    .rr-premium-checkout__label {
        font-size: 0.62rem !important;
        letter-spacing: 0.08em !important;
    }

    .rr-premium-checkout__input,
    .rr-premium-checkout__hosted,
    .rr-premium-checkout__select {
        min-height: 46px !important;
        border-radius: 13px !important;
        padding: 0 0.78rem !important;
        font-size: 0.92rem !important;
    }

    .rr-premium-checkout__hosted {
        padding: 0.62rem 0.78rem !important;
    }

    .rr-premium-checkout__hosted iframe {
        min-height: 32px !important;
        height: 32px !important;
    }

    .rr-premium-checkout__feedback {
        padding: 0.7rem 0.78rem !important;
        border-radius: 13px !important;
        font-size: 0.8rem !important;
        line-height: 1.4 !important;
    }

    .rr-premium-checkout__code {
        padding: 0.72rem 0.78rem !important;
        border-radius: 14px !important;
        font-size: 0.78rem !important;
        line-height: 1.45 !important;
    }

    .rr-premium-checkout__actions {
        gap: 0.5rem !important;
    }

    .rr-premium-checkout__actions .rr-premium-btn,
    .rr-premium-checkout__submit {
        min-height: 46px !important;
        padding: 0.72rem 0.9rem !important;
        border-radius: 14px !important;
        font-size: 0.88rem !important;
    }

    .rr-premium-checkout__qr-image,
    .rr-premium-checkout__empty-qr {
        width: min(100%, 190px) !important;
        border-radius: 14px !important;
        padding: 0.6rem !important;
    }

    .rr-premium-checkout__status {
        padding: 0.48rem 0.72rem !important;
        font-size: 0.68rem !important;
        letter-spacing: 0.08em !important;
    }
}

.rr-premium-plan-brief {
    display: none !important;
}

.rr-premium-plan-stage {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
}

.rr-premium-plans__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 1rem !important;
    align-items: stretch;
}

.rr-premium-plans__mini-card {
    position: relative;
    overflow: hidden;
    border-radius: 18px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08)) !important;
    border: 1px solid rgba(255, 255, 255, 0.16) !important;
    box-shadow: 0 16px 28px rgba(3, 7, 18, 0.14);
}

.rr-premium-plans__mini-card::before {
    content: "";
    position: absolute;
    inset: auto -18% -55% auto;
    width: 90px;
    height: 90px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.34), rgba(59, 130, 246, 0) 70%);
    pointer-events: none;
}

.rr-premium-plan-card {
    position: relative;
    display: grid;
    gap: 0.9rem;
    min-height: 100%;
    padding: 1.15rem !important;
    overflow: hidden;
    border-radius: 28px !important;
    border: 1px solid rgba(255, 255, 255, 0.16) !important;
    background:
        radial-gradient(circle at 84% 16%, rgba(96, 165, 250, 0.24), transparent 28%),
        radial-gradient(circle at 12% 88%, rgba(249, 115, 22, 0.14), transparent 24%),
        linear-gradient(135deg, #0f3ccf 0%, #09288a 44%, #03133f 100%) !important;
    box-shadow: 0 26px 46px rgba(3, 10, 30, 0.28) !important;
    backdrop-filter: blur(18px) !important;
    isolation: isolate;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.rr-premium-plan-card:hover {
    transform: translateY(-4px);
}

.rr-premium-plan-card::before,
.rr-premium-plan-card::after {
    content: "";
    position: absolute;
    pointer-events: none;
    z-index: 0;
}

.rr-premium-plan-card::before {
    inset: -18% -10% auto auto;
    width: 180px;
    height: 180px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(147, 197, 253, 0.36), rgba(147, 197, 253, 0) 70%);
    opacity: 0.9;
}

.rr-premium-plan-card::after {
    inset: auto auto -28% -8%;
    width: 170px;
    height: 170px;
    border-radius: 999px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.28), rgba(249, 115, 22, 0) 72%);
    opacity: 0.95;
}

.rr-premium-plan-card > * {
    position: relative;
    z-index: 1;
}

.rr-premium-plan-card__crowns {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
    opacity: 0.95;
}

.rr-premium-plan-card__crowns span {
    position: absolute;
    top: -18%;
    left: var(--crown-left, 50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 244, 214, 0.2);
    font-size: var(--crown-size, 1rem);
    transform: translateX(-50%);
    animation: rrPremiumCrownRain var(--crown-duration, 7.2s) linear infinite;
    animation-delay: var(--crown-delay, 0s);
}

.rr-premium-plan-card__crowns span:nth-child(2n) {
    color: rgba(191, 219, 254, 0.18);
}

.rr-premium-plan-card__crowns span:nth-child(3n) {
    color: rgba(249, 115, 22, 0.18);
}

.rr-premium-plan-card--tone-1::before {
    background: radial-gradient(circle, rgba(96, 165, 250, 0.42), rgba(96, 165, 250, 0) 70%);
}

.rr-premium-plan-card--tone-2::before {
    background: radial-gradient(circle, rgba(251, 191, 36, 0.35), rgba(251, 191, 36, 0) 70%);
}

.rr-premium-plan-card--tone-3::before {
    background: radial-gradient(circle, rgba(34, 197, 94, 0.32), rgba(34, 197, 94, 0) 70%);
}

.rr-premium-plan-card--featured {
    border-color: rgba(249, 115, 22, 0.34) !important;
    box-shadow: 0 28px 46px rgba(249, 115, 22, 0.18) !important;
}

.rr-premium-plan-card__topline {
    display: flex;
    justify-content: space-between;
    gap: 0.85rem;
    align-items: flex-start;
}

.rr-premium-plan-card__identity {
    display: grid;
    gap: 0.7rem;
}

.rr-premium-plan-card__orb {
    width: 54px;
    height: 54px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    color: #fff7ed;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08));
    border: 1px solid rgba(255, 255, 255, 0.16);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    font-size: 1rem;
}

.rr-premium-plan-card__hero {
    display: grid;
    gap: 0.45rem;
}

.rr-premium-plan-card__name {
    margin: 0 !important;
    font-size: 1.32rem;
    line-height: 1.02;
    letter-spacing: -0.05em;
    max-width: 18ch;
}

.rr-premium-plan-card__price {
    display: grid;
    gap: 0.28rem;
    margin-top: 0.1rem;
}

.rr-premium-plan-card__price-value {
    font-size: clamp(2.35rem, 4vw, 3.2rem) !important;
    line-height: 0.92;
    letter-spacing: -0.08em;
}

.rr-premium-plan-card__price-period {
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: rgba(239, 246, 255, 0.84);
}

.rr-premium-plan-card__price-monthly {
    font-size: 0.92rem;
    font-weight: 700;
}

.rr-premium-plan-card__payment-type {
    width: fit-content;
    max-width: 100%;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.52rem 0.7rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.11em;
    text-transform: uppercase;
    opacity: 0.86;
}

.rr-premium-plan-card__payment-type i {
    width: 22px;
    height: 22px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    font-size: 0.68rem;
}

.rr-premium-plan-card__features {
    display: grid;
    gap: 0.55rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.rr-premium-plan-card__features li {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.58rem;
    align-items: start;
    padding: 0.72rem 0.85rem;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.rr-premium-plan-card__features li i {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 0;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.14);
}

.rr-premium-plan-card__cancel-info {
    padding: 0.82rem 0.95rem !important;
    border-radius: 18px !important;
    font-size: 0.83rem;
    line-height: 1.48;
}

.rr-premium-plan-card__benefit-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255, 244, 214, 0.82);
}

.rr-premium-plan-card__benefit-kicker i {
    color: #fbbf24;
}

.rr-premium-plan-card__actions {
    display: grid;
    gap: 0.65rem;
    margin-top: auto;
}

.rr-premium-plan-card__cta {
    width: 100%;
    min-height: 54px;
    border-radius: 18px !important;
    position: relative;
    overflow: hidden;
}

.rr-premium-plan-card__cta--primary {
    box-shadow: 0 18px 30px rgba(249, 115, 22, 0.22) !important;
    animation: rrPremiumButtonPulse 3.2s ease-in-out infinite;
}

.rr-premium-plan-card__cta--primary::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(110deg, transparent 12%, rgba(255, 255, 255, 0.28) 42%, transparent 68%);
    transform: translateX(-125%);
    animation: rrPremiumButtonSheen 2.8s ease-in-out infinite;
}

.rr-premium-plan-card__cta--primary i,
.rr-premium-plan-card__cta--primary span {
    position: relative;
    z-index: 1;
}

.rr-premium-plan-card__cta--ghost {
    opacity: 0.94;
}

.rr-premium-plan-card__notice {
    border-radius: 18px !important;
}

body.light .rr-premium-plan-card {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(239, 246, 255, 0.88)) !important;
    border-color: rgba(30, 64, 175, 0.12) !important;
    box-shadow: 0 24px 42px rgba(37, 99, 235, 0.1) !important;
}

body.light .rr-premium-plan-card::before {
    background: radial-gradient(circle, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0) 70%);
}

body.light .rr-premium-plan-card::after {
    background: radial-gradient(circle, rgba(249, 115, 22, 0.16), rgba(249, 115, 22, 0) 72%);
}

body.light .rr-premium-plan-card--featured {
    border-color: rgba(249, 115, 22, 0.22) !important;
    box-shadow: 0 26px 44px rgba(249, 115, 22, 0.12) !important;
}

body.light .rr-premium-plan-card__orb,
body.light .rr-premium-plan-card__payment-type,
body.light .rr-premium-plan-card__features li,
body.light .rr-premium-plan-card__cancel-info {
    background: rgba(255, 255, 255, 0.72) !important;
    border-color: rgba(30, 64, 175, 0.1) !important;
    color: #1e3a8a !important;
}

body.light .rr-premium-plan-card__payment-type i {
    background: rgba(219, 234, 254, 0.92);
}

body.light .rr-premium-plan-card__name,
body.light .rr-premium-plan-card__price-value {
    color: #172554 !important;
}

body.light .rr-premium-plan-card__price-period,
body.light .rr-premium-plan-card__price-monthly,
body.light .rr-premium-plan-card__features li span,
body.light .rr-premium-plan-card__cancel-info {
    color: #475569 !important;
}

body.light .rr-premium-plan-card__benefit-kicker {
    color: #1e3a8a !important;
}

body.light .rr-premium-plan-card__crowns span {
    color: rgba(59, 130, 246, 0.1);
}

body.light .rr-premium-plan-card__crowns span:nth-child(2n) {
    color: rgba(249, 115, 22, 0.12);
}

body.light .rr-premium-plan-card__features li i {
    color: #ea580c !important;
    background: rgba(249, 115, 22, 0.12);
}

body.light .rr-premium-landing {
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 28%),
        radial-gradient(circle at 14% 18%, rgba(249, 115, 22, 0.14), transparent 22%),
        linear-gradient(180deg, #fff8f1 0%, #fffdf8 54%, #f6fbff 100%) !important;
    color: #1f2937 !important;
}

body.light .rr-premium-shell {
    background:
        radial-gradient(circle at 82% 18%, rgba(96, 165, 250, 0.2), transparent 28%),
        radial-gradient(circle at 14% 82%, rgba(249, 115, 22, 0.16), transparent 24%),
        linear-gradient(135deg, #fffdfb 0%, #f6f9ff 45%, #e8f1ff 100%) !important;
    border-color: rgba(37, 99, 235, 0.14) !important;
    box-shadow: 0 30px 64px rgba(37, 99, 235, 0.12), 0 12px 32px rgba(249, 115, 22, 0.08) !important;
}

body.light .rr-premium-stage::before {
    background: radial-gradient(circle, rgba(59, 130, 246, 0.2), rgba(255, 255, 255, 0) 72%);
    opacity: 1;
}

body.light .rr-premium-stage__kicker {
    background: rgba(255, 255, 255, 0.84) !important;
    color: #1d4ed8 !important;
    border-color: rgba(37, 99, 235, 0.14) !important;
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.08);
}

body.light .rr-premium-stage__title {
    color: #172554 !important;
    text-shadow: none !important;
}

body.light .rr-premium-stage__title span {
    color: #ea580c !important;
}

body.light .rr-premium-stage__lead {
    color: #475569 !important;
}

body.light .rr-premium-stage__logo-badge {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.95), rgba(234, 88, 12, 0.9)) !important;
    border-color: rgba(255, 255, 255, 0.48) !important;
    color: #fff7ed !important;
    box-shadow: 0 18px 32px rgba(234, 88, 12, 0.22);
}

body.light .rr-premium-stage__logo-wrap::before {
    background: radial-gradient(circle at 50% 50%, rgba(249, 115, 22, 0.18), rgba(59, 130, 246, 0.12) 44%, rgba(14, 165, 233, 0) 70%);
}

body.light .rr-premium-stage__logo-wrap::after {
    border-color: rgba(37, 99, 235, 0.14);
    box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.08);
}

body.light .rr-premium-floater {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(239, 246, 255, 0.74)) !important;
    border-color: rgba(37, 99, 235, 0.1) !important;
    box-shadow: 0 18px 28px rgba(37, 99, 235, 0.1) !important;
    color: #172554 !important;
}

body.light .rr-premium-floater strong {
    color: #1e3a8a !important;
}

body.light .rr-premium-floater span {
    color: #64748b !important;
}

body.light .rr-premium-floater i {
    background: rgba(219, 234, 254, 0.92) !important;
    color: #ea580c !important;
}

body.light .rr-premium-section-title {
    color: #172554 !important;
}

body.light .rr-premium-section-desc {
    color: #64748b !important;
}

body.light .rr-premium-trial-banner {
    background: linear-gradient(135deg, rgba(255, 247, 237, 0.96), rgba(239, 246, 255, 0.94)) !important;
    color: #1e3a8a !important;
    border-color: rgba(37, 99, 235, 0.14) !important;
    box-shadow: 0 16px 28px rgba(37, 99, 235, 0.08);
}

body.light .rr-premium-plans__mini-card {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(239, 246, 255, 0.94)) !important;
    border-color: rgba(37, 99, 235, 0.12) !important;
    box-shadow: 0 16px 26px rgba(37, 99, 235, 0.08);
}

body.light .rr-premium-plans__mini-price {
    color: #172554 !important;
}

body.light .rr-premium-plan-card {
    background:
        radial-gradient(circle at 84% 16%, rgba(59, 130, 246, 0.16), transparent 28%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 249, 255, 0.96)) !important;
    border-color: rgba(37, 99, 235, 0.14) !important;
    box-shadow: 0 26px 42px rgba(37, 99, 235, 0.12) !important;
}

body.light .rr-premium-plan-card--featured {
    border-color: rgba(249, 115, 22, 0.28) !important;
    box-shadow: 0 28px 48px rgba(249, 115, 22, 0.16) !important;
}

body.light .rr-premium-plan-card__orb {
    background: linear-gradient(180deg, rgba(255, 247, 237, 0.98), rgba(255, 255, 255, 0.86)) !important;
    color: #ea580c !important;
    box-shadow: 0 14px 24px rgba(249, 115, 22, 0.12);
}

body.light .rr-premium-plan-card__payment-type {
    background: rgba(239, 246, 255, 0.94) !important;
    border-color: rgba(37, 99, 235, 0.12) !important;
    color: #1d4ed8 !important;
}

body.light .rr-premium-plan-card__payment-type i {
    background: rgba(219, 234, 254, 0.94) !important;
    color: #ea580c !important;
}

body.light .rr-premium-plan-card__name,
body.light .rr-premium-plan-card__price-value {
    color: #172554 !important;
}

body.light .rr-premium-plan-card__price-period {
    color: #1d4ed8 !important;
}

body.light .rr-premium-plan-card__price-monthly,
body.light .rr-premium-plan-card__cancel-info,
body.light .rr-premium-plan-card__features li span {
    color: #475569 !important;
}

body.light .rr-premium-plan-card__benefit-kicker {
    color: #1d4ed8 !important;
}

body.light .rr-premium-plan-card__features li {
    background: rgba(255, 255, 255, 0.9) !important;
    border-color: rgba(37, 99, 235, 0.1) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42);
}

body.light .rr-premium-plan-card__features li i {
    background: rgba(249, 115, 22, 0.12) !important;
    color: #ea580c !important;
}

body.light .rr-premium-plan-card__cancel-info {
    background: rgba(239, 246, 255, 0.92) !important;
    border-color: rgba(37, 99, 235, 0.1) !important;
}

body.light .rr-premium-plan-card__cta--primary {
    box-shadow: 0 20px 34px rgba(249, 115, 22, 0.24) !important;
}

body.light .rr-premium-plan-card__crowns span {
    color: rgba(37, 99, 235, 0.14) !important;
}

body.light .rr-premium-plan-card__crowns span:nth-child(2n) {
    color: rgba(249, 115, 22, 0.14) !important;
}

@media (max-width: 980px) {
    .rr-premium-plans__grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 767px) {
    .rr-premium-plan-card {
        gap: 0.82rem;
        padding: 0.92rem !important;
        border-radius: 22px !important;
    }

    .rr-premium-plan-card__topline {
        align-items: stretch;
    }

    .rr-premium-plan-card__orb {
        width: 44px;
        height: 44px;
        border-radius: 14px;
    }

    .rr-premium-plan-card__payment-type,
    .rr-premium-plan-card__benefit-kicker {
        width: fit-content;
        max-width: 100%;
        font-size: 0.64rem;
        letter-spacing: 0.09em;
    }

    .rr-premium-plan-card__name {
        font-size: 1.18rem;
    }

    .rr-premium-plan-card__price-value {
        font-size: clamp(2.05rem, 10vw, 2.55rem) !important;
    }

    .rr-premium-plan-card__features li {
        padding: 0.65rem 0.78rem;
        border-radius: 15px;
    }

    .rr-premium-plan-card__cta {
        min-height: 48px;
        border-radius: 15px !important;
    }
}

@keyframes rrPremiumCrownRain {
    0% {
        transform: translate3d(-50%, -12%, 0) rotate(0deg);
        opacity: 0;
    }
    8% {
        opacity: 1;
    }
    92% {
        opacity: 1;
    }
    100% {
        transform: translate3d(-50%, 132%, 0) rotate(22deg);
        opacity: 0;
    }
}

@keyframes rrPremiumButtonPulse {
    0%, 100% {
        transform: translateY(0);
        box-shadow: 0 18px 30px rgba(249, 115, 22, 0.22);
    }
    50% {
        transform: translateY(-2px);
        box-shadow: 0 24px 36px rgba(249, 115, 22, 0.3);
    }
}

@keyframes rrPremiumButtonSheen {
    0%,
    18% {
        transform: translateX(-125%);
    }
    52%,
    100% {
        transform: translateX(135%);
    }
}

@media (max-width: 767px) {
    .rr-premium-modal {
        align-items: flex-end !important;
        justify-content: center !important;
        padding: 0 !important;
    }

    .rr-premium-modal__backdrop {
        backdrop-filter: blur(8px);
    }

    .rr-premium-modal__content {
        width: 100vw !important;
        max-width: 100vw !important;
        max-height: min(100dvh, 100vh) !important;
        margin: 0 !important;
        padding: 3.2rem 0.78rem calc(env(safe-area-inset-bottom, 0px) + 0.85rem) !important;
        border-radius: 24px 24px 0 0 !important;
        overflow: hidden !important;
    }

    .rr-premium-modal__close {
        top: 0.7rem !important;
        right: 0.7rem !important;
        z-index: 3 !important;
        width: 38px !important;
        height: 38px !important;
    }

    .rr-premium-modal__body {
        max-height: calc(min(100dvh, 100vh) - 5.2rem) !important;
        overflow: auto !important;
        padding-right: 0.08rem;
    }

    .rr-premium-checkout {
        gap: 0.68rem !important;
    }

    .rr-premium-checkout__hero,
    .rr-premium-checkout__card-shell,
    .rr-premium-checkout__qr-card,
    .rr-premium-checkout__info-card {
        padding: 0.78rem !important;
        border-radius: 18px !important;
    }

    .rr-premium-checkout__headline {
        display: grid !important;
        gap: 0.18rem !important;
        width: 100%;
    }

    .rr-premium-checkout__headline h3 {
        font-size: 0.96rem !important;
        line-height: 1.15 !important;
    }

    .rr-premium-checkout__headline strong {
        font-size: 1.22rem !important;
        line-height: 1 !important;
    }

    .rr-premium-checkout__methods,
    .rr-premium-checkout__pix,
    .rr-premium-checkout__form-grid,
    .rr-premium-checkout__summary {
        grid-template-columns: 1fr !important;
    }

    .rr-premium-checkout__method {
        min-height: 46px !important;
        padding: 0.7rem 0.76rem !important;
        border-radius: 15px !important;
        gap: 0.55rem !important;
    }

    .rr-premium-checkout__method i {
        width: 32px !important;
        height: 32px !important;
        border-radius: 10px !important;
        font-size: 0.82rem !important;
    }

    .rr-premium-checkout__method strong {
        font-size: 0.84rem !important;
        line-height: 1.1 !important;
    }

    .rr-premium-checkout__row {
        display: grid !important;
        gap: 0.42rem !important;
    }

    .rr-premium-checkout__row h4 {
        margin: 0 !important;
        font-size: 0.88rem !important;
    }

    .rr-premium-checkout__back {
        font-size: 0.78rem !important;
        align-self: start !important;
    }

    .rr-premium-checkout__field {
        gap: 0.22rem !important;
    }

    .rr-premium-checkout__label {
        font-size: 0.58rem !important;
        letter-spacing: 0.08em !important;
    }

    .rr-premium-checkout__input,
    .rr-premium-checkout__hosted,
    .rr-premium-checkout__select {
        min-height: 42px !important;
        height: 42px !important;
        border-radius: 12px !important;
        padding: 0 0.74rem !important;
        font-size: 0.86rem !important;
    }

    .rr-premium-checkout__hosted {
        display: flex !important;
        align-items: center !important;
        padding: 0.52rem 0.74rem !important;
    }

    .rr-premium-checkout__hosted iframe {
        min-height: 30px !important;
        height: 30px !important;
    }

    .rr-premium-checkout__feedback {
        padding: 0.66rem 0.74rem !important;
        border-radius: 12px !important;
        font-size: 0.78rem !important;
        line-height: 1.38 !important;
    }

    .rr-premium-checkout__actions {
        grid-template-columns: 1fr !important;
        gap: 0.44rem !important;
    }

    .rr-premium-checkout__actions .rr-premium-btn,
    .rr-premium-checkout__submit {
        width: 100% !important;
        min-height: 44px !important;
        padding: 0.68rem 0.82rem !important;
        border-radius: 13px !important;
        font-size: 0.84rem !important;
    }

    .rr-premium-checkout__code {
        max-height: 152px !important;
        overflow: auto !important;
        padding: 0.7rem 0.74rem !important;
        border-radius: 13px !important;
        font-size: 0.76rem !important;
        line-height: 1.4 !important;
    }

    .rr-premium-checkout__qr-card {
        gap: 0.55rem !important;
        justify-items: center !important;
    }

    .rr-premium-checkout__qr-image,
    .rr-premium-checkout__empty-qr {
        width: min(100%, 172px) !important;
        padding: 0.56rem !important;
        border-radius: 14px !important;
    }

    .rr-premium-checkout__status {
        padding: 0.46rem 0.68rem !important;
        font-size: 0.66rem !important;
        letter-spacing: 0.07em !important;
    }
}

.rr-premium-modal__content {
    width: min(980px, calc(100vw - 24px)) !important;
    max-height: calc(100vh - 24px) !important;
    padding: 0.92rem !important;
}

.rr-premium-modal__body {
    min-height: 0 !important;
}

.rr-premium-checkout--card-mode {
    grid-template-columns: minmax(240px, 0.78fr) minmax(0, 1.22fr) !important;
    align-items: start !important;
}

.rr-premium-checkout--card-mode .rr-premium-checkout__hero {
    gap: 0.72rem !important;
    padding: 1rem !important;
    border-radius: 24px !important;
    min-height: 100% !important;
    align-content: start !important;
}

.rr-premium-checkout--card-mode .rr-premium-checkout__headline {
    align-items: flex-start !important;
    flex-direction: column !important;
    gap: 0.28rem !important;
}

.rr-premium-checkout--card-mode .rr-premium-checkout__headline h3 {
    font-size: 1.18rem !important;
}

.rr-premium-checkout--card-mode .rr-premium-checkout__headline strong {
    font-size: 2rem !important;
}

.rr-premium-checkout--card-mode .rr-premium-checkout__card-shell {
    padding: 1rem !important;
    border-radius: 24px !important;
}

.rr-premium-checkout--card-mode .rr-premium-checkout__row {
    align-items: flex-start !important;
    gap: 0.55rem !important;
}

.rr-premium-checkout--card-mode .rr-premium-checkout__row h4 {
    font-size: 0.96rem !important;
    letter-spacing: 0.02em !important;
}

.rr-premium-checkout__card-form {
    gap: 0.82rem !important;
}

.rr-premium-checkout__form-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 0.72rem !important;
}

.rr-premium-checkout__field {
    min-width: 0 !important;
}

.rr-premium-checkout__field--hidden {
    display: none !important;
}

.rr-premium-checkout__label {
    font-size: 0.68rem !important;
    letter-spacing: 0.09em !important;
}

.rr-premium-checkout__input,
.rr-premium-checkout__select {
    min-height: 52px !important;
    height: 52px !important;
    max-height: 52px !important;
    border-radius: 15px !important;
    padding: 0 0.9rem !important;
    font-size: 0.94rem !important;
}

.rr-premium-checkout__hosted {
    min-height: 52px !important;
    height: 52px !important;
    max-height: 52px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: stretch !important;
    overflow: hidden !important;
    border-radius: 15px !important;
    padding: 0.72rem 0.9rem !important;
}

.rr-premium-checkout__hosted > *,
.rr-premium-checkout__hosted iframe,
.rr-premium-checkout__hosted div,
.rr-premium-checkout__hosted section {
    width: 100% !important;
    min-height: 24px !important;
    height: 24px !important;
    max-height: 24px !important;
    display: block !important;
    flex: 1 1 auto !important;
}

.rr-premium-checkout__hosted iframe {
    border: 0 !important;
}

.rr-premium-checkout__submit {
    min-height: 54px !important;
}

@media (max-width: 900px) {
    .rr-premium-modal__content {
        width: min(100%, calc(100vw - 18px)) !important;
        max-height: calc(100vh - 18px) !important;
        padding: 0.8rem !important;
    }

    .rr-premium-checkout--card-mode {
        grid-template-columns: 1fr !important;
    }

    .rr-premium-checkout--card-mode .rr-premium-checkout__hero,
    .rr-premium-checkout--card-mode .rr-premium-checkout__card-shell {
        padding: 0.86rem !important;
    }

    .rr-premium-checkout__form-grid {
        grid-template-columns: 1fr !important;
        gap: 0.58rem !important;
    }
}

@media (max-width: 767px) {
    .rr-premium-modal {
        align-items: flex-end !important;
        padding: 0.25rem !important;
    }

    .rr-premium-modal__content {
        width: min(100%, 460px) !important;
        max-height: calc(100vh - 6px) !important;
        border-radius: 24px 24px 0 0 !important;
        padding: 0.72rem !important;
    }

    .rr-premium-checkout--card-mode .rr-premium-checkout__hero,
    .rr-premium-checkout--card-mode .rr-premium-checkout__card-shell {
        border-radius: 20px !important;
        padding: 0.78rem !important;
    }

    .rr-premium-checkout__headline h3 {
        font-size: 0.98rem !important;
    }

    .rr-premium-checkout__headline strong {
        font-size: 1.36rem !important;
    }

    .rr-premium-checkout__input,
    .rr-premium-checkout__select,
    .rr-premium-checkout__hosted {
        min-height: 46px !important;
        height: 46px !important;
        max-height: 46px !important;
        border-radius: 13px !important;
        padding: 0 0.78rem !important;
        font-size: 0.88rem !important;
    }

    .rr-premium-checkout__hosted {
        padding: 0.58rem 0.78rem !important;
    }

    .rr-premium-checkout__hosted > *,
    .rr-premium-checkout__hosted iframe,
    .rr-premium-checkout__hosted div,
    .rr-premium-checkout__hosted section {
        min-height: 20px !important;
        height: 20px !important;
        max-height: 20px !important;
    }

    .rr-premium-checkout__submit {
        min-height: 48px !important;
    }
}

.rr-premium-modal {
    align-items: center !important;
    justify-content: center !important;
    overscroll-behavior: contain !important;
}

.rr-premium-modal__content {
    margin: 0 auto !important;
    overscroll-behavior: contain !important;
}

body.rr-premium-modal-open {
    overflow: hidden !important;
}

@media (max-width: 767px) {
    .rr-premium-modal {
        align-items: center !important;
        justify-content: center !important;
        padding: 0.55rem !important;
    }

    .rr-premium-modal__content {
        width: min(460px, calc(100vw - 12px)) !important;
        max-width: calc(100vw - 12px) !important;
        max-height: calc(100dvh - 12px) !important;
        border-radius: 24px !important;
        margin: 0 auto !important;
        padding: 0.72rem !important;
    }

    .rr-premium-modal__body {
        max-height: calc(100dvh - 92px) !important;
        overflow: auto !important;
    }
}
</style>

<div class="rr-premium-landing">
    <div class="rr-premium-container">
        <section class="rr-premium-shell">
            <canvas id="premiumParticles" class="rr-premium-particles"></canvas>

            <div class="rr-premium-shell__rail">
                <span>Premium</span>
                <span>resumo direto</span>
            </div>

            <div class="rr-premium-stage">
                <div class="rr-premium-stage__copy">
                    <span class="rr-premium-kicker rr-premium-stage__kicker"><i class="fas fa-crown"></i> Coroa premium da arena</span>

                    <h1 class="rr-premium-stage__title">
                        Entre na
                        <span>camada premium</span>
                    </h1>

                    <p class="rr-premium-stage__lead">
                        X1 com taxa menor, bolão premium grátis e leitura completa do rodeio no mesmo acesso.
                    </p>

                    <div class="rr-premium-stage__actions">
                        @if($isPremium)
                            <div class="rr-premium-hero__status">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    Sua conta já está Premium
                                    @if($currentSubscription)
                                        <small>{{ $currentSubscription->remaining_days }} dias restantes no ciclo atual</small>
                                    @endif
                                </div>
                            </div>
                            <a href="#plans" class="rr-premium-btn rr-premium-btn--ghost">
                                <i class="fas fa-tags"></i>
                                <span>Ver planos</span>
                            </a>
                        @elseif($canTrial)
                            <a href="#plans" class="rr-premium-btn rr-premium-btn--trial">
                                <i class="fas fa-gift"></i>
                                <span>Começar 3 dias grátis</span>
                            </a>
                        @elseif($isActivityLocked)
                            <div class="rr-premium-hero__alert">
                                <i class="fas fa-lock"></i>
                                <div>{{ $trialReason }}</div>
                            </div>
                            <a href="#plans" class="rr-premium-btn rr-premium-btn--primary">
                                <i class="fas fa-crown"></i>
                                <span>Assinar agora</span>
                            </a>
                        @else
                            <a href="#plans" class="rr-premium-btn rr-premium-btn--primary">
                                <i class="fas fa-crown"></i>
                                <span>Assinar agora</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="rr-premium-stage__visual" aria-hidden="true">
                    <div class="rr-premium-stage__logo-wrap">
                        <span class="rr-premium-stage__logo-badge"><i class="fas fa-crown"></i> Premium</span>
                        <img class="rr-premium-stage__logo" src="{{ asset('assets/images/logo_icon/premiumleague.png') }}?v={{ time() }}" alt="Premium League" onerror="this.src='{{ asset('assets/images/logo_icon/logo.png') }}'">
                    </div>

                    <div class="rr-premium-stage__floaters">
                        <article class="rr-premium-floater rr-premium-floater--x1">
                            <i class="fas fa-bolt"></i>
                            <strong>X1 com taxa menor</strong>
                            <span>mais retorno líquido</span>
                        </article>

                        <article class="rr-premium-floater rr-premium-floater--bolao">
                            <i class="fas fa-trophy"></i>
                            <strong>Bolão premium grátis</strong>
                            <span>ligas exclusivas</span>
                        </article>

                        <article class="rr-premium-floater rr-premium-floater--stats">
                            <i class="fas fa-chart-line"></i>
                            <strong>Leitura completa</strong>
                            <span>odds e estatísticas</span>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="rr-premium-section rr-premium-command">
            <div class="rr-premium-section-heading">
                <span class="rr-premium-section-kicker"><i class="fas fa-rocket"></i> Vantagem imediata</span>
                <h2 class="rr-premium-section-title">O Premium muda o jogo em três frentes.</h2>
                <p class="rr-premium-section-desc">Reduz atrito, melhora leitura e libera acesso. Tudo com a mesma cara de produto do resto do hub.</p>
            </div>
            <div class="rr-premium-command__grid">
                @foreach($commandCards as $card)
                    <article class="rr-premium-command__card">
                        <span class="rr-premium-command__pill"><i class="fas {{ $card['icon'] }}"></i> {{ $card['pill'] }}</span>
                        <h3>{{ $card['title'] }}</h3>
                        <p>{{ $card['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="features" class="rr-premium-section rr-premium-benefits">
            <div class="rr-premium-section-heading">
                <span class="rr-premium-section-kicker"><i class="fas fa-star"></i> Benefícios exclusivos</span>
                <h2 class="rr-premium-section-title">Resumo do que muda.</h2>
                <p class="rr-premium-section-desc">Só o que importa para usar melhor o hub.</p>
            </div>
            <div class="rr-premium-benefits__grid">
                @foreach($benefits as $benefit)
                    <article class="rr-premium-benefit-card" data-accent="{{ $benefit['accent'] }}">
                        <div class="rr-premium-benefit-card__icon"><i class="fas {{ $benefit['icon'] }}"></i></div>
                        <h3 class="rr-premium-benefit-card__title">{{ $benefit['title'] }}</h3>
                        <p class="rr-premium-benefit-card__desc">{{ $benefit['desc'] }}</p>
                        <div class="rr-premium-benefit-card__badge"><i class="fas fa-check"></i><span>{{ $benefit['meta'] }}</span></div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="rr-premium-section rr-premium-compare">
            <div class="rr-premium-section-heading">
                <span class="rr-premium-section-kicker"><i class="fas fa-balance-scale"></i> Free vs Premium</span>
                <h2 class="rr-premium-section-title">Comparativo direto.</h2>
                <p class="rr-premium-section-desc">Premium melhora sua operação dentro da mesma regra do rodeio.</p>
            </div>
            <div class="rr-premium-compare__table">
                <div class="rr-premium-compare__header">
                    <div class="rr-premium-compare__feature">Recurso</div>
                    <div class="rr-premium-compare__free">Free</div>
                    <div class="rr-premium-compare__premium">Premium</div>
                </div>
                @foreach($comparisonRows as $row)
                    <div class="rr-premium-compare__row">
                        <div class="rr-premium-compare__feature"><i class="fas {{ $row['icon'] }}"></i> {{ $row['feature'] }}</div>
                        <div class="rr-premium-compare__free">{{ $row['free'] }}</div>
                        <div class="rr-premium-compare__premium">{{ $row['premium'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>
        <section id="plans" class="rr-premium-section rr-premium-plans">
            @if($canTrial)
                <div class="rr-premium-trial-banner">
                    <i class="fas fa-gift"></i>
                    <span>Você está elegível para <strong>3 dias grátis</strong> antes da cobrança do plano.</span>
                </div>
            @elseif($isActivityLocked)
                <div class="rr-premium-trial-banner rr-premium-trial-banner--locked">
                    <i class="fas fa-lock"></i>
                    <span>{{ $trialReason }}</span>
                </div>
            @endif

            <div class="rr-premium-plans__mini-grid" id="premiumPlansMiniGrid"></div>

            <div class="rr-premium-plan-stage">
                <aside class="rr-premium-plan-brief">
                    <span class="rr-premium-command__pill"><i class="fas fa-route"></i> Como funciona</span>
                    <h3>Fluxo curto e direto.</h3>
                    <p>Escolha o plano, abra o checkout e ative.</p>
                    <ol class="rr-premium-plan-brief__steps">
                        <li><span class="rr-premium-plan-brief__step-index">1</span><span>Escolha o plano que encaixa no seu ritmo.</span></li>
                        <li><span class="rr-premium-plan-brief__step-index">2</span><span>Abra o checkout seguro dentro do modal do hub.</span></li>
                        <li><span class="rr-premium-plan-brief__step-index">3</span><span>Confirmação feita, Premium ativado e pronto para uso.</span></li>
                    </ol>
                    @if($isPremium)
                        <div class="rr-premium-plan-brief__status">
                            <strong>Conta premium ativa.</strong><br>
                            Você já está no nível pago. A seção de planos continua disponível para consulta.
                        </div>
                    @endif
                </aside>

                <div class="rr-premium-plans__grid" id="premiumPlansGrid">
                    <div class="rr-premium-plans__loading">
                        <div class="spinner"></div>
                        <span>Carregando planos do Premium...</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="rr-premium-section rr-premium-endgame">
            <div class="rr-premium-endgame__grid">
                <div class="rr-premium-faq">
                    <div class="rr-premium-section-heading">
                        <span class="rr-premium-section-kicker"><i class="fas fa-question-circle"></i> FAQ</span>
                        <h2 class="rr-premium-section-title">Perguntas rápidas.</h2>
                    </div>
                    <div class="rr-premium-faq__list">
                        @foreach($faqItems as $index => $item)
                            <details class="rr-premium-faq__item" @if($index === 0) open @endif>
                                <summary>{{ $item['question'] }}</summary>
                                <p>{{ $item['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>

                <div class="rr-premium-cta-final">
                    <div>
                        <span class="rr-premium-section-kicker"><i class="fas fa-fire"></i> Fechar a conta</span>
                        <h2>Se o hub está ficando forte, a tela Premium precisa acompanhar.</h2>
                        <p>Ative o Premium para entrar no rodeio com leitura melhor, taxa menor e acesso liberado ao que realmente importa.</p>
                    </div>
                    <div class="rr-premium-cta-final__chips">
                        <span class="rr-premium-cta-final__chip"><i class="fas fa-check"></i> checkout no modal</span>
                        <span class="rr-premium-cta-final__chip"><i class="fas fa-check"></i> Mercado Pago</span>
                        <span class="rr-premium-cta-final__chip"><i class="fas fa-check"></i> experiência mobile forte</span>
                    </div>
                    @if($isPremium)
                        <div class="rr-premium-cta-final__status"><i class="fas fa-crown"></i><span>Você já está Premium.</span></div>
                    @elseif($canTrial)
                        <a href="#plans" class="rr-premium-btn rr-premium-btn--trial rr-premium-btn--large"><i class="fas fa-gift"></i><span>Começar 3 dias grátis</span></a>
                    @else
                        <a href="#plans" class="rr-premium-btn rr-premium-btn--primary rr-premium-btn--large"><i class="fas fa-crown"></i><span>Assinar agora</span></a>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

<div class="rr-premium-modal" id="premiumPaymentModal" style="display: none;">
    <div class="rr-premium-modal__backdrop"></div>
    <div class="rr-premium-modal__content">
        <button class="rr-premium-modal__close" id="closePremiumModal" type="button" aria-label="Fechar modal premium">
            <i class="fas fa-times"></i>
        </button>
        <div class="rr-premium-modal__body" id="premiumModalBody"></div>
    </div>
</div>

<script>
(function() {
    'use strict';

    if (typeof window.__premiumPageCleanup === 'function') {
        window.__premiumPageCleanup();
    }

    const cleanupFns = [];
    const registerCleanup = (fn) => cleanupFns.push(fn);
    window.__premiumPageCleanup = function() {
        while (cleanupFns.length) {
            const fn = cleanupFns.pop();
            try { fn(); } catch (error) { console.warn('Premium cleanup warning:', error); }
        }
    };

    const API_BASE = '{{ url("/api/subscriptions") }}';
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const USER_DEFAULTS = {
        email: @json($user?->email ?? ''),
        cpf: @json($user?->cpf ?? ''),
    };
    const modal = document.getElementById('premiumPaymentModal');
    const modalBody = document.getElementById('premiumModalBody');
    const closeBtn = document.getElementById('closePremiumModal');
    const plansGrid = document.getElementById('premiumPlansGrid');
    const plansMiniGrid = document.getElementById('premiumPlansMiniGrid');
    const premiumState = {
        plans: new Map(),
        selectedPlanSlug: '',
        pollInterval: null,
        sdkPromise: null,
        publicKey: null,
        mp: null,
        cardForm: null,
        isCardSubmitting: false,
        scrollTop: 0,
    };

    function initParticles() {
        const canvas = document.getElementById('premiumParticles');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const container = canvas.parentElement;
        const dpr = window.devicePixelRatio || 1;
        const colors = document.body.classList.contains('light') ? ['#1d4ed8', '#ea580c', '#fb923c', '#0ea5e9'] : ['#60a5fa', '#2563eb', '#f97316', '#fbbf24'];
        let particles = [];
        let frameId = null;

        function resize() {
            const width = container.offsetWidth;
            const height = container.offsetHeight;
            canvas.width = width * dpr;
            canvas.height = height * dpr;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function createParticles() {
            const count = container.offsetWidth < 768 ? 22 : 42;
            particles = Array.from({ length: count }, () => ({
                x: Math.random() * container.offsetWidth,
                y: Math.random() * container.offsetHeight,
                r: Math.random() * 2.3 + 0.6,
                vx: (Math.random() - 0.5) * 0.42,
                vy: (Math.random() - 0.5) * 0.42,
                alpha: Math.random() * (document.body.classList.contains('light') ? 0.18 : 0.34) + (document.body.classList.contains('light') ? 0.08 : 0.16),
                color: colors[Math.floor(Math.random() * colors.length)]
            }));
        }

        function draw() {
            ctx.clearRect(0, 0, container.offsetWidth, container.offsetHeight);
            particles.forEach((particle) => {
                particle.x += particle.vx;
                particle.y += particle.vy;
                if (particle.x < -8) particle.x = container.offsetWidth + 8;
                if (particle.x > container.offsetWidth + 8) particle.x = -8;
                if (particle.y < -8) particle.y = container.offsetHeight + 8;
                if (particle.y > container.offsetHeight + 8) particle.y = -8;
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.r, 0, Math.PI * 2);
                ctx.globalAlpha = particle.alpha;
                ctx.fillStyle = particle.color;
                ctx.fill();
            });
            ctx.globalAlpha = 1;
            frameId = requestAnimationFrame(draw);
        }

        resize();
        createParticles();
        draw();

        const onResize = () => { resize(); createParticles(); };
        window.addEventListener('resize', onResize);
        registerCleanup(() => {
            window.removeEventListener('resize', onResize);
            if (frameId) cancelAnimationFrame(frameId);
        });
    }

    function renderPlansError(message) {
        if (!plansGrid) return;
        if (plansMiniGrid) plansMiniGrid.innerHTML = '';
        plansGrid.innerHTML = `<div class="rr-premium-plans__empty"><div><p>${message}</p></div></div>`;
    }

    function focusPlanCard(planSlug) {
        if (!planSlug || !plansGrid) return;
        const target = plansGrid.querySelector(`[data-plan="${planSlug}"]`);
        if (!target) return;

        target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        target.classList.remove('is-plan-focus');
        void target.offsetWidth;
        target.classList.add('is-plan-focus');
        setTimeout(() => target.classList.remove('is-plan-focus'), 1200);
    }

    function consumePendingPlanFocus() {
        try {
            const planSlug = sessionStorage.getItem('rr_premium_focus_plan');
            if (!planSlug) return;
            sessionStorage.removeItem('rr_premium_focus_plan');
            requestAnimationFrame(() => focusPlanCard(planSlug));
        } catch (error) {
            console.warn('Premium focus plan warning:', error);
        }
    }

    function normalizePaymentMethods(methods) {
        const normalized = Array.isArray(methods) ? methods : [];
        return Array.from(new Set(normalized.filter((method) => method === 'pix' || method === 'card')));
    }

    function buildPremiumLoginRedirect(planSlug = '') {
        const redirectUrl = new URL('{{ route("home") }}', window.location.origin);
        redirectUrl.searchParams.set('tab', 'premium');
        if (planSlug) {
            redirectUrl.searchParams.set('premium_plan', planSlug);
        }
        redirectUrl.hash = 'plans';
        return redirectUrl.toString();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function loadPlans() {
        if (!plansGrid) return;
        try {
            const response = await fetch(API_BASE + '/plans', { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            if (data.success && Array.isArray(data.plans) && data.plans.length) {
                renderPlans(data.plans, data.can_trial, data.trial_reason);
                return;
            }
            renderPlansError('Nenhum plano premium disponível agora.');
        } catch (error) {
            console.error('Erro ao carregar planos:', error);
            renderPlansError('Erro ao carregar planos. Tente novamente em instantes.');
        }
    }
    function renderPlans(plans, canTrial, trialReason) {
        if (!plansGrid) return;

        const blockedByActivity = typeof trialReason === 'string' && /participou/i.test(trialReason);
        const visiblePlans = Array.isArray(plans) ? plans.slice(0, 3) : [];
        premiumState.plans.clear();
        visiblePlans.forEach((plan) => premiumState.plans.set(plan.slug, plan));

        if (plansMiniGrid) {
            plansMiniGrid.innerHTML = visiblePlans.map((plan) => `
                <button type="button" class="rr-premium-plans__mini-card" data-mini-plan="${plan.slug}">
                    <span class="rr-premium-plans__mini-period">${plan.period_label}</span>
                    <span class="rr-premium-plans__mini-price">${plan.formatted_price}</span>
                </button>
            `).join('');

            plansMiniGrid.querySelectorAll('[data-mini-plan]').forEach((button) => {
                button.addEventListener('click', () => focusPlanCard(button.dataset.miniPlan || ''));
            });
        }

        plansGrid.innerHTML = visiblePlans.map((plan, index) => {
            const paymentMethods = normalizePaymentMethods(plan.payment_methods);
            const isRecurring = Boolean(plan.is_recurring);
            const hasTrial = Boolean(plan.has_trial && canTrial);
            const trialBlocked = Boolean(!hasTrial && plan.has_trial && blockedByActivity);
            const paymentIcon = paymentMethods.length > 1 ? 'fa-wallet' : (paymentMethods.includes('card') ? 'fa-credit-card' : 'fa-qrcode');
            const paymentLabel = paymentMethods.length > 1 ? 'PIX + cartão' : (paymentMethods.includes('card') ? 'Cartão' : 'PIX');
            const features = Array.isArray(plan.features) ? plan.features.slice(0, 4) : [];
            const toneClass = `rr-premium-plan-card--tone-${(index % 3) + 1}`;
            const orbIcon = index === 0 ? 'fa-bolt' : (index === 1 ? 'fa-crown' : 'fa-chart-line');

            return `
                <article class="rr-premium-plan-card ${plan.is_featured ? 'rr-premium-plan-card--featured' : ''} ${toneClass}" data-plan="${plan.slug}">
                    <div class="rr-premium-plan-card__crowns" aria-hidden="true">
                        <span style="--crown-left: 11%; --crown-delay: -0.4s; --crown-duration: 7.2s; --crown-size: 0.86rem;"><i class="fas fa-crown"></i></span>
                        <span style="--crown-left: 28%; --crown-delay: -2.6s; --crown-duration: 8s; --crown-size: 0.98rem;"><i class="fas fa-crown"></i></span>
                        <span style="--crown-left: 46%; --crown-delay: -1.1s; --crown-duration: 6.7s; --crown-size: 0.76rem;"><i class="fas fa-crown"></i></span>
                        <span style="--crown-left: 67%; --crown-delay: -3.2s; --crown-duration: 7.6s; --crown-size: 1.04rem;"><i class="fas fa-crown"></i></span>
                        <span style="--crown-left: 83%; --crown-delay: -4.7s; --crown-duration: 6.9s; --crown-size: 0.82rem;"><i class="fas fa-crown"></i></span>
                    </div>

                    <div class="rr-premium-plan-card__topline">
                        <div class="rr-premium-plan-card__identity">
                            <div class="rr-premium-plan-card__payment-type"><i class="fas ${paymentIcon}"></i><span>${paymentLabel}</span></div>
                        </div>
                        <div class="rr-premium-plan-card__orb"><i class="fas ${orbIcon}"></i></div>
                    </div>

                    <div class="rr-premium-plan-card__hero">
                        <div class="rr-premium-plan-card__price">
                            <h3 class="rr-premium-plan-card__name">${plan.name}</h3>
                            <div class="rr-premium-plan-card__price-value">${plan.formatted_price}</div>
                            <div class="rr-premium-plan-card__price-period">${plan.period_label}</div>
                            ${plan.billing_cycle !== 'monthly' && plan.formatted_monthly_price ? `<div class="rr-premium-plan-card__price-monthly">${plan.formatted_monthly_price}/mês</div>` : ''}
                        </div>
                    </div>

                    ${features.length ? `<p class="rr-premium-plan-card__benefit-kicker"><i class="fas fa-star"></i><span>vantagens liberadas</span></p>` : ''}
                    ${features.length ? `<ul class="rr-premium-plan-card__features">${features.map((feature) => `<li><i class="fas fa-check"></i><span>${feature}</span></li>`).join('')}</ul>` : ''}
                    <div class="rr-premium-plan-card__cancel-info">${isRecurring ? '<strong>Cartão:</strong> finalize no popout e confirme sem sair da página.' : '<strong>Pagamento:</strong> gere PIX ou valide o cartão aqui dentro.'}</div>
                    ${trialBlocked ? `<div class="rr-premium-plan-card__notice">${trialReason}</div>` : ''}
                    <div class="rr-premium-plan-card__actions">
                        <button class="rr-premium-btn ${hasTrial ? 'rr-premium-btn--trial' : 'rr-premium-btn--primary'} rr-premium-plan-card__cta rr-premium-plan-card__cta--primary" data-plan-slug="${plan.slug}" data-has-trial="${hasTrial}" data-is-recurring="${isRecurring}" ${trialBlocked ? 'disabled' : ''}>
                            <i class="fas ${hasTrial ? 'fa-gift' : (trialBlocked ? 'fa-lock' : 'fa-crown')}"></i>
                            <span>${hasTrial ? 'Começar 3 dias grátis' : (trialBlocked ? 'Trial bloqueado' : 'Assinar agora')}</span>
                        </button>
                        ${hasTrial ? `<button class="rr-premium-btn rr-premium-btn--ghost rr-premium-plan-card__cta rr-premium-plan-card__cta--ghost" data-plan-slug="${plan.slug}" data-has-trial="false" data-is-recurring="${isRecurring}" data-force-payment="true"><i class="fas fa-credit-card"></i><span>Assinar agora</span></button>` : ''}
                        ${trialBlocked ? `<button class="rr-premium-btn rr-premium-btn--ghost rr-premium-plan-card__cta rr-premium-plan-card__cta--ghost" data-plan-slug="${plan.slug}" data-has-trial="false" data-is-recurring="${isRecurring}"><i class="fas fa-crown"></i><span>Seguir sem trial</span></button>` : ''}
                    </div>
                </article>`;
        }).join('');

        plansGrid.querySelectorAll('[data-plan-slug]').forEach((button) => {
            button.addEventListener('click', () => handlePlanSelect(
                button.dataset.planSlug,
                button.dataset.hasTrial === 'true',
                button.dataset.isRecurring === 'true',
                button.dataset.forcePayment === 'true'
            ));
        });

        initMobileCardSpotlight();
        consumePendingPlanFocus();
    }

    function initMobileCardSpotlight() {
        const selectors = '.rr-premium-command__card, .rr-premium-benefit-card, .rr-premium-plan-brief, .rr-premium-plan-card, .rr-premium-faq__item, .rr-premium-compare__table, .rr-premium-cta-final';
        const cards = Array.from(document.querySelectorAll(selectors));
        cards.forEach((card) => card.classList.add('rr-premium-scroll-card'));

        if (!window.matchMedia('(max-width: 991px)').matches || !cards.length) {
            cards.forEach((card) => card.classList.remove('is-in-focus'));
            return;
        }

        let ticking = false;
        const updateFocus = () => {
            const viewportCenter = window.innerHeight * 0.52;
            let focusedCard = null;
            let minDistance = Number.POSITIVE_INFINITY;

            cards.forEach((card) => {
                const rect = card.getBoundingClientRect();
                if (rect.bottom <= 0 || rect.top >= window.innerHeight) return;
                const cardCenter = rect.top + (rect.height / 2);
                const distance = Math.abs(cardCenter - viewportCenter);
                if (distance < minDistance) {
                    minDistance = distance;
                    focusedCard = card;
                }
            });

            cards.forEach((card) => card.classList.toggle('is-in-focus', card === focusedCard));
            ticking = false;
        };

        const onScroll = () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(updateFocus);
        };

        updateFocus();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        registerCleanup(() => {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
        });
    }

    function destroyCardForm() {
        const instance = premiumState.cardForm;
        premiumState.cardForm = null;
        premiumState.isCardSubmitting = false;

        if (!instance) return;

        ['unmount', 'destroy'].forEach((method) => {
            if (typeof instance[method] === 'function') {
                try {
                    instance[method]();
                } catch (error) {
                    console.warn('Premium card cleanup warning:', error);
                }
            }
        });
    }

    function stopPaymentPolling() {
        if (premiumState.pollInterval) {
            clearInterval(premiumState.pollInterval);
            premiumState.pollInterval = null;
        }
    }

    function lockPremiumModalBackground() {
        premiumState.scrollTop = window.scrollY || window.pageYOffset || 0;
        document.body.classList.add('modal-open', 'rr-modal-open', 'rr-premium-modal-open');
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.inset = '0';
        document.body.style.top = `-${premiumState.scrollTop}px`;
    }

    function unlockPremiumModalBackground() {
        const lockedTop = parseInt(document.body.style.top || '0', 10);
        const scrollTop = Number.isFinite(lockedTop) ? Math.abs(lockedTop) : premiumState.scrollTop;

        document.body.classList.remove('rr-premium-modal-open');
        document.body.classList.remove('modal-open');
        document.body.classList.remove('rr-modal-open');
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.inset = '';
        document.body.style.top = '';

        window.scrollTo({ top: scrollTop || 0, behavior: 'auto' });
        premiumState.scrollTop = 0;
    }

    function showModal() {
        if (!modal) return;
        modal.style.display = 'flex';
        lockPremiumModalBackground();
    }

    function hideModal() {
        if (!modal) return;
        modal.style.display = 'none';
        unlockPremiumModalBackground();
        stopPaymentPolling();
        destroyCardForm();
        premiumState.selectedPlanSlug = '';
    }
    window.hidePremiumModal = hideModal;

    function setModalBody(html) {
        if (!modalBody) return;
        modalBody.innerHTML = html;
        modal?.querySelector('.rr-premium-modal__content')?.scrollTo({ top: 0, behavior: 'auto' });
    }

    function showModalLoading(message) {
        setModalBody(`<div class="rr-premium-modal__loading"><div class="spinner"></div><p>${escapeHtml(message)}</p></div>`);
    }

    function showModalSuccess(message, title = 'Premium ativado') {
        stopPaymentPolling();
        destroyCardForm();
        setModalBody(`
            <div class="rr-premium-modal__success">
                <i class="fas fa-check-circle"></i>
                <h3>${escapeHtml(title)}</h3>
                <p>${escapeHtml(message)}</p>
            </div>
        `);

        window.setTimeout(() => {
            hideModal();
            window.location.reload();
        }, 1400);
    }

    function showModalError(message, title = 'Algo deu errado') {
        setModalBody(`
            <div class="rr-premium-modal__error">
                <i class="fas fa-times-circle"></i>
                <h3>${escapeHtml(title)}</h3>
                <p>${escapeHtml(message)}</p>
                <button class="rr-premium-btn rr-premium-btn--primary" type="button" onclick="window.hidePremiumModal()">Fechar</button>
            </div>
        `);
    }

    function buildCheckoutHero(plan) {
        return `
            <div class="rr-premium-checkout__hero">
                <div class="rr-premium-checkout__headline">
                    <h3>${escapeHtml(plan.name)}</h3>
                    <strong>${escapeHtml(plan.formatted_price)}</strong>
                </div>
            </div>
        `;
    }

    function renderMethodPicker(plan) {
        const methods = normalizePaymentMethods(plan.payment_methods);

        setModalBody(`
            <div class="rr-premium-checkout">
                ${buildCheckoutHero(plan)}
                <div class="rr-premium-checkout__methods">
                    <button type="button" class="rr-premium-checkout__method ${methods.includes('pix') ? '' : 'is-disabled'}" data-premium-method="pix" ${methods.includes('pix') ? '' : 'disabled'}>
                        <i class="fas fa-qrcode"></i>
                        <strong>PIX</strong>
                    </button>
                    <button type="button" class="rr-premium-checkout__method ${methods.includes('card') ? '' : 'is-disabled'}" data-premium-method="card" ${methods.includes('card') ? '' : 'disabled'}>
                        <i class="fas fa-credit-card"></i>
                        <strong>Cartão de crédito</strong>
                    </button>
                </div>
            </div>
        `);

        modalBody.querySelectorAll('[data-premium-method]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.dataset.premiumMethod === 'pix') {
                    startPixCheckout(plan);
                    return;
                }

                startCardCheckout(plan);
            });
        });
    }

    function updatePixStatus(status, message) {
        const chip = document.getElementById('premiumPixStatus');
        const feedback = document.getElementById('premiumPixFeedback');
        if (!chip && !feedback) return;

        const statusMap = {
            approved: { icon: 'fa-check-circle', label: 'Pagamento aprovado', className: 'is-approved' },
            pending: { icon: 'fa-hourglass-half', label: 'Aguardando pagamento', className: '' },
            in_process: { icon: 'fa-clock', label: 'Pagamento em análise', className: '' },
            rejected: { icon: 'fa-times-circle', label: 'Pagamento com erro', className: '' },
        };
        const payload = statusMap[status] || statusMap.pending;

        if (chip) {
            chip.className = `rr-premium-checkout__status ${payload.className}`.trim();
            chip.innerHTML = `<i class="fas ${payload.icon}"></i><span>${payload.label}</span>`;
        }

        if (feedback && message) {
            feedback.style.display = 'block';
            feedback.textContent = message;
            feedback.classList.remove('is-success', 'is-error');
            if (status === 'approved') feedback.classList.add('is-success');
            if (status === 'rejected') feedback.classList.add('is-error');
        } else if (feedback) {
            feedback.style.display = 'none';
        }
    }

    async function checkPaymentStatus(paymentId, silent = true) {
        try {
            const response = await fetch(API_BASE + '/payment/' + encodeURIComponent(paymentId) + '/status', {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });
            const data = await response.json();

            if (data.success && data.status === 'approved') {
                showModalSuccess(data.message || 'Pagamento confirmado.', 'Premium ativado');
                return;
            }

            if (data.success && data.status) {
                updatePixStatus(data.status, data.message || 'Pagamento ainda não confirmado.');
                return;
            }

            if (!silent) {
                updatePixStatus('pending', data.message || 'Ainda aguardando a confirmação do Mercado Pago.');
            }
        } catch (error) {
            console.error('Erro ao consultar status premium:', error);
            if (!silent) {
                updatePixStatus('pending', 'Ainda não foi possível consultar o pagamento. Tentaremos de novo.');
            }
        }
    }

    function startPaymentPolling(paymentId) {
        stopPaymentPolling();
        premiumState.pollInterval = setInterval(() => checkPaymentStatus(paymentId, false), 4000);
    }

    function loadMercadoPagoSdk() {
        if (window.MercadoPago) {
            return Promise.resolve(window.MercadoPago);
        }

        if (premiumState.sdkPromise) {
            return premiumState.sdkPromise;
        }

        premiumState.sdkPromise = new Promise((resolve, reject) => {
            const existing = document.querySelector('script[data-premium-mp-sdk="true"]');
            if (existing && window.MercadoPago) {
                resolve(window.MercadoPago);
                return;
            }

            const script = existing || document.createElement('script');
            script.async = true;
            script.src = 'https://sdk.mercadopago.com/js/v2';
            script.dataset.premiumMpSdk = 'true';
            script.onload = () => resolve(window.MercadoPago);
            script.onerror = () => reject(new Error('Falha ao carregar o SDK do Mercado Pago.'));

            if (!existing) {
                document.head.appendChild(script);
            }
        });

        return premiumState.sdkPromise;
    }

    async function fetchMercadoPagoPublicKey() {
        if (premiumState.publicKey) return premiumState.publicKey;

        const response = await fetch(API_BASE + '/mp-public-key', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        });
        const data = await response.json();

        if (!data.success || !data.public_key) {
            throw new Error(data.message || 'Public key do Mercado Pago não configurada.');
        }

        premiumState.publicKey = data.public_key;
        return premiumState.publicKey;
    }

    async function ensureMercadoPago() {
        if (premiumState.mp) return premiumState.mp;

        await loadMercadoPagoSdk();
        const publicKey = await fetchMercadoPagoPublicKey();
        if (typeof window.MercadoPago !== 'function') {
            throw new Error('SDK do Mercado Pago indisponível.');
        }

        premiumState.mp = new window.MercadoPago(publicKey, { locale: 'pt-BR' });
        return premiumState.mp;
    }

    function setCardFeedback(message, type = '') {
        const feedback = document.getElementById('premiumCardFeedback');
        if (!feedback) return;

        feedback.style.display = message ? 'block' : 'none';
        feedback.className = 'rr-premium-checkout__feedback' + (type ? ' is-' + type : '');
        feedback.textContent = message || '';
    }

    function setCardBusy(isBusy, message = '') {
        premiumState.isCardSubmitting = isBusy;

        const submitButton = document.getElementById('premiumCardSubmit');
        if (submitButton) {
            submitButton.disabled = isBusy;
            submitButton.innerHTML = isBusy
                ? '<i class="fas fa-spinner fa-spin"></i><span>Validando cartão...</span>'
                : '<i class="fas fa-lock"></i><span>Finalizar compra</span>';
        }
    }

    function applyCardDefaults() {
        const cpfDigits = String(USER_DEFAULTS.cpf || '').replace(/\D+/g, '');
        const emailInput = document.getElementById('premiumCardEmail');
        const documentInput = document.getElementById('premiumCardIdentificationNumber');

        if (emailInput && USER_DEFAULTS.email && !emailInput.value) {
            emailInput.value = USER_DEFAULTS.email;
        }

        if (documentInput && cpfDigits) {
            documentInput.value = cpfDigits;
            documentInput.readOnly = true;
        }

        if (cpfDigits) {
            let attempts = 0;
            const interval = window.setInterval(() => {
                attempts += 1;
                const typeSelect = document.getElementById('premiumCardIdentificationType');
                if (!typeSelect) return;
                const hasCpfOption = Array.from(typeSelect.options || []).some((option) => option.value === 'CPF');
                if (hasCpfOption) {
                    typeSelect.value = 'CPF';
                    typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    window.clearInterval(interval);
                } else if (attempts > 12) {
                    window.clearInterval(interval);
                }
            }, 180);
        }
    }

    function getUserCpfDigits() {
        return String(USER_DEFAULTS.cpf || '').replace(/\D+/g, '');
    }

    function getUserEmail() {
        return String(USER_DEFAULTS.email || '').trim();
    }

    function renderCardCheckout(plan) {
        const methods = normalizePaymentMethods(plan.payment_methods);
        const cpfDigits = String(USER_DEFAULTS.cpf || '').replace(/\D+/g, '');

        setModalBody(`
            <div class="rr-premium-checkout rr-premium-checkout--card-mode">
                ${buildCheckoutHero(plan)}
                <div class="rr-premium-checkout__card-shell">
                    <div class="rr-premium-checkout__row">
                        <h4>Cartão de crédito</h4>
                        ${methods.length > 1 ? `
                            <button type="button" class="rr-premium-checkout__back" data-premium-back>
                                <i class="fas fa-arrow-left"></i>
                                <span>Trocar método</span>
                            </button>
                        ` : ''}
                    </div>
                    <form id="premiumCardForm" class="rr-premium-checkout__card-form">
                        <div class="rr-premium-checkout__form-grid">
                            <div class="rr-premium-checkout__field rr-premium-checkout__field--full">
                                <label class="rr-premium-checkout__label" for="premiumCardNumber">Número do cartão</label>
                                <div id="premiumCardNumber" class="rr-premium-checkout__hosted"></div>
                            </div>
                            <div class="rr-premium-checkout__field">
                                <label class="rr-premium-checkout__label" for="premiumCardExpiry">Validade</label>
                                <div id="premiumCardExpiry" class="rr-premium-checkout__hosted"></div>
                            </div>
                            <div class="rr-premium-checkout__field">
                                <label class="rr-premium-checkout__label" for="premiumCardCvv">CVV</label>
                                <div id="premiumCardCvv" class="rr-premium-checkout__hosted"></div>
                            </div>
                            <div class="rr-premium-checkout__field rr-premium-checkout__field--full">
                                <label class="rr-premium-checkout__label" for="premiumCardHolder">Nome no cartão</label>
                                <input id="premiumCardHolder" class="rr-premium-checkout__input" type="text" placeholder="Como aparece no cartão" autocomplete="cc-name">
                            </div>
                            <div class="rr-premium-checkout__field">
                                <label class="rr-premium-checkout__label" for="premiumCardInstallments">Parcelas</label>
                                <select id="premiumCardInstallments" class="rr-premium-checkout__select"></select>
                            </div>
                            <div class="rr-premium-checkout__field rr-premium-checkout__field--hidden" aria-hidden="true">
                                <label class="rr-premium-checkout__label" for="premiumCardEmail">E-mail</label>
                                <input id="premiumCardEmail" class="rr-premium-checkout__input" type="email" value="${escapeHtml(USER_DEFAULTS.email || '')}" autocomplete="email" tabindex="-1" readonly>
                            </div>
                            <div class="rr-premium-checkout__field rr-premium-checkout__field--hidden" aria-hidden="true">
                                <label class="rr-premium-checkout__label" for="premiumCardIssuer">Banco emissor</label>
                                <select id="premiumCardIssuer" class="rr-premium-checkout__select" tabindex="-1"></select>
                            </div>
                            <div class="rr-premium-checkout__field rr-premium-checkout__field--hidden" aria-hidden="true">
                                <label class="rr-premium-checkout__label" for="premiumCardIdentificationType">Documento</label>
                                <select id="premiumCardIdentificationType" class="rr-premium-checkout__select" tabindex="-1"></select>
                            </div>
                            <div class="rr-premium-checkout__field rr-premium-checkout__field--hidden" aria-hidden="true">
                                <label class="rr-premium-checkout__label" for="premiumCardIdentificationNumber">CPF</label>
                                <input id="premiumCardIdentificationNumber" class="rr-premium-checkout__input" type="hidden" inputmode="numeric" value="${escapeHtml(cpfDigits)}" readonly>
                            </div>
                        </div>
                        <div id="premiumCardFeedback" class="rr-premium-checkout__feedback" style="display:none;"></div>
                        <button id="premiumCardSubmit" class="rr-premium-btn rr-premium-btn--primary rr-premium-checkout__submit" type="submit">
                            <i class="fas fa-lock"></i>
                            <span>Finalizar compra</span>
                        </button>
                    </form>
                </div>
            </div>
        `);

        modalBody.querySelector('[data-premium-back]')?.addEventListener('click', () => renderMethodPicker(plan));
    }

    async function mountCardForm(plan) {
        const mp = await ensureMercadoPago();
        destroyCardForm();
        applyCardDefaults();

        premiumState.cardForm = mp.cardForm({
            amount: String(plan.price),
            iframe: true,
            form: {
                id: 'premiumCardForm',
                cardholderName: { id: 'premiumCardHolder', placeholder: 'Como aparece no cartão' },
                cardholderEmail: { id: 'premiumCardEmail', placeholder: 'Seu melhor e-mail' },
                cardNumber: { id: 'premiumCardNumber', placeholder: '1234 1234 1234 1234' },
                expirationDate: { id: 'premiumCardExpiry', placeholder: 'MM/AA' },
                securityCode: { id: 'premiumCardCvv', placeholder: 'CVV' },
                installments: { id: 'premiumCardInstallments', placeholder: 'Parcelas' },
                identificationType: { id: 'premiumCardIdentificationType', placeholder: 'Documento' },
                identificationNumber: { id: 'premiumCardIdentificationNumber', placeholder: 'CPF do titular' },
                issuer: { id: 'premiumCardIssuer', placeholder: 'Banco emissor' },
            },
            callbacks: {
                onFormMounted: (error) => {
                    if (error) {
                        console.error('Erro ao montar card form premium:', error);
                        setCardFeedback('Não foi possível carregar o formulário do cartão. Atualize a página e tente novamente.', 'error');
                        return;
                    }

                    setCardBusy(false, 'O Mercado Pago valida bandeira, emissor e parcelas enquanto você preenche.');
                    applyCardDefaults();
                },
                onSubmit: (event) => {
                    event.preventDefault();
                    processCardPayment(plan);
                },
                onFetching: () => {
                    if (!premiumState.isCardSubmitting) {
                        setCardFeedback('Validando cartão em tempo real pelo Mercado Pago...', '');
                    }

                    return () => {
                        if (!premiumState.isCardSubmitting) {
                            setCardFeedback('', '');
                        }
                    };
                }
            }
        });
    }

    async function startCardCheckout(plan) {
        premiumState.selectedPlanSlug = plan.slug;
        stopPaymentPolling();
        showModal();
        renderCardCheckout(plan);
        setCardFeedback('');
        setCardBusy(true, 'Carregando validacao segura do Mercado Pago...');

        if (!getUserEmail()) {
            showModalError('Seu e-mail não está disponível no cadastro. Atualize seu perfil para pagar com cartão.');
            return;
        }

        if (!getUserCpfDigits()) {
            showModalError('Seu CPF não está disponível no cadastro. Atualize seu perfil para pagar com cartão.');
            return;
        }

        try {
            await mountCardForm(plan);
        } catch (error) {
            console.error('Erro ao iniciar cartao premium:', error);
            showModalError(error.message || 'Não foi possível carregar o pagamento por cartão.');
        }
    }

    async function processCardPayment(plan) {
        const cardForm = premiumState.cardForm;
        if (!cardForm || typeof cardForm.getCardFormData !== 'function') {
            setCardFeedback('Formulário do cartão indisponível. Reabra o checkout e tente novamente.', 'error');
            return;
        }

        const cardData = cardForm.getCardFormData();
        if (!cardData.token || !cardData.paymentMethodId) {
            setCardFeedback('Complete os dados do cartão para continuar.', 'error');
            return;
        }

        const userEmail = getUserEmail();
        if (!userEmail) {
            setCardFeedback('Seu e-mail não está disponível no cadastro. Atualize seu perfil para continuar.', 'error');
            return;
        }

        const cpfDigits = getUserCpfDigits();
        if (!cpfDigits) {
            setCardFeedback('Seu CPF não está disponível no cadastro. Atualize seu perfil para continuar.', 'error');
            return;
        }

        setCardFeedback('');
        setCardBusy(true, 'Verificando e enviando o cartão para o Mercado Pago...');

        try {
            const response = await fetch(API_BASE + '/process-card', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    plan_slug: plan.slug,
                    card_token: cardData.token,
                    payment_method_id: cardData.paymentMethodId,
                    issuer_id: cardData.issuerId || null,
                    installments: Number(cardData.installments || 1),
                    payer_email: cardData.cardholderEmail || userEmail,
                    identification_type: cardData.identificationType || 'CPF',
                    identification_number: cardData.identificationNumber || cpfDigits,
                })
            });
            const data = await response.json();

            if (!data.success) {
                setCardBusy(false, data.message || 'Não foi possível aprovar o cartão.');
                setCardFeedback(data.message || 'Não foi possível aprovar o cartão.', 'error');
                return;
            }

            if (data.status === 'pending') {
                setCardBusy(false, 'Pagamento enviado. Aguarde a análise do emissor.');
                setCardFeedback(data.message || 'Pagamento em análise. Você será avisado quando o Premium for liberado.', '');
                return;
            }

            showModalSuccess(data.message || 'Pagamento aprovado! Bem-vindo ao Premium!');
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            console.error('Erro cartao premium:', error);
            setCardBusy(false, 'Erro ao validar o cartão.');
            setCardFeedback('Erro de conexão ao validar o cartão. Tente novamente.', 'error');
        }
    }

    async function startPixCheckout(plan) {
        premiumState.selectedPlanSlug = plan.slug;
        destroyCardForm();
        stopPaymentPolling();
        showModal();
        showModalLoading('Gerando PIX premium...');

        try {
            const response = await fetch(API_BASE + '/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ plan_slug: plan.slug, payment_method: 'pix' })
            });
            const data = await response.json();

            if (!data.success || !data.payment) {
                showModalError(data.message || 'Não foi possível gerar o PIX do Premium.');
                return;
            }

            renderPixCheckout(plan, data.payment);
        } catch (error) {
            console.error('Erro PIX premium:', error);
            showModalError('Erro de conexão ao gerar o PIX. Tente novamente.');
        }
    }

    function renderPixCheckout(plan, payment) {
        const methods = normalizePaymentMethods(plan.payment_methods);
        const qrMarkup = payment.qr_code_base64
            ? `<div class="rr-premium-checkout__qr-image"><img src="${payment.qr_code_base64}" alt="QR Code PIX Premium"></div>`
            : `<div class="rr-premium-checkout__empty-qr">QR Code indisponível no momento.</div>`;

        setModalBody(`
            <div class="rr-premium-checkout">
                ${buildCheckoutHero(plan)}
                <div class="rr-premium-checkout__pix">
                    <div class="rr-premium-checkout__qr-card">
                        ${qrMarkup}
                        <div class="rr-premium-checkout__status" id="premiumPixStatus">
                            <i class="fas fa-hourglass-half"></i>
                            <span>Aguardando pagamento</span>
                        </div>
                    </div>
                    <div class="rr-premium-checkout__info-card">
                        <h4>PIX</h4>
                        <div class="rr-premium-checkout__code">${escapeHtml(payment.pix_code || payment.qr_code || '')}</div>
                        <div class="rr-premium-checkout__actions">
                            <button type="button" class="rr-premium-btn rr-premium-btn--primary" id="premiumPixCopyBtn">
                                <i class="fas fa-copy"></i>
                                <span>Copiar PIX</span>
                            </button>
                            <button type="button" class="rr-premium-btn rr-premium-btn--primary" id="premiumPixVerifyBtn">
                                <i class="fas fa-rotate"></i>
                                <span>Verificar</span>
                            </button>
                            ${methods.length > 1 ? `
                                <button type="button" class="rr-premium-btn rr-premium-btn--ghost" data-premium-back>
                                    <i class="fas fa-repeat"></i>
                                    <span>Trocar método</span>
                                </button>
                            ` : `
                                <button type="button" class="rr-premium-btn rr-premium-btn--ghost" data-premium-close>
                                    <i class="fas fa-times"></i>
                                    <span>Fechar</span>
                                </button>
                            `}
                        </div>
                        <div class="rr-premium-checkout__feedback" id="premiumPixFeedback" style="display:none;"></div>
                    </div>
                </div>
            </div>
        `);

        modalBody.querySelector('#premiumPixCopyBtn')?.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(payment.pix_code || payment.qr_code || '');
                updatePixStatus('pending', 'PIX copiado. Depois do pagamento, esta tela atualiza sozinha.');
            } catch (error) {
                updatePixStatus('rejected', 'Não foi possível copiar automaticamente. Copie manualmente o código acima.');
            }
        });
        modalBody.querySelector('#premiumPixVerifyBtn')?.addEventListener('click', async () => {
            updatePixStatus('pending', 'Verificando pagamento...');
            await checkPaymentStatus(payment.id, false);
        });

        modalBody.querySelector('[data-premium-back]')?.addEventListener('click', () => renderMethodPicker(plan));
        modalBody.querySelector('[data-premium-close]')?.addEventListener('click', hideModal);
    }

    async function handlePlanSelect(planSlug, hasTrial, isRecurring, forcePayment = false) {
        @if(!auth()->check())
            const premiumStateUrl = buildPremiumLoginRedirect();
            const premiumRedirect = buildPremiumLoginRedirect(planSlug);

            try {
                const redirectUrl = new URL(premiumStateUrl);
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', redirectUrl.pathname + redirectUrl.search + redirectUrl.hash);
                }
            } catch (error) {
                console.warn('Premium redirect state warning:', error);
            }

            if (typeof window.openAuthModal === 'function') {
                window.openAuthModal();
                return;
            }

            if (window.RRAuthModal && typeof window.RRAuthModal.open === 'function') {
                window.RRAuthModal.open();
                return;
            }

            window.location.href = '{{ route("user.login") }}?redirect=' + encodeURIComponent(premiumRedirect);
            return;
        @endif

        if (hasTrial && !forcePayment) {
            showModal();
            showModalLoading('Ativando seu trial...');

            try {
                const response = await fetch(API_BASE + '/start-trial', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ plan_slug: planSlug })
                });
                const data = await response.json();
                if (data.success) {
                    showModalSuccess(data.message || 'Trial ativado com sucesso.');
                    setTimeout(() => window.location.reload(), 1800);
                } else {
                    showModalError(data.message || 'Não foi possível ativar o trial.');
                }
            } catch (error) {
                console.error('Erro premium trial:', error);
                showModalError('Erro de conexão. Tente novamente.');
            }

            return;
        }

        const plan = premiumState.plans.get(planSlug);
        if (!plan) {
            showModal();
            showModalError('Plano premium não encontrado. Atualize a página e tente novamente.');
            return;
        }

        premiumState.selectedPlanSlug = planSlug;
        showModal();
        stopPaymentPolling();
        destroyCardForm();

        const methods = normalizePaymentMethods(plan.payment_methods);
        if (methods.length === 1) {
            if (methods[0] === 'pix') {
                startPixCheckout(plan);
                return;
            }

            startCardCheckout(plan);
            return;
        }

        renderMethodPicker(plan);
    }
    function initSmoothScroll() {
        document.querySelectorAll('.rr-premium-landing a[href^="#"]').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }
    function init() {
        initParticles();
        initMobileCardSpotlight();
        initSmoothScroll();
        loadPlans();
        closeBtn?.addEventListener('click', hideModal);
        modal?.querySelector('.rr-premium-modal__backdrop')?.addEventListener('click', hideModal);
        const onKeydown = (event) => {
            if (event.key === 'Escape' && modal?.style.display === 'flex') {
                hideModal();
            }
        };
        document.addEventListener('keydown', onKeydown);
        registerCleanup(() => document.removeEventListener('keydown', onKeydown));
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
</script>
