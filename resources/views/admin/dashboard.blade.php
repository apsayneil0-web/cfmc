@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Total Users" value="171" icon="fa-users" color="primary" trend="12% from last month" />
    <x-stat-card label="Registered Farmers" value="156" icon="fa-seedling" color="success" />
    <x-stat-card label="Cooperative Managers" value="4" icon="fa-user-tie" color="info" />
    <x-stat-card label="Crop Types Tracked" value="11" icon="fa-leaf" color="warning" />
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card label="Active Accounts" value="148" icon="fa-user-check" color="success" />
    <x-stat-card label="Inactive Accounts" value="14" icon="fa-user-clock" color="secondary" />
    <x-stat-card label="Locked Accounts" value="3" icon="fa-user-lock" color="danger" />
    <x-stat-card label="Archived Accounts" value="6" icon="fa-user-slash" color="secondary" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- System Overview -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-0">Users by Role</h3>
        </div>
        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Farmer</span>
                    <span class="text-sm font-medium text-gray-900">156</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar bg-success" style="width: 91%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Manager</span>
                    <span class="text-sm font-medium text-gray-900">4</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar bg-primary" style="width: 2%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">Admin</span>
                    <span class="text-sm font-medium text-gray-900">1</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div class="progress-bar bg-info" style="width: 1%"></div>
                </div>
            </div>
        </div>

        <hr class="my-4 text-gray-200">

        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-gray-900 mb-0">Recent Account Activity</h4>
        </div>
        <div class="space-y-3">
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <x-avatar-initials name="Juan Dela Cruz" color="success" size="8" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 mb-0">New farmer account created</p>
                    <p class="text-xs text-gray-500 mb-0">Juan Dela Cruz - Brgy. San Jose</p>
                </div>
                <p class="text-xs text-gray-400 mb-0">2 hours ago</p>
            </div>
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <x-avatar-initials name="Maria Santos" color="danger" size="8" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 mb-0">Account locked after failed login attempts</p>
                    <p class="text-xs text-gray-500 mb-0">Maria Santos</p>
                </div>
                <p class="text-xs text-gray-400 mb-0">5 hours ago</p>
            </div>
            <div class="flex items-center gap-3">
                <x-avatar-initials name="Pedro Reyes" color="secondary" size="8" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 mb-0">Account archived</p>
                    <p class="text-xs text-gray-500 mb-0">Pedro Reyes</p>
                </div>
                <p class="text-xs text-gray-400 mb-0">1 day ago</p>
            </div>
        </div>
    </div>

    <!-- Cooperative Snapshot -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Cooperative Snapshot</h3>
        <div class="space-y-3">
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">Pending Memberships</span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">23</span>
            </div>
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">Approved Members</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">156</span>
            </div>
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">Crop Varieties</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">11</span>
            </div>
            <div class="d-flex align-items-center justify-content-between p-3 rounded-lg border border-gray-200">
                <span class="text-sm font-medium text-gray-700">System Status</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">Operational</span>
            </div>
        </div>
    </div>
</div>
@endsection
