<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\LoanAppointment;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $pendingSchedules = ScheduleRequest::where('user_id', $userId)->where('status', 'pending')->count();
        $approvedSchedules = ScheduleRequest::where('user_id', $userId)->where('status', 'approved')->count();
        $deniedSchedules = ScheduleRequest::where('user_id', $userId)->where('status', 'denied')->count();

        $upcomingAppointments = LoanAppointment::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        $recentSchedules = ScheduleRequest::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $month = now()->startOfMonth();
        $calendarDays = ScheduleRequest::calendarForMonth($month);
        $firstWeekday = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;

        return view('farmer.dashboard', compact(
            'pendingSchedules',
            'approvedSchedules',
            'deniedSchedules',
            'upcomingAppointments',
            'recentSchedules',
            'calendarDays',
            'firstWeekday',
            'daysInMonth'
        ));
    }
}
