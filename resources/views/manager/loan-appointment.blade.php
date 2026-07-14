@extends('manager.layout')

@section('title', 'Loan Appointments')
@section('header', 'Loan Appointment Requests')

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
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <x-stat-card label="Pending Requests" value="{{ $stats['pending_count'] }}" icon="fa-hourglass-half" color="warning" />
    <x-stat-card label="Approved" value="{{ $stats['approved_count'] }}" icon="fa-check-circle" color="success" />
    <x-stat-card label="Upcoming Appointments" value="{{ $stats['upcoming_count'] }}" icon="fa-calendar-day" color="primary" />
</div>

<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <form method="GET" action="{{ route('manager.loan-appointment') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search farmer..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control" style="width: auto;" onchange="this.form.submit()">
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status', 'date']))
                <a href="{{ route('manager.loan-appointment') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
    </x-table-toolbar>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Appointment ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appt)
                @php $farmerName = $appt->user->farmer?->full_name ?? $appt->user->name; @endphp
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">APT-{{ str_pad($appt->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $farmerName }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $appt->appointment_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $appt->purpose }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($appt->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#viewModal{{ $appt->id }}" />
                            @if($appt->status === 'pending')
                            <x-icon-button icon="fa-check" color="success" title="Approve" data-bs-toggle="modal" data-bs-target="#approveModal{{ $appt->id }}" />
                            @endif
                            @if($appt->status !== 'cancelled')
                            <x-icon-button icon="fa-times" color="danger" title="Cancel" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $appt->id }}" />
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <x-modal id="viewModal{{ $appt->id }}" title="Appointment Details">
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small d-block">Appointment ID</label><p class="fw-medium mb-0">APT-{{ str_pad($appt->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $farmerName }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $appt->appointment_date->format('M d, Y') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Time</label><p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($appt->appointment_time)->format('g:i A') }}</p></div>
                        <div class="col-12"><label class="text-muted small d-block">Purpose</label><p class="fw-medium mb-0">{{ $appt->purpose }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucfirst($appt->status)" /></div>
                        <div class="col-6"><label class="text-muted small d-block">Requested On</label><p class="fw-medium mb-0">{{ $appt->created_at->format('M d, Y') }}</p></div>
                    </div>
                </x-modal>

                @if($appt->status === 'pending')
                <!-- Approve Modal -->
                <div class="modal fade" id="approveModal{{ $appt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>Approve Appointment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Approve the appointment request from <strong>{{ $farmerName }}</strong> on {{ $appt->appointment_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appt->appointment_time)->format('g:i A') }}?</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.loan-appointment.approve', $appt) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($appt->status !== 'cancelled')
                <!-- Cancel Modal -->
                <div class="modal fade" id="cancelModal{{ $appt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>Cancel Appointment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Cancel the appointment for <strong>{{ $farmerName }}</strong> on {{ $appt->appointment_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appt->appointment_time)->format('g:i A') }}? The farmer will need to submit a new request if they still need one.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                <form action="{{ route('manager.loan-appointment.cancel', $appt) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">No loan appointment requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-info-banner variant="info" title="Loan Appointment Requests" class="mt-6">
    Farmers submit appointment requests to discuss loan applications. Approving confirms the slot; cancelling frees it up and the farmer will need to submit a new request if they still need one.
</x-info-banner>
@endsection
