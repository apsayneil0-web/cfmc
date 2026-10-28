<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cbu;
use App\Models\Expense;
use App\Models\Farmer;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $month = now()->startOfMonth();
        $calendarDays = ScheduleRequest::calendarForMonth($month);
        $firstWeekday = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;

        $machines = Machine::whereNull('archived_at')->get();

        $stats = [
            'total_farmers' => Farmer::where('status', 'approved')->count(),
            'pending_membership' => Farmer::where('status', 'pending')->count(),
            'pending_schedules' => ScheduleRequest::whereNull('archived_at')->where('status', 'pending')->visibleAccount()->count(),
            'approved_schedules' => ScheduleRequest::whereNull('archived_at')->where('status', 'approved')->visibleAccount()->count(),
            'active_loan_applications' => LoanRequest::where('status', 'pending')->visibleAccount()->count(),
            'available_machinery' => $machines->filter(fn ($m) => $m->status === 'available')->count(),
            'maintenance_machinery' => $machines->filter(fn ($m) => $m->status === 'maintenance')->count(),
            'total_cbu' => Cbu::visibleAccount()->sum('balance'),
            'total_expenses' => Expense::sum('amount'),
        ];

        $attentionCount = $stats['pending_membership'] + $stats['pending_schedules']
            + $stats['active_loan_applications'] + $stats['maintenance_machinery'];

        $localNow = now();
        $greeting = match (true) {
            $localNow->hour < 12 => 'Good morning',
            $localNow->hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };

        $nextBookings = ScheduleRequest::with('user.farmer')
            ->whereNull('archived_at')
            ->where('status', 'approved')
            ->visibleAccount()
            ->whereDate('scheduled_date', '>=', $localNow->toDateString())
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->take(3)
            ->get();

        $recentActivities = $this->recentActivities();

        return view('manager.dashboard', compact(
            'calendarDays', 'firstWeekday', 'daysInMonth', 'stats', 'recentActivities',
            'attentionCount', 'greeting', 'localNow', 'nextBookings'
        ));
    }

    /**
     * Merge the last few events across membership, scheduling, machinery, and
     * loan payments into one feed, since there's no dedicated activity log table.
     */
    private function recentActivities()
    {
        $membership = Farmer::where('status', 'approved')
            ->orderByDesc('updated_at')
            ->take(3)
            ->get()
            ->map(fn ($farmer) => (object) [
                'icon' => 'fa-check',
                'bg' => 'bg-success-subtle',
                'color' => 'text-success',
                'title' => 'Membership request approved',
                'subtitle' => "{$farmer->full_name} - Brgy. {$farmer->barangay}",
                'at' => $farmer->updated_at,
            ]);

        $schedules = ScheduleRequest::with('user.farmer')
            ->whereNull('archived_at')
            ->visibleAccount()
            ->orderByDesc('created_at')
            ->take(3)
            ->get()
            ->map(fn ($schedule) => (object) [
                'icon' => 'fa-calendar',
                'bg' => 'bg-brand-light',
                'color' => 'text-brand',
                'title' => 'Schedule request submitted',
                'subtitle' => "{$schedule->display_name} - {$schedule->machinery} Rental",
                'at' => $schedule->created_at,
            ]);

        $maintenance = Machine::whereNull('archived_at')
            ->get()
            ->filter(fn ($machine) => $machine->status === 'maintenance')
            ->take(3)
            ->map(fn ($machine) => (object) [
                'icon' => 'fa-exclamation-triangle',
                'bg' => 'bg-danger-subtle',
                'color' => 'text-danger',
                'title' => 'Machinery maintenance alert',
                'subtitle' => "{$machine->name} - {$machine->usage_hours} hours reached",
                'at' => $machine->updated_at,
            ]);

        $payments = LoanPayment::with('loan.loanRequest.farmer')
            ->where('type', 'payment')
            ->visibleAccount()
            ->orderByDesc('created_at')
            ->take(3)
            ->get()
            ->map(fn ($payment) => (object) [
                'icon' => 'fa-money-bill-wave',
                'bg' => 'bg-warning-subtle',
                'color' => 'text-warning',
                'title' => 'Loan payment received',
                'subtitle' => "{$payment->loan->farmer->full_name} - ".peso($payment->amount),
                'at' => $payment->created_at,
            ]);

        return $membership->concat($schedules)->concat($maintenance)->concat($payments)
            ->sortByDesc('at')
            ->take(4)
            ->values();
    }
}
