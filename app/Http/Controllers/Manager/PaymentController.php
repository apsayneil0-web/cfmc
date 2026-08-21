<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cbu;
use App\Models\CbuTransaction;
use App\Models\Expense;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display recorded loan payments, CBU transactions, and cooperative
     * expenses (operational/replaceable parts) in one unified feed.
     */
    public function index()
    {
        $loanPayments = LoanPayment::with('loan.loanRequest.farmer')
            ->orderByDesc('created_at')
            ->get();

        $cbuTransactions = CbuTransaction::with('cbu.farmer')
            ->orderByDesc('created_at')
            ->get();

        $expenses = Expense::whereIn('category', ['operational', 'replaceable_parts'])
            ->orderByDesc('created_at')
            ->get();

        $payments = $loanPayments->map(fn ($payment) => (object) [
            'kind' => 'loan',
            'id' => $payment->id,
            'transaction_code' => 'LNPAY-'.str_pad($payment->id, 3, '0', STR_PAD_LEFT),
            'date' => $payment->transaction_date,
            'payer' => $payment->loan->farmer->full_name,
            'type_label' => $payment->type === 'payment' ? 'Loan Payment' : 'Interest Charge',
            'reference' => 'LN-'.str_pad($payment->loan_id, 3, '0', STR_PAD_LEFT),
            'amount' => $payment->amount,
            'balance_after' => $payment->balance_after,
            'notes' => $payment->notes,
            'model' => $payment,
        ])->concat($cbuTransactions->map(fn ($transaction) => (object) [
            'kind' => 'cbu',
            'id' => $transaction->id,
            'transaction_code' => 'CBU-'.str_pad($transaction->id, 3, '0', STR_PAD_LEFT),
            'date' => $transaction->transaction_date,
            'payer' => $transaction->cbu->farmer->full_name,
            'type_label' => $transaction->type === 'contribution' ? 'CBU Contribution' : 'CBU Expense',
            'reference' => 'FM-'.str_pad($transaction->cbu->farmer_id, 3, '0', STR_PAD_LEFT),
            'amount' => $transaction->amount,
            'balance_after' => $transaction->balance_after,
            'notes' => $transaction->notes,
            'model' => $transaction,
        ]))->concat($expenses->map(fn ($expense) => (object) [
            'kind' => 'expense',
            'id' => $expense->id,
            'transaction_code' => 'EXP-'.str_pad($expense->id, 3, '0', STR_PAD_LEFT),
            'date' => $expense->expense_date,
            'payer' => $expense->description,
            'type_label' => $expense->category === 'operational' ? 'Operational Expense' : 'Replaceable Parts',
            'reference' => '-',
            'amount' => $expense->amount,
            'balance_after' => null,
            'notes' => null,
            'model' => $expense,
        ]))->sortByDesc(fn ($payment) => $payment->model->created_at)->values();

        $payableLoans = Loan::whereNull('archived_at')
            ->whereIn('status', ['active', 'overdue'])
            ->with('loanRequest.farmer')
            ->orderBy('created_at', 'desc')
            ->get();

        $cbuFarmers = Farmer::where('status', 'approved')
            ->with('cbu')
            ->orderBy('last_name')
            ->get();

        $stats = [
            'loan_payments' => LoanPayment::where('type', 'payment')->sum('amount'),
            'cbu_contributions' => CbuTransaction::where('type', 'contribution')->sum('amount'),
            'operational_expenses' => Expense::where('category', 'operational')->sum('amount'),
            'replaceable_parts' => Expense::where('category', 'replaceable_parts')->sum('amount'),
        ];

        return view('manager.payment', compact('payments', 'payableLoans', 'cbuFarmers', 'stats'));
    }

    /**
     * Record a farmer's CBU contribution or expense from the Payment Management page,
     * opening their CBU account on first use.
     */
    public function recordCbuPayment(Request $request)
    {
        $validated = $request->validate([
            'farmer_id' => 'required|exists:farmers,id',
            'type' => 'required|in:contribution,expense',
            'category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cbu = Cbu::firstOrCreate(
            ['farmer_id' => $validated['farmer_id']],
            ['balance' => 0, 'status' => 'active']
        );

        abort_if($validated['type'] === 'expense' && (float) $validated['amount'] > (float) $cbu->balance, 422, 'Amount exceeds the farmer\'s CBU balance.');

        $cbu->recordTransaction(
            $validated['type'],
            $validated['category'] ?? null,
            (float) $validated['amount'],
            $validated['notes'] ?? null,
            Auth::id()
        );

        return redirect()->route('manager.payment')
            ->with('success', 'CBU payment recorded successfully.');
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
        abort_if($loan->status === 'pending_disbursement', 422, 'This loan has not been disbursed yet.');
        abort_if((float) $validated['amount'] > (float) $loan->remaining_balance, 422, 'Payment exceeds remaining balance.');

        $loan->recordPayment($validated['amount'], $validated['notes'] ?? null, Auth::id());

        return redirect()->route('manager.payment')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Printable receipt for a single loan payment, opened in its own tab.
     */
    public function receipt(LoanPayment $loan_payment)
    {
        $loan_payment->load('loan.loanRequest.farmer', 'recordedBy');

        return view('manager.payment-receipt', ['payment' => $loan_payment]);
    }
}
