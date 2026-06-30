<button type="button" hidden aria-hidden="true" data-open-register></button>
<div class="rr-modal" hidden data-auth-modal>
    <div class="rr-modal__backdrop" data-close-modal></div>
    <div class="rr-modal__dialog">
        <button class="rr-modal__close" type="button" data-close-modal>&times;</button>

        <section class="rr-auth-choice" data-auth-panel="choice">
            <h2>Entre na arena</h2>
            <p>Escolha como deseja continuar. Depois disso, abrimos o próximo passo certinho.</p>
            <div class="rr-auth-choice__actions">
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-auth-action="login">
                    <span>Login</span>
                </button>
                <button class="arena-button arena-button--ghost rr-step-button" type="button" data-auth-action="register">
                    <span>Cadastro</span>
                </button>
            </div>
            <div class="rr-form-step__feedback" data-auth-choice-feedback></div>
        </section>

        <form class="rr-form-step" style="display:none" data-login-form>
            <div class="rr-step-panel">
                <h2>Fazer login</h2>
                <p>Entre com seu CPF ou WhatsApp e sua senha para continuar.</p>
                <input type="text" name="identifier" placeholder="CPF ou WhatsApp" inputmode="numeric" required>
                <input type="password" name="password" placeholder="Senha" required>
                <button class="arena-button arena-button--solid rr-step-button" type="submit">
                    <span>Entrar</span>
                </button>
                <button class="rr-auth-link" type="button" data-auth-back>Voltar</button>
            </div>
            <div class="rr-form-step__feedback" data-login-feedback></div>
        </form>

        <form class="rr-form-step" style="display:none" data-register-form>
            <div class="rr-step-panel" data-step-panel="mobile">
                <h2>Receba avisos da arena</h2>
                <p>Digite seu WhatsApp para verificar se ele já está disponível.</p>
                <input type="text" name="mobile" placeholder="WhatsApp" inputmode="numeric" required>
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-check-mobile><span>Verificar</span></button>
                <button class="rr-auth-link" type="button" data-auth-back>Voltar</button>
            </div>
            <div class="rr-step-panel" style="display:none" data-step-panel="password">
                <h2>Crie sua senha</h2>
                <p>Agora escolha a senha que será usada no acesso à arena.</p>
                <input type="password" name="password" placeholder="Senha" required>
                <input type="password" name="password_confirmation" placeholder="Confirme a senha" required>
                <button class="arena-button arena-button--solid rr-step-button" type="submit"><span>Continuar</span></button>
                <button class="rr-auth-link" type="button" data-auth-back>Voltar</button>
            </div>
            <div class="rr-form-step__feedback" data-register-feedback></div>
        </form>

    </div>
</div>
