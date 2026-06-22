@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">
    {{-- Header Info --}}
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="las la-info-circle fs-3 me-3"></i>
            <div>
                <strong>Comandos do Sistema</strong><br>
                Execute comandos de manutenção diretamente pelo painel, sem necessidade de acesso SSH.
                Ideal para hospedagem compartilhada.
            </div>
        </div>
    </div>
    
    {{-- Cache Warmup --}}
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary">
                <h5 class="card-title text-white mb-0">
                    <i class="las la-fire me-2"></i>Aquecer Cache
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Pré-carrega dados frequentes no cache: rankings, rodeios, modalidades e competidores.
                    <strong>Recomendado após deploy.</strong>
                </p>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="warmupClear" checked>
                    <label class="form-check-label" for="warmupClear">
                        Limpar cache antes de aquecer
                    </label>
                </div>
                @if(isset($lastRun['warmup']))
                <small class="text-muted d-block mb-2">
                    Último: {{ $lastRun['warmup']['ran_at'] }} 
                    ({{ $lastRun['warmup']['duration_ms'] }}ms)
                    @if($lastRun['warmup']['success'])
                        <span class="badge bg-success">OK</span>
                    @else
                        <span class="badge bg-danger">Erro</span>
                    @endif
                </small>
                @endif
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-primary w-100" onclick="runCommand('warmup')">
                    <i class="las la-play me-1"></i>Executar
                </button>
            </div>
        </div>
    </div>
    
    {{-- Update Rankings --}}
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success">
                <h5 class="card-title text-white mb-0">
                    <i class="las la-trophy me-2"></i>Atualizar Rankings
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Regenera os rankings X1 e Fantasy a partir dos dados atuais.
                    <strong>Roda automaticamente a cada 5 minutos via cron.</strong>
                </p>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="rankingsWarm" checked>
                    <label class="form-check-label" for="rankingsWarm">
                        Aquecer cache após atualizar
                    </label>
                </div>
                @if(isset($lastRun['rankings']))
                <small class="text-muted d-block mb-2">
                    Último: {{ $lastRun['rankings']['ran_at'] }} 
                    ({{ $lastRun['rankings']['duration_ms'] }}ms)
                    @if($lastRun['rankings']['success'])
                        <span class="badge bg-success">OK</span>
                    @else
                        <span class="badge bg-danger">Erro</span>
                    @endif
                </small>
                @endif
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-success w-100" onclick="runCommand('rankings')">
                    <i class="las la-play me-1"></i>Executar
                </button>
            </div>
        </div>
    </div>
    
    {{-- Process Payments --}}
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-warning">
                <h5 class="card-title text-dark mb-0">
                    <i class="las la-credit-card me-2"></i>Processar Pagamentos
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Verifica pagamentos X1 pendentes no Mercado Pago e atualiza status.
                    <strong>Roda automaticamente a cada minuto via cron.</strong>
                </p>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small">Limite</label>
                        <input type="number" class="form-control form-control-sm" id="paymentsLimit" value="20" min="1" max="100">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="paymentsDryRun">
                            <label class="form-check-label" for="paymentsDryRun">
                                Simulação
                            </label>
                        </div>
                    </div>
                </div>
                @if(isset($lastRun['payments']))
                <small class="text-muted d-block mb-2">
                    Último: {{ $lastRun['payments']['ran_at'] }} 
                    ({{ $lastRun['payments']['duration_ms'] }}ms)
                    @if($lastRun['payments']['success'])
                        <span class="badge bg-success">OK</span>
                    @else
                        <span class="badge bg-danger">Erro</span>
                    @endif
                </small>
                @endif
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-warning w-100" onclick="runCommand('payments')">
                    <i class="las la-play me-1"></i>Executar
                </button>
            </div>
        </div>
    </div>
    
    {{-- Clear Cache --}}
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-danger">
                <h5 class="card-title text-white mb-0">
                    <i class="las la-trash me-2"></i>Limpar Cache
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Limpa todos os caches: aplicação, rotas, views e configurações.
                    <strong>Use se houver problemas após atualizações.</strong>
                </p>
                @if(isset($lastRun['clear']))
                <small class="text-muted d-block mb-2">
                    Último: {{ $lastRun['clear']['ran_at'] }} 
                    ({{ $lastRun['clear']['duration_ms'] }}ms)
                    @if($lastRun['clear']['success'])
                        <span class="badge bg-success">OK</span>
                    @else
                        <span class="badge bg-danger">Erro</span>
                    @endif
                </small>
                @endif
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-danger w-100" onclick="runCommand('clear')">
                    <i class="las la-play me-1"></i>Executar
                </button>
            </div>
        </div>
    </div>
    
    {{-- Clean X1 Rooms --}}
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-secondary">
                <h5 class="card-title text-white mb-0">
                    <i class="las la-broom me-2"></i>Limpar Salas X1
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Remove salas X1 com pagamento pendente há mais de 3 minutos.
                    <strong>Roda automaticamente a cada minuto via cron.</strong>
                </p>
                @if(isset($lastRun['clean_x1']))
                <small class="text-muted d-block mb-2">
                    Último: {{ $lastRun['clean_x1']['ran_at'] }} 
                    ({{ $lastRun['clean_x1']['duration_ms'] }}ms)
                    @if($lastRun['clean_x1']['success'])
                        <span class="badge bg-success">OK</span>
                    @else
                        <span class="badge bg-danger">Erro</span>
                    @endif
                </small>
                @endif
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-secondary w-100" onclick="runCommand('clean_x1')">
                    <i class="las la-play me-1"></i>Executar
                </button>
            </div>
        </div>
    </div>
    
    {{-- Info Card --}}
    <div class="col-xl-4 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-info">
                <h5 class="card-title text-white mb-0">
                    <i class="las la-clock me-2"></i>Cron Jobs
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Configure o cron no cPanel/Hostinger:</p>
                <code class="d-block p-2 bg-light rounded small mb-3" style="word-break: break-all;">
                    * * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
                </code>
                <small class="text-muted">
                    <strong>Jobs automáticos:</strong><br>
                    • Pagamentos X1: cada 1 min<br>
                    • Rankings: cada 5 min<br>
                    • Limpar X1: cada 1 min<br>
                    • Cache warmup: 05:00 diário
                </small>
            </div>
        </div>
    </div>
</div>

{{-- Output Modal --}}
<div class="modal fade" id="outputModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="las la-terminal me-2"></i>Saída do Comando
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="commandOutput" class="bg-dark text-white p-3 rounded" style="max-height: 400px; overflow: auto; font-size: 12px;"></pre>
            </div>
            <div class="modal-footer">
                <span id="commandDuration" class="text-muted me-auto"></span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
function runCommand(command) {
    let url, data = {};
    
    switch(command) {
        case 'warmup':
            url = '{{ route("admin.system.warmup") }}';
            data.clear = document.getElementById('warmupClear').checked;
            break;
        case 'rankings':
            url = '{{ route("admin.system.rankings") }}';
            data.warm = document.getElementById('rankingsWarm').checked;
            break;
        case 'payments':
            url = '{{ route("admin.system.payments") }}';
            data.limit = document.getElementById('paymentsLimit').value;
            data.dry_run = document.getElementById('paymentsDryRun').checked;
            break;
        case 'clear':
            url = '{{ route("admin.system.clear") }}';
            break;
        case 'clean_x1':
            url = '{{ route("admin.system.clean-x1") }}';
            break;
    }
    
    // Mostrar loading
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Executando...';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (result.success) {
            // Mostrar output se houver
            if (result.output) {
                document.getElementById('commandOutput').textContent = result.output;
                document.getElementById('commandDuration').textContent = `Executado em ${result.duration_ms}ms`;
                new bootstrap.Modal(document.getElementById('outputModal')).show();
            }
            
            // Toast de sucesso
            notify('success', result.message);
        } else {
            notify('error', result.message);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        notify('error', 'Erro ao executar comando: ' + error.message);
    });
}
</script>
@endpush
