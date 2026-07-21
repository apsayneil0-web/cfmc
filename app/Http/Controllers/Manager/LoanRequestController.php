<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\LoanBatch;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanRequestController extends Controller
{
    private const PURPOSES = ['Farming Equipment', 'Seeds & Fertilizer', 'Machinery Purchase', 'Working Capital'];
    private const TERMS = [6, 12, 18, 24];
    private const TYPES = ['regular', 'batch'];

    /**
     * Display all loan requests awaiting or past decision.
     */
    public function index(Request $request)
    {
        $query = LoanRequest::with(['farmer', 'requestedBy', 'loan', 'batch'])
            ->whereNull('archived_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('farmer', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'denied', 'archived')")
            ->orderBy('created_at', 'desc')
            ->get();

        $farmers = Farmer::where('status', 'approved')->orderBy('first_name')->get();
        $batches = LoanBatch::orderBy('batch_number')->get();

        // Batches still collecting members: not yet full, so not yet forwarded
        // to the Administrator. These are the only ones a manager can edit.
        $pendingActiveMember = fn ($q) => $q->where('status', 'pending')->whereNull('archived_at');
        $batchesInProgress = LoanBatch::whereHas('loanRequests', $pendingActiveMember)
            ->with(['loanRequests' => fn ($q) => $pendingActiveMember($q)->with('farmer')->orderBy('created_at')])
            ->orderBy('batch_number')
            ->get()
            ->reject(fn (LoanBatch $batch) => $batch->is_full)
            ->values();

        $farmersIneligibleForNewRequest = [];
        foreach (LoanRequest::where('status', 'pending')->pluck('farmer_id') as $farmerId) {
            $farmersIneligibleForNewRequest[$farmerId] = 'Has a pending loan request';
        }
        foreach (Loan::whereIn('status', ['active', 'overdue'])->with('loanRequest')->get() as $loan) {
            $farmersIneligibleForNewRequest[$loan->loanRequest->farmer_id] ??= 'Has an unpaid loan';
        }

        return view('manager.loan-request', [
            'requests' => $requests,
            'farmers' => $farmers,
            'batches' => $batches,
            'batchesInProgress' => $batchesInProgress,
            'purposes' => self::PURPOSES,
            'termOptions' => self::TERMS,
            'farmersIneligibleForNewRequest' => $farmersIneligibleForNewRequest,
        ]);
    }

    /**
     * Manager encodes a new loan request on behalf of a farmer.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

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
     * Correct loan request details before a decision has been made.
     */
    public function update(Request $request, LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'pending', 422, 'Only pending requests can be edited.');

        $validated = $this->validateRequest($request, $loan_request->id);

        $loan_request->update([
            'farmer_id' => $validated['farmer_id'],
            'type' => $validated['type'],
            'batch_id' => $validated['type'] === 'batch' ? $validated['batch_id'] : null,
            'requested_amount' => $validated['requested_amount'],
            'purpose' => $validated['purpose'],
            'repayment_terms_months' => $validated['repayment_terms_months'],
            'collateral' => $validated['collateral'] ?? null,
        ]);

        return redirect()->route('manager.loan-request')
            ->with('success', 'Loan request updated.');
    }

    /**
     * Finalize a request the Administrator has approved into an active loan record.
     */
    public function finalize(Request $request, LoanRequest $loan_request)
    {
        abort_if($loan_request->status !== 'approved', 422, 'Only approved requests can be finalized.');
        abort_if($loan_request->loan()->exists(), 422, 'This request has already been finalized.');

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
            'remaining_balance' => $validated['principal_amount'],
            'next_due_date' => now()->addMonthNoOverflow()->toDateString(),
            'status' => 'active',
        ]);

        return redirect()->route('manager.loan-management')
            ->with('success', "Loan for {$loan_request->farmer->full_name} is now active.");
    }

    public function archive(LoanRequest $loan_request)
    {
        $loan_request->update(['archived_at' => now()]);

        return redirect()->route('manager.loan-request')
            ->with('success', 'Loan request archived.');
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

    private function validateRequest(Request $request, ?int $excludeRequestId = null): array
    {
        return $request->validate([
            'farmer_id' => [
                'required',
                'exists:farmers,id',
                function ($attribute, $value, $fail) use ($excludeRequestId) {
                    $hasPending = LoanRequest::where('farmer_id', $value)
                        ->where('status', 'pending')
                        ->when($excludeRequestId, fn ($q) => $q->where('id', '!=', $excludeRequestId))
                        ->exists();

                    if ($hasPending) {
                        $fail('This farmer already has a pending loan request and cannot submit another until it is decided.');

                        return;
                    }

                    $hasUnpaidLoan = Loan::whereIn('status', ['active', 'overdue'])
                        ->whereHas('loanRequest', fn ($q) => $q->where('farmer_id', $value))
                        ->exists();

                    if ($hasUnpaidLoan) {
                        $fail('This farmer already has an unpaid loan and cannot request a new one until it is fully paid.');
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
            'requested_amount' => 'required|numeric|min:1',
            'purpose' => ['required', 'string', 'in:'.implode(',', self::PURPOSES)],
            'repayment_terms_months' => ['required', 'integer', 'in:'.implode(',', self::TERMS)],
            'collateral' => 'nullable|string|max:255',
            'documents' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
    }
}
