<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
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

        return view('manager.dashboard', compact('calendarDays', 'firstWeekday', 'daysInMonth'));
    }
}
