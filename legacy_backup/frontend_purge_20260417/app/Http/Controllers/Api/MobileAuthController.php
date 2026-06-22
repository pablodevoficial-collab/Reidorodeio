<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\FantasyTeam;
use App\Models\ReferralUser;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\X1Result;
use App\Models\X1RoomInstance;
use App\Services\AppProgressService;
use App\Services\ClaimedCompetitorProfileSyncService;
use App\Services\ReferralAttributionService;
use App\Services\SubscriptionService;
use App\Services\UserSocialAccountService;
use App\Services\X1StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class MobileAuthController extends Controller
{
    public function __construct(
        private ReferralAttributionService $referralAttributionService,
        private AppProgressService $progressService,
        private UserSocialAccountService $socialAccountService,
        private SubscriptionService $subscriptionService,
        private ClaimedCompetitorProfileSyncService $claimedCompetitorProfileSyncService
    ) {
    }

    public function checkUser(Request $request): JsonResponse
    {
        $field = strtolower(trim((string) $request->input('field', '')));
        $value = trim((string) $request->input('value', ''));

        if ($field === '' || $value === '') {
            return response()->json([
                'available' => false,
                'message' => 'Valor não informado.',
            ]);
        }

        if (!in_array($field, ['username', 'email', 'cpf'], true)) {
            return response()->json([
                'available' => false,
                'message' => 'Campo inválido para verificação.',
            ]);
        }

        if ($field === 'username') {
            if (!preg_match('/^[a-zA-Z0-9_-]{3,40}$/', $value)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Username inválido. Use 3-40 caracteres (letras, números, traços e underscores).',
                ]);
            }
        }

        if ($field === 'email') {
            $value = strtolower($value);
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Email inválido.',
                ]);
            }
        }

        if ($field === 'cpf') {
            $value = preg_replace('/\D+/', '', $value);
            if (strlen($value) !== 11 || !$this->isValidCpf($value)) {
                return response()->json([
                    'available' => false,
                    'message' => 'CPF inválido.',
                ]);
            }
        }

        $exists = User::query()->where($field, $value)->exists();
        $labels = ['username' => 'Nome de usuário', 'email' => 'Email', 'cpf' => 'CPF'];
        $label = $labels[$field] ?? ucfirst($field);

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? ($label . ' já está em uso') : ($label . ' disponível'),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cpf' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['nullable', 'boolean'],
            'device_name' => ['nullable', 'string', 'max:60'],
            'platform' => ['nullable', 'string', 'max:20'],
        ], [
            'cpf.required' => 'Informe seu CPF.',
            'password.required' => 'Informe sua senha.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf', $request->input('username', '')));

            if ($cpf === '') {
                $validator->errors()->add('cpf', 'Informe seu CPF.');
                return;
            }

            if (strlen($cpf) !== 11 || !$this->isValidCpf($cpf)) {
                $validator->errors()->add('cpf', 'Informe um CPF válido.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf', $request->input('username', '')));
        $password = (string) $request->input('password');

        $user = User::query()->where('cpf', $cpf)->first();

        if (!$user || !Hash::check($password, (string) $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['CPF ou senha incorretos.'],
            ], 401);
        }

        if ((int) $user->status === 0) {
            return response()->json([
                'success' => false,
                'errors' => ['Sua conta está suspensa.'],
            ], 403);
        }

        return $this->mobileSessionResponse(
            $user,
            $request,
            'Login realizado com sucesso.'
        );
    }

    public function socialAccess(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider' => ['required', 'string', 'in:google,facebook,apple'],
            'provider_id' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:60'],
            'lastname' => ['nullable', 'string', 'max:60'],
            'username_suggestion' => ['nullable', 'string', 'max:40'],
            'device_name' => ['nullable', 'string', 'max:60'],
            'platform' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $provider = trim((string) $request->input('provider'));
        $providerId = trim((string) $request->input('provider_id'));
        $email = strtolower(trim((string) $request->input('email', '')));

        $user = $this->socialAccountService->findUserByProvider($provider, $providerId);

        if (!$user && $email !== '') {
            $user = User::query()->where('email', $email)->first();
        }

        if ($user) {
            if ((int) $user->status === 0) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Sua conta está suspensa.'],
                ], 403);
            }

            try {
                $this->socialAccountService->syncAccount($user, $provider, [
                    'provider_id' => $providerId,
                    'email' => $email !== '' ? $email : null,
                    'first_name' => $request->filled('firstname') ? (string) $request->input('firstname') : null,
                    'last_name' => $request->filled('lastname') ? (string) $request->input('lastname') : null,
                    'name' => trim(
                        (string) $request->input('firstname', '') . ' ' . (string) $request->input('lastname', '')
                    ),
                ]);
            } catch (\RuntimeException $exception) {
                return response()->json([
                    'success' => false,
                    'errors' => [$exception->getMessage()],
                ], 422);
            }

            return $this->mobileSessionResponse(
                $user,
                $request,
                'Login social realizado com sucesso.',
                200,
                ['status' => 'authenticated']
            );
        }

        return response()->json([
            'success' => true,
            'status' => 'registration_required',
            'message' => 'Complete seu cadastro para continuar.',
            'social' => [
                'provider' => $provider,
                'provider_id' => $providerId,
                'email' => $email !== '' ? $email : null,
                'firstname' => $request->filled('firstname') ? (string) $request->input('firstname') : null,
                'lastname' => $request->filled('lastname') ? (string) $request->input('lastname') : null,
                'username_suggestion' => $request->filled('username_suggestion')
                    ? (string) $request->input('username_suggestion')
                    : null,
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Password::min(6)],
            'cpf' => ['required', 'string', 'size:11', 'unique:users,cpf'],
            'birthdate' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'agree' => ['accepted'],
            'remember' => ['nullable', 'boolean'],
            'device_name' => ['nullable', 'string', 'max:60'],
            'platform' => ['nullable', 'string', 'max:20'],
            'referral_token' => ['nullable', 'string'],
            'referral_code' => ['nullable', 'string', 'max:80'],
            'referral_affiliate_id' => ['nullable', 'integer'],
        ], [
            'password.required' => 'Crie uma senha.',
            'password.confirmed' => 'As senhas não conferem.',
            'cpf.required' => 'Informe seu CPF.',
            'cpf.size' => 'CPF deve ter 11 dígitos.',
            'birthdate.before_or_equal' => 'É necessário ter 18 anos ou mais para se cadastrar.',
            'agree.accepted' => 'Você deve aceitar os termos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf'));
        $referralToken = trim((string) $request->input('referral_token', ''));
        $referralCode = trim((string) $request->input('referral_code', ''));
        $referralAffiliateId = (int) $request->input('referral_affiliate_id', 0);

        $affiliate = $this->referralAttributionService->resolveAffiliate(
            $referralToken !== '' ? $referralToken : null,
            $referralCode !== '' ? $referralCode : null,
            $referralAffiliateId > 0 ? $referralAffiliateId : null
        );

        $user = new User();
        $user->username = $this->generateUsernameFromCpf($cpf);
        $user->email = $this->generateInternalEmail($cpf);
        $user->password = Hash::make((string) $request->input('password'));
        $user->cpf = $cpf;
        $user->birthdate = (string) $request->input('birthdate');
        $user->status = 1;
        $user->ev = 1;
        $user->sv = 1;
        $user->ts = 0;
        $user->tv = 1;
        $user->profile_complete = 0;
        $user->ref_by = 0;
        $user->referral_code = method_exists(User::class, 'getUniqueReferralCode')
            ? User::getUniqueReferralCode()
            : null;
        if ($affiliate) {
            $user->referred_by_id = $affiliate->user_id;
        }
        $user->save();

        if ($affiliate) {
            ReferralUser::create([
                'affiliate_id' => $affiliate->id,
                'referred_user_id' => $user->id,
                'status' => 'active',
            ]);

            $affiliate->increment('total_referrals');
            $affiliate->increment('active_referrals');
        }

        return $this->mobileSessionResponse(
            $user,
            $request,
            'Conta criada com sucesso.',
            201,
            ['profile_incomplete' => false]
        );
    }

    public function resolveReferral(Request $request): JsonResponse
    {
        $referralToken = trim((string) $request->input('referral_token', ''));
        $referralCode = trim((string) $request->input('referral_code', ''));

        $affiliate = $this->referralAttributionService->resolveAffiliate(
            $referralToken !== '' ? $referralToken : null,
            $referralCode !== '' ? $referralCode : null,
            null
        );

        if (!$affiliate) {
            return response()->json([
                'success' => false,
                'message' => 'Indicação inválida ou expirada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'referral' => [
                'affiliate_id' => (int) $affiliate->id,
                'referral_code' => (string) $affiliate->referral_code,
                'display_name' => (string) ($affiliate->user->firstname ?: $affiliate->user->username),
                'token' => $this->referralAttributionService->createToken($affiliate),
            ],
        ]);
    }

    public function socialLinkUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'provider' => ['required', 'string', 'in:google,facebook,apple'],
            'platform' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        /** @var User $user */
        $user = $request->user();
        $provider = trim((string) $request->input('provider'));
        $platform = trim((string) $request->input('platform', 'android'));
        $issued = $this->socialAccountService->issueLinkToken($user, $provider, $platform);

        $url = route('mobile.oauth.start', ['provider' => $provider]) . '?' . http_build_query([
            'platform' => $platform,
            'mode' => 'link',
            'link_token' => $issued['token'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'url' => $url,
                'expires_at' => $issued['expires_at'],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $this->serializeUser($request->user()),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], 401);
        }

        /** @var X1StatsService $x1StatsService */
        $x1StatsService = app(X1StatsService::class);
        $x1Stats = $x1StatsService->getUserStats($user->id, null) ?? [
            'total_x1s' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'win_rate' => 0,
            'total_prize_won' => 0,
            'total_invested' => 0,
            'profit' => 0,
            'current_streak' => 0,
            'best_win_streak' => 0,
            'worst_loss_streak' => 0,
            'rating' => 1000,
            'peak_rating' => 1000,
            'last_x1_at' => null,
            'ranking_position' => null,
            'total_ranked_players' => 0,
        ];

        $activeRooms = X1RoomInstance::query()
            ->with(['modalidade:id,nome', 'competitor:id,nome', 'competitorGroup:id,nome'])
            ->withCount('participants')
            ->where(function ($q) use ($user) {
                $q->where('host_user_id', $user->id)
                    ->orWhereHas('participants', function ($pq) use ($user) {
                        $pq->where('user_id', $user->id);
                    });
            })
            ->whereIn('status', ['pending_payment', 'open', 'in_progress'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function (X1RoomInstance $room) use ($user) {
                return [
                    'id' => (int) $room->id,
                    'name' => (string) ($room->name ?? 'Sala X1'),
                    'status' => (string) ($room->status ?? 'unknown'),
                    'is_host' => (int) $room->host_user_id === (int) $user->id,
                    'modalidade' => $room->modalidade?->nome,
                    'competitor' => $room->competitor?->nome,
                    'competitor_group' => $room->competitorGroup?->nome,
                    'valor_entrada' => (float) ($room->valor_entrada ?? 0),
                    'prize_total' => (float) ($room->prize_total ?? 0),
                    'participants_count' => (int) ($room->participants_count ?? 0),
                    'expires_at' => $room->expires_at?->toIso8601String(),
                    'created_at' => $room->created_at?->toIso8601String(),
                ];
            })
            ->values();

        $recentResults = X1Result::query()
            ->with([
                'room:id,name,modalidade_id,competitor_id,competitor_group_id,valor_entrada,prize_total',
                'room.modalidade:id,nome',
                'room.competitor:id,nome',
                'room.competitorGroup:id,nome',
            ])
            ->whereHas('room.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('processed_at')
            ->limit(10)
            ->get()
            ->map(function (X1Result $result) use ($user) {
                $room = $result->room;
                $isWinner = (int) ($result->winner_user_id ?? 0) === (int) $user->id;

                return [
                    'id' => (int) $result->id,
                    'room_id' => (int) ($room?->id ?? 0),
                    'room_name' => (string) ($room?->name ?? 'Sala X1'),
                    'modalidade' => $room?->modalidade?->nome,
                    'competitor' => $room?->competitor?->nome,
                    'competitor_group' => $room?->competitorGroup?->nome,
                    'result' => $isWinner ? 'victory' : 'defeat',
                    'is_winner' => $isWinner,
                    'valor_entrada' => (float) ($room?->valor_entrada ?? 0),
                    'prize_total' => (float) ($room?->prize_total ?? 0),
                    'profit' => $isWinner
                        ? ((float) ($room?->prize_total ?? 0) - (float) ($room?->valor_entrada ?? 0))
                        : (0 - (float) ($room?->valor_entrada ?? 0)),
                    'processed_at' => $result->processed_at?->toIso8601String(),
                ];
            })
            ->values();

        $teams = FantasyTeam::query()
            ->with([
                'fantasyLeague:id,name,status,rodeio_id,modalidade_id',
                'fantasyLeague.rodeio:id,name',
                'fantasyLeague.modalidade:id,nome',
            ])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->limit(40)
            ->get();

        $leagueRankingMaps = [];
        $leagueIds = $teams->pluck('fantasy_league_id')
            ->filter(fn ($id) => !empty($id))
            ->unique()
            ->values();

        foreach ($leagueIds as $leagueId) {
            $orderedIds = FantasyTeam::query()
                ->where('fantasy_league_id', (int) $leagueId)
                ->where('is_active', true)
                ->orderByDesc('total_points')
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $positions = [];
            foreach ($orderedIds as $index => $teamId) {
                $positions[(int) $teamId] = $index + 1;
            }

            $leagueRankingMaps[(int) $leagueId] = [
                'positions' => $positions,
                'total' => count($orderedIds),
            ];
        }

        $teamsData = $teams->map(function (FantasyTeam $team) use ($leagueRankingMaps) {
            $leagueId = (int) ($team->fantasy_league_id ?? 0);
            $rankingInfo = $leagueRankingMaps[$leagueId] ?? ['positions' => [], 'total' => 0];
            $position = $rankingInfo['positions'][(int) $team->id] ?? null;
            $totalTeams = (int) ($rankingInfo['total'] ?? 0);

            return [
                'id' => (int) $team->id,
                'team_name' => (string) ($team->team_name ?? 'Bolão'),
                'total_points' => (float) ($team->total_points ?? 0),
                'position' => $position ? (int) $position : null,
                'total_teams' => $totalTeams,
                'prize_won' => (float) ($team->prize_won ?? 0),
                'prize_paid_at' => $team->prize_paid_at
                    ? (is_string($team->prize_paid_at) ? $team->prize_paid_at : $team->prize_paid_at->toIso8601String())
                    : null,
                'league' => [
                    'id' => $leagueId ?: null,
                    'name' => $team->fantasyLeague?->name ?? 'Liga Bolão',
                    'status' => $team->fantasyLeague?->status ?? null,
                    'rodeio_name' => $team->fantasyLeague?->rodeio?->name ?? null,
                    'modalidade_name' => $team->fantasyLeague?->modalidade?->nome ?? null,
                ],
            ];
        })->values();

        $fantasyPrizeWonTotal = (float) $teamsData->sum('prize_won');
        $fantasyPrizePaidTotal = (float) $teamsData
            ->filter(fn ($team) => !empty($team['prize_paid_at']))
            ->sum('prize_won');
        $fantasyPrizePendingTotal = max(0.0, $fantasyPrizeWonTotal - $fantasyPrizePaidTotal);

        $wonX1Results = X1Result::query()
            ->with('room:id,prize_total')
            ->where('winner_user_id', $user->id)
            ->get();

        $x1PrizeWonTotal = (float) ($x1Stats['total_prize_won'] ?? 0);
        $x1PrizePaidTotal = (float) $wonX1Results
            ->filter(fn (X1Result $result) => !empty($result->prize_paid_at))
            ->sum(fn (X1Result $result) => (float) ($result->room?->prize_total ?? 0));
        $x1PrizePendingTotal = max(0.0, $x1PrizeWonTotal - $x1PrizePaidTotal);

        $totalEarnings = (float) ($user->total_earnings ?? 0);
        $receivableBalance = (float) ($user->receivable_balance ?? 0);
        $totalAccumulated = $x1PrizeWonTotal + $fantasyPrizeWonTotal;
        $totalReceived = $x1PrizePaidTotal + $fantasyPrizePaidTotal;
        $totalPending = $x1PrizePendingTotal + $fantasyPrizePendingTotal;
        $paidPrizesCount = $wonX1Results->filter(fn (X1Result $result) => !empty($result->prize_paid_at))->count()
            + $teamsData->filter(fn ($team) => !empty($team['prize_paid_at']))->count();
        $pendingPrizesCount = $wonX1Results->filter(fn (X1Result $result) => empty($result->prize_paid_at))->count()
            + $teamsData->filter(fn ($team) => empty($team['prize_paid_at']) && (float) ($team['prize_won'] ?? 0) > 0)->count();
        $requiresFullProfileForPrizes = method_exists($user, 'requiresFullProfileForPrizes')
            ? $user->requiresFullProfileForPrizes()
            : false;
        $profileMissingFields = $requiresFullProfileForPrizes && method_exists($user, 'getPrizeProfileMissingFields')
            ? $user->getPrizeProfileMissingFields()
            : [];
        $currentSubscription = method_exists($user, 'getCurrentSubscription')
            ? $user->getCurrentSubscription()
            : null;
        $subscriptionStatus = method_exists($user, 'subscriptionStatus')
            ? $user->subscriptionStatus()
            : [
                'is_premium' => false,
                'status' => 'free',
                'can_trial' => false,
            ];

        return response()->json([
            'success' => true,
            'user' => $this->serializeUser($user),
            'profile' => [
                'is_complete' => !$requiresFullProfileForPrizes || count($profileMissingFields) === 0,
                'must_complete_for_prize' => $requiresFullProfileForPrizes,
                'missing_fields' => $profileMissingFields,
                'pix_ready' => !empty($user->pix_key),
                'show_in_listings' => (bool) ($user->show_in_listings ?? true),
            ],
            'financial' => [
                'total_earnings' => $totalEarnings,
                'receivable_balance' => $receivableBalance,
                'total_accumulated' => $totalAccumulated,
                'total_received' => $totalReceived,
                'total_pending' => $totalPending,
                'paid_prizes_count' => (int) $paidPrizesCount,
                'pending_prizes_count' => (int) $pendingPrizesCount,
                'x1_total_prize_won' => $x1PrizeWonTotal,
                'x1_prize_paid_total' => $x1PrizePaidTotal,
                'x1_prize_pending_total' => $x1PrizePendingTotal,
                'fantasy_prize_won_total' => $fantasyPrizeWonTotal,
                'fantasy_prize_paid_total' => $fantasyPrizePaidTotal,
                'fantasy_prize_pending_total' => $fantasyPrizePendingTotal,
            ],
            'subscription' => array_merge($subscriptionStatus, [
                'status_label' => $currentSubscription?->status_label,
                'status_color' => $currentSubscription?->status_color,
                'payment_method_label' => $currentSubscription?->payment_method_label,
                'card_info' => $currentSubscription?->card_info,
                'can_activate_app_premium' => $this->subscriptionService->canActivateAppPremiumBenefit($user),
            ]),
            'x1' => [
                'stats' => $x1Stats,
                'active_rooms' => $activeRooms,
                'recent_results' => $recentResults,
            ],
            'fantasy' => [
                'teams' => $teamsData,
                'total_teams' => $teamsData->count(),
            ],
            'progress' => $this->progressService->overview($user),
        ]);
    }

    public function activateAppPremium(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isPremium()) {
            return response()->json([
                'success' => true,
                'message' => 'Sua conta já está com Premium ativo.',
                'user' => $this->serializeUser($user),
            ]);
        }

        if (!$this->subscriptionService->canActivateAppPremiumBenefit($user)) {
            return response()->json([
                'success' => false,
                'message' => $this->subscriptionService->getAppPremiumBenefitIneligibilityReason($user)
                    ?? 'O benefício do app não está disponível para esta conta.',
            ], 422);
        }

        $plan = $this->subscriptionService->findPlanBySlug('mensal');
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plano Premium mensal não encontrado.',
            ], 404);
        }

        $platform = trim((string) $request->input('platform', 'mobile'));

        $this->subscriptionService->grantAppPremiumBenefit(
            $user,
            $plan,
            30,
            $platform !== '' ? $platform : 'mobile'
        );

        $freshUser = $user->fresh();

        return response()->json([
            'success' => true,
            'message' => '1 mês de Premium ativado com sucesso na sua conta.',
            'user' => $this->serializeUser($freshUser),
        ], 201);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'firstname' => ['nullable', 'string', 'max:60'],
            'lastname' => ['nullable', 'string', 'max:60'],
            'username' => ['nullable', 'string', 'min:3', 'max:40', 'alpha_dash', 'unique:users,username,' . $user->id],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'mobile' => ['nullable', 'string', 'max:30'],
            'cpf' => ['nullable', 'string', 'size:11', 'unique:users,cpf,' . $user->id],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'pix_key_type' => ['nullable', 'string', 'max:20'],
            'pix_key' => ['nullable', 'string', 'max:120'],
            'show_in_listings' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $errors = [];
        $mobile = $request->filled('mobile') ? preg_replace('/\D+/', '', (string) $request->input('mobile')) : null;
        $cpf = $request->filled('cpf') ? preg_replace('/\D+/', '', (string) $request->input('cpf')) : null;
        $wantsUsername = $request->filled('username');
        $newUsername = trim((string) $request->input('username'));

        if ($cpf !== null && $cpf !== '' && !preg_match('/^\d{11}$/', $cpf)) {
            $errors[] = 'CPF inválido. Informe 11 dígitos.';
        }

        if ($mobile !== null && $mobile !== '' && !preg_match('/^\d{8,15}$/', $mobile)) {
            $errors[] = 'WhatsApp inválido. Informe apenas números (8 a 15 dígitos).';
        }

        if (!empty($user->cpf) && $cpf !== null && $cpf !== '' && $cpf !== (string) $user->cpf) {
            $errors[] = 'CPF já está cadastrado e não pode ser alterado.';
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'errors' => $errors,
            ], 422);
        }

        if ($request->filled('firstname')) {
            $user->firstname = $request->input('firstname');
        }
        if ($request->filled('lastname')) {
            $user->lastname = $request->input('lastname');
        }
        if ($request->filled('email')) {
            $user->email = strtolower(trim((string) $request->input('email')));
        }
        if ($request->filled('birthdate')) {
            $user->birthdate = $request->input('birthdate');
        }
        if ($mobile !== null && $mobile !== '') {
            $user->mobile = $mobile;
        }
        if ($cpf !== null && $cpf !== '' && empty($user->cpf)) {
            $user->cpf = $cpf;
        }
        if ($wantsUsername && $newUsername !== '') {
            $user->username = $newUsername;
        }
        if ($request->has('pix_key_type')) {
            $user->pix_key_type = $request->input('pix_key_type') ?: null;
        }
        if ($request->has('pix_key')) {
            $user->pix_key = $request->input('pix_key') ?: null;
        }
        if ($request->has('show_in_listings')) {
            $user->show_in_listings = (bool) $request->boolean('show_in_listings');
        }

        $user->save();
        $freshUser = $user->fresh();
        $this->claimedCompetitorProfileSyncService->syncFromUser($freshUser);
        $this->progressService->syncForUser($user->fresh());

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso.',
            'user' => $this->serializeUser($freshUser),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $oldImage = $user->image;
        $suffix = $user->id . '_' . now()->timestamp;

        DB::beginTransaction();
        try {
            $user->status = 0;
            $user->firstname = 'Conta';
            $user->lastname = 'Excluida';
            $user->username = 'deleted_user_' . $suffix;
            $user->email = 'deleted_' . $suffix . '@deleted.local';
            $user->mobile = null;
            $user->cpf = null;
            $user->birthdate = null;
            $user->image = null;
            $user->provider = null;
            $user->provider_id = null;
            $user->pix_key_type = null;
            $user->pix_key = null;
            $user->password = Hash::make(Str::random(40));
            $user->current_session_id = null;

            if (array_key_exists('show_in_listings', $user->getAttributes())) {
                $user->show_in_listings = false;
            }

            $user->save();
            $user->socialAccounts()->delete();
            $user->tokens()->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível excluir a conta agora.',
            ], 500);
        }

        if (!empty($oldImage)) {
            $path = public_path('assets/images/user/profile/' . $oldImage);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Conta excluída com sucesso.',
        ]);
    }

    public function webviewUrl(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $platform = trim((string) $request->input('platform', ''));
        $tab = trim((string) $request->input('tab', ''));

        $params = [
            'uid' => $user->id,
            'app' => '1',
        ];

        if ($platform !== '') {
            $params['platform'] = Str::limit($platform, 20, '');
        }

        if ($tab !== '') {
            $params['tab'] = Str::limit($tab, 30, '');
        }

        $params['remember'] = $request->boolean('remember', true) ? '1' : '0';

        $url = URL::temporarySignedRoute(
            'mobile.webview.entry',
            now()->addMinutes(3),
            $params
        );

        return response()->json([
            'success' => true,
            'url' => $url,
            'expires_in_seconds' => 180,
        ]);
    }

    private function createToken(User $user, Request $request): string
    {
        $deviceName = trim((string) $request->input('device_name', 'mobile-app'));
        if ($deviceName === '') {
            $deviceName = 'mobile-app';
        }

        return $user->createToken('mobile:' . Str::limit($deviceName, 40, ''), ['mobile'])->plainTextToken;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        $connectedSocialAccounts = $this->safeConnectedSocialAccounts($user);
        $isPremium = $this->safeIsPremium($user);
        $profileComplete = $this->safeProfileComplete($user);

        return [
            'id' => $user->id,
            'username' => $user->username,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'cpf' => $user->cpf,
            'birthdate' => $user->birthdate ? (string) $user->birthdate->format('Y-m-d') : null,
            'pix_key_type' => $user->pix_key_type ?? null,
            'pix_key' => $user->pix_key ?? null,
            'show_in_listings' => (bool) ($user->show_in_listings ?? true),
            'avatar' => $user->image ? asset('assets/images/user/profile/' . $user->image) : null,
            'social_provider' => $this->socialAccountService->normalizeProvider((string) $user->provider),
            'connected_social_accounts' => $connectedSocialAccounts,
            'is_premium' => $isPremium,
            'profile_complete' => $profileComplete,
        ];
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function mobileSessionResponse(
        User $user,
        Request $request,
        string $message,
        int $status = 200,
        array $extra = []
    ): JsonResponse {
        try {
            $token = $this->createToken($user, $request);
            $this->logUserLogin($user, $request);

            return response()->json(array_merge([
                'success' => true,
                'message' => $message,
                'token' => $token,
                'user' => $this->serializeUser($user),
            ], $extra), $status);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'errors' => [
                    'O servidor do app ainda não concluiu a configuração do login mobile. Execute as migrations pendentes e tente novamente.',
                ],
            ], 500);
        }
    }

    /**
     * @return array<string, bool>
     */
    private function safeConnectedSocialAccounts(User $user): array
    {
        try {
            return $this->socialAccountService->connectedProviders($user);
        } catch (\Throwable $e) {
            report($e);

            return [
                'google' => false,
                'facebook' => false,
                'apple' => false,
            ];
        }
    }

    private function safeIsPremium(User $user): bool
    {
        try {
            return method_exists($user, 'isPremium') ? (bool) $user->isPremium() : false;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private function safeProfileComplete(User $user): ?bool
    {
        try {
            if (method_exists($user, 'requiresFullProfileForPrizes') && method_exists($user, 'isPrizeProfileComplete')) {
                return !$user->requiresFullProfileForPrizes() || (bool) $user->isPrizeProfileComplete();
            }

            return method_exists($user, 'isProfileComplete') ? (bool) $user->isProfileComplete() : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function logUserLogin(User $user, Request $request): void
    {
        $ip = $request->ip();
        $exist = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude = $exist->longitude;
            $userLogin->latitude = $exist->latitude;
            $userLogin->city = $exist->city;
            $userLogin->country = $exist->country;
            $userLogin->country_code = $exist->country_code;
        } else {
            $info = getIpInfo();
            $userLogin->longitude = $info['lon'] ?? $info['long'] ?? null;
            $userLogin->latitude = $info['lat'] ?? null;
            $userLogin->city = $info['city'] ?? null;
            $userLogin->country = $info['country'] ?? null;
            $userLogin->country_code = $info['countryCode'] ?? $info['code'] ?? null;
        }

        $osBrowser = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;
        $userLogin->browser = $osBrowser['browser'] ?? null;
        $userLogin->os = $osBrowser['os_platform'] ?? null;
        $userLogin->save();
    }

    private function isValidCpf(string $cpf): bool
    {
        if (!preg_match('/^\d{11}$/', $cpf)) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $cpf[$i]) * (10 - $i);
        }

        $rest = ($sum * 10) % 11;
        if ($rest === 10) {
            $rest = 0;
        }
        if ($rest !== (int) $cpf[9]) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $cpf[$i]) * (11 - $i);
        }

        $rest = ($sum * 10) % 11;
        if ($rest === 10) {
            $rest = 0;
        }

        return $rest === (int) $cpf[10];
    }

    private function generateUsernameFromCpf(string $cpf): string
    {
        $suffix = substr($cpf, -6);
        $base = 'rr_' . $suffix;
        $candidate = $base;

        while (User::query()->where('username', $candidate)->exists()) {
            $candidate = Str::limit($base . '_' . random_int(1000, 9999), 40, '');
        }

        return $candidate;
    }

    private function generateInternalEmail(string $cpf): string
    {
        $candidate = 'cpf' . $cpf . '@cadastro.local';

        while (User::query()->where('email', $candidate)->exists()) {
            $candidate = 'cpf' . $cpf . '+' . random_int(1000, 9999) . '@cadastro.local';
        }

        return strtolower($candidate);
    }
}

