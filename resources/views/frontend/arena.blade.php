@extends('frontend.layouts.app')

@section('body-class', 'front-shell')

@section('content')
<main class="arena-page">
    <section
        class="arena-hero arena-hero--empty"
        data-register-url="{{ route('user.register') }}"
        data-check-user-url="{{ route('user.checkUser') }}"
        data-profile-url="{{ route('user.profile.update') }}"
    >
        <img class="arena-hero__logo" src="{{ asset('assets/images/logo/logorei.png') }}" alt="Rei do Rodeio">
        <span class="arena-hero__eyebrow">Arena do bolao</span>
        <h1>Nenhum evento agora.</h1>
        <p>Assim que um novo evento for cadastrado, a arena volta a abrir para o bolao e para a montagem da equipe.</p>
        @guest
        <div class="arena-hero__actions">
            <button class="arena-button arena-button--solid" type="button" data-open-register>Cadastre-se para receber notificacoes</button>
        </div>
        @endguest
    </section>
</main>

@guest
<div class="rr-modal" hidden data-auth-modal>
    <div class="rr-modal__backdrop" data-close-modal></div>
    <div class="rr-modal__dialog">
        <button class="rr-modal__close" type="button" data-close-modal>&times;</button>
        <form class="rr-form-step" data-register-form>
            <div class="rr-step-panel" data-step-panel="mobile">
                <h2>Receba avisos da arena</h2>
                <p>Digite seu WhatsApp para verificar se ele ja esta disponivel.</p>
                <input type="text" name="mobile" placeholder="WhatsApp" inputmode="numeric" required>
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-check-mobile>
                    <span>Verificar</span>
                </button>
            </div>

            <div class="rr-step-panel" hidden data-step-panel="password">
                <h2>Crie sua senha</h2>
                <p>Agora escolha a senha que sera usada no acesso a arena.</p>
                <input type="password" name="password" placeholder="Senha" required>
                <input type="password" name="password_confirmation" placeholder="Confirme a senha" required>
                <button class="arena-button arena-button--solid rr-step-button" type="submit">
                    <span>Continuar</span>
                </button>
            </div>

            <div class="rr-form-step__feedback" data-register-feedback></div>
        </form>

        <form class="rr-form-step" hidden data-profile-form>
            <div class="rr-step-panel" data-profile-panel="cpf">
                <h2>Complete o perfil para receber premios</h2>
                <p>Vamos validar seu CPF antes de seguir.</p>
                <input type="text" name="cpf" placeholder="CPF" inputmode="numeric" required>
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-check-cpf>
                    <span>Verificar CPF</span>
                </button>
            </div>

            <div class="rr-step-panel" hidden data-profile-panel="name">
                <h2>Qual e o seu nome?</h2>
                <p>Informe o nome completo do perfil que vai receber premios.</p>
                <input type="text" name="fullname" placeholder="Nome completo" required>
                <button class="arena-button arena-button--solid rr-step-button" type="button" data-next-profile>
                    <span>Continuar</span>
                </button>
            </div>

            <div class="rr-step-panel" hidden data-profile-panel="birthdate">
                <h2>Ultimo passo</h2>
                <p>Agora confirme sua data de nascimento.</p>
                <input type="date" name="birthdate" required>
                <button class="arena-button arena-button--solid rr-step-button" type="submit">
                    <span>Finalizar cadastro</span>
                </button>
            </div>

            <div class="rr-form-step__feedback" data-profile-feedback></div>
        </form>
    </div>
</div>
@endguest
@endsection
