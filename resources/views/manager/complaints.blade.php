@extends('manager.layout')

@section('title', 'Complaints')
@section('header', 'Complaints Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Total Complaints</p>
        <p class="text-3xl font-bold text-gray-900">28</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">New</p>
        <p class="text-3xl font-bold text-blue-600">5</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">In Progress</p>
        <p class="text-3xl font-bold text-yellow-600">8</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Resolved</p>
        <p class="text-3xl font-bold text-green-600">15</p>
    </div>
</div>

<!-- Complaints Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" placeholder="Search complaints..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option>Machinery</option>
                <option>Service</option>
                <option>Staff</option>
                <option>Billing</option>
                <option>Other</option>
            </select>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option>New</option>
                <option>In Progress</option>
                <option>Resolved</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Complaint ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Complaint Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">CMP-001</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Juan Dela Cruz</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Machinery</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">Harvester malfunction during harvest</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 01, 2026</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">New</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Mark as Viewed">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">CMP-002</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maria Santos</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Service</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">Delayed schedule response</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 02, 2026</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">In Progress</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Mark as Viewed">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">CMP-003</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Pedro Reyes</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Billing</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">Incorrect billing amount</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 03, 2026</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Resolved</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">CMP-004</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Roberto Tan</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Other</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">Feedback on service quality</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 04, 2026</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">New</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Mark as Viewed">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing 1-5 of 28 entries</p>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>
@endsection