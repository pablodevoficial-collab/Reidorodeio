    <section class="rr-stage rr-auth-screen rr-hidden" id="rrAuthStage">
        <div class="rr-auth-shell">
            <div class="rr-panel rr-auth-panel" style="position:relative;">
                <button class="rr-modal__close" type="button" id="rrAuthClose" aria-label="Fechar acesso" style="position:absolute; top:18px; right:18px;">
                    <i class="fas fa-xmark"></i>
                </button>
                <div class="rr-auth-question" id="rrAuthQuestion">
                    <div class="rr-pill"><i class="fas fa-bolt"></i> Bolão online</div>
                    <h2 class="rr-title">Você já tem conta?</h2>
                    <p class="rr-copy">Escolha uma opção para continuar.</p>
                    <div class="rr-choice__grid">
                        <button class="rr-choice__btn" type="button" data-auth-mode-trigger="login">Sim</button>
                        <button class="rr-choice__btn" type="button" data-auth-mode-trigger="register">Não</button>
                    </div>
                </div>

                <div class="rr-auth-flow" id="rrAuthFlow">
                    <button class="rr-auth-back" type="button" id="rrAuthBack">
                        <i class="fas fa-chevron-left"></i>
                        Voltar
                    </button>
                    <div id="rrAuthFeedback" class="rr-hidden" style="width:100%"></div>
                    <form class="rr-auth__form" id="rrLoginForm" novalidate>
                        <div class="rr-pill"><i class="fas fa-bolt"></i> Bolão online</div>
                        <h2 class="rr-title" style="font-size:clamp(2rem,4vw,2.8rem)">Entrar</h2>
                        <div class="rr-field">
                            <label for="rrLoginCpf">CPF</label>
                            <input class="rr-input" id="rrLoginCpf" name="cpf" type="text" inputmode="numeric" maxlength="14" autocomplete="username" placeholder="000.000.000-00">
                        </div>
                        <div class="rr-field">
                            <label for="rrLoginPassword">Senha</label>
                            <input class="rr-input" id="rrLoginPassword" name="password" type="password" minlength="6" autocomplete="current-password" placeholder="Sua senha">
                        </div>
                        <button class="rr-btn rr-btn--primary" type="submit">Entrar</button>
                    </form>
                    <form class="rr-auth__form" id="rrRegisterForm" novalidate>
                        <div class="rr-pill"><i class="fas fa-bolt"></i> Bolão online</div>
                        <h2 class="rr-title" style="font-size:clamp(2rem,4vw,2.8rem)">Criar cadastro</h2>
                        <div class="rr-field">
                            <label for="rrRegisterCpf">CPF</label>
                            <input class="rr-input" id="rrRegisterCpf" name="cpf" type="text" inputmode="numeric" maxlength="14" autocomplete="username" placeholder="000.000.000-00">
                        </div>
                        <div class="rr-field">
                            <label for="rrRegisterPassword">Senha</label>
                            <input class="rr-input" id="rrRegisterPassword" name="password" type="password" minlength="6" autocomplete="new-password" placeholder="Crie uma senha">
                        </div>
                        <div class="rr-field">
                            <label for="rrRegisterPasswordConfirmation">Confirmar senha</label>
                            <input class="rr-input" id="rrRegisterPasswordConfirmation" name="password_confirmation" type="password" minlength="6" autocomplete="new-password" placeholder="Repita a senha">
                        </div>
                        <button class="rr-btn rr-btn--primary" type="submit">Criar cadastro</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="rr-screen-loader" id="rrScreenLoader" role="status" aria-live="polite">
        <div class="rr-screen-loader__panel">
            <img class="rr-screen-loader__logo" src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio">
            <p class="rr-screen-loader__eyebrow">Rei do Rodeio</p>
            <h2 class="rr-screen-loader__title" id="rrScreenLoaderTitle">Carregando ambiente seguro</h2>
            <div class="rr-screen-loader__bar" aria-hidden="true">
                <div class="rr-screen-loader__progress" id="rrScreenLoaderProgress"></div>
            </div>
            <p class="rr-screen-loader__meta" id="rrScreenLoaderMeta">Preparando sua experiência</p>
        </div>
    </div>
