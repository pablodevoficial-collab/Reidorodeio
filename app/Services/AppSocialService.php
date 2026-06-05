<?php

namespace App\Services;

use App\Models\AppDirectMessage;
use App\Models\AppFriendRequest;
use App\Models\AppUserBlock;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AppSocialService
{
    public function __construct(
        private readonly NativePushService $pushService,
        private readonly AppProgressService $progressService
    ) {
    }

    public function friendIds(User $viewer): array
    {
        return AppFriendRequest::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($viewer) {
                $query->where('sender_user_id', $viewer->id)
                    ->orWhere('receiver_user_id', $viewer->id);
            })
            ->get(['sender_user_id', 'receiver_user_id'])
            ->map(function (AppFriendRequest $request) use ($viewer) {
                return (int) $request->sender_user_id === (int) $viewer->id
                    ? (int) $request->receiver_user_id
                    : (int) $request->sender_user_id;
            })
            ->filter(fn (int $friendId) => $friendId > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function friends(User $viewer, int $limit = 12): Collection
    {
        $blockedIds = $this->blockedUserIds($viewer);

        $friendRequests = AppFriendRequest::query()
            ->with([
                'sender:id,username,firstname,lastname,image,show_in_listings',
                'receiver:id,username,firstname,lastname,image,show_in_listings',
            ])
            ->where('status', 'accepted')
            ->where(function ($query) use ($viewer) {
                $query->where('sender_user_id', $viewer->id)
                    ->orWhere('receiver_user_id', $viewer->id);
            })
            ->latest('responded_at')
            ->get();

        $friends = $friendRequests->map(function (AppFriendRequest $request) use ($viewer) {
            return (int) $request->sender_user_id === (int) $viewer->id
                ? $request->receiver
                : $request->sender;
        })->filter();

        if ($blockedIds) {
            $friends = $friends->reject(fn (User $user) => in_array((int) $user->id, $blockedIds, true));
        }

        $friendIds = $friends->pluck('id')->map(fn ($value) => (int) $value)->unique()->values();
        $unreadCounts = AppDirectMessage::query()
            ->selectRaw('sender_user_id, COUNT(*) as unread_count')
            ->where('receiver_user_id', $viewer->id)
            ->whereNull('read_at')
            ->whereIn('sender_user_id', $friendIds->all())
            ->groupBy('sender_user_id')
            ->pluck('unread_count', 'sender_user_id');

        return $friends
            ->unique('id')
            ->take($limit)
            ->map(fn (User $friend) => $this->formatCommunityUser(
                $viewer,
                $friend,
                (int) ($unreadCounts[(int) $friend->id] ?? 0)
            ))
            ->values();
    }

    public function pendingRequests(User $viewer): array
    {
        $received = AppFriendRequest::query()
            ->with('sender:id,username,firstname,lastname,image,show_in_listings')
            ->where('receiver_user_id', $viewer->id)
            ->where('status', 'pending')
            ->latest('id')
            ->get()
            ->map(fn (AppFriendRequest $request) => [
                'id' => (int) $request->id,
                'direction' => 'received',
                'created_at' => optional($request->created_at)->toIso8601String(),
                'user' => $request->sender ? $this->formatCommunityUser($viewer, $request->sender) : null,
            ])
            ->filter(fn (array $item) => !is_null($item['user']))
            ->values()
            ->all();

        $sent = AppFriendRequest::query()
            ->with('receiver:id,username,firstname,lastname,image,show_in_listings')
            ->where('sender_user_id', $viewer->id)
            ->where('status', 'pending')
            ->latest('id')
            ->get()
            ->map(fn (AppFriendRequest $request) => [
                'id' => (int) $request->id,
                'direction' => 'sent',
                'created_at' => optional($request->created_at)->toIso8601String(),
                'user' => $request->receiver ? $this->formatCommunityUser($viewer, $request->receiver) : null,
            ])
            ->filter(fn (array $item) => !is_null($item['user']))
            ->values()
            ->all();

        return [
            'received' => $received,
            'sent' => $sent,
        ];
    }

    public function friendsCount(User $viewer): int
    {
        return (int) AppFriendRequest::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($viewer) {
                $query->where('sender_user_id', $viewer->id)
                    ->orWhere('receiver_user_id', $viewer->id);
            })
            ->count();
    }

    public function searchUsersByUsername(User $viewer, string $username, int $limit = 12): Collection
    {
        $term = trim($username);
        if ($term === '') {
            return collect();
        }

        $blockedIds = $this->blockedUserIds($viewer);

        return User::query()
            ->select(['id', 'username', 'firstname', 'lastname', 'image', 'show_in_listings'])
            ->where('id', '!=', $viewer->id)
            ->when($blockedIds !== [], function ($query) use ($blockedIds) {
                $query->whereNotIn('id', $blockedIds);
            })
            ->where(function ($query) use ($term) {
                $query->where('username', 'like', '%' . $term . '%')
                    ->orWhere(DB::raw("CONCAT(COALESCE(firstname,''), ' ', COALESCE(lastname,''))"), 'like', '%' . $term . '%');
            })
            ->orderByRaw('CASE WHEN username = ? THEN 0 WHEN username LIKE ? THEN 1 ELSE 2 END', [
                $term,
                $term . '%',
            ])
            ->orderBy('username')
            ->limit($limit)
            ->get()
            ->map(fn (User $candidate) => $this->formatCommunityUser($viewer, $candidate))
            ->values();
    }

    public function relationship(User $viewer, ?User $other): array
    {
        if (!$other) {
            return $this->emptyRelationship();
        }

        if ((int) $viewer->id === (int) $other->id) {
            return [
                'is_self' => true,
                'is_friend' => false,
                'pending_sent' => false,
                'pending_received' => false,
                'blocked' => false,
                'can_message' => false,
            ];
        }

        if ($this->areUsersBlocked($viewer, $other)) {
            return [
                'is_self' => false,
                'is_friend' => false,
                'pending_sent' => false,
                'pending_received' => false,
                'blocked' => true,
                'can_message' => false,
            ];
        }

        $accepted = AppFriendRequest::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($viewer, $other) {
                $query->where(function ($pair) use ($viewer, $other) {
                    $pair->where('sender_user_id', $viewer->id)
                        ->where('receiver_user_id', $other->id);
                })->orWhere(function ($pair) use ($viewer, $other) {
                    $pair->where('sender_user_id', $other->id)
                        ->where('receiver_user_id', $viewer->id);
                });
            })
            ->exists();

        if ($accepted) {
            return [
                'is_self' => false,
                'is_friend' => true,
                'pending_sent' => false,
                'pending_received' => false,
                'blocked' => false,
                'can_message' => true,
            ];
        }

        $pendingSent = AppFriendRequest::query()
            ->where('sender_user_id', $viewer->id)
            ->where('receiver_user_id', $other->id)
            ->where('status', 'pending')
            ->exists();

        $pendingReceived = AppFriendRequest::query()
            ->where('sender_user_id', $other->id)
            ->where('receiver_user_id', $viewer->id)
            ->where('status', 'pending')
            ->exists();

        return [
            'is_self' => false,
            'is_friend' => false,
            'pending_sent' => $pendingSent,
            'pending_received' => $pendingReceived,
            'blocked' => false,
            'can_message' => true,
        ];
    }

    public function sendFriendRequest(User $sender, User $receiver): AppFriendRequest
    {
        if ((int) $sender->id === (int) $receiver->id) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode adicionar a si mesmo.',
            ]);
        }

        if ($this->areUsersBlocked($sender, $receiver)) {
            throw ValidationException::withMessages([
                'user' => 'Não é possível enviar solicitação para este usuário.',
            ]);
        }

        $existingAccepted = AppFriendRequest::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($sender, $receiver) {
                $query->where(function ($pair) use ($sender, $receiver) {
                    $pair->where('sender_user_id', $sender->id)
                        ->where('receiver_user_id', $receiver->id);
                })->orWhere(function ($pair) use ($sender, $receiver) {
                    $pair->where('sender_user_id', $receiver->id)
                        ->where('receiver_user_id', $sender->id);
                });
            })
            ->first();

        if ($existingAccepted) {
            throw ValidationException::withMessages([
                'user' => 'Vocês já são amigos na comunidade.',
            ]);
        }

        $reversePending = AppFriendRequest::query()
            ->where('sender_user_id', $receiver->id)
            ->where('receiver_user_id', $sender->id)
            ->where('status', 'pending')
            ->first();

        if ($reversePending) {
            return $this->acceptFriendRequest($sender, $reversePending);
        }

        $request = AppFriendRequest::query()->updateOrCreate(
            [
                'sender_user_id' => $sender->id,
                'receiver_user_id' => $receiver->id,
            ],
            [
                'status' => 'pending',
                'responded_at' => null,
            ]
        );

        $this->notifyUser(
            (int) $receiver->id,
            [
                'title' => 'Novo pedido de amizade',
                'body' => $this->displayName($sender) . ' quer te adicionar na comunidade.',
                'app_click_action' => 'COMMUNITY',
                'source' => 'friend_request',
            ]
        );

        return $request;
    }

    public function acceptFriendRequest(User $user, AppFriendRequest $request): AppFriendRequest
    {
        if ((int) $request->receiver_user_id !== (int) $user->id || $request->status !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Solicitação inválida para aceite.',
            ]);
        }

        $request->status = 'accepted';
        $request->responded_at = now();
        $request->save();

        $this->progressService->syncForUser($user->fresh());

        $sender = User::query()->find($request->sender_user_id);
        if ($sender) {
            $this->progressService->syncForUser($sender);
        }

        $this->notifyUser(
            (int) $request->sender_user_id,
            [
                'title' => 'Amizade aceita',
                'body' => $this->displayName($user) . ' aceitou seu pedido de amizade.',
                'app_click_action' => 'COMMUNITY',
                'source' => 'friend_accept',
            ]
        );

        return $request;
    }

    public function rejectFriendRequest(User $user, AppFriendRequest $request): AppFriendRequest
    {
        if ((int) $request->receiver_user_id !== (int) $user->id || $request->status !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Solicitação inválida para recusa.',
            ]);
        }

        $request->status = 'rejected';
        $request->responded_at = now();
        $request->save();

        return $request;
    }

    public function blockUser(User $user, User $target): void
    {
        if ((int) $user->id === (int) $target->id) {
            throw ValidationException::withMessages([
                'user' => 'Você não pode bloquear a si mesmo.',
            ]);
        }

        AppUserBlock::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'blocked_user_id' => $target->id,
            ],
            []
        );

        AppFriendRequest::query()
            ->where(function ($query) use ($user, $target) {
                $query->where(function ($pair) use ($user, $target) {
                    $pair->where('sender_user_id', $user->id)
                        ->where('receiver_user_id', $target->id);
                })->orWhere(function ($pair) use ($user, $target) {
                    $pair->where('sender_user_id', $target->id)
                        ->where('receiver_user_id', $user->id);
                });
            })
            ->delete();
    }

    public function thread(User $user, User $other, int $limit = 80): array
    {
        if ((int) $user->id === (int) $other->id) {
            throw ValidationException::withMessages([
                'user' => 'Conversa inválida.',
            ]);
        }

        if ($this->areUsersBlocked($user, $other)) {
            throw ValidationException::withMessages([
                'user' => 'Conversa indisponível para este usuário.',
            ]);
        }

        $messages = AppDirectMessage::query()
            ->where(function ($query) use ($user, $other) {
                $query->where('sender_user_id', $user->id)
                    ->where('receiver_user_id', $other->id);
            })
            ->orWhere(function ($query) use ($user, $other) {
                $query->where('sender_user_id', $other->id)
                    ->where('receiver_user_id', $user->id);
            })
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        AppDirectMessage::query()
            ->where('sender_user_id', $other->id)
            ->where('receiver_user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return [
            'user' => $this->formatCommunityUser($user, $other),
            'messages' => $messages->map(fn (AppDirectMessage $message) => [
                'id' => (int) $message->id,
                'body' => (string) $message->body,
                'created_at' => optional($message->created_at)->toIso8601String(),
                'read_at' => optional($message->read_at)->toIso8601String(),
                'is_mine' => (int) $message->sender_user_id === (int) $user->id,
                'sender_user_id' => (int) $message->sender_user_id,
                'receiver_user_id' => (int) $message->receiver_user_id,
            ])->all(),
        ];
    }

    public function sendDirectMessage(User $sender, User $receiver, string $body): AppDirectMessage
    {
        $text = trim($body);
        if ($text === '') {
            throw ValidationException::withMessages([
                'text' => 'Digite uma mensagem para enviar.',
            ]);
        }

        if ((int) $sender->id === (int) $receiver->id) {
            throw ValidationException::withMessages([
                'user' => 'Conversa inválida.',
            ]);
        }

        if ($this->areUsersBlocked($sender, $receiver)) {
            throw ValidationException::withMessages([
                'user' => 'Não foi possível enviar mensagem para este usuário.',
            ]);
        }

        $message = AppDirectMessage::create([
            'sender_user_id' => $sender->id,
            'receiver_user_id' => $receiver->id,
            'body' => $text,
            'metadata' => [
                'sender_name' => $this->displayName($sender),
            ],
        ]);

        $this->notifyUser(
            (int) $receiver->id,
            [
                'title' => 'Nova mensagem da comunidade',
                'body' => $this->displayName($sender) . ': ' . mb_substr($text, 0, 80),
                'app_click_action' => 'COMMUNITY',
                'source' => 'direct_message',
            ]
        );

        return $message;
    }

    public function formatCommunityUser(User $viewer, User $other, int $unreadCount = 0): array
    {
        return [
            'id' => (int) $other->id,
            'username' => (string) ($other->username ?? 'usuario'),
            'display_name' => $this->displayName($other),
            'avatar_url' => $this->avatarUrl($other),
            'show_in_listings' => (bool) ($other->show_in_listings ?? true),
            'unread_count' => $unreadCount,
            'relationship' => $this->relationship($viewer, $other),
        ];
    }

    public function unreadDirectMessagesCount(User $user): int
    {
        return (int) AppDirectMessage::query()
            ->where('receiver_user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function blockedUserIds(User $user): array
    {
        return AppUserBlock::query()
            ->where('user_id', $user->id)
            ->pluck('blocked_user_id')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();
    }

    private function areUsersBlocked(User $first, User $second): bool
    {
        return AppUserBlock::query()
            ->where(function ($query) use ($first, $second) {
                $query->where('user_id', $first->id)
                    ->where('blocked_user_id', $second->id);
            })
            ->orWhere(function ($query) use ($first, $second) {
                $query->where('user_id', $second->id)
                    ->where('blocked_user_id', $first->id);
            })
            ->exists();
    }

    private function notifyUser(int $userId, array $payload): void
    {
        try {
            if ($this->pushService->isConfigured()) {
                $this->pushService->sendToUser($userId, $payload);
            }
        } catch (\Throwable $exception) {
            Log::warning('Falha ao enviar push social do app', [
                'user_id' => $userId,
                'error' => $exception->getMessage(),
            ]);
        }
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

    private function avatarUrl(User $user): ?string
    {
        $image = trim((string) ($user->image ?? ''));
        if ($image === '') {
            return null;
        }

        return asset('assets/images/user/profile/' . ltrim($image, '/'));
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
