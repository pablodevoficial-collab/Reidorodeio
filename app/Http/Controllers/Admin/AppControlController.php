<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\PushSubscription;
use App\Models\Subscription;
use App\Models\User;
use App\Rules\FileTypeValidate;
use App\Services\NativePushService;
use App\Services\PushNotificationService;
use Throwable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppControlController extends Controller
{
    public function dashboard(): View
    {
        $pageTitle = 'App Control';

        $stats = [
            'total_users' => User::query()->real()->count(),
            'premium_users' => User::query()->real()->whereHas('subscriptions', fn ($query) => $query->active())->count(),
            'trial_users' => User::query()->real()->whereHas('subscriptions', function ($query) {
                $query->where('is_trial', true)->where('trial_ends_at', '>=', now());
            })->count(),
            'push_active' => PushSubscription::query()->active()->count(),
            'push_users' => PushSubscription::query()->active()->distinct('user_id')->count('user_id'),
            'recent_signups' => User::query()->real()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $latestUsers = User::query()
            ->real()
            ->withCount([
                'pushSubscriptions as active_push_subscriptions_count' => function ($query) {
                    $query->active();
                },
            ])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $snapshot = [
            'mobile_verified' => User::query()->real()->mobileVerified()->count(),
            'mobile_unverified' => User::query()->real()->mobileUnverified()->count(),
            'subscriptions_active' => Subscription::query()->active()->count(),
            'trials_expiring_soon' => Subscription::query()->trialsExpiringSoon(7)->count(),
        ];

        return view('admin.app_control.dashboard', compact('pageTitle', 'stats', 'latestUsers', 'snapshot'));
    }

    public function users(Request $request): View
    {
        $pageTitle = 'Usuários do App';
        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', '');
        $premium = (string) $request->input('premium', '');

        $users = User::query()
            ->real()
            ->withCount([
                'pushSubscriptions as active_push_subscriptions_count' => function ($query) {
                    $query->active();
                },
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere(DB::raw("CONCAT(COALESCE(firstname,''), ' ', COALESCE(lastname,''))"), 'like', "%{$search}%");
                });
            })
            ->when($status === 'verified_mobile', fn ($query) => $query->mobileVerified())
            ->when($status === 'unverified_mobile', fn ($query) => $query->mobileUnverified())
            ->when($status === 'active', fn ($query) => $query->where('status', Status::USER_ACTIVE))
            ->when($status === 'banned', fn ($query) => $query->where('status', Status::USER_BAN))
            ->when($premium === 'yes', fn ($query) => $query->whereHas('subscriptions', fn ($subscriptionQuery) => $subscriptionQuery->active()))
            ->when($premium === 'no', fn ($query) => $query->whereDoesntHave('subscriptions', fn ($subscriptionQuery) => $subscriptionQuery->active()))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.app_control.users.index', compact('pageTitle', 'users', 'search', 'status', 'premium'));
    }

    public function editUser(User $user): View
    {
        $pageTitle = 'Editar Usuário do App';
        $user->load([
            'subscriptions' => function ($query) {
                $query->latest();
            },
            'pushSubscriptions' => function ($query) {
                $query->latest();
            },
        ]);

        return view('admin.app_control.users.edit', compact('pageTitle', 'user'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:40'],
            'lastname' => ['required', 'string', 'max:40'],
            'username' => ['required', 'string', 'min:3', 'max:40', 'unique:users,username,' . $user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'mobile' => ['nullable', 'string', 'max:40'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'birthdate' => ['nullable', 'date'],
            'status' => ['required', 'in:0,1'],
            'show_in_listings' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'cpf' => $validated['cpf'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'status' => (int) $validated['status'],
            'ev' => $request->boolean('ev') ? Status::VERIFIED : Status::UNVERIFIED,
            'sv' => $request->boolean('sv') ? Status::VERIFIED : Status::UNVERIFIED,
            'show_in_listings' => $request->boolean('show_in_listings'),
        ]);
        $user->save();

        $notify[] = ['success', 'Usuário do app atualizado com sucesso.'];
        return redirect()->route('admin.app_control.users.edit', $user)->withNotify($notify);
    }

    public function pushCenter(): View
    {
        $pageTitle = 'Central de Push do App';

        $stats = [
            'subscriptions_active' => PushSubscription::query()->active()->count(),
            'users_reachable' => PushSubscription::query()->active()->distinct('user_id')->count('user_id'),
            'premium_reachable' => PushSubscription::query()->active()
                ->whereHas('user.subscriptions', fn ($query) => $query->active())
                ->distinct('user_id')
                ->count('user_id'),
            'trial_reachable' => PushSubscription::query()->active()
                ->whereHas('user.subscriptions', function ($query) {
                    $query->where('is_trial', true)->where('trial_ends_at', '>=', now());
                })
                ->distinct('user_id')
                ->count('user_id'),
            'native_tokens' => DeviceToken::query()->where('is_app', true)->count(),
            'native_users' => DeviceToken::query()->where('is_app', true)->distinct('user_id')->count('user_id'),
        ];

        $recentSubscriptions = PushSubscription::query()
            ->with('user:id,username,email')
            ->latest()
            ->limit(12)
            ->get();

        $nativePushService = app(NativePushService::class);
        $firebaseConfig = gs('firebase_config');
        $firebaseClientConfig = [
            'apiKey' => $firebaseConfig->apiKey ?? '',
            'authDomain' => $firebaseConfig->authDomain ?? '',
            'projectId' => $firebaseConfig->projectId ?? '',
            'storageBucket' => $firebaseConfig->storageBucket ?? '',
            'messagingSenderId' => $firebaseConfig->messagingSenderId ?? '',
            'appId' => $firebaseConfig->appId ?? '',
            'measurementId' => $firebaseConfig->measurementId ?? '',
        ];
        $nativePushIssues = $nativePushService->configurationIssues();
        $firebaseClientReady = $nativePushService->hasPublicClientConfig();
        $serviceAccountReady = file_exists(getFilePath('pushConfig') . '/push_config.json');

        return view('admin.app_control.push.index', compact(
            'pageTitle',
            'stats',
            'recentSubscriptions',
            'firebaseClientConfig',
            'nativePushIssues',
            'firebaseClientReady',
            'serviceAccountReady'
        ));
    }

    public function sendPush(
        Request $request,
        PushNotificationService $pushNotificationService,
        NativePushService $nativePushService
    ): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:500'],
            'target' => ['required', 'in:all,premium,trial,verified_mobile,user'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action_url' => ['nullable', 'string', 'max:500'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        $payload = array_filter([
            'title' => $validated['title'],
            'body' => $validated['message'],
            'url' => $validated['action_url'] ?? null,
            'image' => $validated['image_url'] ?? null,
            'source' => 'admin_app_control',
        ], fn ($value) => !is_null($value) && $value !== '');

        try {
            $webResults = ['success' => 0, 'failed' => 0, 'expired' => 0];
            $nativeResults = ['success' => 0, 'failed' => 0, 'expired' => 0];
            $nativeError = null;

            if ($validated['target'] === 'user') {
                $userId = (int) $validated['user_id'];
                $webResults = $pushNotificationService->sendToUser($userId, $payload);
                try {
                    $nativeResults = $nativePushService->sendToUser($userId, $payload);
                } catch (Throwable $exception) {
                    $nativeError = $exception->getMessage();
                }
            } else {
                $userQuery = User::query()
                    ->real()
                    ->select('id')
                    ->when($validated['target'] === 'premium', fn ($query) => $query->whereHas('subscriptions', fn ($subscriptionQuery) => $subscriptionQuery->active()))
                    ->when($validated['target'] === 'trial', function ($query) {
                        $query->whereHas('subscriptions', function ($subscriptionQuery) {
                            $subscriptionQuery->where('is_trial', true)->where('trial_ends_at', '>=', now());
                        });
                    })
                    ->when($validated['target'] === 'verified_mobile', fn ($query) => $query->mobileVerified());

                $subscriptions = PushSubscription::query()
                    ->active()
                    ->when($validated['target'] === 'premium', fn ($query) => $query->whereHas('user.subscriptions', fn ($subscriptionQuery) => $subscriptionQuery->active()))
                    ->when($validated['target'] === 'trial', function ($query) {
                        $query->whereHas('user.subscriptions', function ($subscriptionQuery) {
                            $subscriptionQuery->where('is_trial', true)->where('trial_ends_at', '>=', now());
                        });
                    })
                    ->when($validated['target'] === 'verified_mobile', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->mobileVerified()))
                    ->get();

                $webResults = $pushNotificationService->sendToSubscriptions($subscriptions, $payload);
                try {
                    $nativeResults = $validated['target'] === 'all'
                        ? $nativePushService->sendToAll($payload)
                        : $nativePushService->sendToUserIds($userQuery->pluck('id'), $payload);
                } catch (Throwable $exception) {
                    $nativeError = $exception->getMessage();
                }
            }
        } catch (Throwable $exception) {
            $notify[] = ['error', $exception->getMessage()];
            return redirect()->route('admin.app_control.push.index')->withNotify($notify);
        }

        $notify[] = ['success', sprintf(
            'Push enviado. Web: %d sucesso / %d falhas / %d expiradas | App: %d sucesso / %d falhas / %d expiradas',
            $webResults['success'],
            $webResults['failed'],
            $webResults['expired'],
            $nativeResults['success'],
            $nativeResults['failed'],
            $nativeResults['expired'],
        )];
        if ($nativeError) {
            $notify[] = ['warning', 'Push nativo nao enviado: ' . $nativeError];
        }
        return redirect()->route('admin.app_control.push.index')->withNotify($notify);
    }

    public function updateFirebaseConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apiKey' => ['required', 'string', 'max:255'],
            'authDomain' => ['nullable', 'string', 'max:255'],
            'projectId' => ['required', 'string', 'max:255'],
            'storageBucket' => ['nullable', 'string', 'max:255'],
            'messagingSenderId' => ['required', 'string', 'max:255'],
            'appId' => ['required', 'string', 'max:255'],
            'measurementId' => ['nullable', 'string', 'max:255'],
        ]);

        $general = gs();
        $general->firebase_config = $validated;
        $general->save();

        $notify[] = ['success', 'Configuração pública do Firebase salva com sucesso.'];
        return redirect()->route('admin.app_control.push.index')->withNotify($notify);
    }

    public function uploadFirebaseServiceAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'service_account_file' => ['required', new FileTypeValidate(['json'])],
        ]);

        try {
            fileUploader(
                $request->file('service_account_file'),
                getFilePath('pushConfig'),
                filename: 'push_config.json'
            );
        } catch (Throwable $exception) {
            $notify[] = ['error', 'Nao foi possivel enviar o arquivo service account do Firebase.'];
            return redirect()->route('admin.app_control.push.index')->withNotify($notify);
        }

        $notify[] = ['success', 'Arquivo service account do Firebase enviado com sucesso.'];
        return redirect()->route('admin.app_control.push.index')->withNotify($notify);
    }
}
