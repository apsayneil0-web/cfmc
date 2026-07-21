@extends('admin.layout')

@section('title', 'Loan Approval')
@section('header', 'Farmer Loan Approval')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Pending Requests" value="{{ $requests->count() + $batchGroups->sum(fn($b) => $b->loanRequests->count()) }}" icon="fa-hourglass-half" color="warning" />
    <x-stat-card label="Approved This Month" value="{{ $approvedThisMonth }}" icon="fa-check-circle" color="success" />
    <x-stat-card label="Denied This Month" value="{{ $deniedThisMonth }}" icon="fa-times-circle" color="danger" />
</div>

<!-- Pending Batch Loan Requests -->
@if($batchGroups->isNotEmpty())
<div class="section-card mb-6">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Pending Batch Loan Requests</h3>
            <span class="text-sm text-muted">Full batches (10/10 members) ready for a single approve/deny decision</span>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Batch</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Members</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Total Requested</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Earliest Application</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batchGroups as $batch)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $batch->label }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $batch->loanRequests->count() }} farmer(s)</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($batch->loanRequests->sum('requested_amount')) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $batch->loanRequests->min('created_at')?->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="Review Batch" data-bs-toggle="modal" data-bs-target="#batchReviewModal{{ $batch->id }}"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                </tr>

                <!-- Batch Review Modal -->
                <div class="modal fade" id="batchReviewModal{{ $batch->id }}" tabindex="-1" aria-labelledby="batchReviewModalLabel{{ $batch->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold" id="batchReviewModalLabel{{ $batch->id }}">
                                    <i class="fas fa-layer-group me-2"></i>Review {{ $batch->label }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-3">Approving or denying applies to all {{ $batch->loanRequests->count() }} pending members below at once. Each farmer's repayment is still tracked individually once finalized.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="small">Farmer</th>
                                                <th class="small">Amount</th>
                                                <th class="small">Purpose</th>
                                                <th class="small">Terms</th>
                                                <th class="small">Applied</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($batch->loanRequests as $member)
                                            <tr>
                                                <td class="small fw-medium text-dark">{{ $member->farmer->full_name }}</td>
                                                <td class="small">{{ peso($member->requested_amount) }}</td>
                                                <td class="small text-muted">{{ $member->purpose }}</td>
                                                <td class="small text-muted">{{ $member->repayment_terms_months }} months</td>
                                                <td class="small text-muted">{{ $member->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <form id="denyFormBatch{{ $batch->id }}" action="{{ route('admin.loan-approval.batch-deny', $batch) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <label class="form-label fw-semibold small">Reason for Denial <span class="text-muted">(required if denying, applied to all members)</span></label>
                                    <textarea name="denial_reason" class="form-control" rows="2" placeholder="Explain why this batch does not meet the cooperative's lending criteria..."></textarea>
                                </form>
                                <form id="approveFormBatch{{ $batch->id }}" action="{{ route('admin.loan-approval.batch-approve', $batch) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('PATCH')
                                </form>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" onclick="confirmLoanAction('deny', 'Batch{{ $batch->id }}', {{ Js::from($batch->label.'\'s '.$batch->loanRequests->count().' loan requests') }})">
                                    <i class="fas fa-times me-1"></i> Deny Batch
                                </button>
                                <button type="button" class="btn btn-success" onclick="confirmLoanAction('approve', 'Batch{{ $batch->id }}', {{ Js::from($batch->label.'\'s '.$batch->loanRequests->count().' loan requests') }})">
                                    <i class="fas fa-check me-1"></i> Approve Batch
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Pending Regular Loan Requests -->
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <h3 class="text-lg font-semibold text-gray-900 mb-0 me-2">Pending Regular Loan Requests</h3>
            <span class="text-sm text-muted">Validated by the Manager, awaiting final authorization</span>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Requested Amount</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Repayment Terms</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Application Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Membership Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">{{ $req->farmer->full_name }}</td>
                    <td class="px-4 px-md-6 py-4">{{ peso($req->requested_amount) }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->purpose }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->repayment_terms_months }} months</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$req->farmer->status === 'approved' ? 'Member' : 'Non-member'" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="Review" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $req->id }}"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                </tr>

                <!-- Review Modal -->
                <div class="modal fade" id="reviewModal{{ $req->id }}" tabindex="-1" aria-labelledby="reviewModalLabel{{ $req->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold" id="reviewModalLabel{{ $req->id }}">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>Review Loan Request
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Farmer Name</label>
                                        <p class="fw-semibold mb-0">{{ $req->farmer->full_name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small d-block">Membership Status</label>
                                        <x-status-badge :status="$req->farmer->status === 'approved' ? 'Member' : 'Non-member'" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Requested Amount</label>
                                        <p class="fw-semibold mb-0">{{ peso($req->requested_amount) }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Purpose</label>
                                        <p class="fw-semibold mb-0">{{ $req->purpose }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Repayment Terms</label>
                                        <p class="fw-semibold mb-0">{{ $req->repayment_terms_months }} months</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Collateral</label>
                                        <p class="fw-semibold mb-0">{{ $req->collateral ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Encoded By</label>
                                        <p class="fw-semibold mb-0">{{ $req->requestedBy->name ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Application Date</label>
                                        <p class="fw-semibold mb-0">{{ $req->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="text-muted small">Supporting Document</label>
                                        <div class="mt-2">
                                            @if($req->documents_path)
                                                <a href="{{ asset('storage/'.$req->documents_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file me-1"></i> View Document
                                                </a>
                                            @else
                                                <span class="text-muted">No document uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <form id="denyForm{{ $req->id }}" action="{{ route('admin.loan-approval.deny', $req) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <label class="form-label fw-semibold small">Reason for Denial <span class="text-muted">(required if denying)</span></label>
                                    <textarea name="denial_reason" class="form-control" rows="2" placeholder="Explain why this request does not meet the cooperative's lending criteria..."></textarea>
                                </form>
                                <form id="approveForm{{ $req->id }}" action="{{ route('admin.loan-approval.approve', $req) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('PATCH')
                                </form>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" onclick="confirmLoanAction('deny', {{ $req->id }}, {{ Js::from($req->farmer->full_name.'\'s loan request') }})">
                                    <i class="fas fa-times me-1"></i> Deny
                                </button>
                                <button type="button" class="btn btn-success" onclick="confirmLoanAction('approve', {{ $req->id }}, {{ Js::from($req->farmer->full_name.'\'s loan request') }})">
                                    <i class="fas fa-check me-1"></i> Approve
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">No pending regular loan requests.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Approve/Deny Confirmation Modal -->
<div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-labelledby="actionConfirmTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4 pt-0 text-center">
                <div class="action-confirm-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3" id="actionConfirmBadge">
                    <i class="fas" id="actionConfirmIcon"></i>
                </div>
                <h5 class="fw-bold mb-2" id="actionConfirmTitle">Confirm Action</h5>
                <p class="text-muted mb-4" id="actionConfirmMessage">-</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill py-2" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn flex-fill py-2" id="actionConfirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .action-confirm-icon-badge {
        width: 64px;
        height: 64px;
        font-size: 1.75rem;
    }
    .action-confirm-icon-badge.success {
        background-color: var(--brand-success-light);
        color: var(--brand-success);
    }
    .action-confirm-icon-badge.danger {
        background-color: var(--brand-danger-light);
        color: var(--brand-danger);
    }
</style>

<script>
    var pendingLoanAction = null;

    // Shows a review step before an approve/deny form is actually submitted.
    // idSuffix identifies the target form: a numeric loan_request id for an
    // individual decision, or "Batch{id}" for a whole-batch decision.
    function confirmLoanAction(type, idSuffix, subjectPhrase) {
        var isApprove = type === 'approve';

        if (!isApprove) {
            var reasonField = document.querySelector('#denyForm' + idSuffix + ' textarea[name="denial_reason"]');
            if (!reasonField.value.trim()) {
                reasonField.classList.add('is-invalid');
                reasonField.focus();
                return;
            }
            reasonField.classList.remove('is-invalid');
        }

        pendingLoanAction = { type: type, id: idSuffix };

        document.getElementById('actionConfirmBadge').className =
            'action-confirm-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3 ' + (isApprove ? 'success' : 'danger');
        document.getElementById('actionConfirmIcon').className = 'fas ' + (isApprove ? 'fa-check-circle' : 'fa-times-circle');
        document.getElementById('actionConfirmTitle').textContent = isApprove ? 'Confirm Approval' : 'Confirm Denial';
        document.getElementById('actionConfirmMessage').textContent = 'Are you sure you want to ' + (isApprove ? 'approve' : 'deny') +
            ' ' + subjectPhrase + '? This action cannot be undone.';

        var confirmBtn = document.getElementById('actionConfirmBtn');
        confirmBtn.className = 'btn flex-fill py-2 ' + (isApprove ? 'btn-success' : 'btn-danger');
        confirmBtn.innerHTML = isApprove
            ? '<i class="fas fa-check me-2"></i>Yes, Approve'
            : '<i class="fas fa-times me-2"></i>Yes, Deny';

        new bootstrap.Modal(document.getElementById('actionConfirmModal')).show();
    }

    document.getElementById('actionConfirmBtn').addEventListener('click', function() {
        if (!pendingLoanAction) {
            return;
        }
        var formId = (pendingLoanAction.type === 'approve' ? 'approveForm' : 'denyForm') + pendingLoanAction.id;
        document.getElementById(formId).submit();
    });
</script>

<x-info-banner variant="info" title="Loan Approval Workflow" class="mt-6">
    Loan requests are validated by the Manager before being forwarded here for the Administrator's final authorization. Approving or denying a request permanently logs the decision; approved requests are then finalized into an active loan by the Manager. Batch Loans only appear here once their batch has filled all 10 member slots &mdash; partially-filled batches stay on the Manager's Loan Request page until then.
</x-info-banner>
@endsection
