<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppUserVoucher;
use App\Models\Modalidade;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\User;
use App\Services\FantasyTeamEntryRuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FantasyLeagueApiController extends Controller
{
    private const MIN_COMPETITORS_TO_ENABLE_ENTRY = 8;

    private function modalidadeIsFinalizada(int $modalidadeId): bool
    {
        if ($modalidadeId <= 0) {
            return false;
        }

        if (!Schema::hasTable('modalidades')) {
            return false;
        }

        try {
            $cacheKey = "modalidade_status_finalizada_{$modalidadeId}";
            return (bool) Cache::remember($cacheKey, now()->addSeconds(10), function () use ($modalidadeId) {
                $m = Modalidade::query()->select(['id', 'status'])->find($modalidadeId);
                return $m && strtolower((string) ($m->status ?? '')) === 'finalizado';
            });
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function publicImageUrl(?string $path): ?string
    {
        $resolved = publicStorageUrl($path);
        if ($resolved === '') {
            return null;
        }

        return $resolved;
    }

    private function rodeioLogoUrl($rodeio): ?string
    {
        if (!$rodeio || blank($rodeio->logo ?? null)) {
            return null;
        }

        $url = route('rodeios.logo', $rodeio);
        $version = (string) (($rodeio->updated_at?->timestamp) ?: time());

        return $url . '?v=' . $version;
    }

    private function normalizeCompetitorFotoUrl(?string $foto): string
    {
        return publicStorageUrl($foto) ?: asset('assets/images/logo_icon/favicon.png');
    }

    private function normalizeUserAvatarUrl(?string $image): ?string
    {
        $image = trim((string) $image);
        if ($image === '') {
            return null;
        }

        if (preg_match('~^(https?:)?//~i', $image) || str_starts_with($image, '/')) {
            return $image;
        }

        if (str_contains($image, '/')) {
            return asset(ltrim($image, '/'));
        }

        return asset('assets/images/user/profile/' . $image);
    }

    private function resolveFantasyRankingIdentity(FantasyTeam $team): array
    {
        $userName = 'Usuário';
        $userFoto = null;
        $userIsPremium = false;
        $userShowInListings = true;

        if ($team->user) {
            $userName = (string) ($team->user->username ?? 'Usuário');
            $userFoto = $team->user->image ?? null;
            $userIsPremium = $team->user->isPremium();
            $userShowInListings = (bool) ($team->user->show_in_listings ?? true);
        } elseif ($team->botUser) {
            $userName = (string) ($team->botUser->username ?? 'Usuário');
            $userIsPremium = $team->botUser->isPremium();
            $userShowInListings = false;
        }

        return [
            'name' => $userName !== '' ? $userName : 'Usuário',
            'foto' => $this->normalizeUserAvatarUrl($userFoto),
            'is_premium' => $userIsPremium,
            'show_in_listings' => $userShowInListings,
        ];
    }

    private function resolveEligibleVoucherCredits(?User $user): array
    {
        if (!$user) {
            return [];
        }

        return AppUserVoucher::query()
            ->where('user_id', $user->id)
            ->where('voucher_type', 'fantasy_ticket')
            ->where('status', 'active')
            ->where('remaining_uses', '>', 0)
            ->whereIn('credit_amount', [20.00, 50.00, 100.00])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('credit_amount')
            ->pluck('credit_amount')
            ->map(static fn ($value) => round((float) $value, 2))
            ->values()
            ->all();
    }

    private function decorateLeagueVoucherState(array $league, array $voucherCredits): array
    {
        $price = round((float) ($league['price'] ?? $league['entry_price'] ?? 0), 2);
        $isPremium = (bool) ($league['is_premium'] ?? false);
        $eligibleCredit = null;

        if (!$isPremium && in_array($price, [20.00, 50.00, 100.00], true)) {
            foreach ($voucherCredits as $credit) {
                if (round((float) $credit, 2) === $price) {
                    $eligibleCredit = round((float) $credit, 2);
                    break;
                }
            }
        }

        $league['has_eligible_voucher'] = $eligibleCredit !== null;
        $league['eligible_voucher_credit'] = $eligibleCredit !== null
            ? number_format($eligibleCredit, 2, '.', '')
            : null;

        return $league;
    }

    private function countEligibleCompetitorsForLeague(FantasyLeague $league, bool $onlyAvailable = true): int
    {
        if (!$league->modalidade_id || !Schema::hasTable('competitor_modalidade')) {
            return 0;
        }

        $query = DB::table('competitor_modalidade as cm')
            ->join('competitors as c', 'c.id', '=', 'cm.competitor_id')
            ->where('cm.modalidade_id', (int) $league->modalidade_id)
            ->where('c.status', 'ativo')
            ->when($onlyAvailable, fn ($q) => $q->where('cm.disponivel_participacao', 1));

        $canJoinStats = Schema::hasTable('competitor_stats') && $league->rodeio_id;
        if ($canJoinStats) {
            $query->leftJoin('competitor_stats as cs', function ($join) use ($league) {
                $join->on('cs.competitor_id', '=', 'c.id')
                    ->where('cs.rodeio_id', '=', (int) $league->rodeio_id)
                    ->where('cs.modalidade_id', '=', (int) $league->modalidade_id);
            });

            $query->where(function ($q) {
                $q->whereNull('cs.id')
                    ->orWhere('cs.is_finalized', false)
                    ->orWhere('cs.tipo_fase', 'classificatoria');
            });
        }

        $hasPivotDivisao = Schema::hasColumn('competitor_modalidade', 'divisao');
        $leagueDivisao = trim((string) ($league->divisao ?? ''));
        $modalidade = $league->modalidade;
        $isClassificatoria = $modalidade && in_array($modalidade->status, ['classificatoria', 'programado']);
        $hasAssignedDivisions = false;

        if ($hasPivotDivisao && !$isClassificatoria) {
            $hasAssignedDivisions = DB::table('competitor_modalidade')
                ->where('modalidade_id', (int) $league->modalidade_id)
                ->whereNotNull('divisao')
                ->where('divisao', '!=', '')
                ->exists();
        }

        if ($leagueDivisao !== '' && $hasPivotDivisao && !$isClassificatoria && $hasAssignedDivisions) {
            $query->where('cm.divisao', $leagueDivisao);
        }

        return (int) $query->distinct('c.id')->count('c.id');
    }

    private function leagueHasMinimumCompetitors(FantasyLeague $league): bool
    {
        return $this->countEligibleCompetitorsForLeague($league) >= self::MIN_COMPETITORS_TO_ENABLE_ENTRY;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'rodeio_id' => 'nullable|integer|min:1',
            'modalidade_id' => 'nullable|integer|min:1',
            'divisao' => 'nullable|string|max:60',
            'only_active' => 'nullable|boolean',
            'only_live' => 'nullable|boolean',
        ]);

        $onlyActive = array_key_exists('only_active', $validated) ? (bool) $validated['only_active'] : true;
        $onlyLive = array_key_exists('only_live', $validated) ? (bool) $validated['only_live'] : false;
        $cacheVersion = (int) Cache::get('fantasy_leagues_cache_version', 1);

        // Cache por host + filtros para não misturar URLs locais e ngrok no image_url.
        $requestHost = $request->getSchemeAndHttpHost();
        $cacheKey = 'fantasy_leagues_' . md5(
            json_encode($validated) . '_' . $onlyActive . '_' . $onlyLive . '_' . $requestHost . '_v' . $cacheVersion
        );
        
        $leagues = Cache::remember($cacheKey, now()->addSeconds(15), function () use ($validated, $onlyActive, $onlyLive) {
            return $this->fetchLeagues($validated, $onlyActive, $onlyLive);
        });

        $voucherCredits = $this->resolveEligibleVoucherCredits($request->user());
        $leagues = array_map(
            fn (array $league) => $this->decorateLeagueVoucherState($league, $voucherCredits),
            $leagues
        );

        return response()->json([
            'success' => true,
            'data' => $leagues,
        ]);
    }

    private function fetchLeagues(array $validated, bool $onlyActive, bool $onlyLive): array
    {
        $query = FantasyLeague::query()
            ->when($onlyActive, fn ($q) => $q->where(function ($q2) {
                // Include active leagues AND recently finalized ones
                $q2->where('is_active', true)
                    ->orWhere('status', 'finalized');
            }))
            ->when(isset($validated['rodeio_id']), fn ($q) => $q->where('rodeio_id', (int) $validated['rodeio_id']))
            ->when(isset($validated['modalidade_id']), fn ($q) => $q->where('modalidade_id', (int) $validated['modalidade_id']))
            ->when(
                isset($validated['divisao']) && trim((string) $validated['divisao']) !== '',
                function ($q) use ($validated) {
                    $divisao = trim((string) $validated['divisao']);

                    $q->where(function ($q2) use ($divisao) {
                        $q2->where('divisao', $divisao)
                            ->orWhereNull('divisao')
                            ->orWhere('divisao', '');
                    });
                }
            )
            ->when($onlyLive, function ($q) {
                if (Schema::hasTable('rodeios') && Schema::hasColumn('rodeios', 'status_transmissao')) {
                    $q->whereHas('rodeio', function ($rq) {
                        $rq->where('status_transmissao', 'ao_vivo');
                    });
                }
            })
            ->with([
                'rodeio:id,name,logo,status_transmissao,divisao_atual,updated_at',
                'modalidade:id,nome,tem_divisoes,divisoes',
            ])
            ->withCount('teams')
            ->orderByDesc('id');

        return $query->get()->map(function (FantasyLeague $league) {
            $teams = (int) ($league->teams_count ?? 0);
            $price = (float) ($league->price ?? 0);
            $availableCompetitorsCount = $this->countEligibleCompetitorsForLeague($league);

            $rewardMode = (string) ($league->reward_mode ?? 'computed');
            $manualPrizePool = $league->manual_prize_pool !== null ? (float) $league->manual_prize_pool : null;
            $prizeType = in_array((string) ($league->prize_type ?? 'money'), ['money', 'physical'], true)
                ? (string) ($league->prize_type ?? 'money')
                : 'money';
            $prizeDescription = trim((string) ($league->prize_description ?? '')) ?: null;
            $prizeItems = is_array($league->prize_items ?? null) ? $league->prize_items : null;

            $totalPool = 0.0;
            $houseCut = (float) ($league->house_cut_percent ?? 30);
            if ($houseCut < 0) $houseCut = 0;
            if ($houseCut > 100) $houseCut = 100;
            $houseTake = 0.0;
            $prizePool = 0.0;

            $isPremiumLeague = (bool) $league->is_premium;
            $isFreeLeague = !$isPremiumLeague && $price <= 0.0;

            if ($isPremiumLeague) {
                // Premium: free entry. Prize is either manual or none (points-only).
                $price = 0.0;
                if ($rewardMode === 'manual_prize' && $prizeType === 'money' && $manualPrizePool !== null) {
                    $prizePool = max(0.0, $manualPrizePool);
                } elseif ($rewardMode === 'manual_prize' && $prizeType === 'physical') {
                    $manualPrizePool = null;
                    $prizePool = 0.0;
                } else {
                    $rewardMode = 'points';
                    $manualPrizePool = null;
                    $prizeType = 'money';
                    $prizeDescription = null;
                    $prizeItems = null;
                    $prizePool = 0.0;
                }
                $houseCut = 0.0;
            } elseif ($isFreeLeague) {
                // Free public league: entry is open and prize is managed by the admin.
                $price = 0.0;
                $rewardMode = 'manual_prize';
                $houseCut = 0.0;
                if ($prizeType === 'money' && $manualPrizePool !== null) {
                    $prizeItems = null;
                    $prizePool = max(0.0, $manualPrizePool);
                } else {
                    $manualPrizePool = null;
                    $prizePool = 0.0;
                }
            } else {
                // Paid: computed from entries.
                $rewardMode = 'computed';
                $manualPrizePool = null;
                $prizeType = 'money';
                $prizeDescription = null;
                $prizeItems = null;
                $totalPool = $teams * $price;
                $houseTake = $totalPool * ($houseCut / 100.0);
                $prizePool = max(0.0, $totalPool - $houseTake);
            }

            $entryMode = $isPremiumLeague ? 'premium' : ($isFreeLeague ? 'free' : number_format($price, 2, '.', ''));

            $imageUrl = $this->publicImageUrl($league->image);
            if ($imageUrl) {
                $imageUrl .= (str_contains($imageUrl, '?') ? '&' : '?') . 'v=' . (($league->updated_at?->timestamp) ?: time());
            }

            return [
                'id' => $league->id,
                'name' => $league->name,
                'category' => $league->category,
                'image_url' => $imageUrl,
                'price' => number_format($price, 2, '.', ''),
                'entry_mode' => $entryMode,
                'house_cut_percent' => number_format($houseCut, 2, '.', ''),
                'reward_mode' => $rewardMode,
                'manual_prize_pool' => $manualPrizePool === null ? null : number_format($manualPrizePool, 2, '.', ''),
                'prize_type' => $prizeType,
                'prize_description' => $prizeDescription,
                'prize_items' => $prizeItems,
                'total_pool' => number_format($totalPool, 2, '.', ''),
                'house_take' => number_format($houseTake, 2, '.', ''),
                'prize_pool' => number_format($prizePool, 2, '.', ''),
                'total_prize' => $league->total_prize !== null ? number_format((float) $league->total_prize, 2, '.', '') : null,
                'is_premium' => (bool) $league->is_premium,
                'is_active' => (bool) $league->is_active,
                'status' => $league->status ?? 'active',
                'event_finalized' => $league->status === 'finalized',
                'finalized_at' => $league->finalized_at?->toIso8601String(),
                'max_users' => $league->max_users,
                'teams_count' => (int) ($league->teams_count ?? 0),
                'available_competitors_count' => $availableCompetitorsCount,
                'minimum_competitors_required' => self::MIN_COMPETITORS_TO_ENABLE_ENTRY,
                'entry_enabled' => $availableCompetitorsCount >= self::MIN_COMPETITORS_TO_ENABLE_ENTRY,
                'is_full' => $league->max_users && $teams >= $league->max_users, // 🔥 Liga lotada
                'closes_at' => $league->closes_at ? $league->closes_at->toISOString() : null,
                // 🚫 Deadline de inscrições
                'registration_deadline' => $league->registration_deadline?->toIso8601String(),
                'registration_status' => $league->registration_status,
                'registration_time_left' => $league->registration_time_left,
                'rodeio' => $league->rodeio ? [
                    'id' => $league->rodeio->id,
                    'nome' => $league->rodeio->name,
                    'logo' => $league->rodeio->logo,
                    'logo_url' => $this->rodeioLogoUrl($league->rodeio),
                    'status_transmissao' => $league->rodeio->status_transmissao ?? null,
                    'divisao_atual' => $league->rodeio->divisao_atual ?? null,
                ] : null,
                'modalidade' => $league->modalidade ? [
                    'id' => $league->modalidade->id,
                    'nome' => $league->modalidade->nome,
                    'tem_divisoes' => (bool) ($league->modalidade->tem_divisoes ?? false),
                    'divisoes' => $league->modalidade->divisoes_nomes ?? [],
                ] : null,
                'divisao' => $league->divisao,
            ];
        })->toArray();
    }

    public function show(Request $request, int $leagueId)
    {
        $league = FantasyLeague::query()
            ->with([
                'rodeio:id,name,logo,status_transmissao,divisao_atual,updated_at',
                'modalidade:id,nome,tem_divisoes,divisoes',
            ])
            ->withCount('teams')
            ->find($leagueId);

        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Liga não encontrada',
            ], 404);
        }

        $teams = (int) ($league->teams_count ?? 0);

        $price = (float) ($league->price ?? 0);
        $rewardMode = (string) ($league->reward_mode ?? 'computed');
        $manualPrizePool = $league->manual_prize_pool !== null ? (float) $league->manual_prize_pool : null;
        $prizeType = in_array((string) ($league->prize_type ?? 'money'), ['money', 'physical'], true)
            ? (string) ($league->prize_type ?? 'money')
            : 'money';
        $prizeDescription = trim((string) ($league->prize_description ?? '')) ?: null;
        $prizeItems = is_array($league->prize_items ?? null) ? $league->prize_items : null;

        $totalPool = 0.0;
        $houseCut = (float) ($league->house_cut_percent ?? 30);
        if ($houseCut < 0) $houseCut = 0;
        if ($houseCut > 100) $houseCut = 100;
        $houseTake = 0.0;
        $prizePool = 0.0;

        $isPremiumLeague = (bool) $league->is_premium;
        $isFreeLeague = !$isPremiumLeague && $price <= 0.0;

        if ($isPremiumLeague) {
            $price = 0.0;
            if ($rewardMode === 'manual_prize' && $prizeType === 'money' && $manualPrizePool !== null) {
                $prizePool = max(0.0, $manualPrizePool);
            } elseif ($rewardMode === 'manual_prize' && $prizeType === 'physical') {
                $manualPrizePool = null;
                $prizePool = 0.0;
            } else {
                $rewardMode = 'points';
                $manualPrizePool = null;
                $prizeType = 'money';
                $prizeDescription = null;
                $prizeItems = null;
                $prizePool = 0.0;
            }
            $houseCut = 0.0;
        } elseif ($isFreeLeague) {
            $price = 0.0;
            $rewardMode = 'manual_prize';
            $houseCut = 0.0;
            if ($prizeType === 'money' && $manualPrizePool !== null) {
                $prizeItems = null;
                $prizePool = max(0.0, $manualPrizePool);
            } else {
                $manualPrizePool = null;
                $prizePool = 0.0;
            }
        } else {
            $rewardMode = 'computed';
            $manualPrizePool = null;
            $prizeType = 'money';
            $prizeDescription = null;
            $prizeItems = null;
            $totalPool = $teams * $price;
            $houseTake = $totalPool * ($houseCut / 100.0);
            $prizePool = max(0.0, $totalPool - $houseTake);
        }

        $entryMode = $isPremiumLeague ? 'premium' : ($isFreeLeague ? 'free' : number_format($price, 2, '.', ''));

        $imageUrl = $this->publicImageUrl($league->image);
        if ($imageUrl) {
            $imageUrl .= (str_contains($imageUrl, '?') ? '&' : '?') . 'v=' . (($league->updated_at?->timestamp) ?: time());
        }

        return response()->json([
            'success' => true,
            'data' => $this->decorateLeagueVoucherState([
                'id' => $league->id,
                'name' => $league->name,
                'category' => $league->category,
                'image_url' => $imageUrl,
                'price' => number_format($price, 2, '.', ''),
                'entry_mode' => $entryMode,
                'house_cut_percent' => number_format($houseCut, 2, '.', ''),
                'reward_mode' => $rewardMode,
                'manual_prize_pool' => $manualPrizePool === null ? null : number_format($manualPrizePool, 2, '.', ''),
                'prize_type' => $prizeType,
                'prize_description' => $prizeDescription,
                'prize_items' => $prizeItems,
                'total_pool' => number_format($totalPool, 2, '.', ''),
                'house_take' => number_format($houseTake, 2, '.', ''),
                'prize_pool' => number_format($prizePool, 2, '.', ''),
                'total_prize' => $league->total_prize !== null ? number_format((float) $league->total_prize, 2, '.', '') : null,
                'is_premium' => (bool) $league->is_premium,
                'is_active' => (bool) $league->is_active,
                'max_users' => $league->max_users,
                'teams_count' => (int) ($league->teams_count ?? 0),
                'season_id' => $league->season_id,
                'rodeio_id' => $league->rodeio_id,
                'modalidade_id' => $league->modalidade_id,
                'divisao' => $league->divisao,
                'closes_at' => $league->closes_at ? $league->closes_at->toISOString() : null,
                'rodeio' => $league->rodeio ? [
                    'id' => $league->rodeio->id,
                    'nome' => $league->rodeio->name,
                    'logo' => $league->rodeio->logo,
                    'logo_url' => $this->rodeioLogoUrl($league->rodeio),
                    'status_transmissao' => $league->rodeio->status_transmissao ?? null,
                    'divisao_atual' => $league->rodeio->divisao_atual ?? null,
                ] : null,
                'modalidade' => $league->modalidade ? [
                    'id' => $league->modalidade->id,
                    'nome' => $league->modalidade->nome,
                    'tem_divisoes' => (bool) ($league->modalidade->tem_divisoes ?? false),
                    'divisoes' => $league->modalidade->divisoes_nomes ?? [],
                ] : null,
                'event_finalized' => $league->rodeio && $league->rodeio->status_transmissao === 'finalizado',
            ], $this->resolveEligibleVoucherCredits($request->user())),
        ]);
    }

    public function availableCompetitors(Request $request, int $leagueId)
    {
        $validated = $request->validate([
            'only_available' => 'nullable|boolean',
        ]);

        $onlyAvailable = array_key_exists('only_available', $validated) ? (bool) $validated['only_available'] : true;

        $league = FantasyLeague::query()->find($leagueId);
        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Liga não encontrada',
            ], 404);
        }

        if (!$league->modalidade_id) {
            return response()->json([
                'success' => false,
                'message' => 'Liga sem modalidade vinculada',
            ], 422);
        }

        if (!Schema::hasTable('competitor_modalidade')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabela de vínculo competidor/modalidade não existe',
            ], 500);
        }

        // Fantasy SEMPRE busca competidores individuais, não grupos
        // Mesmo em modalidades de dupla/trio, o usuário monta time com competidores individuais
        $query = DB::table('competitor_modalidade as cm')
            ->join('competitors as c', 'c.id', '=', 'cm.competitor_id')
            ->where('cm.modalidade_id', (int) $league->modalidade_id)
            ->where('c.status', 'ativo')
            ->when($onlyAvailable, fn ($q) => $q->where('cm.disponivel_participacao', 1));

        $canJoinStats = Schema::hasTable('competitor_stats') && $league->rodeio_id;
        if ($canJoinStats) {
            $query->leftJoin('competitor_stats as cs', function ($join) use ($league) {
                $join->on('cs.competitor_id', '=', 'c.id')
                    ->where('cs.rodeio_id', '=', (int) $league->rodeio_id)
                    ->where('cs.modalidade_id', '=', (int) $league->modalidade_id);
            });
            
            // Excluir competidores que têm stats finalizadas na divisão deles
            // Competidor é excluído SE: is_finalized = true E tipo_fase = 'final'
            // E a divisão das stats corresponde à divisão do competidor
            $query->where(function ($q) {
                $q->whereNull('cs.id') // Sem stats ainda - pode participar
                  ->orWhere('cs.is_finalized', false) // Stats não finalizadas - pode participar
                  ->orWhere('cs.tipo_fase', 'classificatoria'); // Stats de classificatória - pode participar
            });
        }

        $hasPivotDivisao = Schema::hasColumn('competitor_modalidade', 'divisao');
        $divisaoFilterApplied = false;
        $leagueDivisao = trim((string) ($league->divisao ?? ''));
        
        // Verificar se modalidade está em fase classificatória - não filtrar por divisão neste caso
        $modalidade = $league->modalidade;
        $isClassificatoria = $modalidade && in_array($modalidade->status, ['classificatoria', 'programado']);
        
        // Verificar se há competidores individuais com divisão atribuída
        // Fantasy sempre usa competidores individuais, não grupos
        $hasAssignedDivisions = false;
        if ($hasPivotDivisao && !$isClassificatoria) {
            $hasAssignedDivisions = DB::table('competitor_modalidade')
                ->where('modalidade_id', (int) $league->modalidade_id)
                ->whereNotNull('divisao')
                ->where('divisao', '!=', '')
                ->exists();
        }
        
        // Só filtra por divisão se NÃO está em classificatória E há divisões atribuídas
        if ($leagueDivisao !== '' && $hasPivotDivisao && !$isClassificatoria && $hasAssignedDivisions) {
            $query->where('cm.divisao', $leagueDivisao);
            $divisaoFilterApplied = true;
        }

        $select = [
            'c.id',
            'c.nome',
            'c.foto',
            'c.nivel',
            'c.categoria',
            'c.cidade',
            'cm.status as modalidade_status',
            'cm.disponivel_participacao',
            'cm.multiplicador_atual',
            'cm.numero_participacao',
        ];

        if ($hasPivotDivisao) {
            $select[] = 'cm.divisao as modalidade_divisao';
        }

        if ($canJoinStats) {
            $select[] = 'cs.divisao as stats_divisao';
            $select[] = 'cs.tipo_fase';
            $select[] = 'cs.is_finalized';
            $select[] = 'cs.pontuacao_total';
            $select[] = 'cs.last_points';
            $select[] = 'cs.count_negativas_total';
            $select[] = 'cs.count_boa';
        }

        $rows = $query
            ->select($select)
            ->orderBy('c.nome')
            ->get();

        foreach ($rows as $row) {
            $row->foto_url = $this->normalizeCompetitorFotoUrl($row->foto ?? null);
        }

        $modalidadeFinalizada = $this->modalidadeIsFinalizada((int) $league->modalidade_id);
        
        // Lógica de exibição de pontuação:
        // - Classificatória finalizada (is_finalized=true, tipo_fase='classificatoria'): MOSTRAR
        // - Final finalizada (is_finalized=true, tipo_fase='final'): MOSTRAR
        // - Em andamento (is_finalized=false): OCULTAR pontuação
        foreach ($rows as $row) {
            $isFinalized = property_exists($row, 'is_finalized') && $row->is_finalized;
            
            // Só mostrar stats se a fase estiver finalizada
            if (!$isFinalized && !$modalidadeFinalizada) {
                if (property_exists($row, 'pontuacao_total')) {
                    $row->pontuacao_total = null;
                }
                if (property_exists($row, 'last_points')) {
                    $row->last_points = null;
                }
                if (property_exists($row, 'count_negativas_total')) {
                    $row->count_negativas_total = null;
                }
                if (property_exists($row, 'count_boa')) {
                    $row->count_boa = null;
                }
            }
        }

        $userActiveTeams = [];
        $user = $request->user();
        if ($user) {
            $userActiveTeams = FantasyTeam::query()
                ->where('fantasy_league_id', $league->id)
                ->where('user_id', (int) $user->id)
                ->where('is_active', true)
                ->get()
                ->map(function (FantasyTeam $team) {
                    return [
                        'id' => (int) $team->id,
                        'team_name' => (string) ($team->team_name ?? ''),
                        'competitor_ids' => $team->getCompetitors()
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->values()
                            ->all(),
                    ];
                })
                ->values()
                ->all();
        }

        return response()->json([
            'success' => true,
            'meta' => [
                'league_id' => $league->id,
                'rodeio_id' => $league->rodeio_id,
                'modalidade_id' => $league->modalidade_id,
                'league_divisao' => $leagueDivisao !== '' ? $leagueDivisao : null,
                'divisao_filter_applied' => $divisaoFilterApplied,
                'points_hidden_until_final' => !$modalidadeFinalizada,
                'modalidade_finalizada' => $modalidadeFinalizada,
                'user_active_teams' => $userActiveTeams,
            ],
            'data' => $rows,
        ]);
    }

    public function createTeam(Request $request, int $leagueId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'team_name' => 'required|string|max:255',
            'competitor_ids' => 'required|array|size:4',
            'competitor_ids.*' => 'integer|min:1',
            'captain_id' => 'nullable|integer|min:1',
        ]);

        $league = FantasyLeague::query()->find($leagueId);
        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Liga não encontrada',
            ], 404);
        }

        if (!$league->modalidade_id) {
            return response()->json([
                'success' => false,
                'message' => 'Liga sem modalidade vinculada',
            ], 422);
        }

        if (!Schema::hasTable('competitor_modalidade')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabela de vínculo competidor/modalidade não existe',
            ], 500);
        }

        if (!(bool) $league->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Liga inativa',
            ], 422);
        }

        if (!$this->leagueHasMinimumCompetitors($league)) {
            return response()->json([
                'success' => false,
                'message' => 'O bolão precisa ter no mínimo 8 competidores disponíveis para liberar entradas.',
            ], 422);
        }

        // === VERIFICAR SE EVENTO ESTÁ FINALIZADO ===
        if ($league->rodeio_id) {
            $rodeio = \App\Models\Rodeio::find($league->rodeio_id);
            if ($rodeio && $rodeio->status_transmissao === 'finalizado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Evento finalizado. Não é possível criar novas equipes.',
                ], 422);
            }
        }

        if ((bool) $league->is_premium && !(bool) $user->isPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Liga Premium requer assinatura ativa',
            ], 403);
        }

        if ($league->max_users) {
            $teamCount = FantasyTeam::query()->where('fantasy_league_id', $league->id)->count();
            if ($teamCount >= (int) $league->max_users) {
                return response()->json([
                    'success' => false,
                    'message' => 'Liga atingiu o limite de participantes',
                ], 422);
            }
        }

        $competitorIds = array_values(array_unique(array_map('intval', $validated['competitor_ids'])));
        
        $captainId = isset($validated['captain_id']) ? (int) $validated['captain_id'] : null;
        if (count($competitorIds) !== 4) {
            return response()->json([
                'success' => false,
                'message' => 'Selecione exatamente 4 competidores',
            ], 422);
        }

        $teamRuleViolation = app(FantasyTeamEntryRuleService::class)
            ->validateForUser((int) $league->id, (int) $user->id, $competitorIds);
        if ($teamRuleViolation) {
            return response()->json($teamRuleViolation, 422);
        }

        // Captain defaults to first selected competitor.
        if (!$captainId) {
            $captainId = $competitorIds[0] ?? null;
        }

        if ($captainId && !in_array($captainId, $competitorIds, true)) {
            $captainId = $competitorIds[0] ?? null;
        }

        // Validate selected competitors belong to league modality.
        $allowedIdsQuery = DB::table('competitor_modalidade as cm')
            ->join('competitors as c', 'c.id', '=', 'cm.competitor_id')
            ->where('cm.modalidade_id', (int) $league->modalidade_id)
            ->where('c.status', 'ativo')
            ->whereIn('cm.competitor_id', $competitorIds)
            ->where('cm.disponivel_participacao', 1);

        $hasPivotDivisao = Schema::hasTable('competitor_modalidade') && Schema::hasColumn('competitor_modalidade', 'divisao');
        $leagueDivisao = trim((string) ($league->divisao ?? ''));

        $modalidade = $league->modalidade;
        $isClassificatoria = $modalidade && in_array($modalidade->status, ['classificatoria', 'programado'], true);
        $hasAssignedDivisions = false;
        if ($hasPivotDivisao && !$isClassificatoria) {
            $hasAssignedDivisions = DB::table('competitor_modalidade')
                ->where('modalidade_id', (int) $league->modalidade_id)
                ->whereNotNull('divisao')
                ->where('divisao', '!=', '')
                ->exists();
        }

        if ($leagueDivisao !== '' && $hasPivotDivisao && !$isClassificatoria && $hasAssignedDivisions) {
            $allowedIdsQuery->where('cm.divisao', $leagueDivisao);
        }

        $allowedIds = $allowedIdsQuery->pluck('cm.competitor_id')->map(fn ($v) => (int) $v)->all();
        sort($allowedIds);
        $selectedSorted = $competitorIds;
        sort($selectedSorted);

        if ($allowedIds !== $selectedSorted) {
            return response()->json([
                'success' => false,
                'message' => 'Um ou mais competidores não estão disponíveis para esta liga',
            ], 422);
        }

        $team = DB::transaction(function () use ($league, $user, $validated, $competitorIds, $captainId) {
            $team = FantasyTeam::query()->create([
                'fantasy_league_id' => (int) $league->id,
                'user_id' => (int) $user->id,
                'team_name' => (string) $validated['team_name'],
                'total_points' => 0,
                'is_active' => true,
            ]);

            if (Schema::hasTable('fantasy_team_competitors')) {
                foreach ($competitorIds as $competitorId) {
                    $isCaptain = $captainId ? ((int) $competitorId === (int) $captainId) : false;
                    $team->competitorsRelation()->attach($competitorId, [
                        'role' => 'titular',
                        'is_captain' => $isCaptain,
                        'multiplier' => $isCaptain ? 1.5 : 1,
                    ]);
                }
            } else {
                $team->competitors = $competitorIds;
                $team->save();
            }

            return $team;
        });

        // Smart bot masking: a cada 3 users reais, remove 1 bot
        try {
            $league = FantasyLeague::find($leagueId);
            if ($league) {
                $realCount = \App\Models\FantasyTeam::where('fantasy_league_id', $leagueId)
                    ->whereNotNull('user_id')
                    ->whereNull('deleted_at')
                    ->count();
                $botCount = \App\Models\FantasyTeam::where('fantasy_league_id', $leagueId)
                    ->whereNotNull('bot_user_id')
                    ->whereNull('deleted_at')
                    ->count();

                // A cada 3 reais, 1 bot é removido (baseado no total de reais)
                $botsToRemove = (int) floor($realCount / 3);
                $botsAlreadyRemoved = \App\Models\FantasyTeam::where('fantasy_league_id', $leagueId)
                    ->whereNotNull('bot_user_id')
                    ->onlyTrashed()
                    ->count();
                $botsToRemoveNow = min(max(0, $botsToRemove - $botsAlreadyRemoved), $botCount);

                if ($botsToRemoveNow > 0 && $botCount > 0) {
                    $botsToDelete = \App\Models\FantasyTeam::where('fantasy_league_id', $leagueId)
                        ->whereNotNull('bot_user_id')
                        ->whereNull('deleted_at')
                        ->orderBy('created_at', 'asc')
                        ->limit($botsToRemoveNow)
                        ->get();

                    foreach ($botsToDelete as $botTeam) {
                        $botTeam->delete(); // soft delete
                    }

                    \Illuminate\Support\Facades\Log::info("Fantasy bot masking: removed {$botsToDelete->count()} bots from league {$leagueId} (real users: {$realCount})");
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Bot masking error: " . $e->getMessage());
        }

        $team->load('fantasyLeague');
        $competitors = $team->getCompetitors()->map(fn ($c) => [
            'id' => $c->id,
            'nome' => $c->nome,
            'foto' => $c->foto,
            'nivel' => $c->nivel,
        ]);

        app(\App\Services\AppCommunityFeedService::class)
            ->publishFantasyTeamJoined($team);

        return response()->json([
            'success' => true,
            'data' => [
                'team' => [
                    'id' => $team->id,
                    'team_name' => $team->team_name,
                    'total_points' => (string) $team->total_points,
                    'fantasy_league_id' => $team->fantasy_league_id,
                ],
                'competitors' => $competitors,
            ],
        ], 201);
    }

    public function verifyTeam(Request $request, int $leagueId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $validated = $request->validate([
            'competitor_ids' => 'required|array|size:4',
            'competitor_ids.*' => 'integer|min:1',
        ]);

        $league = FantasyLeague::query()->find($leagueId);
        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Liga não encontrada',
            ], 404);
        }

        if (!$league->modalidade_id) {
            return response()->json([
                'success' => false,
                'message' => 'Liga sem modalidade vinculada',
            ], 422);
        }

        if (!(bool) $league->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Liga inativa',
            ], 422);
        }

        if (!$this->leagueHasMinimumCompetitors($league)) {
            return response()->json([
                'success' => false,
                'message' => 'O bolão precisa ter no mínimo 8 competidores disponíveis para liberar entradas.',
            ], 422);
        }

        if ((bool) $league->is_premium && !(bool) $user->isPremium()) {
            return response()->json([
                'success' => false,
                'message' => 'Liga Premium requer assinatura ativa',
            ], 403);
        }

        if (!Schema::hasTable('competitor_modalidade')) {
            return response()->json([
                'success' => false,
                'message' => 'Tabela de vínculo competidor/modalidade não existe',
            ], 500);
        }

        $competitorIds = array_values(array_unique(array_map('intval', $validated['competitor_ids'])));
        if (count($competitorIds) !== 4) {
            return response()->json([
                'success' => false,
                'message' => 'Selecione exatamente 4 competidores',
            ], 422);
        }

        $teamRuleViolation = app(FantasyTeamEntryRuleService::class)
            ->validateForUser((int) $league->id, (int) $user->id, $competitorIds);
        if ($teamRuleViolation) {
            return response()->json($teamRuleViolation, 422);
        }

        $hasPivotDivisao = Schema::hasColumn('competitor_modalidade', 'divisao');
        $leagueDivisao = trim((string) ($league->divisao ?? ''));
        $canJoinStats = Schema::hasTable('competitor_stats') && $league->rodeio_id;

        $modalidade = $league->modalidade;
        $isClassificatoria = $modalidade && in_array($modalidade->status, ['classificatoria', 'programado'], true);
        $hasAssignedDivisions = false;
        if ($hasPivotDivisao && !$isClassificatoria) {
            $hasAssignedDivisions = DB::table('competitor_modalidade')
                ->where('modalidade_id', (int) $league->modalidade_id)
                ->whereNotNull('divisao')
                ->where('divisao', '!=', '')
                ->exists();
        }

        $query = DB::table('competitor_modalidade as cm')
            ->join('competitors as c', 'c.id', '=', 'cm.competitor_id')
            ->where('cm.modalidade_id', (int) $league->modalidade_id)
            ->whereIn('cm.competitor_id', $competitorIds);

        if ($leagueDivisao !== '' && $hasPivotDivisao && !$isClassificatoria && $hasAssignedDivisions) {
            $query->where('cm.divisao', $leagueDivisao);
        }

        if ($canJoinStats) {
            $query->leftJoin('competitor_stats as cs', function ($join) use ($league) {
                $join->on('cs.competitor_id', '=', 'c.id')
                    ->where('cs.rodeio_id', '=', (int) $league->rodeio_id)
                    ->where('cs.modalidade_id', '=', (int) $league->modalidade_id);
            });
        }

        $select = [
            'c.id',
            'c.nome',
            'c.status as competitor_status',
            'cm.status as modalidade_status',
            'cm.disponivel_participacao',
        ];

        if ($hasPivotDivisao) {
            $select[] = 'cm.divisao as modalidade_divisao';
        }

        if ($canJoinStats) {
            $select[] = 'cs.pontuacao_total';
            $select[] = 'cs.last_points';
            $select[] = 'cs.count_negativas_total';
        }

        $rows = $query->select($select)->get();
        $byId = $rows->keyBy(fn ($r) => (int) $r->id);

        $result = [];
        $allOk = true;

        foreach ($competitorIds as $cid) {
            $row = $byId->get((int) $cid);
            $reasons = [];
            $ok = true;

            if (!$row) {
                $ok = false;
                $reasons[] = 'Não vinculado à modalidade/divisão da liga';
            } else {
                if (($row->competitor_status ?? '') !== 'ativo') {
                    $ok = false;
                    $reasons[] = 'Competidor inativo';
                }

                if (!(bool) ($row->disponivel_participacao ?? false)) {
                    $ok = false;
                    $reasons[] = 'Fora da disputa';
                }

                $mStatus = strtolower((string) ($row->modalidade_status ?? ''));
                if (in_array($mStatus, ['eliminado', 'desclassificado', 'desclassificado(a)'], true)) {
                    $ok = false;
                    $reasons[] = 'Eliminado';
                }

                if ($canJoinStats) {
                    $negCount = (int) ($row->count_negativas_total ?? 0);
                    $lastPoints = (int) ($row->last_points ?? 0);
                    $totalPoints = (int) ($row->pontuacao_total ?? 0);

                    if ($negCount > 0) {
                        $ok = false;
                        $reasons[] = 'Tem estatísticas negativas';
                    }
                    if ($lastPoints < 0) {
                        $ok = false;
                        $reasons[] = 'Última pontuação negativa';
                    }
                    if ($totalPoints < 0) {
                        $ok = false;
                        $reasons[] = 'Pontuação total negativa';
                    }
                } else {
                    $ok = false;
                    $reasons[] = 'Sem dados de pontuação para verificação';
                }
            }

            if (!$ok) {
                $allOk = false;
            }

            $result[] = [
                'id' => (int) $cid,
                'nome' => $row ? (string) ($row->nome ?? '') : null,
                'ok' => $ok,
                'reasons' => $reasons,
            ];
        }

        return response()->json([
            'success' => true,
            'ok' => $allOk,
            'meta' => [
                'league_id' => (int) $league->id,
                'rodeio_id' => $league->rodeio_id,
                'modalidade_id' => $league->modalidade_id,
                'league_divisao' => $leagueDivisao !== '' ? $leagueDivisao : null,
                'checked_stats' => $canJoinStats,
            ],
            'data' => $result,
        ]);
    }

    public function myTeam(Request $request, int $leagueId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $teams = FantasyTeam::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('user_id', (int) $user->id)
            ->get();

        if ($teams->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        // Calcular posições no ranking (todos os times ordenados por pontos)
        $allTeamIds = FantasyTeam::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('is_active', true)
            ->orderByDesc('total_points')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $totalTeams = count($allTeamIds);
        $positionMap = [];
        foreach ($allTeamIds as $idx => $tid) {
            $positionMap[$tid] = $idx + 1;
        }

        $teamsData = $teams->map(function ($team) use ($positionMap, $totalTeams) {
            $competitors = $team->getCompetitors()->map(fn ($c) => [
                'id' => $c->id,
                'nome' => $c->nome,
                'foto' => $c->foto,
                'nivel' => $c->nivel,
                'points' => (float) ($c->pivot->current_points ?? 0),
                'is_captain' => (bool) ($c->pivot->is_captain ?? false),
            ]);

            return [
                'team' => [
                    'id' => $team->id,
                    'team_name' => $team->team_name,
                    'total_points' => (string) $team->total_points,
                    'fantasy_league_id' => $team->fantasy_league_id,
                    'position' => $positionMap[$team->id] ?? null,
                    'total_teams' => $totalTeams,
                ],
                'competitors' => $competitors,
            ];
        })->values()->all();

        // Retorna primeiro time em 'data' para compatibilidade, e todos em 'teams'
        return response()->json([
            'success' => true,
            'data' => $teamsData[0],
            'teams' => $teamsData,
        ]);
    }

    /**
     * Retorna ranking completo da liga.
     */
    public function leagueRanking(Request $request, int $leagueId)
    {
        $league = FantasyLeague::query()->find($leagueId);
        if (!$league) {
            return response()->json([
                'success' => false,
                'message' => 'Liga não encontrada',
            ], 404);
        }

        $user = auth()->user();
        $userId = $user ? (int) $user->id : null;
        $isPremium = $user && $user->isPremium();

        // Buscar ranking ordenado por pontos
        $teams = FantasyTeam::query()
            ->where('fantasy_league_id', $leagueId)
            ->where('is_active', true)
            ->with(['user:id,username,image,show_in_listings', 'user.subscriptions', 'botUser'])
            ->orderByDesc('total_points')
            ->orderBy('id')
            ->get();

        $totalTeams = count($teams);
        
        // 🎯 Calcular posições pagas
        $isFinalized = $league->status === 'finished' || $league->status === 'finalized';
        $paidPositions = $this->getFantasyLeaguePaidPositions($league, $totalTeams);
        $projectedBase = max($totalTeams, (int) ($league->max_users ?? 0));
        $projectedPaidPositions = $this->getFantasyLeaguePaidPositions($league, $projectedBase);
        $displayPaidPositions = max($isFinalized ? $paidPositions : $projectedPaidPositions, 3);
        
        // Sempre devolve todas as equipes ativas para o frontend montar
        // o ranking fixo completo com placeholders das vagas restantes.
        $limit = $totalTeams;

        $ranking = [];
        $myPosition = null;
        $myTeam = null;
        $myTeams = [];

        foreach ($teams as $index => $team) {
            $position = $index + 1;
            $identity = $this->resolveFantasyRankingIdentity($team);
            $canViewPoints = $isFinalized || ($userId && (int) $team->user_id === $userId);
            $visiblePoints = $canViewPoints ? (float) $team->total_points : null;

            $item = [
                'position' => $position,
                'team_id' => $team->id,
                'team_name' => $team->team_name,
                'user_id' => $team->user_id,
                'is_mine' => $userId ? ((int) $team->user_id === $userId) : false,
                'user_name' => $identity['name'],
                'display_name' => $identity['name'],
                'user_foto' => $identity['foto'],
                'points' => $visiblePoints,
                'can_view_points' => $canViewPoints,
                'is_premium' => $identity['is_premium'],
                'show_in_listings' => $identity['show_in_listings'],
            ];

            // Adicionar ao ranking até o limite configurado
            if ($position <= $limit) {
                $ranking[] = $item;
            }

            // Posições do usuário logado (pode ter múltiplos times)
            if ($userId && (int) $team->user_id === $userId) {
                if ($myPosition === null) {
                    $myPosition = $position; // Melhor posição
                    $myTeam = $item;
                }
                $myTeams[] = $item;
            }
        }

        $entryPrice = (float) ($league->price ?? 0);
        $houseCut = (float) ($league->house_cut_percent ?? 30);
        $dynamicPrizePool = $this->getFantasyLeagueDisplayPrizePool($league, $totalTeams, $isFinalized);
        $distribution = $this->getFantasyLeaguePrizeDistribution(
            $league,
            $isFinalized ? $paidPositions : $projectedPaidPositions
        );

        return response()->json([
            'success' => true,
            'data' => [
                'league_id' => $league->id,
                'league_name' => $league->name,
                'total_teams' => $totalTeams,
                'paid_positions' => $paidPositions,
                'projected_paid_positions' => $projectedPaidPositions,
                'display_paid_positions' => $displayPaidPositions,
                'entry_price' => number_format($entryPrice, 2, '.', ''),
                'house_cut_percent' => number_format($houseCut, 2, '.', ''),
                'prize_pool' => number_format($dynamicPrizePool, 2, '.', ''),
                'prize_type' => $league->prize_type ?? 'money',
                'prize_description' => trim((string) ($league->prize_description ?? '')) ?: null,
                'prize_items' => is_array($league->prize_items ?? null) ? $league->prize_items : null,
                'max_users' => $league->max_users,
                'ranking' => $ranking,
                'distribution' => $distribution,
                'is_finalized' => $isFinalized,
                'my_position' => $myPosition,
                'my_team' => $myTeam,
                'my_teams' => $myTeams,
            ],
        ]);
    }

    private function getFantasyLeaguePaidPositions(FantasyLeague $league, int $totalPlayers): int
    {
        if ($totalPlayers <= 0) {
            return 0;
        }

        $override = (int) ($league->paid_positions_override ?? 0);
        $maxUsers = (int) ($league->max_users ?? 0);

        if ($override > 0) {
            if ($maxUsers <= 0 || $totalPlayers >= $maxUsers) {
                return min($override, $totalPlayers);
            }
        }

        return $this->getFantasyPaidPositions($totalPlayers);
    }

    private function getFantasyLeagueDisplayPrizePool(FantasyLeague $league, int $totalTeams, bool $isFinalized): float
    {
        $configuredPrize = (float) ($league->total_prize ?? 0);
        if ($configuredPrize > 0) {
            return $configuredPrize;
        }

        $rewardMode = (string) ($league->reward_mode ?? 'computed');
        if ($rewardMode === 'manual_prize') {
            if (($league->prize_type ?? 'money') === 'money' && $league->manual_prize_pool !== null) {
                return max(0, (float) $league->manual_prize_pool);
            }

            return 0.0;
        }

        if ((bool) $league->is_premium) {
            return 0.0;
        }

        $entryPrice = (float) ($league->price ?? 0);
        if ($entryPrice <= 0) {
            return 0.0;
        }

        $houseCut = (float) ($league->house_cut_percent ?? 30);
        $baseTeamsForDisplay = $isFinalized ? $totalTeams : max($totalTeams, (int) ($league->max_users ?? 0));
        $totalCollection = $baseTeamsForDisplay * $entryPrice;

        return max(0, $totalCollection * (1 - ($houseCut / 100)));
    }

    private function normalizeFantasyDistributionForPaidPositions(array $distribution, int $paidPositions): array
    {
        if ($paidPositions <= 0) {
            return [];
        }

        $normalized = [];
        foreach ($distribution as $position => $percent) {
            $position = (int) $position;
            $percent = (float) $percent;
            if ($position < 1 || $position > $paidPositions || $percent < 0) {
                continue;
            }
            $normalized[$position] = $percent;
        }

        if (empty($normalized)) {
            return [];
        }

        ksort($normalized);
        $sum = array_sum($normalized);
        if ($sum <= 0) {
            return [];
        }

        foreach ($normalized as $position => $percent) {
            $normalized[$position] = round(($percent / $sum) * 100, 6);
        }

        $finalSum = array_sum($normalized);
        if (abs($finalSum - 100.0) > 0.0001 && isset($normalized[1])) {
            $normalized[1] = round($normalized[1] + (100.0 - $finalSum), 6);
        }

        return $normalized;
    }

    private function getFantasyLeaguePrizeDistribution(FantasyLeague $league, int $paidPositions): array
    {
        if (!empty($league->prize_distribution)) {
            $distribution = is_string($league->prize_distribution)
                ? json_decode($league->prize_distribution, true)
                : $league->prize_distribution;

            if (is_array($distribution) && !empty($distribution)) {
                $raw = [];
                foreach ($distribution as $pos => $pct) {
                    $raw[(int)$pos] = (float)$pct;
                }
                ksort($raw);
                $normalized = $this->normalizeFantasyDistributionForPaidPositions($raw, $paidPositions);
                if (!empty($normalized)) {
                    return $normalized;
                }
            }
        }

        return $this->getFantasyPrizeDistribution($paidPositions);
    }

    /**
     * Calcular posições pagas:
     * - sempre 10% dos inscritos (mín 1)
     * DEVE ser idêntico ao frontend (draft-arena.js → getPaidPositions)
     */
    private function getFantasyPaidPositions(int $totalPlayers): int
    {
        if ($totalPlayers <= 0) return 0;
        return max(1, (int) floor($totalPlayers * 10 / 100));
    }

    /**
     * Obter distribuição de prêmios para o ranking em tempo real
     */
    private function getFantasyPrizeDistribution(int $paidPositions): array
    {
        $tiers = $this->generateFantasyPrizeTiers($paidPositions);
        $distribution = [];

        foreach ($tiers as $tier) {
            $count = $tier['to'] - $tier['from'] + 1;
            $pctPerPerson = round($tier['pct'] / max(1, $count), 2);

            for ($pos = $tier['from']; $pos <= $tier['to']; $pos++) {
                $distribution[$pos] = $pctPerPerson;
            }
        }

        $sum = array_sum($distribution);
        if (!empty($distribution) && abs($sum - 100.0) > 0.01) {
            $distribution[1] = round(($distribution[1] ?? 0) + (100.0 - $sum), 2);
        }

        return $distribution;
    }

    /**
     * Gerar faixas de distribuição espelhando o serviço de finalização
     */
    private function generateFantasyPrizeTiers(int $paidPositions): array
    {
        if ($paidPositions <= 0) return [];
        if ($paidPositions === 1) return [['from' => 1, 'to' => 1, 'pct' => 100.0]];
        if ($paidPositions === 2) return [['from' => 1, 'to' => 1, 'pct' => 65.0], ['from' => 2, 'to' => 2, 'pct' => 35.0]];
        if ($paidPositions === 3) return [['from' => 1, 'to' => 1, 'pct' => 50.0], ['from' => 2, 'to' => 2, 'pct' => 30.0], ['from' => 3, 'to' => 3, 'pct' => 20.0]];

        $tiers = [
            ['from' => 1, 'to' => 1],
            ['from' => 2, 'to' => 2],
            ['from' => 3, 'to' => 3],
        ];

        $remaining = $paidPositions - 3;
        $pos = 4;

        if ($remaining <= 3) {
            $tiers[] = ['from' => 4, 'to' => $paidPositions];
        } else {
            $chunks = $remaining <= 8 ? 2 : ($remaining <= 20 ? 3 : 4);
            $base = (int) floor($remaining / $chunks);
            $extra = $remaining - ($base * $chunks);
            $sizes = [];

            for ($c = 0; $c < $chunks; $c++) {
                $sizes[] = $base + ($c < $extra ? 1 : 0);
            }

            sort($sizes);

            foreach ($sizes as $size) {
                $tiers[] = ['from' => $pos, 'to' => $pos + $size - 1];
                $pos += $size;
            }
        }

        $tierCount = count($tiers);
        $floorPctPerPerson = 100.0 / ($paidPositions * 3.6);
        $totalFloor = $floorPctPerPerson * $paidPositions;
        $curvePool = 100.0 - $totalFloor;

        $spread = max(3, pow($paidPositions, 1.2));
        $ratio = pow($spread, 1.0 / max(1, $tierCount - 1));

        $perPerson = array_fill(0, $tierCount, 0);
        $perPerson[$tierCount - 1] = 1;

        for ($i = $tierCount - 2; $i >= 0; $i--) {
            $perPerson[$i] = $perPerson[$i + 1] * $ratio;
        }

        $totalRaw = 0;
        for ($i = 0; $i < $tierCount; $i++) {
            $count = $tiers[$i]['to'] - $tiers[$i]['from'] + 1;
            $totalRaw += $perPerson[$i] * $count;
        }

        for ($i = 0; $i < $tierCount; $i++) {
            $count = $tiers[$i]['to'] - $tiers[$i]['from'] + 1;
            $curvePctPerPerson = $curvePool * $perPerson[$i] / max($totalRaw, 1);
            $totalPctPerPerson = $floorPctPerPerson + $curvePctPerPerson;
            $tiers[$i]['pct'] = round($totalPctPerPerson * $count, 2);
        }

        $sum = array_sum(array_column($tiers, 'pct'));
        if (abs($sum - 100.0) > 0.01) {
            $tiers[0]['pct'] = round($tiers[0]['pct'] + (100.0 - $sum), 2);
        }

        return $tiers;
    }

    /**
     * Obter estatísticas em tempo real (para atualização sem refresh)
     */
    public function liveStats(Request $request, $leagueId)
    {
        try {
            $fantasyService = app(\App\Services\FantasyPointsUpdateService::class);
            $stats = $fantasyService->getLiveStats($leagueId);

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Error getting live stats:', [
                'league_id' => $leagueId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas em tempo real'
            ], 500);
        }
    }

    /**
     * Buscar minhas equipes Fantasy
     * Para exibir no perfil do usuário
     */
    public function myTeams(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }
        
        try {
            // Buscar todas as equipes do usuário
            $teams = FantasyTeam::query()
                ->with(['fantasyLeague.rodeio', 'fantasyLeague.modalidade'])
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->orderByDesc('updated_at')
                ->get();
            
            $teamsData = $teams->map(function($team) {
                // Skip teams with deleted leagues
                if (!$team->fantasyLeague) {
                    return null;
                }
                
                // Buscar posição no ranking da liga
                $position = FantasyTeam::query()
                    ->where('fantasy_league_id', $team->fantasy_league_id)
                    ->where('is_active', true)
                    ->where('total_points', '>', $team->total_points)
                    ->count() + 1;
                
                // Contar competidores
                $competitorsCount = DB::table('fantasy_team_competitors')
                    ->where('fantasy_team_id', $team->id)
                    ->count();
                
                return [
                    'id' => $team->id,
                    'team_name' => $team->team_name,
                    'total_points' => (float) $team->total_points,
                    'position' => $position,
                    'competitors_count' => $competitorsCount,
                    'prize_won' => (float) ($team->prize_won ?? 0),
                    'prize_paid_at' => $team->prize_paid_at ? 
                        (is_string($team->prize_paid_at) ? $team->prize_paid_at : $team->prize_paid_at->format('d/m/Y')) 
                        : null,
                    'league_id' => $team->fantasy_league_id,
                    'league_name' => $team->fantasyLeague->name ?? 'Liga Fantasy',
                    'league_status' => $team->fantasyLeague->status ?? 'upcoming',
                    'rodeio_name' => $team->fantasyLeague->rodeio->name ?? $team->fantasyLeague->rodeio->nome ?? null,
                    'modalidade_name' => $team->fantasyLeague->modalidade->nome ?? null,
                ];
            })->filter()->values();
            
            return response()->json([
                'success' => true,
                'teams' => $teamsData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('[Fantasy] Erro ao buscar minhas equipes:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar equipes',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
