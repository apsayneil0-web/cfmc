<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FinancialController extends Controller
{
    /**
     * Expense records: each entry is simply the kind of expense and its
     * amount. Expenses are recorded here only — they are not settled through
     * the Payment module.
     */
    public function index()
    {
        $expenses = Expense::with('recordedBy')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'operational' => Expense::where('category', 'operational')->sum('amount'),
            'machinery' => Expense::where('category', 'machinery')->sum('amount'),
            'replaceable_parts' => Expense::where('category', 'replaceable_parts')->sum('amount'),
        ];
        $stats['total'] = $stats['operational'] + $stats['machinery'] + $stats['replaceable_parts'];

        return view('manager.financial', compact('expenses', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:operational,machinery,replaceable_parts',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'proof_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'proof_document.required' => 'Attach the proof of the expense (receipt photo or invoice) before recording.',
            'proof_document.mimes' => 'The proof must be a JPG, PNG or PDF file.',
            'proof_document.max' => 'The proof must not be larger than 5 MB.',
        ]);

        $validated['proof_document'] = $request->file('proof_document')->storeAs(
            'expense_proofs',
            time().'_'.Str::random(6).'.'.strtolower($request->file('proof_document')->getClientOriginalExtension()),
            'public'
        );

        Expense::create([
            ...$validated,
            'recorded_by' => Auth::id(),
        ]);

        return redirect()->route('manager.financial')
            ->with('success', 'Expense recorded successfully.');
    }
}
