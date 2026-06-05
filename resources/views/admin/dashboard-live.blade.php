@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">@lang('Dashboard Sistema Live Rodeio')</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
                        <li class="breadcrumb-item active">@lang('Dashboard Live Rodeio')</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">@lang('Transmissões Ativas')</p>
                            <h4 class="mb-0" id="activeTransmissions">3</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-broadcast font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">@lang('Espectadores Online')</p>
                            <h4 class="mb-0" id="onlineViewers">1,247</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-user font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">@lang('Eventos Hoje')</p>
                            <h4 class="mb-0" id="todayEvents">8</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-calendar font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card mini-stats-wid">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-muted fw-medium">@lang('Atualizações Ativas')</p>
                            <h4 class="mb-0" id="activeUpdates">156</h4>
                        </div>
                        <div class="flex-shrink-0 align-self-center">
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                <span class="avatar-title">
                                    <i class="bx bx-money font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Live Stream Management -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">@lang('Controle de Transmissão')</h4>
                    <div class="btn-group">
                        <button class="btn btn-success btn-sm" id="startStreamBtn" onclick="toggleStream(true)">
                            <i class="bx bx-play"></i> @lang('Iniciar Stream')
                        </button>
                        <button class="btn btn-danger btn-sm" id="stopStreamBtn" onclick="toggleStream(false)" style="display: none;">
                            <i class="bx bx-stop"></i> @lang('Parar Stream')
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="stream-settings">
                                <h6>@lang('Configurações da Transmissão')</h6>
                                <div class="mb-3">
                                    <label class="form-label">@lang('Título do Evento')</label>
                                    <input type="text" class="form-control" id="streamTitle" 
                                           value="Rodeio Championship 2025" placeholder="@lang('Digite o título do evento')">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">@lang('Modalidade Atual')</label>
                                    <select class="form-select" id="currentModalidade">
                                        <option value="montaria_touros">@lang('Montaria em Touros')</option>
                                        <option value="tres_tambores">@lang('Três Tambores')</option>
                                        <option value="laco_dupla">@lang('Laço em Dupla')</option>
                                        <option value="laco_individual">@lang('Laço Individual')</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">@lang('Qualidade do Stream')</label>
                                    <select class="form-select" id="streamQuality">
                                        <option value="720p">HD 720p</option>
                                        <option value="1080p" selected>Full HD 1080p</option>
                                        <option value="4k">4K Ultra HD</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stream-status">
                                <h6>@lang('Status da Transmissão')</h6>
                                <div class="status-indicator mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-dot bg-secondary me-2" id="streamStatusDot"></div>
                                        <span id="streamStatusText">@lang('Desconectado')</span>
                                    </div>
                                </div>
                                <div class="stream-metrics">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="metric-card p-2 bg-light rounded text-center">
                                                <div class="h6 mb-1" id="streamViewers">0</div>
                                                <small>@lang('Espectadores')</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="metric-card p-2 bg-light rounded text-center">
                                                <div class="h6 mb-1" id="streamDuration">00:00:00</div>
                                                <small>@lang('Duração')</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="metric-card p-2 bg-light rounded text-center">
                                                <div class="h6 mb-1" id="streamBitrate">0 Mbps</div>
                                                <small>@lang('Bitrate')</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="metric-card p-2 bg-light rounded text-center">
                                                <div class="h6 mb-1" id="streamFps">0 fps</div>
                                                <small>@lang('FPS')</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('Ações Rápidas')</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.live_transmission.index') }}" class="btn btn-primary">
                            <i class="bx bx-broadcast"></i> @lang('Gerenciar Transmissões')
                        </a>
                        <a href="{{ route('admin.quick_scoring.index') }}" class="btn btn-success">
                            <i class="bx bx-timer"></i> @lang('Pontuação Rápida')
                        </a>
                        <a href="{{ route('admin.dynamic_selection.index') }}" class="btn btn-info">
                            <i class="bx bx-shuffle"></i> @lang('Seleção Dinâmica')
                        </a>
                        <a href="{{ route('admin.fantasy_leagues.index') }}" class="btn btn-blue">
                            <i class="bx bx-trophy"></i> @lang('Fantasy League')
                        </a>
                        <button class="btn btn-danger" onclick="disconnectAllWebSockets()">
                            <i class="bx bx-power-off"></i> @lang('Desconectar WebSockets')
                        </button>
                        <button class="btn btn-secondary" onclick="openWebSocketConsole()">
                            <i class="bx bx-code-alt"></i> @lang('Console WebSocket')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Real-time Events -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('Eventos em Tempo Real')</h4>
                </div>
                <div class="card-body">
                    <div id="realTimeEvents" style="max-height: 400px; overflow-y: auto;">
                        <!-- Events will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Rankings -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('Rankings Atuais')</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('Competidor')</th>
                                    <th>@lang('Pontuação')</th>
                                    <th>@lang('Status')</th>
                                </tr>
                            </thead>
                            <tbody id="currentRankings">
                                <!-- Rankings will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Analytics Charts -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('Análise de Audiência (Últimas 24h)')</h4>
                </div>
                <div class="card-body">
                    <canvas id="audienceChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">@lang('Status do Sistema')</h4>
                </div>
                <div class="card-body">
                    <div class="system-status">
                        <div class="status-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>@lang('WebSocket Server')</span>
                                <span class="badge bg-success" id="websocketStatus">@lang('Online')</span>
                            </div>
                        </div>
                        <div class="status-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>@lang('Streaming Server')</span>
                                <span class="badge bg-success" id="streamingStatus">@lang('Online')</span>
                            </div>
                        </div>
                        <div class="status-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>@lang('Banco de Dados')</span>
                                <span class="badge bg-success" id="databaseStatus">@lang('Online')</span>
                            </div>
                        </div>
                        <div class="status-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>@lang('Fantasy League')</span>
                                <span class="badge bg-success" id="fantasyStatus">@lang('Online')</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WebSocket Console Modal -->
<div class="modal fade" id="websocketConsoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Console WebSocket')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="console-output" id="consoleOutput" style="height: 300px; overflow-y: auto; background: #000; color: #fff; padding: 15px; font-family: monospace;">
                    <div>@lang('Console WebSocket iniciado...')</div>
                </div>
                <div class="console-input mt-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="consoleInput" placeholder="@lang('Digite um comando...')">
                        <button class="btn btn-primary" onclick="sendConsoleCommand()">
                            @lang('Enviar')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.status-dot.bg-success {
    background-color: #28a745 !important;
    animation: pulse 2s infinite;
}

.status-dot.bg-danger {
    background-color: #dc3545 !important;
}

.status-dot.bg-warning {
    background-color: #ffc107 !important;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.metric-card {
    transition: transform 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
}

.real-time-event {
    border-left: 4px solid #007bff;
    padding: 10px;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    animation: slideIn 0.3s ease-out;
}

.real-time-event.success { border-left-color: #28a745; }
.real-time-event.warning { border-left-color: #ffc107; }
.real-time-event.danger { border-left-color: #dc3545; }

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

.console-output {
    border: 1px solid #ccc;
    border-radius: 5px;
}

.mini-stats-wid {
    transition: transform 0.2s;
}

.mini-stats-wid:hover {
    transform: translateY(-2px);
}

.btn-blue {
    background-color: #6f42c1;
    border-color: #6f42c1;
    color: #fff;
}

.btn-blue:hover {
    background-color: #5a32a3;
    border-color: #5a32a3;
    color: #fff;
}
</style>
@endpush

@push('script')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let streamActive = false;
let streamStartTime = null;
let realTimeChart = null;

// Inicializar dashboard
$(document).ready(function() {
    initializeCharts();
    loadRealTimeData();
    updateSystemStatus();
    
    // Auto-refresh a cada 5 segundos
    setInterval(updateDashboard, 5000);
    
    // Event listeners
    $('#consoleInput').on('keypress', function(e) {
        if (e.which === 13) {
            sendConsoleCommand();
        }
    });
});

// Inicializar gráficos
function initializeCharts() {
    const ctx = document.getElementById('audienceChart').getContext('2d');
    
    realTimeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: generateTimeLabels(),
            datasets: [
                {
                    label: '@lang('Espectadores')',
                    data: generateRandomData(24),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                },
                {
                    label: '@lang('Atualizações')',
                    data: generateRandomData(24),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
}

// Gerar labels de tempo
function generateTimeLabels() {
    const labels = [];
    for (let i = 23; i >= 0; i--) {
        const time = new Date();
        time.setHours(time.getHours() - i);
        labels.push(time.getHours() + ':00');
    }
    return labels;
}

// Gerar dados aleatórios
function generateRandomData(count) {
    const data = [];
    for (let i = 0; i < count; i++) {
        data.push(Math.floor(Math.random() * 1000) + 100);
    }
    return data;
}

// Toggle stream
function toggleStream(start) {
    if (start) {
        streamActive = true;
        streamStartTime = new Date();
        
        $('#startStreamBtn').hide();
        $('#stopStreamBtn').show();
        $('#streamStatusDot').removeClass('bg-secondary').addClass('bg-success');
        $('#streamStatusText').text('@lang('Transmitindo')');
        
        // Simular métricas de stream
        updateStreamMetrics();
        addRealTimeEvent('@lang('Transmissão iniciada com sucesso')', 'success');
        
    } else {
        streamActive = false;
        streamStartTime = null;
        
        $('#startStreamBtn').show();
        $('#stopStreamBtn').hide();
        $('#streamStatusDot').removeClass('bg-success').addClass('bg-secondary');
        $('#streamStatusText').text('@lang('Desconectado')');
        
        // Reset metrics
        $('#streamViewers').text('0');
        $('#streamDuration').text('00:00:00');
        $('#streamBitrate').text('0 Mbps');
        $('#streamFps').text('0 fps');
        
        addRealTimeEvent('@lang('Transmissão encerrada')', 'warning');
    }
}

// Atualizar métricas do stream
function updateStreamMetrics() {
    if (!streamActive) return;
    
    // Simular dados em tempo real
    const viewers = Math.floor(Math.random() * 500) + 500;
    const bitrate = (Math.random() * 3 + 2).toFixed(1);
    const fps = Math.floor(Math.random() * 10) + 25;
    
    $('#streamViewers').text(viewers);
    $('#streamBitrate').text(bitrate + ' Mbps');
    $('#streamFps').text(fps + ' fps');
    
    // Calcular duração
    if (streamStartTime) {
        const duration = new Date() - streamStartTime;
        const hours = Math.floor(duration / 3600000);
        const minutes = Math.floor((duration % 3600000) / 60000);
        const seconds = Math.floor((duration % 60000) / 1000);
        
        $('#streamDuration').text(
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0')
        );
    }
    
    // Atualizar dashboard stats
    $('#onlineViewers').text(viewers.toLocaleString());
}

// Carregar dados em tempo real
function loadRealTimeData() {
    // Simular eventos
    const events = [
        { text: '@lang('João Silva pontuou 8.2 na montaria em touros')', type: 'success', time: new Date() },
        { text: '@lang('Nova atualização registrada')', type: 'info', time: new Date() },
        { text: '@lang('Pedro Santos foi eliminado da competição')', type: 'danger', time: new Date() }
    ];
    
    events.forEach((event, index) => {
        setTimeout(() => {
            addRealTimeEvent(event.text, event.type);
        }, index * 2000);
    });
    
    // Carregar rankings
    loadCurrentRankings();
}

// Adicionar evento em tempo real
function addRealTimeEvent(text, type = 'info') {
    const eventHtml = `
        <div class="real-time-event ${type}">
            <div class="d-flex justify-content-between">
                <span>${text}</span>
                <small class="text-muted">${new Date().toLocaleTimeString()}</small>
            </div>
        </div>
    `;
    
    $('#realTimeEvents').prepend(eventHtml);
    
    // Limitar a 10 eventos
    const events = $('#realTimeEvents .real-time-event');
    if (events.length > 10) {
        events.last().remove();
    }
}

// Carregar rankings atuais
function loadCurrentRankings() {
    const rankings = [
        { position: 1, name: 'João Silva', score: 8.2, status: 'active' },
        { position: 2, name: 'Pedro Santos', score: 7.8, status: 'active' },
        { position: 3, name: 'Carlos Oliveira', score: 7.5, status: 'active' },
        { position: 4, name: 'Rafael Costa', score: 6.9, status: 'active' },
        { position: 5, name: 'Lucas Pereira', score: 0.0, status: 'eliminated' }
    ];
    
    let html = '';
    rankings.forEach(ranking => {
        const statusBadge = ranking.status === 'eliminated' ? 
            '<span class="badge bg-danger">Eliminado</span>' :
            '<span class="badge bg-success">Ativo</span>';
        
        html += `
            <tr>
                <td><strong>${ranking.position}</strong></td>
                <td>${ranking.name}</td>
                <td><strong class="text-primary">${ranking.score}</strong></td>
                <td>${statusBadge}</td>
            </tr>
        `;
    });
    
    $('#currentRankings').html(html);
}

// Atualizar status do sistema
function updateSystemStatus() {
    // Simular verificação de status
    const services = ['websocket', 'streaming', 'database', 'fantasy'];
    
    services.forEach(service => {
        const isOnline = Math.random() > 0.1; // 90% chance de estar online
        const statusElement = $(`#${service}Status`);
        
        if (isOnline) {
            statusElement.removeClass('bg-danger').addClass('bg-success').text('@lang('Online')');
        } else {
            statusElement.removeClass('bg-success').addClass('bg-danger').text('@lang('Offline')');
        }
    });
}

// Atualizar dashboard
function updateDashboard() {
    if (streamActive) {
        updateStreamMetrics();
    }
    
    // Simular atualizações de dados
    $('#activeTransmissions').text(Math.floor(Math.random() * 5) + 1);
    $('#todayEvents').text(Math.floor(Math.random() * 10) + 5);
    $('#activeUpdates').text(Math.floor(Math.random() * 200) + 100);
    
    // Adicionar evento aleatório ocasionalmente
    if (Math.random() > 0.8) {
        const randomEvents = [
            '@lang('Novo competidor registrado no sistema')',
            '@lang('Atualização de sistema detectada')',
            '@lang('Time fantasy criado')',
            '@lang('Pontuação atualizada automaticamente')'
        ];
        
        const randomEvent = randomEvents[Math.floor(Math.random() * randomEvents.length)];
        addRealTimeEvent(randomEvent, 'info');
    }
    
    // Atualizar gráfico
    updateChart();
}

// Atualizar gráfico
function updateChart() {
    if (!realTimeChart) return;
    
    // Adicionar novo ponto de dados
    const currentTime = new Date().getHours() + ':' + String(new Date().getMinutes()).padStart(2, '0');
    const newViewersData = Math.floor(Math.random() * 1000) + 100;
    const newUpdatesData = Math.floor(Math.random() * 50) + 10;
    
    realTimeChart.data.labels.push(currentTime);
    realTimeChart.data.datasets[0].data.push(newViewersData);
    realTimeChart.data.datasets[1].data.push(newUpdatesData);
    
    // Manter apenas últimos 24 pontos
    if (realTimeChart.data.labels.length > 24) {
        realTimeChart.data.labels.shift();
        realTimeChart.data.datasets[0].data.shift();
        realTimeChart.data.datasets[1].data.shift();
    }
    
    realTimeChart.update('none');
}

// Abrir console WebSocket
function openWebSocketConsole() {
    $('#websocketConsoleModal').modal('show');
    addConsoleLog('@lang('Console WebSocket conectado')');
}

// Enviar comando do console
function sendConsoleCommand() {
    const command = $('#consoleInput').val().trim();
    if (!command) return;
    
    addConsoleLog('> ' + command);
    
    // Simular resposta do comando
    setTimeout(() => {
        let response = '';
        switch(command.toLowerCase()) {
            case 'status':
                response = '@lang('Sistema: Online | Conectados: 1,247 | Stream: Ativo')';
                break;
            case 'viewers':
                response = '@lang('Espectadores atuais: ') ' + $('#streamViewers').text();
                break;
            case 'help':
                response = '@lang('Comandos disponíveis: status, viewers, broadcast, clear, help')';
                break;
            case 'clear':
                $('#consoleOutput').html('<div>@lang('Console limpo...')</div>');
                $('#consoleInput').val('');
                return;
            case 'broadcast':
                response = '@lang('Mensagem enviada para todos os espectadores')';
                break;
            default:
                response = '@lang('Comando não reconhecido. Digite') "help" @lang('para ver comandos disponíveis.')';
        }
        
        addConsoleLog(response);
    }, 500);
    
    $('#consoleInput').val('');
}

// Adicionar log ao console
function addConsoleLog(message) {
    const timestamp = new Date().toLocaleTimeString();
    const logHtml = `<div>[${timestamp}] ${message}</div>`;
    
    $('#consoleOutput').append(logHtml);
    
    // Scroll para o final
    const consoleOutput = document.getElementById('consoleOutput');
    consoleOutput.scrollTop = consoleOutput.scrollHeight;
}
</script>
@endpush
