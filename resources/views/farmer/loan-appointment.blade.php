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
        <table class="table table-hover mb-0 table-mobile-cards">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Purpose</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Loan Requested</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium" data-label="Date">{{ $appointment->appointment_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Time">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Purpose">{{ $appointment->purpose }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Loan Requested">
                        @if($appointment->requested_amount)
                        {{ peso($appointment->requested_amount) }}
                        <div class="small text-muted">{{ $appointment->loan_purpose }} &bull; {{ $appointment->repayment_terms_months }} mo.</div>
                        @else
                        —
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4" data-label="Status"><x-status-badge :status="ucfirst($appointment->status)" /></td>
                    <td class="px-4 px-md-6 py-4" data-label="Actions">
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
                            <form action="{{ route('farmer.loan-appointment.update', $appointment->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Date</label>
                                        <input
                                            type="date" name="appointment_date" class="form-control"
                                            id="rescheduleDate{{ $appointment->id }}"
                                            min="{{ date('Y-m-d') }}"
                                            value="{{ $appointment->appointment_date->format('Y-m-d') }}"
                                            data-current-time="{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}"
                                            data-exclude="{{ $appointment->id }}"
                                            onchange="loanAppointmentRefreshSlots('rescheduleDate{{ $appointment->id }}', 'rescheduleTime{{ $appointment->id }}', 'rescheduleSlotsHint{{ $appointment->id }}')"
                                            required
                                        >
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                                        <select name="appointment_time" id="rescheduleTime{{ $appointment->id }}" class="form-select" required>
                                            <option value="{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}">{{ \App\Models\LoanAppointment::slotLabel(\Carbon\Carbon::parse($appointment->appointment_time)->format('H:i')) }}</option>
                                        </select>
                                        <p class="small text-muted mb-0 mt-1" id="rescheduleSlotsHint{{ $appointment->id }}">Up to 5 appointments allowed per day, one per 1-hour slot.</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Appointment Notes</label>
                                        <input type="text" name="purpose" class="form-control" value="{{ $appointment->purpose }}" required>
                                    </div>
                                    <hr>
                                    <h6 class="fw-semibold mb-3">Loan Details</h6>
                                    @if($loanEligibility)
                                    <div class="alert {{ $loanEligibility['eligible'] ? 'alert-info' : 'alert-warning' }} py-2 px-3 small mb-3">
                                        @if($loanEligibility['eligible'])
                                        Your CBU balance is {{ peso($loanEligibility['cbu_balance']) }} — maximum loan you can request: <strong>{{ peso($loanEligibility['max_loanable']) }}</strong>.
                                        @else
                                        Your CBU balance is {{ peso($loanEligibility['cbu_balance']) }}. A minimum of {{ peso($loanEligibility['min_cbu']) }} is required before you can request a loan.
                                        @endif
                                    </div>
                                    @endif
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Loan Amount <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="1" @if($loanEligibility) max="{{ $loanEligibility['max_loanable'] }}" @endif name="requested_amount" class="form-control" value="{{ $appointment->requested_amount }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                                            <select name="loan_purpose" class="form-select" required>
                                                <option value="">Select Purpose</option>
                                                @foreach($loanPurposes as $purpose)
                                                <option value="{{ $purpose }}" {{ $appointment->loan_purpose === $purpose ? 'selected' : '' }}>{{ $purpose }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="form-label fw-semibold">Repayment Terms <span class="text-danger">*</span></label>
                                            <select name="repayment_terms_months" class="form-select" required>
                                                <option value="">Select Terms</option>
                                                @foreach($loanTerms as $term)
                                                <option value="{{ $term }}" {{ (int) $appointment->repayment_terms_months === $term ? 'selected' : '' }}>{{ $term }} months</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Collateral</label>
                                            <input type="text" name="collateral" class="form-control" value="{{ $appointment->collateral }}" placeholder="Describe collateral">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">Collateral Proof</label>
                                        <x-collateral-proof-input
                                            id="documentsEdit{{ $appointment->id }}"
                                            :existing-label="$appointment->documents_path ? 'Current file on record — choose a new one only to replace it.' : null"
                                            :existing-url="$appointment->documents_path ? asset('storage/'.$appointment->documents_path) : null"
                                            parent-modal-id="editModal{{ $appointment->id }}"
                                        />
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
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No loan appointments yet.</td>
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
            <form action="{{ route('farmer.loan-appointment.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input
                            type="date" name="appointment_date" class="form-control"
                            id="createAppointmentDate"
                            min="{{ date('Y-m-d') }}"
                            onchange="loanAppointmentRefreshSlots('createAppointmentDate', 'createAppointmentTime', 'createSlotsHint')"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                        <select name="appointment_time" id="createAppointmentTime" class="form-select" required disabled>
                            <option value="">Select a date first</option>
                        </select>
                        <p class="small text-muted mb-0 mt-1" id="createSlotsHint">Up to 5 appointments allowed per day, one per 1-hour slot.</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Appointment Notes <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" class="form-control" placeholder="e.g. Discuss loan application requirements" required>
                    </div>
                    <hr>
                    <h6 class="fw-semibold mb-3">Loan Details</h6>
                    <p class="text-muted small mb-3">Tell us what you're looking for so the Manager can review it before your appointment. This is a pre-request — your Manager will confirm it and encode the official loan request when you meet.</p>
                    @if($loanEligibility)
                    <div class="alert {{ $loanEligibility['eligible'] ? 'alert-info' : 'alert-warning' }} py-2 px-3 small mb-3">
                        @if($loanEligibility['eligible'])
                        Your CBU balance is {{ peso($loanEligibility['cbu_balance']) }} — maximum loan you can request: <strong>{{ peso($loanEligibility['max_loanable']) }}</strong>.
                        @else
                        Your CBU balance is {{ peso($loanEligibility['cbu_balance']) }}. A minimum of {{ peso($loanEligibility['min_cbu']) }} is required before you can request a loan.
                        @endif
                    </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-semibold">Loan Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="1" @if($loanEligibility) max="{{ $loanEligibility['max_loanable'] }}" @endif name="requested_amount" class="form-control" placeholder="&#8369;0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purpose <span class="text-danger">*</span></label>
                            <select name="loan_purpose" class="form-select" required>
                                <option value="">Select Purpose</option>
                                @foreach($loanPurposes as $purpose)
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
                                @foreach($loanTerms as $term)
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
                        <label class="form-label fw-semibold">Collateral Proof</label>
                        <x-collateral-proof-input id="documentsCreate" parent-modal-id="createModal" />
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

{{-- Camera capture modals from x-collateral-proof-input, pushed here so
     they're siblings of the other modals rather than nested descendants. --}}
@stack('modals')

<script>
    // Refreshes a Time <select> with whichever of the 5 daily slots are
    // still open for the chosen date (excluding this appointment's own
    // current slot on reschedule, so re-picking the same time is allowed).
    // Keeps "5 appointments/day, one per 1-hour slot" enforced client-side
    // for a good UX — the server validates the same rule again on submit.
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

    // Pre-load the Reschedule modal's slots (for its already-filled date)
    // the moment it opens, so the dropdown isn't just the one hardcoded
    // "current" option until the farmer happens to touch the date field.
    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target.id.startsWith('editModal')) {
            var dateInput = event.target.querySelector('input[name="appointment_date"]');
            if (dateInput) {
                loanAppointmentRefreshSlots(dateInput.id, dateInput.id.replace('rescheduleDate', 'rescheduleTime'), dateInput.id.replace('rescheduleDate', 'rescheduleSlotsHint'));
            }
        }
    });
</script>
@endsection
