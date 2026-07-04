<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Dashboard routes
Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Manager Routes
    Route::get('/manager/dashboard', function () {
        return view('manager.dashboard');
    })->name('manager.dashboard');

    Route::get('/manager/membership', [MembershipController::class, 'index'])->name('manager.membership');
    Route::post('/manager/membership', [MembershipController::class, 'store'])->name('manager.membership.store');
    Route::put('/manager/membership/{farmer}', [MembershipController::class, 'update'])->name('manager.membership.update');
    Route::patch('/manager/membership/{farmer}/archive', [MembershipController::class, 'archive'])->name('manager.membership.archive');
    Route::patch('/manager/membership/{farmer}/unarchive', [MembershipController::class, 'unarchive'])->name('manager.membership.unarchive');

    Route::get('/manager/farmer-profile', function () {
        return view('manager.farmer-profile');
    })->name('manager.farmer-profile');

    Route::get('/manager/schedule-approval', function () {
        return view('manager.schedule-approval');
    })->name('manager.schedule-approval');

    Route::get('/manager/machine-schedule', function () {
        return view('manager.machine-schedule');
    })->name('manager.machine-schedule');

    Route::get('/manager/financial', function () {
        return view('manager.financial');
    })->name('manager.financial');

    Route::get('/manager/cbu', function () {
        return view('manager.cbu');
    })->name('manager.cbu');

    Route::get('/manager/loan-request', function () {
        return view('manager.loan-request');
    })->name('manager.loan-request');

    Route::get('/manager/loan-management', function () {
        return view('manager.loan-management');
    })->name('manager.loan-management');

    Route::get('/manager/payment', function () {
        return view('manager.payment');
    })->name('manager.payment');

    Route::get('/manager/machinery', function () {
        return view('manager.machinery');
    })->name('manager.machinery');

    Route::get('/manager/complaints', function () {
        return view('manager.complaints');
    })->name('manager.complaints');

    Route::get('/manager/announcement', function () {
        return view('manager.announcement');
    })->name('manager.announcement');

    Route::get('/manager/reporting', function () {
        return view('manager.reporting');
    })->name('manager.reporting');

    Route::get('/manager/user-management', [UserController::class, 'index'])->name('manager.user-management');

    Route::post('/manager/user-management', [UserController::class, 'store'])->name('user.store');
    Route::patch('/manager/user-management/{user}/archive', [UserController::class, 'archive'])->name('user.archive');
    Route::get('/manager/user-management/{user}', [UserController::class, 'show'])->name('user.show');
    Route::put('/manager/user-management/{user}', [UserController::class, 'update'])->name('user.update');

    // Farmer Routes
    Route::get('/farmer/dashboard', function () {
        return view('farmer.dashboard');
    })->name('farmer.dashboard');
});

require __DIR__.'/auth.php';
