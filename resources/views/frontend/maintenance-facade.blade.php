@php
    $pageTitle = 'Rei do Rodeio | Manutenção';
    $hideChrome = true;
    $bodyClass = trim(($bodyClass ?? '') . ' rr-bolao-shell');
@endphp

@extends('frontend.layouts.app')

@push('style')
<style>
    body.rr-bolao-shell {
        margin: 0 !important;
        padding: 0 !important;
        background:
            linear-gradient(125deg, rgba(255, 181, 53, 0.08), transparent 32%),
            linear-gradient(235deg, rgba(31, 182, 165, 0.08), transparent 36%),
            linear-gradient(180deg, #061018 0%, #08131d 52%, #050d15 100%) !important;
    }

    .rr-main,
    .rr-main > .rr-site-shell {
        width: 100% !important;
        max-width: 100% !important;
        min-height: 100dvh !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .rr-maintenance {
        min-height: 100dvh;
        display: grid;
        place-items: center;
        padding: 24px;
    }

    .rr-maintenance__shell {
        width: min(960px, 100%);
        min-height: calc(100dvh - 48px);
        display: grid;
        align-content: center;
        justify-items: center;
        gap: 22px;
        padding: 32px 28px;
        border-inline: 1px solid rgba(255, 255, 255, 0.08);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02)),
            rgba(9, 16, 24, 0.92);
        box-shadow: 0 28px 64px rgba(0, 0, 0, 0.28);
    }

    .rr-maintenance__logo-frame {
        position: relative;
        width: clamp(156px, 16vw, 210px);
        aspect-ratio: 1;
        display: grid;
        place-items: center;
        border-radius: 28px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: linear-gradient(180deg, rgba(7, 12, 18, 0.98), rgba(10, 16, 22, 0.94));
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.08),
            0 18px 42px rgba(0, 0, 0, 0.34);
    }

    .rr-maintenance__logo-frame::before {
        content: "";
        position: absolute;
        inset: -18%;
        border-radius: 42px;
        background:
            radial-gradient(circle at 28% 26%, rgba(255, 181, 53, 0.28), transparent 36%),
            radial-gradient(circle at 74% 34%, rgba(34, 197, 94, 0.16), transparent 34%),
            radial-gradient(circle at 64% 78%, rgba(31, 182, 165, 0.22), transparent 38%);
        filter: blur(18px);
        opacity: 0.82;
        z-index: 0;
    }

    .rr-maintenance__logo {
        position: relative;
        z-index: 1;
        width: 84%;
        height: auto;
        object-fit: contain;
        filter: drop-shadow(0 16px 28px rgba(255, 166, 24, 0.22));
    }

    .rr-maintenance__title {
        margin: 0;
        display: grid;
        gap: 4px;
        justify-items: center;
        color: #fff8ef;
        text-align: center;
        text-transform: uppercase;
        font-family: "Ethnocentric", "Inter", system-ui, sans-serif;
        font-size: clamp(2rem, 5vw, 3.1rem);
        line-height: 0.96;
        letter-spacing: 0;
    }

    .rr-maintenance__status {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 999px;
        border: 1px solid rgba(255, 181, 53, 0.24);
        background: rgba(255, 181, 53, 0.08);
        color: #ffe7bf;
        font-size: 0.84rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .rr-maintenance__copy {
        width: min(580px, 100%);
        margin: 0;
        color: #d7e0ea;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.6;
        text-align: center;
    }

    .rr-maintenance__footer {
        color: #9fb0c2;
        font-size: 0.9rem;
        font-weight: 600;
        text-align: center;
    }

    @media (max-width: 767px) {
        .rr-maintenance {
            padding: 0;
        }

        .rr-maintenance__shell {
            width: 100%;
            min-height: 100dvh;
            padding: 20px 16px;
            gap: 18px;
            border-inline: 0;
        }
    }
</style>
@endpush

@section('content')
<section class="rr-maintenance" aria-label="Site em manutenção">
    <div class="rr-maintenance__shell">
        <div class="rr-maintenance__logo-frame" aria-hidden="true">
            <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio" class="rr-maintenance__logo">
        </div>

        <h1 class="rr-maintenance__title">
            <span>REI DO</span>
            <span>RODEIO</span>
        </h1>

        <div class="rr-maintenance__status">
            <i class="fas fa-screwdriver-wrench" aria-hidden="true"></i>
            <span>Ambiente em manutenção</span>
        </div>

        <p class="rr-maintenance__copy">
            Estamos realizando ajustes operacionais. O acesso aos ambientes está temporariamente indisponível.
        </p>

        <div class="rr-maintenance__footer">
            Retorne em breve.
        </div>
    </div>
</section>
@endsection
