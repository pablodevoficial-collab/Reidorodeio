@extends('admin.layouts.app')
@section('panel')
    @push('topBar')
        @include('admin.notification.top_bar')
    @endpush
    <div class='row'>
        <div class='col-md-12 mb-30'>
            <div class='card bl--5 border--primary'>
                <div class='card-body'>
                    <p class='text--primary'>Configurações para Web Push (VAPID). Gere suas chaves em sites como <a href='https://web-push-codelab.glitch.me/' target='_blank'>web-push-codelab.glitch.me</a> ou via terminal.</p>
                </div>
            </div>
        </div>
        <div class='col-md-12'>
            <div class='card'>
                <form method='POST'>
                    @csrf
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-md-12'>
                                <div class='form-group'>
                                    <label>Subject (URL ou Mailto)</label>
                                    <input type='text' class='form-control' placeholder='https://seusite.com ou mailto:admin@seusite.com' name='subject' value='{{ @gs('vapid_config')->subject }}' required>
                                    <small class='text-muted'>Identificador da sua aplicação para o serviço de Push.</small>
                                </div>
                            </div>
                            <div class='col-md-12'>
                                <div class='form-group'>
                                    <label>Public Key</label>
                                    <textarea class='form-control' rows='3' placeholder='VAPID Public Key' name='public_key' required>{{ @gs('vapid_config')->public_key }}</textarea>
                                </div>
                            </div>
                            <div class='col-md-12'>
                                <div class='form-group'>
                                    <label>Private Key</label>
                                    <input type='text' class='form-control' placeholder='VAPID Private Key' name='private_key' value='{{ @gs('vapid_config')->private_key }}' required>
                                </div>
                            </div>
                        </div>
                        <button type='submit' class='btn btn--primary w-100 h-45'>Salvar Configurações</button>
                    </div>
                </form>
            </div><!-- card end -->
        </div>
    </div>
@endsection
