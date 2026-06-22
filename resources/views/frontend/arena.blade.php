@extends('frontend.layouts.app')

@section('body-class', 'front-shell')

@section('content')
<main class="arena-page">
    <section class="arena-hero">
        <img class="arena-hero__logo" src="{{ asset('assets/images/logo/logorei.png') }}" alt="Rei do Rodeio">
        <span class="arena-hero__eyebrow">Ambiente do bolao</span>
        <h1>A arena esta pronta para a montagem da equipe.</h1>
        <p>
            Esta pagina ja funciona como entrada oficial do ambiente. No proximo passo, a gente pluga aqui o seletor
            real de rodeios, os cards do bolao e a montagem da equipe.
        </p>
        <div class="arena-hero__actions">
            <a class="arena-button arena-button--solid" href="/admin">Abrir admin</a>
            <a class="arena-button arena-button--ghost" href="{{ route('home') }}">Voltar para a abertura</a>
        </div>
    </section>
</main>
@endsection
