<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Static machinery list with availability status.
     * No machinery inventory backend exists yet, so availability is a fixed reference list.
     */
    private const MACHINERY = [
        ['name' => 'Harvester', 'status' => 'Available'],
        ['name' => 'Tractor', 'status' => 'Available'],
        ['name' => 'Pump Boat', 'status' => 'Unavailable'],
    ];

    public function index()
    {
        $machinery = self::MACHINERY;

        $requests = ScheduleRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('farmer.schedule', compact('machinery', 'requests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machinery' => 'required|string|in:Harvester,Tractor,Pump Boat',
            'land_size' => 'required|numeric|min:0.1',
            'scheduled_date' => 'required|date|after_or_equal:today',
        ]);

        ScheduleRequest::create([
            'user_id' => Auth::id(),
            'machinery' => $validated['machinery'],
            'land_size' => $validated['land_size'],
            'scheduled_date' => $validated['scheduled_date'],
            'status' => 'pending',
        ]);

        return redirect()->route('farmer.schedule')
            ->with('success', 'Schedule request submitted successfully!');
    }
}
