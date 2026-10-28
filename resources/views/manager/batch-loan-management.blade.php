@extends('manager.layout')

@section('title', 'Batch Loans')
@section('header', 'Batch Loans')

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
    // One color per loan state, same as the status badges.
    $statusTones = ['active' => 'success', 'overdue' => 'danger', 'fully_paid' => 'info', 'pending_disbursement' => 'warning', 'archived' => 'secondary'];
    $statusOrder = ['active', 'overdue', 'fully_paid', 'pending_disbursement', 'archived'];
    $statusName = fn ($status) => ucwords(str_replace('_', ' ', $status));
    $loanState = fn ($loan) => $loan->archived_at ? 'archived' : $loan->status;
    $delinquencyLabels = ['grace' => '2% Grace Interest', 'penalized' => '10% Penalty · Restricted', 'barangay_summon' => 'Barangay Summons', 'legal_action' => 'Legal Action'];
    $batchTabs = ['' => 'All', 'pending_disbursement' => 'Pending Disbursement', 'active' => 'Active', 'overdue' => 'Overdue', 'fully_paid' => 'Fully Paid'];
@endphp

<p class="text-sm text-gray-500 mb-4">
    Track finalized batches. Each batch is reviewed together; open one to see every member's loan.
    Regular loans are in <a href="{{ route('manager.loan-management') }}" class="text-brand">Loan Management</a>.
</p>

<!-- Summary -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-5">
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-4 {{ $stats['pending_disbursement_count'] > 0 ? 'stat-card-highlight' : '' }}">
        <p class="text-sm {{ $stats['pending_disbursement_count'] > 0 ? 'text-warning fw-semibold' : 'text-gray-500' }} mb-1">Pending Disbursement</p>
        <p class="text-xl font-bold text-gray-900 mb-1">{{ $stats['pending_disbursement_count'] }} {{ Str::plural('loan', $stats['pending_disbursement_count']) }}</p>
        @if($stats['pending_disbursement_count'] > 0)
        <p class="text-xs text-gray-500 mb-0">
            {{ $stats['scheduled_disbursement_count'] === $stats['pending_disbursement_count'] ? 'Scheduled to release automatically' : 'Waiting to be released to farmers' }}
        </p>
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

<!-- Batches -->
<div class="section-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 px-md-5 py-4">
        @if($showArchived)
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Archived batch loans</h3>
        @else
        <div class="segmented" role="group" aria-label="Filter by loan status">
            @foreach($batchTabs as $value => $label)
            <a href="{{ route('manager.batch-loan-management', array_filter(['status' => $value, 'search' => request('search')])) }}"
                class="{{ request('status', '') === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        @endif
        <div class="d-flex flex-wrap align-items-center gap-3">
            <form method="GET" action="{{ route('manager.batch-loan-management') }}" id="batchLoanFilterForm" class="position-relative m-0">
                @if($showArchived)
                <input type="hidden" name="archived" value="1">
                @elseif(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="search" id="batchLoanSearchInput" value="{{ request('search') }}" placeholder="Search farmer or batch" class="form-control ps-5" style="min-width: 240px;" autocomplete="off">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </form>
            @if($showArchived)
            <a href="{{ route('manager.batch-loan-management') }}" class="text-sm fw-semibold text-brand text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to Active</a>
            @else
            <a href="{{ route('manager.batch-loan-management', ['archived' => 1]) }}" class="text-sm fw-semibold text-brand text-decoration-none">View Archived ({{ $stats['archived_count'] }})</a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted">Batch</th>
                    <th class="px-4 py-3 text-xs font-medium text-uppercase text-muted text-end">Total Principal</th>
                    <th class="px-4 py-3 text-xs font-medium text-uppercase text-muted text-end">Total Outstanding</th>
                    <th class="px-4 py-3 text-xs font-medium text-uppercase text-muted" style="min-width: 260px;">Member Loan Status</th>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batchGroups as $group)
                @php
                    $breakdown = $group->loans->groupBy($loanState)->sortBy(fn ($members, $status) => array_search($status, $statusOrder));
                    $allPending = $group->loans->every(fn ($loan) => $loan->status === 'pending_disbursement' && ! $loan->archived_at);
                    $nextRelease = $group->loans->where('status', 'pending_disbursement')->whereNull('archived_at')->min('scheduled_disbursement_date');
                @endphp
                <tr>
                    <td class="px-4 px-md-5 py-3">
                        <p class="batch-name mb-0">{{ $group->batch->label }}</p>
                        <p class="text-sm text-gray-500 mb-0">{{ $group->loans->count() }} {{ Str::plural('farmer', $group->loans->count()) }}</p>
                    </td>
                    <td class="px-4 py-3 text-end text-gray-700">{{ peso($group->loans->sum('principal_amount')) }}</td>
                    <td class="px-4 py-3 text-end fw-semibold text-gray-900">{{ $allPending ? '—' : peso($group->loans->sum('remaining_balance')) }}</td>
                    <td class="px-4 py-3">
                        <div class="status-bar mb-2" aria-hidden="true">
                            @foreach($breakdown as $status => $members)
                            <span class="bg-{{ $statusTones[$status] ?? 'secondary' }}" style="flex-grow: {{ $members->count() }};"></span>
                            @endforeach
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($breakdown as $status => $members)
                            <span class="badge bg-{{ $statusTones[$status] ?? 'secondary' }}-subtle text-{{ $statusTones[$status] ?? 'secondary' }} border border-{{ $statusTones[$status] ?? 'secondary' }}-subtle">
                                <span class="badge-dot bg-{{ $statusTones[$status] ?? 'secondary' }}"></span>{{ $members->count() }} {{ $statusName($status) }}
                            </span>
                            @endforeach
                        </div>
                        @if($nextRelease)
                        <p class="text-xs text-gray-500 mb-0 mt-1">Auto-disburses {{ \Carbon\Carbon::parse($nextRelease)->format('M j, Y') }}</p>
                        @endif
                    </td>
                    <td class="px-4 px-md-5 py-3 text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#batchDetailModal{{ $group->batch->id }}">Review Members</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 px-md-6 py-6 text-center text-muted">{{ $showArchived ? 'No archived batch loans.' : 'No batch loans found.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination-footer :paginator="$batchGroups" />
</div>

{{-- Modals rendered outside the table: a <div> is not valid directly inside a
     table body, and browsers "correct" that by ejecting everything after the
     first row's modals out of the table, breaking every row after the first. --}}
@foreach($batchGroups as $group)
@php
    $groupStates = $group->loans->groupBy($loanState)->sortBy(fn ($members, $status) => array_search($status, $statusOrder));
    $finalizedAt = $group->loans->min('created_at');
    $rate = $group->loans->first()?->interest_rate;
@endphp
<div class="modal fade" id="batchDetailModal{{ $group->batch->id }}" tabindex="-1" aria-labelledby="batchDetailModal{{ $group->batch->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header align-items-start border-0 pb-2">
                <div>
                    <h5 class="modal-title text-2xl fw-bold d-flex align-items-center gap-2 mb-0" id="batchDetailModal{{ $group->batch->id }}Label">
                        {{ $group->batch->label }} Members <x-status-badge status="Finalized" class="text-xs" />
                    </h5>
                    <p class="text-sm text-gray-500 mb-0">
                        {{ $group->loans->count() }} {{ Str::plural('farmer', $group->loans->count()) }}
                        @if($finalizedAt) &middot; finalized {{ \Carbon\Carbon::parse($finalizedAt)->format('M j, Y') }} @endif
                        @if($rate !== null) &middot; {{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}% interest per due date @endif
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2" data-batch-members>
                @php $groupPending = $group->loans->every(fn ($loan) => $loan->status === 'pending_disbursement' && ! $loan->archived_at); @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                    <div class="rounded-3 p-3 bg-light">
                        <p class="text-xs text-gray-500 mb-1">Total principal</p>
                        <p class="text-xl font-bold text-gray-900 mb-0">{{ peso($group->loans->sum('principal_amount')) }}</p>
                    </div>
                    <div class="rounded-3 p-3 bg-light">
                        <p class="text-xs text-gray-500 mb-1">Total outstanding</p>
                        <p class="text-xl font-bold text-gray-900 mb-0">{{ $groupPending ? '—' : peso($group->loans->sum('remaining_balance')) }}</p>
                    </div>
                    <div class="rounded-3 p-3 bg-light">
                        <p class="text-xs text-gray-500 mb-1">Status of {{ $group->loans->count() }} {{ Str::plural('loan', $group->loans->count()) }}</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($groupStates as $status => $members)
                            <span class="badge bg-{{ $statusTones[$status] ?? 'secondary' }}-subtle text-{{ $statusTones[$status] ?? 'secondary' }} border border-{{ $statusTones[$status] ?? 'secondary' }}-subtle">{{ $members->count() }} {{ $statusName($status) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div class="segmented" role="group" aria-label="Filter members">
                        <a href="#" class="active" data-member-filter="">All {{ $group->loans->count() }}</a>
                        @foreach($groupStates as $status => $members)
                        <a href="#" data-member-filter="{{ $status }}">{{ $statusName($status) }} {{ $members->count() }}</a>
                        @endforeach
                    </div>
                    <div class="position-relative">
                        <input type="search" class="form-control form-control-sm ps-5" placeholder="Find a farmer" data-member-search style="min-width: 220px;" aria-label="Find a farmer">
                        <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 13px;"></i>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-xs text-uppercase text-muted">Farmer</th>
                                <th class="text-xs text-uppercase text-muted text-end">Principal</th>
                                <th class="text-xs text-uppercase text-muted text-end">Balance</th>
                                <th class="text-xs text-uppercase text-muted text-end">Monthly Due</th>
                                <th class="text-xs text-uppercase text-muted">Next Due</th>
                                <th class="text-xs text-uppercase text-muted">Status</th>
                                <th class="text-xs text-uppercase text-muted text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->loans as $loan)
                            @php
                                $state = $loanState($loan);
                                $hasSchedule = in_array($loan->status, ['active', 'overdue'], true) && ! $loan->archived_at;
                            @endphp
                            <tr data-member-row data-status="{{ $state }}" data-name="{{ strtolower($loan->farmer->full_name) }}">
                                <td>
                                    <p class="text-sm fw-semibold text-gray-900 mb-0">{{ $loan->farmer->full_name }}</p>
                                    <p class="text-xs text-gray-500 mb-0">{{ $loan->repayment_terms_months }} months{{ $loan->effective_term_months > $loan->repayment_terms_months ? ' · now month '.$loan->current_installment_number : '' }}</p>
                                </td>
                                <td class="text-sm text-end text-gray-700">{{ peso($loan->principal_amount) }}</td>
                                <td class="text-sm text-end fw-semibold text-gray-900">{{ $loan->remaining_balance !== null && $loan->status !== 'pending_disbursement' ? peso($loan->remaining_balance) : '—' }}</td>
                                <td class="text-sm text-end text-gray-700">{{ $hasSchedule ? peso($loan->amount_due) : '—' }}</td>
                                <td class="text-sm {{ $loan->status === 'overdue' ? 'text-danger fw-semibold' : 'text-gray-700' }}">{{ $hasSchedule ? ($loan->next_due_date?->format('M j, Y') ?? '—') : '—' }}</td>
                                <td>
                                    <x-status-badge :status="$statusName($state)" />
                                    @if($loan->delinquency_stage && ! $loan->archived_at)
                                    <div class="text-xs text-danger mt-1">{{ $delinquencyLabels[$loan->delinquency_stage] ?? '' }}</div>
                                    @elseif($loan->status === 'pending_disbursement' && $loan->scheduled_disbursement_date)
                                    <div class="text-xs text-gray-500 mt-1">Auto-disburses {{ $loan->scheduled_disbursement_date->format('M j, Y') }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="switchModal('batchDetailModal{{ $group->batch->id }}', 'viewModal{{ $loan->id }}')">View</button>
                                    <div class="dropdown d-inline-block">
                                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if(! $loan->archived_at)
                                                @if(! in_array($loan->status, ['fully_paid', 'pending_disbursement'], true))
                                                <li><button type="button" class="dropdown-item" onclick="switchModal('batchDetailModal{{ $group->batch->id }}', 'editModal{{ $loan->id }}')"><i class="fas fa-pen me-2 text-gray-500"></i>Edit loan</button></li>
                                                <li><hr class="dropdown-divider"></li>
                                                @endif
                                                <li><button type="button" class="dropdown-item text-danger" onclick="switchModal('batchDetailModal{{ $group->batch->id }}', 'archiveModal{{ $loan->id }}')"><i class="fas fa-archive me-2"></i>Archive loan</button></li>
                                            @else
                                                <li><button type="button" class="dropdown-item" onclick="switchModal('batchDetailModal{{ $group->batch->id }}', 'unarchiveModal{{ $loan->id }}')"><i class="fas fa-box-open me-2 text-success"></i>Restore loan</button></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="d-none" data-member-empty><td colspan="7" class="text-center text-muted py-4">No farmers match.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between bg-light">
                <p class="text-xs text-gray-500 mb-0">More holds Edit (while the loan is active or overdue) and Archive. Archived loans show Restore instead.</p>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@foreach($batchGroups as $group)
@foreach($group->loans as $loan)
@php
    $state = $loanState($loan);
    $hasSchedule = in_array($loan->status, ['active', 'overdue'], true) && ! $loan->archived_at;
    $farmerPayments = $loan->payments->whereIn('type', ['payment', 'prepayment', 'partial'])->count();
    $totalToRepay = round($loan->monthly_due * $loan->repayment_terms_months, 2);
    $penalties = [
        ['label' => '2% grace interest', 'at' => $loan->partial_penalty_applied_at, 'none' => 'Not applied'],
        ['label' => '10% grace penalty and restriction', 'at' => $loan->grace_penalty_applied_at, 'none' => 'Not applied'],
        ['label' => 'Barangay summons', 'at' => $loan->barangay_summon_at, 'none' => 'Not flagged'],
        ['label' => 'Legal action', 'at' => $loan->legal_action_at, 'none' => 'Not flagged'],
    ];
@endphp
<!-- Farmer loan (details + history) -->
<div class="modal fade" id="viewModal{{ $loan->id }}" tabindex="-1" aria-labelledby="viewModal{{ $loan->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header align-items-start border-0 pb-0">
                <div class="flex-grow-1">
                    <button type="button" class="btn btn-light btn-sm fw-semibold mb-2" onclick="switchModal('viewModal{{ $loan->id }}', 'batchDetailModal{{ $group->batch->id }}')">
                        <i class="fas fa-chevron-left me-1"></i>{{ $group->batch->label }} Members
                    </button>
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <h5 class="modal-title text-2xl fw-bold mb-0" id="viewModal{{ $loan->id }}Label">{{ $loan->farmer->full_name }}</h5>
                            <p class="text-sm text-gray-500 mb-0">Batch loan &middot; {{ $group->batch->label }} &middot; {{ $loan->repayment_terms_months }} months</p>
                        </div>
                        <x-status-badge :status="$statusName($state)" class="mt-1" />
                    </div>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div class="rounded-3 p-3 bg-light">
                        <p class="text-xs text-gray-500 mb-1">Remaining balance</p>
                        <p class="text-2xl font-bold text-gray-900 mb-0">{{ $loan->remaining_balance !== null && $loan->status !== 'pending_disbursement' ? peso($loan->remaining_balance) : '—' }}</p>
                    </div>
                    @if($loan->status === 'pending_disbursement')
                    <div class="rounded-3 p-3 bg-warning-subtle">
                        <p class="text-xs text-warning mb-1">Release date</p>
                        <p class="text-2xl font-bold text-warning mb-0">{{ $loan->scheduled_disbursement_date?->format('M j, Y') ?? 'Not scheduled' }}</p>
                    </div>
                    @else
                    <div class="rounded-3 p-3 {{ $loan->status === 'overdue' ? 'bg-danger-subtle' : 'bg-light' }}">
                        <p class="text-xs {{ $loan->status === 'overdue' ? 'text-danger' : 'text-gray-500' }} mb-1">Next due</p>
                        <p class="text-2xl font-bold {{ $loan->status === 'overdue' ? 'text-danger' : 'text-gray-900' }} mb-0">{{ $hasSchedule ? ($loan->next_due_date?->format('M j, Y') ?? '—') : '—' }}</p>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-3 mb-4">
                    <div><p class="text-xs text-gray-500 mb-0">Principal</p><p class="text-sm fw-semibold mb-0">{{ peso($loan->principal_amount) }}</p></div>
                    <div><p class="text-xs text-gray-500 mb-0">Interest rate</p><p class="text-sm fw-semibold mb-0">{{ rtrim(rtrim(number_format($loan->interest_rate, 2), '0'), '.') }}% per due date</p></div>
                    <div><p class="text-xs text-gray-500 mb-0">Monthly due</p><p class="text-sm fw-semibold mb-0">{{ $hasSchedule ? peso($loan->amount_due) : '—' }}</p></div>
                    @if($loan->disbursed_at)
                    <div><p class="text-xs text-gray-500 mb-0">Released</p><p class="text-sm fw-semibold mb-0">{{ $loan->disbursed_at->format('M j, Y') }} &middot; {{ $statusName($loan->disbursement_method ?? 'cash') }}{{ $loan->reference_no ? ' (Ref: '.$loan->reference_no.')' : '' }}</p></div>
                    <div><p class="text-xs text-gray-500 mb-0">Released by</p><p class="text-sm fw-semibold mb-0">{{ $loan->disbursedBy->name ?? '—' }}</p></div>
                    @elseif($loan->status === 'pending_disbursement' && $loan->scheduled_disbursement_date)
                    <div><p class="text-xs text-gray-500 mb-0">Release method</p><p class="text-sm fw-semibold mb-0">{{ $statusName($loan->disbursement_method ?? 'cash') }}{{ $loan->reference_no ? ' (Ref: '.$loan->reference_no.')' : '' }}</p></div>
                    <div><p class="text-xs text-gray-500 mb-0">Scheduled by</p><p class="text-sm fw-semibold mb-0">{{ $loan->scheduledBy->name ?? '—' }}</p></div>
                    @endif
                    <div><p class="text-xs text-gray-500 mb-0">Collateral</p><p class="text-sm fw-semibold mb-0">{{ $loan->collateral ?? '—' }}</p></div>
                    @if($loan->notes)
                    <div class="col-span-full"><p class="text-xs text-gray-500 mb-0">Notes</p><p class="text-sm fw-semibold mb-0">{{ $loan->notes }}</p></div>
                    @endif
                </div>

                @if($loan->original_due_date)
                <h4 class="text-base fw-semibold text-gray-900 mb-2">Delinquency and penalties</h4>
                <ul class="list-unstyled mb-2">
                    @foreach($penalties as $penalty)
                    <li class="d-flex align-items-center justify-content-between gap-3 py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                        <span class="text-sm d-flex align-items-center gap-2">
                            <i class="{{ $penalty['at'] ? 'fas fa-circle-exclamation text-danger' : 'far fa-circle text-gray-400' }}"></i>{{ $penalty['label'] }}
                        </span>
                        <span class="text-sm {{ $penalty['at'] ? 'text-danger fw-semibold' : 'text-gray-500' }}">{{ $penalty['at'] ? $penalty['at']->format('M j, Y') : $penalty['none'] }}</span>
                    </li>
                    @endforeach
                </ul>
                <p class="text-xs text-gray-500 mb-4">Grace period for the original due date ({{ $loan->original_due_date->format('M j, Y') }}) ends {{ $loan->original_due_date->copy()->addDays(30)->format('M j, Y') }}.</p>
                @if($loan->is_restricted)
                <p class="text-sm fw-semibold text-danger mb-4"><i class="fas fa-ban me-1"></i>This farmer is restricted from new loan requests until this balance is fully paid.</p>
                @endif
                @endif

                @if(in_array($loan->status, ['active', 'overdue', 'fully_paid'], true))
                <h4 class="text-base fw-semibold text-gray-900 mb-2">How the balance adds up</h4>
                <div class="border rounded-3 px-3 mb-4">
                    <div class="d-flex justify-content-between gap-3 py-2 border-bottom text-sm">
                        <span><strong>Total to repay</strong> ({{ peso($loan->principal_amount) }} borrowed + {{ peso(round($totalToRepay - $loan->principal_amount, 2)) }} interest for {{ $loan->repayment_terms_months }} months)</span>
                        <span class="fw-semibold text-nowrap">{{ peso($totalToRepay) }}</span>
                    </div>
                    @if($loan->late_charges['count'] > 0)
                    <div class="d-flex justify-content-between gap-3 py-2 border-bottom text-sm text-danger">
                        <span><strong>Late charges added</strong> ({{ $loan->late_charges['count'] }} {{ Str::plural('charge', $loan->late_charges['count']) }} for missed due dates)</span>
                        <span class="fw-semibold text-nowrap">+ {{ peso($loan->late_charges['total']) }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between gap-3 py-2 text-sm">
                        <span><strong>Paid by farmer</strong> ({{ $farmerPayments }} {{ Str::plural('payment', $farmerPayments) }})</span>
                        <span class="fw-semibold text-nowrap">&minus; {{ peso($loan->amount_paid) }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3 py-2 border-top border-2 border-dark">
                        <span class="fw-bold">Remaining balance</span>
                        <span class="text-xl fw-bold text-nowrap">{{ peso($loan->remaining_balance) }}</span>
                    </div>
                </div>
                @endif

                <h4 class="text-base fw-semibold text-gray-900 mb-2">History</h4>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-top">
                        <thead class="table-light">
                            <tr>
                                <th class="text-xs text-uppercase text-muted">Date</th>
                                <th class="text-xs text-uppercase text-muted">What happened</th>
                                <th class="text-xs text-uppercase text-muted text-end">Paid</th>
                                <th class="text-xs text-uppercase text-muted text-end">Charged</th>
                                <th class="text-xs text-uppercase text-muted text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(array_reverse($loan->narrated_history) as $row)
                            <tr>
                                <td class="text-sm text-nowrap text-gray-700">{{ $row['date']->format('M j, Y') }}</td>
                                <td class="text-sm {{ $row['charged'] !== null ? 'text-danger' : 'text-gray-900' }}">{{ $row['summary'] }}</td>
                                <td class="text-sm text-end text-nowrap">{{ $row['paid'] !== null ? '− '.peso($row['paid']) : '' }}</td>
                                <td class="text-sm text-end text-nowrap text-danger">{{ $row['charged'] !== null ? '+ '.peso($row['charged']) : '' }}</td>
                                <td class="text-sm text-end text-nowrap fw-semibold">{{ peso($row['balance']) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted text-sm py-3">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mb-0 mt-2">Only <strong>Paid</strong> is money the farmer gave. <strong class="text-danger">Charged</strong> is added to what the farmer owes.</p>
            </div>
        </div>
    </div>
</div>

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
@endforeach

<x-loan-rules class="mt-6" />

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

    wireDebouncedSearch('batchLoanSearchInput', 'batchLoanFilterForm');

    // Batch members modal: status tabs and "Find a farmer" filter the rows in place.
    document.querySelectorAll('[data-batch-members]').forEach(function (panel) {
        var search = panel.querySelector('[data-member-search]');
        var rows = panel.querySelectorAll('[data-member-row]');
        var empty = panel.querySelector('[data-member-empty]');
        var status = '';

        function apply() {
            var term = search.value.trim().toLowerCase();
            var shown = 0;
            rows.forEach(function (row) {
                var match = (!status || row.dataset.status === status) && (!term || row.dataset.name.indexOf(term) !== -1);
                row.classList.toggle('d-none', !match);
                if (match) { shown++; }
            });
            empty.classList.toggle('d-none', shown > 0);
        }

        panel.querySelectorAll('[data-member-filter]').forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                status = tab.dataset.memberFilter;
                panel.querySelectorAll('[data-member-filter]').forEach(function (t) { t.classList.toggle('active', t === tab); });
                apply();
            });
        });
        search.addEventListener('input', apply);
    });
</script>
@endsection
