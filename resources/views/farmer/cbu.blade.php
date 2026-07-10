@extends('farmer.layout')

@section('title', 'CBU Records')
@section('header', 'My CBU Records')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Total Contributions" value="₱12,500" icon="fa-piggy-bank" color="primary" />
    <x-stat-card label="Total Expenses Charged" value="₱2,000" icon="fa-receipt" color="warning" />
    <x-stat-card label="Current CBU Balance" value="₱10,500" icon="fa-wallet" color="success" />
</div>

<!-- Contribution History -->
<div class="section-card mb-6">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Contribution History</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Running Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4">Monthly Contribution</td>
                    <td class="px-4 px-md-6 py-4 text-success fw-medium">+₱1,000</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱10,500</td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">Jun 01, 2026</td>
                    <td class="px-4 px-md-6 py-4">Monthly Contribution</td>
                    <td class="px-4 px-md-6 py-4 text-success fw-medium">+₱1,000</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱9,500</td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">May 15, 2026</td>
                    <td class="px-4 px-md-6 py-4">Share Capital</td>
                    <td class="px-4 px-md-6 py-4 text-success fw-medium">+₱5,000</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱8,500</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- CBU-Funded Expenses -->
<div class="section-card">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">CBU-Funded Expenses</h3>
        <p class="text-sm text-muted mb-0">Expenses recorded by the Manager and sourced from the CBU fund.</p>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted">Jun 20, 2026</td>
                    <td class="px-4 px-md-6 py-4">Fertilizer subsidy</td>
                    <td class="px-4 px-md-6 py-4 text-danger fw-medium">-₱2,000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<x-info-banner variant="info" title="Monitoring Only" class="mt-6">
    This is a read-only view of your CBU records. Contributions and expenses are recorded by cooperative management.
</x-info-banner>
@endsection
