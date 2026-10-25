<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanBatch;
use App\Models\LoanRequest;
use App\Models\Notification;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LoanApprovalController extends Controller
{
    /**
     * Display loan requests the Manager has validated and forwarded for the
     * Administrator's final authorization. Batch loan requests are decided
     * on together, one batch at a time, and only once the batch has filled
     * up to capacity — a partially-filled batch is not sent for approval yet.
     */
    public function index(Request $request)
    {
        // One search box filters both lists: regular requests by farmer name,
        // batches by batch name ("Batch 3") or any member's name.
        $search = $request->filled('search') ? trim($request->string('search')) : null;
        $farmerNameMatches = fn ($q) => $q->whereRaw(
            "CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?",
            ["%{$search}%"]
        );

        $requests = LoanRequest::with('farmer')
            ->where('status', 'pending')
            ->where('type', 'regular')
            ->visibleAccount()
            ->when($search, fn ($q) => $q->whereHas('farmer', $farmerNameMatches))
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'requests_page')
            ->withQueryString();

        // For the summary card: every pending regular request, unaffected by search.
        $pendingRegularCount = LoanRequest::where('status', 'pending')
            ->where('type', 'regular')
            ->visibleAccount()
            ->count();

        $pendingActiveMember = fn ($q) => $q->where('status', 'pending')->whereNull('archived_at')->visibleAccount();
        $batchGroups = LoanBatch::whereHas('loanRequests', $pendingActiveMember)
            ->with(['loanRequests' => fn ($q) => $pendingActiveMember($q)->with(['farmer', 'requestedBy'])->orderBy('created_at', 'desc')])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn (LoanBatch $batch) => $batch->is_full)
            ->values();

        // The "Pending Requests" stat must count every batch member across
        // every batch, not just the ones on the current page — compute it
        // from the full set before paginating $batchGroups for display.
        $pendingBatchMemberCount = $batchGroups->sum(fn (LoanBatch $batch) => $batch->loanRequests->count());

        // Search narrows the batch list only after the stat above is counted,
        // so the summary cards always reflect everything awaiting approval.
        if ($search) {
            $needle = mb_strtolower($search);
            $batchGroups = $batchGroups->filter(fn (LoanBatch $batch) => str_contains(mb_strtolower($batch->label), $needle)
                || $batch->loanRequests->contains(fn ($member) => str_contains(mb_strtolower($member->farmer->full_name ?? ''), $needle))
            )->values();
        }

        // Two independent tables share this one page (regular requests,
        // batch groups), so each gets its own "page" query param.
        $batchGroups = $this->paginateCollection($batchGroups, $request, 'batches_page');

        $approvedThisMonth = LoanRequest::where('status', 'approved')
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->visibleAccount()
            ->count();

        $deniedThisMonth = LoanRequest::where('status', 'denied')
            ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->visibleAccount()
            ->count();

        return view('admin.loan-approval', compact('requests', 'batchGroups', 'pendingBatchMemberCount', 'pendingRegularCount', 'approvedThisMonth', 'deniedThisMonth'));
    }

    private function paginateCollection($items, Request $request, string $pageName, int $perPage = 10): LengthAwarePaginator
    {
        $page = (int) $request->input($pageName, 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => $pageName]
        );
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

        // By default only show loans still "in play" (not archived); archived
        // ones live in their own view ("View Archived", ?archived=1; old
        // ?status=archived links too).
        $showArchived = $request->boolean('archived') || $request->input('status') === 'archived';
        $matchesArchiveFilter = fn ($q) => $showArchived
            ? $q->whereNotNull('archived_at')
            : $q->whereNull('archived_at');

        $loans = LoanRequest::with('farmer')
            ->where('status', 'approved')
            ->where('type', 'regular')
            ->tap($matchesSearch)
            ->tap($matchesArchiveFilter)
            ->visibleAccount()
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'loans_page')
            ->withQueryString();

        $approvedMember = fn ($q) => $q->where('status', 'approved')->visibleAccount();

        $batchGroups = LoanBatch::whereHas('loanRequests', $approvedMember)
            ->with(['loanRequests' => fn ($q) => $approvedMember($q)->with('farmer')->tap($matchesSearch)->tap($matchesArchiveFilter)->orderByDesc('created_at')])
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (LoanBatch $batch) => $batch->loanRequests->isNotEmpty())
            ->values();

        // Same reasoning as index(): this must count every batch member
        // across every batch, not just the current page's.
        $approvedBatchMemberCount = $batchGroups->sum(fn (LoanBatch $batch) => $batch->loanRequests->count());

        $batchGroups = $this->paginateCollection($batchGroups, $request, 'batches_page');

        // Approved requests (regular and batch members alike) that were archived.
        $archivedCount = LoanRequest::where('status', 'approved')->whereNotNull('archived_at')->visibleAccount()->count();

        return view('admin.approved-loans', compact('loans', 'batchGroups', 'approvedBatchMemberCount', 'showArchived', 'archivedCount'));
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

        // Defense in depth: this should already be the farmer's only request
        // in process, but re-verify at the moment of approval too, in case
        // of a stale record from before the one-loan-at-a-time fix.
        $reason = $loan_request->farmer->loanInProcess($loan_request->id);
        abort_if($reason, 422, "This farmer already has a loan in process ({$reason}). Deny this request instead, or resolve the other one first.");

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
        ], [
            'denial_reason.required' => 'A reason is required to deny a loan request.',
        ]);

        $loan_request->update(['status' => 'denied', 'denial_reason' => $validated['denial_reason']]);

        // Permanent audit entry — denial_reason itself is a single mutable
        // column that gets overwritten if this request is ever denied again
        // after being resubmitted, so this is the one place the original
        // reason survives for good.
        ActivityLogger::log(Auth::user(), 'loan_request.denied', "Denied {$loan_request->farmer->full_name}'s loan request. Reason: {$validated['denial_reason']}", $loan_request);

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

        // Same defense-in-depth re-check as the single approve() — skip
        // (don't block the whole batch over) a member who already has
        // another loan in process, rather than silently approving them too.
        $toApprove = $pendingRequests->reject(fn (LoanRequest $r) => $r->farmer->loanInProcess($r->id));
        $skipped = $pendingRequests->diff($toApprove);

        abort_if($toApprove->isEmpty(), 422, 'None of this batch\'s members could be approved — they all already have a loan in process.');

        LoanRequest::whereIn('id', $toApprove->pluck('id'))->update(['status' => 'approved', 'denial_reason' => null]);

        foreach ($toApprove as $loan_request) {
            $this->notifyFarmerOfDecision($loan_request, 'loan_approved', 'Loan Request Approved', 'Your loan request for '.peso($loan_request->requested_amount)." has been approved as part of {$batch->label}. The Manager will finalize your loan terms soon.");
        }

        $message = "{$batch->label}'s {$toApprove->count()} loan requests have been approved.";

        if ($skipped->isNotEmpty()) {
            $message .= ' Skipped (already has a loan in process): '.$skipped->map(fn (LoanRequest $r) => $r->farmer->full_name)->implode(', ').'.';
        }

        return redirect()->route('admin.loan-approval')
            ->with('success', $message);
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
        ], [
            'denial_reason.required' => 'A reason is required to deny a loan request.',
        ]);

        LoanRequest::whereIn('id', $pendingRequests->pluck('id'))->update(['status' => 'denied', 'denial_reason' => $validated['denial_reason']]);

        foreach ($pendingRequests as $loan_request) {
            ActivityLogger::log(Auth::user(), 'loan_request.denied', "Denied {$loan_request->farmer->full_name}'s loan request as part of {$batch->label}. Reason: {$validated['denial_reason']}", $loan_request);

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
