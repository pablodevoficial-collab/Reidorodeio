@extends('admin.layouts.app')
@section('panel')
<div class="rr-scoring-page">
<div class="row rr-scoring-page__row">
    <!-- Seleção de Evento -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">@lang('Sistema de Pontuação em Tempo Real')</h4>
                <div class="badge badge--success" id="scoringStatus">@lang('Sistema Ativo')</div>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>@lang('Rodeio Ativo')</label>
                        <select id="scoringRodeio" class="form-control" onchange="loadScoringModalidades()">
                            <option value="">@lang('Selecione o Rodeio')</option>
                            @foreach(\App\Models\Rodeio::where('status', 'ativo')->get() as $rodeio)
                                <option value="{{ $rodeio->id }}">{{ $rodeio->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>@lang('Modalidade Atual')</label>
                        <select id="scoringModalidade" class="form-control" onchange="loadScoringCompetitors()">
                            <option value="">@lang('Selecione a Modalidade')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>@lang('Modo de Pontuação')</label>
                        <select id="scoringMode" class="form-control">
                            <option value="manual">@lang('Manual')</option>
                            <option value="automatico">@lang('Automático')</option>
                            <option value="cronometro">@lang('Com Cronômetro')</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn--primary w-100" onclick="initializeScoring()">
                            <i class="las la-play"></i> @lang('Inicializar')
                        </button>
                    </div>
                    <div class="col-md-3">
                    <button class="btn btn--danger w-100" onclick="disconnectQueuePolling()">
                            <i class="las la-power-off"></i> @lang('Parar Monitoramento')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Competidores para Pontuação -->
<div class="row mt-4 rr-scoring-page__row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('Competidores - Pontuação Rápida')</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn--warning" onclick="undoLastAction()" id="undoBtn" disabled>
                        <i class="las la-undo"></i> @lang('Desfazer')
                    </button>
                    <button class="btn btn-sm btn--info" onclick="showScoringHistory()">
                        <i class="las la-history"></i> @lang('Histórico')
                    </button>
                    <button class="btn btn-sm btn--success" onclick="saveCurrentState()">
                        <i class="las la-save"></i> @lang('Salvar Estado')
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="scoringCompetitors" class="row">
                    <div class="col-12 text-center py-5">
                        <i class="las la-trophy" style="font-size: 4rem; color: #ccc;"></i>
                        <p class="text-muted">@lang('Selecione um rodeio e modalidade para iniciar a pontuação')</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Painel de Estatísticas -->
<div class="row mt-4 rr-scoring-page__row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Estatísticas da Modalidade')</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--primary">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="totalParticipants">0</h3>
                                <p class="text-white">@lang('Participantes')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--success">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="completedScores">0</h3>
                                <p class="text-white">@lang('Pontuados')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--warning">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="averageScore">0.0</h3>
                                <p class="text-white">@lang('Média')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--info">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="highestScore">0.0</h3>
                                <p class="text-white">@lang('Maior Nota')</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Cronômetro')</h5>
            </div>
            <div class="card-body text-center">
                <div style="font-size: 3rem; font-weight: bold; color: #007bff;" id="timer">00:00</div>
                <div class="mt-3">
                    <button class="btn btn--success me-2" onclick="startTimer()">
                        <i class="las la-play"></i> @lang('Iniciar')
                    </button>
                    <button class="btn btn--warning me-2" onclick="pauseTimer()">
                        <i class="las la-pause"></i> @lang('Pausar')
                    </button>
                    <button class="btn btn--danger" onclick="resetTimer()">
                        <i class="las la-stop"></i> @lang('Reset')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log de Ações -->
<div class="row mt-4 rr-scoring-page__row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Log de Pontuações')</h5>
                <button class="btn btn-sm btn--secondary" onclick="clearScoringLog()">
                    <i class="las la-trash"></i> @lang('Limpar')
                </button>
            </div>
            <div class="card-body">
                <div id="scoringLog" style="height: 200px; overflow-y: auto;" class="bg-light p-3 rounded">
                    <div class="text-muted">@lang('As pontuações aparecerão aqui...')</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Tempo Personalizado -->
<div class="modal fade" id="customTimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Tempo Personalizado')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>@lang('Tempo (segundos)')</label>
                        <input type="number" step="0.01" id="customTimeValue" class="form-control" placeholder="8.50">
                    </div>
                    <div class="col-md-6">
                        <label>@lang('Pontuação')</label>
                        <input type="number" step="0.1" id="customScoreValue" class="form-control" placeholder="7.5">
                    </div>
                </div>
                <div class="mt-3">
                    <label>@lang('Observações')</label>
                    <textarea id="customObservations" class="form-control" rows="2" placeholder="@lang('Observações adicionais...')"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancelar')</button>
                <button type="button" class="btn btn--primary" onclick="applyCustomScore()">@lang('Aplicar')</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Histórico -->
<div class="modal fade" id="scoringHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Histórico de Pontuações')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Hora')</th>
                                <th>@lang('Competidor')</th>
                                <th>@lang('Ação')</th>
                                <th>@lang('Pontuação')</th>
                                <th>@lang('Operador')</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <!-- Histórico será carregado aqui -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('style')
<style>
.rr-scoring-page {
    display: grid;
    gap: 1rem;
}

.rr-scoring-page__row {
    --bs-gutter-y: 1rem;
}

.rr-scoring-page .card-header {
    gap: 0.75rem;
}

.rr-scoring-page .card-header .btn-group {
    flex-wrap: wrap;
    gap: 0.5rem;
}

.rr-scoring-page label {
    display: inline-block;
    margin-bottom: 0.45rem;
    font-weight: 700;
}

.rr-scoring-page .form-control,
.rr-scoring-page .form-select {
    min-height: 46px;
}

.rr-scoring-page .btn {
    min-height: 44px;
}

.scoring-card {
    border: 2px solid transparent;
    transition: all 0.3s ease;
    position: relative;
    height: 100%;
}

.scoring-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.scoring-card.scored {
    border-color: #28a745;
    background-color: #f8fff9;
}

.scoring-card.eliminated {
    border-color: #dc3545;
    background-color: #fff5f5;
    opacity: 0.8;
}

.scoring-actions {
    margin-top: 15px;
}

.scoring-actions .btn {
    margin: 2px;
    padding: 10px 12px;
    font-size: 0.85rem;
    min-height: 66px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.score-display {
    font-size: 1.5rem;
    font-weight: bold;
    color: #28a745;
    margin: 10px 0;
}

.competitor-timer {
    font-size: 0.9rem;
    color: #6c757d;
}

.action-btn-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 10px;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@media (max-width: 991.98px) {
    .rr-scoring-page .card-header.d-flex {
        align-items: flex-start !important;
    }

    .rr-scoring-page .card-header .btn-group {
        width: 100%;
    }

    .rr-scoring-page .card-header .btn-group .btn {
        flex: 1 1 calc(33.333% - 0.5rem);
    }
}

@media (max-width: 767px) {
    .rr-scoring-page .card-body {
        padding: 1rem;
    }

    .rr-scoring-page .row.align-items-end > [class*="col-"] {
        margin-bottom: 0.85rem;
    }

    .rr-scoring-page .card-header {
        flex-direction: column;
        align-items: stretch !important;
    }

    .rr-scoring-page .card-header .btn-group {
        display: grid;
        grid-template-columns: 1fr;
    }

    .action-btn-group {
        grid-template-columns: 1fr 1fr;
    }

    .score-display {
        font-size: 1.3rem;
    }

    #scoringLog {
        height: 180px !important;
    }
}

@media (max-width: 575.98px) {
    .action-btn-group {
        grid-template-columns: 1fr;
    }

    .scoring-actions .btn {
        min-height: 58px;
    }
}
</style>
@endpush

@push('script')
<script>
let scoringActive = false;
let currentScoringCompetitors = [];
let scoringHistory = [];
let lastAction = null;
let timerInterval = null;
let timerSeconds = 0;
let currentCompetitorForCustom = null;
let queuePollingInterval = null;

// Inicializar polling de filas ao carregar a página
$(document).ready(function() {
    initializeQueuePolling();
});

// Inicializar polling do sistema de filas
function initializeQueuePolling() {
    // Verificar atualizações a cada 3 segundos
    queuePollingInterval = setInterval(() => {
        checkForScoringUpdates();
    }, 3000);

    console.log('Queue polling initialized');
}

// Verificar atualizações de pontuação via sistema de filas
function checkForScoringUpdates() {
    fetch(window.location.origin + '/admin/quick-scoring/check-updates', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.updates && data.updates.length > 0) {
            data.updates.forEach(update => {
                handleScoringUpdate(update);
            });
        }
    })
    .catch(error => {
        console.error('Erro ao verificar atualizações:', error);
    });
}

// Desconectar polling de filas
function disconnectQueuePolling() {
    if (confirm('Tem certeza que deseja parar o monitoramento de atualizações?')) {
        if (queuePollingInterval) {
            clearInterval(queuePollingInterval);
            queuePollingInterval = null;
        }

        // Mostrar notificação de sucesso
        notify('success', 'Monitoramento parado com sucesso!');

        // Atualizar status
        $('#scoringStatus').removeClass('badge--success').addClass('badge--danger').text('Sistema Desconectado');

        // Adicionar ao log
                addScoringLog('Sistema', 'WebSockets desconectados', 0);
            } else {
                notify('error', data.message || 'Erro ao desconectar WebSockets');
            }
        })
        .catch(error => {
            console.error('Erro ao desconectar WebSockets:', error);
            notify('error', 'Erro ao desconectar WebSockets');
        });
    }
}

// Manipular atualização de pontuação recebida via WebSocket
function handleScoringUpdate(data) {
    // Mostrar notificação visual
    showWebSocketNotification(data);

    // Atualizar interface se o competidor estiver sendo exibido
    if (currentScoringCompetitors && currentScoringCompetitors.find(c => c.id == data.competitor_id)) {
        // Recarregar competidores para mostrar atualização
        loadScoringCompetitors();

        // Adicionar ao log de pontuação
        addScoringLog(data.operator || 'Sistema', `${data.competitor_name}: ${data.action}`, data.pontuacao);

        // Adicionar ao histórico
        scoringHistory.unshift({
            time: new Date(data.timestamp).toLocaleTimeString(),
            competitor: data.competitor_name,
            action: data.action,
            score: data.pontuacao
        });
    }
}

// Carregar modalidades do rodeio
function loadScoringModalidades() {
    const rodeioId = $('#scoringRodeio').val();
    const modalidadeSelect = $('#scoringModalidade');
    
    modalidadeSelect.html('<option value="">Selecione a Modalidade</option>');
    
    if (rodeioId) {
        fetch(`/admin/rodeios/${rodeioId}/modalidades`)
            .then(response => response.json())
            .then(modalidades => {
                modalidades.forEach(modalidade => {
                    modalidadeSelect.append(`<option value="${modalidade.id}">${modalidade.nome}</option>`);
                });
            });
    }
}

// Carregar competidores para pontuação
function loadScoringCompetitors() {
    const rodeioId = $('#scoringRodeio').val();
    const modalidadeId = $('#scoringModalidade').val();
    
    if (!rodeioId || !modalidadeId) {
        resetScoringView();
        return;
    }

    showScoringLoading();

    fetch(`/admin/quick-scoring/competitors?rodeio_id=${rodeioId}&modalidade_id=${modalidadeId}`)
        .then(response => response.json())
        .then(data => {
            currentScoringCompetitors = data.competitors;
            renderScoringCompetitors();
            updateScoringStats();
        })
        .catch(error => {
            console.error('Erro ao carregar competidores:', error);
            showScoringError();
        });
}

// Renderizar competidores para pontuação
function renderScoringCompetitors() {
    const container = $('#scoringCompetitors');
    
    if (currentScoringCompetitors.length === 0) {
        container.html(`
            <div class="col-12 text-center py-5">
                <i class="las la-user-slash" style="font-size: 4rem; color: #ccc;"></i>
                <p class="text-muted">@lang('Nenhum competidor confirmado nesta modalidade')</p>
            </div>
        `);
        return;
    }

    let html = '';
    currentScoringCompetitors.forEach(competitor => {
        const isScored = competitor.pivot.pontuacao > 0;
        const isEliminated = competitor.pivot.status === 'eliminado';
        const cardClass = isEliminated ? 'eliminated' : (isScored ? 'scored' : '');
        
        html += `
            <div class="col-12 col-md-6 col-xl-4 mb-4">
                <div class="card scoring-card ${cardClass}" data-competitor-id="${competitor.id}">
                    <div class="card-body text-center">
                        ${competitor.foto ? 
                            `<img src="/assets/images/competitors/${competitor.foto}" alt="${competitor.nome}" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; margin-bottom: 10px;">` :
                            `<div style="width: 80px; height: 80px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                <i class="las la-user" style="font-size: 2rem; color: #ccc;"></i>
                            </div>`
                        }
                        
                        <h6 class="card-title mb-1">${competitor.nome}</h6>
                        <small class="text-muted">${competitor.idade} anos</small>
                        
                        <div class="score-display" id="score-${competitor.id}">
                            ${competitor.pivot.pontuacao || 0} pts
                        </div>
                        
                        <div class="competitor-timer" id="timer-${competitor.id}">
                            --:--
                        </div>
                        
                        <div class="scoring-actions">
                            <div class="action-btn-group">
                                <button class="btn btn--success btn-sm" onclick="quickScore(${competitor.id}, 8, 'Armada Boa')">
                                    🟢 <strong>+8</strong><br><small>Armada Boa</small>
                                </button>
                                <button class="btn btn--danger btn-sm" onclick="quickScore(${competitor.id}, 0, 'Errou/Caiu')">
                                    🔴 <strong>0</strong><br><small>Errou/Caiu</small>
                                </button>
                                <button class="btn btn--warning btn-sm" onclick="quickScore(${competitor.id}, 4, 'Montaria Média')">
                                    🟡 <strong>+4</strong><br><small>Montaria Média</small>
                                </button>
                                <button class="btn btn--dark btn-sm" onclick="quickScore(${competitor.id}, -5, 'Desqualificado')">
                                    ⚫ <strong>-5</strong><br><small>Desqualificado</small>
                                </button>
                                <button class="btn btn--primary btn-sm" onclick="quickScore(${competitor.id}, 12, 'Tempo Recorde')">
                                    🏆 <strong>+12</strong><br><small>Tempo Recorde</small>
                                </button>
                                <button class="btn btn--info btn-sm" onclick="customScore(${competitor.id})">
                                    ⏱️ <strong>Custom</strong><br><small>Personalizado</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

// Pontuação rápida
function quickScore(competitorId, points, action) {
    const competitor = currentScoringCompetitors.find(c => c.id === competitorId);
    if (!competitor) return;

    const oldScore = competitor.pivot.pontuacao || 0;
    const newScore = points;
    
    // Salvar ação para desfazer
    lastAction = {
        competitorId: competitorId,
        oldScore: oldScore,
        newScore: newScore,
        action: action,
        timestamp: new Date()
    };

    // Atualizar no backend
    const modalidadeId = $('#scoringModalidade').val();
    
    fetch(`/admin/quick-scoring/score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            competitor_id: competitorId,
            modalidade_id: modalidadeId,
            pontuacao: newScore,
            action: action,
            tempo_cronometro: timerSeconds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar visualmente
            competitor.pivot.pontuacao = newScore;
            $(`#score-${competitorId}`).text(`${newScore} pts`);
            
            // Atualizar classe do card
            const card = $(`.scoring-card[data-competitor-id="${competitorId}"]`);
            card.removeClass('scored eliminated');
            if (newScore > 0) {
                card.addClass('scored');
            } else if (action === 'Desqualificado') {
                card.addClass('eliminated');
            }
            
            // Adicionar ao log
            addScoringLog(competitor.nome, action, newScore);
            
            // Habilitar botão desfazer
            $('#undoBtn').prop('disabled', false);
            
            // Atualizar estatísticas
            updateScoringStats();
            
            // Efeito visual
            card.addClass('pulse');
            setTimeout(() => card.removeClass('pulse'), 1000);

            // Simular recebimento de atualização WebSocket
            simulateWebSocketUpdate({
                competitor_id: competitorId,
                competitor_name: competitor.nome,
                action: action,
                pontuacao: newScore,
                timestamp: new Date().toISOString()
            });
        }
    })
    .catch(error => {
        console.error('Erro ao pontuar:', error);
        alert('Erro ao salvar pontuação');
    });
}

// Pontuação personalizada
function customScore(competitorId) {
    currentCompetitorForCustom = competitorId;
    $('#customTimeValue').val('');
    $('#customScoreValue').val('');
    $('#customObservations').val('');
    $('#customTimeModal').modal('show');
}

function applyCustomScore() {
    const timeValue = parseFloat($('#customTimeValue').val()) || 0;
    const scoreValue = parseFloat($('#customScoreValue').val()) || 0;
    const observations = $('#customObservations').val();
    
    if (scoreValue === 0) {
        alert('Digite uma pontuação válida');
        return;
    }

    const competitor = currentScoringCompetitors.find(c => c.id === currentCompetitorForCustom);
    if (!competitor) return;

    const modalidadeId = $('#scoringModalidade').val();
    
    fetch(`/admin/quick-scoring/custom-score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            competitor_id: currentCompetitorForCustom,
            modalidade_id: modalidadeId,
            pontuacao: scoreValue,
            tempo: timeValue,
            observacoes: observations
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar competitor
            competitor.pivot.pontuacao = scoreValue;
            $(`#score-${currentCompetitorForCustom}`).text(`${scoreValue} pts`);
            $(`#timer-${currentCompetitorForCustom}`).text(timeValue > 0 ? `${timeValue}s` : '--:--');
            
            // Atualizar classe do card
            const card = $(`.scoring-card[data-competitor-id="${currentCompetitorForCustom}"]`);
            card.addClass('scored');
            
            // Adicionar ao log
            addScoringLog(competitor.nome, `Personalizado (${timeValue}s)`, scoreValue);
            
            // Atualizar estatísticas
            updateScoringStats();
            
            $('#customTimeModal').modal('hide');

            // Simular WebSocket update
            simulateWebSocketUpdate({
                competitor_id: currentCompetitorForCustom,
                competitor_name: competitor.nome,
                action: 'Pontuação Personalizada',
                pontuacao: scoreValue,
                timestamp: new Date().toISOString()
            });
        }
    });
}

// Desfazer última ação
function undoLastAction() {
    if (!lastAction) return;

    const competitor = currentScoringCompetitors.find(c => c.id === lastAction.competitorId);
    if (!competitor) return;

    // Restaurar pontuação anterior
    const modalidadeId = $('#scoringModalidade').val();
    
    fetch(`/admin/quick-scoring/undo`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            competitor_id: lastAction.competitorId,
            modalidade_id: modalidadeId,
            pontuacao: lastAction.oldScore
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Restaurar visualmente
            competitor.pivot.pontuacao = lastAction.oldScore;
            $(`#score-${lastAction.competitorId}`).text(`${lastAction.oldScore} pts`);
            
            // Atualizar classe do card
            const card = $(`.scoring-card[data-competitor-id="${lastAction.competitorId}"]`);
            card.removeClass('scored eliminated');
            if (lastAction.oldScore > 0) {
                card.addClass('scored');
            }
            
            // Adicionar ao log
            addScoringLog(competitor.nome, 'DESFAZER: ' + lastAction.action, lastAction.oldScore);
            
            // Limpar última ação
            lastAction = null;
            $('#undoBtn').prop('disabled', true);
            
            // Atualizar estatísticas
            updateScoringStats();
        }
    });
}

// Adicionar ao log
function addScoringLog(competitorName, action, score) {
    const time = new Date().toLocaleTimeString();
    const logEntry = `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
            <span><strong>${time}</strong> - ${competitorName}</span>
            <span class="badge badge--info">${action}</span>
            <span class="badge badge--success">${score} pts</span>
        </div>
    `;
    
    $('#scoringLog').prepend(logEntry);
    scoringHistory.push({
        time: time,
        competitor: competitorName,
        action: action,
        score: score
    });
}

// Atualizar estatísticas
function updateScoringStats() {
    const total = currentScoringCompetitors.length;
    const scored = currentScoringCompetitors.filter(c => (c.pivot.pontuacao || 0) > 0).length;
    const scores = currentScoringCompetitors.map(c => c.pivot.pontuacao || 0).filter(s => s > 0);
    const average = scores.length > 0 ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : 0;
    const highest = scores.length > 0 ? Math.max(...scores).toFixed(1) : 0;
    
    $('#totalParticipants').text(total);
    $('#completedScores').text(scored);
    $('#averageScore').text(average);
    $('#highestScore').text(highest);
}

// Cronômetro
function startTimer() {
    if (timerInterval) return;
    
    timerInterval = setInterval(() => {
        timerSeconds++;
        updateTimerDisplay();
    }, 1000);
}

function pauseTimer() {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
}

function resetTimer() {
    pauseTimer();
    timerSeconds = 0;
    updateTimerDisplay();
}

function updateTimerDisplay() {
    const minutes = Math.floor(timerSeconds / 60);
    const seconds = timerSeconds % 60;
    $('#timer').text(`${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`);
}

// Inicializar sistema de pontuação
function initializeScoring() {
    if (!$('#scoringRodeio').val() || !$('#scoringModalidade').val()) {
        alert('Selecione um rodeio e modalidade primeiro');
        return;
    }
    
    scoringActive = true;
    $('#scoringStatus').text('Sistema Ativo - Pontuando').removeClass('badge--success').addClass('badge--warning');
    addScoringLog('Sistema', 'Pontuação inicializada', 0);
}

// Funções auxiliares
function showScoringLoading() {
    $('#scoringCompetitors').html(`
        <div class="col-12 text-center py-5">
            <i class="las la-spinner la-spin" style="font-size: 4rem; color: #007bff;"></i>
            <p class="text-muted">@lang('Carregando competidores...')</p>
        </div>
    `);
}

function showScoringError() {
    $('#scoringCompetitors').html(`
        <div class="col-12 text-center py-5">
            <i class="las la-exclamation-triangle" style="font-size: 4rem; color: #dc3545;"></i>
            <p class="text-danger">@lang('Erro ao carregar competidores')</p>
        </div>
    `);
}

function resetScoringView() {
    currentScoringCompetitors = [];
    renderScoringCompetitors();
    updateScoringStats();
}

function showScoringHistory() {
    let historyHtml = '';
    scoringHistory.forEach(entry => {
        historyHtml += `
            <tr>
                <td>${entry.time}</td>
                <td>${entry.competitor}</td>
                <td>${entry.action}</td>
                <td>${entry.score}</td>
                <td>Admin</td>
            </tr>
        `;
    });
    
    $('#historyTableBody').html(historyHtml || '<tr><td colspan="5" class="text-center text-muted">Nenhuma ação registrada</td></tr>');
    $('#scoringHistoryModal').modal('show');
}

function clearScoringLog() {
    $('#scoringLog').html('<div class="text-muted">Log limpo...</div>');
    scoringHistory = [];
}

function saveCurrentState() {
    // Implementar salvamento de estado
    addScoringLog('Sistema', 'Estado salvo', 0);
    alert('Estado atual salvo com sucesso!');
}

// Simular recebimento de atualização WebSocket
function simulateWebSocketUpdate(data) {
    // Em produção, isso seria recebido via WebSocket/Pusher
    console.log('WebSocket Update simulado:', data);
    
    // Mostrar notificação visual
    showWebSocketNotification(data);
    
    // Atualizar interface se necessário
    if (data.competitor_id && currentScoringCompetitors.find(c => c.id === data.competitor_id)) {
        // Atualização já foi aplicada localmente, apenas confirmar
        console.log('Atualização confirmada via WebSocket');
    }
}

// Mostrar notificação de WebSocket
function showWebSocketNotification(data) {
    const notification = `
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto text-success">🟢 Atualização em Tempo Real</strong>
                    <small class="text-muted">agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <strong>${data.competitor_name}</strong><br>
                    ${data.action}: <span class="badge bg-primary">${data.pontuacao} pts</span>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(notification);
    
    // Remover após 5 segundos
    setTimeout(() => {
        $('.toast').fadeOut(() => {
            $('.toast-container').remove();
        });
    }, 5000);
}
</script>
@push('js-lib')
<script src="{{ asset('js/bootstrap.js') }}" defer></script>
@endpush
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.dynamic_selection.index') }}" />
@endpush
