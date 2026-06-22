<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\FacebookDataDeletionController;
use App\Http\Controllers\FantasyLeagueOpeningReminderController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RodeioEmailReminderController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.frontend', [
    'pageTitle' => 'Bolão Rei do Rodeio',
])->name('home');

Route::redirect('/bolao', '/?arena=bolao')->name('bolao');
Route::redirect('/x1', '/')->name('x1');
Route::redirect('/estatisticas', '/')->name('stats');
Route::redirect('/inicio', '/');

Route::get('/favicon.ico', function () {
    $path = public_path('assets/images/logo_icon/favicon.png');
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    return response('', 204);
});

Route::get('/rodeios/{rodeio}/logo', [SiteController::class, 'rodeioLogo'])->name('rodeios.logo');
Route::get('/arena-estatisticas/data', [SiteController::class, 'arenaStatisticsData'])->name('arena.stats.data');
Route::post('/rodeios/{rodeio}/email-reminder', [RodeioEmailReminderController::class, 'store'])->name('rodeios.email-reminder');
Route::post('/fantasy/leagues/slots/{slot}/email-reminder', [FantasyLeagueOpeningReminderController::class, 'store'])->name('fantasy.leagues.slots.email-reminder');

Route::get('/csrf-refresh', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

Route::get('/session-heartbeat', function () {
    if (!auth()->check()) {
        return response()->json(['status' => 'guest']);
    }

    $user = auth()->user();
    $dbSession = \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $user->id)
        ->value('current_session_id');

    if ($dbSession && $dbSession !== session()->getId()) {
        return response()->json(['status' => 'invalid'], 401);
    }

    return response()->json(['status' => 'valid']);
})->name('session.heartbeat');

Route::controller(SiteController::class)->group(function () {
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');
    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode', 'maintenance')->withoutMiddleware('maintenance')->name('maintenance');
});

Route::get('/r/{code}', [ReferralController::class, 'handleReferral'])->name('referral.link');

Route::redirect('/contact', '/');
Route::post('/contact', function () {
    return redirect()->route('home');
});
Route::redirect('/news', '/');
Route::redirect('/cookie-policy', '/');
Route::redirect('/cookie/accept', '/');
Route::redirect('/sobrenos', '/')->name('about');
Route::redirect('/termos', '/')->name('terms');
Route::redirect('/termos-uso', '/')->name('terms.usage');
Route::redirect('/privacidade', '/')->name('privacy');
Route::redirect('/regras-fantasy', '/')->name('rules.fantasy');
Route::get('/news/{slug}', fn () => redirect()->route('home'))->name('blog.details');
Route::get('/policy/{slug}', fn () => redirect()->route('home'))->name('policy.pages');

Route::prefix('facebook')->name('facebook.data_deletion.')->controller(FacebookDataDeletionController::class)->group(function () {
    Route::get('data-deletion', 'instructions')->name('instructions');
    Route::post('data-deletion', 'callback')->name('callback');
    Route::get('data-deletion/status/{code}', 'status')->name('status');
});

Route::middleware(['auth'])->prefix('web/fantasy')->name('web.fantasy.')->group(function () {
    Route::get('/my-teams', [\App\Http\Controllers\Api\FantasyLeagueApiController::class, 'myTeams'])->name('my-teams');
});

Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard-live', function () {
        return view('admin.dashboard-live', [
            'pageTitle' => 'Dashboard Live',
        ]);
    })->name('dashboard-live');
});
