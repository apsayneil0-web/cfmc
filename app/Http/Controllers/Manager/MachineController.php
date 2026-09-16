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

        if ($request->filled('type')) {
            $machines = $machines->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = mb_strtolower($request->string('search'));
            $machines = $machines->filter(fn (Machine $m) => str_starts_with(mb_strtolower($m->name), $search)
                || str_starts_with(mb_strtolower((string) $m->type), $search)
                || str_starts_with(mb_strtolower((string) $m->brand), $search)
                || str_starts_with(mb_strtolower((string) $m->serial_number), $search));
        }

        $machines = $machines->sortByDesc('created_at')->values();

        $existingTypes = Machine::whereNull('archived_at')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');

        // Live "next serial" preview per known type, shown as a placeholder
        // hint on the Add/Edit form — the server still generates the real
        // one at save time, so a stale preview here is harmless.
        $serialPreview = $existingTypes->mapWithKeys(fn ($type) => [$type => Machine::generateSerialNumber($type)]);

        return view('manager.machinery', compact('machines', 'stats', 'existingTypes', 'serialPreview'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        if (empty($validated['serial_number'])) {
            $validated['serial_number'] = Machine::generateSerialNumber($validated['type']);
        }

        Machine::create($validated);

        return redirect()->route('manager.machinery')
            ->with('success', "{$validated['name']} ({$validated['type']}) added to the machinery fleet — serial {$validated['serial_number']}.");
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
            'type' => 'required|string|max:255',
            'name' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) use ($excludeId) {
                    $duplicate = Machine::whereNull('archived_at')
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($value))])
                        ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                        ->first();

                    if ($duplicate) {
                        $fail("A machine named \"{$duplicate->name}\" already exists (MCH-".str_pad((string) $duplicate->id, 3, '0', STR_PAD_LEFT).'). Give this unit its own name, e.g. "'.$duplicate->name.' 2".');
                    }
                },
            ],
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
