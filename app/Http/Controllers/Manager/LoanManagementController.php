<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanManagementController extends Controller
{
    /**
     * Display active, overdue, and completed loans. Interest is caught up
     * for any loan whose due date has passed before the list is rendered.
     */
    public function index(Request $request)
    {
        $activeLoans = Loan::whereNull('archived_at')
            ->whereIn('status', ['active', 'overdue'])
            ->with('loanRequest.farmer')
            ->get();

        foreach ($activeLoans as $loan) {
            $loan->applyOverdueInterest();
            $loan->applyGracePeriodPolicy();
        }

        $query = Loan::with(['loanRequest.farmer', 'loanRequest.batch', 'payments']);

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

        $stats = [
            'pending_disbursement_count' => Loan::whereNull('archived_at')->where('status', 'pending_disbursement')->count(),
            'active_count' => Loan::whereNull('archived_at')->whereIn('status', ['active', 'overdue'])->count(),
            'total_outstanding' => Loan::whereNull('archived_at')->whereIn('status', ['active', 'overdue'])->sum('remaining_balance'),
            'due_this_month' => Loan::whereNull('archived_at')
                ->whereIn('status', ['active', 'overdue'])
                ->whereBetween('next_due_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->get()
                ->sum(fn (Loan $loan) => $loan->monthly_due),
            'interest_earned' => LoanPayment::where('type', 'interest')
                ->whereHas('loan', fn ($q) => $q->whereNull('archived_at'))
                ->sum('amount'),
        ];

        return view('manager.loan-management', compact('loans', 'stats'));
    }

    /**
     * Adjust repayment schedule, due date, or collateral details.
     */
    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'next_due_date' => 'required|date',
            'collateral' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $loan->update($validated);

        return redirect()->route('manager.loan-management')
            ->with('success', 'Loan record updated.');
    }

    /**
     * Release funds for a finalized loan. Only allowed once, from
     * pending_disbursement; this is what actually starts the repayment clock.
     */
    public function disburse(Request $request, Loan $loan)
    {
        abort_if($loan->status !== 'pending_disbursement', 422, 'Only loans awaiting disbursement can be released.');

        $validated = $request->validate([
            'disbursed_at' => 'required|date|before_or_equal:today',
            'disbursement_method' => 'required|string|in:cash,bank_transfer,check',
            'reference_no' => 'nullable|string|max:255',
        ]);

        $loan->disburse(
            $validated['disbursement_method'],
            $validated['reference_no'] ?? null,
            Auth::id(),
            $validated['disbursed_at'],
        );

        return redirect()->route('manager.loan-management')
            ->with('success', "Loan for {$loan->farmer->full_name} has been disbursed and is now active.");
    }

    public function archive(Loan $loan)
    {
        $loan->update(['archived_at' => now()]);

        return redirect()->route('manager.loan-management')
            ->with('success', 'Loan archived.');
    }
}
