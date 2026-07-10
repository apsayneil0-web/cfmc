<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use Illuminate\Http\Request;

class MembershipApprovalController extends Controller
{
    /**
     * Display pending membership applications awaiting approval.
     */
    public function index()
    {
        $applications = Farmer::with('crop')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        return view('admin.membership-approval', compact('applications'));
    }

    /**
     * Approve a membership application.
     */
    public function approve(Farmer $farmer)
    {
        $farmer->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return redirect()->route('admin.membership-approval')
            ->with('success', "{$farmer->full_name}'s membership application has been approved.");
    }

    /**
     * Reject a membership application.
     */
    public function reject(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $farmer->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('admin.membership-approval')
            ->with('success', "{$farmer->full_name}'s membership application has been rejected.");
    }
}
