    <div class="rr-modal" id="rrRankingModal" aria-hidden="true" style="padding: 0; background: linear-gradient(180deg, rgba(2,6,23,0.9), rgba(15,23,42,0.98)); backdrop-filter: blur(12px);">
    <div class="rr-modal__dialog" style="max-width: 100%; padding: 0; background: transparent; border: none; box-shadow: none; display: flex; flex-direction: column; align-items: center; height: 100vh; overflow: hidden;">
        
        <div style="width: 100%; max-width: 600px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
           <button class="rr-modal__close" type="button" data-close-modal="rrRankingModal" style="width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.1); border: none; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-arrow-left"></i></button>
           <button id="rrRankingRefreshBtn" class="rr-hero__btn rr-hero__btn--active" style="min-height: 48px; border-radius: 999px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(4, 120, 87, 0.9)); color: #fff; padding: 0 20px; border: 1px solid rgba(16, 185, 129, 0.4); font-weight: 800; display:flex; align-items:center; gap:8px;"><i class="fas fa-sync-alt"></i> Atualizar</button>
        </div>

        <div style="width: 100%; max-width: 600px; flex: 1; display: flex; flex-direction: column; position: relative; min-height: 0; overflow: hidden;">
            <div id="rrRankingPodiumContainer" style="display:flex; justify-content:center; align-items:flex-end; gap:10px; height: 360px; margin-top: 10px; flex-shrink: 0;"></div>
            <div id="rrRankingListWrap" style="flex:1; background: rgba(3,7,22,0.8); border-top-left-radius: 30px; border-top-right-radius: 30px; border: 1px solid rgba(255,255,255,0.05); padding: 20px; margin-top: -20px; overflow-y: auto; display: flex; flex-direction: column; gap:10px; min-height: 0;">
                <div id="rrRankingList"></div>
            </div>
        </div>

    </div>
</div>
<style>
.rr-podium-v2__slot { display: flex; flex-direction: column; align-items: center; width: 30%; max-width: 130px; position: relative; animation: pr 0.5s ease-out forwards; transform-origin: bottom; transform: scaleY(0); }
.rr-podium-v2__slot--2 { height: 70%; animation-delay: 0.1s; }
.rr-podium-v2__slot--1 { height: 100%; animation-delay: 0.2s; z-index: 2; width: 36%; max-width: 150px; }
.rr-podium-v2__slot--3 { height: 55%; animation-delay: 0.3s; }
.rr-podium-v2__avatar { width: 62px; height: 62px; border-radius: 50%; padding: 3px; box-sizing: border-box; margin-bottom: -31px; z-index: 3; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.6); display:flex; justify-content:center; align-items:center; color:#fff; font-weight:900; background: linear-gradient(135deg, rgba(249,115,22,0.5), rgba(37,99,235,0.5)); }
.rr-podium-v2__avatar img { width: 100%; height: 100%; object-fit: contain; object-position: center; border-radius: 50%; background: #020617; border: 2px solid #000; box-sizing: border-box; padding: 6px; }
.rr-podium-v2__avatar img.rr-ranking-avatar-logo { object-fit: contain; padding: 7px; background: #050816; }
.rr-podium-v2__slot--1 .rr-podium-v2__avatar { width: 84px; height: 84px; margin-bottom: -42px; background: linear-gradient(135deg, #fde047 0%, #b45309 50%, #fde047 100%); box-shadow: 0 0 30px rgba(250,204,21,0.6), inset 0 0 10px rgba(0,0,0,0.5); animation: goldshine 3s ease infinite; background-size: 200% 200%; }
.rr-podium-v2__slot--1 .rr-podium-v2__avatar img { padding: 9px; }
.rr-podium-v2__slot--1 .rr-podium-v2__avatar img.rr-ranking-avatar-logo { padding: 10px; }
.rr-podium-v2__slot--2 .rr-podium-v2__avatar { background: linear-gradient(135deg, #f1f5f9 0%, #64748b 100%); box-shadow: 0 0 15px rgba(241,245,249,0.3); }
.rr-podium-v2__slot--3 .rr-podium-v2__avatar { background: linear-gradient(135deg, #fcd34d 0%, #78350f 100%); box-shadow: 0 0 15px rgba(217,119,6,0.3); }
.rr-list-avatar { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; overflow:hidden; background:rgba(255,255,255,0.1); color:#fff; font-weight:800; font-size:14px; flex:0 0 auto; }
.rr-list-avatar img { width:100%; height:100%; object-fit:contain; object-position:center; border-radius:50%; background:#020617; padding:4px; box-sizing:border-box; }
.rr-list-avatar img.rr-ranking-avatar-logo { object-fit:contain; padding:5px; box-sizing:border-box; background:#050816; }
@keyframes goldshine { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
.rr-podium-v2__base { width: 100%; height: 100%; border-radius: 18px 18px 0 0; display: flex; flex-direction: column; align-items: center; padding-top: 40px; color: #fff; background: linear-gradient(180deg, rgba(30,41,59,0.9), rgba(2,6,23,0.9)); border: 1px solid rgba(255,255,255,0.1); border-bottom: 0; box-shadow: inset 0 20px 40px rgba(255,255,255,0.05); position: relative; overflow: hidden; }
.rr-podium-v2__slot--1 .rr-podium-v2__base { padding-top: 50px; background: linear-gradient(180deg, rgba(234,179,8,0.25), rgba(2,6,23,0.9)); border-color: rgba(251,191,36,0.4); }
.rr-podium-v2__slot--2 .rr-podium-v2__base { background: linear-gradient(180deg, rgba(148,163,184,0.2), rgba(2,6,23,0.9)); }
.rr-podium-v2__slot--3 .rr-podium-v2__base { background: linear-gradient(180deg, rgba(180,83,9,0.25), rgba(2,6,23,0.9)); }
.rr-podium-v2__rank { font-size: 3.5rem; font-weight: 900; opacity: 0.15; position: absolute; bottom: 0; line-height: 0.8; }
.rr-podium-v2__name { font-size: 0.85rem; font-weight: 900; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 90%; margin-bottom: 4px; color: #fff; }
.rr-podium-v2__slot--1 .rr-podium-v2__name { font-size: 1rem; color: #fbbf24; }
.rr-podium-v2__prize { font-size: 0.8rem; font-weight: 900; color: #4ade80; margin-bottom: 6px; }
.rr-podium-v2__slot--1 .rr-podium-v2__prize { font-size: 0.95rem; }
.rr-podium-v2__points { font-size: 0.65rem; font-weight: 800; color: #94a3b8; background: rgba(0,0,0,0.5); padding: 3px 8px; border-radius: 999px; }
@keyframes pr { 0% { transform: scaleY(0); } 100% { transform: scaleY(1); } }
@keyframes rrPixQueueSpin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes rrPixUrgencyPulse { 0% { box-shadow: 0 0 0 rgba(248, 113, 113, 0); } 50% { box-shadow: 0 0 26px rgba(248, 113, 113, 0.22); } 100% { box-shadow: 0 0 0 rgba(248, 113, 113, 0); } }
.rr-podium-wait { text-align:center; padding-top:40px; color:#cbd5e1; font-weight:800; letter-spacing:0.05em; font-size:0.9rem; }
.rr-podium-wait i { font-size:2rem; margin-bottom:10px; display:block; opacity:0.5; }

/* Premium Brazil theme */
.rr-app {
    --rr-br-green: #009739;
    --rr-br-green-dark: #022c22;
    --rr-br-green-deep: #011b15;
    --rr-br-yellow: #fedd00;
    --rr-br-gold: #c8942e;
    --rr-br-gold-soft: #f6d365;
    --rr-br-white: #f8fafc;
    --rr-br-ink: #020617;
}

body,
.rr-main {
    background:
        radial-gradient(circle at 16% 0%, rgba(254, 221, 0, 0.055), transparent 28%),
        radial-gradient(circle at 88% 6%, rgba(0, 151, 57, 0.055), transparent 28%),
        linear-gradient(180deg, #050816 0%, #07111f 52%, #020617 100%) !important;
}

.rr-stage,
.rr-panel,
.rr-modal__dialog,
.rr-box,
.rr-ranking-hero,
.rr-ranking-list-wrap {
    border-color: rgba(254, 221, 0, 0.12) !important;
    background:
        radial-gradient(circle at 16% 0%, rgba(254, 221, 0, 0.045), transparent 28%),
        radial-gradient(circle at 92% 8%, rgba(0, 151, 57, 0.06), transparent 30%),
        linear-gradient(180deg, rgba(9, 15, 30, 0.98), rgba(3, 7, 18, 0.98)) !important;
    box-shadow: 0 28px 60px rgba(0, 0, 0, 0.38), inset 0 1px 0 rgba(248, 250, 252, 0.06) !important;
}

.rr-brand img,
.rr-logo {
    filter: drop-shadow(0 10px 20px rgba(200, 148, 46, 0.28)) drop-shadow(0 0 16px rgba(0, 151, 57, 0.16)) !important;
}

.rr-brand h1,
.rr-title,
.rr-hero__name,
.rr-modal__title,
.rr-card__event strong,
.rr-side__headline,
.rr-ranking-podium-card__name,
.rr-ranking-row__name {
    color: var(--rr-br-white) !important;
}

.rr-copy,
.rr-side__text {
    color: rgba(248, 250, 252, 0.84) !important;
}

.rr-pill,
.rr-card__badge,
.rr-ranking-badge,
.rr-team-member__badge {
    border-color: rgba(254, 221, 0, 0.35) !important;
    background:
        radial-gradient(circle at 18% 0%, rgba(254, 221, 0, 0.18), transparent 44%),
        linear-gradient(135deg, rgba(0, 151, 57, 0.20), rgba(1, 27, 21, 0.88)) !important;
    color: #fef9c3 !important;
    box-shadow: 0 0 18px rgba(200, 148, 46, 0.14), inset 0 1px 0 rgba(248, 250, 252, 0.12) !important;
}

.rr-pill.is-sponsor,
.rr-hero__name.is-sponsor-link {
    border-color: rgba(254, 221, 0, 0.48) !important;
    background:
        radial-gradient(circle at 20% 0%, rgba(248, 250, 252, 0.20), transparent 38%),
        linear-gradient(135deg, rgba(0, 151, 57, 0.38), rgba(254, 221, 0, 0.22) 56%, rgba(200, 148, 46, 0.24)) !important;
    color: var(--rr-br-white) !important;
    box-shadow: 0 0 28px rgba(254, 221, 0, 0.14), 0 0 22px rgba(0, 151, 57, 0.14), inset 0 1px 0 rgba(248, 250, 252, 0.16) !important;
}

.rr-logo-wrap.is-sponsor-showcase {
    border-color: rgba(254, 221, 0, 0.34) !important;
    background:
        radial-gradient(circle at var(--rr-logo-mx) var(--rr-logo-my), rgba(248,250,252,.11), transparent 24%),
        radial-gradient(circle at 18% 0%, rgba(254,221,0,.09), transparent 42%),
        linear-gradient(180deg, rgba(8, 14, 27, .98), rgba(3, 7, 18, .98)) !important;
    box-shadow: 0 22px 55px rgba(0,0,0,.42), 0 0 28px rgba(200,148,46,.12), inset 0 1px 0 rgba(255,255,255,.14) !important;
}

.rr-sponsor-ring::before {
    background: conic-gradient(from 0deg, transparent 0deg, rgba(0,151,57,.95) 48deg, transparent 95deg, rgba(248,250,252,.74) 168deg, transparent 222deg, rgba(254,221,0,.92) 292deg, transparent 360deg) !important;
}

.rr-dot.is-active {
    background: var(--rr-br-yellow) !important;
    box-shadow: 0 0 0 4px rgba(254, 221, 0, 0.14), 0 0 16px rgba(0, 151, 57, 0.28) !important;
}

.rr-card {
    border-color: rgba(254, 221, 0, 0.14) !important;
    background:
        radial-gradient(circle at top right, rgba(254, 221, 0, 0.055), transparent 30%),
        linear-gradient(180deg, rgba(10, 18, 32, 0.98), rgba(3, 8, 18, 0.98)) !important;
}

.rr-card[data-tone=amber],
.rr-card[data-tone=green],
.rr-card[data-tone=blue] {
    border-color: rgba(254, 221, 0, 0.22) !important;
}

.rr-cards::before {
    background:
        radial-gradient(circle at 18% 24%, rgba(0,151,57,.07), transparent 30%),
        radial-gradient(circle at 82% 12%, rgba(254,221,0,.06), transparent 28%),
        radial-gradient(circle at 58% 92%, rgba(248,250,252,.035), transparent 34%) !important;
    opacity: .54 !important;
}

.rr-card__btn--enter {
    background: linear-gradient(180deg, #fff7ad 0%, #fedd00 44%, #c8942e 100%) !important;
    border-color: #fef9c3 !important;
    color: #3b2f06 !important;
    text-shadow: 0 1px 0 rgba(255,255,255,.36) !important;
    box-shadow: inset 0 2px 5px rgba(255,255,255,.46), inset 0 -4px 8px rgba(90,62,5,.22), 0 0 22px rgba(254,221,0,.30) !important;
}

.rr-competitor__add,
#rrTeamModal .rr-competitor__add {
    background: linear-gradient(180deg, #fedd00 0%, #c8942e 42%, #006b2f 100%) !important;
    border-color: #fef9c3 !important;
    color: var(--rr-br-white) !important;
    box-shadow: inset 0 2px 5px rgba(255,255,255,.34), inset 0 -4px 8px rgba(0,0,0,.32), 0 0 22px rgba(200,148,46,.38) !important;
}

.rr-card__btn--ranking,
.rr-mobile-actions__profile,
.rr-side__nav-profile,
.rr-side__nav-support,
.rr-hero__btn.is-active,
.rr-select {
    background: linear-gradient(180deg, #10b981 0%, #009739 46%, #015f2a 100%) !important;
    border-color: #bbf7d0 !important;
    color: var(--rr-br-white) !important;
    box-shadow: inset 0 2px 5px rgba(255,255,255,.30), inset 0 -4px 8px rgba(0,0,0,.28), 0 0 20px rgba(0,151,57,.34) !important;
}

.rr-mobile-actions__pix,
.rr-side__nav-pix,
.rr-side__nav-rules,
.rr-hero__btn.is-sponsor,
.rr-dock__home {
    background: linear-gradient(180deg, #fedd00 0%, #c8942e 52%, #7a5a16 100%) !important;
    border-color: #fef9c3 !important;
    color: var(--rr-br-white) !important;
    box-shadow: inset 0 2px 5px rgba(255,255,255,.36), inset 0 -4px 8px rgba(0,0,0,.28), 0 0 20px rgba(254,221,0,.24) !important;
}

.rr-card__btn--notify,
.rr-dock__pix {
    background: linear-gradient(135deg, #f8fafc 0%, #fedd00 34%, #009739 100%) !important;
    border-color: rgba(248,250,252,.85) !important;
    color: #052e16 !important;
    text-shadow: none !important;
}

.rr-card__btn--notify.is-active {
    background: linear-gradient(180deg, #16a34a 0%, #009739 54%, #064e3b 100%) !important;
    border-color: #bbf7d0 !important;
    color: var(--rr-br-white) !important;
    text-shadow: 0 2px 4px rgba(0,0,0,.35) !important;
}

#rrTeamModal .rr-competitor__add--remove {
    background: linear-gradient(135deg, #ef4444, #991b1b) !important;
    border-color: rgba(254, 202, 202, 0.45) !important;
    box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2) !important;
}

#rrTeamModal .rr-competitor__add--locked {
    background: linear-gradient(180deg, #6b7280, #374151) !important;
    border-color: rgba(203, 213, 225, 0.18) !important;
    color: #f8fafc !important;
    box-shadow: none !important;
}

.rr-countdown {
    border-color: rgba(254, 221, 0, 0.26) !important;
    background: linear-gradient(180deg, rgba(248,250,252,.12), rgba(0,151,57,.18)) !important;
    color: var(--rr-br-white) !important;
}

.rr-ranking-row--gold,
.rr-ranking-podium-card--champion {
    border-color: rgba(254, 221, 0, 0.42) !important;
    box-shadow: 0 20px 30px rgba(0,0,0,.26), 0 0 0 1px rgba(254,221,0,.12), 0 0 26px rgba(200,148,46,.16) !important;
}

.rr-ranking-row__bar span {
    background: linear-gradient(90deg, #009739, #fedd00, #f8fafc) !important;
    box-shadow: 0 0 14px rgba(254,221,0,.28) !important;
}

.rr-ranking-row--gold .rr-ranking-row__pos,
.rr-ranking-podium-card--champion .rr-ranking-podium-card__medal {
    background: linear-gradient(135deg, #fedd00, #c8942e, #009739) !important;
}

.rr-ranking-row--silver .rr-ranking-row__pos,
.rr-ranking-podium-card--silver .rr-ranking-podium-card__medal {
    background: linear-gradient(135deg, #f8fafc, #009739) !important;
}

.rr-podium-v2__slot--1 .rr-podium-v2__avatar {
    background: linear-gradient(135deg, #fedd00 0%, #009739 48%, #f8fafc 100%) !important;
}

#rrRulesModal h4 {
    color: var(--rr-br-gold-soft) !important;
}

#rrLiveAvatar {
    background: linear-gradient(135deg, rgba(0,151,57,.26), rgba(254,221,0,.18)) !important;
    box-shadow: 0 8px 20px rgba(2,6,23,.42), 0 0 0 3px rgba(254,221,0,.08) !important;
}

#rrWalletModal .rr-box {
    border-color: rgba(254, 221, 0, 0.34) !important;
    background: linear-gradient(180deg, rgba(0, 151, 57, 0.13), rgba(2, 20, 17, 0.82)) !important;
}

#rrWalletModal small {
    color: #fef9c3 !important;
}

@keyframes rrSponsorAura {
    0%, 100% {
        opacity: .42;
        transform: translateZ(0) scale(.92);
        background: radial-gradient(circle at var(--rr-logo-mx) var(--rr-logo-my), rgba(255,255,255,.12), transparent 24%), radial-gradient(circle, rgba(0,151,57,.10), transparent 46%), radial-gradient(circle, rgba(254,221,0,.08), transparent 62%);
    }
    45% {
        opacity: .68;
        transform: translateZ(0) scale(1.12);
        background: radial-gradient(circle at var(--rr-logo-mx) var(--rr-logo-my), rgba(255,255,255,.15), transparent 24%), radial-gradient(circle, rgba(254,221,0,.11), transparent 48%), radial-gradient(circle, rgba(0,151,57,.11), transparent 64%);
    }
    72% {
        opacity: .54;
        transform: translateZ(0) scale(1.02);
        background: radial-gradient(circle at var(--rr-logo-mx) var(--rr-logo-my), rgba(255,255,255,.12), transparent 24%), radial-gradient(circle, rgba(248,250,252,.09), transparent 48%), radial-gradient(circle, rgba(200,148,46,.10), transparent 64%);
    }
}
</style>
</div>

<!-- Modal Wallet / Pix -->
<div class="rr-modal" id="rrWalletModal" aria-hidden="true">
    <div class="rr-modal__dialog" style="max-width:440px;">
        <div class="rr-modal__head">
            <div><h3 class="rr-modal__title">Meus Ganhos</h3><p class="rr-meta">Resumo financeiro</p></div>
            <button class="rr-modal__close" type="button" data-close-modal="rrWalletModal"><i class="fas fa-xmark"></i></button>
        </div>
        <div style="display:grid; gap: 14px;">
            <div class="rr-box" style="text-align: center; border-color: rgba(34, 197, 94, 0.4); background: rgba(34, 197, 94, 0.08); padding: 22px;">
                <small style="color: #4ade80; text-transform: uppercase; font-weight: 800; letter-spacing: 0.1em; display:block; margin-bottom: 6px;"><i class="fas fa-trophy"></i> Total Ganho em Bolões</small>
                <div style="font-size: 2.2rem; font-weight: 900; color: #fff; letter-spacing: -0.04em;" id="rrWalletTotalWon">R$ 0,00</div>
            </div>
            
            <div class="rr-box" style="text-align: center; border-color: rgba(59, 130, 246, 0.4); background: rgba(59, 130, 246, 0.08); padding: 22px;">
                <small style="color: #60a5fa; text-transform: uppercase; font-weight: 800; letter-spacing: 0.1em; display:block; margin-bottom: 6px;"><i class="fas fa-wallet"></i> Saldo a Receber</small>
                <div style="font-size: 2.2rem; font-weight: 900; color: #fff; letter-spacing: -0.04em;" id="rrWalletBalance">R$ 0,00</div>
                
            </div>
        </div>
    </div>
</div>

<!-- Modal Profile / Notification Setup -->
