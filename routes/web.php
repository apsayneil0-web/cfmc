<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipApprovalController;
use App\Http\Controllers\Farmer\DashboardController as FarmerDashboardController;
use App\Http\Controllers\Farmer\ScheduleController as FarmerScheduleController;
use App\Http\Controllers\Farmer\LoanAppointmentController;
use App\Http\Controllers\Farmer\ComplaintController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Dashboard routes
Route::middleware(['auth'])->group(function () {
    // Admin Routes
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/members', [MemberController::class, 'index'])->name('admin.members');

    Route::get('/admin/membership-approval', [MembershipApprovalController::class, 'index'])->name('admin.membership-approval');
    Route::patch('/admin/membership-approval/{farmer}/approve', [MembershipApprovalController::class, 'approve'])->name('admin.membership-approval.approve');
    Route::patch('/admin/membership-approval/{farmer}/reject', [MembershipApprovalController::class, 'reject'])->name('admin.membership-approval.reject');

    Route::get('/admin/loan-approval', function () {
        return view('admin.loan-approval');
    })->name('admin.loan-approval');

    Route::get('/admin/schedule', function () {
        return view('admin.schedule');
    })->name('admin.schedule');

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
    Route::get('/farmer/dashboard', [FarmerDashboardController::class, 'index'])->name('farmer.dashboard');

    Route::get('/farmer/schedule', [FarmerScheduleController::class, 'index'])->name('farmer.schedule');
    Route::post('/farmer/schedule', [FarmerScheduleController::class, 'store'])->name('farmer.schedule.store');

    Route::get('/farmer/loan-appointment', [LoanAppointmentController::class, 'index'])->name('farmer.loan-appointment');
    Route::post('/farmer/loan-appointment', [LoanAppointmentController::class, 'store'])->name('farmer.loan-appointment.store');
    Route::put('/farmer/loan-appointment/{loan_appointment}', [LoanAppointmentController::class, 'update'])->name('farmer.loan-appointment.update');
    Route::patch('/farmer/loan-appointment/{loan_appointment}/cancel', [LoanAppointmentController::class, 'cancel'])->name('farmer.loan-appointment.cancel');

    Route::get('/farmer/cbu', function () {
        return view('farmer.cbu');
    })->name('farmer.cbu');

    Route::get('/farmer/complaints', [ComplaintController::class, 'index'])->name('farmer.complaints');
    Route::post('/farmer/complaints', [ComplaintController::class, 'store'])->name('farmer.complaints.store');
    Route::put('/farmer/complaints/{complaint}', [ComplaintController::class, 'update'])->name('farmer.complaints.update');
    Route::delete('/farmer/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('farmer.complaints.destroy');
});

require __DIR__.'/auth.php';
