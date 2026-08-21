<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cbu;
use App\Models\CbuTransaction;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CbuController extends Controller
{
    /**
     * Display every member's CBU account alongside the full contribution/expense ledger.
     */
    public function index()
    {
        $farmers = Farmer::where('status', 'approved')
            ->orderBy('last_name')
            ->get();

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

        return view('manager.cbu', compact('farmers', 'transactions', 'stats'));
    }

    /**
     * Record a contribution or expense entry against a farmer's CBU account,
     * opening the account on first use.
     */
    public function store(Request $request)
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

        return redirect()->route('manager.cbu')
            ->with('success', 'CBU entry recorded successfully.');
    }
}
