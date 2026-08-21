<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CbuController extends Controller
{
    public function index()
    {
        $farmer = Auth::user()->farmer;

        $cbu = $farmer?->cbu;

        $transactions = $cbu
            ? $cbu->transactions()->orderByDesc('created_at')->get()
            : collect();

        $contributions = $transactions->where('type', 'contribution');
        $expenses = $transactions->where('type', 'expense');

        $stats = [
            'total_contributions' => $contributions->sum('amount'),
            'total_expenses' => $expenses->sum('amount'),
            'balance' => $cbu->balance ?? 0,
        ];

        return view('farmer.cbu', compact('contributions', 'expenses', 'stats'));
    }
}
