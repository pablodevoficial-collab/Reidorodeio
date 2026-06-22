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
            <h1 class="brand-title">REGRAS DA <span class="highlight highlight-orange">ARENA X1</span></h1>
            <p class="last-updated">Última atualização: {{ now()->format('d/m/Y') }}</p>
            <div class="hero-divider orange"></div>
        </header>

        <!-- Content -->
        <div class="legal-content">

            <div class="legal-section">
                <h2 class="text-orange">🎯 O que é a Arena X1?</h2>
                <p>A <strong>Arena X1</strong> é o modo de participação direta entre dois jogadores. Cada um escolhe um competidor de rodeio e, durante o evento ao vivo, vence quem chegar mais longe na prova! É adrenalina pura — sem pontuação, sem cálculos. O resultado é decidido na arena, em tempo real.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">📋 Como Funciona</h2>
                <ul class="legal-list list-orange">
                    <li><strong>Criação da Sala:</strong> O anfitrião cria a sala, escolhe o evento, a modalidade e o valor da sala.</li>
                    <li><strong>Pagamento:</strong> O anfitrião paga via PIX para confirmar a sala. A sala fica aberta para oponentes.</li>
                    <li><strong>Entrada do Oponente:</strong> Outro jogador entra na sala, escolhe seu competidor e paga o mesmo valor.</li>
                    <li><strong>Evento ao Vivo:</strong> Durante o rodeio, se um competidor errar, automaticamente decidirá o vencedor da sala X1.</li>
                    <li><strong>Resultado:</strong> Ao final, o sistema determina o vencedor automaticamente e o prêmio é creditado.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">💰 Taxas e Prêmios</h2>
                <p>O prêmio total é calculado assim: <strong>(entrada × 2) - taxa da plataforma</strong>.</p>
                <div style="background: rgba(249, 115, 22, 0.08); border: 1px solid rgba(249, 115, 22, 0.2); border-radius: 12px; padding: 1.5rem; margin: 1.5rem 0;">
                    <p style="margin-bottom: 0.75rem;"><strong style="color: #f97316;">Salas até R$ 1.000:</strong></p>
                    <ul class="legal-list list-orange" style="margin: 0 0 1rem 0;">
                        <li>Usuário normal: <strong>10%</strong> de taxa</li>
                        <li>Usuário Premium: <strong>7%</strong> de taxa</li>
                    </ul>
                    <p style="margin-bottom: 0.75rem;"><strong style="color: #f97316;">Salas acima de R$ 1.000:</strong></p>
                    <ul class="legal-list list-orange" style="margin: 0;">
                        <li>Usuário normal: <strong>15%</strong> de taxa</li>
                        <li>Usuário Premium: <strong>10%</strong> de taxa</li>
                    </ul>
                </div>
                <p><strong>Exemplo:</strong> Sala de R$ 100 (usuário normal) → Prêmio total = R$ 200 - 10% = <strong>R$ 180,00</strong> para o vencedor.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">🏆 Definição do Vencedor</h2>
                <p>A Arena X1 <strong>não usa sistema de pontuação</strong>. O resultado é decidido diretamente na prova:</p>
                <ul class="legal-list list-orange">
                    <li>Vence o jogador cujo competidor <strong>chegar mais longe na modalidade ou divisão</strong>.</li>
                    <li>Se um competidor errar durante a prova, o adversário vence automaticamente.</li>
                    <li>Se ambos errarem, vence quem avançou mais armadas antes de errar.</li>
                    <li>Em caso de empate, o prêmio é dividido igualmente entre os jogadores.</li>
                    <li>O prêmio é creditado automaticamente no saldo do vencedor.</li>
                </ul>

                <div style="background: rgba(249, 115, 22, 0.08); border: 1px solid rgba(249, 115, 22, 0.2); border-radius: 12px; padding: 1.5rem; margin: 1.5rem 0;">
                    <p style="margin-bottom: 0.75rem;"><strong style="color: #f97316;">📊 Competidores de Divisões Diferentes</strong></p>
                    <p style="margin-bottom: 0.75rem;">Quando os competidores/grupos são de divisões diferentes, vale quem chegar mais longe, ganhar ou atirar o maior número de armadas. Porém, <strong>ganhar em uma divisão maior vale mais</strong>:</p>
                    <ul class="legal-list list-orange" style="margin: 0;">
                        <li>Ganhar na <strong>Força A</strong> vale mais que ganhar na Força B</li>
                        <li>Ganhar na <strong>Força B</strong> vale mais que ganhar na Força C</li>
                        <li>E assim por diante — divisões superiores têm prioridade</li>
                    </ul>
                </div>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">⚠️ Regras Importantes</h2>
                <ul class="legal-list list-orange">
                    <li>Cada jogador deve ter pelo menos <strong>18 anos</strong> para participar.</li>
                    <li>O pagamento via PIX é obrigatório para confirmar a entrada na sala.</li>
                    <li>Salas não pagas dentro do prazo são canceladas automaticamente.</li>
                    <li>Tentativas de fraude ou manipulação resultam em <strong>banimento permanente</strong>.</li>
                    <li>Contestações fraudulentas resultam no bloqueio dos fundos e da conta.</li>
                    <li>O uso de contas múltiplas é proibido e resultará em banimento.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-orange">👑 Benefícios Premium</h2>
                <p>Usuários <strong>Premium</strong> têm taxas reduzidas em todas as salas X1, além de acesso a estatísticas avançadas, mudança de username e outros benefícios exclusivos.</p>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="legal-landing-cta">
            <a href="{{ url('/') }}" class="mega-button btn-orange">
                <span class="btn-text">VOLTAR À ARENA</span>
                <span class="btn-icon">🤠</span>
            </a>
        </div>
    </div>
</div>
@endsection
