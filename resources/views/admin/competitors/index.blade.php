@extends('admin.layouts.app')
@section('panel')
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')

{{-- Header com busca e filtros --}}
<div class="rr-comp-header">
    <div class="rr-comp-header__left">
        <h4 class="rr-comp-header__title">
            <i class="las la-users"></i>
            <span>{{ $competitors->total() }} Competidores</span>
        </h4>
    </div>
    <div class="rr-comp-header__right">
        <form class="rr-comp-search" method="get">
            <i class="las la-search"></i>
            <input type="search" id="searchCompetitor" name="q" value="{{ request('q') }}" placeholder="Buscar competidor..." class="rr-comp-search__input">
        </form>
        <a href="{{ route('admin.competitors.create') }}" class="rr-comp-btn rr-comp-btn--primary">
            <i class="las la-plus"></i>
            <span>Novo</span>
        </a>
    </div>
</div>

{{-- Grid de Cards --}}
<div class="rr-comp-grid" id="competitorsGrid">
    @forelse($competitors as $competitor)
        <div class="rr-comp-card"
             data-name="{{ strtolower($competitor->nome) }}"
             data-id="{{ $competitor->id }}"
             data-status="{{ $competitor->status }}"
             data-nivel="{{ $competitor->nivel }}"
             data-biografia="{{ e($competitor->biografia ?? '') }}"
             data-foto="{{ $competitor->foto_url }}">
            <div class="rr-comp-card__header">
                <div class="rr-comp-card__avatar">
                    @if($competitor->foto)
                        <img src="{{ $competitor->foto_url }}" alt="{{ $competitor->nome }}">
                    @else
                        <div class="rr-comp-card__avatar-placeholder">
                            {{ strtoupper(substr($competitor->nome, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="rr-comp-card__status rr-comp-card__status--{{ $competitor->status }}">
                    {{ $competitor->status == 'ativo' ? 'Ativo' : 'Inativo' }}
                </div>
            </div>
            
            <div class="rr-comp-card__body">
                <h5 class="rr-comp-card__name">{{ $competitor->nome }}</h5>
                
                <div class="rr-comp-card__nivel rr-comp-card__nivel--{{ $competitor->nivel }}">
                    @switch($competitor->nivel)
                        @case('favorito')
                            <i class="las la-star"></i> Favorito
                            @break
                        @case('elite')
                            <i class="las la-trophy"></i> Elite
                            @break
                        @case('legado')
                        @case('ascendente')
                            <i class="las la-arrow-up"></i> Ascendente
                            @break
                        @case('presilha')
                        @case('competidor')
                            <i class="las la-user"></i> Competidor
                            @break
                        @default
                            {{ ucfirst($competitor->nivel ?? '-') }}
                    @endswitch
                </div>
                
                @if($competitor->biografia)
                    <p class="rr-comp-card__bio">{{ \Illuminate\Support\Str::limit($competitor->biografia, 60) }}</p>
                @endif
                
                @if($competitor->stats)
                <div class="rr-comp-card__stats">
                    <div class="rr-comp-card__stat rr-comp-card__stat--win">
                        <span class="rr-comp-card__stat-value">{{ $competitor->stats->vitorias ?? 0 }}</span>
                        <span class="rr-comp-card__stat-label">V</span>
                    </div>
                    <div class="rr-comp-card__stat rr-comp-card__stat--draw">
                        <span class="rr-comp-card__stat-value">{{ $competitor->stats->empates ?? 0 }}</span>
                        <span class="rr-comp-card__stat-label">E</span>
                    </div>
                    <div class="rr-comp-card__stat rr-comp-card__stat--loss">
                        <span class="rr-comp-card__stat-value">{{ $competitor->stats->derrotas ?? 0 }}</span>
                        <span class="rr-comp-card__stat-label">D</span>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="rr-comp-card__footer">
                <a href="{{ route('admin.competitors.show', $competitor->id) }}" class="rr-comp-card__action" title="Ver detalhes">
                    <i class="las la-eye"></i>
                </a>
                <button type="button"
                        class="rr-comp-card__action rr-comp-card__action--primary js-edit-competitor"
                        title="Editar"
                        data-action="{{ route('admin.competitors.update', $competitor->id) }}"
                        data-id="{{ $competitor->id }}">
                    <i class="las la-pen"></i>
                </button>
                <form method="POST" action="{{ route('admin.competitors.destroy', $competitor->id) }}" data-action="{{ route('admin.competitors.destroy', $competitor->id) }}" data-competitor-id="{{ $competitor->id }}" class="rr-comp-card__action-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rr-comp-card__action rr-comp-card__action--danger" title="Excluir">
                        <i class="las la-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="rr-comp-empty">
            <i class="las la-users-slash"></i>
            <p>Nenhum competidor cadastrado</p>
            <a href="{{ route('admin.competitors.create') }}" class="rr-comp-btn rr-comp-btn--primary">
                <i class="las la-plus"></i> Criar primeiro competidor
            </a>
        </div>
    @endforelse
</div>

@if ($competitors->hasPages())
    <div class="rr-comp-pagination">
        {{ paginateLinks($competitors) }}
    </div>
@endif

<div class="modal fade rr-comp-modal" id="competitorEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="rr-comp-modal__title-wrap">
                    <h5 class="modal-title">Editar Competidor</h5>
                    <span class="rr-comp-modal__subtitle">Atualize dados, status e foto do competidor.</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="competitorEditForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="delete_foto" id="editDeleteFoto" value="0">
                    <input type="hidden" id="editCompetitorId" name="competitor_id">

                    <div class="rr-comp-modal__grid">
                        <div class="rr-comp-modal__field">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" id="editCompetitorNome" class="form-control" required>
                        </div>
                        <div class="rr-comp-modal__field">
                            <label class="form-label">Nível</label>
                            <select name="nivel" id="editCompetitorNivel" class="form-select" required>
                                <option value="">Selecione o nível</option>
                                <option value="favorito">⭐ Favorito</option>
                                <option value="elite">🏆 Elite</option>
                                <option value="ascendente">📈 Ascendente</option>
                                <option value="competidor">👤 Competidor</option>
                            </select>
                        </div>
                        <div class="rr-comp-modal__field rr-comp-modal__field--status">
                            <label class="form-label">Status</label>
                            <div class="rr-comp-modal__status">
                                <div class="form-check rr-comp-modal__status-item">
                                    <input class="form-check-input" type="radio" name="status" id="editStatusAtivo" value="ativo" required>
                                    <label class="form-check-label" for="editStatusAtivo">Ativo</label>
                                </div>
                                <div class="form-check rr-comp-modal__status-item">
                                    <input class="form-check-input" type="radio" name="status" id="editStatusInativo" value="inativo" required>
                                    <label class="form-check-label" for="editStatusInativo">Inativo</label>
                                </div>
                            </div>
                        </div>
                        <div class="rr-comp-modal__field rr-comp-modal__field--photo">
                            <label class="form-label">Foto</label>
                            <div class="rr-comp-modal__photo">
                                <input type="file" name="foto" id="editCompetitorFoto" class="form-control" accept="image/*">
                                <div class="form-check rr-comp-modal__remove">
                                    <input class="form-check-input" type="checkbox" id="editRemoveFoto">
                                    <label class="form-check-label" for="editRemoveFoto">Remover foto atual</label>
                                </div>
                                <div class="rr-comp-modal__preview" id="editFotoPreviewWrap" style="display:none;">
                                    <img id="editFotoPreview" src="" alt="Foto atual">
                                </div>
                            </div>
                        </div>
                        <div class="rr-comp-modal__field rr-comp-modal__field--full">
                            <label class="form-label">Biografia</label>
                            <textarea name="biografia" id="editCompetitorBio" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn--primary" id="editCompetitorSaveBtn">
                        <i class="las la-save"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
@endsection

@push('style')
<style>
/* ========================================
   COMPETITORS - MODERN CARD GRID LAYOUT
   ======================================== */

:root {
    --comp-primary: #4f46e5;
    --comp-primary-light: #818cf8;
    --comp-success: #10b981;
    --comp-warning: #f59e0b;
    --comp-danger: #ef4444;
    --comp-gray-50: #f9fafb;
    --comp-gray-100: #f3f4f6;
    --comp-gray-200: #e5e7eb;
    --comp-gray-300: #d1d5db;
    --comp-gray-400: #9ca3af;
    --comp-gray-500: #6b7280;
    --comp-gray-600: #4b5563;
    --comp-gray-700: #374151;
    --comp-gray-800: #1f2937;
    --comp-gray-900: #111827;
    --comp-card-bg: #ffffff;
    --comp-card-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
    --comp-card-shadow-hover: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
    --comp-radius: 12px;
    --comp-radius-sm: 8px;
}

/* Header */
.rr-comp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.rr-comp-header__title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--comp-gray-800);
}

.rr-comp-header__title i {
    font-size: 1.5rem;
    color: var(--comp-primary);
}

.rr-comp-header__right {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Search */
.rr-comp-search {
    position: relative;
    display: flex;
    align-items: center;
}

.rr-comp-search i {
    position: absolute;
    left: 12px;
    color: var(--comp-gray-400);
    font-size: 1.1rem;
}

.rr-comp-search__input {
    padding: 10px 12px 10px 38px;
    border: 1px solid var(--comp-gray-200);
    border-radius: var(--comp-radius-sm);
    font-size: 0.875rem;
    width: 220px;
    transition: all 0.2s;
    background: var(--comp-card-bg);
}

.rr-comp-search__input:focus {
    outline: none;
    border-color: var(--comp-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Buttons */
.rr-comp-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: var(--comp-radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.rr-comp-btn--primary {
    background: var(--comp-primary);
    color: white;
}

.rr-comp-btn--primary:hover {
    background: #4338ca;
    color: white;
    transform: translateY(-1px);
}

/* Grid */
.rr-comp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

/* Card */
.rr-comp-card {
    background: var(--comp-card-bg);
    border-radius: var(--comp-radius);
    box-shadow: var(--comp-card-shadow);
    overflow: hidden;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
}

.rr-comp-card:hover {
    box-shadow: var(--comp-card-shadow-hover);
    transform: translateY(-4px);
}

.rr-comp-card__header {
    position: relative;
    padding: 20px 20px 0;
    display: flex;
    justify-content: center;
}

.rr-comp-card__avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--comp-gray-100);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}

.rr-comp-card__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rr-comp-card__avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--comp-primary), var(--comp-primary-light));
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
}

.rr-comp-card__status {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rr-comp-card__status--ativo {
    background: rgba(16, 185, 129, 0.1);
    color: var(--comp-success);
}

.rr-comp-card__status--inativo {
    background: rgba(245, 158, 11, 0.1);
    color: var(--comp-warning);
}

.rr-comp-card__body {
    padding: 16px 20px;
    text-align: center;
    flex: 1;
}

.rr-comp-card__name {
    margin: 0 0 8px;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--comp-gray-800);
}

.rr-comp-card__nivel {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 10px;
}

.rr-comp-card__nivel--favorito {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
}

.rr-comp-card__nivel--elite {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}

.rr-comp-card__nivel--legado,
.rr-comp-card__nivel--ascendente {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
}

.rr-comp-card__nivel--presilha,
.rr-comp-card__nivel--competidor {
    background: var(--comp-gray-100);
    color: var(--comp-gray-600);
}

.rr-comp-card__bio {
    margin: 0;
    font-size: 0.8rem;
    color: var(--comp-gray-500);
    line-height: 1.4;
}

.rr-comp-card__stats {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--comp-gray-100);
}

.rr-comp-card__stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.rr-comp-card__stat-value {
    font-size: 1.1rem;
    font-weight: 700;
}

.rr-comp-card__stat--win .rr-comp-card__stat-value { color: var(--comp-success); }
.rr-comp-card__stat--draw .rr-comp-card__stat-value { color: var(--comp-gray-500); }
.rr-comp-card__stat--loss .rr-comp-card__stat-value { color: var(--comp-danger); }

.rr-comp-card__stat-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--comp-gray-400);
    text-transform: uppercase;
}

.rr-comp-card__footer {
    display: flex;
    border-top: 1px solid var(--comp-gray-100);
}

.rr-comp-card__action {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    color: var(--comp-gray-500);
    text-decoration: none;
    transition: all 0.2s;
    font-size: 1.2rem;
}

.rr-comp-card__action:hover {
    background: var(--comp-gray-50);
    color: var(--comp-gray-700);
}

.rr-comp-card__action--primary:hover {
    background: rgba(79, 70, 229, 0.1);
    color: var(--comp-primary);
}

.rr-comp-card__action + .rr-comp-card__action {
    border-left: 1px solid var(--comp-gray-100);
}

.rr-comp-card__action-form {
    flex: 1;
    display: flex;
    margin: 0;
}

.rr-comp-card__action-form .rr-comp-card__action {
    border-left: 1px solid var(--comp-gray-100);
}

.rr-comp-card__action--danger:hover {
    background: rgba(239, 68, 68, 0.12);
    color: var(--comp-danger);
}

/* Empty State */
.rr-comp-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--comp-gray-500);
}

.rr-comp-empty i {
    font-size: 4rem;
    color: var(--comp-gray-300);
    margin-bottom: 16px;
}

.rr-comp-empty p {
    margin: 0 0 20px;
    font-size: 1.1rem;
}

/* Pagination */
.rr-comp-pagination {
    margin-top: 24px;
    display: flex;
    justify-content: center;
    padding: 1.25rem;
    background: rgba(15, 23, 42, 0.35);
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.15);
}

.rr-comp-pagination .pagination {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 0;
    padding-left: 0;
}

.rr-comp-pagination .page-item .page-link {
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 10px;
    border: 1px solid rgba(148, 163, 184, 0.25);
    background: rgba(30, 41, 59, 0.55);
    color: #e2e8f0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.rr-comp-pagination .page-item .page-link:hover {
    background: rgba(249, 115, 22, 0.2);
    border-color: rgba(249, 115, 22, 0.5);
    color: #fff;
}

.rr-comp-pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #f97316, #ea580c);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 6px 14px rgba(249, 115, 22, 0.35);
}

.rr-comp-pagination .page-item.disabled .page-link {
    opacity: 0.4;
    pointer-events: none;
}

/* Competitor edit modal */
.rr-comp-modal .modal-content {
    border-radius: 18px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    box-shadow: 0 30px 70px rgba(15, 23, 42, 0.55);
    overflow: hidden;
}

.rr-comp-modal .modal-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.95));
}

.rr-comp-modal__title-wrap {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.rr-comp-modal__subtitle {
    font-size: 0.85rem;
    color: rgba(226, 232, 240, 0.75);
}

.rr-comp-modal .modal-body {
    padding: 24px;
    background: rgba(15, 23, 42, 0.6);
}

.rr-comp-modal__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px 20px;
}

.rr-comp-modal__field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.rr-comp-modal__field--full {
    grid-column: 1 / -1;
}

.rr-comp-modal__field--status .form-label,
.rr-comp-modal__field--photo .form-label {
    margin-bottom: 4px;
}

.rr-comp-modal__status {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.rr-comp-modal__status-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.55);
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.rr-comp-modal__status-item .form-check-input {
    margin-top: 0;
}

.rr-comp-modal__photo {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rr-comp-modal__remove {
    margin-top: 0;
}

.rr-comp-modal__preview {
    padding: 10px;
    border-radius: 14px;
    border: 1px dashed rgba(148, 163, 184, 0.3);
    background: rgba(15, 23, 42, 0.5);
    display: inline-flex;
    justify-content: flex-start;
}

.rr-comp-modal__preview img {
    max-width: 130px;
    border-radius: 10px;
    border: 2px solid rgba(249, 115, 22, 0.45);
}

.rr-comp-modal .form-control,
.rr-comp-modal .form-select {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(148, 163, 184, 0.25);
    color: #e2e8f0;
    border-radius: 10px;
}

.rr-comp-modal .form-control:focus,
.rr-comp-modal .form-select:focus {
    border-color: rgba(249, 115, 22, 0.7);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

.rr-comp-modal .modal-footer {
    padding: 16px 24px 22px;
    border-top: 1px solid rgba(148, 163, 184, 0.15);
    background: rgba(15, 23, 42, 0.75);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.rr-comp-modal .btn-close {
    margin-left: auto;
}

/* Modal overlay (bootstrap CSS missing in admin) */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1055;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: auto;
    outline: 0;
    display: none;
}

.modal.show {
    display: block;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 1.75rem auto;
    pointer-events: none;
    max-width: 640px;
}

.modal-dialog.modal-lg {
    max-width: 900px;
}

.modal.fade .modal-dialog {
    transform: translateY(-20px);
    transition: transform 0.2s ease-out;
}

.modal.show .modal-dialog {
    transform: none;
}

.modal-dialog-scrollable {
    height: calc(100% - 3.5rem);
}

.modal-dialog-scrollable .modal-content {
    max-height: 100%;
    overflow: hidden;
}

.modal-dialog-scrollable .modal-body {
    overflow-y: auto;
}

.modal-content {
    pointer-events: auto;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    width: 100%;
    height: 100%;
    background-color: #000;
}

.modal-backdrop.fade {
    opacity: 0;
}

.modal-backdrop.show {
    opacity: 0.6;
}

body.modal-open {
    overflow: hidden;
}

/* Responsive */
@media (max-width: 640px) {
    .rr-comp-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .rr-comp-header__right {
        flex-direction: column;
    }
    
    .rr-comp-search__input {
        width: 100%;
    }
    
    .rr-comp-grid {
        grid-template-columns: 1fr;
    }

    .rr-comp-modal__grid {
        grid-template-columns: 1fr;
    }

    .rr-comp-modal__status {
        grid-template-columns: 1fr;
    }

    .modal-dialog {
        margin: 0.75rem;
    }
}

/* Dark mode support */
.rr-admin-dark .rr-comp-card {
    background: var(--comp-gray-800);
}

.rr-admin-dark .rr-comp-card__name {
    color: var(--comp-gray-100);
}

.rr-admin-dark .rr-comp-card__bio {
    color: var(--comp-gray-400);
}

.rr-admin-dark .rr-comp-card__footer,
.rr-admin-dark .rr-comp-card__stats {
    border-color: var(--comp-gray-700);
}

.rr-admin-dark .rr-comp-card__action:hover {
    background: var(--comp-gray-700);
}

.rr-admin-dark .rr-comp-search__input {
    background: var(--comp-gray-800);
    border-color: var(--comp-gray-700);
    color: var(--comp-gray-100);
}

.rr-admin-dark .rr-comp-header__title {
    color: var(--comp-gray-100);
}
</style>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchCompetitor');
    const cards = document.querySelectorAll('.rr-comp-card');
    const deleteForms = document.querySelectorAll('.rr-comp-card__action-form');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    if (searchInput) {
        searchInput.addEventListener('change', function() {
            this.form && this.form.submit();
        });
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form && this.form.submit();
            }
        });
    }

    function notifyUser(type, message) {
        try {
            if (typeof notify === 'function') {
                notify(type, message);
                return;
            }
        } catch (e) {}
        alert(message);
    }

    const editModalEl = document.getElementById('competitorEditModal');
    const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
    const editForm = document.getElementById('competitorEditForm');
    const editSaveBtn = document.getElementById('editCompetitorSaveBtn');
    const editRemoveFoto = document.getElementById('editRemoveFoto');
    const editDeleteFoto = document.getElementById('editDeleteFoto');
    const editFotoPreviewWrap = document.getElementById('editFotoPreviewWrap');
    const editFotoPreview = document.getElementById('editFotoPreview');

    function setPhotoPreview(url) {
        if (!editFotoPreviewWrap || !editFotoPreview) return;
        if (url) {
            editFotoPreview.src = url;
            editFotoPreviewWrap.style.display = '';
        } else {
            editFotoPreview.src = '';
            editFotoPreviewWrap.style.display = 'none';
        }
    }

    if (editRemoveFoto) {
        editRemoveFoto.addEventListener('change', function() {
            if (editDeleteFoto) editDeleteFoto.value = this.checked ? '1' : '0';
            if (this.checked) setPhotoPreview('');
        });
    }

    document.querySelectorAll('.js-edit-competitor').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = btn.closest('.rr-comp-card');
            if (!card || !editModal) return;

            const id = card.getAttribute('data-id') || '';
            const nome = card.querySelector('.rr-comp-card__name')?.textContent?.trim() || '';
            const nivel = card.getAttribute('data-nivel') || '';
            const status = card.getAttribute('data-status') || 'ativo';
            const bio = card.getAttribute('data-biografia') || '';
            const foto = card.getAttribute('data-foto') || '';
            const action = btn.getAttribute('data-action') || '';

            editForm.setAttribute('data-action', action);
            document.getElementById('editCompetitorId').value = id;
            document.getElementById('editCompetitorNome').value = nome;
            document.getElementById('editCompetitorNivel').value = nivel;
            document.getElementById('editCompetitorBio').value = bio;
            document.getElementById('editStatusAtivo').checked = status === 'ativo';
            document.getElementById('editStatusInativo').checked = status === 'inativo';

            if (editRemoveFoto) editRemoveFoto.checked = false;
            if (editDeleteFoto) editDeleteFoto.value = '0';
            setPhotoPreview(foto);

            editModal.show();
        });
    });

    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const action = editForm.getAttribute('data-action');
            if (!action) return;

            const formData = new FormData(editForm);
            if (editRemoveFoto && editRemoveFoto.checked) {
                formData.set('delete_foto', '1');
            }

            if (editSaveBtn) {
                editSaveBtn.disabled = true;
                editSaveBtn.dataset.originalText = editSaveBtn.innerHTML;
                editSaveBtn.innerHTML = '<i class="las la-spinner la-spin"></i> Salvando...';
            }

            fetch(action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.ok ? res.json() : res.json().catch(() => ({})).then(data => { throw data; }))
            .then(data => {
                if (!data || data.success !== true) {
                    throw data || {};
                }

                const card = document.querySelector(`.rr-comp-card[data-id="${data.competitor.id}"]`);
                if (card) {
                    card.setAttribute('data-status', data.competitor.status);
                    card.setAttribute('data-nivel', data.competitor.nivel);
                    card.setAttribute('data-biografia', data.competitor.biografia || '');
                    card.setAttribute('data-foto', data.competitor.foto_url || '');
                    const nameEl = card.querySelector('.rr-comp-card__name');
                    if (nameEl) nameEl.textContent = data.competitor.nome;
                    const statusEl = card.querySelector('.rr-comp-card__status');
                    if (statusEl) {
                        statusEl.className = `rr-comp-card__status rr-comp-card__status--${data.competitor.status}`;
                        statusEl.textContent = data.competitor.status === 'ativo' ? 'Ativo' : 'Inativo';
                    }
                    const nivelEl = card.querySelector('.rr-comp-card__nivel');
                    if (nivelEl) {
                        nivelEl.className = `rr-comp-card__nivel rr-comp-card__nivel--${data.competitor.nivel}`;
                        nivelEl.innerHTML = data.badge_html || data.competitor.nivel;
                    }
                    const bioEl = card.querySelector('.rr-comp-card__bio');
                    if (bioEl) {
                        if (data.competitor.biografia) {
                            bioEl.textContent = data.competitor.biografia;
                            bioEl.style.display = '';
                        } else {
                            bioEl.textContent = '';
                            bioEl.style.display = 'none';
                        }
                    }
                    const avatarImg = card.querySelector('.rr-comp-card__avatar img');
                    if (avatarImg && data.competitor.foto_url) {
                        avatarImg.src = data.competitor.foto_url;
                    }
                }

                if (editModal) editModal.hide();
                notifyUser('success', data.message || 'Competidor atualizado.');
            })
            .catch(err => {
                const msg = err && err.message ? err.message : 'Erro ao atualizar competidor.';
                notifyUser('error', msg);
            })
            .finally(() => {
                if (editSaveBtn) {
                    editSaveBtn.disabled = false;
                    editSaveBtn.innerHTML = editSaveBtn.dataset.originalText || '<i class="las la-save"></i> Salvar';
                }
            });
        });
    }

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const competitorId = form.getAttribute('data-competitor-id');
            const action = form.getAttribute('data-action');
            if (!action) return;
            if (!confirm('Tem certeza que deseja excluir este competidor?')) return;

            fetch(action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.ok ? res.json() : res.json().catch(() => ({})).then(data => { throw data; }))
            .then(data => {
                if (!data || data.success !== true) {
                    throw data || {};
                }
                const card = form.closest('.rr-comp-card');
                if (card) card.remove();
                notifyUser('success', data.message || 'Competidor excluído.');
            })
            .catch(err => {
                const msg = err && err.message ? err.message : 'Erro ao excluir competidor.';
                notifyUser('error', msg);
            });
        });
    });
});
</script>
@endpush
