@extends('admin.layouts.app')

@section('panel')
    @include('admin.notification.top_bar')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Rodeios com alerta de início</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table--light style--two mb-0">
                    <thead>
                        <tr>
                            <th>Rodeio</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Alertas ativos</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rodeios as $rodeio)
                            @php
                                $rodeioTitle = trim((string) ($rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? 'Rodeio sem nome'));
                                $statusLabel = $rodeio->status_transmissao ? str_replace('_', ' ', (string) $rodeio->status_transmissao) : 'sem status';
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $rodeioTitle }}</div>
                                    <div class="small text-muted">#{{ $rodeio->id }}</div>
                                </td>
                                <td>{{ $rodeio->start ? showDateTime($rodeio->start, 'd/m/Y H:i') : 'Não definida' }}</td>
                                <td>
                                    <span class="badge badge--dark">{{ ucfirst($statusLabel) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge--primary">{{ (int) $rodeio->email_reminders_count }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.setting.notification.rodeio.reminders.show', $rodeio->id) }}" class="btn btn-sm btn-outline--primary">
                                        <i class="las la-eye"></i> Ver clientes
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhum rodeio encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($rodeios->hasPages())
            <div class="card-footer">
                {{ paginateLinks($rodeios) }}
            </div>
        @endif
    </div>
@endsection
