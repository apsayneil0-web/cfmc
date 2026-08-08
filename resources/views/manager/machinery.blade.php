@extends('manager.layout')

@section('title', 'Machinery')
@section('header', 'Machinery Management')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Machinery" value="{{ $stats['total'] }}" icon="fa-cogs" color="secondary" />
    <x-stat-card label="Total Units" value="{{ $stats['total_units'] }}" icon="fa-layer-group" color="primary" />
    <x-stat-card label="With Operator Assigned" value="{{ $stats['with_operator'] }}" icon="fa-user-check" color="success" />
    <x-stat-card label="Archived" value="{{ $stats['archived'] }}" icon="fa-box-archive" color="secondary" />
</div>

<!-- Machinery Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <form method="GET" action="{{ route('manager.machinery') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search machinery..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search']))
                <a href="{{ route('manager.machinery') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <a href="{{ route('manager.machine-usage') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fas fa-chart-line"></i><span>Usage Monitor</span>
            </a>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addMachineModal">
                <i class="fas fa-plus"></i><span>Add Machinery</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machine ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machine Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Brand</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Serial Number</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Quantity</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Daily Hectare Limit</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Assigned Operator</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($machines as $machine)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">MCH-{{ str_pad($machine->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $machine->name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $machine->brand ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $machine->serial_number ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $machine->quantity }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $machine->daily_hectare_limit }} ha/day</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $machine->assigned_operator ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewMachineModal{{ $machine->id }}" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editMachineModal{{ $machine->id }}" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveMachineModal{{ $machine->id }}" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No machinery on record yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modals rendered outside <tbody>: a <div> is not valid directly inside a
     table body, and browsers "correct" that by ejecting everything after the
     first row's modals out of the table, breaking every row after the first. --}}
@foreach($machines as $machine)
<!-- View Modal -->
<x-modal id="viewMachineModal{{ $machine->id }}" title="Machine Details">
    <div class="row g-3">
        <div class="col-6"><label class="text-muted small d-block">Machine ID</label><p class="fw-medium mb-0">MCH-{{ str_pad($machine->id, 3, '0', STR_PAD_LEFT) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Machine Name</label><p class="fw-medium mb-0">{{ $machine->name }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Brand</label><p class="fw-medium mb-0">{{ $machine->brand ?? '—' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Serial Number</label><p class="fw-medium mb-0">{{ $machine->serial_number ?? '—' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Quantity</label><p class="fw-medium mb-0">{{ $machine->quantity }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Daily Hectare Limit</label><p class="fw-medium mb-0">{{ $machine->daily_hectare_limit }} ha / day</p></div>
        <div class="col-6"><label class="text-muted small d-block">Assigned Operator</label><p class="fw-medium mb-0">{{ $machine->assigned_operator ?? '—' }}</p></div>
        @if($machine->notes)
        <div class="col-12"><label class="text-muted small d-block">Notes</label><p class="fw-medium mb-0">{{ $machine->notes }}</p></div>
        @endif
        <div class="col-12">
            <a href="{{ route('manager.machine-usage') }}?search={{ urlencode($machine->name) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-chart-line me-1"></i>View Usage &amp; Maintenance
            </a>
        </div>
    </div>
</x-modal>

<!-- Edit Modal -->
<div class="modal fade" id="editMachineModal{{ $machine->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-2"></i>Edit Machine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machinery.update', $machine) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $machine->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand</label>
                            <input type="text" name="brand" class="form-control" value="{{ $machine->brand }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" value="{{ $machine->serial_number }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="1" name="quantity" class="form-control" value="{{ $machine->quantity }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Daily Hectare Limit <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.1" min="0.1" name="daily_hectare_limit" class="form-control" value="{{ $machine->daily_hectare_limit }}" required>
                                <span class="input-group-text">ha/day</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned Operator</label>
                            <input type="text" name="assigned_operator" class="form-control" value="{{ $machine->assigned_operator }}">
                        </div>
                        <div class="col-12">
                            <p class="text-muted small mb-0"><i class="fas fa-circle-info me-1"></i>Usage hours, status, and maintenance tier are tracked automatically from completed bookings — see the <a href="{{ route('manager.machine-usage') }}">Usage Monitor</a> for those.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" rows="2" class="form-control">{{ $machine->notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="archiveMachineModal{{ $machine->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-archive me-2"></i>Archive Machine</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Archive MCH-{{ str_pad($machine->id, 3, '0', STR_PAD_LEFT) }} ({{ $machine->name }})? It will be removed from this list but kept for records.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('manager.machinery.archive', $machine) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-secondary">Archive</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Add Machine Modal -->
<div class="modal fade" id="addMachineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Add Machinery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machinery.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Machine Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Harvester" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Brand</label>
                            <input type="text" name="brand" class="form-control" value="{{ old('brand') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="1" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Daily Hectare Limit <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.1" min="0.1" name="daily_hectare_limit" class="form-control" value="{{ old('daily_hectare_limit', \App\Models\Machine::DEFAULT_DAILY_HECTARE_LIMIT) }}" required>
                                <span class="input-group-text">ha/day</span>
                            </div>
                            <small class="text-muted">Max combined hectares this machine can service across all bookings on one day.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Assigned Operator</label>
                            <input type="text" name="assigned_operator" class="form-control" value="{{ old('assigned_operator') }}">
                        </div>
                        <div class="col-12">
                            <p class="text-muted small mb-0"><i class="fas fa-circle-info me-1"></i>Usage hours and status will be tracked automatically once bookings are scheduled and completed — visible on the <a href="{{ route('manager.machine-usage') }}">Usage Monitor</a>.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Machinery</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
