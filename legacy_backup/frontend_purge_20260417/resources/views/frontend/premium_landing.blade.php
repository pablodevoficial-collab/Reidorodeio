@extends('frontend.layouts.app')

@php
    $user = auth()->user();
    $subscriptionService = app(\App\Services\SubscriptionService::class);
    
    $isPremium = $user && $user->isPremium();
    $canTrial = $user && $subscriptionService->isEligibleForTrial($user);
    $currentSubscription = $user ? $user->getCurrentSubscription() : null;
    
    // Check ineligibility reason
    $trialReason = null;
    $isActivityLocked = false;
    
    if ($user && !$canTrial && !$isPremium) {
        $trialReason = $subscriptionService->getTrialIneligibilityReason($user);
        // Check if reason is related to activity (not participated yet)
        $isActivityLocked = $trialReason && \Illuminate\Support\Str::contains($trialReason, 'participou');
    }
@endphp

@section('content')
<div class="rr-premium-landing">
    <!-- ============================================
         HERO SECTION - Épico e Majestoso
         ============================================ -->
    <section class="rr-premium-hero">
        <canvas id="premiumParticles" class="rr-premium-particles"></canvas>
        
        <div class="rr-premium-hero__content">
            <!-- Logo Premium -->
            <div class="rr-premium-hero__logo">
                <img src="{{ asset('assets/images/logo_icon/premiumleague.png') }}?v={{ time() }}" alt="Premium League" onerror="this.src='{{ asset('assets/images/logo_icon/logo.png') }}'">
                <div class="rr-premium-hero__logo-glow"></div>
            </div>
            
            <!-- Título Principal -->
            <h1 class="rr-premium-hero__title">
                <span class="rr-premium-hero__title-line">LIBERTE O</span>
                <span class="rr-premium-hero__title-main rr-ethnocentric">PODER PREMIUM</span>
            </h1>
            
            <p class="rr-premium-hero__subtitle">
                Domine o <strong>X1</strong>, conquiste o <strong>Bolão</strong> e alcance o topo das <strong>Estatísticas</strong>
            </p>
            
            <!-- CTA Principal -->
            <div class="rr-premium-hero__cta">
                @if($isPremium)
                    <div class="rr-premium-hero__status rr-premium-hero__status--active">
                        <i class="fas fa-crown"></i>
                        <span>Você já é Premium!</span>
                        @if($currentSubscription)
                            <small>{{ $currentSubscription->remaining_days }} dias restantes</small>
                        @endif
                    </div>
                @elseif($canTrial)
                    <a href="#plans" class="rr-premium-btn rr-premium-btn--trial">
                        <i class="fas fa-gift"></i>
                        <span>COMEÇAR 3 DIAS GRÁTIS</span>
                    </a>
                    <p class="rr-premium-hero__trial-note">Após o teste: R$49,90/mês</p>
                @elseif($isActivityLocked)
                    <div class="rr-premium-hero__locked-trial">
                        <button class="rr-premium-btn rr-premium-btn--trial rr-premium-btn--disabled" disabled style="filter: grayscale(1); opacity: 0.7; cursor: not-allowed;">
                            <i class="fas fa-lock"></i>
                            <span>3 DIAS GRÁTIS (BLOQUEADO)</span>
                        </button>
                        <p class="rr-premium-hero__trial-note" style="color: #fca5a5; max-width: 400px; margin: 0.5rem auto;">
                            <i class="fas fa-exclamation-circle"></i> {{ $trialReason }}
                        </p>
                        <a href="#plans" class="rr-premium-btn rr-premium-btn--primary" style="margin-top: 1rem; font-size: 0.9rem; padding: 0.75rem 1.5rem;">
                            <i class="fas fa-crown"></i>
                            <span>ASSINAR AGORA</span>
                        </a>
                    </div>
                @else
                    <a href="#plans" class="rr-premium-btn rr-premium-btn--primary">
                        <i class="fas fa-crown"></i>
                        <span>ASSINAR AGORA</span>
                    </a>
                @endif
            </div>
            
            <!-- Scroll indicator -->
            <div class="rr-premium-hero__scroll">
                <span>Veja os benefícios</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </section>

    <!-- ============================================
         BENEFÍCIOS - Grid épico
         ============================================ -->
    <section id="features" class="rr-premium-benefits">
        <div class="rr-premium-container">
            <h2 class="rr-premium-section-title">
                <span class="rr-premium-section-title__icon"><i class="fas fa-star"></i></span>
                <span>Benefícios Exclusivos</span>
            </h2>
            
            <div class="rr-premium-benefits__grid">
                <!-- Taxa Reduzida X1 -->
                <div class="rr-premium-benefit-card" data-accent="#facc15">
                    <div class="rr-premium-benefit-card__icon">
                        <i class="fas fa-percent"></i>
                    </div>
                    <h3 class="rr-premium-benefit-card__title">Taxa Reduzida X1</h3>
                    <p class="rr-premium-benefit-card__desc">Economize <strong>3%</strong> em todas as salas X1</p>
                    <div class="rr-premium-benefit-card__compare">
                        <div class="rr-premium-benefit-card__compare-item rr-premium-benefit-card__compare-item--old">
                            <span>Normal</span>
                            <strong>10-15%</strong>
                        </div>
                        <i class="fas fa-arrow-right"></i>
                        <div class="rr-premium-benefit-card__compare-item rr-premium-benefit-card__compare-item--new">
                            <span>Premium</span>
                            <strong>7-10%</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Bolão Premium -->
                <div class="rr-premium-benefit-card" data-accent="#f97316">
                    <div class="rr-premium-benefit-card__icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="rr-premium-benefit-card__title">Bolão Premium Grátis</h3>
                    <p class="rr-premium-benefit-card__desc">Participe de ligas exclusivas sem pagar entrada</p>
                    <div class="rr-premium-benefit-card__badge">
                        <i class="fas fa-infinity"></i>
                        <span>Acesso ilimitado</span>
                    </div>
                </div>
                
                <!-- Rankings Completos -->
                <div class="rr-premium-benefit-card" data-accent="#22c55e">
                    <div class="rr-premium-benefit-card__icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="rr-premium-benefit-card__title">Rankings Completos</h3>
                    <p class="rr-premium-benefit-card__desc">Estatísticas avançadas e histórico detalhado</p>
                    <div class="rr-premium-benefit-card__badge">
                        <i class="fas fa-database"></i>
                        <span>Dados completos</span>
                    </div>
                </div>
                
                <!-- Username Editável -->
                <div class="rr-premium-benefit-card" data-accent="#3b82f6">
                    <div class="rr-premium-benefit-card__icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <h3 class="rr-premium-benefit-card__title">Personalize seu Perfil</h3>
                    <p class="rr-premium-benefit-card__desc">Altere seu username quando quiser</p>
                    <div class="rr-premium-benefit-card__badge">
                        <i class="fas fa-check"></i>
                        <span>Exclusivo Premium</span>
                    </div>
                </div>
                
                <!-- Salas Exclusivas -->
                <div class="rr-premium-benefit-card" data-accent="#8b5cf6">
                    <div class="rr-premium-benefit-card__icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h3 class="rr-premium-benefit-card__title">Salas X1 Exclusivas</h3>
                    <p class="rr-premium-benefit-card__desc">Acesso a salas premium com jogadores de elite</p>
                    <div class="rr-premium-benefit-card__badge">
                        <i class="fas fa-lock-open"></i>
                        <span>VIP Access</span>
                    </div>
                </div>
                
                <!-- Estatísticas Avançadas -->
                <div class="rr-premium-benefit-card" data-accent="#10b981">
                    <div class="rr-premium-benefit-card__icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="rr-premium-benefit-card__title">Estatísticas Avançadas</h3>
                    <p class="rr-premium-benefit-card__desc">Análises detalhadas de competidores e eventos</p>
                    <div class="rr-premium-benefit-card__badge">
                        <i class="fas fa-search"></i>
                        <span>Dados completos</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         COMPARATIVO - Free vs Premium
         ============================================ -->
    <section class="rr-premium-compare">
        <div class="rr-premium-container">
            <h2 class="rr-premium-section-title">
                <span class="rr-premium-section-title__icon"><i class="fas fa-balance-scale"></i></span>
                <span>Free vs Premium</span>
            </h2>
            
            <div class="rr-premium-compare__table">
                <div class="rr-premium-compare__header">
                    <div class="rr-premium-compare__feature">Recurso</div>
                    <div class="rr-premium-compare__free">Free</div>
                    <div class="rr-premium-compare__premium">Premium</div>
                </div>
                
                <div class="rr-premium-compare__row">
                    <div class="rr-premium-compare__feature"><i class="fas fa-percent"></i> Taxa X1</div>
                    <div class="rr-premium-compare__free">10-15%</div>
                    <div class="rr-premium-compare__premium"><strong>7-10%</strong></div>
                </div>
                
                <div class="rr-premium-compare__row">
                    <div class="rr-premium-compare__feature"><i class="fas fa-trophy"></i> Bolão Premium</div>
                    <div class="rr-premium-compare__free"><i class="fas fa-times"></i> Pago</div>
                    <div class="rr-premium-compare__premium"><i class="fas fa-check"></i> Grátis</div>
                </div>
                
                <div class="rr-premium-compare__row">
                    <div class="rr-premium-compare__feature"><i class="fas fa-chart-line"></i> Estatísticas</div>
                    <div class="rr-premium-compare__free">Básicas</div>
                    <div class="rr-premium-compare__premium"><strong>Avançadas</strong></div>
                </div>
                
                <div class="rr-premium-compare__row">
                    <div class="rr-premium-compare__feature"><i class="fas fa-edit"></i> Editar Username</div>
                    <div class="rr-premium-compare__free"><i class="fas fa-times"></i></div>
                    <div class="rr-premium-compare__premium"><i class="fas fa-check"></i></div>
                </div>
                
                <div class="rr-premium-compare__row">
                    <div class="rr-premium-compare__feature"><i class="fas fa-crown"></i> Salas Exclusivas</div>
                    <div class="rr-premium-compare__free"><i class="fas fa-times"></i></div>
                    <div class="rr-premium-compare__premium"><i class="fas fa-check"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         PLANOS - Cards épicos
         ============================================ -->
    <section id="plans" class="rr-premium-plans">
        <div class="rr-premium-container">
            <h2 class="rr-premium-section-title">
                <span class="rr-premium-section-title__icon"><i class="fas fa-tags"></i></span>
                <span>Escolha seu Plano</span>
            </h2>
            
            @if($canTrial)
            <div class="rr-premium-trial-banner">
                <i class="fas fa-gift"></i>
                <span>Usuários ativos: <strong>3 dias grátis</strong> para testar!</span>
            </div>
            @elseif($isActivityLocked)
            <div class="rr-premium-trial-banner" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1)); border-color: rgba(239, 68, 68, 0.3); color: #fca5a5;">
                <i class="fas fa-lock"></i>
                <span>{{ $trialReason }}</span>
            </div>
            @endif
            
            <div class="rr-premium-plans__grid" id="premiumPlansGrid">
                <!-- Plans loaded via JS -->
                <div class="rr-premium-plans__loading">
                    <div class="spinner"></div>
                    <span>Carregando planos...</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         FAQ
         ============================================ -->
    <section class="rr-premium-faq">
        <div class="rr-premium-container">
            <h2 class="rr-premium-section-title">
                <span class="rr-premium-section-title__icon"><i class="fas fa-question-circle"></i></span>
                <span>Perguntas Frequentes</span>
            </h2>
            
            <div class="rr-premium-faq__list">
                <details class="rr-premium-faq__item" open>
                    <summary>Como funciona o período de 3 dias grátis?</summary>
                    <p>Usuários que já participaram de X1 ou Bolão podem experimentar o Premium por 3 dias grátis. Após o teste, será cobrado o valor do plano escolhido. Você pode cancelar quando quiser.</p>
                </details>
                
                <details class="rr-premium-faq__item">
                    <summary>Quais são as formas de pagamento?</summary>
                    <p><strong>Checkout Pro (Mercado Pago):</strong> Aceitamos Cartão de Crédito, PIX, Boleto e Saldo Mercado Pago. Tudo em um ambiente seguro.</p>
                </details>
                
                <details class="rr-premium-faq__item">
                    <summary>Posso cancelar e ter reembolso?</summary>
                    <p><strong>Cartão:</strong> Cancele quando quiser sem multa. A cobrança para no próximo ciclo.<br>
                    <strong>PIX (Semestral/Anual):</strong> Se cancelar antes de 3 meses, há multa equivalente a 2 meses. Após 3 meses, reembolso proporcional integral dos meses restantes.</p>
                </details>
                
                <details class="rr-premium-faq__item">
                    <summary>Como funciona a taxa reduzida no X1?</summary>
                    <p>Usuários Premium pagam menos taxa em todas as salas X1. Em salas até R$1.000, a taxa cai de 10% para 7%. Em salas acima de R$1.000, cai de 15% para 10%.</p>
                </details>
                
                <details class="rr-premium-faq__item">
                    <summary>O que acontece com minhas ligas do Bolão se eu cancelar?</summary>
                    <p>Suas equipes e pontuações são mantidas. Porém, você não poderá participar de novas ligas premium gratuitas após o cancelamento.</p>
                </details>
                
                <details class="rr-premium-faq__item">
                    <summary>Como faço o pagamento?</summary>
                    <p>Aceitamos PIX para pagamento rápido e seguro. Após a confirmação, seu Premium é ativado instantaneamente.</p>
                </details>
            </div>
        </div>
    </section>

    <!-- ============================================
         CTA FINAL
         ============================================ -->
    <section class="rr-premium-cta-final">
        <div class="rr-premium-container">
            <div class="rr-premium-cta-final__content">
                <h2>Pronto para dominar o <span class="rr-ethnocentric">Rei do Rodeio</span>?</h2>
                <p>Junte-se aos competidores de elite e leve sua experiência ao próximo nível</p>
                
                @if($isPremium)
                    <div class="rr-premium-cta-final__status">
                        <i class="fas fa-crown"></i>
                        <span>Você já é Premium!</span>
                    </div>
                @elseif($canTrial)
                    <a href="#plans" class="rr-premium-btn rr-premium-btn--trial rr-premium-btn--large">
                        <i class="fas fa-gift"></i>
                        <span>COMEÇAR 3 DIAS GRÁTIS</span>
                    </a>
                @else
                    <a href="#plans" class="rr-premium-btn rr-premium-btn--primary rr-premium-btn--large">
                        <i class="fas fa-crown"></i>
                        <span>ASSINAR AGORA</span>
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>

<!-- Modal de Pagamento PIX -->
<div class="rr-premium-modal" id="premiumPaymentModal" style="display: none;">
    <div class="rr-premium-modal__backdrop"></div>
    <div class="rr-premium-modal__content">
        <button class="rr-premium-modal__close" id="closePremiumModal">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="rr-premium-modal__body" id="premiumModalBody">
            <!-- Content loaded via JS -->
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
/* ============================================
   🔥 PREMIUM LANDING - ÉPICO STYLE
   ============================================ */

:root {
    --premium-bg: #0a0e17;
    --premium-card: #111827;
    --premium-border: rgba(255,255,255,0.08);
    --premium-text: #e5e7eb;
    --premium-muted: #9ca3af;
    --premium-gold: #fbbf24;
    --premium-purple: #8b5cf6;
    --premium-orange: #f97316;
    --premium-green: #22c55e;
    --premium-blue: #3b82f6;
}

.rr-premium-landing {
    background: var(--premium-bg);
    color: var(--premium-text);
    min-height: 100vh;
}

.rr-premium-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ============================================
   HERO SECTION
   ============================================ */
.rr-premium-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible !important; /* Permite logo ultrapassar */
    background: linear-gradient(180deg, #0a0e17 0%, #1a1033 50%, #0a0e17 100%);
    padding-top: 4rem; /* Espaço para logo */
}

.rr-premium-particles {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    opacity: 0.6;
}

.rr-premium-hero__content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2rem 1rem;
    overflow: visible !important; /* Permite logo ultrapassar */
}

.rr-premium-hero__logo {
    position: relative;
    width: 180px;
    height: 180px;
    margin: 0 auto 2rem;
    overflow: visible !important; /* Permite logo ultrapassar */
    z-index: 10;
}

.rr-premium-hero__logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 0 40px rgba(139, 92, 246, 0.5));
    animation: premiumLogoFloat 4s ease-in-out infinite;
    display: block !important;
    position: relative;
    z-index: 11;
}

.rr-premium-hero__logo-glow {
    position: absolute;
    inset: -20%;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.3) 0%, transparent 70%);
    animation: premiumGlowPulse 3s ease-in-out infinite;
    z-index: 9;
}

@keyframes premiumLogoFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

@keyframes premiumGlowPulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.1); }
}

.rr-premium-hero__title {
    margin: 0 0 1.5rem;
}

.rr-premium-hero__title-line {
    display: block;
    font-size: 1.2rem;
    font-weight: 600;
    letter-spacing: 0.3em;
    color: var(--premium-muted);
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.rr-premium-hero__title-main {
    display: block;
    font-size: clamp(2.5rem, 8vw, 4.5rem);
    background: linear-gradient(135deg, #fbbf24 0%, #f97316 30%, #8b5cf6 70%, #3b82f6 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    filter: drop-shadow(0 4px 20px rgba(139, 92, 246, 0.4));
    line-height: 1.1;
}

.rr-premium-hero__subtitle {
    font-size: 1.1rem;
    color: var(--premium-muted);
    max-width: 500px;
    margin: 0 auto 2rem;
    line-height: 1.6;
}

.rr-premium-hero__subtitle strong {
    color: var(--premium-text);
}

.rr-premium-hero__cta {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.rr-premium-hero__trial-note {
    font-size: 0.85rem;
    color: var(--premium-muted);
    margin: 0;
}

.rr-premium-hero__status {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.1));
    border: 1px solid rgba(34, 197, 94, 0.4);
    border-radius: 50px;
    color: #22c55e;
}

.rr-premium-hero__status i {
    font-size: 1.5rem;
    color: #fbbf24;
    animation: crownBounce 2s ease-in-out infinite;
}

.rr-premium-hero__status small {
    display: block;
    font-size: 0.8rem;
    opacity: 0.8;
}

.rr-premium-hero__scroll {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: var(--premium-muted);
    font-size: 0.85rem;
    animation: scrollBounce 2s ease-in-out infinite;
}

@keyframes scrollBounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(10px); }
}

/* ============================================
   BUTTONS
   ============================================ */
.rr-premium-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
}

.rr-premium-btn--primary {
    background: linear-gradient(135deg, var(--premium-purple), #6d28d9);
    color: white;
    box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
}

.rr-premium-btn--primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(139, 92, 246, 0.5);
}

.rr-premium-btn--trial {
    background: linear-gradient(135deg, var(--premium-green), #16a34a);
    color: white;
    box-shadow: 0 4px 20px rgba(34, 197, 94, 0.4);
}

.rr-premium-btn--trial:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(34, 197, 94, 0.5);
}

.rr-premium-btn--large {
    padding: 1.25rem 3rem;
    font-size: 1.1rem;
}

.rr-premium-btn--gold {
    background: linear-gradient(135deg, var(--premium-gold), #f59e0b);
    color: #1a1a1a;
}

/* ============================================
   SECTION TITLE
   ============================================ */
.rr-premium-section-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 3rem;
    text-align: center;
}

.rr-premium-section-title__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--premium-purple), #6d28d9);
    border-radius: 12px;
    font-size: 1.2rem;
}

/* ============================================
   BENEFITS GRID
   ============================================ */
.rr-premium-benefits {
    padding: 5rem 0;
    background: linear-gradient(180deg, var(--premium-bg) 0%, #0f1729 100%);
}

.rr-premium-benefits__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.rr-premium-benefit-card {
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.8), rgba(17, 24, 39, 0.4));
    border: 1px solid var(--premium-border);
    border-radius: 20px;
    padding: 2rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.rr-premium-benefit-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: attr(data-accent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.rr-premium-benefit-card:hover {
    transform: translateY(-5px);
    border-color: rgba(139, 92, 246, 0.3);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.rr-premium-benefit-card:hover::before {
    opacity: 1;
}

.rr-premium-benefit-card[data-accent="#facc15"]::before { background: #facc15; }
.rr-premium-benefit-card[data-accent="#f97316"]::before { background: #f97316; }
.rr-premium-benefit-card[data-accent="#22c55e"]::before { background: #22c55e; }
.rr-premium-benefit-card[data-accent="#3b82f6"]::before { background: #3b82f6; }
.rr-premium-benefit-card[data-accent="#8b5cf6"]::before { background: #8b5cf6; }
.rr-premium-benefit-card[data-accent="#ef4444"]::before { background: #ef4444; }

.rr-premium-benefit-card__icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(139, 92, 246, 0.1);
    border-radius: 16px;
    font-size: 1.5rem;
    color: var(--premium-purple);
    margin-bottom: 1.5rem;
}

.rr-premium-benefit-card[data-accent="#facc15"] .rr-premium-benefit-card__icon { background: rgba(251, 191, 36, 0.1); color: #facc15; }
.rr-premium-benefit-card[data-accent="#f97316"] .rr-premium-benefit-card__icon { background: rgba(249, 115, 22, 0.1); color: #f97316; }
.rr-premium-benefit-card[data-accent="#22c55e"] .rr-premium-benefit-card__icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.rr-premium-benefit-card[data-accent="#3b82f6"] .rr-premium-benefit-card__icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.rr-premium-benefit-card[data-accent="#ef4444"] .rr-premium-benefit-card__icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

.rr-premium-benefit-card__title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
}

.rr-premium-benefit-card__desc {
    color: var(--premium-muted);
    margin: 0 0 1rem;
    line-height: 1.5;
}

.rr-premium-benefit-card__compare {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
}

.rr-premium-benefit-card__compare-item {
    text-align: center;
    flex: 1;
}

.rr-premium-benefit-card__compare-item span {
    display: block;
    font-size: 0.75rem;
    color: var(--premium-muted);
    margin-bottom: 0.25rem;
}

.rr-premium-benefit-card__compare-item--old strong {
    color: #ef4444;
    text-decoration: line-through;
    opacity: 0.7;
}

.rr-premium-benefit-card__compare-item--new strong {
    color: var(--premium-green);
    font-size: 1.2rem;
}

.rr-premium-benefit-card__compare i {
    color: var(--premium-muted);
}

.rr-premium-benefit-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(139, 92, 246, 0.1);
    border: 1px solid rgba(139, 92, 246, 0.2);
    border-radius: 50px;
    font-size: 0.85rem;
    color: var(--premium-purple);
}

/* ============================================
   COMPARE TABLE
   ============================================ */
.rr-premium-compare {
    padding: 5rem 0;
    background: var(--premium-bg);
}

.rr-premium-compare__table {
    max-width: 700px;
    margin: 0 auto;
    border: 1px solid var(--premium-border);
    border-radius: 20px;
    overflow: hidden;
    background: var(--premium-card);
}

.rr-premium-compare__header,
.rr-premium-compare__row {
    display: grid;
    grid-template-columns: 1fr 100px 100px;
    gap: 1rem;
}

.rr-premium-compare__header {
    background: rgba(139, 92, 246, 0.1);
    padding: 1rem 1.5rem;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
}

.rr-premium-compare__row {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--premium-border);
    align-items: center;
}

.rr-premium-compare__feature {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.rr-premium-compare__feature i {
    color: var(--premium-muted);
    width: 20px;
}

.rr-premium-compare__free,
.rr-premium-compare__premium {
    text-align: center;
}

.rr-premium-compare__free {
    color: var(--premium-muted);
}

.rr-premium-compare__free .fa-times {
    color: #ef4444;
}

.rr-premium-compare__premium {
    color: var(--premium-green);
}

.rr-premium-compare__premium strong {
    color: var(--premium-gold);
}

.rr-premium-compare__premium .fa-check {
    color: var(--premium-green);
}

/* ============================================
   PLANS SECTION
   ============================================ */
.rr-premium-plans {
    padding: 5rem 0;
    background: linear-gradient(180deg, #0f1729 0%, var(--premium-bg) 100%);
}

.rr-premium-trial-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.1));
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 12px;
    margin-bottom: 2rem;
    color: var(--premium-green);
}

.rr-premium-trial-banner i {
    font-size: 1.5rem;
    animation: giftBounce 1s ease-in-out infinite;
}

@keyframes giftBounce {
    0%, 100% { transform: rotate(-5deg); }
    50% { transform: rotate(5deg); }
}

.rr-premium-plans__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.rr-premium-plans__loading {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 3rem;
    color: var(--premium-muted);
}

.rr-premium-plans__loading .spinner {
    width: 40px;
    height: 40px;
    border: 3px solid var(--premium-border);
    border-top-color: var(--premium-purple);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Plan Card */
.rr-premium-plan-card {
    background: var(--premium-card);
    border: 2px solid var(--premium-border);
    border-radius: 24px;
    padding: 2rem;
    position: relative;
    transition: all 0.3s ease;
    overflow: visible !important; /* Permite badge ultrapassar */
    margin-top: 30px; /* Espaço para badge */
    isolation: auto !important; /* Remove isolamento */
}

.rr-premium-plan-card:hover {
    border-color: rgba(139, 92, 246, 0.4);
    transform: translateY(-5px);
}

.rr-premium-plan-card--featured {
    border-color: var(--premium-gold);
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.05), transparent);
}

.rr-premium-plan-card--featured::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, var(--premium-gold), var(--premium-orange));
    border-radius: 26px;
    z-index: -1;
    opacity: 0.3;
}

.rr-premium-plan-card__badge {
    position: absolute !important;
    top: -12px !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    padding: 0.5rem 1.5rem;
    background: linear-gradient(135deg, var(--premium-gold), var(--premium-orange));
    color: #1a1a1a;
    font-weight: 700;
    font-size: 0.8rem;
    border-radius: 50px;
    white-space: nowrap;
    z-index: 100 !important;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
    pointer-events: none;
}

.rr-premium-plan-card__name {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
    text-align: center;
}

.rr-premium-plan-card__price {
    text-align: center;
    margin: 1.5rem 0;
}

.rr-premium-plan-card__price-value {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--premium-purple), var(--premium-blue));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.rr-premium-plan-card__price-period {
    color: var(--premium-muted);
    font-size: 1rem;
}

.rr-premium-plan-card__price-monthly {
    font-size: 0.9rem;
    color: var(--premium-muted);
    margin-top: 0.25rem;
}

.rr-premium-plan-card__payment-type {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.4rem 1rem;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 50px;
    color: #3b82f6;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.rr-premium-plan-card__trial-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
    border-radius: 50px;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    margin-top: 0.5rem;
    animation: pulseGlow 2s ease-in-out infinite;
}

@keyframes pulseGlow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.4); }
    50% { box-shadow: 0 0 15px 5px rgba(139, 92, 246, 0.2); }
}

.rr-premium-plan-card__savings {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-radius: 50px;
    color: var(--premium-green);
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.rr-premium-plan-card__cancel-info {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.05);
}

.rr-premium-plan-card__cancel-info small {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.75rem;
}

.rr-premium-plan-card__cancel-info i {
    color: rgba(34, 197, 94, 0.7);
}

.rr-premium-plan-card__features {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.rr-premium-plan-card__features li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.5rem 0;
    color: var(--premium-text);
    font-size: 0.95rem;
}

.rr-premium-plan-card__features li i {
    color: var(--premium-green);
    margin-top: 0.2rem;
}

.rr-premium-plan-card__cta {
    width: 100%;
    margin-top: 1rem;
}

/* ============================================
   FAQ
   ============================================ */
.rr-premium-faq {
    padding: 5rem 0;
    background: var(--premium-bg);
}

.rr-premium-faq__list {
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.rr-premium-faq__item {
    background: var(--premium-card);
    border: 1px solid var(--premium-border);
    border-radius: 16px;
    overflow: hidden;
}

.rr-premium-faq__item summary {
    padding: 1.25rem 1.5rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    list-style: none;
}

.rr-premium-faq__item summary::-webkit-details-marker {
    display: none;
}

.rr-premium-faq__item summary::after {
    content: '+';
    font-size: 1.5rem;
    color: var(--premium-purple);
    transition: transform 0.3s ease;
}

.rr-premium-faq__item[open] summary::after {
    transform: rotate(45deg);
}

.rr-premium-faq__item p {
    padding: 0 1.5rem 1.25rem;
    margin: 0;
    color: var(--premium-muted);
    line-height: 1.6;
}

/* ============================================
   CTA FINAL
   ============================================ */
.rr-premium-cta-final {
    padding: 5rem 0;
    background: linear-gradient(180deg, var(--premium-bg) 0%, #1a1033 100%);
}

.rr-premium-cta-final__content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.rr-premium-cta-final__content h2 {
    font-size: 2rem;
    margin: 0 0 1rem;
}

.rr-premium-cta-final__content p {
    color: var(--premium-muted);
    margin: 0 0 2rem;
}

.rr-premium-cta-final__status {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 50px;
    color: var(--premium-green);
    font-weight: 600;
}

.rr-premium-cta-final__status i {
    color: var(--premium-gold);
}

/* ============================================
   MODAL
   ============================================ */
.rr-premium-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.rr-premium-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
}

.rr-premium-modal__content {
    position: relative;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    background: var(--premium-card);
    border: 1px solid var(--premium-border);
    border-radius: 24px;
    padding: 2rem;
}

.rr-premium-modal__close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--premium-border);
    border-radius: 50%;
    color: var(--premium-muted);
    cursor: pointer;
    transition: all 0.3s ease;
}

.rr-premium-modal__close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--premium-text);
}

/* Modal Body States */
.rr-premium-modal__loading,
.rr-premium-modal__pix,
.rr-premium-modal__success,
.rr-premium-modal__error {
    text-align: center;
    padding: 2rem 0;
}

.rr-premium-modal__pix-qr {
    width: 200px;
    height: 200px;
    margin: 1.5rem auto;
    background: white;
    border-radius: 12px;
    padding: 1rem;
}

.rr-premium-modal__pix-qr img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.rr-premium-modal__pix-code {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid var(--premium-border);
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
    word-break: break-all;
    font-family: monospace;
    font-size: 0.8rem;
    color: var(--premium-muted);
    max-height: 80px;
    overflow-y: auto;
}

.rr-premium-modal__pix-copy {
    width: 100%;
}

.rr-premium-modal__success i {
    font-size: 4rem;
    color: var(--premium-green);
    margin-bottom: 1rem;
}

.rr-premium-modal__error i {
    font-size: 4rem;
    color: #ef4444;
    margin-bottom: 1rem;
}

/* Crown animation */
@keyframes crownBounce {
    0%, 100% { transform: translateY(0) scale(1); }
    25% { transform: translateY(-4px) scale(1.1); }
    50% { transform: translateY(0) rotate(12deg); }
    75% { transform: translateY(-2px) rotate(-12deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .rr-premium-hero__logo {
        width: 140px;
        height: 140px;
    }
    
    .rr-premium-section-title {
        font-size: 1.4rem;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .rr-premium-compare__header,
    .rr-premium-compare__row {
        grid-template-columns: 1fr 80px 80px;
        font-size: 0.85rem;
    }
    
    .rr-premium-benefits__grid {
        grid-template-columns: 1fr;
    }
    
    .rr-premium-plans__grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('script')
<script>
(function() {
    'use strict';
    
    const API_BASE = '{{ url("/api/subscriptions") }}';
    const modal = document.getElementById('premiumPaymentModal');
    const modalBody = document.getElementById('premiumModalBody');
    const closeBtn = document.getElementById('closePremiumModal');
    const plansGrid = document.getElementById('premiumPlansGrid');
    
    let currentPaymentId = null;
    let pollInterval = null;
    
    // ============================================
    // PARTICLES BACKGROUND
    // ============================================
    function initParticles() {
        const canvas = document.getElementById('premiumParticles');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const DPR = window.devicePixelRatio || 1;
        let particles = [];
        
        function resize() {
            canvas.width = window.innerWidth * DPR;
            canvas.height = window.innerHeight * DPR;
            ctx.scale(DPR, DPR);
        }
        
        function createParticles() {
            const count = window.innerWidth < 768 ? 30 : 60;
            particles = [];
            for (let i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * window.innerWidth,
                    y: Math.random() * window.innerHeight,
                    r: Math.random() * 2 + 0.5,
                    vx: (Math.random() - 0.5) * 0.5,
                    vy: (Math.random() - 0.5) * 0.5,
                    alpha: Math.random() * 0.5 + 0.2,
                    color: ['#8b5cf6', '#f97316', '#fbbf24', '#22c55e'][Math.floor(Math.random() * 4)]
                });
            }
        }
        
        function draw() {
            ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
            
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                
                if (p.x < 0) p.x = window.innerWidth;
                if (p.x > window.innerWidth) p.x = 0;
                if (p.y < 0) p.y = window.innerHeight;
                if (p.y > window.innerHeight) p.y = 0;
                
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = p.alpha;
                ctx.fill();
            });
            
            ctx.globalAlpha = 1;
            requestAnimationFrame(draw);
        }
        
        resize();
        createParticles();
        draw();
        
        window.addEventListener('resize', () => {
            resize();
            createParticles();
        });
    }
    
    // ============================================
    // LOAD PLANS
    // ============================================
    async function loadPlans() {
        try {
            const response = await fetch(API_BASE + '/plans');
            const data = await response.json();
            
            if (data.success && data.plans) {
                renderPlans(data.plans, data.can_trial, data.trial_reason);
            }
        } catch (error) {
            console.error('Erro ao carregar planos:', error);
            plansGrid.innerHTML = '<p class="text-center text-muted">Erro ao carregar planos. Tente novamente.</p>';
        }
    }
    
    function renderPlans(plans, canTrial, trialReason) {
        const isActivityLocked = trialReason && trialReason.includes('participou');

        plansGrid.innerHTML = plans.map(plan => {
            const isCard = plan.is_recurring;
            const hasTrial = plan.has_trial && canTrial;
            const paymentMethods = plan.payment_methods || ['pix'];
            const paymentIcon = isCard ? 'fa-credit-card' : 'fa-qrcode';
            const paymentLabel = isCard ? 'Cartão de Crédito' : 'PIX';
            
            return `
            <div class="rr-premium-plan-card ${plan.is_featured ? 'rr-premium-plan-card--featured' : ''}" data-plan="${plan.slug}">
                ${plan.badge ? `<div class="rr-premium-plan-card__badge" style="background: linear-gradient(135deg, ${plan.badge_color || '#8b5cf6'}, ${plan.badge_color || '#8b5cf6'}88)">${plan.badge}</div>` : ''}
                
                <h3 class="rr-premium-plan-card__name">${plan.name}</h3>
                
                <div class="rr-premium-plan-card__payment-type">
                    <i class="fas ${paymentIcon}"></i> ${paymentLabel}
                </div>
                
                <div class="rr-premium-plan-card__price">
                    <div class="rr-premium-plan-card__price-value">${plan.formatted_price}</div>
                    <div class="rr-premium-plan-card__price-period">${plan.period_label}</div>
                    ${plan.billing_cycle !== 'monthly' ? `<div class="rr-premium-plan-card__price-monthly">${plan.formatted_monthly_price}/mês</div>` : ''}
                    ${hasTrial 
                        ? `<div class="rr-premium-plan-card__trial-badge"><i class="fas fa-gift"></i> 3 dias grátis!</div>` 
                        : (plan.has_trial && isActivityLocked 
                            ? `<div class="rr-premium-plan-card__trial-badge" style="background: #374151; color: #9ca3af; box-shadow: none;"><i class="fas fa-lock"></i> Trial bloqueado</div>` 
                            : '')
                    }
                    ${plan.savings > 0 ? `<div class="rr-premium-plan-card__savings"><i class="fas fa-tag"></i> Economia de R$ ${plan.savings.toFixed(2).replace('.', ',')}</div>` : ''}
                </div>
                
                <ul class="rr-premium-plan-card__features">
                    ${(plan.features || []).map(f => `<li><i class="fas fa-check"></i> ${f}</li>`).join('')}
                </ul>
                
                <div class="rr-premium-plan-card__cancel-info">
                    ${isCard 
                        ? '<small><i class="fas fa-shield-alt"></i> Cancele quando quiser sem multa</small>' 
                        : '<small><i class="fas fa-info-circle"></i> Reembolso proporcional após 3 meses</small>'
                    }
                </div>
                
                <button class="rr-premium-btn ${hasTrial ? 'rr-premium-btn--trial' : 'rr-premium-btn--primary'} rr-premium-plan-card__cta" 
                        data-plan-slug="${plan.slug}" 
                        data-has-trial="${hasTrial}"
                        data-is-recurring="${isCard}"
                        ${!hasTrial && plan.has_trial && isActivityLocked ? 'disabled style="filter: grayscale(1); opacity: 0.7; cursor: not-allowed;"' : ''}>
                    <i class="fas ${hasTrial ? 'fa-gift' : (plan.has_trial && isActivityLocked ? 'fa-lock' : 'fa-crown')}"></i>
                    <span>${hasTrial ? 'COMEÇAR 3 DIAS GRÁTIS' : (plan.has_trial && isActivityLocked ? 'TRIAL BLOQUEADO' : 'ASSINAR AGORA')}</span>
                </button>
                
                ${!hasTrial && plan.has_trial && isActivityLocked ? `
                <div style="text-align: center; margin-top: 0.5rem;">
                    <small style="color: #fca5a5; font-size: 0.75rem;">${trialReason}</small>
                </div>
                <button class="rr-premium-btn rr-premium-btn--primary rr-premium-plan-card__cta" 
                        style="margin-top: 0.5rem;"
                        data-plan-slug="${plan.slug}" 
                        data-has-trial="false"
                        data-is-recurring="${isCard}">
                    <i class="fas fa-crown"></i>
                    <span>ASSINAR AGORA</span>
                </button>
                ` : ''}
            </div>
        `}).join('');
        
        // Add click handlers
        plansGrid.querySelectorAll('[data-plan-slug]').forEach(btn => {
            btn.addEventListener('click', () => handlePlanSelect(
                btn.dataset.planSlug, 
                btn.dataset.hasTrial === 'true',
                btn.dataset.isRecurring === 'true'
            ));
        });
    }
    
    // ============================================
    // HANDLE PLAN SELECTION
    // ============================================
    async function handlePlanSelect(planSlug, hasTrial, isRecurring) {
        @if(!auth()->check())
            window.location.href = '{{ route("user.login") }}?redirect=' + encodeURIComponent(window.location.href);
            return;
        @endif
        
        showModal();
        showModalLoading('Processando...');
        
        try {
            // Se for elegível para Trial, ativa direto (sem cartão)
            if (hasTrial) {
                const response = await fetch(API_BASE + '/start-trial', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ plan_slug: planSlug })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showModalSuccess(data.message || 'Trial ativado com sucesso!');
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showModalError(data.message || 'Erro ao ativar trial.');
                }
                return;
            }

            // Se for Pagamento (Checkout Pro)
            // Cria preferência e abre no modal (iframe)
            const response = await fetch(API_BASE + '/create-preference', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ plan_slug: planSlug })
            });
            
            const data = await response.json();
            
            if (data.success && data.init_point) {
                showModalIframe(data.init_point);
            } else {
                showModalError(data.message || 'Erro ao criar pagamento');
            }
        } catch (error) {
            console.error('Erro:', error);
            showModalError('Erro de conexão. Tente novamente.');
        }
    }
    
    function showModalIframe(url) {
        modalBody.innerHTML = `
            <div style="width: 100%; height: 600px; border: none; overflow: hidden; border-radius: 12px; background: #fff;">
                <iframe src="${url}" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
            <div style="text-align: center; margin-top: 1rem;">
                <p class="text-muted small"><i class="fas fa-lock"></i> Pagamento seguro via Mercado Pago</p>
                <button class="rr-premium-btn rr-premium-btn--primary" onclick="window.location.reload()" style="padding: 0.5rem 1.5rem; font-size: 0.9rem;">
                    Fechar e Atualizar
                </button>
            </div>
        `;
    }
    
    // Break out of iframe if loaded inside one (for success redirect)
    if (window.self !== window.top) {
        window.top.location = window.location.href;
    }
    
    // ============================================
    // MODAL HELPERS
    // ============================================
    function showModal() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function hideModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        stopPaymentPolling();
    }
    
    function showModalLoading(message) {
        modalBody.innerHTML = `
            <div class="rr-premium-modal__loading">
                <div class="spinner"></div>
                <p>${message}</p>
            </div>
        `;
    }
    
    function showModalPix(payment, plan) {
        modalBody.innerHTML = `
            <div class="rr-premium-modal__pix">
                <h3>Pagar com PIX</h3>
                <p>${plan.name} - ${plan.price}</p>
                
                <div class="rr-premium-modal__pix-qr">
                    <img src="data:image/png;base64,${payment.qr_code_base64}" alt="QR Code PIX">
                </div>
                
                <p class="text-muted small">Ou copie o código:</p>
                <div class="rr-premium-modal__pix-code" id="pixCode">${payment.qr_code}</div>
                
                <button class="rr-premium-btn rr-premium-btn--primary rr-premium-modal__pix-copy" onclick="copyPixCode()">
                    <i class="fas fa-copy"></i>
                    <span>Copiar código PIX</span>
                </button>
                
                <p class="text-muted small mt-3">
                    <i class="fas fa-clock"></i> Aguardando pagamento...
                </p>
            </div>
        `;
    }
    
    function showModalSuccess(message) {
        modalBody.innerHTML = `
            <div class="rr-premium-modal__success">
                <i class="fas fa-check-circle"></i>
                <h3>Sucesso!</h3>
                <p>${message}</p>
            </div>
        `;
    }
    
    function showModalError(message) {
        modalBody.innerHTML = `
            <div class="rr-premium-modal__error">
                <i class="fas fa-times-circle"></i>
                <h3>Erro</h3>
                <p>${message}</p>
                <button class="rr-premium-btn rr-premium-btn--primary" onclick="document.getElementById('premiumPaymentModal').style.display='none'">
                    Fechar
                </button>
            </div>
        `;
    }
    
    // ============================================
    // PAYMENT POLLING
    // ============================================
    function startPaymentPolling() {
        if (pollInterval) clearInterval(pollInterval);
        
        pollInterval = setInterval(async () => {
            if (!currentPaymentId) return;
            
            try {
                const response = await fetch(`${API_BASE}/payment/${currentPaymentId}/status`);
                const data = await response.json();
                
                if (data.status === 'approved') {
                    stopPaymentPolling();
                    showModalSuccess(data.message || 'Pagamento aprovado!');
                    setTimeout(() => window.location.reload(), 2000);
                } else if (data.status === 'rejected' || data.status === 'cancelled') {
                    stopPaymentPolling();
                    showModalError('Pagamento ' + (data.status === 'rejected' ? 'rejeitado' : 'cancelado'));
                }
            } catch (error) {
                console.error('Erro ao verificar pagamento:', error);
            }
        }, 5000);
    }
    
    function stopPaymentPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
        currentPaymentId = null;
    }
    
    // ============================================
    // COPY PIX CODE
    // ============================================
    window.copyPixCode = function() {
        const code = document.getElementById('pixCode')?.textContent;
        if (code) {
            navigator.clipboard.writeText(code).then(() => {
                const btn = document.querySelector('.rr-premium-modal__pix-copy');
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-check"></i> <span>Copiado!</span>';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-copy"></i> <span>Copiar código PIX</span>';
                    }, 2000);
                }
            });
        }
    };
    
    // ============================================
    // INIT
    // ============================================
    document.addEventListener('DOMContentLoaded', () => {
        initParticles();
        loadPlans();
        
        // Modal close handlers
        closeBtn?.addEventListener('click', hideModal);
        modal?.querySelector('.rr-premium-modal__backdrop')?.addEventListener('click', hideModal);
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    });
})();
</script>
@endpush
