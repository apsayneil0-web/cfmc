<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MembershipApprovalController;
use App\Http\Controllers\Admin\LoanApprovalController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Farmer\DashboardController as FarmerDashboardController;
use App\Http\Controllers\Farmer\ScheduleController as FarmerScheduleController;
use App\Http\Controllers\Farmer\LoanAppointmentController;
use App\Http\Controllers\Farmer\LoanController as FarmerLoanController;
use App\Http\Controllers\Farmer\ComplaintController;
use App\Http\Controllers\Farmer\CbuController as FarmerCbuController;
use App\Http\Controllers\Manager\ScheduleApprovalController;
use App\Http\Controllers\Manager\MachineScheduleController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\LoanRequestController;
use App\Http\Controllers\Manager\LoanManagementController;
use App\Http\Controllers\Manager\MachineController;
use App\Http\Controllers\Manager\MachineUsageController;
use App\Http\Controllers\Manager\PaymentController;
use App\Http\Controllers\Manager\CbuController;
use App\Http\Controllers\Manager\FinancialController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\Manager\LoanAppointmentController as ManagerLoanAppointmentController;
use App\Http\Controllers\Manager\FarmerProfileController;
use App\Http\Controllers\Manager\AnnouncementController;
use App\Http\Controllers\Manager\ComplaintController as ManagerComplaintController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Dashboard routes
Route::middleware(['auth', 'account.active', 'nocache'])->group(function () {
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

    Route::get('/admin/approved-loans', [LoanApprovalController::class, 'approved'])->name('admin.approved-loans');

    Route::get('/admin/schedule', [AdminScheduleController::class, 'index'])->name('admin.schedule');

    Route::get('/admin/user-management', [AdminUserController::class, 'index'])->name('admin.user-management');
    Route::post('/admin/user-management', [AdminUserController::class, 'store'])->name('admin.user.store');
    Route::patch('/admin/user-management/{user}/archive', [AdminUserController::class, 'archive'])->name('admin.user.archive');
    Route::patch('/admin/user-management/{user}/unarchive', [AdminUserController::class, 'unarchive'])->name('admin.user.unarchive');
    Route::patch('/admin/user-management/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.user.toggle-status');
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
    Route::post('/manager/machine-schedule/shift-day', [MachineScheduleController::class, 'shiftDay'])->name('manager.machine-schedule.shift-day');

    Route::get('/manager/financial', [FinancialController::class, 'index'])->name('manager.financial');
    Route::post('/manager/financial', [FinancialController::class, 'store'])->name('manager.financial.store');

    Route::get('/manager/cbu', [CbuController::class, 'index'])->name('manager.cbu');
    Route::post('/manager/cbu', [CbuController::class, 'store'])->name('manager.cbu.store');

    Route::get('/manager/loan-request', [LoanRequestController::class, 'index'])->name('manager.loan-request');
    Route::post('/manager/loan-request', [LoanRequestController::class, 'store'])->name('manager.loan-request.store');
    Route::put('/manager/loan-request/{loan_request}', [LoanRequestController::class, 'update'])->name('manager.loan-request.update');
    Route::post('/manager/loan-request/{loan_request}/finalize', [LoanRequestController::class, 'finalize'])->name('manager.loan-request.finalize');
    Route::post('/manager/loan-request/batch/{batch}/finalize', [LoanRequestController::class, 'finalizeBatch'])->name('manager.loan-request.batch-finalize');
    Route::patch('/manager/loan-request/{loan_request}/archive', [LoanRequestController::class, 'archive'])->name('manager.loan-request.archive');
    Route::patch('/manager/loan-request/{loan_request}/remove-from-batch', [LoanRequestController::class, 'removeBatchMember'])->name('manager.loan-request.remove-batch-member');

    Route::get('/manager/loan-management', [LoanManagementController::class, 'index'])->name('manager.loan-management');
    Route::put('/manager/loan-management/{loan}', [LoanManagementController::class, 'update'])->name('manager.loan-management.update');
    Route::patch('/manager/loan-management/{loan}/disburse', [LoanManagementController::class, 'disburse'])->name('manager.loan-management.disburse');
    Route::patch('/manager/loan-management/{loan}/archive', [LoanManagementController::class, 'archive'])->name('manager.loan-management.archive');

    Route::get('/manager/loan-appointment', [ManagerLoanAppointmentController::class, 'index'])->name('manager.loan-appointment');
    Route::patch('/manager/loan-appointment/{loan_appointment}/approve', [ManagerLoanAppointmentController::class, 'approve'])->name('manager.loan-appointment.approve');
    Route::patch('/manager/loan-appointment/{loan_appointment}/cancel', [ManagerLoanAppointmentController::class, 'cancel'])->name('manager.loan-appointment.cancel');

    Route::get('/manager/payment', [PaymentController::class, 'index'])->name('manager.payment');
    Route::post('/manager/payment', [PaymentController::class, 'recordLoanPayment'])->name('manager.payment.record');
    Route::post('/manager/payment/cbu', [PaymentController::class, 'recordCbuPayment'])->name('manager.payment.record-cbu');
    Route::get('/manager/payment/{loan_payment}/receipt', [PaymentController::class, 'receipt'])->name('manager.payment.receipt');

    Route::get('/manager/machinery', [MachineController::class, 'index'])->name('manager.machinery');
    Route::post('/manager/machinery', [MachineController::class, 'store'])->name('manager.machinery.store');
    Route::put('/manager/machinery/{machine}', [MachineController::class, 'update'])->name('manager.machinery.update');
    Route::patch('/manager/machinery/{machine}/archive', [MachineController::class, 'archive'])->name('manager.machinery.archive');

    Route::get('/manager/machine-usage', [MachineUsageController::class, 'index'])->name('manager.machine-usage');

    Route::get('/manager/complaints', [ManagerComplaintController::class, 'index'])->name('manager.complaints');
    Route::patch('/manager/complaints/{complaint}/respond', [ManagerComplaintController::class, 'respond'])->name('manager.complaints.respond');

    Route::get('/manager/announcement', [AnnouncementController::class, 'index'])->name('manager.announcement');
    Route::post('/manager/announcement', [AnnouncementController::class, 'store'])->name('manager.announcement.store');
    Route::put('/manager/announcement/{announcement}', [AnnouncementController::class, 'update'])->name('manager.announcement.update');
    Route::patch('/manager/announcement/{announcement}/archive', [AnnouncementController::class, 'archive'])->name('manager.announcement.archive');

    Route::get('/manager/reporting', [ReportController::class, 'index'])->name('manager.reporting');
    Route::get('/manager/reporting/export', [ReportController::class, 'export'])->name('manager.reporting.export');

    Route::get('/manager/user-management', [UserController::class, 'index'])->name('manager.user-management');

    Route::post('/manager/user-management', [UserController::class, 'store'])->name('user.store');
    Route::patch('/manager/user-management/{user}/archive', [UserController::class, 'archive'])->name('user.archive');
    Route::patch('/manager/user-management/{user}/unarchive', [UserController::class, 'unarchive'])->name('user.unarchive');
    Route::patch('/manager/user-management/{user}/unlock', [UserController::class, 'unlock'])->name('user.unlock');
    Route::patch('/manager/user-management/{user}/change-password', [UserController::class, 'changePassword'])->name('user.change-password');
    Route::patch('/manager/user-management/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('user.toggle-status');
    Route::get('/manager/user-management/{user}', [UserController::class, 'show'])->name('user.show');
    Route::put('/manager/user-management/{user}', [UserController::class, 'update'])->name('user.update');

    // Farmer Routes
    Route::get('/farmer/dashboard', [FarmerDashboardController::class, 'index'])->name('farmer.dashboard');

    Route::get('/farmer/loans', [FarmerLoanController::class, 'index'])->name('farmer.loans');

    Route::get('/farmer/schedule', [FarmerScheduleController::class, 'index'])->name('farmer.schedule');
    Route::post('/farmer/schedule', [FarmerScheduleController::class, 'store'])->name('farmer.schedule.store');
    Route::post('/farmer/schedule/{schedule}/reschedule', [FarmerScheduleController::class, 'reschedule'])->name('farmer.schedule.reschedule');

    Route::get('/farmer/loan-appointment', [LoanAppointmentController::class, 'index'])->name('farmer.loan-appointment');
    Route::post('/farmer/loan-appointment', [LoanAppointmentController::class, 'store'])->name('farmer.loan-appointment.store');
    Route::put('/farmer/loan-appointment/{loan_appointment}', [LoanAppointmentController::class, 'update'])->name('farmer.loan-appointment.update');
    Route::patch('/farmer/loan-appointment/{loan_appointment}/cancel', [LoanAppointmentController::class, 'cancel'])->name('farmer.loan-appointment.cancel');

    Route::get('/farmer/cbu', [FarmerCbuController::class, 'index'])->name('farmer.cbu');

    Route::get('/farmer/complaints', [ComplaintController::class, 'index'])->name('farmer.complaints');
    Route::post('/farmer/complaints', [ComplaintController::class, 'store'])->name('farmer.complaints.store');
    Route::put('/farmer/complaints/{complaint}', [ComplaintController::class, 'update'])->name('farmer.complaints.update');
    Route::delete('/farmer/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('farmer.complaints.destroy');
    Route::patch('/farmer/complaints/{complaint}/reopen', [ComplaintController::class, 'reopen'])->name('farmer.complaints.reopen');

    // Shared Routes
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

    Route::post('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture.update');
});

require __DIR__.'/auth.php';
