@extends('manager.layout')

@section('title', 'Manager Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Registered Farmers" value="156" icon="fa-users" color="primary" trend="12% from last month" />
    <x-stat-card label="Pending Membership Requests" value="23" icon="fa-user-clock" color="warning" />
    <x-stat-card label="Pending Schedule Requests" value="18" icon="fa-calendar-alt" color="purple" />
    <x-stat-card label="Approved Schedules" value="45" icon="fa-calendar-check" color="success" />
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Active Loan Applications" value="12" icon="fa-hand-holding-usd" color="danger" />
    <x-stat-card label="Available Machinery" value="8" icon="fa-tractor" color="info" />
    <x-stat-card label="Total CBU" value="₱1.2M" icon="fa-piggy-bank" color="purple" trend="8% growth" />
    <x-stat-card label="Net Income" value="₱890K" icon="fa-chart-line" color="success" trend="15% from last month" />
</div>

<!-- Recent Activities & Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Activities -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
            <a href="{{ route('manager.reporting') }}" class="text-sm text-brand text-decoration-none">View All</a>
        </div>
        <div class="space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Membership request approved</p>
                    <p class="text-xs text-gray-500">Juan Dela Cruz - Brgy. San Jose</p>
                </div>
                <p class="text-xs text-gray-400">2 hours ago</p>
            </div>
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-full bg-brand-light flex items-center justify-center">
                    <i class="fas fa-calendar text-brand"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Schedule request submitted</p>
                    <p class="text-xs text-gray-500">Maria Santos - Harvester Rental</p>
                </div>
                <p class="text-xs text-gray-400">4 hours ago</p>
            </div>
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Machinery maintenance alert</p>
                    <p class="text-xs text-gray-500">Tractor #003 - 500 hours reached</p>
                </div>
                <p class="text-xs text-gray-400">6 hours ago</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-purple-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">Loan payment received</p>
                    <p class="text-xs text-gray-500">Pedro Reyes - PHP 15,000</p>
                </div>
                <p class="text-xs text-gray-400">1 day ago</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
        <div class="space-y-3">
            <a href="{{ route('manager.membership') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-brand-light flex items-center justify-center">
                    <i class="fas fa-user-plus text-brand"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">New Membership</span>
            </a>
            <a href="{{ route('manager.schedule-approval') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-calendar-check text-green-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Approve Schedule</span>
            </a>
            <a href="{{ route('manager.announcement') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-bullhorn text-purple-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Create Announcement</span>
            </a>
            <a href="{{ route('manager.reporting') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <i class="fas fa-file-alt text-red-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Generate Report</span>
            </a>
        </div>
    </div>
</div>

<!-- Machine Schedule Calendar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-0">Machine Schedule &mdash; {{ now()->format('F Y') }}</h3>
            <p class="text-sm text-muted mb-0">Approved machinery bookings for the current month.</p>
        </div>
        <a href="{{ route('manager.machine-schedule') }}" class="text-sm text-brand text-decoration-none">Full calendar</a>
    </div>
    <x-schedule-calendar :calendar-days="$calendarDays" :first-weekday="$firstWeekday" :days-in-month="$daysInMonth"
        :show-names="true" min-height="90px" />
</div>
@endsection
