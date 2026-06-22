<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\FacebookDataDeletionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin')->name('home');

Route::redirect('/bolao', '/admin')->name('bolao');
Route::redirect('/x1', '/admin')->name('x1');
Route::redirect('/estatisticas', '/admin')->name('stats');
Route::redirect('/inicio', '/admin');

Route::get('/favicon.ico', function () {
    $path = public_path('assets/images/logo_icon/favicon.png');
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    return response('', 204);
});

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

Route::redirect('/contact', '/admin');
Route::post('/contact', function () {
    return redirect('/admin');
});
Route::redirect('/news', '/admin');
Route::redirect('/cookie-policy', '/admin');
Route::redirect('/cookie/accept', '/admin');
Route::redirect('/sobrenos', '/admin')->name('about');
Route::redirect('/termos', '/admin')->name('terms');
Route::redirect('/termos-uso', '/admin')->name('terms.usage');
Route::redirect('/privacidade', '/admin')->name('privacy');
Route::redirect('/regras-fantasy', '/admin')->name('rules.fantasy');
Route::get('/news/{slug}', fn () => redirect('/admin'))->name('blog.details');
Route::get('/policy/{slug}', fn () => redirect('/admin'))->name('policy.pages');

Route::prefix('facebook')->name('facebook.data_deletion.')->controller(FacebookDataDeletionController::class)->group(function () {
    Route::get('data-deletion', 'instructions')->name('instructions');
    Route::post('data-deletion', 'callback')->name('callback');
    Route::get('data-deletion/status/{code}', 'status')->name('status');
});

Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('dashboard-live', function () {
        return view('admin.dashboard-live', [
            'pageTitle' => 'Dashboard Live',
        ]);
    })->name('dashboard-live');
});
