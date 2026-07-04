@extends('manager.layout')

@section('title', 'CBU Management')
@section('header', 'CBU Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Total Contributions</p>
        <p class="text-3xl font-bold text-gray-900">₱1,250,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Total Balance</p>
        <p class="text-3xl font-bold text-blue-600">₱890,000</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Active Members</p>
        <p class="text-3xl font-bold text-green-600">156</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">This Month</p>
        <p class="text-3xl font-bold text-emerald-600">+₱125,000</p>
    </div>
</div>

<!-- CBU Records -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" placeholder="Search members..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Members</option>
                <option>Active</option>
                <option>Inactive</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Entry
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contribution Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Running Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">FM-001</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Juan Dela Cruz</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Monthly</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱1,000</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 01, 2026</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱12,500</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">FM-002</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maria Santos</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Monthly</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱800</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 01, 2026</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱8,400</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">FM-003</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Pedro Reyes</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Share Capital</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱5,000</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Jul 05, 2026</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">₱45,000</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing 1-10 of 156 entries</p>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>
@endsection