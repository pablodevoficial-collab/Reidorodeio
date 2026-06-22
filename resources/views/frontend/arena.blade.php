@extends('frontend.layouts.app')

@section('body-class', 'front-shell')

@section('content')
<main class="arena-page">
    <section class="arena-hero arena-hero--empty">
        <img class="arena-hero__logo" src="{{ asset('assets/images/logo/logorei.png') }}" alt="Rei do Rodeio">
        <span class="arena-hero__eyebrow">Arena do bolao</span>
        <h1>Nenhum evento no momento.</h1>
        <p>Assim que um novo evento for cadastrado, a arena volta a abrir para o bolao e para a montagem da equipe.</p>
        @guest
        <div class="arena-hero__actions">
            <a class="arena-button arena-button--solid" href="{{ route('user.register') }}">Cadastre-se para receber notificacoes</a>
        </div>
        @endguest
    </section>
</main>
@endsection
