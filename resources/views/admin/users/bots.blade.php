@extends('admin.layouts.app')

@section('panel')
<div class="row mb-4">
    <div class="col-12">
        <div class="alert border-0" style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: white;">
            <div class="d-flex align-items-center">
                <i class="las la-eye" style="font-size: 2.5rem; margin-right: 15px;"></i>
                <div>
                    <h5 class="mb-1" style="color: white; font-weight: 600;">🎭 BOTS VISÍVEIS (Vitrine)</h5>
                    <p class="mb-0" style="opacity: 0.95;">
                        Bots aparecem para usuários mas não podem interagir com eles. 
                        <strong>X1:</strong> aparecem em "Todas" (fechadas). 
                        <strong>Fantasy:</strong> aparecem em rankings e listas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Criação Rápida -->
<div class="row g-4 mb-4">
    <!-- X1 -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                <h5 class="card-title mb-0 text-white">
                    <i class="las la-bolt"></i> ⚔️ Criar Salas X1
                </h5>
            </div>
            <div class="card-body">
                <form id="form-generate-x1">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Quantidade de Salas</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="quantity" min="1" max="100" value="10" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="document.querySelector('#form-generate-x1 input[name=quantity]').value = Math.floor(Math.random() * 20) + 5">
                                    <i class="las la-dice"></i> Aleatório
                                </button>
                            </div>
                            <small class="text-muted">Salas aparecem em "Todas" como finalizadas (bot vs bot)</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Rodeio</label>
                            <select class="form-select" name="rodeio_id" required>
                                <option value="">Selecione o rodeio...</option>
                                @foreach($rodeios as $rodeio)
                                    <option value="{{ $rodeio->id }}">{{ $rodeio->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Modo de Valores</label>
                            <div class="btn-group w-100 mb-2" role="group">
                                <input type="radio" class="btn-check" name="value_mode_x1" id="value-mode-x1-auto" value="auto" checked>
                                <label class="btn btn-outline-primary" for="value-mode-x1-auto">
                                    <i class="las la-dice"></i> Automático
                                </label>
                                
                                <input type="radio" class="btn-check" name="value_mode_x1" id="value-mode-x1-manual" value="manual">
                                <label class="btn btn-outline-primary" for="value-mode-x1-manual">
                                    <i class="las la-hand-point-up"></i> Manual
                                </label>
                            </div>
                            
                            <div id="x1-manual-values" style="display: none;">
                                <label class="form-label">Valor Fixo (R$)</label>
                                <input type="number" class="form-control" name="fixed_value_x1" min="20" max="5000" step="10" value="100" placeholder="Ex: 100">
                                <small class="text-muted">Valor mínimo: R$ 20,00</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-lg w-100" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; font-weight: 600; border: none;">
                                <i class="las la-rocket"></i> 🚀 Gerar Salas X1 Agora
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Fantasy -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <h5 class="card-title mb-0 text-white">
                    <i class="las la-trophy"></i> 🏆 Criar Ligas Fantasy
                </h5>
            </div>
            <div class="card-body">
                <form id="form-generate-fantasy">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Quantidade de Ligas</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="quantity" min="1" max="50" value="5" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="document.querySelector('#form-generate-fantasy input[name=quantity]').value = Math.floor(Math.random() * 10) + 3">
                                    <i class="las la-dice"></i> Aleatório
                                </button>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Rodeio</label>
                            <select class="form-select" name="rodeio_id" required>
                                <option value="">Selecione o rodeio...</option>
                                @foreach($rodeios as $rodeio)
                                    <option value="{{ $rodeio->id }}">{{ $rodeio->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Times por Liga</label>
                            <input type="number" class="form-control" name="teams_per_league" min="10" max="500" value="40" required>
                            <small class="text-muted">Máximo 500 times por liga (recomendado: 40-100 para melhor performance)</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Modo de Valores</label>
                            <div class="btn-group w-100 mb-2" role="group">
                                <input type="radio" class="btn-check" name="value_mode_fantasy" id="value-mode-fantasy-auto" value="auto" checked>
                                <label class="btn btn-outline-success" for="value-mode-fantasy-auto">
                                    <i class="las la-dice"></i> Automático
                                </label>
                                
                                <input type="radio" class="btn-check" name="value_mode_fantasy" id="value-mode-fantasy-manual" value="manual">
                                <label class="btn btn-outline-success" for="value-mode-fantasy-manual">
                                    <i class="las la-hand-point-up"></i> Manual
                                </label>
                            </div>
                            
                            <div id="fantasy-manual-values" style="display: none;">
                                <label class="form-label">Valor de Entrada (R$)</label>
                                <select class="form-select" name="fixed_value_fantasy">
                                    <option value="20">R$ 20,00</option>
                                    <option value="50">R$ 50,00</option>
                                    <option value="100">R$ 100,00</option>
                                </select>
                                <small class="text-muted">Apenas valores padrão do sistema</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-lg w-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; font-weight: 600; border: none;">
                                <i class="las la-rocket"></i> 🚀 Gerar Ligas Agora
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Configurações e Limpeza -->
<div class="row g-4">
    <!-- Popular Liga Existente com Bots -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                <h5 class="card-title mb-0 text-white">
                    <i class="las la-users-cog"></i> 🤖 Popular Liga com Bots
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 mb-3">
                    <small>
                        <strong>Como funciona:</strong><br>
                        • Adiciona bots a uma liga Fantasy existente<br>
                        • A cada 3 usuários reais que entram, 1 bot sai automaticamente<br>
                        • Mínimo de 5 bots sempre mantidos na liga
                    </small>
                </div>
                <form id="form-populate-league">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Selecionar Liga</label>
                            <select class="form-select" name="league_id" id="populate-league-select" required>
                                <option value="">Carregando ligas...</option>
                            </select>
                            <small class="text-muted">Apenas ligas ativas são exibidas</small>
                        </div>

                        <div class="col-12" id="league-info" style="display: none;">
                            <div class="p-3 rounded" style="background: #f8f9fa;">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <span class="d-block fw-bold text-primary" id="league-total">-</span>
                                        <small class="text-muted">Total</small>
                                    </div>
                                    <div class="col-4">
                                        <span class="d-block fw-bold text-success" id="league-real">-</span>
                                        <small class="text-muted">Reais</small>
                                    </div>
                                    <div class="col-4">
                                        <span class="d-block fw-bold text-warning" id="league-bots">-</span>
                                        <small class="text-muted">Bots</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Mínimo de Bots</label>
                            <input type="number" class="form-control" name="min_bots" min="10" max="500" value="60" required>
                            <small class="text-muted">Bots serão adicionados até atingir este número</small>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-lg w-100" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white; font-weight: 600; border: none;">
                                <i class="las la-robot"></i> 🤖 Popular Liga com Bots
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Separador -->
                <hr class="my-4" style="border-color: #e5e7eb;">

                <!-- Remover Bots da Liga -->
                <div class="alert alert-warning border-0 mb-3">
                    <small>
                        <strong>⚠️ Remover Bots:</strong><br>
                        • Remove bots da liga selecionada acima<br>
                        • Informe a quantidade ou remova todos de uma vez
                    </small>
                </div>
                <form id="form-remove-bots-league">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Quantidade para remover</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="remove_quantity" id="remove-bots-quantity" min="1" max="500" placeholder="Ex: 10">
                                <div class="input-group-text">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="remove-all-bots" name="remove_all">
                                        <label class="form-check-label" for="remove-all-bots">
                                            <small>Todos</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Marque "Todos" para remover todos os bots da liga</small>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-lg w-100 btn-outline-danger" style="font-weight: 600;">
                                <i class="las la-user-minus"></i> 🗑️ Remover Bots da Liga
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload JSON -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-file-upload"></i> 📁 Arquivo de Bots
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0">
                    <div class="d-flex align-items-center">
                        <i class="las la-info-circle" style="font-size: 1.8rem; margin-right: 10px;"></i>
                        <div>
                            <strong>Arquivo atual:</strong> storage/app/bots.json<br>
                            <strong>Bots disponíveis:</strong> <span id="bots-available">{{ $botsAvailable }}</span> pessoas
                        </div>
                    </div>
                </div>

                <form id="form-upload-bots">
                    <div class="mb-3">
                        <label class="form-label">Upload Novo JSON (4devs.com.br)</label>
                        <input type="file" class="form-control" name="bots_file" accept=".json" required>
                        <small class="text-muted">Formato: Array de objetos com nome, cpf, email, celular</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="las la-cloud-upload-alt"></i> Fazer Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Limpeza -->
    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="las la-trash-alt"></i> 🗑️ Limpeza de Bots
                </h5>
            </div>
            <div class="card-body">
                <p class="text-danger mb-3">
                    <i class="las la-exclamation-triangle"></i> <strong>Atenção:</strong> Esta ação não pode ser desfeita!
                </p>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmClear('x1')">
                        <i class="las la-chess-king"></i> Limpar só X1
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="confirmClear('fantasy')">
                        <i class="las la-trophy"></i> Limpar só Fantasy
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmClear('all')">
                        <i class="las la-trash-restore"></i> Limpar TODOS os Bots
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimas Criações -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="las la-history"></i> 📊 Últimas Criações
                </h5>
            </div>
            <div class="card-body">
                <div id="recent-activity">
                    @if(count($recentActivity) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentActivity as $activity)
                                <li class="list-group-item d-flex align-items-center">
                                    @if($activity['type'] === 'x1')
                                        <span class="badge bg-warning me-3" style="font-size: 1.2rem;">⚔️</span>
                                    @else
                                        <span class="badge bg-success me-3" style="font-size: 1.2rem;">🏆</span>
                                    @endif
                                    <div>
                                        <strong>{{ $activity['date'] }}</strong> - {{ $activity['description'] }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="las la-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mb-0 mt-2">Nenhuma atividade recente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loader Overlay -->
<div id="bot-loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; backdrop-filter: blur(5px);">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
        <div class="spinner-border text-warning" role="status" style="width: 4rem; height: 4rem; border-width: 4px;">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="text-white mt-3 mb-0" style="font-size: 1.2rem; font-weight: 500;" id="loader-text">Gerando bots...</p>
    </div>
</div>

@endsection

@push('style')
<style>
.card {
    border-radius: 12px;
    overflow: hidden;
}
.card-header.bg-gradient {
    border: none;
}
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>
@endpush

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// CSRF Token
const csrfToken = '{{ csrf_token() }}';

// Elementos
const loader = document.getElementById('bot-loader');
const loaderText = document.getElementById('loader-text');

// Mostrar/Esconder loader
function showLoader(text = 'Processando...') {
    loaderText.textContent = text;
    loader.style.display = 'block';
}

function hideLoader() {
    loader.style.display = 'none';
}

// 🎛️ Toggle campos manuais X1
document.querySelectorAll('input[name="value_mode_x1"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const manualDiv = document.getElementById('x1-manual-values');
        manualDiv.style.display = this.value === 'manual' ? 'block' : 'none';
    });
});

// 🎛️ Toggle campos manuais Fantasy
document.querySelectorAll('input[name="value_mode_fantasy"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const manualDiv = document.getElementById('fantasy-manual-values');
        manualDiv.style.display = this.value === 'manual' ? 'block' : 'none';
    });
});

// Gerar X1
document.getElementById('form-generate-x1').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const quantity = formData.get('quantity');
    
    showLoader(`Gerando ${quantity} salas X1...`);
    
    try {
        const response = await fetch('{{ route("admin.users.bots.generate.x1") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const result = await response.json();
        
        hideLoader();
        
        if (result.success) {
            // Sucesso
            Swal.fire({
                icon: 'success',
                title: '✅ Sucesso!',
                html: result.message,
                confirmButtonColor: '#f97316'
            });
            
            // Recarregar após 2s
            setTimeout(() => location.reload(), 2000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: result.message,
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        hideLoader();
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro de conexão: ' + error.message,
            confirmButtonColor: '#ef4444'
        });
    }
});

// Gerar Fantasy
document.getElementById('form-generate-fantasy').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const quantity = formData.get('quantity');
    const teams = formData.get('teams_per_league');
    
    showLoader(`Gerando ${quantity} ligas com ${teams} times cada...`);
    
    try {
        const response = await fetch('{{ route("admin.users.bots.generate.fantasy") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const result = await response.json();
        
        hideLoader();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '✅ Sucesso!',
                html: result.message,
                confirmButtonColor: '#10b981'
            });
            
            setTimeout(() => location.reload(), 2000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: result.message,
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        hideLoader();
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro de conexão: ' + error.message,
            confirmButtonColor: '#ef4444'
        });
    }
});

// Upload de arquivo
document.getElementById('form-upload-bots').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    showLoader('Carregando arquivo de bots...');
    
    try {
        const response = await fetch('{{ route("admin.users.bots.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const result = await response.json();
        
        hideLoader();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '✅ Arquivo carregado!',
                html: result.message,
                confirmButtonColor: '#3b82f6'
            });
            
            this.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: result.message,
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        hideLoader();
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro ao processar arquivo: ' + error.message,
            confirmButtonColor: '#ef4444'
        });
    }
});

// Confirmar limpeza
async function confirmClear(type) {
    const messages = {
        'x1': 'Tem certeza que deseja remover TODAS as salas X1 de bots?',
        'fantasy': 'Tem certeza que deseja remover TODAS as ligas Fantasy de bots?',
        'all': 'Tem certeza que deseja remover TODOS os bots do sistema? (usuários, X1 e Fantasy)'
    };
    
    const result = await Swal.fire({
        icon: 'warning',
        title: '⚠️ Atenção!',
        text: messages[type],
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, limpar!',
        cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
        showLoader('Removendo bots...');
        
        try {
            const response = await fetch('{{ route("admin.users.bots.clear") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ type })
            });
            
            const result = await response.json();
            
            hideLoader();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: '✅ Removido!',
                    text: result.message,
                    confirmButtonColor: '#10b981'
                });
                
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: result.message,
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (error) {
            hideLoader();
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Erro ao remover bots: ' + error.message,
                confirmButtonColor: '#ef4444'
            });
        }
    }
}

// ==========================================
// 🤖 POPULAR LIGA COM BOTS
// ==========================================

let availableLeagues = [];

// Carregar ligas ao iniciar a página
async function loadAvailableLeagues() {
    const select = document.getElementById('populate-league-select');
    
    try {
        const response = await fetch('{{ route("admin.users.bots.leagues") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            availableLeagues = result.data;
            
            select.innerHTML = '<option value="">Selecione uma liga...</option>';
            // Ensure the select is enabled even if no leagues returned
            if (result.data.length === 0) {
                select.innerHTML = '<option value="">Nenhuma liga ativa encontrada</option>';
                select.disabled = true;
            } else {
                select.disabled = false;
            }
            
            result.data.forEach(league => {
                const option = document.createElement('option');
                option.value = league.id;
                option.textContent = `${league.name} (${league.rodeio} - ${league.modalidade}) [${league.total_teams}/${league.max_users}]`;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">Erro ao carregar ligas</option>';
        }
    } catch (error) {
        select.innerHTML = '<option value="">Erro de conexão</option>';
    }
}

// Atualizar info da liga selecionada
document.getElementById('populate-league-select').addEventListener('change', function() {
    const leagueId = parseInt(this.value);
    const infoDiv = document.getElementById('league-info');
    
    if (!leagueId) {
        infoDiv.style.display = 'none';
        return;
    }
    
    const league = availableLeagues.find(l => l.id === leagueId);
    if (league) {
        document.getElementById('league-total').textContent = league.total_teams;
        document.getElementById('league-real').textContent = league.real_teams;
        document.getElementById('league-bots').textContent = league.bot_teams;
        infoDiv.style.display = 'block';
    }
});

// Popular liga com bots
document.getElementById('form-populate-league').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const minBots = formData.get('min_bots');
    
    showLoader(`Adicionando bots à liga...`);
    
    try {
        const response = await fetch('{{ route("admin.users.bots.populate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const result = await response.json();
        
        hideLoader();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '✅ Sucesso!',
                html: `${result.message}<br><br><strong>Bots criados:</strong> ${result.data.created}<br><strong>Total na liga:</strong> ${result.data.total_bots}`,
                confirmButtonColor: '#8b5cf6'
            });
            
            // Recarregar ligas
            loadAvailableLeagues();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: result.message,
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        hideLoader();
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro de conexão: ' + error.message,
            confirmButtonColor: '#ef4444'
        });
    }
});

// ==========================================
// 🗑️ REMOVER BOTS DA LIGA
// ==========================================

// Toggle campo quantidade ao marcar "Todos"
document.getElementById('remove-all-bots').addEventListener('change', function() {
    const qtyInput = document.getElementById('remove-bots-quantity');
    if (this.checked) {
        qtyInput.value = '';
        qtyInput.disabled = true;
        qtyInput.placeholder = 'Todos serão removidos';
    } else {
        qtyInput.disabled = false;
        qtyInput.placeholder = 'Ex: 10';
    }
});

document.getElementById('form-remove-bots-league').addEventListener('submit', async function(e) {
    e.preventDefault();

    const leagueSelect = document.getElementById('populate-league-select');
    const leagueId = leagueSelect.value;
    const removeAll = document.getElementById('remove-all-bots').checked;
    const quantity = document.getElementById('remove-bots-quantity').value;

    if (!leagueId) {
        Swal.fire({
            icon: 'warning',
            title: 'Selecione uma liga',
            text: 'Selecione uma liga no seletor acima antes de remover bots.',
            confirmButtonColor: '#8b5cf6'
        });
        return;
    }

    if (!removeAll && (!quantity || quantity < 1)) {
        Swal.fire({
            icon: 'warning',
            title: 'Quantidade inválida',
            text: 'Informe quantos bots deseja remover ou marque "Todos".',
            confirmButtonColor: '#8b5cf6'
        });
        return;
    }

    const league = availableLeagues.find(l => l.id === parseInt(leagueId));
    const leagueName = league ? league.name : 'Liga #' + leagueId;
    const botsCount = league ? league.bot_teams : '?';
    const removeText = removeAll ? `TODOS os ${botsCount} bots` : `${quantity} bot(s)`;

    const confirm = await Swal.fire({
        icon: 'warning',
        title: 'Confirmar remoção',
        html: `Deseja remover <strong>${removeText}</strong> da liga <strong>${leagueName}</strong>?<br><br><small class="text-danger">Esta ação não pode ser desfeita!</small>`,
        showCancelButton: true,
        confirmButtonText: 'Sim, remover!',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d'
    });

    if (!confirm.isConfirmed) return;

    const formData = new FormData();
    formData.append('league_id', leagueId);
    if (removeAll) {
        formData.append('remove_all', '1');
    } else {
        formData.append('quantity', quantity);
    }

    showLoader('Removendo bots da liga...');

    try {
        const response = await fetch('{{ route("admin.users.bots.remove.bots") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });

        const result = await response.json();
        hideLoader();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: '✅ Bots removidos!',
                html: `${result.message}<br><br><strong>Removidos:</strong> ${result.data.removed}<br><strong>Restantes:</strong> ${result.data.remaining_bots}`,
                confirmButtonColor: '#8b5cf6'
            });

            // Recarregar ligas
            loadAvailableLeagues();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: result.message,
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        hideLoader();
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Erro de conexão: ' + error.message,
            confirmButtonColor: '#ef4444'
        });
    }
});

// Carregar ligas ao iniciar
document.addEventListener('DOMContentLoaded', loadAvailableLeagues);
</script>
@endpush
