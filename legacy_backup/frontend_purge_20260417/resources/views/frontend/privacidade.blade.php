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
            <h1 class="brand-title">POLÍTICAS DE <span class="highlight highlight-blue">PRIVACIDADE</span></h1>
            <p class="last-updated">Última atualização: {{ now()->format('d/m/Y') }}</p>
            <div class="hero-divider blue"></div>
        </header>

        <!-- Content -->
        <div class="legal-content">
            <div class="legal-section">
                <h2 class="text-blue">1. Coleta de Dados</h2>
                <p>No <strong>Rei do Rodeio</strong>, coletamos apenas os dados essenciais para o funcionamento da plataforma e conformidade legal (KYC). Isso inclui:</p>
                <ul class="legal-list list-blue">
                    <li>Informações de Identificação: Nome, CPF, Data de Nascimento.</li>
                    <li>Dados de Contato: E-mail, Telefone.</li>
                    <li>Dados Financeiros: Chave PIX para processamento de saques.</li>
                    <li>Dados de Navegação: Endereço IP e cookies para segurança e prevenção de fraudes.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">2. Uso das Informações</h2>
                <p>Seus dados são utilizados exclusivamente para:</p>
                <ul class="legal-list list-blue">
                    <li>Processar transações financeiras (depósitos e saques).</li>
                    <li>Verificar sua identidade e impedir fraudes (Compliance).</li>
                    <li>Melhorar a experiência do usuário e personalizar o conteúdo.</li>
                    <li>Enviar notificações importantes sobre sua conta ou eventos.</li>
                </ul>
                <p><strong>Nós nunca vendemos seus dados para terceiros.</strong></p>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">3. Segurança dos Dados</h2>
                <p>Utilizamos criptografia SSL de ponta a ponta e seguimos as melhores práticas de segurança do framework Laravel. Nossos bancos de dados são protegidos e o acesso é estritamente limitado a pessoal autorizado.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">4. Seus Direitos (LGPD)</h2>
                <p>Conforme a Lei Geral de Proteção de Dados (LGPD), você tem direito a:</p>
                <ul class="legal-list list-blue">
                    <li>Acessar seus dados pessoais armazenados.</li>
                    <li>Solicitar a correção de dados incompletos ou incorretos.</li>
                    <li>Solicitar a exclusão de sua conta e dados (sujeito a retenção legal para fins fiscais).</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">5. Cookies e Rastreamento</h2>
                <p>Utilizamos cookies para manter sua sessão ativa e segura. Cookies de marketing podem ser usados para rastrear a eficácia de nossas campanhas de afiliados, garantindo que as comissões sejam pagas corretamente.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">6. Alterações na Política</h2>
                <p>Podemos atualizar esta política periodicamente. Notificaremos você sobre mudanças significativas através do e-mail cadastrado ou aviso na plataforma.</p>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="legal-landing-cta">
            <a href="{{ url('/') }}" class="mega-button btn-blue">
                <span class="btn-text">ENTENDIDO, VOLTAR</span>
                <span class="btn-icon">🛡️</span>
            </a>
        </div>
    </div>
</div>
@endsection
