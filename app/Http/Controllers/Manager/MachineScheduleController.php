<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineScheduleController extends Controller
{
    /**
     * Names of machines currently in the fleet (archived ones can't be booked).
     */
    private function machineryNames()
    {
        return Machine::whereNull('archived_at')->orderBy('created_at', 'desc')->pluck('name');
    }

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
            ->orderBy('created_at', 'desc')
            ->get();

        $members = Farmer::where('status', 'approved')
            ->whereNotNull('account_user_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $machineryList = $this->machineryNames()->all();
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
        $machine = Machine::whereNull('archived_at')->where('name', $validated['machinery'])->firstOrFail();

        if (ScheduleRequest::hasConflict($machine->id, $validated['scheduled_date'], $validated['start_time'], $validated['end_time'])) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', 'This machinery is already booked for an overlapping date/time.');
        }

        $dailyLimit = (float) $machine->daily_hectare_limit;

        if (ScheduleRequest::wouldExceedDailyCapacity($machine->id, $validated['scheduled_date'], (float) $validated['land_size'], $dailyLimit)) {
            $remaining = ScheduleRequest::remainingCapacity($machine->id, $validated['scheduled_date'], $dailyLimit);
            return redirect()->route('manager.machine-schedule')
                ->with('error', "{$machine->name} has reached its {$dailyLimit} hectare daily limit for this date. Only {$remaining} hectare(s) remaining.");
        }

        ScheduleRequest::create([
            'user_id' => $validated['member_type'] === 'member' ? $validated['user_id'] : null,
            'farmer_name' => $validated['farmer_name'],
            'member_type' => $validated['member_type'],
            'machinery' => $machine->name,
            'machine_id' => $machine->id,
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
        $machine = Machine::whereNull('archived_at')->where('name', $validated['machinery'])->firstOrFail();

        if (ScheduleRequest::hasConflict($machine->id, $validated['scheduled_date'], $validated['start_time'], $validated['end_time'], $schedule->id)) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', 'This machinery is already booked for an overlapping date/time.');
        }

        $dailyLimit = (float) $machine->daily_hectare_limit;

        if (ScheduleRequest::wouldExceedDailyCapacity($machine->id, $validated['scheduled_date'], (float) $validated['land_size'], $dailyLimit, $schedule->id)) {
            $remaining = ScheduleRequest::remainingCapacity($machine->id, $validated['scheduled_date'], $dailyLimit, $schedule->id);
            return redirect()->route('manager.machine-schedule')
                ->with('error', "{$machine->name} has reached its {$dailyLimit} hectare daily limit for this date. Only {$remaining} hectare(s) remaining.");
        }

        $schedule->update([
            'user_id' => $validated['member_type'] === 'member' ? $validated['user_id'] : null,
            'farmer_name' => $validated['farmer_name'],
            'member_type' => $validated['member_type'],
            'machinery' => $machine->name,
            'machine_id' => $machine->id,
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
            'machinery' => ['required', Rule::in($this->machineryNames())],
            'land_size' => 'required|numeric|min:0.1',
            'scheduled_date' => ['required', 'date', 'after_or_equal:'.ScheduleRequest::earliestAllowedDate()->toDateString()],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ], [
            'scheduled_date.after_or_equal' => 'The schedule date must be at least '.ScheduleRequest::MIN_LEAD_DAYS.' days from today.',
        ]);

        if ($validated['member_type'] === 'member' && empty($validated['farmer_name'])) {
            $farmer = Farmer::where('account_user_id', $validated['user_id'])->first();
            $validated['farmer_name'] = $farmer?->full_name;
        }

        return $validated;
    }
}
