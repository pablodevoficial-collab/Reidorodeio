<button type="button" hidden aria-hidden="true" data-open-register></button>
<div class="rr-modal" hidden data-auth-modal>
    <div class="rr-modal__backdrop" data-close-modal></div>
    <div class="rr-modal__dialog">
        <button class="rr-modal__close" type="button" data-close-modal>&times;</button>
        <form class="rr-form-step" data-register-form>
            <div class="rr-step-panel" data-step-panel="mobile">
                <h2>Receba avisos da arena</h2>
                <p>Digite seu WhatsApp para verificar se ele já está disponível.</p>
                <input type="text" name="mobile" placeholder="WhatsApp" inputmode="numeric" required>
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-check-mobile><span>Verificar</span></button>
            </div>
            <div class="rr-step-panel" style="display:none" data-step-panel="password">
                <h2>Crie sua senha</h2>
                <p>Agora escolha a senha que será usada no acesso à arena.</p>
                <input type="password" name="password" placeholder="Senha" required>
                <input type="password" name="password_confirmation" placeholder="Confirme a senha" required>
                <button class="arena-button arena-button--solid rr-step-button" type="submit"><span>Continuar</span></button>
            </div>
            <div class="rr-form-step__feedback" data-register-feedback></div>
        </form>
        <form class="rr-form-step" style="display:none" data-profile-form>
            <div class="rr-step-panel" data-profile-panel="cpf">
                <h2>Complete o perfil para receber prêmios</h2>
                <p>Vamos validar seu CPF antes de seguir.</p>
                <input type="text" name="cpf" placeholder="CPF" inputmode="numeric" required>
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-check-cpf><span>Verificar CPF</span></button>
            </div>
            <div class="rr-step-panel" style="display:none" data-profile-panel="name">
                <h2>Qual é o seu nome?</h2>
                <p>Informe o nome completo do perfil que vai receber prêmios.</p>
                <input type="text" name="fullname" placeholder="Nome completo" required>
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-next-profile><span>Continuar</span></button>
            </div>
            <div class="rr-step-panel" style="display:none" data-profile-panel="birthdate">
                <h2>Ultimo passo</h2>
                <p>Agora confirme sua data de nascimento.</p>
                <input type="date" name="birthdate" required>
                <button class="arena-button arena-button--solid rr-step-button" type="submit"><span>Finalizar cadastro</span></button>
            </div>
            <div class="rr-form-step__feedback" data-profile-feedback></div>
        </form>
    </div>
</div>
