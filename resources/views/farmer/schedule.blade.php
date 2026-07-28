@extends('farmer.layout')

@section('title', 'Request Schedule')
@section('header', 'Machinery Schedule Request')

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

<!-- Machinery Availability -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Machinery Availability</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($machinery as $machine)
        <div class="p-4 rounded-lg border border-gray-200 d-flex align-items-center justify-content-between">
            <div>
                <p class="fw-semibold mb-1">{{ $machine['name'] }}</p>
                <x-status-badge :status="$machine['status']" />
            </div>
            <i class="fas fa-tractor text-muted" style="font-size: 1.5rem;"></i>
        </div>
        @endforeach
    </div>
</div>

@php
    $earliestAllowedDate = \App\Models\ScheduleRequest::earliestAllowedDate();
    $hasOldRequestInput = old('machinery') || old('land_size') || old('scheduled_date') || old('start_time') || old('end_time') || old('location');
@endphp

<!-- Schedule Availability Calendar -->
<div class="section-card mb-6">
    <div class="table-toolbar d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-0">Schedule Availability</h3>
            <p class="text-sm text-muted mb-0">Click an open day to request a schedule for it.</p>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <form method="GET" action="{{ route('farmer.schedule') }}" class="d-flex align-items-center gap-3 flex-wrap">
                <select name="month" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    @foreach($monthOptions as $option)
                    <option value="{{ $option->format('Y-m') }}" {{ $option->format('Y-m') == $selectedMonth->format('Y-m') ? 'selected' : '' }}>{{ $option->format('F Y') }}</option>
                    @endforeach
                </select>
                <select name="calendar_machinery" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Machines</option>
                    @foreach($machineryList as $machine)
                    <option value="{{ $machine }}" {{ $machineryFilter == $machine ? 'selected' : '' }}>{{ $machine }}</option>
                    @endforeach
                </select>
            </form>
            <button type="button" class="btn btn-primary" onclick="openScheduleRequestModal()">
                <i class="fas fa-plus me-1"></i> New Schedule Request
            </button>
        </div>
    </div>

    <div class="p-4 p-md-6">
        <x-schedule-calendar :calendar-days="$calendarDays" :first-weekday="$firstWeekday" :days-in-month="$daysInMonth"
            :show-names="false" :show-open-badge="true" min-height="100px"
            :month="$selectedMonth" :min-date="$earliestAllowedDate" :clickable="true" />
        <div class="d-flex align-items-center gap-4 mt-3 small text-muted">
            <span class="d-flex align-items-center gap-1"><span class="rounded-circle bg-success" style="width:0.6rem;height:0.6rem;display:inline-block;"></span> Open &mdash; click to request</span>
            <span class="d-flex align-items-center gap-1"><span class="rounded-circle bg-danger" style="width:0.6rem;height:0.6rem;display:inline-block;"></span> Booked (approved)</span>
            <span class="d-flex align-items-center gap-1"><span class="rounded-circle bg-secondary" style="width:0.6rem;height:0.6rem;display:inline-block;"></span> Too soon (min {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }} days lead time)</span>
        </div>
    </div>
</div>

<!-- Request Form Modal -->
<div class="modal fade" id="scheduleRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-tractor me-2"></i>Request a Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('farmer.schedule.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3 d-none" id="scheduleRequestSelectedDateHint"></p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Machinery Type <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                @foreach($machinery as $machine)
                                <div class="form-check border rounded-lg p-2 {{ $machine['status'] == 'Unavailable' ? 'opacity-50' : '' }}">
                                    <input class="form-check-input" type="radio" name="machinery" id="machinery{{ $loop->index }}"
                                        value="{{ $machine['name'] }}" {{ $machine['status'] == 'Unavailable' ? 'disabled' : '' }}
                                        {{ old('machinery') == $machine['name'] ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="machinery{{ $loop->index }}">
                                        {{ $machine['name'] }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Land Size (hectares) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" min="0.1" name="land_size" class="form-control" value="{{ old('land_size') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Date <span class="text-danger">*</span></label>
                            <input type="date" id="scheduleRequestDateInput" name="scheduled_date" class="form-control" min="{{ $earliestAllowedDate->format('Y-m-d') }}" value="{{ old('scheduled_date') }}" required>
                            <small class="text-muted">Must be at least {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }} days from today.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Farm Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. Brgy. San Jose" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openScheduleRequestModal(date) {
        var dateInput = document.getElementById('scheduleRequestDateInput');
        var hint = document.getElementById('scheduleRequestSelectedDateHint');

        if (date) {
            dateInput.value = date;
            dateInput.readOnly = true;
            hint.textContent = 'Selected date: ' + new Date(date + 'T00:00:00').toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            hint.classList.remove('d-none');
        } else {
            dateInput.value = '';
            dateInput.readOnly = false;
            hint.classList.add('d-none');
        }

        new bootstrap.Modal(document.getElementById('scheduleRequestModal')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.calendar-cell-clickable').forEach(function (cell) {
            cell.addEventListener('click', function () {
                openScheduleRequestModal(cell.dataset.date);
            });
            cell.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openScheduleRequestModal(cell.dataset.date);
                }
            });
        });

        @if($hasOldRequestInput)
        openScheduleRequestModal(@json(old('scheduled_date')));
        @endif
    });
</script>

<!-- My Schedule Requests -->
<div class="section-card">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">My Schedule Requests</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machinery</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Size</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date & Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Location</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium">
                        {{ $req->machinery }}
                        @if($req->is_reschedule)<span class="badge bg-info-subtle text-info border border-info-subtle ms-1">Reschedule</span>@endif
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->land_size }} ha</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->scheduled_date->format('M d, Y') }}, {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->location }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        @if($req->status === 'approved')
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $req->id }}">
                            <i class="fas fa-calendar-alt me-1"></i>Reschedule
                        </button>
                        @elseif($req->status === 'denied' && $req->denial_reason)
                        <span class="text-danger small" title="{{ $req->denial_reason }}"><i class="fas fa-info-circle me-1"></i>Reason</span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>

                @if($req->status === 'approved')
                <div class="modal fade" id="rescheduleModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-alt me-2"></i>Request Reschedule</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('farmer.schedule.reschedule', $req) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p class="text-muted small">Current schedule: {{ $req->scheduled_date->format('M d, Y') }}, {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}</p>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">New Date <span class="text-danger">*</span></label>
                                        <input type="date" name="scheduled_date" class="form-control" min="{{ \App\Models\ScheduleRequest::earliestAllowedDate()->format('Y-m-d') }}" required>
                                        <small class="text-muted">Must be at least {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }} days from today.</small>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                                            <input type="time" name="start_time" class="form-control" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                                            <input type="time" name="end_time" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="mb-0 mt-3">
                                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                                        <input type="text" name="location" class="form-control" value="{{ $req->location }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Submit Reschedule Request</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <tr>
                    <td colspan="6" class="px-4 px-md-6 py-6 text-center text-muted">No schedule requests yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
