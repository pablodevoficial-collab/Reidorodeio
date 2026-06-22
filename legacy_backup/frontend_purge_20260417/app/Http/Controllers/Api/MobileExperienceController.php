<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Competitor;
use App\Models\FantasyLeague;
use App\Models\Modalidade;
use App\Models\Rodeio;
use App\Models\X1RoomInstance;
use App\Services\X1StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileExperienceController extends Controller
{
    public function home(Request $request): JsonResponse
    {
        $user = $request->user();

        $banners = Banner::query()
            ->whereIn('status', ['ativo', '1', 1])
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderByRaw("FIELD(position, 'home_top', 'home_middle', 'home_bottom')")
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (Banner $banner) => [
                'id' => (int) $banner->id,
                'title' => (string) ($banner->title ?? 'Destaque'),
                'link' => $banner->link,
                'position' => $banner->position,
                'image_url' => $banner->image_mobile_url ?: $banner->image_web_url ?: $banner->image_url,
            ])
            ->values();

        $liveRooms = X1RoomInstance::query()
            ->with([
                'host:id,username,firstname,lastname,image',
                'modalidade:id,nome',
                'rodeio:id,name,status_transmissao',
                'competitor:id,nome,foto,nivel',
                'competitorGroup.members:id,nome,foto',
            ])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("FIELD(status, 'in_progress', 'open')")
            ->latest('id')
            ->limit(10)
            ->get();

        $featuredCompetitorIds = $liveRooms
            ->pluck('competitor_id')
            ->filter()
            ->unique()
            ->values();

        $featuredCompetitors = Competitor::query()
            ->with(['modalidades:id,nome'])
            ->whereIn('id', $featuredCompetitorIds)
            ->limit(8)
            ->get();

        if ($featuredCompetitors->count() < 8) {
            $fallbackIds = $featuredCompetitors->pluck('id')->all();
            $fallbackCompetitors = Competitor::query()
                ->with(['modalidades:id,nome'])
                ->where('status', 'ativo')
                ->whereNotIn('id', $fallbackIds)
                ->orderByRaw("FIELD(nivel, 'favorito', 'elite', 'ascendente', 'competidor')")
                ->latest('id')
                ->limit(8 - $featuredCompetitors->count())
                ->get();

            $featuredCompetitors = $featuredCompetitors->concat($fallbackCompetitors)->values();
        }

        $featuredCompetitorsPayload = $featuredCompetitors
            ->map(function (Competitor $competitor) {
                return [
                    'id' => (int) $competitor->id,
                    'name' => (string) $competitor->nome,
                    'photo_url' => $competitor->foto_url,
                    'level' => (string) ($competitor->nivel ?? 'competidor'),
                    'modalidades' => $competitor->modalidades
                        ->pluck('nome')
                        ->take(2)
                        ->values(),
                ];
            })
            ->values();

        $x1Spotlight = $liveRooms->map(function (X1RoomInstance $room) {
            return [
                'id' => (int) $room->id,
                'name' => (string) ($room->name ?? 'Sala X1'),
                'status' => (string) ($room->status ?? 'open'),
                'entry_amount' => (float) ($room->valor_entrada ?? 0),
                'prize_total' => (float) ($room->prize_total ?? 0),
                'modalidade' => $room->modalidade?->nome,
                'rodeio' => $room->rodeio?->name,
                'competitor' => $room->competitor ? [
                    'id' => (int) $room->competitor->id,
                    'name' => (string) $room->competitor->nome,
                    'photo_url' => $room->competitor->foto_url,
                ] : null,
                'competitor_group' => $room->competitorGroup ? [
                    'id' => (int) $room->competitorGroup->id,
                    'name' => (string) ($room->competitorGroup->nome ?: $room->competitorGroup->members->pluck('nome')->implode(' + ')),
                    'members' => $room->competitorGroup->members->map(fn ($member) => [
                        'id' => (int) $member->id,
                        'name' => (string) $member->nome,
                        'photo_url' => $member->foto_url,
                    ])->values(),
                ] : null,
                'host' => $room->host ? [
                    'id' => (int) $room->host->id,
                    'name' => trim((string) (($room->host->firstname ?? '') . ' ' . ($room->host->lastname ?? ''))) !== ''
                        ? trim((string) (($room->host->firstname ?? '') . ' ' . ($room->host->lastname ?? '')))
                        : (string) $room->host->username,
                ] : null,
            ];
        })->values();

        $fantasySpotlight = FantasyLeague::query()
            ->with(['rodeio:id,name,status_transmissao', 'modalidade:id,nome'])
            ->withCount('teams')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderByRaw('GREATEST(COALESCE(total_prize, 0), COALESCE(manual_prize_pool, 0)) DESC')
            ->limit(6)
            ->get()
            ->map(function (FantasyLeague $league) {
                $prize = max(
                    (float) ($league->total_prize ?? 0),
                    (float) ($league->manual_prize_pool ?? 0)
                );

                return [
                    'id' => (int) $league->id,
                    'name' => (string) $league->name,
                    'image_url' => $league->image_url,
                    'entry_price' => (float) ($league->price ?? 0),
                    'prize_total' => $prize,
                    'teams_count' => (int) ($league->teams_count ?? 0),
                    'is_premium' => (bool) ($league->is_premium ?? false),
                    'modalidade' => $league->modalidade?->nome,
                    'rodeio' => $league->rodeio?->name,
                    'category' => (string) ($league->category ?? 'Bolão'),
                ];
            })
            ->values();

        $liveEvents = Rodeio::query()
            ->with(['modalidades:id,rodeio_id,nome,status'])
            ->where('status_transmissao', 'ao_vivo')
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(fn (Rodeio $rodeio) => $this->mapEventCard($rodeio))
            ->values();

        $upcomingEvents = Rodeio::query()
            ->with(['modalidades:id,rodeio_id,nome,status'])
            ->where('status_transmissao', 'programado')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (Rodeio $rodeio) => $this->mapEventCard($rodeio))
            ->values();

        $summary = [
            'live_events' => Rodeio::query()->where('status_transmissao', 'ao_vivo')->count(),
            'open_x1_rooms' => X1RoomInstance::query()->whereIn('status', ['open', 'in_progress'])->count(),
            'active_leagues' => FantasyLeague::query()->where('is_active', true)->count(),
            'active_competitors' => Competitor::query()->where('status', 'ativo')->count(),
        ];

        $userSnapshot = [
            'display_name' => trim((string) (($user->firstname ?? '') . ' ' . ($user->lastname ?? ''))) !== ''
                ? trim((string) (($user->firstname ?? '') . ' ' . ($user->lastname ?? '')))
                : (string) $user->username,
            'is_premium' => method_exists($user, 'isPremium') ? (bool) $user->isPremium() : false,
            'profile_complete' => method_exists($user, 'requiresFullProfileForPrizes') && method_exists($user, 'isPrizeProfileComplete')
                ? (!$user->requiresFullProfileForPrizes() || (bool) $user->isPrizeProfileComplete())
                : (method_exists($user, 'isProfileComplete') ? (bool) $user->isProfileComplete() : false),
            'receivable_balance' => (float) ($user->receivable_balance ?? 0),
            'total_earnings' => (float) ($user->total_earnings ?? 0),
            'active_x1_rooms' => X1RoomInstance::query()
                ->where(function ($query) use ($user) {
                    $query->where('host_user_id', $user->id)
                        ->orWhereHas('participants', fn ($participantQuery) => $participantQuery->where('user_id', $user->id));
                })
                ->whereIn('status', ['pending_payment', 'open', 'in_progress'])
                ->count(),
            'fantasy_teams' => DB::table('fantasy_teams')
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'voucher' => [
                    'title' => '1 mês de Premium grátis no app',
                    'description' => 'Ative a experiência mobile do Rei do Rodeio e desbloqueie estatísticas, vantagens e leitura avançada.',
                    'cta' => method_exists($user, 'isPremium') && $user->isPremium()
                        ? 'Premium já ativo'
                        : 'Resgate em breve',
                ],
                'summary' => $summary,
                'user_snapshot' => $userSnapshot,
                'banners' => $banners,
                'live_events' => $liveEvents,
                'upcoming_events' => $upcomingEvents,
                'featured_competitors' => $featuredCompetitorsPayload,
                'x1_spotlight' => $x1Spotlight,
                'fantasy_spotlight' => $fantasySpotlight,
            ],
        ]);
    }

    public function statsOverview(Request $request): JsonResponse
    {
        $events = Rodeio::query()
            ->withCount('modalidades')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Rodeio $rodeio) => [
                'id' => (int) $rodeio->id,
                'name' => (string) ($rodeio->name ?? 'Evento'),
                'status' => (string) ($rodeio->status_transmissao ?? 'programado'),
                'modalidades_count' => (int) ($rodeio->modalidades_count ?? 0),
                'divisao_atual' => $rodeio->divisao_atual,
            ])
            ->values();

        $modalidades = Modalidade::query()
            ->withCount('competitors')
            ->with('rodeio:id,name')
            ->latest('id')
            ->limit(12)
            ->get()
            ->map(fn (Modalidade $modalidade) => [
                'id' => (int) $modalidade->id,
                'name' => (string) $modalidade->nome,
                'status' => (string) ($modalidade->status ?? 'programado'),
                'team_size' => (int) ($modalidade->tamanho_equipe ?? 1),
                'competitors_count' => (int) ($modalidade->competitors_count ?? 0),
                'rodeio' => $modalidade->rodeio?->name,
            ])
            ->values();

        $topCompetitors = collect();
        if (Schema::hasTable('competitor_stats')) {
            $topCompetitors = DB::table('competitor_stats as stats')
                ->join('competitors as competitors', 'competitors.id', '=', 'stats.competitor_id')
                ->leftJoin('rodeios as rodeios', 'rodeios.id', '=', 'stats.rodeio_id')
                ->leftJoin('modalidades as modalidades', 'modalidades.id', '=', 'stats.modalidade_id')
                ->select([
                    'stats.competitor_id',
                    'competitors.nome',
                    'competitors.foto',
                    'stats.pontuacao_total',
                    'stats.pontuacao_media',
                    'rodeios.name as rodeio_nome',
                    'modalidades.nome as modalidade_nome',
                ])
                ->orderByDesc('stats.pontuacao_total')
                ->limit(12)
                ->get()
                ->map(function ($row) {
                    $competitor = new Competitor([
                        'foto' => $row->foto,
                    ]);

                    return [
                        'id' => (int) $row->competitor_id,
                        'name' => (string) $row->nome,
                        'photo_url' => $competitor->foto_url,
                        'score_total' => (float) ($row->pontuacao_total ?? 0),
                        'score_average' => (float) ($row->pontuacao_media ?? 0),
                        'rodeio' => $row->rodeio_nome,
                        'modalidade' => $row->modalidade_nome,
                    ];
                })
                ->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events,
                'modalidades' => $modalidades,
                'top_competitors' => $topCompetitors,
            ],
        ]);
    }

    public function rankingsOverview(Request $request): JsonResponse
    {
        $topCompetitors = collect();
        if (Schema::hasTable('competitor_stats_global') && Schema::hasTable('competitors')) {
            $topCompetitors = DB::table('competitor_stats_global as stats')
                ->join('competitors as competitors', 'competitors.id', '=', 'stats.competitor_id')
                ->select([
                    'competitors.id',
                    'competitors.nome',
                    'competitors.foto',
                    'competitors.nivel',
                    'stats.aproveitamento',
                    'stats.pontuacao_media',
                    'stats.pontuacao_total',
                    'stats.vitorias',
                    'stats.derrotas',
                    'stats.empates',
                ])
                ->where(function ($query) {
                    $query->where('stats.vitorias', '>', 0)
                        ->orWhere('stats.derrotas', '>', 0)
                        ->orWhere('stats.empates', '>', 0);
                })
                ->orderByDesc('stats.aproveitamento')
                ->orderByDesc('stats.pontuacao_media')
                ->orderByDesc('stats.vitorias')
                ->limit(12)
                ->get()
                ->values()
                ->map(function ($row, $index) {
                    $competitor = new Competitor([
                        'foto' => $row->foto,
                    ]);

                    return [
                        'position' => $index + 1,
                        'competitor_id' => (int) $row->id,
                        'name' => (string) $row->nome,
                        'photo_url' => $competitor->foto_url,
                        'level' => (string) ($row->nivel ?? 'competidor'),
                        'aproveitamento' => (float) ($row->aproveitamento ?? 0),
                        'score_average' => (float) ($row->pontuacao_media ?? 0),
                        'score_total' => (float) ($row->pontuacao_total ?? 0),
                        'wins' => (int) ($row->vitorias ?? 0),
                        'losses' => (int) ($row->derrotas ?? 0),
                        'draws' => (int) ($row->empates ?? 0),
                        'appearances' => (int) (($row->vitorias ?? 0) + ($row->derrotas ?? 0) + ($row->empates ?? 0)),
                    ];
                });
        }

        $x1TopWinners = collect();
        if (Schema::hasTable('user_x1_stats') && Schema::hasTable('users')) {
            $x1TopWinners = collect(app(X1StatsService::class)->getTopN(12, 'alltime'))
                ->values()
                ->map(function (array $entry, int $index) {
                    return [
                        'position' => (int) ($entry['position'] ?? ($index + 1)),
                        'user_id' => (int) ($entry['user_id'] ?? 0),
                        'name' => $this->resolveDisplayName(
                            $entry['name'] ?? null,
                            $entry['username'] ?? null,
                        ),
                        'username' => (string) ($entry['username'] ?? ''),
                        'avatar_url' => $entry['avatar'] ?? null,
                        'total_prize_won' => (float) ($entry['total_prize_won'] ?? 0),
                        'wins' => (int) ($entry['wins'] ?? 0),
                        'win_rate' => (float) ($entry['win_rate'] ?? 0),
                        'rating' => (int) ($entry['rating'] ?? 0),
                        'entries_count' => (int) ($entry['total_x1s'] ?? 0),
                    ];
                });
        }

        $fantasyTopWinners = collect();
        if (Schema::hasTable('fantasy_teams') && Schema::hasTable('users')) {
            $fantasyTopWinners = DB::table('fantasy_teams')
                ->join('users', 'users.id', '=', 'fantasy_teams.user_id')
                ->select([
                    'fantasy_teams.user_id',
                    'users.username',
                    'users.firstname',
                    'users.lastname',
                    'users.image',
                    DB::raw('SUM(COALESCE(fantasy_teams.prize_won, 0)) as total_prize_won'),
                    DB::raw('COUNT(*) as wins_count'),
                    DB::raw('MIN(COALESCE(fantasy_teams.final_position, 999999)) as best_position'),
                ])
                ->whereNotNull('fantasy_teams.user_id')
                ->where('fantasy_teams.prize_won', '>', 0)
                ->groupBy(
                    'fantasy_teams.user_id',
                    'users.username',
                    'users.firstname',
                    'users.lastname',
                    'users.image',
                )
                ->orderByDesc('total_prize_won')
                ->orderBy('best_position')
                ->orderByDesc('wins_count')
                ->limit(12)
                ->get()
                ->values()
                ->map(function ($row, $index) {
                    return [
                        'position' => $index + 1,
                        'user_id' => (int) $row->user_id,
                        'name' => $this->resolveDisplayName(
                            trim((string) (($row->firstname ?? '') . ' ' . ($row->lastname ?? ''))),
                            $row->username ?? null,
                        ),
                        'username' => (string) ($row->username ?? ''),
                        'avatar_url' => $this->userAvatarUrl($row->image ?? null),
                        'total_prize_won' => (float) ($row->total_prize_won ?? 0),
                        'wins' => (int) ($row->wins_count ?? 0),
                        'best_position' => (int) ($row->best_position ?? 0),
                        'entries_count' => (int) ($row->wins_count ?? 0),
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'top_competitors' => $topCompetitors->values(),
                'x1_top_winners' => $x1TopWinners->values(),
                'fantasy_top_winners' => $fantasyTopWinners->values(),
            ],
        ]);
    }

    private function mapEventCard(Rodeio $rodeio): array
    {
        return [
            'id' => (int) $rodeio->id,
            'name' => (string) ($rodeio->name ?? 'Evento'),
            'status' => (string) ($rodeio->status_transmissao ?? 'programado'),
            'divisao_atual' => $rodeio->divisao_atual,
            'modalidades' => $rodeio->modalidades->pluck('nome')->take(3)->values(),
            'photo_url' => $this->publicAssetUrl($rodeio->logo),
            'stream_url' => $rodeio->stream_url,
        ];
    }

    private function publicAssetUrl(?string $path): ?string
    {
        $value = trim((string) ($path ?? ''));
        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        $value = str_replace('\\', '/', $value);
        $value = ltrim($value, '/');
        $lower = strtolower($value);

        if (str_starts_with($lower, 'public/')) {
            $value = substr($value, strlen('public/'));
            $lower = strtolower($value);
        }

        if (str_starts_with($lower, 'assets/')) {
            return asset($value);
        }

        $resolved = publicStorageUrl($value);

        return $resolved !== '' ? $resolved : null;
    }

    private function userAvatarUrl(?string $image): ?string
    {
        $value = trim((string) ($image ?? ''));
        if ($value === '') {
            return null;
        }

        return asset('assets/images/user/profile/' . ltrim($value, '/'));
    }

    private function resolveDisplayName(?string $preferred, ?string $fallback = null): string
    {
        $preferredValue = trim((string) ($preferred ?? ''));
        if ($preferredValue !== '') {
            return $preferredValue;
        }

        $fallbackValue = trim((string) ($fallback ?? ''));
        if ($fallbackValue !== '') {
            return $fallbackValue;
        }

        return 'Usuário';
    }
}
