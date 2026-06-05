@extends('admin.layouts.app')
@section('panel')
@push('topBar')
  @include('admin.notification.top_bar')
@endpush
<div class="row">
        <div class="col-md-12">
            <div class="card">
                <form method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>E-mail Send Método</label>
                            <select name="email_method" class="select2 form-control" data-minimum-results-for-search="-1">
                                <option value="php" @if (gs('mail_config')->name == 'php') selected @endif>PHP Mail</option>
                                <option value="smtp" @if (gs('mail_config')->name == 'smtp') selected @endif>SMTP</option>
                                <option value="sendgrid" @if (gs('mail_config')->name == 'sendgrid') selected @endif>SendGrid API</option>
                                <option value="mailjet" @if (gs('mail_config')->name == 'mailjet') selected @endif>Mailjet API</option>
                            </select>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remetente (Nome)</label>
                                    <input type="text" class="form-control" placeholder="Ex: Rei do Rodeio" name="email_from_name" value="{{ gs('email_from_name') ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Remetente (E-mail)</label>
                                    <input type="email" class="form-control" placeholder="Ex: oficial@reidorodeio.com.br" name="email_from" value="{{ gs('email_from') ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4 d-none configForm" id="smtp">
                            <div class="col-md-12">
                                <h6 class="mb-2">SMTP Configuration</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Host </label>
                                    <input type="text" class="form-control" placeholder="e.g. smtp.googlemail.com" name="host" value="{{ gs('mail_config')->host ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Port </label>
                                    <input type="text" class="form-control" placeholder="Available port" name="port" value="{{ gs('mail_config')->port ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Encryption</label>
                                    <select class="form-control select2" data-minimum-results-for-search="-1" name="enc">
                                        <option value="ssl" @selected(@gs('mail_config')->enc == 'ssl')>SSL</option>
                                        <option value="tls" @selected(@gs('mail_config')->enc == 'tls')>TLS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Usuário </label>
                                    <input type="text" class="form-control" placeholder="Normally your email address" name="username" value="{{ gs('mail_config')->username ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Senha </label>
                                    <input type="text" class="form-control" placeholder="Normally your email password" name="password" value="{{ gs('mail_config')->password ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4 d-none configForm" id="sendgrid">
                            <div class="col-md-12">
                                <h6 class="mb-2">SendGrid API Configuration</h6>
                            </div>
                            <div class="form-group col-md-12">
                                <label>App Chave </label>
                                <input type="text" class="form-control" placeholder="SendGrid App key" name="appkey" value="{{ gs('mail_config')->appkey ?? '' }}">
                            </div>
                        </div>
                        <div class="row mt-4 d-none configForm" id="mailjet">
                            <div class="col-md-12">
                                <h6 class="mb-2">Mailjet API Configuration</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Api Public Chave </label>
                                    <input type="text" class="form-control" placeholder="Mailjet Api Public Chave" name="public_key" value="{{ gs('mail_config')->public_key ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Api Secret Chave </label>
                                    <input type="text" class="form-control" placeholder="Mailjet Api Secret Chave" name="secret_key" value="{{ gs('mail_config')->secret_key ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary w-100 h-45">Enviar</button>
                    </div>
                </form>
            </div><!-- card end -->
        </div>


    </div>


    {{-- TEST MAIL MODAL --}}
    <div id="testMailModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Mail Setup</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.setting.notification.email.test') }}" method="POST" id="testMailForm">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sent to </label>
                                    <input type="text" name="email" class="form-control" placeholder="E-mail Endereço" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('breadcrumb-plugins')
    <button type="button" data-bs-target="#testMailModal" data-bs-toggle="modal" class="btn btn-sm btn-outline--primary"><i class="las la-paper-plane"></i> Send Test Mail</button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";



            var method = '{{ gs('mail_config')->name }}';
            emailMethod(method);
            $('select[name=email_method]').on('change', function() {
                var method = $(this).val();
                emailMethod(method);
            });

            function emailMethod(method) {
                $('.configForm').addClass('d-none');
                if (method != 'php') {
                    $(`#${method}`).removeClass('d-none');
                }
            }

            // AJAX Email Test
            $('#testMailForm').on('submit', function(e) {
                e.preventDefault();
                var btn = $(this).find('button[type="submit"]');
                var originalText = btn.text();
                
                console.log('🚀 Iniciando teste de email...');
                console.log('📦 Dados:', $(this).serialize());
                
                btn.text('Enviando...').prop('disabled', true);
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        console.log('✅ Sucesso:', response);
                        alert('Sucesso! ' + response.message);
                        btn.text(originalText).prop('disabled', false);
                    },
                    error: function(xhr) {
                        console.error('❌ Erro:', xhr);
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText;
                        console.error('Mensagem de erro:', msg);
                        alert('Erro ao enviar: ' + msg);
                        btn.text(originalText).prop('disabled', false);
                    }
                });
            });

        })(jQuery);
    </script>
@endpush
