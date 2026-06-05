@php
    $user = auth()->user();
    $avatarUrl = $user && $user->image ? asset('assets/images/user/profile/' . $user->image) : null;
    $isPremium = $user && method_exists($user, 'isPremium') ? $user->isPremium() : false;
    $requiresPrizeProfile = $user && method_exists($user, 'requiresFullProfileForPrizes') ? $user->requiresFullProfileForPrizes() : false;

    // Só obrigar perfil completo quando já houver prêmio ganho
    $profileIncomplete = $requiresPrizeProfile && $user && method_exists($user, 'isPrizeProfileComplete')
        ? !$user->isPrizeProfileComplete()
        : false;
@endphp

@guest
<div class="rr-perfil-container">
    <div class="rr-perfil-guest-shell">
        <div class="rr-perfil-card rr-perfil-guest-card">
            <div class="rr-perfil-guest-layout">
                <div class="rr-perfil-guest-copy">
                    <span class="rr-perfil-guest-badge">Área protegida</span>

                    <div class="rr-perfil-guest-icon-wrap" aria-hidden="true">
                        <i class="fas fa-lock"></i>
                    </div>

                    <h2 class="rr-perfil-guest-title">Entre para acessar seu perfil</h2>
                    <p class="rr-perfil-guest-description">Faça login para editar seus dados, acompanhar premiações e gerenciar sua conta.</p>

                    <div class="rr-perfil-guest-points" aria-hidden="true">
                        <span class="rr-perfil-guest-point"><i class="fas fa-id-card"></i> Dados</span>
                        <span class="rr-perfil-guest-point"><i class="fas fa-wallet"></i> Pix</span>
                        <span class="rr-perfil-guest-point"><i class="fas fa-medal"></i> Prêmios</span>
                    </div>

                    <button onclick="window.RRAuthModal?.open()" class="rr-perfil-btn rr-perfil-btn--primary rr-perfil-guest-login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Fazer Login
                    </button>

                    <p class="rr-perfil-guest-footnote">Login rápido e seguro</p>
                </div>

                <div class="rr-perfil-guest-visual" aria-hidden="true">
                    <div class="rr-perfil-guest-orbit">
                        <span class="rr-perfil-guest-orbit__badge"><i class="fas fa-shield-alt"></i> Seguro</span>
                        <div class="rr-perfil-guest-orbit__core">
                            <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="" loading="lazy">
                        </div>
                    </div>

                    <article class="rr-perfil-guest-floater rr-perfil-guest-floater--top">
                        <i class="fas fa-user-shield"></i>
                        <strong>Conta protegida</strong>
                        <span>Acesso individual</span>
                    </article>

                    <article class="rr-perfil-guest-floater rr-perfil-guest-floater--bottom">
                        <i class="fas fa-bolt"></i>
                        <strong>Tudo em um só lugar</strong>
                        <span>Perfil, ganhos e PIX</span>
                    </article>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ========================================
   AFFILIATE STYLES
   ======================================== */

.rr-perfil-guest-shell {
    max-width: min(920px, 96vw);
    margin: 52px auto 28px;
}

.rr-perfil-guest-card {
    position: relative;
    overflow: hidden;
    padding: 0;
    border-radius: 28px;
    border: 1px solid rgba(249, 115, 22, 0.32);
    background:
        radial-gradient(circle at 12% 12%, rgba(249, 115, 22, 0.2) 0%, rgba(249, 115, 22, 0.04) 34%, transparent 58%),
        linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(2, 6, 23, 0.98));
    box-shadow: 0 22px 48px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    will-change: transform;
}

.rr-perfil-guest-card::before {
    content: '';
    position: absolute;
    inset: -28% -8% auto;
    height: 68%;
    background: radial-gradient(circle at center, rgba(249, 115, 22, 0.22), transparent 68%);
    opacity: 0.4;
    pointer-events: none;
    animation: rrPerfilGuestGlow 7s ease-in-out infinite;
}

.rr-perfil-guest-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    border: 1px solid rgba(255, 255, 255, 0.05);
    pointer-events: none;
}

.rr-perfil-guest-card:hover {
    border-color: rgba(249, 115, 22, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 22px 48px rgba(0, 0, 0, 0.46), inset 0 1px 0 rgba(255, 255, 255, 0.07);
}

.rr-perfil-guest-layout {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(280px, 0.92fr);
    gap: 22px;
    align-items: stretch;
    padding: 26px;
}

.rr-perfil-guest-copy {
    display: grid;
    align-content: center;
    justify-items: start;
    gap: 12px;
    min-width: 0;
}

.rr-perfil-guest-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 32px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid rgba(249, 115, 22, 0.45);
    background: rgba(249, 115, 22, 0.14);
    color: #fed7aa;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
}

.rr-perfil-guest-icon-wrap {
    width: 80px;
    height: 80px;
    margin: 4px 0 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 32% 28%, rgba(249, 115, 22, 0.45), rgba(234, 88, 12, 0.2));
    border: 1px solid rgba(249, 115, 22, 0.5);
    box-shadow: 0 10px 22px rgba(249, 115, 22, 0.25);
}

.rr-perfil-guest-icon-wrap i {
    font-size: 2rem;
    color: #fff;
}

.rr-perfil-guest-title {
    margin: 0;
    font-size: clamp(1.35rem, 2.4vw, 1.95rem);
    line-height: 1.22;
    font-weight: 800;
    color: #f8fafc;
    letter-spacing: -0.02em;
}

.rr-perfil-guest-description {
    margin: 0;
    max-width: 44ch;
    color: #cbd5e1;
    font-size: 0.98rem;
    line-height: 1.65;
}

.rr-perfil-guest-points {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.rr-perfil-guest-point {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.05);
    color: #e2e8f0;
    font-size: 0.78rem;
    font-weight: 700;
}

.rr-perfil-guest-point i {
    color: #fb923c;
    font-size: 0.78rem;
}

.rr-perfil-guest-login-btn {
    margin: 2px 0 0;
    width: min(320px, 100%);
    border-radius: 14px;
    min-height: 52px;
    font-size: 1rem;
    font-weight: 700;
    box-shadow: 0 10px 22px rgba(249, 115, 22, 0.28);
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}

.rr-perfil-guest-login-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 26px rgba(249, 115, 22, 0.32);
    filter: saturate(1.08);
}

.rr-perfil-guest-login-btn:active {
    transform: translateY(0);
    box-shadow: 0 7px 18px rgba(249, 115, 22, 0.24);
}

.rr-perfil-guest-footnote {
    margin: 0;
    color: #94a3b8;
    font-size: 0.85rem;
}

.rr-perfil-guest-visual {
    position: relative;
    min-height: 100%;
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background:
        radial-gradient(circle at top right, rgba(249, 115, 22, 0.18), transparent 40%),
        linear-gradient(165deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02));
    padding: 22px;
    overflow: hidden;
    display: grid;
    align-content: center;
    justify-items: center;
    gap: 18px;
}

.rr-perfil-guest-visual::before {
    content: '';
    position: absolute;
    inset: auto -18% -34% auto;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(37, 99, 235, 0.18), transparent 70%);
    filter: blur(8px);
    pointer-events: none;
}

.rr-perfil-guest-orbit {
    position: relative;
    width: 100%;
    min-height: 176px;
    display: grid;
    place-items: center;
}

.rr-perfil-guest-orbit__core {
    width: 124px;
    height: 124px;
    border-radius: 30px;
    display: grid;
    place-items: center;
    background:
        radial-gradient(circle at 30% 25%, rgba(255, 255, 255, 0.2), transparent 38%),
        linear-gradient(145deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.74));
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.06),
        0 18px 30px rgba(2, 6, 23, 0.26);
}

.rr-perfil-guest-orbit__core img {
    width: 74px;
    height: 74px;
    object-fit: contain;
    filter: drop-shadow(0 12px 18px rgba(249, 115, 22, 0.22));
}

.rr-perfil-guest-orbit__badge {
    position: absolute;
    top: 6px;
    right: 24px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.74);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #fff7ed;
    font-size: 0.75rem;
    font-weight: 800;
}

.rr-perfil-guest-floater {
    width: min(220px, 100%);
    display: grid;
    gap: 4px;
    padding: 14px 16px;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(15, 23, 42, 0.58);
    box-shadow: 0 16px 28px rgba(2, 6, 23, 0.18);
}

.rr-perfil-guest-floater i {
    color: #fb923c;
    font-size: 1rem;
}

.rr-perfil-guest-floater strong {
    color: #fff7ed;
    font-size: 0.92rem;
    font-weight: 800;
}

.rr-perfil-guest-floater span {
    color: #cbd5e1;
    font-size: 0.78rem;
    line-height: 1.45;
}

body.light .rr-perfil-guest-card {
    border-color: rgba(234, 88, 12, 0.18);
    background:
        radial-gradient(circle at 14% 10%, rgba(251, 146, 60, 0.18) 0%, rgba(251, 146, 60, 0.05) 32%, transparent 58%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(255, 247, 237, 0.98));
    box-shadow:
        0 20px 44px rgba(234, 88, 12, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.85);
}

body.light .rr-perfil-guest-card::before {
    background: radial-gradient(circle at center, rgba(251, 146, 60, 0.16), transparent 70%);
    opacity: 0.5;
}

body.light .rr-perfil-guest-card::after {
    border-color: rgba(234, 88, 12, 0.08);
}

body.light .rr-perfil-guest-badge {
    background: linear-gradient(135deg, rgba(255, 237, 213, 0.98), rgba(255, 247, 237, 0.96));
    border-color: rgba(251, 146, 60, 0.34);
    color: #c2410c;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
}

body.light .rr-perfil-guest-icon-wrap {
    background: radial-gradient(circle at 32% 28%, rgba(251, 146, 60, 0.34), rgba(249, 115, 22, 0.12));
    border-color: rgba(251, 146, 60, 0.34);
    box-shadow: 0 14px 24px rgba(251, 146, 60, 0.16);
}

body.light .rr-perfil-guest-icon-wrap i {
    color: #fff7ed;
}

body.light .rr-perfil-guest-title {
    color: #172033;
}

body.light .rr-perfil-guest-description {
    color: #64748b;
}

body.light .rr-perfil-guest-point {
    background: linear-gradient(180deg, rgba(255, 251, 247, 0.98), rgba(255, 244, 233, 0.96));
    border-color: rgba(234, 88, 12, 0.12);
    color: #7c2d12;
}

body.light .rr-perfil-guest-point i {
    color: #ea580c;
}

body.light .rr-perfil-guest-visual {
    border-color: rgba(234, 88, 12, 0.12);
    background:
        radial-gradient(circle at top right, rgba(251, 146, 60, 0.16), transparent 42%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.94), rgba(255, 242, 228, 0.98));
}

body.light .rr-perfil-guest-visual::before {
    background: radial-gradient(circle, rgba(59, 130, 246, 0.12), transparent 70%);
}

body.light .rr-perfil-guest-orbit__core {
    background:
        radial-gradient(circle at 30% 25%, rgba(255, 255, 255, 0.84), transparent 38%),
        linear-gradient(145deg, rgba(255, 243, 230, 0.98), rgba(255, 233, 210, 0.98));
    border-color: rgba(234, 88, 12, 0.14);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.9),
        0 18px 30px rgba(234, 88, 12, 0.12);
}

body.light .rr-perfil-guest-orbit__badge {
    background: rgba(255, 255, 255, 0.92);
    border-color: rgba(34, 197, 94, 0.2);
    color: #166534;
}

body.light .rr-perfil-guest-floater {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(255, 247, 237, 0.96));
    border-color: rgba(234, 88, 12, 0.12);
    box-shadow: 0 16px 28px rgba(234, 88, 12, 0.08);
}

body.light .rr-perfil-guest-floater strong {
    color: #172033;
}

body.light .rr-perfil-guest-floater span,
body.light .rr-perfil-guest-footnote {
    color: #64748b;
}

@keyframes rrPerfilGuestGlow {
    0%, 100% { transform: translate3d(-4%, 0, 0) scale(1); opacity: 0.34; }
    50% { transform: translate3d(4%, 0, 0) scale(1.04); opacity: 0.5; }
}

@media (prefers-reduced-motion: reduce) {
    .rr-perfil-guest-card,
    .rr-perfil-guest-login-btn {
        transition: none;
    }

    .rr-perfil-guest-card::before {
        animation: none;
    }
}

/* Barra de Progresso */
.rr-affiliate-progress-container {
    background: rgba(15, 23, 42, 0.8);
    border-radius: 12px;
    height: 40px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(16, 185, 129, 0.2);
    box-shadow: inset 0 2px 8px rgba(0,0,0,0.3);
}

.rr-affiliate-progress-bar {
    background: linear-gradient(90deg, #10b981, #059669);
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: width 0.5s ease;
    position: relative;
    overflow: visible;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    min-width: 90px;
}

.rr-affiliate-progress-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

.rr-affiliate-progress-text {
    position: relative;
    z-index: 2;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.95rem;
    text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    display: inline-block;
    white-space: nowrap !important;
    word-break: keep-all;
    padding: 0 8px;
}

/* Container único para cards da aba Afiliados */
.rr-affiliate-cards-container {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 18px;
    padding: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.35);
}

@media (min-width: 769px) {
    .rr-affiliate-cards-container {
        padding: 20px 24px;
    }
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Stats Cards */
.rr-affiliate-stat-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.rr-affiliate-stat-card:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(16, 185, 129, 0.3);
    transform: translateY(-2px);
}

.rr-affiliate-stat-icon {
    font-size: 2rem;
    margin-bottom: 8px;
}

.rr-affiliate-stat-label {
    color: #94a3b8;
    font-size: 0.85rem;
    margin-bottom: 6px;
}

.rr-affiliate-stat-value {
    color: #e2e8f0;
    font-size: 1.4rem;
    font-weight: 700;
}

/* Rate Items */
.rr-affiliate-rate-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    color: #cbd5e1;
}

.rr-affiliate-rate-item:last-child {
    border-bottom: none;
}

/* MOBILE STYLES - LAYOUT COMPACTO */
@media (max-width: 768px) {
    .rr-perfil-guest-shell {
        margin: 24px auto 10px;
    }

    .rr-perfil-guest-card {
        border-radius: 20px;
    }

    .rr-perfil-guest-layout {
        grid-template-columns: 1fr;
        gap: 14px;
        padding: 18px 16px;
    }

    .rr-perfil-guest-copy {
        justify-items: center;
        text-align: center;
    }

    .rr-perfil-guest-icon-wrap {
        width: 66px;
        height: 66px;
        margin: 4px 0 0;
    }

    .rr-perfil-guest-icon-wrap i {
        font-size: 1.65rem;
    }

    .rr-perfil-guest-description {
        font-size: 0.92rem;
        line-height: 1.55;
        max-width: 100%;
    }

    .rr-perfil-guest-points {
        justify-content: center;
        gap: 8px;
    }

    .rr-perfil-guest-point {
        min-height: 32px;
        padding: 0 10px;
        font-size: 0.72rem;
    }

    .rr-perfil-guest-visual {
        min-height: auto;
        padding: 16px;
        gap: 12px;
        border-radius: 18px;
    }

    .rr-perfil-guest-orbit {
        min-height: 132px;
    }

    .rr-perfil-guest-orbit__core {
        width: 98px;
        height: 98px;
        border-radius: 24px;
    }

    .rr-perfil-guest-orbit__core img {
        width: 58px;
        height: 58px;
    }

    .rr-perfil-guest-orbit__badge {
        top: 0;
        right: 10px;
        min-height: 28px;
        padding: 0 10px;
        font-size: 0.68rem;
    }

    .rr-perfil-guest-floater {
        width: 100%;
        padding: 12px 14px;
        border-radius: 16px;
    }

    .rr-perfil-guest-login-btn {
        min-height: 48px;
        margin-top: 18px;
        font-size: 0.95rem;
    }

    .rr-perfil-guest-footnote {
        margin-top: 10px;
        font-size: 0.78rem;
    }

    /* PREVINE OVERFLOW HORIZONTAL - SUPER AGRESSIVO */
    html, body {
        overflow-x: hidden !important;
        width: 100% !important;
        max-width: 100vw !important;
    }

    /* Força TODOS os elementos a respeitarem 100vw */
    * {
        max-width: 100vw;
        box-sizing: border-box !important;
    }

    /* Container principal do perfil */
    .rr-perfil-container {
        max-width: 100vw !important;
        width: 100% !important;
        overflow-x: hidden !important;
        padding: 12px 6px !important;
        box-sizing: border-box !important;
        margin: 0 !important;
    }

    /* SOBRESCREVE TODOS OS GRIDS COM INLINE STYLES */
    .rr-perfil-affiliate-dashboard [style*="grid-template-columns"],
    .rr-perfil-affiliate-activation [style*="grid-template-columns"],
    [style*="grid-template-columns: repeat(auto-fit"],
    [style*="display: grid"] {
        grid-template-columns: 1fr !important;
        display: grid !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Remove TODOS os paddings inline */
    [style*="padding"] {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    /* Dashboard de Afiliado - COMPACTO */
    .rr-perfil-affiliate-dashboard {
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    /* Stats Grid - MINI CARDS */
    .rr-perfil-affiliate-dashboard > .rr-perfil-grid.rr-perfil-grid--full > div:first-child,
    .rr-perfil-affiliate-dashboard > .rr-perfil-grid.rr-perfil-grid--full > div[style*="grid-template-columns"] {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
        margin-bottom: 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        display: grid !important;
    }

    .rr-affiliate-stat-card {
        padding: 10px 8px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        border-radius: 8px !important;
    }

    .rr-affiliate-stat-icon {
        font-size: 1.2rem !important;
        margin-bottom: 4px !important;
    }

    .rr-affiliate-stat-label {
        font-size: 0.7rem !important;
        margin-bottom: 3px !important;
    }

    .rr-affiliate-stat-value {
        font-size: 0.95rem !important;
        font-weight: 600 !important;
    }

    /* Grid 2 Colunas vira 1 no mobile */
    .rr-perfil-affiliate-dashboard .rr-perfil-grid {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .rr-perfil-grid {
        grid-template-columns: 1fr !important;
        width: 100% !important;
        max-width: 100% !important;
        gap: 10px !important;
    }

    .rr-perfil-grid--full {
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Cards COMPACTOS */
    .rr-perfil-card {
        margin-bottom: 10px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
        padding: 12px !important;
        border-radius: 10px !important;
    }

    .rr-perfil-card__header {
        padding: 8px 0 !important;
        margin-bottom: 12px !important;
        box-sizing: border-box !important;
    }

    .rr-perfil-card__title {
        font-size: 0.9rem !important;
        word-wrap: break-word;
    }

    .rr-perfil-card__title i {
        font-size: 0.85rem !important;
    }

    .rr-perfil-card__body {
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
        padding: 0 !important;
    }

    /* Barra de Progresso - COMPACTA */
    .rr-affiliate-progress-container {
        height: 28px !important;
        border-radius: 8px !important;
    }

    .rr-affiliate-progress-text {
        font-size: 0.75rem !important;
        white-space: nowrap;
        font-weight: 600 !important;
    }

    .rr-affiliate-progress-bar {
        min-width: 60px !important;
    }

    /* Link de Referral - COMPACTO */
    .rr-affiliate-link-display {
        flex-direction: column;
        gap: 6px !important;
        max-width: 100% !important;
    }

    .rr-affiliate-link-display input {
        font-size: 0.75rem !important;
        max-width: 100% !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        box-sizing: border-box !important;
        padding: 8px !important;
    }

    .rr-affiliate-link-display button {
        width: 100% !important;
        padding: 8px !important;
        font-size: 0.8rem !important;
    }

    /* Seus Indicados - COMPACTO */
    .rr-affiliate-referrals-list {
        max-height: 250px !important;
        width: 100% !important;
        overflow-x: hidden !important;
    }

    .rr-affiliate-referral-item {
        padding: 8px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        font-size: 0.8rem !important;
    }

    .rr-affiliate-referral-item strong {
        font-size: 0.85rem !important;
    }

    .rr-affiliate-referral-item small {
        font-size: 0.7rem !important;
    }

    /* Tabela de Comissões - COMPACTA */
    .rr-perfil-table-wrapper {
        overflow-x: auto !important;
        width: 100% !important;
        max-width: 100% !important;
        -webkit-overflow-scrolling: touch;
        box-sizing: border-box !important;
        margin: 0 -12px !important;
        padding: 0 12px !important;
    }

    .rr-perfil-table {
        font-size: 0.7rem !important;
        min-width: 100%;
        width: 100%;
    }

    .rr-perfil-table td,
    .rr-perfil-table th {
        padding: 6px 4px !important;
        white-space: nowrap;
        font-size: 0.7rem !important;
    }

    .rr-perfil-table th {
        font-size: 0.65rem !important;
    }

    /* Tier Cards - COMPACTO */
    .rr-affiliate-tiers,
    .rr-affiliate-tiers[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        gap: 8px !important;
        width: 100% !important;
        display: grid !important;
    }

    .rr-affiliate-tier-card {
        padding: 12px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        border-radius: 8px !important;
    }

    .rr-affiliate-tier-emoji {
        font-size: 1.8rem !important;
        margin-bottom: 6px !important;
    }

    .rr-affiliate-tier-name {
        font-size: 0.9rem !important;
        margin-bottom: 4px !important;
    }

    .rr-affiliate-tier-requirement {
        font-size: 0.7rem !important;
        margin-bottom: 6px !important;
    }

    .rr-affiliate-tier-benefits p {
        font-size: 0.75rem !important;
        margin: 2px 0 !important;
    }

    /* How it Works - COMPACTO */
    .rr-affiliate-how-it-works,
    .rr-affiliate-how-it-works > div,
    .rr-affiliate-how-it-works > div[style*="grid-template-columns"],
    .rr-affiliate-how-it-works [style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
        width: 100% !important;
        display: grid !important;
        padding: 16px 12px !important;
        box-sizing: border-box !important;
    }

    .rr-affiliate-how-it-works h3 {
        font-size: 1rem !important;
        margin-bottom: 12px !important;
    }

    .rr-affiliate-how-it-works i {
        font-size: 2rem !important;
        margin-bottom: 8px !important;
    }

    .rr-affiliate-how-it-works h5 {
        font-size: 0.85rem !important;
        margin-bottom: 4px !important;
    }

    .rr-affiliate-how-it-works p {
        font-size: 0.75rem !important;
    }

    .rr-affiliate-how-step {
        width: 100% !important;
        box-sizing: border-box !important;
        padding: 12px !important;
    }

    /* Ativação - Mobile */
    .rr-perfil-affiliate-activation .rr-perfil-card {
        padding: 20px;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .rr-affiliate-hero {
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .rr-affiliate-hero h2 {
        font-size: 1.5rem !important;
    }

    /* ASSINATURA GRIDS - INLINE OVERRIDE */
    .rr-assinatura-details,
    .rr-assinatura-details[style*="grid-template-columns"],
    .rr-assinatura-benefits__grid,
    .rr-assinatura-benefits__grid[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
        width: 100% !important;
    }
}

/* EXTRA SMALL (< 480px) - ULTRA COMPACTO */
@media (max-width: 480px) {
    .rr-perfil-container {
        padding: 10px 6px !important;
        width: 100% !important;
    }

    .rr-affiliate-stat-card {
        padding: 8px 6px !important;
    }

    .rr-affiliate-stat-icon {
        font-size: 1rem !important;
        margin-bottom: 3px !important;
    }

    .rr-affiliate-stat-label {
        font-size: 0.65rem !important;
    }

    .rr-affiliate-stat-value {
        font-size: 0.85rem !important;
    }

    .rr-affiliate-progress-container {
        height: 26px !important;
    }

    .rr-affiliate-progress-bar {
        min-width: 70px !important;
        overflow: visible !important;
    }

    .rr-affiliate-progress-text {
        font-size: 0.65rem !important;
        white-space: nowrap !important;
        padding: 0 6px !important;
        letter-spacing: -0.5px;
    }

    .rr-perfil-card__header {
        padding: 6px 0 !important;
        margin-bottom: 8px !important;
    }

    .rr-perfil-card {
        padding: 10px !important;
        margin-bottom: 8px !important;
    }

    .rr-perfil-card__title {
        font-size: 0.85rem !important;
    }

    .rr-perfil-table td,
    .rr-perfil-table th {
        padding: 4px 3px !important;
        font-size: 0.65rem !important;
    }

    .rr-perfil-btn {
        padding: 8px 12px !important;
        font-size: 0.8rem !important;
    }

    .rr-affiliate-tier-card {
        padding: 10px !important;
    }

    .rr-affiliate-how-it-works {
        padding: 12px 8px !important;
    }
}

/* ============================================
   SUBMENU PERFIL - MOBILE OTIMIZADO
   ============================================ */
@media (max-width: 768px) {
    /* Container do submenu do perfil */
    #rrPerfilSubmenu {
        margin-bottom: 0.75rem;
    }

    #rrPerfilSubmenu .rr-epic-submenu__track {
        padding: 4px;
        gap: 2px;
        border-radius: 14px;
        background: linear-gradient(180deg, #0a0a0f 0%, #12121a 100%);
        box-shadow:
            0 2px 10px rgba(0,0,0,0.5),
            inset 0 1px 0 rgba(255,255,255,0.03);
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn {
        padding: 10px 2px 8px;
        flex: 1;
        min-width: 0;
        gap: 3px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__icon-wrap {
        width: 30px;
        height: 30px;
        border-radius: 8px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__icon {
        font-size: 13px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__label {
        font-size: 9px;
        max-width: 52px;
        letter-spacing: 0.2px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__meta {
        display: none;
    }

    #rrPerfilSubmenu .rr-epic-submenu__effect {
        width: 35px;
        height: 2px;
    }

    /* Crown menor */
    #rrPerfilSubmenu .rr-epic-submenu__crown {
        font-size: 9px;
        top: 1px;
        right: 1px;
    }
}

/* Telas muito pequenas (< 380px) */
@media (max-width: 380px) {
    #rrPerfilSubmenu .rr-epic-submenu__track {
        padding: 3px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn {
        padding: 8px 1px 6px;
        gap: 2px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__icon-wrap {
        width: 26px;
        height: 26px;
        border-radius: 7px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__icon {
        font-size: 11px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__label {
        font-size: 8px;
        max-width: 44px;
        letter-spacing: 0;
    }

    #rrPerfilSubmenu .rr-epic-submenu__effect {
        width: 28px;
    }
}

/* Landscape mobile / telas médias */
@media (max-width: 576px) and (orientation: landscape) {
    #rrPerfilSubmenu .rr-epic-submenu__track {
        padding: 6px 8px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn {
        flex-direction: row;
        padding: 8px 6px;
        gap: 6px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__icon-wrap {
        width: 28px;
        height: 28px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__text {
        width: 100%;
        min-width: 0;
        align-items: flex-start;
    }

    #rrPerfilSubmenu .rr-epic-submenu__label {
        max-width: none !important;
        white-space: normal !important;
        text-overflow: clip !important;
    }
}

/* ============================================
   CARD LINK DE INDICAÇÃO - ESTILOS COMPLETOS
   ============================================ */

/* === CARD CONTAINER === */
.rr-affiliate-link-card {
    border: 1px solid rgba(16, 185, 129, 0.25) !important;
    background: linear-gradient(165deg,
        rgba(15, 23, 42, 0.95) 0%,
        rgba(16, 185, 129, 0.08) 50%,
        rgba(15, 23, 42, 0.95) 100%) !important;
    position: relative;
    overflow: hidden;
}

/* === CARD DESTACADO NO TOPO === */
.rr-affiliate-link-card--featured {
    border: 2px solid rgba(16, 185, 129, 0.5) !important;
    box-shadow: 0 0 30px rgba(16, 185, 129, 0.25), 0 8px 32px rgba(0, 0, 0, 0.4) !important;
    animation: featuredCardPulse 3s ease-in-out infinite;
}

@keyframes featuredCardPulse {
    0%, 100% { 
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.25), 0 8px 32px rgba(0, 0, 0, 0.4);
        border-color: rgba(16, 185, 129, 0.5);
    }
    50% { 
        box-shadow: 0 0 45px rgba(16, 185, 129, 0.4), 0 12px 40px rgba(0, 0, 0, 0.5);
        border-color: rgba(16, 185, 129, 0.7);
    }
}

.rr-affiliate-link-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981, #34d399, #10b981);
    background-size: 200% 100%;
    animation: linkCardShimmer 3s ease-in-out infinite;
}

@keyframes linkCardShimmer {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* === BADGE DESTAQUE === */
.rr-affiliate-featured-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.3), rgba(52, 211, 153, 0.2));
    border: 1px solid rgba(16, 185, 129, 0.5);
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #34d399;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    animation: badgeGlow 2s ease-in-out infinite;
}

.rr-affiliate-featured-badge i {
    color: #fbbf24;
    filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.6));
    animation: starRotate 3s linear infinite;
}

@keyframes badgeGlow {
    0%, 100% { box-shadow: 0 0 10px rgba(16, 185, 129, 0.3); }
    50% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.5); }
}

@keyframes starRotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* === CARD FULL WIDTH (Desktop only) === */
.rr-affiliate-link-card--fullwidth {
    grid-column: 1 / -1; /* Ocupa todas as colunas do grid */
}

@media (max-width: 768px) {
    .rr-affiliate-link-card--fullwidth {
        grid-column: auto; /* No mobile, comportamento normal */
    }
}

/* === LAYOUT COMBINADO (Link + Próximo Nível) === */
.rr-affiliate-combined-layout {
    padding: 20px;
    display: grid;
    grid-template-columns: 1.5fr auto 1fr;
    gap: 24px;
    align-items: center;
}

.rr-affiliate-link-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.rr-affiliate-divider {
    width: 2px;
    height: 100%;
    min-height: 120px;
    background: linear-gradient(180deg, transparent, rgba(16, 185, 129, 0.4), transparent);
    position: relative;
}

.rr-affiliate-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 12px rgba(16, 185, 129, 0.6);
}

.rr-affiliate-progress-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rr-affiliate-next-level-header {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #10b981;
    margin-bottom: 8px;
}

.rr-affiliate-next-level-header i {
    font-size: 1.2rem;
}

.rr-affiliate-next-level-header h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #34d399;
}

/* Mobile: Layout vertical */
@media (max-width: 768px) {
    .rr-affiliate-combined-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .rr-affiliate-divider {
        width: 100%;
        height: 2px;
        min-height: auto;
        background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.4), transparent);
    }
}

/* === CARD HEADER === */
.rr-affiliate-link-card .rr-perfil-card__header {
    border-bottom: 1px solid rgba(16, 185, 129, 0.15);
    background: linear-gradient(180deg, rgba(16, 185, 129, 0.08), transparent);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.rr-affiliate-link-card .rr-perfil-card__title {
    color: #34d399;
}

.rr-affiliate-link-card .rr-perfil-card__title i {
    color: #10b981;
    filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.5));
}

/* === CARD BODY === */
.rr-affiliate-link-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* === INPUT GROUP - Container do Link === */
.rr-affiliate-link-input-group {
    position: relative;
    border-radius: 18px;
    padding: 6px;
    overflow: hidden;
    background: linear-gradient(135deg,
        rgba(16, 185, 129, 0.22) 0%,
        rgba(15, 23, 42, 0.92) 60%,
        rgba(16, 185, 129, 0.12) 100%);
    border: 1px solid rgba(16, 185, 129, 0.35);
    box-shadow:
        0 12px 30px rgba(16, 185, 129, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.rr-affiliate-link-input-group::before {
    content: '';
    position: absolute;
    inset: -40% -20%;
    background: radial-gradient(circle at 20% 20%,
        rgba(52, 211, 153, 0.45) 0%,
        rgba(52, 211, 153, 0.15) 35%,
        transparent 60%);
    opacity: 0.7;
    animation: linkGroupGlow 6s ease-in-out infinite;
    pointer-events: none;
}

@keyframes linkGroupGlow {
    0%, 100% { transform: translate3d(0, 0, 0); opacity: 0.55; }
    50% { transform: translate3d(18px, -12px, 0); opacity: 0.9; }
}

.rr-affiliate-link-input-group:hover {
    transform: translateY(-1px);
    border-color: rgba(16, 185, 129, 0.6);
    box-shadow:
        0 18px 38px rgba(16, 185, 129, 0.25),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.rr-affiliate-link-input-group:hover::before {
    opacity: 1;
}

/* === INPUT WRAPPER (inner container) === */
.rr-affiliate-link-input-group::after {
    content: '🔗';
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.2rem;
    z-index: 3;
    filter: drop-shadow(0 2px 6px rgba(16, 185, 129, 0.35));
    transition: transform 0.3s ease, filter 0.3s ease;
}

.rr-affiliate-link-input-group:hover::after {
    transform: translateY(-50%) scale(1.08) rotate(-8deg);
    filter: drop-shadow(0 4px 10px rgba(16, 185, 129, 0.5));
}

/* === INPUT FIELD === */
.rr-affiliate-link-input {
    width: 100%;
    padding: 18px 20px 18px 52px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg,
        rgba(10, 15, 25, 0.95) 0%,
        rgba(16, 185, 129, 0.08) 100%);
    color: #34d399;
    font-size: 0.95rem;
    font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', 'Cascadia Code', 'Courier New', monospace;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-overflow: ellipsis;
    box-sizing: border-box;
    box-shadow:
        inset 0 3px 8px rgba(0, 0, 0, 0.4),
        inset 0 -1px 0 rgba(255, 255, 255, 0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    z-index: 2;
}

.rr-affiliate-link-input:hover {
    color: #6ee7b7;
    background: linear-gradient(135deg,
        rgba(10, 15, 25, 0.98) 0%,
        rgba(16, 185, 129, 0.12) 100%);
    box-shadow:
        inset 0 3px 8px rgba(0, 0, 0, 0.5),
        inset 0 -1px 0 rgba(255, 255, 255, 0.05);
}

.rr-affiliate-link-input:focus {
    outline: none;
    color: #a7f3d0;
    background: linear-gradient(135deg,
        rgba(10, 15, 25, 1) 0%,
        rgba(16, 185, 129, 0.15) 100%);
    box-shadow:
        inset 0 3px 8px rgba(0, 0, 0, 0.5),
        0 0 0 3px rgba(16, 185, 129, 0.2);
}

.rr-affiliate-link-input::selection {
    background: rgba(16, 185, 129, 0.5);
    color: #fff;
}

.rr-affiliate-link-input::placeholder {
    color: rgba(148, 163, 184, 0.5);
}

/* === ACTION BUTTONS === */
.rr-affiliate-link-actions {
    display: flex;
    gap: 12px;
}

.rr-affiliate-link-btn {
    flex: 1;
    padding: 14px 20px !important;
    font-size: 0.9rem !important;
    border-radius: 12px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative;
    overflow: hidden;
}

.rr-affiliate-link-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent,
        rgba(255, 255, 255, 0.15),
        transparent);
    transition: left 0.5s ease;
}

.rr-affiliate-link-btn:hover::before {
    left: 100%;
}

/* Botão Copiar (Secondary) */
.rr-affiliate-link-btn.rr-perfil-btn--secondary {
    background: rgba(16, 185, 129, 0.1) !important;
    border: 2px solid rgba(16, 185, 129, 0.35) !important;
    color: #34d399 !important;
    box-shadow: 0 2px 10px rgba(16, 185, 129, 0.1);
}

.rr-affiliate-link-btn.rr-perfil-btn--secondary:hover {
    background: rgba(16, 185, 129, 0.2) !important;
    border-color: #10b981 !important;
    color: #6ee7b7 !important;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.25);
}

.rr-affiliate-link-btn.rr-perfil-btn--secondary:active {
    transform: translateY(-1px);
    box-shadow: 0 3px 12px rgba(16, 185, 129, 0.2);
}

/* Botão Compartilhar (Primary) */
.rr-affiliate-link-btn.rr-perfil-btn--primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: none !important;
    color: #fff !important;
    box-shadow:
        0 4px 15px rgba(16, 185, 129, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.rr-affiliate-link-btn.rr-perfil-btn--primary:hover {
    background: linear-gradient(135deg, #34d399 0%, #10b981 100%) !important;
    transform: translateY(-3px);
    box-shadow:
        0 8px 25px rgba(16, 185, 129, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.rr-affiliate-link-btn.rr-perfil-btn--primary:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
}

.rr-affiliate-link-btn i {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.rr-affiliate-link-btn:hover i {
    transform: scale(1.15);
}

.rr-affiliate-link-btn span {
    display: inline;
    margin-left: 2px;
}

/* === CÓDIGO DE REFERÊNCIA === */
.rr-affiliate-link-code {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: #94a3b8;
    font-size: 0.9rem;
    text-align: center;
    padding: 14px 18px;
    background: linear-gradient(135deg,
        rgba(16, 185, 129, 0.08) 0%,
        rgba(16, 185, 129, 0.02) 100%);
    border-radius: 12px;
    border: 1px dashed rgba(16, 185, 129, 0.3);
    position: relative;
    overflow: hidden;
}

.rr-affiliate-link-code::before {
    content: '✨';
    position: absolute;
    left: 16px;
    font-size: 0.9rem;
    opacity: 0.7;
}

.rr-affiliate-link-code::after {
    content: '✨';
    position: absolute;
    right: 16px;
    font-size: 0.9rem;
    opacity: 0.7;
}

.rr-affiliate-link-code strong {
    color: #10b981;
    font-size: 1.15rem;
    letter-spacing: 3px;
    text-shadow: 0 0 15px rgba(16, 185, 129, 0.6);
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-weight: 800;
    padding: 4px 12px;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 6px;
}

/* Mobile: Card Link de Indicação */
@media (max-width: 480px) {
    .rr-affiliate-link-card::before {
        height: 2px;
    }

    .rr-affiliate-link-body {
        padding: 16px;
        gap: 14px;
    }

    .rr-affiliate-link-input-group {
        border-radius: 14px;
        padding: 3px;
    }

    .rr-affiliate-link-input-group::after {
        left: 14px;
        font-size: 1.1rem;
    }

    .rr-affiliate-link-input {
        padding: 16px 16px 16px 46px;
        font-size: 0.8rem;
        border-radius: 11px;
        letter-spacing: 0.3px;
    }

    .rr-affiliate-link-actions {
        flex-direction: column;
        gap: 10px;
    }

    .rr-affiliate-link-btn {
        width: 100%;
        padding: 16px 20px !important;
        font-size: 0.95rem !important;
        border-radius: 12px !important;
    }

    .rr-affiliate-link-code {
        font-size: 0.85rem;
        padding: 12px 40px;
        flex-direction: column;
        gap: 6px;
    }

    .rr-affiliate-link-code::before,
    .rr-affiliate-link-code::after {
        display: none;
    }

    .rr-affiliate-link-code strong {
        font-size: 1.1rem;
        letter-spacing: 2px;
    }
}

/* Extra small screens */
@media (max-width: 360px) {
    .rr-affiliate-link-body {
        padding: 12px;
        gap: 12px;
    }

    .rr-affiliate-link-input-group::after {
        left: 12px;
        font-size: 1rem;
    }

    .rr-affiliate-link-input {
        font-size: 0.72rem;
        padding: 14px 12px 14px 40px;
        border-radius: 10px;
    }

    .rr-affiliate-link-btn {
        padding: 14px 16px !important;
        font-size: 0.88rem !important;
        border-radius: 10px !important;
    }

    .rr-affiliate-link-btn i {
        font-size: 0.9rem;
    }

    .rr-affiliate-link-code {
        padding: 10px 12px;
    }

    .rr-affiliate-link-code strong {
        font-size: 1rem;
        letter-spacing: 1.5px;
        padding: 3px 10px;
    }
}

/* ========================================
   ORIGINAL PERFIL STYLES
   ======================================== */
.rr-perfil-card {
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(249, 115, 22, 0.2);
    border-radius: 16px;
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}
.rr-perfil-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.938rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.rr-perfil-btn--primary {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    width: 100%;
}
.rr-perfil-btn--primary:hover {
    background: linear-gradient(135deg, #ea580c, #dc2626);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
}

/* Campo obrigatório */
.rr-required {
    color: #ef4444;
    font-weight: 600;
}

.rr-required-field:invalid,
.rr-required-field.rr-field-error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
}

/* Banner de perfil incompleto */
.rr-perfil-incomplete-banner {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 18px;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(234, 88, 12, 0.1));
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: 12px;
    margin-bottom: 20px;
    animation: rr-banner-pulse 2s ease-in-out infinite;
}

@keyframes rr-banner-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.3); }
    50% { box-shadow: 0 0 12px 3px rgba(239, 68, 68, 0.2); }
}

.rr-perfil-incomplete-banner__icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(239, 68, 68, 0.2);
    border-radius: 50%;
    color: #ef4444;
    font-size: 1.1rem;
}

.rr-perfil-incomplete-banner__content strong {
    display: block;
    color: #ef4444;
    font-size: 1rem;
    margin-bottom: 4px;
}

.rr-perfil-incomplete-banner__content p {
    color: #cbd5e1;
    font-size: 0.875rem;
    margin: 0;
    line-height: 1.5;
}
    </style>
@else

<style>
/* ============================================
   AFFILIATE LINK INPUT GROUP (AUTH)
   ============================================ */
.rr-affiliate-link-input-group {
    position: relative;
    border-radius: 18px;
    padding: 6px;
    overflow: hidden;
    background: linear-gradient(135deg,
        rgba(16, 185, 129, 0.22) 0%,
        rgba(15, 23, 42, 0.92) 60%,
        rgba(16, 185, 129, 0.12) 100%);
    border: 1px solid rgba(16, 185, 129, 0.35);
    box-shadow:
        0 12px 30px rgba(16, 185, 129, 0.18),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.rr-affiliate-link-input-group::before {
    content: '';
    position: absolute;
    inset: -40% -20%;
    background: radial-gradient(circle at 20% 20%,
        rgba(52, 211, 153, 0.45) 0%,
        rgba(52, 211, 153, 0.15) 35%,
        transparent 60%);
    opacity: 0.7;
    animation: linkGroupGlow 6s ease-in-out infinite;
    pointer-events: none;
}

@keyframes linkGroupGlow {
    0%, 100% { transform: translate3d(0, 0, 0); opacity: 0.55; }
    50% { transform: translate3d(18px, -12px, 0); opacity: 0.9; }
}

.rr-affiliate-link-input-group:hover {
    transform: translateY(-1px);
    border-color: rgba(16, 185, 129, 0.6);
    box-shadow:
        0 18px 38px rgba(16, 185, 129, 0.25),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.rr-affiliate-link-input-group:hover::before {
    opacity: 1;
}

.rr-affiliate-link-input-group::after {
    content: '🔗';
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.2rem;
    z-index: 3;
    filter: drop-shadow(0 2px 6px rgba(16, 185, 129, 0.35));
    transition: transform 0.3s ease, filter 0.3s ease;
}

.rr-affiliate-link-input-group:hover::after {
    transform: translateY(-50%) scale(1.08) rotate(-8deg);
    filter: drop-shadow(0 4px 10px rgba(16, 185, 129, 0.5));
}

.rr-affiliate-link-input {
    width: 100%;
    padding: 18px 20px 18px 52px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg,
        rgba(10, 15, 25, 0.95) 0%,
        rgba(16, 185, 129, 0.08) 100%);
    color: #34d399;
    font-size: 0.95rem;
    font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', 'Cascadia Code', 'Courier New', monospace;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-overflow: ellipsis;
    box-sizing: border-box;
    box-shadow:
        inset 0 3px 8px rgba(0, 0, 0, 0.4),
        inset 0 -1px 0 rgba(255, 255, 255, 0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    z-index: 2;
}

.rr-affiliate-link-input:hover {
    color: #6ee7b7;
    background: linear-gradient(135deg,
        rgba(10, 15, 25, 0.98) 0%,
        rgba(16, 185, 129, 0.12) 100%);
    box-shadow:
        inset 0 3px 8px rgba(0, 0, 0, 0.5),
        inset 0 -1px 0 rgba(255, 255, 255, 0.05);
}

.rr-affiliate-link-input:focus {
    outline: none;
    color: #a7f3d0;
    background: linear-gradient(135deg,
        rgba(10, 15, 25, 1) 0%,
        rgba(16, 185, 129, 0.15) 100%);
    box-shadow:
        inset 0 3px 8px rgba(0, 0, 0, 0.5),
        0 0 0 3px rgba(16, 185, 129, 0.2);
}

.rr-affiliate-link-input::selection {
    background: rgba(16, 185, 129, 0.5);
    color: #fff;
}

@media (max-width: 480px) {
    .rr-affiliate-link-input-group {
        border-radius: 14px;
        padding: 3px;
    }

    .rr-affiliate-link-input-group::after {
        left: 14px;
        font-size: 1.1rem;
    }

    .rr-affiliate-link-input {
        padding: 16px 16px 16px 46px;
        font-size: 0.8rem;
        border-radius: 11px;
        letter-spacing: 0.3px;
    }
}

@media (max-width: 360px) {
    .rr-affiliate-link-input-group::after {
        left: 12px;
        font-size: 1rem;
    }

    .rr-affiliate-link-input {
        font-size: 0.72rem;
        padding: 14px 12px 14px 40px;
        border-radius: 10px;
    }
}

/* =============================================
   TOGGLE: Mostrar Usuário em Listas
============================================= */
.rr-perfil-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.rr-perfil-toggle-row__info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.rr-perfil-toggle-row__label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
}

.rr-perfil-toggle-row__hint {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.45);
}

/* Toggle switch - X / ✓ animated */
.rr-toggle-switch {
    --toggle-hue: 223;
    --toggle-trans-dur: 0.4s;
    --toggle-trans-timing: cubic-bezier(0.65,0,0.35,1);
    position: relative;
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
}

.rr-toggle-switch__input {
    position: relative;
    background-color: hsl(0, 60%, 45%);
    border: 0;
    border-radius: 0.75em;
    cursor: pointer;
    display: block;
    width: 3em;
    height: 1.5em;
    font-size: 16px;
    transition: background-color var(--toggle-trans-dur) var(--toggle-trans-timing);
    -webkit-appearance: none;
    appearance: none;
    -webkit-tap-highlight-color: transparent;
}

.rr-toggle-switch__input:before {
    background-color: hsl(var(--toggle-hue),10%,10%);
    border-radius: 50%;
    content: "";
    display: block;
    position: absolute;
    top: 0.125em;
    left: 0.125em;
    width: 1.25em;
    height: 1.25em;
    transition:
        background-color var(--toggle-trans-dur) var(--toggle-trans-timing),
        transform var(--toggle-trans-dur) var(--toggle-trans-timing);
}

.rr-toggle-switch__icon {
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    background-color: hsl(var(--toggle-hue),10%,90%);
    border-radius: 50%;
    overflow: hidden;
    pointer-events: none;
    top: 0.125em;
    left: 0.125em;
    width: 1.25em;
    height: 1.25em;
    font-size: 16px;
    transition: transform var(--toggle-trans-dur) var(--toggle-trans-timing);
}

/* X / ✓ icon bars */
.rr-toggle-switch__icon-part {
    display: block;
    position: absolute;
    background-color: hsl(var(--toggle-hue),10%,10%);
    border-radius: 0.0625em;
    transition: transform var(--toggle-trans-dur) var(--toggle-trans-timing);
}

/* Bar 1: top-left to bottom-right diagonal of X → becomes short leg of ✓ */
.rr-toggle-switch__icon-part--1 {
    width: 0.16em;
    height: 0.65em;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(45deg);
}

/* Bar 2: top-right to bottom-left diagonal of X → becomes long leg of ✓ */
.rr-toggle-switch__icon-part--2 {
    width: 0.16em;
    height: 0.65em;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
}

.rr-toggle-switch__sr {
    overflow: hidden;
    position: absolute;
    width: 1px;
    height: 1px;
}

/* ✓ CHECKED state */
.rr-toggle-switch__input:checked {
    background-color: hsl(142, 60%, 40%);
}

.rr-toggle-switch__input:checked:before,
.rr-toggle-switch__input:checked ~ .rr-toggle-switch__icon {
    transform: translateX(1.5em);
}

/* Checked: bar 1 → short leg of checkmark (left side) */
.rr-toggle-switch__input:checked ~ .rr-toggle-switch__icon .rr-toggle-switch__icon-part--1 {
    height: 0.35em;
    transform: translate(-0.19em, -0.05em) rotate(45deg);
    transform-origin: bottom right;
}

/* Checked: bar 2 → long leg of checkmark (right side) */
.rr-toggle-switch__input:checked ~ .rr-toggle-switch__icon .rr-toggle-switch__icon-part--2 {
    height: 0.6em;
    transform: translate(0em, -0.17em) rotate(-45deg);
    transform-origin: bottom left;
}

/* Loading state */
.rr-perfil-toggle-row.is-loading {
    opacity: 0.6;
    pointer-events: none;
}

.rr-perfil-toggle-row--compact {
    margin: 12px 0 0;
    padding: 10px 12px;
    border-radius: 16px;
    background: rgba(255,255,255,0.04);
}

.rr-perfil-toggle-row--compact .rr-perfil-toggle-row__label {
    font-size: 0.8rem;
}

.rr-perfil-toggle-row--compact .rr-perfil-toggle-row__hint {
    font-size: 0.7rem;
}
</style>

<style>
.rr-perfil-container {
    --rr-perfil-v2-bg: #0b1220;
    --rr-perfil-v2-surface: linear-gradient(145deg, rgba(15, 23, 42, 0.94), rgba(2, 6, 23, 0.92));
    --rr-perfil-v2-border: rgba(148, 163, 184, 0.18);
    --rr-perfil-v2-text: #e2e8f0;
    --rr-perfil-v2-muted: #94a3b8;
    --rr-perfil-v2-accent: #f97316;
    --rr-perfil-v2-accent-soft: rgba(249, 115, 22, 0.18);
    --rr-perfil-v2-success: #10b981;
    width: min(1120px, 100%);
    margin: 0 auto;
    padding: 8px 10px 24px;
    color: var(--rr-perfil-v2-text);
}

#rrPerfilSubmenu {
    margin-bottom: 14px;
}

#rrPerfilSubmenu .rr-epic-submenu__track {
    border-radius: 16px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: linear-gradient(150deg, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.55));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 8px 24px rgba(2, 6, 23, 0.34);
    backdrop-filter: blur(10px);
}

#rrPerfilSubmenu .rr-epic-submenu__btn {
    border-radius: 12px;
    border: 1px solid transparent;
    transition: transform 0.18s ease, border-color 0.18s ease, background-color 0.18s ease;
}

#rrPerfilSubmenu .rr-epic-submenu__btn:hover {
    transform: translateY(-1px);
    border-color: rgba(148, 163, 184, 0.32);
    background: rgba(148, 163, 184, 0.08);
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn.active,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
    border-color: rgba(255, 255, 255, 0.14);
    background: transparent;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.05);
}

#rrPerfilSubmenu .rr-epic-submenu__icon-wrap {
    border-radius: 10px;
    background: rgba(15, 23, 42, 0.5);
}

#rrPerfilSubmenu .rr-epic-submenu__label {
    color: #e2e8f0;
    font-weight: 700;
}

#rrPerfilSubmenu .rr-epic-submenu__meta {
    color: #93c5fd;
}

/* Perfil submenu - same language as mobile tabbar */
#rrPerfilSubmenu .rr-epic-submenu__track {
    border-radius: 20px !important;
    border: 2px solid #f97316 !important;
    background:
        radial-gradient(120% 160% at 50% 0%, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0) 56%),
        linear-gradient(160deg, rgba(34, 15, 8, 0.95), rgba(20, 8, 4, 0.95)) !important;
    box-shadow:
        -3px -3px 8px rgba(50, 50, 50, 0.18),
        4px 4px 12px rgba(0, 0, 0, 0.62),
        0 0 16px rgba(249, 115, 22, 0.4),
        inset 0 0 12px rgba(249, 115, 22, 0.16) !important;
    padding: 0 2px !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn {
    border: 0 !important;
    background: transparent !important;
    min-height: 58px;
    padding: 10px 8px 8px !important;
    color: rgba(255, 255, 255, 0.84) !important;
    transition: transform 0.22s ease, color 0.22s ease !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
    width: 30px;
    height: 30px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.06) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
    font-size: 10px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta {
    font-size: 8px;
    opacity: 0.62;
    color: rgba(255, 255, 255, 0.72);
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn.active,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
    color: #fff !important;
    transform: translateY(-1px);
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__icon-wrap {
    background: rgba(255, 255, 255, 0.06) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__track {
    background:
        radial-gradient(120% 160% at 50% 0%, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0) 56%),
        linear-gradient(160deg, rgba(255, 250, 244, 0.96), rgba(255, 238, 220, 0.98)) !important;
    border-color: #f97316 !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn {
    color: rgba(74, 42, 26, 0.88) !important;
}

/* ============================================
   Perfil submenu refresh
   Fixed at top + color-coded tabs like launch menu
   ============================================ */
@keyframes rrPerfilTabPulseBlue {
    0%, 100% {
        box-shadow:
            0 12px 24px rgba(37, 99, 235, 0.24),
            inset 0 1px 0 rgba(255,255,255,0.18);
        filter: saturate(1) brightness(1);
    }
    50% {
        box-shadow:
            0 16px 32px rgba(37, 99, 235, 0.38),
            0 0 0 2px rgba(191, 219, 254, 0.22),
            inset 0 1px 0 rgba(255,255,255,0.28);
        filter: saturate(1.08) brightness(1.05);
    }
}

@keyframes rrPerfilTabPulseOrange {
    0%, 100% {
        box-shadow:
            0 12px 24px rgba(249, 115, 22, 0.26),
            inset 0 1px 0 rgba(255,255,255,0.18);
        filter: saturate(1) brightness(1);
    }
    50% {
        box-shadow:
            0 16px 32px rgba(249, 115, 22, 0.4),
            0 0 0 2px rgba(254, 215, 170, 0.24),
            inset 0 1px 0 rgba(255,255,255,0.28);
        filter: saturate(1.08) brightness(1.06);
    }
}

@keyframes rrPerfilTabPulseGreen {
    0%, 100% {
        box-shadow:
            0 12px 24px rgba(22, 163, 74, 0.24),
            inset 0 1px 0 rgba(255,255,255,0.18);
        filter: saturate(1) brightness(1);
    }
    50% {
        box-shadow:
            0 16px 32px rgba(22, 163, 74, 0.38),
            0 0 0 2px rgba(187, 247, 208, 0.2),
            inset 0 1px 0 rgba(255,255,255,0.28);
        filter: saturate(1.08) brightness(1.05);
    }
}

@keyframes rrPerfilTabSweep {
    0% {
        transform: translateX(-145%) skewX(-18deg);
        opacity: 0;
    }
    15% {
        opacity: 0.16;
    }
    50% {
        opacity: 0.32;
    }
    100% {
        transform: translateX(150%) skewX(-18deg);
        opacity: 0;
    }
}

#rrPerfilSubmenu {
    position: sticky;
    top: calc(var(--hub-navbar-offset, var(--hub-navbar-height, 0px)) + 10px);
    z-index: 80;
    margin-bottom: 16px;
}

#rrPerfilSubmenu .rr-epic-submenu__track {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    backdrop-filter: none !important;
}

#rrPerfilSubmenu .rr-epic-submenu__effect {
    opacity: 0 !important;
    pointer-events: none !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn {
    position: relative;
    min-height: 64px;
    padding: 12px 10px !important;
    border-radius: 22px !important;
    border: 1.5px solid transparent !important;
    overflow: hidden;
    isolation: isolate;
    transform: none !important;
    color: #fff7ed !important;
    box-shadow: none;
}

#rrPerfilSubmenu .rr-epic-submenu__btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, transparent 20%, rgba(255,255,255,0.3) 50%, transparent 80%);
    transform: translateX(-145%) skewX(-18deg);
    opacity: 0;
    pointer-events: none;
}

#rrPerfilSubmenu .rr-epic-submenu__btn > * {
    position: relative;
    z-index: 1;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"] {
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 55%, #1d4ed8 100%) !important;
    border-color: rgba(219, 234, 254, 0.28) !important;
    box-shadow:
        0 12px 24px rgba(37, 99, 235, 0.24),
        inset 0 1px 0 rgba(255,255,255,0.18);
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"] {
    background: linear-gradient(135deg, #ffb347 0%, #f97316 56%, #c2410c 100%) !important;
    border-color: rgba(255, 229, 204, 0.3) !important;
    box-shadow:
        0 12px 24px rgba(249, 115, 22, 0.26),
        inset 0 1px 0 rgba(255,255,255,0.18);
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"] {
    background: linear-gradient(135deg, #4ade80 0%, #16a34a 55%, #166534 100%) !important;
    border-color: rgba(220, 252, 231, 0.26) !important;
    box-shadow:
        0 12px 24px rgba(22, 163, 74, 0.24),
        inset 0 1px 0 rgba(255,255,255,0.18);
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: rgba(255,255,255,0.14) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.14) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label,
#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta,
#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon {
    color: #fff7ed !important;
    text-shadow: 0 2px 10px rgba(15, 23, 42, 0.2);
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.05em;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta {
    font-size: 0.56rem;
    opacity: 0.88;
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn.active,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
    border-color: rgba(255,255,255,0.54) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active::before,
#rrPerfilSubmenu .rr-epic-submenu__btn.active::before,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"]::before {
    animation: rrPerfilTabSweep 2.3s linear infinite;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"].is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"].active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"][aria-selected="true"] {
    animation: rrPerfilTabPulseBlue 2s ease-in-out infinite;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"].is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"].active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"][aria-selected="true"] {
    animation: rrPerfilTabPulseOrange 2s ease-in-out infinite;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"].is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"].active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"][aria-selected="true"] {
    animation: rrPerfilTabPulseGreen 2s ease-in-out infinite;
}

@media (hover: hover) {
    #rrPerfilSubmenu .rr-epic-submenu__btn:hover {
        transform: translateY(-2px) !important;
        filter: brightness(1.04);
    }
}

@media (max-width: 768px) {
    #rrPerfilSubmenu {
        top: calc(var(--hub-navbar-offset, var(--hub-navbar-height, 0px)) + 8px);
    }

    #rrPerfilSubmenu .rr-epic-submenu__track {
        gap: 8px;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn {
        min-height: 58px;
        padding: 10px 6px !important;
        border-radius: 18px !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta {
        display: none;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
        font-size: 0.62rem;
    }
}

.rr-perfil-section-content {
    margin-top: 6px;
}

.rr-perfil-grid {
    gap: 16px;
}

.rr-perfil-card {
    border-radius: 20px;
    border: 1px solid var(--rr-perfil-v2-border);
    background: var(--rr-perfil-v2-surface);
    box-shadow: 0 12px 34px rgba(2, 6, 23, 0.34), inset 0 1px 0 rgba(255, 255, 255, 0.06);
    padding: 20px;
}

.rr-perfil-card__header {
    border-bottom: 1px solid rgba(148, 163, 184, 0.16);
    margin-bottom: 14px;
    padding-bottom: 12px;
}

.rr-perfil-card__title {
    color: #f8fafc;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.rr-perfil-card__title i {
    color: #fb923c;
}

.rr-perfil-card__badge {
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.28);
    background: rgba(51, 65, 85, 0.45);
    color: #cbd5e1;
    font-weight: 700;
    padding: 4px 10px;
}

.rr-perfil-alert {
    border-radius: 12px;
    border: 1px solid rgba(96, 165, 250, 0.28);
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.12), rgba(2, 6, 23, 0.16));
    color: #dbeafe;
}

.rr-perfil-affiliate-cta,
.rr-perfil-affiliate-summary {
    border: 1px solid rgba(16, 185, 129, 0.28);
    background: linear-gradient(140deg, rgba(6, 78, 59, 0.28), rgba(15, 23, 42, 0.45));
    border-radius: 14px;
    transition: transform 0.18s ease, border-color 0.18s ease;
}

.rr-perfil-affiliate-cta:hover,
.rr-perfil-affiliate-summary:hover {
    transform: translateY(-1px);
    border-color: rgba(16, 185, 129, 0.48);
}

.rr-perfil-affiliate-cta__content h4,
.rr-perfil-affiliate-summary__label {
    color: #ecfeff;
}

.rr-perfil-affiliate-cta__content p,
.rr-perfil-affiliate-summary__progress-label {
    color: #99f6e4;
}

.rr-perfil-photo {
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 14px;
    padding: 16px;
    background: rgba(15, 23, 42, 0.44);
}

.rr-perfil-photo__preview {
    border: 2px solid rgba(249, 115, 22, 0.4);
    box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
}

.rr-perfil-photo__btn {
    border-radius: 10px;
    border: 1px solid rgba(249, 115, 22, 0.45);
    background: rgba(249, 115, 22, 0.16);
    color: #ffedd5;
}

.rr-perfil-field-group {
    gap: 12px;
}

.rr-perfil-label {
    color: #cbd5e1;
    font-weight: 700;
}

.rr-perfil-input,
.rr-perfil-input[type="date"],
.rr-perfil-input[type="email"],
.rr-perfil-input[type="text"],
.rr-perfil-input[type="number"],
.rr-perfil-input[type="tel"],
.rr-perfil-input[type="password"],
select.rr-perfil-input,
textarea.rr-perfil-input {
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.24);
    background: rgba(15, 23, 42, 0.78);
    color: #e2e8f0;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
}

.rr-perfil-input:focus,
select.rr-perfil-input:focus,
textarea.rr-perfil-input:focus {
    border-color: rgba(249, 115, 22, 0.72);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.16);
    background: rgba(15, 23, 42, 0.95);
}

.rr-perfil-input::placeholder {
    color: #64748b;
}

.rr-perfil-field__help,
.rr-input-hint,
.rr-input-feedback {
    color: var(--rr-perfil-v2-muted);
}

.rr-perfil-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #f8fafc;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.rr-perfil-section-title i {
    color: #fb923c;
}

.rr-perfil-toggle-row {
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.24);
    background: rgba(15, 23, 42, 0.62);
}

.rr-perfil-toggle-row__label {
    color: #e2e8f0;
    font-weight: 700;
}

.rr-perfil-toggle-row__hint {
    color: #94a3b8;
}

.rr-perfil-btn {
    border-radius: 12px;
    font-weight: 800;
    min-height: 46px;
    transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
}

.rr-perfil-btn--primary {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: #fff;
    border: 1px solid rgba(251, 146, 60, 0.7);
    box-shadow: 0 12px 24px rgba(249, 115, 22, 0.24);
}

.rr-perfil-btn--primary:hover {
    transform: translateY(-1px);
    filter: brightness(1.05);
    box-shadow: 0 14px 28px rgba(249, 115, 22, 0.3);
}

.rr-perfil-table-wrapper,
.rr-perfil-table {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(15, 23, 42, 0.62);
}

.rr-perfil-table th {
    background: rgba(30, 41, 59, 0.86);
    color: #cbd5e1;
}

.rr-perfil-table td {
    color: #e2e8f0;
}

@media (max-width: 768px) {
    .rr-perfil-container {
        padding: 6px 8px 20px;
    }

    #rrPerfilSubmenu {
        margin-bottom: 10px;
    }

    .rr-perfil-card {
        border-radius: 14px;
        padding: 14px;
    }

    .rr-perfil-field-group {
        grid-template-columns: 1fr !important;
        gap: 10px;
    }

    .rr-perfil-section-title {
        font-size: 0.95rem;
    }

    .rr-perfil-btn {
        min-height: 44px;
    }
}
</style>

@php
    $profileChecks = [
        filled($user->firstname ?? null),
        filled($user->lastname ?? null),
        filled($user->cpf ?? null),
        filled($user->birthdate ?? null),
        filled($user->mobile ?? null),
        filled($user->pix_key ?? null),
    ];
    $profileChecksTotal = count($profileChecks);
    $profileChecksDone = collect($profileChecks)->filter()->count();
    $profileCompletionPercent = (int) round(($profileChecksDone / max($profileChecksTotal, 1)) * 100);
    $profileMissingCount = max(0, $profileChecksTotal - $profileChecksDone);
    $profileFullName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
    $profileDisplayName = $profileFullName !== '' ? $profileFullName : ($user->username ?? 'Usuário');
    $profileHandle = '@' . ($user->username ?? 'usuario');
    $profileInitial = mb_strtoupper(mb_substr($profileDisplayName, 0, 1));
    $pixConfigured = filled($user->pix_key ?? null) && filled($user->pix_key_type ?? null);
    $heroPrimaryAction = $isPremium
        ? "window.switchToSection && window.switchToSection('financeiro')"
        : "window.openPremiumTab && window.openPremiumTab()";
    $heroPrimaryLabel = $isPremium ? 'Ver Pix' : 'Virar Premium';
@endphp

<style>
.rr-perfil-hero{
    position:relative;
    overflow:hidden;
    display:grid;
    grid-template-columns:minmax(320px,.92fr) minmax(280px,1.08fr);
    gap:18px;
    margin:0 auto 16px;
    padding:20px;
    border-radius:28px;
    border:1px solid rgba(96,165,250,.18);
    background:
        radial-gradient(circle at top right, rgba(37,99,235,.22), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249,115,22,.16), transparent 30%),
        linear-gradient(160deg, rgba(17,24,39,.96), rgba(9,13,24,.98));
    box-shadow:0 24px 48px rgba(2,6,23,.28), inset 0 1px 0 rgba(255,255,255,.05);
}

.rr-perfil-hero::before{
    content:'';
    position:absolute;
    inset:auto -12% -28% auto;
    width:320px;
    height:320px;
    border-radius:50%;
    background:radial-gradient(circle, rgba(59,130,246,.18), rgba(59,130,246,0));
    pointer-events:none;
}

.rr-perfil-hero__main{display:grid;gap:16px;align-content:start;position:relative;z-index:1;}
.rr-perfil-hero__identity{display:grid;grid-template-columns:auto minmax(0,1fr);gap:16px;align-items:center;}
.rr-perfil-hero__avatar{
    width:88px;height:88px;border-radius:24px;overflow:hidden;display:grid;place-items:center;
    border:1px solid rgba(255,255,255,.08);
    background:linear-gradient(135deg, rgba(249,115,22,.95), rgba(37,99,235,.88));
    box-shadow:0 16px 30px rgba(2,6,23,.26);
}
.rr-perfil-hero__avatar img{width:100%;height:100%;object-fit:cover;}
.rr-perfil-hero__avatar-fallback{font-size:2rem;font-weight:900;color:#fff7ed;}
.rr-perfil-hero__eyebrow{
    display:inline-flex;align-items:center;gap:8px;min-height:30px;padding:0 12px;border-radius:999px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:#dbeafe;
    font-size:.72rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase;
}
.rr-perfil-hero__title{margin:8px 0 4px;font-size:clamp(1.38rem,2vw,2.08rem);line-height:1.04;font-weight:900;color:#fff8f2;letter-spacing:-.04em;}
.rr-perfil-hero__meta{display:flex;flex-wrap:wrap;gap:8px;color:#bfdbfe;font-size:.86rem;font-weight:700;}
.rr-perfil-hero__chips{display:flex;flex-wrap:wrap;gap:10px;}
.rr-perfil-hero__chip{
    display:inline-flex;align-items:center;gap:7px;min-height:36px;padding:0 14px;border-radius:999px;
    border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:#e2e8f0;font-size:.8rem;font-weight:800;
}
.rr-perfil-hero__chip--premium{background:linear-gradient(135deg, rgba(249,115,22,.18), rgba(37,99,235,.2));color:#fff7ed;}
.rr-perfil-hero__chip--good{color:#bbf7d0;border-color:rgba(34,197,94,.22);background:rgba(34,197,94,.12);}
.rr-perfil-hero__chip--warn{color:#fde68a;border-color:rgba(234,179,8,.24);background:rgba(234,179,8,.1);}
.rr-perfil-hero__hint{margin:0;color:#cbd5e1;font-size:.95rem;line-height:1.6;max-width:58ch;}
.rr-perfil-hero__actions{display:flex;flex-wrap:wrap;gap:10px;}
.rr-perfil-hero__btn{
    min-height:46px;padding:0 18px;border-radius:16px;border:0;display:inline-flex;align-items:center;justify-content:center;gap:8px;
    font-weight:900;font-size:.88rem;letter-spacing:.01em;cursor:pointer;transition:transform .18s ease, box-shadow .18s ease;
}
.rr-perfil-hero__btn:hover{transform:translateY(-1px);}
.rr-perfil-hero__btn--primary{background:linear-gradient(135deg, #f97316, #2563eb);color:#fff;box-shadow:0 16px 30px rgba(37,99,235,.22);}
.rr-perfil-hero__btn--ghost{background:rgba(255,255,255,.06);color:#e2e8f0;border:1px solid rgba(255,255,255,.08);}

.rr-perfil-hero__stats{
    position:relative;z-index:1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;align-content:start;
}
.rr-perfil-hero__stat{
    padding:15px 16px;border-radius:22px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
}
.rr-perfil-hero__stat span{display:block;color:#94a3b8;font-size:.72rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;}
.rr-perfil-hero__stat strong{display:block;margin-top:6px;color:#fff8f2;font-size:1.24rem;font-weight:900;line-height:1.08;}
.rr-perfil-hero__stat p{margin:8px 0 0;color:#cbd5e1;font-size:.84rem;line-height:1.5;}
.rr-perfil-hero__progress{margin-top:12px;height:10px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden;}
.rr-perfil-hero__progress > span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#f97316,#60a5fa);}

#rrPerfilSubmenu{width:min(1220px,100%);max-width:1220px;margin:0 auto 16px;}
#rrPerfilSubmenu .rr-epic-submenu__track{
    padding:4px !important;
    border-radius:24px !important;
    border:1px solid rgba(96,165,250,.16) !important;
    background:
        radial-gradient(120% 160% at 50% 0%, rgba(255,255,255,.08) 0%, rgba(255,255,255,0) 56%),
        linear-gradient(160deg, rgba(31,16,10,.96), rgba(13,10,18,.98)) !important;
    box-shadow:0 20px 30px rgba(2,6,23,.18), inset 0 1px 0 rgba(255,255,255,.05) !important;
}
#rrPerfilSubmenu .rr-epic-submenu__effect{
    height:4px !important;border-radius:0 0 10px 10px !important;
    background:linear-gradient(90deg,#fb923c,#60a5fa) !important;
    filter:drop-shadow(0 4px 10px rgba(96,165,250,.42)) !important;
}
#rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn.active,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"]{
    background:linear-gradient(135deg, rgba(249,115,22,.22), rgba(37,99,235,.24)) !important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.08), 0 14px 24px rgba(2,6,23,.18) !important;
}
#rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__icon-wrap{
    background:linear-gradient(90deg,#fb923c,#60a5fa) !important;
}

.rr-perfil-card__header{
    display:flex;justify-content:space-between;align-items:flex-start;gap:12px;
}
.rr-perfil-card__badge{
    background:linear-gradient(135deg, rgba(249,115,22,.14), rgba(37,99,235,.16)) !important;
    border-color:rgba(96,165,250,.22) !important;
    color:#eff6ff !important;
}
.rr-perfil-alert{
    border-radius:16px !important;
    padding:14px 16px !important;
    margin-bottom:16px !important;
    background:linear-gradient(135deg, rgba(249,115,22,.1), rgba(37,99,235,.08)) !important;
    border-color:rgba(96,165,250,.16) !important;
}
.rr-perfil-topstack{display:grid;gap:14px;margin-bottom:18px;}
.rr-perfil-inline-status{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
}
.rr-perfil-inline-status__item{
    padding:12px 14px;
    border-radius:18px;
    border:1px solid rgba(96,165,250,.14);
    background:rgba(255,255,255,.04);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
}
.rr-perfil-inline-status__item span{
    display:block;
    color:#94a3b8;
    font-size:.7rem;
    font-weight:900;
    letter-spacing:.1em;
    text-transform:uppercase;
}
.rr-perfil-inline-status__item strong{
    display:block;
    margin-top:6px;
    color:#fff8f2;
    font-size:1rem;
    font-weight:900;
}
.rr-perfil-inline-status__item small{
    display:block;
    margin-top:4px;
    color:#cbd5e1;
    font-size:.78rem;
    line-height:1.4;
}
.rr-perfil-affiliate-cta,
.rr-perfil-affiliate-summary{
    border-radius:18px !important;
    background:
        radial-gradient(circle at top right, rgba(16,185,129,.12), transparent 30%),
        linear-gradient(140deg, rgba(6,78,59,.22), rgba(15,23,42,.46)) !important;
    box-shadow:0 14px 28px rgba(2,6,23,.18) !important;
}
.rr-perfil-card__title small{
    display:block;
    margin-top:4px;
    color:#94a3b8;
    font-size:.8rem;
    font-weight:700;
}

body.light .rr-perfil-hero{
    background:
        radial-gradient(circle at top right, rgba(37,99,235,.14), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249,115,22,.1), transparent 30%),
        linear-gradient(160deg, rgba(255,255,255,.98), rgba(247,244,239,.98));
    border-color:rgba(59,130,246,.12);
    box-shadow:0 18px 34px rgba(148,163,184,.14);
}
body.light .rr-perfil-hero__title,
body.light .rr-perfil-hero__stat strong{color:#172033;}
body.light .rr-perfil-hero__hint,
body.light .rr-perfil-hero__stat p,
body.light .rr-perfil-hero__meta{color:#475569;}
body.light .rr-perfil-hero__eyebrow,
body.light .rr-perfil-hero__chip,
body.light .rr-perfil-hero__stat,
body.light .rr-perfil-hero__btn--ghost{
    background:rgba(255,255,255,.76);
    border-color:rgba(59,130,246,.12);
    color:#1f2a44;
}
body.light #rrPerfilSubmenu .rr-epic-submenu__track{
    background:
        radial-gradient(120% 160% at 50% 0%, rgba(255,255,255,.6) 0%, rgba(255,255,255,0) 56%),
        linear-gradient(160deg, rgba(255,250,244,.96), rgba(255,238,220,.98)) !important;
    border-color:rgba(59,130,246,.14) !important;
}
body.light #rrPerfilSubmenu .rr-epic-submenu__btn{color:#1f2a44 !important;}
body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta{color:#64748b !important;}
body.light .rr-perfil-card,
body.light .rr-perfil-affiliate-cta,
body.light .rr-perfil-affiliate-summary{
    box-shadow:0 16px 28px rgba(148,163,184,.14), inset 0 1px 0 rgba(255,255,255,.55) !important;
}
body.light .rr-perfil-inline-status__item{
    background:rgba(255,255,255,.76);
    border-color:rgba(59,130,246,.12);
}
body.light .rr-perfil-inline-status__item strong{color:#172033;}
body.light .rr-perfil-inline-status__item small,
body.light .rr-perfil-inline-status__item span{color:#64748b;}

.rr-perfil-container--premium .rr-perfil-card{
    background:
        radial-gradient(circle at top right, rgba(37,99,235,.2), transparent 32%),
        radial-gradient(circle at bottom left, rgba(249,115,22,.12), transparent 28%),
        var(--rr-perfil-v2-surface) !important;
    border-color:rgba(96,165,250,.22) !important;
    box-shadow:0 18px 34px rgba(2,6,23,.28), inset 0 1px 0 rgba(255,255,255,.06) !important;
}
.rr-perfil-container--premium .rr-perfil-card__header{background:transparent !important;border-bottom:1px solid rgba(96,165,250,.14) !important;}
.rr-perfil-container--premium .rr-perfil-card__title i,
.rr-perfil-container--premium .rr-perfil-alert i,
.rr-premium-text{color:#60a5fa !important;}
.rr-perfil-container--premium .rr-perfil-btn--primary{
    background:linear-gradient(135deg, #f97316, #2563eb) !important;
    box-shadow:0 14px 26px rgba(37,99,235,.24) !important;
}
.rr-perfil-container--premium .rr-perfil-btn--primary:hover:not(:disabled){
    background:linear-gradient(135deg, #ea580c, #1d4ed8) !important;
}
.badge-active,.badge-trial,.badge-cancelled{
    border-radius:999px !important;
    display:inline-flex;align-items:center;gap:6px;
}
.badge-active{background:rgba(34,197,94,.14) !important;color:#22c55e !important;border:1px solid rgba(34,197,94,.24);}
.badge-trial{background:rgba(249,115,22,.14) !important;color:#f97316 !important;border:1px solid rgba(249,115,22,.24);}
.badge-cancelled{background:rgba(239,68,68,.14) !important;color:#ef4444 !important;border:1px solid rgba(239,68,68,.22);}

@media (max-width: 860px){
    .rr-perfil-hero{grid-template-columns:1fr;padding:16px;}
    .rr-perfil-hero__stats{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (min-width: 992px){
    .rr-perfil-container{
        display:flex;
        flex-direction:column;
        gap:14px;
    }
    #rrPerfilSubmenu{
        display:none !important;
    }
    #rrPerfilSubmenu{
        order:-1;
        position:sticky;
        top:calc(var(--hub-navbar-offset, var(--hub-navbar-height, 96px)) + 10px);
        z-index:60;
    }
    .rr-perfil-section-content{
        width:100%;
    }
}
@media (max-width: 560px){
    .rr-perfil-hero{padding:14px;border-radius:22px;}
    .rr-perfil-hero__identity{grid-template-columns:72px minmax(0,1fr);gap:12px;}
    .rr-perfil-hero__avatar{width:72px;height:72px;border-radius:18px;}
    .rr-perfil-hero__stats{grid-template-columns:1fr;}
    .rr-perfil-hero__actions{display:grid;}
    .rr-perfil-hero__btn{width:100%;}
    .rr-perfil-inline-status{grid-template-columns:1fr;}
}
</style>

<div class="rr-perfil-container {{ $isPremium ? 'rr-perfil-container--premium' : '' }}">
    <!-- Submenu de Abas (Novo estilo épico) -->
    @php
        $affiliateAccount = $user->affiliate;
        $affiliateIsActive = $affiliateAccount && $affiliateAccount->status === 'active';
        $affiliateIsPending = $affiliateAccount && $affiliateAccount->status === 'pending';
        $perfilSubmenuItems = [
            ['label' => 'Pix', 'meta' => 'Resumo de prêmios', 'filter' => 'financeiro', 'icon' => 'fas fa-wallet', 'accent' => '#2563eb'],
        ];
    @endphp

    <x-rr-submenu
        id="rrPerfilSubmenu"
        :items="$perfilSubmenuItems"
        :activeIndex="0"
    />

    <!-- SEÇÃO 1: PERFIL -->
    <div class="rr-perfil-section-content" id="perfilSection" style="display: none;">
        <div class="rr-perfil-grid rr-perfil-grid--single">
            <div class="rr-perfil-section">
                <div class="rr-perfil-card">
                    <div class="rr-perfil-card__header">
                        <h3 class="rr-perfil-card__title">
                            <i class="fas fa-id-card"></i>
                            Dados da conta
                        </h3>
                    </div>

                    <div class="rr-perfil-topstack">
                        <div class="rr-perfil-incomplete-banner" id="profileIncompleteBanner" style="display: none;">
                            <div class="rr-perfil-incomplete-banner__icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="rr-perfil-incomplete-banner__content">
                                <strong>Complete seu perfil para receber prêmio</strong>
                                <p>Preencha os campos obrigatórios marcados com <span class="rr-required">*</span> para liberar seu recebimento.</p>
                            </div>
                        </div>
                    </div>

                    <form id="perfilForm" method="post" enctype="multipart/form-data" action="{{ route('user.profile.update') }}">
                        @csrf

                        <!-- Foto de Perfil -->
                        <div class="rr-perfil-photo">
                            <div class="rr-perfil-photo__preview">
                                @if($avatarUrl)
                                    <img id="perfilAvatar" src="{{ $avatarUrl }}" alt="Foto de perfil" />
                                @else
                                    <div class="rr-perfil-photo__placeholder" id="perfilAvatarPlaceholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="rr-perfil-photo__controls">
                                <label for="perfilImage" class="rr-perfil-photo__btn">
                                    <i class="fas fa-camera"></i>
                                    Alterar Foto
                                </label>
                                <input type="file" id="perfilImage" name="image" accept="image/*" style="display: none;">
                                <span class="rr-perfil-photo__help">JPG, PNG ou GIF (máx. 2MB)</span>
                            </div>
                        </div>

                        <!-- Campos do Formulário -->
                        <div class="rr-perfil-fields" id="perfilDataAnchor">
                            <div class="rr-perfil-field-group">
                                <div class="rr-perfil-field">
                                    <label class="rr-perfil-label">Primeiro nome <span class="rr-required">*</span></label>
                                    <input class="rr-perfil-input rr-required-field" type="text" name="firstname" value="{{ $user->firstname ?? '' }}" required>
                                </div>
                                <div class="rr-perfil-field">
                                    <label class="rr-perfil-label">Sobrenome <span class="rr-required">*</span></label>
                                    <input class="rr-perfil-input rr-required-field" type="text" name="lastname" value="{{ $user->lastname ?? '' }}" required>
                                </div>
                            </div>

                            <div class="rr-perfil-field">
                                <label class="rr-perfil-label">Usuário</label>

                                <input class="rr-perfil-input" type="text" name="username" id="usernameInput" value="{{ $user->username ?? '' }}" data-original="{{ $user->username ?? '' }}">
                                <div id="usernameAvailability" class="rr-username-feedback" style="display: none;"></div>
                                <input class="rr-perfil-input" type="text" name="username_confirmation" id="usernameConfirmation" placeholder="Confirmar usuário" style="margin-top: 8px;">
                                <small class="rr-perfil-field__help">
                                    <i class="fas fa-info-circle"></i>
                                    Digite o usuário duas vezes para confirmar a alteração
                                </small>
                            </div>

                            <div class="rr-perfil-field">
                                <label class="rr-perfil-label">Email</label>
                                <input class="rr-perfil-input" type="email" name="email" value="{{ $user && method_exists($user, 'hasRealEmail') && $user->hasRealEmail() ? $user->email : '' }}">
                            </div>

                            <div class="rr-perfil-field">
                                <label class="rr-perfil-label">WhatsApp <span class="rr-required">*</span></label>
                                <input class="rr-perfil-input rr-required-field" type="text" name="mobile" value="{{ $user->mobile ?? '' }}" inputmode="numeric" required>
                            </div>

                            <div class="rr-perfil-field-group">
                                <div class="rr-perfil-field">
                                    <label class="rr-perfil-label">CPF <span class="rr-required">*</span></label>
                                    <input class="rr-perfil-input rr-required-field" type="text" name="cpf" id="cpfInput" value="{{ $user->cpf ?? '' }}" inputmode="numeric" {{ !empty($user->cpf) ? 'disabled' : '' }} data-original="{{ $user->cpf ?? '' }}" maxlength="14" placeholder="000.000.000-00" required>
                                    <small id="cpfFeedback" class="rr-input-feedback" style="display: none;"></small>
                                </div>
                                <div class="rr-perfil-field">
                                    <label class="rr-perfil-label">Data de nascimento <span class="rr-required">*</span></label>
                                    <input class="rr-perfil-input rr-required-field" type="date" name="birthdate" value="{{ $user->birthdate ? (\Carbon\Carbon::parse($user->birthdate)->format('Y-m-d')) : '' }}" required>
                                </div>
                            </div>
                            
                            <!-- Chave PIX -->
                            <div class="rr-perfil-section-title" style="margin-top: 2rem;">
                                <i class="fab fa-pix"></i>
                                Dados PIX para Recebimento
                            </div>
                            
                            <div class="rr-perfil-field-group">
                                <div class="rr-perfil-field">
                                    <label class="rr-perfil-label">Tipo de Chave PIX <span class="rr-required">*</span></label>
                                    <select class="rr-perfil-input rr-required-field" name="pix_key_type" id="pixKeyType" required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="cpf" {{ ($user->pix_key_type ?? '') === 'cpf' ? 'selected' : '' }}>CPF</option>
                                        <option value="email" {{ ($user->pix_key_type ?? '') === 'email' ? 'selected' : '' }}>Email</option>
                                        <option value="phone" {{ ($user->pix_key_type ?? '') === 'phone' ? 'selected' : '' }}>Telefone</option>
                                        <option value="random" {{ ($user->pix_key_type ?? '') === 'random' ? 'selected' : '' }}>Chave Aleatória</option>
                                    </select>
                                </div>
                                <div class="rr-perfil-field">
                                    <label class="rr-perfil-label">Chave PIX <span class="rr-required">*</span></label>
                                    <input class="rr-perfil-input rr-required-field" type="text" name="pix_key" id="pixKeyInput" value="{{ $user->pix_key ?? '' }}" placeholder="Digite sua chave PIX" required>
                                    <small class="rr-input-hint">Necessário para receber seus prêmios no bolão</small>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="rr-perfil-btn rr-perfil-btn--danger rr-perfil-btn--tiny" id="btnDeleteAccount">
                            <i class="fas fa-user-times"></i>
                            Excluir Conta
                        </button>

                        <button type="submit" class="rr-perfil-btn rr-perfil-btn--primary" id="perfilSubmit">
                            <i class="fas fa-save"></i>
                            Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SEÇÃO 2: FINANCEIRO -->
    <div class="rr-perfil-section-content" id="financeiroSection" style="display: none;">
    </div>

    <!-- SEÇÃO 3: ASSINATURA PREMIUM -->
    <div class="rr-perfil-section-content" id="assinaturaSection" style="display: none;">
        <div id="financeiroAssinaturaAnchor"></div>
        <section class="rr-finance-panel rr-finance-panel--subscription">
            <div class="rr-finance-panel__header">
                <div>
                    <span class="rr-finance-panel__eyebrow">Assinatura</span>
                    <h3 class="rr-finance-panel__title">Plano, trial e renovação automática</h3>
                </div>
                <p class="rr-finance-panel__summary">Tudo sobre o Premium e o que está liberado agora na sua conta.</p>
            </div>
        <div class="rr-assinatura-container">
            @php
                $subscription = $user->getCurrentSubscription();
                $isOnTrial = $subscription && $subscription->is_trial;
                $trialEndsAt = $subscription ? $subscription->trial_ends_at : null;
                $dataFim = $subscription ? $subscription->data_fim : null;
                $planName = $subscription && $subscription->plan ? $subscription->plan->name : 'N/A';
                $autoRenew = $subscription ? $subscription->auto_renew : false;
                $isCancelled = $subscription && $subscription->cancelled_at;
            @endphp

            @if($isPremium)
            <!-- 👑 USUÁRIO PREMIUM ATIVO -->
            <div class="rr-assinatura-status rr-assinatura-status--premium">
                <div class="rr-assinatura-status__glow"></div>
                <div class="rr-assinatura-status__header">
                    <div class="rr-assinatura-status__icon">
                        <img src="{{ asset('assets/images/logo_icon/premiumleague.png') }}" alt="Premium" onerror="this.src='{{ asset('assets/images/logo_icon/logo_premium.png') }}'">
                    </div>
                    <div class="rr-assinatura-status__info">
                        <h3>Você é <span class="rr-premium-text">PREMIUM</span></h3>
                        <p>{{ $isOnTrial ? 'Período de teste gratuito' : 'Assinatura ativa' }}</p>
                    </div>
                    <div class="rr-assinatura-status__badge">
                        @if($isOnTrial)
                            <span class="badge badge-trial"><i class="fas fa-gift"></i> Trial</span>
                        @elseif($isCancelled)
                            <span class="badge badge-cancelled"><i class="fas fa-pause"></i> Cancelada</span>
                        @else
                            <span class="badge badge-active"><i class="fas fa-check-circle"></i> Ativa</span>
                        @endif
                    </div>
                </div>

                <div class="rr-assinatura-details">
                    <div class="rr-assinatura-details__item">
                        <span class="rr-assinatura-details__label">Plano</span>
                        <span class="rr-assinatura-details__value">{{ $planName }}</span>
                    </div>
                    @if($isOnTrial && $trialEndsAt)
                    <div class="rr-assinatura-details__item rr-assinatura-details__item--highlight">
                        <span class="rr-assinatura-details__label">Trial termina em</span>
                        <span class="rr-assinatura-details__value">{{ \Carbon\Carbon::parse($trialEndsAt)->format('d/m/Y') }}</span>
                    </div>
                    @else
                    <div class="rr-assinatura-details__item">
                        <span class="rr-assinatura-details__label">{{ $isCancelled ? 'Acesso até' : 'Próxima cobrança' }}</span>
                        <span class="rr-assinatura-details__value">{{ $dataFim ? \Carbon\Carbon::parse($dataFim)->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    @endif
                    <div class="rr-assinatura-details__item">
                        <span class="rr-assinatura-details__label">Renovação automática</span>
                        <span class="rr-assinatura-details__value">
                            @if($autoRenew && !$isCancelled)
                                <i class="fas fa-check text-success"></i> Ativa
                            @else
                                <i class="fas fa-times text-danger"></i> Desativada
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Benefícios Ativos -->
                <div class="rr-assinatura-benefits">
                    <h4><i class="fas fa-star"></i> Seus benefícios ativos</h4>
                    <div class="rr-assinatura-benefits__grid">
                        <div class="rr-assinatura-benefit">
                            <i class="fas fa-percent"></i>
                            <span>Taxa X1 reduzida (7-10%)</span>
                        </div>
                        <div class="rr-assinatura-benefit">
                            <i class="fas fa-trophy"></i>
                            <span>Bolão Premium grátis</span>
                        </div>
                        <div class="rr-assinatura-benefit">
                            <i class="fas fa-edit"></i>
                            <span>Trocar nome de usuário</span>
                        </div>
                        <div class="rr-assinatura-benefit">
                            <i class="fas fa-palette"></i>
                            <span>Layout exclusivo azul</span>
                        </div>
                        <div class="rr-assinatura-benefit">
                            <i class="fas fa-door-open"></i>
                            <span>Salas X1 exclusivas</span>
                        </div>
                        <div class="rr-assinatura-benefit">
                            <i class="fas fa-chart-line"></i>
                            <span>Estatísticas avançadas</span>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="rr-assinatura-actions">
                    @if($isCancelled)
                    <button type="button" class="rr-assinatura-btn rr-assinatura-btn--primary" id="btnReativarAssinatura">
                        <i class="fas fa-redo"></i> Reativar Assinatura
                    </button>
                    @elseif($isOnTrial)
                    <button type="button" class="rr-assinatura-btn rr-assinatura-btn--primary" onclick="window.location.hash='premium'">
                        <i class="fas fa-crown"></i> <!-- Converter para Pago (removido) -->
                    </button>
                    @else
                    <button type="button" class="rr-assinatura-btn rr-assinatura-btn--secondary" id="btnCancelarAssinatura">
                        <i class="fas fa-times"></i> Cancelar Assinatura
                    </button>
                    @endif
                </div>

                <!-- Info de cancelamento -->
                <div class="rr-assinatura-cancel-info">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        @if($subscription && $subscription->payment_method === 'card')
                            Assinatura por cartão: cancele quando quiser sem multa.
                        @else
                            PIX: reembolso proporcional após 3 meses. Multa de 2 meses se cancelar antes.
                        @endif
                    </small>
                </div>
            </div>

            @else
            <!-- 🔓 USUÁRIO NÃO PREMIUM -->
            @php
                $canTrial = !$user->hasHadTrial() && !$user->isPremium();
                $hasActivity = \DB::table('x1_rooms')->where('host_user_id', $user->id)->exists()
                    || \DB::table('x1_participants')->where('user_id', $user->id)->exists()
                    || \DB::table('fantasy_teams')->where('user_id', $user->id)->exists();
                $hasCpf = !empty($user->cpf);
                $trialEligible = $canTrial && $hasActivity && $hasCpf;
                $trialBlockedReason = null;
                if (!$canTrial) {
                    $trialBlockedReason = 'already_used';
                } elseif (!$hasActivity) {
                    $trialBlockedReason = 'no_activity';
                } elseif (!$hasCpf) {
                    $trialBlockedReason = 'no_cpf';
                }
            @endphp
            <div class="rr-assinatura-cta">
                <div class="rr-assinatura-cta__glow"></div>
                <div class="rr-assinatura-cta__content">
                    <div class="rr-assinatura-cta__icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h3>Seja Premium e leve vantagem!</h3>
                    <p>Pague menos taxas, acesse bolões exclusivos e destaque-se na arena.</p>

                    <div class="rr-assinatura-cta__benefits">
                        <div class="rr-assinatura-cta__benefit">
                            <i class="fas fa-check"></i> Taxa X1 reduzida de 10% para 7%
                        </div>
                        <div class="rr-assinatura-cta__benefit">
                            <i class="fas fa-check"></i> Acesso a Bolões Premium sem custo extra
                        </div>
                        <div class="rr-assinatura-cta__benefit">
                            <i class="fas fa-check"></i> Estatísticas avançadas de competidores
                        </div>
                        <div class="rr-assinatura-cta__benefit">
                            <i class="fas fa-check"></i> Layout exclusivo e destaque no ranking
                        </div>
                        <div class="rr-assinatura-cta__benefit">
                            <i class="fas fa-check"></i> Troca de nome de usuário liberada
                        </div>
                    </div>

                    @if($trialEligible)
                    <!-- Botão Trial 3 dias -->
                    <div style="margin-bottom: 16px;">
                        <button type="button" class="rr-assinatura-btn rr-assinatura-btn--cta" id="btnAtivarTrial" style="background: linear-gradient(135deg, #10b981, #059669); width: 100%;">
                            <i class="fas fa-gift"></i> Testar Grátis por 3 Dias
                        </button>
                        <p style="text-align: center; color: #6ee7b7; font-size: 12px; margin-top: 6px;">
                            <i class="fas fa-info-circle"></i> Exclusivo para quem já participou de X1 ou Bolão. Sem cobrança.
                        </p>
                    </div>
                    <div style="text-align: center; color: #64748b; font-size: 13px; margin-bottom: 12px;">— ou —</div>
                    @elseif($trialBlockedReason === 'no_activity')
                    <div style="margin-bottom: 16px; padding: 12px; background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); border-radius: 8px;">
                        <p style="color: #fbbf24; font-size: 13px; margin: 0; text-align: center;">
                            <i class="fas fa-lightbulb"></i> <strong>Participe de um X1 ou Bolão</strong> e ganhe 3 dias grátis de Premium!
                        </p>
                    </div>
                    @elseif($trialBlockedReason === 'no_cpf')
                    <div style="margin-bottom: 16px; padding: 12px; background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); border-radius: 8px;">
                        <p style="color: #fbbf24; font-size: 13px; margin: 0; text-align: center;">
                            <i class="fas fa-id-card"></i> <strong>Informe seu CPF no perfil</strong> para liberar 3 dias grátis de Premium!
                        </p>
                    </div>
                    @endif

                    <button type="button" class="rr-assinatura-btn rr-assinatura-btn--cta" onclick="if(window.openPremiumTab) window.openPremiumTab(); return false;">
                        <i class="fas fa-rocket"></i> Assinar Premium
                    </button>
                    <p class="rr-assinatura-cta__note">A partir de R$ 49,90/mês · Cartão ou PIX</p>
                </div>
            </div>

            @if($trialEligible)
            <script>
            document.getElementById('btnAtivarTrial')?.addEventListener('click', async function() {
                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ativando...';
                
                try {
                    const response = await fetch('{{ url("/api/subscriptions/start-trial") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ plan_slug: 'mensal' })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check-circle"></i> Trial ativado!';
                        btn.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)';
                        
                        // Mostrar mensagem de sucesso
                        const msg = document.createElement('div');
                        msg.style.cssText = 'text-align:center;color:#22c55e;font-weight:600;margin-top:12px;font-size:15px;';
                        msg.innerHTML = '🎉 ' + (data.message || 'Trial de 3 dias ativado com sucesso!');
                        btn.parentElement.appendChild(msg);
                        
                        setTimeout(() => window.location.reload(), 2500);
                    } else {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                        alert(data.message || 'Erro ao ativar trial. Tente novamente.');
                    }
                } catch (error) {
                    console.error('Erro ao ativar trial:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert('Erro de conexão. Tente novamente.');
                }
            });
            </script>
            @endif
            @endif
        </div>
        </section>
    </div>

    <!-- SEÇÃO 4: PRÊMIOS -->
    <div class="rr-perfil-section-content" id="premiosSection" style="display: none;">
        <div id="financeiroPremiosAnchor"></div>
        @php
            // ====== X1 PRIZES ======
            $wonX1Results = \App\Models\X1Result::with(['room'])
                ->where('winner_user_id', $user->id)
                ->whereHas('room', function($q) {
                    $q->where('status', 'finished');
                })
                ->orderByDesc('processed_at')
                ->get();
            
            $x1Total = $wonX1Results->sum(fn($r) => $r->room->prize_total ?? 0);
            $x1Received = $wonX1Results->where('prize_paid_at', '!=', null)->sum(fn($r) => $r->room->prize_total ?? 0);
            $x1Pending = $x1Total - $x1Received;
            $x1Wins = $wonX1Results->count();
            
            // ====== FANTASY PRIZES ======
            $wonFantasyTeams = \App\Models\FantasyTeam::with(['fantasyLeague'])
                ->where('user_id', $user->id)
                ->where('prize_won', '>', 0)
                ->whereHas('fantasyLeague', function($q) {
                    $q->where('status', 'finalized');
                })
                ->orderByDesc('prize_paid_at')
                ->get();
            
            $fantasyTotal = $wonFantasyTeams->sum('prize_won');
            $fantasyReceived = $wonFantasyTeams->whereNotNull('prize_paid_at')->sum('prize_won');
            $fantasyPending = $fantasyTotal - $fantasyReceived;
            $fantasyWins = $wonFantasyTeams->count();

            // ====== AFFILIATE COMMISSIONS (BOLÃO) ======
            $affiliateCommissionQuery = $affiliateIsActive
                ? \App\Models\AffiliateCommission::where('affiliate_id', $affiliateAccount->id)->fantasy()
                : null;
            $affiliateCommissionTotal = $affiliateCommissionQuery ? (clone $affiliateCommissionQuery)->sum('commission_amount') : 0;
            $affiliateCommissionPaid = $affiliateCommissionQuery ? (clone $affiliateCommissionQuery)->where('status', 'paid')->sum('commission_amount') : 0;
            $affiliateCommissionPending = $affiliateCommissionQuery ? (clone $affiliateCommissionQuery)->whereIn('status', ['pending', 'approved'])->sum('commission_amount') : 0;
            
            // ====== TOTAIS UNIFICADOS ======
            $totalAcumulado = $x1Total + $fantasyTotal;
            $totalAReceber = $x1Pending + $fantasyPending + $affiliateCommissionPending;
            $totalVitorias = $x1Wins + $fantasyWins;
            $pagamentosPendentes = $wonX1Results->whereNull('prize_paid_at')->count() + $wonFantasyTeams->whereNull('prize_paid_at')->count();
        @endphp

        <section class="rr-finance-panel rr-finance-panel--prizes">
            <!-- Resumo compacto -->
        <div class="rr-perfil-card rr-finance-panel__card" style="margin-bottom: 20px;">
            <div class="rr-perfil-card__header" style="align-items:center;">
                <h3 class="rr-perfil-card__title" style="display:flex;gap:8px;align-items:center;">
                    <i class="fas fa-medal" style="color:#f97316;"></i>
                    Resumo de Prêmios
                </h3>
            </div>
            <div class="rr-carteira-saldo">
                <div class="rr-carteira-saldo__row" style="display:flex; justify-content:space-between; align-items:center; padding:6px 0;">
                    <span class="rr-carteira-saldo__label">Total acumulado</span>
                    <span class="rr-carteira-saldo__value" style="color:#f97316;">R$ {{ number_format($totalAcumulado, 2, ',', '.') }}</span>
                </div>
                <div class="rr-carteira-saldo__row" style="display:flex; justify-content:space-between; align-items:center; padding:6px 0;">
                    <span class="rr-carteira-saldo__label">Comissão</span>
                    <span class="rr-carteira-saldo__value" style="color:#2563eb;">R$ {{ number_format($affiliateCommissionTotal, 2, ',', '.') }}</span>
                </div>
                <div class="rr-carteira-saldo__row" style="display:flex; justify-content:space-between; align-items:center; padding:6px 0;">
                    <span class="rr-carteira-saldo__label">Valor pendente</span>
                    <span class="rr-carteira-saldo__value" style="color:#eab308;">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</span>
                </div>
                <div class="rr-carteira-saldo__note" style="margin-top:10px; font-size:12px; line-height:1.45; color:rgba(148,163,184,.92);">
                    Os valores do bolão já consideram o desconto da comissão de afiliado quando o prêmio foi gerado por indicação.
                </div>
            </div>
        </div>
        @if($affiliateIsActive || $affiliateIsPending)
        @php
            $affiliateTopTier = \App\Models\AffiliateTier::orderByDesc('min_referrals')->first();
            $affiliatePreviewTier = $affiliateIsActive ? $affiliateAccount->tierData() : null;
            $affiliateReferralsCount = (int) ($affiliateAccount->active_referrals ?? 0);
            $affiliateProgressTarget = 200;
            $affiliateProgressPercent = $affiliateReferralsCount >= $affiliateProgressTarget
                ? 100
                : (($affiliateReferralsCount / max(1, $affiliateProgressTarget)) * 100);
            $affiliateProgressDisplay = $affiliateReferralsCount > $affiliateProgressTarget
                ? ($affiliateReferralsCount . ' afiliados')
                : ($affiliateReferralsCount . ' / ' . $affiliateProgressTarget . ' afiliados');
            $affiliateProgressHelper = $affiliateReferralsCount > $affiliateProgressTarget
                ? 'Base ativa acima de 200 indicados.'
                : 'Acompanhe sua base ativa até a régua de 200 indicados.';
            $affiliateTopFantasyPercent = min(7, (float) (optional($affiliateTopTier)->fantasy_commission_percent ?? 7));
        @endphp
        <div class="rr-perfil-card rr-finance-panel__card" style="margin-bottom: 20px;">
            <div class="rr-perfil-card__header" style="align-items:center;">
                <h3 class="rr-perfil-card__title" style="display:flex;gap:8px;align-items:center;">
                    <i class="fas fa-handshake" style="color:#16a34a;"></i>
                    Programa de Afiliados
                </h3>
                @if($affiliateIsActive)
                    <span class="rr-carteira-pill" style="background:rgba(34,197,94,.18);color:#22c55e;border-color:rgba(34,197,94,.35);">Ativo</span>
                @else
                    <span class="rr-carteira-pill" style="background:rgba(234,179,8,.18);color:#f59e0b;border-color:rgba(234,179,8,.35);">Em análise</span>
                @endif
            </div>
            @if($affiliateIsActive)
                <div class="rr-affiliate-stage__chips" style="margin:0 0 16px;">
                    <span class="rr-affiliate-stage__chip"><i class="fas fa-bolt"></i> até {{ $affiliateTopFantasyPercent }}% no bolão</span>
                    <span class="rr-affiliate-stage__chip"><i class="fas fa-medal"></i> nível {{ optional($affiliatePreviewTier)->name ?? 'Afiliado' }}</span>
                </div>
                <div class="rr-affiliate-stage__link">
                    <span>Seu link de compartilhamento está ativo</span>
                    <strong>{{ url('/r/' . $affiliateAccount->referral_code) }}</strong>
                    <input type="hidden"
                           id="affiliateReferralLink"
                           value="{{ url('/r/' . $affiliateAccount->referral_code) }}"
                           data-full-url="{{ url('/r/' . $affiliateAccount->referral_code) }}">
                    <div class="rr-affiliate-stage__progress">
                        <div class="rr-affiliate-progress-container">
                            <div class="rr-affiliate-progress-bar" style="width: {{ number_format($affiliateProgressPercent, 2, '.', '') }}%;"></div>
                            <span class="rr-affiliate-progress-text">{{ $affiliateProgressDisplay }}</span>
                        </div>
                        <small class="rr-affiliate-stage__progress-meta">{{ $affiliateProgressHelper }}</small>
                    </div>
                </div>
                <div class="rr-affiliate-stage__actions" style="margin-top:18px;">
                    <button type="button" class="rr-perfil-hero__btn rr-perfil-hero__btn--ghost" onclick="copyAffiliateLink()">
                        <i class="fas fa-copy"></i>
                        Copiar link
                    </button>
                    <button type="button" class="rr-perfil-hero__btn rr-perfil-hero__btn--primary" onclick="shareAffiliateLink()">
                        <i class="fas fa-paper-plane"></i>
                        Compartilhar link
                    </button>
                </div>
            @else
                <div class="rr-carteira-saldo">
                    <div class="rr-carteira-saldo__row" style="display:block;padding:0;">
                        <span class="rr-carteira-saldo__label" style="display:block;margin-bottom:8px;">Solicitação recebida</span>
                        <span class="rr-carteira-saldo__value" style="display:block;color:#f59e0b;font-size:1rem;">Sua conta de afiliado está aguardando aprovação manual do admin.</span>
                    </div>
                </div>
            @endif
        </div>
        @endif
        </section>
    </div>

    <!-- SEÇÃO 5: X1/EQUIPES -->
    <div class="rr-perfil-section-content" id="x1equipesSection" style="display: none;">
        <div id="financeiroArenaAnchor"></div>
        <section class="rr-finance-panel rr-finance-panel--arena">
            <div class="rr-finance-panel__header">
                <div>
                    <span class="rr-finance-panel__eyebrow">Arena</span>
                    <h3 class="rr-finance-panel__title">Operação em X1 e bolão</h3>
                </div>
                <p class="rr-finance-panel__summary">Acompanhe salas ativas, equipes e desempenho financeiro da sua operação.</p>
            </div>
        <!-- Hero Stats Banner -->
        <div class="rr-x1-hero-banner" id="x1HeroBanner">
            <div class="rr-x1-hero-glow"></div>
            <div class="rr-x1-hero-content">
                <div class="rr-x1-hero-avatar">
                    <div class="rr-x1-hero-avatar__ring"></div>
                    <img src="{{ $avatarUrl ?? asset('assets/images/default.png') }}" alt="Avatar">
                    <div class="rr-x1-hero-level-badge" id="x1HeroLevelBadge" data-level="amador">
                        <span class="rr-x1-hero-level-badge__icon">🎯</span>
                        <span class="rr-x1-hero-level-badge__text" id="x1HeroLevelText">Amador</span>
                    </div>
                </div>
                <div class="rr-x1-hero-info">
                    <h2 class="rr-x1-hero-name">
                        @if($isPremium)
                            <i class="fas fa-crown rr-premium-crown-hero" title="Premium"></i>
                        @endif
                        {{ $user->username ?? $user->name }}
                    </h2>
                </div>
                <div class="rr-x1-hero-quick-stats">
                    <div class="rr-x1-hero-stat">
                        <span class="rr-x1-hero-stat__value rr-x1-hero-stat__value--money" id="x1HeroEarnings">R$ {{ number_format($totalAcumulado ?? 0, 2, ',', '.') }}</span>
                        <span class="rr-x1-hero-stat__label">Total Ganho</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rr-perfil-grid rr-perfil-grid--x1">
            <!-- Coluna 1: Salas Ativas -->
            <div class="rr-perfil-section">
                <!-- Card: Salas Ativas -->
                <div class="rr-perfil-card" id="x1ActiveRoomsCard">
                    <div class="rr-perfil-card__header">
                        <h3 class="rr-perfil-card__title">
                            <i class="fas fa-door-open"></i>
                            Minhas Salas Ativas
                        </h3>
                        <span class="rr-perfil-card__counter rr-x1-active-counter" id="x1ActiveCount">0</span>
                    </div>

                    <div class="rr-x1-rooms-list" id="x1ActiveRoomsList">
                        <div class="rr-x1-stats-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando salas...</span>
                        </div>
                    </div>

                    <div class="rr-x1-card-action">
                        <button onclick="if(window.switchHubTab) { window.switchHubTab('x1'); } else { window.location.href='/?tab=x1'; }" class="rr-x1-btn rr-x1-btn--primary">
                            <i class="fas fa-plus"></i> Criar Nova Sala
                        </button>
                    </div>
                </div>

                <!-- Card: Minhas Equipes Fantasy -->
                <div class="rr-perfil-card" id="fantasyTeamsCard">
                    <div class="rr-perfil-card__header">
                        <h3 class="rr-perfil-card__title">
                            <i class="fas fa-users" style="color: #8b5cf6;"></i>
                            Minhas Equipes Bolão
                        </h3>
                        <span class="rr-perfil-card__counter" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);" id="fantasyTeamsCount">0</span>
                    </div>

                    <div class="rr-x1-rooms-list" id="fantasyTeamsList">
                        <div class="rr-x1-stats-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Carregando equipes...</span>
                        </div>
                    </div>

                    <div class="rr-x1-card-action">
                        <button onclick="if(window.switchHubTab) { window.switchHubTab('equipes'); } else { window.location.href='/?tab=equipes'; }" class="rr-x1-btn" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                            <i class="fas fa-plus"></i> Montar Nova Equipe
                        </button>
                    </div>
                </div>
            </div>

        </div>
        </section>
    </div>

    <!-- SEÇÃO 5: AFILIADOS -->
    <div class="rr-perfil-section-content" id="afiliadosSection" style="display: none;">
        @if(false)
        @php
            $affiliateLogoUrl = asset('assets/images/logo_icon/logoafiliado.png');
            $affiliateFallbackLogoUrl = asset('assets/images/logo_icon/logo.png');
            $affiliateTopTier = \App\Models\AffiliateTier::orderByDesc('min_referrals')->first();
            $affiliateEntryTier = \App\Models\AffiliateTier::orderBy('min_referrals')->first();
            $affiliatePreviewTier = $user->isAffiliate() ? $user->affiliate->tierData() : $affiliateEntryTier;
            $affiliatePreviewNextTier = $user->isAffiliate() ? $user->affiliate->nextTier() : \App\Models\AffiliateTier::where('min_referrals', '>', 0)->orderBy('min_referrals')->first();
            $affiliateHeroPrimaryAction = $user->isAffiliate() ? 'shareAffiliateLink()' : 'validateProfileAndActivateAffiliate()';
            $affiliateHeroPrimaryLabel = $user->isAffiliate() ? 'Compartilhar agora' : 'Ativar programa';
            $affiliateHeroLead = $user->isAffiliate()
                ? 'Compartilhe seu link e acompanhe suas comissões do bolão em uma área mais direta e focada.'
                : 'Ative seu código, compartilhe com a comunidade e receba comissão somente sobre prêmios do bolão indicados pelo seu link.';
            $affiliateHeroTitle = $user->isAffiliate()
                ? 'Comissão de bolão na sua mão'
                : 'Bolão com comissão de até 7%';
            $affiliateTopFantasyPercent = min(7, (float) (optional($affiliateTopTier)->fantasy_commission_percent ?? 7));
            $affiliatePreviewTierName = optional($affiliatePreviewTier)->name ?? 'Afiliado';
            $affiliateReferralsCount = (int) ($user->isAffiliate() ? ($user->affiliate->active_referrals ?? 0) : 0);
            $affiliateProgressTarget = 200;
            $affiliateProgressPercent = $affiliateReferralsCount >= $affiliateProgressTarget
                ? 100
                : (($affiliateReferralsCount / max(1, $affiliateProgressTarget)) * 100);
            $affiliateProgressDisplay = $affiliateReferralsCount > $affiliateProgressTarget
                ? ($affiliateReferralsCount . ' afiliados')
                : ($affiliateReferralsCount . ' / ' . $affiliateProgressTarget . ' afiliados');
            $affiliateProgressHelper = $affiliateReferralsCount > $affiliateProgressTarget
                ? 'Base ativa acima de 200 indicados.'
                : 'Acompanhe sua base ativa até a régua de 200 indicados.';
            $affiliateHeroPill = $user->isAffiliate()
                ? ($affiliateReferralsCount . ' indicações ativas')
                : ('até ' . $affiliateTopFantasyPercent . '% no bolão');
            $affiliateHeroSecondaryPill = $user->isAffiliate()
                ? ('nível ' . $affiliatePreviewTierName)
                : 'comissão sobre prêmio';
            $affiliateHeroLinkText = $user->isAffiliate() ? url('/r/' . $user->affiliate->referral_code) : 'Seu link de afiliado aparece aqui assim que ativar.';
        @endphp

        <section class="rr-affiliate-stage">
            <div class="rr-affiliate-stage__copy">
                <span class="rr-affiliate-stage__kicker"><i class="fas fa-share-nodes"></i> programa de afiliados</span>

                <h2 class="rr-affiliate-stage__title">
                    <span>Compartilhe.</span>
                    {{ $affiliateHeroTitle }}
                </h2>

                <p class="rr-affiliate-stage__lead">{{ $affiliateHeroLead }}</p>

                <div class="rr-affiliate-stage__chips">
                    <span class="rr-affiliate-stage__chip"><i class="fas fa-bolt"></i> {{ $affiliateHeroPill }}</span>
                    <span class="rr-affiliate-stage__chip"><i class="fas fa-medal"></i> {{ $affiliateHeroSecondaryPill }}</span>
                    <span class="rr-affiliate-stage__chip"><i class="fas fa-link"></i> link vitalício por indicação</span>
                </div>

                <div class="rr-affiliate-stage__actions">
                    <button type="button" class="rr-perfil-hero__btn rr-perfil-hero__btn--primary" onclick="{{ $affiliateHeroPrimaryAction }}">
                        <i class="fas {{ $user->isAffiliate() ? 'fa-paper-plane' : 'fa-rocket' }}"></i>
                        {{ $affiliateHeroPrimaryLabel }}
                    </button>
                    @if($user->isAffiliate())
                        <button type="button" class="rr-perfil-hero__btn rr-perfil-hero__btn--ghost" onclick="copyAffiliateLink()">
                            <i class="fas fa-copy"></i>
                            Copiar link
                        </button>
                    @endif
                </div>

                <div class="rr-affiliate-stage__link">
                    <span>{{ $user->isAffiliate() ? 'Seu link está pronto' : 'Como vai funcionar' }}</span>
                    <strong>{{ $affiliateHeroLinkText }}</strong>
                    @if($user->isAffiliate())
                        <input type="hidden"
                               id="affiliateReferralLink"
                               value="{{ url('/r/' . $user->affiliate->referral_code) }}"
                               data-full-url="{{ url('/r/' . $user->affiliate->referral_code) }}">
                        <div class="rr-affiliate-stage__progress">
                            <div class="rr-affiliate-progress-container">
                                <div class="rr-affiliate-progress-bar" style="width: {{ number_format($affiliateProgressPercent, 2, '.', '') }}%;"></div>
                                <span class="rr-affiliate-progress-text">{{ $affiliateProgressDisplay }}</span>
                            </div>
                            <small class="rr-affiliate-stage__progress-meta">{{ $affiliateProgressHelper }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rr-affiliate-stage__visual" aria-hidden="true">
                <div class="rr-affiliate-stage__logo-wrap">
                    <span class="rr-affiliate-stage__logo-badge"><i class="fas fa-crown"></i> Afiliados</span>
                    <img class="rr-affiliate-stage__logo" src="{{ $affiliateLogoUrl }}" alt="Programa de Afiliados" onerror="this.src='{{ $affiliateFallbackLogoUrl }}'">
                </div>

                <div class="rr-affiliate-stage__floaters">
                    <article class="rr-affiliate-floater rr-affiliate-floater--share">
                        <i class="fas fa-share-alt"></i>
                        <strong>Compartilhe o link</strong>
                        <span>cada cadastro fica no seu código</span>
                    </article>
                    <article class="rr-affiliate-floater rr-affiliate-floater--level">
                        <i class="fas fa-arrow-trend-up"></i>
                        <strong>{{ $affiliatePreviewNextTier ? 'Suba de nível' : 'Nível máximo' }}</strong>
                        <span>
                            @if($affiliatePreviewNextTier)
                                {{ $user->isAffiliate() ? 'próximo: ' . $affiliatePreviewNextTier->name : 'suba no programa e avance até 7% no bolão' }}
                            @else
                                topo do programa alcançado
                            @endif
                        </span>
                    </article>
                    <article class="rr-affiliate-floater rr-affiliate-floater--cash">
                        <i class="fas fa-coins"></i>
                        <strong>Comissão sobre prêmio</strong>
                        <span>até {{ $affiliateTopFantasyPercent }}% por prêmio de bolão indicado</span>
                    </article>
                </div>
            </div>
        </section>
        @endif
    </div>

</div>

<!-- X1 Stats Styles -->
<style>
/* ===================================
   🔥 X1/EQUIPES HERO BANNER
   =================================== */
.rr-x1-hero-banner {
    position: relative;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    overflow: hidden;
    border: 1px solid rgba(249, 115, 22, 0.2);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.rr-x1-hero-glow {
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, transparent 60%);
    animation: x1HeroGlow 6s ease-in-out infinite;
    pointer-events: none;
}

@keyframes x1HeroGlow {
    0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.5; }
    50% { transform: translate(-20px, 20px) scale(1.1); opacity: 0.8; }
}

.rr-x1-hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.rr-x1-hero-avatar {
    position: relative;
    width: 90px;
    height: 90px;
    flex-shrink: 0;
}

.rr-x1-hero-avatar__ring {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f97316, #ea580c, #f97316);
    background-size: 200% 200%;
    animation: avatarRingRotate 3s linear infinite;
}

@keyframes avatarRingRotate {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

.rr-x1-hero-avatar img {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #1a1a2e;
}

/* Level Badge */
.rr-x1-hero-level-badge {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 12px;
    border: 2px solid #1a1a2e;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rr-x1-hero-level-badge__icon {
    font-size: 0.75rem;
}

.rr-x1-hero-level-badge__text {
    font-weight: 800;
}

/* Nível: Amador (padrão) */
.rr-x1-hero-level-badge[data-level="amador"] {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    box-shadow: 0 2px 8px rgba(107, 114, 128, 0.4);
}

/* Nível: Competidor (R$1.000+) */
.rr-x1-hero-level-badge[data-level="competidor"] {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);
}

/* Nível: Ascendente (R$5.000+) */
.rr-x1-hero-level-badge[data-level="ascendente"] {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
}

/* Nível: Elite (R$10.000+) */
.rr-x1-hero-level-badge[data-level="elite"] {
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    box-shadow: 0 2px 8px rgba(168, 85, 247, 0.4);
}

/* Nível: Rei do X1 (R$100.000+) */
.rr-x1-hero-level-badge[data-level="rei"] {
    background: linear-gradient(135deg, #f59e0b, #d97706, #f59e0b);
    background-size: 200% 200%;
    animation: reiGlow 2s ease-in-out infinite;
    box-shadow: 0 2px 12px rgba(245, 158, 11, 0.6);
}

@keyframes reiGlow {
    0%, 100% { background-position: 0% 50%; box-shadow: 0 2px 12px rgba(245, 158, 11, 0.6); }
    50% { background-position: 100% 50%; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.8); }
}

.rr-x1-hero-info {
    flex: 1;
    min-width: 150px;
}

.rr-x1-hero-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 0.25rem 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rr-premium-crown-hero {
    color: #fbbf24;
    font-size: 1.3rem;
    filter: drop-shadow(0 0 6px rgba(251, 191, 36, 0.7));
    animation: crownGlowHero 2s ease-in-out infinite;
}

@keyframes crownGlowHero {
    0%, 100% { filter: drop-shadow(0 0 6px rgba(251, 191, 36, 0.7)); transform: scale(1); }
    50% { filter: drop-shadow(0 0 12px rgba(251, 191, 36, 1)); transform: scale(1.05); }
}

.rr-x1-hero-rating {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(251, 191, 36, 0.15);
    padding: 6px 12px;
    border-radius: 20px;
    color: #fbbf24;
    font-weight: 700;
    font-size: 0.9rem;
    border: 1px solid rgba(251, 191, 36, 0.3);
}

.rr-x1-hero-rating i {
    animation: starPulse 2s ease-in-out infinite;
}

@keyframes starPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.rr-x1-hero-quick-stats {
    display: flex;
    gap: 1rem;
    margin-left: auto;
}

.rr-x1-hero-stat {
    text-align: center;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    min-width: 80px;
    transition: all 0.3s;
}

.rr-x1-hero-stat:hover {
    background: rgba(249, 115, 22, 0.1);
    border-color: rgba(249, 115, 22, 0.3);
    transform: translateY(-2px);
}

.rr-x1-hero-stat__value {
    display: block;
    font-size: 1.5rem;
    font-weight: 800;
    color: #f97316; /* accent */
    line-height: 1;
}

.rr-x1-hero-stat__value--money {
    color: #22c55e;
    font-size: 1.2rem;
}

.rr-x1-hero-stat__label {
    display: block;
    font-size: 0.7rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

/* Grid específico para X1 */
.rr-perfil-grid--x1 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

@media (max-width: 900px) {
    .rr-perfil-grid--x1 {
        grid-template-columns: 1fr;
    }
}

/* Card Action Button */
.rr-x1-card-action {
    padding: 12px 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.rr-x1-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    width: 100%;
}

.rr-x1-btn--primary {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
}

.rr-x1-btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
}

.rr-x1-btn--premium {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    padding: 6px 14px;
    font-size: 0.8rem;
}

/* Active Counter Badge */
.rr-x1-active-counter {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    animation: pulseCounter 2s ease-in-out infinite;
}

@keyframes pulseCounter {
    0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
}

/* Refresh Button */
.rr-x1-refresh-btn {
    transition: all 0.3s;
}

.rr-x1-refresh-btn:hover {
    transform: rotate(180deg);
}

/* Premium CTA melhorado */
.rr-x1-premium-cta {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05));
    border-top: 1px solid rgba(139, 92, 246, 0.2);
    font-size: 0.85rem;
    color: #a78bfa;
}

.rr-x1-premium-cta i {
    color: #fbbf24;
    font-size: 1.1rem;
}

.rr-x1-premium-cta span {
    flex: 1;
}

/* Mobile responsivo para Hero */
@media (max-width: 640px) {
    .rr-x1-hero-banner {
        padding: 1.25rem;
    }
    
    .rr-x1-hero-content {
        flex-direction: column;
        text-align: center;
    }
    
    .rr-x1-hero-avatar {
        width: 70px;
        height: 70px;
    }
    
    .rr-x1-hero-name {
        font-size: 1.25rem;
    }
    
    .rr-x1-hero-quick-stats {
        margin-left: 0;
        width: 100%;
        justify-content: center;
    }
    
    .rr-x1-hero-stat {
        min-width: 70px;
        padding: 0.5rem 0.75rem;
    }
    
    .rr-x1-hero-stat__value {
        font-size: 1.25rem;
    }
    
    .rr-x1-hero-stat__value--money {
        font-size: 1rem;
    }
}

/* X1 Stats Grid */
.rr-x1-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    padding: 16px;
}

.rr-x1-stat-item {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(249, 115, 22, 0.1);
    border-radius: 10px;
    padding: 14px;
    text-align: center;
    transition: all 0.2s;
}
.rr-x1-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f97316; /* accent */
    display: block;
    line-height: 1.2;
}

.rr-x1-stat-label {
    font-size: 0.9rem;
    color: rgba(226, 232, 240, 0.8);
}

.rr-x1-stat-value.positive { color: #10b981; }
.rr-x1-stat-value.negative { color: #ef4444; }
.rr-x1-stat-value.neutral  { color: #e2e8f0; }

.rr-x1-stat-item:hover {
    border-color: rgba(249, 115, 22, 0.3);
    transform: translateY(-2px);
}

.rr-x1-stat-item.highlight {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.1));
    border-color: rgba(249, 115, 22, 0.3);
}

.rr-x1-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f97316; /* accent */
    display: block;
    line-height: 1.2;
}

.rr-x1-stat-value.positive { color: #22c55e; }
.rr-x1-stat-value.negative { color: #ef4444; }
.rr-x1-stat-value.neutral { color: #94a3b8; }

.rr-x1-stat-label {
    font-size: 0.75rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

/* Ranking List */
.rr-x1-ranking-list {
    max-height: 350px;
    overflow-y: auto;
    padding: 8px;
}

.rr-x1-ranking-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: rgba(15, 23, 42, 0.5);
    border-radius: 8px;
    margin-bottom: 6px;
    transition: all 0.2s;
}

.rr-x1-ranking-item:hover {
    background: rgba(249, 115, 22, 0.1);
}

.rr-x1-ranking-item.is-me {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(234, 88, 12, 0.1));
    border: 1px solid rgba(249, 115, 22, 0.3);
}

.rr-x1-ranking-position {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 700;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.rr-x1-ranking-position.gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #1e1b4b; }
.rr-x1-ranking-position.silver { background: linear-gradient(135deg, #94a3b8, #64748b); color: #1e1b4b; }
.rr-x1-ranking-position.bronze { background: linear-gradient(135deg, #f97316, #ea580c); color: white; }
.rr-x1-ranking-position.normal { background: rgba(71, 85, 105, 0.5); color: #94a3b8; }

.rr-x1-ranking-info {
    flex: 1;
    min-width: 0;
}

.rr-x1-ranking-name {
    font-weight: 600;
    color: #e2e8f0;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-x1-ranking-stats {
    font-size: 0.75rem;
    color: #64748b;
}

.rr-x1-ranking-rating {
    font-weight: 700;
    color: #fbbf24;
    font-size: 0.9rem;
}

/* Rooms & History Lists */
.rr-x1-rooms-list, .rr-x1-history-list {
    max-height: 320px;
    overflow-y: auto;
    padding: 12px;
}

.rr-x1-room-item, .rr-x1-history-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    margin-bottom: 10px;
    transition: all 0.3s;
}

.rr-x1-room-item:hover, .rr-x1-history-item:hover {
    background: rgba(249, 115, 22, 0.1);
    border-color: rgba(249, 115, 22, 0.2);
    transform: translateX(4px);
}

.rr-x1-room-item--live {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05));
    border-color: rgba(34, 197, 94, 0.3);
    animation: liveRoomPulse 2s ease-in-out infinite;
}

@keyframes liveRoomPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.3); }
    50% { box-shadow: 0 0 0 4px rgba(34, 197, 94, 0); }
}

.rr-x1-room-icon, .rr-x1-history-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.rr-x1-room-icon--host { 
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.2), rgba(245, 158, 11, 0.1)); 
    color: #fbbf24; 
}
.rr-x1-room-icon--opponent { 
    background: rgba(139, 92, 246, 0.2); 
    color: #a78bfa; 
}
.rr-x1-history-icon.victory { 
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.1)); 
    color: #22c55e; 
}
.rr-x1-history-icon.defeat { 
    background: rgba(239, 68, 68, 0.2); 
    color: #ef4444; 
}

.rr-x1-room-info, .rr-x1-history-info {
    flex: 1;
    min-width: 0;
}

.rr-x1-room-name, .rr-x1-history-name {
    font-weight: 600;
    color: #e2e8f0;
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rr-x1-room-meta, .rr-x1-history-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 4px;
}

.rr-x1-room-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.rr-x1-room-prize {
    text-align: right;
    flex-shrink: 0;
}

.rr-x1-room-prize__value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #22c55e;
}

.rr-x1-room-prize__label {
    font-size: 0.65rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rr-x1-room-value, .rr-x1-history-value {
    font-weight: 700;
    font-size: 1rem;
    text-align: right;
    flex-shrink: 0;
}

.rr-x1-history-value.positive { color: #22c55e; }
.rr-x1-history-value.negative { color: #ef4444; }

/* History Item específico */
.rr-x1-history-item.victory {
    border-left: 3px solid #22c55e;
}

.rr-x1-history-item.defeat {
    border-left: 3px solid #ef4444;
}

/* Loading & Empty States */
.rr-x1-stats-loading, .rr-x1-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 40px 16px;
    color: #64748b;
    text-align: center;
}

.rr-x1-stats-loading i { font-size: 2rem; color: #f97316; /* accent */ }
.rr-x1-empty-state i { font-size: 2.5rem; color: #475569; margin-bottom: 8px; }
.rr-x1-empty-state span { font-size: 0.95rem; color: #94a3b8; }
.rr-x1-empty-state small { font-size: 0.8rem; color: #64748b; }

/* Card Counter */
.rr-perfil-card__counter {
    background: rgba(249, 115, 22, 0.2);
    color: #f97316; /* accent */
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.rr-perfil-btn--icon {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 8px;
    background: rgba(249, 115, 22, 0.1);
    border: 1px solid rgba(249, 115, 22, 0.2);
    color: #f97316; /* accent */
    cursor: pointer;
    transition: all 0.2s;
}

.rr-perfil-btn--icon:hover {
    background: rgba(249, 115, 22, 0.2);
    transform: rotate(180deg);
}

.rr-perfil-btn--sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

/* Responsive */
@media (max-width: 768px) {
    .rr-x1-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        padding: 12px;
    }

    .rr-x1-stat-item { padding: 10px; }
    .rr-x1-stat-value { font-size: 1.2rem; }
}

/* 👑 ASSINATURA SECTION STYLES */
.rr-assinatura-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 1rem;
}

/* Status Card - Premium */
.rr-assinatura-status {
    position: relative;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(59, 130, 246, 0.1));
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 20px;
    padding: 2rem;
    overflow: hidden;
}

.rr-assinatura-status--premium {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.1), rgba(59, 130, 246, 0.15));
}

.rr-assinatura-status__glow {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at center, rgba(139, 92, 246, 0.15), transparent 50%);
    animation: assinaturaGlow 8s ease-in-out infinite;
    pointer-events: none;
}

@keyframes assinaturaGlow {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(10%, 10%); }
}

.rr-assinatura-status__header {
    position: relative;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.rr-assinatura-status__icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: logoPulse 2s ease-in-out infinite;
}

.rr-assinatura-status__icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 4px 15px rgba(59, 130, 246, 0.6));
}

@keyframes logoPulse {
    0%, 100% { 
        transform: scale(1); 
        filter: drop-shadow(0 4px 15px rgba(59, 130, 246, 0.6));
    }
    50% { 
        transform: scale(1.08); 
        filter: drop-shadow(0 6px 25px rgba(59, 130, 246, 0.8));
    }
}

.rr-assinatura-status__info h3 {
    font-size: 1.5rem;
    color: #e2e8f0;
    margin: 0 0 0.25rem;
}

.rr-assinatura-status__info p {
    color: #94a3b8;
    margin: 0;
}

.rr-premium-text {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 900;
}

.rr-assinatura-status__badge .badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.badge-active {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.badge-trial {
    background: rgba(168, 85, 247, 0.2);
    color: #a855f7;
    border: 1px solid rgba(168, 85, 247, 0.3);
}

.badge-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* Details Grid */
.rr-assinatura-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
}

.rr-assinatura-details__item {
    text-align: center;
}

.rr-assinatura-details__item--highlight {
    background: rgba(251, 191, 36, 0.1);
    border-radius: 8px;
    padding: 0.75rem;
}

.rr-assinatura-details__label {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
}

.rr-assinatura-details__value {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    color: #e2e8f0;
}

/* Benefits Grid */
.rr-assinatura-benefits {
    margin-bottom: 2rem;
}

.rr-assinatura-benefits h4 {
    font-size: 1rem;
    color: #94a3b8;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.rr-assinatura-benefits h4 i {
    color: #fbbf24;
}

.rr-assinatura-benefits__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}

.rr-assinatura-benefit {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 8px;
    border: 1px solid rgba(59, 130, 246, 0.2);
    color: #e2e8f0;
    font-size: 0.875rem;
}

.rr-assinatura-benefit i {
    color: #3b82f6;
    font-size: 1rem;
}

/* Actions */
.rr-assinatura-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.rr-assinatura-btn {
    padding: 0.875rem 2rem;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.938rem;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
}

.rr-assinatura-btn--primary {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
}

.rr-assinatura-btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.rr-assinatura-btn--secondary {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.rr-assinatura-btn--secondary:hover {
    background: rgba(239, 68, 68, 0.2);
}

/* Cancel Info */
.rr-assinatura-cancel-info {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.05);
    text-align: center;
}

.rr-assinatura-cancel-info small {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.8rem;
}

.rr-assinatura-cancel-info i {
    color: rgba(59, 130, 246, 0.7);
}

/* CTA Card - Non Premium */
.rr-assinatura-cta {
    position: relative;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(59, 130, 246, 0.15));
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 20px;
    padding: 3rem 2rem;
    text-align: center;
    overflow: hidden;
}

.rr-assinatura-cta__glow {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.3), transparent 70%);
    filter: blur(60px);
    pointer-events: none;
}

.rr-assinatura-cta__content {
    position: relative;
    z-index: 1;
}

.rr-assinatura-cta__icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #1a1a2e;
    margin: 0 auto 1.5rem;
    box-shadow: 0 0 40px rgba(251, 191, 36, 0.5);
    animation: crownFloat 3s ease-in-out infinite;
}

@keyframes crownFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.rr-assinatura-cta h3 {
    font-size: 1.75rem;
    color: #e2e8f0;
    margin-bottom: 0.75rem;
}

.rr-assinatura-cta > .rr-assinatura-cta__content > p {
    color: #94a3b8;
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

.rr-assinatura-cta__benefits {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 400px;
    margin: 0 auto 2rem;
    text-align: left;
}

.rr-assinatura-cta__benefit {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #e2e8f0;
    font-size: 0.938rem;
}

.rr-assinatura-cta__benefit i {
    color: #22c55e;
    font-size: 1rem;
}

.rr-assinatura-btn--cta {
    background: linear-gradient(135deg, #8b5cf6, #3b82f6);
    color: white;
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    animation: ctaPulse 2s ease-in-out infinite;
}

@keyframes ctaPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
    50% { box-shadow: 0 0 0 15px rgba(139, 92, 246, 0); }
}

.rr-assinatura-btn--cta:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 35px rgba(139, 92, 246, 0.5);
}

.rr-assinatura-cta__note {
    color: #64748b;
    font-size: 0.875rem;
    margin-top: 1rem;
}

@media (max-width: 576px) {
    .rr-assinatura-status__header {
        flex-direction: column;
        text-align: center;
    }

    .rr-assinatura-details {
        grid-template-columns: 1fr;
    }

    .rr-assinatura-benefits__grid {
        grid-template-columns: 1fr;
    }
}

/* ===== Assinatura: fix de legibilidade ===== */
#assinaturaSection .rr-assinatura-container {
    color: #e2e8f0;
}

#assinaturaSection .rr-assinatura-status,
#assinaturaSection .rr-assinatura-cta {
    background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(2, 6, 23, 0.94));
    border-color: rgba(96, 165, 250, 0.32);
    box-shadow: 0 12px 34px rgba(2, 6, 23, 0.42), inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

#assinaturaSection .rr-assinatura-status__info h3,
#assinaturaSection .rr-assinatura-cta h3,
#assinaturaSection .rr-assinatura-details__value,
#assinaturaSection .rr-assinatura-benefit,
#assinaturaSection .rr-assinatura-cta__benefit {
    color: #f1f5f9;
}

#assinaturaSection .rr-assinatura-status__info p,
#assinaturaSection .rr-assinatura-benefits h4,
#assinaturaSection .rr-assinatura-cta > .rr-assinatura-cta__content > p,
#assinaturaSection .rr-assinatura-cta__note,
#assinaturaSection .rr-assinatura-details__label,
#assinaturaSection .rr-assinatura-cancel-info small {
    color: #cbd5e1;
}

#assinaturaSection .rr-assinatura-details {
    background: rgba(15, 23, 42, 0.82);
    border: 1px solid rgba(148, 163, 184, 0.2);
}

#assinaturaSection .rr-assinatura-details__item {
    background: rgba(30, 41, 59, 0.52);
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 10px;
    padding: 10px;
}

#assinaturaSection .rr-assinatura-details__item--highlight {
    background: rgba(234, 179, 8, 0.18);
    border-color: rgba(234, 179, 8, 0.35);
}

#assinaturaSection .rr-assinatura-benefit,
#assinaturaSection .rr-assinatura-cta__benefit {
    background: rgba(30, 41, 59, 0.62);
    border: 1px solid rgba(96, 165, 250, 0.24);
    border-radius: 10px;
}

#assinaturaSection .rr-assinatura-status__badge .badge {
    font-weight: 700;
    color: #f8fafc;
}

#assinaturaSection .badge-active {
    background: rgba(34, 197, 94, 0.26);
    border-color: rgba(34, 197, 94, 0.45);
}

#assinaturaSection .badge-trial {
    background: rgba(168, 85, 247, 0.28);
    border-color: rgba(168, 85, 247, 0.45);
}

#assinaturaSection .badge-cancelled {
    background: rgba(239, 68, 68, 0.28);
    border-color: rgba(239, 68, 68, 0.45);
}

#assinaturaSection .rr-assinatura-btn--primary,
#assinaturaSection .rr-assinatura-btn--cta {
    color: #fff;
}

#assinaturaSection .rr-assinatura-btn--secondary {
    background: rgba(239, 68, 68, 0.16);
    color: #fecaca;
    border-color: rgba(239, 68, 68, 0.35);
}
</style>

<script>
// X1 Stats Module
window.rrTotalGanhoPerfil = @json($totalAcumulado ?? 0);
window.RRX1Stats = {
    userId: {{ $user->id ?? 'null' }},
    isPremium: {{ $isPremium ? 'true' : 'false' }},
    loaded: false,

    async init() {
        if (!this.userId) return;

        const self = this;

        // Carregar dados quando a aba X1/Equipes for ativada
        document.querySelectorAll('.rr-subcard[data-section="x1equipes"]').forEach(card => {
            card.addEventListener('click', () => {
                self.loaded = false; // Reset para permitir recarregar
                setTimeout(() => self.loadAll(), 100);
            });
        });

        // Carregar ranking e histórico quando a aba Prêmios for ativada
        document.querySelectorAll('.rr-subcard[data-section="premios"], [data-filter="premios"]').forEach(card => {
            card.addEventListener('click', () => {
                setTimeout(() => {
                    self.loadRanking();
                    self.loadHistory();
                }, 100);
            });
        });

        // Se a seção X1/Equipes já estiver visível, carregar dados imediatamente
        const x1Section = document.getElementById('x1equipesSection');
        if (x1Section && x1Section.style.display !== 'none' && x1Section.style.display !== '') {
            setTimeout(() => self.loadAll(), 200);
        }

        // Observer para detectar quando a seção é exibida
        if (x1Section) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        if (x1Section.style.display === 'block' || x1Section.style.display === '') {
                            if (!self.loaded) {
                                setTimeout(() => self.loadAll(), 100);
                            }
                        }
                    }
                });
            });
            observer.observe(x1Section, { attributes: true, attributeFilter: ['style'] });
        }

        // Observer para detectar quando Prêmios é exibida (ranking e histórico estão lá)
        const premiosSection = document.getElementById('premiosSection');
        if (premiosSection) {
            let premiosRankingLoaded = false;
            const premiosObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        if (premiosSection.style.display === 'block' || premiosSection.style.display === '') {
                            if (!premiosRankingLoaded) {
                                premiosRankingLoaded = true;
                                setTimeout(() => {
                                    self.loadRanking();
                                    self.loadHistory();
                                }, 100);
                            }
                        }
                    }
                });
            });
            premiosObserver.observe(premiosSection, { attributes: true, attributeFilter: ['style'] });
        }
    },

    async loadAll() {
        if (this.loaded) {
            console.log('[X1] loadAll já foi executado, ignorando...');
            return;
        }
        this.loaded = true;
        
        console.log('[X1] === INICIANDO loadAll ===');
        
        try {
            await Promise.all([
                this.loadMyStats(),
                this.loadRanking(),
                this.loadActiveRooms(),
                this.loadHistory(),
                this.loadFantasyTeams() // ✅ NOVO: Carregar equipes Fantasy
            ]);
            console.log('[X1] === loadAll COMPLETO ===');
        } catch (error) {
            console.error('[X1] Erro no loadAll:', error);
        }
    },

    async loadMyStats() {
        try {
            console.log('[X1] Carregando stats...');
            const response = await fetch('/web/x1/stats/me', {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            console.log('[X1] Stats - Status:', response.status);

            if (!response.ok) {
                const text = await response.text();
                console.error('[X1] Stats - Erro:', text);
                throw new Error('Erro ao carregar estatísticas');
            }

            const json = await response.json();
            console.log('[X1] Stats - Data:', json);
            this.renderStats(json.data || {});
        } catch (error) {
            console.error('Erro ao carregar stats:', error);
            this.renderStatsError();
        }
    },

    // Calcula e atualiza o nível do usuário baseado no total ganho
    updateLevel(totalPrizeWon) {
        const badge = document.getElementById('x1HeroLevelBadge');
        const text = document.getElementById('x1HeroLevelText');
        const iconEl = badge?.querySelector('.rr-x1-hero-level-badge__icon');
        
        if (!badge || !text) return;

        let level, levelName, icon;
        
        if (totalPrizeWon >= 100000) {
            level = 'rei';
            levelName = 'Rei do X1';
            icon = '👑';
        } else if (totalPrizeWon >= 10000) {
            level = 'elite';
            levelName = 'Elite';
            icon = '💎';
        } else if (totalPrizeWon >= 5000) {
            level = 'ascendente';
            levelName = 'Ascendente';
            icon = '🚀';
        } else if (totalPrizeWon >= 1000) {
            level = 'competidor';
            levelName = 'Competidor';
            icon = '⚔️';
        } else {
            level = 'amador';
            levelName = 'Amador';
            icon = '🎯';
        }

        badge.dataset.level = level;
        text.textContent = levelName;
        if (iconEl) iconEl.textContent = icon;
    },

    renderStats(stats) {
        const container = document.getElementById('x1StatsContent');

        // Atualizar Hero Banner
        const heroRating = document.getElementById('x1HeroRating');
        const heroWins = document.getElementById('x1HeroWins');
        const heroLosses = document.getElementById('x1HeroLosses');
        const heroEarnings = document.getElementById('x1HeroEarnings');

        if (heroRating) heroRating.textContent = stats.rating || 1000;
        if (heroWins) heroWins.textContent = stats.wins || 0;
        if (heroLosses) heroLosses.textContent = stats.losses || 0;
        
        const totalPrizeWon = parseFloat(stats.total_prize_won) || 0;
        const totalGanhoPerfil = Number.isFinite(window.rrTotalGanhoPerfil)
            ? window.rrTotalGanhoPerfil
            : totalPrizeWon;
        
        if (heroEarnings) {
            heroEarnings.textContent = `R$ ${totalGanhoPerfil.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        }

        // Atualizar nível baseado no total ganho (Prêmios)
        this.updateLevel(totalGanhoPerfil);

        const winRate = parseFloat(stats.win_rate) || 0;
        const profit = parseFloat(stats.profit) || 0;
        const streak = stats.current_streak || 0;

        container.innerHTML = `
            <div class="rr-x1-stat-item highlight">
                <span class="rr-x1-stat-value">${stats.wins || 0}/${stats.losses || 0}</span>
                <span class="rr-x1-stat-label">Vitórias/Derrotas</span>
            </div>
            <div class="rr-x1-stat-item">
                <span class="rr-x1-stat-value ${winRate >= 50 ? 'positive' : winRate > 0 ? 'negative' : 'neutral'}">${winRate.toFixed(1)}%</span>
                <span class="rr-x1-stat-label">Taxa de Vitória</span>
            </div>
            <div class="rr-x1-stat-item">
                <span class="rr-x1-stat-value positive">R$ ${totalGanhoPerfil.toFixed(2)}</span>
                <span class="rr-x1-stat-label">Total Ganho</span>
            </div>
            <div class="rr-x1-stat-item">
                <span class="rr-x1-stat-value ${profit >= 0 ? 'positive' : 'negative'}">${profit >= 0 ? '+' : ''}R$ ${Math.abs(profit).toFixed(2)}</span>
                <span class="rr-x1-stat-label">${profit >= 0 ? 'Lucro' : 'Prejuízo'}</span>
            </div>
            <div class="rr-x1-stat-item">
                <span class="rr-x1-stat-value ${streak > 0 ? 'positive' : streak < 0 ? 'negative' : 'neutral'}">${streak > 0 ? '+' : ''}${streak}</span>
                <span class="rr-x1-stat-label">Sequência Atual</span>
            </div>
            <div class="rr-x1-stat-item">
                <span class="rr-x1-stat-value">${stats.best_win_streak || 0}</span>
                <span class="rr-x1-stat-label">Melhor Sequência</span>
            </div>
            <div class="rr-x1-stat-item">
                <span class="rr-x1-stat-value">${stats.total_x1s || 0}</span>
                <span class="rr-x1-stat-label">Total de X1s</span>
            </div>
            ${stats.ranking_position ? `
            <div class="rr-x1-stat-item highlight">
                <span class="rr-x1-stat-value">#${stats.ranking_position}</span>
                <span class="rr-x1-stat-label">Posição Ranking</span>
            </div>
            ` : `
            <div class="rr-x1-stat-item">
                <span class="rr-x1-stat-value neutral">-</span>
                <span class="rr-x1-stat-label">Ranking</span>
            </div>
            `}
        `;
    },

    renderStatsError() {
        document.getElementById('x1StatsContent').innerHTML = `
            <div class="rr-x1-empty-state" style="grid-column: span 2;">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Erro ao carregar estatísticas</span>
            </div>
        `;
    },

    async loadRanking() {
        try {
            console.log('[X1] Carregando ranking...');
            const response = await fetch('/web/x1/rankings/top30', {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            console.log('[X1] Ranking - Status:', response.status);
            
            if (!response.ok) {
                const text = await response.text();
                console.error('[X1] Ranking - Erro:', text);
                throw new Error('Erro ao carregar ranking');
            }

            const json = await response.json();
            console.log('[X1] Ranking - Data:', json);
            this.renderRanking(json.data || []);
        } catch (error) {
            console.error('Erro ao carregar ranking:', error);
            this.renderRankingError();
        }
    },

    async refreshRanking() {
        const btn = document.querySelector('#x1RankingCard .rr-perfil-btn--icon');
        if (btn) btn.disabled = true;

        await this.loadRanking();

        if (btn) {
            btn.disabled = false;
        }
    },

    renderRanking(ranking) {
        const container = document.getElementById('x1RankingList');

        if (!ranking || ranking.length === 0) {
            container.innerHTML = `
                <div class="rr-x1-empty-state">
                    <i class="fas fa-trophy"></i>
                    <span>Nenhum jogador no ranking ainda</span>
                    <small>Seja o primeiro a competir!</small>
                </div>
            `;
            return;
        }

        container.innerHTML = ranking.map((player, index) => {
            const position = index + 1;
            const posClass = position === 1 ? 'gold' : position === 2 ? 'silver' : position === 3 ? 'bronze' : 'normal';
            const isMe = player.user_id === this.userId;
            const totalPrizeWon = parseFloat(player.total_prize_won || 0).toFixed(2);

            return `
                <div class="rr-x1-ranking-item ${isMe ? 'is-me' : ''}">
                    <div class="rr-x1-ranking-position ${posClass}">${position}</div>
                    <div class="rr-x1-ranking-info">
                        <div class="rr-x1-ranking-name">${player.name || player.username} ${isMe ? '(você)' : ''}</div>
                        <div class="rr-x1-ranking-stats">${player.wins}V ${player.losses}D · ${parseFloat(player.win_rate).toFixed(1)}%</div>
                    </div>
                    <div class="rr-x1-ranking-rating" title="Total ganho">R$ ${totalPrizeWon}</div>
                </div>
            `;
        }).join('');
    },

    renderRankingError() {
        document.getElementById('x1RankingList').innerHTML = `
            <div class="rr-x1-empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Erro ao carregar ranking</span>
            </div>
        `;
    },

    async loadActiveRooms() {
        try {
            console.log('[X1] Carregando salas ativas...');
            const response = await fetch('/web/x1/active/me', {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            console.log('[X1] Salas ativas - Status:', response.status);
            
            if (!response.ok) {
                const text = await response.text();
                console.error('[X1] Salas ativas - Erro:', text);
                throw new Error('Erro ao carregar salas');
            }

            const json = await response.json();
            console.log('[X1] Salas ativas - Data:', json);
            this.renderActiveRooms(json.data || []);
        } catch (error) {
            console.error('Erro ao carregar salas:', error);
            this.renderActiveRoomsError();
        }
    },

    renderActiveRooms(rooms) {
        const container = document.getElementById('x1ActiveRoomsList');
        const counter = document.getElementById('x1ActiveCount');

        if (counter) counter.textContent = rooms?.length || 0;

        if (!rooms || rooms.length === 0) {
            container.innerHTML = `
                <div class="rr-x1-empty-state">
                    <i class="fas fa-gamepad"></i>
                    <span>Nenhuma sala ativa</span>
                    <small>Crie uma sala e desafie outros jogadores!</small>
                </div>
            `;
            return;
        }

        const statusLabels = {
            'pending_payment': { label: 'Aguardando Pagamento', color: '#fbbf24', icon: 'fa-clock' },
            'open': { label: 'Aguardando Oponente', color: '#3b82f6', icon: 'fa-hourglass-half' },
            'in_progress': { label: 'Em Andamento', color: '#22c55e', icon: 'fa-play-circle' }
        };

        container.innerHTML = rooms.map(room => {
            const status = statusLabels[room.status] || { label: room.status, color: '#94a3b8', icon: 'fa-question' };
            const prizeTotal = parseFloat(room.prize_total) || (parseFloat(room.valor_entrada) * 2 * 0.9);
            
            return `
                <div class="rr-x1-room-item ${room.status === 'in_progress' ? 'rr-x1-room-item--live' : ''}">
                    <div class="rr-x1-room-icon ${room.is_host ? 'rr-x1-room-icon--host' : 'rr-x1-room-icon--opponent'}">
                        <i class="fas ${room.is_host ? 'fa-crown' : 'fa-user-ninja'}"></i>
                    </div>
                    <div class="rr-x1-room-info">
                        <div class="rr-x1-room-name">${room.name || 'Sala X1 #' + room.id}</div>
                        <div class="rr-x1-room-meta">
                            <span>${room.modalidade || 'X1'}</span>
                            <span class="rr-x1-room-status" style="color: ${status.color};">
                                <i class="fas ${status.icon}"></i> ${status.label}
                            </span>
                        </div>
                    </div>
                    <div class="rr-x1-room-prize">
                        <div class="rr-x1-room-prize__value">R$ ${prizeTotal.toFixed(2)}</div>
                        <div class="rr-x1-room-prize__label">Prêmio</div>
                    </div>
                </div>
            `;
        }).join('');
    },

    renderActiveRoomsError() {
        document.getElementById('x1ActiveRoomsList').innerHTML = `
            <div class="rr-x1-empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Erro ao carregar salas</span>
            </div>
        `;
    },

    async loadHistory() {
        try {
            console.log('[X1] Carregando histórico...');
            const response = await fetch('/web/x1/history/me?per_page=10', {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            console.log('[X1] Histórico - Status:', response.status);

            if (!response.ok) {
                const text = await response.text();
                console.error('[X1] Histórico - Erro:', text);
                throw new Error('Erro ao carregar histórico');
            }

            const json = await response.json();
            console.log('[X1] Histórico - Data:', json);
            this.renderHistory(json.data || []);
        } catch (error) {
            console.error('Erro ao carregar histórico:', error);
            this.renderHistoryError();
        }
    },

    renderHistory(history) {
        const container = document.getElementById('x1HistoryList');

        if (!history || history.length === 0) {
            container.innerHTML = `
                <div class="rr-x1-empty-state">
                    <i class="fas fa-history"></i>
                    <span>Nenhuma partida no histórico</span>
                    <small>Suas batalhas aparecerão aqui</small>
                </div>
            `;
            return;
        }

        container.innerHTML = history.map(match => {
            const isVictory = match.is_winner;
            const profit = parseFloat(match.profit);
            const processedDate = match.processed_at ? new Date(match.processed_at).toLocaleDateString('pt-BR') : '';

            return `
                <div class="rr-x1-history-item ${isVictory ? 'victory' : 'defeat'}">
                    <div class="rr-x1-history-icon ${isVictory ? 'victory' : 'defeat'}">
                        <i class="fas ${isVictory ? 'fa-trophy' : 'fa-skull-crossbones'}"></i>
                    </div>
                    <div class="rr-x1-history-info">
                        <div class="rr-x1-history-name">${isVictory ? '🏆 Vitória' : '💀 Derrota'} - ${match.modalidade || 'X1'}</div>
                        <div class="rr-x1-history-meta">
                            <span>${match.competitor || match.competitor_group || 'Competidor'}</span>
                            ${processedDate ? `<span>· ${processedDate}</span>` : ''}
                        </div>
                    </div>
                    <div class="rr-x1-history-value ${profit >= 0 ? 'positive' : 'negative'}">
                        ${profit >= 0 ? '+' : '-'}R$ ${Math.abs(profit).toFixed(2)}
                    </div>
                </div>
            `;
        }).join('');
    },

    renderHistoryError() {
        document.getElementById('x1HistoryList').innerHTML = `
            <div class="rr-x1-empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Erro ao carregar histórico</span>
            </div>
        `;
    },

    // ===========================
    // FANTASY TEAMS
    // ===========================
    async loadFantasyTeams() {
        try {
            console.log('[Fantasy] Carregando minhas equipes...');
            const container = document.getElementById('fantasyTeamsList');
            const counter = document.getElementById('fantasyTeamsCount');
            
            if (!container) return;
            
            // Mostrar loader
            container.innerHTML = `
                <div class="rr-x1-stats-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Carregando equipes...</span>
                </div>
            `;
            
            // Buscar minhas equipes Fantasy via PHP (server-side)
            const response = await fetch('/web/fantasy/my-teams', {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Erro ao carregar equipes');
            }
            
            const data = await response.json();
            console.log('[Fantasy] Equipes carregadas:', data);
            
            if (counter) {
                counter.textContent = data.teams?.length || 0;
            }
            
            this.renderFantasyTeams(data.teams || []);
            
        } catch (error) {
            console.error('[Fantasy] Erro ao carregar equipes:', error);
            const container = document.getElementById('fantasyTeamsList');
            if (container) {
                container.innerHTML = `
                    <div class="rr-x1-empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Erro ao carregar equipes</span>
                        <small>Tente novamente mais tarde</small>
                    </div>
                `;
            }
        }
    },
    
    renderFantasyTeams(teams) {
        const container = document.getElementById('fantasyTeamsList');
        
        if (!teams || teams.length === 0) {
            container.innerHTML = `
                <div class="rr-x1-empty-state">
                    <i class="fas fa-users"></i>
                    <span>Você ainda não tem equipes</span>
                    <small>Monte sua primeira equipe e comece a competir!</small>
                </div>
            `;
            return;
        }
        
        // Renderizar equipes
        container.innerHTML = teams.map(team => {
            const isFinished = team.league_status === 'finished' || team.league_status === 'finalized';
            const statusClass = isFinished ? 'finished' : 
                               team.league_status === 'active' ? 'active' : 'upcoming';
            const statusText = isFinished ? 'Finalizado' :
                              team.league_status === 'active' ? 'Em Andamento' : 'Aguardando';
            const statusColor = isFinished ? '#6366f1' :
                               team.league_status === 'active' ? '#22c55e' : '#eab308';
            
            const prizeHTML = team.prize_won > 0 ? `
                <div style="margin-top: 8px; padding: 8px; background: rgba(16, 185, 129, 0.1); border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.3);">
                    <div style="color: #10b981; font-weight: 600; font-size: 14px;">
                        🏆 Prêmio: R$ ${team.prize_won.toFixed(2).replace('.', ',')}
                    </div>
                    ${team.prize_paid_at ? `
                        <div style="color: #6ee7b7; font-size: 11px; margin-top: 2px;">
                            <i class="fas fa-check-circle"></i> Pago em ${team.prize_paid_at}
                        </div>
                    ` : `
                        <div style="color: #fbbf24; font-size: 11px; margin-top: 2px;">
                            <i class="fas fa-clock"></i> Aguardando pagamento
                        </div>
                    `}
                </div>
            ` : '';
            
            return `
                <div class="rr-x1-room-item">
                    <div class="rr-x1-room-item__header">
                        <div>
                            <div class="rr-x1-room-item__title">${team.team_name}</div>
                            <div class="rr-x1-room-item__subtitle">${team.league_name}</div>
                        </div>
                        <span class="rr-x1-room-badge" style="background: ${statusColor}20; color: ${statusColor};">
                            ${statusText}
                        </span>
                    </div>
                    <div class="rr-x1-room-item__stats">
                        <div class="rr-x1-stat">
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <span>${team.total_points} pts</span>
                        </div>
                        <div class="rr-x1-stat">
                            <i class="fas fa-trophy" style="color: #8b5cf6;"></i>
                            <span>#${team.position || '—'}</span>
                        </div>
                        <div class="rr-x1-stat">
                            <i class="fas fa-users" style="color: #6366f1;"></i>
                            <span>${team.competitors_count || 0} competidores</span>
                        </div>
                    </div>
                    ${prizeHTML}
                </div>
            `;
        }).join('');
    }
};

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.RRX1Stats.init();
});
</script>

<style>
/* ============================================
   PERFIL & CARTEIRA - ESTILO MODERNO
   ============================================ */
.rr-perfil-container {
    width: min(1340px, 100%);
    max-width: 1400px;
    margin: 0 auto;
}

/* Desktop: limitar largura do submenu */
@media (min-width: 769px) {
    .rr-perfil-container #rrPerfilSubmenu {
        width: min(1220px, 100%);
        max-width: 1220px;
        margin: 0 auto 1rem;
    }
}

.rr-perfil-header {
    margin-bottom: 32px;
    text-align: center;
}

.rr-perfil-header__title {
    font-size: 2rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0 0 8px 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.rr-perfil-header__subtitle {
    font-size: 1rem;
    color: #94a3b8;
    margin: 0;
}

/* ============================================
   SUBMENU - PADRÃO REI DO RODEIO
   ============================================ */
.rr-perfil-topbar {
    padding: 0.3rem 0 1rem;
}

.rr-perfil-submenu {
    display: flex;
    gap: 0;
    position: relative;
    overflow: visible;
    justify-content: center;
    padding: 0.8rem 0 1rem;
    flex-wrap: nowrap;
}

.rr-subcard {
    position: relative;
    width: 90px;
    height: 130px;
    background: linear-gradient(135deg, #0a0e1a 0%, #111827 50%, #0a0e1a 100%);
    border-radius: 12px;
    box-shadow: -0.5rem 0 1.5rem rgba(0,0,0,0.5), 0 4px 20px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.05);
    transition: 0.35s ease-out;
    left: 0;
    padding: .5rem;
    color: #e2e8f0;
    border: 1px solid rgba(255,255,255,0.05);
    flex: 0 0 auto;
    backdrop-filter: blur(10px);
    cursor: pointer;
    user-select: none;
}

.rr-subcard { margin-left: -20px; }
.rr-subcard:first-child { margin-left: 0; }

/* Z-index decrescente: cada card fica atrás do anterior */
.rr-subcard:nth-child(1) { z-index: 3; }
.rr-subcard:nth-child(2) { z-index: 2; }
.rr-subcard:nth-child(3) { z-index: 1; }

.rr-subcard:hover {
    transform: translateY(-10px);
    z-index: 10 !important;
}

.rr-subcard:hover ~ .rr-subcard { transform: translateX(20px); }

.rr-subcard--active {
    transform: translateY(-10px);
    z-index: 10 !important;
}

.rr-subcard--active ~ .rr-subcard { transform: translateX(20px); }

.rr-subcard__title {
    color: #fff;
    font-weight: 800;
    font-size: .7rem;
    margin-bottom: 0;
    text-align: center;
}

.rr-subcard__meta {
    display: block;
    font-size: .5rem;
    margin-bottom: .15rem;
    text-align: center;
}

.rr-subcard__bar {
    position: relative;
    height: 4px;
    width: 60%;
    margin-top: .2rem;
    z-index: 2;
    margin-left: auto;
    margin-right: auto;
}

.rr-subcard__bar-empty {
    display: block;
    width: 100%;
    height: 100%;
    background: #2e3033;
    border-radius: 2px;
}

.rr-subcard__bar-fill {
    position: absolute;
    inset: 0;
    width: 0;
    background: linear-gradient(90deg, #f97316 0%, #fb923c 45%, #fdba74 100%);
    transition: width 0.4s ease-out;
    border-radius: 2px;
}

.rr-subcard:hover .rr-subcard__bar-fill,
.rr-subcard--active .rr-subcard__bar-fill { width: 78%; }

.rr-subcard__circle {
    position: absolute;
    left: 50%;
    bottom: .6rem;
    transform: translateX(-50%);
    width: 50px;
    height: 50px;
    z-index: 1;
}

.rr-subcard__count {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    color: #f8fafc;
    font-weight: 700;
    font-size: 1.25rem;
    pointer-events: none;
}

.rr-subcard__count i {
    color: #f97316; /* accent */
}

.rr-subcard__stroke {
    fill: none;
    stroke: #f97316;
    stroke-width: 2;
    stroke-dasharray: 314;
    stroke-dashoffset: 314;
    transition: stroke-dashoffset 0.6s ease;
    opacity: 0.2;
}

.rr-subcard--active .rr-subcard__stroke {
    stroke-dashoffset: 0;
    opacity: 0.8;
}

/* Desktop/web: slightly bigger submenu */
@media (min-width: 769px) {
    .rr-subcard {
        width: 105px;
        height: 150px;
        padding: .6rem;
    }
    .rr-subcard { margin-left: -22px; }
    .rr-subcard:hover ~ .rr-subcard { transform: translateX(22px); }
    .rr-subcard--active ~ .rr-subcard { transform: translateX(22px); }
    .rr-subcard__title { font-size: .78rem; }
    .rr-subcard__meta { font-size: .55rem; }
    .rr-subcard__circle { width: 58px; height: 58px; }
    .rr-subcard__count { font-size: 1.4rem; }
}

@media (max-width: 576px) {
    .rr-perfil-submenu {
        justify-content: flex-start;
        padding-left: 20px;
    }
}

.rr-perfil-subcard--active .rr-perfil-subcard__icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Grid Layout */
.rr-perfil-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.rr-perfil-grid--single {
    grid-template-columns: 1fr;
    width: 100%;
    max-width: 100%;
    margin: 0;
}

.rr-perfil-grid--three {
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 1200px) {
    .rr-perfil-grid--three {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 992px) {
    .rr-perfil-grid {
        grid-template-columns: 1fr;
    }

    .rr-perfil-grid--single {
        max-width: 100%;
    }
}

.rr-perfil-section {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* Cards */
.rr-perfil-card {
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(249, 115, 22, 0.2);
    border-radius: 16px;
    padding: 24px;
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.rr-perfil-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(249, 115, 22, 0.1);
    margin-bottom: 20px;
}

.rr-perfil-card__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.rr-perfil-card__title i {
    color: #f97316; /* accent */
}

.rr-perfil-card__badge {
    padding: 6px 12px;
    background: rgba(249, 115, 22, 0.15);
    border: 1px solid #f97316;
    border-radius: 20px;
    font-size: 0.813rem;
    font-weight: 600;
    color: #f97316; /* accent */
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Alertas */
.rr-perfil-alert {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(249, 115, 22, 0.05));
    border: 1px solid rgba(249, 115, 22, 0.3);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.875rem;
    color: #e2e8f0;
}

.rr-perfil-alert i {
    color: #f97316; /* accent */
    flex-shrink: 0;
}

.rr-perfil-alert--info {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
    border-color: rgba(59, 130, 246, 0.3);
}

.rr-perfil-alert--info i {
    color: #3b82f6;
}

.rr-perfil-alert--success {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
    border-color: rgba(34, 197, 94, 0.3);
}

.rr-perfil-alert--success i {
    color: #22c55e;
}

/* Foto de Perfil */
.rr-perfil-photo {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid rgba(249, 115, 22, 0.1);
}

.rr-perfil-photo__preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #f97316;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    flex-shrink: 0;
}

.rr-perfil-photo__preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rr-perfil-photo__placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(249, 115, 22, 0.05));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: #f97316; /* accent */
}

.rr-perfil-photo__controls {
    flex: 1;
}

.rr-perfil-photo__btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: rgba(249, 115, 22, 0.15);
    border: 1px solid #f97316;
    border-radius: 8px;
    color: #f97316; /* accent */
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.rr-perfil-photo__btn:hover {
    background: rgba(249, 115, 22, 0.25);
    transform: translateY(-1px);
}

.rr-perfil-photo__help {
    display: block;
    margin-top: 8px;
    color: #64748b;
    font-size: 0.75rem;
}

/* Campos do Formulário */
.rr-perfil-fields {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.rr-perfil-field-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

@media (max-width: 768px) {
    .rr-perfil-field-group {
        grid-template-columns: 1fr;
    }
}

.rr-perfil-field {
    display: flex;
    flex-direction: column;
}

.rr-perfil-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 8px;
}

.rr-perfil-input,
.rr-perfil-select {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(249, 115, 22, 0.2);
    border-radius: 8px;
    padding: 12px 16px;
    color: #e2e8f0;
    font-size: 0.938rem;
    transition: all 0.2s;
    font-family: inherit;
}

.rr-perfil-input::placeholder {
    color: #64748b;
}

.rr-perfil-input:focus,
.rr-perfil-input:focus-visible {
    outline: none;
    border-color: #f97316; /* accent */
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

.rr-perfil-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Username Feedback */
.rr-username-feedback {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.813rem;
    font-weight: 600;
    margin-top: 8px;
}

.rr-username-feedback.checking {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #60a5fa;
}

.rr-username-feedback.checking i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.rr-username-feedback.available {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}

.rr-username-feedback.unavailable {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

/* Premium CTA (Não Premium) */
.rr-perfil-premium-cta {
    margin: 12px 0 16px;
    padding: 16px;
    background: linear-gradient(135deg,
        rgba(37, 99, 235, 0.15) 0%,
        rgba(59, 130, 246, 0.15) 50%,
        rgba(37, 99, 235, 0.1) 100%);
    border: 2px solid;
    border-image: linear-gradient(135deg, #2563eb, #3b82f6, #2563eb) 1;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    position: relative;
    overflow: hidden;
    animation: premiumGlow 3s ease-in-out infinite;
}

@keyframes premiumGlow {
    0%, 100% {
        box-shadow: 0 0 15px rgba(37, 99, 235, 0.3), 0 0 30px rgba(59, 130, 246, 0.2);
    }
    50% {
        box-shadow: 0 0 25px rgba(37, 99, 235, 0.5), 0 0 40px rgba(59, 130, 246, 0.3);
    }
}

.rr-perfil-premium-cta::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent,
        rgba(37, 99, 235, 0.1),
        transparent
    );
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.rr-perfil-premium-cta__content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    position: relative;
    z-index: 1;
}

.rr-perfil-premium-cta__content i {
    font-size: 1.5rem;
    color: #fbbf24;
    animation: float 3s ease-in-out infinite;
    filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.6));
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(-10deg);
    }
    50% {
        transform: translateY(-8px) rotate(10deg);
    }
}

.rr-perfil-premium-cta__content span {
    font-size: 0.938rem;
    color: #e2e8f0;
    line-height: 1.4;
}

.rr-perfil-premium-cta__content strong {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
}

.rr-perfil-premium-cta__btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
}

.rr-perfil-premium-cta__btn:hover {
    background: linear-gradient(135deg, #1d4ed8, #6366f1);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
}

.rr-perfil-premium-cta__btn i {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Premium Notice (Premium Ativo) */
.rr-perfil-premium-notice {
    margin: 12px 0 16px;
    padding: 16px;
    background: linear-gradient(135deg,
        rgba(37, 99, 235, 0.2) 0%,
        rgba(59, 130, 246, 0.15) 100%);
    border: 2px solid;
    border-image: linear-gradient(135deg, #2563eb, #3b82f6) 1;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(37, 99, 235, 0.4), inset 0 0 20px rgba(37, 99, 235, 0.1);
}

.rr-perfil-premium-notice::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent
    );
    animation: slideRight 3s infinite;
}

@keyframes slideRight {
    0% { left: -100%; }
    100% { left: 100%; }
}

.rr-perfil-premium-notice__content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    position: relative;
    z-index: 1;
}

.rr-perfil-premium-notice__content i {
    font-size: 1.5rem;
    color: #fbbf24;
    animation: bounce 2s ease-in-out infinite;
    filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.8));
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    25% {
        transform: translateY(-5px) scale(1.1);
    }
    50% {
        transform: translateY(0) rotate(15deg);
    }
    75% {
        transform: translateY(-3px) rotate(-15deg);
    }
}

.rr-perfil-premium-notice__content span {
    font-size: 0.938rem;
    color: #e2e8f0;
    line-height: 1.4;
}

.rr-perfil-premium-notice__content strong {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 1.05em;
}

.rr-perfil-premium-notice__btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.rr-perfil-premium-notice__btn:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
}

.rr-perfil-label__lock {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
    margin-left: 8px;
}

.rr-perfil-label__lock i {
    font-size: 0.813rem;
}

.rr-perfil-field__help {
    display: block;
    margin-top: 6px;
    font-size: 0.75rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}

.rr-perfil-field__help i {
    color: #3b82f6;
}

/* Responsividade */
@media (max-width: 768px) {
    .rr-perfil-premium-cta,
    .rr-perfil-premium-notice {
        flex-direction: column;
        text-align: center;
    }

    .rr-perfil-premium-cta__content,
    .rr-perfil-premium-notice__content {
        flex-direction: column;
    }

    .rr-perfil-premium-cta__btn,
    .rr-perfil-premium-notice__btn {
        width: 100%;
        justify-content: center;
    }
}

/* Botões */
.rr-perfil-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.938rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: inherit;
}

.rr-perfil-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.rr-perfil-btn--primary {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    width: 100%;
}

.rr-perfil-btn--primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #ea580c, #dc2626);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
}

.rr-perfil-btn--secondary {
    background: rgba(249, 115, 22, 0.15);
    border: 1px solid #f97316;
    color: #f97316; /* accent */
}

.rr-perfil-btn--secondary:hover:not(:disabled) {
    background: rgba(249, 115, 22, 0.25);
    transform: translateY(-1px);
}

.rr-perfil-btn--success {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
}

.rr-perfil-btn--success:hover:not(:disabled) {
    background: linear-gradient(135deg, #16a34a, #15803d);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
}

.rr-perfil-btn--danger {
    background: rgba(239, 68, 68, 0.16);
    border: 1px solid rgba(239, 68, 68, 0.45);
    color: #fff;
}

.rr-perfil-btn--danger:hover:not(:disabled) {
    background: rgba(239, 68, 68, 0.28);
    border-color: rgba(239, 68, 68, 0.65);
    color: #fff;
}

.rr-perfil-btn--danger,
.rr-perfil-btn--danger i,
.rr-perfil-btn--danger:hover:not(:disabled),
.rr-perfil-btn--danger:hover:not(:disabled) i,
.rr-perfil-btn--danger:disabled,
.rr-perfil-btn--danger:disabled i {
    color: #fff;
}

.rr-perfil-btn--tiny {
    width: auto;
    max-width: 220px;
    min-height: 34px;
    padding: 8px 12px;
    font-size: 0.78rem;
    margin: 0 0 10px;
}

/* Feedback de validação de input */
.rr-input-feedback {
    display: block;
    font-size: 12px;
    margin-top: 4px;
    color: #94a3b8;
}

.rr-input-feedback--success {
    color: #22c55e;
}

.rr-input-feedback--error {
    color: #ef4444;
}

/* Carteira (Ganhos) */
.rr-carteira-saldo {
    text-align: center;
    padding: 24px;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 12px;
    margin-bottom: 20px;
}

.rr-carteira-saldo__label {
    font-size: 0.875rem;
    color: #94a3b8;
    margin-bottom: 8px;
    font-weight: 500;
}

.rr-carteira-saldo__value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #22c55e;
    margin-bottom: 16px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.rr-carteira-saldo__stats {
    display: flex;
    gap: 24px;
    justify-content: center;
    flex-wrap: wrap;
}

.rr-carteira-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.938rem;
    color: #e2e8f0;
    font-weight: 600;
}

.rr-carteira-stat i {
    font-size: 1.125rem;
}

/* Placeholder para funcionalidades futuras */
.rr-perfil-placeholder {
    text-align: center;
    padding: 60px 40px;
    color: #94a3b8;
}

.rr-perfil-placeholder i {
    font-size: 3rem;
    color: #f97316; /* accent */
    margin-bottom: 16px;
    display: block;
    opacity: 0.5;
}

.rr-perfil-placeholder h4 {
    font-size: 1.25rem;
    color: #e2e8f0;
    margin: 0 0 8px 0;
    font-weight: 700;
}

.rr-perfil-placeholder p {
    font-size: 0.938rem;
    margin: 0;
    line-height: 1.6;
}

/* Histórico de Vitórias */
.rr-history-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 8px;
}

.rr-history-list::-webkit-scrollbar {
    width: 6px;
}

.rr-history-list::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 3px;
}

.rr-history-list::-webkit-scrollbar-thumb {
    background: rgba(249, 115, 22, 0.5);
    border-radius: 3px;
}

.rr-history-list::-webkit-scrollbar-thumb:hover {
    background: rgba(249, 115, 22, 0.7);
}

.rr-history-item {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(249, 115, 22, 0.2);
    border-radius: 10px;
    padding: 14px;
    transition: all 0.3s ease;
}

.rr-history-item:hover {
    border-color: rgba(249, 115, 22, 0.5);
    transform: translateX(4px);
    background: rgba(15, 23, 42, 0.8);
}

.rr-history-item__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.rr-history-item__title {
    font-size: 0.938rem;
    font-weight: 700;
    color: #e2e8f0;
}

.rr-history-item__prize {
    font-size: 1.125rem;
    font-weight: 700;
    color: #22c55e;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.rr-history-item__meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.813rem;
    color: #94a3b8;
    gap: 12px;
}

.rr-history-item__meta i {
    margin-right: 4px;
    color: #f97316; /* accent */
}

.rr-history-item__badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.rr-history-item__badge--won {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.rr-history-item__badge--won i {
    color: #22c55e;
    margin-right: 0;
}

/* Mobile Adjustments */
@media (max-width: 768px) {

    .rr-perfil-header__title {
        font-size: 1.5rem;
    }

    .rr-perfil-photo {
        flex-direction: column;
        text-align: center;
    }

    .rr-perfil-submenu {
        gap: 8px;
    }

    .rr-perfil-subcard {
        min-width: 120px;
    }

    .rr-carteira-saldo__value {
        font-size: 2rem;
    }
}

/* ============================================
   ESTILOS PREMIUM (Roxo/Azul)
   ============================================ */

.rr-perfil-container--premium .rr-perfil-card {
    background: linear-gradient(145deg, rgba(30, 27, 50, 0.95), rgba(22, 40, 62, 0.95));
    border: 2px solid rgba(37, 99, 235, 0.4);
    box-shadow: 0 0 20px rgba(37, 99, 235, 0.3), 0 8px 30px rgba(0, 0, 0, 0.3);
}

.rr-perfil-container--premium .rr-perfil-card__header {
    background: linear-gradient(to right, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.1));
    border-bottom: 1px solid rgba(37, 99, 235, 0.3);
}

.rr-perfil-container--premium .rr-perfil-card__title i {
    color: #3b82f6;
}

.rr-perfil-container--premium .rr-perfil-photo__preview {
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.5);
}

.rr-perfil-container--premium .rr-perfil-photo__placeholder {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), rgba(37, 99, 235, 0.05));
    color: #2563eb;
}

.rr-perfil-container--premium .rr-perfil-photo__btn {
    background: rgba(37, 99, 235, 0.15);
    border-color: #2563eb;
    color: #2563eb;
}

.rr-perfil-container--premium .rr-perfil-photo__btn:hover {
    background: rgba(37, 99, 235, 0.25);
}

.rr-perfil-container--premium .rr-perfil-input:focus,
.rr-perfil-container--premium .rr-perfil-input:focus-visible {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.rr-perfil-container--premium .rr-perfil-btn--primary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.rr-perfil-container--premium .rr-perfil-btn--primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #1d4ed8, #6d28d9);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
}

.rr-perfil-container--premium .rr-perfil-btn--success {
    background: linear-gradient(135deg, #60a5fa, #3b82f6);
    box-shadow: 0 4px 12px rgba(96, 165, 250, 0.3);
}

.rr-perfil-container--premium .rr-perfil-btn--success:hover:not(:disabled) {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    box-shadow: 0 6px 20px rgba(96, 165, 250, 0.5);
}

.rr-perfil-container--premium .rr-perfil-alert {
    background: linear-gradient(135deg, rgba(96, 165, 250, 0.1), rgba(59, 130, 246, 0.05));
    border-color: rgba(96, 165, 250, 0.3);
}

.rr-perfil-container--premium .rr-perfil-alert i {
    color: #60a5fa;
}

.rr-perfil-container--premium .rr-perfil-subcard {
    border-color: rgba(37, 99, 235, 0.15);
}

.rr-perfil-container--premium .rr-perfil-subcard:hover {
    border-color: rgba(37, 99, 235, 0.4);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.2);
}

.rr-perfil-container--premium .rr-perfil-subcard--active {
    border-color: #2563eb;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(37, 99, 235, 0.05));
    box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
}

.rr-perfil-container--premium .rr-perfil-subcard__bar-empty {
    background: rgba(37, 99, 235, 0.2);
}

.rr-perfil-container--premium .rr-perfil-subcard__bar-fill {
    background: linear-gradient(90deg, #2563eb, #1d4ed8);
}

.rr-perfil-container--premium .rr-perfil-subcard__stroke {
    stroke: #2563eb;
}

.rr-perfil-container--premium .rr-perfil-subcard__icon {
    color: #3b82f6;
}

.rr-perfil-container--premium .rr-perfil-placeholder i {
    color: #2563eb;
}

.rr-perfil-container {
    display: flex;
    flex-direction: column;
    gap: 14px;
    overflow: visible;
}

.rr-perfil-container > #rrPerfilSubmenu {
    order: -1;
    position: sticky;
    top: calc(var(--hub-navbar-offset, var(--hub-navbar-height, 96px)) + 10px);
    z-index: 60;
    overflow: visible;
}

.rr-affiliate-stage {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1.02fr) minmax(300px, 0.98fr);
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1.2rem;
    border-radius: 30px;
    border: 1px solid rgba(16, 185, 129, 0.18);
    background:
        radial-gradient(circle at top left, rgba(16, 185, 129, 0.18), transparent 28%),
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.18), transparent 34%),
        linear-gradient(160deg, rgba(7, 16, 26, 0.96), rgba(5, 20, 17, 0.98));
    box-shadow: 0 24px 48px rgba(2, 6, 23, 0.26), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.rr-affiliate-stage::before {
    content: "";
    position: absolute;
    inset: auto -10% -30% auto;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.16), rgba(16, 185, 129, 0));
    pointer-events: none;
}

.rr-affiliate-stage__copy,
.rr-affiliate-stage__visual {
    position: relative;
    z-index: 1;
}

.rr-affiliate-stage__copy {
    display: grid;
    align-content: center;
    gap: 0.95rem;
}

.rr-affiliate-stage__kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    width: fit-content;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    border: 1px solid rgba(16, 185, 129, 0.18);
    background: rgba(255, 255, 255, 0.06);
    color: #d1fae5;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.rr-affiliate-stage__title {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3.5rem);
    line-height: 0.95;
    letter-spacing: -0.05em;
    color: #f8fafc;
}

.rr-affiliate-stage__title span {
    display: block;
    color: #86efac;
}

.rr-affiliate-stage__lead {
    margin: 0;
    max-width: 54ch;
    color: #cbd5e1;
    line-height: 1.7;
}

.rr-affiliate-stage__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.7rem;
}

.rr-affiliate-stage__chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.05);
    color: #f8fafc;
    font-size: 0.82rem;
    font-weight: 700;
}

.rr-affiliate-stage__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
}

.rr-finance-shell {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}

.rr-finance-shell__hero {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
    gap: 1rem;
    padding: 1.25rem;
    border-radius: 30px;
    border: 1px solid rgba(96, 165, 250, 0.18);
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.18), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.14), transparent 28%),
        linear-gradient(160deg, rgba(7, 16, 26, 0.96), rgba(19, 10, 5, 0.98));
    box-shadow: 0 24px 46px rgba(2, 6, 23, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.rr-finance-shell__copy,
.rr-finance-shell__spotlight {
    position: relative;
    z-index: 1;
}

.rr-finance-shell__copy {
    display: grid;
    align-content: center;
    gap: 0.95rem;
}

.rr-finance-shell__eyebrow,
.rr-finance-panel__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    width: fit-content;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    border: 1px solid rgba(96, 165, 250, 0.16);
    background: rgba(255, 255, 255, 0.06);
    color: #dbeafe;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.rr-finance-shell__title {
    margin: 0;
    max-width: 14ch;
    font-size: clamp(2rem, 4vw, 3.3rem);
    line-height: 0.95;
    letter-spacing: -0.05em;
    color: #f8fafc;
}

.rr-finance-shell__lead {
    margin: 0;
    max-width: 58ch;
    color: #cbd5e1;
    line-height: 1.7;
}

.rr-finance-shell__pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.7rem;
}

.rr-finance-shell__pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.05);
    color: #f8fafc;
    font-size: 0.82rem;
    font-weight: 700;
}

.rr-finance-shell__spotlight {
    display: grid;
    align-content: center;
}

.rr-finance-shell__spotlight-card {
    display: grid;
    gap: 0.5rem;
    padding: 1.15rem;
    border-radius: 26px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: linear-gradient(160deg, rgba(255, 255, 255, 0.08), rgba(15, 23, 42, 0.22));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.rr-finance-shell__spotlight-label,
.rr-finance-metric-card__label {
    color: #94a3b8;
    font-size: 0.74rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.rr-finance-shell__spotlight-value,
.rr-finance-metric-card__value {
    color: #fff8f2;
    font-size: clamp(1.35rem, 2vw, 2.2rem);
    line-height: 1.02;
    font-weight: 900;
}

.rr-finance-shell__spotlight-text,
.rr-finance-metric-card__text {
    margin: 0;
    color: #cbd5e1;
    font-size: 0.88rem;
    line-height: 1.58;
}

.rr-finance-shell__metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.9rem;
}

.rr-finance-metric-card {
    display: grid;
    gap: 0.55rem;
    padding: 1rem;
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.07);
    background: linear-gradient(180deg, rgba(16, 23, 38, 0.98), rgba(12, 9, 18, 0.98));
    box-shadow: 0 18px 34px rgba(2, 6, 23, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-finance-metric-card--balance {
    border-color: rgba(251, 146, 60, 0.18);
}

.rr-finance-metric-card--pending {
    border-color: rgba(250, 204, 21, 0.2);
}

.rr-finance-metric-card--activity {
    border-color: rgba(96, 165, 250, 0.2);
}

.rr-finance-panel {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}

.rr-finance-panel__header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 1rem;
}

.rr-finance-panel__title {
    margin: 0.6rem 0 0;
    color: #f8fafc;
    font-size: clamp(1.4rem, 3vw, 2rem);
    line-height: 1.05;
    letter-spacing: -0.03em;
}

.rr-finance-panel__summary {
    margin: 0;
    max-width: 40ch;
    color: #94a3b8;
    font-size: 0.92rem;
    line-height: 1.6;
    text-align: right;
}

.rr-finance-panel__card {
    border-color: rgba(96, 165, 250, 0.12);
}

.rr-finance-stage {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(320px, 0.94fr);
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1.2rem;
    border-radius: 30px;
    border: 1px solid rgba(96, 165, 250, 0.16);
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.18), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.14), transparent 28%),
        linear-gradient(160deg, rgba(7, 16, 26, 0.96), rgba(19, 10, 5, 0.98));
    box-shadow: 0 24px 46px rgba(2, 6, 23, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.rr-finance-stage__copy,
.rr-finance-stage__visual {
    position: relative;
    z-index: 1;
}

.rr-finance-stage__copy {
    display: grid;
    align-content: center;
    gap: 0.95rem;
}

.rr-finance-stage__kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    width: fit-content;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    border: 1px solid rgba(96, 165, 250, 0.16);
    background: rgba(255, 255, 255, 0.06);
    color: #dbeafe;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.rr-finance-stage__title {
    margin: 0;
    font-size: clamp(2rem, 4vw, 3.4rem);
    line-height: 0.95;
    letter-spacing: -0.05em;
    color: #f8fafc;
}

.rr-finance-stage__title span {
    display: block;
    color: #93c5fd;
}

.rr-finance-stage__lead {
    margin: 0;
    max-width: 60ch;
    color: #cbd5e1;
    line-height: 1.72;
}

.rr-finance-stage__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.7rem;
}

.rr-finance-stage__chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.05);
    color: #f8fafc;
    font-size: 0.82rem;
    font-weight: 700;
}

.rr-finance-stage__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
}

.rr-finance-stage__btn {
    position: relative;
    overflow: hidden;
    border-width: 1px;
    border-style: solid;
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.22);
}

.rr-finance-stage__btn::before {
    content: "";
    position: absolute;
    inset: 1px;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0));
    opacity: 0.9;
    pointer-events: none;
}

.rr-finance-stage__btn > * {
    position: relative;
    z-index: 1;
}

.rr-finance-stage__btn--premios {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.98), rgba(245, 158, 11, 0.92));
    color: #fffaf3;
    border-color: rgba(251, 191, 36, 0.48);
    box-shadow: 0 18px 32px rgba(249, 115, 22, 0.26), inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

.rr-finance-stage__btn--premios i {
    color: #fff4d4;
}

.rr-finance-stage__btn--arena {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.96), rgba(59, 130, 246, 0.88));
    color: #eff6ff;
    border-color: rgba(96, 165, 250, 0.44);
    box-shadow: 0 18px 32px rgba(37, 99, 235, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.16);
}

.rr-finance-stage__btn--arena i {
    color: #dbeafe;
}

.rr-finance-stage__visual {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
    align-content: center;
}

.rr-finance-stage__card {
    padding: 1rem;
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.05);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
}

.rr-finance-stage__card span {
    display: block;
    color: #94a3b8;
    font-size: 0.72rem;
    font-weight: 900;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.rr-finance-stage__card strong {
    display: block;
    margin-top: 0.45rem;
    color: #fff8f2;
    font-size: 1.18rem;
    font-weight: 900;
    line-height: 1.08;
}

.rr-finance-stage__card p {
    margin: 0.55rem 0 0;
    color: #cbd5e1;
    font-size: 0.84rem;
    line-height: 1.55;
}

.rr-finance-stage__card--accent {
    background: linear-gradient(160deg, rgba(249, 115, 22, 0.14), rgba(37, 99, 235, 0.14));
    border-color: rgba(96, 165, 250, 0.18);
}

.rr-affiliate-stage__link {
    display: grid;
    gap: 0.35rem;
    padding: 0.95rem 1rem;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.04);
}

.rr-affiliate-stage__link span {
    color: #86efac;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.rr-affiliate-stage__link strong {
    color: #e2e8f0;
    font-size: 0.9rem;
    line-height: 1.55;
    word-break: break-word;
}

.rr-affiliate-stage__progress {
    display: grid;
    gap: 0.55rem;
    margin-top: 0.4rem;
}

.rr-affiliate-stage__progress .rr-affiliate-progress-container {
    position: relative;
    display: flex;
    align-items: center;
    height: 36px;
    border-radius: 16px;
    background: rgba(15, 23, 42, 0.72);
    border-color: rgba(59, 130, 246, 0.18);
    overflow: hidden;
}

.rr-affiliate-stage__progress .rr-affiliate-progress-bar {
    min-width: 0 !important;
    height: 100%;
    border-radius: 16px;
    background: linear-gradient(90deg, #10b981 0%, #22c55e 40%, #3b82f6 100%);
    box-shadow: 0 8px 18px rgba(16, 185, 129, 0.22);
}

.rr-affiliate-stage__progress .rr-affiliate-progress-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 0 14px;
    color: #d1fae5;
    font-size: 0.83rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    text-shadow: none;
    pointer-events: none;
    z-index: 1;
}

.rr-affiliate-stage__progress-meta {
    color: #94a3b8;
    font-size: 0.8rem;
    line-height: 1.45;
}

.rr-affiliate-stage__visual {
    min-height: 320px;
    display: grid;
    place-items: center;
}

.rr-affiliate-stage__logo-wrap {
    position: relative;
    width: min(340px, 100%);
    aspect-ratio: 1 / 1;
    display: grid;
    place-items: center;
    animation: rrAffiliateLogoFloat 5.6s ease-in-out infinite;
}

.rr-affiliate-stage__logo-wrap::before,
.rr-affiliate-stage__logo-wrap::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 50%;
    pointer-events: none;
}

.rr-affiliate-stage__logo-wrap::before {
    background: radial-gradient(circle, rgba(16, 185, 129, 0.24), rgba(16, 185, 129, 0));
    filter: blur(16px);
    animation: rrAffiliateHaloPulse 3.2s ease-in-out infinite;
}

.rr-affiliate-stage__logo-wrap::after {
    inset: 14%;
    border: 1px solid rgba(16, 185, 129, 0.24);
    animation: rrAffiliateRingDrift 12s linear infinite;
}

.rr-affiliate-stage__logo {
    position: relative;
    z-index: 1;
    width: min(240px, 72%);
    height: auto;
    object-fit: contain;
    filter: drop-shadow(0 22px 30px rgba(0, 0, 0, 0.35));
}

.rr-affiliate-stage__logo-badge {
    position: absolute;
    top: 0.35rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1rem;
    border-radius: 999px;
    background: rgba(255, 250, 244, 0.9);
    color: #059669;
    font-size: 0.8rem;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    box-shadow: 0 14px 28px rgba(5, 150, 105, 0.18);
}

.rr-affiliate-stage__floaters {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.rr-affiliate-floater {
    position: absolute;
    display: grid;
    gap: 0.2rem;
    min-width: 172px;
    max-width: 220px;
    padding: 0.9rem 1rem;
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.86);
    color: #0f172a;
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
    animation: rrAffiliateCardFloat 4.6s ease-in-out infinite;
}

.rr-affiliate-floater i {
    color: #059669;
    font-size: 0.95rem;
}

.rr-affiliate-floater strong {
    font-size: 1rem;
    line-height: 1.15;
}

.rr-affiliate-floater span {
    color: #475569;
    font-size: 0.76rem;
    line-height: 1.45;
}

.rr-affiliate-floater--share {
    top: 1.6rem;
    right: -0.5rem;
    transform: rotate(6deg);
}

.rr-affiliate-floater--level {
    left: 0.35rem;
    bottom: 1.4rem;
    transform: rotate(-6deg);
    animation-delay: 0.5s;
}

.rr-affiliate-floater--cash {
    right: 1rem;
    bottom: 0.9rem;
    transform: rotate(4deg);
    animation-delay: 1.1s;
}

@keyframes rrAffiliateLogoFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes rrAffiliateHaloPulse {
    0%, 100% { transform: scale(1); opacity: 0.76; }
    50% { transform: scale(1.06); opacity: 1; }
}

@keyframes rrAffiliateRingDrift {
    to { transform: rotate(360deg); }
}

@keyframes rrAffiliateCardFloat {
    0%, 100% { transform: translateY(0) rotate(var(--rr-affiliate-rotation, 0deg)); }
    50% { transform: translateY(-8px) rotate(var(--rr-affiliate-rotation, 0deg)); }
}

.rr-affiliate-floater--share { --rr-affiliate-rotation: 6deg; }
.rr-affiliate-floater--level { --rr-affiliate-rotation: -6deg; }
.rr-affiliate-floater--cash { --rr-affiliate-rotation: 4deg; }

body.light .rr-affiliate-stage {
    border-color: rgba(5, 150, 105, 0.14);
    background:
        radial-gradient(circle at top left, rgba(16, 185, 129, 0.12), transparent 28%),
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 34%),
        linear-gradient(160deg, rgba(255, 252, 248, 0.98), rgba(243, 255, 249, 0.98));
    box-shadow: 0 18px 34px rgba(148, 163, 184, 0.12);
}

body.light .rr-affiliate-stage__kicker,
body.light .rr-affiliate-stage__chip,
body.light .rr-affiliate-stage__link {
    background: rgba(255, 255, 255, 0.82);
    border-color: rgba(5, 150, 105, 0.12);
}

body.light .rr-affiliate-stage__kicker {
    color: #047857;
}

body.light .rr-affiliate-stage__title,
body.light .rr-affiliate-stage__lead,
body.light .rr-affiliate-stage__chip,
body.light .rr-affiliate-stage__link strong {
    color: #172033;
}

body.light .rr-affiliate-stage__title span,
body.light .rr-affiliate-stage__link span {
    color: #047857;
}

body.light .rr-affiliate-stage__progress-meta {
    color: #475569;
}

body.light .rr-affiliate-stage__logo-badge {
    background: rgba(255, 255, 255, 0.94);
    color: #047857;
}

body.light .rr-affiliate-floater {
    background: rgba(255, 255, 255, 0.94);
}

@media (max-width: 768px) {
    #rrPerfilSubmenu {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 0 12px !important;
        position: relative !important;
        top: auto !important;
        z-index: 1 !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__track {
        padding: 6px !important;
        border-radius: 24px !important;
        border-width: 1px !important;
        gap: 4px !important;
        background:
            radial-gradient(120% 160% at 50% 0%, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 56%),
            linear-gradient(160deg, rgba(27, 13, 8, 0.96), rgba(12, 10, 19, 0.98)) !important;
        box-shadow: 0 18px 28px rgba(2, 6, 23, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn {
        min-height: 70px !important;
        padding: 10px 6px 9px !important;
        gap: 5px !important;
        border-radius: 18px !important;
        color: rgba(255, 255, 255, 0.9) !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
        width: 36px !important;
        height: 36px !important;
        border-radius: 14px !important;
        background: rgba(255, 255, 255, 0.08) !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
        max-width: none !important;
        font-size: 10px !important;
        letter-spacing: 0.06em !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta {
        display: none !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
    #rrPerfilSubmenu .rr-epic-submenu__btn.active,
    #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
        background: transparent !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__track {
        background:
            radial-gradient(120% 160% at 50% 0%, rgba(255, 255, 255, 0.75) 0%, rgba(255, 255, 255, 0) 56%),
            linear-gradient(160deg, rgba(255, 251, 246, 0.98), rgba(255, 239, 221, 0.98)) !important;
        border-color: rgba(251, 146, 60, 0.18) !important;
        box-shadow:
            0 14px 28px rgba(148, 163, 184, 0.14),
            inset 0 1px 0 rgba(255, 255, 255, 0.94) !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__btn {
        color: #1f2a44 !important;
        background: rgba(255, 255, 255, 0.88) !important;
        border: 1px solid rgba(251, 146, 60, 0.12) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.96),
            0 8px 18px rgba(148, 163, 184, 0.12) !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
        background: rgba(255, 255, 255, 0.84) !important;
        border: 1px solid rgba(251, 146, 60, 0.16) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.96),
            0 6px 14px rgba(148, 163, 184, 0.1) !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
        color: #1f2a44 !important;
        opacity: 1 !important;
        text-shadow: none !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta {
        color: #6b7280 !important;
        opacity: 0.95 !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon {
        color: #1f2a44 !important;
        opacity: 1 !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
    body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active,
    body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 245, 235, 0.96)) !important;
        border-color: rgba(59, 130, 246, 0.18) !important;
        box-shadow:
            inset 0 0 0 1px rgba(59, 130, 246, 0.14),
            0 12px 24px rgba(249, 115, 22, 0.12) !important;
    }

    .rr-affiliate-stage {
        grid-template-columns: 1fr;
        padding: 1rem;
        border-radius: 24px;
    }

    .rr-affiliate-stage__copy {
        order: 2;
    }

    .rr-affiliate-stage__visual {
        order: 1;
        min-height: 240px;
    }

    .rr-affiliate-stage__logo-wrap {
        width: min(250px, 100%);
    }

    .rr-affiliate-stage__logo {
        width: min(180px, 72%);
    }

    .rr-affiliate-stage__logo-badge {
        top: 0;
        font-size: 0.72rem;
        padding: 0.55rem 0.85rem;
    }

    .rr-affiliate-stage__chips {
        gap: 0.55rem;
    }

    .rr-affiliate-stage__chip {
        padding: 0.65rem 0.8rem;
        font-size: 0.76rem;
    }

    .rr-affiliate-stage__actions {
        display: grid;
    }

    .rr-affiliate-stage__actions .rr-perfil-hero__btn {
        width: 100%;
    }

    .rr-finance-stage {
        grid-template-columns: 1fr;
        padding: 1rem;
        border-radius: 24px;
    }

    .rr-finance-shell__hero {
        grid-template-columns: 1fr;
        padding: 1rem;
        border-radius: 24px;
    }

    .rr-finance-shell__title {
        max-width: none;
        font-size: clamp(1.8rem, 9vw, 2.8rem);
    }

    .rr-finance-shell__metrics {
        grid-template-columns: 1fr;
    }

    .rr-finance-panel__header {
        grid-template-columns: 1fr;
        display: grid;
        align-items: start;
    }

    .rr-finance-panel__summary {
        text-align: left;
        max-width: none;
    }

    .rr-finance-stage__visual {
        grid-template-columns: 1fr;
    }

    .rr-finance-stage__actions {
        display: grid;
    }

    .rr-finance-stage__actions .rr-perfil-hero__btn {
        width: 100%;
    }

    .rr-affiliate-floater {
        min-width: 138px;
        max-width: 160px;
        padding: 0.7rem 0.8rem;
        border-radius: 18px;
    }

    .rr-affiliate-floater strong {
        font-size: 0.84rem;
    }

    .rr-affiliate-floater span {
        font-size: 0.68rem;
    }

    .rr-affiliate-floater--share {
        top: 1.2rem;
        right: 0;
    }

    .rr-affiliate-floater--level {
        left: 0;
        bottom: 0.8rem;
    }

    .rr-affiliate-floater--cash {
        right: 0.5rem;
        bottom: 0.2rem;
    }
}

body.light #rrPerfilSubmenu .rr-epic-submenu__track {
    background:
        radial-gradient(120% 160% at 50% 0%, rgba(255, 255, 255, 0.88) 0%, rgba(255, 255, 255, 0) 58%),
        linear-gradient(160deg, rgba(255, 250, 244, 0.99), rgba(255, 238, 220, 0.99)) !important;
    border-color: rgba(251, 146, 60, 0.26) !important;
    box-shadow:
        0 18px 34px rgba(148, 163, 184, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.92) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn {
    color: #1f2a44 !important;
    background: rgba(255, 255, 255, 0.92) !important;
    border: 1px solid rgba(251, 146, 60, 0.14) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.96),
        0 10px 22px rgba(148, 163, 184, 0.1) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__label,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__label,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__label {
    color: #1f2a44 !important;
    opacity: 1 !important;
    text-shadow: none !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__meta,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__meta,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__meta {
    color: #5f6f86 !important;
    opacity: 0.96 !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__icon,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__icon {
    color: #1f2a44 !important;
    opacity: 1 !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 245, 235, 0.96)) !important;
    box-shadow:
        inset 0 0 0 1px rgba(59, 130, 246, 0.16),
        0 12px 24px rgba(249, 115, 22, 0.12) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__icon-wrap,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__icon-wrap {
    background: rgba(255, 255, 255, 0.94) !important;
    border-color: rgba(251, 146, 60, 0.16) !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.96),
        0 8px 20px rgba(148, 163, 184, 0.12) !important;
}

body.light .rr-perfil-card {
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 32%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.08), transparent 28%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(250, 245, 238, 0.98));
    border-color: rgba(59, 130, 246, 0.12);
    box-shadow: 0 18px 34px rgba(148, 163, 184, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.78);
}

body.light .rr-perfil-card__title {
    color: #172033;
}

body.light .rr-perfil-photo {
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 32%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 242, 236, 0.96));
    border-color: rgba(59, 130, 246, 0.14);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.88), 0 14px 24px rgba(148, 163, 184, 0.1);
}

body.light .rr-perfil-photo__btn {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(249, 115, 22, 0.1));
    border-color: rgba(37, 99, 235, 0.18);
    color: #2563eb;
}

body.light .rr-perfil-photo__help,
body.light .rr-perfil-field__help,
body.light .rr-input-hint,
body.light .rr-input-feedback,
body.light .rr-perfil-label__lock {
    color: #64748b;
}

body.light .rr-perfil-label {
    color: #475569;
}

body.light .rr-perfil-input,
body.light .rr-perfil-input[type="date"],
body.light .rr-perfil-input[type="email"],
body.light .rr-perfil-input[type="text"],
body.light .rr-perfil-input[type="number"],
body.light .rr-perfil-input[type="tel"],
body.light .rr-perfil-input[type="password"],
body.light select.rr-perfil-input,
body.light textarea.rr-perfil-input {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 242, 237, 0.98));
    border-color: rgba(59, 130, 246, 0.16);
    color: #172033;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.88);
}

body.light .rr-perfil-input:focus,
body.light select.rr-perfil-input:focus,
body.light textarea.rr-perfil-input:focus {
    background: #ffffff;
    border-color: rgba(37, 99, 235, 0.3);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

body.light .rr-perfil-input::placeholder {
    color: #94a3b8;
}

body.light .rr-perfil-premium-notice {
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.1), transparent 30%),
        linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(238, 244, 255, 0.96));
    border: 1px solid rgba(37, 99, 235, 0.2);
    box-shadow: 0 16px 28px rgba(148, 163, 184, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.82);
}

body.light .rr-perfil-premium-notice__content span {
    color: #64748b;
}

body.light .rr-perfil-premium-notice__content strong {
    background: none;
    -webkit-text-fill-color: initial;
    color: #2563eb;
}

body.light .rr-perfil-premium-notice__btn {
    box-shadow: 0 12px 22px rgba(37, 99, 235, 0.18);
}

body.light .rr-finance-stage {
    border-color: rgba(37, 99, 235, 0.12);
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.11), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.08), transparent 28%),
        linear-gradient(160deg, rgba(255, 252, 248, 0.98), rgba(248, 252, 255, 0.98));
    box-shadow: 0 18px 34px rgba(148, 163, 184, 0.14);
}

body.light .rr-finance-stage__kicker,
body.light .rr-finance-stage__chip,
body.light .rr-finance-stage__card {
    background: rgba(255, 255, 255, 0.84);
    border-color: rgba(37, 99, 235, 0.12);
}

body.light .rr-finance-stage__title,
body.light .rr-finance-stage__lead,
body.light .rr-finance-stage__chip,
body.light .rr-finance-stage__card strong,
body.light .rr-finance-stage__card p {
    color: #172033;
}

body.light .rr-finance-stage__title span,
body.light .rr-finance-stage__kicker {
    color: #1d4ed8;
}

body.light .rr-finance-stage__card span {
    color: #64748b;
}

body.light .rr-finance-stage__btn {
    border-color: rgba(255, 255, 255, 0.7);
}

body.light .rr-finance-stage__btn::before {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.34), rgba(255, 255, 255, 0.02));
}

body.light .rr-finance-stage__btn--premios {
    background: linear-gradient(135deg, #f97316, #f59e0b);
    color: #fffaf5;
    border-color: rgba(251, 191, 36, 0.78);
    box-shadow: 0 18px 28px rgba(249, 115, 22, 0.24);
}

body.light .rr-finance-stage__btn--premios i {
    color: #fff2cf;
}

body.light .rr-finance-stage__btn--arena {
    background: linear-gradient(135deg, #2563eb, #60a5fa);
    color: #f8fbff;
    border-color: rgba(147, 197, 253, 0.88);
    box-shadow: 0 18px 28px rgba(37, 99, 235, 0.22);
}

body.light .rr-finance-stage__btn--arena i {
    color: #eff6ff;
}

body.light .rr-finance-shell__hero {
    border-color: rgba(37, 99, 235, 0.12);
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.11), transparent 34%),
        radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.08), transparent 28%),
        linear-gradient(160deg, rgba(255, 252, 248, 0.98), rgba(248, 252, 255, 0.98));
    box-shadow: 0 18px 34px rgba(148, 163, 184, 0.14);
}

body.light .rr-finance-shell__eyebrow,
body.light .rr-finance-panel__eyebrow,
body.light .rr-finance-shell__pill,
body.light .rr-finance-shell__spotlight-card,
body.light .rr-finance-metric-card {
    background: rgba(255, 255, 255, 0.88);
    border-color: rgba(37, 99, 235, 0.12);
}

body.light .rr-finance-shell__title,
body.light .rr-finance-panel__title,
body.light .rr-finance-shell__lead,
body.light .rr-finance-shell__pill,
body.light .rr-finance-shell__spotlight-value,
body.light .rr-finance-shell__spotlight-text,
body.light .rr-finance-metric-card__value,
body.light .rr-finance-metric-card__text {
    color: #172033;
}

body.light .rr-finance-shell__eyebrow,
body.light .rr-finance-panel__eyebrow {
    color: #1d4ed8;
}

body.light .rr-finance-shell__spotlight-label,
body.light .rr-finance-metric-card__label,
body.light .rr-finance-panel__summary {
    color: #64748b;
}

body.light #afiliadosSection .rr-perfil-card,
body.light #afiliadosSection .rr-affiliate-stat-card,
body.light #afiliadosSection .rr-affiliate-tier-card,
body.light #afiliadosSection .rr-perfil-affiliate-activation .rr-perfil-card {
    background:
        radial-gradient(circle at top right, rgba(16, 185, 129, 0.08), transparent 26%),
        radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.08), transparent 32%),
        linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(248, 252, 255, 0.98)) !important;
    border-color: rgba(37, 99, 235, 0.14) !important;
    box-shadow: 0 18px 30px rgba(148, 163, 184, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.8) !important;
}

body.light #afiliadosSection .rr-perfil-card__header {
    border-bottom-color: rgba(37, 99, 235, 0.12) !important;
    background: transparent !important;
}

body.light #afiliadosSection .rr-perfil-card__title,
body.light #afiliadosSection .rr-perfil-card__title small,
body.light #afiliadosSection .rr-affiliate-stat-label,
body.light #afiliadosSection .rr-affiliate-tier-requirement,
body.light #afiliadosSection .rr-affiliate-tier-benefits p,
body.light #afiliadosSection .rr-affiliate-progress-text,
body.light #afiliadosSection .rr-affiliate-next-level-header h4,
body.light #afiliadosSection .rr-affiliate-link-code,
body.light #afiliadosSection .rr-affiliate-referral-item small,
body.light #afiliadosSection .rr-affiliate-commission-item small,
body.light #afiliadosSection .rr-affiliate-commission-status,
body.light #afiliadosSection .rr-affiliate-progress-section p,
body.light #afiliadosSection .rr-affiliate-how-it-works p,
body.light #afiliadosSection .rr-affiliate-how-it-works h5,
body.light #afiliadosSection .rr-affiliate-how-it-works h3,
body.light #afiliadosSection .rr-affiliate-hero p,
body.light #afiliadosSection .rr-affiliate-hero h2 {
    color: #334155 !important;
}

body.light #afiliadosSection .rr-perfil-card__title,
body.light #afiliadosSection .rr-affiliate-stat-value,
body.light #afiliadosSection .rr-affiliate-tier-name,
body.light #afiliadosSection .rr-affiliate-link-code strong,
body.light #afiliadosSection .rr-affiliate-next-level-header,
body.light #afiliadosSection .rr-affiliate-referral-item span,
body.light #afiliadosSection .rr-affiliate-commission-item strong,
body.light #afiliadosSection .rr-affiliate-rate-item strong {
    color: #172033 !important;
}

body.light #afiliadosSection .rr-affiliate-stat-icon,
body.light #afiliadosSection .rr-perfil-card__title i,
body.light #afiliadosSection .rr-affiliate-next-level-header i,
body.light #afiliadosSection .rr-affiliate-how-it-works i {
    color: #059669 !important;
    filter: none !important;
}

body.light #afiliadosSection .rr-affiliate-link-card {
    background:
        radial-gradient(circle at top right, rgba(16, 185, 129, 0.1), transparent 28%),
        linear-gradient(165deg, rgba(255, 255, 255, 0.98) 0%, rgba(240, 253, 250, 0.98) 48%, rgba(248, 250, 252, 0.98) 100%) !important;
    border-color: rgba(5, 150, 105, 0.2) !important;
}

body.light #afiliadosSection .rr-affiliate-link-card--featured {
    box-shadow: 0 18px 34px rgba(5, 150, 105, 0.12), 0 12px 28px rgba(148, 163, 184, 0.12) !important;
}

body.light #afiliadosSection .rr-affiliate-featured-badge {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.16), rgba(59, 130, 246, 0.12)) !important;
    border-color: rgba(5, 150, 105, 0.2) !important;
    color: #047857 !important;
    box-shadow: 0 10px 20px rgba(5, 150, 105, 0.1) !important;
}

body.light #afiliadosSection .rr-affiliate-link-input-group {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(236, 253, 245, 0.96)) !important;
    border-color: rgba(5, 150, 105, 0.24) !important;
    box-shadow: 0 14px 26px rgba(5, 150, 105, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.9) !important;
}

body.light #afiliadosSection .rr-affiliate-link-input {
    background: linear-gradient(135deg, rgba(10, 15, 25, 0.98) 0%, rgba(6, 78, 59, 0.92) 100%) !important;
    color: #d1fae5 !important;
    box-shadow: inset 0 3px 8px rgba(0, 0, 0, 0.35), inset 0 -1px 0 rgba(255, 255, 255, 0.04) !important;
}

body.light #afiliadosSection .rr-affiliate-link-input:hover,
body.light #afiliadosSection .rr-affiliate-link-input:focus {
    color: #ecfdf5 !important;
}

body.light #afiliadosSection .rr-affiliate-link-code {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.09), rgba(59, 130, 246, 0.06)) !important;
    border-color: rgba(5, 150, 105, 0.2) !important;
    color: #475569 !important;
}

body.light #afiliadosSection .rr-affiliate-link-code strong {
    background: rgba(16, 185, 129, 0.12) !important;
    color: #047857 !important;
    text-shadow: none !important;
}

body.light #afiliadosSection .rr-affiliate-progress-container {
    background: rgba(226, 232, 240, 0.9) !important;
    border: 1px solid rgba(5, 150, 105, 0.14) !important;
}

body.light #afiliadosSection .rr-affiliate-progress-bar {
    background: linear-gradient(90deg, #10b981, #3b82f6) !important;
}

body.light #afiliadosSection .rr-affiliate-rate-item,
body.light #afiliadosSection .rr-affiliate-referral-item,
body.light #afiliadosSection .rr-affiliate-commission-item {
    background: rgba(255, 255, 255, 0.82) !important;
    border-color: rgba(37, 99, 235, 0.1) !important;
}

body.light #afiliadosSection .rr-affiliate-referrals-list,
body.light #afiliadosSection .rr-affiliate-commissions-list {
    background: rgba(255, 255, 255, 0.42);
    border-radius: 18px;
}

body.light #afiliadosSection .rr-perfil-btn,
body.light #afiliadosSection .rr-affiliate-link-btn.rr-perfil-btn--secondary {
    color: #1f2937 !important;
}

/* Final override: submenu do perfil fixo no topo + paleta alinhada ao site */
.rr-perfil-container {
    --rr-perfil-submenu-top: calc(var(--hub-navbar-offset, var(--hub-navbar-height, 72px)) + 8px);
    --rr-perfil-submenu-space: 96px;
    padding-top: var(--rr-perfil-submenu-space) !important;
}

#rrPerfilSubmenu {
    position: fixed !important;
    top: var(--rr-perfil-submenu-top) !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: min(1220px, calc(100vw - 28px)) !important;
    max-width: min(1220px, calc(100vw - 28px)) !important;
    margin: 0 !important;
    z-index: 95 !important;
}

#rrPerfilSubmenu .rr-epic-submenu__track {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 0.65rem !important;
    padding: 0.7rem !important;
    border-radius: 24px !important;
    border: 1px solid rgba(59, 130, 246, 0.16) !important;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.14), transparent 34%),
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.14), transparent 36%),
        linear-gradient(180deg, rgba(10, 16, 33, 0.96), rgba(9, 12, 24, 0.94)) !important;
    box-shadow:
        0 18px 34px rgba(2, 6, 23, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.06) !important;
    backdrop-filter: blur(14px) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn {
    min-height: 62px !important;
    padding: 0.72rem 0.5rem !important;
    border-radius: 18px !important;
    border: 1px solid rgba(148, 163, 184, 0.16) !important;
    background: linear-gradient(180deg, rgba(18, 25, 43, 0.9), rgba(15, 23, 42, 0.78)) !important;
    color: #dbe7ff !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04) !important;
    transform: none !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn:hover {
    border-color: rgba(249, 115, 22, 0.22) !important;
    background: linear-gradient(180deg, rgba(24, 33, 56, 0.94), rgba(18, 25, 43, 0.82)) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
    width: 28px !important;
    height: 28px !important;
    border-radius: 10px !important;
    background: rgba(255, 255, 255, 0.06) !important;
    border: 1px solid rgba(255, 255, 255, 0.06) !important;
    box-shadow: none !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon {
    color: #f8fafc !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__text {
    width: 100% !important;
    min-width: 0 !important;
    align-items: center !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
    display: block !important;
    width: 100% !important;
    color: #f8fafc !important;
    font-size: 0.68rem !important;
    font-weight: 900 !important;
    letter-spacing: 0.02em !important;
    line-height: 1.06 !important;
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
    max-width: none !important;
    text-align: center !important;
    overflow-wrap: anywhere !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta {
    color: #93c5fd !important;
    font-size: 0.62rem !important;
    opacity: 0.9 !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn.active,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
    border-color: rgba(255, 255, 255, 0.08) !important;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.96), rgba(37, 99, 235, 0.92)) !important;
    color: #fff !important;
    box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__icon-wrap {
    background: rgba(255, 255, 255, 0.14) !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
}

body.light .rr-perfil-container {
    --rr-perfil-submenu-space: 96px;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__track {
    border-color: rgba(251, 146, 60, 0.16) !important;
    background:
        radial-gradient(circle at top left, rgba(249, 115, 22, 0.12), transparent 34%),
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.1), transparent 36%),
        linear-gradient(180deg, rgba(255, 250, 244, 0.98), rgba(255, 241, 229, 0.96)) !important;
    box-shadow:
        0 16px 30px rgba(194, 65, 12, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.94) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn {
    border-color: rgba(194, 65, 12, 0.1) !important;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 247, 237, 0.96)) !important;
    color: #1f2937 !important;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.96),
        0 8px 18px rgba(148, 163, 184, 0.1) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn:hover {
    border-color: rgba(249, 115, 22, 0.18) !important;
    background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(255, 242, 230, 0.98)) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
    background: rgba(249, 250, 251, 0.98) !important;
    border-color: rgba(194, 65, 12, 0.12) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
    color: #1f2937 !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta {
    color: #64748b !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
    border-color: rgba(37, 99, 235, 0.14) !important;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.94), rgba(59, 130, 246, 0.9)) !important;
    color: #fff !important;
    box-shadow: 0 16px 26px rgba(249, 115, 22, 0.14) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__icon,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__icon,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__icon,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__label,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__label,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__label,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.is-active .rr-epic-submenu__meta,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn.active .rr-epic-submenu__meta,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] .rr-epic-submenu__meta {
    color: #fff !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"].is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"].active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"][aria-selected="true"] {
    background: #d4a017 !important;
    border-color: rgba(250, 204, 21, 0.34) !important;
    color: #fffdf5 !important;
    box-shadow: 0 16px 28px rgba(212, 160, 23, 0.28) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"].is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"].active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"][aria-selected="true"] {
    background: #2563eb !important;
    border-color: rgba(96, 165, 250, 0.34) !important;
    color: #eff6ff !important;
    box-shadow: 0 16px 28px rgba(37, 99, 235, 0.28) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"].is-active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"].active,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"][aria-selected="true"] {
    background: #166534 !important;
    border-color: rgba(74, 222, 128, 0.28) !important;
    color: #ecfdf5 !important;
    box-shadow: 0 16px 28px rgba(22, 101, 52, 0.28) !important;
}

#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter].is-active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter].active .rr-epic-submenu__icon-wrap,
#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter][aria-selected="true"] .rr-epic-submenu__icon-wrap {
    background: rgba(255, 255, 255, 0.16) !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"].is-active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"].active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"][aria-selected="true"] {
    background: #d4a017 !important;
    border-color: rgba(202, 138, 4, 0.34) !important;
    color: #fffdf5 !important;
    box-shadow: 0 14px 24px rgba(212, 160, 23, 0.22) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"].is-active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"].active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="financeiro"][aria-selected="true"] {
    background: #2563eb !important;
    border-color: rgba(37, 99, 235, 0.34) !important;
    color: #eff6ff !important;
    box-shadow: 0 14px 24px rgba(37, 99, 235, 0.2) !important;
}

body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"].is-active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"].active,
body.light #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"][aria-selected="true"] {
    background: #166534 !important;
    border-color: rgba(22, 101, 52, 0.34) !important;
    color: #ecfdf5 !important;
    box-shadow: 0 14px 24px rgba(22, 101, 52, 0.2) !important;
}

@media (max-width: 768px) {
    .rr-perfil-container {
        display: grid;
        gap: 1rem;
        padding: 0 0 1rem;
        --rr-perfil-submenu-space: 104px;
    }

    .rr-perfil-section-content {
        display: grid;
        gap: 1rem;
    }

    .rr-perfil-section-content[style*="display: none"] {
        display: none !important;
    }

    .rr-perfil-hero {
        padding: 1rem !important;
        border-radius: 28px !important;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent 34%),
            radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.16), transparent 36%),
            linear-gradient(180deg, rgba(24, 12, 8, 0.98), rgba(10, 5, 3, 0.99)) !important;
        border: 1px solid rgba(249, 115, 22, 0.14) !important;
        box-shadow: 0 20px 38px rgba(0, 0, 0, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.04) !important;
    }

    .rr-perfil-hero__identity {
        grid-template-columns: 64px minmax(0, 1fr) !important;
        gap: 0.9rem !important;
        align-items: center !important;
    }

    .rr-perfil-hero__avatar {
        width: 64px !important;
        height: 64px !important;
        border-radius: 18px !important;
    }

    .rr-perfil-hero__title {
        font-size: 1.28rem !important;
        line-height: 1.06 !important;
    }

    .rr-perfil-hero__meta,
    .rr-perfil-hero__chips {
        gap: 0.55rem !important;
    }

    .rr-perfil-hero__chip {
        width: 100%;
        justify-content: center;
        min-height: 40px;
        border-radius: 14px;
        font-size: 0.78rem;
    }

    .rr-perfil-hero__actions,
    .rr-finance-stage__actions,
    .rr-affiliate-stage__actions {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 0.65rem !important;
    }

    .rr-perfil-hero__btn {
        width: 100% !important;
        min-height: 48px;
        justify-content: center;
    }

    .rr-perfil-hero__stats {
        grid-template-columns: 1fr !important;
        gap: 0.72rem !important;
    }

    .rr-perfil-hero__stat,
    .rr-perfil-inline-status__item {
        border-radius: 20px !important;
        background: rgba(255, 255, 255, 0.04) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
    }

    #rrPerfilSubmenu {
        width: calc(100vw - 16px) !important;
        max-width: calc(100vw - 16px) !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__track {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 0.7rem !important;
        padding: 0.85rem 1rem !important;
        border-radius: 26px !important;
        border-width: 1px !important;
        overflow: visible !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn {
        flex: 0 0 auto !important;
        min-height: 42px !important;
        padding: 0.72rem 1rem !important;
        border-radius: 999px !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        background: rgba(255, 255, 255, 0.04) !important;
        color: #f8fafc !important;
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.55rem !important;
        font-size: 0.82rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        box-shadow: none !important;
        transform: none !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn:hover {
        transform: translateY(-1px) !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn.is-active,
    #rrPerfilSubmenu .rr-epic-submenu__btn.active,
    #rrPerfilSubmenu .rr-epic-submenu__btn[aria-selected="true"] {
        background: linear-gradient(135deg, #f97316, #2563eb) !important;
        border-color: transparent !important;
        color: #fff !important;
        box-shadow: 0 18px 30px rgba(37, 99, 235, 0.2) !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon-wrap {
        width: 18px !important;
        height: 18px !important;
        min-width: 18px !important;
        border-radius: 0 !important;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__icon {
        width: 16px !important;
        height: 16px !important;
        color: currentColor !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__text {
        width: 100% !important;
        min-width: 0 !important;
        align-items: center !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
        display: block !important;
        width: 100% !important;
        font-size: 0.74rem !important;
        font-weight: 900 !important;
        letter-spacing: 0.04em !important;
        line-height: 1.08 !important;
        text-transform: uppercase !important;
        color: currentColor !important;
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        max-width: none !important;
        text-align: center !important;
        overflow-wrap: anywhere !important;
    }

    #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__meta,
    #rrPerfilSubmenu .rr-epic-submenu__effect,
    #rrPerfilSubmenu .rr-epic-submenu__crown {
        display: none !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__track {
        box-shadow: 0 14px 26px rgba(194, 65, 12, 0.1) !important;
    }

    body.light #rrPerfilSubmenu .rr-epic-submenu__btn {
        background: rgba(255, 255, 255, 0.88) !important;
        border-color: rgba(194, 65, 12, 0.1) !important;
        color: #1f2937 !important;
    }

@media (max-width: 767px) {
    .rr-perfil-container {
        --rr-perfil-submenu-space: 0px !important;
        padding-top: 0 !important;
    }

    .rr-perfil-section-content {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    #rrPerfilSubmenu {
        display: none !important;
    }

    #rrPerfilSubmenu {
        margin-bottom: 0.75rem !important;
    }

        #rrPerfilSubmenu .rr-epic-submenu__track {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.55rem !important;
            padding: 0.8rem !important;
            border-radius: 22px !important;
        }

        #rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="perfil"] {
            display: none !important;
        }

        #rrPerfilSubmenu .rr-epic-submenu__btn {
            width: 100% !important;
            min-width: 0 !important;
            min-height: 58px !important;
            padding: 0.72rem 0.55rem !important;
            font-size: 0.7rem !important;
            letter-spacing: 0.06em !important;
        }

        #rrPerfilSubmenu .rr-epic-submenu__btn .rr-epic-submenu__label {
            font-size: 0.64rem !important;
            letter-spacing: 0.03em !important;
        }
    }

    .rr-perfil-grid,
    .rr-perfil-grid--single,
    .rr-perfil-grid--three,
    .rr-perfil-grid--x1,
    .rr-perfil-field-group {
        grid-template-columns: 1fr !important;
        gap: 0.85rem !important;
    }

    .rr-perfil-card,
    .rr-finance-stage,
    .rr-affiliate-stage,
    #afiliadosSection .rr-affiliate-stat-card,
    #afiliadosSection .rr-affiliate-tier-card,
    #afiliadosSection .rr-affiliate-link-card {
        border-radius: 26px !important;
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 32%),
            radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.12), transparent 28%),
            linear-gradient(180deg, rgba(23, 10, 6, 0.98), rgba(10, 5, 3, 0.99)) !important;
        border: 1px solid rgba(249, 115, 22, 0.12) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.2) !important;
    }

    .rr-perfil-card__header {
        padding: 1rem 1rem 0.85rem !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.7rem !important;
    }

    .rr-perfil-card__title {
        font-size: 1rem !important;
        line-height: 1.35 !important;
    }

    .rr-perfil-card__title small {
        display: block;
        margin-top: 0.32rem;
        line-height: 1.5;
    }

    .rr-perfil-card__body,
    .rr-affiliate-link-body,
    .rr-affiliate-combined-layout {
        padding: 1rem !important;
    }

    .rr-perfil-photo {
        grid-template-columns: 1fr !important;
        justify-items: center;
        text-align: center;
        gap: 0.9rem !important;
        padding: 1rem !important;
        border-radius: 22px !important;
    }

    .rr-perfil-photo__controls {
        align-items: center !important;
    }

    .rr-perfil-photo__btn,
    .rr-perfil-btn {
        width: 100%;
        justify-content: center;
    }

    .rr-perfil-inline-status {
        grid-template-columns: 1fr !important;
        gap: 0.72rem !important;
    }

    .rr-affiliate-combined-layout,
    .rr-finance-stage__visual,
    .rr-affiliate-stage__visual {
        grid-template-columns: 1fr !important;
    }

    .rr-affiliate-divider {
        width: 100% !important;
        height: 2px !important;
        min-height: 2px !important;
    }
}

@media (max-width: 768px) {
    body.light .rr-perfil-hero,
    body.light .rr-perfil-card,
    body.light .rr-finance-stage,
    body.light .rr-affiliate-stage,
    body.light #afiliadosSection .rr-affiliate-stat-card,
    body.light #afiliadosSection .rr-affiliate-tier-card,
    body.light #afiliadosSection .rr-affiliate-link-card {
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 32%),
            radial-gradient(circle at bottom left, rgba(249, 115, 22, 0.08), transparent 28%),
            linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(250, 245, 238, 0.98)) !important;
        border-color: rgba(59, 130, 246, 0.12) !important;
        box-shadow: 0 18px 34px rgba(148, 163, 184, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.78) !important;
    }

    body.light .rr-perfil-hero__stat,
    body.light .rr-perfil-inline-status__item {
        background: rgba(255, 255, 255, 0.82) !important;
        border-color: rgba(37, 99, 235, 0.1) !important;
    }
}
</style>

@endguest

<script>
console.log('🔍 [PERFIL] Script carregado');

// Função para inicializar os submenus do perfil
window.initPerfilSubmenus = function() {
    console.log('🚀 [PERFIL] initPerfilSubmenus() chamada');

    // Novo seletor para epic-submenu
    const submenuBtns = document.querySelectorAll('#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter]');
    const perfilHeroSection = document.getElementById('perfilHeroSection');
    const financeiroSections = [
        document.getElementById('premiosSection'),
    ].filter(Boolean);
    const legacyRecompensasSections = [
        document.getElementById('premiosSection'),
    ].filter(Boolean);
    const sections = {
        'perfil': document.getElementById('perfilSection'),
        'financeiro': financeiroSections
    };
    const sectionAliases = {
        recompensas: 'financeiro',
        premios: 'financeiro',
        assinatura: 'financeiro',
        x1equipes: 'financeiro'
    };

    console.log('📊 [PERFIL] Botões encontrados:', submenuBtns.length);
    console.log('📦 [PERFIL] Seções:', {
        perfil: sections.perfil ? 'OK ✅' : 'NULL ❌',
        financeiro: financeiroSections.length ? 'OK ✅' : 'NULL ❌'
    });

    if (submenuBtns.length === 0) {
        console.error('❌ [PERFIL] ERRO: Nenhum botão de submenu encontrado!');
        return false;
    }

    if (!financeiroSections.length) {
        console.error('❌ [PERFIL] ERRO: Algumas seções não foram encontradas!');
        return false;
    }

    const togglePerfilHero = (targetSection) => {
        if (!perfilHeroSection) return;
        perfilHeroSection.style.display = targetSection === 'perfil' ? '' : 'none';
    };

    const activateSection = (rawSectionName) => {
        const targetSection = sectionAliases[rawSectionName] || rawSectionName;
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log(`🎯 [PERFIL] Ativando: "${rawSectionName}" -> "${targetSection}"`);

        submenuBtns.forEach(btn => {
            const isActive = (btn.getAttribute('data-filter') === rawSectionName)
                || ((sectionAliases[btn.getAttribute('data-filter')] || btn.getAttribute('data-filter')) === targetSection);
            btn.classList.toggle('is-active', isActive);
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        Object.entries(sections).forEach(([name, section]) => {
            const list = Array.isArray(section) ? section : [section];
            list.forEach(sec => {
                if (sec) {
                    sec.style.display = 'none';
                    console.log(`   ➜ "${name}" = none`);
                }
            });
        });

        if (sections[targetSection]) {
            const list = Array.isArray(sections[targetSection]) ? sections[targetSection] : [sections[targetSection]];
            list.forEach(sec => {
                if (sec) {
                    sec.style.display = 'block';
                    console.log(`👁️ [PERFIL] "${targetSection}" = BLOCK (${sec.id})`);
                }
            });
        } else {
            console.error(`❌ [PERFIL] Seção "${targetSection}" não encontrada!`);
        }

        togglePerfilHero(targetSection);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    };

    submenuBtns.forEach((btn, index) => {
        const dataFilter = btn.getAttribute('data-filter');
        console.log(`🎯 [PERFIL] Registrando listener ${index + 1}/${submenuBtns.length} - "${dataFilter}"`);

        btn.onclick = function(e) {
            e.preventDefault();
            activateSection(this.getAttribute('data-filter'));
        };
    });

    console.log('✅ [PERFIL] Submenus inicializados com sucesso!');

    // Função global para alternar seções
    window.switchToSection = function(sectionName) {
        const resolvedSectionName = sectionAliases[sectionName] || sectionName;
        console.log(`🔀 [PERFIL] switchToSection("${sectionName}") -> "${resolvedSectionName}"`);
        activateSection(resolvedSectionName);
        window.RR_ACTIVE_PROFILE_SECTION = resolvedSectionName;
        console.log(`✅ [PERFIL] Seção "${resolvedSectionName}" exibida`);

        if (sectionName === 'x1') { // legacy fallback
            const x1TabButton = document.querySelector('.hub-mobile-tabbar__btn[data-section="x1"]');
            if (x1TabButton) x1TabButton.click();
        }
    };

    const pendingSection = window.RR_PENDING_PROFILE_SECTION || null;
    const rememberedSection = window.RR_ACTIVE_PROFILE_SECTION || null;
    const currentHubSection = document.body ? (document.body.getAttribute('data-hub-section') || '') : '';
    let initialSection = pendingSection || rememberedSection || null;

    if (!initialSection) {
        if (currentHubSection === 'afiliados' || currentHubSection === 'pix') {
            initialSection = 'financeiro';
        } else if (currentHubSection === 'perfil') {
            initialSection = 'perfil';
        } else {
            initialSection = 'financeiro';
        }
    }

    if (typeof window.switchToSection === 'function') {
        console.log(`⏳ [PERFIL] Seção inicial: "${initialSection}"`);
        window.switchToSection(initialSection);
        window.RR_PENDING_PROFILE_SECTION = null;
    }

    window.scrollToPerfilData = function() {
        if (typeof window.switchToSection === 'function') {
            window.switchToSection('perfil');
        }

        window.setTimeout(function() {
            const target = document.getElementById('perfilDataAnchor') || document.getElementById('perfilForm');
            if (!target) return;

            const rootStyles = getComputedStyle(document.documentElement);
            const bodyStyles = getComputedStyle(document.body);
            const navbarOffset = parseFloat(rootStyles.getPropertyValue('--hub-navbar-offset'))
                || parseFloat(bodyStyles.getPropertyValue('--hub-navbar-offset'))
                || ((parseFloat(rootStyles.getPropertyValue('--hub-navbar-height')) || 96) + 8);
            const submenu = document.getElementById('rrPerfilSubmenu');
            const submenuOffset = submenu ? (submenu.getBoundingClientRect().height + 14) : 0;
            const targetTop = window.scrollY + target.getBoundingClientRect().top - navbarOffset - submenuOffset;

            window.scrollTo({
                top: Math.max(0, targetTop),
                behavior: 'smooth'
            });
        }, 140);
    };

    return true;
};

// Observa quando o conteúdo do perfil é inserido na página
if (typeof window.rrPerfilObserver === 'undefined') {
    console.log('👀 [PERFIL] Criando MutationObserver...');

    window.rrPerfilObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Verifica se o conteúdo do perfil foi adicionado
                        if (node.classList && node.classList.contains('rr-perfil-container')) {
                            console.log('🎉 [PERFIL] Conteúdo do perfil detectado via MutationObserver!');
                            setTimeout(function() {
                                window.initPerfilSubmenus();
                            }, 100);
                        } else if (node.querySelector && node.querySelector('.rr-perfil-container')) {
                            console.log('🎉 [PERFIL] Container de perfil encontrado dentro do nó adicionado!');
                            setTimeout(function() {
                                window.initPerfilSubmenus();
                            }, 100);
                        }
                    }
                });
            }
        });
    });

    // Observa mudanças no body
    window.rrPerfilObserver.observe(document.body, {
        childList: true,
        subtree: true
    });

    console.log('✅ [PERFIL] MutationObserver ativo');
}

// Tenta inicializar imediatamente caso já esteja na página
setTimeout(function() {
    console.log('🔄 [PERFIL] Tentando inicializar (fallback)...');
    if (document.querySelector('.rr-perfil-container')) {
        console.log('✅ [PERFIL] Container encontrado, inicializando...');
        window.initPerfilSubmenus();
    } else {
        console.log('⏳ [PERFIL] Container não encontrado, aguardando MutationObserver...');
    }
}, 300);

// ============================================
// ASSINATURA ACTIONS (Cancelar/Reativar)
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const btnCancelar = document.getElementById('btnCancelarAssinatura');
    const btnReativar = document.getElementById('btnReativarAssinatura');

    if (btnCancelar) {
        btnCancelar.addEventListener('click', async function() {
            if (!confirm('Tem certeza que deseja cancelar sua assinatura Premium?\n\nVocê continuará com acesso até o fim do período atual.')) {
                return;
            }

            btnCancelar.disabled = true;
            btnCancelar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelando...';

            try {
                const response = await fetch('/api/subscriptions/cancel', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ reason: 'user_requested' })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Assinatura cancelada. Você ainda tem acesso até ' + (data.access_until || 'o fim do período.'));
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao cancelar assinatura');
                }
            } catch (e) {
                console.error('Erro:', e);
                alert('Erro de conexão. Tente novamente.');
            } finally {
                btnCancelar.disabled = false;
                btnCancelar.innerHTML = '<i class="fas fa-times"></i> Cancelar Assinatura';
            }
        });
    }

    if (btnReativar) {
        btnReativar.addEventListener('click', async function() {
            btnReativar.disabled = true;
            btnReativar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Reativando...';

            try {
                const response = await fetch('/api/subscriptions/reactivate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    alert('Assinatura reativada com sucesso!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao reativar assinatura');
                }
            } catch (e) {
                console.error('Erro:', e);
                alert('Erro de conexão. Tente novamente.');
            } finally {
                btnReativar.disabled = false;
                btnReativar.innerHTML = '<i class="fas fa-redo"></i> Reativar Assinatura';
            }
        });
    }
});

// ============================================
// VERIFICAÇÃO DE USERNAME
// ============================================
if (typeof window.usernameCheckTimeout === 'undefined') {
    window.usernameCheckTimeout = null;
}

async function checkUsernameAvailability(username) {
    const usernameInput = document.getElementById('usernameInput');
    const usernameFeedback = document.getElementById('usernameAvailability');
    const originalUsername = usernameInput?.dataset.original || '';

    if (!username || username.length < 3) {
        if (usernameFeedback) usernameFeedback.style.display = 'none';
        return;
    }

    if (username === originalUsername) {
        if (usernameFeedback) usernameFeedback.style.display = 'none';
        return;
    }

    if (usernameFeedback) {
        usernameFeedback.style.display = 'flex';
        usernameFeedback.className = 'rr-username-feedback checking';
        usernameFeedback.innerHTML = '<i class="fas fa-spinner"></i><span>Verificando disponibilidade...</span>';
    }

    try {
        const response = await fetch('{{ route("user.username.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ username: username })
        });

        const data = await response.json();
        if (usernameFeedback) {
            if (data.available) {
                usernameFeedback.className = 'rr-username-feedback available';
                usernameFeedback.innerHTML = '<i class="fas fa-check-circle"></i><span>' + data.message + '</span>';
            } else {
                usernameFeedback.className = 'rr-username-feedback unavailable';
                usernameFeedback.innerHTML = '<i class="fas fa-times-circle"></i><span>' + data.message + '</span>';
            }
        }
    } catch (error) {
        console.error('Erro ao verificar username:', error);
        if (usernameFeedback) usernameFeedback.style.display = 'none';
    }
}

// ============================================
// INICIALIZA FORMULÁRIOS QUANDO PERFIL É CARREGADO
// ============================================
window.initPerfilForms = function() {
    console.log('📋 [PERFIL] Inicializando formulários...');

    // Username verification
    const usernameInput = document.getElementById('usernameInput');
    const usernameConfirmation = document.getElementById('usernameConfirmation');

    if (usernameInput) {
        usernameInput.dataset.original = usernameInput.value;

        usernameInput.addEventListener('blur', function() {
            const username = this.value.trim();
            if (username && username !== this.dataset.original) {
                checkUsernameAvailability(username);
            }
        });

        usernameInput.addEventListener('input', function() {
            clearTimeout(window.usernameCheckTimeout);
            const username = this.value.trim();
            if (username && username !== this.dataset.original && username.length >= 3) {
                window.usernameCheckTimeout = setTimeout(() => {
                    checkUsernameAvailability(username);
                }, 800);
            } else {
                const feedback = document.getElementById('usernameAvailability');
                if (feedback) feedback.style.display = 'none';
            }
        });
    }

    if (usernameConfirmation) {
        usernameConfirmation.addEventListener('focus', function() {
            const username = usernameInput?.value.trim();
            if (username && username !== usernameInput.dataset.original) {
                checkUsernameAvailability(username);
            }
        });
    }

    // ========== BANNER DE PERFIL INCOMPLETO ==========
    (function() {
        const banner = document.getElementById('profileIncompleteBanner');
        const showBanner = sessionStorage.getItem('show_complete_profile_banner');

        // Verificar se deve mostrar o banner (novo registro ou perfil incompleto)
        const profileIncomplete = {{ $profileIncomplete ? 'true' : 'false' }};

        if (banner && profileIncomplete && (showBanner === '1' || profileIncomplete)) {
            banner.style.display = 'flex';
            // Limpar o flag do sessionStorage
            sessionStorage.removeItem('show_complete_profile_banner');

            // Destacar campos vazios
            document.querySelectorAll('.rr-required-field').forEach(function(field) {
                if (!field.value || field.value.trim() === '') {
                    field.classList.add('rr-field-error');
                }
            });

            // Scroll suave para o formulário
            setTimeout(function() {
                const form = document.getElementById('perfilForm');
                if (form) {
                    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 300);
        }

        // Remover destaque de erro quando campo é preenchido
        document.querySelectorAll('.rr-required-field').forEach(function(field) {
            field.addEventListener('input', function() {
                if (this.value && this.value.trim() !== '') {
                    this.classList.remove('rr-field-error');
                }
            });
        });
    })();

    // ========== VERIFICAÇÃO DE CPF ==========
    const cpfInput = document.getElementById('cpfInput');

    function formatCPF(value) {
        value = value.replace(/\D/g, '');
        value = value.slice(0, 11);
        if (value.length > 9) {
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
        } else if (value.length > 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
        } else if (value.length > 3) {
            value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
        }
        return value;
    }

    async function checkCpfAvailability(cpf) {
        const feedback = document.getElementById('cpfFeedback');
        if (!feedback) return;

        const cleanCpf = cpf.replace(/\D/g, '');

        if (cleanCpf.length !== 11) {
            feedback.textContent = 'CPF deve ter 11 dígitos';
            feedback.className = 'rr-input-feedback rr-input-feedback--error';
            feedback.style.display = 'block';
            return;
        }

        feedback.textContent = 'Verificando...';
        feedback.className = 'rr-input-feedback';
        feedback.style.display = 'block';

        try {
            const response = await fetch('{{ route("user.cpf.check") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ cpf: cleanCpf })
            });

            const data = await response.json();

            if (data.available) {
                feedback.textContent = '✓ CPF disponível';
                feedback.className = 'rr-input-feedback rr-input-feedback--success';
            } else {
                feedback.textContent = '✗ ' + data.message;
                feedback.className = 'rr-input-feedback rr-input-feedback--error';
            }
        } catch (error) {
            console.error('Erro ao verificar CPF:', error);
            feedback.textContent = 'Erro ao verificar CPF';
            feedback.className = 'rr-input-feedback rr-input-feedback--error';
        }
    }

    if (cpfInput && !cpfInput.disabled) {
        cpfInput.addEventListener('input', function() {
            this.value = formatCPF(this.value);

            clearTimeout(window.cpfCheckTimeout);
            const cpf = this.value.replace(/\D/g, '');
            const feedback = document.getElementById('cpfFeedback');

            if (cpf.length === 11) {
                window.cpfCheckTimeout = setTimeout(() => {
                    checkCpfAvailability(this.value);
                }, 500);
            } else if (feedback) {
                feedback.style.display = 'none';
            }
        });

        cpfInput.addEventListener('blur', function() {
            const cpf = this.value.replace(/\D/g, '');
            if (cpf.length === 11) {
                checkCpfAvailability(this.value);
            }
        });
    }

    // Upload de imagem
    const perfilImageInput = document.getElementById('perfilImage');
    if (perfilImageInput) {
        perfilImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    let img = document.getElementById('perfilAvatar');
                    const placeholder = document.getElementById('perfilAvatarPlaceholder');

                    if (img) {
                        img.src = event.target.result;
                        img.style.display = 'block';
                        if (placeholder) placeholder.style.display = 'none';
                    } else if (placeholder) {
                        img = document.createElement('img');
                        img.id = 'perfilAvatar';
                        img.src = event.target.result;
                        img.alt = 'Foto de perfil';
                        placeholder.parentNode.insertBefore(img, placeholder);
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Formulário de perfil - AJAX
    const perfilForm = document.getElementById('perfilForm');
    if (perfilForm) {
        console.log('✅ [PERFIL] Formulário encontrado, registrando submit AJAX');

        const deleteAccountBtn = document.getElementById('btnDeleteAccount');
        if (deleteAccountBtn) {
            deleteAccountBtn.addEventListener('click', async function() {
                const confirmed = confirm('Tem certeza que deseja excluir sua conta? Esta ação desativa permanentemente seu acesso.');
                if (!confirmed) return;

                const typed = prompt('Digite EXCLUIR para confirmar:');
                if (typed !== 'EXCLUIR') {
                    showErrorToast('Confirmação inválida. A conta não foi excluída.');
                    return;
                }

                const originalText = deleteAccountBtn.innerHTML;
                deleteAccountBtn.disabled = true;
                deleteAccountBtn.innerHTML = '<i class=\"fas fa-spinner fa-spin\"></i> Excluindo...';

                try {
                    const response = await fetch('{{ route("user.profile.deleteAccount") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        showSuccessToast('✅ Conta excluída com sucesso.');
                        setTimeout(function() {
                            window.location.href = data.redirect || '{{ route("home") }}';
                        }, 900);
                    } else {
                        showErrorToast((data && data.message) ? data.message : 'Não foi possível excluir a conta.');
                    }
                } catch (error) {
                    console.error('❌ [PERFIL] Erro ao excluir conta:', error);
                    showErrorToast('Erro ao excluir conta. Tente novamente.');
                } finally {
                    deleteAccountBtn.disabled = false;
                    deleteAccountBtn.innerHTML = originalText;
                }
            });
        }

        perfilForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('📤 [PERFIL] Submit interceptado - enviando via AJAX');

            const submitBtn = document.getElementById('perfilSubmit');
            const originalBtnText = submitBtn.innerHTML;

            const mustCompletePrizeProfile = {{ $profileIncomplete ? 'true' : 'false' }};
            if (mustCompletePrizeProfile) {
                const requiredFields = [
                    { name: 'firstname', label: 'Primeiro nome' },
                    { name: 'lastname', label: 'Sobrenome' },
                    { name: 'email', label: 'Email' },
                    { name: 'cpf', label: 'CPF' },
                    { name: 'birthdate', label: 'Data de nascimento' },
                    { name: 'mobile', label: 'WhatsApp' },
                    { name: 'pix_key_type', label: 'Tipo de Chave PIX' },
                    { name: 'pix_key', label: 'Chave PIX' }
                ];

                const missingFields = [];
                requiredFields.forEach(function(field) {
                    const input = perfilForm.querySelector('[name="' + field.name + '"]');
                    if (input && (!input.value || input.value.trim() === '')) {
                        missingFields.push(field.label);
                        input.classList.add('rr-field-error');
                    }
                });

                if (missingFields.length > 0) {
                    showErrorToast('Preencha os campos obrigatórios: ' + missingFields.join(', '));
                    const banner = document.getElementById('profileIncompleteBanner');
                    if (banner) banner.style.display = 'flex';
                    return;
                }
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            const formData = new FormData(this);

            // Verifica se o username foi alterado
            const usernameInput = document.getElementById('usernameInput');
            const usernameConfirmation = document.getElementById('usernameConfirmation');

            if (usernameInput) {
                const originalUsername = usernameInput.dataset.original || '';
                const currentUsername = usernameInput.value.trim();

                console.log('🔍 [PERFIL] Username original:', originalUsername);
                console.log('🔍 [PERFIL] Username atual:', currentUsername);

                // Se o username NÃO mudou, remove os campos de username do FormData
                if (currentUsername === originalUsername || !currentUsername) {
                    console.log('⚠️ [PERFIL] Username não alterado - removendo campos do FormData');
                    formData.delete('username');
                    formData.delete('username_confirmation');
                } else {
                    // Username mudou - valida se tem confirmação
                    const confirmation = usernameConfirmation?.value.trim() || '';

                    if (!confirmation) {
                        showErrorToast('Por favor, confirme o novo usuário no segundo campo.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        return;
                    }

                    if (currentUsername !== confirmation) {
                        showErrorToast('Os campos de usuário não coincidem.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        return;
                    }

                    console.log('✅ [PERFIL] Username alterado e confirmado');
                }
            }

            console.log('📦 [PERFIL] Dados a enviar:', Array.from(formData.entries()));

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                console.log('📥 [PERFIL] Status:', response.status);
                console.log('📥 [PERFIL] Resposta completa:', data);

                if (response.ok && data.success) {
                    // Toast de sucesso
                    showSuccessToast('✅ Perfil atualizado com sucesso!');

                    // Atualiza o data-original se o username foi alterado
                    if (data.user && data.user.username && usernameInput) {
                        usernameInput.dataset.original = data.user.username;
                        usernameInput.value = data.user.username;
                        if (usernameConfirmation) {
                            usernameConfirmation.value = '';
                        }

                        const heroMetaPrimary = document.querySelector('.rr-perfil-hero__meta span');
                        if (heroMetaPrimary) {
                            heroMetaPrimary.textContent = '@' + data.user.username;
                        }
                    }
                } else {
                    const errors = data.errors || [];
                    const message = data.message || (Array.isArray(errors) ? errors.join(', ') : JSON.stringify(errors));
                    showErrorToast('Erro: ' + message);
                    console.error('❌ [PERFIL] Erros:', errors);
                }
            } catch (error) {
                console.error('❌ [PERFIL] Erro:', error);
                showErrorToast('Erro ao salvar. Tente novamente.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    } else {
        console.warn('⚠️ [PERFIL] Formulário não encontrado!');
    }

    // Formulário PIX - AJAX
    const pixForm = document.getElementById('pixForm');
    if (pixForm) {
        console.log('✅ [PIX] Formulário encontrado');

        pixForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('📤 [PIX] Submit interceptado');

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

            const formData = new FormData(this);

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showSuccessToast('✅ Chave PIX salva com sucesso!');
                } else {
                    showErrorToast('Erro: ' + (data.message || 'Erro ao salvar chave PIX'));
                }
            } catch (error) {
                console.error('❌ [PIX] Erro:', error);
                showErrorToast('Erro ao salvar. Tente novamente.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }

    // Toggle: Mostrar usuário em listas
    const listingsToggle = document.getElementById('showInListingsToggle');
    if (listingsToggle) {
        listingsToggle.addEventListener('change', async function() {
            const row = document.getElementById('listingsToggleRow');
            const statusEl = document.getElementById('listingsVisibilityStatus');
            const hintEl = document.getElementById('listingsVisibilityHint');
            if (row) row.classList.add('is-loading');

            try {
                const resp = await fetch('{{ route("user.profile.toggleListings") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                });

                const data = await resp.json();
                if (data.success) {
                    if (statusEl) {
                        statusEl.textContent = listingsToggle.checked ? 'Visível' : 'Oculto';
                    }
                    if (hintEl) {
                        hintEl.textContent = listingsToggle.checked
                            ? 'Seu nome aparece em rankings, salas e destaques públicos.'
                            : 'Seu nome fica fora de rankings, ganhadores e listas públicas.';
                    }
                    if (typeof showSuccessToast === 'function') {
                        showSuccessToast(data.message);
                    }
                } else {
                    listingsToggle.checked = !listingsToggle.checked;
                    if (typeof showErrorToast === 'function') {
                        showErrorToast('Erro ao alterar configuração.');
                    }
                }
            } catch (err) {
                listingsToggle.checked = !listingsToggle.checked;
                if (typeof showErrorToast === 'function') {
                    showErrorToast('Erro de conexão. Tente novamente.');
                }
            } finally {
                if (row) row.classList.remove('is-loading');
            }
        });
    }

    console.log('✅ [PERFIL] Formulários inicializados');
};

// Chama a inicialização dos formulários junto com os submenus
if (!window.__rrPerfilInitSubmenusWrapped) {
    window.__rrPerfilOriginalInitSubmenus = window.initPerfilSubmenus;
    window.initPerfilSubmenus = function() {
        const result = typeof window.__rrPerfilOriginalInitSubmenus === 'function'
            ? window.__rrPerfilOriginalInitSubmenus()
            : false;
        if (result) {
            setTimeout(window.initPerfilForms, 100);
        }
        return result;
    };
    window.__rrPerfilInitSubmenusWrapped = true;
}

// ============================================
// FUNÇÃO PARA ABRIR ABA PREMIUM
// ============================================
window.openPremiumTab = function() {
    const premiumBtn = document.querySelector('.hub-mobile-tabbar__btn[data-section="premium"]');
    if (premiumBtn) {
        premiumBtn.click();
    }
};

// ============================================
// TOAST DE NOTIFICAÇÃO (Igual ao X1)
// ============================================
window.showSuccessToast = function(message) {
    // Criar toast
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10000;
        animation: slideIn 0.4s ease-out;
    `;

    toast.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 6L9 17l-5-5"/>
        </svg>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    // Adicionar animação
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Remover após 3 segundos
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            toast.remove();
            style.remove();
        }, 300);
    }, 3000);
};

window.showErrorToast = function(message) {
    // Criar toast de erro
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10000;
        animation: slideIn 0.4s ease-out;
    `;

    toast.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
        </svg>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    // Adicionar animação
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Remover após 3 segundos
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            toast.remove();
            style.remove();
        }, 300);
    }, 3000);
};

</script>


<style>
/* Affiliate progress text - no wrap (auth) */
.rr-affiliate-progress-text {
    white-space: nowrap !important;
    word-break: keep-all;
    display: inline-flex;
    align-items: center;
    line-height: 1;
}

.rr-affiliate-progress-bar {
    white-space: nowrap;
}

/* Affiliate CTA Card */
.rr-perfil-affiliate-cta {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 16px;
    cursor: pointer;
    transition: all 0.3s;
}

.rr-perfil-affiliate-cta:hover {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.1));
    border-color: rgba(16, 185, 129, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.2);
}

.rr-perfil-affiliate-cta__icon {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(16, 185, 129, 0.2);
    border-radius: 12px;
    font-size: 1.5rem;
    color: #10b981;
}

.rr-perfil-affiliate-cta__content {
    flex: 1;
}

.rr-perfil-affiliate-cta__content h4 {
    margin: 0 0 4px 0;
    font-size: 1.1rem;
    color: #e2e8f0;
}

.rr-perfil-affiliate-cta__content p {
    margin: 0;
    font-size: 0.875rem;
    color: #94a3b8;
}

.rr-perfil-affiliate-cta__arrow {
    flex-shrink: 0;
    font-size: 1.25rem;
    color: #10b981;
}

/* Affiliate Dashboard Card */
.rr-perfil-affiliate-card {
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    cursor: pointer;
    transition: all 0.3s;
}

.rr-perfil-affiliate-card:hover {
    border-color: rgba(16, 185, 129, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.rr-perfil-affiliate-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.rr-perfil-affiliate-card__badge {
    font-size: 1.1rem;
    font-weight: 600;
    color: #e2e8f0;
}

.rr-perfil-affiliate-card__stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

.rr-perfil-affiliate-stat {
    text-align: center;
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
}

.rr-perfil-affiliate-stat__value {
    font-size: 1.25rem;
    font-weight: bold;
    color: #10b981;
    margin-bottom: 4px;
}

.rr-perfil-affiliate-stat__label {
    font-size: 0.75rem;
    color: #94a3b8;
    text-transform: uppercase;
}

.rr-perfil-affiliate-card__cta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: #10b981;
    font-weight: 600;
}

/* ============================================
   AFFILIATE SUMMARY (Perfil Tab) - SIMPLIFICADO
   ============================================ */
.rr-perfil-affiliate-summary {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.rr-perfil-affiliate-summary::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(16, 185, 129, 0.1) 50%, transparent 70%);
    background-size: 200% 200%;
    animation: shimmer 3s ease-in-out infinite;
    pointer-events: none;
}

@keyframes shimmer {
    0% { background-position: 100% 100%; }
    100% { background-position: 0% 0%; }
}

.rr-perfil-affiliate-summary:hover {
    border-color: rgba(16, 185, 129, 0.6);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.2);
}

.rr-perfil-affiliate-summary__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    position: relative;
    z-index: 1;
}

.rr-perfil-affiliate-summary__badge {
    font-size: 1.2rem;
    font-weight: 700;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rr-perfil-affiliate-summary__label {
    font-size: 8px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rr-perfil-affiliate-summary__progress {
    margin-bottom: 16px;
    position: relative;
    z-index: 1;
}

.rr-perfil-affiliate-summary__progress-label {
    font-size: 0.9rem;
    color: #cbd5e1;
    margin-bottom: 8px;
    font-weight: 500;
}

.rr-perfil-affiliate-summary__cta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid rgba(16, 185, 129, 0.2);
    color: #10b981;
    font-weight: 600;
    font-size: 0.95rem;
    position: relative;
    z-index: 1;
    transition: gap 0.3s ease;
}

.rr-perfil-affiliate-summary:hover .rr-perfil-affiliate-summary__cta {
    gap: 8px;
}

.rr-perfil-affiliate-summary__cta i {
    transition: transform 0.3s ease;
}

.rr-perfil-affiliate-summary:hover .rr-perfil-affiliate-summary__cta i {
    transform: translateX(4px);
}
</style>

<style>
/* Affiliate Section Styles */
.rr-affiliate-tier-card {
    background: rgba(15, 23, 42, 0.6);
    border: 2px solid rgba(16, 185, 129, 0.2);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s;
}

.rr-affiliate-tier-card.active {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.1);
}

.rr-affiliate-tier-card:hover {
    transform: translateY(-4px);
    border-color: #10b981;
}

.rr-affiliate-tier-emoji {
    font-size: 3rem;
    margin-bottom: 12px;
}

.rr-affiliate-tier-name {
    color: #e2e8f0;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.rr-affiliate-tier-requirement {
    color: #94a3b8;
    font-size: 0.875rem;
    margin-bottom: 12px;
}

.rr-affiliate-tier-benefits p {
    color: #cbd5e1;
    font-size: 0.9rem;
    margin: 4px 0;
}

.rr-affiliate-stat-card {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}

.rr-affiliate-stat-icon {
    font-size: 2.5rem;
    margin-bottom: 8px;
}

.rr-affiliate-stat-label {
    color: #94a3b8;
    font-size: 0.875rem;
    margin-bottom: 4px;
}

.rr-affiliate-stat-value {
    color: #e2e8f0;
    font-size: 1.5rem;
    font-weight: 700;
}

@media (min-width: 769px) {
    .rr-affiliate-stat-card {
        aspect-ratio: 1 / 1;
        padding: 14px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 6px;
    }

    .rr-affiliate-stat-icon {
        font-size: 2.1rem;
        margin-bottom: 0;
    }

    .rr-affiliate-stat-label {
        margin-bottom: 0;
        font-size: 0.82rem;
    }

    .rr-affiliate-stat-value {
        font-size: 1.2rem;
    }
}

.rr-affiliate-rate-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    color: #cbd5e1;
}

.rr-affiliate-rate-item:last-child {
    border-bottom: none;
}

.rr-affiliate-referral-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.rr-affiliate-referral-item:last-child {
    border-bottom: none;
}

.rr-affiliate-commission-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.rr-affiliate-commission-item:last-child {
    border-bottom: none;
}

.rr-affiliate-commission-type {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 8px;
}

.rr-affiliate-commission-type.x1 {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.rr-affiliate-commission-type.fantasy {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.rr-affiliate-commission-status {
    font-size: 0.75rem;
    font-weight: 600;
}

.rr-affiliate-commission-status.pending {
    color: #f59e0b;
}

.rr-affiliate-commission-status.approved {
    color: #3b82f6;
}

.rr-affiliate-commission-status.paid {
    color: #10b981;
}

.rr-affiliate-referrals-list,
.rr-affiliate-commissions-list {
    max-height: 400px;
    overflow-y: auto;
}
</style>

<script>
// Função para verificar se o perfil está completo
function checkProfileComplete() {
    @php
        $userData = $user ? [
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user && method_exists($user, 'hasRealEmail') && $user->hasRealEmail() ? $user->email : null,
            'mobile' => $user->mobile,
            'cpf' => $user->cpf,
            'birthdate' => $user->birthdate,
            'pix_key_type' => $user->pix_key_type,
            'pix_key' => $user->pix_key
        ] : [];
    @endphp
    const user = @json($userData);
    const mustCompletePrizeProfile = {{ $requiresPrizeProfile ? 'true' : 'false' }};
    
    if (!user || Object.keys(user).length === 0) {
        return { isComplete: false, missingFields: ['Dados do usuário não carregados'] };
    }

    if (!mustCompletePrizeProfile) {
        return { isComplete: true, missingFields: [] };
    }
    
    const requiredFields = {
        'firstname': user.firstname,
        'lastname': user.lastname,
        'email': user.email,
        'mobile': user.mobile,
        'cpf': user.cpf,
        'birthdate': user.birthdate,
        'pix_key_type': user.pix_key_type,
        'pix_key': user.pix_key
    };

    const missingFields = [];
    const fieldLabels = {
        'firstname': 'Primeiro nome',
        'lastname': 'Sobrenome',
        'email': 'Email',
        'mobile': 'WhatsApp',
        'cpf': 'CPF',
        'birthdate': 'Data de nascimento',
        'pix_key_type': 'Tipo de Chave PIX',
        'pix_key': 'Chave PIX'
    };

    for (const [field, value] of Object.entries(requiredFields)) {
        // Verificar se o valor existe e não está vazio
        const isEmpty = value === null || value === undefined || 
                       String(value).trim() === '' || 
                       String(value).trim() === 'null';
        if (isEmpty) {
            missingFields.push(fieldLabels[field]);
        }
    }

    return {
        isComplete: missingFields.length === 0,
        missingFields: missingFields
    };
}

// Função para abrir o submenu de afiliados
function openAfiliadosSubmenu() {
    const profileCheck = checkProfileComplete();
    
    if (!profileCheck.isComplete) {
        if (typeof RRToast !== 'undefined') {
            RRToast.error('Complete seu perfil antes de se tornar um afiliado!');
        }
        
        // Abrir modal de aviso
        const missingFieldsList = profileCheck.missingFields.join(', ');
        const confirmModal = confirm(
            '⚠️ PERFIL INCOMPLETO\n\n' +
            'Para se tornar um afiliado, você precisa completar os seguintes campos:\n\n' +
            '• ' + profileCheck.missingFields.join('\n• ') + '\n\n' +
            'Deseja ir para a aba Perfil agora para completar as informações?'
        );
        
        if (confirmModal) {
            // Abrir aba de perfil (dados)
            if (typeof window.openProfileTarget === 'function') {
                window.openProfileTarget('perfil');
            }
        }
        return;
    }
    
    // Se o perfil está completo, abrir aba de afiliados
    const targetBtn = document.querySelector('#rrPerfilSubmenu .rr-epic-submenu__btn[data-filter="afiliados"]');
    if (targetBtn) {
        targetBtn.click();
    }
}

// Função para validar perfil antes de ativar afiliado
function validateProfileAndActivateAffiliate() {
    const profileCheck = checkProfileComplete();
    
    if (!profileCheck.isComplete) {
        if (typeof RRToast !== 'undefined') {
            RRToast.error('Complete seu perfil antes de se tornar um afiliado!');
        }
        
        // Abrir modal de aviso
        const missingFieldsList = profileCheck.missingFields.join(', ');
        const confirmModal = confirm(
            '⚠️ PERFIL INCOMPLETO\n\n' +
            'Para se tornar um afiliado, você precisa completar os seguintes campos:\n\n' +
            '• ' + profileCheck.missingFields.join('\n• ') + '\n\n' +
            'Deseja ir para a aba Perfil agora para completar as informações?'
        );
        
        if (confirmModal) {
            // Abrir aba de perfil (dados)
            if (typeof window.openProfileTarget === 'function') {
                window.openProfileTarget('perfil');
            }
        }
        return;
    }
    
    // Se o perfil está completo, abrir modal de termos
    openAffiliateTermsModal();
}

// Função para copiar link de afiliado
function copyAffiliateLink() {
    const input = document.getElementById('affiliateReferralLink');
    if (!input) return;
    
    // ✅ Sempre usar o link completo
    const fullUrl = input.dataset.fullUrl || input.value;
    
    // ✅ Método moderno (funciona na maioria dos navegadores)
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(fullUrl)
            .then(() => {
                if (typeof RRToast !== 'undefined') {
                    RRToast.success('Link completo copiado! 🔗');
                } else {
                    alert('✅ Link copiado: ' + fullUrl);
                }
            })
            .catch(() => {
                // Fallback se falhar
                fallbackCopy(fullUrl);
            });
    } else {
        // Fallback para navegadores antigos
        fallbackCopy(fullUrl);
    }
}

// ✅ Função fallback para copiar (navegadores antigos)
function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        if (typeof RRToast !== 'undefined') {
            RRToast.success('Link completo copiado! 🔗');
        } else {
            alert('✅ Link copiado: ' + text);
        }
    } catch (err) {
        console.error('Erro ao copiar:', err);
        alert('❌ Erro ao copiar link');
    }
    
    document.body.removeChild(textarea);
}

// Função para compartilhar link de afiliado
function shareAffiliateLink() {
    const input = document.getElementById('affiliateReferralLink');
    const link = input?.dataset.fullUrl || input?.value;
    if (!link) return;

    const text = 'Junte-se a mim no Rei do Rodeio! 🤠🐂';

    if (navigator.share) {
        navigator.share({
            title: 'Rei do Rodeio',
            text: text,
            url: link
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: WhatsApp Web
        window.open('https://wa.me/?text=' + encodeURIComponent(text + ' ' + link), '_blank');
    }
}

// Funções do Modal de Termos
function openAffiliateTermsModal() {
    const modal = document.getElementById('affiliateTermsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Bloquear scroll
    }
}

function closeAffiliateTermsModal() {
    const modal = document.getElementById('affiliateTermsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restaurar scroll
    }
}

// Fechar modal ao clicar fora
document.addEventListener('click', function(e) {
    const modal = document.getElementById('affiliateTermsModal');
    if (e.target === modal) {
        closeAffiliateTermsModal();
    }
});

// Ativar afiliado (botão do modal)
if (document.getElementById('btnAcceptTerms')) {
    document.getElementById('btnAcceptTerms').addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span class=\"spinner-border spinner-border-sm me-2\"></span>Ativando...';

        try {
            const response = await fetch('{{ route("user.affiliate.activate.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                if (typeof RRToast !== 'undefined') {
                    RRToast.success(data.message);
                } else {
                    alert(data.message);
                }

                // Fechar modal
                closeAffiliateTermsModal();

                // Recarregar a página para mostrar o dashboard
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                if (typeof RRToast !== 'undefined') {
                    RRToast.error(data.message);
                } else {
                    alert(data.message);
                }
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            if (typeof RRToast !== 'undefined') {
                RRToast.error('Erro ao ativar conta de afiliado');
            }
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}
</script>

<style>
/* Popout Fullscreen de Termos */
.rr-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.rr-modal-container {
    width: 100% !important;
    height: 100% !important;
    display: flex;
    flex-direction: column;
    background: transparent;
    position: relative;
    z-index: 2147483647 !important;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.rr-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 32px 48px;
    border-bottom: 2px solid rgba(16, 185, 129, 0.3);
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
}

.rr-modal-title {
    font-size: 2rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 16px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

.rr-modal-title i {
    color: #10b981;
}

.rr-modal-close {
    background: rgba(239, 68, 68, 0.15);
    border: 2px solid rgba(239, 68, 68, 0.4);
    border-radius: 12px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ef4444;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s;
}

.rr-modal-close:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: #ef4444;
    transform: scale(1.1);
}

.rr-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 48px;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}

.rr-terms-content {
    color: #cbd5e1;
    line-height: 1.8;
    font-size: 1.05rem;
}

.rr-terms-content h4 {
    color: #10b981;
    font-size: 1.4rem;
    font-weight: 700;
    margin-top: 32px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid rgba(16, 185, 129, 0.3);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.rr-terms-content h4:first-child {
    margin-top: 0;
}

.rr-terms-content p {
    margin-bottom: 12px;
    font-size: 0.95rem;
}

.rr-terms-content ul {
    margin: 12px 0;
    padding-left: 24px;
}

.rr-terms-content li {
    margin-bottom: 8px;
}

.rr-terms-content strong {
    color: #e2e8f0;
    font-weight: 600;
}

.rr-terms-acceptance {
    background: rgba(16, 185, 129, 0.1);
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.rr-modal-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 32px 48px;
    border-top: 2px solid rgba(16, 185, 129, 0.3);
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
}

.rr-modal-footer button {
    min-width: 200px;
    padding: 14px 32px;
    font-size: 1.1rem;
}

.rr-modal-body::-webkit-scrollbar {
    width: 8px;
}

.rr-modal-body::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}

.rr-modal-body::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.3);
    border-radius: 4px;
}

.rr-modal-body::-webkit-scrollbar-thumb:hover {
    background: rgba(16, 185, 129, 0.5);
}

/* Responsive */
@media (max-width: 768px) {
    .rr-modal-container {
        max-height: 95vh;
        margin: 10px;
    }

    .rr-modal-header,
    .rr-modal-body,
    .rr-modal-footer {
        padding: 20px;
    }

    .rr-modal-title {
        font-size: 1.25rem;
    }

    .rr-terms-content h4 {
        font-size: 1rem;
    }

    .rr-modal-footer {
        flex-direction: column;
    }

    .rr-modal-footer button {
        width: 100%;
    }
}

/* ===================================================
   🔥 MODAL OVERLAY - FORÇA MÁXIMA ABSOLUTA
   =================================================== */

/* Quando modal está ativo, TODOS elementos ficam com z-index reduzido, exceto o header fixo do hub */
body.modal-open > *:not(#hubBrandOverlay) {
    z-index: 0 !important;
}

/* EXCETO o modal que tem z-index máximo */
body.modal-open #affiliateTermsModal {
    z-index: 2147483647 !important;
}

/* Força z-index em elementos específicos que podem ter z-index alto */
body.modal-open .hub-hero,
body.modal-open .hub-top,
body.modal-open .hub-shell,
body.modal-open .hub-mobile-tabbar,
body.modal-open .rr-navbar,
body.modal-open .hub-brand-overlay,
body.modal-open .hub-brand-center,
body.modal-open .hub-hero__player,
body.modal-open iframe,
body.modal-open video,
body.modal-open canvas,
body.modal-open .rr-particles-canvas {
    z-index: 0 !important;
    position: relative !important;
}

/* Modal overlay tem que estar FORA de qualquer container */
#affiliateTermsModal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Bloquear interação com tudo quando modal está aberto */
body.modal-open > *:not(#affiliateTermsModal):not(#hubBrandOverlay) {
    pointer-events: none !important;
}

body.modal-open #affiliateTermsModal {
    pointer-events: auto !important;
}

/* Garantir que modal fique na frente visualmente também */
body.modal-open::before {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 2147483646;
    pointer-events: none;
}
body.light .rr-x1-stat-label { color: rgba(15, 23, 42, 0.7); }
body.light .rr-x1-stat-value.positive { color: #15803d; }
body.light .rr-x1-stat-value.negative { color: #b91c1c; }
body.light .rr-x1-stat-value.neutral  { color: #0f172a; }
</style>
