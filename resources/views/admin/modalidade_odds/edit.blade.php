@extends('admin.layouts.app')

@section('panel')
<div class="row gy-3">
    <div class="col-12">
        <div class="card b-radius--10">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="mb-1">{{ $modalidade->nome }}</h5>
                    <div class="text-muted small">
                        Rodeio: {{ $modalidade->rodeio?->nome ?? $modalidade->rodeio?->titulo ?? $modalidade->rodeio?->name ?? '-' }}
                        · Modalidade #{{ $modalidade->id }}
                    </div>
                </div>
                <a href="{{ route('admin.modalidade_odds.index') }}" class="btn btn--dark btn-sm">
                    <i class="las la-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="dashboard-w1 bg--primary b-radius--10 box-shadow">
            <div class="details">
                <span class="text--small">Caixa Pago (X1)</span>
                <h4 class="amount">R$ {{ number_format((float) ($finance['paid_volume'] ?? 0), 2, ',', '.') }}</h4>
            </div>
            <div class="icon"><i class="las la-wallet"></i></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="dashboard-w1 bg--success b-radius--10 box-shadow">
            <div class="details">
                <span class="text--small">Lucro Casa (X1)</span>
                <h4 class="amount">R$ {{ number_format((float) ($finance['house_fee'] ?? 0), 2, ',', '.') }}</h4>
            </div>
            <div class="icon"><i class="las la-coins"></i></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12">
        <div class="dashboard-w1 bg--info b-radius--10 box-shadow">
            <div class="details">
                <span class="text--small">Boost global</span>
                <h4 class="amount">{{ $boostAvailable ? 'ATIVO' : 'INATIVO' }}</h4>
                <p class="mb-0 text--muted">
                    Margem atual: {{ number_format((float) ($finance['margin_percent'] ?? 0), 2, ',', '.') }}%
                </p>
            </div>
            <div class="icon"><i class="las la-chart-line"></i></div>
        </div>
    </div>

    <div class="col-12">
        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="mb-0">Parâmetros da automação de odds</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.modalidade_odds.update', $modalidade->id) }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" value="1" @checked((bool) ($settings['is_enabled'] ?? false))>
                            <label class="form-check-label" for="is_enabled">Ativar boost automático para baixa demanda</label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gate de caixa (R$)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('bankroll_gate_amount') is-invalid @enderror" name="bankroll_gate_amount" value="{{ old('bankroll_gate_amount', $settings['bankroll_gate_amount'] ?? 0) }}" required>
                        @error('bankroll_gate_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Faixa baixa participação (até)</label>
                        <input type="number" min="0" class="form-control @error('low_bet_threshold') is-invalid @enderror" name="low_bet_threshold" value="{{ old('low_bet_threshold', $settings['low_bet_threshold'] ?? 0) }}" required>
                        @error('low_bet_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Faixa quase sem participação (até)</label>
                        <input type="number" min="0" class="form-control @error('very_low_bet_threshold') is-invalid @enderror" name="very_low_bet_threshold" value="{{ old('very_low_bet_threshold', $settings['very_low_bet_threshold'] ?? 0) }}" required>
                        @error('very_low_bet_threshold')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Margem mínima da casa (%)</label>
                        <input type="number" step="0.01" min="30" max="100" class="form-control @error('min_house_margin_percent') is-invalid @enderror" name="min_house_margin_percent" value="{{ old('min_house_margin_percent', max(30, (float) ($settings['min_house_margin_percent'] ?? 30))) }}" required>
                        @error('min_house_margin_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Teto odd Free</label>
                        <input type="number" step="0.01" min="1" max="2" class="form-control @error('max_free_odd') is-invalid @enderror" name="max_free_odd" value="{{ old('max_free_odd', min(2, (float) ($settings['max_free_odd'] ?? 2))) }}" required>
                        @error('max_free_odd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Teto odd Premium</label>
                        <input type="number" step="0.01" min="1" max="10" class="form-control @error('max_premium_odd') is-invalid @enderror" name="max_premium_odd" value="{{ old('max_premium_odd', $settings['max_premium_odd'] ?? 2.3) }}" required>
                        @error('max_premium_odd')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            <strong>Regra fixa:</strong> odds Free limitadas a <strong>2,00x</strong> e odds nunca abaixo de <strong>1,70x</strong>.
                            O boost usa <strong>30%</strong> do lucro real excedente após bater a margem mínima.
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn--primary" type="submit">
                            <i class="las la-save"></i> Salvar configuração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="mb-0">Competidores e volume de participação (escopo atual)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two mb-0">
                        <thead>
                            <tr>
                                <th>Competidor</th>
                                <th>X1 vinculados</th>
                                <th>Faixa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($competitorRows as $row)
                                <tr>
                                    <td>{{ $row->nome }}</td>
                                    <td>{{ $row->x1_count }}</td>
                                    <td>
                                        @if($row->tier === 'very_low')
                                            <span class="badge bg-danger">Quase sem participação</span>
                                        @elseif($row->tier === 'low')
                                            <span class="badge bg-warning text-dark">Baixa participação</span>
                                        @else
                                            <span class="badge bg-secondary">Normal</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Nenhum competidor vinculado à modalidade.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
