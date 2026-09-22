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
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan Requested</th>
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
                    <td class="px-4 px-md-6 py-4 text-muted">
                        @if($appt->requested_amount)
                        {{ peso($appt->requested_amount) }}
                        <div class="small text-muted">{{ $appt->loan_purpose }} &bull; {{ $appt->repayment_terms_months }} mo.</div>
                        @if($appt->loan_request_id)
                        <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">Submitted to Admin</span>
                        @endif
                        @else
                        —
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($appt->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex align-items-center gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#viewModal{{ $appt->id }}" />

                            @if($appt->status === 'pending')
                            <div class="vr mx-1"></div>
                            <x-icon-button icon="fa-check" color="success" title="Approve Appointment" data-bs-toggle="modal" data-bs-target="#approveModal{{ $appt->id }}" />
                            @endif

                            @if($appt->status === 'approved' && $appt->requested_amount && ! $appt->loan_request_id)
                            <div class="vr mx-1"></div>
                                @if($appt->user->farmer)
                                <x-icon-button icon="fa-paper-plane" color="info" title="Submit Loan Request to Admin" data-bs-toggle="modal" data-bs-target="#submitLoanRequestModal{{ $appt->id }}" />
                                @else
                                <span class="icon-btn text-muted" title="This account has no linked farmer membership record, so a loan request can't be created for it." style="cursor: help;"><i class="fas fa-exclamation-triangle"></i></span>
                                @endif
                            @endif

                            @if($appt->status !== 'cancelled' && ! $appt->loan_request_id)
                            <div class="vr mx-1"></div>
                            <x-icon-button icon="fa-calendar-alt" color="warning" title="Reschedule Appointment" data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $appt->id }}" />
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
                        <div class="col-12"><label class="text-muted small d-block">Appointment Notes</label><p class="fw-medium mb-0">{{ $appt->purpose }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucfirst($appt->status)" /></div>
                        <div class="col-6"><label class="text-muted small d-block">Requested On</label><p class="fw-medium mb-0">{{ $appt->created_at->format('M d, Y') }}</p></div>
                    </div>

                    @if($appt->requested_amount)
                    <hr>
                    <h4 class="text-sm fw-semibold text-dark mb-2">Loan Pre-Request</h4>
                    @if($appt->loan_request_id)
                    <p class="text-success small mb-3"><i class="fas fa-check-circle me-1"></i> Submitted to the Administrator as LN-REQ-{{ str_pad($appt->loan_request_id, 3, '0', STR_PAD_LEFT) }}.</p>
                    @else
                    <p class="text-muted small mb-3">Submitted by the farmer for your review. If it checks out, encode it as an official request from Loan Requests.</p>
                    @endif
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small d-block">Loan Amount</label><p class="fw-medium mb-0">{{ peso($appt->requested_amount) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Purpose</label><p class="fw-medium mb-0">{{ $appt->loan_purpose }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Repayment Terms</label><p class="fw-medium mb-0">{{ $appt->repayment_terms_months }} months</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Collateral</label><p class="fw-medium mb-0">{{ $appt->collateral ?? '—' }}</p></div>
                        <div class="col-12">
                            <label class="text-muted small d-block">Collateral Proof</label>
                            <div class="mt-2">
                                @if($appt->documents_path)
                                <a href="{{ asset('storage/'.$appt->documents_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file me-1"></i> View Document
                                </a>
                                @else
                                <span class="text-muted">No document uploaded</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($appt->status === 'approved' && ! $appt->loan_request_id)
                        @if($appt->user->farmer)
                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-info btn-sm text-white" onclick="switchModal('viewModal{{ $appt->id }}', 'submitLoanRequestModal{{ $appt->id }}')">
                                <i class="fas fa-paper-plane me-1"></i> Submit for Loan Request
                            </button>
                        </div>
                        @else
                        <p class="text-danger small mb-0 mt-3"><i class="fas fa-exclamation-triangle me-1"></i> This account has no linked farmer membership record, so a loan request can't be created for it. Link the account to a farmer profile first.</p>
                        @endif
                    @endif
                    @endif
                </x-modal>

                @if($appt->status === 'approved' && $appt->requested_amount && ! $appt->loan_request_id && $appt->user->farmer)
                <!-- Submit for Loan Request Confirmation Modal -->
                <div class="modal fade" id="submitLoanRequestModal{{ $appt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Submit for Loan Request</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2">Submit <strong>{{ $farmerName }}</strong>'s loan pre-request to the Administrator for approval?</p>
                                <div class="row g-2 small text-muted">
                                    <div class="col-6">Amount: <span class="text-dark fw-medium">{{ peso($appt->requested_amount) }}</span></div>
                                    <div class="col-6">Purpose: <span class="text-dark fw-medium">{{ $appt->loan_purpose }}</span></div>
                                    <div class="col-6">Terms: <span class="text-dark fw-medium">{{ $appt->repayment_terms_months }} months</span></div>
                                    <div class="col-6">Collateral: <span class="text-dark fw-medium">{{ $appt->collateral ?? '—' }}</span></div>
                                </div>
                                <p class="text-muted small mb-0 mt-2">This creates the official loan request immediately — no further review step.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.loan-appointment.submit-loan-request', $appt) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info text-white">
                                        <i class="fas fa-paper-plane me-1"></i> Submit to Admin
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

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

                @if($appt->status !== 'cancelled' && ! $appt->loan_request_id)
                <!-- Reschedule Modal -->
                <div class="modal fade" id="rescheduleModal{{ $appt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-alt me-2"></i>Reschedule Appointment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.loan-appointment.reschedule', $appt) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="modal-body">
                                    <p class="text-muted small">Move <strong>{{ $farmerName }}</strong>'s appointment (currently {{ $appt->appointment_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appt->appointment_time)->format('g:i A') }}) to a new date/time.</p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input
                                            type="date" name="appointment_date" class="form-control"
                                            id="mgrRescheduleDate{{ $appt->id }}"
                                            min="{{ date('Y-m-d') }}"
                                            value="{{ $appt->appointment_date->format('Y-m-d') }}"
                                            data-current-time="{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}"
                                            data-exclude="{{ $appt->id }}"
                                            onchange="loanAppointmentRefreshSlots('mgrRescheduleDate{{ $appt->id }}', 'mgrRescheduleTime{{ $appt->id }}', 'mgrRescheduleSlotsHint{{ $appt->id }}')"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                                        <select name="appointment_time" id="mgrRescheduleTime{{ $appt->id }}" class="form-select" required>
                                            <option value="{{ \Carbon\Carbon::parse($appt->appointment_time)->format('H:i') }}">{{ \App\Models\LoanAppointment::slotLabel(\Carbon\Carbon::parse($appt->appointment_time)->format('H:i')) }}</option>
                                        </select>
                                        <p class="small text-muted mb-0 mt-1" id="mgrRescheduleSlotsHint{{ $appt->id }}">Up to 5 appointments allowed per day, one per 1-hour slot.</p>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Save New Schedule</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No loan appointment requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-info-banner variant="info" title="Loan Appointment Requests" class="mt-6">
    Farmers submit appointment requests to discuss loan applications. Approving confirms the slot; use Reschedule if the date/time no longer works. Once an appointment's loan pre-request has been submitted to the Administrator, it can no longer be rescheduled from here.
</x-info-banner>

<script>
    // Hides one modal and, once it's fully closed, opens another. Bootstrap
    // doesn't support two open modals cleanly, so the View Details modal
    // must finish closing before the submit-confirmation modal opens.
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

    // Refreshes a Time <select> with whichever of the 5 daily slots are
    // still open for the chosen date (excluding this appointment's own
    // current slot, so re-picking the same time is allowed). Mirrors the
    // farmer-side booking form so both enforce "5 appointments/day, one
    // per 1-hour slot" the same way — the server validates it again too.
    async function loanAppointmentRefreshSlots(dateInputId, timeSelectId, hintId) {
        var dateInput = document.getElementById(dateInputId);
        var timeSelect = document.getElementById(timeSelectId);
        var hint = document.getElementById(hintId);
        var currentValue = dateInput.dataset.currentTime || null;
        var excludeId = dateInput.dataset.exclude || '';

        if (!dateInput.value) {
            return;
        }

        timeSelect.disabled = true;

        try {
            var url = '{{ route('loan-appointment.available-slots') }}?date=' + encodeURIComponent(dateInput.value)
                + (excludeId ? '&exclude=' + encodeURIComponent(excludeId) : '');
            var response = await fetch(url);
            var data = await response.json();

            timeSelect.innerHTML = '';

            if (data.slots.length === 0) {
                timeSelect.innerHTML = '<option value="">No slots available</option>';
                hint.textContent = 'This date is fully booked (5 of 5 slots taken). Please choose another date.';
                hint.classList.add('text-danger');
                hint.classList.remove('text-muted');
                return;
            }

            data.slots.forEach(function (slot) {
                var option = document.createElement('option');
                option.value = slot.value;
                option.textContent = slot.label;
                if (slot.value === currentValue) {
                    option.selected = true;
                }
                timeSelect.appendChild(option);
            });

            hint.textContent = data.slots.length + ' of 5 slot(s) available for this date.';
            hint.classList.remove('text-danger');
            hint.classList.add('text-muted');
        } finally {
            timeSelect.disabled = false;
        }
    }

    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target.id.startsWith('rescheduleModal')) {
            var dateInput = event.target.querySelector('input[name="appointment_date"]');
            if (dateInput) {
                loanAppointmentRefreshSlots(dateInput.id, dateInput.id.replace('mgrRescheduleDate', 'mgrRescheduleTime'), dateInput.id.replace('mgrRescheduleDate', 'mgrRescheduleSlotsHint'));
            }
        }
    });
</script>
@endsection
