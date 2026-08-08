<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    /**
     * Fleet roster only — add/edit/archive. Usage hours, status, and
     * maintenance tier are monitoring concerns handled by
     * MachineUsageController on a separate page.
     */
    public function index(Request $request)
    {
        $machines = Machine::whereNull('archived_at')->get();

        $stats = [
            'total' => $machines->count(),
            'total_units' => $machines->sum('quantity'),
            'with_operator' => $machines->whereNotNull('assigned_operator')->count(),
            'archived' => Machine::whereNotNull('archived_at')->count(),
        ];

        if ($request->filled('search')) {
            $search = mb_strtolower($request->string('search'));
            $machines = $machines->filter(fn (Machine $m) => str_contains(mb_strtolower($m->name), $search)
                || str_contains(mb_strtolower((string) $m->brand), $search)
                || str_contains(mb_strtolower((string) $m->serial_number), $search));
        }

        $machines = $machines->sortByDesc('created_at')->values();

        return view('manager.machinery', compact('machines', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        Machine::create($validated);

        return redirect()->route('manager.machinery')
            ->with('success', "{$validated['name']} added to the machinery fleet.");
    }

    public function update(Request $request, Machine $machine)
    {
        $validated = $this->validateRequest($request, $machine->id);

        $machine->update($validated);

        return redirect()->route('manager.machinery')
            ->with('success', "{$machine->name} updated.");
    }

    public function archive(Machine $machine)
    {
        $machine->update(['archived_at' => now()]);

        return redirect()->route('manager.machinery')
            ->with('success', "{$machine->name} archived.");
    }

    private function validateRequest(Request $request, ?int $excludeId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'serial_number' => [
                'nullable', 'string', 'max:255',
                'unique:machines,serial_number,'.($excludeId ?? 'NULL').',id',
            ],
            'quantity' => 'required|integer|min:1',
            'daily_hectare_limit' => 'required|numeric|min:0.1|max:9999.99',
            'assigned_operator' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
    }
}
