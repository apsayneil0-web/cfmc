<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Read-only oversight of every active machinery booking. Schedules are
     * created and processed by the Manager (see manager.machine-schedule).
     */
    public function index(Request $request)
    {
        $query = ScheduleRequest::with('user.farmer')
            ->whereNull('archived_at')
            ->visibleAccount()
            ->orderBy('created_at', 'desc');

        // Search by the name shown in the list (linked farmer, account, or
        // walk-in name), the machinery or the location.
        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('farmer_name', 'like', "%{$search}%")
                    ->orWhere('machinery', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user.farmer', fn ($f) => $f->whereRaw(
                        "CONCAT_WS(' ', first_name, middle_initial, last_name, suffix) LIKE ?",
                        ["%{$search}%"]
                    ));
            });
        }

        // Filter by machine type (Tractor, Harvester…). Types were entered with
        // mixed capitalisation, so compare case-insensitively. Schedules saved
        // before machines were linked by id fall back to the machine name.
        if ($request->filled('type')) {
            $type = mb_strtolower(trim($request->string('type')));
            $namesOfType = Machine::whereRaw('LOWER(TRIM(type)) = ?', [$type])
                ->pluck('name')
                ->map(fn ($name) => mb_strtolower($name))
                ->unique()
                ->all();

            $query->where(function ($q) use ($type, $namesOfType) {
                $q->whereHas('machine', fn ($m) => $m->whereRaw('LOWER(TRIM(type)) = ?', [$type]))
                    ->orWhere(fn ($legacy) => $legacy->whereNull('machine_id')
                        ->whereIn(DB::raw('LOWER(machinery)'), $namesOfType ?: ['']));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $schedules = $query->paginate(10)->withQueryString();

        // Every machine type, archived machines included (their schedules still
        // appear here), listed once with consistent capitalisation.
        $machineTypes = Machine::whereNotNull('type')
            ->pluck('type')
            ->map(fn ($type) => ucwords(mb_strtolower(trim($type))))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.schedule', compact('schedules', 'machineTypes'));
    }
}
