@extends('frontend.layouts.app')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/legal-pages.css') }}">
@endpush

@section('content')
<div class="legal-page-wrapper">
    <div class="legal-background"></div>

    <div class="legal-container">
        <header class="legal-hero">
            <h1 class="brand-title">REGRAS DO <span class="highlight highlight-orange">BOLÃO</span></h1>
            <p class="last-updated">Última atualização: {{ now()->format('d/m/Y') }}</p>
            <div class="hero-divider orange"></div>
        </header>

        <div class="legal-content">
            <div class="legal-section">
                <h2 class="text-orange">1. O que é o bolão?</h2>
                <p>O bolão é a experiência principal do Rei do Rodeio. Você escolhe uma liga, monta sua equipe de competidores e acompanha a pontuação em tempo real durante o evento.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">2. Como funciona</h2>
                <ul class="legal-list list-orange">
                    <li>Escolha uma liga ou evento disponível.</li>
                    <li>Monte sua equipe dentro das regras da modalidade.</li>
                    <li>Confirme a entrada com o pagamento solicitado.</li>
                    <li>Acompanhe a pontuação ao vivo até o fechamento do ranking.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">3. Pontuação</h2>
                <p>As ações dos competidores são pontuadas pelo time responsável pela transmissão. Pontos positivos e negativos podem alterar o ranking ao longo do evento.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">4. Regras importantes</h2>
                <ul class="legal-list list-orange">
                    <li>Não é permitido usar múltiplas contas para manipular disputas.</li>
                    <li>Os dados da conta e do pagamento precisam estar corretos.</li>
                    <li>Em caso de fraude, a conta pode ser suspensa ou banida.</li>
                    <li>Se algo parecer incorreto, fale com o suporte antes de contestar publicamente.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">5. Também leia os termos</h2>
                <p>As regras do bolão funcionam junto com os termos de uso da plataforma. Se quiser o panorama completo, abra também a página de termos.</p>
            </div>
        </div>

        <div class="legal-landing-cta" style="gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('terms') }}" class="mega-button btn-green">Termos de uso</a>
            <a href="{{ url('/') }}" class="mega-button btn-blue">Voltar à home</a>
        </div>
    </div>
</div>
@endsection