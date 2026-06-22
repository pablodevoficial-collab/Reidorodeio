@extends('admin.layouts.app')
@push('topBar')
  @include('admin.notification.top_bar')
@endpush

@section('panel')
    <style>
        .fantasy-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .fantasy-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(16, 185, 129, 0.2); /* Verde para Push */
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .fantasy-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); /* Verde para Push */
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .fantasy-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .fantasy-title {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 1;
        }

        .fantasy-nav {
            display: flex;
            gap: 0.5rem;
            z-index: 1;
        }

        .fantasy-nav-btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .fantasy-nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            transform: translateY(-2px);
        }

        .fantasy-nav-btn.active {
            background: #fff;
            color: #059669; /* Verde */
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .fantasy-body {
            padding: 2rem;
        }

        .fantasy-form-group {
            margin-bottom: 1.5rem;
        }

        .fantasy-label {
            display: block;
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .fantasy-label i {
            color: #10b981; /* Verde */
        }

        .fantasy-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            transition: all 0.3s;
        }

        .fantasy-input:focus {
            outline: none;
            border-color: #10b981;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .fantasy-input small {
            display: block;
            margin-top: 0.5rem;
            color: #94a3b8;
        }

        .fantasy-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .fantasy-table th {
            background: rgba(30, 41, 59, 0.8);
            color: #10b981;
            padding: 1rem;
            text-align: left;
            font-weight: 700;
        }

        .fantasy-table td {
            background: rgba(30, 41, 59, 0.4);
            color: #e2e8f0;
            padding: 0.75rem 1rem;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }

        .short-code-badge {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
        }

        .submit-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 700;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        textarea.fantasy-input {
            min-height: 200px;
        }
    </style>

    <div class="fantasy-wrapper">
        <form action="{{ route('admin.setting.notification.template.update',['push',$template->id]) }}" method="post">
            @csrf
            
            <div class="fantasy-card">
                <div class="fantasy-header">
                    <h5 class="fantasy-title">
                        <i class="las la-bell"></i> Editando: {{ $template->name }}
                    </h5>
                    <div class="fantasy-nav">
                        <a href="{{ route('admin.setting.notification.template.edit', ['email', $template->id]) }}" class="fantasy-nav-btn">
                            <i class="las la-envelope"></i> Email
                        </a>
                        <a href="{{ route('admin.setting.notification.template.edit', ['sms', $template->id]) }}" class="fantasy-nav-btn">
                            <i class="las la-sms"></i> SMS
                        </a>
                        <a href="{{ route('admin.setting.notification.template.edit', ['push', $template->id]) }}" class="fantasy-nav-btn active">
                            <i class="las la-bell"></i> Push
                        </a>
                    </div>
                </div>

                <div class="fantasy-body">
                    <!-- Shortcodes Section -->
                    <div class="mb-4">
                        <p class="text-white mb-2"><i class="las la-code"></i> Códigos Disponíveis:</p>
                        <div class="table-responsive">
                            <table class="fantasy-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Descrição</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($template->shortcodes as $shortcode => $key)
                                        <tr>
                                            <td><span class="short-code-badge">@php echo "{{". $shortcode ."}}"  @endphp</span></td>
                                            <td>{{ __($key) }}</td>
                                        </tr>
                                    @endforeach
                                    @foreach(gs('global_shortcodes') as $shortCode => $codeDetails)
                                        <tr>
                                            <td><span class="short-code-badge">@{{@php echo $shortCode @endphp}}</span></td>
                                            <td>{{ __($codeDetails) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="fantasy-form-group">
                                <label class="fantasy-label">Título da Notificação</label>
                                <input type="text" class="fantasy-input" placeholder="Título da Notificação" name="push_title" value="{{ $template->push_title }}">
                                <small>Deixe vazio para usar o título global.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fantasy-form-group">
                                <label class="fantasy-label">Status</label>
                                <input type="checkbox" data-height="46px" data-width="100%" data-onstyle="-success"
                                   data-offstyle="-danger" data-bs-toggle="toggle" data-on="Ativado"
                                   data-off="Desativado" name="push_status"
                                   @if($template->push_status) checked @endif>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="fantasy-form-group">
                                <label class="fantasy-label">Mensagem</label>
                                <textarea name="push_body" class="fantasy-input" placeholder="Sua mensagem usando short-codes" required>{{ $template->push_body }}</textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="las la-save"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.setting.notification.templates') }}" />
@endpush