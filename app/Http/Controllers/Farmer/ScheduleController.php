<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $machinery = ScheduleRequest::MACHINERY;
        $machineryList = array_column(ScheduleRequest::MACHINERY, 'name');

        $requests = ScheduleRequest::where('user_id', Auth::id())
            ->whereNull('archived_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $month = $request->filled('month') ? Carbon::parse($request->string('month').'-01') : now()->startOfMonth();
        $machineryFilter = $request->string('calendar_machinery')->toString();

        $calendarDays = ScheduleRequest::calendarForMonth($month, $machineryFilter ?: null);

        $selectedMonth = $month;
        $firstWeekday = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;
        $monthOptions = collect(range(0, 3))->map(fn ($offset) => now()->startOfMonth()->addMonths($offset));

        return view('farmer.schedule', compact(
            'machinery', 'requests', 'machineryList', 'calendarDays', 'selectedMonth',
            'firstWeekday', 'daysInMonth', 'monthOptions', 'machineryFilter'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        if (ScheduleRequest::hasConflict($validated['machinery'], $validated['scheduled_date'], $validated['start_time'], $validated['end_time'])) {
            return back()->withErrors(['machinery' => 'This machinery is already booked for an overlapping date/time. Please choose another slot.'])->withInput();
        }

        $farmer = Auth::user()->farmer;

        ScheduleRequest::create([
            'user_id' => Auth::id(),
            'farmer_name' => $farmer?->full_name ?? Auth::user()->name,
            'member_type' => $farmer && $farmer->status === 'approved' ? 'member' : 'non-member',
            'machinery' => $validated['machinery'],
            'land_size' => $validated['land_size'],
            'scheduled_date' => $validated['scheduled_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'status' => 'pending',
        ]);

        return redirect()->route('farmer.schedule')
            ->with('success', 'Schedule request submitted successfully!');
    }

    /**
     * Submit a reschedule request for an approved schedule. This creates a new
     * pending request linked back to the original, which stays active until
     * the Manager approves or denies the reschedule.
     */
    public function reschedule(Request $request, ScheduleRequest $schedule)
    {
        abort_if($schedule->user_id !== Auth::id(), 403);
        abort_if($schedule->status !== 'approved', 422, 'Only approved schedules can be rescheduled.');

        $validated = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ]);

        if (ScheduleRequest::hasConflict($schedule->machinery, $validated['scheduled_date'], $validated['start_time'], $validated['end_time'], $schedule->id)) {
            return back()->withErrors(['scheduled_date' => 'This machinery is already booked for an overlapping date/time. Please choose another slot.'])->withInput();
        }

        ScheduleRequest::create([
            'user_id' => $schedule->user_id,
            'farmer_name' => $schedule->farmer_name,
            'member_type' => $schedule->member_type,
            'machinery' => $schedule->machinery,
            'land_size' => $schedule->land_size,
            'scheduled_date' => $validated['scheduled_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'status' => 'pending',
            'is_reschedule' => true,
            'original_schedule_id' => $schedule->id,
        ]);

        return redirect()->route('farmer.schedule')
            ->with('success', 'Reschedule request submitted for Manager approval.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'machinery' => ['required', 'string', 'in:'.implode(',', array_column(ScheduleRequest::MACHINERY, 'name'))],
            'land_size' => 'required|numeric|min:0.1',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ]);
    }
}
