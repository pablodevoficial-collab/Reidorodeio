@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card ">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> As views compiladas serão limpas</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> O cache da aplicação será limpo</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> O cache de rotas será limpo</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> O cache de configurações será limpo</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> Arquivos compilados de serviços e pacotes serão removidos</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> Todos os caches serão limpos</span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.system.optimize.clear') }}" class="btn btn--primary w-100 h-45">Limpar agora</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .list-group-item span {
            font-size: 22px !important;
            padding: 8px 0px
        }
    </style>
@endpush
