<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Read-only oversight of every active machinery booking. Schedules are
     * created and processed by the Manager (see manager.machine-schedule).
     */
    public function index(Request $request)
    {
        $query = ScheduleRequest::with('user.farmer')
            ->whereNull('archived_at')
            ->orderBy('created_at', 'desc');

        if ($request->filled('machinery')) {
            $query->where('machinery', $request->string('machinery'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $schedules = $query->get();

        $machineryList = Machine::whereNull('archived_at')->orderBy('name')->pluck('name');

        return view('admin.schedule', compact('schedules', 'machineryList'));
    }
}
