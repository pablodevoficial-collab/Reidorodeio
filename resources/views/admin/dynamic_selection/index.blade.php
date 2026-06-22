@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Seleção de Rodeio e Modalidade -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">@lang('Sistema de Seleção Dinâmica')</h4>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>@lang('Rodeio Ativo')</label>
                        <select id="dynamicRodeio" class="form-control" onchange="loadDynamicModalidades()">
                            <option value="">@lang('Selecione o Rodeio')</option>
                            @foreach(\App\Models\Rodeio::where('status', 'ativo')->get() as $rodeio)
                                <option value="{{ $rodeio->id }}">{{ $rodeio->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>@lang('Modalidade Atual')</label>
                        <select id="dynamicModalidade" class="form-control" onchange="loadDynamicCompetitors()">
                            <option value="">@lang('Selecione a Modalidade')</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>@lang('Busca Rápida')</label>
                        <div class="input-group">
                            <input type="text" id="quickSearch" class="form-control" placeholder="@lang('Digite o nome do competidor...')" onkeyup="filterCompetitors()">
                            <button class="btn btn--primary" onclick="clearSearch()">
                                <i class="las la-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn--success w-100" onclick="refreshCompetitors()">
                            <i class="las la-sync"></i> @lang('Atualizar')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid de Competidores -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('Competidores Participantes')</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn--info" onclick="toggleView('grid')" id="gridViewBtn">
                        <i class="las la-th"></i> @lang('Grade')
                    </button>
                    <button class="btn btn-sm btn--secondary" onclick="toggleView('list')" id="listViewBtn">
                        <i class="las la-list"></i> @lang('Lista')
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Grid View -->
                <div id="competitorsGrid" class="row">
                    <div class="col-12 text-center py-5">
                        <i class="las la-users" style="font-size: 4rem; color: #ccc;"></i>
                        <p class="text-muted">@lang('Selecione um rodeio e modalidade para carregar os competidores')</p>
                    </div>
                </div>

                <!-- List View (Hidden by default) -->
                <div id="competitorsList" class="d-none">
                    <div class="table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Competidor')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Pontuação')</th>
                                    <th>@lang('Posição')</th>
                                    <th>@lang('Ações')</th>
                                </tr>
                            </thead>
                            <tbody id="competitorsTableBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">@lang('Nenhum competidor carregado')</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Painel de Informações -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Informações da Modalidade')</h5>
            </div>
            <div class="card-body">
                <div id="modalidadeInfo" class="row text-center">
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--info">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="totalCompetitors">0</h3>
                                <p class="text-white">@lang('Total de Competidores')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--success">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="confirmedCompetitors">0</h3>
                                <p class="text-white">@lang('Confirmados')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--warning">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="inscribedCompetitors">0</h3>
                                <p class="text-white">@lang('Inscritos')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget-two style--two box--shadow2 b-radius--5 bg--danger">
                            <div class="widget-two__content">
                                <h3 class="text-white" id="eliminatedCompetitors">0</h3>
                                <p class="text-white">@lang('Eliminados')</p>
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
                <h5 class="card-title">@lang('Competidor Selecionado')</h5>
            </div>
            <div class="card-body">
                <div id="selectedCompetitorInfo" class="text-center">
                    <i class="las la-user-circle" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted">@lang('Clique em um competidor para ver detalhes')</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes do Competidor -->
<div class="modal fade" id="competitorDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Detalhes do Competidor')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="competitorDetailsContent">
                <!-- Conteúdo será carregado dinamicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Fechar')</button>
                <button type="button" class="btn btn--primary" onclick="editCompetitorInModal()">@lang('Editar')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
.competitor-card {
    border: 2px solid transparent;
    transition: all 0.3s ease;
    cursor: pointer;
}

.competitor-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.competitor-card.selected {
    border-color: #28a745;
    background-color: #f8fff9;
}

.competitor-card.eliminated {
    opacity: 0.6;
    background-color: #fff5f5;
}

.competitor-photo {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    margin: 0 auto 10px;
    display: block;
}

.status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.search-highlight {
    background-color: #fff3cd;
    border-radius: 3px;
    padding: 2px 4px;
}
</style>
@endpush

@push('script')
<script>
let currentCompetitors = [];
let selectedCompetitor = null;
let currentView = 'grid';

// Carregar modalidades do rodeio
function loadDynamicModalidades() {
    const rodeioId = $('#dynamicRodeio').val();
    const modalidadeSelect = $('#dynamicModalidade');
    
    modalidadeSelect.html('<option value="">@lang('Selecione a Modalidade')</option>');
    
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

// Carregar competidores da modalidade
function loadDynamicCompetitors() {
    const rodeioId = $('#dynamicRodeio').val();
    const modalidadeId = $('#dynamicModalidade').val();
    
    if (!rodeioId || !modalidadeId) {
        resetCompetitorsView();
        return;
    }

    showLoading();

    fetch(`/admin/dynamic-selection/competitors?rodeio_id=${rodeioId}&modalidade_id=${modalidadeId}`)
        .then(response => response.json())
        .then(data => {
            currentCompetitors = data.competitors;
            updateCompetitorsView();
            updateStatsCards();
        })
        .catch(error => {
            console.error('Erro ao carregar competidores:', error);
            showError();
        });
}

// Atualizar visualização dos competidores
function updateCompetitorsView() {
    if (currentView === 'grid') {
        renderGridView();
    } else {
        renderListView();
    }
}

// Renderizar visualização em grade
function renderGridView() {
    const container = $('#competitorsGrid');
    
    if (currentCompetitors.length === 0) {
        container.html(`
            <div class="col-12 text-center py-5">
                <i class="las la-user-slash" style="font-size: 4rem; color: #ccc;"></i>
                <p class="text-muted">@lang('Nenhum competidor encontrado nesta modalidade')</p>
            </div>
        `);
        return;
    }

    let html = '';
    currentCompetitors.forEach(competitor => {
        const statusClass = getStatusClass(competitor.pivot.status);
        const isEliminated = competitor.pivot.status === 'eliminado';
        
        html += `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 competitor-item" data-name="${competitor.nome.toLowerCase()}">
                <div class="card competitor-card ${isEliminated ? 'eliminated' : ''}" 
                     onclick="selectCompetitor(${competitor.id})" 
                     data-competitor-id="${competitor.id}">
                    <div class="card-body text-center position-relative">
                        <span class="badge ${statusClass} status-badge">${getStatusText(competitor.pivot.status)}</span>
                        
                        ${competitor.foto ? 
                            `<img src="/assets/images/competitors/${competitor.foto}" alt="${competitor.nome}" class="competitor-photo">` :
                            `<div class="competitor-photo bg-light d-flex align-items-center justify-content-center">
                                <i class="las la-user" style="font-size: 2rem; color: #ccc;"></i>
                            </div>`
                        }
                        
                        <h6 class="card-title mb-1">${competitor.nome}</h6>
                        <small class="text-muted">${competitor.idade} anos</small>
                        
                        <div class="mt-2">
                            <small class="d-block">@lang('Pontuação'): <strong>${competitor.pivot.pontuacao || 0}</strong></small>
                            ${competitor.pivot.posicao_final ? `<small class="d-block">@lang('Posição'): <strong>${competitor.pivot.posicao_final}º</strong></small>` : ''}
                        </div>
                        
                        <div class="mt-3">
                            <div class="btn-group">
                                <button class="btn btn-sm btn--success" onclick="quickStatusUpdate(${competitor.id}, 'confirmado', event)">
                                    <i class="las la-check"></i>
                                </button>
                                <button class="btn btn-sm btn--danger" onclick="quickStatusUpdate(${competitor.id}, 'eliminado', event)">
                                    <i class="las la-times"></i>
                                </button>
                                <button class="btn btn-sm btn--info" onclick="showCompetitorDetails(${competitor.id}, event)">
                                    <i class="las la-eye"></i>
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

// Renderizar visualização em lista
function renderListView() {
    const tbody = $('#competitorsTableBody');
    
    if (currentCompetitors.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center text-muted">@lang('Nenhum competidor encontrado')</td></tr>');
        return;
    }

    let html = '';
    currentCompetitors.forEach(competitor => {
        const statusBadge = `<span class="badge ${getStatusClass(competitor.pivot.status)}">${getStatusText(competitor.pivot.status)}</span>`;
        
        html += `
            <tr class="competitor-item" data-name="${competitor.nome.toLowerCase()}" data-competitor-id="${competitor.id}">
                <td>
                    <div class="user">
                        ${competitor.foto ? 
                            `<div class="thumb"><img src="/assets/images/competitors/${competitor.foto}" alt=""></div>` : 
                            '<div class="thumb bg-light"><i class="las la-user"></i></div>'
                        }
                        <span class="name">${competitor.nome}</span>
                    </div>
                </td>
                <td>${statusBadge}</td>
                <td>${competitor.pivot.pontuacao || 0}</td>
                <td>${competitor.pivot.posicao_final ? competitor.pivot.posicao_final + 'º' : '-'}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn--success" onclick="quickStatusUpdate(${competitor.id}, 'confirmado', event)">
                            <i class="las la-check"></i>
                        </button>
                        <button class="btn btn-sm btn--danger" onclick="quickStatusUpdate(${competitor.id}, 'eliminado', event)">
                            <i class="las la-times"></i>
                        </button>
                        <button class="btn btn-sm btn--info" onclick="showCompetitorDetails(${competitor.id}, event)">
                            <i class="las la-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.html(html);
}

// Alternar entre visualizações
function toggleView(view) {
    currentView = view;
    
    if (view === 'grid') {
        $('#competitorsGrid').removeClass('d-none');
        $('#competitorsList').addClass('d-none');
        $('#gridViewBtn').removeClass('btn--secondary').addClass('btn--info');
        $('#listViewBtn').removeClass('btn--info').addClass('btn--secondary');
    } else {
        $('#competitorsGrid').addClass('d-none');
        $('#competitorsList').removeClass('d-none');
        $('#gridViewBtn').removeClass('btn--info').addClass('btn--secondary');
        $('#listViewBtn').removeClass('btn--secondary').addClass('btn--info');
    }
    
    updateCompetitorsView();
}

// Filtrar competidores por busca
function filterCompetitors() {
    const searchTerm = $('#quickSearch').val().toLowerCase();
    const items = $('.competitor-item');
    
    items.each(function() {
        const name = $(this).data('name');
        if (name.includes(searchTerm)) {
            $(this).show();
            // Destacar termo de busca
            if (searchTerm) {
                const nameElement = $(this).find('.card-title, .name');
                const originalText = nameElement.text();
                const highlightedText = originalText.replace(new RegExp(searchTerm, 'gi'), '<span class="search-highlight">$&</span>');
                nameElement.html(highlightedText);
            }
        } else {
            $(this).hide();
        }
    });
}

// Limpar busca
function clearSearch() {
    $('#quickSearch').val('');
    $('.competitor-item').show();
    $('.search-highlight').each(function() {
        $(this).parent().text($(this).parent().text());
    });
}

// Selecionar competidor
function selectCompetitor(competitorId) {
    // Remover seleção anterior
    $('.competitor-card').removeClass('selected');
    
    // Adicionar seleção atual
    $(`.competitor-card[data-competitor-id="${competitorId}"]`).addClass('selected');
    
    const competitor = currentCompetitors.find(c => c.id === competitorId);
    if (competitor) {
        selectedCompetitor = competitor;
        updateSelectedCompetitorInfo();
    }
}

// Atualizar informações do competidor selecionado
function updateSelectedCompetitorInfo() {
    if (!selectedCompetitor) return;
    
    const info = $('#selectedCompetitorInfo');
    const statusBadge = `<span class="badge ${getStatusClass(selectedCompetitor.pivot.status)}">${getStatusText(selectedCompetitor.pivot.status)}</span>`;
    
    info.html(`
        ${selectedCompetitor.foto ? 
            `<img src="/assets/images/competitors/${selectedCompetitor.foto}" alt="${selectedCompetitor.nome}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 15px;">` :
            '<div style="width: 100px; height: 100px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;"><i class="las la-user" style="font-size: 3rem; color: #ccc;"></i></div>'
        }
        <h5>${selectedCompetitor.nome}</h5>
        <p class="text-muted">${selectedCompetitor.idade} anos - ${selectedCompetitor.cidade || 'N/A'}</p>
        <p>@lang('Status'): ${statusBadge}</p>
        <p>@lang('Pontuação'): <strong>${selectedCompetitor.pivot.pontuacao || 0}</strong></p>
        ${selectedCompetitor.pivot.posicao_final ? `<p>@lang('Posição'): <strong>${selectedCompetitor.pivot.posicao_final}º</strong></p>` : ''}
    `);
}

// Atualização rápida de status
function quickStatusUpdate(competitorId, status, event) {
    event.stopPropagation();
    
    const modalidadeId = $('#dynamicModalidade').val();
    
    fetch(`/admin/competitor-modalidade/modalidades/${modalidadeId}/competitors/${competitorId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar competidor na lista
            const competitor = currentCompetitors.find(c => c.id === competitorId);
            if (competitor) {
                competitor.pivot.status = status;
            }
            
            updateCompetitorsView();
            updateStatsCards();
        }
    });
}

// Mostrar detalhes do competidor
function showCompetitorDetails(competitorId, event) {
    event.stopPropagation();
    
    const competitor = currentCompetitors.find(c => c.id === competitorId);
    if (!competitor) return;
    
    $('#competitorDetailsContent').html(`
        <div class="row">
            <div class="col-md-4 text-center">
                ${competitor.foto ? 
                    `<img src="/assets/images/competitors/${competitor.foto}" alt="${competitor.nome}" class="img-fluid rounded">` :
                    '<div class="bg-light p-4 rounded"><i class="las la-user" style="font-size: 4rem; color: #ccc;"></i></div>'
                }
            </div>
            <div class="col-md-8">
                <h4>${competitor.nome}</h4>
                <p><strong>@lang('Idade'):</strong> ${competitor.idade} anos</p>
                <p><strong>@lang('Cidade'):</strong> ${competitor.cidade || 'N/A'}</p>
                <p><strong>@lang('Categoria'):</strong> ${competitor.categoria}</p>
                <p><strong>@lang('Status'):</strong> <span class="badge ${getStatusClass(competitor.pivot.status)}">${getStatusText(competitor.pivot.status)}</span></p>
                <p><strong>@lang('Pontuação'):</strong> ${competitor.pivot.pontuacao || 0}</p>
                ${competitor.pivot.posicao_final ? `<p><strong>@lang('Posição Final'):</strong> ${competitor.pivot.posicao_final}º</p>` : ''}
                ${competitor.biografia ? `<p><strong>@lang('Biografia'):</strong> ${competitor.biografia}</p>` : ''}
            </div>
        </div>
    `);
    
    $('#competitorDetailsModal').modal('show');
}

// Atualizar cards de estatísticas
function updateStatsCards() {
    const total = currentCompetitors.length;
    const confirmed = currentCompetitors.filter(c => c.pivot.status === 'confirmado').length;
    const inscribed = currentCompetitors.filter(c => c.pivot.status === 'inscrito').length;
    const eliminated = currentCompetitors.filter(c => c.pivot.status === 'eliminado').length;
    
    $('#totalCompetitors').text(total);
    $('#confirmedCompetitors').text(confirmed);
    $('#inscribedCompetitors').text(inscribed);
    $('#eliminatedCompetitors').text(eliminated);
}

// Utility functions
function getStatusClass(status) {
    const classes = {
        'inscrito': 'badge--warning',
        'confirmado': 'badge--success',
        'eliminado': 'badge--danger'
    };
    return classes[status] || 'badge--secondary';
}

function getStatusText(status) {
    const texts = {
        'inscrito': '@lang('Inscrito')',
        'confirmado': '@lang('Confirmado')',
        'eliminado': '@lang('Eliminado')'
    };
    return texts[status] || status;
}

function showLoading() {
    $('#competitorsGrid').html(`
        <div class="col-12 text-center py-5">
            <i class="las la-spinner la-spin" style="font-size: 4rem; color: #007bff;"></i>
            <p class="text-muted">@lang('Carregando competidores...')</p>
        </div>
    `);
}

function showError() {
    $('#competitorsGrid').html(`
        <div class="col-12 text-center py-5">
            <i class="las la-exclamation-triangle" style="font-size: 4rem; color: #dc3545;"></i>
            <p class="text-danger">@lang('Erro ao carregar competidores')</p>
        </div>
    `);
}

function resetCompetitorsView() {
    currentCompetitors = [];
    selectedCompetitor = null;
    updateCompetitorsView();
    updateStatsCards();
    $('#selectedCompetitorInfo').html(`
        <i class="las la-user-circle" style="font-size: 4rem; color: #ccc;"></i>
        <p class="text-muted">@lang('Clique em um competidor para ver detalhes')</p>
    `);
}

function refreshCompetitors() {
    loadDynamicCompetitors();
}
</script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.live_transmission.index') }}" />
@endpush
