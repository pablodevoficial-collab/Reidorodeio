/**
 * 🎯 X1 ARENA - Versão Clean com PIX Integrado
 * Sistema completo de criação de salas X1 com pagamento PIX
 */

(function() {
    'use strict';
    
    console.log('🚀 X1 Arena Clean carregado!');

    // ==========================
    // STATE
    // ==========================
    const state = {
        isOpen: false,
        step: 1, // 1=valor, 2=rodeio, 3=modalidade, 4=divisao, 5=competidor, 6=payment
        isPremium: false,
        
        // Form
        valorEntrada: 0,
        rodeioId: null,
        modalidadeId: null,
        divisao: '',
        competitorId: null,
        groupId: null,
        isGroup: false,
        teamSize: 1,
        isClassificatoria: false, // ✅ Flag de classificatória (sem divisão)
        
        // Data
        rodeios: [],
        modalidades: [],
        divisoes: [],
        competitors: [],
        
        // Payment
        preferenceId: null,
        paymentData: null,
        pollingInterval: null
    };

    let modal = null;

    // ==========================
    // UTILITIES
    // ==========================
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function formatBRL(value) {
        return parseFloat(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function calculateFee(amount) {
        const fee = amount <= 1000 ? (state.isPremium ? 7 : 10) : (state.isPremium ? 10 : 15);
        const total = amount * 2;
        const feeAmount = total * (fee / 100);
        return {
            fee,
            prize: Math.round((total - feeAmount) * 100) / 100
        };
    }

    // ==========================
    // HTML TEMPLATE
    // ==========================
    function createHTML() {
        return `
            <div class="x1-arena" id="x1Arena">
                <div class="x1-arena__backdrop"></div>
                <div class="x1-arena__container">
                    <button class="x1-arena__close" id="x1ArenaClose">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="x1-arena__content" id="x1ArenaContent">
                        ${renderStep()}
                    </div>
                </div>
            </div>
        `;
    }

    function renderStep() {
        switch (state.step) {
            case 1: return renderStepValor();
            case 2: return renderStepRodeio();
            case 3: return renderStepModalidade();
            case 4: return renderStepDivisao();
            case 5: return renderStepCompetidor();
            case 6: return renderStepPayment();
            default: return '';
        }
    }

    // ==========================
    // STEP 1: VALOR
    // ==========================
    function renderStepValor() {
        const calc = calculateFee(state.valorEntrada);
        return `
            <div class="x1-step">
                <h2 class="x1-step__title">💰 Valor de Entrada</h2>
                <div class="x1-step__body">
                    <input type="number" 
                           class="x1-input" 
                           id="x1InputValor"
                           placeholder="R$ 20,00" 
                           min="0" 
                           step="0.01"
                           value="${state.valorEntrada || ''}">
                    
                    ${state.valorEntrada > 0 ? `
                        <div class="x1-calc">
                            <div class="x1-calc__row">
                                <span>Taxa:</span>
                                <span>${calc.fee}%</span>
                            </div>
                            <div class="x1-calc__row x1-calc__row--prize">
                                <span>🏆 Prêmio:</span>
                                <span>${formatBRL(calc.prize)}</span>
                            </div>
                        </div>
                    ` : ''}
                    
                    <button class="x1-btn x1-btn--primary" 
                            id="x1BtnNext"
                            ${state.valorEntrada < 0 ? 'disabled' : ''}>
                        Continuar
                    </button>
                </div>
            </div>
        `;
    }

    // ==========================
    // STEP 2: RODEIO
    // ==========================
    function renderStepRodeio() {
        return `
            <div class="x1-step">
                <h2 class="x1-step__title">🏛️ Escolha o Rodeio</h2>
                <div class="x1-step__body">
                    <div class="x1-grid" id="x1GridRodeio">
                        ${state.rodeios.length === 0 ? '<p class="x1-empty">Carregando...</p>' : ''}
                        ${state.rodeios.map(r => `
                            <div class="x1-card ${state.rodeioId === r.id ? 'is-selected' : ''}" 
                                 data-id="${r.id}" 
                                 data-name="${escapeHtml(r.nome)}">
                                <span class="x1-card__name">${escapeHtml(r.nome)}</span>
                                ${state.rodeioId === r.id ? '<i class="fas fa-check x1-card__check"></i>' : ''}
                            </div>
                        `).join('')}
                    </div>
                    <div class="x1-actions">
                        <button class="x1-btn x1-btn--secondary" onclick="window.X1Arena.prevStep()">Voltar</button>
                        <button class="x1-btn x1-btn--primary" 
                                id="x1BtnNext"
                                ${!state.rodeioId ? 'disabled' : ''}>
                            Continuar
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // ==========================
    // STEP 3: MODALIDADE
    // ==========================
    function renderStepModalidade() {
        return `
            <div class="x1-step">
                <h2 class="x1-step__title">🎯 Escolha a Modalidade</h2>
                <div class="x1-step__body">
                    <div class="x1-grid" id="x1GridModalidade">
                        ${state.modalidades.length === 0 ? '<p class="x1-empty">Carregando...</p>' : ''}
                        ${state.modalidades.map(m => `
                            <div class="x1-card ${state.modalidadeId === m.id ? 'is-selected' : ''}" 
                                 data-id="${m.id}" 
                                 data-name="${escapeHtml(m.nome)}"
                                 data-team-size="${m.tamanho_equipe || 1}"
                                 data-divisoes='${JSON.stringify(m.divisoes || [])}'>
                                <span class="x1-card__name">${escapeHtml(m.nome)}</span>
                                ${m.tamanho_equipe > 1 ? `<span class="x1-card__badge">${m.tamanho_equipe}x${m.tamanho_equipe}</span>` : ''}
                                ${state.modalidadeId === m.id ? '<i class="fas fa-check x1-card__check"></i>' : ''}
                            </div>
                        `).join('')}
                    </div>
                    <div class="x1-actions">
                        <button class="x1-btn x1-btn--secondary" onclick="window.X1Arena.prevStep()">Voltar</button>
                        <button class="x1-btn x1-btn--primary" 
                                id="x1BtnNext"
                                ${!state.modalidadeId ? 'disabled' : ''}>
                            Continuar
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // ==========================
    // STEP 4: DIVISÃO
    // ==========================
    function renderStepDivisao() {
        return `
            <div class="x1-step">
                <h2 class="x1-step__title">📊 Escolha a Divisão</h2>
                <div class="x1-step__body">
                    <div class="x1-grid" id="x1GridDivisao">
                        ${state.divisoes.map(d => `
                            <div class="x1-card ${state.divisao === d ? 'is-selected' : ''}" 
                                 data-divisao="${escapeHtml(d)}">
                                <span class="x1-card__name">${escapeHtml(d)}</span>
                                ${state.divisao === d ? '<i class="fas fa-check x1-card__check"></i>' : ''}
                            </div>
                        `).join('')}
                    </div>
                    <div class="x1-actions">
                        <button class="x1-btn x1-btn--secondary" onclick="window.X1Arena.prevStep()">Voltar</button>
                        <button class="x1-btn x1-btn--primary" 
                                id="x1BtnNext"
                                ${!state.divisao ? 'disabled' : ''}>
                            Continuar
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // ==========================
    // STEP 5: COMPETIDOR
    // ==========================
    function renderStepCompetidor() {
        const label = state.isGroup ? 'Escolha seu Grupo' : 'Escolha seu Competidor';
        const selected = state.isGroup ? state.groupId : state.competitorId;
        
        return `
            <div class="x1-step">
                <h2 class="x1-step__title">👤 ${label}</h2>
                <div class="x1-step__body">
                    <input type="text" 
                           class="x1-input x1-input--search" 
                           id="x1SearchCompetitor"
                           placeholder="Buscar...">
                    
                    <div class="x1-grid x1-grid--competitors" id="x1GridCompetitor">
                        ${state.competitors.length === 0 ? '<p class="x1-empty">Carregando...</p>' : ''}
                        ${state.competitors.map(c => {
                            const id = state.isGroup ? c.group_id : c.competitor_id || c.id;
                            const name = state.isGroup ? c.group_name : c.competitor_name || c.nome;
                            const isSelected = selected == id;
                            
                            return `
                                <div class="x1-card x1-card--competitor ${isSelected ? 'is-selected' : ''}" 
                                     data-id="${id}" 
                                     data-name="${escapeHtml(name)}">
                                    ${state.isGroup ? renderGroupMembers(c.members) : renderCompetitorPhoto(c)}
                                    <span class="x1-card__name">${escapeHtml(name)}</span>
                                    ${isSelected ? '<i class="fas fa-check x1-card__check"></i>' : ''}
                                </div>
                            `;
                        }).join('')}
                    </div>
                    
                    <div class="x1-actions">
                        <button class="x1-btn x1-btn--secondary" onclick="window.X1Arena.prevStep()">Voltar</button>
                        <button class="x1-btn x1-btn--primary" 
                                id="x1BtnSubmit"
                                ${!selected ? 'disabled' : ''}>
                            Criar Sala e Pagar
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function renderCompetitorPhoto(c) {
        let foto = c.foto || c.foto_url || '/assets/images/logo_icon/favicon.png';
        if (foto && !foto.startsWith('/') && !foto.startsWith('http')) {
            foto = '/storage/' + foto;
        }
        return `<div class="x1-card__photo"><img src="${foto}" alt="${escapeHtml(c.nome)}" onerror="this.src='/assets/images/logo_icon/favicon.png'"></div>`;
    }

    function renderGroupMembers(members) {
        if (!members || members.length === 0) return '';
        return `
            <div class="x1-card__group">
                ${members.map(m => {
                    let foto = m.foto || '/assets/images/logo_icon/favicon.png';
                    if (foto && !foto.startsWith('/') && !foto.startsWith('http')) {
                        foto = '/storage/' + foto;
                    }
                    return `<img src="${foto}" alt="${escapeHtml(m.nome)}" onerror="this.src='/assets/images/logo_icon/favicon.png'">`;
                }).join('')}
            </div>
        `;
    }

    // ==========================
    // STEP 6: PAYMENT (PIX)
    // ==========================
    function renderStepPayment() {
        if (!state.paymentData) {
            return `
                <div class="x1-step x1-step--payment">
                    <div class="x1-loading">
                        <div class="x1-spinner"></div>
                        <p>Gerando pagamento...</p>
                    </div>
                </div>
            `;
        }

        const { qr_code_base64, qr_code } = state.paymentData;
        
        return `
            <div class="x1-step x1-step--payment">
                <h2 class="x1-step__title">📱 Pague com PIX</h2>
                <div class="x1-step__body">
                    <div class="x1-qr">
                        <img src="data:image/png;base64,${qr_code_base64}" alt="QR Code PIX" class="x1-qr__img">
                    </div>
                    
                    ${qr_code ? `
                        <div class="x1-pix-code">
                            <input type="text" 
                                   class="x1-input x1-input--code" 
                                   id="x1PixCode"
                                   value="${qr_code}" 
                                   readonly>
                            <button class="x1-btn x1-btn--copy" id="x1BtnCopy">
                                📋 Copiar
                            </button>
                        </div>
                    ` : ''}
                    
                    <div class="x1-status">
                        <div class="x1-status__pulse"></div>
                        <p>Aguardando confirmação...</p>
                    </div>
                    
                    <button class="x1-btn x1-btn--check" id="x1BtnCheck">
                        ✅ PIX já foi pago!
                    </button>
                    
                    <button class="x1-btn x1-btn--cancel" id="x1BtnCancel">
                        ❌ Cancelar Sala
                    </button>
                </div>
            </div>
        `;
    }

    // ==========================
    // NAVIGATION
    // ==========================
    function nextStep() {
        // ✅ Pular divisão se: classificatória OU sem divisões OU nenhum competidor/grupo tem divisão atribuída
        if (state.step === 3 && (state.isClassificatoria || state.divisoes.length === 0 || !state.hasAssignedDivisions)) {
            state.step = 5; // Pular direto para competidor
        } else if (state.step === 4 && (state.divisoes.length === 0 || !state.hasAssignedDivisions)) {
            state.step = 5; // Pular divisão se não tiver ou se nenhum competidor tem divisão
        } else if (state.step === 5) {
            createRoom(); // Submit ao invés de next
            return;
        } else {
            state.step++;
        }
        
        render();
        bindCurrentStepEvents();
    }

    function prevStep() {
        // ✅ Voltar pulando divisão se: classificatória OU sem divisões OU nenhum competidor/grupo tem divisão
        if (state.step === 5 && (state.isClassificatoria || state.divisoes.length === 0 || !state.hasAssignedDivisions)) {
            state.step = 3; // Voltar para modalidade se pulou divisão
        } else {
            state.step--;
        }
        
        render();
        bindCurrentStepEvents();
    }

    // ==========================
    // RENDER
    // ==========================
    function render() {
        if (!modal) return;
        const content = document.getElementById('x1ArenaContent');
        if (content) {
            content.innerHTML = renderStep();
        }
    }

    // ==========================
    // EVENT BINDINGS
    // ==========================
    function bindCurrentStepEvents() {
        const nextBtn = document.getElementById('x1BtnNext');
        const submitBtn = document.getElementById('x1BtnSubmit');
        
        if (nextBtn) {
            nextBtn.addEventListener('click', nextStep);
        }
        
        if (submitBtn) {
            submitBtn.addEventListener('click', createRoom);
        }
        
        // Step-specific bindings
        switch (state.step) {
            case 1: bindStepValor(); break;
            case 2: bindStepRodeio(); break;
            case 3: bindStepModalidade(); break;
            case 4: bindStepDivisao(); break;
            case 5: bindStepCompetidor(); break;
            case 6: bindStepPayment(); break;
        }
    }

    function bindStepValor() {
        const input = document.getElementById('x1InputValor');
        if (input) {
            input.focus();
            input.addEventListener('input', () => {
                state.valorEntrada = parseFloat(input.value) || 0;
                render();
                bindCurrentStepEvents();
            });
        }
    }

    function bindStepRodeio() {
        if (state.rodeios.length === 0) {
            loadRodeios();
        }
        
        document.querySelectorAll('#x1GridRodeio .x1-card').forEach(card => {
            card.addEventListener('click', () => {
                state.rodeioId = parseInt(card.dataset.id);
                render();
                bindCurrentStepEvents();
            });
        });
    }

    function bindStepModalidade() {
        if (state.modalidades.length === 0) {
            loadModalidades();
        }
        
        document.querySelectorAll('#x1GridModalidade .x1-card').forEach(card => {
            card.addEventListener('click', () => {
                state.modalidadeId = parseInt(card.dataset.id);
                state.teamSize = parseInt(card.dataset.teamSize) || 1;
                state.isGroup = state.teamSize > 1;
                
                // ✅ Verificar classificatória e divisões atribuídas
                const modalidade = state.modalidades.find(m => m.id == state.modalidadeId);
                state.isClassificatoria = modalidade?.is_classificatoria === true;
                state.hasAssignedDivisions = modalidade?.has_assigned_divisions === true;
                
                try {
                    state.divisoes = JSON.parse(card.dataset.divisoes || '[]');
                } catch (e) {
                    state.divisoes = [];
                }
                
                render();
                bindCurrentStepEvents();
            });
        });
    }

    function bindStepDivisao() {
        document.querySelectorAll('#x1GridDivisao .x1-card').forEach(card => {
            card.addEventListener('click', () => {
                state.divisao = card.dataset.divisao;
                render();
                bindCurrentStepEvents();
            });
        });
    }

    function bindStepCompetidor() {
        if (state.competitors.length === 0) {
            loadCompetitors();
        }
        
        const searchInput = document.getElementById('x1SearchCompetitor');
        if (searchInput) {
            searchInput.addEventListener('input', filterCompetitors);
        }
        
        document.querySelectorAll('#x1GridCompetitor .x1-card').forEach(card => {
            card.addEventListener('click', () => {
                const id = parseInt(card.dataset.id);
                if (state.isGroup) {
                    state.groupId = id;
                    state.competitorId = null;
                } else {
                    state.competitorId = id;
                    state.groupId = null;
                }
                render();
                bindCurrentStepEvents();
            });
        });
    }

    function bindStepPayment() {
        const copyBtn = document.getElementById('x1BtnCopy');
        if (copyBtn) {
            copyBtn.addEventListener('click', copyPixCode);
        }
        
        const checkBtn = document.getElementById('x1BtnCheck');
        if (checkBtn) {
            checkBtn.addEventListener('click', checkPaymentStatus);
        }
        
        const cancelBtn = document.getElementById('x1BtnCancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', cancelRoom);
        }
        
        // Start polling
        startPolling();
    }

    function filterCompetitors() {
        const search = document.getElementById('x1SearchCompetitor').value.toLowerCase();
        document.querySelectorAll('#x1GridCompetitor .x1-card').forEach(card => {
            const name = card.dataset.name.toLowerCase();
            card.style.display = name.includes(search) ? '' : 'none';
        });
    }

    // ==========================
    // API CALLS
    // ==========================
    async function loadRodeios() {
        try {
            const res = await fetch('/api/realtime/rodeios');
            const data = await res.json();
            state.rodeios = data.data || [];
            render();
            bindCurrentStepEvents();
        } catch (e) {
            console.error('Erro ao carregar rodeios:', e);
        }
    }

    async function loadModalidades() {
        try {
            const url = state.rodeioId 
                ? `/api/realtime/modalidades?rodeio_id=${state.rodeioId}`
                : '/api/realtime/modalidades';
            const res = await fetch(url);
            const data = await res.json();
            state.modalidades = data.data || [];
            render();
            bindCurrentStepEvents();
        } catch (e) {
            console.error('Erro ao carregar modalidades:', e);
        }
    }

    async function loadCompetitors() {
        try {
            const params = new URLSearchParams();
            if (state.divisao) params.append('divisao', state.divisao);
            params.append('modo', state.isGroup ? 'grupos' : 'competidores');
            
            const res = await fetch(`/api/realtime/competitors/modalidade/${state.modalidadeId}?${params}`);
            const data = await res.json();
            state.competitors = data.data || [];
            render();
            bindCurrentStepEvents();
        } catch (e) {
            console.error('Erro ao carregar competidores:', e);
        }
    }

    async function createRoom() {
        const submitBtn = document.getElementById('x1BtnSubmit');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando...';
        }
        
        try {
            const payload = {
                valor_entrada: state.valorEntrada,
                rodeio_id: state.rodeioId,
                modalidade_id: state.modalidadeId,
                divisao: state.divisao || null
            };
            
            if (state.isGroup) {
                payload.competitor_group_id = state.groupId;
            } else {
                payload.competitor_id = state.competitorId;
            }
            
            const res = await fetch('/api/x1', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(payload)
            });
            
            const data = await res.json();
            
            if (res.ok) {
                state.preferenceId = data.payment?.preference_id;
                await generatePix();
            } else {
                alert(data.message || 'Erro ao criar sala');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Criar Sala e Pagar';
                }
            }
        } catch (e) {
            console.error('Erro ao criar sala:', e);
            alert('Erro ao criar sala. Tente novamente.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Criar Sala e Pagar';
            }
        }
    }

    async function generatePix() {
        state.step = 6;
        render();
        
        try {
            const res = await fetch('/api/x1/process-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    formData: {},
                    preferenceId: state.preferenceId
                })
            });
            
            const data = await res.json();
            
            if (res.ok && (data.qr_code_base64 || data.qr_code)) {
                state.paymentData = data;
                render();
                bindCurrentStepEvents();
            } else {
                alert('Erro ao gerar PIX');
                close();
            }
        } catch (e) {
            console.error('Erro ao gerar PIX:', e);
            alert('Erro ao gerar PIX. Tente novamente.');
            close();
        }
    }

    function copyPixCode() {
        const input = document.getElementById('x1PixCode');
        if (input) {
            input.select();
            document.execCommand('copy');
            
            const btn = document.getElementById('x1BtnCopy');
            if (btn) {
                btn.innerHTML = '✅ Copiado!';
                setTimeout(() => {
                    btn.innerHTML = '📋 Copiar';
                }, 2000);
            }
        }
    }

    async function checkPaymentStatus() {
        const btn = document.getElementById('x1BtnCheck');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        }
        
        try {
            const res = await fetch(`/api/x1/payment-status?preference_id=${state.preferenceId}`, {
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            });
            
            const data = await res.json();
            
            if (data.status === 'approved') {
                alert('✅ Pagamento confirmado! Sala aberta.');
                window.location.reload();
            } else if (String(data.status || '').toLowerCase().startsWith('refunded') || data.wallet_refunded) {
                alert(data.message || 'A sala ja foi preenchida antes da confirmacao e o valor voltou para sua carteira.');
                window.location.reload();
            } else {
                alert('⏳ Pagamento ainda não confirmado');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '✅ PIX já foi pago!';
                }
            }
        } catch (e) {
            console.error('Erro ao verificar pagamento:', e);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '✅ PIX já foi pago!';
            }
        }
    }

    function startPolling() {
        if (state.pollingInterval) {
            clearInterval(state.pollingInterval);
        }
        
        state.pollingInterval = setInterval(async () => {
            try {
                const res = await fetch(`/api/x1/payment-status?preference_id=${state.preferenceId}`, {
                    headers: { 'X-CSRF-TOKEN': getCsrfToken() }
                });
                
                const data = await res.json();
                
                if (data.status === 'approved') {
                    clearInterval(state.pollingInterval);
                    alert('✅ Pagamento confirmado! Sala aberta.');
                    window.location.reload();
                } else if (String(data.status || '').toLowerCase().startsWith('refunded') || data.wallet_refunded) {
                    clearInterval(state.pollingInterval);
                    alert(data.message || 'A sala ja foi preenchida antes da confirmacao e o valor voltou para sua carteira.');
                    window.location.reload();
                }
            } catch (e) {
                console.log('Erro no polling:', e);
            }
        }, 3000);
    }

    async function cancelRoom() {
        if (!confirm('Deseja realmente cancelar a sala?')) return;
        
        try {
            const res = await fetch('/api/x1/cancel-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ preferenceId: state.preferenceId })
            });
            
            if (res.ok) {
                alert('Sala cancelada');
                window.location.reload();
            } else {
                alert('Erro ao cancelar sala');
            }
        } catch (e) {
            console.error('Erro ao cancelar:', e);
        }
    }

    // ==========================
    // OPEN/CLOSE
    // ==========================
    function open(preset = {}) {
        // Verificar se está autenticado usando data attribute do body
        const body = document.body;
        const isAuth = body.getAttribute('data-user-authenticated') === '1';
        
        if (!isAuth) {
            if (typeof openAuthModal === 'function') {
                openAuthModal();
            } else {
                alert('Faça login para criar salas');
            }
            return;
        }
        
        // Verificar se é premium
        state.isPremium = body.getAttribute('data-user-premium') === '1';
        
        console.log('🔓 Usuário autenticado:', isAuth);
        console.log('💎 Usuário premium:', state.isPremium);
        
        // Reset state
        state.step = 1;
        state.valorEntrada = 0;
        state.rodeioId = null;
        state.modalidadeId = null;
        state.divisao = '';
        state.competitorId = null;
        state.groupId = null;
        state.isGroup = false;
        state.teamSize = 1;
        state.isClassificatoria = false;
        state.hasAssignedDivisions = false;
        state.rodeios = [];
        state.modalidades = [];
        state.divisoes = [];
        state.competitors = [];
        state.paymentData = null;

        const presetEntry = parseFloat(preset.valorEntrada || preset.valor_entrada || 0);
        const presetRodeioId = parseInt(preset.rodeioId || preset.rodeio_id || 0, 10) || null;
        const presetModalidadeId = parseInt(preset.modalidadeId || preset.modalidade_id || 0, 10) || null;
        const presetDivisao = String(preset.divisao || '').trim();
        const presetTeamSize = parseInt(preset.teamSize || preset.team_size || 1, 10) || 1;

        if (presetEntry > 0) {
            state.valorEntrada = presetEntry;
        }

        if (presetRodeioId) {
            state.rodeioId = presetRodeioId;
        }

        if (presetModalidadeId) {
            state.modalidadeId = presetModalidadeId;
            state.teamSize = Math.max(1, presetTeamSize);
            state.isGroup = state.teamSize > 1;
        }

        if (presetDivisao) {
            state.divisao = presetDivisao;
        }

        if (state.valorEntrada > 0 && state.modalidadeId) {
            state.step = 5;
        } else if (state.valorEntrada > 0 && state.rodeioId) {
            state.step = 3;
        } else if (state.valorEntrada > 0) {
            state.step = 2;
        }
        
        if (!modal) {
            const container = document.createElement('div');
            container.innerHTML = createHTML();
            document.body.appendChild(container.firstElementChild);
            modal = document.getElementById('x1Arena');
            
            // Bind close button
            document.getElementById('x1ArenaClose').addEventListener('click', close);
            modal.querySelector('.x1-arena__backdrop').addEventListener('click', close);
        }
        
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        state.isOpen = true;
        
        render();
        bindCurrentStepEvents();
    }

    function close() {
        if (state.pollingInterval) {
            clearInterval(state.pollingInterval);
        }
        
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
        
        state.isOpen = false;
    }

    // ==========================
    // GLOBAL EXPORT
    // ==========================
    window.X1Arena = {
        open,
        close,
        nextStep,
        prevStep
    };
})();
