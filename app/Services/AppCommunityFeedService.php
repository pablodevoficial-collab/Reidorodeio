<?php

namespace App\Services;

use App\Models\AppCommunityPost;
use App\Models\AppUserRewardUnlock;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\Rodeio;
use App\Models\User;
use App\Models\X1RoomInstance;
use Illuminate\Support\Collection;

class AppCommunityFeedService
{
    public function officialTimeline(int $limit = 8): Collection
    {
        $eventPosts = Rodeio::query()
            ->withCount('modalidades')
            ->where(function ($query) {
                $query->whereNull('status_transmissao')
                    ->orWhere('status_transmissao', '!=', 'finalizado');
            })
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Rodeio $rodeio) => $this->mapOfficialEventPost($rodeio));

        $leaguePosts = FantasyLeague::query()
            ->with(['rodeio:id,name', 'modalidade:id,nome'])
            ->where('is_active', true)
            ->latest('updated_at')
            ->limit(max($limit, 6))
            ->get()
            ->filter(fn (FantasyLeague $league) => $league->isRegistrationOpen())
            ->take(4)
            ->map(fn (FantasyLeague $league) => $this->mapOfficialLeaguePost($league));

        return $eventPosts
            ->concat($leaguePosts)
            ->sortByDesc(fn (array $item) => (string) ($item['created_at'] ?? ''))
            ->take($limit)
            ->values();
    }

    public function postMessage(User $user, string $text, ?string $emoji = null): AppCommunityPost
    {
        return AppCommunityPost::create([
            'type' => 'message',
            'subtype' => 'chat_message',
            'user_id' => $user->id,
            'emoji' => $this->normalizeEmoji($emoji),
            'body' => trim($text),
            'metadata' => [
                'actor_user_id' => (int) $user->id,
                'actor_username' => (string) ($user->username ?? 'usuario'),
                'actor_name' => $this->displayName($user),
                'actor_avatar_url' => $this->userAvatarUrl($user),
            ],
        ]);
    }

    public function publishFantasyTeamJoined(FantasyTeam $team): ?AppCommunityPost
    {
        $team->loadMissing([
            'user:id,username,firstname,lastname,image,show_in_listings',
            'fantasyLeague.rodeio:id,name',
            'fantasyLeague.modalidade:id,nome',
        ]);

        if (!$team->fantasyLeague || !$team->user) {
            return null;
        }

        $league = $team->fantasyLeague;
        $dedupeKey = 'fantasy_team_joined:' . $team->id;

        return AppCommunityPost::firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'type' => 'feed',
                'subtype' => 'fantasy_team_joined',
                'user_id' => $team->user_id,
                'emoji' => '🏆',
                'title' => $this->displayName($team->user) . ' entrou no bolão',
                'body' => $league->name,
                'metadata' => [
                    'team_id' => (int) $team->id,
                    'team_name' => $team->team_name,
                    'league_id' => (int) $league->id,
                    'league_name' => $league->name,
                    'rodeio' => $league->rodeio?->name,
                    'modalidade' => $league->modalidade?->nome,
                    'entry_price' => (float) ($league->price ?? 0),
                    'prize_total' => max(
                        (float) ($league->total_prize ?? 0),
                        (float) ($league->manual_prize_pool ?? 0)
                    ),
                    'image_url' => $this->publicImageUrl($league->image),
                    'actor_user_id' => (int) $team->user->id,
                    'actor_username' => (string) ($team->user->username ?? 'usuario'),
                    'actor_name' => $this->displayName($team->user),
                    'actor_avatar_url' => $this->userAvatarUrl($team->user),
                ],
            ]
        );
    }

    public function publishX1RoomMatched(X1RoomInstance $room): ?AppCommunityPost
    {
        $room->loadMissing([
            'host:id,username,firstname,lastname,image,show_in_listings',
            'modalidade:id,nome',
            'rodeio:id,name',
            'participants.user:id,username,firstname,lastname,image,show_in_listings',
        ]);

        $participants = $room->participants
            ->where('payment_status', 'paid')
            ->sortBy('slot')
            ->values();

        if ($participants->count() < 2) {
            return null;
        }

        $hostParticipant = $participants->firstWhere('is_host', true) ?? $participants->get(0);
        $opponentParticipant = $participants->firstWhere('is_host', false) ?? $participants->get(1);

        if (!$hostParticipant?->user || !$opponentParticipant?->user) {
            return null;
        }

        $dedupeKey = 'x1_room_matched:' . $room->id;

        return AppCommunityPost::firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'type' => 'feed',
                'subtype' => 'x1_room_matched',
                'user_id' => $hostParticipant->user->id,
                'emoji' => '🔥',
                'title' => 'Sala X1 efetivada',
                'body' => $room->name ?: ('Sala X1 #' . $room->id),
                'metadata' => [
                    'room_id' => (int) $room->id,
                    'room_name' => $room->name ?: ('Sala X1 #' . $room->id),
                    'host_name' => $this->displayName($hostParticipant->user),
                    'opponent_name' => $this->displayName($opponentParticipant->user),
                    'entry_amount' => (float) ($room->valor_entrada ?? 0),
                    'prize_total' => (float) ($room->prize_total ?? 0),
                    'rodeio' => $room->rodeio?->name,
                    'modalidade' => $room->modalidade?->nome,
                    'actor_user_id' => (int) $hostParticipant->user->id,
                    'actor_username' => (string) ($hostParticipant->user->username ?? 'usuario'),
                    'actor_name' => $this->displayName($hostParticipant->user),
                    'actor_avatar_url' => $this->userAvatarUrl($hostParticipant->user),
                ],
            ]
        );
    }

    public function publishRewardUnlocked(User $user, AppUserRewardUnlock $unlock): ?AppCommunityPost
    {
        $dedupeKey = 'reward_unlocked:' . $unlock->user_id . ':' . $unlock->code;

        return AppCommunityPost::firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'type' => 'feed',
                'subtype' => 'reward_unlocked',
                'user_id' => $user->id,
                'emoji' => $unlock->icon ?: '⭐',
                'title' => $this->displayName($user) . ' desbloqueou uma recompensa',
                'body' => $unlock->title,
                'metadata' => [
                    'reward_code' => $unlock->code,
                    'reward_title' => $unlock->title,
                    'reward_description' => $unlock->description,
                    'actor_user_id' => (int) $user->id,
                    'actor_username' => (string) ($user->username ?? 'usuario'),
                    'actor_name' => $this->displayName($user),
                    'actor_avatar_url' => $this->userAvatarUrl($user),
                ],
            ]
        );
    }

    private function normalizeEmoji(?string $emoji): ?string
    {
        $value = trim((string) ($emoji ?? ''));
        return $value === '' ? null : mb_substr($value, 0, 12);
    }

    private function mapOfficialEventPost(Rodeio $rodeio): array
    {
        $status = strtolower(trim((string) ($rodeio->status_transmissao ?? 'programado')));
        $eventName = trim((string) ($rodeio->name ?? 'Evento'));
        $title = 'Novo evento cadastrado pela Rei do Rodeio';
        $body = $eventName;
        $emoji = '📅';

        if ($status === 'ao_vivo') {
            $title = $eventName . ' está ao vivo';
            $body = 'A arena já está movimentando o evento em tempo real.';
            $emoji = '🔴';
        } elseif ($status === 'ativo') {
            $title = 'Evento em destaque na arena';
            $body = $eventName;
            $emoji = '📢';
        }

        return [
            'id' => -100000 - (int) $rodeio->id,
            'type' => 'feed',
            'subtype' => 'official_event_update',
            'emoji' => $emoji,
            'title' => $title,
            'body' => $body,
            'created_at' => optional($rodeio->updated_at ?? $rodeio->created_at)->toIso8601String()
                ?? now()->toIso8601String(),
            'actor_user_id' => null,
            'actor_username' => 'reidorodeio',
            'actor_name' => 'Rei do Rodeio',
            'actor_avatar_url' => null,
            'relationship' => $this->emptyRelationship(),
            'metadata' => [
                'rodeio' => $eventName,
                'status' => $status,
                'status_label' => $status === 'ao_vivo'
                    ? 'Ao vivo'
                    : ($status === 'ativo' ? 'Em destaque' : 'Programado'),
                'modalidades_count_label' => (int) ($rodeio->modalidades_count ?? 0) > 0
                    ? ((int) $rodeio->modalidades_count) . ' modalidades'
                    : null,
            ],
        ];
    }

    private function mapOfficialLeaguePost(FantasyLeague $league): array
    {
        return [
            'id' => -200000 - (int) $league->id,
            'type' => 'feed',
            'subtype' => 'official_fantasy_league_open',
            'emoji' => '🏆',
            'title' => 'Novo bolão liberado pela Rei do Rodeio',
            'body' => 'Inscrições abertas para ' . trim((string) ($league->name ?? 'novo bolão')),
            'created_at' => optional($league->updated_at ?? $league->created_at)->toIso8601String()
                ?? now()->toIso8601String(),
            'actor_user_id' => null,
            'actor_username' => 'reidorodeio',
            'actor_name' => 'Rei do Rodeio',
            'actor_avatar_url' => null,
            'relationship' => $this->emptyRelationship(),
            'metadata' => [
                'league_id' => (int) $league->id,
                'league_name' => (string) ($league->name ?? 'Bolão'),
                'rodeio' => $league->rodeio?->name,
                'modalidade' => $league->modalidade?->nome,
                'entry_amount' => (float) ($league->price ?? 0),
                'prize_total' => max(
                    (float) ($league->total_prize ?? 0),
                    (float) ($league->manual_prize_pool ?? 0)
                ),
                'registration_label' => 'Inscrições abertas',
                'image_url' => $this->publicImageUrl($league->image),
            ],
        ];
    }

    private function displayName(User $user): string
    {
        $fullName = trim((string) (($user->firstname ?? '') . ' ' . ($user->lastname ?? '')));
        if ($fullName !== '') {
            return $fullName;
        }

        if (method_exists($user, 'getPublicUsername')) {
            return (string) $user->getPublicUsername();
        }

        return (string) ($user->username ?? 'Usuário');
    }

    private function userAvatarUrl(User $user): ?string
    {
        $image = trim((string) ($user->image ?? ''));
        if ($image === '') {
            return null;
        }

        return asset('assets/images/user/profile/' . ltrim($image, '/'));
    }

    private function publicImageUrl(?string $path): ?string
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
            $value = substr($value, 7);
            $lower = strtolower($value);
        }

        if (str_starts_with($lower, 'assets/')) {
            return asset($value);
        }

        $resolved = publicStorageUrl($value);

        return $resolved !== '' ? $resolved : null;
    }

    private function emptyRelationship(): array
    {
        return [
            'is_self' => false,
            'is_friend' => false,
            'pending_sent' => false,
            'pending_received' => false,
            'blocked' => false,
            'can_message' => false,
        ];
    }
}
