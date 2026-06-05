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
    
    <div class="legal-container wide">
        <!-- Hero Section -->
        <header class="legal-hero">
            <div class="hero-content">
                <h1 class="brand-title">REI DO <span class="highlight highlight-orange">RODEIO</span></h1>
                <p class="brand-tagline">A plataforma oficial do rodeio com bolão e experiência pensada para web e mobile.</p>
                <div class="hero-divider orange"></div>
            </div>
        </header>

        <!-- Mission Section -->
        <section class="mission-section">
            <div class="mission-text">
                <h2>A arena digital do rodeio brasileiro</h2>
                <p>
                    O <strong>Rei do Rodeio</strong> evoluiu para uma plataforma oficial onde o público acompanha o evento,
                    consulta estatísticas reais e entra direto no bolão a partir do mesmo lugar. Hoje o site reúne
                    radar de competidores, prêmios do evento e uma navegação única pensada para funcionar bem na web e no mobile.
                </p>
                <p>
                    A proposta é simples: transformar informação oficial do rodeio em uma experiência prática, rápida e intuitiva.
                    Você entra no evento atual, encontra os competidores, acompanha o cenário da etapa e monta sua equipe
                    no bolão com uma interface feita para funcionar bem no navegador e no celular.
                </p>
            </div>
        </section>

        <!-- Features Grid -->
        <div class="features-grid">
            <div class="feature-card fantasy-card">
                <div class="card-glow"></div>
                <div class="card-content">
                    <div class="icon-wrapper">🏆</div>
                    <h3>Bolão do Evento</h3>
                    <p>O bolão foi desenhado para entrada rápida e leitura clara do prêmio. O usuário escolhe o valor da arena, monta a equipe e acompanha o ranking dentro do mesmo fluxo.</p>
                    <ul class="feature-list">
                        <li>💰 Prêmio destacado em cada arena</li>
                        <li>👥 Montagem de equipe simples e direta</li>
                        <li>📲 Experiência otimizada para mobile e desktop</li>
                    </ul>
                </div>
            </div>

            <div class="feature-card affiliate-card">
                <div class="card-glow"></div>
                <div class="card-content">
                    <div class="icon-wrapper">🔥</div>
                    <h3>Experiência Oficial do Evento</h3>
                    <p>O lançamento foi pensado para colocar o bolão no centro da experiência, com acesso rápido ao evento, visual forte e navegação enxuta para o público entrar e jogar sem fricção.</p>
                    <ul class="feature-list">
                        <li>🚀 Entrada direta no bolão pela página inicial</li>
                        <li>🎯 Fluxo focado em conversão e participação</li>
                        <li>📡 Evento e contexto do rodeio sempre em destaque</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Trust & Security -->
        <section class="trust-section">
            <div class="trust-badge">
                <span class="trust-icon">🔒</span>
                <div class="trust-info">
                    <h4>Segurança Máxima</h4>
                    <div class="trust-meta">
                        <span>CNPJ 64.681.500/0001-19</span>
                        <span>DUNS 579277622</span>
                    </div>
                </div>
            </div>
            <p class="trust-desc">
                Operamos com transparência, ambiente seguro e tecnologia própria. Seus dados, pagamentos e sessões são protegidos com criptografia,
                fluxo autenticado e uma base preparada para entregar a experiência oficial do bolão com estabilidade.
            </p>
        </section>

        <!-- CTA Section -->
        <div class="legal-landing-cta">
            <a href="{{ url('/') }}" class="mega-button btn-orange">
                <span class="btn-text">VOLTAR PARA O BOLÃO</span>
                <span class="btn-icon">🚀</span>
            </a>
        </div>
    </div>
</div>
@endsection
