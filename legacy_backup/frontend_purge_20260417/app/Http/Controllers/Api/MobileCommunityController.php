<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppCommunityPost;
use App\Models\AppFriendRequest;
use App\Models\User;
use App\Models\X1RoomInstance;
use App\Services\AppCommunityFeedService;
use App\Services\AppSocialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCommunityController extends Controller
{
    public function __construct(
        private readonly AppCommunityFeedService $feedService,
        private readonly AppSocialService $socialService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $blockedIds = $this->socialService->blockedUserIds($user);
        $friendIds = $this->socialService->friendIds($user);
        $officialPosts = $this->feedService->officialTimeline();

        $posts = AppCommunityPost::query()
            ->with('user:id,username,firstname,lastname,image,show_in_listings')
            ->when($blockedIds !== [], function ($query) use ($blockedIds) {
                $query->where(function ($inner) use ($blockedIds) {
                    $inner->whereNull('user_id')
                        ->orWhereNotIn('user_id', $blockedIds);
                });
            })
            ->where('type', '!=', 'message')
            ->where(function ($query) use ($user, $friendIds) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $user->id);

                if ($friendIds !== []) {
                    $query->orWhereIn('user_id', $friendIds);
                }
            })
            ->latest('id')
            ->limit(80)
            ->get()
            ->map(fn (AppCommunityPost $post) => $this->formatPost($user, $post, $blockedIds))
            ->filter()
            ->values();

        $posts = $officialPosts
            ->concat($posts)
            ->sortByDesc(fn (array $post) => (string) ($post['created_at'] ?? ''))
            ->take(50)
            ->values();

        if ($posts->isEmpty()) {
            $posts = collect([
                [
                    'id' => 0,
                    'type' => 'feed',
                    'subtype' => 'welcome',
                    'emoji' => '🤠',
                    'title' => 'Feed de atividades pronto',
                    'body' => 'As movimentações dos seus amigos e da Rei do Rodeio aparecem aqui.',
                    'created_at' => now()->toIso8601String(),
                    'actor_user_id' => null,
                    'actor_username' => 'reidorodeio',
                    'actor_name' => 'Rei do Rodeio',
                    'actor_avatar_url' => null,
                    'relationship' => $this->emptyRelationship(),
                    'metadata' => [],
                ],
            ]);
        }

        $pending = $this->socialService->pendingRequests($user);
        $friends = $this->socialService->friends($user, 18);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'open_x1_rooms' => X1RoomInstance::query()
                        ->whereIn('status', ['open', 'in_progress'])
                        ->count(),
                    'messages_count' => (int) $posts->count(),
                    'activities_count' => (int) $posts->count(),
                    'friends_count' => $this->socialService->friendsCount($user),
                    'pending_requests_count' => count($pending['received']),
                    'unread_direct_messages' => $this->socialService->unreadDirectMessagesCount($user),
                ],
                'friends' => $friends->all(),
                'friend_requests' => $pending,
                'timeline' => $posts->all(),
            ],
        ]);
    }

    public function storeMessage(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'O feed da comunidade agora exibe apenas atividades. Use mensagens diretas para conversar com amigos.',
        ], 422);
    }

    public function sendFriendRequest(Request $request, User $userTarget): JsonResponse
    {
        $friendRequest = $this->socialService->sendFriendRequest($request->user(), $userTarget);

        return response()->json([
            'success' => true,
            'message' => $friendRequest->status === 'accepted'
                ? 'Solicitação aceita automaticamente.'
                : 'Solicitação de amizade enviada.',
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:2', 'max:40'],
        ], [
            'username.required' => 'Informe um username para buscar.',
            'username.min' => 'Digite pelo menos 2 caracteres para buscar.',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->socialService
                ->searchUsersByUsername($user, (string) $validated['username'])
                ->all(),
        ]);
    }

    public function acceptFriendRequest(Request $request, AppFriendRequest $friendRequest): JsonResponse
    {
        $this->socialService->acceptFriendRequest($request->user(), $friendRequest);

        return response()->json([
            'success' => true,
            'message' => 'Pedido de amizade aceito.',
        ]);
    }

    public function rejectFriendRequest(Request $request, AppFriendRequest $friendRequest): JsonResponse
    {
        $this->socialService->rejectFriendRequest($request->user(), $friendRequest);

        return response()->json([
            'success' => true,
            'message' => 'Pedido de amizade recusado.',
        ]);
    }

    public function blockUser(Request $request, User $userTarget): JsonResponse
    {
        $this->socialService->blockUser($request->user(), $userTarget);

        return response()->json([
            'success' => true,
            'message' => 'Usuário bloqueado com sucesso.',
        ]);
    }

    public function directThread(Request $request, User $userTarget): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->socialService->thread($request->user(), $userTarget),
        ]);
    }

    public function sendDirectMessage(Request $request, User $userTarget): JsonResponse
    {
        $data = $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $message = $this->socialService->sendDirectMessage(
            $request->user(),
            $userTarget,
            (string) $data['text']
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (int) $message->id,
            ],
        ], 201);
    }

    private function formatPost(User $viewer, AppCommunityPost $post, array $blockedIds): ?array
    {
        $metadata = is_array($post->metadata) ? $post->metadata : [];
        $user = $post->user;
        $actorUserId = (int) ($metadata['actor_user_id'] ?? $user?->id ?? 0);

        if ($actorUserId > 0 && in_array($actorUserId, $blockedIds, true)) {
            return null;
        }

        return [
            'id' => (int) $post->id,
            'type' => (string) $post->type,
            'subtype' => (string) ($post->subtype ?? ''),
            'emoji' => $post->emoji,
            'title' => $post->title,
            'body' => $post->body,
            'created_at' => optional($post->created_at)->toIso8601String(),
            'actor_user_id' => $actorUserId > 0 ? $actorUserId : null,
            'actor_username' => $metadata['actor_username']
                ?? ($user ? (string) ($user->username ?? 'usuario') : null),
            'actor_name' => $metadata['actor_name']
                ?? ($user ? $this->displayName($user) : 'Rei do Rodeio'),
            'actor_avatar_url' => $metadata['actor_avatar_url']
                ?? ($user ? $this->userAvatarUrl($user) : null),
            'relationship' => $user
                ? $this->socialService->relationship($viewer, $user)
                : $this->emptyRelationship(),
            'metadata' => $metadata,
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
