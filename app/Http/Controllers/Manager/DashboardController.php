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

        $stats = [
            'total_farmers' => Farmer::where('status', 'approved')->count(),
            'pending_membership' => Farmer::where('status', 'pending')->count(),
            'pending_schedules' => ScheduleRequest::whereNull('archived_at')->where('status', 'pending')->count(),
            'approved_schedules' => ScheduleRequest::whereNull('archived_at')->where('status', 'approved')->count(),
            'active_loan_applications' => LoanRequest::where('status', 'pending')->count(),
            'available_machinery' => Machine::whereNull('archived_at')->get()->filter(fn ($m) => $m->status === 'available')->count(),
            'total_cbu' => Cbu::sum('balance'),
            'total_expenses' => Expense::sum('amount'),
        ];

        $recentActivities = $this->recentActivities();

        return view('manager.dashboard', compact('calendarDays', 'firstWeekday', 'daysInMonth', 'stats', 'recentActivities'));
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
                'bg' => 'bg-green-100',
                'color' => 'text-green-600',
                'title' => 'Membership request approved',
                'subtitle' => "{$farmer->full_name} - Brgy. {$farmer->barangay}",
                'at' => $farmer->updated_at,
            ]);

        $schedules = ScheduleRequest::with('user.farmer')
            ->whereNull('archived_at')
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
                'bg' => 'bg-yellow-100',
                'color' => 'text-yellow-600',
                'title' => 'Machinery maintenance alert',
                'subtitle' => "{$machine->name} - {$machine->usage_hours} hours reached",
                'at' => $machine->updated_at,
            ]);

        $payments = LoanPayment::with('loan.loanRequest.farmer')
            ->where('type', 'payment')
            ->orderByDesc('created_at')
            ->take(3)
            ->get()
            ->map(fn ($payment) => (object) [
                'icon' => 'fa-money-bill-wave',
                'bg' => 'bg-purple-100',
                'color' => 'text-purple-600',
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
