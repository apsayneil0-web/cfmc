<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipApprovalController;
use App\Http\Controllers\Admin\LoanApprovalController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Farmer\DashboardController as FarmerDashboardController;
use App\Http\Controllers\Farmer\ScheduleController as FarmerScheduleController;
use App\Http\Controllers\Farmer\LoanAppointmentController;
use App\Http\Controllers\Farmer\ComplaintController;
use App\Http\Controllers\Manager\ScheduleApprovalController;
use App\Http\Controllers\Manager\MachineScheduleController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\LoanRequestController;
use App\Http\Controllers\Manager\LoanManagementController;
use App\Http\Controllers\Manager\PaymentController;
use App\Http\Controllers\Manager\LoanAppointmentController as ManagerLoanAppointmentController;
use App\Http\Controllers\Manager\FarmerProfileController;

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

    Route::get('/admin/loan-approval', [LoanApprovalController::class, 'index'])->name('admin.loan-approval');
    Route::patch('/admin/loan-approval/{loan_request}/approve', [LoanApprovalController::class, 'approve'])->name('admin.loan-approval.approve');
    Route::patch('/admin/loan-approval/{loan_request}/deny', [LoanApprovalController::class, 'deny'])->name('admin.loan-approval.deny');
    Route::patch('/admin/loan-approval/batch/{batch}/approve', [LoanApprovalController::class, 'approveBatch'])->name('admin.loan-approval.batch-approve');
    Route::patch('/admin/loan-approval/batch/{batch}/deny', [LoanApprovalController::class, 'denyBatch'])->name('admin.loan-approval.batch-deny');

    Route::get('/admin/schedule', function () {
        return view('admin.schedule');
    })->name('admin.schedule');

    Route::get('/admin/user-management', [AdminUserController::class, 'index'])->name('admin.user-management');
    Route::post('/admin/user-management', [AdminUserController::class, 'store'])->name('admin.user.store');
    Route::patch('/admin/user-management/{user}/archive', [AdminUserController::class, 'archive'])->name('admin.user.archive');
    Route::get('/admin/user-management/{user}', [AdminUserController::class, 'show'])->name('admin.user.show');
    Route::put('/admin/user-management/{user}', [AdminUserController::class, 'update'])->name('admin.user.update');

    // Manager Routes
    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');

    Route::get('/manager/membership', [MembershipController::class, 'index'])->name('manager.membership');
    Route::post('/manager/membership', [MembershipController::class, 'store'])->name('manager.membership.store');
    Route::put('/manager/membership/{farmer}', [MembershipController::class, 'update'])->name('manager.membership.update');
    Route::patch('/manager/membership/{farmer}/archive', [MembershipController::class, 'archive'])->name('manager.membership.archive');
    Route::patch('/manager/membership/{farmer}/unarchive', [MembershipController::class, 'unarchive'])->name('manager.membership.unarchive');

    Route::get('/manager/farmer-profile', [FarmerProfileController::class, 'index'])->name('manager.farmer-profile');

    Route::get('/manager/schedule-approval', [ScheduleApprovalController::class, 'index'])->name('manager.schedule-approval');
    Route::patch('/manager/schedule-approval/{schedule}/approve', [ScheduleApprovalController::class, 'approve'])->name('manager.schedule-approval.approve');
    Route::patch('/manager/schedule-approval/{schedule}/deny', [ScheduleApprovalController::class, 'deny'])->name('manager.schedule-approval.deny');

    Route::get('/manager/machine-schedule', [MachineScheduleController::class, 'index'])->name('manager.machine-schedule');
    Route::post('/manager/machine-schedule', [MachineScheduleController::class, 'store'])->name('manager.machine-schedule.store');
    Route::put('/manager/machine-schedule/{schedule}', [MachineScheduleController::class, 'update'])->name('manager.machine-schedule.update');
    Route::patch('/manager/machine-schedule/{schedule}/archive', [MachineScheduleController::class, 'archive'])->name('manager.machine-schedule.archive');
    Route::patch('/manager/machine-schedule/{schedule}/complete', [MachineScheduleController::class, 'complete'])->name('manager.machine-schedule.complete');

    Route::get('/manager/financial', function () {
        return view('manager.financial');
    })->name('manager.financial');

    Route::get('/manager/cbu', function () {
        return view('manager.cbu');
    })->name('manager.cbu');

    Route::get('/manager/loan-request', [LoanRequestController::class, 'index'])->name('manager.loan-request');
    Route::post('/manager/loan-request', [LoanRequestController::class, 'store'])->name('manager.loan-request.store');
    Route::put('/manager/loan-request/{loan_request}', [LoanRequestController::class, 'update'])->name('manager.loan-request.update');
    Route::post('/manager/loan-request/{loan_request}/finalize', [LoanRequestController::class, 'finalize'])->name('manager.loan-request.finalize');
    Route::patch('/manager/loan-request/{loan_request}/archive', [LoanRequestController::class, 'archive'])->name('manager.loan-request.archive');

    Route::get('/manager/loan-management', [LoanManagementController::class, 'index'])->name('manager.loan-management');
    Route::put('/manager/loan-management/{loan}', [LoanManagementController::class, 'update'])->name('manager.loan-management.update');
    Route::patch('/manager/loan-management/{loan}/archive', [LoanManagementController::class, 'archive'])->name('manager.loan-management.archive');

    Route::get('/manager/loan-appointment', [ManagerLoanAppointmentController::class, 'index'])->name('manager.loan-appointment');
    Route::patch('/manager/loan-appointment/{loan_appointment}/approve', [ManagerLoanAppointmentController::class, 'approve'])->name('manager.loan-appointment.approve');
    Route::patch('/manager/loan-appointment/{loan_appointment}/cancel', [ManagerLoanAppointmentController::class, 'cancel'])->name('manager.loan-appointment.cancel');

    Route::get('/manager/payment', [PaymentController::class, 'index'])->name('manager.payment');
    Route::post('/manager/payment', [PaymentController::class, 'recordLoanPayment'])->name('manager.payment.record');

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
    Route::post('/farmer/schedule/{schedule}/reschedule', [FarmerScheduleController::class, 'reschedule'])->name('farmer.schedule.reschedule');

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
