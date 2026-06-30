@extends('frontend.layouts.app')

@section('body-class', 'front-shell front-shell--locked')

@section('content')
<main class="loader-screen">
    <section class="loader-card" aria-labelledby="loader-title">
        <h1 class="sr-only" id="loader-title">Rei do Rodeio</h1>

        @if($loaderSponsor)
            <div class="loader-sponsor" aria-label="Patrocinador da arena">
                <div class="loader-sponsor__item">
                    <img
                        src="{{ publicStorageUrl($loaderSponsor->logo) }}"
                        alt="{{ $loaderSponsor->name }}"
                    >
                </div>
            </div>
        @endif

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
