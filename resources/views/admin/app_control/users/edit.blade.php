@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4">
        <div class="col-lg-8">
            <div class="card custom--card">
                <div class="card-header"><h5 class="mb-0">Editar usuário do app</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.app_control.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input type="text" name="firstname" class="form-control" value="{{ old('firstname', $user->firstname) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sobrenome</label>
                                <input type="text" name="lastname" class="form-control" value="{{ old('lastname', $user->lastname) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Usuário</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Celular</label>
                                <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $user->mobile) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CPF</label>
                                <input type="text" name="cpf" class="form-control" value="{{ old('cpf', $user->cpf) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nascimento</label>
                                <input type="date" name="birthdate" class="form-control" value="{{ old('birthdate', optional($user->birthdate)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="1" @selected((int) old('status', $user->status) === 1)>Ativo</option>
                                    <option value="0" @selected((int) old('status', $user->status) === 0)>Banido</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-4">
                                <label><input type="checkbox" name="ev" value="1" @checked(old('ev', (int) $user->ev) == 1)> Email verificado</label>
                                <label><input type="checkbox" name="sv" value="1" @checked(old('sv', (int) $user->sv) == 1)> Mobile verificado</label>
                                <label><input type="checkbox" name="show_in_listings" value="1" @checked(old('show_in_listings', $user->show_in_listings))> Exibir em listagens</label>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn--primary">Salvar usuário</button>
                            <a href="{{ route('admin.app_control.users.index') }}" class="btn btn--dark">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card custom--card">
                <div class="card-header"><h5 class="mb-0">Resumo</h5></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><span>ID</span><strong>#{{ $user->id }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Premium</span><strong>{{ $user->isPremium() ? 'Sim' : 'Não' }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Trial</span><strong>{{ $user->isOnTrial() ? 'Sim' : 'Não' }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Push ativos</span><strong>{{ $user->pushSubscriptions->where('is_active', true)->count() }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
