<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanBatch;
use App\Models\LoanRequest;
use Illuminate\Http\Request;

class LoanApprovalController extends Controller
{
    /**
     * Display loan requests the Manager has validated and forwarded for the
     * Administrator's final authorization. Batch loan requests are decided
     * on together, one batch at a time, and only once the batch has filled
     * up to capacity — a partially-filled batch is not sent for approval yet.
     */
    public function index()
    {
        $requests = LoanRequest::with('farmer')
            ->where('status', 'pending')
            ->where('type', 'regular')
            ->orderBy('created_at')
            ->get();

        $batchGroups = LoanBatch::whereHas('loanRequests', fn ($q) => $q->where('status', 'pending'))
            ->with(['loanRequests' => fn ($q) => $q->where('status', 'pending')->with('farmer')->orderBy('created_at')])
            ->orderBy('batch_number')
            ->get()
            ->filter(fn (LoanBatch $batch) => $batch->is_full)
            ->values();

        $approvedThisMonth = LoanRequest::where('status', 'approved')
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $deniedThisMonth = LoanRequest::where('status', 'denied')
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return view('admin.loan-approval', compact('requests', 'batchGroups', 'approvedThisMonth', 'deniedThisMonth'));
    }

    /**
     * Grant final authorization for a loan request.
     */
    public function approve(LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'pending', 422, 'Only pending requests can be approved.');

        $loan_request->update(['status' => 'approved', 'denial_reason' => null]);

        return redirect()->route('admin.loan-approval')
            ->with('success', "{$loan_request->farmer->full_name}'s loan request has been approved.");
    }

    /**
     * Deny a loan request, requiring a documented reason.
     */
    public function deny(Request $request, LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'pending', 422, 'Only pending requests can be denied.');

        $validated = $request->validate([
            'denial_reason' => 'required|string|max:1000',
        ]);

        $loan_request->update(['status' => 'denied', 'denial_reason' => $validated['denial_reason']]);

        return redirect()->route('admin.loan-approval')
            ->with('success', "{$loan_request->farmer->full_name}'s loan request has been denied.");
    }

    /**
     * Grant final authorization for every pending member of a batch at once.
     */
    public function approveBatch(LoanBatch $batch)
    {
        abort_unless($batch->is_full, 422, "{$batch->label} is not full yet and cannot be sent for approval.");

        $pendingIds = $batch->loanRequests()->where('status', 'pending')->pluck('id');

        abort_if($pendingIds->isEmpty(), 422, 'This batch has no pending requests to approve.');

        LoanRequest::whereIn('id', $pendingIds)->update(['status' => 'approved', 'denial_reason' => null]);

        return redirect()->route('admin.loan-approval')
            ->with('success', "{$batch->label}'s {$pendingIds->count()} loan requests have been approved.");
    }

    /**
     * Deny every pending member of a batch at once, requiring a documented reason.
     */
    public function denyBatch(Request $request, LoanBatch $batch)
    {
        abort_unless($batch->is_full, 422, "{$batch->label} is not full yet and cannot be sent for approval.");

        $pendingIds = $batch->loanRequests()->where('status', 'pending')->pluck('id');

        abort_if($pendingIds->isEmpty(), 422, 'This batch has no pending requests to deny.');

        $validated = $request->validate([
            'denial_reason' => 'required|string|max:1000',
        ]);

        LoanRequest::whereIn('id', $pendingIds)->update(['status' => 'denied', 'denial_reason' => $validated['denial_reason']]);

        return redirect()->route('admin.loan-approval')
            ->with('success', "{$batch->label}'s {$pendingIds->count()} loan requests have been denied.");
    }
}
