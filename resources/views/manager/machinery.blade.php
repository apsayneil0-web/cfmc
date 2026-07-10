@extends('manager.layout')

@section('title', 'Machinery')
@section('header', 'Machinery Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Machinery" value="11" icon="fa-cogs" color="secondary" />
    <x-stat-card label="Available" value="8" icon="fa-check-circle" color="success" />
    <x-stat-card label="In Use" value="2" icon="fa-tractor" color="primary" />
    <x-stat-card label="Under Maintenance" value="1" icon="fa-wrench" color="danger" />
</div>

<!-- Machinery Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search machinery..." class="form-control ps-5" style="min-width: 220px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option>Available</option>
                <option>In Use</option>
                <option>Maintenance</option>
            </select>
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-primary d-flex align-items-center gap-2">
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
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Usage Hours</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Assigned Operator</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">MCH-001</td>
                    <td class="px-4 px-md-6 py-4">Harvester</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Kubota</td>
                    <td class="px-4 px-md-6 py-4 text-muted">KB-HV-2023-001</td>
                    <td class="px-4 px-md-6 py-4 text-muted">1</td>
                    <td class="px-4 px-md-6 py-4"><span class="text-success fw-medium">245 hrs</span></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Available" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">-</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-wrench" color="secondary" title="Maintenance History" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">MCH-002</td>
                    <td class="px-4 px-md-6 py-4">Tractor</td>
                    <td class="px-4 px-md-6 py-4 text-muted">John Deere</td>
                    <td class="px-4 px-md-6 py-4 text-muted">JD-TR-2022-015</td>
                    <td class="px-4 px-md-6 py-4 text-muted">1</td>
                    <td class="px-4 px-md-6 py-4">
                        <span class="text-warning fw-medium">480 hrs</span>
                        <span class="ms-1 text-danger small"><i class="fas fa-exclamation-triangle"></i></span>
                    </td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Maintenance" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">-</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-wrench" color="secondary" title="Maintenance History" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">MCH-003</td>
                    <td class="px-4 px-md-6 py-4">Tractor</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Kubota</td>
                    <td class="px-4 px-md-6 py-4 text-muted">KB-TR-2023-008</td>
                    <td class="px-4 px-md-6 py-4 text-muted">1</td>
                    <td class="px-4 px-md-6 py-4"><span class="text-success fw-medium">180 hrs</span></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="In Use" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">Carlos Mendoza</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-wrench" color="secondary" title="Maintenance History" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">MCH-004</td>
                    <td class="px-4 px-md-6 py-4">Pump Boat</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Honda</td>
                    <td class="px-4 px-md-6 py-4 text-muted">HN-PB-2021-003</td>
                    <td class="px-4 px-md-6 py-4 text-muted">2</td>
                    <td class="px-4 px-md-6 py-4"><span class="text-success fw-medium">320 hrs</span></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Available" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">-</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-wrench" color="secondary" title="Maintenance History" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-10 of 11 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- Maintenance Alert -->
<x-info-banner variant="danger" title="Maintenance Alert" class="mt-6">
    Tractor #002 (MCH-002) has reached 480 hours and requires maintenance.
</x-info-banner>
@endsection
