<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MachineUsageController extends Controller
{
    /**
     * Read-only usage/maintenance monitor. No add/edit/archive actions live
     * here — that's the Machinery Registry's job.
     */
    public function index(Request $request)
    {
        $machines = Machine::whereNull('archived_at')->get();

        $stats = [
            'total' => $machines->count(),
            'available' => $machines->where('status', 'available')->count(),
            'in_use' => $machines->where('status', 'in_use')->count(),
            'maintenance' => $machines->where('status', 'maintenance')->count(),
        ];

        if ($request->filled('search')) {
            $search = mb_strtolower($request->string('search'));
            $machines = $machines->filter(fn (Machine $m) => str_starts_with(mb_strtolower($m->name), $search)
                || str_starts_with(mb_strtolower((string) $m->brand), $search)
                || str_starts_with(mb_strtolower((string) $m->serial_number), $search));
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            $machines = $machines->filter(fn (Machine $m) => $m->status === $status);
        }

        if ($request->filled('maintenance')) {
            $tier = $request->string('maintenance')->toString();
            $machines = $machines->filter(fn (Machine $m) => $m->maintenance_level === $tier);
        }

        $machines = $machines->sortByDesc('created_at')->values();

        // status/maintenance_level are PHP accessors derived from usage
        // records, not real columns, so the filters above can't be pushed
        // into SQL — the search/filter has to happen on the fetched
        // collection. Paginating is still doable: slice that already-
        // filtered collection into a real paginator by hand, same API the
        // view already uses everywhere else (currentPage(), url(), etc).
        $page = (int) $request->input('page', 1);
        $perPage = 10;
        $machines = new LengthAwarePaginator(
            $machines->forPage($page, $perPage)->values(),
            $machines->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('manager.machine-usage', compact('machines', 'stats'));
    }
}
