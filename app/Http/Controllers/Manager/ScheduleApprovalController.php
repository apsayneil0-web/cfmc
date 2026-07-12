<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;

class ScheduleApprovalController extends Controller
{
    /**
     * Display schedule and reschedule requests awaiting Manager review.
     */
    public function index(Request $request)
    {
        $query = ScheduleRequest::with(['user.farmer', 'originalSchedule'])
            ->whereNull('archived_at')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'denied', 'completed')")
            ->orderBy('scheduled_date');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date('date'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('farmer_name', 'like', "%{$search}%")
                    ->orWhere('machinery', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->get();

        return view('manager.schedule-approval', compact('requests'));
    }

    /**
     * Approve a schedule (or reschedule) request. If this approval supersedes
     * an original schedule, that original is archived since it is no longer active.
     */
    public function approve(Request $request, ScheduleRequest $schedule)
    {
        abort_if($schedule->status !== 'pending', 422, 'Only pending requests can be approved.');

        if (ScheduleRequest::hasConflict($schedule->machinery, $schedule->scheduled_date->format('Y-m-d'), $schedule->start_time, $schedule->end_time, $schedule->id)) {
            return redirect()->route('manager.schedule-approval')
                ->with('error', 'Cannot approve: this machinery is already booked for an overlapping date/time.');
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        $schedule->update([
            'status' => 'approved',
            'remarks' => $validated['remarks'] ?? $schedule->remarks,
            'denial_reason' => null,
        ]);

        if ($schedule->is_reschedule && $schedule->original_schedule_id) {
            $schedule->originalSchedule?->update(['archived_at' => now()]);
        }

        return redirect()->route('manager.schedule-approval')
            ->with('success', "Schedule for {$schedule->display_name} has been approved.");
    }

    /**
     * Deny a schedule (or reschedule) request, requiring a documented reason.
     */
    public function deny(Request $request, ScheduleRequest $schedule)
    {
        abort_if($schedule->status !== 'pending', 422, 'Only pending requests can be denied.');

        $validated = $request->validate([
            'denial_reason' => 'required|string|max:1000',
        ]);

        $schedule->update([
            'status' => 'denied',
            'denial_reason' => $validated['denial_reason'],
        ]);

        return redirect()->route('manager.schedule-approval')
            ->with('success', "Schedule for {$schedule->display_name} has been denied.");
    }
}
