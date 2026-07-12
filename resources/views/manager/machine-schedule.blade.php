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
        </form>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus"></i><span>Add Schedule</span>
            </button>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="p-4 p-md-6">
        <x-schedule-calendar :calendar-days="$calendarDays" :first-weekday="$firstWeekday" :days-in-month="$daysInMonth"
            :show-names="true" min-height="120px" />
    </div>
</div>

<!-- Schedule List -->
<div class="section-card">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">All Schedules</h3>
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
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->scheduled_date->format('M d, Y') }} - {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="$req->member_type === 'member' ? 'Member' : 'Non-member'" /></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $req->id }}" />
                            @if($req->status === 'approved')
                            <x-icon-button icon="fa-clipboard-check" color="success" title="Complete & Record Yield" data-bs-toggle="modal" data-bs-target="#completeModal{{ $req->id }}" />
                            @endif
                            <x-icon-button icon="fa-archive" color="danger" title="Archive" data-bs-toggle="modal" data-bs-target="#archiveModal{{ $req->id }}" />
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <x-modal id="viewModal{{ $req->id }}" title="Schedule Details">
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small d-block">Schedule ID</label><p class="fw-medium mb-0">SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $req->display_name }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Machinery</label><p class="fw-medium mb-0">{{ $req->machinery }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block mb-1">Member Status</label><x-status-badge :status="$req->member_type === 'member' ? 'Member' : 'Non-member'" /></div>
                        <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $req->scheduled_date->format('M d, Y') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Time</label><p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Location</label><p class="fw-medium mb-0">{{ $req->location }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Land Area</label><p class="fw-medium mb-0">{{ $req->land_size }} hectares</p></div>
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
                                    <p>Close out this schedule for <strong>{{ $req->display_name }}</strong> and record the total harvest yield.</p>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Harvest Yield (sacks/tons) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" name="harvest_yield" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Mark Complete</button>
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
                @empty
                <tr>
                    <td colspan="7" class="px-4 px-md-6 py-6 text-center text-muted">No schedules found for this month.</td>
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
    document.querySelectorAll('.schedule-form').forEach(function (form) {
        var radios = form.querySelectorAll('input[name="member_type"]');
        var memberBlock = form.querySelector('.member-only');
        var nonMemberBlock = form.querySelector('.nonmember-only');

        function toggle() {
            var isMember = form.querySelector('input[name="member_type"]:checked').value === 'member';
            memberBlock.classList.toggle('d-none', !isMember);
            nonMemberBlock.classList.toggle('d-none', isMember);
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', toggle);
        });
        toggle();
    });
</script>
@endsection
