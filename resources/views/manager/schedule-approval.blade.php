@extends('manager.layout')

@section('title', 'Schedule Approval')
@section('header', 'Schedule Approval')

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

<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <form method="GET" action="{{ route('manager.schedule-approval') }}" class="d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search requests..." class="form-control ps-5" style="min-width: 220px;">
                    <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
                </div>
                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denied</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control" style="width: auto;" onchange="this.form.submit()">
                <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                @if(request()->anyFilled(['search', 'status', 'date']))
                <a href="{{ route('manager.schedule-approval') }}" class="btn btn-link btn-sm">Clear</a>
                @endif
            </form>
        </x-slot:filters>
    </x-table-toolbar>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Request ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machine Type</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Remarks</th>
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
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $req->scheduled_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">
                        {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}
                    </td>
                    <td class="px-4 px-md-6 py-4">
                        @if($req->is_reschedule)
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Reschedule Request</span>
                        @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">New Request</span>
                        @endif
                    </td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($req->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#scheduleModal{{ $req->id }}" />
                            @if($req->status === 'pending')
                            <x-icon-button icon="fa-check" color="success" title="Approve" data-bs-toggle="modal" data-bs-target="#approveModal{{ $req->id }}" />
                            <x-icon-button icon="fa-times" color="danger" title="Deny" data-bs-toggle="modal" data-bs-target="#denyModal{{ $req->id }}" />
                            @endif
                        </div>
                    </td>
                </tr>

                <!-- Details Modal -->
                <x-modal id="scheduleModal{{ $req->id }}" title="Schedule Request Details">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="text-muted small d-block">Request ID</label>
                            <p class="fw-medium mb-0">SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block">Farmer Name</label>
                            <p class="fw-medium mb-0">{{ $req->display_name }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block">Machine Type</label>
                            <p class="fw-medium mb-0">{{ $req->machinery }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block mb-1">Member Status</label>
                            <x-status-badge :status="$req->member_type === 'member' ? 'Member' : 'Non-member'" />
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block">Scheduled Date</label>
                            <p class="fw-medium mb-0">{{ $req->scheduled_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block">Time</label>
                            <p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block">Farm Location</label>
                            <p class="fw-medium mb-0">{{ $req->location }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small d-block">Land Area</label>
                            <p class="fw-medium mb-0">{{ $req->land_size }} hectares</p>
                        </div>
                        @if($req->is_reschedule && $req->originalSchedule)
                        <div class="col-12">
                            <label class="text-muted small d-block">Original Schedule</label>
                            <p class="fw-medium mb-0">{{ $req->originalSchedule->scheduled_date->format('M d, Y') }}, {{ \Carbon\Carbon::parse($req->originalSchedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($req->originalSchedule->end_time)->format('g:i A') }}</p>
                        </div>
                        @endif
                        @if($req->status === 'denied' && $req->denial_reason)
                        <div class="col-12">
                            <label class="text-muted small d-block">Denial Reason</label>
                            <p class="fw-medium mb-0 text-danger">{{ $req->denial_reason }}</p>
                        </div>
                        @endif
                        @if($req->remarks)
                        <div class="col-12">
                            <label class="text-muted small d-block">Remarks</label>
                            <p class="fw-medium mb-0">{{ $req->remarks }}</p>
                        </div>
                        @endif
                    </div>
                </x-modal>

                @if($req->status === 'pending')
                <!-- Approve Modal -->
                <div class="modal fade" id="approveModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-check-circle me-2"></i>Approve Schedule</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.schedule-approval.approve', $req) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="modal-body">
                                    <p>Approve the {{ $req->machinery }} request for <strong>{{ $req->display_name }}</strong> on {{ $req->scheduled_date->format('M d, Y') }}?</p>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Remarks (optional)</label>
                                        <textarea name="remarks" rows="2" class="form-control" placeholder="Add remarks..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Deny Modal -->
                <div class="modal fade" id="denyModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>Deny Schedule</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('manager.schedule-approval.deny', $req) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="modal-body">
                                    <p>Deny the {{ $req->machinery }} request for <strong>{{ $req->display_name }}</strong> on {{ $req->scheduled_date->format('M d, Y') }}?</p>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Reason for denial <span class="text-danger">*</span></label>
                                        <textarea name="denial_reason" rows="2" class="form-control" placeholder="e.g. Machinery unavailable / scheduling conflict" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Deny</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No schedule requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
