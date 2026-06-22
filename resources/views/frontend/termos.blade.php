@extends('frontend.layouts.app')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/legal-pages.css') }}">
@endpush

@section('content')
<div class="legal-page-wrapper">
    <div class="legal-background"></div>

    <div class="legal-container">
        <header class="legal-hero">
            <h1 class="brand-title">TERMOS DE <span class="highlight highlight-green">USO</span></h1>
            <p class="last-updated">Última atualização: {{ now()->format('d/m/Y') }}</p>
            <div class="hero-divider green"></div>
        </header>

        <div class="legal-content">
            <div class="legal-section">
                <h2 class="text-green">1. Aceitação</h2>
                <p>Ao acessar o Rei do Rodeio, você concorda com estes termos. Se não concordar, não utilize a plataforma ou seus recursos pagos.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">2. Cadastro e elegibilidade</h2>
                <p>Para participar dos bolões, das salas X1 e de outros serviços pagos, você precisa fornecer dados verdadeiros, manter a conta segura e ter idade mínima legal exigida para participação.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">3. Pagamentos</h2>
                <p>As entradas, assinaturas e demais cobranças podem ser processadas por PIX, cartão ou outros meios habilitados. O usuário é responsável por conferir os dados antes de confirmar o pagamento.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">4. Conduta do usuário</h2>
                <p>Fraudes, múltiplas contas, manipulação de resultados, uso indevido de dados e qualquer atividade ilícita podem gerar bloqueio da conta e retenção de créditos para análise.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">5. Prêmios e tributos</h2>
                <p>O usuário participante é integralmente responsável por declarar e pagar, quando aplicável, quaisquer impostos, tributos, taxas, encargos ou obrigações fiscais incidentes sobre prêmios, valores, bens ou benefícios recebidos por meio da plataforma.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">6. Limitação de responsabilidade</h2>
                <p>O Rei do Rodeio depende de eventos reais, internet, gateways de pagamento e terceiros. Interrupções externas, cancelamentos e falhas fora do controle da plataforma podem impactar experiências e prazos.</p>
            </div>
        </div>

        <div class="legal-landing-cta" style="gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('rules.fantasy') }}" class="mega-button btn-blue">Ver regras do bolão</a>
            <a href="{{ url('/') }}" class="mega-button btn-green">Voltar à home</a>
        </div>
    </div>
</div>
@endsection
