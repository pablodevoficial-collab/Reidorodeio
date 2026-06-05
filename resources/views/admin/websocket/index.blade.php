@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Status das Filas -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">@lang('Monitoramento de Filas em Tempo Real')</h4>
                <div class="btn-group">
                    <button class="btn btn-sm btn--success" onclick="startQueueWorker()" id="startQueueBtn">
                        <i class="las la-play"></i> @lang('Iniciar Worker')
                    </button>
                    <button class="btn btn-sm btn--warning" onclick="pauseQueueWorker()" id="pauseQueueBtn">
                        <i class="las la-pause"></i> @lang('Pausar Worker')
                    </button>
                    <button class="btn btn-sm btn--info" onclick="checkQueueStatus()" id="statusCheckBtn">
                        <i class="las la-sync"></i> @lang('Verificar Status')
                    </button>
                    <button class="btn btn-sm btn--danger" onclick="clearQueueJobs()">
                        <i class="las la-trash"></i> @lang('Limpar Filas')
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--success">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="pendingJobs">0</h3>
                                <p class="text-white">@lang('Jobs Pendentes')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--primary">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="processedJobs">0</h3>
                                <p class="text-white">@lang('Jobs Processados')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--info">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="failedJobs">0</h3>
                                <p class="text-white">@lang('Jobs Falhados')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--warning">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="activeWorkers">0</h3>
                                <p class="text-white">@lang('Workers Ativos')</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalhes das Filas e Terminal -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Status das Filas')</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Job')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Pendentes')</th>
                                <th>@lang('Processados')</th>
                                <th>@lang('Falhas')</th>
                            </tr>
                        </thead>
                        <tbody id="queuesTable">
                            <tr>
                                <td colspan="5" class="text-center text-muted">@lang('Carregando...')</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">@lang('Terminal - Últimas 30 Chamadas')</h5>
                <button class="btn btn-sm btn--secondary" onclick="clearTerminal()">
                    <i class="las la-trash"></i> @lang('Limpar')
                </button>
            </div>
            <div class="card-body">
                <div id="queueTerminal" style="height: 300px; overflow-y: auto;" class="bg-dark text-light p-3 rounded font-monospace">
                    <div class="text-success">[INFO] Terminal de filas iniciado...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Controle das Filas -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Teste de Jobs')</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>@lang('Tipo de Job')</label>
                        <select id="testJobType" class="form-control">
                            <option value="scoring">@lang('Pontuação')</option>
                            <option value="transmission">@lang('Transmissão')</option>
                            <option value="competitor">@lang('Competidor')</option>
                            <option value="ranking">@lang('Ranking')</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>@lang('Ação')</label>
                        <select id="testJobAction" class="form-control">
                            <option value="add">@lang('Adicionar')</option>
                            <option value="update">@lang('Atualizar')</option>
                            <option value="delete">@lang('Remover')</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label>@lang('Dados do Job (JSON)')</label>
                    <textarea id="testJobData" class="form-control" rows="4" placeholder='{"competitor_id": 1, "pontuacao": 85.5}'></textarea>
                </div>
                <div class="mt-3">
                    <button class="btn btn--primary w-100" onclick="dispatchTestJob()">
                        <i class="las la-paper-plane"></i> @lang('Disparar Job')
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Configurações do Sistema')</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <strong>@lang('Driver de Filas'):</strong>
                            <span class="badge badge--info" id="queueDriver">-</span>
                        </div>
                        <div class="mb-3">
                            <strong>@lang('Driver de Cache'):</strong>
                            <span class="badge badge--info" id="cacheDriver">-</span>
                        </div>
                        <div class="mb-3">
                            <strong>@lang('Tempo do Servidor'):</strong>
                            <span class="badge badge--secondary" id="serverTime">-</span>
                        </div>
                        <div class="mb-3">
                            <strong>@lang('Status do Worker'):</strong>
                            <span class="badge badge--danger" id="workerStatus">@lang('Parado')</span>
                        </div>
                        <div class="mb-3">
                            <strong>@lang('Atualização Automática'):</strong>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                                <label class="form-check-label" for="autoRefresh">
                                    @lang('Ativada (5s)')
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn--success w-100" onclick="checkQueueStatus()">
                        <i class="las la-sync"></i> @lang('Atualizar Status')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes das Filas -->
<div class="modal fade" id="queueDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Detalhes das Filas')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>@lang('Jobs Recentes')</h6>
                        <div id="recentJobs" style="max-height: 200px; overflow-y: auto;">
                            <div class="text-muted">@lang('Carregando...')</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>@lang('Estatísticas Detalhadas')</h6>
                        <div id="detailedStats">
                            <div class="text-muted">@lang('Carregando...')</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Fechar')</button>
                <button type="button" class="btn btn--primary" onclick="refreshQueueDetails()">@lang('Atualizar')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
.queue-status {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
}

.queue-status.active { background-color: #28a745; }
.queue-status.idle { background-color: #ffc107; }
.queue-status.error { background-color: #dc3545; }

.terminal-console {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.4;
}

.log-info { color: #17a2b8; }
.log-success { color: #28a745; }
.log-warning { color: #ffc107; }
.log-error { color: #dc3545; }

.worker-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    padding: 10px 15px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
}

.worker-active { background-color: #28a745; }
.worker-paused { background-color: #ffc107; }
.worker-stopped { background-color: #dc3545; }
</style>
@endpush

@push('script')
<script>
let workerStatus = 'stopped';
let autoRefreshInterval = null;
let recentJobs = [];

// Inicializar página
$(document).ready(function() {
    // Verificar se o usuário está autenticado antes de inicializar
    checkAuthentication().then(() => {
        checkQueueStatus();
        startAutoRefresh();
        updateWorkerIndicator();
    }).catch(() => {
        // Se não estiver autenticado, redirecionar para login
        addTerminalLog('error', 'Usuário não autenticado - redirecionando...');
        setTimeout(() => {
            window.location.href = '/admin/login';
        }, 2000);
    });
});

// Verificar se o usuário está autenticado
function checkAuthentication() {
    return fetch(window.location.origin + '/admin/queues/status', {
        credentials: 'same-origin'
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
                if (data.success) {
                    return Promise.resolve();
                } else {
                    return Promise.reject();
                }
            });
        } else {
            // Se retorna HTML, não está autenticado
            return Promise.reject();
        }
    });
}

// Verificar status das filas
function checkQueueStatus() {
    $('#statusCheckBtn').prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Verificando...');

    fetch(window.location.origin + '/admin/queues/status', {
        credentials: 'same-origin'
    })
        .then(response => {
            // Verificar se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Se não é JSON, provavelmente é HTML (página de login)
                if (response.status === 200 && !contentType.includes('application/json')) {
                    throw new Error('Sessão expirada - redirecionando para login');
                }
                throw new Error('Resposta inválida do servidor');
            }
        })
        .then(data => {
            if (data.success) {
                updateQueueStats(data.stats);
                updateQueuesTable(data.queues);
                updateSystemInfo(data);
                addTerminalLog('success', 'Status das filas atualizado');
            } else {
                addTerminalLog('error', 'Erro ao verificar status: ' + data.message);
            }
        })
        .catch(error => {
            if (error.message.includes('Sessão expirada')) {
                addTerminalLog('error', 'Sessão expirada - redirecionando...');
                // Redirecionar para login após um pequeno delay
                setTimeout(() => {
                    window.location.href = '/admin/login';
                }, 2000);
            } else {
                addTerminalLog('error', 'Erro de conexão: ' + error.message);
            }
        })
        .finally(() => {
            $('#statusCheckBtn').prop('disabled', false).html('<i class="las la-sync"></i> Verificar Status');
        });
}

// Atualizar estatísticas das filas
function updateQueueStats(stats) {
    $('#pendingJobs').text(stats.pending || 0);
    $('#processedJobs').text(stats.processed || 0);
    $('#failedJobs').text(stats.failed || 0);
    $('#activeWorkers').text(stats.workers || 0);
}

// Atualizar tabela de filas
function updateQueuesTable(queues) {
    let html = '';
    Object.keys(queues).forEach(queueName => {
        const queue = queues[queueName];
        const statusClass = queue.active ? 'active' : 'idle';

        html += `
            <tr>
                <td>
                    <span class="queue-status ${statusClass}"></span>
                    ${queueName}
                </td>
                <td>
                    <span class="badge badge--${queue.active ? 'success' : 'warning'}">${queue.active ? 'Ativa' : 'Ociosa'}</span>
                </td>
                <td>${queue.pending}</td>
                <td>${queue.processed}</td>
                <td>${queue.failed}</td>
            </tr>
        `;
    });

    $('#queuesTable').html(html);
}

// Atualizar informações do sistema
function updateSystemInfo(data) {
    $('#queueDriver').text(data.queue_driver || '-');
    $('#cacheDriver').text(data.cache_driver || '-');
    $('#serverTime').text(new Date(data.server_time).toLocaleString());
    $('#workerStatus').removeClass('badge--success badge--warning badge--danger')
                     .addClass(`badge--${data.worker_status === 'active' ? 'success' : data.worker_status === 'paused' ? 'warning' : 'danger'}`)
                     .text(data.worker_status === 'active' ? 'Ativo' : data.worker_status === 'paused' ? 'Pausado' : 'Parado');

    workerStatus = data.worker_status;
    updateWorkerIndicator();
}

// Iniciar worker de filas
function startQueueWorker() {
    $('#startQueueBtn').prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Iniciando...');

    fetch(window.location.origin + '/admin/queues/start-worker', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            if (response.status === 200 && !contentType.includes('application/json')) {
                throw new Error('Sessão expirada - redirecionando para login');
            }
            throw new Error('Resposta inválida do servidor');
        }
    })
    .then(data => {
        if (data.success) {
            addTerminalLog('success', 'Worker de filas iniciado com sucesso');
            workerStatus = 'active';
            updateWorkerIndicator();
            checkQueueStatus();
        } else {
            // Se retornou um comando para executar manualmente (caso localhost)
            if (data.command) {
                addTerminalLog('info', 'Para iniciar o worker em localhost, execute no terminal:');
                addTerminalLog('info', data.command);
                addTerminalLog('warning', 'Worker não pode ser iniciado automaticamente em localhost');
            } else {
                addTerminalLog('error', 'Erro ao iniciar worker: ' + data.message);
            }
        }
    })
    .catch(error => {
        if (error.message.includes('Sessão expirada')) {
            addTerminalLog('error', 'Sessão expirada - redirecionando...');
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 2000);
        } else {
            addTerminalLog('error', 'Erro de conexão: ' + error.message);
        }
    })
    .finally(() => {
        $('#startQueueBtn').prop('disabled', false).html('<i class="las la-play"></i> Iniciar Worker');
    });
}

// Pausar worker de filas
function pauseQueueWorker() {
    $('#pauseQueueBtn').prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Pausando...');

    fetch(window.location.origin + '/admin/queues/pause-worker', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            if (response.status === 200 && !contentType.includes('application/json')) {
                throw new Error('Sessão expirada - redirecionando para login');
            }
            throw new Error('Resposta inválida do servidor');
        }
    })
    .then(data => {
        if (data.success) {
            addTerminalLog('warning', 'Worker de filas pausado');
            workerStatus = 'paused';
            updateWorkerIndicator();
            checkQueueStatus();
        } else {
            addTerminalLog('error', 'Erro ao pausar worker: ' + data.message);
        }
    })
    .catch(error => {
        if (error.message.includes('Sessão expirada')) {
            addTerminalLog('error', 'Sessão expirada - redirecionando...');
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 2000);
        } else {
            addTerminalLog('error', 'Erro de conexão: ' + error.message);
        }
    })
    .finally(() => {
        $('#pauseQueueBtn').prop('disabled', false).html('<i class="las la-pause"></i> Pausar Worker');
    });
}

// Limpar jobs das filas
function clearQueueJobs() {
    if (!confirm('Tem certeza que deseja limpar todos os jobs das filas? Isso removerá jobs pendentes e falhados.')) {
        return;
    }

    fetch(window.location.origin + '/admin/queues/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            if (response.status === 200 && !contentType.includes('application/json')) {
                throw new Error('Sessão expirada - redirecionando para login');
            }
            throw new Error('Resposta inválida do servidor');
        }
    })
    .then(data => {
        if (data.success) {
            addTerminalLog('success', 'Filas limpas com sucesso');
            checkQueueStatus();
        } else {
            addTerminalLog('error', 'Erro ao limpar filas: ' + data.message);
        }
    })
    .catch(error => {
        if (error.message.includes('Sessão expirada')) {
            addTerminalLog('error', 'Sessão expirada - redirecionando...');
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 2000);
        } else {
            addTerminalLog('error', 'Erro de conexão: ' + error.message);
        }
    });
}

// Disparar job de teste
function dispatchTestJob() {
    const jobType = $('#testJobType').val();
    const action = $('#testJobAction').val();
    const jobData = $('#testJobData').val();

    let data = {};
    if (jobData.trim()) {
        try {
            data = JSON.parse(jobData);
        } catch (e) {
            alert('JSON inválido nos dados do job');
            return;
        }
    }

    // Adicionar dados padrão baseados no tipo
    switch (jobType) {
        case 'scoring':
            data = {
                competitor_id: data.competitor_id || 1,
                modalidade_id: data.modalidade_id || 1,
                rodeio_id: data.rodeio_id || 1,
                pontuacao: data.pontuacao || 85.5,
                tempo: data.tempo || null,
                action: action,
                competitor_name: data.competitor_name || 'Test Competitor',
                operator_name: data.operator_name || 'Admin'
            };
            break;
        case 'transmission':
            data = {
                rodeio_id: data.rodeio_id || 1,
                status: data.status || 'live',
                modalidade_atual: data.modalidade_atual || 1,
                stream_url: data.stream_url || 'https://stream.example.com/live',
                viewers_count: data.viewers_count || 100
            };
            break;
        case 'competitor':
            data = {
                competitor_id: data.competitor_id || 1,
                competitor_name: data.competitor_name || 'Test Competitor',
                modalidade_id: data.modalidade_id || 1,
                rodeio_id: data.rodeio_id || 1,
                old_status: data.old_status || 'inactive',
                new_status: data.new_status || 'active'
            };
            break;
        case 'ranking':
            data = {
                modalidade_id: data.modalidade_id || 1,
                rodeio_id: data.rodeio_id || 1,
                ranking: data.ranking || [
                    {competitor_id: 1, name: 'João Silva', score: 95.5},
                    {competitor_id: 2, name: 'Maria Santos', score: 92.0}
                ],
                statistics: data.statistics || {
                    total_competitors: 2,
                    average_score: 93.75,
                    highest_score: 95.5
                }
            };
            break;
    }

    fetch(window.location.origin + '/admin/queues/test-job', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            job_type: jobType,
            data: data
        }),
        credentials: 'same-origin'
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            if (response.status === 200 && !contentType.includes('application/json')) {
                throw new Error('Sessão expirada - redirecionando para login');
            }
            throw new Error('Resposta inválida do servidor');
        }
    })
    .then(data => {
        if (data.success) {
            addTerminalLog('success', `Job ${jobType} disparado com sucesso`);
            checkQueueStatus();
        } else {
            addTerminalLog('error', 'Erro ao disparar job: ' + data.message);
        }
    })
    .catch(error => {
        if (error.message.includes('Sessão expirada')) {
            addTerminalLog('error', 'Sessão expirada - redirecionando...');
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 2000);
        } else {
            addTerminalLog('error', 'Erro de conexão: ' + error.message);
        }
    });
}

// Atualizar indicador do worker
function updateWorkerIndicator() {
    const indicator = $('#workerIndicator');
    if (indicator.length === 0) {
        $('body').append('<div id="workerIndicator" class="worker-indicator"></div>');
    }

    const classes = 'worker-active worker-paused worker-stopped';
    $('#workerIndicator').removeClass(classes);

    switch (workerStatus) {
        case 'active':
            $('#workerIndicator').addClass('worker-active').text('🟢 Worker Ativo');
            break;
        case 'paused':
            $('#workerIndicator').addClass('worker-paused').text('🟡 Worker Pausado');
            break;
        default:
            $('#workerIndicator').addClass('worker-stopped').text('🔴 Worker Parado');
    }
}

// Adicionar log no terminal
function addTerminalLog(type, message) {
    const timestamp = new Date().toLocaleTimeString();
    const logClass = `log-${type}`;
    const icon = {
        'info': 'ℹ️',
        'success': '✅',
        'warning': '⚠️',
        'error': '❌'
    }[type] || 'ℹ️';

    const logEntry = `<div class="${logClass}">[${timestamp}] ${icon} ${message}</div>`;
    $('#queueTerminal').append(logEntry);

    // Scroll para o final
    const terminal = document.getElementById('queueTerminal');
    terminal.scrollTop = terminal.scrollHeight;

    // Limitar número de logs (últimas 30 chamadas)
    const logs = $('#queueTerminal > div');
    if (logs.length > 30) {
        logs.first().remove();
    }

    // Adicionar à lista de jobs recentes
    recentJobs.unshift({
        timestamp: timestamp,
        type: type,
        message: message
    });

    if (recentJobs.length > 30) {
        recentJobs = recentJobs.slice(0, 30);
    }
}

// Limpar terminal
function clearTerminal() {
    $('#queueTerminal').html('<div class="text-success">[INFO] Terminal limpo...</div>');
    recentJobs = [];
}

// Auto refresh
function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }

    if ($('#autoRefresh').is(':checked')) {
        autoRefreshInterval = setInterval(() => {
            checkQueueStatus();
        }, 5000);
    }
}

// Toggle auto refresh
$('#autoRefresh').change(function() {
    if ($(this).is(':checked')) {
        startAutoRefresh();
        addTerminalLog('info', 'Atualização automática ativada');
    } else {
        clearInterval(autoRefreshInterval);
        addTerminalLog('info', 'Atualização automática desativada');
    }
});

// Atualizar dados do job de teste baseado no tipo selecionado
$('#testJobType').change(function() {
    const jobType = $(this).val();
    const jobDataTextarea = $('#testJobData');

    let sampleData = '';
    switch (jobType) {
        case 'scoring':
            sampleData = `{
    "competitor_id": 1,
    "modalidade_id": 1,
    "rodeio_id": 1,
    "pontuacao": 85.5,
    "tempo": "12.45",
    "action": "add",
    "competitor_name": "João Silva",
    "operator_name": "Admin"
}`;
            break;
        case 'transmission':
            sampleData = `{
    "rodeio_id": 1,
    "status": "live",
    "modalidade_atual": 1,
    "stream_url": "https://stream.example.com/live",
    "viewers_count": 1250
}`;
            break;
        case 'competitor':
            sampleData = `{
    "competitor_id": 1,
    "competitor_name": "João Silva",
    "modalidade_id": 1,
    "rodeio_id": 1,
    "old_status": "inactive",
    "new_status": "active"
}`;
            break;
        case 'ranking':
            sampleData = `{
    "modalidade_id": 1,
    "rodeio_id": 1,
    "ranking": [
        {"competitor_id": 1, "name": "João Silva", "score": 95.5},
        {"competitor_id": 2, "name": "Maria Santos", "score": 92.0}
    ],
    "statistics": {
        "total_competitors": 2,
        "average_score": 93.75,
        "highest_score": 95.5
    }
}`;
            break;
    }

    jobDataTextarea.val(sampleData);
});
</script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.dashboard') }}" />
@endpush
