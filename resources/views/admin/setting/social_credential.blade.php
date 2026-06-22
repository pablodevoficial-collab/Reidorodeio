@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Client ID</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ((array) (gs('socialite_credentials') ?? []) as $key => $credential)
                                    <tr>
                                        <td class="fw-bold">{{ ucfirst($key) }}</td>
                                        <td>{{ $credential->client_id }}</td>
                                        <td>
                                            @if (@$credential->status == Status::ENABLE)
                                                <span class="badge badge--success">Ativado</span>
                                            @else
                                                <span class="badge badge--warning">Desativado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button class="btn btn-outline--primary btn-sm editBtn"
                                                        data-client_id="{{ $credential->client_id }}"
                                                        data-client_secret="{{ $credential->client_secret }}"
                                                        data-key="{{ $key }}"><i class="la la-cogs"></i>
                                                    Configurar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline--dark helpBtn" data-target-key="{{ $key }}">
                                                    <i class="la la-question"></i> Ajuda
                                                </button>
                                                @if (@$credential->status == Status::ENABLE)
                                                    <button class="btn btn-outline--danger btn-sm confirmationBtn" data-question="Tem certeza que deseja desativar esta credencial de login?" data-action="{{ route('admin.setting.socialite.credentials.status.update', $key) }}">
                                                        <i class="las la-eye-slash"></i>Desativar
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--success btn-sm confirmationBtn" data-question="Tem certeza que deseja ativar esta credencial de login?" data-action="{{ route('admin.setting.socialite.credentials.status.update', $key) }}">
                                                        <i class="las la-eye"></i>Ativar
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atualizar credencial: <span class="credential-name"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Client ID</label>
                            <input type="text" class="form-control" name="client_id">
                        </div>
                        <div class="form-group">
                            <label>Client Secret</label>
                            <input type="text" class="form-control" name="client_secret">
                        </div>
                        <div class="form-group">
                            <label>URL de retorno (callback)</label>
                            <div class="input-group">
                                <input type="text" class="form-control callback" readonly>
                                <button type="button" class="input-group-text copyInput" title="Copiar">
                                    <i class="las la-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45"
                                id="editBtn">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help -->
    <div id="helpModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Como obter as credenciais do <span class="title-key"></span>?</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@php
    $callbackUrlTemplate = null;
    if (\Illuminate\Support\Facades\Route::has('user.social.callback')) {
        $callbackUrlTemplate = route('user.social.callback', ['provider' => '__provider__']);
    } elseif (\Illuminate\Support\Facades\Route::has('user.social.login.callback')) {
        $callbackUrlTemplate = route('user.social.login.callback', '__provider__');
    }
@endphp

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).on('click', '.editBtn', function() {
                let modal = $('#editModal');
                let data = $(this).data();
                let route = "{{ route('admin.setting.socialite.credentials.update', '') }}";
                let callbackTemplate = @json($callbackUrlTemplate);
                modal.find('form').attr('action', `${route}/${data.key}`);
                modal.find('.credential-name').text(data.key);
                modal.find('[name=client_id]').val(data.client_id);
                modal.find('[name=client_secret]').val(data.client_secret);

                if (callbackTemplate) {
                    modal.find('.callback').val(callbackTemplate.replace('__provider__', data.key));
                } else {
                    modal.find('.callback').val('Rota de callback não configurada');
                }
                modal.modal('show');
            });
            $('.copyInput').on('click', function(e) {
                var copybtn = $(this);
                var input = copybtn.closest('.input-group').find('input');
                if (input && input.select) {
                    input.select();
                    try {
                        document.execCommand('SelectAll')
                        document.execCommand('Copy', false, null);
                        input.blur();
                        notify('success', `Copiado: ${copybtn.closest('.input-group').find('input').val()}`);
                    } catch (err) {
                        alert('Pressione Ctrl/Cmd + C para copiar');
                    }
                }
            });

            $(document).on('click', '.helpBtn', function() {
                var modal = $('#helpModal');

                let rules = '';
                let key = $(this).data('target-key');
                modal.find('.title-key').text(key);

                if (key == 'google') {

                    rules = `<ul class="list-group list-group-flush">
                        <li class="list-group-item"><b>Passo 1</b>: Acesse a <a href="https://console.developers.google.com" target="_blank">Google Developer Console</a>.</li>
                        <li class="list-group-item"><b>Passo 2</b>: Clique em Select a project e depois em <a href="https://console.cloud.google.com/projectcreate" target="_blank">New Project</a> para criar um projeto informando o nome.</li>
                        <li class="list-group-item"><b>Passo 3</b>: Vá em <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Credentials</a>.</li>
                        <li class="list-group-item"><b>Passo 4</b>: Clique em Create Credentials e selecione <a href="https://console.cloud.google.com/apis/credentials/oauthclient" target="_blank">OAuth client ID</a>.</li>
                        <li class="list-group-item"><b>Passo 5</b>: Clique em <a href="https://console.cloud.google.com/apis/credentials/consent" target="_blank">Configure Consent Screen</a>.</li>
                        <li class="list-group-item"><b>Passo 6</b>: Escolha a opção External e crie.</li>
                        <li class="list-group-item"><b>Passo 7</b>: Preencha as informações obrigatórias da configuração do app.</li>
                        <li class="list-group-item"><b>Passo 8</b>: Volte em <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Credentials</a>, selecione Web application, preencha as informações e adicione a URL de redirecionamento; depois crie.</li>
                        <li class="list-group-item"><b>Passo 9</b>: Copie o Client ID e o Client Secret e cole na configuração do Google no painel admin.</li>
                    </ul>`;
                } else if (key == 'facebook') {
                    rules = ` <ul class="list-group list-group-flush">
                        <li class="list-group-item"><b>Passo 1</b>: Acesse o <a href="https://developers.facebook.com/" target="_blank">Facebook for Developers</a>.</li>
                        <li class="list-group-item"><b>Passo 2</b>: Clique em Get Started e crie uma conta de Desenvolvedor Meta.</li>
                        <li class="list-group-item"><b>Passo 3</b>: Crie um app selecionando a opção Consumer.</li>
                        <li class="list-group-item"><b>Passo 4</b>: Configure o Facebook Login e selecione a opção Web.</li>
                        <li class="list-group-item"><b>Passo 5</b>: Adicione a URL do site.</li>
                        <li class="list-group-item"><b>Passo 6</b>: Vá em Facebook Login > Settings e adicione aqui a URL de retorno (callback).</li>
                        <li class="list-group-item"><b>Passo 7</b>: Vá em Setting > Basic, copie as credenciais e cole no painel admin.</li>

                    </ul>`;
                } else if (key == 'linkedin') {
                    rules = `<ul class="list-group list-group-flush">
                        <li class="list-group-item"><b>Passo 1</b>: Acesse o <a href="https://developer.linkedin.com/" target="_blank">LinkedIn Developer</a>.</li>
                        <li class="list-group-item"><b>Passo 2</b>: Crie um app e preencha as informações solicitadas.</li>
                        <li class="list-group-item"><b>Passo 3</b>: Clique em Sign In with LinkedIn > Request access.</li>
                        <li class="list-group-item"><b>Passo 4</b>: Em Auth, copie as credenciais e cole no painel admin. Não esqueça de adicionar a URL de redirecionamento aqui.</li>
                    </ul>`;
                }

                modal.find('.modal-body').html(rules);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
