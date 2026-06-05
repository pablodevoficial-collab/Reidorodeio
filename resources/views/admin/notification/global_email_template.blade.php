@extends('admin.layouts.app')
@section('panel')
    @push('topBar')
        @include('admin.notification.top_bar')
    @endpush
    <div class="row mb-none-30">
        @include('admin.notification.global_template_nav')
        
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.setting.notification.global.email.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="fw-bold">Remetente (Nome)</label>
                                            <input type="text" class="form-control form-control-lg" placeholder="Ex: Rei do Rodeio" name="email_from_name" value="{{ gs('email_from_name') }}" required>
                                            <small class="text-muted">Nome que aparecerá na caixa de entrada do usuário.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="fw-bold">Remetente (E-mail)</label>
                                            <input type="text" class="form-control form-control-lg" placeholder="Ex: no-reply@reidorodeio.com" name="email_from" value="{{ gs('email_from') }}" required>
                                            <small class="text-muted">Endereço de e-mail que enviará as mensagens.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-4">
                                <div class="row">
                                    <div class="col-xl-8">
                                        <div class="form-group">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="fw-bold mb-0">Template HTML do E-mail</label>
                                                <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#shortcodeModal">
                                                    <i class="las la-code"></i> Ver Shortcodes Disponíveis
                                                </button>
                                            </div>
                                            <div class="editor-wrapper">
                                                <textarea name="email_template" rows="10" class="form-control emailTemplateEditor" id="htmlInput" placeholder="Cole seu HTML aqui">{{ gs('email_template') }}</textarea>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="las la-info-circle"></i> Use o shortcode <span class="text--primary">@{{message}}</span> onde o conteúdo da notificação deve aparecer.
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="form-group">
                                            <label class="fw-bold">Pré-visualização</label>
                                            <div class="preview-container">
                                                <iframe id="iframePreview"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4 mb-0">
                            <button type="submit" class="btn btn--primary w-100 h-45">
                                <i class="las la-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Shortcode Modal -->
    <div class="modal fade" id="shortcodeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Shortcodes Globais</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @include('admin.notification.global_shortcodes')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .editor-wrapper {
            position: relative;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        .emailTemplateEditor {
            height: 500px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
            font-size: 14px;
            line-height: 1.5;
            padding: 15px;
            border: none;
            resize: none;
            background-color: #f8f9fa;
        }
        .emailTemplateEditor:focus {
            box-shadow: none;
            background-color: #fff;
        }
        .preview-container {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background: #fff;
            padding: 0;
            height: 500px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        #iframePreview {
            width: 100%;
            height: 100%;
            border: none;
            background: #fff;
        }
        .active-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background-color: #28c76f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            opacity: 0;
            transition: all 0.3s;
        }
        .notification-via.active .active-badge {
            opacity: 1;
        }
    </style>
@endpush

@push('script')
    <script>
        var iframe = document.getElementById('iframePreview');
        $(".emailTemplateEditor").on('input', function() {
            var htmlContent = document.getElementById('htmlInput').value;
            iframe.src = 'data:text/html;charset=utf-8,' + encodeURIComponent(htmlContent);
        }).trigger('input');
    </script>
@endpush
