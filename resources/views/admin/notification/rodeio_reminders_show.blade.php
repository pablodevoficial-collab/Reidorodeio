@extends('admin.layouts.app')

@section('panel')
    @include('admin.notification.top_bar')

    @php
        $rodeioTitle = trim((string) ($rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? 'Rodeio sem nome'));
    @endphp

    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ $rodeioTitle }}</h5>
                <div class="small text-muted">
                    Rodeio #{{ $rodeio->id }}
                    @if ($rodeio->start)
                        • {{ showDateTime($rodeio->start, 'd/m/Y H:i') }}
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge badge--primary">{{ (int) $rodeio->email_reminders_count }} alertas</span>
                <a href="{{ route('admin.setting.notification.rodeio.reminders.index') }}" class="btn btn-sm btn-outline--secondary">
                    <i class="las la-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Clientes que ativaram notificação de início</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table--light style--two mb-0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>E-mail</th>
                            <th>Usuário vinculado</th>
                            <th>Confirmação</th>
                            <th>Início enviado</th>
                            <th>Ativado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reminders as $reminder)
                            @php
                                $user = $reminder->user;
                                $displayName = trim((string) ($reminder->name ?: ($user?->fullname ?: $user?->username ?: 'Cliente sem nome')));
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $displayName }}</div>
                                    <div class="small text-muted">#{{ $reminder->id }}</div>
                                </td>
                                <td>{{ $reminder->email ?: 'Sem e-mail' }}</td>
                                <td>
                                    @if ($user)
                                        <div class="fw-bold">{{ $user->username }}</div>
                                        <div class="small text-muted">ID {{ $user->id }}</div>
                                    @else
                                        <span class="text-muted">Sem vínculo</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($reminder->confirmation_sent_at)
                                        <span class="badge badge--success">{{ showDateTime($reminder->confirmation_sent_at, 'd/m/Y H:i') }}</span>
                                    @else
                                        <span class="badge badge--warning">Pendente</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($reminder->live_notification_sent_at)
                                        <span class="badge badge--success">{{ showDateTime($reminder->live_notification_sent_at, 'd/m/Y H:i') }}</span>
                                    @else
                                        <span class="badge badge--dark">Ainda não</span>
                                    @endif
                                </td>
                                <td>{{ showDateTime($reminder->created_at, 'd/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Nenhum cliente ativou o alerta de início para este rodeio ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($reminders->hasPages())
            <div class="card-footer">
                {{ paginateLinks($reminders) }}
            </div>
        @endif
    </div>
@endsection
