<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CbuController extends Controller
{
    public function index(Request $request)
    {
        $farmer = Auth::user()->farmer;

        $cbu = $farmer?->cbu;

        $transactions = $cbu
            ? $cbu->transactions()->orderByDesc('created_at')->get()
            : collect();

        $allContributions = $transactions->where('type', 'contribution')->values();
        $allExpenses = $transactions->where('type', 'expense')->values();

        $stats = [
            'total_contributions' => $allContributions->sum('amount'),
            'total_expenses' => $allExpenses->sum('amount'),
            'balance' => $cbu->balance ?? 0,
        ];

        // Two independent tables share this one page, so each gets its own
        // "page" query param — paging through one must not reset the other.
        $contributions = $this->paginateCollection($allContributions, $request, 'contrib_page');
        $expenses = $this->paginateCollection($allExpenses, $request, 'expense_page');

        return view('farmer.cbu', compact('contributions', 'expenses', 'stats'));
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
