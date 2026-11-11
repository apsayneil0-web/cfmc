<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Notification;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;

class ScheduleApprovalController extends Controller
{
    /**
     * Display schedule and reschedule requests awaiting Manager review.
     */
    public function index(Request $request)
    {
        $query = ScheduleRequest::with(['user.farmer', 'originalSchedule', 'crop', 'batchSiblings'])
            ->whereNull('archived_at')
            ->visibleAccount()
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'denied', 'completed')")
            // Pending requests are a queue, so they list oldest first (the
            // order they must be decided in); everything else newest first.
            ->orderByRaw("CASE WHEN status = 'pending' THEN created_at END ASC")
            ->orderByRaw("CASE WHEN status = 'pending' THEN id END ASC")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date('date'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('farmer_name', 'like', "{$search}%")
                    ->orWhere('farmer_name', 'like', "% {$search}%")
                    ->orWhere('machinery', 'like', "{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "{$search}%")
                        ->orWhere('name', 'like', "% {$search}%"));
            });
        }

        $requests = $query->paginate(10)->withQueryString();

        $nextInQueue = ScheduleRequest::nextInQueue();

        return view('manager.schedule-approval', compact('requests', 'nextInQueue'));
    }

    /**
     * First-in, first-out: refuse to decide a request while an earlier one
     * is still pending. Returns the redirect to send back, or null when
     * this request is the one at the front of the queue.
     */
    private function blockedByQueue(ScheduleRequest $schedule)
    {
        $next = ScheduleRequest::nextInQueue();

        if (! $next || $next->id === $schedule->id) {
            return null;
        }

        return redirect()->route('manager.schedule-approval')
            ->with('error', "Requests are processed in the order they were submitted. Approve or deny {$next->display_name}'s {$next->machinery} request (submitted ".$next->created_at->format('M d, Y g:i A').') first.');
    }

    /**
     * Approve a schedule (or reschedule) request. If this approval supersedes
     * an original schedule, that original is archived since it is no longer active.
     */
    public function approve(Request $request, ScheduleRequest $schedule)
    {
        abort_if($schedule->status !== 'pending', 422, 'Only pending requests can be approved.');

        if ($blocked = $this->blockedByQueue($schedule)) {
            return $blocked;
        }

        if (ScheduleRequest::hasConflict($schedule->machine_id, $schedule->scheduled_date->format('Y-m-d'), $schedule->start_time, $schedule->end_time, $schedule->id, $schedule->units_requested)) {
            return redirect()->route('manager.schedule-approval')
                ->with('error', 'Cannot approve: this machinery is already booked for an overlapping date/time.');
        }

        // A pending request doesn't hold hectare capacity against competing
        // requests (see ScheduleRequest::reservedHectares()), so two farmers
        // can both have a pending request for the same machine/date that
        // together exceed its daily limit — this is the point that catches it.
        $dailyLimit = $schedule->machine?->effective_daily_limit ?? Machine::DEFAULT_DAILY_HECTARE_LIMIT;

        if (ScheduleRequest::wouldExceedDailyCapacity($schedule->machine_id, $schedule->scheduled_date->format('Y-m-d'), (float) $schedule->land_size, $dailyLimit, $schedule->id)) {
            $remaining = ScheduleRequest::remainingCapacity($schedule->machine_id, $schedule->scheduled_date->format('Y-m-d'), $dailyLimit, $schedule->id);

            return redirect()->route('manager.schedule-approval')
                ->with('error', "Cannot approve: {$schedule->machinery} has reached its {$dailyLimit} hectare daily limit for this date. Only {$remaining} hectare(s) remaining.");
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

        if ($schedule->user_id) {
            Notification::notify(
                $schedule->user_id,
                'Schedule Approved',
                "Your {$schedule->machinery} schedule for ".$schedule->scheduled_date->format('M d, Y').' has been approved.',
                'schedule_approved',
                ['schedule_id' => $schedule->id],
            );
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

        if ($blocked = $this->blockedByQueue($schedule)) {
            return $blocked;
        }

        $validated = $request->validate([
            'denial_reason' => 'required|string|max:1000',
        ], [
            'denial_reason.required' => 'A reason is required to deny a schedule request.',
        ]);

        $schedule->update([
            'status' => 'denied',
            'denial_reason' => $validated['denial_reason'],
        ]);

        if ($schedule->user_id) {
            Notification::notify(
                $schedule->user_id,
                'Schedule Denied',
                "Your {$schedule->machinery} schedule request has been denied. Reason: {$validated['denial_reason']}",
                'schedule_denied',
                ['schedule_id' => $schedule->id],
            );
        }

        return redirect()->route('manager.schedule-approval')
            ->with('success', "Schedule for {$schedule->display_name} has been denied.");
    }
}
