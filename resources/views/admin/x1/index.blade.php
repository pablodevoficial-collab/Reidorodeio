@extends('admin.layouts.app')

@section('panel')
    <style>
        .x1-index-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .x1-index-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(249, 115, 22, 0.2);
            overflow: hidden;
        }
        
        .x1-index-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 1.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .x1-index-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .x1-index-header h5 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }
        
        .x1-index-header h5 i {
            font-size: 1.6rem;
        }
        
        .x1-filters-section {
            background: rgba(30, 41, 59, 0.4);
            padding: 1.75rem;
            border-bottom: 1px solid rgba(249, 115, 22, 0.2);
        }
        
        .x1-filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .x1-filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .x1-filter-item.search {
            flex: 2;
            min-width: 280px;
        }
        
        .x1-filter-label {
            display: block;
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .x1-filter-label i {
            color: #f97316;
            margin-right: 0.35rem;
        }
        
        .x1-filter-input,
        .x1-filter-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .x1-filter-input:focus,
        .x1-filter-select:focus {
            outline: none;
            border-color: #f97316;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        
        .x1-filter-input::placeholder {
            color: #64748b;
        }
        
        .x1-filter-select option {
            background: #1e293b;
            color: #e2e8f0;
        }
        
        .x1-filter-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .x1-filter-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .x1-filter-btn.primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        
        .x1-filter-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.4);
        }
        
        .x1-filter-btn.secondary {
            background: rgba(71, 85, 105, 0.8);
            color: #e2e8f0;
        }
        
        .x1-filter-btn.secondary:hover {
            background: rgba(71, 85, 105, 1);
        }
        
        .x1-table-wrapper {
            overflow-x: auto;
        }
        
        .x1-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .x1-table thead {
            background: rgba(30, 41, 59, 0.6);
        }
        
        .x1-table thead th {
            padding: 1rem 1.25rem;
            color: #f97316;
            font-weight: 700;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid rgba(249, 115, 22, 0.3);
        }
        
        .x1-table tbody tr {
            background: rgba(30, 41, 59, 0.3);
            transition: all 0.3s ease;
        }
        
        .x1-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.5);
            box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.3);
        }
        
        .x1-table tbody td {
            padding: 1.25rem 1.25rem;
            color: #e2e8f0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 0.95rem;
        }
        
        .x1-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .x1-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }
        
        .x1-badge.status-open {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }
        
        .x1-badge.status-pending {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #1e293b;
        }
        
        .x1-badge.status-in_progress {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
        }

        .x1-badge.status-completed {
            background: rgba(71, 85, 105, 0.6);
            color: #e2e8f0;
        }
        
        .x1-badge.status-cancelled {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
        }
        
        .x1-action-btn {
            padding: 0.5rem 1rem;
            border: 2px solid;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
            background: transparent;
        }
        
        .x1-action-btn.view {
            border-color: #f97316;
            color: #f97316;
        }
        
        .x1-action-btn.view:hover {
            background: #f97316;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .x1-action-btn.close-room {
            border-color: #ef4444;
            color: #ef4444;
        }
        
        .x1-action-btn.close-room:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .x1-action-btn.delete-room {
            border-color: #dc2626;
            color: #dc2626;
        }
        
        .x1-action-btn.delete-room:hover {
            background: #dc2626;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .x1-empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #94a3b8;
        }
        
        .x1-empty-state i {
            font-size: 4rem;
            color: rgba(249, 115, 22, 0.3);
            margin-bottom: 1rem;
        }
        
        .x1-empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }
        
        .x1-pagination {
            padding: 2rem;
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid rgba(249, 115, 22, 0.2);
        }
        
        @media (max-width: 768px) {
            .x1-index-header {
                padding: 1.25rem 1.5rem;
            }
            
            .x1-index-header h5 {
                font-size: 1.35rem;
            }
            
            .x1-filters-section {
                padding: 1.25rem;
            }
            
            .x1-filter-item {
                min-width: 100%;
            }
            
            .x1-table thead th,
            .x1-table tbody td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
            
            .x1-filter-actions {
                flex-direction: column;
            }
            
            .x1-filter-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="x1-index-wrapper">
        <div class="x1-index-card">
            <div class="x1-index-header">
                <h5><i class="las la-trophy"></i> @lang('Salas X1')</h5>
            </div>

            <div class="x1-filters-section">
                <form action="{{ route('admin.x1.index') }}" method="GET">
                    <div class="x1-filter-group">
                        <div class="x1-filter-item search">
                            <label class="x1-filter-label">
                                <i class="las la-search"></i> @lang('Buscar')
                            </label>
                            <input type="text" name="search" class="x1-filter-input" placeholder="ID ou Nome da Sala" value="{{ request('search') }}">
                        </div>

                        <div class="x1-filter-item">
                            <label class="x1-filter-label">
                                <i class="las la-tag"></i> @lang('Status')
                            </label>
                            <select name="status" class="x1-filter-select">
                                <option value="">Todos</option>
                                <option value="pending_payment" {{ request('status') == 'pending_payment' ? 'selected' : '' }}>Pendente Pagamento</option>
                                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aguardando Oponente</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Em Progresso</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Finalizada</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Fechada</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>

                        <div class="x1-filter-item">
                            <div class="x1-filter-actions">
                                <button type="submit" class="x1-filter-btn primary">
                                    <i class="las la-filter"></i> @lang('Filtrar')
                                </button>
                                <a href="{{ route('admin.x1.index') }}" class="x1-filter-btn secondary">
                                    <i class="las la-redo-alt"></i> @lang('Limpar')
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="x1-table-wrapper">
                <table class="x1-table">
                    <thead>
                        <tr>
                            <th>@lang('ID')</th>
                            <th>@lang('Nome')</th>
                            <th>@lang('Criador')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Ações')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                            <tr>
                                <td>
                                    <span style="color: #94a3b8; font-weight: bold;">#{{ $room->id }}</span>
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: #fff;">{{ $room->name }}</span>
                                </td>
                                <td>
                                    @php
                                        // Fallback: Tenta pegar o user via relação host, senão via participante host
                                        // Prioriza username, se não tiver, usa name
                                        $hostParticipant = $room->participants->where('is_host', true)->first();
                                        $hostName = optional($room->host)->username
                                            ?? optional($room->host)->name
                                            ?? optional(optional($hostParticipant)->user)->username 
                                            ?? optional(optional($hostParticipant)->user)->name
                                            ?? 'N/A';
                                    @endphp
                                    <span style="color: #e2e8f0;">{{ $hostName }}</span>
                                </td>
                                <td>
                                    @php
                                        // Normalizar status para evitar erros
                                        $rawStatus = trim((string) $room->status);
                                        
                                        $statusClass = match($rawStatus) {
                                            'open' => 'status-open',
                                            'pending' => 'status-pending',
                                            'pending_payment' => 'status-pending',
                                            'in_progress' => 'status-in_progress',
                                            'completed' => 'status-completed',
                                            'closed' => 'status-completed',
                                            'cancelled' => 'status-cancelled',
                                            default => 'status-completed' // Fallback seguro
                                        };
                                        
                                        $statusLabel = match($rawStatus) {
                                            'open' => 'Aguardando Oponente',
                                            'pending' => 'Pagamento Pendente',
                                            'pending_payment' => 'Pagamento Pendente',
                                            'in_progress' => 'Sala Ao Vivo',
                                            'completed' => 'Finalizada',
                                            'closed' => 'Fechada',
                                            'cancelled' => 'Cancelada',
                                            default => $rawStatus ?: 'Desconhecido'
                                        };
                                    @endphp
                                    <span class="x1-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="{{ route('admin.x1.show', $room->id) }}" class="x1-action-btn view">
                                            <i class="las la-eye"></i> Ver
                                        </a>
                                        @if($room->status === 'open')
                                            <form method="POST" action="{{ route('admin.x1.close', $room->id) }}" style="display:inline" onsubmit="return confirm('Tem certeza que deseja encerrar esta sala?');">
                                                @csrf
                                                <button class="x1-action-btn close-room">
                                                    <i class="las la-times-circle"></i> Encerrar
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.x1.destroy', $room->id) }}" style="display:inline" onsubmit="return confirm('⚠️ ATENÇÃO: Isso vai EXCLUIR PERMANENTEMENTE a sala #{{ $room->id }} e todos os dados relacionados. Essa ação NÃO pode ser desfeita. Continuar?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="x1-action-btn delete-room">
                                                <i class="las la-trash-alt"></i> Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="x1-empty-state">
                                        <i class="las la-inbox"></i>
                                        <p>@lang('Nenhuma sala X1 encontrada')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($rooms->hasPages())
                <div class="x1-pagination">
                    {{ $rooms->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.dashboard') }}" />
@endpush
