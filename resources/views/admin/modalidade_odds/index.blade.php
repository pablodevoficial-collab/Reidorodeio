@extends('admin.layouts.app')

@section('panel')
<div class="row gy-3">
    <div class="col-12">
        <div class="card b-radius--10">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Rodeio</label>
                        <select name="rodeio_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($rodeios as $rodeio)
                                <option value="{{ $rodeio->id }}" @selected((string) request('rodeio_id') === (string) $rodeio->id)>
                                    {{ $rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? ('Rodeio #' . $rodeio->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Modalidade</label>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Buscar por nome da modalidade">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn--primary w-100">
                            <i class="las la-search"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.modalidade_odds.index') }}" class="btn btn--dark w-100">
                            <i class="las la-sync"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two mb-0">
                        <thead>
                            <tr>
                                <th>Rodeio</th>
                                <th>Modalidade</th>
                                <th>Caixa Pago</th>
                                <th>Lucro Casa</th>
                                <th>Margem</th>
                                <th>Gate Caixa</th>
                                <th>Boost</th>
                                <th>Comp. baixa demanda</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modalidades as $row)
                                @php
                                    $m = $row->modalidade;
                                    $s = $row->settings;
                                    $f = $row->finance;
                                    $gateReached = (float) ($f['paid_volume'] ?? 0) >= (float) ($s['bankroll_gate_amount'] ?? 0);
                                @endphp
                                <tr>
                                    <td>
                                        {{ $m->rodeio?->nome ?? $m->rodeio?->titulo ?? $m->rodeio?->name ?? '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $m->nome }}</strong>
                                        <div class="small text-muted">#{{ $m->id }}</div>
                                    </td>
                                    <td>R$ {{ number_format((float) ($f['paid_volume'] ?? 0), 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format((float) ($f['house_fee'] ?? 0), 2, ',', '.') }}</td>
                                    <td>{{ number_format((float) ($f['margin_percent'] ?? 0), 2, ',', '.') }}%</td>
                                    <td>
                                        @if($gateReached)
                                            <span class="badge bg-success">Atingido</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Aguardando</span>
                                        @endif
                                        <div class="small text-muted">
                                            meta: R$ {{ number_format((float) ($s['bankroll_gate_amount'] ?? 0), 2, ',', '.') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($row->boost_available)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                        <div class="small text-muted">
                                            min margem: {{ number_format(max(30, (float) ($s['min_house_margin_percent'] ?? 0)), 2, ',', '.') }}%
                                        </div>
                                    </td>
                                    <td>{{ (int) $row->low_volume_competitors }}</td>
                                    <td>
                                        <a href="{{ route('admin.modalidade_odds.edit', $m->id) }}" class="btn btn-sm btn--primary">
                                            <i class="las la-cog"></i> Configurar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Nenhuma modalidade encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($modalidades->hasPages())
                <div class="card-footer">
                    {{ $modalidades->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
