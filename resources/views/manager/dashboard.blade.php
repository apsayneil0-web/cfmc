@extends('manager.layout')

@section('title', 'Manager Dashboard')
@section('header', 'Dashboard')

@section('content')
@php
    // Dot colors follow the badge rule: wheat = waiting, clay = needs action, grey = nothing to do.
    $attention = [
        [
            'label' => 'Pending Membership Requests',
            'value' => $stats['pending_membership'],
            'dot' => $stats['pending_membership'] > 0 ? 'warning' : null,
            'link' => 'Members Central',
            'route' => route('manager.membership'),
        ],
        [
            'label' => 'Pending Schedule Requests',
            'value' => $stats['pending_schedules'],
            'dot' => $stats['pending_schedules'] > 0 ? 'warning' : null,
            'link' => 'Review requests',
            'route' => route('manager.schedule-approval'),
        ],
        [
            'label' => 'Active Loan Applications',
            'value' => $stats['active_loan_applications'],
            'dot' => $stats['active_loan_applications'] > 0 ? 'warning' : null,
            'link' => 'Finance Central',
            'route' => route('manager.loan-request'),
        ],
        [
            'label' => 'Available Machinery',
            'value' => $stats['available_machinery'],
            'dot' => $stats['maintenance_machinery'] > 0 ? 'danger' : null,
            'link' => $stats['maintenance_machinery'] > 0
                ? $stats['maintenance_machinery'].' due for maintenance'
                : 'Machinery',
            'route' => route('manager.machinery'),
        ],
    ];
@endphp

<!-- Greeting & quick actions -->
<div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $greeting }}, {{ auth()->user()->name }}</h2>
        <p class="text-sm text-gray-500 mb-0">
            {{ $localNow->format('l, F j, Y') }} &middot;
            @if($attentionCount > 0)
                {{ $attentionCount }} {{ Str::plural('item', $attentionCount) }} {{ $attentionCount === 1 ? 'needs' : 'need' }} your attention
            @else
                Nothing needs your attention right now
            @endif
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('manager.membership') }}" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i>New Membership</a>
        <a href="{{ route('manager.schedule-approval') }}" class="btn btn-outline-secondary"><i class="fas fa-calendar-check me-2"></i>Approve Schedule</a>
        <a href="{{ route('manager.announcement') }}" class="btn btn-outline-secondary"><i class="fas fa-bullhorn me-2"></i>Create Announcement</a>
        <a href="{{ route('manager.reporting') }}" class="btn btn-outline-secondary"><i class="fas fa-file-alt me-2"></i>Generate Report</a>
    </div>
</div>

<!-- Needs your attention -->
<p class="text-xs fw-semibold text-gray-500 text-uppercase mb-3" style="letter-spacing: 0.08em;">Needs your attention</p>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    @foreach($attention as $card)
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6 d-flex flex-column">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <p class="text-sm text-gray-500 mb-1">{{ $card['label'] }}</p>
            <span class="rounded-circle flex-shrink-0 mt-1 {{ $card['dot'] ? 'bg-'.$card['dot'] : 'bg-gray-300' }}"
                style="width: 10px; height: 10px;" aria-hidden="true"></span>
        </div>
        <p class="text-xl font-bold text-gray-900 mb-3">{{ $card['value'] }}</p>
        <a href="{{ $card['route'] }}" class="text-sm fw-semibold text-decoration-none mt-auto {{ $card['dot'] === 'danger' ? 'text-danger' : 'text-brand' }}">
            {{ $card['link'] }} &rarr;
        </a>
    </div>
    @endforeach
</div>

<!-- Totals -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <div class="grid grid-cols-2 lg:grid-cols-4">
        <div class="stat-card p-5 border-b lg:border-b-0 border-e border-gray-200">
            <p class="text-sm text-gray-500 mb-1">Total Registered Farmers</p>
            <p class="text-xl font-bold text-gray-900 mb-0">{{ number_format($stats['total_farmers']) }}</p>
        </div>
        <div class="stat-card p-5 border-b lg:border-b-0 lg:border-e border-gray-200">
            <p class="text-sm text-gray-500 mb-1">Approved Schedules</p>
            <p class="text-xl font-bold text-gray-900 mb-0">{{ number_format($stats['approved_schedules']) }}</p>
        </div>
        <div class="stat-card p-5 border-e border-gray-200">
            <p class="text-sm text-gray-500 mb-1">Total CBU</p>
            <p class="text-xl font-bold text-warning mb-0" style="overflow-wrap: break-word;">{{ peso($stats['total_cbu']) }}</p>
        </div>
        <div class="stat-card p-5">
            <p class="text-sm text-gray-500 mb-1">Total Expenses</p>
            <p class="text-xl font-bold text-gray-900 mb-0" style="overflow-wrap: break-word;">{{ peso($stats['total_expenses']) }}</p>
        </div>
    </div>
</div>

<!-- Recent Activities & Machine Schedule -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6 align-self-start">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-0">Recent Activities</h3>
            <a href="{{ route('manager.reporting') }}" class="text-sm text-brand text-decoration-none">View All</a>
        </div>
        <div class="space-y-4">
            @forelse($recentActivities as $activity)
            <div class="flex items-center gap-4 {{ $loop->last ? '' : 'pb-4 border-b border-gray-100' }}">
                <div class="w-10 h-10 rounded-full {{ $activity->bg }} flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $activity->icon }} {{ $activity->color }}"></i>
                </div>
                <div class="flex-1" style="min-width: 0;">
                    <p class="text-sm font-medium text-gray-900 mb-0">{{ $activity->title }}</p>
                    <p class="text-xs text-gray-500 mb-0">{{ $activity->subtitle }} &middot; {{ $activity->at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-muted mb-0">No recent activity yet.</p>
            @endforelse
        </div>
    </div>

    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-0">Machine Schedule &mdash; {{ now()->format('F Y') }}</h3>
                <p class="text-sm text-muted mb-0">Approved machinery bookings for the current month.</p>
            </div>
            <a href="{{ route('manager.machine-schedule') }}" class="text-sm text-brand text-decoration-none text-nowrap">Full calendar</a>
        </div>
        <x-schedule-calendar :calendar-days="$calendarDays" :first-weekday="$firstWeekday" :days-in-month="$daysInMonth"
            :show-names="true" :compact="true" min-height="64px" />

        <div class="border-top border-gray-200 mt-4 pt-4">
            <p class="text-sm text-gray-500 mb-2">Next bookings</p>
            @forelse($nextBookings as $booking)
            <div class="d-flex align-items-center gap-3 py-2 {{ $loop->last ? '' : 'border-bottom border-gray-100' }}">
                <span class="text-sm fw-bold text-gray-900 flex-shrink-0" style="width: 4rem;">{{ $booking->scheduled_date->format('M j') }}</span>
                <span class="text-sm text-gray-700 flex-grow-1" style="min-width: 0;">
                    {{ $booking->machinery }} &middot; {{ $booking->display_name }}
                    @if($booking->land_size) &middot; {{ rtrim(rtrim(number_format($booking->land_size, 2), '0'), '.') }} ha @endif
                </span>
                <x-status-badge status="Approved" />
            </div>
            @empty
            <p class="text-sm text-muted mb-0">No upcoming approved bookings.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
