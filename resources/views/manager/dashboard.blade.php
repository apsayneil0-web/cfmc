@extends('manager.layout')

@section('title', 'Manager Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Registered Farmers</p>
                <p class="text-3xl font-bold text-gray-900">156</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-green-600 mt-2"><i class="fas fa-arrow-up"></i> 12% from last month</p>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Pending Membership Requests</p>
                <p class="text-3xl font-bold text-gray-900">23</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Requires review</p>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Pending Schedule Requests</p>
                <p class="text-3xl font-bold text-gray-900">18</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Awaiting approval</p>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Approved Schedules</p>
                <p class="text-3xl font-bold text-gray-900">45</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="fas fa-calendar-check text-green-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">This month</p>
    </div>
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Active Loan Applications</p>
                <p class="text-3xl font-bold text-gray-900">12</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="fas fa-hand-holding-usd text-red-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">PHP 450,000 total</p>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Available Machinery</p>
                <p class="text-3xl font-bold text-gray-900">8</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center">
                <i class="fas fa-tractor text-indigo-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">3 under maintenance</p>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total CBU</p>
                <p class="text-3xl font-bold text-gray-900">₱1.2M</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-pink-100 flex items-center justify-center">
                <i class="fas fa-piggy-bank text-pink-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-green-600 mt-2"><i class="fas fa-arrow-up"></i> 8% growth</p>
    </div>

    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Net Income</p>
                <p class="text-3xl font-bold text-gray-900">₱890K</p>
            </div>
            <div class="w-14 h-14 rounded-xl bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-chart-line text-emerald-600 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-green-600 mt-2"><i class="fas fa-arrow-up"></i> 15% from last month</p>
    </div>
</div>

<!-- Recent Activities & Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Activities -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
            <a href="#" class="text-sm text-blue-600 hover:underline">View All</a>
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
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-calendar text-blue-600"></i>
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
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-user-plus text-blue-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">New Membership</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-calendar-check text-green-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Approve Schedule</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-bullhorn text-purple-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Create Announcement</span>
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <i class="fas fa-file-alt text-red-600"></i>
                </div>
                <span class="text-sm font-medium text-gray-700">Generate Report</span>
            </a>
        </div>
    </div>
</div>
@endsection