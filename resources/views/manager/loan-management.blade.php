@extends('manager.layout')

@section('title', 'Loan Management')
@section('header', 'Loan Management')

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
    <x-stat-card label="Active Loans" value="{{ $stats['active_count'] }}" icon="fa-file-invoice-dollar" color="primary" />
    <x-stat-card label="Total Outstanding" value="&#8369;{{ number_format($stats['total_outstanding'], 2) }}" icon="fa-hand-holding-usd" color="danger" />
    <x-stat-card label="Due This Month" value="&#8369;{{ number_format($stats['due_this_month'], 2) }}" icon="fa-calendar-day" color="warning" />
    <x-stat-card label="Interest Earned" value="&#8369;{{ number_format($stats['interest_earned'], 2) }}" icon="fa-chart-line" color="success" />
</div>

<!-- Loans Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Loans</h3>
            <form method="GET" action="{{ route('manager.loan-management') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search farmer..." class="form-control ps-5" style="min-width: 200px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="fully_paid" {{ request('status') == 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('manager.loan-management') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
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
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">
                        {{ $loan->farmer->full_name }}
                        @if($loan->loanRequest->type === 'batch')
                        <span class="badge bg-info-subtle text-info border border-info-subtle ms-1">{{ $loan->loanRequest->batch?->label ?? 'Batch' }}</span>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4">&#8369;{{ number_format($loan->principal_amount, 2) }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">&#8369;{{ number_format($loan->remaining_balance, 2) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">&#8369;{{ number_format($loan->monthly_due, 2) }}</td>
                    <td class="px-4 px-md-6 py-4 {{ $loan->status === 'overdue' ? 'text-danger' : 'text-muted' }}">{{ $loan->next_due_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$loan->status === 'fully_paid' ? 'Fully Paid' : ucfirst($loan->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $loan->id }}" />
                            @if(!in_array($loan->status, ['fully_paid', 'archived']))
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $loan->id }}" />
                            @endif
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $loan->id }}" />
                        </div>
                    </td>
                </tr>

                <!-- View Modal (details + payment history) -->
                <x-modal id="viewModal{{ $loan->id }}" title="Loan Details">
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="text-muted small d-block">Loan ID</label><p class="fw-medium mb-0">LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $loan->farmer->full_name }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Loan Type</label><p class="fw-medium mb-0">{{ $loan->loanRequest->type === 'batch' ? ($loan->loanRequest->batch?->label ?? 'Batch') : 'Regular Loan' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Principal Amount</label><p class="fw-medium mb-0">&#8369;{{ number_format($loan->principal_amount, 2) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Remaining Balance</label><p class="fw-medium mb-0">&#8369;{{ number_format($loan->remaining_balance, 2) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $loan->repayment_terms_months }} months</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Interest Rate</label><p class="fw-medium mb-0">{{ $loan->interest_rate }}% per due date</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $loan->collateral ?? '—' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="$loan->status === 'fully_paid' ? 'Fully Paid' : ucfirst($loan->status)" /></div>
                        @if($loan->notes)
                        <div class="col-12"><label class="text-muted small d-block">Notes</label><p class="fw-medium mb-0">{{ $loan->notes }}</p></div>
                        @endif
                    </div>
                    <h4 class="text-sm fw-semibold text-dark mb-2">Payment &amp; Interest History</h4>
                    <div class="table-responsive" style="max-height: 240px;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Date</th>
                                    <th class="small">Type</th>
                                    <th class="small">Amount</th>
                                    <th class="small">Balance After</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loan->payments->sortByDesc('transaction_date') as $payment)
                                <tr>
                                    <td class="small">{{ $payment->transaction_date->format('M d, Y') }}</td>
                                    <td class="small">
                                        @if($payment->type === 'payment')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Payment</span>
                                        @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Interest</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $payment->type === 'payment' ? '-' : '+' }}&#8369;{{ number_format($payment->amount, 2) }}</td>
                                    <td class="small">&#8369;{{ number_format($payment->balance_after, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted small py-3">No transactions yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-modal>

                @if(!in_array($loan->status, ['fully_paid', 'archived']))
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-2"></i>Edit Loan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.loan-management.update', $loan) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Next Due Date <span class="text-danger">*</span></label>
                                        <input type="date" name="next_due_date" class="form-control" value="{{ $loan->next_due_date->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Collateral</label>
                                        <input type="text" name="collateral" class="form-control" value="{{ $loan->collateral }}">
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">Notes</label>
                                        <textarea name="notes" rows="2" class="form-control">{{ $loan->notes }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Archive Modal -->
                <div class="modal fade" id="archiveModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-archive me-2"></i>Archive Loan</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Archive LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }} for {{ $loan->farmer->full_name }}? It will be removed from the active list but kept for auditing.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.loan-management.archive', $loan) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary">Archive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No loans found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Interest Computation Info -->
<x-info-banner variant="info" title="Interest Computation: 2% every due date" class="mt-6">
    Interest is automatically calculated and added to the loan balance whenever a due date passes without payment.
</x-info-banner>
@endsection
