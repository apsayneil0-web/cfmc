@extends('manager.layout')

@section('title', 'Payments')
@section('header', 'Payment Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Loan Payments" value="₱125,000" icon="fa-hand-holding-usd" color="primary" />
    <x-stat-card label="CBU Contributions" value="₱85,000" icon="fa-piggy-bank" color="info" />
    <x-stat-card label="Operational Expenses" value="₱45,000" icon="fa-file-invoice" color="warning" />
    <x-stat-card label="Replaceable Parts" value="₱22,500" icon="fa-cogs" color="danger" />
</div>

<!-- Payments Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search payments..." class="form-control ps-5" style="min-width: 220px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Types</option>
                <option>Loan Payment</option>
                <option>CBU Contribution</option>
                <option>Operational Expense</option>
                <option>Replaceable Parts</option>
            </select>
            <input type="date" class="form-control" style="width: auto;">
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-primary d-flex align-items-center gap-2">
                <i class="fas fa-plus"></i><span>Record Payment</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Transaction ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Payor/Payer</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Payment Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Reference No.</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">PAY-001</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Loan Payment" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">LN-002</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱5,000</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Completed" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">PAY-002</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 01, 2026</td>
                    <td class="px-4 px-md-6 py-4">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="CBU Contribution" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">FM-001</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱1,000</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Completed" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">PAY-003</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 02, 2026</td>
                    <td class="px-4 px-md-6 py-4">Office Operations</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Operational Expense" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">-</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱5,500</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Completed" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">PAY-004</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 03, 2026</td>
                    <td class="px-4 px-md-6 py-4">Maintenance Dept</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Replaceable Parts" /></td>
                    <td class="px-4 px-md-6 py-4 text-muted">-</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">₱12,500</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Completed" /></td>
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
        <p class="text-muted small mb-0">Showing 1-10 of 45 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- Auto-Update Info -->
<x-info-banner variant="success" title="Automatic Updates" class="mt-6">
    Payments automatically update loan balance, CBU balance, and financial records.
</x-info-banner>
@endsection
