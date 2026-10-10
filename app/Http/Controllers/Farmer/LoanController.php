<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $farmer = Auth::user()->farmer;

        $allLoanRequests = $farmer
            ? LoanRequest::where('farmer_id', $farmer->id)
                ->whereNull('archived_at')
                ->with(['loan.payments', 'batch'])
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $loans = $allLoanRequests->pluck('loan')->filter();

        $activeLoan = $loans->first(fn ($loan) => $loan->status !== 'fully_paid');

        $paidLoans = $loans->where('status', 'fully_paid')
            ->sortByDesc(fn ($loan) => $loan->payments->max('transaction_date'))
            ->values();

        // Two independent tables share this one page (request history, and
        // paid-off loan history), so each gets its own "page" query param —
        // paging through one must not reset or collide with the other.
        $loanRequests = $this->paginateCollection($allLoanRequests, $request, 'requests_page');
        $paidLoans = $this->paginateCollection($paidLoans, $request, 'paid_page');

        return view('farmer.loans', compact('farmer', 'loanRequests', 'activeLoan', 'paidLoans'));
    }

    private function paginateCollection($items, Request $request, string $pageName, int $perPage = 10): LengthAwarePaginator
    {
        $page = (int) $request->input($pageName, 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => $pageName]
        );
    }
}
