<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;

class BatchLoanManagementController extends Controller
{
    /**
     * Display batch loans grouped by their originating LoanBatch, mirroring
     * the individual-loan Loan Management page but grouped for a batch's
     * members to be reviewed and acted on together. Interest is caught up
     * for any loan whose due date has passed before the list is rendered.
     */
    public function index(Request $request)
    {
        $activeLoans = Loan::whereNull('archived_at')
            ->whereIn('status', ['active', 'overdue'])
            ->whereHas('loanRequest', fn ($q) => $q->where('type', 'batch'))
            ->with('loanRequest.farmer')
            ->get();

        foreach ($activeLoans as $loan) {
            $loan->applyOverdueInterest();
            $loan->applyGracePeriodPolicy();
        }

        $query = Loan::with(['loanRequest.farmer', 'loanRequest.batch', 'payments'])
            ->whereHas('loanRequest', fn ($q) => $q->where('type', 'batch'));

        // By default (and for any specific business status), only show
        // approved/active loans still in play. Archived ones are hidden
        // unless the manager explicitly filters for "Archived".
        if ($request->input('status') === 'archived') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');

            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('loanRequest.farmer', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $loans = $query->orderBy('created_at', 'desc')->get();

        $batchGroups = $loans->groupBy(fn (Loan $loan) => $loan->loanRequest->batch_id)
            ->map(fn ($members) => (object) [
                'batch' => $members->first()->loanRequest->batch,
                'loans' => $members,
            ])
            ->sortByDesc(fn ($group) => $group->loans->max('created_at'))
            ->values();

        $batchOnly = fn ($q) => $q->whereHas('loanRequest', fn ($q) => $q->where('type', 'batch'));

        $stats = [
            'pending_disbursement_count' => Loan::whereNull('archived_at')->where('status', 'pending_disbursement')->tap($batchOnly)->count(),
            'active_count' => Loan::whereNull('archived_at')->whereIn('status', ['active', 'overdue'])->tap($batchOnly)->count(),
            'total_outstanding' => Loan::whereNull('archived_at')->whereIn('status', ['active', 'overdue'])->tap($batchOnly)->sum('remaining_balance'),
            'due_this_month' => Loan::whereNull('archived_at')
                ->whereIn('status', ['active', 'overdue'])
                ->tap($batchOnly)
                ->whereBetween('next_due_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->get()
                ->sum(fn (Loan $loan) => $loan->monthly_due),
            'interest_earned' => LoanPayment::where('type', 'interest')
                ->whereHas('loan', fn ($q) => $q->whereNull('archived_at')->whereHas('loanRequest', fn ($q) => $q->where('type', 'batch')))
                ->sum('amount'),
        ];

        return view('manager.batch-loan-management', compact('batchGroups', 'stats'));
    }
}
