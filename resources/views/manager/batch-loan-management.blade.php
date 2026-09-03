@extends('manager.layout')

@section('title', 'Batch Loans')
@section('header', 'Batch Loan Management')

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
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    <x-stat-card label="Pending Disbursement" value="{{ $stats['pending_disbursement_count'] }}" icon="fa-money-check-alt" color="warning" />
    <x-stat-card label="Active Loans" value="{{ $stats['active_count'] }}" icon="fa-file-invoice-dollar" color="primary" />
    <x-stat-card label="Total Outstanding" value="{{ peso($stats['total_outstanding']) }}" icon="fa-hand-holding-usd" color="danger" />
    <x-stat-card label="Due This Month" value="{{ peso($stats['due_this_month']) }}" icon="fa-calendar-day" color="warning" />
    <x-stat-card label="Interest Earned" value="{{ peso($stats['interest_earned']) }}" icon="fa-chart-line" color="success" />
</div>

<!-- Batches Table -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Batch Loans</h3>
            <form method="GET" action="{{ route('manager.batch-loan-management') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search farmer..." class="form-control ps-5" style="min-width: 200px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending_disbursement" {{ request('status') == 'pending_disbursement' ? 'selected' : '' }}>Pending Disbursement</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="fully_paid" {{ request('status') == 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('manager.batch-loan-management') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Batch</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Members</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Total Principal</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Total Outstanding</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status Breakdown</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batchGroups as $group)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $group->batch->label }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $group->loans->count() }} farmer(s)</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($group->loans->sum('principal_amount')) }}</td>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ peso($group->loans->sum('remaining_balance')) }}</td>
                    <td class="px-4 px-md-6 py-4">
                        @foreach($group->loans->groupBy(fn($loan) => $loan->archived_at ? 'archived' : $loan->status) as $status => $members)
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle me-1">{{ $members->count() }} {{ ucwords(str_replace('_', ' ', $status)) }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="Review Members" data-bs-toggle="modal" data-bs-target="#batchDetailModal{{ $group->batch->id }}" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No batch loans found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modals rendered outside the table: a <div> is not valid directly inside a
     table body, and browsers "correct" that by ejecting everything after the
     first row's modals out of the table, breaking every row after the first. --}}
@foreach($batchGroups as $group)
<x-modal id="batchDetailModal{{ $group->batch->id }}" title="{{ $group->batch->label }} Members" size="modal-xl">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small">Loan ID</th>
                    <th class="small">Farmer</th>
                    <th class="small">Principal</th>
                    <th class="small">Balance</th>
                    <th class="small">Monthly Due</th>
                    <th class="small">Next Due</th>
                    <th class="small">Status</th>
                    <th class="small">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group->loans as $loan)
                <tr>
                    <td class="small fw-medium text-dark">LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="small">{{ $loan->farmer->full_name }}</td>
                    <td class="small">{{ peso($loan->principal_amount) }}</td>
                    <td class="small fw-medium text-dark">
                        {{ $loan->remaining_balance !== null ? peso($loan->remaining_balance) : '—' }}
                        <div class="text-muted fw-normal" style="font-size: 0.7rem;">Total repayable: {{ peso($loan->monthly_due * $loan->repayment_terms_months) }}</div>
                    </td>
                    <td class="small text-muted">{{ peso($loan->monthly_due) }}</td>
                    <td class="small {{ $loan->status === 'overdue' ? 'text-danger' : 'text-muted' }}">{{ $loan->next_due_date?->format('M d, Y') ?? '—' }}</td>
                    <td class="small">
                        @if($loan->archived_at)
                        <x-status-badge status="Archived" />
                        @else
                        <x-status-badge :status="ucwords(str_replace('_', ' ', $loan->status))" />
                        @endif
                        @if($loan->status === 'pending_disbursement' && $loan->scheduled_disbursement_date)
                        <div class="text-muted" style="font-size: 0.7rem;">Auto-disburses {{ $loan->scheduled_disbursement_date->format('M d, Y') }}</div>
                        @endif
                    </td>
                    <td class="small">
                        <div class="d-flex gap-1">
                            <button type="button" class="icon-btn text-primary" title="View" onclick="switchModal('batchDetailModal{{ $group->batch->id }}', 'viewModal{{ $loan->id }}')"><i class="fas fa-eye"></i></button>
                            @if(!$loan->archived_at)
                                @if(!in_array($loan->status, ['fully_paid', 'pending_disbursement']))
                                <button type="button" class="icon-btn text-warning" title="Edit" onclick="switchModal('batchDetailModal{{ $group->batch->id }}', 'editModal{{ $loan->id }}')"><i class="fas fa-edit"></i></button>
                                @endif
                                <button type="button" class="icon-btn text-secondary" title="Archive" onclick="switchModal('batchDetailModal{{ $group->batch->id }}', 'archiveModal{{ $loan->id }}')"><i class="fas fa-archive"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-modal>
@endforeach

@foreach($batchGroups as $group)
@foreach($group->loans as $loan)
<!-- View Modal (details + payment history) -->
<x-modal id="viewModal{{ $loan->id }}" title="Loan Details">
    <div class="row g-3 mb-3">
        <div class="col-6"><label class="text-muted small d-block">Loan ID</label><p class="fw-medium mb-0">LN-{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $loan->farmer->full_name }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Loan Type</label><p class="fw-medium mb-0">{{ $group->batch->label }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Principal Amount</label><p class="fw-medium mb-0">{{ peso($loan->principal_amount) }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Remaining Balance</label><p class="fw-medium mb-0">{{ $loan->remaining_balance !== null ? peso($loan->remaining_balance) : '—' }}</p></div>
        <div class="col-6"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $loan->repayment_terms_months }} months</p></div>
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
                @forelse($loan->payments->sortByDesc('created_at') as $payment)
                <tr>
                    <td class="small">{{ $payment->transaction_date->format('M d, Y') }}</td>
                    <td class="small">
                        @if($payment->type === 'payment')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Payment</span>
                        @elseif($payment->type === 'prepayment')
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Prepayment</span>
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

    <div class="mt-3 text-end">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="switchModal('viewModal{{ $loan->id }}', 'batchDetailModal{{ $group->batch->id }}')">
            <i class="fas fa-arrow-left me-1"></i> Back to {{ $group->batch->label }}
        </button>
    </div>
</x-modal>

@if(!$loan->archived_at && !in_array($loan->status, ['fully_paid', 'pending_disbursement']))
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
@endif
@endforeach
@endforeach

<!-- Interest Computation Info -->
<x-info-banner variant="info" title="Interest Computation: 2% every due date" class="mt-6">
    Interest is automatically calculated and added to the loan balance whenever a due date passes without payment.
</x-info-banner>

<!-- Delinquency Policy Info -->
<x-info-banner variant="warning" title="Grace Period &amp; Penalty Policy" class="mt-4">
    If a due date passes with a balance still unpaid, a one-time 2% grace-period interest applies. If it's still unpaid after the 30-day grace period, a one-time 10% penalty applies and the farmer is restricted from new loan requests until fully paid. Accounts unpaid for 5 months are flagged for Barangay summons, and for 6 months for legal action &mdash; both notify the Administrator.
</x-info-banner>

<script>
    // Hides one modal and, once it's fully closed, opens another. Bootstrap
    // doesn't support two open modals cleanly, so a batch's detail modal must
    // finish closing before a member's view/disburse/edit/archive modal opens
    // (and vice versa when navigating back).
    function switchModal(fromModalId, toModalId) {
        var fromEl = document.getElementById(fromModalId);
        var fromModal = bootstrap.Modal.getInstance(fromEl);
        var openTarget = function() {
            new bootstrap.Modal(document.getElementById(toModalId)).show();
        };

        if (fromModal) {
            fromEl.addEventListener('hidden.bs.modal', openTarget, { once: true });
            fromModal.hide();
        } else {
            openTarget();
        }
    }
</script>
@endsection
