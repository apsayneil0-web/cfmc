<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;

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
            $machines = $machines->filter(fn (Machine $m) => str_contains(mb_strtolower($m->name), $search)
                || str_contains(mb_strtolower((string) $m->brand), $search)
                || str_contains(mb_strtolower((string) $m->serial_number), $search));
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

        return view('manager.machine-usage', compact('machines', 'stats'));
    }
}
