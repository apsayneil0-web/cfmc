@extends('manager.layout')

@section('title', 'Machine Usage')
@section('header', 'Machine Usage Monitor')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Machinery" value="{{ $stats['total'] }}" icon="fa-cogs" color="secondary" />
    <x-stat-card label="Available" value="{{ $stats['available'] }}" icon="fa-check-circle" color="success" />
    <x-stat-card label="In Use" value="{{ $stats['in_use'] }}" icon="fa-tractor" color="primary" />
    <x-stat-card label="Under Maintenance" value="{{ $stats['maintenance'] }}" icon="fa-wrench" color="danger" />
</div>

<!-- Usage Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <form method="GET" action="{{ route('manager.machine-usage') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search machinery..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="in_use" {{ request('status') == 'in_use' ? 'selected' : '' }}>In Use</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
                <select name="maintenance" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Maintenance Tiers</option>
                    <option value="none" {{ request('maintenance') == 'none' ? 'selected' : '' }}>Not Yet Used</option>
                    <option value="routine" {{ request('maintenance') == 'routine' ? 'selected' : '' }}>Routine Inspection</option>
                    <option value="basic" {{ request('maintenance') == 'basic' ? 'selected' : '' }}>Basic Maintenance</option>
                    <option value="full" {{ request('maintenance') == 'full' ? 'selected' : '' }}>Full Maintenance</option>
                    <option value="comprehensive" {{ request('maintenance') == 'comprehensive' ? 'selected' : '' }}>Comprehensive Servicing</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status', 'maintenance']))
                <a href="{{ route('manager.machine-usage') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <a href="{{ route('manager.machinery') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fas fa-cogs"></i><span>Manage Fleet</span>
            </a>
        </x-slot:actions>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machine ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machine Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Usage Hours</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Times Used</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Maintenance</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Assigned Operator</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($machines as $machine)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">MCH-{{ str_pad($machine->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $machine->name }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucwords(str_replace('_', ' ', $machine->status))" /></td>
                    <td class="px-4 px-md-6 py-4">{{ $machine->usage_hours }} hrs</td>
                    <td class="px-4 px-md-6 py-4 text-muted" title="Completed bookings for this machine">{{ $machine->times_used }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$machine->maintenance_label" :title="$machine->maintenance_recommendation" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $machine->assigned_operator ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewUsageModal{{ $machine->id }}" />
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

@foreach($machines as $machine)
<x-modal id="viewUsageModal{{ $machine->id }}" title="Usage Details">
    <div class="row g-3">
        <div class="col-6"><label class="text-muted small d-block">Machine ID</label><p class="fw-medium mb-0">MCH-{{ str_pad($machine->id, 3, '0', STR_PAD_LEFT) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Machine Name</label><p class="fw-medium mb-0">{{ $machine->name }}</p></div>
        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucwords(str_replace('_', ' ', $machine->status))" /></div>
        <div class="col-6"><label class="text-muted small d-block">Assigned Operator</label><p class="fw-medium mb-0">{{ $machine->assigned_operator ?? '—' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Usage Hours</label><p class="fw-medium mb-0">{{ $machine->usage_hours }} hrs</p></div>
        <div class="col-6"><label class="text-muted small d-block">Times Used</label><p class="fw-medium mb-0">{{ $machine->times_used }} completed booking(s)</p></div>
        <div class="col-6"><label class="text-muted small d-block">Daily Hectare Limit</label><p class="fw-medium mb-0">{{ $machine->daily_hectare_limit }} ha / day</p></div>
        <div class="col-6"><label class="text-muted small d-block mb-1">Maintenance Tier</label><x-status-badge :status="$machine->maintenance_label" /></div>
        <div class="col-12"><label class="text-muted small d-block">Maintenance Recommendation</label><p class="fw-medium mb-0">{{ $machine->maintenance_recommendation }}</p></div>
    </div>
</x-modal>
@endforeach
@endsection
