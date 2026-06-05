<div class="affiliate-activation-wrapper">
    <!-- Hero Section -->
    <div class="affiliate-hero">
        <div class="hero-icon">🤝</div>
        <h1>Programa de Afiliados</h1>
        <p class="hero-subtitle">
            Indique amigos e ganhe comissões <strong>vitalícias</strong> enquanto eles jogarem!
        </p>
    </div>

    <!-- Como Você Ganha -->
    <div class="earning-types mb-5">
        <h3 class="section-title">💰 Como Você Ganha</h3>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="earning-card earning-x1">
                    <div class="earning-header">
                        <span class="earning-icon">🎯</span>
                        <h4>Salas X1</h4>
                    </div>
                    <div class="earning-body">
                        <p class="earning-desc">Você recebe parte do <strong>lucro da casa</strong> quando seu indicado joga</p>
                        <div class="earning-rates">
                            <div class="rate-row">
                                <span>🥉 Bronze</span>
                                <span class="rate">20%</span>
                            </div>
                            <div class="rate-row">
                                <span>🥈 Prata</span>
                                <span class="rate">25%</span>
                            </div>
                            <div class="rate-row">
                                <span>🥇 Ouro</span>
                                <span class="rate">30%</span>
                            </div>
                            <div class="rate-row highlight">
                                <span>💎 Diamante</span>
                                <span class="rate">35%</span>
                            </div>
                        </div>
                        <div class="earning-example">
                            <small>Ex: Casa lucra R$100 → Você ganha até <strong>R$35</strong></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="earning-card earning-fantasy">
                    <div class="earning-header">
                        <span class="earning-icon">🏆</span>
                        <h4>Fantasy</h4>
                    </div>
                    <div class="earning-body">
                        <p class="earning-desc">Você recebe quando seu indicado <strong>ganha prêmio</strong></p>
                        <div class="earning-rates">
                            <div class="rate-row">
                                <span>🥉 Bronze</span>
                                <span class="rate">2%</span>
                            </div>
                            <div class="rate-row">
                                <span>🥈 Prata</span>
                                <span class="rate">3%</span>
                            </div>
                            <div class="rate-row">
                                <span>🥇 Ouro</span>
                                <span class="rate">4%</span>
                            </div>
                            <div class="rate-row highlight">
                                <span>💎 Diamante</span>
                                <span class="rate">5%</span>
                            </div>
                        </div>
                        <div class="earning-example">
                            <small>Ex: Indicado ganha R$1.000 → Você ganha até <strong>R$50</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tier Cards -->
    <div class="tiers-section mb-5">
        <h3 class="section-title">📈 Níveis de Afiliado</h3>
        <div class="tier-cards row g-3">
            @foreach($tiers as $tier)
            <div class="col-6 col-lg-3">
                <div class="tier-card {{ $loop->first ? 'tier-starter' : '' }} {{ $loop->last ? 'tier-diamond' : '' }}">
                    <div class="tier-emoji">{{ json_decode($tier->benefits)->emoji ?? '🏆' }}</div>
                    <h5 class="tier-name">{{ $tier->name }}</h5>
                    <p class="tier-req">
                        @if($tier->min_referrals == 0)
                            Início
                        @else
                            {{ $tier->min_referrals }}+ indicações
                        @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Passo a Passo -->
    <div class="steps-section mb-5">
        <h3 class="section-title">🚀 Como Começar</h3>
        <div class="steps-grid">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h5>Ative sua Conta</h5>
                    <p>Clique no botão abaixo para ativar</p>
                </div>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h5>Compartilhe seu Link</h5>
                    <p>Envie para amigos via WhatsApp</p>
                </div>
            </div>
            <div class="step-arrow">→</div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h5>Ganhe Comissões</h5>
                    <p>Receba automaticamente</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Garantias -->
    <div class="guarantees-section mb-5">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="guarantee-item">
                    <span class="guarantee-icon">♾️</span>
                    <span>Comissão Vitalícia</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="guarantee-item">
                    <span class="guarantee-icon">🔒</span>
                    <span>Indicação Permanente</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="guarantee-item">
                    <span class="guarantee-icon">💸</span>
                    <span>Pagamento Automático</span>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="cta-section text-center">
        <button id="btnActivateAffiliate" class="btn-activate-affiliate">
            <span class="btn-icon">🚀</span>
            <span class="btn-text">Ativar Conta de Afiliado</span>
        </button>
        <p class="cta-subtitle">Gratuito • Sem compromisso • Comece agora</p>
    </div>
</div>

<style>
.affiliate-activation-wrapper {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.affiliate-hero {
    text-align: center;
    margin-bottom: 3rem;
}

.hero-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.affiliate-hero h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.hero-subtitle {
    color: rgba(255,255,255,0.7);
    font-size: 1.1rem;
}

.section-title {
    text-align: center;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
    font-weight: 600;
}

/* Earning Cards */
.earning-card {
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    overflow: hidden;
    height: 100%;
    border: 1px solid rgba(255,255,255,0.1);
}

.earning-x1 {
    border-top: 3px solid #3b82f6;
}

.earning-fantasy {
    border-top: 3px solid #10b981;
}

.earning-header {
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: rgba(255,255,255,0.03);
}

.earning-icon {
    font-size: 1.5rem;
}

.earning-header h4 {
    margin: 0;
    font-size: 1.1rem;
}

.earning-body {
    padding: 1rem;
}

.earning-desc {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 1rem;
}

.earning-rates {
    background: rgba(0,0,0,0.2);
    border-radius: 0.5rem;
    padding: 0.5rem;
    margin-bottom: 1rem;
}

.rate-row {
    display: flex;
    justify-content: space-between;
    padding: 0.4rem 0.5rem;
    font-size: 0.85rem;
    border-radius: 0.25rem;
}

.rate-row.highlight {
    background: rgba(255,215,0,0.1);
}

.rate {
    font-weight: 700;
    color: #10b981;
}

.earning-example {
    text-align: center;
    padding: 0.5rem;
    background: rgba(255,255,255,0.05);
    border-radius: 0.5rem;
    font-size: 0.8rem;
}

/* Tier Cards */
.tier-card {
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    padding: 1.25rem;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.1);
    transition: transform 0.2s;
}

.tier-card:hover {
    transform: translateY(-3px);
}

.tier-starter {
    border-color: #cd7f32;
}

.tier-diamond {
    border-color: #b9f2ff;
    background: linear-gradient(135deg, rgba(185,242,255,0.1), rgba(255,255,255,0.05));
}

.tier-emoji {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.tier-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.tier-req {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    margin: 0;
}

/* Steps */
.steps-grid {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.step-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255,255,255,0.05);
    padding: 1rem 1.5rem;
    border-radius: 1rem;
}

.step-number {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #f97316, #ea580c);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.step-content h5 {
    margin: 0;
    font-size: 0.95rem;
}

.step-content p {
    margin: 0;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.6);
}

.step-arrow {
    font-size: 1.5rem;
    color: rgba(255,255,255,0.3);
}

@media (max-width: 768px) {
    .step-arrow {
        display: none;
    }
    .steps-grid {
        flex-direction: column;
    }
}

/* Guarantees */
.guarantee-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.3);
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
}

.guarantee-icon {
    font-size: 1.25rem;
}

/* CTA */
.cta-section {
    margin-top: 2rem;
}

.btn-activate-affiliate {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    background: linear-gradient(135deg, #f97316, #ea580c);
    border: none;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 1rem 2.5rem;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(249,115,22,0.4);
}

.btn-activate-affiliate:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(249,115,22,0.5);
}

.btn-activate-affiliate:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-icon {
    font-size: 1.5rem;
}

.cta-subtitle {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.5);
}
</style>

<script>
document.getElementById('btnActivateAffiliate').addEventListener('click', async function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ativando...';
    
    try {
        const response = await fetch('{{ route("user.affiliate.activate.submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof RRToast !== 'undefined') {
                RRToast.success(data.message);
            } else {
                alert(data.message);
            }
            
            setTimeout(() => {
                window.location.href = '{{ route("user.affiliate.dashboard") }}';
            }, 1500);
        } else {
            if (typeof RRToast !== 'undefined') {
                RRToast.error(data.message);
            } else {
                alert(data.message);
            }
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        if (typeof RRToast !== 'undefined') {
            RRToast.error('Erro ao ativar conta de afiliado');
        }
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
