<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetController::class, 'showForgotForm'])
                ->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendOtp'])
                ->middleware('throttle:5,1')
                ->name('password.otp.send');

    Route::get('forgot-password/verify', [PasswordResetController::class, 'showVerifyForm'])
                ->name('password.otp.verify.form');
    Route::post('forgot-password/verify', [PasswordResetController::class, 'verifyOtp'])
                ->middleware('throttle:10,1')
                ->name('password.otp.verify');

    Route::get('reset-password', [PasswordResetController::class, 'showResetForm'])
                ->name('password.reset.form');
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])
                ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
