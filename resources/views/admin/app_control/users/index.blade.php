@extends('admin.layouts.app')

@section('panel')
    <div class="card custom--card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-lg-4">
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Buscar usuário, email ou celular">
                </div>
                <div class="col-lg-3">
                    <select name="status" class="form-control">
                        <option value="">Todos os status</option>
                        <option value="verified_mobile" @selected($status === 'verified_mobile')>Mobile verificado</option>
                        <option value="unverified_mobile" @selected($status === 'unverified_mobile')>Mobile pendente</option>
                        <option value="active" @selected($status === 'active')>Ativo</option>
                        <option value="banned" @selected($status === 'banned')>Banido</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select name="premium" class="form-control">
                        <option value="">Premium e free</option>
                        <option value="yes" @selected($premium === 'yes')>Só premium</option>
                        <option value="no" @selected($premium === 'no')>Só free</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn--primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card custom--card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table--light style--two mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Celular</th>
                            <th>Premium</th>
                            <th>Push</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>#{{ $user->id }}</td>
                                <td>
                                    <strong>{{ $user->username }}</strong><br>
                                    <span class="text-muted">{{ $user->email }}</span>
                                </td>
                                <td>{{ $user->mobile ?: '—' }}</td>
                                <td>{{ $user->isPremium() ? 'Sim' : 'Não' }}</td>
                                <td>{{ $user->active_push_subscriptions_count }}</td>
                                <td>{{ (int) $user->status === 1 ? 'Ativo' : 'Banido' }}</td>
                                <td><a href="{{ route('admin.app_control.users.edit', $user) }}" class="btn btn-sm btn-outline--primary">Editar</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
