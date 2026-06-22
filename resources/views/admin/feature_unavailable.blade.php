@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-2">{{ __($pageTitle ?? __('Recurso indisponível')) }}</h5>
                <p class="text-muted mb-0">
                    {{ __($message ?? __('Este recurso não está disponível porque dependências (tabelas/seeders) não foram instaladas neste ambiente.')) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
