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
    <style>
        .fantasy-rules-highlight,
        .fantasy-rules-panel {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            --rr-rules-accent: 59, 130, 246;
            border: 1px solid rgba(var(--rr-rules-accent), 0.22);
            background: rgba(var(--rr-rules-accent), 0.08);
        }

        .fantasy-rules-highlight {
            text-align: center;
        }

        .fantasy-rules-highlight__title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: rgb(var(--rr-rules-accent));
        }

        .fantasy-rules-panel--success {
            --rr-rules-accent: 16, 185, 129;
        }

        .fantasy-rules-panel--info {
            --rr-rules-accent: 59, 130, 246;
        }

        .fantasy-rules-panel--danger {
            --rr-rules-accent: 239, 68, 68;
        }

        .fantasy-rules-panel--warning {
            --rr-rules-accent: 234, 179, 8;
        }

        .fantasy-rules-panel__title {
            margin: 0 0 1rem;
            font-size: 1.08rem;
            font-weight: 800;
            color: rgb(var(--rr-rules-accent));
        }

        .fantasy-rules-panel__text {
            margin: 0;
            color: #cbd5e1;
            line-height: 1.7;
        }

        .fantasy-rules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0.5rem;
        }

        .fantasy-rules-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.55rem 0.8rem;
            border-radius: 10px;
            background: rgba(var(--rr-rules-accent), 0.08);
        }

        .fantasy-rules-item__label {
            color: #cbd5e1;
        }

        .fantasy-rules-item__value {
            flex: none;
            font-weight: 800;
            color: rgb(var(--rr-rules-accent));
        }

        body.light .fantasy-rules-highlight,
        body.light .fantasy-rules-panel {
            box-shadow: 0 16px 30px rgba(194, 65, 12, 0.08);
        }

        body.light .fantasy-rules-highlight {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(239, 246, 255, 0.92));
        }

        body.light .fantasy-rules-panel--success {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(236, 253, 245, 0.94));
        }

        body.light .fantasy-rules-panel--info {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(239, 246, 255, 0.94));
        }

        body.light .fantasy-rules-panel--danger {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(254, 242, 242, 0.94));
        }

        body.light .fantasy-rules-panel--warning {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(254, 252, 232, 0.94));
        }

        body.light .fantasy-rules-panel__text,
        body.light .fantasy-rules-item__label {
            color: #475569;
        }

        body.light .fantasy-rules-item {
            background: rgba(var(--rr-rules-accent), 0.08);
        }

        @media (max-width: 640px) {
            .fantasy-rules-highlight,
            .fantasy-rules-panel {
                padding: 1rem;
                border-radius: 14px;
            }

            .fantasy-rules-grid {
                grid-template-columns: 1fr;
            }

            .fantasy-rules-item {
                padding: 0.55rem 0.7rem;
            }
        }
    </style>

    <div class="legal-background"></div>

    <div class="legal-container">
        <header class="legal-hero">
            <h1 class="brand-title">REGRAS DO <span class="highlight highlight-blue">BOL&Atilde;O</span></h1>
            <p class="last-updated">&Uacute;ltima atualiza&ccedil;&atilde;o: {{ now()->format('d/m/Y') }}</p>
            <div class="hero-divider blue"></div>
        </header>

        <div class="legal-content">
            <div class="legal-section">
                <h2 class="text-blue">🏇 O que &eacute; o Bol&atilde;o?</h2>
                <p>O <strong>Bol&atilde;o</strong> &eacute; o modo de liga onde voc&ecirc; monta sua equipe com competidores reais de rodeio. Seus competidores acumulam pontos com base no desempenho ao vivo, e o melhor time no ranking da liga leva o pr&ecirc;mio.</p>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">📋 Como Funciona</h2>
                <ul class="legal-list list-blue">
                    <li><strong>Escolha a Liga:</strong> Selecione uma liga aberta vinculada a um evento de rodeio.</li>
                    <li><strong>Monte sua Equipe:</strong> Escolha competidores dentro do or&ccedil;amento da liga usando o sistema de draft.</li>
                    <li><strong>Capit&atilde;o:</strong> O primeiro competidor que voc&ecirc; selecionar ser&aacute; automaticamente o capit&atilde;o da equipe e pontua em dobro.</li>
                    <li><strong>Pague a Entrada:</strong> Confirme sua equipe pagando via PIX.</li>
                    <li><strong>Acompanhe ao Vivo:</strong> Os pontos s&atilde;o atualizados em tempo real durante o evento.</li>
                    <li><strong>Ranking Final:</strong> Ao final do evento, o ranking &eacute; calculado e os pr&ecirc;mios s&atilde;o distribu&iacute;dos.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">⭐ Capit&atilde;o</h2>
                <p>O <strong>Capit&atilde;o</strong> &eacute; o primeiro competidor que voc&ecirc; seleciona ao montar sua equipe. Todos os pontos que ele conquistar durante o evento s&atilde;o <strong>multiplicados por 2</strong>. Escolha com sabedoria, porque seu Capit&atilde;o pode ser a diferen&ccedil;a entre ganhar ou perder.</p>
                <div class="fantasy-rules-highlight">
                    <p class="fantasy-rules-highlight__title">🎯 Capit&atilde;o = Pontos x 2</p>
                </div>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">⚡ Tabela de Pontua&ccedil;&atilde;o</h2>
                <p>A pontua&ccedil;&atilde;o &eacute; baseada nas a&ccedil;&otilde;es durante a prova de <strong>La&ccedil;o Comprido</strong>:</p>

                <div class="fantasy-rules-panel fantasy-rules-panel--success">
                    <p class="fantasy-rules-panel__title">✅ Armadas Positivas</p>
                    <div class="fantasy-rules-grid">
                        <div class="fantasy-rules-item">
                            <span class="fantasy-rules-item__label">Armada boa</span>
                            <strong class="fantasy-rules-item__value">+300 pts</strong>
                        </div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Limpou top com a m&atilde;o</span><strong class="fantasy-rules-item__value">+100 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Limpou top</span><strong class="fantasy-rules-item__value">+50 pts</strong></div>
                    </div>
                </div>

                <div class="fantasy-rules-panel fantasy-rules-panel--danger">
                    <p class="fantasy-rules-panel__title">🎯 Ações que Descontam Pontos</p>
                    <div class="fantasy-rules-grid">
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Pescou uma aspa</span><strong class="fantasy-rules-item__value">-50 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Limpou garupa</span><strong class="fantasy-rules-item__value">-60 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Limpou cupim (M&atilde;o)</span><strong class="fantasy-rules-item__value">-20 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Limpou cupim (Longe)</span><strong class="fantasy-rules-item__value">-50 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Limpou cola</span><strong class="fantasy-rules-item__value">-50 pts</strong></div>
                    </div>
                </div>

                <div class="fantasy-rules-panel fantasy-rules-panel--danger">
                    <p class="fantasy-rules-panel__title">❌ Erros (Penaliza&ccedil;&otilde;es)</p>
                    <div class="fantasy-rules-grid">
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Caiu do Cavalo</span><strong class="fantasy-rules-item__value">-500 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Saiu Enrolado</span><strong class="fantasy-rules-item__value">-500 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Errou por Top</span><strong class="fantasy-rules-item__value">-500 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Boi Tirou Armada</span><strong class="fantasy-rules-item__value">-250 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Boi Pulou</span><strong class="fantasy-rules-item__value">-250 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Lan&ccedil;ou por Cima</span><strong class="fantasy-rules-item__value">-350 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Queimou Raia</span><strong class="fantasy-rules-item__value">-300 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Errou por Garupa</span><strong class="fantasy-rules-item__value">-200 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Pegou a Pata</span><strong class="fantasy-rules-item__value">-250 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Uma Aspa Somente</span><strong class="fantasy-rules-item__value">-250 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Cabresteou</span><strong class="fantasy-rules-item__value">-200 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Dobrada</span><strong class="fantasy-rules-item__value">-150 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Pegou Cola</span><strong class="fantasy-rules-item__value">-150 pts</strong></div>
                        <div class="fantasy-rules-item"><span class="fantasy-rules-item__label">Pegou Pesco&ccedil;o</span><strong class="fantasy-rules-item__value">-100 pts</strong></div>
                    </div>
                </div>

                <div class="fantasy-rules-panel fantasy-rules-panel--warning">
                    <p class="fantasy-rules-panel__title">⚠️ Desqualifica&ccedil;&atilde;o</p>
                    <p class="fantasy-rules-panel__text">Competidor desqualificado <strong>mant&eacute;m a pontua&ccedil;&atilde;o e as estat&iacute;sticas j&aacute; conquistadas</strong>. A desqualifica&ccedil;&atilde;o apenas remove esse competidor, e o grupo dele se houver, da lista dispon&iacute;vel para novas escolhas no bol&atilde;o.</p>
                </div>

                <div class="fantasy-rules-panel fantasy-rules-panel--info">
                    <p class="fantasy-rules-panel__title">✏️ Pontua&ccedil;&atilde;o Personalizada</p>
                    <p class="fantasy-rules-panel__text">A&ccedil;&otilde;es especiais n&atilde;o listadas acima podem valer pontos positivos ou negativos, conforme decis&atilde;o do juiz(a) durante a transmiss&atilde;o.</p>
                </div>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">🏆 Premia&ccedil;&atilde;o</h2>
                <ul class="legal-list list-blue">
                    <li>A distribui&ccedil;&atilde;o de pr&ecirc;mios varia conforme a liga (1&ordm; lugar, top 3, etc.).</li>
                    <li>O ranking final &eacute; baseado no <strong>total de pontos</strong> acumulados pela equipe.</li>
                    <li>Os pr&ecirc;mios s&atilde;o creditados automaticamente no saldo do vencedor ao final do evento.</li>
                    <li>Ligas gratuitas tamb&eacute;m podem oferecer pr&ecirc;mios em cr&eacute;ditos na plataforma.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">⚠️ Regras Importantes</h2>
                <ul class="legal-list list-blue">
                    <li>Cada jogador pode montar <strong>quantas equipes quiser, mudando 2 competidores</strong>.</li>
                    <li>Ap&oacute;s o pagamento, a equipe &eacute; confirmada e <strong>n&atilde;o pode ser alterada</strong>.</li>
                    <li>O pagamento via PIX deve ser realizado dentro do prazo, sen&atilde;o a equipe &eacute; cancelada.</li>
                    <li>&Eacute; necess&aacute;rio ter pelo menos <strong>18 anos</strong> para participar de bol&atilde;o pago.</li>
                    <li>Tentativas de fraude ou manipula&ccedil;&atilde;o resultam em <strong>banimento permanente</strong>.</li>
                    <li>O uso de contas m&uacute;ltiplas para participar da mesma liga &eacute; proibido.</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="text-blue">📊 Acompanhamento</h2>
                <p>Durante o evento, voc&ecirc; pode acompanhar em tempo real:</p>
                <ul class="legal-list list-blue">
                    <li><strong>Ranking da Liga:</strong> Posi&ccedil;&atilde;o de todas as equipes.</li>
                    <li><strong>Posi&ccedil;&atilde;o das suas equipes:</strong> Cada a&ccedil;&atilde;o ao vivo atualiza as posi&ccedil;&otilde;es.</li>
                    <li><strong>Detalhes da sua Equipe:</strong> Veja cada equipe separadamente.</li>
                </ul>
            </div>
        </div>

        <div class="legal-landing-cta">
            <a href="{{ url('/') }}" class="mega-button btn-blue">
                <span class="btn-text">MONTAR MINHA EQUIPE</span>
                <span class="btn-icon">🏇</span>
            </a>
        </div>
    </div>
</div>
@endsection
