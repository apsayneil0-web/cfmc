<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Notification;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Display complaints submitted for manager review (drafts stay private to the farmer).
     */
    public function index(Request $request)
    {
        $query = Complaint::with('user')
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "{$search}%")
                        ->orWhere('name', 'like', "% {$search}%"));
            });
        }

        $complaints = $query->paginate(10)->withQueryString();

        $counts = [
            'total' => Complaint::where('status', '!=', 'draft')->count(),
            'submitted' => Complaint::where('status', 'submitted')->count(),
            'in_progress' => Complaint::where('status', 'in_progress')->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
        ];

        return view('manager.complaints', compact('complaints', 'counts'));
    }

    /**
     * Update a complaint's status and record the manager's response.
     */
    public function respond(Request $request, Complaint $complaint)
    {
        abort_if($complaint->status === 'draft', 422, 'Draft complaints are not yet visible to managers.');

        $validated = $request->validate([
            'status' => 'required|in:in_progress,resolved',
            'manager_response' => 'required|string|max:2000',
        ]);

        $complaint->update([
            'status' => $validated['status'],
            'manager_response' => $validated['manager_response'],
        ]);

        $statusLabel = $validated['status'] === 'resolved' ? 'resolved' : 'marked in progress';
        $message = "Your complaint \"{$complaint->subject}\" has been {$statusLabel}.";

        $message .= " Response: {$validated['manager_response']}";

        Notification::notify($complaint->user_id, 'Complaint Update', $message, 'complaint_response');

        return redirect()->route('manager.complaints')->with('success', "Complaint \"{$complaint->subject}\" updated.");
    }

    /**
     * Mark a complaint as viewed so the farmer knows it's been seen, even
     * before the manager has decided on a status/response yet.
     */
    public function markViewed(Complaint $complaint)
    {
        if (! $complaint->viewed_at) {
            $complaint->update(['viewed_at' => now()]);

            Notification::notify(
                $complaint->user_id,
                'Complaint Viewed',
                "Your complaint \"{$complaint->subject}\" has been viewed by the manager.",
                'complaint_viewed',
            );
        }

        return redirect()->route('manager.complaints')->with('success', "Complaint \"{$complaint->subject}\" marked as viewed.");
    }
}
