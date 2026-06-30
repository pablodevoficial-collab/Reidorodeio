@extends('admin.layouts.app')

@section('panel')
    @php
        $currentAdmin = auth('admin')->user();
        $canAdminAccess = static function (string $routeName) use ($currentAdmin): bool {
            return !$currentAdmin || !method_exists($currentAdmin, 'canAccessAdminRoute') || $currentAdmin->canAccessAdminRoute($routeName);
        };
        $formatMetric = static function (array $metric): string {
            $format = $metric['format'] ?? 'number';
            $value = $metric['value'] ?? 0;

            return match ($format) {
                'currency' => 'R$ ' . number_format((float) $value, 2, ',', '.'),
                'text' => (string) $value,
                default => number_format((float) $value, 0, ',', '.'),
            };
        };
        $quickLinks = [
            ['route' => 'admin.fantasy_leagues.index', 'label' => 'Bolões', 'icon' => 'las la-trophy'],
            ['route' => 'admin.fantasy_leagues.create', 'label' => 'Novo bolão', 'icon' => 'las la-plus-circle'],
            ['route' => 'admin.fantasy_leagues.entries', 'label' => 'Entradas', 'icon' => 'las la-list-ol'],
            ['route' => 'admin.fantasy_prizes.index', 'label' => 'Premiações', 'icon' => 'las la-award'],
            ['route' => 'admin.rodeios.index', 'label' => 'Rodeios', 'icon' => 'las la-calendar-alt'],
            ['route' => 'admin.modalidades.index', 'label' => 'Modalidades', 'icon' => 'las la-list'],
            ['route' => 'admin.competitors.index', 'label' => 'Competidores', 'icon' => 'las la-horse-head'],
            ['route' => 'admin.competitors.requests.index', 'label' => 'Pedidos de competidor', 'icon' => 'las la-clipboard-list'],
            ['route' => 'admin.x1.index', 'label' => 'X1 / Duelo', 'icon' => 'las la-chess-king'],
            ['route' => 'admin.live_transmission.index', 'label' => 'Ao vivo', 'icon' => 'las la-broadcast-tower'],
            ['route' => 'admin.quick_scoring.index', 'label' => 'Pontuação rápida', 'icon' => 'las la-bolt'],
            ['route' => 'admin.dynamic_selection.index', 'label' => 'Seleção dinâmica', 'icon' => 'las la-vector-square'],
            ['route' => 'admin.report.login.history', 'label' => 'Financeiro', 'icon' => 'las la-wallet'],
            ['route' => 'admin.ticket.index', 'label' => 'Suporte', 'icon' => 'las la-life-ring'],
            ['route' => 'admin.users.all', 'label' => 'Usuários', 'icon' => 'las la-users'],
            ['route' => 'admin.app_control.dashboard', 'label' => 'App Control', 'icon' => 'las la-mobile'],
        ];
        $statusClass = isset($currentRodeioSummary['status'])
            ? str_replace('_', '-', $currentRodeioSummary['status'])
            : 'programado';
    @endphp

    <div class="rr-admin-dashboard">
        <section class="rr-admin-hero">
            <div class="rr-admin-hero__copy">
                <span class="rr-admin-kicker">Admin bolão</span>
                <h2 class="rr-admin-title">Operação do bolão em primeiro plano</h2>
                <p class="rr-admin-lead">
                    Leitura consolidada do produto sem precisar abrir módulo por módulo:
                    base de usuários, operação ativa, financeiro consolidado e recorte do evento que está puxando o frontend agora.
                </p>

                <div class="rr-admin-pulse-grid">
                    @foreach ($adminPulse as $metric)
                        <article class="rr-admin-pulse-card rr-admin-pulse-card--{{ $metric['tone'] ?? 'slate' }}">
                            <span class="rr-admin-pulse-card__label">{{ $metric['label'] }}</span>
                            <strong class="rr-admin-pulse-card__value">{{ $formatMetric($metric) }}</strong>
                            @if (!empty($metric['hint']))
                                <span class="rr-admin-pulse-card__hint">{{ $metric['hint'] }}</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="rr-admin-hero__aside">
                @if ($currentRodeioSummary)
                    <article class="rr-admin-event-card">
                        <div class="rr-admin-event-card__header">
                            <span class="rr-admin-event-card__eyebrow">Bolão atual</span>
                            <span class="rr-admin-status-badge rr-admin-status-badge--{{ $statusClass }}">
                                {{ $currentRodeioSummary['status_label'] }}
                            </span>
                        </div>

                        <h3 class="rr-admin-event-card__title">{{ $currentRodeioSummary['name'] }}</h3>

                        <div class="rr-admin-event-card__meta">
                            @if (!empty($currentRodeioSummary['city']))
                                <span><i class="las la-map-marker"></i>{{ $currentRodeioSummary['city'] }}</span>
                            @endif
                            @if (!empty($currentRodeioSummary['start_label']))
                                <span><i class="las la-calendar"></i>{{ $currentRodeioSummary['start_label'] }}</span>
                            @endif
                            @if (!empty($currentRodeioSummary['end_label']))
                                <span><i class="las la-flag-checkered"></i>{{ $currentRodeioSummary['end_label'] }}</span>
                            @endif
                        </div>

                        <div class="rr-admin-event-card__chips">
                            <span class="rr-admin-chip">
                                <i class="las la-layer-group"></i>
                                {{ $currentRodeioSummary['modalidade_atual'] ?: 'Sem modalidade' }}
                            </span>
                            <span class="rr-admin-chip">
                                <i class="las la-sitemap"></i>
                                {{ $currentRodeioSummary['divisao_atual'] ?: 'Sem divisão' }}
                            </span>
                            <span class="rr-admin-chip">
                                <i class="las la-sync"></i>
                                Atualizado {{ $currentRodeioSummary['updated_human'] ?: 'agora' }}
                            </span>
                        </div>

                        <div class="rr-admin-event-card__stats">
                            @foreach ($currentRodeioSummary['pulse'] as $metric)
                                <div class="rr-admin-event-mini">
                                    <span>{{ $metric['label'] }}</span>
                                    <strong>{{ $formatMetric($metric) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @else
                    <article class="rr-admin-empty-card">
                        <span class="rr-admin-empty-card__eyebrow">Bolão atual</span>
                        <h3>Nenhum bolão em destaque</h3>
                        <p>Quando houver bolão ativo, programado ou em transmissão, o painel vai resumir aqui o evento que está puxando o frontend.</p>
                    </article>
                @endif
            </aside>
        </section>

        <section class="rr-admin-section">
            <div class="rr-admin-section__header">
                <div>
                    <span class="rr-admin-section__eyebrow">Acesso rápido</span>
                    <h3 class="rr-admin-section__title">Atalhos do bolão</h3>
                    <p class="rr-admin-section__subtitle">Entradas rápidas para os módulos que mais impactam a operação.</p>
                </div>
            </div>

            <div class="rr-admin-links-grid">
                @foreach ($quickLinks as $link)
                    @if ($canAdminAccess($link['route']))
                        <a href="{{ route($link['route']) }}" class="rr-admin-link-card">
                            <span class="rr-admin-link-card__icon"><i class="{{ $link['icon'] }}"></i></span>
                            <span class="rr-admin-link-card__text">{{ $link['label'] }}</span>
                            <i class="las la-arrow-right rr-admin-link-card__arrow"></i>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="rr-admin-section">
            <div class="rr-admin-section__header">
                <div>
                    <span class="rr-admin-section__eyebrow">Agora</span>
                    <h3 class="rr-admin-section__title">Radar do bolão</h3>
                    <p class="rr-admin-section__subtitle">O que precisa de atenção imediata na operação.</p>
                </div>
            </div>

            <div class="rr-admin-metrics-grid">
                @foreach ($systemNow as $metric)
                    <article class="rr-admin-metric-card rr-admin-metric-card--{{ $metric['tone'] ?? 'slate' }}">
                        <span class="rr-admin-metric-card__label">{{ $metric['label'] }}</span>
                        <strong class="rr-admin-metric-card__value">{{ $formatMetric($metric) }}</strong>
                        @if (!empty($metric['hint']))
                            <p class="rr-admin-metric-card__hint">{{ $metric['hint'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        @foreach ($wholePeriodSections as $section)
            <section class="rr-admin-section">
                <div class="rr-admin-section__header">
                    <div>
                        <span class="rr-admin-section__eyebrow">Todo o período</span>
                        <h3 class="rr-admin-section__title">{{ $section['title'] }}</h3>
                        <p class="rr-admin-section__subtitle">{{ $section['subtitle'] }}</p>
                    </div>
                </div>

                <div class="rr-admin-metrics-grid rr-admin-metrics-grid--dense">
                    @foreach ($section['items'] as $metric)
                        <article class="rr-admin-metric-card rr-admin-metric-card--{{ $metric['tone'] ?? 'slate' }}">
                            <span class="rr-admin-metric-card__label">{{ $metric['label'] }}</span>
                            <strong class="rr-admin-metric-card__value">{{ $formatMetric($metric) }}</strong>
                            @if (!empty($metric['hint']))
                                <p class="rr-admin-metric-card__hint">{{ $metric['hint'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="rr-admin-section">
            <div class="rr-admin-section__header">
                <div>
                    <span class="rr-admin-section__eyebrow">Bolão atual</span>
                    <h3 class="rr-admin-section__title">Resumo operacional do evento em destaque</h3>
                    <p class="rr-admin-section__subtitle">Recorte direto de modalidades, pontuação, X1 e bolão dentro do evento atual.</p>
                </div>
            </div>

            @if ($currentRodeioSummary)
                <div class="rr-admin-current-grid">
                    <article class="rr-admin-panel">
                        <div class="rr-admin-panel__header">
                            <h4>Radar técnico do bolão</h4>
                            <span>Estado geral da operação</span>
                        </div>
                        <div class="rr-admin-metrics-grid rr-admin-metrics-grid--compact">
                            @foreach ($currentRodeioSummary['pulse'] as $metric)
                                <article class="rr-admin-metric-card rr-admin-metric-card--{{ $metric['tone'] ?? 'slate' }}">
                                    <span class="rr-admin-metric-card__label">{{ $metric['label'] }}</span>
                                    <strong class="rr-admin-metric-card__value">{{ $formatMetric($metric) }}</strong>
                                    @if (!empty($metric['hint']))
                                        <p class="rr-admin-metric-card__hint">{{ $metric['hint'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </article>

                    <article class="rr-admin-panel">
                        <div class="rr-admin-panel__header">
                            <h4>X1 dentro do rodeio</h4>
                            <span>Salas, volume e receita</span>
                        </div>
                        <div class="rr-admin-metrics-grid rr-admin-metrics-grid--compact">
                            @foreach ($currentRodeioSummary['x1'] as $metric)
                                <article class="rr-admin-metric-card rr-admin-metric-card--{{ $metric['tone'] ?? 'slate' }}">
                                    <span class="rr-admin-metric-card__label">{{ $metric['label'] }}</span>
                                    <strong class="rr-admin-metric-card__value">{{ $formatMetric($metric) }}</strong>
                                    @if (!empty($metric['hint']))
                                        <p class="rr-admin-metric-card__hint">{{ $metric['hint'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </article>

                    <article class="rr-admin-panel">
                        <div class="rr-admin-panel__header">
                            <h4>Bolão dentro do rodeio</h4>
                            <span>Ligas, equipes, entradas e casa</span>
                        </div>
                        <div class="rr-admin-metrics-grid rr-admin-metrics-grid--compact">
                            @foreach ($currentRodeioSummary['fantasy'] as $metric)
                                <article class="rr-admin-metric-card rr-admin-metric-card--{{ $metric['tone'] ?? 'slate' }}">
                                    <span class="rr-admin-metric-card__label">{{ $metric['label'] }}</span>
                                    <strong class="rr-admin-metric-card__value">{{ $formatMetric($metric) }}</strong>
                                    @if (!empty($metric['hint']))
                                        <p class="rr-admin-metric-card__hint">{{ $metric['hint'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </article>
                </div>
            @else
                <article class="rr-admin-empty-card rr-admin-empty-card--full">
                    <span class="rr-admin-empty-card__eyebrow">Sem bolão atual</span>
                    <h3>O bloco do evento atual aparece automaticamente</h3>
                    <p>Assim que houver bolão programado, ao vivo ou ativo, esta área passa a consolidar o recorte daquele evento para facilitar a operação.</p>
                </article>
            @endif
        </section>
    </div>
@endsection

@push('style')
<style>
.rr-admin-dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.4rem;
}

.rr-admin-hero,
.rr-admin-section,
.rr-admin-panel,
.rr-admin-event-card,
.rr-admin-empty-card {
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    background:
        radial-gradient(circle at top right, rgba(249, 115, 22, 0.08), transparent 28%),
        linear-gradient(145deg, rgba(20, 24, 38, 0.96), rgba(11, 15, 27, 0.98));
    box-shadow: 0 24px 44px rgba(0, 0, 0, 0.22);
}

.rr-admin-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(320px, .95fr);
    gap: 1.25rem;
    padding: 1.4rem;
}

.rr-admin-hero__copy,
.rr-admin-hero__aside,
.rr-admin-panel {
    min-width: 0;
}

.rr-admin-kicker,
.rr-admin-section__eyebrow,
.rr-admin-empty-card__eyebrow,
.rr-admin-event-card__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.14);
    border: 1px solid rgba(249, 115, 22, 0.24);
    color: #fdba74;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.rr-admin-title {
    margin: .9rem 0 .55rem;
    color: #f8fafc;
    font-size: clamp(1.6rem, 2.2vw, 2.3rem);
    line-height: 1.05;
    font-weight: 900;
}

.rr-admin-lead,
.rr-admin-section__subtitle,
.rr-admin-empty-card p {
    margin: 0;
    color: #94a3b8;
    font-size: .97rem;
    line-height: 1.6;
}

.rr-admin-pulse-grid,
.rr-admin-links-grid,
.rr-admin-metrics-grid,
.rr-admin-current-grid,
.rr-admin-event-card__stats {
    display: grid;
    gap: .95rem;
}

.rr-admin-pulse-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    margin-top: 1.2rem;
}

.rr-admin-pulse-card,
.rr-admin-metric-card,
.rr-admin-link-card,
.rr-admin-event-mini {
    position: relative;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.07);
    background: rgba(12, 18, 32, 0.72);
    overflow: hidden;
}

.rr-admin-pulse-card,
.rr-admin-metric-card {
    padding: 1rem 1rem .95rem;
}

.rr-admin-pulse-card::before,
.rr-admin-metric-card::before {
    content: '';
    position: absolute;
    inset: 0 auto 0 0;
    width: 3px;
    background: var(--metric-accent, #64748b);
}

.rr-admin-pulse-card__label,
.rr-admin-metric-card__label,
.rr-admin-event-mini span {
    display: block;
    color: #94a3b8;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.rr-admin-pulse-card__value,
.rr-admin-metric-card__value,
.rr-admin-event-mini strong {
    display: block;
    margin-top: .45rem;
    color: #f8fafc;
    font-size: 1.55rem;
    line-height: 1.05;
    font-weight: 900;
}

.rr-admin-pulse-card__hint,
.rr-admin-metric-card__hint {
    margin: .55rem 0 0;
    color: #94a3b8;
    font-size: .86rem;
    line-height: 1.45;
}

.rr-admin-event-card {
    height: 100%;
    padding: 1.2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.rr-admin-event-card__header,
.rr-admin-section__header,
.rr-admin-panel__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.rr-admin-event-card__title,
.rr-admin-section__title,
.rr-admin-empty-card h3,
.rr-admin-panel__header h4 {
    margin: .65rem 0 0;
    color: #f8fafc;
    font-weight: 900;
    line-height: 1.08;
}

.rr-admin-event-card__title {
    font-size: clamp(1.45rem, 2vw, 1.95rem);
    margin-top: 0;
}

.rr-admin-section {
    padding: 1.2rem;
}

.rr-admin-section__title {
    font-size: 1.28rem;
}

.rr-admin-links-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    margin-top: 1rem;
}

.rr-admin-link-card {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: 1rem 1.05rem;
    text-decoration: none;
    transition: transform .18s ease, border-color .18s ease, background .18s ease;
}

.rr-admin-link-card:hover {
    transform: translateY(-2px);
    border-color: rgba(249, 115, 22, 0.26);
    background: rgba(15, 23, 42, 0.92);
}

.rr-admin-link-card__icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.24), rgba(37, 99, 235, 0.24));
    color: #fdba74;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.rr-admin-link-card__text {
    color: #f8fafc;
    font-size: .95rem;
    font-weight: 800;
}

.rr-admin-link-card__arrow {
    margin-left: auto;
    color: #64748b;
}

.rr-admin-metrics-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    margin-top: 1rem;
}

.rr-admin-metrics-grid--dense {
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
}

.rr-admin-current-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.rr-admin-panel {
    padding: 1.1rem;
}

.rr-admin-panel__header h4 {
    font-size: 1.02rem;
    margin: 0;
}

.rr-admin-panel__header span {
    color: #94a3b8;
    font-size: .84rem;
}

.rr-admin-event-card__meta,
.rr-admin-event-card__chips {
    display: flex;
    flex-wrap: wrap;
    gap: .55rem;
}

.rr-admin-event-card__meta span,
.rr-admin-chip {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .5rem .72rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
    font-size: .86rem;
    font-weight: 700;
}

.rr-admin-chip {
    color: #cbd5e1;
}

.rr-admin-event-card__stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.rr-admin-event-mini {
    padding: .9rem .95rem;
}

.rr-admin-empty-card {
    padding: 1.2rem;
}

.rr-admin-empty-card--full {
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.rr-admin-status-badge {
    display: inline-flex;
    align-items: center;
    padding: .42rem .75rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #e2e8f0;
    background: rgba(51, 65, 85, 0.32);
}

.rr-admin-status-badge--ao-vivo { background: rgba(34, 197, 94, 0.14); color: #86efac; border-color: rgba(34, 197, 94, 0.24); }
.rr-admin-status-badge--pausado { background: rgba(245, 158, 11, 0.14); color: #fcd34d; border-color: rgba(245, 158, 11, 0.24); }
.rr-admin-status-badge--programado { background: rgba(96, 165, 250, 0.14); color: #93c5fd; border-color: rgba(96, 165, 250, 0.24); }
.rr-admin-status-badge--classificatoria { background: rgba(59, 130, 246, 0.14); color: #bfdbfe; border-color: rgba(59, 130, 246, 0.24); }
.rr-admin-status-badge--em-apuracao { background: rgba(168, 85, 247, 0.14); color: #d8b4fe; border-color: rgba(168, 85, 247, 0.24); }
.rr-admin-status-badge--inicio-finais { background: rgba(249, 115, 22, 0.14); color: #fdba74; border-color: rgba(249, 115, 22, 0.24); }
.rr-admin-status-badge--divisao-finalizada { background: rgba(250, 204, 21, 0.14); color: #fde68a; border-color: rgba(250, 204, 21, 0.24); }
.rr-admin-status-badge--finalizado { background: rgba(148, 163, 184, 0.14); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.24); }

.rr-admin-pulse-card--blue,
.rr-admin-metric-card--blue { --metric-accent: #60a5fa; }
.rr-admin-pulse-card--green,
.rr-admin-metric-card--green { --metric-accent: #34d399; }
.rr-admin-pulse-card--gold,
.rr-admin-metric-card--gold { --metric-accent: #fbbf24; }
.rr-admin-pulse-card--orange,
.rr-admin-metric-card--orange { --metric-accent: #fb923c; }
.rr-admin-pulse-card--violet,
.rr-admin-metric-card--violet { --metric-accent: #a78bfa; }
.rr-admin-pulse-card--red,
.rr-admin-metric-card--red { --metric-accent: #f87171; }
.rr-admin-pulse-card--cyan,
.rr-admin-metric-card--cyan { --metric-accent: #22d3ee; }
.rr-admin-pulse-card--lime,
.rr-admin-metric-card--lime { --metric-accent: #a3e635; }
.rr-admin-pulse-card--slate,
.rr-admin-metric-card--slate { --metric-accent: #94a3b8; }

@media (max-width: 1199px) {
    .rr-admin-hero {
        grid-template-columns: 1fr;
    }

    .rr-admin-current-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767px) {
    .rr-admin-dashboard {
        gap: 1rem;
    }

    .rr-admin-hero,
    .rr-admin-section,
    .rr-admin-panel,
    .rr-admin-event-card,
    .rr-admin-empty-card {
        border-radius: 18px;
    }

    .rr-admin-hero,
    .rr-admin-section,
    .rr-admin-panel,
    .rr-admin-empty-card {
        padding: 1rem;
    }

    .rr-admin-links-grid,
    .rr-admin-metrics-grid,
    .rr-admin-pulse-grid,
    .rr-admin-event-card__stats {
        grid-template-columns: 1fr;
    }

    .rr-admin-event-card__header,
    .rr-admin-section__header,
    .rr-admin-panel__header {
        flex-direction: column;
    }

    .rr-admin-title {
        font-size: 1.48rem;
    }

    .rr-admin-pulse-card__value,
    .rr-admin-metric-card__value {
        font-size: 1.32rem;
    }
}
</style>
@endpush
