@extends('admin.layouts.app')

@section('panel')
    <style>
        .rodeios-index-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .rodeios-index-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(249, 115, 22, 0.2);
            overflow: hidden;
        }
        
        .rodeios-index-header {
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
        
        .rodeios-index-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .rodeios-index-header h5 {
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
        
        .rodeios-index-header h5 i {
            font-size: 1.6rem;
        }
        
        .rodeios-add-btn {
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.95);
            color: #f97316;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }
        
        .rodeios-add-btn:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            color: #ea580c;
        }
        
        .rodeios-add-btn i {
            font-size: 1.2rem;
        }
        
        .rodeios-filters-section {
            background: rgba(30, 41, 59, 0.4);
            padding: 1.75rem;
            border-bottom: 1px solid rgba(249, 115, 22, 0.2);
        }
        
        .rodeios-filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .rodeios-filter-item {
            flex: 1;
            min-width: 200px;
        }
        
        .rodeios-filter-item.search {
            flex: 2;
            min-width: 280px;
        }
        
        .rodeios-filter-label {
            display: block;
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .rodeios-filter-label i {
            color: #f97316;
            margin-right: 0.35rem;
        }
        
        .rodeios-filter-input,
        .rodeios-filter-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .rodeios-filter-input:focus,
        .rodeios-filter-select:focus {
            outline: none;
            border-color: #f97316;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        
        .rodeios-filter-input::placeholder {
            color: #64748b;
        }
        
        .rodeios-filter-select option {
            background: #1e293b;
            color: #e2e8f0;
        }
        
        .rodeios-filter-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .rodeios-filter-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .rodeios-filter-btn.primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
        
        .rodeios-filter-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.4);
        }
        
        .rodeios-filter-btn.secondary {
            background: rgba(71, 85, 105, 0.8);
            color: #e2e8f0;
        }
        
        .rodeios-filter-btn.secondary:hover {
            background: rgba(71, 85, 105, 1);
        }
        
        .rodeios-table-wrapper {
            overflow-x: auto;
        }
        
        .rodeios-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .rodeios-table thead {
            background: rgba(30, 41, 59, 0.6);
        }
        
        .rodeios-table thead th {
            padding: 1rem 1.25rem;
            color: #f97316;
            font-weight: 700;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid rgba(249, 115, 22, 0.3);
        }
        
        .rodeios-table tbody tr {
            background: rgba(30, 41, 59, 0.3);
            transition: all 0.3s ease;
        }
        
        .rodeios-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.5);
            box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.3);
        }
        
        .rodeios-table tbody td {
            padding: 1.25rem 1.25rem;
            color: #e2e8f0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 0.95rem;
        }
        
        .rodeios-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .rodeios-name {
            font-weight: 700;
            color: #fff;
            font-size: 1rem;
        }
        
        .rodeios-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }
        
        .rodeios-badge.ativo {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }
        
        .rodeios-badge.inativo {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
        }
        
        .rodeios-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .rodeios-action-btn {
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
        
        .rodeios-action-btn.edit {
            border-color: #f97316;
            color: #f97316;
        }
        
        .rodeios-action-btn.edit:hover {
            background: #f97316;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .rodeios-action-btn.delete {
            border-color: #ef4444;
            color: #ef4444;
        }
        
        .rodeios-action-btn.delete:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .rodeios-empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #94a3b8;
        }
        
        .rodeios-empty-state i {
            font-size: 4rem;
            color: rgba(249, 115, 22, 0.3);
            margin-bottom: 1rem;
        }
        
        .rodeios-empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }
        
        .rodeios-pagination {
            padding: 2rem;
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid rgba(249, 115, 22, 0.2);
        }
        
        @media (max-width: 768px) {
            .rodeios-index-header {
                padding: 1.25rem 1.5rem;
            }
            
            .rodeios-index-header h5 {
                font-size: 1.35rem;
            }
            
            .rodeios-filters-section {
                padding: 1.25rem;
            }
            
            .rodeios-filter-item {
                min-width: 100%;
            }
            
            .rodeios-table thead th,
            .rodeios-table tbody td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
            
            .rodeios-actions {
                flex-direction: column;
            }
            
            .rodeios-action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="rodeios-index-wrapper">
        <div class="rodeios-index-card">
            <div class="rodeios-index-header">
                <h5><i class="las la-calendar-check"></i> @lang('Rodeios')</h5>
                <a href="{{ route('admin.rodeios.create') }}" class="rodeios-add-btn">
                    <i class="las la-plus-circle"></i> @lang('Novo Rodeio')
                </a>
            </div>

            <div class="rodeios-filters-section">
                <form method="get">
                    <div class="rodeios-filter-group">
                        <div class="rodeios-filter-item search">
                            <label class="rodeios-filter-label">
                                <i class="las la-search"></i> @lang('Buscar')
                            </label>
                            <input type="search" name="q" value="{{ request('q') }}" class="rodeios-filter-input" placeholder="@lang('Digite o nome do rodeio ou cidade...')">
                        </div>

                        <div class="rodeios-filter-item">
                            <label class="rodeios-filter-label">
                                <i class="las la-power-off"></i> @lang('Status')
                            </label>
                            <select name="status" class="rodeios-filter-select">
                                <option value="">@lang('Todos os status')</option>
                                <option value="ativo" @selected(request('status') === 'ativo')>@lang('Ativos')</option>
                                <option value="inativo" @selected(request('status') === 'inativo')>@lang('Inativos')</option>
                            </select>
                        </div>

                        <div class="rodeios-filter-item">
                            <div class="rodeios-filter-actions">
                                <button type="submit" class="rodeios-filter-btn primary">
                                    <i class="las la-filter"></i> @lang('Filtrar')
                                </button>
                                <a href="{{ route('admin.rodeios.index') }}" class="rodeios-filter-btn secondary">
                                    <i class="las la-redo-alt"></i> @lang('Limpar')
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="rodeios-table-wrapper">
                <table class="rodeios-table">
                    <thead>
                        <tr>
                            <th>@lang('Rodeio')</th>
                            <th>@lang('Local')</th>
                            <th>@lang('Datas')</th>
                            <th>@lang('Modalidades')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Ações')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rodeios as $rodeio)
                            @php
                                $startDate = $rodeio->start ? \Carbon\Carbon::parse($rodeio->start)->format('d/m/Y') : '-';
                                $endDate = $rodeio->end ? \Carbon\Carbon::parse($rodeio->end)->format('d/m/Y') : '-';
                            @endphp
                            <tr>
                                <td>
                                    <span class="rodeios-name">{{ $rodeio->name }}</span>
                                </td>
                                <td>
                                    <span style="color: #94a3b8;"><i class="las la-map-marker-alt"></i> {{ $rodeio->info['cidade'] ?? '-' }}</span>
                                </td>
                                <td>
                                    <div style="color: #e2e8f0;">{{ $startDate }}</div>
                                    <div style="color: #94a3b8; font-size: 0.85rem;">{{ $endDate }}</div>
                                </td>
                                <td>
                                    @if($rodeio->modalidades && count($rodeio->modalidades))
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                                            @foreach($rodeio->modalidades as $mod)
                                                <span style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 0.25rem 0.6rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">{{ $mod->nome }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: #64748b;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="rodeios-badge {{ $rodeio->status }}">
                                        @if($rodeio->status == 'ativo') ✓ @else ✕ @endif {{ ucfirst($rodeio->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="rodeios-actions">
                                        <a href="{{ route('admin.rodeios.edit', $rodeio->id) }}" class="rodeios-action-btn edit">
                                            <i class="las la-edit"></i> Editar
                                        </a>

                                        <form method="POST" action="{{ route('admin.rodeios.destroy', $rodeio->id) }}" onsubmit="return confirm('Tem certeza que deseja excluir este rodeio?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rodeios-action-btn delete">
                                                <i class="las la-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="rodeios-empty-state">
                                        <i class="las la-inbox"></i>
                                        <p>@lang('Nenhum rodeio encontrado')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($rodeios->hasPages())
                <div class="rodeios-pagination">
                    {{ paginateLinks($rodeios) }}
                </div>
            @endif
        </div>
    </div>

@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.dashboard') }}" />
@endpush
