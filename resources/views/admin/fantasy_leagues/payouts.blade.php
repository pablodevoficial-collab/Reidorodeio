@extends('admin.layouts.app')

@section('panel')

    <div class="fantasy-payouts-wrapper">
        <div class="fantasy-payouts-card">
            <div class="fantasy-payouts-header">
                <h5><i class="las la-money-check-alt"></i> Pagar bolao</h5>
            </div>

            <div class="fantasy-payouts-filters">
                <form method="GET" action="{{ route('admin.fantasy_prizes.index') }}">
                    <div class="fantasy-payouts-filter-group">
                        <div class="fantasy-payouts-filter-item search">
                            <label class="fantasy-payouts-filter-label"><i class="las la-search"></i> Buscar</label>
                            <input
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                class="fantasy-payouts-filter-input"
                                placeholder="Liga, time, usuario ou ID"
                            >
                        </div>
                        <div class="fantasy-payouts-filter-item">
                            <label class="fantasy-payouts-filter-label"><i class="las la-filter"></i> Status</label>
                            <select name="status" class="fantasy-payouts-filter-select">
                                <option value="">Todos</option>
                                <option value="pending" @if(request('status') === 'pending') selected @endif>Pendentes</option>
                                <option value="paid" @if(request('status') === 'paid') selected @endif>Pagos</option>
                            </select>
                        </div>
                        <div class="fantasy-payouts-filter-actions">
                            <button type="submit" class="fantasy-payouts-filter-btn primary">
                                <i class="las la-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.fantasy_prizes.index') }}" class="fantasy-payouts-filter-btn secondary">
                                Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="fantasy-payouts-table-wrapper">
                <table class="fantasy-payouts-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Liga</th>
                            <th>Time</th>
                            <th>Usuario</th>
                            <th>Posicao</th>
                            <th>Premio bruto</th>
                            <th>Comissao</th>
                            <th>Liquido</th>
                            <th>PIX</th>
                            <th>Status</th>
                            <th>Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teams as $team)
                            @php
                                $commission = (float) ($commissionTotals[$team->id] ?? 0);
                                $gross = (float) ($team->prize_won ?? 0);
                                $net = max(0, $gross - $commission);
                                $leagueName = $team->fantasyLeague->name ?? 'Liga';
                                $user = $team->user;
                                $userName = $user?->username ?: trim(($user?->firstname ?? '') . ' ' . ($user?->lastname ?? ''));
                                $userName = $userName ?: 'Usuario';
                                $pixType = $user?->pix_key_type;
                                $pixKey = $user?->pix_key;
                            @endphp
                            <tr>
                                <td>#{{ $team->id }}</td>
                                <td>
                                    <div>{{ $leagueName }}</div>
                                    <div class="payout-muted">Liga #{{ $team->fantasy_league_id }}</div>
                                </td>
                                <td>
                                    <div>{{ $team->team_name ?: 'Time sem nome' }}</div>
                                </td>
                                <td>
                                    @if ($team->user_id)
                                        <div>{{ $userName }}</div>
                                        <div class="payout-muted">{{ $team->user->email ?? '' }}</div>
                                    @else
                                        <div>BOT #{{ $team->bot_user_id }}</div>
                                        <div class="payout-muted">Sem usuario</div>
                                    @endif
                                </td>
                                <td>{{ $team->final_position ?? '-' }}</td>
                                <td>R$ {{ number_format($gross, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($commission, 2, ',', '.') }}</td>
                                <td><strong>R$ {{ number_format($net, 2, ',', '.') }}</strong></td>
                                <td>
                                    @if ($pixKey)
                                        <div class="payout-pill">{{ strtoupper($pixType ?? 'pix') }}</div>
                                        <div style="margin-top: 6px;">{{ $pixKey }}</div>
                                    @else
                                        <span class="payout-muted">Sem PIX</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($team->prize_paid_at)
                                        <span class="payout-badge badge-payment-paid">Pago</span>
                                        <div class="payout-muted" style="margin-top: 6px;">
                                            {{ $team->prize_paid_at ? \Illuminate\Support\Carbon::parse($team->prize_paid_at)->format('d/m/Y H:i') : '' }}
                                        </div>
                                    @else
                                        <span class="payout-badge badge-payment-pending">Pendente</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($team->prize_paid_at)
                                        <span class="payout-muted">Pago</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.fantasy_prizes.mark_paid', $team->id) }}" onsubmit="return confirm('Marcar premio como pago?');">
                                            @csrf
                                            <button type="submit" class="btn-mark-paid">
                                                <i class="las la-check"></i> Pagamento feito
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Nenhum premio encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding: 1.25rem 1.5rem;">
                {{ $teams->links() }}
            </div>
        </div>
    </div>
@endsection
