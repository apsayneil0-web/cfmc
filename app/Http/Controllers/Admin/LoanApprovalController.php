<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanBatch;
use App\Models\LoanRequest;
use App\Models\Notification;
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
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingActiveMember = fn ($q) => $q->where('status', 'pending')->whereNull('archived_at');
        $batchGroups = LoanBatch::whereHas('loanRequests', $pendingActiveMember)
            ->with(['loanRequests' => fn ($q) => $pendingActiveMember($q)->with(['farmer', 'requestedBy'])->orderBy('created_at', 'desc')])
            ->orderBy('created_at', 'desc')
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
     * Display every loan request the Administrator has approved. Batch
     * members are folded into one row per batch (mirroring the pending
     * Loan Approval page) instead of flooding the table with every
     * individual member, since a batch can hold up to 10 farmers.
     */
    public function approved(Request $request)
    {
        $matchesSearch = function ($q) use ($request) {
            if (! $request->filled('search')) {
                return;
            }

            $search = $request->string('search');

            $q->whereHas('farmer', function ($q) use ($search) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ["{$search}%"])
                    ->orWhere('last_name', 'like', "{$search}%");
            });
        };

        // By default only show loans still "in play" (not archived); the
        // manager explicitly filters for "Archived" to review old ones.
        $matchesArchiveFilter = fn ($q) => $request->input('status') === 'archived'
            ? $q->whereNotNull('archived_at')
            : $q->whereNull('archived_at');

        $loans = LoanRequest::with('farmer')
            ->where('status', 'approved')
            ->where('type', 'regular')
            ->tap($matchesSearch)
            ->tap($matchesArchiveFilter)
            ->orderByDesc('created_at')
            ->get();

        $approvedMember = fn ($q) => $q->where('status', 'approved');

        $batchGroups = LoanBatch::whereHas('loanRequests', $approvedMember)
            ->with(['loanRequests' => fn ($q) => $approvedMember($q)->with('farmer')->tap($matchesSearch)->tap($matchesArchiveFilter)->orderByDesc('created_at')])
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (LoanBatch $batch) => $batch->loanRequests->isNotEmpty())
            ->values();

        return view('admin.approved-loans', compact('loans', 'batchGroups'));
    }

    /**
     * Archive an approved loan request, hiding it from the default view
     * without touching its approval status or any finalized loan record.
     */
    public function archive(LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'approved', 422, 'Only approved requests can be archived.');

        $loan_request->update(['archived_at' => now()]);

        return redirect()->route('admin.approved-loans')
            ->with('success', "{$loan_request->farmer->full_name}'s loan request has been archived.");
    }

    /**
     * Restore an archived loan request back into the default view.
     */
    public function unarchive(LoanRequest $loan_request)
    {
        $loan_request->update(['archived_at' => null]);

        return redirect()->route('admin.approved-loans', ['status' => 'archived'])
            ->with('success', "{$loan_request->farmer->full_name}'s loan request has been restored.");
    }

    /**
     * Archive every approved member of a batch at once.
     */
    public function archiveBatch(LoanBatch $batch)
    {
        $count = $batch->loanRequests()->where('status', 'approved')->whereNull('archived_at')->count();

        abort_if($count === 0, 422, 'This batch has no approved members left to archive.');

        $batch->loanRequests()->where('status', 'approved')->whereNull('archived_at')->update(['archived_at' => now()]);

        return redirect()->route('admin.approved-loans')
            ->with('success', "{$batch->label}'s {$count} approved loan request(s) have been archived.");
    }

    /**
     * Restore every archived member of a batch at once.
     */
    public function unarchiveBatch(LoanBatch $batch)
    {
        $count = $batch->loanRequests()->where('status', 'approved')->whereNotNull('archived_at')->count();

        abort_if($count === 0, 422, 'This batch has no archived members left to restore.');

        $batch->loanRequests()->where('status', 'approved')->whereNotNull('archived_at')->update(['archived_at' => null]);

        return redirect()->route('admin.approved-loans', ['status' => 'archived'])
            ->with('success', "{$batch->label}'s {$count} approved loan request(s) have been restored.");
    }

    /**
     * Grant final authorization for a loan request.
     */
    public function approve(LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'pending', 422, 'Only pending requests can be approved.');

        $loan_request->update(['status' => 'approved', 'denial_reason' => null]);

        $this->notifyFarmerOfDecision($loan_request, 'loan_approved', 'Loan Request Approved', 'Your loan request for '.peso($loan_request->requested_amount).' has been approved. The Manager will finalize your loan terms soon.');

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

        $this->notifyFarmerOfDecision($loan_request, 'loan_denied', 'Loan Request Denied', 'Your loan request for '.peso($loan_request->requested_amount).' has been denied. Reason: '.$validated['denial_reason']);

        return redirect()->route('admin.loan-approval')
            ->with('success', "{$loan_request->farmer->full_name}'s loan request has been denied.");
    }

    /**
     * Grant final authorization for every pending member of a batch at once.
     */
    public function approveBatch(LoanBatch $batch)
    {
        abort_unless($batch->is_full, 422, "{$batch->label} is not full yet and cannot be sent for approval.");

        $pendingRequests = $batch->loanRequests()->where('status', 'pending')->whereNull('archived_at')->with('farmer')->get();

        abort_if($pendingRequests->isEmpty(), 422, 'This batch has no pending requests to approve.');

        LoanRequest::whereIn('id', $pendingRequests->pluck('id'))->update(['status' => 'approved', 'denial_reason' => null]);

        foreach ($pendingRequests as $loan_request) {
            $this->notifyFarmerOfDecision($loan_request, 'loan_approved', 'Loan Request Approved', 'Your loan request for '.peso($loan_request->requested_amount)." has been approved as part of {$batch->label}. The Manager will finalize your loan terms soon.");
        }

        return redirect()->route('admin.loan-approval')
            ->with('success', "{$batch->label}'s {$pendingRequests->count()} loan requests have been approved.");
    }

    /**
     * Deny every pending member of a batch at once, requiring a documented reason.
     */
    public function denyBatch(Request $request, LoanBatch $batch)
    {
        abort_unless($batch->is_full, 422, "{$batch->label} is not full yet and cannot be sent for approval.");

        $pendingRequests = $batch->loanRequests()->where('status', 'pending')->whereNull('archived_at')->with('farmer')->get();

        abort_if($pendingRequests->isEmpty(), 422, 'This batch has no pending requests to deny.');

        $validated = $request->validate([
            'denial_reason' => 'required|string|max:1000',
        ]);

        LoanRequest::whereIn('id', $pendingRequests->pluck('id'))->update(['status' => 'denied', 'denial_reason' => $validated['denial_reason']]);

        foreach ($pendingRequests as $loan_request) {
            $this->notifyFarmerOfDecision($loan_request, 'loan_denied', 'Loan Request Denied', 'Your loan request for '.peso($loan_request->requested_amount)." as part of {$batch->label} has been denied. Reason: ".$validated['denial_reason']);
        }

        return redirect()->route('admin.loan-approval')
            ->with('success', "{$batch->label}'s {$pendingRequests->count()} loan requests have been denied.");
    }

    /**
     * Notify a loan request's farmer of an Admin decision, if they have a
     * login account (farmers without one yet can't receive in-app notices).
     */
    private function notifyFarmerOfDecision(LoanRequest $loan_request, string $type, string $title, string $message): void
    {
        if ($loan_request->farmer->account_user_id) {
            Notification::notify($loan_request->farmer->account_user_id, $title, $message, $type);
        }
    }
}
