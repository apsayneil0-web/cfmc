@extends('admin.layout')

@section('title', 'View Schedule')
@section('header', 'Machinery Schedule Overview')

@section('content')
<div class="section-card">
    <div class="table-toolbar d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="position-relative">
                <input type="text" placeholder="Search schedule..." class="form-control ps-5 py-2" style="min-width: 240px;">
                <i class="fas fa-search position-absolute start-3 top-50 translate-middle-y text-muted" style="font-size: 14px;"></i>
            </div>
            <select class="form-select" style="width: auto;">
                <option value="">All Machinery</option>
                <option value="harvester">Harvester</option>
                <option value="tractor">Tractor</option>
                <option value="pump-boat">Pump Boat</option>
            </select>
            <select class="form-select" style="width: auto;">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Schedule ID</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Farmer Name</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Machinery</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Date</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Time</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Location</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Status</th>
                    <th class="px-4 px-md-6 py-3 text-xs font-medium text-uppercase text-muted">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-001</td>
                    <td class="px-4 px-md-6 py-4">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Harvester</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 10, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">8:00 AM - 12:00 PM</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Brgy. San Jose</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <x-icon-button icon="fa-eye" color="primary" title="View Details" />
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-002</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Tractor</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 12, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">7:00 AM - 11:00 AM</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Brgy. Poblacion</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <x-icon-button icon="fa-eye" color="primary" title="View Details" />
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-003</td>
                    <td class="px-4 px-md-6 py-4">Roberto Tan</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Pump Boat</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 15, 2026</td>
                    <td class="px-4 px-md-6 py-4 text-muted">6:00 AM - 9:00 AM</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Brgy. Malinao</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Pending" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <x-icon-button icon="fa-eye" color="primary" title="View Details" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing 1-3 of 3 entries</p>
    </div>
</div>

<x-info-banner variant="info" title="Monitoring Only" class="mt-6">
    This view is for oversight of machinery usage across the cooperative. Schedule requests are created and processed by the Manager to avoid conflicts and support efficient planning.
</x-info-banner>
@endsection
