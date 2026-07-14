<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display recorded loan payments alongside the still-static CBU,
     * operational expense, and replaceable parts placeholders.
     */
    public function index()
    {
        $loanPayments = LoanPayment::with('loan.loanRequest.farmer')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $payableLoans = Loan::whereNull('archived_at')
            ->whereIn('status', ['active', 'overdue'])
            ->with('loanRequest.farmer')
            ->orderBy('next_due_date')
            ->get();

        $stats = [
            'loan_payments' => LoanPayment::where('type', 'payment')->sum('amount'),
        ];

        return view('manager.payment', compact('loanPayments', 'payableLoans', 'stats'));
    }

    /**
     * Record a farmer's payment against one of their active loans.
     */
    public function recordLoanPayment(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $loan = Loan::findOrFail($validated['loan_id']);

        abort_if(in_array($loan->status, ['fully_paid', 'archived']), 422, 'This loan is already closed.');
        abort_if((float) $validated['amount'] > (float) $loan->remaining_balance, 422, 'Payment exceeds remaining balance.');

        $loan->recordPayment($validated['amount'], $validated['notes'] ?? null, Auth::id());

        return redirect()->route('manager.payment')
            ->with('success', 'Payment recorded successfully.');
    }
}
