<section class="rr-stats-stage rr-arena-view rr-hidden" id="rrStatsStage" aria-label="Arena Estat&iacute;sticas">
    <div class="rr-stats-stage__shell">
        <div class="rr-panel rr-stats-stage__hero">
            <button class="rr-arena-back" type="button" data-arena-target="home">
                <i class="fas fa-arrow-left"></i> Escolher outra arena
            </button>

            <div id="rrStatsFeedback" class="rr-hidden"></div>

            <div class="rr-stats-stage__hero-top">
                <div class="rr-stats-stage__hero-copy">
                    <span class="rr-stats-stage__tag"><i class="fas fa-chart-simple"></i> Arena Estat&iacute;sticas</span>
                    <h2 class="rr-stats-stage__title">Leitura premium do competidor, rodada por rodada.</h2>
                    <p class="rr-stats-stage__copy">
                        A mesma pontua&ccedil;&atilde;o lan&ccedil;ada pela transmiss&atilde;o oficial alimenta o comparativo por rodeio,
                        modalidade e divis&atilde;o.
                    </p>
                </div>

                <div class="rr-stats-stage__status" id="rrStatsSubscriptionStatus"></div>
            </div>

            <div class="rr-stats-stage__filters">
                <label class="rr-stats-stage__field">
                    <span>Rodeio</span>
                    <select id="rrStatsRodeioSelect"></select>
                </label>

                <label class="rr-stats-stage__field">
                    <span>Modalidade</span>
                    <select id="rrStatsModalidadeSelect"></select>
                </label>

                <button class="rr-stats-stage__refresh" id="rrStatsRefreshButton" type="button">
                    <i class="fas fa-rotate"></i> Atualizar
                </button>
            </div>

            <div class="rr-stats-stage__divisions" id="rrStatsDivisionChips"></div>
        </div>

        <div class="rr-stats-stage__body">
            <aside class="rr-panel rr-stats-stage__summary">
                <div class="rr-stats-stage__summary-head">
                    <div>
                        <small id="rrStatsScopeEyebrow">Arena premium</small>
                        <h3 id="rrStatsScopeTitle">Aguardando sele&ccedil;&atilde;o</h3>
                    </div>
                    <div class="rr-stats-stage__scope-logo-wrap">
                        <img id="rrStatsScopeLogo" src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio">
                    </div>
                </div>

                <div class="rr-stats-stage__summary-grid" id="rrStatsSummaryGrid"></div>
            </aside>

            <div class="rr-panel rr-stats-stage__board">
                <div class="rr-stats-stage__board-head">
                    <div>
                        <small id="rrStatsBoardEyebrow">Painel premium</small>
                        <h3 id="rrStatsBoardTitle">Estat&iacute;sticas por competidor</h3>
                        <p id="rrStatsBoardMeta">Os dados entram a partir da live oficial.</p>
                    </div>
                    <button class="rr-stats-stage__board-cta" id="rrStatsPremiumAction" type="button">
                        Liberar premium
                    </button>
                </div>

                <div class="rr-stats-stage__plans" id="rrStatsPlans"></div>
                <div class="rr-stats-stage__leaderboard" id="rrStatsLeaderboard"></div>
            </div>
        </div>
    </div>
</section>
