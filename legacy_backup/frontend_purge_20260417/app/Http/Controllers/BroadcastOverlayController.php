<?php

namespace App\Http\Controllers;

use App\Models\Rodeio;
use App\Models\X1RoomInstance;
use App\Models\X1Result;
use App\Models\FantasyTeam;
use App\Models\FantasyLeague;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BroadcastOverlayController extends Controller
{
    /**
     * Show login form for broadcast page.
     */
    public function loginForm()
    {
        if (session('broadcast_authenticated')) {
            return redirect()->route('broadcast.overlay');
        }
        return view('broadcast.login');
    }

    /**
     * Handle broadcast login (uses admin credentials).
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admin = \App\Models\Admin::first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            session(['broadcast_authenticated' => true]);
            return redirect()->route('broadcast.overlay');
        }

        return back()->withErrors(['password' => 'Senha incorreta.']);
    }

    /**
     * Show the broadcast overlay page.
     */
    public function overlay()
    {
        if (!session('broadcast_authenticated')) {
            return redirect()->route('broadcast.login');
        }

        $activeRodeio = $this->getActiveRodeio();

        // Build stream embed URL
        $liveStreamEmbedUrl = null;
        $rawStreamUrl = $activeRodeio?->stream_url ?: env('LIVE_STREAM_URL');
        if ($rawStreamUrl) {
            if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|live\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $rawStreamUrl, $m)) {
                $liveStreamEmbedUrl = "https://www.youtube.com/embed/{$m[1]}?autoplay=1&mute=1&modestbranding=1&rel=0&controls=0";
            } else {
                $liveStreamEmbedUrl = $rawStreamUrl;
            }
        }

        return view('broadcast.overlay', compact('activeRodeio', 'liveStreamEmbedUrl'));
    }

    /**
     * API: live feed data for the broadcast overlay.
     */
    public function feedData(Request $request)
    {
        if (!session('broadcast_authenticated')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $activeRodeio = $this->getActiveRodeio();
        $rodeioId = $activeRodeio?->id;

        // ── X1 Rooms (open + in_progress) ──
        $x1Rooms = X1RoomInstance::whereIn('status', ['open', 'in_progress'])
            ->when($rodeioId, fn($q) => $q->where('rodeio_id', $rodeioId))
            ->with([
                'host', 'hostBot', 'opponentBot',
                'participants.user', 'participants.competitor',
                'modalidade', 'competitor'
            ])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(function ($room) {
                $hostParticipant = $room->participants->firstWhere('is_host', true);
                $opponentParticipant = $room->participants->firstWhere('is_host', false);

                // Host name/avatar (user or bot)
                $hostName = $room->host?->username ?? $room->hostBot?->username ?? 'Bot';
                $hostAvatar = $this->userAvatar($room->host);

                // Opponent name/avatar
                $oppUser = $opponentParticipant?->user;
                $oppName = null;
                $oppAvatar = null;
                if ($oppUser) {
                    $oppName = $oppUser->username;
                    $oppAvatar = $this->userAvatar($oppUser);
                } elseif ($room->opponentBot) {
                    $oppName = $room->opponentBot->username ?? 'Bot';
                } elseif ($room->status === 'in_progress') {
                    $oppName = 'Oponente';
                }

                // Prize calculation
                $prize = (float) $room->prize_total;
                if (!$prize && $room->valor_entrada) {
                    $total = (float) $room->valor_entrada * 2;
                    $fee = $total * ((float) ($room->fee_percent ?? 10) / 100);
                    $prize = round($total - $fee, 2);
                }

                return [
                    'id'              => $room->id,
                    'status'          => $room->status,
                    'valor'           => (float) $room->valor_entrada,
                    'prize'           => $prize,
                    'is_premium'      => (bool) $room->is_premium_room,
                    'modalidade'      => $room->modalidade?->nome ?? '—',
                    'host_name'       => $hostName,
                    'host_avatar'     => $hostAvatar,
                    'opponent_name'   => $oppName,
                    'opponent_avatar' => $oppAvatar,
                    'competitor'      => $hostParticipant?->competitor?->nome ?? $room->competitor?->nome ?? null,
                    'created_at'      => $room->created_at?->diffForHumans(),
                ];
            });

        // ── Latest bolão entries (last 12 fantasy teams created) ──
        $latestBolaoEntries = FantasyTeam::with(['user', 'botUser', 'fantasyLeague', 'competitorsRelation'])
            ->when($rodeioId, fn($q) => $q->whereHas('fantasyLeague', fn($q2) => $q2->where('rodeio_id', $rodeioId)))
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(function ($team) {
                $userName = $team->user?->username ?? $team->botUser?->username ?? 'Jogador';
                $userAvatar = $this->userAvatar($team->user);

                return [
                    'id'           => $team->id,
                    'user_name'    => $userName,
                    'user_avatar'  => $userAvatar,
                    'team_name'    => $team->team_name,
                    'league_name'  => $team->fantasyLeague?->name ?? '—',
                    'league_price' => (float) ($team->fantasyLeague?->price ?? 0),
                    'is_premium'   => (bool) $team->fantasyLeague?->is_premium,
                    'competitors'  => $team->competitorsRelation->map(fn($c) => [
                        'nome' => $c->nome,
                        'foto' => $c->foto_url ?? null,
                    ])->values(),
                    'created_at'   => $team->created_at?->diffForHumans(),
                ];
            });

        // ── Latest X1 winners ──
        $latestWinners = X1Result::with(['winner', 'room.modalidade'])
            ->whereNotNull('winner_user_id')
            ->when($rodeioId, fn($q) => $q->whereHas('room', fn($q2) => $q2->where('rodeio_id', $rodeioId)))
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(function ($result) {
                $prize = (float) ($result->room?->prize_total ?? 0);
                if (!$prize && $result->room) {
                    $total = (float) $result->room->valor_entrada * 2;
                    $fee = $total * ((float) ($result->room->fee_percent ?? 10) / 100);
                    $prize = round($total - $fee, 2);
                }

                return [
                    'id'            => $result->id,
                    'winner_name'   => $result->winner?->username ?? 'Desconhecido',
                    'winner_avatar' => $this->userAvatar($result->winner),
                    'prize'         => $prize,
                    'valor_entrada' => (float) ($result->room?->valor_entrada ?? 0),
                    'modalidade'    => $result->room?->modalidade?->nome ?? '—',
                    'finished_at'   => $result->created_at?->diffForHumans(),
                ];
            });

        // ── Top Winners Ranking (most prize money won in event) ──
        $topWinnersQuery = X1Result::select('winner_user_id')
            ->selectRaw('COUNT(*) as wins')
            ->selectRaw('SUM(COALESCE(x1_rooms.prize_total, x1_rooms.valor_entrada * 2 * (1 - COALESCE(x1_rooms.fee_percent, 10) / 100), 0)) as total_prize')
            ->join('x1_rooms', 'x1_results.x1_room_id', '=', 'x1_rooms.id')
            ->whereNotNull('winner_user_id');

        if ($rodeioId) {
            $topWinnersQuery->where('x1_rooms.rodeio_id', $rodeioId);
        }

        $topWinners = $topWinnersQuery
            ->groupBy('winner_user_id')
            ->orderByDesc('total_prize')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $user = User::find($row->winner_user_id);
                return [
                    'user_id'     => $row->winner_user_id,
                    'user_name'   => $user?->username ?? 'Desconhecido',
                    'user_avatar' => $this->userAvatar($user),
                    'wins'        => (int) $row->wins,
                    'total_prize' => (float) $row->total_prize,
                ];
            });

        // ── Stats summary ──
        $totalX1Active = X1RoomInstance::whereIn('status', ['open', 'in_progress'])
            ->when($rodeioId, fn($q) => $q->where('rodeio_id', $rodeioId))->count();
        $totalX1Finished = X1RoomInstance::whereIn('status', ['closed', 'finished'])
            ->when($rodeioId, fn($q) => $q->where('rodeio_id', $rodeioId))->count();
        $totalBolaoTeams = FantasyTeam::where('is_active', true)
            ->when($rodeioId, fn($q) => $q->whereHas('fantasyLeague', fn($q2) => $q2->where('rodeio_id', $rodeioId)))->count();
        $totalPrizePool = X1RoomInstance::whereIn('status', ['closed', 'finished'])
            ->when($rodeioId, fn($q) => $q->where('rodeio_id', $rodeioId))
            ->sum('prize_total');
        $totalBolaoLeagues = FantasyLeague::where('is_active', true)
            ->when($rodeioId, fn($q) => $q->where('rodeio_id', $rodeioId))->count();

        $stats = [
            'total_x1_active'    => $totalX1Active,
            'total_x1_finished'  => $totalX1Finished,
            'total_bolao_teams'  => $totalBolaoTeams,
            'total_bolao_leagues' => $totalBolaoLeagues,
            'total_prize_pool'   => (float) $totalPrizePool,
            'rodeio_name'        => $activeRodeio?->name ?? 'Sem rodeio ativo',
            'modalidade_atual'   => $activeRodeio?->modalidadeAtual?->nome ?? null,
            'divisao_atual'      => $activeRodeio?->divisao_atual ?? null,
        ];

        return response()->json([
            'x1_rooms'       => $x1Rooms,
            'bolao_entries'  => $latestBolaoEntries,
            'latest_winners' => $latestWinners,
            'top_winners'    => $topWinners,
            'stats'          => $stats,
        ]);
    }

    /**
     * Logout from broadcast.
     */
    public function logout()
    {
        session()->forget('broadcast_authenticated');
        return redirect()->route('broadcast.login');
    }

    // ── Helpers ──

    private function getActiveRodeio(): ?Rodeio
    {
        // Priority 1: rodeio with active transmission
        $rodeio = Rodeio::whereNotNull('status_transmissao')
            ->whereIn('status_transmissao', ['ao_vivo', 'pausado', 'programado'])
            ->with(['modalidadeAtual'])
            ->first();

        // Priority 2: most recent rodeio that has X1 rooms
        if (!$rodeio) {
            $rodeio = Rodeio::whereHas('modalidades')
                ->latest('created_at')
                ->first();
        }

        return $rodeio;
    }

    private function userAvatar($user): ?string
    {
        if (!$user || !$user->image) return null;
        return asset('assets/images/user/profile/' . $user->image);
    }
}
