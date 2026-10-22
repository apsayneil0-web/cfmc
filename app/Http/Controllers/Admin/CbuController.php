<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cbu;
use App\Models\CbuTransaction;
use App\Models\Farmer;
use Illuminate\Http\Request;

class CbuController extends Controller
{
    /**
     * Read-only oversight of every approved farmer's CBU account and full
     * transaction ledger. Entries are recorded by the Manager (see
     * manager.cbu / manager.payment); Admin can only view them here.
     */
    public function index(Request $request)
    {
        $query = Farmer::where('status', 'approved')
            ->with(['cbu.transactions' => fn ($q) => $q->visibleAccount()->orderByDesc('created_at')]);

        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->whereRaw("CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?", ["%{$search}%"]);
        }

        // CBU status: members without a CBU account yet count as Inactive,
        // matching the badge shown in the list.
        if ($request->input('cbu_status') === 'active') {
            $query->whereHas('cbu', fn ($q) => $q->where('status', 'active'));
        } elseif ($request->input('cbu_status') === 'inactive') {
            $query->whereDoesntHave('cbu', fn ($q) => $q->where('status', 'active'));
        }

        $farmers = $query->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total_balance' => Cbu::visibleAccount()->sum('balance'),
            'total_contributions' => CbuTransaction::where('type', 'contribution')->visibleAccount()->sum('amount'),
            'active_members' => Cbu::where('status', 'active')->count(),
        ];

        return view('admin.cbu', compact('farmers', 'stats'));
    }
}
