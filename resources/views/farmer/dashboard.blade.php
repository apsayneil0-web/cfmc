@extends('farmer.layout')

@section('title', 'Farmer Dashboard')
@section('header', 'My Dashboard')

@section('content')
<!-- Welcome Banner -->
<x-info-banner variant="success" title="Welcome back!" class="mb-6">
    Here's a summary of your membership, schedules, and cooperative updates.
</x-info-banner>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Membership Status" value="Pending" icon="fa-id-card" color="warning" />
    <x-stat-card label="CBU Balance" value="₱12,500" icon="fa-piggy-bank" color="primary" />
    <x-stat-card label="Active Loan Balance" value="₱0" icon="fa-hand-holding-usd" color="success" />
    <x-stat-card label="Upcoming Schedules" value="1" icon="fa-calendar-check" color="info" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- My Profile Summary -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">My Profile Summary</h3>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="text-muted small d-block">Crop Type</label>
                <p class="fw-medium mb-0">Rice</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small d-block">Land Area</label>
                <p class="fw-medium mb-0">2.5 hectares</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small d-block">Barangay</label>
                <p class="fw-medium mb-0">Brgy. San Jose</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small d-block mb-1">Membership Status</label>
                <x-status-badge status="Pending" />
            </div>
        </div>

        <hr class="my-4 text-gray-200">

        <h4 class="text-sm font-semibold text-gray-900 mb-3">Recent Updates</h4>
        <div class="space-y-3">
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-calendar text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 mb-0">Harvester rental scheduled</p>
                    <p class="text-xs text-gray-500 mb-0">July 10, 2026 - 8:00 AM</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 mb-0">Membership request under review</p>
                    <p class="text-xs text-gray-500 mb-0">Submitted July 1, 2026</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Cooperative Announcements</h3>
        <div class="space-y-3">
            <div class="p-3 rounded-lg border border-gray-200">
                <p class="text-sm font-medium text-gray-900 mb-1">Schedule Maintenance Notice</p>
                <p class="text-xs text-gray-500 mb-0">Harvester unit under maintenance</p>
            </div>
            <div class="p-3 rounded-lg border border-gray-200">
                <p class="text-sm font-medium text-gray-900 mb-1">Membership Renewal</p>
                <p class="text-xs text-gray-500 mb-0">Annual membership renewal reminder</p>
            </div>
        </div>
    </div>
</div>
@endsection
