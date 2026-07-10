@extends('manager.layout')

@section('title', 'Machine Scheduling')
@section('header', 'Machine Rental Scheduling')

@section('content')
<div class="section-card mb-6">
    <!-- Header Actions -->
    <div class="table-toolbar d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <select class="form-select" style="width: auto;">
                <option>July 2026</option>
                <option>August 2026</option>
                <option>September 2026</option>
            </select>
            <select class="form-select" style="width: auto;">
                <option>All Machines</option>
                <option>Harvester</option>
                <option>Tractor</option>
                <option>Pump Boat</option>
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fas fa-calendar-week"></i><span>Week</span>
            </button>
            <button class="btn btn-primary d-flex align-items-center gap-2">
                <i class="fas fa-plus"></i><span>Add Schedule</span>
            </button>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="p-4 p-md-6">
        <div class="grid grid-cols-7 gap-2 mb-4">
            <div class="text-center text-xs font-medium text-muted py-2">Sun</div>
            <div class="text-center text-xs font-medium text-muted py-2">Mon</div>
            <div class="text-center text-xs font-medium text-muted py-2">Tue</div>
            <div class="text-center text-xs font-medium text-muted py-2">Wed</div>
            <div class="text-center text-xs font-medium text-muted py-2">Thu</div>
            <div class="text-center text-xs font-medium text-muted py-2">Fri</div>
            <div class="text-center text-xs font-medium text-muted py-2">Sat</div>
        </div>
        <div class="grid grid-cols-7 gap-2">
            @for($i = 0; $i < 7; $i++)
            <div class="rounded-lg p-2 bg-light border border-gray-200" style="min-height: 120px;">
                <p class="text-xs font-medium text-muted mb-2">{{ $i + 1 }}</p>
                @if($i == 2)
                <div class="bg-primary bg-opacity-10 text-primary text-xs p-2 rounded mb-1">
                    <p class="fw-medium mb-0">Juan - Harvester</p>
                    <p class="text-xs mb-0">8AM-12PM</p>
                </div>
                <div class="bg-success bg-opacity-10 text-success text-xs p-2 rounded">
                    <p class="fw-medium mb-0">Maria - Tractor</p>
                    <p class="text-xs mb-0">1PM-5PM</p>
                </div>
                @endif
            </div>
            @endfor
            @for($i = 7; $i < 14; $i++)
            <div class="rounded-lg p-2 border border-gray-200" style="min-height: 120px;">
                <p class="text-xs font-medium text-muted mb-2">{{ $i + 1 }}</p>
            </div>
            @endfor
            @for($i = 14; $i < 21; $i++)
            <div class="rounded-lg p-2 border border-gray-200" style="min-height: 120px;">
                <p class="text-xs font-medium text-muted mb-2">{{ $i + 1 }}</p>
                @if($i == 18)
                <div class="bg-purple text-white bg-opacity-75 text-xs p-2 rounded">
                    <p class="fw-medium mb-0">Pedro - Pump</p>
                    <p class="text-xs mb-0">6AM-9AM</p>
                </div>
                @endif
            </div>
            @endfor
            @for($i = 21; $i < 28; $i++)
            <div class="rounded-lg p-2 border border-gray-200" style="min-height: 120px;">
                <p class="text-xs font-medium text-muted mb-2">{{ $i + 1 }}</p>
            </div>
            @endfor
            @for($i = 28; $i < 31; $i++)
            <div class="rounded-lg p-2 border border-gray-200" style="min-height: 120px;">
                <p class="text-xs font-medium text-muted mb-2">{{ $i + 1 }}</p>
            </div>
            @endfor
        </div>
    </div>
</div>

<!-- Schedule List -->
<div class="section-card">
    <div class="table-toolbar">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Upcoming Schedules</h3>
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
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-001</td>
                    <td class="px-4 px-md-6 py-4">Juan Dela Cruz</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Harvester</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 10, 2026 - 8:00 AM</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Member" /></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-calendar-alt" color="secondary" title="Reschedule" />
                            <x-icon-button icon="fa-archive" color="danger" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-002</td>
                    <td class="px-4 px-md-6 py-4">Maria Santos</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Tractor</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 12, 2026 - 7:00 AM</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Member" /></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Approved" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-calendar-alt" color="secondary" title="Reschedule" />
                            <x-icon-button icon="fa-archive" color="danger" title="Archive" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="px-4 px-md-6 py-4 fw-medium text-dark">SCH-003</td>
                    <td class="px-4 px-md-6 py-4">Roberto Tan</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Harvester</td>
                    <td class="px-4 px-md-6 py-4 text-muted">Jul 15, 2026 - 9:00 AM</td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Non-member" /></td>
                    <td class="px-4 px-md-6 py-4"><x-status-badge status="Pending" /></td>
                    <td class="px-4 px-md-6 py-4">
                        <div class="d-flex gap-1">
                            <x-icon-button icon="fa-eye" color="primary" title="View" />
                            <x-icon-button icon="fa-edit" color="warning" title="Edit" />
                            <x-icon-button icon="fa-archive" color="danger" title="Archive" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
