@extends('admin.layouts.app')

@section('panel')
    <style>
        .x1-details-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        .x1-details-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(249, 115, 22, 0.2);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .x1-details-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .x1-details-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .x1-details-header h5 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .x1-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
            background: rgba(30, 41, 59, 0.4);
        }

        .x1-info-item label {
            display: block;
            color: #94a3b8;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .x1-info-item span {
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .x1-metadata-box {
            padding: 2rem;
            background: rgba(15, 23, 42, 0.4);
            border-top: 1px solid rgba(249, 115, 22, 0.1);
        }
        
        .x1-metadata-box h6 {
            color: #f97316;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .x1-metadata-content {
            background: #0f172a;
            padding: 1rem;
            border-radius: 8px;
            color: #cbd5e1;
            font-family: monospace;
            font-size: 0.85rem;
            overflow-x: auto;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Participant Table Styles (Matches Index) */
        .x1-participants-wrapper {
            background: rgba(30, 41, 59, 0.2);
            padding: 0; 
        }

        .x1-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .x1-table thead {
            background: rgba(30, 41, 59, 0.6);
        }

        .x1-table thead th {
            padding: 1rem 1.25rem;
            color: #f97316;
            font-weight: 700;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid rgba(249, 115, 22, 0.3);
        }

        .x1-table tbody tr {
            background: rgba(30, 41, 59, 0.3);
            transition: all 0.3s ease;
        }

        .x1-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.5);
            box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.3);
        }

        .x1-table tbody td {
            padding: 1.25rem 1.25rem;
            color: #e2e8f0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .x1-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.02em;
        }

        .badge-role-host { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); }
        .badge-role-opponent { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); }
        
        .badge-payment-paid, .badge-payment-completed { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .badge-payment-pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
        
        .badge-result-winner { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3); }
        .badge-result-loser { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }

        .btn-prize-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-prize-paid:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
        }

        .btn-prize-paid:disabled {
            background: #64748b;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .prize-paid-badge {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-terminate {
            background: #fff;
            color: #ef4444;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-decoration: none;
            z-index: 2;
        }

        .btn-terminate:hover {
            background: #fef2f2;
            transform: translateY(-2px);
            color: #dc2626;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .pix-info {
            background: rgba(0, 0, 0, 0.2);
            padding: 0.5rem;
            border-radius: 6px;
            border-left: 3px solid #f97316;
            font-size: 0.85rem;
        }
        
        .pix-key {
            font-family: monospace;
            color: #f97316;
            word-break: break-all;
        }
        
        .x1-result-card {
            background: linear-gradient(135deg, #065f46 0%, #064e3b 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .x1-result-card .x1-details-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .prize-highlight {
            font-size: 1.5rem;
            font-weight: 700;
            color: #10b981;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .status-open { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .status-in_progress { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .status-completed { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .status-cancelled { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .status-closed { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
    </style>

    @php
        // Determinar vencedor e perdedor do X1Result
        $winnerUserId = $x1Result?->winner_user_id;
        $payload = $x1Result?->payload ?? [];
        $loserUserId = $payload['loser_user_id'] ?? null;
        $prizeAmount = $payload['prize_amount'] ?? $room->prize_total;
        $resultType = $payload['result_type'] ?? null;
        $resultNotes = $payload['notes'] ?? null;
    @endphp

    <div class="x1-details-wrapper">
        <!-- Room Info Card -->
        <div class="x1-details-card">
            <div class="x1-details-header">
                <h5><i class="las la-info-circle"></i> Detalhes da Sala #{{ $room->id }}</h5>
                <div>
                    @if($room->status === 'open')
                        <form method="POST" action="{{ route('admin.x1.close', $room->id) }}" style="display:inline" onsubmit="return confirm('Tem certeza que deseja encerrar manualmente esta sala?');">
                            @csrf
                            <button class="btn-terminate">
                                <i class="las la-times-circle"></i> Encerrar Sala
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="x1-info-grid">
                <div class="x1-info-item">
                    <label>Nome da Sala</label>
                    <span>{{ $room->name ?: 'Sala X1 #'.$room->id }}</span>
                </div>
                <div class="x1-info-item">
                    <label>Criado por (Criador)</label>
                    @php
                        $hostUser = $room->host;
                        $hostName = $hostUser->username ?? $hostUser->name ?? 'N/A';
                    @endphp
                    <span>{{ $hostName }}</span>
                </div>
                <div class="x1-info-item">
                    <label>Modalidade</label>
                    <span>{{ $room->modalidade->nome ?? 'N/A' }}</span>
                </div>
                <div class="x1-info-item">
                    <label>Valor de Entrada</label>
                    <span style="color: #10b981;">R$ {{ number_format($room->valor_entrada, 2, ',', '.') }}</span>
                </div>
                <div class="x1-info-item">
                    <label>Taxa Plataforma</label>
                    <span>{{ $room->fee_percent ?? 20 }}%</span>
                </div>
                <div class="x1-info-item">
                    <label>Prêmio Total</label>
                    <span class="prize-highlight">R$ {{ number_format($room->prize_total ?? ($room->valor_entrada * 2 * (1 - ($room->fee_percent ?? 20) / 100)), 2, ',', '.') }}</span>
                </div>
                <div class="x1-info-item">
                    <label>Status Atual</label>
                    @php
                        $statusLabel = match($room->status) {
                            'open' => 'Aguardando Oponente',
                            'pending' => 'Pagamento Pendente',
                            'pending_payment' => 'Pagamento Pendente',
                            'in_progress' => 'Em Andamento',
                            'completed' => 'Finalizada',
                            'closed' => 'Fechada',
                            'cancelled' => 'Cancelada',
                            default => $room->status ?? 'Desconhecido'
                        };
                        $statusIcon = match($room->status) {
                            'open' => 'la-hourglass-half',
                            'pending', 'pending_payment' => 'la-clock',
                            'in_progress' => 'la-play-circle',
                            'completed' => 'la-check-circle',
                            'closed' => 'la-times-circle',
                            'cancelled' => 'la-ban',
                            default => 'la-question-circle'
                        };
                    @endphp
                    <span class="status-badge status-{{ $room->status }}">
                        <i class="las {{ $statusIcon }}"></i> {{ $statusLabel }}
                    </span>
                </div>
                <div class="x1-info-item">
                    <label>Criado em</label>
                    <span>{{ $room->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        @if($x1Result)
        <!-- Result Card -->
        <div class="x1-details-card x1-result-card">
            <div class="x1-details-header">
                <h5><i class="las la-trophy"></i> Resultado do X1</h5>
            </div>
            <div class="x1-info-grid">
                <div class="x1-info-item">
                    <label>🏆 Vencedor</label>
                    @php
                        $winner = \App\Models\User::find($winnerUserId);
                    @endphp
                    <span style="color: #10b981; font-size: 1.25rem;">
                        {{ $winner->username ?? $winner->name ?? 'ID: '.$winnerUserId }}
                    </span>
                </div>
                <div class="x1-info-item">
                    <label>Prêmio Pago</label>
                    <span class="prize-highlight">R$ {{ number_format($prizeAmount, 2, ',', '.') }}</span>
                </div>
                <div class="x1-info-item">
                    <label>Tipo de Resultado</label>
                    @php
                        $resultTypeLabel = match($resultType) {
                            'disqualification' => '🚫 Desqualificação',
                            'score' => '📊 Por Pontuação',
                            'forfeit' => '🏳️ Desistência',
                            'admin' => '👨‍💼 Decisão Admin',
                            default => $resultType ?? 'Normal'
                        };
                    @endphp
                    <span>{{ $resultTypeLabel }}</span>
                </div>
                <div class="x1-info-item">
                    <label>Processado em</label>
                    <span>{{ $x1Result->processed_at?->format('d/m/Y H:i') ?? $x1Result->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($resultNotes)
                <div class="x1-info-item" style="grid-column: span 2;">
                    <label>Observações</label>
                    <span style="font-size: 0.95rem; color: #cbd5e1;">{{ $resultNotes }}</span>
                </div>
                @endif

                <!-- Seção de Pagamento do Prêmio -->
                <div class="x1-info-item" style="grid-column: span 2; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem; margin-top: 0.5rem;">
                    <label style="margin-bottom: 0.75rem;">💰 Status do Pagamento PIX</label>
                    @if($x1Result->prize_paid_at)
                        <div class="prize-paid-badge">
                            <i class="las la-check-circle"></i>
                            Pago em {{ $x1Result->prize_paid_at->format('d/m/Y H:i') }}
                        </div>
                        @if($winner && $winner->pix_key)
                        <div style="margin-top: 0.75rem; padding: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 8px; border-left: 3px solid #10b981;">
                            <div style="color: #94a3b8; font-size: 0.8rem; text-transform: uppercase;">PIX {{ $winner->pix_key_type ?? '' }}</div>
                            <div style="color: #10b981; font-family: monospace; word-break: break-all;">{{ $winner->pix_key }}</div>
                        </div>
                        @endif
                    @else
                        @if($winner && $winner->pix_key)
                        <div style="margin-bottom: 1rem; padding: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 8px; border-left: 3px solid #f97316;">
                            <div style="color: #94a3b8; font-size: 0.8rem; text-transform: uppercase;">PIX do Vencedor ({{ $winner->pix_key_type ?? '' }})</div>
                            <div style="color: #f97316; font-family: monospace; word-break: break-all; font-size: 1.1rem;">{{ $winner->pix_key }}</div>
                        </div>
                        @else
                        <div style="margin-bottom: 1rem; padding: 0.75rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border-left: 3px solid #ef4444;">
                            <span style="color: #f87171;">⚠️ Vencedor não cadastrou chave PIX</span>
                        </div>
                        @endif
                        <form method="POST" action="{{ route('admin.x1.mark-prize-paid', $room->id) }}" onsubmit="return confirm('Confirma que o PIX de R$ {{ number_format($prizeAmount, 2, ',', '.') }} foi realizado para {{ $winner->username ?? $winner->name ?? 'o vencedor' }}?');">
                            @csrf
                            <button type="submit" class="btn-prize-paid">
                                <i class="las la-check-double"></i>
                                PGT. Feito (PIX Realizado)
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Participants Card -->
        <div class="x1-details-card">
            <div class="x1-details-header" style="background: linear-gradient(135deg, #334155 0%, #1e293b 100%);">
                <h5><i class="las la-users"></i> Participantes & Financeiro</h5>
            </div>
            
            <div class="x1-participants-wrapper">
                <div class="table-responsive">
                    <table class="x1-table">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Usuário</th>
                                <th>Função</th>
                                <th>Pagamento</th>
                                <th>Chave PIX</th>
                                <th>Competidor</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($room->participants as $p)
                                @php
                                    $pUser = $p->user;
                                    $isWinner = $winnerUserId && $p->user_id == $winnerUserId;
                                    $isLoser = $loserUserId && $p->user_id == $loserUserId;
                                @endphp
                                <tr>
                                    <td><span style="color: #64748b;">#{{ $p->id }}</span></td>
                                    <td>
                                        <div style="font-weight: 600; color: #fff;">
                                            {{ $pUser->username ?? $pUser->name ?? 'Usuário #'.$p->user_id }}
                                        </div>
                                        <div style="font-size: 0.8rem; color: #94a3b8;">{{ $pUser->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($p->is_host)
                                            <span class="x1-badge badge-role-host">CRIADOR</span>
                                        @else
                                            <span class="x1-badge badge-role-opponent">OPONENTE</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $payStatus = $p->payment_status ?? 'pending';
                                        @endphp
                                        @if(in_array($payStatus, ['paid', 'completed', 'approved']))
                                            <span class="x1-badge badge-payment-completed">
                                                <i class="las la-check-circle" style="margin-right:4px"></i> concluído
                                            </span>
                                        @else
                                            <span class="x1-badge badge-payment-pending">{{ $payStatus }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pUser && $pUser->pix_key)
                                            <div class="pix-info">
                                                <div style="color: #cbd5e1; font-size: 0.75rem; text-transform: uppercase;">
                                                    {{ $pUser->pix_key_type ?? 'PIX' }}
                                                </div>
                                                <div class="pix-key">{{ $pUser->pix_key }}</div>
                                            </div>
                                        @else
                                            <span style="color: #64748b; font-style: italic;">Não cadastrada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->competitor)
                                            <span style="color: #38bdf8; font-weight: 600;">
                                                {{ $p->competitor->nome }}
                                            </span>
                                        @elseif($p->competitorGroup)
                                            <span style="color: #c084fc; font-weight: 600;">
                                                {{ $p->competitorGroup->nome ?: $p->competitorGroup->members->pluck('nome')->implode(' + ') }}
                                            </span>
                                        @else
                                            <span style="color: #64748b;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($room->status === 'completed' || $x1Result || $room->closed_at)
                                            @if($isWinner)
                                                <span class="x1-badge badge-result-winner">
                                                    <i class="las la-trophy" style="margin-right:4px"></i> VENCEDOR
                                                </span>
                                            @elseif($isLoser)
                                                <span class="x1-badge badge-result-loser">PERDEDOR</span>
                                            @else
                                                <span style="color: #64748b;">-</span>
                                            @endif
                                        @else
                                            <span style="color: #64748b;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div style="padding: 2rem; text-align: center; color: #94a3b8;">
                                            Nenhum participante encontrado.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($room->metadata)
        <div class="x1-details-card">
            <div class="x1-details-header" style="background: linear-gradient(135deg, #475569 0%, #334155 100%);">
                <h5><i class="las la-code"></i> Metadados Técnicos</h5>
            </div>
            <div class="x1-metadata-box">
                <div class="x1-metadata-content">
                    {{ json_encode($room->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.x1.index') }}" />
@endpush
