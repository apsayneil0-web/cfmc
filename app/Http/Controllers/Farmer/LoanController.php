<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index()
    {
        $farmer = Auth::user()->farmer;

        $loanRequests = $farmer
            ? LoanRequest::where('farmer_id', $farmer->id)
                ->whereNull('archived_at')
                ->with(['loan.payments', 'batch'])
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $loans = $loanRequests->pluck('loan')->filter();

        $activeLoan = $loans->first(fn ($loan) => $loan->status !== 'fully_paid');

        $paidLoans = $loans->where('status', 'fully_paid')
            ->sortByDesc(fn ($loan) => $loan->payments->max('transaction_date'))
            ->values();

        return view('farmer.loans', compact('farmer', 'loanRequests', 'activeLoan', 'paidLoans'));
    }
}
