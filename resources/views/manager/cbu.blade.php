@extends('manager.layout')

@section('title', 'CBU Management')
@section('header', 'CBU Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Contributions" value="₱1,250,000" icon="fa-piggy-bank" color="primary" />
    <x-stat-card label="Total Balance" value="₱890,000" icon="fa-wallet" color="info" />
    <x-stat-card label="Active Members" value="156" icon="fa-users" color="success" />
    <x-stat-card label="This Month" value="+₱125,000" icon="fa-arrow-trend-up" color="success" />
</div>

<!-- CBU Records -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search members..." class="form-control ps-5" style="min-width: 240px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Members</option>
                <option>Active</option>
                <option>Inactive</option>
            </select>
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-primary d-flex align-items-center gap-2">
                <i class="fas fa-plus"></i><span>Add Entry</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Member ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Member Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Contribution Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Running Balance</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">FM-001</td>
                    <td class="px-4 px-md-6 py-4">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Monthly</td>
                    <td class="px-4 px-md-6 py-4">₱1,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱12,500</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Active" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">FM-002</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Monthly</td>
                    <td class="px-4 px-md-6 py-4">₱800</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱8,400</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Active" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">FM-003</td>
                    <td class="px-4 px-md-6 py-4">Pedro Reyes</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Share Capital</td>
                    <td class="px-4 px-md-6 py-4">₱5,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 05, 2026</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱45,000</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Active" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-10 of 156 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>
@endsection
