@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light custom-data-table">
                            <thead>
                                <tr>
                                    <th>Início</th>
                                    <th>Fim</th>
                                    <th>Tempo de execução</th>
                                    <th>Erro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ showDateTime($log->start_at) }} </td>
                                        <td>{{ showDateTime($log->end_at) }} </td>
                                        <td>{{ $log->duration }} segundos</td>
                                        <td>{{ $log->error }}</td>
                                        <td>
                                            @if ($log->error != null)
                                                <button type="button" class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.cron.schedule.log.resolved', $log->id) }}" data-question="Tem certeza que deseja marcar este log como resolvido?">
                                                    <i class="la la-check"></i> Resolvido
                                                </button>
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($logs->hasPages())
                    <div class="card-footer">
                        {{ paginateLinks($logs) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-outline--danger confirmationBtn" data-action="{{ route('admin.cron.log.flush', $cronJob->id) }}" data-question="Tem certeza que deseja limpar todos os logs?"><i class="la la-history"></i> Limpar logs</button>
@endpush
