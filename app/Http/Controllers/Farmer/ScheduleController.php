<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $machines = Machine::with('crops')->whereNull('archived_at')->orderBy('created_at', 'desc')->get();
        $machinery = $machines->map(fn (Machine $m) => [
            'name' => $m->name,
            'status' => $m->isOutOfService() ? 'Unavailable' : 'Available',
            'quantity' => max(1, (int) $m->quantity),
            'requires_crop' => $m->requires_crop,
            // Empty = works with every crop.
            'crop_ids' => $m->requires_crop ? $m->crops->pluck('id')->all() : [],
        ])->all();
        $machineryList = $machines->pluck('name')->all();
        $crops = Crop::orderBy('name')->get();

        $requests = ScheduleRequest::with(['crop', 'batchSiblings'])
            ->where('user_id', Auth::id())
            ->whereNull('archived_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $month = $request->filled('month') ? Carbon::parse($request->string('month').'-01') : now()->startOfMonth();
        $machineryFilter = $request->string('calendar_machinery')->toString();

        $calendarDays = ScheduleRequest::calendarForMonth($month, $machineryFilter ?: null);
        $availableDays = ScheduleRequest::availabilityByDay($month);

        $selectedMonth = $month;
        $firstWeekday = $month->copy()->startOfMonth()->dayOfWeek;
        $daysInMonth = $month->daysInMonth;
        $monthOptions = collect(range(0, 3))->map(fn ($offset) => now()->startOfMonth()->addMonths($offset));

        return view('farmer.schedule', compact(
            'machinery', 'requests', 'machineryList', 'crops', 'calendarDays', 'selectedMonth',
            'firstWeekday', 'daysInMonth', 'monthOptions', 'machineryFilter', 'availableDays'
        ));
    }

    /**
     * Per-machine availability for one date (and optional time window), so
     * the request form can show "2.5 ha left" / "Fully booked" before the
     * farmer submits. Uses the same capacity helpers store() enforces.
     */
    public function availability(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ]);

        $date = $validated['date'];
        $startTime = $validated['start_time'] ?? null;
        $endTime = $validated['end_time'] ?? null;
        $hasWindow = $startTime && $endTime;

        $machines = Machine::whereNull('archived_at')->get()->map(function (Machine $machine) use ($date, $startTime, $endTime, $hasWindow) {
            if ($machine->isOutOfService()) {
                return ['name' => $machine->name, 'status' => 'unavailable', 'hectares_left' => 0, 'units_free' => 0, 'requires_crop' => $machine->requires_crop];
            }

            $unitsFree = $hasWindow
                ? max(0, ScheduleRequest::unitsFree($machine->id, $date, $startTime, $endTime))
                : null;

            // A general-use machine has no hectare limit; only an
            // overlapping time slot can make it unavailable.
            if (! $machine->requires_crop) {
                return [
                    'name' => $machine->name,
                    'status' => $unitsFree === 0 ? 'booked' : 'available',
                    'hectares_left' => null,
                    'units_free' => $unitsFree,
                    'requires_crop' => false,
                ];
            }

            $hectaresLeft = ScheduleRequest::remainingCapacity($machine->id, $date, $machine->effective_daily_limit);

            return [
                'name' => $machine->name,
                'status' => $hectaresLeft <= 0 || $unitsFree === 0 ? 'booked' : 'available',
                'hectares_left' => round($hectaresLeft, 2),
                'units_free' => $unitsFree,
                'requires_crop' => true,
            ];
        })->values();

        return response()->json(['date' => $date, 'machines' => $machines]);
    }

    /**
     * Submit one or more machines in a single request (e.g. Tractor + Seeder
     * for the same job). Each machine still gets its own ScheduleRequest row
     * — own date/time, own conflict and capacity checks, own approval — but
     * rows from one submission share a batch_id when there's more than one,
     * and either all of them are created or none are (a transaction, so a
     * later machine's conflict/capacity failure rolls back earlier ones too
     * rather than leaving a half-submitted batch).
     */
    public function store(Request $request)
    {
        $request->validate([
            'location' => 'required|string|max:255',
            'machines' => 'required|array|min:1',
        ]);

        $machinesByName = Machine::with('crops')->whereNull('archived_at')->get()->keyBy('name');
        $machineryNames = $machinesByName->keys();

        // Pass 1: validate every block's own fields up front, so the farmer
        // sees every problem at once instead of fixing one row at a time.
        $blocks = [];
        $errors = [];

        $cropIds = Crop::pluck('id')->all();

        foreach ($request->input('machines') as $index => $block) {
            $block['purpose'] = $block['purpose'] ?? 'crop';

            $validator = Validator::make($block, [
                'purpose' => ['required', Rule::in(['crop', 'borrow'])],
                'machinery' => ['required', 'string', Rule::in($machineryNames)],
                'land_size' => 'nullable|required_if:purpose,crop|numeric|min:0.1',
                'units_requested' => 'nullable|integer|min:1',
                'crop_id' => ['nullable', 'required_if:purpose,crop', Rule::in(array_merge($cropIds, [Crop::OTHER_OPTION]))],
                'new_crop_name' => 'nullable|required_if:crop_id,'.Crop::OTHER_OPTION.'|string|max:255',
                'scheduled_date' => ['required', 'date', 'after_or_equal:'.ScheduleRequest::earliestAllowedDate()->toDateString()],
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
            ], [
                'scheduled_date.after_or_equal' => 'the date must be at least '.ScheduleRequest::MIN_LEAD_DAYS.' days from today.',
                'land_size.required_if' => 'the land size is required when requesting a machine for a crop.',
                'crop_id.required_if' => 'choose the crop this machine is for.',
                'machinery.required' => 'choose a machine.',
            ]);

            if ($validator->fails()) {
                $errors[] = 'Machine #'.($index + 1).': '.$validator->errors()->first();

                continue;
            }

            $validated = $validator->validated();
            $isBorrow = $validated['purpose'] === 'borrow';
            $isNewCrop = false;
            $cropId = null;

            if (! $isBorrow) {
                if ($validated['crop_id'] === Crop::OTHER_OPTION) {
                    $existing = Crop::findByName($validated['new_crop_name']);
                    $isNewCrop = ! $existing;
                    $cropId = $existing?->id;
                } else {
                    $cropId = (int) $validated['crop_id'];
                }
            }

            $mismatch = $machinesByName[$validated['machinery']]->purposeMismatch($validated['purpose'], $cropId, $isNewCrop);

            if ($mismatch) {
                $errors[] = 'Machine #'.($index + 1).': '.$mismatch;

                continue;
            }

            // A borrow-only request carries no crop and no land size.
            $validated['crop_id'] = $isBorrow ? null : ($cropId ?? Crop::OTHER_OPTION);
            $validated['land_size'] = $isBorrow ? null : $validated['land_size'];

            $blocks[$index] = $validated;
        }

        if (! empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        // Only now that every row is valid, create any crop typed in under
        // "+ Other" — a rejected submission shouldn't leave new crops behind.
        foreach ($blocks as $index => $validated) {
            if ($validated['crop_id'] === Crop::OTHER_OPTION) {
                $blocks[$index]['crop_id'] = Crop::findOrCreateByName($validated['new_crop_name'])->id;
            }
        }

        // Pass 2: conflict/capacity checks + creation, one block at a time
        // inside a transaction — a later block's checks see earlier blocks'
        // freshly-created rows, so two machines requested at an overlapping
        // slot within the same batch are still caught, not just conflicts
        // against bookings that already existed before this submission.
        $farmer = Auth::user()->farmer;
        $batchId = count($blocks) > 1 ? (string) Str::uuid() : null;
        $location = $request->input('location');
        $created = 0;
        $failure = null;

        try {
            DB::transaction(function () use ($blocks, $farmer, $batchId, $location, &$created) {
                foreach ($blocks as $index => $validated) {
                    $machine = Machine::whereNull('archived_at')->where('name', $validated['machinery'])->first();

                    if (! $machine) {
                        throw new \RuntimeException('Machine #'.($index + 1).': that machinery is no longer available.');
                    }

                    $unitsRequested = min(max(1, (int) ($validated['units_requested'] ?? 1)), max(1, (int) $machine->quantity));

                    if (ScheduleRequest::hasConflict($machine->id, $validated['scheduled_date'], $validated['start_time'], $validated['end_time'], null, $unitsRequested)) {
                        throw new \RuntimeException('Machine #'.($index + 1).": not enough {$machine->name} units are free for that date/time.");
                    }

                    $dailyLimit = $machine->effective_daily_limit;

                    if ($validated['purpose'] !== 'borrow' && ScheduleRequest::wouldExceedDailyCapacity($machine->id, $validated['scheduled_date'], (float) $validated['land_size'], $dailyLimit)) {
                        $remaining = ScheduleRequest::remainingCapacity($machine->id, $validated['scheduled_date'], $dailyLimit);
                        throw new \RuntimeException('Machine #'.($index + 1).": {$machine->name} has reached its {$dailyLimit} hectare daily limit for this date. Only {$remaining} hectare(s) remaining.");
                    }

                    ScheduleRequest::create([
                        'batch_id' => $batchId,
                        'user_id' => Auth::id(),
                        'farmer_name' => $farmer?->full_name ?? Auth::user()->name,
                        'member_type' => $farmer && $farmer->status === 'approved' ? 'member' : 'non-member',
                        'machinery' => $machine->name,
                        'machine_id' => $machine->id,
                        'purpose' => $validated['purpose'],
                        'land_size' => $validated['land_size'],
                        'units_requested' => $unitsRequested,
                        'crop_id' => $validated['crop_id'],
                        'scheduled_date' => $validated['scheduled_date'],
                        'start_time' => $validated['start_time'],
                        'end_time' => $validated['end_time'],
                        'location' => $location,
                        'status' => 'pending',
                    ]);

                    $created++;
                }
            });
        } catch (\RuntimeException $e) {
            $failure = $e->getMessage();
        }

        if ($failure) {
            return back()->withErrors(['machines' => $failure])->withInput();
        }

        $message = $created > 1
            ? "{$created} schedule requests submitted successfully!"
            : 'Schedule request submitted successfully!';

        return redirect()->route('farmer.schedule')->with('success', $message);
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

        if (! $schedule->isBorrowOnly() && ScheduleRequest::wouldExceedDailyCapacity($schedule->machine_id, $validated['scheduled_date'], (float) $schedule->land_size, $dailyLimit, $schedule->id)) {
            $remaining = ScheduleRequest::remainingCapacity($schedule->machine_id, $validated['scheduled_date'], $dailyLimit, $schedule->id);
            return back()->withErrors(['scheduled_date' => "{$schedule->machinery} has reached its {$dailyLimit} hectare daily limit for this date. Only {$remaining} hectare(s) remaining."])->withInput();
        }

        ScheduleRequest::create([
            'user_id' => $schedule->user_id,
            'farmer_name' => $schedule->farmer_name,
            'member_type' => $schedule->member_type,
            'machinery' => $schedule->machinery,
            'machine_id' => $schedule->machine_id,
            'purpose' => $schedule->purpose,
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
}
