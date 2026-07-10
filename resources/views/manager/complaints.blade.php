@extends('manager.layout')

@section('title', 'Complaints')
@section('header', 'Complaints Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Complaints" value="28" icon="fa-exclamation-circle" color="secondary" />
    <x-stat-card label="New" value="5" icon="fa-envelope" color="primary" />
    <x-stat-card label="In Progress" value="8" icon="fa-spinner" color="warning" />
    <x-stat-card label="Resolved" value="15" icon="fa-check-circle" color="success" />
</div>

<!-- Complaints Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search complaints..." class="form-control ps-5" style="min-width: 220px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Types</option>
                <option>Machinery</option>
                <option>Service</option>
                <option>Staff</option>
                <option>Billing</option>
                <option>Other</option>
            </select>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option>New</option>
                <option>In Progress</option>
                <option>Resolved</option>
            </select>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Complaint ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Complaint Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Description</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">CMP-001</td>
                    <td class="px-4 px-md-6 py-4">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4"><span class="badge bg-danger-subtle text-danger border border-danger-subtle">Machinery</span></td>
                    <td class="px-4 px-md-6 py-4 text-muted text-truncate d-inline-block" style="max-width: 220px;">Harvester malfunction during harvest</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="New" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="success" title="Mark as Viewed" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">CMP-002</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4"><span class="badge bg-warning-subtle text-warning border border-warning-subtle">Service</span></td>
                    <td class="px-4 px-md-6 py-4 text-muted text-truncate d-inline-block" style="max-width: 220px;">Delayed schedule response</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 02, 2026</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="In Progress" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="success" title="Mark as Viewed" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">CMP-003</td>
                    <td class="px-4 px-md-6 py-4">Pedro Reyes</td>
                    <td class="px-4 px-md-6 py-4"><span class="badge bg-info-subtle text-info border border-info-subtle">Billing</span></td>
                    <td class="px-4 px-md-6 py-4 text-muted text-truncate d-inline-block" style="max-width: 220px;">Incorrect billing amount</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 03, 2026</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Resolved" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">CMP-004</td>
                    <td class="px-4 px-md-6 py-4">Roberto Tan</td>
                    <td class="px-4 px-md-6 py-4"><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Other</span></td>
                    <td class="px-4 px-md-6 py-4 text-muted text-truncate d-inline-block" style="max-width: 220px;">Feedback on service quality</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 04, 2026</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="New" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-check" color="success" title="Mark as Viewed" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-5 of 28 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>
@endsection
