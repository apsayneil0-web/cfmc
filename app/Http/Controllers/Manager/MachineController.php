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
        $activeMachines = Machine::whereNull('archived_at')->get();
        $showArchived = $request->boolean('archived');

        $stats = [
            'total' => $activeMachines->count(),
            'total_units' => $activeMachines->sum('quantity'),
            'with_operator' => $activeMachines->whereNotNull('assigned_operator')->count(),
            'archived' => Machine::whereNotNull('archived_at')->count(),
        ];

        $query = $showArchived ? Machine::whereNotNull('archived_at') : Machine::whereNull('archived_at');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "{$search}%")
                    ->orWhere('type', 'like', "{$search}%")
                    ->orWhere('brand', 'like', "{$search}%")
                    ->orWhere('serial_number', 'like', "{$search}%");
            });
        }

        $machines = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $existingTypes = Machine::whereNull('archived_at')->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');

        // Live "next serial" preview per known type, shown as a placeholder
        // hint on the Add/Edit form — the server still generates the real
        // one at save time, so a stale preview here is harmless.
        $serialPreview = $existingTypes->mapWithKeys(fn ($type) => [$type => Machine::generateSerialNumber($type)]);

        return view('manager.machinery', compact('machines', 'stats', 'existingTypes', 'serialPreview', 'showArchived'));
    }

    public function store(Request $request)
    {
        // Serial number is auto-generated and not editable on the Add form.
        // Drop whatever the (read-only) field submitted before validating,
        // so a stale client-side preview can never trip the uniqueness
        // check — the real value is always computed fresh below.
        $request->merge(['serial_number' => null]);

        $validated = $this->validateRequest($request);
        $validated['serial_number'] = Machine::generateSerialNumber($validated['type']);

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

    /**
     * Restore an archived machine back into the active fleet, unless another
     * active machine has since taken the same name.
     */
    public function unarchive(Machine $machine)
    {
        $nameTaken = Machine::whereNull('archived_at')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($machine->name))])
            ->exists();

        if ($nameTaken) {
            return redirect()->route('manager.machinery', ['archived' => 1])
                ->withErrors(['name' => "Cannot restore {$machine->name}: an active machine already uses that name. Rename or archive it first."]);
        }

        $machine->update(['archived_at' => null]);

        return redirect()->route('manager.machinery', ['archived' => 1])
            ->with('success', "{$machine->name} restored to the active fleet.");
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
