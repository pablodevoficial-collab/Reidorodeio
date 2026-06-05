@extends('admin.layouts.app')

@section('panel')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Enviar Notificação (Removido)</h4>
            <p>Esta funcionalidade foi removida do painel administrativo. A opção "Enviar notificação" foi retirada do menu e das rotas do admin.</p>
            <a href="{{ route('admin.users.all') }}" class="btn btn--primary">Voltar para Usuários</a>
        </div>
    </div>
@endsection
