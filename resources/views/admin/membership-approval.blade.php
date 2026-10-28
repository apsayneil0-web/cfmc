@extends('admin.layout')

@section('title', 'Membership Approval')
@section('header', 'Membership Approval')

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
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
    $isRejectedView = $status === 'rejected';
    $documentFields = [
        ['path' => 'documents_path', 'label' => 'Valid ID'],
        ['path' => 'certificate_of_title_path', 'label' => 'Certificate of Title'],
        ['path' => 'barangay_certification_path', 'label' => 'Barangay Certification'],
        ['path' => 'rsbsa_path', 'label' => 'RSBSA Number / ID'],
    ];
    $formatHectares = fn ($value) => number_format((float) $value, 2);
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <p class="text-sm text-gray-500 mb-0">
        @if($isRejectedView)
            Membership applications that were declined, along with the reason given at the time.
        @else
            Membership Approval: applications forwarded by the Manager. Approving creates the farmer's account and sends the login by SMS.
        @endif
    </p>
    <form method="GET" action="{{ route('admin.membership-approval') }}" class="d-flex align-items-center gap-2 m-0">
        @if($isRejectedView)
        <input type="hidden" name="status" value="rejected">
        @endif
        <x-live-search placeholder="Search name, barangay or contact..." width="280px" />
    </form>
</div>

@if($isRejectedView)
<div class="section-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted">Applicant</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Barangay</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Contact</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Crop</th>
                    <th class="px-3 py-3 text-xs font-medium text-uppercase text-muted">Date Rejected</th>
                    <th class="px-4 px-md-5 py-3 text-xs font-medium text-uppercase text-muted">Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                <tr>
                    <td class="px-4 px-md-5 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <x-avatar-initials :name="$application->full_name" />
                            <span class="text-sm fw-semibold text-gray-900">{{ $application->full_name }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-sm text-gray-700">{{ $application->barangay ?? '—' }}</td>
                    <td class="px-3 py-3 text-sm text-gray-700 text-nowrap">{{ phone($application->contact_number) }}</td>
                    <td class="px-3 py-3 text-sm text-gray-700">{{ $application->crops->pluck('name')->implode(', ') ?: '—' }}</td>
                    <td class="px-3 py-3 text-sm text-gray-700 text-nowrap">{{ $application->updated_at->format('M j, Y') }}</td>
                    <td class="px-4 px-md-5 py-3 text-sm text-gray-700">{{ $application->rejection_reason ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">{{ request()->filled('search') ? 'No rejected applications match your search.' : 'No rejected membership applications.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination-footer :paginator="$applications" />
</div>
@elseif($applications->isEmpty())
<div class="section-card p-6 text-center text-muted">
    {{ request()->filled('search') ? 'No pending applications match your search.' : 'No pending membership applications.' }}
</div>
@else
<div class="grid grid-cols-1 lg:grid-cols-[22rem_minmax(0,1fr)] gap-5 align-items-start" data-applications>
    <!-- Applicant list -->
    <div>
        <div class="applicant-list" role="tablist" aria-label="Pending applications">
            @foreach($applications as $application)
            <button type="button" class="applicant-item {{ $loop->first ? 'active' : '' }}" role="tab" id="applicantTab{{ $application->id }}"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="applicantPanel{{ $application->id }}" data-applicant="{{ $application->id }}">
                <x-avatar-initials :name="$application->full_name" />
                <span class="flex-grow-1 text-start" style="min-width: 0;">
                    <span class="d-block text-sm fw-semibold text-gray-900 text-truncate">{{ $application->full_name }}</span>
                    <span class="d-block text-xs text-gray-500 text-truncate">
                        {{ collect([$application->barangay, $application->crops->pluck('name')->implode(', '), $application->land_area !== null ? $formatHectares($application->land_area).' ha' : null])->filter()->implode(' · ') }}
                    </span>
                </span>
                <span class="text-xs text-gray-500 text-nowrap">{{ $application->created_at->format('M j') }}</span>
            </button>
            @endforeach
        </div>
        @if($applications->hasPages())
        <div class="section-card mt-3">
            <x-pagination-footer :paginator="$applications" />
        </div>
        @endif
    </div>

    <!-- Selected applicant -->
    <div>
        @foreach($applications as $application)
        <section class="section-card p-4 p-md-5 {{ $loop->first ? '' : 'd-none' }}" role="tabpanel" id="applicantPanel{{ $application->id }}" aria-labelledby="applicantTab{{ $application->id }}" data-applicant-panel="{{ $application->id }}">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-0">{{ $application->full_name }}</h3>
                    <p class="text-sm text-gray-500 mb-0">Applied {{ $application->created_at->format('M j, Y') }}</p>
                </div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending approval</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-5 gap-y-4 mb-4">
                <div>
                    <p class="detail-label">Contact number</p>
                    <p class="detail-value">{{ phone($application->contact_number) }}</p>
                </div>
                <div>
                    <p class="detail-label">Barangay</p>
                    <p class="detail-value">{{ $application->barangay ?? '—' }}</p>
                </div>
                <div>
                    <p class="detail-label">Municipality / Province</p>
                    <p class="detail-value">{{ collect([$application->municipality, $application->province])->filter()->implode(', ') ?: '—' }}</p>
                </div>
                <div>
                    <p class="detail-label">Crop</p>
                    <p class="detail-value">{{ $application->crops->pluck('name')->implode(', ') ?: '—' }}</p>
                </div>
                <div>
                    <p class="detail-label">Land area</p>
                    <p class="detail-value">{{ $application->land_area !== null ? $formatHectares($application->land_area).' hectares' : '—' }}</p>
                </div>
                <div>
                    <p class="detail-label">RSBSA</p>
                    <p class="detail-value">{{ $application->documentPaths('rsbsa_path') ? 'Submitted' : 'Not submitted' }}</p>
                </div>
            </div>

            <p class="detail-label mb-2">Documents</p>
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
                @foreach($documentFields as $doc)
                @php $paths = $application->documentPaths($doc['path']); @endphp
                <div class="document-tile {{ $paths ? '' : 'is-missing' }}">
                    <div class="document-tile-preview"><i class="far {{ $paths ? 'fa-file' : 'fa-file-excel' }}"></i></div>
                    <p class="text-sm fw-semibold text-gray-900 mb-1">{{ $doc['label'] }}</p>
                    @forelse($paths as $index => $path)
                    @php $isPdf = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf'; @endphp
                    <a href="{{ asset('storage/'.$path) }}" target="_blank" data-file-viewer data-viewer-title="{{ $doc['label'] }}{{ count($paths) > 1 ? ' '.($index + 1) : '' }}"
                        data-viewer-group="application-{{ $application->id }}" class="d-block text-xs fw-semibold text-brand">
                        View {{ $isPdf ? 'PDF' : 'image' }}{{ count($paths) > 1 ? ' '.($index + 1) : '' }}
                    </a>
                    @empty
                    <span class="text-xs text-danger">Not uploaded</span>
                    @endforelse
                </div>
                @endforeach
            </div>

            <form id="rejectForm{{ $application->id }}" action="{{ route('admin.membership-approval.reject', $application->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <label for="rejectionReason{{ $application->id }}" class="form-label text-sm"><strong class="text-gray-900">Reason for rejection</strong> <span class="text-gray-500">(required only when rejecting)</span></label>
                <textarea name="rejection_reason" id="rejectionReason{{ $application->id }}" class="form-control" rows="3" maxlength="1000" placeholder="Explain why this application does not meet the cooperative's requirements..."></textarea>
                <div class="invalid-feedback">Please provide a reason for rejecting this application.</div>
            </form>
            <form id="approveForm{{ $application->id }}" action="{{ route('admin.membership-approval.approve', $application->id) }}" method="POST" class="d-none">
                @csrf
                @method('PATCH')
            </form>

            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-danger px-4" onclick="confirmMembershipAction('reject', {{ $application->id }}, {{ Js::from($application->full_name) }})">Reject</button>
                <button type="button" class="btn btn-primary px-4" onclick="confirmMembershipAction('approve', {{ $application->id }}, {{ Js::from($application->full_name) }})">Approve and send login</button>
            </div>
        </section>
        @endforeach
    </div>
</div>
@endif

<!-- Approve/Reject Confirmation Modal -->
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
    // Applicant list: picking a name shows that application on the right.
    document.querySelectorAll('[data-applications]').forEach(function (root) {
        var items = Array.prototype.slice.call(root.querySelectorAll('[data-applicant]'));

        function select(item) {
            items.forEach(function (other) {
                var on = other === item;
                other.classList.toggle('active', on);
                other.setAttribute('aria-selected', on ? 'true' : 'false');
                root.querySelector('[data-applicant-panel="' + other.dataset.applicant + '"]').classList.toggle('d-none', !on);
            });
        }

        items.forEach(function (item, index) {
            item.addEventListener('click', function () { select(item); });
            item.addEventListener('keydown', function (e) {
                var next = e.key === 'ArrowDown' ? items[index + 1] : (e.key === 'ArrowUp' ? items[index - 1] : null);
                if (next) {
                    e.preventDefault();
                    next.focus();
                    select(next);
                }
            });
        });
    });

    var pendingMembershipAction = null;

    // Shows a review step before an approve/reject form is actually submitted.
    function confirmMembershipAction(type, applicationId, applicantName) {
        var isApprove = type === 'approve';

        if (!isApprove) {
            var reasonField = document.getElementById('rejectForm' + applicationId).elements['rejection_reason'];
            if (!reasonField.value.trim()) {
                reasonField.classList.add('is-invalid');
                reasonField.focus();
                return;
            }
            reasonField.classList.remove('is-invalid');
        }

        pendingMembershipAction = { type: type, id: applicationId };

        document.getElementById('actionConfirmBadge').className =
            'action-confirm-icon-badge d-inline-flex align-items-center justify-content-center rounded-circle mb-3 ' + (isApprove ? 'success' : 'danger');
        document.getElementById('actionConfirmIcon').className = 'fas ' + (isApprove ? 'fa-check-circle' : 'fa-times-circle');
        document.getElementById('actionConfirmTitle').textContent = isApprove ? 'Confirm Approval' : 'Confirm Rejection';
        document.getElementById('actionConfirmMessage').textContent = isApprove
            ? 'Approve ' + applicantName + '\'s membership? This creates their login account and texts the username and password to them. This cannot be undone.'
            : 'Reject ' + applicantName + '\'s membership application? The reason you entered is saved with it. This cannot be undone.';

        var confirmBtn = document.getElementById('actionConfirmBtn');
        confirmBtn.className = 'btn flex-fill py-2 ' + (isApprove ? 'btn-success' : 'btn-danger');
        confirmBtn.innerHTML = isApprove
            ? '<i class="fas fa-check me-2"></i>Yes, Approve'
            : '<i class="fas fa-times me-2"></i>Yes, Reject';

        new bootstrap.Modal(document.getElementById('actionConfirmModal')).show();
    }

    document.getElementById('actionConfirmBtn').addEventListener('click', function() {
        if (!pendingMembershipAction) {
            return;
        }
        var formId = (pendingMembershipAction.type === 'approve' ? 'approveForm' : 'rejectForm') + pendingMembershipAction.id;
        document.getElementById(formId).submit();
    });
</script>
@endsection
