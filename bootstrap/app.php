<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AdminPermissionMiddleware;
use App\Http\Middleware\CheckStatus;
use App\Http\Middleware\Demo;
use App\Http\Middleware\KycMiddleware;
use App\Http\Middleware\LicenseCheck;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RegistrationStep;
use App\Http\Middleware\SkipNgrokWarning;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

$requiredStorageDirectories = [
    dirname(__DIR__) . '/storage/framework',
    dirname(__DIR__) . '/storage/framework/cache',
    dirname(__DIR__) . '/storage/framework/cache/data',
    dirname(__DIR__) . '/storage/framework/sessions',
    dirname(__DIR__) . '/storage/framework/views',
    dirname(__DIR__) . '/storage/logs',
];

foreach ($requiredStorageDirectories as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
}

if (PHP_OS_FAMILY === 'Windows' && PHP_SAPI === 'cli') {
    foreach (getenv() as $key => $value) {
        $upperKey = strtoupper($key);

        if (! array_key_exists($upperKey, $_ENV)) {
            $_ENV[$upperKey] = $value;
        }

        if (! array_key_exists($upperKey, $_SERVER)) {
            $_SERVER[$upperKey] = $value;
        }
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::namespace('App\Http\Controllers')->group(function () {
                Route::middleware(['web'])
                    ->namespace('Admin')
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));

                // Public API (read-mostly). Keep /api prefix for JSON exception handling.
                Route::middleware(['api'])
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));

                Route::middleware(['web'])
                    ->namespace('Gateway')
                    ->prefix('ipn')
                    ->name('ipn.')
                    ->group(base_path('routes/ipn.php'));

                Route::middleware(['web'])->prefix('user')->group(base_path('routes/user.php'));
                Route::middleware(['web'])->group(base_path('routes/web.php'));

            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('web', [
            \App\Http\Middleware\SkipNgrokWarning::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LanguageMiddleware::class,
            \App\Http\Middleware\ActiveTemplateMiddleware::class,
            \App\Http\Middleware\EnsureSingleSession::class, // Single Session Control
        ]);

        $middleware->group('api', [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth.basic'            => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers'         => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can'                   => \Illuminate\Auth\Middleware\Authorize::class,
            'auth'                  => Authenticate::class,
            'guest'                 => RedirectIfAuthenticated::class,
            'password.confirm'      => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed'                => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle'              => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified'              => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            'admin'                 => RedirectIfNotAdmin::class,
            'admin.guest'           => RedirectIfAdmin::class,
            'admin.permissions'     => AdminPermissionMiddleware::class,

            'check.status'          => CheckStatus::class,
            'demo'                  => Demo::class,
            'kyc'                   => KycMiddleware::class,
            'registration.complete' => RegistrationStep::class,
            'license'               => LicenseCheck::class,
        ]);

        $middleware->validateCsrfTokens(
            except: [
                'user/deposit',
                'ipn*',
                'api/webhooks/*',
            ]
        );

        // Trust ngrok and other proxies
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function () {
            if (request()->is('api/*')) {
                return true;
            }
        });
        
        // Trata erro 419 (CSRF Token Mismatch) - redireciona de volta ao login
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Token expirado. Por favor, atualize a página.',
                    'error' => 'csrf_token_mismatch'
                ], 419);
            }
            
            // Se for requisição admin, redireciona para login admin
            if ($request->is('admin/*') || $request->is('admin')) {
                return redirect()->route('admin.login')
                    ->with('error', 'Sessão expirada. Por favor, faça login novamente.');
            }
            
            // Se for requisição user, redireciona para página inicial
            return redirect()->route('home')
                ->with('error', 'Sessão expirada. Por favor, tente novamente.');
        });
        
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 401) {
                if (request()->is('api/*')) {
                    $notify[] = 'Unauthorized request';
                    return response()->json([
                        'remark'  => 'unauthenticated',
                        'status'  => 'error',
                        'message' => ['error' => $notify],
                    ]);
                }
            }

            return $response;
        });
    })->create();
