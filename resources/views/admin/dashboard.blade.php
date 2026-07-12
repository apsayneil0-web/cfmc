@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Registered Farmers" value="{{ $totalFarmers }}" icon="fa-seedling" color="success" />
    <x-stat-card label="Pending Membership Applications" value="{{ $pendingMembership }}" icon="fa-user-clock" color="warning" />
    <x-stat-card label="Pending Loan Requests" value="0" icon="fa-hand-holding-usd" color="danger" />
    <x-stat-card label="Machinery Schedules" value="0" icon="fa-tractor" color="info" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Membership Applications -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-0">Recent Membership Applications</h3>
            <a href="{{ route('admin.membership-approval') }}" class="text-sm text-primary text-decoration-none">Review all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentApplications as $application)
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <x-avatar-initials :name="$application->full_name" color="success" size="8" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 mb-0">{{ $application->full_name }}</p>
                    <p class="text-xs text-gray-500 mb-0">{{ $application->crop->name ?? 'N/A' }} &middot; Brgy. {{ $application->barangay ?? $application->municipality }}</p>
                </div>
                <x-status-badge :status="$application->status" />
            </div>
            @empty
            <p class="text-sm text-muted mb-0">No membership applications yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Cooperative Snapshot -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Cooperative Snapshot</h3>
        <div class="space-y-3">
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">Pending Memberships</span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $pendingMembership }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">Approved Members</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $approvedFarmers }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">Rejected Applications</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $rejectedFarmers }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">Archived Members</span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $archivedFarmers }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Machinery Schedule Calendar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Machinery Schedule &mdash; {{ now()->format('F Y') }}</h3>
        <p class="text-sm text-muted mb-0">Approved machinery bookings for the current month.</p>
    </div>
    <x-schedule-calendar :calendar-days="$calendarDays" :first-weekday="$firstWeekday" :days-in-month="$daysInMonth"
        :show-names="true" min-height="90px" />
</div>
@endsection

