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

@php
    $statusName = fn ($status) => ucwords(str_replace('_', ' ', $status));
    $delinquencyLabels = ['grace' => '2% Grace Interest', 'penalized' => '10% Penalty · Restricted', 'barangay_summon' => 'Barangay Summons', 'legal_action' => 'Legal Action'];
    $loanTabs = ['all' => 'All', 'pending_disbursement' => 'Pending Disbursement', 'active' => 'Active', 'overdue' => 'Overdue', 'fully_paid' => 'Fully Paid'];
    $tabCount = fn ($status) => $status === 'all' ? $statusCounts->sum() : (int) ($statusCounts[$status] ?? 0);
@endphp

<p class="text-sm text-gray-500 mb-4">
    Release approved regular loans and track what each farmer still owes.
    Batch loans are tracked in <a href="{{ route('manager.batch-loan-management') }}" class="text-brand">Batch Loans</a>.
</p>

<!-- Summary -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-5">
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-4 {{ $stats['pending_disbursement_count'] > 0 ? 'stat-card-highlight' : '' }}">
        <p class="text-sm {{ $stats['pending_disbursement_count'] > 0 ? 'text-warning fw-semibold' : 'text-gray-500' }} mb-1">Pending Disbursement</p>
        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['pending_disbursement_count'] }} {{ Str::plural('loan', $stats['pending_disbursement_count']) }}</p>
        @if($stats['pending_disbursement_count'] > 0)
        <p class="text-xs text-gray-500 mb-0">Waiting to be released to farmers</p>
        @endif
    </div>
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 mb-1">Active Loans</p>
        <p class="text-xl font-bold text-gray-900 mb-0">{{ number_format($stats['active_count']) }}</p>
    </div>
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 mb-1">Total Outstanding</p>
        <p class="text-xl font-bold text-gray-900 mb-0">{{ peso($stats['total_outstanding']) }}</p>
    </div>
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 mb-1">Due This Month</p>
        <p class="text-xl font-bold text-gray-900 mb-0">{{ peso($stats['due_this_month']) }}</p>
    </div>
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-sm text-gray-500 mb-1">Interest Earned</p>
        <p class="text-xl font-bold text-gray-900 mb-0">{{ peso($stats['interest_earned']) }}</p>
    </div>
</div>

<!-- Loans -->
<div class="section-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 px-md-5 py-4">
        @if($showArchived)
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Archived loans</h3>
        @else
        <div class="segmented" role="group" aria-label="Filter by loan status">
            @foreach($loanTabs as $value => $label)
            <a href="{{ route('manager.loan-management', array_filter(['status' => $value, 'search' => request('search')])) }}"
                class="{{ $currentStatus === $value ? 'active' : '' }}">{{ $label }} <span class="segmented-count">{{ $tabCount($value) }}</span></a>
            @endforeach
        </div>
        @endif
        <div class="d-flex flex-wrap align-items-center gap-3">
            <form method="GET" action="{{ route('manager.loan-management') }}" id="loanManagementFilterForm" class="position-relative m-0">
                @if($showArchived)
                <input type="hidden" name="archived" value="1">
                @else
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <input type="text" name="search" id="loanManagementSearchInput" value="{{ request('search') }}" placeholder="Search farmer" class="form-control ps-5" style="min-width: 240px;" autocomplete="off">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </form>
            @if($showArchived)
            <a href="{{ route('manager.loan-management') }}" class="text-sm fw-semibold text-brand text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to Active</a>
            @else
            <a href="{{ route('manager.loan-management', ['archived' => 1]) }}" class="text-sm fw-semibold text-brand text-decoration-none">View Archived ({{ $stats['archived_count'] }})</a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted text-end">Principal</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted text-end">Remaining Balance</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted text-end">Monthly Due</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Next Due</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                @php
                    $isPending = $loan->status === 'pending_disbursement' && ! $loan->archived_at;
                    $hasSchedule = in_array($loan->status, ['active', 'overdue'], true) && ! $loan->archived_at;
                @endphp
                <tr>
                    <td class="px-4 px-md-5 py-3">
                        <p class="text-sm fw-semibold text-gray-900 mb-0">{{ $loan->farmer->full_name }}</p>
                        <p class="text-xs text-gray-500 mb-0">{{ $loan->repayment_terms_months }} months{{ $loan->effective_term_months > $loan->repayment_terms_months ? ' · now month '.$loan->current_installment_number : '' }}</p>
                    </td>
                    <td class="px-3 py-3 text-sm text-end text-gray-700">{{ peso($loan->principal_amount) }}</td>
                    <td class="px-3 py-3 text-sm text-end fw-semibold text-gray-900">{{ $loan->remaining_balance !== null && $loan->status !== 'pending_disbursement' ? peso($loan->remaining_balance) : '—' }}</td>
                    <td class="px-3 py-3 text-sm text-end text-gray-700">{{ $loan->status === 'fully_paid' ? '—' : peso($loan->amount_due) }}</td>
                    <td class="px-3 py-3 text-sm {{ $loan->status === 'overdue' ? 'text-danger fw-semibold' : 'text-gray-700' }}">{{ $hasSchedule ? ($loan->next_due_date?->format('M j, Y') ?? '—') : '—' }}</td>
                    <td class="px-3 py-3">
                        <x-status-badge :status="$loan->archived_at ? 'Archived' : $statusName($loan->status)" />
                        @if($isPending && $loan->scheduled_disbursement_date)
                        <div class="text-xs text-gray-500 mt-1">Auto-disburses {{ $loan->scheduled_disbursement_date->format('M j, Y') }}</div>
                        @elseif($loan->delinquency_stage && ! $loan->archived_at)
                        <div class="text-xs fw-semibold text-danger mt-1">{{ $delinquencyLabels[$loan->delinquency_stage] ?? '' }}</div>
                        @endif
                    </td>
                    <td class="px-4 px-md-5 py-3 text-end text-nowrap">
                        @if($isPending)
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#disburseModal{{ $loan->id }}">Mark Disbursed</button>
                        @else
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $loan->id }}">View</button>
                        @endif
                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($isPending)
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewModal{{ $loan->id }}"><i class="fas fa-eye me-2 text-gray-500"></i>View</button></li>
                                @endif
                                @if(! $loan->archived_at)
                                    @if(! in_array($loan->status, ['fully_paid', 'pending_disbursement'], true))
                                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal{{ $loan->id }}"><i class="fas fa-pen me-2 text-gray-500"></i>Edit loan</button></li>
                                    @endif
                                    @if($isPending || $loan->status !== 'fully_paid')
                                    <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li><button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $loan->id }}"><i class="fas fa-archive me-2"></i>Archive loan</button></li>
                                @else
                                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#unarchiveModal{{ $loan->id }}"><i class="fas fa-box-open me-2 text-success"></i>Restore loan</button></li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">{{ $showArchived ? 'No archived loans.' : 'No loans found.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination-footer :paginator="$loans" />
</div>

{{-- Modals rendered outside <tbody>: a <div> is not valid directly inside a
     table body, and browsers "correct" that by ejecting everything after the
     first row's modals out of the table, breaking every row after the first. --}}
@foreach($loans as $loan)
<!-- View Modal (details + payment history) -->
<x-modal id="viewModal{{ $loan->id }}" title="Loan Details" size="modal-lg modal-dialog-scrollable">
    <div class="row g-3 mb-3">
        <div class="d-none col-6"><label class="text-muted small d-block">Loan ID</label><p class="fw-medium mb-0">LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $loan->farmer->full_name }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Loan Type</label><p class="fw-medium mb-0">Regular Loan</p></div>
        <div class="col-6"><label class="text-muted small d-block">Principal Amount</label><p class="fw-medium mb-0">{{ peso($loan->principal_amount) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Remaining Balance</label><p class="fw-medium mb-0">{{ $loan->remaining_balance !== null ? peso($loan->remaining_balance) : '—' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $loan->repayment_terms_months }} months{{ $loan->effective_term_months > $loan->repayment_terms_months ? ' — extended, now on month '.$loan->current_installment_number : '' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Interest Rate</label><p class="fw-medium mb-0">{{ $loan->interest_rate }}% per due date</p></div>
        <div class="col-6"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $loan->collateral ?? '—' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucwords(str_replace('_', ' ', $loan->status))" /></div>
        @if($loan->disbursed_at)
        <div class="col-6"><label class="text-muted small d-block">Disbursed</label><p class="fw-medium mb-0">{{ $loan->disbursed_at->format('M d, Y') }} &mdash; {{ ucwords(str_replace('_', ' ', $loan->disbursement_method)) }}{{ $loan->reference_no ? ' (Ref: '.$loan->reference_no.')' : '' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Released By</label><p class="fw-medium mb-0">{{ $loan->disbursedBy->name ?? '—' }}</p></div>
        @elseif($loan->status === 'pending_disbursement' && $loan->scheduled_disbursement_date)
        <div class="col-6"><label class="text-muted small d-block">Scheduled Disbursement</label><p class="fw-medium mb-0">{{ $loan->scheduled_disbursement_date->format('M d, Y') }} &mdash; {{ ucwords(str_replace('_', ' ', $loan->disbursement_method ?? 'cash')) }}{{ $loan->reference_no ? ' (Ref: '.$loan->reference_no.')' : '' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Scheduled By</label><p class="fw-medium mb-0">{{ $loan->scheduledBy->name ?? '—' }}</p></div>
        @endif
        @if($loan->notes)
        <div class="col-12"><label class="text-muted small d-block">Notes</label><p class="fw-medium mb-0">{{ $loan->notes }}</p></div>
        @endif
    </div>

    @if($loan->original_due_date)
    <h4 class="text-sm fw-semibold text-dark mb-2">Delinquency &amp; Penalties</h4>
    <div class="row g-3 mb-3">
        <div class="col-6"><label class="text-muted small d-block">Original Due Date</label><p class="fw-medium mb-0">{{ $loan->original_due_date->format('M d, Y') }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Grace Period Ends</label><p class="fw-medium mb-0">{{ $loan->original_due_date->copy()->addDays(30)->format('M d, Y') }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">2% Grace Interest</label><p class="fw-medium mb-0">{{ $loan->partial_penalty_applied_at?->format('M d, Y') ?? 'Not applied' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">10% Grace Penalty</label><p class="fw-medium mb-0">{{ $loan->grace_penalty_applied_at?->format('M d, Y') ?? 'Not applied' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Barangay Summons</label><p class="fw-medium mb-0">{{ $loan->barangay_summon_at?->format('M d, Y') ?? 'Not flagged' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Legal Action</label><p class="fw-medium mb-0">{{ $loan->legal_action_at?->format('M d, Y') ?? 'Not flagged' }}</p></div>
        @if($loan->is_restricted)
        <div class="col-12"><p class="fw-medium mb-0 text-danger"><i class="fas fa-ban me-1"></i> This farmer is restricted from new loan requests until this balance is fully paid.</p></div>
        @endif
    </div>
    @endif

    <h4 class="text-sm fw-semibold text-dark mb-2">Payment &amp; Interest History</h4>
    <div class="table-responsive">
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
                @forelse($loan->payments->sortByDesc('created_at') as $payment)
                <tr>
                    <td class="small">{{ $payment->transaction_date->format('M d, Y') }}</td>
                    <td class="small">
                        @if($payment->type === 'payment')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Payment</span>
                        @elseif($payment->type === 'prepayment')
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Prepayment</span>
                        @elseif($payment->type === 'partial')
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Partial Payment</span>
                        @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Interest</span>
                        @endif
                    </td>
                    <td class="small">{{ $payment->type === 'interest' ? '+' : '-' }}{{ peso($payment->amount) }}</td>
                    <td class="small">{{ peso($payment->balance_after) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted small py-3">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-modal>

@if($loan->status === 'pending_disbursement')
<!-- Disburse Modal -->
<div class="modal fade" id="disburseModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-money-check-alt me-2"></i>Mark as Disbursed</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.loan-management.disburse', $loan) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p class="text-muted small">Confirm that {{ $loan->principal_amount ? peso($loan->principal_amount) : '' }} has been released to {{ $loan->farmer->full_name }}. Choosing today disburses immediately; choosing a future date schedules it to disburse automatically that day. Either way, the repayment schedule starts from the date below.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Disbursement Date <span class="text-danger">*</span></label>
                        <input type="date" name="disbursed_at" class="form-control" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Disbursement Method</label>
                        <input type="text" class="form-control" value="Cash" disabled>
                        <input type="hidden" name="disbursement_method" value="cash">
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Reference No.</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="Receipt/voucher no. (optional)">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Disbursement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(!$loan->archived_at && !in_array($loan->status, ['fully_paid', 'pending_disbursement']))
<!-- Edit Modal -->
<div class="modal fade" id="editModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Loan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(!$loan->archived_at)
<!-- Archive Modal -->
<div class="modal fade" id="archiveModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-archive me-2"></i>Archive Loan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Archive this loan for {{ $loan->farmer->full_name }}? It will be removed from the active list but kept for auditing.</p>
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
@endif

@if($loan->archived_at)
<!-- Unarchive (Restore) Modal -->
<div class="modal fade" id="unarchiveModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2"></i>Restore Loan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Restore this loan for {{ $loan->farmer->full_name }}? It will reappear on the active list.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('manager.loan-management.unarchive', $loan) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">Restore</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

<x-loan-rules class="mt-6" />

<script>
    // Debounced live search: Status already auto-submits on change, so the
    // search box submits the same way instead of requiring Filter/Enter —
    // waits briefly after typing stops so it doesn't reload on every keystroke.
    (function () {
        var searchInput = document.getElementById('loanManagementSearchInput');
        var form = document.getElementById('loanManagementFilterForm');
        if (!searchInput || !form) {
            return;
        }

        var debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                form.submit();
            }, 500);
        });

        // Restore focus (cursor at the end) after a search reloads the page,
        // so typing feels continuous instead of resetting to the top.
        searchInput.focus();
        if (searchInput.value) {
            var restoredValue = searchInput.value;
            searchInput.value = '';
            searchInput.value = restoredValue;
        }
    })();
</script>
@endsection
