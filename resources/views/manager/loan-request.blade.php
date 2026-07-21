@extends('manager.layout')

@section('title', 'Loan Requests')
@section('header', 'Loan Request Management')

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

@if($batchesInProgress->isNotEmpty())
<div class="section-card mb-6">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Batch Loans in Progress</h3>
            <span class="text-sm text-muted">Not yet full &mdash; not yet sent to the Administrator</span>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Batch</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Members</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batchesInProgress as $batch)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $batch->label }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $batch->member_count }}/{{ $batch->capacity }} members</td>
                    <td class="px-4 px-md-6 py-4">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageBatchModal{{ $batch->id }}">
                            <i class="fas fa-users-cog me-1"></i> Manage Batch
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@foreach($batchesInProgress as $batch)
<!-- Manage Batch Modal -->
<div class="modal fade" id="manageBatchModal{{ $batch->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-users-cog me-2"></i>Manage {{ $batch->label }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">{{ $batch->member_count }}/{{ $batch->capacity }} members. This batch will be sent to the Administrator automatically once it's full.</p>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small">Farmer</th>
                                <th class="small">Amount</th>
                                <th class="small">Purpose</th>
                                <th class="small">Terms</th>
                                <th class="small">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batch->loanRequests as $member)
                            <tr>
                                <td class="small fw-medium text-dark">{{ $member->farmer->full_name }}</td>
                                <td class="small">{{ peso($member->requested_amount) }}</td>
                                <td class="small text-muted">{{ $member->purpose }}</td>
                                <td class="small text-muted">{{ $member->repayment_terms_months }} months</td>
                                <td class="small">
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-warning" title="Edit" onclick="switchModal('manageBatchModal{{ $batch->id }}', 'editModal{{ $member->id }}')"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Remove from batch" onclick="switchModal('manageBatchModal{{ $batch->id }}', 'removeMemberModal{{ $member->id }}')"><i class="fas fa-user-minus"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted small py-3">No members yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="openAddFarmerToBatch({{ $batch->id }}, 'manageBatchModal{{ $batch->id }}')">
                    <i class="fas fa-user-plus me-1"></i> Add Farmer
                </button>
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
@endif

<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <form method="GET" action="{{ route('manager.loan-request') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search farmer..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denied</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('manager.loan-request') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                <i class="fas fa-plus"></i><span>New Request</span>
            </button>
        </x-slot:actions>
    </x-table-toolbar>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Request ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Repayment Terms</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Collateral</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">LN-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $req->farmer->full_name }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <span class="badge bg-{{ $req->type === 'batch' ? 'info' : 'primary' }}-subtle text-{{ $req->type === 'batch' ? 'info' : 'primary' }} border border-{{ $req->type === 'batch' ? 'info' : 'primary' }}-subtle">
                            {{ $req->type === 'batch' ? ($req->batch?->label ?? 'Batch') : 'Regular' }}
                        </span>
                        @if($req->type === 'batch' && $req->batch && $req->status === 'pending')
                        <div class="small text-muted mt-1">
                            {{ $req->batch->member_count }}/{{ $req->batch->capacity }} members
                            @if(!$req->batch->is_full)
                            &mdash; awaiting more before admin review
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4">{{ peso($req->requested_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->purpose }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->repayment_terms_months }} months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->collateral ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}" />
                            @if($req->status === 'pending')
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $req->id }}" />
                            @endif
                            @if($req->status === 'approved' && !$req->loan)
                            <x-icon-button icon="fa-file-invoice-dollar" color="success" title="Finalize into Loan" data-bs-toggle="modal" data-bs-target="#finalizeModal{{ $req->id }}" />
                            @endif
                            <x-icon-button icon="fa-archive" color="secondary" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $req->id }}" />
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <x-modal id="viewModal{{ $req->id }}" title="Loan Request Details">
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small d-block">Request ID</label><p class="fw-medium mb-0">LN-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $req->farmer->full_name }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Loan Type</label><p class="fw-medium mb-0">{{ $req->type === 'batch' ? ($req->batch?->label ?? 'Batch') : 'Regular Loan' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Requested Amount</label><p class="fw-medium mb-0">{{ peso($req->requested_amount) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Purpose</label><p class="fw-medium mb-0">{{ $req->purpose }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $req->repayment_terms_months }} months</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $req->collateral ?? '—' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucfirst($req->status)" /></div>
                        <div class="col-6"><label class="text-muted small d-block">Encoded By</label><p class="fw-medium mb-0">{{ $req->requestedBy->name ?? '—' }}</p></div>
                        @if($req->documents_path)
                        <div class="col-12"><label class="text-muted small d-block">Supporting Document</label><a href="{{ asset('storage/'.$req->documents_path) }}" target="_blank" class="fw-medium">View Document</a></div>
                        @endif
                        @if($req->status === 'denied' && $req->denial_reason)
                        <div class="col-12"><label class="text-muted small d-block">Denial Reason</label><p class="fw-medium mb-0 text-danger">{{ $req->denial_reason }}</p></div>
                        @endif
                        @if($req->loan)
                        <div class="col-12"><label class="text-muted small d-block">Finalized Loan</label><p class="fw-medium mb-0">Active loan created &mdash; see Loan Management.</p></div>
                        @endif
                    </div>
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
                            <form action="{{ route('manager.loan-request.update', $req) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                                        <select name="farmer_id" class="form-select" required>
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

                @if($req->status === 'approved' && !$req->loan)
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
                                <div class="modal-body">
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
                                    <button type="submit" class="btn btn-success">Save &amp; Activate Loan</button>
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
                                <p class="mb-0">Archive LN-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }} for {{ $req->farmer->full_name }}? It will be removed from the active list but kept for records.</p>
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
                @empty
                <tr>
                    <td colspan="9" class="px-4 px-md-6 py-6 text-center text-muted">No loan requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

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
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                        <select name="farmer_id" class="form-select" required>
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
                            <label class="form-label fw-semibold">Collateral</label>
                            <input type="text" name="collateral" class="form-control" placeholder="Describe collateral">
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Upload Documents</label>
                        <input type="file" name="documents" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
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
    New requests are forwarded to the Administrator for final authorization. Once approved, use "Finalize into Loan" here to activate it and start tracking repayments in Loan Management. Batch Loans group up to 10 farmers under one batch, but each member's repayment, balance, due date, and status are tracked individually. A batch is only sent to the Administrator for approval once it has reached its full 10 members.
</x-info-banner>

<script>
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
@endsection
