@extends('admin.layouts.app')
@section('panel')
    @push('style')
    <style>
        .pagination {
            justify-content: center;
        }
        /* Garantir que o modal apareça corretamente dentro do fluxo, mas acima do conteúdo */
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal {
            z-index: 1050 !important;
        }
    </style>
    @endpush
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Listagem de Clientes</h5>
                    <form action="" method="GET" class="d-flex gap-2">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Buscar Username, Email ou Celular" value="{{ request()->search }}">
                            <button class="btn btn--primary" type="submit"><i class="las la-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                @if(request()->routeIs('admin.users.email.unverified'))
                                    <tr>
                                        <th>Usuário</th>
                                        <th>E-mail</th>
                                        <th>Registrado em</th>
                                        <th>Ação</th>
                                    </tr>
                                @else
                                    <tr>
                                        <th>Usuário</th>
                                        <th>E-mail</th>
                                        <th>Registrado em</th>
                                        <th>Ação</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $user->username }}</span>
                                        </td>

                                        @if(request()->routeIs('admin.users.email.unverified'))
                                            <td>{{ $user->email }}</td>
                                            <td>{{ showDateTime($user->created_at) }}</td>
                                            <td>
                                                <div class="button--group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Em desenvolvimento" disabled aria-disabled="true">
                                                        <i class="las la-bell"></i> Notificar
                                                    </button>
                                                </div>
                                            </td>
                                        @else
                                            <td>
                                                {{ $user->email }}
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span>{{ showDateTime($user->created_at) }}</span>
                                                    <span class="text-muted small">{{ diffForHumans($user->created_at) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary notifyBtn" data-id="{{ $user->id }}" data-name="{{ $user->fullname }}" title="Notificar">
                                                        <i class="las la-bell"></i> Notificar
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-secondary editBtn" 
                                                        data-id="{{ $user->id }}"
                                                        data-firstname="{{ $user->firstname }}"
                                                        data-lastname="{{ $user->lastname }}"
                                                        data-email="{{ $user->email }}"
                                                        data-mobile="{{ $user->mobile }}"
                                                        data-cpf="{{ $user->cpf }}"
                                                        data-birthdate="{{ $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('Y-m-d') : '' }}"
                                                        data-pix_key="{{ $user->pix_key }}"
                                                        data-pix_key_type="{{ $user->pix_key_type }}"
                                                        data-ev="{{ $user->ev }}"
                                                        data-sv="{{ $user->sv }}"
                                                        data-ts="{{ $user->ts }}"
                                                        data-kv="{{ $user->kv }}"
                                                        title="Editar">
                                                        <i class="las la-edit"></i> Editar
                                                    </button>

                                                    @if (request()->routeIs('admin.users.kyc.pending'))
                                                        <a href="{{ route('admin.users.kyc.details', $user->id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                            <i class="las la-user-check"></i> Dados KYC
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif

                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($users->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($users) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de Edição --}}
    <div id="editUserModal" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="editUserForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome</label>
                                <input class="form-control" type="text" name="firstname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sobrenome</label>
                                <input class="form-control" type="text" name="lastname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">E-mail</label>
                                <input class="form-control" type="email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input class="form-control" type="text" name="mobile" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CPF</label>
                                <input class="form-control" type="text" name="cpf" maxlength="14" placeholder="000.000.000-00">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Data de Nascimento</label>
                                <input class="form-control" type="date" name="birthdate">
                            </div>

                            <div class="col-md-12"><hr></div>
                            <h6 class="mb-3">Dados Bancários (PIX)</h6>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo de Chave</label>
                                <select class="form-select" name="pix_key_type">
                                    <option value="">Selecione...</option>
                                    <option value="cpf">CPF</option>
                                    <option value="email">Email</option>
                                    <option value="phone">Telefone</option>
                                    <option value="random">Chave Aleatória</option>
                                </select>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label">Chave PIX</label>
                                <input class="form-control" type="text" name="pix_key">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label text-warning">Nova Senha (deixe em branco para não alterar)</label>
                                <input class="form-control" type="password" name="password" autocomplete="new-password">
                            </div>

                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label d-block">E-mail Verificado</label>
                                        <input type="checkbox" data-width="100%" data-onstyle="success" data-offstyle="danger" data-bs-toggle="toggle" data-on="Sim" data-off="Não" name="ev">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label d-block">SMS Verificado</label>
                                        <input type="checkbox" data-width="100%" data-onstyle="success" data-offstyle="danger" data-bs-toggle="toggle" data-on="Sim" data-off="Não" name="sv">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label d-block">2FA Ativo</label>
                                        <input type="checkbox" data-width="100%" data-onstyle="success" data-offstyle="danger" data-bs-toggle="toggle" data-on="Sim" data-off="Não" name="ts">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label d-block">KYC Verificado</label>
                                        <input type="checkbox" data-width="100%" data-onstyle="success" data-offstyle="danger" data-bs-toggle="toggle" data-on="Sim" data-off="Não" name="kv">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Notificação --}}
    <div id="notifyUserModal" class="modal fade" tabindex="-1" role="dialog" data-bs-backdrop="static" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notificar Usuário: <span id="notifyUserName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="notifyUserForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Assunto</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mensagem</label>
                            <textarea class="form-control" name="message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Enviar Notificação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script')
<script>
    (function($){
        "use strict";
        
        // Editar Usuário
        $('.editBtn').on('click', function() {
            var modal = $('#editUserModal');
            var form = modal.find('form');
            var data = $(this).data();
            
            form.attr('action', "{{ route('admin.users.update', '') }}/" + data.id);
            
            modal.find('input[name=firstname]').val(data.firstname);
            modal.find('input[name=lastname]').val(data.lastname);
            modal.find('input[name=email]').val(data.email);
            modal.find('input[name=mobile]').val(data.mobile);
            modal.find('input[name=cpf]').val(data.cpf);
            modal.find('input[name=birthdate]').val(data.birthdate);
            modal.find('select[name=pix_key_type]').val(data.pix_key_type);
            modal.find('input[name=pix_key]').val(data.pix_key);
            modal.find('input[name=password]').val(''); // Limpa senha
            
            // Toggles
            if (typeof $.fn.bootstrapToggle !== 'undefined') {
                if (data.ev) modal.find('input[name=ev]').bootstrapToggle('on'); else modal.find('input[name=ev]').bootstrapToggle('off');
                if (data.sv) modal.find('input[name=sv]').bootstrapToggle('on'); else modal.find('input[name=sv]').bootstrapToggle('off');
                if (data.ts) modal.find('input[name=ts]').bootstrapToggle('on'); else modal.find('input[name=ts]').bootstrapToggle('off');
                if (data.kv) modal.find('input[name=kv]').bootstrapToggle('on'); else modal.find('input[name=kv]').bootstrapToggle('off');
            } else {
                modal.find('input[name=ev]').prop('checked', data.ev == 1);
                modal.find('input[name=sv]').prop('checked', data.sv == 1);
                modal.find('input[name=ts]').prop('checked', data.ts == 1);
                modal.find('input[name=kv]').prop('checked', data.kv == 1);
            }
            
            modal.modal('show');
        });

        // Notificar Usuário
        $('.notifyBtn').on('click', function() {
            var modal = $('#notifyUserModal');
            var form = modal.find('form');
            var id = $(this).data('id');
            var name = $(this).data('name');

            $('#notifyUserName').text(name);
            form.attr('action', "{{ route('admin.users.notify.single', '') }}/" + id);
            
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush




