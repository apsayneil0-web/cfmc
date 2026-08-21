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
                @foreach($machineryList as $machinery)
                <option value="{{ $machinery }}">{{ $machinery }}</option>
                @endforeach
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
                @forelse($schedules as $schedule)
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-{{ str_pad($schedule->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 px-md-6 py-4">{{ $schedule->display_name }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $schedule->machinery }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $schedule->scheduled_date->format('M d, Y') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</td>
                    <td class="px-4 px-md-6 py-4 text-muted">{{ $schedule->location }}</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge :status="ucfirst($schedule->status)" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <x-icon-button icon="fa-eye" color="primary" title="View Details" data-bs-toggle="modal" data-bs-target="#viewScheduleModal{{ $schedule->id }}" />
                    </td>
                </tr>

                <x-modal id="viewScheduleModal{{ $schedule->id }}" title="Schedule Details">
                    <div class="row g-3">
                        <div class="col-6"><label class="text-muted small d-block">Schedule ID</label><p class="fw-medium mb-0">SCH-{{ str_pad($schedule->id, 3, '0', STR_PAD_LEFT) }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Status</label><p class="fw-medium mb-0"><x-status-badge :status="ucfirst($schedule->status)" /></p></div>
                        <div class="col-6"><label class="text-muted small d-block">Farmer Name</label><p class="fw-medium mb-0">{{ $schedule->display_name }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Machinery</label><p class="fw-medium mb-0">{{ $schedule->machinery }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Land Size</label><p class="fw-medium mb-0">{{ $schedule->land_size }} ha</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Date</label><p class="fw-medium mb-0">{{ $schedule->scheduled_date->format('M d, Y') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Time</label><p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</p></div>
                        <div class="col-6"><label class="text-muted small d-block">Location</label><p class="fw-medium mb-0">{{ $schedule->location }}</p></div>
                        @if($schedule->remarks)
                        <div class="col-12"><label class="text-muted small d-block">Remarks</label><p class="fw-medium mb-0">{{ $schedule->remarks }}</p></div>
                        @endif
                    </div>
                </x-modal>
                @empty
                <tr>
                    <td colspan="8" class="px-4 px-md-6 py-6 text-center text-muted">No schedules recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 px-md-6 py-4 border-top d-flex align-items-center justify-content-between">
        <p class="text-muted small mb-0">Showing {{ $schedules->count() }} of {{ $schedules->count() }} entries</p>
    </div>
</div>

<x-info-banner variant="info" title="Monitoring Only" class="mt-6">
    This view is for oversight of machinery usage across the cooperative. Schedule requests are created and processed by the Manager to avoid conflicts and support efficient planning.
</x-info-banner>
@endsection
