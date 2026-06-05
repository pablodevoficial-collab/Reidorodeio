@extends('admin.layouts.app')
@section('panel')
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h3>{{ $modalidade->nome }}</h3>
                <p><strong>@lang('Rodeio'):</strong> {{ $modalidade->rodeio->name ?? '-' }}</p>
                <p><strong>@lang('Início'):</strong> {{ \Carbon\Carbon::parse($modalidade->inicio)->format('d/m/Y H:i') }}</p>
                <p><strong>@lang('Tipo de Prêmio'):</strong> 
                    <span class="badge badge--{{ $modalidade->tipo_premio == 'dinheiro' ? 'success' : 'info' }}">
                        {{ ucfirst($modalidade->tipo_premio) }}
                    </span>
                </p>
                <p><strong>@lang('Prêmio'):</strong> 
                    @if($modalidade->tipo_premio == 'dinheiro')
                        R$ {{ number_format($modalidade->valor_premio, 2, ',', '.') }}
                    @else
                        {{ $modalidade->descricao_premio }}
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.modalidades.index') }}" />
@endpush
