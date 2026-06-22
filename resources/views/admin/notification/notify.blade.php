@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.setting.notification.notify.send') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Enviar Para')</label>
                                <select name="target" class="form-control" id="target">
                                    <option value="all">@lang('Todos os Usuários')</option>
                                    <option value="premium">@lang('Somente Premium')</option>
                                    <option value="specific">@lang('Usuário Específico')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="specific_user_div" style="display: none;">
                            <div class="form-group">
                                <label>@lang('Usuário (Username ou Email)')</label>
                                <input type="text" name="user_identifier" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('Canais de Envio')</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channel[]" value="push" id="checkPush" checked>
                                <label class="form-check-label" for="checkPush">Push Notification (Web/App)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channel[]" value="email" id="checkEmail">
                                <label class="form-check-label" for="checkEmail">E-mail</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channel[]" value="sms" id="checkSms">
                                <label class="form-check-label" for="checkSms">SMS</label>
                            </div>
                            {{-- Whatsapp future implementation --}}
                            {{-- <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="channel[]" value="whatsapp" id="checkWhatsapp">
                                <label class="form-check-label" for="checkWhatsapp">WhatsApp</label>
                            </div> --}}
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('Título / Assunto')</label>
                        <input type="text" name="subject" class="form-control" placeholder="Título da notificação ou assunto do e-mail" required>
                    </div>

                    <div class="form-group">
                        <label>@lang('Mensagem')</label>
                        <textarea name="message" rows="5" class="form-control" placeholder="Digite sua mensagem aqui..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>@lang('Imagem (Opcional - Para Push)')</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>@lang('Link de Destino (Opcional)')</label>
                        <input type="url" name="url" class="form-control" placeholder="https://..." value="{{ url('/') }}">
                    </div>

                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Enviar Notificação')</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function($){
        "use strict";
        $('#target').on('change', function(){
            if($(this).val() == 'specific'){
                $('#specific_user_div').show();
            }else{
                $('#specific_user_div').hide();
            }
        });
    })(jQuery);
</script>
@endpush
