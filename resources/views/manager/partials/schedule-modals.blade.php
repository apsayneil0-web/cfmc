{{-- View / Edit / Complete / Archive / Restore modals for one schedule ($req). --}}
<!-- View Modal -->
<x-modal id="viewModal{{ $req->id }}" title="Schedule Details">
    <div class="row g-3">
        <div class="d-none col-6"><label class="text-muted small d-block">Schedule ID</label><p class="fw-medium mb-0">SCH-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</p></div>
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
                <p class="mb-0">Archive this schedule for {{ $req->display_name }}? It will be removed from the active list but kept for reporting and auditing.</p>
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
                <p class="mb-0">Restore this schedule for {{ $req->display_name }}? It will reappear on the active calendar and schedule list.</p>
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
