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

@php
    $earliestAllowedDate = \App\Models\ScheduleRequest::earliestAllowedDate();
    $hasOldRequestInput = old('machines') || old('location');
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
            :month="$selectedMonth" :min-date="$earliestAllowedDate" :clickable="true" :highlight-own="true" />
        <div class="d-flex align-items-center gap-4 mt-3 small text-muted flex-wrap">
            <span class="d-flex align-items-center gap-1"><span class="rounded-circle bg-success" style="width:0.6rem;height:0.6rem;display:inline-block;"></span> Open &mdash; click to request</span>
            <span class="d-flex align-items-center gap-1"><span class="rounded-circle" style="width:0.6rem;height:0.6rem;display:inline-block;background-color: var(--brand-success-hover);"></span> Your schedule</span>
            <span class="d-flex align-items-center gap-1"><span class="rounded-circle bg-danger" style="width:0.6rem;height:0.6rem;display:inline-block;"></span> Booked by another farmer</span>
            <span class="d-flex align-items-center gap-1"><span class="rounded-circle bg-secondary" style="width:0.6rem;height:0.6rem;display:inline-block;"></span> Too soon (min {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }} days lead time)</span>
        </div>
    </div>
</div>

<!-- Request Form Modal -->
<div class="modal fade" id="scheduleRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-tractor me-2"></i>Request a Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('farmer.schedule.store') }}" method="POST" class="modal-form-flex">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3 d-none" id="scheduleRequestSelectedDateHint"></p>
                    <div id="scheduleMachineRows"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="addMachineRowBtn">
                        <i class="fas fa-plus me-1"></i>Add Another Machine
                    </button>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Farm Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="e.g. Brgy. San Jose" required>
                            <small class="text-muted">Used for every machine in this request.</small>
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

<!-- One machine's fields; cloned by JS into #scheduleMachineRows, with __IDX__
     replaced by that clone's array index (machines[__IDX__][...]). -->
<template id="machineRowTemplate">
    <div class="schedule-machine-row border rounded-lg p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-semibold">Machine <span class="machine-row-number">#1</span></h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-machine-row-btn d-none" title="Remove this machine">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Choose a machine <span class="text-danger">*</span></label>
                <div class="d-flex flex-column gap-2">
                    @foreach($machinery as $machine)
                    @php $isUnavailable = $machine['status'] == 'Unavailable'; @endphp
                    <input type="radio" class="btn-check machine-option-input" name="machines[__IDX__][machinery]" id="machines___IDX___machinery{{ $loop->index }}"
                        value="{{ $machine['name'] }}" data-quantity="{{ $machine['quantity'] }}"
                        data-maintenance="{{ $isUnavailable ? '1' : '0' }}" {{ $isUnavailable ? 'disabled' : '' }}
                        autocomplete="off" required>
                    <label class="machine-option" for="machines___IDX___machinery{{ $loop->index }}">
                        <span style="min-width: 0;">
                            <span class="machine-option-name">{{ $machine['name'] }}</span>
                            <span class="machine-option-detail">{{ $isUnavailable ? 'Due for maintenance' : 'Pick a date to check availability' }}</span>
                        </span>
                        <span class="badge machine-option-badge {{ $isUnavailable ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'd-none' }}">{{ $isUnavailable ? 'Unavailable' : '' }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Units Needed</label>
                <select name="machines[__IDX__][units_requested]" class="form-select machine-units-select">
                    <option value="1">1 unit</option>
                </select>
                <small class="text-muted">Request more than one to work in parallel and finish faster.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Land Size (hectares) <span class="text-danger">*</span></label>
                <input type="number" step="0.1" min="0.1" name="machines[__IDX__][land_size]" class="form-control machine-land-size" required>
                <small class="text-danger d-none machine-capacity-hint"></small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Crop to be Harvested <span class="text-danger">*</span></label>
                <select name="machines[__IDX__][crop_id]" class="form-select" required>
                    <option value="">Select crop...</option>
                    @foreach($crops as $crop)
                    <option value="{{ $crop->id }}">{{ $crop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Preferred Date <span class="text-danger">*</span></label>
                <input type="date" name="machines[__IDX__][scheduled_date]" class="form-control machine-date" min="{{ $earliestAllowedDate->format('Y-m-d') }}" required>
                <small class="text-muted">Must be at least {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }} days from today.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                <input type="time" name="machines[__IDX__][start_time]" class="form-control machine-start-time" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                <input type="time" name="machines[__IDX__][end_time]" class="form-control machine-end-time" required>
            </div>
            <div class="col-12">
                <div class="schedule-finish-note">
                    <i class="far fa-clock mt-1"></i>
                    <span>
                        <span class="machine-finish-note">End Time is estimated</span>
                        ({{ \App\Models\ScheduleRequest::HOURS_PER_HECTARE }} hours per hectare per unit, you can adjust it).
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    var SCHEDULE_HOURS_PER_HECTARE = {{ \App\Models\ScheduleRequest::HOURS_PER_HECTARE }};
    var SCHEDULE_AVAILABILITY_URL = @json(route('farmer.schedule.availability'));
    var scheduleRowTemplate = document.getElementById('machineRowTemplate');
    var scheduleRowsContainer = document.getElementById('scheduleMachineRows');
    var scheduleNextRowIndex = 0;
    var scheduleOldMachines = @json(old('machines', []));

    function formatHectares(value) {
        return (Math.round(value * 100) / 100).toString();
    }

    function renumberMachineRows() {
        var rows = scheduleRowsContainer.querySelectorAll('.schedule-machine-row');
        rows.forEach(function (row, i) {
            row.querySelector('.machine-row-number').textContent = '#' + (i + 1);
            row.querySelector('.remove-machine-row-btn').classList.toggle('d-none', rows.length <= 1);
        });
    }

    // Adds one machine row (cloned from #machineRowTemplate, __IDX__ replaced
    // with a fresh array index) and wires up its own independent copy of the
    // end-time estimate, units picker, and live availability lookup — each
    // row can target a different machine on a different date.
    function addMachineRow(oldValues) {
        var index = scheduleNextRowIndex++;
        var html = scheduleRowTemplate.innerHTML.split('__IDX__').join(index);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;
        scheduleRowsContainer.appendChild(row);

        initMachineRow(row, oldValues || {});
        renumberMachineRows();

        return row;
    }

    function initMachineRow(row, oldValues) {
        var removeBtn = row.querySelector('.remove-machine-row-btn');
        var landSizeInput = row.querySelector('.machine-land-size');
        var dateInput = row.querySelector('.machine-date');
        var startTimeInput = row.querySelector('.machine-start-time');
        var endTimeInput = row.querySelector('.machine-end-time');
        var unitsSelect = row.querySelector('.machine-units-select');
        var finishNote = row.querySelector('.machine-finish-note');
        var capacityHint = row.querySelector('.machine-capacity-hint');
        var cropSelect = row.querySelector('select[name$="[crop_id]"]');

        var availability = {};
        var availabilityTimer = null;
        var availabilityRequestId = 0;

        function checkedMachineInput() {
            return row.querySelector('.machine-option-input:checked');
        }

        function estimateEndTime() {
            var landSize = parseFloat(landSizeInput.value);
            var startTime = startTimeInput.value;
            var units = parseInt(unitsSelect.value, 10) || 1;

            if (!landSize || landSize <= 0 || !startTime) {
                return;
            }

            var totalMinutes = Math.round((landSize / units) * SCHEDULE_HOURS_PER_HECTARE * 60 / 5) * 5;
            var parts = startTime.split(':');
            var end = new Date(0, 0, 0, parseInt(parts[0], 10), parseInt(parts[1], 10));
            end.setMinutes(end.getMinutes() + totalMinutes);

            endTimeInput.value = String(end.getHours()).padStart(2, '0') + ':' + String(end.getMinutes()).padStart(2, '0');
            finishNote.innerHTML = 'Estimated finish: <strong>' + end.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) + '</strong>';
            refreshAvailability();
        }

        function refreshUnits() {
            var checked = checkedMachineInput();
            var quantity = checked ? parseInt(checked.dataset.quantity, 10) || 1 : 1;
            var previousValue = parseInt(unitsSelect.value, 10) || 1;
            var desired = unitsSelect.dataset.initialized ? previousValue : (parseInt(oldValues.units_requested, 10) || 1);

            unitsSelect.innerHTML = '';
            for (var i = 1; i <= quantity; i++) {
                var option = document.createElement('option');
                option.value = i;
                option.textContent = i === 1 ? '1 unit' : (i + ' units (parallel)');
                unitsSelect.appendChild(option);
            }
            unitsSelect.value = Math.min(desired, quantity);
            unitsSelect.dataset.initialized = '1';

            estimateEndTime();
        }

        function refreshAvailability() {
            clearTimeout(availabilityTimer);
            availabilityTimer = setTimeout(loadAvailability, 250);
        }

        function loadAvailability() {
            var date = dateInput.value;
            if (!date) {
                availability = {};
                renderAvailability(null);
                return;
            }

            var params = new URLSearchParams({ date: date });
            var start = startTimeInput.value;
            var end = endTimeInput.value;
            if (start && end && end > start) {
                params.set('start_time', start);
                params.set('end_time', end);
            }

            var requestId = ++availabilityRequestId;
            fetch(SCHEDULE_AVAILABILITY_URL + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (data) {
                    if (!data || requestId !== availabilityRequestId) {
                        return;
                    }
                    availability = {};
                    data.machines.forEach(function (m) { availability[m.name] = m; });
                    renderAvailability(date);
                })
                .catch(function () { /* keep the form usable; the server still validates on submit */ });
        }

        function renderAvailability(date) {
            var dayLabel = date ? new Date(date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '';

            row.querySelectorAll('.machine-option-input').forEach(function (input) {
                var label = row.querySelector('label[for="' + input.id + '"]');
                var detail = label.querySelector('.machine-option-detail');
                var badge = label.querySelector('.machine-option-badge');
                var info = availability[input.value];

                if (input.dataset.maintenance === '1') {
                    return;
                }

                if (!date || !info) {
                    input.disabled = false;
                    detail.textContent = 'Pick a date to check availability';
                    badge.className = 'badge machine-option-badge d-none';
                    return;
                }

                if (info.status === 'booked') {
                    input.disabled = true;
                    input.checked = false;
                    detail.textContent = 'Fully booked on ' + dayLabel;
                    badge.className = 'badge machine-option-badge bg-warning-subtle text-warning border border-warning-subtle';
                    badge.textContent = 'Booked';
                } else {
                    input.disabled = false;
                    detail.textContent = info.units_free !== null
                        ? info.units_free + ' of ' + input.dataset.quantity + ' ' + (info.units_free === 1 ? 'unit' : 'units') + ' free · ' + formatHectares(info.hectares_left) + ' ha left on ' + dayLabel
                        : formatHectares(info.hectares_left) + ' ha left on ' + dayLabel;
                    badge.className = 'badge machine-option-badge bg-success-subtle text-success border border-success-subtle';
                    badge.textContent = 'Available';
                }
            });

            updateCapacityHint(dayLabel);
        }

        function updateCapacityHint(dayLabel) {
            var checked = checkedMachineInput();
            var landSize = parseFloat(landSizeInput.value);
            var info = checked ? availability[checked.value] : null;

            if (info && info.status === 'available' && landSize > info.hectares_left) {
                capacityHint.textContent = 'Only ' + formatHectares(info.hectares_left) + ' ha left on ' + dayLabel + ' for ' + checked.value + '.';
                capacityHint.classList.remove('d-none');
            } else {
                capacityHint.classList.add('d-none');
            }
        }

        removeBtn.addEventListener('click', function () {
            row.remove();
            renumberMachineRows();
        });

        landSizeInput.addEventListener('input', estimateEndTime);
        startTimeInput.addEventListener('input', estimateEndTime);
        unitsSelect.addEventListener('change', estimateEndTime);
        row.querySelectorAll('.machine-option-input').forEach(function (radio) {
            radio.addEventListener('change', refreshUnits);
            radio.addEventListener('change', refreshAvailability);
        });
        dateInput.addEventListener('change', refreshAvailability);
        endTimeInput.addEventListener('change', refreshAvailability);
        landSizeInput.addEventListener('input', refreshAvailability);

        // Repopulate this row from old() input, e.g. after a validation error.
        if (oldValues.machinery) {
            var radio = row.querySelector('.machine-option-input[value="' + CSS.escape(oldValues.machinery) + '"]');
            if (radio) { radio.checked = true; }
        }
        if (oldValues.land_size) { landSizeInput.value = oldValues.land_size; }
        if (oldValues.crop_id && cropSelect) { cropSelect.value = oldValues.crop_id; }
        if (oldValues.scheduled_date) { dateInput.value = oldValues.scheduled_date; }
        if (oldValues.start_time) { startTimeInput.value = oldValues.start_time; }
        if (oldValues.end_time) { endTimeInput.value = oldValues.end_time; }

        refreshUnits();
        refreshAvailability();
    }

    function openScheduleRequestModal(date) {
        scheduleRowsContainer.innerHTML = '';
        scheduleNextRowIndex = 0;

        if (scheduleOldMachines && scheduleOldMachines.length) {
            scheduleOldMachines.forEach(function (oldRow) {
                addMachineRow(oldRow);
            });
            scheduleOldMachines = null; // only replay old input once
        } else {
            addMachineRow({});
        }

        var hint = document.getElementById('scheduleRequestSelectedDateHint');
        var firstDateInput = scheduleRowsContainer.querySelector('.machine-date');

        if (date) {
            firstDateInput.value = date;
            firstDateInput.readOnly = true;
            hint.textContent = 'Selected date: ' + new Date(date + 'T00:00:00').toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            hint.classList.remove('d-none');
            firstDateInput.dispatchEvent(new Event('change'));
        } else {
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

        document.getElementById('addMachineRowBtn').addEventListener('click', function () {
            addMachineRow({});
        });

        @if($hasOldRequestInput)
        openScheduleRequestModal(null);
        @endif
    });
</script>

<!-- My Schedule Requests -->
<div class="section-card">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">My Schedule Requests</h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-mobile-cards">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machinery</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Land Size</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Crop</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date & Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Location</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="px-4 px-md-6 py-4 text-dark fw-medium" data-label="Machinery">
                        {{ $req->units_requested > 1 ? $req->units_requested.'× ' : '' }}{{ $req->machinery }}
                        @if($req->is_reschedule)<span class="badge bg-info-subtle text-info border border-info-subtle ms-1">Reschedule</span>@endif
                        @if($req->batch_id)<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1" title="Submitted together with {{ $req->batchSiblings->count() }} other machine(s)"><i class="fas fa-link"></i></span>@endif
                    </td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Land Size">{{ $req->land_size }} ha</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Crop">{{ $req->crop->name ?? '—' }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Date &amp; Time">{{ $req->scheduled_date->format('M d, Y') }}, {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted" data-label="Location">{{ $req->location }}</td>
                    <td class="px-4 px-md-6 py-4" data-label="Status"><x-status-badge :status="ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-6 py-4" data-label="Actions">
                        @if($req->status === 'approved')
                        <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $req->id }}">Reschedule</button>
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
                            <form action="{{ route('farmer.schedule.reschedule', $req) }}" method="POST" class="modal-form-flex">
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
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">No schedule requests yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination-footer :paginator="$requests" />
</div>
@endsection
