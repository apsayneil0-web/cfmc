@extends('farmer.layout')

@section('title', 'Loan Appointment')
@section('header', 'Loan Appointment')

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
            <h3 class="text-lg font-semibold text-gray-900 mb-0">My Loan Appointments</h3>
            <p class="text-sm text-muted mb-0">Schedule a meeting with cooperative management to discuss your loan.</p>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus"></i>
            <span>New Appointment</span>
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium">{{ $appointment->appointment_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $appointment->purpose }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($appointment->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        @if($appointment->status == 'pending')
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-warning" title="Reschedule" data-bs-toggle="modal" data-bs-target="#editModal{{ $appointment->id }}"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Cancel" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $appointment->id }}"><i class="fas fa-times"></i></button>
                        </div>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>

                <!-- Edit / Reschedule Modal -->
                <div class="modal fade" id="editModal{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-2"></i>Reschedule Appointment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('farmer.loan-appointment.update', $appointment->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Date</label>
                                        <input type="date" name="appointment_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ $appointment->appointment_date->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Time</label>
                                        <input type="time" name="appointment_time" class="form-control" value="{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Purpose</label>
                                        <input type="text" name="purpose" class="form-control" value="{{ $appointment->purpose }}" required>
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

                <!-- Cancel Modal -->
                <div class="modal fade" id="cancelModal{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Cancel Appointment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Are you sure you want to cancel your appointment on {{ $appointment->appointment_date->format('M d, Y') }}?</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                                <form action="{{ route('farmer.loan-appointment.cancel', $appointment->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="5" class="px-4 px-md-6 py-6 text-center text-muted">No loan appointments yet.</td>
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
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>New Loan Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('farmer.loan-appointment.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="appointment_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                        <input type="time" name="appointment_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" class="form-control" placeholder="e.g. Discuss loan application requirements" required>
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
@endsection
