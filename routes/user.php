<?php

use App\Http\Controllers\User\ProfileController;
use Illuminate\Support\Facades\Route;

Route::namespace('User\Auth')->name('user.')->middleware('guest')->group(function () {
    Route::controller('LoginController')->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
        Route::get('logout', 'logout')->middleware('auth')->withoutMiddleware('guest')->name('logout');
    });

    Route::controller('SocialLoginController')->prefix('social')->name('social.')->group(function () {
        Route::get('{provider}/redirect', 'redirect')->name('redirect');
        Route::match(['get', 'post'], '{provider}/callback', 'callback')->name('callback');
    });

    Route::controller('RegisterController')->group(function () {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register');
        Route::post('check-user', 'checkUser')->name('checkUser')->withoutMiddleware('guest');
    });

    Route::controller('ForgotPasswordController')->group(function () {
        Route::post('password/email', 'sendResetLinkEmail')->name('password.email'); // actually sends code now
        Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
    });
    
    Route::controller('ResetPasswordController')->group(function () {
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
        Route::post('password/reset', 'reset')->name('password.update');
        Route::post('password/reset-code', 'resetWithCode')->name('password.update.code');
    });
});

Route::middleware('auth')->name('user.')->group(function () {
    Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/delete-account', [ProfileController::class, 'deleteAccount'])->name('profile.deleteAccount');
    Route::post('profile/toggle-listings', [ProfileController::class, 'toggleListings'])->name('profile.toggleListings');
    Route::post('username/check', [ProfileController::class, 'checkUsername'])->name('username.check');
    Route::post('cpf/check', [ProfileController::class, 'checkCpf'])->name('cpf.check');
});
