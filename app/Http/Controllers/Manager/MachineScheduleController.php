<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineScheduleController extends Controller
{
    private const MACHINERY_NAMES = ['Harvester', 'Tractor', 'Pump Boat'];

    /**
     * Schedule Management dashboard: monthly calendar of bookings plus the
     * full schedule/reschedule table (active, non-archived records).
     */
    public function index(Request $request)
    {
        $month = $request->filled('month') ? Carbon::parse($request->string('month').'-01') : now()->startOfMonth();

        $calendarDays = ScheduleRequest::calendarForMonth($month, $request->string('machinery')->toString() ?: null);

        $requests = ScheduleRequest::with(['user.farmer', 'originalSchedule', 'rescheduleRequests'])
            ->whereNull('archived_at')
            ->orderBy('scheduled_date', 'desc')
            ->get();

        $members = Farmer::where('status', 'approved')
            ->whereNotNull('account_user_id')
            ->orderBy('first_name')
            ->get();

        $machineryList = self::MACHINERY_NAMES;
        $selectedMonth = $month;
        $firstWeekday = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;
        $monthOptions = collect(range(-2, 3))->map(fn ($offset) => now()->startOfMonth()->addMonths($offset));

        return view('manager.machine-schedule', compact(
            'requests', 'calendarDays', 'machineryList', 'members', 'selectedMonth', 'firstWeekday', 'daysInMonth', 'monthOptions'
        ));
    }

    /**
     * Manager manually creates a schedule on behalf of a farmer (member or non-member).
     * Manager-created schedules are entered directly onto the official schedule.
     */
    public function store(Request $request)
    {
        $validated = $this->validateSchedule($request);

        if (ScheduleRequest::hasConflict($validated['machinery'], $validated['scheduled_date'], $validated['start_time'], $validated['end_time'])) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', 'This machinery is already booked for an overlapping date/time.');
        }

        ScheduleRequest::create([
            'user_id' => $validated['member_type'] === 'member' ? $validated['user_id'] : null,
            'farmer_name' => $validated['farmer_name'],
            'member_type' => $validated['member_type'],
            'machinery' => $validated['machinery'],
            'land_size' => $validated['land_size'],
            'scheduled_date' => $validated['scheduled_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'status' => 'approved',
        ]);

        return redirect()->route('manager.machine-schedule')
            ->with('success', 'Schedule created and added to the official schedule.');
    }

    /**
     * Update an existing schedule's details (time, machinery, location, etc.).
     */
    public function update(Request $request, ScheduleRequest $schedule)
    {
        $validated = $this->validateSchedule($request);

        if (ScheduleRequest::hasConflict($validated['machinery'], $validated['scheduled_date'], $validated['start_time'], $validated['end_time'], $schedule->id)) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', 'This machinery is already booked for an overlapping date/time.');
        }

        $schedule->update([
            'user_id' => $validated['member_type'] === 'member' ? $validated['user_id'] : null,
            'farmer_name' => $validated['farmer_name'],
            'member_type' => $validated['member_type'],
            'machinery' => $validated['machinery'],
            'land_size' => $validated['land_size'],
            'scheduled_date' => $validated['scheduled_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
        ]);

        return redirect()->route('manager.machine-schedule')
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Archive a schedule: removes it from the active list while preserving
     * it in the database for reporting and auditing.
     */
    public function archive(ScheduleRequest $schedule)
    {
        $schedule->update(['archived_at' => now()]);

        return redirect()->route('manager.machine-schedule')
            ->with('success', 'Schedule archived.');
    }

    /**
     * Close out a completed schedule by recording the total harvest yield,
     * which then feeds into the Harvesting Report.
     */
    public function complete(Request $request, ScheduleRequest $schedule)
    {
        abort_if($schedule->status !== 'approved', 422, 'Only approved schedules can be marked complete.');

        $validated = $request->validate([
            'harvest_yield' => 'required|numeric|min:0',
        ]);

        $schedule->update([
            'status' => 'completed',
            'harvest_yield' => $validated['harvest_yield'],
        ]);

        return redirect()->route('manager.machine-schedule')
            ->with('success', 'Schedule marked complete and harvest yield recorded.');
    }

    private function validateSchedule(Request $request): array
    {
        $validated = $request->validate([
            'member_type' => ['required', Rule::in(['member', 'non-member'])],
            'user_id' => 'nullable|required_if:member_type,member|exists:users,id',
            'farmer_name' => 'nullable|required_if:member_type,non-member|string|max:255',
            'machinery' => ['required', Rule::in(self::MACHINERY_NAMES)],
            'land_size' => 'required|numeric|min:0.1',
            'scheduled_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ]);

        if ($validated['member_type'] === 'member' && empty($validated['farmer_name'])) {
            $farmer = Farmer::where('account_user_id', $validated['user_id'])->first();
            $validated['farmer_name'] = $farmer?->full_name;
        }

        return $validated;
    }
}
