@extends('admin.layouts.app')

@section('panel')

    <div class="entries-card">
        <div class="entries-header">
            <h4><i class="fas fa-list-ol"></i> Entradas nos Bolões</h4>
            <a href="{{ route('admin.fantasy_leagues.index') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: #fff; border-radius: 8px;">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="entries-stats">
            @php
                $totalReal = \App\Models\FantasyTeam::whereNotNull('user_id')->count();
                $totalBots = \App\Models\FantasyTeam::whereNotNull('bot_user_id')->count();
                $todayReal = \App\Models\FantasyTeam::whereNotNull('user_id')->whereDate('created_at', today())->count();
            @endphp
            <div class="entries-stat">
                <div class="entries-stat-value">{{ $totalReal }}</div>
                <div class="entries-stat-label">Reais Total</div>
            </div>
            <div class="entries-stat">
                <div class="entries-stat-value">{{ $totalBots }}</div>
                <div class="entries-stat-label">Bots Total</div>
            </div>
            <div class="entries-stat">
                <div class="entries-stat-value" style="color: #22c55e;">{{ $todayReal }}</div>
                <div class="entries-stat-label">Reais Hoje</div>
            </div>
        </div>

        <form method="GET" class="entries-filters">
            <select name="league_id" onchange="this.form.submit()">
                <option value="">Todos os Bolões</option>
                @foreach($leagues as $id => $name)
                    <option value="{{ $id }}" @selected(request('league_id') == $id)>{{ $name }}</option>
                @endforeach
            </select>
            <select name="type" onchange="this.form.submit()">
                <option value="">Todos os Tipos</option>
                <option value="real" @selected(request('type') === 'real')>🟢 Reais</option>
                <option value="bot" @selected(request('type') === 'bot')>🤖 Bots</option>
            </select>
        </form>

        <div style="overflow-x: auto;">
            <table class="entries-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Usuário</th>
                        <th>Bolão</th>
                        <th>Entrada</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $isBot = !empty($entry->bot_user_id);
                            $userName = 'Desconhecido';
                            $isPremium = false;
                            if (!$isBot && $entry->user) {
                                $userName = $entry->user->username ?? $entry->user->name ?? 'User #' . $entry->user_id;
                                $isPremium = $entry->user->isPremium();
                            } elseif ($isBot && $entry->botUser) {
                                $userName = $entry->botUser->username ?? 'Bot';
                                $isPremium = $entry->botUser->isPremium();
                            }
                            $league = $entry->fantasyLeague;
                            $leaguePrice = $league ? $league->price : 0;
                        @endphp
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>
                                @if($isBot)
                                    <span class="badge-bot">🤖 BOT</span>
                                @else
                                    <span class="badge-real">🟢 REAL</span>
                                @endif
                            </td>
                            <td>
                                {{ $userName }}
                                @if($isPremium)
                                    <span class="badge-premium-sm" title="Premium">👑</span>
                                @endif
                            </td>
                            <td>
                                @if($league)
                                    <strong>{{ $league->name }}</strong>
                                    <br><span class="entry-league-name">ID: {{ $league->id }}</span>
                                @else
                                    <span style="color: #ef4444;">Liga removida</span>
                                @endif
                            </td>
                            <td>
                                @if($leaguePrice > 0)
                                    <strong style="color: #22c55e;">R$ {{ number_format($leaguePrice, 2, ',', '.') }}</strong>
                                @else
                                    <span style="color: #94a3b8;">Grátis</span>
                                @endif
                            </td>
                            <td>
                                {{ $entry->created_at->format('d/m/Y H:i') }}
                                <br><small style="color: #64748b;">{{ $entry->created_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">
                                Nenhuma entrada encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
            <div style="padding: 1rem 1.5rem; display: flex; justify-content: center;">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
@endsection
