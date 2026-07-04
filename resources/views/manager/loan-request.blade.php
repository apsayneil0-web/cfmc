@extends('manager.layout')

@section('title', 'Loan Requests')
@section('header', 'Loan Request Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Header Actions -->
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" placeholder="Search requests..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>New Request
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Farmer Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Repayment Terms</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collateral</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">LN-001</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Juan Dela Cruz</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱50,000</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Farming Equipment</td>
                    <td class="px-6 py-4 text-sm text-gray-500">12 months</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Tractor</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">LN-002</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Maria Santos</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱30,000</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Seeds & Fertilizer</td>
                    <td class="px-6 py-4 text-sm text-gray-500">6 months</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Land Title</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="p-2 text-gray-400 cursor-not-allowed" disabled>
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">LN-003</td>
                    <td class="px-6 py-4 text-sm text-gray-900">Pedro Reyes</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₱75,000</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Harvester Purchase</td>
                    <td class="px-6 py-4 text-sm text-gray-500">24 months</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Property</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Rejected</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View">
                                <i class="fas fa-eye"></i>
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
        <p class="text-sm text-gray-500">Showing 1-10 of 12 entries</p>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>

<!-- Create Request Modal -->
<div id="createRequestModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">New Loan Request</h3>
            <button onclick="document.getElementById('createRequestModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Farmer</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Farmer</option>
                    <option>Juan Dela Cruz</option>
                    <option>Maria Santos</option>
                    <option>Pedro Reyes</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loan Amount</label>
                    <input type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="₱0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Purpose</option>
                        <option>Farming Equipment</option>
                        <option>Seeds & Fertilizer</option>
                        <option>Machinery Purchase</option>
                        <option>Working Capital</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Repayment Terms</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Terms</option>
                        <option>6 months</option>
                        <option>12 months</option>
                        <option>18 months</option>
                        <option>24 months</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Collateral</label>
                    <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Describe collateral">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Documents</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                    <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-1"></i>
                    <p class="text-sm text-gray-500">Upload supporting documents</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="document.getElementById('createRequestModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Submit Request</button>
            </div>
        </form>
    </div>
</div>
@endsection