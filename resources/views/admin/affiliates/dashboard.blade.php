@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        {{-- Stats --}}
        <div class="col-xl-4 col-md-6 mb-30">
            <div class="card bg--primary">
                <div class="card-body">
                    <div class="widget-one text-center">
                        <div class="widget-one__content">
                            <h2 class="text-white">{{ $stats['active_affiliates'] }}</h2>
                            <p class="text-white">Afiliados Ativos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-30">
            <div class="card bg--warning">
                <div class="card-body">
                    <div class="widget-one text-center">
                        <div class="widget-one__content">
                            <h2 class="text-white">R$ {{ number_format($stats['pending_withdrawals_amount'], 2, ',', '.') }}</h2>
                            <p class="text-white">Solicitações de Saque ({{ $stats['pending_withdrawals_count'] }})</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-30">
            <div class="card bg--success">
                <div class="card-body">
                    <div class="widget-one text-center">
                        <div class="widget-one__content">
                            <h2 class="text-white">R$ {{ number_format($stats['total_paid_this_month'], 2, ',', '.') }}</h2>
                            <p class="text-white">Pago este Mês</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Solicitações de Saque Pendentes --}}
    @if(isset($pendingWithdrawals) && $pendingWithdrawals->count() > 0)
    <div class="row mb-30">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-header">
                    <h4 class="card-title">💸 Solicitações de Saque Pendentes</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>Afiliado</th>
                                <th>Valor</th>
                                <th>Dados Pagamento</th>
                                <th>Data</th>
                                <th>Ação</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pendingWithdrawals as $withdrawal)
                            <tr>
                                <td data-label="Afiliado">
                                    <span class="font-weight-bold">{{ $withdrawal->affiliate->user->fullname }}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.users.detail', $withdrawal->affiliate->user_id) }}"><span>@</span>{{ $withdrawal->affiliate->user->username }}</a>
                                    </span>
                                </td>
                                <td data-label="Valor">
                                    <strong>R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}</strong>
                                </td>
                                <td data-label="Dados">
                                    {{ $withdrawal->payment_details }}
                                </td>
                                <td data-label="Data">
                                    {{ $withdrawal->created_at->format('d/m/Y H:i') }}
                                    <br> {{ $withdrawal->created_at->diffForHumans() }}
                                </td>
                                <td data-label="Ação">
                                    <button class="btn btn-sm btn-outline--success" onclick="confirmWithdrawal({{ $withdrawal->id }}, 'approve')">
                                        <i class="las la-check"></i> Aprovar
                                    </button>
                                    <button class="btn btn-sm btn-outline--danger" onclick="confirmWithdrawal({{ $withdrawal->id }}, 'reject')">
                                        <i class="las la-times"></i> Rejeitar
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Afiliados --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-header">
                    <h4 class="card-title">🏆 Top Afiliados</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>Afiliado</th>
                                <th>Indicações Ativas</th>
                                <th>Tier</th>
                                <th>Total Ganho</th>
                                <th>Ação</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($topAffiliates as $affiliate)
                            <tr>
                                <td data-label="Afiliado">
                                    <span class="font-weight-bold">{{ $affiliate->user->fullname }}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.users.detail', $affiliate->user_id) }}"><span>@</span>{{ $affiliate->user->username }}</a>
                                    </span>
                                </td>
                                <td data-label="Indicações">
                                    {{ $affiliate->active_referrals }}
                                </td>
                                <td data-label="Tier">
                                    <span class="badge badge--primary">{{ ucfirst($affiliate->tier) }}</span>
                                </td>
                                <td data-label="Total Ganho">
                                    R$ {{ number_format($affiliate->total_earned, 2, ',', '.') }}
                                </td>
                                <td data-label="Ação">
                                    <a href="{{ route('admin.affiliates.show', $affiliate->id) }}" class="btn btn-sm btn-outline--primary">
                                        <i class="las la-desktop"></i> Detalhes
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmação --}}
    <div id="confirmModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Ação</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="confirmForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="action" id="actionInput">
                    <div class="modal-body">
                        <p id="confirmMessage"></p>
                        <div class="form-group">
                            <label>Observações / Motivo</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn--primary">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    function confirmWithdrawal(id, action) {
        var modal = $('#confirmModal');
        var form = $('#confirmForm');
        var message = $('#confirmMessage');
        var input = $('#actionInput');
        
        form.attr('action', "{{ route('admin.affiliates.withdrawal.process', '') }}/" + id);
        input.val(action);
        
        if(action === 'approve') {
            message.text('Tem certeza que deseja APROVAR este saque? O saldo será debitado.');
            modal.find('.btn--primary').removeClass('btn--danger').addClass('btn--success').text('Aprovar');
        } else {
            message.text('Tem certeza que deseja REJEITAR este saque? O valor retornará ao saldo disponível.');
            modal.find('.btn--primary').removeClass('btn--success').addClass('btn--danger').text('Rejeitar');
        }
        
        modal.modal('show');
    }
</script>
@endpush