<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cbu;
use App\Models\CbuTransaction;
use App\Models\Expense;
use App\Models\Farmer;
use App\Models\HarvestPayment;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Notification;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Minimum for every CBU contribution after a farmer's first. The first
     * contribution's minimum instead depends on land area — see
     * Farmer::first_cbu_contribution_minimum.
     */
    private const CBU_SUBSEQUENT_CONTRIBUTION_MINIMUM = 1000;

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

        $harvestPayments = HarvestPayment::with('farmer')
            ->orderByDesc('created_at')
            ->get();

        $payments = $loanPayments->map(fn ($payment) => (object) [
            'kind' => 'loan',
            'id' => $payment->id,
            'transaction_code' => 'LNPAY-'.str_pad($payment->id, 3, '0', STR_PAD_LEFT),
            'date' => $payment->transaction_date,
            'payer' => $payment->loan->farmer->full_name,
            'type_label' => match ($payment->type) {
                'payment' => 'Loan Payment',
                'prepayment' => 'Prepayment',
                'partial' => 'Partial Payment',
                default => 'Interest Charge',
            },
            'reference' => 'LN-'.str_pad($payment->loan_id, 3, '0', STR_PAD_LEFT),
            'amount' => $payment->amount,
            'balance_after' => $payment->balance_after,
            'notes' => $payment->notes,
            'status_label' => 'Completed',
            'filter_category' => 'Loan Payment',
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
            'status_label' => 'Completed',
            'filter_category' => 'CBU Contribution',
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
            'status_label' => ucfirst($expense->status),
            'filter_category' => $expense->category === 'operational' ? 'Operational Expense' : 'Replaceable Parts',
            'model' => $expense,
        ]))->concat($harvestPayments->map(fn ($harvest) => (object) [
            'kind' => 'harvest',
            'id' => $harvest->id,
            'transaction_code' => 'HRV-'.str_pad($harvest->id, 3, '0', STR_PAD_LEFT),
            'date' => $harvest->payment_date,
            'payer' => $harvest->payer_name,
            'type_label' => $harvest->member_type === 'member' ? 'Harvest Payment (Member)' : 'Harvest Payment (Non-member)',
            'reference' => $harvest->farmer_id ? 'FM-'.str_pad($harvest->farmer_id, 3, '0', STR_PAD_LEFT) : '-',
            'amount' => $harvest->payment_amount,
            'balance_after' => null,
            'notes' => $harvest->notes,
            'status_label' => 'Completed',
            'filter_category' => 'Harvest Payment',
            'model' => $harvest,
        ]))->sortByDesc(fn ($payment) => $payment->model->created_at)->values();

        $payableLoans = Loan::whereNull('archived_at')
            ->whereIn('status', ['active', 'overdue'])
            ->with('loanRequest.farmer')
            ->orderBy('created_at', 'desc')
            ->get();

        $cbuFarmers = Farmer::where('status', 'approved')
            ->with('cbu.transactions')
            ->orderBy('last_name')
            ->get();

        $payableExpenses = Expense::where('status', 'pending')
            ->orderBy('expense_date')
            ->get();

        // Past non-member names, so a returning walk-in harvester can be
        // picked consistently instead of retyped/misspelled each visit.
        $nonMemberNames = HarvestPayment::where('member_type', 'non-member')
            ->whereNotNull('farmer_name')
            ->pluck('farmer_name')
            ->merge(
                ScheduleRequest::where('member_type', 'non-member')
                    ->whereNotNull('farmer_name')
                    ->pluck('farmer_name')
            )
            ->unique()
            ->sort()
            ->values();

        $stats = [
            'loan_payments' => LoanPayment::whereIn('type', ['payment', 'prepayment', 'partial'])->sum('amount'),
            'cbu_contributions' => CbuTransaction::where('type', 'contribution')->sum('amount'),
            'operational_expenses' => Expense::where('category', 'operational')->sum('amount'),
            'replaceable_parts' => Expense::where('category', 'replaceable_parts')->sum('amount'),
            'harvest_payments' => HarvestPayment::sum('payment_amount'),
        ];

        return view('manager.payment', compact('payments', 'payableLoans', 'cbuFarmers', 'payableExpenses', 'nonMemberNames', 'stats'));
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

        if ($validated['type'] === 'contribution') {
            $hasContributed = $cbu->transactions()->where('type', 'contribution')->exists();

            if ($hasContributed) {
                abort_if(
                    (float) $validated['amount'] < self::CBU_SUBSEQUENT_CONTRIBUTION_MINIMUM,
                    422,
                    'CBU contributions must be at least '.peso(self::CBU_SUBSEQUENT_CONTRIBUTION_MINIMUM).'.'
                );
            } else {
                $minimum = $cbu->farmer->first_cbu_contribution_minimum;

                abort_if(
                    (float) $validated['amount'] < $minimum,
                    422,
                    "This farmer's first CBU contribution must be at least ".peso($minimum).'.'
                );
            }
        }

        abort_if($validated['type'] === 'expense' && (float) $validated['amount'] > (float) $cbu->balance, 422, 'Amount exceeds the farmer\'s CBU balance.');

        $cbu->recordTransaction(
            $validated['type'],
            $validated['category'] ?? null,
            (float) $validated['amount'],
            $validated['notes'] ?? null,
            Auth::id()
        );

        if ($cbu->farmer->account_user_id) {
            $typeLabel = $validated['type'] === 'contribution' ? 'CBU contribution' : 'CBU expense';

            Notification::notify(
                $cbu->farmer->account_user_id,
                'Payment Recorded',
                'A '.$typeLabel.' of '.peso($validated['amount']).' has been recorded on your CBU account.',
                'payment_recorded',
            );
        }

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
            'type' => 'required|in:payment,prepayment,partial',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $loan = Loan::findOrFail($validated['loan_id']);

        abort_if(in_array($loan->status, ['fully_paid', 'archived']), 422, 'This loan is already closed.');
        abort_if($loan->status === 'pending_disbursement', 422, 'This loan has not been disbursed yet.');
        abort_if((float) $validated['amount'] > (float) $loan->remaining_balance, 422, 'Payment exceeds remaining balance.');

        $loan->recordPayment($validated['amount'], $validated['notes'] ?? null, Auth::id(), $validated['type']);

        if ($loan->farmer?->account_user_id) {
            Notification::notify(
                $loan->farmer->account_user_id,
                'Payment Recorded',
                'Your payment of '.peso($validated['amount']).' has been recorded against your loan. Remaining balance: '.peso($loan->remaining_balance).'.',
                'payment_recorded',
                ['loan_id' => $loan->id],
            );
        }

        return redirect()->route('manager.payment')
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Settle a pending cooperative expense (operational/machinery/replaceable
     * parts) from the Payment page.
     */
    public function payExpense(Request $request)
    {
        $validated = $request->validate([
            'expense_id' => 'required|exists:expenses,id',
        ]);

        $expense = Expense::findOrFail($validated['expense_id']);

        abort_if($expense->status === 'paid', 422, 'This expense is already paid.');

        $expense->markPaid(Auth::id());

        return redirect()->route('manager.payment')
            ->with('success', 'Expense payment recorded successfully.');
    }

    /**
     * Record the cooperative's cut of a farmer's reported harvest income —
     * 9% for members, 12% for non-members. The rate and resulting payment
     * amount are always computed here from HarvestPayment's constants, never
     * trusted from the request, even though the form previews the same math
     * client-side for the Manager's benefit.
     */
    public function recordHarvestPayment(Request $request)
    {
        $validated = $request->validate([
            'member_type' => 'required|in:member,non-member',
            'farmer_id' => 'required_if:member_type,member|nullable|exists:farmers,id',
            'farmer_name' => 'required_if:member_type,non-member|nullable|string|max:255',
            'harvest_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $rate = HarvestPayment::rateFor($validated['member_type']);
        $paymentAmount = round((float) $validated['harvest_amount'] * $rate / 100, 2);

        $harvestPayment = HarvestPayment::create([
            'farmer_id' => $validated['member_type'] === 'member' ? $validated['farmer_id'] : null,
            'farmer_name' => $validated['member_type'] === 'non-member' ? $validated['farmer_name'] : null,
            'member_type' => $validated['member_type'],
            'harvest_amount' => $validated['harvest_amount'],
            'rate' => $rate,
            'payment_amount' => $paymentAmount,
            'payment_date' => now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        if ($validated['member_type'] === 'member' && $harvestPayment->farmer?->account_user_id) {
            Notification::notify(
                $harvestPayment->farmer->account_user_id,
                'Payment Recorded',
                'A harvest payment of '.peso($paymentAmount).' ('.$rate.'% of '.peso($validated['harvest_amount']).' reported harvest) has been recorded for you.',
                'payment_recorded',
            );
        }

        return redirect()->route('manager.payment')
            ->with('success', 'Harvesting payment recorded successfully.');
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
