@extends('manager.layout')

@section('title', 'CBU Management')
@section('header', 'CBU Management')

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
    <x-stat-card label="Total Contributions" value="{{ peso($stats['total_contributions']) }}" icon="fa-piggy-bank" color="primary" />
    <x-stat-card label="Total Balance" value="{{ peso($stats['total_balance']) }}" icon="fa-wallet" color="info" />
    <x-stat-card label="Active Members" value="{{ $stats['active_members'] }}" icon="fa-users" color="success" />
    <x-stat-card label="This Month" value="+{{ peso($stats['this_month']) }}" icon="fa-arrow-trend-up" color="success" />
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
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCbuEntryModal">
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
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">FM-{{ str_pad($transaction->cbu->farmer_id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $transaction->cbu->farmer->full_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $transaction->category ?? ($transaction->type === 'contribution' ? 'Contribution' : 'Expense') }}</td>
                    <td class="px-4 px-md-6 py-4 {{ $transaction->type === 'contribution' ? 'text-success' : 'text-danger' }} fw-medium">
                        {{ $transaction->type === 'contribution' ? '+' : '-' }}{{ peso($transaction->amount) }}
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($transaction->balance_after) }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$transaction->cbu->status" /></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">No CBU entries recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Entry Modal -->
<div class="modal fade" id="addCbuEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-piggy-bank me-2"></i>Add CBU Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.cbu.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                        <select name="farmer_id" class="form-select" required>
                            <option value="" disabled selected>Select a farmer</option>
                            @foreach($farmers as $farmer)
                            <option value="{{ $farmer->id }}">FM-{{ str_pad($farmer->id, 3, '0', STR_PAD_LEFT) }} — {{ $farmer->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Entry Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="contribution">Contribution</option>
                            <option value="expense">Expense (charged against CBU balance)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Monthly Contribution, Share Capital, Fertilizer Subsidy">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Optional remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
