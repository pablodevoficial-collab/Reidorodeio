@extends('admin.layouts.app')

@section('panel')
    <style>
        .rodeios-create-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        .rodeios-create-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(249, 115, 22, 0.2);
            overflow: hidden;
        }

        .rodeios-create-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .rodeios-create-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .rodeios-create-header h5 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .rodeios-create-header h5 i {
            font-size: 1.5rem;
        }

        .rodeios-create-body {
            padding: 2.5rem;
        }

        .rodeios-section {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 12px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(249, 115, 22, 0.15);
            transition: all 0.3s ease;
        }

        .rodeios-section:hover {
            border-color: rgba(249, 115, 22, 0.4);
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.1);
        }

        .rodeios-section-title {
            color: #f97316;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(249, 115, 22, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rodeios-section-title i {
            font-size: 1rem;
        }

        .rodeios-form-group {
            margin-bottom: 1.5rem;
        }

        .rodeios-form-group label {
            display: block;
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .rodeios-form-group label i {
            color: #f97316;
            margin-right: 0.35rem;
            font-size: 0.85rem;
        }

        .rodeios-form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .rodeios-form-control:focus {
            outline: none;
            border-color: #f97316;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }

        .rodeios-form-control::placeholder {
            color: #64748b;
        }

        .rodeios-form-control option {
            background: #1e293b;
            color: #e2e8f0;
            padding: 0.5rem;
        }

        .rodeios-form-help {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #94a3b8;
            font-style: italic;
        }

        .rodeios-form-help i {
            margin-right: 0.25rem;
            color: #f97316;
        }

        .rodeios-file-input {
            position: relative;
            overflow: hidden;
        }

        .rodeios-file-input input[type=\"file\"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .rodeios-file-label {
            display: block;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px dashed rgba(249, 115, 22, 0.4);
            border-radius: 8px;
            color: #94a3b8;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rodeios-file-label:hover {
            border-color: #f97316;
            background: rgba(15, 23, 42, 0.8);
            color: #e2e8f0;
        }

        .rodeios-file-label i {
            margin-right: 0.5rem;
            color: #f97316;
        }

        .rodeios-submit-footer {
            padding: 2rem;
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid rgba(249, 115, 22, 0.2);
        }

        .rodeios-submit-btn {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.3);
        }

        .rodeios-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.5);
        }

        .rodeios-submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .rodeios-create-body {
                padding: 1.5rem;
            }

            .rodeios-section {
                padding: 1.25rem;
            }

            .rodeios-create-header h5 {
                font-size: 1.35rem;
            }
        }
    </style>

    <div class="rodeios-create-wrapper">
        <div class="rodeios-create-card">
            <div class="rodeios-create-header">
                <h5><i class="las la-plus-circle"></i> @lang('Criar Novo Rodeio')</h5>
            </div>

            <form method="post" action="{{ route('admin.rodeios.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="rodeios-create-body">
                    <div class="rodeios-section">
                        <div class="rodeios-section-title">
                            <i class="las la-info-circle"></i>
                            @lang('Informações do Rodeio')
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="rodeios-form-group">
                                    <label><i class="las la-flag"></i> @lang('Nome')</label>
                                    <input type="text" name="nome" value="{{ old('nome') }}" class="rodeios-form-control" placeholder="Ex: Rodeio Rei do Laço" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="rodeios-form-group">
                                    <label><i class="las la-map-marker-alt"></i> @lang('Cidade')</label>
                                    <input type="text" name="cidade" value="{{ old('cidade') }}" class="rodeios-form-control" placeholder="Ex: Barretos - SP" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="rodeios-form-group">
                                    <label><i class="las la-calendar"></i> @lang('Data e Hora de Início')</label>
                                    <input type="datetime-local" name="data_inicio" value="{{ old('data_inicio') }}" class="rodeios-form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="rodeios-form-group">
                                    <label><i class="las la-calendar-check"></i> @lang('Data e Hora de Encerramento')</label>
                                    <input type="datetime-local" name="data_fim" value="{{ old('data_fim') }}" class="rodeios-form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="rodeios-form-group">
                                    <label><i class="las la-toggle-on"></i> @lang('Status')</label>
                                    <select name="status" class="rodeios-form-control" required>
                                        <option value="ativo" @selected(old('status') == 'ativo')>@lang('Ativo')</option>
                                        <option value="inativo" @selected(old('status') == 'inativo')>@lang('Inativo')</option>
                                    </select>
                                    <small class="rodeios-form-help"><i class="las la-info-circle"></i> @lang('Define se o rodeio aparecerá no sistema')</small>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="rodeios-form-group">
                                    <label><i class="las la-align-left"></i> @lang('Descrição')</label>
                                    <textarea name="descricao" rows="4" class="rodeios-form-control" placeholder="Detalhes do rodeio...">{{ old('descricao') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="rodeios-form-group">
                                    <label><i class="las la-image"></i> @lang('Imagem do Rodeio')</label>
                                    <div class="rodeios-file-input">
                                        <input type="file" name="imagem" accept="image/*" id="rodeioImagem">
                                        <label class="rodeios-file-label" for="rodeioImagem">
                                            <i class="las la-cloud-upload-alt"></i>
                                            <span id="fileName">@lang('Clique para selecionar uma imagem')</span>
                                        </label>
                                    </div>
                                    <small class="rodeios-form-help"><i class="las la-info-circle"></i> @lang('Formatos aceitos: JPG, JPEG, PNG (máx 2MB)')</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rodeios-submit-footer">
                    <button type="submit" class="rodeios-submit-btn">
                        <i class="las la-save"></i> @lang('Salvar Rodeio')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
(function () {
    const fileInput = document.getElementById('rodeioImagem');
    const fileNameSpan = document.getElementById('fileName');

    if (fileInput && fileNameSpan) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Clique para selecionar uma imagem';
            fileNameSpan.textContent = e.target.files[0] ? '📁 ' + fileName : fileName;
        });
    }
})();
</script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.rodeios.index') }}" />
@endpush
