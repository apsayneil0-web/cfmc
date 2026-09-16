<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cbu;
use App\Models\CbuTransaction;
use Illuminate\Http\Request;

class CbuController extends Controller
{
    /**
     * Display every member's CBU account alongside the full contribution/expense
     * ledger. New entries are recorded from the Payment page (see
     * Manager\PaymentController::recordCbuPayment) — this page only lets the
     * Manager correct an existing entry, not create new ones.
     */
    public function index()
    {
        $transactions = CbuTransaction::with('cbu.farmer', 'recordedBy')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total_contributions' => CbuTransaction::where('type', 'contribution')->sum('amount'),
            'total_balance' => Cbu::sum('balance'),
            'active_members' => Cbu::where('status', 'active')->count(),
            'this_month' => CbuTransaction::where('type', 'contribution')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount'),
        ];

        return view('manager.cbu', compact('transactions', 'stats'));
    }

    /**
     * Correct an existing transaction's details, then replay the farmer's
     * whole ledger so every balance_after downstream (and the account's
     * current balance) stays consistent with the edit.
     */
    public function update(Request $request, CbuTransaction $cbu_transaction)
    {
        $validated = $request->validate([
            'type' => 'required|in:contribution,expense',
            'category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cbu_transaction->update($validated);
        $cbu_transaction->cbu->recalculateBalances();

        return redirect()->route('manager.cbu')
            ->with('success', 'CBU entry updated successfully.');
    }
}
