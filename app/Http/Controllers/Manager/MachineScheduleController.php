<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Machine;
use App\Models\Notification;
use App\Models\ScheduleRequest;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $showArchived = $request->boolean('archived');

        $calendarDays = ScheduleRequest::calendarForMonth($month, $request->string('machinery')->toString() ?: null);

        $requests = ScheduleRequest::with(['user.farmer', 'originalSchedule', 'rescheduleRequests', 'crop'])
            ->when($showArchived, fn ($query) => $query->whereNotNull('archived_at'), fn ($query) => $query->whereNull('archived_at'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('created_at', 'desc')
            ->get();

        $members = Farmer::where('status', 'approved')
            ->whereNotNull('account_user_id')
            ->orderBy('created_at', 'desc')
            ->get();

        $machineryList = $this->machineryNames()->all();
        $crops = Crop::orderBy('name')->get();
        $selectedMonth = $month;
        $firstWeekday = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;
        $monthOptions = collect(range(-2, 3))->map(fn ($offset) => now()->startOfMonth()->addMonths($offset));

        return view('manager.machine-schedule', compact(
            'requests', 'calendarDays', 'machineryList', 'members', 'crops', 'selectedMonth', 'firstWeekday', 'daysInMonth', 'monthOptions', 'showArchived'
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
            'contact_number' => $validated['member_type'] === 'non-member' ? $validated['contact_number'] : null,
            'member_type' => $validated['member_type'],
            'machinery' => $machine->name,
            'machine_id' => $machine->id,
            'land_size' => $validated['land_size'],
            'crop_id' => $validated['crop_id'],
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
            'contact_number' => $validated['member_type'] === 'non-member' ? $validated['contact_number'] : null,
            'member_type' => $validated['member_type'],
            'machinery' => $machine->name,
            'machine_id' => $machine->id,
            'land_size' => $validated['land_size'],
            'crop_id' => $validated['crop_id'],
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
     * Restore an archived schedule back onto the active list.
     */
    public function unarchive(ScheduleRequest $schedule)
    {
        $schedule->update(['archived_at' => null]);

        return redirect()->route('manager.machine-schedule', ['archived' => 1])
            ->with('success', 'Schedule restored.');
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

    /**
     * Push every active (pending/approved), non-archived schedule — every
     * scheduled date, not just today onward — forward by one day, e.g. for a
     * fleet-wide rainout or delay, and notify each affected farmer account.
     */
    public function shiftDay(Request $request)
    {
        return $this->applyBulkShift($request, 1);
    }

    /**
     * Same as shiftDay(), but pulls every active schedule back by one day
     * instead — e.g. to undo an earlier move. Any schedule that would land
     * before today or inside the minimum lead-time window is skipped rather
     * than backdated, since that would put it somewhere it can't be serviced.
     */
    public function shiftDayBackward(Request $request)
    {
        return $this->applyBulkShift($request, -1);
    }

    private function applyBulkShift(Request $request, int $days)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        $reason = $validated['reason'];

        $schedules = ScheduleRequest::whereIn('status', ['pending', 'approved'])
            ->whereNull('archived_at')
            ->get();

        if ($schedules->isEmpty()) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', 'There are no schedules to move.');
        }

        $earliestAllowedDate = ScheduleRequest::earliestAllowedDate();
        $moved = 0;
        $skipped = [];

        DB::transaction(function () use ($schedules, $days, $reason, $earliestAllowedDate, &$moved, &$skipped) {
            $rows = [];

            foreach ($schedules as $schedule) {
                $newDate = $schedule->scheduled_date->copy()->addDays($days);

                if ($days < 0 && $newDate->lt($earliestAllowedDate)) {
                    $skipped[] = 'SCH-'.str_pad((string) $schedule->id, 3, '0', STR_PAD_LEFT);

                    continue;
                }

                $oldDate = $schedule->scheduled_date->format('M d, Y');
                $schedule->update(['scheduled_date' => $newDate->toDateString()]);
                $moved++;

                $direction = $days > 0 ? 'moved to' : 'moved back to';
                $time = Carbon::parse($schedule->start_time)->format('g:i A').' - '.Carbon::parse($schedule->end_time)->format('g:i A');
                $message = "Your {$schedule->machinery} schedule originally set for {$oldDate} has been {$direction} {$newDate->format('M d, Y')}. Time ({$time}) and location ({$schedule->location}) remain the same. Reason: {$reason}";

                if ($schedule->user_id) {
                    $rows[] = [
                        'user_id' => $schedule->user_id,
                        'schedule_id' => $schedule->id,
                        'title' => 'Your Schedule Has Been Moved',
                        'message' => $message,
                        'type' => 'reminder',
                        'is_read' => false,
                        'created_at' => now(),
                    ];
                } elseif ($schedule->contact_number) {
                    app(SmsService::class)->send($schedule->contact_number, $message);
                }
            }

            if (! empty($rows)) {
                Notification::insert($rows);
            }
        });

        $verb = $days > 0 ? 'forward' : 'back';

        if ($moved === 0) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', "Could not move any schedules {$verb} — it would put ".implode(', ', $skipped).' before the minimum lead time.');
        }

        $summary = "{$moved} schedule(s) moved {$verb} by 1 day. Affected farmers have been notified.";

        if (! empty($skipped)) {
            $summary .= " Skipped (would fall before the minimum lead time): ".implode(', ', $skipped).'.';
        }

        return redirect()->route('manager.machine-schedule')->with('success', $summary);
    }

    /**
     * Push every active (pending/approved), non-archived schedule on one or
     * more manager-picked dates forward (or back) by one day, notifying each
     * affected farmer. Unlike applyBulkShift(), which moves every schedule
     * together (so relative spacing never changes), moving a single date's
     * schedules can land them on a day that already has other bookings — so
     * each move is checked for conflicts/capacity and skipped rather than
     * overbooking.
     */
    public function shiftSpecificDay(Request $request)
    {
        $validated = $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'date_format:Y-m-d',
            'direction' => ['nullable', Rule::in(['forward', 'backward'])],
            'reason' => 'required|string|max:500',
        ]);

        $days = ($validated['direction'] ?? 'forward') === 'backward' ? -1 : 1;
        $reason = $validated['reason'];
        $dates = collect($validated['dates'])->unique()->values();

        $schedules = ScheduleRequest::whereIn('status', ['pending', 'approved'])
            ->whereNull('archived_at')
            ->whereIn('scheduled_date', $dates)
            ->get();

        if ($schedules->isEmpty()) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', 'There are no schedules on the selected date(s) to move.');
        }

        $earliestAllowedDate = ScheduleRequest::earliestAllowedDate();
        $moved = 0;
        $skippedLeadTime = [];
        $skippedConflict = [];

        DB::transaction(function () use ($schedules, $days, $reason, $earliestAllowedDate, &$moved, &$skippedLeadTime, &$skippedConflict) {
            foreach ($schedules as $schedule) {
                $newDate = $schedule->scheduled_date->copy()->addDays($days);
                $code = 'SCH-'.str_pad((string) $schedule->id, 3, '0', STR_PAD_LEFT);

                if ($days < 0 && $newDate->lt($earliestAllowedDate)) {
                    $skippedLeadTime[] = $code;

                    continue;
                }

                $dailyLimit = (float) ($schedule->machine?->daily_hectare_limit ?? Machine::DEFAULT_DAILY_HECTARE_LIMIT);

                $conflict = ScheduleRequest::hasConflict($schedule->machine_id, $newDate->toDateString(), $schedule->start_time, $schedule->end_time, $schedule->id)
                    || ScheduleRequest::wouldExceedDailyCapacity($schedule->machine_id, $newDate->toDateString(), (float) $schedule->land_size, $dailyLimit, $schedule->id);

                if ($conflict) {
                    $skippedConflict[] = $code;

                    continue;
                }

                $oldDate = $schedule->scheduled_date->format('M d, Y');
                $schedule->update(['scheduled_date' => $newDate->toDateString()]);
                $moved++;

                $direction = $days > 0 ? 'moved to' : 'moved back to';
                $time = Carbon::parse($schedule->start_time)->format('g:i A').' - '.Carbon::parse($schedule->end_time)->format('g:i A');
                $message = "Your {$schedule->machinery} schedule originally set for {$oldDate} has been {$direction} {$newDate->format('M d, Y')}. Time ({$time}) and location ({$schedule->location}) remain the same. Reason: {$reason}";

                if ($schedule->user_id) {
                    Notification::create([
                        'user_id' => $schedule->user_id,
                        'schedule_id' => $schedule->id,
                        'title' => 'Your Schedule Has Been Moved',
                        'message' => $message,
                        'type' => 'reminder',
                        'is_read' => false,
                        'created_at' => now(),
                    ]);
                } elseif ($schedule->contact_number) {
                    app(SmsService::class)->send($schedule->contact_number, $message);
                }
            }
        });

        $verb = $days > 0 ? 'forward' : 'back';
        $reasons = [];

        if (! empty($skippedConflict)) {
            $reasons[] = 'conflicts with an existing booking on the target day: '.implode(', ', $skippedConflict);
        }

        if (! empty($skippedLeadTime)) {
            $reasons[] = 'would fall before the '.ScheduleRequest::MIN_LEAD_DAYS.'-day minimum lead time: '.implode(', ', $skippedLeadTime);
        }

        if ($moved === 0) {
            return redirect()->route('manager.machine-schedule')
                ->with('error', "Could not move any schedules {$verb} — ".implode('; ', $reasons).'.');
        }

        $summary = "{$moved} schedule(s) moved {$verb} by 1 day. Affected farmers have been notified.";

        if (! empty($reasons)) {
            $summary .= ' Skipped — '.implode('; ', $reasons).'.';
        }

        return redirect()->route('manager.machine-schedule')->with('success', $summary);
    }

    private function validateSchedule(Request $request): array
    {
        $validated = $request->validate([
            'member_type' => ['required', Rule::in(['member', 'non-member'])],
            'user_id' => 'nullable|required_if:member_type,member|exists:users,id',
            'farmer_name' => 'nullable|required_if:member_type,non-member|string|max:255',
            'contact_number' => 'nullable|required_if:member_type,non-member|string|max:20',
            'machinery' => ['required', Rule::in($this->machineryNames())],
            'land_size' => 'required|numeric|min:0.1',
            'crop_id' => 'required|exists:crops,id',
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
