    <div class="rr-modal" id="rrTeamModal" aria-hidden="true">
        <style>
            #rrTeamModal .rr-modal__dialog {
                max-width: 1080px;
                max-height: min(92vh, 920px);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            #rrTeamModal .rr-modal__head {
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }

            #rrTeamModal .rr-team-toolbar {
                margin-bottom: 12px;
            }

            #rrTeamModal .rr-team-refresh {
                min-height: 42px;
                padding: 0 14px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 14px;
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.9), rgba(8, 47, 73, 0.95));
                color: #e0f2fe;
                font-weight: 900;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                cursor: pointer;
                white-space: nowrap;
            }

            #rrTeamModal .rr-team-refresh span {
                display: inline-flex;
            }

            #rrTeamModal .rr-team-refresh:disabled {
                opacity: 0.6;
                cursor: wait;
            }

            @media (min-width: 768px) {
                #rrTeamModal .rr-modal__dialog {
                    display: grid;
                    grid-template-columns: minmax(0, 1.6fr) minmax(300px, 0.84fr);
                    grid-template-rows: auto auto auto minmax(0, 1fr);
                    gap: 12px 18px;
                    align-items: start;
                }

                #rrTeamModal .rr-modal__head,
                #rrTeamModal .rr-team-toolbar,
                #rrTeamModal #rrTeamModalFeedback {
                    grid-column: 1 / -1;
                }

                #rrTeamModal .rr-modal__head {
                    margin-bottom: 0;
                }

                #rrTeamModal .rr-team-toolbar {
                    margin-bottom: 0;
                }

                #rrTeamModal .rr-team-list-shell {
                    grid-column: 1;
                    grid-row: 4;
                    height: min(58vh, 560px);
                    min-height: 360px;
                    margin-bottom: 0;
                    overflow: hidden;
                }

                #rrTeamModal .rr-team-search {
                    height: 100%;
                    padding: 16px;
                    overflow: hidden;
                }

                #rrTeamModal .rr-team-scroll {
                    height: 100%;
                    max-height: 100%;
                }

                #rrTeamModal .rr-team {
                    grid-column: 2;
                    grid-row: 4;
                    height: min(58vh, 560px);
                    min-height: 360px;
                    display: block;
                    overflow: hidden;
                }

                #rrTeamModal .rr-team-modal__selection {
                    height: 100%;
                    padding: 18px;
                    border-radius: 24px;
                    justify-content: flex-start;
                    background: rgba(10, 17, 35, 0.82);
                    border: 1px solid rgba(255, 255, 255, 0.06);
                }

                #rrTeamModal .rr-team-modal__selection h4 {
                    margin-bottom: 12px;
                    font-size: 1rem;
                }

                #rrTeamModal .rr-slots {
                    grid-template-columns: 1fr;
                    gap: 10px;
                    margin-bottom: 14px;
                }

                #rrTeamModal .rr-team-member {
                    min-height: 78px;
                }

                #rrTeamModal #rrConfirmTeamButton {
                    width: 100%;
                    min-height: 48px;
                    margin-top: auto;
                }

                #rrTeamModal .rr-note {
                    margin-top: 10px;
                    font-size: 0.8rem;
                    line-height: 1.4;
                    color: #94a3b8;
                }
            }

            #rrTeamModal .rr-team {
                align-items: stretch;
            }

            #rrTeamModal .rr-box {
                min-height: 0;
                display: flex;
                flex-direction: column;
            }

            #rrTeamModal .rr-team-list-shell {
                position: relative;
                min-height: 0;
                flex: 1;
                margin-bottom: 12px;
            }

            #rrTeamModal .rr-team-list-shell::after {
                content: '';
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                height: 58px;
                border-radius: 0 0 22px 22px;
                background: linear-gradient(180deg, rgba(5, 10, 24, 0), rgba(5, 10, 24, 0.9) 78%, rgba(5, 10, 24, 0.98));
                opacity: 0;
                pointer-events: none;
                transition: opacity 180ms ease;
            }

            #rrTeamModal .rr-team-list-shell.has-more::after {
                opacity: 1;
            }

            #rrTeamModal .rr-team-search {
                display: flex;
                flex-direction: column;
                flex: 1;
                min-height: 0;
                overflow: hidden;
                padding: 14px;
                border-radius: 22px;
                border: 1px solid rgba(255, 255, 255, 0.06);
                background: rgba(10, 17, 35, 0.86);
            }

            #rrTeamModal .rr-team-scroll {
                flex: 1;
                min-height: 0;
                overflow-y: auto;
                overscroll-behavior: contain;
                padding-right: 4px;
                scrollbar-gutter: stable;
            }

            #rrTeamModal .rr-competitors {
                grid-template-columns: 1fr;
            }

            #rrTeamModal .rr-competitor {
                grid-template-columns: 64px minmax(0, 1fr) auto;
                gap: 14px;
                border-radius: 20px;
                padding: 14px 16px;
            }

            #rrTeamModal .rr-competitor img {
                width: 64px;
                height: 64px;
                border-radius: 18px;
            }

            #rrTeamModal .rr-competitor__meta {
                margin-top: 4px;
                color: #94a3b8;
                font-size: 0.82rem;
                font-weight: 700;
            }

            #rrTeamModal .rr-competitor__add {
                min-width: 96px;
                min-height: 40px;
                border-radius: 14px;
                padding: 0 12px;
                border: 1px solid transparent;
                background: linear-gradient(135deg, #f59e0b, #ea580c);
                box-shadow: 0 12px 24px rgba(249, 115, 22, 0.22);
            }

            #rrTeamModal .rr-competitor__add--remove {
                background: linear-gradient(135deg, #ef4444, #b91c1c);
                box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2);
            }

            #rrTeamModal .rr-competitor__add--locked {
                background: linear-gradient(180deg, #6b7280, #4b5563);
                border-color: rgba(15, 23, 42, 0.5);
                color: #f8fafc;
                box-shadow: none;
            }

            #rrTeamModal .rr-competitor__add--locked i {
                color: #020617;
            }

            #rrTeamModal #rrConfirmTeamButton {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            #rrTeamModal #rrConfirmTeamButton.rr-btn--locked {
                background: linear-gradient(180deg, #6b7280, #4b5563);
                border-color: rgba(15, 23, 42, 0.5);
                color: #f8fafc;
                box-shadow: none;
            }

            #rrTeamModal #rrConfirmTeamButton.rr-btn--locked i {
                color: #020617;
            }

            #rrTeamModal .rr-slots {
                grid-template-columns: 1fr;
            }

            .rr-team-member {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                border-radius: 18px;
                border: 1px solid rgba(255, 255, 255, 0.08);
                background: rgba(15, 23, 42, 0.76);
            }

            .rr-team-member__avatar {
                width: 54px;
                height: 54px;
                flex: none;
                border-radius: 16px;
                object-fit: cover;
                background: rgba(255, 255, 255, 0.06);
            }

            .rr-team-member__avatar--empty {
                display: grid;
                place-items: center;
                color: #94a3b8;
            }

            .rr-team-member__content {
                min-width: 0;
                flex: 1;
                display: grid;
                gap: 6px;
            }

            .rr-team-member__badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                width: fit-content;
                padding: 4px 10px;
                border-radius: 999px;
                background: rgba(245, 158, 11, 0.16);
                border: 1px solid rgba(245, 158, 11, 0.24);
                color: #fdba74;
                font-size: 0.72rem;
                font-weight: 900;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }

            .rr-team-member__top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }

            .rr-team-member__name {
                min-width: 0;
                color: #fff;
                font-weight: 800;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .rr-team-member__remove {
                width: 34px;
                height: 34px;
                border: 0;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.08);
                color: #fca5a5;
                cursor: pointer;
                flex: none;
            }

            .rr-team-member--empty .rr-team-member__name {
                color: #94a3b8;
                font-weight: 700;
            }

            @media (max-width: 767px) {
                #rrTeamModal .rr-modal__dialog {
                    max-height: 94vh;
                    padding: 14px;
                }

                #rrTeamModal .rr-modal__head {
                    margin-bottom: 8px;
                }

                #rrTeamModal .rr-modal__close,
                #rrTeamModal .rr-team-refresh {
                    width: 34px;
                    min-width: 34px;
                    height: 34px;
                    min-height: 34px;
                    padding: 0;
                    border-radius: 12px;
                }

                #rrTeamModal .rr-team-refresh span {
                    display: none;
                }

                #rrTeamModal .rr-team-toolbar {
                    margin-bottom: 8px;
                }

                #rrTeamModal .rr-search {
                    min-height: 42px;
                    padding: 0 14px;
                    border-radius: 14px;
                }

                #rrTeamModal .rr-team-scroll {
                    max-height: 38vh;
                }

                #rrTeamModal .rr-team {
                    grid-template-columns: 1fr;
                    gap: 0;
                }

                #rrTeamModal .rr-team-modal__selection {
                    padding: 12px 10px 10px;
                }

                #rrTeamModal .rr-team-list-shell {
                    order: 1;
                    width: calc(100% + 12px);
                    margin: 0 -6px 10px;
                }

                #rrTeamModal .rr-team-search {
                    padding: 10px 8px 14px;
                    border-radius: 18px;
                }

                #rrTeamModal .rr-box h4 {
                    margin-bottom: 8px;
                    font-size: 0.9rem;
                }

                #rrTeamModal .rr-slots {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 6px;
                    margin-bottom: 8px;
                }

                #rrTeamModal .rr-team-member {
                    padding: 8px 6px;
                    gap: 6px;
                    border-radius: 14px;
                    flex-direction: column;
                    align-items: stretch;
                    text-align: center;
                }

                #rrTeamModal .rr-team-member__avatar,
                #rrTeamModal .rr-team-member__avatar--empty {
                    width: 100%;
                    aspect-ratio: 1;
                    height: auto;
                    border-radius: 14px;
                }

                #rrTeamModal .rr-team-member__content {
                    gap: 2px;
                }

                #rrTeamModal .rr-team-member__top {
                    justify-content: center;
                }

                #rrTeamModal .rr-team-member__name {
                    font-size: 0.66rem;
                    white-space: normal;
                    line-height: 1.05;
                    text-align: center;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }

                #rrTeamModal .rr-team-member__badge {
                    justify-self: center;
                    margin: 0 auto;
                    font-size: 0;
                    padding: 4px 6px;
                    gap: 0;
                }

                #rrTeamModal .rr-team-member__badge i {
                    font-size: 0.7rem;
                }

                #rrTeamModal .rr-team-member__remove {
                    position: absolute;
                    top: 4px;
                    right: 4px;
                    width: 22px;
                    height: 22px;
                    font-size: 0.7rem;
                }

                #rrTeamModal .rr-team-member {
                    position: relative;
                }

                #rrTeamModal .rr-team-member--empty .rr-team-member__remove {
                    display: none;
                }

                #rrTeamModal .rr-team-member .rr-meta {
                    display: none;
                }

                #rrTeamModal .rr-competitor {
                    grid-template-columns: 40px minmax(0, 1fr) auto;
                    align-items: center;
                    gap: 8px;
                    padding: 8px 10px;
                    border-radius: 14px;
                }

                #rrTeamModal .rr-competitor img {
                    width: 40px;
                    height: 40px;
                    border-radius: 12px;
                }

                #rrTeamModal .rr-competitor__name {
                    font-size: 0.82rem;
                    line-height: 1.1;
                }

                #rrTeamModal .rr-competitor__meta {
                    display: none;
                }

                #rrTeamModal .rr-competitor__add {
                    min-width: 56px;
                    min-height: 30px;
                    padding: 0 10px;
                    border-radius: 10px;
                    font-size: 0.72rem;
                    box-shadow: none;
                }

                #rrTeamModal .rr-note {
                    display: none;
                }

                #rrTeamModal #rrConfirmTeamButton {
                    min-height: 42px;
                    border-radius: 14px;
                    font-size: 0.88rem;
                    margin-top: auto;
                }

                #rrTeamModal .rr-team-toast {
                    width: min(calc(100% - 20px), 320px);
                    bottom: 12px;
                    padding: 10px 12px;
                    border-radius: 16px;
                }

                #rrTeamModal .rr-team-toast span {
                    font-size: 0.78rem;
                }
            }

            #rrTeamModal .rr-team-toast {
                position: absolute;
                left: 50%;
                bottom: 18px;
                transform: translate(-50%, 18px);
                width: min(calc(100% - 28px), 360px);
                padding: 12px 14px;
                border-radius: 18px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                background: rgba(15, 23, 42, 0.96);
                box-shadow: 0 20px 38px rgba(15, 23, 42, 0.34);
                color: #e2e8f0;
                display: flex;
                align-items: center;
                gap: 10px;
                opacity: 0;
                pointer-events: none;
                transition: opacity 180ms ease, transform 180ms ease;
                z-index: 5;
            }

            #rrTeamModal .rr-team-toast.is-visible {
                opacity: 1;
                transform: translate(-50%, 0);
            }

            #rrTeamModal .rr-team-toast i {
                width: 28px;
                height: 28px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: none;
                background: rgba(255, 255, 255, 0.08);
            }

            #rrTeamModal .rr-team-toast span {
                min-width: 0;
                font-size: 0.86rem;
                font-weight: 700;
                line-height: 1.3;
            }

            #rrTeamModal .rr-team-toast--success i {
                color: #4ade80;
            }

            #rrTeamModal .rr-team-toast--error i {
                color: #fda4af;
            }
        </style>
        <div class="rr-modal__dialog" style="position:relative;">
            <div class="rr-modal__head">
                <button class="rr-team-refresh" id="rrRefreshCompetitorsButton" type="button" aria-label="Atualizar competidores"><i class="fas fa-rotate"></i><span>Atualizar</span></button>
                <button class="rr-modal__close" type="button" data-close-modal="rrTeamModal" aria-label="Fechar modal de equipe"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="rr-team-toolbar">
                <input class="rr-search" id="rrCompetitorSearch" type="search" placeholder="Buscar competidor">
            </div>
            <div id="rrTeamModalFeedback" class="rr-hidden"></div>
            <div class="rr-team-list-shell has-more" id="rrTeamListShell">
                <div class="rr-team-search">
                    <div class="rr-team-scroll" id="rrTeamScroll">
                        <div class="rr-competitors" id="rrCompetitorsGrid"></div>
                    </div>
                </div>
            </div>
            <div class="rr-team">
                <div class="rr-box rr-team-modal__selection">
                    <h4>Sua equipe</h4>
                    <div class="rr-slots" id="rrTeamSlots"></div>
                    <button class="rr-btn rr-btn--primary" id="rrConfirmTeamButton" type="button">Confirmar entrada</button>
                    <p class="rr-note">Escolha 4 competidores. O primeiro vira Capitão.</p>
                    <div class="rr-pix rr-hidden" id="rrPixBox"></div>
                </div>
            </div>
            <div class="rr-team-toast" id="rrTeamToast" aria-live="polite" aria-atomic="true"></div>
        </div>
    </div>

    <!-- Modal Regras do Bolão -->
