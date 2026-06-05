const fs = require("fs");
let content = fs.readFileSync("resources/views/frontend/frontend.blade.php", "utf8");
const startStr = "    function renderRankingPodiumCard(";
const endStr = "    async function handleReminder() {";
const startIdx = content.indexOf(startStr);
const endIdx = content.indexOf(endStr);
if (startIdx > -1 && endIdx > -1) {
    const before = content.substring(0, startIdx);
    const after = content.substring(endIdx);
    const newJS = `    function renderRankingPodiumCard(item, position, distribution, prizePool, isFinished) {
        const name = item ? rankingDisplayName(item) : "Aguardando...";
        const prize = rankingPrizeForPosition(distribution, prizePool, position);
        const points = item ? rankingPointsLabel(item) : "0.00";
        const showPoints = item && (isFinished || item.is_mine);
        
        return \`<div class="rr-podium-v2__slot rr-podium-v2__slot--\${position}">
            \${rankingAvatarMarkup(item || {}, "rr-podium-v2__avatar").replace("<img", "<img style=\\"width:100%;height:100%;object-fit:cover;\\" ")}
            <div class="rr-podium-v2__base">
                <span class="rr-podium-v2__rank">\${position}</span>
                <div class="rr-podium-v2__name">\${esc(name)}</div>
                \${prize > 0 ? \`<div class="rr-podium-v2__prize">\${esc(money(prize))}</div>\` : ""}
                \${showPoints ? \`<div class="rr-podium-v2__points">\${esc(points)} pts</div>\` : ""}
            </div>
        </div>\`;
    }

    async function openRankingModal(leagueId) {
        const league = leagueById(leagueId);
        if (!league) return;
        
        const podiumContainer = document.getElementById("rrRankingPodiumContainer");
        const listContainer = document.getElementById("rrRankingList");
        const refreshBtn = document.getElementById("rrRankingRefreshBtn");
        
        if (refreshBtn) {
            refreshBtn.onclick = async function() {
                const icon = this.querySelector("i");
                if(icon) icon.classList.add("fa-spin");
                this.disabled = true;
                await openRankingModal(leagueId);
                if(icon) icon.classList.remove("fa-spin");
                this.disabled = false;
            };
        }

        if(podiumContainer) podiumContainer.innerHTML = "<div class=\\"rr-podium-wait\\"><i class=\\"fas fa-spinner fa-spin\\"></i>Carregando pódio...</div>";
        if(listContainer) listContainer.innerHTML = "";
        
        openModal(el.rankingModal);
        
        try {
            const payload = await json(config.ranking.replace("__LEAGUE__", leagueId));
            const data = payload.data || {};
            const prizePool = Number(data.prize_pool || 0);
            const distribution = data.distribution || {};
            const isFinished = league.status === "finished" || league.status === "completed" || data.status === "finished";
            
            const ranking = (Array.isArray(data.ranking) ? data.ranking : [])
                .map((item, index) => ({ ...item, position: Number(item.position || index + 1) }))
                .sort((a, b) => Number(a.position || 0) - Number(b.position || 0));
                
            const topMap = new Map(ranking.slice(0, 3).map((item) => [Number(item.position), item]));
            const podiumHtml = [2, 1, 3].map((pos) => renderRankingPodiumCard(topMap.get(pos) || null, pos, distribution, prizePool, isFinished)).join("");
            
            if(podiumContainer) podiumContainer.innerHTML = podiumHtml || "<div class=\\"rr-podium-wait\\">Pódio vazio</div>";
            
            const listRows = ranking.slice(3, 100);
            if(listContainer) {
                listContainer.innerHTML = listRows.length
                    ? listRows.map((item) => {
                        const showPointsList = isFinished || item.is_mine;
                        const pointsValList = rankingPointsLabel(item);
                        return \`<div style="display:grid; grid-template-columns: auto 1fr auto; align-items:center; gap:12px; padding:12px; background:rgba(255,255,255,0.03); border-radius:16px;">
                            <div style="width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,0.08); color:#fff; font-weight:800; display:grid; place-items:center; font-size:0.85rem;">\${item.position}</div>
                            <div style="display:flex; align-items:center; gap:10px; overflow:hidden;">
                                \${rankingAvatarMarkup(item, "rr-list-avatar").replace("class=\\"rr-list-avatar\\"", "style=\\"width:34px; height:34px; border-radius:50%; display:block; overflow:hidden;\\"").replace("<img", "<img style=\\"width:100%;height:100%;object-fit:cover;\\"")}
                                <div style="color:#fff; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:0.9rem;">\${esc(rankingDisplayName(item))}</div>
                            </div>
                            <div style="color:#cbd5e1; font-weight:800; font-size:0.85rem;">\${showPointsList ? esc(pointsValList) + " pts" : "-- pts"}</div>
                        </div>\`;
                    }).join("")
                    : "<div style=\\"text-align:center; padding:20px; color:#cbd5e1; font-size:0.85rem;\\">Nenhuma outra equipe qualificada ainda.</div>";
            }
        } catch(e) {
             if(podiumContainer) podiumContainer.innerHTML = "<div style=\\"color:#fca5a5; padding:20px;\\">" + esc(e.message) + "</div>";
        }
    }

`;
    fs.writeFileSync("resources/views/frontend/frontend.blade.php", before + newJS + after);
    console.log("JS replaced successfully");
} else {
    console.log("Failed to locate strings.");
}
