<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialController extends Controller
{
    /**
     * Expense-side financial overview. Income (harvest sales, machine rental,
     * CBU/loan interest, cooperative share) isn't tracked anywhere in the
     * system yet, so this view deliberately shows expenses only rather than
     * fabricating income figures.
     */
    public function index()
    {
        $expenses = Expense::with('recordedBy')
            ->orderByDesc('expense_date')
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
            'status' => 'required|in:pending,paid',
        ]);

        Expense::create([
            ...$validated,
            'recorded_by' => Auth::id(),
        ]);

        return redirect()->route('manager.financial')
            ->with('success', 'Expense recorded successfully.');
    }
}
