@extends('admin.layouts.app')

@section('panel')
    <style>
        .sponsors-page {
            max-width: 100%;
            margin: 0 auto;
        }

        .sponsors-card {
            overflow: hidden;
            border: 1px solid rgba(249, 115, 22, 0.2);
            border-radius: 16px;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .sponsors-header {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.75rem 2rem;
            overflow: hidden;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .sponsors-header::before {
            content: "";
            position: absolute;
            top: -55%;
            right: -5%;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.14) 0%, transparent 70%);
        }

        .sponsors-header h5 {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
            color: #fff;
            font-size: 1.75rem;
            font-weight: 800;
        }

        .sponsors-add-btn,
        .sponsors-filter-btn,
        .sponsors-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border-radius: 8px;
            font-weight: 800;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .sponsors-add-btn {
            position: relative;
            z-index: 1;
            min-height: 46px;
            padding: 0 1.4rem;
            border: 0;
            background: rgba(255, 255, 255, 0.96);
            color: #f97316;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
        }

        .sponsors-add-btn:hover {
            color: #ea580c;
            background: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.26);
        }

        .sponsors-filters {
            padding: 1.5rem 1.75rem;
            border-bottom: 1px solid rgba(249, 115, 22, 0.2);
            background: rgba(30, 41, 59, 0.42);
        }

        .sponsors-filter-form {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) minmax(200px, 280px) auto;
            gap: 1rem;
            align-items: end;
        }

        .sponsors-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #e2e8f0;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .sponsors-label i {
            margin-right: 0.35rem;
            color: #f97316;
        }

        .sponsors-input,
        .sponsors-select {
            width: 100%;
            min-height: 46px;
            padding: 0 1rem;
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.62);
            color: #e2e8f0;
            font-weight: 700;
        }

        .sponsors-input:focus,
        .sponsors-select:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }

        .sponsors-input::placeholder {
            color: #64748b;
        }

        .sponsors-select option {
            background: #1e293b;
            color: #e2e8f0;
        }

        .sponsors-filter-actions {
            display: flex;
            gap: 0.55rem;
        }

        .sponsors-filter-btn {
            min-height: 46px;
            padding: 0 1.2rem;
            border: 0;
        }

        .sponsors-filter-btn.primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .sponsors-filter-btn.secondary {
            background: rgba(71, 85, 105, 0.85);
            color: #e2e8f0;
        }

        .sponsors-table-wrap {
            overflow-x: auto;
        }

        .sponsors-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .sponsors-table thead {
            background: rgba(30, 41, 59, 0.64);
        }

        .sponsors-table th {
            padding: 1rem 1.25rem;
            border-bottom: 2px solid rgba(249, 115, 22, 0.32);
            color: #f97316;
            font-size: 0.86rem;
            font-weight: 900;
            letter-spacing: 0.05em;
            text-align: left;
            text-transform: uppercase;
        }

        .sponsors-table td {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            color: #e2e8f0;
            vertical-align: middle;
        }

        .sponsors-table tbody tr {
            background: rgba(30, 41, 59, 0.3);
        }

        .sponsors-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.52);
        }

        .sponsors-logo {
            width: 72px;
            height: 54px;
            object-fit: contain;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            background: rgba(3, 7, 18, 0.6);
            padding: 6px;
        }

        .sponsors-name {
            display: grid;
            gap: 0.25rem;
        }

        .sponsors-name strong {
            color: #fff7ed;
            font-size: 1rem;
            font-weight: 900;
        }

        .sponsors-name a {
            max-width: 360px;
            overflow: hidden;
            color: #93c5fd;
            font-size: 0.86rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sponsors-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            padding: 0 0.75rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 900;
        }

        .sponsors-badge.active {
            border: 1px solid rgba(34, 197, 94, 0.28);
            background: rgba(22, 163, 74, 0.16);
            color: #86efac;
        }

        .sponsors-badge.inactive {
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(71, 85, 105, 0.28);
            color: #cbd5e1;
        }

        .sponsors-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .sponsors-action-btn {
            min-height: 38px;
            padding: 0 0.9rem;
            border: 2px solid;
            background: transparent;
            cursor: pointer;
            font-size: 0.84rem;
        }

        .sponsors-action-btn.edit {
            border-color: #f97316;
            color: #fb923c;
        }

        .sponsors-action-btn.delete {
            border-color: #ef4444;
            color: #f87171;
        }

        .sponsors-empty {
            padding: 2.25rem;
            color: #94a3b8;
            text-align: center;
            font-weight: 700;
        }

        .sponsors-pagination {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(148, 163, 184, 0.12);
        }

        @media (max-width: 900px) {
            .sponsors-filter-form {
                grid-template-columns: 1fr;
            }

            .sponsors-filter-actions {
                flex-wrap: wrap;
            }
        }
    </style>

    <div class="sponsors-page">
        <div class="sponsors-card">
            <div class="sponsors-header">
                <h5><i class="las la-handshake"></i> Patrocinadores</h5>
                <a href="{{ route('admin.sponsors.create') }}" class="sponsors-add-btn">
                    <i class="las la-plus-circle"></i>
                    Novo patrocinador
                </a>
            </div>

            <div class="sponsors-filters">
                <form method="GET" action="{{ route('admin.sponsors.index') }}" class="sponsors-filter-form">
                    <div>
                        <label class="sponsors-label" for="sponsor-search"><i class="las la-search"></i> Buscar</label>
                        <input class="sponsors-input" id="sponsor-search" type="text" name="q" value="{{ $search }}" placeholder="Nome ou link do patrocinador...">
                    </div>
                    <div>
                        <label class="sponsors-label" for="sponsor-status"><i class="las la-toggle-on"></i> Status</label>
                        <select class="sponsors-select" id="sponsor-status" name="status">
                            <option value="">Todos os status</option>
                            <option value="active" @selected($status === 'active')>Ativos</option>
                            <option value="inactive" @selected($status === 'inactive')>Inativos</option>
                        </select>
                    </div>
                    <div class="sponsors-filter-actions">
                        <button class="sponsors-filter-btn primary" type="submit"><i class="las la-filter"></i> Filtrar</button>
                        <a class="sponsors-filter-btn secondary" href="{{ route('admin.sponsors.index') }}"><i class="las la-undo"></i> Limpar</a>
                    </div>
                </form>
            </div>

            <div class="sponsors-table-wrap">
                <table class="sponsors-table">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Patrocinador</th>
                            <th>Status</th>
                            <th>Ordem</th>
                            <th>Atualizado</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sponsors as $sponsor)
                            <tr>
                                <td>
                                    <img class="sponsors-logo" src="{{ asset('storage/' . $sponsor->logo) }}" alt="Logo {{ $sponsor->name }}">
                                </td>
                                <td>
                                    <div class="sponsors-name">
                                        <strong>{{ $sponsor->name }}</strong>
                                        <a href="{{ $sponsor->url }}" target="_blank" rel="noopener">{{ $sponsor->url }}</a>
                                    </div>
                                </td>
                                <td>
                                    <span class="sponsors-badge {{ $sponsor->is_active ? 'active' : 'inactive' }}">
                                        {{ $sponsor->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ $sponsor->sort_order }}</td>
                                <td>{{ optional($sponsor->updated_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="sponsors-actions">
                                        <a href="{{ route('admin.sponsors.edit', $sponsor->id) }}" class="sponsors-action-btn edit">
                                            <i class="las la-edit"></i> Editar
                                        </a>
                                        <form method="POST" action="{{ route('admin.sponsors.destroy', $sponsor->id) }}" onsubmit="return confirm('Tem certeza que deseja excluir este patrocinador?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="sponsors-action-btn delete">
                                                <i class="las la-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sponsors-empty">Nenhum patrocinador cadastrado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sponsors->hasPages())
                <div class="sponsors-pagination">
                    {{ $sponsors->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
