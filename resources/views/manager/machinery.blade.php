@extends('manager.layout')

@section('title', 'Machinery')
@section('header', 'Machinery Management')

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Total Machinery</p>
        <p class="text-3xl font-bold text-gray-900">11</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Available</p>
        <p class="text-3xl font-bold text-green-600">8</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">In Use</p>
        <p class="text-3xl font-bold text-blue-600">2</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Under Maintenance</p>
        <p class="text-3xl font-bold text-red-600">1</p>
    </div>
</div>

<!-- Machinery Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" placeholder="Search machinery..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option>Available</option>
                <option>In Use</option>
                <option>Maintenance</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add Machinery
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Machine ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Machine Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Brand</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage Hours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned Operator</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">MCH-001</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Harvester</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Kubota</td>
                    <td class="px-6 py-4 text-sm text-gray-500">KB-HV-2023-001</td>
                    <td class="px-6 py-4 text-sm text-gray-500">1</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="text-green-600 font-medium">245 hrs</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Available</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">-</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Maintenance History">
                                <i class="fas fa-wrench"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">MCH-002</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Tractor</td>
                    <td class="px-6 py-4 text-sm text-gray-500">John Deere</td>
                    <td class="px-6 py-4 text-sm text-gray-500">JD-TR-2022-015</td>
                    <td class="px-6 py-4 text-sm text-gray-500">1</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="text-yellow-600 font-medium">480 hrs</span>
                        <span class="ml-1 text-xs text-red-600"><i class="fas fa-exclamation-triangle"></i></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Maintenance</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">-</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Maintenance History">
                                <i class="fas fa-wrench"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">MCH-003</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Tractor</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Kubota</td>
                    <td class="px-6 py-4 text-sm text-gray-500">KB-TR-2023-008</td>
                    <td class="px-6 py-4 text-sm text-gray-500">1</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="text-green-600 font-medium">180 hrs</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">In Use</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">Carlos Mendoza</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Maintenance History">
                                <i class="fas fa-wrench"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">MCH-004</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Pump Boat</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Honda</td>
                    <td class="px-6 py-4 text-sm text-gray-500">HN-PB-2021-003</td>
                    <td class="px-6 py-4 text-sm text-gray-500">2</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="text-green-600 font-medium">320 hrs</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Available</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">-</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg" title="Maintenance History">
                                <i class="fas fa-wrench"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing 1-10 of 11 entries</p>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>

<!-- Maintenance Alert -->
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mt-6">
    <div class="flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
        <div>
            <p class="text-sm font-medium text-red-900">Maintenance Alert</p>
            <p class="text-xs text-red-700">Tractor #002 (MCH-002) has reached 480 hours and requires maintenance.</p>
        </div>
    </div>
</div>
@endsection