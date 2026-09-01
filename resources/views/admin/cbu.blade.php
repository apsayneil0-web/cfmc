@extends('admin.layout')

@section('title', 'Capital Build-Up')
@section('header', 'Capital Build-Up')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Total Balance" value="{{ peso($stats['total_balance']) }}" icon="fa-wallet" color="info" />
    <x-stat-card label="Total Contributions" value="{{ peso($stats['total_contributions']) }}" icon="fa-piggy-bank" color="primary" />
    <x-stat-card label="Active Members" value="{{ $stats['active_members'] }}" icon="fa-users" color="success" />
</div>

<!-- CBU by Farmer -->
<div class="section-card">
    <div class="table-toolbar">
        <div class="position-relative">
            <input type="text" placeholder="Search members..." class="form-control ps-5" style="min-width: 240px;">
            <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Member ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Member Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Balance</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($farmers as $farmer)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $farmer->full_name }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($farmer->cbu->balance ?? 0) }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$farmer->cbu->status ?? 'inactive'" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <x-icon-button icon="fa-eye" color="primary" title="View CBU Ledger" data-bs-toggle="modal" data-bs-target="#viewCbuModal{{ $farmer->id }}" />
                    </td>
                </tr>

                <x-modal id="viewCbuModal{{ $farmer->id }}" title="{{ $farmer->full_name }} — CBU Ledger" size="modal-lg">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4"><label class="text-muted small d-block">Member ID</label><p class="fw-medium mb-0">FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6 col-md-4"><label class="text-muted small d-block">Current Balance</label><p class="fw-medium mb-0">{{ peso($farmer->cbu->balance ?? 0) }}</p></div>
                        <div class="col-6 col-md-4"><label class="text-muted small d-block">Status</label><p class="fw-medium mb-0"><x-status-badge :status="$farmer->cbu->status ?? 'inactive'" /></p></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3 py-2 text-xs font-medium text-uppercase text-muted">Date</th>
                                    <th class="px-3 py-2 text-xs font-medium text-uppercase text-muted">Type</th>
                                    <th class="px-3 py-2 text-xs font-medium text-uppercase text-muted">Amount</th>
                                    <th class="px-3 py-2 text-xs font-medium text-uppercase text-muted">Balance After</th>
                                    <th class="px-3 py-2 text-xs font-medium text-uppercase text-muted">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($farmer->cbu->transactions ?? collect()) as $transaction)
                                <tr>
                                    <td class="px-3 py-2 text-muted">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->category ?? ($transaction->type === 'contribution' ? 'Contribution' : 'Expense') }}</td>
                                    <td class="px-3 py-2 {{ $transaction->type === 'contribution' ? 'text-success' : 'text-danger' }} fw-medium">
                                        {{ $transaction->type === 'contribution' ? '+' : '-' }}{{ peso($transaction->amount) }}
                                    </td>
                                    <td class="px-3 py-2 fw-medium text-dark">{{ peso($transaction->balance_after) }}</td>
                                    <td class="px-3 py-2 text-muted">{{ $transaction->notes ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-muted">No CBU entries recorded for this farmer yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-modal>
                @empty
                <tr>
                    <td colspan="5" class="px-4 px-md-6 py-6 text-center text-muted">No approved farmers on file.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-info-banner variant="info" title="Monitoring Only" class="mt-6">
    This view is for oversight of every farmer's CBU account. Contributions and expenses are recorded by the Manager.
</x-info-banner>
@endsection
