@extends('manager.layout')

@section('title', 'Schedule Approval')
@section('header', 'Schedule Approval')

@section('content')
<div class="section-card">
    <x-table-toolbar>
        <x-slot:filters>
            <div class="position-relative">
                <input type="text" placeholder="Search requests..." class="form-control ps-5" style="min-width: 220px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="denied">Denied</option>
            </select>
            <input type="date" class="form-control" style="width: auto;">
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
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Duration</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-001</td>
                    <td class="px-4 px-md-6 py-4">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Harvester</td>
                    <td class="px-4 px-md-6 py-4 text-muted">July 10, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">8:00 AM</td>
                    <td class="px-4 px-md-6 py-4 text-muted">4 hours</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Pending" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#scheduleModal" />
                            <x-icon-button icon="fa-calendar-check" color="success" title="Check Availability" />
                            <x-icon-button icon="fa-check" color="success" title="Approve" />
                            <x-icon-button icon="fa-times" color="danger" title="Deny" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-002</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Tractor</td>
                    <td class="px-4 px-md-6 py-4 text-muted">July 12, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">7:00 AM</td>
                    <td class="px-4 px-md-6 py-4 text-muted">6 hours</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Pending" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#scheduleModal" />
                            <x-icon-button icon="fa-calendar-check" color="success" title="Check Availability" />
                            <x-icon-button icon="fa-check" color="success" title="Approve" />
                            <x-icon-button icon="fa-times" color="danger" title="Deny" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-003</td>
                    <td class="px-4 px-md-6 py-4">Pedro Reyes</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Pump Boat</td>
                    <td class="px-4 px-md-6 py-4 text-muted">July 08, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">6:00 AM</td>
                    <td class="px-4 px-md-6 py-4 text-muted">3 hours</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#scheduleModal" />
                            <x-icon-button icon="fa-check" color="secondary" title="Approve" disabled />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-004</td>
                    <td class="px-4 px-md-6 py-4">Ana Garcia</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Harvester</td>
                    <td class="px-4 px-md-6 py-4 text-muted">July 05, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">8:00 AM</td>
                    <td class="px-4 px-md-6 py-4 text-muted">5 hours</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Denied" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#scheduleModal" />
                            <x-icon-button icon="fa-check" color="secondary" title="Approve" disabled />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-10 of 18 entries</p>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" disabled>Previous</button>
            <button class="btn btn-outline-secondary btn-sm">Next</button>
        </div>
    </div>
</div>

<!-- Schedule Details Modal -->
<x-modal id="scheduleModal" title="Schedule Request Details">
    <div class="row g-3 mb-3">
        <div class="col-6">
            <label class="text-muted small d-block">Request ID</label>
            <p class="fw-medium mb-0">SCH-001</p>
        </div>
        <div class="col-6">
            <label class="text-muted small d-block">Farmer Name</label>
            <p class="fw-medium mb-0">Juan Dela Cruz</p>
        </div>
        <div class="col-6">
            <label class="text-muted small d-block">Machine Type</label>
            <p class="fw-medium mb-0">Harvester</p>
        </div>
        <div class="col-6">
            <label class="text-muted small d-block mb-1">Member Status</label>
            <x-status-badge status="Member" />
        </div>
        <div class="col-6">
            <label class="text-muted small d-block">Scheduled Date</label>
            <p class="fw-medium mb-0">July 10, 2026</p>
        </div>
        <div class="col-6">
            <label class="text-muted small d-block">Time</label>
            <p class="fw-medium mb-0">8:00 AM - 12:00 PM</p>
        </div>
        <div class="col-6">
            <label class="text-muted small d-block">Farm Location</label>
            <p class="fw-medium mb-0">Brgy. San Jose</p>
        </div>
        <div class="col-6">
            <label class="text-muted small d-block">Land Area</label>
            <p class="fw-medium mb-0">2.5 hectares</p>
        </div>
    </div>

    <div class="bg-light rounded-3 p-3 mb-3">
        <h4 class="text-sm fw-semibold text-dark mb-2">Machinery Availability</h4>
        <div class="d-flex align-items-center gap-2 small">
            <span class="rounded-circle bg-success" style="width: 0.75rem; height: 0.75rem; display: inline-block;"></span>
            <span class="text-dark">Available - Harvester #002</span>
        </div>
    </div>

    <div>
        <label class="form-label fw-semibold">Remarks</label>
        <textarea rows="3" class="form-control" placeholder="Add remarks..."></textarea>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger">Deny</button>
        <button type="button" class="btn btn-success">Approve</button>
    </x-slot:footer>
</x-modal>
@endsection
