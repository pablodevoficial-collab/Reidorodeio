@extends('frontend.layouts.app')

@section('body-class', 'front-shell front-shell--locked')

@section('content')
<main class="loader-screen">
    <section class="loader-card" aria-labelledby="loader-title">
        <img class="loader-card__logo" src="{{ asset('assets/images/logo/logorei.png') }}" alt="Rei do Rodeio">
        <h1 class="sr-only" id="loader-title">Rei do Rodeio</h1>
        <div class="loader-card__progress" aria-hidden="true">
            <span class="loader-card__bar"></span>
        </div>
        <p class="loader-card__status" data-loader-status>Preparando a arena do bolão...</p>
        <a class="arena-button is-locked" href="{{ route('arena') }}" aria-disabled="true" data-arena-entry>
            Entrar na arena
        </a>
    </section>
</main>
@endsection
