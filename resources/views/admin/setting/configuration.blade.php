@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form method="post">
                    @csrf
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Registro de Usuário</p>
                                    <p class="mb-0">
                                        <small>Se desabilitar este módulo, ninguém poderá se registrar no sistema.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="registration" @if (gs('registration')) checked @endif>
                                </div>
                            </li>
                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Forçar SSL</p>
                                    <p class="mb-0">
                                        <small>By enabling <span class="fw-bold">Force SSL (Secure Sockets Layer)</span> the system will force a visitor that he/she must have to visit in secure mode. Otherwise, the site will be loaded in secure mode.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Enable" data-off="Disable" name="force_ssl" @if (gs('force_ssl')) checked @endif>
                                </div>
                            </li>
                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Concordar com a Política</p>
                                    <p class="mb-0">
                                        <small>If you enable this module, that means a user must have to agree with your system's <a href="{{ route('admin.frontend.sections', 'policy_pages') }}">policies</a> during registration.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Enable" data-off="Disable" name="agree" @if (gs('agree')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Force Secure Senha</p>
                                    <p class="mb-0">
                                        <small>By enabling this module, a user must set a secure password while signing up or changing the password.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="secure_password" @if (gs('secure_password')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Verificação KYC</p>
                                    <p class="mb-0">
                                        <small>If you enable <span class="fw-bold">KYC (Know Your Client)</span> module, users must have to submit <a href="{{ route('admin.kyc.setting') }}">the required data</a>. Otherwise, any money out transaction will be prevented by this system.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="kv" @if (gs('kv')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Verificação de E-mail</p>
                                    <p class="mb-0">
                                        <small>
                                            If you enable <span class="fw-bold">E-mail Verification</span>, users have to verify their email to access the dashboard. A 6-digit verification code will be sent to their email to be verified.
                                            <br>
                                            <span class="fw-bold"><i>Note:</i></span> <i>Make sure that the <span class="fw-bold">E-mail Notificação </span> module is enabled</i>
                                        </small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="ev" @if (gs('ev')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Notificações por E-mail</p>
                                    <p class="mb-0">
                                        <small>If you enable this module, the system will send emails to users where needed. Otherwise, no email will be sent. <code>So be sure before disabling this module that, the system doesn't need to send any emails.</code></small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="en" @if (gs('en')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Verificação de Celular</p>
                                    <p class="mb-0">
                                        <small>
                                            If you enable <span class="fw-bold">Celular Verification</span>, users have to verify their mobile to access the dashboard. A 6-digit verification code will be sent to their mobile to be verified.
                                            <br>
                                            <span class="fw-bold"><i>Note:</i></span> <i>Make sure that the <span class="fw-bold">SMS Notificação </span> module is enabled</i>
                                        </small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="sv" @if (gs('sv')) checked @endif>
                                </div>
                            </li>


                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Notificações por SMS</p>
                                    <p class="mb-0">
                                        <small>If you enable this module, the system will send SMS to users where needed. Otherwise, no SMS will be sent. <code>So be sure before disabling this module that, the system doesn't need to send any SMS.</code></small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="sn" @if (gs('sn')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Notificações Push</p>
                                    <p class="mb-0">
                                        <small>
                                            If you enable this module, the system will send push notifications to users. Otherwise, no push notification will be sent.
                                            <a href="{{ route('admin.setting.notification.push') }}">Setting here</a>
                                        </small>
                                    </p>
                                </div>
                                <div class="form-group">
                                     <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="pn"
                                         @if (gs('pn')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Opção de Idioma</p>
                                    <p class="mb-0">
                                        <small>If you enable this module, users can change the language according to their needs.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="multi_language" @if (gs('multi_language')) checked @endif>
                                </div>
                            </li>

                            <li class="list-group-item d-flex flex-wrap flex-sm-nowrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <p class="fw-bold mb-0">Programa de Indicação</p>
                                    <p class="mb-0">
                                        <small>Here, you can enable or disable the Referral program. After disabling this, the Referral program will no longer work on this system.</small>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="Ativar" data-off="Desativar" name="referral_program" @if (gs('referral_program')) checked @endif>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .toggle.btn-lg {
            height: 37px !important;
            min-height: 37px !important;
        }

        .toggle-handle {
            width: 25px !important;
            padding: 0;
        }

        .form-group {
            width: 125px;
            margin-bottom: 0;
            flex-shrink: 0
        }

        .list-group-item:hover {
            background-color: #F7F7F7
        }
    </style>
@endpush
