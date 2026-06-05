@extends('admin.layouts.app')
@section('panel')
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')

<div class="rr-comp-detail">
    {{-- Header Card --}}
    <div class="rr-comp-detail__header-card">
        <div class="rr-comp-detail__avatar-section">
            <div class="rr-comp-detail__avatar">
                @if($competitor->foto)
                    <img src="{{ $competitor->foto_url }}" alt="{{ $competitor->nome }}">
                @else
                    <div class="rr-comp-detail__avatar-placeholder">
                        {{ strtoupper(substr($competitor->nome, 0, 2)) }}
                    </div>
                @endif
            </div>
            <div class="rr-comp-detail__status rr-comp-detail__status--{{ $competitor->status }}">
                <i class="las la-{{ $competitor->status == 'ativo' ? 'check-circle' : 'pause-circle' }}"></i>
                {{ $competitor->status == 'ativo' ? 'Ativo' : 'Inativo' }}
            </div>
        </div>
        
        <div class="rr-comp-detail__info">
            <h2 class="rr-comp-detail__name">{{ $competitor->nome }}</h2>
            
            <div class="rr-comp-detail__nivel rr-comp-detail__nivel--{{ $competitor->nivel }}">
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
                        {{ ucfirst($competitor->nivel ?? 'N/A') }}
                @endswitch
            </div>
            
            @if($competitor->biografia)
                <p class="rr-comp-detail__bio">{{ $competitor->biografia }}</p>
            @endif
        </div>
        
        <div class="rr-comp-detail__actions">
            <a href="{{ route('admin.competitors.edit', $competitor->id) }}" class="rr-comp-btn rr-comp-btn--primary">
                <i class="las la-pen"></i> Editar
            </a>
            <a href="{{ route('admin.competitors.index') }}" class="rr-comp-btn rr-comp-btn--secondary">
                <i class="las la-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
    
    {{-- Stats Cards --}}
    @if($competitor->stats)
    <div class="rr-comp-detail__stats-grid">
        <div class="rr-comp-detail__stat-card rr-comp-detail__stat-card--win">
            <div class="rr-comp-detail__stat-icon">
                <i class="las la-trophy"></i>
            </div>
            <div class="rr-comp-detail__stat-content">
                <span class="rr-comp-detail__stat-value">{{ $competitor->stats->vitorias ?? 0 }}</span>
                <span class="rr-comp-detail__stat-label">Vitórias</span>
            </div>
        </div>
        
        <div class="rr-comp-detail__stat-card rr-comp-detail__stat-card--draw">
            <div class="rr-comp-detail__stat-icon">
                <i class="las la-handshake"></i>
            </div>
            <div class="rr-comp-detail__stat-content">
                <span class="rr-comp-detail__stat-value">{{ $competitor->stats->empates ?? 0 }}</span>
                <span class="rr-comp-detail__stat-label">Empates</span>
            </div>
        </div>
        
        <div class="rr-comp-detail__stat-card rr-comp-detail__stat-card--loss">
            <div class="rr-comp-detail__stat-icon">
                <i class="las la-times-circle"></i>
            </div>
            <div class="rr-comp-detail__stat-content">
                <span class="rr-comp-detail__stat-value">{{ $competitor->stats->derrotas ?? 0 }}</span>
                <span class="rr-comp-detail__stat-label">Derrotas</span>
            </div>
        </div>
        
        <div class="rr-comp-detail__stat-card rr-comp-detail__stat-card--avg">
            <div class="rr-comp-detail__stat-icon">
                <i class="las la-chart-line"></i>
            </div>
            <div class="rr-comp-detail__stat-content">
                <span class="rr-comp-detail__stat-value">{{ number_format($competitor->aproveitamento, 1) }}%</span>
                <span class="rr-comp-detail__stat-label">Aproveitamento</span>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Informações Adicionais --}}
    <div class="rr-comp-detail__info-cards">
        <div class="rr-comp-detail__info-card">
            <h4 class="rr-comp-detail__info-card-title">
                <i class="las la-info-circle"></i> Informações Gerais
            </h4>
            <div class="rr-comp-detail__info-list">
                <div class="rr-comp-detail__info-item">
                    <span class="rr-comp-detail__info-label">ID</span>
                    <span class="rr-comp-detail__info-value">#{{ $competitor->id }}</span>
                </div>
                <div class="rr-comp-detail__info-item">
                    <span class="rr-comp-detail__info-label">Cadastrado em</span>
                    <span class="rr-comp-detail__info-value">{{ $competitor->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="rr-comp-detail__info-item">
                    <span class="rr-comp-detail__info-label">Última atualização</span>
                    <span class="rr-comp-detail__info-value">{{ $competitor->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
        
        @if($competitor->stats && $competitor->stats->pontuacao_media)
        <div class="rr-comp-detail__info-card">
            <h4 class="rr-comp-detail__info-card-title">
                <i class="las la-chart-bar"></i> Pontuação
            </h4>
            <div class="rr-comp-detail__info-list">
                <div class="rr-comp-detail__info-item">
                    <span class="rr-comp-detail__info-label">Pontuação Média</span>
                    <span class="rr-comp-detail__info-value rr-comp-detail__info-value--highlight">
                        {{ number_format($competitor->stats->pontuacao_media, 2) }}
                    </span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

</div>
@endsection

@push('style')
<style>
/* ========================================
   COMPETITOR DETAIL - MODERN LAYOUT
   ======================================== */

:root {
    --comp-primary: #4f46e5;
    --comp-primary-light: #818cf8;
    --comp-success: #10b981;
    --comp-warning: #f59e0b;
    --comp-danger: #ef4444;
    --comp-info: #3b82f6;
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
    --comp-radius: 16px;
    --comp-radius-sm: 12px;
}

.rr-comp-detail {
    max-width: 900px;
    margin: 0 auto;
}

/* Header Card */
.rr-comp-detail__header-card {
    background: var(--comp-card-bg);
    border-radius: var(--comp-radius);
    box-shadow: var(--comp-card-shadow);
    padding: 32px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 24px;
    align-items: start;
    margin-bottom: 24px;
}

.rr-comp-detail__avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.rr-comp-detail__avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--comp-gray-100);
    box-shadow: 0 8px 16px -4px rgba(0,0,0,0.15);
}

.rr-comp-detail__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rr-comp-detail__avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--comp-primary), var(--comp-primary-light));
    color: white;
    font-size: 2.5rem;
    font-weight: 700;
}

.rr-comp-detail__status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.rr-comp-detail__status--ativo {
    background: rgba(16, 185, 129, 0.1);
    color: var(--comp-success);
}

.rr-comp-detail__status--inativo {
    background: rgba(245, 158, 11, 0.1);
    color: var(--comp-warning);
}

.rr-comp-detail__info {
    padding-top: 8px;
}

.rr-comp-detail__name {
    margin: 0 0 12px;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--comp-gray-800);
}

.rr-comp-detail__nivel {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 16px;
}

.rr-comp-detail__nivel--favorito {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e;
}

.rr-comp-detail__nivel--elite {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}

.rr-comp-detail__nivel--legado,
.rr-comp-detail__nivel--ascendente {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
}

.rr-comp-detail__nivel--presilha,
.rr-comp-detail__nivel--competidor {
    background: var(--comp-gray-100);
    color: var(--comp-gray-600);
}

.rr-comp-detail__bio {
    margin: 0;
    font-size: 0.95rem;
    color: var(--comp-gray-600);
    line-height: 1.6;
}

.rr-comp-detail__actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Buttons */
.rr-comp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: var(--comp-radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
    min-width: 120px;
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

.rr-comp-btn--secondary {
    background: var(--comp-gray-100);
    color: var(--comp-gray-700);
}

.rr-comp-btn--secondary:hover {
    background: var(--comp-gray-200);
    color: var(--comp-gray-800);
}

/* Stats Grid */
.rr-comp-detail__stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.rr-comp-detail__stat-card {
    background: var(--comp-card-bg);
    border-radius: var(--comp-radius-sm);
    box-shadow: var(--comp-card-shadow);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.rr-comp-detail__stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.rr-comp-detail__stat-card--win .rr-comp-detail__stat-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--comp-success);
}

.rr-comp-detail__stat-card--draw .rr-comp-detail__stat-icon {
    background: rgba(107, 114, 128, 0.1);
    color: var(--comp-gray-500);
}

.rr-comp-detail__stat-card--loss .rr-comp-detail__stat-icon {
    background: rgba(239, 68, 68, 0.1);
    color: var(--comp-danger);
}

.rr-comp-detail__stat-card--avg .rr-comp-detail__stat-icon {
    background: rgba(59, 130, 246, 0.1);
    color: var(--comp-info);
}

.rr-comp-detail__stat-content {
    display: flex;
    flex-direction: column;
}

.rr-comp-detail__stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--comp-gray-800);
    line-height: 1.2;
}

.rr-comp-detail__stat-label {
    font-size: 0.8rem;
    color: var(--comp-gray-500);
    font-weight: 500;
}

/* Info Cards */
.rr-comp-detail__info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}

.rr-comp-detail__info-card {
    background: var(--comp-card-bg);
    border-radius: var(--comp-radius-sm);
    box-shadow: var(--comp-card-shadow);
    padding: 20px;
}

.rr-comp-detail__info-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 16px;
    font-size: 1rem;
    font-weight: 600;
    color: var(--comp-gray-700);
}

.rr-comp-detail__info-card-title i {
    color: var(--comp-primary);
}

.rr-comp-detail__info-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rr-comp-detail__info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--comp-gray-100);
}

.rr-comp-detail__info-item:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

.rr-comp-detail__info-label {
    font-size: 0.85rem;
    color: var(--comp-gray-500);
}

.rr-comp-detail__info-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--comp-gray-700);
}

.rr-comp-detail__info-value--highlight {
    color: var(--comp-primary);
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .rr-comp-detail__header-card {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .rr-comp-detail__actions {
        flex-direction: row;
        justify-content: center;
    }
    
    .rr-comp-detail__stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .rr-comp-detail__stats-grid {
        grid-template-columns: 1fr;
    }
    
    .rr-comp-detail__actions {
        flex-direction: column;
    }
}

/* Dark mode */
.rr-admin-dark .rr-comp-detail__header-card,
.rr-admin-dark .rr-comp-detail__stat-card,
.rr-admin-dark .rr-comp-detail__info-card {
    background: var(--comp-gray-800);
}

.rr-admin-dark .rr-comp-detail__name,
.rr-admin-dark .rr-comp-detail__stat-value {
    color: var(--comp-gray-100);
}

.rr-admin-dark .rr-comp-detail__bio,
.rr-admin-dark .rr-comp-detail__info-value {
    color: var(--comp-gray-300);
}

.rr-admin-dark .rr-comp-detail__info-card-title {
    color: var(--comp-gray-200);
}

.rr-admin-dark .rr-comp-detail__info-item {
    border-color: var(--comp-gray-700);
}

.rr-admin-dark .rr-comp-btn--secondary {
    background: var(--comp-gray-700);
    color: var(--comp-gray-200);
}
</style>
@endpush
