<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\LoanAppointment;
use App\Models\LoanBatch;
use App\Models\LoanRequest;
use App\Models\Notification;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LoanRequestController extends Controller
{
    private const PURPOSES = ['Farming Equipment', 'Seeds & Fertilizer', 'Machinery Purchase', 'Working Capital'];
    private const TERMS = [6, 12, 18, 24];
    private const TYPES = ['regular', 'batch'];

    /**
     * Production loan schedule for batch loans: the maximum amount a farmer
     * may borrow and the minimum CBU balance required, per hectare owned.
     */
    private const PRODUCTION_LOAN_BRACKETS = [
        1 => ['max_loanable' => 32000, 'min_cbu' => 4000],
        2 => ['max_loanable' => 64000, 'min_cbu' => 8000],
        3 => ['max_loanable' => 96000, 'min_cbu' => 12000],
    ];

    /**
     * Display all loan requests awaiting or past decision.
     */
    public function index(Request $request)
    {
        $query = LoanRequest::with(['farmer', 'requestedBy', 'loan', 'batch', 'activityLogs'])->visibleAccount();

        // Archived requests live in their own view ("View Archived",
        // ?archived=1; old ?status=archived links too). Only denied requests
        // can be archived; pending batch members removed from a batch also
        // carry archived_at but are not archived records, so they stay out.
        $showArchived = $request->boolean('archived') || $request->input('status') === 'archived';

        if ($showArchived) {
            $query->whereNotNull('archived_at')->where('status', 'denied');
        } else {
            $query->whereNull('archived_at');

            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('farmer', function ($q) use ($search) {
                $q->where('first_name', 'like', "{$search}%")
                    ->orWhere('last_name', 'like', "{$search}%");
            });
        }

        $requests = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'denied', 'archived')")
            ->orderBy('created_at', 'desc')
            ->get();

        // Fold every batch's requests into one row on the main table (Regular
        // requests stay listed individually) — same pattern used on the
        // Admin Loan Approval / Approved Loans pages. $requests itself stays
        // the full, unpaginated collection: the View/Edit/Archive modals
        // below are rendered once per request (regular and batch members
        // alike) and reached via switchModal() from either table, so every
        // request needs a modal regardless of which page it's displayed on.
        $regularRequests = $requests->where('type', '!=', 'batch')->values();
        $requestBatchGroups = $requests->where('type', 'batch')
            ->groupBy('batch_id')
            ->map(fn ($members) => (object) [
                'batch' => $members->first()->batch,
                'members' => $members->values(),
            ])
            ->sortByDesc(fn ($group) => $group->members->max('created_at'))
            ->values();

        // Two independent tables share this one page (batch groups, regular
        // requests), so each gets its own "page" query param.
        $requestBatchGroups = $this->paginateCollection($requestBatchGroups, $request, 'batches_page');
        $regularRequests = $this->paginateCollection($regularRequests, $request, 'regular_page');

        $farmers = Farmer::where('status', 'approved')
            ->whereDoesntHave('account', fn ($q) => $q->where('status', 'inactive'))
            ->orderBy('created_at', 'desc')
            ->get();
        $batches = LoanBatch::orderBy('created_at', 'desc')->get();

        // Batches still collecting members: not yet full, so not yet forwarded
        // to the Administrator. These are the only ones a manager can edit.
        $pendingActiveMember = fn ($q) => $q->where('status', 'pending')->whereNull('archived_at')->visibleAccount();
        $batchesInProgress = LoanBatch::whereHas('loanRequests', $pendingActiveMember)
            ->with(['loanRequests' => fn ($q) => $pendingActiveMember($q)->with('farmer')->orderBy('created_at', 'desc')])
            ->orderBy('created_at', 'desc')
            ->get()
            ->reject(fn (LoanBatch $batch) => $batch->is_full)
            ->values();

        // Single source of truth (Farmer::loanInProcess()) so this list can
        // never drift out of sync with what validateRequest() actually
        // enforces on submit — that drift (this list not knowing about
        // approved-but-unfinalized requests) was the one-loan-at-a-time bug.
        $farmersIneligibleForNewRequest = [];
        foreach ($farmers as $farmer) {
            $reason = $farmer->loanInProcess();

            if ($reason) {
                $farmersIneligibleForNewRequest[$farmer->id] = $reason;
            }
        }

        // Batches the Administrator has approved but that haven't been
        // finalized into loans yet — finalized one decision at a time per
        // batch, same as approval, rather than clicking "Finalize" 10 times.
        $approvedUnfinalizedMember = fn ($q) => $q->where('status', 'approved')->whereNull('archived_at')->whereDoesntHave('loan')->visibleAccount();
        $batchesReadyToFinalize = LoanBatch::whereHas('loanRequests', $approvedUnfinalizedMember)
            ->with(['loanRequests' => fn ($q) => $approvedUnfinalizedMember($q)->with('farmer')->orderBy('created_at', 'desc')])
            ->orderBy('created_at', 'desc')
            ->get();

        // Full batches still awaiting the Administrator's approve/deny
        // decision — read-only visibility only (mirrors
        // LoanApprovalController::index()'s own query exactly); the Manager
        // can see this stage now without switching roles, but the decision
        // itself still only happens on the Admin's Loan Approval page.
        $batchesWithAdministrator = LoanBatch::whereHas('loanRequests', $pendingActiveMember)
            ->with(['loanRequests' => fn ($q) => $pendingActiveMember($q)->with('farmer')->orderBy('created_at', 'desc')])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn (LoanBatch $batch) => $batch->is_full)
            ->values();

        // Batches already turned into loans — a lightweight summary only,
        // linking out to Batch Loans (manager.batch-loan-management), which
        // stays the one place their repayment is actually tracked/managed.
        $batchesFinalized = LoanBatch::whereHas('loanRequests.loan')
            ->with(['loanRequests.loan'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (LoanBatch $batch) => (object) [
                'batch' => $batch,
                'loan_count' => $batch->loanRequests->filter(fn ($r) => $r->loan)->count(),
                'total_principal' => $batch->loanRequests->sum(fn ($r) => $r->loan->principal_amount ?? 0),
                'total_outstanding' => $batch->loanRequests->sum(fn ($r) => $r->loan->remaining_balance ?? 0),
            ])
            ->values();

        // Regular / Batch view switch. Remembered in the session so batch
        // actions (which redirect to the plain page) land back on Batch.
        if (in_array($request->input('mode'), ['regular', 'batch'], true)) {
            $request->session()->put('loan_request_mode', $request->input('mode'));
        }
        $mode = $request->session()->get('loan_request_mode', 'regular');

        // Status tab counts for regular requests, independent of the active filter.
        $regularCounts = LoanRequest::whereNull('archived_at')
            ->where('type', '!=', 'batch')
            ->visibleAccount()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // View/Edit modals are rendered per request. Members of batches still
        // collecting are always included, so Manage Batch's Edit button works
        // even when a status/search filter has narrowed $requests.
        $modalRequests = $requests
            ->concat($batchesInProgress->flatMap(fn (LoanBatch $batch) => $batch->loanRequests))
            ->unique('id')
            ->values();
        $modalRequests->loadMissing(['farmer', 'requestedBy', 'loan', 'batch', 'activityLogs']);

        return view('manager.loan-request', [
            'mode' => $mode,
            'modalRequests' => $modalRequests,
            'regularCounts' => $regularCounts,
            'requests' => $requests,
            'regularRequests' => $regularRequests,
            'requestBatchGroups' => $requestBatchGroups,
            'farmers' => $farmers,
            'batches' => $batches,
            'batchesInProgress' => $batchesInProgress,
            'batchesWithAdministrator' => $batchesWithAdministrator,
            'batchesReadyToFinalize' => $batchesReadyToFinalize,
            'batchesFinalized' => $batchesFinalized,
            'purposes' => self::PURPOSES,
            'termOptions' => self::TERMS,
            'farmersIneligibleForNewRequest' => $farmersIneligibleForNewRequest,
            'showArchived' => $showArchived,
            'archivedCount' => LoanRequest::whereNotNull('archived_at')->where('status', 'denied')->visibleAccount()->count(),
        ]);
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
     * Manager encodes a new loan request on behalf of a farmer.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request, requireCollateralProof: true);

        $documentsPath = null;
        if ($request->hasFile('documents')) {
            $document = $request->file('documents');
            $documentsPath = $document->storeAs('loan_documents', time().'_'.$document->getClientOriginalName(), 'public');
        }

        LoanRequest::create([
            'farmer_id' => $validated['farmer_id'],
            'requested_by' => Auth::id(),
            'type' => $validated['type'],
            'batch_id' => $validated['type'] === 'batch' ? $validated['batch_id'] : null,
            'requested_amount' => $validated['requested_amount'],
            'purpose' => $validated['purpose'],
            'repayment_terms_months' => $validated['repayment_terms_months'],
            'collateral' => $validated['collateral'] ?? null,
            'documents_path' => $documentsPath,
            'status' => 'pending',
        ]);

        return redirect()->route('manager.loan-request')
            ->with('success', 'Loan request submitted successfully.');
    }

    /**
     * One-click submission of an approved appointment's loan pre-request
     * straight to the Administrator — no manual re-entry. Still goes through
     * the exact same validateRequest() rules (CBU eligibility for regular
     * loans included) as any other request, so an appointment that
     * wouldn't otherwise qualify gets rejected with a clear reason instead
     * of silently going through just because a manager clicked a button.
     */
    public function storeFromAppointment(LoanAppointment $loan_appointment)
    {
        abort_if($loan_appointment->status !== 'approved', 422, 'Only approved appointments can be submitted.');
        abort_if(! $loan_appointment->requested_amount, 422, 'This appointment has no loan pre-request attached.');
        abort_if($loan_appointment->loan_request_id, 422, 'This appointment has already been submitted.');

        $farmer = $loan_appointment->user->farmer;

        abort_if(! $farmer, 422, 'This account has no linked farmer membership record.');

        $syntheticRequest = new Request();
        $syntheticRequest->merge([
            'farmer_id' => $farmer->id,
            'type' => 'regular',
            'requested_amount' => $loan_appointment->requested_amount,
            'purpose' => $loan_appointment->loan_purpose,
            'repayment_terms_months' => $loan_appointment->repayment_terms_months,
            'collateral' => $loan_appointment->collateral,
        ]);

        try {
            $validated = $this->validateRequest($syntheticRequest, excludeAppointmentId: $loan_appointment->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('manager.loan-appointment')
                ->withErrors($e->validator);
        }

        $loanRequest = LoanRequest::create([
            'farmer_id' => $validated['farmer_id'],
            'requested_by' => Auth::id(),
            'type' => 'regular',
            'batch_id' => null,
            'requested_amount' => $validated['requested_amount'],
            'purpose' => $validated['purpose'],
            'repayment_terms_months' => $validated['repayment_terms_months'],
            'collateral' => $validated['collateral'] ?? null,
            'documents_path' => $loan_appointment->documents_path,
            'status' => 'pending',
        ]);

        $loan_appointment->update(['loan_request_id' => $loanRequest->id]);

        return redirect()->route('manager.loan-appointment')
            ->with('success', "Loan request for {$farmer->full_name} has been submitted to the Administrator for approval.");
    }

    /**
     * Correct loan request details before a decision has been made.
     */
    public function update(Request $request, LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'pending', 422, 'Only pending requests can be edited.');

        $validated = $this->validateRequest($request, $loan_request->id);

        $documentsPath = $loan_request->documents_path;
        if ($request->hasFile('documents')) {
            if ($documentsPath) {
                Storage::disk('public')->delete($documentsPath);
            }
            $document = $request->file('documents');
            $documentsPath = $document->storeAs('loan_documents', time().'_'.$document->getClientOriginalName(), 'public');
        }

        $loan_request->update([
            'farmer_id' => $validated['farmer_id'],
            'type' => $validated['type'],
            'batch_id' => $validated['type'] === 'batch' ? $validated['batch_id'] : null,
            'requested_amount' => $validated['requested_amount'],
            'purpose' => $validated['purpose'],
            'repayment_terms_months' => $validated['repayment_terms_months'],
            'collateral' => $validated['collateral'] ?? null,
            'documents_path' => $documentsPath,
        ]);

        return redirect()->route('manager.loan-request')
            ->with('success', 'Loan request updated.');
    }

    /**
     * Correct a denied request and send it back to the Administrator,
     * instead of making the Manager re-encode it from scratch. Reuses the
     * exact same validation a new/edited request goes through — including
     * the batch capacity check, which matters here specifically: a denied
     * batch member doesn't count toward its batch's 10-member cap (see
     * LoanBatch::getMemberCountAttribute()), so without re-checking, a
     * resubmitted member could push a batch that backfilled its freed slot
     * past capacity. The original denial_reason is cleared from the live
     * record here, but survives permanently in this request's ActivityLog
     * entry (see deny()/denyBatch() in Admin\LoanApprovalController).
     */
    public function resubmit(Request $request, LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'denied', 422, 'Only denied requests can be resubmitted.');
        abort_if($loan_request->archived_at, 422, 'Restore this request from the archive before resubmitting it.');

        $validated = $this->validateRequest($request, $loan_request->id);

        $documentsPath = $loan_request->documents_path;
        if ($request->hasFile('documents')) {
            if ($documentsPath) {
                Storage::disk('public')->delete($documentsPath);
            }
            $document = $request->file('documents');
            $documentsPath = $document->storeAs('loan_documents', time().'_'.$document->getClientOriginalName(), 'public');
        }

        $loan_request->update([
            'farmer_id' => $validated['farmer_id'],
            'type' => $validated['type'],
            'batch_id' => $validated['type'] === 'batch' ? $validated['batch_id'] : null,
            'requested_amount' => $validated['requested_amount'],
            'purpose' => $validated['purpose'],
            'repayment_terms_months' => $validated['repayment_terms_months'],
            'collateral' => $validated['collateral'] ?? null,
            'documents_path' => $documentsPath,
            'status' => 'pending',
            'denial_reason' => null,
        ]);

        ActivityLogger::log(Auth::user(), 'loan_request.resubmitted', "Resubmitted {$loan_request->farmer->full_name}'s loan request to the Administrator after correcting it.", $loan_request);

        return redirect()->route('manager.loan-request')
            ->with('success', "{$loan_request->farmer->full_name}'s loan request has been corrected and resubmitted to the Administrator.");
    }

    /**
     * Finalize a request the Administrator has approved into an active loan record.
     */
    public function finalize(Request $request, LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'approved', 422, 'Only approved requests can be finalized.');
        abort_if($loan_request->loan()->exists(), 422, 'This request has already been finalized.');

        // Defense in depth: validateRequest() already blocks a second
        // request from being created while one is in process, but this
        // re-checks at the moment a Loan is actually about to be created,
        // in case of a stale approved-unfinalized request that predates
        // that fix, or any other way the farmer ended up with two.
        $hasOtherUnpaidLoan = Loan::whereIn('status', ['pending_disbursement', 'active', 'overdue'])
            ->whereHas('loanRequest', fn ($q) => $q->where('farmer_id', $loan_request->farmer_id))
            ->exists();

        abort_if($hasOtherUnpaidLoan, 422, 'This farmer already has an unpaid loan. Deny or archive this request instead.');

        $validated = $request->validate([
            'principal_amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'repayment_terms_months' => 'required|integer|min:1',
            'collateral' => 'nullable|string|max:255',
        ]);

        $installmentAmount = round($validated['principal_amount'] / $validated['repayment_terms_months'], 2);

        Loan::create([
            'loan_request_id' => $loan_request->id,
            'principal_amount' => $validated['principal_amount'],
            'interest_rate' => $validated['interest_rate'],
            'repayment_terms_months' => $validated['repayment_terms_months'],
            'installment_amount' => $installmentAmount,
            'collateral' => $validated['collateral'] ?? $loan_request->collateral,
            'status' => 'pending_disbursement',
        ]);

        if ($loan_request->farmer->account_user_id) {
            Notification::notify(
                $loan_request->farmer->account_user_id,
                'Loan Terms Finalized',
                'Your loan terms have been finalized: principal '.peso($validated['principal_amount']).", {$validated['repayment_terms_months']}-month term. Awaiting disbursement.",
                'loan_finalized',
            );
        }

        return redirect()->route('manager.loan-management')
            ->with('success', "Loan terms for {$loan_request->farmer->full_name} are finalized. Awaiting disbursement.");
    }

    /**
     * Finalize every approved, not-yet-finalized member of a batch at once,
     * using each member's own requested amount and term with one shared
     * interest rate — mirrors the Administrator's batch approve/deny decision.
     * Also collects one shared disbursement date/method/reference for the
     * whole batch: a today-or-earlier date disburses every member's loan
     * immediately, while a future date schedules them, to be released
     * automatically by the loans:process-scheduled-disbursements command
     * once that date arrives — batch loans never need a separate manual
     * "Mark Disbursed" step.
     */
    public function finalizeBatch(Request $request, LoanBatch $batch)
    {
        $members = $batch->loanRequests()
            ->where('status', 'approved')
            ->whereNull('archived_at')
            ->whereDoesntHave('loan')
            ->with('farmer')
            ->get();

        abort_if($members->isEmpty(), 422, 'This batch has no approved requests left to finalize.');

        $validated = $request->validate([
            'interest_rate' => 'required|numeric|min:0|max:100',
            'disbursement_date' => 'required|date|after_or_equal:today',
            'disbursement_method' => 'required|string|in:cash',
            'reference_no' => 'nullable|string|max:255',
        ]);

        $isImmediate = now()->toDateString() >= $validated['disbursement_date'];
        $skipped = [];

        foreach ($members as $member) {
            // Same defense-in-depth re-check as finalize(): skip (don't
            // abort the whole batch over) a member who already has an
            // unpaid loan, e.g. from a stale approved-unfinalized request
            // that predates the one-loan-at-a-time fix.
            $hasOtherUnpaidLoan = Loan::whereIn('status', ['pending_disbursement', 'active', 'overdue'])
                ->whereHas('loanRequest', fn ($q) => $q->where('farmer_id', $member->farmer_id))
                ->exists();

            if ($hasOtherUnpaidLoan) {
                $skipped[] = $member->farmer->full_name;

                continue;
            }

            $loan = Loan::create([
                'loan_request_id' => $member->id,
                'principal_amount' => $member->requested_amount,
                'interest_rate' => $validated['interest_rate'],
                'repayment_terms_months' => $member->repayment_terms_months,
                'installment_amount' => round($member->requested_amount / $member->repayment_terms_months, 2),
                'collateral' => $member->collateral,
                'status' => 'pending_disbursement',
                'scheduled_disbursement_date' => $validated['disbursement_date'],
                'scheduled_by' => Auth::id(),
            ]);

            if ($member->farmer->account_user_id) {
                Notification::notify(
                    $member->farmer->account_user_id,
                    'Loan Terms Finalized',
                    'Your loan terms have been finalized: principal '.peso($member->requested_amount).", {$member->repayment_terms_months}-month term, as part of {$batch->label}.",
                    'loan_finalized',
                );
            }

            if ($isImmediate) {
                $loan->disburse(
                    $validated['disbursement_method'],
                    $validated['reference_no'] ?? null,
                    Auth::id(),
                    $validated['disbursement_date'],
                );
            } else {
                $loan->update([
                    'disbursement_method' => $validated['disbursement_method'],
                    'reference_no' => $validated['reference_no'] ?? null,
                ]);
            }
        }

        $finalizedCount = $members->count() - count($skipped);

        if ($finalizedCount === 0) {
            return redirect()->route('manager.batch-loan-management')
                ->with('error', "None of {$batch->label}'s members could be finalized — they already have an unpaid loan: ".implode(', ', $skipped).'.');
        }

        $message = $isImmediate
            ? "{$batch->label}'s {$finalizedCount} loan(s) are finalized and disbursed."
            : "{$batch->label}'s {$finalizedCount} loan(s) are finalized and scheduled for disbursement on ".now()->parse($validated['disbursement_date'])->format('M d, Y').'.';

        if ($skipped) {
            $message .= ' Skipped (already has an unpaid loan): '.implode(', ', $skipped).'.';
        }

        return redirect()->route('manager.batch-loan-management')
            ->with('success', $message);
    }

    public function archive(LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'denied', 422, 'Only denied/rejected loan requests can be archived.');

        $loan_request->update(['archived_at' => now()]);

        return redirect()->route('manager.loan-request')
            ->with('undo', [
                'message' => 'Loan request archived.',
                'url' => route('manager.loan-request.unarchive', $loan_request),
            ]);
    }

    /**
     * Restore an archived (denied) loan request back onto the active list.
     */
    public function unarchive(LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'denied' || ! $loan_request->archived_at, 422, 'Only archived denied/rejected loan requests can be restored.');

        $loan_request->update(['archived_at' => null]);

        if (request()->boolean('undo')) {
            return back()->with('undo_done', 'Loan request restored.');
        }

        return redirect()->route('manager.loan-request', ['status' => 'archived'])
            ->with('success', 'Loan request restored.');
    }

    /**
     * Remove a single farmer from a batch that hasn't filled up (and so hasn't
     * been forwarded to the Administrator yet), freeing their slot for someone
     * else. Once a batch is full it's locked for admin review instead.
     */
    public function removeBatchMember(LoanRequest $loan_request)
    {
        abort_if($loan_request->type !== 'batch', 422, 'Only batch loan members can be removed this way.');
        abort_if($loan_request->status !== 'pending', 422, 'Only pending batch members can be removed.');
        abort_if($loan_request->archived_at, 422, 'This member has already been removed from the batch.');
        abort_if($loan_request->batch?->is_full, 422, 'This batch is already full and has been forwarded for approval.');

        $farmerName = $loan_request->farmer->full_name;
        $batchLabel = $loan_request->batch->label;

        $loan_request->update(['archived_at' => now()]);

        return redirect()->route('manager.loan-request')
            ->with('success', "{$farmerName} has been removed from {$batchLabel}.");
    }

    private function validateRequest(Request $request, ?int $excludeRequestId = null, bool $requireCollateralProof = false, ?int $excludeAppointmentId = null): array
    {
        return $request->validate([
            'farmer_id' => [
                'required',
                'exists:farmers,id',
                function ($attribute, $value, $fail) use ($excludeRequestId, $excludeAppointmentId) {
                    $reason = Farmer::find($value)?->loanInProcess($excludeRequestId, $excludeAppointmentId);

                    if ($reason) {
                        $fail("This farmer already has a loan in process ({$reason}) and can only request a new loan after it is fully paid.");
                    }
                },
            ],
            'type' => ['required', 'string', 'in:'.implode(',', self::TYPES)],
            'batch_id' => [
                'required_if:type,batch',
                'nullable',
                'exists:loan_batches,id',
                function ($attribute, $value, $fail) use ($request, $excludeRequestId) {
                    if ($request->input('type') !== 'batch' || ! $value) {
                        return;
                    }

                    $batch = LoanBatch::find($value);

                    $memberCount = $batch->loanRequests()
                        ->whereNull('archived_at')
                        ->where('status', '!=', 'denied')
                        ->when($excludeRequestId, fn ($q) => $q->where('id', '!=', $excludeRequestId))
                        ->count();

                    if ($memberCount >= $batch->capacity) {
                        $fail("{$batch->label} already has the maximum of {$batch->capacity} members. Please choose another batch.");
                    }
                },
            ],
            'requested_amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('type') === 'regular') {
                        $farmer = Farmer::with('cbu')->find($request->input('farmer_id'));

                        if (! $farmer) {
                            return;
                        }

                        $eligibility = $farmer->regular_loan_eligibility;

                        if (! $eligibility['eligible']) {
                            $fail("Your CBU balance is ".peso($eligibility['cbu_balance']).". A minimum of ".peso($eligibility['min_cbu'])." is required to request a Regular Loan.");

                            return;
                        }

                        if ($value > $eligibility['max_loanable']) {
                            $fail("Your CBU balance is ".peso($eligibility['cbu_balance']).". The maximum Regular Loan you can request is ".peso($eligibility['max_loanable']).".");
                        }

                        return;
                    }

                    if ($request->input('type') !== 'batch') {
                        return;
                    }

                    $farmer = Farmer::with('cbu')->find($request->input('farmer_id'));

                    if (! $farmer) {
                        return;
                    }

                    $bracket = self::PRODUCTION_LOAN_BRACKETS[(int) floor($farmer->land_area)] ?? null;

                    if (! $bracket) {
                        $fail("No production loan bracket is defined for {$farmer->land_area} hectare(s).");

                        return;
                    }

                    if ($value > $bracket['max_loanable']) {
                        $fail("Maximum loanable amount for {$farmer->land_area} hectare(s) is ".peso($bracket['max_loanable']).".");

                        return;
                    }

                    if (($farmer->cbu->balance ?? 0) < $bracket['min_cbu']) {
                        $fail("This farmer needs at least ".peso($bracket['min_cbu'])." in CBU for {$farmer->land_area} hectare(s).");
                    }
                },
            ],
            'purpose' => ['required', 'string', 'in:'.implode(',', self::PURPOSES)],
            'repayment_terms_months' => ['required', 'integer', 'in:'.implode(',', self::TERMS)],
            'collateral' => $requireCollateralProof ? 'required|string|max:255' : 'nullable|string|max:255',
            'documents' => [$requireCollateralProof ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
    }
}
