<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalFarmers = Farmer::where('status', '!=', 'archived')->count();
        $pendingMembership = Farmer::where('status', 'pending')->count();
        $approvedFarmers = Farmer::where('status', 'approved')->count();
        $archivedFarmers = Farmer::where('status', 'archived')->count();
        $rejectedFarmers = Farmer::where('status', 'rejected')->count();

        $recentApplications = Farmer::with('crop')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $month = now()->startOfMonth();
        $calendarDays = ScheduleRequest::calendarForMonth($month);
        $firstWeekday = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;

        return view('admin.dashboard', compact(
            'totalFarmers',
            'pendingMembership',
            'approvedFarmers',
            'archivedFarmers',
            'rejectedFarmers',
            'recentApplications',
            'calendarDays',
            'firstWeekday',
            'daysInMonth'
        ));
    }
}
