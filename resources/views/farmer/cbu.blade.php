@extends('farmer.layout')

@section('title', 'CBU Records')
@section('header', 'My CBU Records')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Total Contributions" value="{{ peso($stats['total_contributions']) }}" icon="fa-piggy-bank" color="primary" />
    <x-stat-card label="Total Expenses Charged" value="{{ peso($stats['total_expenses']) }}" icon="fa-receipt" color="warning" />
    <x-stat-card label="Current CBU Balance" value="{{ peso($stats['balance']) }}" icon="fa-wallet" color="success" />
</div>

<!-- Contribution History -->
<div class="section-card mb-6">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Contribution History</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-mobile-cards">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Running Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contributions as $contribution)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Date">{{ $contribution->transaction_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4" data-label="Type">{{ $contribution->category ?? 'Contribution' }}</td>
                    <td class="px-4 px-md-6 py-4 text-success fw-medium" data-label="Amount">+{{ peso($contribution->amount) }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark" data-label="Running Balance">{{ peso($contribution->balance_after) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 px-md-6 py-6 text-center text-muted">No contributions recorded yet.</td>
                </tr>
                @endforelse
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
        <table class="table table-hover mb-0 table-mobile-cards">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Date">{{ $expense->transaction_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4" data-label="Purpose">{{ $expense->category ?? 'Expense' }}</td>
                    <td class="px-4 px-md-6 py-4 text-danger fw-medium" data-label="Amount">-{{ peso($expense->amount) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 px-md-6 py-6 text-center text-muted">No CBU-funded expenses recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-info-banner variant="info" title="Monitoring Only" class="mt-6">
    This is a read-only view of your CBU records. Contributions and expenses are recorded by cooperative management.
</x-info-banner>
@endsection
