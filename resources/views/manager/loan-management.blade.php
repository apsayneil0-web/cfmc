@extends('manager.layout')

@section('title', 'Loan Management')
@section('header', 'Loan Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Active Loans" value="12" icon="fa-file-invoice-dollar" color="primary" />
    <x-stat-card label="Total Outstanding" value="₱450,000" icon="fa-hand-holding-usd" color="danger" />
    <x-stat-card label="Due This Month" value="₱85,000" icon="fa-calendar-day" color="warning" />
    <x-stat-card label="Interest Earned" value="₱35,000" icon="fa-chart-line" color="success" />
</div>

<!-- Active Loans Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Active Loans</h3>
        </x-slot:filters>
        <x-slot:actions>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option value="current">Current</option>
                <option value="overdue">Overdue</option>
                <option value="completed">Completed</option>
            </select>
        </x-slot:actions>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Principal</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Remaining Balance</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Monthly Due</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Next Due</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Interest (2%)</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-002</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4">₱30,000</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱24,500</td>
                    <td class="px-4 px-md-6 py-4 text-muted">₱5,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 15, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-warning">+₱490</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Current" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-money-bill-wave" color="success" title="Record Payment" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-004</td>
                    <td class="px-4 px-md-6 py-4">Roberto Tan</td>
                    <td class="px-4 px-md-6 py-4">₱100,000</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱85,000</td>
                    <td class="px-4 px-md-6 py-4 text-muted">₱8,333</td>
                    <td class="px-4 px-md-6 py-4 text-danger">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-warning">+₱1,700</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Overdue" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-money-bill-wave" color="success" title="Record Payment" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-005</td>
                    <td class="px-4 px-md-6 py-4">Ana Garcia</td>
                    <td class="px-4 px-md-6 py-4">₱45,000</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱37,500</td>
                    <td class="px-4 px-md-6 py-4 text-muted">₱3,750</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 20, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-warning">+₱750</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Current" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-money-bill-wave" color="success" title="Record Payment" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-10 of 12 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- Interest Computation Info -->
<x-info-banner variant="info" title="Interest Computation: 2% every due date" class="mt-6">
    Interest is automatically calculated and added to the loan balance on each due date.
</x-info-banner>
@endsection
