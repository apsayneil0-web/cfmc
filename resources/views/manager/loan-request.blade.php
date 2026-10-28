@extends('manager.layout')

@section('title', 'Loan Requests')
@section('header', 'Loan Requests')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif


@php
    $hasBatchActivity = $batchesInProgress->isNotEmpty() || $batchesWithAdministrator->isNotEmpty() || $batchesReadyToFinalize->isNotEmpty() || $batchesFinalized->isNotEmpty();
    $batchView = ! $showArchived && $mode === 'batch';

    // Every batch in the pipeline as one card, in the order they need attention.
    $names = fn ($requests) => $requests->map(fn ($r) => strtolower($r->farmer->full_name ?? ''))->implode('|');
    $pipeline = collect()
        ->concat($batchesReadyToFinalize->map(fn ($batch) => (object) [
            'batch' => $batch, 'stage' => 'ready', 'label' => 'Ready to finalize', 'tone' => 'success',
            'members' => $batch->loanRequests->count(), 'amount' => $batch->loanRequests->sum('requested_amount'), 'amountLabel' => 'Total requested',
            'note' => 'Approved by the Administrator. Waiting for you to finalize.', 'names' => $names($batch->loanRequests),
        ]))
        ->concat($batchesInProgress->map(fn ($batch) => (object) [
            'batch' => $batch, 'stage' => 'collecting', 'label' => 'Collecting members', 'tone' => 'warning',
            'members' => $batch->member_count, 'amount' => $batch->loanRequests->sum('requested_amount'), 'amountLabel' => 'Total requested',
            'note' => ($batch->capacity - $batch->member_count).' more '.Str::plural('farmer', $batch->capacity - $batch->member_count).' needed. Sent to the Administrator automatically when full.',
            'names' => $names($batch->loanRequests),
        ]))
        ->concat($batchesWithAdministrator->map(fn ($batch) => (object) [
            'batch' => $batch, 'stage' => 'admin', 'label' => 'With Administrator', 'tone' => 'info',
            'members' => $batch->loanRequests->count(), 'amount' => $batch->loanRequests->sum('requested_amount'), 'amountLabel' => 'Total requested',
            'note' => 'Full and sent for approval. No action needed from you yet.', 'names' => $names($batch->loanRequests),
        ]))
        ->concat($batchesFinalized->map(fn ($group) => (object) [
            'batch' => $group->batch, 'stage' => 'finalized', 'label' => 'Finalized', 'tone' => 'secondary',
            'members' => $group->loan_count, 'amount' => $group->total_principal, 'amountLabel' => 'Total principal',
            'note' => 'Loans created. Track repayments in Batch Loans.', 'names' => $names($group->batch->loanRequests),
        ]));
    $stageTabs = ['' => 'All', 'collecting' => 'Collecting members', 'admin' => 'With Administrator', 'ready' => 'Ready to finalize', 'finalized' => 'Finalized'];
    $stageCounts = $pipeline->countBy('stage');
    $activeBatchCount = $pipeline->whereIn('stage', ['collecting', 'admin', 'ready'])->count();
    $regularTotal = $regularCounts->sum();

    // Batches that exist but have nobody in them yet, e.g. "Batches 6 to 10".
    $pipelineIds = $pipeline->map(fn ($item) => $item->batch->id)->all();
    $emptyBatches = $batches->reject(fn ($b) => in_array($b->id, $pipelineIds, true))->filter(fn ($b) => $b->member_count === 0)->sortBy('batch_number')->values();
    $emptyNumbers = $emptyBatches->pluck('batch_number')->values();
    $emptyContiguous = $emptyNumbers->count() > 1 && $emptyNumbers->last() - $emptyNumbers->first() === $emptyNumbers->count() - 1;
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <p class="text-sm text-gray-500 mb-0">Review farmers' loan applications. Approved loans move to Loan Management.</p>
    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createRequestModal">
        <i class="fas fa-plus"></i><span>New Request</span>
    </button>
</div>

@unless($showArchived)
<!-- Regular / Batch switch -->
<div class="d-flex flex-wrap gap-3 mb-5">
    <a href="{{ route('manager.loan-request', ['mode' => 'regular']) }}" class="view-switch {{ $batchView ? '' : 'active' }}">
        <span class="view-switch-title">Regular requests</span>
        <span class="view-switch-meta">One farmer, one loan &middot; {{ $regularTotal }} {{ Str::plural('request', $regularTotal) }}</span>
    </a>
    <a href="{{ route('manager.loan-request', ['mode' => 'batch']) }}" class="view-switch {{ $batchView ? 'active' : '' }}">
        <span class="view-switch-title">
            Batch requests
            @if($batchesReadyToFinalize->isNotEmpty())
            <span class="badge rounded-pill bg-success ms-1">{{ $batchesReadyToFinalize->count() }} ready</span>
            @endif
        </span>
        <span class="view-switch-meta">Up to {{ $batches->max('capacity') ?: 10 }} farmers per batch &middot; {{ $activeBatchCount }} active {{ Str::plural('batch', $activeBatchCount) }}</span>
    </a>
</div>
@endunless

@if($batchView)
@foreach($batchesReadyToFinalize as $batch)
<!-- Approved batch waiting to be finalized -->
<div class="action-banner mb-4">
    <div>
        <p class="fw-bold text-gray-900 mb-0">{{ $batch->label }} is approved by the Administrator</p>
        <p class="text-sm text-gray-600 mb-0">Finalize all {{ $batch->loanRequests->count() }} members into loans at once with one interest rate and one disbursement date.</p>
    </div>
    <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#batchFinalizeModal{{ $batch->id }}">Finalize {{ $batch->label }}</button>
</div>
@endforeach

<div data-batch-board>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="segmented" role="group" aria-label="Filter batches by stage">
            @foreach($stageTabs as $value => $label)
            <a href="#" class="{{ $value === '' ? 'active' : '' }}" data-stage-filter="{{ $value }}">{{ $label }} <span class="segmented-count">{{ $value === '' ? $pipeline->count() : ($stageCounts[$value] ?? 0) }}</span></a>
            @endforeach
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="position-relative">
                <input type="search" class="form-control ps-5" placeholder="Search farmer in batches" data-batch-search style="min-width: 240px;" aria-label="Search farmer in batches">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <a href="{{ route('manager.loan-request', ['archived' => 1]) }}" class="text-sm fw-semibold text-brand text-decoration-none">View Archived ({{ $archivedCount }})</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($pipeline as $item)
        @php $capacity = $item->batch->capacity ?: 10; @endphp
        <div class="batch-card" data-batch-card data-stage="{{ $item->stage }}" data-names="{{ $item->names }}">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                <h3 class="batch-name mb-0">{{ $item->batch->label }}</h3>
                <span class="badge bg-{{ $item->tone }}-subtle text-{{ $item->tone }} border border-{{ $item->tone }}-subtle">{{ $item->label }}</span>
            </div>
            <div class="d-flex justify-content-between text-sm mb-1">
                <span class="text-gray-500">Members</span>
                <span class="fw-semibold text-gray-900">{{ $item->members }} / {{ $capacity }}</span>
            </div>
            <div class="progress mb-3" style="height: 8px;" role="progressbar" aria-label="Members" aria-valuenow="{{ $item->members }}" aria-valuemin="0" aria-valuemax="{{ $capacity }}">
                <div class="progress-bar bg-{{ $item->tone === 'secondary' ? 'secondary' : $item->tone }}" style="width: {{ min(100, round($item->members / $capacity * 100)) }}%"></div>
            </div>
            <div class="d-flex justify-content-between text-sm mb-2">
                <span class="text-gray-500">{{ $item->amountLabel }}</span>
                <span class="fw-bold text-gray-900">{{ peso($item->amount) }}</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">{{ $item->note }}</p>
            <div class="mt-auto">
                @switch($item->stage)
                    @case('ready')
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#batchFinalizeModal{{ $item->batch->id }}">Finalize Batch</button>
                    @break
                    @case('collecting')
                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#manageBatchModal{{ $item->batch->id }}">Manage Batch</button>
                    @break
                    @case('admin')
                    <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#batchAdminViewModal{{ $item->batch->id }}">View Members</button>
                    @break
                    @default
                    <a href="{{ route('manager.batch-loan-management', ['search' => $item->batch->label]) }}" class="btn btn-outline-primary w-100">Open in Batch Loans</a>
                @endswitch
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl border border-gray-200 p-6 text-center text-muted">
            No batch requests yet. Add a farmer to a batch with New Request.
        </div>
        @endforelse
        <div class="col-span-full bg-white rounded-xl border border-gray-200 p-6 text-center text-muted d-none" data-batch-empty>No batches match.</div>
    </div>

    @if($emptyBatches->isNotEmpty())
    <p class="text-sm text-gray-500 mt-4 mb-0">
        @if($emptyContiguous)
        Batches {{ $emptyNumbers->first() }} to {{ $emptyNumbers->last() }} have no members yet.
        @elseif($emptyBatches->count() === 1)
        {{ $emptyBatches->first()->label }} has no members yet.
        @else
        {{ $emptyBatches->pluck('label')->implode(', ') }} have no members yet.
        @endif
        They appear here once a farmer joins.
    </p>
    @endif
</div>

{{-- Denied batch members needing a resubmit — not part of the lifecycle
     cards above (those only track the pre-decision stages). Without this, a
     denied-but-not-yet-archived batch member would have no visible entry
     point to resubmit from. --}}
@php
    $deniedBatchMembers = $requestBatchGroups->flatMap(fn ($group) => $group->members)->where('status', 'denied')->values();
@endphp
@if($deniedBatchMembers->isNotEmpty())
<div class="section-card mt-5">
    <div class="px-4 px-md-5 py-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Denied batch requests</h3>
        <p class="text-sm text-gray-500 mb-0">Correct and resubmit, or archive if no longer needed.</p>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted">Farmer</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Batch</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted text-end">Amount</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Reason</th>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deniedBatchMembers as $member)
                <tr>
                    <td class="px-4 px-md-5 py-3 text-sm fw-semibold text-gray-900">{{ $member->farmer->full_name }}</td>
                    <td class="px-3 py-3 text-sm text-gray-700">{{ $member->batch?->label ?? 'Batch' }}</td>
                    <td class="px-3 py-3 text-sm text-end">{{ peso($member->requested_amount) }}</td>
                    <td class="px-3 py-3 text-sm text-gray-500">{{ $member->denial_reason ?? '—' }}</td>
                    <td class="px-4 px-md-5 py-3 text-end text-nowrap">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $member->id }}">View</button>
                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#resubmitModal{{ $member->id }}"><i class="fas fa-redo me-2 text-gray-500"></i>Resubmit to Admin</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $member->id }}"><i class="fas fa-archive me-2"></i>Archive</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<x-flow-strip class="mt-5" title="How a batch moves" :steps="['Farmers join (up to '.($batches->max('capacity') ?: 10).')', 'Sent to Administrator when full', 'Approved or denied', 'Manager finalizes', 'Tracked in Batch Loans']" />
@endif

@foreach($batchesReadyToFinalize as $batch)
<!-- Batch Finalize Modal -->
<div class="modal fade" id="batchFinalizeModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i>Finalize {{ $batch->label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.loan-request.batch-finalize', $batch) }}" method="POST">
                @csrf
                <input type="hidden" name="source_modal" value="batchFinalizeModal{{ $batch->id }}">
                <div class="modal-body">
                    <p class="text-muted small mb-3">Confirm to finalize all {{ $batch->loanRequests->count() }} approved members below into loans at once, each using their own requested amount and repayment term. This can't be undone from here. Set one interest rate and one disbursement date/method to apply across the batch — choosing today releases funds immediately, choosing a future date schedules it to disburse automatically that day.</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Farmer</th>
                                    <th class="small">Amount</th>
                                    <th class="small">Terms</th>
                                    <th class="small">Collateral</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->loanRequests as $member)
                                <tr>
                                    <td class="small fw-medium text-dark">{{ $member->farmer->full_name }}</td>
                                    <td class="small">{{ peso($member->requested_amount) }}</td>
                                    <td class="small">{{ $member->repayment_terms_months }} months</td>
                                    <td class="small text-muted">{{ $member->collateral ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Interest Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate" class="form-control" value="2.00" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Disbursement Date <span class="text-danger">*</span></label>
                            <input type="date" name="disbursement_date" class="form-control" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Disbursement Method</label>
                            <input type="text" class="form-control" value="Cash" disabled>
                            <input type="hidden" name="disbursement_method" value="cash">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Reference No.</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm &amp; Finalize Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($batchesWithAdministrator as $batch)
<!-- With Administrator: read-only member view (the actual approve/deny
     decision only happens on the Admin's Loan Approval page) -->
<x-modal id="batchAdminViewModal{{ $batch->id }}" title="{{ $batch->label }} — With the Administrator">
    <p class="text-muted small mb-3">{{ $batch->loanRequests->count() }} member(s), full and awaiting the Administrator's approve/deny decision. This list is read-only here.</p>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small">Farmer</th>
                    <th class="small">Amount</th>
                    <th class="small">Purpose</th>
                    <th class="small">Terms</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batch->loanRequests as $member)
                <tr>
                    <td class="small fw-medium text-dark">{{ $member->farmer->full_name }}</td>
                    <td class="small">{{ peso($member->requested_amount) }}</td>
                    <td class="small text-muted">{{ $member->purpose }}</td>
                    <td class="small text-muted">{{ $member->repayment_terms_months }} months</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted small py-3">No members.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-modal>
@endforeach

@foreach($batchesInProgress as $batch)
<!-- Manage Batch Modal -->
@php
    $capacity = $batch->capacity ?: 10;
    $filled = $batch->loanRequests->count();
    $openSlots = max(0, $capacity - $filled);
@endphp
<div class="modal fade" id="manageBatchModal{{ $batch->id }}" tabindex="-1" aria-labelledby="manageBatchModal{{ $batch->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title text-2xl fw-bold d-flex align-items-center gap-2 mb-0" id="manageBatchModal{{ $batch->id }}Label">
                    Manage {{ $batch->label }}
                    <x-status-badge status="Collecting members" class="text-xs" />
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="px-4 pb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 text-sm mb-1">
                    <span class="text-gray-700"><strong class="text-gray-900">{{ $filled }} of {{ $capacity }}</strong> members &middot; {{ peso($batch->loanRequests->sum('requested_amount')) }} requested</span>
                    <span class="fw-semibold text-warning">{{ $openSlots }} {{ Str::plural('slot', $openSlots) }} open</span>
                </div>
                <div class="progress mb-2" style="height: 8px;" role="progressbar" aria-label="Members" aria-valuenow="{{ $filled }}" aria-valuemin="0" aria-valuemax="{{ $capacity }}">
                    <div class="progress-bar bg-warning" style="width: {{ min(100, round($filled / $capacity * 100)) }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mb-0">When all {{ $capacity }} slots are filled, this batch is sent to the Administrator automatically.</p>
            </div>
            <div class="modal-body p-0 border-top">
                <ol class="batch-slots">
                    @foreach($batch->loanRequests as $member)
                    <li class="batch-slot">
                        <span class="batch-slot-num">{{ $loop->iteration }}</span>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <p class="text-sm fw-semibold text-gray-900 mb-0 text-truncate">{{ $member->farmer->full_name }}</p>
                            <p class="text-xs text-gray-500 mb-0">{{ $member->purpose }} &middot; {{ $member->repayment_terms_months }} months</p>
                        </div>
                        <span class="text-sm fw-bold text-gray-900 text-nowrap">{{ peso($member->requested_amount) }}</span>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="switchModal('manageBatchModal{{ $batch->id }}', 'editModal{{ $member->id }}')">Edit</button>
                        <button type="button" class="btn btn-outline-danger btn-sm px-2" title="Remove from batch" aria-label="Remove {{ $member->farmer->full_name }} from {{ $batch->label }}"
                            onclick="switchModal('manageBatchModal{{ $batch->id }}', 'removeMemberModal{{ $member->id }}')">
                            <i class="fas fa-user-minus"></i>
                        </button>
                    </li>
                    @endforeach
                    @for($slot = $filled + 1; $slot <= $capacity; $slot++)
                    <li class="batch-slot">
                        <span class="batch-slot-num">{{ $slot }}</span>
                        <button type="button" class="batch-slot-open" onclick="openAddFarmerToBatch({{ $batch->id }}, 'manageBatchModal{{ $batch->id }}')">
                            Open slot <span class="batch-slot-add"><i class="fas fa-plus me-1"></i>Add farmer</span>
                        </button>
                    </li>
                    @endfor
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                @if($openSlots > 0)
                <button type="button" class="btn btn-primary" onclick="openAddFarmerToBatch({{ $batch->id }}, 'manageBatchModal{{ $batch->id }}')">
                    <i class="fas fa-plus me-1"></i> Add Farmer
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

@foreach($batch->loanRequests as $member)
<!-- Remove Member Confirmation Modal -->
<div class="modal fade" id="removeMemberModal{{ $member->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-minus me-2"></i>Remove from Batch</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Remove <strong>{{ $member->farmer->full_name }}</strong> from {{ $batch->label }}? This frees their slot for another farmer; they can be re-added later with a new request.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('manager.loan-request.remove-batch-member', $member) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger">Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endforeach

<div class="{{ $batchView ? '' : 'section-card' }}">
    @unless($batchView)
    @php
        $requestTabs = ['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'denied' => 'Denied'];
        $currentRequestStatus = request('status', '');
    @endphp
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 px-md-5 py-4">
        @if($showArchived)
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Archived loan requests</h3>
        @else
        <div class="segmented" role="group" aria-label="Filter by request status">
            @foreach($requestTabs as $value => $label)
            <a href="{{ route('manager.loan-request', array_filter(['status' => $value, 'search' => request('search')])) }}"
                class="{{ $currentRequestStatus === $value ? 'active' : '' }}">{{ $label }} <span class="segmented-count">{{ $value === '' ? $regularCounts->sum() : (int) ($regularCounts[$value] ?? 0) }}</span></a>
            @endforeach
        </div>
        @endif
        <div class="d-flex flex-wrap align-items-center gap-3">
            <form method="GET" action="{{ route('manager.loan-request') }}" id="loanRequestFilterForm" class="position-relative m-0">
                @if($showArchived)
                <input type="hidden" name="archived" value="1">
                @elseif(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="search" id="loanRequestSearchInput" value="{{ request('search') }}" placeholder="Search farmer" class="form-control ps-5" style="min-width: 240px;" autocomplete="off">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </form>
            @if($showArchived)
            <a href="{{ route('manager.loan-request') }}" class="text-sm fw-semibold text-brand text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to Active</a>
            @else
            <a href="{{ route('manager.loan-request', ['archived' => 1]) }}" class="text-sm fw-semibold text-brand text-decoration-none">View Archived ({{ $archivedCount }})</a>
            @endif
        </div>
    </div>

    {{-- Batch Requests (folded to one row per batch): only shown in the
         archived view now. The live lifecycle (collecting/with admin/ready
         to finalize/finalized) has its own card section above instead. --}}
    @if($showArchived && $requestBatchGroups->isNotEmpty())
    <div class="px-4 px-md-6 pb-3">
        <h3 class="text-base fw-semibold text-gray-900 mb-0">Batch Requests</h3>
    </div>
    <div class="table-responsive mb-4">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Batch</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Members</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Total Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requestBatchGroups as $group)
                @php
                    $groupStatuses = $group->members->pluck('status')->unique();
                    $groupStatus = $groupStatuses->count() === 1 ? ucfirst($groupStatuses->first()) : 'Mixed';
                @endphp
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $group->batch?->label ?? 'Batch' }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $group->members->count() }} farmer(s)</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($group->members->sum('requested_amount')) }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$groupStatus" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#viewBatchGroupModal{{ $group->batch?->id }}">View members</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-pagination-footer :paginator="$requestBatchGroups" />

    @foreach($requestBatchGroups as $group)
    <x-modal id="viewBatchGroupModal{{ $group->batch?->id }}" title="{{ $group->batch?->label ?? 'Batch' }} — Requests">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small">Farmer</th>
                        <th class="small">Amount</th>
                        <th class="small">Purpose</th>
                        <th class="small">Terms</th>
                        <th class="small">Status</th>
                        <th class="small">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group->members as $member)
                    <tr>
                        <td class="small fw-medium text-dark">{{ $member->farmer->full_name }}</td>
                        <td class="small">{{ peso($member->requested_amount) }}</td>
                        <td class="small text-muted">{{ $member->purpose }}</td>
                        <td class="small text-muted">{{ $member->repayment_terms_months }} months</td>
                        <td class="small"><x-status-badge :status="ucfirst($member->status)" /></td>
                        <td class="small">
                            <x-action-dropdown>
                                <x-slot:primary><button type="button" class="btn btn-outline-primary btn-sm" onclick="switchModal('viewBatchGroupModal{{ $group->batch?->id }}', 'viewModal{{ $member->id }}')">View</button></x-slot:primary>
                                @if($member->status === 'pending')
                                <x-dropdown-item icon="fa-pen" onclick="switchModal('viewBatchGroupModal{{ $group->batch?->id }}', 'editModal{{ $member->id }}'); return false;">Edit</x-dropdown-item>
                                @endif
                                @if($member->archived_at)
                                <x-dropdown-item icon="fa-box-open" onclick="switchModal('viewBatchGroupModal{{ $group->batch?->id }}', 'unarchiveModal{{ $member->id }}'); return false;">Restore</x-dropdown-item>
                                @elseif($member->status === 'denied')
                                <x-dropdown-item icon="fa-archive" :danger="true" onclick="switchModal('viewBatchGroupModal{{ $group->batch?->id }}', 'archiveModal{{ $member->id }}'); return false;">Archive</x-dropdown-item>
                                @endif
                            </x-action-dropdown>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-modal>
    @endforeach
    @endif

    @if($showArchived && $requestBatchGroups->isNotEmpty())
    <div class="px-4 px-md-5 pb-3">
        <h3 class="text-base fw-semibold text-gray-900 mb-0">Regular requests</h3>
    </div>
    @endif
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted">Farmer and Purpose</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted text-end">Amount</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Terms</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Collateral</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regularRequests as $req)
                @php
                    $canFinalize = $req->status === 'approved' && ! $req->loan;
                    $moreItems = ($canFinalize ? 1 : 0) + ($req->status === 'pending' ? 1 : 0) + ($req->status === 'denied' ? 2 : 0);
                @endphp
                <tr>
                    <td class="px-4 px-md-5 py-3">
                        <p class="text-sm fw-semibold text-gray-900 mb-0">{{ $req->farmer->full_name }}</p>
                        <p class="text-xs text-gray-500 mb-0">{{ $req->purpose }}</p>
                    </td>
                    <td class="px-3 py-3 text-sm text-end fw-semibold text-gray-900">{{ peso($req->requested_amount) }}</td>
                    <td class="px-3 py-3 text-sm text-gray-700">{{ $req->repayment_terms_months }} months</td>
                    <td class="px-3 py-3 text-sm text-gray-700">{{ $req->collateral ?? '—' }}</td>
                    <td class="px-3 py-3"><x-status-badge :status="$req->archived_at ? 'Archived' : ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-5 py-3 text-end text-nowrap">
                        @if($canFinalize)
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#finalizeModal{{ $req->id }}">Finalize Loan</button>
                        @else
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}">View</button>
                        @endif
                        @if($moreItems > 0 || $req->archived_at)
                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($canFinalize)
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}"><i class="fas fa-eye me-2 text-gray-500"></i>View</button></li>
                                @endif
                                @if($req->status === 'pending')
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal{{ $req->id }}"><i class="fas fa-pen me-2 text-gray-500"></i>Edit request</button></li>
                                @endif
                                @if($req->archived_at)
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#unarchiveModal{{ $req->id }}"><i class="fas fa-box-open me-2 text-success"></i>Restore</button></li>
                                @elseif($req->status === 'denied')
                                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#resubmitModal{{ $req->id }}"><i class="fas fa-redo me-2 text-gray-500"></i>Resubmit to Admin</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $req->id }}"><i class="fas fa-archive me-2"></i>Archive</button></li>
                                @endif
                            </ul>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">{{ $showArchived ? 'No archived loan requests.' : 'No regular loan requests found.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination-footer :paginator="$regularRequests" />
    @endunless

    {{-- View/Edit/Finalize/Archive modals for every request (regular rows above,
         and batch members reached via the Batch Requests group modal's own
         View/Edit/Archive buttons through switchModal()). --}}
    @foreach($modalRequests as $req)
                <!-- View Modal -->
                <x-modal id="viewModal{{ $req->id }}" title="Loan Request Details">
                    <div class="row g-3">
                        <div class="d-none col-6"><label class="text-muted small d-block">Request ID</label><p class="fw-medium mb-0">LN-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $req->farmer->full_name }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Loan Type</label><p class="fw-medium mb-0">{{ $req->type === 'batch' ? ($req->batch?->label ?? 'Batch') : 'Regular Loan' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Requested Amount</label><p class="fw-medium mb-0">{{ peso($req->requested_amount) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Purpose</label><p class="fw-medium mb-0">{{ $req->purpose }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $req->repayment_terms_months }} months</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $req->collateral ?? '—' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucfirst($req->status)" /></div>
                        <div class="col-6"><label class="text-muted small d-block">Encoded By</label><p class="fw-medium mb-0">{{ $req->requestedBy->name ?? '—' }}</p></div>
                        @if($req->documents_path)
                        <div class="col-12"><label class="text-muted small d-block">Supporting Document</label><a href="{{ asset('storage/'.$req->documents_path) }}" target="_blank" data-file-viewer data-viewer-title="Supporting Document" class="fw-medium">View Document</a></div>
                        @endif
                        @if($req->status === 'denied' && $req->denial_reason)
                        <div class="col-12"><label class="text-muted small d-block">Denial Reason</label><p class="fw-medium mb-0 text-danger">{{ $req->denial_reason }}</p></div>
                        @endif
                        @if($req->loan)
                        <div class="col-12"><label class="text-muted small d-block">Finalized Loan</label><p class="fw-medium mb-0">{{ $req->loan->status === 'pending_disbursement' ? 'Terms finalized, awaiting disbursement' : 'Active loan created' }} &mdash; see Loan Management.</p></div>
                        @endif
                    </div>

                    @if($req->activityLogs->isNotEmpty())
                    <h4 class="text-sm fw-semibold text-dark mb-2 mt-1">History</h4>
                    <ul class="list-unstyled mb-0 small">
                        @foreach($req->activityLogs as $log)
                        <li class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <p class="mb-0">{{ $log->description }}</p>
                            <p class="text-muted mb-0" style="font-size: 0.75rem;">{{ $log->created_at->format('M d, Y g:i A') }}</p>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </x-modal>

                @if($req->status === 'pending')
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-2"></i>Edit Loan Request</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.loan-request.update', $req) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="source_modal" value="editModal{{ $req->id }}">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                                        <select name="farmer_id" class="form-select farmer-select" required>
                                            @foreach($farmers as $farmer)
                                            @php $blockReason = $farmer->id != $req->farmer_id ? ($farmersIneligibleForNewRequest[$farmer->id] ?? null) : null; @endphp
                                            <option value="{{ $farmer->id }}" {{ $farmer->id == $req->farmer_id ? 'selected' : '' }} {{ $blockReason ? 'disabled' : '' }}>
                                                {{ $farmer->full_name }}{{ $blockReason ? " - {$blockReason}" : '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Loan Type <span class="text-danger">*</span></label>
                                            <select name="type" class="form-select" required onchange="toggleBatchField(this)" data-batch-wrapper="batchField-edit{{ $req->id }}">
                                                <option value="regular" {{ $req->type === 'regular' ? 'selected' : '' }}>Regular Loan</option>
                                                <option value="batch" {{ $req->type === 'batch' ? 'selected' : '' }}>Batch Loan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="batchField-edit{{ $req->id }}" style="{{ $req->type === 'batch' ? '' : 'display:none' }}">
                                            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                                            <select name="batch_id" class="form-select" {{ $req->type === 'batch' ? 'required' : '' }}>
                                                <option value="">Select Batch</option>
                                                @foreach($batches as $batch)
                                                <option value="{{ $batch->id }}" {{ $batch->id == $req->batch_id ? 'selected' : '' }} {{ ($batch->is_full && $batch->id != $req->batch_id) ? 'disabled' : '' }}>
                                                    {{ $batch->label }} ({{ $batch->member_count }}/{{ $batch->capacity }}){{ ($batch->is_full && $batch->id != $req->batch_id) ? ' - Full' : '' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Loan Amount <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="1" name="requested_amount" class="form-control" value="{{ $req->requested_amount }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                                            <select name="purpose" class="form-select" required>
                                                @foreach($purposes as $purpose)
                                                <option value="{{ $purpose }}" {{ $purpose == $req->purpose ? 'selected' : '' }}>{{ $purpose }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Repayment Terms <span class="text-danger">*</span></label>
                                            <select name="repayment_terms_months" class="form-select" required>
                                                @foreach($termOptions as $term)
                                                <option value="{{ $term }}" {{ $term == $req->repayment_terms_months ? 'selected' : '' }}>{{ $term }} months</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Collateral</label>
                                            <input type="text" name="collateral" class="form-control" value="{{ $req->collateral }}">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">Collateral Proof</label>
                                        <input type="file" name="documents" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        @if($req->documents_path)
                                        <div class="form-text">
                                            Current file: <a href="{{ asset('storage/'.$req->documents_path) }}" target="_blank" data-file-viewer data-viewer-title="Supporting Document">View Photo</a>. Choosing a new file will replace it.
                                        </div>
                                        @else
                                        <div class="form-text">No file attached yet.</div>
                                        @endif
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

                @if($req->status === 'denied' && !$req->archived_at)
                <!-- Resubmit Modal -->
                <div class="modal fade" id="resubmitModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-redo me-2"></i>Resubmit to Admin</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.loan-request.resubmit', $req) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="source_modal" value="resubmitModal{{ $req->id }}">
                                <div class="modal-body">
                                    @if($req->denial_reason)
                                    <div class="alert alert-danger py-2 small mb-3"><strong>Denied:</strong> {{ $req->denial_reason }}</div>
                                    @endif
                                    <p class="text-muted small mb-3">Correct whatever caused the denial below, then resubmit — this sends it straight back to the Administrator as a new pending request. No need to start over.</p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                                        <select name="farmer_id" class="form-select farmer-select" required>
                                            @foreach($farmers as $farmer)
                                            @php $blockReason = $farmer->id != $req->farmer_id ? ($farmersIneligibleForNewRequest[$farmer->id] ?? null) : null; @endphp
                                            <option value="{{ $farmer->id }}" {{ $farmer->id == $req->farmer_id ? 'selected' : '' }} {{ $blockReason ? 'disabled' : '' }}>
                                                {{ $farmer->full_name }}{{ $blockReason ? " - {$blockReason}" : '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Loan Type <span class="text-danger">*</span></label>
                                            <select name="type" class="form-select" required onchange="toggleBatchField(this)" data-batch-wrapper="batchField-resubmit{{ $req->id }}">
                                                <option value="regular" {{ $req->type === 'regular' ? 'selected' : '' }}>Regular Loan</option>
                                                <option value="batch" {{ $req->type === 'batch' ? 'selected' : '' }}>Batch Loan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="batchField-resubmit{{ $req->id }}" style="{{ $req->type === 'batch' ? '' : 'display:none' }}">
                                            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                                            <select name="batch_id" class="form-select" {{ $req->type === 'batch' ? 'required' : '' }}>
                                                <option value="">Select Batch</option>
                                                @foreach($batches as $batch)
                                                <option value="{{ $batch->id }}" {{ $batch->id == $req->batch_id ? 'selected' : '' }} {{ ($batch->is_full && $batch->id != $req->batch_id) ? 'disabled' : '' }}>
                                                    {{ $batch->label }} ({{ $batch->member_count }}/{{ $batch->capacity }}){{ ($batch->is_full && $batch->id != $req->batch_id) ? ' - Full' : '' }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Loan Amount <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="1" name="requested_amount" class="form-control" value="{{ $req->requested_amount }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                                            <select name="purpose" class="form-select" required>
                                                @foreach($purposes as $purpose)
                                                <option value="{{ $purpose }}" {{ $purpose == $req->purpose ? 'selected' : '' }}>{{ $purpose }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Repayment Terms <span class="text-danger">*</span></label>
                                            <select name="repayment_terms_months" class="form-select" required>
                                                @foreach($termOptions as $term)
                                                <option value="{{ $term }}" {{ $term == $req->repayment_terms_months ? 'selected' : '' }}>{{ $term }} months</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Collateral</label>
                                            <input type="text" name="collateral" class="form-control" value="{{ $req->collateral }}">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">Collateral Proof</label>
                                        <input type="file" name="documents" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        @if($req->documents_path)
                                        <div class="form-text">
                                            Current file: <a href="{{ asset('storage/'.$req->documents_path) }}" target="_blank" data-file-viewer data-viewer-title="Supporting Document">View Photo</a>. Choosing a new file will replace it.
                                        </div>
                                        @else
                                        <div class="form-text">No file attached yet.</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-info text-white">
                                        <i class="fas fa-redo me-1"></i> Resubmit to Admin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @if($req->type !== 'batch' && $req->status === 'approved' && !$req->loan)
                <!-- Finalize Modal -->
                <div class="modal fade" id="finalizeModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i>Finalize Loan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.loan-request.finalize', $req) }}" method="POST">
                                @csrf
                                <input type="hidden" name="source_modal" value="finalizeModal{{ $req->id }}">
                                <div class="modal-body">
                                    <p class="text-muted small">Confirm the terms below to finalize {{ $req->farmer->full_name }}'s loan and send it to disbursement. This can't be undone from here.</p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Approved Principal Amount <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="1" name="principal_amount" class="form-control" value="{{ $req->requested_amount }}" required>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Interest Rate (%) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0" max="100" name="interest_rate" class="form-control" value="2.00" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Repayment Terms (months) <span class="text-danger">*</span></label>
                                            <input type="number" min="1" name="repayment_terms_months" class="form-control" value="{{ $req->repayment_terms_months }}" required>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label fw-semibold">Collateral</label>
                                        <input type="text" name="collateral" class="form-control" value="{{ $req->collateral }}">
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Confirm &amp; Send to Disbursement</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Archive Modal -->
                <div class="modal fade" id="archiveModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-archive me-2"></i>Archive Loan Request</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Archive this request for {{ $req->farmer->full_name }}? It will be removed from the active list but kept for records.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.loan-request.archive', $req) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary">Archive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @if($req->archived_at)
                <!-- Unarchive (Restore) Modal -->
                <div class="modal fade" id="unarchiveModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2"></i>Restore Loan Request</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Restore this request for {{ $req->farmer->full_name }}? It will reappear on the active list as a denied request.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.loan-request.unarchive', $req) }}" method="POST">
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
</div>

@unless($batchView || $showArchived)
<x-flow-strip class="mt-5" title="How a loan moves" :steps="['Farmer requests', 'Manager reviews', 'Approved or denied', 'Released in Loan Management', 'Paid through Payments']" />
@endunless

<!-- Create Request Modal -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>New Loan Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.loan-request.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="source_modal" value="createRequestModal">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                        <select name="farmer_id" id="createFarmerSelect" class="form-select farmer-select" required>
                            <option value="">Select Farmer</option>
                            @foreach($farmers as $farmer)
                            @php $blockReason = $farmersIneligibleForNewRequest[$farmer->id] ?? null; @endphp
                            <option value="{{ $farmer->id }}" {{ $blockReason ? 'disabled' : '' }}>
                                {{ $farmer->full_name }}{{ $blockReason ? " - {$blockReason}" : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Loan Type <span class="text-danger">*</span></label>
                            <select name="type" id="createTypeSelect" class="form-select" required onchange="toggleBatchField(this)" data-batch-wrapper="batchField-create">
                                <option value="regular" selected>Regular Loan</option>
                                <option value="batch">Batch Loan</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="batchField-create" style="display:none">
                            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                            <select name="batch_id" id="createBatchSelect" class="form-select">
                                <option value="">Select Batch</option>
                                @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ $batch->is_full ? 'disabled' : '' }}>
                                    {{ $batch->label }} ({{ $batch->member_count }}/{{ $batch->capacity }}){{ $batch->is_full ? ' - Full' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Loan Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="1" name="requested_amount" class="form-control" placeholder="&#8369;0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                            <select name="purpose" class="form-select" required>
                                <option value="">Select Purpose</option>
                                @foreach($purposes as $purpose)
                                <option value="{{ $purpose }}">{{ $purpose }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Repayment Terms <span class="text-danger">*</span></label>
                            <select name="repayment_terms_months" class="form-select" required>
                                <option value="">Select Terms</option>
                                @foreach($termOptions as $term)
                                <option value="{{ $term }}">{{ $term }} months</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Collateral <span class="text-danger">*</span></label>
                            <input type="text" name="collateral" class="form-control" placeholder="Describe collateral" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Collateral Proof <span class="text-danger">*</span></label>
                        <input type="file" name="documents" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-info-banner variant="info" title="Loan Approval Workflow" class="mt-6">
    New requests are forwarded to the Administrator for final authorization. Once approved, use "Finalize into Loan" here to lock in the terms; the loan then waits in Loan Management for funds to actually be released via "Mark Disbursed" before repayment tracking begins. Batch Loans group up to 10 farmers under one batch, but each member's repayment, balance, due date, and status are tracked individually. A batch is only sent to the Administrator for approval once it has reached its full 10 members, and once approved, use "Finalize Batch" above to send every member to disbursement in one step.
</x-info-banner>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
    // Upgrades the Farmer <select> in the New/Edit Loan Request modals into a
    // searchable dropdown once its modal is shown (Tom Select needs a visible,
    // laid-out element to size itself against correctly).
    document.addEventListener('shown.bs.modal', function (event) {
        event.target.querySelectorAll('select.farmer-select').forEach(function (select) {
            if (select.tomselect) {
                return;
            }

            new TomSelect(select, {
                placeholder: 'Search farmer by name...',
                sortField: { field: 'text', direction: 'asc' },
            });
        });
    });

    function toggleBatchField(select) {
        var wrapper = document.getElementById(select.dataset.batchWrapper);
        var batchSelect = wrapper.querySelector('select[name="batch_id"]');
        var isBatch = select.value === 'batch';

        wrapper.style.display = isBatch ? '' : 'none';
        batchSelect.required = isBatch;

        if (!isBatch) {
            batchSelect.value = '';
        }
    }

    // Hides one modal and, once it's fully closed, opens another. Bootstrap
    // doesn't support two open modals cleanly, so the batch-management modal
    // must finish closing before the edit/remove/add-farmer modal opens.
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

    // "Add Farmer" from within a batch's management modal: pre-selects Batch
    // Loan and this batch in the New Request form before showing it.
    function openAddFarmerToBatch(batchId, manageModalId) {
        switchModal(manageModalId, 'createRequestModal');

        var typeSelect = document.getElementById('createTypeSelect');
        var batchSelect = document.getElementById('createBatchSelect');
        typeSelect.value = 'batch';
        toggleBatchField(typeSelect);
        batchSelect.value = batchId;
    }
</script>

<!-- Validation Error Dialog: shown instead of a page-top banner so whichever
     modal was submitted doesn't just vanish behind a wall of text on a
     failed submit — dismissing this reopens it (via source_modal). -->
<div class="modal fade" id="validationErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Okay, let me fix it</button>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var errorModalEl = document.getElementById('validationErrorModal');
        var errorModal = new bootstrap.Modal(errorModalEl);
        errorModal.show();

        errorModalEl.addEventListener('hidden.bs.modal', function () {
            var sourceId = {!! json_encode(old('source_modal')) !!};
            var sourceEl = sourceId ? document.getElementById(sourceId) : null;

            if (sourceEl) {
                new bootstrap.Modal(sourceEl).show();
            }
        }, { once: true });
    });
</script>
@endif

<script>
    if (document.getElementById('loanRequestSearchInput')) {
        wireDebouncedSearch('loanRequestSearchInput', 'loanRequestFilterForm');
    }

    // Batch requests view: stage tabs and "Search farmer in batches" filter the cards.
    document.querySelectorAll('[data-batch-board]').forEach(function (board) {
        var search = board.querySelector('[data-batch-search]');
        var cards = board.querySelectorAll('[data-batch-card]');
        var empty = board.querySelector('[data-batch-empty]');
        var stage = '';

        function apply() {
            var term = search.value.trim().toLowerCase();
            var shown = 0;
            cards.forEach(function (card) {
                var match = (!stage || card.dataset.stage === stage) && (!term || card.dataset.names.indexOf(term) !== -1);
                card.classList.toggle('d-none', !match);
                if (match) { shown++; }
            });
            empty.classList.toggle('d-none', shown > 0 || cards.length === 0);
        }

        board.querySelectorAll('[data-stage-filter]').forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                stage = tab.dataset.stageFilter;
                board.querySelectorAll('[data-stage-filter]').forEach(function (t) { t.classList.toggle('active', t === tab); });
                apply();
            });
        });
        search.addEventListener('input', apply);
    });
</script>
@endsection
