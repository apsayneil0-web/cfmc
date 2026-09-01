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
        $farmers = Farmer::where('status', 'approved')
            ->with(['cbu.transactions' => fn ($q) => $q->orderByDesc('transaction_date')->orderByDesc('created_at')])
            ->orderBy('last_name')
            ->get();

        $stats = [
            'total_balance' => Cbu::sum('balance'),
            'total_contributions' => CbuTransaction::where('type', 'contribution')->sum('amount'),
            'active_members' => Cbu::where('status', 'active')->count(),
        ];

        return view('admin.cbu', compact('farmers', 'stats'));
    }
}
