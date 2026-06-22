@extends('frontend.layouts.app')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/css/legal-pages.css') }}">
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.top === window.self) return;
    document.querySelectorAll('.legal-landing-cta a, .legal-landing-cta button, .mega-button').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            ev.preventDefault();
            window.parent.postMessage({ type: 'rr-legal-close' }, '*');
        });
    });
});
</script>
@endpush

@section('content')
<div class="legal-page-wrapper">
    <div class="legal-background"></div>
    
    <div class="legal-container">
        <!-- Header -->
        <header class="legal-hero">
            <h1 class="brand-title">TERMOS DE <span class="highlight highlight-green">USO</span></h1>
            <p class="last-updated">Última atualização: {{ now()->format('d/m/Y') }}</p>
            <div class="hero-divider green"></div>
        </header>

        <!-- Content -->
        <div class="legal-content">
            <div class="legal-section">
                <h2 class="text-green">1. Aceitação dos Termos</h2>
                <p>Ao acessar e usar a plataforma <strong>Rei do Rodeio</strong>, você concorda em cumprir e ficar vinculado aos seguintes termos e condições. Se você não concordar com qualquer parte destes termos, você não deve utilizar nossos serviços.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">2. Elegibilidade e Cadastro</h2>
                <p>Para utilizar os serviços pagos do <strong>Bolão</strong>, você deve ter pelo menos <strong>18 anos de idade</strong>. Ao criar uma conta, você garante que as informações fornecidas são verdadeiras e precisas. O uso de contas múltiplas para manipular resultados ou rankings resultará em banimento imediato.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">3. Pagamentos e Saques</h2>
                <p>Os depósitos são processados via PIX e são finais. Os saques são processados instantaneamente ou em até 24 horas, dependendo da análise de segurança. A plataforma reserva-se o direito de solicitar verificação de identidade (KYC) antes de liberar saques de valores elevados.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">4. Conduta do Usuário</h2>
                <p>É estritamente proibido usar a plataforma para lavagem de dinheiro, fraude ou qualquer atividade ilegal. O Rei do Rodeio monitora transações suspeitas e colabora com as autoridades competentes quando necessário.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-green">5. Limitação de Responsabilidade</h2>
                <p>O Rei do Rodeio não se responsabiliza por perdas decorrentes de falhas na conexão de internet do usuário, erros bancários externos ou cancelamento de eventos reais de rodeio que afetem as pontuações.</p>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="legal-landing-cta">
            <a href="{{ url('/') }}" class="mega-button btn-green">
                <span class="btn-text">CONCORDO E VOLTAR</span>
                <span class="btn-icon">✅</span>
            </a>
        </div>
    </div>
</div>
@endsection
