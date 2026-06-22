@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h3>{{ $rodeio->name }}</h3>
                <p><strong>@lang('Cidade'):</strong> {{ $rodeio->info['cidade'] ?? '-' }}</p>
                <p><strong>@lang('Data Início'):</strong> {{ $rodeio->start ? \Carbon\Carbon::parse($rodeio->start)->format('d/m/Y') : '-' }}</p>
                <p><strong>@lang('Data Fim'):</strong> {{ $rodeio->end ? \Carbon\Carbon::parse($rodeio->end)->format('d/m/Y') : '-' }}</p>
                <p><strong>@lang('Status'):</strong> <span class="badge badge--{{ $rodeio->status == 'ativo' ? 'success' : 'warning' }}">{{ ucfirst($rodeio->status) }}</span></p>
                <p><strong>@lang('Descrição'):</strong> {{ $rodeio->info['descricao'] ?? '-' }}</p>
                @if($rodeio->logo)
                    <div class="mb-3">
                        <img src="{{ Storage::url($rodeio->logo) }}" alt="Imagem" style="height: 120px; object-fit: cover;">
                    </div>
                @endif
                <h5>@lang('Modalidades')</h5>
                @if($rodeio->modalidades && count($rodeio->modalidades))
                    <ul>
                        @foreach($rodeio->modalidades as $mod)
                            <li>{{ $mod->nome }} ({{ $mod->inicio }})</li>
                        @endforeach
                    </ul>
                @else
                    <span class="text-muted">Nenhuma modalidade cadastrada.</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.rodeios.index') }}" />
@endpush
