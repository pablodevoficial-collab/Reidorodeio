<?php

use App\Http\Controllers\Api\FantasyLeagueApiController;
use App\Http\Controllers\Api\FantasyPaymentController;
use App\Http\Controllers\Api\RealtimeDataController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - bolao only
|--------------------------------------------------------------------------
*/

RateLimiter::for('api-general', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('api-heavy', function (Request $request) {
    return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('payment', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('realtime')->middleware('throttle:api-general')->group(function () {
    Route::get('/competitors/modalidade/{modalidadeId}', [RealtimeDataController::class, 'getCompetitorsByModalidade']);
    Route::get('/competitors/search', [RealtimeDataController::class, 'searchCompetitors']);
    Route::get('/rodeios', [RealtimeDataController::class, 'getRodeios']);
    Route::get('/modalidades', [RealtimeDataController::class, 'getModalidades']);
    Route::get('/rodeios/{rodeioId}/modalidades', [RealtimeDataController::class, 'getModalidadesByRodeio']);
    Route::get('/modalidades/{modalidadeId}/meta', [RealtimeDataController::class, 'getModalidadeMeta']);
});

Route::prefix('fantasy')->middleware('throttle:api-general')->group(function () {
    Route::get('/leagues', [FantasyLeagueApiController::class, 'index']);
    Route::get('/leagues/{leagueId}', [FantasyLeagueApiController::class, 'show']);
    Route::get('/leagues/{leagueId}/available-competitors', [FantasyLeagueApiController::class, 'availableCompetitors']);
    Route::get('/leagues/{leagueId}/ranking', [FantasyLeagueApiController::class, 'leagueRanking']);
    Route::get('/leagues/{leagueId}/live-stats', [FantasyLeagueApiController::class, 'liveStats']);

    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/user/profile', [\App\Http\Controllers\Api\UserProfileController::class, 'showProfile']);
        Route::post('/user/profile', [\App\Http\Controllers\Api\UserProfileController::class, 'updateProfile']);
        Route::post('/leagues/{leagueId}/teams/verify', [FantasyLeagueApiController::class, 'verifyTeam']);
        Route::post('/leagues/{leagueId}/teams', [FantasyLeagueApiController::class, 'createTeam'])->middleware('throttle:api-heavy');
        Route::get('/leagues/{leagueId}/teams/me', [FantasyLeagueApiController::class, 'myTeam']);
        Route::get('/my-teams', [FantasyLeagueApiController::class, 'myTeams']);
        Route::post('/leagues/{leagueId}/teams/pay', [FantasyPaymentController::class, 'initiatePayment'])->middleware('throttle:payment');
        Route::get('/payments/{preferenceId}/status', [FantasyPaymentController::class, 'checkStatus']);
        Route::post('/payments/{preferenceId}/cancel', [FantasyPaymentController::class, 'cancelPayment'])->middleware('throttle:payment');
    });
});
