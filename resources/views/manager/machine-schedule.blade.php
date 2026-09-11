@extends('manager.layout')

@section('title', 'Machine Scheduling')
@section('header', 'Machine Rental Scheduling')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    {{ session('error') }}
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

<div class="section-card mb-6">
    <!-- Header Actions -->
    <div class="table-toolbar d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <form method="GET" action="{{ route('manager.machine-schedule') }}" class="d-flex align-items-center gap-3 flex-wrap">
            <select name="month" class="form-select" style="width: auto;" onchange="this.form.submit()">
                @foreach($monthOptions as $option)
                <option value="{{ $option->format('Y-m') }}" {{ $option->format('Y-m') == $selectedMonth->format('Y-m') ? 'selected' : '' }}>{{ $option->format('F Y') }}</option>
                @endforeach
            </select>
            <select name="machinery" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Machines</option>
                @foreach($machineryList as $machine)
                <option value="{{ $machine }}" {{ request('machinery') == $machine ? 'selected' : '' }}>{{ $machine }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denied</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            @if($showArchived)
            <input type="hidden" name="archived" value="1">
            @endif
            @if(request()->filled('status'))
            <a href="{{ route('manager.machine-schedule', request()->except('status')) }}" class="btn btn-link btn-sm">Clear Status</a>
            @endif
        </form>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" id="moveOneDayBtn" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1 text-nowrap" title="Pick specific date(s) to move">
                <i class="fas fa-calendar-day"></i><span>Move One Day</span>
            </button>
            <div class="btn-group btn-group-sm" role="group" aria-label="Move every schedule">
                <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#shiftDayBackwardModal" title="Move every schedule back 1 day">
                    <i class="fas fa-backward"></i><span>−1 Day</span>
                </button>
                <button type="button" class="btn btn-outline-warning d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#shiftDayModal" title="Move every schedule forward 1 day">
                    <i class="fas fa-forward"></i><span>+1 Day</span>
                </button>
            </div>
            <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus"></i><span>Add Schedule</span>
            </button>
        </div>
    </div>

    <!-- Move One Day: selection toolbar (shown while picking dates) -->
    <div id="moveOneDayToolbar" class="d-none align-items-center justify-content-between gap-3 px-4 px-md-6 py-3 border-top border-bottom flex-wrap" style="background-color: var(--brand-warning-light);">
        <p class="mb-0 small fw-medium"><i class="fas fa-info-circle me-1"></i>Check the days you want to move, then confirm. <span id="moveOneDayCount">0 selected</span></p>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 small fw-medium">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="radio" name="moveOneDayDirection" id="moveOneDayForward" value="forward" checked>
                    <label class="form-check-label" for="moveOneDayForward">Forward</label>
                </div>
                <div class="form-check mb-0">
                    <input class="form-check-input" type="radio" name="moveOneDayDirection" id="moveOneDayBackward" value="backward">
                    <label class="form-check-label" for="moveOneDayBackward">Backward</label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="moveOneDayCancel" class="btn btn-sm btn-outline-secondary">Cancel</button>
                <button type="button" id="moveOneDayConfirm" class="btn btn-sm btn-warning" disabled>Move Selected Day(s)</button>
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="p-4 p-md-6">
        <x-schedule-calendar :calendar-days="$calendarDays" :first-weekday="$firstWeekday" :days-in-month="$daysInMonth"
            :show-names="true" min-height="120px" :month="$selectedMonth" selection-mode="true" />
    </div>
</div>

<!-- Schedule List -->
<div class="section-card">
    <div class="table-toolbar d-flex align-items-center justify-content-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">{{ $showArchived ? 'Archived Schedules' : 'All Schedules' }}</h3>
        @if($showArchived)
        <a href="{{ route('manager.machine-schedule', request()->except('archived')) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Active
        </a>
        @else
        <a href="{{ route('manager.machine-schedule', array_merge(request()->query(), ['archived' => 1])) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-box-archive me-1"></i>View Archived
        </a>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Schedule ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date & Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Member Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $req->display_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->units_requested > 1 ? $req->units_requested.'× ' : '' }}{{ $req->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->scheduled_date->format('M d, Y') }} - {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$req->member_type === 'member' ? 'Member' : 'Non-member'" /></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}" />
                            @if($showArchived)
                            <x-icon-button icon="fa-box-open" color="success" title="Restore" data-bs-toggle="modal" data-bs-target="#unarchiveModal{{ $req->id }}" />
                            @else
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $req->id }}" />
                            @if($req->status === 'approved')
                            <x-icon-button icon="fa-clipboard-check" color="success" title="Complete & Record Yield" data-bs-toggle="modal" data-bs-target="#completeModal{{ $req->id }}" />
                            @endif
                            <x-icon-button icon="fa-archive" color="danger" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $req->id }}" />
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <x-modal id="viewModal{{ $req->id }}" title="Schedule Details">
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small d-block">Schedule ID</label><p class="fw-medium mb-0">SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $req->display_name }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Machinery</label><p class="fw-medium mb-0">{{ $req->machinery }}{{ $req->units_requested > 1 ? ' ('.$req->units_requested.' units)' : '' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Member Status</label><x-status-badge :status="$req->member_type === 'member' ? 'Member' : 'Non-member'" /></div>
                        @if($req->member_type === 'non-member' && $req->contact_number)
                        <div class="col-6"><label class="text-muted small d-block">Contact Number</label><p class="fw-medium mb-0">{{ $req->contact_number }}</p></div>
                        @endif
                        <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $req->scheduled_date->format('M d, Y') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Time</label><p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Location</label><p class="fw-medium mb-0">{{ $req->location }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Land Area</label><p class="fw-medium mb-0">{{ $req->land_size }} hectares</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Crop to be Harvested</label><p class="fw-medium mb-0">{{ $req->crop->name ?? '—' }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Status</label><x-status-badge :status="ucfirst($req->status)" /></div>
                        @if($req->is_reschedule && $req->originalSchedule)
                        <div class="col-12"><label class="text-muted small d-block">Original Schedule</label><p class="fw-medium mb-0">{{ $req->originalSchedule->scheduled_date->format('M d, Y') }}, {{ \Carbon\Carbon::parse($req->originalSchedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->originalSchedule->end_time)->format('g:i A') }}</p></div>
                        @endif
                        @if($req->status === 'completed')
                        <div class="col-6"><label class="text-muted small d-block">Harvest Yield</label><p class="fw-medium mb-0">{{ $req->harvest_yield }}</p></div>
                        @endif
                        @if($req->rescheduleRequests->isNotEmpty())
                        <div class="col-12"><label class="text-muted small d-block">Reschedule Requests</label>
                            <ul class="mb-0 ps-3">
                                @foreach($req->rescheduleRequests as $r)
                                <li class="small">{{ $r->scheduled_date->format('M d, Y') }} &mdash; <x-status-badge :status="ucfirst($r->status)" /></li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </x-modal>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit me-2"></i>Edit Schedule</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.machine-schedule.update', $req) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    @include('manager.partials.schedule-form-fields', ['prefix' => 'edit'.$req->id, 'schedule' => $req])
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Complete / Harvest Yield Modal -->
                <div class="modal fade" id="completeModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check me-2"></i>Complete Schedule</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.machine-schedule.complete', $req) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="modal-body">
                                    <p>Confirm that this schedule for <strong>{{ $req->display_name }}</strong> is finished and record the total harvest yield. Once marked complete, it can't be reopened.</p>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Harvest Yield (sacks/tons) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" name="harvest_yield" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Confirm Complete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Archive Modal -->
                <div class="modal fade" id="archiveModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-archive me-2"></i>Archive Schedule</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Archive SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }} for {{ $req->display_name }}? It will be removed from the active list but kept for reporting and auditing.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.machine-schedule.archive', $req) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-danger">Archive</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unarchive (Restore) Modal -->
                <div class="modal fade" id="unarchiveModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2"></i>Restore Schedule</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-0">Restore SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }} for {{ $req->display_name }}? It will reappear on the active calendar and schedule list.</p>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('manager.machine-schedule.unarchive', $req) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">Restore</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">{{ $showArchived ? 'No archived schedules.' : 'No schedules found for this month.' }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Move Schedule +1 Day Confirmation Modal -->
<div class="modal fade" id="shiftDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-forward me-2"></i>Move Schedule +1 Day</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.shift-day') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>This will move <strong>every pending/approved schedule</strong> — every scheduled date, including any already overdue — forward by 1 day, and automatically notify each affected farmer of their new date. This action cannot be undone. Continue?</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Reason for moving <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" maxlength="500" placeholder="e.g. Fleet-wide rainout, machine breakdown, operator unavailable..." required></textarea>
                        <small class="text-muted">Sent to every affected farmer so they know why their schedule moved.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Yes, Move Schedules</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move Schedule -1 Day Confirmation Modal -->
<div class="modal fade" id="shiftDayBackwardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-backward me-2"></i>Move Schedule −1 Day</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.shift-day-backward') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>This will move <strong>every pending/approved schedule</strong> back by 1 day, and automatically notify each affected farmer of their new date. Any schedule that would land before the {{ \App\Models\ScheduleRequest::MIN_LEAD_DAYS }}-day minimum lead time is skipped instead of being backdated. This action cannot be undone. Continue?</p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Reason for moving <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" maxlength="500" placeholder="e.g. Original date was scheduled in error, farmer requested earlier slot..." required></textarea>
                        <small class="text-muted">Sent to every affected farmer so they know why their schedule moved.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">Yes, Move Schedules Back</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move One Day Confirmation Modal -->
<div class="modal fade" id="moveOneDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-day me-2"></i><span id="moveOneDayModalTitle">Move Selected Day(s)</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.shift-specific-day') }}" method="POST">
                @csrf
                <input type="hidden" name="direction" id="moveOneDayDirectionInput" value="forward">
                <div id="moveOneDayHiddenInputs"></div>
                <div class="modal-body">
                    <p id="moveOneDaySummaryIntro">This will move every pending/approved schedule on the date(s) below forward by 1 day, and notify each affected farmer. Any schedule that would conflict with an existing booking on the next day is skipped instead of overbooking. Continue?</p>
                    <ul id="moveOneDaySummary" class="mb-0 ps-3 small"></ul>
                    <div class="mb-0 mt-3">
                        <label class="form-label fw-semibold">Reason for moving <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="2" maxlength="500" placeholder="e.g. Fleet-wide rainout, machine breakdown, operator unavailable..." required></textarea>
                        <small class="text-muted">Sent to every affected farmer so they know why their schedule moved.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Yes, Move</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('manager.machine-schedule.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @include('manager.partials.schedule-form-fields', ['prefix' => 'create', 'schedule' => null])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var SCHEDULE_HOURS_PER_HECTARE = {{ \App\Models\ScheduleRequest::HOURS_PER_HECTARE }};

    document.querySelectorAll('.schedule-form').forEach(function (form) {
        var radios = form.querySelectorAll('input[name="member_type"]');
        var memberBlocks = form.querySelectorAll('.member-only');
        var nonMemberBlocks = form.querySelectorAll('.nonmember-only');

        function toggle() {
            var isMember = form.querySelector('input[name="member_type"]:checked').value === 'member';
            memberBlocks.forEach(function (block) { block.classList.toggle('d-none', !isMember); });
            nonMemberBlocks.forEach(function (block) { block.classList.toggle('d-none', isMember); });
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', toggle);
        });
        toggle();

        var landSizeInput = form.querySelector('input[name="land_size"]');
        var startTimeInput = form.querySelector('input[name="start_time"]');
        var endTimeInput = form.querySelector('input[name="end_time"]');
        var machinerySelect = form.querySelector('select[name="machinery"]');
        var unitsSelect = form.querySelector('select[name="units_requested"]');

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
        }

        function refreshUnitsOptions() {
            var selectedOption = machinerySelect.options[machinerySelect.selectedIndex];
            var quantity = selectedOption ? (parseInt(selectedOption.dataset.quantity, 10) || 1) : 1;
            var desired = unitsSelect.dataset.initialized ? (parseInt(unitsSelect.value, 10) || 1) : (parseInt(unitsSelect.dataset.selected, 10) || 1);

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

        landSizeInput.addEventListener('input', estimateEndTime);
        startTimeInput.addEventListener('input', estimateEndTime);
        unitsSelect.addEventListener('change', estimateEndTime);
        machinerySelect.addEventListener('change', refreshUnitsOptions);
        refreshUnitsOptions();
    });

    (function () {
        var moveBtn = document.getElementById('moveOneDayBtn');
        var toolbar = document.getElementById('moveOneDayToolbar');
        var cancelBtn = document.getElementById('moveOneDayCancel');
        var confirmBtn = document.getElementById('moveOneDayConfirm');
        var countLabel = document.getElementById('moveOneDayCount');
        var calendarGrid = document.querySelector('.calendar-grid');
        var hiddenInputs = document.getElementById('moveOneDayHiddenInputs');
        var summaryList = document.getElementById('moveOneDaySummary');
        var directionInput = document.getElementById('moveOneDayDirectionInput');
        var modalTitle = document.getElementById('moveOneDayModalTitle');
        var summaryIntro = document.getElementById('moveOneDaySummaryIntro');

        if (!moveBtn || !calendarGrid) {
            return;
        }

        function checkboxes() {
            return Array.prototype.slice.call(calendarGrid.querySelectorAll('.calendar-select-checkbox'));
        }

        function updateSelectionState() {
            var checked = checkboxes().filter(function (cb) { return cb.checked; });
            countLabel.textContent = checked.length + ' selected';
            confirmBtn.disabled = checked.length === 0;

            checkboxes().forEach(function (cb) {
                cb.closest('.calendar-cell').classList.toggle('is-day-selected', cb.checked);
            });
        }

        function exitSelectionMode() {
            calendarGrid.classList.remove('is-selecting');
            checkboxes().forEach(function (cb) {
                cb.checked = false;
                cb.closest('.calendar-cell').classList.remove('is-day-selected');
            });
            toolbar.classList.add('d-none');
            toolbar.classList.remove('d-flex');
            moveBtn.classList.remove('d-none');
            document.getElementById('moveOneDayForward').checked = true;
            updateSelectionState();
        }

        moveBtn.addEventListener('click', function () {
            calendarGrid.classList.add('is-selecting');
            toolbar.classList.remove('d-none');
            toolbar.classList.add('d-flex');
            moveBtn.classList.add('d-none');
            updateSelectionState();
        });

        cancelBtn.addEventListener('click', exitSelectionMode);

        calendarGrid.addEventListener('change', function (e) {
            if (e.target.classList.contains('calendar-select-checkbox')) {
                updateSelectionState();
            }
        });

        confirmBtn.addEventListener('click', function () {
            var checked = checkboxes().filter(function (cb) { return cb.checked; });

            if (checked.length === 0) {
                return;
            }

            var direction = document.querySelector('input[name="moveOneDayDirection"]:checked').value;
            var isBackward = direction === 'backward';

            directionInput.value = direction;
            modalTitle.textContent = isBackward ? 'Move Selected Day(s) Back' : 'Move Selected Day(s)';
            summaryIntro.textContent = isBackward
                ? 'This will move every pending/approved schedule on the date(s) below back by 1 day, and notify each affected farmer. Any schedule that would conflict with an existing booking, or fall before the minimum lead time, is skipped instead.'
                : 'This will move every pending/approved schedule on the date(s) below forward by 1 day, and notify each affected farmer. Any schedule that would conflict with an existing booking on the next day is skipped instead of overbooking.';

            hiddenInputs.innerHTML = '';
            summaryList.innerHTML = '';

            checked.forEach(function (cb) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'dates[]';
                input.value = cb.value;
                hiddenInputs.appendChild(input);

                var date = new Date(cb.value + 'T00:00:00');
                var formatted = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                var count = cb.dataset.count;

                var li = document.createElement('li');
                li.textContent = formatted + ' — ' + count + ' schedule(s)';
                summaryList.appendChild(li);
            });

            new bootstrap.Modal(document.getElementById('moveOneDayModal')).show();
        });
    })();
</script>
@endsection
