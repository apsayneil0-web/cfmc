@extends('farmer.layout')

@section('title', 'Complaints')
@section('header', 'Complaints Management')

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

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-4 p-md-6 border-b border-gray-200 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-0">My Complaints</h3>
            <p class="text-sm text-muted mb-0">Draft complaints stay private until you submit them for review.</p>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus"></i>
            <span>New Complaint</span>
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Subject</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Submitted</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium">{{ $complaint->subject }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $complaint->created_at->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucwords(str_replace('_', ' ', $complaint->status))" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $complaint->id }}"><i class="fas fa-eye"></i></button>
                            @if($complaint->status == 'draft')
                            <button class="btn btn-sm btn-outline-warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $complaint->id }}"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $complaint->id }}"><i class="fas fa-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <div class="modal fade" id="viewModal{{ $complaint->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-file-alt me-2"></i>Complaint Details</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label class="text-muted small">Subject</label>
                                <p class="fw-semibold">{{ $complaint->subject }}</p>
                                <label class="text-muted small">Description</label>
                                <p>{{ $complaint->description }}</p>
                                <label class="text-muted small">Status</label>
                                <p><x-status-badge :status="ucwords(str_replace('_', ' ', $complaint->status))" /></p>
                                @if($complaint->manager_response)
                                <label class="text-muted small">Manager Response</label>
                                <p class="mb-0">{{ $complaint->manager_response }}</p>
                                @endif
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                @if($complaint->status == 'draft')
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $complaint->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-2"></i>Edit Complaint</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('farmer.complaints.update', $complaint->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject</label>
                                        <input type="text" name="subject" class="form-control" value="{{ $complaint->subject }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="4" required>{{ $complaint->description }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary">Save Draft</button>
                                    <button type="submit" name="action" value="submit" class="btn btn-primary">Submit for Review</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal{{ $complaint->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-trash me-2"></i>Delete Draft</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Are you sure you want to delete this draft complaint? This cannot be undone.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('farmer.complaints.destroy', $complaint->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <tr>
                    <td colspan="4" class="px-4 px-md-6 py-6 text-center text-muted">No complaints yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>New Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('farmer.complaints.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe your concern..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary">Save as Draft</button>
                    <button type="submit" name="action" value="submit" class="btn btn-primary">Submit for Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
