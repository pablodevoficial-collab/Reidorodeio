<?php

namespace App\Providers;

use App\Constants\Status;
use App\Lib\Searchable;
use App\Models\AdminNotification;
use App\Models\CompetitorRegistrationRequest;
use App\Models\Frontend;
use App\Models\ProfilePhotoRequest;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Builder::mixin(new Searchable);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (
                !app()->runningInConsole()
                && app()->bound('debugbar')
                && (
                    request()->boolean('app')
                    || request()->is('mobile/webview/entry')
                    || request()->is('api/mobile/*')
                )
            ) {
                app('debugbar')->disable();
            }
        } catch (\Throwable $e) {
            // Ignore debugbar disabling failures in environments where the package is absent.
        }

        // Rate limiters (devem estar no provider, não no routes/api.php, para funcionar com route:cache)
        RateLimiter::for('api-general', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('api-heavy', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('x1-create', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('x1-join', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('payment', function (Request $request) {
            // Evita bloqueio agressivo em fluxos com múltiplas tentativas (PIX/Fantasy/X1).
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Registrar Observer para atualizar salas X1 quando user vira premium
        \App\Models\Subscription::observe(\App\Observers\SubscriptionObserver::class);
        
        // Registrar Observer para ajustar bots quando usuário real entra em liga Fantasy
        \App\Models\FantasyTeam::observe(\App\Observers\FantasyTeamObserver::class);

        // Skip cache/config checks during migrations
        if (app()->runningInConsole() && isset($_SERVER['argv']) && in_array('migrate', $_SERVER['argv'])) {
            // Skip database checks during migrations
        } else {
            try {
                if (Schema::hasTable('cache') && !cache()->get('SystemInstalled')) {
                    $envFilePath = base_path('.env');

                    if (!file_exists($envFilePath)) {
                        header('Location: install');
                        exit;
                    }

                    $envContents = file_get_contents($envFilePath);
                    if (empty($envContents)) {
                        header('Location: install');
                        exit;
                    }

                    cache()->put('SystemInstalled', true);
                }
            } catch (\Exception $e) {
                // Silently fail during boot if database is corrupted
            }
        }

        $viewShare['emptyMessage'] = 'Data not found';
        view()->share($viewShare);

        view()->composer('admin.partials.sidenav', function ($view) {
            try {
                $view->with([
                    'bannedUsersCount'           => User::banned()->count(),
                    'emailUnverifiedUsersCount'  => User::emailUnverified()->count(),
                    'mobileUnverifiedUsersCount' => User::mobileUnverified()->count(),
                    'kycUnverifiedUsersCount'    => User::kycUnverified()->count(),
                    'kycPendingUsersCount'       => User::kycPending()->count(),
                    'pendingTicketCount'         => Schema::hasTable('support_tickets')
                        ? SupportTicket::whereIN('status', [Status::TICKET_OPEN, Status::TICKET_REPLY])->count()
                        : 0,
                    'competitorRegistrationRequestsCount' => Schema::hasTable('competitor_registration_requests')
                        ? CompetitorRegistrationRequest::where('status', 'pending')->count()
                        : 0,
                    'profilePhotoApprovalPendingCount' => Schema::hasTable('profile_photo_requests')
                        ? ProfilePhotoRequest::where('status', 'pending')->count()
                        : 0,
                    'updateAvailable'            => version_compare(gs('available_version'), systemDetails()['version'], '>') ? 'v' . gs('available_version') : false,
                ]);
            } catch (\Throwable $e) {
                $view->with([
                    'bannedUsersCount' => 0,
                    'emailUnverifiedUsersCount' => 0,
                    'mobileUnverifiedUsersCount' => 0,
                    'kycUnverifiedUsersCount' => 0,
                    'kycPendingUsersCount' => 0,
                    'pendingTicketCount' => 0,
                    'competitorRegistrationRequestsCount' => 0,
                    'profilePhotoApprovalPendingCount' => 0,
                    'updateAvailable' => false,
                ]);
            }
        });

        view()->composer('admin.partials.topnav', function ($view) {
            try {
                $view->with([
                    'adminNotifications'     => Schema::hasTable('admin_notifications')
                        ? AdminNotification::where('is_read', Status::NO)->with('user')->orderBy('id', 'desc')->take(10)->get()
                        : collect(),
                    'adminNotificationCount' => Schema::hasTable('admin_notifications')
                        ? AdminNotification::where('is_read', Status::NO)->count()
                        : 0,
                ]);
            } catch (\Throwable $e) {
                $view->with([
                    'adminNotifications'     => collect(),
                    'adminNotificationCount' => 0,
                ]);
            }
        });

        view()->composer('partials.seo', function ($view) {
            $seo = Frontend::where('data_keys', 'seo.data')->first();
            $view->with([
                'seo' => $seo ? $seo->data_values : $seo,
            ]);
        });

        // Force HTTPS for ngrok and production
        if (gs('force_ssl') || request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || str_contains(request()->getHost(), 'ngrok')) {
            \URL::forceScheme('https');
        }

        // Shared hosting fix: keep base URL clean (without /public), but only
        // force root URL when request host matches APP_URL host.
        // This avoids cross-host login/session issues (localhost vs LAN IP, www vs non-www).
        try {
            $base = rtrim((string) config('app.url', ''), '/');
            if ($base && !app()->runningInConsole()) {
                $configuredHost = parse_url($base, PHP_URL_HOST);
                $requestHost    = request()->getHost();

                if ($configuredHost && $requestHost && strcasecmp($configuredHost, $requestHost) === 0) {
                    \URL::forceRootUrl($base);
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        Paginator::useBootstrapFive();

        // Configure Mailer Dynamically from DB
        try {
            $config = gs('mail_config');
            if ($config && $config->name == 'smtp') {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $config->host,
                    'mail.mailers.smtp.port' => $config->port,
                    'mail.mailers.smtp.encryption' => $config->enc,
                    'mail.mailers.smtp.username' => $config->username,
                    'mail.mailers.smtp.password' => $config->password,
                    'mail.from.address' => gs('email_from') ?: env('MAIL_FROM_ADDRESS'),
                    'mail.from.name' => gs('email_from_name') ?: gs('site_name') ?: env('MAIL_FROM_NAME'),
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore if DB not ready
        }
    }
}
