<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
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
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $complaints = $query->get();

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
            'manager_response' => 'nullable|string|max:2000',
        ]);

        $complaint->update([
            'status' => $validated['status'],
            'manager_response' => $validated['manager_response'] ?? $complaint->manager_response,
        ]);

        return redirect()->route('manager.complaints')->with('success', "Complaint \"{$complaint->subject}\" updated.");
    }
}
