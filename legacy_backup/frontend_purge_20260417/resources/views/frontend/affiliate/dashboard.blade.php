<div class="affiliate-dashboard-wrapper">
    <!-- Header com Tier e Stats Principais -->
    <div class="dashboard-header mb-4">
        <div class="tier-badge tier-{{ strtolower($stats['tier']->name ?? 'bronze') }}">
            <span class="tier-emoji">{{ $stats['tier']->emoji ?? '🥉' }}</span>
            <div class="tier-info">
                <span class="tier-label">Seu Nível</span>
                <span class="tier-name">{{ $stats['tier']->name ?? 'Bronze' }}</span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card stat-referrals">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <span class="stat-value">{{ $stats['active_referrals'] }}</span>
                <span class="stat-label">Indicações</span>
            </div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <span class="stat-value">R$ {{ number_format($stats['pending_commission'], 2, ',', '.') }}</span>
                <span class="stat-label">Pendente (Total)</span>
            </div>
        </div>
        <div class="stat-card stat-total">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <span class="stat-value">R$ {{ number_format($stats['available_balance'], 2, ',', '.') }}</span>
                <span class="stat-label">Disponível Saque</span>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar mb-4 text-end">
        <button class="btn btn-success btn-lg" onclick="openWithdrawModal()" {{ $stats['available_balance'] < 10 ? 'disabled' : '' }}>
            💸 Solicitar Saque
        </button>
        <div class="text-muted small mt-1">Mínimo: R$ 10,00</div>
    </div>

    <!-- Modal de Saque -->
    <div id="withdrawModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>💸 Solicitar Saque</h3>
                <button class="close-btn" onclick="closeWithdrawModal()">×</button>
            </div>
            <form action="{{ route('user.affiliate.withdraw.request') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Saldo Disponível: <strong>R$ {{ number_format($stats['available_balance'], 2, ',', '.') }}</strong></p>
                    
                    <div class="form-group mb-3">
                        <label>Valor (R$)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="10" max="{{ $stats['available_balance'] }}" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Chave PIX ou Dados Bancários</label>
                        <input type="text" name="payment_details" class="form-control" required placeholder="CPF, Email, Telefone ou Chave Aleatória">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWithdrawModal()">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Solicitação</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Link de Indicação -->
    <div class="referral-link-section mb-4">
        <h5 class="section-title">🔗 Seu Link de Indicação</h5>
        <div class="referral-link-box">
            <input type="text" class="referral-input" id="referralLink" 
                   value="{{ $stats['referral_link'] }}" 
                   readonly>
            <div class="referral-actions">
                <button class="btn-copy" onclick="copyReferralLink()" title="Copiar">
                    <i class="las la-copy"></i>
                </button>
                <button class="btn-share" onclick="shareWhatsApp()" title="WhatsApp">
                    <i class="lab la-whatsapp"></i>
                </button>
            </div>
        </div>
        <p class="referral-code">Código: <strong>{{ $stats['referral_code'] }}</strong></p>
    </div>

    <!-- Comissões por Tipo -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="commission-card commission-x1">
                <div class="commission-header">
                    <span class="commission-icon">🎯</span>
                    <h5>Salas X1</h5>
                </div>
                <div class="commission-body">
                    <div class="commission-rate">
                        <span class="rate-percent">{{ $stats['tier']->x1_commission_percent ?? 20 }}%</span>
                        <span class="rate-desc">do lucro da casa</span>
                    </div>
                    <p class="commission-note">Você ganha quando seu indicado joga X1</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="commission-card commission-fantasy">
                <div class="commission-header">
                    <span class="commission-icon">🏆</span>
                    <h5>Fantasy</h5>
                </div>
                <div class="commission-body">
                    <div class="commission-rate">
                        <span class="rate-percent">{{ $stats['tier']->fantasy_commission_percent ?? 2 }}%</span>
                        <span class="rate-desc">do prêmio ganho</span>
                    </div>
                    <p class="commission-note">Você ganha quando seu indicado ganha prêmio</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progresso para Próximo Nível -->
    @if($stats['next_tier'])
    <div class="next-tier-section mb-4">
        <h5 class="section-title">📈 Próximo Nível: {{ $stats['next_tier']->name }}</h5>
        <div class="progress-wrapper">
            <div class="progress-bar-custom">
                <div class="progress-fill" style="width: {{ min(100, ($stats['active_referrals'] / max(1, $stats['next_tier']->min_referrals)) * 100) }}%"></div>
            </div>
            <div class="progress-info">
                <span>{{ $stats['active_referrals'] }} / {{ $stats['next_tier']->min_referrals }} indicações</span>
                <span>Faltam {{ max(0, $stats['next_tier']->min_referrals - $stats['active_referrals']) }}</span>
            </div>
        </div>
    </div>
    @else
    <div class="max-tier-badge mb-4">
        <span class="max-icon">👑</span>
        <span>Você já alcançou o nível máximo!</span>
    </div>
    @endif

    <!-- Tabs: Comissões e Indicados -->
    <div class="dashboard-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="commissions">
                💵 Comissões
            </button>
            <button class="tab-btn" data-tab="referrals">
                👥 Indicados
            </button>
            <button class="tab-btn" data-tab="payments">
                💸 Saques
            </button>
        </div>

        <!-- Tab: Comissões -->
        <div class="tab-content active" id="tab-commissions">
            @if($recentCommissions->count() > 0)
            <div class="commission-list">
                @foreach($recentCommissions as $commission)
                <div class="commission-item">
                    <div class="commission-type">
                        @if($commission->source_type === 'x1')
                            <span class="type-badge type-x1">X1</span>
                        @else
                            <span class="type-badge type-fantasy">Fantasy</span>
                        @endif
                    </div>
                    <div class="commission-details">
                        <span class="commission-user">{{ $commission->referred_user?->username ?? 'Usuário' }}</span>
                        <span class="commission-date">{{ $commission->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="commission-amount">
                        <span class="amount">R$ {{ number_format($commission->commission_amount, 2, ',', '.') }}</span>
                        <span class="status status-{{ $commission->status }}">
                            @if($commission->status === 'pending')
                                Pendente
                            @elseif($commission->status === 'approved')
                                Aprovada
                            @else
                                Paga
                            @endif
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <span class="empty-icon">📭</span>
                <p>Nenhuma comissão ainda</p>
                <small>Compartilhe seu link para começar a ganhar!</small>
            </div>
            @endif
        </div>

        <!-- Tab: Indicados -->
        <div class="tab-content" id="tab-referrals">
            @if($referrals->count() > 0)
            <div class="referral-list">
                @foreach($referrals as $ref)
                <div class="referral-item">
                    <div class="referral-number">
                        {{ $loop->iteration + ($referrals->currentPage() - 1) * $referrals->perPage() }}
                    </div>
                    <div class="referral-name">{{ $ref->username }}</div>
                    <div class="referral-date">Entrou em {{ $ref->created_at->format('d/m/Y') }}</div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-3 pagination-wrapper">
                {{ $referrals->appends(request()->query())->links() }}
            </div>
            @else
            <div class="empty-state">
                <span class="empty-icon">👥</span>
                <p>Nenhuma indicação ainda</p>
            </div>
            @endif
        </div>

        <!-- Tab: Saques -->
        <div class="tab-content" id="tab-payments">
            @if($recentPayments->count() > 0)
            <div class="commission-list">
                @foreach($recentPayments as $payment)
                <div class="commission-item">
                    <div class="commission-type">
                        <span class="type-badge type-fantasy" style="background: #8b5cf6;">PIX</span>
                    </div>
                    <div class="commission-details">
                        <span class="commission-user">{{ $payment->status === 'pending' ? 'Solicitado' : 'Pago' }}</span>
                        <span class="commission-date">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="commission-amount">
                        <span class="amount">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                        <span class="status status-{{ $payment->status }}">
                            @if($payment->status === 'pending')
                                Em Análise
                            @elseif($payment->status === 'paid')
                                Pago
                            @else
                                Rejeitado
                            @endif
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <span class="empty-icon">💸</span>
                <p>Nenhum saque ainda</p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.affiliate-dashboard-wrapper {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem;
}

/* Tier Badge */
.tier-badge {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    padding: 1rem 1.5rem;
    border: 2px solid;
}

.tier-bronze { border-color: #cd7f32; }
.tier-prata, .tier-silver { border-color: #c0c0c0; }
.tier-ouro, .tier-gold { border-color: #ffd700; }
.tier-diamante, .tier-diamond { border-color: #b9f2ff; background: linear-gradient(135deg, rgba(185,242,255,0.1), rgba(255,255,255,0.05)); }

.tier-emoji { font-size: 2.5rem; }
.tier-label { font-size: 0.75rem; color: rgba(255,255,255,0.5); text-transform: uppercase; }
.tier-name { font-size: 1.25rem; font-weight: 700; }
.tier-info { display: flex; flex-direction: column; }

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.stat-card {
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid rgba(255,255,255,0.1);
}

.stat-icon { font-size: 2rem; }
.stat-content { display: flex; flex-direction: column; }
.stat-value { font-size: 1.25rem; font-weight: 700; }
.stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.6); }

.stat-referrals { border-left: 3px solid #3b82f6; }
.stat-pending { border-left: 3px solid #f59e0b; }
.stat-total { border-left: 3px solid #10b981; }

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr; }
}

/* Referral Link */
.referral-link-section {
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    padding: 1.25rem;
}

.section-title {
    font-size: 1rem;
    margin-bottom: 0.75rem;
}

.referral-link-box {
    display: flex;
    gap: 0.5rem;
    background: rgba(0,0,0,0.3);
    border-radius: 0.75rem;
    padding: 0.5rem;
}

.referral-input {
    flex: 1;
    background: transparent;
    border: none;
    color: white;
    padding: 0.5rem;
    font-size: 0.9rem;
}

.referral-input:focus { outline: none; }

.referral-actions { display: flex; gap: 0.5rem; }

.btn-copy, .btn-share {
    background: rgba(255,255,255,0.1);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1.25rem;
}

.btn-copy:hover { background: #3b82f6; }
.btn-share:hover { background: #25d366; }

.referral-code {
    margin: 0.5rem 0 0;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
}

/* Commission Cards */
.commission-card {
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
    height: 100%;
}

.commission-x1 { border-top: 3px solid #3b82f6; }
.commission-fantasy { border-top: 3px solid #10b981; }

.commission-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: rgba(255,255,255,0.03);
}

.commission-header h5 { margin: 0; font-size: 1rem; }
.commission-icon { font-size: 1.5rem; }

.commission-body { padding: 1rem; text-align: center; }

.commission-rate {
    display: flex;
    flex-direction: column;
    margin-bottom: 0.5rem;
}

.rate-percent {
    font-size: 2rem;
    font-weight: 700;
    color: #10b981;
}

.rate-desc {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.6);
}

.commission-note {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
    margin: 0;
}

/* Progress */
.next-tier-section {
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    padding: 1.25rem;
}

.progress-wrapper { margin-top: 0.5rem; }

.progress-bar-custom {
    height: 12px;
    background: rgba(255,255,255,0.1);
    border-radius: 6px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #f97316, #ea580c);
    border-radius: 6px;
    transition: width 0.3s;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.6);
    margin-top: 0.5rem;
}

.max-tier-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background: linear-gradient(135deg, rgba(255,215,0,0.1), rgba(255,255,255,0.05));
    border: 1px solid rgba(255,215,0,0.3);
    border-radius: 1rem;
    padding: 1rem;
    font-weight: 600;
}

.max-icon { font-size: 1.5rem; }

/* Tabs */
.dashboard-tabs {
    background: rgba(255,255,255,0.05);
    border-radius: 1rem;
    overflow: hidden;
}

.tab-buttons {
    display: flex;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.tab-btn {
    flex: 1;
    background: none;
    border: none;
    color: rgba(255,255,255,0.6);
    padding: 1rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-btn.active {
    color: white;
    background: rgba(255,255,255,0.05);
    border-bottom: 2px solid #f97316;
}

.tab-content { display: none; padding: 1rem; }
.tab-content.active { display: block; }

/* Commission List */
.commission-list { display: flex; flex-direction: column; gap: 0.5rem; }

.commission-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: rgba(255,255,255,0.03);
    border-radius: 0.5rem;
}

.type-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 600;
}

.type-x1 { background: #3b82f6; }
.type-fantasy { background: #10b981; }

.commission-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.commission-user { font-weight: 500; }
.commission-date { font-size: 0.75rem; color: rgba(255,255,255,0.5); }

.commission-amount {
    text-align: right;
    display: flex;
    flex-direction: column;
}

.amount { font-weight: 700; color: #10b981; }

.status {
    font-size: 0.7rem;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
}

.status-pending { background: rgba(245,158,11,0.2); color: #f59e0b; }
.status-approved { background: rgba(59,130,246,0.2); color: #3b82f6; }
.status-paid { background: rgba(16,185,129,0.2); color: #10b981; }

/* Referral List */
.referral-list { display: flex; flex-direction: column; gap: 0.5rem; }

.referral-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: rgba(255,255,255,0.03);
    border-radius: 0.5rem;
}

.referral-number {
    width: 30px;
    height: 30px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.referral-name { flex: 1; font-weight: 500; }
.referral-date { font-size: 0.8rem; color: rgba(255,255,255,0.5); }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: rgba(255,255,255,0.5);
}

.empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 0.5rem;
}

.empty-state p { margin: 0.5rem 0; }
.empty-state small { font-size: 0.8rem; }
/* Modal */
.modal-overlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: #1a1a2e; /* Dark theme bg */
    padding: 1.5rem;
    border-radius: 1rem;
    width: 90%;
    max-width: 400px;
    border: 1px solid rgba(255,255,255,0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    border-radius: 0.5rem;
    margin-top: 0.25rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn-secondary {
    background: rgba(255,255,255,0.1);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
}

.btn-success {
    background: #10b981;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
}
</style>

<script>
function openWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'flex';
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Check URL params for active tab (for pagination)
    const urlParams = new URLSearchParams(window.location.search);
    const activeTabParam = urlParams.get('tab'); // ou referrals_page se quiser inferir
    
    // Se tiver page de referrals, ativa a tab referrals
    if (urlParams.has('referrals_page')) {
        const btn = document.querySelector(`.tab-btn[data-tab="referrals"]`);
        if(btn) btn.click();
    }

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');
        });
    });
});

function copyReferralLink() {
    const input = document.getElementById('referralLink');
    navigator.clipboard?.writeText(input.value).catch(() => {
        input.select();
        document.execCommand('copy');
    });
    
    if (typeof RRToast !== 'undefined') {
        RRToast.success('Link copiado!');
    } else {
        alert('Link copiado!');
    }
}

function shareWhatsApp() {
    const link = document.getElementById('referralLink').value;
    const text = '🤠 Junte-se a mim no Rei do Rodeio! Monte seu time e ganhe prêmios: ';
    window.open(`https://wa.me/?text=${encodeURIComponent(text + link)}`, '_blank');
}
</script>
