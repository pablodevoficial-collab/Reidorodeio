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
        <h1>Nenhum evento no momento.</h1>
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
            <h2>Receba avisos da arena</h2>
            <p>Crie seu acesso com WhatsApp e senha.</p>
            <input type="text" name="mobile" placeholder="WhatsApp" inputmode="numeric" required>
            <input type="password" name="password" placeholder="Senha" required>
            <input type="password" name="password_confirmation" placeholder="Confirme a senha" required>
            <div class="rr-form-step__feedback" data-register-feedback></div>
            <button class="arena-button arena-button--solid" type="submit">Continuar</button>
        </form>

        <form class="rr-form-step" hidden data-profile-form>
            <h2>Complete o perfil para receber premios</h2>
            <p>Agora faltam apenas os dados finais.</p>
            <input type="text" name="cpf" placeholder="CPF" inputmode="numeric" required>
            <input type="text" name="fullname" placeholder="Nome completo" required>
            <input type="date" name="birthdate" required>
            <div class="rr-form-step__feedback" data-profile-feedback></div>
            <button class="arena-button arena-button--solid" type="submit">Finalizar cadastro</button>
        </form>
    </div>
</div>
@endguest
@endsection
