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

        $activeLoan = $loanRequests
            ->pluck('loan')
            ->filter()
            ->first(fn ($loan) => $loan->status !== 'fully_paid');

        return view('farmer.loans', compact('farmer', 'loanRequests', 'activeLoan'));
    }
}
