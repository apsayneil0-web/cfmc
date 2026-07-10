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

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-4 p-md-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Pending Applications</h3>
        <p class="text-sm text-muted mb-0">Review farmer membership applications against the cooperative's criteria and bylaws.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Applicant</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Barangay</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Contact</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Crop</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date Applied</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium">{{ $application->full_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $application->barangay ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $application->contact_number }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $application->crop->name ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $application->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="Review" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $application->id }}"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                </tr>

                <!-- Review Modal -->
                <div class="modal fade" id="reviewModal{{ $application->id }}" tabindex="-1" aria-labelledby="reviewModalLabel{{ $application->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold" id="reviewModalLabel{{ $application->id }}">
                                    <i class="fas fa-user-check me-2"></i>Review Membership Application
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Full Name</label>
                                        <p class="fw-semibold mb-0">{{ $application->full_name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Contact Number</label>
                                        <p class="fw-semibold mb-0">{{ $application->contact_number }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Barangay</label>
                                        <p class="fw-semibold mb-0">{{ $application->barangay ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Municipality / Province</label>
                                        <p class="fw-semibold mb-0">{{ $application->municipality }}, {{ $application->province }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Crop Type</label>
                                        <p class="fw-semibold mb-0">{{ $application->crop->name ?? '—' }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Land Area</label>
                                        <p class="fw-semibold mb-0">{{ $application->land_area }} hectares</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Date Applied</label>
                                        <p class="fw-semibold mb-0">{{ $application->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="text-muted small">Uploaded Document</label>
                                        <div class="mt-2">
                                            @if($application->documents_path)
                                                <a href="{{ asset('storage/' . $application->documents_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file me-1"></i> View Document
                                                </a>
                                            @else
                                                <span class="text-muted">No document uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <form id="rejectForm{{ $application->id }}" action="{{ route('admin.membership-approval.reject', $application->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <label class="form-label fw-semibold small">Reason for Rejection <span class="text-muted">(required if rejecting)</span></label>
                                    <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Explain why this application does not meet the cooperative's requirements..."></textarea>
                                </form>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" form="rejectForm{{ $application->id }}" class="btn btn-danger">
                                    <i class="fas fa-times me-1"></i> Reject
                                </button>
                                <form action="{{ route('admin.membership-approval.approve', $application->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No pending membership applications.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
