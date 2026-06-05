@extends('admin.layouts.app')

@section('panel')
    <style>
        .fantasy-index-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .fantasy-index-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(249, 115, 22, 0.2);
            overflow: hidden;
        }
        
        .fantasy-index-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 1.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .fantasy-index-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .fantasy-index-header h5 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .fantasy-table-wrapper {
            overflow-x: auto;
        }
        
        .fantasy-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .fantasy-table thead {
            background: rgba(30, 41, 59, 0.6);
        }
        
        .fantasy-table thead th {
            padding: 1rem 1.25rem;
            color: #f97316;
            font-weight: 700;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid rgba(249, 115, 22, 0.3);
        }
        
        .fantasy-table tbody tr {
            background: rgba(30, 41, 59, 0.3);
            transition: all 0.3s ease;
        }
        
        .fantasy-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.5);
            box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.3);
        }
        
        .fantasy-table tbody td {
            padding: 1.25rem 1.25rem;
            color: #e2e8f0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 0.95rem;
        }
        
        .fantasy-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .fantasy-league-name {
            font-weight: 700;
            color: #fff;
            font-size: 1rem;
        }

        .fantasy-empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #94a3b8;
        }
        
        .fantasy-empty-state i {
            font-size: 4rem;
            color: rgba(249, 115, 22, 0.3);
            margin-bottom: 1rem;
        }
        
        .fantasy-empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }

        /* Custom buttons for notifications */
        .notify-btn-wrapper {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .notify-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            background: rgba(15, 23, 42, 0.6);
        }

        /* Email Button */
        .notify-action-btn.email {
            border-color: #f97316;
            color: #f97316;
        }
        .notify-action-btn.email:hover, .notify-action-btn.email.active {
            background: #f97316;
            color: #fff;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        /* SMS Button */
        .notify-action-btn.sms {
            border-color: #3b82f6;
            color: #3b82f6;
        }
        .notify-action-btn.sms:hover, .notify-action-btn.sms.active {
            background: #3b82f6;
            color: #fff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Push Button */
        .notify-action-btn.push {
            border-color: #10b981;
            color: #10b981;
        }
        .notify-action-btn.push:hover, .notify-action-btn.push.active {
            background: #10b981;
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Send Button */
        .notify-action-btn.send {
            border-color: #f43f5e;
            color: #f43f5e;
            margin-left: 8px;
        }
        .notify-action-btn.send:hover {
            background: #f43f5e;
            color: #fff;
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
        }

        .status-badge {
            display: inline-flex;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        .status-badge.on { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
        .status-badge.off { background: #ef4444; }
    </style>

    <div class="fantasy-index-wrapper">
        <div class="fantasy-index-card">
            <div class="fantasy-index-header">
                <h5><i class="las la-bell"></i> Modelos de Notificação</h5>
            </div>

            <div class="fantasy-table-wrapper">
                <table class="fantasy-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Assunto</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>
                                    <span class="fantasy-league-name">{{ __($template->name) }}</span>
                                </td>
                                <td>{{ __($template->subject) }}</td>
                                <td>
                                    <div class="notify-btn-wrapper">
                                        <a href="{{ route('admin.setting.notification.template.edit', ['email',$template->id]) }}" 
                                           class="notify-action-btn email {{ $template->email_status == Status::ENABLE ? 'active' : '' }}" title="Editar Email">
                                            <i class="las la-envelope"></i> Email
                                            <span class="status-badge {{ $template->email_status == Status::ENABLE ? 'on' : 'off' }}"></span>
                                        </a>

                                        <a href="{{ route('admin.setting.notification.template.edit', ['sms',$template->id]) }}" 
                                           class="notify-action-btn sms {{ $template->sms_status == Status::ENABLE ? 'active' : '' }}" title="Editar SMS">
                                            <i class="las la-sms"></i> SMS
                                            <span class="status-badge {{ $template->sms_status == Status::ENABLE ? 'on' : 'off' }}"></span>
                                        </a>

                                        <a href="{{ route('admin.setting.notification.template.edit', ['push',$template->id]) }}" 
                                           class="notify-action-btn push {{ $template->push_status == Status::ENABLE ? 'active' : '' }}" title="Editar Push">
                                            <i class="las la-bell"></i> Push
                                            <span class="status-badge {{ $template->push_status == Status::ENABLE ? 'on' : 'off' }}"></span>
                                        </a>

                                        <a href="{{ route('admin.setting.notification.template.send', $template->id) }}" 
                                           class="notify-action-btn send" 
                                           onclick="return confirm('Tem certeza que deseja enviar esta notificação para TODOS os usuários ativos?')"
                                           title="Enviar para Todos">
                                            <i class="las la-paper-plane"></i> Enviar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="fantasy-empty-state">
                                        <i class="las la-bell-slash"></i>
                                        <p>Nenhum modelo de notificação encontrado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
