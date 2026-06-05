@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4">
        <div class="col-xl-3 col-md-6">
            <div class="rr-stat-card rr-stat-card--info">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['subscriptions_active'] }}</span>
                    <span class="rr-stat-card__label">Subscriptions ativas</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rr-stat-card rr-stat-card--primary">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['users_reachable'] }}</span>
                    <span class="rr-stat-card__label">Usuários alcançáveis</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rr-stat-card rr-stat-card--success">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['premium_reachable'] }}</span>
                    <span class="rr-stat-card__label">Premium com push</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rr-stat-card rr-stat-card--warning">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['trial_reachable'] }}</span>
                    <span class="rr-stat-card__label">Trial com push</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rr-stat-card rr-stat-card--primary">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['native_tokens'] }}</span>
                    <span class="rr-stat-card__label">Tokens nativos do app</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="rr-stat-card rr-stat-card--info">
                <div class="rr-stat-card__content">
                    <span class="rr-stat-card__value">{{ $stats['native_users'] }}</span>
                    <span class="rr-stat-card__label">Usuários alcançáveis no app</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mt-1">
        <div class="col-lg-7">
            <div class="card custom--card">
                <div class="card-header"><h5 class="mb-0">Enviar push notification</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.app_control.push.send') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Título</label>
                                <input type="text" name="title" class="form-control" maxlength="120" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segmento</label>
                                <select name="target" class="form-control" id="pushTargetSelect" required>
                                    <option value="all">Todos com push ativo</option>
                                    <option value="premium">Somente premium</option>
                                    <option value="trial">Somente trial</option>
                                    <option value="verified_mobile">Somente mobile verificado</option>
                                    <option value="user">Usuário específico</option>
                                </select>
                            </div>
                            <div class="col-md-12 d-none" id="pushUserIdWrap">
                                <label class="form-label">ID do usuário</label>
                                <input type="number" name="user_id" class="form-control" min="1" placeholder="Ex.: 123">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mensagem</label>
                                <textarea name="message" class="form-control" rows="5" maxlength="500" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">URL de ação</label>
                                <input type="text" name="action_url" class="form-control" placeholder="rei://... ou https://...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Imagem</label>
                                <input type="text" name="image_url" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn--primary">Enviar push</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card custom--card mb-4">
                <div class="card-header"><h5 class="mb-0">Status do push nativo</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-{{ $firebaseClientReady ? 'success' : 'warning' }}">Config pública {{ $firebaseClientReady ? 'ok' : 'pendente' }}</span>
                        <span class="badge bg-{{ $serviceAccountReady ? 'success' : 'warning' }}">Service account {{ $serviceAccountReady ? 'ok' : 'pendente' }}</span>
                    </div>

                    @if($nativePushIssues)
                        <div class="alert alert-warning mb-0">
                            @foreach($nativePushIssues as $issue)
                                <div>{{ $issue }}</div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            O backend já está pronto para enviar FCM ao app nativo.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card custom--card mb-4">
                <div class="card-header"><h5 class="mb-0">Configuração pública do Firebase</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.app_control.push.firebase_config') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">API Key</label>
                                <input type="text" name="apiKey" class="form-control" value="{{ old('apiKey', $firebaseClientConfig['apiKey']) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Auth Domain</label>
                                <input type="text" name="authDomain" class="form-control" value="{{ old('authDomain', $firebaseClientConfig['authDomain']) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Project ID</label>
                                <input type="text" name="projectId" class="form-control" value="{{ old('projectId', $firebaseClientConfig['projectId']) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Storage Bucket</label>
                                <input type="text" name="storageBucket" class="form-control" value="{{ old('storageBucket', $firebaseClientConfig['storageBucket']) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Messaging Sender ID</label>
                                <input type="text" name="messagingSenderId" class="form-control" value="{{ old('messagingSenderId', $firebaseClientConfig['messagingSenderId']) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">App ID</label>
                                <input type="text" name="appId" class="form-control" value="{{ old('appId', $firebaseClientConfig['appId']) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Measurement ID</label>
                                <input type="text" name="measurementId" class="form-control" value="{{ old('measurementId', $firebaseClientConfig['measurementId']) }}">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn--primary">Salvar Firebase</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom--card mt-4">
                <div class="card-header"><h5 class="mb-0">Service account do Firebase</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.app_control.push.service_account') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Arquivo JSON</label>
                            <input type="file" name="service_account_file" class="form-control" accept=".json" required>
                        </div>
                        <button type="submit" class="btn btn--primary">Enviar service account</button>
                    </form>
                </div>
            </div>

            <div class="card custom--card mt-4">
                <div class="card-header"><h5 class="mb-0">Últimas subscriptions</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table--light style--two mb-0">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Status</th>
                                    <th>Último uso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSubscriptions as $subscription)
                                    <tr>
                                        <td>
                                            {{ $subscription->user?->username ?? 'Anônimo' }}<br>
                                            <span class="text-muted">{{ $subscription->user?->email ?? 'Sem vínculo' }}</span>
                                        </td>
                                        <td>{{ $subscription->is_active ? 'Ativa' : 'Inativa' }}</td>
                                        <td>{{ optional($subscription->last_used_at)->format('d/m/Y H:i') ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    (function () {
        var targetSelect = document.getElementById('pushTargetSelect');
        var userWrap = document.getElementById('pushUserIdWrap');

        function syncPushTarget() {
            if (!targetSelect || !userWrap) return;
            userWrap.classList.toggle('d-none', targetSelect.value !== 'user');
        }

        if (targetSelect) {
            targetSelect.addEventListener('change', syncPushTarget);
            syncPushTarget();
        }
    })();
</script>
@endpush
