@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="rr-stat-card rr-stat-card--primary">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['total_users'] }}</span>
                    <span class="rr-stat-card__label">Usuários totais</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="rr-stat-card rr-stat-card--success">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['premium_users'] }}</span>
                    <span class="rr-stat-card__label">Premium ativos</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="rr-stat-card rr-stat-card--warning">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['trial_users'] }}</span>
                    <span class="rr-stat-card__label">Trials ativos</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="rr-stat-card rr-stat-card--info">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['push_active'] }}</span>
                    <span class="rr-stat-card__label">Push ativos</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="rr-stat-card rr-stat-card--orange">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['push_users'] }}</span>
                    <span class="rr-stat-card__label">Usuários alcançáveis</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="rr-stat-card rr-stat-card--pink">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['recent_signups'] }}</span>
                    <span class="rr-stat-card__label">Cadastros 7 dias</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mt-1">
        <div class="col-lg-7">
            <div class="card custom--card">
                <div class="card-header"><h5 class="mb-0">Últimos usuários do app</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table--light style--two mb-0">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th>Push</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestUsers as $user)
                                    <tr>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->active_push_subscriptions_count }}</td>
                                        <td>
                                            <a href="{{ route('admin.app_control.users.edit', $user) }}" class="btn btn-sm btn-outline--primary">Editar</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card custom--card">
                <div class="card-header"><h5 class="mb-0">Saúde do app</h5></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><span>Mobile verificado</span><strong>{{ $snapshot['mobile_verified'] }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Mobile pendente</span><strong>{{ $snapshot['mobile_unverified'] }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Assinaturas ativas</span><strong>{{ $snapshot['subscriptions_active'] }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Trials expiram em 7 dias</span><strong>{{ $snapshot['trials_expiring_soon'] }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
