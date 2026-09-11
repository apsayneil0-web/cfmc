<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $machines = Machine::whereNull('archived_at')->orderBy('created_at', 'desc')->get();
        $machinery = $machines->map(fn (Machine $m) => [
            'name' => $m->name,
            'status' => $m->status === 'maintenance' ? 'Unavailable' : 'Available',
            'quantity' => max(1, (int) $m->quantity),
        ])->all();
        $machineryList = $machines->pluck('name')->all();
        $crops = Crop::orderBy('name')->get();

        $requests = ScheduleRequest::with('crop')
            ->where('user_id', Auth::id())
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
            'machinery', 'requests', 'machineryList', 'crops', 'calendarDays', 'selectedMonth',
            'firstWeekday', 'daysInMonth', 'monthOptions', 'machineryFilter'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $machine = Machine::whereNull('archived_at')->where('name', $validated['machinery'])->firstOrFail();
        $unitsRequested = min(max(1, (int) ($validated['units_requested'] ?? 1)), max(1, (int) $machine->quantity));

        if (ScheduleRequest::hasConflict($machine->id, $validated['scheduled_date'], $validated['start_time'], $validated['end_time'], null, $unitsRequested)) {
            return back()->withErrors(['machinery' => "Not enough {$machine->name} units are free for that date/time. Please request fewer units or choose another slot."])->withInput();
        }

        $dailyLimit = $machine->effective_daily_limit;

        if (ScheduleRequest::wouldExceedDailyCapacity($machine->id, $validated['scheduled_date'], (float) $validated['land_size'], $dailyLimit)) {
            $remaining = ScheduleRequest::remainingCapacity($machine->id, $validated['scheduled_date'], $dailyLimit);
            return back()->withErrors(['land_size' => "{$machine->name} has reached its {$dailyLimit} hectare daily limit for this date. Only {$remaining} hectare(s) remaining."])->withInput();
        }

        $farmer = Auth::user()->farmer;

        ScheduleRequest::create([
            'user_id' => Auth::id(),
            'farmer_name' => $farmer?->full_name ?? Auth::user()->name,
            'member_type' => $farmer && $farmer->status === 'approved' ? 'member' : 'non-member',
            'machinery' => $machine->name,
            'machine_id' => $machine->id,
            'land_size' => $validated['land_size'],
            'units_requested' => $unitsRequested,
            'crop_id' => $validated['crop_id'],
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
            'scheduled_date' => ['required', 'date', 'after_or_equal:'.ScheduleRequest::earliestAllowedDate()->toDateString()],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ], [
            'scheduled_date.after_or_equal' => 'The schedule date must be at least '.ScheduleRequest::MIN_LEAD_DAYS.' days from today.',
        ]);

        if (ScheduleRequest::hasConflict($schedule->machine_id, $validated['scheduled_date'], $validated['start_time'], $validated['end_time'], $schedule->id, $schedule->units_requested)) {
            return back()->withErrors(['scheduled_date' => 'This machinery is already booked for an overlapping date/time. Please choose another slot.'])->withInput();
        }

        $dailyLimit = $schedule->machine?->effective_daily_limit ?? Machine::DEFAULT_DAILY_HECTARE_LIMIT;

        if (ScheduleRequest::wouldExceedDailyCapacity($schedule->machine_id, $validated['scheduled_date'], (float) $schedule->land_size, $dailyLimit, $schedule->id)) {
            $remaining = ScheduleRequest::remainingCapacity($schedule->machine_id, $validated['scheduled_date'], $dailyLimit, $schedule->id);
            return back()->withErrors(['scheduled_date' => "{$schedule->machinery} has reached its {$dailyLimit} hectare daily limit for this date. Only {$remaining} hectare(s) remaining."])->withInput();
        }

        ScheduleRequest::create([
            'user_id' => $schedule->user_id,
            'farmer_name' => $schedule->farmer_name,
            'member_type' => $schedule->member_type,
            'machinery' => $schedule->machinery,
            'machine_id' => $schedule->machine_id,
            'land_size' => $schedule->land_size,
            'units_requested' => $schedule->units_requested,
            'crop_id' => $schedule->crop_id,
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
        $machineryNames = Machine::whereNull('archived_at')->pluck('name');

        return $request->validate([
            'machinery' => ['required', 'string', Rule::in($machineryNames)],
            'land_size' => 'required|numeric|min:0.1',
            'units_requested' => 'nullable|integer|min:1',
            'crop_id' => 'required|exists:crops,id',
            'scheduled_date' => ['required', 'date', 'after_or_equal:'.ScheduleRequest::earliestAllowedDate()->toDateString()],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
        ], [
            'scheduled_date.after_or_equal' => 'The schedule date must be at least '.ScheduleRequest::MIN_LEAD_DAYS.' days from today.',
        ]);
    }
}
