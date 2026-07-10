@extends('farmer.layout')

@section('title', 'Farmer Dashboard')
@section('header', 'My Dashboard')

@section('content')
<!-- Welcome Banner -->
<x-info-banner variant="success" title="Welcome back!" class="mb-6">
    Here's a summary of your machinery schedule requests and loan appointments.
</x-info-banner>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Pending Schedules" value="{{ $pendingSchedules }}" icon="fa-hourglass-half" color="warning" />
    <x-stat-card label="Approved Schedules" value="{{ $approvedSchedules }}" icon="fa-calendar-check" color="success" />
    <x-stat-card label="Denied Schedules" value="{{ $deniedSchedules }}" icon="fa-calendar-times" color="danger" />
    <x-stat-card label="Upcoming Loan Appointments" value="{{ $upcomingAppointments }}" icon="fa-hand-holding-usd" color="info" />
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-0">Recent Schedule Requests</h3>
        <a href="{{ route('farmer.schedule') }}" class="text-sm text-primary text-decoration-none">Request a schedule</a>
    </div>
    <div class="space-y-3">
        @forelse($recentSchedules as $schedule)
        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
            <div class="w-10 h-10 rounded-full bg-brand-light flex items-center justify-center">
                <i class="fas fa-tractor text-brand"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 mb-0">{{ $schedule->machinery }} &middot; {{ $schedule->land_size }} ha</p>
                <p class="text-xs text-gray-500 mb-0">{{ $schedule->scheduled_date->format('M d, Y') }}</p>
            </div>
            <x-status-badge :status="ucfirst($schedule->status)" />
        </div>
        @empty
        <p class="text-sm text-muted mb-0">You haven't requested any machinery schedules yet.</p>
        @endforelse
    </div>
</div>
@endsection
