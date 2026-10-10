<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
        // approved/active loans still in play. Archived ones live in their own
        // view ("View Archived", ?archived=1; old ?status=archived links too).
        $showArchived = $request->boolean('archived') || $request->input('status') === 'archived';

        if ($showArchived) {
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
                $q->where('first_name', 'like', "{$search}%")
                    ->orWhere('last_name', 'like', "{$search}%");
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

        // Paginate by batch (one page = N batch groups), not by individual
        // loan — a batch's members always need to be reviewed together.
        $perPage = 10;
        $page = (int) $request->input('page', 1);
        $batchGroups = new LengthAwarePaginator(
            $batchGroups->forPage($page, $perPage)->values(),
            $batchGroups->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

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
                ->sum(fn (Loan $loan) => $loan->amount_due),
            'interest_earned' => LoanPayment::where('type', 'interest')
                ->whereHas('loan', fn ($q) => $q->whereNull('archived_at')->whereHas('loanRequest', fn ($q) => $q->where('type', 'batch')))
                ->sum('amount'),
            'archived_count' => Loan::whereNotNull('archived_at')->tap($batchOnly)->count(),
        ];

        return view('manager.batch-loan-management', compact('batchGroups', 'stats', 'showArchived'));
    }
}
