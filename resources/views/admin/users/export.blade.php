@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-body p-5">
                <div class="text-center mb-5">
                    <h3 class="text--primary mb-3">Exportar Base de Usuários Reais</h3>
                    <p class="text--muted">
                        Esta ferramenta exporta todos os usuários que <strong>não são bots</strong>.<br>
                        Selecione o tipo de dado que deseja exportar.
                    </p>
                </div>

                <div class="row justify-content-center">
                    <!-- Exportar E-mails -->
                    <div class="col-md-4 mb-3">
                        <form action="{{ route('admin.users.export.download') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="emails">
                            <button type="submit" class="btn btn--primary w-100 btn-lg p-4">
                                <i class="las la-envelope fa-2x mb-2 d-block"></i>
                                <span>Lista de E-mails</span>
                            </button>
                        </form>
                    </div>

                    <!-- Exportar Celulares -->
                    <div class="col-md-4 mb-3">
                        <form action="{{ route('admin.users.export.download') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="phones">
                            <button type="submit" class="btn btn--success w-100 btn-lg p-4">
                                <i class="las la-mobile-alt fa-2x mb-2 d-block"></i>
                                <span>Lista de Celulares</span>
                            </button>
                        </form>
                    </div>

                    <!-- Exportar Completo -->
                    <div class="col-md-4 mb-3">
                        <form action="{{ route('admin.users.export.download') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="full">
                            <button type="submit" class="btn btn--dark w-100 btn-lg p-4">
                                <i class="las la-database fa-2x mb-2 d-block"></i>
                                <span>Backup Completo</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info mt-5 text-center" role="alert">
                    <i class="las la-info-circle"></i> 
                    Os arquivos são gerados em formato CSV (compatível com Excel), ideais para importar em ferramentas de Marketing.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.all') }}" class="btn btn-sm btn--primary box--shadow1 text--small">
        <i class="la la-backward"></i> Voltar para Lista
    </a>
@endpush
