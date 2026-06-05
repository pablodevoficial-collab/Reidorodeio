<?php

namespace App\Services;

use App\Models\AppCommunityPost;
use App\Models\AppFriendRequest;
use App\Models\AppUserRewardUnlock;
use App\Models\FantasyTeam;
use App\Models\User;
use App\Models\X1RoomInstance;
use Illuminate\Support\Collection;

class AppProgressService
{
    public function __construct(
        private readonly X1StatsService $x1StatsService,
        private readonly AppCommunityFeedService $feedService
    ) {
    }

    public function overview(User $user): array
    {
        $metrics = $this->metrics($user);
        $unlocks = $this->syncForUser($user, $metrics);
        $definitions = $this->definitions();

        $xp = (int) $unlocks->sum(fn (AppUserRewardUnlock $unlock) => (int) data_get($unlock->metadata, 'xp', 0));
        $xp += ((int) $metrics['total_x1s'] * 4)
            + ((int) $metrics['x1_wins'] * 6)
            + ((int) $metrics['fantasy_teams'] * 5)
            + ((int) $metrics['friends_count'] * 8)
            + ((int) $metrics['community_messages'] * 2)
            + ($metrics['is_premium'] ? 15 : 0);

        $levelSize = 120;
        $level = max(1, intdiv($xp, $levelSize) + 1);
        $currentLevelXp = ($level - 1) * $levelSize;
        $nextLevelXp = $level * $levelSize;
        $progressPercent = (int) min(100, round((($xp - $currentLevelXp) / $levelSize) * 100));

        $unlockedCodes = $unlocks->pluck('code')->all();
        $nextRewards = collect($definitions)
            ->reject(fn (array $definition, string $code) => in_array($code, $unlockedCodes, true))
            ->take(3)
            ->map(fn (array $definition, string $code) => [
                'code' => $code,
                'title' => $definition['title'],
                'description' => $definition['description'],
                'icon' => $definition['icon'],
                'xp' => $definition['xp'],
            ])
            ->values()
            ->all();

        return [
            'level' => $level,
            'xp' => $xp,
            'current_level_xp' => $currentLevelXp,
            'next_level_xp' => $nextLevelXp,
            'progress_percent' => $progressPercent,
            'friends_count' => (int) $metrics['friends_count'],
            'community_messages' => (int) $metrics['community_messages'],
            'unlocked_count' => $unlocks->count(),
            'rewards' => $unlocks
                ->sortByDesc(fn (AppUserRewardUnlock $unlock) => optional($unlock->unlocked_at)->getTimestamp() ?? 0)
                ->values()
                ->map(fn (AppUserRewardUnlock $unlock) => [
                    'code' => $unlock->code,
                    'title' => $unlock->title,
                    'description' => $unlock->description,
                    'icon' => $unlock->icon,
                    'unlocked_at' => optional($unlock->unlocked_at)->toIso8601String(),
                    'xp' => (int) data_get($unlock->metadata, 'xp', 0),
                ])
                ->all(),
            'next_rewards' => $nextRewards,
        ];
    }

    public function syncForUser(User $user, ?array $metrics = null): Collection
    {
        $metrics ??= $this->metrics($user);
        $definitions = $this->definitions();

        foreach ($definitions as $code => $definition) {
            $condition = $definition['condition'];
            if (!$condition($metrics, $user)) {
                continue;
            }

            $unlock = AppUserRewardUnlock::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'code' => $code,
                ],
                [
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'],
                    'metadata' => [
                        'xp' => (int) $definition['xp'],
                    ],
                    'unlocked_at' => now(),
                ]
            );

            if ($unlock->wasRecentlyCreated) {
                $this->feedService->publishRewardUnlocked($user, $unlock);
            }
        }

        return AppUserRewardUnlock::query()
            ->where('user_id', $user->id)
            ->orderByDesc('unlocked_at')
            ->get();
    }

    private function metrics(User $user): array
    {
        $x1Stats = $this->x1StatsService->getUserStats($user->id, null) ?? [];
        $fantasyTeams = FantasyTeam::query()->where('user_id', $user->id)->count();
        $communityMessages = AppCommunityPost::query()
            ->where('type', 'message')
            ->where('user_id', $user->id)
            ->count();
        $friendsCount = AppFriendRequest::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($user) {
                $query->where('sender_user_id', $user->id)
                    ->orWhere('receiver_user_id', $user->id);
            })
            ->count();
        $x1Rooms = X1RoomInstance::query()
            ->where(function ($query) use ($user) {
                $query->where('host_user_id', $user->id)
                    ->orWhereHas('participants', function ($participantQuery) use ($user) {
                        $participantQuery->where('user_id', $user->id);
                    });
            })
            ->distinct('id')
            ->count('id');

        return [
            'profile_complete' => method_exists($user, 'requiresFullProfileForPrizes') && method_exists($user, 'isPrizeProfileComplete')
                ? (!$user->requiresFullProfileForPrizes() || $user->isPrizeProfileComplete())
                : ($user->isProfileComplete() && !empty($user->pix_key)),
            'is_premium' => $user->isPremium(),
            'friends_count' => (int) $friendsCount,
            'community_messages' => (int) $communityMessages,
            'fantasy_teams' => (int) $fantasyTeams,
            'x1_rooms' => (int) $x1Rooms,
            'x1_wins' => (int) ($x1Stats['wins'] ?? 0),
            'total_x1s' => (int) ($x1Stats['total_x1s'] ?? 0),
        ];
    }

    private function definitions(): array
    {
        return [
            'profile_complete' => [
                'title' => 'Perfil Completo',
                'description' => 'Preencheu seus dados essenciais e deixou a conta pronta para saques e desafios.',
                'icon' => '🛡️',
                'xp' => 30,
                'condition' => fn (array $metrics) => $metrics['profile_complete'] === true,
            ],
            'first_friend' => [
                'title' => 'Primeira Conexão',
                'description' => 'Adicionou o primeiro amigo dentro da comunidade.',
                'icon' => '🤝',
                'xp' => 25,
                'condition' => fn (array $metrics) => $metrics['friends_count'] >= 1,
            ],
            'first_message' => [
                'title' => 'Voz da Arena',
                'description' => 'Enviou sua primeira mensagem na comunidade do app.',
                'icon' => '💬',
                'xp' => 12,
                'condition' => fn (array $metrics) => $metrics['community_messages'] >= 1,
            ],
            'first_x1_room' => [
                'title' => 'Entrou na Arena',
                'description' => 'Participou da sua primeira sala X1.',
                'icon' => '🔥',
                'xp' => 24,
                'condition' => fn (array $metrics) => $metrics['x1_rooms'] >= 1,
            ],
            'first_x1_win' => [
                'title' => 'Primeira Vitória X1',
                'description' => 'Conquistou sua primeira vitória em uma sala X1.',
                'icon' => '🏆',
                'xp' => 36,
                'condition' => fn (array $metrics) => $metrics['x1_wins'] >= 1,
            ],
            'x1_veteran' => [
                'title' => 'Veterano do X1',
                'description' => 'Chegou à marca de 10 disputas na arena X1.',
                'icon' => '🤠',
                'xp' => 50,
                'condition' => fn (array $metrics) => $metrics['total_x1s'] >= 10,
            ],
            'fantasy_manager' => [
                'title' => 'Manager do Bolão',
                'description' => 'Montou sua primeira equipe no bolão.',
                'icon' => '📋',
                'xp' => 20,
                'condition' => fn (array $metrics) => $metrics['fantasy_teams'] >= 1,
            ],
            'fantasy_elite' => [
                'title' => 'Técnico de Elite',
                'description' => 'Chegou a cinco equipes montadas no bolão.',
                'icon' => '⭐',
                'xp' => 42,
                'condition' => fn (array $metrics) => $metrics['fantasy_teams'] >= 5,
            ],
            'premium_member' => [
                'title' => 'Membro Premium',
                'description' => 'Ativou a assinatura premium e liberou recursos avançados.',
                'icon' => '👑',
                'xp' => 28,
                'condition' => fn (array $metrics) => $metrics['is_premium'] === true,
            ],
        ];
    }
}
