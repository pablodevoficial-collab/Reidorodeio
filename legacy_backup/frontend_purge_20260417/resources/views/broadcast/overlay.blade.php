<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🔴 LIVE - Rei do Rodeio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Orbitron:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fonts/ethnocentric.css') }}">
    <style>
        /* ══════════════════════════════════════════
           BROADCAST OVERLAY — OBS Fullscreen
           100% Responsive (vw/vh) — sem scroll
           ══════════════════════════════════════════ */
        :root {
            --bg-deep:    #060a14;
            --bg-card:    #0d1220;
            --bg-card2:   #111827;
            --border:     rgba(249,115,22,0.18);
            --border-glow: rgba(249,115,22,0.4);
            --orange:     #f97316;
            --gold:       #fbbf24;
            --green:      #22c55e;
            --blue:       #3b82f6;
            --red:        #ef4444;
            --cyan:       #06b6d4;
            --text:       #f1f5f9;
            --text-dim:   rgba(241,245,249,0.45);
            --text-mid:   rgba(241,245,249,0.7);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: var(--bg-deep);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: var(--text);
        }

        /* ═══ MAIN GRID ═══
           Top bar 7.4vh
           Below: left 50% | right 50%
           Left: mini-live 18.5vhhhh + X1 panel + Winners panel
           Right: Bolão panel + Ranking panel
        */
        .bcast {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 7.4vh 1fr;
            height: 100vh;
        }

        /* ═══ TOP BAR ═══ */
        .topbar {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            padding: 0 1.67vw;
            background: linear-gradient(180deg, #0d1220 0%, #0a0f1c 100%);
            border-bottom: 2px solid var(--border);
            gap: 1.04vw;
        }
        .topbar__brand {
            display: flex;
            align-items: center;
            gap: 0.73vw;
            flex-shrink: 0;
        }
        .topbar__logo {
            width: 5.2vh; height: 5.2vh;
            border-radius: 0.63vw;
            filter: drop-shadow(0 2px 10px rgba(249,115,22,0.5));
        }
        .topbar__name {
            font-family: 'Ethnocentric', 'Orbitron', sans-serif;
            font-weight: 900;
            font-size: 1.25vw;
            letter-spacing: 0.1em;
            background: linear-gradient(135deg, #f97316, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .topbar__rodeio {
            font-size: 0.78vw;
            color: var(--text-mid);
            font-weight: 500;
        }
        .topbar__stats {
            display: flex;
            align-items: center;
            gap: 0.63vw;
            margin-left: auto;
        }
        .spill {
            display: flex;
            align-items: center;
            gap: 0.42vw;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 0.52vw;
            padding: 0.74vh 0.83vw;
            font-size: 0.78vw;
            font-weight: 600;
            color: var(--text-mid);
        }
        .spill__val {
            font-weight: 900;
            font-size: 0.96vw;
        }
        .spill__val--orange { color: var(--orange); }
        .spill__val--green  { color: var(--green); }
        .spill__val--gold   { color: var(--gold); }
        .spill__val--blue   { color: var(--blue); }
        .live-badge {
            display: flex;
            align-items: center;
            gap: 0.42vw;
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.35);
            border-radius: 0.52vw;
            padding: 0.74vh 1.04vw;
            font-weight: 900;
            font-size: 0.78vw;
            color: #ef4444;
            letter-spacing: 0.12em;
        }
        .live-dot {
            width: 1.1vh; height: 1.1vh;
            border-radius: 50%;
            background: #ef4444;
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(239,68,68,0.6); }
            50% { opacity: 0.5; box-shadow: 0 0 0 8px rgba(239,68,68,0); }
        }

        /* ═══ LEFT HALF ═══ */
        .left-half {
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.05);
            overflow: hidden;
        }

        /* Mini live (small) */
        .mini-live {
            position: relative;
            height: 18.5vh;
            aspect-ratio: 16 / 9;
            margin: 0 auto;
            flex-shrink: 0;
            background: #000;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .mini-live iframe { width: 100%; height: 100%; border: none; }
        .mini-live__off {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center; gap: 0.63vwvw;
            background: linear-gradient(145deg, #0d1220, #060a14);
        }
        .mini-live__off img { width: 5.56vh; height: 5.56vh; opacity: 0.2; border-radius: 0.52vw; }
        .mini-live__off span { color: var(--text-dim); font-size: 0.78vw; }
        .mini-live__tag {
            position: absolute; top: 0.74vh; right: 0.52vw;
            background: rgba(239,68,68,0.9); color: #fff;
            font-size: 0.54vw; font-weight: 800;
            padding: 0.28vh 0.52vw; border-radius: 0.31vw;
            letter-spacing: 0.08em; z-index: 2;
        }

        /* ═══ RIGHT HALF ═══ */
        .right-half {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ═══ PANEL (generic) ═══ */
        .panel {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }
        .panel__hdr {
            display: flex;
            align-items: center;
            gap: 0.52vw;
            padding: 1.3vh 1.04vw;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            border-top: 1px solid rgba(255,255,255,0.04);
            flex-shrink: 0;
        }
        .panel__icon { font-size: 1.08vw; }
        .panel__title {
            font-weight: 800;
            font-size: 0.83vw;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text);
        }
        .panel__badge {
            margin-left: auto;
            background: rgba(249,115,22,0.12);
            color: var(--orange);
            font-size: 0.71vw;
            font-weight: 900;
            padding: 0.28vh 0.63vw;
            border-radius: 0.42vw;
        }
        .panel__body {
            flex: 1;
            overflow: hidden;
            position: relative;
        }
        .panel__scroll {
            position: absolute; inset: 0;
            overflow-y: auto;
            padding: 0.74vh 0.73vw;
            scrollbar-width: none;
        }
        .panel__scroll::-webkit-scrollbar { display: none; }

        /* ═══ X1 CARD ═══ */
        .x1c {
            display: flex; align-items: center; gap: 0.73vw;
            padding: 1.1vh 0.73vw; margin-bottom: 0.56vh;
            background: var(--bg-card); border-radius: 0.63vw;
            border: 1px solid rgba(255,255,255,0.06);
            animation: slideIn 0.5s ease-out;
        }
        .x1c.is-new { border-color: var(--orange); box-shadow: 0 0 16px rgba(249,115,22,0.25); }
        .x1c__vs { display: flex; align-items: center; gap: 0.31vw; flex-shrink: 0; }
        .x1c__av {
            width: 3.7vh; height: 3.7vh; border-radius: 50%;
            object-fit: cover; border: 2px solid rgba(255,255,255,0.12);
            background: var(--bg-card2);
        }
        .x1c__av--host { border-color: var(--orange); }
        .x1c__av--opp { border-color: var(--blue); }
        .x1c__av--wait {
            border-color: rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            color: var(--text-dim); font-size: 0.83vw; background: rgba(255,255,255,0.03);
        }
        .x1c__vsico { font-size: 0.67vw; font-weight: 900; color: var(--red); }
        .x1c__info { flex: 1; min-width: 0; }
        .x1c__names {
            font-size: 0.79vw; font-weight: 700; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .x1c__meta { font-size: 0.67vw; color: var(--text-dim); margin-top: 0.19vh; }
        .x1c__prize { flex-shrink: 0; text-align: right; }
        .x1c__prize-val { font-weight: 900; font-size: 0.92vw; color: var(--green); }
        .x1c__prize-lbl { font-size: 0.58vw; color: var(--text-dim); text-transform: uppercase; }
        .x1c__st {
            flex-shrink: 0; padding: 0.37vh 0.63vw; border-radius: 0.42vw;
            font-size: 0.63vw; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.06em;
        }
        .x1c__st--open { background: rgba(34,197,94,0.12); color: var(--green); border: 1px solid rgba(34,197,94,0.3); }
        .x1c__st--in_progress { background: rgba(249,115,22,0.12); color: var(--orange); border: 1px solid rgba(249,115,22,0.3); animation: stPulse 2s ease-in-out infinite; }
        @keyframes stPulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

        /* ═══ BOLÃO CARD ═══ */
        .blc {
            display: flex; align-items: center; gap: 0.73vw;
            padding: 1.1vh 0.73vw; margin-bottom: 0.56vh;
            background: var(--bg-card); border-radius: 0.63vw;
            border: 1px solid rgba(255,255,255,0.06);
            animation: slideIn 0.5s ease-out;
        }
        .blc.is-new { border-color: var(--gold); box-shadow: 0 0 16px rgba(251,191,36,0.25); }
        .blc__av {
            width: 3.7vh; height: 3.7vh; border-radius: 50%;
            object-fit: cover; border: 2px solid rgba(255,255,255,0.12);
            flex-shrink: 0; background: var(--bg-card2);
        }
        .blc__info { flex: 1; min-width: 0; }
        .blc__user {
            font-size: 0.79vw; font-weight: 700; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .blc__team {
            font-size: 0.67vw; color: var(--cyan); font-weight: 600;
            margin-top: 0.19vh; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .blc__league { font-size: 0.63vw; color: var(--text-dim); margin-top: 0.09vh; }
        .blc__price {
            flex-shrink: 0; padding: 0.37vh 0.73vw; border-radius: 0.42vw;
            font-weight: 900; font-size: 0.71vw;
        }
        .blc__price--20 { background: rgba(234,179,8,0.12); color: #eab308; border: 1px solid rgba(234,179,8,0.3); }
        .blc__price--50 { background: rgba(34,197,94,0.12); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
        .blc__price--100 { background: rgba(249,115,22,0.12); color: #f97316; border: 1px solid rgba(249,115,22,0.3); }
        .blc__price--premium { background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); }

        /* ═══ WINNER CARD ═══ */
        .wnc {
            display: flex; align-items: center; gap: 0.73vw;
            padding: 1.1vh 0.73vw; margin-bottom: 0.56vh;
            background: var(--bg-card); border-radius: 0.63vw;
            border: 1px solid rgba(255,255,255,0.06);
            animation: slideIn 0.5s ease-out;
        }
        .wnc.is-new { border-color: var(--green); box-shadow: 0 0 16px rgba(34,197,94,0.25); }
        .wnc__av {
            width: 3.7vh; height: 3.7vh; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--green);
            flex-shrink: 0; background: var(--bg-card2);
        }
        .wnc__info { flex: 1; min-width: 0; }
        .wnc__name { font-size: 0.79vw; font-weight: 700; color: var(--text); }
        .wnc__detail { font-size: 0.67vw; color: var(--text-dim); margin-top: 0.19vh; }
        .wnc__prize { flex-shrink: 0; font-weight: 900; font-size: 0.96vw; color: var(--green); }

        /* ═══ RANK CARD ═══ */
        .rkc {
            display: flex; align-items: center; gap: 0.73vw;
            padding: 1.1vh 0.73vw; margin-bottom: 0.46vh;
            background: var(--bg-card); border-radius: 0.63vw;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .rkc:nth-child(1) { border-color: rgba(251,191,36,0.35); background: linear-gradient(90deg, rgba(251,191,36,0.08), transparent); }
        .rkc:nth-child(2) { border-color: rgba(192,192,192,0.3); background: linear-gradient(90deg, rgba(192,192,192,0.05), transparent); }
        .rkc:nth-child(3) { border-color: rgba(205,127,50,0.3); background: linear-gradient(90deg, rgba(205,127,50,0.05), transparent); }
        .rkc__pos {
            width: 3.15vh; height: 3.15vh; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 0.71vw; flex-shrink: 0;
            background: rgba(255,255,255,0.06); color: var(--text-mid);
        }
        .rkc:nth-child(1) .rkc__pos { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #000; font-size: 0.83vw; }
        .rkc:nth-child(2) .rkc__pos { background: linear-gradient(135deg, #d1d5db, #9ca3af); color: #000; }
        .rkc:nth-child(3) .rkc__pos { background: linear-gradient(135deg, #cd7f32, #b8690e); color: #fff; }
        .rkc__av {
            width: 3.5vh; height: 3.5vh; border-radius: 50%;
            object-fit: cover; border: 2px solid rgba(255,255,255,0.12);
            flex-shrink: 0; background: var(--bg-card2);
        }
        .rkc__info { flex: 1; min-width: 0; }
        .rkc__name {
            font-size: 0.79vw; font-weight: 700; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .rkc__wins { font-size: 0.63vw; color: var(--text-dim); margin-top: 0.19vh; }
        .rkc__prize { flex-shrink: 0; text-align: right; }
        .rkc__prize-val { font-weight: 900; font-size: 0.88vw; color: var(--gold); }
        .rkc__prize-lbl { font-size: 0.54vw; color: var(--text-dim); text-transform: uppercase; }

        /* ═══ EMPTY ═══ */
        .empty {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 100%;
            color: var(--text-dim); font-size: 0.83vw; gap: 0.74vh;
            padding: 2.22vh; text-align: center;
        }
        .empty__ico { font-size: 1.67vw; opacity: 0.35; }

        /* ═══ ANIMATIONS ═══ */
        @keyframes slideIn { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }

        /* ═══ TOAST ═══ */
        .toast-wrap {
            position: fixed; bottom: 2.6vh; left: 1.46vw; z-index: 100;
            display: flex; flex-direction: column-reverse; gap: 0.93vh;
        }
        .toast {
            display: flex; align-items: center; gap: 0.63vw;
            padding: 1.3vh 1.15vw;
            background: rgba(13,18,32,0.95);
            border: 1px solid var(--border-glow); border-radius: 0.63vw;
            font-size: 0.83vw; font-weight: 700; color: var(--text);
            box-shadow: 0 4px 30px rgba(0,0,0,0.5);
            animation: tIn 0.4s ease-out, tOut 0.4s ease-in 4.6s forwards;
            backdrop-filter: blur(8px);
        }
        .toast--winner { border-color: rgba(34,197,94,0.4); }
        .toast--x1 { border-color: rgba(249,115,22,0.4); }
        .toast--bolao { border-color: rgba(251,191,36,0.4); }
        @keyframes tIn { from{opacity:0;transform:translateX(-30px)} to{opacity:1;transform:translateX(0)} }
        @keyframes tOut { from{opacity:1} to{opacity:0;transform:translateX(-30px)} }

        ::-webkit-scrollbar { display: none; }
        * { scrollbar-width: none; }

        .av-fb {
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: var(--text-dim); font-weight: 800; font-size: 0.71vw;
        }
    </style>
</head>
<body>
    <div class="bcast">
        <!-- ═══ TOP BAR ═══ -->
        <header class="topbar">
            <div class="topbar__brand">
                <img class="topbar__logo" src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Logo">
                <div>
                    <div class="topbar__name">REI DO RODEIO</div>
                    <div class="topbar__rodeio" id="rodeioName">{{ $activeRodeio?->name ?? 'Carregando...' }}</div>
                </div>
            </div>
            <div class="topbar__stats">
                <div class="spill">⚔️ X1 Ativas <span class="spill__val spill__val--orange" id="statX1Active">0</span></div>
                <div class="spill">✅ Finalizadas <span class="spill__val spill__val--green" id="statX1Finished">0</span></div>
                <div class="spill">🎯 Equipes <span class="spill__val spill__val--blue" id="statBolaoTeams">0</span></div>
                <div class="spill">💰 Premiação <span class="spill__val spill__val--gold" id="statPrizePool">R$ 0</span></div>
                <div class="live-badge"><div class="live-dot"></div>AO VIVO</div>
            </div>
        </header>

        <!-- ═══ LEFT HALF ═══ -->
        <div class="left-half">
            <div class="mini-live">
                @if($liveStreamEmbedUrl)
                    <div class="mini-live__tag">🔴 LIVE</div>
                    <iframe src="{{ $liveStreamEmbedUrl }}" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                @else
                    <div class="mini-live__off">
                        <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="">
                        <span>Transmissão indisponível</span>
                    </div>
                @endif
            </div>

            <!-- X1 Rooms -->
            <div class="panel">
                <div class="panel__hdr">
                    <span class="panel__icon">⚔️</span>
                    <span class="panel__title">Salas X1</span>
                    <span class="panel__badge" id="x1Count">0</span>
                </div>
                <div class="panel__body">
                    <div class="panel__scroll" id="x1List">
                        <div class="empty"><span class="empty__ico">⚔️</span>Nenhuma sala X1 ativa</div>
                    </div>
                </div>
            </div>

            <!-- Latest Winners -->
            <div class="panel">
                <div class="panel__hdr">
                    <span class="panel__icon">🏆</span>
                    <span class="panel__title">Últimos Ganhadores X1</span>
                </div>
                <div class="panel__body">
                    <div class="panel__scroll" id="winnersList">
                        <div class="empty"><span class="empty__ico">🏆</span>Nenhum ganhador ainda</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ RIGHT HALF ═══ -->
        <div class="right-half">
            <!-- Bolão Entries -->
            <div class="panel">
                <div class="panel__hdr">
                    <span class="panel__icon">🎯</span>
                    <span class="panel__title">Últimas Entradas — Bolão</span>
                    <span class="panel__badge" id="bolaoCount">0</span>
                </div>
                <div class="panel__body">
                    <div class="panel__scroll" id="bolaoList">
                        <div class="empty"><span class="empty__ico">🎯</span>Nenhuma entrada recente</div>
                    </div>
                </div>
            </div>

            <!-- Top Winners Ranking -->
            <div class="panel">
                <div class="panel__hdr">
                    <span class="panel__icon">👑</span>
                    <span class="panel__title">Ranking — Top Ganhadores do Evento</span>
                </div>
                <div class="panel__body">
                    <div class="panel__scroll" id="rankingList">
                        <div class="empty"><span class="empty__ico">👑</span>Ranking vazio</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-wrap" id="toastWrap"></div>

    <script>
    (function() {
        'use strict';

        const FEED = @json(route('broadcast.feed'));
        const INTERVAL = 8000;

        let prevX1 = new Set();
        let prevBl = new Set();
        let prevWn = new Set();

        function av(url, cls, name) {
            if (url && !url.includes('/default.png') && !url.endsWith('/')) {
                return `<img class="${cls}" src="${url}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"><div class="${cls} av-fb" style="display:none">${(name||'?')[0].toUpperCase()}</div>`;
            }
            return `<div class="${cls} av-fb">${(name||'?')[0].toUpperCase()}</div>`;
        }

        function brl(v) {
            return 'R$ ' + Number(v||0).toFixed(2).replace('.', ',');
        }

        function priceClass(price, premium) {
            if (premium) return 'blc__price--premium';
            if (price <= 20) return 'blc__price--20';
            if (price <= 50) return 'blc__price--50';
            return 'blc__price--100';
        }

        function priceLabel(price, premium) {
            return premium ? 'PREMIUM' : brl(price);
        }

        // ── X1 ROOMS ──
        function renderX1(rooms) {
            const el = document.getElementById('x1List');
            document.getElementById('x1Count').textContent = rooms.length;
            if (!rooms.length) { el.innerHTML = '<div class="empty"><span class="empty__ico">⚔️</span>Nenhuma sala X1 ativa</div>'; return; }
            const ids = new Set(rooms.map(r => r.id));
            let h = '';
            rooms.forEach(r => {
                const nw = !prevX1.has(r.id);
                const stL = r.status === 'in_progress' ? 'AO VIVO' : 'ABERTA';
                const stC = `x1c__st--${r.status}`;
                h += `<div class="x1c ${nw?'is-new':''}">
                    <div class="x1c__vs">
                        ${av(r.host_avatar,'x1c__av x1c__av--host',r.host_name)}
                        <span class="x1c__vsico">VS</span>
                        ${r.opponent_name ? av(r.opponent_avatar,'x1c__av x1c__av--opp',r.opponent_name) : '<div class="x1c__av x1c__av--wait av-fb">?</div>'}
                    </div>
                    <div class="x1c__info">
                        <div class="x1c__names">${r.host_name} ${r.opponent_name ? 'vs '+r.opponent_name : '— Aguardando...'}</div>
                        <div class="x1c__meta">${r.modalidade}${r.competitor ? ' • '+r.competitor : ''} • Entrada: ${brl(r.valor)}</div>
                    </div>
                    <div class="x1c__prize">
                        <div class="x1c__prize-val">${brl(r.prize)}</div>
                        <div class="x1c__prize-lbl">prêmio</div>
                    </div>
                    <span class="x1c__st ${stC}">${stL}</span>
                </div>`;
                if (nw && prevX1.size > 0) toast('x1', `⚔️ Nova X1: ${r.host_name} — ${brl(r.valor)}`);
            });
            el.innerHTML = h;
            prevX1 = ids;
        }

        // ── BOLÃO ──
        function renderBolao(entries) {
            const el = document.getElementById('bolaoList');
            document.getElementById('bolaoCount').textContent = entries.length;
            if (!entries.length) { el.innerHTML = '<div class="empty"><span class="empty__ico">🎯</span>Nenhuma entrada recente</div>'; return; }
            const ids = new Set(entries.map(e => e.id));
            let h = '';
            entries.forEach(e => {
                const nw = !prevBl.has(e.id);
                const pc = priceClass(e.league_price, e.is_premium);
                const pl = priceLabel(e.league_price, e.is_premium);
                const comps = e.competitors.map(c => c.nome).join(', ');
                h += `<div class="blc ${nw?'is-new':''}">
                    ${av(e.user_avatar,'blc__av',e.user_name)}
                    <div class="blc__info">
                        <div class="blc__user">${e.user_name}</div>
                        ${comps ? `<div class="blc__team">🐴 ${comps}</div>` : (e.team_name ? `<div class="blc__team">${e.team_name}</div>` : '')}
                        <div class="blc__league">${e.league_name} • ${e.created_at}</div>
                    </div>
                    <span class="blc__price ${pc}">${pl}</span>
                </div>`;
                if (nw && prevBl.size > 0) toast('bolao', `🎯 ${e.user_name} entrou no bolão!`);
            });
            el.innerHTML = h;
            prevBl = ids;
        }

        // ── WINNERS ──
        function renderWinners(wn) {
            const el = document.getElementById('winnersList');
            if (!wn.length) { el.innerHTML = '<div class="empty"><span class="empty__ico">🏆</span>Nenhum ganhador ainda</div>'; return; }
            const ids = new Set(wn.map(w => w.id));
            let h = '';
            wn.forEach(w => {
                const nw = !prevWn.has(w.id);
                h += `<div class="wnc ${nw?'is-new':''}">
                    ${av(w.winner_avatar,'wnc__av',w.winner_name)}
                    <div class="wnc__info">
                        <div class="wnc__name">🏆 ${w.winner_name}</div>
                        <div class="wnc__detail">${w.modalidade} • ${w.finished_at}</div>
                    </div>
                    <span class="wnc__prize">+${brl(w.prize)}</span>
                </div>`;
                if (nw && prevWn.size > 0) toast('winner', `🏆 ${w.winner_name} ganhou ${brl(w.prize)}!`);
            });
            el.innerHTML = h;
            prevWn = ids;
        }

        // ── RANKING ──
        function renderRank(rk) {
            const el = document.getElementById('rankingList');
            if (!rk.length) { el.innerHTML = '<div class="empty"><span class="empty__ico">👑</span>Ranking vazio</div>'; return; }
            let h = '';
            rk.forEach((r, i) => {
                const medal = i===0?'🥇':i===1?'🥈':i===2?'🥉':'';
                h += `<div class="rkc">
                    <div class="rkc__pos">${medal||(i+1)}</div>
                    ${av(r.user_avatar,'rkc__av',r.user_name)}
                    <div class="rkc__info">
                        <div class="rkc__name">${r.user_name}</div>
                        <div class="rkc__wins">${r.wins} vitória${r.wins!==1?'s':''}</div>
                    </div>
                    <div class="rkc__prize">
                        <div class="rkc__prize-val">${brl(r.total_prize)}</div>
                        <div class="rkc__prize-lbl">total ganho</div>
                    </div>
                </div>`;
            });
            el.innerHTML = h;
        }

        // ── STATS ──
        function updateStats(s) {
            document.getElementById('statX1Active').textContent = s.total_x1_active || 0;
            document.getElementById('statX1Finished').textContent = s.total_x1_finished || 0;
            document.getElementById('statBolaoTeams').textContent = s.total_bolao_teams || 0;
            document.getElementById('statPrizePool').textContent = brl(s.total_prize_pool || 0);
            if (s.rodeio_name) {
                let sub = s.rodeio_name;
                if (s.modalidade_atual) sub += ' — ' + s.modalidade_atual;
                if (s.divisao_atual) sub += ' • ' + s.divisao_atual;
                document.getElementById('rodeioName').textContent = sub;
            }
        }

        // ── TOAST ──
        function toast(type, msg) {
            const wrap = document.getElementById('toastWrap');
            const t = document.createElement('div');
            t.className = `toast toast--${type}`;
            t.innerHTML = `<span>${msg}</span>`;
            wrap.appendChild(t);
            setTimeout(() => t.remove(), 5200);
        }

        // ── FETCH LOOP ──
        async function fetchFeed() {
            try {
                const res = await fetch(FEED, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const d = await res.json();
                renderX1(d.x1_rooms || []);
                renderBolao(d.bolao_entries || []);
                renderWinners(d.latest_winners || []);
                renderRank(d.top_winners || []);
                updateStats(d.stats || {});
            } catch (e) { console.warn('Feed error:', e); }
        }

        fetchFeed();
        setInterval(fetchFeed, INTERVAL);
        setInterval(() => document.querySelectorAll('.is-new').forEach(e => e.classList.remove('is-new')), 6000);
    })();
    </script>
</body>
</html>
